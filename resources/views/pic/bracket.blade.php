@extends('layouts.admin')

@section('title', 'Bagan Pertandingan - ' . $competition->name)
@section('page_title', 'Bagan Pertandingan (Tournament Bracket)')

@section('content')
<div class="space-y-6 font-sans" x-data="bracketApp()">

    <!-- Top Header Card -->
    <div class="bg-slate-900 rounded-3xl p-5 sm:p-7 border border-slate-800 shadow-xl flex flex-col lg:flex-row lg:items-center justify-between gap-5 text-white">
        <div class="space-y-1.5">
            <div class="flex items-center gap-2 flex-wrap">
                <span class="px-2.5 py-0.5 text-[10px] font-mono font-bold rounded bg-indigo-500/20 text-indigo-400 border border-indigo-500/30 uppercase">
                    SYS_MODULE: TOURNAMENT_BRACKET
                </span>
                <span class="px-2.5 py-0.5 text-[10px] font-mono font-bold rounded bg-cyan-500/20 text-cyan-400 border border-cyan-500/30 uppercase">
                    {{ $competition->category->name }}
                </span>
                @if($bracketData)
                    <span class="px-2.5 py-0.5 text-[10px] font-mono font-bold rounded bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 uppercase">
                        SISTEM GUGUR BWF (BAGAN {{ $bracketData['bracket_size'] }})
                    </span>
                @endif
            </div>
            <h2 class="text-xl sm:text-2xl font-black text-white tracking-tight flex items-center gap-2">
                <span>{{ $competition->name }}</span>
            </h2>
            <p class="text-xs text-slate-400 font-mono flex items-center gap-2 flex-wrap">
                @if($bracketData)
                    <span class="text-indigo-400 font-bold">{{ $bracketData['total_participants'] }} Peserta Terdaftar</span>
                    <span>•</span>
                    <span class="text-amber-400 font-bold">{{ $bracketData['total_byes'] }} Bebas Babak 1 (BYE)</span>
                    <span>•</span>
                    <span class="text-cyan-400 font-bold">{{ $bracketData['total_rounds'] }} Babak Pertandingan</span>
                @else
                    <span>Bagan belum tersedia</span>
                @endif
            </p>
        </div>

        <div class="flex items-center flex-wrap gap-2.5">
            <!-- Sync to Referee Match Schedule -->
            <button type="button" 
                    @click="syncMatches()" 
                    :disabled="isSyncing || !hasRounds"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 text-slate-950 font-black text-xs shadow-lg shadow-emerald-500/20 disabled:opacity-50 disabled:cursor-not-allowed transition cursor-pointer"
                    title="Sinkronkan susunan bagan ke sistem skor wasit bulu tangkis">
                <i data-lucide="refresh-cw" class="w-4 h-4" :class="isSyncing ? 'animate-spin' : ''"></i>
                <span x-text="isSyncing ? 'Menyinkronkan...' : 'Sinkronkan ke Wasit'"></span>
            </button>

            <!-- Public TV View -->
            <a href="{{ route('public.bracket', $competition->slug) }}?pool={{ urlencode($activePoolKey) }}" 
               target="_blank" 
               class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-amber-500/15 hover:bg-amber-500/25 text-amber-300 border border-amber-500/40 font-bold text-xs shadow-sm transition"
               title="Buka tampilan TV & penonton publik">
                <i data-lucide="tv" class="w-4 h-4 text-amber-400"></i>
                <span>Layar TV</span>
            </a>

            <!-- Print PDF Button -->
            <a href="{{ route('pic.bracket.print', $competition->id) }}?pool={{ urlencode($activePoolKey) }}" 
               target="_blank" 
               class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-white/[0.08] hover:bg-white/[0.14] text-slate-200 border border-white/[0.12] font-bold text-xs shadow-sm transition"
               title="Cetak format bagan resmi A4 Landscape">
                <i data-lucide="printer" class="w-4 h-4 text-slate-300"></i>
                <span>Cetak Bagan (A4)</span>
            </a>

            <!-- Draw / Spin Wheel Button -->
            <a href="{{ route('pic.spin.wheel', $competition->id) }}?pool={{ urlencode($activePoolKey) }}" 
               class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 font-bold text-xs transition">
                <i data-lucide="disc" class="w-4 h-4 text-amber-400"></i>
                <span>Undi Peserta</span>
            </a>

            <!-- Back Link -->
            <a href="{{ route('pic.undian') }}" class="px-3.5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white font-bold text-xs transition">
                Kembali
            </a>
        </div>
    </div>

    <!-- Pool / Category Filter Tabs -->
    @if(count($pools) > 1)
    <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-thin">
        @foreach($pools as $p)
            @php $isActive = ($p['key'] === $activePoolKey); @endphp
            <a href="{{ route('pic.bracket', $competition->id) }}?pool={{ urlencode($p['key']) }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl text-xs font-bold transition whitespace-nowrap {{ $isActive ? 'bg-gradient-to-r from-indigo-500 to-blue-600 text-white shadow-lg shadow-indigo-500/25 border border-indigo-400/30' : 'bg-slate-900/80 text-slate-400 hover:text-slate-200 hover:bg-slate-800 border border-slate-800' }}">
                <span>{{ $p['title'] }}</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] {{ $isActive ? 'bg-white/20 text-white' : 'bg-slate-800 text-slate-400' }}">
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

        <!-- Scrollable Bracket Visual Tree Container -->
        <div class="overflow-x-auto pb-8 pt-2 scrollbar-thin">
            <div class="inline-flex gap-8 min-w-full items-stretch px-2 py-4">

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

                                    <!-- Match Competitors List -->
                                    <div class="p-3 space-y-2">
                                        <!-- Team 1 Slot -->
                                        @php
                                            $isT1Winner = false;
                                            if ($match['winner'] && ($t1['id'] ?? null) && ($match['winner']['id'] ?? null) === $t1['id']) {
                                                $isT1Winner = true;
                                            }
                                            if ($isByeAdvance && !($t1['is_bye'] ?? false)) {
                                                $isT1Winner = true;
                                            }
                                        @endphp
                                        <div class="flex items-center justify-between gap-2 p-2 rounded-xl transition
                                            {{ $isT1Winner ? 'bg-emerald-500/15 border border-emerald-500/30' : (($t1['is_bye'] ?? false) ? 'bg-cyan-950/20 border border-cyan-500/20 opacity-70' : 'bg-slate-950/40 border border-transparent') }}">
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
                                                    <div class="text-xs font-bold truncate {{ $isT1Winner ? 'text-emerald-300 font-black' : (($t1['is_bye'] ?? false) ? 'text-cyan-400 italic' : 'text-slate-200') }}">
                                                        {{ $t1['name'] ?? 'Menunggu Pemenang' }}
                                                    </div>
                                                    @if(!empty($t1['institution']))
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
                                            $isT2Winner = false;
                                            if ($match['winner'] && ($t2['id'] ?? null) && ($match['winner']['id'] ?? null) === $t2['id']) {
                                                $isT2Winner = true;
                                            }
                                            if ($isByeAdvance && !($t2['is_bye'] ?? false)) {
                                                $isT2Winner = true;
                                            }
                                        @endphp
                                        <div class="flex items-center justify-between gap-2 p-2 rounded-xl transition
                                            {{ $isT2Winner ? 'bg-emerald-500/15 border border-emerald-500/30' : (($t2['is_bye'] ?? false) ? 'bg-cyan-950/20 border border-cyan-500/20 opacity-70' : 'bg-slate-950/40 border border-transparent') }}">
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
                                                    <div class="text-xs font-bold truncate {{ $isT2Winner ? 'text-emerald-300 font-black' : (($t2['is_bye'] ?? false) ? 'text-cyan-400 italic' : 'text-slate-200') }}">
                                                        {{ $t2['name'] ?? 'Menunggu Pemenang' }}
                                                    </div>
                                                    @if(!empty($t2['institution']))
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
                                            <span class="text-slate-400 font-mono">{{ $existing->court_number }}</span>
                                            <div class="flex items-center gap-1.5">
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

</div>
@endsection

@push('scripts')
<script>
    function bracketApp() {
        return {
            competitionId: {{ $competition->id }},
            activePoolKey: '{{ $activePoolKey }}',
            hasRounds: {{ ($bracketData && !empty($bracketData['rounds'])) ? 'true' : 'false' }},
            isSyncing: false,
            toastMessage: '',
            toastSuccess: true,

            async syncMatches() {
                if (this.isSyncing) return;
                
                if (!confirm('Apakah Anda ingin menyinkronkan seluruh pertandingan babak 1 dan babak berikutnya ke jadwal wasit bulu tangkis?')) {
                    return;
                }

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
                            pool_key: this.activePoolKey
                        })
                    });

                    const res = await response.json();
                    if (res.success) {
                        this.toastSuccess = true;
                        this.toastMessage = res.message || 'Jadwal pertandingan berhasil disinkronkan ke sistem wasit!';
                        setTimeout(() => window.location.reload(), 1200);
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
            }
        };
    }
</script>
@endpush
