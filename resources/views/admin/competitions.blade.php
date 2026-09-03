@extends('layouts.admin')

@section('title', 'Master Cabang Lomba & Timeline Jadwal')
@section('page_title', 'Master Lomba & Jadwal Rangkaian Acara')

@section('content')
<script>
    function competitionPageApp() {
        const urlParams = new URLSearchParams(window.location.search);
        const tabParam = urlParams.get('tab') || 'lomba';

        return {
            activeTab: (tabParam === 'timeline' ? 'timeline' : 'lomba'),
            competitionFilter: 'all',
            createModal: false,
            editCompetitionModal: false,
            createCategoryModal: false,
            editCategoryModal: false,
            createTimelineModal: false,
            editTimelineModal: false,
            bltEditMode: 'all',
            newCompetition: {
                category_id: '',
                name: '',
                code: '',
                type: 'individu',
                min_members: 1,
                max_members: 1,
                quota: 50,
                registration_fee: 0,
                pic_id: '',
                venue: '',
                schedule_time: '',
                rules: '',
                show_criteria: true,
                criteria: [
                    { name: 'Penilaian Umum', weight_percentage: 100, min_score: 0, max_score: 100, description: '' }
                ]
            },
            selectedCompetition: {
                id: null,
                category_id: '',
                name: '',
                code: '',
                type: 'individu',
                min_members: 1,
                max_members: 1,
                quota: 50,
                registration_fee: 0,
                blt_fee_a_tunggal: 75000,
                blt_fee_b_tunggal: 100000,
                blt_fee_c_tunggal: 125000,
                blt_fee_a_ganda: 75000,
                blt_fee_b_ganda: 100000,
                blt_fee_c_ganda: 125000,
                blt_quota_a_tunggal: 20,
                blt_quota_b_tunggal: 10,
                blt_quota_c_tunggal: 20,
                blt_quota_a_ganda: 10,
                blt_quota_b_ganda: 10,
                blt_quota_c_ganda: 10,
                pic_id: '',
                status: 'buka',
                venue: '',
                schedule_time: '',
                rules: '',
                show_criteria: true,
                criteria: []
            },
            selectedCategory: {
                id: null,
                name: '',
                category_group: 'non_akademik',
                icon: 'trophy',
                order: 1,
                description: ''
            },
            selectedTimeline: {
                id: null,
                title: '',
                date_label: '',
                time_label: '',
                location: '',
                description: '',
                order: 1,
                is_active: true
            },
            addCriterion(target) {
                if (!target.criteria) target.criteria = [];
                target.criteria.push({
                    name: '',
                    weight_percentage: 50,
                    min_score: 0,
                    max_score: 100,
                    description: ''
                });
                this.$nextTick(() => {
                    if (window.lucide) window.lucide.createIcons();
                });
            },
            removeCriterion(target, index) {
                if (target.criteria && target.criteria.length > 0) {
                    target.criteria.splice(index, 1);
                }
            },
            calculateTotalWeight(criteria) {
                if (!criteria || criteria.length === 0) return 0;
                return criteria.reduce((sum, item) => sum + (parseInt(item.weight_percentage) || 0), 0);
            },
            openBltEdit(item, mode = 'all') {
                window.location.href = '{{ url('/admin/competitions') }}/' + item.id + '/edit' + (mode && mode !== 'all' ? '?mode=' + mode : '');
            },
            closeEditCompetitionModal() {
                this.editCompetitionModal = false;
                document.body.classList.remove('edit-fullscreen');
                document.body.style.overflow = '';
            },
            editCompetition(item) {
                window.location.href = '{{ url('/admin/competitions') }}/' + item.id + '/edit';
            },
            createCompetitionWithCategory(categoryId) {
                this.newCompetition = {
                    category_id: categoryId,
                    name: '',
                    code: '',
                    type: 'individu',
                    min_members: 1,
                    max_members: 1,
                    quota: 50,
                    registration_fee: 0,
                    pic_id: '',
                    venue: '',
                    schedule_time: '',
                    rules: '',
                    criteria: [
                        { name: 'Penilaian Umum', weight_percentage: 100, min_score: 0, max_score: 100, description: '' }
                    ]
                };
                this.createModal = true;
                this.$nextTick(() => {
                    if (window.lucide) window.lucide.createIcons();
                });
            },
            editCategory(item) {
                this.selectedCategory = Object.assign({}, item);
                this.editCategoryModal = true;
            },
            editTimeline(item) {
                this.selectedTimeline = Object.assign({}, item);
                this.editTimelineModal = true;
            }
        };
    }
    window.competitionPageApp = competitionPageApp;
</script>

