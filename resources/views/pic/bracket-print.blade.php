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

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800;900&family=Space+Mono:wght@700&display=swap" rel="stylesheet">
    
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
                        <span>Format: <strong>Bagan {{ $bracketData['bracket_size'] }} ({{ $bracketData['total_participants'] }} Peserta, {{ $bracketData['total_byes'] }} BYE)</strong></span>
                    @endif
                </div>
            </div>

            <!-- Bracket Layout Tree Grid -->
            @if(!$bracketData || empty($bracketData['rounds']))
                <div class="py-16 text-center text-xs text-slate-500 italic">
                    Data bagan belum tersedia untuk dicetak.
                </div>
            @else
                <div class="flex items-stretch justify-between gap-3 my-2 w-full">
                    @foreach($bracketData['rounds'] as $rIdx => $round)
                        <div class="flex-1 flex flex-col">
                            <!-- Round Title -->
                            <div class="text-center pb-1 mb-2 border-b border-slate-700">
                                <span class="text-[10px] font-black uppercase tracking-wider text-slate-900 font-mono">
                                    {{ $round['round_name'] }}
                                </span>
                            </div>

                            <!-- Matches column with justify-around -->
                            <div class="flex-1 flex flex-col justify-around gap-2">
                                @foreach($round['matches'] as $match)
                                    @php
                                        $t1 = $match['team1'];
                                        $t2 = $match['team2'];
                                        $isByeAdvance = ($match['status'] === 'bye_advance');
                                        $existing = $match['existing_match'];
                                    @endphp

                                    <div class="border border-slate-700 rounded p-1.5 text-[9px] bg-white relative">
                                        <!-- Match code & Court -->
                                        <div class="flex items-center justify-between text-[8px] font-mono text-slate-500 mb-0.5 border-b border-slate-200 pb-0.5">
                                            <span>{{ $match['match_code'] }}</span>
                                            <span>{{ $existing->court_number ?? '' }}</span>
                                        </div>

                                        <!-- Team 1 -->
                                        <div class="flex items-center justify-between gap-1 py-0.5 border-b border-slate-100">
                                            <div class="flex items-center gap-1 min-w-0">
                                                <span class="font-mono font-bold text-[8px] text-slate-500 w-3.5 shrink-0">
                                                    {{ $t1['slot_number'] ?? '' }}
                                                </span>
                                                @if(!empty($t1['seed_number']))
                                                    <span class="px-1 font-bold text-[7.5px] bg-amber-100 text-amber-900 border border-amber-300 rounded shrink-0">S{{ $t1['seed_number'] }}</span>
                                                @endif
                                                <div class="truncate">
                                                    <strong class="text-slate-900 {{ ($t1['is_bye'] ?? false) ? 'text-slate-400 italic' : '' }}">
                                                        {{ $t1['name'] ?? '............' }}
                                                    </strong>
                                                    @if(!empty($t1['institution']))
                                                        <span class="text-[7.5px] text-slate-500 block truncate">({{ $t1['institution'] }})</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="w-8 h-3.5 border border-slate-300 rounded text-center font-mono font-bold text-[8px] shrink-0 leading-3">
                                                @if($existing && ($existing->team1_set1 > 0 || $existing->team2_set1 > 0))
                                                    {{ $existing->team1_set1 }}-{{ $existing->team1_set2 }}
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Team 2 -->
                                        <div class="flex items-center justify-between gap-1 py-0.5">
                                            <div class="flex items-center gap-1 min-w-0">
                                                <span class="font-mono font-bold text-[8px] text-slate-500 w-3.5 shrink-0">
                                                    {{ $t2['slot_number'] ?? '' }}
                                                </span>
                                                @if(!empty($t2['seed_number']))
                                                    <span class="px-1 font-bold text-[7.5px] bg-amber-100 text-amber-900 border border-amber-300 rounded shrink-0">S{{ $t2['seed_number'] }}</span>
                                                @endif
                                                <div class="truncate">
                                                    <strong class="text-slate-900 {{ ($t2['is_bye'] ?? false) ? 'text-slate-400 italic' : '' }}">
                                                        {{ $t2['name'] ?? '............' }}
                                                    </strong>
                                                    @if(!empty($t2['institution']))
                                                        <span class="text-[7.5px] text-slate-500 block truncate">({{ $t2['institution'] }})</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="w-8 h-3.5 border border-slate-300 rounded text-center font-mono font-bold text-[8px] shrink-0 leading-3">
                                                @if($existing && ($existing->team1_set1 > 0 || $existing->team2_set1 > 0))
                                                    {{ $existing->team2_set1 }}-{{ $existing->team2_set2 }}
                                                @endif
                                            </div>
                                        </div>

                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach

                    <!-- Champion Box -->
                    <div class="w-[120px] shrink-0 flex flex-col justify-center">
                        <div class="border-2 border-amber-500 rounded-lg p-2.5 bg-amber-50/50 text-center">
                            <span class="text-[9px] font-black uppercase text-amber-800 tracking-wider block mb-1">
                                JUARA 1
                            </span>
                            <div class="w-8 h-8 rounded-full bg-amber-200 text-amber-800 flex items-center justify-center mx-auto mb-1.5 font-bold text-xs">
                                🏆
                            </div>
                            <div class="text-[9px] font-black text-slate-900 leading-tight">
                                {{ $bracketData['champion']['name'] ?? '........................' }}
                            </div>
                            <div class="text-[8px] text-slate-600 mt-0.5">
                                {{ $bracketData['champion']['institution'] ?? '........................' }}
                            </div>
                        </div>
                    </div>
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
