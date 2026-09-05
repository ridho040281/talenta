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

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800;900&family=Space+Grotesk:wght@700;800&family=Space+Mono:wght@700&display=swap" rel="stylesheet">
    
    <!-- Vite Local Tailwind CSS & JS Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Lucide Icons -->
    <script src="https://cdn.jsdelivr.net/npm/lucide@0.344.0/dist/umd/lucide.min.js"></script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }

        body {
            background-color: #070B14;
            background-image:
                radial-gradient(at 10% 15%, rgba(78, 110, 255, 0.28) 0px, transparent 50%),
                radial-gradient(at 90% 10%, rgba(122, 90, 248, 0.25) 0px, transparent 45%),
                radial-gradient(at 50% 60%, rgba(15, 23, 42, 0.8) 0px, transparent 70%),
                radial-gradient(at 80% 85%, rgba(236, 72, 153, 0.15) 0px, transparent 50%),
                radial-gradient(at 20% 80%, rgba(16, 185, 129, 0.15) 0px, transparent 50%),
                linear-gradient(180deg, #0d1527 0%, #080d1a 50%, #050811 100%);
            background-attachment: fixed;
            color: #F8FAFC;
        }

        .card-stage {
            background: rgba(15, 23, 42, 0.75);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            box-shadow: 0 10px 40px -10px rgba(0, 0, 0, 0.6);
        }

        .timer-digits {
            font-family: 'Space Grotesk', 'Space Mono', monospace;
            letter-spacing: -0.02em;
            font-variant-numeric: tabular-nums;
        }

        @keyframes pulse-live {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.4; transform: scale(0.9); }
        }
        .animate-live-dot {
            animation: pulse-live 1.8s infinite ease-in-out;
        }

        @keyframes overtime-blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.35; }
        }
        .animate-overtime {
            animation: overtime-blink 1s infinite ease-in-out;
        }
    </style>
