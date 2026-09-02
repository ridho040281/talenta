@extends('layouts.admin')

@section('title', 'Detail Pendaftaran ' . $registration->registration_code)
@section('page_title', 'Rincian Data Pendaftaran')

@section('content')
<div class="max-w-4xl mx-auto space-y-8">
    
    <!-- Top Action & Status Bar -->
    <div class="glass-card rounded-3xl p-6 sm:p-8 border border-slate-800 shadow-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <span class="text-xs font-mono font-bold text-emerald-400 bg-emerald-500/20 px-2.5 py-1 rounded-lg border border-emerald-500/30">
                {{ $registration->registration_code }}
            </span>
            <h2 class="text-2xl font-black text-white mt-2 font-display">{{ $registration->competition->name }}</h2>
            @if($registration->sub_category)
                <div class="mt-1.5">
                    <span class="px-3 py-1 text-xs font-bold rounded-lg bg-amber-500/20 text-amber-300 border border-amber-500/30">
                        🏸 {{ $registration->sub_category }}
                    </span>
                </div>
            @endif
            <p class="text-xs text-slate-400 mt-1">{{ $registration->institution_name }}</p>
        </div>

        <div class="flex items-center gap-3">
            @if($registration->status === 'verified')
                <a href="{{ route('peserta.print.idcard', $registration->id) }}" target="_blank" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-slate-950 font-black text-xs shadow-lg shadow-emerald-600/20 transition">
                    <i data-lucide="printer" class="w-4 h-4"></i>
                    <span>Cetak ID Card & No. Dada</span>
                </a>
            @endif
            <a href="{{ route('peserta.registrations') }}" class="px-4 py-2.5 rounded-2xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 font-bold text-xs transition">
                Kembali ke Pendaftaran Saya
            </a>
        </div>
    </div>

    <!-- Status Banner Alert -->
    @if($registration->status === 'verified')
        <div class="bg-emerald-500/10 border border-emerald-500/30 rounded-3xl p-6 flex items-start gap-4 backdrop-blur-xl">
            <div class="w-10 h-10 rounded-2xl bg-emerald-500 text-white flex items-center justify-center shrink-0 shadow-lg shadow-emerald-500/30">
                <i data-lucide="check" class="w-6 h-6"></i>
            </div>
            <div class="space-y-1">
                <h4 class="text-sm font-bold text-emerald-300">Pendaftaran Telah Diverifikasi & Sah</h4>
                <p class="text-xs text-slate-300 leading-relaxed">
                    Berkas pendaftaran Anda telah disetujui oleh panitia. Nomor Peserta resmi Anda adalah <strong class="text-white">{{ $registration->participant_number }}</strong>.
                    @if($registration->draw_number)
                        Nomor Undian Tampil: <strong class="text-amber-400">No. {{ $registration->draw_number }}</strong>.
                    @endif
                </p>
            </div>
        </div>
    @elseif($registration->status === 'revision')
        <div class="bg-amber-500/10 border border-amber-500/30 rounded-3xl p-6 space-y-4 backdrop-blur-xl">
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 rounded-2xl bg-amber-500 text-slate-950 flex items-center justify-center shrink-0 shadow-lg shadow-amber-500/30">
                    <i data-lucide="alert-triangle" class="w-6 h-6"></i>
                </div>
                <div class="space-y-1">
                    <h4 class="text-sm font-bold text-amber-300">Perlu Perbaikan / Revisi Berkas</h4>
                    <p class="text-xs text-slate-300 leading-relaxed">
                        Catatan Panitia: <em class="text-amber-200">"{{ $registration->verification_notes }}"</em>
                    </p>
                </div>
            </div>

            <!-- Upload Revision Form -->
            <form action="{{ route('peserta.registration.revision', $registration->id) }}" method="POST" enctype="multipart/form-data" class="bg-slate-900/90 p-6 rounded-2xl border border-amber-500/30 space-y-4">
                @csrf
                <h5 class="text-xs font-bold uppercase tracking-wider text-slate-300">Unggah Ulang Berkas Revisi</h5>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">Dokumen Surat / Kartu Pelajar</label>
                        <input type="file" name="document_file" class="block w-full text-xs text-slate-400 file:py-2 file:px-4 file:rounded-xl file:border-0 file:bg-amber-500/20 file:text-amber-300">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">Bukti Transfer / Pembayaran</label>
                        <input type="file" name="payment_proof" class="block w-full text-xs text-slate-400 file:py-2 file:px-4 file:rounded-xl file:border-0 file:bg-amber-500/20 file:text-amber-300">
                    </div>
                </div>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs transition">
                    Kirim Perbaikan Berkas
                </button>
            </form>
        </div>
    @elseif($registration->status === 'rejected')
        <div class="bg-rose-500/10 border border-rose-500/30 rounded-3xl p-6 flex items-start gap-4 backdrop-blur-xl">
            <div class="w-10 h-10 rounded-2xl bg-rose-500 text-white flex items-center justify-center shrink-0 shadow-lg shadow-rose-500/30">
                <i data-lucide="x-circle" class="w-6 h-6"></i>
            </div>
            <div class="space-y-1">
                <h4 class="text-sm font-bold text-rose-300">Pendaftaran Ditolak</h4>
                <p class="text-xs text-slate-300 leading-relaxed">
                    Alasan penolakan: {{ $registration->verification_notes ?? 'Tidak memenuhi syarat usia/jenjang atau kuota telah terpenuhi.' }}
                </p>
            </div>
        </div>
    @else
        <div class="bg-slate-900/80 border border-slate-800 rounded-3xl p-6 flex items-start gap-4 backdrop-blur-xl">
            <div class="w-10 h-10 rounded-2xl bg-slate-800 text-slate-400 flex items-center justify-center shrink-0">
                <i data-lucide="clock" class="w-6 h-6"></i>
            </div>
            <div class="space-y-1">
                <h4 class="text-sm font-bold text-slate-200">Menunggu Verifikasi Panitia</h4>
                <p class="text-xs text-slate-400 leading-relaxed">
                    Panitia sedang memeriksa kelengkapan data & berkas yang Anda kirim. Silakan cek berkala status di halaman ini.
                </p>
            </div>
        </div>
    @endif

    <!-- Berkas yang Telah Diupload -->
    <div class="glass-card rounded-3xl p-6 sm:p-8 border border-slate-800/80 shadow-2xl space-y-4">
        <h3 class="text-base font-bold text-white border-b border-slate-800 pb-3 flex items-center gap-2 font-display">
            <i data-lucide="folder-check" class="w-4 h-4 text-emerald-400"></i>
            <span>Berkas & Dokumen yang Telah Diunggah</span>
        </h3>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- 1. Dokumen Surat Rekomendasi -->
            <div class="p-4 rounded-2xl bg-slate-900/80 border border-slate-800 flex items-center justify-between gap-3">
                <div class="flex items-center gap-3 overflow-hidden">
                    <div class="w-10 h-10 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0 border border-emerald-500/30">
                        <i data-lucide="file-text" class="w-5 h-5"></i>
                    </div>
                    <div class="overflow-hidden">
                        <h5 class="text-xs font-bold text-white truncate">Surat Rekomendasi / Kartu Pelajar</h5>
                        <p class="text-[11px] text-slate-400">
                            {{ $registration->document_file ? 'Berkas Terlampir' : 'Belum diunggah' }}
                        </p>
                    </div>
                </div>
                @if($registration->document_file)
                    <a href="{{ asset('storage/' . $registration->document_file) }}" target="_blank" class="px-3 py-1.5 rounded-lg bg-slate-800 border border-slate-700 hover:bg-slate-700 text-xs font-bold text-slate-200 transition shrink-0 flex items-center gap-1">
                        <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                        <span>Lihat</span>
                    </a>
                @endif
            </div>

            <!-- 2. Bukti Transfer / Pembayaran -->
            <div class="p-4 rounded-2xl bg-slate-900/80 border border-slate-800 flex items-center justify-between gap-3">
                <div class="flex items-center gap-3 overflow-hidden">
                    <div class="w-10 h-10 rounded-xl bg-amber-500/20 text-amber-400 flex items-center justify-center shrink-0 border border-amber-500/30">
                        <i data-lucide="receipt" class="w-5 h-5"></i>
                    </div>
                    <div class="overflow-hidden">
                        <h5 class="text-xs font-bold text-white truncate">Bukti Transfer / Pembayaran</h5>
                        <p class="text-[11px] text-slate-400">
                            {{ $registration->payment_proof ? 'Bukti Transfer Terlampir' : 'Tidak dilampirkan / Gratis' }}
                        </p>
                    </div>
                </div>
                @if($registration->payment_proof)
                    <a href="{{ asset('storage/' . $registration->payment_proof) }}" target="_blank" class="px-3 py-1.5 rounded-lg bg-amber-500 hover:bg-amber-400 text-xs font-bold text-slate-950 transition shrink-0 flex items-center gap-1 shadow-sm">
                        <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                        <span>Lihat Slip</span>
                    </a>
                @endif
            </div>
        </div>
    </div>

    <!-- Data Anggota Peserta -->
    <div class="glass-card rounded-3xl p-6 sm:p-8 border border-slate-800/80 shadow-2xl space-y-6">
        <h3 class="text-base font-bold text-white border-b border-slate-800 pb-4 font-display">
            Daftar Anggota / Peserta ({{ $registration->members->count() }} Orang)
        </h3>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            @foreach($registration->members as $index => $member)
                <div class="p-5 rounded-2xl bg-slate-900/80 border border-slate-800 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-400">#{{ $index + 1 }} • {{ $member->role_in_team }}</span>
                        <span class="px-2 py-0.5 text-[10px] font-bold rounded bg-slate-800 text-slate-300 border border-slate-700">
                            {{ $member->gender === 'L' ? 'Laki-laki' : 'Perempuan' }}
                        </span>
                    </div>
                    <h4 class="text-base font-bold text-white">{{ $member->full_name }}</h4>
                    <p class="text-xs text-slate-400">NISN: <span class="font-mono text-slate-300">{{ $member->nisn ?? '-' }}</span></p>
                    <p class="text-xs text-slate-400">TTL: {{ $member->birth_place ?? '-' }}, {{ $member->birth_date ? $member->birth_date->format('d M Y') : '-' }}</p>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Dokumen Administrasi Cetak (Bukti Akun, Bukti Daftar, Kwitansi) -->
    <div class="glass-card rounded-3xl p-6 sm:p-8 border border-slate-800/80 shadow-2xl space-y-4">
        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
            <div>
                <h3 class="text-base font-black text-white font-display">Berkas Administrasi Resmi (Cetak & Unduh)</h3>
                <p class="text-xs text-slate-400">Cetak dokumen administrasi pendaftaran untuk arsip dan verifikasi saat daftar ulang lomba</p>
            </div>
            <div class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold border border-emerald-500/30">
                <i data-lucide="printer" class="w-4 h-4"></i>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5 pt-1">
            <!-- 1. Bukti Akun Pendaftar -->
            <a href="{{ route('document.print.account', $registration->id) }}" target="_blank" class="p-4 rounded-2xl bg-blue-500/10 hover:bg-blue-500/20 border border-blue-500/30 hover:border-blue-400 transition group flex flex-col justify-between space-y-3 cursor-pointer">
                <div class="space-y-1.5">
                    <div class="w-8 h-8 rounded-xl bg-blue-500 text-white flex items-center justify-center font-bold shadow-sm">
                        <i data-lucide="user-check" class="w-4 h-4"></i>
                    </div>
                    <h4 class="font-black text-white text-xs group-hover:text-blue-300 transition">📄 Bukti Akun Pendaftar</h4>
                    <p class="text-[11px] text-slate-400 leading-relaxed">Tanda bukti pembuatan akun sistem, email, dan instansi.</p>
                </div>
                <span class="inline-flex items-center gap-1 text-[11px] font-bold text-blue-300 pt-2 border-t border-blue-500/20">
                    <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                    <span>Cetak Bukti Akun</span>
                </span>
            </a>

            <!-- 2. Bukti Pendaftaran & Kartu Peserta -->
            @if($registration->status === 'verified')
                <a href="{{ route('document.print.registration', $registration->id) }}" target="_blank" class="p-4 rounded-2xl bg-emerald-500/10 hover:bg-emerald-500/20 border border-emerald-500/30 hover:border-emerald-400 transition group flex flex-col justify-between space-y-3 cursor-pointer">
                    <div class="space-y-1.5">
                        <div class="w-8 h-8 rounded-xl bg-emerald-500 text-slate-950 flex items-center justify-center font-bold shadow-sm">
                            <i data-lucide="file-check" class="w-4 h-4"></i>
                        </div>
                        <h4 class="font-black text-white text-xs group-hover:text-emerald-300 transition">🪪 Bukti Pendaftaran & Formulir</h4>
                        <p class="text-[11px] text-slate-400 leading-relaxed">Nomor peserta resmi, biodata lengkap atlet & cabang lomba.</p>
                    </div>
                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-300 pt-2 border-t border-emerald-500/20">
                        <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                        <span>Cetak Formulir Resmi</span>
                    </span>
                </a>
            @else
                <div onclick="alert('Peringatan: Pendaftaran Anda belum terverifikasi!\n\nAnda tidak bisa mencetak Bukti Pendaftaran sebelum status pendaftaran diverifikasi oleh panitia.');" class="p-4 rounded-2xl bg-slate-900/60 border border-slate-800 opacity-70 hover:opacity-100 transition group flex flex-col justify-between space-y-3 cursor-pointer">
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between">
                            <div class="w-8 h-8 rounded-xl bg-slate-800 text-slate-400 flex items-center justify-center font-bold">
                                <i data-lucide="lock" class="w-4 h-4"></i>
                            </div>
                            <span class="text-[9px] px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-300 font-bold border border-amber-500/30">Menunggu Verifikasi</span>
                        </div>
                        <h4 class="font-black text-slate-300 text-xs transition">🪪 Bukti Pendaftaran & Formulir</h4>
                        <p class="text-[11px] text-slate-500 leading-relaxed">Nomor peserta resmi, biodata lengkap atlet & cabang lomba.</p>
                    </div>
                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-slate-400 pt-2 border-t border-slate-800">
                        <i data-lucide="lock" class="w-3.5 h-3.5"></i>
                        <span>Terkunci (Belum Sah)</span>
                    </span>
                </div>
            @endif

            <!-- 3. Kwitansi / Invoice Pembayaran -->
            @if($registration->status === 'verified')
                <a href="{{ route('document.print.receipt', $registration->id) }}" target="_blank" class="p-4 rounded-2xl bg-amber-500/10 hover:bg-amber-500/20 border border-amber-500/30 hover:border-amber-400 transition group flex flex-col justify-between space-y-3 cursor-pointer">
                    <div class="space-y-1.5">
                        <div class="w-8 h-8 rounded-xl bg-amber-500 text-slate-950 flex items-center justify-center font-bold shadow-sm">
                            <i data-lucide="receipt" class="w-4 h-4"></i>
                        </div>
                        <h4 class="font-black text-white text-xs group-hover:text-amber-300 transition">🧾 Kwitansi / Invoice Resmi</h4>
                        <p class="text-[11px] text-slate-400 leading-relaxed">Tanda bukti pembayaran sah berstempel panitia.</p>
                    </div>
                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-amber-300 pt-2 border-t border-amber-500/20">
                        <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                        <span>Cetak Kwitansi</span>
                    </span>
                </a>
            @else
                <div onclick="alert('Peringatan: Pendaftaran Anda belum terverifikasi!\n\nAnda tidak bisa mencetak Kwitansi Pembayaran sebelum status pendaftaran diverifikasi oleh panitia.');" class="p-4 rounded-2xl bg-slate-900/60 border border-slate-800 opacity-70 hover:opacity-100 transition group flex flex-col justify-between space-y-3 cursor-pointer">
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between">
                            <div class="w-8 h-8 rounded-xl bg-slate-800 text-slate-400 flex items-center justify-center font-bold">
                                <i data-lucide="lock" class="w-4 h-4"></i>
                            </div>
                            <span class="text-[9px] px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-300 font-bold border border-amber-500/30">Menunggu Verifikasi</span>
                        </div>
                        <h4 class="font-black text-slate-300 text-xs transition">🧾 Kwitansi / Invoice Resmi</h4>
                        <p class="text-[11px] text-slate-500 leading-relaxed">Tanda bukti pembayaran sah berstempel panitia.</p>
                    </div>
                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-slate-400 pt-2 border-t border-slate-800">
                        <i data-lucide="lock" class="w-3.5 h-3.5"></i>
                        <span>Terkunci (Belum Sah)</span>
                    </span>
                </div>
            @endif
        </div>
    </div>

</div>
@endsection
