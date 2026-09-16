@extends('layouts.admin')

@section('title', 'Desainer Layout & Posisi Teks Sertifikat')
@section('page_title', 'Desainer Posisi Teks & Template Blangko')

@section('content')
@php
    $layout = $template->effective_layout;
    $bgUrl = $template->background_path ? asset('storage/' . $template->background_path) : null;
@endphp

<div class="space-y-6" x-data="{
    activeTab: 'nama',
    saving: false,
    saveSuccess: false,
    saveError: '',
    
    // Layout Configuration Object
    cfg: {{ json_encode($layout) }},

    // Active dragged element
    draggingElement: null,

    resetDefaults() {
        if (confirm('Kembalikan seluruh tata letak ke pengaturan awal?')) {
            this.cfg = {
                nomor: { top: 25, left: 50, size: 14, color: '#1e293b', bold: false, align: 'center', font: 'sans', visible: true },
                nama: { top: 43, left: 50, size: 32, color: '#0f172a', bold: true, align: 'center', font: 'serif', visible: true },
                sekolah: { top: 53, left: 50, size: 18, color: '#334155', bold: false, align: 'center', font: 'sans', visible: true },
                predikat: { top: 61, left: 50, size: 22, color: '#b45309', bold: true, align: 'center', font: 'sans', visible: true },
                lomba: { top: 68, left: 50, size: 18, color: '#1e293b', bold: true, align: 'center', font: 'sans', visible: true },
                tanggal: { top: 79, left: 75, size: 14, color: '#334155', bold: false, align: 'center', font: 'sans', visible: true },
                qrcode: { top: 75, left: 15, size: 75, visible: true },
                teks_1: { text: '', top: 35, left: 50, size: 16, color: '#1e293b', bold: false, align: 'center', font: 'sans', visible: false },
                teks_2: { text: '', top: 57, left: 50, size: 15, color: '#1e293b', bold: false, align: 'center', font: 'sans', visible: false },
                teks_3: { text: '', top: 71, left: 50, size: 14, color: '#1e293b', bold: false, align: 'center', font: 'sans', visible: false }
            };
        }
    },

    saveLayoutAjax() {
        this.saving = true;
        this.saveSuccess = false;
        this.saveError = '';

        fetch('{{ route('admin.certificates.template.layout', $template->id) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ layout_config: this.cfg })
        })
        .then(res => res.json())
        .then(data => {
            this.saving = false;
            if (data.success) {
                this.saveSuccess = true;
                setTimeout(() => this.saveSuccess = false, 3500);
            } else {
                this.saveError = data.message || 'Gagal menyimpan layout.';
            }
        })
        .catch(err => {
            this.saving = false;
            this.saveError = 'Terjadi kesalahan jaringan saat menyimpan.';
        });
    },

    testPrint() {
        const url = new URL('{{ route('admin.certificates.print') }}', window.location.origin);
        url.searchParams.set('type', '{{ $template->type }}');
        @if($template->competition_id)
        url.searchParams.set('competition_id', '{{ $template->competition_id }}');
        @endif
        url.searchParams.set('name', 'AHMAD FAUZI NURDIN');
        url.searchParams.set('school', 'MTs Negeri 1 Blitar');
        url.searchParams.set('rank', 'Juara 1');
        url.searchParams.set('cert_seq', '001');
        window.open(url.toString(), '_blank');
    }
}">

    <!-- Alert Notifikasi -->
    @if(session('success'))
    <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-semibold flex items-center justify-between">
        <div class="flex items-center gap-3">
            <i data-lucide="check-circle" class="w-5 h-5 text-emerald-400"></i>
            <span>{{ session('success') }}</span>
        </div>
        <button type="button" @click="$el.parentElement.remove()" class="text-emerald-400/60 hover:text-emerald-400">
            <i data-lucide="x" class="w-4 h-4"></i>
        </button>
    </div>
    @endif

    <!-- Top Action Bar -->
    <div class="ai-card p-4 sm:p-5 rounded-3xl border border-white/[0.08] shadow-xl flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <a href="{{ route('admin.certificates.index', ['type' => $template->type, 'competition_id' => $template->competition_id]) }}" class="w-10 h-10 rounded-2xl bg-white/[0.05] hover:bg-white/[0.1] border border-white/[0.08] flex items-center justify-center text-slate-300 hover:text-white transition">
                <i data-lucide="arrow-left" class="w-5 h-5"></i>
            </a>
            <div>
                <h2 class="text-base sm:text-lg font-black text-white flex items-center gap-2">
                    <span>Desainer Template: {{ $template->name }}</span>
                    <span class="text-[10px] font-bold px-2.5 py-0.5 rounded-full bg-purple-500/20 text-purple-300 border border-purple-500/30">
                        {{ strtoupper($template->type) }}
                    </span>
                </h2>
                <p class="text-xs text-slate-400">Sesuaikan posisi teks secara visual tepat di atas blangko JPG/PNG Anda.</p>
            </div>
        </div>

        <div class="flex items-center gap-2.5">
            <button type="button" @click="testPrint()" class="px-3.5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold border border-white/[0.1] transition flex items-center gap-2">
                <i data-lucide="printer" class="w-4 h-4 text-emerald-400"></i>
                <span>Uji Cetak Preview</span>
            </button>

            <button type="button" @click="saveLayoutAjax()" :disabled="saving" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 text-white text-xs font-bold shadow-lg shadow-purple-500/20 transition flex items-center gap-2 cursor-pointer disabled:opacity-50">
                <i data-lucide="save" class="w-4 h-4" x-show="!saving"></i>
                <span x-show="!saving">Simpan Tata Letak</span>
                <span x-show="saving">Menyimpan...</span>
            </button>
        </div>
    </div>

    <!-- Toast Floating Alert untuk AJAX Save -->
    <div x-show="saveSuccess" x-transition class="p-3.5 rounded-2xl bg-emerald-500 text-slate-950 font-black text-xs shadow-2xl flex items-center gap-2.5 fixed bottom-6 right-6 z-50">
        <i data-lucide="check-circle-2" class="w-5 h-5"></i>
        <span>Tata letak & koordinat teks berhasil disimpan ke database!</span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- ==================== KOLOM KIRI: UPLOAD BLANGKO & KONTROL KOORDINAT (5 COLS) ==================== -->
        <div class="lg:col-span-5 space-y-6">

            <!-- Card 1: Upload Blangko Template Gambar -->
            <div class="ai-card p-5 rounded-3xl border border-white/[0.08] shadow-lg space-y-4">
                <div class="flex items-center justify-between border-b border-white/[0.08] pb-3">
                    <h3 class="text-xs font-black uppercase tracking-wider text-white flex items-center gap-2">
                        <i data-lucide="upload-cloud" class="w-4 h-4 text-purple-400"></i>
                        <span>Upload Blangko Template (JPG/PNG)</span>
                    </h3>
                </div>

                <form action="{{ route('admin.certificates.template.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <input type="hidden" name="template_id" value="{{ $template->id }}">
                    <input type="hidden" name="type" value="{{ $template->type }}">

                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1.5">Nama Template:</label>
                        <input type="text" name="name" value="{{ old('name', $template->name) }}" required class="w-full px-3.5 py-2 rounded-xl bg-[#0C111D] border border-white/[0.1] text-xs text-white outline-none focus:border-purple-500">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-300 mb-1.5">Cabang Khusus (Opsional):</label>
                            <select name="competition_id" class="w-full px-3 py-2 rounded-xl bg-[#0C111D] border border-white/[0.1] text-xs text-white outline-none focus:border-purple-500">
                                <option value="">-- Berlaku Semua Cabang --</option>
                                @foreach($competitions as $c)
                                    <option value="{{ $c->id }}" {{ $template->competition_id == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-300 mb-1.5">Format Nomor Surat:</label>
                            <input type="text" name="number_format" value="{{ old('number_format', $template->number_format) }}" class="w-full px-3 py-2 rounded-xl bg-[#0C111D] border border-white/[0.1] text-xs text-white font-mono outline-none focus:border-purple-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1.5">Pilih File Gambar Blangko (A4 Landscape):</label>
                        <div class="relative border-2 border-dashed border-white/[0.15] hover:border-purple-500/50 rounded-2xl p-4 text-center cursor-pointer transition bg-white/[0.01]">
                            <input type="file" name="background_image" accept="image/png,image/jpeg,image/webp" class="absolute inset-0 opacity-0 cursor-pointer w-full h-full">
                            <i data-lucide="image" class="w-8 h-8 mx-auto text-purple-400/80 mb-2"></i>
                            <p class="text-xs font-bold text-white">Klik atau seret file gambar ke sini</p>
                            <p class="text-[10px] text-slate-400 mt-1">Format: JPG, PNG, atau WebP (Rekomendasi rasio A4 Landscape: 3508 x 2480 px, Maks. 10MB)</p>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-1">
                        <button type="submit" class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-500 text-white font-bold text-xs transition flex items-center gap-2">
                            <i data-lucide="upload" class="w-3.5 h-3.5"></i>
                            <span>Unggah & Simpan Template</span>
                        </button>

                        @if($template->background_path)
                        <span class="text-[11px] text-emerald-400 font-semibold flex items-center gap-1">
                            <i data-lucide="check" class="w-3.5 h-3.5"></i> Blangko Terpasang
                        </span>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Card 2: Pengatur Koordinat & Tipografi Teks -->
            <div class="ai-card p-5 rounded-3xl border border-white/[0.08] shadow-lg space-y-4">
                <div class="flex items-center justify-between border-b border-white/[0.08] pb-3">
                    <h3 class="text-xs font-black uppercase tracking-wider text-white flex items-center gap-2">
                        <i data-lucide="sliders" class="w-4 h-4 text-indigo-400"></i>
                        <span>Pengatur Posisi & Gaya Teks</span>
                    </h3>
                    <button type="button" @click="resetDefaults()" class="text-[11px] text-rose-400 hover:text-rose-300 font-bold transition flex items-center gap-1">
                        <i data-lucide="rotate-ccw" class="w-3 h-3"></i>
                        <span>Reset Default</span>
                    </button>
                </div>

                <!-- Sub-tab Selector Elemen Teks -->
                <div class="grid grid-cols-5 gap-1.5 p-1 bg-white/[0.04] rounded-2xl">
                    <button type="button" @click="activeTab = 'nama'" :class="activeTab === 'nama' ? 'bg-purple-600 text-white font-bold shadow' : 'text-slate-400 hover:text-white'" class="py-1.5 px-2 rounded-xl text-[11px] transition text-center">Nama</button>
                    <button type="button" @click="activeTab = 'predikat'" :class="activeTab === 'predikat' ? 'bg-purple-600 text-white font-bold shadow' : 'text-slate-400 hover:text-white'" class="py-1.5 px-2 rounded-xl text-[11px] transition text-center">Predikat</button>
                    <button type="button" @click="activeTab = 'sekolah'" :class="activeTab === 'sekolah' ? 'bg-purple-600 text-white font-bold shadow' : 'text-slate-400 hover:text-white'" class="py-1.5 px-2 rounded-xl text-[11px] transition text-center">Sekolah</button>
                    <button type="button" @click="activeTab = 'lomba'" :class="activeTab === 'lomba' ? 'bg-purple-600 text-white font-bold shadow' : 'text-slate-400 hover:text-white'" class="py-1.5 px-2 rounded-xl text-[11px] transition text-center">Lomba</button>
                    <button type="button" @click="activeTab = 'nomor'" :class="activeTab === 'nomor' ? 'bg-purple-600 text-white font-bold shadow' : 'text-slate-400 hover:text-white'" class="py-1.5 px-2 rounded-xl text-[11px] transition text-center">Nomor</button>
                    <button type="button" @click="activeTab = 'tanggal'" :class="activeTab === 'tanggal' ? 'bg-purple-600 text-white font-bold shadow' : 'text-slate-400 hover:text-white'" class="py-1.5 px-2 rounded-xl text-[11px] transition text-center">Tanggal</button>
                    <button type="button" @click="activeTab = 'teks_1'" :class="activeTab === 'teks_1' ? 'bg-purple-600 text-white font-bold shadow' : 'text-slate-400 hover:text-white'" class="py-1.5 px-2 rounded-xl text-[11px] transition text-center">Teks 1</button>
                    <button type="button" @click="activeTab = 'teks_2'" :class="activeTab === 'teks_2' ? 'bg-purple-600 text-white font-bold shadow' : 'text-slate-400 hover:text-white'" class="py-1.5 px-2 rounded-xl text-[11px] transition text-center">Teks 2</button>
                    <button type="button" @click="activeTab = 'teks_3'" :class="activeTab === 'teks_3' ? 'bg-purple-600 text-white font-bold shadow' : 'text-slate-400 hover:text-white'" class="py-1.5 px-2 rounded-xl text-[11px] transition text-center">Teks 3</button>
                    <button type="button" @click="activeTab = 'qrcode'" :class="activeTab === 'qrcode' ? 'bg-purple-600 text-white font-bold shadow' : 'text-slate-400 hover:text-white'" class="py-1.5 px-2 rounded-xl text-[11px] transition text-center">QR Code</button>
                </div>

                <!-- FORM SLIDERS UNTUK ELEMEN YANG DIPILIH -->
                <template x-for="(val, key) in cfg" :key="key">
                    <div x-show="activeTab === key" class="space-y-4 pt-1">
                        <!-- Toggle Tampilkan -->
                        <div class="flex items-center justify-between p-3 rounded-xl bg-white/[0.02] border border-white/[0.05]">
                            <div>
                                <span class="text-xs font-bold text-slate-300 block">Tampilkan Elemen Ini</span>
                                <span class="text-[10px] text-slate-500">Matikan jika sudah include di gambar template</span>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" x-model="val.visible" class="sr-only peer">
                                <div class="w-9 h-5 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-purple-600"></div>
                            </label>
                        </div>

                        <!-- Khusus Teks Tambahan (teks_1, teks_2, teks_3): Input Teks Kustom -->
                        <div x-show="key.startsWith('teks_')" class="space-y-1.5">
                            <label class="block text-xs font-bold text-slate-300">
                                Isi Kalimat / Teks Tambahan:
                            </label>
                            <textarea x-model="val.text" rows="2" placeholder="Contoh: Memberikan penghargaan kepada : atau Pada Kejuaraan Bulutangkis..." class="w-full px-3.5 py-2 rounded-xl bg-[#0C111D] border border-white/[0.1] text-xs text-white placeholder-slate-500 outline-none focus:border-purple-500"></textarea>
                            <p class="text-[10px] text-slate-500">Kosongkan jika teks ini sudah ada pada gambar blangko template Anda.</p>
                        </div>

                        <!-- Slider Posisi Vertikal (Top %) -->
                        <div>
                            <div class="flex items-center justify-between text-xs font-bold mb-1.5">
                                <span class="text-slate-400">Posisi Vertikal (Atas ke Bawah):</span>
                                <span class="text-purple-300 font-mono" x-text="val.top + '%'"></span>
                            </div>
                            <input type="range" min="0" max="100" step="0.5" x-model="val.top" class="w-full accent-purple-500 cursor-pointer">
                        </div>

                        <!-- Slider Posisi Horizontal (Left %) -->
                        <div>
                            <div class="flex items-center justify-between text-xs font-bold mb-1.5">
                                <span class="text-slate-400">Posisi Horizontal (Kiri ke Kanan):</span>
                                <span class="text-purple-300 font-mono" x-text="val.left + '%'"></span>
                            </div>
                            <input type="range" min="0" max="100" step="0.5" x-model="val.left" class="w-full accent-purple-500 cursor-pointer">
                        </div>

                        <!-- Ukuran Font / Size -->
                        <div>
                            <div class="flex items-center justify-between text-xs font-bold mb-1.5">
                                <span class="text-slate-400" x-text="key === 'qrcode' ? 'Ukuran Kotak QR (px):' : 'Ukuran Huruf / Font (px):'"></span>
                                <span class="text-purple-300 font-mono" x-text="val.size + 'px'"></span>
                            </div>
                            <input type="range" :min="key === 'qrcode' ? 40 : 10" :max="key === 'qrcode' ? 160 : 72" step="1" x-model="val.size" class="w-full accent-purple-500 cursor-pointer">
                        </div>

                        <!-- Warna & Gaya Teks (Khusus Non-QR) -->
                        <div x-show="key !== 'qrcode'" class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-1">
                            <div>
                                <label class="block text-xs font-bold text-slate-400 mb-1.5">Warna Teks:</label>
                                <div class="flex items-center gap-2">
                                    <input type="color" x-model="val.color" class="w-9 h-9 rounded-lg bg-transparent border-0 cursor-pointer">
                                    <input type="text" x-model="val.color" class="flex-1 px-2 py-1.5 rounded-lg bg-[#0C111D] border border-white/[0.1] text-xs font-mono text-white outline-none">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-400 mb-1.5">Rata Teks:</label>
                                <select x-model="val.align" class="w-full px-2 py-2 rounded-lg bg-[#0C111D] border border-white/[0.1] text-xs text-white outline-none">
                                    <option value="center">Rata Tengah (Center)</option>
                                    <option value="left">Rata Kiri (Left)</option>
                                    <option value="right">Rata Kanan (Right)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-400 mb-1.5">Gaya Huruf:</label>
                                <div class="flex items-center gap-2 pt-1">
                                    <label class="flex items-center gap-1.5 text-xs text-slate-300 cursor-pointer">
                                        <input type="checkbox" x-model="val.bold" class="rounded border-white/[0.2] bg-slate-900 text-purple-600 focus:ring-0">
                                        <span>Bold</span>
                                    </label>
                                    <select x-show="val.font !== undefined" x-model="val.font" class="px-2 py-1 rounded bg-[#0C111D] border border-white/[0.1] text-[11px] text-white">
                                        <option value="sans">Sans</option>
                                        <option value="serif">Serif</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- ==================== KOLOM KANAN: LIVE INTERACTIVE PREVIEW (7 COLS) ==================== -->
        <div class="lg:col-span-7 space-y-4">
            <div class="ai-card p-5 rounded-3xl border border-white/[0.08] shadow-lg">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-xs font-black uppercase tracking-wider text-white flex items-center gap-2">
                        <i data-lucide="eye" class="w-4 h-4 text-emerald-400"></i>
                        <span>Live Preview (Standar A4 Landscape)</span>
                    </h3>
                    <span class="text-[11px] text-slate-400 font-mono">297mm × 210mm</span>
                </div>

                <!-- CANVAS PREVIEW DENGAN BACKGROUND DAN TEKS OVERLAY DINAMIS -->
                <div class="relative w-full rounded-2xl overflow-hidden shadow-2xl border border-white/[0.1] bg-white select-none" style="aspect-ratio: 297 / 210;">
                    
                    <!-- Background Blangko Gambar (Jika Diunggah) -->
                    @if($bgUrl)
                        <img src="{{ $bgUrl }}" alt="Certificate Background" class="absolute inset-0 w-full h-full object-fill pointer-events-none">
                    @else
                        <!-- Desain Fallback Bawaan Elegan jika belum upload gambar -->
                        <div class="absolute inset-0 p-6 flex flex-col justify-between pointer-events-none bg-gradient-to-br from-amber-50 via-white to-amber-50 border-[12px] border-amber-600/30">
                            <div class="border-2 border-dashed border-amber-500/40 w-full h-full flex flex-col items-center justify-center text-center p-4">
                                <div class="opacity-10 absolute inset-0 flex items-center justify-center">
                                    <i data-lucide="award" class="w-64 h-64 text-amber-900"></i>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- LAYER TEKS OVERLAY DI ATAS BLANGKO -->
                    
                    <!-- 1. Nomor Sertifikat -->
                    <div x-show="cfg.nomor.visible" 
                         :style="{
                             position: 'absolute',
                             top: cfg.nomor.top + '%',
                             left: cfg.nomor.left + '%',
                             transform: 'translate(-50%, -50%)',
                             fontSize: (cfg.nomor.size * 0.45) + 'px',
                             color: cfg.nomor.color,
                             fontWeight: cfg.nomor.bold ? 'bold' : 'normal',
                             fontFamily: cfg.nomor.font === 'serif' ? 'Georgia, serif' : 'Plus Jakarta Sans, sans-serif'
                         }"
                         class="cursor-pointer transition hover:outline hover:outline-2 hover:outline-purple-500 whitespace-nowrap"
                         @click="activeTab = 'nomor'">
                        Nomor: 001/TALENTA/MTsN1-BLT/X/2026
                    </div>

                    <!-- 2. Nama Penerima -->
                    <div x-show="cfg.nama.visible" 
                         :style="{
                             position: 'absolute',
                             top: cfg.nama.top + '%',
                             left: cfg.nama.left + '%',
                             transform: 'translate(-50%, -50%)',
                             fontSize: (cfg.nama.size * 0.48) + 'px',
                             color: cfg.nama.color,
                             fontWeight: cfg.nama.bold ? 'bold' : 'normal',
                             fontFamily: cfg.nama.font === 'serif' ? 'Georgia, serif' : 'Plus Jakarta Sans, sans-serif',
                             letterSpacing: '0.5px'
                         }"
                         class="cursor-pointer transition hover:outline hover:outline-2 hover:outline-purple-500 whitespace-nowrap"
                         @click="activeTab = 'nama'">
                        AHMAD FAUZI NURDIN
                    </div>

                    <!-- 3. Asal Sekolah / Lembaga -->
                    <div x-show="cfg.sekolah.visible" 
                         :style="{
                             position: 'absolute',
                             top: cfg.sekolah.top + '%',
                             left: cfg.sekolah.left + '%',
                             transform: 'translate(-50%, -50%)',
                             fontSize: (cfg.sekolah.size * 0.45) + 'px',
                             color: cfg.sekolah.color,
                             fontWeight: cfg.sekolah.bold ? 'bold' : 'normal',
                             fontFamily: cfg.sekolah.font === 'serif' ? 'Georgia, serif' : 'Plus Jakarta Sans, sans-serif'
                         }"
                         class="cursor-pointer transition hover:outline hover:outline-2 hover:outline-purple-500 whitespace-nowrap"
                         @click="activeTab = 'sekolah'">
                        SDN Kepanjenlor 2 Kota Blitar
                    </div>

                    <!-- 4. Predikat Juara / Kategori -->
                    <div x-show="cfg.predikat.visible" 
                         :style="{
                             position: 'absolute',
                             top: cfg.predikat.top + '%',
                             left: cfg.predikat.left + '%',
                             transform: 'translate(-50%, -50%)',
                             fontSize: (cfg.predikat.size * 0.48) + 'px',
                             color: cfg.predikat.color,
                             fontWeight: cfg.predikat.bold ? 'bold' : 'normal',
                             fontFamily: cfg.predikat.font === 'serif' ? 'Georgia, serif' : 'Plus Jakarta Sans, sans-serif'
                         }"
                         class="cursor-pointer transition hover:outline hover:outline-2 hover:outline-purple-500 whitespace-nowrap"
                         @click="activeTab = 'predikat'">
                        JUARA 1
                    </div>

                    <!-- 5. Cabang Lomba -->
                    <div x-show="cfg.lomba.visible" 
                         :style="{
                             position: 'absolute',
                             top: cfg.lomba.top + '%',
                             left: cfg.lomba.left + '%',
                             transform: 'translate(-50%, -50%)',
                             fontSize: (cfg.lomba.size * 0.45) + 'px',
                             color: cfg.lomba.color,
                             fontWeight: cfg.lomba.bold ? 'bold' : 'normal',
                             fontFamily: cfg.lomba.font === 'serif' ? 'Georgia, serif' : 'Plus Jakarta Sans, sans-serif'
                         }"
                         class="cursor-pointer transition hover:outline hover:outline-2 hover:outline-purple-500 whitespace-nowrap"
                         @click="activeTab = 'lomba'">
                        Cabang Musabaqah Tilawatil Qur'an (MTQ) - Kategori Putra
                    </div>

                    <!-- 6. Tanggal Titimangsa -->
                    <div x-show="cfg.tanggal.visible" 
                         :style="{
                             position: 'absolute',
                             top: cfg.tanggal.top + '%',
                             left: cfg.tanggal.left + '%',
                             transform: 'translate(-50%, -50%)',
                             fontSize: (cfg.tanggal.size * 0.45) + 'px',
                             color: cfg.tanggal.color,
                             fontWeight: cfg.tanggal.bold ? 'bold' : 'normal',
                             fontFamily: cfg.tanggal.font === 'serif' ? 'Georgia, serif' : 'Plus Jakarta Sans, sans-serif'
                         }"
                         class="cursor-pointer transition hover:outline hover:outline-2 hover:outline-purple-500 whitespace-nowrap"
                         @click="activeTab = 'tanggal'">
                        Blitar, 17 Oktober 2026
                    </div>

                    <!-- 8. Teks Tambahan 1 (Opsional) -->
                    <div x-show="cfg.teks_1 && cfg.teks_1.visible && cfg.teks_1.text" 
                         :style="{
                             position: 'absolute',
                             top: cfg.teks_1.top + '%',
                             left: cfg.teks_1.left + '%',
                             transform: (cfg.teks_1.align === 'left' ? 'translate(0, -50%)' : (cfg.teks_1.align === 'right' ? 'translate(-100%, -50%)' : 'translate(-50%, -50%)')),
                             fontSize: (cfg.teks_1.size * 0.45) + 'px',
                             color: cfg.teks_1.color,
                             fontWeight: cfg.teks_1.bold ? 'bold' : 'normal',
                             fontFamily: cfg.teks_1.font === 'serif' ? 'Georgia, serif' : 'Plus Jakarta Sans, sans-serif',
                             textAlign: cfg.teks_1.align || 'center'
                         }"
                         class="cursor-pointer transition hover:outline hover:outline-2 hover:outline-purple-500 whitespace-pre-wrap max-w-[80%]"
                         @click="activeTab = 'teks_1'"
                         x-text="cfg.teks_1.text">
                    </div>

                    <!-- 9. Teks Tambahan 2 (Opsional) -->
                    <div x-show="cfg.teks_2 && cfg.teks_2.visible && cfg.teks_2.text" 
                         :style="{
                             position: 'absolute',
                             top: cfg.teks_2.top + '%',
                             left: cfg.teks_2.left + '%',
                             transform: (cfg.teks_2.align === 'left' ? 'translate(0, -50%)' : (cfg.teks_2.align === 'right' ? 'translate(-100%, -50%)' : 'translate(-50%, -50%)')),
                             fontSize: (cfg.teks_2.size * 0.45) + 'px',
                             color: cfg.teks_2.color,
                             fontWeight: cfg.teks_2.bold ? 'bold' : 'normal',
                             fontFamily: cfg.teks_2.font === 'serif' ? 'Georgia, serif' : 'Plus Jakarta Sans, sans-serif',
                             textAlign: cfg.teks_2.align || 'center'
                         }"
                         class="cursor-pointer transition hover:outline hover:outline-2 hover:outline-purple-500 whitespace-pre-wrap max-w-[80%]"
                         @click="activeTab = 'teks_2'"
                         x-text="cfg.teks_2.text">
                    </div>

                    <!-- 10. Teks Tambahan 3 (Opsional) -->
                    <div x-show="cfg.teks_3 && cfg.teks_3.visible && cfg.teks_3.text" 
                         :style="{
                             position: 'absolute',
                             top: cfg.teks_3.top + '%',
                             left: cfg.teks_3.left + '%',
                             transform: (cfg.teks_3.align === 'left' ? 'translate(0, -50%)' : (cfg.teks_3.align === 'right' ? 'translate(-100%, -50%)' : 'translate(-50%, -50%)')),
                             fontSize: (cfg.teks_3.size * 0.45) + 'px',
                             color: cfg.teks_3.color,
                             fontWeight: cfg.teks_3.bold ? 'bold' : 'normal',
                             fontFamily: cfg.teks_3.font === 'serif' ? 'Georgia, serif' : 'Plus Jakarta Sans, sans-serif',
                             textAlign: cfg.teks_3.align || 'center'
                         }"
                         class="cursor-pointer transition hover:outline hover:outline-2 hover:outline-purple-500 whitespace-pre-wrap max-w-[80%]"
                         @click="activeTab = 'teks_3'"
                         x-text="cfg.teks_3.text">
                    </div>

                    <!-- 7. QR Code Keabsahan -->
                    <div x-show="cfg.qrcode.visible" 
                         :style="{
                             position: 'absolute',
                             top: cfg.qrcode.top + '%',
                             left: cfg.qrcode.left + '%',
                             transform: 'translate(-50%, -50%)',
                             width: (cfg.qrcode.size * 0.45) + 'px',
                             height: (cfg.qrcode.size * 0.45) + 'px'
                         }"
                         class="cursor-pointer transition hover:outline hover:outline-2 hover:outline-purple-500 p-1 bg-white rounded shadow-sm border border-slate-300 flex items-center justify-center"
                         @click="activeTab = 'qrcode'">
                        <i data-lucide="qr-code" class="w-full h-full text-slate-800"></i>
                    </div>

                </div>

                <div class="mt-3 flex items-center justify-between text-xs text-slate-400">
                    <span>💡 <em>Klik pada teks di atas untuk langsung beralih ke pengatur posisi elemen tersebut.</em></span>
                    <span class="text-purple-300 font-semibold" x-text="'Sedang mengedit: ' + activeTab.toUpperCase()"></span>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
