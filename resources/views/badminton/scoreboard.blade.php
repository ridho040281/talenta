<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PAPAN SKOR LED BULU TANGKIS - {{ $appSettings['app_name'] ?? 'TALENTA' }}</title>
    
    <!-- Favicon -->
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
        
        body {
            background-color: #060913;
            background-image: 
                radial-gradient(at 50% 10%, rgba(78, 110, 255, 0.25) 0px, transparent 60%),
                radial-gradient(at 90% 40%, rgba(245, 158, 11, 0.15) 0px, transparent 50%),
                radial-gradient(at 10% 40%, rgba(6, 214, 160, 0.15) 0px, transparent 50%),
                radial-gradient(at 50% 90%, rgba(122, 90, 248, 0.20) 0px, transparent 60%),
                linear-gradient(180deg, #090E1C 0%, #04060C 100%);
            background-attachment: fixed;
        }

        .font-led { font-family: 'Orbitron', monospace, sans-serif; }
        .font-player { font-family: 'Rajdhani', 'Chakra Petch', sans-serif; }

        /* Neon Glow Effects */
        .glow-cyan {
            text-shadow: 0 0 15px rgba(6, 214, 160, 0.9), 0 0 30px rgba(6, 214, 160, 0.6);
        }
        .glow-lime {
            text-shadow: 0 0 15px rgba(163, 230, 53, 0.9), 0 0 30px rgba(163, 230, 53, 0.6);
        }
        .glow-amber {
            text-shadow: 0 0 15px rgba(251, 191, 36, 0.9);
        }

        /* LED Sub-pixel Grid Simulation & Titanium Border */
        .led-panel {
            background-color: #030712;
            background-image: 
                radial-gradient(rgba(255, 255, 255, 0.08) 1.5px, transparent 0),
                linear-gradient(180deg, rgba(17, 24, 39, 0.85) 0%, rgba(3, 7, 18, 0.98) 100%);
            background-size: 6px 6px, 100% 100%;
            box-shadow: 
                0 30px 80px -10px rgba(0, 0, 0, 0.95),
                0 0 60px rgba(78, 110, 255, 0.15),
                inset 0 0 60px rgba(0, 0, 0, 0.9);
            border: 2px solid rgba(255, 255, 255, 0.14);
        }

        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="h-screen w-screen overflow-hidden text-slate-100 font-sans antialiased flex flex-col justify-between select-none" x-data="liveScoreboardApp()">

    <!-- TOP CONTROL BAR (COMPACT HEADER) -->
    <header class="h-10 sm:h-12 shrink-0 bg-slate-950/80 border-b border-white/[0.08] px-4 flex items-center justify-between text-xs backdrop-blur-xl z-20">
        <div class="flex items-center gap-3">
            <a href="{{ route('badminton.index') }}" class="flex items-center gap-2 text-slate-400 hover:text-white transition">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span class="font-bold">Kembali</span>
            </a>
            <div class="h-4 w-[1px] bg-white/[0.1]"></div>
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-rose-500 animate-ping"></span>
                <span class="font-extrabold tracking-wider text-rose-400 uppercase text-[11px] sm:text-xs">LIVE SCOREBOARD</span>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <!-- Court Selector -->
            @if(isset($allMatches) && $allMatches->isNotEmpty())
            <select onchange="window.location.href='/badminton/scoreboard/' + this.value" class="bg-[#0C111D] text-amber-300 font-bold border border-white/[0.12] rounded-xl px-2.5 py-1 text-xs focus:ring-2 focus:ring-amber-400 outline-none">
                @foreach($allMatches as $m)
                    <option value="{{ $m->id }}" {{ $match && $match->id == $m->id ? 'selected' : '' }}>
                        {{ $m->court_number }} ({{ $m->category }}) - {{ $m->team1_school }} vs {{ $m->team2_school }}
                    </option>
                @endforeach
            </select>
            @endif

            <a href="{{ route('badminton.arena') }}" class="px-3 py-1 rounded-xl bg-white/[0.06] hover:bg-white/[0.12] text-amber-300 hover:text-white text-xs font-bold border border-white/[0.1] flex items-center gap-1.5 transition">
                <i data-lucide="layout-grid" class="w-3.5 h-3.5 text-emerald-400"></i>
                <span class="hidden sm:inline">Mode Multi-Lapangan</span>
            </a>

            <button @click="toggleFullscreen()" class="p-1.5 rounded-xl bg-white/[0.06] hover:bg-white/[0.12] text-slate-300 hover:text-white border border-white/[0.1] transition cursor-pointer" title="Toggle Fullscreen">
                <i data-lucide="maximize" class="w-3.5 h-3.5"></i>
            </button>
        </div>
    </header>

    @if(!$match)
    <div class="flex-1 flex flex-col items-center justify-center text-center p-6 text-slate-500">
        <i data-lucide="tv" class="w-16 h-16 mb-4 text-slate-700"></i>
        <h2 class="text-xl font-bold text-slate-300">Tidak ada pertandingan aktif</h2>
        <p class="text-xs text-slate-500 mt-1">Silakan pilih atau buat pertandingan bulu tangkis di dashboard.</p>
    </div>
    @else
    <!-- MAIN DISPLAY CONTAINER (ZERO-SCROLL VIEWPORT RESPONSIVE) -->
    <main class="flex-1 w-full max-w-[98vw] 2xl:max-w-[96vw] mx-auto p-2 sm:p-3 flex items-center justify-center min-h-0 overflow-hidden">
        
        <!-- THE AUTHENTIC LED DISPLAY PANEL -->
        <div class="led-panel rounded-2xl sm:rounded-3xl p-3 sm:p-5 lg:p-6 w-full h-full max-h-full shadow-2xl relative overflow-hidden flex flex-col justify-between border-2 border-white/[0.14]">
            
            <!-- HEADER MATCH INFO -->
            <div class="flex justify-between items-center pb-2 border-b border-white/[0.08] text-xs sm:text-sm shrink-0">
                <div class="flex items-center gap-2 sm:gap-4">
                    <span class="font-led font-black bg-[#0C111D] text-amber-400 border border-amber-400/50 px-3 py-1 rounded-xl text-sm sm:text-base lg:text-lg shadow-md" x-text="match.category"></span>
                    <div>
                        <span class="font-black text-white uppercase tracking-wider text-xs sm:text-base lg:text-lg block" x-text="match.court_number + ' • ' + match.round_name"></span>
                        <span class="text-[10px] sm:text-xs text-neutral-400 block font-semibold uppercase tracking-widest mt-0.5">{{ $appSettings['app_name'] ?? 'TALENTA' }} {{ $appSettings['institution_name'] ?? 'MTsN 1 BLITAR' }}</span>
                    </div>
                </div>

                <div class="flex items-center gap-1.5 font-led text-neutral-300">
                    <span class="text-amber-400 font-black text-sm sm:text-xl lg:text-2xl">'<span x-text="matchTimer">24</span></span>
                    <span class="text-[10px] sm:text-xs text-neutral-500 font-bold">MIN</span>
                </div>
            </div>

            <!-- SCOREBOARD GRID -->
            <div class="grid grid-cols-12 gap-2 sm:gap-4 lg:gap-6 items-center flex-1 my-auto min-h-0 py-1">
                
                <!-- ================= TIM 1 (ATAS) ================= -->
                <div class="col-span-8 sm:col-span-7 space-y-1 sm:space-y-2">
                    <!-- Asal Sekolah -->
                    <div class="flex items-center gap-2 text-xs sm:text-sm lg:text-base font-extrabold text-amber-400 tracking-widest">
                        <span class="text-xs sm:text-sm">🏫</span>
                        <span class="uppercase font-player tracking-widest text-amber-300 truncate" x-text="match.team1_school"></span>
                    </div>

                    <!-- Pemain 1 -->
                    <div class="flex items-center gap-2">
                        <div :class="isServing(1, 1) ? 'bg-amber-400 text-black shadow-xl ring-2 sm:ring-4 ring-amber-300/80 font-black' : 'text-neutral-100'" class="px-3 sm:px-4 py-1.5 sm:py-2.5 rounded-xl transition-all duration-200 flex-1 truncate font-player font-black tracking-wide uppercase text-lg sm:text-2xl md:text-3xl lg:text-4xl shadow-inner flex items-center justify-between">
                            <span class="truncate" x-text="match.team1_player1"></span>
                            <template x-if="isServing(1, 1)">
                                <div class="flex items-center gap-1 shrink-0 bg-black/20 px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-lg border border-black/30 animate-pulse ml-2">
                                    <span class="text-sm sm:text-lg">🏸</span>
                                    <span class="font-led font-black text-[10px] sm:text-xs lg:text-sm text-neutral-900 tracking-wider hidden md:inline">SERVE</span>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Pemain 2 (Jika Ganda) -->
                    <template x-if="match.match_type === 'double' && match.team1_player2">
                        <div class="flex items-center gap-2 pt-0.5">
                            <div :class="isServing(1, 2) ? 'bg-amber-400 text-black shadow-xl ring-2 sm:ring-4 ring-amber-300/80 font-black' : 'text-neutral-100'" class="px-3 sm:px-4 py-1.5 sm:py-2.5 rounded-xl transition-all duration-200 flex-1 truncate font-player font-black tracking-wide uppercase text-lg sm:text-2xl md:text-3xl lg:text-4xl shadow-inner flex items-center justify-between">
                                <span class="truncate" x-text="match.team1_player2"></span>
                                <template x-if="isServing(1, 2)">
                                    <div class="flex items-center gap-1 shrink-0 bg-black/20 px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-lg border border-black/30 animate-pulse ml-2">
                                        <span class="text-sm sm:text-lg">🏸</span>
                                        <span class="font-led font-black text-[10px] sm:text-xs lg:text-sm text-neutral-900 tracking-wider hidden md:inline">SERVE</span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- SKOR TIM 1 -->
                <div class="col-span-4 sm:col-span-5 grid grid-cols-3 gap-1.5 sm:gap-3 text-center font-led items-center">
                    <!-- Set 1 -->
                    <div class="border-2 rounded-xl sm:rounded-2xl py-1 sm:py-2.5 lg:py-3 flex items-center justify-center transition-all h-[11vh] sm:h-[13vh] lg:h-[15vh] max-h-[120px]" :class="isServerScoreBox(1, 1) ? 'bg-amber-400 border-amber-300 shadow-[0_0_25px_rgba(251,191,36,0.6)]' : 'bg-[#060A14]/90 border-white/[0.1] shadow-inner'">
                        <span class="text-3xl sm:text-5xl md:text-6xl lg:text-7xl font-black leading-none" :class="isServerScoreBox(1, 1) ? 'text-black' : 'text-lime-400 glow-lime'" x-text="match.team1_set1"></span>
                    </div>
                    <!-- Set 2 -->
                    <div class="border-2 rounded-xl sm:rounded-2xl py-1 sm:py-2.5 lg:py-3 flex items-center justify-center transition-all h-[11vh] sm:h-[13vh] lg:h-[15vh] max-h-[120px]" :class="isServerScoreBox(1, 2) ? 'bg-amber-400 border-amber-300 shadow-[0_0_25px_rgba(251,191,36,0.6)]' : 'bg-[#060A14]/90 border-white/[0.1] shadow-inner'">
                        <span class="text-3xl sm:text-5xl md:text-6xl lg:text-7xl font-black leading-none" :class="isServerScoreBox(1, 2) ? 'text-black' : 'text-cyan-400 glow-cyan'" x-text="match.team1_set2"></span>
                    </div>
                    <!-- Set 3 -->
                    <div class="border-2 rounded-xl sm:rounded-2xl py-1 sm:py-2.5 lg:py-3 flex items-center justify-center transition-all h-[11vh] sm:h-[13vh] lg:h-[15vh] max-h-[120px]" :class="isServerScoreBox(1, 3) ? 'bg-amber-400 border-amber-300 shadow-[0_0_25px_rgba(251,191,36,0.6)]' : 'bg-[#060A14]/90 border-white/[0.1] shadow-inner'">
                        <span class="text-3xl sm:text-5xl md:text-6xl lg:text-7xl font-black leading-none" :class="isServerScoreBox(1, 3) ? 'text-black' : (match.current_set >= 3 ? 'text-cyan-400 glow-cyan' : 'text-neutral-700')" x-text="match.current_set >= 3 ? match.team1_set3 : '-'"></span>
                    </div>
                </div>

                <!-- PEMBATAS TENGAH & STATUS GAME -->
                <div class="col-span-12 flex items-center gap-3 my-1 sm:my-2 shrink-0">
                    <div class="h-[1.5px] bg-gradient-to-r from-transparent via-white/[0.15] to-transparent flex-1"></div>
                    <span class="text-[10px] sm:text-xs lg:text-sm font-led tracking-widest uppercase font-bold px-4 py-1 rounded-full border shadow-lg transition-all" :class="match.match_status == 'interval' ? 'bg-cyan-950/90 text-cyan-400 border-cyan-500 animate-pulse shadow-cyan-500/20' : (match.match_status == 'finished' ? 'bg-emerald-950/90 text-emerald-400 border-emerald-500 shadow-emerald-500/20' : 'bg-[#0C111D] text-amber-400 border-amber-400/40 shadow-amber-400/10')" x-text="getMatchStatusLabel()"></span>
                    <div class="h-[1.5px] bg-gradient-to-r from-transparent via-white/[0.15] to-transparent flex-1"></div>
                </div>

                <!-- ================= TIM 2 (BAWAH) ================= -->
                <div class="col-span-8 sm:col-span-7 space-y-1 sm:space-y-2">
                    <!-- Asal Sekolah -->
                    <div class="flex items-center gap-2 text-xs sm:text-sm lg:text-base font-extrabold text-cyan-400 tracking-widest">
                        <span class="text-xs sm:text-sm">🏫</span>
                        <span class="uppercase font-player tracking-widest text-cyan-300 truncate" x-text="match.team2_school"></span>
                    </div>

                    <!-- Pemain 1 -->
                    <div class="flex items-center gap-2">
                        <div :class="isServing(2, 1) ? 'bg-amber-400 text-black shadow-xl ring-2 sm:ring-4 ring-amber-300/80 font-black' : 'text-neutral-100'" class="px-3 sm:px-4 py-1.5 sm:py-2.5 rounded-xl transition-all duration-200 flex-1 truncate font-player font-black tracking-wide uppercase text-lg sm:text-2xl md:text-3xl lg:text-4xl shadow-inner flex items-center justify-between">
                            <span class="truncate" x-text="match.team2_player1"></span>
                            <template x-if="isServing(2, 1)">
                                <div class="flex items-center gap-1 shrink-0 bg-black/20 px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-lg border border-black/30 animate-pulse ml-2">
                                    <span class="text-sm sm:text-lg">🏸</span>
                                    <span class="font-led font-black text-[10px] sm:text-xs lg:text-sm text-neutral-900 tracking-wider hidden md:inline">SERVE</span>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Pemain 2 (Jika Ganda) -->
                    <template x-if="match.match_type === 'double' && match.team2_player2">
                        <div class="flex items-center gap-2 pt-0.5">
                            <div :class="isServing(2, 2) ? 'bg-amber-400 text-black shadow-xl ring-2 sm:ring-4 ring-amber-300/80 font-black' : 'text-neutral-100'" class="px-3 sm:px-4 py-1.5 sm:py-2.5 rounded-xl transition-all duration-200 flex-1 truncate font-player font-black tracking-wide uppercase text-lg sm:text-2xl md:text-3xl lg:text-4xl shadow-inner flex items-center justify-between">
                                <span class="truncate" x-text="match.team2_player2"></span>
                                <template x-if="isServing(2, 2)">
                                    <div class="flex items-center gap-1 shrink-0 bg-black/20 px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-lg border border-black/30 animate-pulse ml-2">
                                        <span class="text-sm sm:text-lg">🏸</span>
                                        <span class="font-led font-black text-[10px] sm:text-xs lg:text-sm text-neutral-900 tracking-wider hidden md:inline">SERVE</span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- SKOR TIM 2 -->
                <div class="col-span-4 sm:col-span-5 grid grid-cols-3 gap-1.5 sm:gap-3 text-center font-led items-center">
                    <!-- Set 1 -->
                    <div class="border-2 rounded-xl sm:rounded-2xl py-1 sm:py-2.5 lg:py-3 flex items-center justify-center transition-all h-[11vh] sm:h-[13vh] lg:h-[15vh] max-h-[120px]" :class="isServerScoreBox(2, 1) ? 'bg-amber-400 border-amber-300 shadow-[0_0_25px_rgba(251,191,36,0.6)]' : 'bg-[#060A14]/90 border-white/[0.1] shadow-inner'">
                        <span class="text-3xl sm:text-5xl md:text-6xl lg:text-7xl font-black leading-none" :class="isServerScoreBox(2, 1) ? 'text-black' : 'text-lime-400 glow-lime'" x-text="match.team2_set1"></span>
                    </div>
                    <!-- Set 2 -->
                    <div class="border-2 rounded-xl sm:rounded-2xl py-1 sm:py-2.5 lg:py-3 flex items-center justify-center transition-all h-[11vh] sm:h-[13vh] lg:h-[15vh] max-h-[120px]" :class="isServerScoreBox(2, 2) ? 'bg-amber-400 border-amber-300 shadow-[0_0_25px_rgba(251,191,36,0.6)]' : 'bg-[#060A14]/90 border-white/[0.1] shadow-inner'">
                        <span class="text-3xl sm:text-5xl md:text-6xl lg:text-7xl font-black leading-none" :class="isServerScoreBox(2, 2) ? 'text-black' : 'text-cyan-400 glow-cyan'" x-text="match.team2_set2"></span>
                    </div>
                    <!-- Set 3 -->
                    <div class="border-2 rounded-xl sm:rounded-2xl py-1 sm:py-2.5 lg:py-3 flex items-center justify-center transition-all h-[11vh] sm:h-[13vh] lg:h-[15vh] max-h-[120px]" :class="isServerScoreBox(2, 3) ? 'bg-amber-400 border-amber-300 shadow-[0_0_25px_rgba(251,191,36,0.6)]' : 'bg-[#060A14]/90 border-white/[0.1] shadow-inner'">
                        <span class="text-3xl sm:text-5xl md:text-6xl lg:text-7xl font-black leading-none" :class="isServerScoreBox(2, 3) ? 'text-black' : (match.current_set >= 3 ? 'text-cyan-400 glow-cyan' : 'text-neutral-700')" x-text="match.current_set >= 3 ? match.team2_set3 : '-'"></span>
                    </div>
                </div>

            </div>

            <!-- FOOTER LED -->
            <div class="pt-2 border-t border-white/[0.08] flex justify-between items-center text-[10px] sm:text-xs text-neutral-400 shrink-0">
                <div class="flex items-center gap-2">
                    <span class="inline-block w-3 h-3 bg-amber-400 rounded-sm shadow-[0_0_8px_rgba(251,191,36,0.8)]"></span>
                    <span>Kotak Kuning = <strong>Server Aktif (Pegang Servis)</strong></span>
                </div>
                <div class="font-mono text-neutral-500 font-bold uppercase tracking-wider text-[10px] sm:text-xs">
                    TALENTA DIGITAL ARENA • OFFICIAL SYSTEM
                </div>
            </div>

        </div>

    </main>
    @endif

    <!-- JAVASCRIPT LIVE STATE & AUTO SYNC -->
    <script>
        function liveScoreboardApp() {
            return {
                match: @json($match),
                matchTimer: 18,
                isSyncing: false,
                lastDataHash: '',

                init() {
                    lucide.createIcons();
                    if (this.match) {
                        this.startUltraFastSync();
                    }
                },

                startUltraFastSync() {
                    // Instant initial fetch
                    this.fetchLatestScore();

                    // Ultra-fast 200ms non-blocking polling (5x per second)
                    // Zero PHP thread hold, instant < 0.2s sync from referee phone
                    setInterval(() => {
                        this.fetchLatestScore();
                    }, 200);
                },

                isServing(team, player) {
                    if (!this.match) return false;
                    return this.match.server_team == team && this.match.server_player == player;
                },

                isServerScoreBox(team, set) {
                    if (!this.match) return false;
                    return this.match.server_team == team && this.match.current_set == set && this.match.match_status != 'finished';
                },

                getCurrentScore(team) {
                    if (!this.match) return 0;
                    const set = this.match.current_set;
                    if (team === 1) {
                        if (set === 1) return this.match.team1_set1;
                        if (set === 2) return this.match.team1_set2;
                        return this.match.team1_set3;
                    } else {
                        if (set === 1) return this.match.team2_set1;
                        if (set === 2) return this.match.team2_set2;
                        return this.match.team2_set3;
                    }
                },

                getMatchStatusLabel() {
                    if (!this.match) return '';
                    if (this.match.match_status === 'interval') return 'INTERVAL (11 POIN)';
                    if (this.match.match_status === 'finished') {
                        const winner = this.match.winner_team == 1 ? this.match.team1_school : this.match.team2_school;
                        return 'MATCH FINISHED • WINNER: ' + winner;
                    }
                    const s1 = this.getCurrentScore(1);
                    const s2 = this.getCurrentScore(2);
                    if (s1 >= 20 && s2 >= 20) {
                        return `🔥 SETTING / DEUCE (${s1} - ${s2}) • GAME ${this.match.current_set}`;
                    }
                    if (s1 >= 20 || s2 >= 20) {
                        return `⚡ GAME POINT (${s1} - ${s2}) • GAME ${this.match.current_set}`;
                    }
                    return 'GAME ' + this.match.current_set + ' IN PROGRESS';
                },

                async fetchLatestScore() {
                    if (!this.match || this.isSyncing) return;
                    this.isSyncing = true;
                    try {
                        const res = await fetch(`{{ url('/badminton/matches') }}/${this.match.id}/state?_t=${Date.now()}`, {
                            headers: { 'Cache-Control': 'no-cache', 'Pragma': 'no-cache' }
                        });
                        if (res.ok) {
                            const data = await res.json();
                            const hash = `${data.current_set}-${data.team1_set1}-${data.team2_set1}-${data.team1_set2}-${data.team2_set2}-${data.team1_set3}-${data.team2_set3}-${data.server_team}-${data.server_player}-${data.match_status}-${data.team1_player1}-${data.team2_player1}`;
                            if (this.lastDataHash !== hash) {
                                this.lastDataHash = hash;
                                this.match = data;
                            }
                        }
                    } catch (err) {
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