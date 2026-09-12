@extends('layouts.admin')

@section('title', 'Data Peserta - ' . $competition->name)
@section('page_title', 'Data Peserta')

@section('content')
<div class="space-y-6" x-data="{ 
    verifyModal: false, 
    selectedReg: null,
    activeDocTab: 'surat',
    mobileViewTab: 'data',
    pdfFitMode: 'Fit',
    get hasDocFile() {
        return !!(this.selectedReg && this.selectedReg.document_file);
    },
    get hasPaymentFile() {
        if (!this.selectedReg) return false;
        return !!(this.selectedReg.payment_proof || (this.selectedReg.invoice && this.selectedReg.invoice.payment_proof));
    },
    get currentDocUrl() {
        if (!this.selectedReg) return '';
        if (this.activeDocTab === 'surat') {
            if (this.selectedReg.document_file) {
                return '{{ url("storage") }}/' + String(this.selectedReg.document_file).replace(/^(public\/|storage\/)+/, '');
            }
            return '';
        }
        if (this.activeDocTab === 'payment') {
            const p = this.selectedReg.payment_proof || (this.selectedReg.invoice && this.selectedReg.invoice.payment_proof);
            if (p) {
                return '{{ url("storage") }}/' + String(p).replace(/^(public\/|storage\/)+/, '');
            }
            return '';
        }
        return '';
    },
    get isCurrentDocPdf() {
        const url = this.currentDocUrl;
        if (!url) return false;
        const clean = url.split('?')[0].split('#')[0].toLowerCase();
        return clean.endsWith('.pdf') || clean.includes('.pdf');
    },
    renderPdfDocument() {
        this.$nextTick(() => {
            const container = this.$refs.pdfContainer;
            if (!container) return;
            this._currentPdfRenderId = (this._currentPdfRenderId || 0) + 1;
            if (window.renderPdfToContainer) {
                window.renderPdfToContainer(container, this.currentDocUrl, this.isCurrentDocPdf, this._currentPdfRenderId, () => this._currentPdfRenderId);
            }
        });
    },
    openVerify(reg) {
        this.selectedReg = reg;
        this.verifyModal = true;
        this.mobileViewTab = 'data';
        if (this.selectedReg && this.selectedReg.document_file) {
            this.activeDocTab = 'surat';
        } else if (this.selectedReg && (this.selectedReg.payment_proof || (this.selectedReg.invoice && this.selectedReg.invoice.payment_proof))) {
            this.activeDocTab = 'payment';
        } else {
            this.activeDocTab = 'surat';
        }
        this.$nextTick(() => { 
            if (window.lucide) lucide.createIcons(); 
            this.renderPdfDocument();
        });
    }
}">
    
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
                                <div>{{ $reg->pure_name }}</div>
                                @if($reg->sub_category)
                                    <span class="inline-block mt-1 px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                        {{ $reg->sub_category }}
                                    </span>
                                @endif
                                @if($reg->chosen_song)
                                    <span class="inline-block mt-1 px-2 py-0.5 rounded text-[10px] font-bold bg-purple-50 text-purple-800 border border-purple-200">
                                        🎵 {{ $reg->chosen_song }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-xs font-semibold text-slate-800">
                                {{ $reg->display_school }}
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
                                <button type="button" @click="openVerify({{ $reg->toJson() }})" class="px-4 py-2 rounded-xl bg-brand-50 hover:bg-brand-100 text-brand-700 font-bold text-xs border border-brand-200/60 transition inline-flex items-center gap-1.5 cursor-pointer">
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

    <style>
        .verify-grid-layout {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.25rem;
            align-items: stretch;
        }
        @media (min-width: 1024px) {
            .verify-grid-layout {
                grid-template-columns: repeat(12, minmax(0, 1fr));
            }
        }
        .verify-left-panel {
            width: 100%;
        }
        @media (min-width: 1024px) {
            .verify-left-panel {
                grid-column: span 5 / span 5;
                height: 750px;
                max-height: 82vh;
                overflow-y: auto;
            }
        }
        .verify-right-panel {
            display: flex;
            flex-direction: column;
            width: 100%;
            min-height: 480px;
        }
        @media (min-width: 1024px) {
            .verify-right-panel {
                grid-column: span 7 / span 7;
                height: 750px;
                min-height: 650px;
                max-height: 82vh;
            }
        }
        .verify-preview-frame {
            flex: 1 1 0%;
            min-height: 380px;
            width: 100%;
            display: flex;
            flex-direction: column;
            position: relative;
            overflow-y: auto;
        }
        @media (min-width: 1024px) {
            .verify-preview-frame {
                min-height: 0;
                height: 100%;
            }
        }
    </style>

    <!-- Verification Modal (Alpine.js) -->
    <div x-show="verifyModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-start justify-center min-h-screen p-2 sm:p-6 text-center">
            <div x-show="verifyModal" @click="verifyModal = false" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"></div>

            <div x-show="verifyModal" class="inline-block bg-white rounded-2xl sm:rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all w-full max-w-6xl xl:max-w-7xl 2xl:max-w-[1540px] p-3 sm:p-6 lg:p-7 space-y-3.5 my-2 sm:my-6">
                
                <!-- Modal Header -->
                <div class="flex items-start justify-between border-b border-slate-100 pb-3 gap-2">
                    <div class="flex flex-wrap items-center gap-2 flex-1 min-w-0">
                        <span class="text-[11px] sm:text-xs font-mono font-black text-brand-700 px-2.5 sm:px-3 py-1 bg-brand-50 border border-brand-200 rounded-lg sm:rounded-xl shrink-0" x-text="selectedReg ? selectedReg.registration_code : ''"></span>
                        <span class="text-[11px] sm:text-xs font-mono font-black text-slate-700 px-2.5 sm:px-3 py-1 bg-slate-100 border border-slate-200 rounded-lg sm:rounded-xl shrink-0" x-text="selectedReg && selectedReg.participant_number ? 'No: ' + selectedReg.participant_number : 'Belum Ada No.'"></span>
                        <div class="h-3.5 w-px bg-slate-200 hidden sm:block"></div>
                        <h3 class="text-sm sm:text-base font-black text-slate-900 truncate max-w-[200px] sm:max-w-none" x-text="selectedReg ? (selectedReg.display_school || selectedReg.institution_name) : ''"></h3>
                    </div>
                    <button @click="verifyModal = false" class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-400 hover:text-slate-700 flex items-center justify-center transition cursor-pointer shrink-0 ml-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Mobile View Mode Switcher (Only on screens < 1024px) -->
                <div class="flex lg:hidden items-center bg-slate-100 p-1 rounded-xl border border-slate-200 gap-1 shrink-0">
                    <button type="button" 
                            @click="mobileViewTab = 'data'" 
                            :class="mobileViewTab === 'data' ? 'bg-brand-600 text-white shadow-md font-black' : 'text-slate-600 hover:text-slate-900 font-bold'" 
                            class="flex-1 py-2 rounded-lg text-xs flex items-center justify-center gap-1.5 transition cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>1. Data & Form</span>
                    </button>
                    <button type="button" 
                            @click="mobileViewTab = 'doc'; renderPdfDocument();" 
                            :class="mobileViewTab === 'doc' ? 'bg-brand-600 text-white shadow-md font-black' : 'text-slate-600 hover:text-slate-900 font-bold'" 
                            class="flex-1 py-2 rounded-lg text-xs flex items-center justify-center gap-1.5 transition cursor-pointer relative">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <span>2. Lihat Dokumen</span>
                        <template x-if="hasDocFile || hasPaymentFile">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block shadow-sm"></span>
                        </template>
                    </button>
                </div>

                <!-- 2-Column Split View -->
                <div class="verify-grid-layout">
                    
                    <!-- SISI KIRI (DATA & FORMULIR VERIFIKASI) -->
                    <div class="verify-left-panel space-y-4 pr-1 sm:pr-2" :class="mobileViewTab === 'data' ? 'block' : 'hidden lg:block'">
                        
                        <!-- Collective Invoice Info (if applicable) -->
                        <template x-if="selectedReg && selectedReg.invoice">
                            <div class="p-3.5 rounded-2xl bg-blue-50 border border-blue-200 text-xs text-blue-900 space-y-1">
                                <div class="flex items-center justify-between font-bold">
                                    <span class="inline-flex items-center gap-1.5 text-blue-800">
                                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
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

                        <!-- Data Official -->
                        <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 text-xs space-y-1">
                            <span class="text-[10px] font-black uppercase tracking-wider text-slate-500 block">Official / Pembina Pendamping</span>
                            <div class="font-bold text-slate-900 text-sm" x-text="selectedReg && selectedReg.official_name ? selectedReg.official_name : '-'"></div>
                            <div class="text-slate-600" x-text="'Kontak: ' + (selectedReg && selectedReg.official_phone ? selectedReg.official_phone : '-')"></div>
                        </div>

                        <!-- Form Verifikasi -->
                        <form :action="'/pic/peserta/' + (selectedReg ? selectedReg.id : '') + '/verifikasi'" method="POST" class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-3.5">
                            @csrf

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">Pilih Keputusan Status</label>
                                <select name="status" required class="block w-full px-4 py-3 rounded-xl border border-slate-200 bg-white text-sm font-bold text-slate-800 outline-none focus:border-brand-500">
                                    <option value="verified">✅ Terverifikasi (Terbitkan No. Peserta)</option>
                                    <option value="revision">⚠️ Minta Revisi / Perbaikan Berkas</option>
                                    <option value="rejected">❌ Tolak Pendaftaran</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">Catatan Panitia / Alasan</label>
                                <textarea name="verification_notes" rows="2" placeholder="Contoh: Berkas sah dan lengkap / Bukti transfer terkonfirmasi..." class="block w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm outline-none focus:border-brand-500 text-slate-800 placeholder-slate-400"></textarea>
                            </div>

                            <div class="pt-3 flex flex-col sm:flex-row sm:items-center justify-end gap-2.5 border-t border-slate-200">
                                <button type="button" @click="verifyModal = false" class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-bold transition cursor-pointer text-center">
                                    Batal
                                </button>
                                <button type="submit" class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold shadow-md shadow-brand-500/20 transition cursor-pointer flex items-center justify-center gap-1.5 text-center">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    <span>Simpan Keputusan</span>
                                </button>
                            </div>
                        </form>

                    </div>

                    <!-- SISI KANAN (LIVE DOCUMENT VIEWER / PREVIEWER) -->
                    <div class="verify-right-panel bg-slate-900 rounded-2xl border border-slate-800 overflow-hidden p-3 sm:p-4 space-y-3" :class="mobileViewTab === 'doc' ? 'flex' : 'hidden lg:flex'">
                        
                        <!-- Tab Dokumen & Aksi -->
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-800 pb-3 shrink-0">
                            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 sm:pb-0">
                                <button type="button" 
                                        @click="activeDocTab = 'surat'; renderPdfDocument();" 
                                        :class="activeDocTab === 'surat' ? 'bg-emerald-500/20 text-emerald-300 border-emerald-500/40 font-black' : 'bg-white/5 text-slate-400 hover:text-white border-white/10 font-bold'" 
                                        class="px-3 py-1.5 rounded-xl border text-xs flex items-center gap-1.5 transition cursor-pointer shrink-0">
                                    <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    <span>Surat Rekomendasi</span>
                                    <template x-if="hasDocFile">
                                        <span class="w-2 h-2 rounded-full bg-emerald-400 inline-block shadow-sm shadow-emerald-400/50"></span>
                                    </template>
                                </button>

                                <button type="button" 
                                        @click="activeDocTab = 'payment'; renderPdfDocument();" 
                                        :class="activeDocTab === 'payment' ? 'bg-amber-500/20 text-amber-300 border-amber-500/40 font-black' : 'bg-white/5 text-slate-400 hover:text-white border-white/10 font-bold'" 
                                        class="px-3 py-1.5 rounded-xl border text-xs flex items-center gap-1.5 transition cursor-pointer shrink-0">
                                    <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                    <span>Bukti Pembayaran</span>
                                    <template x-if="hasPaymentFile">
                                        <span class="w-2 h-2 rounded-full bg-amber-400 inline-block shadow-sm shadow-amber-400/50"></span>
                                    </template>
                                </button>
                            </div>

                            <!-- Tombol Buka Tab Baru -->
                            <template x-if="currentDocUrl">
                                <a :href="currentDocUrl" target="_blank" class="px-3 py-1.5 rounded-xl bg-white/10 hover:bg-white/20 text-slate-200 hover:text-white border border-white/10 text-xs font-bold transition flex items-center justify-center gap-1.5 cursor-pointer shrink-0" title="Buka berkas di tab baru">
                                    <svg class="w-3.5 h-3.5 text-brand-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    <span>Buka File Asli</span>
                                </a>
                            </template>
                        </div>

                        <!-- Preview Viewport Frame -->
                        <div class="verify-preview-frame bg-slate-950 rounded-xl border border-slate-800 overflow-hidden">
                            <!-- Kondisi 1: Dokumen PDF (Canvas PDF.js) -->
                            <div x-show="currentDocUrl && isCurrentDocPdf" class="w-full h-full flex flex-col">
                                <div x-ref="pdfContainer" class="flex-1 w-full overflow-y-auto p-2 sm:p-4 flex flex-col items-center">
                                    <!-- Rendered dynamically by PDF.js -->
                                </div>
                            </div>

                            <!-- Kondisi 2: Gambar (JPG/PNG/WebP) -->
                            <template x-if="currentDocUrl && !isCurrentDocPdf">
                                <div class="w-full h-full flex items-center justify-center p-3 overflow-auto" style="flex: 1 1 0%; min-height: 0;">
                                    <img :src="currentDocUrl" alt="Lampiran Berkas" class="max-h-full max-w-full object-contain rounded-lg shadow-2xl transition hover:opacity-95 cursor-zoom-in" @click="window.open(currentDocUrl, '_blank')" title="Klik untuk membuka ukuran penuh">
                                </div>
                            </template>

                            <!-- Kondisi 3: Tidak Ada Dokumen Pada Tab Yang Dipilih -->
                            <template x-if="!currentDocUrl">
                                <div class="m-auto text-center p-6 space-y-2.5 text-slate-500">
                                    <div class="w-14 h-14 rounded-2xl bg-white/5 border border-white/10 flex items-center justify-center mx-auto text-slate-500">
                                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2zM3 10h18"/></svg>
                                    </div>
                                    <div class="text-sm font-bold text-slate-300">
                                        <span x-text="activeDocTab === 'surat' ? 'Surat Keterangan / Rekomendasi Belum Diunggah' : 'Bukti Pembayaran Tidak Tersedia (Gratis / Bayar Tunai)'"></span>
                                    </div>
                                    <p class="text-xs text-slate-500 max-w-sm mx-auto leading-relaxed">
                                        Pendaftar ini tidak melampirkan berkas pada kategori ini saat proses registrasi.
                                    </p>
                                </div>
                            </template>
                        </div>

                        <!-- Mobile Quick Switch Button to Form -->
                        <div class="lg:hidden pt-2 border-t border-slate-800 shrink-0">
                            <button type="button" @click="mobileViewTab = 'data'" class="w-full py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-black shadow-lg flex items-center justify-center gap-2 transition cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                                <span>Lanjut Isi Keputusan Verifikasi &rarr;</span>
                            </button>
                        </div>

                        <!-- Footer Keterangan Berkas -->
                        <div class="flex items-center justify-between text-[11px] text-slate-400 px-1 pt-1 shrink-0">
                            <span class="flex items-center gap-1.5 truncate">
                                <span class="w-1.5 h-1.5 rounded-full shrink-0" :class="currentDocUrl ? 'bg-emerald-400' : 'bg-slate-500'"></span>
                                <span class="truncate" x-text="activeDocTab === 'surat' ? 'Surat Keterangan Siswa' : 'Bukti Transfer Pembayaran'"></span>
                            </span>
                            <span class="font-mono text-xs text-slate-300 shrink-0 ml-2" x-text="isCurrentDocPdf ? 'Tipe: Dokumen PDF' : (currentDocUrl ? 'Tipe: Gambar (JPG/PNG)' : 'Status: Kosong')"></span>
                        </div>

                    </div>

                </div>

            </div>
        </div>
    </div>

</div>
@endsection
