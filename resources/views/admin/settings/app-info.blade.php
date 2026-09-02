@extends('layouts.admin')

@section('title', 'Informasi Sistem & Spesifikasi Aplikasi')
@section('page_title', 'Informasi Sistem & Server')

@section('content')
<div class="space-y-6">
    
    <!-- Top Header Bar (AIStarterKit Dark Style) -->
    <div class="ai-card rounded-3xl p-6 sm:p-8 border border-white/[0.08] shadow-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="space-y-1.5">
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-400">
                <span>TALENTA Admin</span>
                <span>/</span>
                <span>Pengaturan Sistem</span>
                <span>/</span>
                <span class="text-[#84D0FF] font-bold">Info Aplikasi</span>
            </div>
            <h2 class="text-xl sm:text-3xl font-black tracking-tight text-white font-display">Spesifikasi & Status Server</h2>
            <p class="text-xs text-slate-400">Rincian teknis lingkungan hosting, modul database, dan status integritas sistem</p>
        </div>

        <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-white/[0.06] hover:bg-white/[0.12] text-slate-300 hover:text-white font-bold text-xs border border-white/[0.08] transition shadow-sm">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Kembali ke Dashboard</span>
        </a>
    </div>

    <!-- System Specs Grid (AIStarterKit Dark Cards) -->
    <div class="ai-card rounded-3xl p-6 sm:p-8 border border-white/[0.08] shadow-2xl space-y-6 text-white">
        <div class="flex items-center gap-3 border-b border-white/[0.08] pb-4">
            <div class="w-10 h-10 rounded-2xl bg-emerald-500/15 text-emerald-400 border border-emerald-500/30 flex items-center justify-center font-bold">
                <i data-lucide="cpu" class="w-5 h-5"></i>
            </div>
            <div>
                <h3 class="text-base sm:text-lg font-black text-white font-display">Lingkungan Server & Framework</h3>
                <p class="text-xs text-slate-400">Informasi runtime dan konfigurasi server aktif</p>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 text-xs">
            
            <div class="p-4 rounded-2xl bg-[#0C111D]/80 border border-white/[0.08] flex items-center justify-between">
                <span class="text-slate-400 font-medium">Nama Aplikasi</span>
                <span class="font-bold text-white">{{ $systemInfo['app_name'] ?? '-' }}</span>
            </div>

            <div class="p-4 rounded-2xl bg-[#0C111D]/80 border border-white/[0.08] flex items-center justify-between">
                <span class="text-slate-400 font-medium">Versi Rilis</span>
                <span class="font-bold text-emerald-400 font-mono">{{ $systemInfo['app_version'] ?? '1.5.0' }}</span>
            </div>

            <div class="p-4 rounded-2xl bg-[#0C111D]/80 border border-white/[0.08] flex items-center justify-between">
                <span class="text-slate-400 font-medium">Laravel Framework</span>
                <span class="font-bold text-rose-400 font-mono">v{{ $systemInfo['laravel_version'] ?? app()->version() }}</span>
            </div>

            <div class="p-4 rounded-2xl bg-[#0C111D]/80 border border-white/[0.08] flex items-center justify-between">
                <span class="text-slate-400 font-medium">PHP Engine</span>
                <span class="font-bold text-[#A594FD] font-mono">v{{ $systemInfo['php_version'] ?? PHP_VERSION }}</span>
            </div>

            <div class="p-4 rounded-2xl bg-[#0C111D]/80 border border-white/[0.08] flex items-center justify-between">
                <span class="text-slate-400 font-medium">Web Server</span>
                <span class="font-bold text-white truncate max-w-[200px]">{{ $systemInfo['server_software'] ?? '-' }}</span>
            </div>

            <div class="p-4 rounded-2xl bg-[#0C111D]/80 border border-white/[0.08] flex items-center justify-between">
                <span class="text-slate-400 font-medium">Database Engine</span>
                <span class="font-bold text-emerald-400">{{ $systemInfo['database_engine'] ?? 'MySQL' }} ({{ $systemInfo['db_name'] ?? '-' }})</span>
            </div>

            <div class="p-4 rounded-2xl bg-[#0C111D]/80 border border-white/[0.08] flex items-center justify-between">
                <span class="text-slate-400 font-medium">Environment Mode</span>
                <span class="font-bold text-white capitalize">{{ $systemInfo['environment'] ?? 'production' }} ({{ $systemInfo['debug_mode'] ?? '-' }})</span>
            </div>

            <div class="p-4 rounded-2xl bg-[#0C111D]/80 border border-white/[0.08] flex items-center justify-between">
                <span class="text-slate-400 font-medium">Waktu Server (Timezone)</span>
                <span class="font-bold text-white font-mono">{{ $systemInfo['server_time'] ?? '-' }}</span>
            </div>

            <div class="sm:col-span-2 p-4 rounded-2xl bg-[#0C111D]/80 border border-white/[0.08] flex items-center justify-between">
                <span class="text-slate-400 font-medium">Storage Symlink / Junction</span>
                <span class="font-bold text-emerald-400 flex items-center gap-1.5">
                    <i data-lucide="check-circle" class="w-4 h-4 text-emerald-400"></i>
                    <span>{{ $systemInfo['storage_status'] ?? 'Tersambung' }}</span>
                </span>
            </div>

        </div>
    </div>

    <!-- Institutional & Attribution Card -->
    <div class="ai-card rounded-3xl p-6 sm:p-8 border border-white/[0.08] shadow-2xl space-y-4 text-white">
        <div class="flex items-center gap-3 border-b border-white/[0.08] pb-4">
            <div class="w-10 h-10 rounded-2xl bg-[#4E6EFF]/15 text-[#84D0FF] border border-[#4E6EFF]/30 flex items-center justify-center font-bold">
                <i data-lucide="building" class="w-5 h-5"></i>
            </div>
            <div>
                <h3 class="text-base sm:text-lg font-black text-white font-display">Identitas Lembaga Penyelenggara</h3>
                <p class="text-xs text-slate-400">Informasi kepanitiaan dan pengembang sistem</p>
            </div>
        </div>

        <div class="space-y-2.5 text-xs text-slate-300">
            <p><strong class="text-white">Lembaga Penyelenggara:</strong> {{ $systemInfo['institution_name'] ?? $systemInfo['institution'] ?? 'MTs Negeri 1 Blitar' }}</p>
            <p><strong class="text-white">Kepala Madrasah:</strong> {{ $systemInfo['headmaster'] ?? '-' }}</p>
            <p><strong class="text-white">Ketua Panitia TALENTA:</strong> {{ $systemInfo['committee_chairman'] ?? '-' }}</p>
            <p><strong class="text-white">Pengembang & Pengelola:</strong> {{ $systemInfo['developer'] ?? 'Tim IT TALENTA MTsN 1 Blitar' }}</p>
            <p class="text-slate-400 pt-2 border-t border-white/[0.06]"><strong class="text-white">Arsitektur UI:</strong> Dark AI StarterKit UI Design • Tailwind CSS 3.4 • Alpine.js 3 • Lucide Icons</p>
        </div>
    </div>

</div>
@endsection
