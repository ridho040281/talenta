<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\Competition;
use App\Models\Invoice;
use App\Models\Registration;
use App\Models\RegistrationMember;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
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

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('FORMULIR_PENDAFTARAN');

        // Main Header Title
        $sheet->setCellValue('A1', 'FORMULIR PENDAFTARAN KOLEKTIF TALENTA 2026 - MTsN 1 BLITAR');
        $sheet->mergeCells('A1:K1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('064E3B'));

        $sheet->setCellValue('A2', 'Petunjuk: Isi biodata siswa di bawah. Pada kolom CABANG_LOMBA, klik panah drop-down untuk langsung memilih nama lomba yang diikuti.');
        $sheet->mergeCells('A2:K2');
        $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(10)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('475569'));

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
        $sheet->getStyle('A4:K4')->applyFromArray($headerStyle);
        $sheet->getRowDimension(4)->setRowHeight(28);

        // Build Dropdown Options for Competitions (Langsung Nama Lomba)
        $dropdownList = [];
        foreach ($competitions as $c) {
            if ($c->code === 'BLT') {
                $dropdownList[] = "Bulu Tangkis (Kat A: Kls 1-2 • Tunggal PA)";
                $dropdownList[] = "Bulu Tangkis (Kat A: Kls 1-2 • Tunggal PI)";
                $dropdownList[] = "Bulu Tangkis (Kat B: Kls 3-4 • Tunggal PA)";
                $dropdownList[] = "Bulu Tangkis (Kat B: Kls 3-4 • Tunggal PI)";
                $dropdownList[] = "Bulu Tangkis (Kat C: Kls 5-6 • Tunggal PA)";
                $dropdownList[] = "Bulu Tangkis (Kat C: Kls 5-6 • Tunggal PI)";
                $dropdownList[] = "Bulu Tangkis (Ganda PA)";
                $dropdownList[] = "Bulu Tangkis (Ganda PI)";
            } else {
                $dropdownList[] = $c->name;
            }
        }

        // Create Helper Hidden Sheet for Dropdown List
        $listSheet = $spreadsheet->createSheet();
        $listSheet->setTitle('LIST_LOMBA');
        foreach ($dropdownList as $index => $item) {
            $listSheet->setCellValue('A' . ($index + 1), $item);
        }
        $listSheetCount = count($dropdownList);
        $listSheet->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_VERYHIDDEN);

        // Ensure active sheet is the main form
        $spreadsheet->setActiveSheetIndex(0);

        // Sample Data Rows for Guidance (Dibuat 1 nama contoh saja)
        $sampleData = [
            [1, 'Ahmad Zaki Mubarak', '0112345678', 'L', 'Blitar', '2012-04-15', 'SD Islam Al-Falah', 'Olimpiade MIPA', '', 'Ust. Ridwan', '081234567890'],
        ];

        $rowNum = 5;
        foreach ($sampleData as $row) {
            $colLetter = 'A';
            foreach ($row as $val) {
                $sheet->setCellValue($colLetter . $rowNum, $val);
                $colLetter++;
            }
            $sheet->getStyle("A{$rowNum}:K{$rowNum}")->getFont()->setSize(10);
            $sheet->getStyle("A{$rowNum}:K{$rowNum}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('CBD5E1');
            $rowNum++;
        }

        // Apply Data Validation (Dropdowns) to Rows 5 through 200
        for ($r = 5; $r <= 200; $r++) {
            // Dropdown Gender (Col D)
            $genderVal = $sheet->getCell("D{$r}")->getDataValidation();
            $genderVal->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
            $genderVal->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION);
            $genderVal->setAllowBlank(true);
            $genderVal->setShowDropDown(true);
            $genderVal->setFormula1('"L,P"');

            // Dropdown Cabang Lomba (Col H)
            $compVal = $sheet->getCell("H{$r}")->getDataValidation();
            $compVal->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
            $compVal->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION);
            $compVal->setAllowBlank(true);
            $compVal->setShowInputMessage(true);
            $compVal->setShowErrorMessage(true);
            $compVal->setShowDropDown(true);
            $compVal->setPromptTitle('Pilih Nama Lomba');
            $compVal->setPrompt('Klik panah drop-down untuk memilih nama cabang lomba');
            $compVal->setFormula1("LIST_LOMBA!\$A\$1:\$A\${$listSheetCount}");
        }

        // Auto-fit Column Widths A through K
        foreach (range('A', 'K') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $fileName = 'Template_Pendaftaran_Kolektif_TALENTA_2026.xlsx';

        return new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
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
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getRealPath());
            $sheet = $spreadsheet->getSheet(0); // Single sheet
            $rows = $sheet->toArray(null, true, true, true);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal membaca file Excel: ' . $e->getMessage());
        }

        if (count($rows) < 5) {
            return back()->with('error', 'File Excel kosong atau tidak memiliki data peserta.');
        }

        $competitions = Competition::with('category')->withCount(['registrations' => function($q) {
            $q->whereIn('status', ['pending', 'verified']);
        }])->get()->keyBy(fn($item) => strtoupper(trim($item->code)));

        $parsedRows = [];
        $totalFee = 0;
        $validRowCount = 0;
        $errorRowCount = 0;
        $competitionCounts = [];
        $registeredNisnsInBatch = []; // Keyed by [competition_code][nisn] => row_number

        $invalidPatterns = [
            '0000000000', '1111111111', '2222222222', '3333333333', '4444444444',
            '5555555555', '6666666666', '7777777777', '8888888888', '9999999999',
            '1234567890', '0123456789', '9876543210', '0987654321'
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
            if (!empty($rawComp)) {
                if (stripos($rawComp, 'Bulu Tangkis') !== false || stripos($rawComp, 'BLT') !== false) {
                    $code = 'BLT';
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

            // Extract Bulu Tangkis sub-category if applicable
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
                    $subCategory = 'Ganda - ' . $matchType;
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

                    $subCategory = $targetClass . ' - ' . $matchType;
                }
            }

            $teamName = trim($row['I'] ?? '');
            $officialName = trim($row['J'] ?? '') ?: Auth::user()->name;
            $officialPhone = trim($row['K'] ?? '') ?: Auth::user()->phone;

            // Skip empty rows
            if (empty($name) && empty($code)) {
                continue;
            }

            $errors = [];

            if (empty($name)) {
                $errors[] = 'Nama peserta kosong';
            }

            // Validate NISN format if given
            if (!empty($nisn)) {
                if (!preg_match('/^[0-9]{8,12}$/', $nisn)) {
                    $errors[] = "Format NISN '{$nisn}' tidak valid (harus 10 digit angka)";
                } elseif (in_array($nisn, $invalidPatterns)) {
                    $errors[] = "NISN '{$nisn}' terdeteksi angka acak/palsu";
                }
            }

            if (empty($code)) {
                $errors[] = 'Cabang lomba belum dipilih';
            } elseif (!isset($competitions[$code])) {
                $errors[] = "Cabang lomba '{$rawComp}' tidak dikenali di sistem";
            } else {
                $comp = $competitions[$code];
                if ($comp->status !== 'buka') {
                    $errors[] = "Lomba '{$comp->name}' saat ini berstatus {$comp->status}";
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
                    if (!empty($nisn)) {
                        // A. Check duplicate in same Excel batch
                        if (isset($registeredNisnsInBatch[$code][$nisn])) {
                            $prevRow = $registeredNisnsInBatch[$code][$nisn];
                            $errors[] = "Siswa dengan NISN '{$nisn}' didaftarkan ganda pada cabang {$comp->name} (duplikat baris {$prevRow})";
                        } else {
                            $registeredNisnsInBatch[$code][$nisn] = $i;
                        }

                        // B. Check duplicate in database for the same competition
                        $alreadyInDb = RegistrationMember::where('nisn', $nisn)
                            ->whereHas('registration', function($q) use ($comp) {
                                $q->where('competition_id', $comp->id)
                                  ->whereIn('status', ['pending', 'verified']);
                            })
                            ->exists();

                        if ($alreadyInDb) {
                            $errors[] = "Siswa dengan NISN '{$nisn}' sudah terdaftar sebelumnya pada cabang {$comp->name}";
                        }
                    }
                }
            }

            if (!in_array($gender, ['L', 'P'])) {
                $gender = 'L';
            }

            $compObj = $competitions[$code] ?? null;
            $fee = $compObj ? (float)$compObj->registration_fee : 0;

            // Tiered pricing for Bulu Tangkis (Tunggal & Ganda, Kat A, Kat B, Kat C)
            if ($code === 'BLT' || (isset($compObj) && $compObj->code === 'BLT')) {
                $isGanda = stripos($rawComp, 'Ganda') !== false || stripos($rawComp, 'GPA') !== false || stripos($rawComp, 'GPI') !== false || stripos($matchType ?? '', 'Ganda') !== false;
                
                if ($isGanda) {
                    $fee = (float) AppSetting::get('blt_fee_ganda', AppSetting::get('blt_fee_c_ganda', 125000));
                } else {
                    $feeA = (float) AppSetting::get('blt_fee_a_tunggal', AppSetting::get('blt_fee_a', 75000));
                    $feeB = (float) AppSetting::get('blt_fee_b_tunggal', AppSetting::get('blt_fee_b', 100000));
                    $feeC = (float) AppSetting::get('blt_fee_c_tunggal', AppSetting::get('blt_fee_c', 125000));

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

        // Exact nominal amount without unique rupiah code
        $uniqueCode = 0;
        $finalAmount = $totalFee;

        $bankInfo = [
            'bank_name' => AppSetting::get('bank_name', 'Bank Syariah Indonesia (BSI)'),
            'bank_account_number' => AppSetting::get('bank_account_number', '7145 8892 01'),
            'bank_account_holder' => AppSetting::get('bank_account_holder', 'Panitia TALENTA MTsN 1 Blitar'),
        ];

        return view('peserta.collective.preview', compact(
            'parsedRows',
            'validRowCount',
            'errorRowCount',
            'totalFee',
            'uniqueCode',
            'finalAmount',
            'bankInfo'
        ));
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
        if (!$data || !is_array($data)) {
            return redirect()->route('peserta.collective.wizard')->with('error', 'Data pendaftaran tidak valid.');
        }

        $user = Auth::user();

        // Filter only valid rows
        $validRows = array_filter($data, fn($item) => !empty($item['is_valid']) && !empty($item['competition_id']));

        if (empty($validRows)) {
            return redirect()->route('peserta.collective.wizard')->with('error', 'Tidak ada data peserta valid untuk didaftarkan.');
        }

        $totalFee = array_sum(array_column($validRows, 'fee'));
        $uniqueCode = 0;
        $finalAmount = $totalFee;
        $invoiceNumber = 'INV-' . date('Ymd') . '-' . strtoupper(Str::random(5));

        // Store payment proof file
        $paymentProofPath = $request->file('payment_proof')->store('payments', 'public');

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
                'notes' => 'Pendaftaran kolektif ' . count($validRows) . ' peserta dari ' . ($user->institution_name ?? $user->name),
            ]);

            // 2. Create Registrations & Registration Members
            foreach ($validRows as $row) {
                $comp = Competition::findOrFail($row['competition_id']);
                
                $regCode = strtoupper($comp->code) . '-' . strtoupper(Str::random(6));
                while (Registration::where('registration_code', $regCode)->exists()) {
                    $regCode = strtoupper($comp->code) . '-' . strtoupper(Str::random(6));
                }

                $registration = Registration::create([
                    'competition_id' => $comp->id,
                    'user_id' => $user->id,
                    'invoice_id' => $invoice->id,
                    'registration_code' => $regCode,
                    'team_name' => !empty($row['team_name']) ? $row['team_name'] : null,
                    'sub_category' => $row['sub_category'] ?? null,
                    'target_class' => $row['target_class'] ?? null,
                    'match_type' => $row['match_type'] ?? null,
                    'institution_name' => !empty($row['institution_name']) ? $row['institution_name'] : ($user->institution_name ?? 'Mandiri'),
                    'official_name' => !empty($row['official_name']) ? $row['official_name'] : $user->name,
                    'official_phone' => !empty($row['official_phone']) ? $row['official_phone'] : $user->phone,
                    'payment_proof' => $paymentProofPath,
                    'status' => 'pending',
                    'is_collective' => true,
                ]);

                // Create Member
                RegistrationMember::create([
                    'registration_id' => $registration->id,
                    'full_name' => $row['name'],
                    'nisn' => !empty($row['nisn']) ? $row['nisn'] : null,
                    'gender' => !empty($row['gender']) ? $row['gender'] : 'L',
                    'birth_place' => !empty($row['birth_place']) ? $row['birth_place'] : null,
                    'birth_date' => !empty($row['birth_date']) ? date('Y-m-d', strtotime($row['birth_date'])) : null,
                    'role_in_team' => 'Peserta Utama',
                ]);
            }

            DB::commit();

            // Trigger WhatsApp Notifications for Batch Registration
            try {
                // 1. Notify User/Pendaftar
                $userPhone = $user->phone;
                if (!empty($userPhone)) {
                    \App\Services\WablasNotificationService::sendAutoNotification('registration_submitted', [
                        'phone' => $userPhone,
                        'nama_peserta' => $user->name,
                        'nama_sekolah' => $user->institution_name ?? 'Sekolah/Madrasah',
                        'cabang_lomba' => count($cleanBatch) . ' Peserta (Kolektif)',
                        'kode_pendaftaran' => $invoice->invoice_number,
                        'link_login' => route('peserta.invoices.show', $invoice->id),
                    ]);
                }

                // 2. Notify Treasurer about New Collective Payment
                $firstReg = Registration::with('competition')->where('invoice_id', $invoice->id)->first();
                if ($firstReg) {
                    \App\Services\WablasNotificationService::notifyTreasurerNewPayment($firstReg, $totalAmount);
                }
            } catch (\Throwable $e) {
                // Non-blocking
            }

            return redirect()->route('peserta.invoices.show', $invoice->id)
                ->with('success', 'Pendaftaran kolektif dan bukti pembayaran berhasil dikirim dalam satu langkah! Panitia akan segera memverifikasi berkas Anda.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('peserta.collective.wizard')->with('error', 'Terjadi kesalahan saat memproses pendaftaran: ' . $e->getMessage());
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
            'verifier'
        ])->findOrFail($id);

        // Security check
        if ($user->role !== 'superadmin' && $invoice->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki akses ke tagihan ini.');
        }

        return view('peserta.invoices.show', compact('invoice', 'user'));
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
        $filename = 'proof_' . $invoice->invoice_number . '_' . time() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('payment_proofs', $filename, 'public');

        $invoice->update([
            'payment_proof' => $path,
            'status' => 'pending',
        ]);

        // Link payment proof to all individual registrations
        Registration::where('invoice_id', $invoice->id)->update([
            'payment_proof' => $path,
            'status' => 'pending',
        ]);

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
            'verifier'
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
                    $reg->verification_notes = 'Lunas & Disetujui via Invoice Kolektif ' . $invoice->invoice_number;
                    if (empty($reg->participant_number)) {
                        $reg->generateParticipantNumber();
                    } else {
                        $reg->save();
                    }
                }
            });

            return redirect()->route('admin.invoices.index')
                ->with('success', 'Tagihan ' . $invoice->invoice_number . ' dan seluruh pendaftaran di dalamnya BERHASIL DISETUJUI & LUNAS.');
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
                    'verification_notes' => 'Ditolak: ' . ($request->rejection_reason ?? 'Bukti pembayaran tidak valid.'),
                ]);
            });

            return redirect()->route('admin.invoices.index')
                ->with('info', 'Tagihan ' . $invoice->invoice_number . ' telah ditolak.');
        }
    }
}
