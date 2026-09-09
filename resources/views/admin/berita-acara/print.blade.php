<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Berita Acara - {{ $competition->name }} - {{ $type === 'blank' ? 'Template' : 'Resmi' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        @media print {
            @page {
                size: A4 portrait;
                margin: 8mm 12mm 6mm 12mm;
            }
            html, body {
                background: #ffffff !important;
                color: #000000 !important;
                font-family: 'Arial Narrow', Arial, 'Nimbus Sans L', sans-serif !important;
                font-size: 11pt !important;
                line-height: 1.25 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .print-page {
                page-break-after: always;
                break-after: page;
                padding: 0 !important;
                margin: 0 !important;
                border: none !important;
                box-shadow: none !important;
                max-width: 100% !important;
                width: 100% !important;
                min-height: 100vh !important;
                display: flex !important;
                flex-direction: column !important;
                justify-content: space-between !important;
            }
            .print-page:last-child {
                page-break-after: auto;
                break-after: auto;
            }
            .avoid-break {
                page-break-inside: avoid;
                break-inside: avoid;
            }
            table {
                border-collapse: collapse !important;
                width: 100% !important;
            }
            th, td {
                border: 1px solid #000000 !important;
            }
        }

        body {
            font-family: 'Arial Narrow', Arial, 'Nimbus Sans L', sans-serif;
            font-size: 11pt;
            line-height: 1.25;
            color: #111827;
        }

        .kop-double-line {
            border-top: 2.5px solid #000000;
            border-bottom: 1px solid #000000;
            height: 4px;
            margin-top: 4px;
            margin-bottom: 10px;
        }

        .report-table th, .report-table td {
            border: 1px solid #000000;
            padding: 3px 6px;
            font-size: 11pt;
            line-height: 1.2;
        }

        .report-table th {
            background-color: #f3f4f6;
            font-weight: bold;
            text-align: center;
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen py-4 sm:py-8 font-sans">

    <!-- Top Action Bar (Hidden on Print) -->
    <div class="max-w-4xl mx-auto mb-4 px-4 no-print flex items-center justify-between gap-3 bg-white p-3.5 rounded-2xl shadow-md border border-slate-200">
        <div class="flex items-center gap-2">
            <button type="button" onclick="window.history.back()" class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition flex items-center gap-1.5 cursor-pointer font-sans">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Kembali</span>
            </button>
            <span class="text-xs font-bold text-slate-500 font-sans">
                Mode: <strong class="text-slate-900">{{ $type === 'blank' ? 'Template Kosong (Siap Tulis Tangan)' : 'Berita Acara Terisi (Live)' }}</strong>
            </span>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="window.print()" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-md transition flex items-center gap-2 cursor-pointer font-sans">
                <i data-lucide="printer" class="w-4 h-4"></i>
                <span>Cetak Dokumen (A4)</span>
            </button>
        </div>
    </div>

    @php
        // Prioritize KOP Kegiatan / Kepanitiaan image as requested
        $kopImage = $appSettings['kop_kegiatan'] ?? ($appSettings['kop_lembaga'] ?? ($appSettings['letterhead_image'] ?? null));
        $eventName = strtoupper($appSettings['event_name'] ?? 'MILAD KE-57');
        $hasMts = str_contains($eventName, 'MTS');
    @endphp

    <div class="space-y-8 print:space-y-0">
        @foreach($sectorsData as $secKey => $sector)
            <!-- Printable A4 Page per Sector -->
            <div class="print-page max-w-[210mm] mx-auto bg-white p-8 sm:p-10 shadow-xl border border-slate-300 print:border-none print:shadow-none print:p-0">
                
                <div>
                    <!-- ==================== KOP SURAT RESMI ==================== -->
                    @if(!empty($kopImage))
                        <div class="mb-3 w-full flex justify-center">
                            <img src="{{ asset('storage/' . $kopImage) }}" alt="Kop Kegiatan" class="w-full h-auto max-h-[115px] object-contain block">
                        </div>
                    @else
                        <!-- Fallback Kop Surat Manual jika belum upload kop kegiatan -->
                        <div class="flex items-center justify-between gap-4">
                            <div class="w-20 h-20 shrink-0 flex items-center justify-center">
                                @if(!empty($appSettings['institution_logo']))
                                    <img src="{{ asset('storage/' . $appSettings['institution_logo']) }}" alt="Logo Madrasah" class="max-h-20 max-w-20 object-contain">
                                @elseif(!empty($appSettings['app_logo']))
                                    <img src="{{ asset('storage/' . $appSettings['app_logo']) }}" alt="Logo" class="max-h-20 max-w-20 object-contain">
                                @elseif(!empty($appSettings['favicon']))
                                    <img src="{{ asset('storage/' . $appSettings['favicon']) }}" alt="Logo" class="max-h-20 max-w-20 object-contain">
                                @else
                                    <div class="w-16 h-16 rounded-full border-2 border-emerald-800 text-emerald-900 font-black flex flex-col items-center justify-center text-center p-1">
                                        <span class="text-[9px] font-bold leading-none">MTsN 1</span>
                                        <span class="text-[11px] font-black tracking-wider leading-none mt-0.5">BLITAR</span>
                                    </div>
                                @endif
                            </div>

                            <div class="flex-1 text-center space-y-0.5">
                                <div class="text-xs font-bold tracking-wider uppercase text-black">
                                    PANITIA {{ strtoupper($appSettings['event_name'] ?? 'MILAD KE-57') }}
                                </div>
                                <div class="text-sm sm:text-base font-black tracking-wide uppercase text-black">
                                    {{ strtoupper($appSettings['institution_name'] ?? 'MADRASAH TSANAWIYAH NEGERI 1 BLITAR') }}
                                </div>
                                <div class="text-[10px] text-black italic">
                                    {{ $appSettings['address'] ?? 'Kantor : Jl. Ponpes Terpadu Al-Kamal Kunir Wonodadi Blitar' }}
                                </div>
                                <div class="text-[9.5px] text-black">
                                    Telp. {{ $appSettings['contact_phone'] ?? '0342-561634' }} Kode Pos {{ $appSettings['postal_code'] ?? '66156' }} Website: <span class="underline text-blue-800">{{ $appSettings['school_website'] ?? 'www.mtsn1blitar.sch.id' }}</span>
                                </div>
                            </div>

                            <div class="w-20 h-20 shrink-0 flex items-center justify-center">
                                @if(!empty($appSettings['event_logo']))
                                    <img src="{{ asset('storage/' . $appSettings['event_logo']) }}" alt="Logo Milad" class="max-h-20 max-w-20 object-contain">
                                @elseif(!empty($appSettings['app_logo']))
                                    <img src="{{ asset('storage/' . $appSettings['app_logo']) }}" alt="Logo" class="max-h-20 max-w-20 object-contain">
                                @else
                                    <div class="w-16 h-16 rounded-full border-2 border-amber-600 text-amber-700 font-black flex flex-col items-center justify-center text-center p-1">
                                        <span class="text-[8px] font-bold leading-none">MILAD 57</span>
                                        <span class="text-[10px] font-black tracking-wider leading-none mt-0.5">TALENTA</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="kop-double-line"></div>
                    @endif

                    <!-- ==================== JUDUL DOKUMEN ==================== -->
                    <div class="text-center space-y-0.5 mb-3">
                        <h1 class="text-base sm:text-lg font-black tracking-wider uppercase underline underline-offset-4">
                            BERITA ACARA
                        </h1>
                        <div class="text-xs sm:text-sm font-bold uppercase tracking-wide">
                            {{ $isSports ? 'PERTANDINGAN CABANG' : 'PENJURIAN LOMBA' }} {{ strtoupper($competition->name) }}
                        </div>
                        @if(count($sectorsData) > 1 || (!empty($sector['definition']['title']) && !str_contains(strtolower($sector['definition']['title']), 'umum')))
                            <div class="text-xs sm:text-sm font-black uppercase tracking-wider text-black">
                                {{ strtoupper($sector['definition']['title']) }}
                            </div>
                        @endif
                        <div class="text-[11pt] font-bold uppercase tracking-wider">
                            TINGKAT SD/MI SE-EKS KARESIDENAN KEDIRI
                        </div>
                        <div class="text-[11pt] font-bold uppercase tracking-wider">
                            DALAM RANGKA {{ $eventName }}{{ $hasMts ? '' : ' MTSN 1 BLITAR' }}
                        </div>
                    </div>

                    <!-- ==================== NARASI PEMBUKA ==================== -->
                    <div class="text-[11pt] text-justify space-y-2 mb-3 leading-snug">
                        <p>
                            Bahwa pada hari ini, <strong>{{ $type === 'blank' ? '..........................' : $eventDay }}</strong> tanggal <strong>{{ $type === 'blank' ? '..................................................' : $dateSpelled['day_spelled'] }}</strong> bulan <strong>{{ $type === 'blank' ? '..........................' : $dateSpelled['month_name'] }}</strong> tahun <strong>{{ $type === 'blank' ? '..................................................' : $dateSpelled['year_spelled'] }}</strong> Pukul <strong>{{ $type === 'blank' ? '........' : $eventTime }}</strong> WIB bertempat di MTs N 1 Blitar. Berdasarkan Penilaian {{ $isSports ? 'Dewan Wasit' : 'Dewan Juri' }} yang terdiri dari:
                        </p>

                        <ol class="list-decimal list-inside pl-4 space-y-0.5">
                            <li>{{ $type === 'blank' ? '........................................................................................................................' : ($judges[0] ?: '....................................................................................................') }}</li>
                            <li>{{ $type === 'blank' ? '........................................................................................................................' : ($judges[1] ?: '....................................................................................................') }}</li>
                            <li>{{ $type === 'blank' ? '........................................................................................................................' : ($judges[2] ?: '....................................................................................................') }}</li>
                        </ol>

                        <p>
                            Telah memutuskan pemenang dalam {{ $isSports ? 'Pertandingan' : 'Lomba' }} <strong>{{ $competition->name }}</strong>{{ (!empty($sector['definition']['title']) && !str_contains(strtolower($sector['definition']['title']), 'umum')) ? ' (' . $sector['definition']['title'] . ')' : '' }} tingkat SD/MI se-karesidenan Kediri. Adapun nama-nama pemenang tersebut adalah sebagai berikut:
                        </p>
                    </div>

                    <!-- ==================== TABEL PEMENANG ==================== -->
                    <div class="mb-3">
                        <table class="w-full report-table">
                            <thead>
                                <tr>
                                    <th style="width: 18%;">Tingkat Juara</th>
                                    <th style="width: 15%;">No. Peserta</th>
                                    <th style="width: 32%;">Nama</th>
                                    <th style="width: 25%;">Asal Sekolah</th>
                                    <th style="width: 10%;">{{ $isSports ? 'Skor' : 'Total Nilai' }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sector['tiers'] as $tier)
                                    @php
                                        $cleanNama = preg_replace('/\s*\([^)]*\)$/', '', $tier['nama']);
                                    @endphp
                                    <tr>
                                        <td class="font-bold text-center">{{ $tier['tier_label'] }}</td>
                                        <td class="text-center font-bold">{{ $tier['no_peserta'] }}</td>
                                        <td class="font-bold">{{ $cleanNama }}</td>
                                        <td>{{ $tier['sekolah'] }}</td>
                                        <td class="text-center font-bold">{{ $tier['nilai'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- ==================== KALIMAT PENUTUP ==================== -->
                    <div class="text-[11pt] text-justify mb-4 leading-snug">
                        <p>
                            Demikian hasil keputusan ini ditetapkan. Keputusan {{ $isSports ? 'Dewan Wasit' : 'Dewan Juri' }} bersifat mutlak dan tidak dapat diganggu gugat.
                        </p>
                    </div>
                </div>

                <!-- ==================== TANDA TANGAN DEWAN JURI / WASIT ==================== -->
                <div class="avoid-break pt-1">
                    <div class="text-right text-[11pt] mb-3 pr-4">
                        Blitar, {{ $type === 'blank' ? '........................................' : $dateSpelled['date_formatted'] }}
                    </div>

                    <div class="grid grid-cols-3 text-center text-[11pt] gap-4">
                        <!-- Juri / Wasit 1 -->
                        <div class="flex flex-col justify-between h-20 sm:h-22">
                            <div class="font-bold">{{ $isSports ? 'Wasit 1' : 'Juri 1' }}</div>
                            <div>
                                <div class="font-bold underline underline-offset-2">
                                    {{ $type === 'blank' ? '( ........................................ )' : ($judges[0] ?: '( ........................................ )') }}
                                </div>
                            </div>
                        </div>

                        <!-- Juri / Wasit 2 -->
                        <div class="flex flex-col justify-between h-20 sm:h-22">
                            <div class="font-bold">{{ $isSports ? 'Wasit 2' : 'Juri 2' }}</div>
                            <div>
                                <div class="font-bold underline underline-offset-2">
                                    {{ $type === 'blank' ? '( ........................................ )' : ($judges[1] ?: '( ........................................ )') }}
                                </div>
                            </div>
                        </div>

                        <!-- Juri / Wasit 3 -->
                        <div class="flex flex-col justify-between h-20 sm:h-22">
                            <div class="font-bold">{{ $isSports ? 'Wasit 3' : 'Juri 3' }}</div>
                            <div>
                                <div class="font-bold underline underline-offset-2">
                                    {{ $type === 'blank' ? '( ........................................ )' : ($judges[2] ?: '( ........................................ )') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        @endforeach
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
