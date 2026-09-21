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

    <!-- Alpine.js (Local) -->
    <script defer src="{{ asset('vendor/alpine/alpine.min.js') }}"></script>

    <style>
        [x-cloak] { display: none !important; }

        *, *::before, *::after {
            box-sizing: border-box;
        }

        body {
            background-color: #0f172a;
            color: #0f172a;
            font-family: 'Plus Jakarta Sans', sans-serif;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            margin: 0;
            padding: 0;
        }

        .print-sheet {
            margin: 0 auto;
            background: #ffffff;
            box-sizing: border-box;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
        }

        .bracket-svg-container {
            flex: 1 1 0%;
            min-height: 0;
            height: 100%;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .bracket-svg-container svg {
            width: 100%;
            height: 100%;
            max-height: 100%;
            max-width: 100%;
            object-fit: contain;
            display: block;
        }

        @media screen {
            body {
                padding: 1.25rem 0.75rem;
                min-height: 100vh;
            }
            .print-sheet.orientation-landscape {
                width: 297mm;
                max-width: 100%;
                height: 200mm;
                max-height: 202mm;
                padding: 4mm 7mm 3mm 7mm;
                margin-bottom: 24px;
                box-shadow: 0 12px 35px -4px rgba(0, 0, 0, 0.4), 0 4px 12px -2px rgba(0, 0, 0, 0.2);
                border: 1px solid #334155;
                border-radius: 8px;
            }
            .print-sheet.orientation-portrait {
                width: 210mm;
                max-width: 100%;
                height: 285mm;
                max-height: 287mm;
                padding: 5mm 6mm 4mm 6mm;
                margin-bottom: 24px;
                box-shadow: 0 12px 35px -4px rgba(0, 0, 0, 0.4), 0 4px 12px -2px rgba(0, 0, 0, 0.2);
                border: 1px solid #334155;
                border-radius: 8px;
            }
        }

        @media print {
            html, body {
                width: 100% !important;
                height: 100% !important;
                min-height: 100% !important;
                max-height: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                background: #ffffff !important;
                overflow: hidden !important;
            }
            .no-print {
                display: none !important;
            }
            .print-sheet {
                width: 100% !important;
                height: 100% !important;
                max-height: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
                box-shadow: none !important;
                border: none !important;
                border-radius: 0 !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
                page-break-before: avoid !important;
                break-before: avoid !important;
                page-break-after: avoid !important;
                break-after: avoid !important;
                overflow: hidden !important;
                display: flex !important;
                flex-direction: column !important;
                justify-content: space-between !important;
            }
            .bracket-svg-container {
                flex: 1 1 0% !important;
                min-height: 0 !important;
                height: 100% !important;
                width: 100% !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                overflow: hidden !important;
            }
            .bracket-svg-container svg {
                width: 100% !important;
                height: 100% !important;
                max-height: 100% !important;
                max-width: 100% !important;
                object-fit: contain !important;
                display: block !important;
            }
            .print-signatures {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
        }
    </style>

    <!-- Dynamic @page Orientation Style Injection -->
    <style id="dynamic-print-page-style">
        @page {
            size: A4 landscape;
            margin: 4mm 6mm;
        }
    </style>
</head>
<body class="antialiased" 
      x-data="{
          orientation: (new URLSearchParams(window.location.search).get('orientation') || localStorage.getItem('talenta_bracket_print_orientation') || 'landscape'),
          setOrientation(mode) {
              this.orientation = mode;
              localStorage.setItem('talenta_bracket_print_orientation', mode);
              this.updatePrintStyle();
              if (window.lucide) { 
                  this.$nextTick(() => window.lucide.createIcons()); 
              }
          },
          updatePrintStyle() {
              const styleEl = document.getElementById('dynamic-print-page-style');
              if (styleEl) {
                  if (this.orientation === 'portrait') {
                      styleEl.innerHTML = `@page { size: A4 portrait !important; margin: 6mm 6mm !important; }`;
                  } else {
                      styleEl.innerHTML = `@page { size: A4 landscape !important; margin: 4mm 6mm !important; }`;
                  }
              }
          }
      }"
      x-init="updatePrintStyle()">

    <!-- Screen Action Control Bar (No Print) -->
    <div class="no-print max-w-[297mm] mx-auto mb-4 px-2">
        <div class="bg-slate-900 text-white px-5 py-3.5 rounded-2xl shadow-xl flex flex-col lg:flex-row items-center justify-between border border-slate-800 gap-4">
            
            <!-- Left Info -->
            <div class="flex items-center gap-3 w-full lg:w-auto justify-between lg:justify-start">
                <button type="button" onclick="smartGoBack('{{ route('pic.bracket', $competition->id) }}')" class="p-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white transition cursor-pointer" title="Kembali ke Bagan PIC">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                </button>
                <div>
                    <h2 class="text-sm font-black flex items-center gap-2">
                        <i data-lucide="printer" class="w-4 h-4 text-emerald-400"></i>
                        <span>Pratinjau Cetak Bagan Pertandingan</span>
                    </h2>
                    <p class="text-xs text-slate-400">{{ $competition->name }} • {{ $activePool['title'] ?? 'Bagan Resmi' }}</p>
                </div>
            </div>

            <!-- Center Menu: Orientation Switcher (Landscape / Portrait) -->
            <div class="flex items-center gap-2 w-full lg:w-auto justify-center">
                <span class="text-xs font-bold text-slate-400 mr-1 flex items-center gap-1.5">
                    <i data-lucide="sliders" class="w-3.5 h-3.5 text-slate-400"></i>
                    <span>Orientasi Kertas:</span>
                </span>
                <div class="inline-flex p-1 bg-slate-950 rounded-xl border border-slate-800 text-xs font-bold shadow-inner">
                    <!-- Landscape Button -->
                    <button type="button" 
                            @click="setOrientation('landscape')"
                            :class="orientation === 'landscape' ? 'bg-emerald-500 text-slate-950 shadow-md font-black' : 'text-slate-400 hover:text-white'"
                            class="px-3 py-1.5 rounded-lg transition cursor-pointer flex items-center gap-1.5">
                        <i data-lucide="layout-template" class="w-3.5 h-3.5"></i>
                        <span>Landscape (Mendatar)</span>
                    </button>

                    <!-- Portrait Button -->
                    <button type="button" 
                            @click="setOrientation('portrait')"
                            :class="orientation === 'portrait' ? 'bg-emerald-500 text-slate-950 shadow-md font-black' : 'text-slate-400 hover:text-white'"
                            class="px-3 py-1.5 rounded-lg transition cursor-pointer flex items-center gap-1.5">
                        <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
                        <span>Portrait (Tegak)</span>
                    </button>
                </div>
            </div>

            <!-- Right Actions -->
            <div class="flex items-center gap-3 w-full lg:w-auto justify-end flex-wrap">
                <div class="bg-amber-500/15 text-amber-300 border border-amber-500/30 px-3 py-1.5 rounded-xl text-xs flex items-center gap-1.5 font-medium">
                    <i data-lucide="info" class="w-3.5 h-3.5 text-amber-400 shrink-0"></i>
                    <span x-text="orientation === 'portrait' ? 'Format 1 Halaman A4 Portrait' : 'Format 1 Halaman A4 Landscape'">Format 1 Halaman A4</span>
                </div>
                <button type="button" onclick="window.print()" class="px-5 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black text-xs shadow-lg shadow-emerald-500/20 transition flex items-center gap-2 cursor-pointer">
                    <i data-lucide="printer" class="w-4 h-4"></i>
                    <span x-text="orientation === 'portrait' ? 'Cetak Sekarang (A4 Portrait)' : 'Cetak Sekarang (A4 Landscape)'">Cetak Sekarang</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Printable Sheet (Strictly 1 Sheet A4 Landscape or Portrait) -->
    <div id="printable-sheet" 
         class="print-sheet transition-all duration-300"
         :class="orientation === 'portrait' ? 'orientation-portrait' : 'orientation-landscape'">

        <!-- Top Header & Kop Surat -->
        <div class="shrink-0">
            @php
                $kopImage = $appSettings['kop_kegiatan'] ?? ($appSettings['kop_lembaga'] ?? ($appSettings['letterhead_image'] ?? null));
            @endphp
            @if(!empty($kopImage))
                <div class="mb-1 w-full flex justify-center">
                    <img src="{{ asset('storage/' . $kopImage) }}" alt="Kop Surat" class="w-full h-auto max-h-[42px] object-contain block mx-auto">
                </div>
            @else
                <div class="border-b-2 border-slate-800 pb-1 mb-1 flex items-center justify-between gap-3">
                    <div class="w-10 h-10 shrink-0 flex items-center justify-center">
                        @if(!empty($appSettings['app_logo']))
                            <img src="{{ asset('storage/' . $appSettings['app_logo']) }}" alt="Logo" class="max-h-9 max-w-9 object-contain">
                        @else
                            <div class="w-8 h-8 rounded-lg bg-slate-900 text-white font-black flex items-center justify-center text-[9px]">
                                TALENTA
                            </div>
                        @endif
                    </div>
                    <div class="flex-1 text-center">
                        <div class="text-[8.5px] font-bold uppercase tracking-wider text-slate-600 leading-tight">PANITIA PELAKSANA {{ $appSettings['event_name'] ?? 'TURNAMEN OLAHRAGA & SENI' }}</div>
                        <div class="text-xs font-black uppercase text-slate-900 leading-tight">{{ $appSettings['institution_name'] ?? 'LEMBAGA PENYELENGGARA TALENTA' }}</div>
                        <div class="text-[7.5px] text-slate-500">{{ $appSettings['address'] ?? 'Blitar, Jawa Timur' }} • Telp: {{ $appSettings['contact_phone'] ?? '-' }}</div>
                    </div>
                    <div class="w-10 h-10 shrink-0 flex items-center justify-center">
                        @if(!empty($appSettings['event_logo']))
                            <img src="{{ asset('storage/' . $appSettings['event_logo']) }}" alt="Event Logo" class="max-h-9 max-w-9 object-contain">
                        @endif
                    </div>
                </div>
            @endif

            <!-- Document Title Bar -->
            <div class="text-center mb-0.5">
                <h1 class="text-[11px] font-black uppercase tracking-wider text-slate-900 leading-tight">
                    BAGAN RESMI PERTANDINGAN SISTEM GUGUR TUNGGAL
                </h1>
                <div class="text-[9px] font-bold text-slate-700 flex items-center justify-center gap-2 mt-0.5">
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
        </div>

        <!-- Middle Section: Classic Vector Bracket Tree SVG -->
        <div class="bracket-svg-container flex-1 min-h-0 w-full flex items-center justify-center my-0.5 overflow-hidden">
            @if(!$bracketData || empty($bracketData['rounds']))
                <div class="py-8 text-center text-xs text-slate-500 italic">
                    Data bagan belum tersedia untuk dicetak.
                </div>
            @else
                {!! $bracketData['classic_svg_light'] !!}
            @endif
        </div>

        <!-- Bottom Section: Official Signatures Block -->
        <div class="shrink-0 print-signatures pt-1 border-t border-slate-300 text-[9px]">
            <div class="grid grid-cols-3 text-center gap-2">
                <div>
                    <div class="text-[8.5px] text-slate-500">Mengetahui,</div>
                    <div class="text-[8.5px] font-bold text-slate-900 uppercase">Ketua Panitia Pelaksana</div>
                    <div class="h-8"></div>
                    <div class="text-[8.5px] font-black text-slate-900 border-b border-slate-600 inline-block px-3 pb-0.5">
                        {{ $appSettings['committee_chairman_name'] ?? '( .................................................... )' }}
                    </div>
                </div>

                <div>
                    <div class="text-[8.5px] text-slate-500">Diverifikasi Oleh,</div>
                    <div class="text-[8.5px] font-bold text-slate-900 uppercase">Koordinator Lomba / PIC</div>
                    <div class="h-8"></div>
                    <div class="text-[8.5px] font-black text-slate-900 border-b border-slate-600 inline-block px-3 pb-0.5">
                        {{ $competition->pic?->name ?? (Auth::user()->name ?: '( .................................................... )') }}
                    </div>
                </div>

                <div>
                    <div class="text-[8.5px] text-slate-500">
                        {{ $appSettings['city'] ?? 'Blitar' }}, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}
                    </div>
                    <div class="text-[8.5px] font-bold text-slate-900 uppercase">Wasit Utama / Referee</div>
                    <div class="h-8"></div>
                    <div class="text-[8.5px] font-black text-slate-900 border-b border-slate-600 inline-block px-3 pb-0.5">
                        ( .................................................... )
                    </div>
                </div>
            </div>

            <!-- Footer page info -->
            <div class="mt-0.5 text-[7px] text-slate-400 flex items-center justify-between font-mono">
                <span>Dokumen Bagan Resmi Dicetak Melalui Sistem Talenta • {{ date('d/m/Y H:i:s') }}</span>
                <span x-text="orientation === 'portrait' ? 'Halaman 1 / 1 (A4 Portrait)' : 'Halaman 1 / 1 (A4 Landscape)'">Halaman 1 / 1 (A4 Landscape)</span>
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
