<!DOCTYPE html>
<html lang="id" class="dark scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', ($appSettings['app_name'] ?? 'TALENTA') . ' | ' . ($appSettings['institution_name'] ?? 'MTs Negeri 1 Blitar'))</title>
    <meta name="description" content="Aplikasi Pendaftaran & Manajemen Perlombaan {{ $appSettings['app_name'] ?? 'TALENTA' }} {{ $appSettings['institution_name'] ?? 'MTs Negeri 1 Blitar' }}.">
    
    <!-- Favicon -->
    @if(!empty($appSettings['favicon']))
        <link rel="icon" type="image/png" href="{{ asset('storage/' . $appSettings['favicon']) }}">
        <link rel="shortcut icon" href="{{ asset('storage/' . $appSettings['favicon']) }}">
        <link rel="apple-touch-icon" href="{{ asset('storage/' . $appSettings['favicon']) }}">
    @else
        <link rel="icon" href="{{ asset('favicon.ico') }}">
    @endif

    <!-- Preconnect CDNs for faster loading -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=Space+Grotesk:wght@500;700&family=Space+Mono:wght@700&display=swap" rel="stylesheet">
    
    <!-- Vite Local Tailwind CSS & JS Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Lucide Icons -->
    <script src="https://cdn.jsdelivr.net/npm/lucide@0.344.0/dist/umd/lucide.min.js"></script>
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Canvas Confetti -->
    <script defer src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        
        html, body {
            overflow-x: hidden !important;
            max-width: 100vw !important;
            width: 100% !important;
        }

        /* Hide scrollbars for chrome, safari and opera */
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        /* Hide scrollbar for IE, Edge and Firefox */
        .no-scrollbar {
            -ms-overflow-style: none;  /* IE and Edge */
            scrollbar-width: none;  /* Firefox */
        }

        /* Make date/time input calendar icons bright and visible in dark mode */
        input[type="date"], input[type="time"], input[type="datetime-local"] {
            color-scheme: dark;
        }
        input[type="date"]::-webkit-calendar-picker-indicator,
        input[type="time"]::-webkit-calendar-picker-indicator,
        input[type="datetime-local"]::-webkit-calendar-picker-indicator {
            filter: invert(1) brightness(1.2);
            cursor: pointer;
            opacity: 0.85;
            padding: 2px;
            border-radius: 4px;
            transition: opacity 0.2s, filter 0.2s;
        }
        input[type="date"]::-webkit-calendar-picker-indicator:hover,
        input[type="time"]::-webkit-calendar-picker-indicator:hover,
        input[type="datetime-local"]::-webkit-calendar-picker-indicator:hover {
            opacity: 1;
            filter: invert(1) brightness(1.5) drop-shadow(0 0 4px #7A5AF8);
        }

        /* ==========================================================================
           AI STARTER KIT (THEMEWAGON AISTARTERKIT) DESIGN SYSTEM TOKENS & GRADIENTS
           ========================================================================== */
        body {
            background-color: #141c2e;
            background-image: 
                radial-gradient(at 15% 15%, rgba(78, 110, 255, 0.22) 0px, transparent 55%),
                radial-gradient(at 85% 10%, rgba(122, 90, 248, 0.20) 0px, transparent 50%),
                radial-gradient(at 50% 50%, rgba(30, 41, 59, 0.5) 0px, transparent 70%),
                radial-gradient(at 75% 85%, rgba(255, 88, 213, 0.12) 0px, transparent 55%),
                radial-gradient(at 20% 80%, rgba(16, 185, 129, 0.10) 0px, transparent 50%),
                linear-gradient(180deg, #182338 0%, #131b2e 50%, #0e1524 100%);
            background-attachment: fixed;
            color: #F8FAFC;
        }

        /* Ambient AI Aurora Glow Effects */
        .ambient-glow-1, .ai-glow-1 {
            position: absolute;
            width: 450px;
            max-width: 80vw;
            height: 450px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(78, 110, 255, 0.32) 0%, rgba(122, 90, 248, 0.15) 45%, transparent 70%);
            filter: blur(100px);
            pointer-events: none;
            z-index: 0;
        }

        .ambient-glow-2, .ai-glow-2 {
            position: absolute;
            width: 450px;
            max-width: 80vw;
            height: 450px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255, 88, 213, 0.22) 0%, rgba(122, 90, 248, 0.10) 50%, transparent 70%);
            filter: blur(100px);
            pointer-events: none;
            z-index: 0;
        }

        .ambient-glow-3, .ai-glow-3 {
            position: absolute;
            width: 400px;
            max-width: 80vw;
            height: 400px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(56, 189, 248, 0.22) 0%, rgba(78, 110, 255, 0.10) 50%, transparent 70%);
            filter: blur(90px);
            pointer-events: none;
            z-index: 0;
        }

        /* AI Starter Kit Glass Surfaces */
        .glass-card, .ai-card {
            background: rgba(22, 31, 48, 0.88);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.10);
            box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.5), 0 0 25px rgba(78, 110, 255, 0.06);
        }

        .glass-nav, .ai-nav {
            background: rgba(16, 24, 40, 0.88);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.10);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);
        }

        /* Gradient Texts & Buttons */
        .text-gradient, .ai-gradient-text {
            background: linear-gradient(135deg, #FFFFFF 0%, #E0EAFF 50%, #C7D2FE 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .btn-gradient, .btn-gradient-ai, .gradient-btn {
            background: linear-gradient(90deg, #7A5AF8 0%, #4E6EFF 100%) !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            color: #ffffff !important;
        }
        .btn-gradient:hover, .btn-gradient-ai:hover, .gradient-btn:hover {
            background: linear-gradient(90deg, #6941C6 0%, #3555EC 100%) !important;
            box-shadow: 0 0 25px rgba(122, 90, 248, 0.4);
            transform: translateY(-1px);
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: rgba(12, 17, 29, 0.5);
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 9999px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: rgba(122, 90, 248, 0.5);
        }
    </style>
</head>
<body class="text-slate-100 font-sans antialiased min-h-screen flex flex-col selection:bg-[#7A5AF8] selection:text-white relative overflow-x-hidden w-full max-w-full" x-data="{ mobileMenu: false }">

    <!-- Global Ambient Glow Orbs in Contained Box (Prevents Mobile Viewport Overflow) -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none z-0 w-full h-full max-w-[100vw]">
        <div class="ai-glow-1 top-0 right-0"></div>
        <div class="ai-glow-2 top-1/4 left-0"></div>
        <div class="ai-glow-3 top-2/3 right-0"></div>
        <div class="ai-glow-1 bottom-10 left-0"></div>
    </div>

    <!-- Header Navigation (Fully Responsive & Clean Desktop/Mobile) -->
    <header class="sticky top-0 z-50 glass-nav transition-all duration-300 w-full">
        <div class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20 gap-4">
                
                <!-- Logo & Brand (Clean, Non-wrapping) -->
                <a href="{{ route('home') }}" class="flex items-center gap-3 shrink-0 group">
                    @if(!empty($appSettings['app_logo']))
                        <img src="{{ asset('storage/' . $appSettings['app_logo']) }}" alt="{{ $appSettings['app_name'] ?? 'TALENTA' }}" class="h-10 sm:h-11 w-auto max-w-[48px] object-contain group-hover:scale-105 transition-transform duration-300">
                    @else
                        <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-2xl bg-gradient-to-tr from-[#7A5AF8] to-[#4E6EFF] flex items-center justify-center text-white shadow-lg shadow-[#7A5AF8]/30 group-hover:scale-105 transition-transform duration-300">
                            <i data-lucide="sparkles" class="w-5 h-5 sm:w-6 sm:h-6"></i>
                        </div>
                    @endif
                    <div class="flex flex-col">
                        <div class="flex items-center gap-2">
                            <span class="text-xl sm:text-2xl font-black tracking-tight text-white font-display">{{ $appSettings['app_name'] ?? 'TALENTA' }}</span>
                        </div>
                        <p class="text-[9px] sm:text-[10px] font-bold text-[#A594FD] tracking-widest uppercase">{{ $appSettings['institution_name'] ?? 'MTsN 1 BLITAR' }}</p>
                    </div>
                </a>

                <!-- Desktop Nav Links (Spacious, Single Line, No Clutter) -->
                <nav class="hidden xl:flex items-center gap-6 font-medium text-xs lg:text-sm text-slate-300 whitespace-nowrap">
                    <a href="{{ route('home') }}" class="hover:text-[#A594FD] transition py-1">Beranda</a>
                    <a href="{{ route('home') }}#kategori" class="hover:text-[#A594FD] transition py-1">Cabang Lomba</a>
                    <a href="{{ route('home') }}#jadwal" class="hover:text-[#A594FD] transition py-1">Jadwal & Timeline</a>
                    <a href="{{ route('home') }}#cara-kerja" class="hover:text-[#A594FD] transition py-1">Alur & Syarat</a>
                    <a href="{{ route('check.status') }}" class="hover:text-[#A594FD] transition flex items-center gap-1.5 py-1">
                        <i data-lucide="search" class="w-3.5 h-3.5 text-[#7A5AF8]"></i>
                        <span>Cek Status</span>
                    </a>
                </nav>

                <!-- Desktop Action & Auth Area -->
                <div class="hidden xl:flex items-center gap-3 shrink-0">
                    <a href="{{ route('live.scoreboard') }}" class="px-3.5 py-2 rounded-xl bg-amber-400/10 text-amber-300 border border-amber-400/30 hover:bg-amber-400/20 font-bold text-xs transition flex items-center gap-1.5 shadow-sm whitespace-nowrap">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                        </span>
                        <span>Live Scoreboard TV</span>
                    </a>

                    @auth
                        @php
                            $dashboardRoute = match(auth()->user()->role) {
                                'superadmin' => route('admin.dashboard'),
                                'pic_lomba' => route('pic.dashboard'),
                                'juri' => route('juri.dashboard'),
                                default => route('peserta.dashboard'),
                            };
                            $roleLabel = match(auth()->user()->role) {
                                'superadmin' => 'Admin',
                                'pic_lomba' => 'PIC Lomba',
                                'juri' => 'Dewan Juri',
                                default => 'Peserta',
                            };
                        @endphp
                        <a href="{{ $dashboardRoute }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl btn-gradient text-white font-bold text-xs shadow-lg shadow-[#7A5AF8]/30 whitespace-nowrap">
                            <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                            <span>Dashboard ({{ $roleLabel }})</span>
                        </a>
                        <form action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="p-2.5 text-slate-400 hover:text-rose-400 hover:bg-white/[0.06] rounded-xl transition" title="Keluar">
                                <i data-lucide="log-out" class="w-4 h-4"></i>
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="px-4 py-2 text-xs font-bold text-slate-300 hover:text-white transition whitespace-nowrap">
                            Masuk
                        </a>
                        <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl btn-gradient text-white font-bold text-xs shadow-lg shadow-[#7A5AF8]/30 whitespace-nowrap">
                            <i data-lucide="user-plus" class="w-4 h-4"></i>
                            <span>Daftar Akun</span>
                        </a>
                    @endauth
                </div>

                <!-- Mobile Hamburger Button (Shows below XL screen size) -->
                <div class="flex items-center gap-2 xl:hidden">
                    <a href="{{ route('live.scoreboard') }}" class="p-2 rounded-xl bg-amber-400/10 text-amber-300 border border-amber-400/30" title="Scoreboard">
                        <i data-lucide="tv" class="w-4 h-4"></i>
                    </a>
                    <button @click="mobileMenu = !mobileMenu" type="button" class="p-2.5 rounded-xl bg-white/[0.06] border border-white/[0.1] text-slate-200 hover:text-white transition focus:outline-none" aria-label="Toggle Menu">
                        <i data-lucide="menu" class="w-5 h-5" x-show="!mobileMenu"></i>
                        <i data-lucide="x" class="w-5 h-5" x-show="mobileMenu" x-cloak></i>
                    </button>
                </div>

            </div>
        </div>

        <!-- Mobile Drawer Menu (Slide Down & Dropdown) -->
        <div x-show="mobileMenu" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-4" class="xl:hidden border-b border-white/[0.12] bg-[#090D17]/98 backdrop-blur-2xl px-6 py-6 space-y-4 shadow-2xl">
            
            <nav class="flex flex-col space-y-3 font-semibold text-sm">
                <a @click="mobileMenu = false" href="{{ route('home') }}" class="py-2 px-3 rounded-xl hover:bg-white/[0.06] text-slate-200 hover:text-[#A594FD] transition flex items-center gap-3">
                    <i data-lucide="home" class="w-4 h-4 text-[#7A5AF8]"></i>
                    <span>Beranda Utama</span>
                </a>
                <a @click="mobileMenu = false" href="{{ route('home') }}#kategori" class="py-2 px-3 rounded-xl hover:bg-white/[0.06] text-slate-200 hover:text-[#A594FD] transition flex items-center gap-3">
                    <i data-lucide="trophy" class="w-4 h-4 text-[#4E6EFF]"></i>
                    <span>Cabang Perlombaan</span>
                </a>
                <a @click="mobileMenu = false" href="{{ route('home') }}#jadwal" class="py-2 px-3 rounded-xl hover:bg-white/[0.06] text-slate-200 hover:text-[#A594FD] transition flex items-center gap-3">
                    <i data-lucide="calendar" class="w-4 h-4 text-amber-400"></i>
                    <span>Jadwal & Timeline</span>
                </a>
                <a @click="mobileMenu = false" href="{{ route('home') }}#cara-kerja" class="py-2 px-3 rounded-xl hover:bg-white/[0.06] text-slate-200 hover:text-[#A594FD] transition flex items-center gap-3">
                    <i data-lucide="list-ordered" class="w-4 h-4 text-[#FF58D5]"></i>
                    <span>Alur & Syarat Partisipasi</span>
                </a>
                <a @click="mobileMenu = false" href="{{ route('check.status') }}" class="py-2 px-3 rounded-xl hover:bg-white/[0.06] text-slate-200 hover:text-[#A594FD] transition flex items-center gap-3">
                    <i data-lucide="search" class="w-4 h-4 text-[#7A5AF8]"></i>
                    <span>Cek Status Verifikasi</span>
                </a>
                <a @click="mobileMenu = false" href="{{ route('live.scoreboard') }}" class="py-2 px-3 rounded-xl bg-amber-400/10 text-amber-300 border border-amber-400/20 flex items-center gap-3 font-bold">
                    <i data-lucide="tv" class="w-4 h-4 text-amber-400"></i>
                    <span>Live Scoreboard TV Venue</span>
                </a>
            </nav>

            <div class="pt-4 border-t border-white/[0.08] flex flex-col gap-2.5">
                @auth
                    @php
                        $dashboardRoute = match(auth()->user()->role) {
                            'superadmin' => route('admin.dashboard'),
                            'pic_lomba' => route('pic.dashboard'),
                            'juri' => route('juri.dashboard'),
                            default => route('peserta.dashboard'),
                        };
                    @endphp
                    <a href="{{ $dashboardRoute }}" class="w-full text-center py-3 rounded-xl btn-gradient text-white font-bold text-xs shadow-lg">
                        Buka Dashboard ({{ strtoupper(auth()->user()->role) }})
                    </a>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full py-2.5 rounded-xl bg-white/[0.04] text-slate-400 hover:text-rose-400 text-xs font-bold transition">
                            Keluar Akun
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="w-full text-center py-2.5 rounded-xl bg-white/[0.06] hover:bg-white/[0.1] text-white font-bold text-xs transition">
                        Masuk ke Akun
                    </a>
                    <a href="{{ route('register') }}" class="w-full text-center py-3 rounded-xl btn-gradient text-white font-bold text-xs shadow-lg">
                        Daftar Akun Baru
                    </a>
                @endauth
            </div>

        </div>
    </header>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full" x-data="{ show: true }" x-show="show">
            <div class="bg-[#161F30]/95 border border-[#7A5AF8]/40 text-slate-200 px-4 py-3 rounded-2xl flex items-center justify-between shadow-xl backdrop-blur-md">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-[#7A5AF8] to-[#4E6EFF] text-white flex items-center justify-center shrink-0 font-bold">
                        <i data-lucide="check" class="w-5 h-5"></i>
                    </div>
                    <span class="text-xs sm:text-sm font-medium">{{ session('success') }}</span>
                </div>
                <button @click="show = false" class="text-slate-400 hover:text-white">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full" x-data="{ show: true }" x-show="show">
            <div class="bg-[#161F30]/95 border border-rose-500/40 text-rose-200 px-4 py-3 rounded-2xl flex items-center justify-between shadow-xl backdrop-blur-md">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-rose-500 text-white flex items-center justify-center shrink-0 font-bold">
                        <i data-lucide="alert-circle" class="w-5 h-5"></i>
                    </div>
                    <span class="text-xs sm:text-sm font-medium">{{ session('error') }}</span>
                </div>
                <button @click="show = false" class="text-rose-400 hover:text-white">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
        </div>
    @endif

    <!-- Main Content Body -->
    <main class="flex-grow relative z-10">
        @yield('content')
    </main>

    <!-- Footer (AI Dark Glass Structure) -->
    <footer class="bg-[#080C15]/95 text-slate-400 border-t border-white/[0.08] pt-16 pb-12 mt-20 relative z-10 backdrop-blur-xl">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-10 mb-12">
                
                <!-- Col 1 -->
                <div class="md:col-span-2 space-y-4">
                    <div class="flex items-center gap-3">
                        @if(!empty($appSettings['app_logo']))
                            <img src="{{ asset('storage/' . $appSettings['app_logo']) }}" alt="{{ $appSettings['app_name'] ?? 'TALENTA' }}" class="h-10 w-auto max-w-[180px] object-contain">
                        @else
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-[#7A5AF8] to-[#4E6EFF] text-white flex items-center justify-center shadow-lg shadow-[#7A5AF8]/30">
                                <i data-lucide="sparkles" class="w-5 h-5"></i>
                            </div>
                            <span class="text-2xl font-black text-white font-display">{{ $appSettings['app_name'] ?? 'TALENTA 2026' }}</span>
                        @endif
                    </div>
                    <p class="text-xs sm:text-sm text-slate-400 leading-relaxed max-w-md">
                        {{ $appSettings['footer_about'] ?? ('Sistem Pendaftaran & Manajemen Perlombaan Terpadu ' . ($appSettings['institution_name'] ?? 'MTsN 1 Blitar') . '. Mengusung arsitektur modern berkecepatan tinggi, sistem undian interaktif spin wheel, dan live scoreboard transparan.') }}
                    </p>
                    <div class="flex items-center gap-3 pt-2">
                        <a href="{{ $appSettings['school_website'] ?? 'https://mtsn1blitar.sch.id' }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white/[0.04] hover:bg-white/[0.08] text-[#A594FD] text-xs font-bold border border-white/[0.08] transition">
                            <i data-lucide="globe" class="w-3.5 h-3.5"></i>
                            <span>Portal Resmi {{ $appSettings['institution_name'] ?? 'MTsN 1 Blitar' }}</span>
                        </a>
                    </div>
                </div>

                <!-- Col 2 -->
                <div>
                    <h4 class="text-white font-bold text-xs uppercase tracking-widest mb-4 font-display">Navigasi Cepat</h4>
                    <ul class="space-y-2.5 text-xs sm:text-sm">
                        <li><a href="{{ route('home') }}" class="hover:text-[#A594FD] transition">Beranda Utama</a></li>
                        <li><a href="{{ route('home') }}#kategori" class="hover:text-[#A594FD] transition">Katalog Cabang Lomba</a></li>
                        <li><a href="{{ route('home') }}#jadwal" class="hover:text-[#A594FD] transition">Jadwal & Timeline</a></li>
                        <li><a href="{{ route('check.status') }}" class="hover:text-[#A594FD] transition">Cek Status Verifikasi</a></li>
                        <li><a href="{{ route('live.scoreboard') }}" class="hover:text-[#A594FD] transition">Live Scoreboard Venue</a></li>
                    </ul>
                </div>

                <!-- Col 3 -->
                <div>
                    <h4 class="text-white font-bold text-xs uppercase tracking-widest mb-4 font-display">Sekretariat</h4>
                    <ul class="space-y-3 text-xs sm:text-sm">
                        <li class="flex items-start gap-2.5">
                            <i data-lucide="map-pin" class="w-4 h-4 text-[#7A5AF8] shrink-0 mt-0.5"></i>
                            <span>{{ $appSettings['address'] ?? 'Kampus MTsN 1 Blitar, Jl. Raya Kuningan, Kanigoro, Kab. Blitar, Jawa Timur' }}</span>
                        </li>
                        <li class="flex items-center gap-2.5">
                            <i data-lucide="phone" class="w-4 h-4 text-[#4E6EFF] shrink-0"></i>
                            <span>{{ $appSettings['contact_phone'] ?? '0812-3456-7890' }}</span>
                        </li>
                        <li class="flex items-center gap-2.5">
                            <i data-lucide="mail" class="w-4 h-4 text-[#FF58D5] shrink-0"></i>
                            <span>{{ $appSettings['contact_email'] ?? 'talenta@mtsn1blitar.sch.id' }}</span>
                        </li>
                    </ul>
                </div>

            </div>

            <div class="pt-8 border-t border-white/[0.08] flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500 text-center sm:text-left">
                <p>&copy; {{ $appSettings['event_year'] ?? date('Y') }} {{ $appSettings['institution_name'] ?? 'MTsN 1 Blitar' }}. Hak Cipta Dilindungi.</p>
                <p class="text-slate-400">Pure Tailwind CSS • Responsive Mobile & Desktop</p>
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
        });
    </script>
    @stack('scripts')
</body>
</html>
