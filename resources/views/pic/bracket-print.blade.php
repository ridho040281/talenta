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
            /* A4 */
            .print-sheet.size-a4.orientation-landscape {
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
            .print-sheet.size-a4.orientation-portrait {
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
            /* A3 */
            .print-sheet.size-a3.orientation-landscape {
                width: 420mm;
                max-width: 100%;
                height: 285mm;
                max-height: 290mm;
                padding: 6mm 9mm 5mm 9mm;
                margin-bottom: 24px;
                box-shadow: 0 16px 45px -4px rgba(0, 0, 0, 0.45);
                border: 1px solid #334155;
                border-radius: 10px;
            }
            .print-sheet.size-a3.orientation-portrait {
                width: 297mm;
                max-width: 100%;
                height: 405mm;
                max-height: 410mm;
                padding: 7mm 8mm 6mm 8mm;
                margin-bottom: 24px;
                box-shadow: 0 16px 45px -4px rgba(0, 0, 0, 0.45);
                border: 1px solid #334155;
                border-radius: 10px;
            }
            /* F4 / Folio */
            .print-sheet.size-f4.orientation-landscape {
                width: 330mm;
                max-width: 100%;
                height: 205mm;
                max-height: 210mm;
                padding: 4mm 7mm 3mm 7mm;
                margin-bottom: 24px;
                box-shadow: 0 12px 35px -4px rgba(0, 0, 0, 0.4);
                border: 1px solid #334155;
                border-radius: 8px;
            }
            .print-sheet.size-f4.orientation-portrait {
                width: 215mm;
                max-width: 100%;
                height: 318mm;
                max-height: 322mm;
                padding: 5mm 6mm 4mm 6mm;
                margin-bottom: 24px;
                box-shadow: 0 12px 35px -4px rgba(0, 0, 0, 0.4);
                border: 1px solid #334155;
                border-radius: 8px;
            }
            /* Poster / Bebas Ukuran (Auto) */
            .print-sheet.size-poster.orientation-landscape {
                width: 100%;
                max-width: 1360px;
                min-height: 780px;
                padding: 8mm 12mm 6mm 12mm;
                margin-bottom: 24px;
                box-shadow: 0 20px 50px -4px rgba(0, 0, 0, 0.5);
                border: 1px solid #334155;
                border-radius: 12px;
            }
            .print-sheet.size-poster.orientation-portrait {
                width: 100%;
                max-width: 900px;
                min-height: 1100px;
                padding: 8mm 10mm 6mm 10mm;
                margin-bottom: 24px;
                box-shadow: 0 20px 50px -4px rgba(0, 0, 0, 0.5);
                border: 1px solid #334155;
                border-radius: 12px;
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
            .sheet-scroll-wrapper {
                overflow: visible !important;
                padding: 0 !important;
                margin: 0 !important;
                width: 100% !important;
                height: 100% !important;
                display: block !important;
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

    <!-- Dynamic @page Orientation & Size Style Injection -->
    <style id="dynamic-print-page-style">
        @page {
            size: A4 landscape;
            margin: 4mm 6mm;
        }
    </style>
</head>
<body class="antialiased" x-data="bracketPrintApp">

    <!-- Screen Action Control Bar (No Print) -->
    <div class="no-print max-w-7xl mx-auto mb-3 px-2">
        <div class="bg-slate-900 text-white px-5 py-3.5 rounded-2xl shadow-xl flex flex-col xl:flex-row items-center justify-between border border-slate-800 gap-4">
            
            <!-- Left Info -->
            <div class="flex items-center gap-3 w-full xl:w-auto justify-between xl:justify-start">
                <button type="button" onclick="smartGoBack('{{ route('pic.bracket', $competition->id) }}')" class="p-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white transition cursor-pointer" title="Kembali ke Bagan PIC">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                </button>
                <div>
                    <h2 class="text-sm font-black flex items-center gap-2">
                        <i data-lucide="printer" class="w-4 h-4 text-emerald-400"></i>
                        <span>Cetak & Ekspor Bagan Pertandingan</span>
                    </h2>
                    <p class="text-xs text-slate-400">{{ $competition->name }} • {{ $activePool['title'] ?? 'Bagan Resmi' }}</p>
                </div>
            </div>

            <!-- Center Controls: Paper Size & Orientation Switchers -->
            <div class="flex items-center gap-3 w-full xl:w-auto justify-center flex-wrap">
                <!-- Paper Size Switcher -->
                <div class="flex items-center gap-1.5">
                    <span class="text-xs font-bold text-slate-400 flex items-center gap-1">
                        <i data-lucide="file" class="w-3.5 h-3.5 text-slate-400"></i>
                        <span>Kertas:</span>
                    </span>
                    <div class="inline-flex p-1 bg-slate-950 rounded-xl border border-slate-800 text-xs font-bold shadow-inner">
                        <button type="button" 
                                @click="setPaperSize('a4')"
                                :class="paperSize === 'a4' ? 'bg-emerald-500 text-slate-950 shadow-md font-black' : 'text-slate-400 hover:text-white'"
                                class="px-2.5 py-1 rounded-lg transition cursor-pointer"
                                title="Ukuran A4 Standar (297 x 210 mm)">
                            A4
                        </button>
                        <button type="button" 
                                @click="setPaperSize('a3')"
                                :class="paperSize === 'a3' ? 'bg-emerald-500 text-slate-950 shadow-md font-black' : 'text-slate-400 hover:text-white'"
                                class="px-2.5 py-1 rounded-lg transition cursor-pointer"
                                title="Ukuran A3 Besar (420 x 297 mm - 2x A4)">
                            A3
                        </button>
                        <button type="button" 
                                @click="setPaperSize('f4')"
                                :class="paperSize === 'f4' ? 'bg-emerald-500 text-slate-950 shadow-md font-black' : 'text-slate-400 hover:text-white'"
                                class="px-2.5 py-1 rounded-lg transition cursor-pointer"
                                title="Ukuran Folio / F4 (330 x 215 mm)">
                            F4
                        </button>
                        <button type="button" 
                                @click="setPaperSize('poster')"
                                :class="paperSize === 'poster' ? 'bg-amber-400 text-slate-950 shadow-md font-black' : 'text-slate-400 hover:text-white'"
                                class="px-2.5 py-1 rounded-lg transition cursor-pointer flex items-center gap-1"
                                title="Mode Poster / Bebas Ukuran (Auto size - bisa A2, A1, A0, Plotter, atau Cetak Poster Multi-Lembar)">
                            <i data-lucide="sparkles" class="w-3 h-3 text-amber-950"></i>
                            <span>Poster / Bebas</span>
                        </button>
                    </div>
                </div>

                <!-- Orientation Switcher -->
                <div class="flex items-center gap-1.5">
                    <span class="text-xs font-bold text-slate-400 flex items-center gap-1">
                        <i data-lucide="sliders" class="w-3.5 h-3.5 text-slate-400"></i>
                        <span>Posisi:</span>
                    </span>
                    <div class="inline-flex p-1 bg-slate-950 rounded-xl border border-slate-800 text-xs font-bold shadow-inner">
                        <button type="button" 
                                @click="setOrientation('landscape')"
                                :class="orientation === 'landscape' ? 'bg-emerald-500 text-slate-950 shadow-md font-black' : 'text-slate-400 hover:text-white'"
                                class="px-2.5 py-1 rounded-lg transition cursor-pointer flex items-center gap-1"
                                title="Landscape / Mendatar">
                            <i data-lucide="layout-template" class="w-3.5 h-3.5"></i>
                            <span class="hidden sm:inline">Landscape</span>
                        </button>
                        <button type="button" 
                                @click="setOrientation('portrait')"
                                :class="orientation === 'portrait' ? 'bg-emerald-500 text-slate-950 shadow-md font-black' : 'text-slate-400 hover:text-white'"
                                class="px-2.5 py-1 rounded-lg transition cursor-pointer flex items-center gap-1"
                                title="Portrait / Tegak">
                            <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
                            <span class="hidden sm:inline">Portrait</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Right Actions: Download & Print Buttons -->
            <div class="flex items-center gap-2 w-full xl:w-auto justify-end flex-wrap">
                <!-- Unduh SVG -->
                <button type="button" 
                        @click="downloadSvg()"
                        class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white font-bold text-xs border border-slate-700 transition flex items-center gap-1.5 cursor-pointer shadow-sm"
                        title="Unduh file Vektor SVG murni untuk cetak banner/spanduk MMT di percetakan tanpa batas resolusi">
                    <i data-lucide="download" class="w-3.5 h-3.5 text-sky-400"></i>
                    <span>Unduh SVG</span>
                </button>

                <!-- Unduh PNG HD -->
                <button type="button" 
                        @click="downloadPng()"
                        :disabled="isExportingImage"
                        class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white font-bold text-xs border border-slate-700 transition flex items-center gap-1.5 cursor-pointer shadow-sm disabled:opacity-50"
                        title="Unduh gambar PNG HD Resolusi Tinggi 3600px siap cetak poster">
                    <span x-show="!isExportingImage" class="inline-flex items-center gap-1.5">
                        <i data-lucide="image" class="w-3.5 h-3.5 text-amber-400"></i>
                        <span>Unduh PNG HD</span>
                    </span>
                    <span x-show="isExportingImage" class="inline-flex items-center gap-1.5" style="display: none;">
                        <svg class="animate-spin w-3.5 h-3.5 text-amber-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <span>Memproses...</span>
                    </span>
                </button>

                <!-- Cetak Sekarang -->
                <button type="button" 
                        onclick="window.print()" 
                        class="px-4 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black text-xs shadow-lg shadow-emerald-500/20 transition flex items-center gap-1.5 cursor-pointer">
                    <i data-lucide="printer" class="w-4 h-4"></i>
                    <span>Cetak Sekarang</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Notice Banners (Screen only) -->
    <div class="no-print max-w-7xl mx-auto mb-3 px-2">
        <div x-show="paperSize === 'poster'" class="p-3 bg-amber-500/15 border border-amber-500/30 rounded-xl text-amber-200 text-xs flex items-center gap-2.5 shadow-sm" style="display: none;">
            <i data-lucide="sparkles" class="w-4 h-4 text-amber-400 shrink-0"></i>
            <div class="leading-relaxed">
                <strong>Mode Poster / Ukuran Bebas Aktif:</strong> Batasan ukuran A4 telah dibuka! Pada dialog cetak printer (<kbd class="px-1 py-0.5 rounded bg-slate-800 text-amber-300 font-mono text-[10px]">Ctrl + P</kbd>), Anda bebas memilih ukuran kertas apa saja di printer Anda (A3, A2, A1, Plotter) atau aktifkan fitur <strong>Poster Printing / Multi-Sheet Tiling</strong> (cetak gabungan 4 atau 9 lembar A4 untuk ditempel jadi poster besar). Anda juga bisa mengklik <strong>Unduh SVG</strong> untuk mencetak banner/spanduk di percetakan.
            </div>
        </div>
        <div x-show="paperSize === 'a3'" class="p-3 bg-sky-500/15 border border-sky-500/30 rounded-xl text-sky-200 text-xs flex items-center gap-2.5 shadow-sm" style="display: none;">
            <i data-lucide="info" class="w-4 h-4 text-sky-400 shrink-0"></i>
            <div class="leading-relaxed">
                <strong>Format Kertas A3:</strong> Dokumen disesuaikan untuk kertas ukuran A3 (420 &times; 297 mm — 2x lipat A4). Pada dialog cetak printer, pastikan Anda memilih ukuran kertas <strong>A3</strong>. Sangat ideal untuk papan pengumuman GOR!
            </div>
        </div>
        <div x-show="paperSize === 'f4'" class="p-3 bg-indigo-500/15 border border-indigo-500/30 rounded-xl text-indigo-200 text-xs flex items-center gap-2.5 shadow-sm" style="display: none;">
            <i data-lucide="info" class="w-4 h-4 text-indigo-400 shrink-0"></i>
            <div class="leading-relaxed">
                <strong>Format Kertas F4 / Folio:</strong> Disesuaikan untuk kertas HVS panjang / Folio (330 &times; 215 mm). Pada dialog cetak, pastikan memilih ukuran kertas Folio / F4.
            </div>
        </div>
    </div>

    <!-- Printable Sheet Wrapper for horizontal screen scrolling -->
    <div class="sheet-scroll-wrapper w-full overflow-x-auto flex justify-center pb-8">
        <div id="printable-sheet" 
             class="print-sheet transition-all duration-300"
             :class="['size-' + paperSize, 'orientation-' + orientation]">

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
                <!-- Ketua Panitia -->
                <div class="flex flex-col items-center">
                    <div class="text-[8px] text-slate-500 leading-tight">Mengetahui,</div>
                    <div class="text-[8px] font-bold text-slate-900 uppercase leading-tight">Ketua Panitia Pelaksana</div>
                    <div class="py-0.5 flex flex-col items-center justify-center">
                        @php
                            $chairmanSignData = url('/cek-status?signer=ketua_panitia&nip=' . urlencode($appSettings['committee_chairman_nip'] ?? '197506172024211004'));
                        @endphp
                        <div class="p-0.5 bg-white border border-slate-300 rounded shadow-2xs inline-block">
                            {!! \App\Services\QrSignatureService::generateSvg($chairmanSignData, 32) !!}
                        </div>
                        <span class="text-[6.5px] font-mono text-slate-400">TTD Digital</span>
                    </div>
                    <div class="text-[8.5px] font-black text-slate-900 border-b border-slate-600 inline-block px-3 pb-0.5">
                        {{ $appSettings['committee_chairman_name'] ?? 'KHOIRUL ANAM, S.Pd' }}
                    </div>
                </div>

                <!-- Koordinator Lomba / PIC -->
                <div class="flex flex-col items-center">
                    <div class="text-[8px] text-slate-500 leading-tight">Diverifikasi Oleh,</div>
                    <div class="text-[8px] font-bold text-slate-900 uppercase leading-tight">Koordinator Lomba / PIC</div>
                    <div class="py-0.5 flex flex-col items-center justify-center">
                        @php
                            $picSignData = url('/cek-status?signer=pic&comp=' . urlencode($competition->code ?? 'BLT'));
                        @endphp
                        <div class="p-0.5 bg-white border border-slate-300 rounded shadow-2xs inline-block">
                            {!! \App\Services\QrSignatureService::generateSvg($picSignData, 32) !!}
                        </div>
                        <span class="text-[6.5px] font-mono text-slate-400">TTD Digital</span>
                    </div>
                    <div class="text-[8.5px] font-black text-slate-900 border-b border-slate-600 inline-block px-3 pb-0.5">
                        {{ $competition->pic?->name ?? (Auth::user()->name ?: 'PANITIA PELAKSANA') }}
                    </div>
                </div>

                <!-- Wasit Utama / Referee -->
                <div class="flex flex-col items-center">
                    <div class="text-[8px] text-slate-500 leading-tight">
                        {{ $appSettings['city'] ?? 'Blitar' }}, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}
                    </div>
                    <div class="text-[8px] font-bold text-slate-900 uppercase leading-tight">Wasit Utama / Referee</div>
                    <div class="py-0.5 flex flex-col items-center justify-center">
                        @php
                            $refSignData = url('/cek-status?signer=referee&comp=' . urlencode($competition->code ?? 'BLT'));
                        @endphp
                        <div class="p-0.5 bg-white border border-slate-300 rounded shadow-2xs inline-block">
                            {!! \App\Services\QrSignatureService::generateSvg($refSignData, 32) !!}
                        </div>
                        <span class="text-[6.5px] font-mono text-slate-400">TTD Digital</span>
                    </div>
                    <div class="text-[8.5px] font-black text-slate-900 border-b border-slate-600 inline-block px-3 pb-0.5">
                        ( WASIT UTAMA / REFEREE )
                    </div>
                </div>
            </div>

            <!-- Footer page info -->
            <div class="mt-0.5 text-[7px] text-slate-400 flex items-center justify-between font-mono">
                <span>Dokumen Bagan Resmi Dicetak Melalui Sistem Talenta • {{ date('d/m/Y H:i:s') }}</span>
                <span x-text="footerPageLabel">Halaman 1 / 1 (A4 Landscape)</span>
            </div>
        </div>

        </div> <!-- /printable-sheet -->
    </div> <!-- /sheet-scroll-wrapper -->

    <script>
        function initBracketPrintApp() {
            Alpine.data('bracketPrintApp', () => ({
                paperSize: (new URLSearchParams(window.location.search).get('size') || localStorage.getItem('talenta_bracket_print_size') || 'a4'),
                orientation: (new URLSearchParams(window.location.search).get('orientation') || localStorage.getItem('talenta_bracket_print_orientation') || 'landscape'),
                isExportingImage: false,

                init() {
                    this.updatePrintStyle();
                },

                setPaperSize(size) {
                    this.paperSize = size;
                    localStorage.setItem('talenta_bracket_print_size', size);
                    this.updatePrintStyle();
                    if (window.lucide) {
                        this.$nextTick(() => window.lucide.createIcons());
                    }
                },

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
                    if (!styleEl) return;

                    if (this.paperSize === 'poster') {
                        styleEl.innerHTML = `@page { size: auto; margin: 4mm 6mm; }`;
                    } else if (this.paperSize === 'a3') {
                        styleEl.innerHTML = `@page { size: A3 ${this.orientation}; margin: 4mm 6mm; }`;
                    } else if (this.paperSize === 'f4') {
                        if (this.orientation === 'portrait') {
                            styleEl.innerHTML = `@page { size: 215mm 330mm; margin: 5mm 6mm; }`;
                        } else {
                            styleEl.innerHTML = `@page { size: 330mm 215mm; margin: 4mm 6mm; }`;
                        }
                    } else {
                        styleEl.innerHTML = `@page { size: A4 ${this.orientation}; margin: 4mm 6mm; }`;
                    }
                },

                get footerPageLabel() {
                    const sizeName = this.paperSize.toUpperCase();
                    const orientName = this.orientation === 'portrait' ? 'Portrait' : 'Landscape';
                    return `Halaman 1 / 1 (${sizeName} ${orientName})`;
                },

                downloadSvg() {
                    const svgEl = document.querySelector('.bracket-svg-container svg');
                    if (!svgEl) {
                        alert('Bagan SVG tidak ditemukan.');
                        return;
                    }
                    const serializer = new XMLSerializer();
                    let source = serializer.serializeToString(svgEl);
                    if (!source.match(/^<svg[^>]+xmlns="http:\/\/www\.w3\.org\/2000\/svg"/)) {
                        source = source.replace(/^<svg/, '<svg xmlns="http://www.w3.org/2000/svg"');
                    }
                    const blob = new Blob([source], { type: 'image/svg+xml;charset=utf-8' });
                    const url = URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = `Bagan_{{ Str::slug($competition->name . '_' . ($activePool['title'] ?? 'Resmi')) }}.svg`;
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    URL.revokeObjectURL(url);
                },

                async downloadPng() {
                    const svgEl = document.querySelector('.bracket-svg-container svg');
                    if (!svgEl) {
                        alert('Bagan SVG tidak ditemukan.');
                        return;
                    }
                    this.isExportingImage = true;
                    try {
                        const serializer = new XMLSerializer();
                        let source = serializer.serializeToString(svgEl);
                        if (!source.match(/^<svg[^>]+xmlns="http:\/\/www\.w3\.org\/2000\/svg"/)) {
                            source = source.replace(/^<svg/, '<svg xmlns="http://www.w3.org/2000/svg"');
                        }
                        const img = new Image();
                        const svgBlob = new Blob([source], { type: 'image/svg+xml;charset=utf-8' });
                        const url = URL.createObjectURL(svgBlob);

                        img.onload = () => {
                            const canvas = document.createElement('canvas');
                            const targetWidth = 3600;
                            const viewBox = svgEl.viewBox ? svgEl.viewBox.baseVal : null;
                            const aspect = (viewBox && viewBox.height && viewBox.width) ? (viewBox.height / viewBox.width) : 0.6;
                            canvas.width = targetWidth;
                            canvas.height = Math.round(targetWidth * aspect);

                            const ctx = canvas.getContext('2d');
                            ctx.fillStyle = '#ffffff';
                            ctx.fillRect(0, 0, canvas.width, canvas.height);
                            ctx.drawImage(img, 0, 0, canvas.width, canvas.height);

                            URL.revokeObjectURL(url);

                            canvas.toBlob((pngBlob) => {
                                this.isExportingImage = false;
                                if (!pngBlob) return;
                                const pngUrl = URL.createObjectURL(pngBlob);
                                const a = document.createElement('a');
                                a.href = pngUrl;
                                a.download = `Bagan_Poster_HD_{{ Str::slug($competition->name . '_' . ($activePool['title'] ?? 'Resmi')) }}.png`;
                                document.body.appendChild(a);
                                a.click();
                                document.body.removeChild(a);
                                URL.revokeObjectURL(pngUrl);
                            }, 'image/png');
                        };
                        img.onerror = () => {
                            this.isExportingImage = false;
                            alert('Gagal mengonversi gambar.');
                        };
                        img.src = url;
                    } catch (e) {
                        this.isExportingImage = false;
                        console.error(e);
                        alert('Terjadi kesalahan saat mengonversi gambar.');
                    }
                }
            }));
        }

        if (window.Alpine) {
            initBracketPrintApp();
        } else {
            document.addEventListener('alpine:init', initBracketPrintApp);
        }

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
