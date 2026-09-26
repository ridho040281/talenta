<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\BadmintonMatch;
use App\Models\Competition;
use App\Traits\CompetitionPoolTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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
                if (method_exists($user, 'managesBadminton') && $user->managesBadminton()) {
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
            $isAuthorizedSport = (method_exists($user, 'managesTournamentBracket') && $user->managesTournamentBracket()) && (
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

        if (! in_array($user->role, ['superadmin', 'panitia']) && ! (method_exists($user, 'managesTournamentBracket') && $user->managesTournamentBracket())) {
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
            $isAuthorizedBadminton = (method_exists($user, 'managesBadminton') && $user->managesBadminton()) && (
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
     * Export Rekap Jadwal & Order of Play ke Microsoft Excel (.xlsx)
     */
    public function exportScheduleExcel(Request $request, $competition_id)
    {
        $competition = Competition::with(['category', 'registrations.members'])->findOrFail($competition_id);
        $this->ensureIsBadminton($competition);
        $user = Auth::user();

        if ($user && ! in_array($user->role, ['superadmin', 'panitia'])) {
            $managedIds = PicController::getManagedCompetitionIds($user);
            $isAuthorizedBadminton = (method_exists($user, 'managesBadminton') && $user->managesBadminton()) && (
                strtoupper($competition->code ?? '') === 'BLT' ||
                str_contains(strtolower($competition->name ?? ''), 'bulu tangkis') ||
                str_contains(strtolower($competition->name ?? ''), 'badminton')
            );

            if (! in_array($competition->id, $managedIds) && ! $isAuthorizedBadminton) {
                abort(403, 'Akses ditolak.');
            }
        }

        // 1. Ambil data pertandingan
        $useSimulation = $request->boolean('use_simulation');
        $dbMatches = BadmintonMatch::where('competition_id', $competition->id)
            ->where('court_number', '!=', 'BYE')
            ->orderBy('match_day')
            ->orderBy('court_number')
            ->orderBy('scheduled_time')
            ->orderBy('match_order')
            ->get();

        $matchesList = [];

        if (! $useSimulation && $dbMatches->isNotEmpty()) {
            foreach ($dbMatches as $m) {
                $categoryLabel = match ($m->category) {
                    'MS' => 'Tunggal Putra',
                    'WS' => 'Tunggal Putri',
                    'MD' => 'Ganda Putra',
                    'WD' => 'Ganda Putri',
                    'XD' => 'Ganda Campuran',
                    default => $m->category ?: 'Bulu Tangkis',
                };

                $scoreText = '-';
                if ($m->match_status === 'finished') {
                    $scores = [];
                    if ($m->team1_set1 || $m->team2_set1) {
                        $scores[] = "{$m->team1_set1}-{$m->team2_set1}";
                    }
                    if ($m->team1_set2 || $m->team2_set2) {
                        $scores[] = "{$m->team1_set2}-{$m->team2_set2}";
                    }
                    if ($m->team1_set3 || $m->team2_set3) {
                        $scores[] = "{$m->team1_set3}-{$m->team2_set3}";
                    }
                    $scoreText = ! empty($scores) ? implode(', ', $scores) : 'Selesai';
                } elseif ($m->match_status === 'ongoing') {
                    $scoreText = 'Sedang Main';
                }

                $matchesList[] = [
                    'match_day' => (int) ($m->match_day ?: 1),
                    'match_day_label' => $m->match_day_label ?: "Hari {$m->match_day}",
                    'match_date' => $m->match_date?->format('Y-m-d'),
                    'court_number' => $m->court_number ?: 'Lapangan 1',
                    'scheduled_time' => $m->scheduled_time ? $m->scheduled_time.' WIB' : '-',
                    'match_order' => $m->match_order ? '#'.$m->match_order : '-',
                    'category' => $categoryLabel,
                    'round_name' => $m->round_name ?: 'Babak 1',
                    'team1_player' => $m->team1_player1 ?: 'Menunggu Pemenang',
                    'team1_school' => $m->team1_school ?: 'TBD',
                    'team2_player' => $m->team2_player1 ?: 'Menunggu Pemenang',
                    'team2_school' => $m->team2_school ?: 'TBD',
                    'status' => $m->match_status === 'finished' ? 'Selesai' : ($m->match_status === 'ongoing' ? 'Sedang Main' : 'Belum Main'),
                    'score' => $scoreText,
                ];
            }
        } else {
            // Ambil dari simulasi jadwal multi-hari
            $plan = $this->buildMultiDaySchedulePlan($competition, $request->all());
            foreach ($plan['matches'] as $m) {
                if (! $m['is_contested'] || $m['court_number'] === 'BYE') {
                    continue;
                }
                $matchesList[] = [
                    'match_day' => (int) ($m['match_day'] ?: 1),
                    'match_day_label' => $m['match_day_label'] ?: "Hari {$m['match_day']}",
                    'match_date' => $m['match_date'],
                    'court_number' => $m['court_number'],
                    'scheduled_time' => $m['scheduled_time'] ? $m['scheduled_time'].' WIB' : '-',
                    'match_order' => $m['match_order'] ? '#'.$m['match_order'] : '-',
                    'category' => $m['pool_title'] ?: $m['category'],
                    'round_name' => $m['round_name'],
                    'team1_player' => $m['team1_player'] ?: 'Menunggu Pemenang',
                    'team1_school' => $m['team1_school'] ?: 'TBD',
                    'team2_player' => $m['team2_player'] ?: 'Menunggu Pemenang',
                    'team2_school' => $m['team2_school'] ?: 'TBD',
                    'status' => $m['status'] === 'finished' ? 'Selesai' : ($m['status'] === 'ongoing' ? 'Sedang Main' : 'Belum Main'),
                    'score' => '-',
                ];
            }
        }

        // 2. Buat Dokumen Excel dengan PhpSpreadsheet
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Order of Play');

        // Page setup
        $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);

        // Header Title
        $sheet->setCellValue('A1', 'REKAPITULASI JADWAL PERTANDINGAN (ORDER OF PLAY)');
        $sheet->setCellValue('A2', strtoupper($competition->name));
        $sheet->setCellValue('A3', 'Diterbitkan: '.Carbon::now()->locale('id')->isoFormat('dddd, D MMMM Y - HH:mm').' WIB • Total: '.count($matchesList).' Partai Pertandingan');

        $sheet->mergeCells('A1:L1');
        $sheet->mergeCells('A2:L2');
        $sheet->mergeCells('A3:L3');

        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->setColor(new Color('FF0F172A'));
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(11)->setColor(new Color('FF2563EB'));
        $sheet->getStyle('A3')->getFont()->setItalic(true)->setSize(9)->setColor(new Color('FF64748B'));

        $sheet->getStyle('A1:L3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Table Header
        $headers = [
            'A5' => 'NO',
            'B5' => 'HARI & TANGGAL',
            'C5' => 'JAM MAIN',
            'D5' => 'LAPANGAN',
            'E5' => 'PARTAI',
            'F5' => 'SEKTOR / KATEGORI',
            'G5' => 'BABAK',
            'H5' => 'PEMAIN 1',
            'I5' => 'ASAL SEKOLAH 1',
            'J5' => 'VS',
            'K5' => 'PEMAIN 2',
            'L5' => 'ASAL SEKOLAH 2',
        ];

        foreach ($headers as $cell => $title) {
            $sheet->setCellValue($cell, $title);
        }

        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 10,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '0F172A'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '334155'],
                ],
            ],
        ];
        $sheet->getStyle('A5:L5')->applyFromArray($headerStyle);
        $sheet->getRowDimension(5)->setRowHeight(26);

        // Data Rows
        $row = 6;
        $no = 1;
        $currentDay = null;

        foreach ($matchesList as $m) {
            // Group header when Day changes
            if ($currentDay !== $m['match_day']) {
                $currentDay = $m['match_day'];
                $dayTitle = '📅 '.strtoupper($m['match_day_label']);

                $sheet->setCellValue("A{$row}", $dayTitle);
                $sheet->mergeCells("A{$row}:L{$row}");
                $sheet->getStyle("A{$row}:L{$row}")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => '0F172A'],
                        'size' => 10,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'E2E8F0'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'indent' => 1,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'CBD5E1'],
                        ],
                    ],
                ]);
                $sheet->getRowDimension($row)->setRowHeight(22);
                $row++;
            }

            $sheet->setCellValue("A{$row}", $no++);
            $sheet->setCellValue("B{$row}", $m['match_day_label']);
            $sheet->setCellValue("C{$row}", $m['scheduled_time']);
            $sheet->setCellValue("D{$row}", $m['court_number']);
            $sheet->setCellValue("E{$row}", $m['match_order']);
            $sheet->setCellValue("F{$row}", $m['category']);
            $sheet->setCellValue("G{$row}", $m['round_name']);
            $sheet->setCellValue("H{$row}", $m['team1_player']);
            $sheet->setCellValue("I{$row}", $m['team1_school']);
            $sheet->setCellValue("J{$row}", 'VS');
            $sheet->setCellValue("K{$row}", $m['team2_player']);
            $sheet->setCellValue("L{$row}", $m['team2_school']);

            $isZebra = ($no % 2 === 0);
            $rowBg = $isZebra ? 'F8FAFC' : 'FFFFFF';

            $sheet->getStyle("A{$row}:L{$row}")->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => $rowBg],
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'E2E8F0'],
                    ],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);

            // Specific cell alignments
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("D{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("E{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("G{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("J{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("J{$row}")->getFont()->setBold(true)->getColor()->setRGB('D97706');

            $sheet->getRowDimension($row)->setRowHeight(20);
            $row++;
        }

        // Auto size columns with min width
        $colWidths = [
            'A' => 6,
            'B' => 24,
            'C' => 13,
            'D' => 15,
            'E' => 10,
            'F' => 26,
            'G' => 18,
            'H' => 26,
            'I' => 22,
            'J' => 6,
            'K' => 26,
            'L' => 22,
        ];
        foreach ($colWidths as $col => $w) {
            $sheet->getColumnDimension($col)->setWidth($w);
        }

        // Freeze pane below header
        $sheet->freezePane('A6');

        $slugName = Str::slug($competition->name ?: 'Turnamen');
        $fileName = "Jadwal_Pertandingan_{$slugName}_".date('Ymd_His').'.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
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
            $isAuthorizedBadminton = (method_exists($user, 'managesBadminton') && $user->managesBadminton()) && (
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
     * Preview Jadwal Multi-Hari Terpadu sebelum disimpan ke database (AJAX)
     */
    public function previewSchedule(Request $request, $competition_id)
    {
        $competition = Competition::with(['category', 'registrations.members'])->findOrFail($competition_id);
        $this->ensureIsBadminton($competition);
        $user = Auth::user();

        if (! in_array($user->role, ['superadmin', 'panitia'])) {
            $managedIds = PicController::getManagedCompetitionIds($user);
            $isAuthorizedBadminton = (method_exists($user, 'managesBadminton') && $user->managesBadminton()) && (
                strtoupper($competition->code ?? '') === 'BLT' ||
                str_contains(strtolower($competition->name ?? ''), 'bulu tangkis') ||
                str_contains(strtolower($competition->name ?? ''), 'badminton')
            );

            if (! in_array($competition->id, $managedIds) && ! $isAuthorizedBadminton) {
                return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
            }
        }

        $plan = $this->buildMultiDaySchedulePlan($competition, $request->all());

        return response()->json([
            'success' => true,
            'summary' => $plan['summary'],
            'days' => $plan['days'],
            'matches' => $plan['matches'],
            'total_matches' => count($plan['matches']),
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
            $isAuthorizedBadminton = (method_exists($user, 'managesBadminton') && $user->managesBadminton()) && (
                strtoupper($competition->code ?? '') === 'BLT' ||
                str_contains(strtolower($competition->name ?? ''), 'bulu tangkis') ||
                str_contains(strtolower($competition->name ?? ''), 'badminton')
            );

            if (! in_array($competition->id, $managedIds) && ! $isAuthorizedBadminton) {
                return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
            }
        }

        $incomingMatches = $request->input('matches');
        $planMatches = [];
        $planSummary = '';

        if (! empty($incomingMatches) && is_array($incomingMatches)) {
            $planMatches = $incomingMatches;
            $planSummary = $request->input('summary', 'Jadwal Kustom Panitia');
        } else {
            $plan = $this->buildMultiDaySchedulePlan($competition, $request->all());
            if (empty($plan['matches'])) {
                return response()->json(['success' => false, 'message' => 'Tidak ada pertandingan yang dapat dijadwalkan.'], 422);
            }
            $planMatches = $plan['matches'];
            $planSummary = $plan['summary'];
        }

        $syncedCount = 0;
        foreach ($planMatches as $m) {
            $existingRecord = BadmintonMatch::where('competition_id', $competition->id)
                ->where('match_code', $m['match_code'])
                ->first();

            $status = $m['status'];
            $winnerTeam = $m['winner_team'];
            if ($existingRecord && in_array($existingRecord->match_status, ['ongoing', 'finished'])) {
                $status = $existingRecord->match_status;
                $winnerTeam = $existingRecord->winner_team;
            }

            // Lindungi nama atlet definitif jika data hasil pertandingan babak sebelumnya sudah ada di database
            $team1Player = $m['team1_player'] ?? 'Menunggu Pemenang';
            $team1School = $m['team1_school'] ?? 'TBD';
            $team1Id = $m['team1_id'] ?? null;
            if ($existingRecord && ! empty($existingRecord->team1_registration_id)) {
                $team1Id = $existingRecord->team1_registration_id;
                $team1School = $existingRecord->team1_school;
                $team1Player = $existingRecord->team1_player1;
            }

            $team2Player = $m['team2_player'] ?? 'Menunggu Pemenang';
            $team2School = $m['team2_school'] ?? 'TBD';
            $team2Id = $m['team2_id'] ?? null;
            if ($existingRecord && ! empty($existingRecord->team2_registration_id)) {
                $team2Id = $existingRecord->team2_registration_id;
                $team2School = $existingRecord->team2_school;
                $team2Player = $existingRecord->team2_player1;
            }

            BadmintonMatch::updateOrCreate(
                [
                    'competition_id' => $competition->id,
                    'match_code' => $m['match_code'],
                ],
                [
                    'court_number' => $m['court_number'],
                    'scheduled_time' => $m['scheduled_time'],
                    'match_order' => $m['match_order'],
                    'match_day' => $m['match_day'],
                    'match_date' => $m['match_date'],
                    'match_day_label' => $m['match_day_label'],
                    'round_name' => $m['round_name'],
                    'category' => $m['category'],
                    'match_type' => $m['match_type'],
                    'team1_registration_id' => $team1Id,
                    'team1_school' => $team1School,
                    'team1_player1' => $team1Player,
                    'team2_registration_id' => $team2Id,
                    'team2_school' => $team2School,
                    'team2_player1' => $team2Player,
                    'match_status' => $status,
                    'winner_team' => $winnerTeam,
                ]
            );

            $syncedCount++;
        }

        return response()->json([
            'success' => true,
            'message' => "Berhasil menyinkronkan {$syncedCount} pertandingan ke jadwal ({$plan['summary']})!",
            'total_synced' => $syncedCount,
        ]);
    }

    /**
     * Kosongkan alokasi waktu dan lapangan pertandingan (Reset Jadwal)
     * Hanya berlaku untuk pertandingan yang belum berjalan (status upcoming).
     * Pertandingan yang sudah finished/ongoing TIDAK AKAN di-reset demi integritas skor wasit.
     */
    public function resetSchedule(Request $request, $competition_id)
    {
        $competition = Competition::findOrFail($competition_id);
        $this->ensureIsBadminton($competition);
        $user = Auth::user();

        if ($user && ! in_array($user->role, ['superadmin', 'panitia'])) {
            $managedIds = PicController::getManagedCompetitionIds($user);
            $isAuthorizedBadminton = (method_exists($user, 'managesBadminton') && $user->managesBadminton()) && (
                strtoupper($competition->code ?? '') === 'BLT' ||
                str_contains(strtolower($competition->name ?? ''), 'bulu tangkis') ||
                str_contains(strtolower($competition->name ?? ''), 'badminton')
            );

            if (! in_array($competition->id, $managedIds) && ! $isAuthorizedBadminton) {
                return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
            }
        }

        $scope = $request->input('scope', 'all');
        $targetPoolKey = $request->input('pool_key');

        $query = BadmintonMatch::where('competition_id', $competition->id)
            ->whereNotIn('match_status', ['ongoing', 'finished']);

        if ($scope === 'pool' && ! empty($targetPoolKey)) {
            $query->where('match_code', 'like', "{$targetPoolKey}-%");
        }

        $resetCount = $query->update([
            'scheduled_time' => null,
            'match_order' => null,
            'match_day' => null,
            'match_date' => null,
            'match_day_label' => null,
            'court_number' => 'Lapangan 1',
        ]);

        return response()->json([
            'success' => true,
            'message' => "Berhasil mengosongkan jadwal untuk {$resetCount} pertandingan! Anda dapat mengatur ulang jadwal dari awal.",
            'reset_count' => $resetCount,
        ]);
    }

    /**
     * Membangun rencana jadwal multi-hari terpadu (dipakai Preview dan Generate)
     */
    protected function buildMultiDaySchedulePlan(Competition $competition, array $input): array
    {
        $scope = $input['scope'] ?? 'all';
        $targetPoolKey = $input['pool_key'] ?? null;
        $allPools = $this->buildCompetitionPools($competition);

        if ($scope === 'pool' && $targetPoolKey) {
            $poolsToProcess = collect($allPools)->where('key', $targetPoolKey)->values()->all();
        } else {
            $poolsToProcess = $allPools;
        }

        $rawCourts = $input['courts'] ?? ['Lapangan 1', 'Lapangan 2'];
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

        $tournamentDays = max(1, min(7, (int) ($input['tournament_days'] ?? 4)));
        $rawStartDate = $input['start_date'] ?? null;
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
        if (! $startDate) {
            $startDate = Carbon::parse('2026-09-29');
        }

        $startTime = $input['start_time'] ?? '08:00';
        if (! preg_match('/^\d{1,2}:\d{2}$/', $startTime)) {
            $startTime = '08:00';
        }

        $endTime = $input['end_time'] ?? '18:00';
        if (! preg_match('/^\d{1,2}:\d{2}$/', $endTime)) {
            $endTime = '18:00';
        }

        $matchDuration = max(10, (int) ($input['match_duration'] ?? 20));
        $semifinalDuration = max(10, (int) ($input['semifinal_duration'] ?? 30));
        $distributionMode = $input['distribution_mode'] ?? 'category_based'; // 'category_based' | 'custom' | 'even'
        $customRules = $input['custom_rules'] ?? [];
        if (is_string($customRules)) {
            try {
                $customRules = json_decode($customRules, true) ?: [];
            } catch (\Throwable $e) {
                $customRules = [];
            }
        }
        $lunchBreak = ! empty($input['lunch_break'] ?? true);
        $lunchStart = $input['lunch_start'] ?? '12:00';
        $lunchEnd = $input['lunch_end'] ?? '13:00';
        $fridayBreak = ! empty($input['friday_break'] ?? true);

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

        $rawMatchesList = [];

        foreach ($poolsToProcess as $pool) {
            $poolKey = $pool['key'];
            $poolTitle = $pool['title'];
            $categoryCode = stripos($poolTitle, 'putri') !== false ? 'WS' : 'MS';
            if (stripos($poolTitle, 'ganda') !== false) {
                $categoryCode = stripos($poolTitle, 'putri') !== false ? 'WD' : 'MD';
                if (stripos($poolTitle, 'campuran') !== false || stripos($poolTitle, 'mix') !== false) {
                    $categoryCode = 'XD';
                }
            }
            $matchType = stripos($poolTitle, 'ganda') !== false ? 'double' : 'single';

            $bData = $this->buildTournamentTree($pool['participants'], $competition, $poolKey);
            if (empty($bData['rounds'])) {
                continue;
            }

            // Lapangan 1: Kelas 5-6 (kat_c) dan Ganda
            // Lapangan 2: Kelas 1-2 (kat_a) dan Kelas 3-4 (kat_b)
            $isUpper = str_contains($poolKey, 'kat_c') ||
                       stripos($poolTitle, 'kelas 5') !== false ||
                       stripos($poolTitle, 'ganda') !== false;

            $assignedCourt = $courts[0] ?? 'Lapangan 1';
            if ($distributionMode === 'category_based') {
                $assignedCourt = $isUpper ? ($courts[0] ?? 'Lapangan 1') : ($courts[1] ?? ($courts[0] ?? 'Lapangan 1'));
            } elseif ($distributionMode === 'custom' && ! empty($customRules[$poolKey]['court'])) {
                $assignedCourt = $customRules[$poolKey]['court'];
                if (! in_array($assignedCourt, $courts)) {
                    $assignedCourt = $courts[0] ?? 'Lapangan 1';
                }
            }

            // 1. Play-offs
            if (! empty($bData['playoffs']['has_playoffs']) && ! empty($bData['playoffs']['matches'])) {
                foreach ($bData['playoffs']['matches'] as $poIdx => $poMatch) {
                    $t1 = $poMatch['team1'] ?? null;
                    $t2 = $poMatch['team2'] ?? null;
                    if (! $t1 && ! $t2) {
                        continue;
                    }

                    $rawMatchesList[] = [
                        'pool_key' => $poolKey,
                        'pool_title' => $poolTitle,
                        'category' => $categoryCode,
                        'match_type' => $matchType,
                        'match_code' => $poMatch['match_code'],
                        'round_name' => 'Play-off Kualifikasi',
                        'round_type' => 'playoff',
                        'round_index' => 0,
                        'total_rounds' => count($bData['rounds']),
                        'assigned_court' => $assignedCourt,
                        'team1' => $t1,
                        'team2' => $t2,
                        'is_bye1' => false,
                        'is_bye2' => false,
                        'is_contested' => true,
                        'status' => $poMatch['status'] ?? 'upcoming',
                        'winner_team' => ! empty($poMatch['winner']) ? (($poMatch['winner'] == $t1) ? 1 : 2) : null,
                    ];
                }
            }

            // 2. Bracket Rounds (Babak Penyisihan, 16 Besar, Perempat Final, Semifinal, dan Grand Final)
            $totalRounds = count($bData['rounds']);
            foreach ($bData['rounds'] as $round) {
                $rIdx = (int) ($round['round_index'] ?? 1);
                $rName = $round['round_name'];

                $isFinal = ($rIdx === $totalRounds);
                $isSemi = ($rIdx === $totalRounds - 1);
                $isQf = ($rIdx === $totalRounds - 2);
                $is16B = ($rIdx === $totalRounds - 3);

                $roundType = 'prelim';
                if ($isFinal) {
                    $roundType = 'final';
                } elseif ($isSemi) {
                    $roundType = 'semifinal';
                } elseif ($isQf) {
                    $roundType = 'qf';
                } elseif ($is16B) {
                    $roundType = '16b';
                }

                foreach ($round['matches'] as $m) {
                    $t1 = $m['team1'] ?? null;
                    $t2 = $m['team2'] ?? null;

                    if ($rIdx === 1) {
                        $isBye1 = ! empty($m['is_bye1']);
                        $isBye2 = ! empty($m['is_bye2']);

                        // Jika kedua slot kosong atau keduanya BYE (tidak ada partai riil)
                        if (($isBye1 && $isBye2) || (! $t1 && ! $t2)) {
                            continue;
                        }

                        $isContested = (! $isBye1 && ! $isBye2);
                        $status = 'upcoming';
                        $winnerTeam = null;
                        if ($t1 && ! $isBye1 && $isBye2) {
                            $status = 'finished';
                            $winnerTeam = 1;
                        } elseif ($t2 && ! $isBye2 && $isBye1) {
                            $status = 'finished';
                            $winnerTeam = 2;
                        }
                    } else {
                        // Babak 2, QF, Semifinal, Final adalah pertandingan resmi yang WAJIB dijadwalkan
                        $isBye1 = false;
                        $isBye2 = false;
                        $isContested = true;
                        $status = 'upcoming';
                        $winnerTeam = null;

                        if (! empty($m['existing_match'])) {
                            $em = $m['existing_match'];
                            if (in_array($em->match_status, ['ongoing', 'finished'])) {
                                $status = $em->match_status;
                                $winnerTeam = $em->winner_team;
                            }
                        }

                        // Buat label placeholder resmi jika pemenang babak sebelumnya belum bertanding
                        $mIdx = (int) ($m['match_index'] ?? 1);
                        $prevRoundShort = $isFinal ? 'SF' : ($isSemi ? 'QF' : ($isQf ? '16B' : 'R'.($rIdx - 1)));
                        if (! $t1 || empty($t1['name'])) {
                            $prevM1 = ($mIdx * 2) - 1;
                            $t1 = [
                                'id' => null,
                                'name' => "Pemenang {$prevRoundShort} #{$prevM1}",
                                'institution' => 'Menunggu Pemenang',
                                'is_placeholder' => true,
                            ];
                        }
                        if (! $t2 || empty($t2['name'])) {
                            $prevM2 = $mIdx * 2;
                            $t2 = [
                                'id' => null,
                                'name' => "Pemenang {$prevRoundShort} #{$prevM2}",
                                'institution' => 'Menunggu Pemenang',
                                'is_placeholder' => true,
                            ];
                        }
                    }

                    $rawMatchesList[] = [
                        'pool_key' => $poolKey,
                        'pool_title' => $poolTitle,
                        'category' => $categoryCode,
                        'match_type' => $matchType,
                        'match_code' => $m['match_code'],
                        'round_name' => $rName,
                        'round_type' => $roundType,
                        'round_index' => $rIdx,
                        'total_rounds' => $totalRounds,
                        'assigned_court' => $assignedCourt,
                        'team1' => $t1,
                        'team2' => $t2,
                        'is_bye1' => $isBye1,
                        'is_bye2' => $isBye2,
                        'is_contested' => $isContested,
                        'status' => $status,
                        'winner_team' => $winnerTeam,
                    ];
                }
            }
        }

        // Map Match to Day
        $poolContestedCounter = [];
        $getDayForMatch = function ($m) use ($tournamentDays, $distributionMode, $customRules, &$poolContestedCounter) {
            if ($tournamentDays <= 1) {
                return 1;
            }

            $rType = $m['round_type'];
            $pk = $m['pool_key'];

            if ($rType === 'playoff') {
                return 1;
            }

            // Mode Kustom Kuota Per Kategori untuk babak awal (Prelim / 16B Round 1)
            if ($distributionMode === 'custom' && ! empty($customRules[$pk])) {
                $rule = $customRules[$pk];
                $cQuota = ! empty($rule['quota_day1']) ? (int) $rule['quota_day1'] : 0;
                $cDay = ! empty($rule['day']) ? (int) $rule['day'] : 1;

                $isFirstRound = ($rType === 'prelim' || ($rType === '16b' && (int) ($m['round_index'] ?? 1) === 1));
                if ($isFirstRound) {
                    if ($cQuota > 0 && ! empty($m['is_contested'])) {
                        $poolContestedCounter[$pk] = ($poolContestedCounter[$pk] ?? 0) + 1;
                        if ($poolContestedCounter[$pk] <= $cQuota) {
                            return $cDay;
                        }

                        return min($tournamentDays, $cDay + 1);
                    }

                    return $cDay;
                }
            }

            if ($tournamentDays === 2) {
                return in_array($rType, ['semifinal', 'final']) ? 2 : 1;
            }

            if ($tournamentDays === 3) {
                if ($rType === 'final' || $rType === 'semifinal') {
                    return 3;
                }
                if ($rType === 'qf') {
                    return 2;
                }
                if ($rType === '16b') {
                    return ((int) ($m['round_index'] ?? 1) === 1) ? 1 : 2;
                }

                return 1;
            }

            if ($tournamentDays === 4) {
                if ($rType === 'final') {
                    return 4;
                }
                if ($rType === 'semifinal') {
                    return 3;
                }
                if ($rType === 'qf') {
                    // Kelas 5-6 (kat_c) dan Ganda QF dimainkan Hari 3 Pagi sebelum Semifinal
                    // Kelas 1-2 (kat_a) dan Kelas 3-4 (kat_b) QF dimainkan Hari 2 Siang
                    if (str_contains($m['pool_key'], 'kat_c') || str_contains($m['pool_key'], 'ganda')) {
                        return 3;
                    }

                    return 2;
                }
                if ($rType === '16b') {
                    // Babak 1 pada bagan 16 besar (Round 1) dimainkan Hari 1 Siang
                    // Babak 2 pada bagan 32 besar (Round 2) dimainkan Hari 2 Pagi
                    if ((int) ($m['round_index'] ?? 1) === 1) {
                        return 1;
                    }

                    return 2;
                }

                return 1; // 32 Besar / Penyisihan Awal / Playoff
            }

            // 5+ Hari
            if ($rType === 'final') {
                return $tournamentDays;
            }
            if ($rType === 'semifinal') {
                return max(1, $tournamentDays - 1);
            }
            if ($rType === 'qf') {
                return max(1, $tournamentDays - 2);
            }
            if ($rType === '16b') {
                return ((int) ($m['round_index'] ?? 1) === 1) ? max(1, $tournamentDays - 4) : max(1, $tournamentDays - 3);
            }

            return 1; // 32 Besar / Prelim / Playoff
        };

        // Priority comparator per day
        $getPriority = function ($m, $day) use ($distributionMode) {
            $pk = $m['pool_key'];
            $rt = $m['round_type'];

            // Jika mode kustom, urutkan pertandingan berdasarkan Lapangan terlebih dahulu, lalu urutan Babak
            if ($distributionMode === 'custom') {
                $courtOrder = str_contains($m['assigned_court'] ?? '', '1') ? 10 : (str_contains($m['assigned_court'] ?? '', '2') ? 20 : 30);
                $roundOrder = match ($rt) {
                    'playoff' => 1,
                    'prelim' => 2,
                    '16b' => 3,
                    'qf' => 4,
                    'semifinal' => 5,
                    'final' => 6,
                    default => 7,
                };

                return ($courtOrder * 10) + $roundOrder;
            }

            if ($day === 1) {
                // Lapangan 1: Kat C Pi prelim -> Kat C Pa prelim -> Ganda 16b (R1)
                if (str_contains($pk, 'kat_c_pi')) {
                    return 10;
                }
                if (str_contains($pk, 'kat_c_pa')) {
                    return 20;
                }
                if (str_contains($pk, 'ganda')) {
                    return 25;
                }
                // Lapangan 2: Kat B Pa prelim -> Kat B Pi 16b (R1) -> Kat A Pa 16b (R1) -> Kat A Pi 16b (R1)
                if (str_contains($pk, 'kat_b_pa')) {
                    return 30;
                }
                if (str_contains($pk, 'kat_b_pi')) {
                    return 40;
                }
                if (str_contains($pk, 'kat_a_pa')) {
                    return 50;
                }
                if (str_contains($pk, 'kat_a_pi')) {
                    return 60;
                }

                return 70;
            }

            if ($day === 2) {
                // Lapangan 1: Kat C Pi 16b -> Kat C Pa 16b
                if (str_contains($pk, 'kat_c_pi') && $rt === '16b') {
                    return 10;
                }
                if (str_contains($pk, 'kat_c_pa') && $rt === '16b') {
                    return 20;
                }
                // Lapangan 2: Kat B Pa 16b -> QF Kat A Pi -> QF Kat A Pa -> QF Kat B Pi -> QF Kat B Pa
                if (str_contains($pk, 'kat_b_pa') && $rt === '16b') {
                    return 30;
                }
                if (str_contains($pk, 'kat_a_pi') && $rt === 'qf') {
                    return 40;
                }
                if (str_contains($pk, 'kat_a_pa') && $rt === 'qf') {
                    return 50;
                }
                if (str_contains($pk, 'kat_b_pi') && $rt === 'qf') {
                    return 60;
                }
                if (str_contains($pk, 'kat_b_pa') && $rt === 'qf') {
                    return 70;
                }

                return 80;
            }

            if ($day === 3) {
                // Sesi Pagi: Perempat Final (QF)
                if ($rt === 'qf') {
                    if (str_contains($pk, 'kat_c_pi')) {
                        return 10;
                    }
                    if (str_contains($pk, 'kat_c_pa')) {
                        return 20;
                    }
                    if (str_contains($pk, 'ganda')) {
                        return 30;
                    }

                    return 35;
                }

                // Sesi Siang: Seluruh Semifinal
                if (str_contains($pk, 'kat_a_pi')) {
                    return 40;
                }
                if (str_contains($pk, 'kat_a_pa')) {
                    return 50;
                }
                if (str_contains($pk, 'kat_b_pi')) {
                    return 60;
                }
                if (str_contains($pk, 'kat_b_pa')) {
                    return 70;
                }
                if (str_contains($pk, 'kat_c_pi')) {
                    return 80;
                }
                if (str_contains($pk, 'kat_c_pa')) {
                    return 90;
                }
                if (str_contains($pk, 'ganda')) {
                    return 100;
                }

                return 110;
            }

            if ($day >= 4) {
                // Grand Final (Lapangan 1)
                // Sesi Pagi: 5 Partai (Kat A Pi, Kat A Pa, Kat B Pi, Kat B Pa, Kat C Pi)
                if (str_contains($pk, 'kat_a_pi')) {
                    return 10;
                }
                if (str_contains($pk, 'kat_a_pa')) {
                    return 20;
                }
                if (str_contains($pk, 'kat_b_pi')) {
                    return 30;
                }
                if (str_contains($pk, 'kat_b_pa')) {
                    return 40;
                }
                if (str_contains($pk, 'kat_c_pi')) {
                    return 50;
                }
                // Sesi Siang (setelah Jumatan): 2 Partai (Kat C Pa, Ganda)
                if (str_contains($pk, 'kat_c_pa')) {
                    return 60;
                }
                if (str_contains($pk, 'ganda')) {
                    return 70;
                }

                return 80;
            }

            return 100;
        };

        // Group matches into days
        $matchesByDay = [];
        for ($d = 1; $d <= $tournamentDays; $d++) {
            $matchesByDay[$d] = [];
        }

        foreach ($rawMatchesList as $m) {
            $mDay = $getDayForMatch($m);
            $matchesByDay[$mDay][] = $m;
        }

        $scheduledMatches = [];
        $daysSummary = [];

        for ($d = 1; $d <= $tournamentDays; $d++) {
            [$dayDate, $dayLabel] = $getDayDateAndLabel($d);
            $dayMatches = $matchesByDay[$d] ?? [];

            // Sort matches for this day
            usort($dayMatches, function ($a, $b) use ($d, $getPriority) {
                $pA = $getPriority($a, $d);
                $pB = $getPriority($b, $d);
                if ($pA !== $pB) {
                    return $pA <=> $pB;
                }

                return strcmp($a['match_code'], $b['match_code']);
            });

            // Tracking court order & times
            $courtCounters = [];
            $courtCurrentTime = [];
            $dayCourtIndex = 0;

            foreach ($courts as $c) {
                $courtCounters[$c] = 0;
                $courtCurrentTime[$c] = Carbon::createFromFormat('H:i', $startTime);
            }

            $currentDuration = ($d >= 3) ? $semifinalDuration : $matchDuration;
            $dayContestedCount = 0;
            $dayPreviewList = [];

            foreach ($dayMatches as $m) {
                $isContested = $m['is_contested'];
                $assignedCourt = $m['assigned_court'];

                if ($distributionMode === 'even' && $isContested) {
                    $assignedCourt = $courts[$dayCourtIndex % count($courts)];
                    $dayCourtIndex++;
                }

                // Pada Hari 3 (QF Kat C & Ganda pagi, Semifinal siang):
                // Jika mode kategori, alokasikan Ganda QF ke Lapangan 2 agar kedua lapangan aktif seimbang di sesi pagi
                if ($distributionMode === 'category_based' && $d === 3 && $m['round_type'] === 'qf' && str_contains($m['pool_key'], 'ganda') && count($courts) > 1) {
                    $assignedCourt = $courts[1];
                }

                // Pada Hari 4 (Final), seluruh pertandingan dipusatkan di Lapangan 1 (Utama)
                if ($d === 4 && count($courts) > 0) {
                    $assignedCourt = $courts[0];
                }

                $assignedTime = null;
                $assignedOrder = null;

                if ($isContested) {
                    // Jeda Ishoma Siang (12:00 - 13:00 WIB) pada Hari 1, 2, 3
                    if ($lunchBreak && $d !== 4) {
                        $cTimeStr = $courtCurrentTime[$assignedCourt]->format('H:i');
                        if ($cTimeStr >= $lunchStart && $cTimeStr < $lunchEnd) {
                            $courtCurrentTime[$assignedCourt] = Carbon::createFromFormat('H:i', $lunchEnd);
                        }
                    }

                    // Pada Hari 3, seluruh Semifinal dimulai pada Sesi Siang (13:00 WIB) setelah istirahat & makan siang
                    if ($d === 3 && $m['round_type'] === 'semifinal') {
                        $cTimeStr = $courtCurrentTime[$assignedCourt]->format('H:i');
                        if ($cTimeStr < '13:00') {
                            $courtCurrentTime[$assignedCourt] = Carbon::createFromFormat('H:i', '13:00');
                        }
                    }

                    $courtCounters[$assignedCourt]++;
                    $assignedOrder = $courtCounters[$assignedCourt];

                    // Jeda Sholat Jumat pada Hari Final (Hari 4):
                    // Sesi Pagi: Partai 1 s.d. 5 selesai pukul 10:30 WIB
                    // Sesi Siang: Partai 6 (Putra 5-6) & Partai 7 (Ganda) lanjut pukul 13:00 WIB
                    if ($d === 4 && $fridayBreak) {
                        $cTimeStr = $courtCurrentTime[$assignedCourt]->format('H:i');
                        if ($assignedOrder === 6 || ($cTimeStr >= '10:30' && $cTimeStr < '13:00')) {
                            $courtCurrentTime[$assignedCourt] = Carbon::createFromFormat('H:i', '13:00');
                        }
                    }

                    $assignedTime = $courtCurrentTime[$assignedCourt]->format('H:i');
                    $courtCurrentTime[$assignedCourt]->addMinutes($currentDuration);
                    $dayContestedCount++;
                } else {
                    $assignedCourt = 'BYE';
                }

                $matchItem = [
                    'match_code' => $m['match_code'],
                    'court_number' => $assignedCourt,
                    'scheduled_time' => $assignedTime,
                    'match_order' => $assignedOrder,
                    'match_day' => $d,
                    'match_date' => $dayDate,
                    'match_day_label' => $dayLabel,
                    'round_name' => $m['round_name'],
                    'round_type' => $m['round_type'],
                    'category' => $m['category'],
                    'match_type' => $m['match_type'],
                    'pool_key' => $m['pool_key'],
                    'pool_title' => $m['pool_title'],
                    'team1_id' => $m['team1']['id'] ?? null,
                    'team1_school' => $m['team1']['institution'] ?? ($m['is_bye1'] ? 'BYE' : 'TBD'),
                    'team1_player' => $m['team1']['name'] ?? ($m['is_bye1'] ? '[BYE]' : 'Menunggu Pemenang'),
                    'team2_id' => $m['team2']['id'] ?? null,
                    'team2_school' => $m['team2']['institution'] ?? ($m['is_bye2'] ? 'BYE' : 'TBD'),
                    'team2_player' => $m['team2']['name'] ?? ($m['is_bye2'] ? '[BYE]' : 'Menunggu Pemenang'),
                    'status' => $m['status'],
                    'winner_team' => $m['winner_team'],
                    'is_contested' => $isContested,
                ];

                $scheduledMatches[] = $matchItem;
                $dayPreviewList[] = $matchItem;
            }

            $daysSummary[$d] = [
                'day_num' => $d,
                'date' => $dayDate,
                'label' => $dayLabel,
                'total_matches' => $dayContestedCount,
                'matches' => $dayPreviewList,
            ];
        }

        $courtListStr = implode(', ', $courts);
        $summary = "{$tournamentDays} Hari ({$courtListStr}, {$startTime} - {$endTime} WIB)";

        return [
            'summary' => $summary,
            'days' => $daysSummary,
            'matches' => $scheduledMatches,
        ];
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
            $isAuthorizedBadminton = (method_exists($user, 'managesBadminton') && $user->managesBadminton()) && (
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

                // Play-off badge on connector junction
                $poBadgeBg = $isDark ? '#0b1329' : '#ffffff';
                $poBadgeBorder = $isDark ? '#f59e0b' : '#d97706';
                $poBadgeText = $isDark ? '#f59e0b' : '#d97706';
                $svg[] = "<g class='match-node-badge' title='Play-off Kualifikasi'>";
                $svg[] = "<rect x='".($poStemX - 12)."' y='".($targetY - 9)."' width='24' height='18' rx='4' fill='{$poBadgeBg}' stroke='{$poBadgeBorder}' stroke-width='1.6'/>";
                $svg[] = "<text x='{$poStemX}' y='".($targetY + 4)."' text-anchor='middle' font-size='9.5' font-weight='900' font-family='monospace, system-ui, sans-serif' fill='{$poBadgeText}'>PO</text>";
                $svg[] = '</g>';

                // Play-off schedule text above the line
                $poSched = $po['existing_match']?->scheduled_time ?? '07:30';
                $svg[] = "<text x='".($poStemX + 15)."' y='".($targetY - 4)."' font-size='7.5' font-mono font-weight='700' fill='{$accentColor}'>{$poSched}</text>";
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
        $sequentialMatchNum = 0;

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

                // Cek apakah pertandingan ini adalah BYE lolos otomatis di Babak 1 (tidak perlu nomor partai)
                $isByeAdvance = ($r === 1) && (
                    ($match['status'] ?? '') === 'bye_advance' ||
                    ! empty($match['is_bye1']) ||
                    ! empty($match['is_bye2']) ||
                    (($match['team1']['name'] ?? '') === '[BYE]') ||
                    (($match['team2']['name'] ?? '') === '[BYE]')
                );

                $matchNumber = null;
                if (! $isByeAdvance) {
                    $sequentialMatchNum++;
                    $em = $match['existing_match'] ?? null;
                    // Prioritaskan nomor partai resmi dari jadwal database jika ada, jika belum ada gunakan nomor urut bagan
                    $matchNumber = (! empty($em?->match_order)) ? $em->match_order : $sequentialMatchNum;
                }

                // Horizontal arm from top
                $svg[] = "<line x1='{$branchStartX}' y1='{$y1}' x2='{$bracketVLineX}' y2='{$y1}' stroke='{$strokeColor}' stroke-width='{$strokeWidth}'/>";
                // Horizontal arm from bottom
                $svg[] = "<line x1='{$branchStartX}' y1='{$y2}' x2='{$bracketVLineX}' y2='{$y2}' stroke='{$strokeColor}' stroke-width='{$strokeWidth}'/>";
                // Vertical connector bar
                $svg[] = "<line x1='{$bracketVLineX}' y1='{$y1}' x2='{$bracketVLineX}' y2='{$y2}' stroke='{$strokeColor}' stroke-width='{$strokeWidth}'/>";
                // Horizontal stem to right
                $svg[] = "<line x1='{$bracketVLineX}' y1='{$yMid}' x2='{$stemEndX}' y2='{$yMid}' stroke='{$strokeColor}' stroke-width='{$strokeWidth}'/>";

                // Badge Nomor Pertandingan / Nomor Partai (Agak Besar & Jelas seperti Standar Resmi BWF)
                if ($matchNumber !== null) {
                    $numStr = (string) $matchNumber;
                    $badgeW = (strlen($numStr) >= 3) ? 32 : (strlen($numStr) >= 2 ? 26 : 22);
                    $badgeH = 20;
                    $badgeX = $bracketVLineX - ($badgeW / 2);
                    $badgeY = $yMid - ($badgeH / 2);
                    $badgeBg = $isDark ? '#0b1329' : '#ffffff';
                    $badgeBorder = $isDark ? '#38bdf8' : '#0f172a';
                    $badgeText = $isDark ? '#38bdf8' : '#0f172a';

                    $svg[] = "<g class='match-node-badge' title='Partai #{$numStr}'>";
                    $svg[] = "<rect x='{$badgeX}' y='{$badgeY}' width='{$badgeW}' height='{$badgeH}' rx='4' fill='{$badgeBg}' stroke='{$badgeBorder}' stroke-width='1.8'/>";
                    $svg[] = "<text x='{$bracketVLineX}' y='".($yMid + 4.5)."' text-anchor='middle' font-size='12' font-weight='900' font-family='monospace, system-ui, sans-serif' fill='{$badgeText}'>{$numStr}</text>";
                    $svg[] = '</g>';
                }

                $contentStartX = $bracketVLineX + ($matchNumber !== null ? 20 : 8);

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
                        $svg[] = "<text x='{$contentStartX}' y='".($yMid - 13)."' font-size='{$wSize}' font-weight='800' fill='{$wColor}'>{$w1}</text>";
                        $svg[] = "<text x='{$contentStartX}' y='".($yMid - 3)."' font-size='{$wSize}' font-weight='800' fill='{$wColor}'>{$w2}</text>";
                    } else {
                        // Single player advancing
                        $wName = htmlspecialchars($rawWinnerName, ENT_QUOTES);
                        $wLen = mb_strlen($rawWinnerName);
                        $wFontSize = ($wLen > 26) ? '10' : (($bracketSize > 16) ? '11' : '12.5');
                        $svg[] = "<text x='{$contentStartX}' y='".($yMid - 6)."' font-size='{$wFontSize}' font-weight='800' fill='{$wColor}'>{$wName}</text>";
                    }
                }

                // Match score or Court Schedule if available
                if (! empty($match['existing_match'])) {
                    $em = $match['existing_match'];
                    if ($em->team1_set1 > 0 || $em->team2_set1 > 0) {
                        $scoreStr = "{$em->team1_set1}-{$em->team2_set1}";
                        $svg[] = "<text x='{$contentStartX}' y='".($yMid + 12)."' font-size='8.5' font-mono font-weight='bold' fill='{$subTextColor}'>{$scoreStr}</text>";
                    } elseif (! empty($em->court_number) || ! empty($em->scheduled_time)) {
                        $schedParts = [];
                        if (! empty($em->match_day)) {
                            $schedParts[] = "H{$em->match_day}";
                        }
                        if (! empty($em->court_number) && strtoupper($em->court_number) !== 'BYE') {
                            $schedParts[] = $em->court_number;
                        }
                        if (! empty($em->scheduled_time)) {
                            $schedParts[] = $em->scheduled_time;
                        }
                        if (! empty($schedParts)) {
                            $schedStr = htmlspecialchars(implode(' • ', $schedParts), ENT_QUOTES);
                            $svg[] = "<text x='{$contentStartX}' y='".($yMid + 11)."' font-size='7.5' font-mono font-weight='700' fill='{$accentColor}'>{$schedStr}</text>";
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
