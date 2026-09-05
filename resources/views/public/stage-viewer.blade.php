<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Layar Panggung & Stage Timer - {{ $competition->name }} | {{ $appSettings['event_name'] ?? ($appSettings['app_name'] ?? 'TALENTA') }}</title>
    
    <!-- Favicon -->
    @if(!empty($appSettings['favicon']))
        <link rel="icon" type="image/png" href="{{ asset('storage/' . $appSettings['favicon']) }}">
        <link rel="shortcut icon" href="{{ asset('storage/' . $appSettings['favicon']) }}">
        <link rel="apple-touch-icon" href="{{ asset('storage/' . $appSettings['favicon']) }}">
    @else
        <link rel="icon" href="{{ asset('favicon.ico') }}">
    @endif

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=Space+Grotesk:wght@600;700;800;900&family=JetBrains+Mono:wght@600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Vite Local Tailwind CSS & JS Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Lucide Icons -->
    <script src="https://cdn.jsdelivr.net/npm/lucide@0.344.0/dist/umd/lucide.min.js"></script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }

        :root {
            --font-display: 'Outfit', 'Plus Jakarta Sans', sans-serif;
            --font-timer: 'JetBrains Mono', 'Space Grotesk', monospace;
        }

        body {
            font-family: var(--font-display);
            background-color: #040711;
            background-image:
                radial-gradient(at 0% 0%, rgba(79, 70, 229, 0.22) 0px, transparent 50%),
                radial-gradient(at 100% 0%, rgba(14, 165, 233, 0.20) 0px, transparent 45%),
                radial-gradient(at 50% 50%, rgba(15, 23, 42, 0.9) 0px, transparent 75%),
                radial-gradient(at 100% 100%, rgba(236, 72, 153, 0.15) 0px, transparent 50%),
                radial-gradient(at 0% 100%, rgba(16, 185, 129, 0.15) 0px, transparent 50%),
                radial-gradient(rgba(255, 255, 255, 0.05) 1.2px, transparent 1.2px);
            background-size: 100% 100%, 100% 100%, 100% 100%, 100% 100%, 100% 100%, 32px 32px;
            background-attachment: fixed;
            color: #F8FAFC;
        }

        /* Ultra Glassmorphism */
        .glass-card {
            background: rgba(13, 20, 36, 0.72);
            backdrop-filter: blur(28px);
            -webkit-backdrop-filter: blur(28px);
            border: 1px solid rgba(255, 255, 255, 0.10);
            box-shadow: 0 20px 50px -15px rgba(0, 0, 0, 0.7), inset 0 1px 0 rgba(255, 255, 255, 0.12);
        }

        .glass-card-amber {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.12) 0%, rgba(15, 23, 42, 0.85) 100%);
            backdrop-filter: blur(24px);
            border: 1px solid rgba(245, 158, 11, 0.35);
            box-shadow: 0 20px 40px -10px rgba(245, 158, 11, 0.15), inset 0 1px 0 rgba(245, 158, 11, 0.3);
        }

        .glass-card-emerald {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.10) 0%, rgba(15, 23, 42, 0.85) 100%);
            backdrop-filter: blur(24px);
            border: 1px solid rgba(16, 185, 129, 0.30);
            box-shadow: 0 20px 40px -10px rgba(16, 185, 129, 0.15), inset 0 1px 0 rgba(16, 185, 129, 0.3);
        }

        .timer-digits {
            font-family: var(--font-timer);
            letter-spacing: -0.04em;
            font-variant-numeric: tabular-nums;
        }

        /* Equalizer Animation */
        @keyframes eq-dance {
            0%, 100% { height: 6px; }
            50% { height: 28px; }
        }
        .eq-bar-1 { animation: eq-dance 0.9s ease-in-out infinite 0.1s; }
        .eq-bar-2 { animation: eq-dance 0.7s ease-in-out infinite 0.3s; }
        .eq-bar-3 { animation: eq-dance 1.1s ease-in-out infinite 0.2s; }
        .eq-bar-4 { animation: eq-dance 0.8s ease-in-out infinite 0.4s; }
        .eq-bar-5 { animation: eq-dance 1.0s ease-in-out infinite 0.15s; }

        /* Pulse Live Animation */
        @keyframes pulse-live {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.3; transform: scale(0.85); }
        }
        .animate-live-dot {
            animation: pulse-live 1.8s infinite ease-in-out;
        }

        @keyframes pulse-ring {
            0% { transform: scale(0.9); opacity: 0.8; }
            100% { transform: scale(2); opacity: 0; }
        }
        .animate-pulse-ring {
            animation: pulse-ring 2s cubic-bezier(0.215, 0.61, 0.355, 1) infinite;
        }

        /* Overtime Warning Blink */
        @keyframes overtime-blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.35; }
        }
        .animate-overtime {
            animation: overtime-blink 0.9s infinite ease-in-out;
        }

        /* Shimmer Effect */
        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }
        .shimmer-text {
            background: linear-gradient(90deg, #ffffff 0%, #93c5fd 50%, #ffffff 100%);
            background-size: 200% auto;
            color: transparent;
            -webkit-background-clip: text;
            background-clip: text;
            animation: shimmer 4s linear infinite;
        }
    </style>
</head>
<body class="font-sans antialiased min-h-screen flex flex-col selection:bg-[#7A5AF8] selection:text-white overflow-x-hidden"
      x-data="stageViewerApp({{ json_encode($initialState) }})"
      x-init="initApp()">

    <!-- TOP GLOWING HEADER BAR -->
    <header class="h-20 sm:h-24 px-4 sm:px-8 flex items-center justify-between border-b border-white/[0.1] bg-[#060913]/85 backdrop-blur-2xl shrink-0 z-30 sticky top-0 shadow-2xl">
        
        <!-- Left: Logo & Event / Competition Identity -->
        <div class="flex items-center gap-3.5 sm:gap-6 min-w-0">
            @if(!empty($appSettings['app_logo']))
                <div class="relative group shrink-0">
                    <div class="absolute -inset-1 rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-600 opacity-40 blur-sm group-hover:opacity-75 transition"></div>
                    <img src="{{ asset('storage/' . $appSettings['app_logo']) }}" alt="Logo" class="relative h-11 sm:h-14 w-auto max-w-[160px] object-contain shrink-0 drop-shadow-md">
                </div>
            @else
                <div class="w-11 h-11 sm:w-14 sm:h-14 rounded-2xl bg-gradient-to-tr from-[#6366F1] via-[#4F46E5] to-[#06B6D4] flex items-center justify-center text-white font-black text-xl sm:text-2xl shadow-lg shadow-indigo-500/30 shrink-0 border border-white/20">
                    <i data-lucide="sparkles" class="w-6 h-6 sm:w-7 sm:h-7 text-white"></i>
                </div>
            @endif

            <div class="min-w-0">
                <div class="flex items-center gap-2.5">
                    <span class="inline-flex items-center gap-1.5 text-[10px] sm:text-xs font-black uppercase tracking-wider text-cyan-300 bg-cyan-500/15 border border-cyan-500/30 px-2.5 py-0.5 rounded-full shadow-sm">
                        <span class="w-1.5 h-1.5 rounded-full bg-cyan-400"></span>
                        {{ $competition->code }} • STAGE DISPLAY
                    </span>
                    
                    <span class="inline-flex items-center gap-1.5 text-[10px] sm:text-xs font-black uppercase tracking-wider text-emerald-300 bg-emerald-500/15 border border-emerald-500/30 px-2.5 py-0.5 rounded-full shadow-sm">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-pulse-ring absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500 animate-live-dot"></span>
                        </span>
                        <span>LIVE ON AIR</span>
                    </span>
                </div>
                
                <h1 class="text-base sm:text-2xl lg:text-3xl font-black text-white truncate tracking-tight font-display mt-0.5 drop-shadow-sm">
                    {{ $competition->name }}
                </h1>
            </div>
        </div>

        <!-- Right: Digital Clock & Action Controls -->
        <div class="flex items-center gap-2.5 sm:gap-4 shrink-0">
            <!-- Realtime Clock Widget -->
            <div class="hidden md:flex flex-col items-end px-3.5 py-1.5 rounded-2xl bg-white/[0.04] border border-white/[0.08] shadow-inner">
                <span class="text-[9px] font-black uppercase tracking-widest text-slate-400">WAKTU LOKAL</span>
                <span class="font-mono text-base font-black text-slate-100 tracking-wider" x-text="clockTime">--:--:--</span>
            </div>

            <!-- Audio Unmute Pill -->
            <button @click="enableAudio()" 
                    x-show="!audioUnlocked"
                    type="button" 
                    class="relative group flex items-center gap-2 px-3 sm:px-4 py-2 sm:py-2.5 rounded-2xl bg-amber-500/20 hover:bg-amber-500/30 text-amber-300 border border-amber-500/40 text-xs font-bold transition shadow-lg shadow-amber-500/10">
                <span class="relative flex h-2.5 w-2.5">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-amber-400"></span>
                </span>
                <i data-lucide="volume-2" class="w-4 h-4"></i>
                <span class="hidden sm:inline">Aktifkan Bel Audio</span>
            </button>

            <!-- Audio Active Indicator -->
            <div x-show="audioUnlocked" x-cloak class="hidden sm:flex items-center gap-1.5 px-3 py-2 rounded-2xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 text-xs font-bold">
                <i data-lucide="volume-check" class="w-4 h-4"></i>
                <span>Bel Aktif</span>
            </div>

            <!-- Fullscreen Button -->
            <button @click="toggleFullscreen()" 
                    type="button" 
                    class="p-2.5 sm:px-4 sm:py-2.5 rounded-2xl bg-white/[0.06] hover:bg-white/[0.12] text-slate-200 hover:text-white border border-white/[0.12] text-xs font-bold transition flex items-center gap-2 shadow-lg">
                <i data-lucide="maximize" class="w-4 h-4" x-show="!isFullscreen"></i>
                <i data-lucide="minimize" class="w-4 h-4" x-show="isFullscreen" x-cloak></i>
                <span class="hidden sm:inline" x-text="isFullscreen ? 'Kecilkan' : 'Layar Penuh'">Layar Penuh</span>
            </button>
        </div>
    </header>

    <!-- MAIN 3-PANEL STAGE LAYOUT -->
    <main class="flex-1 p-3 sm:p-6 lg:p-8 max-w-[1920px] w-full mx-auto grid grid-cols-1 lg:grid-cols-12 gap-5 sm:gap-6 items-stretch">
        
        <!-- ============================================================== -->
        <!-- PANEL 1: SEDANG TAMPIL (NOW PERFORMING) — 8 COLS (66%)          -->
        <!-- ============================================================== -->
        <div class="lg:col-span-8 flex flex-col justify-between glass-card rounded-[2.5rem] p-6 sm:p-8 lg:p-12 relative overflow-hidden group transition-all duration-700">
            
            <!-- Dynamic Stage Aura / Ambient Glow -->
            <div class="absolute -right-24 -top-24 w-96 h-96 rounded-full blur-[100px] pointer-events-none transition-all duration-1000"
                 :class="{
                     'bg-emerald-500/25': timerZone === 'normal' && timer.status === 'running',
                     'bg-amber-500/30': timerZone === 'warning',
                     'bg-rose-600/35 animate-pulse': timerZone === 'overtime',
                     'bg-indigo-600/20': timer.status !== 'running'
                 }"></div>
            
            <div class="absolute -left-20 -bottom-20 w-80 h-80 rounded-full blur-[90px] pointer-events-none transition-all duration-1000"
                 :class="{
                     'bg-teal-500/20': timerZone === 'normal' && timer.status === 'running',
                     'bg-orange-500/25': timerZone === 'warning',
                     'bg-red-600/30': timerZone === 'overtime',
                     'bg-blue-600/15': timer.status !== 'running'
                 }"></div>

            <!-- Top Row: Live Status Pill & Huge Stage Draw Badge -->
            <div class="flex items-center justify-between gap-4 relative z-10">
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center gap-2.5 px-4 py-2 rounded-2xl font-black text-xs sm:text-sm uppercase tracking-wider shadow-lg backdrop-blur-md"
                          :class="{
                              'bg-emerald-500/20 text-emerald-300 border border-emerald-500/50 shadow-emerald-500/10': timer.status === 'running',
                              'bg-amber-500/20 text-amber-300 border border-amber-500/50 shadow-amber-500/10': timer.status === 'paused',
                              'bg-indigo-500/20 text-indigo-300 border border-indigo-500/40 shadow-indigo-500/10': timer.status === 'idle',
                              'bg-rose-500/20 text-rose-300 border border-rose-500/40 shadow-rose-500/10': timer.status === 'finished'
                          }">
                        <span class="relative flex h-3 w-3">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75"
                                  :class="{
                                      'bg-emerald-400': timer.status === 'running',
                                      'bg-amber-400': timer.status === 'paused',
                                      'bg-indigo-400': timer.status === 'idle',
                                      'bg-rose-400': timer.status === 'finished'
                                  }"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3"
                                  :class="{
                                      'bg-emerald-400': timer.status === 'running',
                                      'bg-amber-400': timer.status === 'paused',
                                      'bg-indigo-400': timer.status === 'idle',
                                      'bg-rose-400': timer.status === 'finished'
                                  }"></span>
                        </span>
                        <span x-text="timerStatusLabel">SEDANG TAMPIL</span>
                    </span>

                    <template x-if="current && current.sub_category">
                        <span class="hidden sm:inline-flex items-center px-3.5 py-2 rounded-2xl bg-white/[0.08] border border-white/[0.12] text-xs font-bold text-slate-200 backdrop-blur-md shadow-sm"
                              x-text="current.sub_category"></span>
                    </template>
                </div>

                <!-- Massive High-Impact Stage Draw Number Badge -->
                <template x-if="current && current.draw_number">
                    <div class="relative group">
                        <div class="absolute -inset-1 rounded-2xl bg-gradient-to-r from-amber-500 to-yellow-400 opacity-60 blur-md group-hover:opacity-90 transition"></div>
                        <div class="relative flex items-center gap-2.5 px-4 sm:px-6 py-2 sm:py-3 rounded-2xl bg-[#0e1628] border-2 border-amber-400/80 text-amber-300 shadow-2xl">
                            <span class="text-[10px] sm:text-xs font-black uppercase tracking-widest text-amber-400/90">NO. TAMPIL</span>
                            <span class="text-2xl sm:text-4xl font-black font-mono text-amber-200 tracking-tight" x-text="current.draw_number"></span>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Middle: Performer Identity Info (ACTIVE STATE) -->
            <div class="my-8 sm:my-10 relative z-10" x-show="current">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-cyan-500/15 border border-cyan-500/30 text-cyan-300 text-xs sm:text-sm font-bold uppercase tracking-wider mb-3">
                    <i data-lucide="school" class="w-4 h-4 text-cyan-400"></i>
                    <span x-text="current ? current.institution : '-'" class="truncate"></span>
                    <template x-if="current && current.participant_number">
                        <span class="text-cyan-400/70 font-mono" x-text="'• #' + current.participant_number"></span>
                    </template>
                </div>

                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0 flex-1">
                        <h2 class="text-3xl sm:text-5xl lg:text-7xl font-black text-white tracking-tight leading-tight uppercase font-display drop-shadow-lg"
                            x-text="current ? current.name : 'Menunggu Penampil...'">
                        </h2>

                        <!-- Member names if collective -->
                        <template x-if="current && current.members && current.members.length > 1">
                            <p class="text-xs sm:text-base text-slate-300 line-clamp-1 mt-3 flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded-lg bg-white/10 text-slate-300 font-bold text-xs uppercase">Anggota Tim:</span>
                                <span x-text="current.members.join(', ')"></span>
                            </p>
                        </template>
                    </div>

                    <!-- Live Equalizer Visualizer (Dancing Bars on Stage) -->
                    <div class="hidden sm:flex items-end gap-1.5 h-10 px-3 py-2 rounded-2xl bg-white/[0.04] border border-white/[0.08] shrink-0" 
                         x-show="timer.status === 'running'">
                        <span class="w-1.5 bg-emerald-400 rounded-full eq-bar-1"></span>
                        <span class="w-1.5 bg-emerald-400 rounded-full eq-bar-2"></span>
                        <span class="w-1.5 bg-emerald-400 rounded-full eq-bar-3"></span>
                        <span class="w-1.5 bg-emerald-400 rounded-full eq-bar-4"></span>
                        <span class="w-1.5 bg-emerald-400 rounded-full eq-bar-5"></span>
                    </div>
                </div>
            </div>

            <!-- Middle: STANDBY / EMPTY STATE (WHEN NO ACTIVE PERFORMER) -->
            <div class="my-8 sm:my-12 py-10 sm:py-16 text-center relative z-10 rounded-3xl bg-white/[0.02] border border-white/[0.06] backdrop-blur-sm" x-show="!current">
                <div class="relative w-24 h-24 mx-auto mb-5 flex items-center justify-center">
                    <div class="absolute inset-0 rounded-full bg-gradient-to-tr from-indigo-500/20 to-cyan-500/20 blur-xl animate-pulse"></div>
                    <div class="w-20 h-20 rounded-3xl bg-slate-900/90 border border-white/15 flex items-center justify-center shadow-2xl relative">
                        <i data-lucide="mic" class="w-10 h-10 text-cyan-400 animate-bounce"></i>
                    </div>
                </div>
                
                <h3 class="text-xl sm:text-3xl font-black text-white uppercase tracking-tight font-display">
                    PANGGUNG SIAP • STANDBY
                </h3>
                <p class="text-xs sm:text-sm text-slate-400 mt-2 max-w-md mx-auto">
                    Menunggu operator memulai penampilan peserta selanjutnya dari konsol timekeeper.
                </p>

                <!-- Next Performer Preview in Empty State -->
                <template x-if="next">
                    <div class="inline-flex items-center gap-3 mt-6 px-5 py-2.5 rounded-2xl bg-amber-500/15 border border-amber-500/30 text-amber-300 text-xs sm:text-sm font-bold shadow-lg">
                        <span class="w-2 h-2 rounded-full bg-amber-400 animate-ping"></span>
                        <span>Peserta berikutnya siap: <strong class="text-white" x-text="next.name"></strong> (<span x-text="next.institution"></span>)</span>
                    </div>
                </template>
            </div>

            <!-- Bottom: Massive High-Impact Stage Digital Timer & Progress -->
            <div class="space-y-4 sm:space-y-6 relative z-10 pt-6 border-t border-white/[0.12]">
                
                <div class="flex flex-col sm:flex-row items-center justify-between gap-5">
                    <!-- GIANT TIMER DISPLAY -->
                    <div class="flex items-center gap-3 sm:gap-6">
                        <div class="timer-digits text-7xl sm:text-8xl lg:text-[10rem] font-black tracking-tight transition-all duration-300 select-none leading-none"
                             :class="{
                                 'text-emerald-400 drop-shadow-[0_0_40px_rgba(52,211,153,0.5)]': timerZone === 'normal',
                                 'text-amber-300 drop-shadow-[0_0_45px_rgba(252,211,77,0.6)]': timerZone === 'warning',
                                 'text-rose-500 drop-shadow-[0_0_55px_rgba(244,63,94,0.75)] animate-overtime': timerZone === 'overtime'
                             }"
                             x-text="formattedTimer">
                            00:00
                        </div>
                        
                        <div class="flex flex-col text-left">
                            <span class="text-xs sm:text-sm font-black uppercase tracking-widest"
                                  :class="{
                                      'text-emerald-400': timerZone === 'normal',
                                      'text-amber-300': timerZone === 'warning',
                                      'text-rose-400': timerZone === 'overtime'
                                  }"
                                  x-text="timerZone === 'overtime' ? 'OVERTIME (LEWAT)' : 'SISA WAKTU'">
                                SISA WAKTU
                            </span>
                            <span class="text-[11px] sm:text-xs text-slate-400 font-mono mt-0.5" 
                                  x-text="'Maks ' + competition.duration_minutes + ' Menit'"></span>
                        </div>
                    </div>

                    <!-- Visual Indicator Badges -->
                    <div class="flex sm:flex-col items-center sm:items-end gap-2 text-right">
                        <div class="px-4 py-2 rounded-2xl border text-xs sm:text-sm font-black tracking-wider uppercase shadow-lg backdrop-blur-md"
                             :class="{
                                 'bg-emerald-500/20 border-emerald-500/40 text-emerald-300 shadow-emerald-500/10': timerZone === 'normal',
                                 'bg-amber-500/25 border-amber-500/50 text-amber-300 shadow-amber-500/10 animate-pulse': timerZone === 'warning',
                                 'bg-rose-500/30 border-rose-500/50 text-rose-300 shadow-rose-500/20 animate-bounce': timerZone === 'overtime'
                             }">
                            <span x-text="timerZoneLabel"></span>
                        </div>
                        <span class="text-[11px] text-slate-400 font-mono"
                              x-text="'Peringatan: sisa ' + competition.warning_minutes + ' menit'"></span>
                    </div>
                </div>

                <!-- Sleek Glowing Progress Bar -->
                <div class="w-full bg-slate-950/80 rounded-full h-3.5 sm:h-5 p-1 border border-white/[0.12] overflow-hidden shadow-inner">
                    <div class="h-full rounded-full transition-all duration-300 ease-out"
                         :style="'width: ' + progressPercent + '%;'"
                         :class="{
                             'bg-gradient-to-r from-teal-500 via-emerald-400 to-emerald-300 shadow-[0_0_20px_rgba(52,211,153,0.7)]': timerZone === 'normal',
                             'bg-gradient-to-r from-amber-600 via-amber-400 to-yellow-300 shadow-[0_0_25px_rgba(252,211,77,0.8)]': timerZone === 'warning',
                             'bg-gradient-to-r from-rose-700 via-red-500 to-rose-400 shadow-[0_0_30px_rgba(244,63,94,0.9)]': timerZone === 'overtime'
                         }"></div>
                </div>

            </div>
        </div>

        <!-- ============================================================== -->
        <!-- SIDEBAR: BERIKUTNYA & RIWAYAT SELESAI — 4 COLS (33%)            -->
        <!-- ============================================================== -->
        <div class="lg:col-span-4 flex flex-col gap-5 sm:gap-6">
            
            <!-- CARD 2: BERIKUTNYA (UP NEXT / STANDBY) -->
            <div class="glass-card-amber rounded-[2rem] p-6 sm:p-7 relative overflow-hidden shadow-2xl">
                <!-- Background ambient sparkle -->
                <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-amber-500/15 rounded-full blur-2xl pointer-events-none"></div>

                <div class="flex items-center justify-between gap-2 pb-3.5 border-b border-amber-500/25 relative z-10">
                    <div class="flex items-center gap-2.5">
                        <span class="relative flex h-2.5 w-2.5">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-amber-400"></span>
                        </span>
                        <h3 class="text-xs sm:text-sm font-black uppercase tracking-wider text-amber-300">
                            PESERTA BERIKUTNYA
                        </h3>
                    </div>
                    <span class="text-[10px] font-black uppercase px-2.5 py-0.5 rounded-full bg-amber-400/20 text-amber-300 border border-amber-400/30 font-mono tracking-wider">
                        STANDBY
                    </span>
                </div>

                <template x-if="next">
                    <div class="mt-4 space-y-3 relative z-10">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0 flex-1">
                                <h4 class="text-lg sm:text-2xl font-black text-white truncate font-display uppercase tracking-tight"
                                    x-text="next.name"></h4>
                                <p class="text-xs sm:text-sm font-semibold text-amber-200/90 truncate flex items-center gap-2 mt-1">
                                    <i data-lucide="school" class="w-4 h-4 text-amber-400 shrink-0"></i>
                                    <span x-text="next.institution"></span>
                                </p>
                            </div>

                            <template x-if="next.draw_number">
                                <div class="px-3.5 py-1.5 rounded-2xl bg-amber-400/20 border border-amber-400/40 text-amber-300 text-center shrink-0 shadow-lg shadow-amber-500/10">
                                    <span class="text-[9px] font-black block leading-none text-amber-400/80">NO.</span>
                                    <span class="text-xl font-black font-mono leading-none text-amber-200" x-text="next.draw_number"></span>
                                </div>
                            </template>
                        </div>

                        <!-- Backstage Notice -->
                        <div class="p-3 rounded-2xl bg-amber-500/15 border border-amber-500/25 text-xs text-amber-200 flex items-center gap-2.5 mt-4 shadow-sm">
                            <i data-lucide="bell-ring" class="w-4 h-4 text-amber-400 shrink-0 animate-bounce"></i>
                            <span class="font-medium">Harap segera bersiap di sayap panggung (Backstage).</span>
                        </div>
                    </div>
                </template>

                <template x-if="!next">
                    <div class="py-8 text-center text-slate-400 text-xs font-semibold relative z-10">
                        <div class="w-12 h-12 rounded-2xl bg-white/[0.05] border border-white/10 flex items-center justify-center mx-auto mb-2 text-slate-500">
                            <i data-lucide="check-check" class="w-6 h-6"></i>
                        </div>
                        <span class="text-slate-300 font-bold block">Tidak Ada Antrian Berikutnya</span>
                        <span class="text-[11px] text-slate-500">Semua peserta telah dipanggil atau selesai.</span>
                    </div>
                </template>
            </div>

            <!-- CARD 3: RIWAYAT SELESAI (COMPLETED HISTORY) -->
            <div class="flex-1 glass-card rounded-[2rem] p-6 sm:p-7 flex flex-col relative overflow-hidden shadow-2xl">
                <div class="flex items-center justify-between pb-3.5 border-b border-white/[0.1] mb-4">
                    <h3 class="text-xs sm:text-sm font-black uppercase tracking-wider text-slate-200 flex items-center gap-2">
                        <div class="w-6 h-6 rounded-lg bg-emerald-500/20 border border-emerald-500/30 flex items-center justify-center text-emerald-400">
                            <i data-lucide="history" class="w-3.5 h-3.5"></i>
                        </div>
                        <span>Sudah Selesai Tampil</span>
                    </h3>
                    <span class="text-xs font-black text-emerald-400 px-2.5 py-0.5 rounded-full bg-emerald-500/15 border border-emerald-500/30 font-mono" 
                          x-text="completed.length + ' Peserta'">0 Peserta</span>
                </div>

                <div class="flex-1 overflow-y-auto space-y-2.5 max-h-[380px] lg:max-h-[420px] pr-1.5 scrollbar-thin scrollbar-thumb-slate-700 scrollbar-track-transparent">
                    <template x-for="(item, idx) in completed" :key="item.id">
                        <div class="p-3.5 rounded-2xl bg-white/[0.03] hover:bg-white/[0.07] border border-white/[0.06] transition-all flex items-center justify-between gap-3 group">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-9 h-9 rounded-xl bg-emerald-500/20 border border-emerald-500/40 text-emerald-300 font-black text-xs flex items-center justify-center shrink-0 font-mono shadow-sm group-hover:scale-105 transition-transform">
                                    <span x-text="item.draw_number || (idx + 1)"></span>
                                </div>
                                <div class="min-w-0">
                                    <h5 class="text-xs sm:text-sm font-bold text-white truncate group-hover:text-emerald-300 transition-colors" x-text="item.name"></h5>
                                    <p class="text-[11px] text-slate-400 truncate" x-text="item.institution"></p>
                                </div>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="text-[11px] font-black text-emerald-400 px-2.5 py-1 rounded-xl bg-emerald-500/15 border border-emerald-500/25 font-mono shadow-sm"
                                      x-text="'⏱ ' + item.formatted_duration"></span>
                            </div>
                        </div>
                    </template>

                    <template x-if="completed.length === 0">
                        <div class="py-12 text-center text-slate-500 text-xs flex flex-col items-center justify-center">
                            <i data-lucide="clock" class="w-8 h-8 opacity-40 mb-2"></i>
                            <span class="font-medium">Belum ada peserta yang menyelesaikan penampilan.</span>
                        </div>
                    </template>
                </div>
            </div>

        </div>
    </main>

    <!-- FOOTER INFO BAR -->
    <footer class="h-12 px-6 sm:px-8 flex items-center justify-between text-xs text-slate-400 border-t border-white/[0.08] bg-[#040711]/90 backdrop-blur-md">
        <div class="flex items-center gap-2 truncate">
            <span class="font-bold text-slate-300">{{ $appSettings['institution_name'] ?? 'MTsN 1 Blitar' }}</span>
            <span>•</span>
            <span class="truncate">{{ $appSettings['event_name'] ?? ($appSettings['app_name'] ?? 'TALENTA') }}</span>
        </div>
        <div class="flex items-center gap-3 shrink-0">
            <span class="font-mono text-slate-400 hidden sm:inline" x-text="clockTime">--:--:--</span>
            <span class="text-[10px] uppercase font-mono px-2 py-0.5 rounded bg-white/5 border border-white/10 text-slate-400">STAGE SYSTEM</span>
        </div>
    </footer>

    <!-- JAVASCRIPT LOGIC & ENHANCED WEB AUDIO API -->
    <script>
        function stageViewerApp(initialState) {
            return {
                competition: initialState.competition,
                current: initialState.current,
                next: initialState.next,
                completed: initialState.completed || [],
                timer: initialState.timer,
                serverTime: initialState.server_time,
                
                // Local Timer State
                secondsLeft: initialState.timer.seconds_remaining || (initialState.competition.duration_minutes * 60),
                totalSeconds: initialState.timer.total_duration_seconds || (initialState.competition.duration_minutes * 60),
                warningThreshold: initialState.timer.warning_threshold_seconds || (initialState.competition.warning_minutes * 60),
                
                audioUnlocked: false,
                audioCtx: null,
                lastBellTimestamp: null,
                isFullscreen: false,
                clockTime: '',
                pollInterval: null,
                timerTickInterval: null,

                initApp() {
                    this.updateClock();
                    setInterval(() => this.updateClock(), 1000);

                    // Re-render Lucide icons
                    this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });

                    // Auto attempt audio unlock on first user click anywhere on the page
                    document.addEventListener('click', () => {
                        if (!this.audioUnlocked) this.enableAudio();
                    }, { once: true });

                    // Start Local Timer countdown
                    this.startLocalTicker();

                    // Start Background Sync Polling (1.5s interval for snappy TV updates)
                    this.startSyncPolling();
                },

                startLocalTicker() {
                    if (this.timerTickInterval) clearInterval(this.timerTickInterval);
                    this.timerTickInterval = setInterval(() => {
                        if (this.timer.status === 'running') {
                            this.secondsLeft = Math.max(-3600, this.secondsLeft - 1);

                            // Auto trigger warning chime when reaching exact warning minute
                            if (this.secondsLeft === this.warningThreshold) {
                                this.playChimeSound('single');
                            } else if (this.secondsLeft === 0) {
                                this.playChimeSound('double');
                            }
                        }
                    }, 1000);
                },

                startSyncPolling() {
                    const slug = this.competition.slug || this.competition.code;
                    const url = '{{ route("stage.api.state", ":slug") }}'.replace(':slug', slug);

                    this.pollInterval = setInterval(async () => {
                        try {
                            const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
                            if (res.ok) {
                                const data = await res.json();
                                this.applySyncData(data);
                            }
                        } catch (err) {
                            console.warn('Sync poll error:', err);
                        }
                    }, 1500);
                },

                applySyncData(data) {
                    this.competition = data.competition;
                    this.current = data.current;
                    this.next = data.next;
                    this.completed = data.completed || [];
                    
                    const oldStatus = this.timer.status;
                    this.timer = data.timer;
                    this.totalSeconds = data.timer.total_duration_seconds;
                    this.warningThreshold = data.timer.warning_threshold_seconds;

                    // If server timer paused or idle, sync seconds directly
                    if (data.timer.status !== 'running') {
                        this.secondsLeft = data.timer.seconds_remaining;
                    } else {
                        // Drift correction: calculate remaining based on server start time if available
                        if (data.timer.started_at) {
                            const startedMs = new Date(data.timer.started_at).getTime();
                            const nowMs = Date.now();
                            const elapsed = Math.floor((nowMs - startedMs) / 1000);
                            const calculatedRemaining = data.timer.seconds_remaining - elapsed;
                            // Only adjust if drift > 2 seconds
                            if (Math.abs(this.secondsLeft - calculatedRemaining) > 2) {
                                this.secondsLeft = calculatedRemaining;
                            }
                        } else {
                            if (Math.abs(this.secondsLeft - data.timer.seconds_remaining) > 2) {
                                this.secondsLeft = data.timer.seconds_remaining;
                            }
                        }
                    }

                    // Check for remote bell triggers
                    if (data.timer.bell_trigger && data.timer.bell_trigger.timestamp !== this.lastBellTimestamp) {
                        this.lastBellTimestamp = data.timer.bell_trigger.timestamp;
                        this.playChimeSound(data.timer.bell_trigger.type || 'bell');
                    }

                    this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
                },

                // Timer Computed Getters
                get formattedTimer() {
                    const isNegative = this.secondsLeft < 0;
                    const absSec = Math.abs(this.secondsLeft);
                    const mins = Math.floor(absSec / 60);
                    const secs = absSec % 60;
                    const sign = isNegative ? '+' : '';
                    return sign + String(mins).padStart(2, '0') + ':' + String(secs).padStart(2, '0');
                },

                get timerZone() {
                    if (this.secondsLeft <= 0) return 'overtime';
                    if (this.secondsLeft <= this.warningThreshold) return 'warning';
                    return 'normal';
                },

                get timerZoneLabel() {
                    if (this.secondsLeft <= 0) return 'WAKTU HABIS (OVERTIME)';
                    if (this.secondsLeft <= this.warningThreshold) return 'PERINGATAN WAKTU';
                    return 'WAKTU AMAN';
                },

                get timerStatusLabel() {
                    if (this.timer.status === 'running') return 'SEDANG TAMPIL';
                    if (this.timer.status === 'paused') return 'DIJEDA (PAUSED)';
                    if (this.timer.status === 'finished') return 'SELESAI';
                    return 'BERSIAP (STANDBY)';
                },

                get progressPercent() {
                    if (this.totalSeconds <= 0) return 100;
                    if (this.secondsLeft <= 0) return 100;
                    const elapsed = this.totalSeconds - this.secondsLeft;
                    return Math.min(100, Math.max(0, (elapsed / this.totalSeconds) * 100));
                },

                updateClock() {
                    const now = new Date();
                    this.clockTime = now.toLocaleTimeString('id-ID', { hour12: false });
                },

                toggleFullscreen() {
                    if (!document.fullscreenElement) {
                        document.documentElement.requestFullscreen().catch(err => console.log(err));
                        this.isFullscreen = true;
                    } else {
                        if (document.exitFullscreen) {
                            document.exitFullscreen();
                            this.isFullscreen = false;
                        }
                    }
                },

                // ==========================================
                // WEB AUDIO API SOUND SYNTHESIS ENGINE
                // ==========================================
                enableAudio() {
                    try {
                        const AudioContext = window.AudioContext || window.webkitAudioContext;
                        this.audioCtx = new AudioContext();
                        if (this.audioCtx.state === 'suspended') {
                            this.audioCtx.resume();
                        }
                        this.audioUnlocked = true;
                    } catch (e) {
                        console.error('Audio init failed:', e);
                    }
                },

                playChimeSound(type) {
                    if (!this.audioCtx) {
                        const AudioContext = window.AudioContext || window.webkitAudioContext;
                        if (AudioContext) this.audioCtx = new AudioContext();
                    }
                    if (!this.audioCtx) return;
                    if (this.audioCtx.state === 'suspended') this.audioCtx.resume();

                    const ctx = this.audioCtx;
                    const now = ctx.currentTime;

                    if (type === 'double' || type === 'two_bells') {
                        // 2 Harmonic Stage Bells
                        this.synthBellTone(ctx, now, 880, 1.2);
                        this.synthBellTone(ctx, now + 0.45, 880, 1.6);
                    } else if (type === 'gong') {
                        // Deep Resonant Gong
                        this.synthGongTone(ctx, now, 220, 2.8);
                    } else if (type === 'buzzer') {
                        // Digital Buzzer
                        this.synthBuzzerTone(ctx, now, 440, 0.8);
                    } else {
                        // Default Single Stage Bell (Crystal Clear Chime)
                        this.synthBellTone(ctx, now, 880, 1.8);
                    }
                },

                synthBellTone(ctx, startTime, freq, duration) {
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();

                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(freq, startTime);
                    osc.frequency.exponentialRampToValueAtTime(freq * 0.98, startTime + duration);

                    gain.gain.setValueAtTime(0.001, startTime);
                    gain.gain.exponentialRampToValueAtTime(0.6, startTime + 0.02);
                    gain.gain.exponentialRampToValueAtTime(0.0001, startTime + duration);

                    osc.connect(gain);
                    gain.connect(ctx.destination);

                    osc.start(startTime);
                    osc.stop(startTime + duration);
                },

                synthGongTone(ctx, startTime, freq, duration) {
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();

                    osc.type = 'triangle';
                    osc.frequency.setValueAtTime(freq, startTime);
                    osc.frequency.exponentialRampToValueAtTime(freq * 0.85, startTime + duration);

                    gain.gain.setValueAtTime(0.001, startTime);
                    gain.gain.exponentialRampToValueAtTime(0.8, startTime + 0.04);
                    gain.gain.exponentialRampToValueAtTime(0.0001, startTime + duration);

                    osc.connect(gain);
                    gain.connect(ctx.destination);

                    osc.start(startTime);
                    osc.stop(startTime + duration);
                },

                synthBuzzerTone(ctx, startTime, freq, duration) {
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();

                    osc.type = 'sawtooth';
                    osc.frequency.setValueAtTime(freq, startTime);

                    gain.gain.setValueAtTime(0.4, startTime);
                    gain.gain.exponentialRampToValueAtTime(0.01, startTime + duration);

                    osc.connect(gain);
                    gain.connect(ctx.destination);

                    osc.start(startTime);
                    osc.stop(startTime + duration);
                }
            };
        }
    </script>
</body>
</html>