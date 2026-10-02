@extends('layouts.admin')

@section('title', 'Berita Acara Pemenang & Template')
@section('page_title', 'Berita Acara Pemenang & Penjurian')

@section('content')
<div class="space-y-6" x-data="{
    activeTab: '{{ $activeTab }}',
    selectedCompId: '{{ $selectedComp->id ?? '' }}',
    tierFormat: '{{ $tierFormat }}',
    eventDate: '{{ date('Y-m-d') }}',
    eventDay: '{{ $dateSpelled['day_name'] }}',
    eventTime: '08.00',
    judge1: '{{ addslashes($judgesList[0] ?? '') }}',
    judge2: '{{ addslashes($judgesList[1] ?? '') }}',
    judge3: '{{ addslashes($judgesList[2] ?? '') }}',
    showImportModal: false,
    importLoading: false,
    
    changeCompetition(id) {
        window.location.href = '{{ route('admin.berita-acara.index') }}?competition_id=' + id + '&tab=' + this.activeTab + '&tier_format=' + this.tierFormat;
    },

    setTierFormat(format) {
        this.tierFormat = format;
        window.location.href = '{{ route('admin.berita-acara.index') }}?competition_id=' + this.selectedCompId + '&tab=' + this.activeTab + '&tier_format=' + format;
    },
    
    printLive(sector = null) {
        const url = new URL('{{ route('admin.berita-acara.print') }}', window.location.origin);
        url.searchParams.set('competition_id', this.selectedCompId);
        url.searchParams.set('type', 'live');
        url.searchParams.set('tier_format', this.tierFormat);
        url.searchParams.set('date', this.eventDate);
        url.searchParams.set('day', this.eventDay);
        url.searchParams.set('time', this.eventTime);
        url.searchParams.set('judge1', this.judge1);
        url.searchParams.set('judge2', this.judge2);
        url.searchParams.set('judge3', this.judge3);
        if (sector) {
            url.searchParams.set('sector', sector);
        }
        window.open(url.toString(), '_blank');
    },

    printBlank(sector = null) {
        const url = new URL('{{ route('admin.berita-acara.print') }}', window.location.origin);
        url.searchParams.set('competition_id', this.selectedCompId);
        url.searchParams.set('type', 'blank');
        url.searchParams.set('tier_format', this.tierFormat);
        if (sector) {
            url.searchParams.set('sector', sector);
        }
        window.open(url.toString(), '_blank');
    }
}">

    <!-- Alert Notifikasi Feedback -->
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
            <i data-lucide="alert-circle" class="w-5 h-5 text-rose-400"></i>
            <span>{{ session('error') }}</span>
        </div>
        <button type="button" @click="$el.parentElement.remove()" class="text-rose-400/60 hover:text-rose-400">
            <i data-lucide="x" class="w-4 h-4"></i>
        </button>
    </div>
    @endif

    <!-- Top Control Bar (Pilih Cabang & Quick Info) -->
    <div class="ai-card p-4 sm:p-5 rounded-3xl border border-white/[0.08] shadow-xl flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-[#7A5AF8]/20 to-[#4E6EFF]/20 border border-[#7A5AF8]/30 flex items-center justify-center text-white shrink-0 shadow-inner">
                <i data-lucide="file-text" class="w-6 h-6 text-[#A594FD]"></i>
            </div>
            <div>
                <h2 class="text-base sm:text-lg font-black text-white flex items-center gap-2">
                    <span>Berita Acara {{ $isSports ? 'Pertandingan' : 'Penjurian' }}</span>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $isSports ? 'bg-amber-500/20 text-amber-300 border border-amber-500/30' : 'bg-purple-500/20 text-purple-300 border border-purple-500/30' }}">
                        {{ $isSports ? '🏃 Olahraga (Dewan Wasit)' : '🎨 Seni / Akademik (Dewan Juri)' }}
                    </span>
                </h2>
                <p class="text-xs text-slate-400">Penerbitan dokumen resmi hasil pemenang lomba format standar kertas A4</p>
            </div>
        </div>

        <!-- Filter Cabang & Format Juara -->
        <div class="flex flex-wrap items-center gap-3 w-full lg:w-auto">
            <!-- Format Tingkat Juara Selector -->
            <div class="inline-flex p-1 rounded-2xl bg-[#0C111D] border border-white/[0.1] text-xs">
                <button type="button" 
                        @click="setTierFormat('juara_123')"
                        class="px-3 py-1.5 rounded-xl font-bold transition flex items-center gap-1.5 cursor-pointer text-xs"
                        :class="tierFormat === 'juara_123' ? 'bg-gradient-to-r from-amber-500 to-amber-600 text-slate-950 font-black shadow-md shadow-amber-500/20' : 'text-slate-400 hover:text-white'">
                    <i data-lucide="award" class="w-3.5 h-3.5"></i>
                    <span>Juara 1–3 (Tanpa Harapan)</span>
                </button>
                <button type="button" 
                        @click="setTierFormat('with_harapan')"
                        class="px-3 py-1.5 rounded-xl font-bold transition flex items-center gap-1.5 cursor-pointer text-xs"
                        :class="tierFormat === 'with_harapan' ? 'bg-gradient-to-r from-[#7A5AF8] to-[#4E6EFF] text-white font-black shadow-md shadow-[#7A5AF8]/20' : 'text-slate-400 hover:text-white'">
                    <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                    <span>+ Harapan 1–3</span>
                </button>
            </div>

            <!-- Filter Cabang Dropdown -->
            <div class="flex items-center gap-2">
                <label class="text-xs font-bold text-slate-400 whitespace-nowrap">Cabang:</label>
                <select x-model="selectedCompId" @change="changeCompetition($event.target.value)" class="w-full sm:w-56 px-3.5 py-2 rounded-xl bg-[#0C111D] border border-white/[0.12] text-xs font-bold text-white outline-none focus:border-[#7A5AF8] cursor-pointer">
                    @foreach($competitions as $c)
                        <option value="{{ $c->id }}" {{ $selectedComp && $selectedComp->id === $c->id ? 'selected' : '' }}>
                            {{ $c->name }} ({{ $c->code }})
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- Segmented Navigation Tabs (Tab 1: Live Terisi, Tab 2: Blank Template) -->
    <div class="flex items-center gap-2 border-b border-white/[0.08] pb-1">
        <button type="button" @click="activeTab = 'live'" :class="activeTab === 'live' ? 'bg-gradient-to-r from-[#7A5AF8] to-[#4E6EFF] text-white shadow-lg shadow-[#7A5AF8]/25 font-black' : 'text-slate-400 hover:text-white hover:bg-white/[0.04] font-bold'" class="px-5 py-2.5 rounded-2xl text-xs transition flex items-center gap-2 cursor-pointer">
            <i data-lucide="award" class="w-4 h-4"></i>
            <span>Tab 1: Berita Acara Pemenang (Terisi)</span>
        </button>
        <button type="button" @click="activeTab = 'blank'" :class="activeTab === 'blank' ? 'bg-gradient-to-r from-[#7A5AF8] to-[#4E6EFF] text-white shadow-lg shadow-[#7A5AF8]/25 font-black' : 'text-slate-400 hover:text-white hover:bg-white/[0.04] font-bold'" class="px-5 py-2.5 rounded-2xl text-xs transition flex items-center gap-2 cursor-pointer">
            <i data-lucide="file-pen-line" class="w-4 h-4"></i>
            <span>Tab 2: Template Berita Acara (Blank / Siap Tulis Tangan)</span>
        </button>
    </div>

    @if($selectedComp)
        <!-- ==================== TAB 1: BERITA ACARA PEMENANG (LIVE / TERISI) ==================== -->
        <div x-show="activeTab === 'live'" class="space-y-6">

            <!-- Panel Pengaturan Narasi & Juri/Wasit -->
            <div class="ai-card p-5 rounded-3xl border border-white/[0.08] shadow-lg space-y-4">
                <div class="flex items-center justify-between flex-wrap gap-3 border-b border-white/[0.08] pb-3">
                    <h3 class="text-xs font-bold text-white uppercase tracking-wider flex items-center gap-2">
                        <i data-lucide="sliders" class="w-4 h-4 text-[#7A5AF8]"></i>
                        <span>Parameter Berita Acara & Penginputan Nilai</span>
                    </h3>
                    <div class="flex items-center gap-2 flex-wrap">
                        <a :href="'{{ route('admin.berita-acara.template-nilai') }}?competition_id=' + selectedCompId" 
                           class="px-3.5 py-2 rounded-xl bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 text-xs font-bold transition flex items-center gap-1.5 cursor-pointer shadow-sm" 
                           title="Unduh blanko penilaian peserta dalam format Excel (.xlsx)">
                            <i data-lucide="file-spreadsheet" class="w-4 h-4 text-emerald-400"></i>
                            <span>Template Excel</span>
                        </a>

                        <button type="button" 
                                @click="showImportModal = true" 
                                class="px-3.5 py-2 rounded-xl bg-gradient-to-r from-[#7A5AF8] to-[#4E6EFF] hover:from-[#6941C6] hover:to-[#3538CD] text-white text-xs font-bold shadow-lg shadow-[#7A5AF8]/20 transition flex items-center gap-1.5 cursor-pointer" 
                                title="Import nilai peserta dari file Excel untuk penentuan juara instan">
                            <i data-lucide="upload-cloud" class="w-4 h-4 text-purple-200"></i>
                            <span>Import Nilai (Excel)</span>
                        </button>

                        <button type="button" 
                                @click="printLive()" 
                                class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold border border-white/[0.1] shadow-md transition flex items-center gap-1.5 cursor-pointer">
                            <i data-lucide="printer" class="w-4 h-4 text-emerald-400"></i>
                            <span>Cetak Berita Acara</span>
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 text-xs">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Hari Pelaksanaan:</label>
                        <input type="text" x-model="eventDay" class="w-full px-3.5 py-2.5 rounded-xl bg-[#0C111D] border border-white/[0.1] text-xs font-bold text-slate-200 outline-none focus:border-[#7A5AF8]" placeholder="Misal: Sabtu">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Tanggal Pelaksanaan:</label>
                        <input type="date" x-model="eventDate" class="w-full px-3.5 py-2.5 rounded-xl bg-[#0C111D] border border-white/[0.1] text-xs font-bold text-slate-200 outline-none focus:border-[#7A5AF8]">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Waktu / Pukul:</label>
                        <input type="text" x-model="eventTime" class="w-full px-3.5 py-2.5 rounded-xl bg-[#0C111D] border border-white/[0.1] text-xs font-bold text-slate-200 outline-none focus:border-[#7A5AF8]" placeholder="Misal: 08.00">
                    </div>

                    <!-- Juri / Wasit 1, 2, 3 -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">{{ $isSports ? 'Wasit 1 (Utama)' : 'Juri 1 (Ketua Juri)' }}:</label>
                        <input type="text" x-model="judge1" class="w-full px-3.5 py-2.5 rounded-xl bg-[#0C111D] border border-white/[0.1] text-xs font-bold text-slate-200 outline-none focus:border-[#7A5AF8]" placeholder="Nama Juri/Wasit 1">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">{{ $isSports ? 'Wasit 2' : 'Juri 2 (Anggota)' }}:</label>
                        <input type="text" x-model="judge2" class="w-full px-3.5 py-2.5 rounded-xl bg-[#0C111D] border border-white/[0.1] text-xs font-bold text-slate-200 outline-none focus:border-[#7A5AF8]" placeholder="Nama Juri/Wasit 2">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">{{ $isSports ? 'Wasit 3' : 'Juri 3 (Opsional / Kosongkan jika 2 Juri)' }}:</label>
                        <input type="text" x-model="judge3" class="w-full px-3.5 py-2.5 rounded-xl bg-[#0C111D] border border-white/[0.1] text-xs font-bold text-slate-200 outline-none focus:border-[#7A5AF8]" placeholder="Nama Juri/Wasit 3 (Opsional)">
                    </div>

                    <div class="sm:col-span-2 lg:col-span-3 text-[11px] text-slate-400 bg-white/[0.02] p-2.5 rounded-xl border border-white/[0.05] flex items-center gap-2">
                        <i data-lucide="info" class="w-4 h-4 text-[#7A5AF8] shrink-0"></i>
                        <span><strong>Info Format Juri:</strong> Jika cabang lomba menggunakan <strong>2 Juri</strong>, cukup kosongkan kolom Juri 3. Hasil cetak otomatis menyesuaikan hanya memuat 2 Juri dengan posisi tanda tangan simetris di kiri dan kanan.</span>
                    </div>
                </div>
            </div>

            <!-- Daftar Tabel Juara Per Kategori / Sektor -->
            <div class="space-y-6">
                @foreach($sectorsData as $secKey => $sector)
                    <div class="ai-card p-5 rounded-3xl border border-white/[0.08] shadow-lg space-y-3">
                        <div class="flex items-center justify-between flex-wrap gap-2">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
                                <h4 class="font-bold text-white text-sm sm:text-base">{{ $sector['definition']['title'] }}</h4>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="text-[11px] text-slate-400 font-medium">
                                    {{ $sector['total_participants'] }} Peserta Terverifikasi
                                </span>
                                @if(!empty($sector['has_scored_winners']))
                                    <span class="px-2.5 py-0.5 rounded-full bg-emerald-500/15 border border-emerald-500/30 text-emerald-300 text-[10px] font-bold flex items-center gap-1">
                                        <i data-lucide="check-circle" class="w-3 h-3"></i>
                                        <span>Nilai Juri Terkunci</span>
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full bg-amber-500/15 border border-amber-500/30 text-amber-300 text-[10px] font-bold flex items-center gap-1">
                                        <i data-lucide="clock" class="w-3 h-3"></i>
                                        <span>Menunggu Penilaian Juri</span>
                                    </span>
                                @endif
                                @if(count($sectorsData) > 1)
                                    <button type="button" @click="printLive('{{ $secKey }}')" class="px-3 py-1.5 rounded-xl bg-emerald-600/20 hover:bg-emerald-600/30 border border-emerald-500/30 text-emerald-300 text-xs font-bold transition flex items-center gap-1.5 cursor-pointer">
                                        <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                                        <span>Cetak Kategori Ini</span>
                                    </button>
                                @endif
                            </div>
                        </div>

                        @if(empty($sector['has_scored_winners']))
                            <div class="p-3.5 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-300 text-xs flex items-center gap-3">
                                <i data-lucide="info" class="w-4 h-4 shrink-0 text-amber-400"></i>
                                <div>
                                    <span class="font-bold">Belum ada penilaian yang dikunci oleh {{ $isSports ? 'Dewan Wasit' : 'Dewan Juri' }}.</span>
                                    <span class="text-slate-400 block text-[11px] mt-0.5">Nama pemenang (Juara 1 s.d. Harapan) otomatis terisi & diurutkan dari skor tertinggi setelah juri mengunci nilai di portal juri. Bila ingin formulir cetak untuk penilaian tulis tangan, silakan gunakan <strong>Tab 2 (Template Blank)</strong>.</span>
                                </div>
                            </div>
                        @endif

                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs text-slate-300">
                                <thead class="text-[11px] font-bold uppercase tracking-wider bg-[#0C111D]/80 text-slate-400 border-b border-white/[0.08]">
                                    <tr>
                                        <th class="py-2.5 px-3.5 w-32">Tingkat Juara</th>
                                        <th class="py-2.5 px-3.5 w-32">No. Peserta</th>
                                        <th class="py-2.5 px-3.5">Nama Peserta / Tim</th>
                                        <th class="py-2.5 px-3.5">Asal Sekolah / Madrasah</th>
                                        <th class="py-2.5 px-3.5 text-center w-28">{{ $isSports ? 'Skor' : 'Total Nilai' }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-white/[0.04]">
                                    @foreach($sector['tiers'] as $tier)
                                        <tr class="hover:bg-white/[0.02] transition">
                                            <td class="py-2.5 px-3.5 font-bold text-amber-300">
                                                {{ $tier['tier_label'] }}
                                            </td>
                                            <td class="py-2.5 px-3.5 font-mono font-bold text-[#84D0FF]">
                                                {{ !empty($tier['winner']['participant_number']) ? $tier['winner']['participant_number'] : '-' }}
                                            </td>
                                            <td class="py-2.5 px-3.5 font-bold text-white">
                                                {{ !empty($tier['winner']['display_name']) ? $tier['winner']['display_name'] : '-' }}
                                            </td>
                                            <td class="py-2.5 px-3.5 text-slate-300">
                                                {{ !empty($tier['winner']['institution_name']) ? $tier['winner']['institution_name'] : '-' }}
                                            </td>
                                            <td class="py-2.5 px-3.5 text-center font-mono font-bold text-emerald-400">
                                                {{ !empty($tier['winner']['score']) ? $tier['winner']['score'] : '-' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            </div>

        </div>

        <!-- ==================== TAB 2: TEMPLATE BERITA ACARA (BLANK / SIAP TULIS TANGAN) ==================== -->
        <div x-show="activeTab === 'blank'" class="space-y-6">

            <!-- Banner Info Template Blank -->
            <div class="ai-card p-5 rounded-3xl border border-white/[0.08] shadow-lg flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-amber-500/15 text-amber-400 border border-amber-500/30 flex items-center justify-center shrink-0">
                        <i data-lucide="printer" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-black text-white">Template Formulir Blank Siap Cetak (A4)</h4>
                        <p class="text-xs text-slate-400">Formulir berita acara resmi dengan baris kosong dan titik-titik untuk pengisian manual / tulis tangan oleh {{ $isSports ? 'Dewan Wasit' : 'Dewan Juri' }} di venue lomba.</p>
                    </div>
                </div>
                <button type="button" @click="printBlank()" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-slate-950 font-black text-xs shadow-lg shadow-amber-500/20 transition flex items-center gap-2 cursor-pointer shrink-0">
                    <i data-lucide="printer" class="w-4 h-4"></i>
                    <span>Cetak Template Blank (A4)</span>
                </button>
            </div>

            <!-- Live Blank Form Mockup Preview -->
            <div class="bg-white text-slate-900 p-8 sm:p-12 rounded-3xl shadow-2xl border border-slate-300 font-serif max-w-3xl mx-auto space-y-5">
                
                <!-- Kop Surat Preview -->
                <div class="text-center border-b-2 border-black pb-3 space-y-0.5">
                    <div class="text-xs font-bold uppercase tracking-wider">PANITIA {{ strtoupper($appSettings['event_name'] ?? 'MILAD KE-57') }}</div>
                    <div class="text-sm font-black uppercase">{{ strtoupper($appSettings['institution_name'] ?? 'MADRASAH TSANAWIYAH NEGERI 1 BLITAR') }}</div>
                    <div class="text-[10px] text-slate-600 italic">{{ $appSettings['address'] ?? 'Kantor : Jl. Ponpes Terpadu Al-Kamal Kunir Wonodadi Blitar' }}</div>
                </div>

                <!-- Judul Preview -->
                <div class="text-center space-y-1 py-2">
                    <div class="font-black text-sm uppercase underline">BERITA ACARA</div>
                    <div class="font-bold text-xs uppercase">{{ $isSports ? 'PERTANDINGAN CABANG' : 'PENJURIAN LOMBA' }} {{ strtoupper($selectedComp->name) }}</div>
                    <div class="font-bold text-[11px] uppercase">TINGKAT SD/MI SE-EKS KARESIDENAN KEDIRI</div>
                </div>

                <!-- Narasi Preview -->
                <div class="text-[11px] text-justify space-y-2 leading-relaxed">
                    <p>Bahwa pada hari ini, <strong>..........................</strong> tanggal <strong>........................................</strong> bulan <strong>..........................</strong> tahun <strong>........................................</strong> Pukul <strong>........</strong> WIB bertempat di MTs N 1 Blitar. Berdasarkan Penilaian {{ $isSports ? 'Dewan Wasit' : 'Dewan Juri' }} yang terdiri dari:</p>
                    <ol class="list-decimal list-inside pl-4 space-y-0.5">
                        <li>........................................................................................................................</li>
                        <li>........................................................................................................................</li>
                        <li>........................................................................................................................</li>
                    </ol>
                    <p>Telah memutuskan pemenang dalam {{ $isSports ? 'Pertandingan' : 'Lomba' }} <strong>{{ $selectedComp->name }}</strong> tingkat SD/MI se-karesidenan Kediri. Adapun nama-nama pemenang tersebut adalah sebagai berikut:</p>
                </div>

                <!-- Tabel Blank Preview -->
                @foreach($sectorsData as $secKey => $sector)
                    <div class="space-y-1">
                        @if(count($sectorsData) > 1)
                            <div class="font-bold text-[11px] underline">{{ $sector['definition']['title'] }}:</div>
                        @endif
                        <table class="w-full border-collapse border border-black text-[10px]">
                            <thead>
                                <tr class="bg-slate-100">
                                    <th class="border border-black py-1 px-2 w-28">Tingkat Juara</th>
                                    <th class="border border-black py-1 px-2 w-24">No. Peserta</th>
                                    <th class="border border-black py-1 px-2">Nama</th>
                                    <th class="border border-black py-1 px-2">Asal Sekolah</th>
                                    <th class="border border-black py-1 px-2 w-20">{{ $isSports ? 'Skor' : 'Total Nilai' }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sector['tiers'] as $tier)
                                    <tr>
                                        <td class="border border-black py-1.5 px-2 font-bold">{{ $tier['tier_label'] }}</td>
                                        <td class="border border-black py-1.5 px-2"></td>
                                        <td class="border border-black py-1.5 px-2"></td>
                                        <td class="border border-black py-1.5 px-2"></td>
                                        <td class="border border-black py-1.5 px-2"></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endforeach

                <div class="text-[11px] pt-1">
                    <p>Demikian hasil keputusan ini ditetapkan. Keputusan {{ $isSports ? 'Dewan Wasit' : 'Dewan Juri' }} bersifat mutlak dan tidak dapat diganggu gugat.</p>
                </div>

                <!-- TTD Preview -->
                <div class="pt-4">
                    <div class="text-right text-[11px] mb-3">Blitar, ........................................</div>
                    <div class="grid grid-cols-3 text-center text-[10px] gap-4">
                        <div class="space-y-12">
                            <div class="font-bold">{{ $isSports ? 'Wasit 1' : 'Juri 1' }}</div>
                            <div class="font-bold">( ........................................ )</div>
                        </div>
                        <div class="space-y-12">
                            <div class="font-bold">{{ $isSports ? 'Wasit 2' : 'Juri 2' }}</div>
                            <div class="font-bold">( ........................................ )</div>
                        </div>
                        <div class="space-y-12">
                            <div class="font-bold">{{ $isSports ? 'Wasit 3' : 'Juri 3' }}</div>
                            <div class="font-bold">( ........................................ )</div>
                        </div>
                    </div>
                </div>

                <!-- Footer Preview (Tipis Seperti Bukti Pendaftaran) -->
                <div class="pt-3 mt-6 border-t border-slate-300 flex items-center justify-between text-[9px] text-slate-500 font-mono">
                    <span>Panitia {{ $appSettings['event_name'] ?? 'Milad ke-57' }} {{ $appSettings['institution_name'] ?? 'MTsN 1 Blitar' }} • Dokumen Berita Acara • Aplikasi {{ $appSettings['app_name'] ?? 'TALENTA' }}</span>
                    <span>Dicetak pada: {{ now()->format('d/m/Y H:i:s') }}</span>
                </div>

            </div>

        </div>
    @endif

    <!-- ==================== MODAL IMPORT NILAI EXCEL ==================== -->
    <div x-show="showImportModal" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm transition">
        
        <div class="relative w-full max-w-lg rounded-3xl bg-[#0F172A] border border-white/[0.12] p-6 shadow-2xl space-y-5 text-slate-200"
             @click.away="if (!importLoading) showImportModal = false">
            
            <!-- Modal Header -->
            <div class="flex items-center justify-between border-b border-white/[0.08] pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-[#7A5AF8]/20 to-[#4E6EFF]/20 border border-[#7A5AF8]/30 flex items-center justify-center text-[#A594FD]">
                        <i data-lucide="upload-cloud" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-black text-white">Import Nilai & Rekap Juara</h3>
                        <p class="text-xs text-slate-400">{{ $selectedComp->name ?? 'Cabang Lomba' }} ({{ $selectedComp->code ?? '' }})</p>
                    </div>
                </div>
                <button type="button" @click="if (!importLoading) showImportModal = false" class="text-slate-400 hover:text-white p-1 rounded-lg transition cursor-pointer">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Petunjuk & Link Unduh Template -->
            <div class="p-3.5 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-xs text-emerald-300 flex items-start gap-3">
                <i data-lucide="info" class="w-4 h-4 text-emerald-400 shrink-0 mt-0.5"></i>
                <div class="space-y-1">
                    <p class="font-bold text-white">Belum memiliki template Excel?</p>
                    <p class="text-slate-300 text-[11px]">Unduh format resmi yang telah terisi nomor urut dan nama peserta cabang lomba ini:</p>
                    <a :href="'{{ route('admin.berita-acara.template-nilai') }}?competition_id=' + selectedCompId" 
                       class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-400 hover:underline pt-0.5">
                        <i data-lucide="download" class="w-3.5 h-3.5"></i>
                        <span>Unduh Format Excel ({{ $selectedComp->code ?? '' }})</span>
                    </a>
                </div>
            </div>

            <!-- Form Upload -->
            <form action="{{ route('admin.berita-acara.import-nilai') }}" 
                  method="POST" 
                  enctype="multipart/form-data" 
                  @submit="importLoading = true" 
                  class="space-y-4 text-xs">
                @csrf
                <input type="hidden" name="competition_id" value="{{ $selectedComp->id ?? '' }}">

                <!-- Pilih Juri Penilai (Opsional / Otomatis) -->
                @if($selectedComp && $selectedComp->judges->isNotEmpty())
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Tetapkan Sebagai Penilaian Juri:</label>
                        <select name="judge_id" class="w-full px-3.5 py-2.5 rounded-xl bg-[#0C111D] border border-white/[0.12] text-xs text-white outline-none focus:border-[#7A5AF8]">
                            <option value="">-- Otomatis (Juri Utama / Pengguna Saat Ini) --</option>
                            @foreach($selectedComp->judges as $j)
                                <option value="{{ $j->id }}">{{ $j->name }} ({{ $j->pivot->role_title ?? 'Dewan Juri' }})</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <!-- Opsi Kunci Nilai -->
                <div class="flex items-center gap-2.5 p-3 rounded-xl bg-white/[0.03] border border-white/[0.06]">
                    <input type="checkbox" name="lock_scores" value="1" id="lockScoresCheckbox" checked class="w-4 h-4 rounded border-white/[0.2] bg-slate-900 text-[#7A5AF8] focus:ring-0 cursor-pointer">
                    <label for="lockScoresCheckbox" class="text-xs text-slate-300 font-medium cursor-pointer">
                        <strong>Kunci Nilai sebagai Nilai Final</strong>
                        <span class="block text-[11px] text-slate-400">Otomatis menetapkan pemenang pada Berita Acara & Piagam Sertifikat</span>
                    </label>
                </div>

                <!-- File Input Dropzone -->
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Pilih File Spreadsheet (.xlsx / .xls / .csv):</label>
                    <input type="file" 
                           name="excel_file" 
                           required 
                           accept=".xlsx,.xls,.csv" 
                           class="w-full text-xs text-slate-300 file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[#7A5AF8] file:text-white hover:file:bg-[#6941C6] file:cursor-pointer cursor-pointer border border-white/[0.1] rounded-2xl p-2 bg-[#0C111D] outline-none">
                </div>

                <!-- Modal Actions -->
                <div class="pt-3 border-t border-white/[0.08] flex items-center justify-end gap-2.5">
                    <button type="button" 
                            @click="if (!importLoading) showImportModal = false" 
                            :disabled="importLoading"
                            class="px-4 py-2.5 rounded-xl border border-white/[0.1] text-xs font-bold text-slate-300 hover:bg-white/[0.05] transition disabled:opacity-50 cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" 
                            :disabled="importLoading"
                            class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-[#7A5AF8] to-[#4E6EFF] hover:from-[#6941C6] hover:to-[#3538CD] text-white text-xs font-bold shadow-lg shadow-[#7A5AF8]/30 transition flex items-center gap-2 cursor-pointer disabled:opacity-50">
                        <template x-if="!importLoading">
                            <span class="flex items-center gap-2">
                                <i data-lucide="upload" class="w-4 h-4"></i>
                                <span>Upload & Proses Nilai</span>
                            </span>
                        </template>
                        <template x-if="importLoading">
                            <span class="flex items-center gap-2">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4l3-3-3-3v4a8 8 0 00-8 8h4l-3 3 3 3H4z"></path>
                                </svg>
                                <span>Memproses File...</span>
                            </span>
                        </template>
                    </button>
                </div>
            </form>

        </div>
    </div>

</div>
@endsection
