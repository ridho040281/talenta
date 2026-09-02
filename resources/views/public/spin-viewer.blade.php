<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Layar Undian Nomor Tampil - {{ $competition->name }} | {{ $appSettings['app_name'] ?? 'TALENTA' }}</title>
    
    <!-- Favicon -->
    @if(!empty($appSettings['favicon']))
        <link rel="icon" type="image/png" href="{{ asset('storage/' . $appSettings['favicon']) }}">
        <link rel="shortcut icon" href="{{ asset('storage/' . $appSettings['favicon']) }}">
        <link rel="apple-touch-icon" href="{{ asset('storage/' . $appSettings['favicon']) }}">
    @else
        <link rel="icon" href="{{ asset('favicon.ico') }}">
    @endif

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Vite Local Tailwind CSS & JS Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-slate-950 text-slate-100 font-sans antialiased min-h-screen flex flex-col">

    <header class="bg-slate-900 border-b border-slate-800 p-6 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                @if(!empty($appSettings['app_logo']))
                    <img src="{{ asset('storage/' . $appSettings['app_logo']) }}" alt="{{ $appSettings['app_name'] ?? 'Logo' }}" class="h-10 w-auto max-w-[140px] object-contain group-hover:scale-105 transition">
                @else
                    <div class="w-10 h-10 rounded-xl bg-amber-400 text-slate-950 flex items-center justify-center font-black group-hover:scale-105 transition">
                        <i data-lucide="trophy" class="w-5 h-5"></i>
                    </div>
                @endif
            </a>
            <div>
                <h1 class="text-lg font-black text-white">HASIL UNDIAN NOMOR TAMPIL</h1>
                <p class="text-xs text-amber-400 font-bold">{{ $competition->name }} ({{ $appSettings['app_name'] ?? 'TALENTA' }})</p>
            </div>
        </div>
        <a href="{{ route('live.scoreboard', $competition->slug) }}" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-bold text-slate-300 border border-slate-700 transition">
            Ke Papan Skor Live
        </a>
    </header>

    <main class="flex-1 max-w-6xl w-full mx-auto p-6 sm:p-10 space-y-8">
        
        <div class="text-center space-y-2">
            <span class="text-xs font-black tracking-widest text-emerald-400 uppercase">Daftar Urutan Tampil Peserta</span>
            <h2 class="text-3xl font-black text-white">Hasil Drawing / Spin Wheel Resmi</h2>
            <p class="text-xs text-slate-400">Nomor tampil menentukan giliran maju di panggung perlombaan</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($participants->sortBy('draw_number') as $p)
                <div class="p-5 rounded-2xl border transition {{ $p['has_draw'] ? 'bg-slate-900 border-emerald-500/40' : 'bg-slate-900/40 border-slate-800 opacity-60' }} flex items-center gap-4">
                    <div class="w-14 h-14 rounded-2xl {{ $p['has_draw'] ? 'bg-gradient-to-tr from-amber-500 to-amber-300 text-slate-950 font-black shadow-lg shadow-amber-500/20 text-2xl' : 'bg-slate-800 text-slate-500 text-sm font-bold' }} flex items-center justify-center shrink-0">
                        {{ $p['has_draw'] ? $p['draw_number'] : '?' }}
                    </div>
                    <div class="overflow-hidden">
                        <h4 class="font-bold text-white text-sm truncate">{{ $p['name'] }}</h4>
                        <p class="text-xs text-slate-400 truncate">{{ $p['institution'] }}</p>
                        <span class="text-[10px] font-bold uppercase tracking-wider {{ $p['has_draw'] ? 'text-emerald-400' : 'text-slate-500' }}">
                            {{ $p['has_draw'] ? 'Nomor Tampil #' . $p['draw_number'] . ' • Terkunci' : 'Menunggu Undian' }}
                        </span>
                    </div>
                </div>
            @empty
                <div class="col-span-3 text-center py-12 text-slate-500">
                    Belum ada data peserta untuk cabang lomba ini.
                </div>
            @endforelse
        </div>

    </main>

    <footer class="bg-slate-900 border-t border-slate-800 py-4 text-center text-xs text-slate-500">
        TALENTA Drawing Monitor • MTsN 1 Blitar
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
            // Polling refresh every 10 seconds for live draw monitoring
            setTimeout(() => {
                window.location.reload();
            }, 10000);
        });
    </script>
</body>
</html>
