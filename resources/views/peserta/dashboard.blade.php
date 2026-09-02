@extends('layouts.admin')

@section('title', 'Dashboard Pendaftar')
@section('page_title', 'Portal Pendaftaran Peserta')

@section('content')
<div class="space-y-8" x-data="{ activeCategory: 'all' }">
    
    <!-- Top Greeting Banner (Dark Glass Theme) -->
    <div class="glass-card rounded-3xl p-6 sm:p-7 border border-slate-800/80 shadow-2xl relative overflow-hidden flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="relative z-10">
            <div class="flex items-center gap-2 flex-wrap">
                <span class="px-2.5 py-0.5 text-[11px] font-bold rounded-md bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                    {{ $user->institution_name ?: 'Pendaftar Resmi' }}
                </span>
                <span class="text-xs text-slate-400 font-mono">NISN/ID: {{ $user->nisn ?: $user->email }}</span>
            </div>
            <h2 class="text-xl sm:text-2xl font-black text-white mt-1 font-display">Halo, {{ $user->name }}</h2>
            <p class="text-xs text-slate-300 mt-0.5">Pilih cabang lomba di bawah untuk mendaftarkan peserta delegasi sekolah, unduh bukti pendaftaran, dan kelola berkas.</p>
        </div>
        <div class="shrink-0 flex items-center gap-2.5 relative z-10 flex-wrap">
            @if($registrations->isNotEmpty())
                <a href="{{ route('peserta.registrations') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-slate-800/90 hover:bg-slate-700 text-white font-bold text-xs border border-slate-700 shadow-md transition cursor-pointer">
                    <i data-lucide="award" class="w-4 h-4 text-amber-400"></i>
                    <span>Pendaftaran Saya ({{ $registrations->count() }})</span>
                </a>
            @endif
        </div>
    </div>

    <!-- Active Invoices Widget (if any) -->
    @if(isset($invoices) && $invoices->isNotEmpty())
        <div class="glass-card rounded-3xl p-6 sm:p-7 border border-slate-800 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-800/80 pb-3">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold border border-emerald-500/30">
                        <i data-lucide="receipt" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-black text-white font-display">Tagihan & Bukti Pembayaran Kolektif</h3>
                        <p class="text-xs text-slate-400">Status 1 lembar bukti transfer untuk pendaftaran rombongan sekolah</p>
                    </div>
                </div>
                <a href="{{ route('peserta.collective.wizard') }}" class="text-xs font-bold text-emerald-400 hover:text-emerald-300 inline-flex items-center gap-1">
                    <span>Lihat Semua</span>
                    <i data-lucide="chevron-right" class="w-4 h-4"></i>
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($invoices->take(2) as $inv)
                    <div class="p-4 rounded-2xl bg-slate-900/80 border border-slate-800 flex items-center justify-between gap-4 hover:border-slate-700 transition">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="font-mono text-xs font-bold text-white bg-slate-800 px-2.5 py-0.5 rounded-lg border border-slate-700">
                                    {{ $inv->invoice_number }}
                                </span>
                                @if($inv->status === 'verified')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">LUNAS</span>
                                @elseif($inv->payment_proof)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-amber-500/20 text-amber-400 border border-amber-500/30">PROSES VERIFIKASI</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-rose-500/20 text-rose-400 border border-rose-500/30">BELUM BAYAR</span>
                                @endif
                            </div>
                            <p class="text-xs text-slate-300 font-medium">
                                {{ $inv->registrations->count() }} Peserta Rombongan • <strong class="text-emerald-400 font-bold">{{ $inv->formatted_final_amount }}</strong>
                            </p>
                        </div>

                        <a href="{{ route('peserta.invoices.show', $inv->id) }}" class="px-4 py-2 rounded-xl bg-slate-800 border border-slate-700 hover:border-emerald-500 hover:text-emerald-400 font-bold text-xs text-slate-200 transition shrink-0 shadow-sm">
                            Detail Tagihan
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- KATALOG CABANG PERLOMBAAN TALENTA 2026 (Category-Dynamic Neon Cards) -->
    <div class="glass-card rounded-3xl p-6 sm:p-8 border border-slate-800/80 shadow-2xl space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-800/80 pb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-amber-500/20 text-amber-400 flex items-center justify-center font-bold shrink-0 border border-amber-500/30 shadow-lg shadow-amber-500/10">
                    <i data-lucide="trophy" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-base sm:text-lg font-black text-white font-display">Katalog Cabang Perlombaan TALENTA 2026</h3>
                    <p class="text-xs text-slate-400">Klik langsung pada kartu lomba di bawah untuk mengisi formulir pendaftaran peserta</p>
                </div>
            </div>

            <a href="{{ route('peserta.collective.wizard') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-gradient-to-r from-emerald-400 via-teal-400 to-cyan-400 hover:from-emerald-300 hover:to-cyan-300 text-slate-950 font-black text-xs shadow-lg shadow-emerald-500/30 hover:scale-[1.02] transition-all cursor-pointer shrink-0">
                <i data-lucide="file-spreadsheet" class="w-4 h-4 text-slate-950"></i>
                <span>Daftar Kolektif Excel (Banyak Siswa)</span>
            </a>
        </div>

        <!-- Dynamic Filter Category Pills -->
        <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-none text-xs">
            <button type="button" @click="activeCategory = 'all'" :class="activeCategory === 'all' ? 'bg-gradient-to-r from-emerald-400 to-teal-400 text-slate-950 font-black shadow-lg shadow-emerald-500/30 scale-105' : 'bg-slate-900/80 hover:bg-slate-800 text-slate-300 border border-slate-800 font-bold'" class="px-4 py-2 rounded-xl transition-all duration-200 shrink-0 cursor-pointer">
                Semua Cabang ({{ $openCompetitions->count() }})
            </button>
            <button type="button" @click="activeCategory = 'Seni'" :class="activeCategory === 'Seni' ? 'bg-gradient-to-r from-pink-500 to-rose-500 text-white font-black shadow-lg shadow-pink-500/40 scale-105' : 'bg-slate-900/80 hover:bg-slate-800 text-slate-300 border border-slate-800 font-bold'" class="px-4 py-2 rounded-xl transition-all duration-200 shrink-0 cursor-pointer">
                🎨 Seni
            </button>
            <button type="button" @click="activeCategory = 'Olahraga'" :class="activeCategory === 'Olahraga' ? 'bg-gradient-to-r from-amber-400 to-orange-500 text-slate-950 font-black shadow-lg shadow-amber-500/40 scale-105' : 'bg-slate-900/80 hover:bg-slate-800 text-slate-300 border border-slate-800 font-bold'" class="px-4 py-2 rounded-xl transition-all duration-200 shrink-0 cursor-pointer">
                🏸 Olahraga
            </button>
            <button type="button" @click="activeCategory = 'Olimpiade'" :class="activeCategory === 'Olimpiade' ? 'bg-gradient-to-r from-cyan-400 to-blue-500 text-slate-950 font-black shadow-lg shadow-cyan-500/40 scale-105' : 'bg-slate-900/80 hover:bg-slate-800 text-slate-300 border border-slate-800 font-bold'" class="px-4 py-2 rounded-xl transition-all duration-200 shrink-0 cursor-pointer">
                📐 Olimpiade & Akademik
            </button>
            <button type="button" @click="activeCategory = 'Teknologi'" :class="activeCategory === 'Teknologi' ? 'bg-gradient-to-r from-violet-500 to-purple-600 text-white font-black shadow-lg shadow-purple-500/40 scale-105' : 'bg-slate-900/80 hover:bg-slate-800 text-slate-300 border border-slate-800 font-bold'" class="px-4 py-2 rounded-xl transition-all duration-200 shrink-0 cursor-pointer">
                🤖 Teknologi / Robotik
            </button>
            <button type="button" @click="activeCategory = 'Pramuka'" :class="activeCategory === 'Pramuka' ? 'bg-gradient-to-r from-emerald-400 to-teal-500 text-slate-950 font-black shadow-lg shadow-emerald-500/40 scale-105' : 'bg-slate-900/80 hover:bg-slate-800 text-slate-300 border border-slate-800 font-bold'" class="px-4 py-2 rounded-xl transition-all duration-200 shrink-0 cursor-pointer">
                ⛺ Pramuka & Keagamaan
            </button>
        </div>

        <!-- Competition Cards Grid (Category-Dynamic Neon Glow) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
            @foreach($openCompetitions as $c)
                @php
                    $registeredReg = $registrations->firstWhere('competition_id', $c->id);
                    $isRegistered = !is_null($registeredReg);
                    $categoryName = $c->category->name ?? 'Lomba';
                    $catLower = strtolower($categoryName . ' ' . $c->name);

                    // Dynamic category color styling (Opsi 2)
                    if (str_contains($catLower, 'seni') || str_contains($catLower, 'pop') || str_contains($catLower, 'art') || str_contains($catLower, 'lukis') || str_contains($catLower, 'puisi')) {
                        // SENI: Pink / Rose Neon
                        $theme = [
                            'key' => 'Seni',
                            'borderHover' => 'hover:border-pink-500/80 hover:shadow-[0_0_35px_rgba(244,63,94,0.30)]',
                            'topStripe' => 'from-pink-500 via-rose-400 to-pink-500',
                            'badge' => 'bg-pink-500/20 text-pink-300 border-pink-500/40 group-hover:bg-pink-500/30',
                            'dot' => 'bg-pink-400',
                            'code' => 'group-hover:text-pink-400',
                            'title' => 'group-hover:text-pink-300',
                            'btnGrad' => 'bg-gradient-to-r from-pink-500 via-rose-500 to-pink-600 text-white shadow-[0_0_22px_rgba(244,63,94,0.55)] group-hover:shadow-[0_0_35px_rgba(244,63,94,0.90)]',
                            'feeText' => 'text-pink-400',
                        ];
                    } elseif (str_contains($catLower, 'olahraga') || str_contains($catLower, 'sport') || str_contains($catLower, 'tangkis') || str_contains($catLower, 'catur') || str_contains($catLower, 'tenis') || str_contains($catLower, 'lari') || str_contains($catLower, 'atletik')) {
                        // OLAHRAGA: Electric Amber / Orange Fire
                        $theme = [
                            'key' => 'Olahraga',
                            'borderHover' => 'hover:border-amber-500/80 hover:shadow-[0_0_35px_rgba(245,158,11,0.30)]',
                            'topStripe' => 'from-amber-400 via-orange-500 to-amber-500',
                            'badge' => 'bg-amber-500/20 text-amber-300 border-amber-500/40 group-hover:bg-amber-500/30',
                            'dot' => 'bg-amber-400',
                            'code' => 'group-hover:text-amber-400',
                            'title' => 'group-hover:text-amber-300',
                            'btnGrad' => 'bg-gradient-to-r from-amber-400 via-orange-500 to-amber-500 text-slate-950 shadow-[0_0_22px_rgba(245,158,11,0.55)] group-hover:shadow-[0_0_35px_rgba(245,158,11,0.90)]',
                            'feeText' => 'text-amber-400',
                        ];
                    } elseif (str_contains($catLower, 'olimpiade') || str_contains($catLower, 'akademik') || str_contains($catLower, 'sains') || str_contains($catLower, 'mipa') || str_contains($catLower, 'matematika') || str_contains($catLower, 'ipa') || str_contains($catLower, 'ips') || str_contains($catLower, 'inggris')) {
                        // OLIMPIADE & AKADEMIK: Hyper Cyan / Electric Blue
                        $theme = [
                            'key' => 'Olimpiade',
                            'borderHover' => 'hover:border-cyan-400/80 hover:shadow-[0_0_35px_rgba(6,182,212,0.30)]',
                            'topStripe' => 'from-cyan-400 via-blue-500 to-cyan-400',
                            'badge' => 'bg-cyan-500/20 text-cyan-300 border-cyan-500/40 group-hover:bg-cyan-500/30',
                            'dot' => 'bg-cyan-400',
                            'code' => 'group-hover:text-cyan-400',
                            'title' => 'group-hover:text-cyan-300',
                            'btnGrad' => 'bg-gradient-to-r from-cyan-400 via-sky-400 to-blue-500 text-slate-950 shadow-[0_0_22px_rgba(6,182,212,0.55)] group-hover:shadow-[0_0_35px_rgba(6,182,212,0.90)]',
                            'feeText' => 'text-cyan-400',
                        ];
                    } elseif (str_contains($catLower, 'teknologi') || str_contains($catLower, 'robotik') || str_contains($catLower, 'it') || str_contains($catLower, 'komputer') || str_contains($catLower, 'coding')) {
                        // TEKNOLOGI / ROBOTIK: Cyber Violet / Purple
                        $theme = [
                            'key' => 'Teknologi',
                            'borderHover' => 'hover:border-purple-500/80 hover:shadow-[0_0_35px_rgba(168,85,247,0.30)]',
                            'topStripe' => 'from-violet-500 via-purple-500 to-indigo-500',
                            'badge' => 'bg-purple-500/20 text-purple-300 border-purple-500/40 group-hover:bg-purple-500/30',
                            'dot' => 'bg-purple-400',
                            'code' => 'group-hover:text-purple-400',
                            'title' => 'group-hover:text-purple-300',
                            'btnGrad' => 'bg-gradient-to-r from-violet-500 via-purple-500 to-indigo-500 text-white shadow-[0_0_22px_rgba(168,85,247,0.55)] group-hover:shadow-[0_0_35px_rgba(168,85,247,0.90)]',
                            'feeText' => 'text-purple-400',
                        ];
                    } else {
                        // PRAMUKA / TAHFIDZ / KEAGAMAAN / DEFAULT: Luminous Emerald & Mint
                        $theme = [
                            'key' => 'Pramuka',
                            'borderHover' => 'hover:border-emerald-400/80 hover:shadow-[0_0_35px_rgba(16,185,129,0.30)]',
                            'topStripe' => 'from-emerald-400 via-teal-400 to-emerald-500',
                            'badge' => 'bg-emerald-500/20 text-emerald-300 border-emerald-500/40 group-hover:bg-emerald-500/30',
                            'dot' => 'bg-emerald-400',
                            'code' => 'group-hover:text-emerald-400',
                            'title' => 'group-hover:text-emerald-300',
                            'btnGrad' => 'bg-gradient-to-r from-emerald-400 via-teal-300 to-emerald-500 text-slate-950 shadow-[0_0_22px_rgba(16,185,129,0.55)] group-hover:shadow-[0_0_35px_rgba(16,185,129,0.90)]',
                            'feeText' => 'text-emerald-400',
                        ];
                    }

                    $targetUrl = $isRegistered 
                        ? route('peserta.registration.detail', $registeredReg->id) 
                        : route('peserta.register.competition', $c->slug);
                @endphp
                
                <a href="{{ $targetUrl }}" 
                   x-show="activeCategory === 'all' || activeCategory === '{{ $theme['key'] }}' || activeCategory === '{{ $categoryName }}'" 
                   x-transition:enter="transition ease-out duration-200" 
                   x-transition:enter-start="opacity-0 scale-95" 
                   x-transition:enter-end="opacity-100 scale-100" 
                   class="group relative rounded-3xl border {{ $isRegistered ? 'border-emerald-500/50 bg-[#131B2E]/90 hover:border-emerald-400' : 'border-slate-800/90 bg-[#131B2E]/80 hover:bg-[#18233D] ' . $theme['borderHover'] }} transition-all duration-300 flex flex-col justify-between hover:-translate-y-2 cursor-pointer block backdrop-blur-xl overflow-hidden">
                    
                    <!-- Top Glowing Accent Stripe -->
                    <div class="h-1.5 w-full bg-gradient-to-r {{ $theme['topStripe'] }} opacity-80 group-hover:opacity-100 transition-opacity"></div>

                    <div class="p-5 space-y-4 flex-1 flex flex-col justify-between">
                        
                        <div class="space-y-3">
                            <!-- Card Header: Category Badge & Code -->
                            <div class="flex items-center justify-between">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl text-[10px] font-black uppercase tracking-wider border {{ $theme['badge'] }} transition">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $theme['dot'] }}"></span>
                                    <span>{{ $categoryName }}</span>
                                </span>
                                <span class="font-mono text-xs font-bold text-slate-400 {{ $theme['code'] }} transition">
                                    {{ $c->code }}
                                </span>
                            </div>

                            <!-- Card Body: Competition Name & Quota -->
                            <div>
                                <h4 class="font-black text-white text-base leading-snug {{ $theme['title'] }} transition font-display">
                                    {{ $c->name }}
                                </h4>
                                <p class="text-xs text-slate-400 mt-1">
                                    @if($c->code === 'BLT')
                                        Kuota: <strong class="text-slate-200">{{ ($c->tier_quotas['A_tunggal_pa'] ?? 16) + ($c->tier_quotas['B_tunggal_pa'] ?? 16) + ($c->tier_quotas['C_tunggal_pa'] ?? 16) + ($c->tier_quotas['A_tunggal_pi'] ?? 16) + ($c->tier_quotas['B_tunggal_pi'] ?? 16) + ($c->tier_quotas['C_tunggal_pi'] ?? 16) }} Tunggal / {{ ($c->tier_quotas['ganda_pa'] ?? 10) + ($c->tier_quotas['ganda_pi'] ?? 10) }} Ganda</strong> • <span class="capitalize font-semibold text-slate-300">Tunggal & Ganda PA/PI</span>
                                    @elseif(in_array($c->code, ['MTQ', 'POP']))
                                        Kuota: <strong class="text-slate-200">{{ $c->tier_quotas['pa'] ?? ceil($c->quota / 2) }} PA / {{ $c->tier_quotas['pi'] ?? floor($c->quota / 2) }} PI</strong> • <span class="font-semibold text-slate-300">Individu PA & PI</span>
                                    @elseif($c->code === 'TMJ')
                                        Kuota: <strong class="text-slate-200">{{ ($c->tier_quotas['A_tunggal_pa'] ?? 10) + ($c->tier_quotas['B_tunggal_pa'] ?? 10) + ($c->tier_quotas['A_tunggal_pi'] ?? 10) + ($c->tier_quotas['B_tunggal_pi'] ?? 10) }} Total</strong> • <span class="font-semibold text-slate-300">Tunggal Kat A & B (PA/PI)</span>
                                    @elseif($c->isUnlimitedQuota())
                                        Kuota: <strong class="text-purple-300">∞ Tak Terbatas</strong> • Tipe: <span class="capitalize font-semibold text-slate-300">{{ $c->type }}</span>
                                    @else
                                        Kuota: <strong class="text-slate-200">{{ $c->quota }}</strong> • Tipe: <span class="capitalize font-semibold text-slate-300">{{ $c->type }}</span>
                                    @endif
                                </p>
                            </div>

                            <!-- Fee Box (High-Tech Contrast) -->
                            @if($c->code === 'BLT')
                                <div class="p-2.5 rounded-2xl bg-slate-950/80 border border-slate-800 text-xs space-y-1 transition">
                                    <div class="flex items-center justify-between border-b border-slate-800/80 pb-1">
                                        <span class="text-[10px] uppercase font-bold text-slate-400">Biaya Pendaftaran:</span>
                                        <span class="text-[9px] font-black {{ $theme['feeText'] }} bg-slate-900 px-1.5 py-0.2 rounded border border-slate-700">Tunggal & Ganda PA/PI</span>
                                    </div>
                                    <div class="flex items-center justify-between text-[10px]">
                                        <span class="text-slate-300">Tunggal Kat A (Kls 1–2):</span>
                                        <span class="font-bold {{ $theme['feeText'] }} font-mono">Rp {{ number_format($c->tier_fees['A_tunggal_pa'] ?? 100000, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="flex items-center justify-between text-[10px]">
                                        <span class="text-slate-300">Tunggal Kat B (Kls 3–4):</span>
                                        <span class="font-bold {{ $theme['feeText'] }} font-mono">Rp {{ number_format($c->tier_fees['B_tunggal_pa'] ?? 130000, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="flex items-center justify-between text-[10px]">
                                        <span class="text-slate-300">Tunggal Kat C (Kls 5–6):</span>
                                        <span class="font-bold {{ $theme['feeText'] }} font-mono">Rp {{ number_format($c->tier_fees['C_tunggal_pa'] ?? 130000, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="flex items-center justify-between text-[10px] border-t border-slate-800/80 pt-0.5">
                                        <span class="text-slate-300 font-semibold">Ganda (PA & PI):</span>
                                        <span class="font-bold {{ $theme['feeText'] }} font-mono">Rp {{ number_format($c->tier_fees['ganda_pa'] ?? 200000, 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            @elseif($c->code === 'TMJ')
                                <div class="p-2.5 rounded-2xl bg-slate-950/80 border border-slate-800 text-xs space-y-1 transition">
                                    <div class="flex items-center justify-between border-b border-slate-800/80 pb-1">
                                        <span class="text-[10px] uppercase font-bold text-slate-400">Biaya Pendaftaran:</span>
                                        <span class="text-[9px] font-black {{ $theme['feeText'] }} bg-slate-900 px-1.5 py-0.2 rounded border border-slate-700">Tunggal Kat A & B</span>
                                    </div>
                                    <div class="flex items-center justify-between text-[10px]">
                                        <span class="text-slate-300">Tunggal Kat A (Kls 1–3):</span>
                                        <span class="font-bold {{ $theme['feeText'] }} font-mono">Rp {{ number_format($c->tier_fees['A_tunggal_pa'] ?? 35000, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="flex items-center justify-between text-[10px]">
                                        <span class="text-slate-300">Tunggal Kat B (Kls 4–6):</span>
                                        <span class="font-bold {{ $theme['feeText'] }} font-mono">Rp {{ number_format($c->tier_fees['B_tunggal_pa'] ?? 35000, 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            @else
                                <div class="p-2.5 rounded-2xl bg-slate-950/80 border border-slate-800 text-xs flex items-center justify-between transition">
                                    <span class="text-[10px] uppercase font-bold text-slate-400">Biaya Pendaftaran:</span>
                                    <span class="font-black {{ $theme['feeText'] }} font-mono text-sm">
                                        {{ $c->registration_fee > 0 ? 'Rp ' . number_format($c->registration_fee, 0, ',', '.') : 'Gratis' }}
                                    </span>
                                </div>
                            @endif
                        </div>

                        <!-- Card Action CTA Button (Category-Dynamic Neon Glowing CTA) -->
                        <div class="pt-3 border-t border-slate-800/80">
                            @if($isRegistered)
                                <div class="w-full py-2.5 px-3 rounded-2xl bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 font-black text-xs flex items-center justify-between shadow-xs">
                                    <span class="flex items-center gap-1.5">
                                        <i data-lucide="check-circle" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                                        <span>Sudah Terdaftar</span>
                                    </span>
                                    <span class="text-[10px] text-emerald-300 font-bold underline">Lihat Berkas ➔</span>
                                </div>
                            @else
                                <div class="w-full py-3 px-4 rounded-2xl {{ $theme['btnGrad'] }} font-black text-xs flex items-center justify-center gap-2 tracking-wide uppercase group-hover:scale-[1.03] active:scale-[0.98] transition-all duration-300 cursor-pointer">
                                    <span>Daftar Cabang Ini</span>
                                    <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                                </div>
                            @endif
                        </div>

                    </div>
                </a>
            @endforeach
        </div>
    </div>

</div>
@endsection
