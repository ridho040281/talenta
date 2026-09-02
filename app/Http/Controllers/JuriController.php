<?php

namespace App\Http\Controllers;

use App\Models\Competition;
use App\Models\Registration;
use App\Models\Score;
use App\Models\ScoreDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JuriController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();

        // Get competitions assigned to this judge (or all if superadmin)
        $competitions = Competition::with(['category', 'criteria', 'registrations.scores'])
            ->when($user->role === 'juri', function ($q) use ($user) {
                $q->whereHas('judges', function ($sub) use ($user) {
                    $sub->where('user_id', $user->id);
                });
            })
            ->get();

        return view('juri.dashboard', compact('user', 'competitions'));
    }

    public function scoringSheet($competition_id)
    {
        $user = Auth::user();
        $competition = Competition::with(['criteria', 'registrations' => function ($q) {
            $q->where('status', 'verified')->with(['members', 'scores']);
        }])->findOrFail($competition_id);

        // Sort participants by Draw Number first (Urutan Tampil), then ID
        $participants = $competition->registrations->sortBy(function ($reg) {
            return $reg->draw_number ?? 999;
        })->values();

        // Get existing scores by this judge
        $existingScores = Score::with('details')
            ->where('competition_id', $competition->id)
            ->where('judge_id', $user->id)
            ->get()
            ->keyBy('registration_id');

        return view('juri.scoring-sheet', compact('competition', 'participants', 'existingScores', 'user'));
    }

    public function storeScore(Request $request, $competition_id, $registration_id)
    {
        $user = Auth::user();
        $competition = Competition::with('criteria')->findOrFail($competition_id);
        $registration = Registration::where('id', $registration_id)
            ->where('competition_id', $competition->id)
            ->firstOrFail();

        $validated = $request->validate([
            'criteria' => ['required', 'array'],
            'criteria.*' => ['required', 'numeric', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string'],
            'is_locked' => ['nullable', 'boolean'],
        ]);

        // Calculate weighted score
        $totalScore = 0;
        $totalWeight = $competition->criteria->sum('weight_percentage') ?: 100;

        foreach ($competition->criteria as $criterion) {
            $val = floatval($validated['criteria'][$criterion->id] ?? 0);
            $weight = $criterion->weight_percentage;
            $totalScore += ($val * ($weight / $totalWeight));
        }

        $score = Score::updateOrCreate(
            [
                'competition_id' => $competition->id,
                'registration_id' => $registration->id,
                'judge_id' => $user->id,
            ],
            [
                'total_score' => round($totalScore, 2),
                'is_locked' => $request->boolean('is_locked'),
                'notes' => $validated['notes'] ?? null,
            ]
        );

        foreach ($competition->criteria as $criterion) {
            ScoreDetail::updateOrCreate(
                [
                    'score_id' => $score->id,
                    'criterion_id' => $criterion->id,
                ],
                [
                    'score_value' => floatval($validated['criteria'][$criterion->id] ?? 0),
                ]
            );
        }

        return redirect()->route('juri.scoring', $competition->id)
            ->with('success', 'Nilai untuk ' . $registration->display_name . ' (' . number_format($score->total_score, 2) . ') berhasil disimpan' . ($score->is_locked ? ' dan dikunci.' : '.'));
    }
}
