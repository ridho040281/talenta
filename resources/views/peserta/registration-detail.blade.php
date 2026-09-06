@extends('layouts.admin')

@section('title', 'Detail Pendaftaran ' . $registration->registration_code)
@section('page_title', 'Rincian Data Pendaftaran')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    
    <!-- Top Action & Status Bar -->
    <div class="glass-card rounded-3xl p-5 sm:p-6 border border-slate-800 shadow-xl flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="space-y-1">
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs font-mono font-bold text-white bg-slate-800 px-2.5 py-0.5 rounded-lg border border-slate-700">
                    {{ $registration->registration_code }}
                </span>
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                    {{ $registration->competition->category->name ?? 'Lomba' }}
                </span>
                @if($registration->sub_category)
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                        {{ $registration->sub_category }}
                    </span>
                @endif
                @if($registration->chosen_song)
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded text-[10px] font-black tracking-wider bg-purple-500/20 text-purple-300 border border-purple-500/30 shadow-sm">
                        <i data-lucide="music" class="w-3 h-3 text-purple-400"></i>
                        <span>Lagu: {{ $registration->chosen_song }}</span>
                    </span>
                @endif
            </div>
            
            <h2 class="text-xl sm:text-2xl font-black text-white font-display">{{ $registration->competition->name }}</h2>
            
            <div class="flex items-center gap-1.5 text-xs text-slate-400">
                <i data-lucide="school" class="w-3.5 h-3.5 text-slate-500"></i>
                <span class="font-medium text-slate-300">{{ $registration->institution_name }}</span>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            @if($registration->status === 'verified')
                <a href="{{ route('document.print.registration', $registration->id) }}" target="_blank" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black text-xs shadow-lg shadow-emerald-500/20 transition">
                    <i data-lucide="printer" class="w-4 h-4"></i>
                    <span>Cetak Formulir</span>
                </a>
                <a href="{{ route('document.print.receipt', $registration->id) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-amber-500/20 hover:bg-amber-500/30 text-amber-300 border border-amber-500/30 font-bold text-xs transition">
                    <i data-lucide="receipt" class="w-4 h-4"></i>
                    <span>Kwitansi</span>
                </a>
            @endif
            <a href="{{ route('peserta.registrations') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 font-bold text-xs transition">
                <i data-lucide="arrow-left" class="w-4 h-4 text-slate-400"></i>
                <span>Kembali</span>
            </a>
        </div>
    </div>

    <!-- Status Banner Alert -->
    @if($registration->status === 'verified')
        <div class="bg-emerald-500/10 border border-emerald-500/30 rounded-2xl p-4 sm:p-5 flex items-start gap-3.5 backdrop-blur-xl">
            <div class="w-9 h-9 rounded-xl bg-emerald-500 text-slate-950 flex items-center justify-center shrink-0 shadow-md shadow-emerald-500/20">
                <i data-lucide="check-circle" class="w-5 h-5"></i>
            </div>
            <div class="space-y-1">
                <h4 class="text-xs sm:text-sm font-bold text-emerald-300">Pendaftaran Telah Diverifikasi & Sah</h4>
                <p class="text-xs text-slate-300 leading-relaxed">
                    Berkas pendaftaran Anda telah disetujui. Nomor Peserta resmi: <strong class="font-mono text-emerald-300 bg-emerald-500/20 px-1.5 py-0.5 rounded border border-emerald-500/30">{{ $registration->participant_number }}</strong>
                    @if($registration->draw_number)
                        &nbsp;•&nbsp; No. Undian Tampil: <strong class="font-mono text-amber-300 bg-amber-500/20 px-1.5 py-0.5 rounded border border-amber-500/30">#{{ $registration->draw_number }}</strong>
                    @endif
                </p>
            </div>
        </div>
    @elseif($registration->status === 'revision')
        <div class="bg-amber-500/10 border border-amber-500/30 rounded-2xl p-5 space-y-4 backdrop-blur-xl">
            <div class="flex items-start gap-3.5">
                <div class="w-9 h-9 rounded-xl bg-amber-500 text-slate-950 flex items-center justify-center shrink-0 shadow-md shadow-amber-500/20">
                    <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                </div>
                <div class="space-y-1">
                    <h4 class="text-xs sm:text-sm font-bold text-amber-300">Perlu Perbaikan / Revisi Berkas</h4>
                    <p class="text-xs text-slate-300 leading-relaxed">
                        Catatan Panitia: <em class="text-amber-200">"{{ $registration->verification_notes }}"</em>
                    </p>
                </div>
            </div>

            <!-- Upload Revision Form -->
            <form action="{{ route('peserta.registration.revision', $registration->id) }}" method="POST" enctype="multipart/form-data" class="bg-slate-900/90 p-4 sm:p-5 rounded-xl border border-amber-500/30 space-y-3">
                @csrf
                <h5 class="text-xs font-bold uppercase tracking-wider text-slate-300">Unggah Ulang Berkas Revisi</h5>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-300 mb-1">Dokumen Surat / Kartu Pelajar</label>
                        <input type="file" name="document_file" class="block w-full text-xs text-slate-400 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-amber-500/20 file:text-amber-300 file:text-xs file:font-bold">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-300 mb-1">Bukti Transfer / Pembayaran</label>
                        <input type="file" name="payment_proof" class="block w-full text-xs text-slate-400 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-amber-500/20 file:text-amber-300 file:text-xs file:font-bold">
                    </div>
                </div>
                <button type="submit" class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs transition">
                    Kirim Perbaikan Berkas
                </button>
            </form>
        </div>
    @elseif($registration->status === 'rejected')
        <div class="bg-rose-500/10 border border-rose-500/30 rounded-2xl p-4 sm:p-5 flex items-start gap-3.5 backdrop-blur-xl">
            <div class="w-9 h-9 rounded-xl bg-rose-500 text-white flex items-center justify-center shrink-0 shadow-md shadow-rose-500/20">
                <i data-lucide="x-circle" class="w-5 h-5"></i>
            </div>
            <div class="space-y-1">
                <h4 class="text-xs sm:text-sm font-bold text-rose-300">Pendaftaran Ditolak</h4>
                <p class="text-xs text-slate-300 leading-relaxed">
                    Alasan penolakan: {{ $registration->verification_notes ?? 'Tidak memenuhi syarat usia/jenjang atau kuota telah terpenuhi.' }}
                </p>
            </div>
        </div>
    @else
        <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-4 sm:p-5 flex items-start gap-3.5 backdrop-blur-xl">
            <div class="w-9 h-9 rounded-xl bg-slate-800 text-amber-400 flex items-center justify-center shrink-0 border border-slate-700">
                <i data-lucide="clock" class="w-5 h-5"></i>
            </div>
            <div class="space-y-1">
                <h4 class="text-xs sm:text-sm font-bold text-slate-200">Menunggu Verifikasi Panitia</h4>
                <p class="text-xs text-slate-400 leading-relaxed">
                    Panitia sedang memeriksa kelengkapan data & berkas yang Anda kirim. Status akan otomatis diperbarui setelah diverifikasi.
                </p>
            </div>
        </div>
    @endif

    <!-- Main Content: Compact 2-Column Responsive Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- Kolom Kiri: Daftar Anggota / Peserta (Col 7) -->
        <div class="lg:col-span-7 glass-card rounded-3xl p-5 sm:p-6 border border-slate-800/80 shadow-xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="text-sm font-extrabold text-white flex items-center gap-2 font-display">
                    <i data-lucide="users" class="w-4 h-4 text-[#7A5AF8]"></i>
                    <span>Daftar Peserta ({{ $registration->members->count() }} Orang)</span>
                </h3>
                @if($registration->team_name)
                    <span class="text-xs text-slate-300 font-bold bg-slate-800 px-2.5 py-0.5 rounded-lg border border-slate-700">
                        {{ $registration->team_name }}
                    </span>
                @endif
            </div>

            <div class="space-y-3">
                @foreach($registration->members as $index => $member)
                    @php
                        $bDateStr = null;
                        if ($member->birth_date) {
                            if ($member->birth_date instanceof \Carbon\Carbon) {
                                $bDateStr = $member->birth_date->format('d/m/Y');
                            } elseif (is_string($member->birth_date) && strtotime($member->birth_date)) {
                                $bDateStr = \Carbon\Carbon::parse($member->birth_date)->format('d/m/Y');
                            } else {
                                $bDateStr = $member->birth_date;
                            }
                        }
                        $ttl = trim(($member->birth_place ? $member->birth_place : '').($member->birth_place && $bDateStr ? ', ' : '').($bDateStr ?: ''));
                    @endphp
                    <div class="p-3.5 rounded-2xl bg-slate-900/80 border border-slate-800 hover:border-slate-700 transition space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-bold text-slate-400">#{{ $index + 1 }} • {{ $member->role_in_team ?? 'Peserta' }}</span>
                            <span class="px-2 py-0.5 text-[10px] font-bold rounded {{ $member->gender === 'L' ? 'bg-blue-500/20 text-blue-300 border border-blue-500/30' : 'bg-pink-500/20 text-pink-300 border border-pink-500/30' }}">
                                {{ $member->gender === 'L' ? '👦 Laki-laki (PA)' : '👧 Perempuan (PI)' }}
                            </span>
                        </div>
                        
                        <h4 class="text-sm font-black text-white">{{ $member->full_name }}</h4>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-1 text-xs pt-1 border-t border-slate-800/80">
                            <div>
                                <span class="text-slate-500 text-[11px]">Asal Sekolah:</span>
                                <p class="text-slate-200 font-medium truncate">{{ $member->school_name ?? $registration->institution_name }}</p>
                            </div>
                            <div>
                                <span class="text-slate-500 text-[11px]">NISN:</span>
                                <p class="font-mono text-emerald-400 font-semibold">{{ $member->nisn ?: '-' }}</p>
                            </div>
                            <div class="sm:col-span-2 pt-0.5">
                                <span class="text-slate-500 text-[11px]">TTL:</span>
                                <p class="text-slate-300 font-medium">{{ $ttl ?: '-' }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Kolom Kanan: Berkas Pendaftaran & Bukti Transfer (Col 5) -->
        <div class="lg:col-span-5 space-y-6">
            
            <div class="glass-card rounded-3xl p-5 sm:p-6 border border-slate-800/80 shadow-xl space-y-4">
                <h3 class="text-sm font-extrabold text-white border-b border-slate-800 pb-3 flex items-center gap-2 font-display">
                    <i data-lucide="folder-check" class="w-4 h-4 text-emerald-400"></i>
                    <span>Berkas & Dokumen</span>
                </h3>

                <div class="space-y-3">
                    <!-- 1. Dokumen Surat Rekomendasi / Kartu Pelajar -->
                    <div class="p-3.5 rounded-2xl bg-slate-900/80 border border-slate-800 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 overflow-hidden">
                            <div class="w-9 h-9 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0 border border-emerald-500/30">
                                <i data-lucide="file-text" class="w-4 h-4"></i>
                            </div>
                            <div class="overflow-hidden">
                                <h5 class="text-xs font-bold text-white truncate">Surat Rekomendasi</h5>
                                <p class="text-[10px] text-slate-400">
                                    {{ $registration->document_file ? 'Berkas Terlampir' : 'Belum diunggah' }}
                                </p>
                            </div>
                        </div>
                        @if($registration->document_file)
                            <a href="{{ asset('storage/' . $registration->document_file) }}" target="_blank" class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 border border-slate-700 text-xs font-bold text-slate-200 transition shrink-0 flex items-center gap-1">
                                <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                <span>Lihat</span>
                            </a>
                        @endif
                    </div>

                    <!-- 2. Bukti Transfer / Pembayaran -->
                    <div class="p-3.5 rounded-2xl bg-slate-900/80 border border-slate-800 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 overflow-hidden">
                            <div class="w-9 h-9 rounded-xl bg-amber-500/20 text-amber-400 flex items-center justify-center shrink-0 border border-amber-500/30">
                                <i data-lucide="receipt" class="w-4 h-4"></i>
                            </div>
                            <div class="overflow-hidden">
                                <h5 class="text-xs font-bold text-white truncate">Bukti Transfer</h5>
                                <p class="text-[10px] text-slate-400">
                                    {{ $registration->payment_proof ? 'Slip Terlampir' : 'Tidak dilampirkan / Gratis' }}
                                </p>
                            </div>
                        </div>
                        @if($registration->payment_proof)
                            <a href="{{ asset('storage/' . $registration->payment_proof) }}" target="_blank" class="px-2.5 py-1 rounded-lg bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-bold transition shrink-0 flex items-center gap-1 shadow-sm">
                                <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                <span>Lihat Slip</span>
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Ringkasan Cepat Info Kontak Official / Pendamping -->
            @if($registration->official_name || $registration->official_phone)
                <div class="glass-card rounded-3xl p-5 border border-slate-800/80 shadow-xl space-y-2">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400">Official / Pendamping</h4>
                    <div class="text-xs text-slate-200 space-y-1">
                        <p class="font-bold text-white">{{ $registration->official_name ?: '-' }}</p>
                        <p class="font-mono text-slate-400">{{ $registration->official_phone ?: '-' }}</p>
                    </div>
                </div>
            @endif

        </div>
    </div>

</div>
@endsection
