@extends('layouts.admin')

@section('title', 'Undi Peserta Cabang Lomba')
@section('page_title', 'Undi Peserta')

@section('content')
@php
    $categories = $competitions->pluck('category')->unique()->filter()->values();
@endphp

<div class="space-y-6" x-data="{ 
    searchQuery: '', 
    activeCategory: 'all',
    matches(comp) {
        const matchesSearch = this.searchQuery === '' || (comp.name + ' ' + comp.code + ' ' + comp.category).toLowerCase().includes(this.searchQuery.toLowerCase());
        const matchesCat = this.activeCategory === 'all' || comp.category === this.activeCategory;
        return matchesSearch && matchesCat;
    }
}">

    <!-- Header Banner (AI Starter Kit Dark Glass) -->
    <div class="ai-card rounded-3xl p-6 sm:p-8 border border-white/[0.08] relative overflow-hidden shadow-2xl">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-2.5">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#7A5AF8]/15 text-[#A594FD] border border-[#7A5AF8]/30 text-xs font-bold shadow-xs">
                    <i data-lucide="sparkles" class="w-3.5 h-3.5 text-[#FF58D5]"></i>
                    <span>Modul Pengacakan & Undian Transparan</span>
                </div>
                <h2 class="text-2xl sm:text-3xl font-black tracking-tight text-white font-display">Undi Peserta Cabang Lomba</h2>
                <p class="text-xs sm:text-sm text-slate-400 max-w-2xl leading-relaxed">
                    Pilih cabang lomba untuk memulai pengundian nomor urut tampil peserta secara transparan. Tersedia animasi pengacakan <span class="text-[#84D0FF] font-bold">Hacker Matrix Live Decoder</span> dan <span class="text-amber-400 font-bold">Spin Wheel Interaktif</span>.
                </p>
            </div>

            <!-- Stats Counter Pill -->
            <div class="grid grid-cols-3 gap-2.5 sm:gap-4 shrink-0 ai-panel p-3.5 sm:p-4 rounded-2xl border border-white/[0.08] shadow-inner">
                <div class="text-center px-2">
                    <div class="text-xl sm:text-2xl font-black text-white font-mono">{{ $totalVerifiedAll }}</div>
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-0.5">Peserta Sah</div>
                </div>
                <div class="text-center px-2 border-x border-white/[0.08]">
                    <div class="text-xl sm:text-2xl font-black text-emerald-400 font-mono">{{ $totalDrawn }}</div>
                    <div class="text-[10px] font-bold text-emerald-400/90 uppercase tracking-wider mt-0.5">Sudah Diundi</div>
                </div>
                <div class="text-center px-2">
                    <div class="text-xl sm:text-2xl font-black text-amber-400 font-mono">{{ $totalUndrawn }}</div>
                    <div class="text-[10px] font-bold text-amber-400/90 uppercase tracking-wider mt-0.5">Belum Diundi</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Category Filter Tabs & Search Controls -->
    <div class="space-y-3">
        <!-- Interactive Category Pills -->
        <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-none">
            <button type="button" 
                    @click="activeCategory = 'all'" 
                    class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 shrink-0 cursor-pointer"
                    :class="activeCategory === 'all' ? 'bg-gradient-to-r from-[#7A5AF8] to-[#4E6EFF] text-white shadow-lg shadow-[#7A5AF8]/30 font-black' : 'bg-[#161F30] text-slate-400 hover:text-white border border-white/[0.08] hover:border-white/[0.2]'">
                <i data-lucide="layout-grid" class="w-3.5 h-3.5"></i>
                <span>Semua Kategori</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-white/20 text-white font-mono">{{ $competitions->count() }}</span>
            </button>

            @foreach($categories as $cat)
                @php
                    $catCount = $competitions->where('category', $cat)->count();
                @endphp
                <button type="button" 
                        @click="activeCategory = '{{ $cat }}'" 
                        class="px-3.5 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 shrink-0 cursor-pointer"
                        :class="activeCategory === '{{ $cat }}' ? 'bg-gradient-to-r from-[#7A5AF8] to-[#4E6EFF] text-white shadow-lg shadow-[#7A5AF8]/30 font-black' : 'bg-[#161F30] text-slate-400 hover:text-white border border-white/[0.08] hover:border-white/[0.2]'">
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
                       x-model="searchQuery" 
                       placeholder="Cari nama cabang lomba, kode (MTQ, MIPA, dll), atau kategori..." 
                       class="w-full pl-10 pr-10 py-2.5 text-xs sm:text-sm rounded-xl bg-[#0C111D] border border-white/[0.12] text-white placeholder-slate-500 focus:border-[#7A5AF8] focus:ring-2 focus:ring-[#7A5AF8]/25 outline-none transition">
                <button x-show="searchQuery.length > 0" @click="searchQuery = ''" type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-white">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
            <div class="flex items-center gap-2 shrink-0 text-xs font-bold text-slate-400 px-2">
                <span class="w-2 h-2 rounded-full bg-[#4E6EFF] animate-pulse"></span>
                <span>Total: <strong class="text-white">{{ $competitions->count() }}</strong> Cabang Terdaftar</span>
            </div>
        </div>
    </div>

    <!-- Competition Cards Grid (Modern Dark Glass Scheme) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($competitions as $comp)
            @php
                $pct = $comp['total_verified'] > 0 ? round(($comp['drawn_count'] / $comp['total_verified']) * 100) : 0;
            @endphp
            <div class="ai-card rounded-3xl p-5 sm:p-6 border border-white/[0.08] hover:border-[#7A5AF8]/50 hover:shadow-2xl hover:shadow-[#7A5AF8]/15 transition-all duration-300 flex flex-col justify-between space-y-5 group relative overflow-hidden"
                 x-show="matches({{ json_encode($comp) }})"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100">
                
                <!-- Ambient Subtle Top Glow -->
                <div class="absolute -top-12 -right-12 w-28 h-28 bg-[#7A5AF8]/10 rounded-full blur-2xl pointer-events-none group-hover:bg-[#7A5AF8]/20 transition-all duration-500"></div>

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

                    <!-- Progress Bar with Glowing Pill -->
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

                <!-- Action Launchers (Clean & Structured) -->
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
@endsection
