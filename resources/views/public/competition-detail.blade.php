@extends('layouts.app')

@section('title', $competition->name . ' - Juknis & Pendaftaran ' . ($appSettings['app_name'] ?? 'TALENTA 2026'))

@section('content')
<div class="py-12 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    
    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-xs font-semibold text-slate-400 mb-8">
        <a href="{{ route('home') }}" class="hover:text-[#A594FD] transition">Beranda</a>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-500"></i>
        <a href="{{ route('home') }}#kategori" class="hover:text-[#A594FD] transition">{{ $competition->category->name }}</a>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-500"></i>
        <span class="text-white font-bold">{{ $competition->name }}</span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left: Main Details -->
        <div class="lg:col-span-2 space-y-8">
            
            <!-- Title Card -->
            <div class="glass-card rounded-3xl p-6 sm:p-8 border border-white/[0.08] shadow-2xl space-y-4">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="px-3 py-1 text-xs font-bold rounded-xl bg-[#7A5AF8]/15 text-[#A594FD] border border-[#7A5AF8]/30">
                        {{ $competition->category->name }}
                    </span>
                    <span class="px-3 py-1 text-xs font-bold rounded-xl bg-[#4E6EFF]/15 text-[#84D0FF] border border-[#4E6EFF]/30 uppercase font-mono">
                        Kode: {{ $competition->code }}
                    </span>
                    <span class="px-3 py-1 text-xs font-bold rounded-xl {{ $competition->type === 'individu' ? 'bg-amber-500/15 text-amber-300 border border-amber-500/30' : 'bg-purple-500/15 text-purple-300 border border-purple-500/30' }}">
                        @if($competition->code === 'BLT')
                            Kategori Tunggal & Ganda (1–2 Siswa)
                        @else
                            {{ $competition->type === 'individu' ? 'Kategori Individu' : 'Kategori ' . ucfirst($competition->type) . ' (' . $competition->min_members . '-' . $competition->max_members . ' Siswa)' }}
                        @endif
                    </span>
                </div>

                <h1 class="text-2xl sm:text-4xl font-black text-white tracking-tight font-display">
                    {{ $competition->name }}
                </h1>

                <p class="text-xs sm:text-sm text-slate-300 leading-relaxed">
                    {{ $competition->category->description }}
                </p>
            </div>

            @if($competition->show_rules && !empty($competition->rules))
            <!-- Petunjuk Teknis & Aturan Lomba -->
            <div class="glass-card rounded-3xl p-6 sm:p-8 border border-white/[0.08] shadow-2xl space-y-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-[#7A5AF8]/15 text-[#A594FD] border border-[#7A5AF8]/30 flex items-center justify-center">
                        <i data-lucide="book-open-check" class="w-5 h-5"></i>
                    </div>
                    <h2 class="text-lg sm:text-xl font-black text-white font-display">Ketentuan & Peraturan Lomba</h2>
                </div>

                <div class="text-xs sm:text-sm text-slate-300 whitespace-pre-line leading-relaxed bg-[#0C111D]/80 p-5 sm:p-6 rounded-2xl border border-white/[0.08]">
                    {{ $competition->rules }}
                </div>
            </div>
            @endif

            @if(($competition->show_guidelines ?? true) && $competition->guidelines_embed_url)
            <!-- Petunjuk Teknis Lengkap (Juknis PDF Embed) -->
            <div class="glass-card rounded-3xl p-6 sm:p-8 border border-white/[0.08] shadow-2xl space-y-4">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b border-white/[0.08] pb-4">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-10 h-10 rounded-2xl bg-rose-500/15 text-rose-400 border border-rose-500/30 flex items-center justify-center shrink-0">
                            <i data-lucide="file-text" class="w-5 h-5"></i>
                        </div>
                        <div class="min-w-0">
                            <h2 class="text-lg sm:text-xl font-black text-white font-display">Petunjuk Teknis (Juknis Resmi)</h2>
                            <p class="text-xs text-slate-400">Dokumen panduan, regulasi, dan petunjuk operasional pelaksanaan perlombaan</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2.5 shrink-0 flex-nowrap">
                        <a href="{{ $competition->guidelines_download_url ?? $competition->guidelines_embed_url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-white/[0.05] hover:bg-white/[0.1] text-slate-200 text-xs font-bold transition border border-white/[0.08] whitespace-nowrap">
                            <i data-lucide="external-link" class="w-3.5 h-3.5 text-[#84D0FF]"></i>
                            <span>Buka di Tab Baru</span>
                        </a>
                        <a href="{{ $competition->guidelines_download_url ?? $competition->guidelines_embed_url }}" target="_blank" rel="noopener noreferrer" download class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-rose-500/20 hover:bg-rose-500/30 text-rose-300 border border-rose-500/30 text-xs font-bold transition whitespace-nowrap">
                            <i data-lucide="download" class="w-3.5 h-3.5"></i>
                            <span>Unduh PDF</span>
                        </a>
                    </div>
                </div>

                <!-- PDF Embed Frame -->
                <div class="relative w-full rounded-2xl overflow-hidden border border-white/[0.08] bg-[#0C111D] shadow-inner">
                    <iframe src="{{ $competition->guidelines_embed_url }}" 
                            class="w-full h-[600px] sm:h-[750px] border-0 rounded-2xl bg-white" 
                            allow="autoplay; fullscreen"
                            loading="lazy">
                    </iframe>
                </div>
            </div>
            @endif

            @if($competition->show_criteria && $competition->criteria->isNotEmpty())
            <!-- Kriteria Penilaian Dewan Juri -->
            <div class="glass-card rounded-3xl p-6 sm:p-8 border border-white/[0.08] shadow-2xl space-y-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-amber-500/15 text-amber-400 border border-amber-500/30 flex items-center justify-center">
                        <i data-lucide="scale" class="w-5 h-5"></i>
                    </div>
                    <h2 class="text-lg sm:text-xl font-black text-white font-display">Kriteria Penilaian Dewan Juri</h2>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @foreach($competition->criteria as $crit)
                        <div class="p-4 rounded-2xl bg-[#0C111D]/80 border border-white/[0.08] flex items-start justify-between">
                            <div>
                                <h4 class="text-sm font-bold text-slate-200">{{ $crit->name }}</h4>
                                <p class="text-xs text-slate-400 mt-1">{{ $crit->description ?? 'Rentang nilai ' . $crit->min_score . ' - ' . $crit->max_score }}</p>
                            </div>
                            <span class="px-2.5 py-1 text-xs font-black rounded-lg bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 shrink-0">
                                {{ $crit->weight_percentage }}%
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

        </div>

        <!-- Right: Meta, Schedule, & Register Action -->
        <div class="space-y-6">
            
            <!-- Action Card -->
            <div class="glass-card bg-gradient-to-br from-[#161F30] to-[#1e293b]/90 rounded-3xl p-6 sm:p-8 text-white border border-white/[0.1] shadow-2xl space-y-6">
                @php
                    $regInfo = \App\Models\AppSetting::getRegistrationStatusInfo();
                    $isCompOpen = ($competition->status === 'buka') && $regInfo['is_open'];
                @endphp
                <div>
                    <span class="text-xs font-bold text-[#A594FD] uppercase tracking-wider">Status Pendaftaran</span>
                    <div class="flex items-center justify-between mt-1">
                        <span class="text-xl font-black text-white capitalize font-display">{{ $isCompOpen ? 'Dibuka' : 'Ditutup' }}</span>
                        <span class="px-3 py-1 text-xs font-black rounded-full {{ $isCompOpen ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/40' : 'bg-rose-500/20 text-rose-300 border border-rose-500/40' }}">
                            {{ $isCompOpen ? 'Pendaftaran Dibuka' : 'Ditutup' }}
                        </span>
                    </div>
                </div>

                <div class="space-y-3 text-xs text-slate-300 border-t border-white/[0.08] pt-4">
                    @if(!empty($regInfo['deadline_formatted']))
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">Batas Pendaftaran:</span>
                        <span class="font-bold text-xs text-amber-300">{{ $regInfo['deadline_formatted'] }} WIB</span>
                    </div>
                    @endif
                    <div class="flex items-start justify-between gap-2">
                        <span class="text-slate-400 shrink-0">Biaya Pendaftaran:</span>
                        <span class="font-bold text-sm {{ in_array($competition->code, ['BLT', 'TMJ']) ? 'text-amber-300' : 'text-white' }} text-right">{{ $competition->fee_display }}</span>
                    </div>

                    @if($competition->code === 'BLT')
                        <div class="p-3 rounded-2xl bg-black/40 border border-white/[0.06] text-[11px] space-y-1.5 mt-1">
                            <div class="flex items-center justify-between text-slate-300">
                                <span>Tunggal Kat A (Kls 1–2):</span>
                                <span class="font-bold font-mono text-emerald-400">Rp {{ number_format($competition->tier_fees['A_tunggal_pa'] ?? 100000, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex items-center justify-between text-slate-300">
                                <span>Tunggal Kat B (Kls 3–4):</span>
                                <span class="font-bold font-mono text-emerald-400">Rp {{ number_format($competition->tier_fees['B_tunggal_pa'] ?? 130000, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex items-center justify-between text-slate-300">
                                <span>Tunggal Kat C (Kls 5–6):</span>
                                <span class="font-bold font-mono text-emerald-400">Rp {{ number_format($competition->tier_fees['C_tunggal_pa'] ?? 150000, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex items-center justify-between text-slate-300 border-t border-white/[0.06] pt-1">
                                <span>Ganda (PA & PI):</span>
                                <span class="font-bold font-mono text-[#84D0FF]">Rp {{ number_format($competition->tier_fees['ganda_pa'] ?? 200000, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    @elseif($competition->code === 'TMJ')
                        <div class="p-3 rounded-2xl bg-black/40 border border-white/[0.06] text-[11px] space-y-1.5 mt-1">
                            <div class="flex items-center justify-between text-slate-300">
                                <span>Tunggal Kat A (Kls 1–3 SD/MI):</span>
                                <span class="font-bold font-mono text-emerald-400">Rp {{ number_format($competition->tier_fees['A_tunggal_pa'] ?? 35000, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex items-center justify-between text-slate-300">
                                <span>Tunggal Kat B (Kls 4–6 SD/MI):</span>
                                <span class="font-bold font-mono text-emerald-400">Rp {{ number_format($competition->tier_fees['B_tunggal_pa'] ?? 40000, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    @elseif(in_array($competition->code, ['MTQ', 'POP']) && ($competition->tier_fees['pa'] ?? 0) != ($competition->tier_fees['pi'] ?? 0))
                        <div class="p-3 rounded-2xl bg-black/40 border border-white/[0.06] text-[11px] space-y-1.5 mt-1">
                            <div class="flex items-center justify-between text-slate-300">
                                <span>Putra (PA):</span>
                                <span class="font-bold font-mono text-emerald-400">Rp {{ number_format($competition->tier_fees['pa'] ?? $competition->registration_fee, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex items-center justify-between text-slate-300">
                                <span>Putri (PI):</span>
                                <span class="font-bold font-mono text-pink-400">Rp {{ number_format($competition->tier_fees['pi'] ?? $competition->registration_fee, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    @endif
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">Kuota Peserta:</span>
                        <span class="font-bold text-sm text-white text-right">{{ $competition->quota_display }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">Peserta Terverifikasi:</span>
                        @php
                            $verifiedUnit = match(strtolower($competition->type)) {
                                'regu' => 'Regu',
                                'tim' => 'Tim',
                                'kelompok' => 'Kelompok',
                                default => 'Peserta',
                            };
                        @endphp
                        <span class="font-bold text-sm text-[#A594FD]">{{ $verifiedCount }} {{ $verifiedUnit }}</span>
                    </div>
                </div>

                @if($isCompOpen)
                    <a href="{{ route('peserta.register.competition', $competition->slug) }}" class="w-full flex items-center justify-center gap-2 py-4 px-6 rounded-2xl btn-gradient text-white font-black text-sm shadow-xl shadow-[#7A5AF8]/30 hover:scale-[1.02] active:scale-[0.98] transition duration-200">
                        <i data-lucide="edit-3" class="w-5 h-5"></i>
                        <span>Daftar Cabang Lomba Ini</span>
                    </a>
                @else
                    @if($regInfo['status_code'] === 'not_started')
                        <div class="w-full flex items-center justify-center gap-2 py-3.5 px-6 rounded-2xl bg-amber-500/10 text-amber-300 border border-amber-500/30 font-bold text-xs shadow-sm">
                            <i data-lucide="clock" class="w-4 h-4 text-amber-400"></i>
                            <span>Belum Dibuka (Terjadwal: {{ $regInfo['start_date_formatted'] ?: '-' }} WIB)</span>
                        </div>
                    @else
                        <div class="w-full flex items-center justify-center gap-2 py-3.5 px-6 rounded-2xl bg-slate-800/80 text-slate-400 border border-slate-700 font-bold text-xs">
                            <i data-lucide="lock" class="w-4 h-4 text-rose-400"></i>
                            <span>Pendaftaran Ditutup</span>
                        </div>
                    @endif
                @endif
            </div>

            <!-- Schedule Card -->
            <div class="glass-card rounded-3xl p-6 border border-white/[0.08] shadow-2xl space-y-4">
                <h3 class="text-sm font-bold uppercase tracking-wider text-white flex items-center gap-2 font-display">
                    <i data-lucide="clock" class="w-4 h-4 text-[#7A5AF8]"></i>
                    <span>Informasi Pelaksanaan</span>
                </h3>

                <ul class="space-y-3 text-xs text-slate-300">
                    <li class="flex items-start gap-2.5">
                        <i data-lucide="map-pin" class="w-4 h-4 text-[#4E6EFF] shrink-0 mt-0.5"></i>
                        <div>
                            <p class="font-bold text-white">Lokasi / Venue</p>
                            <p class="text-slate-400">{{ $competition->venue ?? 'Kampus MTsN 1 Blitar' }}</p>
                        </div>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <i data-lucide="calendar" class="w-4 h-4 text-amber-400 shrink-0 mt-0.5"></i>
                        <div>
                            <p class="font-bold text-white">Tanggal Pelaksanaan</p>
                            <p class="text-slate-400">{{ $competition->schedule_date ? $competition->schedule_date->format('d F Y') : '15 - 17 September 2026' }}</p>
                        </div>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <i data-lucide="clock" class="w-4 h-4 text-[#FF58D5] shrink-0 mt-0.5"></i>
                        <div>
                            <p class="font-bold text-white">Waktu Lomba</p>
                            <p class="text-slate-400">{{ $competition->schedule_time ?? '08:00 WIB s.d Selesai' }}</p>
                        </div>
                    </li>
                </ul>
            </div>

            <!-- Contact Coordinator -->
            @php
                $picPhone = $competition->all_pic_phones[0] ?? ($competition->pic?->phone ?? \App\Models\AppSetting::get('whatsapp_admin_number', '6281234567890'));
                $cleanPicPhone = preg_replace('/[^0-9]/', '', (string)$picPhone);
                if (str_starts_with($cleanPicPhone, '0')) {
                    $cleanPicPhone = '62' . substr($cleanPicPhone, 1);
                } elseif (str_starts_with($cleanPicPhone, '8')) {
                    $cleanPicPhone = '628' . substr($cleanPicPhone, 1);
                }
            @endphp
            <div class="glass-card rounded-3xl p-6 border border-white/[0.08] shadow-2xl text-center space-y-3">
                <p class="text-xs font-bold text-slate-300">Butuh Bantuan Mengenai Cabang Lomba Ini?</p>
                <a href="https://wa.me/{{ $cleanPicPhone }}?text=Halo%20Panitia%20TALENTA,%20saya%20ingin%20bertanya%20tentang%20lomba%20{{ urlencode($competition->name) }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 text-xs font-black transition shadow-lg shadow-emerald-500/20">
                    <i data-lucide="message-circle" class="w-4 h-4"></i>
                    <span>Chat WhatsApp Koordinator</span>
                </a>
            </div>

        </div>

    </div>

</div>
@endsection