<div class="space-y-6" x-data="competitionPageApp()">
    
    <!-- AIStarterKit Top Action Bar & Tab Switcher -->
    <div class="ai-card rounded-3xl p-5 sm:p-6 border border-white/[0.08] shadow-xl flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="p-1 rounded-lg bg-[#7A5AF8]/20 text-[#A594FD] border border-[#7A5AF8]/30">
                    <i data-lucide="medal" class="w-4 h-4"></i>
                </span>
                <h2 class="text-xl sm:text-2xl font-black text-white ai-gradient-text">Pengaturan Lomba & Timeline Jadwal</h2>
            </div>
            <p class="text-xs text-slate-400 mt-1">Kelola master cabang lomba, kuota, dan tanggal-tanggal penting rangkaian acara TALENTA.</p>
        </div>

        <!-- Tab Buttons (AI Starter Kit Pill Nav) -->
        <div class="flex items-center gap-2 bg-[#0C111D] border border-white/[0.08] p-1.5 rounded-2xl overflow-x-auto no-scrollbar shrink-0">
            <button type="button" @click="activeTab = 'lomba'" :class="activeTab === 'lomba' ? 'bg-gradient-to-r from-[#7A5AF8] to-[#4E6EFF] text-white shadow-md shadow-[#7A5AF8]/30 font-black' : 'text-slate-400 hover:text-white font-bold'" class="px-4 py-2 rounded-xl text-xs transition flex items-center gap-2 whitespace-nowrap cursor-pointer">
                <i data-lucide="medal" class="w-4 h-4 text-emerald-400"></i>
                <span>Cabang Lomba ({{ $competitions->count() }})</span>
            </button>
            <button type="button" @click="activeTab = 'timeline'" :class="activeTab === 'timeline' ? 'bg-gradient-to-r from-[#7A5AF8] to-[#4E6EFF] text-white shadow-md shadow-[#7A5AF8]/30 font-black' : 'text-slate-400 hover:text-white font-bold'" class="px-4 py-2 rounded-xl text-xs transition flex items-center gap-2 whitespace-nowrap cursor-pointer">
                <i data-lucide="calendar" class="w-4 h-4 text-amber-400"></i>
                <span>Timeline & Jadwal ({{ $timelines->count() }})</span>
            </button>
        </div>
    </div>

    <!-- ==================== TAB 1: MASTER CABANG LOMBA ==================== -->
    <div x-show="activeTab === 'lomba'" x-transition class="space-y-6">
        
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h3 class="text-base font-bold text-white">Daftar Cabang Perlombaan Aktif</h3>
                <p class="text-xs text-slate-400">Seluruh cabang lomba yang dibuka pada TALENTA MTsN 1 Blitar</p>
            </div>
            <div class="flex items-center gap-3">
                <button type="button" @click="createModal = true" class="gradient-btn inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl text-white font-bold text-xs shadow-lg shadow-[#7A5AF8]/25 transition cursor-pointer">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                    <span>Tambah Cabang Lomba</span>
                </button>
            </div>
        </div>

        <!-- Quick Category Filter Pills -->
        <div class="flex items-center gap-2 overflow-x-auto no-scrollbar pb-1">
            <button type="button" @click="competitionFilter = 'all'" :class="competitionFilter === 'all' ? 'bg-gradient-to-r from-[#7A5AF8] to-[#4E6EFF] text-white shadow-md font-black' : 'bg-white/[0.05] hover:bg-white/[0.1] text-slate-300 border border-white/[0.08] font-bold'" class="px-3.5 py-2 rounded-xl text-xs transition shrink-0 whitespace-nowrap cursor-pointer">
                Semua Lomba ({{ $competitions->count() }})
            </button>
            @foreach($categories as $cat)
                <button type="button" @click="competitionFilter = '{{ $cat->id }}'" :class="competitionFilter === '{{ $cat->id }}' ? 'bg-gradient-to-r from-[#7A5AF8] to-[#4E6EFF] text-white shadow-md font-black' : 'bg-white/[0.05] hover:bg-white/[0.1] text-slate-300 border border-white/[0.08] font-bold'" class="px-3.5 py-2 rounded-xl text-xs transition flex items-center gap-1.5 shrink-0 whitespace-nowrap cursor-pointer">
                    <i data-lucide="{{ $cat->icon ?: 'folder' }}" class="w-3.5 h-3.5"></i>
                    <span>{{ $cat->name }} ({{ $cat->competitions->count() }})</span>
                </button>
            @endforeach
        </div>

        <!-- Competitions Table -->
        <div class="ai-card rounded-3xl border border-white/[0.08] shadow-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-300">
                    <thead class="text-xs font-bold uppercase tracking-wider bg-[#0C111D]/90 text-slate-400 border-b border-white/[0.08]">
                        <tr>
                            <th class="py-4 px-4 text-center">Urutan</th>
                            <th class="py-4 px-6">Kode</th>
                            <th class="py-4 px-6">Nama Lomba</th>
                            <th class="py-4 px-6">Jenis Lomba</th>
                            <th class="py-4 px-6">Kategori</th>
                            <th class="py-4 px-6">Biaya Pendaftaran</th>
                            <th class="py-4 px-6">Kuota</th>
                            <th class="py-4 px-6">PIC</th>
                            <th class="py-4 px-6 text-center">Status</th>
                            <th class="py-4 px-6 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/[0.04] font-medium">
                        @forelse($competitions as $comp)
                            <tr x-show="competitionFilter === 'all' || competitionFilter === '{{ $comp->category_id }}'" class="hover:bg-white/[0.025] transition">
                                <td class="py-4 px-4 text-center">
                                    <span class="inline-block px-2 py-1 rounded-lg bg-amber-500/10 border border-amber-500/25 font-mono font-black text-xs text-amber-400">
                                        #{{ $comp->order ?? '-' }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 font-mono text-xs font-bold text-[#84D0FF]">
                                    {{ $comp->code }}
                                </td>
                                <td class="py-4 px-6 font-black text-white text-sm">
                                    {{ $comp->name }}
                                </td>
                                <td class="py-4 px-6 text-xs whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-[#4E6EFF]/15 text-[#84D0FF] border border-[#4E6EFF]/30 font-bold">
                                        <i data-lucide="{{ $comp->category->icon ?: 'folder' }}" class="w-3.5 h-3.5"></i>
                                        <span>{{ $comp->category->name }}</span>
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-xs whitespace-nowrap align-middle">
                                    @if($comp->code === 'BLT')
                                        <div class="flex flex-col py-1">
                                            <!-- 1. Tunggal PA -->
                                            <div class="h-[84px] flex items-center gap-1.5 font-bold text-slate-200">
                                                <i data-lucide="user" class="w-4 h-4 text-emerald-400"></i>
                                                <span>Tunggal | PA</span>
                                            </div>
                                            <div class="border-t border-white/[0.08] my-1.5"></div>
                                            <!-- 2. Tunggal PI -->
                                            <div class="h-[84px] flex items-center gap-1.5 font-bold text-slate-200">
                                                <i data-lucide="user" class="w-4 h-4 text-pink-400"></i>
                                                <span>Tunggal | PI</span>
                                            </div>
                                            <div class="border-t border-white/[0.08] my-1.5"></div>
                                            <!-- 3. Ganda PA -->
                                            <div class="h-[36px] flex items-center gap-1.5 font-bold text-slate-200">
                                                <i data-lucide="users" class="w-4 h-4 text-[#7A5AF8]"></i>
                                                <span>Ganda | PA</span>
                                            </div>
                                            <div class="border-t border-white/[0.08] my-1.5"></div>
                                            <!-- 4. Ganda PI -->
                                            <div class="h-[36px] flex items-center gap-1.5 font-bold text-slate-200">
                                                <i data-lucide="users" class="w-4 h-4 text-amber-400"></i>
                                                <span>Ganda | PI</span>
                                            </div>
                                        </div>
                                    @elseif(in_array($comp->code, ['MTQ', 'POP']))
                                        <div class="flex flex-col py-1">
                                            <!-- 1. Individu PA -->
                                            <div class="h-[36px] flex items-center gap-1.5 font-bold text-slate-200">
                                                <i data-lucide="user" class="w-4 h-4 text-emerald-400"></i>
                                                <span>Individu | PA</span>
                                            </div>
                                            <div class="border-t border-white/[0.08] my-1.5"></div>
                                            <!-- 2. Individu PI -->
                                            <div class="h-[36px] flex items-center gap-1.5 font-bold text-slate-200">
                                                <i data-lucide="user" class="w-4 h-4 text-pink-400"></i>
                                                <span>Individu | PI</span>
                                            </div>
                                        </div>
                                    @elseif($comp->code === 'TMJ')
                                        <div class="flex flex-col py-1">
                                            <!-- 1. Tunggal PA -->
                                            <div class="h-[56px] flex items-center gap-1.5 font-bold text-slate-200">
                                                <i data-lucide="user" class="w-4 h-4 text-emerald-400"></i>
                                                <span>Tunggal | PA</span>
                                            </div>
                                            <div class="border-t border-white/[0.08] my-1.5"></div>
                                            <!-- 2. Tunggal PI -->
                                            <div class="h-[56px] flex items-center gap-1.5 font-bold text-slate-200">
                                                <i data-lucide="user" class="w-4 h-4 text-pink-400"></i>
                                                <span>Tunggal | PI</span>
                                            </div>
                                        </div>
                                    @else
                                        <span class="capitalize text-slate-300 font-bold">{{ $comp->type }}</span>
                                    @endif
                                </td>
                                <td class="py-4 px-6 text-xs font-mono whitespace-nowrap align-middle">
                                    @if($comp->code === 'BLT')
                                        <div class="flex flex-col py-1">
                                            <!-- 1. Biaya Tunggal PA -->
                                            <div class="h-[84px] flex flex-col justify-center gap-1.5">
                                                <div class="h-[22px] flex items-center">
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-emerald-500/15 text-emerald-400 font-bold border border-emerald-500/30 text-[11px]">
                                                        <span>Kat A : Rp {{ number_format($comp->tier_fees['A_tunggal_pa'] ?? 130000, 0, ',', '.') }}</span>
                                                    </span>
                                                </div>
                                                <div class="h-[22px] flex items-center">
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-emerald-500/15 text-emerald-400 font-bold border border-emerald-500/30 text-[11px]">
                                                        <span>Kat B : Rp {{ number_format($comp->tier_fees['B_tunggal_pa'] ?? 150000, 0, ',', '.') }}</span>
                                                    </span>
                                                </div>
                                                <div class="h-[22px] flex items-center">
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-emerald-500/15 text-emerald-400 font-bold border border-emerald-500/30 text-[11px]">
                                                        <span>Kat C : Rp {{ number_format($comp->tier_fees['C_tunggal_pa'] ?? 150000, 0, ',', '.') }}</span>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="border-t border-white/[0.08] my-1.5"></div>
                                            <!-- 2. Biaya Tunggal PI -->
                                            <div class="h-[84px] flex flex-col justify-center gap-1.5">
                                                <div class="h-[22px] flex items-center">
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-pink-500/15 text-pink-400 font-bold border border-pink-500/30 text-[11px]">
                                                        <span>Kat A : Rp {{ number_format($comp->tier_fees['A_tunggal_pi'] ?? 130000, 0, ',', '.') }}</span>
                                                    </span>
                                                </div>
                                                <div class="h-[22px] flex items-center">
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-pink-500/15 text-pink-400 font-bold border border-pink-500/30 text-[11px]">
                                                        <span>Kat B : Rp {{ number_format($comp->tier_fees['B_tunggal_pi'] ?? 150000, 0, ',', '.') }}</span>
                                                    </span>
                                                </div>
                                                <div class="h-[22px] flex items-center">
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-pink-500/15 text-pink-400 font-bold border border-pink-500/30 text-[11px]">
                                                        <span>Kat C : Rp {{ number_format($comp->tier_fees['C_tunggal_pi'] ?? 150000, 0, ',', '.') }}</span>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="border-t border-white/[0.08] my-1.5"></div>
                                            <!-- 3. Biaya Ganda PA -->
                                            <div class="h-[36px] flex items-center">
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-[#4E6EFF]/15 text-[#84D0FF] font-bold border border-[#4E6EFF]/30 text-xs">
                                                    <span>Rp {{ number_format($comp->tier_fees['ganda_pa'] ?? 200000, 0, ',', '.') }}</span>
                                                </span>
                                            </div>
                                            <div class="border-t border-white/[0.08] my-1.5"></div>
                                            <!-- 4. Biaya Ganda PI -->
                                            <div class="h-[36px] flex items-center">
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-amber-500/15 text-amber-300 font-bold border border-amber-500/30 text-xs">
                                                    <span>Rp {{ number_format($comp->tier_fees['ganda_pi'] ?? 200000, 0, ',', '.') }}</span>
                                                </span>
                                            </div>
                                        </div>
                                    @elseif(in_array($comp->code, ['MTQ', 'POP']))
                                        <div class="flex flex-col py-1">
                                            <!-- 1. Biaya PA -->
                                            <div class="h-[36px] flex items-center">
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-emerald-500/15 text-emerald-400 font-bold border border-emerald-500/30 text-xs">
                                                    <span>Rp {{ number_format($comp->tier_fees['pa'] ?? $comp->registration_fee, 0, ',', '.') }}</span>
                                                </span>
                                            </div>
                                            <div class="border-t border-white/[0.08] my-1.5"></div>
                                            <!-- 2. Biaya PI -->
                                            <div class="h-[36px] flex items-center">
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-pink-500/15 text-pink-400 font-bold border border-pink-500/30 text-xs">
                                                    <span>Rp {{ number_format($comp->tier_fees['pi'] ?? $comp->registration_fee, 0, ',', '.') }}</span>
                                                </span>
                                            </div>
                                        </div>
                                    @elseif($comp->code === 'TMJ')
                                        <div class="flex flex-col py-1">
                                            <!-- 1. Biaya Tunggal PA -->
                                            <div class="h-[56px] flex flex-col justify-center gap-1.5">
                                                <div class="h-[22px] flex items-center">
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-emerald-500/15 text-emerald-400 font-bold border border-emerald-500/30 text-[11px]">
                                                        <span>Kat A : Rp {{ number_format($comp->tier_fees['A_tunggal_pa'] ?? 35000, 0, ',', '.') }}</span>
                                                    </span>
                                                </div>
                                                <div class="h-[22px] flex items-center">
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-emerald-500/15 text-emerald-400 font-bold border border-emerald-500/30 text-[11px]">
                                                        <span>Kat B : Rp {{ number_format($comp->tier_fees['B_tunggal_pa'] ?? 35000, 0, ',', '.') }}</span>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="border-t border-white/[0.08] my-1.5"></div>
                                            <!-- 2. Biaya Tunggal PI -->
                                            <div class="h-[56px] flex flex-col justify-center gap-1.5">
                                                <div class="h-[22px] flex items-center">
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-pink-500/15 text-pink-400 font-bold border border-pink-500/30 text-[11px]">
                                                        <span>Kat A : Rp {{ number_format($comp->tier_fees['A_tunggal_pi'] ?? 35000, 0, ',', '.') }}</span>
                                                    </span>
                                                </div>
                                                <div class="h-[22px] flex items-center">
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-pink-500/15 text-pink-400 font-bold border border-pink-500/30 text-[11px]">
                                                        <span>Kat B : Rp {{ number_format($comp->tier_fees['B_tunggal_pi'] ?? 35000, 0, ',', '.') }}</span>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    @elseif(($comp->registration_fee ?? 0) > 0)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-emerald-500/15 text-emerald-400 font-bold border border-emerald-500/30">
                                            <span>Rp {{ number_format($comp->registration_fee, 0, ',', '.') }}</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-white/[0.05] text-slate-400 font-bold border border-white/[0.08]">
                                            <span>Gratis / Rp 0</span>
                                        </span>
                                    @endif
                                </td>
                                <td class="py-4 px-6 text-xs whitespace-nowrap align-middle">
                                    @if($comp->code === 'BLT')
                                        <div class="flex flex-col py-1 text-slate-400 text-xs">
                                            <!-- Kuota Tunggal PA -->
                                            <div class="h-[84px] flex flex-col justify-center gap-1.5 font-medium text-[11px]">
                                                <div class="h-[22px] flex items-center"><span class="font-bold text-white">0</span>&nbsp;/ {{ $comp->tier_quotas['A_tunggal_pa'] ?? 16 }}</div>
                                                <div class="h-[22px] flex items-center"><span class="font-bold text-white">0</span>&nbsp;/ {{ $comp->tier_quotas['B_tunggal_pa'] ?? 16 }}</div>
                                                <div class="h-[22px] flex items-center"><span class="font-bold text-white">0</span>&nbsp;/ {{ $comp->tier_quotas['C_tunggal_pa'] ?? 16 }}</div>
                                            </div>
                                            <div class="border-t border-white/[0.08] my-1.5"></div>
                                            <!-- Kuota Tunggal PI -->
                                            <div class="h-[84px] flex flex-col justify-center gap-1.5 font-medium text-[11px]">
                                                <div class="h-[22px] flex items-center"><span class="font-bold text-white">0</span>&nbsp;/ {{ $comp->tier_quotas['A_tunggal_pi'] ?? 16 }}</div>
                                                <div class="h-[22px] flex items-center"><span class="font-bold text-white">0</span>&nbsp;/ {{ $comp->tier_quotas['B_tunggal_pi'] ?? 16 }}</div>
                                                <div class="h-[22px] flex items-center"><span class="font-bold text-white">0</span>&nbsp;/ {{ $comp->tier_quotas['C_tunggal_pi'] ?? 16 }}</div>
                                            </div>
                                            <div class="border-t border-white/[0.08] my-1.5"></div>
                                            <!-- Kuota Ganda PA -->
                                            <div class="h-[36px] flex items-center font-medium text-xs">
                                                <div><span class="font-bold text-white">0</span>&nbsp;/ {{ $comp->tier_quotas['ganda_pa'] ?? 10 }}</div>
                                            </div>
                                            <div class="border-t border-white/[0.08] my-1.5"></div>
                                            <!-- Kuota Ganda PI -->
                                            <div class="h-[36px] flex items-center font-medium text-xs">
                                                <div><span class="font-bold text-white">0</span>&nbsp;/ {{ $comp->tier_quotas['ganda_pi'] ?? 10 }}</div>
                                            </div>
                                        </div>
                                    @elseif(in_array($comp->code, ['MTQ', 'POP']))
                                        <div class="flex flex-col py-1 text-slate-400 text-xs">
                                            @php
                                                $countPa = $comp->registrations->filter(fn($r) => $r->primary_gender === 'L')->count();
                                                $countPi = $comp->registrations->filter(fn($r) => $r->primary_gender === 'P')->count();
                                                $quotaPa = $comp->tier_quotas['pa'] ?? (int) ceil($comp->quota / 2);
                                                $quotaPi = $comp->tier_quotas['pi'] ?? (int) floor($comp->quota / 2);
                                            @endphp
                                            <div class="h-[36px] flex items-center font-medium text-xs">
                                                <div><span class="font-bold text-white">{{ $countPa }}</span>&nbsp;/ {{ $quotaPa }}</div>
                                            </div>
                                            <div class="border-t border-white/[0.08] my-1.5"></div>
                                            <div class="h-[36px] flex items-center font-medium text-xs">
                                                <div><span class="font-bold text-white">{{ $countPi }}</span>&nbsp;/ {{ $quotaPi }}</div>
                                            </div>
                                        </div>
                                    @elseif($comp->code === 'TMJ')
                                        <div class="flex flex-col py-1 text-slate-400 text-xs">
                                            <!-- Kuota Tunggal PA -->
                                            <div class="h-[56px] flex flex-col justify-center gap-1.5 font-medium text-[11px]">
                                                <div class="h-[22px] flex items-center"><span class="font-bold text-white">0</span>&nbsp;/ {{ $comp->tier_quotas['A_tunggal_pa'] ?? 10 }}</div>
                                                <div class="h-[22px] flex items-center"><span class="font-bold text-white">0</span>&nbsp;/ {{ $comp->tier_quotas['B_tunggal_pa'] ?? 10 }}</div>
                                            </div>
                                            <div class="border-t border-white/[0.08] my-1.5"></div>
                                            <!-- Kuota Tunggal PI -->
                                            <div class="h-[56px] flex flex-col justify-center gap-1.5 font-medium text-[11px]">
                                                <div class="h-[22px] flex items-center"><span class="font-bold text-white">0</span>&nbsp;/ {{ $comp->tier_quotas['A_tunggal_pi'] ?? 10 }}</div>
                                                <div class="h-[22px] flex items-center"><span class="font-bold text-white">0</span>&nbsp;/ {{ $comp->tier_quotas['B_tunggal_pi'] ?? 10 }}</div>
                                            </div>
                                        </div>
                                    @else
                                        @if($comp->isUnlimitedQuota())
                                            <div class="inline-flex flex-col items-center">
                                                <div><span class="font-bold text-white">{{ $comp->registrations_count }}</span> <span class="text-slate-400 font-mono">/ ∞</span></div>
                                                <span class="mt-0.5 px-2 py-0.5 rounded-full text-[9px] font-black bg-purple-500/20 text-purple-300 border border-purple-500/30 whitespace-nowrap">
                                                    ∞ Tak Terbatas
                                                </span>
                                            </div>
                                        @else
                                            <span class="font-bold text-white">{{ $comp->registrations_count }}</span> / {{ $comp->quota }}
                                        @endif
                                    @endif
                                </td>
                                <td class="py-4 px-6 text-xs text-slate-300 whitespace-nowrap align-middle">
                                    @if($comp->code === 'BLT')
                                        <div class="flex flex-col py-1 text-xs">
                                            <!-- PIC Tunggal PA -->
                                            <div class="h-[84px] flex items-center text-slate-300 font-medium">
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-500/10 text-emerald-300 border border-emerald-500/20">
                                                    <i data-lucide="user-check" class="w-3.5 h-3.5 text-emerald-400"></i>
                                                    <span>{{ $comp->pic_tunggal_pa->name ?? $comp->pic->name ?? 'Belum Ditugaskan' }}</span>
                                                </span>
                                            </div>
                                            <div class="border-t border-white/[0.08] my-1.5"></div>
                                            <!-- PIC Tunggal PI -->
                                            <div class="h-[84px] flex items-center text-slate-300 font-medium">
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-pink-500/10 text-pink-300 border border-pink-500/20">
                                                    <i data-lucide="user-check" class="w-3.5 h-3.5 text-pink-400"></i>
                                                    <span>{{ $comp->pic_tunggal_pi->name ?? $comp->pic->name ?? 'Belum Ditugaskan' }}</span>
                                                </span>
                                            </div>
                                            <div class="border-t border-white/[0.08] my-1.5"></div>
                                            <!-- PIC Ganda PA -->
                                            <div class="h-[36px] flex items-center text-slate-300 font-medium">
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-[#4E6EFF]/10 text-[#84D0FF] border border-[#4E6EFF]/20">
                                                    <i data-lucide="user-check" class="w-3.5 h-3.5 text-[#84D0FF]"></i>
                                                    <span>{{ $comp->pic_ganda_pa->name ?? $comp->pic->name ?? 'Belum Ditugaskan' }}</span>
                                                </span>
                                            </div>
                                            <div class="border-t border-white/[0.08] my-1.5"></div>
                                            <!-- PIC Ganda PI -->
                                            <div class="h-[36px] flex items-center text-slate-300 font-medium">
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-amber-500/10 text-amber-300 border border-amber-500/20">
                                                    <i data-lucide="user-check" class="w-3.5 h-3.5 text-amber-400"></i>
                                                    <span>{{ $comp->pic_ganda_pi->name ?? $comp->pic->name ?? 'Belum Ditugaskan' }}</span>
                                                </span>
                                            </div>
                                        </div>
                                    @elseif(in_array($comp->code, ['MTQ', 'POP']))
                                        <div class="flex flex-col py-1 text-xs">
                                            <!-- PIC PA -->
                                            <div class="h-[36px] flex items-center text-slate-300 font-medium">
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-500/10 text-emerald-300 border border-emerald-500/20">
                                                    <i data-lucide="user-check" class="w-3.5 h-3.5 text-emerald-400"></i>
                                                    <span>{{ $comp->pic_pa->name ?? $comp->pic->name ?? 'Belum Ditugaskan' }}</span>
                                                </span>
                                            </div>
                                            <div class="border-t border-white/[0.08] my-1.5"></div>
                                            <!-- PIC PI -->
                                            <div class="h-[36px] flex items-center text-slate-300 font-medium">
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-pink-500/10 text-pink-300 border border-pink-500/20">
                                                    <i data-lucide="user-check" class="w-3.5 h-3.5 text-pink-400"></i>
                                                    <span>{{ $comp->pic_pi->name ?? $comp->pic->name ?? 'Belum Ditugaskan' }}</span>
                                                </span>
                                            </div>
                                        </div>
                                    @elseif($comp->code === 'TMJ')
                                        <div class="flex flex-col py-1 text-xs">
                                            <!-- PIC Tunggal PA -->
                                            <div class="h-[56px] flex items-center text-slate-300 font-medium">
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-500/10 text-emerald-300 border border-emerald-500/20">
                                                    <i data-lucide="user-check" class="w-3.5 h-3.5 text-emerald-400"></i>
                                                    <span>{{ $comp->pic_tunggal_pa->name ?? $comp->pic->name ?? 'Belum Ditugaskan' }}</span>
                                                </span>
                                            </div>
                                            <div class="border-t border-white/[0.08] my-1.5"></div>
                                            <!-- PIC Tunggal PI -->
                                            <div class="h-[56px] flex items-center text-slate-300 font-medium">
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-pink-500/10 text-pink-300 border border-pink-500/20">
                                                    <i data-lucide="user-check" class="w-3.5 h-3.5 text-pink-400"></i>
                                                    <span>{{ $comp->pic_tunggal_pi->name ?? $comp->pic->name ?? 'Belum Ditugaskan' }}</span>
                                                </span>
                                            </div>
                                        </div>
                                    @else
                                        @php $allPics = $comp->all_pics; @endphp
                                        @if($allPics->isNotEmpty())
                                            <div class="flex flex-col gap-1.5">
                                                @foreach($allPics as $p)
                                                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-[#4E6EFF]/10 text-slate-200 border border-[#4E6EFF]/20 text-xs">
                                                        <i data-lucide="user-check" class="w-3.5 h-3.5 text-[#84D0FF] shrink-0"></i>
                                                        <span class="font-bold truncate">{{ $p->name }}</span>
                                                        @if(!empty($p->phone))
                                                            <span class="text-[10px] text-emerald-400 font-mono">({{ $p->phone }})</span>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="flex items-center gap-1.5 font-medium text-slate-500 italic text-xs">
                                                <i data-lucide="user-x" class="w-3.5 h-3.5 text-slate-500"></i>
                                                <span>Belum Ditugaskan</span>
                                            </div>
                                        @endif
                                    @endif
                                </td>
                                <td class="py-4 px-6 text-center whitespace-nowrap align-middle">
                                    @if($comp->code === 'BLT')
                                        <div class="flex flex-col py-1 text-xs">
                                            <!-- Status Tunggal PA (3 Baris Kat A, B, C) -->
                                            <div class="h-[84px] flex flex-col justify-center gap-1.5 font-bold text-[11px]">
                                                @php
                                                    $stPaA = $comp->status_a_tunggal_pa ?? $comp->status;
                                                    $stPaB = $comp->status_b_tunggal_pa ?? $comp->status;
                                                    $stPaC = $comp->status_c_tunggal_pa ?? $comp->status;
                                                @endphp
                                                <div class="h-[22px] flex items-center justify-center">
                                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold capitalize {{ $stPaA === 'buka' ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30' : ($stPaA === 'tutup' ? 'bg-rose-500/15 text-rose-400 border border-rose-500/30' : 'bg-white/[0.05] text-slate-400 border border-white/[0.08]') }}">
                                                        {{ $stPaA }}
                                                    </span>
                                                </div>
                                                <div class="h-[22px] flex items-center justify-center">
                                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold capitalize {{ $stPaB === 'buka' ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30' : ($stPaB === 'tutup' ? 'bg-rose-500/15 text-rose-400 border border-rose-500/30' : 'bg-white/[0.05] text-slate-400 border border-white/[0.08]') }}">
                                                        {{ $stPaB }}
                                                    </span>
                                                </div>
                                                <div class="h-[22px] flex items-center justify-center">
                                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold capitalize {{ $stPaC === 'buka' ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30' : ($stPaC === 'tutup' ? 'bg-rose-500/15 text-rose-400 border border-rose-500/30' : 'bg-white/[0.05] text-slate-400 border border-white/[0.08]') }}">
                                                        {{ $stPaC }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="border-t border-white/[0.08] my-1.5"></div>
                                            <!-- Status Tunggal PI (3 Baris Kat A, B, C) -->
                                            <div class="h-[84px] flex flex-col justify-center gap-1.5 font-bold text-[11px]">
                                                @php
                                                    $stPiA = $comp->status_a_tunggal_pi ?? $comp->status;
                                                    $stPiB = $comp->status_b_tunggal_pi ?? $comp->status;
                                                    $stPiC = $comp->status_c_tunggal_pi ?? $comp->status;
                                                @endphp
                                                <div class="h-[22px] flex items-center justify-center">
                                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold capitalize {{ $stPiA === 'buka' ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30' : ($stPiA === 'tutup' ? 'bg-rose-500/15 text-rose-400 border border-rose-500/30' : 'bg-white/[0.05] text-slate-400 border border-white/[0.08]') }}">
                                                        {{ $stPiA }}
                                                    </span>
                                                </div>
                                                <div class="h-[22px] flex items-center justify-center">
                                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold capitalize {{ $stPiB === 'buka' ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30' : ($stPiB === 'tutup' ? 'bg-rose-500/15 text-rose-400 border border-rose-500/30' : 'bg-white/[0.05] text-slate-400 border border-white/[0.08]') }}">
                                                        {{ $stPiB }}
                                                    </span>
                                                </div>
                                                <div class="h-[22px] flex items-center justify-center">
                                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold capitalize {{ $stPiC === 'buka' ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30' : ($stPiC === 'tutup' ? 'bg-rose-500/15 text-rose-400 border border-rose-500/30' : 'bg-white/[0.05] text-slate-400 border border-white/[0.08]') }}">
                                                        {{ $stPiC }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="border-t border-white/[0.08] my-1.5"></div>
                                            <!-- Status Ganda PA -->
                                            <div class="h-[36px] flex items-center justify-center">
                                                @php $sgPa = $comp->status_ganda_pa ?? $comp->status; @endphp
                                                <span class="px-2.5 py-1 rounded-full text-xs font-bold capitalize {{ $sgPa === 'buka' ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30' : ($sgPa === 'tutup' ? 'bg-rose-500/15 text-rose-400 border border-rose-500/30' : 'bg-white/[0.05] text-slate-400 border border-white/[0.08]') }}">
                                                    {{ $sgPa }}
                                                </span>
                                            </div>
                                            <div class="border-t border-white/[0.08] my-1.5"></div>
                                            <!-- Status Ganda PI -->
                                            <div class="h-[36px] flex items-center justify-center">
                                                @php $sgPi = $comp->status_ganda_pi ?? $comp->status; @endphp
                                                <span class="px-2.5 py-1 rounded-full text-xs font-bold capitalize {{ $sgPi === 'buka' ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30' : ($sgPi === 'tutup' ? 'bg-rose-500/15 text-rose-400 border border-rose-500/30' : 'bg-white/[0.05] text-slate-400 border border-white/[0.08]') }}">
                                                    {{ $sgPi }}
                                                </span>
                                            </div>
                                        </div>
                                    @elseif(in_array($comp->code, ['MTQ', 'POP']))
                                        <div class="flex flex-col py-1 text-xs">
                                            <!-- Status PA -->
                                            <div class="h-[36px] flex items-center justify-center">
                                                @php $stPa = $comp->status_pa ?? $comp->status; @endphp
                                                <span class="px-2.5 py-1 rounded-full text-xs font-bold capitalize {{ $stPa === 'buka' ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30' : ($stPa === 'tutup' ? 'bg-rose-500/15 text-rose-400 border border-rose-500/30' : 'bg-white/[0.05] text-slate-400 border border-white/[0.08]') }}">
                                                    {{ $stPa }}
                                                </span>
                                            </div>
                                            <div class="border-t border-white/[0.08] my-1.5"></div>
                                            <!-- Status PI -->
                                            <div class="h-[36px] flex items-center justify-center">
                                                @php $stPi = $comp->status_pi ?? $comp->status; @endphp
                                                <span class="px-2.5 py-1 rounded-full text-xs font-bold capitalize {{ $stPi === 'buka' ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30' : ($stPi === 'tutup' ? 'bg-rose-500/15 text-rose-400 border border-rose-500/30' : 'bg-white/[0.05] text-slate-400 border border-white/[0.08]') }}">
                                                    {{ $stPi }}
                                                </span>
                                            </div>
                                        </div>
                                    @elseif($comp->code === 'TMJ')
                                        <div class="flex flex-col py-1 text-xs">
                                            <!-- Status Tunggal PA (Kat A & B) -->
                                            <div class="h-[56px] flex flex-col justify-center gap-1.5 font-bold text-[11px]">
                                                @php
                                                    $stPaA = $comp->status_a_tunggal_pa ?? $comp->status;
                                                    $stPaB = $comp->status_b_tunggal_pa ?? $comp->status;
                                                @endphp
                                                <div class="h-[22px] flex items-center justify-center">
                                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold capitalize {{ $stPaA === 'buka' ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30' : ($stPaA === 'tutup' ? 'bg-rose-500/15 text-rose-400 border border-rose-500/30' : 'bg-white/[0.05] text-slate-400 border border-white/[0.08]') }}">
                                                        {{ $stPaA }}
                                                    </span>
                                                </div>
                                                <div class="h-[22px] flex items-center justify-center">
                                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold capitalize {{ $stPaB === 'buka' ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30' : ($stPaB === 'tutup' ? 'bg-rose-500/15 text-rose-400 border border-rose-500/30' : 'bg-white/[0.05] text-slate-400 border border-white/[0.08]') }}">
                                                        {{ $stPaB }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="border-t border-white/[0.08] my-1.5"></div>
                                            <!-- Status Tunggal PI (Kat A & B) -->
                                            <div class="h-[56px] flex flex-col justify-center gap-1.5 font-bold text-[11px]">
                                                @php
                                                    $stPiA = $comp->status_a_tunggal_pi ?? $comp->status;
                                                    $stPiB = $comp->status_b_tunggal_pi ?? $comp->status;
                                                @endphp
                                                <div class="h-[22px] flex items-center justify-center">
                                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold capitalize {{ $stPiA === 'buka' ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30' : ($stPiA === 'tutup' ? 'bg-rose-500/15 text-rose-400 border border-rose-500/30' : 'bg-white/[0.05] text-slate-400 border border-white/[0.08]') }}">
                                                        {{ $stPiA }}
                                                    </span>
                                                </div>
                                                <div class="h-[22px] flex items-center justify-center">
                                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold capitalize {{ $stPiB === 'buka' ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30' : ($stPiB === 'tutup' ? 'bg-rose-500/15 text-rose-400 border border-rose-500/30' : 'bg-white/[0.05] text-slate-400 border border-white/[0.08]') }}">
                                                        {{ $stPiB }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-xs font-bold capitalize {{ $comp->status === 'buka' ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30' : ($comp->status === 'tutup' ? 'bg-rose-500/15 text-rose-400 border border-rose-500/30' : 'bg-white/[0.05] text-slate-400 border border-white/[0.08]') }}">
                                            {{ $comp->status }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-4 px-6 text-center whitespace-nowrap align-middle">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('admin.competitions.edit', $comp->id) }}" class="p-2 rounded-xl bg-white/[0.06] hover:bg-white/[0.12] text-slate-200 border border-white/[0.1] transition cursor-pointer" title="Edit Cabang Lomba {{ $comp->name }}">
                                            <i data-lucide="edit-3" class="w-4 h-4"></i>
                                        </a>
                                        <form action="{{ route('admin.competitions.delete', $comp->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus cabang lomba {{ $comp->name }} beserta seluruh data pendaftarannya?')">
                                            @csrf
                                            <button type="submit" class="p-2 rounded-xl bg-rose-500/15 hover:bg-rose-500/25 text-rose-400 border border-rose-500/30 transition cursor-pointer" title="Hapus Cabang Lomba {{ $comp->name }}">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-12 text-center text-slate-400">
                                    Belum ada cabang lomba yang terdaftar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- ==================== TAB 2: TIMELINE & JADWAL KEGIATAN ==================== -->
    <div x-show="activeTab === 'timeline'" x-cloak x-transition class="space-y-6">
        
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h3 class="text-base font-bold text-white">Jadwal Rangkaian Waktu & Tanggal Kegiatan</h3>
                <p class="text-xs text-slate-400">Tanggal yang diatur di sini akan otomatis tampil dinamis di halaman depan (Landing Page)</p>
            </div>
            <button type="button" @click="createTimelineModal = true" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-black text-xs shadow-md shadow-amber-500/20 transition cursor-pointer">
                <i data-lucide="calendar-plus" class="w-4 h-4"></i>
                <span>Tambah Jadwal Baru</span>
            </button>
        </div>

        <!-- Timelines Table -->
        <div class="ai-card rounded-3xl border border-white/[0.08] shadow-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-300">
                    <thead class="text-xs font-bold uppercase tracking-wider bg-[#0C111D]/90 text-slate-400 border-b border-white/[0.08]">
                        <tr>
                            <th class="py-4 px-4 text-center w-12">No</th>
                            <th class="py-4 px-6">Tanggal / Rentang Waktu</th>
                            <th class="py-4 px-6">Nama Agenda / Kegiatan</th>
                            <th class="py-4 px-6">Waktu & Lokasi</th>
                            <th class="py-4 px-6">Keterangan</th>
                            <th class="py-4 px-4 text-center">Status</th>
                            <th class="py-4 px-6 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/[0.04] font-medium">
                        @forelse($timelines as $t)
                            <tr class="hover:bg-white/[0.025] transition text-xs">
                                <td class="py-4 px-4 text-center font-mono font-bold text-slate-400">
                                    {{ $t->order }}
                                </td>
                                <td class="py-4 px-6 font-bold text-emerald-400">
                                    <div class="flex items-center gap-2">
                                        <i data-lucide="calendar" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                                        <span>{{ $t->date_label }}</span>
                                    </div>
                                </td>
                                <td class="py-4 px-6 font-black text-white text-sm">
                                    {{ $t->title }}
                                </td>
                                <td class="py-4 px-6 text-slate-400 space-y-0.5">
                                    @if($t->time_label)
                                        <div class="flex items-center gap-1.5 text-[11px] font-semibold text-slate-200">
                                            <i data-lucide="clock" class="w-3.5 h-3.5 text-amber-400 shrink-0"></i>
                                            <span>{{ $t->time_label }}</span>
                                        </div>
                                    @endif
                                    @if($t->location)
                                        <div class="flex items-center gap-1.5 text-[11px] text-slate-400">
                                            <i data-lucide="map-pin" class="w-3.5 h-3.5 text-[#4E6EFF] shrink-0"></i>
                                            <span>{{ $t->location }}</span>
                                        </div>
                                    @endif
                                </td>
                                <td class="py-4 px-6 text-slate-400 max-w-xs truncate text-[11px]">
                                    {{ $t->description ?? '-' }}
                                </td>
                                <td class="py-4 px-4 text-center">
                                    @if($t->is_active)
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">
                                            Tampil
                                        </span>
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-white/[0.05] text-slate-500 border border-white/[0.08]">
                                            Disembunyikan
                                        </span>
                                    @endif
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button type="button" @click="editTimeline({{ $t->toJson() }})" class="p-2 rounded-xl bg-white/[0.06] hover:bg-white/[0.12] text-slate-200 border border-white/[0.1] transition cursor-pointer" title="Edit Jadwal">
                                            <i data-lucide="edit-3" class="w-4 h-4"></i>
                                        </button>
                                        <form action="{{ route('admin.timeline.delete', $t->id) }}" method="POST" onsubmit="return confirm('Hapus jadwal ini dari rangkaian acara?')">
                                            @csrf
                                            <button type="submit" class="p-2 rounded-xl bg-rose-500/15 hover:bg-rose-500/25 text-rose-400 border border-rose-500/30 transition cursor-pointer" title="Hapus">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-500">
                                    Belum ada jadwal rangkaian acara.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- Create Competition Modal -->
    <div x-show="createModal" x-cloak class="fixed inset-0 z-[70] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
        <div class="flex items-center justify-center min-h-screen p-4 text-center sm:p-0">
            <!-- Backdrop -->
            <div x-show="createModal" @click="createModal = false" class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm transition-opacity"></div>

            <!-- Modal Content (Crisp, High Z-Index) -->
            <div x-show="createModal" class="relative z-10 inline-block bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all my-8 sm:align-middle sm:max-w-xl w-full p-6 sm:p-8 space-y-6">
                
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <h3 class="text-lg font-black text-slate-900">Tambah Cabang Lomba Baru</h3>
                    <button @click="createModal = false" class="text-slate-400 hover:text-slate-600">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form action="{{ route('admin.competitions.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf

                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600">Jenis Lomba</label>
                            <button type="button" @click="createModal = false; createCategoryModal = true" class="text-xs font-bold text-blue-600 hover:text-blue-700 flex items-center gap-1">
                                <i data-lucide="plus-circle" class="w-3.5 h-3.5"></i>
                                <span>+ Buat Jenis Lomba Baru</span>
                            </button>
                        </div>
                        <select name="category_id" required class="block w-full px-4 py-2.5 rounded-xl bg-white border border-slate-200 text-sm font-semibold text-slate-900 outline-none focus:border-emerald-500">
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-12 gap-3">
                        <div class="col-span-12 sm:col-span-6">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Nama Lomba</label>
                            <input name="name" type="text" required placeholder="Contoh: Story Telling Bahasa Inggris" class="block w-full px-4 py-2.5 rounded-xl bg-white border border-slate-200 text-sm font-semibold text-slate-900 outline-none focus:border-emerald-500">
                        </div>
                        <div class="col-span-6 sm:col-span-3">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Kode Singkat</label>
                            <input name="code" type="text" required placeholder="STG" class="block w-full px-4 py-2.5 rounded-xl bg-white border border-slate-200 text-sm font-mono font-bold text-slate-900 outline-none uppercase focus:border-emerald-500">
                        </div>
                        <div class="col-span-6 sm:col-span-3">
                            <label class="block text-xs font-bold uppercase tracking-wider text-amber-700 mb-1.5">No. Urut (Order)</label>
                            <input name="order" type="number" min="1" placeholder="1" class="block w-full px-3 py-2.5 rounded-xl bg-amber-50/60 border border-amber-300/80 text-sm font-mono font-black text-amber-900 outline-none focus:border-amber-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Kategori Lomba</label>
                            <select name="type" required class="block w-full px-3 py-2.5 rounded-xl bg-white border border-slate-200 text-xs font-bold text-slate-900 outline-none focus:border-emerald-500">
                                <option value="individu">Individu</option>
                                <option value="tim">Tim</option>
                                <option value="kelompok">Kelompok</option>
                                <option value="regu">Regu</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Min Anggota</label>
                            <input name="min_members" type="number" value="1" min="1" required class="block w-full px-3 py-2.5 rounded-xl bg-white border border-slate-200 text-xs font-bold text-slate-900 outline-none focus:border-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Maks Anggota</label>
                            <input name="max_members" type="number" value="1" min="1" required class="block w-full px-3 py-2.5 rounded-xl bg-white border border-slate-200 text-xs font-bold text-slate-900 outline-none focus:border-emerald-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Biaya Pendaftaran (Rp)</label>
                            <input name="registration_fee" type="number" value="0" min="0" step="1000" placeholder="0" class="block w-full px-4 py-2.5 rounded-xl bg-white border border-slate-200 text-sm font-mono font-bold text-emerald-800 outline-none focus:border-emerald-500">
                            <span class="text-[10px] text-slate-400">Isi 0 jika gratis</span>
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Kuota Peserta</label>
                            <input name="quota" type="number" value="50" min="0" required class="block w-full px-4 py-2.5 rounded-xl bg-white border border-slate-200 text-sm font-bold text-slate-900 outline-none focus:border-emerald-500">
                            <span class="text-[10px] text-slate-400">Isi 0 untuk kuota tak terbatas (∞)</span>
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Koordinator PIC Utama</label>
                            <select name="pic_id" class="block w-full px-3 py-2.5 rounded-xl bg-white border border-slate-200 text-xs font-semibold text-slate-900 outline-none focus:border-emerald-500">
                                <option value="">-- Pilih PIC Utama --</option>
                                @foreach($pics as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }} {{ $p->phone ? '('.$p->phone.')' : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- TIM PETUGAS PIC (MULTI-PIC) -->
                    <div class="space-y-2 p-3.5 rounded-2xl bg-slate-50 border border-slate-200/80">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 flex items-center gap-1.5">
                                <i data-lucide="users" class="w-4 h-4 text-blue-600"></i>
                                <span>Tim Petugas PIC (Multi-PIC & Notifikasi WhatsApp)</span>
                            </label>
                            <span class="text-[11px] text-slate-400 font-medium">Centang 1 atau lebih PIC</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-36 overflow-y-auto">
                            @foreach($pics as $p)
                                <label class="flex items-center gap-2.5 p-2 rounded-xl bg-white border border-slate-200 hover:border-blue-400 text-xs cursor-pointer transition select-none">
                                    <input type="checkbox" name="pic_ids[]" value="{{ $p->id }}" class="rounded text-blue-600 border-slate-300">
                                    <span class="font-bold text-slate-800">{{ $p->name }}</span>
                                    @if(!empty($p->phone))
                                        <span class="text-[10px] text-emerald-600 font-mono">({{ $p->phone }})</span>
                                    @endif
                                </label>
                            @endforeach
                        </div>
                        <p class="text-[11px] text-slate-500">Seluruh PIC yang dicentang akan otomatis menerima pesan notifikasi WhatsApp saat ada peserta mendaftar.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Aturan & Petunjuk Teknis Singkat</label>
                        <textarea name="rules" rows="3" placeholder="Tuliskan petunjuk teknis pelaksanaan..." class="block w-full px-4 py-2.5 rounded-xl bg-white border border-slate-200 text-xs font-medium text-slate-900 outline-none focus:border-emerald-500"></textarea>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 flex items-center gap-1.5">
                                <i data-lucide="file-text" class="w-4 h-4 text-brand-600"></i>
                                <span>Embed Link Juknis PDF / Dokumen Resmi</span>
                            </label>
                            <span class="text-[10px] text-slate-400 font-semibold">Google Drive / URL PDF / Upload</span>
                        </div>
                        
                        <div class="space-y-2">
                            <div class="flex items-center gap-2">
                                <div class="relative flex-1">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                        <i data-lucide="link" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <input name="guidelines_file" type="text" placeholder="Paste link Google Drive, URL PDF, atau kode embed (misal: https://drive.google.com/file/d/.../view)" class="block w-full pl-9 pr-3 py-2 rounded-xl bg-white border border-slate-200 text-xs font-medium text-slate-900 outline-none focus:border-brand-500 shadow-sm">
                                </div>
                                <label class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold border border-slate-200 cursor-pointer flex items-center gap-1.5 shrink-0 transition" title="Upload file PDF langsung">
                                    <i data-lucide="upload" class="w-3.5 h-3.5 text-slate-500"></i>
                                    <span>Upload PDF</span>
                                    <input type="file" name="guidelines_pdf" accept=".pdf" class="hidden" @change="if($event.target.files.length > 0) { $refs.createPdfName.innerText = '📁 File terpilih: ' + $event.target.files[0].name; }">
                                </label>
                            </div>
                            <p class="text-[11px] text-slate-500 flex items-center gap-1">
                                <i data-lucide="info" class="w-3.5 h-3.5 text-blue-500 shrink-0"></i>
                                <span>Bisa memasukkan link Google Drive (pastikan akses publik), direct link PDF, atau upload file PDF (maks. 20MB).</span>
                            </p>
                            <div x-ref="createPdfName" class="text-xs font-bold text-emerald-600"></div>
                        </div>
                    </div>

                    <!-- LINK GRUP WHATSAPP CABANG LOMBA -->
                    <div class="space-y-2 p-3.5 rounded-2xl bg-emerald-50/50 border border-emerald-200/80">
                        <label class="block text-xs font-black uppercase tracking-wider text-emerald-800 flex items-center gap-2">
                            <i data-lucide="message-circle" class="w-4 h-4 text-emerald-600"></i>
                            <span>Tautan Undangan Grup WhatsApp Cabang (Opsional)</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-emerald-500">
                                <i data-lucide="link-2" class="w-3.5 h-3.5"></i>
                            </div>
                            <input name="whatsapp_group_url" type="url" placeholder="https://chat.whatsapp.com/Gzxxxxxxxxxx" class="block w-full pl-9 pr-3 py-2 rounded-xl bg-white border border-emerald-200 text-xs font-medium text-slate-900 outline-none focus:border-emerald-500 shadow-sm font-mono">
                        </div>
                        <p class="text-[11px] text-slate-500">Jika diisi, icon WhatsApp akan otomatis tampil di samping tombol Juknis di halaman depan.</p>
                    </div>

                    <!-- KRITERIA PENILAIAN DEWAN JURI -->
                    <div class="space-y-3 pt-3 border-t border-slate-100">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 bg-slate-50 p-3 rounded-2xl border border-slate-200/80">
                            <div>
                                <label class="block text-xs font-black uppercase tracking-wider text-slate-800 flex items-center gap-1.5">
                                    <i data-lucide="scale" class="w-4 h-4 text-amber-500"></i>
                                    <span>Kriteria Penilaian Dewan Juri</span>
                                </label>
                                <p class="text-[11px] text-slate-500">Atur kriteria penilaian dan visibilitasnya pada pendaftar</p>
                            </div>
                            
                            <div class="flex items-center gap-2 shrink-0">
                                <!-- Toggle Aktif / Nonaktif -->
                                <input type="hidden" name="show_criteria" :value="newCompetition.show_criteria ? '1' : '0'">
                                <button type="button" 
                                        @click="newCompetition.show_criteria = !newCompetition.show_criteria"
                                        :class="newCompetition.show_criteria ? 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-emerald-500/20' : 'bg-slate-200 hover:bg-slate-300 text-slate-700'"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition shadow-sm cursor-pointer"
                                        :title="newCompetition.show_criteria ? 'Klik untuk menonaktifkan kriteria di halaman pendaftar' : 'Klik untuk mengaktifkan kriteria di halaman pendaftar'">
                                    <span class="w-2 h-2 rounded-full" :class="newCompetition.show_criteria ? 'bg-emerald-200 animate-pulse' : 'bg-slate-400'"></span>
                                    <span x-text="newCompetition.show_criteria ? '✓ Aktif (Tampil)' : '✗ Nonaktif (Sembunyi)'"></span>
                                </button>

                                <!-- Button Tambah Kriteria -->
                                <button type="button" @click="addCriterion(newCompetition)" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-white hover:bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-bold transition cursor-pointer shadow-xs">
                                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                    <span>+ Kriteria</span>
                                </button>
                            </div>
                        </div>

                        <!-- Notification banner when non-aktif -->
                        <div x-show="!newCompetition.show_criteria" class="p-3 rounded-2xl bg-amber-50 border border-amber-200/80 text-xs text-amber-900 flex items-start gap-2.5">
                            <i data-lucide="eye-off" class="w-4 h-4 text-amber-600 shrink-0 mt-0.5"></i>
                            <div>
                                <span class="font-bold block text-[11px] uppercase tracking-wider text-amber-800">Status: Nonaktif di Halaman Pendaftar</span>
                                <p class="text-[11px] text-amber-700 mt-0.5 leading-relaxed">
                                    Kriteria penilaian ini <strong>disembunyikan</strong> dan tidak akan muncul di halaman detail lomba maupun formulir pendaftar.
                                </p>
                            </div>
                        </div>

                        <!-- Criteria List -->
                        <div class="space-y-2.5 max-h-60 overflow-y-auto pr-1">
                            <template x-for="(crit, cIdx) in newCompetition.criteria" :key="cIdx">
                                <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200 space-y-2">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1">
                                            <input :name="'criteria[' + cIdx + '][name]'" type="text" required x-model="crit.name" placeholder="Nama Kriteria (misal: Kaidah Tajwid)" class="block w-full px-3 py-1.5 rounded-xl bg-white border border-slate-200 text-xs font-bold text-slate-800 outline-none focus:border-emerald-500">
                                        </div>
                                        <div class="w-24 shrink-0 flex items-center gap-1">
                                            <input :name="'criteria[' + cIdx + '][weight_percentage]'" type="number" min="1" max="100" required x-model.number="crit.weight_percentage" placeholder="Bobot" class="block w-full px-2 py-1.5 rounded-xl bg-white border border-slate-200 text-xs font-mono font-black text-center text-emerald-700 outline-none focus:border-emerald-500">
                                            <span class="text-xs font-bold text-slate-500">%</span>
                                        </div>
                                        <button type="button" @click="removeCriterion(newCompetition, cIdx)" class="p-1.5 rounded-lg hover:bg-rose-100 text-rose-500 transition cursor-pointer" title="Hapus Kriteria">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                        <div class="sm:col-span-2">
                                            <input :name="'criteria[' + cIdx + '][description]'" type="text" x-model="crit.description" placeholder="Penjelasan kriteria (opsional)" class="block w-full px-2.5 py-1 rounded-lg bg-white border border-slate-200 text-[11px] text-slate-600 outline-none">
                                        </div>
                                        <div class="flex items-center gap-1 text-[10px] text-slate-400 font-bold justify-end">
                                            <span>Skor:</span>
                                            <input :name="'criteria[' + cIdx + '][min_score]'" type="number" x-model="crit.min_score" class="w-12 px-1.5 py-0.5 rounded bg-white border border-slate-200 text-center font-mono text-[11px] text-slate-700">
                                            <span>-</span>
                                            <input :name="'criteria[' + cIdx + '][max_score]'" type="number" x-model="crit.max_score" class="w-12 px-1.5 py-0.5 rounded bg-white border border-slate-200 text-center font-mono text-[11px] text-slate-700">
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <div x-show="!newCompetition.criteria || newCompetition.criteria.length === 0" class="p-3 text-center rounded-xl border border-dashed border-slate-200 text-slate-400 text-xs">
                                Belum ada kriteria khusus. Klik tombol <strong>+ Tambah Kriteria</strong> di atas.
                            </div>
                        </div>

                        <!-- Total Weight Badge -->
                        <div x-show="newCompetition.criteria && newCompetition.criteria.length > 0" class="flex items-center justify-between text-xs px-2 pt-1 font-bold">
                            <span class="text-slate-500">Total Akumulasi Bobot:</span>
                            <span :class="calculateTotalWeight(newCompetition.criteria) === 100 ? 'text-emerald-700 bg-emerald-100 px-2.5 py-0.5 rounded-full font-black' : 'text-amber-700 bg-amber-100 px-2.5 py-0.5 rounded-full font-black'" x-text="calculateTotalWeight(newCompetition.criteria) + '% ' + (calculateTotalWeight(newCompetition.criteria) === 100 ? '(Pas 100%)' : '(Disarankan total 100%)')"></span>
                        </div>
                    </div>

                    <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                        <button type="button" @click="createModal = false" class="px-5 py-2.5 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold">
                            Batal
                        </button>
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold shadow-md shadow-brand-500/20">
                            Simpan Cabang Lomba
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <!-- Create Timeline Modal -->
    <div x-show="createTimelineModal" x-cloak class="fixed inset-0 z-[100] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
        <div class="flex items-center justify-center min-h-screen p-4 text-center sm:p-0">
            <!-- Backdrop -->
            <div x-show="createTimelineModal" @click="createTimelineModal = false" class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm transition-opacity"></div>

            <!-- Modal Content (Crisp, High Z-Index) -->
            <div x-show="createTimelineModal" class="relative z-10 inline-block bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all my-8 sm:align-middle sm:max-w-lg w-full p-6 sm:p-8 space-y-6">
                
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <h3 class="text-lg font-black text-slate-900">Tambah Jadwal Rangkaian Acara Baru</h3>
                    <button @click="createTimelineModal = false" class="text-slate-400 hover:text-slate-600">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form action="{{ route('admin.timeline.store') }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Nama Kegiatan / Agenda</label>
                        <input name="title" type="text" required placeholder="Contoh: Pendaftaran & Upload Berkas" class="block w-full px-4 py-2.5 rounded-xl bg-white border border-slate-200 text-sm font-semibold text-slate-900 outline-none focus:border-amber-500">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Tanggal / Rentang Waktu</label>
                            <input name="date_label" type="text" required placeholder="Contoh: 01 – 15 September 2026" class="block w-full px-4 py-2.5 rounded-xl bg-white border border-slate-200 text-sm outline-none font-bold text-emerald-700 focus:border-amber-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Urutan Tampil (No.)</label>
                            <input name="order" type="number" value="{{ $timelines->count() + 1 }}" min="1" required class="block w-full px-4 py-2.5 rounded-xl bg-white border border-slate-200 text-sm font-bold text-slate-900 outline-none focus:border-amber-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Jam / Waktu (Opsional)</label>
                            <input name="time_label" type="text" placeholder="Contoh: 08.00 WIB – Selesai" class="block w-full px-4 py-2.5 rounded-xl bg-white border border-slate-200 text-xs font-medium text-slate-900 outline-none focus:border-amber-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Lokasi (Opsional)</label>
                            <input name="location" type="text" placeholder="Contoh: Aula MTsN 1 Blitar" class="block w-full px-4 py-2.5 rounded-xl bg-white border border-slate-200 text-xs font-medium text-slate-900 outline-none focus:border-amber-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Keterangan Tambahan</label>
                        <textarea name="description" rows="3" placeholder="Tuliskan petunjuk teknis atau rincian kegiatan..." class="block w-full px-4 py-2.5 rounded-xl bg-white border border-slate-200 text-xs font-medium text-slate-900 outline-none focus:border-amber-500"></textarea>
                    </div>

                    <div>
                        <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-slate-700">
                            <input type="checkbox" name="is_active" value="1" checked class="w-4 h-4 rounded text-brand-600">
                            <span>Tampilkan di Landing Page</span>
                        </label>
                    </div>

                    <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                        <button type="button" @click="createTimelineModal = false" class="px-5 py-2.5 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold">
                            Batal
                        </button>
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-black text-xs shadow-md shadow-amber-500/20">
                            Simpan Jadwal
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <!-- Edit Timeline Modal -->
    <div x-show="editTimelineModal" x-cloak class="fixed inset-0 z-[100] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
        <div class="flex items-center justify-center min-h-screen p-4 text-center sm:p-0">
            <!-- Backdrop -->
            <div x-show="editTimelineModal" @click="editTimelineModal = false" class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm transition-opacity"></div>

            <!-- Modal Content (Crisp, High Z-Index) -->
            <div x-show="editTimelineModal" class="relative z-10 inline-block bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all my-8 sm:align-middle sm:max-w-lg w-full p-6 sm:p-8 space-y-6">
                
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <h3 class="text-lg font-black text-slate-900">Edit Jadwal Rangkaian Acara</h3>
                    <button @click="editTimelineModal = false" class="text-slate-400 hover:text-slate-600">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form :action="'{{ url('admin/timeline') }}/' + (selectedTimeline ? selectedTimeline.id : '') + '/update'" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Nama Kegiatan / Agenda</label>
                        <input name="title" type="text" required x-model="selectedTimeline.title" class="block w-full px-4 py-2.5 rounded-xl bg-white border border-slate-200 text-sm font-semibold text-slate-900 outline-none focus:border-amber-500">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Tanggal / Rentang Waktu</label>
                            <input name="date_label" type="text" required x-model="selectedTimeline.date_label" class="block w-full px-4 py-2.5 rounded-xl bg-white border border-slate-200 text-sm outline-none font-bold text-emerald-700 focus:border-amber-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Urutan Tampil (No.)</label>
                            <input name="order" type="number" min="1" required x-model="selectedTimeline.order" class="block w-full px-4 py-2.5 rounded-xl bg-white border border-slate-200 text-sm font-bold text-slate-900 outline-none focus:border-amber-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Jam / Waktu (Opsional)</label>
                            <input name="time_label" type="text" x-model="selectedTimeline.time_label" class="block w-full px-4 py-2.5 rounded-xl bg-white border border-slate-200 text-xs font-medium text-slate-900 outline-none focus:border-amber-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Lokasi (Opsional)</label>
                            <input name="location" type="text" x-model="selectedTimeline.location" class="block w-full px-4 py-2.5 rounded-xl bg-white border border-slate-200 text-xs font-medium text-slate-900 outline-none focus:border-amber-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Keterangan Tambahan</label>
                        <textarea name="description" rows="3" x-model="selectedTimeline.description" class="block w-full px-4 py-2.5 rounded-xl bg-white border border-slate-200 text-xs font-medium text-slate-900 outline-none focus:border-amber-500"></textarea>
                    </div>

                    <div>
                        <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-slate-700">
                            <input type="checkbox" name="is_active" value="1" :checked="selectedTimeline.is_active" class="w-4 h-4 rounded text-brand-600">
                            <span>Tampilkan di Landing Page</span>
                        </label>
                    </div>

                    <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                        <button type="button" @click="editTimelineModal = false" class="px-5 py-2.5 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold">
                            Batal
                        </button>
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-500/20">
                            Perbarui Jadwal
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    @if(false)
    <!-- Edit Competition Fullpage Workspace (1 Halaman Penuh, Menutup Seluruh Layar & Sidebar) -->
    <div x-show="editCompetitionModal" x-cloak aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none; position: fixed; inset: 0; z-index: 999999; background: #070A13; overflow-y: auto; min-height: 100vh; width: 100vw; flex-direction: column;" :style="editCompetitionModal ? 'display: flex;' : 'display: none;'">
        
        <!-- Sticky Workspace Header Bar -->
        <div style="position: sticky; top: 0; z-index: 50; background: rgba(11,16,29,0.97); border-bottom: 1px solid rgba(255,255,255,0.1); padding: 14px 32px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 4px 32px rgba(0,0,0,0.6);">
            <div class="flex items-center gap-3.5 min-w-0">
                <button type="button" @click="closeEditCompetitionModal()" class="flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white/[0.08] hover:bg-white/[0.15] text-slate-200 hover:text-white text-xs font-bold transition cursor-pointer border border-white/[0.1] shrink-0">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    <span>Tutup & Kembali</span>
                </button>
                <div class="min-w-0">
                    <h3 class="text-sm sm:text-base font-black text-white truncate" x-text="
                        selectedCompetition.code === 'BLT' ? (
                            bltEditMode === 'tunggal_pa_a' ? 'Edit Bulu Tangkis — Tunggal Putra (Kat A)' :
                            bltEditMode === 'tunggal_pa_b' ? 'Edit Bulu Tangkis — Tunggal Putra (Kat B)' :
                            bltEditMode === 'tunggal_pa_c' ? 'Edit Bulu Tangkis — Tunggal Putra (Kat C)' :
                            bltEditMode === 'tunggal_pi_a' ? 'Edit Bulu Tangkis — Tunggal Putri (Kat A)' :
                            bltEditMode === 'tunggal_pi_b' ? 'Edit Bulu Tangkis — Tunggal Putri (Kat B)' :
                            bltEditMode === 'tunggal_pi_c' ? 'Edit Bulu Tangkis — Tunggal Putri (Kat C)' :
                            bltEditMode === 'ganda_pa' ? 'Edit Bulu Tangkis — Ganda Putra (PA)' :
                            bltEditMode === 'ganda_pi' ? 'Edit Bulu Tangkis — Ganda Putri (PI)' :
                            'Edit ' + selectedCompetition.name
                        ) : (
                        selectedCompetition.code === 'TMJ' ? (
                            bltEditMode === 'tmj_pa_a' ? 'Edit Tenis Meja — Tunggal Putra (Kat A)' :
                            bltEditMode === 'tmj_pa_b' ? 'Edit Tenis Meja — Tunggal Putra (Kat B)' :
                            bltEditMode === 'tmj_pi_a' ? 'Edit Tenis Meja — Tunggal Putri (Kat A)' :
                            bltEditMode === 'tmj_pi_b' ? 'Edit Tenis Meja — Tunggal Putri (Kat B)' :
                            'Edit ' + selectedCompetition.name
                        ) : (
                        ['MTQ', 'POP'].includes(selectedCompetition.code) ? (
                            'Edit ' + selectedCompetition.name + ' (' + (bltEditMode === 'pa' ? 'Putra / PA' : (bltEditMode === 'pi' ? 'Putri / PI' : 'Semua Sektor')) + ')'
                        ) : 'Edit Cabang Perlombaan'))"></h3>
                    <p class="text-xs text-slate-400 hidden sm:block truncate" x-text="
                        selectedCompetition.code === 'BLT' ? (
                            bltEditMode === 'tunggal_pa_a' ? 'Perbarui biaya, kuota, PIC, dan status Tunggal Putra Kat A (Kelas 1–2 SD/MI)' :
                            bltEditMode === 'tunggal_pa_b' ? 'Perbarui biaya, kuota, PIC, dan status Tunggal Putra Kat B (Kelas 3–4 SD/MI)' :
                            bltEditMode === 'tunggal_pa_c' ? 'Perbarui biaya, kuota, PIC, dan status Tunggal Putra Kat C (Kelas 5–6 SD/MI)' :
                            bltEditMode === 'tunggal_pi_a' ? 'Perbarui biaya, kuota, PIC, dan status Tunggal Putri Kat A (Kelas 1–2 SD/MI)' :
                            bltEditMode === 'tunggal_pi_b' ? 'Perbarui biaya, kuota, PIC, dan status Tunggal Putri Kat B (Kelas 3–4 SD/MI)' :
                            bltEditMode === 'tunggal_pi_c' ? 'Perbarui biaya, kuota, PIC, dan status Tunggal Putri Kat C (Kelas 5–6 SD/MI)' :
                            bltEditMode === 'ganda_pa' ? 'Perbarui biaya, kuota, PIC, dan status Ganda Putra (PA)' :
                            bltEditMode === 'ganda_pi' ? 'Perbarui biaya, kuota, PIC, dan status Ganda Putri (PI)' :
                            'Perbarui informasi cabang lomba'
                        ) : (
                        selectedCompetition.code === 'TMJ' ? (
                            bltEditMode === 'tmj_pa_a' ? 'Perbarui biaya, kuota, PIC, dan status Tunggal Putra Kat A (Kelas 1–3 SD/MI)' :
                            bltEditMode === 'tmj_pa_b' ? 'Perbarui biaya, kuota, PIC, dan status Tunggal Putra Kat B (Kelas 4–6 SD/MI)' :
                            bltEditMode === 'tmj_pi_a' ? 'Perbarui biaya, kuota, PIC, dan status Tunggal Putri Kat A (Kelas 1–3 SD/MI)' :
                            bltEditMode === 'tmj_pi_b' ? 'Perbarui biaya, kuota, PIC, dan status Tunggal Putri Kat B (Kelas 4–6 SD/MI)' :
                            'Perbarui informasi cabang lomba'
                        ) : (
                        ['MTQ', 'POP'].includes(selectedCompetition.code) ? 'Perbarui biaya, kuota, PIC, dan status per sektor Putra (PA) & Putri (PI)' : 'Perbarui informasi cabang lomba, kuota, PIC, dan status pendaftaran'))"></p>
                </div>
            </div>

            <div class="flex items-center gap-3 shrink-0">
                <button type="button" @click="closeEditCompetitionModal()" class="px-4 py-2 rounded-xl bg-white/[0.06] hover:bg-white/[0.1] text-slate-300 text-xs font-bold transition cursor-pointer">
                    Batal
                </button>
                <button type="button" @click="$refs.editCompetitionForm.submit()" class="px-5 py-2 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-black text-xs shadow-lg shadow-emerald-500/20 transition cursor-pointer flex items-center gap-1.5">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>Simpan Perubahan</span>
                </button>
            </div>
        </div>

        <!-- Fullpage Form Body Container -->
        <div class="flex-1 max-w-5xl w-full mx-auto p-4 sm:p-6 lg:p-8 space-y-6">
            <form x-ref="editCompetitionForm" :action="'{{ url('admin/competitions') }}/' + (selectedCompetition ? selectedCompetition.id : '') + '/update'" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <!-- Form Card -->
                <div class="bg-white rounded-3xl p-5 sm:p-7 shadow-2xl space-y-5 text-slate-900 border border-slate-200">
                    <div class="space-y-3.5 pr-1 sm:pr-2">

                        <!-- Row 1: Jenis Lomba, Nama Lomba, Kode Singkat -->
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                            <div class="md:col-span-3">
                                <div class="flex items-center justify-between mb-1">
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600">Jenis Lomba</label>
                                    <button type="button" @click="closeEditCompetitionModal(); createCategoryModal = true" class="text-[11px] font-bold text-blue-600 hover:text-blue-700 flex items-center gap-1">
                                        <i data-lucide="plus-circle" class="w-3.5 h-3.5"></i>
                                        <span>+ Baru</span>
                                    </button>
                                </div>
                                <select name="category_id" required x-model="selectedCompetition.category_id" class="block w-full px-3 py-2 rounded-xl bg-white border border-slate-200 text-xs font-semibold text-slate-900 outline-none focus:border-emerald-500">
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="md:col-span-4">
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Nama Lomba</label>
                                <input name="name" type="text" required x-model="selectedCompetition.name" class="block w-full px-3 py-2 rounded-xl bg-white border border-slate-200 text-xs font-semibold text-slate-900 outline-none focus:border-emerald-500">
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Kode Singkat</label>
                                <input name="code" type="text" required x-model="selectedCompetition.code" class="block w-full px-3 py-2 rounded-xl bg-white border border-slate-200 text-xs font-mono font-bold text-slate-900 outline-none uppercase focus:border-emerald-500">
                            </div>

                            <div class="md:col-span-3">
                                <label class="block text-xs font-bold uppercase tracking-wider text-amber-700 mb-1 flex items-center gap-1">
                                    <i data-lucide="list-ordered" class="w-3.5 h-3.5 text-amber-500"></i>
                                    <span>Urutan Tampilan</span>
                                </label>
                                <input name="order" type="number" min="1" x-model="selectedCompetition.order" class="block w-full px-3 py-2 rounded-xl bg-amber-50/60 border border-amber-300/80 text-xs font-mono font-black text-amber-900 outline-none focus:border-amber-500">
                            </div>
                        </div>

                        <!-- Row 2: Kategori Lomba, Min/Maks Anggota, Lokasi, Waktu -->
                        <div class="grid grid-cols-2 sm:grid-cols-12 gap-3">
                            <div class="sm:col-span-3">
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Kategori Lomba</label>
                                <select name="type" required x-model="selectedCompetition.type" class="block w-full px-2.5 py-2 rounded-xl bg-white border border-slate-200 text-xs font-bold text-slate-900 outline-none focus:border-emerald-500">
                                    <option value="individu">Individu</option>
                                    <option value="tim">Tim</option>
                                    <option value="kelompok">Kelompok</option>
                                    <option value="regu">Regu</option>
                                </select>
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Min Anggota</label>
                                <input name="min_members" type="number" min="1" required x-model="selectedCompetition.min_members" class="block w-full px-2.5 py-2 rounded-xl bg-white border border-slate-200 text-xs font-bold text-slate-900 outline-none focus:border-emerald-500">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Maks Anggota</label>
                                <input name="max_members" type="number" min="1" required x-model="selectedCompetition.max_members" class="block w-full px-2.5 py-2 rounded-xl bg-white border border-slate-200 text-xs font-bold text-slate-900 outline-none focus:border-emerald-500">
                            </div>
                            <div class="col-span-2 sm:col-span-3">
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Lokasi / Venue</label>
                                <input name="venue" type="text" x-model="selectedCompetition.venue" placeholder="Contoh: GOR MTsN 1 Blitar" class="block w-full px-3 py-2 rounded-xl bg-white border border-slate-200 text-xs font-medium text-slate-900 outline-none">
                            </div>
                            <div class="col-span-2 sm:col-span-2">
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Waktu / Jadwal</label>
                                <input name="schedule_time" type="text" x-model="selectedCompetition.schedule_time" placeholder="Contoh: 08.00 WIB" class="block w-full px-3 py-2 rounded-xl bg-white border border-slate-200 text-xs font-medium text-slate-900 outline-none">
                            </div>
                        </div>

                        <!-- Khusus Bulu Tangkis: Kartu Pengaturan Sesuai Kategori yang Diklik -->
                        <div x-show="selectedCompetition.code === 'BLT'" class="space-y-4">
                            
                            <!-- TUNGGAL PA - KATEGORI A -->
                            <div x-show="bltEditMode === 'tunggal_pa_a' || bltEditMode === 'all'" class="p-4 sm:p-5 rounded-2xl bg-emerald-50/70 border border-emerald-200/80 space-y-3.5">
                                <div class="flex items-center justify-between border-b border-emerald-200/60 pb-2">
                                    <span class="text-xs font-black text-emerald-950 flex items-center gap-2 tracking-wide">
                                        <i data-lucide="user" class="w-4 h-4 text-emerald-700"></i>
                                        <span>PENGATURAN TUNGGAL PUTRA (PA) — KAT A (KELAS 1–2 SD/MI)</span>
                                    </span>
                                    <span class="text-[10px] font-bold text-emerald-800 bg-emerald-100 px-2 py-0.5 rounded font-mono">Tunggal PA • Kat A</span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                                    <div class="bg-white p-3 rounded-xl border border-emerald-100 shadow-sm space-y-1.5">
                                        <label class="block text-[10px] font-extrabold text-slate-800">Biaya Kat A (Rp)</label>
                                        <input name="blt_fee_a_tunggal_pa" type="number" step="1000" min="0" x-model="selectedCompetition.blt_fee_a_tunggal_pa" class="block w-full px-2.5 py-1.5 rounded-lg bg-white border border-slate-200 text-xs font-mono font-bold text-emerald-800 outline-none">
                                    </div>
                                    <div class="bg-white p-3 rounded-xl border border-emerald-100 shadow-sm space-y-1.5">
                                        <label class="block text-[10px] font-extrabold text-slate-800">Kuota Kat A (Peserta)</label>
                                        <input name="blt_quota_a_tunggal_pa" type="number" min="0" x-model="selectedCompetition.blt_quota_a_tunggal_pa" class="block w-full px-2.5 py-1.5 rounded-lg bg-white border border-slate-200 text-xs font-bold text-slate-800 outline-none">
                                    </div>
                                    <div class="bg-white p-3 rounded-xl border border-emerald-100 shadow-sm space-y-1.5">
                                        <label class="block text-[10px] text-slate-500 font-bold">Petugas PIC Tunggal PA</label>
                                        <select name="blt_pic_tunggal_pa" x-model="selectedCompetition.blt_pic_tunggal_pa" class="block w-full px-2 py-1.5 rounded-lg bg-white border border-slate-200 text-xs font-semibold text-slate-900 outline-none">
                                            <option value="">-- Sama PIC Utama --</option>
                                            @foreach($pics as $p)
                                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="bg-white p-3 rounded-xl border border-emerald-100 shadow-sm space-y-1.5">
                                        <label class="block text-[10px] text-slate-500 font-bold">Status Pendaftaran</label>
                                        <select name="blt_status_a_tunggal_pa" x-model="selectedCompetition.blt_status_a_tunggal_pa" class="block w-full px-2 py-1.5 rounded-lg bg-white border border-slate-200 text-xs font-semibold text-slate-900 outline-none">
                                            <option value="buka">Buka</option>
                                            <option value="tutup">Tutup</option>
                                            <option value="selesai">Selesai</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- TUNGGAL PA - KATEGORI B -->
                            <div x-show="bltEditMode === 'tunggal_pa_b' || bltEditMode === 'all'" class="p-4 sm:p-5 rounded-2xl bg-emerald-50/70 border border-emerald-200/80 space-y-3.5">
                                <div class="flex items-center justify-between border-b border-emerald-200/60 pb-2">
                                    <span class="text-xs font-black text-emerald-950 flex items-center gap-2 tracking-wide">
                                        <i data-lucide="user" class="w-4 h-4 text-emerald-700"></i>
                                        <span>PENGATURAN TUNGGAL PUTRA (PA) — KAT B (KELAS 3–4 SD/MI)</span>
                                    </span>
                                    <span class="text-[10px] font-bold text-emerald-800 bg-emerald-100 px-2 py-0.5 rounded font-mono">Tunggal PA • Kat B</span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                                    <div class="bg-white p-3 rounded-xl border border-emerald-100 shadow-sm space-y-1.5">
                                        <label class="block text-[10px] font-extrabold text-slate-800">Biaya Kat B (Rp)</label>
                                        <input name="blt_fee_b_tunggal_pa" type="number" step="1000" min="0" x-model="selectedCompetition.blt_fee_b_tunggal_pa" class="block w-full px-2.5 py-1.5 rounded-lg bg-white border border-slate-200 text-xs font-mono font-bold text-emerald-800 outline-none">
                                    </div>
                                    <div class="bg-white p-3 rounded-xl border border-emerald-100 shadow-sm space-y-1.5">
                                        <label class="block text-[10px] font-extrabold text-slate-800">Kuota Kat B (Peserta)</label>
                                        <input name="blt_quota_b_tunggal_pa" type="number" min="0" x-model="selectedCompetition.blt_quota_b_tunggal_pa" class="block w-full px-2.5 py-1.5 rounded-lg bg-white border border-slate-200 text-xs font-bold text-slate-800 outline-none">
                                    </div>
                                    <div class="bg-white p-3 rounded-xl border border-emerald-100 shadow-sm space-y-1.5">
                                        <label class="block text-[10px] text-slate-500 font-bold">Petugas PIC Tunggal PA</label>
                                        <select name="blt_pic_tunggal_pa" x-model="selectedCompetition.blt_pic_tunggal_pa" class="block w-full px-2 py-1.5 rounded-lg bg-white border border-slate-200 text-xs font-semibold text-slate-900 outline-none">
                                            <option value="">-- Sama PIC Utama --</option>
                                            @foreach($pics as $p)
                                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="bg-white p-3 rounded-xl border border-emerald-100 shadow-sm space-y-1.5">
                                        <label class="block text-[10px] text-slate-500 font-bold">Status Pendaftaran</label>
                                        <select name="blt_status_b_tunggal_pa" x-model="selectedCompetition.blt_status_b_tunggal_pa" class="block w-full px-2 py-1.5 rounded-lg bg-white border border-slate-200 text-xs font-semibold text-slate-900 outline-none">
                                            <option value="buka">Buka</option>
                                            <option value="tutup">Tutup</option>
                                            <option value="selesai">Selesai</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- TUNGGAL PA - KATEGORI C -->
                            <div x-show="bltEditMode === 'tunggal_pa_c' || bltEditMode === 'all'" class="p-4 sm:p-5 rounded-2xl bg-emerald-50/70 border border-emerald-200/80 space-y-3.5">
                                <div class="flex items-center justify-between border-b border-emerald-200/60 pb-2">
                                    <span class="text-xs font-black text-emerald-950 flex items-center gap-2 tracking-wide">
                                        <i data-lucide="user" class="w-4 h-4 text-emerald-700"></i>
                                        <span>PENGATURAN TUNGGAL PUTRA (PA) — KAT C (KELAS 5–6 SD/MI)</span>
                                    </span>
                                    <span class="text-[10px] font-bold text-emerald-800 bg-emerald-100 px-2 py-0.5 rounded font-mono">Tunggal PA • Kat C</span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                                    <div class="bg-white p-3 rounded-xl border border-emerald-100 shadow-sm space-y-1.5">
                                        <label class="block text-[10px] font-extrabold text-slate-800">Biaya Kat C (Rp)</label>
                                        <input name="blt_fee_c_tunggal_pa" type="number" step="1000" min="0" x-model="selectedCompetition.blt_fee_c_tunggal_pa" class="block w-full px-2.5 py-1.5 rounded-lg bg-white border border-slate-200 text-xs font-mono font-bold text-emerald-800 outline-none">
                                    </div>
                                    <div class="bg-white p-3 rounded-xl border border-emerald-100 shadow-sm space-y-1.5">
                                        <label class="block text-[10px] font-extrabold text-slate-800">Kuota Kat C (Peserta)</label>
                                        <input name="blt_quota_c_tunggal_pa" type="number" min="0" x-model="selectedCompetition.blt_quota_c_tunggal_pa" class="block w-full px-2.5 py-1.5 rounded-lg bg-white border border-slate-200 text-xs font-bold text-slate-800 outline-none">
                                    </div>
                                    <div class="bg-white p-3 rounded-xl border border-emerald-100 shadow-sm space-y-1.5">
                                        <label class="block text-[10px] text-slate-500 font-bold">Petugas PIC Tunggal PA</label>
                                        <select name="blt_pic_tunggal_pa" x-model="selectedCompetition.blt_pic_tunggal_pa" class="block w-full px-2 py-1.5 rounded-lg bg-white border border-slate-200 text-xs font-semibold text-slate-900 outline-none">
                                            <option value="">-- Sama PIC Utama --</option>
                                            @foreach($pics as $p)
                                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="bg-white p-3 rounded-xl border border-emerald-100 shadow-sm space-y-1.5">
                                        <label class="block text-[10px] text-slate-500 font-bold">Status Pendaftaran</label>
                                        <select name="blt_status_c_tunggal_pa" x-model="selectedCompetition.blt_status_c_tunggal_pa" class="block w-full px-2 py-1.5 rounded-lg bg-white border border-slate-200 text-xs font-semibold text-slate-900 outline-none">
                                            <option value="buka">Buka</option>
                                            <option value="tutup">Tutup</option>
                                            <option value="selesai">Selesai</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- TUNGGAL PI - KATEGORI A -->
                            <div x-show="bltEditMode === 'tunggal_pi_a' || bltEditMode === 'all'" class="p-4 sm:p-5 rounded-2xl bg-pink-50/70 border border-pink-200/80 space-y-3.5">
                                <div class="flex items-center justify-between border-b border-pink-200/60 pb-2">
                                    <span class="text-xs font-black text-pink-950 flex items-center gap-2 tracking-wide">
                                        <i data-lucide="user" class="w-4 h-4 text-pink-700"></i>
                                        <span>PENGATURAN TUNGGAL PUTRI (PI) — KAT A (KELAS 1–2 SD/MI)</span>
                                    </span>
                                    <span class="text-[10px] font-bold text-pink-800 bg-pink-100 px-2 py-0.5 rounded font-mono">Tunggal PI • Kat A</span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                                    <div class="bg-white p-3 rounded-xl border border-pink-100 shadow-sm space-y-1.5">
                                        <label class="block text-[10px] font-extrabold text-slate-800">Biaya Kat A (Rp)</label>
                                        <input name="blt_fee_a_tunggal_pi" type="number" step="1000" min="0" x-model="selectedCompetition.blt_fee_a_tunggal_pi" class="block w-full px-2.5 py-1.5 rounded-lg bg-white border border-slate-200 text-xs font-mono font-bold text-pink-800 outline-none">
                                    </div>
                                    <div class="bg-white p-3 rounded-xl border border-pink-100 shadow-sm space-y-1.5">
                                        <label class="block text-[10px] font-extrabold text-slate-800">Kuota Kat A (Peserta)</label>
                                        <input name="blt_quota_a_tunggal_pi" type="number" min="0" x-model="selectedCompetition.blt_quota_a_tunggal_pi" class="block w-full px-2.5 py-1.5 rounded-lg bg-white border border-slate-200 text-xs font-bold text-slate-800 outline-none">
                                    </div>
                                    <div class="bg-white p-3 rounded-xl border border-pink-100 shadow-sm space-y-1.5">
                                        <label class="block text-[10px] text-slate-500 font-bold">Petugas PIC Tunggal PI</label>
                                        <select name="blt_pic_tunggal_pi" x-model="selectedCompetition.blt_pic_tunggal_pi" class="block w-full px-2 py-1.5 rounded-lg bg-white border border-slate-200 text-xs font-semibold text-slate-900 outline-none">
                                            <option value="">-- Sama PIC Utama --</option>
                                            @foreach($pics as $p)
                                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="bg-white p-3 rounded-xl border border-pink-100 shadow-sm space-y-1.5">
                                        <label class="block text-[10px] text-slate-500 font-bold">Status Pendaftaran</label>
                                        <select name="blt_status_a_tunggal_pi" x-model="selectedCompetition.blt_status_a_tunggal_pi" class="block w-full px-2 py-1.5 rounded-lg bg-white border border-slate-200 text-xs font-semibold text-slate-900 outline-none">
                                            <option value="buka">Buka</option>
                                            <option value="tutup">Tutup</option>
                                            <option value="selesai">Selesai</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- TUNGGAL PI - KATEGORI B -->
                            <div x-show="bltEditMode === 'tunggal_pi_b' || bltEditMode === 'all'" class="p-4 sm:p-5 rounded-2xl bg-pink-50/70 border border-pink-200/80 space-y-3.5">
                                <div class="flex items-center justify-between border-b border-pink-200/60 pb-2">
                                    <span class="text-xs font-black text-pink-950 flex items-center gap-2 tracking-wide">
                                        <i data-lucide="user" class="w-4 h-4 text-pink-700"></i>
                                        <span>PENGATURAN TUNGGAL PUTRI (PI) — KAT B (KELAS 3–4 SD/MI)</span>
                                    </span>
                                    <span class="text-[10px] font-bold text-pink-800 bg-pink-100 px-2 py-0.5 rounded font-mono">Tunggal PI • Kat B</span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                                    <div class="bg-white p-3 rounded-xl border border-pink-100 shadow-sm space-y-1.5">
                                        <label class="block text-[10px] font-extrabold text-slate-800">Biaya Kat B (Rp)</label>
                                        <input name="blt_fee_b_tunggal_pi" type="number" step="1000" min="0" x-model="selectedCompetition.blt_fee_b_tunggal_pi" class="block w-full px-2.5 py-1.5 rounded-lg bg-white border border-slate-200 text-xs font-mono font-bold text-pink-800 outline-none">
                                    </div>
                                    <div class="bg-white p-3 rounded-xl border border-pink-100 shadow-sm space-y-1.5">
                                        <label class="block text-[10px] font-extrabold text-slate-800">Kuota Kat B (Peserta)</label>
                                        <input name="blt_quota_b_tunggal_pi" type="number" min="0" x-model="selectedCompetition.blt_quota_b_tunggal_pi" class="block w-full px-2.5 py-1.5 rounded-lg bg-white border border-slate-200 text-xs font-bold text-slate-800 outline-none">
                                    </div>
                                    <div class="bg-white p-3 rounded-xl border border-pink-100 shadow-sm space-y-1.5">
                                        <label class="block text-[10px] text-slate-500 font-bold">Petugas PIC Tunggal PI</label>
                                        <select name="blt_pic_tunggal_pi" x-model="selectedCompetition.blt_pic_tunggal_pi" class="block w-full px-2 py-1.5 rounded-lg bg-white border border-slate-200 text-xs font-semibold text-slate-900 outline-none">
                                            <option value="">-- Sama PIC Utama --</option>
                                            @foreach($pics as $p)
                                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="bg-white p-3 rounded-xl border border-pink-100 shadow-sm space-y-1.5">
                                        <label class="block text-[10px] text-slate-500 font-bold">Status Pendaftaran</label>
                                        <select name="blt_status_b_tunggal_pi" x-model="selectedCompetition.blt_status_b_tunggal_pi" class="block w-full px-2 py-1.5 rounded-lg bg-white border border-slate-200 text-xs font-semibold text-slate-900 outline-none">
                                            <option value="buka">Buka</option>
                                            <option value="tutup">Tutup</option>
                                            <option value="selesai">Selesai</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- TUNGGAL PI - KATEGORI C -->
                            <div x-show="bltEditMode === 'tunggal_pi_c' || bltEditMode === 'all'" class="p-4 sm:p-5 rounded-2xl bg-pink-50/70 border border-pink-200/80 space-y-3.5">
                                <div class="flex items-center justify-between border-b border-pink-200/60 pb-2">
                                    <span class="text-xs font-black text-pink-950 flex items-center gap-2 tracking-wide">
                                        <i data-lucide="user" class="w-4 h-4 text-pink-700"></i>
                                        <span>PENGATURAN TUNGGAL PUTRI (PI) — KAT C (KELAS 5–6 SD/MI)</span>
                                    </span>
                                    <span class="text-[10px] font-bold text-pink-800 bg-pink-100 px-2 py-0.5 rounded font-mono">Tunggal PI • Kat C</span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                                    <div class="bg-white p-3 rounded-xl border border-pink-100 shadow-sm space-y-1.5">
                                        <label class="block text-[10px] font-extrabold text-slate-800">Biaya Kat C (Rp)</label>
                                        <input name="blt_fee_c_tunggal_pi" type="number" step="1000" min="0" x-model="selectedCompetition.blt_fee_c_tunggal_pi" class="block w-full px-2.5 py-1.5 rounded-lg bg-white border border-slate-200 text-xs font-mono font-bold text-pink-800 outline-none">
                                    </div>
                                    <div class="bg-white p-3 rounded-xl border border-pink-100 shadow-sm space-y-1.5">
                                        <label class="block text-[10px] font-extrabold text-slate-800">Kuota Kat C (Peserta)</label>
                                        <input name="blt_quota_c_tunggal_pi" type="number" min="0" x-model="selectedCompetition.blt_quota_c_tunggal_pi" class="block w-full px-2.5 py-1.5 rounded-lg bg-white border border-slate-200 text-xs font-bold text-slate-800 outline-none">
                                    </div>
                                    <div class="bg-white p-3 rounded-xl border border-pink-100 shadow-sm space-y-1.5">
                                        <label class="block text-[10px] text-slate-500 font-bold">Petugas PIC Tunggal PI</label>
                                        <select name="blt_pic_tunggal_pi" x-model="selectedCompetition.blt_pic_tunggal_pi" class="block w-full px-2 py-1.5 rounded-lg bg-white border border-slate-200 text-xs font-semibold text-slate-900 outline-none">
                                            <option value="">-- Sama PIC Utama --</option>
                                            @foreach($pics as $p)
                                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="bg-white p-3 rounded-xl border border-pink-100 shadow-sm space-y-1.5">
                                        <label class="block text-[10px] text-slate-500 font-bold">Status Pendaftaran</label>
                                        <select name="blt_status_c_tunggal_pi" x-model="selectedCompetition.blt_status_c_tunggal_pi" class="block w-full px-2 py-1.5 rounded-lg bg-white border border-slate-200 text-xs font-semibold text-slate-900 outline-none">
                                            <option value="buka">Buka</option>
                                            <option value="tutup">Tutup</option>
                                            <option value="selesai">Selesai</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- 3. KARTU NOMOR GANDA PUTRA (PA) -->
                            <div x-show="bltEditMode === 'ganda_pa' || bltEditMode === 'all'" class="p-4 sm:p-5 rounded-2xl bg-blue-50/70 border border-blue-200/80 space-y-3.5">
                                <div class="flex items-center justify-between border-b border-blue-200/60 pb-2">
                                    <span class="text-xs font-black text-blue-950 flex items-center gap-2 tracking-wide">
                                        <i data-lucide="users" class="w-4 h-4 text-blue-700"></i>
                                        <span>PENGATURAN GANDA PUTRA (PA)</span>
                                    </span>
                                    <span class="text-[10px] font-bold text-blue-800 bg-blue-100 px-2 py-0.5 rounded font-mono">Ganda Putra (PA)</span>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                                    <div class="bg-white p-3 rounded-xl border border-blue-100 shadow-sm space-y-1.5">
                                        <label class="block text-[10px] font-extrabold text-slate-800">Biaya Ganda Putra (Rp)</label>
                                        <input name="blt_fee_ganda_pa" type="number" step="1000" min="0" x-model="selectedCompetition.blt_fee_ganda_pa" class="block w-full px-2.5 py-1.5 rounded-lg bg-white border border-slate-200 text-xs font-mono font-bold text-blue-800 outline-none">
                                    </div>

                                    <div class="bg-white p-3 rounded-xl border border-blue-100 shadow-sm space-y-1.5">
                                        <label class="block text-[10px] font-extrabold text-slate-800">Kuota Ganda PA (Pasang)</label>
                                        <input name="blt_quota_ganda_pa" type="number" min="0" x-model="selectedCompetition.blt_quota_ganda_pa" class="block w-full px-2.5 py-1.5 rounded-lg bg-white border border-slate-200 text-xs font-bold text-slate-800 outline-none">
                                    </div>

                                    <div class="bg-white p-3 rounded-xl border border-blue-100 shadow-sm space-y-1.5">
                                        <label class="block text-[10px] text-slate-500 font-bold">Petugas PIC Ganda PA</label>
                                        <select name="blt_pic_ganda_pa" x-model="selectedCompetition.blt_pic_ganda_pa" class="block w-full px-2 py-1.5 rounded-lg bg-white border border-slate-200 text-xs font-semibold text-slate-900 outline-none">
                                            <option value="">-- Sama PIC Utama --</option>
                                            @foreach($pics as $p)
                                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="bg-white p-3 rounded-xl border border-blue-100 shadow-sm space-y-1.5">
                                        <label class="block text-[10px] text-slate-500 font-bold">Status Pendaftaran</label>
                                        <select name="blt_status_ganda_pa" x-model="selectedCompetition.blt_status_ganda_pa" class="block w-full px-2 py-1.5 rounded-lg bg-white border border-slate-200 text-xs font-semibold text-slate-900 outline-none">
                                            <option value="buka">Buka</option>
                                            <option value="tutup">Tutup</option>
                                            <option value="selesai">Selesai</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- 4. KARTU NOMOR GANDA PUTRI (PI) -->
                            <div x-show="bltEditMode === 'ganda_pi' || bltEditMode === 'all'" class="p-4 sm:p-5 rounded-2xl bg-amber-50/70 border border-amber-200/80 space-y-3.5">
                                <div class="flex items-center justify-between border-b border-amber-200/60 pb-2">
                                    <span class="text-xs font-black text-amber-950 flex items-center gap-2 tracking-wide">
                                        <i data-lucide="users" class="w-4 h-4 text-amber-700"></i>
                                        <span>PENGATURAN GANDA PUTRI (PI)</span>
                                    </span>
                                    <span class="text-[10px] font-bold text-amber-800 bg-amber-100 px-2 py-0.5 rounded font-mono">Ganda Putri (PI)</span>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                                    <div class="bg-white p-3 rounded-xl border border-amber-100 shadow-sm space-y-1.5">
                                        <label class="block text-[10px] font-extrabold text-slate-800">Biaya Ganda Putri (Rp)</label>
                                        <input name="blt_fee_ganda_pi" type="number" step="1000" min="0" x-model="selectedCompetition.blt_fee_ganda_pi" class="block w-full px-2.5 py-1.5 rounded-lg bg-white border border-slate-200 text-xs font-mono font-bold text-amber-800 outline-none">
                                    </div>

                                    <div class="bg-white p-3 rounded-xl border border-amber-100 shadow-sm space-y-1.5">
                                        <label class="block text-[10px] font-extrabold text-slate-800">Kuota Ganda PI (Pasang)</label>
                                        <input name="blt_quota_ganda_pi" type="number" min="0" x-model="selectedCompetition.blt_quota_ganda_pi" class="block w-full px-2.5 py-1.5 rounded-lg bg-white border border-slate-200 text-xs font-bold text-slate-800 outline-none">
                                    </div>

                                    <div class="bg-white p-3 rounded-xl border border-amber-100 shadow-sm space-y-1.5">
                                        <label class="block text-[10px] text-slate-500 font-bold">Petugas PIC Ganda PI</label>
                                        <select name="blt_pic_ganda_pi" x-model="selectedCompetition.blt_pic_ganda_pi" class="block w-full px-2 py-1.5 rounded-lg bg-white border border-slate-200 text-xs font-semibold text-slate-900 outline-none">
                                            <option value="">-- Sama PIC Utama --</option>
                                            @foreach($pics as $p)
                                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="bg-white p-3 rounded-xl border border-amber-100 shadow-sm space-y-1.5">
                                        <label class="block text-[10px] text-slate-500 font-bold">Status Pendaftaran</label>
                                        <select name="blt_status_ganda_pi" x-model="selectedCompetition.blt_status_ganda_pi" class="block w-full px-2 py-1.5 rounded-lg bg-white border border-slate-200 text-xs font-semibold text-slate-900 outline-none">
                                            <option value="buka">Buka</option>
                                            <option value="tutup">Tutup</option>
                                            <option value="selesai">Selesai</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <!-- Khusus Cabang MTQ & Pop Singer (MTQ / POP) -->
                        <div x-show="['MTQ', 'POP'].includes(selectedCompetition.code)" class="space-y-3.5">
                            <!-- 1. KARTU INDIVIDU PUTRA (PA) -->
                            <div x-show="bltEditMode === 'pa' || bltEditMode === 'all'" class="p-4 sm:p-5 rounded-2xl bg-emerald-50/70 border border-emerald-200/80 space-y-3.5">
                                <div class="flex items-center justify-between border-b border-emerald-200/60 pb-2">
                                    <span class="text-xs font-black text-emerald-950 flex items-center gap-2 tracking-wide">
                                        <i data-lucide="user" class="w-4 h-4 text-emerald-700"></i>
                                        <span x-text="'PENGATURAN ' + (selectedCompetition.name || '').toUpperCase() + ' — INDIVIDU PUTRA (PA)'"></span>
                                    </span>
                                    <span class="text-[10px] font-bold text-emerald-800 bg-emerald-100 px-2 py-0.5 rounded font-mono">Individu • PA</span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                                    <div class="bg-white p-3 rounded-xl border border-emerald-100 shadow-sm space-y-1.5">
                                        <label class="block text-[10px] font-extrabold text-slate-800">Biaya Putra (Rp)</label>
                                        <input :name="selectedCompetition.code ? selectedCompetition.code.toLowerCase() + '_fee_pa' : 'fee_pa'" type="number" step="1000" min="0" x-model="selectedCompetition.fee_pa" class="block w-full px-2.5 py-1.5 rounded-lg bg-white border border-slate-200 text-xs font-mono font-bold text-emerald-800 outline-none">
                                    </div>
                                    <div class="bg-white p-3 rounded-xl border border-emerald-100 shadow-sm space-y-1.5">
                                        <label class="block text-[10px] font-extrabold text-slate-800">Kuota Putra (Peserta)</label>
                                        <input :name="selectedCompetition.code ? selectedCompetition.code.toLowerCase() + '_quota_pa' : 'quota_pa'" type="number" min="0" x-model="selectedCompetition.quota_pa" class="block w-full px-2.5 py-1.5 rounded-lg bg-white border border-slate-200 text-xs font-bold text-slate-800 outline-none">
                                    </div>
                                    <div class="bg-white p-3 rounded-xl border border-emerald-100 shadow-sm space-y-1.5">
                                        <label class="block text-[10px] text-slate-500 font-bold">Petugas PIC Putra</label>
                                        <select :name="selectedCompetition.code ? selectedCompetition.code.toLowerCase() + '_pic_pa' : 'pic_pa'" x-model="selectedCompetition.pic_pa" class="block w-full px-2 py-1.5 rounded-lg bg-white border border-slate-200 text-xs font-semibold text-slate-900 outline-none">
                                            <option value="">-- Sama PIC Utama --</option>
                                            @foreach($pics as $p)
                                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="bg-white p-3 rounded-xl border border-emerald-100 shadow-sm space-y-1.5">
                                        <label class="block text-[10px] text-slate-500 font-bold">Status Pendaftaran</label>
                                        <select :name="selectedCompetition.code ? selectedCompetition.code.toLowerCase() + '_status_pa' : 'status_pa'" x-model="selectedCompetition.status_pa" class="block w-full px-2 py-1.5 rounded-lg bg-white border border-slate-200 text-xs font-semibold text-slate-900 outline-none">
                                            <option value="buka">Buka</option>
                                            <option value="tutup">Tutup</option>
                                            <option value="selesai">Selesai</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- 2. KARTU INDIVIDU PUTRI (PI) -->
                            <div x-show="bltEditMode === 'pi' || bltEditMode === 'all'" class="p-4 sm:p-5 rounded-2xl bg-pink-50/70 border border-pink-200/80 space-y-3.5">
                                <div class="flex items-center justify-between border-b border-pink-200/60 pb-2">
                                    <span class="text-xs font-black text-pink-950 flex items-center gap-2 tracking-wide">
                                        <i data-lucide="user" class="w-4 h-4 text-pink-700"></i>
                                        <span x-text="'PENGATURAN ' + (selectedCompetition.name || '').toUpperCase() + ' — INDIVIDU PUTRI (PI)'"></span>
                                    </span>
                                    <span class="text-[10px] font-bold text-pink-800 bg-pink-100 px-2 py-0.5 rounded font-mono">Individu • PI</span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                                    <div class="bg-white p-3 rounded-xl border border-pink-100 shadow-sm space-y-1.5">
                                        <label class="block text-[10px] font-extrabold text-slate-800">Biaya Putri (Rp)</label>
                                        <input :name="selectedCompetition.code ? selectedCompetition.code.toLowerCase() + '_fee_pi' : 'fee_pi'" type="number" step="1000" min="0" x-model="selectedCompetition.fee_pi" class="block w-full px-2.5 py-1.5 rounded-lg bg-white border border-slate-200 text-xs font-mono font-bold text-pink-800 outline-none">
                                    </div>
                                    <div class="bg-white p-3 rounded-xl border border-pink-100 shadow-sm space-y-1.5">
                                        <label class="block text-[10px] font-extrabold text-slate-800">Kuota Putri (Peserta)</label>
                                        <input :name="selectedCompetition.code ? selectedCompetition.code.toLowerCase() + '_quota_pi' : 'quota_pi'" type="number" min="0" x-model="selectedCompetition.quota_pi" class="block w-full px-2.5 py-1.5 rounded-lg bg-white border border-slate-200 text-xs font-bold text-slate-800 outline-none">
                                    </div>
                                    <div class="bg-white p-3 rounded-xl border border-pink-100 shadow-sm space-y-1.5">
                                        <label class="block text-[10px] text-slate-500 font-bold">Petugas PIC Putri</label>
                                        <select :name="selectedCompetition.code ? selectedCompetition.code.toLowerCase() + '_pic_pi' : 'pic_pi'" x-model="selectedCompetition.pic_pi" class="block w-full px-2 py-1.5 rounded-lg bg-white border border-slate-200 text-xs font-semibold text-slate-900 outline-none">
                                            <option value="">-- Sama PIC Utama --</option>
                                            @foreach($pics as $p)
                                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="bg-white p-3 rounded-xl border border-pink-100 shadow-sm space-y-1.5">
                                        <label class="block text-[10px] text-slate-500 font-bold">Status Pendaftaran</label>
                                        <select :name="selectedCompetition.code ? selectedCompetition.code.toLowerCase() + '_status_pi' : 'status_pi'" x-model="selectedCompetition.status_pi" class="block w-full px-2 py-1.5 rounded-lg bg-white border border-slate-200 text-xs font-semibold text-slate-900 outline-none">
                                            <option value="buka">Buka</option>
                                            <option value="tutup">Tutup</option>
                                            <option value="selesai">Selesai</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Khusus Cabang Tenis Meja (TMJ - Kat A & Kat B) -->
                        <div x-show="selectedCompetition.code === 'TMJ'" class="space-y-4">
                            
                            <!-- 1. TUNGGAL PA - KATEGORI A -->
                            <div x-show="bltEditMode === 'tmj_pa_a' || bltEditMode === 'all'" class="p-4 sm:p-5 rounded-2xl bg-emerald-50/70 border border-emerald-200/80 space-y-3.5">
                                <div class="flex items-center justify-between border-b border-emerald-200/60 pb-2">
                                    <span class="text-xs font-black text-emerald-950 flex items-center gap-2 tracking-wide">
                                        <i data-lucide="user" class="w-4 h-4 text-emerald-700"></i>
                                        <span>PENGATURAN TENIS MEJA — TUNGGAL PUTRA (PA) — KAT A (KELAS 1–3 SD/MI)</span>
                                    </span>
                                    <span class="text-[10px] font-bold text-emerald-800 bg-emerald-100 px-2 py-0.5 rounded font-mono">Tunggal PA • Kat A</span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                                    <div class="bg-white p-3 rounded-xl border border-emerald-100 shadow-sm space-y-1.5">
                                        <label class="block text-[10px] font-extrabold text-slate-800">Biaya Kat A (Rp)</label>
                                        <input name="tmj_fee_a_tunggal_pa" type="number" step="1000" min="0" x-model="selectedCompetition.tmj_fee_a_tunggal_pa" class="block w-full px-2.5 py-1.5 rounded-lg bg-white border border-slate-200 text-xs font-mono font-bold text-emerald-800 outline-none">
                                    </div>
                                    <div class="bg-white p-3 rounded-xl border border-emerald-100 shadow-sm space-y-1.5">
                                        <label class="block text-[10px] font-extrabold text-slate-800">Kuota Kat A (Peserta)</label>
                                        <input name="tmj_quota_a_tunggal_pa" type="number" min="0" x-model="selectedCompetition.tmj_quota_a_tunggal_pa" class="block w-full px-2.5 py-1.5 rounded-lg bg-white border border-slate-200 text-xs font-bold text-slate-800 outline-none">
                                    </div>
                                    <div class="bg-white p-3 rounded-xl border border-emerald-100 shadow-sm space-y-1.5">
                                        <label class="block text-[10px] text-slate-500 font-bold">Petugas PIC Tunggal PA</label>
                                        <select name="tmj_pic_tunggal_pa" x-model="selectedCompetition.tmj_pic_tunggal_pa" class="block w-full px-2 py-1.5 rounded-lg bg-white border border-slate-200 text-xs font-semibold text-slate-900 outline-none">
                                            <option value="">-- Sama PIC Utama --</option>
                                            @foreach($pics as $p)
                                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="bg-white p-3 rounded-xl border border-emerald-100 shadow-sm space-y-1.5">
                                        <label class="block text-[10px] text-slate-500 font-bold">Status Pendaftaran</label>
                                        <select name="tmj_status_a_tunggal_pa" x-model="selectedCompetition.tmj_status_a_tunggal_pa" class="block w-full px-2 py-1.5 rounded-lg bg-white border border-slate-200 text-xs font-semibold text-slate-900 outline-none">
                                            <option value="buka">Buka</option>
                                            <option value="tutup">Tutup</option>
                                            <option value="selesai">Selesai</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- 2. TUNGGAL PA - KATEGORI B -->
                            <div x-show="bltEditMode === 'tmj_pa_b' || bltEditMode === 'all'" class="p-4 sm:p-5 rounded-2xl bg-emerald-50/70 border border-emerald-200/80 space-y-3.5">
                                <div class="flex items-center justify-between border-b border-emerald-200/60 pb-2">
                                    <span class="text-xs font-black text-emerald-950 flex items-center gap-2 tracking-wide">
                                        <i data-lucide="user" class="w-4 h-4 text-emerald-700"></i>
                                        <span>PENGATURAN TENIS MEJA — TUNGGAL PUTRA (PA) — KAT B (KELAS 4–6 SD/MI)</span>
                                    </span>
                                    <span class="text-[10px] font-bold text-emerald-800 bg-emerald-100 px-2 py-0.5 rounded font-mono">Tunggal PA • Kat B</span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                                    <div class="bg-white p-3 rounded-xl border border-emerald-100 shadow-sm space-y-1.5">
                                        <label class="block text-[10px] font-extrabold text-slate-800">Biaya Kat B (Rp)</label>
                                        <input name="tmj_fee_b_tunggal_pa" type="number" step="1000" min="0" x-model="selectedCompetition.tmj_fee_b_tunggal_pa" class="block w-full px-2.5 py-1.5 rounded-lg bg-white border border-slate-200 text-xs font-mono font-bold text-emerald-800 outline-none">
                                    </div>
                                    <div class="bg-white p-3 rounded-xl border border-emerald-100 shadow-sm space-y-1.5">
                                        <label class="block text-[10px] font-extrabold text-slate-800">Kuota Kat B (Peserta)</label>
                                        <input name="tmj_quota_b_tunggal_pa" type="number" min="0" x-model="selectedCompetition.tmj_quota_b_tunggal_pa" class="block w-full px-2.5 py-1.5 rounded-lg bg-white border border-slate-200 text-xs font-bold text-slate-800 outline-none">
                                    </div>
                                    <div class="bg-white p-3 rounded-xl border border-emerald-100 shadow-sm space-y-1.5">
                                        <label class="block text-[10px] text-slate-500 font-bold">Petugas PIC Tunggal PA</label>
                                        <select name="tmj_pic_tunggal_pa" x-model="selectedCompetition.tmj_pic_tunggal_pa" class="block w-full px-2 py-1.5 rounded-lg bg-white border border-slate-200 text-xs font-semibold text-slate-900 outline-none">
                                            <option value="">-- Sama PIC Utama --</option>
                                            @foreach($pics as $p)
                                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="bg-white p-3 rounded-xl border border-emerald-100 shadow-sm space-y-1.5">
                                        <label class="block text-[10px] text-slate-500 font-bold">Status Pendaftaran</label>
                                        <select name="tmj_status_b_tunggal_pa" x-model="selectedCompetition.tmj_status_b_tunggal_pa" class="block w-full px-2 py-1.5 rounded-lg bg-white border border-slate-200 text-xs font-semibold text-slate-900 outline-none">
                                            <option value="buka">Buka</option>
                                            <option value="tutup">Tutup</option>
                                            <option value="selesai">Selesai</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- 3. TUNGGAL PI - KATEGORI A -->
                            <div x-show="bltEditMode === 'tmj_pi_a' || bltEditMode === 'all'" class="p-4 sm:p-5 rounded-2xl bg-pink-50/70 border border-pink-200/80 space-y-3.5">
                                <div class="flex items-center justify-between border-b border-pink-200/60 pb-2">
                                    <span class="text-xs font-black text-pink-950 flex items-center gap-2 tracking-wide">
                                        <i data-lucide="user" class="w-4 h-4 text-pink-700"></i>
                                        <span>PENGATURAN TENIS MEJA — TUNGGAL PUTRI (PI) — KAT A (KELAS 1–3 SD/MI)</span>
                                    </span>
                                    <span class="text-[10px] font-bold text-pink-800 bg-pink-100 px-2 py-0.5 rounded font-mono">Tunggal PI • Kat A</span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                                    <div class="bg-white p-3 rounded-xl border border-pink-100 shadow-sm space-y-1.5">
                                        <label class="block text-[10px] font-extrabold text-slate-800">Biaya Kat A (Rp)</label>
                                        <input name="tmj_fee_a_tunggal_pi" type="number" step="1000" min="0" x-model="selectedCompetition.tmj_fee_a_tunggal_pi" class="block w-full px-2.5 py-1.5 rounded-lg bg-white border border-slate-200 text-xs font-mono font-bold text-pink-800 outline-none">
                                    </div>
                                    <div class="bg-white p-3 rounded-xl border border-pink-100 shadow-sm space-y-1.5">
                                        <label class="block text-[10px] font-extrabold text-slate-800">Kuota Kat A (Peserta)</label>
                                        <input name="tmj_quota_a_tunggal_pi" type="number" min="0" x-model="selectedCompetition.tmj_quota_a_tunggal_pi" class="block w-full px-2.5 py-1.5 rounded-lg bg-white border border-slate-200 text-xs font-bold text-slate-800 outline-none">
                                    </div>
                                    <div class="bg-white p-3 rounded-xl border border-pink-100 shadow-sm space-y-1.5">
                                        <label class="block text-[10px] text-slate-500 font-bold">Petugas PIC Tunggal PI</label>
                                        <select name="tmj_pic_tunggal_pi" x-model="selectedCompetition.tmj_pic_tunggal_pi" class="block w-full px-2 py-1.5 rounded-lg bg-white border border-slate-200 text-xs font-semibold text-slate-900 outline-none">
                                            <option value="">-- Sama PIC Utama --</option>
                                            @foreach($pics as $p)
                                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="bg-white p-3 rounded-xl border border-pink-100 shadow-sm space-y-1.5">
                                        <label class="block text-[10px] text-slate-500 font-bold">Status Pendaftaran</label>
                                        <select name="tmj_status_a_tunggal_pi" x-model="selectedCompetition.tmj_status_a_tunggal_pi" class="block w-full px-2 py-1.5 rounded-lg bg-white border border-slate-200 text-xs font-semibold text-slate-900 outline-none">
                                            <option value="buka">Buka</option>
                                            <option value="tutup">Tutup</option>
                                            <option value="selesai">Selesai</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- 4. TUNGGAL PI - KATEGORI B -->
                            <div x-show="bltEditMode === 'tmj_pi_b' || bltEditMode === 'all'" class="p-4 sm:p-5 rounded-2xl bg-pink-50/70 border border-pink-200/80 space-y-3.5">
                                <div class="flex items-center justify-between border-b border-pink-200/60 pb-2">
                                    <span class="text-xs font-black text-pink-950 flex items-center gap-2 tracking-wide">
                                        <i data-lucide="user" class="w-4 h-4 text-pink-700"></i>
                                        <span>PENGATURAN TENIS MEJA — TUNGGAL PUTRI (PI) — KAT B (KELAS 4–6 SD/MI)</span>
                                    </span>
                                    <span class="text-[10px] font-bold text-pink-800 bg-pink-100 px-2 py-0.5 rounded font-mono">Tunggal PI • Kat B</span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                                    <div class="bg-white p-3 rounded-xl border border-pink-100 shadow-sm space-y-1.5">
                                        <label class="block text-[10px] font-extrabold text-slate-800">Biaya Kat B (Rp)</label>
                                        <input name="tmj_fee_b_tunggal_pi" type="number" step="1000" min="0" x-model="selectedCompetition.tmj_fee_b_tunggal_pi" class="block w-full px-2.5 py-1.5 rounded-lg bg-white border border-slate-200 text-xs font-mono font-bold text-pink-800 outline-none">
                                    </div>
                                    <div class="bg-white p-3 rounded-xl border border-pink-100 shadow-sm space-y-1.5">
                                        <label class="block text-[10px] font-extrabold text-slate-800">Kuota Kat B (Peserta)</label>
                                        <input name="tmj_quota_b_tunggal_pi" type="number" min="0" x-model="selectedCompetition.tmj_quota_b_tunggal_pi" class="block w-full px-2.5 py-1.5 rounded-lg bg-white border border-slate-200 text-xs font-bold text-slate-800 outline-none">
                                    </div>
                                    <div class="bg-white p-3 rounded-xl border border-pink-100 shadow-sm space-y-1.5">
                                        <label class="block text-[10px] text-slate-500 font-bold">Petugas PIC Tunggal PI</label>
                                        <select name="tmj_pic_tunggal_pi" x-model="selectedCompetition.tmj_pic_tunggal_pi" class="block w-full px-2 py-1.5 rounded-lg bg-white border border-slate-200 text-xs font-semibold text-slate-900 outline-none">
                                            <option value="">-- Sama PIC Utama --</option>
                                            @foreach($pics as $p)
                                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="bg-white p-3 rounded-xl border border-pink-100 shadow-sm space-y-1.5">
                                        <label class="block text-[10px] text-slate-500 font-bold">Status Pendaftaran</label>
                                        <select name="tmj_status_b_tunggal_pi" x-model="selectedCompetition.tmj_status_b_tunggal_pi" class="block w-full px-2 py-1.5 rounded-lg bg-white border border-slate-200 text-xs font-semibold text-slate-900 outline-none">
                                            <option value="buka">Buka</option>
                                            <option value="tutup">Tutup</option>
                                            <option value="selesai">Selesai</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pengaturan Umum untuk Lomba Lainnya (Non-BLT & Non-MTQ & Non-POP & Non-TMJ) -->
                        <div x-show="!['BLT', 'MTQ', 'POP', 'TMJ'].includes(selectedCompetition.code)" class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Biaya (Rp)</label>
                                <input name="registration_fee" type="number" min="0" step="1000" x-model="selectedCompetition.registration_fee" class="block w-full px-3 py-2.5 rounded-xl bg-white border border-slate-200 text-xs font-mono font-bold text-emerald-800 outline-none">
                            </div>
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600">Kuota Total</label>
                                    <span x-show="selectedCompetition.quota == 0" class="text-[9px] font-black text-purple-700 bg-purple-100 px-1.5 py-0.5 rounded border border-purple-200">∞ Unlimited</span>
                                </div>
                                <input name="quota" type="number" min="0" required x-model="selectedCompetition.quota" class="block w-full px-3 py-2.5 rounded-xl bg-white border border-slate-200 text-xs font-bold text-slate-900 outline-none">
                                <span class="text-[10px] text-slate-400">Isi 0 untuk Tak Terbatas (∞)</span>
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Koordinator PIC</label>
                                <select name="pic_id" x-model="selectedCompetition.pic_id" class="block w-full px-2 py-2.5 rounded-xl bg-white border border-slate-200 text-xs font-semibold text-slate-900 outline-none">
                                    <option value="">-- Pilih --</option>
                                    @foreach($pics as $p)
                                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Status</label>
                                <select name="status" required x-model="selectedCompetition.status" class="block w-full px-2 py-2.5 rounded-xl bg-white border border-slate-200 text-xs font-bold text-slate-900 outline-none">
                                    <option value="buka">Buka</option>
                                    <option value="tutup">Tutup</option>
                                    <option value="selesai">Selesai</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Aturan & Petunjuk Teknis Singkat</label>
                            <textarea name="rules" rows="3" x-model="selectedCompetition.rules" placeholder="Tuliskan petunjuk teknis pelaksanaan..." class="block w-full px-4 py-2.5 rounded-xl bg-white border border-slate-200 text-xs font-medium text-slate-900 outline-none"></textarea>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 flex items-center gap-1.5">
                                    <i data-lucide="file-text" class="w-4 h-4 text-brand-600"></i>
                                    <span>Embed Link Juknis PDF / Dokumen Resmi</span>
                                </label>
                                <span class="text-[10px] text-slate-400 font-semibold">Google Drive / URL PDF / Upload</span>
                            </div>
                            
                            <div class="space-y-2">
                                <div class="flex items-center gap-2">
                                    <div class="relative flex-1">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                            <i data-lucide="link" class="w-3.5 h-3.5"></i>
                                        </div>
                                        <input name="guidelines_file" type="text" x-model="selectedCompetition.guidelines_file" placeholder="Paste link Google Drive, URL PDF, atau kode embed (misal: https://drive.google.com/file/d/.../view)" class="block w-full pl-9 pr-3 py-2 rounded-xl bg-white border border-slate-200 text-xs font-medium text-slate-900 outline-none focus:border-brand-500 shadow-sm">
                                    </div>
                                    <label class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold border border-slate-200 cursor-pointer flex items-center gap-1.5 shrink-0 transition" title="Upload file PDF baru">
                                        <i data-lucide="upload" class="w-3.5 h-3.5 text-slate-500"></i>
                                        <span>Upload PDF</span>
                                        <input type="file" name="guidelines_pdf" accept=".pdf" class="hidden" @change="if($event.target.files.length > 0) { $refs.editPdfName.innerText = '📁 File terpilih: ' + $event.target.files[0].name; }">
                                    </label>
                                </div>
                                <div class="flex flex-wrap items-center justify-between gap-1 text-[11px] text-slate-500">
                                    <span class="flex items-center gap-1">
                                        <i data-lucide="info" class="w-3.5 h-3.5 text-blue-500 shrink-0"></i>
                                        <span>Bisa link Google Drive (akses publik), URL direct PDF, atau upload PDF langsung.</span>
                                    </span>
                                    <template x-if="selectedCompetition.guidelines_file">
                                        <a :href="selectedCompetition.guidelines_file.startsWith('http') ? selectedCompetition.guidelines_file : ('{{ asset('storage') }}/' + selectedCompetition.guidelines_file)" target="_blank" class="text-brand-600 hover:text-brand-700 font-bold flex items-center gap-1">
                                            <i data-lucide="external-link" class="w-3 h-3"></i>
                                            <span>Lihat Juknis Saat Ini</span>
                                        </a>
                                    </template>
                                </div>
                                <div x-ref="editPdfName" class="text-xs font-bold text-emerald-600"></div>
                            </div>
                        </div>

                        <!-- LINK GRUP WHATSAPP CABANG LOMBA -->
                        <div class="space-y-2 p-3.5 rounded-2xl bg-emerald-50/50 border border-emerald-200/80">
                            <label class="block text-xs font-black uppercase tracking-wider text-emerald-800 flex items-center gap-2">
                                <i data-lucide="message-circle" class="w-4 h-4 text-emerald-600"></i>
                                <span>Tautan Undangan Grup WhatsApp Cabang (Opsional)</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-emerald-500">
                                    <i data-lucide="link-2" class="w-3.5 h-3.5"></i>
                                </div>
                                <input name="whatsapp_group_url" type="url" x-model="selectedCompetition.whatsapp_group_url" placeholder="https://chat.whatsapp.com/Gzxxxxxxxxxx" class="block w-full pl-9 pr-3 py-2 rounded-xl bg-white border border-emerald-200 text-xs font-medium text-slate-900 outline-none focus:border-emerald-500 shadow-sm font-mono">
                            </div>
                            <p class="text-[11px] text-slate-500">Jika diisi, icon WhatsApp akan otomatis tampil di samping tombol Juknis di halaman depan.</p>
                        </div>

                        <!-- KRITERIA PENILAIAN DEWAN JURI -->
                        <div class="space-y-3 pt-3 border-t border-slate-100">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 bg-slate-50 p-3 rounded-2xl border border-slate-200/80">
                                <div>
                                    <label class="block text-xs font-black uppercase tracking-wider text-slate-800 flex items-center gap-1.5">
                                        <i data-lucide="scale" class="w-4 h-4 text-amber-500"></i>
                                        <span>Kriteria Penilaian Dewan Juri</span>
                                    </label>
                                    <p class="text-[11px] text-slate-500">Atur kriteria penilaian dan visibilitasnya pada pendaftar</p>
                                </div>
                                
                                <div class="flex items-center gap-2 shrink-0">
                                    <!-- Toggle Aktif / Nonaktif -->
                                    <input type="hidden" name="show_criteria" :value="selectedCompetition.show_criteria ? '1' : '0'">
                                    <button type="button" 
                                            @click="selectedCompetition.show_criteria = !selectedCompetition.show_criteria"
                                            :class="selectedCompetition.show_criteria ? 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-emerald-500/20' : 'bg-slate-200 hover:bg-slate-300 text-slate-700'"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition shadow-sm cursor-pointer"
                                            :title="selectedCompetition.show_criteria ? 'Klik untuk menonaktifkan kriteria di halaman pendaftar' : 'Klik untuk mengaktifkan kriteria di halaman pendaftar'">
                                        <span class="w-2 h-2 rounded-full" :class="selectedCompetition.show_criteria ? 'bg-emerald-200 animate-pulse' : 'bg-slate-400'"></span>
                                        <span x-text="selectedCompetition.show_criteria ? '✓ Aktif (Tampil)' : '✗ Nonaktif (Sembunyi)'"></span>
                                    </button>

                                    <!-- Button Tambah Kriteria -->
                                    <button type="button" @click="addCriterion(selectedCompetition)" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-white hover:bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-bold transition cursor-pointer shadow-xs">
                                        <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                        <span>+ Kriteria</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Notification banner when non-aktif -->
                            <div x-show="!selectedCompetition.show_criteria" class="p-3 rounded-2xl bg-amber-50 border border-amber-200/80 text-xs text-amber-900 flex items-start gap-2.5">
                                <i data-lucide="eye-off" class="w-4 h-4 text-amber-600 shrink-0 mt-0.5"></i>
                                <div>
                                    <span class="font-bold block text-[11px] uppercase tracking-wider text-amber-800">Status: Nonaktif di Halaman Pendaftar</span>
                                    <p class="text-[11px] text-amber-700 mt-0.5 leading-relaxed">
                                        Kriteria penilaian ini <strong>disembunyikan</strong> dan tidak akan muncul di halaman detail lomba maupun formulir pendaftar (cocok untuk cabang olahraga/turnamen sistem gugur).
                                    </p>
                                </div>
                            </div>

                            <!-- Criteria List -->
                            <div class="space-y-2.5 max-h-48 overflow-y-auto pr-1">
                                <template x-for="(crit, cIdx) in selectedCompetition.criteria" :key="cIdx">
                                    <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200 space-y-2">
                                        <div class="flex items-center gap-2">
                                            <div class="flex-1">
                                                <input :name="'criteria[' + cIdx + '][name]'" type="text" required x-model="crit.name" placeholder="Nama Kriteria" class="block w-full px-3 py-1.5 rounded-xl bg-white border border-slate-200 text-xs font-bold text-slate-800 outline-none focus:border-emerald-500">
                                            </div>
                                            <div class="w-24 shrink-0 flex items-center gap-1">
                                                <input :name="'criteria[' + cIdx + '][weight_percentage]'" type="number" min="1" max="100" required x-model.number="crit.weight_percentage" placeholder="Bobot" class="block w-full px-2 py-1.5 rounded-xl bg-white border border-slate-200 text-xs font-mono font-black text-center text-emerald-700 outline-none focus:border-emerald-500">
                                                <span class="text-xs font-bold text-slate-500">%</span>
                                            </div>
                                            <button type="button" @click="removeCriterion(selectedCompetition, cIdx)" class="p-1.5 rounded-lg hover:bg-rose-100 text-rose-500 transition cursor-pointer" title="Hapus Kriteria">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </div>
                                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                            <div class="sm:col-span-2">
                                                <input :name="'criteria[' + cIdx + '][description]'" type="text" x-model="crit.description" placeholder="Penjelasan kriteria (opsional)" class="block w-full px-2.5 py-1 rounded-lg bg-white border border-slate-200 text-[11px] text-slate-600 outline-none">
                                            </div>
                                            <div class="flex items-center gap-1 text-[10px] text-slate-400 font-bold justify-end">
                                                <span>Skor:</span>
                                                <input :name="'criteria[' + cIdx + '][min_score]'" type="number" x-model="crit.min_score" class="w-12 px-1.5 py-0.5 rounded bg-white border border-slate-200 text-center font-mono text-[11px] text-slate-700">
                                                <span>-</span>
                                                <input :name="'criteria[' + cIdx + '][max_score]'" type="number" x-model="crit.max_score" class="w-12 px-1.5 py-0.5 rounded bg-white border border-slate-200 text-center font-mono text-[11px] text-slate-700">
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <div x-show="!selectedCompetition.criteria || selectedCompetition.criteria.length === 0" class="p-3 text-center rounded-xl border border-dashed border-slate-200 text-slate-400 text-xs">
                                    Belum ada kriteria khusus. Klik tombol <strong>+ Tambah Kriteria</strong> di atas.
                                </div>
                            </div>

                            <!-- Total Weight Badge -->
                            <div x-show="selectedCompetition.criteria && selectedCompetition.criteria.length > 0" class="flex items-center justify-between text-xs px-2 pt-1 font-bold">
                                <span class="text-slate-500">Total Akumulasi Bobot:</span>
                                <span :class="calculateTotalWeight(selectedCompetition.criteria) === 100 ? 'text-emerald-700 bg-emerald-100 px-2.5 py-0.5 rounded-full font-black' : 'text-amber-700 bg-amber-100 px-2.5 py-0.5 rounded-full font-black'" x-text="calculateTotalWeight(selectedCompetition.criteria) + '% ' + (calculateTotalWeight(selectedCompetition.criteria) === 100 ? '(Pas 100%)' : '(Disarankan total 100%)')"></span>
                            </div>
                        </div>

                    </div>

                    <!-- Modal Footer -->
                    <div class="pt-5 flex flex-col sm:flex-row items-center justify-between gap-4 border-t border-slate-100">
                        <button type="button" @click="if(confirm('Apakah Anda yakin ingin menghapus cabang lomba ' + selectedCompetition.name + ' beserta seluruh data pendaftarannya?')) { $refs.deleteModalForm.submit(); }" class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 text-xs font-bold transition flex items-center justify-center gap-1.5 cursor-pointer">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                            <span>Hapus Cabang Lomba</span>
                        </button>

                        <div class="w-full sm:w-auto flex items-center justify-end gap-3">
                            <button type="button" @click="closeEditCompetitionModal()" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition cursor-pointer">
                                Batal
                            </button>
                            <button type="submit" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-black text-xs shadow-lg shadow-emerald-500/20 transition cursor-pointer flex items-center gap-1.5">
                                <i data-lucide="check" class="w-4 h-4"></i>
                                <span>Simpan Perubahan</span>
                            </button>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Hidden Delete Form triggered by modal -->
            <form x-ref="deleteModalForm" :action="'{{ url('/admin/competitions') }}/' + selectedCompetition.id + '/delete'" method="POST" class="hidden">
                @csrf
            </form>

        </div>
    </div>
    @endif

    <!-- Create Category Modal -->
    <div x-show="createCategoryModal" x-cloak class="fixed inset-0 z-[100] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
        <div class="flex items-center justify-center min-h-screen p-4 text-center sm:p-0">
            <!-- Backdrop -->
            <div x-show="createCategoryModal" @click="createCategoryModal = false" class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm transition-opacity"></div>

            <!-- Modal Content (Crisp, High Z-Index) -->
            <div x-show="createCategoryModal" class="relative z-10 inline-block bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all my-8 sm:align-middle sm:max-w-lg w-full p-6 sm:p-8 space-y-6">
                
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div>
                        <h3 class="text-lg font-black text-slate-900">Tambah Kategori Baru</h3>
                        <p class="text-xs text-slate-500">Buat kategori baru untuk mengelompokkan cabang perlombaan</p>
                    </div>
                    <button @click="createCategoryModal = false" class="text-slate-400 hover:text-slate-600">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form action="{{ route('admin.categories.store') }}" method="POST" class="space-y-4">
                    @csrf

                    <div class="grid grid-cols-3 gap-3">
                        <div class="col-span-2">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Jenis Lomba</label>
                            <input name="name" type="text" required placeholder="Contoh: Robotik / MTQ / Olahraga" class="block w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Kategori Utama</label>
                            <select name="category_group" required class="block w-full px-3 py-2.5 rounded-xl border border-slate-200 text-xs font-bold outline-none">
                                <option value="non_akademik">Non Akademik</option>
                                <option value="akademik">Akademik</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Ikon (Lucide Icon)</label>
                            <input name="icon" type="text" placeholder="book-open / trophy / cpu" value="folder" class="block w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-mono outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Nomor Urutan</label>
                            <input name="order" type="number" value="{{ $categories->count() + 1 }}" min="1" required class="block w-full px-4 py-2.5 rounded-xl bg-white border border-slate-200 text-xs font-bold text-slate-900 outline-none focus:border-blue-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Deskripsi Singkat (Opsional)</label>
                        <textarea name="description" rows="2" placeholder="Deskripsi ringkas lingkup bidang lomba kategori ini..." class="block w-full px-4 py-2.5 rounded-xl bg-white border border-slate-200 text-xs font-medium text-slate-900 outline-none focus:border-blue-500"></textarea>
                    </div>

                    <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                        <button type="button" @click="createCategoryModal = false" class="px-5 py-2.5 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold">
                            Batal
                        </button>
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md shadow-blue-500/20">
                            Simpan Jenis Lomba
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <!-- Edit Category Modal -->
    <div x-show="editCategoryModal" x-cloak class="fixed inset-0 z-[100] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
        <div class="flex items-center justify-center min-h-screen p-4 text-center sm:p-0">
            <!-- Backdrop -->
            <div x-show="editCategoryModal" @click="editCategoryModal = false" class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm transition-opacity"></div>

            <!-- Modal Content (Crisp, High Z-Index) -->
            <div x-show="editCategoryModal" class="relative z-10 inline-block bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all my-8 sm:align-middle sm:max-w-lg w-full p-6 sm:p-8 space-y-6">
                
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div>
                        <h3 class="text-lg font-black text-slate-900">Edit Jenis Lomba / Kategori</h3>
                        <p class="text-xs text-slate-500">Perbarui nama jenis lomba, kategori utama, urutan, atau ikon</p>
                    </div>
                    <button @click="editCategoryModal = false" class="text-slate-400 hover:text-slate-600">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form :action="'{{ url('admin/categories') }}/' + (selectedCategory ? selectedCategory.id : '') + '/update'" method="POST" class="space-y-4">
                    @csrf

                    <div class="grid grid-cols-3 gap-3">
                        <div class="col-span-2">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Jenis Lomba</label>
                            <input name="name" type="text" required x-model="selectedCategory.name" class="block w-full px-4 py-2.5 rounded-xl bg-white border border-slate-200 text-sm font-semibold text-slate-900 outline-none focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Kategori Utama</label>
                            <select name="category_group" required x-model="selectedCategory.category_group" class="block w-full px-3 py-2.5 rounded-xl bg-white border border-slate-200 text-xs font-bold text-slate-900 outline-none focus:border-blue-500">
                                <option value="non_akademik">Non Akademik</option>
                                <option value="akademik">Akademik</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Ikon (Lucide Icon)</label>
                            <input name="icon" type="text" x-model="selectedCategory.icon" class="block w-full px-4 py-2.5 rounded-xl bg-white border border-slate-200 text-xs font-mono font-bold text-slate-900 outline-none focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Nomor Urutan</label>
                            <input name="order" type="number" min="1" required x-model="selectedCategory.order" class="block w-full px-4 py-2.5 rounded-xl bg-white border border-slate-200 text-xs font-bold text-slate-900 outline-none focus:border-blue-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Deskripsi Singkat</label>
                        <textarea name="description" rows="2" x-model="selectedCategory.description" class="block w-full px-4 py-2.5 rounded-xl bg-white border border-slate-200 text-xs font-medium text-slate-900 outline-none focus:border-blue-500"></textarea>
                    </div>

                    <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                        <button type="button" @click="editCategoryModal = false" class="px-5 py-2.5 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold">
                            Batal
                        </button>
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md shadow-blue-500/20">
                            Perbarui Jenis Lomba
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

</div>
@endsection
