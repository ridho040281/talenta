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
    <!-- Alpine.js & Lucide Icons (Self-hosted Local) -->
    <script defer src="{{ asset('vendor/lucide/lucide.min.js') }}"></script>
    <script defer src="{{ asset('vendor/alpine/alpine.min.js') }}"></script>

    <!-- Self-hosted Fonts (lokal) -->
    <link rel="stylesheet" href="{{ asset('vendor/fonts/fonts.css') }}">

    <style>
        
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
<body class="h-screen w-screen overflow-hidden text-slate-100 font-sans antialiased flex flex-col justify-between select-none relative" x-data="liveScoreboardApp()">

    <!-- CHAMPION CELEBRATION CONFETTI CANVAS -->
    <canvas id="champion-confetti-canvas" class="fixed inset-0 pointer-events-none z-40 w-full h-full" style="display: none;"></canvas>

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
            <!-- Winner Celebration Toggle Button -->
            <button type="button" @click="showWinnerModal = !showWinnerModal" class="px-2.5 py-1 rounded-xl bg-amber-500/20 hover:bg-amber-500/30 text-amber-300 border border-amber-400/50 flex items-center gap-1.5 transition text-xs font-bold cursor-pointer shadow-sm" x-show="match && match.match_status === 'finished'">
                <span>🏆</span>
                <span class="hidden sm:inline">Pemenang</span>
            </button>

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
                        <div :class="isServing(1, 1) ? 'bg-amber-400 text-black shadow-xl ring-2 sm:ring-4 ring-amber-300/80 font-black' : (match.match_status === 'finished' && match.winner_team == 1 ? 'bg-amber-400/20 text-amber-200 ring-2 ring-amber-400/70 shadow-[0_0_20px_rgba(251,191,36,0.3)]' : 'text-neutral-100')" class="px-3 sm:px-4 py-1.5 sm:py-2.5 rounded-xl transition-all duration-200 flex-1 truncate font-player font-black tracking-wide uppercase text-lg sm:text-2xl md:text-3xl lg:text-4xl shadow-inner flex items-center justify-between">
                            <span class="truncate" x-text="match.team1_player1"></span>
                            <div class="flex items-center gap-1.5 shrink-0 ml-2">
                                <template x-if="match.match_status === 'finished' && match.winner_team == 1">
                                    <span class="px-2.5 py-0.5 rounded-full bg-amber-400 text-black text-[10px] sm:text-xs font-black tracking-wider uppercase shadow-md animate-pulse">👑 WINNER</span>
                                </template>
                                <template x-if="isServing(1, 1)">
                                    <div class="flex items-center gap-1 bg-black/20 px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-lg border border-black/30 animate-pulse">
                                        <span class="text-sm sm:text-lg">🏸</span>
                                        <span class="font-led font-black text-[10px] sm:text-xs lg:text-sm text-neutral-900 tracking-wider hidden md:inline">SERVE</span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Pemain 2 (Jika Ganda) -->
                    <template x-if="match.match_type === 'double' && match.team1_player2">
                        <div class="flex items-center gap-2 pt-0.5">
                            <div :class="isServing(1, 2) ? 'bg-amber-400 text-black shadow-xl ring-2 sm:ring-4 ring-amber-300/80 font-black' : (match.match_status === 'finished' && match.winner_team == 1 ? 'bg-amber-400/20 text-amber-200 ring-2 ring-amber-400/70 shadow-[0_0_20px_rgba(251,191,36,0.3)]' : 'text-neutral-100')" class="px-3 sm:px-4 py-1.5 sm:py-2.5 rounded-xl transition-all duration-200 flex-1 truncate font-player font-black tracking-wide uppercase text-lg sm:text-2xl md:text-3xl lg:text-4xl shadow-inner flex items-center justify-between">
                                <span class="truncate" x-text="match.team1_player2"></span>
                                <div class="flex items-center gap-1.5 shrink-0 ml-2">
                                    <template x-if="match.match_status === 'finished' && match.winner_team == 1">
                                        <span class="px-2.5 py-0.5 rounded-full bg-amber-400 text-black text-[10px] sm:text-xs font-black tracking-wider uppercase shadow-md animate-pulse">👑 WINNER</span>
                                    </template>
                                    <template x-if="isServing(1, 2)">
                                        <div class="flex items-center gap-1 bg-black/20 px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-lg border border-black/30 animate-pulse">
                                            <span class="text-sm sm:text-lg">🏸</span>
                                            <span class="font-led font-black text-[10px] sm:text-xs lg:text-sm text-neutral-900 tracking-wider hidden md:inline">SERVE</span>
                                        </div>
                                    </template>
                                </div>
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
                    <template x-if="match.match_status !== 'finished'">
                        <span class="text-[10px] sm:text-xs lg:text-sm font-led tracking-widest uppercase font-bold px-4 py-1 rounded-full border shadow-lg transition-all" :class="match.match_status == 'interval' ? 'bg-cyan-950/90 text-cyan-400 border-cyan-500 animate-pulse shadow-cyan-500/20' : 'bg-[#0C111D] text-amber-400 border-amber-400/40 shadow-amber-400/10'" x-text="getMatchStatusLabel()"></span>
                    </template>
                    <template x-if="match.match_status === 'finished'">
                        <div class="px-4 py-1.5 sm:px-6 sm:py-2 rounded-2xl bg-gradient-to-r from-amber-500/25 via-emerald-500/30 to-amber-500/25 border-2 border-amber-400/90 shadow-[0_0_35px_rgba(251,191,36,0.5)] flex items-center gap-2 sm:gap-3 cursor-pointer select-none transition hover:scale-105" @click="showWinnerModal = true" title="Klik untuk menampilkan popup perayaan pemenang">
                            <span class="text-lg sm:text-2xl animate-bounce">🏆</span>
                            <div class="text-center min-w-0">
                                <span class="text-[9px] sm:text-[10px] font-black tracking-widest uppercase text-amber-300 block">PERTANDINGAN SELESAI • WINNER</span>
                                <div class="text-xs sm:text-base lg:text-lg font-black font-player tracking-wide text-white uppercase glow-amber truncate">
                                    <span x-text="getWinnerInfo() ? getWinnerInfo().athlete : ''"></span>
                                    <span class="text-amber-300 font-extrabold text-[11px] sm:text-sm ml-1" x-text="getWinnerInfo() ? '(' + getWinnerInfo().school + ')' : ''"></span>
                                </div>
                            </div>
                            <span class="text-lg sm:text-2xl animate-bounce">🏆</span>
                        </div>
                    </template>
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
                        <div :class="isServing(2, 1) ? 'bg-amber-400 text-black shadow-xl ring-2 sm:ring-4 ring-amber-300/80 font-black' : (match.match_status === 'finished' && match.winner_team == 2 ? 'bg-cyan-500/20 text-cyan-200 ring-2 ring-cyan-400/70 shadow-[0_0_20px_rgba(6,182,212,0.3)]' : 'text-neutral-100')" class="px-3 sm:px-4 py-1.5 sm:py-2.5 rounded-xl transition-all duration-200 flex-1 truncate font-player font-black tracking-wide uppercase text-lg sm:text-2xl md:text-3xl lg:text-4xl shadow-inner flex items-center justify-between">
                            <span class="truncate" x-text="match.team2_player1"></span>
                            <div class="flex items-center gap-1.5 shrink-0 ml-2">
                                <template x-if="match.match_status === 'finished' && match.winner_team == 2">
                                    <span class="px-2.5 py-0.5 rounded-full bg-cyan-400 text-black text-[10px] sm:text-xs font-black tracking-wider uppercase shadow-md animate-pulse">👑 WINNER</span>
                                </template>
                                <template x-if="isServing(2, 1)">
                                    <div class="flex items-center gap-1 bg-black/20 px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-lg border border-black/30 animate-pulse">
                                        <span class="text-sm sm:text-lg">🏸</span>
                                        <span class="font-led font-black text-[10px] sm:text-xs lg:text-sm text-neutral-900 tracking-wider hidden md:inline">SERVE</span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Pemain 2 (Jika Ganda) -->
                    <template x-if="match.match_type === 'double' && match.team2_player2">
                        <div class="flex items-center gap-2 pt-0.5">
                            <div :class="isServing(2, 2) ? 'bg-amber-400 text-black shadow-xl ring-2 sm:ring-4 ring-amber-300/80 font-black' : (match.match_status === 'finished' && match.winner_team == 2 ? 'bg-cyan-500/20 text-cyan-200 ring-2 ring-cyan-400/70 shadow-[0_0_20px_rgba(6,182,212,0.3)]' : 'text-neutral-100')" class="px-3 sm:px-4 py-1.5 sm:py-2.5 rounded-xl transition-all duration-200 flex-1 truncate font-player font-black tracking-wide uppercase text-lg sm:text-2xl md:text-3xl lg:text-4xl shadow-inner flex items-center justify-between">
                                <span class="truncate" x-text="match.team2_player2"></span>
                                <div class="flex items-center gap-1.5 shrink-0 ml-2">
                                    <template x-if="match.match_status === 'finished' && match.winner_team == 2">
                                        <span class="px-2.5 py-0.5 rounded-full bg-cyan-400 text-black text-[10px] sm:text-xs font-black tracking-wider uppercase shadow-md animate-pulse">👑 WINNER</span>
                                    </template>
                                    <template x-if="isServing(2, 2)">
                                        <div class="flex items-center gap-1 bg-black/20 px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-lg border border-black/30 animate-pulse">
                                            <span class="text-sm sm:text-lg">🏸</span>
                                            <span class="font-led font-black text-[10px] sm:text-xs lg:text-sm text-neutral-900 tracking-wider hidden md:inline">SERVE</span>
                                        </div>
                                    </template>
                                </div>
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

            <!-- INTERVAL COUNTDOWN OVERLAY ON TV SCOREBOARD -->
            <template x-if="isIntervalActive()">
                <div class="absolute inset-0 bg-[#060A14]/95 backdrop-blur-md z-50 flex flex-col items-center justify-center p-6 text-center animate-fade-in border-4 border-amber-400/80 rounded-2xl sm:rounded-3xl">
                    <div class="inline-flex items-center gap-2 px-4 sm:px-6 py-1.5 sm:py-2 rounded-full bg-amber-400/20 text-amber-400 border border-amber-400/50 text-sm sm:text-xl font-black uppercase tracking-widest mb-2 sm:mb-4 animate-pulse shadow-lg">
                        <span>⏱️</span>
                        <span>JEDA INTERVAL (11 POIN / ANTAR-GAME)</span>
                    </div>
                    <div class="text-8xl sm:text-9xl md:text-[11rem] font-black font-led text-amber-400 glow-amber my-2 leading-none" x-text="getIntervalSeconds()">
                        60
                    </div>
                    <p class="text-sm sm:text-2xl text-neutral-200 font-bold max-w-xl mt-3 sm:mt-4">
                        Pemain beristirahat dan menerima instruksi pelatih.
                    </p>
                    <div class="mt-4 flex items-center gap-3 text-xs sm:text-sm text-neutral-400 font-mono">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-400 animate-ping"></span>
                        <span>WASIT SEDANG MEMIMPIN INTERVAL</span>
                    </div>
                </div>
            </template>

            <!-- GRAND CHAMPION CELEBRATION OVERLAY ON TV SCOREBOARD -->
            <template x-if="match && match.match_status === 'finished' && showWinnerModal">
                <div class="absolute inset-0 bg-[#060A14]/94 backdrop-blur-md z-50 flex flex-col items-center justify-center p-6 text-center animate-fade-in border-4 border-amber-400/90 rounded-2xl sm:rounded-3xl shadow-[0_0_80px_rgba(251,191,36,0.4)]">
                    <!-- Grand Trophy Icon & Champion Title -->
                    <div class="inline-flex items-center gap-2 sm:gap-3 px-6 py-2 rounded-full bg-gradient-to-r from-amber-500/30 via-yellow-400/40 to-amber-500/30 text-amber-300 border-2 border-amber-400 text-sm sm:text-2xl font-black uppercase tracking-widest mb-3 sm:mb-4 animate-pulse shadow-lg">
                        <span class="text-xl sm:text-3xl">🏆</span>
                        <span>PERTANDINGAN SELESAI • CHAMPION!</span>
                        <span class="text-xl sm:text-3xl">🏆</span>
                    </div>

                    <!-- Nama Atlet Pemenang (Dahulukan Nama Atlet - Besar & Megah) -->
                    <div class="my-2 max-w-4xl px-4">
                        <span class="text-xs sm:text-sm font-bold text-amber-400 uppercase tracking-widest block">PEMENANG (WINNER)</span>
                        <h1 class="text-3xl sm:text-5xl md:text-6xl lg:text-7xl font-black font-player tracking-wider text-white uppercase glow-amber mt-1" x-text="getWinnerInfo() ? getWinnerInfo().athlete : ''"></h1>
                    </div>

                    <!-- Asal Sekolah (Di Bawah Nama Atlet) -->
                    <div class="mt-2 sm:mt-3 inline-flex items-center gap-2 px-6 py-2 rounded-2xl bg-slate-900/90 border border-amber-400/50 text-amber-300 text-sm sm:text-2xl font-extrabold uppercase tracking-wider shadow-lg">
                        <span>🏫</span>
                        <span x-text="getWinnerInfo() ? getWinnerInfo().school : ''"></span>
                    </div>

                    <!-- Skor Akhir Pertandingan -->
                    <div class="mt-4 sm:mt-5 flex items-center justify-center gap-3 sm:gap-5 text-sm sm:text-xl text-neutral-300 font-mono bg-black/60 px-6 py-2.5 rounded-xl border border-white/10 shadow-inner">
                        <span class="text-neutral-400 font-bold">SKOR AKHIR:</span>
                        <span class="font-black text-lime-400" x-text="`${match.team1_set1} - ${match.team2_set1}`"></span>
                        <span class="text-neutral-600">|</span>
                        <span class="font-black text-cyan-400" x-text="`${match.team1_set2} - ${match.team2_set2}`"></span>
                        <template x-if="match.current_set >= 3">
                            <span class="flex items-center gap-3 sm:gap-5">
                                <span class="text-neutral-600">|</span>
                                <span class="font-black text-amber-400" x-text="`${match.team1_set3} - ${match.team2_set3}`"></span>
                            </span>
                        </template>
                    </div>

                    <!-- Tombol Tutup / Lihat Papan Skor Lengkap -->
                    <button type="button" @click="showWinnerModal = false" class="mt-6 px-6 py-2.5 rounded-xl bg-slate-800/90 hover:bg-slate-700 active:scale-95 text-neutral-300 hover:text-white text-xs sm:text-sm font-bold border border-slate-600 transition flex items-center gap-2 cursor-pointer shadow-lg">
                        <i data-lucide="layout-grid" class="w-4 h-4 text-amber-400"></i>
                        <span>Tampilkan Papan Skor Lengkap</span>
                    </button>
                </div>
            </template>

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

    <!-- CHAMPION CONFETTI ENGINE (PURE JAVASCRIPT - ZERO DEPENDENCIES - 100% OFFLINE) -->
    <script>
        class ChampionConfetti {
            constructor(canvasId) {
                this.canvas = document.getElementById(canvasId);
                this.ctx = this.canvas ? this.canvas.getContext('2d') : null;
                this.particles = [];
                this.animationId = null;
                this.isRunning = false;
                this.resize = this.resize.bind(this);
                window.addEventListener('resize', this.resize);
            }

            resize() {
                if (!this.canvas) return;
                this.canvas.width = window.innerWidth;
                this.canvas.height = window.innerHeight;
            }

            start() {
                if (!this.canvas || !this.ctx || this.isRunning) return;
                this.resize();
                this.canvas.style.display = 'block';
                this.isRunning = true;
                this.particles = [];
                const colors = ['#f59e0b', '#10b981', '#06b6d4', '#ec4899', '#8b5cf6', '#eab308', '#ffffff', '#3b82f6'];

                for (let i = 0; i < 180; i++) {
                    this.particles.push({
                        x: Math.random() * this.canvas.width,
                        y: Math.random() * -this.canvas.height,
                        size: Math.random() * 9 + 4,
                        color: colors[Math.floor(Math.random() * colors.length)],
                        speedY: Math.random() * 3.5 + 2,
                        speedX: Math.random() * 2.5 - 1.25,
                        rotation: Math.random() * 360,
                        rotationSpeed: Math.random() * 8 - 4,
                        wobble: Math.random() * 10
                    });
                }

                const render = () => {
                    if (!this.isRunning) return;
                    this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);

                    this.particles.forEach(p => {
                        p.y += p.speedY;
                        p.wobble += 0.05;
                        p.x += Math.sin(p.wobble) * 1.5 + p.speedX;
                        p.rotation += p.rotationSpeed;

                        if (p.y > this.canvas.height + 20) {
                            p.y = -20;
                            p.x = Math.random() * this.canvas.width;
                        }

                        this.ctx.save();
                        this.ctx.translate(p.x, p.y);
                        this.ctx.rotate((p.rotation * Math.PI) / 180);
                        this.ctx.fillStyle = p.color;
                        this.ctx.fillRect(-p.size / 2, -p.size / 4, p.size, p.size * 0.6);
                        this.ctx.restore();
                    });

                    this.animationId = requestAnimationFrame(render);
                };

                render();
            }

            stop() {
                this.isRunning = false;
                if (this.animationId) {
                    cancelAnimationFrame(this.animationId);
                    this.animationId = null;
                }
                if (this.canvas && this.ctx) {
                    this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
                    this.canvas.style.display = 'none';
                }
            }
        }

        function liveScoreboardApp() {
            return {
                match: @json($match),
                matchTimer: 18,
                isSyncing: false,
                lastDataHash: '',
                intervalRemaining: 0,
                intervalTimerId: null,
                showWinnerModal: true,
                confettiEngine: null,

                init() {
                    lucide.createIcons();
                    this.confettiEngine = new ChampionConfetti('champion-confetti-canvas');
                    if (this.match) {
                        this.syncIntervalTimer(this.match);
                        if (this.match.match_status === 'finished') {
                            this.confettiEngine.start();
                        }
                        this.startFastSync();
                    }
                },

                startFastSync() {
                    this.fetchLatestScore();
                    // Fast 300ms stateless polling - no session lock, zero delay
                    setInterval(() => {
                        this.fetchLatestScore();
                    }, 300);
                },

                syncIntervalTimer(data) {
                    if (data && data.match_status === 'interval') {
                        let sec = 0;
                        if (data.interval_until) {
                            const diff = Math.round((new Date(data.interval_until).getTime() - Date.now()) / 1000);
                            sec = Math.max(0, diff);
                        } else if (data.interval_remaining) {
                            sec = Math.max(0, data.interval_remaining);
                        } else {
                            sec = 60;
                        }

                        // Prevent clock skew between server and client from showing 61s instead of 60s
                        if (sec === 61 || sec === 62) sec = 60;
                        if (sec === 121 || sec === 122) sec = 120;

                        // Resync if timer not running or drifted by more than 1s
                        if (!this.intervalTimerId || Math.abs(this.intervalRemaining - sec) > 1) {
                            this.intervalRemaining = sec;
                        }

                        // Start 1-second live ticker if not yet ticking
                        if (!this.intervalTimerId && this.intervalRemaining > 0) {
                            this.intervalTimerId = setInterval(() => {
                                if (this.intervalRemaining > 0) {
                                    this.intervalRemaining--;
                                } else {
                                    clearInterval(this.intervalTimerId);
                                    this.intervalTimerId = null;
                                }
                            }, 1000);
                        }
                    } else {
                        // Interval has ended, stop ticker
                        if (this.intervalTimerId) {
                            clearInterval(this.intervalTimerId);
                            this.intervalTimerId = null;
                        }
                        this.intervalRemaining = 0;
                    }
                },

                async fetchLatestScore() {
                    if (!this.match || this.isSyncing) return;
                    this.isSyncing = true;
                    try {
                        const res = await fetch(`{{ url('/api/badminton/matches') }}/${this.match.id}/state?_t=${Date.now()}`, {
                            headers: { 'Cache-Control': 'no-cache', 'Pragma': 'no-cache', 'Accept': 'application/json' }
                        });
                        if (res.ok) {
                            const data = await res.json();
                            
                            // Synchronize interval countdown on every state update
                            this.syncIntervalTimer(data);

                            // Trigger / stop confetti based on match status
                            if (data.match_status === 'finished') {
                                if (this.confettiEngine && !this.confettiEngine.isRunning) {
                                    this.confettiEngine.start();
                                }
                            } else {
                                if (this.confettiEngine && this.confettiEngine.isRunning) {
                                    this.confettiEngine.stop();
                                    this.showWinnerModal = true;
                                }
                            }

                            const hash = `${data.current_set}-${data.team1_set1}-${data.team2_set1}-${data.team1_set2}-${data.team2_set2}-${data.team1_set3}-${data.team2_set3}-${data.server_team}-${data.server_player}-${data.match_status}-${data.team1_player1}-${data.team2_player1}-${data.interval_until}`;
                            if (this.lastDataHash !== hash) {
                                this.lastDataHash = hash;
                                this.match = data;
                            }
                        }
                    } catch (_) {
                    } finally {
                        this.isSyncing = false;
                    }
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

                isCurrentSetFinished() {
                    if (!this.match) return false;
                    if (this.match.is_set_finished) return true;
                    const s1 = this.getCurrentScore(1);
                    const s2 = this.getCurrentScore(2);
                    return ((s1 >= 21 || s2 >= 21) && Math.abs(s1 - s2) >= 2) || Math.max(s1, s2) >= 30;
                },

                getWinnerInfo() {
                    if (!this.match || this.match.match_status !== 'finished') return null;
                    const isT1 = (this.match.winner_team == 1);
                    const athlete = isT1 
                        ? (this.match.match_type === 'double' && this.match.team1_player2 ? `${this.match.team1_player1} / ${this.match.team1_player2}` : this.match.team1_player1)
                        : (this.match.match_type === 'double' && this.match.team2_player2 ? `${this.match.team2_player1} / ${this.match.team2_player2}` : this.match.team2_player1);
                    const school = isT1 ? this.match.team1_school : this.match.team2_school;
                    return { athlete, school, isTeam1: isT1 };
                },

                getMatchStatusLabel() {
                    if (!this.match) return '';
                    if (this.match.match_status === 'interval') return 'INTERVAL (11 POIN)';
                    if (this.match.match_status === 'finished') {
                        const winner = this.getWinnerInfo();
                        if (winner) {
                            return `🏆 WINNER: ${winner.athlete} (${winner.school})`;
                        }
                        return 'MATCH FINISHED';
                    }
                    const s1 = this.getCurrentScore(1);
                    const s2 = this.getCurrentScore(2);
                    if (this.isCurrentSetFinished()) {
                        return `🏆 GAME ${this.match.current_set} SELESAI (${s1} - ${s2})`;
                    }
                    if (s1 >= 20 && s2 >= 20) {
                        return `🔥 SETTING / DEUCE (${s1} - ${s2}) • GAME ${this.match.current_set}`;
                    }
                    if (s1 >= 20 || s2 >= 20) {
                        return `⚡ GAME POINT (${s1} - ${s2}) • GAME ${this.match.current_set}`;
                    }
                    return 'GAME ' + this.match.current_set + ' IN PROGRESS';
                },

                isIntervalActive() {
                    if (!this.match) return false;
                    if (this.match.match_status === 'interval') return true;
                    if (this.match.interval_until && new Date(this.match.interval_until) > new Date()) return true;
                    return false;
                },

                getIntervalSeconds() {
                    return this.intervalRemaining;
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