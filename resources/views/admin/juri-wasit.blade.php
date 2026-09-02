@extends('layouts.admin')

@section('title', 'Pusat Operasional: Juri, Wasit & Undian')
@section('page_title', 'Juri, Wasit & Undian')

@section('content')
@php
    $drawCategories = $drawCompetitions->pluck('category')->unique()->filter()->values();
@endphp

<div class="space-y-6" x-data="{ 
    activeTab: '{{ $activeTab ?? 'juri' }}',
    createMatchModal: false,
    editMatchModal: false,
    editMatchData: {},
    drawSearchQuery: '',
    drawActiveCat: 'all',
    openEditModal(item) {
        this.editMatchData = JSON.parse(JSON.stringify(item));
        this.editMatchModal = true;
    },
    matchesDraw(comp) {
        const matchesSearch = this.drawSearchQuery === '' || (comp.name + ' ' + comp.code + ' ' + comp.category).toLowerCase().includes(this.drawSearchQuery.toLowerCase());
        const matchesCat = this.drawActiveCat === 'all' || comp.category === this.drawActiveCat;
        return matchesSearch && matchesCat;
    }
}">

    <!-- HERO HEADER BANNER (Dark Glass with Quick TV/Broadcast Action Launchers) -->
    <div class="ai-card rounded-3xl p-6 sm:p-7 border border-white/[0.08] shadow-2xl relative overflow-hidden">
        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div class="space-y-2">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#7A5AF8]/15 text-[#A594FD] border border-[#7A5AF8]/30 text-xs font-bold shadow-xs">
                    <i data-lucide="sparkles" class="w-3.5 h-3.5 text-[#FF58D5]"></i>
                    <span>Pusat Kendali Teknis Hari-H TALENTA 2026</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight font-display">
                    Juri, Wasit & Undian Peserta
                </h1>
                <p class="text-xs sm:text-sm text-slate-400 max-w-2xl leading-relaxed">
                    Satu pintu operasional untuk penilaian kriteria dewan juri, pengaturan wasit bulu tangkis, dan pengundian nomor urut tampil.
                </p>
            </div>

            <!-- Quick TV / Public Broadcast Launch Bar -->
            <div class="flex flex-wrap items-center gap-2.5 shrink-0 bg-[#0C111D]/90 p-2.5 rounded-2xl border border-white/[0.08] shadow-inner">
                <span class="text-[11px] font-bold text-slate-400 px-2 uppercase tracking-wider hidden sm:inline">Siaran TV:</span>
                
                <a href="{{ route('badminton.scoreboard') }}" target="_blank" class="px-3 py-2 rounded-xl bg-white/[0.06] hover:bg-white/[0.12] text-amber-300 hover:text-white text-xs font-bold border border-white/[0.08] flex items-center gap-2 transition group" title="Buka Layar Tunggal TV">
                    <i data-lucide="tv" class="w-4 h-4 text-rose-400 group-hover:scale-110 transition"></i>
                    <span>Papan Skor LED</span>
                </a>

                <a href="{{ route('badminton.arena') }}" target="_blank" class="px-3 py-2 rounded-xl bg-white/[0.06] hover:bg-white/[0.12] text-emerald-300 hover:text-white text-xs font-bold border border-white/[0.08] flex items-center gap-2 transition group" title="Buka Multi-Lapangan TV">
                    <i data-lucide="layout-grid" class="w-4 h-4 text-emerald-400 group-hover:scale-110 transition"></i>
                    <span>Arena Multi-Lap</span>
                </a>

                <a href="{{ route('live.scoreboard') }}" target="_blank" class="px-3 py-2 rounded-xl bg-[#7A5AF8]/20 hover:bg-[#7A5AF8]/30 text-[#C7D2FE] hover:text-white text-xs font-bold border border-[#7A5AF8]/30 flex items-center gap-2 transition group" title="Buka Live Leaderboard Panggung">
                    <i data-lucide="trophy" class="w-4 h-4 text-amber-400 group-hover:scale-110 transition"></i>
                    <span>Live Leaderboard</span>
                </a>
            </div>
        </div>

        <!-- 3 MAIN TABS NAVIGATION -->
        <div class="flex items-center gap-2 mt-6 pt-5 border-t border-white/[0.08] overflow-x-auto scrollbar-none">
            
            <!-- Tab 1: Penilaian Dewan Juri -->
            <button type="button" 
                    @click="activeTab = 'juri'" 
                    class="px-5 py-2.5 rounded-2xl text-xs font-bold transition flex items-center gap-2.5 shrink-0 cursor-pointer"
                    :class="activeTab === 'juri' ? 'bg-gradient-to-r from-[#7A5AF8] to-[#4E6EFF] text-white shadow-lg shadow-[#7A5AF8]/30 font-black' : 'bg-[#0C111D] text-slate-400 hover:text-slate-200 border border-white/[0.08] hover:border-white/[0.15]'">
                <i data-lucide="clipboard-pen" class="w-4 h-4" :class="activeTab === 'juri' ? 'text-white' : 'text-[#7A5AF8]'"></i>
                <span>Penilaian Dewan Juri</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-mono" :class="activeTab === 'juri' ? 'bg-white/20 text-white' : 'bg-white/[0.06] text-slate-400'">
                    {{ $competitionsWithJudging->count() }}
                </span>
            </button>

            <!-- Tab 2: Wasit Bulu Tangkis -->
            <button type="button" 
                    @click="activeTab = 'wasit'" 
                    class="px-5 py-2.5 rounded-2xl text-xs font-bold transition flex items-center gap-2.5 shrink-0 cursor-pointer"
                    :class="activeTab === 'wasit' ? 'bg-gradient-to-r from-[#7A5AF8] to-[#4E6EFF] text-white shadow-lg shadow-[#7A5AF8]/30 font-black' : 'bg-[#0C111D] text-slate-400 hover:text-slate-200 border border-white/[0.08] hover:border-white/[0.15]'">
                <i data-lucide="activity" class="w-4 h-4" :class="activeTab === 'wasit' ? 'text-white' : 'text-emerald-400'"></i>
                <span>Wasit & Skor Bulu Tangkis</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-mono" :class="activeTab === 'wasit' ? 'bg-white/20 text-white' : 'bg-white/[0.06] text-slate-400'">
                    {{ $badmintonMatches->total() }}
                </span>
            </button>

            <!-- Tab 3: Undian Peserta -->
            <button type="button" 
                    @click="activeTab = 'undian'" 
                    class="px-5 py-2.5 rounded-2xl text-xs font-bold transition flex items-center gap-2.5 shrink-0 cursor-pointer"
                    :class="activeTab === 'undian' ? 'bg-gradient-to-r from-[#7A5AF8] to-[#4E6EFF] text-white shadow-lg shadow-[#7A5AF8]/30 font-black' : 'bg-[#0C111D] text-slate-400 hover:text-slate-200 border border-white/[0.08] hover:border-white/[0.15]'">
                <i data-lucide="disc" class="w-4 h-4" :class="activeTab === 'undian' ? 'text-white' : 'text-[#FF58D5]'"></i>
                <span>Undian Nomor Peserta</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-mono" :class="activeTab === 'undian' ? 'bg-white/20 text-white' : 'bg-white/[0.06] text-slate-400'">
                    {{ $totalDrawn }}/{{ $totalVerifiedAll }}
                </span>
            </button>

        </div>
    </div>

    <!-- =========================================================================
         TAB 1: PENILAIAN DEWAN JURI
         ========================================================================= -->
    <div x-show="activeTab === 'juri'" x-transition x-cloak class="space-y-6">
        
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 ai-panel p-4 rounded-2xl border border-white/[0.08]">
            <div class="flex items-center gap-2 text-xs text-slate-300">
                <i data-lucide="info" class="w-4 h-4 text-[#84D0FF]"></i>
                <span>Pilih cabang lomba untuk membuka lembar penilaian digital juri, melihat rincian bobot kriteria, atau memantau progres nilai masuk.</span>
            </div>
            <a href="{{ route('admin.recap') }}" class="px-3.5 py-2 rounded-xl bg-[#7A5AF8]/15 hover:bg-[#7A5AF8]/25 text-[#C7D2FE] hover:text-white text-xs font-bold border border-[#7A5AF8]/30 flex items-center gap-2 shrink-0 transition">
                <i data-lucide="bar-chart-3" class="w-4 h-4 text-amber-400"></i>
                <span>Lihat Rekap Nilai & Juara</span>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @forelse($competitionsWithJudging as $comp)
                @php
                    $scoringPct = $comp['total_verified'] > 0 ? round(($comp['total_scored'] / $comp['total_verified']) * 100) : 0;
                @endphp
                <div class="ai-card rounded-3xl p-6 border border-white/[0.08] hover:border-[#7A5AF8]/50 hover:shadow-xl transition-all duration-300 flex flex-col justify-between space-y-5 group">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between gap-2">
                            <span class="px-2.5 py-1 text-[10px] font-black uppercase tracking-wider rounded-xl bg-[#7A5AF8]/15 text-[#A594FD] border border-[#7A5AF8]/30">
                                {{ $comp['category'] }}
                            </span>
                            <span class="font-mono text-xs font-black text-[#84D0FF] bg-[#4E6EFF]/15 border border-[#4E6EFF]/30 px-2.5 py-0.5 rounded-lg">
                                {{ $comp['code'] }}
                            </span>
                        </div>

                        <div>
                            <h3 class="text-lg font-black text-white group-hover:text-[#84D0FF] transition font-display">{{ $comp['name'] }}</h3>
                            <div class="flex items-center gap-2 mt-1 text-xs text-slate-400">
                                <i data-lucide="map-pin" class="w-3.5 h-3.5 text-slate-500"></i>
                                <span class="truncate">{{ $comp['venue'] ?: 'Lokasi Belum Ditentukan' }}</span>
                            </div>
                        </div>

                        <!-- Progress Penilaian -->
                        <div class="space-y-1.5 pt-2 border-t border-white/[0.06]">
                            <div class="flex items-center justify-between text-[11px] font-bold">
                                <span class="text-slate-400">Progres Nilai Masuk:</span>
                                @if($comp['is_scoring_complete'])
                                    <span class="text-emerald-400 inline-flex items-center gap-1 text-[10px]">
                                        <i data-lucide="check-circle" class="w-3 h-3"></i> Lengkap (100%)
                                    </span>
                                @else
                                    <span class="text-slate-300 font-mono">
                                        {{ $comp['total_scored'] }} / {{ $comp['total_verified'] }} Peserta ({{ $scoringPct }}%)
                                    </span>
                                @endif
                            </div>
                            <div class="w-full h-2 bg-[#0C111D] rounded-full overflow-hidden p-[1px] border border-white/[0.08]">
                                <div class="h-full rounded-full transition-all duration-500 {{ $comp['is_scoring_complete'] ? 'bg-gradient-to-r from-emerald-500 to-teal-400' : 'bg-gradient-to-r from-[#7A5AF8] to-[#4E6EFF]' }}" style="width: {{ $scoringPct }}%"></div>
                            </div>
                        </div>

                        <!-- Criteria tags -->
                        <div class="pt-2">
                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block mb-1.5">Bobot Kriteria:</span>
                            <div class="flex flex-wrap gap-1.5">
                                @forelse($comp['criteria'] as $crit)
                                    <span class="px-2 py-0.5 rounded-lg bg-white/[0.04] border border-white/[0.08] text-[10px] font-semibold text-slate-300">
                                        {{ $crit->name }} ({{ $crit->weight_percentage }}%)
                                    </span>
                                @empty
                                    <span class="text-[10px] text-slate-500 italic">Umum (100%)</span>
                                @endforelse
                            </div>
                        </div>

                        <!-- Live Score Broadcast Setting & Toggle Switch -->
                        <div class="pt-3 border-t border-white/[0.06] flex items-center justify-between gap-3 select-none"
                             x-data="{ 
                                isLive: {{ $comp['is_live_score'] ? 'true' : 'false' }}, 
                                isToggling: false,
                                async toggleLive() {
                                    if (this.isToggling) return;
                                    this.isToggling = true;
                                    const prev = this.isLive;
                                    this.isLive = !this.isLive;

                                    try {
                                        const formData = new FormData();
                                        formData.append('_token', '{{ csrf_token() }}');

                                        const res = await fetch('{{ route('admin.competitions.toggle-live-score', $comp['id']) }}', {
                                            method: 'POST',
                                            headers: {
                                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                'Accept': 'application/json',
                                                'X-Requested-With': 'XMLHttpRequest'
                                            },
                                            body: formData
                                        });
                                        const data = await res.json();
                                        if (data && typeof data.is_live_score !== 'undefined') {
                                            this.isLive = Boolean(data.is_live_score);
                                        } else {
                                            this.isLive = prev;
                                        }
                                    } catch (err) {
                                        console.error('Toggle live score failed:', err);
                                        this.isLive = prev;
                                    } finally {
                                        this.isToggling = false;
                                        this.$nextTick(() => {
                                            if (window.lucide) window.lucide.createIcons();
                                        });
                                    }
                                }
                             }">
                            <div class="flex items-center gap-2 cursor-pointer" @click="toggleLive()">
                                <span class="w-2.5 h-2.5 rounded-full transition-all duration-300" :class="isLive ? 'bg-emerald-400 animate-ping shadow-[0_0_10px_rgba(52,211,153,1)]' : 'bg-slate-600'"></span>
                                <div>
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-xs font-black transition-colors" :class="isLive ? 'text-emerald-400' : 'text-slate-400'" x-text="isLive ? 'Live Score: PUBLIK' : 'Live Score: RAHASIA'"></span>
                                        <template x-if="isLive">
                                            <a href="{{ route('live.scoreboard', $comp['slug']) }}" target="_blank" @click.stop class="text-[10px] text-amber-400 hover:underline" title="Buka Layar Publik">
                                                <i data-lucide="external-link" class="w-3 h-3 inline"></i>
                                            </a>
                                        </template>
                                    </div>
                                    <span class="text-[10px] text-slate-500 block" x-text="isLive ? 'Disiarkan ke layar publik' : 'Tertutup / rahasia'"></span>
                                </div>
                            </div>

                            <!-- Interactive iOS Style Switch Toggle -->
                            <button type="button" 
                                    @click.stop="toggleLive()" 
                                    :disabled="isToggling"
                                    class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none disabled:opacity-50"
                                    :class="isLive ? 'bg-emerald-500 shadow-md shadow-emerald-500/30' : 'bg-slate-800 border-slate-700'"
                                    :title="isLive ? 'Klik untuk merahasiakan Live Score' : 'Klik untuk menyiarkan Live Score ke Publik'">
                                <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-lg ring-0 transition duration-200 ease-in-out"
                                      :class="isLive ? 'translate-x-5' : 'translate-x-0'"></span>
                            </button>
                        </div>
                    </div>

                    <!-- CTA Action -->
                    <div class="pt-3 border-t border-white/[0.08]">
                        <a href="{{ route('juri.scoring', $comp['id']) }}" class="gradient-btn w-full py-2.5 px-4 rounded-xl text-white font-bold text-xs shadow-lg shadow-[#7A5AF8]/20 transition flex items-center justify-center gap-2 group/btn cursor-pointer">
                            <i data-lucide="clipboard-pen" class="w-4 h-4 text-white group-hover/btn:scale-110 transition"></i>
                            <span>Buka Lembar Penilaian Juri</span>
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-12 text-center ai-card rounded-3xl border border-white/[0.08] text-slate-400 text-xs">
                    Belum ada cabang lomba dengan kriteria penilaian dewan juri.
                </div>
            @endforelse
        </div>

    </div>

    <!-- =========================================================================
         TAB 2: WASIT & PERTANDINGAN BULU TANGKIS
         ========================================================================= -->
    <div x-show="activeTab === 'wasit'" x-transition x-cloak class="space-y-6">
        
        <!-- Filter & Add Match Controls -->
        <div class="ai-panel p-4 rounded-2xl border border-white/[0.08] flex flex-col md:flex-row md:items-center justify-between gap-4 shadow-lg">
            <form action="{{ route('admin.juri.wasit') }}" method="GET" class="flex flex-wrap items-center gap-2">
                <input type="hidden" name="tab" value="wasit">
                
                <select name="court" onchange="this.form.submit()" class="bg-[#0C111D] border border-white/[0.12] text-slate-200 rounded-xl px-3 py-2 text-xs font-medium outline-none">
                    <option value="">Semua Lapangan</option>
                    @foreach($badmintonCourts as $c)
                        <option value="{{ $c }}" {{ request('court') == $c ? 'selected' : '' }}>{{ $c }}</option>
                    @endforeach
                </select>

                <select name="category" onchange="this.form.submit()" class="bg-[#0C111D] border border-white/[0.12] text-slate-200 rounded-xl px-3 py-2 text-xs font-medium outline-none">
                    <option value="">Semua Sektor</option>
                    <option value="MS" {{ request('category') == 'MS' ? 'selected' : '' }}>MS - Tunggal Putra</option>
                    <option value="WS" {{ request('category') == 'WS' ? 'selected' : '' }}>WS - Tunggal Putri</option>
                    <option value="MD" {{ request('category') == 'MD' ? 'selected' : '' }}>MD - Ganda Putra</option>
                    <option value="WD" {{ request('category') == 'WD' ? 'selected' : '' }}>WD - Ganda Putri</option>
                    <option value="XD" {{ request('category') == 'XD' ? 'selected' : '' }}>XD - Ganda Campuran</option>
                </select>

                <select name="match_status" onchange="this.form.submit()" class="bg-[#0C111D] border border-white/[0.12] text-slate-200 rounded-xl px-3 py-2 text-xs font-medium outline-none">
                    <option value="">Semua Status</option>
                    <option value="upcoming" {{ request('match_status') == 'upcoming' ? 'selected' : '' }}>Belum Dimulai</option>
                    <option value="ongoing" {{ request('match_status') == 'ongoing' ? 'selected' : '' }}>Sedang Berlangsung (LIVE)</option>
                    <option value="finished" {{ request('match_status') == 'finished' ? 'selected' : '' }}>Selesai</option>
                </select>
            </form>

            <button @click="createMatchModal = true" type="button" class="gradient-btn px-4 py-2.5 rounded-xl text-white text-xs font-bold shadow-lg shadow-[#7A5AF8]/25 flex items-center gap-2 shrink-0 cursor-pointer">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                <span>Buat Pertandingan Baru</span>
            </button>
        </div>

        <!-- Matches Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @forelse($badmintonMatches as $match)
            <div class="ai-card rounded-3xl p-5 border border-white/[0.08] hover:border-[#7A5AF8]/40 hover:shadow-xl transition flex flex-col justify-between space-y-4">
                
                <!-- Card Header -->
                <div class="flex items-center justify-between border-b border-white/[0.08] pb-3 text-xs">
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded-md font-mono font-bold bg-[#0C111D] text-amber-400 border border-amber-400/30 text-[11px]">{{ $match->category }}</span>
                        <span class="font-bold text-white">{{ $match->court_number }}</span>
                    </div>
                    <div>
                        @if($match->match_status === 'ongoing')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-ping"></span>
                                LIVE (Set {{ $match->current_set }})
                            </span>
                        @elseif($match->match_status === 'finished')
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-white/[0.06] text-slate-400 border border-white/[0.08]">
                                Selesai
                            </span>
                        @else
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/15 text-amber-400 border border-amber-500/30">
                                Belum Mulai
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Teams & Live Scores -->
                <div class="space-y-2.5 my-1">
                    <!-- Team 1 -->
                    <div class="p-3 rounded-2xl {{ $match->winner_team == 1 ? 'bg-emerald-500/15 border border-emerald-500/30' : 'bg-[#0C111D]/80 border border-white/[0.06]' }} flex items-center justify-between gap-3">
                        <div class="overflow-hidden flex-1">
                            <span class="text-[10px] font-bold text-amber-400 uppercase tracking-wider block truncate">
                                🏫 {{ $match->team1_school }}
                            </span>
                            <p class="text-xs font-bold text-white truncate mt-0.5">
                                {{ $match->team1_player1 }} {{ $match->team1_player2 ? '/ ' . $match->team1_player2 : '' }}
                            </p>
                        </div>
                        <div class="flex items-center gap-1 font-mono font-black text-xs shrink-0">
                            <span class="w-6 h-6 rounded-lg bg-black text-lime-400 flex items-center justify-center border border-white/[0.1]">{{ $match->team1_set1 }}</span>
                            <span class="w-6 h-6 rounded-lg bg-black text-cyan-400 flex items-center justify-center border border-white/[0.1]">{{ $match->team1_set2 }}</span>
                            @if($match->current_set == 3 || $match->team1_set3 > 0 || $match->team2_set3 > 0)
                                <span class="w-6 h-6 rounded-lg bg-black text-cyan-400 flex items-center justify-center border border-white/[0.1]">{{ $match->team1_set3 }}</span>
                            @endif
                        </div>
                    </div>

                    <!-- Team 2 -->
                    <div class="p-3 rounded-2xl {{ $match->winner_team == 2 ? 'bg-emerald-500/15 border border-emerald-500/30' : 'bg-[#0C111D]/80 border border-white/[0.06]' }} flex items-center justify-between gap-3">
                        <div class="overflow-hidden flex-1">
                            <span class="text-[10px] font-bold text-cyan-400 uppercase tracking-wider block truncate">
                                🏫 {{ $match->team2_school }}
                            </span>
                            <p class="text-xs font-bold text-white truncate mt-0.5">
                                {{ $match->team2_player1 }} {{ $match->team2_player2 ? '/ ' . $match->team2_player2 : '' }}
                            </p>
                        </div>
                        <div class="flex items-center gap-1 font-mono font-black text-xs shrink-0">
                            <span class="w-6 h-6 rounded-lg bg-black text-lime-400 flex items-center justify-center border border-white/[0.1]">{{ $match->team2_set1 }}</span>
                            <span class="w-6 h-6 rounded-lg bg-black text-cyan-400 flex items-center justify-center border border-white/[0.1]">{{ $match->team2_set2 }}</span>
                            @if($match->current_set == 3 || $match->team1_set3 > 0 || $match->team2_set3 > 0)
                                <span class="w-6 h-6 rounded-lg bg-black text-cyan-400 flex items-center justify-center border border-white/[0.1]">{{ $match->team2_set3 }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Footer & Actions -->
                <div class="flex items-center justify-between pt-3 border-t border-white/[0.08] text-xs">
                    <span class="text-slate-400 text-[11px] font-medium truncate max-w-[120px]">
                        {{ $match->round_name }}
                    </span>

                    <div class="flex items-center gap-1.5">
                        <a href="{{ route('badminton.umpire', $match->id) }}" class="px-3 py-1.5 rounded-xl bg-emerald-500/15 hover:bg-emerald-500/25 text-emerald-400 hover:text-emerald-300 font-bold text-xs border border-emerald-500/30 flex items-center gap-1 transition cursor-pointer">
                            <i data-lucide="play" class="w-3.5 h-3.5"></i>
                            <span>Wasit</span>
                        </a>

                        <button @click="openEditModal({{ json_encode($match) }})" type="button" class="p-1.5 rounded-xl bg-white/[0.06] hover:bg-white/[0.12] text-slate-300 hover:text-white transition" title="Edit Pertandingan">
                            <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                        </button>

                        <form action="{{ route('badminton.destroy', $match->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pertandingan ini?')">
                            @csrf
                            <button type="submit" class="p-1.5 rounded-xl bg-white/[0.06] hover:bg-rose-500/20 text-slate-400 hover:text-rose-400 transition" title="Hapus Pertandingan">
                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                            </button>
                        </form>
                    </div>
                </div>

            </div>
            @empty
            <div class="col-span-full py-12 text-center ai-card rounded-3xl border border-white/[0.08] text-slate-400 text-xs">
                Belum ada jadwal pertandingan bulu tangkis yang dibuat.
            </div>
            @endforelse
        </div>

        @if($badmintonMatches->hasPages())
        <div class="pt-4">
            {{ $badmintonMatches->links() }}
        </div>
        @endif

    </div>

    <!-- =========================================================================
         TAB 3: UNDIAN NOMOR PESERTA
         ========================================================================= -->
    <div x-show="activeTab === 'undian'" x-transition x-cloak class="space-y-6">
        
        <!-- Category Filter Tabs & Search Controls -->
        <div class="space-y-3">
            <!-- Interactive Category Pills -->
            <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-none">
                <button type="button" 
                        @click="drawActiveCat = 'all'" 
                        class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 shrink-0 cursor-pointer"
                        :class="drawActiveCat === 'all' ? 'bg-gradient-to-r from-[#7A5AF8] to-[#4E6EFF] text-white shadow-lg shadow-[#7A5AF8]/30 font-black' : 'bg-[#161F30] text-slate-400 hover:text-white border border-white/[0.08] hover:border-white/[0.2]'">
                    <i data-lucide="layout-grid" class="w-3.5 h-3.5"></i>
                    <span>Semua Kategori</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-white/20 text-white font-mono">{{ $drawCompetitions->count() }}</span>
                </button>

                @foreach($drawCategories as $cat)
                    @php
                        $catCount = $drawCompetitions->where('category', $cat)->count();
                    @endphp
                    <button type="button" 
                            @click="drawActiveCat = '{{ $cat }}'" 
                            class="px-3.5 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 shrink-0 cursor-pointer"
                            :class="drawActiveCat === '{{ $cat }}' ? 'bg-gradient-to-r from-[#7A5AF8] to-[#4E6EFF] text-white shadow-lg shadow-[#7A5AF8]/30 font-black' : 'bg-[#161F30] text-slate-400 hover:text-white border border-white/[0.08] hover:border-white/[0.2]'">
                        <span>{{ $cat }}</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-white/10 text-slate-300 font-mono">{{ $catCount }}</span>
                    </button>
                @endforeach
            </div>

            <!-- Search Bar with Live Indicator -->
            <div class="ai-panel rounded-2xl p-3 border border-white/[0.08] flex flex-col sm:flex-row items-center justify-between gap-3 shadow-lg">
                <div class="relative flex-1 w-full">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                    <input type="text" 
                           x-model="drawSearchQuery" 
                           placeholder="Cari nama cabang lomba, kode (MTQ, MIPA, dll), atau kategori..." 
                           class="w-full pl-10 pr-10 py-2.5 text-xs sm:text-sm rounded-xl bg-[#0C111D] border border-white/[0.12] text-white placeholder-slate-500 focus:border-[#7A5AF8] focus:ring-2 focus:ring-[#7A5AF8]/25 outline-none transition">
                    <button x-show="drawSearchQuery.length > 0" @click="drawSearchQuery = ''" type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-white">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
                <div class="flex items-center gap-2 shrink-0 text-xs font-bold text-slate-400 px-2">
                    <span class="w-2 h-2 rounded-full bg-[#4E6EFF] animate-pulse"></span>
                    <span>Total: <strong class="text-white">{{ $drawCompetitions->count() }}</strong> Cabang Terdaftar</span>
                </div>
            </div>
        </div>

        <!-- Competition Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @forelse($drawCompetitions as $comp)
                @php
                    $pct = $comp['total_verified'] > 0 ? round(($comp['drawn_count'] / $comp['total_verified']) * 100) : 0;
                @endphp
                <div class="ai-card rounded-3xl p-5 sm:p-6 border border-white/[0.08] hover:border-[#7A5AF8]/50 hover:shadow-2xl hover:shadow-[#7A5AF8]/15 transition-all duration-300 flex flex-col justify-between space-y-5 group relative overflow-hidden"
                     x-show="matchesDraw({{ json_encode($comp) }})"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100">
                    
                    <div class="space-y-4 relative z-10">
                        <!-- Top Category & Code Badges -->
                        <div class="flex items-center justify-between gap-2">
                            <span class="px-2.5 py-1 text-[10px] font-black uppercase tracking-wider rounded-xl bg-[#7A5AF8]/15 text-[#A594FD] border border-[#7A5AF8]/30 shadow-xs">
                                {{ $comp['category'] }}
                            </span>
                            <span class="font-mono text-xs font-black text-[#84D0FF] bg-[#4E6EFF]/15 border border-[#4E6EFF]/30 px-2.5 py-0.5 rounded-lg">
                                {{ $comp['code'] }}
                            </span>
                        </div>

                        <!-- Competition Title & Stats -->
                        <div>
                            <h3 class="text-lg font-black text-white group-hover:text-[#84D0FF] transition-colors leading-snug font-display">
                                {{ $comp['name'] }}
                            </h3>
                            <div class="flex items-center gap-2 mt-1.5 text-xs text-slate-400">
                                <i data-lucide="users" class="w-3.5 h-3.5 text-[#7A5AF8]"></i>
                                <span><strong class="text-white font-mono">{{ $comp['total_verified'] }}</strong> Peserta Terverifikasi</span>
                            </div>
                        </div>

                        <!-- Progress Bar -->
                        <div class="space-y-2 pt-2 border-t border-white/[0.06]">
                            <div class="flex items-center justify-between text-[11px] font-bold">
                                <span class="text-slate-400">Progres Pengundian:</span>
                                @if($comp['is_complete'])
                                    <span class="inline-flex items-center gap-1 text-emerald-400 bg-emerald-500/15 px-2 py-0.5 rounded-lg border border-emerald-500/30 text-[10px]">
                                        <i data-lucide="check-circle" class="w-3 h-3"></i> Tuntas (100%)
                                    </span>
                                @else
                                    <span class="text-slate-300 font-mono">
                                        {{ $comp['drawn_count'] }} / {{ $comp['total_verified'] }} <span class="text-amber-400">({{ $pct }}%)</span>
                                    </span>
                                @endif
                            </div>
                            <div class="w-full h-2.5 bg-[#0C111D] rounded-full overflow-hidden p-[1px] border border-white/[0.08]">
                                <div class="h-full rounded-full transition-all duration-700 {{ $comp['is_complete'] ? 'bg-gradient-to-r from-emerald-500 to-teal-400 shadow-[0_0_10px_rgba(16,185,129,0.5)]' : ($comp['drawn_count'] > 0 ? 'bg-gradient-to-r from-amber-500 to-amber-300 shadow-[0_0_10px_rgba(245,158,11,0.4)]' : 'bg-white/10') }}" 
                                     style="width: {{ $pct }}%"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Launchers -->
                    <div class="space-y-2 pt-3 border-t border-white/[0.08] relative z-10">
                        <!-- Primary: Hacker Scramble Animation Button -->
                        <a href="{{ route('pic.hacker.draw', $comp['id']) }}" class="w-full py-2.5 px-4 rounded-xl gradient-btn font-bold text-xs flex items-center justify-center gap-2 shadow-lg shadow-[#7A5AF8]/20 transition group/btn cursor-pointer">
                            <i data-lucide="terminal" class="w-4 h-4 text-emerald-300 group-hover/btn:scale-110 transition-transform"></i>
                            <span>Undi Mode Hacker (Live Decoder)</span>
                        </a>

                        <!-- Secondary Actions Grid (Spin Wheel & Public TV) -->
                        <div class="grid grid-cols-2 gap-2">
                            <!-- Spin Wheel Button -->
                            <a href="{{ route('pic.spin.wheel', $comp['id']) }}" class="py-2 px-3 rounded-xl bg-amber-500/10 hover:bg-amber-500/20 text-amber-300 border border-amber-500/30 hover:border-amber-400/60 font-bold text-xs flex items-center justify-center gap-1.5 transition cursor-pointer">
                                <i data-lucide="disc" class="w-3.5 h-3.5 text-amber-400"></i>
                                <span>Spin Wheel</span>
                            </a>

                            <!-- Public Screen Viewer -->
                            <a href="{{ route('spin.viewer', $comp['slug']) }}" target="_blank" class="py-2 px-3 rounded-xl bg-white/[0.06] hover:bg-white/[0.12] text-slate-300 hover:text-white border border-white/[0.10] hover:border-white/[0.25] font-bold text-xs flex items-center justify-center gap-1.5 transition cursor-pointer">
                                <i data-lucide="tv" class="w-3.5 h-3.5 text-[#84D0FF]"></i>
                                <span>Layar Publik</span>
                            </a>
                        </div>
                    </div>

                </div>
            @empty
                <div class="col-span-full py-16 text-center ai-card rounded-3xl border border-white/[0.08]">
                    <div class="w-14 h-14 rounded-2xl bg-white/[0.06] flex items-center justify-center mx-auto text-slate-400 mb-3">
                        <i data-lucide="inbox" class="w-7 h-7"></i>
                    </div>
                    <h4 class="text-white font-bold text-sm">Tidak ada cabang lomba</h4>
                    <p class="text-xs text-slate-400 mt-1">Belum ada cabang lomba yang terdaftar dalam sistem.</p>
                </div>
            @endforelse
        </div>

    </div>

    <!-- =========================================================================
         MODAL CREATE BADMINTON MATCH
         ========================================================================= -->
    <div x-show="createMatchModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-md overflow-y-auto">
        <div @click.away="createMatchModal = false" class="ai-card rounded-3xl w-full max-w-2xl border border-white/[0.12] shadow-2xl p-6 sm:p-8 space-y-6 my-8">
            <div class="flex items-center justify-between border-b border-white/[0.08] pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-[#7A5AF8] to-[#4E6EFF] text-white flex items-center justify-center font-bold shadow-lg shadow-[#7A5AF8]/30">
                        <i data-lucide="plus-circle" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-white font-display">Buat Pertandingan Bulu Tangkis Baru</h3>
                        <p class="text-xs text-slate-400">Jadwalkan partai pertandingan baru dan tetapkan nomor lapangan.</p>
                    </div>
                </div>
                <button @click="createMatchModal = false" class="text-slate-400 hover:text-white p-1 rounded-xl">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="{{ route('badminton.store') }}" method="POST" class="space-y-4 text-xs font-bold">
                @csrf
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-slate-300 mb-1.5">Cabang Lomba Induk</label>
                        <select name="competition_id" class="w-full px-3.5 py-2.5 rounded-xl bg-[#0C111D] border border-white/[0.12] text-white outline-none">
                            <option value="">Pilih Cabang (Opsional)</option>
                            @foreach($badmintonCompetitions as $c)
                                <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-slate-300 mb-1.5">Nomor / Nama Lapangan <span class="text-rose-400">*</span></label>
                        <input type="text" name="court_number" placeholder="Contoh: Lapangan 1" required class="w-full px-3.5 py-2.5 rounded-xl bg-[#0C111D] border border-white/[0.12] text-white outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-slate-300 mb-1.5">Babak Pertandingan <span class="text-rose-400">*</span></label>
                        <input type="text" name="round_name" placeholder="Contoh: Babak Penyisihan / Final" required class="w-full px-3.5 py-2.5 rounded-xl bg-[#0C111D] border border-white/[0.12] text-white outline-none">
                    </div>
                    <div>
                        <label class="block text-slate-300 mb-1.5">Sektor Tanding <span class="text-rose-400">*</span></label>
                        <select name="category" required class="w-full px-3.5 py-2.5 rounded-xl bg-[#0C111D] border border-white/[0.12] text-white outline-none">
                            <option value="MS">MS - Tunggal Putra</option>
                            <option value="WS">WS - Tunggal Putri</option>
                            <option value="MD">MD - Ganda Putra</option>
                            <option value="WD">WD - Ganda Putri</option>
                            <option value="XD">XD - Ganda Campuran</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-slate-300 mb-1.5">Tipe Partai <span class="text-rose-400">*</span></label>
                        <select name="match_type" required class="w-full px-3.5 py-2.5 rounded-xl bg-[#0C111D] border border-white/[0.12] text-white outline-none">
                            <option value="single">Single (Tunggal)</option>
                            <option value="double">Double (Ganda)</option>
                        </select>
                    </div>
                </div>

                <!-- Tim 1 -->
                <div class="p-4 rounded-2xl bg-[#0C111D] border border-white/[0.08] space-y-3">
                    <span class="text-[11px] font-black text-amber-400 uppercase tracking-wider block">🏸 DATA TIM / PEMAIN 1</span>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-slate-400 mb-1 text-[11px]">Asal Sekolah / Kontingen</label>
                            <input type="text" name="team1_school" placeholder="Contoh: SMPN 1 Blitar" required class="w-full px-3 py-2 rounded-xl bg-[#161F30] border border-white/[0.1] text-white">
                        </div>
                        <div>
                            <label class="block text-slate-400 mb-1 text-[11px]">Nama Pemain 1</label>
                            <input type="text" name="team1_player1" placeholder="Nama Atlet 1" required class="w-full px-3 py-2 rounded-xl bg-[#161F30] border border-white/[0.1] text-white">
                        </div>
                        <div>
                            <label class="block text-slate-400 mb-1 text-[11px]">Nama Pemain 2 (Jika Ganda)</label>
                            <input type="text" name="team1_player2" placeholder="Nama Atlet 2" class="w-full px-3 py-2 rounded-xl bg-[#161F30] border border-white/[0.1] text-white">
                        </div>
                    </div>
                </div>

                <!-- Tim 2 -->
                <div class="p-4 rounded-2xl bg-[#0C111D] border border-white/[0.08] space-y-3">
                    <span class="text-[11px] font-black text-cyan-400 uppercase tracking-wider block">🏸 DATA TIM / PEMAIN 2</span>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-slate-400 mb-1 text-[11px]">Asal Sekolah / Kontingen</label>
                            <input type="text" name="team2_school" placeholder="Contoh: MTsN 1 Blitar" required class="w-full px-3 py-2 rounded-xl bg-[#161F30] border border-white/[0.1] text-white">
                        </div>
                        <div>
                            <label class="block text-slate-400 mb-1 text-[11px]">Nama Pemain 1</label>
                            <input type="text" name="team2_player1" placeholder="Nama Atlet 1" required class="w-full px-3 py-2 rounded-xl bg-[#161F30] border border-white/[0.1] text-white">
                        </div>
                        <div>
                            <label class="block text-slate-400 mb-1 text-[11px]">Nama Pemain 2 (Jika Ganda)</label>
                            <input type="text" name="team2_player2" placeholder="Nama Atlet 2" class="w-full px-3 py-2 rounded-xl bg-[#161F30] border border-white/[0.1] text-white">
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-white/[0.08] flex items-center justify-end gap-3">
                    <button type="button" @click="createMatchModal = false" class="px-5 py-2.5 rounded-xl bg-white/[0.06] hover:bg-white/[0.1] text-slate-300 font-bold transition cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" class="gradient-btn px-6 py-2.5 rounded-xl text-white font-bold shadow-lg shadow-[#7A5AF8]/30 transition cursor-pointer">
                        Simpan Pertandingan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- =========================================================================
         MODAL EDIT BADMINTON MATCH
         ========================================================================= -->
    <div x-show="editMatchModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-md overflow-y-auto">
        <div @click.away="editMatchModal = false" class="ai-card rounded-3xl w-full max-w-2xl border border-white/[0.12] shadow-2xl p-6 sm:p-8 space-y-6 my-8">
            <div class="flex items-center justify-between border-b border-white/[0.08] pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-[#7A5AF8] to-[#4E6EFF] text-white flex items-center justify-center font-bold shadow-lg shadow-[#7A5AF8]/30">
                        <i data-lucide="edit-3" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-white font-display">Edit Pertandingan Bulu Tangkis</h3>
                        <p class="text-xs text-slate-400">Perbarui data partai atau ganti nomor lapangan.</p>
                    </div>
                </div>
                <button @click="editMatchModal = false" class="text-slate-400 hover:text-white p-1 rounded-xl">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form :action="'{{ url('badminton/matches') }}/' + (editMatchData ? editMatchData.id : '') + '/update'" method="POST" class="space-y-4 text-xs font-bold">
                @csrf
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-slate-300 mb-1.5">Cabang Lomba Induk</label>
                        <select name="competition_id" x-model="editMatchData.competition_id" class="w-full px-3.5 py-2.5 rounded-xl bg-[#0C111D] border border-white/[0.12] text-white outline-none">
                            <option value="">Pilih Cabang (Opsional)</option>
                            @foreach($badmintonCompetitions as $c)
                                <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-slate-300 mb-1.5">Nomor / Nama Lapangan <span class="text-rose-400">*</span></label>
                        <input type="text" name="court_number" x-model="editMatchData.court_number" required class="w-full px-3.5 py-2.5 rounded-xl bg-[#0C111D] border border-white/[0.12] text-white outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-slate-300 mb-1.5">Babak Pertandingan <span class="text-rose-400">*</span></label>
                        <input type="text" name="round_name" x-model="editMatchData.round_name" required class="w-full px-3.5 py-2.5 rounded-xl bg-[#0C111D] border border-white/[0.12] text-white outline-none">
                    </div>
                    <div>
                        <label class="block text-slate-300 mb-1.5">Sektor Tanding <span class="text-rose-400">*</span></label>
                        <select name="category" x-model="editMatchData.category" required class="w-full px-3.5 py-2.5 rounded-xl bg-[#0C111D] border border-white/[0.12] text-white outline-none">
                            <option value="MS">MS - Tunggal Putra</option>
                            <option value="WS">WS - Tunggal Putri</option>
                            <option value="MD">MD - Ganda Putra</option>
                            <option value="WD">WD - Ganda Putri</option>
                            <option value="XD">XD - Ganda Campuran</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-slate-300 mb-1.5">Tipe Partai <span class="text-rose-400">*</span></label>
                        <select name="match_type" x-model="editMatchData.match_type" required class="w-full px-3.5 py-2.5 rounded-xl bg-[#0C111D] border border-white/[0.12] text-white outline-none">
                            <option value="single">Single (Tunggal)</option>
                            <option value="double">Double (Ganda)</option>
                        </select>
                    </div>
                </div>

                <!-- Tim 1 -->
                <div class="p-4 rounded-2xl bg-[#0C111D] border border-white/[0.08] space-y-3">
                    <span class="text-[11px] font-black text-amber-400 uppercase tracking-wider block">🏸 DATA TIM / PEMAIN 1</span>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-slate-400 mb-1 text-[11px]">Asal Sekolah</label>
                            <input type="text" name="team1_school" x-model="editMatchData.team1_school" required class="w-full px-3 py-2 rounded-xl bg-[#161F30] border border-white/[0.1] text-white">
                        </div>
                        <div>
                            <label class="block text-slate-400 mb-1 text-[11px]">Nama Pemain 1</label>
                            <input type="text" name="team1_player1" x-model="editMatchData.team1_player1" required class="w-full px-3 py-2 rounded-xl bg-[#161F30] border border-white/[0.1] text-white">
                        </div>
                        <div>
                            <label class="block text-slate-400 mb-1 text-[11px]">Nama Pemain 2</label>
                            <input type="text" name="team1_player2" x-model="editMatchData.team1_player2" class="w-full px-3 py-2 rounded-xl bg-[#161F30] border border-white/[0.1] text-white">
                        </div>
                    </div>
                </div>

                <!-- Tim 2 -->
                <div class="p-4 rounded-2xl bg-[#0C111D] border border-white/[0.08] space-y-3">
                    <span class="text-[11px] font-black text-cyan-400 uppercase tracking-wider block">🏸 DATA TIM / PEMAIN 2</span>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-slate-400 mb-1 text-[11px]">Asal Sekolah</label>
                            <input type="text" name="team2_school" x-model="editMatchData.team2_school" required class="w-full px-3 py-2 rounded-xl bg-[#161F30] border border-white/[0.1] text-white">
                        </div>
                        <div>
                            <label class="block text-slate-400 mb-1 text-[11px]">Nama Pemain 1</label>
                            <input type="text" name="team2_player1" x-model="editMatchData.team2_player1" required class="w-full px-3 py-2 rounded-xl bg-[#161F30] border border-white/[0.1] text-white">
                        </div>
                        <div>
                            <label class="block text-slate-400 mb-1 text-[11px]">Nama Pemain 2</label>
                            <input type="text" name="team2_player2" x-model="editMatchData.team2_player2" class="w-full px-3 py-2 rounded-xl bg-[#161F30] border border-white/[0.1] text-white">
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-white/[0.08] flex items-center justify-end gap-3">
                    <button type="button" @click="editMatchModal = false" class="px-5 py-2.5 rounded-xl bg-white/[0.06] hover:bg-white/[0.1] text-slate-300 font-bold transition cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" class="gradient-btn px-6 py-2.5 rounded-xl text-white font-bold shadow-lg shadow-[#7A5AF8]/30 transition cursor-pointer">
                        Perbarui Pertandingan
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection