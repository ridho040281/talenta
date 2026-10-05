<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CETAK MASSAL KARTU PESERTA — {{ $competition->name }}</title>
    
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
    
    <!-- Lucide Icons (Self-hosted Local) -->
    <script src="{{ asset('vendor/lucide/lucide.min.js') }}"></script>

    <!-- Alpine.js -->
    <script defer src="{{ asset('vendor/alpine/alpine.min.js') }}"></script>

    <!-- Dynamic Paged Media CSS -->
    <style id="dynamic-page-style">
        @page {
            size: A4 landscape;
            margin: 4mm;
        }
    </style>

    <style>
        @media print {
            body { 
                background: white !important; 
                -webkit-print-color-adjust: exact !important; 
                print-color-adjust: exact !important; 
                padding: 0 !important;
                margin: 0 !important;
            }
            .no-print { display: none !important; }

            /* Lembar A4 Portrait (Grid 4 & Grid 6) */
            .a4-print-page {
                box-shadow: none !important;
                margin: 0 auto !important;
                page-break-after: always !important;
                break-after: page !important;
                width: 194mm !important;
                min-height: 280mm !important;
            }

            /* Lembar A4 Landscape (Grid 8 - Super Hemat) */
            .a4-landscape-page {
                box-shadow: none !important;
                margin: 0 auto !important;
                page-break-after: always !important;
                break-after: page !important;
                width: 288mm !important;
                min-height: 200mm !important;
                max-height: 202mm !important;
                overflow: hidden !important;
            }

            .a4-print-page:last-child,
            .a4-landscape-page:last-child {
                page-break-after: auto !important;
                break-after: auto !important;
            }
        }

        /* Garis Tanda Potong */
        .card-cut-mark {
            border: 1px dashed #cbd5e1;
            border-radius: 4px;
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            box-sizing: border-box;
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen py-6 px-4 flex flex-col items-center"
      x-data="{ 
          printLayout: 'grid8',
          setLayout(layout) {
              this.printLayout = layout;
              const styleTag = document.getElementById('dynamic-page-style');
              if (layout === 'grid8') {
                  styleTag.innerHTML = '@page { size: A4 landscape; margin: 4mm; }';
              } else {
                  styleTag.innerHTML = '@page { size: A4 portrait; margin: 6mm; }';
              }
              this.$nextTick(() => {
                  if (window.lucide) lucide.createIcons();
              });
          }
      }">

    @php
        $templateName = $competition->effective_card_template;
        $viewPath = view()->exists('documents.idcards.' . $templateName) 
            ? 'documents.idcards.' . $templateName 
            : 'documents.idcards.universal';

        // Flatten all members with their parent registration
        $allItems = collect();
        foreach ($registrations as $reg) {
            if ($reg->members && $reg->members->isNotEmpty()) {
                foreach ($reg->members as $m) {
                    $allItems->push([
                        'registration' => $reg,
                        'member' => $m,
                    ]);
                }
            } else {
                $mockMember = new \App\Models\RegistrationMember([
                    'full_name' => $reg->user->name ?? $reg->team_name ?? 'Peserta Lomba',
                    'school_name' => $reg->institution_name,
                    'photo' => null,
                ]);
                $allItems->push([
                    'registration' => $reg,
                    'member' => $mockMember,
                ]);
            }
        }

        $chunks8 = $allItems->chunk(8);
        $chunks6 = $allItems->chunk(6);
        $chunks4 = $allItems->chunk(4);
    @endphp

    <!-- Top Action Bar (Hidden on Print) -->
    <div class="no-print mb-6 max-w-5xl w-full flex flex-col md:flex-row items-center justify-between gap-4 bg-white p-4 rounded-2xl shadow-md border border-slate-200">
        <div class="flex items-center gap-3">
            <a href="javascript:history.back()" class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-xs font-bold text-slate-700 transition flex items-center gap-1.5 cursor-pointer">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Kembali</span>
            </a>
            <div>
                <h1 class="text-xs sm:text-sm font-black text-slate-900 leading-tight">
                    Cetak Massal Kartu Peserta — {{ $competition->name }}
                </h1>
                <p class="text-[11px] text-slate-500">
                    Total {{ $allItems->count() }} Kartu Peserta • Format: <strong class="text-emerald-700 font-bold uppercase">{{ $templateName }}</strong> • Ukuran: <span class="bg-emerald-50 text-emerald-800 font-bold px-1.5 py-0.5 rounded border border-emerald-200">B2 (10,5 x 6,5 cm)</span>
                </p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2.5 w-full md:w-auto justify-end">
            <!-- Layout Switcher (3 Pilihan) -->
            <div class="bg-slate-100 p-1 rounded-xl flex flex-wrap items-center gap-1 border border-slate-200 text-xs font-bold">
                <!-- Tombol Grid 8 (A4 Landscape) -->
                <button type="button" @click="setLayout('grid8')" :class="printLayout === 'grid8' ? 'bg-emerald-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900'" class="px-2.5 py-1.5 rounded-lg transition cursor-pointer flex items-center gap-1">
                    <i data-lucide="layout-grid" class="w-3.5 h-3.5"></i>
                    <span>Grid 8 (A4 Landscape)</span>
                    <span class="text-[9px] bg-amber-400 text-amber-950 font-black px-1 rounded">Super Hemat</span>
                </button>

                <!-- Tombol Grid 6 (A4 Portrait) -->
                <button type="button" @click="setLayout('grid6')" :class="printLayout === 'grid6' ? 'bg-white text-emerald-700 shadow-xs' : 'text-slate-600 hover:text-slate-900'" class="px-2.5 py-1.5 rounded-lg transition cursor-pointer flex items-center gap-1">
                    <span>Grid 6 (A4)</span>
                </button>

                <!-- Tombol Grid 4 (Standar) -->
                <button type="button" @click="setLayout('grid4')" :class="printLayout === 'grid4' ? 'bg-white text-emerald-700 shadow-xs' : 'text-slate-600 hover:text-slate-900'" class="px-2.5 py-1.5 rounded-lg transition cursor-pointer flex items-center gap-1">
                    <span>Grid 4 (Standar)</span>
                </button>
            </div>

            <!-- Print Trigger Button -->
            <button onclick="window.print()" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs shadow-md shadow-emerald-500/20 transition cursor-pointer">
                <i data-lucide="printer" class="w-4 h-4"></i>
                <span>Cetak Semua (PDF/Print)</span>
            </button>
        </div>
    </div>

    <!-- ==================== MODE 1: GRID 8 (A4 LANDSCAPE - 8 KARTU SUSUN 4x2) ==================== -->
    <div x-show="printLayout === 'grid8'" class="w-full flex flex-col items-center gap-8">
        @forelse($chunks8 as $pageIndex => $chunkItems)
            <div class="a4-landscape-page bg-white p-3 shadow-xl border border-slate-300 rounded-2xl flex flex-col justify-between" style="width: 288mm; min-height: 200mm; max-height: 202mm; box-sizing: border-box;">
                
                <!-- Notice on top of sheet (Hidden in print) -->
                <div class="no-print pb-1 mb-1 border-b border-slate-200 flex items-center justify-between text-[11px] text-slate-500">
                    <span>Lembar {{ $pageIndex + 1 }} dari {{ $chunks8->count() }} (A4 Landscape • 8 Kartu per Lembar)</span>
                    <span class="text-emerald-700 font-bold">✂️ Susunan 4 Kolom x 2 Baris • Ukuran Pas Plastik B2 (10,5 x 6,5 cm)</span>
                </div>

                <!-- 4x2 Grid of Cards -->
                <div class="flex-1 flex items-center justify-center overflow-hidden">
                    <div class="grid grid-cols-4 gap-2" style="transform: scale(0.93); transform-origin: top center;">
                        @foreach($chunkItems as $item)
                            <div class="card-cut-mark flex items-center justify-center p-0.5">
                                @include($viewPath, [
                                    'registration' => $item['registration'],
                                    'competition' => $competition,
                                    'member' => $item['member']
                                ])
                            </div>
                        @endforeach

                        {{-- Empty slots placeholder to maintain 4x2 grid alignment --}}
                        @for($i = $chunkItems->count(); $i < 8; $i++)
                            <div class="card-cut-mark border-dashed border-slate-200 flex flex-col items-center justify-center text-slate-300 text-xs text-center p-2" style="width: 65mm; height: 105mm; box-sizing: border-box;">
                                <i data-lucide="scissors" class="w-5 h-5 mb-1 opacity-40"></i>
                                <span class="text-[9px] uppercase font-bold tracking-wider">Slot Kosong</span>
                            </div>
                        @endfor
                    </div>
                </div>

                <!-- Footer Sheet Info -->
                <div class="pt-1 mt-1 border-t border-slate-200 text-center text-[9px] text-slate-400 font-mono flex items-center justify-between">
                    <span>TALENTA MTsN 1 Blitar — Lembar Cetak ID Card (A4 Landscape 8 Kartu)</span>
                    <span>Halaman {{ $pageIndex + 1 }} / {{ $chunks8->count() }}</span>
                </div>
            </div>
        @empty
            <div class="bg-white p-12 rounded-3xl border border-slate-200 text-center max-w-md w-full shadow-lg">
                <i data-lucide="alert-circle" class="w-12 h-12 text-amber-500 mx-auto mb-3"></i>
                <h3 class="text-base font-black text-slate-900">Belum Ada Peserta Terverifikasi</h3>
                <p class="text-xs text-slate-500 mt-1">Hanya pendaftaran yang berstatus 'verified' yang dicetak kartunya.</p>
            </div>
        @endforelse
    </div>

    <!-- ==================== MODE 2: GRID 6 (A4 PORTRAIT - 6 KARTU SUSUN 3x2) ==================== -->
    <div x-show="printLayout === 'grid6'" x-cloak class="w-full flex flex-col items-center gap-8">
        @forelse($chunks6 as $pageIndex => $chunkItems)
            <div class="a4-print-page bg-white p-3 shadow-xl border border-slate-300 rounded-2xl flex flex-col justify-between" style="width: 194mm; min-height: 280mm; box-sizing: border-box;">
                
                <!-- Notice on top of sheet (Hidden in print) -->
                <div class="no-print pb-2 mb-2 border-b border-slate-200 flex items-center justify-between text-[11px] text-slate-500">
                    <span>Lembar {{ $pageIndex + 1 }} dari {{ $chunks6->count() }} (A4 Portrait • 6 Kartu per Lembar)</span>
                    <span class="text-emerald-700 font-bold">✂️ Susunan 3 Kolom x 2 Baris • Standar B2 (10,5 x 6,5 cm)</span>
                </div>

                <!-- 3x2 Grid of Cards -->
                <div class="flex-1 flex items-center justify-center overflow-hidden">
                    <div class="grid grid-cols-3 gap-2" style="transform: scale(0.97); transform-origin: top center;">
                        @foreach($chunkItems as $item)
                            <div class="card-cut-mark flex items-center justify-center p-0.5">
                                @include($viewPath, [
                                    'registration' => $item['registration'],
                                    'competition' => $competition,
                                    'member' => $item['member']
                                ])
                            </div>
                        @endforeach

                        {{-- Empty slots placeholder to maintain 3x2 grid alignment --}}
                        @for($i = $chunkItems->count(); $i < 6; $i++)
                            <div class="card-cut-mark border-dashed border-slate-200 flex flex-col items-center justify-center text-slate-300 text-xs text-center p-2" style="width: 65mm; height: 105mm; box-sizing: border-box;">
                                <i data-lucide="scissors" class="w-5 h-5 mb-1 opacity-40"></i>
                                <span class="text-[9px] uppercase font-bold tracking-wider">Slot Kosong</span>
                            </div>
                        @endfor
                    </div>
                </div>

                <!-- Footer Sheet Info -->
                <div class="pt-2 mt-2 border-t border-slate-200 text-center text-[9px] text-slate-400 font-mono flex items-center justify-between">
                    <span>TALENTA MTsN 1 Blitar — Lembar Cetak ID Card (A4 Portrait 6 Kartu)</span>
                    <span>Halaman {{ $pageIndex + 1 }} / {{ $chunks6->count() }}</span>
                </div>
            </div>
        @empty
            <div class="bg-white p-12 rounded-3xl border border-slate-200 text-center max-w-md w-full shadow-lg">
                <i data-lucide="alert-circle" class="w-12 h-12 text-amber-500 mx-auto mb-3"></i>
                <h3 class="text-base font-black text-slate-900">Belum Ada Peserta Terverifikasi</h3>
                <p class="text-xs text-slate-500 mt-1">Hanya pendaftaran yang berstatus 'verified' yang dicetak kartunya.</p>
            </div>
        @endforelse
    </div>

    <!-- ==================== MODE 3: GRID 4 (A4 PORTRAIT - 4 KARTU STANDAR 2x2) ==================== -->
    <div x-show="printLayout === 'grid4'" x-cloak class="w-full flex flex-col items-center gap-8">
        @forelse($chunks4 as $pageIndex => $chunkItems)
            <div class="a4-print-page bg-white p-4 shadow-xl border border-slate-300 rounded-2xl flex flex-col justify-between" style="width: 194mm; min-height: 280mm; box-sizing: border-box;">
                
                <!-- Notice on top of sheet (Hidden in print) -->
                <div class="no-print pb-2 mb-2 border-b border-slate-200 flex items-center justify-between text-[11px] text-slate-500">
                    <span>Lembar {{ $pageIndex + 1 }} dari {{ $chunks4->count() }} (A4 Portrait • 4 Kartu per Lembar)</span>
                    <span class="text-emerald-700 font-bold">✂️ Standar Plastik B2 (10,5 x 6,5 cm) — Potong mengikuti garis batas luar kartu</span>
                </div>

                <!-- 2x2 Grid of Cards -->
                <div class="grid grid-cols-2 gap-4 flex-1">
                    @foreach($chunkItems as $item)
                        <div class="card-cut-mark flex items-center justify-center p-1">
                            @include($viewPath, [
                                'registration' => $item['registration'],
                                'competition' => $competition,
                                'member' => $item['member']
                            ])
                        </div>
                    @endforeach

                    {{-- Empty slots placeholder to maintain grid alignment --}}
                    @for($i = $chunkItems->count(); $i < 4; $i++)
                        <div class="card-cut-mark border-dashed border-slate-200 flex flex-col items-center justify-center text-slate-300 text-xs text-center p-4">
                            <i data-lucide="scissors" class="w-6 h-6 mb-1 opacity-40"></i>
                            <span class="text-[10px] uppercase font-bold tracking-wider">Slot Kosong</span>
                        </div>
                    @endfor
                </div>

                <!-- Footer Sheet Info -->
                <div class="pt-2 mt-2 border-t border-slate-200 text-center text-[9px] text-slate-400 font-mono flex items-center justify-between">
                    <span>TALENTA MTsN 1 Blitar — Lembar Cetak ID Card Peserta</span>
                    <span>Halaman {{ $pageIndex + 1 }} / {{ $chunks4->count() }}</span>
                </div>
            </div>
        @empty
            <div class="bg-white p-12 rounded-3xl border border-slate-200 text-center max-w-md w-full shadow-lg">
                <i data-lucide="alert-circle" class="w-12 h-12 text-amber-500 mx-auto mb-3"></i>
                <h3 class="text-base font-black text-slate-900">Belum Ada Peserta Terverifikasi</h3>
                <p class="text-xs text-slate-500 mt-1">Hanya pendaftaran yang berstatus 'verified' yang dicetak kartunya.</p>
            </div>
        @endforelse
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.lucide) {
                lucide.createIcons();
            }
        });
    </script>
</body>
</html>
