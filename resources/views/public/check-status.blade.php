@extends('layouts.app')

@section('title', 'Cek Status Pendaftaran & Nomor Peserta ' . ($appSettings['app_name'] ?? 'TALENTA 2026'))

@section('content')
<div class="py-16 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
    
    <!-- Hero Header -->
    <div class="text-center space-y-4 max-w-2xl mx-auto">
        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-black tracking-widest uppercase shadow-lg shadow-emerald-500/10">
            <i data-lucide="search-check" class="w-4 h-4"></i>
            <span>TRACKING SISTEM {{ $appSettings['app_name'] ?? 'TALENTA 2026' }}</span>
        </div>
        
        <h1 class="text-3xl sm:text-5xl font-black text-white tracking-tight font-display">
            Cek Status Pendaftaran & <span class="text-gradient">Nomor Tampil</span>
        </h1>
        
        <p class="text-sm sm:text-base text-slate-400 leading-relaxed font-normal">
            Masukkan <strong class="text-slate-200">Kode Registrasi</strong>, <strong class="text-slate-200">Nama Peserta</strong>, atau <strong class="text-slate-200">Nama Sekolah</strong> untuk mengecek hasil verifikasi berkas dan nomor urut undian tampil.
        </p>
    </div>

    <!-- Search Form Bar -->
    <div class="glass-card p-3 sm:p-4 rounded-3xl border border-slate-700/80 shadow-2xl relative z-10">
        <form action="{{ route('check.status') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                    <i data-lucide="search" class="w-5 h-5 text-emerald-400"></i>
                </div>
                <input type="text" name="q" value="{{ $query }}" placeholder="Ketik Kode Registrasi (misal: REG-2026-MTQ-001) / Nama Peserta / Sekolah..." class="block w-full pl-12 pr-4 py-4 rounded-2xl bg-slate-900/90 border border-slate-700/80 text-white placeholder-slate-500 text-sm font-medium focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 outline-none transition">
            </div>
            <button type="submit" class="inline-flex items-center justify-center gap-2.5 px-8 py-4 rounded-2xl btn-gradient text-slate-950 font-black text-sm uppercase tracking-wider shadow-xl shadow-emerald-500/20 hover:scale-105 transition duration-300 shrink-0">
                <i data-lucide="search" class="w-4 h-4"></i>
                <span>Cari Status</span>
            </button>
        </form>
    </div>

    <!-- Search Results Section -->
    @if($query)
        <div class="space-y-6">
            
            <div class="flex items-center justify-between px-2">
                <div>
                    <h3 class="text-xs font-black uppercase tracking-wider text-slate-400">Hasil Pencarian Untuk:</h3>
                    <p class="text-lg font-black text-white font-mono">"{{ $query }}"</p>
                </div>
                <span class="px-3.5 py-1.5 rounded-xl bg-slate-800/80 border border-slate-700 text-xs font-bold text-emerald-400">
                    {{ $results->count() }} Data Ditemukan
                </span>
            </div>

            @forelse($results as $reg)
                <div class="glass-card p-6 sm:p-8 rounded-3xl border border-slate-700/80 shadow-2xl hover:border-emerald-500/50 transition duration-300 space-y-6">
                    
                    <!-- Card Top Bar -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-800 pb-6">
                        <div class="space-y-1.5">
                            <div class="flex items-center gap-2.5 flex-wrap">
                                <span class="text-xs font-mono font-black text-emerald-400 bg-slate-900 px-3 py-1 rounded-xl border border-emerald-500/40">
                                    {{ $reg->registration_code }}
                                </span>
                                <span class="px-2.5 py-0.5 rounded-lg bg-slate-800 text-slate-400 text-[10px] font-bold uppercase tracking-wider">
                                    {{ $reg->competition->category->name ?? 'Cabang Lomba' }}
                                </span>
                            </div>
                            <h4 class="text-xl sm:text-2xl font-black text-white font-display">{{ $reg->display_name }}</h4>
                            <p class="text-xs sm:text-sm text-slate-400 flex items-center gap-1.5">
                                <i data-lucide="school" class="w-4 h-4 text-blue-400 shrink-0"></i>
                                <span>{{ $reg->institution_name }}</span>
                            </p>
                        </div>
                        
                        <!-- Status Badge -->
                        <div class="shrink-0">
                            @if($reg->status === 'verified')
                                <span class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl text-xs font-black uppercase tracking-wider bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 shadow-lg shadow-emerald-500/10">
                                    <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-400"></i>
                                    <span>Terverifikasi Sah</span>
                                </span>
                            @elseif($reg->status === 'revision')
                                <span class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl text-xs font-black uppercase tracking-wider bg-amber-500/20 text-amber-300 border border-amber-500/40 shadow-lg shadow-amber-500/10">
                                    <i data-lucide="alert-triangle" class="w-4 h-4 text-amber-400"></i>
                                    <span>Perlu Revisi Berkas</span>
                                </span>
                            @elseif($reg->status === 'rejected')
                                <span class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl text-xs font-black uppercase tracking-wider bg-rose-500/20 text-rose-300 border border-rose-500/40 shadow-lg shadow-rose-500/10">
                                    <i data-lucide="x-circle" class="w-4 h-4 text-rose-400"></i>
                                    <span>Berkas Ditolak</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl text-xs font-black uppercase tracking-wider bg-slate-800 text-slate-300 border border-slate-700 shadow-sm">
                                    <i data-lucide="clock" class="w-4 h-4 text-amber-400"></i>
                                    <span>Menunggu Verifikasi</span>
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Metadata Grid -->
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 text-xs">
                        
                        <div class="p-4 rounded-2xl bg-slate-900/90 border border-slate-800/80 space-y-1">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 block">Cabang Lomba</span>
                            <span class="font-extrabold text-white text-sm block truncate">{{ $reg->competition->name }}</span>
                        </div>

                        <div class="p-4 rounded-2xl bg-slate-900/90 border border-slate-800/80 space-y-1">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 block">Nomor Peserta</span>
                            <span class="font-mono font-black text-sm block {{ $reg->participant_number ? 'text-emerald-400' : 'text-slate-500' }}">
                                {{ $reg->participant_number ?? 'Belum Diterbitkan' }}
                            </span>
                        </div>

                        <div class="p-4 rounded-2xl bg-slate-900/90 border border-slate-800/80 space-y-1">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 block">Nomor Undian Tampil</span>
                            @if($reg->draw_number)
                                <span class="font-mono font-black text-sm text-amber-400 flex items-center gap-1.5">
                                    <i data-lucide="disc" class="w-3.5 h-3.5 text-amber-400"></i>
                                    <span>No. Urut {{ $reg->draw_number }}</span>
                                </span>
                            @else
                                <span class="font-bold text-slate-500 text-sm block">Menunggu Technical Meeting</span>
                            @endif
                        </div>

                        <div class="p-4 rounded-2xl bg-slate-900/90 border border-slate-800/80 space-y-1">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 block">Lokasi & Venue</span>
                            <span class="font-bold text-blue-400 text-sm block truncate">
                                {{ $reg->competition->venue ?? 'Kampus MTsN 1 Blitar' }}
                            </span>
                        </div>

                    </div>

                    <!-- Members / Participants List if Available -->
                    @if($reg->members->isNotEmpty())
                        <div class="pt-4 border-t border-slate-800/80">
                            <h5 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Daftar Anggota / Delegasi:</h5>
                            <div class="flex flex-wrap gap-2">
                                @foreach($reg->members as $m)
                                    <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-900 border border-slate-800 text-xs font-medium text-slate-300">
                                        <i data-lucide="user" class="w-3.5 h-3.5 text-emerald-400"></i>
                                        <span>{{ $m->full_name }}</span>
                                        @if($m->nisn)
                                            <span class="text-[10px] font-mono text-slate-500">({{ $m->nisn }})</span>
                                        @endif
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                </div>
            @empty
                <div class="glass-card p-12 rounded-3xl border border-slate-800 text-center space-y-4 max-w-md mx-auto">
                    <div class="w-16 h-16 mx-auto rounded-3xl bg-slate-900 border border-slate-800 text-slate-400 flex items-center justify-center font-bold">
                        <i data-lucide="file-question" class="w-8 h-8 text-amber-400"></i>
                    </div>
                    <div class="space-y-1">
                        <h4 class="text-lg font-black text-white">Data Tidak Ditemukan</h4>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            Tidak ada peserta yang cocok dengan kata kunci <strong class="text-white">"{{ $query }}"</strong>. Pastikan ejaan nama peserta, asal sekolah, atau kode registrasi sudah benar.
                        </p>
                    </div>
                </div>
            @endforelse
        </div>
    @else
        <!-- Initial Guidance Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-4">
            
            <div class="glass-card p-6 rounded-3xl border border-slate-800 space-y-3">
                <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 flex items-center justify-center font-black">
                    <i data-lucide="qr-code" class="w-6 h-6"></i>
                </div>
                <h3 class="text-base font-black text-white">Kode Registrasi</h3>
                <p class="text-xs text-slate-400 leading-relaxed">
                    Gunakan kode registrasi resmi yang Anda dapatkan saat mendaftar (contoh: <span class="text-emerald-400 font-mono">REG-2026-MTQ-001</span>).
                </p>
            </div>

            <div class="glass-card p-6 rounded-3xl border border-slate-800 space-y-3">
                <div class="w-12 h-12 rounded-2xl bg-blue-500/10 border border-blue-500/30 text-blue-400 flex items-center justify-center font-black">
                    <i data-lucide="disc" class="w-6 h-6"></i>
                </div>
                <h3 class="text-base font-black text-white">Undian Nomor Tampil</h3>
                <p class="text-xs text-slate-400 leading-relaxed">
                    Nomor urut giliran tampil akan otomatis muncul di sini setelah sesi pengundian Interactive Spin Wheel selesai diputar oleh PIC.
                </p>
            </div>

            <div class="glass-card p-6 rounded-3xl border border-slate-800 space-y-3">
                <div class="w-12 h-12 rounded-2xl bg-amber-500/10 border border-amber-500/30 text-amber-400 flex items-center justify-center font-black">
                    <i data-lucide="tv" class="w-6 h-6"></i>
                </div>
                <h3 class="text-base font-black text-white">Pantau Live Scoring</h3>
                <p class="text-xs text-slate-400 leading-relaxed">
                    Peserta yang telah terverifikasi sah dapat dipantau nilai dan podium perolehan skornya langsung melalui Live Scoreboard TV.
                </p>
            </div>

        </div>
    @endif

</div>
@endsection
