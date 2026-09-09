@extends('layouts.app')

@section('title', ($appSettings['app_name'] ?? 'TALENTA') . ' | ' . ($appSettings['institution_name'] ?? 'MTs Negeri 1 Blitar'))

@section('content')
<div x-data="{ activeCategory: 'all' }" class="w-full max-w-full overflow-x-hidden">

    <!-- HERO SECTION (Crypto-NextJS Inspired Style) -->
    <section class="relative pt-4 pb-4 sm:pt-6 sm:pb-6 lg:pt-8 lg:pb-10 overflow-hidden w-full">
        <div class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-10 items-center">
                
                <!-- Left Column: Hero Text (With Arabic Calligraphy & Welcome Heading) -->
                <div class="lg:col-span-7 space-y-3 sm:space-y-4 text-center lg:text-left">
                    
                    <!-- Arabic Calligraphy "Ahlan Wa Sahlan" -->
                    <div class="flex justify-center lg:justify-start">
                        <img src="{{ asset('images/ahlan ptsp.png') }}" alt="Ahlan Wa Sahlan" class="h-10 sm:h-14 lg:h-20 w-auto max-w-[200px] sm:max-w-none object-contain drop-shadow-lg hover:scale-105 transition-transform duration-300">
                    </div>

                    <!-- Main Heading: Selamat Datang -->
                    <h1 class="text-2xl sm:text-4xl lg:text-5xl font-black text-white tracking-tight leading-snug sm:leading-tight font-display">
                        @if(!empty($appSettings['hero_title']))
                            {!! nl2br(e($appSettings['hero_title'])) !!}
                        @else
                            Selamat Datang di <span class="text-white">{{ $appSettings['app_name'] ?? 'TALENTA' }}</span><br/>
                            <span class="text-gradient">{{ $appSettings['institution_name'] ?? 'MTsN 1 Blitar' }}</span>
                        @endif
                    </h1>

                    <!-- Subtitle / Deskripsi Hero -->
                    @if(!empty($appSettings['hero_subtitle']))
                        <p class="text-xs sm:text-sm text-slate-300 max-w-xl mx-auto lg:mx-0 leading-relaxed font-normal">
                            {{ $appSettings['hero_subtitle'] }}
                        </p>
                    @endif

                </div>

                <!-- Right Column: Logo Milad 57 MTsN 1 Blitar -->
                <div class="lg:col-span-5 relative flex items-center justify-center">
                    <div class="relative mx-auto flex flex-col items-center justify-center p-2">
                        
                        <!-- Logo Milad 57 Image -->
                        <div class="relative z-10 group flex items-center justify-center">
                            <img src="{{ asset('images/logo milad 57.png') }}" alt="Logo Milad 57 MTsN 1 Blitar" class="max-h-[160px] sm:max-h-[220px] lg:max-h-[270px] w-auto max-w-[140px] sm:max-w-[200px] lg:max-w-[250px] object-contain drop-shadow-[0_10px_25px_rgba(0,0,0,0.5)] group-hover:scale-105 transition-transform duration-500">
                        </div>

                        <!-- Ambient Glow Decoration behind Logo -->
                        <div class="absolute inset-0 bg-gradient-to-tr from-[#7A5AF8]/30 via-[#4E6EFF]/20 to-[#FF58D5]/20 rounded-full blur-3xl pointer-events-none"></div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- STATS COUNTER BAR -->
    <section class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-1 sm:mt-0 relative z-20">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-4">
            
            <div class="glass-card p-3 sm:p-5 rounded-2xl border border-white/[0.08] shadow-xl flex items-center gap-2.5 sm:gap-3.5 hover:border-[#7A5AF8]/50 transition duration-300">
                <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-xl bg-[#7A5AF8]/15 border border-[#7A5AF8]/30 text-[#A594FD] flex items-center justify-center shrink-0 font-bold">
                    <i data-lucide="trophy" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                </div>
                <div class="min-w-0">
                    <div class="text-base sm:text-2xl font-black text-white leading-none">{{ $stats['total_competitions'] }}</div>
                    <div class="text-[10px] sm:text-[11px] font-medium text-slate-400 mt-1 truncate">Cabang Lomba</div>
                </div>
            </div>

            <div class="glass-card p-3 sm:p-5 rounded-2xl border border-white/[0.08] shadow-xl flex items-center gap-2.5 sm:gap-3.5 hover:border-[#4E6EFF]/50 transition duration-300">
                <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-xl bg-[#4E6EFF]/15 border border-[#4E6EFF]/30 text-[#84D0FF] flex items-center justify-center shrink-0 font-bold">
                    <i data-lucide="users" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                </div>
                <div class="min-w-0">
                    <div class="text-base sm:text-2xl font-black text-white leading-none">{{ $stats['total_participants'] }}</div>
                    <div class="text-[10px] sm:text-[11px] font-medium text-slate-400 mt-1 truncate">Pendaftar</div>
                </div>
            </div>

            <div class="glass-card p-3 sm:p-5 rounded-2xl border border-white/[0.08] shadow-xl flex items-center gap-2.5 sm:gap-3.5 hover:border-emerald-500/50 transition duration-300">
                <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 flex items-center justify-center shrink-0 font-bold">
                    <i data-lucide="check-circle" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                </div>
                <div class="min-w-0">
                    <div class="text-base sm:text-2xl font-black text-emerald-400 leading-none">{{ $stats['verified_participants'] }}</div>
                    <div class="text-[10px] sm:text-[11px] font-medium text-slate-400 mt-1 truncate">Lolos Verifikasi</div>
                </div>
            </div>

            <div class="glass-card p-3 sm:p-5 rounded-2xl border border-white/[0.08] shadow-xl flex items-center gap-2.5 sm:gap-3.5 hover:border-[#FF58D5]/50 transition duration-300">
                <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-xl bg-[#FF58D5]/15 border border-[#FF58D5]/30 text-[#FF58D5] flex items-center justify-center shrink-0 font-bold">
                    <i data-lucide="school" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                </div>
                <div class="min-w-0">
                    <div class="text-base sm:text-2xl font-black text-white leading-none">{{ $stats['total_schools'] }}</div>
                    <div class="text-[10px] sm:text-[11px] font-medium text-slate-400 mt-1 truncate">Sekolah / Madrasah</div>
                </div>
            </div>

        </div>
    </section>

    <!-- HOW IT WORKS (Direct AI StarterKit Aesthetic) -->
    <section id="cara-kerja" class="py-8 lg:py-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
        <div class="text-center max-w-2xl mx-auto mb-8 space-y-1.5">
            <span class="text-[11px] font-black uppercase tracking-widest text-[#A594FD]">{{ $appSettings['how_it_works_tagline'] ?? 'Tahapan Partisipasi' }}</span>
            <h2 class="text-2xl sm:text-3xl font-black text-white tracking-tight font-display">{{ $appSettings['how_it_works_title'] ?? ('Alur Mudah Mengikuti ' . ($appSettings['app_name'] ?? 'TALENTA')) }}</h2>
            <p class="text-xs sm:text-sm text-slate-400 leading-relaxed">
                {{ $appSettings['how_it_works_subtitle'] ?? '4 langkah terstruktur dari pembuatan akun resmi, pendaftaran, undian giliran tampil, hingga bertanding.' }}
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-y-8 gap-x-4 mt-4">
            
            <!-- Step 1 -->
            <div class="glass-card p-5 sm:p-6 rounded-2xl relative group hover:border-[#7A5AF8]/60 hover:scale-105 duration-300 transition-all border border-white/[0.08] flex flex-col justify-between">
                <div class="w-12 h-12 rounded-full flex items-center justify-center absolute -top-6 left-1/2 -translate-x-1/2 bg-gradient-to-r from-[#7A5AF8] to-[#4E6EFF] text-white font-black text-lg shadow-lg shadow-[#7A5AF8]/30">
                    <i data-lucide="user-plus" class="w-5 h-5"></i>
                </div>
                <div class="pt-4 text-center space-y-2">
                    <h3 class="text-base sm:text-lg text-white font-bold">{{ $appSettings['step_1_title'] ?? 'Buat Akun' }}</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        {{ $appSettings['step_1_desc'] ?? 'Daftar akun resmi untuk mengakses portal dashboard dan mendaftarkan peserta perlombaan.' }}
                    </p>
                </div>
                <div class="mt-4 pt-3 border-t border-white/[0.08] text-center">
                    <span class="text-[10px] font-bold text-[#A594FD] uppercase tracking-wider">Langkah 1</span>
                </div>
            </div>

            <!-- Step 2 -->
            <div class="glass-card p-5 sm:p-6 rounded-2xl relative group hover:border-[#4E6EFF]/60 hover:scale-105 duration-300 transition-all border border-white/[0.08] flex flex-col justify-between">
                <div class="w-12 h-12 rounded-full flex items-center justify-center absolute -top-6 left-1/2 -translate-x-1/2 bg-gradient-to-r from-[#4E6EFF] to-cyan-500 text-white font-black text-lg shadow-lg shadow-[#4E6EFF]/30">
                    <i data-lucide="file-text" class="w-5 h-5"></i>
                </div>
                <div class="pt-4 text-center space-y-2">
                    <h3 class="text-base sm:text-lg text-white font-bold">{{ $appSettings['step_2_title'] ?? 'Pilih Lomba' }}</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        {{ $appSettings['step_2_desc'] ?? 'Pilih cabang lomba yang diminati, isi biodata peserta/tim, dan unggah berkas syarat pendukung.' }}
                    </p>
                </div>
                <div class="mt-4 pt-3 border-t border-white/[0.08] text-center">
                    <span class="text-[10px] font-bold text-[#84D0FF] uppercase tracking-wider">Langkah 2</span>
                </div>
            </div>

            <!-- Step 3 -->
            <div class="glass-card p-5 sm:p-6 rounded-2xl relative group hover:border-[#FF58D5]/60 hover:scale-105 duration-300 transition-all border border-white/[0.08] flex flex-col justify-between">
                <div class="w-12 h-12 rounded-full flex items-center justify-center absolute -top-6 left-1/2 -translate-x-1/2 bg-gradient-to-r from-[#FF58D5] to-purple-600 text-white font-black text-lg shadow-lg shadow-[#FF58D5]/30">
                    <i data-lucide="disc" class="w-5 h-5"></i>
                </div>
                <div class="pt-4 text-center space-y-2">
                    <h3 class="text-base sm:text-lg text-white font-bold">{{ $appSettings['step_3_title'] ?? 'Spin Undian' }}</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        {{ $appSettings['step_3_desc'] ?? 'Verifikasi berkas oleh PIC dan pengundian nomor urut tampil secara transparan via Interactive Spin Wheel.' }}
                    </p>
                </div>
                <div class="mt-4 pt-3 border-t border-white/[0.08] text-center">
                    <span class="text-[10px] font-bold text-[#FF58D5] uppercase tracking-wider">Langkah 3</span>
                </div>
            </div>

            <!-- Step 4 -->
            <div class="glass-card p-5 sm:p-6 rounded-2xl relative group hover:border-emerald-500/60 hover:scale-105 duration-300 transition-all border border-white/[0.08] flex flex-col justify-between">
                <div class="w-12 h-12 rounded-full flex items-center justify-center absolute -top-6 left-1/2 -translate-x-1/2 bg-gradient-to-r from-emerald-500 to-teal-400 text-slate-950 font-black text-lg shadow-lg shadow-emerald-500/30">
                    <i data-lucide="tv" class="w-5 h-5"></i>
                </div>
                <div class="pt-4 text-center space-y-2">
                    <h3 class="text-base sm:text-lg text-white font-bold">{{ $appSettings['step_4_title'] ?? 'Live Scoring' }}</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        {{ $appSettings['step_4_desc'] ?? 'Pelaksanaan lomba, penilaian digital oleh dewan juri, dan skor tampil langsung di Live Scoreboard.' }}
                    </p>
                </div>
                <div class="mt-4 pt-3 border-t border-white/[0.08] text-center">
                    <span class="text-[10px] font-bold text-emerald-400 uppercase tracking-wider">Langkah 4</span>
                </div>
            </div>

        </div>
    </section>

    <!-- LIVE ARENA / MARKET TREND STYLE TABLE (Crypto-NextJS Inspired) -->
    <section id="kategori" class="py-6 lg:py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="glass-card p-5 sm:p-7 rounded-2xl border border-white/[0.08] shadow-2xl relative overflow-hidden space-y-5">
            
            @php
                $regInfo = \App\Models\AppSetting::getRegistrationStatusInfo();
            @endphp
            <!-- Section Header & Filter Navigation (Full Width, No Cramping) -->
            <div class="space-y-4 border-b border-white/[0.08] pb-4">
                
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
                    <div>
                        <span class="text-[11px] font-black uppercase tracking-widest text-[#A594FD]">{{ $appSettings['catalog_tagline'] ?? 'Live Status Cabang Lomba' }}</span>
                        <h2 class="text-xl sm:text-2xl font-black text-white tracking-tight mt-0.5 font-display">
                            {{ $appSettings['catalog_title'] ?? 'Katalog & Kuota Perlombaan' }}
                        </h2>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 self-start sm:self-auto">
                        @if($regInfo['status_code'] === 'not_started' && $regInfo['start_date_formatted'])
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-amber-500/15 text-amber-300 border border-amber-500/30">
                                <i data-lucide="clock" class="w-3.5 h-3.5 text-amber-400"></i>
                                <span>Buka: {{ $regInfo['start_date_formatted'] }} WIB</span>
                            </span>
                        @elseif($regInfo['deadline_formatted'])
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold {{ $regInfo['is_open'] ? 'bg-indigo-500/15 text-indigo-300 border border-indigo-500/30' : 'bg-rose-500/15 text-rose-300 border border-rose-500/30' }}">
                                <i data-lucide="{{ $regInfo['is_open'] ? 'timer' : 'alert-circle' }}" class="w-3.5 h-3.5 {{ $regInfo['is_open'] ? 'text-amber-400' : 'text-rose-400' }}"></i>
                                <span>{{ $regInfo['is_open'] ? 'Batas Akhir: ' . $regInfo['deadline_formatted'] . ' WIB' : 'Pendaftaran Ditutup' }}</span>
                            </span>
                        @endif
                        <span class="text-xs font-medium text-slate-400 flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl bg-white/[0.04] border border-white/[0.06]">
                            <span class="w-2 h-2 rounded-full bg-[#7A5AF8] animate-ping"></span>
                            <span>Live Kuota</span>
                        </span>
                    </div>
                </div>

                @if(!$regInfo['is_open'])
                    <div class="p-3.5 sm:p-4 rounded-2xl {{ $regInfo['status_code'] === 'not_started' ? 'bg-amber-500/10 border border-amber-500/30 text-amber-300' : 'bg-rose-500/10 border border-rose-500/30 text-rose-300' }} flex items-start sm:items-center gap-3 text-xs">
                        <i data-lucide="{{ $regInfo['status_code'] === 'not_started' ? 'clock' : 'lock' }}" class="w-5 h-5 {{ $regInfo['status_code'] === 'not_started' ? 'text-amber-400' : 'text-rose-400' }} shrink-0 mt-0.5 sm:mt-0"></i>
                        <div>
                            <strong class="font-bold text-white">{{ $regInfo['status_label'] }}:</strong>
                            <span>{{ $regInfo['closed_message'] }}</span>
                        </div>
                    </div>
                @endif

                <!-- Category Filter Pills (Aligned with Admin Master Categories) -->
                <div class="flex flex-wrap items-center gap-2">
                    <button @click="activeCategory = 'all'" :class="activeCategory === 'all' ? 'bg-gradient-to-r from-[#7A5AF8] to-[#4E6EFF] text-white font-black shadow-lg shadow-[#7A5AF8]/30 ring-2 ring-[#7A5AF8]/50' : 'bg-[#0C111D]/80 text-slate-300 hover:bg-white/[0.06] hover:text-white border border-white/[0.08]'" class="px-3.5 py-2 rounded-xl text-xs font-bold transition cursor-pointer">
                        Semua Lomba
                    </button>

                    @foreach($categories as $cat)
                        <button @click="activeCategory = '{{ $cat->slug }}'" :class="activeCategory === '{{ $cat->slug }}' ? 'bg-gradient-to-r from-[#7A5AF8] to-[#4E6EFF] text-white font-black shadow-lg shadow-[#7A5AF8]/30 ring-2 ring-[#7A5AF8]/50' : 'bg-[#0C111D]/80 text-slate-300 hover:bg-white/[0.06] hover:text-white border border-white/[0.08]'" class="px-3.5 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5 cursor-pointer">
                            <i data-lucide="{{ $cat->icon ?: 'folder' }}" class="w-3.5 h-3.5" :class="activeCategory === '{{ $cat->slug }}' ? 'text-white' : 'text-[#A594FD]'"></i>
                            <span>{{ $cat->name }}</span>
                        </button>
                    @endforeach
                </div>

            </div>

            <!-- Table of Competitions (Crypto-NextJS Style) -->
            <div class="overflow-x-auto no-scrollbar rounded-xl border border-white/[0.08]">
                <table class="w-full text-left text-sm text-slate-300">
                    <thead class="text-[11px] font-bold uppercase tracking-wider bg-[#0C111D]/90 text-slate-400 border-b border-white/[0.08]">
                        <tr>
                            <th class="py-3 px-5 whitespace-nowrap min-w-[220px]">Nama Lomba</th>
                            <th class="py-3 px-5 whitespace-nowrap min-w-[180px]">Kategori</th>
                            <th class="py-3 px-5 whitespace-nowrap min-w-[180px]">Sisa Kuota</th>
                            <th class="py-3 px-4 text-center whitespace-nowrap min-w-[120px]">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/[0.04] font-medium text-xs sm:text-sm">
                        @foreach($competitions as $comp)
                            @php
                                $isBlt = $comp->code === 'BLT';
                                $isTmj = $comp->code === 'TMJ';
                                $isMtqPop = in_array($comp->code, ['MTQ', 'POP']);

                                if ($isBlt) {
                                    $countBltTunggalPaA = $comp->registrations->filter(fn($r) => !$r->isGanda() && $r->primary_gender === 'L' && $r->isKatA())->count();
                                    $countBltTunggalPaB = $comp->registrations->filter(fn($r) => !$r->isGanda() && $r->primary_gender === 'L' && $r->isKatB())->count();
                                    $countBltTunggalPaC = $comp->registrations->filter(fn($r) => !$r->isGanda() && $r->primary_gender === 'L' && $r->isKatC())->count();
                                    
                                    $countBltTunggalPiA = $comp->registrations->filter(fn($r) => !$r->isGanda() && $r->primary_gender === 'P' && $r->isKatA())->count();
                                    $countBltTunggalPiB = $comp->registrations->filter(fn($r) => !$r->isGanda() && $r->primary_gender === 'P' && $r->isKatB())->count();
                                    $countBltTunggalPiC = $comp->registrations->filter(fn($r) => !$r->isGanda() && $r->primary_gender === 'P' && $r->isKatC())->count();
                                    
                                    $countBltGandaPa = $comp->registrations->filter(fn($r) => $r->isGanda() && $r->primary_gender === 'L')->count();
                                    $countBltGandaPi = $comp->registrations->filter(fn($r) => $r->isGanda() && $r->primary_gender === 'P')->count();
                                } elseif ($isTmj) {
                                    $countTmjTunggalPaA = $comp->registrations->filter(fn($r) => !$r->isGanda() && $r->primary_gender === 'L' && $r->isKatA())->count();
                                    $countTmjTunggalPaB = $comp->registrations->filter(fn($r) => !$r->isGanda() && $r->primary_gender === 'L' && $r->isKatB())->count();
                                    $countTmjTunggalPiA = $comp->registrations->filter(fn($r) => !$r->isGanda() && $r->primary_gender === 'P' && $r->isKatA())->count();
                                    $countTmjTunggalPiB = $comp->registrations->filter(fn($r) => !$r->isGanda() && $r->primary_gender === 'P' && $r->isKatB())->count();
                                } elseif ($isMtqPop) {
                                    $countPa = $comp->registrations->filter(fn($r) => $r->primary_gender === 'L')->count();
                                    $countPi = $comp->registrations->filter(fn($r) => $r->primary_gender === 'P')->count();
                                    $totalMtqPop = $countPa + $countPi;
                                    $quotaMtqPop = (int) ($comp->quota ?? 50);
                                }

                                $renderTierQuota = function($count, $quota, $unit = 'Peserta') {
                                    $quota = (int) $quota;
                                    $isUnlimited = ($quota <= 0);
                                    if ($isUnlimited) {
                                        $hasParticipants = $count > 0;
                                        $barWidth = $hasParticipants ? min(100, max(25, $count * 10)) : 0;
                                        $barHtml = $hasParticipants 
                                            ? '<div class="bg-gradient-to-r from-purple-500 via-indigo-500 to-[#4E6EFF] h-full rounded-full animate-pulse shadow-sm shadow-purple-500/30" style="width: ' . $barWidth . '%"></div>'
                                            : '<div class="bg-slate-700/40 h-full rounded-full" style="width: 0%"></div>';
                                            
                                        return '
                                        <div>
                                            <div class="flex items-center justify-between text-xs font-bold gap-3">
                                                <span class="text-purple-300 font-black flex items-center gap-1">
                                                    <span class="text-sm leading-none font-sans">∞</span>
                                                    <span>Tak Terbatas</span>
                                                </span>
                                                <span class="text-slate-400 font-semibold text-[11px] font-mono">' . $count . ' / ∞</span>
                                            </div>
                                            <div class="w-full bg-white/[0.08] h-2 rounded-full overflow-hidden mt-1 p-0.5 border border-white/[0.05]">
                                                ' . $barHtml . '
                                            </div>
                                        </div>';
                                    } else {
                                        $sisa = max(0, $quota - $count);
                                        $isFull = ($sisa <= 0);
                                        $isLow = ($sisa > 0 && $sisa <= 5);
                                        $textColor = $isFull ? 'text-rose-400 font-black' : ($isLow ? 'text-amber-300 font-black' : 'text-emerald-400 font-extrabold');
                                        $sisaText = $isFull ? 'Penuh' : 'Sisa: ' . $sisa . ' ' . $unit;
                                        $barWidth = min(100, ($count / max(1, $quota)) * 100);
                                        $barGradient = $isFull ? 'from-rose-500 to-red-600' : ($isLow ? 'from-amber-400 to-orange-500' : 'from-[#7A5AF8] to-[#4E6EFF]');
                                        return '
                                        <div>
                                            <div class="flex items-center justify-between text-xs font-bold gap-3">
                                                <span class="' . $textColor . '">' . $sisaText . '</span>
                                                <span class="text-slate-400 font-medium text-[11px]">' . $count . '/' . $quota . '</span>
                                            </div>
                                            <div class="w-full bg-white/[0.08] h-2 rounded-full overflow-hidden mt-1 p-0.5 border border-white/[0.05]">
                                                <div class="bg-gradient-to-r ' . $barGradient . ' h-full rounded-full" style="width: ' . $barWidth . '%"></div>
                                            </div>
                                        </div>';
                                    }
                                };

                                $categorySlug = $comp->category->slug ?? '';
                                $rowTheme = match($categorySlug) {
                                    'seni' => [
                                        'bg' => 'bg-pink-500/[0.03] hover:bg-pink-500/[0.07]',
                                        'border_l' => 'border-l-4 border-l-pink-500/80',
                                        'badge' => 'bg-pink-500/15 text-pink-300 border border-pink-500/30',
                                        'icon_color' => 'text-pink-400',
                                    ],
                                    'olahraga' => [
                                        'bg' => 'bg-emerald-500/[0.025] hover:bg-emerald-500/[0.06]',
                                        'border_l' => 'border-l-4 border-l-emerald-500/80',
                                        'badge' => 'bg-emerald-500/15 text-emerald-300 border border-emerald-500/30',
                                        'icon_color' => 'text-emerald-400',
                                    ],
                                    'teknologi' => [
                                        'bg' => 'bg-[#4E6EFF]/[0.03] hover:bg-[#4E6EFF]/[0.07]',
                                        'border_l' => 'border-l-4 border-l-[#4E6EFF]/80',
                                        'badge' => 'bg-[#4E6EFF]/15 text-[#84D0FF] border border-[#4E6EFF]/30',
                                        'icon_color' => 'text-[#4E6EFF]',
                                    ],
                                    'pramuka' => [
                                        'bg' => 'bg-amber-500/[0.03] hover:bg-amber-500/[0.07]',
                                        'border_l' => 'border-l-4 border-l-amber-500/80',
                                        'badge' => 'bg-amber-500/15 text-amber-300 border border-amber-500/30',
                                        'icon_color' => 'text-amber-400',
                                    ],
                                    'tahfidz' => [
                                        'bg' => 'bg-teal-500/[0.03] hover:bg-teal-500/[0.07]',
                                        'border_l' => 'border-l-4 border-l-teal-500/80',
                                        'badge' => 'bg-teal-500/15 text-teal-300 border border-teal-500/30',
                                        'icon_color' => 'text-teal-400',
                                    ],
                                    default => [
                                        'bg' => 'bg-[#7A5AF8]/[0.03] hover:bg-[#7A5AF8]/[0.07]',
                                        'border_l' => 'border-l-4 border-l-[#7A5AF8]/80',
                                        'badge' => 'bg-[#7A5AF8]/15 text-[#A594FD] border border-[#7A5AF8]/30',
                                        'icon_color' => 'text-[#A594FD]',
                                    ],
                                };
                            @endphp

                            <tr x-show="activeCategory === 'all' || activeCategory === '{{ $comp->category->slug }}'" class="{{ $rowTheme['bg'] }} {{ $rowTheme['border_l'] }} transition-colors duration-150 border-b border-white/[0.05]">
                                
                                <td class="py-3.5 px-5 min-w-[200px] align-middle">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <a href="{{ route('competition.detail', $comp->slug) }}" class="font-extrabold text-white hover:text-[#A594FD] transition text-sm sm:text-base block">
                                            {{ $comp->name }}
                                        </a>
                                        @if($comp->code === 'MIPA')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black bg-gradient-to-r from-amber-400 to-amber-500 text-slate-950 shadow-sm shadow-amber-500/20">
                                                🎁 Bonus 10 Get 1
                                            </span>
                                        @endif
                                    </div>
                                    <span class="text-[11px] text-slate-400 flex items-center gap-1 mt-0.5">
                                        <i data-lucide="map-pin" class="w-3 h-3 text-[#4E6EFF] shrink-0"></i>
                                        <span>{{ $comp->venue ?? 'Kampus MTsN 1 Blitar' }}</span>
                                    </span>
                                </td>

                                <td class="py-3.5 px-5 text-xs whitespace-nowrap align-middle">
                                    @if($isBlt)
                                        <div class="flex flex-col py-1">
                                            <!-- 1. Tunggal PA -->
                                            <div class="flex flex-col justify-center">
                                                <div class="flex items-center gap-1.5 font-bold text-emerald-400 text-xs mb-1">
                                                    <i data-lucide="user" class="w-3.5 h-3.5"></i>
                                                    <span>Tunggal | PA</span>
                                                </div>
                                                <div class="space-y-1.5 text-[10px] text-slate-400 pl-5">
                                                    <div class="py-0.5">Kat A (Kelas 1-2)</div>
                                                    <div class="py-0.5">Kat B (Kelas 3-4)</div>
                                                    <div class="py-0.5">Kat C (Kelas 5-6)</div>
                                                </div>
                                            </div>
                                            <div class="border-t border-white/[0.08] my-2"></div>
                                            <!-- 2. Tunggal PI -->
                                            <div class="flex flex-col justify-center">
                                                <div class="flex items-center gap-1.5 font-bold text-pink-400 text-xs mb-1">
                                                    <i data-lucide="user" class="w-3.5 h-3.5"></i>
                                                    <span>Tunggal | PI</span>
                                                </div>
                                                <div class="space-y-1.5 text-[10px] text-slate-400 pl-5">
                                                    <div class="py-0.5">Kat A (Kelas 1-2)</div>
                                                    <div class="py-0.5">Kat B (Kelas 3-4)</div>
                                                    <div class="py-0.5">Kat C (Kelas 5-6)</div>
                                                </div>
                                            </div>
                                            <div class="border-t border-white/[0.08] my-2"></div>
                                            <!-- 3. Ganda PA -->
                                            <div class="py-0.5 flex items-center gap-1.5 font-bold text-[#A594FD] text-xs">
                                                <i data-lucide="users" class="w-3.5 h-3.5 text-[#7A5AF8]"></i>
                                                <span>Ganda | PA</span>
                                            </div>
                                            <div class="border-t border-white/[0.08] my-2"></div>
                                            <!-- 4. Ganda PI -->
                                            <div class="py-0.5 flex items-center gap-1.5 font-bold text-amber-300 text-xs">
                                                <i data-lucide="users" class="w-3.5 h-3.5 text-amber-400"></i>
                                                <span>Ganda | PI</span>
                                            </div>
                                        </div>
                                    @elseif($isMtqPop)
                                        <div class="flex items-center gap-1.5 font-bold text-emerald-400 text-xs py-1">
                                            <i data-lucide="users" class="w-3.5 h-3.5"></i>
                                            <span>Individu (PA & PI)</span>
                                        </div>
                                    @elseif($isTmj)
                                        <div class="flex flex-col py-1">
                                            <!-- 1. Tunggal PA -->
                                            <div class="flex flex-col justify-center">
                                                <div class="flex items-center gap-1.5 font-bold text-emerald-400 text-xs mb-1">
                                                    <i data-lucide="user" class="w-3.5 h-3.5"></i>
                                                    <span>Tunggal | PA</span>
                                                </div>
                                                <div class="space-y-1.5 text-[10px] text-slate-400 pl-5">
                                                    <div class="py-0.5">Kat A (Kelas 1-3)</div>
                                                    <div class="py-0.5">Kat B (Kelas 4-6)</div>
                                                </div>
                                            </div>
                                            <div class="border-t border-white/[0.08] my-2"></div>
                                            <!-- 2. Tunggal PI -->
                                            <div class="flex flex-col justify-center">
                                                <div class="flex items-center gap-1.5 font-bold text-pink-400 text-xs mb-1">
                                                    <i data-lucide="user" class="w-3.5 h-3.5"></i>
                                                    <span>Tunggal | PI</span>
                                                </div>
                                                <div class="space-y-1.5 text-[10px] text-slate-400 pl-5">
                                                    <div class="py-0.5">Kat A (Kelas 1-3)</div>
                                                    <div class="py-0.5">Kat B (Kelas 4-6)</div>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="capitalize text-slate-200 font-bold text-xs">{{ $comp->type }}</span>
                                    @endif
                                </td>

                                <td class="py-3.5 px-5 whitespace-nowrap align-middle">
                                    @if($isBlt)
                                        <div class="flex flex-col py-1 text-slate-400 text-xs min-w-[210px]">
                                            <!-- Kuota Tunggal PA -->
                                            <div class="space-y-1.5">
                                                <!-- Kat A -->
                                                {!! $renderTierQuota($countBltTunggalPaA, $comp->tier_quotas['A_tunggal_pa'] ?? 16, 'Peserta') !!}
                                                <!-- Kat B -->
                                                {!! $renderTierQuota($countBltTunggalPaB, $comp->tier_quotas['B_tunggal_pa'] ?? 16, 'Peserta') !!}
                                                <!-- Kat C -->
                                                {!! $renderTierQuota($countBltTunggalPaC, $comp->tier_quotas['C_tunggal_pa'] ?? 16, 'Peserta') !!}
                                            </div>

                                            <div class="border-t border-white/[0.08] my-2"></div>

                                            <!-- Kuota Tunggal PI -->
                                            <div class="space-y-1.5">
                                                <!-- Kat A -->
                                                {!! $renderTierQuota($countBltTunggalPiA, $comp->tier_quotas['A_tunggal_pi'] ?? 16, 'Peserta') !!}
                                                <!-- Kat B -->
                                                {!! $renderTierQuota($countBltTunggalPiB, $comp->tier_quotas['B_tunggal_pi'] ?? 16, 'Peserta') !!}
                                                <!-- Kat C -->
                                                {!! $renderTierQuota($countBltTunggalPiC, $comp->tier_quotas['C_tunggal_pi'] ?? 16, 'Peserta') !!}
                                            </div>

                                            <div class="border-t border-white/[0.08] my-2"></div>

                                            <!-- Kuota Ganda PA -->
                                            {!! $renderTierQuota($countBltGandaPa, $comp->tier_quotas['ganda_pa'] ?? 0, 'Pasangan') !!}

                                            <div class="border-t border-white/[0.08] my-2"></div>

                                            <!-- Kuota Ganda PI -->
                                            {!! $renderTierQuota($countBltGandaPi, $comp->tier_quotas['ganda_pi'] ?? 0, 'Pasangan') !!}
                                        </div>
                                    @elseif($isMtqPop)
                                        <div class="flex flex-col py-1 text-slate-400 text-xs min-w-[210px] space-y-2">
                                            <!-- Main Combined Quota Bar (Total 50) -->
                                            {!! $renderTierQuota($totalMtqPop, $quotaMtqPop, 'Peserta') !!}
                                            
                                            <!-- Real-time Gender Composition Breakdown -->
                                            <div class="flex items-center justify-between gap-1.5 px-2.5 py-1 rounded-xl bg-white/[0.04] border border-white/[0.08] text-[10px] font-bold">
                                                <span class="text-cyan-300 flex items-center gap-1">
                                                    <i data-lucide="user" class="w-3 h-3 text-cyan-400"></i>
                                                    <span>{{ $countPa }} Putra</span>
                                                </span>
                                                <span class="text-slate-500">•</span>
                                                <span class="text-pink-300 flex items-center gap-1">
                                                    <i data-lucide="user" class="w-3 h-3 text-pink-400"></i>
                                                    <span>{{ $countPi }} Putri</span>
                                                </span>
                                            </div>
                                        </div>
                                    @elseif($isTmj)
                                        <div class="flex flex-col py-1 text-slate-400 text-xs min-w-[210px]">
                                            <!-- Kuota Tunggal PA -->
                                            <div class="space-y-1.5">
                                                <!-- Kat A -->
                                                {!! $renderTierQuota($countTmjTunggalPaA, $comp->tier_quotas['A_tunggal_pa'] ?? 10, 'Peserta') !!}
                                                <!-- Kat B -->
                                                {!! $renderTierQuota($countTmjTunggalPaB, $comp->tier_quotas['B_tunggal_pa'] ?? 10, 'Peserta') !!}
                                            </div>

                                            <div class="border-t border-white/[0.08] my-2"></div>

                                            <!-- Kuota Tunggal PI -->
                                            <div class="space-y-1.5">
                                                <!-- Kat A -->
                                                {!! $renderTierQuota($countTmjTunggalPiA, $comp->tier_quotas['A_tunggal_pi'] ?? 10, 'Peserta') !!}
                                                <!-- Kat B -->
                                                {!! $renderTierQuota($countTmjTunggalPiB, $comp->tier_quotas['B_tunggal_pi'] ?? 10, 'Peserta') !!}
                                            </div>
                                        </div>
                                    @else
                                        @php
                                            $regQuota = (int) ($comp->quota ?? 0);
                                            $regCount = $comp->registrations_count;
                                            $unitWord = match(strtolower($comp->type)) {
                                                'regu' => 'Regu',
                                                'tim' => 'Tim',
                                                'kelompok' => 'Kelompok',
                                                default => 'Peserta',
                                            };
                                        @endphp
                                        @if($regQuota <= 0)
                                            @php
                                                $hasRegs = $regCount > 0;
                                                $barWidth = $hasRegs ? min(100, max(25, $regCount * 10)) : 0;
                                            @endphp
                                            <div class="space-y-1.5 min-w-[210px]">
                                                <div class="flex items-center justify-between gap-3">
                                                    <span class="text-xs sm:text-sm text-purple-300 font-black flex items-center gap-1">
                                                        <span class="text-sm leading-none font-sans">∞</span>
                                                        <span>Tak Terbatas</span>
                                                    </span>
                                                    <span class="text-[11px] sm:text-xs font-semibold text-slate-400 font-mono">
                                                        {{ $regCount }} / ∞
                                                    </span>
                                                </div>
                                                <div class="w-full bg-white/[0.08] h-2.5 rounded-full overflow-hidden p-0.5 border border-white/[0.05]">
                                                    @if($hasRegs)
                                                        <div class="bg-gradient-to-r from-purple-500 via-indigo-500 to-[#4E6EFF] h-full rounded-full animate-pulse shadow-sm shadow-purple-500/30" style="width: {{ $barWidth }}%"></div>
                                                    @else
                                                        <div class="bg-slate-700/40 h-full rounded-full" style="width: 0%"></div>
                                                    @endif
                                                </div>
                                            </div>
                                        @else
                                            @php
                                                $sisa = max(0, $regQuota - $regCount);
                                                $isFull = $sisa <= 0;
                                                $isLow = $sisa > 0 && $sisa <= 5;
                                            @endphp
                                            <div class="space-y-1.5 min-w-[210px]">
                                                <div class="flex items-center justify-between gap-3">
                                                    <span class="text-xs sm:text-sm {{ $isFull ? 'text-rose-400 font-black' : ($isLow ? 'text-amber-300 font-black' : 'text-emerald-400 font-black') }}">
                                                        {{ $isFull ? 'Kuota Penuh' : 'Sisa: ' . $sisa . ' ' . $unitWord }}
                                                    </span>
                                                    <span class="text-[11px] sm:text-xs font-semibold text-slate-400">
                                                        {{ $regCount }}/{{ $regQuota }}
                                                    </span>
                                                </div>
                                                <div class="w-full bg-white/[0.08] h-2.5 rounded-full overflow-hidden p-0.5 border border-white/[0.05]">
                                                    <div class="bg-gradient-to-r {{ $isFull ? 'from-rose-500 to-red-600' : ($isLow ? 'from-amber-400 to-orange-500' : 'from-[#7A5AF8] to-[#4E6EFF]') }} h-full rounded-full transition-all duration-300 shadow-sm" style="width: {{ min(100, ($regCount / max(1, $regQuota)) * 100) }}%"></div>
                                                </div>
                                            </div>
                                        @endif
                                    @endif
                                </td>

                                <td class="py-3.5 px-4 text-center whitespace-nowrap min-w-[120px] align-middle">
                                    <div class="inline-flex items-center justify-center gap-1.5">
                                        <a href="{{ route('competition.detail', $comp->slug) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white/[0.05] hover:bg-gradient-to-r hover:from-[#7A5AF8] hover:to-[#4E6EFF] hover:text-white text-slate-200 text-xs font-bold border border-white/[0.1] transition-all duration-200 shadow-sm group" title="Lihat Juknis & Detail {{ $comp->name }}">
                                            <i data-lucide="file-text" class="w-3.5 h-3.5 text-[#A594FD] group-hover:text-white transition-colors"></i>
                                            <span>Juknis & Detail</span>
                                        </a>

                                        @if(!empty($comp->whatsapp_group_url))
                                            <a href="{{ $comp->whatsapp_group_url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-500/15 hover:bg-emerald-500 text-emerald-400 hover:text-slate-950 text-xs font-bold border border-emerald-500/30 hover:border-emerald-500 transition-all duration-200 shadow-sm shadow-emerald-500/10 group" title="Gabung Grup WhatsApp Resmi {{ $comp->name }}">
                                                <i data-lucide="message-circle" class="w-3.5 h-3.5 text-emerald-400 group-hover:text-slate-950 transition-colors"></i>
                                                <span>Grup WA</span>
                                            </a>
                                        @endif
                                    </div>
                                </td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </section>

    <!-- PAMFLET / BROSUR RESMI EVENT (AUTO-SLIDE CAROUSEL & ZOOM LIGHTBOX) -->
    @php
        $pamphletImages = json_decode($appSettings['pamphlet_images'] ?? '[]', true) ?: [];
        $rawPamphletEmbed = $appSettings['pamphlet_embed_url'] ?? '';
        
        $pamphletList = [];
        
        // 1. Uploaded image files
        foreach ($pamphletImages as $img) {
            if (!empty($img)) {
                $pamphletList[] = [
                    'type' => 'image',
                    'url' => asset('storage/' . $img),
                ];
            }
        }

        $showPamphletEmbed = ($appSettings['show_pamphlet_embed'] ?? '1') !== '0';

        // 2. Canva embed links (if active)
        if ($showPamphletEmbed && !empty($rawPamphletEmbed)) {
            $lines = preg_split('/[\r\n]+/', trim($rawPamphletEmbed));
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;
                
                $canvaUrl = '';
                if (preg_match('/src=["\']([^"\']+)["\']/', $line, $matches)) {
                    $canvaUrl = $matches[1];
                } else {
                    $canvaUrl = $line;
                }

                if (str_contains($canvaUrl, 'canva.com/design/')) {
                    if (!str_contains($canvaUrl, '?embed') && !str_contains($canvaUrl, '&embed')) {
                        $canvaUrl = preg_replace('#/watch(\?.*)?$#', '/view', $canvaUrl);
                        if (!str_contains($canvaUrl, '/view')) {
                            $canvaUrl = rtrim($canvaUrl, '/') . '/view';
                        }
                        $canvaUrl .= '?embed';
                    }
                }
                
                if (!empty($canvaUrl) && (str_starts_with($canvaUrl, 'http://') || str_starts_with($canvaUrl, 'https://'))) {
                    $pamphletList[] = [
                        'type' => 'canva',
                        'url' => $canvaUrl,
                    ];
                }
            }
        }

        $totalPamphlets = count($pamphletList);
    @endphp

    @if($totalPamphlets > 0)
        <section id="pamflet" class="py-6 lg:py-10 max-w-7xl mx-auto px-2 sm:px-6 lg:px-8 relative"
            x-data="{
                active: 0,
                total: {{ $totalPamphlets }},
                autoTimer: null,
                isHovered: false,
                isZoomed: false,
                zoomUrl: '',
                zoomType: 'image',
                init() {
                    if (this.total > 1) {
                        this.startTimer();
                    }
                },
                startTimer() {
                    this.autoTimer = setInterval(() => {
                        if (!this.isHovered && !this.isZoomed) {
                            this.next();
                        }
                    }, 5000);
                },
                stopTimer() {
                    if (this.autoTimer) clearInterval(this.autoTimer);
                },
                next() {
                    this.active = (this.active + 1) % this.total;
                },
                prev() {
                    this.active = (this.active - 1 + this.total) % this.total;
                },
                openZoom(url, type = 'image') {
                    this.zoomUrl = url;
                    this.zoomType = type;
                    this.isZoomed = true;
                }
            }"
            @mouseenter="isHovered = true"
            @mouseleave="isHovered = false"
            x-init="init()">
            
            <div class="glass-card p-3 sm:p-6 lg:p-8 rounded-2xl sm:rounded-3xl border border-white/[0.08] shadow-2xl relative overflow-hidden space-y-4 sm:space-y-6">
                
                <!-- Section Header -->
                <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 border-b border-white/[0.08] pb-4 px-2 sm:px-0">
                    <div class="space-y-1.5">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-pink-500/15 border border-pink-500/30 text-pink-300 text-[11px] font-bold tracking-wider uppercase shadow-xs">
                            <i data-lucide="image" class="w-3.5 h-3.5 text-pink-400"></i>
                            <span>Pamflet & Brosur Resmi</span>
                        </span>
                        <h2 class="text-xl sm:text-2xl lg:text-3xl font-black text-white tracking-tight font-display">
                            Pamflet Informasi {{ $appSettings['app_name'] ?? 'TALENTA' }} {{ $appSettings['event_year'] ?? '2026' }}
                        </h2>
                        <p class="text-xs text-slate-400 max-w-2xl leading-relaxed">
                            Simak informasi lengkap petunjuk dan pamflet resmi penyelenggaraan acara langsung di bawah ini.
                        </p>
                    </div>

                    @if($totalPamphlets > 1)
                        <!-- Carousel Counter Indicator -->
                        <div class="flex items-center gap-2 self-start sm:self-auto">
                            <span class="text-xs font-mono font-bold px-3 py-1 rounded-full bg-pink-500/20 text-pink-300 border border-pink-500/30">
                                Pamflet <span x-text="active + 1"></span> dari <span x-text="total"></span>
                            </span>
                        </div>
                    @endif
                </div>

                <!-- Showcase / Carousel Slider Container (Responsive Large Frame) -->
                <div class="relative w-full flex flex-col items-center justify-center py-1 sm:py-3">
                    
                    <!-- Ambient Glow Aura behind Poster -->
                    <div class="absolute w-[320px] sm:w-[600px] lg:w-[800px] h-[450px] sm:h-[800px] bg-gradient-to-tr from-pink-500/20 via-[#7A5AF8]/15 to-[#4E6EFF]/15 rounded-full blur-3xl pointer-events-none -z-0"></div>

                    <div class="relative z-10 w-full flex items-center justify-center">
                        @foreach($pamphletList as $idx => $item)
                            <div x-show="active === {{ $idx }}" 
                                x-transition:enter="transition ease-out duration-500 transform"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-300 transform absolute"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95"
                                class="w-full flex flex-col items-center justify-center">

                                @if($item['type'] === 'image')
                                    <!-- IMAGE POSTER: Full Responsive Scale on Mobile & Desktop -->
                                    <div class="relative group cursor-zoom-in flex flex-col items-center justify-center w-full max-w-full sm:max-w-xl md:max-w-2xl lg:max-w-3xl xl:max-w-4xl mx-auto" @click="openZoom('{{ $item['url'] }}', 'image')">
                                        <img src="{{ $item['url'] }}" 
                                            alt="Pamflet {{ $idx + 1 }}" 
                                            class="w-full h-auto max-h-[88vh] sm:max-h-[92vh] object-contain rounded-2xl sm:rounded-3xl shadow-2xl border-2 border-white/[0.15] transition-transform duration-300 group-hover:scale-[1.01]">
                                        
                                        <!-- Zoom Hover Pill -->
                                        <div class="absolute bottom-3 px-4 py-2 rounded-full bg-black/85 backdrop-blur-md border border-white/20 text-white text-xs font-bold flex items-center gap-2 opacity-90 group-hover:opacity-100 transition shadow-xl pointer-events-none">
                                            <i data-lucide="zoom-in" class="w-4 h-4 text-pink-400"></i>
                                            <span>Klik untuk Perbesar Layar Penuh</span>
                                        </div>
                                    </div>
                                @else
                                    <!-- EMBED PAMFLET: Exact A4 Portrait Proportions (Large on Mobile & Desktop) -->
                                    <div class="w-full max-w-full sm:max-w-xl md:max-w-2xl lg:max-w-3xl xl:max-w-4xl mx-auto space-y-3">
                                        
                                        <!-- Portrait Container: 141.4286% Aspect Ratio (A4 Standard) -->
                                        <div style="position: relative; width: 100%; height: 0; padding-top: 141.4286%; overflow: hidden; border-radius: 24px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.7); border: 2px solid rgba(255, 255, 255, 0.15); background: #0C111D;">
                                            <iframe loading="lazy" 
                                                style="position: absolute; width: 100%; height: 100%; top: 0; left: 0; border: none; padding: 0; margin: 0;"
                                                src="{{ $item['url'] }}" 
                                                allowfullscreen="allowfullscreen" 
                                                allow="fullscreen">
                                            </iframe>
                                        </div>

                                        <!-- Clean Fullscreen Button -->
                                        <div class="flex items-center justify-center pt-1">
                                            <button type="button" 
                                                @click="openZoom('{{ $item['url'] }}', 'canva')" 
                                                class="px-5 py-2.5 rounded-xl bg-white/[0.08] hover:bg-white/[0.15] text-white border border-white/[0.12] text-xs font-bold transition flex items-center gap-2 shadow-sm cursor-pointer">
                                                <i data-lucide="maximize" class="w-3.5 h-3.5 text-pink-400"></i>
                                                <span>Mode Layar Penuh (Fullscreen)</span>
                                            </button>
                                        </div>

                                    </div>
                                @endif

                            </div>
                        @endforeach
                    </div>

                    @if($totalPamphlets > 1)
                        <!-- Prev / Next Floating Navigation Buttons -->
                        <button type="button" @click="prev()" class="absolute left-1 sm:left-4 top-1/2 -translate-y-1/2 w-9 h-9 sm:w-12 sm:h-12 rounded-2xl bg-black/80 hover:bg-pink-600 text-white border border-white/20 flex items-center justify-center transition shadow-2xl hover:scale-110 active:scale-95 cursor-pointer z-20 backdrop-blur-md" title="Pamflet Sebelumnya">
                            <i data-lucide="chevron-left" class="w-4 h-4 sm:w-6 sm:h-6"></i>
                        </button>
                        
                        <button type="button" @click="next()" class="absolute right-1 sm:right-4 top-1/2 -translate-y-1/2 w-9 h-9 sm:w-12 sm:h-12 rounded-2xl bg-black/80 hover:bg-pink-600 text-white border border-white/20 flex items-center justify-center transition shadow-2xl hover:scale-110 active:scale-95 cursor-pointer z-20 backdrop-blur-md" title="Pamflet Selanjutnya">
                            <i data-lucide="chevron-right" class="w-4 h-4 sm:w-6 sm:h-6"></i>
                        </button>

                        <!-- Bottom Dot Indicators -->
                        <div class="flex items-center gap-1.5 sm:gap-2 px-3 py-1.5 rounded-full bg-black/70 backdrop-blur-md border border-white/15 z-20 mt-4">
                            @foreach($pamphletList as $idx => $item)
                                <button type="button" 
                                    @click="active = {{ $idx }}" 
                                    :class="active === {{ $idx }} ? 'w-6 sm:w-8 bg-pink-500' : 'w-2 sm:w-2.5 bg-white/40 hover:bg-white/70'"
                                    class="h-2 sm:h-2.5 rounded-full transition-all duration-300 cursor-pointer"
                                    title="Pindah ke Pamflet {{ $idx + 1 }}">
                                </button>
                            @endforeach
                        </div>
                    @endif

                </div>

            </div>

            <!-- Fullscreen Lightbox Zoom Modal for Images & Canva -->
            <div x-show="isZoomed" 
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @keydown.escape.window="isZoomed = false"
                class="fixed inset-0 z-50 bg-black/95 backdrop-blur-xl flex items-center justify-center p-2 sm:p-6"
                style="display: none;">
                
                <button type="button" @click="isZoomed = false" class="absolute top-3 right-3 sm:top-6 sm:right-6 w-11 h-11 rounded-full bg-white/10 hover:bg-rose-600 text-white flex items-center justify-center border border-white/20 transition cursor-pointer z-50 shadow-2xl">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>

                <div class="w-full max-w-5xl max-h-[96vh] h-full flex items-center justify-center relative select-none p-2" @click.away="isZoomed = false">
                    <template x-if="zoomType === 'image'">
                        <img :src="zoomUrl" alt="Pamflet Layar Penuh" class="max-w-full max-h-[92vh] object-contain rounded-2xl shadow-2xl border border-white/15">
                    </template>
                    <template x-if="zoomType === 'canva'">
                        <div class="w-full h-full max-h-[92vh] aspect-[1/1.414] rounded-2xl overflow-hidden border-2 border-white/20 shadow-2xl bg-[#0C111D]">
                            <iframe :src="zoomUrl" class="w-full h-full border-0" allowfullscreen="allowfullscreen" allow="fullscreen"></iframe>
                        </div>
                    </template>
                </div>
            </div>

        </section>
    @endif

    <!-- TIMELINE & ROADMAP RANGKAIAN ACARA (Compact Horizontal Infographic Style - Swipeable on Mobile & Desktop) -->
    <section id="jadwal" class="py-6 lg:py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
        
        <div x-data="{
            isDown: false,
            startX: 0,
            scrollLeft: 0,
            scrollContainer(amount) {
                this.$refs.timelineScroll.scrollBy({ left: amount, behavior: 'smooth' });
            },
            startDrag(e) {
                if (e.button !== 0) return;
                this.isDown = true;
                this.startX = e.pageX - this.$refs.timelineScroll.offsetLeft;
                this.scrollLeft = this.$refs.timelineScroll.scrollLeft;
            },
            stopDrag() {
                this.isDown = false;
            },
            moveDrag(e) {
                if (!this.isDown) return;
                e.preventDefault();
                const x = e.pageX - this.$refs.timelineScroll.offsetLeft;
                const walk = (x - this.startX) * 1.6;
                this.$refs.timelineScroll.scrollLeft = this.scrollLeft - walk;
            },
            wheelScroll(e) {
                if (e.deltaY !== 0 && this.$refs.timelineScroll.scrollWidth > this.$refs.timelineScroll.clientWidth) {
                    e.preventDefault();
                    this.$refs.timelineScroll.scrollLeft += (e.deltaY * 1.5);
                }
            }
        }" class="glass-card p-4 sm:p-7 lg:p-9 rounded-2xl border border-white/[0.08] shadow-2xl relative overflow-hidden space-y-5">
            
            <!-- Section Header -->
            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 border-b border-white/[0.08] pb-4">
                <div class="space-y-1">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#7A5AF8]/15 border border-[#7A5AF8]/30 text-[#A594FD] text-[11px] font-bold tracking-wider uppercase">
                        <i data-lucide="calendar" class="w-3 h-3"></i>
                        <span>{{ $appSettings['timeline_tagline'] ?? ('Agenda & Jadwal Resmi ' . ($appSettings['app_name'] ?? 'TALENTA') . ' ' . ($appSettings['event_year'] ?? '2026')) }}</span>
                    </span>
                    <h2 class="text-lg sm:text-2xl lg:text-3xl font-black text-white tracking-tight font-display">
                        {{ $appSettings['timeline_title'] ?? 'Timeline Rangkaian Kegiatan' }}
                    </h2>
                </div>

                <div class="flex items-center justify-between sm:justify-end gap-2.5 pt-1 sm:pt-0">
                    <div class="flex items-center gap-1.5 text-xs text-slate-400">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-white/[0.04] border border-white/[0.08] text-[11px] font-semibold text-slate-300">
                            <i data-lucide="move-horizontal" class="w-3.5 h-3.5 text-[#7A5AF8]"></i>
                            <span class="hidden sm:inline">Klik & drag untuk menggeser timeline</span>
                            <span class="sm:hidden">Swipe / Geser untuk melihat alur</span>
                        </span>
                    </div>

                    <!-- Scroll Navigation Buttons -->
                    <div class="flex items-center gap-1 bg-[#0C111D] p-1 rounded-xl border border-white/[0.1] shrink-0">
                        <button type="button" @click="scrollContainer(-320)" class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-white/[0.05] hover:bg-white/[0.15] active:bg-[#7A5AF8]/30 text-slate-300 hover:text-white flex items-center justify-center transition cursor-pointer" title="Geser ke Kiri">
                            <i data-lucide="chevron-left" class="w-4 h-4"></i>
                        </button>
                        <button type="button" @click="scrollContainer(320)" class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-white/[0.05] hover:bg-white/[0.15] active:bg-[#7A5AF8]/30 text-slate-300 hover:text-white flex items-center justify-center transition cursor-pointer" title="Geser ke Kanan">
                            <i data-lucide="chevron-right" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>
            </div>

            @php
                $infographicThemes = [
                    1 => [
                        'ring' => 'border-amber-400',
                        'bg' => 'bg-amber-400 text-slate-950',
                        'glow' => 'shadow-amber-500/40',
                        'text' => 'text-amber-400',
                        'track' => 'bg-amber-400',
                        'icon' => 'clipboard-edit',
                    ],
                    2 => [
                        'ring' => 'border-emerald-400',
                        'bg' => 'bg-emerald-400 text-slate-950',
                        'glow' => 'shadow-emerald-500/40',
                        'text' => 'text-emerald-400',
                        'track' => 'bg-emerald-400',
                        'icon' => 'shield-check',
                    ],
                    3 => [
                        'ring' => 'border-teal-400',
                        'bg' => 'bg-teal-400 text-slate-950',
                        'glow' => 'shadow-teal-500/40',
                        'text' => 'text-teal-400',
                        'track' => 'bg-teal-400',
                        'icon' => 'disc',
                    ],
                    4 => [
                        'ring' => 'border-[#4E6EFF]',
                        'bg' => 'bg-[#4E6EFF] text-white',
                        'glow' => 'shadow-[#4E6EFF]/40',
                        'text' => 'text-[#84D0FF]',
                        'track' => 'bg-[#4E6EFF]',
                        'icon' => 'swords',
                    ],
                    5 => [
                        'ring' => 'border-[#7A5AF8]',
                        'bg' => 'bg-[#7A5AF8] text-white',
                        'glow' => 'shadow-[#7A5AF8]/40',
                        'text' => 'text-[#A594FD]',
                        'track' => 'bg-[#7A5AF8]',
                        'icon' => 'medal',
                    ],
                    6 => [
                        'ring' => 'border-purple-400',
                        'bg' => 'bg-purple-400 text-white',
                        'glow' => 'shadow-purple-500/40',
                        'text' => 'text-purple-400',
                        'track' => 'bg-purple-400',
                        'icon' => 'trophy',
                    ],
                    7 => [
                        'ring' => 'border-[#FF58D5]',
                        'bg' => 'bg-[#FF58D5] text-white',
                        'glow' => 'shadow-[#FF58D5]/40',
                        'text' => 'text-[#FF58D5]',
                        'track' => 'bg-[#FF58D5]',
                        'icon' => 'award',
                    ],
                    8 => [
                        'ring' => 'border-rose-400',
                        'bg' => 'bg-rose-400 text-white',
                        'glow' => 'shadow-rose-500/40',
                        'text' => 'text-rose-400',
                        'track' => 'bg-rose-400',
                        'icon' => 'sparkles',
                    ],
                ];
                $totalTimelines = count($timelines);
                $calculatedMinWidth = max(900, $totalTimelines * 210);
            @endphp

            <!-- HORIZONTAL INFOGRAPHIC RIBBON FLOW (Swipeable on Mobile / Drag on Desktop) -->
            <div 
                x-ref="timelineScroll"
                @mousedown="startDrag($event)"
                @mouseleave="stopDrag()"
                @mouseup="stopDrag()"
                @mousemove="moveDrag($event)"
                x-on:wheel.passive="false"
                x-on:wheel="wheelScroll($event)"
                :class="isDown ? 'cursor-grabbing select-none' : 'cursor-grab select-none'"
                class="overflow-x-auto no-scrollbar pb-6 pt-2"
                style="-webkit-overflow-scrolling: touch; touch-action: pan-x pan-y; overscroll-behavior-x: contain;"
            >
                <div class="relative px-4" style="min-width: {{ $calculatedMinWidth }}px;">
                    
                    <!-- Horizontal Track Line with Connecting Gradient Ribbon spanning across ALL steps -->
                    <div class="absolute top-[145px] left-12 right-12 h-2.5 bg-gradient-to-r from-amber-400 via-emerald-400 via-teal-400 via-[#4E6EFF] via-[#7A5AF8] to-[#FF58D5] rounded-full shadow-lg opacity-85 z-0"></div>

                    <!-- Steps Dynamic Grid Container -->
                    <div class="grid gap-3 relative z-10" style="grid-template-columns: repeat({{ max(1, $totalTimelines) }}, minmax(200px, 1fr));">
                        @forelse($timelines as $index => $item)
                            @php
                                $stepNum = $item->order ?? ($index + 1);
                                $isOdd = ($index % 2) === 0;
                                $themeKey = ($index % count($infographicThemes)) + 1;
                                $t = $infographicThemes[$themeKey];
                            @endphp

                            <div class="flex flex-col items-center text-center px-1 min-w-[200px]">
                                
                                <!-- Top Area (Height: 110px) -->
                                <div class="h-[110px] w-full flex flex-col justify-end items-center mb-3 space-y-1 px-1">
                                    @if($isOdd)
                                        <!-- Top Content for Odd Steps (Title & Description) -->
                                        <span class="px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider {{ $t['bg'] }} shadow-md">
                                            Tahap {{ $stepNum }}
                                        </span>
                                        <h4 class="text-xs font-black text-white leading-snug line-clamp-2">
                                            {{ $item->title }}
                                        </h4>
                                        <p class="text-[10px] text-slate-400 leading-tight line-clamp-2">
                                            {{ $item->description ?? '-' }}
                                        </p>
                                    @else
                                        <!-- Top Date for Even Steps -->
                                        <div class="flex flex-col items-center justify-end pb-1 space-y-0.5">
                                            <span class="text-xs font-black font-mono tracking-wider {{ $t['text'] }}">
                                                {{ $item->date_label }}
                                            </span>
                                            <span class="text-[9px] font-bold text-slate-500 uppercase tracking-wider">Pelaksanaan</span>
                                        </div>
                                    @endif
                                </div>

                                <!-- Central Circular Node along the Ribbon -->
                                <div class="relative flex items-center justify-center my-1 group cursor-pointer w-full">
                                    <!-- Outer Glowing Ring -->
                                    <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-full border-3 {{ $t['ring'] }} bg-[#0C111D] shadow-xl {{ $t['glow'] }} flex items-center justify-center group-hover:scale-110 transition duration-300 relative z-10">
                                        <!-- Inner Icon Circle -->
                                        <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-full {{ $t['bg'] }} flex items-center justify-center shadow-md font-black">
                                            <i data-lucide="{{ $t['icon'] }}" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                                        </div>
                                    </div>
                                    
                                    <!-- Direction Arrow indicator pointing right -->
                                    @if(!$loop->last)
                                        <div class="hidden sm:block absolute -right-3 sm:-right-4 top-1/2 -translate-y-1/2 text-slate-400 z-0">
                                            <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400"></i>
                                        </div>
                                    @endif
                                </div>

                                <!-- Bottom Area (Height: 110px) -->
                                <div class="h-[110px] w-full flex flex-col justify-start items-center mt-3 space-y-1 px-1">
                                    @if($isOdd)
                                        <!-- Bottom Date for Odd Steps -->
                                        <div class="flex flex-col items-center pt-1 space-y-0.5">
                                            <span class="text-xs font-black font-mono tracking-wider {{ $t['text'] }}">
                                                {{ $item->date_label }}
                                            </span>
                                            <span class="text-[9px] font-bold text-slate-500 uppercase tracking-wider">Pelaksanaan</span>
                                        </div>
                                    @else
                                        <!-- Bottom Content for Even Steps (Title & Description) -->
                                        <span class="px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider {{ $t['bg'] }} shadow-md">
                                            Tahap {{ $stepNum }}
                                        </span>
                                        <h4 class="text-xs font-black text-white leading-snug line-clamp-2">
                                            {{ $item->title }}
                                        </h4>
                                        <p class="text-[10px] text-slate-400 leading-tight line-clamp-2">
                                            {{ $item->description ?? '-' }}
                                        </p>
                                    @endif
                                </div>

                            </div>
                        @empty
                            <div class="col-span-full text-center py-8 text-slate-400">
                                Belum ada jadwal rangkaian acara yang diatur.
                            </div>
                        @endforelse
                    </div>

                </div>
            </div>

        </div>

    </section>

    <!-- SPONSOR & PARTNER BANNER (Supported by) -->
    @php
        $sponsorLogos = [];
        if (!empty($appSettings['sponsor_logos'])) {
            $decodedLogos = json_decode($appSettings['sponsor_logos'], true);
            if (is_array($decodedLogos)) {
                $sponsorLogos = $decodedLogos;
            }
        }
    @endphp
    @if(count($sponsorLogos) > 0)
    <section class="py-3 lg:py-4 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="glass-card rounded-2xl py-4 px-6 sm:py-4 sm:px-8 border border-white/[0.08] shadow-xl relative overflow-hidden text-center space-y-3">
            
            <!-- Ambient Subtle Background Glow -->
            <div class="absolute inset-0 bg-gradient-to-r from-[#7A5AF8]/10 via-[#4E6EFF]/10 to-[#FF58D5]/10 pointer-events-none"></div>

            <!-- Centered Header: Supported by -->
            <div class="relative z-10">
                <h3 class="text-xs sm:text-sm font-extrabold uppercase tracking-widest text-slate-300 font-display flex items-center justify-center gap-2">
                    <span>{{ $appSettings['sponsor_title'] ?? 'Supported by :' }}</span>
                </h3>
            </div>

            <!-- Sponsor Logos Grid / Flex Centered -->
            <div class="relative z-10 flex flex-wrap items-center justify-center gap-4 sm:gap-8 pt-2 pb-1">
                @foreach($sponsorLogos as $logo)
                    @php
                        $cleanLogo = ltrim(str_replace(['public/', 'storage/'], '', $logo), '/');
                        $logoUrl = \Illuminate\Support\Str::startsWith($logo, ['http://', 'https://']) ? $logo : asset('storage/' . $cleanLogo);
                    @endphp
                    <div class="p-3 sm:p-4 rounded-2xl bg-[#0C111D]/80 border border-white/[0.08] hover:border-[#7A5AF8]/50 hover:bg-[#0C111D] transition-all duration-300 group flex items-center justify-center shadow-lg">
                        <img src="{{ $logoUrl }}" 
                             alt="Logo Sponsor" 
                             class="h-12 sm:h-16 lg:h-20 w-auto max-w-[160px] sm:max-w-[200px] object-contain drop-shadow-md group-hover:scale-105 transition-transform duration-300"
                             onerror="this.closest('.group')?.remove();">
                    </div>
                @endforeach
            </div>

        </div>
    </section>
    @endif

</div>
@endsection
