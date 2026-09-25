<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $publication['standby_title'] ?? 'Bagan Pertandingan Sedang Disiapkan' }} - {{ $competition->name }} | {{ $appSettings['event_name'] ?? ($appSettings['app_name'] ?? 'TALENTA') }}</title>
    
    @if(!empty($appSettings['favicon']))
        <link rel="icon" type="image/png" href="{{ asset('storage/' . $appSettings['favicon']) }}">
        <link rel="shortcut icon" href="{{ asset('storage/' . $appSettings['favicon']) }}">
    @else
        <link rel="icon" href="{{ asset('favicon.ico') }}">
    @endif

    <link rel="stylesheet" href="{{ asset('vendor/fonts/fonts.css') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="{{ asset('vendor/lucide/lucide.min.js') }}"></script>
    <script defer src="{{ asset('vendor/alpine/alpine.min.js') }}"></script>

    <style>
        [x-cloak] { display: none !important; }
        @keyframes pulseGlow {
            0%, 100% { opacity: 0.4; transform: scale(1); }
            50% { opacity: 0.8; transform: scale(1.05); }
        }
        .pulse-ambient {
            animation: pulseGlow 4s ease-in-out infinite;
        }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 font-sans antialiased min-h-screen flex flex-col selection:bg-amber-500 selection:text-slate-950 relative overflow-x-hidden"
      x-data="standbyApp()">

    <div class="fixed top-1/4 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[550px] sm:w-[750px] h-[350px] bg-gradient-to-tr from-amber-500/10 via-rose-500/15 to-indigo-600/10 rounded-full blur-[130px] pointer-events-none pulse-ambient"></div>
    <div class="fixed -bottom-24 right-1/4 w-[450px] h-[300px] bg-indigo-500/10 rounded-full blur-[100px] pointer-events-none"></div>

    <header class="bg-slate-900/80 backdrop-blur-md border-b border-slate-800/80 px-6 py-4 flex flex-col sm:flex-row items-center justify-between gap-4 sticky top-0 z-50">
        <div class="flex items-center gap-3 w-full sm:w-auto justify-between sm:justify-start">
            <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                @if(!empty($appSettings['app_logo']))
                    <img src="{{ asset('storage/' . $appSettings['app_logo']) }}" alt="{{ $appSettings['app_name'] ?? 'Logo' }}" class="h-10 w-auto max-w-[140px] object-contain group-hover:scale-105 transition">
                @else
                    <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-amber-500 to-rose-600 text-slate-950 flex items-center justify-center font-black group-hover:scale-105 transition shadow-lg shadow-amber-500/20">
                        <i data-lucide="shield-alert" class="w-5 h-5 text-white"></i>
                    </div>
                @endif
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-base sm:text-lg font-black text-white tracking-tight">LAYAR INFORMASI BAGAN TV</h1>
                    <span class="px-2 py-0.5 rounded-full bg-rose-500/20 text-rose-300 border border-rose-500/30 text-[10px] font-black uppercase font-mono flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-rose-500 animate-ping"></span>
                        <span>STATUS: STANDBY</span>
                    </span>
                </div>
                <p class="text-xs text-amber-400 font-bold">
                    {{ $competition->name }} <span class="text-slate-500">•</span> <span class="text-slate-400 font-normal">{{ $appSettings['event_name'] ?? 'Festival & Kejuaraan' }}</span>
                </p>
            </div>
        </div>

        <div class="flex items-center gap-3 w-full sm:w-auto justify-end flex-wrap">
            <button type="button" 
                    onclick="toggleFullScreen()" 
                    class="px-3.5 py-1.5 rounded-xl bg-slate-800/90 hover:bg-slate-700 text-slate-300 hover:text-white border border-slate-700 text-xs font-bold flex items-center gap-2 transition cursor-pointer">
                <i data-lucide="maximize" class="w-3.5 h-3.5"></i>
                <span class="hidden sm:inline">Layar Penuh TV</span>
            </button>

            <a href="{{ route('live.scoreboard', $competition->slug) }}" class="px-3.5 py-1.5 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 font-black text-xs shadow-md shadow-amber-500/20 transition flex items-center gap-1.5">
                <i data-lucide="activity" class="w-3.5 h-3.5"></i>
                <span>Papan Skor</span>
            </a>
        </div>
    </header>

    <main class="flex-1 flex items-center justify-center p-4 sm:p-8 relative z-10">
        <div class="max-w-2xl w-full bg-slate-900/90 border border-amber-500/40 rounded-3xl p-6 sm:p-10 shadow-2xl backdrop-blur-xl text-center space-y-6 animate-in fade-in zoom-in-95 duration-300">
            
            <div class="flex flex-col items-center justify-center space-y-3">
                <div class="relative">
                    <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-3xl bg-gradient-to-tr from-amber-500/20 via-rose-500/20 to-indigo-500/20 border border-amber-500/40 flex items-center justify-center text-amber-400 shadow-2xl shadow-amber-500/20">
                        <i data-lucide="lock" class="w-10 h-10 sm:w-12 sm:h-12 text-amber-400 animate-pulse"></i>
                    </div>
                    <span class="absolute -top-1 -right-1 flex h-4 w-4">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-4 w-4 bg-amber-500"></span>
                    </span>
                </div>

                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/10 border border-amber-500/30 text-amber-300 font-mono text-[11px] font-bold uppercase tracking-widest">
                    <i data-lucide="radio" class="w-3.5 h-3.5 text-amber-400 animate-pulse"></i>
                    <span>INFORMASI RESMI PANITIA</span>
                </div>
            </div>

            <div class="space-y-2">
                <h2 class="text-2xl sm:text-3xl lg:text-4xl font-black text-white tracking-tight font-display leading-tight">
                    {{ $publication['standby_title'] ?? 'BAGAN PERTANDINGAN SEDANG DISIAPKAN' }}
                </h2>
                <div class="h-1 w-20 bg-gradient-to-r from-amber-500 to-rose-500 rounded-full mx-auto"></div>
            </div>

            <div class="bg-slate-950/70 border border-white/[0.08] rounded-2xl p-5 sm:p-6 text-sm sm:text-base text-slate-300 leading-relaxed font-sans space-y-3">
                <p class="whitespace-pre-line text-slate-200 font-medium">
                    {{ $publication['standby_message'] ?? 'Bagan resmi akan segera dirilis oleh panitia setelah sesi pengundian dan technical meeting selesai.' }}
                </p>

                @if(!empty($publication['standby_contact']))
                <div class="pt-3 border-t border-white/[0.08] flex items-center justify-center gap-2 text-xs font-mono text-amber-400 font-bold">
                    <i data-lucide="map-pin" class="w-3.5 h-3.5"></i>
                    <span>Informasi Langsung: {{ $publication['standby_contact'] }}</span>
                </div>
                @endif
            </div>

            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 p-3.5 rounded-2xl bg-indigo-950/30 border border-indigo-500/30 text-xs text-indigo-200">
                <div class="flex items-center gap-2.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-ping shrink-0"></span>
                    <span class="text-left font-mono text-[11px]">
                        Layar ini memantau rilis resmi secara live. Bagan akan tampil otomatis tanpa perlu refresh.
                    </span>
                </div>
                <button type="button" 
                        @click="checkStatus(true)" 
                        :disabled="isChecking"
                        class="px-3 py-1.5 rounded-xl bg-indigo-600/40 hover:bg-indigo-600 text-white font-bold text-[11px] border border-indigo-400/40 transition shrink-0 flex items-center gap-1.5 cursor-pointer disabled:opacity-50">
                    <i data-lucide="refresh-cw" class="w-3 h-3" :class="isChecking ? 'animate-spin' : ''"></i>
                    <span x-text="isChecking ? 'Memeriksa...' : 'Cek Sekarang'"></span>
                </button>
            </div>

            <p class="text-[11px] text-slate-500 font-mono">
                {{ $competition->name }} • Sistem Manajemen Pertandingan Turnamen TALENTA
            </p>

        </div>
    </main>

    <footer class="bg-slate-900/60 border-t border-slate-800/80 px-6 py-3 text-center text-xs text-slate-500 font-mono">
        &copy; {{ date('Y') }} {{ $appSettings['event_name'] ?? ($appSettings['app_name'] ?? 'TALENTA') }}. Semua Hak Dilindungi.
    </footer>

    <script>
        function standbyApp() {
            return {
                isChecking: false,
                checkInterval: null,

                init() {
                    this.checkInterval = setInterval(() => {
                        this.checkStatus(false);
                    }, 10000);

                    this.$nextTick(() => {
                        if (window.lucide) window.lucide.createIcons();
                    });
                },

                async checkStatus(manual = false) {
                    if (this.isChecking) return;
                    this.isChecking = true;

                    try {
                        const res = await fetch('{{ route("public.bracket.status", $competition->slug) }}');
                        const data = await res.json();
                        if (data && data.is_published === true) {
                            window.location.reload();
                            return;
                        }
                    } catch (err) {
                        console.error('Polling status error:', err);
                    } finally {
                        this.isChecking = false;
                        if (manual && window.lucide) {
                            this.$nextTick(() => window.lucide.createIcons());
                        }
                    }
                }
            };
        }

        function toggleFullScreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch(err => {
                    console.log('Error attempting to enable fullscreen:', err.message);
                });
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                }
            }
        }
    </script>
</body>
</html>
