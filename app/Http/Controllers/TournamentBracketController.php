<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\BadmintonMatch;
use App\Models\Competition;
use App\Traits\CompetitionPoolTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TournamentBracketController extends Controller
{
    use CompetitionPoolTrait;

    /**
     * Tampilan Bagan langsung untuk Wasit & Pengurus Bulu Tangkis
     */
    public function badmintonShow(Request $request, $competition_id = null)
    {
        if (! $competition_id) {
            $user = Auth::user();

            // 1. Jika PIC Lomba, cari cabor turnamen yang dikelolanya dengan prioritas Bulu Tangkis
            if ($user && $user->role === 'pic_lomba') {
                $comp = null;

                // Cek apakah mengelola Bulu Tangkis
                if ($user->managesBadminton()) {
                    $comp = Competition::where(function ($q) {
                        $q->where('code', 'BLT')
                            ->orWhere('name', 'like', '%Bulu Tangkis%')
                            ->orWhere('name', 'like', '%Badminton%');
                    })->first();
                }

                if (! $comp) {
                    $comp = $user->managedCompetitions()
                        ->where(function ($q) {
                            $q->where('code', 'BLT')
                                ->orWhere('name', 'like', '%Bulu Tangkis%')
                                ->orWhere('name', 'like', '%Badminton%');
                        })
                        ->first();
                }

                // Fallback ke Tenis Meja hanya jika BUKAN PIC Bulu Tangkis
                if (! $comp) {
                    $comp = $user->managedCompetitions()
                        ->where(function ($q) {
                            $q->where('code', 'TMJ')
                                ->orWhere('name', 'like', '%Tenis Meja%');
                        })
                        ->first();
                }

                if ($comp) {
                    $competition_id = $comp->id;
                }
            }

            // 2. Jika Juri / Wasit
            if (! $competition_id && $user && $user->role === 'juri') {
                $comp = $user->judgedCompetitions()
                    ->where(function ($q) {
                        $q->where('code', 'BLT')
                            ->orWhere('name', 'like', '%Bulu Tangkis%')
                            ->orWhere('name', 'like', '%Badminton%');
                    })
                    ->first();

                if (! $comp) {
                    $comp = $user->judgedCompetitions()
                        ->where(function ($q) {
                            $q->where('code', 'TMJ')
                                ->orWhere('name', 'like', '%Tenis Meja%');
                        })
                        ->first();
                }

                if ($comp) {
                    $competition_id = $comp->id;
                }
            }

            // 3. Default Global (Super Admin, Panitia, atau Pengguna Umum): SELALU Bulu Tangkis Utama
            if (! $competition_id) {
                $competition = Competition::where(function ($q) {
                    $q->where('code', 'BLT')
                        ->orWhere('name', 'like', '%Bulu Tangkis%')
                        ->orWhere('name', 'like', '%Badminton%');
                })->first();

                // Secondary fallback jika cabor Bulu Tangkis belum ada di DB
                if (! $competition) {
                    $competition = Competition::where(function ($q) {
                        $q->where('code', 'TMJ')
                            ->orWhere('name', 'like', '%Tenis Meja%');
                    })->first();
                }

                if (! $competition) {
                    return redirect()->route('admin.dashboard')->with('error', 'Cabang lomba turnamen belum terdaftar dalam sistem.');
                }

                $competition_id = $competition->id;
            }
        }

        return $this->show($request, $competition_id);
    }

    /**
     * Tampilan Bagan Interaktif untuk PIC & Admin
     */
    public function show(Request $request, $competition_id)
    {
        $competition = Competition::with(['category', 'registrations.members'])->findOrFail($competition_id);
        $this->ensureIsBadminton($competition);
        $user = Auth::user();

        // Check authorization
        if (! in_array($user->role, ['superadmin', 'panitia'])) {
            $managedIds = PicController::getManagedCompetitionIds($user);
            $isAuthorizedSport = $user->managesTournamentBracket() && (
                in_array(strtoupper($competition->code ?? ''), ['BLT', 'TMJ']) ||
                str_contains(strtolower($competition->name ?? ''), 'bulu tangkis') ||
                str_contains(strtolower($competition->name ?? ''), 'badminton') ||
                str_contains(strtolower($competition->name ?? ''), 'tenis meja')
            );

            if (! in_array($competition->id, $managedIds) && ! $isAuthorizedSport) {
                abort(403, 'Anda tidak memiliki hak akses untuk mengelola bagan lomba ini.');
            }
        }

        $pools = $this->buildCompetitionPools($competition);
        $activePoolKey = $request->query('pool', $pools[0]['key'] ?? 'all');
        $activePool = collect($pools)->firstWhere('key', $activePoolKey) ?? ($pools[0] ?? null);

        $bracketData = null;
        if ($activePool) {
            $bracketData = $this->buildTournamentTree($activePool['participants'], $competition, $activePoolKey);
        }

        // Daftar Cabor Turnamen untuk Cabor Switcher (Bulu Tangkis vs Tenis Meja)
        $tCompQuery = Competition::where(function ($q) {
            $q->whereIn('code', ['BLT', 'TMJ'])
                ->orWhere('name', 'like', '%Bulu Tangkis%')
                ->orWhere('name', 'like', '%Badminton%')
                ->orWhere('name', 'like', '%Tenis Meja%');
        })
            ->orderByRaw("CASE WHEN code = 'BLT' OR name LIKE '%Bulu Tangkis%' OR name LIKE '%Badminton%' THEN 1 ELSE 2 END");

        if (! in_array($user->role, ['superadmin', 'panitia']) && ! $user->managesTournamentBracket()) {
            $tCompQuery->whereIn('id', PicController::getManagedCompetitionIds($user));
        }
        $tournamentCompetitions = $tCompQuery->get();

        $settings = $competition->bracket_settings ?? [];
        $tvPublication = $settings['tv_publication'] ?? [
            'is_published' => true,
            'standby_title' => 'BAGAN PERTANDINGAN SEDANG DISIAPKAN',
            'standby_message' => 'Bagan resmi akan segera dirilis oleh panitia setelah sesi pengundian dan technical meeting selesai.',
            'standby_contact' => 'Meja Panitia / Sekretariat GOR',
        ];

        return view('pic.bracket', compact('competition', 'pools', 'activePoolKey', 'activePool', 'bracketData', 'tournamentCompetitions', 'tvPublication'));
    }

    /**
     * Tampilan Publik & TV GOR untuk Penonton & Peserta
     */
    public function publicView(Request $request, $slug)
    {
        $competition = Competition::with(['category', 'registrations.members'])
            ->where('slug', $slug)
            ->orWhere('id', $slug)
            ->firstOrFail();
        $this->ensureIsBadminton($competition);

        $pools = $this->buildCompetitionPools($competition);
        $activePoolKey = $request->query('pool', $pools[0]['key'] ?? 'all');
        $activePool = collect($pools)->firstWhere('key', $activePoolKey) ?? ($pools[0] ?? null);

        $bracketData = null;
        if ($activePool) {
            $bracketData = $this->buildTournamentTree($activePool['participants'], $competition, $activePoolKey);
        }

        $appSettings = AppSetting::pluck('value', 'key')->all();

        $settings = $competition->bracket_settings ?? [];
        $publication = $settings['tv_publication'] ?? [
            'is_published' => true,
            'standby_title' => 'BAGAN PERTANDINGAN SEDANG DISIAPKAN',
            'standby_message' => 'Bagan resmi akan segera dirilis oleh panitia setelah sesi pengundian dan technical meeting selesai.',
            'standby_contact' => 'Meja Panitia / Sekretariat GOR',
        ];

        $isPublished = (bool) ($publication['is_published'] ?? true);

        // Check if current visitor is an authorized staff member (Admin / PIC / Wasit)
        $user = Auth::user();
        $isAuthorizedStaff = false;
        if ($user) {
            if (in_array($user->role, ['superadmin', 'panitia'])) {
                $isAuthorizedStaff = true;
            } else {
                $managedIds = PicController::getManagedCompetitionIds($user);
                $isAuthorizedSport = (method_exists($user, 'managesTournamentBracket') && $user->managesTournamentBracket()) && (
                    in_array(strtoupper($competition->code ?? ''), ['BLT', 'TMJ']) ||
                    str_contains(strtolower($competition->name ?? ''), 'bulu tangkis') ||
                    str_contains(strtolower($competition->name ?? ''), 'badminton') ||
                    str_contains(strtolower($competition->name ?? ''), 'tenis meja')
                );
                $isAuthorizedStaff = in_array($competition->id, $managedIds) || $isAuthorizedSport;
            }
        }

        $isPreviewMode = (! $isPublished && $isAuthorizedStaff);

        // If bracket is locked and visitor is NOT a staff member, display standby screen
        if (! $isPublished && ! $isAuthorizedStaff) {
            return view('public.bracket-standby', compact('competition', 'appSettings', 'publication', 'pools', 'activePoolKey'));
        }

        return view('public.bracket-viewer', compact('competition', 'pools', 'activePoolKey', 'activePool', 'bracketData', 'appSettings', 'publication', 'isPreviewMode'));
    }

    /**
     * API Cek Status Publikasi TV Bagan (untuk auto-refresh layar TV GOR)
     */
    public function publicationStatus($slug)
    {
        $competition = Competition::where('slug', $slug)->orWhere('id', $slug)->first();
        if (! $competition) {
            return response()->json(['is_published' => true]);
        }

        $settings = $competition->bracket_settings ?? [];
        $publication = $settings['tv_publication'] ?? ['is_published' => true];

        return response()->json([
            'is_published' => (bool) ($publication['is_published'] ?? true),
        ]);
    }

    /**
     * Format Cetak Dokumen Bagan (A4/F4 Landscape Print-Ready)
     */
    public function printPdf(Request $request, $competition_id)
    {
        $competition = Competition::with(['category', 'registrations.members'])->findOrFail($competition_id);
        $this->ensureIsBadminton($competition);
        $user = Auth::user();

        if (! in_array($user->role, ['superadmin', 'panitia'])) {
            $managedIds = PicController::getManagedCompetitionIds($user);
            $isAuthorizedBadminton = $user->managesBadminton() && (
                strtoupper($competition->code ?? '') === 'BLT' ||
                str_contains(strtolower($competition->name ?? ''), 'bulu tangkis') ||
                str_contains(strtolower($competition->name ?? ''), 'badminton')
            );

            if (! in_array($competition->id, $managedIds) && ! $isAuthorizedBadminton) {
                abort(403, 'Akses ditolak.');
            }
        }

        $pools = $this->buildCompetitionPools($competition);
        $activePoolKey = $request->query('pool', $pools[0]['key'] ?? 'all');
        $activePool = collect($pools)->firstWhere('key', $activePoolKey) ?? ($pools[0] ?? null);

        $bracketData = null;
        if ($activePool) {
            $bracketData = $this->buildTournamentTree($activePool['participants'], $competition, $activePoolKey);
        }

        $appSettings = AppSetting::pluck('value', 'key')->all();

        return view('pic.bracket-print', compact('competition', 'activePool', 'bracketData', 'appSettings'));
    }

    /**
     * Simpan Pengaturan Format Bagan (Auto BWF vs Play-off Kualifikasi)
     */
    public function saveBracketFormat(Request $request, $competition_id)
    {
        $competition = Competition::findOrFail($competition_id);
        $this->ensureIsBadminton($competition);
        $user = Auth::user();

        if (! in_array($user->role, ['superadmin', 'panitia'])) {
            $managedIds = PicController::getManagedCompetitionIds($user);
            $isAuthorizedBadminton = $user->managesBadminton() && (
                strtoupper($competition->code ?? '') === 'BLT' ||
                str_contains(strtolower($competition->name ?? ''), 'bulu tangkis') ||
                str_contains(strtolower($competition->name ?? ''), 'badminton')
            );

            if (! in_array($competition->id, $managedIds) && ! $isAuthorizedBadminton) {
                return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
            }
        }

        $request->validate([
            'pool_key' => 'required|string',
            'mode' => 'required|in:auto,playoff',
            'target_bracket_size' => 'nullable|integer|in:4,8,16,32,64',
        ]);

        $settings = $competition->bracket_settings ?? [];
        $poolKey = $request->input('pool_key');
        $settings[$poolKey] = [
            'mode' => $request->input('mode', 'auto'),
            'target_bracket_size' => (int) $request->input('target_bracket_size', 32),
            'updated_at' => now()->toIso8601String(),
        ];

        $competition->bracket_settings = $settings;
        $competition->save();

        return response()->json([
            'success' => true,
            'message' => 'Format bagan untuk kategori ini berhasil diperbarui!',
            'settings' => $settings[$poolKey],
        ]);
    }

    /**
     * Simpan Pengaturan Publikasi & Redaksi Standby TV Bagan
     */
    public function updatePublicationSettings(Request $request, $competition_id)
    {
        $competition = Competition::findOrFail($competition_id);
        $this->ensureIsBadminton($competition);
        $user = Auth::user();

        if (! in_array($user->role, ['superadmin', 'panitia'])) {
            $managedIds = PicController::getManagedCompetitionIds($user);
            $isAuthorizedSport = (method_exists($user, 'managesTournamentBracket') && $user->managesTournamentBracket()) && (
                in_array(strtoupper($competition->code ?? ''), ['BLT', 'TMJ']) ||
                str_contains(strtolower($competition->name ?? ''), 'bulu tangkis') ||
                str_contains(strtolower($competition->name ?? ''), 'badminton') ||
                str_contains(strtolower($competition->name ?? ''), 'tenis meja')
            );

            if (! in_array($competition->id, $managedIds) && ! $isAuthorizedSport) {
                return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
            }
        }

        $validated = $request->validate([
            'is_published' => 'required|boolean',
            'standby_title' => 'nullable|string|max:150',
            'standby_message' => 'nullable|string|max:1000',
            'standby_contact' => 'nullable|string|max:150',
        ]);

        $settings = $competition->bracket_settings ?? [];
        $settings['tv_publication'] = [
            'is_published' => (bool) $validated['is_published'],
            'standby_title' => ! empty($validated['standby_title']) ? trim($validated['standby_title']) : 'BAGAN PERTANDINGAN SEDANG DISIAPKAN',
            'standby_message' => ! empty($validated['standby_message']) ? trim($validated['standby_message']) : 'Bagan resmi akan segera dirilis oleh panitia setelah sesi pengundian dan technical meeting selesai.',
            'standby_contact' => ! empty($validated['standby_contact']) ? trim($validated['standby_contact']) : 'Meja Panitia / Sekretariat GOR',
            'updated_at' => now()->toIso8601String(),
            'updated_by' => $user->name ?? 'Admin',
        ];

        $competition->bracket_settings = $settings;
        $competition->save();

        $statusLabel = $settings['tv_publication']['is_published'] ? 'PUBLIK (AKTIF)' : 'STANDBY / KUNCI (OFF)';

        return response()->json([
            'success' => true,
            'message' => "Pengaturan TV Bagan berhasil disimpan! Status: {$statusLabel}",
            'publication' => $settings['tv_publication'],
        ]);
    }

    /**
     * Sinkronkan Pasangan Bagan ke Jadwal Pertandingan Wasit (badminton_matches)
     */
    public function generateMatches(Request $request, $competition_id)
    {
        $competition = Competition::with(['category', 'registrations.members'])->findOrFail($competition_id);
        $this->ensureIsBadminton($competition);
        $user = Auth::user();

        if (! in_array($user->role, ['superadmin', 'panitia'])) {
            $managedIds = PicController::getManagedCompetitionIds($user);
            $isAuthorizedBadminton = $user->managesBadminton() && (
                strtoupper($competition->code ?? '') === 'BLT' ||
                str_contains(strtolower($competition->name ?? ''), 'bulu tangkis') ||
                str_contains(strtolower($competition->name ?? ''), 'badminton')
            );

            if (! in_array($competition->id, $managedIds) && ! $isAuthorizedBadminton) {
                return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
            }
        }

        $poolKey = $request->input('pool_key');
        $pools = $this->buildCompetitionPools($competition);
        $targetPool = collect($pools)->firstWhere('key', $poolKey);

        if (! $targetPool) {
            return response()->json(['success' => false, 'message' => 'Kategori / Pool tidak ditemukan.'], 404);
        }

        $bracketData = $this->buildTournamentTree($targetPool['participants'], $competition, $poolKey);
        if (empty($bracketData['rounds'])) {
            return response()->json(['success' => false, 'message' => 'Peserta belum cukup untuk membuat jadwal.'], 422);
        }

        // Auto-Schedule configuration parameters
        $rawCourts = $request->input('courts', ['Lapangan 1', 'Lapangan 2']);
        if (is_string($rawCourts)) {
            $courts = array_values(array_filter(array_map('trim', explode(',', $rawCourts))));
        } elseif (is_array($rawCourts)) {
            $courts = array_values(array_filter(array_map('trim', $rawCourts)));
        } else {
            $courts = [];
        }
        if (empty($courts)) {
            $courts = ['Lapangan 1', 'Lapangan 2'];
        }

        $tournamentDays = max(1, min(7, (int) $request->input('tournament_days', 4)));
        $rawStartDate = $request->input('start_date');
        $startDate = null;
        if (! empty($rawStartDate)) {
            try {
                $startDate = Carbon::parse($rawStartDate);
            } catch (\Throwable $e) {
                $startDate = null;
            }
        }
        if (! $startDate && ! empty($competition->schedule_date)) {
            try {
                $startDate = Carbon::parse($competition->schedule_date);
            } catch (\Throwable $e) {
                $startDate = null;
            }
        }

        $startTime = $request->input('start_time', '08:30');
        if (! preg_match('/^\d{1,2}:\d{2}$/', $startTime)) {
            $startTime = '08:30';
        }
        $matchDuration = max(10, (int) $request->input('match_duration', 30));

        $totalRounds = count($bracketData['rounds'] ?? []);

        // Helper closures: mapping round to day
        $getDayForRound = function ($roundIndex) use ($totalRounds, $tournamentDays) {
            if ($tournamentDays <= 1) {
                return 1;
            }
            if ($tournamentDays === 2) {
                if ($totalRounds <= 2) {
                    return $roundIndex;
                }

                return ($roundIndex <= 2) ? 1 : 2;
            }
            if ($tournamentDays === 3) {
                if ($totalRounds <= 3) {
                    return min($roundIndex, 3);
                }
                if ($totalRounds === 4) {
                    if ($roundIndex === 1) {
                        return 1;
                    }
                    if ($roundIndex <= 3) {
                        return 2;
                    }

                    return 3;
                }
                // 5 rounds (bracket 32): R1 -> D1, R2 & R3 -> D2, R4 & R5 -> D3
                if ($roundIndex === 1) {
                    return 1;
                }
                if ($roundIndex <= 3) {
                    return 2;
                }

                return 3;
            }
            // 4 Days (default)
            if ($totalRounds >= 5) {
                // R1 (32 besar) -> D1, R2 (16 besar) -> D2, R3 (QF) -> D3, R4 (SF) & R5 (Final) -> D4
                if ($roundIndex === 1) {
                    return 1;
                }
                if ($roundIndex === 2) {
                    return 2;
                }
                if ($roundIndex === 3) {
                    return 3;
                }

                return 4;
            } elseif ($totalRounds === 4) {
                if ($roundIndex === 1) {
                    return 1;
                }
                if ($roundIndex === 2) {
                    return 2;
                }
                if ($roundIndex === 3) {
                    return 3;
                }

                return 4;
            } elseif ($totalRounds === 3) {
                if ($roundIndex === 1) {
                    return 2;
                }
                if ($roundIndex === 2) {
                    return 3;
                }

                return 4;
            }

            return min($roundIndex, $tournamentDays);
        };

        $getDayDateAndLabel = function ($dayNum) use ($startDate) {
            $mDate = null;
            $mLabel = "Hari {$dayNum}";
            if ($startDate) {
                $cDate = $startDate->copy()->addDays($dayNum - 1);
                $mDate = $cDate->format('Y-m-d');
                $mLabel = "Hari {$dayNum} (".$cDate->locale('id')->isoFormat('dddd, D MMM').')';
            }

            return [$mDate, $mLabel];
        };

        // Per-day court tracking counters (each day starts fresh from startTime)
        $courtMatchCountsByDay = [];
        $courtIndexByDay = [];
        for ($d = 1; $d <= max($tournamentDays, 5); $d++) {
            $courtIndexByDay[$d] = 0;
            $courtMatchCountsByDay[$d] = [];
            foreach ($courts as $c) {
                $courtMatchCountsByDay[$d][$c] = 0;
            }
        }

        $syncedCount = 0;
        $categoryCode = stripos($targetPool['title'], 'putri') !== false ? 'WS' : 'MS';
        if (stripos($targetPool['title'], 'ganda') !== false) {
            $categoryCode = stripos($targetPool['title'], 'putri') !== false ? 'WD' : 'MD';
        }

        // 1. Sync Play-off matches if active (always on Day 1 early)
        if (! empty($bracketData['playoffs']['has_playoffs']) && ! empty($bracketData['playoffs']['matches'])) {
            $poCourt = $courts[0] ?? 'Lapangan 1';
            $poEarlyTime = Carbon::createFromFormat('H:i', $startTime)->subMinutes(30)->format('H:i');
            [$poDate, $poDayLabel] = $getDayDateAndLabel(1);

            foreach ($bracketData['playoffs']['matches'] as $poIdx => $poMatch) {
                $poTeam1 = $poMatch['team1'];
                $poTeam2 = $poMatch['team2'];

                if (! $poTeam1 && ! $poTeam2) {
                    continue;
                }
                if (! empty($poTeam1['is_pending_draw']) || ! empty($poTeam2['is_pending_draw'])) {
                    continue;
                }

                $poExisting = BadmintonMatch::where('competition_id', $competition->id)
                    ->where('match_code', $poMatch['match_code'])
                    ->first();

                $poStatus = 'upcoming';
                $poWinnerTeam = null;
                if ($poExisting && in_array($poExisting->match_status, ['ongoing', 'finished'])) {
                    $poStatus = $poExisting->match_status;
                    $poWinnerTeam = $poExisting->winner_team;
                }

                BadmintonMatch::updateOrCreate(
                    [
                        'competition_id' => $competition->id,
                        'match_code' => $poMatch['match_code'],
                    ],
                    [
                        'court_number' => $poExisting?->court_number ?: $poCourt,
                        'scheduled_time' => $poExisting?->scheduled_time ?: $poEarlyTime,
                        'match_order' => $poExisting?->match_order ?? 0,
                        'match_day' => $poExisting?->match_day ?: 1,
                        'match_date' => $poExisting?->match_date ?: $poDate,
                        'match_day_label' => $poExisting?->match_day_label ?: $poDayLabel,
                        'round_name' => 'Play-off Kualifikasi',
                        'category' => $categoryCode,
                        'match_type' => stripos($targetPool['title'], 'ganda') !== false ? 'double' : 'single',
                        'team1_registration_id' => $poTeam1['id'] ?? null,
                        'team1_school' => $poTeam1['institution'] ?? 'TBD',
                        'team1_player1' => $poTeam1['name'] ?? 'Peserta 1',
                        'team2_registration_id' => $poTeam2['id'] ?? null,
                        'team2_school' => $poTeam2['institution'] ?? 'TBD',
                        'team2_player1' => $poTeam2['name'] ?? 'Peserta 2',
                        'match_status' => $poStatus,
                        'winner_team' => $poWinnerTeam,
                    ]
                );

                $syncedCount++;
            }
        }

        foreach ($bracketData['rounds'] as $round) {
            $roundIndex = (int) ($round['round_index'] ?? 1);
            $roundName = $round['round_name'];
            $mDay = $getDayForRound($roundIndex);
            [$mDate, $mDayLabel] = $getDayDateAndLabel($mDay);

            foreach ($round['matches'] as $match) {
                $team1 = $match['team1'];
                $team2 = $match['team2'];

                // Skip if both are empty or any team is pending draw
                if (! $team1 && ! $team2) {
                    continue;
                }

                if (! empty($team1['is_pending_draw']) || ! empty($team2['is_pending_draw'])) {
                    continue;
                }

                $isBye1 = ! empty($match['is_bye1']);
                $isBye2 = ! empty($match['is_bye2']);
                $isContested = (! $isBye1 && ! $isBye2);

                // If one team is BYE, mark as finished with winner
                $status = 'upcoming';
                $winnerTeam = null;

                if ($team1 && ! $isBye1 && $isBye2) {
                    $status = 'finished';
                    $winnerTeam = 1;
                } elseif ($team2 && ! $isBye2 && $isBye1) {
                    $status = 'finished';
                    $winnerTeam = 2;
                }

                $assignedCourt = 'Lapangan 1';
                $assignedTime = null;
                $assignedOrder = null;

                if ($isContested) {
                    $assignedCourt = $courts[$courtIndexByDay[$mDay] % count($courts)];
                    $courtMatchCountsByDay[$mDay][$assignedCourt]++;
                    $assignedOrder = $courtMatchCountsByDay[$mDay][$assignedCourt];

                    $minutesToAdd = ($assignedOrder - 1) * $matchDuration;
                    $assignedTime = Carbon::createFromFormat('H:i', $startTime)->addMinutes($minutesToAdd)->format('H:i');

                    $courtIndexByDay[$mDay]++;
                } else {
                    $assignedCourt = 'BYE';
                }

                $existingMatchRecord = BadmintonMatch::where('competition_id', $competition->id)
                    ->where('match_code', $match['match_code'])
                    ->first();

                if ($existingMatchRecord && in_array($existingMatchRecord->match_status, ['ongoing', 'finished'])) {
                    $status = $existingMatchRecord->match_status;
                    $winnerTeam = $existingMatchRecord->winner_team;
                }

                BadmintonMatch::updateOrCreate(
                    [
                        'competition_id' => $competition->id,
                        'match_code' => $match['match_code'],
                    ],
                    [
                        'court_number' => $assignedCourt,
                        'scheduled_time' => $assignedTime,
                        'match_order' => $assignedOrder,
                        'match_day' => $mDay,
                        'match_date' => $mDate,
                        'match_day_label' => $mDayLabel,
                        'round_name' => $roundName,
                        'category' => $categoryCode,
                        'match_type' => stripos($targetPool['title'], 'ganda') !== false ? 'double' : 'single',
                        'team1_registration_id' => $team1['id'] ?? null,
                        'team1_school' => $team1['institution'] ?? ($isBye1 ? 'BYE' : 'TBD'),
                        'team1_player1' => $team1['name'] ?? ($isBye1 ? '[BYE]' : 'Menunggu Pemenang'),
                        'team2_registration_id' => $team2['id'] ?? null,
                        'team2_school' => $team2['institution'] ?? ($isBye2 ? 'BYE' : 'TBD'),
                        'team2_player1' => $team2['name'] ?? ($isBye2 ? '[BYE]' : 'Menunggu Pemenang'),
                        'match_status' => $status,
                        'winner_team' => $winnerTeam,
                    ]
                );

                $syncedCount++;
            }
        }

        $courtListStr = implode(', ', $courts);

        return response()->json([
            'success' => true,
            'message' => "Berhasil menyinkronkan {$syncedCount} pertandingan ke jadwal {$tournamentDays} Hari ({$courtListStr}, mulai {$startTime} WIB)!",
        ]);
    }

    /**
     * Update cepat jadwal lapangan, waktu dan nomor partai per pertandingan (AJAX)
     */
    public function updateMatchSchedule(Request $request, $competition_id)
    {
        $competition = Competition::findOrFail($competition_id);
        $this->ensureIsBadminton($competition);
        $user = Auth::user();

        if (! in_array($user->role, ['superadmin', 'panitia'])) {
            $managedIds = PicController::getManagedCompetitionIds($user);
            $isAuthorizedBadminton = $user->managesBadminton() && (
                strtoupper($competition->code ?? '') === 'BLT' ||
                str_contains(strtolower($competition->name ?? ''), 'bulu tangkis') ||
                str_contains(strtolower($competition->name ?? ''), 'badminton')
            );

            if (! in_array($competition->id, $managedIds) && ! $isAuthorizedBadminton) {
                return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
            }
        }

        $request->validate([
            'match_code' => 'required|string',
            'court_number' => 'nullable|string|max:50',
            'scheduled_time' => 'nullable|string|max:10',
            'match_order' => 'nullable|integer|min:1|max:999',
            'match_day' => 'nullable|integer|min:1|max:7',
            'match_date' => 'nullable|date',
        ]);

        $match = BadmintonMatch::firstOrNew([
            'competition_id' => $competition->id,
            'match_code' => $request->input('match_code'),
        ]);

        $match->court_number = $request->input('court_number') ?: ($match->court_number ?: 'Lapangan 1');
        $match->scheduled_time = $request->input('scheduled_time');
        $match->match_order = $request->input('match_order');

        if ($request->filled('match_day')) {
            $match->match_day = (int) $request->input('match_day');
            $match->match_day_label = "Hari {$match->match_day}";
            if ($request->filled('match_date')) {
                try {
                    $cd = Carbon::parse($request->input('match_date'));
                    $match->match_date = $cd->format('Y-m-d');
                    $match->match_day_label .= ' ('.$cd->locale('id')->isoFormat('dddd, D MMM').')';
                } catch (\Throwable $e) {
                }
            }
        } elseif ($request->filled('match_date')) {
            try {
                $cd = Carbon::parse($request->input('match_date'));
                $match->match_date = $cd->format('Y-m-d');
            } catch (\Throwable $e) {
            }
        }

        if (! $match->exists) {
            $match->round_name = $request->input('round_name', 'Babak 1');
            $match->category = $request->input('category', 'MS');
            $match->match_type = $request->input('match_type', 'single');
            $match->team1_school = $request->input('team1_school', 'TBD');
            $match->team1_player1 = $request->input('team1_player1', 'TBD');
            $match->team2_school = $request->input('team2_school', 'TBD');
            $match->team2_player1 = $request->input('team2_player1', 'TBD');
            $match->match_status = 'upcoming';
        }

        $match->save();

        return response()->json([
            'success' => true,
            'message' => "Jadwal pertandingan {$match->match_code} ({$match->court_number} • {$match->match_day_label}) berhasil disimpan!",
            'data' => [
                'match_code' => $match->match_code,
                'court_number' => $match->court_number,
                'scheduled_time' => $match->scheduled_time,
                'match_order' => $match->match_order,
                'match_day' => $match->match_day,
                'match_date' => $match->match_date?->format('Y-m-d'),
                'match_day_label' => $match->match_day_label,
            ],
        ]);
    }

    /**
     * Membangun Pohon Bagan Turnamen Berdasarkan Standar BWF
     */
    public function buildTournamentTree(array $poolParticipants, Competition $competition, string $poolKey = 'pool'): array
    {
        $settings = $competition->bracket_settings[$poolKey] ?? null;
        $bracketMode = $settings['mode'] ?? 'auto';
        $targetBracketSize = (int) ($settings['target_bracket_size'] ?? 32);

        $total = count($poolParticipants);
        $isPlayoff = false;
        $surplus = 0;
        $numPlayoffs = 0;

        if ($bracketMode === 'playoff' && $targetBracketSize > 0 && $total > $targetBracketSize) {
            $bracketSize = $targetBracketSize;
            $isPlayoff = true;
            $surplus = $total - $bracketSize;
            $numPlayoffs = $surplus;
        } else {
            if ($total <= 2) {
                $bracketSize = 2;
            } elseif ($total <= 4) {
                $bracketSize = 4;
            } elseif ($total <= 8) {
                $bracketSize = 8;
            } elseif ($total <= 16) {
                $bracketSize = 16;
            } elseif ($total <= 32) {
                $bracketSize = 32;
            } else {
                $bracketSize = 64;
            }
        }

        $totalRounds = (int) log($bracketSize, 2);
        $totalByes = $isPlayoff ? 0 : max(0, $bracketSize - $total);

        // BWF Seed Slots (Standard Tournament Placements)
        $seedSlots = [
            1 => 1,
            2 => $bracketSize,
            3 => (int) ($bracketSize / 2) + 1,
            4 => (int) ($bracketSize / 2),
            5 => (int) ($bracketSize / 4) + 1,
            6 => (int) (3 * $bracketSize / 4),
            7 => (int) (3 * $bracketSize / 4) + 1,
            8 => (int) ($bracketSize / 4),
        ];

        if ($bracketSize >= 16) {
            $seedSlots += [
                9 => (int) ($bracketSize / 8) + 1,
                10 => (int) (7 * $bracketSize / 8),
                11 => (int) (5 * $bracketSize / 8) + 1,
                12 => (int) (3 * $bracketSize / 8),
                13 => (int) (3 * $bracketSize / 8) + 1,
                14 => (int) (5 * $bracketSize / 8),
                15 => (int) (7 * $bracketSize / 8) + 1,
                16 => (int) ($bracketSize / 8),
            ];
        }

        $slots = array_fill(1, $bracketSize, null);

        // Priority slots for BYEs (paired with seeds 1..8) if not playoff
        if (! $isPlayoff && $totalByes > 0) {
            $byePrioritySlots = [
                2,
                $bracketSize - 1,
                (int) ($bracketSize / 2) + 2,
                (int) ($bracketSize / 2) - 1,
                (int) ($bracketSize / 4) + 2,
                (int) (3 * $bracketSize / 4) - 1,
                (int) (3 * $bracketSize / 4) + 2,
                (int) ($bracketSize / 4) - 1,
            ];

            for ($i = 4; $i <= $bracketSize; $i += 2) {
                if (! in_array($i, $byePrioritySlots, true) && ! in_array($i - 1, $byePrioritySlots, true)) {
                    $byePrioritySlots[] = $i;
                }
            }

            $assignedByes = array_slice($byePrioritySlots, 0, $totalByes);

            foreach ($assignedByes as $bs) {
                $slots[$bs] = [
                    'is_bye' => true,
                    'is_pending_draw' => false,
                    'name' => '[BYE]',
                    'institution' => 'Bebas Babak 1',
                    'id' => null,
                    'draw_number' => null,
                    'seed_number' => null,
                    'slot_number' => $bs,
                ];
            }
        }

        // Separate Seeded vs Unseeded participants
        $seededParticipants = [];
        $unseededParticipants = [];

        foreach ($poolParticipants as $p) {
            $seedNum = $p['seed_number'] ?? null;
            if (! empty($seedNum) && isset($seedSlots[$seedNum])) {
                $seededParticipants[] = $p;
            } else {
                $unseededParticipants[] = $p;
            }
        }

        // Place seeded participants first into their official BWF slots
        foreach ($seededParticipants as $p) {
            $targetSlot = $seedSlots[$p['seed_number']];
            $p['slot_number'] = $targetSlot;
            $p['is_pending_draw'] = false;
            $slots[$targetSlot] = $p;
        }

        // Fetch existing BadmintonMatch records for this competition to overlay live scores & match statuses
        $existingMatches = collect();
        if (! empty($competition->id)) {
            try {
                $existingMatches = BadmintonMatch::where('competition_id', $competition->id)
                    ->where('match_code', 'like', "{$poolKey}-%")
                    ->get()
                    ->keyBy('match_code');
            } catch (\Throwable $e) {
                $existingMatches = collect();
            }
        }

        $drawnUnseeded = [];
        $undrawnUnseeded = [];
        foreach ($unseededParticipants as $p) {
            if (! empty($p['draw_number'])) {
                $drawnUnseeded[] = $p;
            } else {
                $undrawnUnseeded[] = $p;
            }
        }
        usort($drawnUnseeded, fn ($a, $b) => ((int) ($a['draw_number'] ?? 999)) <=> ((int) ($b['draw_number'] ?? 999)));

        $playoffMatchesList = [];

        if ($isPlayoff) {
            $poNeeded = $numPlayoffs * 2;
            $poTargetSlots = [
                1 => $bracketSize,
                2 => (int) ($bracketSize / 2),
                3 => (int) (3 * $bracketSize / 4),
                4 => (int) ($bracketSize / 4),
            ];

            // If drawn participants >= $poNeeded, the highest drawn numbers enter play-off
            if (count($drawnUnseeded) >= $poNeeded) {
                $directDrawn = array_slice($drawnUnseeded, 0, count($drawnUnseeded) - $poNeeded);
                $poPlayers = array_slice($drawnUnseeded, -$poNeeded);
            } else {
                $allUnseededCombined = array_merge($drawnUnseeded, $undrawnUnseeded);
                $directDrawn = array_slice($allUnseededCombined, 0, max(0, count($allUnseededCombined) - $poNeeded));
                $poPlayers = array_slice($allUnseededCombined, -$poNeeded);
            }

            for ($po = 1; $po <= $numPlayoffs; $po++) {
                $poMatchCode = "{$poolKey}-PO-M{$po}";
                $targetSlot = $poTargetSlots[$po] ?? ($bracketSize - ($po - 1));

                $p1 = $poPlayers[($po - 1) * 2] ?? null;
                $p2 = $poPlayers[($po - 1) * 2 + 1] ?? null;

                if (! $p1 || empty($p1['name'])) {
                    $p1 = [
                        'id' => null,
                        'name' => "[Undian Play-off #{$po}-1]",
                        'institution' => 'Menunggu Undian',
                        'is_pending_draw' => true,
                    ];
                }
                if (! $p2 || empty($p2['name'])) {
                    $p2 = [
                        'id' => null,
                        'name' => "[Undian Play-off #{$po}-2]",
                        'institution' => 'Menunggu Undian',
                        'is_pending_draw' => true,
                    ];
                }

                $poExisting = $existingMatches->get($poMatchCode);
                $poWinner = null;
                $poStatus = 'upcoming';

                if ($poExisting && $poExisting->match_status === 'finished') {
                    $poStatus = 'finished';
                    $poWinner = ($poExisting->winner_team === 1) ? $p1 : (($poExisting->winner_team === 2) ? $p2 : null);
                } elseif ($poExisting && $poExisting->match_status === 'ongoing') {
                    $poStatus = 'ongoing';
                } elseif (! empty($p1['is_pending_draw']) || ! empty($p2['is_pending_draw'])) {
                    $poStatus = 'pending_draw';
                }

                $playoffMatchesList[] = [
                    'match_code' => $poMatchCode,
                    'playoff_index' => $po,
                    'target_slot' => $targetSlot,
                    'round_name' => 'Play-off Kualifikasi',
                    'team1' => $p1,
                    'team2' => $p2,
                    'winner' => $poWinner,
                    'status' => $poStatus,
                    'existing_match' => $poExisting,
                ];

                // Populate Target Slot in Main Draw
                if ($poWinner) {
                    $slots[$targetSlot] = array_merge($poWinner, [
                        'slot_number' => $targetSlot,
                        'is_bye' => false,
                        'is_pending_draw' => false,
                        'is_playoff_winner' => true,
                        'playoff_match_code' => $poMatchCode,
                        'playoff_label' => "Pemenang Play-off #{$po}",
                    ]);
                } else {
                    $poVersusText = (empty($p1['is_pending_draw']) && empty($p2['is_pending_draw']))
                        ? ($p1['name'].' vs '.$p2['name'])
                        : "Partai Play-off #{$po}";

                    $slots[$targetSlot] = [
                        'id' => null,
                        'name' => "[Pemenang Play-off #{$po}]",
                        'institution' => $poVersusText,
                        'is_bye' => false,
                        'is_pending_draw' => false,
                        'is_playoff_slot' => true,
                        'slot_number' => $targetSlot,
                        'playoff_match_code' => $poMatchCode,
                        'draw_number' => null,
                        'seed_number' => null,
                    ];
                }
            }

            $bwfProtections = $this->assignSlotsWithBwfSeparation($slots, $directDrawn, $bracketSize);
        } else {
            $bwfProtections = $this->assignSlotsWithBwfSeparation($slots, $drawnUnseeded, $bracketSize);
        }

        $roundNames = [
            1 => $bracketSize === 8 ? 'Perempat Final' : ($bracketSize === 16 ? 'Babak 16 Besar' : ($bracketSize === 32 ? 'Babak 32 Besar' : 'Babak 1')),
            2 => $bracketSize === 8 ? 'Semifinal' : ($bracketSize === 16 ? 'Perempat Final' : ($bracketSize === 32 ? 'Babak 16 Besar' : 'Babak 2')),
            3 => $bracketSize === 8 ? 'Final' : ($bracketSize === 16 ? 'Semifinal' : ($bracketSize === 32 ? 'Perempat Final' : 'Babak 3')),
            4 => $bracketSize === 16 ? 'Final' : ($bracketSize === 32 ? 'Semifinal' : 'Babak 4'),
            5 => 'Final',
        ];

        $rounds = [];

        // Round 1
        $numR1Matches = (int) ($bracketSize / 2);
        $r1Matches = [];
        for ($m = 1; $m <= $numR1Matches; $m++) {
            $slot1 = ($m * 2) - 1;
            $slot2 = $m * 2;

            $p1 = $slots[$slot1] ?? null;
            $p2 = $slots[$slot2] ?? null;

            $isBye1 = ! empty($p1['is_bye']);
            $isBye2 = ! empty($p2['is_bye']);
            $isPending1 = ! empty($p1['is_pending_draw']);
            $isPending2 = ! empty($p2['is_pending_draw']);

            $matchCode = "{$poolKey}-R1-M{$m}";
            $existingMatch = $existingMatches->get($matchCode);

            $winner = null;
            $status = 'upcoming';

            if ($existingMatch && $existingMatch->match_status === 'finished') {
                $status = 'finished';
                $winner = $existingMatch->winner_team === 1 ? $p1 : ($existingMatch->winner_team === 2 ? $p2 : null);
            } elseif ($p1 && ! $isBye1 && ! $isPending1 && $isBye2) {
                $winner = $p1;
                $status = 'bye_advance';
            } elseif ($p2 && ! $isBye2 && ! $isPending2 && $isBye1) {
                $winner = $p2;
                $status = 'bye_advance';
            } elseif ($existingMatch && $existingMatch->match_status === 'ongoing') {
                $status = 'ongoing';
            } elseif ($isPending1 || $isPending2) {
                $status = 'pending_draw';
            }

            $hasBwfProtection = ! empty($p1['is_bwf_separated']) || ! empty($p2['is_bwf_separated']);
            $bwfNote = $p1['bwf_note'] ?? ($p2['bwf_note'] ?? null);

            $r1Matches[$m] = [
                'match_code' => $matchCode,
                'round_index' => 1,
                'match_index' => $m,
                'slot1' => $slot1,
                'slot2' => $slot2,
                'team1' => $p1,
                'team2' => $p2,
                'is_bye1' => $isBye1,
                'is_bye2' => $isBye2,
                'winner' => $winner,
                'status' => $status,
                'existing_match' => $existingMatch,
                'has_bwf_protection' => $hasBwfProtection,
                'bwf_note' => $bwfNote,
            ];
        }

        $rounds[1] = [
            'round_index' => 1,
            'round_name' => $roundNames[1] ?? 'Babak 1',
            'matches' => array_values($r1Matches),
        ];

        // Subsequent rounds
        for ($r = 2; $r <= $totalRounds; $r++) {
            $prevMatches = $rounds[$r - 1]['matches'];
            $numMatches = (int) (count($prevMatches) / 2);
            $currentMatches = [];

            for ($m = 1; $m <= $numMatches; $m++) {
                $mIdx1 = ($m * 2) - 1;
                $mIdx2 = $m * 2;

                $prevMatch1 = $prevMatches[$mIdx1 - 1] ?? null;
                $prevMatch2 = $prevMatches[$mIdx2 - 1] ?? null;

                $t1 = $prevMatch1['winner'] ?? null;
                $t2 = $prevMatch2['winner'] ?? null;

                $matchCode = "{$poolKey}-R{$r}-M{$m}";
                $existingMatch = $existingMatches->get($matchCode);

                $winner = null;
                $status = 'upcoming';

                if ($existingMatch && $existingMatch->match_status === 'finished') {
                    $status = 'finished';
                    $winner = $existingMatch->winner_team === 1 ? $t1 : ($existingMatch->winner_team === 2 ? $t2 : null);
                } elseif ($existingMatch && $existingMatch->match_status === 'ongoing') {
                    $status = 'ongoing';
                }

                $currentMatches[] = [
                    'match_code' => $matchCode,
                    'round_index' => $r,
                    'match_index' => $m,
                    'team1' => $t1,
                    'team2' => $t2,
                    'winner' => $winner,
                    'status' => $status,
                    'existing_match' => $existingMatch,
                ];
            }

            $rounds[$r] = [
                'round_index' => $r,
                'round_name' => $roundNames[$r] ?? "Babak {$r}",
                'matches' => $currentMatches,
            ];
        }

        // Champion
        $finalRound = end($rounds);
        $finalMatch = $finalRound['matches'][0] ?? null;
        $champion = $finalMatch['winner'] ?? null;

        $isDoublesPool = str_contains(strtolower($poolKey), 'ganda') || collect($poolParticipants)->contains(fn ($p) => str_contains($p['name'] ?? '', ' / '));

        $bracketData = [
            'bracket_size' => $bracketSize,
            'is_doubles' => $isDoublesPool,
            'total_participants' => $total,
            'total_byes' => $totalByes,
            'total_rounds' => $totalRounds,
            'rounds' => $rounds,
            'champion' => $champion,
            'bwf_protections' => $bwfProtections,
            'has_bwf_protections' => count($bwfProtections) > 0,
            'playoffs' => [
                'has_playoffs' => $isPlayoff,
                'mode' => $bracketMode,
                'target_bracket_size' => $bracketSize,
                'surplus' => $surplus,
                'num_playoffs' => $numPlayoffs,
                'matches' => $playoffMatchesList,
            ],
        ];

        $bracketData['classic_svg_light'] = $this->renderClassicBracketSvg($bracketData, ['isDark' => false]);
        $bracketData['classic_svg_dark'] = $this->renderClassicBracketSvg($bracketData, ['isDark' => true]);

        return $bracketData;
    }

    /**
     * Alokasi Slot Terbuka Mengikuti Regulasi Resmi BWF GCR Pasal 14 (Separation of Entries)
     * 1. Dua atlet dari sekolah yang sama TIDAK BOLEH bertemu di Babak 1 (Match yang sama).
     * 2. Dua atlet dari sekolah yang sama dipisahkan ke Pool Atas (Top Half) dan Pool Bawah (Bottom Half).
     * 3. Tiga atau empat atlet dari sekolah yang sama disebar ke 4 Kuadran (Quarters) berbeda.
     */
    protected function assignSlotsWithBwfSeparation(array &$slots, array $drawnUnseeded, int $bracketSize): array
    {
        $placedSchools = [];
        for ($s = 1; $s <= $bracketSize; $s++) {
            if (! empty($slots[$s]) && empty($slots[$s]['is_bye']) && ! empty($slots[$s]['institution'])) {
                $school = trim(mb_strtolower($slots[$s]['institution']));
                $placedSchools[$school][] = $s;
            }
        }

        $bwfProtections = [];

        foreach ($drawnUnseeded as $p) {
            $school = trim(mb_strtolower($p['institution'] ?? ''));
            $openSlots = [];
            for ($s = 1; $s <= $bracketSize; $s++) {
                if ($slots[$s] === null) {
                    $openSlots[] = $s;
                }
            }

            if (empty($openSlots)) {
                break;
            }

            $existingSlots = $placedSchools[$school] ?? [];
            $existingInTop = 0;
            $existingInBottom = 0;
            $existingInQuarter = [1 => 0, 2 => 0, 3 => 0, 4 => 0];

            foreach ($existingSlots as $es) {
                if ($es <= $bracketSize / 2) {
                    $existingInTop++;
                } else {
                    $existingInBottom++;
                }
                $q = (int) ceil($es / max(1, $bracketSize / 4));
                $existingInQuarter[$q] = ($existingInQuarter[$q] ?? 0) + 1;
            }

            $bestSlot = null;
            $bestPenalty = PHP_INT_MAX;

            foreach ($openSlots as $s) {
                $penalty = 0;

                // Aturan 1 (Mutlak): Proteksi bentrok sesama sekolah di Babak 1
                $partnerSlot = ($s % 2 === 1) ? $s + 1 : $s - 1;
                $partner = $slots[$partnerSlot] ?? null;
                if ($partner && empty($partner['is_bye']) && ! empty($partner['institution'])) {
                    $partnerSchool = trim(mb_strtolower($partner['institution']));
                    if (! empty($school) && $partnerSchool === $school) {
                        $penalty += 100000; // Penalti sangat tinggi agar tidak pernah berhadapan di R1
                    }
                }

                // Aturan 2: Keseimbangan Pool Atas (Top Half) vs Pool Bawah (Bottom Half)
                if (! empty($school) && count($existingSlots) > 0) {
                    $candidateHalf = ($s <= $bracketSize / 2) ? 'top' : 'bottom';
                    if ($candidateHalf === 'top' && $existingInTop > $existingInBottom) {
                        $penalty += 1000 * ($existingInTop - $existingInBottom);
                    } elseif ($candidateHalf === 'bottom' && $existingInBottom > $existingInTop) {
                        $penalty += 1000 * ($existingInBottom - $existingInTop);
                    }

                    // Aturan 3: Distribusi Kuadran (Quarters) jika peserta >= 3
                    $q = (int) ceil($s / max(1, $bracketSize / 4));
                    $penalty += 100 * ($existingInQuarter[$q] ?? 0);
                }

                // Penentu urutan: utamakan slot terendah yang aman
                $penalty += $s;

                if ($penalty < $bestPenalty) {
                    $bestPenalty = $penalty;
                    $bestSlot = $s;
                }
            }

            if ($bestSlot !== null) {
                $isSeparated = false;
                $bwfNote = null;
                if (! empty($school) && count($existingSlots) > 0) {
                    $isSeparated = true;
                    $halfName = $bestSlot <= ($bracketSize / 2) ? 'Pool Atas' : 'Pool Bawah';
                    $bwfNote = "Proteksi BWF GCR 14: Pemisahan satu delegasi {$p['institution']} ke {$halfName} (Slot #{$bestSlot})";
                    $bwfProtections[] = [
                        'participant_name' => $p['name'],
                        'institution' => $p['institution'],
                        'slot' => $bestSlot,
                        'teammate_slots' => $existingSlots,
                        'message' => "Peserta dari {$p['institution']} ({$p['name']}) dialokasikan ke {$halfName} (Slot #{$bestSlot}) sesuai aturan BWF GCR 14 tentang proteksi satu delegasi.",
                    ];
                }

                $p['slot_number'] = $bestSlot;
                $p['is_pending_draw'] = false;
                $p['is_bwf_separated'] = $isSeparated;
                $p['bwf_note'] = $bwfNote;
                $slots[$bestSlot] = $p;

                if (! empty($school)) {
                    $placedSchools[$school][] = $bestSlot;
                }
            }
        }

        // Isi sisa slot yang belum diundi sebagai pending draw
        for ($s = 1; $s <= $bracketSize; $s++) {
            if ($slots[$s] === null) {
                $slots[$s] = [
                    'is_bye' => false,
                    'is_pending_draw' => true,
                    'name' => '[Menunggu Undian]',
                    'institution' => 'Slot #'.$s,
                    'id' => null,
                    'draw_number' => null,
                    'seed_number' => null,
                    'slot_number' => $s,
                ];
            }
        }

        return $bwfProtections;
    }

    /**
     * Render Bagan Turnamen Klasik (Format Garis Cabang Tradisional PBSI/BWF)
     * Persis seperti format papan bagan resmi di GOR
     */
    public function renderClassicBracketSvg($bracketData, $options = [])
    {
        $bracketSize = $bracketData['bracket_size'] ?? 16;
        $totalRounds = $bracketData['total_rounds'] ?? (int) log($bracketSize, 2);
        $rounds = $bracketData['rounds'] ?? [];
        $playoffs = $bracketData['playoffs'] ?? null;
        $hasPlayoffs = ! empty($playoffs['has_playoffs']) && ! empty($playoffs['matches']);

        // Extract slots from Round 1 early to detect if doubles
        $r1Matches = $rounds[1]['matches'] ?? [];
        $slots = [];
        $isDoublesBracket = ! empty($bracketData['is_doubles']);

        foreach ($r1Matches as $mIdx => $m) {
            $s1 = ($mIdx * 2) + 1;
            $s2 = ($mIdx * 2) + 2;
            $slots[$s1] = $m['team1'] ?? ['name' => '', 'slot_number' => $s1];
            $slots[$s2] = $m['team2'] ?? ['name' => '', 'slot_number' => $s2];

            if (! $isDoublesBracket) {
                if ((! empty($slots[$s1]['name']) && str_contains($slots[$s1]['name'], ' / ')) ||
                    (! empty($slots[$s2]['name']) && str_contains($slots[$s2]['name'], ' / '))) {
                    $isDoublesBracket = true;
                }
            }
        }

        $isDark = $options['isDark'] ?? false;

        // Dynamic geometry based on whether category is doubles and bracket size
        if ($isDoublesBracket) {
            // Doubles: 2 player lines + 1 school line per slot
            $defaultSlotHeight = ($bracketSize > 16 ? 44 : ($bracketSize > 8 ? 50 : 58));
            $defaultSlotWidth = ($bracketSize > 16 ? 380 : 420);
            $defaultBranchWidth = ($bracketSize > 16 ? 180 : 210);
            $champBoxWidth = 220;
            $champBoxHeight = 52;
        } else {
            // Singles: 1 player line + 1 school line per slot
            $defaultSlotHeight = ($bracketSize > 16 ? 36 : ($bracketSize > 8 ? 44 : 52));
            $defaultSlotWidth = ($bracketSize > 16 ? 340 : 380);
            $defaultBranchWidth = ($bracketSize > 16 ? 160 : 185);
            $champBoxWidth = 180;
            $champBoxHeight = 42;
        }

        $slotHeight = $options['slotHeight'] ?? $defaultSlotHeight;
        $slotWidth = $options['slotWidth'] ?? $defaultSlotWidth;
        $branchWidth = $options['branchWidth'] ?? $defaultBranchWidth;

        $poWidth = $hasPlayoffs ? ($isDoublesBracket ? 270 : 230) : 0;
        $leftMargin = ($options['leftMargin'] ?? 48) + $poWidth;
        $topMargin = $options['topMargin'] ?? 52;

        $strokeColor = $isDark ? '#64748b' : '#0f172a';
        $strokeWidth = '1.8';
        $textColor = $isDark ? '#f8fafc' : '#0f172a';
        $subTextColor = $isDark ? '#94a3b8' : '#64748b';
        $boxBg = $isDark ? '#1e293b' : '#ffffff';
        $byeBoxBg = $isDark ? '#0f172a' : '#f8fafc';
        $accentColor = '#d97706';

        $champLineLength = 40;
        $rightPadding = 40;

        $totalHeight = ($bracketSize * $slotHeight) + $topMargin + 40;
        $totalWidth = $leftMargin + $slotWidth + ($totalRounds * $branchWidth) + $champLineLength + $champBoxWidth + $rightPadding;

        $svg = [];
        $svg[] = "<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 {$totalWidth} {$totalHeight}' preserveAspectRatio='xMidYMid meet' width='100%' height='100%' style='display: block; width: 100%; height: 100%; max-width: 100%; max-height: 100%; object-fit: contain; font-family: system-ui, -apple-system, sans-serif;'>";

        // 0. Play-off Column Header (if active)
        if ($hasPlayoffs) {
            $poHeaderX = $leftMargin - ($poWidth / 2);
            $svg[] = "<text x='{$poHeaderX}' y='26' text-anchor='middle' font-size='12' font-weight='800' fill='{$accentColor}'>Play-off Kualifikasi</text>";
            $svg[] = "<text x='{$poHeaderX}' y='40' text-anchor='middle' font-size='9.5' font-weight='600' fill='{$subTextColor}'>Memperebutkan Slot #{$bracketSize}</text>";
        }

        // 1. Column Headers (Main ke -1, Main ke -2, dst.)
        $headerX = $leftMargin + ($slotWidth / 2);
        for ($r = 1; $r <= $totalRounds; $r++) {
            $rName = "Main ke -{$r}";
            $svg[] = "<text x='{$headerX}' y='26' text-anchor='middle' font-size='13' font-weight='800' fill='{$textColor}'>{$rName}</text>";

            $roundSub = $rounds[$r]['round_name'] ?? '';
            if ($roundSub) {
                $svg[] = "<text x='{$headerX}' y='40' text-anchor='middle' font-size='9.5' font-weight='600' fill='{$subTextColor}'>({$roundSub})</text>";
            }

            $headerX += ($r === 1 ? ($slotWidth / 2 + $branchWidth / 2) : $branchWidth);
        }
        // Juara 1 Header (tepat di atas kotak Juara 1)
        $champHeaderX = $leftMargin + $slotWidth + ($totalRounds * $branchWidth) + $champLineLength + ($champBoxWidth / 2);
        $svg[] = "<text x='{$champHeaderX}' y='26' text-anchor='middle' font-size='13' font-weight='800' fill='{$accentColor}'>Juara 1</text>";

        // 2. Draw Slot Numbers and Boxes for Round 1
        $slotX = $leftMargin;
        $slotYPositions = [];

        for ($s = 1; $s <= $bracketSize; $s++) {
            $slotData = $slots[$s] ?? ['name' => '', 'slot_number' => $s];
            $isBye = $slotData['is_bye'] ?? false;
            $isPending = $slotData['is_pending_draw'] ?? false;
            $isPlayoffSlot = ! empty($slotData['is_playoff_slot']);
            $isPlayoffWinner = ! empty($slotData['is_playoff_winner']);

            $yCenter = $topMargin + ($s - 0.5) * $slotHeight;
            $slotYPositions[$s] = $yCenter;

            $boxY = $yCenter - ($slotHeight * 0.44);
            $boxH = $slotHeight * 0.88;

            // Slot Number (1, 2, ..., 32)
            $svg[] = "<text x='".($slotX - 10)."' y='".($yCenter + 4.5)."' text-anchor='end' font-size='11.5' font-weight='800' fill='{$subTextColor}'>{$s}</text>";

            // Rectangle Box
            if ($isPlayoffSlot) {
                $currentBoxBg = $isDark ? '#2a1b08' : '#fffbeb';
                $currentBorder = '#f59e0b';
                $dashAttr = "stroke-dasharray='4 2'";
            } elseif ($isPlayoffWinner) {
                $currentBoxBg = $isDark ? '#064e3b' : '#ecfdf5';
                $currentBorder = '#059669';
                $dashAttr = '';
            } elseif ($isPending) {
                $currentBoxBg = $isDark ? '#090d16' : '#f8fafc';
                $currentBorder = $isDark ? '#334155' : '#cbd5e1';
                $dashAttr = "stroke-dasharray='3 3'";
            } elseif ($isBye) {
                $currentBoxBg = $byeBoxBg;
                $currentBorder = '#94a3b8';
                $dashAttr = "stroke-dasharray='4 2'";
            } else {
                $currentBoxBg = $boxBg;
                $currentBorder = $strokeColor;
                $dashAttr = '';
            }
            $svg[] = "<rect x='{$slotX}' y='{$boxY}' width='{$slotWidth}' height='{$boxH}' fill='{$currentBoxBg}' stroke='{$currentBorder}' stroke-width='1.5' rx='4' {$dashAttr}/>";

            // Player Text & Institution (Clean multi-line layout without truncation)
            if ($isPlayoffSlot) {
                $displayText = htmlspecialchars($slotData['name'] ?? '[Pemenang Play-off]', ENT_QUOTES);
                $nameColor = '#d97706';
                $svg[] = "<text x='".($slotX + 12)."' y='".($yCenter + 4.5)."' font-size='11.5' font-weight='800' fill='{$nameColor}'>{$displayText}</text>";
            } elseif ($isPending) {
                $displayText = '[Menunggu Undian]';
                $nameColor = $isDark ? '#475569' : '#94a3b8';
                $svg[] = "<text x='".($slotX + 12)."' y='".($yCenter + 4.5)."' font-size='11.5' font-style='italic' font-weight='600' fill='{$nameColor}'>{$displayText}</text>";
            } elseif ($isBye) {
                $displayText = '[BYE] Bebas Babak 1';
                $byeSize = ($bracketSize > 16) ? '11.5' : '13';
                $byeColor = $isDark ? '#94a3b8' : '#334155';
                $svg[] = "<text x='".($slotX + 12)."' y='".($yCenter + 4.5)."' font-size='{$byeSize}' font-weight='800' fill='{$byeColor}'>{$displayText}</text>";
            } else {
                $nameText = $slotData['name'] ?? '';
                $seedText = ! empty($slotData['seed_number']) ? "(S{$slotData['seed_number']}) " : '';
                $poBadge = $isPlayoffWinner ? '[PO] ' : '';
                $nameColor = $isPlayoffWinner ? '#059669' : $textColor;
                $instText = trim($slotData['institution'] ?? '');

                $isSlotPair = str_contains($nameText, ' / ');

                if ($isSlotPair) {
                    // GANDA (Doubles): Split into Player 1 & Player 2
                    $pairNames = explode(' / ', $nameText, 2);
                    $p1Raw = trim($pairNames[0] ?? '');
                    $p2Raw = trim($pairNames[1] ?? '');

                    $p1Full = $poBadge.$seedText.$p1Raw;
                    $p2Full = $p2Raw;

                    $p1Display = htmlspecialchars($p1Full, ENT_QUOTES);
                    $p2Display = htmlspecialchars($p2Full, ENT_QUOTES);
                    $displayInst = htmlspecialchars($instText, ENT_QUOTES);

                    $maxPNameLen = max(mb_strlen($p1Full), mb_strlen($p2Full));

                    if (! empty($instText)) {
                        // 3 Lines: Line 1 = Player 1, Line 2 = Player 2, Line 3 = School
                        if ($bracketSize > 16) {
                            $nameSize = ($maxPNameLen > 30) ? '8.5' : '9.5';
                            $instSize = (mb_strlen($instText) > 36) ? '7.5' : '8';
                            $line1Y = $yCenter - 9;
                            $line2Y = $yCenter + 1.5;
                            $line3Y = $yCenter + 11.5;
                        } else {
                            $nameSize = ($maxPNameLen > 30) ? '9.5' : ($maxPNameLen > 22 ? '10.5' : '11.5');
                            $instSize = (mb_strlen($instText) > 38) ? '8' : '9';
                            $line1Y = $yCenter - 10;
                            $line2Y = $yCenter + 2;
                            $line3Y = $yCenter + 13;
                        }

                        $svg[] = "<text x='".($slotX + 12)."' y='{$line1Y}' font-size='{$nameSize}' font-weight='800' fill='{$nameColor}'>{$p1Display}</text>";
                        $svg[] = "<text x='".($slotX + 12)."' y='{$line2Y}' font-size='{$nameSize}' font-weight='800' fill='{$nameColor}'>{$p2Display}</text>";
                        $svg[] = "<text x='".($slotX + 12)."' y='{$line3Y}' font-size='{$instSize}' font-weight='600' fill='{$subTextColor}'>{$displayInst}</text>";
                    } else {
                        // 2 Lines: Line 1 = Player 1, Line 2 = Player 2
                        $nameSize = ($bracketSize > 16) ? '10' : '11.5';
                        $line1Y = $yCenter - 4;
                        $line2Y = $yCenter + 9;

                        $svg[] = "<text x='".($slotX + 12)."' y='{$line1Y}' font-size='{$nameSize}' font-weight='800' fill='{$nameColor}'>{$p1Display}</text>";
                        $svg[] = "<text x='".($slotX + 12)."' y='{$line2Y}' font-size='{$nameSize}' font-weight='800' fill='{$nameColor}'>{$p2Display}</text>";
                    }
                } else {
                    // TUNGGAL (Singles): 1 Player
                    $fullPlayerName = $poBadge.$seedText.$nameText;
                    $displayName = htmlspecialchars($fullPlayerName, ENT_QUOTES);
                    $displayInst = htmlspecialchars($instText, ENT_QUOTES);
                    $nameLen = mb_strlen($fullPlayerName);
                    $instLen = mb_strlen($instText);

                    if (! empty($instText)) {
                        // Two lines: Line 1 Player Name, Line 2 Institution / School
                        $nameY = $yCenter - 3;
                        $instY = $yCenter + 10;

                        if ($bracketSize > 16) {
                            $nameSize = ($nameLen > 32) ? '9.5' : '11';
                            $instSize = ($instLen > 34) ? '8' : '9';
                        } else {
                            $nameSize = ($nameLen > 32) ? '10.5' : ($nameLen > 22 ? '11.5' : '12.5');
                            $instSize = ($instLen > 36) ? '8.5' : '9.5';
                        }

                        $svg[] = "<text x='".($slotX + 12)."' y='{$nameY}' font-size='{$nameSize}' font-weight='800' fill='{$nameColor}'>{$displayName}</text>";
                        $svg[] = "<text x='".($slotX + 12)."' y='{$instY}' font-size='{$instSize}' font-weight='600' fill='{$subTextColor}'>{$displayInst}</text>";
                    } else {
                        // Single line
                        $nameSize = ($bracketSize > 16) ? '11.5' : '13';
                        $svg[] = "<text x='".($slotX + 12)."' y='".($yCenter + 4.5)."' font-size='{$nameSize}' font-weight='800' fill='{$nameColor}'>{$displayName}</text>";
                    }
                }
            }
        }

        // 2.5 Draw Play-off Wing (Left Section)
        if ($hasPlayoffs) {
            foreach ($playoffs['matches'] as $po) {
                $targetSlot = $po['target_slot'] ?? $bracketSize;
                $targetY = $slotYPositions[$targetSlot] ?? ($topMargin + ($targetSlot - 0.5) * $slotHeight);

                $poBoxW = $isDoublesBracket ? 210 : 175;
                $poBoxH = max(30, $slotHeight * 0.88);
                $poX = $leftMargin - $poWidth + 8;
                $poY1 = $targetY - ($slotHeight * 0.75);
                $poY2 = $targetY + ($slotHeight * 0.75);
                $poStemX = $leftMargin - 28;

                $team1 = $po['team1'] ?? null;
                $team2 = $po['team2'] ?? null;
                $isPendingT1 = empty($team1['name']) || ! empty($team1['is_pending_draw']);
                $isPendingT2 = empty($team2['name']) || ! empty($team2['is_pending_draw']);
                $poWinnerTeam = $po['existing_match']?->winner_team;

                $box1Y = $poY1 - ($poBoxH / 2);
                $box2Y = $poY2 - ($poBoxH / 2);

                $border1 = ($poWinnerTeam === 1) ? '#059669' : ($isDark ? '#475569' : '#cbd5e1');
                $border2 = ($poWinnerTeam === 2) ? '#059669' : ($isDark ? '#475569' : '#cbd5e1');
                $fill1 = ($poWinnerTeam === 1) ? ($isDark ? '#064e3b' : '#ecfdf5') : $boxBg;
                $fill2 = ($poWinnerTeam === 2) ? ($isDark ? '#064e3b' : '#ecfdf5') : $boxBg;

                // Box 1
                $svg[] = "<rect x='{$poX}' y='{$box1Y}' width='{$poBoxW}' height='{$poBoxH}' fill='{$fill1}' stroke='{$border1}' stroke-width='1.4' rx='3'/>";
                if ($isPendingT1) {
                    $svg[] = "<text x='".($poX + 8)."' y='".($poY1 + 4)."' font-size='9' font-style='italic' fill='".($isDark ? '#475569' : '#94a3b8')."'>[Menunggu Undian]</text>";
                } else {
                    $t1Name = $team1['name'] ?? '';
                    $t1Inst = trim($team1['institution'] ?? '');
                    if (str_contains($t1Name, ' / ')) {
                        $pNames = explode(' / ', $t1Name, 2);
                        $svg[] = "<text x='".($poX + 8)."' y='".($poY1 - 7)."' font-size='8.5' font-weight='700' fill='{$textColor}'>".htmlspecialchars(trim($pNames[0]), ENT_QUOTES).'</text>';
                        $svg[] = "<text x='".($poX + 8)."' y='".($poY1 + 2)."' font-size='8.5' font-weight='700' fill='{$textColor}'>".htmlspecialchars(trim($pNames[1]), ENT_QUOTES).'</text>';
                        if (! empty($t1Inst)) {
                            $svg[] = "<text x='".($poX + 8)."' y='".($poY1 + 11)."' font-size='7.5' font-weight='500' fill='{$subTextColor}'>".htmlspecialchars($t1Inst, ENT_QUOTES).'</text>';
                        }
                    } else {
                        if (! empty($t1Inst)) {
                            $svg[] = "<text x='".($poX + 8)."' y='".($poY1 - 2)."' font-size='9.5' font-weight='700' fill='{$textColor}'>".htmlspecialchars($t1Name, ENT_QUOTES).'</text>';
                            $svg[] = "<text x='".($poX + 8)."' y='".($poY1 + 9)."' font-size='8' font-weight='500' fill='{$subTextColor}'>".htmlspecialchars($t1Inst, ENT_QUOTES).'</text>';
                        } else {
                            $svg[] = "<text x='".($poX + 8)."' y='".($poY1 + 4)."' font-size='9.5' font-weight='700' fill='{$textColor}'>".htmlspecialchars($t1Name, ENT_QUOTES).'</text>';
                        }
                    }
                }

                // Box 2
                $svg[] = "<rect x='{$poX}' y='{$box2Y}' width='{$poBoxW}' height='{$poBoxH}' fill='{$fill2}' stroke='{$border2}' stroke-width='1.4' rx='3'/>";
                if ($isPendingT2) {
                    $svg[] = "<text x='".($poX + 8)."' y='".($poY2 + 4)."' font-size='9' font-style='italic' fill='".($isDark ? '#475569' : '#94a3b8')."'>[Menunggu Undian]</text>";
                } else {
                    $t2Name = $team2['name'] ?? '';
                    $t2Inst = trim($team2['institution'] ?? '');
                    if (str_contains($t2Name, ' / ')) {
                        $pNames2 = explode(' / ', $t2Name, 2);
                        $svg[] = "<text x='".($poX + 8)."' y='".($poY2 - 7)."' font-size='8.5' font-weight='700' fill='{$textColor}'>".htmlspecialchars(trim($pNames2[0]), ENT_QUOTES).'</text>';
                        $svg[] = "<text x='".($poX + 8)."' y='".($poY2 + 2)."' font-size='8.5' font-weight='700' fill='{$textColor}'>".htmlspecialchars(trim($pNames2[1]), ENT_QUOTES).'</text>';
                        if (! empty($t2Inst)) {
                            $svg[] = "<text x='".($poX + 8)."' y='".($poY2 + 11)."' font-size='7.5' font-weight='500' fill='{$subTextColor}'>".htmlspecialchars($t2Inst, ENT_QUOTES).'</text>';
                        }
                    } else {
                        if (! empty($t2Inst)) {
                            $svg[] = "<text x='".($poX + 8)."' y='".($poY2 - 2)."' font-size='9.5' font-weight='700' fill='{$textColor}'>".htmlspecialchars($t2Name, ENT_QUOTES).'</text>';
                            $svg[] = "<text x='".($poX + 8)."' y='".($poY2 + 9)."' font-size='8' font-weight='500' fill='{$subTextColor}'>".htmlspecialchars($t2Inst, ENT_QUOTES).'</text>';
                        } else {
                            $svg[] = "<text x='".($poX + 8)."' y='".($poY2 + 4)."' font-size='9.5' font-weight='700' fill='{$textColor}'>".htmlspecialchars($t2Name, ENT_QUOTES).'</text>';
                        }
                    }
                }

                // Connecting lines to Main Draw target slot
                $lineX1 = $poX + $poBoxW;
                $svg[] = "<line x1='{$lineX1}' y1='{$poY1}' x2='{$poStemX}' y2='{$poY1}' stroke='{$strokeColor}' stroke-width='{$strokeWidth}'/>";
                $svg[] = "<line x1='{$lineX1}' y1='{$poY2}' x2='{$poStemX}' y2='{$poY2}' stroke='{$strokeColor}' stroke-width='{$strokeWidth}'/>";
                $svg[] = "<line x1='{$poStemX}' y1='{$poY1}' x2='{$poStemX}' y2='{$poY2}' stroke='{$strokeColor}' stroke-width='{$strokeWidth}'/>";
                $svg[] = "<line x1='{$poStemX}' y1='{$targetY}' x2='".($slotX - 18)."' y2='{$targetY}' stroke='{$strokeColor}' stroke-width='{$strokeWidth}' stroke-dasharray='3 2'/>";

                // Arrowhead pointing into Slot box
                $arrowX = $slotX - 18;
                $svg[] = "<polygon points='{$arrowX},{$targetY} ".($arrowX - 6).','.($targetY - 4).' '.($arrowX - 6).','.($targetY + 4)."' fill='{$accentColor}'/>";

                // Play-off schedule text above the line
                $poSched = $po['existing_match']?->scheduled_time ?? '07:30';
                $svg[] = "<text x='".($poStemX + 3)."' y='".($targetY - 4)."' font-size='7.5' font-mono font-weight='700' fill='{$accentColor}'>{$poSched}</text>";
            }
        }

        // 3. Draw Branching Lines and Connectors
        $currentStems = [];
        for ($s = 1; $s <= $bracketSize; $s++) {
            $currentStems[$s] = [
                'x' => $slotX + $slotWidth,
                'y' => $slotYPositions[$s],
            ];
        }

        $colStartX = $slotX + $slotWidth;

        for ($r = 1; $r <= $totalRounds; $r++) {
            $rMatches = $rounds[$r]['matches'] ?? [];
            $numMatches = count($rMatches);
            $nextStems = [];

            $branchStartX = $colStartX;
            $bracketVLineX = $branchStartX + 20;
            $stemEndX = $bracketVLineX + ($branchWidth - 20);

            for ($m = 0; $m < $numMatches; $m++) {
                $match = $rMatches[$m] ?? null;
                $idx1 = ($m * 2) + 1;
                $idx2 = ($m * 2) + 2;

                $y1 = $currentStems[$idx1]['y'] ?? 0;
                $y2 = $currentStems[$idx2]['y'] ?? $y1;
                $yMid = ($y1 + $y2) / 2;

                // Horizontal arm from top
                $svg[] = "<line x1='{$branchStartX}' y1='{$y1}' x2='{$bracketVLineX}' y2='{$y1}' stroke='{$strokeColor}' stroke-width='{$strokeWidth}'/>";
                // Horizontal arm from bottom
                $svg[] = "<line x1='{$branchStartX}' y1='{$y2}' x2='{$bracketVLineX}' y2='{$y2}' stroke='{$strokeColor}' stroke-width='{$strokeWidth}'/>";
                // Vertical connector bar
                $svg[] = "<line x1='{$bracketVLineX}' y1='{$y1}' x2='{$bracketVLineX}' y2='{$y2}' stroke='{$strokeColor}' stroke-width='{$strokeWidth}'/>";
                // Horizontal stem to right
                $svg[] = "<line x1='{$bracketVLineX}' y1='{$yMid}' x2='{$stemEndX}' y2='{$yMid}' stroke='{$strokeColor}' stroke-width='{$strokeWidth}'/>";

                // Winner text on line (Peserta yang Lolos ke Babak Berikutnya)
                if (! empty($match['winner'])) {
                    $rawWinnerName = $match['winner']['name'] ?? '';
                    $wColor = $isDark ? '#34d399' : '#047857';

                    if (str_contains($rawWinnerName, ' / ')) {
                        // Doubles pair advancing
                        $wNames = explode(' / ', $rawWinnerName, 2);
                        $w1 = htmlspecialchars(trim($wNames[0] ?? ''), ENT_QUOTES);
                        $w2 = htmlspecialchars(trim($wNames[1] ?? ''), ENT_QUOTES);
                        $wMaxLen = max(mb_strlen($w1), mb_strlen($w2));
                        $wSize = ($wMaxLen > 24) ? '8.5' : (($bracketSize > 16) ? '9.5' : '10.5');

                        // 2 lines above the horizontal branch stem line
                        $svg[] = "<text x='".($bracketVLineX + 8)."' y='".($yMid - 13)."' font-size='{$wSize}' font-weight='800' fill='{$wColor}'>{$w1}</text>";
                        $svg[] = "<text x='".($bracketVLineX + 8)."' y='".($yMid - 3)."' font-size='{$wSize}' font-weight='800' fill='{$wColor}'>{$w2}</text>";
                    } else {
                        // Single player advancing
                        $wName = htmlspecialchars($rawWinnerName, ENT_QUOTES);
                        $wLen = mb_strlen($rawWinnerName);
                        $wFontSize = ($wLen > 26) ? '10' : (($bracketSize > 16) ? '11' : '12.5');
                        $svg[] = "<text x='".($bracketVLineX + 8)."' y='".($yMid - 6)."' font-size='{$wFontSize}' font-weight='800' fill='{$wColor}'>{$wName}</text>";
                    }
                }

                // Match score or Court Schedule if available
                if (! empty($match['existing_match'])) {
                    $em = $match['existing_match'];
                    if ($em->team1_set1 > 0 || $em->team2_set1 > 0) {
                        $scoreStr = "{$em->team1_set1}-{$em->team2_set1}";
                        $svg[] = "<text x='".($bracketVLineX + 8)."' y='".($yMid + 12)."' font-size='8.5' font-mono font-weight='bold' fill='{$subTextColor}'>{$scoreStr}</text>";
                    } elseif (! empty($em->court_number) || ! empty($em->scheduled_time)) {
                        $schedParts = [];
                        if (! empty($em->match_day)) {
                            $schedParts[] = "H{$em->match_day}";
                        }
                        if (! empty($em->match_order)) {
                            $schedParts[] = "#{$em->match_order}";
                        }
                        if (! empty($em->court_number) && strtoupper($em->court_number) !== 'BYE') {
                            $schedParts[] = $em->court_number;
                        }
                        if (! empty($em->scheduled_time)) {
                            $schedParts[] = $em->scheduled_time;
                        }
                        if (! empty($schedParts)) {
                            $schedStr = htmlspecialchars(implode(' • ', $schedParts), ENT_QUOTES);
                            $svg[] = "<text x='".($bracketVLineX + 8)."' y='".($yMid + 11)."' font-size='7.5' font-mono font-weight='700' fill='{$accentColor}'>{$schedStr}</text>";
                        }
                    }
                }

                $nextStems[$m + 1] = [
                    'x' => $stemEndX,
                    'y' => $yMid,
                ];
            }

            $colStartX = $stemEndX;
            $currentStems = $nextStems;
        }

        // 4. Final Champion Line
        $champStem = $currentStems[1] ?? ['x' => $colStartX, 'y' => $totalHeight / 2];
        $champX1 = $champStem['x'];
        $champY = $champStem['y'];
        $champX2 = $champX1 + $champLineLength;
        $champCenterX = $champX2 + ($champBoxWidth / 2);
        $champBoxY = $champY - ($champBoxHeight / 2);

        $svg[] = "<line x1='{$champX1}' y1='{$champY}' x2='{$champX2}' y2='{$champY}' stroke='{$strokeColor}' stroke-width='2.2'/>";

        $champion = $bracketData['champion'] ?? null;
        if ($champion) {
            $rawChampName = $champion['name'] ?? '';
            $svg[] = "<rect x='{$champX2}' y='{$champBoxY}' width='{$champBoxWidth}' height='{$champBoxHeight}' fill='#fef3c7' stroke='{$accentColor}' stroke-width='2' rx='6'/>";

            if (str_contains($rawChampName, ' / ')) {
                // Doubles Champion
                $cNames = explode(' / ', $rawChampName, 2);
                $c1 = htmlspecialchars(trim($cNames[0] ?? ''), ENT_QUOTES);
                $c2 = htmlspecialchars(trim($cNames[1] ?? ''), ENT_QUOTES);
                $maxCLen = max(mb_strlen($c1), mb_strlen($c2));
                $cSize = ($maxCLen > 24) ? '9.5' : '10.5';

                $svg[] = "<text x='{$champCenterX}' y='".($champY - 10)."' text-anchor='middle' font-size='9.5' font-weight='800' fill='#b45309'>🏆 JUARA 1</text>";
                $svg[] = "<text x='{$champCenterX}' y='".($champY + 2)."' text-anchor='middle' font-size='{$cSize}' font-weight='800' fill='#0f172a'>{$c1}</text>";
                $svg[] = "<text x='{$champCenterX}' y='".($champY + 14)."' text-anchor='middle' font-size='{$cSize}' font-weight='800' fill='#0f172a'>{$c2}</text>";
            } else {
                // Singles Champion
                $champName = htmlspecialchars($rawChampName, ENT_QUOTES);
                $cSize = (mb_strlen($rawChampName) > 22) ? '10.5' : '12';
                $svg[] = "<text x='{$champCenterX}' y='".($champY - 4)."' text-anchor='middle' font-size='9.5' font-weight='800' fill='#b45309'>🏆 JUARA 1</text>";
                $svg[] = "<text x='{$champCenterX}' y='".($champY + 11)."' text-anchor='middle' font-size='{$cSize}' font-weight='800' fill='#0f172a'>{$champName}</text>";
            }
        } else {
            $svg[] = "<rect x='{$champX2}' y='{$champBoxY}' width='{$champBoxWidth}' height='{$champBoxHeight}' fill='{$boxBg}' stroke='{$strokeColor}' stroke-width='1.5' stroke-dasharray='4 3' rx='6'/>";
            $svg[] = "<text x='{$champCenterX}' y='".($champY + 4.5)."' text-anchor='middle' font-size='11' font-weight='800' fill='{$subTextColor}'>Pemenang Final</text>";
        }

        $svg[] = '</svg>';

        return implode("\n", $svg);
    }

    /**
     * Pastikan lomba adalah cabang turnamen sistem gugur (Bulu Tangkis atau Tenis Meja)
     */
    private function ensureIsBadminton(Competition $competition): void
    {
        $code = strtoupper($competition->code ?? '');
        $name = strtolower($competition->name ?? '');

        $isTournamentSport = in_array($code, ['BLT', 'TMJ'])
            || str_contains($name, 'bulu tangkis')
            || str_contains($name, 'badminton')
            || str_contains($name, 'tenis meja');

        if (! $isTournamentSport) {
            abort(404, 'Bagan turnamen sistem gugur hanya tersedia khusus untuk cabang Bulu Tangkis dan Tenis Meja.');
        }
    }
}
