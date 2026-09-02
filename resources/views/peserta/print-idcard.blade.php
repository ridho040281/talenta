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
        <link rel="apple-touch-icon" href="{{ asset('storage/' . $appSettings['favicon']) }}">
    @else
        <link rel="icon" href="{{ asset('favicon.ico') }}">
    @endif

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800;900&family=Space+Mono:wght@700&display=swap" rel="stylesheet">
    
    <!-- Vite Local Tailwind CSS & JS Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Lucide Icons -->
    <script src="https://cdn.jsdelivr.net/npm/lucide@0.344.0/dist/umd/lucide.min.js"></script>

    <style>
        @page {
            size: A4 portrait;
            margin: 1.5cm;
        }
        @media print {
            body { background: white !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .no-print { display: none !important; }
            .id-card-wrapper { box-shadow: none !important; margin: 0 auto; page-break-inside: avoid; }
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen py-10 flex flex-col items-center justify-center p-4">

    <!-- Print Action Bar (Hidden on Print) -->
    <div class="no-print mb-8 max-w-md w-full flex items-center justify-between gap-3 bg-white p-4 rounded-2xl shadow-sm border border-slate-200">
        <a href="{{ route('peserta.dashboard') }}" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-xs font-bold text-slate-700 transition">
            Kembali
        </a>
        <button onclick="window.print()" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-500/20 transition">
            <i data-lucide="printer" class="w-4 h-4"></i>
            <span>Cetak Kartu Peserta</span>
        </button>
    </div>

    <!-- ID Card / Badge Component -->
    <div class="id-card-wrapper w-full max-w-[420px] bg-white rounded-3xl overflow-hidden border-2 border-slate-300 shadow-2xl relative text-slate-800">
        
        <!-- Header Ribbon -->
        <div class="bg-gradient-to-r from-emerald-800 via-emerald-700 to-teal-800 p-6 text-white text-center relative overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full blur-xl pointer-events-none"></div>
            
            <div class="flex items-center justify-center gap-3 mb-2">
                @if(!empty($appSettings['app_logo']))
                    <img src="{{ asset('storage/' . $appSettings['app_logo']) }}" alt="Logo" class="h-10 w-auto max-w-[120px] object-contain">
                @else
                    <div class="w-9 h-9 rounded-xl bg-white/20 backdrop-blur-md flex items-center justify-center text-amber-300 font-bold border border-white/30">
                        <i data-lucide="trophy" class="w-5 h-5"></i>
                    </div>
                    <div class="text-left">
                        <span class="block text-sm font-black tracking-wider">{{ $appSettings['app_name'] ?? 'TALENTA 2026' }}</span>
                        <span class="block text-[10px] font-bold text-emerald-200 tracking-widest uppercase">{{ $appSettings['institution_name'] ?? 'MTsN 1 BLITAR' }}</span>
                    </div>
                @endif
            </div>
            
            <h1 class="text-xs font-bold uppercase tracking-widest text-amber-300 pt-1">KARTU TANDA PESERTA RESMI</h1>
        </div>

        <!-- Participant Big Number (Nomor Dada) -->
        <div class="bg-slate-900 py-3 text-center border-b border-slate-800">
            <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400 block mb-0.5">NOMOR PESERTA RESMI</span>
            <span class="text-3xl font-mono font-black text-amber-400 tracking-wider">
                {{ $registration->participant_number ?? 'MIPA-01' }}
            </span>
        </div>

        <!-- Body Details -->
        <div class="p-6 space-y-5">
            
            <!-- Participant / Team Name -->
            <div class="text-center space-y-1">
                <h2 class="text-xl font-extrabold text-slate-900 leading-tight">
                    {{ $registration->display_name }}
                </h2>
                <p class="text-xs font-bold text-emerald-700 bg-emerald-50 px-3 py-1 rounded-full inline-block border border-emerald-200">
                    {{ $registration->institution_name }}
                </p>
            </div>

            <!-- Competition & Draw Number Grid -->
            <div class="grid grid-cols-2 gap-3 text-xs">
                <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200 text-center">
                    <span class="text-[10px] font-bold text-slate-400 uppercase block">Cabang Lomba</span>
                    <span class="font-extrabold text-slate-800 text-sm mt-0.5 block">{{ $registration->competition->name }}</span>
                </div>
                <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200 text-center">
                    <span class="text-[10px] font-bold text-slate-400 uppercase block">Nomor Tampil</span>
                    <span class="font-mono font-black text-amber-600 text-base mt-0.5 block">
                        {{ $registration->draw_number ? '#' . $registration->draw_number : 'Menunggu Spin' }}
                    </span>
                </div>
            </div>

            <!-- Verification Stamp & QR Area -->
            <div class="pt-4 border-t border-slate-200/80 flex items-center justify-between gap-4">
                
                <!-- QR Code Box Placeholder -->
                <div class="w-20 h-20 bg-slate-50 border-2 border-slate-300 rounded-2xl flex flex-col items-center justify-center p-1 text-center shrink-0">
                    <i data-lucide="qr-code" class="w-12 h-12 text-slate-800"></i>
                    <span class="text-[8px] font-mono font-bold text-slate-500 truncate w-full">{{ substr($registration->registration_code, -6) }}</span>
                </div>

                <!-- Official Panitia Validation Sign -->
                <div class="text-right space-y-1">
                    <span class="text-[10px] text-slate-400 block">Diverifikasi Sah Oleh:</span>
                    <span class="text-xs font-black text-slate-900 block">Panitia TALENTA 2026</span>
                    <span class="inline-block px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 text-[9px] font-bold uppercase tracking-wider">
                        STATUS: VERIFIED
                    </span>
                    <span class="text-[9px] text-slate-400 block">{{ $registration->verified_at ? $registration->verified_at->format('d/m/Y H:i') : date('d/m/Y') }}</span>
                </div>

            </div>

            <!-- Notes footer -->
            <div class="bg-amber-50 rounded-xl p-2.5 text-center text-[10px] text-amber-800 border border-amber-200/60 font-medium">
                ⚠️ Harap membawa & mengenakan kartu ini saat registrasi ulang di venue MTsN 1 Blitar.
            </div>

        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
        });
    </script>
</body>
</html>
