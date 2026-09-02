@extends('layouts.admin')

@section('title', 'Verifikasi ' . $invoice->invoice_number)
@section('page_title', 'Verifikasi Pembayaran Invoice Kolektif')

@section('content')
<div class="space-y-8" x-data="{ rejectModal: false }">

    <!-- Top Action Navigation -->
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.invoices.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-white border border-slate-200 text-xs font-bold text-slate-700 hover:bg-slate-50 transition shadow-sm">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Kembali ke Daftar Tagihan</span>
        </a>

        <div class="flex items-center gap-2">
            <span class="text-xs font-bold text-slate-500">Status Saat Ini:</span>
            @if($invoice->status === 'verified')
                <span class="px-3 py-1 rounded-full text-xs font-black bg-emerald-100 text-emerald-800 border border-emerald-200">
                    ✔ LUNAS & TERVERIFIKASI
                </span>
            @elseif($invoice->status === 'rejected')
                <span class="px-3 py-1 rounded-full text-xs font-black bg-rose-100 text-rose-800 border border-rose-200">
                    ✕ DITOLAK
                </span>
            @else
                <span class="px-3 py-1 rounded-full text-xs font-black bg-amber-100 text-amber-800 border border-amber-200">
                    ⏳ MENUNGGU VERIFIKASI
                </span>
            @endif
        </div>
    </div>

    <!-- Side-by-Side Verification Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <!-- Left Column: Bukti Transfer (5 Cols) -->
        <div class="lg:col-span-5 space-y-6">
            <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-base font-black text-slate-900 flex items-center gap-2">
                        <i data-lucide="receipt" class="w-4 h-4 text-brand-600"></i>
                        <span>Bukti Transfer Pembayaran</span>
                    </h3>
                    @if($invoice->payment_proof)
                        <a href="{{ asset('storage/' . $invoice->payment_proof) }}" target="_blank" class="text-xs font-bold text-brand-600 hover:underline inline-flex items-center gap-1">
                            <span>Buka Ukuran Asli</span>
                            <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                        </a>
                    @endif
                </div>

                @if($invoice->payment_proof)
                    <div class="rounded-2xl border border-slate-200 overflow-hidden bg-slate-950/5 p-2 text-center">
                        <a href="{{ asset('storage/' . $invoice->payment_proof) }}" target="_blank">
                            <img src="{{ asset('storage/' . $invoice->payment_proof) }}" alt="Bukti Transfer" class="rounded-xl max-h-[480px] w-full object-contain mx-auto hover:opacity-95 transition">
                        </a>
                    </div>
                @else
                    <div class="py-16 text-center text-slate-400 bg-slate-50 rounded-2xl border-2 border-dashed border-slate-200 space-y-2">
                        <i data-lucide="file-question" class="w-10 h-10 mx-auto text-slate-300"></i>
                        <p class="text-xs font-bold">Pendaftar belum mengunggah bukti transfer.</p>
                    </div>
                @endif

                <!-- Total Amount Match Indicator -->
                <div class="p-4 rounded-2xl bg-slate-900 text-white space-y-1">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Tagihan Yang Harus Dibayar:</span>
                    <p class="text-2xl font-black text-emerald-400">{{ $invoice->formatted_final_amount }}</p>
                    <p class="text-[11px] text-slate-400">Total Biaya: {{ $invoice->formatted_total }} + Kode Unik: {{ $invoice->unique_code }}</p>
                </div>
            </div>

            <!-- Verification Action Box -->
            <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-sm space-y-4">
                <h4 class="text-sm font-black text-slate-900">Keputusan Verifikasi Pembayaran</h4>
                <p class="text-xs text-slate-500">
                    Menyetujui tagihan ini akan <strong>otomatis melunaskan seluruh {{ $invoice->registrations->count() }} peserta</strong> dan mengaktifkan cetak kartu peserta mereka.
                </p>

                <div class="grid grid-cols-2 gap-3 pt-2">
                    <form action="{{ route('admin.invoices.verify', $invoice->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin nominal transfer SUDAH SESUAI dan ingin MENYETUJUI tagihan ini beserta seluruh pesertanya?')">
                        @csrf
                        <input type="hidden" name="action" value="approve">
                        <button type="submit" class="w-full py-3.5 px-4 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs shadow-md shadow-emerald-500/20 transition flex items-center justify-center gap-2">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            <span>Setujui & Lunas</span>
                        </button>
                    </form>

                    <button type="button" @click="rejectModal = true" class="w-full py-3.5 px-4 rounded-2xl bg-rose-50 hover:bg-rose-100 text-rose-600 font-black text-xs border border-rose-200 transition flex items-center justify-center gap-2">
                        <i data-lucide="x" class="w-4 h-4"></i>
                        <span>Tolak Pembayaran</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Right Column: Rincian Peserta & Cabang Lomba (7 Cols) -->
        <div class="lg:col-span-7 space-y-6">
            <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-sm space-y-6">
                
                <!-- School & Official Info Header -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-4">
                    <div>
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Identitas Pendaftar</span>
                        <h3 class="text-lg font-black text-slate-900">{{ $invoice->user->institution_name ?? $invoice->user->name }}</h3>
                        <p class="text-xs text-slate-500">Official: {{ $invoice->user->name }} • No WA: {{ $invoice->user->phone ?? '-' }}</p>
                    </div>
                    <div class="text-right">
                        <span class="font-mono text-xs font-black text-slate-900 bg-slate-100 px-3 py-1.5 rounded-xl block">
                            {{ $invoice->invoice_number }}
                        </span>
                        <span class="text-[11px] text-slate-400 font-medium block mt-1">
                            {{ $invoice->created_at->format('d F Y, H:i') }} WIB
                        </span>
                    </div>
                </div>

                <!-- Participants Table Breakdown -->
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700">
                            Rincian Siswa & Cabang Lomba ({{ $invoice->registrations->count() }} Pendaftaran)
                        </h4>
                    </div>

                    <div class="overflow-x-auto rounded-2xl border border-slate-200/80">
                        <table class="w-full text-left text-xs text-slate-600">
                            <thead class="text-[10px] font-bold uppercase tracking-wider bg-slate-50 text-slate-500 border-b border-slate-200">
                                <tr>
                                    <th class="py-3 px-3.5 text-center">No</th>
                                    <th class="py-3 px-3.5">Nama Siswa / Anggota</th>
                                    <th class="py-3 px-3.5">Cabang Lomba</th>
                                    <th class="py-3 px-3.5 font-mono">Kode Reg</th>
                                    <th class="py-3 px-3.5 text-right">Biaya</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 font-medium">
                                @foreach($invoice->registrations as $idx => $reg)
                                    <tr class="hover:bg-slate-50/80 transition">
                                        <td class="py-3 px-3.5 text-center text-slate-400 font-mono">
                                            {{ $idx + 1 }}
                                        </td>
                                        <td class="py-3 px-3.5">
                                            <div class="font-bold text-slate-900 text-xs">
                                                {{ $reg->team_name ?: ($reg->members->first()->full_name ?? $reg->institution_name) }}
                                            </div>
                                            <div class="text-[10px] text-slate-400">
                                                NISN: {{ $reg->members->first()->nisn ?? '-' }} ({{ $reg->members->first()->gender ?? 'L' }})
                                            </div>
                                        </td>
                                        <td class="py-3 px-3.5">
                                            <span class="font-bold text-slate-800">{{ $reg->competition->name }}</span>
                                            <span class="block text-[10px] text-slate-400 uppercase font-mono">{{ $reg->competition->code }}</span>
                                            @if($reg->sub_category)
                                                <span class="inline-block mt-0.5 px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                                    🏸 {{ $reg->sub_category }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-3.5 font-mono font-bold text-brand-700 text-[11px]">
                                            {{ $reg->registration_code }}
                                        </td>
                                        <td class="py-3 px-3.5 text-right font-black text-emerald-700">
                                            Rp {{ number_format($reg->fee, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-slate-50 border-t border-slate-200 font-bold text-slate-800">
                                <tr>
                                    <td colspan="4" class="py-3 px-3.5 text-right">Subtotal Biaya Pendaftaran:</td>
                                    <td class="py-3 px-3.5 text-right font-black text-slate-900">{{ $invoice->formatted_total }}</td>
                                </tr>
                                @if($invoice->unique_code > 0)
                                    <tr>
                                        <td colspan="4" class="py-2 px-3.5 text-right text-[11px] text-slate-500 font-normal">Kode Unik Transfer:</td>
                                        <td class="py-2 px-3.5 text-right font-mono text-[11px] text-slate-600">+{{ $invoice->unique_code }}</td>
                                    </tr>
                                @endif
                                <tr class="bg-emerald-50 text-emerald-900 text-sm">
                                    <td colspan="4" class="py-3.5 px-3.5 text-right font-black">TOTAL TAGIHAN LUNAS:</td>
                                    <td class="py-3.5 px-3.5 text-right font-black text-emerald-800 text-base">{{ $invoice->formatted_final_amount }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

            </div>
        </div>

    </div>

    <!-- Reject Modal Dialog -->
    <div x-show="rejectModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="rejectModal" @click="rejectModal = false" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"></div>

            <div x-show="rejectModal" class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full p-6 sm:p-8 space-y-6">
                
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <h3 class="text-lg font-black text-slate-900">Tolak Pembayaran Tagihan</h3>
                    <button @click="rejectModal = false" class="text-slate-400 hover:text-slate-600">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form action="{{ route('admin.invoices.verify', $invoice->id) }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="action" value="reject">

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                            Alasan Penolakan / Catatan untuk Pendaftar
                        </label>
                        <textarea name="rejection_reason" rows="3" required placeholder="Contoh: Nominal bukti transfer tidak sesuai, atau foto struk terpotong tidak terbaca..." class="block w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm outline-none"></textarea>
                    </div>

                    <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                        <button type="button" @click="rejectModal = false" class="px-5 py-2.5 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold">
                            Batal
                        </button>
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-md shadow-rose-500/20">
                            Konfirmasi Tolak Pembayaran
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

</div>
@endsection
