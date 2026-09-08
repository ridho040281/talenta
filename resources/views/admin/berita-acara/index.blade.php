@extends('layouts.admin')

@section('title', 'Berita Acara Pemenang & Template')
@section('page_title', 'Berita Acara Pemenang & Penjurian')

@section('content')
<div class="space-y-6" x-data="{
    activeTab: '{{ $activeTab }}',
    selectedCompId: '{{ $selectedComp->id ?? '' }}',
    eventDate: '{{ date('Y-m-d') }}',
    eventDay: '{{ $dateSpelled['day_name'] }}',
    eventTime: '08.00',
    judge1: '{{ addslashes($judgesList[0] ?? '') }}',
    judge2: '{{ addslashes($judgesList[1] ?? '') }}',
    judge3: '{{ addslashes($judgesList[2] ?? '') }}',
    
    changeCompetition(id) {
        window.location.href = '{{ route('admin.berita-acara.index') }}?competition_id=' + id + '&tab=' + this.activeTab;
    },
    
    printLive() {
        const url = new URL('{{ route('admin.berita-acara.print') }}', window.location.origin);
        url.searchParams.set('competition_id', this.selectedCompId);
        url.searchParams.set('type', 'live');
        url.searchParams.set('date', this.eventDate);
        url.searchParams.set('day', this.eventDay);
        url.searchParams.set('time', this.eventTime);
        url.searchParams.set('judge1', this.judge1);
        url.searchParams.set('judge2', this.judge2);
        url.searchParams.set('judge3', this.judge3);
        window.open(url.toString(), '_blank');
    },

    printBlank() {
        const url = new URL('{{ route('admin.berita-acara.print') }}', window.location.origin);
        url.searchParams.set('competition_id', this.selectedCompId);
        url.searchParams.set('type', 'blank');
        window.open(url.toString(), '_blank');
    }
}">

    <!-- Top Control Bar (Pilih Cabang & Quick Info) -->
    <div class="ai-card p-4 sm:p-5 rounded-3xl border border-white/[0.08] shadow-xl flex flex-col md:flex-row md:items-center justify-between gap-4">
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

        <!-- Filter Cabang Dropdown -->
        <div class="flex items-center gap-2.5 w-full md:w-auto">
            <label class="text-xs font-bold text-slate-400 whitespace-nowrap">Cabang:</label>
            <select x-model="selectedCompId" @change="changeCompetition($event.target.value)" class="w-full md:w-64 px-3.5 py-2.5 rounded-xl bg-[#0C111D] border border-white/[0.12] text-xs font-bold text-white outline-none focus:border-[#7A5AF8] cursor-pointer">
                @foreach($competitions as $c)
                    <option value="{{ $c->id }}" {{ $selectedComp && $selectedComp->id === $c->id ? 'selected' : '' }}>
                        {{ $c->name }} ({{ $c->code }})
                    </option>
                @endforeach
            </select>
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
                <div class="flex items-center justify-between border-b border-white/[0.08] pb-3">
                    <h3 class="text-xs font-bold text-white uppercase tracking-wider flex items-center gap-2">
                        <i data-lucide="sliders" class="w-4 h-4 text-[#7A5AF8]"></i>
                        <span>Parameter Berita Acara (Tanggal, Waktu & Dewan {{ $isSports ? 'Wasit' : 'Juri' }})</span>
                    </h3>
                    <button type="button" @click="printLive()" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold shadow-md transition flex items-center gap-2 cursor-pointer">
                        <i data-lucide="printer" class="w-4 h-4"></i>
                        <span>Cetak Berita Acara (A4)</span>
                    </button>
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
                        <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">{{ $isSports ? 'Wasit 3' : 'Juri 3 (Anggota)' }}:</label>
                        <input type="text" x-model="judge3" class="w-full px-3.5 py-2.5 rounded-xl bg-[#0C111D] border border-white/[0.1] text-xs font-bold text-slate-200 outline-none focus:border-[#7A5AF8]" placeholder="Nama Juri/Wasit 3">
                    </div>
                </div>
            </div>

            <!-- Daftar Tabel Juara Per Kategori / Sektor -->
            <div class="space-y-6">
                @foreach($sectorsData as $secKey => $sector)
                    <div class="ai-card p-5 rounded-3xl border border-white/[0.08] shadow-lg space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
                                <h4 class="font-bold text-white text-sm sm:text-base">{{ $sector['definition']['title'] }}</h4>
                            </div>
                            <span class="text-[11px] text-slate-400 font-medium">
                                {{ $sector['total_participants'] }} Peserta Terverifikasi
                            </span>
                        </div>

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

            </div>

        </div>
    @endif

</div>
@endsection
