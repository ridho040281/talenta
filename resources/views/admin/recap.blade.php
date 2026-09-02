@extends('layouts.admin')

@section('title', 'Rekapitulasi Terpadu TALENTA 2026')
@section('page_title', 'Rekapitulasi Terpadu & Hasil Lomba')

@section('content')
<div class="space-y-6" x-data="{ 
    activeTab: 'keuangan',
    searchQuery: '',
    selectedCategory: 'all',
    selectedStatus: 'all'
}">

    <!-- Quick Financial & Registration Stat Cards (AIStarterKit Design) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Total Pendaftar -->
        <div class="ai-card rounded-3xl p-5 border border-white/[0.08] shadow-lg space-y-1 hover:border-[#4E6EFF]/50 transition">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Pendaftar</span>
                <div class="w-8 h-8 rounded-xl bg-[#4E6EFF]/15 text-[#84D0FF] border border-[#4E6EFF]/30 flex items-center justify-center">
                    <i data-lucide="users" class="w-4 h-4"></i>
                </div>
            </div>
            <div>
                <span class="text-xl sm:text-2xl font-black text-white">{{ $grandTotals['total_registrations'] }}</span>
                <span class="text-[11px] text-slate-400 font-medium"> / {{ $grandTotals['total_quota'] }} Kuota</span>
            </div>
            <p class="text-[10px] text-slate-400">{{ $competitions->count() }} Cabang Lomba</p>
        </div>

        <!-- Card 2: Terverifikasi (Lunas) -->
        <div class="ai-card rounded-3xl p-5 border border-white/[0.08] shadow-lg space-y-1 hover:border-emerald-500/50 transition">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-emerald-400 uppercase tracking-wider">Terverifikasi (Lunas)</span>
                <div class="w-8 h-8 rounded-xl bg-emerald-500/15 text-emerald-400 border border-emerald-500/30 flex items-center justify-center">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                </div>
            </div>
            <div>
                <span class="text-xl sm:text-2xl font-black text-emerald-400">{{ $grandTotals['verified_registrations'] }}</span>
                <span class="text-[11px] text-slate-400 font-medium"> Siswa</span>
            </div>
            <p class="text-[10px] text-emerald-400 font-semibold">Telah valid & siap lomba</p>
        </div>

        <!-- Card 3: Uang Pendaftaran Masuk (Lunas) -->
        <div class="ai-card rounded-3xl p-5 border border-white/[0.08] shadow-lg space-y-1 hover:border-emerald-500/50 transition">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Dana Masuk (Lunas)</span>
                <div class="w-8 h-8 rounded-xl bg-emerald-500/15 text-emerald-400 border border-emerald-500/30 flex items-center justify-center">
                    <i data-lucide="wallet" class="w-4 h-4"></i>
                </div>
            </div>
            <div>
                <span class="text-xl sm:text-2xl font-black text-emerald-400 font-mono">Rp {{ number_format($grandTotals['verified_income'], 0, ',', '.') }}</span>
            </div>
            <p class="text-[10px] text-slate-400">Dana registrasi terverifikasi</p>
        </div>

        <!-- Card 4: Potensi Total Dana -->
        <div class="ai-card rounded-3xl p-5 border border-white/[0.08] shadow-lg space-y-1 bg-gradient-to-tr from-[#7A5AF8]/30 to-[#4E6EFF]/30 hover:border-[#7A5AF8]/50 transition">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-[#A594FD] uppercase tracking-wider">Total Potensi Dana</span>
                <div class="w-8 h-8 rounded-xl bg-[#7A5AF8]/20 text-white border border-[#7A5AF8]/30 flex items-center justify-center">
                    <i data-lucide="coins" class="w-4 h-4"></i>
                </div>
            </div>
            <div>
                <span class="text-xl sm:text-2xl font-black text-white font-mono">Rp {{ number_format($grandTotals['total_potential_income'], 0, ',', '.') }}</span>
            </div>
            <p class="text-[10px] text-[#A594FD] font-medium">{{ $grandTotals['pending_registrations'] }} pendaftar pending</p>
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
        <div class="ai-card rounded-3xl border border-white/[0.08] shadow-xl p-6 sm:p-8 space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-white/[0.08] pb-4">
                <div>
                    <h3 class="text-lg font-black text-white">Rekapitulasi Keuangan & Kuota Pendaftaran Cabang Lomba</h3>
                    <p class="text-xs text-slate-400">Rincian pendapatan registrasi dan keterisian kuota peserta per cabang lomba</p>
                </div>
                <div class="text-right">
                    <span class="text-xs text-slate-400 block uppercase font-bold">Total Dana Lunas Masuk</span>
                    <span class="text-xl font-black text-emerald-400 font-mono">Rp {{ number_format($grandTotals['verified_income'], 0, ',', '.') }}</span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="text-[10px] font-bold uppercase tracking-wider bg-[#0C111D]/90 text-slate-400 border-b border-white/[0.08]">
                        <tr>
                            <th class="py-3.5 px-4">KODE</th>
                            <th class="py-3.5 px-4">NAMA CABANG LOMBA</th>
                            <th class="py-3.5 px-4 text-center">KUOTA</th>
                            <th class="py-3.5 px-4 text-center">PENDAFTAR</th>
                            <th class="py-3.5 px-4 text-center text-emerald-700">VERIFIKASI (LUNAS)</th>
                            <th class="py-3.5 px-4 text-center text-amber-700">PENDING</th>
                            <th class="py-3.5 px-4 text-right">DANA LUNAS MASUK</th>
                            <th class="py-3.5 px-4 text-right">POTENSI TOTAL</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/[0.04] font-medium">
                        @foreach($financeRecap as $item)
                            @php
                                $c = $item['competition'];
                                $fillPercent = $item['quota'] > 0 ? min(100, round(($item['total_regs'] / $item['quota']) * 100)) : 0;
                            @endphp
                            <tr class="hover:bg-white/[0.025] transition">
                                <td class="py-3.5 px-4 font-mono font-bold text-[#84D0FF]">
                                    {{ $c->code }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="font-bold text-white text-sm block">{{ $c->name }}</span>
                                    <span class="text-[11px] text-slate-400">{{ $c->category->name ?? 'Lomba' }} • PIC: {{ $c->pic->name ?? '-' }}</span>
                                </td>
                                <td class="py-3.5 px-4 text-center font-bold text-slate-200">
                                    @if($item['quota'] <= 0)
                                        <span class="text-purple-300 bg-purple-500/20 px-2 py-0.5 rounded-full text-[10px] font-black border border-purple-500/30">∞ Tak Terbatas</span>
                                    @else
                                        {{ $item['quota'] }}
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="font-bold text-white">{{ $item['total_regs'] }}</span>
                                    <div class="w-16 bg-[#0C111D] h-1.5 rounded-full mx-auto mt-1 overflow-hidden border border-white/[0.06]">
                                        <div class="bg-gradient-to-r from-[#7A5AF8] to-[#4E6EFF] h-full rounded-full" style="width: {{ $fillPercent }}%"></div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">
                                        {{ $item['verified_count'] }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black {{ $item['pending_count'] > 0 ? 'bg-amber-500/15 text-amber-400 border border-amber-500/30' : 'bg-white/[0.05] text-slate-500 border border-white/[0.08]' }}">
                                        {{ $item['pending_count'] }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right font-black text-emerald-400 font-mono text-sm">
                                    Rp {{ number_format($item['verified_income'], 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-4 text-right font-bold text-slate-200 font-mono">
                                    Rp {{ number_format($item['total_income'], 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-[#0C111D] text-white font-bold border-t-2 border-white/[0.1]">
                        <tr>
                            <td colspan="2" class="py-4 px-4 font-black uppercase text-xs">GRAND TOTAL KESELURUHAN:</td>
                            <td class="py-4 px-4 text-center font-black">{{ $grandTotals['total_quota'] }}</td>
                            <td class="py-4 px-4 text-center font-black">{{ $grandTotals['total_registrations'] }}</td>
                            <td class="py-4 px-4 text-center font-black text-emerald-300">{{ $grandTotals['verified_registrations'] }}</td>
                            <td class="py-4 px-4 text-center font-black text-amber-300">{{ $grandTotals['pending_registrations'] }}</td>
                            <td class="py-4 px-4 text-right font-black text-emerald-400 font-mono text-base">
                                Rp {{ number_format($grandTotals['verified_income'], 0, ',', '.') }}
                            </td>
                            <td class="py-4 px-4 text-right font-black text-white font-mono text-base">
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
        <div class="ai-card rounded-3xl border border-white/[0.08] shadow-xl p-6 sm:p-8 space-y-6">
            
            <!-- Filters & Search Bar -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-white/[0.08] pb-6">
                <div>
                    <h3 class="text-lg font-black text-white">Master Data Seluruh Peserta Terdaftar</h3>
                    <p class="text-xs text-slate-400">Daftar lengkap seluruh delegasi siswa dari seluruh cabang lomba TALENTA 2026</p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <!-- Search Input -->
                    <div class="relative min-w-[220px]">
                        <i data-lucide="search" class="w-4 h-4 text-slate-500 absolute left-3 top-1/2 -translate-y-1/2"></i>
                        <input type="text" x-model="searchQuery" placeholder="Cari nama / NISN / sekolah..." class="w-full pl-9 pr-4 py-2.5 rounded-xl bg-[#0C111D] border border-white/[0.1] text-xs text-white placeholder-slate-500 focus:border-[#7A5AF8] focus:ring-2 focus:ring-[#7A5AF8]/20 outline-none">
                    </div>

                    <!-- Category / Competition Filter -->
                    <select x-model="selectedCategory" class="px-3 py-2.5 rounded-xl border border-white/[0.1] text-xs font-bold text-slate-200 bg-[#0C111D] focus:border-[#7A5AF8] outline-none">
                        <option value="all">Semua Cabang Lomba</option>
                        @foreach($competitions as $c)
                            <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->code }})</option>
                        @endforeach
                    </select>

                    <!-- Status Filter -->
                    <select x-model="selectedStatus" class="px-3 py-2.5 rounded-xl border border-white/[0.1] text-xs font-bold text-slate-200 bg-[#0C111D] focus:border-[#7A5AF8] outline-none">
                        <option value="all">Semua Status</option>
                        <option value="verified">Lunas / Terverifikasi</option>
                        <option value="pending">Menunggu Verifikasi</option>
                        <option value="rejected">Ditolak</option>
                    </select>
                </div>
            </div>

            <!-- Master Participants Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="text-[10px] font-bold uppercase tracking-wider bg-[#0C111D]/90 text-slate-400 border-b border-white/[0.08]">
                        <tr>
                            <th class="py-3.5 px-4">NO. REGISTRASI</th>
                            <th class="py-3.5 px-4">NAMA PESERTA / TIM</th>
                            <th class="py-3.5 px-4">ASAL SEKOLAH / MADRASAH</th>
                            <th class="py-3.5 px-4">CABANG LOMBA & KATEGORI</th>
                            <th class="py-3.5 px-4 text-right">BIAYA DAFTAR</th>
                            <th class="py-3.5 px-4 text-center">BUKTI / STRUK</th>
                            <th class="py-3.5 px-4 text-center">STATUS</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/[0.04] font-medium">
                        @forelse($allRegistrations as $reg)
                            @php
                                $firstName = $reg->members->first()->full_name ?? $reg->team_name;
                                $firstNisn = $reg->members->first()->nisn ?? '-';
                                $filterSearch = strtolower($firstName . ' ' . $firstNisn . ' ' . $reg->institution_name . ' ' . $reg->registration_code);
                            @endphp
                            <tr x-show="(selectedCategory === 'all' || selectedCategory == '{{ $reg->competition_id }}') &&
                                        (selectedStatus === 'all' || selectedStatus === '{{ $reg->status }}') &&
                                        (searchQuery === '' || '{{ addslashes($filterSearch) }}'.includes(searchQuery.toLowerCase()))"
                                class="hover:bg-white/[0.025] transition">
                                <td class="py-3.5 px-4 font-mono font-bold text-[#84D0FF]">
                                    {{ $reg->registration_code }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-white text-sm">
                                        {{ $reg->team_name ?: ($reg->members->first()->full_name ?? 'Peserta #' . $reg->id) }}
                                    </div>
                                    <div class="text-[11px] text-slate-400">
                                        NISN: {{ $reg->members->first()->nisn ?? '-' }} • {{ $reg->members->count() }} Anggota
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 font-bold text-slate-200">
                                    {{ $reg->institution_name }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="font-bold text-white">{{ $reg->competition->name }}</span>
                                    @if($reg->sub_category)
                                        <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-500/15 text-amber-400 border border-amber-500/30">
                                            {{ $reg->sub_category }}
                                        </span>
                                    @else
                                        <span class="block text-[10px] text-slate-400 uppercase font-mono">{{ $reg->competition->code }} ({{ $reg->competition->type }})</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right font-black text-emerald-400 font-mono text-sm">
                                    Rp {{ number_format($reg->fee, 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if($reg->payment_proof)
                                        <a href="{{ asset('storage/' . $reg->payment_proof) }}" target="_blank" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-[#4E6EFF]/15 text-[#84D0FF] font-bold hover:bg-[#4E6EFF]/25 transition text-[11px] border border-[#4E6EFF]/30">
                                            <i data-lucide="image" class="w-3.5 h-3.5"></i>
                                            <span>Struk</span>
                                        </a>
                                    @else
                                        <span class="text-slate-500 italic text-[11px]">-</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if($reg->status === 'verified')
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">
                                            ✔ LUNAS / TERVERIFIKASI
                                        </span>
                                    @elseif($reg->status === 'rejected')
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-rose-500/15 text-rose-400 border border-rose-500/30">
                                            ✕ DITOLAK
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-amber-500/15 text-amber-400 border border-amber-500/30">
                                            ⏳ PENDING
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-slate-500">Belum ada peserta terdaftar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ==================== TAB 3: REKAP SEMUA PERAIH JUARA ==================== -->
    <div x-show="activeTab === 'juara'" x-transition class="space-y-6">
        <div class="ai-card rounded-3xl border border-white/[0.08] shadow-xl p-6 sm:p-8 space-y-6">
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
        <div class="ai-card rounded-3xl border border-white/[0.08] shadow-xl overflow-hidden space-y-6 p-6 sm:p-8">
            <div class="flex items-center justify-between border-b border-white/[0.08] pb-4">
                <div>
                    <h3 class="text-base font-bold text-white">Klasemen Perolehan Medali & Juara Umum Kontingen</h3>
                    <p class="text-xs text-slate-400">Bobot poin kontingen: 🥇 Emas (5 Poin), 🥈 Perak (3 Poin), 🥉 Perunggu (1 Poin)</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-300">
                    <thead class="text-xs font-bold uppercase tracking-wider bg-[#0C111D]/90 text-slate-400 border-b border-white/[0.08]">
                        <tr>
                            <th class="py-4 px-6 text-center w-16">Peringkat</th>
                            <th class="py-4 px-6">Nama Asal Sekolah / Madrasah</th>
                            <th class="py-4 px-4 text-center">🥇 Emas (5p)</th>
                            <th class="py-4 px-4 text-center">🥈 Perak (3p)</th>
                            <th class="py-4 px-4 text-center">🥉 Perunggu (1p)</th>
                            <th class="py-4 px-6 text-center">Total Medali</th>
                            <th class="py-4 px-6 text-right">Total Poin</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/[0.04] font-medium">
                        @forelse($standings as $index => $item)
                            <tr class="hover:bg-white/[0.025] transition {{ $index === 0 ? 'bg-amber-500/10' : '' }}">
                                <td class="py-4 px-6 text-center">
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
                                <td class="py-4 px-6 font-bold text-white text-base">
                                    {{ $item['institution'] }}
                                    @if($index === 0)
                                        <span class="ml-2 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-amber-400 text-slate-950">
                                            Calon Juara Umum
                                        </span>
                                    @endif
                                </td>
                                <td class="py-4 px-4 text-center font-black text-amber-400 text-base">{{ $item['emas'] }}</td>
                                <td class="py-4 px-4 text-center font-black text-slate-300 text-base">{{ $item['perak'] }}</td>
                                <td class="py-4 px-4 text-center font-black text-amber-500 text-base">{{ $item['perunggu'] }}</td>
                                <td class="py-4 px-6 text-center font-bold text-slate-200">{{ $item['total_medali'] }}</td>
                                <td class="py-4 px-6 text-right">
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
@endsection
