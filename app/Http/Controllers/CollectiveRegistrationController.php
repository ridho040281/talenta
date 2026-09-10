<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\Competition;
use App\Models\Invoice;
use App\Models\Registration;
use App\Models\RegistrationMember;
use App\Models\User;
use App\Services\WablasNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CollectiveRegistrationController extends Controller
{
    /**
     * Show Collective Registration Wizard Page
     */
    public function wizard()
    {
        $user = Auth::user();
        $regInfo = AppSetting::getRegistrationStatusInfo();
        if (!$regInfo['is_open'] && !$user->isTester()) {
            return redirect()->route('peserta.dashboard')
                ->with('error', $regInfo['closed_message'] ?: 'Pendaftaran kolektif saat ini sedang ditutup.');
        }

        $competitions = Competition::with('category')->where('status', 'buka')->get();
        $invoices = Invoice::with(['registrations.competition', 'registrations.members'])
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        return view('peserta.collective.wizard', compact('user', 'competitions', 'invoices'));
    }

    /**
     * Generate & Download Official Single-Sheet Excel Template with Dropdowns
     */
    public function downloadTemplate(): StreamedResponse
    {
        $competitions = Competition::with('category')->where('status', 'buka')->orderBy('name')->get();

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('FORMULIR_PENDAFTARAN');

        // Main Header Title
        $sheet->setCellValue('A1', 'FORMULIR PENDAFTARAN KOLEKTIF TALENTA 2026 - MTsN 1 BLITAR');
        $sheet->mergeCells('A1:L1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->setColor(new Color('064E3B'));

        $sheet->setCellValue('A2', 'Petunjuk: Isi biodata siswa di bawah. Pada kolom CABANG_LOMBA, klik panah drop-down untuk memilih lomba. Khusus Pop Singer, pilih judul lagu di kolom JUDUL_LAGU.');
        $sheet->mergeCells('A2:L2');
        $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(10)->setColor(new Color('475569'));

        // Column Headers for Participant Table
        $headers = [
            'A4' => 'NO',
            'B4' => 'NAMA_LENGKAP_PESERTA',
            'C4' => 'NISN',
            'D4' => 'JENIS_KELAMIN (L/P)',
            'E4' => 'TEMPAT_LAHIR',
            'F4' => 'TANGGAL_LAHIR (YYYY-MM-DD)',
            'G4' => 'ASAL_SEKOLAH_MADRASAH',
            'H4' => 'CABANG_LOMBA (PILIH DROPDOWN)',
            'I4' => 'NAMA_TIM (KHUSUS REGU)',
            'J4' => 'NAMA_OFFICIAL_PEMBINA',
            'K4' => 'NO_WA_PEMBINA',
            'L4' => 'JUDUL_LAGU (KHUSUS POP SINGER)',
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '059669']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '047857']]],
        ];
        $sheet->getStyle('A4:L4')->applyFromArray($headerStyle);
        $sheet->getRowDimension(4)->setRowHeight(28);

        // Build Dropdown Options for Competitions (Langsung Nama Lomba)
        $dropdownList = [];
        foreach ($competitions as $c) {
            if ($c->code === 'BLT') {
                $dropdownList[] = 'Bulu Tangkis (Kat A: Kls 1-2 • Tunggal PA)';
                $dropdownList[] = 'Bulu Tangkis (Kat A: Kls 1-2 • Tunggal PI)';
                $dropdownList[] = 'Bulu Tangkis (Kat B: Kls 3-4 • Tunggal PA)';
                $dropdownList[] = 'Bulu Tangkis (Kat B: Kls 3-4 • Tunggal PI)';
                $dropdownList[] = 'Bulu Tangkis (Kat C: Kls 5-6 • Tunggal PA)';
                $dropdownList[] = 'Bulu Tangkis (Kat C: Kls 5-6 • Tunggal PI)';
                $dropdownList[] = 'Bulu Tangkis (Ganda PA)';
                $dropdownList[] = 'Bulu Tangkis (Ganda PI)';
            } elseif ($c->code === 'TMJ') {
                $dropdownList[] = 'Tenis Meja (Kat A: Kls 1-3 • Tunggal PA)';
                $dropdownList[] = 'Tenis Meja (Kat A: Kls 1-3 • Tunggal PI)';
                $dropdownList[] = 'Tenis Meja (Kat B: Kls 4-6 • Tunggal PA)';
                $dropdownList[] = 'Tenis Meja (Kat B: Kls 4-6 • Tunggal PI)';
            } else {
                $dropdownList[] = $c->name;
            }
        }

        // Build Pop Singer Songs list
        $popComp = $competitions->firstWhere('code', 'POP');
        $popSongs = $popComp ? $popComp->song_options : [];
        if (empty($popSongs)) {
            $defaultRaw = AppSetting::get('pop_song_options') ?: "Deen Assalam\nRahmatun Lil'Alameen\nYa Maulana\nMan Ana\nAisyah Istri Rasulullah\nBidadari Surga\nSholawat Cinta\nKisah Sang Rasul";
            $popSongs = array_values(array_filter(array_map('trim', explode("\n", str_replace("\r", "", $defaultRaw)))));
        }

        // Create Helper Hidden Sheet for Dropdown Lists (Competitions & Pop Songs)
        $listSheet = $spreadsheet->createSheet();
        $listSheet->setTitle('LIST_LOMBA');
        foreach ($dropdownList as $index => $item) {
            $listSheet->setCellValue('A'.($index + 1), $item);
        }
        $listSheetCount = count($dropdownList);

        foreach ($popSongs as $sIndex => $sTitle) {
            $listSheet->setCellValue('B'.($sIndex + 1), $sTitle);
        }
        $popSongCount = count($popSongs);

        $listSheet->setSheetState(Worksheet::SHEETSTATE_VERYHIDDEN);

        // Ensure active sheet is the main form
        $spreadsheet->setActiveSheetIndex(0);

        // Sample Data Rows for Guidance (Dibuat 1 nama contoh saja)
        $sampleData = [
            [1, 'Ahmad Zaki Mubarak', '0112345678', 'L', 'Blitar', '2012-04-15', 'SD Islam Al-Falah', 'Olimpiade MIPA', '', 'Ust. Ridwan', '081234567890', ''],
        ];

        $rowNum = 5;
        foreach ($sampleData as $row) {
            $colLetter = 'A';
            foreach ($row as $val) {
                $sheet->setCellValue($colLetter.$rowNum, $val);
                $colLetter++;
            }
            $sheet->getStyle("A{$rowNum}:L{$rowNum}")->getFont()->setSize(10);
            $sheet->getStyle("A{$rowNum}:L{$rowNum}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('CBD5E1');
            $rowNum++;
        }

        // Apply Data Validation (Dropdowns) to Rows 5 through 200
        for ($r = 5; $r <= 200; $r++) {
            // Dropdown Gender (Col D)
            $genderVal = $sheet->getCell("D{$r}")->getDataValidation();
            $genderVal->setType(DataValidation::TYPE_LIST);
            $genderVal->setErrorStyle(DataValidation::STYLE_INFORMATION);
            $genderVal->setAllowBlank(true);
            $genderVal->setShowDropDown(true);
            $genderVal->setFormula1('"L,P"');

            // Dropdown Cabang Lomba (Col H)
            $compVal = $sheet->getCell("H{$r}")->getDataValidation();
            $compVal->setType(DataValidation::TYPE_LIST);
            $compVal->setErrorStyle(DataValidation::STYLE_INFORMATION);
            $compVal->setAllowBlank(true);
            $compVal->setShowInputMessage(true);
            $compVal->setShowErrorMessage(true);
            $compVal->setShowDropDown(true);
            $compVal->setPromptTitle('Pilih Nama Lomba');
            $compVal->setPrompt('Klik panah drop-down untuk memilih nama cabang lomba');
            $compVal->setFormula1("LIST_LOMBA!\$A\$1:\$A\${$listSheetCount}");

            // Dropdown Lagu Pop Singer (Col L)
            if ($popSongCount > 0) {
                $songVal = $sheet->getCell("L{$r}")->getDataValidation();
                $songVal->setType(DataValidation::TYPE_LIST);
                $songVal->setErrorStyle(DataValidation::STYLE_INFORMATION);
                $songVal->setAllowBlank(true);
                $songVal->setShowInputMessage(true);
                $songVal->setShowErrorMessage(true);
                $songVal->setShowDropDown(true);
                $songVal->setPromptTitle('Pilih Judul Lagu');
                $songVal->setPrompt('Khusus cabang Pop Singer: klik drop-down untuk memilih judul lagu');
                $songVal->setFormula1("LIST_LOMBA!\$B\$1:\$B\${$popSongCount}");
            }
        }

        // Auto-fit Column Widths A through L
        foreach (range('A', 'L') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $fileName = 'Template_Pendaftaran_Kolektif_TALENTA_2026.xlsx';

        return new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Parse & Validate Uploaded Excel File (Interactive Preview)
     */
    public function parseExcel(Request $request)
    {
        $request->validate([
            'excel_file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
        ], [
            'excel_file.required' => 'Silakan pilih file Excel template yang telah diisi.',
            'excel_file.mimes' => 'Format file harus berupa Excel (.xlsx, .xls) atau CSV.',
            'excel_file.max' => 'Ukuran file maksimal 10MB.',
        ]);

        $file = $request->file('excel_file');

        try {
            $spreadsheet = IOFactory::load($file->getRealPath());
            $sheet = $spreadsheet->getSheet(0); // Single sheet
            $rows = $sheet->toArray(null, true, true, true);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal membaca file Excel: '.$e->getMessage());
        }

        if (count($rows) < 5) {
            return back()->with('error', 'File Excel kosong atau tidak memiliki data peserta.');
        }

        $competitions = Competition::with('category')->withCount(['registrations' => function ($q) {
            $q->whereIn('status', ['pending', 'verified']);
        }])->get()->keyBy(fn ($item) => strtoupper(trim($item->code)));

        $parsedRows = [];
        $totalFee = 0;
        $validRowCount = 0;
        $errorRowCount = 0;
        $competitionCounts = [];
        $registeredNisnsInBatch = []; // Keyed by [competition_code][nisn] => row_number

        $invalidPatterns = [
            '0000000000', '1111111111', '2222222222', '3333333333', '4444444444',
            '5555555555', '6666666666', '7777777777', '8888888888', '9999999999',
            '1234567890', '0123456789', '9876543210', '0987654321',
        ];

        // Loop data starting at row 5 (row 1-4 are headers and guidance)
        for ($i = 5; $i <= count($rows); $i++) {
            $row = $rows[$i];

            $name = trim($row['B'] ?? '');
            $nisn = trim($row['C'] ?? '');
            $gender = strtoupper(trim($row['D'] ?? 'L'));
            $birthPlace = trim($row['E'] ?? '');
            $birthDate = trim($row['F'] ?? '');
            $institution = trim($row['G'] ?? '') ?: (Auth::user()->institution_name ?? 'Mandiri');

            // Extract raw competition from Column H (e.g. "Bulu Tangkis (Kat C: Kls 5-6 • Tunggal PA)", "Bulu Tangkis (Ganda PA)", "Olimpiade MIPA", "Catur", "MTQ")
            $rawComp = trim($row['H'] ?? '');
            $code = '';
            if (! empty($rawComp)) {
                if (stripos($rawComp, 'Bulu Tangkis') !== false || stripos($rawComp, 'BLT') !== false) {
                    $code = 'BLT';
                } elseif (stripos($rawComp, 'Tenis Meja') !== false || stripos($rawComp, 'TMJ') !== false) {
                    $code = 'TMJ';
                } elseif (preg_match('/^([A-Za-z0-9]+)\s*[-:]/i', $rawComp, $matches) && isset($competitions[strtoupper(trim($matches[1]))])) {
                    $code = strtoupper(trim($matches[1]));
                } elseif (isset($competitions[strtoupper($rawComp)])) {
                    $code = strtoupper($rawComp);
                } else {
                    foreach ($competitions as $c) {
                        if (strcasecmp($c->name, $rawComp) === 0 || stripos($rawComp, $c->name) !== false || stripos($c->name, $rawComp) !== false) {
                            $code = strtoupper($c->code);
                            break;
                        }
                    }
                }
            }

            // Extract Bulu Tangkis & Tenis Meja sub-category if applicable
            $targetClass = null;
            $matchType = null;
            $subCategory = null;

            if (str_starts_with($code, 'BLT') || str_contains(strtoupper($rawComp), 'BULU TANGKIS')) {
                $code = 'BLT';
                $isGanda = stripos($rawComp, 'Ganda') !== false || stripos($rawComp, 'GPA') !== false || stripos($rawComp, 'GPI') !== false;

                if ($isGanda) {
                    $targetClass = 'Semua Kelas SD/MI';
                    if (stripos($rawComp, 'PI') !== false || stripos($rawComp, 'Putri') !== false || stripos($rawComp, 'GPI') !== false) {
                        $matchType = 'Ganda Putri (PI)';
                    } else {
                        $matchType = 'Ganda Putra (PA)';
                    }
                    $subCategory = 'Ganda - '.$matchType;
                } else {
                    if (stripos($rawComp, 'Kat A') !== false || stripos($rawComp, '-A-') !== false || stripos($rawComp, 'Kelas 1') !== false || stripos($rawComp, 'Kls 1') !== false) {
                        $targetClass = 'Kategori A (Kelas 1 - 2)';
                    } elseif (stripos($rawComp, 'Kat B') !== false || stripos($rawComp, '-B-') !== false || stripos($rawComp, 'Kelas 3') !== false || stripos($rawComp, 'Kls 3') !== false) {
                        $targetClass = 'Kategori B (Kelas 3 - 4)';
                    } elseif (stripos($rawComp, 'Kat C') !== false || stripos($rawComp, '-C-') !== false || stripos($rawComp, 'Kelas 5') !== false || stripos($rawComp, 'Kls 5') !== false) {
                        $targetClass = 'Kategori C (Kelas 5 - 6)';
                    } else {
                        $targetClass = 'Kategori A (Kelas 1 - 2)';
                    }

                    if (stripos($rawComp, 'PI') !== false || stripos($rawComp, 'Putri') !== false || stripos($rawComp, 'TPI') !== false) {
                        $matchType = 'Tunggal Putri (PI)';
                    } else {
                        $matchType = 'Tunggal Putra (PA)';
                    }

                    $subCategory = $targetClass.' - '.$matchType;
                }
            } elseif (str_starts_with($code, 'TMJ') || str_contains(strtoupper($rawComp), 'TENIS MEJA')) {
                $code = 'TMJ';

                if (stripos($rawComp, 'Kat B') !== false || stripos($rawComp, '-B-') !== false || stripos($rawComp, 'Kelas 4') !== false || stripos($rawComp, 'Kls 4') !== false || stripos($rawComp, 'Kelas 5') !== false || stripos($rawComp, 'Kelas 6') !== false || stripos($rawComp, '4-6') !== false || stripos($rawComp, '4 - 6') !== false) {
                    $targetClass = 'Kategori B (Kelas 4 - 6)';
                } else {
                    $targetClass = 'Kategori A (Kelas 1 - 3)';
                }

                if (stripos($rawComp, 'PI') !== false || stripos($rawComp, 'Putri') !== false || stripos($rawComp, 'TPI') !== false) {
                    $matchType = 'Tunggal Putri (PI)';
                    $gender = 'P';
                } elseif (stripos($rawComp, 'PA') !== false || stripos($rawComp, 'Putra') !== false || stripos($rawComp, 'TPA') !== false) {
                    $matchType = 'Tunggal Putra (PA)';
                    $gender = 'L';
                } else {
                    $matchType = ($gender === 'P') ? 'Tunggal Putri (PI)' : 'Tunggal Putra (PA)';
                }

                $subCategory = $targetClass.' - '.$matchType;
            } elseif (in_array($code, ['MTQ', 'POP'])) {
                $matchType = ($gender === 'P') ? 'Putri (PI)' : 'Putra (PA)';
                $subCategory = $matchType;
            }

            $teamName = trim($row['I'] ?? '');
            $officialName = trim($row['J'] ?? '') ?: Auth::user()->name;
            $officialPhone = trim($row['K'] ?? '') ?: Auth::user()->phone;
            $chosenSong = trim($row['L'] ?? '');

            // Skip empty rows
            if (empty($name) && empty($code)) {
                continue;
            }

            $errors = [];

            if (empty($name)) {
                $errors[] = 'Nama peserta kosong';
            }

            // Validate NISN format if given
            if (! empty($nisn)) {
                if (! preg_match('/^[0-9]{8,12}$/', $nisn)) {
                    $errors[] = "Format NISN '{$nisn}' tidak valid (harus 10 digit angka)";
                } elseif (in_array($nisn, $invalidPatterns)) {
                    $errors[] = "NISN '{$nisn}' terdeteksi angka acak/palsu";
                }
            }

            if (empty($code)) {
                $errors[] = 'Cabang lomba belum dipilih';
            } elseif (! isset($competitions[$code])) {
                $errors[] = "Cabang lomba '{$rawComp}' tidak dikenali di sistem";
            } else {
                $comp = $competitions[$code];
                $compStatus = $comp->registration_status_info;
                if (! $compStatus['is_open'] && ! $user->isTester()) {
                    $errors[] = "Lomba '{$comp->name}': {$compStatus['message']}";
                } else {
                    // Check quota
                    $currentRegistered = $comp->registrations_count ?? 0;
                    $batchCountForComp = $competitionCounts[$code] ?? 0;
                    if ($comp->quota > 0 && ($currentRegistered + $batchCountForComp) >= $comp->quota) {
                        $errors[] = "Kuota pendaftaran lomba {$comp->name} sudah penuh ({$comp->quota} peserta)";
                    } else {
                        $competitionCounts[$code] = ($competitionCounts[$code] ?? 0) + 1;
                    }

                    // Check duplicate registration in the SAME competition
                    if (! empty($nisn)) {
                        $isBltGanda = ($code === 'BLT' && ! empty($matchType) && stripos($matchType, 'ganda') !== false);
                        $batchKey = ($code === 'BLT') ? ($code.'_'.($isBltGanda ? 'ganda' : 'tunggal')) : $code;

                        // A. Check duplicate in same Excel batch
                        if (isset($registeredNisnsInBatch[$batchKey][$nisn])) {
                            $prevRow = $registeredNisnsInBatch[$batchKey][$nisn];
                            $sectorText = ($code === 'BLT') ? (' sektor '.($isBltGanda ? 'Ganda' : 'Tunggal')) : '';
                            $errors[] = "Siswa dengan NISN '{$nisn}' didaftarkan ganda pada cabang {$comp->name}{$sectorText} (duplikat baris {$prevRow})";
                        } else {
                            $registeredNisnsInBatch[$batchKey][$nisn] = $i;
                        }

                        // B. Check duplicate in database for the same competition / sector
                        $alreadyInDb = RegistrationMember::where('nisn', $nisn)
                            ->whereHas('registration', function ($q) use ($comp, $code, $isBltGanda) {
                                $q->where('competition_id', $comp->id)
                                    ->whereIn('status', ['pending', 'verified']);

                                if ($code === 'BLT') {
                                    if ($isBltGanda) {
                                        $q->where(function ($sub) {
                                            $sub->where('match_type', 'like', '%ganda%')
                                                ->orWhere('target_class', 'like', '%ganda%')
                                                ->orWhere('sub_category', 'like', '%ganda%');
                                        });
                                    } else {
                                        $q->where(function ($sub) {
                                            $sub->where(function ($s) {
                                                $s->whereNull('match_type')
                                                    ->orWhere('match_type', 'not like', '%ganda%');
                                            })->where(function ($s) {
                                                $s->whereNull('target_class')
                                                    ->orWhere('target_class', 'not like', '%ganda%');
                                            })->where(function ($s) {
                                                $s->whereNull('sub_category')
                                                    ->orWhere('sub_category', 'not like', '%ganda%');
                                            });
                                        });
                                    }
                                }
                            })
                            ->exists();

                        if ($alreadyInDb) {
                            $sectorText = ($code === 'BLT') ? (' sektor '.($isBltGanda ? 'Ganda' : 'Tunggal')) : '';
                            $errors[] = "Siswa dengan NISN '{$nisn}' sudah terdaftar sebelumnya pada cabang {$comp->name}{$sectorText}";
                        }
                    }
                }
            }

            // Check Pop Singer song requirement
            if ($code === 'POP' || stripos($rawComp, 'Pop Singer') !== false || (isset($competitions[$code]) && $competitions[$code]->code === 'POP')) {
                if (empty($chosenSong)) {
                    $errors[] = 'Judul lagu pilihan wajib diisi untuk cabang Pop Singer (kolom L)';
                }
            }

            if (! in_array($gender, ['L', 'P'])) {
                $gender = 'L';
            }

            $compObj = $competitions[$code] ?? null;
            $fee = $compObj ? (float) $compObj->registration_fee : 0;

            if ($compObj) {
                if ($compObj->code === 'BLT' || str_starts_with($code, 'BLT')) {
                    $isGanda = stripos($rawComp, 'Ganda') !== false || stripos($rawComp, 'GPA') !== false || stripos($rawComp, 'GPI') !== false || stripos($matchType ?? '', 'Ganda') !== false;
                    $isPutri = $gender === 'P' || stripos($rawComp, 'PI') !== false || stripos($rawComp, 'Putri') !== false || stripos($matchType ?? '', 'Putri') !== false || stripos($matchType ?? '', 'PI') !== false;

                    if ($isGanda) {
                        $fee = (float) AppSetting::get($isPutri ? 'blt_fee_ganda_pi' : 'blt_fee_ganda_pa', AppSetting::get('blt_fee_ganda', 200000));
                    } else {
                        $feeA = (float) AppSetting::get($isPutri ? 'blt_fee_a_tunggal_pi' : 'blt_fee_a_tunggal_pa', 130000);
                        $feeB = (float) AppSetting::get($isPutri ? 'blt_fee_b_tunggal_pi' : 'blt_fee_b_tunggal_pa', 150000);
                        $feeC = (float) AppSetting::get($isPutri ? 'blt_fee_c_tunggal_pi' : 'blt_fee_c_tunggal_pa', 150000);

                        if (stripos($targetClass ?? '', 'Kategori A') !== false || stripos($rawComp, 'Kat A') !== false || stripos($rawComp, 'Kls 1') !== false) {
                            $fee = $feeA;
                        } elseif (stripos($targetClass ?? '', 'Kategori B') !== false || stripos($rawComp, 'Kat B') !== false || stripos($rawComp, 'Kls 3') !== false) {
                            $fee = $feeB;
                        } elseif (stripos($targetClass ?? '', 'Kategori C') !== false || stripos($rawComp, 'Kat C') !== false || stripos($rawComp, 'Kls 5') !== false) {
                            $fee = $feeC;
                        } else {
                            $fee = $feeA;
                        }
                    }
                } elseif ($compObj->code === 'TMJ') {
                    $isPutri = $gender === 'P' || stripos($matchType ?? '', 'Putri') !== false || stripos($matchType ?? '', 'PI') !== false;
                    $feeA = (float) AppSetting::get($isPutri ? 'tmj_fee_a_tunggal_pi' : 'tmj_fee_a_tunggal_pa', $compObj->registration_fee ?: 35000);
                    $feeB = (float) AppSetting::get($isPutri ? 'tmj_fee_b_tunggal_pi' : 'tmj_fee_b_tunggal_pa', $compObj->registration_fee ?: 35000);
                    $isKatB = stripos($targetClass ?? '', 'Kategori B') !== false || stripos($rawComp, 'Kat B') !== false || stripos($rawComp, 'Kelas 4') !== false || stripos($rawComp, 'Kls 4') !== false || stripos($rawComp, '4-6') !== false;
                    $fee = $isKatB ? $feeB : $feeA;
                } elseif ($compObj->code === 'MTQ') {
                    $isPutri = $gender === 'P';
                    $fee = (float) AppSetting::get($isPutri ? 'mtq_fee_pi' : 'mtq_fee_pa', $compObj->registration_fee);
                } elseif ($compObj->code === 'POP') {
                    $isPutri = $gender === 'P';
                    $fee = (float) AppSetting::get($isPutri ? 'pop_fee_pi' : 'pop_fee_pa', $compObj->registration_fee);
                }
            }

            $isValid = empty($errors);
            if ($isValid) {
                $validRowCount++;
                $totalFee += $fee;
            } else {
                $errorRowCount++;
            }

            $parsedRows[] = [
                'row_number' => $i,
                'name' => $name,
                'nisn' => $nisn,
                'gender' => $gender,
                'birth_place' => $birthPlace,
                'birth_date' => $birthDate,
                'institution_name' => $institution,
                'competition_code' => $code,
                'competition_name' => $compObj->name ?? $code,
                'competition_id' => $compObj->id ?? null,
                'sub_category' => $subCategory,
                'target_class' => $targetClass,
                'match_type' => $matchType,
                'chosen_song' => $chosenSong ?: null,
                'team_name' => $teamName,
                'official_name' => $officialName,
                'official_phone' => $officialPhone,
                'fee' => $fee,
                'is_valid' => $isValid,
                'errors' => $errors,
            ];
        }

        if (empty($parsedRows)) {
            return back()->with('error', 'Tidak ada baris data peserta yang dapat dibaca pada file Excel.');
        }

        $validRows = array_filter($parsedRows, fn ($item) => ! empty($item['is_valid']) && ! empty($item['competition_id']));
        $bonusResult = self::calculateBonusDiscounts($validRows, $competitions);
        $totalBonusDiscount = $bonusResult['total_bonus_discount'];
        $bonusDiscounts = $bonusResult['bonus_discounts'];
        $bonusSummaryList = $bonusResult['bonus_summary_list'];

        // Exact nominal amount without unique rupiah code
        $uniqueCode = 0;
        $finalAmount = max(0, $totalFee - $totalBonusDiscount);

        $bankInfo = [
            'bank_name' => AppSetting::get('bank_name', 'Bank Syariah Indonesia (BSI)'),
            'bank_account_number' => AppSetting::get('bank_account_number', '7199242042'),
            'bank_account_holder' => AppSetting::get('bank_account_holder', 'WIJIATIN'),
        ];

        return view('peserta.collective.preview', compact(
            'parsedRows',
            'validRowCount',
            'errorRowCount',
            'totalFee',
            'totalBonusDiscount',
            'bonusDiscounts',
            'bonusSummaryList',
            'uniqueCode',
            'finalAmount',
            'bankInfo'
        ));
    }

    /**
     * Calculate Bonus (10 Get 1) discounts for collective registration rows
     */
    public static function calculateBonusDiscounts(array $validRows, $competitions = null): array
    {
        $validRowsByComp = [];
        foreach ($validRows as $row) {
            $code = $row['competition_code'] ?? '';
            if (! empty($code)) {
                $validRowsByComp[$code][] = $row;
            }
        }

        $bonusDiscounts = [];
        $totalBonusDiscount = 0;
        $bonusSummaryList = [];

        foreach ($validRowsByComp as $compCode => $rowsInComp) {
            $compObj = ($competitions && isset($competitions[$compCode]))
                ? $competitions[$compCode]
                : Competition::where('code', $compCode)->first();

            if (! $compObj) {
                continue;
            }

            // Bonus rule: MIPA is default active (10 get 1), or if explicitly configured in AppSetting
            $isBonusActive = ($compCode === 'MIPA') || (AppSetting::get('bonus_active_'.strtolower($compCode), '0') === '1');
            $minQuota = (int) AppSetting::get('bonus_min_'.strtolower($compCode), 10);
            $freeCountPerBatch = (int) AppSetting::get('bonus_free_'.strtolower($compCode), 1);

            $count = count($rowsInComp);
            if ($isBonusActive && $minQuota > 0 && $count >= $minQuota) {
                $freeCount = (int) (floor($count / $minQuota) * $freeCountPerBatch);
                $unitFee = (float) $compObj->registration_fee;
                $discount = $freeCount * $unitFee;

                if ($discount > 0) {
                    $totalBonusDiscount += $discount;
                    $bonusDiscounts[$compCode] = [
                        'competition_name' => $compObj->name,
                        'competition_code' => $compCode,
                        'count' => $count,
                        'free_count' => $freeCount,
                        'discount' => $discount,
                        'unit_fee' => $unitFee,
                        'text' => "Bonus {$freeCount} Peserta Gratis ({$count} Peserta didaftarkan)",
                    ];
                    $bonusSummaryList[] = "Bonus {$compObj->name}: {$freeCount} Peserta Gratis (-Rp ".number_format($discount, 0, ',', '.').')';
                }
            }
        }

        return [
            'bonus_discounts' => $bonusDiscounts,
            'total_bonus_discount' => $totalBonusDiscount,
            'bonus_summary_list' => $bonusSummaryList,
        ];
    }

    /**
     * Confirm & Execute Batch Registration with Required 1-Step Payment Proof
     */
    public function confirmBatch(Request $request)
    {
        $request->validate([
            'payload' => ['required', 'string'],
            'payment_proof' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ], [
            'payment_proof.required' => 'Bukti pembayaran / slip transfer wajib diunggah dalam satu kali pengiriman.',
            'payment_proof.mimes' => 'Format file bukti transfer harus berupa JPG, PNG, atau PDF.',
            'payment_proof.max' => 'Ukuran file bukti transfer maksimal 5MB.',
        ]);

        $data = json_decode($request->payload, true);
        if (! $data || ! is_array($data)) {
            return redirect()->route('peserta.collective.wizard')->with('error', 'Data pendaftaran tidak valid.');
        }

        $user = Auth::user();
        $regInfo = AppSetting::getRegistrationStatusInfo();
        if (!$regInfo['is_open'] && !$user->isTester()) {
            return redirect()->route('peserta.dashboard')
                ->with('error', $regInfo['closed_message'] ?: 'Pendaftaran kolektif saat ini sedang ditutup.');
        }

        // Filter only valid rows
        $validRows = array_filter($data, fn ($item) => ! empty($item['is_valid']) && ! empty($item['competition_id']));

        if (empty($validRows)) {
            return redirect()->route('peserta.collective.wizard')->with('error', 'Tidak ada data peserta valid untuk didaftarkan.');
        }

        $totalFee = array_sum(array_column($validRows, 'fee'));
        $bonusResult = self::calculateBonusDiscounts($validRows);
        $totalBonusDiscount = $bonusResult['total_bonus_discount'];
        $bonusSummaryList = $bonusResult['bonus_summary_list'];

        $uniqueCode = 0;
        $finalAmount = max(0, $totalFee - $totalBonusDiscount);
        $invoiceNumber = 'INV-'.date('Ymd').'-'.strtoupper(Str::random(5));

        $notes = 'Pendaftaran kolektif '.count($validRows).' peserta dari '.($user->institution_name ?? $user->name);
        if (! empty($bonusSummaryList)) {
            $notes .= ' • '.implode(', ', $bonusSummaryList);
        }

        // Store payment proof file
        $paymentProofPath = $request->file('payment_proof')->store('payments', 'public');
        AdminSettingsController::ensurePublicStorageSync($paymentProofPath);

        DB::beginTransaction();
        try {
            // 1. Create Master Invoice with attached payment proof
            $invoice = Invoice::create([
                'user_id' => $user->id,
                'invoice_number' => $invoiceNumber,
                'type' => 'kolektif',
                'total_amount' => $totalFee,
                'unique_code' => $uniqueCode,
                'final_amount' => $finalAmount,
                'payment_proof' => $paymentProofPath,
                'status' => 'pending',
                'notes' => $notes,
            ]);

            // 2. Create Registrations & Registration Members
            foreach ($validRows as $row) {
                $comp = Competition::findOrFail($row['competition_id']);

                $regCode = strtoupper($comp->code).'-'.strtoupper(Str::random(6));
                while (Registration::where('registration_code', $regCode)->exists()) {
                    $regCode = strtoupper($comp->code).'-'.strtoupper(Str::random(6));
                }

                $matchType = $row['match_type'] ?? null;
                $subCategory = $row['sub_category'] ?? null;

                if (in_array($comp->code, ['MTQ', 'POP'])) {
                    $rowGender = ! empty($row['gender']) ? $row['gender'] : 'L';
                    $matchType = ($rowGender === 'P') ? 'Putri (PI)' : 'Putra (PA)';
                    $subCategory = $matchType;
                }

                $registration = Registration::create([
                    'competition_id' => $comp->id,
                    'user_id' => $user->id,
                    'invoice_id' => $invoice->id,
                    'registration_code' => $regCode,
                    'team_name' => ! empty($row['team_name']) ? $row['team_name'] : null,
                    'sub_category' => $subCategory,
                    'target_class' => $row['target_class'] ?? null,
                    'match_type' => $matchType,
                    'chosen_song' => ! empty($row['chosen_song']) ? $row['chosen_song'] : null,
                    'institution_name' => ! empty($row['institution_name']) ? $row['institution_name'] : ($user->institution_name ?? 'Mandiri'),
                    'official_name' => ! empty($row['official_name']) ? $row['official_name'] : $user->name,
                    'official_phone' => ! empty($row['official_phone']) ? $row['official_phone'] : $user->phone,
                    'payment_proof' => $paymentProofPath,
                    'status' => 'pending',
                    'is_collective' => true,
                ]);

                // Create Member
                RegistrationMember::create([
                    'registration_id' => $registration->id,
                    'full_name' => $row['name'],
                    'nisn' => ! empty($row['nisn']) ? $row['nisn'] : null,
                    'gender' => ! empty($row['gender']) ? $row['gender'] : 'L',
                    'birth_place' => ! empty($row['birth_place']) ? $row['birth_place'] : null,
                    'birth_date' => ! empty($row['birth_date']) ? date('Y-m-d', strtotime($row['birth_date'])) : null,
                    'role_in_team' => 'Peserta Utama',
                ]);
            }

            DB::commit();

            // Trigger WhatsApp Notifications for Batch Registration
            try {
                // 1. Notify User/Pendaftar
                $userPhone = $user->phone;
                if (! empty($userPhone)) {
                    WablasNotificationService::sendAutoNotification('registration_submitted', [
                        'phone' => $userPhone,
                        'nama_peserta' => $user->name,
                        'nama_sekolah' => $user->institution_name ?? 'Sekolah/Madrasah',
                        'cabang_lomba' => count($validRows).' Peserta (Kolektif)',
                        'kode_pendaftaran' => $invoice->invoice_number,
                        'nominal_biaya' => $finalAmount,
                        'jumlah_peserta' => count($validRows),
                        'link_login' => route('peserta.invoices.show', $invoice->id),
                    ]);
                }

                // 2. Notify Treasurer specifically about New Collective Invoice & Payment Proof
                WablasNotificationService::notifyTreasurerCollectiveInvoice($invoice);
            } catch (\Throwable $e) {
                // Non-blocking
            }

            return redirect()->route('peserta.invoices.show', $invoice->id)
                ->with('success', 'Pendaftaran kolektif dan bukti pembayaran berhasil dikirim dalam satu langkah! Panitia akan segera memverifikasi berkas Anda.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->route('peserta.collective.wizard')->with('error', 'Terjadi kesalahan saat memproses pendaftaran: '.$e->getMessage());
        }
    }

    /**
     * Show Invoice Details & Single Payment Proof Upload Page
     */
    public function showInvoice($id)
    {
        $user = Auth::user();

        $invoice = Invoice::with([
            'user',
            'registrations.competition.category',
            'registrations.members',
            'verifier',
        ])->findOrFail($id);

        // Security check
        if ($user->role !== 'superadmin' && $invoice->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki akses ke tagihan ini.');
        }

        $bankInfo = [
            'bank_name' => AppSetting::get('bank_name', 'Bank Syariah Indonesia (BSI)'),
            'bank_account_number' => AppSetting::get('bank_account_number', '7199242042'),
            'bank_account_holder' => AppSetting::get('bank_account_holder', 'WIJIATIN'),
        ];

        return view('peserta.invoices.show', compact('invoice', 'user', 'bankInfo'));
    }

    /**
     * Upload Single Proof of Payment for Master Invoice
     */
    public function uploadPaymentProof(Request $request, $id)
    {
        $user = Auth::user();
        $invoice = Invoice::where('user_id', $user->id)->findOrFail($id);

        $request->validate([
            'payment_proof' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ], [
            'payment_proof.required' => 'Silakan pilih foto / berkas bukti transfer Anda.',
            'payment_proof.mimes' => 'Format bukti transfer harus berupa JPG, PNG, atau PDF.',
            'payment_proof.max' => 'Ukuran file maksimal 5MB.',
        ]);

        $file = $request->file('payment_proof');
        $filename = 'proof_'.$invoice->invoice_number.'_'.time().'.'.$file->getClientOriginalExtension();
        $path = $file->storeAs('payment_proofs', $filename, 'public');
        AdminSettingsController::ensurePublicStorageSync($path);

        $invoice->update([
            'payment_proof' => $path,
            'status' => 'pending',
        ]);

        // Link payment proof to all individual registrations
        Registration::where('invoice_id', $invoice->id)->update([
            'payment_proof' => $path,
            'status' => 'pending',
        ]);

        // Trigger WhatsApp Notification to Treasurer about uploaded proof
        try {
            WablasNotificationService::notifyTreasurerCollectiveInvoice($invoice);
        } catch (\Throwable $e) {
            // Non-blocking
        }

        return back()->with('success', 'Bukti transfer berhasil diunggah! Panitia akan segera memverifikasi pendaftaran kolektif Anda.');
    }

    /**
     * ================= ADMIN INVOICE MANAGEMENT =================
     */

    /**
     * Admin List of Invoices
     */
    public function adminInvoices(Request $request)
    {
        $status = $request->get('status', 'all');

        $invoicesQuery = Invoice::with(['user', 'registrations.competition', 'registrations.members'])
            ->withCount('registrations')
            ->latest();

        if ($status !== 'all') {
            $invoicesQuery->where('status', $status);
        }

        $invoices = $invoicesQuery->paginate(15)->withQueryString();

        $stats = [
            'total' => Invoice::count(),
            'pending' => Invoice::where('status', 'pending')->count(),
            'verified' => Invoice::where('status', 'verified')->count(),
            'rejected' => Invoice::where('status', 'rejected')->count(),
            'total_nominal' => Invoice::where('status', 'verified')->sum('total_amount'),
        ];

        return view('admin.invoices.index', compact('invoices', 'stats', 'status'));
    }

    /**
     * Admin Show Single Invoice Verification Modal / Page
     */
    public function adminShowInvoice($id)
    {
        $invoice = Invoice::with([
            'user',
            'registrations.competition.category',
            'registrations.members',
            'verifier',
        ])->findOrFail($id);

        return view('admin.invoices.show', compact('invoice'));
    }

    /**
     * Admin Single-Click Batch Approval / Rejection of Master Invoice
     */
    public function adminVerifyInvoice(Request $request, $id)
    {
        $invoice = Invoice::findOrFail($id);

        $request->validate([
            'action' => ['required', 'in:approve,reject'],
            'rejection_reason' => ['nullable', 'string', 'max:500'],
        ]);

        $admin = Auth::user();

        if ($request->action === 'approve') {
            DB::transaction(function () use ($invoice, $admin) {
                // Mark Invoice Verified
                $invoice->update([
                    'status' => 'verified',
                    'verified_at' => now(),
                    'verified_by' => $admin->id,
                    'rejection_reason' => null,
                ]);

                // Mark all registrations inside this invoice as verified & generate participant numbers
                $registrations = Registration::with('competition')->where('invoice_id', $invoice->id)->get();
                foreach ($registrations as $reg) {
                    $reg->status = 'verified';
                    $reg->verified_at = now();
                    $reg->verified_by = $admin->id;
                    $reg->verification_notes = 'Lunas & Disetujui via Invoice Kolektif '.$invoice->invoice_number;
                    if (empty($reg->participant_number)) {
                        $reg->generateParticipantNumber();
                    } else {
                        $reg->save();
                    }
                }
            });

            // Trigger WhatsApp Notification: Pendaftaran Kolektif Terverifikasi Sah
            try {
                $invoice->loadMissing(['user', 'registrations.competition']);
                $targetPhone = $invoice->user?->phone;
                if (! empty($targetPhone)) {
                    $templateCode = \App\Models\WhatsappTemplate::where('code', 'collective_invoice_verified')->where('is_active', true)->exists()
                        ? 'collective_invoice_verified'
                        : 'registration_verified';

                    WablasNotificationService::sendAutoNotification($templateCode, [
                        'phone' => $targetPhone,
                        'nama_peserta' => $invoice->user->name,
                        'nama_pendaftar' => $invoice->user->name,
                        'nisn' => $invoice->user->nisn ?? '-',
                        'nama_sekolah' => $invoice->user->institution_name ?? 'Sekolah/Madrasah',
                        'cabang_lomba' => $invoice->registrations->count().' Peserta (Pendaftaran Kolektif)',
                        'no_peserta' => 'Invoice: '.$invoice->invoice_number,
                        'kode_pendaftaran' => $invoice->invoice_number,
                        'nominal_biaya' => $invoice->final_amount,
                        'jumlah_peserta' => $invoice->registrations->count(),
                        'link_scoreboard' => url('/'),
                        'link_login' => route('peserta.invoices.show', $invoice->id),
                    ]);
                }
            } catch (\Throwable $e) {
                // Non-blocking
            }

            return redirect()->route('admin.invoices.index')
                ->with('success', 'Tagihan '.$invoice->invoice_number.' dan seluruh pendaftaran di dalamnya BERHASIL DISETUJUI & LUNAS.');
        } else {
            DB::transaction(function () use ($invoice, $admin, $request) {
                $invoice->update([
                    'status' => 'rejected',
                    'verified_at' => now(),
                    'verified_by' => $admin->id,
                    'rejection_reason' => $request->rejection_reason ?? 'Bukti transfer tidak sesuai dengan total tagihan.',
                ]);

                Registration::where('invoice_id', $invoice->id)->update([
                    'status' => 'rejected',
                    'verification_notes' => 'Ditolak: '.($request->rejection_reason ?? 'Bukti pembayaran tidak valid.'),
                ]);
            });

            // Trigger WhatsApp Notification: Pendaftaran Kolektif Ditolak
            try {
                $invoice->loadMissing(['user', 'registrations.competition']);
                $targetPhone = $invoice->user?->phone;
                if (! empty($targetPhone)) {
                    $templateCode = \App\Models\WhatsappTemplate::where('code', 'collective_invoice_rejected')->where('is_active', true)->exists()
                        ? 'collective_invoice_rejected'
                        : 'registration_rejected';

                    WablasNotificationService::sendAutoNotification($templateCode, [
                        'phone' => $targetPhone,
                        'nama_peserta' => $invoice->user->name,
                        'nama_pendaftar' => $invoice->user->name,
                        'nisn' => $invoice->user->nisn ?? '-',
                        'nama_sekolah' => $invoice->user->institution_name ?? 'Sekolah/Madrasah',
                        'cabang_lomba' => $invoice->registrations->count().' Peserta (Pendaftaran Kolektif)',
                        'kode_pendaftaran' => $invoice->invoice_number,
                        'nominal_biaya' => $invoice->final_amount,
                        'jumlah_peserta' => $invoice->registrations->count(),
                        'catatan_verifikasi' => $request->rejection_reason ?? 'Bukti transfer / berkas tidak sesuai.',
                        'link_login' => route('peserta.invoices.show', $invoice->id),
                    ]);
                }
            } catch (\Throwable $e) {
                // Non-blocking
            }

            return redirect()->route('admin.invoices.index')
                ->with('info', 'Tagihan '.$invoice->invoice_number.' telah ditolak.');
        }
    }
}
