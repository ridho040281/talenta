@extends('layouts.admin')

@section('title', 'Pusat Informasi & Pengumuman')
@section('page_title', 'Pusat Informasi')

@section('content')
<div class="space-y-6" x-data="{
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
    }
}">
    
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

                        <!-- Switch ON/OFF -->
                        <label class="relative inline-flex items-center cursor-pointer shrink-0">
                            <input type="hidden" name="popup_enabled" value="0">
                            <input type="checkbox" name="popup_enabled" value="1" x-model="enabled" :checked="enabled == '1'" class="sr-only peer">
                            <div class="w-12 h-6 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                        </label>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5 bg-[#0C111D]/80 p-4 rounded-2xl border border-white/[0.08]">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-300">
                                Status Pop-up Saat Ini
                            </label>
                            <p class="text-[10px] text-slate-500">Saklar utama modal</p>
                            <div class="flex items-center gap-2 pt-1">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border" :class="enabled == '1' ? 'bg-emerald-500/15 text-emerald-300 border-emerald-500/30' : 'bg-rose-500/15 text-rose-300 border-rose-500/30'">
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

                <!-- Submit Button -->
                <div class="pt-2">
                    <button type="submit" class="gradient-btn px-8 py-3.5 rounded-2xl text-white font-black text-xs shadow-xl shadow-[#7A5AF8]/30 hover:scale-[1.02] active:scale-[0.98] transition flex items-center gap-2.5 cursor-pointer uppercase tracking-wider">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        <span>Simpan & Rilis Informasi Baru</span>
                    </button>
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
                            $isCurrentActive = ($idx === 0 && ($settings['popup_enabled'] ?? '1') == '1');
                        @endphp
                        <div class="ai-card rounded-2xl p-4 sm:p-5 border transition-all duration-200 space-y-3 relative group {{ $isCurrentActive ? 'border-amber-500/50 bg-amber-500/[0.03]' : 'border-white/[0.08] hover:border-white/[0.18]' }}">
                            
                            <!-- Header: Target & Timestamp -->
                            <div class="flex items-start justify-between gap-2 border-b border-white/[0.06] pb-2.5">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold border {{ $targetBadge['class'] }}">
                                            {{ $targetBadge['label'] }}
                                        </span>
                                        @if($isCurrentActive)
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-black bg-emerald-500/15 text-emerald-300 border border-emerald-500/30 flex items-center gap-1">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                                <span>Sedang Aktif</span>
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-[10px] text-slate-400 flex items-center gap-1">
                                        <i data-lucide="clock" class="w-3 h-3 text-slate-500"></i>
                                        <span>
                                            @if(!empty($item['timestamp']))
                                                {{ \Carbon\Carbon::createFromTimestamp($item['timestamp'])->setTimezone('Asia/Jakarta')->translatedFormat('d F Y, H:i') }} WIB
                                            @else
                                                {{ $item['created_at'] ?? '-' }}
                                            @endif
                                        </span>
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
