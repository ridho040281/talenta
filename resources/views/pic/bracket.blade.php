@extends('layouts.admin')

@section('title', 'Bagan Pertandingan - ' . $competition->name)
@section('page_title', 'Bagan Pertandingan (Tournament Bracket)')

@section('content')
<div class="space-y-6 font-sans" x-data="bracketApp()">

    <!-- Tournament 5-Step Workflow Stepper -->
    @include('partials.tournament-stepper', [
        'competition' => $competition,
        'activeStep' => 'bagan',
        'activePoolKey' => $activePoolKey,
        'pools' => $pools
    ])

    <!-- Top Header Card -->
    <div class="bg-slate-900/90 rounded-2xl p-4 sm:p-5 border border-slate-800 shadow-lg flex flex-col lg:flex-row lg:items-center justify-between gap-4 text-white">
        <div class="space-y-1">
            <div class="flex items-center gap-2 flex-wrap">
                <span class="px-2 py-0.5 text-[10px] font-mono font-bold rounded bg-cyan-500/20 text-cyan-400 border border-cyan-500/30 uppercase">
                    {{ $competition->category?->name ?? 'Turnamen' }}
                </span>
                @if($activePool)
                    @php
                        $actIsPi = ($activePool['sector'] ?? '') === 'PI' || str_contains($activePool['key'], '_pi') || stripos($activePool['title'], 'putri') !== false;
                        $actIsGanda = str_contains($activePool['key'], 'ganda');
                        $actIsMix = ($activePool['sector'] ?? '') === 'MIX' || str_contains($activePool['key'], 'mix');
                        $actSec = $actIsMix ? '(MIX)' : ($actIsPi ? '(PI)' : '(PA)');
                        $actClass = $activePool['class_label'] ?? $activePool['category_label'] ?? $activePool['title'];
                    @endphp
                    <span class="px-2 py-0.5 text-[10px] font-mono font-black rounded uppercase flex items-center gap-1.5 shadow-sm {{ $actIsPi ? 'bg-rose-500/20 text-rose-300 border border-rose-500/40' : ($actIsGanda ? 'bg-amber-500/20 text-amber-300 border border-amber-500/40' : 'bg-blue-500/20 text-blue-300 border border-blue-500/40') }}">
                        <span>{{ $actIsGanda ? '👥' : ($actIsPi ? '👧' : '👦') }}</span>
                        <span>{{ $actClass }} {{ $actSec }}</span>
                    </span>
                @endif
                @if($bracketData)
                    @if(!empty($bracketData['playoffs']['has_playoffs']))
                        <span class="px-2 py-0.5 text-[10px] font-mono font-bold rounded bg-amber-500/20 text-amber-300 border border-amber-500/30 uppercase flex items-center gap-1">
                            <span>BAGAN PADAT {{ $bracketData['bracket_size'] }} + {{ $bracketData['playoffs']['num_playoffs'] }} PLAY-OFF</span>
                        </span>
                    @else
                        <span class="px-2 py-0.5 text-[10px] font-mono font-bold rounded bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 uppercase">
                            SISTEM GUGUR BWF (BAGAN {{ $bracketData['bracket_size'] }})
                        </span>
                    @endif
                @endif
            </div>
            <h2 class="text-xl sm:text-2xl font-black text-white tracking-tight flex items-center gap-2">
                <span>{{ $competition->name }}</span>
            </h2>
            <p class="text-xs text-slate-400 font-mono flex items-center gap-2 flex-wrap">
                @if($bracketData)
                    <span class="text-indigo-400 font-bold">{{ $bracketData['total_participants'] }} Peserta Terdaftar</span>
                    <span>•</span>
                    @if(!empty($bracketData['playoffs']['has_playoffs']))
                        <span class="text-amber-400 font-bold">{{ $bracketData['playoffs']['num_playoffs'] }} Partai Play-off</span>
                        <span>•</span>
                        <span class="text-emerald-400 font-bold">{{ $bracketData['bracket_size'] - $bracketData['playoffs']['num_playoffs'] }} Lolos Langsung</span>
                    @else
                        <span class="text-amber-400 font-bold">{{ $bracketData['total_byes'] }} Bebas Babak 1 (BYE)</span>
                    @endif
                    <span>•</span>
                    <span class="text-cyan-400 font-bold">{{ $bracketData['total_rounds'] }} Babak Pertandingan</span>
                @else
                    <span>Bagan belum tersedia</span>
                @endif
            </p>
        </div>

        <div class="flex items-center flex-wrap gap-2">
            <!-- Atur Jadwal & Wasit -->
            <button type="button" 
                    @click="openSyncModal()" 
                    :disabled="isSyncing || !hasRounds"
                    class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 text-slate-950 font-black text-xs shadow-md shadow-emerald-500/20 disabled:opacity-50 disabled:cursor-not-allowed transition cursor-pointer"
                    title="Atur lapangan, jam tanding, dan sinkronkan ke sistem wasit">
                <i data-lucide="calendar-clock" class="w-4 h-4"></i>
                <span>Atur Jadwal & Wasit</span>
            </button>

            <!-- Format Bagan & Play-off Selector -->
            <button type="button" 
                    @click="openFormatModal()" 
                    class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-indigo-500/15 hover:bg-indigo-500/25 text-indigo-200 border border-indigo-500/30 font-bold text-xs shadow-sm transition cursor-pointer"
                    title="Pilih format bagan: Bagan Otomatis BWF vs Bagan Padat + Play-off Kualifikasi">
                <i data-lucide="sliders" class="w-4 h-4 text-indigo-400"></i>
                <span>Format Bagan</span>
                @if(!empty($bracketData['playoffs']['has_playoffs']))
                    <span class="px-1.5 py-0.5 rounded bg-amber-500 text-slate-950 text-[9px] font-black tracking-wider">
                        PLAY-OFF
                    </span>
                @endif
            </button>

            <!-- Toggle / Settings Publikasi TV Bagan -->
            <button type="button" 
                    @click="openPublicationModal()" 
                    class="inline-flex items-center gap-2 px-3 py-2 rounded-xl font-bold text-xs shadow-sm transition cursor-pointer border"
                    :class="pubSettings.is_published 
                        ? 'bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-300 border-emerald-500/30' 
                        : 'bg-rose-500/10 hover:bg-rose-500/20 text-rose-300 border-rose-500/30'"
                    title="Atur status publikasi dan redaksi standby layar TV Bagan">
                <span class="w-2 h-2 rounded-full" :class="pubSettings.is_published ? 'bg-emerald-400 animate-pulse' : 'bg-rose-400'"></span>
                <span x-text="pubSettings.is_published ? 'Bagan TV: ON' : 'Bagan TV: STANDBY'"></span>
                <i data-lucide="settings-2" class="w-3.5 h-3.5 opacity-70"></i>
            </button>

            <!-- Print PDF Button -->
            <a href="{{ route('pic.bracket.print', $competition->id) }}?pool={{ urlencode($activePoolKey) }}" 
               target="_blank" 
               class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-white/[0.06] hover:bg-white/[0.12] text-slate-200 border border-white/[0.10] font-bold text-xs shadow-sm transition"
               title="Cetak format bagan resmi A4 Landscape">
                <i data-lucide="printer" class="w-4 h-4 text-slate-300"></i>
                <span>Cetak Bagan (A4)</span>
            </a>

            <!-- Export Jadwal Excel Button -->
            <a href="{{ route('pic.bracket.export_excel', $competition->id) }}" 
               target="_blank" 
               class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-emerald-500/15 hover:bg-emerald-500/25 text-emerald-300 border border-emerald-500/30 font-bold text-xs shadow-sm transition cursor-pointer"
               title="Export Rekap Jadwal & Order of Play ke Microsoft Excel (.xlsx)">
                <i data-lucide="file-spreadsheet" class="w-4 h-4 text-emerald-400"></i>
                <span>Export Jadwal (Excel)</span>
            </a>
        </div>
    </div>

    <!-- Pool / Category Filter Tabs (Pemisahan Visual Sektor PA & PI) -->
    @if(count($pools) > 1)
    <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-thin">
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
            <a href="{{ route('pic.bracket', $competition->id) }}?pool={{ urlencode($p['key']) }}"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-2xl text-xs font-bold transition whitespace-nowrap {{ $isActive ? ($isPi ? 'bg-gradient-to-r from-rose-600 to-pink-600 text-white shadow-lg shadow-rose-500/25 border border-rose-400/40 ring-1 ring-rose-400/30 font-black' : 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-lg shadow-blue-500/25 border border-blue-400/40 ring-1 ring-blue-400/30 font-black') : 'bg-slate-900/80 text-slate-300 hover:text-white hover:bg-slate-800 border border-slate-800' }}">
                <span>{{ $icon }}</span>
                <span>{{ $tabName }}</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] {{ $isActive ? 'bg-white/25 text-white font-mono font-bold' : 'bg-slate-800 text-slate-400 font-mono' }}">
                    {{ count($p['participants']) }}
                </span>
            </a>
        @endforeach
    </div>
    @endif

    <!-- Alert / Flash Message Toast -->
    <div x-show="toastMessage" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         class="p-4 rounded-2xl border text-xs font-bold flex items-center justify-between shadow-xl"
         :class="toastSuccess ? 'bg-emerald-500/10 border-emerald-500/30 text-emerald-300' : 'bg-rose-500/10 border-rose-500/30 text-rose-300'"
         style="display: none;">
        <div class="flex items-center gap-2.5">
            <i :data-lucide="toastSuccess ? 'check-circle' : 'alert-circle'" class="w-4 h-4"></i>
            <span x-text="toastMessage"></span>
        </div>
        <button type="button" @click="toastMessage = ''" class="text-slate-400 hover:text-white">
            <i data-lucide="x" class="w-3.5 h-3.5"></i>
        </button>
    </div>

    <!-- Bracket Content Area -->
    @if(!$bracketData || empty($bracketData['rounds']))
        <div class="py-20 text-center bg-slate-900/80 rounded-3xl border border-slate-800 p-8">
            <div class="w-16 h-16 rounded-2xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center mx-auto mb-4 border border-indigo-500/20">
                <i data-lucide="git-branch" class="w-8 h-8"></i>
            </div>
            <h3 class="text-lg font-black text-white mb-2">Belum Cukup Peserta Terdaftar</h3>
            <p class="text-xs text-slate-400 max-w-md mx-auto mb-6">
                Bagan turnamen membutuhkan minimal 2 peserta yang telah terdaftar dan diverifikasi dalam kategori ini. Silakan tambahkan peserta atau verifikasi pendaftar terlebih dahulu.
            </p>
            <a href="{{ route('pic.participants', $competition->id) }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-lg transition">
                <i data-lucide="users" class="w-4 h-4"></i>
                <span>Kelola Data Peserta</span>
            </a>
        </div>
    @else

        <!-- Bracket Explanatory Banner -->
        <div class="bg-gradient-to-r from-indigo-950/60 via-slate-900/80 to-blue-950/60 rounded-2xl p-4 border border-indigo-500/20 flex flex-col md:flex-row items-start md:items-center justify-between gap-3 text-xs">
            <div class="flex items-center gap-3 text-slate-300">
                <div class="w-8 h-8 rounded-xl bg-indigo-500/20 border border-indigo-500/30 flex items-center justify-center text-indigo-400 flex-shrink-0">
                    <i data-lucide="info" class="w-4 h-4"></i>
                </div>
                <div>
                    <span class="font-bold text-white">Prinsip Bagan Standar BWF:</span>
                    Unggulan (Seed 1 & 2) dipisah di kutub atas & bawah bagan. Slot <span class="text-cyan-300 font-mono">[BYE]</span> diberikan secara prioritas kepada unggulan utama agar lolos langsung ke babak berikutnya tanpa tanding.
                </div>
            </div>
            <div class="flex items-center gap-2 self-end md:self-auto flex-shrink-0">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-amber-500/10 text-amber-300 border border-amber-500/20 font-mono text-[11px]">
                    ⭐ Seeded Player
                </span>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-cyan-500/10 text-cyan-300 border border-cyan-500/20 font-mono text-[11px]">
                    [BYE] Bebas Babak 1
                </span>
            </div>
        </div>

        <!-- BWF Separation of Entries Notification Banner -->
        @if(!empty($bracketData['has_bwf_protections']))
        <div class="bg-indigo-950/60 border border-indigo-500/30 rounded-2xl p-4 flex flex-col md:flex-row items-start gap-3.5 text-xs text-indigo-200 shadow-xl">
            <div class="w-9 h-9 rounded-xl bg-indigo-500/20 border border-indigo-500/40 flex items-center justify-center text-indigo-300 flex-shrink-0 text-base shadow-sm">
                🛡️
            </div>
            <div class="flex-1 space-y-1.5">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="font-black text-indigo-300 uppercase tracking-wider text-[10px] bg-indigo-500/20 px-2.5 py-0.5 rounded-md border border-indigo-500/40 shadow-sm">
                        Proteksi Resmi BWF GCR 14 Aktif
                    </span>
                    <span class="text-white font-bold">Proteksi Satu Delegasi Sekolah</span>
                </div>
                <p class="text-slate-300 text-[11px] leading-relaxed">
                    Sistem mendeteksi peserta dari satu delegasi sekolah yang sama dan secara otomatis memisahkan mereka ke pool berlawanan (Pool Atas & Pool Bawah) untuk menjamin <strong>tidak terjadi bentrok sesama rekan delegasi di Babak 1</strong>.
                </p>
                <div class="flex flex-wrap gap-2 pt-1">
                    @foreach($bracketData['bwf_protections'] as $prot)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-900/90 border border-indigo-500/30 text-[11px] text-slate-200 shadow-sm">
                            <span class="text-indigo-400 font-bold">🛡️ {{ $prot['institution'] }}:</span>
                            <span>{{ $prot['participant_name'] }}</span>
                            <span class="text-amber-400 font-mono font-bold">&rarr; Slot #{{ $prot['slot'] }}</span>
                        </span>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- View Mode Switcher -->
        <div class="flex items-center justify-between flex-wrap gap-3 bg-slate-900/90 p-3.5 rounded-2xl border border-slate-800 shadow-md">
            <div class="flex items-center gap-2 flex-wrap">
                <span class="text-xs font-bold text-slate-400">Tampilan Bagan:</span>
                <div class="inline-flex p-1 bg-slate-950 rounded-xl border border-slate-800">
                    <button type="button" @click="viewMode = 'classic'" :class="viewMode === 'classic' ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-400 hover:text-white'" class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5 cursor-pointer">
                        <i data-lucide="git-branch" class="w-3.5 h-3.5"></i>
                        <span>Model Garis Klasik (BWF GOR)</span>
                    </button>
                    <button type="button" @click="viewMode = 'cards'" :class="viewMode === 'cards' ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-400 hover:text-white'" class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5 cursor-pointer">
                        <i data-lucide="layout-grid" class="w-3.5 h-3.5"></i>
                        <span>Model Kartu Pertandingan</span>
                    </button>
                </div>
            </div>

            <!-- In Classic Mode: Canvas Theme Toggle -->
            <div x-show="viewMode === 'classic'" class="flex items-center gap-2">
                <span class="text-[11px] text-slate-400">Papan:</span>
                <div class="inline-flex p-1 bg-slate-950 rounded-xl border border-slate-800 text-xs font-bold">
                    <button type="button" @click="classicTheme = 'white'" :class="classicTheme === 'white' ? 'bg-white text-slate-950 shadow' : 'text-slate-400 hover:text-white'" class="px-3 py-1 rounded-lg transition cursor-pointer">
                        Kertas Putih
                    </button>
                    <button type="button" @click="classicTheme = 'dark'" :class="classicTheme === 'dark' ? 'bg-slate-800 text-cyan-300 shadow' : 'text-slate-400 hover:text-white'" class="px-3 py-1 rounded-lg transition cursor-pointer">
                        Dark Mode
                    </button>
                </div>
            </div>
        </div>

        <!-- Classic Line Tree (Persis Bagan GOR Standar BWF) -->
        <div x-show="viewMode === 'classic'" class="overflow-x-auto pb-8 pt-2 scrollbar-thin">
            <div class="p-6 rounded-3xl border shadow-2xl overflow-auto min-w-[1050px] lg:min-w-[1300px] flex justify-center transition-colors duration-300"
                 :class="classicTheme === 'white' ? 'bg-white border-slate-200 shadow-slate-950/30' : 'bg-slate-950 border-slate-800 shadow-indigo-950/30'">
                <div x-show="classicTheme === 'white'" class="w-full flex justify-center">
                    {!! $bracketData['classic_svg_light'] !!}
                </div>
                <div x-show="classicTheme === 'dark'" class="w-full flex justify-center">
                    {!! $bracketData['classic_svg_dark'] !!}
                </div>
            </div>
        </div>

        <!-- Scrollable Bracket Visual Cards Container -->
        <div x-show="viewMode === 'cards'" class="overflow-x-auto pb-8 pt-2 scrollbar-thin">
            <div class="inline-flex gap-8 min-w-full items-stretch px-2 py-4">

                <!-- Play-off Column (if active) -->
                @if(!empty($bracketData['playoffs']['has_playoffs']) && !empty($bracketData['playoffs']['matches']))
                    <div class="flex flex-col min-w-[280px] sm:min-w-[320px] max-w-[340px]">
                        <div class="mb-5 text-center">
                            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-amber-500/15 border border-amber-500/30 shadow-md">
                                <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                                <span class="text-xs font-black text-amber-300 tracking-wide uppercase">Play-off Kualifikasi</span>
                                <span class="text-[10px] font-mono text-amber-400">({{ count($bracketData['playoffs']['matches']) }} Partai)</span>
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
                                <div class="bg-slate-900/90 rounded-2xl border transition-all duration-200 shadow-lg relative overflow-hidden group
                                    {{ $isPoOngoing ? 'border-amber-500/60 ring-1 ring-amber-500/40' : ($isPoFinished ? 'border-emerald-500/40 shadow-emerald-500/5' : 'border-amber-500/30 hover:border-amber-500/50') }}">
                                    <!-- Header Bar -->
                                    <div class="px-3.5 py-2 bg-slate-950/80 border-b border-slate-800/80 flex items-center justify-between text-[11px]">
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
                                                    Jadwal
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Schedule Info Bar -->
                                    @if($poExisting && $poExisting->court_number)
                                        <div class="px-3 py-1.5 bg-slate-950/80 border-b border-slate-800/80 flex items-center justify-between text-[11px] font-mono">
                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-sky-500/20 text-sky-300 font-bold text-[10px] border border-sky-500/30">
                                                    📅 {{ $poExisting->match_day_label ?: ('Hari ' . ($poExisting->match_day ?: 1)) }}
                                                </span>
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-indigo-500/20 text-indigo-300 font-bold text-[10px] border border-indigo-500/30">
                                                    🏸 {{ $poExisting->court_number }}
                                                </span>
                                                @if($poExisting->match_order)
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-amber-500/20 text-amber-300 font-bold text-[10px] border border-amber-500/30">
                                                        Partai #{{ $poExisting->match_order }}
                                                    </span>
                                                @endif
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-300 font-bold text-[10px] border border-emerald-500/30">
                                                    ⏰ {{ $poExisting->scheduled_time ?? '07:30' }}
                                                </span>
                                            </div>
                                            <button type="button" 
                                                    @click="openEditScheduleModal('{{ $poMatch['match_code'] }}', '{{ $poExisting->court_number }}', '{{ $poExisting->scheduled_time ?? '07:30' }}', '{{ $poExisting->match_order ?? 0 }}', '{{ $poExisting->match_day ?? 1 }}', '{{ $poExisting->match_date?->format('Y-m-d') ?? '' }}')"
                                                    class="p-1 rounded hover:bg-slate-800 text-slate-400 hover:text-amber-300 transition cursor-pointer"
                                                    title="Ubah Jadwal Play-off">
                                                <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                            </button>
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
                                            <div class="flex items-center gap-1">
                                                <a href="{{ route('badminton.umpire', $poExisting->id) }}" target="_blank" class="px-1.5 py-0.5 rounded bg-indigo-500/20 text-indigo-300 font-bold">Wasit</a>
                                            </div>
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
                            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-slate-900 border border-slate-800 shadow-md">
                                <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                                <span class="text-xs font-black text-white tracking-wide uppercase">{{ $round['round_name'] }}</span>
                                <span class="text-[10px] font-mono text-slate-400">({{ count($round['matches']) }} Partai)</span>
                            </div>
                        </div>

                        <!-- Matches Container with Flex Justify-Around for Tree Alignment -->
                        <div class="flex-1 flex flex-col justify-around gap-6 py-2">
                            @foreach($round['matches'] as $match)
                                @php
                                    $t1 = $match['team1'];
                                    $t2 = $match['team2'];
                                    $hasBye = ($t1['is_bye'] ?? false) || ($t2['is_bye'] ?? false);
                                    $isFinished = ($match['status'] === 'finished');
                                    $isOngoing = ($match['status'] === 'ongoing');
                                    $isByeAdvance = ($match['status'] === 'bye_advance');
                                    $existing = $match['existing_match'];
                                @endphp

                                <div class="bg-slate-900/90 rounded-2xl border transition-all duration-200 shadow-lg relative overflow-hidden group
                                    {{ $isOngoing ? 'border-amber-500/60 shadow-amber-500/10 ring-1 ring-amber-500/40' : ($isFinished ? 'border-emerald-500/40 shadow-emerald-500/5' : ($isByeAdvance ? 'border-cyan-500/30 bg-cyan-950/10' : 'border-slate-800 hover:border-slate-700')) }}">

                                    <!-- Match Header Bar -->
                                    <div class="px-3.5 py-2 bg-slate-950/60 border-b border-slate-800/80 flex items-center justify-between text-[11px]">
                                        <span class="font-mono font-bold text-slate-400">
                                            {{ $match['match_code'] }}
                                        </span>
                                        <div class="flex items-center gap-1.5">
                                            @if($isByeAdvance)
                                                <span class="px-2 py-0.5 rounded-md bg-cyan-500/15 text-cyan-300 font-bold text-[10px] border border-cyan-500/30">
                                                    BYE Advance
                                                </span>
                                            @elseif(($match['status'] ?? '') === 'pending_draw')
                                                <span class="px-2 py-0.5 rounded-md bg-slate-800 text-slate-400 font-bold text-[10px] border border-slate-700/60 flex items-center gap-1">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-500"></span>
                                                    Menunggu Undian
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

                                            @if(!empty($match['has_bwf_protection']))
                                                <span class="px-1.5 py-0.5 rounded bg-indigo-500/20 text-indigo-300 font-bold text-[9px] border border-indigo-500/40 flex items-center gap-1 shadow-sm" title="{{ $match['bwf_note'] ?? 'Proteksi BWF GCR 14' }}">
                                                    🛡️ BWF
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Court & Time Schedule Badge Bar -->
                                    @if($existing && $existing->court_number && strtoupper($existing->court_number) !== 'BYE')
                                        <div class="px-3 py-1.5 bg-slate-950/80 border-b border-slate-800/80 flex items-center justify-between text-[11px] font-mono">
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
                                            <button type="button" 
                                                    @click="openEditScheduleModal('{{ $match['match_code'] }}', '{{ $existing->court_number }}', '{{ $existing->scheduled_time ?? '' }}', '{{ $existing->match_order ?? '' }}', '{{ $existing->match_day ?? 1 }}', '{{ $existing->match_date?->format('Y-m-d') ?? '' }}')"
                                                    class="p-1 rounded hover:bg-slate-800 text-slate-400 hover:text-amber-300 transition cursor-pointer"
                                                    title="Ubah Hari, Lapangan & Jam Tanding Partai Ini">
                                                <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                            </button>
                                        </div>
                                    @elseif(!$isByeAdvance && ($match['status'] ?? '') !== 'pending_draw')
                                        <div class="px-3 py-1.5 bg-slate-950/40 border-b border-slate-800/60 flex items-center justify-between text-[10px] text-slate-400 font-mono">
                                            <span class="text-slate-500">Jadwal belum diset</span>
                                            <button type="button" 
                                                    @click="openEditScheduleModal('{{ $match['match_code'] }}', 'Lapangan 1', '', '', 1, '{{ $competition->schedule_date ? \Carbon\Carbon::parse($competition->schedule_date)->format('Y-m-d') : '' }}')"
                                                    class="px-2 py-0.5 rounded bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white font-bold transition flex items-center gap-1 cursor-pointer">
                                                <i data-lucide="clock" class="w-3 h-3 text-amber-400"></i>
                                                <span>Set Jadwal</span>
                                            </button>
                                        </div>
                                    @endif

                                    <!-- Match Competitors List -->
                                    <div class="p-3 space-y-2">
                                        <!-- Team 1 Slot -->
                                        @php
                                            $isT1Pending = !empty($t1['is_pending_draw']);
                                            $isT1Winner = false;
                                            if ($match['winner'] && ($t1['id'] ?? null) && ($match['winner']['id'] ?? null) === $t1['id']) {
                                                $isT1Winner = true;
                                            }
                                            if ($isByeAdvance && !($t1['is_bye'] ?? false) && !$isT1Pending) {
                                                $isT1Winner = true;
                                            }
                                        @endphp
                                        <div class="flex items-center justify-between gap-2 p-2 rounded-xl transition
                                            {{ $isT1Winner ? 'bg-emerald-500/15 border border-emerald-500/30' : (($t1['is_bye'] ?? false) ? 'bg-cyan-950/20 border border-cyan-500/20 opacity-70' : ($isT1Pending ? 'bg-slate-950/30 border border-dashed border-slate-800' : 'bg-slate-950/40 border border-transparent')) }}">
                                            <div class="flex items-center gap-2.5 min-w-0">
                                                <!-- Slot & Seed Badge -->
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

                                                <!-- Player Name & School -->
                                                <div class="min-w-0">
                                                    <div class="text-xs font-bold truncate {{ $isT1Winner ? 'text-emerald-300 font-black' : (($t1['is_bye'] ?? false) ? 'text-cyan-400 italic' : ($isT1Pending ? 'text-slate-500 italic' : 'text-slate-200')) }}">
                                                        {{ $t1['name'] ?? 'Menunggu Pemenang' }}
                                                    </div>
                                                    @if(!empty($t1['institution']) && !$isT1Pending && !($t1['is_bye'] ?? false))
                                                        <div class="text-[10px] text-slate-400 truncate">
                                                            {{ $t1['institution'] }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>

                                            <!-- Scores / Status Icon -->
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

                                        <!-- Divider line with VS -->
                                        <div class="relative flex items-center justify-center py-0.5">
                                            <div class="w-full border-t border-slate-800/80"></div>
                                            <span class="absolute px-2 bg-slate-900 text-[9px] font-mono font-bold text-slate-500">VS</span>
                                        </div>

                                        <!-- Team 2 Slot -->
                                        @php
                                            $isT2Pending = !empty($t2['is_pending_draw']);
                                            $isT2Winner = false;
                                            if ($match['winner'] && ($t2['id'] ?? null) && ($match['winner']['id'] ?? null) === $t2['id']) {
                                                $isT2Winner = true;
                                            }
                                            if ($isByeAdvance && !($t2['is_bye'] ?? false) && !$isT2Pending) {
                                                $isT2Winner = true;
                                            }
                                        @endphp
                                        <div class="flex items-center justify-between gap-2 p-2 rounded-xl transition
                                            {{ $isT2Winner ? 'bg-emerald-500/15 border border-emerald-500/30' : (($t2['is_bye'] ?? false) ? 'bg-cyan-950/20 border border-cyan-500/20 opacity-70' : ($isT2Pending ? 'bg-slate-950/30 border border-dashed border-slate-800' : 'bg-slate-950/40 border border-transparent')) }}">
                                            <div class="flex items-center gap-2.5 min-w-0">
                                                <!-- Slot & Seed Badge -->
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

                                                <!-- Player Name & School -->
                                                <div class="min-w-0">
                                                    <div class="text-xs font-bold truncate {{ $isT2Winner ? 'text-emerald-300 font-black' : (($t2['is_bye'] ?? false) ? 'text-cyan-400 italic' : ($isT2Pending ? 'text-slate-500 italic' : 'text-slate-200')) }}">
                                                        {{ $t2['name'] ?? 'Menunggu Pemenang' }}
                                                    </div>
                                                    @if(!empty($t2['institution']) && !$isT2Pending && !($t2['is_bye'] ?? false))
                                                        <div class="text-[10px] text-slate-400 truncate">
                                                            {{ $t2['institution'] }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>

                                            <!-- Scores / Status Icon -->
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

                                    <!-- Card Action Footer (Umpire & TV buttons if synced) -->
                                    @if($existing)
                                        <div class="px-3 py-2 bg-slate-950/80 border-t border-slate-800/80 flex items-center justify-between text-[10px]">
                                            <span class="text-slate-400 font-mono font-bold">
                                                {{ $existing->court_number !== 'BYE' ? $existing->court_number : 'Lolos Langsung' }}
                                                @if($existing->scheduled_time)
                                                    • {{ $existing->scheduled_time }}
                                                @endif
                                            </span>
                                            <div class="flex items-center gap-1.5">
                                                @if($existing->court_number !== 'BYE')
                                                    <button type="button" 
                                                            @click="openEditScheduleModal('{{ $match['match_code'] }}', '{{ $existing->court_number }}', '{{ $existing->scheduled_time ?? '' }}', '{{ $existing->match_order ?? '' }}')"
                                                            class="px-2 py-1 rounded bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white font-bold transition flex items-center gap-1 cursor-pointer"
                                                            title="Atur Jadwal / Lapangan">
                                                        <i data-lucide="calendar" class="w-3 h-3 text-amber-400"></i>
                                                        <span>Jadwal</span>
                                                    </button>
                                                @endif
                                                <a href="{{ route('badminton.umpire', $existing->id) }}" target="_blank" class="px-2 py-1 rounded bg-indigo-500/20 hover:bg-indigo-500/30 text-indigo-300 font-bold transition flex items-center gap-1">
                                                    <i data-lucide="activity" class="w-3 h-3"></i>
                                                    <span>Wasit</span>
                                                </a>
                                                <a href="{{ route('badminton.scoreboard', $existing->id) }}" target="_blank" class="px-2 py-1 rounded bg-amber-500/20 hover:bg-amber-500/30 text-amber-300 font-bold transition flex items-center gap-1">
                                                    <i data-lucide="tv" class="w-3 h-3"></i>
                                                    <span>Skor</span>
                                                </a>
                                            </div>
                                        </div>
                                    @endif

                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                <!-- Champion Column (Podium Trophy) -->
                <div class="flex flex-col min-w-[260px] max-w-[280px]">
                    <div class="mb-5 text-center">
                        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-gradient-to-r from-amber-500/20 to-yellow-500/20 border border-amber-500/30 shadow-md">
                            <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                            <span class="text-xs font-black text-amber-300 tracking-wide uppercase">Juara 1 Turnamen</span>
                        </div>
                    </div>

                    <div class="flex-1 flex flex-col justify-center py-2">
                        <div class="bg-gradient-to-b from-amber-500/10 via-slate-900 to-slate-900 rounded-3xl border border-amber-500/40 p-6 text-center shadow-xl shadow-amber-500/5 relative overflow-hidden">
                            <div class="absolute -right-8 -top-8 w-24 h-24 bg-amber-500/10 rounded-full blur-2xl"></div>
                            
                            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-amber-400 to-yellow-600 text-slate-950 flex items-center justify-center mx-auto mb-4 shadow-lg shadow-amber-500/30">
                                <i data-lucide="trophy" class="w-8 h-8"></i>
                            </div>

                            @if(!empty($bracketData['champion']))
                                <span class="px-2.5 py-0.5 rounded-md bg-amber-500/20 text-amber-300 font-black text-[10px] uppercase tracking-wider border border-amber-500/30">
                                    CHAMPION
                                </span>
                                <h4 class="text-base font-black text-white mt-2">
                                    {{ $bracketData['champion']['name'] }}
                                </h4>
                                <p class="text-xs text-slate-400 mt-0.5">
                                    {{ $bracketData['champion']['institution'] ?? '' }}
                                </p>
                            @else
                                <span class="px-2.5 py-0.5 rounded-md bg-slate-800 text-slate-400 font-bold text-[10px] uppercase tracking-wider">
                                    Menunggu Final
                                </span>
                                <h4 class="text-sm font-bold text-slate-400 mt-2">
                                    Pemenang Babak Final
                                </h4>
                                <p class="text-[11px] text-slate-500 mt-0.5">
                                    Akan muncul otomatis setelah pertandingan final selesai.
                                </p>
                            @endif
                        </div>
                    </div>
                </div>

            </div>
        </div>

    @endif

    <!-- Modal: Pengaturan Format Bagan & Play-off Kualifikasi -->
    <div x-show="showFormatModal" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.away="closeFormatModal()" 
             class="bg-slate-900 border border-slate-800 w-full max-w-xl rounded-3xl p-6 shadow-2xl space-y-5 text-white animate-in fade-in zoom-in-95 duration-200">
            
            <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-indigo-500 to-amber-500 text-slate-950 flex items-center justify-center font-bold shadow-lg shadow-indigo-500/20">
                        <i data-lucide="sliders" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-base text-white">Pengaturan Format Bagan</h3>
                        <p class="text-xs text-slate-400">Pilih format bagan pertandingan untuk kategori {{ $activePool['title'] ?? '' }}</p>
                    </div>
                </div>
                <button type="button" @click="closeFormatModal()" class="text-slate-400 hover:text-white p-1 rounded-lg hover:bg-slate-800 transition cursor-pointer">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <div class="space-y-4 text-xs">
                <div class="p-3.5 rounded-2xl bg-slate-950 border border-slate-800 text-slate-300 flex items-center justify-between">
                    <span>Jumlah Peserta Terdaftar:</span>
                    <span class="font-mono font-black text-amber-400 text-sm">{{ $bracketData ? $bracketData['total_participants'] : 0 }} Peserta</span>
                </div>

                <!-- Opsi Format -->
                <div class="space-y-3">
                    <!-- Opsi 1: Play-off Kualifikasi -->
                    <label class="block p-4 rounded-2xl border cursor-pointer transition relative"
                           :class="bracketMode === 'playoff' ? 'bg-indigo-950/40 border-indigo-500 ring-2 ring-indigo-500/40' : 'bg-slate-950/60 border-slate-800 hover:border-slate-700'">
                        <div class="flex items-start gap-3">
                            <input type="radio" name="format_mode" value="playoff" x-model="bracketMode" class="mt-1 text-indigo-600 focus:ring-indigo-500">
                            <div class="flex-1 space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="font-black text-white text-sm">Bagan Padat + Play-off Kualifikasi</span>
                                    <span class="px-2 py-0.5 rounded-md bg-amber-500/20 text-amber-300 border border-amber-500/30 text-[10px] font-black uppercase">
                                        Rekomendasi Efisiensi
                                    </span>
                                </div>
                                <p class="text-slate-400 text-[11px] leading-relaxed">
                                    Menggunakan bagan kelipatan di bawahnya (misal: 33 peserta &rarr; Bagan 32).
                                    31 peserta langsung masuk Bagan Utama (Slot 1..31). 2 peserta bertanding di 1 partai Play-off (jam 07:30) memperebutkan Slot #32.
                                </p>
                                <div class="pt-2 flex items-center gap-3">
                                    <span class="text-slate-400 font-bold">Target Ukuran Bagan Utama:</span>
                                    <select x-model="targetBracketSize" 
                                            class="bg-slate-900 border border-slate-700 rounded-xl px-2.5 py-1 text-white font-mono font-bold text-xs focus:ring-1 focus:ring-indigo-500">
                                        <option value="8">Bagan 8</option>
                                        <option value="16">Bagan 16</option>
                                        <option value="32">Bagan 32 (Standar 33 Peserta)</option>
                                        <option value="64">Bagan 64</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </label>

                    <!-- Opsi 2: Standar BWF Baku Otomatis -->
                    <label class="block p-4 rounded-2xl border cursor-pointer transition relative"
                           :class="bracketMode === 'auto' ? 'bg-indigo-950/40 border-indigo-500 ring-2 ring-indigo-500/40' : 'bg-slate-950/60 border-slate-800 hover:border-slate-700'">
                        <div class="flex items-start gap-3">
                            <input type="radio" name="format_mode" value="auto" x-model="bracketMode" class="mt-1 text-indigo-600 focus:ring-indigo-500">
                            <div class="flex-1 space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="font-black text-white text-sm">Standar BWF Baku (Bagan Otomatis + BYE)</span>
                                </div>
                                <p class="text-slate-400 text-[11px] leading-relaxed">
                                    Menggunakan kelipatan 2 di atasnya (misal: 33 peserta &rarr; Bagan 64).
                                    Menghasilkan 31 slot [BYE] (Bebas Babak 1). Tidak ada babak play-off kualifikasi.
                                </p>
                            </div>
                        </div>
                    </label>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-800">
                <button type="button" 
                        @click="closeFormatModal()"
                        class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-xs transition cursor-pointer">
                    Batal
                </button>
                <button type="button" 
                        @click="saveBracketFormat()"
                        :disabled="isSavingFormat"
                        class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-black text-xs shadow-lg shadow-indigo-600/20 disabled:opacity-50 disabled:cursor-not-allowed transition flex items-center gap-1.5 cursor-pointer">
                    <i data-lucide="check" class="w-3.5 h-3.5"></i>
                    <span x-text="isSavingFormat ? 'Menyimpan...' : 'Terapkan Format Bagan'"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- Modal: Sinkronisasi Jadwal & Pembagian Lapangan Otomatis (Unified Multi-Day & Simulation Preview) -->
    <div x-show="showSyncScheduleModal" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-black/85 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4">
        <div @click.away="closeSyncModal()" 
             :class="showPreviewTable ? 'max-w-6xl' : 'max-w-4xl'"
             class="bg-slate-900 border border-slate-800 w-full rounded-3xl p-5 sm:p-6 shadow-2xl space-y-4 text-white animate-in fade-in zoom-in-95 duration-200 transition-all max-h-[92vh] flex flex-col">
            
            <!-- Modal Header -->
            <div class="flex items-center justify-between border-b border-slate-800 pb-3.5 shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-emerald-500 to-teal-400 text-slate-950 flex items-center justify-center font-bold shadow-lg shadow-emerald-500/20">
                        <i data-lucide="calendar-clock" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-base text-white">Atur Lapangan & Waktu Tanding</h3>
                        <p class="text-xs text-slate-400">Sinkronisasi otomatis multi-hari ke Modul Wasit & Bagan</p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <template x-if="previewData">
                        <div class="flex items-center bg-slate-950 p-1 rounded-xl border border-slate-800">
                            <button type="button" 
                                    @click="showPreviewTable = false"
                                    :class="!showPreviewTable ? 'bg-slate-800 text-white font-bold' : 'text-slate-400 hover:text-white'"
                                    class="px-2.5 py-1 rounded-lg text-xs transition cursor-pointer">
                                ⚙️ Pengaturan
                            </button>
                            <button type="button" 
                                    @click="showPreviewTable = true"
                                    :class="showPreviewTable ? 'bg-emerald-600 text-white font-bold' : 'text-slate-400 hover:text-white'"
                                    class="px-2.5 py-1 rounded-lg text-xs transition cursor-pointer flex items-center gap-1">
                                <span>👁️ Pratinjau</span>
                                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            </button>
                        </div>
                    </template>
                    <button type="button" @click="closeSyncModal()" class="text-slate-400 hover:text-white p-1 rounded-lg hover:bg-slate-800 transition">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>

            <!-- Modal Body Scrollable -->
            <div class="overflow-y-auto pr-1 space-y-4 text-xs flex-1">
                
                <!-- TAB 1: PENGATURAN FORMULIR -->
                <div x-show="!showPreviewTable" class="space-y-4">
                    
                    <!-- 1. Durasi Turnamen (Hari Dinamis) & Tanggal Mulai -->
                    <div class="p-3.5 rounded-2xl bg-slate-950 border border-slate-800 space-y-3">
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="font-bold text-slate-300 flex items-center gap-1.5 text-amber-400">
                                    <i data-lucide="calendar" class="w-4 h-4"></i>
                                    <span>Durasi Turnamen (Pilihan Hari):</span>
                                </label>
                                <span class="text-[10px] text-emerald-400 font-bold font-mono">Format Resmi: 4 Hari</span>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <template x-for="days in scheduleDaysList" :key="days">
                                    <button type="button" 
                                            @click="tournamentDays = days"
                                            :class="tournamentDays === days ? 'bg-gradient-to-r from-amber-500 to-amber-600 text-slate-950 font-black shadow-md border-amber-400 ring-2 ring-amber-400/30' : 'bg-slate-900 border-slate-800 text-slate-400 hover:text-white'"
                                            class="py-2 px-3 text-center rounded-xl border text-xs font-bold transition cursor-pointer">
                                        <span x-text="days + ' Hari'"></span>
                                    </button>
                                </template>
                                <button type="button" 
                                        x-show="scheduleDaysList.length < 7"
                                        @click="addScheduleDay()"
                                        class="py-2 px-2.5 rounded-xl border border-dashed border-slate-700 hover:border-amber-400 text-slate-400 hover:text-amber-300 font-bold transition flex items-center gap-1 cursor-pointer">
                                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                    <span>Tambah Hari</span>
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2 border-t border-slate-800/80">
                            <div>
                                <label class="block font-bold text-slate-400 text-[11px] mb-1">
                                    Tanggal Mulai (Hari 1):
                                </label>
                                <input type="date" 
                                       x-model="scheduleStartDate"
                                       class="w-full bg-slate-900 border border-slate-800 rounded-xl px-3 py-2 text-white font-mono font-bold text-xs focus:ring-1 focus:ring-amber-500 focus:outline-none">
                            </div>
                            <div class="flex flex-col justify-center">
                                <span class="text-[10px] text-slate-500 font-mono">Alokasi Babak Otomatis:</span>
                                <div class="text-[11px] font-bold text-slate-300 leading-relaxed">
                                    <span x-show="tournamentDays >= 4">📅 H1: 32 Besar • H2: 16B & QF • H3: Semifinal • H4: Final</span>
                                    <span x-show="tournamentDays === 3">📅 H1: 32 Besar • H2: 16B & QF • H3: SF & Final</span>
                                    <span x-show="tournamentDays === 2">📅 H1: Penyisihan s.d QF • H2: Semifinal & Final</span>
                                    <span x-show="tournamentDays === 1">📅 Semua babak dimainkan dalam 1 hari</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Cakupan Sinkronisasi & Mode Distribusi Lapangan -->
                    <div class="p-3.5 rounded-2xl bg-slate-950 border border-slate-800 space-y-3">
                        <div>
                            <label class="block font-bold text-slate-300 mb-1.5 flex items-center gap-1.5 text-indigo-400">
                                <i data-lucide="layers" class="w-4 h-4"></i>
                                <span>Cakupan Kategori:</span>
                            </label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <label class="flex items-center gap-2 p-2.5 rounded-xl border transition cursor-pointer"
                                       :class="scheduleScope === 'all' ? 'bg-indigo-950/40 border-indigo-500/50 text-indigo-200' : 'bg-slate-900/60 border-slate-800 text-slate-400'">
                                    <input type="radio" value="all" x-model="scheduleScope" class="text-indigo-600 focus:ring-0">
                                    <div>
                                        <p class="font-bold text-xs">Seluruh Kategori Lomba</p>
                                        <p class="text-[10px] text-slate-400">Sinkronkan semua kelas sekaligus</p>
                                    </div>
                                </label>
                                <label class="flex items-center gap-2 p-2.5 rounded-xl border transition cursor-pointer"
                                       :class="scheduleScope === 'pool' ? 'bg-indigo-950/40 border-indigo-500/50 text-indigo-200' : 'bg-slate-900/60 border-slate-800 text-slate-400'">
                                    <input type="radio" value="pool" x-model="scheduleScope" class="text-indigo-600 focus:ring-0">
                                    <div>
                                        <p class="font-bold text-xs">Hanya Kategori Ini</p>
                                        <p class="text-[10px] text-slate-400 truncate">{{ $activePool['short_title'] ?? $activePool['title'] ?? 'Kategori Aktif' }}</p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div class="pt-2 border-t border-slate-800/80">
                            <label class="block font-bold text-slate-300 mb-1.5 flex items-center gap-1.5 text-cyan-400">
                                <i data-lucide="map-pin" class="w-4 h-4"></i>
                                <span>Distribusi Lapangan:</span>
                            </label>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                <label class="flex items-start gap-2 p-2.5 rounded-xl border transition cursor-pointer"
                                       :class="scheduleDistributionMode === 'category_based' ? 'bg-cyan-950/40 border-cyan-500/50 text-cyan-200 ring-1 ring-cyan-500/30' : 'bg-slate-900/60 border-slate-800 text-slate-400'">
                                    <input type="radio" value="category_based" x-model="scheduleDistributionMode" class="text-cyan-600 focus:ring-0 mt-0.5">
                                    <div>
                                        <p class="font-bold text-xs">Sektor Otomatis</p>
                                        <p class="text-[10px] text-slate-400 leading-relaxed mt-0.5">
                                            🏸 Lap 1: Kelas 5–6 & Ganda<br>
                                            🏸 Lap 2: Kelas 1–2 & 3–4
                                        </p>
                                    </div>
                                </label>
                                <label class="flex items-start gap-2 p-2.5 rounded-xl border transition cursor-pointer"
                                       :class="scheduleDistributionMode === 'custom' ? 'bg-amber-950/40 border-amber-500/50 text-amber-200 ring-1 ring-amber-500/40' : 'bg-slate-900/60 border-slate-800 text-slate-400'">
                                    <input type="radio" value="custom" x-model="scheduleDistributionMode" class="text-amber-500 focus:ring-0 mt-0.5">
                                    <div>
                                        <p class="font-bold text-xs flex items-center gap-1">
                                            <span>Kustom Lapangan & Kuota</span>
                                            <span class="px-1.5 py-0.2 bg-amber-500 text-slate-950 rounded text-[9px] font-black">FLEKSIBEL</span>
                                        </p>
                                        <p class="text-[10px] text-slate-400 leading-relaxed mt-0.5">
                                            Atur bebas lapangan & batasan kuota partai per kelas
                                        </p>
                                    </div>
                                </label>
                                <label class="flex items-start gap-2 p-2.5 rounded-xl border transition cursor-pointer"
                                       :class="scheduleDistributionMode === 'even' ? 'bg-cyan-950/40 border-cyan-500/50 text-cyan-200 ring-1 ring-cyan-500/30' : 'bg-slate-900/60 border-slate-800 text-slate-400'">
                                    <input type="radio" value="even" x-model="scheduleDistributionMode" class="text-cyan-600 focus:ring-0 mt-0.5">
                                    <div>
                                        <p class="font-bold text-xs">Bagi Rata Bergantian</p>
                                        <p class="text-[10px] text-slate-400 leading-relaxed mt-0.5">
                                            Selang-seling di seluruh lapangan aktif terpilih
                                        </p>
                                    </div>
                                </label>
                            </div>

                            <!-- Panel Kustom Kuota & Lapangan per Kategori -->
                            <div x-show="scheduleDistributionMode === 'custom'" class="pt-3 border-t border-slate-800/80 space-y-3" x-transition>
                                <div class="flex items-center justify-between flex-wrap gap-2">
                                    <label class="font-extrabold text-amber-400 text-xs sm:text-sm flex items-center gap-2">
                                        <i data-lucide="sliders" class="w-4 h-4 text-amber-400"></i>
                                        <span>Atur Lapangan & Kuota Partai Babak Awal per Kategori:</span>
                                    </label>
                                    <span class="text-[11px] text-slate-400 bg-slate-800 px-2.5 py-0.5 rounded-full font-medium">
                                        Kuota kosong = Jadwalkan semua partai babak 1
                                    </span>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                                    @foreach($pools as $p)
                                    <div class="p-3.5 rounded-2xl bg-slate-950/80 border border-slate-800 hover:border-slate-700 shadow-sm flex flex-col justify-between gap-3 transition">
                                        <div class="flex items-start justify-between gap-2">
                                            <div>
                                                <p class="font-extrabold text-white text-xs sm:text-sm leading-snug">{{ $p['title'] }}</p>
                                                <p class="text-[11px] text-slate-400 font-mono mt-0.5 flex items-center gap-1.5">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                                    <span>{{ count($p['participants']) }} Peserta Terdaftar</span>
                                                </p>
                                            </div>
                                            <span class="px-2 py-0.5 rounded-lg text-[10px] font-mono font-bold bg-slate-800/90 text-amber-300 border border-slate-700/80 shrink-0">
                                                {{ strtoupper($p['key']) }}
                                            </span>
                                        </div>

                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 pt-2.5 border-t border-slate-800/80 text-xs">
                                            <div>
                                                <label class="block text-[11px] font-bold text-slate-400 mb-1">Pilih Lapangan:</label>
                                                <select x-model="scheduleCustomRules['{{ $p['key'] }}'].court"
                                                        class="w-full bg-slate-900 border border-slate-700 rounded-xl px-2.5 py-2 text-white font-bold text-xs focus:ring-1 focus:ring-amber-500 focus:outline-none">
                                                    <template x-for="court in scheduleCourts" :key="court">
                                                        <option :value="court" x-text="court" :selected="scheduleCustomRules['{{ $p['key'] }}']?.court === court"></option>
                                                    </template>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-[11px] font-bold text-slate-400 mb-1">Kuota Hari 1 (Partai):</label>
                                                <div class="flex items-center">
                                                    <input type="number" 
                                                           x-model.number="scheduleCustomRules['{{ $p['key'] }}'].quota_day1" 
                                                           min="1" 
                                                           max="64" 
                                                           placeholder="Semua"
                                                           class="w-full bg-slate-900 border border-slate-700 rounded-l-xl px-3 py-2 text-amber-300 font-mono font-bold text-xs placeholder:text-slate-500 focus:ring-1 focus:ring-amber-500 focus:outline-none">
                                                    <span class="bg-slate-800 border border-l-0 border-slate-700 rounded-r-xl px-2.5 py-2 text-slate-400 text-xs font-semibold select-none">
                                                        partai
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                <p class="text-[11px] text-slate-400 bg-amber-950/20 border border-amber-500/20 p-2.5 rounded-xl leading-relaxed">
                                    💡 <strong>Contoh:</strong> Jika Kelas 5-6 Putra diisi <span class="text-amber-300 font-bold">7 partai</span> di Lapangan 1 dan Kelas 1-2 diisi <span class="text-amber-300 font-bold">8 partai</span> di Lapangan 2, sistem otomatis membatasi partai Hari 1 sesuai kuota tersebut dan memindahkan sisanya ke Hari 2.
                                </p>
                            </div>
                        </div>

                        <!-- Pilihan Lapangan Aktif -->
                        <div class="pt-2 border-t border-slate-800/80">
                            <div class="flex items-center justify-between mb-2">
                                <label class="font-bold text-slate-400 text-[11px]">
                                    Lapangan Aktif:
                                </label>
                                <span class="text-[10px] text-slate-500 font-mono">Utama: Lap 1 & 2</span>
                            </div>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 mb-2">
                                <template x-for="court in defaultCourtOptions" :key="court">
                                    <button type="button" 
                                            @click="toggleCourt(court)"
                                            :class="scheduleCourts.includes(court) ? 'bg-indigo-600/30 border-indigo-500 text-indigo-300 font-extrabold ring-1 ring-indigo-500/40' : 'bg-slate-900 border-slate-800 text-slate-400 hover:text-slate-200'"
                                            class="p-2 rounded-xl border flex items-center justify-between transition cursor-pointer text-left">
                                        <span class="truncate text-xs" x-text="court"></span>
                                        <span class="w-3.5 h-3.5 rounded flex items-center justify-center text-[9px] shrink-0"
                                              :class="scheduleCourts.includes(court) ? 'bg-indigo-500 text-white' : 'border border-slate-700'">
                                            <i data-lucide="check" class="w-3 h-3" x-show="scheduleCourts.includes(court)"></i>
                                        </span>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Jam Mulai, Jam Selesai & Estimasi Durasi Per Babak -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 p-3.5 rounded-2xl bg-slate-950 border border-slate-800">
                        <div>
                            <label class="block font-bold text-slate-300 mb-1">
                                Jam Mulai Harian:
                            </label>
                            <input type="time" 
                                   x-model="scheduleStartTime"
                                   class="w-full bg-slate-900 border border-slate-800 rounded-xl px-3 py-2 text-white font-mono font-bold text-xs focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                            <p class="text-[10px] text-slate-500 mt-1">Default: 08:00 WIB</p>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-300 mb-1">
                                Jam Selesai Harian (Maks):
                            </label>
                            <input type="time" 
                                   x-model="scheduleEndTime"
                                   class="w-full bg-slate-900 border border-slate-800 rounded-xl px-3 py-2 text-white font-mono font-bold text-xs focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                            <p class="text-[10px] text-slate-500 mt-1">Batas Maks: 18:00 WIB</p>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-300 mb-1">
                                Durasi Penyisihan (H1-H2):
                            </label>
                            <div class="relative flex items-center">
                                <input type="number" 
                                       x-model="scheduleMatchDuration"
                                       min="10" 
                                       max="60" 
                                       step="5"
                                       class="w-full bg-slate-900 border border-slate-800 rounded-xl px-3 py-2 text-white font-mono font-bold text-xs focus:ring-1 focus:ring-emerald-500 focus:outline-none pr-12">
                                <span class="absolute right-2.5 text-slate-500 font-bold text-[10px] pointer-events-none">menit</span>
                            </div>
                            <p class="text-[10px] text-slate-500 mt-1">Sesuai Kertas: 20 mnt</p>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-300 mb-1">
                                Durasi Semifinal & Final:
                            </label>
                            <div class="relative flex items-center">
                                <input type="number" 
                                       x-model="scheduleSemifinalDuration"
                                       min="15" 
                                       max="90" 
                                       step="5"
                                       class="w-full bg-slate-900 border border-slate-800 rounded-xl px-3 py-2 text-white font-mono font-bold text-xs focus:ring-1 focus:ring-emerald-500 focus:outline-none pr-12">
                                <span class="absolute right-2.5 text-slate-500 font-bold text-[10px] pointer-events-none">menit</span>
                            </div>
                            <p class="text-[10px] text-slate-500 mt-1">Sesuai Kertas: 30 mnt</p>
                        </div>

                        <!-- Checkbox Jeda Ishoma Siang -->
                        <div class="col-span-1 sm:col-span-2 md:col-span-4 pt-2 border-t border-slate-800/80 flex items-center justify-between flex-wrap gap-2">
                            <label class="flex items-center gap-2 cursor-pointer text-slate-300 font-medium">
                                <input type="checkbox" x-model="scheduleLunchBreak" class="rounded bg-slate-900 border-slate-700 text-emerald-500 focus:ring-0">
                                <span>Otomatis Jeda Ishoma Siang (12:00 – 13:00 WIB) pada Hari 1, 2, 3</span>
                            </label>
                            <span class="text-[10px] text-amber-400 font-mono font-bold">Lanjut Pukul 13:00 WIB</span>
                        </div>

                        <!-- Checkbox Jeda Jumat -->
                        <div class="col-span-1 sm:col-span-2 md:col-span-4 pt-1 flex items-center justify-between flex-wrap gap-2">
                            <label class="flex items-center gap-2 cursor-pointer text-slate-300 font-medium">
                                <input type="checkbox" x-model="scheduleFridayBreak" class="rounded bg-slate-900 border-slate-700 text-emerald-500 focus:ring-0">
                                <span>Otomatis Jeda Ibadah Sholat Jum'at (10:30 – 13:00 WIB) pada Hari Final</span>
                            </label>
                            <span class="text-[10px] text-emerald-400 font-mono font-bold">Lanjut Pukul 13:00 WIB</span>
                        </div>
                    </div>

                </div>

                <!-- TAB 2: PRATINJAU JADWAL (SIMULATION PREVIEW) -->
                <div x-show="showPreviewTable" class="space-y-3" style="display: none;">
                    <template x-if="previewData">
                        <div>
                            <!-- Header Ringkasan Preview -->
                            <div class="p-3 rounded-2xl bg-emerald-950/30 border border-emerald-500/30 flex items-center justify-between flex-wrap gap-2 text-xs">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                                    <span class="font-extrabold text-white">Simulasi Jadwal Berhasil Dihitung!</span>
                                    <span class="text-slate-400" x-text="'• Total: ' + previewData.total_matches + ' Partai Pertandingan'"></span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 font-mono text-[11px] font-bold" x-text="previewData.summary"></span>
                                    <a :href="'{{ route('pic.bracket.export_excel', $competition->id) }}?use_simulation=1&tournament_days=' + tournamentDays + '&start_date=' + scheduleStartDate + '&start_time=' + scheduleStartTime + '&end_time=' + scheduleEndTime + '&match_duration=' + scheduleMatchDuration + '&semifinal_duration=' + scheduleSemifinalDuration + '&distribution_mode=' + scheduleDistributionMode + '&scope=' + scheduleScope + '&pool_key=' + activePoolKey + '&lunch_break=' + (scheduleLunchBreak ? '1' : '0') + '&friday_break=' + (scheduleFridayBreak ? '1' : '0') + '&custom_rules=' + encodeURIComponent(JSON.stringify(scheduleCustomRules))" 
                                       target="_blank"
                                       class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black text-xs shadow transition cursor-pointer"
                                       title="Download hasil simulasi jadwal ini ke file Excel">
                                        <i data-lucide="file-spreadsheet" class="w-3.5 h-3.5"></i>
                                        <span>Download Excel</span>
                                    </a>
                                </div>
                            </div>

                            <!-- Pilihan Tab Hari di Pratinjau -->
                            <div class="flex items-center gap-1.5 overflow-x-auto py-2">
                                <template x-for="(dayData, dKey) in (previewData.days || {})" :key="dKey">
                                    <button type="button" 
                                            @click="previewActiveDay = dKey"
                                            :class="previewActiveDay == dKey ? 'bg-gradient-to-r from-amber-500 to-amber-600 text-slate-950 font-black shadow-md border-amber-400' : 'bg-slate-950 border-slate-800 text-slate-400 hover:text-white'"
                                            class="px-3 py-1.5 rounded-xl border text-xs font-bold transition flex items-center gap-1.5 whitespace-nowrap cursor-pointer">
                                        <span x-text="dayData.label"></span>
                                        <span class="px-1.5 py-0.2 rounded-full text-[10px] font-mono"
                                              :class="previewActiveDay == dKey ? 'bg-black/30 text-slate-950' : 'bg-slate-800 text-slate-300'"
                                              x-text="dayData.total_matches + ' Partai'"></span>
                                    </button>
                                </template>
                            </div>

                            <!-- Tabel Rincian Partai Per Hari -->
                            <div class="border border-slate-800 rounded-2xl overflow-hidden bg-slate-950/70">
                                <div class="max-h-[380px] overflow-y-auto scrollbar-thin">
                                    <table class="w-full text-left text-xs">
                                        <thead class="bg-slate-900 text-slate-400 font-bold sticky top-0 z-10 border-b border-slate-800">
                                            <tr>
                                                <th class="p-2.5 text-center w-14">Jam</th>
                                                <th class="p-2.5 w-32">Pilih Lapangan</th>
                                                <th class="p-2.5 text-center w-16">Partai</th>
                                                <th class="p-2.5 w-32">Kategori</th>
                                                <th class="p-2.5 w-28">Babak</th>
                                                <th class="p-2.5">Pertandingan (Tim 1 vs Tim 2)</th>
                                                <th class="p-2.5 text-center w-24">Pindah Hari</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-800/60 font-mono text-[11px]">
                                            <template x-for="(m, mIdx) in (previewData.days[previewActiveDay]?.matches || [])" :key="m.match_code">
                                                <tr class="hover:bg-slate-900/50 transition" :class="m.scheduled_time === '13:00' && previewActiveDay == 4 ? 'bg-emerald-950/20' : ''">
                                                    <td class="p-2 text-center font-bold text-emerald-400" x-text="m.scheduled_time || '-'"></td>
                                                    <td class="p-2">
                                                        <select x-model="m.court_number" 
                                                                @change="recalculatePreviewTimes()"
                                                                class="bg-slate-900 border border-slate-700 rounded-lg px-2 py-1 text-[11px] font-bold text-white focus:ring-1 focus:ring-amber-500 cursor-pointer">
                                                            <template x-for="c in scheduleCourts" :key="c">
                                                                <option :value="c" x-text="c" :selected="m.court_number === c"></option>
                                                            </template>
                                                        </select>
                                                    </td>
                                                    <td class="p-2 text-center text-slate-300 font-bold" x-text="m.match_order ? '#' + m.match_order : '-'"></td>
                                                    <td class="p-2 font-sans font-medium text-slate-300 truncate" x-text="m.pool_title"></td>
                                                    <td class="p-2 font-sans text-slate-400 truncate" x-text="m.round_name"></td>
                                                    <td class="p-2 font-sans">
                                                        <div class="flex items-center gap-1.5 truncate">
                                                            <span class="font-bold text-white truncate" x-text="m.team1_player"></span>
                                                            <span class="text-slate-500 text-[10px]" x-text="'(' + m.team1_school + ')'"></span>
                                                            <span class="text-amber-400 font-bold px-1">VS</span>
                                                            <span class="font-bold text-white truncate" x-text="m.team2_player"></span>
                                                            <span class="text-slate-500 text-[10px]" x-text="'(' + m.team2_school + ')'"></span>
                                                        </div>
                                                    </td>
                                                    <td class="p-2 text-center">
                                                        <select x-model.number="m.match_day" 
                                                                @change="recalculatePreviewTimes()"
                                                                class="bg-slate-900 border border-slate-700 rounded-lg px-1.5 py-1 text-[10px] font-bold text-amber-300 focus:ring-1 focus:ring-amber-500 cursor-pointer">
                                                            <template x-for="dNum in scheduleDaysList" :key="dNum">
                                                                <option :value="dNum" x-text="'Hari ' + dNum" :selected="m.match_day == dNum"></option>
                                                            </template>
                                                        </select>
                                                    </td>
                                                </tr>
                                            </template>
                                            <template x-if="!previewData.days[previewActiveDay]?.matches?.length">
                                                <tr>
                                                    <td colspan="7" class="p-8 text-center text-slate-500 font-sans">
                                                         Tidak ada pertandingan yang dijadwalkan pada hari ini.
                                                    </td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

            </div>

            <!-- Modal Footer Buttons -->
            <div class="flex items-center justify-between gap-2.5 pt-3 border-t border-slate-800 shrink-0">
                <div class="flex items-center gap-2">
                    <button type="button" 
                            x-show="showPreviewTable"
                            @click="showPreviewTable = false"
                            class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-xs transition cursor-pointer flex items-center gap-1.5">
                        <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                        <span>Kembali ke Pengaturan</span>
                    </button>
                    <button type="button" 
                            x-show="!showPreviewTable"
                            @click="closeSyncModal()"
                            class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-xs transition cursor-pointer">
                        Batal
                    </button>
                    <!-- Tombol Reset Jadwal (Kosongkan Waktu & Lapangan) -->
                    <button type="button" 
                            x-show="!showPreviewTable"
                            @click="resetSchedule()"
                            :disabled="isResetting || isSyncing"
                            class="px-3 py-2 rounded-xl bg-rose-500/15 hover:bg-rose-500/25 text-rose-300 border border-rose-500/30 font-bold text-xs transition cursor-pointer flex items-center gap-1.5 disabled:opacity-50"
                            title="Kosongkan seluruh jam main dan hari pertandingan yang belum bertanding">
                        <i data-lucide="trash-2" class="w-3.5 h-3.5 text-rose-400" :class="isResetting ? 'animate-spin' : ''"></i>
                        <span x-text="isResetting ? 'Mengosongkan...' : 'Reset Jadwal'"></span>
                    </button>
                </div>

                <div class="flex items-center gap-2">
                    <!-- Tombol Lihat Pratinjau (Sebelum Simpan) -->
                    <button type="button" 
                            x-show="!showPreviewTable"
                            @click="fetchSchedulePreview()"
                            :disabled="isLoadingPreview"
                            class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-amber-300 border border-amber-500/30 font-bold text-xs transition flex items-center gap-1.5 cursor-pointer disabled:opacity-50">
                        <i data-lucide="eye" class="w-4 h-4" :class="isLoadingPreview ? 'animate-spin' : ''"></i>
                        <span x-text="isLoadingPreview ? 'Menghitung Simulasi...' : 'Lihat Pratinjau Jadwal'"></span>
                    </button>

                    <!-- Tombol Terapkan & Sinkronkan -->
                    <button type="button" 
                            @click="executeSyncSchedule()"
                            :disabled="isSyncing || scheduleCourts.length === 0"
                            class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 text-slate-950 font-black text-xs shadow-lg shadow-emerald-500/20 disabled:opacity-50 disabled:cursor-not-allowed transition flex items-center gap-2 cursor-pointer">
                        <i data-lucide="check-circle" class="w-4 h-4" :class="isSyncing ? 'animate-spin' : ''"></i>
                        <span x-text="isSyncing ? 'Menyinkronkan...' : (showPreviewTable ? 'Konfirmasi & Terapkan Jadwal' : 'Terapkan & Sinkronkan')"></span>
                    </button>
                </div>
        </div>
    </div>

    <!-- Modal: Quick Edit Single Match Schedule -->
    <div x-show="showEditMatchModal" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.away="closeEditModal()" 
             class="bg-slate-900 border border-slate-800 w-full max-w-md rounded-3xl p-6 shadow-2xl space-y-4 text-white animate-in fade-in zoom-in-95 duration-200">
            
            <div class="flex items-center justify-between border-b border-slate-800 pb-3.5">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-amber-500/20 border border-amber-500/30 text-amber-400 flex items-center justify-center font-bold">
                        <i data-lucide="edit-2" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-sm text-white">Ubah Jadwal Partai</h3>
                        <p class="text-[11px] font-mono text-amber-400 font-bold" x-text="editMatchData.matchCode"></p>
                    </div>
                </div>
                <button type="button" @click="closeEditModal()" class="text-slate-400 hover:text-white p-1 rounded-lg hover:bg-slate-800 transition">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <div class="space-y-3.5 text-xs">
                <!-- Hari & Tanggal -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-300 mb-1">
                            Pilih Hari:
                        </label>
                        <select x-model="editMatchData.matchDay" 
                                class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-white font-bold text-xs focus:ring-1 focus:ring-amber-500 focus:outline-none">
                            <option value="1">Hari 1</option>
                            <option value="2">Hari 2</option>
                            <option value="3">Hari 3</option>
                            <option value="4">Hari 4</option>
                            <option value="5">Hari 5</option>
                            <option value="6">Hari 6</option>
                            <option value="7">Hari 7</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-300 mb-1">
                            Tanggal Main:
                        </label>
                        <input type="date" 
                               x-model="editMatchData.matchDate"
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-white font-mono text-xs focus:ring-1 focus:ring-amber-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-300 mb-1">
                        Pilih Lapangan:
                    </label>
                    <select x-model="editMatchData.courtNumber" 
                            class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-white font-bold text-xs focus:ring-1 focus:ring-amber-500 focus:outline-none">
                        <template x-for="court in defaultCourtOptions" :key="court">
                            <option :value="court" x-text="court"></option>
                        </template>
                        <option value="Lapangan 5">Lapangan 5</option>
                        <option value="Lapangan 6">Lapangan 6</option>
                        <option value="BYE">BYE (Lolos Langsung)</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-300 mb-1">
                            Nomor Partai:
                        </label>
                        <input type="number" 
                               x-model="editMatchData.matchOrder"
                               min="1" 
                               max="999"
                               placeholder="Cth: 1"
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-white font-mono font-bold text-xs focus:ring-1 focus:ring-amber-500 focus:outline-none">
                        <p class="text-[10px] text-slate-500 mt-1">Urutan partai di lapangan</p>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-300 mb-1">
                            Jam Tanding (WIB):
                        </label>
                        <input type="text" 
                               x-model="editMatchData.scheduledTime"
                               placeholder="Cth: 08:35"
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-white font-mono font-bold text-xs focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                        <p class="text-[10px] text-slate-500 mt-1">Estimasi jam main</p>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-800">
                <button type="button" 
                        @click="closeEditModal()"
                        class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-xs transition cursor-pointer">
                    Batal
                </button>
                <button type="button" 
                        @click="saveSingleMatchSchedule()"
                        :disabled="isSavingSchedule"
                        class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs shadow-lg shadow-amber-500/20 disabled:opacity-50 disabled:cursor-not-allowed transition flex items-center gap-1.5 cursor-pointer">
                    <i data-lucide="save" class="w-3.5 h-3.5"></i>
                    <span x-text="isSavingSchedule ? 'Menyimpan...' : 'Simpan Jadwal'"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- Modal: Pengaturan Publikasi & Redaksi Standby TV Bagan -->
    <div x-show="showPublicationModal" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-black/85 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.away="if (!isSavingPublication) closePublicationModal()" 
             class="bg-slate-900 border border-slate-700/80 w-full max-w-lg rounded-3xl p-6 sm:p-7 shadow-2xl space-y-5 text-white animate-in fade-in zoom-in-95 duration-200">
            
            <!-- Modal Header -->
            <div class="flex items-start justify-between border-b border-slate-800 pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-2xl flex items-center justify-center font-bold shadow-lg"
                         :class="pubSettings.is_published 
                            ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 shadow-emerald-500/20' 
                            : 'bg-rose-500/20 text-rose-400 border border-rose-500/30 shadow-rose-500/20'">
                        <i :data-lucide="pubSettings.is_published ? 'tv' : 'lock'" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <span class="text-[10px] font-mono font-bold uppercase tracking-wider text-slate-400 block">KONTROL PENYIARAN</span>
                        <h3 class="font-black text-base text-white">Publikasi & Pengumuman TV Bagan</h3>
                    </div>
                </div>
                <button type="button" @click="closePublicationModal()" :disabled="isSavingPublication" class="text-slate-400 hover:text-white p-1 rounded-lg hover:bg-slate-800 transition">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Modal Body Form -->
            <div class="space-y-4 text-xs">
                
                <!-- Status Switcher Radio/Pill Buttons -->
                <div>
                    <label class="block font-bold text-slate-300 mb-2">
                        Pilih Status Layar Bagan untuk Penonton & Publik:
                    </label>
                    <div class="grid grid-cols-2 gap-2.5">
                        <!-- Option 1: PUBLIK (ON) -->
                        <button type="button" 
                                @click="pubSettings.is_published = true"
                                class="p-3 rounded-2xl border text-left transition cursor-pointer flex flex-col justify-between gap-2"
                                :class="pubSettings.is_published 
                                    ? 'bg-emerald-500/15 border-emerald-500 text-white ring-2 ring-emerald-500/30' 
                                    : 'bg-slate-950 border-slate-800 text-slate-400 hover:bg-slate-900'">
                            <div class="flex items-center justify-between">
                                <span class="font-black text-xs text-emerald-400 flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                    <span>PUBLIK (ON)</span>
                                </span>
                                <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-400" x-show="pubSettings.is_published"></i>
                            </div>
                            <span class="text-[10.5px] leading-tight text-slate-300">
                                Bagan pertandingan resmi dibuka & dapat dilihat langsung di TV/HP peserta.
                            </span>
                        </button>

                        <!-- Option 2: STANDBY / KUNCI (OFF) -->
                        <button type="button" 
                                @click="pubSettings.is_published = false"
                                class="p-3 rounded-2xl border text-left transition cursor-pointer flex flex-col justify-between gap-2"
                                :class="!pubSettings.is_published 
                                    ? 'bg-rose-500/15 border-rose-500 text-white ring-2 ring-rose-500/30' 
                                    : 'bg-slate-950 border-slate-800 text-slate-400 hover:bg-slate-900'">
                            <div class="flex items-center justify-between">
                                <span class="font-black text-xs text-rose-400 flex items-center gap-1.5">
                                    <i data-lucide="lock" class="w-3.5 h-3.5"></i>
                                    <span>STANDBY (OFF)</span>
                                </span>
                                <i data-lucide="check-circle-2" class="w-4 h-4 text-rose-400" x-show="!pubSettings.is_published"></i>
                            </div>
                            <span class="text-[10.5px] leading-tight text-slate-300">
                                Bagan disembunyikan. Layar TV penonton menampilkan pengumuman panitia.
                            </span>
                        </button>
                    </div>
                </div>

                <!-- Custom Announcement Editor (Active when Standby) -->
                <div x-show="!pubSettings.is_published" x-transition class="space-y-3.5 p-4 rounded-2xl bg-slate-950/80 border border-slate-800">
                    <div class="flex items-center justify-between border-b border-slate-800/80 pb-2">
                        <span class="text-[11px] font-mono font-bold text-amber-400 uppercase tracking-wider flex items-center gap-1.5">
                            <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                            <span>Redaksi Pesan Pengumuman Standby</span>
                        </span>
                        <span class="text-[10px] text-slate-500">Tampil di Layar TV & Publik</span>
                    </div>

                    <!-- Judul Standby -->
                    <div>
                        <label class="block font-bold text-slate-300 mb-1">
                            Judul Pengumuman: <span class="text-rose-400">*</span>
                        </label>
                        <input type="text" 
                               x-model="pubSettings.standby_title"
                               placeholder="Cth: BAGAN PERTANDINGAN SEDANG DISIAPKAN"
                               class="w-full bg-slate-900 border border-slate-700/80 rounded-xl px-3.5 py-2.5 text-white font-bold text-xs focus:ring-1 focus:ring-amber-500 focus:outline-none">
                    </div>

                    <!-- Pesan Redaksi Kustom -->
                    <div>
                        <label class="block font-bold text-slate-300 mb-1">
                            Isi Pesan Keterangan / Pengumuman: <span class="text-rose-400">*</span>
                        </label>
                        <textarea x-model="pubSettings.standby_message" 
                                  rows="3"
                                  placeholder="Tuliskan keterangan detail untuk penonton/atlet..."
                                  class="w-full bg-slate-900 border border-slate-700/80 rounded-xl p-3 text-white text-xs leading-relaxed focus:ring-1 focus:ring-amber-500 focus:outline-none"></textarea>
                        <p class="text-[10px] text-slate-400 mt-1">
                            Tips: Anda bisa mencantumkan jam perkiraan rilis, jadwal technical meeting, atau info penting lainnya.
                        </p>
                    </div>

                    <!-- Kontak / Info Tambahan -->
                    <div>
                        <label class="block font-bold text-slate-300 mb-1">
                            Keterangan Meja Panitia / Kontak (Opsional):
                        </label>
                        <input type="text" 
                               x-model="pubSettings.standby_contact"
                               placeholder="Cth: Meja Panitia / Sekretariat GOR"
                               class="w-full bg-slate-900 border border-slate-700/80 rounded-xl px-3.5 py-2 text-white text-xs focus:ring-1 focus:ring-amber-500 focus:outline-none">
                    </div>
                </div>

                <!-- Live Info Note -->
                <div class="p-3 rounded-xl bg-indigo-950/30 border border-indigo-500/30 text-[11px] text-indigo-200 leading-relaxed flex items-start gap-2">
                    <i data-lucide="info" class="w-4 h-4 text-indigo-400 shrink-0 mt-0.5"></i>
                    <span>
                        <strong>Catatan Panitia:</strong> Ketika status diatur ke <em>STANDBY (OFF)</em>, Anda dan panitia yang login tetap dapat melihat bagan secara normal untuk keperluan pengaturan. Penonton umum dan layar TV publik akan otomatis beralih menampilkan pesan pengumuman di atas.
                    </span>
                </div>
            </div>

            <!-- Modal Action Buttons -->
            <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-800">
                <button type="button" 
                        @click="closePublicationModal()"
                        class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-xs transition cursor-pointer">
                    Batal
                </button>
                <button type="button" 
                        @click="savePublicationSettings()"
                        :disabled="isSavingPublication"
                        class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 text-slate-950 font-black text-xs shadow-lg shadow-emerald-500/20 disabled:opacity-50 disabled:cursor-not-allowed transition flex items-center gap-1.5 cursor-pointer">
                    <i data-lucide="save" class="w-4 h-4" :class="isSavingPublication ? 'animate-spin' : ''"></i>
                    <span x-text="isSavingPublication ? 'Menyimpan...' : 'Simpan Pengaturan TV'"></span>
                </button>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    function bracketApp() {
        return {
            competitionId: {{ $competition->id }},
            activePoolKey: '{{ $activePoolKey }}',
            hasRounds: {{ ($bracketData && !empty($bracketData['rounds'])) ? 'true' : 'false' }},
            viewMode: 'classic',
            classicTheme: 'white',
            isSyncing: false,
            toastMessage: '',
            toastSuccess: true,


            // Publication & Standby Notice State
            showPublicationModal: false,
            isSavingPublication: false,
            @php
                $pubDefault = ['is_published' => true, 'standby_title' => 'BAGAN PERTANDINGAN SEDANG DISIAPKAN', 'standby_message' => 'Bagan resmi akan segera dirilis oleh panitia setelah sesi pengundian dan technical meeting selesai.', 'standby_contact' => 'Meja Panitia / Sekretariat GOR'];
            @endphp
            pubSettings: @json($tvPublication ?? $pubDefault),

            openPublicationModal() {
                this.showPublicationModal = true;
                this.$nextTick(() => {
                    if (window.lucide) window.lucide.createIcons();
                });
            },

            closePublicationModal() {
                this.showPublicationModal = false;
            },

            async savePublicationSettings() {
                if (this.isSavingPublication) return;
                this.isSavingPublication = true;

                try {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
                    const response = await fetch('{{ route("pic.bracket.publication", $competition->id) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            is_published: Boolean(this.pubSettings.is_published),
                            standby_title: this.pubSettings.standby_title,
                            standby_message: this.pubSettings.standby_message,
                            standby_contact: this.pubSettings.standby_contact,
                        })
                    });

                    const res = await response.json();
                    if (res.success) {
                        this.toastSuccess = true;
                        this.toastMessage = res.message;
                        this.pubSettings = res.publication;
                        this.closePublicationModal();
                    } else {
                        alert(res.message || 'Gagal menyimpan pengaturan publikasi.');
                    }
                } catch (err) {
                    console.error('Save publication error:', err);
                    alert('Terjadi kesalahan jaringan.');
                } finally {
                    this.isSavingPublication = false;
                    if (window.lucide) window.lucide.createIcons();
                }
            },

            // Format & Play-off Modal State
            showFormatModal: false,
            bracketMode: '{{ $bracketData['playoffs']['mode'] ?? ($competition->bracket_settings[$activePoolKey]['mode'] ?? 'auto') }}',
            targetBracketSize: {{ $bracketData['playoffs']['target_bracket_size'] ?? ($competition->bracket_settings[$activePoolKey]['target_bracket_size'] ?? 32) }},
            isSavingFormat: false,

            // Schedule & Court Management State
            showSyncScheduleModal: false,
            showEditMatchModal: false,
            defaultCourtOptions: ['Lapangan 1', 'Lapangan 2', 'Lapangan 3', 'Lapangan 4'],
            scheduleCourts: ['Lapangan 1', 'Lapangan 2'],
            tournamentDays: 4,
            scheduleDaysList: [1, 2, 3, 4],
            scheduleStartDate: '{{ $competition->schedule_date ? \Carbon\Carbon::parse($competition->schedule_date)->format("Y-m-d") : "2026-09-29" }}',
            scheduleScope: 'all',
            scheduleDistributionMode: 'category_based',
            newCourtInput: '',
            scheduleStartTime: '08:00',
            scheduleEndTime: '18:00',
            scheduleMatchDuration: 20,
            scheduleSemifinalDuration: 30,
            scheduleLunchBreak: true,
            scheduleLunchStart: '12:00',
            scheduleLunchEnd: '13:00',
            scheduleFridayBreak: true,
            scheduleCustomRules: {
                @foreach($pools as $p)
                '{{ $p['key'] }}': {
                    court: '{{ (str_contains($p['key'], "kat_c") || stripos($p['title'], "kelas 5") !== false || stripos($p['title'], "ganda") !== false) ? "Lapangan 1" : "Lapangan 2" }}',
                    day: 1,
                    quota_day1: null
                },
                @endforeach
            },

            // Preview State
            showPreviewTable: false,
            isLoadingPreview: false,
            previewData: null,
            previewActiveDay: 1,

            // Single match edit state
            editMatchData: {
                matchCode: '',
                courtNumber: 'Lapangan 1',
                scheduledTime: '',
                matchOrder: '',
                matchDay: 1,
                matchDate: ''
            },
            isSavingSchedule: false,

            openFormatModal() {
                this.showFormatModal = true;
                this.$nextTick(() => {
                    if (window.lucide) window.lucide.createIcons();
                });
            },

            closeFormatModal() {
                this.showFormatModal = false;
            },

            async saveBracketFormat() {
                if (this.isSavingFormat) return;
                this.isSavingFormat = true;
                try {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
                    const response = await fetch('{{ route("pic.bracket.save_format", $competition->id) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            pool_key: this.activePoolKey,
                            mode: this.bracketMode,
                            target_bracket_size: parseInt(this.targetBracketSize)
                        })
                    });

                    const res = await response.json();
                    if (res.success) {
                        this.toastSuccess = true;
                        this.toastMessage = res.message;
                        this.closeFormatModal();
                        setTimeout(() => window.location.reload(), 600);
                    } else {
                        alert(res.message || 'Gagal menyimpan format bagan.');
                    }
                } catch (err) {
                    console.error('Save format error:', err);
                    alert('Terjadi kesalahan jaringan.');
                } finally {
                    this.isSavingFormat = false;
                }
            },

            openSyncModal() {
                this.showSyncScheduleModal = true;
                this.showPreviewTable = false;
                this.previewData = null;
                this.$nextTick(() => {
                    if (window.lucide) window.lucide.createIcons();
                });
            },

            closeSyncModal() {
                this.showSyncScheduleModal = false;
                this.showPreviewTable = false;
            },

            addScheduleDay() {
                const nextDay = this.scheduleDaysList.length + 1;
                if (nextDay <= 7) {
                    this.scheduleDaysList.push(nextDay);
                    this.tournamentDays = nextDay;
                }
            },

            toggleCourt(courtName) {
                if (this.scheduleCourts.includes(courtName)) {
                    if (this.scheduleCourts.length > 1) {
                        this.scheduleCourts = this.scheduleCourts.filter(c => c !== courtName);
                    }
                } else {
                    this.scheduleCourts.push(courtName);
                }
            },

            addCustomCourt() {
                const name = this.newCourtInput.trim();
                if (name && !this.scheduleCourts.includes(name)) {
                    this.scheduleCourts.push(name);
                    if (!this.defaultCourtOptions.includes(name)) {
                        this.defaultCourtOptions.push(name);
                    }
                    this.newCourtInput = '';
                }
            },

            removeCourt(courtName) {
                if (this.scheduleCourts.length > 1) {
                    this.scheduleCourts = this.scheduleCourts.filter(c => c !== courtName);
                }
            },

            openEditScheduleModal(matchCode, courtNumber, scheduledTime, matchOrder, matchDay, matchDate) {
                this.editMatchData = {
                    matchCode: matchCode,
                    courtNumber: courtNumber || 'Lapangan 1',
                    scheduledTime: scheduledTime || '',
                    matchOrder: matchOrder || '',
                    matchDay: matchDay || 1,
                    matchDate: matchDate || ''
                };
                this.showEditMatchModal = true;
                this.$nextTick(() => {
                    if (window.lucide) window.lucide.createIcons();
                });
            },

            closeEditModal() {
                this.showEditMatchModal = false;
            },

            async saveSingleMatchSchedule() {
                if (this.isSavingSchedule) return;
                this.isSavingSchedule = true;
                try {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
                    const response = await fetch('{{ route("pic.bracket.update_schedule", $competition->id) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            match_code: this.editMatchData.matchCode,
                            court_number: this.editMatchData.courtNumber,
                            scheduled_time: this.editMatchData.scheduledTime,
                            match_order: this.editMatchData.matchOrder ? parseInt(this.editMatchData.matchOrder) : null,
                            match_day: this.editMatchData.matchDay ? parseInt(this.editMatchData.matchDay) : 1,
                            match_date: this.editMatchData.matchDate || null
                        })
                    });

                    const res = await response.json();
                    if (res.success) {
                        this.toastSuccess = true;
                        this.toastMessage = res.message;
                        this.closeEditModal();
                        setTimeout(() => window.location.reload(), 700);
                    } else {
                        alert(res.message || 'Gagal menyimpan perubahan.');
                    }
                } catch (err) {
                    console.error('Save schedule error:', err);
                    alert('Terjadi kesalahan jaringan.');
                } finally {
                    this.isSavingSchedule = false;
                    if (window.lucide) window.lucide.createIcons();
                }
            },

            recalculatePreviewTimes() {
                if (!this.previewData || !this.previewData.matches) return;

                const days = {};
                for (let d = 1; d <= this.tournamentDays; d++) {
                    const existingDay = this.previewData.days ? this.previewData.days[d] : null;
                    days[d] = {
                        day_num: d,
                        date: existingDay?.date || null,
                        label: existingDay?.label || ('Hari ' + d),
                        total_matches: 0,
                        matches: []
                    };
                }

                // Place each match into its assigned day
                this.previewData.matches.forEach(m => {
                    let d = parseInt(m.match_day) || 1;
                    if (d > this.tournamentDays) d = this.tournamentDays;
                    if (d < 1) d = 1;
                    m.match_day = d;
                    m.match_day_label = days[d]?.label || ('Hari ' + d);
                    m.match_date = days[d]?.date || null;
                    if (!days[d]) {
                        days[d] = { day_num: d, date: null, label: 'Hari ' + d, total_matches: 0, matches: [] };
                    }
                    days[d].matches.push(m);
                });

                // Calculate order & time per court for each day
                for (let d = 1; d <= this.tournamentDays; d++) {
                    const dayMatches = days[d].matches;
                    const courtCounters = {};
                    const courtCurrentTime = {};

                    this.scheduleCourts.forEach(c => {
                        courtCounters[c] = 0;
                        const [h, min] = (this.scheduleStartTime || '08:00').split(':').map(Number);
                        const dt = new Date();
                        dt.setHours(h, min, 0, 0);
                        courtCurrentTime[c] = dt;
                    });

                    const duration = (d >= 3) ? parseInt(this.scheduleSemifinalDuration) : parseInt(this.scheduleMatchDuration);
                    let dayContested = 0;

                    dayMatches.forEach(m => {
                        if (!m.is_contested || m.court_number === 'BYE') {
                            m.scheduled_time = null;
                            m.match_order = null;
                            return;
                        }

                        let court = m.court_number;
                        if (!courtCurrentTime[court]) {
                            court = this.scheduleCourts[0] || 'Lapangan 1';
                            m.court_number = court;
                        }

                        // Jeda Ishoma Siang (12:00 - 13:00) pada hari 1, 2, 3
                        if (this.scheduleLunchBreak && d !== 4) {
                            const curHours = courtCurrentTime[court].getHours();
                            const curMins = courtCurrentTime[court].getMinutes();
                            const curTimeStr = String(curHours).padStart(2, '0') + ':' + String(curMins).padStart(2, '0');
                            if (curTimeStr >= '12:00' && curTimeStr < '13:00') {
                                courtCurrentTime[court].setHours(13, 0, 0, 0);
                            }
                        }

                        // Jeda Jumatan (10:30 - 13:00) pada hari 4
                        if (d === 4 && this.scheduleFridayBreak) {
                            const curHours = courtCurrentTime[court].getHours();
                            const curMins = courtCurrentTime[court].getMinutes();
                            const curTimeStr = String(curHours).padStart(2, '0') + ':' + String(curMins).padStart(2, '0');
                            if (courtCounters[court] >= 5 || (curTimeStr >= '10:30' && curTimeStr < '13:00')) {
                                courtCurrentTime[court].setHours(13, 0, 0, 0);
                            }
                        }

                        courtCounters[court] = (courtCounters[court] || 0) + 1;
                        m.match_order = courtCounters[court];

                        const hoursStr = String(courtCurrentTime[court].getHours()).padStart(2, '0');
                        const minsStr = String(courtCurrentTime[court].getMinutes()).padStart(2, '0');
                        m.scheduled_time = hoursStr + ':' + minsStr;

                        courtCurrentTime[court].setMinutes(courtCurrentTime[court].getMinutes() + duration);
                        dayContested++;
                    });

                    days[d].total_matches = dayContested;
                }

                this.previewData.days = days;
            },

            async fetchSchedulePreview() {
                if (this.isLoadingPreview) return;
                this.isLoadingPreview = true;
                this.previewData = null;
                try {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
                    const response = await fetch('{{ route("pic.bracket.preview_schedule", $competition->id) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            scope: this.scheduleScope,
                            pool_key: this.activePoolKey,
                            courts: this.scheduleCourts,
                            start_time: this.scheduleStartTime,
                            end_time: this.scheduleEndTime,
                            match_duration: parseInt(this.scheduleMatchDuration),
                            semifinal_duration: parseInt(this.scheduleSemifinalDuration),
                            tournament_days: parseInt(this.tournamentDays),
                            start_date: this.scheduleStartDate,
                            distribution_mode: this.scheduleDistributionMode,
                            lunch_break: this.scheduleLunchBreak,
                            lunch_start: this.scheduleLunchStart,
                            lunch_end: this.scheduleLunchEnd,
                            friday_break: this.scheduleFridayBreak,
                            custom_rules: this.scheduleCustomRules
                        })
                    });

                    const res = await response.json();
                    if (res.success) {
                        this.previewData = res;
                        this.showPreviewTable = true;
                        this.previewActiveDay = 1;
                    } else {
                        alert(res.message || 'Gagal memuat pratinjau jadwal.');
                    }
                } catch (err) {
                    console.error('Preview error:', err);
                    alert('Terjadi kesalahan saat memuat pratinjau jadwal.');
                } finally {
                    this.isLoadingPreview = false;
                    this.$nextTick(() => {
                        if (window.lucide) window.lucide.createIcons();
                    });
                }
            },

            async executeSyncSchedule() {
                if (this.isSyncing) return;
                this.isSyncing = true;
                this.toastMessage = '';

                try {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
                    const response = await fetch('{{ route("pic.bracket.generate_matches", $competition->id) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            scope: this.scheduleScope,
                            pool_key: this.activePoolKey,
                            courts: this.scheduleCourts,
                            start_time: this.scheduleStartTime,
                            end_time: this.scheduleEndTime,
                            match_duration: parseInt(this.scheduleMatchDuration),
                            semifinal_duration: parseInt(this.scheduleSemifinalDuration),
                            tournament_days: parseInt(this.tournamentDays),
                            start_date: this.scheduleStartDate,
                            distribution_mode: this.scheduleDistributionMode,
                            lunch_break: this.scheduleLunchBreak,
                            lunch_start: this.scheduleLunchStart,
                            lunch_end: this.scheduleLunchEnd,
                            friday_break: this.scheduleFridayBreak,
                            custom_rules: this.scheduleCustomRules,
                            matches: (this.previewData && this.previewData.matches) ? this.previewData.matches : null,
                            summary: (this.previewData && this.previewData.summary) ? this.previewData.summary : null
                        })
                    });

                    const res = await response.json();
                    if (res.success) {
                        this.toastSuccess = true;
                        this.toastMessage = res.message || 'Jadwal pertandingan berhasil disinkronkan ke sistem wasit!';
                        this.closeSyncModal();
                        setTimeout(() => window.location.reload(), 1000);
                    } else {
                        this.toastSuccess = false;
                        this.toastMessage = res.message || 'Gagal menyinkronkan jadwal pertandingan.';
                    }
                } catch (err) {
                    console.error('Sync error:', err);
                    this.toastSuccess = false;
                    this.toastMessage = 'Terjadi kesalahan jaringan saat menyinkronkan data.';
                } finally {
                    this.isSyncing = false;
                    if (window.lucide) window.lucide.createIcons();
                }
            },

            isResetting: false,

            async resetSchedule() {
                if (this.isResetting) return;

                const scopeText = (this.scheduleScope === 'pool') 
                    ? 'kategori lomba ini' 
                    : 'seluruh kategori lomba';
                const msg = `Apakah Anda yakin ingin mengosongkan seluruh jam main, hari, dan nomor lapangan untuk ${scopeText}?\n\nCatatan:\n• Pertandingan yang belum dimulai akan kembali bersih (siap diatur ulang dari nol).\n• Skor pertandingan yang SUDAH SELESAI tetap AMAN 100%.`;

                if (!confirm(msg)) {
                    return;
                }

                this.isResetting = true;
                this.toastMessage = '';

                try {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
                    const response = await fetch('{{ route("pic.bracket.reset_schedule", $competition->id) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            scope: this.scheduleScope,
                            pool_key: this.activePoolKey
                        })
                    });

                    const res = await response.json();
                    if (res.success) {
                        this.toastSuccess = true;
                        this.toastMessage = res.message || 'Jadwal pertandingan berhasil dikosongkan!';
                        this.previewData = null;
                        this.showPreviewTable = false;
                        setTimeout(() => window.location.reload(), 1200);
                    } else {
                        this.toastSuccess = false;
                        this.toastMessage = res.message || 'Gagal mereset jadwal.';
                    }
                } catch (err) {
                    console.error('Reset schedule error:', err);
                    this.toastSuccess = false;
                    this.toastMessage = 'Terjadi kesalahan jaringan saat mereset jadwal.';
                } finally {
                    this.isResetting = false;
                    if (window.lucide) window.lucide.createIcons();
                }
            }
        };
    }
</script>
@endpush
