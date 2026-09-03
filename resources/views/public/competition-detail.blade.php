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

            <!-- Petunjuk Teknis & Aturan Lomba -->
            <div class="glass-card rounded-3xl p-6 sm:p-8 border border-white/[0.08] shadow-2xl space-y-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-[#7A5AF8]/15 text-[#A594FD] border border-[#7A5AF8]/30 flex items-center justify-center">
                        <i data-lucide="book-open-check" class="w-5 h-5"></i>
                    </div>
                    <h2 class="text-lg sm:text-xl font-black text-white font-display">Ketentuan & Peraturan Lomba</h2>
                </div>

                <div class="text-xs sm:text-sm text-slate-300 whitespace-pre-line leading-relaxed bg-[#0C111D]/80 p-5 sm:p-6 rounded-2xl border border-white/[0.08]">
                    {{ $competition->rules ?? "1. Peserta merupakan siswa SD/MI atau SMP/MTs yang telah terdaftar resmi.\n2. Mengenakan seragam atau busana sesuai cabang lomba.\n3. Mengikuti tata tertib dan keputusan dewan juri bersifat mutlak." }}
                </div>
            </div>

            @if($competition->guidelines_embed_url)
            <!-- Petunjuk Teknis Lengkap (Juknis PDF Embed) -->
            <div class="glass-card rounded-3xl p-6 sm:p-8 border border-white/[0.08] shadow-2xl space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-white/[0.08] pb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-rose-500/15 text-rose-400 border border-rose-500/30 flex items-center justify-center">
                            <i data-lucide="file-text" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h2 class="text-lg sm:text-xl font-black text-white font-display">Petunjuk Teknis (Juknis Resmi)</h2>
                            <p class="text-xs text-slate-400">Dokumen panduan, regulasi, dan petunjuk operasional pelaksanaan perlombaan</p>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        @if(!empty($competition->whatsapp_group_url))
                            <a href="{{ $competition->whatsapp_group_url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-slate-950 text-xs font-black transition shadow-lg shadow-emerald-500/20">
                                <i data-lucide="message-circle" class="w-4 h-4"></i>
                                <span>Grup WhatsApp</span>
                            </a>
                        @endif
                        <a href="{{ $competition->guidelines_download_url ?? $competition->guidelines_embed_url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-white/[0.05] hover:bg-white/[0.1] text-slate-200 text-xs font-bold transition border border-white/[0.08]">
                            <i data-lucide="external-link" class="w-3.5 h-3.5 text-[#84D0FF]"></i>
                            <span>Buka di Tab Baru</span>
                        </a>
                        <a href="{{ $competition->guidelines_download_url ?? $competition->guidelines_embed_url }}" target="_blank" rel="noopener noreferrer" download class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-rose-500/20 hover:bg-rose-500/30 text-rose-300 border border-rose-500/30 text-xs font-bold transition">
                            <i data-lucide="download" class="w-3.5 h-3.5"></i>
                            <span>Unduh PDF</span>
                        </a>
                    </div>
                </div>

                <!-- PDF Embed Frame -->
                <div class="relative w-full rounded-2xl overflow-hidden border border-white/[0.08] bg-[#0C111D] shadow-inner">
                    <iframe src="{{ $competition->guidelines_embed_url }}" 
                            class="w-full h-[600px] sm:h-[750px] border-0 rounded-2xl bg-white" 
                            allow="autoplay"
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
                <div>
                    <span class="text-xs font-bold text-[#A594FD] uppercase tracking-wider">Status Perlombaan</span>
                    <div class="flex items-center justify-between mt-1">
                        <span class="text-2xl font-black text-white capitalize font-display">{{ $competition->status }}</span>
                        <span class="px-3 py-1 text-xs font-black rounded-full {{ $competition->status === 'buka' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/40' : 'bg-rose-500/20 text-rose-300 border border-rose-500/40' }}">
                            {{ $competition->status === 'buka' ? 'Pendaftaran Dibuka' : 'Ditutup' }}
                        </span>
                    </div>
                </div>

                <div class="space-y-3 text-xs text-slate-300 border-t border-white/[0.08] pt-4">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">Biaya Pendaftaran:</span>
                        @if($competition->code === 'BLT')
                            <span class="font-bold text-xs text-amber-300 text-right">Rp 130k – Rp 200k (Tunggal & Ganda PA/PI)</span>
                        @else
                            <span class="font-bold text-sm text-white">{{ $competition->registration_fee > 0 ? 'Rp ' . number_format($competition->registration_fee, 0, ',', '.') : 'GRATIS' }}</span>
                        @endif
                    </div>
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

                <a href="{{ route('peserta.register.competition', $competition->slug) }}" class="w-full flex items-center justify-center gap-2 py-4 px-6 rounded-2xl btn-gradient text-white font-black text-sm shadow-xl shadow-[#7A5AF8]/30 hover:scale-[1.02] active:scale-[0.98] transition duration-200">
                    <i data-lucide="edit-3" class="w-5 h-5"></i>
                    <span>Daftar Cabang Lomba Ini</span>
                </a>

                @if(!empty($competition->whatsapp_group_url))
                    <a href="{{ $competition->whatsapp_group_url }}" target="_blank" rel="noopener noreferrer" class="w-full flex items-center justify-center gap-2 py-3 px-4 rounded-2xl bg-emerald-500/15 hover:bg-emerald-500 text-emerald-400 hover:text-slate-950 font-bold text-xs border border-emerald-500/30 hover:border-emerald-500 transition duration-200">
                        <i data-lucide="message-circle" class="w-4 h-4"></i>
                        <span>Gabung Grup WhatsApp Resmi</span>
                    </a>
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
            <div class="glass-card rounded-3xl p-6 border border-white/[0.08] shadow-2xl text-center space-y-3">
                <p class="text-xs font-bold text-slate-300">Butuh Bantuan Mengenai Cabang Lomba Ini?</p>
                <a href="https://wa.me/6281234567890?text=Halo%20Panitia%20TALENTA,%20saya%20ingin%20bertanya%20tentang%20lomba%20{{ urlencode($competition->name) }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 text-xs font-black transition shadow-lg shadow-emerald-500/20">
                    <i data-lucide="message-circle" class="w-4 h-4"></i>
                    <span>Chat WhatsApp Koordinator</span>
                </a>
            </div>

        </div>

    </div>

</div>
@endsection
