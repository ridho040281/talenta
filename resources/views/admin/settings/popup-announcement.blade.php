@extends('layouts.admin')

@section('title', 'Pusat Informasi & Pengumuman')
@section('page_title', 'Pusat Informasi')

@section('content')
<div class="space-y-6 relative" x-data="{
    enabled: '{{ $settings['popup_enabled'] ?? '1' }}',
    target: '{{ $settings['popup_target'] ?? 'all' }}',
    title: '{{ addslashes($settings['popup_title'] ?? '📢 PENGUMUMAN RESMI TALENTA 2026') }}',
    subtitle: '{{ addslashes($settings['popup_subtitle'] ?? 'Informasi Petunjuk Teknis & Pendaftaran Peserta') }}',
    content: `{{ addslashes($settings['popup_content'] ?? '') }}`,
    btnText: '{{ addslashes($settings['popup_button_text'] ?? 'Lihat Katalog Lomba & Juknis') }}',
    btnUrl: '{{ addslashes($settings['popup_button_url'] ?? '#kategori') }}',
    secBtnText: '{{ addslashes($settings['popup_secondary_button_text'] ?? 'Saya Mengerti / Tutup') }}',
    previewModal: false,
    imagePreview: '{{ !empty($settings['popup_image']) ? asset('storage/' . $settings['popup_image']) : '' }}',
    
    // Instant AJAX toggle states
    isToggling: false,
    toastMessage: '',
    toastType: 'success',
    showToastAlert: false,
    toastTimeout: null,

    showToast(msg, type = 'success') {
        this.toastMessage = msg;
        this.toastType = type;
        this.showToastAlert = true;
        if (this.toastTimeout) clearTimeout(this.toastTimeout);
        this.toastTimeout = setTimeout(() => {
            this.showToastAlert = false;
        }, 4500);
        this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
    },

    toggleStatus() {
        if (this.isToggling) return;
        const nextState = (this.enabled == '1') ? '0' : '1';
        this.isToggling = true;

        fetch('{{ route('admin.settings.popup.toggle') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ popup_enabled: nextState })
        })
        .then(res => {
            if (!res.ok) throw new Error('HTTP error ' + res.status);
            return res.json();
        })
        .then(data => {
            if (data.success) {
                this.enabled = String(data.enabled);
                this.showToast(data.message, 'success');
            } else {
                this.showToast(data.message || 'Gagal menyimpan status pop-up.', 'error');
            }
        })
        .catch(err => {
            console.error('Error toggling popup status:', err);
            this.showToast('Gagal menghubungi server. Status belum tersimpan.', 'error');
        })
        .finally(() => {
            this.isToggling = false;
            this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
        });
    },

    // Preview modal states
    previewTitle: '',
    previewSubtitle: '',
    previewContent: '',
    previewBtnText: '',
    previewSecBtnText: '',
    previewImage: '',

    openPreview(t, sub, cont, bText, secText, img) {
        this.previewTitle = t || this.title;
        this.previewSubtitle = sub || this.subtitle;
        this.previewContent = cont || this.content;
        this.previewBtnText = bText || this.btnText;
        this.previewSecBtnText = secText || this.secBtnText;
        this.previewImage = (img !== undefined && img !== null) ? img : this.imagePreview;
        this.previewModal = true;
        this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
    }
}">

    <!-- Toast Notification (Fixed Floating Alert) -->
    <div x-show="showToastAlert"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-[-20px] scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-[-20px] scale-95"
        style="position: fixed; top: 1.5rem; right: 1.5rem; z-index: 9999999;"
        x-cloak>
        <div class="flex items-center gap-3.5 px-5 py-3.5 rounded-2xl shadow-2xl border backdrop-blur-2xl"
            :class="toastType === 'success' ? 'bg-[#061e14]/95 border-emerald-500/50 text-white shadow-emerald-950/60' : 'bg-[#27080f]/95 border-rose-500/50 text-white shadow-rose-950/60'">
            <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0"
                :class="toastType === 'success' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-rose-500/20 text-rose-400 border border-rose-500/30'">
                <template x-if="toastType === 'success'">
                    <i data-lucide="check-circle" class="w-5 h-5 text-emerald-400"></i>
                </template>
                <template x-if="toastType !== 'success'">
                    <i data-lucide="alert-circle" class="w-5 h-5 text-rose-400"></i>
                </template>
            </div>
            <div class="pr-2">
                <p class="text-[10px] font-black uppercase tracking-wider" :class="toastType === 'success' ? 'text-emerald-300' : 'text-rose-300'" x-text="toastType === 'success' ? 'Tersimpan Otomatis' : 'Perhatian'"></p>
                <p class="text-xs font-semibold text-slate-100 mt-0.5" x-text="toastMessage"></p>
            </div>
            <button type="button" @click="showToastAlert = false" class="p-1 rounded-lg text-slate-400 hover:text-white hover:bg-white/10 transition cursor-pointer">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
    </div>
    
    <!-- Top Action Bar & Navigation Breadcrumbs -->
    <div class="ai-card rounded-3xl p-5 sm:p-6 border border-white/[0.08] shadow-xl flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2.5">
                <span class="p-2 rounded-2xl bg-amber-500/20 text-amber-300 border border-amber-500/30 shadow-lg shadow-amber-500/10">
                    <i data-lucide="bell-ring" class="w-5 h-5 text-amber-400"></i>
                </span>
                <div>
                    <h2 class="text-xl sm:text-2xl font-black text-white ai-gradient-text font-display">Pusat Informasi & Pengumuman</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Kelola modal pengumuman resmi dan pantau seluruh riwayat informasi yang pernah dikirimkan.</p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2.5 shrink-0">
            <button type="button" @click="openPreview()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-indigo-500/20 hover:bg-indigo-500/30 text-indigo-300 border border-indigo-500/40 font-bold text-xs shadow-md transition cursor-pointer">
                <i data-lucide="eye" class="w-4 h-4"></i>
                <span>Simulasi Pop-up</span>
            </button>
            <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-white/[0.06] hover:bg-white/[0.12] text-slate-300 hover:text-white font-bold text-xs border border-white/[0.08] transition shadow-sm">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Kembali</span>
            </a>
        </div>
    </div>

    <!-- Main Grid: Left (Form Edit) & Right (History List) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- Left Column: Settings Form (7 Cols) -->
        <div class="lg:col-span-7 space-y-6">
            <form action="{{ route('admin.settings.popup.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <!-- Card 1: Status & Target -->
                <div class="ai-card rounded-3xl border border-white/[0.08] shadow-2xl p-6 sm:p-7 space-y-5">
                    <div class="flex items-center justify-between border-b border-white/[0.08] pb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-2xl bg-amber-500/15 text-amber-400 border border-amber-500/30 flex items-center justify-center font-bold">
                                <i data-lucide="power" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-black text-white font-display">Aktivasi & Target Tampilan</h3>
                                <p class="text-[11px] text-slate-400">Tentukan apakah pop-up aktif dan target pengguna yang melihat.</p>
                            </div>
                        </div>

                        <!-- Switch ON/OFF with Instant AJAX Auto-Save -->
                        <div class="flex items-center gap-3 shrink-0">
                            <!-- Hidden input for standard form POST -->
                            <input type="hidden" name="popup_enabled" :value="enabled">

                            <span x-show="isToggling" x-cloak class="text-[11px] text-amber-300 font-bold flex items-center gap-1.5 bg-amber-500/10 px-2.5 py-1 rounded-xl border border-amber-500/25">
                                <svg class="animate-spin h-3.5 w-3.5 text-amber-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span>Menyimpan...</span>
                            </span>

                            <button type="button" 
                                @click="toggleStatus()" 
                                :disabled="isToggling"
                                :title="enabled == '1' ? 'Klik untuk mematikan pop-up' : 'Klik untuk mengaktifkan pop-up'"
                                class="relative inline-flex items-center cursor-pointer transition-all duration-300 focus:outline-none select-none rounded-full shadow-lg"
                                :style="{
                                    width: '3.6rem',
                                    height: '2rem',
                                    backgroundColor: (enabled == '1') ? '#10b981' : '#334155',
                                    padding: '3px',
                                    boxShadow: (enabled == '1') ? '0 0 18px rgba(16, 185, 129, 0.45)' : 'none',
                                    border: '1.5px solid ' + ((enabled == '1') ? '#34d399' : '#475569')
                                }">
                                <span class="inline-block transform transition-transform duration-300 ease-in-out bg-white rounded-full shadow-md flex items-center justify-center"
                                    :style="{
                                        width: '1.5rem',
                                        height: '1.5rem',
                                        transform: (enabled == '1') ? 'translateX(1.6rem)' : 'translateX(0)'
                                    }">
                                    <template x-if="enabled == '1'">
                                        <i data-lucide="check" class="w-3.5 h-3.5 text-emerald-600"></i>
                                    </template>
                                    <template x-if="enabled != '1'">
                                        <i data-lucide="x" class="w-3.5 h-3.5 text-slate-400"></i>
                                    </template>
                                </span>
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5 bg-[#0C111D]/80 p-4 rounded-2xl border border-white/[0.08]">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-300">
                                Status Pop-up Saat Ini
                            </label>
                            <p class="text-[10px] text-slate-500">Otomatis tersimpan permanen saat saklar ditekan</p>
                            <div class="flex items-center gap-2 pt-1">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border transition-all duration-300" 
                                    :class="enabled == '1' ? 'bg-emerald-500/15 text-emerald-300 border-emerald-500/30 shadow-sm shadow-emerald-500/20' : 'bg-rose-500/15 text-rose-300 border-rose-500/30 shadow-sm shadow-rose-500/20'">
                                    <span class="w-2 h-2 rounded-full" :class="enabled == '1' ? 'bg-emerald-400 animate-pulse' : 'bg-rose-400'"></span>
                                    <span x-text="enabled == '1' ? '🟢 AKTIF (Tampil Otomatis)' : '🔴 NONAKTIF (Dimatikan)'"></span>
                                </span>
                            </div>
                        </div>

                        <div class="space-y-1.5 bg-[#0C111D]/80 p-4 rounded-2xl border border-white/[0.08]">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-300">
                                Target Muncul Informasi <span class="text-rose-400">*</span>
                            </label>
                            <p class="text-[10px] text-slate-500">Lokasi pop-up dimunculkan</p>
                            <select name="popup_target" x-model="target" required class="block w-full px-3 py-2.5 rounded-xl bg-[#161F30] border border-white/[0.1] text-white text-xs font-bold focus:border-[#7A5AF8] outline-none">
                                <option value="all">🌐 Tampil di Keduanya (Landing Page & Akun Peserta)</option>
                                <option value="landing">🏠 Hanya Halaman Depan (Landing Page)</option>
                                <option value="dashboard">👤 Hanya Dashboard Akun Peserta</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Konten Pengumuman & Media Gambar -->
                <div class="ai-card rounded-3xl border border-white/[0.08] shadow-2xl p-6 sm:p-7 space-y-5">
                    <div class="flex items-center gap-3 border-b border-white/[0.08] pb-4">
                        <div class="w-9 h-9 rounded-2xl bg-indigo-500/15 text-indigo-400 border border-indigo-500/30 flex items-center justify-center font-bold">
                            <i data-lucide="file-text" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-white font-display">Konten Informasi & Poster</h3>
                            <p class="text-[11px] text-slate-400">Teks judul, pesan pengumuman, dan gambar pamflet/poster info.</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <!-- Judul Pengumuman -->
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-300">
                                Judul Pengumuman Modal <span class="text-rose-400">*</span>
                            </label>
                            <input type="text" name="popup_title" x-model="title" required placeholder="Contoh: 📢 PENGUMUMAN RESMI TALENTA 2026" class="block w-full px-4 py-2.5 rounded-xl bg-[#0C111D] border border-white/[0.1] text-white text-xs font-bold focus:border-[#7A5AF8] outline-none">
                        </div>

                        <!-- Subjudul / Kategori -->
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-300">
                                Sub-Judul / Label Informasi <span class="text-slate-500 text-[10px] lowercase">(opsional)</span>
                            </label>
                            <input type="text" name="popup_subtitle" x-model="subtitle" placeholder="Contoh: Petunjuk Teknis & Ketentuan Pendaftaran" class="block w-full px-4 py-2.5 rounded-xl bg-[#0C111D] border border-white/[0.1] text-slate-200 text-xs font-medium focus:border-[#7A5AF8] outline-none">
                        </div>

                        <!-- Upload Poster / Gambar Banner Pop-up -->
                        <div class="space-y-2 bg-[#0C111D]/80 p-4 rounded-2xl border border-white/[0.08]">
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-300">
                                    Gambar Poster / Pamflet Informasi <span class="text-slate-500 text-[10px] lowercase">(opsional)</span>
                                </label>
                                @if(!empty($settings['popup_image']))
                                    <label class="inline-flex items-center gap-1.5 text-[11px] text-rose-400 hover:text-rose-300 font-bold cursor-pointer">
                                        <input type="checkbox" name="delete_popup_image" value="1" class="rounded border-slate-700 bg-slate-900 text-rose-500 focus:ring-0">
                                        <span>Hapus Poster Saat Ini</span>
                                    </label>
                                @endif
                            </div>
                            <p class="text-[10px] text-slate-500">Format: JPG, PNG, WebP (Maks 3MB). Disarankan rasio banner horizontal atau poster persegi.</p>
                            
                            <input type="file" name="popup_image" accept="image/*" @change="
                                const file = $event.target.files[0];
                                if (file) {
                                    imagePreview = URL.createObjectURL(file);
                                }
                            " class="block w-full text-xs text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[#7A5AF8]/20 file:text-[#A594FD] hover:file:bg-[#7A5AF8]/30 file:cursor-pointer">
                        </div>

                        <!-- Isi Pengumuman -->
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-300">
                                Isi Pesan Informasi / Pengumuman <span class="text-rose-400">*</span>
                            </label>
                            <p class="text-[10px] text-slate-500">Gunakan baris baru (Enter) untuk memisahkan paragraf atau poin pengumuman.</p>
                            <textarea name="popup_content" x-model="content" rows="6" required placeholder="Tuliskan pengumuman penting di sini..." class="block w-full p-4 rounded-xl bg-[#0C111D] border border-white/[0.1] text-slate-200 text-xs leading-relaxed focus:border-[#7A5AF8] outline-none font-sans"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Card 3: Tombol Aksi (CTA) & Tombol Tutup -->
                <div class="ai-card rounded-3xl border border-white/[0.08] shadow-2xl p-6 sm:p-7 space-y-5">
                    <div class="flex items-center gap-3 border-b border-white/[0.08] pb-4">
                        <div class="w-9 h-9 rounded-2xl bg-emerald-500/15 text-emerald-400 border border-emerald-500/30 flex items-center justify-center font-bold">
                            <i data-lucide="mouse-pointer-click" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-white font-display">Tombol Aksi & Navigasi Pop-up</h3>
                            <p class="text-[11px] text-slate-400">Atur teks tombol tindakan dan link pengalihan.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-300">
                                Teks Tombol Aksi Utama
                            </label>
                            <input type="text" name="popup_button_text" x-model="btnText" placeholder="Contoh: Lihat Katalog Lomba & Juknis" class="block w-full px-3.5 py-2.5 rounded-xl bg-[#0C111D] border border-white/[0.1] text-white text-xs font-bold focus:border-[#7A5AF8] outline-none">
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-300">
                                Link URL Tujuan Tombol Aksi
                            </label>
                            <input type="text" name="popup_button_url" x-model="btnUrl" placeholder="Contoh: #kategori atau /peserta/register/..." class="block w-full px-3.5 py-2.5 rounded-xl bg-[#0C111D] border border-white/[0.1] text-slate-200 text-xs font-mono focus:border-[#7A5AF8] outline-none">
                        </div>

                        <div class="sm:col-span-2 space-y-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-300">
                                Teks Tombol Tutup / Konfirmasi
                            </label>
                            <input type="text" name="popup_secondary_button_text" x-model="secBtnText" placeholder="Contoh: Saya Mengerti / Tutup" class="block w-full px-3.5 py-2.5 rounded-xl bg-[#0C111D] border border-white/[0.1] text-white text-xs font-semibold focus:border-[#7A5AF8] outline-none">
                        </div>
                    </div>
                </div>

                <!-- Action Buttons: Simpan Pengaturan vs Rilis Baru -->
                <div class="pt-2 space-y-2.5">
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                        <!-- Tombol 1: Simpan Perubahan Pengaturan -->
                        <button type="submit" name="save_action" value="update" class="px-5 py-3.5 rounded-2xl bg-emerald-600/25 hover:bg-emerald-600/40 border border-emerald-500/40 text-emerald-200 hover:text-white font-bold text-xs hover:scale-[1.01] active:scale-[0.99] transition flex items-center justify-center gap-2 cursor-pointer shadow-lg shadow-emerald-950/30">
                            <i data-lucide="save" class="w-4 h-4 text-emerald-400"></i>
                            <span>Simpan Perubahan Pengaturan</span>
                        </button>

                        <!-- Tombol 2: Rilis Sebagai Informasi Baru -->
                        <button type="submit" name="save_action" value="publish" onclick="return confirm('Apakah Anda yakin ingin merilis ini sebagai INFORMASI BARU?\n\nPengumuman ini akan dicatat sebagai riwayat baru dan otomatis muncul kembali ke seluruh pengunjung/peserta.');" class="gradient-btn px-6 py-3.5 rounded-2xl text-white font-black text-xs shadow-xl shadow-[#7A5AF8]/30 hover:scale-[1.02] active:scale-[0.98] transition flex items-center justify-center gap-2.5 cursor-pointer uppercase tracking-wider">
                            <i data-lucide="send" class="w-4 h-4"></i>
                            <span>Rilis Sebagai Informasi Baru</span>
                        </button>
                    </div>
                    <div class="flex items-center gap-1.5 text-[11px] text-slate-400 px-1">
                        <i data-lucide="info" class="w-3.5 h-3.5 text-indigo-400 shrink-0"></i>
                        <span>Saklar ON/OFF di atas otomatis tersimpan langsung saat diklik. Gunakan <b>Simpan Perubahan Pengaturan</b> untuk memperbarui teks/poster/target saat ini.</span>
                    </div>
                </div>

            </form>
        </div>

        <!-- Right Column: Riwayat Informasi yang Pernah Dikirim (5 Cols) -->
        <div class="lg:col-span-5 space-y-6">
            
            <div class="sticky top-20 space-y-4">
                
                <!-- Section Header: Riwayat Pesan -->
                <div class="flex items-center justify-between px-1">
                    <div class="flex items-center gap-2">
                        <span class="p-1 rounded-lg bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                            <i data-lucide="history" class="w-4 h-4 text-indigo-400"></i>
                        </span>
                        <h3 class="text-xs font-black uppercase tracking-wider text-slate-200">Riwayat Pesan & Rilis Informasi</h3>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-white/[0.08] text-slate-300 border border-white/[0.1]">
                        {{ count($histories) }} Riwayat
                    </span>
                </div>

                <!-- History List Container (Scrollable) -->
                <div class="space-y-3.5 max-h-[680px] overflow-y-auto pr-1 scrollbar-thin scrollbar-thumb-slate-700">
                    @forelse($histories as $idx => $item)
                        @php
                            $targetBadge = match($item['target'] ?? 'all') {
                                'landing' => ['label' => '🏠 Landing Page', 'class' => 'bg-cyan-500/15 text-cyan-300 border-cyan-500/30'],
                                'dashboard' => ['label' => '👤 Akun Peserta', 'class' => 'bg-purple-500/15 text-purple-300 border-purple-500/30'],
                                default => ['label' => '🌐 Semua Halaman', 'class' => 'bg-indigo-500/15 text-indigo-300 border-indigo-500/30'],
                            };
                        @endphp
                        <div class="ai-card rounded-2xl p-4 sm:p-5 border transition-all duration-200 space-y-3 relative group"
                            @if($idx === 0)
                                :class="(enabled == '1') ? 'border-amber-500/50 bg-amber-500/[0.03]' : 'border-white/[0.08] hover:border-white/[0.18]'"
                            @else
                                class="border-white/[0.08] hover:border-white/[0.18]"
                            @endif>
                            
                            <!-- Header: Target & Timestamp -->
                            <div class="flex items-start justify-between gap-2 border-b border-white/[0.06] pb-2.5">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold border {{ $targetBadge['class'] }}">
                                            {{ $targetBadge['label'] }}
                                        </span>
                                        @if($idx === 0)
                                            <span x-show="enabled == '1'" class="px-2 py-0.5 rounded-md text-[10px] font-black bg-emerald-500/15 text-emerald-300 border border-emerald-500/30 flex items-center gap-1">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                                <span>Sedang Aktif</span>
                                            </span>
                                            <span x-show="enabled != '1'" class="px-2 py-0.5 rounded-md text-[10px] font-black bg-rose-500/15 text-rose-300 border border-rose-500/30 flex items-center gap-1" x-cloak>
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span>
                                                <span>Nonaktif</span>
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-[10px] text-slate-400 flex items-center gap-1 flex-wrap">
                                        <i data-lucide="clock" class="w-3 h-3 text-slate-500"></i>
                                        <span>
                                            @if(!empty($item['timestamp']))
                                                {{ \Carbon\Carbon::createFromTimestamp($item['timestamp'])->setTimezone('Asia/Jakarta')->translatedFormat('d F Y, H:i') }} WIB
                                            @else
                                                {{ $item['created_at'] ?? '-' }}
                                            @endif
                                        </span>
                                        @if(!empty($item['updated_at']))
                                            <span class="text-amber-400/90 text-[9px] font-medium">(koreksi: {{ $item['updated_at'] }})</span>
                                        @endif
                                        @if(!empty($item['created_by']))
                                            <span class="text-slate-500">• {{ $item['created_by'] }}</span>
                                        @endif
                                    </p>
                                </div>

                                <!-- Delete History Item -->
                                <form action="{{ route('admin.settings.popup.history.delete', $item['id'] ?? $idx) }}" method="POST" onsubmit="return confirm('Hapus pesan riwayat ini?')">
                                    @csrf
                                    <button type="submit" class="p-1 rounded-lg text-slate-500 hover:text-rose-400 hover:bg-rose-500/10 transition" title="Hapus Riwayat">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                </form>
                            </div>

                            <!-- Content Details -->
                            <div class="flex items-start gap-3">
                                @if(!empty($item['image']))
                                    <div class="w-12 h-12 rounded-xl overflow-hidden border border-white/10 bg-black/40 shrink-0 flex items-center justify-center cursor-pointer" @click="openPreview({{ json_encode($item['title'] ?? '') }}, {{ json_encode($item['subtitle'] ?? '') }}, {{ json_encode($item['content'] ?? '') }}, {{ json_encode($item['button_text'] ?? '') }}, {{ json_encode($item['secondary_button_text'] ?? 'Tutup') }}, '{{ asset('storage/' . $item['image']) }}')">
                                        <img src="{{ asset('storage/' . $item['image']) }}" alt="Poster" class="w-full h-full object-cover">
                                    </div>
                                @endif
                                <div class="space-y-1 overflow-hidden flex-1">
                                    <h4 class="text-xs font-bold text-white leading-snug line-clamp-2">{{ $item['title'] ?? 'Pengumuman' }}</h4>
                                    <p class="text-[11px] text-slate-400 line-clamp-2 leading-relaxed whitespace-pre-line">{{ $item['content'] ?? '-' }}</p>
                                </div>
                            </div>

                            <!-- Action Buttons for History Item -->
                            <div class="pt-2 border-t border-white/[0.06] flex items-center justify-between gap-2">
                                <button type="button" 
                                    @click="
                                        title = {{ json_encode($item['title'] ?? '') }};
                                        subtitle = {{ json_encode($item['subtitle'] ?? '') }};
                                        content = {{ json_encode($item['content'] ?? '') }};
                                        target = {{ json_encode($item['target'] ?? 'all') }};
                                        btnText = {{ json_encode($item['button_text'] ?? '') }};
                                        btnUrl = {{ json_encode($item['button_url'] ?? '') }};
                                        secBtnText = {{ json_encode($item['secondary_button_text'] ?? 'Saya Mengerti / Tutup') }};
                                        imagePreview = {{ json_encode(!empty($item['image']) ? asset('storage/' . $item['image']) : '') }};
                                        window.scrollTo({ top: 0, behavior: 'smooth' });
                                    "
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-500/15 hover:bg-indigo-500/30 text-indigo-300 border border-indigo-500/30 text-[11px] font-bold transition cursor-pointer">
                                    <i data-lucide="rotate-ccw" class="w-3 h-3 text-indigo-400"></i>
                                    <span>Gunakan Lagi</span>
                                </button>

                                <button type="button" 
                                    @click="openPreview({{ json_encode($item['title'] ?? '') }}, {{ json_encode($item['subtitle'] ?? '') }}, {{ json_encode($item['content'] ?? '') }}, {{ json_encode($item['button_text'] ?? '') }}, {{ json_encode($item['secondary_button_text'] ?? 'Tutup') }}, {{ json_encode(!empty($item['image']) ? asset('storage/' . $item['image']) : '') }})"
                                    class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl bg-white/[0.04] hover:bg-white/[0.08] text-slate-300 text-[11px] font-semibold transition border border-white/[0.08] cursor-pointer">
                                    <i data-lucide="eye" class="w-3 h-3 text-slate-400"></i>
                                    <span>Pratinjau</span>
                                </button>
                            </div>

                        </div>
                    @empty
                        <div class="ai-card rounded-2xl p-8 border border-white/[0.08] text-center space-y-2 text-slate-400">
                            <i data-lucide="inbox" class="w-8 h-8 mx-auto text-slate-600"></i>
                            <p class="text-xs font-bold text-slate-300">Belum Ada Riwayat Informasi</p>
                            <p class="text-[11px] text-slate-500">Setiap informasi yang Anda simpan akan otomatis tercatat di panel riwayat ini.</p>
                        </div>
                    @endforelse
                </div>

                <!-- Info Box & Reset Button -->
                <div class="p-4 rounded-2xl bg-[#161F30]/80 border border-white/[0.08] text-xs text-slate-300 space-y-2.5 shadow-md">
                    <div class="flex items-center gap-2 text-white font-bold">
                        <i data-lucide="sparkles" class="w-4 h-4 text-amber-400"></i>
                        <span>Smart Memory & Munculkan Ulang:</span>
                    </div>
                    <p class="text-[11px] text-slate-400 leading-relaxed">
                        Pengguna hanya melihat pop-up 1 kali per versi. Jika Anda klik tombol <em>Reset Status Tampilan</em> di bawah, pop-up akan muncul kembali satu kali kepada seluruh pengguna.
                    </p>
                </div>

            </div>

        </div>

    </div>

    <!-- Reset Version Form (Outside main form to prevent conflicts) -->
    <div class="ai-card rounded-3xl p-6 border border-white/[0.08] flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-xl">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-2xl bg-amber-500/15 text-amber-400 border border-amber-500/30 flex items-center justify-center font-bold">
                <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
            </div>
            <div>
                <h4 class="text-xs font-black uppercase tracking-wider text-white">Munculkan Ulang Informasi ke Seluruh Pengguna</h4>
                <p class="text-[11px] text-slate-400">Reset status ingatan browser sehingga pengumuman muncul kembali satu kali ke semua pengunjung & peserta yang sudah pernah menutupnya.</p>
            </div>
        </div>
        <form action="{{ route('admin.settings.popup.reset-version') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin me-reset status informasi? Pop-up akan muncul kembali satu kali ke seluruh pengguna yang mengakses website.')">
            @csrf
            <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-amber-500/20 hover:bg-amber-500 text-amber-300 hover:text-slate-950 font-bold text-xs border border-amber-500/40 transition cursor-pointer">
                <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                <span>Reset Status Tampilan</span>
            </button>
        </form>
    </div>

    <!-- Interactive Simulation Modal Backdrop (Large & Opaque) -->
    <div x-show="previewModal" x-transition.opacity.duration.300ms class="fixed inset-0 z-[999999] flex items-center justify-center p-4 sm:p-6 bg-slate-950/85 backdrop-blur-md" style="display: none;">
        <div @click.away="previewModal = false" x-show="previewModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100" class="relative w-full max-w-3xl rounded-3xl border border-white/[0.2] bg-[#0C111D] p-6 sm:p-8 shadow-[0_0_60px_rgba(122,90,248,0.35)] space-y-6 text-white max-h-[90vh] overflow-y-auto no-scrollbar">
            
            <!-- Close Button -->
            <button type="button" @click="previewModal = false" class="absolute top-5 right-5 w-10 h-10 rounded-2xl bg-white/[0.08] hover:bg-white/[0.18] text-slate-300 hover:text-white flex items-center justify-center transition cursor-pointer">
                ✕
            </button>

            <!-- Subtitle -->
            <div class="flex items-center gap-2 pr-8">
                <span class="w-2.5 h-2.5 rounded-full bg-amber-400 animate-pulse"></span>
                <span class="text-xs font-black uppercase tracking-widest text-[#A594FD]" x-text="previewSubtitle || 'Informasi Resmi TALENTA 2026'"></span>
            </div>

            <!-- Poster Image Preview -->
            <template x-if="previewImage">
                <div class="rounded-2xl overflow-hidden border border-white/[0.12] max-h-[340px] bg-slate-950 flex items-center justify-center">
                    <img :src="previewImage" alt="Poster Preview" class="w-full h-full object-contain">
                </div>
            </template>

            <!-- Title -->
            <h2 class="text-xl sm:text-2xl font-black text-white leading-snug font-display" x-text="previewTitle || 'Judul Pengumuman'"></h2>

            <!-- Content -->
            <div class="text-sm sm:text-base text-slate-200 leading-relaxed whitespace-pre-line font-normal max-h-[300px] overflow-y-auto no-scrollbar bg-slate-900/60 p-5 rounded-2xl border border-white/[0.08]" x-text="previewContent || 'Isi teks pengumuman.'"></div>

            <!-- Action Buttons -->
            <div class="pt-4 border-t border-white/[0.08] flex flex-col sm:flex-row items-center justify-end gap-3">
                <button type="button" @click="previewModal = false" class="w-full sm:w-auto px-6 py-3 rounded-2xl bg-white/[0.08] hover:bg-white/[0.15] text-slate-200 font-bold text-xs border border-white/[0.1] transition cursor-pointer">
                    <span x-text="previewSecBtnText || 'Tutup'"></span>
                </button>
                <template x-if="previewBtnText">
                    <button type="button" @click="previewModal = false" class="w-full sm:w-auto px-7 py-3 rounded-2xl bg-gradient-to-r from-[#7A5AF8] to-[#4E6EFF] text-white font-black text-xs shadow-lg shadow-[#7A5AF8]/35 transition uppercase tracking-wider cursor-pointer">
                        <span x-text="previewBtnText"></span>
                    </button>
                </template>
            </div>

        </div>
    </div>

</div>
@endsection
