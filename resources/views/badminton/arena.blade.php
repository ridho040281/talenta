<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ARENA MULTI-LAPANGAN - {{ $appSettings['app_name'] ?? 'TALENTA' }}</title>
    
    @if(!empty($appSettings['favicon']))
        <link rel="icon" type="image/png" href="{{ asset('storage/' . $appSettings['favicon']) }}">
    @endif

    <!-- Vite Local Tailwind CSS & JS Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- Alpine.js & Lucide Icons -->
    <script src="https://cdn.jsdelivr.net/npm/lucide@0.344.0/dist/umd/lucide.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Chakra+Petch:wght@500;700;800;900&family=Orbitron:wght@700;900&family=Rajdhani:wght@600;700;800;900&display=swap');
        
        .font-led { font-family: 'Orbitron', monospace, sans-serif; }
        .font-player { font-family: 'Rajdhani', 'Chakra Petch', sans-serif; }

        .glow-cyan { text-shadow: 0 0 10px rgba(6, 214, 160, 0.8), 0 0 20px rgba(6, 214, 160, 0.5); }
        .glow-lime { text-shadow: 0 0 10px rgba(163, 230, 53, 0.8), 0 0 20px rgba(163, 230, 53, 0.5); }
        
        .led-panel {
            background-color: #020608;
            background-image: radial-gradient(rgba(255, 255, 255, 0.05) 1px, transparent 0);
            background-size: 4px 4px;
            box-shadow: inset 0 0 40px rgba(0, 0, 0, 0.95), 0 15px 30px -10px rgba(0, 0, 0, 0.9);
        }

        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-black text-slate-100 font-sans antialiased min-h-screen flex flex-col justify-between select-none" x-data="arenaMultiCourtApp()">

    <!-- TOP HEADER -->
    <header class="bg-slate-950/90 border-b border-slate-900 px-4 py-2.5 flex items-center justify-between text-xs backdrop-blur-md sticky top-0 z-50">
        <div class="flex items-center gap-3">
            <a href="{{ route('badminton.index') }}" class="flex items-center gap-2 text-slate-400 hover:text-white transition">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span class="font-bold">Kembali</span>
            </a>
            <div class="h-4 w-[1px] bg-slate-800"></div>
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-ping"></span>
                <span class="font-extrabold tracking-wider text-emerald-400 uppercase">ARENA MULTI-LAPANGAN (SIMULTAN)</span>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('badminton.scoreboard') }}" class="px-3 py-1 rounded-lg bg-slate-900 hover:bg-slate-800 text-slate-300 hover:text-white text-xs font-bold border border-slate-800 flex items-center gap-1.5 transition">
                <i data-lucide="monitor" class="w-4 h-4 text-amber-400"></i>
                <span>Mode 1 Lapangan Fokus</span>
            </a>
            <button @click="toggleFullscreen()" class="p-1.5 rounded-lg bg-slate-900 hover:bg-slate-800 text-slate-300 transition" title="Toggle Fullscreen">
                <i data-lucide="maximize" class="w-4 h-4"></i>
            </button>
        </div>
    </header>

    <!-- MULTI-COURT GRID (2 COLUMNS FOR 2 COURTS, AUTO RESPONSIVE) -->
    <main class="flex-1 p-3 sm:p-5 lg:p-6 w-full max-w-[1920px] mx-auto flex items-center">
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6 w-full">
            
            <template x-for="(courtName, index) in Object.keys(courtMatches)" :key="courtName">
                <div class="led-panel border-2 sm:border-4 border-neutral-900 rounded-2xl p-4 sm:p-6 shadow-2xl flex flex-col justify-between relative overflow-hidden">
                    
                    <!-- Top Match Info -->
                    <div>
                        <div class="flex justify-between items-center pb-2.5 border-b-2 border-neutral-800/80 mb-3 text-xs tracking-wider">
                            <div class="flex items-center gap-2 sm:gap-3">
                                <span class="font-led font-black bg-amber-400 text-black px-2.5 py-0.5 rounded text-xs sm:text-sm uppercase tracking-wider" x-text="courtName"></span>
                                <span class="font-led font-black bg-neutral-900 text-amber-400 border border-amber-400/40 px-2 py-0.5 rounded text-xs" x-text="courtMatches[courtName].category"></span>
                                <span class="font-bold text-neutral-300 uppercase tracking-wider text-xs truncate max-w-[180px] sm:max-w-none" x-text="courtMatches[courtName].round_name"></span>
                            </div>

                            <div class="flex items-center gap-2">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-led font-extrabold uppercase" :class="courtMatches[courtName].match_status == 'ongoing' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/40' : (courtMatches[courtName].match_status == 'interval' ? 'bg-cyan-500/20 text-cyan-400 border border-cyan-500/40 animate-pulse' : 'bg-slate-800 text-slate-400 border border-slate-700')" x-text="courtMatches[courtName].match_status == 'ongoing' ? 'LIVE (SET ' + courtMatches[courtName].current_set + ')' : (courtMatches[courtName].match_status == 'interval' ? 'INTERVAL' : 'SELESAI')"></span>
                            </div>
                        </div>

                        <!-- SCOREBOARD GRID FOR THIS COURT -->
                        <div class="grid grid-cols-12 gap-2 sm:gap-4 items-center">
                            
                            <!-- TIM 1 (ATAS) -->
                            <div class="col-span-8 space-y-1">
                                <span class="text-[11px] sm:text-xs font-extrabold text-amber-400 tracking-wider block truncate uppercase font-player">
                                    🏫 <span x-text="courtMatches[courtName].team1_school"></span>
                                </span>
                                <div class="flex items-center gap-2">
                                    <div :class="courtMatches[courtName].server_team == 1 && courtMatches[courtName].server_player == 1 ? 'bg-amber-400 text-black shadow-md ring-2 ring-amber-300' : 'text-neutral-200'" class="px-2.5 py-0.5 rounded-md transition-all truncate font-player font-bold uppercase text-sm sm:text-lg lg:text-xl flex-1 flex items-center justify-between">
                                        <span class="truncate" x-text="courtMatches[courtName].team1_player1"></span>
                                        <template x-if="courtMatches[courtName].server_team == 1 && courtMatches[courtName].server_player == 1">
                                            <div class="flex items-center gap-1 shrink-0 bg-black/20 px-1.5 py-0.5 rounded text-[10px] animate-pulse ml-1">
                                                <span>🏸</span>
                                                <span class="font-led font-black text-[9px] sm:text-[10px] text-neutral-900 tracking-wider hidden sm:inline">SERVE</span>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                                <template x-if="courtMatches[courtName].match_type === 'double' && courtMatches[courtName].team1_player2">
                                    <div class="flex items-center gap-2 pt-0.5">
                                        <div :class="courtMatches[courtName].server_team == 1 && courtMatches[courtName].server_player == 2 ? 'bg-amber-400 text-black shadow-md ring-2 ring-amber-300' : 'text-neutral-200'" class="px-2.5 py-0.5 rounded-md transition-all truncate font-player font-bold uppercase text-sm sm:text-lg lg:text-xl flex-1 flex items-center justify-between">
                                            <span class="truncate" x-text="courtMatches[courtName].team1_player2"></span>
                                            <template x-if="courtMatches[courtName].server_team == 1 && courtMatches[courtName].server_player == 2">
                                                <div class="flex items-center gap-1 shrink-0 bg-black/20 px-1.5 py-0.5 rounded text-[10px] animate-pulse ml-1">
                                                    <span>🏸</span>
                                                    <span class="font-led font-black text-[9px] sm:text-[10px] text-neutral-900 tracking-wider hidden sm:inline">SERVE</span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <!-- SKOR TIM 1 -->
                            <div class="col-span-4 grid grid-cols-3 gap-1 sm:gap-2 text-center font-led">
                                <div class="border rounded-lg py-1.5 sm:py-2.5 flex items-center justify-center" :class="isServerScoreBox(courtMatches[courtName], 1, 1) ? 'bg-amber-400 border-amber-300 shadow-md' : 'bg-neutral-950/90 border-neutral-800'">
                                    <span class="text-lg sm:text-2xl lg:text-3xl font-black" :class="isServerScoreBox(courtMatches[courtName], 1, 1) ? 'text-black' : 'text-lime-400 glow-lime'" x-text="courtMatches[courtName].team1_set1"></span>
                                </div>
                                <div class="border rounded-lg py-1.5 sm:py-2.5 flex items-center justify-center" :class="isServerScoreBox(courtMatches[courtName], 1, 2) ? 'bg-amber-400 border-amber-300 shadow-md' : 'bg-neutral-950/90 border-neutral-800'">
                                    <span class="text-lg sm:text-2xl lg:text-3xl font-black" :class="isServerScoreBox(courtMatches[courtName], 1, 2) ? 'text-black' : 'text-cyan-400 glow-cyan'" x-text="courtMatches[courtName].team1_set2"></span>
                                </div>
                                <div class="border rounded-lg py-1.5 sm:py-2.5 flex items-center justify-center" :class="isServerScoreBox(courtMatches[courtName], 1, 3) ? 'bg-amber-400 border-amber-300 shadow-md' : 'bg-neutral-950/90 border-neutral-800'">
                                    <span class="text-lg sm:text-2xl lg:text-3xl font-black" :class="isServerScoreBox(courtMatches[courtName], 1, 3) ? 'text-black' : (courtMatches[courtName].current_set >= 3 ? 'text-cyan-400 glow-cyan' : 'text-neutral-700')" x-text="courtMatches[courtName].current_set >= 3 ? courtMatches[courtName].team1_set3 : '-'"></span>
                                </div>
                            </div>

                            <!-- DIVIDER -->
                            <div class="col-span-12 h-[1px] bg-neutral-800 my-1"></div>

                            <!-- TIM 2 (BAWAH) -->
                            <div class="col-span-8 space-y-1">
                                <span class="text-[11px] sm:text-xs font-extrabold text-cyan-400 tracking-wider block truncate uppercase font-player">
                                    🏫 <span x-text="courtMatches[courtName].team2_school"></span>
                                </span>
                                <div class="flex items-center gap-2">
                                    <div :class="courtMatches[courtName].server_team == 2 && courtMatches[courtName].server_player == 1 ? 'bg-amber-400 text-black shadow-md ring-2 ring-amber-300' : 'text-neutral-200'" class="px-2.5 py-0.5 rounded-md transition-all truncate font-player font-bold uppercase text-sm sm:text-lg lg:text-xl flex-1 flex items-center justify-between">
                                        <span class="truncate" x-text="courtMatches[courtName].team2_player1"></span>
                                        <template x-if="courtMatches[courtName].server_team == 2 && courtMatches[courtName].server_player == 1">
                                            <div class="flex items-center gap-1 shrink-0 bg-black/20 px-1.5 py-0.5 rounded text-[10px] animate-pulse ml-1">
                                                <span>🏸</span>
                                                <span class="font-led font-black text-[9px] sm:text-[10px] text-neutral-900 tracking-wider hidden sm:inline">SERVE</span>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                                <template x-if="courtMatches[courtName].match_type === 'double' && courtMatches[courtName].team2_player2">
                                    <div class="flex items-center gap-2 pt-0.5">
                                        <div :class="courtMatches[courtName].server_team == 2 && courtMatches[courtName].server_player == 2 ? 'bg-amber-400 text-black shadow-md ring-2 ring-amber-300' : 'text-neutral-200'" class="px-2.5 py-0.5 rounded-md transition-all truncate font-player font-bold uppercase text-sm sm:text-lg lg:text-xl flex-1 flex items-center justify-between">
                                            <span class="truncate" x-text="courtMatches[courtName].team2_player2"></span>
                                            <template x-if="courtMatches[courtName].server_team == 2 && courtMatches[courtName].server_player == 2">
                                                <div class="flex items-center gap-1 shrink-0 bg-black/20 px-1.5 py-0.5 rounded text-[10px] animate-pulse ml-1">
                                                    <span>🏸</span>
                                                    <span class="font-led font-black text-[9px] sm:text-[10px] text-neutral-900 tracking-wider hidden sm:inline">SERVE</span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <!-- SKOR TIM 2 -->
                            <div class="col-span-4 grid grid-cols-3 gap-1 sm:gap-2 text-center font-led">
                                <div class="border rounded-lg py-1.5 sm:py-2.5 flex items-center justify-center" :class="isServerScoreBox(courtMatches[courtName], 2, 1) ? 'bg-amber-400 border-amber-300 shadow-md' : 'bg-neutral-950/90 border-neutral-800'">
                                    <span class="text-lg sm:text-2xl lg:text-3xl font-black" :class="isServerScoreBox(courtMatches[courtName], 2, 1) ? 'text-black' : 'text-lime-400 glow-lime'" x-text="courtMatches[courtName].team2_set1"></span>
                                </div>
                                <div class="border rounded-lg py-1.5 sm:py-2.5 flex items-center justify-center" :class="isServerScoreBox(courtMatches[courtName], 2, 2) ? 'bg-amber-400 border-amber-300 shadow-md' : 'bg-neutral-950/90 border-neutral-800'">
                                    <span class="text-lg sm:text-2xl lg:text-3xl font-black" :class="isServerScoreBox(courtMatches[courtName], 2, 2) ? 'text-black' : 'text-cyan-400 glow-cyan'" x-text="courtMatches[courtName].team2_set2"></span>
                                </div>
                                <div class="border rounded-lg py-1.5 sm:py-2.5 flex items-center justify-center" :class="isServerScoreBox(courtMatches[courtName], 2, 3) ? 'bg-amber-400 border-amber-300 shadow-md' : 'bg-neutral-950/90 border-neutral-800'">
                                    <span class="text-lg sm:text-2xl lg:text-3xl font-black" :class="isServerScoreBox(courtMatches[courtName], 2, 3) ? 'text-black' : (courtMatches[courtName].current_set >= 3 ? 'text-cyan-400 glow-cyan' : 'text-neutral-700')" x-text="courtMatches[courtName].current_set >= 3 ? courtMatches[courtName].team2_set3 : '-'"></span>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Card Footer Actions -->
                    <div class="mt-3 pt-2 border-t border-neutral-800 flex justify-between items-center text-[10px] text-neutral-400">
                        <div class="flex items-center gap-1.5">
                            <span class="inline-block w-2 h-2 bg-amber-400 rounded-xs"></span>
                            <span>Kotak Kuning = Server</span>
                        </div>
                        <a :href="'/badminton/scoreboard/' + courtMatches[courtName].id" class="text-amber-400 hover:underline font-bold flex items-center gap-1">
                            <span>Layar Penuh</span>
                            <i data-lucide="external-link" class="w-3 h-3"></i>
                        </a>
                    </div>

                </div>
            </template>

        </div>

    </main>

    <!-- FOOTER ARENA -->
    <footer class="bg-slate-950 border-t border-slate-900 px-4 py-2 text-center text-xs text-slate-500 flex justify-between items-center">
        <span>{{ $appSettings['app_name'] ?? 'TALENTA' }} • {{ $appSettings['institution_name'] ?? 'MTsN 1 BLITAR' }}</span>
        <span class="font-mono text-emerald-400">● REAL-TIME INSTANT PUSH (< 0.05s)</span>
    </footer>

    <!-- JAVASCRIPT STATE APP -->
    <script>
        function arenaMultiCourtApp() {
            return {
                courtMatches: @json($courtMatches),
                isSyncing: false,
                lastDataHash: '',

                init() {
                    lucide.createIcons();
                    this.startUltraFastArenaSync();
                },

                startUltraFastArenaSync() {
                    this.fetchActiveCourts();

                    // Ultra-fast 250ms non-blocking polling (4x per second)
                    setInterval(() => {
                        this.fetchActiveCourts();
                    }, 250);
                },

                isServerScoreBox(match, team, set) {
                    if (!match) return false;
                    return match.server_team == team && match.current_set == set && match.match_status != 'finished';
                },

                async fetchActiveCourts() {
                    if (this.isSyncing) return;
                    this.isSyncing = true;
                    try {
                        const res = await fetch(`{{ url('/badminton/api/active-courts') }}?_t=${Date.now()}`, {
                            headers: { 'Cache-Control': 'no-cache', 'Pragma': 'no-cache' }
                        });
                        if (res.ok) {
                            const data = await res.json();
                            const hash = JSON.stringify(data);
                            if (this.lastDataHash !== hash) {
                                this.lastDataHash = hash;
                                this.courtMatches = data;
                            }
                        }
                    } catch (e) {
                        // Ignore transient network hiccups
                    } finally {
                        this.isSyncing = false;
                    }
                },

                toggleFullscreen() {
                    if (!document.fullscreenElement) {
                        document.documentElement.requestFullscreen();
                    } else {
                        if (document.exitFullscreen) {
                            document.exitFullscreen();
                        }
                    }
                }
            }
        }
    </script>
</body>
</html>