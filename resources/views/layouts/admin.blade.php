<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') | {{ $appSettings['app_name'] ?? 'TALENTA' }} {{ $appSettings['institution_name'] ?? 'MTsN 1 Blitar' }}</title>
    
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
    
    <!-- Lucide Icons CDN -->
    <script src="https://cdn.jsdelivr.net/npm/lucide@0.344.0/dist/umd/lucide.min.js"></script>
    
    <!-- Alpine.js CDN -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Canvas Confetti -->
    <script defer src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }

        /* Edit Fullscreen Mode: sembunyikan sidebar sepenuhnya */
        body.edit-fullscreen aside {
            display: none !important;
        }
        body.edit-fullscreen > div.flex-1 {
            padding-left: 0 !important;
        }

        html, body {
            overflow-x: hidden !important;
            max-width: 100vw !important;
            width: 100% !important;
        }

        /* Hide scrollbars for chrome, safari and opera */
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        /* Make date/time input calendar icons bright and visible in dark mode */
        input[type="date"], input[type="time"], input[type="datetime-local"] {
            color-scheme: dark !important;
        }
        input[type="date"]::-webkit-calendar-picker-indicator,
        input[type="time"]::-webkit-calendar-picker-indicator,
        input[type="datetime-local"]::-webkit-calendar-picker-indicator {
            filter: invert(1) brightness(2) !important;
            cursor: pointer !important;
            opacity: 1 !important;
            display: block !important;
            padding: 3px !important;
            border-radius: 6px !important;
            background-color: rgba(16, 185, 129, 0.25) !important;
            transition: all 0.2s ease !important;
        }
        input[type="date"]::-webkit-calendar-picker-indicator:hover,
        input[type="time"]::-webkit-calendar-picker-indicator:hover,
        input[type="datetime-local"]::-webkit-calendar-picker-indicator:hover {
            background-color: rgba(16, 185, 129, 0.5) !important;
            filter: invert(1) brightness(3) drop-shadow(0 0 6px #10b981) !important;
            transform: scale(1.1);
        }

        /* ==========================================================================
           AI STARTER KIT (THEMEWAGON AISTARTERKIT) DESIGN SYSTEM TOKENS
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
        .ai-glow-1 {
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

        .ai-glow-2 {
            position: absolute;
            width: 600px;
            height: 600px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255, 88, 213, 0.22) 0%, rgba(122, 90, 248, 0.10) 50%, transparent 70%);
            filter: blur(120px);
            pointer-events: none;
            z-index: 0;
        }

        .ai-glow-3 {
            position: absolute;
            width: 550px;
            height: 550px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(56, 189, 248, 0.22) 0%, rgba(78, 110, 255, 0.10) 50%, transparent 70%);
            filter: blur(100px);
            pointer-events: none;
            z-index: 0;
        }

        /* AI Starter Kit Glass Surfaces */
        .ai-card {
            background: rgba(22, 31, 48, 0.88);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.10);
            box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.5), 0 0 25px rgba(78, 110, 255, 0.06);
        }

        .ai-panel {
            background: rgba(12, 17, 29, 0.92);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .ai-nav {
            background: rgba(16, 24, 40, 0.82);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.10);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);
        }

        /* AI Gradient Text & Buttons */
        .btn-gradient {
            background: linear-gradient(90deg, #34d399 0%, #2dd4bf 50%, #38bdf8 100%) !important;
            color: #020617 !important;
        }

        .ai-gradient-text {
            background: linear-gradient(135deg, #FFFFFF 0%, #E0EAFF 50%, #C7D2FE 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .ai-gradient-pill {
            background: linear-gradient(90deg, rgba(255, 88, 213, 0.5) 0%, rgba(78, 110, 255, 0.5) 100%);
            padding: 1px;
            border-radius: 9999px;
        }

        .gradient-btn, .btn-gradient-ai {
            background: linear-gradient(90deg, #7A5AF8 0%, #4E6EFF 100%);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            color: #ffffff !important;
        }
        .gradient-btn:hover, .btn-gradient-ai:hover {
            background: linear-gradient(90deg, #6941C6 0%, #3555EC 100%);
            box-shadow: 0 0 25px rgba(122, 90, 248, 0.4);
            transform: translateY(-1px);
        }

        /* Universal Form Control Styling for AIStarterKit Dark Scheme */
        input:not([type="checkbox"]):not([type="radio"]):not([type="submit"]):not([type="button"]):not([type="reset"]),
        select,
        textarea {
            color: #F8FAFC !important;
            background-color: #0C111D !important;
            border: 1px solid rgba(255, 255, 255, 0.12) !important;
            border-radius: 0.75rem;
            outline: none !important;
            transition: all 0.2s ease;
        }

        input:focus, select:focus, textarea:focus {
            border-color: #7A5AF8 !important;
            box-shadow: 0 0 0 3px rgba(122, 90, 248, 0.25) !important;
            background-color: #0C111D !important;
            color: #FFFFFF !important;
        }

        input::placeholder, textarea::placeholder {
            color: #64748B !important;
            opacity: 1 !important;
        }

        select option {
            background-color: #0C111D !important;
            color: #F8FAFC !important;
        }

        /* Modal Dialog Styling (AIStarterKit Dark Glassmorphism) */
        [role="dialog"] .bg-white,
        .modal-ai-dark {
            background: #161F30 !important;
            border: 1px solid rgba(255, 255, 255, 0.12) !important;
            color: #F8FAFC !important;
        }

        [role="dialog"] .bg-white h1, 
        [role="dialog"] .bg-white h2, 
        [role="dialog"] .bg-white h3, 
        [role="dialog"] .bg-white h4,
        [role="dialog"] h3 {
            color: #FFFFFF !important;
        }

        [role="dialog"] .bg-white label,
        [role="dialog"] label {
            color: #94A3B8 !important;
        }

        [role="dialog"] .bg-white p,
        [role="dialog"] p {
            color: #94A3B8 !important;
        }

        [role="dialog"] .bg-slate-50,
        [role="dialog"] .bg-slate-100 {
            background-color: rgba(12, 17, 29, 0.8) !important;
            border-color: rgba(255, 255, 255, 0.08) !important;
            color: #F8FAFC !important;
        }

        /* Table Global Adaptations */
        table thead tr {
            background-color: rgba(12, 17, 29, 0.9) !important;
            color: #94A3B8 !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
        }

        table tbody tr {
            border-bottom: 1px solid rgba(255, 255, 255, 0.04) !important;
        }

        table tbody tr:hover {
            background-color: rgba(255, 255, 255, 0.025) !important;
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
<body class="text-slate-100 font-sans antialiased min-h-screen flex selection:bg-[#7A5AF8] selection:text-white relative overflow-x-hidden" x-data="{ sidebarOpen: false, passwordModal: false }">

    <!-- Mobile Sidebar Backdrop -->
    <div x-show="sidebarOpen" @click="sidebarOpen = false" x-cloak class="fixed inset-0 z-40 bg-black/80 backdrop-blur-sm lg:hidden transition-opacity"></div>

    <!-- Sidebar Navigation (AIStarterKit Dark Glass Structure) -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'" class="fixed inset-y-0 left-0 z-20 w-64 bg-[#090D17]/98 backdrop-blur-2xl text-slate-300 flex flex-col transition-transform duration-300 ease-in-out border-r border-white/[0.12] shadow-[4px_0_25px_rgba(0,0,0,0.6)]">
        
        <!-- Sidebar Brand Header -->
        <div class="h-16 flex items-center justify-between px-5 border-b border-white/[0.08]">
            <a href="{{ auth()->check() && auth()->user()->role === 'peserta' ? route('peserta.dashboard') : (auth()->check() && auth()->user()->role === 'superadmin' ? route('admin.dashboard') : route('home')) }}" class="flex items-center gap-3 overflow-hidden group">
                @if(!empty($appSettings['app_logo']))
                    <img src="{{ asset('storage/' . $appSettings['app_logo']) }}" alt="{{ $appSettings['app_name'] ?? 'TALENTA' }}" class="h-9 w-auto max-w-[140px] object-contain group-hover:scale-105 transition-transform duration-300">
                @else
                    <div class="w-9 h-9 rounded-2xl bg-gradient-to-tr from-[#7A5AF8] to-[#4E6EFF] flex items-center justify-center text-white shadow-lg shadow-[#7A5AF8]/30 shrink-0 group-hover:scale-105 transition-transform duration-300">
                        <i data-lucide="sparkles" class="w-5 h-5"></i>
                    </div>
                    <div class="overflow-hidden">
                        <span class="text-base font-black tracking-tight text-white block leading-none truncate font-display">{{ $appSettings['app_name'] ?? 'TALENTA' }}</span>
                        <span class="text-[10px] font-bold tracking-widest text-[#7A5AF8] uppercase block truncate mt-0.5">{{ $appSettings['institution_name'] ?? 'MTsN 1 BLITAR' }}</span>
                    </div>
                @endif
            </a>
            <button @click="sidebarOpen = false" class="lg:hidden text-slate-400 hover:text-white p-1 rounded-lg">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Sidebar Navigation Menu -->
        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-4 text-xs font-medium scrollbar-thin scrollbar-thumb-slate-800">
            
            @if(auth()->user()->role === 'superadmin')
                <!-- Group: OVERVIEW -->
                <div class="space-y-1">
                    <div class="px-3 text-[10px] font-bold uppercase tracking-widest text-slate-500">Overview</div>
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-2xl transition {{ request()->routeIs('admin.dashboard') ? 'bg-gradient-to-r from-[#7A5AF8] to-[#4E6EFF] text-white font-bold shadow-lg shadow-[#7A5AF8]/25' : 'hover:bg-white/[0.04] text-slate-400 hover:text-slate-200' }}">
                        <i data-lucide="layout-dashboard" class="w-4 h-4 {{ request()->routeIs('admin.dashboard') ? 'text-white' : 'text-slate-400' }}"></i>
                        <span>Dashboard Utama</span>
                    </a>
                </div>

                <!-- Group: MASTER DATA -->
                <div class="space-y-1">
                    <div class="px-3 text-[10px] font-bold uppercase tracking-widest text-slate-500">Master Data</div>
                    <a href="{{ route('admin.competitions') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-2xl transition {{ request()->routeIs('admin.competitions*') ? 'bg-gradient-to-r from-[#7A5AF8] to-[#4E6EFF] text-white font-bold shadow-lg shadow-[#7A5AF8]/25' : 'hover:bg-white/[0.04] text-slate-400 hover:text-slate-200' }}">
                        <i data-lucide="medal" class="w-4 h-4 {{ request()->routeIs('admin.competitions*') ? 'text-white' : 'text-slate-400' }}"></i>
                        <span>Master Cabang Lomba</span>
                    </a>
                    <a href="{{ route('admin.users') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-2xl transition {{ request()->routeIs('admin.users*') ? 'bg-gradient-to-r from-[#7A5AF8] to-[#4E6EFF] text-white font-bold shadow-lg shadow-[#7A5AF8]/25' : 'hover:bg-white/[0.04] text-slate-400 hover:text-slate-200' }}">
                        <i data-lucide="users" class="w-4 h-4 {{ request()->routeIs('admin.users*') ? 'text-white' : 'text-slate-400' }}"></i>
                        <span>Kelola Pengguna</span>
                    </a>
                </div>

                <!-- Group: OPERASIONAL PERLOMBAAN -->
                <div class="space-y-1">
                    <div class="px-3 text-[10px] font-bold uppercase tracking-widest text-slate-500">Operasional Lomba</div>
                    <a href="{{ route('admin.verifications') }}" class="flex items-center justify-between px-3 py-2.5 rounded-2xl transition {{ request()->routeIs('admin.verifications*') || request()->routeIs('admin.participants.index*') ? 'bg-gradient-to-r from-[#7A5AF8] to-[#4E6EFF] text-white font-bold shadow-lg shadow-[#7A5AF8]/25' : 'hover:bg-white/[0.04] text-slate-400 hover:text-slate-200' }}">
                        <div class="flex items-center gap-3">
                            <i data-lucide="users" class="w-4 h-4 {{ request()->routeIs('admin.verifications*') || request()->routeIs('admin.participants.index*') ? 'text-white' : 'text-[#4E6EFF]' }}"></i>
                            <span>Data Peserta</span>
                        </div>
                    </a>
                    <a href="{{ route('admin.juri.wasit') }}" class="flex items-center justify-between px-3 py-2.5 rounded-2xl transition {{ request()->routeIs('admin.juri.wasit*') || request()->routeIs('admin.undian*') || request()->routeIs('badminton.index*') || request()->routeIs('juri.*') ? 'bg-gradient-to-r from-[#7A5AF8] to-[#4E6EFF] text-white font-bold shadow-lg shadow-[#7A5AF8]/25' : 'hover:bg-white/[0.04] text-slate-400 hover:text-slate-200' }}">
                        <div class="flex items-center gap-3">
                            <i data-lucide="scale" class="w-4 h-4 {{ request()->routeIs('admin.juri.wasit*') || request()->routeIs('admin.undian*') || request()->routeIs('badminton.index*') || request()->routeIs('juri.*') ? 'text-white' : 'text-[#A594FD]' }}"></i>
                            <span>Juri, Wasit & Undian</span>
                        </div>
                    </a>
                </div>

                <!-- Group: LAPORAN -->
                <div class="space-y-1">
                    <div class="px-3 text-[10px] font-bold uppercase tracking-widest text-slate-500">Laporan</div>
                    <a href="{{ route('admin.recap') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-2xl transition {{ request()->routeIs('admin.recap*') || request()->routeIs('admin.scores*') ? 'bg-gradient-to-r from-[#7A5AF8] to-[#4E6EFF] text-white font-bold shadow-lg shadow-[#7A5AF8]/25' : 'hover:bg-white/[0.04] text-slate-400 hover:text-slate-200' }}">
                        <i data-lucide="bar-chart-3" class="w-4 h-4 {{ request()->routeIs('admin.recap*') || request()->routeIs('admin.scores*') ? 'text-white' : 'text-amber-400' }}"></i>
                        <span>Rekapitulasi</span>
                    </a>
                </div>

                <!-- Group: PENGATURAN & TOOLS -->
                <div class="space-y-1">
                    <div class="px-3 text-[10px] font-bold uppercase tracking-widest text-slate-500">Pengaturan Sistem</div>
                    <a href="{{ route('admin.settings.general') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-2xl transition {{ request()->routeIs('admin.settings.general*') ? 'bg-gradient-to-r from-[#7A5AF8] to-[#4E6EFF] text-white font-bold shadow-lg shadow-[#7A5AF8]/25' : 'hover:bg-white/[0.04] text-slate-400 hover:text-slate-200' }}">
                        <i data-lucide="sliders" class="w-4 h-4 {{ request()->routeIs('admin.settings.general*') ? 'text-white' : 'text-[#7A5AF8]' }}"></i>
                        <span>Pengaturan Aplikasi</span>
                    </a>
                    <a href="{{ route('admin.settings.whatsapp.blast') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-2xl transition {{ request()->routeIs('admin.settings.whatsapp*') ? 'bg-gradient-to-r from-[#7A5AF8] to-[#4E6EFF] text-white font-bold shadow-lg shadow-[#7A5AF8]/25' : 'hover:bg-white/[0.04] text-slate-400 hover:text-slate-200' }}">
                        <i data-lucide="message-square" class="w-4 h-4 {{ request()->routeIs('admin.settings.whatsapp*') ? 'text-white' : 'text-green-400' }}"></i>
                        <span>WhatsApp Blast</span>
                    </a>
                    <a href="{{ route('admin.settings.changelog') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-2xl transition {{ request()->routeIs('admin.settings.changelog*') ? 'bg-gradient-to-r from-[#7A5AF8] to-[#4E6EFF] text-white font-bold shadow-lg shadow-[#7A5AF8]/25' : 'hover:bg-white/[0.04] text-slate-400 hover:text-slate-200' }}">
                        <i data-lucide="history" class="w-4 h-4 text-blue-400"></i>
                        <span>Changelog Rilis</span>
                    </a>
                    <a href="{{ route('admin.settings.app.info') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-2xl transition {{ request()->routeIs('admin.settings.app.info*') ? 'bg-gradient-to-r from-[#7A5AF8] to-[#4E6EFF] text-white font-bold shadow-lg shadow-[#7A5AF8]/25' : 'hover:bg-white/[0.04] text-slate-400 hover:text-slate-200' }}">
                        <i data-lucide="info" class="w-4 h-4 text-amber-400"></i>
                        <span>Info Aplikasi & Server</span>
                    </a>
                </div>
            @endif

            @if(auth()->user()->role === 'pic_lomba')
                <div class="space-y-1">
                    <div class="px-3 text-[10px] font-bold uppercase tracking-widest text-slate-500">Menu Peserta</div>
                    <a href="{{ route('pic.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-2xl transition {{ request()->routeIs('pic.dashboard') || request()->routeIs('pic.participants*') ? 'bg-gradient-to-r from-[#7A5AF8] to-[#4E6EFF] text-white font-bold shadow-lg shadow-[#7A5AF8]/25' : 'hover:bg-white/[0.04] text-slate-400 hover:text-slate-200' }}">
                        <i data-lucide="users" class="w-4 h-4"></i>
                        <span>Data Peserta</span>
                    </a>
                    <a href="{{ route('pic.undian') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-2xl transition {{ request()->routeIs('pic.undian*') || request()->routeIs('pic.hacker.draw*') || request()->routeIs('pic.spin.wheel*') ? 'bg-gradient-to-r from-[#7A5AF8] to-[#4E6EFF] text-white font-bold shadow-lg shadow-[#7A5AF8]/25' : 'hover:bg-white/[0.04] text-slate-400 hover:text-slate-200' }}">
                        <i data-lucide="disc" class="w-4 h-4 text-[#FF58D5]"></i>
                        <span>Undi Peserta</span>
                    </a>
                    <a href="{{ route('badminton.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-2xl transition {{ request()->routeIs('badminton.*') ? 'bg-gradient-to-r from-[#7A5AF8] to-[#4E6EFF] text-white font-bold shadow-lg shadow-[#7A5AF8]/25' : 'hover:bg-white/[0.04] text-slate-400 hover:text-slate-200' }}">
                        <i data-lucide="activity" class="w-4 h-4 text-emerald-400"></i>
                        <span>Scoring Bulu Tangkis</span>
                    </a>
                    <a href="{{ route('badminton.scoreboard') }}" target="_blank" class="flex items-center justify-between px-3 py-2.5 rounded-2xl transition hover:bg-white/[0.04] text-slate-400 hover:text-slate-200">
                        <div class="flex items-center gap-3">
                            <i data-lucide="tv" class="w-4 h-4 text-rose-400"></i>
                            <span>Papan Skor LED TV</span>
                        </div>
                        <span class="w-2 h-2 rounded-full bg-rose-500 animate-ping"></span>
                    </a>
                    <a href="{{ route('badminton.arena') }}" target="_blank" class="flex items-center gap-3 px-3 py-2.5 rounded-2xl transition hover:bg-white/[0.04] text-slate-400 hover:text-slate-200">
                        <i data-lucide="layout-grid" class="w-4 h-4 text-[#4E6EFF]"></i>
                        <span>Arena Multi-Lapangan</span>
                    </a>
                    <a href="{{ route('live.scoreboard') }}" target="_blank" class="flex items-center justify-between px-3 py-2.5 rounded-2xl transition hover:bg-white/[0.04] text-slate-400 hover:text-slate-200">
                        <div class="flex items-center gap-3">
                            <i data-lucide="trophy" class="w-4 h-4 text-amber-400"></i>
                            <span>Live Leaderboard</span>
                        </div>
                    </a>
                </div>
            @endif

            @if(auth()->user()->role === 'juri')
                <div class="space-y-1">
                    <div class="px-3 text-[10px] font-bold uppercase tracking-widest text-slate-500">Menu Dewan Juri & Wasit</div>
                    <a href="{{ route('juri.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-2xl transition {{ request()->routeIs('juri.dashboard') ? 'bg-gradient-to-r from-[#7A5AF8] to-[#4E6EFF] text-white font-bold shadow-lg shadow-[#7A5AF8]/25' : 'hover:bg-white/[0.04] text-slate-400 hover:text-slate-200' }}">
                        <i data-lucide="clipboard-pen" class="w-4 h-4"></i>
                        <span>Penilaian Juri Kriteria</span>
                    </a>
                    <a href="{{ route('badminton.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-2xl transition {{ request()->routeIs('badminton.*') ? 'bg-gradient-to-r from-[#7A5AF8] to-[#4E6EFF] text-white font-bold shadow-lg shadow-[#7A5AF8]/25' : 'hover:bg-white/[0.04] text-slate-400 hover:text-slate-200' }}">
                        <i data-lucide="activity" class="w-4 h-4 text-emerald-400"></i>
                        <span>Wasit Bulu Tangkis</span>
                    </a>
                    <a href="{{ route('badminton.scoreboard') }}" target="_blank" class="flex items-center justify-between px-3 py-2.5 rounded-2xl transition hover:bg-white/[0.04] text-slate-400 hover:text-slate-200">
                        <div class="flex items-center gap-3">
                            <i data-lucide="tv" class="w-4 h-4 text-rose-400"></i>
                            <span>Papan Skor LED TV</span>
                        </div>
                        <span class="w-2 h-2 rounded-full bg-rose-500 animate-ping"></span>
                    </a>
                    <a href="{{ route('badminton.arena') }}" target="_blank" class="flex items-center gap-3 px-3 py-2.5 rounded-2xl transition hover:bg-white/[0.04] text-slate-400 hover:text-slate-200">
                        <i data-lucide="layout-grid" class="w-4 h-4 text-[#4E6EFF]"></i>
                        <span>Arena Multi-Lapangan</span>
                    </a>
                </div>
            @endif

            @if(auth()->user()->role === 'peserta')
                <div class="space-y-1">
                    <div class="px-3 text-[10px] font-bold uppercase tracking-widest text-slate-500">Menu Pendaftar</div>
                    <a href="{{ route('peserta.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-2xl transition {{ request()->routeIs('peserta.dashboard') ? 'bg-gradient-to-r from-[#7A5AF8] to-[#4E6EFF] text-white font-bold shadow-lg shadow-[#7A5AF8]/25' : 'hover:bg-white/[0.04] text-slate-400 hover:text-slate-200' }}">
                        <i data-lucide="layout-grid" class="w-4 h-4 {{ request()->routeIs('peserta.dashboard') ? 'text-white' : 'text-[#7A5AF8]' }}"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="{{ route('peserta.registrations') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-2xl transition {{ request()->routeIs('peserta.registrations*') || request()->routeIs('peserta.registration.detail*') ? 'bg-gradient-to-r from-[#7A5AF8] to-[#4E6EFF] text-white font-bold shadow-lg shadow-[#7A5AF8]/25' : 'hover:bg-white/[0.04] text-slate-400 hover:text-slate-200' }}">
                        <i data-lucide="award" class="w-4 h-4 {{ request()->routeIs('peserta.registrations*') ? 'text-white' : 'text-amber-400' }}"></i>
                        <span>Pendaftaran Saya</span>
                    </a>
                    <a href="{{ route('peserta.collective.wizard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-2xl transition {{ request()->routeIs('peserta.collective*') || request()->routeIs('peserta.invoices*') ? 'bg-gradient-to-r from-[#7A5AF8] to-[#4E6EFF] text-white font-bold shadow-lg shadow-[#7A5AF8]/25' : 'hover:bg-white/[0.04] text-slate-400 hover:text-slate-200' }}">
                        <i data-lucide="file-spreadsheet" class="w-4 h-4 {{ request()->routeIs('peserta.collective*') ? 'text-white' : 'text-[#4E6EFF]' }}"></i>
                        <span>Daftar Kolektif (Excel)</span>
                    </a>
                </div>
            @endif

        </nav>

        <!-- Sidebar Footer / User Profile -->
        <div class="p-3 border-t border-white/[0.08] bg-[#080C15]/80">
            <div class="flex items-center justify-between gap-2">
                <div class="flex items-center gap-2.5 overflow-hidden">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-[#7A5AF8] to-[#4E6EFF] text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-md shadow-[#7A5AF8]/30">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div class="overflow-hidden">
                        <p class="text-xs font-bold text-white truncate">{{ auth()->user()->name }}</p>
                        <p class="text-[10px] font-semibold text-[#A594FD] uppercase tracking-wider truncate">
                            {{ auth()->user()->position ?: match(auth()->user()->role) {
                                'superadmin' => 'Super Administrator',
                                'pic_lomba' => 'PIC Koordinator',
                                'juri' => 'Dewan Juri',
                                default => 'Pendaftar Resmi'
                            } }}
                        </p>
                    </div>
                </div>
                <form action="{{ route('logout') }}" method="POST" class="shrink-0">
                    @csrf
                    <button type="submit" class="p-1.5 rounded-xl text-slate-400 hover:text-rose-400 hover:bg-rose-500/10 transition" title="Keluar">
                        <i data-lucide="log-out" class="w-4 h-4"></i>
                    </button>
                </form>
            </div>
        </div>

    </aside>

    <!-- Main Content Area with AIStarterKit Ambient Glow Effects -->
    <div class="flex-1 flex flex-col lg:pl-64 min-w-0 relative w-full max-w-full">
        
        <!-- Ambient AI Glow Orbs in Dashboard -->
        <div class="fixed inset-0 overflow-hidden pointer-events-none z-0 w-full h-full max-w-[100vw]">
            <div class="ai-glow-1 top-0 right-0"></div>
            <div class="ai-glow-2 top-1/3 left-0"></div>
            <div class="ai-glow-3 bottom-10 right-0"></div>
        </div>
        
        <!-- Dashboard Top Navbar (AI Glass Nav) -->
        <header class="sticky top-0 z-30 ai-nav h-14 sm:h-16 flex items-center justify-between px-3.5 sm:px-8">
            <div class="flex items-center gap-2.5 sm:gap-4 overflow-hidden">
                <button @click="sidebarOpen = true" class="lg:hidden p-2 rounded-xl text-slate-400 hover:bg-white/[0.06] flex items-center justify-center shrink-0" aria-label="Buka Menu">
                    <i data-lucide="menu" class="w-5 h-5"></i>
                </button>
                <div class="overflow-hidden">
                    <h1 class="text-sm sm:text-base font-bold text-white tracking-tight truncate font-display ai-gradient-text">@yield('page_title', 'TALENTA Portal')</h1>
                </div>
            </div>

            <!-- Top Right Action Area -->
            <div class="flex items-center gap-3 shrink-0">
                <!-- AI Badge Pill -->
                <div class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/[0.05] border border-white/[0.08] text-[11px] font-bold text-slate-300">
                    <span class="w-2 h-2 rounded-full bg-[#7A5AF8] animate-pulse"></span>
                    <span>Portal 2026</span>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" @click="passwordModal = true" class="flex items-center gap-2.5 p-1 sm:px-3 sm:py-1.5 rounded-2xl hover:bg-white/[0.06] border border-transparent hover:border-white/[0.08] transition cursor-pointer text-left" title="Klik untuk Ganti Kata Sandi">
                        <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-gradient-to-tr from-[#7A5AF8] to-[#4E6EFF] text-white flex items-center justify-center font-bold text-xs shadow-md shadow-[#7A5AF8]/30 shrink-0">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <div class="hidden md:block">
                            <p class="text-xs font-bold text-slate-200 leading-tight">{{ auth()->user()->name }}</p>
                            <p class="text-[10px] text-slate-400 truncate max-w-[150px]">{{ auth()->user()->position ? auth()->user()->position . ' • ' : '' }}{{ auth()->user()->institution_name ?? 'MTsN 1 Blitar' }}</p>
                        </div>
                    </button>
                </div>
            </div>
        </header>

        <!-- Floating Toast Notifications -->
        <div class="fixed top-5 right-4 sm:top-6 sm:right-6 z-50 flex flex-col gap-3 max-w-sm sm:max-w-md w-full pointer-events-none px-2 sm:px-0">
            @if(session('success') || session('status'))
                <div x-data="{ show: false }" 
                     x-init="setTimeout(() => show = true, 50); setTimeout(() => show = false, 4000)" 
                     x-show="show"
                     x-cloak
                     x-transition:enter="transition ease-out duration-400 transform"
                     x-transition:enter-start="opacity-0 -translate-y-4 scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                     x-transition:leave="transition ease-in duration-500 transform"
                     x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                     x-transition:leave-end="opacity-0 -translate-y-4 scale-95"
                     class="pointer-events-auto bg-[#161F30]/95 backdrop-blur-xl border border-[#7A5AF8]/40 shadow-2xl shadow-[#7A5AF8]/20 rounded-2xl p-4 flex items-center justify-between gap-3.5 text-slate-200 ring-1 ring-[#7A5AF8]/30">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-[#7A5AF8] to-[#4E6EFF] text-white flex items-center justify-center shrink-0 shadow-lg shadow-[#7A5AF8]/30">
                            <i data-lucide="check" class="w-5 h-5"></i>
                        </div>
                        <div class="min-w-0">
                            <h5 class="text-xs font-black text-white leading-tight">Berhasil!</h5>
                            <p class="text-xs text-slate-300 font-medium mt-0.5 leading-snug break-words">{{ session('success') ?? session('status') }}</p>
                        </div>
                    </div>
                    <button @click="show = false" class="text-slate-400 hover:text-white p-1.5 rounded-xl hover:bg-white/[0.08] transition cursor-pointer shrink-0" title="Tutup">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div x-data="{ show: false }" 
                     x-init="setTimeout(() => show = true, 50); setTimeout(() => show = false, 5500)" 
                     x-show="show"
                     x-cloak
                     x-transition:enter="transition ease-out duration-400 transform"
                     x-transition:enter-start="opacity-0 -translate-y-4 scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                     x-transition:leave="transition ease-in duration-500 transform"
                     x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                     x-transition:leave-end="opacity-0 -translate-y-4 scale-95"
                     class="pointer-events-auto bg-[#161F30]/95 backdrop-blur-xl border border-rose-500/40 shadow-2xl shadow-rose-500/20 rounded-2xl p-4 flex items-center justify-between gap-3.5 text-slate-200 ring-1 ring-rose-500/30">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-9 h-9 rounded-xl bg-rose-500 text-white flex items-center justify-center shrink-0 shadow-lg shadow-rose-500/30">
                            <i data-lucide="alert-circle" class="w-5 h-5"></i>
                        </div>
                        <div class="min-w-0">
                            <h5 class="text-xs font-black text-rose-300 leading-tight">Perhatian / Gagal</h5>
                            <p class="text-xs text-slate-300 font-medium mt-0.5 leading-snug break-words">{{ session('error') }}</p>
                        </div>
                    </div>
                    <button @click="show = false" class="text-slate-400 hover:text-white p-1.5 rounded-xl hover:bg-white/[0.08] transition cursor-pointer shrink-0" title="Tutup">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
            @endif

            @if(session('info'))
                <div x-data="{ show: false }" 
                     x-init="setTimeout(() => show = true, 50); setTimeout(() => show = false, 4500)" 
                     x-show="show"
                     x-cloak
                     x-transition:enter="transition ease-out duration-400 transform"
                     x-transition:enter-start="opacity-0 -translate-y-4 scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                     x-transition:leave="transition ease-in duration-500 transform"
                     x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                     x-transition:leave-end="opacity-0 -translate-y-4 scale-95"
                     class="pointer-events-auto bg-[#161F30]/95 backdrop-blur-xl border border-[#4E6EFF]/40 shadow-2xl shadow-[#4E6EFF]/20 rounded-2xl p-4 flex items-center justify-between gap-3.5 text-slate-200 ring-1 ring-[#4E6EFF]/30">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-9 h-9 rounded-xl bg-[#4E6EFF] text-white flex items-center justify-center shrink-0 shadow-lg shadow-[#4E6EFF]/30">
                            <i data-lucide="info" class="w-5 h-5"></i>
                        </div>
                        <div class="min-w-0">
                            <h5 class="text-xs font-black text-[#84D0FF] leading-tight">Informasi</h5>
                            <p class="text-xs text-slate-300 font-medium mt-0.5 leading-snug break-words">{{ session('info') }}</p>
                        </div>
                    </div>
                    <button @click="show = false" class="text-slate-400 hover:text-white p-1.5 rounded-xl hover:bg-white/[0.08] transition cursor-pointer shrink-0" title="Tutup">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
            @endif
        </div>

        <!-- Main Workspace -->
        <main class="flex-1 p-3.5 sm:p-6 lg:p-8 max-w-[1600px] w-full mx-auto">
            @yield('content')
        </main>

    </div>

    <!-- MODAL GANTI KATA SANDI (AIStarterKit Style) -->
    <div x-show="passwordModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="passwordModal" @click="passwordModal = false" class="fixed inset-0 bg-black/80 backdrop-blur-md transition-opacity"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div x-show="passwordModal" class="inline-block align-bottom bg-[#161F30] rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-white/[0.12] p-6 sm:p-8 space-y-6 text-slate-200">
                <div class="flex items-center justify-between border-b border-white/[0.08] pb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-[#7A5AF8]/20 text-[#A594FD] flex items-center justify-center border border-[#7A5AF8]/30">
                            <i data-lucide="key-round" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-black text-white">Ganti Kata Sandi</h3>
                            <p class="text-xs text-slate-400">Perbarui kata sandi akun Anda</p>
                        </div>
                    </div>
                    <button @click="passwordModal = false" class="text-slate-400 hover:text-white">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form action="{{ route('user.password.update') }}" method="POST" class="space-y-4">
                    @csrf
                    <div x-data="{ showPass: false }">
                        <label class="block text-xs font-bold uppercase text-slate-300 mb-1.5">Kata Sandi Saat Ini / Default (NISN)</label>
                        <div class="relative">
                            <input :type="showPass ? 'text' : 'password'" type="password" name="current_password" required placeholder="Masukkan kata sandi saat ini" class="w-full pl-4 pr-11 py-2.5 rounded-xl bg-[#0C111D] border border-white/[0.12] text-white placeholder-slate-500 text-sm focus:border-[#7A5AF8] focus:ring-1 focus:ring-[#7A5AF8] outline-none transition">
                            <button type="button" @click="showPass = !showPass" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-white transition" tabindex="-1">
                                <i :data-lucide="showPass ? 'eye-off' : 'eye'" class="w-4 h-4" x-show="!showPass"></i>
                                <svg x-show="showPass" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" /></svg>
                            </button>
                        </div>
                    </div>

                    <div x-data="{ showPass: false }">
                        <label class="block text-xs font-bold uppercase text-slate-300 mb-1.5">Kata Sandi Baru (Min. 6 Karakter)</label>
                        <div class="relative">
                            <input :type="showPass ? 'text' : 'password'" type="password" name="password" required placeholder="Masukkan kata sandi baru" class="w-full pl-4 pr-11 py-2.5 rounded-xl bg-[#0C111D] border border-white/[0.12] text-white placeholder-slate-500 text-sm focus:border-[#7A5AF8] focus:ring-1 focus:ring-[#7A5AF8] outline-none transition">
                            <button type="button" @click="showPass = !showPass" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-white transition" tabindex="-1">
                                <svg x-show="!showPass" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                <svg x-show="showPass" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" /></svg>
                            </button>
                        </div>
                    </div>

                    <div x-data="{ showPass: false }">
                        <label class="block text-xs font-bold uppercase text-slate-300 mb-1.5">Konfirmasi Kata Sandi Baru</label>
                        <div class="relative">
                            <input :type="showPass ? 'text' : 'password'" type="password" name="password_confirmation" required placeholder="Ulangi kata sandi baru" class="w-full pl-4 pr-11 py-2.5 rounded-xl bg-[#0C111D] border border-white/[0.12] text-white placeholder-slate-500 text-sm focus:border-[#7A5AF8] focus:ring-1 focus:ring-[#7A5AF8] outline-none transition">
                            <button type="button" @click="showPass = !showPass" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-white transition" tabindex="-1">
                                <svg x-show="!showPass" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                <svg x-show="showPass" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" /></svg>
                            </button>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-white/[0.08] flex items-center justify-end gap-2">
                        <button type="button" @click="passwordModal = false" class="px-4 py-2.5 rounded-xl border border-white/[0.1] text-xs font-bold text-slate-300 hover:bg-white/[0.06] transition">
                            Batal
                        </button>
                        <button type="submit" class="px-6 py-2.5 rounded-xl gradient-btn text-white text-xs font-black uppercase tracking-wider shadow-lg shadow-[#7A5AF8]/30 transition">
                            Simpan Sandi Baru
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Initialize Lucide Icons -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
        });
    </script>
    @stack('scripts')
</body>
</html>
