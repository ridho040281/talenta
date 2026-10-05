<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KARTU TANDA PESERTA - {{ $registration->participant_number }} ({{ $registration->display_name }})</title>
    
    <!-- Favicon -->
    @if(!empty($appSettings['favicon']))
        <link rel="icon" type="image/png" href="{{ asset('storage/' . $appSettings['favicon']) }}">
        <link rel="shortcut icon" href="{{ asset('storage/' . $appSettings['favicon']) }}">
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

    <style>
        @page {
            size: A4 portrait;
            margin: 8mm;
        }
        @media print {
            body { 
                background: white !important; 
                -webkit-print-color-adjust: exact !important; 
                print-color-adjust: exact !important; 
                padding: 0 !important;
                margin: 0 !important;
            }
            .no-print { display: none !important; }
            .a4-print-page {
                box-shadow: none !important;
                margin: 0 auto !important;
                page-break-after: always !important;
                break-after: page !important;
                width: 194mm !important;
                min-height: 280mm !important;
            }
            .a4-print-page:last-child {
                page-break-after: auto !important;
                break-after: auto !important;
            }
        }

        /* Dashed Cut Marks for A4 sheet */
        .card-cut-mark {
            border: 1px dashed #cbd5e1;
            padding: 4mm;
            border-radius: 4px;
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen py-6 px-4 flex flex-col items-center" x-data="{ printLayout: 'grid' }">

    @php
        $competition = $registration->competition;
        $members = $registration->members;
        if ($members->isEmpty()) {
            // Fallback: create mock member from registration
            $mockMember = new \App\Models\RegistrationMember([
                'full_name' => $registration->user->name ?? $registration->team_name ?? 'Peserta Lomba',
                'school_name' => $registration->institution_name,
                'photo' => null,
            ]);
            $members = collect([$mockMember]);
        }

        $templateName = $competition->effective_card_template;
        $viewPath = view()->exists('documents.idcards.' . $templateName) 
            ? 'documents.idcards.' . $templateName 
            : 'documents.idcards.universal';
    @endphp

    <!-- Top Action Bar (Hidden on Print) -->
    <div class="no-print mb-6 max-w-4xl w-full flex flex-col sm:flex-row items-center justify-between gap-4 bg-white p-4 rounded-2xl shadow-md border border-slate-200">
        <div class="flex items-center gap-3">
            <a href="javascript:history.back()" class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-xs font-bold text-slate-700 transition flex items-center gap-1.5 cursor-pointer">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Kembali</span>
            </a>
            <div>
                <h1 class="text-xs sm:text-sm font-black text-slate-900 leading-tight">
                    Cetak Kartu Peserta — {{ $competition->name }}
                </h1>
                <p class="text-[11px] text-slate-500">
                    Total {{ $members->count() }} Anggota Regu/Peserta • Format: <strong class="text-emerald-700 font-bold uppercase">{{ $templateName }}</strong> • Ukuran: <span class="bg-emerald-50 text-emerald-800 font-bold px-1.5 py-0.5 rounded border border-emerald-200">B2 (10,5 x 6,5 cm)</span>
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2.5 w-full sm:w-auto justify-end">
            <!-- Layout Switcher -->
            <div class="bg-slate-100 p-1 rounded-xl flex items-center gap-1 border border-slate-200 text-xs font-bold">
                <button type="button" @click="printLayout = 'grid'" :class="printLayout === 'grid' ? 'bg-white text-emerald-700 shadow-xs' : 'text-slate-600 hover:text-slate-900'" class="px-3 py-1.5 rounded-lg transition cursor-pointer">
                    Grid A4 (4 Kartu B2)
                </button>
                <button type="button" @click="printLayout = 'single'" :class="printLayout === 'single' ? 'bg-white text-emerald-700 shadow-xs' : 'text-slate-600 hover:text-slate-900'" class="px-3 py-1.5 rounded-lg transition cursor-pointer">
                    Satuan Lanyard
                </button>
            </div>

            <!-- Print Trigger Button -->
            <button onclick="window.print()" class="inline-flex items-center gap-2 px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs shadow-md shadow-emerald-500/20 transition cursor-pointer">
                <i data-lucide="printer" class="w-4 h-4"></i>
                <span>Cetak Sekarang</span>
            </button>
        </div>
    </div>

    <!-- ==================== MODE 1: GRID A4 (4 Kartu per Lembar A4) ==================== -->
    <div x-show="printLayout === 'grid'" class="w-full flex flex-col items-center gap-8">
        @php
            $chunks = $members->chunk(4);
        @endphp

        @foreach($chunks as $pageIndex => $chunkMembers)
            <div class="a4-print-page bg-white p-4 shadow-xl border border-slate-300 rounded-2xl flex flex-col justify-between" style="width: 194mm; min-height: 280mm; box-sizing: border-box;">
                
                <!-- Notice on top of sheet (Hidden in print) -->
                <div class="no-print pb-2 mb-2 border-b border-slate-200 flex items-center justify-between text-[11px] text-slate-500">
                    <span>Lembar {{ $pageIndex + 1 }} dari {{ $chunks->count() }} (A4 Portrait)</span>
                    <span class="text-emerald-700 font-bold">✂️ Standar Plastik B2 (10,5 x 6,5 cm) — Potong mengikuti garis batas luar kartu</span>
                </div>

                <!-- 2x2 Grid of Cards -->
                <div class="grid grid-cols-2 gap-4 flex-1">
                    @foreach($chunkMembers as $memberItem)
                        <div class="card-cut-mark flex items-center justify-center">
                            @include($viewPath, [
                                'registration' => $registration,
                                'competition' => $competition,
                                'member' => $memberItem
                            ])
                        </div>
                    @endforeach

                    {{-- Empty slots placeholder to maintain grid alignment --}}
                    @for($i = $chunkMembers->count(); $i < 4; $i++)
                        <div class="card-cut-mark border-dashed border-slate-200 flex flex-col items-center justify-center text-slate-300 text-xs text-center p-4">
                            <i data-lucide="scissors" class="w-6 h-6 mb-1 opacity-40"></i>
                            <span class="text-[10px] uppercase font-bold tracking-wider">Slot Kosong</span>
                        </div>
                    @endfor
                </div>

                <!-- Footer Sheet Info -->
                <div class="pt-2 mt-2 border-t border-slate-200 text-center text-[9px] text-slate-400 font-mono flex items-center justify-between">
                    <span>TALENTA MTsN 1 Blitar — Lembar Cetak ID Card Peserta</span>
                    <span>Halaman {{ $pageIndex + 1 }} / {{ $chunks->count() }}</span>
                </div>
            </div>
        @endforeach
    </div>

    <!-- ==================== MODE 2: SATUAN LANYARD (Single Badges) ==================== -->
    <div x-show="printLayout === 'single'" x-cloak class="w-full flex flex-col items-center gap-6">
        @foreach($members as $mIdx => $memberItem)
            <div class="id-card-single-wrapper bg-white p-3 rounded-2xl shadow-xl border border-slate-300 flex flex-col items-center">
                <div class="no-print pb-2 mb-2 w-full text-center text-xs text-slate-500 border-b border-slate-200 font-bold">
                    Kartu Anggota #{{ $mIdx + 1 }} — {{ $memberItem->full_name }}
                </div>
                @include($viewPath, [
                    'registration' => $registration,
                    'competition' => $competition,
                    'member' => $memberItem
                ])
            </div>
        @endforeach
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