</head>
<body class="font-sans antialiased min-h-screen flex flex-col selection:bg-[#7A5AF8] selection:text-white"
      x-data="stageViewerApp({{ json_encode($initialState) }})"
      x-init="initApp()">

    <!-- TOP HEADER BAR -->
    <header class="h-20 sm:h-24 px-4 sm:px-8 flex items-center justify-between border-b border-white/[0.1] bg-[#070b14]/80 backdrop-blur-xl shrink-0 z-30">
        <div class="flex items-center gap-3 sm:gap-5 min-w-0">
            @if(!empty($appSettings['app_logo']))
                <img src="{{ asset('storage/' . $appSettings['app_logo']) }}" alt="Logo" class="h-10 sm:h-12 w-auto max-w-[150px] object-contain shrink-0">
            @else
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-gradient-to-tr from-[#7A5AF8] to-[#4E6EFF] flex items-center justify-center text-white font-black text-xl shadow-lg shadow-[#7A5AF8]/30 shrink-0">
                    <i data-lucide="sparkles" class="w-6 h-6"></i>
                </div>
            @endif

            <div class="min-w-0">
                <div class="flex items-center gap-2">
                    <span class="text-[10px] sm:text-xs font-black uppercase tracking-widest text-[#84D0FF] bg-[#4E6EFF]/20 border border-[#4E6EFF]/30 px-2 py-0.5 rounded-md">
                        {{ $competition->code }} • STAGE DISPLAY
                    </span>
                    <span class="hidden sm:inline-flex items-center gap-1.5 text-[11px] font-bold text-slate-400">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-live-dot"></span>
                        <span>LIVE SINKRON</span>
                    </span>
                </div>
                <h1 class="text-base sm:text-2xl font-black text-white truncate tracking-tight font-display mt-0.5">
                    {{ $competition->name }}
                </h1>
            </div>
        </div>

        <div class="flex items-center gap-2 sm:gap-3 shrink-0">
            <!-- Audio Permission Button (Required for Browser Autoplay Policy) -->
            <button @click="enableAudio()" 
                    x-show="!audioUnlocked"
                    type="button" 
                    class="flex items-center gap-1.5 px-3 py-2 rounded-xl bg-amber-500/20 hover:bg-amber-500/30 text-amber-300 border border-amber-500/40 text-xs font-bold transition">
                <i data-lucide="volume-2" class="w-4 h-4"></i>
                <span class="hidden sm:inline">Aktifkan Bel Suara</span>
            </button>

            <!-- Fullscreen Button -->
            <button @click="toggleFullscreen()" 
                    type="button" 
                    class="p-2 sm:px-3.5 sm:py-2 rounded-xl bg-white/[0.08] hover:bg-white/[0.15] text-slate-200 hover:text-white border border-white/[0.12] text-xs font-bold transition flex items-center gap-1.5">
                <i data-lucide="maximize" class="w-4 h-4" x-show="!isFullscreen"></i>
                <i data-lucide="minimize" class="w-4 h-4" x-show="isFullscreen" x-cloak></i>
                <span class="hidden sm:inline" x-text="isFullscreen ? 'Kecilkan' : 'Layar Penuh'">Layar Penuh</span>
            </button>
        </div>
    </header>

    <!-- MAIN 3-PANEL STAGE LAYOUT -->
    <main class="flex-1 p-3 sm:p-6 lg:p-8 max-w-[1920px] w-full mx-auto grid grid-cols-1 lg:grid-cols-12 gap-4 sm:gap-6 items-stretch">
        
        <!-- ============================================================== -->
        <!-- PANEL 1: SEDANG TAMPIL (NOW PERFORMING) — 8 COLS (66%)          -->
        <!-- ============================================================== -->
        <div class="lg:col-span-8 flex flex-col justify-between card-stage rounded-3xl p-5 sm:p-8 lg:p-10 border border-white/[0.15] relative overflow-hidden group">
            
            <!-- Ambient Glow for Current Performer -->
            <div class="absolute -right-20 -top-20 w-80 h-80 rounded-full blur-3xl pointer-events-none transition-colors duration-1000"
                 :class="{
                     'bg-emerald-500/15': timerZone === 'normal',
                     'bg-amber-500/20': timerZone === 'warning',
                     'bg-rose-600/25': timerZone === 'overtime'
                 }"></div>

            <!-- Top Row: Status Badge & Category -->
            <div class="flex items-center justify-between gap-3 relative z-10">
                <div class="flex items-center gap-2 sm:gap-3">
                    <span class="flex items-center gap-2 px-3 py-1.5 rounded-xl font-black text-xs sm:text-sm uppercase tracking-wider"
                          :class="{
                              'bg-emerald-500/20 text-emerald-300 border border-emerald-500/40': timer.status === 'running',
                              'bg-amber-500/20 text-amber-300 border border-amber-500/40': timer.status === 'paused',
                              'bg-indigo-500/20 text-indigo-300 border border-indigo-500/40': timer.status === 'idle',
                              'bg-rose-500/20 text-rose-300 border border-rose-500/40': timer.status === 'finished'
                          }">
                        <span class="w-2.5 h-2.5 rounded-full"
                              :class="{
                                  'bg-emerald-400 animate-live-dot': timer.status === 'running',
                                  'bg-amber-400': timer.status === 'paused',
                                  'bg-indigo-400': timer.status === 'idle',
                                  'bg-rose-400': timer.status === 'finished'
                              }"></span>
                        <span x-text="timerStatusLabel">SEDANG TAMPIL</span>
                    </span>

                    <template x-if="current && current.sub_category">
                        <span class="hidden sm:inline-block px-3 py-1.5 rounded-xl bg-white/[0.06] border border-white/[0.1] text-xs font-bold text-slate-300"
                              x-text="current.sub_category"></span>
                    </template>
                </div>

                <!-- Stage Number Badge -->
                <template x-if="current && current.draw_number">
                    <div class="flex items-center gap-2 px-3.5 sm:px-4 py-1.5 sm:py-2 rounded-2xl bg-gradient-to-r from-amber-500/25 to-yellow-500/25 border border-amber-500/40 text-amber-300 shadow-lg shadow-amber-500/10">
                        <span class="text-[10px] sm:text-xs font-black uppercase tracking-widest text-amber-400/80">NO. TAMPIL</span>
                        <span class="text-lg sm:text-2xl font-black font-mono text-amber-200" x-text="current.draw_number"></span>
                    </div>
                </template>
            </div>

            <!-- Middle: Performer Identity Info -->
            <div class="my-6 sm:my-8 space-y-2 sm:space-y-3 relative z-10" x-show="current">
                <div class="flex items-center gap-2 text-xs sm:text-sm font-bold text-[#84D0FF]">
                    <i data-lucide="school" class="w-4 h-4 sm:w-5 sm:h-5 text-[#84D0FF]"></i>
                    <span x-text="current ? current.institution : '-'" class="truncate uppercase tracking-wider"></span>
                    <template x-if="current && current.participant_number">
                        <span class="text-slate-500 font-mono" x-text="'(' + current.participant_number + ')'"></span>
                    </template>
                </div>

                <h2 class="text-3xl sm:text-5xl lg:text-6xl font-black text-white tracking-tight leading-tight uppercase font-display"
                    x-text="current ? current.name : 'Menunggu Penampil...'">
                </h2>

                <!-- Member names if collective -->
                <template x-if="current && current.members && current.members.length > 1">
                    <p class="text-xs sm:text-sm text-slate-400 line-clamp-1">
                        <span class="font-bold text-slate-300">Anggota:</span>
                        <span x-text="current.members.join(', ')"></span>
                    </p>
                </template>
            </div>

            <div class="my-6 sm:my-8 text-center py-12 text-slate-500 relative z-10" x-show="!current">
                <i data-lucide="user-x" class="w-12 h-12 mx-auto mb-2 opacity-50"></i>
                <p class="text-sm font-bold">Belum ada peserta yang dipilih untuk tampil.</p>
            </div>

            <!-- Bottom: Massive High-Contrast Digital Timer & Progress -->
            <div class="space-y-4 sm:space-y-6 relative z-10 pt-4 border-t border-white/[0.08]">
                
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <!-- TIMER DISPLAY -->
                    <div class="flex items-baseline gap-2 sm:gap-4">
                        <div class="timer-digits text-7xl sm:text-8xl lg:text-9xl font-black tracking-tight transition-colors duration-300 select-none"
                             :class="{
                                 'text-emerald-400 drop-shadow-[0_0_35px_rgba(52,211,153,0.4)]': timerZone === 'normal',
                                 'text-amber-300 drop-shadow-[0_0_40px_rgba(252,211,77,0.5)]': timerZone === 'warning',
                                 'text-rose-500 drop-shadow-[0_0_45px_rgba(244,63,94,0.6)] animate-overtime': timerZone === 'overtime'
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
                                  x-text="timerZone === 'overtime' ? 'OVERTIME' : 'SISA WAKTU'">
                                SISA WAKTU
                            </span>
                            <span class="text-[10px] sm:text-xs text-slate-400 font-mono" 
                                  x-text="'Maks ' + competition.duration_minutes + ' Menit'"></span>
                        </div>
                    </div>

                    <!-- Visual Indicator Badges -->
                    <div class="flex sm:flex-col items-end gap-2 text-right">
                        <div class="px-3 py-1.5 rounded-xl border text-xs font-bold"
                             :class="{
                                 'bg-emerald-500/15 border-emerald-500/30 text-emerald-300': timerZone === 'normal',
                                 'bg-amber-500/20 border-amber-500/40 text-amber-300 animate-pulse': timerZone === 'warning',
                                 'bg-rose-500/25 border-rose-500/40 text-rose-300': timerZone === 'overtime'
                             }">
                            <span x-text="timerZoneLabel"></span>
                        </div>
                        <span class="text-[11px] text-slate-400 hidden sm:block font-mono"
                              x-text="'Peringatan: sisa ' + competition.warning_minutes + ' menit'"></span>
                    </div>
                </div>

                <!-- Progress Bar -->
                <div class="w-full bg-slate-900/90 rounded-full h-3 sm:h-4 p-0.5 border border-white/[0.1] overflow-hidden shadow-inner">
                    <div class="h-full rounded-full transition-all duration-300 ease-out"
                         :style="'width: ' + progressPercent + '%;'"
                         :class="{
                             'bg-gradient-to-r from-emerald-500 to-teal-400 shadow-[0_0_15px_rgba(52,211,153,0.5)]': timerZone === 'normal',
                             'bg-gradient-to-r from-amber-500 to-yellow-400 shadow-[0_0_20px_rgba(252,211,77,0.6)]': timerZone === 'warning',
                             'bg-gradient-to-r from-rose-600 to-red-500 shadow-[0_0_25px_rgba(244,63,94,0.7)]': timerZone === 'overtime'
                         }"></div>
                </div>

            </div>
        </div>

        <!-- ============================================================== -->
        <!-- SIDEBAR: BERIKUTNYA & RIWAYAT SELESAI — 4 COLS (33%)            -->
        <!-- ============================================================== -->
        <div class="lg:col-span-4 flex flex-col gap-4 sm:gap-6">
            
            <!-- CARD 2: BERIKUTNYA (UP NEXT / STANDBY) -->
            <div class="card-stage rounded-3xl p-5 sm:p-6 border border-amber-500/30 bg-gradient-to-br from-amber-500/10 via-slate-900/80 to-slate-900/90 shadow-xl relative overflow-hidden">
                <div class="flex items-center justify-between gap-2 pb-3 border-b border-amber-500/20">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-400 animate-pulse"></span>
                        <h3 class="text-xs sm:text-sm font-black uppercase tracking-wider text-amber-300">
                            PESERTA BERIKUTNYA
                        </h3>
                    </div>
                    <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-full bg-amber-400/20 text-amber-300 border border-amber-400/30 font-mono">
                        Standby
                    </span>
                </div>

                <template x-if="next">
                    <div class="mt-4 space-y-2">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <h4 class="text-lg sm:text-xl font-black text-white truncate font-display uppercase tracking-tight"
                                    x-text="next.name"></h4>
                                <p class="text-xs font-semibold text-slate-300 truncate flex items-center gap-1.5 mt-0.5">
                                    <i data-lucide="school" class="w-3.5 h-3.5 text-amber-400 shrink-0"></i>
                                    <span x-text="next.institution"></span>
                                </p>
                            </div>
                            <template x-if="next.draw_number">
                                <div class="px-3 py-1 rounded-xl bg-amber-400/20 border border-amber-400/30 text-amber-300 text-center shrink-0">
                                    <span class="text-[9px] font-bold block leading-none">NO</span>
                                    <span class="text-base font-black font-mono leading-none" x-text="next.draw_number"></span>
                                </div>
                            </template>
                        </div>
                        <div class="p-2.5 rounded-xl bg-amber-500/10 border border-amber-500/20 text-[11px] text-amber-200/90 flex items-center gap-2 mt-3">
                            <i data-lucide="alert-circle" class="w-4 h-4 text-amber-400 shrink-0"></i>
                            <span>Harap bersiap di sayap panggung (Backstage).</span>
                        </div>
                    </div>
                </template>

                <template x-if="!next">
                    <div class="py-6 text-center text-slate-500 text-xs font-semibold">
                        <i data-lucide="check-check" class="w-6 h-6 mx-auto mb-1 opacity-50"></i>
                        <span>Tidak ada antrian peserta berikutnya (Selesai).</span>
                    </div>
                </template>
            </div>

            <!-- CARD 3: RIWAYAT SELESAI (COMPLETED HISTORY) -->
            <div class="flex-1 card-stage rounded-3xl p-5 sm:p-6 border border-white/[0.1] flex flex-col">
                <div class="flex items-center justify-between pb-3 border-b border-white/[0.08] mb-3">
                    <h3 class="text-xs sm:text-sm font-black uppercase tracking-wider text-slate-300 flex items-center gap-2">
                        <i data-lucide="history" class="w-4 h-4 text-emerald-400"></i>
                        <span>Sudah Selesai Tampil</span>
                    </h3>
                    <span class="text-[11px] font-bold text-emerald-400 font-mono" x-text="completed.length + ' Peserta'">0 Peserta</span>
                </div>

                <div class="flex-1 overflow-y-auto space-y-2.5 max-h-[380px] pr-1 scrollbar-thin scrollbar-thumb-slate-800">
                    <template x-for="(item, idx) in completed" :key="item.id">
                        <div class="p-3 rounded-2xl bg-white/[0.03] hover:bg-white/[0.06] border border-white/[0.06] transition flex items-center justify-between gap-3">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <div class="w-8 h-8 rounded-xl bg-emerald-500/20 border border-emerald-500/30 text-emerald-400 font-black text-xs flex items-center justify-center shrink-0">
                                    <span x-text="item.draw_number || (idx + 1)"></span>
                                </div>
                                <div class="min-w-0">
                                    <h5 class="text-xs font-bold text-white truncate" x-text="item.name"></h5>
                                    <p class="text-[10px] text-slate-400 truncate" x-text="item.institution"></p>
                                </div>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="text-[10px] font-black text-emerald-400 px-2 py-0.5 rounded-md bg-emerald-500/10 border border-emerald-500/20 font-mono"
                                      x-text="'✓ ' + item.formatted_duration"></span>
                            </div>
                        </div>
                    </template>

                    <template x-if="completed.length === 0">
                        <div class="py-10 text-center text-slate-600 text-xs">
                            Belum ada peserta yang menyelesaikan penampilan.
                        </div>
                    </template>
                </div>
            </div>

        </div>
    </main>

    <!-- FOOTER INFO BAR -->
    <footer class="h-10 px-6 flex items-center justify-between text-[11px] text-slate-500 border-t border-white/[0.06] bg-[#050811]/90">
        <span class="truncate">
            {{ $appSettings['institution_name'] ?? 'MTsN 1 Blitar' }} • {{ $appSettings['event_name'] ?? ($appSettings['app_name'] ?? 'TALENTA') }}
        </span>
        <span class="font-mono text-slate-400" x-text="clockTime">--:--:--</span>
    </footer>

    <!-- JAVASCRIPT LOGIC & WEB AUDIO API -->
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
                        this.playChimeSound('bell');
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