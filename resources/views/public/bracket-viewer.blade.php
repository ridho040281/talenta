<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bagan Pertandingan Turnamen - {{ $competition->name }} | {{ $appSettings['event_name'] ?? ($appSettings['app_name'] ?? 'TALENTA') }}</title>
    
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
    <script defer src="{{ asset('vendor/lucide/lucide.min.js') }}"></script>

    <!-- Alpine.js (Self-hosted Local) -->
    <script defer src="{{ asset('vendor/alpine/alpine.min.js') }}"></script>

    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 font-sans antialiased min-h-screen flex flex-col selection:bg-indigo-500 selection:text-white"
      x-data="{ 
          viewMode: (new URLSearchParams(window.location.search).get('view') || localStorage.getItem('talenta_bracket_view') || 'classic'),
          classicTheme: (localStorage.getItem('talenta_bracket_theme') || 'dark'),
          setViewMode(mode) {
              this.viewMode = mode;
              localStorage.setItem('talenta_bracket_view', mode);
              if (window.lucide) { this.$nextTick(() => window.lucide.createIcons()); }
          },
          setClassicTheme(theme) {
              this.classicTheme = theme;
              localStorage.setItem('talenta_bracket_theme', theme);
          }
      }">

    <!-- Top Header Bar -->
    <header class="bg-slate-900/90 backdrop-blur border-b border-slate-800 px-6 py-4 flex flex-col sm:flex-row items-center justify-between gap-4 sticky top-0 z-50">
        <div class="flex items-center gap-3 w-full sm:w-auto justify-between sm:justify-start">
            <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                @if(!empty($appSettings['app_logo']))
                    <img src="{{ asset('storage/' . $appSettings['app_logo']) }}" alt="{{ $appSettings['app_name'] ?? 'Logo' }}" class="h-10 w-auto max-w-[140px] object-contain group-hover:scale-105 transition">
                @else
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-500 to-blue-600 text-white flex items-center justify-center font-black group-hover:scale-105 transition shadow-lg shadow-indigo-500/20">
                        <i data-lucide="git-branch" class="w-5 h-5"></i>
                    </div>
                @endif
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-base sm:text-lg font-black text-white tracking-tight">BAGAN PERTANDINGAN</h1>
                    @if($bracketData)
                        @if(!empty($bracketData['playoffs']['has_playoffs']))
                            <span class="px-2 py-0.5 rounded bg-amber-500/20 text-amber-300 border border-amber-500/30 text-[10px] font-black uppercase font-mono">
                                BAGAN {{ $bracketData['bracket_size'] }} + {{ $bracketData['playoffs']['num_playoffs'] }} PLAY-OFF
                            </span>
                        @else
                            <span class="px-2 py-0.5 rounded bg-indigo-500/20 text-indigo-400 border border-indigo-500/30 text-[10px] font-black uppercase font-mono">
                                BAGAN {{ $bracketData['bracket_size'] }}
                            </span>
                        @endif
                    @endif
                </div>
                <p class="text-xs text-amber-400 font-bold">{{ $competition->name }} <span class="text-slate-500">•</span> <span class="text-slate-400 font-normal">{{ $appSettings['event_name'] ?? 'Festival Lomba' }}</span></p>
            </div>
        </div>

        <div class="flex items-center gap-3 w-full sm:w-auto justify-end flex-wrap">
            <!-- View Mode Switcher -->
            <div class="inline-flex p-1 bg-slate-800/80 rounded-xl border border-slate-700 text-xs font-bold shadow-inner">
                <button type="button" 
                        @click="setViewMode('classic')" 
                        :class="viewMode === 'classic' ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-400 hover:text-white'" 
                        class="px-3 py-1.5 rounded-lg transition cursor-pointer flex items-center gap-1.5">
                    <i data-lucide="git-branch" class="w-3.5 h-3.5"></i>
                    <span>Bagan Garis Klasik</span>
                </button>
                <button type="button" 
                        @click="setViewMode('cards')" 
                        :class="viewMode === 'cards' ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-400 hover:text-white'" 
                        class="px-3 py-1.5 rounded-lg transition cursor-pointer flex items-center gap-1.5">
                    <i data-lucide="layout-grid" class="w-3.5 h-3.5"></i>
                    <span>Kartu Lomba</span>
                </button>
            </div>

            <!-- In Classic Mode: White / Dark Paper Theme Toggle -->
            <div x-show="viewMode === 'classic'" x-cloak class="inline-flex p-1 bg-slate-800/80 rounded-xl border border-slate-700 text-xs font-bold">
                <button type="button" @click="setClassicTheme('white')" :class="classicTheme === 'white' ? 'bg-white text-slate-950 shadow' : 'text-slate-400 hover:text-white'" class="px-2.5 py-1 rounded-lg transition cursor-pointer" title="Latar Kertas Putih">
                    Putih
                </button>
                <button type="button" @click="setClassicTheme('dark')" :class="classicTheme === 'dark' ? 'bg-slate-900 text-cyan-300 shadow' : 'text-slate-400 hover:text-white'" class="px-2.5 py-1 rounded-lg transition cursor-pointer" title="Latar Gelap (Dark Mode)">
                    Gelap
                </button>
            </div>

            <!-- Fullscreen TV toggle -->
            <button type="button" 
                    onclick="toggleFullScreen()" 
                    id="btnFullscreen"
                    class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white border border-slate-700 font-bold text-xs flex items-center gap-1.5 transition cursor-pointer">
                <i data-lucide="maximize" class="w-3.5 h-3.5"></i>
                <span class="hidden sm:inline">Layar Penuh TV</span>
            </button>

            <!-- Link to Scoreboard -->
            <a href="{{ route('live.scoreboard', $competition->slug) }}" class="px-3.5 py-1.5 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 font-black text-xs shadow-md shadow-amber-500/20 transition">
                Papan Skor
            </a>
        </div>
    </header>

    <!-- Subheader with Pools / Categories Switcher -->
    @if(count($pools) > 1)
    <div class="bg-slate-900/60 border-b border-slate-800/80 px-6 py-2.5 flex items-center gap-2 overflow-x-auto scrollbar-thin">
        <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mr-1 shrink-0">Kategori:</span>
        @foreach($pools as $p)
            @php 
                $isActive = ($p['key'] === $activePoolKey); 
                $isPi = ($p['sector'] ?? '') === 'PI' || str_contains($p['key'], '_pi') || stripos($p['title'], 'putri') !== false;
                $isGanda = str_contains($p['key'], 'ganda');
                $isMix = ($p['sector'] ?? '') === 'MIX' || str_contains($p['key'], 'mix');

                $icon = $isGanda ? '👥' : ($isPi ? '👧' : '👦');
                
                $cLabel = $p['class_label'] ?? $p['category_label'] ?? '';
                if ($isGanda) {
                    $tabName = $isMix ? 'Ganda (MIX)' : ($isPi ? 'Ganda (PI)' : 'Ganda (PA)');
                } elseif (!empty($cLabel)) {
                    $tabName = $cLabel . ' (' . ($isPi ? 'PI' : 'PA') . ')';
                } else {
                    $tabName = $p['title'];
                }
            @endphp
            <a href="{{ route('public.bracket', $competition->slug) }}?pool={{ urlencode($p['key']) }}"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition whitespace-nowrap {{ $isActive ? ($isPi ? 'bg-gradient-to-r from-rose-600 to-pink-600 text-white shadow-lg shadow-rose-600/30 font-black' : 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-lg shadow-indigo-600/30 font-black') : 'bg-slate-800 text-slate-300 hover:text-white hover:bg-slate-700' }}">
                <span>{{ $icon }}</span>
                <span>{{ $tabName }}</span>
                <span class="px-1.5 py-0.5 rounded-md text-[10px] {{ $isActive ? 'bg-white/25 text-white font-mono font-bold' : 'bg-slate-900 text-slate-400 font-mono' }}">
                    {{ count($p['participants']) }}
                </span>
            </a>
        @endforeach
    </div>
    @endif

    <!-- Main Bracket Tree Content -->
    <main class="flex-1 w-full p-4 sm:p-8 flex flex-col justify-start">

        @if(!$bracketData || empty($bracketData['rounds']))
            <div class="py-24 text-center max-w-lg mx-auto bg-slate-900/60 rounded-3xl border border-slate-800 p-8 my-auto">
                <div class="w-16 h-16 rounded-2xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center mx-auto mb-4 border border-indigo-500/20">
                    <i data-lucide="git-branch" class="w-8 h-8"></i>
                </div>
                <h3 class="text-xl font-black text-white mb-2">Bagan Sedang Dipersiapkan</h3>
                <p class="text-xs text-slate-400">
                    Jadwal dan bagan pertandingan untuk kategori ini belum diterbitkan oleh panitia lomba. Silakan periksa kembali beberapa saat lagi.
                </p>
            </div>
        @else

            <!-- Info Bar -->
            <div class="mb-6 flex flex-wrap items-center justify-between gap-3 bg-slate-900/60 px-5 py-3 rounded-2xl border border-slate-800/80">
                <div class="flex items-center gap-4 text-xs font-mono">
                    <span class="text-slate-400">Total: <strong class="text-white">{{ $bracketData['total_participants'] }}</strong> Peserta</span>
                    <span class="text-slate-600">•</span>
                    <span class="text-slate-400">Bebas Babak 1 (BYE): <strong class="text-cyan-400">{{ $bracketData['total_byes'] }}</strong></span>
                    <span class="text-slate-600">•</span>
                    <span class="text-slate-400">Format: <strong class="text-emerald-400">Gugur Tunggal BWF</strong></span>
                </div>
                <div class="flex items-center gap-3 text-[11px]">
                    <span class="inline-flex items-center gap-1.5 text-amber-300 font-bold">
                        <span class="w-2 h-2 rounded-full bg-amber-400"></span> ⭐ Seeded
                    </span>
                    <span class="inline-flex items-center gap-1.5 text-cyan-300 font-bold">
                        <span class="w-2 h-2 rounded-full bg-cyan-400"></span> [BYE] Lolos Langsung
                    </span>
                </div>
            </div>

            <!-- BWF Separation Notification (Public View) -->
            @if(!empty($bracketData['has_bwf_protections']))
            <div class="mb-6 bg-indigo-950/50 border border-indigo-500/30 rounded-2xl p-4 flex items-center justify-between flex-wrap gap-3 text-xs text-indigo-200">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-indigo-500/20 border border-indigo-500/30 flex items-center justify-center text-indigo-300 shrink-0">
                        🛡️
                    </div>
                    <div>
                        <span class="font-bold text-white">Proteksi BWF GCR 14 Aktif:</span>
                        <span class="text-slate-300">Peserta dari satu delegasi sekolah yang sama dipisahkan pool untuk menjamin tidak bentrok di Babak 1.</span>
                    </div>
                </div>
                <div class="flex flex-wrap gap-1.5">
                    @foreach($bracketData['bwf_protections'] as $prot)
                        <span class="px-2.5 py-1 rounded-lg bg-slate-900 border border-indigo-500/30 text-[10px] text-indigo-300 font-mono">
                            🛡️ {{ $prot['institution'] }} (Slot #{{ $prot['slot'] }})
                        </span>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Classic Line Tree (Persis Bagan GOR Standar BWF) -->
            <div x-show="viewMode === 'classic'" x-cloak class="overflow-x-auto pb-10 scrollbar-thin">
                <div class="p-6 rounded-3xl border shadow-2xl overflow-auto min-w-[950px] lg:min-w-[1150px] flex justify-center transition-colors duration-300"
                     :class="classicTheme === 'white' ? 'bg-white border-slate-200 shadow-slate-950/30' : 'bg-slate-900/90 border-slate-800 shadow-2xl'">
                    <div x-show="classicTheme === 'white'" class="w-full flex justify-center">
                        {!! $bracketData['classic_svg_light'] !!}
                    </div>
                    <div x-show="classicTheme === 'dark'" class="w-full flex justify-center">
                        {!! $bracketData['classic_svg_dark'] !!}
                    </div>
                </div>
            </div>

            <!-- Bracket Columns Horizontal Scroll Area -->
            <div x-show="viewMode === 'cards'" x-cloak class="overflow-x-auto pb-10 scrollbar-thin">
                <div class="inline-flex gap-8 min-w-full items-stretch px-2">

                    <!-- Play-off Column (if active) -->
                    @if(!empty($bracketData['playoffs']['has_playoffs']) && !empty($bracketData['playoffs']['matches']))
                        <div class="flex flex-col min-w-[280px] sm:min-w-[320px] max-w-[340px]">
                            <div class="mb-5 text-center">
                                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-amber-500/15 border border-amber-500/30 shadow-md">
                                    <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                                    <span class="text-xs font-black text-amber-300 tracking-wide uppercase">Play-off Kualifikasi</span>
                                    <span class="text-[10px] font-mono text-amber-400">({{ count($bracketData['playoffs']['matches']) }})</span>
                                </div>
                            </div>

                            <div class="flex-1 flex flex-col justify-around gap-6 py-2">
                                @foreach($bracketData['playoffs']['matches'] as $poMatch)
                                    @php
                                        $poT1 = $poMatch['team1'];
                                        $poT2 = $poMatch['team2'];
                                        $poExisting = $poMatch['existing_match'];
                                        $isPoFinished = ($poMatch['status'] === 'finished');
                                        $isPoOngoing = ($poMatch['status'] === 'ongoing');
                                        $isPoPending = ($poMatch['status'] === 'pending_draw');
                                    @endphp
                                    <div class="bg-slate-900/90 rounded-2xl border transition-all duration-200 shadow-lg relative overflow-hidden
                                        {{ $isPoOngoing ? 'border-amber-500/70 shadow-amber-500/20 ring-1 ring-amber-500/50' : ($isPoFinished ? 'border-emerald-500/40' : 'border-amber-500/30') }}">
                                        <!-- Header Bar -->
                                        <div class="px-3.5 py-2 bg-slate-950/70 border-b border-slate-800/80 flex items-center justify-between text-[11px]">
                                            <div class="flex items-center gap-1.5 font-mono font-bold text-amber-400">
                                                <i data-lucide="zap" class="w-3.5 h-3.5"></i>
                                                <span>{{ $poMatch['match_code'] }}</span>
                                            </div>
                                            <div>
                                                @if($isPoOngoing)
                                                    <span class="px-2 py-0.5 rounded-md bg-amber-500/20 text-amber-300 font-bold text-[10px] border border-amber-500/40 flex items-center gap-1">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-ping"></span> Live Tanding
                                                    </span>
                                                @elseif($isPoFinished)
                                                    <span class="px-2 py-0.5 rounded-md bg-emerald-500/15 text-emerald-300 font-bold text-[10px] border border-emerald-500/30">
                                                        Selesai
                                                    </span>
                                                @elseif($isPoPending)
                                                    <span class="px-2 py-0.5 rounded-md bg-slate-800 text-slate-400 font-bold text-[10px] border border-slate-700/60">
                                                        Menunggu Undian
                                                    </span>
                                                @else
                                                    <span class="px-2 py-0.5 rounded-md bg-amber-500/15 text-amber-300 font-mono text-[10px] border border-amber-500/30">
                                                        Pra-Babak 1
                                                    </span>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Schedule Info Bar -->
                                        @if($poExisting && $poExisting->court_number)
                                            <div class="px-3.5 py-1.5 bg-slate-950/80 border-b border-slate-800/80 flex items-center justify-between text-[11px] font-mono">
                                                <div class="flex items-center gap-1.5 flex-wrap">
                                                    @if($poExisting->match_day_label || $poExisting->match_day)
                                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-sky-500/20 text-sky-300 font-bold text-[10px] border border-sky-500/30">
                                                            📅 {{ $poExisting->match_day_label ?: ('Hari ' . ($poExisting->match_day ?: 1)) }}
                                                        </span>
                                                    @endif
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-indigo-500/20 text-indigo-300 font-bold text-[10px] border border-indigo-500/30">
                                                        🏸 {{ $poExisting->court_number }}
                                                    </span>
                                                    @if($poExisting->match_order)
                                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-amber-500/20 text-amber-300 font-bold text-[10px] border border-amber-500/30">
                                                            Partai #{{ $poExisting->match_order }}
                                                        </span>
                                                    @endif
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-300 font-bold text-[10px] border border-emerald-500/30">
                                                        ⏰ {{ $poExisting->scheduled_time ?? '08:30' }}
                                                    </span>
                                                </div>
                                                <span class="text-[9px] text-slate-500 font-bold uppercase">GOR</span>
                                            </div>
                                        @endif

                                        <!-- Teams -->
                                        <div class="p-3 space-y-2">
                                            @php
                                                $isT1Winner = ($poMatch['winner'] && ($poT1['id'] ?? null) && ($poMatch['winner']['id'] ?? null) === $poT1['id']);
                                                $isT2Winner = ($poMatch['winner'] && ($poT2['id'] ?? null) && ($poMatch['winner']['id'] ?? null) === $poT2['id']);
                                            @endphp
                                            <!-- Team 1 -->
                                            <div class="flex items-center justify-between gap-2 p-2 rounded-xl transition {{ $isT1Winner ? 'bg-emerald-500/15 border border-emerald-500/30' : 'bg-slate-950/40' }}">
                                                <div class="min-w-0 flex-1">
                                                    <p class="text-xs font-bold text-white truncate">{{ $poT1['name'] ?? '[Menunggu Undian]' }}</p>
                                                    <p class="text-[10px] text-slate-400 truncate">{{ $poT1['institution'] ?? 'Peserta Undian' }}</p>
                                                </div>
                                                @if($isT1Winner)
                                                    <span class="px-1.5 py-0.5 rounded bg-emerald-500/20 text-emerald-300 font-black text-[9px] uppercase">Lolos</span>
                                                @endif
                                            </div>

                                            <!-- Team 2 -->
                                            <div class="flex items-center justify-between gap-2 p-2 rounded-xl transition {{ $isT2Winner ? 'bg-emerald-500/15 border border-emerald-500/30' : 'bg-slate-950/40' }}">
                                                <div class="min-w-0 flex-1">
                                                    <p class="text-xs font-bold text-white truncate">{{ $poT2['name'] ?? '[Menunggu Undian]' }}</p>
                                                    <p class="text-[10px] text-slate-400 truncate">{{ $poT2['institution'] ?? 'Peserta Undian' }}</p>
                                                </div>
                                                @if($isT2Winner)
                                                    <span class="px-1.5 py-0.5 rounded bg-emerald-500/20 text-emerald-300 font-black text-[9px] uppercase">Lolos</span>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Qualification Target Footer -->
                                        <div class="px-3.5 py-2 bg-amber-500/5 border-t border-amber-500/20 flex items-center justify-between text-[10px]">
                                            <span class="text-amber-400 font-bold flex items-center gap-1">
                                                <span>&rarr; Menuju Slot #{{ $poMatch['target_slot'] }} Babak 1</span>
                                            </span>
                                            @if($poExisting)
                                                <a href="{{ route('live.scoreboard', $competition->slug) }}" target="_blank" class="px-1.5 py-0.5 rounded bg-amber-500/20 text-amber-300 font-bold">Live Skor</a>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @foreach($bracketData['rounds'] as $round)
                        <div class="flex flex-col min-w-[280px] sm:min-w-[320px] max-w-[340px]">
                            <!-- Round Header -->
                            <div class="mb-5 text-center">
                                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-900 border border-slate-800 shadow-md">
                                    <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                                    <span class="text-xs font-black text-white tracking-wide uppercase">{{ $round['round_name'] }}</span>
                                    <span class="text-[10px] font-mono text-slate-400">({{ count($round['matches']) }})</span>
                                </div>
                            </div>

                            <!-- Matches List -->
                            <div class="flex-1 flex flex-col justify-around gap-6 py-2">
                                @foreach($round['matches'] as $match)
                                    @php
                                        $t1 = $match['team1'];
                                        $t2 = $match['team2'];
                                        $isFinished = ($match['status'] === 'finished');
                                        $isOngoing = ($match['status'] === 'ongoing');
                                        $isByeAdvance = ($match['status'] === 'bye_advance');
                                        $existing = $match['existing_match'];
                                    @endphp

                                    <div class="bg-slate-900/90 rounded-2xl border transition-all duration-200 shadow-lg relative overflow-hidden
                                        {{ $isOngoing ? 'border-amber-500/70 shadow-amber-500/20 ring-1 ring-amber-500/50' : ($isFinished ? 'border-emerald-500/40' : ($isByeAdvance ? 'border-cyan-500/30 bg-cyan-950/10' : 'border-slate-800')) }}">

                                        <!-- Header Bar -->
                                        <div class="px-3.5 py-2 bg-slate-950/70 border-b border-slate-800/80 flex items-center justify-between text-[11px]">
                                            <span class="font-mono font-bold text-slate-400">
                                                {{ $match['match_code'] }}
                                            </span>
                                            <div>
                                                @if($isByeAdvance)
                                                    <span class="px-2 py-0.5 rounded-md bg-cyan-500/15 text-cyan-300 font-bold text-[10px] border border-cyan-500/30">
                                                        BYE Advance
                                                    </span>
                                                @elseif($isOngoing)
                                                    <span class="px-2 py-0.5 rounded-md bg-amber-500/20 text-amber-300 font-bold text-[10px] border border-amber-500/40 flex items-center gap-1">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-ping"></span>
                                                        Live Tanding
                                                    </span>
                                                @elseif($isFinished)
                                                    <span class="px-2 py-0.5 rounded-md bg-emerald-500/15 text-emerald-300 font-bold text-[10px] border border-emerald-500/30">
                                                        Selesai
                                                    </span>
                                                @else
                                                    <span class="px-2 py-0.5 rounded-md bg-slate-800 text-slate-400 font-mono text-[10px]">
                                                        Jadwal
                                                    </span>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Court & Time Schedule Badge Bar (Public) -->
                                        @if($existing && $existing->court_number && strtoupper($existing->court_number) !== 'BYE')
                                            <div class="px-3.5 py-1.5 bg-slate-950/80 border-b border-slate-800/80 flex items-center justify-between text-[11px] font-mono">
                                                <div class="flex items-center gap-1.5 flex-wrap">
                                                    @if($existing->match_day_label || $existing->match_day)
                                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-sky-500/20 text-sky-300 font-bold text-[10px] border border-sky-500/30">
                                                            📅 {{ $existing->match_day_label ?: ('Hari ' . $existing->match_day) }}
                                                        </span>
                                                    @endif
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-indigo-500/20 text-indigo-300 font-bold text-[10px] border border-indigo-500/30">
                                                        🏸 {{ $existing->court_number }}
                                                    </span>
                                                    @if($existing->match_order)
                                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-amber-500/20 text-amber-300 font-bold text-[10px] border border-amber-500/30">
                                                            Partai #{{ $existing->match_order }}
                                                        </span>
                                                    @endif
                                                    @if($existing->scheduled_time)
                                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-300 font-bold text-[10px] border border-emerald-500/30">
                                                            ⏰ {{ $existing->scheduled_time }}
                                                        </span>
                                                    @endif
                                                </div>
                                                <span class="text-[9px] text-slate-500 font-bold uppercase tracking-wider">GOR</span>
                                            </div>
                                        @endif

                                        <!-- Competitors -->
                                        <div class="p-3 space-y-2">
                                            <!-- Team 1 -->
                                            @php
                                                $isT1Winner = false;
                                                if ($match['winner'] && ($t1['id'] ?? null) && ($match['winner']['id'] ?? null) === $t1['id']) {
                                                    $isT1Winner = true;
                                                }
                                                if ($isByeAdvance && !($t1['is_bye'] ?? false)) {
                                                    $isT1Winner = true;
                                                }
                                            @endphp
                                            <div class="flex items-center justify-between gap-2 p-2.5 rounded-xl transition
                                                {{ $isT1Winner ? 'bg-emerald-500/15 border border-emerald-500/30' : (($t1['is_bye'] ?? false) ? 'bg-cyan-950/20 border border-cyan-500/20 opacity-70' : 'bg-slate-950/40 border border-transparent') }}">
                                                <div class="flex items-center gap-2.5 min-w-0">
                                                    <div class="flex-shrink-0 flex items-center gap-1">
                                                        @if(isset($t1['slot_number']))
                                                            <span class="w-5 h-5 rounded-md bg-slate-800 text-slate-400 font-mono text-[10px] font-bold flex items-center justify-center">
                                                                {{ $t1['slot_number'] }}
                                                            </span>
                                                        @endif
                                                        @if(!empty($t1['seed_number']))
                                                            <span class="px-1.5 py-0.5 rounded bg-amber-500/20 text-amber-300 border border-amber-500/40 text-[9px] font-black">
                                                                S{{ $t1['seed_number'] }}
                                                            </span>
                                                        @elseif(!empty($t1['draw_number']))
                                                            <span class="px-1.5 py-0.5 rounded bg-slate-800 text-slate-400 text-[9px] font-mono">
                                                                #{{ $t1['draw_number'] }}
                                                            </span>
                                                        @endif
                                                    </div>

                                                    <div class="min-w-0">
                                                        <div class="text-xs font-bold truncate {{ $isT1Winner ? 'text-emerald-300 font-black' : (($t1['is_bye'] ?? false) ? 'text-cyan-400 italic' : 'text-slate-100') }}">
                                                            {{ $t1['name'] ?? 'Menunggu Pemenang' }}
                                                        </div>
                                                        @if(!empty($t1['institution']))
                                                            <div class="text-[10px] text-slate-400 truncate">
                                                                {{ $t1['institution'] }}
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>

                                                <div class="flex-shrink-0 flex items-center gap-1.5 pl-2 font-mono text-xs font-bold">
                                                    @if($existing)
                                                        <div class="flex items-center gap-1 text-[11px]">
                                                            @if($existing->team1_set1 > 0 || $existing->team2_set1 > 0)
                                                                <span class="{{ $existing->team1_set1 > $existing->team2_set1 ? 'text-emerald-400 font-bold' : 'text-slate-400' }}">{{ $existing->team1_set1 }}</span>
                                                            @endif
                                                            @if($existing->team1_set2 > 0 || $existing->team2_set2 > 0)
                                                                <span class="text-slate-600">/</span>
                                                                <span class="{{ $existing->team1_set2 > $existing->team2_set2 ? 'text-emerald-400 font-bold' : 'text-slate-400' }}">{{ $existing->team1_set2 }}</span>
                                                            @endif
                                                            @if($existing->team1_set3 > 0 || $existing->team2_set3 > 0)
                                                                <span class="text-slate-600">/</span>
                                                                <span class="{{ $existing->team1_set3 > $existing->team2_set3 ? 'text-emerald-400 font-bold' : 'text-slate-400' }}">{{ $existing->team1_set3 }}</span>
                                                            @endif
                                                        </div>
                                                    @endif
                                                    @if($isT1Winner)
                                                        <i data-lucide="check-circle" class="w-3.5 h-3.5 text-emerald-400"></i>
                                                    @endif
                                                </div>
                                            </div>

                                            <!-- Divider line -->
                                            <div class="relative flex items-center justify-center py-0.5">
                                                <div class="w-full border-t border-slate-800/80"></div>
                                                <span class="absolute px-2 bg-slate-900 text-[9px] font-mono font-bold text-slate-500">VS</span>
                                            </div>

                                            <!-- Team 2 -->
                                            @php
                                                $isT2Winner = false;
                                                if ($match['winner'] && ($t2['id'] ?? null) && ($match['winner']['id'] ?? null) === $t2['id']) {
                                                    $isT2Winner = true;
                                                }
                                                if ($isByeAdvance && !($t2['is_bye'] ?? false)) {
                                                    $isT2Winner = true;
                                                }
                                            @endphp
                                            <div class="flex items-center justify-between gap-2 p-2.5 rounded-xl transition
                                                {{ $isT2Winner ? 'bg-emerald-500/15 border border-emerald-500/30' : (($t2['is_bye'] ?? false) ? 'bg-cyan-950/20 border border-cyan-500/20 opacity-70' : 'bg-slate-950/40 border border-transparent') }}">
                                                <div class="flex items-center gap-2.5 min-w-0">
                                                    <div class="flex-shrink-0 flex items-center gap-1">
                                                        @if(isset($t2['slot_number']))
                                                            <span class="w-5 h-5 rounded-md bg-slate-800 text-slate-400 font-mono text-[10px] font-bold flex items-center justify-center">
                                                                {{ $t2['slot_number'] }}
                                                            </span>
                                                        @endif
                                                        @if(!empty($t2['seed_number']))
                                                            <span class="px-1.5 py-0.5 rounded bg-amber-500/20 text-amber-300 border border-amber-500/40 text-[9px] font-black">
                                                                S{{ $t2['seed_number'] }}
                                                            </span>
                                                        @elseif(!empty($t2['draw_number']))
                                                            <span class="px-1.5 py-0.5 rounded bg-slate-800 text-slate-400 text-[9px] font-mono">
                                                                #{{ $t2['draw_number'] }}
                                                            </span>
                                                        @endif
                                                    </div>

                                                    <div class="min-w-0">
                                                        <div class="text-xs font-bold truncate {{ $isT2Winner ? 'text-emerald-300 font-black' : (($t2['is_bye'] ?? false) ? 'text-cyan-400 italic' : 'text-slate-100') }}">
                                                            {{ $t2['name'] ?? 'Menunggu Pemenang' }}
                                                        </div>
                                                        @if(!empty($t2['institution']))
                                                            <div class="text-[10px] text-slate-400 truncate">
                                                                {{ $t2['institution'] }}
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>

                                                <div class="flex-shrink-0 flex items-center gap-1.5 pl-2 font-mono text-xs font-bold">
                                                    @if($existing)
                                                        <div class="flex items-center gap-1 text-[11px]">
                                                            @if($existing->team1_set1 > 0 || $existing->team2_set1 > 0)
                                                                <span class="{{ $existing->team2_set1 > $existing->team1_set1 ? 'text-emerald-400 font-bold' : 'text-slate-400' }}">{{ $existing->team2_set1 }}</span>
                                                            @endif
                                                            @if($existing->team1_set2 > 0 || $existing->team2_set2 > 0)
                                                                <span class="text-slate-600">/</span>
                                                                <span class="{{ $existing->team2_set2 > $existing->team1_set2 ? 'text-emerald-400 font-bold' : 'text-slate-400' }}">{{ $existing->team2_set2 }}</span>
                                                            @endif
                                                            @if($existing->team1_set3 > 0 || $existing->team2_set3 > 0)
                                                                <span class="text-slate-600">/</span>
                                                                <span class="{{ $existing->team2_set3 > $existing->team1_set3 ? 'text-emerald-400 font-bold' : 'text-slate-400' }}">{{ $existing->team2_set3 }}</span>
                                                            @endif
                                                        </div>
                                                    @endif
                                                    @if($isT2Winner)
                                                        <i data-lucide="check-circle" class="w-3.5 h-3.5 text-emerald-400"></i>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        @if($existing && $existing->court_number && strtoupper($existing->court_number) !== 'BYE')
                                            <div class="px-3 py-1.5 bg-slate-950/80 border-t border-slate-800 text-[10px] text-slate-400 flex items-center justify-between font-mono">
                                                <span>{{ $existing->court_number }} {{ $existing->scheduled_time ? '• ' . $existing->scheduled_time : '' }}</span>
                                                <a href="{{ route('badminton.scoreboard', $existing->id) }}" target="_blank" class="text-amber-400 hover:text-amber-300 font-bold flex items-center gap-1">
                                                    <span>Lihat Skor »</span>
                                                </a>
                                            </div>
                                        @endif

                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach

                    <!-- Champion Podium Card -->
                    <div class="flex flex-col min-w-[260px] max-w-[280px]">
                        <div class="mb-5 text-center">
                            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gradient-to-r from-amber-500/20 to-yellow-500/20 border border-amber-500/30 shadow-md">
                                <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                                <span class="text-xs font-black text-amber-300 tracking-wide uppercase">Juara Turnamen</span>
                            </div>
                        </div>

                        <div class="flex-1 flex flex-col justify-center py-2">
                            <div class="bg-gradient-to-b from-amber-500/15 via-slate-900 to-slate-900 rounded-3xl border border-amber-500/40 p-6 text-center shadow-2xl relative overflow-hidden">
                                <div class="w-20 h-20 rounded-3xl bg-gradient-to-br from-amber-400 to-yellow-600 text-slate-950 flex items-center justify-center mx-auto mb-4 shadow-xl shadow-amber-500/30">
                                    <i data-lucide="trophy" class="w-10 h-10"></i>
                                </div>

                                @if(!empty($bracketData['champion']))
                                    <span class="px-3 py-1 rounded-full bg-amber-500/20 text-amber-300 font-black text-[10px] uppercase tracking-wider border border-amber-500/40">
                                        🏆 JUARA 1
                                    </span>
                                    <h3 class="text-lg font-black text-white mt-3 leading-tight">
                                        {{ $bracketData['champion']['name'] }}
                                    </h3>
                                    <p class="text-xs text-amber-400/80 font-bold mt-1">
                                        {{ $bracketData['champion']['institution'] ?? '' }}
                                    </p>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-md bg-slate-800 text-slate-400 font-bold text-[10px] uppercase tracking-wider">
                                        Menunggu Final
                                    </span>
                                    <h4 class="text-sm font-bold text-slate-400 mt-2">
                                        Pemenang Babak Final
                                    </h4>
                                    <p class="text-[11px] text-slate-500 mt-1">
                                        Akan ditentukan di partai puncak
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        @endif

    </main>

    <!-- Footer with Auto-refresh status -->
    <footer class="bg-slate-900/80 border-t border-slate-800/80 px-6 py-3 text-xs text-slate-500 flex items-center justify-between">
        <div>
            <span>Sistem Bagan Turnamen Talenta &copy; {{ date('Y') }}</span>
        </div>
        <div class="flex items-center gap-3">
            <span class="flex items-center gap-1.5 text-slate-400 font-mono text-[11px]">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                Pembaruan Otomatis (60 detik)
            </span>
        </div>
    </footer>

    <script>
        // Auto refresh every 60 seconds for live tournament updates
        setTimeout(function() {
            window.location.reload();
        }, 60000);

        function toggleFullScreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch(err => {
                    console.error('Error attempting to enable full-screen mode:', err.message);
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
