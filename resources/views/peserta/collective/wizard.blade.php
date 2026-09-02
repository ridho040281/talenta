@extends('layouts.admin')

@section('title', 'Pendaftaran Kolektif (Excel)')
@section('page_title', 'Pendaftaran Kolektif via Excel')

@section('content')
<div class="space-y-8" x-data="{ isUploading: false }">

    <!-- Header Banner -->
    <div class="bg-gradient-to-r from-emerald-900/90 via-teal-900/80 to-slate-950 rounded-3xl p-8 text-white shadow-2xl border border-emerald-500/30 flex flex-col md:flex-row md:items-center justify-between gap-6 relative overflow-hidden backdrop-blur-xl">
        <div class="space-y-2 relative z-10">
            <span class="px-3 py-1 text-xs font-bold rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-400/30 uppercase tracking-wider">
                👥 Jalur Kolektif (Bulk Import)
            </span>
            <h2 class="text-2xl sm:text-3xl font-black text-white font-display">Pendaftaran Kolektif via Excel</h2>
            <p class="text-xs sm:text-sm text-slate-300 max-w-2xl leading-relaxed">
                Daftarkan banyak siswa sekaligus dari <span class="font-bold text-white">{{ $user->institution_name ?? 'sekolah/madrasah Anda' }}</span> ke berbagai cabang lomba hanya dengan satu kali unggah file Excel dan satu kali transfer tagihan.
            </p>
        </div>
        <div class="relative z-10 shrink-0">
            <a href="{{ route('peserta.collective.template') }}" class="inline-flex items-center gap-2 px-6 py-3.5 rounded-2xl bg-amber-400 hover:bg-amber-300 text-slate-950 font-black text-xs shadow-lg shadow-amber-400/25 hover:scale-105 transition duration-200">
                <i data-lucide="download" class="w-4 h-4 text-slate-950"></i>
                <span>Unduh Template Excel Resmi</span>
            </a>
        </div>
    </div>

    <!-- 3-Step Guide Cards (Dark Glass) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Step 1 -->
        <div class="glass-card rounded-3xl p-6 border border-slate-800 shadow-2xl space-y-3 relative hover:border-emerald-500/50 transition">
            <div class="w-10 h-10 rounded-2xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-black text-sm border border-emerald-500/30">
                1
            </div>
            <h4 class="font-black text-white text-sm font-display">Unduh Template Excel</h4>
            <p class="text-xs text-slate-400 leading-relaxed">
                Unduh file master template resmi yang sudah dilengkapi kolom standar dan lembar referensi kode cabang lomba.
            </p>
            <div class="pt-2">
                <a href="{{ route('peserta.collective.template') }}" class="text-xs font-bold text-emerald-400 hover:text-emerald-300 inline-flex items-center gap-1">
                    <span>Download Template</span>
                    <i data-lucide="arrow-down-to-line" class="w-3.5 h-3.5"></i>
                </a>
            </div>
        </div>

        <!-- Step 2 -->
        <div class="glass-card rounded-3xl p-6 border border-slate-800 shadow-2xl space-y-3 relative hover:border-blue-500/50 transition">
            <div class="w-10 h-10 rounded-2xl bg-blue-500/20 text-blue-400 flex items-center justify-center font-black text-sm border border-blue-500/30">
                2
            </div>
            <h4 class="font-black text-white text-sm font-display">Isi Data Siswa (Offline)</h4>
            <p class="text-xs text-slate-400 leading-relaxed">
                Isi biodata seluruh siswa dan pilih kode lomba di Excel. Anda dapat mendaftarkan berbagai cabang lomba berbeda dalam satu file.
            </p>
            <div class="pt-2 text-xs font-semibold text-slate-500 font-mono">
                Format tanggal: YYYY-MM-DD
            </div>
        </div>

        <!-- Step 3 -->
        <div class="glass-card rounded-3xl p-6 border border-slate-800 shadow-2xl space-y-3 relative hover:border-purple-500/50 transition">
            <div class="w-10 h-10 rounded-2xl bg-purple-500/20 text-purple-400 flex items-center justify-center font-black text-sm border border-purple-500/30">
                3
            </div>
            <h4 class="font-black text-white text-sm font-display">Upload & 1x Transfer</h4>
            <p class="text-xs text-slate-400 leading-relaxed">
                Unggah file Excel, periksa preview validasi data, lalu bayar total tagihan dengan 1 lembar bukti transfer.
            </p>
            <div class="pt-2 text-xs font-semibold text-purple-400">
                Otomatis kalkulasi total biaya
            </div>
        </div>
    </div>

    <!-- Upload Box Area (Dark Glass) -->
    <div class="glass-card rounded-3xl p-8 border border-slate-800 shadow-2xl space-y-6">
        <div class="border-b border-slate-800 pb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <h3 class="text-lg font-black text-white font-display">Unggah File Excel Pendaftaran</h3>
                <p class="text-xs text-slate-400">Pilih file Excel (.xlsx / .xls / .csv) yang telah diisi sesuai template resmi</p>
            </div>
            <span class="text-[11px] font-bold px-3 py-1 rounded-full bg-slate-900 text-slate-400 border border-slate-800">
                Maksimal 10 MB
            </span>
        </div>

        <form action="{{ route('peserta.collective.parse') }}" method="POST" enctype="multipart/form-data" @submit="isUploading = true" class="space-y-6">
            @csrf

            <div class="relative border-2 border-dashed border-slate-700 hover:border-emerald-500 rounded-3xl p-10 text-center transition bg-slate-900/60 hover:bg-slate-900/90 group">
                <input type="file" name="excel_file" id="excel_file" required accept=".xlsx, .xls, .csv" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20" onchange="document.getElementById('file-chosen').textContent = this.files[0].name">
                
                <div class="space-y-4">
                    <div class="w-16 h-16 rounded-3xl bg-emerald-500/20 text-emerald-400 group-hover:bg-emerald-500 group-hover:text-slate-950 flex items-center justify-center mx-auto transition duration-300 shadow-lg shadow-emerald-500/20 border border-emerald-500/30">
                        <i data-lucide="file-spreadsheet" class="w-8 h-8"></i>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-white">
                            Klik untuk memilih file Excel atau drag & drop di sini
                        </p>
                        <p class="text-xs text-slate-400 mt-1">Mendukung format .xlsx, .xls, atau .csv</p>
                    </div>
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-800 border border-slate-700 text-xs font-bold text-slate-200 shadow-sm" id="file-chosen">
                        <i data-lucide="paperclip" class="w-3.5 h-3.5 text-slate-400"></i>
                        <span>Belum ada file dipilih</span>
                    </div>
                </div>
            </div>

            @error('excel_file')
                <p class="text-xs text-rose-400 font-semibold">{{ $message }}</p>
            @enderror

            <div class="flex items-center justify-between pt-2">
                <a href="{{ route('peserta.dashboard') }}" class="px-5 py-2.5 rounded-xl bg-slate-800 text-slate-300 text-xs font-bold hover:bg-slate-700 border border-slate-700 transition">
                    Kembali ke Dashboard
                </a>
                <button type="submit" :disabled="isUploading" class="inline-flex items-center gap-2 px-6 py-3 rounded-2xl bg-gradient-to-r from-emerald-400 via-teal-400 to-cyan-400 hover:from-emerald-300 hover:to-cyan-300 text-slate-950 font-black text-xs shadow-lg shadow-emerald-500/30 transition disabled:opacity-50 cursor-pointer">
                    <i data-lucide="upload-cloud" class="w-4 h-4"></i>
                    <span x-text="isUploading ? 'Sedang Membaca Excel...' : 'Unggah & Pratinjau Data'">Unggah & Pratinjau Data</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Active / Past Collective Invoices (Dark Glass) -->
    @if($invoices->isNotEmpty())
        <div class="glass-card rounded-3xl p-6 sm:p-8 border border-slate-800 shadow-2xl space-y-6">
            <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                <div>
                    <h3 class="text-base font-black text-white font-display">Riwayat Tagihan Kolektif Anda</h3>
                    <p class="text-xs text-slate-400">Daftar invoice pendaftaran kolektif beserta status pembayarannya</p>
                </div>
                <span class="text-xs font-bold px-3 py-1 rounded-full bg-slate-900 text-slate-300 border border-slate-800">
                    {{ $invoices->count() }} Tagihan
                </span>
            </div>

            <div class="overflow-x-auto rounded-2xl border border-slate-800 shadow-xl">
                <table class="w-full text-left text-sm text-slate-300">
                    <thead class="text-xs font-bold uppercase tracking-wider bg-slate-950/80 text-slate-400 border-b border-slate-800">
                        <tr>
                            <th class="py-3.5 px-4">No. Invoice</th>
                            <th class="py-3.5 px-4">Tanggal Buat</th>
                            <th class="py-3.5 px-4">Jumlah Peserta</th>
                            <th class="py-3.5 px-4">Total Biaya</th>
                            <th class="py-3.5 px-4 text-center">Status Pembayaran</th>
                            <th class="py-3.5 px-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800 font-medium text-xs">
                        @foreach($invoices as $inv)
                            <tr class="hover:bg-slate-800/40 transition">
                                <td class="py-3.5 px-4 font-mono font-bold text-white">
                                    {{ $inv->invoice_number }}
                                </td>
                                <td class="py-3.5 px-4 text-slate-400">
                                    {{ $inv->created_at->format('d M Y, H:i') }}
                                </td>
                                <td class="py-3.5 px-4 font-bold text-slate-200">
                                    {{ $inv->registrations->count() }} Peserta
                                </td>
                                <td class="py-3.5 px-4 font-black text-emerald-400 font-mono">
                                    {{ $inv->formatted_final_amount }}
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if($inv->status === 'verified')
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                                            ✔ LUNAS & TERVERIFIKASI
                                        </span>
                                    @elseif($inv->status === 'rejected')
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-rose-500/20 text-rose-400 border border-rose-500/30">
                                            ✕ DITOLAK
                                        </span>
                                    @elseif($inv->payment_proof)
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-amber-500/20 text-amber-400 border border-amber-500/30">
                                            ⏳ MENUNGGU VERIFIKASI
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-slate-800 text-slate-400 border border-slate-700">
                                            💳 BELUM BAYAR
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <a href="{{ route('peserta.invoices.show', $inv->id) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white font-bold border border-slate-700 transition">
                                        <i data-lucide="eye" class="w-3.5 h-3.5 text-emerald-400"></i>
                                        <span>Rincian & Upload Bukti</span>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

</div>
@endsection
