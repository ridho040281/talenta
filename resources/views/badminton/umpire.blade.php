<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>WASIT BULU TANGKIS - {{ $match->court_number }} ({{ $match->category }}) | {{ $appSettings['app_name'] ?? 'TALENTA' }}</title>
    
    <!-- Favicon -->
    @if(!empty($appSettings['favicon']))
        <link rel="icon" type="image/png" href="{{ asset('storage/' . $appSettings['favicon']) }}">
        <link rel="shortcut icon" href="{{ asset('storage/' . $appSettings['favicon']) }}">
        <link rel="apple-touch-icon" href="{{ asset('storage/' . $appSettings['favicon']) }}">
    @else
        <link rel="icon" href="{{ asset('favicon.ico') }}">
    @endif

    <!-- Vite Local Tailwind CSS & JS Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Lucide Icons & Alpine.js (Self-hosted Local) -->
    <script defer src="{{ asset('vendor/lucide/lucide.min.js') }}"></script>
    <script defer src="{{ asset('vendor/alpine/alpine.min.js') }}"></script>

    <!-- Self-hosted Fonts (lokal) -->
    <link rel="stylesheet" href="{{ asset('vendor/fonts/fonts.css') }}">

    <style>
        .font-score { font-family: 'Orbitron', monospace, sans-serif; }
        .font-badminton { font-family: 'Chakra Petch', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 font-sans antialiased min-h-screen select-none flex flex-col justify-between" x-data="badmintonUmpireApp()">

    <!-- TOP APP HEADER -->
    <header class="bg-slate-900 border-b border-slate-800 px-3 py-2 sm:px-4 sm:py-3 flex items-center justify-between gap-2 shrink-0">
        <!-- Left: Back Button + Match Court & Category Info -->
        <div class="flex items-center gap-2 sm:gap-3 min-w-0">
            <a href="{{ route('badminton.index') }}" class="p-2 rounded-xl bg-slate-800 hover:bg-slate-700 active:scale-95 text-slate-300 transition shrink-0" title="Kembali ke Daftar Pertandingan">
                <i data-lucide="arrow-left" class="w-4 h-4 sm:w-5 sm:h-5"></i>
            </a>
            <div class="min-w-0">
                <div class="flex items-center gap-1.5 sm:gap-2 flex-wrap">
                    <span class="px-1.5 py-0.5 rounded font-mono font-black bg-amber-400 text-black text-[10px] sm:text-xs shrink-0 shadow-xs">{{ $match->category }}</span>
                    <h1 class="text-xs sm:text-base font-extrabold text-white truncate flex items-center gap-1">
                        <span>{{ $match->court_number }}</span>
                        @if($match->match_order)
                            <span class="text-amber-400 font-mono text-[11px] sm:text-xs font-bold">(#{{ $match->match_order }})</span>
                        @endif
                    </h1>
                </div>
                <p class="text-[10px] sm:text-[11px] text-slate-400 font-medium truncate">
                    @if($match->scheduled_time)
                        <span class="text-emerald-400 font-mono font-bold">{{ $match->scheduled_time }} • </span>
                    @endif
                    <span>{{ $match->round_name }}</span>
                    <span class="hidden xs:inline">• {{ $match->match_type === 'double' ? 'Ganda' : 'Tunggal' }}</span>
                </p>
            </div>
        </div>

        <!-- Right: Action Buttons + Live Badge -->
        <div class="flex items-center gap-1 sm:gap-2 shrink-0">
            <button type="button" @click="editInfoModal = true" class="p-2 sm:px-3 sm:py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 active:scale-95 text-slate-300 text-xs font-bold border border-slate-700 flex items-center gap-1.5 transition cursor-pointer" title="Edit Info & Nama Pemain">
                <i data-lucide="edit-3" class="w-4 h-4 text-amber-400"></i>
                <span class="hidden md:inline">Edit</span>
            </button>
            <a href="{{ route('badminton.scoreboard', $match->id) }}" target="_blank" class="p-2 sm:px-3 sm:py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 active:scale-95 text-amber-300 text-xs font-bold border border-slate-700 flex items-center gap-1.5 cursor-pointer" title="Buka Layar LED Proyektor">
                <i data-lucide="tv" class="w-4 h-4 text-rose-500"></i>
                <span class="hidden md:inline">LED</span>
            </a>
            <a href="{{ route('badminton.bracket') }}" target="_blank" class="p-2 sm:px-3 sm:py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 active:scale-95 text-indigo-300 hover:text-white text-xs font-bold border border-slate-700 flex items-center gap-1.5 cursor-pointer" title="Lihat Bagan Pertandingan">
                <i data-lucide="git-branch" class="w-4 h-4 text-indigo-400"></i>
                <span class="hidden md:inline">Bagan</span>
            </a>
            <div class="px-2 py-1 sm:px-3 sm:py-1 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-[10px] sm:text-xs font-mono font-bold flex items-center gap-1.5 shrink-0">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                <span class="hidden sm:inline">WASIT AKTIF</span>
                <span class="sm:hidden">LIVE</span>
            </div>
        </div>
    </header>

    <!-- MAIN TOUCH INTERFACE -->
    <main class="flex-1 p-2 sm:p-5 max-w-5xl mx-auto w-full space-y-2.5 sm:space-y-4">
        
        <!-- SCORE OVERVIEW BANNER (MINI SCOREBOARD) -->
        <div class="bg-slate-900/90 border-2 border-slate-800 rounded-2xl p-2.5 sm:p-4 shadow-xl">
            <div class="grid grid-cols-12 gap-2 sm:gap-3 items-center">
                
                <!-- TIM 1 (ATAS) -->
                <div class="col-span-7 sm:col-span-8 space-y-1 min-w-0">
                    <span class="text-[10px] sm:text-xs font-extrabold text-amber-400 tracking-wider block truncate">🏫 <span x-text="match.team1_school"></span></span>
                    <div class="flex items-center gap-2">
                        <div :class="isServing(1, 1) ? 'bg-amber-400 text-black shadow-md font-black' : 'bg-slate-800 text-slate-200 font-bold'" class="px-2 sm:px-2.5 py-1 rounded-lg font-badminton text-xs sm:text-sm uppercase flex-1 truncate transition-colors flex items-center justify-between gap-1">
                            <span class="truncate" x-text="match.team1_player1"></span>
                            <template x-if="isServing(1, 1)">
                                <div class="flex items-center gap-1 shrink-0 bg-black/20 px-1.5 py-0.5 rounded animate-pulse">
                                    <span class="text-xs">🏸</span>
                                    <span class="font-bold text-[9px] text-neutral-900 hidden xs:inline">SERVE</span>
                                </div>
                            </template>
                        </div>
                    </div>
                    <template x-if="match.match_type === 'double' && match.team1_player2">
                        <div class="flex items-center gap-2 pt-0.5">
                            <div :class="isServing(1, 2) ? 'bg-amber-400 text-black shadow-md font-black' : 'bg-slate-800 text-slate-200 font-bold'" class="px-2 sm:px-2.5 py-1 rounded-lg font-badminton text-xs sm:text-sm uppercase flex-1 truncate transition-colors flex items-center justify-between gap-1">
                                <span class="truncate" x-text="match.team1_player2"></span>
                                <template x-if="isServing(1, 2)">
                                    <div class="flex items-center gap-1 shrink-0 bg-black/20 px-1.5 py-0.5 rounded animate-pulse">
                                        <span class="text-xs">🏸</span>
                                        <span class="font-bold text-[9px] text-neutral-900 hidden xs:inline">SERVE</span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- SKOR TIM 1 -->
                <div class="col-span-5 sm:col-span-4 grid grid-cols-3 gap-1 text-center font-score">
                    <div class="bg-slate-950 border border-slate-800 rounded-lg py-1 sm:py-1.5 px-0.5">
                        <span class="text-[9px] sm:text-xs text-slate-500 block leading-tight">SET 1</span>
                        <span class="text-sm sm:text-xl font-bold text-lime-400" x-text="match.team1_set1"></span>
                    </div>
                    <div class="bg-slate-950 border border-slate-800 rounded-lg py-1 sm:py-1.5 px-0.5" :class="match.current_set == 2 ? 'ring-2 ring-amber-400' : ''">
                        <span class="text-[9px] sm:text-xs text-slate-500 block leading-tight">SET 2</span>
                        <span class="text-sm sm:text-xl font-bold" :class="match.server_team == 1 && match.current_set == 2 ? 'text-amber-400' : 'text-cyan-400'" x-text="match.team1_set2"></span>
                    </div>
                    <div class="bg-slate-950 border border-slate-800 rounded-lg py-1 sm:py-1.5 px-0.5" :class="match.current_set == 3 ? 'ring-2 ring-amber-400' : ''">
                        <span class="text-[9px] sm:text-xs text-slate-500 block leading-tight">SET 3</span>
                        <span class="text-sm sm:text-xl font-bold" :class="match.current_set >= 3 ? 'text-cyan-400' : 'text-slate-600'" x-text="match.current_set >= 3 ? match.team1_set3 : '-'"></span>
                    </div>
                </div>

                <!-- DIVIDER & STATUS BADGE -->
                <div class="col-span-12 flex items-center justify-between gap-2 py-0.5">
                    <div class="h-[1px] bg-slate-800 flex-1"></div>
                    <span class="text-[9px] sm:text-[10px] font-score tracking-wider font-bold px-2.5 py-0.5 rounded-full border uppercase truncate max-w-full text-center" :class="isDeuce() ? 'bg-rose-950/80 text-rose-400 border-rose-600/60 animate-pulse' : 'text-amber-400 bg-slate-950 border-slate-800'" x-text="getStatusBadgeText()"></span>
                    <div class="h-[1px] bg-slate-800 flex-1"></div>
                </div>

                <!-- TIM 2 (BAWAH) -->
                <div class="col-span-7 sm:col-span-8 space-y-1 min-w-0">
                    <span class="text-[10px] sm:text-xs font-extrabold text-cyan-400 tracking-wider block truncate">🏫 <span x-text="match.team2_school"></span></span>
                    <div class="flex items-center gap-2">
                        <div :class="isServing(2, 1) ? 'bg-cyan-400 text-black shadow-md font-black' : 'bg-slate-800 text-slate-200 font-bold'" class="px-2 sm:px-2.5 py-1 rounded-lg font-badminton text-xs sm:text-sm uppercase flex-1 truncate transition-colors flex items-center justify-between gap-1">
                            <span class="truncate" x-text="match.team2_player1"></span>
                            <template x-if="isServing(2, 1)">
                                <div class="flex items-center gap-1 shrink-0 bg-black/20 px-1.5 py-0.5 rounded animate-pulse">
                                    <span class="text-xs">🏸</span>
                                    <span class="font-bold text-[9px] text-neutral-900 hidden xs:inline">SERVE</span>
                                </div>
                            </template>
                        </div>
                    </div>
                    <template x-if="match.match_type === 'double' && match.team2_player2">
                        <div class="flex items-center gap-2 pt-0.5">
                            <div :class="isServing(2, 2) ? 'bg-cyan-400 text-black shadow-md font-black' : 'bg-slate-800 text-slate-200 font-bold'" class="px-2 sm:px-2.5 py-1 rounded-lg font-badminton text-xs sm:text-sm uppercase flex-1 truncate transition-colors flex items-center justify-between gap-1">
                                <span class="truncate" x-text="match.team2_player2"></span>
                                <template x-if="isServing(2, 2)">
                                    <div class="flex items-center gap-1 shrink-0 bg-black/20 px-1.5 py-0.5 rounded animate-pulse">
                                        <span class="text-xs">🏸</span>
                                        <span class="font-bold text-[9px] text-neutral-900 hidden xs:inline">SERVE</span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- SKOR TIM 2 -->
                <div class="col-span-5 sm:col-span-4 grid grid-cols-3 gap-1 text-center font-score">
                    <div class="bg-slate-950 border border-slate-800 rounded-lg py-1 sm:py-1.5 px-0.5">
                        <span class="text-[9px] sm:text-xs text-slate-500 block leading-tight">SET 1</span>
                        <span class="text-sm sm:text-xl font-bold text-lime-400" x-text="match.team2_set1"></span>
                    </div>
                    <div class="bg-slate-950 border border-slate-800 rounded-lg py-1 sm:py-1.5 px-0.5" :class="match.current_set == 2 ? 'ring-2 ring-cyan-400' : ''">
                        <span class="text-[9px] sm:text-xs text-slate-500 block leading-tight">SET 2</span>
                        <span class="text-sm sm:text-xl font-bold" :class="match.server_team == 2 && match.current_set == 2 ? 'text-amber-400' : 'text-cyan-400'" x-text="match.team2_set2"></span>
                    </div>
                    <div class="bg-slate-950 border border-slate-800 rounded-lg py-1 sm:py-1.5 px-0.5" :class="match.current_set == 3 ? 'ring-2 ring-cyan-400' : ''">
                        <span class="text-[9px] sm:text-xs text-slate-500 block leading-tight">SET 3</span>
                        <span class="text-sm sm:text-xl font-bold" :class="match.current_set >= 3 ? 'text-cyan-400' : 'text-slate-600'" x-text="match.current_set >= 3 ? match.team2_set3 : '-'"></span>
                    </div>
                </div>

            </div>
        </div>

        <!-- SET FINISHED / MATCH FINISHED NOTICE -->
        <template x-if="isCurrentSetFinished() && match.match_status !== 'finished'">
            <div class="bg-gradient-to-r from-purple-950/90 via-purple-900/90 to-purple-950/90 border-2 border-purple-400 p-2.5 sm:p-3.5 rounded-2xl shadow-xl flex items-center justify-between gap-3 animate-pulse">
                <div class="min-w-0">
                    <span class="text-xs sm:text-sm font-black text-purple-200 uppercase tracking-wider block">
                        🏆 SET <span x-text="match.current_set"></span> TELAH SELESAI!
                    </span>
                    <span class="text-[10px] sm:text-xs text-purple-300 font-semibold block truncate">
                        Klik tombol di samping untuk lanjut ke Set <span x-text="parseInt(match.current_set) + 1"></span>.
                    </span>
                </div>
                <button type="button" 
                        @click="sendAction('next_set')" 
                        class="px-3.5 py-2 rounded-xl bg-purple-500 hover:bg-purple-400 text-white font-black text-xs shadow-lg shadow-purple-500/30 flex items-center gap-1.5 shrink-0 cursor-pointer active:scale-95 transition">
                    <i data-lucide="fast-forward" class="w-4 h-4"></i>
                    <span>Set Selanjutnya</span>
                </button>
            </div>
        </template>

        <template x-if="match.match_status === 'finished'">
            <div class="bg-gradient-to-r from-emerald-950/90 via-emerald-900/90 to-emerald-950/90 border-2 border-emerald-400 p-2.5 sm:p-3.5 rounded-2xl text-center shadow-xl">
                <span class="text-xs sm:text-sm font-black text-emerald-200 uppercase tracking-wider block">
                    🎉 PERTANDINGAN SELESAI!
                </span>
                <span class="text-xs sm:text-sm text-white font-extrabold block mt-0.5" x-text="'Pemenang: ' + getWinnerDisplayName()"></span>
            </div>
        </template>

        <!-- BIG TOUCH SCORING PADS (PRIMARY CONTROLS: 2 COLUMNS ON ALL SCREENS) -->
        <div class="grid grid-cols-2 gap-2 sm:gap-4">
            
            <!-- TEAM 1 TOUCH PAD (KIRI) -->
            <div class="bg-slate-900/95 border-2 border-amber-500/40 rounded-2xl p-2.5 sm:p-4 flex flex-col justify-between space-y-2 sm:space-y-3 shadow-xl" :class="isCurrentSetFinished() ? 'opacity-60 grayscale-[30%]' : ''">
                <!-- Header: School & Set badge -->
                <div class="flex items-center justify-between gap-1 min-w-0">
                    <span class="text-[11px] sm:text-xs font-black text-amber-400 truncate flex-1" x-text="match.team1_school"></span>
                    <span class="text-[9px] sm:text-xs font-mono font-bold bg-slate-800 px-1.5 py-0.5 rounded text-amber-300 shrink-0">Set <span x-text="match.current_set"></span></span>
                </div>

                <!-- Players Name -->
                <div class="text-[10px] sm:text-xs font-bold text-slate-200 truncate font-badminton">
                    <span x-text="match.team1_player1"></span>
                    <template x-if="match.match_type === 'double' && match.team1_player2">
                        <span class="text-slate-400 block text-[9px] sm:text-[10px] truncate" x-text="'/ ' + match.team1_player2"></span>
                    </template>
                </div>

                <!-- HUGE TAP BUTTON (+1 POIN) -->
                <button type="button" 
                        @click="sendAction('add_point', { team: 1 })" 
                        :disabled="loading || isCurrentSetFinished() || match.match_status === 'finished'" 
                        class="w-full py-4 sm:py-7 bg-gradient-to-b from-amber-400 to-amber-500 hover:from-amber-300 hover:to-amber-500 active:from-amber-500 active:to-amber-600 disabled:opacity-50 disabled:cursor-not-allowed text-slate-950 rounded-2xl shadow-lg shadow-amber-500/20 active:scale-[0.97] transition-all flex flex-col items-center justify-center cursor-pointer select-none">
                    <span class="text-4xl sm:text-6xl font-black font-score tracking-tighter" x-text="getCurrentScore(1)"></span>
                    <span class="text-[10px] sm:text-xs font-black tracking-wider mt-1 uppercase" x-text="isCurrentSetFinished() ? 'SET SELESAI' : '+1 POIN'"></span>
                </button>

                <!-- SERVER SELECTOR -->
                <div class="space-y-1 pt-0.5">
                    <button type="button" 
                            @click="sendAction('set_server', { team: 1, player: 1 })" 
                            :class="isServing(1, 1) ? 'bg-amber-400 text-black font-black shadow-md ring-1 ring-amber-300' : 'bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold'" 
                            class="w-full py-1.5 px-2 rounded-xl text-[10px] sm:text-xs transition truncate cursor-pointer flex items-center justify-center gap-1">
                        <span>🏸 Servis:</span>
                        <span class="truncate" x-text="match.team1_player1"></span>
                    </button>
                    <template x-if="match.match_type === 'double' && match.team1_player2">
                        <button type="button" 
                                @click="sendAction('set_server', { team: 1, player: 2 })" 
                                :class="isServing(1, 2) ? 'bg-amber-400 text-black font-black shadow-md ring-1 ring-amber-300' : 'bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold'" 
                                class="w-full py-1.5 px-2 rounded-xl text-[10px] sm:text-xs transition truncate cursor-pointer flex items-center justify-center gap-1">
                            <span>🏸 Servis:</span>
                            <span class="truncate" x-text="match.team1_player2"></span>
                        </button>
                    </template>
                </div>
            </div>

            <!-- TEAM 2 TOUCH PAD (KANAN) -->
            <div class="bg-slate-900/95 border-2 border-cyan-500/40 rounded-2xl p-2.5 sm:p-4 flex flex-col justify-between space-y-2 sm:space-y-3 shadow-xl" :class="isCurrentSetFinished() ? 'opacity-60 grayscale-[30%]' : ''">
                <!-- Header: School & Set badge -->
                <div class="flex items-center justify-between gap-1 min-w-0">
                    <span class="text-[11px] sm:text-xs font-black text-cyan-400 truncate flex-1" x-text="match.team2_school"></span>
                    <span class="text-[9px] sm:text-xs font-mono font-bold bg-slate-800 px-1.5 py-0.5 rounded text-cyan-300 shrink-0">Set <span x-text="match.current_set"></span></span>
                </div>

                <!-- Players Name -->
                <div class="text-[10px] sm:text-xs font-bold text-slate-200 truncate font-badminton">
                    <span x-text="match.team2_player1"></span>
                    <template x-if="match.match_type === 'double' && match.team2_player2">
                        <span class="text-slate-400 block text-[9px] sm:text-[10px] truncate" x-text="'/ ' + match.team2_player2"></span>
                    </template>
                </div>

                <!-- HUGE TAP BUTTON (+1 POIN) -->
                <button type="button" 
                        @click="sendAction('add_point', { team: 2 })" 
                        :disabled="loading || isCurrentSetFinished() || match.match_status === 'finished'" 
                        class="w-full py-4 sm:py-7 bg-gradient-to-b from-cyan-500 to-teal-600 hover:from-cyan-400 hover:to-teal-500 active:from-cyan-600 active:to-teal-700 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-2xl shadow-lg shadow-cyan-500/20 active:scale-[0.97] transition-all flex flex-col items-center justify-center cursor-pointer select-none">
                    <span class="text-4xl sm:text-6xl font-black font-score tracking-tighter" x-text="getCurrentScore(2)"></span>
                    <span class="text-[10px] sm:text-xs font-black tracking-wider mt-1 uppercase" x-text="isCurrentSetFinished() ? 'SET SELESAI' : '+1 POIN'"></span>
                </button>

                <!-- SERVER SELECTOR -->
                <div class="space-y-1 pt-0.5">
                    <button type="button" 
                            @click="sendAction('set_server', { team: 2, player: 1 })" 
                            :class="isServing(2, 1) ? 'bg-cyan-400 text-black font-black shadow-md ring-1 ring-cyan-300' : 'bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold'" 
                            class="w-full py-1.5 px-2 rounded-xl text-[10px] sm:text-xs transition truncate cursor-pointer flex items-center justify-center gap-1">
                        <span>🏸 Servis:</span>
                        <span class="truncate" x-text="match.team2_player1"></span>
                    </button>
                    <template x-if="match.match_type === 'double' && match.team2_player2">
                        <button type="button" 
                                @click="sendAction('set_server', { team: 2, player: 2 })" 
                                :class="isServing(2, 2) ? 'bg-cyan-400 text-black font-black shadow-md ring-1 ring-cyan-300' : 'bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold'" 
                                class="w-full py-1.5 px-2 rounded-xl text-[10px] sm:text-xs transition truncate cursor-pointer flex items-center justify-center gap-1">
                            <span>🏸 Servis:</span>
                            <span class="truncate" x-text="match.team2_player2"></span>
                        </button>
                    </template>
                </div>
            </div>

        </div>

        <!-- UTILITY CONTROLS (UNDO, INTERVAL, NEXT SET, RESET) -->
        <div class="grid grid-cols-4 gap-1.5 sm:gap-2.5">
            <!-- UNDO (-1) -->
            <button type="button" 
                    @click="sendAction('undo')" 
                    :disabled="loading" 
                    class="py-2.5 px-2 sm:py-3 sm:px-3 rounded-xl bg-slate-800 hover:bg-slate-700 active:scale-95 text-rose-400 hover:text-rose-300 font-black text-[11px] sm:text-xs border border-rose-500/30 hover:border-rose-400/60 flex flex-col sm:flex-row items-center justify-center gap-1 sm:gap-1.5 transition shadow cursor-pointer">
                <i data-lucide="rotate-ccw" class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-rose-400"></i>
                <span>UNDO</span>
            </button>

            <!-- INTERVAL 60S -->
            <button type="button" 
                    @click="startIntervalTimer(60)" 
                    class="py-2.5 px-2 sm:py-3 sm:px-3 rounded-xl bg-blue-950/70 hover:bg-blue-900/70 active:scale-95 text-blue-300 font-bold text-[11px] sm:text-xs border border-blue-700/50 flex flex-col sm:flex-row items-center justify-center gap-1 sm:gap-1.5 transition shadow cursor-pointer">
                <i data-lucide="timer" class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-blue-400"></i>
                <span>Jeda 60s</span>
            </button>

            <!-- SET SELANJUTNYA -->
            <button type="button" 
                    @click="sendAction('next_set')" 
                    :disabled="match.current_set >= 3 || match.match_status === 'finished'" 
                    :class="isCurrentSetFinished() && match.match_status !== 'finished' ? 'bg-purple-600 hover:bg-purple-500 text-white font-black animate-pulse ring-2 ring-purple-400 shadow-lg shadow-purple-500/50' : 'bg-purple-950/70 hover:bg-purple-900/70 text-purple-300 font-bold border border-purple-700/50 disabled:opacity-40 disabled:cursor-not-allowed'" 
                    class="py-2.5 px-2 sm:py-3 sm:px-3 rounded-xl active:scale-95 text-[11px] sm:text-xs flex flex-col sm:flex-row items-center justify-center gap-1 sm:gap-1.5 transition shadow cursor-pointer">
                <i data-lucide="fast-forward" class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-purple-300"></i>
                <span class="truncate">Next Set</span>
            </button>

            <!-- RESET SKOR -->
            <button type="button" 
                    @click="confirmReset()" 
                    class="py-2.5 px-2 sm:py-3 sm:px-3 rounded-xl bg-slate-800 hover:bg-slate-700 active:scale-95 text-slate-400 hover:text-white font-bold text-[11px] sm:text-xs border border-slate-700 flex flex-col sm:flex-row items-center justify-center gap-1 sm:gap-1.5 transition shadow cursor-pointer">
                <i data-lucide="refresh-cw" class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-slate-400"></i>
                <span>Reset</span>
            </button>
        </div>

    </main>

    <!-- INTERVAL TIMER POPUP / MODAL -->
    <div x-show="intervalModal" x-cloak class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-md flex items-center justify-center p-4">
        <div class="bg-slate-900 border-2 border-blue-500 rounded-3xl p-8 max-w-sm w-full text-center space-y-4 shadow-2xl">
            <span class="text-xs font-extrabold uppercase tracking-widest text-blue-400 block">JEDA INTERVAL POIN 11 / ANTAR-GAME</span>
            <div class="text-7xl font-black font-score text-white my-2" x-text="intervalSeconds"></div>
            <p class="text-xs text-slate-400">Pemain beristirahat dan menerima instruksi pelatih.</p>
            <div class="flex gap-2 pt-2">
                <button @click="stopInterval()" class="w-full py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm shadow">
                    Lanjutkan Pertandingan
                </button>
            </div>
        </div>
    </div>

    <!-- EDIT INFO POPUP / MODAL -->
    <div x-show="editInfoModal" x-cloak class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-md flex items-center justify-center p-4">
        <div @click.outside="editInfoModal = false" class="bg-slate-900 border-2 border-slate-700 rounded-3xl p-6 max-w-lg w-full space-y-4 shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="text-base font-extrabold text-white">Edit Nama & Info Pertandingan</h3>
                <button @click="editInfoModal = false" class="text-slate-400 hover:text-white"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>

            <form action="{{ route('badminton.update', $match->id) }}" method="POST" class="space-y-3 text-xs">
                @csrf
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="text-slate-400 font-bold block mb-1">Nomor Lapangan</label>
                        <input type="text" name="court_number" value="{{ $match->court_number }}" required class="w-full px-3 py-2 rounded-xl bg-slate-800 border border-slate-700 text-white font-bold">
                    </div>
                    <div>
                        <label class="text-slate-400 font-bold block mb-1">Babak</label>
                        <input type="text" name="round_name" value="{{ $match->round_name }}" required class="w-full px-3 py-2 rounded-xl bg-slate-800 border border-slate-700 text-white font-bold">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="text-slate-400 font-bold block mb-1">Kategori</label>
                        <select name="category" class="w-full px-3 py-2 rounded-xl bg-slate-800 border border-slate-700 text-white font-bold">
                            @foreach(['MS', 'WS', 'MD', 'WD', 'XD'] as $c)
                                <option value="{{ $c }}" {{ $match->category == $c ? 'selected' : '' }}>{{ $c }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-slate-400 font-bold block mb-1">Tipe Pertandingan</label>
                        <select name="match_type" class="w-full px-3 py-2 rounded-xl bg-slate-800 border border-slate-700 text-white font-bold">
                            <option value="single" {{ $match->match_type == 'single' ? 'selected' : '' }}>Single (Tunggal)</option>
                            <option value="double" {{ $match->match_type == 'double' ? 'selected' : '' }}>Double (Ganda)</option>
                        </select>
                    </div>
                </div>

                <!-- Tim 1 -->
                <div class="p-3 bg-amber-950/20 border border-amber-800/40 rounded-xl space-y-1.5">
                    <span class="text-amber-400 font-bold block">Tim 1 (Sisi Atas)</span>
                    <input type="text" name="team1_school" value="{{ $match->team1_school }}" placeholder="Asal Sekolah" required class="w-full px-2.5 py-1.5 rounded-lg bg-slate-800 border border-slate-700 text-white">
                    <input type="text" name="team1_player1" value="{{ $match->team1_player1 }}" placeholder="Nama Pemain 1" required class="w-full px-2.5 py-1.5 rounded-lg bg-slate-800 border border-slate-700 text-white">
                    <input type="text" name="team1_player2" value="{{ $match->team1_player2 }}" placeholder="Nama Pemain 2 (Kosongkan jika Single)" class="w-full px-2.5 py-1.5 rounded-lg bg-slate-800 border border-slate-700 text-white">
                </div>

                <!-- Tim 2 -->
                <div class="p-3 bg-cyan-950/20 border border-cyan-800/40 rounded-xl space-y-1.5">
                    <span class="text-cyan-400 font-bold block">Tim 2 (Sisi Bawah)</span>
                    <input type="text" name="team2_school" value="{{ $match->team2_school }}" placeholder="Asal Sekolah" required class="w-full px-2.5 py-1.5 rounded-lg bg-slate-800 border border-slate-700 text-white">
                    <input type="text" name="team2_player1" value="{{ $match->team2_player1 }}" placeholder="Nama Pemain 1" required class="w-full px-2.5 py-1.5 rounded-lg bg-slate-800 border border-slate-700 text-white">
                    <input type="text" name="team2_player2" value="{{ $match->team2_player2 }}" placeholder="Nama Pemain 2 (Kosongkan jika Single)" class="w-full px-2.5 py-1.5 rounded-lg bg-slate-800 border border-slate-700 text-white">
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="editInfoModal = false" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 font-bold">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold shadow">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>

    <!-- FOOTER STATUS -->
    <footer class="bg-slate-900 border-t border-slate-800 px-4 py-2 text-center text-xs text-slate-500 flex justify-between items-center">
        <span>Wasit: <strong>{{ auth()->user()->name ?? 'Official Umpire' }}</strong></span>
        <span class="font-mono text-emerald-400">● LIVE SYNC DATABASE</span>
    </footer>

    <!-- JAVASCRIPT STATE APP -->
    <script>
        function badmintonUmpireApp() {
            return {
                match: @json($match),
                loading: false,
                intervalModal: false,
                editInfoModal: false,
                intervalSeconds: 60,
                intervalTimerId: null,
                intervalSetsTriggered: { 1: false, 2: false, 3: false },
                actionQueue: [],
                isProcessingQueue: false,
                lastSyncTime: 0,

                init() {
                    lucide.createIcons();
                    // Background sync every 800ms so umpire screen never goes stale
                    setInterval(() => {
                        this.backgroundSync();
                    }, 800);
                },

                isServing(team, player) {
                    return this.match.server_team == team && this.match.server_player == player;
                },

                isDeuce() {
                    const s1 = this.getCurrentScore(1);
                    const s2 = this.getCurrentScore(2);
                    return s1 >= 20 && s2 >= 20 && this.match.match_status !== 'finished';
                },

                isCurrentSetFinished() {
                    if (this.match.is_set_finished) return true;
                    const s1 = this.getCurrentScore(1);
                    const s2 = this.getCurrentScore(2);
                    return ((s1 >= 21 || s2 >= 21) && Math.abs(s1 - s2) >= 2) || Math.max(s1, s2) >= 30;
                },

                getWinnerDisplayName() {
                    if (!this.match || this.match.match_status !== 'finished') return '';
                    const isT1 = (this.match.winner_team == 1);
                    const athlete = isT1 
                        ? (this.match.match_type === 'double' && this.match.team1_player2 ? `${this.match.team1_player1} / ${this.match.team1_player2}` : this.match.team1_player1)
                        : (this.match.match_type === 'double' && this.match.team2_player2 ? `${this.match.team2_player1} / ${this.match.team2_player2}` : this.match.team2_player1);
                    const school = isT1 ? this.match.team1_school : this.match.team2_school;
                    return `${athlete} (${school})`;
                },

                getStatusBadgeText() {
                    if (this.match.match_status === 'finished') {
                        return 'MATCH FINISHED • WINNER: ' + this.getWinnerDisplayName();
                    }
                    if (this.isCurrentSetFinished()) {
                        return `🏆 SET ${this.match.current_set} SELESAI • SILAKAN KLIK SET SELANJUTNYA`;
                    }
                    if (this.match.match_status === 'interval') {
                        return 'INTERVAL (11 POIN)';
                    }
                    const s1 = this.getCurrentScore(1);
                    const s2 = this.getCurrentScore(2);
                    if (s1 >= 20 && s2 >= 20) {
                        return `SETTING / DEUCE (${s1} - ${s2})`;
                    }
                    if (s1 >= 20 || s2 >= 20) {
                        return `GAME POINT (${s1} - ${s2})`;
                    }
                    return `GAME ${this.match.current_set} • IN PROGRESS`;
                },

                getCurrentScore(team) {
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

                async sendAction(action, payload = {}) {
                    // 1. OPTIMISTIC LOCAL UPDATE (0ms Instant UI Response on Tap)
                    if (action === 'add_point') {
                        if (this.isCurrentSetFinished() || this.match.match_status === 'finished') {
                            return;
                        }

                        const team = payload.team;
                        const set = this.match.current_set;
                        const key = `team${team}_set${set}`;
                        const currentVal = parseInt(this.match[key]) || 0;
                        if (currentVal >= 30) {
                            return;
                        }

                        this.match[key] = Math.min(30, currentVal + 1);
                        
                        // Update serve rotation immediately
                        if (this.match.match_type === 'double') {
                            if (this.match.server_team === team) {
                                this.match.server_player = (this.match.server_player === 1) ? 2 : 1;
                            } else {
                                this.match.server_team = team;
                                this.match.server_player = (this.match[key] % 2 === 0) ? 1 : 2;
                            }
                        } else {
                            this.match.server_team = team;
                            this.match.server_player = 1;
                        }
                        if (this.match.match_status === 'upcoming' || this.match.match_status === 'interval') {
                            this.match.match_status = 'ongoing';
                        }
                    }

                    this.lastSyncTime = Date.now();
                    this.actionQueue.push({ action, ...payload });
                    this.processQueue();
                },

                async processQueue() {
                    if (this.isProcessingQueue || this.actionQueue.length === 0) return;
                    this.isProcessingQueue = true;

                    while (this.actionQueue.length > 0) {
                        const payload = this.actionQueue[0];
                        try {
                            const response = await fetch(`{{ url('/api/badminton/matches') }}/${this.match.id}/score`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify(payload)
                            });

                            if (response.ok) {
                                const data = await response.json();
                                this.actionQueue.shift(); // Remove successful item
                                if (data.success) {
                                    this.match = data.match;
                                    this.$nextTick(() => { lucide.createIcons(); });

                                    // Check 11 point interval (Only triggers ONCE per set when first player reaches 11)
                                    const currentS1 = this.getCurrentScore(1);
                                    const currentS2 = this.getCurrentScore(2);
                                    const currentSet = this.match.current_set;
                                    if (payload.action === 'add_point' && !this.intervalSetsTriggered[currentSet]) {
                                        const isFirstTo11 = (currentS1 === 11 && currentS2 < 11) || (currentS2 === 11 && currentS1 < 11);
                                        if (isFirstTo11) {
                                            this.intervalSetsTriggered[currentSet] = true;
                                            this.startIntervalTimer(60);
                                        }
                                    }
                                }
                            } else {
                                // Discard bad request on 4xx to prevent permanent queue freeze
                                if (response.status >= 400 && response.status < 500) {
                                    this.actionQueue.shift();
                                }
                                break;
                            }
                        } catch (e) {
                            console.error('Queue network error:', e);
                            this.actionQueue.shift(); // Prevent blocking
                            break;
                        }
                    }
                    this.isProcessingQueue = false;
                },

                async backgroundSync() {
                    // Only sync from server if no actions are actively queued or being sent in the last 1.2s
                    if (this.isProcessingQueue || this.actionQueue.length > 0 || (Date.now() - this.lastSyncTime < 1200)) {
                        return;
                    }
                    try {
                        const res = await fetch(`{{ url('/api/badminton/matches') }}/${this.match.id}/state?_t=${Date.now()}`, {
                            headers: { 'Cache-Control': 'no-cache', 'Pragma': 'no-cache', 'Accept': 'application/json' }
                        });
                        if (res.ok) {
                            const data = await res.json();
                            const currentHash = `${this.match.current_set}-${this.match.team1_set1}-${this.match.team2_set1}-${this.match.team1_set2}-${this.match.team2_set2}-${this.match.team1_set3}-${this.match.team2_set3}-${this.match.server_team}-${this.match.server_player}-${this.match.match_status}`;
                            const newHash = `${data.current_set}-${data.team1_set1}-${data.team2_set1}-${data.team1_set2}-${data.team2_set2}-${data.team1_set3}-${data.team2_set3}-${data.server_team}-${data.server_player}-${data.match_status}`;
                            if (currentHash !== newHash) {
                                this.match = data;
                                this.$nextTick(() => { lucide.createIcons(); });
                            }
                        }
                    } catch (e) {
                        // Silently ignore background polling errors
                    }
                },

                startIntervalTimer(sec) {
                    this.intervalSeconds = sec;
                    this.intervalModal = true;
                    this.sendAction('start_interval', { seconds: sec });
                    clearInterval(this.intervalTimerId);
                    this.intervalTimerId = setInterval(() => {
                        if (this.intervalSeconds > 0) {
                            this.intervalSeconds--;
                        } else {
                            this.stopInterval();
                        }
                    }, 1000);
                },

                stopInterval() {
                    clearInterval(this.intervalTimerId);
                    this.intervalModal = false;
                    this.sendAction('stop_interval');
                },

                confirmReset() {
                    if (confirm('Yakin ingin mereset skor pertandingan ini kembali ke 0-0?')) {
                        this.sendAction('reset');
                    }
                }
            }
        }
    </script>
</body>
</html>