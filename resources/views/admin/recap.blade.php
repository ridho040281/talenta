@extends('layouts.admin')

@section('title', 'Rekapitulasi Terpadu TALENTA 2026')
@section('page_title', 'Rekapitulasi Terpadu & Hasil Lomba')

@section('content')
<div class="space-y-5" x-data="{ 
    activeTab: 'keuangan',
    searchQuery: '',
    selectedCategory: 'all',
    selectedStatus: 'all',
    showBranchBreakdown: true,
    items: @js($allRegistrations->map(function($r) {
        $firstMember = $r->members->first();
        return [
            'id' => $r->id,
            'comp_id' => (string) $r->competition_id,
            'status' => $r->status,
            'search' => strtolower(($r->team_name ?: ($firstMember?->full_name ?? '')) . ' ' . ($firstMember?->nisn ?? '') . ' ' . $r->institution_name . ' ' . $r->registration_code . ' ' . ($r->competition->name ?? '') . ' ' . ($r->sub_category ?? ''))
        ];
    })),
    get filteredItems() {
        const query = (this.searchQuery || '').toLowerCase().trim();
        return this.items.filter(item => {
            const matchComp = (this.selectedCategory === 'all' || item.comp_id === String(this.selectedCategory));
            const matchStatus = (this.selectedStatus === 'all' || item.status === this.selectedStatus);
            const matchSearch = (!query || item.search.includes(query));
            return matchComp && matchStatus && matchSearch;
        });
    },
    isItemVisible(id) {
        const query = (this.searchQuery || '').toLowerCase().trim();
        const item = this.items.find(i => i.id === id);
        if (!item) return false;
        const matchComp = (this.selectedCategory === 'all' || item.comp_id === String(this.selectedCategory));
        const matchStatus = (this.selectedStatus === 'all' || item.status === this.selectedStatus);
        const matchSearch = (!query || item.search.includes(query));
        return matchComp && matchStatus && matchSearch;
    },
    resetFilters() {
        this.searchQuery = '';
        this.selectedCategory = 'all';
        this.selectedStatus = 'all';
    }
}">

    <!-- Compact Global Overview Stat Cards (Hemat Ruang & Modern) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <!-- Card 1: Total Pendaftar -->
        <div class="ai-card rounded-2xl p-3.5 border border-white/[0.08] shadow-md flex items-center justify-between hover:border-[#4E6EFF]/50 transition">
            <div class="space-y-0.5">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Total Pendaftar</span>
                <div class="flex items-baseline gap-1.5">
                    <span class="text-lg sm:text-xl font-black text-white font-mono">{{ $grandTotals['total_registrations'] }}</span>
                    <span class="text-[10px] text-slate-400 font-medium">/ {{ $grandTotals['total_quota'] }} Kuota</span>
                </div>
                <span class="text-[10px] text-[#84D0FF] font-semibold block">{{ $competitions->count() }} Cabang Lomba</span>
            </div>
            <div class="w-8 h-8 rounded-xl bg-[#4E6EFF]/15 text-[#84D0FF] border border-[#4E6EFF]/30 flex items-center justify-center shrink-0">
                <i data-lucide="users" class="w-4 h-4"></i>
            </div>
        </div>

        <!-- Card 2: Terverifikasi (Lunas) -->
        <div class="ai-card rounded-2xl p-3.5 border border-white/[0.08] shadow-md flex items-center justify-between hover:border-emerald-500/50 transition">
            <div class="space-y-0.5">
                <span class="text-[10px] font-bold text-emerald-400 uppercase tracking-wider block">Terverifikasi (Lunas)</span>
                <div class="flex items-baseline gap-1.5">
                    <span class="text-lg sm:text-xl font-black text-emerald-400 font-mono">{{ $grandTotals['verified_registrations'] }}</span>
                    <span class="text-[10px] text-emerald-300/80 font-medium">Siswa Valid</span>
                </div>
                <span class="text-[10px] text-emerald-400 font-semibold block">Siap tanding 100%</span>
            </div>
            <div class="w-8 h-8 rounded-xl bg-emerald-500/15 text-emerald-400 border border-emerald-500/30 flex items-center justify-center shrink-0">
                <i data-lucide="check-circle" class="w-4 h-4"></i>
            </div>
        </div>

        <!-- Card 3: Uang Pendaftaran Masuk (Lunas) -->
        <div class="ai-card rounded-2xl p-3.5 border border-white/[0.08] shadow-md flex items-center justify-between hover:border-emerald-500/50 transition">
            <div class="space-y-0.5">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Dana Masuk (Lunas)</span>
                <div>
                    <span class="text-base sm:text-lg font-black text-emerald-400 font-mono">Rp {{ number_format($grandTotals['verified_income'], 0, ',', '.') }}</span>
                </div>
                <span class="text-[10px] text-slate-400 block">Dana registrasi valid</span>
            </div>
            <div class="w-8 h-8 rounded-xl bg-emerald-500/15 text-emerald-400 border border-emerald-500/30 flex items-center justify-center shrink-0">
                <i data-lucide="wallet" class="w-4 h-4"></i>
            </div>
        </div>

        <!-- Card 4: Potensi Total Dana -->
        <div class="ai-card rounded-2xl p-3.5 border border-white/[0.08] shadow-md flex items-center justify-between bg-gradient-to-tr from-[#7A5AF8]/20 to-[#4E6EFF]/20 hover:border-[#7A5AF8]/50 transition">
            <div class="space-y-0.5">
                <span class="text-[10px] font-bold text-[#A594FD] uppercase tracking-wider block">Total Potensi Dana</span>
                <div>
                    <span class="text-base sm:text-lg font-black text-white font-mono">Rp {{ number_format($grandTotals['total_potential_income'], 0, ',', '.') }}</span>
                </div>
                <span class="text-[10px] text-[#A594FD] font-medium block">{{ $grandTotals['pending_registrations'] }} pendaftar pending</span>
            </div>
            <div class="w-8 h-8 rounded-xl bg-[#7A5AF8]/20 text-white border border-[#7A5AF8]/30 flex items-center justify-center shrink-0">
                <i data-lucide="coins" class="w-4 h-4"></i>
            </div>
        </div>
    </div>

    <!-- Section: Rincian Pendaftar & Keuangan Per Cabang (Kompak, Elegan & Rinci) -->
    <div class="ai-card rounded-3xl p-4 sm:p-5 border border-white/[0.08] shadow-xl space-y-3.5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 border-b border-white/[0.08] pb-3">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-xl bg-gradient-to-tr from-[#7A5AF8] to-[#4E6EFF] text-white flex items-center justify-center text-xs shadow-sm">
                    <i data-lucide="layers" class="w-3.5 h-3.5"></i>
                </div>
                <div>
                    <h4 class="text-xs sm:text-sm font-black text-white">Rincian Pendaftar & Keuangan Per Cabang</h4>
                    <p class="text-[10px] text-slate-400">Rincian peserta, nominal masuk, serta pembagian kelas Bulu Tangkis & Tenis Meja</p>
                </div>
            </div>

            <div class="flex items-center gap-2 self-end sm:self-auto">
                <button type="button" @click="showBranchBreakdown = !showBranchBreakdown" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-white/[0.06] hover:bg-white/[0.12] text-slate-300 hover:text-white text-[11px] font-bold border border-white/[0.08] transition cursor-pointer">
                    <i data-lucide="sliders-horizontal" class="w-3.5 h-3.5 text-[#84D0FF]"></i>
                    <span x-text="showBranchBreakdown ? 'Sembunyikan Rincian Kelas' : 'Tampilkan Rincian Kelas (BLT & TMJ)'"></span>
                </button>
            </div>
        </div>

        <!-- Grid Cards Per Cabang -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-3 gap-3">
            @foreach($branchStats as $branch)
                @php
                    $hasBreakdown = !empty($branch['breakdown']);
                    $fillPercent = $branch['quota'] > 0 ? min(100, round(($branch['total_regs'] / $branch['quota']) * 100)) : 0;
                @endphp
                <div class="rounded-2xl border border-white/[0.07] bg-[#0A0E1A]/80 p-3.5 space-y-2.5 hover:border-[#7A5AF8]/40 transition group cursor-pointer"
                     @click="activeTab = 'peserta'; selectedCategory = '{{ $branch['id'] }}'"
                     title="Klik untuk melihat peserta cabang {{ $branch['name'] }}">
                    
                    <!-- Card Header -->
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2 overflow-hidden">
                            <span class="px-2 py-0.5 rounded-lg text-[10px] font-mono font-black text-[#84D0FF] bg-[#4E6EFF]/15 border border-[#4E6EFF]/30 shrink-0">
                                {{ $branch['code'] }}
                            </span>
                            <h5 class="text-xs font-bold text-white truncate group-hover:text-[#84D0FF] transition">{{ $branch['name'] }}</h5>
                        </div>
                        <span class="text-[10px] font-bold text-slate-400 bg-white/[0.04] px-2 py-0.5 rounded-full border border-white/[0.06] shrink-0">
                            {{ $branch['total_regs'] }}@if($branch['quota'] > 0)/{{ $branch['quota'] }}@endif
                        </span>
                    </div>

                    <!-- Summary Numbers (Pendaftar & Nominal Uang) -->
                    <div class="grid grid-cols-2 gap-2 bg-[#060911]/80 rounded-xl p-2 border border-white/[0.04]">
                        <div>
                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block">Pendaftar</span>
                            <div class="flex items-baseline gap-1">
                                <span class="text-sm font-black text-white font-mono">{{ $branch['total_regs'] }}</span>
                                <span class="text-[10px] text-emerald-400 font-bold">({{ $branch['verified_count'] }} Lunas)</span>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block">Dana Lunas</span>
                            <span class="text-xs sm:text-sm font-black text-emerald-400 font-mono block">
                                Rp {{ number_format($branch['verified_income'], 0, ',', '.') }}
                            </span>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <div class="w-full bg-[#0C111D] h-1 rounded-full overflow-hidden border border-white/[0.04]">
                        <div class="bg-gradient-to-r from-[#7A5AF8] to-[#4E6EFF] h-full rounded-full" style="width: {{ $fillPercent }}%"></div>
                    </div>

                    <!-- Class/Category Breakdown (Khusus BLT, TMJ, dll) -->
                    @if($hasBreakdown)
                        <div x-show="showBranchBreakdown" class="space-y-1 pt-1 border-t border-white/[0.06]">
                            <span class="text-[9px] font-black uppercase text-slate-400 tracking-wider block mb-1">Rincian Kategori / Kelas:</span>
                            <div class="space-y-1">
                                @foreach($branch['breakdown'] as $bItem)
                                    <div class="flex items-center justify-between text-[10px] px-2 py-1 rounded-lg bg-white/[0.03] border border-white/[0.04]">
                                        <span class="text-slate-300 font-medium truncate max-w-[130px]">{{ $bItem['label'] }}</span>
                                        <div class="flex items-center gap-2 shrink-0">
                                            <span class="font-bold text-white font-mono">{{ $bItem['count'] }} <span class="text-[9px] text-slate-400">psrt</span></span>
                                            <span class="font-bold text-emerald-400 font-mono">Rp {{ number_format($bItem['income'], 0, ',', '.') }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <!-- Navigation Tabs Bar (AIStarterKit Pill Nav) -->
    <div class="ai-card rounded-3xl p-2 border border-white/[0.08] shadow-lg flex flex-wrap items-center gap-2">
        <button @click="activeTab = 'keuangan'" :class="activeTab === 'keuangan' ? 'bg-gradient-to-r from-[#7A5AF8] to-[#4E6EFF] text-white shadow-md shadow-[#7A5AF8]/30 font-black' : 'text-slate-400 hover:text-white'" class="flex items-center gap-2 px-5 py-3 rounded-2xl text-xs sm:text-sm transition cursor-pointer">
            <i data-lucide="landmark" class="w-4 h-4"></i>
            <span>1. Rekap Keuangan & Lomba</span>
        </button>

        <button @click="activeTab = 'peserta'" :class="activeTab === 'peserta' ? 'bg-gradient-to-r from-[#7A5AF8] to-[#4E6EFF] text-white shadow-md shadow-[#7A5AF8]/30 font-black' : 'text-slate-400 hover:text-white'" class="flex items-center gap-2 px-5 py-3 rounded-2xl text-xs sm:text-sm transition cursor-pointer">
            <i data-lucide="users" class="w-4 h-4"></i>
            <span>2. Master Seluruh Peserta</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-black" :class="activeTab === 'peserta' ? 'bg-white text-slate-900' : 'bg-white/[0.1] text-slate-300'">{{ $allRegistrations->count() }}</span>
        </button>

        <button @click="activeTab = 'juara'" :class="activeTab === 'juara' ? 'bg-gradient-to-r from-[#7A5AF8] to-[#4E6EFF] text-white shadow-md shadow-[#7A5AF8]/30 font-black' : 'text-slate-400 hover:text-white'" class="flex items-center gap-2 px-5 py-3 rounded-2xl text-xs sm:text-sm transition cursor-pointer">
            <i data-lucide="medal" class="w-4 h-4 text-[#FF58D5]"></i>
            <span>3. Rekap Semua Peraih Juara</span>
        </button>

        <button @click="activeTab = 'juara-umum'" :class="activeTab === 'juara-umum' ? 'bg-gradient-to-r from-[#7A5AF8] to-[#4E6EFF] text-white shadow-md shadow-[#7A5AF8]/30 font-black' : 'text-slate-400 hover:text-white'" class="flex items-center gap-2 px-5 py-3 rounded-2xl text-xs sm:text-sm transition cursor-pointer">
            <i data-lucide="trophy" class="w-4 h-4 text-amber-400"></i>
            <span>4. Rekap Juara Umum</span>
        </button>
    </div>

    <!-- ==================== TAB 1: REKAP KEUANGAN & LOMBA ==================== -->
    <div x-show="activeTab === 'keuangan'" x-transition class="space-y-6">
        <div class="ai-card rounded-3xl border border-white/[0.08] shadow-xl p-5 sm:p-7 lg:p-8 space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-white/[0.08] pb-4">
                <div>
                    <h3 class="text-lg font-black text-white">Rekapitulasi Keuangan & Kuota Pendaftaran Cabang Lomba</h3>
                    <p class="text-xs text-slate-400">Rincian pendapatan registrasi dan keterisian kuota peserta per cabang lomba</p>
                </div>
                <div class="text-left sm:text-right">
                    <span class="text-xs text-slate-400 block uppercase font-bold">Total Dana Lunas Masuk</span>
                    <span class="text-xl font-black text-emerald-400 font-mono">Rp {{ number_format($grandTotals['verified_income'], 0, ',', '.') }}</span>
                </div>
            </div>

            <div class="overflow-x-auto rounded-2xl border border-white/[0.08] bg-[#0A0E1A]/40 shadow-inner">
                <table class="w-full min-w-[950px] text-left text-xs text-slate-300 border-collapse">
                    <thead class="text-[10px] font-bold uppercase tracking-wider bg-[#0C111D]/90 text-slate-400 border-b border-white/[0.08]">
                        <tr>
                            <th class="py-3.5 px-4 whitespace-nowrap">KODE</th>
                            <th class="py-3.5 px-4 whitespace-nowrap min-w-[200px]">NAMA CABANG LOMBA</th>
                            <th class="py-3.5 px-4 text-center whitespace-nowrap">KUOTA</th>
                            <th class="py-3.5 px-4 text-center whitespace-nowrap">PENDAFTAR</th>
                            <th class="py-3.5 px-4 text-center text-emerald-400 whitespace-nowrap">VERIFIKASI (LUNAS)</th>
                            <th class="py-3.5 px-4 text-center text-amber-400 whitespace-nowrap">PENDING</th>
                            <th class="py-3.5 px-4 text-right whitespace-nowrap">DANA LUNAS MASUK</th>
                            <th class="py-3.5 px-4 text-right whitespace-nowrap">POTENSI TOTAL</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/[0.04] font-medium">
                        @foreach($financeRecap as $item)
                            @php
                                $c = $item['competition'];
                                $fillPercent = $item['quota'] > 0 ? min(100, round(($item['total_regs'] / $item['quota']) * 100)) : 0;
                            @endphp
                            <tr class="hover:bg-white/[0.025] transition">
                                <td class="py-3.5 px-4 font-mono font-bold text-[#84D0FF] whitespace-nowrap">
                                    {{ $c->code }}
                                </td>
                                <td class="py-3.5 px-4 min-w-[200px]">
                                    <span class="font-bold text-white text-sm block">{{ $c->name }}</span>
                                    <span class="text-[11px] text-slate-400">{{ $c->category->name ?? 'Lomba' }} • PIC: {{ $c->pic->name ?? '-' }}</span>
                                </td>
                                <td class="py-3.5 px-4 text-center font-bold text-slate-200 whitespace-nowrap">
                                    @if($item['quota'] <= 0)
                                        <span class="text-purple-300 bg-purple-500/20 px-2 py-0.5 rounded-full text-[10px] font-black border border-purple-500/30 whitespace-nowrap">∞ Tak Terbatas</span>
                                    @else
                                        {{ $item['quota'] }}
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                    <span class="font-bold text-white">{{ $item['total_regs'] }}</span>
                                    <div class="w-16 bg-[#0C111D] h-1.5 rounded-full mx-auto mt-1 overflow-hidden border border-white/[0.06]">
                                        <div class="bg-gradient-to-r from-[#7A5AF8] to-[#4E6EFF] h-full rounded-full" style="width: {{ $fillPercent }}%"></div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-emerald-500/15 text-emerald-400 border border-emerald-500/30 whitespace-nowrap">
                                        {{ $item['verified_count'] }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black {{ $item['pending_count'] > 0 ? 'bg-amber-500/15 text-amber-400 border border-amber-500/30' : 'bg-white/[0.05] text-slate-500 border border-white/[0.08]' }} whitespace-nowrap">
                                        {{ $item['pending_count'] }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right font-black text-emerald-400 font-mono text-sm whitespace-nowrap">
                                    Rp {{ number_format($item['verified_income'], 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-4 text-right font-bold text-slate-200 font-mono whitespace-nowrap">
                                    Rp {{ number_format($item['total_income'], 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-[#0C111D] text-white font-bold border-t-2 border-white/[0.1]">
                        <tr>
                            <td colspan="2" class="py-4 px-4 font-black uppercase text-xs whitespace-nowrap">GRAND TOTAL KESELURUHAN:</td>
                            <td class="py-4 px-4 text-center font-black whitespace-nowrap">{{ $grandTotals['total_quota'] }}</td>
                            <td class="py-4 px-4 text-center font-black whitespace-nowrap">{{ $grandTotals['total_registrations'] }}</td>
                            <td class="py-4 px-4 text-center font-black text-emerald-300 whitespace-nowrap">{{ $grandTotals['verified_registrations'] }}</td>
                            <td class="py-4 px-4 text-center font-black text-amber-300 whitespace-nowrap">{{ $grandTotals['pending_registrations'] }}</td>
                            <td class="py-4 px-4 text-right font-black text-emerald-400 font-mono text-base whitespace-nowrap">
                                Rp {{ number_format($grandTotals['verified_income'], 0, ',', '.') }}
                            </td>
                            <td class="py-4 px-4 text-right font-black text-white font-mono text-base whitespace-nowrap">
                                Rp {{ number_format($grandTotals['total_potential_income'], 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- ==================== TAB 2: MASTER SEMUA PESERTA ==================== -->
    <div x-show="activeTab === 'peserta'" x-transition class="space-y-6">
        <div class="ai-card rounded-3xl border border-white/[0.08] shadow-xl p-5 sm:p-7 lg:p-8 space-y-6">
            
            <!-- Header & Responsive Filter Bar -->
            <div class="space-y-4 border-b border-white/[0.08] pb-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <h3 class="text-lg font-black text-white">Master Data Seluruh Peserta Terdaftar</h3>
                        <p class="text-xs text-slate-400">Daftar lengkap seluruh delegasi siswa dari seluruh cabang lomba TALENTA 2026</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="px-3 py-1.5 rounded-xl text-xs font-bold bg-[#0C111D] text-slate-300 border border-white/[0.08] whitespace-nowrap">
                            Menampilkan <strong class="text-[#84D0FF]" x-text="filteredItems.length"></strong> dari {{ $allRegistrations->count() }} Peserta
                        </span>
                    </div>
                </div>

                <!-- Filter Controls Toolbar (Responsive Grid/Flex) -->
                <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
                    <!-- Search Input -->
                    <div class="sm:col-span-5 lg:col-span-5 relative">
                        <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                        <input type="text" x-model="searchQuery" placeholder="Cari nama, NISN, sekolah, no. reg..." class="w-full pl-9 pr-8 py-2.5 rounded-xl bg-[#0C111D] border border-white/[0.1] text-xs text-white placeholder-slate-500 focus:border-[#7A5AF8] focus:ring-2 focus:ring-[#7A5AF8]/20 outline-none">
                        <button x-show="searchQuery" @click="searchQuery = ''" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-white text-xs p-1 cursor-pointer" title="Hapus pencarian">
                            <i data-lucide="x" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>

                    <!-- Competition Filter -->
                    <div class="sm:col-span-4 lg:col-span-4">
                        <select x-model="selectedCategory" class="w-full px-3 py-2.5 rounded-xl border border-white/[0.1] text-xs font-bold text-slate-200 bg-[#0C111D] focus:border-[#7A5AF8] outline-none cursor-pointer">
                            <option value="all">Semua Cabang Lomba ({{ $competitions->count() }})</option>
                            @foreach($competitions as $c)
                                <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->code }})</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Status Filter & Reset -->
                    <div class="sm:col-span-3 lg:col-span-3 flex items-center gap-2">
                        <select x-model="selectedStatus" class="w-full px-3 py-2.5 rounded-xl border border-white/[0.1] text-xs font-bold text-slate-200 bg-[#0C111D] focus:border-[#7A5AF8] outline-none cursor-pointer">
                            <option value="all">Semua Status</option>
                            <option value="verified">Lunas / Terverifikasi</option>
                            <option value="pending">Menunggu Verifikasi</option>
                            <option value="rejected">Ditolak</option>
                        </select>

                        <button x-show="searchQuery !== '' || selectedCategory !== 'all' || selectedStatus !== 'all'" @click="resetFilters()" class="p-2.5 rounded-xl bg-white/[0.08] hover:bg-rose-500/20 text-slate-400 hover:text-rose-400 border border-white/[0.08] transition shrink-0 cursor-pointer" title="Reset Semua Filter">
                            <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Master Participants Table (Responsive with min-w) -->
            <div class="overflow-x-auto rounded-2xl border border-white/[0.08] bg-[#0A0E1A]/40 shadow-inner">
                <table class="w-full min-w-[1020px] text-left text-xs text-slate-300 border-collapse">
                    <thead class="text-[10px] font-bold uppercase tracking-wider bg-[#0C111D]/90 text-slate-400 border-b border-white/[0.08]">
                        <tr>
                            <th class="py-3.5 px-4 whitespace-nowrap w-[160px]">NO. REGISTRASI</th>
                            <th class="py-3.5 px-4 whitespace-nowrap min-w-[200px]">NAMA PESERTA / TIM</th>
                            <th class="py-3.5 px-4 whitespace-nowrap min-w-[180px]">ASAL SEKOLAH / MADRASAH</th>
                            <th class="py-3.5 px-4 whitespace-nowrap min-w-[220px]">CABANG LOMBA & KATEGORI</th>
                            <th class="py-3.5 px-4 text-right whitespace-nowrap w-[130px]">BIAYA DAFTAR</th>
                            <th class="py-3.5 px-4 text-center whitespace-nowrap w-[110px]">BUKTI / STRUK</th>
                            <th class="py-3.5 px-4 text-center whitespace-nowrap w-[160px]">STATUS</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/[0.04] font-medium">
                        @forelse($allRegistrations as $reg)
                            <tr x-show="isItemVisible({{ $reg->id }})" class="hover:bg-white/[0.025] transition">
                                <!-- No. Registrasi -->
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <span class="font-mono font-bold text-[#84D0FF] tracking-tight bg-[#4E6EFF]/10 px-2.5 py-1 rounded-lg border border-[#4E6EFF]/20 inline-block">
                                        {{ $reg->registration_code }}
                                    </span>
                                </td>

                                <!-- Nama Peserta / Tim -->
                                <td class="py-3.5 px-4 min-w-[200px]">
                                    <div class="font-bold text-white text-sm leading-snug">
                                        {{ $reg->team_name ?: ($reg->members->first()->full_name ?? 'Peserta #' . $reg->id) }}
                                    </div>
                                    <div class="text-[11px] text-slate-400 flex items-center gap-1.5 mt-0.5 whitespace-nowrap">
                                        <span>NISN: <span class="font-mono text-slate-300">{{ $reg->members->first()->nisn ?? '-' }}</span></span>
                                        <span>•</span>
                                        <span>{{ $reg->members->count() }} Anggota</span>
                                    </div>
                                </td>

                                <!-- Asal Sekolah / Madrasah -->
                                <td class="py-3.5 px-4 min-w-[180px]">
                                    <span class="font-bold text-slate-200 block text-xs">
                                        {{ $reg->institution_name }}
                                    </span>
                                </td>

                                <!-- Cabang Lomba & Kategori -->
                                <td class="py-3.5 px-4 min-w-[220px]">
                                    <span class="font-bold text-white block text-xs">
                                        {{ $reg->competition->name }}
                                    </span>
                                    @if($reg->sub_category)
                                        <span class="inline-flex items-center mt-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-500/15 text-amber-300 border border-amber-500/30 whitespace-nowrap">
                                            {{ $reg->sub_category }}
                                        </span>
                                    @else
                                        <span class="inline-block mt-0.5 text-[10px] text-slate-400 uppercase font-mono">{{ $reg->competition->code }} ({{ $reg->competition->type }})</span>
                                    @endif
                                </td>

                                <!-- Biaya Daftar -->
                                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                    <span class="font-black text-emerald-400 font-mono text-sm">
                                        Rp {{ number_format($reg->fee, 0, ',', '.') }}
                                    </span>
                                </td>

                                <!-- Bukti / Struk -->
                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                    @php
                                        $proofPath = $reg->payment_proof ?: ($reg->invoice?->payment_proof ?? null);
                                    @endphp
                                    @if($proofPath)
                                        <a href="{{ asset('storage/' . $proofPath) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-[#4E6EFF]/15 text-[#84D0FF] font-bold hover:bg-[#4E6EFF]/25 transition text-xs border border-[#4E6EFF]/30 shadow-xs">
                                            <i data-lucide="image" class="w-3.5 h-3.5"></i>
                                            <span>Struk</span>
                                        </a>
                                    @else
                                        <span class="text-slate-500 italic text-xs">-</span>
                                    @endif
                                </td>

                                <!-- Status -->
                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                    @if($reg->status === 'verified')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-black bg-emerald-500/15 text-emerald-400 border border-emerald-500/30 whitespace-nowrap">
                                            <i data-lucide="check-circle" class="w-3 h-3"></i>
                                            <span>✔ LUNAS / TERVERIFIKASI</span>
                                        </span>
                                    @elseif($reg->status === 'rejected')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-black bg-rose-500/15 text-rose-400 border border-rose-500/30 whitespace-nowrap">
                                            <i data-lucide="x-circle" class="w-3 h-3"></i>
                                            <span>✕ DITOLAK</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-black bg-amber-500/15 text-amber-400 border border-amber-500/30 whitespace-nowrap">
                                            <i data-lucide="clock" class="w-3 h-3"></i>
                                            <span>⏳ PENDING</span>
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-500">Belum ada peserta terdaftar.</td>
                            </tr>
                        @endforelse

                        <!-- Empty state when search filters yield no results -->
                        <tr x-show="filteredItems.length === 0 && {{ $allRegistrations->count() }} > 0">
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center space-y-2">
                                    <i data-lucide="search-x" class="w-8 h-8 text-slate-500"></i>
                                    <p class="font-bold text-sm text-slate-300">Tidak ada peserta yang cocok dengan filter pencarian</p>
                                    <p class="text-xs text-slate-500">Coba gunakan kata kunci lain atau reset filter</p>
                                    <button @click="resetFilters()" class="mt-2 px-3.5 py-1.5 rounded-xl bg-white/[0.08] hover:bg-white/[0.15] text-xs font-bold text-white transition cursor-pointer">
                                        Reset Filter
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ==================== TAB 3: REKAP SEMUA PERAIH JUARA ==================== -->
    <div x-show="activeTab === 'juara'" x-transition class="space-y-6">
        <div class="ai-card rounded-3xl border border-white/[0.08] shadow-xl p-5 sm:p-7 lg:p-8 space-y-6">
            <div class="flex items-center justify-between border-b border-white/[0.08] pb-4">
                <div>
                    <h3 class="text-lg font-black text-white">Rekapitulasi Peraih Juara (1, 2, 3 & Harapan)</h3>
                    <p class="text-xs text-slate-400">Daftar pemenang resmi berdasarkan hasil akhir penilaian dewan juri di setiap cabang lomba</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach($winnersByCompetition as $w)
                    @php $comp = $w['competition']; @endphp
                    <div class="rounded-3xl border border-white/[0.08] bg-[#0C111D]/80 p-6 space-y-4 hover:border-white/[0.15] transition">
                        <div class="flex items-center justify-between border-b border-white/[0.08] pb-3">
                            <div>
                                <span class="text-[10px] font-black uppercase tracking-wider text-[#A594FD] bg-[#7A5AF8]/15 px-2.5 py-1 rounded-lg border border-[#7A5AF8]/30">{{ $comp->code }}</span>
                                <h4 class="font-black text-white text-base mt-2">{{ $comp->name }}</h4>
                            </div>
                            <span class="text-xs font-bold text-slate-400">
                                {{ $w['total_participants'] }} Peserta Terverifikasi
                            </span>
                        </div>

                        @if($w['has_results'])
                            <div class="space-y-2.5">
                                <!-- Juara 1 -->
                                @if($w['juara_1'])
                                    <div class="p-3 rounded-2xl bg-amber-500/15 border border-amber-500/30 flex items-center justify-between">
                                        <div class="flex items-center gap-2.5">
                                            <span class="w-7 h-7 rounded-full bg-amber-400 text-slate-950 font-black text-xs flex items-center justify-center shadow-xs">1</span>
                                            <div>
                                                <h5 class="font-bold text-white text-xs">{{ $w['juara_1']['registration']->display_name }}</h5>
                                                <p class="text-[10px] text-slate-400">{{ $w['juara_1']['institution'] }}</p>
                                            </div>
                                        </div>
                                        <span class="font-black text-amber-400 text-sm font-mono">{{ number_format($w['juara_1']['avg'], 2) }}</span>
                                    </div>
                                @endif

                                <!-- Juara 2 -->
                                @if($w['juara_2'])
                                    <div class="p-3 rounded-2xl bg-white/[0.05] border border-white/[0.08] flex items-center justify-between">
                                        <div class="flex items-center gap-2.5">
                                            <span class="w-7 h-7 rounded-full bg-slate-300 text-slate-950 font-black text-xs flex items-center justify-center">2</span>
                                            <div>
                                                <h5 class="font-bold text-white text-xs">{{ $w['juara_2']['registration']->display_name }}</h5>
                                                <p class="text-[10px] text-slate-400">{{ $w['juara_2']['institution'] }}</p>
                                            </div>
                                        </div>
                                        <span class="font-bold text-slate-300 text-sm font-mono">{{ number_format($w['juara_2']['avg'], 2) }}</span>
                                    </div>
                                @endif

                                <!-- Juara 3 -->
                                @if($w['juara_3'])
                                    <div class="p-3 rounded-2xl bg-amber-900/20 border border-amber-700/30 flex items-center justify-between">
                                        <div class="flex items-center gap-2.5">
                                            <span class="w-7 h-7 rounded-full bg-amber-700 text-white font-black text-xs flex items-center justify-center">3</span>
                                            <div>
                                                <h5 class="font-bold text-white text-xs">{{ $w['juara_3']['registration']->display_name }}</h5>
                                                <p class="text-[10px] text-slate-400">{{ $w['juara_3']['institution'] }}</p>
                                            </div>
                                        </div>
                                        <span class="font-bold text-amber-400 text-sm font-mono">{{ number_format($w['juara_3']['avg'], 2) }}</span>
                                    </div>
                                @endif

                                <!-- Juara Harapan 1 -->
                                @if($w['harapan_1'])
                                    <div class="p-2.5 rounded-xl bg-white/[0.03] border border-white/[0.06] flex items-center justify-between text-xs">
                                        <div class="flex items-center gap-2">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-white/[0.08] text-slate-300">Harapan 1</span>
                                            <span class="font-medium text-slate-200">{{ $w['harapan_1']['registration']->display_name }}</span>
                                        </div>
                                        <span class="font-mono text-slate-400 text-xs">{{ number_format($w['harapan_1']['avg'], 2) }}</span>
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="py-6 text-center text-slate-500 text-xs italic">
                                Belum ada skor nilai yang dikunci oleh dewan juri.
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- ==================== TAB 4: REKAP JUARA UMUM ==================== -->
    <div x-show="activeTab === 'juara-umum'" x-transition class="space-y-6">
        <div class="ai-card rounded-3xl border border-white/[0.08] shadow-xl overflow-hidden space-y-6 p-5 sm:p-7 lg:p-8">
            <div class="flex items-center justify-between border-b border-white/[0.08] pb-4">
                <div>
                    <h3 class="text-base font-bold text-white">Klasemen Perolehan Medali & Juara Umum Kontingen</h3>
                    <p class="text-xs text-slate-400">Bobot poin kontingen: 🥇 Emas (5 Poin), 🥈 Perak (3 Poin), 🥉 Perunggu (1 Poin)</p>
                </div>
            </div>

            <div class="overflow-x-auto rounded-2xl border border-white/[0.08] bg-[#0A0E1A]/40 shadow-inner">
                <table class="w-full min-w-[850px] text-left text-sm text-slate-300 border-collapse">
                    <thead class="text-xs font-bold uppercase tracking-wider bg-[#0C111D]/90 text-slate-400 border-b border-white/[0.08]">
                        <tr>
                            <th class="py-4 px-6 text-center w-16 whitespace-nowrap">Peringkat</th>
                            <th class="py-4 px-6 min-w-[220px] whitespace-nowrap">Nama Asal Sekolah / Madrasah</th>
                            <th class="py-4 px-4 text-center whitespace-nowrap">🥇 Emas (5p)</th>
                            <th class="py-4 px-4 text-center whitespace-nowrap">🥈 Perak (3p)</th>
                            <th class="py-4 px-4 text-center whitespace-nowrap">🥉 Perunggu (1p)</th>
                            <th class="py-4 px-6 text-center whitespace-nowrap">Total Medali</th>
                            <th class="py-4 px-6 text-right whitespace-nowrap">Total Poin</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/[0.04] font-medium">
                        @forelse($standings as $index => $item)
                            <tr class="hover:bg-white/[0.025] transition {{ $index === 0 ? 'bg-amber-500/10' : '' }}">
                                <td class="py-4 px-6 text-center whitespace-nowrap">
                                    @if($index === 0)
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-amber-400 text-slate-950 font-black text-sm shadow-md">1</span>
                                    @elseif($index === 1)
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-slate-300 text-slate-950 font-black text-sm">2</span>
                                    @elseif($index === 2)
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-amber-700 text-white font-black text-sm">3</span>
                                    @else
                                        <span class="text-slate-400 font-bold">{{ $index + 1 }}</span>
                                    @endif
                                </td>
                                <td class="py-4 px-6 font-bold text-white text-base min-w-[220px] whitespace-nowrap">
                                    {{ $item['institution'] }}
                                    @if($index === 0)
                                        <span class="ml-2 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-amber-400 text-slate-950">
                                            Calon Juara Umum
                                        </span>
                                    @endif
                                </td>
                                <td class="py-4 px-4 text-center font-black text-amber-400 text-base whitespace-nowrap">{{ $item['emas'] }}</td>
                                <td class="py-4 px-4 text-center font-black text-slate-300 text-base whitespace-nowrap">{{ $item['perak'] }}</td>
                                <td class="py-4 px-4 text-center font-black text-amber-500 text-base whitespace-nowrap">{{ $item['perunggu'] }}</td>
                                <td class="py-4 px-6 text-center font-bold text-slate-200 whitespace-nowrap">{{ $item['total_medali'] }}</td>
                                <td class="py-4 px-6 text-right whitespace-nowrap">
                                    <span class="text-2xl font-black text-emerald-400">{{ $item['total_poin'] }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-500">
                                    Belum ada skor perlombaan yang dikunci oleh dewan juri.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

