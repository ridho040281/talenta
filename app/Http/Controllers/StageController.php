<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\Competition;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StageController extends Controller
{
    /**
     * Public / Smart TV Stage Viewer (3-Panel Display: Sedang Tampil, Berikutnya, Selesai)
     */
    public function stageViewer($slug)
    {
        $competition = Competition::with(['category', 'registrations' => function ($q) {
            $q->where('status', 'verified')->with('members');
        }])->where(function ($query) use ($slug) {
            $query->where('slug', $slug)
                ->orWhere('code', strtoupper($slug));
        })->firstOrFail();

        $appSettings = AppSetting::pluck('value', 'key')->toArray();
        $state = $this->buildStageState($competition);

        return view('public.stage-viewer', [
            'competition' => $competition,
            'appSettings' => $appSettings,
            'initialState' => $state,
        ]);
    }

    /**
     * API State for TV & Operator Real-time Sync
     */
    public function apiState($slug)
    {
        $competition = Competition::with(['category', 'registrations' => function ($q) {
            $q->where('status', 'verified')->with('members');
        }])->where(function ($query) use ($slug) {
            $query->where('slug', $slug)
                ->orWhere('code', strtoupper($slug));
        })->first();

        if (! $competition) {
            return response()->json(['error' => 'Cabang lomba tidak ditemukan'], 404);
        }

        $state = $this->buildStageState($competition);

        return response()->json($state)
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    /**
     * Operator / Timekeeper Console (PIC & Superadmin)
     */
    public function operatorPanel($competition_id)
    {
        $user = Auth::user();
        if ($user->role !== 'superadmin') {
            $managedIds = PicController::getManagedCompetitionIds($user);
            if (! in_array($competition_id, $managedIds)) {
                abort(403, 'Anda tidak memiliki hak akses untuk mengelola panggung cabang lomba ini.');
            }
        }

        $competition = Competition::with(['category', 'registrations' => function ($q) {
            $q->where('status', 'verified')->with(['members', 'scores']);
        }])->findOrFail($competition_id);

        $appSettings = AppSetting::pluck('value', 'key')->toArray();
        $state = $this->buildStageState($competition);

        $registrations = $competition->registrations->sortBy(function ($r) {
            return $r->draw_number ?? 99999;
        })->values();

        return view('pic.stage-control', compact('competition', 'appSettings', 'state', 'registrations'));
    }

    /**
     * Handle Operator Actions (Start, Pause, Resume, Next, Prev, Bell, Adjust Time)
     */
    public function handleAction(Request $request, $competition_id)
    {
        $user = Auth::user();
        if ($user->role !== 'superadmin') {
            $managedIds = PicController::getManagedCompetitionIds($user);
            if (! in_array($competition_id, $managedIds)) {
                return response()->json(['error' => 'Akses ditolak.'], 403);
            }
        }

        $competition = Competition::with('registrations')->findOrFail($competition_id);
        $action = $request->input('action');
        $stageState = $competition->stage_state ?? [];

        $defaultTotalSeconds = ($competition->stage_duration_minutes ?: 7) * 60;

        switch ($action) {
            case 'select_performer':
                $regId = $request->input('registration_id');
                $targetReg = Registration::where('competition_id', $competition->id)->where('id', $regId)->first();
                if ($targetReg) {
                    Registration::where('competition_id', $competition->id)
                        ->where('stage_status', 'performing')
                        ->update(['stage_status' => 'waiting']);

                    $targetReg->update(['stage_status' => 'performing']);

                    $stageState['current_registration_id'] = $targetReg->id;
                    $stageState['timer_status'] = 'idle';
                    $stageState['seconds_remaining'] = $defaultTotalSeconds;
                    $stageState['total_duration_seconds'] = $defaultTotalSeconds;
                    $stageState['started_at'] = null;
                    $stageState['paused_at'] = null;
                }
                break;

            case 'start':
            case 'resume':
                if (empty($stageState['current_registration_id'])) {
                    // Auto select the first waiting participant
                    $firstWaiting = $competition->registrations()
                        ->where('status', 'verified')
                        ->where('stage_status', 'waiting')
                        ->orderByRaw('COALESCE(draw_number, 99999) ASC')
                        ->first();

                    if ($firstWaiting) {
                        $firstWaiting->update(['stage_status' => 'performing']);
                        $stageState['current_registration_id'] = $firstWaiting->id;
                        $stageState['seconds_remaining'] = $defaultTotalSeconds;
                        $stageState['total_duration_seconds'] = $defaultTotalSeconds;
                    }
                }

                if (! empty($stageState['current_registration_id'])) {
                    $stageState['timer_status'] = 'running';
                    $stageState['started_at'] = now()->toIso8601String();
                    $stageState['paused_at'] = null;
                    if (! isset($stageState['seconds_remaining'])) {
                        $stageState['seconds_remaining'] = $defaultTotalSeconds;
                    }
                }
                break;

            case 'pause':
                if (($stageState['timer_status'] ?? '') === 'running') {
                    $secRemaining = (int) ($request->input('seconds_remaining', $stageState['seconds_remaining'] ?? $defaultTotalSeconds));
                    $stageState['timer_status'] = 'paused';
                    $stageState['seconds_remaining'] = max(0, $secRemaining);
                    $stageState['paused_at'] = now()->toIso8601String();
                }
                break;

            case 'adjust_time':
                $delta = (int) $request->input('delta_seconds', 0);
                $curr = (int) ($stageState['seconds_remaining'] ?? $defaultTotalSeconds);
                $stageState['seconds_remaining'] = max(0, $curr + $delta);
                $stageState['total_duration_seconds'] = max($stageState['seconds_remaining'], (int) ($stageState['total_duration_seconds'] ?? $defaultTotalSeconds));
                break;

            case 'finish':
                $elapsedSec = (int) $request->input('elapsed_seconds', 0);
                $currRegId = $stageState['current_registration_id'] ?? null;

                if ($currRegId) {
                    $currReg = Registration::find($currRegId);
                    if ($currReg) {
                        $currReg->update([
                            'stage_status' => 'completed',
                            'stage_duration_seconds' => $elapsedSec > 0 ? $elapsedSec : ($defaultTotalSeconds - ($stageState['seconds_remaining'] ?? 0)),
                        ]);
                    }
                }

                // Auto find next waiting performer
                $nextWaiting = Registration::where('competition_id', $competition->id)
                    ->where('status', 'verified')
                    ->where('stage_status', 'waiting')
                    ->orderByRaw('COALESCE(draw_number, 99999) ASC')
                    ->first();

                if ($nextWaiting) {
                    $nextWaiting->update(['stage_status' => 'performing']);
                    $stageState['current_registration_id'] = $nextWaiting->id;
                    $stageState['timer_status'] = 'idle';
                    $stageState['seconds_remaining'] = $defaultTotalSeconds;
                    $stageState['total_duration_seconds'] = $defaultTotalSeconds;
                    $stageState['started_at'] = null;
                    $stageState['paused_at'] = null;
                } else {
                    $stageState['current_registration_id'] = null;
                    $stageState['timer_status'] = 'finished';
                    $stageState['seconds_remaining'] = 0;
                }
                break;

            case 'skip':
                $currRegId = $stageState['current_registration_id'] ?? null;
                if ($currRegId) {
                    $currReg = Registration::find($currRegId);
                    if ($currReg) {
                        $currReg->update(['stage_status' => 'skipped']);
                    }
                }

                $nextWaiting = Registration::where('competition_id', $competition->id)
                    ->where('status', 'verified')
                    ->where('stage_status', 'waiting')
                    ->orderByRaw('COALESCE(draw_number, 99999) ASC')
                    ->first();

                if ($nextWaiting) {
                    $nextWaiting->update(['stage_status' => 'performing']);
                    $stageState['current_registration_id'] = $nextWaiting->id;
                    $stageState['timer_status'] = 'idle';
                    $stageState['seconds_remaining'] = $defaultTotalSeconds;
                    $stageState['total_duration_seconds'] = $defaultTotalSeconds;
                    $stageState['started_at'] = null;
                    $stageState['paused_at'] = null;
                } else {
                    $stageState['current_registration_id'] = null;
                    $stageState['timer_status'] = 'idle';
                    $stageState['seconds_remaining'] = $defaultTotalSeconds;
                }
                break;

            case 'trigger_bell':
                $bellType = $request->input('bell_type', $competition->stage_bell_sound ?: 'bell');
                $stageState['bell_trigger'] = [
                    'type' => $bellType,
                    'timestamp' => round(microtime(true) * 1000),
                ];
                break;

            case 'reset_timer':
                $stageState['timer_status'] = 'idle';
                $stageState['seconds_remaining'] = $defaultTotalSeconds;
                $stageState['total_duration_seconds'] = $defaultTotalSeconds;
                $stageState['started_at'] = null;
                $stageState['paused_at'] = null;
                break;
        }

        $competition->update(['stage_state' => $stageState]);

        $updatedState = $this->buildStageState($competition->fresh(['registrations.members']));

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'state' => $updatedState,
            ]);
        }

        return redirect()->back()->with('success', 'Status panggung berhasil diperbarui.');
    }

    /**
     * Reset all stage status for this competition
     */
    public function resetAllStage($competition_id)
    {
        $user = Auth::user();
        if ($user->role !== 'superadmin') {
            $managedIds = PicController::getManagedCompetitionIds($user);
            if (! in_array($competition_id, $managedIds)) {
                abort(403, 'Akses ditolak.');
            }
        }

        $competition = Competition::findOrFail($competition_id);

        Registration::where('competition_id', $competition->id)
            ->update([
                'stage_status' => 'waiting',
                'stage_duration_seconds' => null,
            ]);

        $defaultTotalSeconds = ($competition->stage_duration_minutes ?: 7) * 60;
        $competition->update([
            'stage_state' => [
                'current_registration_id' => null,
                'timer_status' => 'idle',
                'seconds_remaining' => $defaultTotalSeconds,
                'total_duration_seconds' => $defaultTotalSeconds,
                'started_at' => null,
                'paused_at' => null,
                'bell_trigger' => null,
            ],
        ]);

        return redirect()->back()->with('success', 'Semua antrian giliran panggung berhasil direset.');
    }

    /**
     * Helper to construct 3-Panel State
     */
    private function buildStageState(Competition $competition): array
    {
        $stageState = $competition->stage_state ?? [];
        $defaultDurationMin = $competition->stage_duration_minutes ?: 7;
        $defaultWarningMin = $competition->stage_warning_minutes ?: 2;
        $defaultOvertimeMin = $competition->stage_overtime_minutes ?: 1;
        $totalDurationSec = (int) ($stageState['total_duration_seconds'] ?? ($defaultDurationMin * 60));
        $warningThresholdSec = $defaultWarningMin * 60;

        $registrations = $competition->registrations->sortBy(function ($r) {
            return $r->draw_number ?? 99999;
        })->values();

        // 1. Current Performer
        $currentReg = null;
        if (! empty($stageState['current_registration_id'])) {
            $currentReg = $registrations->firstWhere('id', $stageState['current_registration_id']);
        }

        if (! $currentReg) {
            $currentReg = $registrations->firstWhere('stage_status', 'performing')
                       ?: $registrations->firstWhere('stage_status', 'waiting');
        }

        // 2. Next Performer (Waiting list right after current)
        $nextReg = null;
        if ($currentReg) {
            $nextReg = $registrations->filter(function ($r) use ($currentReg) {
                return $r->stage_status === 'waiting' && $r->id !== $currentReg->id;
            })->first();
        } else {
            $nextReg = $registrations->firstWhere('stage_status', 'waiting');
        }

        // 3. Completed List (ordered latest completed first)
        $completedList = $registrations->where('stage_status', 'completed')
            ->values()
            ->map(function ($r) {
                $firstMember = $r->members->first();
                $displayName = $r->team_name ?: ($firstMember?->full_name ?: 'Peserta #'.$r->id);
                $durSec = $r->stage_duration_seconds ?? 0;
                $min = floor($durSec / 60);
                $sec = $durSec % 60;

                return [
                    'id' => $r->id,
                    'draw_number' => $r->draw_number,
                    'participant_number' => $r->participant_number,
                    'name' => $displayName,
                    'institution' => $r->institution_name,
                    'duration_seconds' => $durSec,
                    'formatted_duration' => sprintf('%02d:%02d', $min, $sec),
                ];
            });

        $currentData = null;
        if ($currentReg) {
            $firstMember = $currentReg->members->first();
            $displayName = $currentReg->team_name ?: ($firstMember?->full_name ?: 'Peserta #'.$currentReg->id);
            $membersList = $currentReg->members->pluck('full_name')->toArray();

            $currentData = [
                'id' => $currentReg->id,
                'draw_number' => $currentReg->draw_number,
                'participant_number' => $currentReg->participant_number,
                'name' => $displayName,
                'institution' => $currentReg->institution_name,
                'members' => $membersList,
                'sub_category' => $currentReg->sub_category,
                'stage_status' => $currentReg->stage_status,
            ];
        }

        $nextData = null;
        if ($nextReg) {
            $firstMember = $nextReg->members->first();
            $displayName = $nextReg->team_name ?: ($firstMember?->full_name ?: 'Peserta #'.$nextReg->id);

            $nextData = [
                'id' => $nextReg->id,
                'draw_number' => $nextReg->draw_number,
                'participant_number' => $nextReg->participant_number,
                'name' => $displayName,
                'institution' => $nextReg->institution_name,
            ];
        }

        $timerStatus = $stageState['timer_status'] ?? 'idle';
        $secondsRemaining = isset($stageState['seconds_remaining']) ? (int) $stageState['seconds_remaining'] : $totalDurationSec;

        return [
            'competition' => [
                'id' => $competition->id,
                'name' => $competition->name,
                'code' => $competition->code,
                'slug' => $competition->slug,
                'venue' => $competition->venue,
                'duration_minutes' => $defaultDurationMin,
                'warning_minutes' => $defaultWarningMin,
                'overtime_minutes' => $defaultOvertimeMin,
                'bell_sound' => $competition->stage_bell_sound ?: 'bell',
            ],
            'current' => $currentData,
            'next' => $nextData,
            'completed' => $completedList,
            'timer' => [
                'status' => $timerStatus,
                'seconds_remaining' => $secondsRemaining,
                'total_duration_seconds' => $totalDurationSec,
                'warning_threshold_seconds' => $warningThresholdSec,
                'started_at' => $stageState['started_at'] ?? null,
                'paused_at' => $stageState['paused_at'] ?? null,
                'bell_trigger' => $stageState['bell_trigger'] ?? null,
            ],
            'server_time' => now()->toIso8601String(),
        ];
    }
}
