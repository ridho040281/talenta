@extends('layouts.admin')

@section('title', 'Peserta Multi Cabang Lomba')
@section('page_title', 'Peserta Multi Lomba')

@section('content')
<style>
    @media print {
        body {
            background: #ffffff !important;
            color: #000000 !important;
        }
        aside, nav, header, .no-print, .btn, button, input, select {
            display: none !important;
        }
        .main-content, .content-container, main {
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
        }
        .print-only {
            display: block !important;
        }
        .ai-card, .glass-card {
            background: #ffffff !important;
            border: 1px solid #cccccc !important;
            box-shadow: none !important;
            color: #000000 !important;
        }
        table {
            border-collapse: collapse !important;
            width: 100% !important;
            color: #000000 !important;
        }
        th, td {
            border: 1px solid #dddddd !important;
            padding: 6px 8px !important;
            color: #000000 !important;
        }
        thead th {
            background-color: #f3f4f6 !important;
        }
    }
</style>

<div class="space-y-6" x-data="{
    searchQuery: '',
    filterType: 'all', // 'all', 'seni_olahraga', 'conflict', 'triple'
    selectedCategory: 'all',
    selectedStudent: null,
    showModal: false,

    allStudents: {{ Js::from($multiStudents) }},

    get filteredStudents() {
        return this.allStudents.filter(s => {
            // Filter Pencarian Teks
            if (this.searchQuery) {
                const q = this.searchQuery.toLowerCase();
                const matchName = (s.full_name || '').toLowerCase().includes(q);
                const matchNisn = (s.nisn || '').toLowerCase().includes(q);
                const matchSchool = (s.school_name || '').toLowerCase().includes(q);
                const matchOfficial = (s.official_name || '').toLowerCase().includes(q);
                const matchComps = s.registrations.some(r => 
                    (r.competition_name || '').toLowerCase().includes(q) || 
                    (r.competition_code || '').toLowerCase().includes(q) ||
                    (r.category_name || '').toLowerCase().includes(q)
                );

                if (!matchName && !matchNisn && !matchSchool && !matchOfficial && !matchComps) {
                    return false;
                }
            }

            // Filter Tipe Kombinasi
            if (this.filterType === 'seni_olahraga' && !s.has_seni_and_olahraga) {
                return false;
            }
            if (this.filterType === 'conflict' && !s.has_conflict) {
                return false;
            }
            if (this.filterType === 'triple' && s.total_competitions < 3) {
                return false;
            }

            // Filter Kategori Spesifik
            if (this.selectedCategory !== 'all') {
                const hasCategory = s.registrations.some(r => r.category_slug === this.selectedCategory);
                if (!hasCategory) return false;
            }

            return true;
        });
    },

    openDetail(student) {
        this.selectedStudent = student;
        this.showModal = true;
    },

    printPage() {
        window.print();
    }
}">

    <!-- ==================== HEADER BANNER ==================== -->
    <div class="glass-card rounded-3xl p-6 sm:p-7 border border-white/[0.08] shadow-2xl relative overflow-hidden flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="relative z-10 space-y-1">
            <div class="flex items-center gap-2 flex-wrap">
                <span class="px-2.5 py-0.5 text-[11px] font-bold rounded-md bg-amber-500/20 text-amber-300 border border-amber-500/30">
                    Operasional &amp; Rekap Lintas Lomba
                </span>
                <span class="text-xs text-slate-400 font-mono">Total {{ $totalMultiStudents }} Siswa Terdeteksi Multi Cabang</span>
            </div>
            <h2 class="text-xl sm:text-2xl font-black text-white font-display flex items-center gap-2">
                <i data-lucide="layers" class="w-6 h-6 text-amber-400"></i>
                <span>Rekapitulasi Peserta Multi Cabang Lomba</span>
            </h2>
            <p class="text-xs sm:text-sm text-slate-300 max-w-3xl">
                Daftar siswa yang mendaftar di lebih dari 1 cabang lomba (termasuk lintas cabang Seni, Olahraga, Sains, dll). Pantau data pendaftaran ganda dan cegah potensi bentrok jadwal pertandingan secara dini.
            </p>
        </div>

        <div class="shrink-0 flex items-center gap-2.5 relative z-10 no-print flex-wrap">
            <button type="button" @click="printPage()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-white/[0.08] hover:bg-white/[0.15] text-white font-bold text-xs border border-white/[0.1] shadow-md transition cursor-pointer">
                <i data-lucide="printer" class="w-4 h-4 text-[#84D0FF]"></i>
                <span>Cetak Rekap</span>
            </button>
            <a href="{{ route('admin.participants.multi') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-gradient-to-r from-[#7A5AF8] to-[#4E6EFF] hover:from-[#6941C6] hover:to-[#3538CD] text-white font-bold text-xs shadow-lg shadow-[#7A5AF8]/30 transition cursor-pointer">
                <i data-lucide="rotate-cw" class="w-4 h-4"></i>
                <span>Segarkan Data</span>
            </a>
        </div>
    </div>

    <!-- ==================== QUICK STATS CARDS ==================== -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 no-print">
        <!-- Card 1: Total Peserta Multi -->
        <div class="ai-card p-4 rounded-2xl border border-white/[0.08] shadow-md flex items-center gap-3.5 hover:border-[#7A5AF8]/50 transition">
            <div class="w-11 h-11 rounded-2xl bg-[#7A5AF8]/15 text-[#A594FD] border border-[#7A5AF8]/30 flex items-center justify-center font-black shrink-0">
                <i data-lucide="users" class="w-5 h-5"></i>
            </div>
            <div class="min-w-0 flex-1">
                <div class="text-xl sm:text-2xl font-black text-white leading-tight font-mono">{{ $totalMultiStudents }}</div>
                <div class="text-xs font-semibold text-slate-400 truncate">Total Siswa Multi Lomba</div>
                <div class="text-[10px] text-purple-300/80 font-medium">Maksimal {{ $maxLomba }} lomba per anak</div>
            </div>
        </div>

        <!-- Card 2: Lintas Seni & Olahraga -->
        <div class="ai-card p-4 rounded-2xl border border-white/[0.08] shadow-md flex items-center gap-3.5 hover:border-pink-500/50 transition">
            <div class="w-11 h-11 rounded-2xl bg-pink-500/15 text-pink-400 border border-pink-500/30 flex items-center justify-center font-black shrink-0">
                <i data-lucide="sparkles" class="w-5 h-5"></i>
            </div>
            <div class="min-w-0 flex-1">
                <div class="text-xl sm:text-2xl font-black text-pink-400 leading-tight font-mono">{{ $totalSeniOlahraga }}</div>
                <div class="text-xs font-semibold text-slate-400 truncate">Lintas Seni &amp; Olahraga</div>
                <div class="text-[10px] text-pink-300/80 font-medium">Kombinasi seni sekaligus fisik</div>
            </div>
        </div>

        <!-- Card 3: Lintas Kategori Umum -->
        <div class="ai-card p-4 rounded-2xl border border-white/[0.08] shadow-md flex items-center gap-3.5 hover:border-[#4E6EFF]/50 transition">
            <div class="w-11 h-11 rounded-2xl bg-[#4E6EFF]/15 text-[#84D0FF] border border-[#4E6EFF]/30 flex items-center justify-center font-black shrink-0">
                <i data-lucide="git-merge" class="w-5 h-5"></i>
            </div>
            <div class="min-w-0 flex-1">
                <div class="text-xl sm:text-2xl font-black text-[#84D0FF] leading-tight font-mono">{{ $totalCrossCategory }}</div>
                <div class="text-xs font-semibold text-slate-400 truncate">Lintas Kategori Campuran</div>
                <div class="text-[10px] text-cyan-300/80 font-medium">Ikut &ge; 2 kategori berbeda</div>
            </div>
        </div>

        <!-- Card 4: Potensi Bentrok Jadwal -->
        <div class="ai-card p-4 rounded-2xl border border-white/[0.08] shadow-md flex items-center gap-3.5 hover:border-amber-500/50 transition">
            <div class="w-11 h-11 rounded-2xl {{ $totalConflicts > 0 ? 'bg-amber-500/15 text-amber-400 border border-amber-500/30' : 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30' }} flex items-center justify-center font-black shrink-0">
                <i data-lucide="{{ $totalConflicts > 0 ? 'alert-triangle' : 'check-circle-2' }}" class="w-5 h-5"></i>
            </div>
            <div class="min-w-0 flex-1">
                <div class="text-xl sm:text-2xl font-black {{ $totalConflicts > 0 ? 'text-amber-400' : 'text-emerald-400' }} leading-tight font-mono">{{ $totalConflicts }}</div>
                <div class="text-xs font-semibold text-slate-400 truncate">Potensi Bentrok Jadwal</div>
                <div class="text-[10px] {{ $totalConflicts > 0 ? 'text-amber-300/80 font-bold' : 'text-emerald-300/80' }}">
                    {{ $totalConflicts > 0 ? 'Perlu penyesuaian jadwal tanding' : 'Aman! Tidak ada jadwal bertabrakan' }}
                </div>
            </div>
        </div>
    </div>

    <!-- ==================== FILTER & SEARCH TOOLBAR ==================== -->
    <div class="ai-card rounded-3xl p-4 sm:p-5 border border-white/[0.08] shadow-xl space-y-4 no-print">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
            <!-- Search Input -->
            <div class="relative flex-1 max-w-md">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                <input type="text" 
                       x-model="searchQuery" 
                       placeholder="Cari nama siswa, NISN, sekolah, nama lomba..." 
                       class="w-full pl-10 pr-9 py-2.5 rounded-xl bg-[#0C111D] border border-white/[0.1] text-xs text-white placeholder-slate-500 focus:border-[#7A5AF8] outline-none transition">
                <button type="button" x-show="searchQuery" @click="searchQuery = ''" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-white p-0.5 cursor-pointer">
                    <i data-lucide="x" class="w-3.5 h-3.5"></i>
                </button>
            </div>

            <!-- Quick Filter Presets -->
            <div class="flex flex-wrap items-center gap-1.5 text-xs font-bold">
                <button type="button" 
                        @click="filterType = 'all'" 
                        :class="filterType === 'all' ? 'bg-[#7A5AF8] text-white shadow-md shadow-[#7A5AF8]/30' : 'bg-white/[0.05] text-slate-400 hover:text-white border border-white/[0.08]'" 
                        class="px-3.5 py-2 rounded-xl transition cursor-pointer">
                    Semua ({{ $totalMultiStudents }})
                </button>
                <button type="button" 
                        @click="filterType = 'seni_olahraga'" 
                        :class="filterType === 'seni_olahraga' ? 'bg-pink-600 text-white shadow-md shadow-pink-600/30' : 'bg-white/[0.05] text-slate-400 hover:text-white border border-white/[0.08]'" 
                        class="px-3.5 py-2 rounded-xl transition cursor-pointer flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-pink-400"></span>
                    <span>Seni &amp; Olahraga ({{ $totalSeniOlahraga }})</span>
                </button>
                @if($totalConflicts > 0)
                <button type="button" 
                        @click="filterType = 'conflict'" 
                        :class="filterType === 'conflict' ? 'bg-amber-600 text-white shadow-md shadow-amber-600/30' : 'bg-white/[0.05] text-amber-300 hover:text-white border border-amber-500/30'" 
                        class="px-3.5 py-2 rounded-xl transition cursor-pointer flex items-center gap-1.5">
                    <i data-lucide="alert-triangle" class="w-3.5 h-3.5 text-amber-300"></i>
                    <span>Bentrok Jadwal ({{ $totalConflicts }})</span>
                </button>
                @endif
                <button type="button" 
                        @click="filterType = 'triple'" 
                        :class="filterType === 'triple' ? 'bg-purple-600 text-white shadow-md shadow-purple-600/30' : 'bg-white/[0.05] text-slate-400 hover:text-white border border-white/[0.08]'" 
                        class="px-3.5 py-2 rounded-xl transition cursor-pointer">
                    &ge; 3 Lomba
                </button>
            </div>
        </div>

        <!-- Category Filter Pills -->
        @if(isset($categories) && $categories->isNotEmpty())
        <div class="flex flex-wrap items-center gap-1.5 pt-2 border-t border-white/[0.06] text-xs">
            <span class="text-[11px] text-slate-400 font-bold mr-1">Filter Kategori:</span>
            <button type="button" 
                    @click="selectedCategory = 'all'" 
                    :class="selectedCategory === 'all' ? 'bg-white/[0.15] text-white font-bold' : 'text-slate-400 hover:text-slate-200'" 
                    class="px-2.5 py-1 rounded-lg transition cursor-pointer">
                Semua Kategori
            </button>
            @foreach($categories as $cat)
                <button type="button" 
                        @click="selectedCategory = '{{ $cat->slug }}'" 
                        :class="selectedCategory === '{{ $cat->slug }}' ? 'bg-[#7A5AF8]/30 text-[#A594FD] border border-[#7A5AF8]/50 font-bold' : 'text-slate-400 hover:text-white'" 
                        class="px-2.5 py-1 rounded-lg transition cursor-pointer">
                    {{ $cat->name }}
                </button>
            @endforeach
        </div>
        @endif
    </div>

    <!-- ==================== MAIN PARTICIPANTS TABLE ==================== -->
    <div class="ai-card rounded-3xl border border-white/[0.08] shadow-2xl p-5 sm:p-7 space-y-4">
        
        <div class="flex items-center justify-between gap-3 border-b border-white/[0.08] pb-4">
            <div>
                <h3 class="text-base sm:text-lg font-black text-white">Daftar Peserta Multi Cabang Lomba</h3>
                <p class="text-xs text-slate-400">
                    Menampilkan <span class="text-[#84D0FF] font-bold font-mono" x-text="filteredStudents.length"></span> dari total <span class="font-mono">{{ $totalMultiStudents }}</span> siswa multi-lomba
                </p>
            </div>
            <div class="text-right">
                <span class="text-[10px] text-slate-400 block uppercase font-bold">TALENTA MTsN 1 Blitar</span>
                <span class="text-xs font-mono font-bold text-amber-400">Milad Ke-57 (2026)</span>
            </div>
        </div>

        <div class="overflow-x-auto rounded-2xl border border-white/[0.08] bg-[#0A0E1A]/40 shadow-inner">
            <table class="w-full min-w-[1050px] text-left text-xs text-slate-300 border-collapse">
                <thead class="text-[10px] font-bold uppercase tracking-wider bg-[#0C111D]/90 text-slate-400 border-b border-white/[0.08]">
                    <tr>
                        <th class="py-3.5 px-4 whitespace-nowrap w-[60px] text-center">NO</th>
                        <th class="py-3.5 px-4 whitespace-nowrap min-w-[220px]">BIODATA SISWA</th>
                        <th class="py-3.5 px-4 whitespace-nowrap min-w-[340px]">CABANG LOMBA YANG DIIKUTI</th>
                        <th class="py-3.5 px-4 whitespace-nowrap min-w-[220px]">JADWAL &amp; DETEKSI BENTROK</th>
                        <th class="py-3.5 px-4 whitespace-nowrap min-w-[170px]">OFFICIAL &amp; KONTAK</th>
                        <th class="py-3.5 px-4 whitespace-nowrap w-[90px] text-center no-print">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/[0.04] font-medium">
                    <template x-for="(s, index) in filteredStudents" :key="s.id_key">
                        <tr class="hover:bg-white/[0.025] transition align-top">
                            <!-- No -->
                            <td class="py-4 px-4 text-center font-mono font-bold text-slate-400" x-text="index + 1"></td>

                            <!-- Biodata Siswa -->
                            <td class="py-4 px-4">
                                <div class="flex items-start gap-3">
                                    <template x-if="s.photo">
                                        <img :src="'/storage/' + s.photo" :alt="s.full_name" class="w-10 h-10 rounded-xl object-cover border border-white/[0.1] shrink-0">
                                    </template>
                                    <template x-if="!s.photo">
                                        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-[#7A5AF8]/30 to-[#4E6EFF]/30 text-white font-black text-sm flex items-center justify-center border border-white/[0.1] shrink-0 font-mono">
                                            <span x-text="(s.full_name || 'P').substring(0, 2).toUpperCase()"></span>
                                        </div>
                                    </template>
                                    <div class="space-y-1 min-w-0">
                                        <div class="font-black text-white text-sm leading-snug" x-text="s.full_name"></div>
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-white/[0.06] text-slate-300 border border-white/[0.08]" x-text="'NISN: ' + s.nisn"></span>
                                            <span class="px-1.5 py-0.5 rounded text-[9px] font-bold" :class="s.gender === 'P' ? 'bg-pink-500/20 text-pink-300' : 'bg-blue-500/20 text-blue-300'" x-text="s.gender === 'P' ? 'PI' : 'PA'"></span>
                                        </div>
                                        <div class="text-[11px] text-slate-400 flex items-center gap-1 truncate">
                                            <i data-lucide="building" class="w-3 h-3 text-slate-500 shrink-0"></i>
                                            <span class="truncate" x-text="s.school_name"></span>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Cabang Lomba yang Diikuti -->
                            <td class="py-4 px-4">
                                <div class="space-y-2">
                                    <div class="flex items-center gap-2">
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-black bg-purple-500/20 text-purple-300 border border-purple-500/30" x-text="s.total_competitions + ' Lomba'"></span>
                                        <template x-if="s.has_seni_and_olahraga">
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-black bg-gradient-to-r from-pink-500/20 to-emerald-500/20 text-white border border-pink-500/30 flex items-center gap-1">
                                                <i data-lucide="sparkles" class="w-3 h-3 text-pink-400"></i>
                                                <span>Seni &amp; Olahraga</span>
                                            </span>
                                        </template>
                                    </div>

                                    <div class="space-y-1.5">
                                        <template x-for="reg in s.registrations" :key="reg.id">
                                            <div class="p-2.5 rounded-xl bg-[#080D18]/90 border border-white/[0.06] flex items-start justify-between gap-2 hover:border-white/[0.15] transition">
                                                <div class="min-w-0 space-y-0.5">
                                                    <div class="flex items-center gap-1.5 flex-wrap">
                                                        <span class="px-1.5 py-0.5 rounded text-[9px] font-black font-mono" 
                                                              :class="{
                                                                  'bg-pink-500/20 text-pink-300': reg.category_slug.includes('seni'),
                                                                  'bg-emerald-500/20 text-emerald-300': reg.category_slug.includes('olahraga'),
                                                                  'bg-blue-500/20 text-blue-300': reg.category_slug.includes('tekno'),
                                                                  'bg-amber-500/20 text-amber-300': reg.category_slug.includes('pramuka'),
                                                                  'bg-teal-500/20 text-teal-300': reg.category_slug.includes('tahfid') || reg.category_slug.includes('agama'),
                                                                  'bg-indigo-500/20 text-indigo-300': reg.category_slug.includes('olimpiade') || reg.category_slug.includes('sains')
                                                              }"
                                                              x-text="reg.competition_code"></span>
                                                        <span class="font-bold text-white text-xs" x-text="reg.competition_name"></span>
                                                    </div>
                                                    <div class="text-[10px] text-slate-400">
                                                        <span x-text="reg.match_type || reg.target_class || reg.sub_category || 'Individu'"></span>
                                                        <template x-if="reg.draw_number">
                                                            <span class="text-amber-400 font-mono font-bold" x-text="' • Undian #' + reg.draw_number"></span>
                                                        </template>
                                                    </div>
                                                </div>

                                                <div class="shrink-0 text-right">
                                                    <span class="px-2 py-0.5 rounded-full text-[9px] font-bold uppercase"
                                                          :class="{
                                                              'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30': reg.status === 'verified',
                                                              'bg-amber-500/20 text-amber-400 border border-amber-500/30': reg.status === 'pending',
                                                              'bg-orange-500/20 text-orange-400 border border-orange-500/30': reg.status === 'revision'
                                                          }"
                                                          x-text="reg.status"></span>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </td>

                            <!-- Jadwal & Lokasi -->
                            <td class="py-4 px-4">
                                <div class="space-y-2">
                                    <template x-if="s.has_conflict">
                                        <div class="p-2 rounded-xl bg-rose-500/15 border border-rose-500/30 text-rose-300 text-[11px] flex items-start gap-1.5 font-bold">
                                            <i data-lucide="alert-triangle" class="w-4 h-4 shrink-0 text-rose-400 mt-0.5"></i>
                                            <div>
                                                <div class="text-rose-200">Potensi Bentrok Jadwal!</div>
                                                <div class="text-[10px] font-normal text-rose-300/80">Lomba jatuh pada hari/tanggal yang sama.</div>
                                            </div>
                                        </div>
                                    </template>

                                    <div class="space-y-1 text-xs">
                                        <template x-for="reg in s.registrations" :key="'sch_' + reg.id">
                                            <div class="text-[11px] text-slate-300 flex items-start gap-1.5">
                                                <span class="text-slate-500 font-mono">↳</span>
                                                <div>
                                                    <span class="font-bold text-slate-200" x-text="reg.competition_code + ':'"></span>
                                                    <span class="text-slate-400" x-text="reg.schedule_date_formatted + ' (' + reg.schedule_time + ')'"></span>
                                                    <div class="text-[10px] text-slate-500" x-text="'Lokasi: ' + reg.venue"></div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </td>

                            <!-- Official & Kontak -->
                            <td class="py-4 px-4">
                                <div class="space-y-1.5">
                                    <div class="font-bold text-white text-xs truncate" x-text="s.official_name || 'Official Sekolah'"></div>
                                    <div class="text-[11px] text-slate-400 font-mono" x-text="s.official_phone"></div>
                                    
                                    <template x-if="s.official_phone && s.official_phone !== '-'">
                                        <div class="pt-1 no-print">
                                            <a :href="'https://wa.me/' + s.official_phone.replace(/[^0-9]/g, '').replace(/^0/, '62') + '?text=' + encodeURIComponent('Halo Bpk/Ibu ' + (s.official_name || 'Pembina') + ', kami dari Panitia TALENTA MTsN 1 Blitar ingin mengonfirmasi jadwal siswa ' + s.full_name + ' yang mengikuti beberapa cabang lomba.')" 
                                               target="_blank" 
                                               class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-300 border border-emerald-500/30 text-[10px] font-bold transition">
                                                <i data-lucide="message-circle" class="w-3.5 h-3.5 text-emerald-400"></i>
                                                <span>Chat WhatsApp</span>
                                            </a>
                                        </div>
                                    </template>
                                </div>
                            </td>

                            <!-- Aksi -->
                            <td class="py-4 px-4 text-center no-print">
                                <button type="button" 
                                        @click="openDetail(s)" 
                                        class="p-2 rounded-xl bg-white/[0.06] hover:bg-white/[0.12] text-slate-300 hover:text-white border border-white/[0.08] transition cursor-pointer" 
                                        title="Rincian Lengkap Siswa">
                                    <i data-lucide="eye" class="w-4 h-4 text-[#84D0FF]"></i>
                                </button>
                            </td>
                        </tr>
                    </template>

                    <template x-if="filteredStudents.length === 0">
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-500">
                                <div class="w-12 h-12 rounded-2xl bg-white/[0.04] text-slate-600 flex items-center justify-center mx-auto mb-3">
                                    <i data-lucide="inbox" class="w-6 h-6"></i>
                                </div>
                                <div class="text-sm font-bold text-slate-400">Tidak ada data peserta multi-lomba yang cocok</div>
                                <div class="text-xs text-slate-600 mt-1">Coba ubah kata kunci pencarian atau reset filter.</div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ==================== MODAL DETAIL PESERTA ==================== -->
    <div x-show="showModal" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm no-print"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="ai-card rounded-3xl border border-white/[0.1] shadow-2xl p-6 sm:p-7 w-full max-w-2xl max-h-[90vh] overflow-y-auto space-y-5 bg-[#0E1320]"
             @click.outside="showModal = false">
            
            <template x-if="selectedStudent">
                <div class="space-y-5">
                    <!-- Modal Header -->
                    <div class="flex items-start justify-between gap-3 border-b border-white/[0.08] pb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-[#7A5AF8] to-[#4E6EFF] text-white font-black text-lg flex items-center justify-center shadow-lg shadow-[#7A5AF8]/30 shrink-0">
                                <span x-text="(selectedStudent.full_name || 'P').substring(0, 2).toUpperCase()"></span>
                            </div>
                            <div>
                                <h3 class="text-lg font-black text-white" x-text="selectedStudent.full_name"></h3>
                                <div class="text-xs text-slate-400 flex items-center gap-2">
                                    <span x-text="'NISN: ' + selectedStudent.nisn"></span>
                                    <span>•</span>
                                    <span x-text="selectedStudent.school_name"></span>
                                </div>
                            </div>
                        </div>
                        <button type="button" @click="showModal = false" class="p-2 rounded-xl text-slate-400 hover:text-white hover:bg-white/[0.08] transition cursor-pointer">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

                    <!-- Ringkasan Multi Cabang -->
                    <div class="p-3.5 rounded-2xl bg-[#080D18] border border-white/[0.08] flex items-center justify-between gap-3">
                        <div>
                            <span class="text-[11px] text-slate-400 block uppercase font-bold">Partisipasi Lomba</span>
                            <span class="text-base font-black text-amber-400" x-text="selectedStudent.total_competitions + ' Cabang Perlombaan'"></span>
                        </div>
                        <div class="text-right">
                            <span class="text-[11px] text-slate-400 block uppercase font-bold">Kombinasi Kategori</span>
                            <span class="text-xs font-bold text-white" x-text="selectedStudent.categories.join(', ')"></span>
                        </div>
                    </div>

                    <!-- Detail Per Lomba -->
                    <div class="space-y-3">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400">Rincian Cabang Lomba &amp; Jadwal:</h4>
                        <template x-for="reg in selectedStudent.registrations" :key="'mod_' + reg.id">
                            <div class="p-4 rounded-2xl bg-[#080D18] border border-white/[0.08] space-y-2">
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-black bg-purple-500/20 text-purple-300 border border-purple-500/30" x-text="reg.competition_code"></span>
                                            <span class="font-bold text-white text-sm" x-text="reg.competition_name"></span>
                                        </div>
                                        <div class="text-xs text-slate-400 mt-0.5">
                                            <span x-text="'No. Registrasi: ' + reg.registration_code"></span>
                                            <template x-if="reg.draw_number">
                                                <span class="text-amber-400 font-bold" x-text="' • No. Undian #' + reg.draw_number"></span>
                                            </template>
                                        </div>
                                    </div>
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase"
                                          :class="{
                                              'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30': reg.status === 'verified',
                                              'bg-amber-500/20 text-amber-400 border border-amber-500/30': reg.status === 'pending',
                                              'bg-orange-500/20 text-orange-400 border border-orange-500/30': reg.status === 'revision'
                                          }"
                                          x-text="reg.status"></span>
                                </div>

                                <div class="grid grid-cols-2 gap-2 text-xs pt-2 border-t border-white/[0.04]">
                                    <div>
                                        <span class="text-slate-500 block text-[10px]">Waktu &amp; Tanggal:</span>
                                        <span class="text-slate-300 font-medium" x-text="reg.schedule_date_formatted + ' (' + reg.schedule_time + ')'"></span>
                                    </div>
                                    <div>
                                        <span class="text-slate-500 block text-[10px]">Lokasi / Venue:</span>
                                        <span class="text-slate-300 font-medium" x-text="reg.venue"></span>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Kontak Official -->
                    <div class="p-3.5 rounded-2xl bg-white/[0.03] border border-white/[0.08] flex items-center justify-between gap-3">
                        <div>
                            <div class="text-[11px] text-slate-400">Pembina / Official:</div>
                            <div class="text-sm font-bold text-white" x-text="selectedStudent.official_name || 'Pembina Sekolah'"></div>
                            <div class="text-xs text-slate-400 font-mono" x-text="selectedStudent.official_phone"></div>
                        </div>
                        <template x-if="selectedStudent.official_phone && selectedStudent.official_phone !== '-'">
                            <a :href="'https://wa.me/' + selectedStudent.official_phone.replace(/[^0-9]/g, '').replace(/^0/, '62')" 
                               target="_blank" 
                               class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 text-xs font-black transition cursor-pointer">
                                <i data-lucide="message-circle" class="w-4 h-4"></i>
                                <span>Hubungi WA</span>
                            </a>
                        </template>
                    </div>

                    <!-- Modal Footer -->
                    <div class="flex justify-end pt-2 border-t border-white/[0.08]">
                        <button type="button" @click="showModal = false" class="px-5 py-2.5 rounded-xl text-xs font-bold bg-white/[0.08] hover:bg-white/[0.15] text-white transition cursor-pointer">
                            Tutup
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>

</div>
@endsection
