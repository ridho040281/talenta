@extends('layouts.admin')

@section('title', 'Data Peserta - ' . $competition->name)
@section('page_title', 'Data Peserta')

@section('content')
<div class="space-y-6" x-data="{ verifyModal: false, selectedReg: null }">
    
    <!-- Top Header (Compact & Mobile Friendly) -->
    <div class="bg-white rounded-2xl p-4 sm:p-6 border border-slate-200/80 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <span class="px-2.5 py-0.5 text-[11px] font-bold rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200">
                {{ $competition->category->name }}
            </span>
            <h2 class="text-xl sm:text-2xl font-black text-slate-900 mt-1">{{ $competition->name }}</h2>
            <p class="text-xs text-slate-500">Tinjau keabsahan identitas peserta dan tentukan status pendaftaran.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5 shrink-0">
            <a href="{{ route('pic.spin.wheel', $competition->id) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-black text-xs shadow-md shadow-amber-500/20 transition">
                <i data-lucide="disc" class="w-4 h-4"></i>
                <span>Mesin Spin Wheel</span>
            </a>
            <a href="{{ route('pic.dashboard') }}" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">
                Kembali
            </a>
        </div>
    </div>

    <!-- Filter Tabs (Horizontal Swipe on Mobile) -->
    <div class="flex items-center gap-2 overflow-x-auto no-scrollbar pb-1">
        <a href="{{ route('pic.participants', [$competition->id, 'status' => 'all']) }}" class="px-3.5 py-2 rounded-xl text-xs font-bold transition shrink-0 whitespace-nowrap {{ $statusFilter === 'all' ? 'bg-brand-600 text-white shadow-xs' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50' }}">
            Semua ({{ $competition->registrations->count() }})
        </a>
        <a href="{{ route('pic.participants', [$competition->id, 'status' => 'pending']) }}" class="px-3.5 py-2 rounded-xl text-xs font-bold transition shrink-0 whitespace-nowrap {{ $statusFilter === 'pending' ? 'bg-amber-500 text-slate-950 shadow-xs' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50' }}">
            Menunggu Verifikasi ({{ $competition->registrations->where('status', 'pending')->count() }})
        </a>
        <a href="{{ route('pic.participants', [$competition->id, 'status' => 'verified']) }}" class="px-3.5 py-2 rounded-xl text-xs font-bold transition shrink-0 whitespace-nowrap {{ $statusFilter === 'verified' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50' }}">
            Terverifikasi ({{ $competition->registrations->where('status', 'verified')->count() }})
        </a>
        <a href="{{ route('pic.participants', [$competition->id, 'status' => 'revision']) }}" class="px-3.5 py-2 rounded-xl text-xs font-bold transition shrink-0 whitespace-nowrap {{ $statusFilter === 'revision' ? 'bg-amber-600 text-white shadow-xs' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50' }}">
            Perlu Revisi ({{ $competition->registrations->where('status', 'revision')->count() }})
        </a>
    </div>

    <!-- Participants Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm text-slate-600">
                <thead class="text-[11px] font-bold uppercase tracking-wider bg-slate-50 text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-3.5 sm:px-6">Kode Reg</th>
                        <th class="py-3 px-3.5 sm:px-6">No. Peserta</th>
                        <th class="py-3 px-3.5 sm:px-6">Nama Peserta / Tim</th>
                        <th class="py-3 px-3.5 sm:px-6">Asal Sekolah</th>
                        <th class="py-3 px-3.5 sm:px-6">Berkas</th>
                        <th class="py-3 px-3.5 sm:px-6">No. Undian</th>
                        <th class="py-3 px-3.5 sm:px-6">Status</th>
                        <th class="py-3 px-3.5 sm:px-6 text-center">Aksi Verifikasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($registrations as $reg)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-4 px-6 font-mono text-xs font-bold text-brand-700">
                                {{ $reg->registration_code }}
                            </td>
                            <td class="py-4 px-6 font-mono font-bold text-slate-800 text-xs">
                                {{ $reg->participant_number ?? '-' }}
                            </td>
                            <td class="py-4 px-6 font-bold text-slate-900">
                                <div>{{ $reg->display_name }}</div>
                                @if($reg->sub_category)
                                    <span class="inline-block mt-1 px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                        🏸 {{ $reg->sub_category }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-xs">
                                {{ $reg->institution_name }}
                            </td>
                            <td class="py-4 px-6 text-xs">
                                <div class="flex items-center gap-1.5">
                                    @if($reg->document_file)
                                        <span class="px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 text-[10px] font-bold border border-emerald-200" title="Surat Tugas Ada">
                                            📄 Surat
                                        </span>
                                    @endif
                                    @if($reg->payment_proof)
                                        <span class="px-2 py-0.5 rounded bg-amber-50 text-amber-700 text-[10px] font-bold border border-amber-200" title="Bukti Transfer Ada">
                                            💳 Slip
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                @if($reg->draw_number)
                                    <span class="px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-800 font-mono font-bold text-xs border border-emerald-200">
                                        #{{ $reg->draw_number }}
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400">Belum diundi</span>
                                @endif
                            </td>
                            <td class="py-4 px-6">
                                @if($reg->status === 'verified')
                                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        Verified
                                    </span>
                                @elseif($reg->status === 'revision')
                                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                        Revision
                                    </span>
                                @elseif($reg->status === 'rejected')
                                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                        Rejected
                                    </span>
                                @else
                                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700">
                                        Pending
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-center">
                                <button type="button" @click="selectedReg = {{ $reg->toJson() }}; verifyModal = true" class="px-4 py-2 rounded-xl bg-brand-50 hover:bg-brand-100 text-brand-700 font-bold text-xs border border-brand-200/60 transition inline-flex items-center gap-1.5">
                                    <i data-lucide="check-square" class="w-3.5 h-3.5"></i>
                                    <span>Tinjau & Verifikasi</span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400">
                                Tidak ada data pendaftar pada kategori filter ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($registrations->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $registrations->links() }}
            </div>
        @endif
    </div>

    <!-- Verification Modal (Alpine.js) -->
    <div x-show="verifyModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="verifyModal" @click="verifyModal = false" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"></div>

            <div x-show="verifyModal" class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full p-6 sm:p-8 space-y-6">
                
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div>
                        <span class="text-xs font-mono font-bold text-brand-700" x-text="selectedReg ? selectedReg.registration_code : ''"></span>
                        <h3 class="text-lg font-black text-slate-900" x-text="selectedReg ? selectedReg.institution_name : ''"></h3>
                    </div>
                    <button @click="verifyModal = false" class="text-slate-400 hover:text-slate-600">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <!-- Collective Invoice Info (if applicable) -->
                <template x-if="selectedReg && selectedReg.invoice">
                    <div class="p-3.5 rounded-2xl bg-blue-50 border border-blue-200 text-xs text-blue-900 space-y-1">
                        <div class="flex items-center justify-between font-bold">
                            <span class="inline-flex items-center gap-1.5 text-blue-800">
                                <i data-lucide="file-spreadsheet" class="w-4 h-4 text-blue-600"></i>
                                <span>Pendaftaran Kolektif (Rombongan)</span>
                            </span>
                            <span class="font-mono text-xs px-2 py-0.5 bg-blue-100 rounded text-blue-800" x-text="selectedReg.invoice.invoice_number"></span>
                        </div>
                        <div class="flex items-center justify-between text-[11px] text-slate-600 pt-0.5">
                            <span>Total Tagihan Rombongan:</span>
                            <span class="font-black text-slate-900 font-mono" x-text="'Rp ' + Number(selectedReg.invoice.final_amount).toLocaleString('id-ID')"></span>
                        </div>
                    </div>
                </template>

                <!-- Inspect Uploaded Documents & Transfer Proof -->
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-3">
                    <span class="text-[10px] font-black uppercase tracking-wider text-slate-500 block">Lampiran Berkas & Pembayaran:</span>
                    <div class="grid grid-cols-2 gap-3 text-xs">
                        
                        <!-- Surat Tugas / Dokumen -->
                        <div class="p-3 rounded-xl bg-white border border-slate-200 space-y-2">
                            <span class="font-bold text-slate-700 block text-[11px]">📄 Surat Rekomendasi</span>
                            <template x-if="selectedReg && selectedReg.document_file">
                                <a :href="'/storage/' + selectedReg.document_file" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-bold hover:bg-emerald-100 transition">
                                    <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                                    <span>Buka Surat</span>
                                </a>
                            </template>
                            <template x-if="!selectedReg || !selectedReg.document_file">
                                <span class="text-slate-400 text-xs italic">Tidak ada berkas</span>
                            </template>
                        </div>

                        <!-- Bukti Transfer / Pembayaran -->
                        <div class="p-3 rounded-xl bg-white border border-slate-200 space-y-2">
                            <span class="font-bold text-slate-700 block text-[11px]">💳 Bukti Transfer Slip</span>
                            <template x-if="selectedReg && (selectedReg.payment_proof || (selectedReg.invoice && selectedReg.invoice.payment_proof))">
                                <a :href="'/storage/' + (selectedReg.payment_proof || selectedReg.invoice.payment_proof)" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-amber-50 text-amber-700 border border-amber-200 text-xs font-bold hover:bg-amber-100 transition">
                                    <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                                    <span>Buka Slip Transfer</span>
                                </a>
                            </template>
                            <template x-if="!selectedReg || (!selectedReg.payment_proof && (!selectedReg.invoice || !selectedReg.invoice.payment_proof))">
                                <span class="text-slate-400 text-xs italic">Tidak ada slip (Gratis)</span>
                            </template>
                        </div>

                    </div>
                </div>

                <form :action="'/pic/peserta/' + (selectedReg ? selectedReg.id : '') + '/verifikasi'" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Pilih Keputusan Status</label>
                        <select name="status" required class="block w-full px-4 py-3 rounded-xl border border-slate-200 text-sm font-bold outline-none focus:border-brand-500">
                            <option value="verified">✅ Terverifikasi (Terbitkan No. Peserta)</option>
                            <option value="revision">⚠️ Minta Revisi / Perbaikan Berkas</option>
                            <option value="rejected">❌ Tolak Pendaftaran</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Catatan Panitia / Alasan</label>
                        <textarea name="verification_notes" rows="3" placeholder="Contoh: Berkas sah dan lengkap / Bukti transfer terkonfirmasi..." class="block w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm outline-none focus:border-brand-500"></textarea>
                    </div>

                    <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                        <button type="button" @click="verifyModal = false" class="px-5 py-2.5 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold">
                            Batal
                        </button>
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold shadow-md shadow-brand-500/20">
                            Simpan Keputusan
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

</div>
@endsection
