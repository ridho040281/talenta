<?php

namespace App\Http\Controllers;

use App\Helpers\Terbilang;
use App\Models\AppSetting;
use App\Models\Competition;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OfficialReportController extends Controller
{
    /**
     * Determine if a competition is Olahraga (Sports) or Seni/Umum (Art/Academic)
     */
    public static function isSportsCompetition(Competition $comp): bool
    {
        $code = strtoupper($comp->code ?? '');
        $slug = strtolower($comp->category?->slug ?? '');
        $catName = strtolower($comp->category?->name ?? '');

        return in_array($code, ['BLT', 'TMJ', 'CTR']) 
            || $slug === 'olahraga' 
            || str_contains($catName, 'olahraga');
    }

    /**
     * Get competition sectors/categories definition
     */
    public static function getCompetitionSectors(Competition $comp): array
    {
        $code = strtoupper($comp->code ?? '');

        if ($code === 'BLT') {
            return [
                'tunggal_pa_a' => ['title' => 'Kategori A (Kelas 1–2 SD/MI) - Tunggal Putra (PA)', 'group' => 'Tunggal Putra A', 'gender' => 'L', 'is_ganda' => false, 'kat' => 'a'],
                'tunggal_pi_a' => ['title' => 'Kategori A (Kelas 1–2 SD/MI) - Tunggal Putri (PI)', 'group' => 'Tunggal Putri A', 'gender' => 'P', 'is_ganda' => false, 'kat' => 'a'],
                'tunggal_pa_b' => ['title' => 'Kategori B (Kelas 3–4 SD/MI) - Tunggal Putra (PA)', 'group' => 'Tunggal Putra B', 'gender' => 'L', 'is_ganda' => false, 'kat' => 'b'],
                'tunggal_pi_b' => ['title' => 'Kategori B (Kelas 3–4 SD/MI) - Tunggal Putri (PI)', 'group' => 'Tunggal Putri B', 'gender' => 'P', 'is_ganda' => false, 'kat' => 'b'],
                'tunggal_pa_c' => ['title' => 'Kategori C (Kelas 5–6 SD/MI) - Tunggal Putra (PA)', 'group' => 'Tunggal Putra C', 'gender' => 'L', 'is_ganda' => false, 'kat' => 'c'],
                'tunggal_pi_c' => ['title' => 'Kategori C (Kelas 5–6 SD/MI) - Tunggal Putri (PI)', 'group' => 'Tunggal Putri C', 'gender' => 'P', 'is_ganda' => false, 'kat' => 'c'],
                'ganda_pa'     => ['title' => 'Kategori Ganda Putra (PA) - Semua Kelas', 'group' => 'Ganda Putra', 'gender' => 'L', 'is_ganda' => true, 'kat' => 'all'],
                'ganda_pi'     => ['title' => 'Kategori Ganda Putri (PI) - Semua Kelas', 'group' => 'Ganda Putri', 'gender' => 'P', 'is_ganda' => true, 'kat' => 'all'],
            ];
        }

        if ($code === 'TMJ') {
            return [
                'tmj_pa_a' => ['title' => 'Kategori A (Kelas 1–3 SD/MI) - Tunggal Putra (PA)', 'group' => 'Putra A', 'gender' => 'L', 'is_ganda' => false, 'kat' => 'a'],
                'tmj_pi_a' => ['title' => 'Kategori A (Kelas 1–3 SD/MI) - Tunggal Putri (PI)', 'group' => 'Putri A', 'gender' => 'P', 'is_ganda' => false, 'kat' => 'a'],
                'tmj_pa_b' => ['title' => 'Kategori B (Kelas 4–6 SD/MI) - Tunggal Putra (PA)', 'group' => 'Putra B', 'gender' => 'L', 'is_ganda' => false, 'kat' => 'b'],
                'tmj_pi_b' => ['title' => 'Kategori B (Kelas 4–6 SD/MI) - Tunggal Putri (PI)', 'group' => 'Putri B', 'gender' => 'P', 'is_ganda' => false, 'kat' => 'b'],
            ];
        }

        if (in_array($code, ['MTQ', 'POP'])) {
            return [
                'pa' => ['title' => 'Kategori Putra (PA)', 'group' => 'Putra', 'gender' => 'L', 'is_ganda' => false, 'kat' => 'all'],
                'pi' => ['title' => 'Kategori Putri (PI)', 'group' => 'Putri', 'gender' => 'P', 'is_ganda' => false, 'kat' => 'all'],
            ];
        }

        // Default Single Open / Umum
        return [
            'umum' => ['title' => 'Kategori Umum / Seluruh Peserta', 'group' => 'Umum', 'gender' => 'all', 'is_ganda' => false, 'kat' => 'all'],
        ];
    }

    /**
     * Helper to get Indonesian date in formal words
     */
    public static function getDateSpelledOut(Carbon $date): array
    {
        $days = [
            'Sunday' => 'Minggu',
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
        ];

        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        $dayName = $days[$date->format('l')] ?? 'Sabtu';
        $dayNum = (int) $date->format('d');
        $monthNum = (int) $date->format('m');
        $yearNum = (int) $date->format('Y');

        $daySpelled = ucwords(Terbilang::make($dayNum));
        $monthName = $months[$monthNum] ?? 'Oktober';
        $yearSpelled = ucwords(Terbilang::make($yearNum));

        return [
            'day_name' => $dayName,
            'day_spelled' => $daySpelled,
            'month_name' => $monthName,
            'year_spelled' => $yearSpelled,
            'date_formatted' => $date->format('d') . ' ' . $monthName . ' ' . $date->format('Y'),
        ];
    }

    /**
     * Main Index Page for Berita Acara Management (Tab 1: Terisi, Tab 2: Template Kosong)
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Scope competitions based on role
        if ($user->role === 'superadmin') {
            $competitions = Competition::with(['category', 'judges', 'pic'])->get();
        } elseif ($user->role === 'pic_lomba') {
            $managedIds = PicController::getManagedCompetitionIds($user);
            $competitions = Competition::with(['category', 'judges', 'pic'])->whereIn('id', $managedIds)->get();
        } else {
            $competitions = Competition::with(['category', 'judges', 'pic'])->get();
        }

        $selectedCompId = $request->query('competition_id', $competitions->first()?->id);
        $activeTab = $request->query('tab', 'live'); // 'live' or 'blank'

        $selectedComp = $competitions->firstWhere('id', $selectedCompId) ?: $competitions->first();

        // Calculate winners and structured sectors for selected competition
        $sectorsData = [];
        $judgesList = [];
        $isSports = false;

        if ($selectedComp) {
            $isSports = self::isSportsCompetition($selectedComp);
            $sectorsDef = self::getCompetitionSectors($selectedComp);

            // Fetch verified registrations with scores
            $regs = $selectedComp->registrations()
                ->where('status', 'verified')
                ->with(['members', 'scores.details'])
                ->get();

            foreach ($sectorsDef as $secKey => $secDef) {
                // Filter registrations belonging to this sector
                $filteredRegs = $regs->filter(function ($r) use ($secDef, $selectedComp) {
                    $isGanda = $r->isGanda();
                    $gender = $r->primary_gender;

                    // Match Ganda
                    if ($secDef['is_ganda'] && !$isGanda) return false;
                    if (!$secDef['is_ganda'] && $isGanda && in_array($selectedComp->code, ['BLT', 'TMJ'])) return false;

                    // Match Gender
                    if ($secDef['gender'] !== 'all' && $gender !== $secDef['gender']) return false;

                    // Match Category / Class
                    if ($secDef['kat'] === 'a') {
                        return $r->isKatA();
                    } elseif ($secDef['kat'] === 'b') {
                        return $r->isKatB();
                    } elseif ($secDef['kat'] === 'c') {
                        return $r->isKatC();
                    }

                    return true;
                });

                // Rank winners by average locked score
                $ranked = $filteredRegs->map(function ($r) {
                    $lockedScores = $r->scores->where('is_locked', true);
                    $avgScore = $lockedScores->isNotEmpty() ? round($lockedScores->avg('total_score'), 2) : 0;
                    $participantName = $r->pure_name ?: ($r->team_name ?: ($r->members->first()?->full_name ?? ('Peserta #' . $r->id)));
                    $schoolName = $r->display_school ?: ($r->institution_name ?: '-');
                    return [
                        'registration' => $r,
                        'participant_number' => $r->participant_number ?: $r->registration_code,
                        'display_name' => $participantName,
                        'institution_name' => $schoolName,
                        'score' => $avgScore > 0 ? $avgScore : '',
                    ];
                })->sortByDesc(fn($item) => (float) $item['score'])->values();

                $emptyWinner = [
                    'registration' => null,
                    'participant_number' => '',
                    'display_name' => '',
                    'institution_name' => '',
                    'score' => '',
                ];

                $tiers = [
                    ['tier_label' => 'Juara 1', 'winner' => $ranked[0] ?? $emptyWinner],
                    ['tier_label' => 'Juara 2', 'winner' => $ranked[1] ?? $emptyWinner],
                    ['tier_label' => 'Juara 3', 'winner' => $ranked[2] ?? $emptyWinner],
                    ['tier_label' => 'Juara Harapan 1', 'winner' => $ranked[3] ?? $emptyWinner],
                    ['tier_label' => 'Juara Harapan 2', 'winner' => $ranked[4] ?? $emptyWinner],
                    ['tier_label' => 'Juara Harapan 3', 'winner' => $ranked[5] ?? $emptyWinner],
                ];

                $sectorsData[$secKey] = [
                    'definition' => $secDef,
                    'tiers' => $tiers,
                    'total_participants' => $filteredRegs->count(),
                ];
            }

            // Prefill Judges / Referees (default 3)
            $judges = $selectedComp->judges;
            $judgesList = [
                $judges->get(0)?->name ?? '',
                $judges->get(1)?->name ?? '',
                $judges->get(2)?->name ?? '',
            ];

            // If empty, prefill with PIC or default
            if (empty($judgesList[0]) && $selectedComp->pic) {
                $judgesList[0] = $selectedComp->pic->name;
            }
        }

        $nowDate = Carbon::now();
        $dateSpelled = self::getDateSpelledOut($nowDate);

        $appSettings = AppSetting::pluck('value', 'key')->toArray();

        return view('admin.berita-acara.index', compact(
            'competitions',
            'selectedComp',
            'activeTab',
            'isSports',
            'sectorsData',
            'judgesList',
            'dateSpelled',
            'appSettings'
        ));
    }

    /**
     * Print Official Berita Acara A4 View (Filled / Blank Template)
     */
    public function print(Request $request)
    {
        $competitionId = $request->query('competition_id');
        $competition = Competition::with(['category', 'judges', 'pic'])->findOrFail($competitionId);
        $type = $request->query('type', 'live'); // 'live' (terisi) or 'blank' (template kosong)

        $isSports = self::isSportsCompetition($competition);
        $sectorsDef = self::getCompetitionSectors($competition);

        $sectorFilter = $request->query('sector');
        if ($sectorFilter && isset($sectorsDef[$sectorFilter])) {
            $sectorsDef = [$sectorFilter => $sectorsDef[$sectorFilter]];
        }

        // Date customization from query or current date
        $reqDate = $request->query('date');
        $carbonDate = $reqDate ? Carbon::parse($reqDate) : Carbon::now();
        $dateSpelled = self::getDateSpelledOut($carbonDate);
        
        $eventTime = $request->query('time', '08.00');
        $eventDay = $request->query('day', $dateSpelled['day_name']);

        // Custom Judges / Referees
        $judge1 = $request->query('judge1', $competition->judges->get(0)?->name ?? ($competition->pic?->name ?? ''));
        $judge2 = $request->query('judge2', $competition->judges->get(1)?->name ?? '');
        $judge3 = $request->query('judge3', $competition->judges->get(2)?->name ?? '');
        $judges = [$judge1, $judge2, $judge3];

        $appSettings = AppSetting::pluck('value', 'key')->toArray();

        // Prepare sectors with winners data or blank rows
        $sectorsData = [];
        $regs = $competition->registrations()
            ->where('status', 'verified')
            ->with(['members', 'scores.details'])
            ->get();

        foreach ($sectorsDef as $secKey => $secDef) {
            $tiers = [
                ['tier_label' => 'Juara 1', 'no_peserta' => '', 'nama' => '', 'sekolah' => '', 'nilai' => ''],
                ['tier_label' => 'Juara 2', 'no_peserta' => '', 'nama' => '', 'sekolah' => '', 'nilai' => ''],
                ['tier_label' => 'Juara 3', 'no_peserta' => '', 'nama' => '', 'sekolah' => '', 'nilai' => ''],
                ['tier_label' => 'Juara Harapan 1', 'no_peserta' => '', 'nama' => '', 'sekolah' => '', 'nilai' => ''],
                ['tier_label' => 'Juara Harapan 2', 'no_peserta' => '', 'nama' => '', 'sekolah' => '', 'nilai' => ''],
                ['tier_label' => 'Juara Harapan 3', 'no_peserta' => '', 'nama' => '', 'sekolah' => '', 'nilai' => ''],
            ];

            if ($type === 'live') {
                // If custom winners were submitted via query/form:
                $customTiers = $request->query('winners_' . $secKey);
                if (is_array($customTiers)) {
                    foreach ($tiers as $idx => &$t) {
                        if (isset($customTiers[$idx])) {
                            $t['no_peserta'] = $customTiers[$idx]['no_peserta'] ?? '';
                            $t['nama'] = $customTiers[$idx]['nama'] ?? '';
                            $t['sekolah'] = $customTiers[$idx]['sekolah'] ?? '';
                            $t['nilai'] = $customTiers[$idx]['nilai'] ?? '';
                        }
                    }
                } else {
                    // Auto-rank from system scores
                    $filteredRegs = $regs->filter(function ($r) use ($secDef, $competition) {
                        $isGanda = $r->isGanda();
                        $gender = $r->primary_gender;

                        if ($secDef['is_ganda'] && !$isGanda) return false;
                        if (!$secDef['is_ganda'] && $isGanda && in_array($competition->code, ['BLT', 'TMJ'])) return false;
                        if ($secDef['gender'] !== 'all' && $gender !== $secDef['gender']) return false;

                        if ($secDef['kat'] === 'a') {
                            return $r->isKatA();
                        } elseif ($secDef['kat'] === 'b') {
                            return $r->isKatB();
                        } elseif ($secDef['kat'] === 'c') {
                            return $r->isKatC();
                        }

                        return true;
                    });

                    $ranked = $filteredRegs->map(function ($r) {
                        $lockedScores = $r->scores->where('is_locked', true);
                        $avgScore = $lockedScores->isNotEmpty() ? round($lockedScores->avg('total_score'), 2) : 0;
                        $participantName = $r->pure_name ?: ($r->team_name ?: ($r->members->first()?->full_name ?? ('Peserta #' . $r->id)));
                        $schoolName = $r->display_school ?: ($r->institution_name ?: '-');
                        return [
                            'no_peserta' => $r->participant_number ?: $r->registration_code,
                            'nama' => $participantName,
                            'sekolah' => $schoolName,
                            'nilai' => $avgScore > 0 ? (string) $avgScore : '',
                        ];
                    })->sortByDesc(fn($item) => (float) $item['nilai'])->values();

                    foreach ($tiers as $idx => &$t) {
                        if (isset($ranked[$idx])) {
                            $t['no_peserta'] = $ranked[$idx]['no_peserta'];
                            $t['nama'] = $ranked[$idx]['nama'];
                            $t['sekolah'] = $ranked[$idx]['sekolah'];
                            $t['nilai'] = $ranked[$idx]['nilai'];
                        }
                    }
                }
            }

            $sectorsData[$secKey] = [
                'definition' => $secDef,
                'tiers' => $tiers,
            ];
        }

        return view('admin.berita-acara.print', compact(
            'competition',
            'type',
            'isSports',
            'sectorsData',
            'judges',
            'dateSpelled',
            'eventTime',
            'eventDay',
            'appSettings'
        ));
    }
}
