@extends('layouts.admin')

@section('title', 'Rekapitulasi Terpadu TALENTA 2026')
@section('page_title', 'Rekapitulasi Terpadu & Hasil Lomba')

@section('content')
<style>
    /* Styling during PNG export to ensure a pristine, widescreen, unclipped infographic */
    .exporting-infographic {
        width: 1100px !important;
        min-width: 1100px !important;
        max-width: 1100px !important;
        margin: 0 !important;
        padding: 40px 44px 48px 44px !important;
        background-color: #0C111D !important;
        border-radius: 24px !important;
        box-shadow: none !important;
        overflow: visible !important;
    }
    .exporting-infographic * {
        scrollbar-width: none !important;
        -ms-overflow-style: none !important;
    }
    .exporting-infographic *::-webkit-scrollbar {
        display: none !important;
        width: 0 !important;
        height: 0 !important;
    }
    .exporting-infographic .rekap-table-container {
        overflow: visible !important;
        overflow-x: visible !important;
        border-radius: 16px !important;
    }
    .exporting-infographic table {
        width: 100% !important;
        min-width: 100% !important;
        table-layout: auto !important;
    }
</style>
<div class="space-y-4" x-data="{ 
    activeTab: '{{ request('tab', 'keuangan') }}',
    downloadingPng: false,
    recapCategory: 'all',
    searchQuery: '',
    selectedCategory: 'all',
    selectedStatus: 'all',
    expandedBranches: { 'BLT': true, 'TMJ': true },
    toggleBranch(code) {
        this.expandedBranches[code] = !this.expandedBranches[code];
    },
    expandAllBranches() {
        this.expandedBranches = { 'BLT': true, 'TMJ': true };
    },
    collapseAllBranches() {
        this.expandedBranches = {};
    },
    areAllBranchesExpanded() {
        return !!(this.expandedBranches['BLT'] && this.expandedBranches['TMJ']);
    },
    toggleAllBranches() {
        if (this.areAllBranchesExpanded()) {
            this.collapseAllBranches();
        } else {
            this.expandAllBranches();
        }
    },
    async downloadPNG() {
        if (this.downloadingPng) return;
        this.downloadingPng = true;
        
        const originalCard = document.getElementById('rekapPendaftarCard');
        if (!originalCard) {
            this.downloadingPng = false;
            return;
        }

        if (typeof htmlToImage === 'undefined') {
            alert('Pustaka htmlToImage belum selesai dimuat. Silakan muat ulang halaman (Ctrl+F5).');
            this.downloadingPng = false;
            return;
        }

        let container = null;
        try {
            // Buat container staging off-screen dengan lebar pasti 1100px
            // Bebas dari kendala lebar layar HP, laptop kecil, zoom browser, sidebar, dan overflow parent
            container = document.createElement('div');
            container.setAttribute('x-ignore', '');
            container.style.position = 'fixed';
            container.style.top = '-99999px';
            container.style.left = '0';
            container.style.width = '1100px';
            container.style.zIndex = '-99999';
            container.style.opacity = '1';
            container.style.pointerEvents = 'none';

            // Kloning kartu infografis
            const clone = originalCard.cloneNode(true);
            clone.setAttribute('x-ignore', '');
            clone.id = 'rekapPendaftarCard_exportClone';
            
            // Format styling kloning agar tidak terpotong sama sekali
            clone.style.width = '1100px';
            clone.style.minWidth = '1100px';
            clone.style.maxWidth = '1100px';
            clone.style.margin = '0';
            clone.style.padding = '40px 44px 48px 44px';
            clone.style.boxSizing = 'border-box';
            clone.style.backgroundColor = '#0C111D';
            clone.style.borderRadius = '24px';
            clone.style.overflow = 'visible';

            // Bersihkan semua atribut Alpine (x-show, x-data, dll) dari klon agar MutationObserver Alpine tidak menyembunyikan baris
            clone.removeAttribute('x-data');
            clone.querySelectorAll('*').forEach(el => {
                Array.from(el.attributes).forEach(attr => {
                    if (attr.name.startsWith('x-')) {
                        el.removeAttribute(attr.name);
                    }
                });
            });

            // Pastikan seluruh baris data tabel yang sesuai filter aktif tampil 100% utuh
            const activeCat = this.recapCategory || 'all';
            const allRows = clone.querySelectorAll('tbody tr');
            allRows.forEach(row => {
                const rowCat = row.getAttribute('data-category') || '';
                if (activeCat === 'all' || rowCat === activeCat) {
                    row.style.setProperty('display', 'table-row', 'important');
                    row.style.setProperty('visibility', 'visible', 'important');
                    row.style.setProperty('opacity', '1', 'important');
                    row.removeAttribute('hidden');
                } else {
                    row.style.setProperty('display', 'none', 'important');
                }
            });

            // Pastikan kontainer tabel di dalam klon tidak memiliki scrollbar atau pemotongan overflow
            const tableContainers = clone.querySelectorAll('.rekap-table-container');
            tableContainers.forEach(el => {
                el.style.overflow = 'visible';
                el.style.overflowX = 'visible';
                el.style.width = '100%';
            });

            container.appendChild(clone);
            document.body.appendChild(container);

            // Berikan waktu 250ms untuk layout reflow, webfonts, dan aset gambar ter-render sempurna
            await new Promise(resolve => setTimeout(resolve, 250));

            // Ukur dimensi elemen sesungguhnya secara akurat
            const exportWidth = clone.offsetWidth || 1100;
            const exportHeight = (clone.scrollHeight || clone.offsetHeight) + 12;

            // Generate gambar beresolusi tinggi dengan htmlToImage
            const dataUrl = await htmlToImage.toPng(clone, {
                pixelRatio: 2,
                width: exportWidth,
                height: exportHeight,
                backgroundColor: '#0C111D',
                cacheBust: true,
                imagePlaceholder: 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII='
            });

            // Eksekusi download file PNG
            const link = document.createElement('a');
            const now = new Date();
            const pad = (n) => String(n).padStart(2, '0');
            const timeTag = `${now.getFullYear()}${pad(now.getMonth()+1)}${pad(now.getDate())}_${pad(now.getHours())}${pad(now.getMinutes())}`;
            link.download = `REKAP-PENDAFTAR-TALENTA-2026-${timeTag}.png`;
            link.href = dataUrl;
            link.click();

            if (typeof confetti === 'function') {
                confetti({ particleCount: 60, spread: 70, origin: { y: 0.7 } });
            }
        } catch (err) {
            console.error('Error saat mengekspor gambar via htmlToImage:', err);
            alert('Gagal membuat file gambar: ' + (err.message || err));
        } finally {
            if (container && container.parentNode) {
                container.parentNode.removeChild(container);
            }
            this.downloadingPng = false;
        }
    },
    items: @js($allRegistrations->map(function($r) {
        $firstMember = $r->members->first();
        return [
            'id' => $r->id,
            'comp_id' => (string) $r->competition_id,
            'status' => $r->status,
            'search' => strtolower(($firstMember?->full_name ?? '') . ' ' . ($r->team_name ?? '') . ' ' . $r->members->pluck('full_name')->implode(' ') . ' ' . ($firstMember?->nisn ?? '') . ' ' . $r->display_school . ' ' . $r->institution_name . ' ' . $r->registration_code . ' ' . ($r->competition->name ?? '') . ' ' . ($r->sub_category ?? ''))
        ];
    })),
    pesertaCurrentPage: 1,
    pesertaPerPage: 10,
    init() {
        this.$watch('searchQuery', () => { this.pesertaCurrentPage = 1; });
        this.$watch('selectedCategory', () => { this.pesertaCurrentPage = 1; });
        this.$watch('selectedStatus', () => { this.pesertaCurrentPage = 1; });
        this.$watch('pesertaPerPage', () => { this.pesertaCurrentPage = 1; });
    },
    get filteredItems() {
        const query = (this.searchQuery || '').toLowerCase().trim();
        return this.items.filter(item => {
            const matchComp = (this.selectedCategory === 'all' || item.comp_id === String(this.selectedCategory));
            const matchStatus = (this.selectedStatus === 'all' || item.status === this.selectedStatus);
            const matchSearch = (!query || item.search.includes(query));
            return matchComp && matchStatus && matchSearch;
        });
    },
    get pesertaTotalPages() {
        return Math.max(1, Math.ceil(this.filteredItems.length / this.pesertaPerPage));
    },
    get paginatedPesertaList() {
        const page = Math.min(Math.max(1, this.pesertaCurrentPage), this.pesertaTotalPages);
        const start = (page - 1) * this.pesertaPerPage;
        return this.filteredItems.slice(start, start + this.pesertaPerPage);
    },
    get paginatedPesertaIds() {
        return new Set(this.paginatedPesertaList.map(i => i.id));
    },
    isItemVisible(id) {
        return this.paginatedPesertaIds.has(id);
    },
    goToPesertaPage(p) {
        if (typeof p !== 'number') return;
        if (p < 1) p = 1;
        if (p > this.pesertaTotalPages) p = this.pesertaTotalPages;
        this.pesertaCurrentPage = p;
        const el = document.getElementById('recapPesertaTableCard');
        if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    },
    prevPesertaPage() {
        if (this.pesertaCurrentPage > 1) this.goToPesertaPage(this.pesertaCurrentPage - 1);
    },
    nextPesertaPage() {
        if (this.pesertaCurrentPage < this.pesertaTotalPages) this.goToPesertaPage(this.pesertaCurrentPage + 1);
    },
    get pesertaPaginationPages() {
        const total = this.pesertaTotalPages;
        const current = Math.min(Math.max(1, this.pesertaCurrentPage), total);
        if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1);
        if (current <= 4) return [1, 2, 3, 4, 5, '...', total];
        if (current >= total - 3) return [1, '...', total - 4, total - 3, total - 2, total - 1, total];
        return [1, '...', current - 1, current, current + 1, '...', total];
    },
    get pesertaPaginationStart() {
        if (this.filteredItems.length === 0) return 0;
        const page = Math.min(Math.max(1, this.pesertaCurrentPage), this.pesertaTotalPages);
        return (page - 1) * this.pesertaPerPage + 1;
    },
    get pesertaPaginationEnd() {
        const page = Math.min(Math.max(1, this.pesertaCurrentPage), this.pesertaTotalPages);
        return Math.min(page * this.pesertaPerPage, this.filteredItems.length);
    },
    resetFilters() {
        this.searchQuery = '';
        this.selectedCategory = 'all';
        this.selectedStatus = 'all';
        this.pesertaCurrentPage = 1;
    }
}">

    <!-- Compact Global Overview Stat Cards (Ultra Slim ~50px) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <!-- Card 1: Total Pendaftar -->
        <div class="ai-card rounded-2xl p-3 border border-white/[0.08] shadow-md flex items-center justify-between hover:border-[#4E6EFF]/50 transition">
            <div class="space-y-0.5">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Total Pendaftar</span>
                <div class="flex items-baseline gap-1.5">
                    <span class="text-base sm:text-lg font-black text-white font-mono">{{ $grandTotals['total_registrations'] }}</span>
                    <span class="text-[10px] text-slate-400 font-medium">/ {{ $grandTotals['total_quota'] }} Kuota</span>
                </div>
            </div>
            <div class="w-7 h-7 rounded-lg bg-[#4E6EFF]/15 text-[#84D0FF] border border-[#4E6EFF]/30 flex items-center justify-center shrink-0">
                <i data-lucide="users" class="w-3.5 h-3.5"></i>
            </div>
        </div>

        <!-- Card 2: Terverifikasi (Lunas) -->
        <div class="ai-card rounded-2xl p-3 border border-white/[0.08] shadow-md flex items-center justify-between hover:border-emerald-500/50 transition">
            <div class="space-y-0.5">
                <span class="text-[10px] font-bold text-emerald-400 uppercase tracking-wider block">Terverifikasi (Lunas)</span>
                <div class="flex items-baseline gap-1.5">
                    <span class="text-base sm:text-lg font-black text-emerald-400 font-mono">{{ $grandTotals['verified_registrations'] }}</span>
                    <span class="text-[10px] text-emerald-300/80 font-medium">Siswa Valid</span>
                </div>
            </div>
            <div class="w-7 h-7 rounded-lg bg-emerald-500/15 text-emerald-400 border border-emerald-500/30 flex items-center justify-center shrink-0">
                <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
            </div>
        </div>

        <!-- Card 3: Uang Pendaftaran Masuk (Lunas) -->
        <div class="ai-card rounded-2xl p-3 border border-white/[0.08] shadow-md flex items-center justify-between hover:border-emerald-500/50 transition">
            <div class="space-y-0.5">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Dana Masuk (Lunas)</span>
                <div>
                    <span class="text-sm sm:text-base font-black text-emerald-400 font-mono">Rp {{ number_format($grandTotals['verified_income'], 0, ',', '.') }}</span>
                </div>
            </div>
            <div class="w-7 h-7 rounded-lg bg-emerald-500/15 text-emerald-400 border border-emerald-500/30 flex items-center justify-center shrink-0">
                <i data-lucide="wallet" class="w-3.5 h-3.5"></i>
            </div>
        </div>

        <!-- Card 4: Potensi Total Dana -->
        <div class="ai-card rounded-2xl p-3 border border-white/[0.08] shadow-md flex items-center justify-between bg-gradient-to-tr from-[#7A5AF8]/20 to-[#4E6EFF]/20 hover:border-[#7A5AF8]/50 transition">
            <div class="space-y-0.5">
                <span class="text-[10px] font-bold text-[#A594FD] uppercase tracking-wider block">Total Potensi Dana</span>
                <div>
                    <span class="text-sm sm:text-base font-black text-white font-mono">Rp {{ number_format($grandTotals['total_potential_income'], 0, ',', '.') }}</span>
                </div>
            </div>
            <div class="w-7 h-7 rounded-lg bg-[#7A5AF8]/20 text-white border border-[#7A5AF8]/30 flex items-center justify-center shrink-0">
                <i data-lucide="coins" class="w-3.5 h-3.5"></i>
            </div>
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

        <button @click="activeTab = 'pendaftar'" :class="activeTab === 'pendaftar' ? 'bg-gradient-to-r from-[#7A5AF8] to-[#4E6EFF] text-white shadow-md shadow-[#7A5AF8]/30 font-black' : 'text-slate-400 hover:text-white'" class="flex items-center gap-2 px-5 py-3 rounded-2xl text-xs sm:text-sm transition cursor-pointer">
            <i data-lucide="layout-grid" class="w-4 h-4 text-cyan-400"></i>
            <span>3. Rekap Pendaftar</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-black" :class="activeTab === 'pendaftar' ? 'bg-white text-slate-950' : 'bg-cyan-500/20 text-cyan-300'">Infografis</span>
        </button>

        <button @click="activeTab = 'juara'" :class="activeTab === 'juara' ? 'bg-gradient-to-r from-[#7A5AF8] to-[#4E6EFF] text-white shadow-md shadow-[#7A5AF8]/30 font-black' : 'text-slate-400 hover:text-white'" class="flex items-center gap-2 px-5 py-3 rounded-2xl text-xs sm:text-sm transition cursor-pointer">
            <i data-lucide="medal" class="w-4 h-4 text-[#FF58D5]"></i>
            <span>4. Rekap Semua Peraih Juara</span>
        </button>

        <button @click="activeTab = 'juara-umum'" :class="activeTab === 'juara-umum' ? 'bg-gradient-to-r from-[#7A5AF8] to-[#4E6EFF] text-white shadow-md shadow-[#7A5AF8]/30 font-black' : 'text-slate-400 hover:text-white'" class="flex items-center gap-2 px-5 py-3 rounded-2xl text-xs sm:text-sm transition cursor-pointer">
            <i data-lucide="trophy" class="w-4 h-4 text-amber-400"></i>
            <span>5. Rekap Juara Umum</span>
        </button>
    </div>

    <!-- ==================== TAB 1: REKAP KEUANGAN & LOMBA ==================== -->
    <div x-show="activeTab === 'keuangan'" x-transition class="space-y-6">
        <div class="ai-card rounded-3xl border border-white/[0.08] shadow-xl p-5 sm:p-7 lg:p-8 space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-white/[0.08] pb-4">
                <div>
                    <h3 class="text-lg font-black text-white">Rekapitulasi Keuangan & Kuota Pendaftaran Cabang Lomba</h3>
                    <p class="text-xs text-slate-400">Rincian pendapatan registrasi & kuota (klik baris bertanda <span class="text-[#A594FD] font-bold">Rincian Kelas</span> untuk melihat pembagian kategori/kelas)</p>
                </div>
                <div class="flex items-center gap-3 self-end sm:self-auto">
                    <button type="button" @click="toggleAllBranches()" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white/[0.06] hover:bg-white/[0.12] text-slate-300 hover:text-white text-xs font-bold border border-white/[0.08] transition cursor-pointer">
                        <i data-lucide="layers" class="w-3.5 h-3.5 text-[#84D0FF]"></i>
                        <span x-text="areAllBranchesExpanded() ? 'Tutup Semua Rincian Kelas' : 'Buka Semua Rincian Kelas'"></span>
                    </button>
                    <div class="text-right border-l border-white/[0.08] pl-3">
                        <span class="text-[10px] text-slate-400 block uppercase font-bold">Total Dana Lunas</span>
                        <span class="text-base sm:text-lg font-black text-emerald-400 font-mono">Rp {{ number_format($grandTotals['verified_income'], 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto rounded-2xl border border-white/[0.08] bg-[#0A0E1A]/40 shadow-inner">
                <table class="w-full min-w-[950px] text-left text-xs text-slate-300 border-collapse">
                    <thead class="text-[10px] font-bold uppercase tracking-wider bg-[#0C111D]/90 text-slate-400 border-b border-white/[0.08]">
                        <tr>
                            <th class="py-3.5 px-4 whitespace-nowrap w-[110px]">KODE</th>
                            <th class="py-3.5 px-4 whitespace-nowrap min-w-[220px]">NAMA CABANG LOMBA</th>
                            <th class="py-3.5 px-4 text-center whitespace-nowrap w-[100px]">KUOTA</th>
                            <th class="py-3.5 px-4 text-center whitespace-nowrap w-[110px]">PENDAFTAR</th>
                            <th class="py-3.5 px-4 text-center text-emerald-400 whitespace-nowrap w-[130px]">VERIFIKASI (LUNAS)</th>
                            <th class="py-3.5 px-4 text-center text-amber-400 whitespace-nowrap w-[100px]">PENDING</th>
                            <th class="py-3.5 px-4 text-right whitespace-nowrap w-[140px]">DANA LUNAS MASUK</th>
                            <th class="py-3.5 px-4 text-right whitespace-nowrap w-[140px]">POTENSI TOTAL</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/[0.04] font-medium">
                        @foreach($financeRecap as $item)
                            @php
                                $c = $item['competition'];
                                $hasBreakdown = !empty($item['breakdown']);
                                $fillPercent = $item['quota'] > 0 ? min(100, round(($item['total_regs'] / $item['quota']) * 100)) : 0;
                            @endphp
                            <!-- Main Competition Row -->
                            <tr class="hover:bg-white/[0.025] transition {{ $hasBreakdown ? 'cursor-pointer' : '' }}"
                                @if($hasBreakdown) @click="toggleBranch('{{ $c->code }}')" @endif>
                                <td class="py-3.5 px-4 font-mono font-bold text-[#84D0FF] whitespace-nowrap">
                                    <div class="flex items-center gap-1.5">
                                        @if($hasBreakdown)
                                            <button type="button" class="w-5 h-5 rounded-md bg-white/[0.08] text-slate-300 hover:text-white flex items-center justify-center text-[10px] transition shrink-0" :class="expandedBranches['{{ $c->code }}'] ? 'bg-[#7A5AF8] text-white rotate-90' : ''">
                                                <i data-lucide="chevron-right" class="w-3 h-3 transition-transform duration-200"></i>
                                            </button>
                                        @else
                                            <span class="w-5 h-5 inline-block shrink-0"></span>
                                        @endif
                                        <span>{{ $c->code }}</span>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 min-w-[220px]">
                                    <div class="flex items-center gap-2">
                                        <div>
                                            <span class="font-bold text-white text-sm block">{{ $c->name }}</span>
                                            <span class="text-[11px] text-slate-400">{{ $c->category->name ?? 'Lomba' }} • PIC: {{ $c->pic->name ?? '-' }}</span>
                                        </div>
                                        @if($hasBreakdown)
                                            <span class="px-2 py-0.5 rounded-md text-[9px] font-bold bg-[#7A5AF8]/15 text-[#A594FD] border border-[#7A5AF8]/30 whitespace-nowrap flex items-center gap-1">
                                                <i data-lucide="layers" class="w-2.5 h-2.5"></i>
                                                <span x-text="expandedBranches['{{ $c->code }}'] ? 'Tutup Kelas' : 'Rincian Kelas'"></span>
                                            </span>
                                        @endif
                                    </div>
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

                            <!-- Sub-Rows Accordion Breakdown (Khusus BLT & TMJ) -->
                            @if($hasBreakdown)
                                @foreach($item['breakdown'] as $sub)
                                    <tr x-show="expandedBranches['{{ $c->code }}']" class="bg-[#080D18]/95 border-t border-b border-white/[0.04]">
                                        <td class="py-2.5 px-4 text-center whitespace-nowrap">
                                            <span class="text-[#A594FD] font-mono text-xs">↳</span>
                                        </td>
                                        <td class="py-2.5 px-4 whitespace-nowrap pl-6">
                                            <div class="flex items-center gap-2">
                                                <span class="w-1.5 h-1.5 rounded-full bg-[#7A5AF8]"></span>
                                                <span class="text-xs font-bold text-slate-200">{{ $sub['name'] }}</span>
                                            </div>
                                        </td>
                                        <td class="py-2.5 px-4 text-center text-[10px] text-slate-500 whitespace-nowrap">-</td>
                                        <td class="py-2.5 px-4 text-center font-bold text-white text-xs whitespace-nowrap">
                                            {{ $sub['total_regs'] }} <span class="text-[10px] text-slate-400 font-normal">psrt</span>
                                        </td>
                                        <td class="py-2.5 px-4 text-center whitespace-nowrap">
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-500/15 text-emerald-400 border border-emerald-500/25">
                                                {{ $sub['verified_count'] }}
                                            </span>
                                        </td>
                                        <td class="py-2.5 px-4 text-center whitespace-nowrap">
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold {{ $sub['pending_count'] > 0 ? 'bg-amber-500/15 text-amber-400 border border-amber-500/25' : 'text-slate-500' }}">
                                                {{ $sub['pending_count'] }}
                                            </span>
                                        </td>
                                        <td class="py-2.5 px-4 text-right font-black text-emerald-400 font-mono text-xs whitespace-nowrap">
                                            Rp {{ number_format($sub['verified_income'], 0, ',', '.') }}
                                        </td>
                                        <td class="py-2.5 px-4 text-right font-bold text-slate-300 font-mono text-xs whitespace-nowrap">
                                            Rp {{ number_format($sub['total_income'], 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
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
        <div id="recapPesertaTableCard" class="ai-card rounded-3xl border border-white/[0.08] shadow-xl p-5 sm:p-7 lg:p-8 space-y-6 scroll-mt-6">
            
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
                                        @if($reg->isGanda())
                                            {{ $reg->team_name ?: $reg->display_name }}
                                        @else
                                            {{ $reg->members->first()?->full_name ?: ($reg->team_name ?: 'Peserta #' . $reg->id) }}
                                        @endif
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
                                        {{ $reg->display_school }}
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
                        <tr x-show="filteredItems.length === 0 && {{ $allRegistrations->count() }} > 0" x-cloak>
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

            <!-- Table Pagination Footer (Tailwind Style) -->
            <div class="pt-4 border-t border-white/[0.08] flex flex-col md:flex-row items-center justify-between gap-4">
                <!-- Left: Info & Per Page Selector -->
                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-3 text-xs text-slate-400">
                    <div>
                        Menampilkan <span class="font-bold text-white font-mono" x-text="pesertaPaginationStart"></span>
                        sampai <span class="font-bold text-white font-mono" x-text="pesertaPaginationEnd"></span>
                        dari <span class="font-bold text-[#84D0FF] font-mono" x-text="filteredItems.length"></span> peserta
                        <span x-show="filteredItems.length < items.length" class="text-slate-500 text-[11px]">
                            (total data: <span x-text="items.length"></span>)
                        </span>
                    </div>

                    <span class="text-white/[0.1] hidden sm:inline">•</span>

                    <!-- Rows Per Page Selector -->
                    <div class="flex items-center gap-1.5">
                        <span class="text-[11px] text-slate-400">Tampilkan:</span>
                        <select x-model.number="pesertaPerPage" class="px-2.5 py-1 rounded-xl bg-[#161F30] border border-white/[0.12] text-xs font-bold text-slate-200 focus:border-[#7A5AF8] outline-none cursor-pointer">
                            <option :value="10">10 baris</option>
                            <option :value="25">25 baris</option>
                            <option :value="50">50 baris</option>
                            <option :value="100">100 baris</option>
                            <option :value="999999">Semua</option>
                        </select>
                    </div>
                </div>

                <!-- Right: Pagination Buttons -->
                <div class="flex items-center gap-1 sm:gap-1.5" x-show="pesertaTotalPages > 1">
                    <!-- Prev Button -->
                    <button 
                        type="button" 
                        @click="prevPesertaPage()" 
                        :disabled="pesertaCurrentPage === 1"
                        :class="pesertaCurrentPage === 1 ? 'opacity-30 cursor-not-allowed text-slate-500 border-white/[0.04]' : 'hover:bg-white/[0.1] text-slate-200 border-white/[0.1] hover:text-white cursor-pointer'"
                        class="px-2.5 sm:px-3 py-1.5 rounded-xl text-xs font-bold border transition flex items-center gap-1 bg-white/[0.04]"
                        title="Halaman Sebelumnya">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
                        <span class="hidden sm:inline">Sebelumnya</span>
                    </button>

                    <!-- Page Numbers -->
                    <div class="flex items-center gap-1">
                        <template x-for="(p, idx) in pesertaPaginationPages" :key="idx">
                            <div>
                                <!-- Ellipsis -->
                                <template x-if="p === '...'">
                                    <span class="px-2 py-1 text-slate-500 text-xs font-bold select-none">...</span>
                                </template>
                                <!-- Number Button -->
                                <template x-if="p !== '...'">
                                    <button 
                                        type="button" 
                                        @click="goToPesertaPage(p)" 
                                        :class="pesertaCurrentPage === p ? 'bg-gradient-to-r from-[#7A5AF8] to-[#4E6EFF] text-white font-black shadow-md shadow-[#7A5AF8]/30 border-transparent' : 'bg-white/[0.04] hover:bg-white/[0.1] text-slate-300 hover:text-white border-white/[0.08]'"
                                        class="min-w-[32px] sm:min-w-[34px] h-[32px] sm:h-[34px] px-2 rounded-xl text-xs font-bold border transition flex items-center justify-center cursor-pointer"
                                        x-text="p">
                                    </button>
                                </template>
                            </div>
                        </template>
                    </div>

                    <!-- Next Button -->
                    <button 
                        type="button" 
                        @click="nextPesertaPage()" 
                        :disabled="pesertaCurrentPage === pesertaTotalPages"
                        :class="pesertaCurrentPage === pesertaTotalPages ? 'opacity-30 cursor-not-allowed text-slate-500 border-white/[0.04]' : 'hover:bg-white/[0.1] text-slate-200 border-white/[0.1] hover:text-white cursor-pointer'"
                        class="px-2.5 sm:px-3 py-1.5 rounded-xl text-xs font-bold border transition flex items-center gap-1 bg-white/[0.04]"
                        title="Halaman Berikutnya">
                        <span class="hidden sm:inline">Berikutnya</span>
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ==================== TAB 3: REKAP PENDAFTAR (INFOGRAFIS LIVE & EXPORT PNG) ==================== -->
    <div x-show="activeTab === 'pendaftar'" x-transition class="space-y-6">
        
        <!-- Top Toolbar / Action Bar -->
        <div class="ai-card rounded-3xl p-4 sm:p-5 border border-white/[0.08] shadow-lg flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-cyan-500/15 border border-cyan-500/30 flex items-center justify-center text-cyan-400 shrink-0 shadow-inner">
                    <i data-lucide="layout-grid" class="w-5 h-5"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        <h3 class="text-sm sm:text-base font-extrabold text-white">Infografis Live Kuota Pendaftar</h3>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-cyan-500/20 text-cyan-300 border border-cyan-500/30 font-mono">HD Export</span>
                    </div>
                    <p class="text-xs text-slate-400 mt-0.5">Tampilan infografis resmi kuota cabang lomba TALENTA 2026. Siap diunduh sebagai gambar PNG untuk publikasi.</p>
                </div>
            </div>

            <!-- Controls: Filter & Download PNG Button -->
            <div class="flex flex-wrap items-center gap-2.5 w-full md:w-auto justify-end">
                <!-- Category Filter Pills -->
                <div class="flex items-center p-1 rounded-2xl bg-white/[0.04] border border-white/[0.08] text-xs font-bold">
                    <button type="button" @click="recapCategory = 'all'" :class="recapCategory === 'all' ? 'bg-[#7A5AF8] text-white shadow-xs' : 'text-slate-400 hover:text-white'" class="px-3 py-1.5 rounded-xl transition cursor-pointer">Semua</button>
                    <button type="button" @click="recapCategory = 'olahraga'" :class="recapCategory === 'olahraga' ? 'bg-[#7A5AF8] text-white shadow-xs' : 'text-slate-400 hover:text-white'" class="px-3 py-1.5 rounded-xl transition cursor-pointer">Olahraga</button>
                    <button type="button" @click="recapCategory = 'seni'" :class="recapCategory === 'seni' ? 'bg-[#7A5AF8] text-white shadow-xs' : 'text-slate-400 hover:text-white'" class="px-3 py-1.5 rounded-xl transition cursor-pointer">Seni</button>
                    <button type="button" @click="recapCategory = 'teknologi'" :class="recapCategory === 'teknologi' ? 'bg-[#7A5AF8] text-white shadow-xs' : 'text-slate-400 hover:text-white'" class="px-3 py-1.5 rounded-xl transition cursor-pointer">Teknologi</button>
                </div>

                <!-- Download PNG Button -->
                <button type="button" 
                        @click="downloadPNG()" 
                        :disabled="downloadingPng"
                        class="px-5 py-2.5 rounded-2xl bg-gradient-to-r from-[#7A5AF8] via-[#6941C6] to-[#4E6EFF] hover:from-[#6941C6] hover:to-[#3538CD] text-white font-black text-xs sm:text-sm shadow-lg shadow-[#7A5AF8]/30 border border-white/[0.15] flex items-center gap-2.5 transition duration-200 cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed">
                    <template x-if="!downloadingPng">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                            <span>Download PNG</span>
                        </span>
                    </template>
                    <template x-if="downloadingPng">
                        <span class="flex items-center gap-2">
                            <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <span>Memproses Gambar...</span>
                        </span>
                    </template>
                </button>
            </div>
        </div>

        <!-- ==================== EXPORTABLE INFOGRAPHIC CARD CONTAINER ==================== -->
        <div id="rekapPendaftarCard" class="w-full max-w-5xl mx-auto rounded-3xl p-6 sm:p-8 lg:p-10 pb-8 sm:pb-10 space-y-7 relative overflow-hidden shadow-2xl border border-white/[0.12]" style="background-color: #0C111D; color: #F8FAFC;">
            
            <!-- Ambient Glow for Aesthetic Quality -->
            <div class="absolute inset-0 bg-gradient-to-b from-[#7A5AF8]/10 via-[#4E6EFF]/5 to-transparent pointer-events-none"></div>

            <!-- HEADER / JUDUL INFOGRAFIS SESUAI INSTRUKSI -->
            <div class="text-center relative z-10 space-y-1 sm:space-y-1.5">
                @php
                    $recapHeaderLogo = !empty($appSettings['event_logo']) ? $appSettings['event_logo'] : (!empty($appSettings['app_logo']) ? $appSettings['app_logo'] : null);
                @endphp
                @if(!empty($recapHeaderLogo))
                    <div class="flex items-center justify-center mb-2">
                        <img src="{{ asset('storage/' . $recapHeaderLogo) }}" 
                             alt="Logo" 
                             crossorigin="anonymous"
                             class="h-28 sm:h-36 md:h-40 w-auto max-w-[340px] sm:max-w-[400px] object-contain drop-shadow-2xl">
                    </div>
                @endif

                <div class="text-2xl sm:text-3xl lg:text-4xl font-black tracking-wider text-white uppercase font-display drop-shadow-md leading-tight whitespace-nowrap">
                    REKAPITULASI
                </div>
                <div class="text-base sm:text-lg lg:text-xl font-extrabold tracking-wide text-transparent bg-clip-text bg-gradient-to-r from-blue-200 via-indigo-200 to-purple-200 uppercase leading-tight whitespace-nowrap">
                    PENDAFTAR PERLOMBAAN &amp; PERTANDINGAN
                </div>
                <div class="text-xl sm:text-2xl lg:text-3xl font-black tracking-wide text-amber-400 uppercase font-display drop-shadow-sm leading-tight whitespace-nowrap inline-block">
                    TALENTA MILAD KE-57
                </div>
                <div class="text-sm sm:text-base font-extrabold tracking-widest text-slate-300 uppercase leading-tight whitespace-nowrap">
                    {{ $appSettings['institution_name'] ?? 'MTSN 1 BLITAR' }}
                </div>
                <div class="pt-0.5">
                    <span class="inline-block px-5 py-0.5 rounded-full bg-white/[0.08] border border-white/[0.15] text-xs sm:text-sm font-black text-[#84D0FF] tracking-widest uppercase font-mono leading-normal">
                        2026
                    </span>
                </div>

                <!-- Update Timestamp Badge -->
                <div class="pt-2">
                    <div class="inline-flex items-center gap-2 px-5 py-2 rounded-full bg-emerald-500/15 border border-emerald-500/30 text-emerald-300 text-xs sm:text-sm font-bold shadow-sm font-mono whitespace-nowrap">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span>Update : {{ \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM Y, [Pukul] HH:mm') }} WIB</span>
                    </div>
                </div>
            </div>

            <!-- Divider Line -->
            <div class="relative z-10 w-full h-[2px] bg-gradient-to-r from-transparent via-[#7A5AF8]/60 to-transparent my-5"></div>

            <!-- TABEL KUOTA REKAPITULASI (PERSIS LANDING PAGE) -->
            @php
                $renderTierQuota = function($count, $quota, $unit = 'Peserta') {
                    $quota = (int) $quota;
                    $isUnlimited = ($quota <= 0);
                    if ($isUnlimited) {
                        $hasParticipants = $count > 0;
                        $barWidth = $hasParticipants ? min(100, max(25, $count * 10)) : 0;
                        $barHtml = $hasParticipants 
                            ? '<div class="bg-gradient-to-r from-purple-500 via-indigo-500 to-[#4E6EFF] h-full rounded-full shadow-sm shadow-purple-500/30" style="width: ' . $barWidth . '%"></div>'
                            : '<div class="bg-slate-700/40 h-full rounded-full" style="width: 0%"></div>';
                            
                        return '
                        <div class="w-full">
                            <div class="flex items-center justify-between text-sm sm:text-base font-bold gap-3">
                                <span class="text-purple-300 font-black flex items-center gap-1.5 shrink-0">
                                    <span class="text-base sm:text-lg leading-none font-sans">∞</span>
                                    <span>Tak Terbatas</span>
                                </span>
                                <span class="text-slate-300 font-bold text-xs sm:text-sm font-mono shrink-0 pr-1.5">' . $count . ' / ∞</span>
                            </div>
                            <div class="w-full bg-white/[0.08] h-3 rounded-full overflow-hidden mt-1.5 p-0.5 border border-white/[0.05]">
                                ' . $barHtml . '
                            </div>
                        </div>';
                    } else {
                        $sisa = max(0, $quota - $count);
                        $isFull = ($sisa <= 0);
                        $isLow = ($sisa > 0 && $sisa <= 5);
                        $textColor = $isFull ? 'text-rose-400 font-black' : ($isLow ? 'text-amber-300 font-black' : 'text-emerald-400 font-black');
                        $sisaText = $isFull ? 'Penuh' : 'Sisa: ' . $sisa . ' ' . $unit;
                        $barWidth = min(100, ($count / max(1, $quota)) * 100);
                        $barGradient = $isFull ? 'from-rose-500 to-red-600' : ($isLow ? 'from-amber-400 to-orange-500' : 'from-[#7A5AF8] to-[#4E6EFF]');
                        return '
                        <div class="w-full">
                            <div class="flex items-center justify-between text-sm sm:text-base font-bold gap-3">
                                <span class="' . $textColor . ' shrink-0">' . $sisaText . '</span>
                                <span class="text-slate-300 font-bold text-xs sm:text-sm font-mono shrink-0 pr-1.5">' . $count . '/' . $quota . '</span>
                            </div>
                            <div class="w-full bg-white/[0.08] h-3 rounded-full overflow-hidden mt-1.5 p-0.5 border border-white/[0.05]">
                                <div class="bg-gradient-to-r ' . $barGradient . ' h-full rounded-full transition-all duration-300" style="width: ' . $barWidth . '%"></div>
                            </div>
                        </div>';
                    }
                };
            @endphp

            <div class="rekap-table-container relative z-10 overflow-x-auto rounded-2xl border border-white/[0.08] bg-[#0A0E1A]/80 shadow-xl">
                <table class="w-full text-left text-sm sm:text-base text-slate-300 border-collapse">
                    <thead class="text-xs sm:text-sm font-black uppercase tracking-wider bg-[#0C111D]/95 text-slate-200 border-b border-white/[0.08]">
                        <tr>
                            <th class="py-3.5 px-5 whitespace-nowrap w-[32%] min-w-[270px]">Nama Lomba</th>
                            <th class="py-3.5 px-5 whitespace-nowrap w-[26%] min-w-[220px]">Kategori</th>
                            <th class="py-3.5 px-5 whitespace-nowrap w-[42%] min-w-[340px]">Sisa Kuota</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/[0.05] font-medium text-sm sm:text-base">
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

                                $categorySlug = $comp->category->slug ?? '';
                                $rowTheme = match($categorySlug) {
                                    'seni' => [
                                        'bg' => 'bg-pink-500/[0.03]',
                                        'border_l' => 'border-l-4 border-l-pink-500/80',
                                        'badge' => 'bg-pink-500/15 text-pink-300 border border-pink-500/30',
                                        'icon_color' => 'text-pink-400',
                                    ],
                                    'olahraga' => [
                                        'bg' => 'bg-emerald-500/[0.025]',
                                        'border_l' => 'border-l-4 border-l-emerald-500/80',
                                        'badge' => 'bg-emerald-500/15 text-emerald-300 border border-emerald-500/30',
                                        'icon_color' => 'text-emerald-400',
                                    ],
                                    'teknologi' => [
                                        'bg' => 'bg-[#4E6EFF]/[0.03]',
                                        'border_l' => 'border-l-4 border-l-[#4E6EFF]/80',
                                        'badge' => 'bg-[#4E6EFF]/15 text-[#84D0FF] border border-[#4E6EFF]/30',
                                        'icon_color' => 'text-[#4E6EFF]',
                                    ],
                                    'pramuka' => [
                                        'bg' => 'bg-amber-500/[0.03]',
                                        'border_l' => 'border-l-4 border-l-amber-500/80',
                                        'badge' => 'bg-amber-500/15 text-amber-300 border border-amber-500/30',
                                        'icon_color' => 'text-amber-400',
                                    ],
                                    'tahfidz' => [
                                        'bg' => 'bg-teal-500/[0.03]',
                                        'border_l' => 'border-l-4 border-l-teal-500/80',
                                        'badge' => 'bg-teal-500/15 text-teal-300 border border-teal-500/30',
                                        'icon_color' => 'text-teal-400',
                                    ],
                                    default => [
                                        'bg' => 'bg-[#7A5AF8]/[0.03]',
                                        'border_l' => 'border-l-4 border-l-[#7A5AF8]/80',
                                        'badge' => 'bg-[#7A5AF8]/15 text-[#A594FD] border border-[#7A5AF8]/30',
                                        'icon_color' => 'text-[#A594FD]',
                                    ],
                                };
                            @endphp

                            <tr data-category="{{ $comp->category->slug ?? '' }}" x-show="recapCategory === 'all' || recapCategory === '{{ $comp->category->slug ?? '' }}'" class="{{ $rowTheme['bg'] }} {{ $rowTheme['border_l'] }} transition-colors duration-150 border-b border-white/[0.05]">
                                
                                <!-- Nama Lomba & Lokasi -->
                                <td class="py-3.5 px-5 w-[32%] min-w-[270px] align-middle">
                                    <div class="flex items-center gap-2">
                                        <span class="font-black text-white text-base sm:text-lg leading-snug whitespace-nowrap block">
                                            {{ $comp->name }}
                                        </span>
                                        @if($comp->code === 'MIPA')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-black bg-gradient-to-r from-amber-400 to-amber-500 text-slate-950 shadow-sm shadow-amber-500/20 shrink-0 whitespace-nowrap">
                                                🎁 Bonus 10 Get 1
                                            </span>
                                        @endif
                                    </div>
                                    <div class="text-xs sm:text-sm text-slate-300 font-medium flex items-center gap-1.5 mt-2 whitespace-nowrap">
                                        <svg class="w-4 h-4 text-[#4E6EFF] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                                        <span>{{ $comp->venue ?? 'Kampus MTsN 1 Blitar' }}</span>
                                    </div>
                                </td>

                                <!-- Kategori -->
                                <td class="py-3.5 px-5 whitespace-nowrap align-middle w-[26%] min-w-[220px]">
                                    @if($isBlt)
                                        <div class="flex flex-col py-1">
                                            <!-- Tunggal PA -->
                                            <div class="flex flex-col justify-center">
                                                <div class="flex items-center gap-1.5 font-black text-emerald-400 text-sm mb-1.5">
                                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                                                    <span>Tunggal | PA</span>
                                                </div>
                                                <div class="space-y-1.5 text-xs sm:text-sm font-semibold text-slate-300 pl-5">
                                                    <div class="py-0.5">Kat A (Kelas 1–2)</div>
                                                    <div class="py-0.5">Kat B (Kelas 3–4)</div>
                                                    <div class="py-0.5">Kat C (Kelas 5–6)</div>
                                                </div>
                                            </div>
                                            <div class="border-t border-white/[0.08] my-2"></div>
                                            <!-- Tunggal PI -->
                                            <div class="flex flex-col justify-center">
                                                <div class="flex items-center gap-1.5 font-black text-pink-400 text-sm mb-1.5">
                                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                                                    <span>Tunggal | PI</span>
                                                </div>
                                                <div class="space-y-1.5 text-xs sm:text-sm font-semibold text-slate-300 pl-5">
                                                    <div class="py-0.5">Kat A (Kelas 1–2)</div>
                                                    <div class="py-0.5">Kat B (Kelas 3–4)</div>
                                                    <div class="py-0.5">Kat C (Kelas 5–6)</div>
                                                </div>
                                            </div>
                                            <div class="border-t border-white/[0.08] my-2"></div>
                                            <!-- Ganda PA -->
                                            <div class="py-0.5 flex items-center gap-1.5 font-black text-[#A594FD] text-sm">
                                                <svg class="w-4 h-4 shrink-0 text-[#7A5AF8]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.999-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/></svg>
                                                <span>Ganda | PA</span>
                                            </div>
                                            <div class="border-t border-white/[0.08] my-2"></div>
                                            <!-- Ganda PI -->
                                            <div class="py-0.5 flex items-center gap-1.5 font-black text-amber-300 text-sm">
                                                <svg class="w-4 h-4 shrink-0 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.999-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/></svg>
                                                <span>Ganda | PI</span>
                                            </div>
                                        </div>
                                    @elseif($isMtqPop)
                                        <div class="flex items-center gap-2 font-black text-emerald-400 text-sm sm:text-base py-1">
                                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.999-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/></svg>
                                            <span>Individu (PA &amp; PI)</span>
                                        </div>
                                    @elseif($isTmj)
                                        <div class="flex flex-col py-1">
                                            <!-- Tunggal PA -->
                                            <div class="flex flex-col justify-center">
                                                <div class="flex items-center gap-1.5 font-black text-emerald-400 text-sm mb-1.5">
                                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                                                    <span>Tunggal | PA</span>
                                                </div>
                                                <div class="space-y-1.5 text-xs sm:text-sm font-semibold text-slate-300 pl-5">
                                                    <div class="py-0.5">Kat A (Kelas 1–3)</div>
                                                    <div class="py-0.5">Kat B (Kelas 4–6)</div>
                                                </div>
                                            </div>
                                            <div class="border-t border-white/[0.08] my-2"></div>
                                            <!-- Tunggal PI -->
                                            <div class="flex flex-col justify-center">
                                                <div class="flex items-center gap-1.5 font-black text-pink-400 text-sm mb-1.5">
                                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                                                    <span>Tunggal | PI</span>
                                                </div>
                                                <div class="space-y-1.5 text-xs sm:text-sm font-semibold text-slate-300 pl-5">
                                                    <div class="py-0.5">Kat A (Kelas 1–3)</div>
                                                    <div class="py-0.5">Kat B (Kelas 4–6)</div>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="capitalize text-slate-100 font-extrabold text-sm sm:text-base">{{ $comp->type }}</span>
                                    @endif
                                </td>

                                <!-- Sisa Kuota & Progress Bar -->
                                <td class="py-3.5 px-5 whitespace-nowrap align-middle w-[42%] min-w-[340px]">
                                    @if($isBlt)
                                        <div class="flex flex-col py-1 text-slate-400 text-xs sm:text-sm w-full">
                                            <!-- Kuota Tunggal PA -->
                                            <div class="space-y-1.5">
                                                {!! $renderTierQuota($countBltTunggalPaA, $comp->tier_quotas['A_tunggal_pa'] ?? 16, 'Peserta') !!}
                                                {!! $renderTierQuota($countBltTunggalPaB, $comp->tier_quotas['B_tunggal_pa'] ?? 16, 'Peserta') !!}
                                                {!! $renderTierQuota($countBltTunggalPaC, $comp->tier_quotas['C_tunggal_pa'] ?? 16, 'Peserta') !!}
                                            </div>

                                            <div class="border-t border-white/[0.08] my-2"></div>

                                            <!-- Kuota Tunggal PI -->
                                            <div class="space-y-1.5">
                                                {!! $renderTierQuota($countBltTunggalPiA, $comp->tier_quotas['A_tunggal_pi'] ?? 16, 'Peserta') !!}
                                                {!! $renderTierQuota($countBltTunggalPiB, $comp->tier_quotas['B_tunggal_pi'] ?? 16, 'Peserta') !!}
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
                                        <div class="flex flex-col py-1 text-slate-400 text-xs sm:text-sm w-full space-y-2">
                                            <!-- Main Combined Quota Bar -->
                                            {!! $renderTierQuota($totalMtqPop, $quotaMtqPop, 'Peserta') !!}
                                            
                                            <!-- Gender Composition Breakdown -->
                                            <div class="flex items-center justify-between gap-2 px-3 py-1.5 rounded-xl bg-white/[0.05] border border-white/[0.1] text-xs sm:text-sm font-bold">
                                                <span class="text-cyan-300 flex items-center gap-1.5 shrink-0">
                                                    <svg class="w-3.5 h-3.5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                                                    <span class="font-extrabold">{{ $countPa }} Putra</span>
                                                </span>
                                                <span class="text-slate-500">•</span>
                                                <span class="text-pink-300 flex items-center gap-1.5 shrink-0">
                                                    <svg class="w-3.5 h-3.5 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                                                    <span class="font-extrabold">{{ $countPi }} Putri</span>
                                                </span>
                                            </div>
                                        </div>
                                    @elseif($isTmj)
                                        <div class="flex flex-col py-1 text-slate-400 text-xs sm:text-sm w-full">
                                            <!-- Kuota Tunggal PA -->
                                            <div class="space-y-1.5">
                                                {!! $renderTierQuota($countTmjTunggalPaA, $comp->tier_quotas['A_tunggal_pa'] ?? 10, 'Peserta') !!}
                                                {!! $renderTierQuota($countTmjTunggalPaB, $comp->tier_quotas['B_tunggal_pa'] ?? 10, 'Peserta') !!}
                                            </div>

                                            <div class="border-t border-white/[0.08] my-2"></div>

                                            <!-- Kuota Tunggal PI -->
                                            <div class="space-y-1.5">
                                                {!! $renderTierQuota($countTmjTunggalPiA, $comp->tier_quotas['A_tunggal_pi'] ?? 10, 'Peserta') !!}
                                                {!! $renderTierQuota($countTmjTunggalPiB, $comp->tier_quotas['B_tunggal_pi'] ?? 10, 'Peserta') !!}
                                            </div>
                                        </div>
                                    @else
                                        @php
                                            $regQuota = (int) ($comp->quota ?? 0);
                                            $regCount = $comp->registrations->count();
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
                                            <div class="space-y-1.5 w-full">
                                                <div class="flex items-center justify-between gap-3">
                                                    <span class="text-sm sm:text-base text-purple-300 font-black flex items-center gap-1.5 shrink-0">
                                                        <span class="text-base sm:text-lg leading-none font-sans">∞</span>
                                                        <span>Tak Terbatas</span>
                                                    </span>
                                                    <span class="text-xs sm:text-sm font-bold text-slate-300 font-mono shrink-0 pr-1.5">
                                                        {{ $regCount }} / ∞
                                                    </span>
                                                </div>
                                                <div class="w-full bg-white/[0.08] h-3 rounded-full overflow-hidden p-0.5 border border-white/[0.05]">
                                                    @if($hasRegs)
                                                        <div class="bg-gradient-to-r from-purple-500 via-indigo-500 to-[#4E6EFF] h-full rounded-full shadow-sm shadow-purple-500/30" style="width: {{ $barWidth }}%"></div>
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
                                            <div class="space-y-1.5 w-full">
                                                <div class="flex items-center justify-between gap-3">
                                                    <span class="text-sm sm:text-base {{ $isFull ? 'text-rose-400 font-black' : ($isLow ? 'text-amber-300 font-black' : 'text-emerald-400 font-black') }} shrink-0">
                                                        {{ $isFull ? 'Kuota Penuh' : 'Sisa: ' . $sisa . ' ' . $unitWord }}
                                                    </span>
                                                    <span class="text-xs sm:text-sm font-bold text-slate-300 font-mono shrink-0 pr-1.5">
                                                        {{ $regCount }}/{{ $regQuota }}
                                                    </span>
                                                </div>
                                                <div class="w-full bg-white/[0.08] h-3 rounded-full overflow-hidden p-0.5 border border-white/[0.05]">
                                                    <div class="bg-gradient-to-r {{ $isFull ? 'from-rose-500 to-red-600' : ($isLow ? 'from-amber-400 to-orange-500' : 'from-[#7A5AF8] to-[#4E6EFF]') }} h-full rounded-full transition-all duration-300 shadow-sm" style="width: {{ min(100, ($regCount / max(1, $regQuota)) * 100) }}%"></div>
                                                </div>
                                            </div>
                                        @endif
                                    @endif
                                </td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- ==================== FOOTER: SUPPORTED BY & SPONSORS ==================== -->
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
                <div class="relative z-10 pt-5 border-t border-white/[0.08] space-y-3">
                    <div class="text-center">
                        <h4 class="text-xs sm:text-sm font-extrabold uppercase tracking-widest text-slate-300 font-display flex items-center justify-center gap-2 whitespace-nowrap">
                            <span>{{ rtrim($appSettings['sponsor_title'] ?? 'Supported by', ' :') }}:</span>
                        </h4>
                    </div>

                    <div class="flex flex-wrap items-center justify-center gap-3 sm:gap-5 pt-1 pb-2">
                        @foreach($sponsorLogos as $logo)
                            @php
                                $cleanLogo = ltrim(str_replace(['public/', 'storage/'], '', $logo), '/');
                                $logoUrl = \Illuminate\Support\Str::startsWith($logo, ['http://', 'https://']) ? $logo : asset('storage/' . $cleanLogo);
                            @endphp
                            <div class="sponsor-item p-3 sm:p-4 rounded-2xl bg-[#090D17]/90 border border-white/[0.08] shadow-md flex items-center justify-center">
                                <img src="{{ $logoUrl }}" 
                                     alt="Logo Sponsor" 
                                     crossorigin="anonymous"
                                     class="h-10 sm:h-12 w-auto max-w-[140px] sm:max-w-[170px] object-contain drop-shadow-md"
                                     onerror="this.closest('.sponsor-item')?.remove();">
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Watermark & Footer Info -->
            <div class="relative z-10 pt-5 pb-3 border-t border-white/[0.08] text-xs text-slate-400 flex flex-col sm:flex-row items-center justify-between gap-2">
                <span>© {{ date('Y') }} Panitia Milad ke-57 MTsN 1 Blitar</span>
                <span class="font-medium text-slate-400">Sistem Informasi Pendaftaran &amp; Manajemen Lomba TALENTA 2026</span>
            </div>

        </div>

    </div>

    <!-- ==================== TAB 4: REKAP SEMUA PERAIH JUARA ==================== -->
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

    <!-- ==================== TAB 5: REKAP JUARA UMUM ==================== -->
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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/html-to-image@1.11.11/dist/html-to-image.min.js"></script>
@endpush

