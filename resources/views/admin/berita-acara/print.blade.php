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
                margin: 10mm 15mm 10mm 15mm;
            }
            body {
                background: #ffffff !important;
                color: #000000 !important;
                font-size: 10.5pt;
                line-height: 1.35;
                font-family: 'Times New Roman', Times, serif;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .no-print {
                display: none !important;
            }
            .page-break {
                page-break-after: always;
                break-after: page;
            }
            .avoid-break {
                page-break-inside: avoid;
                break-inside: avoid;
            }
            table {
                border-collapse: collapse !important;
            }
            th, td {
                border: 1px solid #000000 !important;
            }
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            color: #111827;
        }

        .kop-double-line {
            border-top: 3px solid #000000;
            border-bottom: 1px solid #000000;
            height: 5px;
            margin-top: 6px;
            margin-bottom: 14px;
        }

        .report-table th, .report-table td {
            border: 1px solid #000000;
            padding: 4px 6px;
            font-size: 10pt;
        }

        .report-table th {
            background-color: #f3f4f6;
            font-weight: bold;
            text-align: center;
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen py-4 sm:py-8 font-serif">

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

    <!-- Printable A4 Paper Container -->
    <div class="max-w-[210mm] mx-auto bg-white p-8 sm:p-12 shadow-xl border border-slate-300 print:border-none print:shadow-none print:p-0">

        <!-- ==================== KOP SURAT RESMI ==================== -->
        <div class="flex items-center justify-between gap-4">
            <!-- Logo Madrasah (Kiri) -->
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

            <!-- Teks Kop Surat (Tengah) -->
            <div class="flex-1 text-center space-y-0.5">
                <div class="text-sm font-bold tracking-wider uppercase text-black">
                    PANITIA {{ strtoupper($appSettings['event_name'] ?? 'MILAD KE-57') }}
                </div>
                <div class="text-base sm:text-lg font-black tracking-wide uppercase text-black">
                    {{ strtoupper($appSettings['institution_name'] ?? 'MADRASAH TSANAWIYAH NEGERI 1 BLITAR') }}
                </div>
                <div class="text-[11px] text-black italic">
                    {{ $appSettings['address'] ?? 'Kantor : Jl. Ponpes Terpadu Al-Kamal Kunir Wonodadi Blitar' }}
                </div>
                <div class="text-[10.5px] text-black">
                    Telp. {{ $appSettings['contact_phone'] ?? '0342-561634' }} Kode Pos {{ $appSettings['postal_code'] ?? '66156' }} Website: <span class="underline text-blue-800">{{ $appSettings['school_website'] ?? 'www.mtsn1blitar.sch.id' }}</span>
                </div>
            </div>

            <!-- Logo Milad (Kanan) -->
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

        <!-- Garis Pemisah Kop Surat Ganda -->
        <div class="kop-double-line"></div>

        <!-- ==================== JUDUL DOKUMEN ==================== -->
        <div class="text-center space-y-1 mb-5">
            <h1 class="text-base sm:text-lg font-black tracking-wider uppercase underline underline-offset-4">
                BERITA ACARA
            </h1>
            <div class="text-sm sm:text-base font-bold uppercase tracking-wide">
                {{ $isSports ? 'PERTANDINGAN CABANG' : 'PENJURIAN LOMBA' }} {{ strtoupper($competition->name) }}
            </div>
            <div class="text-xs sm:text-sm font-bold uppercase tracking-wider">
                TINGKAT SD/MI SE-EKS KARESIDENAN KEDIRI
            </div>
            <div class="text-xs sm:text-sm font-bold uppercase tracking-wider">
                DALAM RANGKA {{ strtoupper($appSettings['event_name'] ?? 'MILAD KE 57') }} MTs N 1 BLITAR
            </div>
        </div>

        <!-- ==================== NARASI PEMBUKA ==================== -->
        <div class="text-xs sm:text-[11pt] text-justify space-y-3 mb-4 leading-relaxed">
            <p>
                Bahwa pada hari ini, <strong>{{ $type === 'blank' ? '..........................' : $eventDay }}</strong> tanggal <strong>{{ $type === 'blank' ? '..................................................' : $dateSpelled['day_spelled'] }}</strong> bulan <strong>{{ $type === 'blank' ? '..........................' : $dateSpelled['month_name'] }}</strong> tahun <strong>{{ $type === 'blank' ? '..................................................' : $dateSpelled['year_spelled'] }}</strong> Pukul <strong>{{ $type === 'blank' ? '........' : $eventTime }}</strong> WIB bertempat di MTs N 1 Blitar. Berdasarkan Penilaian {{ $isSports ? 'Dewan Wasit' : 'Dewan Juri' }} yang terdiri dari:
            </p>

            <ol class="list-decimal list-inside pl-4 space-y-1">
                <li>{{ $type === 'blank' ? '........................................................................................................................' : ($judges[0] ?: '....................................................................................................') }}</li>
                <li>{{ $type === 'blank' ? '........................................................................................................................' : ($judges[1] ?: '....................................................................................................') }}</li>
                <li>{{ $type === 'blank' ? '........................................................................................................................' : ($judges[2] ?: '....................................................................................................') }}</li>
            </ol>

            <p>
                Telah memutuskan pemenang dalam {{ $isSports ? 'Pertandingan' : 'Lomba' }} <strong>{{ $competition->name }}</strong> tingkat SD/MI se-karesidenan Kediri. Adapun nama-nama pemenang tersebut adalah sebagai berikut:
            </p>
        </div>

        <!-- ==================== TABEL PEMENANG ==================== -->
        <div class="space-y-4 mb-5">
            @foreach($sectorsData as $secKey => $sector)
                <div class="avoid-break space-y-1.5">
                    @if(count($sectorsData) > 1)
                        <div class="font-bold text-xs sm:text-sm underline">
                            {{ $sector['definition']['title'] }}:
                        </div>
                    @endif

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
                                <tr>
                                    <td class="font-bold text-center">{{ $tier['tier_label'] }}</td>
                                    <td class="text-center font-mono font-bold">{{ $tier['no_peserta'] }}</td>
                                    <td class="font-bold">{{ $tier['nama'] }}</td>
                                    <td>{{ $tier['sekolah'] }}</td>
                                    <td class="text-center font-bold font-mono">{{ $tier['nilai'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endforeach
        </div>

        <!-- ==================== KALIMAT PENUTUP ==================== -->
        <div class="text-xs sm:text-[11pt] text-justify mb-6 avoid-break">
            <p>
                Demikian hasil keputusan ini ditetapkan. Keputusan {{ $isSports ? 'Dewan Wasit' : 'Dewan Juri' }} bersifat mutlak dan tidak dapat diganggu gugat.
            </p>
        </div>

        <!-- ==================== TANDA TANGAN DEWAN JURI / WASIT ==================== -->
        <div class="avoid-break pt-2">
            <div class="text-right text-xs sm:text-[11pt] mb-4 pr-6">
                Blitar, {{ $type === 'blank' ? '........................................' : $dateSpelled['date_formatted'] }}
            </div>

            <div class="grid grid-cols-3 text-center text-xs sm:text-[11pt] gap-4">
                <!-- Juri / Wasit 1 -->
                <div class="flex flex-col justify-between h-28 sm:h-32">
                    <div class="font-bold">{{ $isSports ? 'Wasit 1' : 'Juri 1' }}</div>
                    <div>
                        <div class="font-bold underline underline-offset-2">
                            {{ $type === 'blank' ? '( ........................................ )' : ($judges[0] ?: '( ........................................ )') }}
                        </div>
                    </div>
                </div>

                <!-- Juri / Wasit 2 -->
                <div class="flex flex-col justify-between h-28 sm:h-32">
                    <div class="font-bold">{{ $isSports ? 'Wasit 2' : 'Juri 2' }}</div>
                    <div>
                        <div class="font-bold underline underline-offset-2">
                            {{ $type === 'blank' ? '( ........................................ )' : ($judges[1] ?: '( ........................................ )') }}
                        </div>
                    </div>
                </div>

                <!-- Juri / Wasit 3 -->
                <div class="flex flex-col justify-between h-28 sm:h-32">
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

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
