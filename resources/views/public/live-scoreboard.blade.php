<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LIVE SCOREBOARD - {{ $appSettings['app_name'] ?? 'TALENTA' }} {{ $appSettings['institution_name'] ?? 'MTsN 1 BLITAR' }}</title>
    
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
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Vite Local Tailwind CSS & JS Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        @keyframes pulse-slow {
            0%, 100% { opacity: 1; }
            50% { opacity: .6; }
        }
        .animate-pulse-slow {
            animation: pulse-slow 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 font-sans antialiased min-h-screen flex flex-col selection:bg-amber-500 selection:text-black">

    <!-- Top Scoreboard Header Bar (Mobile Responsive) -->
    <header class="bg-slate-900/90 backdrop-blur-md border-b border-slate-800 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-3.5 sm:px-6 lg:px-8 py-3 sm:py-0 sm:h-20 flex flex-col sm:flex-row items-center justify-between gap-3">
            
            <div class="flex items-center justify-between w-full sm:w-auto gap-3">
                <div class="flex items-center gap-3">
                    <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                        @if(!empty($appSettings['app_logo']))
                            <img src="{{ asset('storage/' . $appSettings['app_logo']) }}" alt="{{ $appSettings['app_name'] ?? 'Logo' }}" class="h-9 sm:h-11 w-auto max-w-[140px] object-contain group-hover:scale-105 transition">
                        @else
                            <div class="w-9 h-9 sm:w-11 sm:h-11 rounded-xl sm:rounded-2xl bg-gradient-to-tr from-amber-500 to-amber-300 flex items-center justify-center text-slate-950 font-black shadow-lg shadow-amber-500/20 group-hover:scale-105 transition shrink-0">
                                <i data-lucide="trophy" class="w-5 h-5 sm:w-6 sm:h-6"></i>
                            </div>
                        @endif
                    </a>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-base sm:text-xl font-black tracking-tight text-white">LIVE SCOREBOARD</span>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] sm:text-[10px] font-black bg-rose-500/20 text-rose-400 border border-rose-500/30 shrink-0">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-ping"></span>
                                <span>LIVE</span>
                            </span>
                        </div>
                        <p class="text-[10px] sm:text-xs text-slate-400 font-semibold">{{ $appSettings['app_name'] ?? 'TALENTA 2026' }} • {{ $appSettings['institution_name'] ?? 'MTsN 1 BLITAR' }}</p>
                    </div>
                </div>

                <a href="{{ auth()->check() ? (auth()->user()->role === 'peserta' ? route('peserta.dashboard') : route('admin.dashboard')) : route('home') }}" class="sm:hidden px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white text-xs font-bold border border-slate-700 transition shrink-0">
                    Kembali
                </a>
            </div>

            <!-- Controls: Competition Selector & Auto Refresh -->
            <div class="flex items-center justify-between w-full sm:w-auto gap-2" x-data="{ autoRefresh: true, timer: 15, isFullscreen: false }" x-init="
                if (autoRefresh) {
                    setInterval(() => {
                        if (timer > 0) timer--;
                        else {
                            window.location.reload();
                        }
                    }, 1000);
                }
            ">
                
                <!-- Competition Selector Dropdown (Direct Competition List Without Category & Code) -->
                <div class="flex-1 sm:flex-initial flex items-center gap-1.5">
                    <select onchange="window.location.href='{{ url('/live-scoreboard') }}/' + this.value" class="w-full sm:w-auto bg-slate-800 hover:bg-slate-750 text-amber-300 text-xs sm:text-sm font-bold px-3 py-2 rounded-xl border border-slate-700 focus:ring-2 focus:ring-amber-400 outline-none cursor-pointer truncate max-w-[240px] sm:max-w-xs md:max-w-none">
                        @foreach($competitions as $c)
                            <option value="{{ $c->slug }}" class="bg-slate-800 text-white font-medium" {{ ($selectedCompetition && $selectedCompetition->id === $c->id) ? 'selected' : '' }}>
                                {{ $c->is_live_score ? '🔴' : '🔒' }} {{ $c->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Auto Refresh Badge / Timer -->
                <div class="hidden lg:flex items-center gap-1.5 px-3 py-2 rounded-xl bg-slate-800/80 border border-slate-700 text-xs text-slate-300" title="Skor otomatis diperbarui">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span class="font-mono text-[11px]" x-text="'Auto (' + timer + 's)'"></span>
                </div>

                <!-- Fullscreen TV Mode Button -->
                <button @click="
                    if (!document.fullscreenElement) {
                        document.documentElement.requestFullscreen();
                        isFullscreen = true;
                    } else {
                        if (document.exitFullscreen) {
                            document.exitFullscreen();
                            isFullscreen = false;
                        }
                    }
                " class="p-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white border border-slate-700 transition cursor-pointer" title="Layar Penuh (TV / Proyektor)">
                    <i data-lucide="maximize" class="w-4 h-4 text-amber-400"></i>
                </button>

                <!-- Refresh Button -->
                <button onclick="window.location.reload()" class="p-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white border border-slate-700 transition cursor-pointer" title="Refresh Manual">
                    <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                </button>

                <a href="{{ auth()->check() ? (auth()->user()->role === 'peserta' ? route('peserta.dashboard') : route('admin.dashboard')) : route('home') }}" class="px-3 sm:px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white text-xs font-bold border border-slate-700 transition">
                    Kembali
                </a>

            </div>

        </div>
    </header>

    <!-- Main Scoreboard Workspace -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
        
        @if($selectedCompetition)
            <!-- Active Competition Info Header -->
            <div class="bg-gradient-to-r from-slate-900 via-slate-900/90 to-emerald-950 p-6 sm:p-8 rounded-3xl border border-slate-800 shadow-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-black uppercase tracking-widest text-emerald-400">Papan Skor Resmi</span>
                        @if($selectedCompetition->is_live_score)
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-ping"></span>
                                LIVE SCORE AKTIF
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                                <i data-lucide="lock" class="w-3 h-3"></i>
                                MODE RAHASIA
                            </span>
                        @endif
                    </div>
                    <h1 class="text-2xl sm:text-4xl font-black text-white tracking-tight">{{ $selectedCompetition->name }}</h1>
                    <p class="text-xs text-slate-400 flex items-center gap-2 pt-1">
                        <i data-lucide="map-pin" class="w-3.5 h-3.5 text-emerald-400"></i>
                        <span>{{ $selectedCompetition->venue ?? 'Arena MTsN 1 Blitar' }}</span>
                        <span>•</span>
                        <span>Total Peserta: {{ $leaderboard->count() }}</span>
                    </p>
                </div>

                <!-- Live Spin Viewer Link -->
                <div class="shrink-0 flex items-center gap-3">
                    <a href="{{ route('spin.viewer', $selectedCompetition->slug) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs transition shadow-lg shadow-amber-500/20">
                        <i data-lucide="disc" class="w-4 h-4"></i>
                        <span>Layar Undian Spin Wheel</span>
                    </a>
                </div>
            </div>

            @if(!$selectedCompetition->is_live_score)
                <!-- Confidential Mode Notice Banner -->
                <div class="bg-slate-900/90 border-2 border-amber-500/30 rounded-3xl p-8 sm:p-14 text-center space-y-4 shadow-2xl">
                    <div class="w-16 h-16 rounded-2xl bg-amber-500/15 border border-amber-500/30 flex items-center justify-center mx-auto text-amber-400 shadow-lg shadow-amber-500/10">
                        <i data-lucide="lock" class="w-8 h-8"></i>
                    </div>
                    <div class="space-y-2 max-w-lg mx-auto">
                        <h2 class="text-xl sm:text-2xl font-black text-white">Live Score Cabang Ini Bersifat Rahasia / Tertutup</h2>
                        <p class="text-xs sm:text-sm text-slate-400 leading-relaxed">
                            Hasil penilaian dewan juri untuk cabang lomba <strong class="text-amber-300">{{ $selectedCompetition->name }}</strong> tidak disiarkan secara publik saat ini. Rekapitulasi nilai dan pengumuman juara resmi akan disampaikan pada sesi penutupan (*Awarding Ceremony*).
                        </p>
                    </div>
                </div>
            @else
                <!-- Top 3 Podium (If Scores Exist) -->
                @php
                    $scoredItems = $leaderboard->where('has_score', true)->values();
                @endphp

                @if($scoredItems->count() >= 3)
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-4">
                        
                        <!-- Juara 2 (Perak) -->
                        <div class="bg-slate-900/90 border border-slate-700/80 rounded-3xl p-6 text-center space-y-3 relative order-2 md:order-1 mt-4 md:mt-8">
                            <div class="w-12 h-12 mx-auto rounded-2xl bg-slate-300 text-slate-900 flex items-center justify-center font-black text-lg shadow-lg">
                                🥈 2
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-white">{{ $scoredItems[1]['display_name'] }}</h3>
                                <p class="text-xs text-slate-400">{{ $scoredItems[1]['institution_name'] }}</p>
                            </div>
                            <div class="pt-2">
                                <span class="text-3xl font-black text-slate-200">{{ number_format($scoredItems[1]['total_score'], 2) }}</span>
                                <span class="text-[10px] text-slate-400 block font-semibold uppercase">Poin Akhir</span>
                            </div>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-white">{{ $scoredItems[1]['display_name'] }}</h3>
                            <p class="text-xs text-slate-400">{{ $scoredItems[1]['institution_name'] }}</p>
                        </div>
                        <div class="pt-2">
                            <span class="text-3xl font-black text-slate-200">{{ number_format($scoredItems[1]['total_score'], 2) }}</span>
                            <span class="text-[10px] text-slate-400 block font-semibold uppercase">Poin Akhir</span>
                        </div>
                    </div>

                    <!-- Juara 1 (Emas - Center Highlight) -->
                    <div class="bg-gradient-to-b from-amber-500/20 via-slate-900 to-slate-900 border-2 border-amber-400 rounded-3xl p-8 text-center space-y-4 relative order-1 md:order-2 shadow-2xl shadow-amber-500/10">
                        <div class="w-16 h-16 mx-auto rounded-2xl bg-amber-400 text-slate-950 flex items-center justify-center font-black text-2xl shadow-xl shadow-amber-400/30">
                            🥇 1
                        </div>
                        <div>
                            <span class="px-3 py-1 rounded-full bg-amber-400/20 text-amber-300 text-[10px] font-black uppercase tracking-wider">Pemimpin Klasemen</span>
                            <h3 class="text-xl font-black text-white mt-1.5">{{ $scoredItems[0]['display_name'] }}</h3>
                            <p class="text-xs text-slate-300">{{ $scoredItems[0]['institution_name'] }}</p>
                        </div>
                        <div class="pt-2">
                            <span class="text-4xl sm:text-5xl font-black text-amber-400">{{ number_format($scoredItems[0]['total_score'], 2) }}</span>
                            <span class="text-xs text-amber-200/80 block font-bold uppercase tracking-wider">Poin Tertinggi</span>
                        </div>
                    </div>

                    <!-- Juara 3 (Perunggu) -->
                    <div class="bg-slate-900/90 border border-slate-700/80 rounded-3xl p-6 text-center space-y-3 relative order-3 mt-4 md:mt-8">
                        <div class="w-12 h-12 mx-auto rounded-2xl bg-amber-700 text-amber-100 flex items-center justify-center font-black text-lg shadow-lg">
                            🥉 3
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-white">{{ $scoredItems[2]['display_name'] }}</h3>
                            <p class="text-xs text-slate-400">{{ $scoredItems[2]['institution_name'] }}</p>
                        </div>
                        <div class="pt-2">
                            <span class="text-3xl font-black text-amber-600">{{ number_format($scoredItems[2]['total_score'], 2) }}</span>
                            <span class="text-[10px] text-slate-400 block font-semibold uppercase">Poin Akhir</span>
                        </div>
                    </div>

                </div>
            @endif

            <!-- Full Scoreboard Table -->
            <div class="bg-slate-900 rounded-3xl border border-slate-800 shadow-2xl overflow-hidden">
                <div class="p-5 bg-slate-800/60 border-b border-slate-700/80 flex items-center justify-between">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-white flex items-center gap-2">
                        <i data-lucide="list-ordered" class="w-4 h-4 text-amber-400"></i>
                        <span>Klasemen & Rekap Perolehan Nilai</span>
                    </h3>
                    <span class="text-xs text-slate-400 font-semibold">Update otomatis setiap 15 detik</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-300">
                        <thead class="text-xs font-black uppercase tracking-wider bg-slate-950/70 text-slate-400 border-b border-slate-800">
                            <tr>
                                <th class="py-4 px-6 text-center w-16">Peringkat</th>
                                <th class="py-4 px-4 text-center w-24">No. Tampil</th>
                                <th class="py-4 px-4 w-28">No. Dada</th>
                                <th class="py-4 px-6">Nama Peserta / Tim</th>
                                <th class="py-4 px-6">Asal Sekolah / Kontingen</th>
                                <th class="py-4 px-6 text-right w-36">Total Skor</th>
                                <th class="py-4 px-6 text-center w-32">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/80 font-medium">
                            @forelse($leaderboard as $index => $item)
                                <tr class="hover:bg-slate-800/40 transition {{ $index === 0 && $item['has_score'] ? 'bg-amber-500/5' : '' }}">
                                    
                                    <!-- Rank -->
                                    <td class="py-4 px-6 text-center">
                                        @if($item['has_score'])
                                            @if($index === 0)
                                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-amber-400 text-slate-950 font-black text-sm shadow-md shadow-amber-400/30">1</span>
                                            @elseif($index === 1)
                                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-slate-300 text-slate-950 font-black text-sm">2</span>
                                            @elseif($index === 2)
                                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-amber-700 text-white font-black text-sm">3</span>
                                            @else
                                                <span class="text-slate-500 font-bold">{{ $index + 1 }}</span>
                                            @endif
                                        @else
                                            <span class="text-slate-600 font-bold">-</span>
                                        @endif
                                    </td>

                                    <!-- Draw Number -->
                                    <td class="py-4 px-4 text-center">
                                        @if($item['draw_number'] && $item['draw_number'] < 999)
                                            <span class="inline-block px-2.5 py-1 rounded-lg bg-slate-800 text-emerald-400 font-mono font-bold text-xs border border-emerald-500/30">
                                                #{{ $item['draw_number'] }}
                                            </span>
                                        @else
                                            <span class="text-slate-600 text-xs">Belum diundi</span>
                                        @endif
                                    </td>

                                    <!-- Participant ID -->
                                    <td class="py-4 px-4 font-mono font-bold text-xs text-slate-400">
                                        {{ $item['participant_number'] }}
                                    </td>

                                    <!-- Name -->
                                    <td class="py-4 px-6 font-bold text-white text-base">
                                        {{ $item['display_name'] }}
                                    </td>

                                    <!-- School -->
                                    <td class="py-4 px-6 text-slate-400 text-xs">
                                        {{ $item['institution_name'] }}
                                    </td>

                                    <!-- Score -->
                                    <td class="py-4 px-6 text-right">
                                        @if($item['has_score'])
                                            <span class="text-xl font-black {{ $index === 0 ? 'text-amber-400' : 'text-white' }}">
                                                {{ number_format($item['total_score'], 2) }}
                                            </span>
                                        @else
                                            <span class="text-xs text-slate-600 italic">Menunggu giliran</span>
                                        @endif
                                    </td>

                                    <!-- Status -->
                                    <td class="py-4 px-6 text-center">
                                        @if($item['has_score'])
                                            <span class="px-3 py-1 rounded-full text-[11px] font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                                                Terkunci
                                            </span>
                                        @else
                                            <span class="px-3 py-1 rounded-full text-[11px] font-bold bg-slate-800 text-slate-500 border border-slate-700">
                                                Belum Tampil
                                            </span>
                                        @endif
                                    </td>

                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-12 text-center text-slate-500">
                                        Belum ada peserta yang terverifikasi pada cabang lomba ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

        @else
            <div class="bg-slate-900 p-16 rounded-3xl text-center space-y-4">
                <i data-lucide="trophy" class="w-12 h-12 text-slate-600 mx-auto"></i>
                <h3 class="text-xl font-bold text-white">Belum Ada Cabang Lomba Dipilih</h3>
                <p class="text-sm text-slate-400">Silakan pilih cabang lomba dari menu untuk memuat papan skor.</p>
            </div>
        @endif

    </main>

    <footer class="bg-slate-900 border-t border-slate-800 py-6 text-center text-xs text-slate-500">
        TALENTA Live Scoreboard Engine • MTsN 1 Blitar
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
            // Auto reload every 15 seconds for live venue update
            setTimeout(() => {
                window.location.reload();
            }, 15000);
        });
    </script>
</body>
</html>
