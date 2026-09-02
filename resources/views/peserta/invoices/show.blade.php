@extends('layouts.admin')

@section('title', 'Tagihan ' . $invoice->invoice_number)
@section('page_title', 'Rincian Tagihan Kolektif')

@section('content')
<div class="space-y-8" x-data="{ previewUrl: null }">

    <!-- Top Invoice Header (Dark Glass) -->
    <div class="glass-card rounded-3xl p-6 sm:p-8 border border-slate-800 shadow-2xl space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-800 pb-6">
            <div class="space-y-1">
                <div class="flex items-center gap-2.5">
                    <span class="font-mono text-sm font-black text-emerald-400 bg-slate-900 px-3 py-1 rounded-xl border border-slate-700">
                        {{ $invoice->invoice_number }}
                    </span>
                    @if($invoice->status === 'verified')
                        <span class="px-3 py-1 rounded-full text-xs font-black bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                            ✔ LUNAS & TERVERIFIKASI
                        </span>
                    @elseif($invoice->status === 'rejected')
                        <span class="px-3 py-1 rounded-full text-xs font-black bg-rose-500/20 text-rose-400 border border-rose-500/30">
                            ✕ DITOLAK
                        </span>
                    @elseif($invoice->payment_proof)
                        <span class="px-3 py-1 rounded-full text-xs font-black bg-amber-500/20 text-amber-400 border border-amber-500/30">
                            ⏳ MENUNGGU VERIFIKASI PANITIA
                        </span>
                    @else
                        <span class="px-3 py-1 rounded-full text-xs font-black bg-slate-800 text-slate-400 border border-slate-700">
                            💳 MENUNGGU PEMBAYARAN
                        </span>
                    @endif
                </div>
                <h2 class="text-2xl font-black text-white pt-2 font-display">Tagihan Pendaftaran Kolektif</h2>
                <p class="text-xs text-slate-400">
                    Diterbitkan pada {{ $invoice->created_at->format('d F Y, H:i') }} WIB • Instansi: <strong class="text-slate-200">{{ $invoice->user->institution_name ?? $invoice->user->name }}</strong>
                </p>
            </div>

            <div class="text-right">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Total Pembayaran</span>
                <span class="text-3xl font-black text-emerald-400 block font-mono">{{ $invoice->formatted_final_amount }}</span>
                <span class="text-[11px] text-slate-500 font-medium">Nominal Pas Resmi</span>
            </div>
        </div>

        <!-- Payment Instructions & Upload Section Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">
            <!-- Bank Info -->
            <div class="bg-gradient-to-br from-slate-900 to-slate-950 rounded-3xl p-6 text-white space-y-4 shadow-xl border border-slate-800">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-emerald-400 uppercase tracking-widest">Rekening Resmi Pembayaran</span>
                    <i data-lucide="landmark" class="w-5 h-5 text-emerald-400"></i>
                </div>

                <div class="space-y-1 pt-2">
                    <p class="text-xs text-slate-400">Bank Syariah Indonesia (BSI) / Rekening Panitia</p>
                    <p class="text-2xl font-mono font-black tracking-wider text-emerald-300">7145 8892 01</p>
                    <p class="text-xs text-slate-300 font-medium">a.n. <strong class="text-white">PANITIA TALENTA MTSN 1 BLITAR</strong></p>
                </div>

                <div class="p-3.5 rounded-2xl bg-slate-800/80 border border-slate-700/80 text-[11px] text-slate-300 space-y-1">
                    <p class="font-bold text-white flex items-center gap-1.5">
                        <i data-lucide="info" class="w-3.5 h-3.5 text-amber-400"></i>
                        <span>Petunjuk Transfer Kolektif:</span>
                    </p>
                    <p>1. Transfer sesuai <strong>nominal pas {{ $invoice->formatted_final_amount }}</strong>.</p>
                    <p>2. Sertakan nomor tagihan <strong>{{ $invoice->invoice_number }}</strong> pada berita/catatan transfer.</p>
                    <p>3. Simpan bukti transfer dan unggah pada kolom di sebelah.</p>
                </div>
            </div>

            <!-- Upload Payment Proof Box (Dark Glass) -->
            <div class="bg-slate-900/80 border border-slate-800 rounded-3xl p-6 flex flex-col justify-between space-y-4">
                <div>
                    <h4 class="text-sm font-black text-white font-display">Upload 1 Lembar Bukti Transfer</h4>
                    <p class="text-xs text-slate-400">1 bukti transfer ini akan otomatis melunaskan seluruh {{ $invoice->registrations->count() }} peserta dalam tagihan ini.</p>
                </div>

                @if($invoice->payment_proof)
                    <div class="space-y-3">
                        <div class="flex items-center gap-3 p-3 rounded-2xl bg-slate-950 border border-slate-800">
                            <div class="w-10 h-10 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0 border border-emerald-500/30">
                                <i data-lucide="file-check" class="w-5 h-5"></i>
                            </div>
                            <div class="overflow-hidden">
                                <span class="block text-xs font-bold text-white truncate">Bukti Transfer Terunggah</span>
                                <a href="{{ asset('storage/' . $invoice->payment_proof) }}" target="_blank" class="text-[11px] font-bold text-emerald-400 hover:text-emerald-300 inline-flex items-center gap-1">
                                    <span>Lihat Berkas Struk</span>
                                    <i data-lucide="external-link" class="w-3 h-3"></i>
                                </a>
                            </div>
                        </div>

                        @if($invoice->status !== 'verified')
                            <form action="{{ route('peserta.invoices.upload', $invoice->id) }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                                @csrf
                                <label class="block text-[11px] font-bold text-slate-400">Ganti / Unggah Ulang Bukti Transfer:</label>
                                <div class="flex items-center gap-2">
                                    <input type="file" name="payment_proof" required accept="image/*,.pdf" class="block w-full text-xs text-slate-300 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-800 file:text-slate-200 hover:file:bg-slate-700 bg-slate-950 p-1.5 rounded-xl border border-slate-800">
                                    <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-slate-950 font-bold text-xs shrink-0 transition cursor-pointer">
                                        Upload Ulang
                                    </button>
                                </div>
                            </form>
                        @endif
                    </div>
                @else
                    <form action="{{ route('peserta.invoices.upload', $invoice->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                        @csrf

                        <div>
                            <input type="file" name="payment_proof" id="payment_proof" required accept="image/*,.pdf" class="block w-full text-xs text-slate-300 file:mr-4 file:py-2.5 file:px-5 file:rounded-2xl file:border-0 file:text-xs file:font-bold file:bg-emerald-600 file:text-slate-950 hover:file:bg-emerald-500 file:shadow-md file:shadow-emerald-500/20 file:transition cursor-pointer bg-slate-950 p-2 rounded-2xl border border-slate-800" @change="previewUrl = URL.createObjectURL($event.target.files[0])">
                            @error('payment_proof')
                                <p class="mt-1 text-xs text-rose-400 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Image Preview -->
                        <template x-if="previewUrl">
                            <div class="p-2 rounded-2xl bg-slate-950 border border-slate-800 max-w-xs">
                                <img :src="previewUrl" alt="Preview Bukti" class="rounded-xl max-h-40 object-cover mx-auto">
                            </div>
                        </template>

                        <button type="submit" class="w-full py-3 px-4 rounded-2xl bg-gradient-to-r from-emerald-400 via-teal-400 to-cyan-400 hover:from-emerald-300 hover:to-cyan-300 text-slate-950 font-black text-xs shadow-lg shadow-emerald-500/30 transition flex items-center justify-center gap-2 cursor-pointer">
                            <i data-lucide="upload" class="w-4 h-4"></i>
                            <span>Simpan & Kirim Bukti Transfer</span>
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <!-- Registered Participants List in This Invoice (Dark Glass) -->
    <div class="glass-card rounded-3xl p-6 sm:p-8 border border-slate-800 shadow-2xl space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-800 pb-4">
            <div>
                <h3 class="text-base font-black text-white font-display">Daftar Peserta & Cabang Lomba Terdaftar</h3>
                <p class="text-xs text-slate-400">Seluruh delegasi siswa yang diikutkan pada tagihan kolektif ini</p>
            </div>
            <span class="text-xs font-bold px-3 py-1 rounded-full bg-slate-900 text-slate-300 border border-slate-800">
                Total: {{ $invoice->registrations->count() }} Pendaftaran
            </span>
        </div>

        <div class="overflow-x-auto rounded-2xl border border-slate-800 shadow-xl">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="text-[10px] font-bold uppercase tracking-wider bg-slate-950/80 text-slate-400 border-b border-slate-800">
                    <tr>
                        <th class="py-3.5 px-4">No. Registrasi</th>
                        <th class="py-3.5 px-4">Nama Peserta / Anggota</th>
                        <th class="py-3.5 px-4">Cabang Lomba</th>
                        <th class="py-3.5 px-4">Biaya</th>
                        <th class="py-3.5 px-4 text-center">Status Verifikasi</th>
                        <th class="py-3.5 px-4 text-center">Kartu Peserta</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800 font-medium">
                    @foreach($invoice->registrations as $reg)
                        <tr class="hover:bg-slate-800/40 transition">
                            <td class="py-3.5 px-4 font-mono font-bold text-white">
                                {{ $reg->registration_code }}
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-white text-sm">
                                    {{ $reg->team_name ?: ($reg->members->first()->full_name ?? $reg->institution_name) }}
                                </div>
                                <div class="text-[11px] text-slate-400">
                                    NISN: {{ $reg->members->first()->nisn ?? '-' }} • {{ $reg->institution_name }}
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="font-bold text-white">{{ $reg->competition->name }}</span>
                                <span class="block text-[10px] text-slate-400 uppercase font-mono">{{ $reg->competition->code }} ({{ $reg->competition->type }})</span>
                            </td>
                            <td class="py-3.5 px-4 font-bold text-emerald-400 whitespace-nowrap font-mono">
                                Rp {{ number_format($reg->fee, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($reg->status === 'verified')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                                        ✔ TERVERIFIKASI
                                    </span>
                                @elseif($reg->status === 'rejected')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-rose-500/20 text-rose-400 border border-rose-500/30">
                                        ✕ DITOLAK
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-amber-500/20 text-amber-400 border border-amber-500/30">
                                        ⏳ MENUNGGU VERIFIKASI
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($reg->status === 'verified')
                                    <a href="{{ route('peserta.print.idcard', $reg->id) }}" target="_blank" class="inline-flex items-center gap-1 px-3 py-1 rounded-xl bg-purple-500/20 text-purple-300 hover:bg-purple-500/30 font-bold border border-purple-500/30 transition">
                                        <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                                        <span>Cetak Kartu</span>
                                    </a>
                                @else
                                    <span class="text-slate-500 italic text-[11px]">Tersedia saat lunas</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="pt-4 border-t border-slate-800 flex items-center justify-between">
            <a href="{{ route('peserta.dashboard') }}" class="px-5 py-2.5 rounded-xl bg-slate-800 text-slate-300 font-bold text-xs hover:bg-slate-700 border border-slate-700 transition">
                Kembali ke Dashboard
            </a>
            <a href="{{ route('peserta.collective.wizard') }}" class="px-5 py-2.5 rounded-xl bg-emerald-500/20 text-emerald-300 font-bold text-xs hover:bg-emerald-500/30 border border-emerald-500/30 transition">
                + Tambah Pendaftaran Kolektif Lain
            </a>
        </div>
    </div>

</div>
@endsection
