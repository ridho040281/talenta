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
    },
    batchModalOpen: false,
    selectedComp: null,
    isProcessingBatch: false,
    batchSuccessMessage: '',
    batchErrorMessage: '',
    batchCountdown: null,
    
    openBatchModal(comp) {
        this.selectedComp = comp;
        this.batchSuccessMessage = '';
        this.batchErrorMessage = '';
        this.isProcessingBatch = false;
        this.batchCountdown = null;
        this.batchModalOpen = true;
        this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
    },
    
    async executeBatchDraw() {
        if (!this.selectedComp || this.isProcessingBatch) return;
        this.isProcessingBatch = true;
        this.batchErrorMessage = '';
        this.batchSuccessMessage = '';
        
        // Animasi countdown 3 detik
        for (let i = 3; i >= 1; i--) {
            this.batchCountdown = i;
            await new Promise(r => setTimeout(r, 600));
        }
        this.batchCountdown = 'SHUFFLING...';
        await new Promise(r => setTimeout(r, 600));
        
        try {
            const res = await fetch(`/pic/lomba/${this.selectedComp.id}/batch-draw`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ pool_key: 'all' })
            });
            const data = await res.json();
            if (data.success) {
                this.batchSuccessMessage = data.message;
                if (typeof confetti === 'function') {
                    confetti({ particleCount: 120, spread: 80, origin: { y: 0.6 } });
                }
                setTimeout(() => {
                    window.location.reload();
                }, 1600);
            } else {
                this.batchErrorMessage = data.message || 'Gagal melakukan pengacakan nomor undian.';
                this.isProcessingBatch = false;
            }
        } catch (e) {
            this.batchErrorMessage = 'Terjadi kesalahan server saat memproses batch draw.';
            this.isProcessingBatch = false;
        } finally {
            this.batchCountdown = null;
        }
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
                    Pilih metode pengundian sesuai kebutuhan: <span class="text-amber-400 font-bold">Mode 1-by-1 (Interaktif)</span> via Hacker Matrix / Spin Wheel, atau <span class="text-emerald-400 font-bold">⚡ Batch / Full-Shuffle Auto Draw</span> untuk mengacak seluruh nomor urut sekaligus dalam 1 kali putar.
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
                    <!-- Option 1: 1-by-1 Interactive Mode (Hacker Terminal) -->
                    <a href="{{ route('pic.hacker.draw', $comp['id']) }}" class="w-full py-2.5 px-4 rounded-xl gradient-btn font-bold text-xs flex items-center justify-center gap-2 shadow-lg shadow-[#7A5AF8]/20 transition group/btn cursor-pointer">
                        <i data-lucide="terminal" class="w-4 h-4 text-emerald-300 group-hover/btn:scale-110 transition-transform"></i>
                        <span>Mode 1-by-1 (Live Decoder)</span>
                    </a>

                    <!-- Option 2: Batch / Full-Shuffle Auto Draw Quick Trigger -->
                    @if(!$comp['is_complete'])
                        <button type="button" 
                                @click="openBatchModal({{ json_encode($comp) }})"
                                class="w-full py-2.5 px-4 rounded-xl bg-gradient-to-r from-emerald-500/20 via-teal-500/20 to-emerald-600/20 hover:from-emerald-500/30 hover:to-teal-500/30 text-emerald-300 border border-emerald-500/40 hover:border-emerald-400 font-bold text-xs flex items-center justify-center gap-2 transition cursor-pointer shadow-md shadow-emerald-500/10">
                            <i data-lucide="zap" class="w-4 h-4 text-emerald-400"></i>
                            <span>⚡ Batch / Full-Shuffle Auto Draw</span>
                        </button>
                    @endif

                    <!-- Secondary Actions Grid (Spin Wheel & Public TV) -->
                    <div class="grid grid-cols-2 gap-2">
                        <!-- Spin Wheel Button -->
                        <a href="{{ route('pic.spin.wheel', $comp['id']) }}" class="py-2 px-3 rounded-xl bg-amber-500/10 hover:bg-amber-500/20 text-amber-300 border border-amber-500/30 hover:border-amber-400/60 font-bold text-xs flex items-center justify-center gap-1.5 transition cursor-pointer">
                            <i data-lucide="disc" class="w-3.5 h-3.5 text-amber-400"></i>
                            <span>Spin Wheel (1-by-1)</span>
                        </a>

                        <!-- Public Screen Viewer -->
                        <a href="{{ route('spin.viewer', $comp['slug']) }}" target="_blank" class="py-2 px-3 rounded-xl bg-white/[0.06] hover:bg-white/[0.12] text-slate-300 hover:text-white border border-white/[0.10] hover:border-white/[0.25] font-bold text-xs flex items-center justify-center gap-1.5 transition cursor-pointer">
                            <i data-lucide="tv" class="w-3.5 h-3.5 text-[#84D0FF]"></i>
                            <span>Layar TV Publik</span>
                        </a>
                    </div>

                    @php
                        $isBadminton = (strtoupper($comp['code'] ?? '') === 'BLT') 
                            || str_contains(strtolower($comp['name'] ?? ''), 'bulu tangkis') 
                            || str_contains(strtolower($comp['name'] ?? ''), 'badminton');
                    @endphp
                    @if($isBadminton)
                    <!-- Tournament Dedicated Hub Actions -->
                    <div class="p-2.5 rounded-2xl bg-indigo-500/10 border border-indigo-500/30 space-y-2">
                        <div class="flex items-center justify-between text-[11px] font-bold text-indigo-300">
                            <span class="flex items-center gap-1.5">
                                <i data-lucide="git-branch" class="w-3.5 h-3.5 text-indigo-400"></i>
                                <span>Turnamen Sistem Gugur:</span>
                            </span>
                            <span class="text-[10px] text-amber-300 font-mono">BWF Standard</span>
                        </div>
                        <div class="grid grid-cols-2 gap-1.5">
                            <a href="{{ route('pic.bracket', $comp['id']) }}" class="py-1.5 px-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-black text-[11px] flex items-center justify-center gap-1.5 transition shadow-sm cursor-pointer" title="Kelola Bagan Pertandingan & Jadwal 4 Hari">
                                <i data-lucide="git-branch" class="w-3.5 h-3.5"></i>
                                <span>Bagan & Jadwal</span>
                            </a>
                            <a href="{{ route('badminton.index') }}" class="py-1.5 px-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white font-bold text-[11px] flex items-center justify-center gap-1.5 transition cursor-pointer" title="Modul Wasit & Arena Lapangan">
                                <i data-lucide="activity" class="w-3.5 h-3.5 text-emerald-400"></i>
                                <span>Wasit Scoring</span>
                            </a>
                        </div>
                    </div>
                    @endif
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

    <!-- Modal Batch / Full-Shuffle Auto Draw -->
    <div x-show="batchModalOpen" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="bg-slate-900 border border-emerald-500/40 rounded-3xl p-6 sm:p-8 max-w-lg w-full shadow-2xl space-y-5 relative text-white"
             @click.outside="if (!isProcessingBatch) batchModalOpen = false">
            
            <div class="flex items-start justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-500/20 text-emerald-400 border border-emerald-500/40 flex items-center justify-center shrink-0 shadow-lg shadow-emerald-500/20">
                        <i data-lucide="zap" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <span class="text-[10px] font-mono font-bold uppercase tracking-wider text-emerald-400 block">MODUL PENGACAKAN MASSAL</span>
                        <h3 class="text-lg font-black text-white font-display">Batch / Full-Shuffle Auto Draw</h3>
                    </div>
                </div>
                <button type="button" @click="batchModalOpen = false" :disabled="isProcessingBatch" class="text-slate-400 hover:text-white p-1">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <div class="space-y-3 bg-slate-950/60 p-4 rounded-2xl border border-white/[0.08] text-xs leading-relaxed text-slate-300">
                <p>Cabang Lomba: <strong class="text-white" x-text="selectedComp ? selectedComp.name : ''"></strong></p>
                <div class="flex items-center justify-between text-[11px] font-mono bg-slate-900/90 p-2.5 rounded-xl border border-slate-800">
                    <span class="text-slate-400">Peserta Belum Diundi:</span>
                    <span class="text-amber-400 font-bold" x-text="selectedComp ? (selectedComp.total_verified - selectedComp.drawn_count) + ' Peserta' : '0'"></span>
                </div>
                <p class="text-[11px] text-slate-400">
                    Sistem akan mengacak seluruh slot nomor urut peserta yang belum diundi secara kriptografis & adil dalam 1 kali proses. Hasil akan langsung tersimpan dan otomatis sinkron ke Layar TV Publik.
                </p>
            </div>

            <!-- Countdown / Processing Animation Banner -->
            <div x-show="isProcessingBatch" class="p-4 rounded-2xl bg-emerald-950/60 border border-emerald-500/50 text-center space-y-2">
                <div class="text-3xl font-black font-mono text-emerald-400 animate-bounce" x-text="batchCountdown || 'MEMPROSES...'"></div>
                <div class="text-xs font-mono text-emerald-300 tracking-wider">Mengacak & Mengunci Seluruh Nomor Peserta...</div>
            </div>

            <!-- Success / Error Alerts -->
            <div x-show="batchSuccessMessage" class="p-3 rounded-xl bg-emerald-500/20 border border-emerald-500/40 text-emerald-300 text-xs font-bold flex items-center gap-2">
                <i data-lucide="check-circle" class="w-4 h-4 text-emerald-400"></i>
                <span x-text="batchSuccessMessage"></span>
            </div>
            <div x-show="batchErrorMessage" class="p-3 rounded-xl bg-rose-500/20 border border-rose-500/40 text-rose-300 text-xs font-bold flex items-center gap-2">
                <i data-lucide="alert-triangle" class="w-4 h-4 text-rose-400"></i>
                <span x-text="batchErrorMessage"></span>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="button" 
                        @click="batchModalOpen = false" 
                        :disabled="isProcessingBatch"
                        class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-xs transition cursor-pointer">
                    Batal
                </button>
                <button type="button" 
                        @click="executeBatchDraw()" 
                        :disabled="isProcessingBatch"
                        class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 text-slate-950 font-black text-xs shadow-lg shadow-emerald-500/25 flex items-center gap-2 transition cursor-pointer disabled:opacity-50">
                    <i data-lucide="zap" class="w-4 h-4"></i>
                    <span>Mulai Batch / Full-Shuffle Auto Draw</span>
                </button>
            </div>

        </div>
    </div>

</div>
@endsection

