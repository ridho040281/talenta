<?php

namespace App\Http\Controllers;

use App\Helpers\Terbilang;
use App\Models\AppSetting;
use App\Models\Competition;
use App\Models\Score;
use App\Models\ScoreDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OfficialReportController extends Controller
{
    /**
     * Determine if a competition is Olahraga (Sports) or Seni/Umum (Art/Academic)
     */
    public static function isSportsCompetition(Competition $comp): bool
    {
        return $comp->isSports();
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
                'ganda_pa' => ['title' => 'Kategori Ganda Putra (PA) - Semua Kelas', 'group' => 'Ganda Putra', 'gender' => 'L', 'is_ganda' => true, 'kat' => 'all'],
                'ganda_pi' => ['title' => 'Kategori Ganda Putri (PI) - Semua Kelas', 'group' => 'Ganda Putri', 'gender' => 'P', 'is_ganda' => true, 'kat' => 'all'],
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

        if ($code === 'ROB' || (method_exists($comp, 'isRobotik') && $comp->isRobotik()) || stripos($comp->name, 'robot') !== false) {
            return [
                'rob_sumo' => ['title' => 'Robotik - Kategori Sumo', 'group' => 'Robotik Sumo', 'gender' => 'all', 'is_ganda' => false, 'kat' => 'all', 'rob_cat' => 'Sumo'],
                'rob_soccer' => ['title' => 'Robotik - Kategori Soccer', 'group' => 'Robotik Soccer', 'gender' => 'all', 'is_ganda' => false, 'kat' => 'all', 'rob_cat' => 'Soccer'],
                'rob_kreatif' => ['title' => 'Robotik - Kategori Kreatif', 'group' => 'Robotik Kreatif', 'gender' => 'all', 'is_ganda' => false, 'kat' => 'all', 'rob_cat' => 'Kreatif'],
            ];
        }

        if (in_array($code, ['MTQ', 'POP', 'THF', 'TFID', 'TAH'])
            || str_contains(strtolower($comp->name ?? ''), 'mtq')
            || str_contains(strtolower($comp->name ?? ''), 'tahfid')
            || str_contains(strtolower($comp->name ?? ''), 'pop')) {
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
            'date_formatted' => $date->format('d').' '.$monthName.' '.$date->format('Y'),
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
        $tierFormat = $request->query('tier_format', 'juara_123'); // 'juara_123' (default Juara 1-3) or 'with_harapan'

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
                    if ($secDef['is_ganda'] && ! $isGanda) {
                        return false;
                    }
                    if (! $secDef['is_ganda'] && $isGanda && in_array($selectedComp->code, ['BLT', 'TMJ'])) {
                        return false;
                    }

                    // Match Robotik Category
                    if (! empty($secDef['rob_cat'])) {
                        $cat = $secDef['rob_cat'];

                        return stripos($r->match_type ?? '', $cat) !== false
                            || stripos($r->sub_category ?? '', $cat) !== false
                            || stripos($r->target_class ?? '', $cat) !== false;
                    }

                    // Match Gender
                    if ($secDef['gender'] !== 'all' && $gender !== $secDef['gender']) {
                        return false;
                    }

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

                // Rank winners by average score (participants who have entered scores > 0)
                $ranked = $filteredRegs->map(function ($r) {
                    $validScores = $r->scores->filter(fn ($s) => (bool) $s->is_locked || (float) $s->total_score > 0);
                    $avgScore = $validScores->isNotEmpty() ? round($validScores->avg('total_score'), 2) : 0;
                    $participantName = $r->pure_name ?: ($r->team_name ?: ($r->members->first()?->full_name ?? ('Peserta #'.$r->id)));
                    $schoolName = $r->display_school ?: ($r->institution_name ?: '-');

                    return [
                        'registration' => $r,
                        'participant_number' => $r->participant_number ?: $r->registration_code,
                        'display_name' => $participantName,
                        'institution_name' => $schoolName,
                        'score' => $avgScore > 0 ? $avgScore : '',
                        'has_score' => ($validScores->isNotEmpty() && $avgScore > 0),
                    ];
                })
                    ->filter(fn ($item) => $item['has_score'])
                    ->sortByDesc(fn ($item) => (float) $item['score'])->values();

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
                ];

                if ($tierFormat === 'with_harapan') {
                    $tiers[] = ['tier_label' => 'Juara Harapan 1', 'winner' => $ranked[3] ?? $emptyWinner];
                    $tiers[] = ['tier_label' => 'Juara Harapan 2', 'winner' => $ranked[4] ?? $emptyWinner];
                    $tiers[] = ['tier_label' => 'Juara Harapan 3', 'winner' => $ranked[5] ?? $emptyWinner];
                }

                $sectorsData[$secKey] = [
                    'definition' => $secDef,
                    'tiers' => $tiers,
                    'total_participants' => $filteredRegs->count(),
                    'has_scored_winners' => $ranked->isNotEmpty(),
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
            'tierFormat',
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

        $user = Auth::user();
        if ($user && $user->role === 'pic_lomba') {
            $managedIds = PicController::getManagedCompetitionIds($user);
            if (! in_array($competition->id, $managedIds)) {
                abort(403, 'Akses ditolak: Anda tidak memiliki wewenang untuk membuka berita acara cabang lomba ini.');
            }
        }

        $type = $request->query('type', 'live'); // 'live' (terisi) or 'blank' (template kosong)
        $tierFormat = $request->query('tier_format', 'juara_123'); // 'juara_123' (default Juara 1-3) or 'with_harapan'

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
            ];

            if ($tierFormat === 'with_harapan') {
                $tiers[] = ['tier_label' => 'Juara Harapan 1', 'no_peserta' => '', 'nama' => '', 'sekolah' => '', 'nilai' => ''];
                $tiers[] = ['tier_label' => 'Juara Harapan 2', 'no_peserta' => '', 'nama' => '', 'sekolah' => '', 'nilai' => ''];
                $tiers[] = ['tier_label' => 'Juara Harapan 3', 'no_peserta' => '', 'nama' => '', 'sekolah' => '', 'nilai' => ''];
            }

            if ($type === 'live') {
                // If custom winners were submitted via query/form:
                $customTiers = $request->query('winners_'.$secKey);
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

                        if ($secDef['is_ganda'] && ! $isGanda) {
                            return false;
                        }
                        if (! $secDef['is_ganda'] && $isGanda && in_array($competition->code, ['BLT', 'TMJ'])) {
                            return false;
                        }

                        if (! empty($secDef['rob_cat'])) {
                            $cat = $secDef['rob_cat'];

                            return stripos($r->match_type ?? '', $cat) !== false
                                || stripos($r->sub_category ?? '', $cat) !== false
                                || stripos($r->target_class ?? '', $cat) !== false;
                        }
                        if ($secDef['gender'] !== 'all' && $gender !== $secDef['gender']) {
                            return false;
                        }

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
                        $validScores = $r->scores->filter(fn ($s) => (bool) $s->is_locked || (float) $s->total_score > 0);
                        $avgScore = $validScores->isNotEmpty() ? round($validScores->avg('total_score'), 2) : 0;
                        $participantName = $r->pure_name ?: ($r->team_name ?: ($r->members->first()?->full_name ?? ('Peserta #'.$r->id)));
                        $schoolName = $r->display_school ?: ($r->institution_name ?: '-');

                        return [
                            'no_peserta' => $r->participant_number ?: $r->registration_code,
                            'nama' => $participantName,
                            'sekolah' => $schoolName,
                            'nilai' => $avgScore > 0 ? (string) $avgScore : '',
                            'has_score' => ($validScores->isNotEmpty() && $avgScore > 0),
                        ];
                    })
                        ->filter(fn ($item) => $item['has_score'])
                        ->sortByDesc(fn ($item) => (float) $item['nilai'])->values();

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
            'tierFormat',
            'isSports',
            'sectorsData',
            'judges',
            'dateSpelled',
            'eventTime',
            'eventDay',
            'appSettings'
        ));
    }

    /**
     * Download Official Excel Template for Score Entry
     */
    public function downloadScoreTemplate(Request $request): StreamedResponse
    {
        $competitionId = $request->query('competition_id');
        $competition = Competition::with(['category', 'criteria', 'judges'])->findOrFail($competitionId);

        // Fetch verified registrations
        $regs = $competition->registrations()
            ->where('status', 'verified')
            ->with(['members', 'scores.details'])
            ->orderBy('draw_number', 'asc')
            ->orderBy('participant_number', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $sectorsDef = self::getCompetitionSectors($competition);

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('REKAP_NILAI');

        // Header Title Banner
        $sheet->setCellValue('A1', 'REKAPITULASI PENILAIAN LOMBA - TALENTA 2026 MTsN 1 BLITAR');
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13)->setColor(new Color('064E3B'));

        $sheet->setCellValue('A2', 'CABANG LOMBA: '.strtoupper($competition->name).' ('.strtoupper($competition->code).') | TOTAL PESERTA: '.$regs->count());
        $sheet->mergeCells('A2:H2');
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(11)->setColor(new Color('1E293B'));

        $sheet->setCellValue('A3', 'PETUNJUK: Masukkan angka nilai pada kolom "NILAI_TOTAL" (skala 0 - 100). Jangan mengubah ID_REGISTRASI atau NO_PESERTA agar sistem dapat memvalidasi data otomatis.');
        $sheet->mergeCells('A3:H3');
        $sheet->getStyle('A3')->getFont()->setItalic(true)->setSize(9)->setColor(new Color('475569'));

        // Column Headers
        $criteria = $competition->criteria;
        $hasCriteria = $criteria->isNotEmpty();

        $headers = [
            'A5' => 'NO',
            'B5' => 'ID_REGISTRASI',
            'C5' => 'NO_PESERTA',
            'D5' => 'NAMA_PESERTA',
            'E5' => 'ASAL_SEKOLAH',
            'F5' => 'SEKTOR_KATEGORI',
        ];

        $colIdx = 7; // Column G
        if ($hasCriteria) {
            foreach ($criteria as $crit) {
                $colLetter = Coordinate::stringFromColumnIndex($colIdx);
                $headers[$colLetter.'5'] = 'NILAI: '.strtoupper($crit->name).' ('.$crit->weight_percentage.'%)';
                $colIdx++;
            }
            $colLetter = Coordinate::stringFromColumnIndex($colIdx);
            $headers[$colLetter.'5'] = 'NILAI_TOTAL';
            $colIdx++;
        } else {
            $colLetter = Coordinate::stringFromColumnIndex($colIdx);
            $headers[$colLetter.'5'] = 'NILAI_TOTAL';
            $colIdx++;
        }

        $colLetter = Coordinate::stringFromColumnIndex($colIdx);
        $headers[$colLetter.'5'] = 'CATATAN_JURI';
        $lastColLetter = $colLetter;

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        // Style Headers Row 5
        $headerRange = 'A5:'.$lastColLetter.'5';
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->setColor(new Color('FFFFFF'))->setSize(10);
        $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('1E293B');
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(5)->setRowHeight(28);

        // Fill Participant Data
        $row = 6;
        $no = 1;

        foreach ($regs as $r) {
            $participantName = $r->pure_name ?: ($r->team_name ?: ($r->members->first()?->full_name ?? ('Peserta #'.$r->id)));
            $schoolName = $r->display_school ?: ($r->institution_name ?: '-');
            $noPeserta = $r->participant_number ?: ($r->registration_code ?: ('P-'.str_pad((string) $r->id, 3, '0', STR_PAD_LEFT)));

            // Determine Sector Label
            $sectorLabel = 'Umum';
            foreach ($sectorsDef as $secKey => $secDef) {
                $isGanda = $r->isGanda();
                $gender = $r->primary_gender;
                if ($secDef['is_ganda'] && ! $isGanda) {
                    continue;
                }
                if (! $secDef['is_ganda'] && $isGanda && in_array($competition->code, ['BLT', 'TMJ'])) {
                    continue;
                }
                if (! empty($secDef['rob_cat'])) {
                    $cat = $secDef['rob_cat'];
                    if (stripos($r->match_type ?? '', $cat) === false && stripos($r->sub_category ?? '', $cat) === false && stripos($r->target_class ?? '', $cat) === false) {
                        continue;
                    }
                }
                if ($secDef['gender'] !== 'all' && $gender !== $secDef['gender']) {
                    continue;
                }
                if ($secDef['kat'] === 'a' && ! $r->isKatA()) {
                    continue;
                }
                if ($secDef['kat'] === 'b' && ! $r->isKatB()) {
                    continue;
                }
                if ($secDef['kat'] === 'c' && ! $r->isKatC()) {
                    continue;
                }
                $sectorLabel = $secDef['title'];
                break;
            }

            // Existing score if any
            $existingScore = $r->scores->first();

            $sheet->setCellValue('A'.$row, $no++);
            $sheet->setCellValueExplicit('B'.$row, 'REG-'.$r->id, DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('C'.$row, (string) $noPeserta, DataType::TYPE_STRING);
            $sheet->setCellValue('D'.$row, $participantName);
            $sheet->setCellValue('E'.$row, $schoolName);
            $sheet->setCellValue('F'.$row, $sectorLabel);

            $cIdx = 7;
            if ($hasCriteria) {
                foreach ($criteria as $crit) {
                    $cL = Coordinate::stringFromColumnIndex($cIdx);
                    $critDetail = $existingScore?->details->firstWhere('criterion_id', $crit->id);
                    if ($critDetail) {
                        $sheet->setCellValue($cL.$row, (float) $critDetail->score_value);
                    }
                    $cIdx++;
                }
                $cL = Coordinate::stringFromColumnIndex($cIdx);
                if ($existingScore) {
                    $sheet->setCellValue($cL.$row, (float) $existingScore->total_score);
                }
                $cIdx++;
            } else {
                $cL = Coordinate::stringFromColumnIndex($cIdx);
                if ($existingScore) {
                    $sheet->setCellValue($cL.$row, (float) $existingScore->total_score);
                }
                $cIdx++;
            }

            $cL = Coordinate::stringFromColumnIndex($cIdx);
            if ($existingScore && $existingScore->notes) {
                $sheet->setCellValue($cL.$row, $existingScore->notes);
            }

            // Zebra styling & borders
            $rowRange = 'A'.$row.':'.$lastColLetter.$row;
            if ($row % 2 === 1) {
                $sheet->getStyle($rowRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('F8FAFC');
            }

            $row++;
        }

        if ($row > 6) {
            $dataRange = 'A5:'.$lastColLetter.($row - 1);
            $sheet->getStyle($dataRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('CBD5E1'));

            // Center align specific columns
            $sheet->getStyle('A6:C'.($row - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('F6:'.$lastColLetter.($row - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Highlight score column with a soft emerald background
            $scoreColLetter = $hasCriteria ? Coordinate::stringFromColumnIndex(7 + $criteria->count()) : 'G';
            $sheet->getStyle($scoreColLetter.'5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('047857');
            $sheet->getStyle($scoreColLetter.'6:'.$scoreColLetter.($row - 1))->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('ECFDF5');
        }

        // Auto-fit column widths
        foreach (range('A', $lastColLetter) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $safeName = Str::slug($competition->name);
        $fileName = 'Template_Nilai_'.strtoupper($competition->code).'_'.$safeName.'.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0, no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
        ]);
    }

    /**
     * Import Scores from Excel Spreadsheet
     */
    public function importScores(Request $request)
    {
        $validated = $request->validate([
            'competition_id' => 'required|exists:competitions,id',
            'excel_file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
            'judge_id' => 'nullable|exists:users,id',
            'lock_scores' => 'nullable',
        ]);

        $competition = Competition::with(['criteria', 'judges'])->findOrFail($validated['competition_id']);
        $file = $request->file('excel_file');

        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);

        if (empty($rows)) {
            return redirect()->back()->with('error', 'File Excel kosong atau tidak dapat dibaca.');
        }

        // Find header row (search for ID_REGISTRASI or NO_PESERTA or NILAI)
        $headerRowIndex = null;
        $colMap = [];

        foreach ($rows as $rowIndex => $rowCols) {
            foreach ($rowCols as $colLetter => $val) {
                $cleanVal = strtoupper(trim((string) $val));
                if (str_contains($cleanVal, 'ID_REG') || str_contains($cleanVal, 'ID REGISTRASI')) {
                    $colMap['id_reg'] = $colLetter;
                    $headerRowIndex = $rowIndex;
                } elseif (str_contains($cleanVal, 'NO_PESERTA') || str_contains($cleanVal, 'NO PESERTA')) {
                    $colMap['no_peserta'] = $colLetter;
                    $headerRowIndex = $rowIndex;
                } elseif (str_contains($cleanVal, 'NILAI_TOTAL') || str_contains($cleanVal, 'NILAI TOTAL') || $cleanVal === 'NILAI' || str_contains($cleanVal, 'NILAI AKHIR')) {
                    $colMap['total_score'] = $colLetter;
                } elseif (str_contains($cleanVal, 'CATATAN')) {
                    $colMap['notes'] = $colLetter;
                }

                // Check criteria columns
                if (str_starts_with($cleanVal, 'NILAI:')) {
                    foreach ($competition->criteria as $crit) {
                        if (stripos($cleanVal, $crit->name) !== false) {
                            $colMap['crit_'.$crit->id] = $colLetter;
                        }
                    }
                }
            }

            if ($headerRowIndex !== null && isset($colMap['total_score'])) {
                break;
            }
        }

        if (! $headerRowIndex || (! isset($colMap['id_reg']) && ! isset($colMap['no_peserta'])) || ! isset($colMap['total_score'])) {
            return redirect()->back()->with('error', 'Format file Excel tidak sesuai. Pastikan menggunakan template resmi yang diunduh dari sistem.');
        }

        // Determine Judge ID
        $judgeId = $validated['judge_id'] ?? null;
        if (! $judgeId) {
            $user = Auth::user();
            if ($user->role === 'juri') {
                $judgeId = $user->id;
            } else {
                $assignedJudge = $competition->judges->first();
                $judgeId = $assignedJudge ? $assignedJudge->id : $user->id;
            }
        }

        $isLocked = $request->boolean('lock_scores', true);
        $importedCount = 0;
        $skippedCount = 0;

        // All verified registrations for this competition indexed by id and participant_number
        $registrations = $competition->registrations()
            ->where('status', 'verified')
            ->get();
        $regsById = $registrations->keyBy('id');
        $regsByNo = $registrations->filter(fn ($r) => ! empty($r->participant_number))->keyBy('participant_number');
        $regsByCode = $registrations->keyBy('registration_code');

        DB::beginTransaction();
        try {
            foreach ($rows as $rowIndex => $rowCols) {
                if ($rowIndex <= $headerRowIndex) {
                    continue; // Skip header and above
                }

                $rawIdReg = isset($colMap['id_reg']) ? trim((string) ($rowCols[$colMap['id_reg']] ?? '')) : '';
                $rawNoPeserta = isset($colMap['no_peserta']) ? trim((string) ($rowCols[$colMap['no_peserta']] ?? '')) : '';
                $rawTotalScore = isset($colMap['total_score']) ? trim((string) ($rowCols[$colMap['total_score']] ?? '')) : '';
                $notes = isset($colMap['notes']) ? trim((string) ($rowCols[$colMap['notes']] ?? '')) : null;

                // Strip non-digit characters from REG- prefix if present
                $idNumber = preg_replace('/[^0-9]/', '', $rawIdReg);

                $registration = null;
                if ($idNumber && isset($regsById[$idNumber])) {
                    $registration = $regsById[$idNumber];
                } elseif ($rawNoPeserta && isset($regsByNo[$rawNoPeserta])) {
                    $registration = $regsByNo[$rawNoPeserta];
                } elseif ($rawNoPeserta && isset($regsByCode[$rawNoPeserta])) {
                    $registration = $regsByCode[$rawNoPeserta];
                }

                if (! $registration) {
                    $skippedCount++;

                    continue;
                }

                // If criteria values are present, calculate total score
                $totalScore = null;
                $hasCriteriaScores = false;
                $critScores = [];

                foreach ($competition->criteria as $crit) {
                    if (isset($colMap['crit_'.$crit->id])) {
                        $critVal = trim((string) ($rowCols[$colMap['crit_'.$crit->id]] ?? ''));
                        if ($critVal !== '' && is_numeric($critVal)) {
                            $critScores[$crit->id] = (float) $critVal;
                            $hasCriteriaScores = true;
                        }
                    }
                }

                if ($hasCriteriaScores && $competition->criteria->isNotEmpty()) {
                    $weightedTotal = 0;
                    $totalWeight = $competition->criteria->sum('weight_percentage') ?: 100;
                    foreach ($competition->criteria as $crit) {
                        $cVal = $critScores[$crit->id] ?? 0;
                        $weightedTotal += ($cVal * ($crit->weight_percentage / $totalWeight));
                    }
                    $totalScore = round($weightedTotal, 2);
                } elseif ($rawTotalScore !== '' && is_numeric(str_replace(',', '.', $rawTotalScore))) {
                    $totalScore = round((float) str_replace(',', '.', $rawTotalScore), 2);
                }

                if ($totalScore === null) {
                    $skippedCount++;

                    continue; // Skip rows without score
                }

                // Create or Update Score
                $score = Score::updateOrCreate(
                    [
                        'competition_id' => $competition->id,
                        'registration_id' => $registration->id,
                        'judge_id' => $judgeId,
                    ],
                    [
                        'total_score' => $totalScore,
                        'is_locked' => $isLocked,
                        'notes' => $notes ?: ('Import Excel oleh '.Auth::user()->name.' pada '.Carbon::now()->translatedFormat('d/m/Y H:i')),
                    ]
                );

                // Save criteria details if applicable
                foreach ($critScores as $critId => $cVal) {
                    ScoreDetail::updateOrCreate(
                        [
                            'score_id' => $score->id,
                            'criterion_id' => $critId,
                        ],
                        [
                            'score_value' => $cVal,
                        ]
                    );
                }

                $importedCount++;
            }

            DB::commit();
            Cache::forget('talenta_admin_recap_summary_data');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Import score error: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return redirect()->back()->with('error', 'Terjadi kesalahan saat memproses file Excel: '.$e->getMessage());
        }

        $msg = "Berhasil mengimpor {$importedCount} nilai peserta untuk cabang {$competition->name}!";
        if ($skippedCount > 0) {
            $msg .= " ({$skippedCount} baris kosong/tidak valid dilewati)";
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'imported_count' => $importedCount,
                'skipped_count' => $skippedCount,
            ]);
        }

        return redirect()->route('admin.berita-acara.index', ['competition_id' => $competition->id])
            ->with('success', $msg);
    }
}
