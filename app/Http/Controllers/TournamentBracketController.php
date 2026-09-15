<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\BadmintonMatch;
use App\Models\Competition;
use App\Models\Registration;
use App\Traits\CompetitionPoolTrait;
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
            $competition = Competition::where('code', 'BLT')
                ->orWhere('name', 'like', '%Bulu Tangkis%')
                ->orWhere('name', 'like', '%Badminton%')
                ->first();

            if (! $competition) {
                return redirect()->route('badminton.index')->with('error', 'Cabang lomba Bulu Tangkis belum terdaftar dalam sistem.');
            }

            $competition_id = $competition->id;
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
            $isAuthorizedBadminton = $user->managesBadminton() && (
                strtoupper($competition->code ?? '') === 'BLT' ||
                str_contains(strtolower($competition->name ?? ''), 'bulu tangkis') ||
                str_contains(strtolower($competition->name ?? ''), 'badminton')
            );

            if (! in_array($competition->id, $managedIds) && ! $isAuthorizedBadminton) {
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

        return view('pic.bracket', compact('competition', 'pools', 'activePoolKey', 'activePool', 'bracketData'));
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

        return view('public.bracket-viewer', compact('competition', 'pools', 'activePoolKey', 'activePool', 'bracketData', 'appSettings'));
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

        $syncedCount = 0;
        $categoryCode = stripos($targetPool['title'], 'putri') !== false ? 'WS' : 'MS';
        if (stripos($targetPool['title'], 'ganda') !== false) {
            $categoryCode = stripos($targetPool['title'], 'putri') !== false ? 'WD' : 'MD';
        }

        foreach ($bracketData['rounds'] as $round) {
            $roundName = $round['round_name'];

            foreach ($round['matches'] as $match) {
                $team1 = $match['team1'];
                $team2 = $match['team2'];

                // Skip if both are empty
                if (! $team1 && ! $team2) {
                    continue;
                }

                $isBye1 = ! empty($match['is_bye1']);
                $isBye2 = ! empty($match['is_bye2']);

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

                BadmintonMatch::updateOrCreate(
                    [
                        'competition_id' => $competition->id,
                        'match_code'     => $match['match_code'],
                    ],
                    [
                        'court_number'          => 'Lapangan 1',
                        'round_name'            => $roundName,
                        'category'              => $categoryCode,
                        'match_type'            => stripos($targetPool['title'], 'ganda') !== false ? 'double' : 'single',
                        'team1_registration_id' => $team1['id'] ?? null,
                        'team1_school'          => $team1['institution'] ?? ($isBye1 ? 'BYE' : 'TBD'),
                        'team1_player1'         => $team1['name'] ?? ($isBye1 ? '[BYE]' : 'Menunggu Pemenang'),
                        'team2_registration_id' => $team2['id'] ?? null,
                        'team2_school'          => $team2['institution'] ?? ($isBye2 ? 'BYE' : 'TBD'),
                        'team2_player1'         => $team2['name'] ?? ($isBye2 ? '[BYE]' : 'Menunggu Pemenang'),
                        'match_status'          => $status,
                        'winner_team'           => $winnerTeam,
                    ]
                );

                $syncedCount++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Berhasil menyinkronkan {$syncedCount} pertandingan ke modul wasit & jadwal!",
        ]);
    }

    /**
     * Membangun Pohon Bagan Turnamen Berdasarkan Standar BWF
     */
    public function buildTournamentTree(array $poolParticipants, Competition $competition, string $poolKey = 'pool'): array
    {
        $total = count($poolParticipants);
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

        $totalRounds = (int) log($bracketSize, 2);
        $totalByes = $bracketSize - $total;

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

        // Priority slots for BYEs (paired with seeds 1..8)
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

        $slots = array_fill(1, $bracketSize, null);
        foreach ($assignedByes as $bs) {
            $slots[$bs] = [
                'is_bye'      => true,
                'name'        => '[BYE]',
                'institution' => 'Bebas Babak 1',
                'id'          => null,
                'draw_number' => null,
                'seed_number' => null,
            ];
        }

        // Place seeded participants first
        $unseeded = [];
        foreach ($poolParticipants as $p) {
            $seedNum = $p['seed_number'] ?? null;
            if (! empty($seedNum) && isset($seedSlots[$seedNum])) {
                $targetSlot = $seedSlots[$seedNum];
                $slots[$targetSlot] = $p;
            } else {
                $unseeded[] = $p;
            }
        }

        // Sort unseeded by draw_number
        usort($unseeded, fn ($a, $b) => ($a['draw_number'] ?? 999) <=> ($b['draw_number'] ?? 999));

        // Fill remaining open slots
        $uIdx = 0;
        for ($s = 1; $s <= $bracketSize; $s++) {
            if ($slots[$s] === null) {
                if ($uIdx < count($unseeded)) {
                    $slots[$s] = $unseeded[$uIdx++];
                }
            }
        }

        // Fetch existing BadmintonMatch records for this competition to overlay live scores & match statuses
        $existingMatches = BadmintonMatch::where('competition_id', $competition->id)
            ->where('match_code', 'like', "{$poolKey}-R%")
            ->get()
            ->keyBy('match_code');

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

            $matchCode = "{$poolKey}-R1-M{$m}";
            $existingMatch = $existingMatches->get($matchCode);

            $winner = null;
            $status = 'upcoming';

            if ($existingMatch && $existingMatch->match_status === 'finished') {
                $status = 'finished';
                $winner = $existingMatch->winner_team === 1 ? $p1 : ($existingMatch->winner_team === 2 ? $p2 : null);
            } elseif ($p1 && ! $isBye1 && $isBye2) {
                $winner = $p1;
                $status = 'bye_advance';
            } elseif ($p2 && ! $isBye2 && $isBye1) {
                $winner = $p2;
                $status = 'bye_advance';
            } elseif ($existingMatch && $existingMatch->match_status === 'ongoing') {
                $status = 'ongoing';
            }

            $r1Matches[$m] = [
                'match_code'     => $matchCode,
                'round_index'    => 1,
                'match_index'    => $m,
                'slot1'          => $slot1,
                'slot2'          => $slot2,
                'team1'          => $p1,
                'team2'          => $p2,
                'is_bye1'        => $isBye1,
                'is_bye2'        => $isBye2,
                'winner'         => $winner,
                'status'         => $status,
                'existing_match' => $existingMatch,
            ];
        }

        $rounds[1] = [
            'round_index' => 1,
            'round_name'  => $roundNames[1] ?? 'Babak 1',
            'matches'     => array_values($r1Matches),
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
                    'match_code'     => $matchCode,
                    'round_index'    => $r,
                    'match_index'    => $m,
                    'team1'          => $t1,
                    'team2'          => $t2,
                    'winner'         => $winner,
                    'status'         => $status,
                    'existing_match' => $existingMatch,
                ];
            }

            $rounds[$r] = [
                'round_index' => $r,
                'round_name'  => $roundNames[$r] ?? "Babak {$r}",
                'matches'     => $currentMatches,
            ];
        }

        // Champion
        $finalRound = end($rounds);
        $finalMatch = $finalRound['matches'][0] ?? null;
        $champion = $finalMatch['winner'] ?? null;

        $bracketData = [
            'bracket_size'       => $bracketSize,
            'total_participants' => $total,
            'total_byes'         => $totalByes,
            'total_rounds'       => $totalRounds,
            'rounds'             => $rounds,
            'champion'           => $champion,
        ];

        $bracketData['classic_svg_light'] = $this->renderClassicBracketSvg($bracketData, ['isDark' => false]);
        $bracketData['classic_svg_dark']  = $this->renderClassicBracketSvg($bracketData, ['isDark' => true]);

        return $bracketData;
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

        $isDark = $options['isDark'] ?? false;
        $slotHeight = $options['slotHeight'] ?? ($bracketSize > 16 ? 32 : ($bracketSize > 8 ? 42 : 54));
        $slotWidth = $options['slotWidth'] ?? 190;
        $branchWidth = $options['branchWidth'] ?? 110;
        $leftMargin = $options['leftMargin'] ?? 45;
        $topMargin = $options['topMargin'] ?? 52;

        $strokeColor = $isDark ? '#64748b' : '#0f172a';
        $strokeWidth = '1.8';
        $textColor = $isDark ? '#f8fafc' : '#0f172a';
        $subTextColor = $isDark ? '#94a3b8' : '#64748b';
        $boxBg = $isDark ? '#1e293b' : '#ffffff';
        $byeBoxBg = $isDark ? '#0f172a' : '#f8fafc';
        $accentColor = '#d97706';

        $totalHeight = ($bracketSize * $slotHeight) + $topMargin + 40;
        $totalWidth = $leftMargin + $slotWidth + ($totalRounds * $branchWidth) + 160;

        $svg = [];
        $svg[] = "<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 {$totalWidth} {$totalHeight}' width='100%' height='auto' style='max-width: {$totalWidth}px; font-family: system-ui, -apple-system, sans-serif;'>";

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
        // Juara
        $svg[] = "<text x='" . ($totalWidth - 70) . "' y='26' text-anchor='middle' font-size='13' font-weight='800' fill='{$accentColor}'>Juara 1</text>";

        // Extract slots from Round 1
        $r1Matches = $rounds[1]['matches'] ?? [];
        $slots = [];
        foreach ($r1Matches as $mIdx => $m) {
            $s1 = ($mIdx * 2) + 1;
            $s2 = ($mIdx * 2) + 2;
            $slots[$s1] = $m['team1'] ?? ['name' => '', 'slot_number' => $s1];
            $slots[$s2] = $m['team2'] ?? ['name' => '', 'slot_number' => $s2];
        }

        // 2. Draw Slot Numbers and Boxes for Round 1
        $slotX = $leftMargin;
        $slotYPositions = [];

        for ($s = 1; $s <= $bracketSize; $s++) {
            $slotData = $slots[$s] ?? ['name' => '', 'slot_number' => $s];
            $isBye = $slotData['is_bye'] ?? false;
            $yCenter = $topMargin + ($s - 0.5) * $slotHeight;
            $slotYPositions[$s] = $yCenter;

            $boxY = $yCenter - ($slotHeight * 0.42);
            $boxH = $slotHeight * 0.84;

            // Slot Number (1, 2, ..., 32)
            $svg[] = "<text x='" . ($slotX - 10) . "' y='" . ($yCenter + 4) . "' text-anchor='end' font-size='11' font-weight='700' fill='{$subTextColor}'>{$s}</text>";

            // Rectangle Box
            $currentBoxBg = $isBye ? $byeBoxBg : $boxBg;
            $currentBorder = $isBye ? '#94a3b8' : $strokeColor;
            $dashAttr = $isBye ? "stroke-dasharray='4 2'" : "";
            $svg[] = "<rect x='{$slotX}' y='{$boxY}' width='{$slotWidth}' height='{$boxH}' fill='{$currentBoxBg}' stroke='{$currentBorder}' stroke-width='1.5' rx='2' {$dashAttr}/>";

            // Player Text
            $nameText = $slotData['name'] ?? '';
            $seedText = !empty($slotData['seed_number']) ? "(S{$slotData['seed_number']}) " : "";
            $instText = (!empty($slotData['institution']) && !$isBye) ? " - " . $slotData['institution'] : "";
            $fullText = $seedText . $nameText . $instText;

            if (mb_strlen($fullText) > 25) {
                $fullText = mb_substr($fullText, 0, 23) . '..';
            }

            $displayText = htmlspecialchars($fullText, ENT_QUOTES);
            $nameColor = $isBye ? '#94a3b8' : $textColor;
            $fontStyle = $isBye ? "font-style='italic'" : "";
            $svg[] = "<text x='" . ($slotX + 8) . "' y='" . ($yCenter + 4) . "' font-size='10' font-weight='600' fill='{$nameColor}' {$fontStyle}>{$displayText}</text>";
        }

        // 3. Draw Branching Lines and Connectors
        $currentStems = [];
        for ($s = 1; $s <= $bracketSize; $s++) {
            $currentStems[$s] = [
                'x' => $slotX + $slotWidth,
                'y' => $slotYPositions[$s]
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

                $y1 = $currentStems[$idx1]['y'];
                $y2 = $currentStems[$idx2]['y'];
                $yMid = ($y1 + $y2) / 2;

                // Horizontal arm from top
                $svg[] = "<line x1='{$branchStartX}' y1='{$y1}' x2='{$bracketVLineX}' y2='{$y1}' stroke='{$strokeColor}' stroke-width='{$strokeWidth}'/>";
                // Horizontal arm from bottom
                $svg[] = "<line x1='{$branchStartX}' y1='{$y2}' x2='{$bracketVLineX}' y2='{$y2}' stroke='{$strokeColor}' stroke-width='{$strokeWidth}'/>";
                // Vertical connector bar
                $svg[] = "<line x1='{$bracketVLineX}' y1='{$y1}' x2='{$bracketVLineX}' y2='{$y2}' stroke='{$strokeColor}' stroke-width='{$strokeWidth}'/>";
                // Horizontal stem to right
                $svg[] = "<line x1='{$bracketVLineX}' y1='{$yMid}' x2='{$stemEndX}' y2='{$yMid}' stroke='{$strokeColor}' stroke-width='{$strokeWidth}'/>";

                // Winner text on line
                if (!empty($match['winner'])) {
                    $wName = htmlspecialchars(mb_substr($match['winner']['name'] ?? '', 0, 16), ENT_QUOTES);
                    $svg[] = "<text x='" . ($bracketVLineX + 6) . "' y='" . ($yMid - 5) . "' font-size='9' font-weight='700' fill='#059669'>{$wName}</text>";
                }

                // Match score if available
                if (!empty($match['existing_match']) && ($match['existing_match']->team1_set1 > 0 || $match['existing_match']->team2_set1 > 0)) {
                    $em = $match['existing_match'];
                    $scoreStr = "{$em->team1_set1}-{$em->team2_set1}";
                    $svg[] = "<text x='" . ($bracketVLineX + 6) . "' y='" . ($yMid + 11) . "' font-size='8' font-mono font-weight='bold' fill='{$subTextColor}'>{$scoreStr}</text>";
                }

                $nextStems[$m + 1] = [
                    'x' => $stemEndX,
                    'y' => $yMid
                ];
            }

            $colStartX = $stemEndX;
            $currentStems = $nextStems;
        }

        // 4. Final Champion Line
        $champStem = $currentStems[1] ?? ['x' => $colStartX, 'y' => $totalHeight / 2];
        $champX1 = $champStem['x'];
        $champY = $champStem['y'];
        $champX2 = $champX1 + 110;

        $svg[] = "<line x1='{$champX1}' y1='{$champY}' x2='{$champX2}' y2='{$champY}' stroke='{$strokeColor}' stroke-width='2.2'/>";

        $champion = $bracketData['champion'] ?? null;
        if ($champion) {
            $champName = htmlspecialchars($champion['name'], ENT_QUOTES);
            $svg[] = "<rect x='{$champX2}' y='" . ($champY - 17) . "' width='130' height='34' fill='#fef3c7' stroke='{$accentColor}' stroke-width='1.8' rx='4'/>";
            $svg[] = "<text x='" . ($champX2 + 65) . "' y='" . ($champY - 3) . "' text-anchor='middle' font-size='8.5' font-weight='800' fill='#b45309'>🏆 JUARA 1</text>";
            $svg[] = "<text x='" . ($champX2 + 65) . "' y='" . ($champY + 10) . "' text-anchor='middle' font-size='10' font-weight='800' fill='#0f172a'>{$champName}</text>";
        } else {
            $svg[] = "<rect x='{$champX2}' y='" . ($champY - 15) . "' width='120' height='30' fill='{$boxBg}' stroke='{$strokeColor}' stroke-width='1.5' stroke-dasharray='3 3' rx='4'/>";
            $svg[] = "<text x='" . ($champX2 + 60) . "' y='" . ($champY + 4) . "' text-anchor='middle' font-size='9.5' font-weight='700' fill='{$subTextColor}'>Pemenang Final</text>";
        }

        $svg[] = "</svg>";
        return implode("\n", $svg);
    }

    /**
     * Pastikan lomba adalah cabang Bulu Tangkis
     */
    private function ensureIsBadminton(Competition $competition): void
    {
        $isBadminton = (strtoupper($competition->code ?? '') === 'BLT')
            || str_contains(strtolower($competition->name ?? ''), 'bulu tangkis')
            || str_contains(strtolower($competition->name ?? ''), 'badminton');

        if (! $isBadminton) {
            abort(404, 'Bagan turnamen sistem gugur hanya tersedia khusus untuk cabang Bulu Tangkis.');
        }
    }
}
