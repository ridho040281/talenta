@extends('layouts.admin')

@section('title', 'Data Peserta Cabang Lomba')
@section('page_title', 'Data Peserta')

@section('content')
@php
    $popComp = $competitions->first(fn($c) => $c->code === 'POP' || str_contains(strtolower($c->name), 'pop'));
    if (!$popComp) {
        $popComp = \App\Models\Competition::where('code', 'POP')->first();
    }
    $popSongOptions = $popComp ? $popComp->song_options : [];
    if (empty($popSongOptions)) {
        $rawPop = \App\Models\AppSetting::get('pop_song_options') ?: "Deen Assalam\nRahmatun Lil'Alameen\nYa Maulana\nMan Ana\nAisyah Istri Rasulullah\nBidadari Surga\nSholawat Cinta\nKisah Sang Rasul";
        $popSongOptions = array_values(array_unique(array_filter(array_map('trim', explode("\n", str_replace("\r", "", $rawPop))))));
    }
@endphp

<div class="space-y-6" x-data="{ 
    searchQuery: '',
    selectedCompetition: 'all',
    selectedGender: 'all',
    selectedSector: 'all',
    selectedStatus: 'all',
    verifyModal: false,
    editModal: false,
    exportModal: false,
    singlePrintModal: false,
    createModal: false,
    createCompId: '',
    createPaymentMethod: 'tunai',
    popSongOptions: @js($popSongOptions),
    get isPopSinger() {
        if (!this.selectedEditReg) return false;
        if (this.selectedEditReg.chosen_song) return true;
        if (this.editCompCode === 'POP') return true;
        if (this.selectedEditReg.competition) {
            const code = String(this.selectedEditReg.competition.code || '').toUpperCase();
            const name = String(this.selectedEditReg.competition.name || '').toLowerCase();
            if (code === 'POP' || name.includes('pop')) return true;
        }
        if (this.selectedEditReg.competition_id) {
            const c = this.competitionsData.find(x => String(x.id) === String(this.selectedEditReg.competition_id));
            if (c && (String(c.code).toUpperCase() === 'POP' || String(c.name || '').toLowerCase().includes('pop'))) return true;
        }
        if (this.selectedEditReg.registration_code && String(this.selectedEditReg.registration_code).toUpperCase().includes('POP')) {
            return true;
        }
        return false;
    },
    get createCompCode() {
        const c = this.competitionsData.find(x => x.id === this.createCompId);
        return c ? c.code : '';
    },
    get editCompCode() {
        if (!this.selectedEditReg) return '';
        if (this.selectedEditReg.competition && this.selectedEditReg.competition.code) {
            return String(this.selectedEditReg.competition.code).toUpperCase();
        }
        if (this.selectedEditReg.competition_id) {
            const c = this.competitionsData.find(x => String(x.id) === String(this.selectedEditReg.competition_id));
            if (c && c.code) return String(c.code).toUpperCase();
        }
        if (this.selectedEditReg.registration_code) {
            const parts = String(this.selectedEditReg.registration_code).split('-');
            if (parts.length >= 3 && parts[2]) {
                return parts[2].toUpperCase();
            }
            return String(parts[0]).toUpperCase();
        }
        return '';
    },
    createMatchType: '',
    createTargetClass: '',
    selectedReg: null,
    selectedEditReg: {
        id: null,
        registration_code: '',
        institution_name: '',
        official_name: '',
        official_phone: '',
        team_name: '',
        target_class: '',
        match_type: '',
        participant_number: '',
        draw_number: '',
        chosen_song: '',
        members: []
    },
    selectedSingleReg: null,
    selectedPrintCompetition: 'all',
    selectedPrintStatus: 'all',
    selectedPrintGender: 'all',
    selectedPrintCategory: 'all',
    get selectedPrintCompCode() {
        if (!this.selectedPrintCompetition || this.selectedPrintCompetition === 'all') return '';
        const c = this.competitionsData.find(x => String(x.id) === String(this.selectedPrintCompetition));
        return c ? String(c.code || '').toUpperCase() : '';
    },
    competitionsData: @js($competitions->map(fn($c) => ['id' => (string)$c->id, 'code' => $c->code, 'name' => $c->name, 'fee' => (float)$c->registration_fee, 'song_options' => $c->song_options ?? []])),
    appFeeSettings: {
        blt_fee_ganda_pa: {{ (float) \App\Models\AppSetting::get('blt_fee_ganda_pa', 200000) }},
        blt_fee_ganda_pi: {{ (float) \App\Models\AppSetting::get('blt_fee_ganda_pi', 200000) }},
        blt_fee_a_tunggal_pa: {{ (float) \App\Models\AppSetting::get('blt_fee_a_tunggal_pa', 130000) }},
        blt_fee_a_tunggal_pi: {{ (float) \App\Models\AppSetting::get('blt_fee_a_tunggal_pi', 130000) }},
        blt_fee_b_tunggal_pa: {{ (float) \App\Models\AppSetting::get('blt_fee_b_tunggal_pa', 150000) }},
        blt_fee_b_tunggal_pi: {{ (float) \App\Models\AppSetting::get('blt_fee_b_tunggal_pi', 150000) }},
        blt_fee_c_tunggal_pa: {{ (float) \App\Models\AppSetting::get('blt_fee_c_tunggal_pa', 150000) }},
        blt_fee_c_tunggal_pi: {{ (float) \App\Models\AppSetting::get('blt_fee_c_tunggal_pi', 150000) }},
        tmj_fee_a_tunggal_pa: {{ (float) \App\Models\AppSetting::get('tmj_fee_a_tunggal_pa', 35000) }},
        tmj_fee_a_tunggal_pi: {{ (float) \App\Models\AppSetting::get('tmj_fee_a_tunggal_pi', 35000) }},
        tmj_fee_b_tunggal_pa: {{ (float) \App\Models\AppSetting::get('tmj_fee_b_tunggal_pa', 35000) }},
        tmj_fee_b_tunggal_pi: {{ (float) \App\Models\AppSetting::get('tmj_fee_b_tunggal_pi', 35000) }},
        mtq_fee_pa: {{ (float) \App\Models\AppSetting::get('mtq_fee_pa', 35000) }},
        mtq_fee_pi: {{ (float) \App\Models\AppSetting::get('mtq_fee_pi', 35000) }},
        pop_fee_pa: {{ (float) \App\Models\AppSetting::get('pop_fee_pa', 35000) }},
        pop_fee_pi: {{ (float) \App\Models\AppSetting::get('pop_fee_pi', 35000) }},
    },
    get currentManualFee() {
        if (!this.createCompId) return 0;
        const comp = this.competitionsData.find(c => c.id === this.createCompId);
        if (!comp) return 0;
        const isPi = this.createGender === 'P' || (this.createMatchType && this.createMatchType.includes('(PI)'));
        if (comp.code === 'BLT') {
            if (this.createMatchType && this.createMatchType.includes('Ganda')) {
                return isPi ? this.appFeeSettings.blt_fee_ganda_pi : this.appFeeSettings.blt_fee_ganda_pa;
            }
            if (this.createTargetClass && this.createTargetClass.includes('Kategori A')) {
                return isPi ? this.appFeeSettings.blt_fee_a_tunggal_pi : this.appFeeSettings.blt_fee_a_tunggal_pa;
            }
            if (this.createTargetClass && this.createTargetClass.includes('Kategori B')) {
                return isPi ? this.appFeeSettings.blt_fee_b_tunggal_pi : this.appFeeSettings.blt_fee_b_tunggal_pa;
            }
            if (this.createTargetClass && this.createTargetClass.includes('Kategori C')) {
                return isPi ? this.appFeeSettings.blt_fee_c_tunggal_pi : this.appFeeSettings.blt_fee_c_tunggal_pa;
            }
            return isPi ? this.appFeeSettings.blt_fee_a_tunggal_pi : this.appFeeSettings.blt_fee_a_tunggal_pa;
        }
        if (comp.code === 'TMJ') {
            if (this.createTargetClass && (this.createTargetClass.includes('Kategori B') || this.createTargetClass.includes('4 - 6'))) {
                return isPi ? this.appFeeSettings.tmj_fee_b_tunggal_pi : this.appFeeSettings.tmj_fee_b_tunggal_pa;
            }
            return isPi ? this.appFeeSettings.tmj_fee_a_tunggal_pi : this.appFeeSettings.tmj_fee_a_tunggal_pa;
        }
        if (comp.code === 'MTQ') {
            return isPi ? this.appFeeSettings.mtq_fee_pi : this.appFeeSettings.mtq_fee_pa;
        }
        if (comp.code === 'POP') {
            return isPi ? this.appFeeSettings.pop_fee_pi : this.appFeeSettings.pop_fee_pa;
        }
        return comp.fee || 35000;
    },
    get currentCompCode() {
        if (this.selectedCompetition === 'all') return 'ALL';
        const comp = this.competitionsData.find(c => c.id === this.selectedCompetition);
        return comp ? comp.code : '';
    },
    get currentCompName() {
        if (this.selectedCompetition === 'all') return 'Semua Cabang';
        const comp = this.competitionsData.find(c => c.id === this.selectedCompetition);
        return comp ? comp.name : 'Cabang';
    },
    get sectorOptions() {
        if (this.currentCompCode === 'BLT') {
            return [
                { value: 'all', label: '🏸 Semua Sektor Bulu Tangkis' },
                { value: 'tunggal_pa', label: '👦 Semua Tunggal Putra (PA)' },
                { value: 'tunggal_pa_a', label: '🏷️ Tunggal PA - Kat A (Kelas 1–2 SD/MI)' },
                { value: 'tunggal_pa_b', label: '🏷️ Tunggal PA - Kat B (Kelas 3–4 SD/MI)' },
                { value: 'tunggal_pa_c', label: '🏷️ Tunggal PA - Kat C (Kelas 5–6 SD/MI)' },
                { value: 'tunggal_pi', label: '👧 Semua Tunggal Putri (PI)' },
                { value: 'tunggal_pi_a', label: '🏷️ Tunggal PI - Kat A (Kelas 1–2 SD/MI)' },
                { value: 'tunggal_pi_b', label: '🏷️ Tunggal PI - Kat B (Kelas 3–4 SD/MI)' },
                { value: 'tunggal_pi_c', label: '🏷️ Tunggal PI - Kat C (Kelas 5–6 SD/MI)' },
                { value: 'ganda_all', label: '👥 Semua Ganda (PA & PI)' },
                { value: 'ganda_pa', label: '👥 Ganda Putra (PA) - Semua Kelas' },
                { value: 'ganda_pi', label: '👥 Ganda Putri (PI) - Semua Kelas' },
            ];
        } else if (this.currentCompCode === 'TMJ') {
            return [
                { value: 'all', label: '🏓 Semua Kategori Tenis Meja' },
                { value: 'tmj_a_all', label: '🏷️ Semua Kategori A (Kelas 1–3 SD/MI)' },
                { value: 'tmj_pa_a', label: '👦 Putra (PA) - Kat A (Kelas 1–3 SD/MI)' },
                { value: 'tmj_pi_a', label: '👧 Putri (PI) - Kat A (Kelas 1–3 SD/MI)' },
                { value: 'tmj_b_all', label: '🏷️ Semua Kategori B (Kelas 4–6 SD/MI)' },
                { value: 'tmj_pa_b', label: '👦 Putra (PA) - Kat B (Kelas 4–6 SD/MI)' },
                { value: 'tmj_pi_b', label: '👧 Putri (PI) - Kat B (Kelas 4–6 SD/MI)' },
            ];
        } else if (this.currentCompCode === 'MTQ') {
            return [
                { value: 'all', label: '📖 Semua Kategori MTQ' },
                { value: 'individu_pa', label: '👦 MTQ Putra (PA)' },
                { value: 'individu_pi', label: '👧 MTQ Putri (PI)' },
            ];
        } else if (this.currentCompCode === 'POP') {
            return [
                { value: 'all', label: '🎤 Semua Kategori Pop Singer' },
                { value: 'individu_pa', label: '👦 Pop Singer Putra (PA)' },
                { value: 'individu_pi', label: '👧 Pop Singer Putri (PI)' },
            ];
        } else if (this.currentCompCode === 'ALL') {
            const list = [{ value: 'all', label: 'Semua Sektor / Kategori Lomba' }];
            const codes = this.competitionsData.map(c => c.code);

            if (codes.includes('BLT')) {
                list.push(
                    { value: 'tunggal_pa', label: '🏸 Bulu Tangkis: Semua Tunggal Putra (PA)' },
                    { value: 'tunggal_pa_a', label: '🏷️ Bulu Tangkis: Tunggal PA - Kat A (Kelas 1–2)' },
                    { value: 'tunggal_pa_b', label: '🏷️ Bulu Tangkis: Tunggal PA - Kat B (Kelas 3–4)' },
                    { value: 'tunggal_pa_c', label: '🏷️ Bulu Tangkis: Tunggal PA - Kat C (Kelas 5–6)' },
                    { value: 'tunggal_pi', label: '🏸 Bulu Tangkis: Semua Tunggal Putri (PI)' },
                    { value: 'tunggal_pi_a', label: '🏷️ Bulu Tangkis: Tunggal PI - Kat A (Kelas 1–2)' },
                    { value: 'tunggal_pi_b', label: '🏷️ Bulu Tangkis: Tunggal PI - Kat B (Kelas 3–4)' },
                    { value: 'tunggal_pi_c', label: '🏷️ Bulu Tangkis: Tunggal PI - Kat C (Kelas 5–6)' },
                    { value: 'ganda_all', label: '👥 Bulu Tangkis: Semua Ganda (PA & PI)' },
                    { value: 'ganda_pa', label: '👥 Bulu Tangkis: Ganda Putra (PA)' },
                    { value: 'ganda_pi', label: '👥 Bulu Tangkis: Ganda Putri (PI)' }
                );
            }
            if (codes.includes('TMJ')) {
                list.push(
                    { value: 'tmj_a_all', label: '🏓 Tenis Meja: Semua Kategori A (Kelas 1–3)' },
                    { value: 'tmj_pa_a', label: '🏷️ Tenis Meja: Tunggal PA - Kat A (Kelas 1–3)' },
                    { value: 'tmj_pi_a', label: '🏷️ Tenis Meja: Tunggal PI - Kat A (Kelas 1–3)' },
                    { value: 'tmj_b_all', label: '🏓 Tenis Meja: Semua Kategori B (Kelas 4–6)' },
                    { value: 'tmj_pa_b', label: '🏷️ Tenis Meja: Tunggal PA - Kat B (Kelas 4–6)' },
                    { value: 'tmj_pi_b', label: '🏷️ Tenis Meja: Tunggal PI - Kat B (Kelas 4–6)' }
                );
            }
            if (codes.includes('MTQ')) {
                list.push(
                    { value: 'individu_pa', label: '📖 MTQ: Sektor Putra (PA)' },
                    { value: 'individu_pi', label: '📖 MTQ: Sektor Putri (PI)' }
                );
            }
            if (codes.includes('POP')) {
                list.push(
                    { value: 'individu_pa', label: '🎤 Pop Singer: Sektor Putra (PA)' },
                    { value: 'individu_pi', label: '🎤 Pop Singer: Sektor Putri (PI)' }
                );
            }
            return list;
        } else {
            // Cabang Terbuka / Tanpa Pembagian Sektor PA-PI di Master (Olimpiade MIPA, Catur, Kaligrafi, Pidato, dll)
            return [
                { value: 'all', label: 'Semua Peserta ' + this.currentCompName + ' (Umum / Satu Kategori)' },
            ];
        }
    },
    formatTTL(place, date) {
        if (!place && !date) return '-';
        let formattedDate = '';
        if (date) {
            const str = String(date).split('T')[0];
            const parts = str.split('-');
            if (parts.length === 3) {
                const months = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                const day = parseInt(parts[2], 10);
                const mIdx = parseInt(parts[1], 10);
                const year = parts[0];
                if (!isNaN(day) && mIdx >= 1 && mIdx <= 12) {
                    formattedDate = `${day} ${months[mIdx]} ${year}`;
                } else {
                    formattedDate = str;
                }
            } else {
                formattedDate = date;
            }
        }
        if (place && formattedDate) {
            return `${place}, ${formattedDate}`;
        }
        return place || formattedDate || '-';
    },
    items: @js($allRegistrations->map(function($r) {
        $firstMember = $r->members->first();

        return [
            'id' => $r->id,
            'comp_id' => (string) $r->competition_id,
            'gender' => $r->primary_gender,
            'status' => $r->status,
            'is_ganda' => $r->isGanda(),
            'is_kat_a' => $r->isKatA(),
            'is_kat_b' => $r->isKatB(),
            'is_kat_c' => $r->isKatC(),
            'search' => strtolower($r->display_name . ' ' . $r->registration_code . ' ' . ($r->participant_number ?? '') . ' ' . $r->display_school . ' ' . $r->institution_name . ' ' . ($firstMember?->nisn ?? '') . ' ' . $r->members->pluck('school_name')->filter()->implode(' '))
        ];
    })),
    currentPage: 1,
    perPage: 10,
    init() {
        this.$watch('searchQuery', () => { this.currentPage = 1; });
        this.$watch('selectedCompetition', () => { this.currentPage = 1; });
        this.$watch('selectedGender', () => { this.currentPage = 1; });
        this.$watch('selectedSector', () => { this.currentPage = 1; });
        this.$watch('selectedStatus', () => { this.currentPage = 1; });
        this.$watch('perPage', () => { this.currentPage = 1; });
    },
    get activeList() {
        return this.items.filter(item => {
            const matchComp = (this.selectedCompetition === 'all' || item.comp_id === this.selectedCompetition);
            const matchStatus = (this.selectedStatus === 'all' || item.status === this.selectedStatus);
            const matchGender = (this.selectedGender === 'all' || item.gender === this.selectedGender);
            
            const isGanda = item.is_ganda;
            const isPa = item.gender === 'L';
            const isPi = item.gender === 'P';
            
            let matchSector = true;
            if (this.selectedSector === 'tunggal_pa') matchSector = (!isGanda && isPa);
            else if (this.selectedSector === 'tunggal_pa_a' || this.selectedSector === 'tmj_pa_a') matchSector = (!isGanda && isPa && item.is_kat_a);
            else if (this.selectedSector === 'tunggal_pa_b' || this.selectedSector === 'tmj_pa_b') matchSector = (!isGanda && isPa && item.is_kat_b);
            else if (this.selectedSector === 'tunggal_pa_c') matchSector = (!isGanda && isPa && item.is_kat_c);
            else if (this.selectedSector === 'tunggal_pi') matchSector = (!isGanda && isPi);
            else if (this.selectedSector === 'tunggal_pi_a' || this.selectedSector === 'tmj_pi_a') matchSector = (!isGanda && isPi && item.is_kat_a);
            else if (this.selectedSector === 'tunggal_pi_b' || this.selectedSector === 'tmj_pi_b') matchSector = (!isGanda && isPi && item.is_kat_b);
            else if (this.selectedSector === 'tunggal_pi_c') matchSector = (!isGanda && isPi && item.is_kat_c);
            else if (this.selectedSector === 'tmj_a_all' || this.selectedSector === 'kat_a') matchSector = item.is_kat_a;
            else if (this.selectedSector === 'tmj_b_all' || this.selectedSector === 'kat_b') matchSector = item.is_kat_b;
            else if (this.selectedSector === 'ganda_all' || this.selectedSector === 'ganda') matchSector = isGanda;
            else if (this.selectedSector === 'ganda_pa') matchSector = (isGanda && isPa);
            else if (this.selectedSector === 'ganda_pi') matchSector = (isGanda && isPi);
            else if (this.selectedSector === 'individu_pa') matchSector = isPa;
            else if (this.selectedSector === 'individu_pi') matchSector = isPi;

            const matchSearch = (!this.searchQuery || item.search.includes(this.searchQuery.toLowerCase()));
            return matchComp && matchStatus && matchGender && matchSector && matchSearch;
        });
    },
    get countAll() {
        return this.activeList.length;
    },
    get countPa() {
        return this.activeList.filter(i => i.gender === 'L').length;
    },
    get countPi() {
        return this.activeList.filter(i => i.gender === 'P').length;
    },
    get totalPages() {
        return Math.max(1, Math.ceil(this.activeList.length / this.perPage));
    },
    get paginatedList() {
        const page = Math.min(Math.max(1, this.currentPage), this.totalPages);
        const start = (page - 1) * this.perPage;
        return this.activeList.slice(start, start + this.perPage);
    },
    get paginatedIds() {
        return new Set(this.paginatedList.map(i => i.id));
    },
    isItemVisible(id) {
        return this.paginatedIds.has(id);
    },
    goToPage(p) {
        if (typeof p !== 'number') return;
        if (p < 1) p = 1;
        if (p > this.totalPages) p = this.totalPages;
        this.currentPage = p;
        const card = document.getElementById('participantsTableCard');
        if (card) {
            card.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    },
    prevPage() {
        if (this.currentPage > 1) {
            this.goToPage(this.currentPage - 1);
        }
    },
    nextPage() {
        if (this.currentPage < this.totalPages) {
            this.goToPage(this.currentPage + 1);
        }
    },
    get paginationPages() {
        const total = this.totalPages;
        const current = Math.min(Math.max(1, this.currentPage), total);
        if (total <= 7) {
            return Array.from({ length: total }, (_, i) => i + 1);
        }
        if (current <= 4) {
            return [1, 2, 3, 4, 5, '...', total];
        }
        if (current >= total - 3) {
            return [1, '...', total - 4, total - 3, total - 2, total - 1, total];
        }
        return [1, '...', current - 1, current, current + 1, '...', total];
    },
    get paginationStart() {
        if (this.activeList.length === 0) return 0;
        const page = Math.min(Math.max(1, this.currentPage), this.totalPages);
        return (page - 1) * this.perPage + 1;
    },
    get paginationEnd() {
        const page = Math.min(Math.max(1, this.currentPage), this.totalPages);
        return Math.min(page * this.perPage, this.activeList.length);
    }
}">

    <!-- Flash Notifications (Success & Error Feedback) -->
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-300 text-xs sm:text-sm font-bold flex items-center justify-between shadow-lg shadow-emerald-950/20">
            <div class="flex items-center gap-2.5">
                <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-400 shrink-0"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-emerald-400 hover:text-white p-1">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
    @endif

    @if(session('error') || $errors->any())
        <div class="p-4 rounded-2xl bg-rose-500/15 border border-rose-500/30 text-rose-300 text-xs sm:text-sm font-bold space-y-1.5 shadow-lg shadow-rose-950/20">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <i data-lucide="alert-triangle" class="w-5 h-5 text-rose-400 shrink-0"></i>
                    <span>{{ session('error') ?: 'Terjadi kesalahan saat memproses data pendaftaran:' }}</span>
                </div>
                <button type="button" @click="$el.closest('.p-4').remove()" class="text-rose-400 hover:text-white p-1">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
            @if($errors->any())
                <ul class="list-disc list-inside text-xs font-normal pl-7 space-y-0.5 text-rose-200">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            @endif
        </div>
    @endif

    <!-- Quick Stats Grid (AIStarterKit Design) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Cabang Dikelola -->
        <div class="ai-card p-4 sm:p-5 rounded-3xl border border-white/[0.08] shadow-lg flex items-center gap-3.5 hover:border-[#7A5AF8]/50 transition">
            <div class="w-10 h-10 rounded-2xl bg-[#7A5AF8]/15 text-[#A594FD] border border-[#7A5AF8]/30 flex items-center justify-center font-black shrink-0">
                <i data-lucide="medal" class="w-5 h-5"></i>
            </div>
            <div>
                <div class="text-2xl font-black text-white">{{ $stats['total_competitions'] }}</div>
                <div class="text-xs font-semibold text-slate-400">Cabang Dikelola</div>
            </div>
        </div>

        <!-- Card 2: Total Peserta & Komposisi PA/PI -->
        <div class="ai-card p-4 sm:p-5 rounded-3xl border border-white/[0.08] shadow-lg flex items-center gap-3.5 hover:border-[#4E6EFF]/50 transition">
            <div class="w-10 h-10 rounded-2xl bg-[#4E6EFF]/15 text-[#84D0FF] border border-[#4E6EFF]/30 flex items-center justify-center font-black shrink-0">
                <i data-lucide="users" class="w-5 h-5"></i>
            </div>
            <div>
                <div class="text-2xl font-black text-white" x-text="countAll"></div>
                <div class="flex items-center gap-1.5 text-[10px] font-bold text-slate-400 mt-0.5">
                    <span class="text-[#84D0FF] bg-[#4E6EFF]/15 border border-[#4E6EFF]/30 px-2 py-0.5 rounded-full font-bold"><span x-text="countPa"></span> PA</span>
                    <span>•</span>
                    <span class="text-[#FFA0E7] bg-[#FF58D5]/15 border border-[#FF58D5]/30 px-2 py-0.5 rounded-full font-bold"><span x-text="countPi"></span> PI</span>
                </div>
            </div>
        </div>

        <!-- Card 3: Terverifikasi -->
        <div class="ai-card p-4 sm:p-5 rounded-3xl border border-white/[0.08] shadow-lg flex items-center gap-3.5 hover:border-emerald-500/50 transition">
            <div class="w-10 h-10 rounded-2xl bg-emerald-500/15 text-emerald-400 border border-emerald-500/30 flex items-center justify-center font-black shrink-0">
                <i data-lucide="check-circle-2" class="w-5 h-5"></i>
            </div>
            <div>
                <div class="text-2xl font-black text-emerald-400">{{ $stats['verified_registrations'] }}</div>
                <div class="text-xs font-semibold text-slate-400">Terverifikasi</div>
            </div>
        </div>

        <!-- Card 4: Sudah Dapat No Undian -->
        <div class="ai-card p-4 sm:p-5 rounded-3xl border border-white/[0.08] shadow-lg flex items-center gap-3.5 hover:border-[#FF58D5]/50 transition">
            <div class="w-10 h-10 rounded-2xl bg-[#FF58D5]/15 text-[#FFA0E7] border border-[#FF58D5]/30 flex items-center justify-center font-black shrink-0">
                <i data-lucide="disc" class="w-5 h-5"></i>
            </div>
            <div>
                <div class="text-2xl font-black text-[#FFA0E7]">{{ $stats['drawn_participants'] }}</div>
                <div class="text-xs font-semibold text-slate-400">Sudah Diundi (TM)</div>
            </div>
        </div>
    </div>

    <!-- Master Filter & Action Header (AIStarterKit Design) -->
    <div class="ai-card rounded-3xl p-5 sm:p-6 border border-white/[0.08] shadow-xl space-y-4 relative z-30">
        
        <!-- Row 1: Search Bar & Quick Actions -->
        <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-3.5">
            
            <!-- Search input -->
            <div class="relative flex-1 min-w-[240px]">
                <i data-lucide="search" class="w-4 h-4 text-slate-500 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                <input type="text" x-model="searchQuery" placeholder="Cari nama peserta, NISN, asal sekolah, atau nomor registrasi..." class="w-full pl-10 pr-4 py-2.5 text-xs rounded-xl bg-[#0C111D] border border-white/[0.1] text-white placeholder-slate-500 focus:border-[#7A5AF8] focus:ring-2 focus:ring-[#7A5AF8]/20 outline-none">
            </div>

            <!-- Action Buttons: Tambah Peserta, Cetak/Export, Spin Wheel & Layar Panggung -->
            <div class="flex flex-wrap items-center gap-2">

                <!-- Tambah Peserta Manual Button -->
                <button @click="createModal = true; $nextTick(() => { if (window.lucide) lucide.createIcons(); })" type="button" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-black text-xs shadow-lg shadow-emerald-500/20 transition cursor-pointer shrink-0">
                    <i data-lucide="user-plus" class="w-4 h-4"></i>
                    <span>+ Tambah Peserta</span>
                </button>
                
                <!-- Print & Export Modal Launcher Button -->
                <button @click="selectedPrintCompetition = selectedCompetition; exportModal = true" type="button" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-[#4E6EFF]/15 hover:bg-[#4E6EFF]/25 text-[#84D0FF] border border-[#4E6EFF]/30 font-black text-xs transition cursor-pointer shrink-0">
                    <i data-lucide="printer" class="w-4 h-4"></i>
                    <span>Cetak & Export</span>
                </button>

                <!-- Spin Wheel Launcher Button -->
                <div class="relative" x-data="{ spinMenuOpen: false }">
                    <button @click="spinMenuOpen = !spinMenuOpen" type="button" class="gradient-btn inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl text-white font-black text-xs shadow-lg shadow-[#7A5AF8]/25 transition cursor-pointer shrink-0">
                        <i data-lucide="disc" class="w-4 h-4"></i>
                        <span>Putar Spin Wheel</span>
                        <i data-lucide="chevron-down" class="w-3.5 h-3.5"></i>
                    </button>

                    <!-- Dropdown Menu -->
                    <div x-show="spinMenuOpen" @click.away="spinMenuOpen = false" x-cloak class="absolute right-0 mt-2 w-64 bg-[#161F30] rounded-2xl shadow-2xl border border-white/[0.12] py-2 z-50 text-xs">
                        <div class="px-3 py-1.5 text-[10px] font-bold text-slate-400 uppercase tracking-wider border-b border-white/[0.08]">
                            Pilih Cabang untuk Diundi:
                        </div>
                        <div class="max-h-60 overflow-y-auto">
                            @foreach($competitions as $c)
                                <a href="{{ route('pic.spin.wheel', $c->id) }}" class="flex items-center justify-between px-3 py-2 hover:bg-white/[0.05] text-slate-200 font-bold transition">
                                    <span class="truncate">{{ $c->name }}</span>
                                    <span class="text-[10px] px-1.5 py-0.2 rounded bg-[#0C111D] text-[#84D0FF] border border-white/[0.08] font-mono">{{ $c->code }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Stage Timer & Layar Panggung Launcher Button -->
                <div class="relative" x-data="{ stageMenuOpen: false }">
                    <button @click="stageMenuOpen = !stageMenuOpen" type="button" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-amber-500/15 hover:bg-amber-500/25 text-amber-300 border border-amber-500/30 font-black text-xs transition cursor-pointer shrink-0">
                        <i data-lucide="tv" class="w-4 h-4 text-amber-400"></i>
                        <span>Layar Panggung</span>
                        <i data-lucide="chevron-down" class="w-3.5 h-3.5"></i>
                    </button>

                    <!-- Dropdown Menu -->
                    <div x-show="stageMenuOpen" @click.away="stageMenuOpen = false" x-cloak class="absolute right-0 mt-2 w-72 bg-[#161F30] rounded-2xl shadow-2xl border border-white/[0.12] py-2 z-50 text-xs">
                        <div class="px-3 py-1.5 text-[10px] font-bold text-amber-300 uppercase tracking-wider border-b border-white/[0.08] flex items-center justify-between">
                            <span>Layar Panggung & Stage Timer:</span>
                        </div>
                        <div class="max-h-60 overflow-y-auto divide-y divide-white/[0.04]">
                            @foreach($competitions as $c)
                                <div class="p-2.5 hover:bg-white/[0.04] transition space-y-1.5">
                                    <div class="flex items-center justify-between gap-1">
                                        <span class="font-bold text-white text-xs truncate">{{ $c->name }}</span>
                                        <span class="text-[9px] px-1.5 py-0.2 rounded bg-[#0C111D] text-amber-400 border border-amber-500/30 font-mono">{{ $c->code }}</span>
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        <a href="{{ route('pic.stage.control', $c->id) }}" class="flex-1 py-1 px-2 rounded-lg bg-white/[0.08] hover:bg-white/[0.15] text-slate-200 hover:text-white font-bold text-[10px] text-center transition">
                                            Panel Kontrol
                                        </a>
                                        <a href="{{ route('stage.viewer', $c->slug ?: $c->code) }}" target="_blank" class="py-1 px-2 rounded-lg bg-amber-500/20 hover:bg-amber-500/30 text-amber-300 font-bold text-[10px] text-center transition flex items-center gap-1">
                                            <i data-lucide="tv" class="w-3 h-3"></i>
                                            <span>TV</span>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Row 2: Gender Segment & 3 Filter Dropdowns (4-Column Balanced Grid) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3 pt-3.5 border-t border-white/[0.08] text-xs items-end">
            <!-- Filter 1: Gender Segmented Buttons -->
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Filter Gender:</label>
                <div class="flex items-center gap-1 bg-[#0C111D] border border-white/[0.1] p-1 rounded-xl h-[42px]">
                    <button type="button" @click="selectedGender = 'all'" :class="selectedGender === 'all' ? 'bg-gradient-to-r from-[#7A5AF8] to-[#4E6EFF] text-white font-black shadow-md' : 'text-slate-400 hover:text-white font-bold'" class="flex-1 h-full rounded-lg text-xs transition cursor-pointer flex items-center justify-center">
                        <span>Semua</span>
                    </button>
                    <button type="button" @click="selectedGender = 'L'" :class="selectedGender === 'L' ? 'bg-blue-600 text-white font-black shadow-md' : 'text-[#84D0FF] hover:text-white font-bold'" class="flex-1 h-full rounded-lg text-xs transition cursor-pointer flex items-center justify-center gap-1">
                        <span>PA</span>
                        <span class="text-[10px] px-1.5 py-0.2 rounded-full bg-blue-500/20 font-bold" x-text="countPa"></span>
                    </button>
                    <button type="button" @click="selectedGender = 'P'" :class="selectedGender === 'P' ? 'bg-rose-600 text-white font-black shadow-md' : 'text-[#FFA0E7] hover:text-white font-bold'" class="flex-1 h-full rounded-lg text-xs transition cursor-pointer flex items-center justify-center gap-1">
                        <span>PI</span>
                        <span class="text-[10px] px-1.5 py-0.2 rounded-full bg-rose-500/20 font-bold" x-text="countPi"></span>
                    </button>
                </div>
            </div>

            <!-- Filter 2: Cabang Lomba -->
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Cabang Lomba:</label>
                <select x-model="selectedCompetition" @change="selectedSector = 'all'" class="w-full px-3 py-2.5 h-[42px] rounded-xl bg-[#0C111D] border border-white/[0.1] text-xs font-bold text-slate-200 outline-none focus:border-[#7A5AF8] cursor-pointer">
                    <option value="all">Semua Cabang Lomba ({{ $competitions->count() }})</option>
                    @foreach($competitions as $comp)
                        <option value="{{ $comp->id }}">{{ $comp->name }} ({{ $comp->registrations->count() }} Pendaftar)</option>
                    @endforeach
                </select>
            </div>

            <!-- Filter 3: Sektor / Kategori Tanding -->
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1 truncate" x-text="currentCompCode === 'BLT' ? 'Sektor / Kelas Bulu Tangkis:' : (currentCompCode === 'TMJ' ? 'Kategori Tenis Meja:' : (['MTQ', 'POP'].includes(currentCompCode) ? 'Kategori Sektor (PA/PI):' : 'Kategori Lomba:'))"></label>
                <select x-model="selectedSector" :disabled="sectorOptions.length <= 1" :class="sectorOptions.length <= 1 ? 'opacity-70 bg-[#0C111D]/60' : 'cursor-pointer'" class="w-full px-3 py-2.5 h-[42px] rounded-xl bg-[#0C111D] border border-white/[0.1] text-xs font-bold text-slate-200 outline-none focus:border-[#7A5AF8]">
                    <template x-for="opt in sectorOptions" :key="opt.value">
                        <option :value="opt.value" x-text="opt.label"></option>
                    </template>
                </select>
            </div>

            <!-- Filter 4: Status Keabsahan -->
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Status Keabsahan:</label>
                <select x-model="selectedStatus" class="w-full px-3 py-2.5 h-[42px] rounded-xl bg-[#0C111D] border border-white/[0.1] text-xs font-bold text-slate-200 outline-none focus:border-[#7A5AF8] cursor-pointer">
                    <option value="all">Semua Status</option>
                    <option value="verified">✅ Terverifikasi ({{ $stats['verified_registrations'] ?? 0 }})</option>
                    <option value="pending">⏳ Menunggu ({{ $stats['pending_registrations'] ?? $stats['pending_verifications'] ?? 0 }})</option>
                    <option value="revision">⚠️ Butuh Revisi ({{ $stats['revision_registrations'] ?? 0 }})</option>
                    <option value="rejected">❌ Ditolak ({{ $stats['rejected_registrations'] ?? 0 }})</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Unified Data Table (AIStarterKit Design) -->
    <div id="participantsTableCard" class="ai-card rounded-3xl border border-white/[0.08] shadow-xl overflow-hidden scroll-mt-6">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm text-slate-300">
                <thead class="text-[11px] font-bold uppercase tracking-wider bg-[#0C111D]/90 text-slate-400 border-b border-white/[0.08]">
                    <tr>
                        <th class="py-3 px-3.5 sm:px-4">Kode & No. Reg</th>
                        <th class="py-3 px-3.5 sm:px-4">Nama Peserta / Tim</th>
                        <th class="py-3 px-3.5 sm:px-4">Cabang & Sektor / Kelas</th>
                        <th class="py-3 px-3.5 sm:px-4">Asal Sekolah</th>
                        <th class="py-3 px-3.5 sm:px-4 text-center">Berkas & Slip</th>
                        <th class="py-3 px-3.5 sm:px-4 text-center">No. Undian</th>
                        <th class="py-3 px-3.5 sm:px-4 text-center">Status</th>
                        <th class="py-3 px-3.5 sm:px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/[0.04] font-medium">
                    @forelse($allRegistrations as $index => $reg)
                        @php
                            $firstMember = $reg->members->first();
                            $isGanda = $reg->isGanda();
                            $gender = $reg->primary_gender;
                            $compCode = $reg->competition?->code ?? '';
                            $isKatA = $reg->isKatA();
                            $isKatB = $reg->isKatB();
                            $isKatC = $reg->isKatC();
                        @endphp
                        <tr class="hover:bg-white/[0.025] transition" x-show="isItemVisible({{ $reg->id }})">
                            <!-- Kode & No Reg -->
                            <td class="py-3 px-3.5 sm:px-4">
                                <span class="font-mono font-bold text-[#84D0FF] block text-xs">{{ $reg->participant_number ?: '-' }}</span>
                                <span class="text-[10px] font-mono text-slate-400">{{ $reg->registration_code }}</span>
                            </td>

                            <!-- Nama Peserta / Tim -->
                            <td class="py-3 px-3.5 sm:px-4">
                                @if($isGanda)
                                    <div class="font-bold text-white text-xs sm:text-sm">{{ $reg->team_name ?: $reg->display_name }}</div>
                                    <div class="text-[11px] text-slate-400 space-y-0.5 pt-0.5">
                                        @foreach($reg->members as $m)
                                            <div class="flex items-center gap-1.5">
                                                <span class="text-[10px] px-1.5 py-0.2 rounded {{ $m->gender === 'L' ? 'bg-[#4E6EFF]/15 text-[#84D0FF] border border-[#4E6EFF]/30' : 'bg-[#FF58D5]/15 text-[#FFA0E7] border border-[#FF58D5]/30' }} font-bold">
                                                    {{ $m->gender === 'L' ? 'PA' : 'PI' }}
                                                </span>
                                                <span>{{ $m->full_name }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="font-bold text-white text-xs sm:text-sm flex items-center gap-1.5">
                                        <span>{{ $firstMember?->full_name ?: $reg->display_name }}</span>
                                        @if($firstMember)
                                            <span class="text-[10px] px-1.5 py-0.2 rounded {{ $firstMember->gender === 'L' ? 'bg-[#4E6EFF]/15 text-[#84D0FF] border border-[#4E6EFF]/30' : 'bg-[#FF58D5]/15 text-[#FFA0E7] border border-[#FF58D5]/30' }} font-bold">
                                                {{ $firstMember->gender === 'L' ? '👦 PA' : '👧 PI' }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="text-[11px] text-slate-400 pt-0.5">
                                        <span>NISN: {{ $firstMember?->nisn ?: '-' }}</span>
                                    </div>
                                @endif
                            </td>

                            <!-- Cabang & Sektor / Kelas -->
                            <td class="py-3 px-3.5 sm:px-4">
                                <span class="font-bold text-white text-xs sm:text-sm block">{{ $reg->competition->name }}</span>
                                <div class="flex items-center gap-1 pt-0.5 flex-wrap">
                                    @if($reg->competition->code === 'BLT')
                                        @if(!$isGanda)
                                            <span class="text-[10px] font-bold px-1.5 py-0.2 rounded {{ $gender === 'L' ? 'bg-[#4E6EFF]/15 text-[#84D0FF] border border-[#4E6EFF]/30' : 'bg-[#FF58D5]/15 text-[#FFA0E7] border border-[#FF58D5]/30' }}">
                                                {{ $gender === 'L' ? '👦 Tunggal PA' : '👧 Tunggal PI' }}
                                            </span>
                                            @if($isKatA)
                                                <span class="text-[10px] text-emerald-400 font-bold bg-emerald-500/15 px-1.5 py-0.2 rounded border border-emerald-500/30">Kat A (Kelas 1–2)</span>
                                            @elseif($isKatB)
                                                <span class="text-[10px] text-emerald-400 font-bold bg-emerald-500/15 px-1.5 py-0.2 rounded border border-emerald-500/30">Kat B (Kelas 3–4)</span>
                                            @elseif($isKatC)
                                                <span class="text-[10px] text-emerald-400 font-bold bg-emerald-500/15 px-1.5 py-0.2 rounded border border-emerald-500/30">Kat C (Kelas 5–6)</span>
                                            @elseif($reg->target_class)
                                                <span class="text-[10px] text-emerald-400 font-bold bg-emerald-500/15 px-1.5 py-0.2 rounded border border-emerald-500/30">{{ $reg->target_class }}</span>
                                            @endif
                                        @else
                                            <span class="text-[10px] font-bold px-1.5 py-0.2 rounded {{ $gender === 'L' ? 'bg-blue-500/15 text-blue-400 border border-blue-500/30' : 'bg-amber-500/15 text-amber-400 border border-amber-500/30' }}">
                                                {{ $gender === 'L' ? '👥 Ganda PA (Putra)' : '👥 Ganda PI (Putri)' }}
                                            </span>
                                            <span class="text-[10px] text-slate-300 font-bold bg-white/[0.05] border border-white/[0.08] px-1.5 py-0.2 rounded">Semua Kelas</span>
                                        @endif
                                    @elseif($reg->competition->code === 'TMJ')
                                        <span class="text-[10px] font-bold px-1.5 py-0.2 rounded {{ $gender === 'L' ? 'bg-[#4E6EFF]/15 text-[#84D0FF] border border-[#4E6EFF]/30' : 'bg-[#FF58D5]/15 text-[#FFA0E7] border border-[#FF58D5]/30' }}">
                                            {{ $gender === 'L' ? '👦 Tunggal PA' : '👧 Tunggal PI' }}
                                        </span>
                                        @if($isKatA)
                                            <span class="text-[10px] text-emerald-400 font-bold bg-emerald-500/15 px-1.5 py-0.2 rounded border border-emerald-500/30">Kat A (Kelas 1–3)</span>
                                        @elseif($isKatB)
                                            <span class="text-[10px] text-emerald-400 font-bold bg-emerald-500/15 px-1.5 py-0.2 rounded border border-emerald-500/30">Kat B (Kelas 4–6)</span>
                                        @elseif($reg->target_class)
                                            <span class="text-[10px] text-emerald-400 font-bold bg-emerald-500/15 px-1.5 py-0.2 rounded border border-emerald-500/30">{{ $reg->target_class }}</span>
                                        @endif
                                    @elseif(in_array($reg->competition->code, ['MTQ', 'POP']))
                                        <span class="text-[10px] font-bold px-1.5 py-0.2 rounded {{ $gender === 'L' ? 'bg-[#4E6EFF]/15 text-[#84D0FF] border border-[#4E6EFF]/30' : 'bg-[#FF58D5]/15 text-[#FFA0E7] border border-[#FF58D5]/30' }}">
                                            {{ $gender === 'L' ? '👦 Individu PA' : '👧 Individu PI' }}
                                        </span>
                                        @if($reg->sub_category)
                                            <span class="text-[10px] text-slate-300 font-bold bg-white/[0.05] border border-white/[0.08] px-1.5 py-0.2 rounded">{{ $reg->sub_category }}</span>
                                        @endif
                                        @if($reg->target_class)
                                            <span class="text-[10px] text-emerald-400 font-bold bg-emerald-500/15 px-1.5 py-0.2 rounded border border-emerald-500/30">{{ $reg->target_class }}</span>
                                        @endif
                                    @else
                                        @if($isGanda)
                                            <span class="text-[10px] font-bold px-1.5 py-0.2 rounded bg-amber-500/15 text-amber-400 border border-amber-500/30">
                                                Tim / Regu
                                            </span>
                                        @endif
                                        @if($reg->sub_category)
                                            <span class="text-[10px] text-slate-300 font-bold bg-white/[0.05] border border-white/[0.08] px-1.5 py-0.2 rounded">{{ $reg->sub_category }}</span>
                                        @endif
                                        @if($reg->chosen_song)
                                            <span class="text-[10px] text-purple-300 font-bold bg-purple-500/15 border border-purple-500/30 px-1.5 py-0.2 rounded">🎵 {{ $reg->chosen_song }}</span>
                                        @endif
                                        @if($reg->target_class)
                                            <span class="text-[10px] text-emerald-400 font-bold bg-emerald-500/15 px-1.5 py-0.2 rounded border border-emerald-500/30">{{ $reg->target_class }}</span>
                                        @endif
                                    @endif
                                </div>
                            </td>

                            <!-- Asal Sekolah -->
                            <td class="py-3 px-3.5 sm:px-4">
                                <span class="text-xs font-bold text-slate-200 block">{{ $reg->display_school }}</span>
                                <span class="text-[10px] text-slate-400">{{ $reg->official_name ? 'Official: ' . $reg->official_name : '' }}</span>
                            </td>

                            <!-- Berkas & Slip -->
                            <td class="py-3 px-3.5 sm:px-4 text-center">
                                <div class="inline-flex items-center gap-1">
                                    @if($reg->document_file)
                                        <span class="p-1 rounded bg-emerald-500/15 text-emerald-400 border border-emerald-500/30" title="Surat Tugas Terlampir">
                                            <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
                                        </span>
                                    @else
                                        <span class="p-1 rounded bg-white/[0.05] text-slate-500 border border-white/[0.08]" title="Tidak ada surat">
                                            <i data-lucide="file" class="w-3.5 h-3.5"></i>
                                        </span>
                                    @endif

                                    @if($reg->payment_proof || ($reg->invoice && $reg->invoice->payment_proof))
                                        <span class="p-1 rounded bg-amber-500/15 text-amber-400 border border-amber-500/30" title="Slip Pembayaran Terlampir">
                                            <i data-lucide="credit-card" class="w-3.5 h-3.5"></i>
                                        </span>
                                    @else
                                        <span class="p-1 rounded bg-white/[0.05] text-slate-500 border border-white/[0.08]" title="Tanpa slip / Gratis">
                                            <i data-lucide="minus" class="w-3.5 h-3.5"></i>
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <!-- No Undian -->
                            <td class="py-3 px-3.5 sm:px-4 text-center">
                                @if($reg->draw_number)
                                    <span class="w-7 h-7 rounded-xl bg-amber-400 text-slate-950 font-black inline-flex items-center justify-center text-xs shadow-md">
                                        #{{ $reg->draw_number }}
                                    </span>
                                @else
                                    <span class="text-slate-500 text-xs font-mono">-</span>
                                @endif
                            </td>

                            <!-- Status -->
                            <td class="py-3 px-3.5 sm:px-4 text-center whitespace-nowrap">
                                @if($reg->status === 'verified')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-500/15 text-emerald-400 border border-emerald-500/30 inline-flex items-center gap-1">
                                        <i data-lucide="check" class="w-3 h-3"></i>
                                        <span>Terverifikasi</span>
                                    </span>
                                @elseif($reg->status === 'pending')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-500/15 text-amber-400 border border-amber-500/30 inline-flex items-center gap-1">
                                        <i data-lucide="clock" class="w-3 h-3"></i>
                                        <span>Menunggu</span>
                                    </span>
                                @elseif($reg->status === 'revision')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/40 inline-flex items-center gap-1">
                                        <i data-lucide="alert-triangle" class="w-3 h-3"></i>
                                        <span>Revisi</span>
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-500/15 text-rose-400 border border-rose-500/30">
                                        Ditolak
                                    </span>
                                @endif
                            </td>

                            <!-- 4 Compact Action Icons -->
                            <td class="py-3 px-3.5 sm:px-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <!-- 1. Icon Mata: Tinjau & Verifikasi Lengkap -->
                                    <button type="button" @click="selectedReg = {{ $reg->toJson() }}; verifyModal = true" class="w-8 h-8 rounded-xl bg-emerald-500/15 hover:bg-emerald-500/25 text-emerald-400 border border-emerald-500/30 flex items-center justify-center transition cursor-pointer" title="Tinjau Seluruh Data & Verifikasi">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                    </button>

                                    <!-- 2. Icon Edit: Edit Data Peserta -->
                                    <button type="button" @click="
                                        selectedEditReg = JSON.parse(JSON.stringify({{ $reg->toJson() }})); 
                                        if(selectedEditReg) { 
                                            if(selectedEditReg.members) { 
                                                selectedEditReg.members.forEach(m => { 
                                                    if(m.birth_date) m.birth_date = String(m.birth_date).split('T')[0]; 
                                                }); 
                                                if(selectedEditReg.members.length === 1 && selectedEditReg.members[0].school_name) {
                                                    selectedEditReg.institution_name = selectedEditReg.members[0].school_name;
                                                }
                                            }
                                            const cCode = (selectedEditReg.competition && selectedEditReg.competition.code ? selectedEditReg.competition.code : (selectedEditReg.registration_code ? selectedEditReg.registration_code.split('-')[0] : '')).toUpperCase();
                                            if (cCode === 'TMJ') {
                                                if (!selectedEditReg.match_type) {
                                                    const g = (selectedEditReg.members && selectedEditReg.members[0]) ? selectedEditReg.members[0].gender : 'L';
                                                    selectedEditReg.match_type = (g === 'P') ? 'Tunggal Putri (PI)' : 'Tunggal Putra (PA)';
                                                }
                                                let tc = selectedEditReg.target_class || selectedEditReg.sub_category || '';
                                                if (tc.includes('4-6') || tc.includes('4 - 6') || tc.toLowerCase().includes('kat b') || tc.toLowerCase().includes('kategori b') || tc.toLowerCase().includes('kelas 4') || tc.toLowerCase().includes('kelas 5') || tc.toLowerCase().includes('kelas 6')) {
                                                    selectedEditReg.target_class = 'Kategori B (Kelas 4 - 6)';
                                                } else {
                                                    selectedEditReg.target_class = 'Kategori A (Kelas 1 - 3)';
                                                }
                                            } else if (cCode === 'BLT') {
                                                let tc = selectedEditReg.target_class || selectedEditReg.sub_category || '';
                                                if (tc.toLowerCase().includes('ganda')) {
                                                    selectedEditReg.target_class = 'Ganda (Semua Kelas)';
                                                } else if (tc.includes('3-4') || tc.includes('3 - 4') || tc.toLowerCase().includes('kat b') || tc.toLowerCase().includes('kategori b')) {
                                                    selectedEditReg.target_class = 'Kategori B (Kelas 3 - 4)';
                                                } else if (tc.includes('5-6') || tc.includes('5 - 6') || tc.toLowerCase().includes('kat c') || tc.toLowerCase().includes('kategori c')) {
                                                    selectedEditReg.target_class = 'Kategori C (Kelas 5 - 6)';
                                                } else {
                                                    selectedEditReg.target_class = 'Kategori A (Kelas 1 - 2)';
                                                }
                                            }
                                        } 
                                        editModal = true;
                                    " class="w-8 h-8 rounded-xl bg-white/[0.06] hover:bg-white/[0.12] text-slate-200 border border-white/[0.1] flex items-center justify-center transition cursor-pointer" title="Edit Data Peserta">
                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                    </button>

                                    <!-- 3. Icon Cetak: Cetak ID Card / Bukti Satuan -->
                                    <button type="button" @click="selectedSingleReg = {{ $reg->toJson() }}; singlePrintModal = true" class="w-8 h-8 rounded-xl bg-[#4E6EFF]/15 hover:bg-[#4E6EFF]/25 text-[#84D0FF] border border-[#4E6EFF]/30 flex items-center justify-center transition cursor-pointer" title="Cetak Kartu Peserta / Formulir">
                                        <i data-lucide="printer" class="w-4 h-4"></i>
                                    </button>

                                    <!-- 4. Icon Sampah: Hapus Peserta -->
                                    <form action="{{ route('pic.delete.participant', $reg->id) }}" method="POST" onsubmit="return confirm('Hapus permanen pendaftaran peserta {{ addslashes($reg->display_name) }}? Seluruh berkas dan data anggota akan terhapus.');" class="inline">
                                        @csrf
                                        <button type="submit" class="w-8 h-8 rounded-xl bg-rose-500/15 hover:bg-rose-500/25 text-rose-400 border border-rose-500/30 flex items-center justify-center transition cursor-pointer" title="Hapus Data Peserta">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-12 text-slate-500 text-xs">
                                Belum ada data pendaftar yang masuk.
                            </td>
                        </tr>
                    @endforelse

                    <!-- Empty Filter State -->
                    <tr x-show="items.length > 0 && activeList.length === 0" x-cloak>
                        <td colspan="8" class="text-center py-12 text-slate-400 text-xs">
                            <div class="flex flex-col items-center justify-center gap-2">
                                <div class="w-10 h-10 rounded-2xl bg-white/[0.05] border border-white/[0.08] flex items-center justify-center text-slate-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7"/></svg>
                                </div>
                                <div class="font-bold text-slate-300 text-sm">Tidak ada peserta yang cocok dengan filter atau pencarian</div>
                                <p class="text-xs text-slate-500 max-w-sm">Coba ubah kata kunci atau setel ulang filter cabang lomba, status, atau sektor.</p>
                                <button type="button" @click="searchQuery = ''; selectedCompetition = 'all'; selectedSector = 'all'; selectedStatus = 'all'; selectedGender = 'all'" class="mt-2 inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-white/[0.08] hover:bg-white/[0.15] text-[#84D0FF] border border-white/[0.1] text-xs font-bold transition cursor-pointer">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    <span>Reset Semua Filter</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Table Pagination Footer (Tailwind Style) -->
        <div class="px-4 sm:px-6 py-4 border-t border-white/[0.08] bg-[#0C111D]/80 flex flex-col md:flex-row items-center justify-between gap-4">
            <!-- Left: Info & Per Page Selector -->
            <div class="flex flex-wrap items-center justify-center sm:justify-start gap-3 text-xs text-slate-400">
                <div>
                    Menampilkan <span class="font-bold text-white font-mono" x-text="paginationStart"></span>
                    sampai <span class="font-bold text-white font-mono" x-text="paginationEnd"></span>
                    dari <span class="font-bold text-[#84D0FF] font-mono" x-text="countAll"></span> peserta
                    <span x-show="countAll < items.length" class="text-slate-500 text-[11px]">
                        (total data: <span x-text="items.length"></span>)
                    </span>
                </div>

                <span class="text-white/[0.1] hidden sm:inline">•</span>

                <!-- Rows Per Page Selector -->
                <div class="flex items-center gap-1.5">
                    <span class="text-[11px] text-slate-400">Tampilkan:</span>
                    <select x-model.number="perPage" class="px-2.5 py-1 rounded-xl bg-[#161F30] border border-white/[0.12] text-xs font-bold text-slate-200 focus:border-[#7A5AF8] outline-none cursor-pointer">
                        <option :value="10">10 baris</option>
                        <option :value="25">25 baris</option>
                        <option :value="50">50 baris</option>
                        <option :value="100">100 baris</option>
                        <option :value="999999">Semua</option>
                    </select>
                </div>
            </div>

            <!-- Right: Pagination Buttons -->
            <div class="flex items-center gap-1 sm:gap-1.5" x-show="totalPages > 1">
                <!-- Prev Button -->
                <button 
                    type="button" 
                    @click="prevPage()" 
                    :disabled="currentPage === 1"
                    :class="currentPage === 1 ? 'opacity-30 cursor-not-allowed text-slate-500 border-white/[0.04]' : 'hover:bg-white/[0.1] text-slate-200 border-white/[0.1] hover:text-white cursor-pointer'"
                    class="px-2.5 sm:px-3 py-1.5 rounded-xl text-xs font-bold border transition flex items-center gap-1 bg-white/[0.04]"
                    title="Halaman Sebelumnya">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
                    <span class="hidden sm:inline">Sebelumnya</span>
                </button>

                <!-- Page Numbers -->
                <div class="flex items-center gap-1">
                    <template x-for="(p, idx) in paginationPages" :key="idx">
                        <div>
                            <!-- Ellipsis -->
                            <template x-if="p === '...'">
                                <span class="px-2 py-1 text-slate-500 text-xs font-bold select-none">...</span>
                            </template>
                            <!-- Number Button -->
                            <template x-if="p !== '...'">
                                <button 
                                    type="button" 
                                    @click="goToPage(p)" 
                                    :class="currentPage === p ? 'bg-gradient-to-r from-[#7A5AF8] to-[#4E6EFF] text-white font-black shadow-md shadow-[#7A5AF8]/30 border-transparent' : 'bg-white/[0.04] hover:bg-white/[0.1] text-slate-300 hover:text-white border-white/[0.08]'"
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
                    @click="nextPage()" 
                    :disabled="currentPage === totalPages"
                    :class="currentPage === totalPages ? 'opacity-30 cursor-not-allowed text-slate-500 border-white/[0.04]' : 'hover:bg-white/[0.1] text-slate-200 border-white/[0.1] hover:text-white cursor-pointer'"
                    class="px-2.5 sm:px-3 py-1.5 rounded-xl text-xs font-bold border transition flex items-center gap-1 bg-white/[0.04]"
                    title="Halaman Berikutnya">
                    <span class="hidden sm:inline">Berikutnya</span>
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                </button>
            </div>
        </div>
    </div>

    <!-- ==================== VERIFICATION MODAL (AI STARTER KIT DARK GLASS) ==================== -->
    <div x-show="verifyModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="verifyModal" @click="verifyModal = false" class="fixed inset-0 bg-black/80 backdrop-blur-md transition-opacity"></div>

            <div x-show="verifyModal" class="relative z-10 inline-block align-bottom bg-[#161F30] border border-white/[0.12] rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full p-6 sm:p-8 space-y-6">
                
                <!-- Modal Header -->
                <div class="flex items-center justify-between border-b border-white/[0.08] pb-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-mono font-bold text-[#84D0FF] px-2.5 py-1 bg-[#4E6EFF]/15 border border-[#4E6EFF]/30 rounded-lg" x-text="selectedReg ? selectedReg.registration_code : ''"></span>
                            <span class="text-xs font-mono font-bold text-slate-300 px-2.5 py-1 bg-white/[0.05] border border-white/[0.08] rounded-lg" x-text="selectedReg && selectedReg.participant_number ? 'No. Peserta: ' + selectedReg.participant_number : 'Belum Ada No. Peserta'"></span>
                        </div>
                        <h3 class="text-lg font-black text-white mt-2" x-text="selectedReg ? selectedReg.institution_name : ''"></h3>
                    </div>
                    <button @click="verifyModal = false" class="text-slate-400 hover:text-white transition cursor-pointer">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <!-- Complete Participant Info Card -->
                <div class="space-y-4 max-h-[60vh] overflow-y-auto pr-1">
                    
                    <!-- Section 1: Identitas Atlet / Anggota -->
                    <div class="p-4 rounded-2xl bg-[#0C111D] border border-white/[0.08] space-y-3">
                        <div class="flex items-center justify-between border-b border-white/[0.06] pb-2">
                            <span class="text-[11px] font-black uppercase tracking-wider text-slate-300 flex items-center gap-1.5">
                                <i data-lucide="user" class="w-3.5 h-3.5 text-[#A594FD]"></i>
                                <span>Data Atlet / Siswa Pendaftar</span>
                            </span>
                        </div>

                        <template x-if="selectedReg && selectedReg.members && selectedReg.members.length > 0">
                            <div class="space-y-2.5">
                                <template x-for="(m, idx) in selectedReg.members" :key="m.id || idx">
                                    <div class="bg-[#161F30] p-3 rounded-xl border border-white/[0.08] text-xs space-y-1">
                                        <div class="flex items-center justify-between font-bold">
                                            <span class="text-white text-sm" x-text="m.full_name"></span>
                                            <span class="text-[10px] px-2 py-0.5 rounded font-black" :class="m.gender === 'L' ? 'bg-[#4E6EFF]/15 text-[#84D0FF] border border-[#4E6EFF]/30' : 'bg-[#FF58D5]/15 text-[#FFA0E7] border border-[#FF58D5]/30'" x-text="m.gender === 'L' ? '👦 Putra (PA)' : '👧 Putri (PI)'"></span>
                                        </div>
                                        <div class="grid grid-cols-2 gap-2 text-slate-400 pt-1">
                                            <div class="col-span-2"><span class="text-slate-500">Asal Sekolah:</span> <span class="font-bold text-slate-200" x-text="m.school_name || (selectedReg ? (selectedReg.display_school || selectedReg.institution_name) : '-')"></span></div>
                                            <div><span class="text-slate-500">NISN:</span> <span class="font-mono font-bold text-slate-300" x-text="m.nisn || '-'"></span></div>
                                            <div><span class="text-slate-500">TTL:</span> <span class="font-medium text-slate-300" x-text="formatTTL(m.birth_place, m.formatted_birth_date || m.birth_date)"></span></div>
                                            <div><span class="text-slate-500">No HP/WA:</span> <span class="font-medium text-slate-300" x-text="m.phone || '-'"></span></div>
                                            <div><span class="text-slate-500">Peran:</span> <span class="font-medium text-slate-300" x-text="m.role_in_team || 'Peserta Utama'"></span></div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>

                    <!-- Section 2: Info Cabang Lomba & Official -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                        <div class="p-3.5 rounded-2xl bg-[#0C111D] border border-white/[0.08] space-y-1.5">
                            <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 block">Cabang Perlombaan</span>
                            <div class="font-black text-white text-sm" x-text="selectedReg && selectedReg.competition ? selectedReg.competition.name : ''"></div>
                            <div class="text-slate-400" x-text="'Sektor: ' + (selectedReg && selectedReg.sub_category ? selectedReg.sub_category : 'Umum')"></div>
                            <div class="text-emerald-400 font-bold" x-text="'Kelas: ' + (selectedReg && selectedReg.target_class ? selectedReg.target_class : 'Umum SD/MI')"></div>

                            <!-- Khusus Pop Singer / Lagu Pilihan -->
                            <template x-if="selectedReg && (selectedReg.chosen_song || (selectedReg.competition && (String(selectedReg.competition.code).toUpperCase() === 'POP' || String(selectedReg.competition.name || '').toLowerCase().includes('pop'))) || (selectedReg.registration_code && String(selectedReg.registration_code).toUpperCase().includes('POP')))">
                                <div class="pt-2 border-t border-purple-500/20 mt-2">
                                    <div class="flex items-center gap-1.5 text-purple-300 font-bold text-[10px] uppercase tracking-wider mb-1">
                                        <span>🎵 Lagu Pilihan:</span>
                                    </div>
                                    <div class="px-2.5 py-1.5 rounded-xl bg-purple-500/15 border border-purple-500/30 flex items-center justify-between">
                                        <span class="text-xs font-black text-purple-200" x-text="selectedReg.chosen_song || 'Belum memilih lagu'" :class="!selectedReg.chosen_song ? 'italic text-slate-400 font-normal' : ''"></span>
                                        <span class="text-[11px] text-purple-400 shrink-0 ml-2">🎤</span>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <div class="p-3.5 rounded-2xl bg-[#0C111D] border border-white/[0.08] space-y-1.5">
                            <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 block">Official / Pembina Pendamping</span>
                            <div class="font-black text-white text-sm" x-text="selectedReg && selectedReg.official_name ? selectedReg.official_name : '-'"></div>
                            <div class="text-slate-400" x-text="'No. Kontak: ' + (selectedReg && selectedReg.official_phone ? selectedReg.official_phone : '-')"></div>
                            <div class="text-slate-400" x-text="'Sekolah: ' + (selectedReg ? selectedReg.institution_name : '')"></div>
                        </div>
                    </div>

                    <!-- Collective Invoice Info -->
                    <template x-if="selectedReg && selectedReg.invoice">
                        <div class="p-3.5 rounded-2xl bg-[#4E6EFF]/10 border border-[#4E6EFF]/30 text-xs text-slate-200 space-y-1">
                            <div class="flex items-center justify-between font-bold">
                                <span class="inline-flex items-center gap-1.5 text-[#84D0FF]">
                                    <i data-lucide="file-spreadsheet" class="w-4 h-4 text-[#84D0FF]"></i>
                                    <span>Pendaftaran Kolektif (Rombongan)</span>
                                </span>
                                <span class="font-mono text-xs px-2 py-0.5 bg-[#4E6EFF]/20 rounded text-[#84D0FF]" x-text="selectedReg.invoice.invoice_number"></span>
                            </div>
                            <div class="flex items-center justify-between text-[11px] text-slate-400 pt-0.5">
                                <span>Total Tagihan Rombongan:</span>
                                <span class="font-black text-emerald-400 font-mono" x-text="'Rp ' + Number(selectedReg.invoice.final_amount).toLocaleString('id-ID')"></span>
                            </div>
                        </div>
                    </template>

                    <!-- Section 3: Lampiran Berkas & Pembayaran -->
                    <div class="p-4 rounded-2xl bg-[#0C111D] border border-white/[0.08] space-y-3">
                        <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 block">Lampiran Berkas & Bukti Pembayaran:</span>
                        <div class="grid grid-cols-2 gap-3 text-xs">
                            
                            <!-- Surat Tugas / Dokumen -->
                            <div class="p-3 rounded-xl bg-[#161F30] border border-white/[0.08] space-y-2">
                                <span class="font-bold text-slate-300 block text-[11px]">📄 Surat Keterangan / Rekomendasi</span>
                                <template x-if="selectedReg && selectedReg.document_file">
                                    <a :href="'{{ url('storage') }}/' + selectedReg.document_file.replace(/^(public\/|storage\/)+/, '')" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-500/15 text-emerald-400 border border-emerald-500/30 text-xs font-bold hover:bg-emerald-500/25 transition">
                                        <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                                        <span>Buka Surat</span>
                                    </a>
                                </template>
                                <template x-if="!selectedReg || !selectedReg.document_file">
                                    <span class="text-slate-500 text-xs italic">Tidak ada berkas</span>
                                </template>
                            </div>

                            <!-- Bukti Transfer / Pembayaran -->
                            <div class="p-3 rounded-xl bg-[#161F30] border border-white/[0.08] space-y-2">
                                <span class="font-bold text-slate-300 block text-[11px]">💳 Bukti Transfer / Struk / Slip</span>
                                <template x-if="selectedReg && (selectedReg.payment_proof || (selectedReg.invoice && selectedReg.invoice.payment_proof))">
                                    <a :href="'{{ url('storage') }}/' + (selectedReg.payment_proof || selectedReg.invoice.payment_proof).replace(/^(public\/|storage\/)+/, '')" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-amber-500/15 text-amber-400 border border-amber-500/30 text-xs font-bold hover:bg-amber-500/25 transition">
                                        <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                                        <span>Buka Slip Transfer</span>
                                    </a>
                                </template>
                                <template x-if="!selectedReg || (!selectedReg.payment_proof && (!selectedReg.invoice || !selectedReg.invoice.payment_proof))">
                                    <span class="text-slate-500 text-xs italic">Tidak ada slip (Gratis)</span>
                                </template>
                            </div>

                        </div>
                    </div>

                    <!-- Section 4: Formulir Keputusan Verifikasi -->
                    <form :action="'{{ url('pic/peserta') }}/' + (selectedReg ? selectedReg.id : '') + '/verifikasi'" method="POST" class="space-y-4 pt-2">
                        @csrf

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Pilih Keputusan Status Verifikasi</label>
                            <select name="status" required class="block w-full px-4 py-3 rounded-xl bg-[#0C111D] border border-white/[0.1] text-sm font-bold text-white outline-none focus:border-[#7A5AF8]">
                                <option value="verified">✅ Terverifikasi (Terbitkan No. Peserta)</option>
                                <option value="revision">⚠️ Minta Revisi / Perbaikan Berkas</option>
                                <option value="rejected">❌ Tolak Pendaftaran</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Catatan Panitia / Alasan</label>
                            <textarea name="verification_notes" rows="3" placeholder="Contoh: Berkas sah dan lengkap / Bukti transfer terkonfirmasi..." class="block w-full px-4 py-2.5 rounded-xl bg-[#0C111D] border border-white/[0.1] text-sm text-white placeholder-slate-500 outline-none focus:border-[#7A5AF8]"></textarea>
                        </div>

                        <div class="pt-4 flex items-center justify-between gap-3 border-t border-white/[0.08]">
                            <button type="button" @click="
                                const regData = selectedReg;
                                verifyModal = false;
                                selectedEditReg = JSON.parse(JSON.stringify(regData));
                                if(selectedEditReg && selectedEditReg.members) {
                                    selectedEditReg.members.forEach(m => {
                                        if(m.birth_date) m.birth_date = String(m.birth_date).split('T')[0];
                                    });
                                    if(selectedEditReg.members.length === 1 && selectedEditReg.members[0].school_name) {
                                        selectedEditReg.institution_name = selectedEditReg.members[0].school_name;
                                    }
                                }
                                editModal = true;
                            " class="px-3.5 py-2 rounded-xl bg-[#7A5AF8]/15 hover:bg-[#7A5AF8]/25 text-[#A594FD] border border-[#7A5AF8]/30 text-xs font-bold transition flex items-center gap-1.5 cursor-pointer" title="Edit data pendaftar ini">
                                <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                <span>Edit Data</span>
                            </button>

                            <div class="flex items-center gap-3">
                                <button type="button" @click="verifyModal = false" class="px-5 py-2.5 rounded-xl bg-white/[0.06] hover:bg-white/[0.1] text-slate-300 text-xs font-bold border border-white/[0.08] transition cursor-pointer">
                                    Batal
                                </button>
                                <button type="submit" class="gradient-btn px-6 py-2.5 rounded-xl text-white text-xs font-bold shadow-lg shadow-[#7A5AF8]/25 cursor-pointer">
                                    Simpan Keputusan
                                </button>
                            </div>
                        </div>
                    </form>

                </div>

            </div>
        </div>
    </div>

    <!-- ==================== EDIT PARTICIPANT MODAL (AI STARTER KIT DARK GLASS) ==================== -->
    <div x-show="editModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="edit-modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="editModal" @click="editModal = false" class="fixed inset-0 bg-black/80 backdrop-blur-md transition-opacity"></div>

            <div x-show="editModal" class="relative z-10 inline-block align-bottom bg-[#161F30] border border-white/[0.12] rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full p-6 sm:p-8 space-y-6">
                
                <!-- Modal Header -->
                <div class="flex items-center justify-between border-b border-white/[0.08] pb-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-mono font-bold text-amber-400 px-2.5 py-1 bg-amber-500/15 border border-amber-500/30 rounded-lg" x-text="selectedEditReg ? selectedEditReg.registration_code : ''"></span>
                            <span class="text-xs font-bold text-slate-400">Edit Data Pendaftaran Peserta (Admin)</span>
                        </div>
                        <h3 class="text-lg font-black text-white mt-2" x-text="selectedEditReg ? (selectedEditReg.institution_name || (selectedEditReg.members && selectedEditReg.members[0] ? selectedEditReg.members[0].school_name : '')) : ''"></h3>
                    </div>
                    <button @click="editModal = false" class="text-slate-400 hover:text-white transition cursor-pointer">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form :action="'{{ url('pic/peserta') }}/' + (selectedEditReg ? selectedEditReg.id : '') + '/update'" method="POST" enctype="multipart/form-data" class="space-y-4 max-h-[65vh] overflow-y-auto pr-1">
                    @csrf

                    <!-- Section 1: Data Atlet / Anggota Peserta -->
                    <div class="p-4 rounded-2xl bg-[#0C111D] border border-white/[0.08] space-y-3">
                        <span class="text-[11px] font-black uppercase tracking-wider text-slate-300 flex items-center gap-1.5">
                            <i data-lucide="user-check" class="w-3.5 h-3.5 text-[#A594FD]"></i>
                            <span>Data Siswa / Anggota Atlet</span>
                        </span>

                        <template x-if="selectedEditReg && selectedEditReg.members && selectedEditReg.members.length > 0">
                            <div class="space-y-3">
                                <template x-for="(m, idx) in selectedEditReg.members" :key="m.id || idx">
                                    <div class="bg-[#161F30] p-3.5 rounded-xl border border-white/[0.08] text-xs space-y-2.5">
                                        <input type="hidden" :name="'members[' + idx + '][id]'" :value="m.id">
                                        
                                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                                            <div>
                                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Nama Lengkap Siswa</label>
                                                <input type="text" :name="'members[' + idx + '][full_name]'" x-model="m.full_name" required class="w-full px-3 py-2 rounded-xl bg-[#0C111D] border border-white/[0.1] text-xs font-bold text-white outline-none focus:border-[#7A5AF8]">
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Asal Sekolah Siswa</label>
                                                <input type="text" :name="'members[' + idx + '][school_name]'" x-model="m.school_name" 
                                                    @input="if(selectedEditReg && selectedEditReg.members && selectedEditReg.members.length === 1) { selectedEditReg.institution_name = m.school_name; }"
                                                    placeholder="Nama Sekolah..." class="w-full px-3 py-2 rounded-xl bg-[#0C111D] border border-white/[0.1] text-xs font-bold text-white outline-none focus:border-[#7A5AF8]">
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">NISN</label>
                                                <input type="text" :name="'members[' + idx + '][nisn]'" x-model="m.nisn" placeholder="0012345678" class="w-full px-3 py-2 rounded-xl bg-[#0C111D] border border-white/[0.1] text-xs font-mono font-bold text-white outline-none focus:border-[#7A5AF8]">
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                                            <div>
                                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Gender (PA/PI)</label>
                                                <select :name="'members[' + idx + '][gender]'" x-model="m.gender" class="w-full px-3 py-2 rounded-xl bg-[#0C111D] border border-white/[0.1] text-xs font-bold text-white outline-none focus:border-[#7A5AF8]">
                                                    <option value="L">👦 Laki-laki (PA)</option>
                                                    <option value="P">👧 Perempuan (PI)</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Tempat Lahir</label>
                                                <input type="text" :name="'members[' + idx + '][birth_place]'" x-model="m.birth_place" class="w-full px-3 py-2 rounded-xl bg-[#0C111D] border border-white/[0.1] text-xs text-white outline-none focus:border-[#7A5AF8]">
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Tanggal Lahir</label>
                                                <input type="date" :name="'members[' + idx + '][birth_date]'" x-model="m.birth_date" class="w-full px-3 py-2 rounded-xl bg-[#0C111D] border border-white/[0.1] text-xs text-white outline-none focus:border-[#7A5AF8]">
                                            </div>
                                        </div>

                                        <div>
                                            <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">No. WhatsApp Siswa (Opsional)</label>
                                            <input type="text" :name="'members[' + idx + '][phone]'" x-model="m.phone" placeholder="08..." class="w-full px-3 py-2 rounded-xl bg-[#0C111D] border border-white/[0.1] text-xs text-white outline-none focus:border-[#7A5AF8]">
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>

                        <!-- Team Name if applicable -->
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Nama Tim / Pasangan Ganda (Opsional)</label>
                            <input type="text" name="team_name" x-model="selectedEditReg.team_name" placeholder="Nama Regu / Pasangan Ganda..." class="w-full px-3 py-2 rounded-xl bg-[#0C111D] border border-white/[0.1] text-xs font-bold text-white outline-none focus:border-[#7A5AF8]">
                        </div>
                    </div>

                    <!-- Section 2: Data Sekolah & Official -->
                    <div class="p-4 rounded-2xl bg-[#0C111D] border border-white/[0.08] space-y-3">
                        <span class="text-[11px] font-black uppercase tracking-wider text-slate-300 flex items-center gap-1.5">
                            <i data-lucide="building" class="w-3.5 h-3.5 text-[#84D0FF]"></i>
                            <span>Asal Sekolah & Guru Pendamping (Official)</span>
                        </span>

                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Asal Sekolah / Madrasah Utama (Kontingen)</label>
                            <input type="text" name="institution_name" x-model="selectedEditReg.institution_name" 
                                @input="if(selectedEditReg && selectedEditReg.members && selectedEditReg.members.length === 1 && selectedEditReg.members[0]) { selectedEditReg.members[0].school_name = selectedEditReg.institution_name; }"
                                required class="w-full px-3 py-2 rounded-xl bg-[#0C111D] border border-white/[0.1] text-xs font-bold text-white outline-none focus:border-[#7A5AF8]">
                            <p class="text-[10px] text-slate-400 mt-1">Nama sekolah/instansi resmi yang tampil pada tabel data peserta, bagan pertandingan, dan cetak sertifikat.</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Nama Guru Pendamping / Official</label>
                                <input type="text" name="official_name" x-model="selectedEditReg.official_name" class="w-full px-3 py-2 rounded-xl bg-[#0C111D] border border-white/[0.1] text-xs text-white outline-none focus:border-[#7A5AF8]">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">No. WhatsApp Official</label>
                                <input type="text" name="official_phone" x-model="selectedEditReg.official_phone" class="w-full px-3 py-2 rounded-xl bg-[#0C111D] border border-white/[0.1] text-xs text-white outline-none focus:border-[#7A5AF8]">
                            </div>
                        </div>
                    </div>

                    <!-- Section 3: Kategori Kelas & Nomor Administrasi -->
                    <div class="p-4 rounded-2xl bg-[#0C111D] border border-white/[0.08] space-y-3">
                        <span class="text-[11px] font-black uppercase tracking-wider text-slate-300 flex items-center gap-1.5">
                            <i data-lucide="tag" class="w-3.5 h-3.5 text-[#A594FD]"></i>
                            <span>Kategori Kelas & Nomor Administrasi</span>
                        </span>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5">
                            <!-- 1. Kategori / Kelompok Kelas -->
                            <div :class="['BLT', 'TMJ', 'MTQ', 'POP'].includes(editCompCode) ? '' : 'sm:col-span-2'">
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Kategori / Kelompok Kelas</label>
                                
                                <!-- Opsi Khusus Tenis Meja (TMJ) -->
                                <div x-show="editCompCode === 'TMJ'">
                                    <select name="target_class" :disabled="editCompCode !== 'TMJ'" x-model="selectedEditReg.target_class" class="w-full px-3 py-2 rounded-xl bg-[#0C111D] border border-white/[0.1] text-xs font-bold text-white outline-none focus:border-[#7A5AF8]">
                                        <option value="Kategori A (Kelas 1 - 3)">🏓 Kategori A (Kelas 1–3 SD/MI)</option>
                                        <option value="Kategori B (Kelas 4 - 6)">🏓 Kategori B (Kelas 4–6 SD/MI)</option>
                                    </select>
                                </div>

                                <!-- Opsi Khusus Bulu Tangkis (BLT) -->
                                <div x-show="editCompCode === 'BLT'">
                                    <select name="target_class" :disabled="editCompCode !== 'BLT'" x-model="selectedEditReg.target_class" class="w-full px-3 py-2 rounded-xl bg-[#0C111D] border border-white/[0.1] text-xs font-bold text-white outline-none focus:border-[#7A5AF8]">
                                        <option value="Ganda (Semua Kelas)">👥 Ganda (Semua Jenjang SD/MI)</option>
                                        <option value="Kategori A (Kelas 1 - 2)">🏷️ Kategori A (Kelas 1–2 SD/MI)</option>
                                        <option value="Kategori B (Kelas 3 - 4)">🏷️ Kategori B (Kelas 3–4 SD/MI)</option>
                                        <option value="Kategori C (Kelas 5 - 6)">🏷️ Kategori C (Kelas 5–6 SD/MI)</option>
                                    </select>
                                </div>

                                <!-- Opsi Lomba Lain -->
                                <div x-show="editCompCode !== 'TMJ' && editCompCode !== 'BLT'">
                                    <select name="target_class" :disabled="editCompCode === 'TMJ' || editCompCode === 'BLT'" x-model="selectedEditReg.target_class" class="w-full px-3 py-2 rounded-xl bg-[#0C111D] border border-white/[0.1] text-xs font-bold text-white outline-none focus:border-[#7A5AF8]">
                                        <option value="">-- Tanpa Kategori Khusus --</option>
                                        <option value="Semua Kelas SD/MI">Umum (Semua Jenjang SD/MI)</option>
                                    </select>
                                </div>
                            </div>

                            <!-- 2. Sektor / Nomor Tanding -->
                            <div x-show="['BLT', 'TMJ', 'MTQ', 'POP'].includes(editCompCode)">
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Nomor / Sektor Tanding</label>
                                
                                <!-- Opsi Sektor Tenis Meja (TMJ) -->
                                <div x-show="editCompCode === 'TMJ'">
                                    <select name="match_type" :disabled="editCompCode !== 'TMJ'" x-model="selectedEditReg.match_type" 
                                        @change="if(selectedEditReg && selectedEditReg.members && selectedEditReg.members[0]) { if($el.value.includes('Putri') || $el.value.includes('(PI)')) selectedEditReg.members[0].gender = 'P'; else if($el.value.includes('Putra') || $el.value.includes('(PA)')) selectedEditReg.members[0].gender = 'L'; }"
                                        class="w-full px-3 py-2 rounded-xl bg-[#0C111D] border border-white/[0.1] text-xs font-bold text-white outline-none focus:border-[#7A5AF8]">
                                        <option value="Tunggal Putra (PA)">👤 Tunggal Putra (PA)</option>
                                        <option value="Tunggal Putri (PI)">👤 Tunggal Putri (PI)</option>
                                    </select>
                                </div>

                                <!-- Opsi Sektor Bulu Tangkis (BLT) -->
                                <div x-show="editCompCode === 'BLT'">
                                    <select name="match_type" :disabled="editCompCode !== 'BLT'" x-model="selectedEditReg.match_type" 
                                        @change="if($el.value.includes('Ganda')) { if(selectedEditReg) selectedEditReg.target_class = 'Ganda (Semua Kelas)'; }"
                                        class="w-full px-3 py-2 rounded-xl bg-[#0C111D] border border-white/[0.1] text-xs font-bold text-white outline-none focus:border-[#7A5AF8]">
                                        <option value="Tunggal Putra (PA)">👤 Tunggal Putra (PA)</option>
                                        <option value="Tunggal Putri (PI)">👤 Tunggal Putri (PI)</option>
                                        <option value="Ganda Putra (PA)">👥 Ganda Putra (PA)</option>
                                        <option value="Ganda Putri (PI)">👥 Ganda Putri (PI)</option>
                                    </select>
                                </div>

                                <!-- Opsi Sektor MTQ / POP -->
                                <div x-show="['MTQ', 'POP'].includes(editCompCode)">
                                    <select name="match_type" :disabled="!['MTQ', 'POP'].includes(editCompCode)" x-model="selectedEditReg.match_type" 
                                        @change="if(selectedEditReg && selectedEditReg.members && selectedEditReg.members[0]) { if($el.value.includes('Putri') || $el.value.includes('(PI)')) selectedEditReg.members[0].gender = 'P'; else if($el.value.includes('Putra') || $el.value.includes('(PA)')) selectedEditReg.members[0].gender = 'L'; }"
                                        class="w-full px-3 py-2 rounded-xl bg-[#0C111D] border border-white/[0.1] text-xs font-bold text-white outline-none focus:border-[#7A5AF8]">
                                        <option value="Putra (PA)">👤 Putra (PA)</option>
                                        <option value="Putri (PI)">👤 Putri (PI)</option>
                                    </select>
                                </div>
                            </div>

                            <!-- 3. No. Peserta Resmi -->
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">No. Peserta (Resmi)</label>
                                <input type="text" name="participant_number" x-model="selectedEditReg.participant_number" placeholder="TMJ-01" class="w-full px-3 py-2 rounded-xl bg-[#0C111D] border border-white/[0.1] text-xs font-mono font-bold text-white outline-none focus:border-[#7A5AF8]">
                            </div>

                            <!-- 4. No. Undian Tampil (TM) -->
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">No. Undian Tampil (TM)</label>
                                <input type="number" name="draw_number" x-model="selectedEditReg.draw_number" placeholder="1, 2, 3..." class="w-full px-3 py-2 rounded-xl bg-[#0C111D] border border-white/[0.1] text-xs font-mono font-bold text-white outline-none focus:border-[#7A5AF8]">
                            </div>
                        </div>

                        <!-- Khusus Pop Singer: Edit Lagu Pilihan -->
                        <div x-show="isPopSinger" class="pt-3 border-t border-purple-500/20 space-y-2.5">
                            <div class="flex items-center justify-between">
                                <label class="block text-[11px] font-black uppercase tracking-wider text-purple-300 flex items-center gap-1.5">
                                    <span>🎵</span>
                                    <span>Lagu Pilihan Peserta (Pop Singer)</span>
                                </label>
                                <span class="text-[10px] font-bold text-purple-300 px-2 py-0.5 rounded-full bg-purple-500/20 border border-purple-500/30 font-mono">
                                    Pop Singer
                                </span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                <!-- 1. Dropdown Pilihan Lagu Resmi Panitia -->
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-400 mb-1 uppercase tracking-wider">Pilih Opsi Lagu Resmi:</label>
                                    <select @change="if($event.target.value) { selectedEditReg.chosen_song = $event.target.value; }" class="w-full px-3 py-2.5 rounded-xl bg-[#0C111D] border border-purple-500/40 text-xs font-bold text-white outline-none focus:border-purple-400 cursor-pointer">
                                        <option value="">-- Pilih Dari Daftar Lagu --</option>
                                        @foreach($popSongOptions as $song)
                                            <option value="{{ $song }}" :selected="selectedEditReg.chosen_song === '{{ addslashes($song) }}'">🎵 {{ $song }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- 2. Input Teks Lagu Terpilih / Custom Edit -->
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-400 mb-1 uppercase tracking-wider">Judul Lagu Tersimpan / Ubah Manual:</label>
                                    <input type="text" name="chosen_song" x-model="selectedEditReg.chosen_song" placeholder="Judul lagu pilihan..." class="w-full px-3 py-2.5 rounded-xl bg-[#0C111D] border border-purple-500/30 text-xs font-bold text-purple-200 outline-none focus:border-purple-400">
                                </div>
                            </div>
                            <p class="text-[10px] text-slate-400 italic">
                                * Pilih lagu dari menu dropdown di atas atau ketik langsung pada kotak isian jika ada penyesuaian judul.
                            </p>
                        </div>
                    </div>

                    <!-- Section 4: Ganti Berkas & Slip (Opsional) -->
                    <div class="p-4 rounded-2xl bg-[#0C111D] border border-white/[0.08] space-y-3">
                        <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 block">Ganti Berkas / Dokumen (Kosongkan jika tidak diubah):</span>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                            <div class="p-3 rounded-xl bg-[#161F30] border border-white/[0.08] space-y-1.5">
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-300">📄 Ganti Surat Keterangan / Rekomendasi</label>
                                <input type="file" name="document_file" accept=".pdf,.jpg,.jpeg,.png" class="w-full text-xs text-slate-400 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-emerald-500/20 file:text-emerald-300 hover:file:bg-emerald-500/30">
                            </div>
                            <div class="p-3 rounded-xl bg-[#161F30] border border-white/[0.08] space-y-1.5">
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-300">💳 Ganti Bukti Transfer / Struk / Slip</label>
                                <input type="file" name="payment_proof" accept=".pdf,.jpg,.jpeg,.png" class="w-full text-xs text-slate-400 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-amber-500/20 file:text-amber-300 hover:file:bg-amber-500/30">
                            </div>
                        </div>
                    </div>

                    <!-- Modal Actions -->
                    <div class="pt-4 flex items-center justify-end gap-3 border-t border-white/[0.08]">
                        <button type="button" @click="editModal = false" class="px-5 py-2.5 rounded-xl bg-white/[0.06] hover:bg-white/[0.1] text-slate-300 text-xs font-bold border border-white/[0.08] transition cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" class="gradient-btn px-6 py-2.5 rounded-xl text-white text-xs font-bold shadow-lg shadow-[#7A5AF8]/25 cursor-pointer">
                            Simpan Perubahan Data
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <!-- ==================== EXPORT & PRINT MODAL (AI STARTER KIT DARK GLASS) ==================== -->
    <div x-show="exportModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="export-modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="exportModal" @click="exportModal = false" class="fixed inset-0 bg-black/80 backdrop-blur-md transition-opacity"></div>

            <div x-show="exportModal" class="relative z-10 inline-block align-bottom bg-[#161F30] border border-white/[0.12] rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full p-6 sm:p-8 space-y-6">
                
                <!-- Header -->
                <div class="flex items-center justify-between border-b border-white/[0.08] pb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-[#4E6EFF]/15 text-[#84D0FF] border border-[#4E6EFF]/30 flex items-center justify-center font-black shrink-0">
                            <i data-lucide="printer" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-black text-white">Cetak & Export Data Peserta</h3>
                            <p class="text-xs text-slate-400">Pilih format cetak resmi (PDF) atau unduhan data (Excel)</p>
                        </div>
                    </div>
                    <button @click="exportModal = false" class="text-slate-400 hover:text-white transition cursor-pointer">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <!-- Parameter Filters -->
                <div class="space-y-3.5 bg-[#0C111D] p-4 rounded-2xl border border-white/[0.08] text-xs">
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Pilih Cabang Lomba:</label>
                        <select x-model="selectedPrintCompetition" @change="selectedPrintCategory = 'all'" class="w-full px-3 py-2.5 rounded-xl bg-[#161F30] border border-white/[0.1] text-xs font-bold text-slate-200 outline-none focus:border-[#7A5AF8] cursor-pointer">
                            <option value="all">Semua Cabang Lomba ({{ $competitions->count() }})</option>
                            @foreach($competitions as $comp)
                                <option value="{{ $comp->id }}">{{ $comp->name }} ({{ $comp->registrations->count() }} Pendaftar)</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-2.5">
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Status Keabsahan:</label>
                            <select x-model="selectedPrintStatus" class="w-full px-3 py-2.5 rounded-xl bg-[#161F30] border border-white/[0.1] text-xs font-bold text-slate-200 outline-none focus:border-[#7A5AF8] cursor-pointer">
                                <option value="all">Semua Status</option>
                                <option value="verified">✅ Terverifikasi Saja</option>
                                <option value="pending">⏳ Menunggu Saja</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Kelompok Gender:</label>
                            <select x-model="selectedPrintGender" class="w-full px-3 py-2.5 rounded-xl bg-[#161F30] border border-white/[0.1] text-xs font-bold text-slate-200 outline-none focus:border-[#7A5AF8] cursor-pointer">
                                <option value="all">Semua (Pisah Halaman PA & PI)</option>
                                <option value="L">👦 Khusus Putra (PA)</option>
                                <option value="P">👧 Khusus Putri (PI)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Filter Kategori & Kelas Khusus Tenis Meja (TMJ) -->
                    <div x-show="selectedPrintCompCode === 'TMJ'" x-cloak class="pt-2 border-t border-white/[0.08]">
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-amber-400 mb-1 flex items-center gap-1.5">
                            <span>🏓</span>
                            <span>Kategori & Kelas (Tenis Meja):</span>
                        </label>
                        <select x-model="selectedPrintCategory" class="w-full px-3 py-2.5 rounded-xl bg-[#161F30] border border-amber-500/30 text-xs font-bold text-amber-200 outline-none focus:border-amber-400 cursor-pointer">
                            <option value="all">Semua Kategori (Pisah Halaman Kat A & Kat B)</option>
                            <option value="kat_a">Kategori A (Kelas 1–3 SD/MI)</option>
                            <option value="kat_b">Kategori B (Kelas 4–6 SD/MI)</option>
                        </select>
                    </div>

                    <!-- Filter Kategori & Sektor Khusus Bulu Tangkis (BLT) -->
                    <div x-show="selectedPrintCompCode === 'BLT'" x-cloak class="pt-2 border-t border-white/[0.08]">
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-emerald-400 mb-1 flex items-center gap-1.5">
                            <span>🏸</span>
                            <span>Kategori & Sektor (Bulu Tangkis):</span>
                        </label>
                        <select x-model="selectedPrintCategory" class="w-full px-3 py-2.5 rounded-xl bg-[#161F30] border border-emerald-500/30 text-xs font-bold text-emerald-200 outline-none focus:border-emerald-400 cursor-pointer">
                            <option value="all">Semua Kategori (Pisah Halaman A, B, C & Ganda)</option>
                            <option value="kat_a">Kategori A (Kelas 1–2 SD/MI)</option>
                            <option value="kat_b">Kategori B (Kelas 3–4 SD/MI)</option>
                            <option value="kat_c">Kategori C (Kelas 5–6 SD/MI)</option>
                            <option value="ganda">Kategori Ganda (Semua Kelas)</option>
                        </select>
                    </div>
                </div>

                <!-- 2 Action Options -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    
                    <!-- Option 1: PDF Print Out -->
                    <a :href="'{{ url('pic/peserta/cetak-pdf') }}?competition_id=' + selectedPrintCompetition + '&status=' + selectedPrintStatus + '&gender=' + selectedPrintGender + '&category_class=' + selectedPrintCategory" target="_blank" class="p-4 rounded-2xl border border-emerald-500/30 bg-emerald-500/10 hover:bg-emerald-500/20 hover:border-emerald-500/60 transition group flex flex-col justify-between cursor-pointer space-y-3">
                        <div class="space-y-1.5">
                            <div class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 flex items-center justify-center font-bold">
                                <i data-lucide="file-text" class="w-4 h-4"></i>
                            </div>
                            <h4 class="font-black text-white text-sm group-hover:text-emerald-400 transition">Cetak PDF / Print Out</h4>
                            <p class="text-[11px] text-slate-400 leading-relaxed">
                                Dilengkapi <strong>KOP Surat Resmi MTsN 1 Blitar</strong>. Otomatis pisah halaman per PA/PI dan Kategori Kelas.
                            </p>
                        </div>
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-400 pt-2 border-t border-emerald-500/20">
                            <span>Buka Dokumen PDF</span>
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                        </span>
                    </a>

                    <!-- Option 2: Excel Export -->
                    <a :href="'{{ url('pic/peserta/export-excel') }}?competition_id=' + selectedPrintCompetition + '&status=' + selectedPrintStatus + '&gender=' + selectedPrintGender + '&category_class=' + selectedPrintCategory" class="p-4 rounded-2xl border border-[#4E6EFF]/30 bg-[#4E6EFF]/10 hover:bg-[#4E6EFF]/20 hover:border-[#4E6EFF]/60 transition group flex flex-col justify-between cursor-pointer space-y-3">
                        <div class="space-y-1.5">
                            <div class="w-8 h-8 rounded-xl bg-[#4E6EFF]/20 text-[#84D0FF] border border-[#4E6EFF]/30 flex items-center justify-center font-bold">
                                <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                            </div>
                            <h4 class="font-black text-white text-sm group-hover:text-[#84D0FF] transition">Export Excel (.xls)</h4>
                            <p class="text-[11px] text-slate-400 leading-relaxed">
                                Data rapi dalam <strong>1 Single Sheet</strong> yang diurutkan berurutan berdasarkan Kategori Kelas dan PA/PI.
                            </p>
                        </div>
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold text-[#84D0FF] pt-2 border-t border-[#4E6EFF]/20">
                            <span>Download File Excel</span>
                            <i data-lucide="download" class="w-3.5 h-3.5"></i>
                        </span>
                    </a>

                </div>

            </div>
        </div>
    </div>

    <!-- ==================== INDIVIDUAL PARTICIPANT PRINT MODAL (AI STARTER KIT DARK GLASS) ==================== -->
    <div x-show="singlePrintModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="single-print-modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="singlePrintModal" @click="singlePrintModal = false" class="fixed inset-0 bg-black/80 backdrop-blur-md transition-opacity"></div>

            <div x-show="singlePrintModal" class="relative z-10 inline-block align-bottom bg-[#161F30] border border-white/[0.12] rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full p-6 sm:p-8 space-y-6">
                
                <!-- Header -->
                <div class="flex items-center justify-between border-b border-white/[0.08] pb-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-mono font-bold text-[#84D0FF] px-2.5 py-1 bg-[#4E6EFF]/15 border border-[#4E6EFF]/30 rounded-lg" x-text="selectedSingleReg ? selectedSingleReg.registration_code : ''"></span>
                            <span class="text-xs font-bold text-slate-400">Cetak Berkas Administrasi Peserta</span>
                        </div>
                        <h3 class="text-lg font-black text-white mt-2" x-text="selectedSingleReg ? (selectedSingleReg.team_name || (selectedSingleReg.members && selectedSingleReg.members[0] ? selectedSingleReg.members[0].full_name : selectedSingleReg.institution_name)) : ''"></h3>
                        <p class="text-xs text-slate-400" x-text="selectedSingleReg ? selectedSingleReg.institution_name : ''"></p>
                    </div>
                    <button @click="singlePrintModal = false" class="text-slate-400 hover:text-white transition cursor-pointer">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <!-- 3 Options Card -->
                <div class="space-y-3">
                    
                    <!-- 1. Bukti Akun Pendaftar -->
                    <a :href="'{{ url('dokumen/bukti-akun') }}/' + (selectedSingleReg ? selectedSingleReg.id : '')" target="_blank" class="p-4 rounded-2xl border border-white/[0.08] bg-[#0C111D] hover:bg-white/[0.04] hover:border-[#4E6EFF]/50 transition group flex items-center justify-between cursor-pointer">
                        <div class="flex items-center gap-3.5">
                            <div class="w-10 h-10 rounded-xl bg-[#4E6EFF]/15 text-[#84D0FF] border border-[#4E6EFF]/30 flex items-center justify-center font-bold shrink-0 shadow-xs">
                                <i data-lucide="user-check" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h4 class="font-black text-white text-xs sm:text-sm group-hover:text-[#84D0FF] transition">📄 Bukti Akun Pendaftar</h4>
                                <p class="text-[11px] text-slate-400 mt-0.5">Tanda bukti pembuatan akun sistem, email, dan instansi.</p>
                            </div>
                        </div>
                        <i data-lucide="printer" class="w-4 h-4 text-[#84D0FF] shrink-0"></i>
                    </a>

                    <!-- 2. Bukti Pendaftaran & Kartu Peserta -->
                    <a :href="'{{ url('dokumen/bukti-pendaftaran') }}/' + (selectedSingleReg ? selectedSingleReg.id : '')" target="_blank" class="p-4 rounded-2xl border border-white/[0.08] bg-[#0C111D] hover:bg-white/[0.04] hover:border-emerald-500/50 transition group flex items-center justify-between cursor-pointer">
                        <div class="flex items-center gap-3.5">
                            <div class="w-10 h-10 rounded-xl bg-emerald-500/15 text-emerald-400 border border-emerald-500/30 flex items-center justify-center font-bold shrink-0 shadow-xs">
                                <i data-lucide="file-check" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h4 class="font-black text-white text-xs sm:text-sm group-hover:text-emerald-400 transition">🪪 Bukti Pendaftaran & Kartu Peserta</h4>
                                <p class="text-[11px] text-slate-400 mt-0.5">Berisi No. Peserta resmi, biodata lengkap atlet, dan cabang lomba.</p>
                            </div>
                        </div>
                        <i data-lucide="printer" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                    </a>

                    <!-- 3. Kwitansi / Invoice Pembayaran -->
                    <a :href="'{{ url('dokumen/kwitansi') }}/' + (selectedSingleReg ? selectedSingleReg.id : '')" target="_blank" class="p-4 rounded-2xl border border-white/[0.08] bg-[#0C111D] hover:bg-white/[0.04] hover:border-amber-500/50 transition group flex items-center justify-between cursor-pointer">
                        <div class="flex items-center gap-3.5">
                            <div class="w-10 h-10 rounded-xl bg-amber-500/15 text-amber-400 border border-amber-500/30 flex items-center justify-center font-bold shrink-0 shadow-xs">
                                <i data-lucide="receipt" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <h4 class="font-black text-white text-xs sm:text-sm group-hover:text-amber-400 transition">🧾 Kwitansi / Invoice Pembayaran</h4>
                                <p class="text-[11px] text-slate-400 mt-0.5">Lembar resmi bukti pelunasan biaya registrasi panitia.</p>
                            </div>
                        </div>
                        <i data-lucide="printer" class="w-4 h-4 text-amber-400 shrink-0"></i>
                    </a>

                </div>

                <div class="pt-3 border-t border-white/[0.08] flex items-center justify-end">
                    <button type="button" @click="singlePrintModal = false" class="px-5 py-2 rounded-xl bg-white/[0.06] hover:bg-white/[0.1] text-slate-300 text-xs font-bold border border-white/[0.08] transition cursor-pointer">
                        Tutup
                    </button>
                </div>

            </div>
        </div>
    </div>

    <!-- ==================== CREATE PARTICIPANT MODAL (TAMBAH PESERTA MANUAL) ==================== -->
    <div x-show="createModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 overflow-y-auto" role="dialog" aria-modal="true">
        <div x-show="createModal" x-transition.opacity @click="createModal = false" class="fixed inset-0 bg-black/80 backdrop-blur-md"></div>

        <div x-show="createModal" x-transition.scale.95 @click.stop class="relative z-10 w-full max-w-2xl bg-[#161F30] border border-white/[0.12] rounded-3xl p-6 sm:p-8 space-y-6 shadow-2xl my-auto text-left">
                
                <!-- Modal Header -->
                <div class="flex items-center justify-between border-b border-white/[0.08] pb-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-mono font-bold text-emerald-400 px-2.5 py-1 bg-emerald-500/15 border border-emerald-500/30 rounded-lg">PENDAFTARAN MANUAL</span>
                            <span class="text-xs font-bold text-slate-400">Admin & PIC Lomba</span>
                        </div>
                        <h3 class="text-lg font-black text-white mt-2">Daftarkan Peserta Baru (Offline / Sekretariat)</h3>
                    </div>
                    <button @click="createModal = false" type="button" class="text-slate-400 hover:text-white transition cursor-pointer">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form action="{{ route('pic.store.participant') }}" method="POST" enctype="multipart/form-data" class="space-y-4 max-h-[70vh] overflow-y-auto pr-1">
                    @csrf

                    <!-- Cabang Lomba Selection -->
                    <div class="p-4 rounded-2xl bg-[#0C111D] border border-white/[0.08] space-y-3">
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider">
                            Pilih Cabang Lomba <span class="text-rose-400">*</span>
                        </label>
                        <select name="competition_id" x-model="createCompId" required class="w-full px-3.5 py-2.5 rounded-xl bg-[#161F30] border border-white/[0.12] text-xs font-bold text-white focus:border-[#7A5AF8] outline-none">
                            <option value="">-- Pilih Cabang Lomba --</option>
                            @foreach($competitions as $comp)
                                <option value="{{ $comp->id }}">{{ $comp->name }} ({{ $comp->code }})</option>
                            @endforeach
                        </select>

                        <!-- Info Biaya Pendaftaran Resmi (Dynamic Fee Display) -->
                        <div x-show="createCompId" class="p-3 rounded-xl bg-gradient-to-r from-[#7A5AF8]/15 via-[#4E6EFF]/15 to-transparent border border-[#7A5AF8]/30 flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-lg bg-[#7A5AF8]/20 text-[#A594FD] flex items-center justify-center font-bold">
                                    <i data-lucide="wallet" class="w-3.5 h-3.5"></i>
                                </div>
                                <div>
                                    <span class="text-[9px] uppercase font-bold text-slate-400 block tracking-wider">Tarif Pendaftaran Resmi</span>
                                    <span class="text-sm font-black text-emerald-400 font-mono" x-text="'Rp ' + (new Intl.NumberFormat('id-ID').format(currentManualFee))"></span>
                                </div>
                            </div>
                            <span class="px-2 py-0.5 rounded-md text-[9px] font-black bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 uppercase tracking-wider">
                                Wajib Lunas
                            </span>
                        </div>

                        <!-- Sektor Bulu Tangkis (BLT) -->
                        <template x-if="createCompCode === 'BLT'">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2 border-t border-white/[0.06]">
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Sektor Pertandingan <span class="text-rose-400">*</span></label>
                                    <select name="match_type" x-model="createMatchType" @change="if(createMatchType.includes('Putri') || createMatchType.includes('(PI)')) createGender = 'P'; else if(createMatchType.includes('Putra') || createMatchType.includes('(PA)')) createGender = 'L';" required class="w-full px-3 py-2 rounded-xl bg-[#161F30] border border-white/[0.1] text-xs font-bold text-white">
                                        <option value="">-- Pilih Sektor --</option>
                                        <option value="Tunggal Putra (PA)">Tunggal Putra (PA)</option>
                                        <option value="Tunggal Putri (PI)">Tunggal Putri (PI)</option>
                                        <option value="Ganda Putra (PA)">Ganda Putra (PA)</option>
                                        <option value="Ganda Putri (PI)">Ganda Putri (PI)</option>
                                    </select>
                                </div>
                                <div x-show="!createMatchType.includes('Ganda')">
                                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Kategori Jenjang Kelas <span class="text-rose-400">*</span></label>
                                    <select name="target_class" x-model="createTargetClass" :disabled="createMatchType.includes('Ganda')" :required="!createMatchType.includes('Ganda')" class="w-full px-3 py-2 rounded-xl bg-[#161F30] border border-white/[0.1] text-xs font-bold text-white">
                                        <option value="">-- Pilih Kategori --</option>
                                        <option value="Kategori A (Kelas 1 - 2)">Kategori A (Kelas 1–2 SD/MI)</option>
                                        <option value="Kategori B (Kelas 3 - 4)">Kategori B (Kelas 3–4 SD/MI)</option>
                                        <option value="Kategori C (Kelas 5 - 6)">Kategori C (Kelas 5–6 SD/MI)</option>
                                    </select>
                                </div>
                            </div>
                        </template>

                        <!-- Sektor Tenis Meja (TMJ) -->
                        <template x-if="createCompCode === 'TMJ'">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2 border-t border-white/[0.06]">
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Sektor Pertandingan <span class="text-rose-400">*</span></label>
                                    <select name="match_type" x-model="createMatchType" @change="if(createMatchType.includes('Putri') || createMatchType.includes('(PI)')) createGender = 'P'; else if(createMatchType.includes('Putra') || createMatchType.includes('(PA)')) createGender = 'L';" required class="w-full px-3 py-2 rounded-xl bg-[#161F30] border border-white/[0.1] text-xs font-bold text-white">
                                        <option value="">-- Pilih Sektor --</option>
                                        <option value="Tunggal Putra (PA)">Tunggal Putra (PA)</option>
                                        <option value="Tunggal Putri (PI)">Tunggal Putri (PI)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Kategori Jenjang Kelas <span class="text-rose-400">*</span></label>
                                    <select name="target_class" x-model="createTargetClass" required class="w-full px-3 py-2 rounded-xl bg-[#161F30] border border-white/[0.1] text-xs font-bold text-white">
                                        <option value="">-- Pilih Kategori --</option>
                                        <option value="Kategori A (Kelas 1 - 3)">Kategori A (Kelas 1–3 SD/MI)</option>
                                        <option value="Kategori B (Kelas 4 - 6)">Kategori B (Kelas 4–6 SD/MI)</option>
                                    </select>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Data Peserta Utama -->
                    <div class="p-4 rounded-2xl bg-[#0C111D] border border-white/[0.08] space-y-3">
                        <span class="text-[11px] font-black uppercase tracking-wider text-emerald-400 flex items-center gap-1.5">
                            <i data-lucide="user" class="w-4 h-4"></i>
                            <span>Biodata Peserta Utama</span>
                        </span>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="sm:col-span-2">
                                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Nama Lengkap Peserta <span class="text-rose-400">*</span></label>
                                <input type="text" name="full_name" required placeholder="Contoh: Muhammad Rayhan" class="w-full px-3 py-2 rounded-xl bg-[#161F30] border border-white/[0.1] text-xs font-bold text-white focus:border-[#7A5AF8] outline-none">
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">NISN (10 Digit)</label>
                                <input type="text" name="nisn" maxlength="20" placeholder="Opsional / 10 digit angka" class="w-full px-3 py-2 rounded-xl bg-[#161F30] border border-white/[0.1] text-xs font-mono font-bold text-white focus:border-[#7A5AF8] outline-none">
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Jenis Kelamin <span class="text-rose-400">*</span></label>
                                <select name="gender" x-model="createGender" required class="w-full px-3 py-2 rounded-xl bg-[#161F30] border border-white/[0.1] text-xs font-bold text-white">
                                    <option value="L">Laki-laki (Putra / PA)</option>
                                    <option value="P">Perempuan (Putri / PI)</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Asal Sekolah / Madrasah <span class="text-rose-400">*</span></label>
                                <input type="text" name="institution_name" required placeholder="Contoh: MI Al-Ikhlas" class="w-full px-3 py-2 rounded-xl bg-[#161F30] border border-white/[0.1] text-xs font-bold text-white focus:border-[#7A5AF8] outline-none">
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">No. WhatsApp / HP</label>
                                <input type="text" name="phone" placeholder="08xxxxxxxxxx" class="w-full px-3 py-2 rounded-xl bg-[#161F30] border border-white/[0.1] text-xs font-bold text-white focus:border-[#7A5AF8] outline-none">
                            </div>
                        </div>
                    </div>

                    <!-- Khusus Ganda Bulu Tangkis (Pemain 2) -->
                    <div x-show="createCompCode === 'BLT' && createMatchType.includes('Ganda')" class="p-4 rounded-2xl bg-[#0C111D] border border-blue-500/20 space-y-3">
                        <span class="text-[11px] font-black uppercase tracking-wider text-blue-400 flex items-center gap-1.5">
                            <i data-lucide="users" class="w-4 h-4"></i>
                            <span>Data Pasangan Ganda (Pemain 2)</span>
                        </span>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="sm:col-span-2">
                                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Nama Pemain 2 <span class="text-rose-400">*</span></label>
                                <input type="text" name="member2_name" :disabled="!(createCompCode === 'BLT' && createMatchType.includes('Ganda'))" :required="createCompCode === 'BLT' && createMatchType.includes('Ganda')" placeholder="Nama lengkap pasangan ganda" class="w-full px-3 py-2 rounded-xl bg-[#161F30] border border-white/[0.1] text-xs font-bold text-white focus:border-[#7A5AF8] outline-none">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">NISN Pemain 2</label>
                                <input type="text" name="member2_nisn" :disabled="!(createCompCode === 'BLT' && createMatchType.includes('Ganda'))" placeholder="Opsional" class="w-full px-3 py-2 rounded-xl bg-[#161F30] border border-white/[0.1] text-xs font-mono font-bold text-white">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Asal Sekolah Pemain 2</label>
                                <input type="text" name="member2_school" :disabled="!(createCompCode === 'BLT' && createMatchType.includes('Ganda'))" placeholder="Kosongkan jika sama dengan pemain 1" class="w-full px-3 py-2 rounded-xl bg-[#161F30] border border-white/[0.1] text-xs font-bold text-white">
                            </div>
                        </div>
                    </div>

                    <!-- Validasi Pembayaran & Upload Bukti -->
                    <div class="p-4 rounded-2xl bg-[#0C111D] border border-white/[0.08] space-y-3.5">
                        <span class="text-[11px] font-black uppercase tracking-wider text-amber-400 flex items-center gap-1.5">
                            <i data-lucide="receipt" class="w-4 h-4"></i>
                            <span>Validasi Pembayaran & Bukti Transaksi</span>
                        </span>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <!-- Metode Pembayaran -->
                            <div>
                                <label class="block text-[10px] font-bold text-slate-300 uppercase tracking-wider mb-1">
                                    Metode Pembayaran <span class="text-rose-400">*</span>
                                </label>
                                <select name="payment_method" x-model="createPaymentMethod" required class="w-full px-3 py-2 rounded-xl bg-[#161F30] border border-white/[0.12] text-xs font-bold text-white focus:border-[#7A5AF8] outline-none">
                                    <option value="tunai">💵 Tunai (Diterima di Meja PIC/Sekretariat)</option>
                                    <option value="transfer">🏦 Transfer Bank (Rekening Panitia)</option>
                                </select>
                            </div>

                            <!-- Status Langsung -->
                            <div>
                                <label class="block text-[10px] font-bold text-slate-300 uppercase tracking-wider mb-1">
                                    Status Pendaftaran <span class="text-rose-400">*</span>
                                </label>
                                <select name="status" required class="w-full px-3 py-2 rounded-xl bg-[#161F30] border border-white/[0.12] text-xs font-bold text-white focus:border-[#7A5AF8] outline-none">
                                    <option value="verified" selected>✅ Terverifikasi (Lunas & Sah)</option>
                                    <option value="pending">⏳ Pending (Menunggu Verifikasi)</option>
                                </select>
                            </div>

                            <!-- Unggah Bukti Pembayaran (Opsional untuk PIC) -->
                            <div class="sm:col-span-2 pt-1">
                                <label class="block text-[10px] font-bold text-slate-300 uppercase tracking-wider mb-1">
                                    Unggah Bukti Bayar / Slip / Kwitansi Fisik <span class="text-slate-500 font-normal">(Opsional untuk entri meja PIC)</span>
                                </label>
                                <input type="file" 
                                       name="payment_proof" 
                                       id="manual_payment_proof" 
                                       accept="image/jpeg,image/png,image/webp,application/pdf" 
                                       class="w-full px-3 py-2 rounded-xl bg-[#161F30] border border-white/[0.12] text-xs text-slate-300 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-[#7A5AF8] file:text-white hover:file:bg-[#6842f5] file:cursor-pointer">
                                <p class="text-[10px] text-slate-400 mt-1">
                                    Opsional jika diterima tunai langsung di meja PIC / Sekretariat. Format JPG, PNG, PDF max 5MB.
                                </p>
                            </div>

                            <!-- Catatan Tambahan / Kwitansi -->
                            <div class="sm:col-span-2">
                                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">
                                    Catatan / No. Kwitansi Fisik / Ref Bank
                                </label>
                                <input type="text" name="verification_notes" placeholder="Misal: Diterima tunai meja 1 / Ref BCA an Budi..." class="w-full px-3 py-2 rounded-xl bg-[#161F30] border border-white/[0.1] text-xs font-bold text-white focus:border-[#7A5AF8] outline-none">
                            </div>
                        </div>

                        <!-- Opsi Abaikan Kuota (Dispensasi Kuota Panitia) -->
                        <div class="pt-2 border-t border-white/[0.06] flex items-center gap-2">
                            <input type="checkbox" name="ignore_quota" id="ignore_quota" value="1" class="w-4 h-4 rounded text-emerald-600 bg-[#161F30] border-white/[0.2] focus:ring-emerald-500 cursor-pointer">
                            <label for="ignore_quota" class="text-xs text-amber-300 font-semibold cursor-pointer select-none">
                                Abaikan batas kuota (Gunakan sebagai kuota dispensasi khusus panitia jika kuota penuh)
                            </label>
                        </div>
                    </div>

                    <!-- Modal Actions -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-white/[0.08]">
                        <button type="button" @click="createModal = false" class="px-5 py-2.5 rounded-xl bg-white/[0.06] hover:bg-white/[0.12] text-slate-300 text-xs font-bold transition cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-black text-xs shadow-lg shadow-emerald-500/25 transition cursor-pointer flex items-center gap-2">
                            <i data-lucide="check-circle" class="w-4 h-4"></i>
                            <span>Simpan & Daftarkan Peserta</span>
                        </button>
                    </div>
                </form>

            </div>
        </div>

</div>
@endsection

