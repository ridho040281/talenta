<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BAGAN PERTANDINGAN - {{ $competition->name }} - {{ $activePool['title'] ?? '' }} | {{ $appSettings['event_name'] ?? 'TALENTA' }}</title>
    
    <!-- Favicon -->
    @if(!empty($appSettings['favicon']))
        <link rel="icon" type="image/png" href="{{ asset('storage/' . $appSettings['favicon']) }}">
        <link rel="shortcut icon" href="{{ asset('storage/' . $appSettings['favicon']) }}">
        <link rel="apple-touch-icon" href="{{ asset('storage/' . $appSettings['favicon']) }}">
    @else
        <link rel="icon" href="{{ asset('favicon.ico') }}">
    @endif

    <!-- Self-hosted Fonts (lokal) -->
    <link rel="stylesheet" href="{{ asset('vendor/fonts/fonts.css') }}">
    
    <!-- Vite Local Tailwind CSS & JS Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Lucide Icons -->
    <script src="{{ asset('vendor/lucide/lucide.min.js') }}"></script>

    <style>
        @page {
            size: A4 landscape !important;
            margin: 6mm 10mm;
        }

        body {
            background-color: #f1f5f9;
            color: #0f172a;
            font-family: 'Plus Jakarta Sans', sans-serif;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .print-sheet {
            width: 285mm;
            min-height: 195mm;
            margin: 0 auto;
            background: white;
            box-sizing: border-box;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        @media screen {
            .print-sheet {
                box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.1), 0 2px 6px -1px rgba(0, 0, 0, 0.06);
                border: 1px solid #cbd5e1;
                border-radius: 6px;
                padding: 8mm 10mm;
                margin-bottom: 24px;
            }
        }

        @media print {
            body {
                background: white !important;
                padding: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .print-sheet {
                width: 100% !important;
                min-height: auto !important;
                box-shadow: none !important;
                border: none !important;
                padding: 0 !important;
                margin: 0 !important;
            }
        }

        .bracket-slot-box {
            border: 1.5px solid #334155;
            background: #ffffff;
        }
        .bracket-slot-bye {
            border: 1.5px dashed #94a3b8;
            background: #f8fafc;
        }
    </style>
</head>
<body class="py-6 sm:py-8 antialiased">

    <!-- Screen Action Control Bar (No Print) -->
    <div class="no-print max-w-[285mm] mx-auto mb-5 px-4">
        <div class="bg-slate-900 text-white px-6 py-4 rounded-2xl shadow-xl flex items-center justify-between border border-slate-800 flex-wrap gap-4">
            <div class="flex items-center gap-3">
                <button type="button" onclick="smartGoBack('{{ route('pic.bracket', $competition->id) }}')" class="p-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white transition cursor-pointer">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                </button>
                <div>
                    <h2 class="text-sm font-black">Pratinjau Cetak Bagan Pertandingan (A4 Landscape)</h2>
                    <p class="text-xs text-slate-400">{{ $competition->name }} • {{ $activePool['title'] ?? 'Bagan Resmi' }}</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <button type="button" onclick="window.print()" class="px-5 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black text-xs shadow-lg shadow-emerald-500/20 transition flex items-center gap-2 cursor-pointer">
                    <i data-lucide="printer" class="w-4 h-4"></i>
                    <span>Cetak Sekarang (Print / PDF)</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Printable Sheet (A4 Landscape) -->
    <div class="print-sheet">

        <!-- Top Header & Kop Surat -->
        <div>
            @php
                $kopImage = $appSettings['kop_kegiatan'] ?? ($appSettings['kop_lembaga'] ?? ($appSettings['letterhead_image'] ?? null));
            @endphp
            @if(!empty($kopImage))
                <div class="mb-3 w-full flex justify-center">
                    <img src="{{ asset('storage/' . $kopImage) }}" alt="Kop Surat" class="w-full h-auto max-h-[110px] object-contain block">
                </div>
            @else
                <div class="border-b-2 border-slate-800 pb-2 mb-3 flex items-center justify-between gap-4">
                    <div class="w-14 h-14 shrink-0 flex items-center justify-center">
                        @if(!empty($appSettings['app_logo']))
                            <img src="{{ asset('storage/' . $appSettings['app_logo']) }}" alt="Logo" class="max-h-14 max-w-14 object-contain">
                        @else
                            <div class="w-12 h-12 rounded-xl bg-slate-900 text-white font-black flex items-center justify-center text-xs">
                                TALENTA
                            </div>
                        @endif
                    </div>
                    <div class="flex-1 text-center">
                        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-600">PANITIA PELAKSANA {{ $appSettings['event_name'] ?? 'TURNAMEN OLAHRAGA & SENI' }}</div>
                        <div class="text-base font-black uppercase text-slate-900 leading-tight">{{ $appSettings['institution_name'] ?? 'LEMBAGA PENYELENGGARA TALENTA' }}</div>
                        <div class="text-[9px] text-slate-500 mt-0.5">{{ $appSettings['address'] ?? 'Blitar, Jawa Timur' }} • Telp: {{ $appSettings['contact_phone'] ?? '-' }}</div>
                    </div>
                    <div class="w-14 h-14 shrink-0 flex items-center justify-center">
                        @if(!empty($appSettings['event_logo']))
                            <img src="{{ asset('storage/' . $appSettings['event_logo']) }}" alt="Event Logo" class="max-h-14 max-w-14 object-contain">
                        @endif
                    </div>
                </div>
            @endif

            <!-- Document Title Bar -->
            <div class="text-center mb-3">
                <h1 class="text-sm font-black uppercase tracking-wider text-slate-900">
                    BAGAN RESMI PERTANDINGAN SISTEM GUGUR TUNGGAL
                </h1>
                <div class="text-xs font-bold text-slate-700 flex items-center justify-center gap-3 mt-0.5">
                    <span>Cabang: <strong>{{ $competition->name }}</strong></span>
                    <span>•</span>
                    <span>Kategori: <strong>{{ $activePool['title'] ?? 'Semua' }}</strong></span>
                    @if($bracketData)
                        <span>•</span>
                        @if(!empty($bracketData['playoffs']['has_playoffs']))
                            <span>Format: <strong>Bagan {{ $bracketData['bracket_size'] }} + {{ $bracketData['playoffs']['num_playoffs'] }} Play-off ({{ $bracketData['total_participants'] }} Peserta)</strong></span>
                        @else
                            <span>Format: <strong>Bagan {{ $bracketData['bracket_size'] }} ({{ $bracketData['total_participants'] }} Peserta, {{ $bracketData['total_byes'] }} BYE)</strong></span>
                        @endif
                    @endif
                </div>
            </div>

            <!-- Classic Vector Bracket Tree (Persis Standar BWF GOR) -->
            @if(!$bracketData || empty($bracketData['rounds']))
                <div class="py-16 text-center text-xs text-slate-500 italic">
                    Data bagan belum tersedia untuk dicetak.
                </div>
            @else
                <div class="my-2 w-full flex justify-center items-center overflow-hidden">
                    {!! $bracketData['classic_svg_light'] !!}
                </div>
            @endif

        </div>

        <!-- Official Signatures Block -->
        <div class="pt-3 border-t border-slate-300 text-xs">
            <div class="grid grid-cols-3 text-center gap-4">
                <div>
                    <div class="text-[10px] text-slate-500">Mengetahui,</div>
                    <div class="text-[10px] font-bold text-slate-900 uppercase">Ketua Panitia Pelaksana</div>
                    <div class="h-14"></div>
                    <div class="text-[10px] font-bold text-slate-900 border-b border-slate-400 inline-block px-8 pb-0.5">
                        ( .................................................... )
                    </div>
                </div>

                <div>
                    <div class="text-[10px] text-slate-500">Diverifikasi Oleh,</div>
                    <div class="text-[10px] font-bold text-slate-900 uppercase">Koordinator Lomba / PIC</div>
                    <div class="h-14"></div>
                    <div class="text-[10px] font-bold text-slate-900 border-b border-slate-400 inline-block px-8 pb-0.5">
                        ( .................................................... )
                    </div>
                </div>

                <div>
                    <div class="text-[10px] text-slate-500">
                        {{ $appSettings['city'] ?? 'Blitar' }}, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}
                    </div>
                    <div class="text-[10px] font-bold text-slate-900 uppercase">Wasit Utama / Referee</div>
                    <div class="h-14"></div>
                    <div class="text-[10px] font-bold text-slate-900 border-b border-slate-400 inline-block px-8 pb-0.5">
                        ( .................................................... )
                    </div>
                </div>
            </div>

            <!-- Footer page info -->
            <div class="mt-2 text-[8px] text-slate-400 flex items-center justify-between font-mono">
                <span>Dokumen Bagan Resmi Dicetak Melalui Sistem Talenta • {{ date('d/m/Y H:i:s') }}</span>
                <span>Halaman 1 / 1</span>
            </div>
        </div>

    </div>

    <script>
        if (window.lucide) {
            window.lucide.createIcons();
        }

        function smartGoBack(fallbackUrl) {
            if (window.opener && !window.opener.closed) {
                window.close();
                return;
            }

            if (document.referrer && document.referrer !== window.location.href && document.referrer.indexOf(window.location.origin) === 0) {
                if (window.history.length > 1) {
                    window.history.back();
                    setTimeout(function() {
                        window.location.href = document.referrer;
                    }, 250);
                    return;
                } else {
                    window.location.href = document.referrer;
                    return;
                }
            }

            try {
                window.close();
            } catch (e) {}

            window.location.href = fallbackUrl;
        }
    </script>
</body>
</html>
