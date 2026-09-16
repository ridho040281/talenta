@extends('layouts.admin')

@section('title', 'Sertifikat & Piagam Penghargaan')
@section('page_title', 'Sertifikat & Piagam Penghargaan')

@section('content')
<div class="space-y-6" x-data="{
    activeType: '{{ $type }}',
    selectedCompId: '{{ $selectedComp->id ?? '' }}',
    searchQuery: '',

    changeCompetition(id) {
        window.location.href = '{{ route('admin.certificates.index') }}?competition_id=' + id + '&type=' + this.activeType;
    },

    changeType(t) {
        window.location.href = '{{ route('admin.certificates.index') }}?competition_id=' + this.selectedCompId + '&type=' + t;
    },

    printBulk(sector = null) {
        const url = new URL('{{ route('admin.certificates.print.bulk') }}', window.location.origin);
        url.searchParams.set('competition_id', this.selectedCompId);
        url.searchParams.set('type', this.activeType);
        if (sector) {
            url.searchParams.set('sector', sector);
        }
        window.open(url.toString(), '_blank');
    },

    printSingle(regId, rank = '', seq = '001', name = '', school = '') {
        const url = new URL('{{ route('admin.certificates.print') }}', window.location.origin);
        url.searchParams.set('type', this.activeType);
        url.searchParams.set('competition_id', this.selectedCompId);
        if (regId) url.searchParams.set('registration_id', regId);
        if (rank) url.searchParams.set('rank', rank);
        if (seq) url.searchParams.set('cert_seq', seq);
        if (name) url.searchParams.set('name', name);
        if (school) url.searchParams.set('school', school);
        window.open(url.toString(), '_blank');
    }
}">

    <!-- Alert Notifikasi -->
    @if(session('success'))
    <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-semibold flex items-center justify-between">
        <div class="flex items-center gap-3">
            <i data-lucide="check-circle" class="w-5 h-5 text-emerald-400"></i>
            <span>{{ session('success') }}</span>
        </div>
        <button type="button" @click="$el.parentElement.remove()" class="text-emerald-400/60 hover:text-emerald-400">
            <i data-lucide="x" class="w-4 h-4"></i>
        </button>
    </div>
    @endif

    @if(session('error'))
    <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-xs font-semibold flex items-center justify-between">
        <div class="flex items-center gap-3">
            <i data-lucide="alert-triangle" class="w-5 h-5 text-rose-400"></i>
            <span>{{ session('error') }}</span>
        </div>
        <button type="button" @click="$el.parentElement.remove()" class="text-rose-400/60 hover:text-rose-400">
            <i data-lucide="x" class="w-4 h-4"></i>
        </button>
    </div>
    @endif

    <!-- Top Control Bar (Pilih Cabang & Quick Actions) -->
    <div class="ai-card p-5 rounded-3xl border border-white/[0.08] shadow-xl flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-purple-500/20 to-blue-500/20 border border-purple-500/30 flex items-center justify-center text-white shrink-0 shadow-inner">
                <i data-lucide="award" class="w-6 h-6 text-purple-300"></i>
            </div>
            <div>
                <h2 class="text-base sm:text-lg font-black text-white flex items-center gap-2">
                    <span>Sertifikat & Piagam Penghargaan</span>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-purple-500/20 text-purple-300 border border-purple-500/30">
                        Standar A4 Landscape
                    </span>
                </h2>
                <p class="text-xs text-slate-400">Penerbitan piagam juara, sertifikat peserta, guru pembimbing, dan dewan juri berbasis template blangko.</p>
            </div>
        </div>

        <!-- Filter Cabang Dropdown & Rilis Toggle -->
        <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
            <div class="flex items-center gap-2 flex-1 md:flex-initial">
                <label class="text-xs font-bold text-slate-400 whitespace-nowrap">Cabang:</label>
                <select x-model="selectedCompId" @change="changeCompetition($event.target.value)" class="w-full md:w-60 px-3.5 py-2.5 rounded-xl bg-[#0C111D] border border-white/[0.12] text-xs font-bold text-white outline-none focus:border-[#7A5AF8] cursor-pointer">
                    @foreach($competitions as $c)
                        <option value="{{ $c->id }}" {{ $selectedComp && $selectedComp->id === $c->id ? 'selected' : '' }}>
                            {{ $c->name }} ({{ $c->code }})
                        </option>
                    @endforeach
                </select>
            </div>

            @if($selectedComp)
            <!-- Form Toggle Rilis untuk Peserta -->
            <form action="{{ route('admin.certificates.toggle-release') }}" method="POST">
                @csrf
                <input type="hidden" name="competition_id" value="{{ $selectedComp->id }}">
                <button type="submit" class="px-3.5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 {{ $isReleased ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 hover:bg-emerald-500/30' : 'bg-slate-800 text-slate-300 border border-white/[0.1] hover:bg-slate-700' }}" title="{{ $isReleased ? 'Klik untuk menutup akses download peserta' : 'Klik untuk mengaktifkan tombol download pada akun peserta' }}">
                    <i data-lucide="{{ $isReleased ? 'unlock' : 'lock' }}" class="w-3.5 h-3.5"></i>
                    <span>{{ $isReleased ? 'Rilis Peserta: AKTIF' : 'Rilis Peserta: DITUTUP' }}</span>
                </button>
            </form>
            @endif
        </div>
    </div>

    <!-- Segmented Navigation Tabs (Kategori Sertifikat) -->
    <div class="flex flex-wrap items-center gap-2 border-b border-white/[0.08] pb-2">
        <button type="button" @click="changeType('juara')" :class="activeType === 'juara' ? 'bg-gradient-to-r from-[#7A5AF8] to-[#4E6EFF] text-white shadow-lg shadow-[#7A5AF8]/25 font-black' : 'text-slate-400 hover:text-white hover:bg-white/[0.04] font-bold'" class="px-4 py-2.5 rounded-2xl text-xs transition flex items-center gap-2 cursor-pointer">
            <i data-lucide="trophy" class="w-4 h-4 text-amber-400"></i>
            <span>1. Piagam Kejuaraan (Juara 1, 2, 3 & Harapan)</span>
        </button>
        <button type="button" @click="changeType('peserta')" :class="activeType === 'peserta' ? 'bg-gradient-to-r from-[#7A5AF8] to-[#4E6EFF] text-white shadow-lg shadow-[#7A5AF8]/25 font-black' : 'text-slate-400 hover:text-white hover:bg-white/[0.04] font-bold'" class="px-4 py-2.5 rounded-2xl text-xs transition flex items-center gap-2 cursor-pointer">
            <i data-lucide="graduation-cap" class="w-4 h-4 text-blue-400"></i>
            <span>2. Sertifikat Peserta</span>
        </button>
        <button type="button" @click="changeType('pembimbing')" :class="activeType === 'pembimbing' ? 'bg-gradient-to-r from-[#7A5AF8] to-[#4E6EFF] text-white shadow-lg shadow-[#7A5AF8]/25 font-black' : 'text-slate-400 hover:text-white hover:bg-white/[0.04] font-bold'" class="px-4 py-2.5 rounded-2xl text-xs transition flex items-center gap-2 cursor-pointer">
            <i data-lucide="user-check" class="w-4 h-4 text-emerald-400"></i>
            <span>3. Sertifikat Pembimbing / Pendamping</span>
        </button>
        <button type="button" @click="changeType('juri')" :class="activeType === 'juri' ? 'bg-gradient-to-r from-[#7A5AF8] to-[#4E6EFF] text-white shadow-lg shadow-[#7A5AF8]/25 font-black' : 'text-slate-400 hover:text-white hover:bg-white/[0.04] font-bold'" class="px-4 py-2.5 rounded-2xl text-xs transition flex items-center gap-2 cursor-pointer">
            <i data-lucide="scale" class="w-4 h-4 text-purple-400"></i>
            <span>4. Sertifikat Juri / Wasit</span>
        </button>
    </div>

    <!-- Active Template Card & Customizer Banner -->
    <div class="ai-card p-5 rounded-3xl border border-white/[0.08] shadow-lg flex flex-col md:flex-row items-start md:items-center justify-between gap-4 bg-gradient-to-r from-[#0C111D] via-[#111827] to-[#0C111D]">
        <div class="flex items-center gap-4">
            <!-- Template Thumbnail / Status Icon -->
            <div class="w-20 h-14 rounded-xl border border-white/[0.1] bg-slate-950 overflow-hidden flex items-center justify-center shrink-0 relative shadow-md">
                @if($template && $template->background_path)
                    <img src="{{ asset('storage/' . $template->background_path) }}" alt="Template" class="w-full h-full object-cover">
                    <span class="absolute bottom-0 right-0 px-1 bg-emerald-500 text-[8px] font-black text-white uppercase rounded-tl">Kustom</span>
                @else
                    <div class="text-center p-1">
                        <i data-lucide="image" class="w-5 h-5 mx-auto text-slate-500"></i>
                        <span class="text-[8px] text-slate-400 block font-mono">Bawaan CSS</span>
                    </div>
                @endif
            </div>

            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <h3 class="text-sm font-bold text-white">{{ $template->name ?? 'Template Standar' }}</h3>
                    <span class="text-[10px] px-2 py-0.5 rounded-full {{ $template && $template->background_path ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'bg-amber-500/20 text-amber-300 border border-amber-500/30' }}">
                        {{ $template && $template->background_path ? 'Blangko JPG/PNG Aktif' : 'Desain Bawaan Sistem' }}
                    </span>
                </div>
                <p class="text-xs text-slate-400">
                    Format No: <code class="text-[#A594FD] bg-white/[0.05] px-1.5 py-0.5 rounded text-[11px]">{{ $template->number_format ?? '[NO]/TALENTA/MTsN1-BLT/[MONTH]/[YEAR]' }}</code>
                    @if($template && $template->competition_id)
                        • Khusus Cabang: <span class="text-white font-semibold">{{ $template->competition?->name }}</span>
                    @else
                        • Berlaku Umum Semua Cabang
                    @endif
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2.5 w-full md:w-auto">
            <!-- Button Atur Desain & Posisi Teks -->
            <a href="{{ route('admin.certificates.designer', ['type' => $type, 'competition_id' => $selectedComp?->id]) }}" class="flex-1 md:flex-initial px-4 py-2.5 rounded-xl bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 text-white font-bold text-xs shadow-lg shadow-purple-500/20 transition flex items-center justify-center gap-2">
                <i data-lucide="palette" class="w-4 h-4"></i>
                <span>Atur Desain & Posisi Teks</span>
            </a>

            <!-- Button Cetak Massal -->
            <button type="button" @click="printBulk()" class="flex-1 md:flex-initial px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs border border-white/[0.1] transition flex items-center justify-center gap-2">
                <i data-lucide="printer" class="w-4 h-4 text-emerald-400"></i>
                <span>Cetak Massal Semua</span>
            </button>
        </div>
    </div>

    @if($selectedComp)

        <!-- ==================== TAB 1: PIAGAM KEJUARAAN (JUARA) ==================== -->
        @if($type === 'juara')
        <div class="space-y-6">
            @forelse($sectorsData as $secKey => $sector)
            <div class="ai-card p-5 sm:p-6 rounded-3xl border border-white/[0.08] shadow-lg space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-white/[0.08] gap-3">
                    <div>
                        <h4 class="text-sm font-black text-white flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-400 animate-pulse"></span>
                            <span>{{ $sector['definition']['title'] ?? 'Kategori Lomba' }}</span>
                        </h4>
                        <p class="text-xs text-slate-400 mt-0.5">Penetapan Juara 1, 2, 3 dan Harapan sesuai Berita Acara & skor terverifikasi</p>
                    </div>

                    @if(!empty($sector['winners']))
                    <button type="button" @click="printBulk('{{ $secKey }}')" class="px-3.5 py-2 rounded-xl bg-amber-500/20 text-amber-300 hover:bg-amber-500/30 border border-amber-500/30 text-xs font-bold transition flex items-center gap-2 self-start sm:self-auto">
                        <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                        <span>Cetak Kategori Ini</span>
                    </button>
                    @endif
                </div>

                @if(empty($sector['winners']))
                    <div class="py-8 text-center bg-white/[0.01] rounded-2xl border border-dashed border-white/[0.06]">
                        <i data-lucide="award" class="w-10 h-10 mx-auto text-slate-600 mb-2"></i>
                        <p class="text-xs font-bold text-slate-400">Belum ada pemenang yang tercatat dengan skor terkunci pada kategori ini.</p>
                        <p class="text-[11px] text-slate-500 mt-1">Pastikan penjurian / pertandingan telah selesai dan nilai telah dikunci dewan juri.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs text-slate-300">
                            <thead>
                                <tr class="border-b border-white/[0.06] text-[11px] uppercase tracking-wider text-slate-400 font-bold">
                                    <th class="py-3 px-4">Peringkat</th>
                                    <th class="py-3 px-4">No. Peserta</th>
                                    <th class="py-3 px-4">Nama Juara</th>
                                    <th class="py-3 px-4">Asal Sekolah / Madrasah</th>
                                    <th class="py-3 px-4 text-center">Nilai Akhir</th>
                                    <th class="py-3 px-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/[0.04]">
                                @foreach($sector['winners'] as $idx => $item)
                                @php
                                    $w = $item['winner'];
                                    $reg = $w['registration'];
                                    $tierBadge = match($item['tier_label']) {
                                        'Juara 1' => 'bg-amber-500/20 text-amber-300 border-amber-500/40',
                                        'Juara 2' => 'bg-slate-300/20 text-slate-200 border-slate-300/40',
                                        'Juara 3' => 'bg-amber-700/20 text-amber-400 border-amber-700/40',
                                        default => 'bg-blue-500/20 text-blue-300 border-blue-500/40',
                                    };
                                @endphp
                                <tr class="hover:bg-white/[0.02] transition">
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        <span class="px-2.5 py-1 rounded-full text-[11px] font-black border {{ $tierBadge }}">
                                            {{ $item['tier_label'] }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 font-mono font-bold text-slate-400">
                                        {{ $w['participant_number'] }}
                                    </td>
                                    <td class="py-3.5 px-4 font-black text-white">
                                        {{ $w['display_name'] }}
                                    </td>
                                    <td class="py-3.5 px-4 text-slate-300">
                                        {{ $w['institution_name'] }}
                                    </td>
                                    <td class="py-3.5 px-4 text-center font-mono font-bold text-amber-400">
                                        {{ $w['score'] }}
                                    </td>
                                    <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                        <button type="button" @click="printSingle('{{ $reg?->id }}', '{{ $item['tier_label'] }}', '{{ str_pad($idx+1, 3, '0', STR_PAD_LEFT) }}')" class="px-3 py-1.5 rounded-lg bg-white/[0.06] hover:bg-[#7A5AF8] text-white text-[11px] font-bold transition inline-flex items-center gap-1.5 shadow-sm">
                                            <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                                            <span>Cetak Piagam</span>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
            @empty
            <div class="ai-card p-12 text-center rounded-3xl border border-white/[0.08]">
                <i data-lucide="folder-x" class="w-12 h-12 mx-auto text-slate-600 mb-3"></i>
                <h4 class="text-sm font-bold text-white">Kategori cabang lomba belum dikonfigurasi.</h4>
            </div>
            @endforelse
        </div>
        @endif

        <!-- ==================== TAB 2, 3, 4: PESERTA, PEMBIMBING, JURI ==================== -->
        @if(in_array($type, ['peserta', 'pembimbing', 'juri']))
        <div class="ai-card p-5 sm:p-6 rounded-3xl border border-white/[0.08] shadow-lg space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-white/[0.08] gap-3">
                <div>
                    <h4 class="text-sm font-black text-white flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-blue-400"></span>
                        <span>Daftar Penerima Sertifikat {{ ucfirst($type) }}</span>
                        <span class="text-xs px-2 py-0.5 rounded-full bg-white/[0.08] text-slate-300 font-mono">
                            {{ $recipients->count() }} Orang
                        </span>
                    </h4>
                    <p class="text-xs text-slate-400 mt-0.5">Seluruh data yang terverifikasi pada cabang {{ $selectedComp->name }}</p>
                </div>

                <!-- Live Search Box -->
                <div class="w-full sm:w-64">
                    <input type="text" x-model="searchQuery" placeholder="Cari nama / sekolah..." class="w-full px-3.5 py-2 rounded-xl bg-[#0C111D] border border-white/[0.1] text-xs text-white placeholder-slate-500 outline-none focus:border-[#7A5AF8]">
                </div>
            </div>

            @if($recipients->isEmpty())
                <div class="py-12 text-center bg-white/[0.01] rounded-2xl border border-dashed border-white/[0.06]">
                    <i data-lucide="users" class="w-10 h-10 mx-auto text-slate-600 mb-2"></i>
                    <p class="text-xs font-bold text-slate-400">Belum ada data penerima untuk kategori {{ $type }} pada cabang ini.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-300">
                        <thead>
                            <tr class="border-b border-white/[0.06] text-[11px] uppercase tracking-wider text-slate-400 font-bold">
                                <th class="py-3 px-4">No</th>
                                <th class="py-3 px-4">No. Peserta</th>
                                <th class="py-3 px-4">Nama Penerima</th>
                                <th class="py-3 px-4">Asal Sekolah / Lembaga</th>
                                <th class="py-3 px-4">Predikat / Status</th>
                                <th class="py-3 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/[0.04]">
                            @foreach($recipients as $idx => $r)
                            <tr class="hover:bg-white/[0.02] transition" x-show="!searchQuery || '{{ strtolower(addslashes($r['name'] . ' ' . $r['institution'])) }}'.includes(searchQuery.toLowerCase())">
                                <td class="py-3.5 px-4 font-mono text-slate-500">
                                    {{ $idx + 1 }}
                                </td>
                                <td class="py-3.5 px-4 font-mono font-bold text-slate-400">
                                    {{ $r['participant_number'] }}
                                </td>
                                <td class="py-3.5 px-4 font-black text-white">
                                    {{ $r['name'] }}
                                </td>
                                <td class="py-3.5 px-4 text-slate-300">
                                    {{ $r['institution'] }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-500/20 text-purple-300 border border-purple-500/30">
                                        {{ $r['role_label'] }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                    <button type="button" @click="printSingle('{{ $r['id'] ?? '' }}', '{{ $r['role_label'] }}', '{{ str_pad($idx+1, 3, '0', STR_PAD_LEFT) }}', '{{ addslashes($r['name']) }}', '{{ addslashes($r['institution']) }}')" class="px-3 py-1.5 rounded-lg bg-white/[0.06] hover:bg-[#7A5AF8] text-white text-[11px] font-bold transition inline-flex items-center gap-1.5 shadow-sm">
                                        <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                                        <span>Cetak Sertifikat</span>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
        @endif

    @endif
</div>
@endsection
