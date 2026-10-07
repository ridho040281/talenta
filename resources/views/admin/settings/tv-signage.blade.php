@extends('layouts.admin')

@section('title', 'Layar TV & Digital Signage (Iklan & Sponsor)')
@section('page_title', 'Layar TV & Iklan')

@section('content')
<style>
    .slide-sortable-ghost {
        opacity: 0.35 !important;
        background: rgba(122, 90, 248, 0.15) !important;
        border: 2px dashed #7A5AF8 !important;
        transform: scale(0.97) !important;
        border-radius: 20px !important;
    }
    .slide-sortable-chosen {
        background: #101828 !important;
        border-color: #7A5AF8 !important;
        box-shadow: 0 14px 30px -6px rgba(122, 90, 248, 0.45) !important;
        border-radius: 20px !important;
    }
    .slide-sortable-drag {
        opacity: 1 !important;
        box-shadow: 0 25px 40px -10px rgba(0, 0, 0, 0.85) !important;
        transform: rotate(1.5deg) scale(1.03) !important;
        border-radius: 20px !important;
    }
</style>

<div class="space-y-6 relative" x-data="{
    activeTab: 'playlist',
    editModal: false,
    previewModal: false,
    currentEditSlide: {
        id: '',
        title: '',
        type: 'image',
        duration: 10,
        video_url: '',
        notes: '',
        is_active: true,
        media_url: ''
    },
    previewSlide: {
        title: '',
        type: 'image',
        media_url: '',
        video_url: '',
        duration: 10
    },
    newSlideType: 'image',
    imagePreviewUrl: null,
    copiedLink: false,
    
    // Toast Alert
    showToastAlert: false,
    toastMessage: '',
    toastType: 'success',
    toastTimeout: null,

    showToast(msg, type = 'success') {
        this.toastMessage = msg;
        this.toastType = type;
        this.showToastAlert = true;
        if (this.toastTimeout) clearTimeout(this.toastTimeout);
        this.toastTimeout = setTimeout(() => { this.showToastAlert = false; }, 4500);
        this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
    },

    copyTvUrl() {
        const url = '{{ route('public.tv.signage') }}';
        navigator.clipboard.writeText(url).then(() => {
            this.copiedLink = true;
            this.showToast('Tautan Layar TV berhasil disalin: ' + url, 'success');
            setTimeout(() => { this.copiedLink = false; }, 3000);
        }).catch(err => {
            this.showToast('Gagal menyalin tautan.', 'error');
        });
    },

    openEdit(slide) {
        this.currentEditSlide = {
            id: slide.id,
            title: slide.title || '',
            type: slide.type || 'image',
            duration: slide.duration || 10,
            video_url: slide.video_url || '',
            notes: slide.notes || '',
            is_active: slide.is_active !== undefined ? slide.is_active : true,
            media_url: slide.media_url || ''
        };
        this.editModal = true;
        this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
    },

    openPreview(slide) {
        this.previewSlide = {
            title: slide.title || '',
            type: slide.type || 'image',
            media_url: slide.media_url || '',
            video_url: slide.video_url || '',
            duration: slide.duration || 10
        };
        this.previewModal = true;
        this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
    },

    toggleSlide(id) {
        fetch('{{ url('/admin/settings/tv-signage/slide') }}/' + id + '/toggle', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                this.showToast(data.message, 'success');
                setTimeout(() => { window.location.reload(); }, 600);
            } else {
                this.showToast('Gagal mengubah status slide.', 'error');
            }
        })
        .catch(() => {
            this.showToast('Koneksi terputus. Gagal memperbarui.', 'error');
        });
    },

    handleFileChange(event) {
        const file = event.target.files[0];
        if (file) {
            if (file.type.startsWith('image/')) {
                this.imagePreviewUrl = URL.createObjectURL(file);
            } else if (file.type.startsWith('video/')) {
                this.imagePreviewUrl = URL.createObjectURL(file);
            }
        }
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
        class="fixed top-6 right-6 z-[9999] max-w-md w-full shadow-2xl rounded-2xl p-4 border flex items-center gap-3 backdrop-blur-xl pointer-events-auto"
        :class="toastType === 'success' ? 'bg-emerald-950/90 border-emerald-500/40 text-emerald-100' : 'bg-rose-950/90 border-rose-500/40 text-rose-100'"
        style="display: none;">
        <div class="p-2 rounded-xl shrink-0" :class="toastType === 'success' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-rose-500/20 text-rose-400'">
            <i :data-lucide="toastType === 'success' ? 'check-circle-2' : 'alert-triangle'" class="w-5 h-5"></i>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-xs font-bold" x-text="toastMessage"></p>
        </div>
        <button @click="showToastAlert = false" class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-white/10 transition">
            <i data-lucide="x" class="w-4 h-4"></i>
        </button>
    </div>

    <!-- Header Section (AIStarterKit Dark Style) -->
    <div class="ai-card rounded-3xl p-6 sm:p-8 border border-white/[0.08] shadow-2xl flex flex-col md:flex-row md:items-center justify-between gap-6 relative overflow-hidden">
        <div class="absolute -right-20 -bottom-20 w-80 h-80 rounded-full bg-gradient-to-tr from-[#7A5AF8]/20 via-[#4E6EFF]/20 to-[#FF58D5]/20 blur-3xl pointer-events-none"></div>

        <div class="space-y-2 relative z-10">
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-400">
                <span>TALENTA Admin</span>
                <span>/</span>
                <span>Pengaturan Sistem</span>
                <span>/</span>
                <span class="text-[#FF58D5] font-bold">Layar TV & Iklan</span>
            </div>
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-[#FF58D5]/30 to-[#7A5AF8]/30 border border-[#FF58D5]/40 flex items-center justify-center text-[#FF58D5] shadow-lg shadow-[#FF58D5]/20">
                    <i data-lucide="tv" class="w-6 h-6"></i>
                </div>
                <div>
                    <h2 class="text-xl sm:text-2xl lg:text-3xl font-black tracking-tight text-white font-display">
                        Layar TV & Digital Signage Iklan
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-400">
                        Atur slide tayangan poster, video sponsor, pamflet lomba, dan teks berjalan untuk Smart TV & Proyektor.
                    </p>
                </div>
            </div>
        </div>

        <!-- Quick Action Buttons -->
        <div class="flex flex-wrap items-center gap-2.5 relative z-10">
            <button type="button" @click="copyTvUrl()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-white/[0.06] hover:bg-white/[0.12] text-slate-200 hover:text-white font-bold text-xs border border-white/[0.12] transition shadow-sm cursor-pointer">
                <i data-lucide="copy" class="w-4 h-4 text-cyan-400"></i>
                <span x-text="copiedLink ? 'Tersalin!' : 'Salin Link TV'"></span>
            </button>

            <a href="{{ route('public.tv.signage') }}" target="_blank" class="gradient-btn inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl text-white font-black text-xs shadow-lg shadow-[#7A5AF8]/30 hover:scale-105 transition-transform duration-200">
                <i data-lucide="monitor-play" class="w-4 h-4"></i>
                <span>Buka Layar TV (Fullscreen)</span>
                <i data-lucide="external-link" class="w-3.5 h-3.5 opacity-80"></i>
            </a>
        </div>
    </div>

    <!-- Quick Stats Cards (4 Metrics) -->
    @php
        $activeSlidesCount = count(array_filter($slides, fn($s) => ($s['is_active'] ?? true)));
        $sponsorDurationSetting = (int) ($settings['tv_signage_sponsor_duration'] ?? ($settings['tv_signage_default_duration'] ?? 5));
        $defaultDurationSetting = (int) ($settings['tv_signage_default_duration'] ?? 10);
        $customSlidesDuration = array_reduce(array_filter($slides, fn($s) => ($s['is_active'] ?? true)), fn($carry, $item) => $carry + ($item['duration'] ?? $defaultDurationSetting), 0);
        $totalSponsorsCount = count($sponsorLogos);
        $totalCycleDuration = $defaultDurationSetting + $customSlidesDuration + ($totalSponsorsCount * $sponsorDurationSetting);
    @endphp
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Metric 1: Total Slide Aktif -->
        <div class="ai-card rounded-2xl p-4.5 border border-white/[0.08] shadow-lg flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-[#7A5AF8]/15 border border-[#7A5AF8]/30 text-[#7A5AF8] flex items-center justify-center shrink-0">
                <i data-lucide="layers" class="w-6 h-6"></i>
            </div>
            <div class="min-w-0">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Slide Playlist</span>
                <div class="text-xl font-black text-white font-mono mt-0.5">
                    {{ $activeSlidesCount }} <span class="text-xs font-normal text-slate-400">Khusus + {{ $totalSponsorsCount }} Sponsor</span>
                </div>
            </div>
        </div>

        <!-- Metric 2: Total Durasi 1 Siklus -->
        <div class="ai-card rounded-2xl p-4.5 border border-white/[0.08] shadow-lg flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-cyan-500/15 border border-cyan-500/30 text-cyan-400 flex items-center justify-center shrink-0">
                <i data-lucide="timer" class="w-6 h-6"></i>
            </div>
            <div class="min-w-0">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Durasi 1 Putaran</span>
                <div class="text-xl font-black text-white font-mono mt-0.5">
                    {{ $totalCycleDuration }} <span class="text-xs font-normal text-slate-400">Detik</span>
                </div>
            </div>
        </div>

        <!-- Metric 3: Status 24 Logo Sponsor -->
        <div class="ai-card rounded-2xl p-4.5 border border-white/[0.08] shadow-lg flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-amber-500/15 border border-amber-500/30 text-amber-400 flex items-center justify-center shrink-0">
                <i data-lucide="award" class="w-6 h-6"></i>
            </div>
            <div class="min-w-0">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Strip Sponsor Landing</span>
                <div class="text-xl font-black text-amber-300 font-mono mt-0.5">
                    {{ $totalSponsorsCount }} <span class="text-xs font-normal text-slate-400">Logo Aktif</span>
                </div>
            </div>
        </div>

        <!-- Metric 4: Status Teks Berjalan -->
        <div class="ai-card rounded-2xl p-4.5 border border-white/[0.08] shadow-lg flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 flex items-center justify-center shrink-0">
                <i data-lucide="radio" class="w-6 h-6"></i>
            </div>
            <div class="min-w-0">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Running Text TV</span>
                <div class="text-sm font-black text-emerald-400 truncate mt-0.5" title="{{ $settings['tv_signage_running_text'] }}">
                    {{ !empty($settings['tv_signage_running_text']) ? 'Teks Aktif' : 'Nonaktif' }}
                </div>
            </div>
        </div>

    </div>

    <!-- Navigation Tabs -->
    <div class="flex items-center gap-2 pb-1 overflow-x-auto no-scrollbar">
        <button type="button" @click="activeTab = 'playlist'" :class="activeTab === 'playlist' ? 'gradient-btn text-white font-black shadow-lg shadow-[#7A5AF8]/25' : 'bg-white/[0.04] hover:bg-white/[0.08] text-slate-400 hover:text-slate-200 font-bold border border-white/[0.08]'" class="flex items-center gap-2 px-4 py-2.5 rounded-2xl text-xs transition whitespace-nowrap cursor-pointer">
            <i data-lucide="play-square" class="w-4 h-4 text-pink-400"></i>
            <span>Daftar Slide & Banner ({{ count($slides) }})</span>
        </button>

        <button type="button" @click="activeTab = 'add'" :class="activeTab === 'add' ? 'gradient-btn text-white font-black shadow-lg shadow-[#7A5AF8]/25' : 'bg-white/[0.04] hover:bg-white/[0.08] text-slate-400 hover:text-slate-200 font-bold border border-white/[0.08]'" class="flex items-center gap-2 px-4 py-2.5 rounded-2xl text-xs transition whitespace-nowrap cursor-pointer">
            <i data-lucide="plus-circle" class="w-4 h-4 text-emerald-400"></i>
            <span>+ Tambah Slide / Video Iklan</span>
        </button>

        <button type="button" @click="activeTab = 'settings'" :class="activeTab === 'settings' ? 'gradient-btn text-white font-black shadow-lg shadow-[#7A5AF8]/25' : 'bg-white/[0.04] hover:bg-white/[0.08] text-slate-400 hover:text-slate-200 font-bold border border-white/[0.08]'" class="flex items-center gap-2 px-4 py-2.5 rounded-2xl text-xs transition whitespace-nowrap cursor-pointer">
            <i data-lucide="sliders" class="w-4 h-4 text-cyan-400"></i>
            <span>Pengaturan Teks Berjalan & Layout TV</span>
        </button>
    </div>

    <!-- TAB 1: DAFTAR SLIDE & BANNER (PLAYLIST) -->
    <div x-show="activeTab === 'playlist'" x-transition class="space-y-6">
        
        <div class="ai-card rounded-3xl p-6 border border-white/[0.08] shadow-2xl space-y-5">
            
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-white/[0.08] pb-4">
                <div>
                    <h3 class="text-base font-black text-white font-display flex items-center gap-2">
                        <span>Urutan Tayang Slide TV</span>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                            Auto Loop
                        </span>
                    </h3>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Geser & lepas kartu slide untuk mengubah urutan tayang secara instan.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" @click="activeTab = 'add'" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-emerald-600/80 hover:bg-emerald-600 text-white text-xs font-bold transition shadow-sm cursor-pointer">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        <span>Tambah Slide Baru</span>
                    </button>
                </div>
            </div>

            @if(count($slides) > 0)
                <div id="slides-sortable-container" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
                    @foreach($slides as $index => $slide)
                        @php
                            $slideId = $slide['id'] ?? ('slide_' . $index);
                            $cleanMedia = !empty($slide['media_path']) ? ltrim(str_replace(['public/', 'storage/'], '', $slide['media_path']), '/') : null;
                            $mediaUrl = $cleanMedia ? asset('storage/' . $cleanMedia) : ($slide['video_url'] ?? null);
                            $slideObj = array_merge($slide, ['media_url' => $mediaUrl]);
                            $isActive = $slide['is_active'] ?? true;
                            $isVideo = ($slide['type'] ?? 'image') === 'video';
                        @endphp
                        
                        <div data-id="{{ $slideId }}" class="slide-card group rounded-2xl bg-[#0C111D] border {{ $isActive ? 'border-white/[0.12] hover:border-[#7A5AF8]/60' : 'border-rose-500/30 opacity-60' }} overflow-hidden shadow-xl transition-all duration-200 flex flex-col relative">
                            
                            <!-- Drag Handle & Position Badge -->
                            <div class="p-3 bg-white/[0.02] border-b border-white/[0.06] flex items-center justify-between gap-2">
                                <div class="flex items-center gap-2">
                                    <span class="slide-order-badge px-2 py-0.5 rounded-lg bg-white/10 text-white font-mono font-bold text-[11px]">
                                        #{{ $slide['order'] ?? ($index + 1) }}
                                    </span>
                                    <span class="px-2 py-0.5 rounded-lg text-[10px] font-black uppercase tracking-wider {{ $isVideo ? 'bg-amber-500/20 text-amber-300 border border-amber-500/30' : 'bg-cyan-500/20 text-cyan-300 border border-cyan-500/30' }}">
                                        {{ $isVideo ? 'Video' : 'Gambar' }}
                                    </span>
                                </div>

                                <div class="flex items-center gap-1">
                                    <!-- Duration Badge -->
                                    <span class="px-2 py-0.5 rounded-lg bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 font-mono text-[11px] font-bold">
                                        {{ $slide['duration'] ?? 10 }}s
                                    </span>
                                    <!-- Drag Icon -->
                                    <div class="cursor-grab active:cursor-grabbing p-1 text-slate-400 hover:text-white" title="Geser untuk atur urutan">
                                        <i data-lucide="grip-vertical" class="w-4 h-4"></i>
                                    </div>
                                </div>
                            </div>

                            <!-- Media Thumbnail Area (16:9 Aspect Ratio) -->
                            <div class="aspect-video bg-black/60 relative overflow-hidden flex items-center justify-center group/thumb">
                                @if(!empty($mediaUrl))
                                    @if($isVideo)
                                        <video src="{{ $mediaUrl }}" class="w-full h-full object-cover pointer-events-none" muted preload="metadata"></video>
                                        <div class="absolute inset-0 bg-black/40 flex items-center justify-center">
                                            <div class="w-10 h-10 rounded-full bg-white/20 backdrop-blur-md flex items-center justify-center text-white">
                                                <i data-lucide="play" class="w-5 h-5 ml-0.5"></i>
                                            </div>
                                        </div>
                                    @else
                                        <img src="{{ $mediaUrl }}" alt="{{ $slide['title'] }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                    @endif
                                @else
                                    <div class="flex flex-col items-center justify-center text-slate-500 gap-1 p-4 text-center">
                                        <i data-lucide="image-off" class="w-8 h-8 opacity-40"></i>
                                        <span class="text-[10px]">Tanpa Media Fisik</span>
                                    </div>
                                @endif

                                <!-- Quick Preview Hover Overlay -->
                                <button type="button" @click="openPreview({{ json_encode($slideObj) }})" class="absolute inset-0 bg-[#0C111D]/80 opacity-0 group-hover/thumb:opacity-100 transition-opacity duration-200 flex flex-col items-center justify-center gap-1 text-white text-xs font-bold cursor-pointer">
                                    <i data-lucide="eye" class="w-6 h-6 text-[#7A5AF8]"></i>
                                    <span>Pratinjau Layar</span>
                                </button>
                            </div>

                            <!-- Slide Details -->
                            <div class="p-3.5 flex-1 flex flex-col justify-between gap-2.5">
                                <div>
                                    <h4 class="text-xs font-black text-white line-clamp-1" title="{{ $slide['title'] }}">
                                        {{ $slide['title'] }}
                                    </h4>
                                    @if(!empty($slide['notes']))
                                        <p class="text-[10px] text-slate-400 line-clamp-1 mt-0.5" title="{{ $slide['notes'] }}">
                                            {{ $slide['notes'] }}
                                        </p>
                                    @endif
                                </div>

                                <!-- Action Toolbar -->
                                <div class="pt-2 border-t border-white/[0.06] flex items-center justify-between gap-1.5">
                                    <!-- Toggle Active Button -->
                                    <button type="button" @click="toggleSlide('{{ $slideId }}')" class="px-2 py-1 rounded-lg text-[10px] font-bold border transition cursor-pointer {{ $isActive ? 'bg-emerald-500/15 text-emerald-300 border-emerald-500/30 hover:bg-emerald-500/25' : 'bg-rose-500/15 text-rose-300 border-rose-500/30 hover:bg-rose-500/25' }}">
                                        {{ $isActive ? '● Tayang' : '○ Jeda' }}
                                    </button>

                                    <div class="flex items-center gap-1">
                                        <!-- Edit Button -->
                                        <button type="button" @click="openEdit({{ json_encode($slideObj) }})" class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-white/10 transition cursor-pointer" title="Edit Slide">
                                            <i data-lucide="edit-3" class="w-3.5 h-3.5 text-cyan-400"></i>
                                        </button>

                                        <!-- Delete Form -->
                                        <form action="{{ route('admin.settings.tv.signage.slide.delete', $slideId) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus slide \'{{ addslashes($slide['title']) }}\'?');" class="inline">
                                            @csrf
                                            <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-400 hover:bg-rose-500/10 transition cursor-pointer" title="Hapus Slide">
                                                <i data-lucide="trash-2" class="w-3.5 h-3.5 text-rose-400"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                        </div>
                    @endforeach
                </div>
            @else
                <!-- Empty State -->
                <div class="py-12 px-4 rounded-2xl bg-white/[0.02] border border-dashed border-white/[0.12] text-center space-y-4">
                    <div class="w-16 h-16 rounded-3xl bg-[#7A5AF8]/15 border border-[#7A5AF8]/30 text-[#7A5AF8] flex items-center justify-center mx-auto shadow-xl">
                        <i data-lucide="tv" class="w-8 h-8"></i>
                    </div>
                    <div class="max-w-md mx-auto space-y-1">
                        <h4 class="text-base font-black text-white">Belum Ada Slide Khusus di Playlist</h4>
                        <p class="text-xs text-slate-400">
                            Layar TV saat ini tetap berjalan secara otomatis menampilkan <b class="text-white">Showcase {{ $totalSponsorsCount }} Logo Sponsor & Pamflet Acara</b>. Tambahkan banner atau video kustom Anda untuk disiarkan di TV!
                        </p>
                    </div>
                    <button type="button" @click="activeTab = 'add'" class="gradient-btn inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl text-white font-bold text-xs shadow-lg shadow-[#7A5AF8]/30">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        <span>Unggah Slide Iklan Pertama</span>
                    </button>
                </div>
            @endif

        </div>

    </div>

    <!-- TAB 2: TAMBAH SLIDE / VIDEO IKLAN BARU -->
    <div x-show="activeTab === 'add'" x-transition class="space-y-6">
        
        <div class="ai-card rounded-3xl p-6 sm:p-8 border border-white/[0.08] shadow-2xl max-w-3xl mx-auto space-y-6">
            
            <div class="border-b border-white/[0.08] pb-4">
                <h3 class="text-lg font-black text-white font-display flex items-center gap-2">
                    <i data-lucide="plus-circle" class="w-5 h-5 text-emerald-400"></i>
                    <span>Tambah Slide Tayangan TV Baru</span>
                </h3>
                <p class="text-xs text-slate-400 mt-1">
                    Unggah poster promosi sponsor, pamflet cabang lomba, atau video iklan pendek.
                </p>
            </div>

            <form action="{{ route('admin.settings.tv.signage.slide.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                @csrf

                <!-- Judul Slide -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-slate-300">
                        Judul Slide / Nama Sponsor <span class="text-rose-400">*</span>
                    </label>
                    <input type="text" name="title" required placeholder="Contoh: Sponsor Utama BSI / Flyer Lomba Robotik" class="w-full px-4 py-2.5 text-xs rounded-xl bg-[#0C111D] border border-white/[0.12] text-white focus:border-[#7A5AF8] focus:ring-1 focus:ring-[#7A5AF8]">
                </div>

                <!-- Tipe Media Selection -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-slate-300">
                        Jenis Media
                    </label>
                    <div class="grid grid-cols-2 gap-3">
                        <label @click="newSlideType = 'image'" class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition" :class="newSlideType === 'image' ? 'bg-[#7A5AF8]/15 border-[#7A5AF8] text-white font-bold' : 'bg-[#0C111D] border-white/[0.1] text-slate-400'">
                            <input type="radio" name="type" value="image" x-model="newSlideType" class="hidden">
                            <i data-lucide="image" class="w-5 h-5 text-cyan-400"></i>
                            <div>
                                <span class="text-xs block">Gambar / Poster</span>
                                <span class="text-[10px] text-slate-500 font-normal">JPG, PNG, WebP (Rasio 16:9)</span>
                            </div>
                        </label>

                        <label @click="newSlideType = 'video'" class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition" :class="newSlideType === 'video' ? 'bg-[#7A5AF8]/15 border-[#7A5AF8] text-white font-bold' : 'bg-[#0C111D] border-white/[0.1] text-slate-400'">
                            <input type="radio" name="type" value="video" x-model="newSlideType" class="hidden">
                            <i data-lucide="video" class="w-5 h-5 text-amber-400"></i>
                            <div>
                                <span class="text-xs block">Video MP4</span>
                                <span class="text-[10px] text-slate-500 font-normal">File MP4, WebM (Maks 100MB)</span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- File Upload Area -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-slate-300">
                        Berkas Media (Unggah File) <span class="text-rose-400">*</span>
                    </label>
                    
                    <div class="border-2 border-dashed border-white/[0.15] hover:border-[#7A5AF8] rounded-2xl p-6 text-center bg-[#0C111D]/80 transition relative">
                        <input type="file" name="media_file" @change="handleFileChange($event)" :accept="newSlideType === 'image' ? 'image/jpeg,image/png,image/webp,image/svg+xml' : 'video/mp4,video/webm'" class="absolute inset-0 opacity-0 w-full h-full cursor-pointer z-10">
                        
                        <template x-if="!imagePreviewUrl">
                            <div class="space-y-2">
                                <div class="w-12 h-12 rounded-2xl bg-white/[0.04] text-slate-400 flex items-center justify-center mx-auto">
                                    <i :data-lucide="newSlideType === 'image' ? 'upload-cloud' : 'film'" class="w-6 h-6 text-[#7A5AF8]"></i>
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-white">Klik atau Tarik File ke Sini</p>
                                    <p class="text-[11px] text-slate-400 mt-0.5">
                                        Rekomendasi resolusi: <b class="text-white">1920 x 1080 (Full HD 16:9)</b> agar pas sempurna di layar Smart TV.
                                    </p>
                                </div>
                            </div>
                        </template>

                        <template x-if="imagePreviewUrl">
                            <div class="space-y-2">
                                <template x-if="newSlideType === 'image'">
                                    <img :src="imagePreviewUrl" class="max-h-48 rounded-xl mx-auto shadow-lg object-contain">
                                </template>
                                <template x-if="newSlideType === 'video'">
                                    <video :src="imagePreviewUrl" class="max-h-48 rounded-xl mx-auto shadow-lg" controls muted></video>
                                </template>
                                <p class="text-xs font-bold text-emerald-400">Berkas Siap Diunggah (Klik untuk mengganti)</p>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Durasi Tayang Slide & Catatan -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-slate-300">
                            Durasi Tampil (Detik) <span class="text-rose-400">*</span>
                        </label>
                        <div class="relative">
                            <input type="number" name="duration" value="10" min="3" max="300" required class="w-full pl-4 pr-12 py-2.5 text-xs rounded-xl bg-[#0C111D] border border-white/[0.12] text-white focus:border-[#7A5AF8] focus:ring-1 focus:ring-[#7A5AF8] font-mono">
                            <span class="absolute right-3.5 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400">detik</span>
                        </div>
                        <p class="text-[10px] text-slate-500">Standar tayang: 10–15 detik per poster.</p>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-slate-300">
                            Catatan / Keterangan (Opsional)
                        </label>
                        <input type="text" name="notes" placeholder="Misal: Tayang selama sesi pembukaan" class="w-full px-4 py-2.5 text-xs rounded-xl bg-[#0C111D] border border-white/[0.12] text-white focus:border-[#7A5AF8] focus:ring-1 focus:ring-[#7A5AF8]">
                    </div>
                </div>

                <!-- Toggle Langsung Aktifkan -->
                <div class="flex items-center gap-3 p-3.5 rounded-xl bg-white/[0.02] border border-white/[0.08]">
                    <input type="checkbox" name="is_active" value="1" id="is_active_new" checked class="w-4 h-4 rounded text-[#7A5AF8] focus:ring-[#7A5AF8]">
                    <label for="is_active_new" class="text-xs font-bold text-slate-200 cursor-pointer">
                        Langsung aktifkan dan masukkan ke rotasi siaran TV sekarang
                    </label>
                </div>

                <!-- Submit Button -->
                <div class="flex items-center justify-end gap-3 pt-3 border-t border-white/[0.08]">
                    <button type="button" @click="activeTab = 'playlist'" class="px-4 py-2.5 rounded-xl bg-white/[0.06] hover:bg-white/[0.12] text-slate-300 font-bold text-xs transition">
                        Batal
                    </button>
                    <button type="submit" class="gradient-btn px-6 py-2.5 rounded-xl text-white font-black text-xs shadow-lg shadow-[#7A5AF8]/30 flex items-center gap-2">
                        <i data-lucide="upload" class="w-4 h-4"></i>
                        <span>Unggah & Simpan Slide</span>
                    </button>
                </div>

            </form>

        </div>

    </div>

    <!-- TAB 3: PENGATURAN TEKS BERJALAN & LAYOUT TV -->
    <div x-show="activeTab === 'settings'" x-transition class="space-y-6">
        
        <div class="ai-card rounded-3xl p-6 sm:p-8 border border-white/[0.08] shadow-2xl max-w-3xl mx-auto space-y-6">
            
            <div class="border-b border-white/[0.08] pb-4">
                <h3 class="text-lg font-black text-white font-display flex items-center gap-2">
                    <i data-lucide="sliders" class="w-5 h-5 text-cyan-400"></i>
                    <span>Konfigurasi Teks Berjalan & Tampilan Layar TV</span>
                </h3>
                <p class="text-xs text-slate-400 mt-1">
                    Atur teks ticker pengumuman di baris bawah TV, integrasi 24 logo sponsor, jam digital, dan efek transisi.
                </p>
            </div>

            <form action="{{ route('admin.settings.tv.signage.settings.update') }}" method="POST" class="space-y-5">
                @csrf

                <!-- Header Title & Subtitle TV -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-slate-300">
                            Judul Header di Layar TV
                        </label>
                        <input type="text" name="tv_signage_header_title" value="{{ $settings['tv_signage_header_title'] }}" class="w-full px-4 py-2.5 text-xs rounded-xl bg-[#0C111D] border border-white/[0.12] text-white focus:border-[#7A5AF8]">
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-slate-300">
                            Subjudul / Keterangan Acara
                        </label>
                        <input type="text" name="tv_signage_header_subtitle" value="{{ $settings['tv_signage_header_subtitle'] }}" class="w-full px-4 py-2.5 text-xs rounded-xl bg-[#0C111D] border border-white/[0.12] text-white focus:border-[#7A5AF8]">
                    </div>
                </div>

                <!-- Judul & Keterangan Slide Sponsor TV -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-amber-300 flex items-center gap-1.5">
                            <i data-lucide="award" class="w-3.5 h-3.5 text-amber-400"></i>
                            <span>Judul Slide Sponsor TV</span>
                        </label>
                        <input type="text" name="tv_signage_sponsor_title" value="{{ $settings['tv_signage_sponsor_title'] ?? 'Mitra & Sponsor Resmi' }}" placeholder="Mitra & Sponsor Resmi" class="w-full px-4 py-2.5 text-xs rounded-xl bg-[#0C111D] border border-amber-500/30 text-amber-200 focus:border-amber-400 font-bold">
                        <p class="text-[10px] text-slate-400">Judul teks yang tampil di atas logo sponsor pada layar TV.</p>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-slate-300">
                            Teks Keterangan Bawah Sponsor
                        </label>
                        <input type="text" name="tv_signage_sponsor_subtitle" value="{{ $settings['tv_signage_sponsor_subtitle'] ?? 'Terima kasih atas partisipasi dan dukungan sponsorship TALENTA 2026' }}" placeholder="Terima kasih atas partisipasi dan dukungan sponsorship" class="w-full px-4 py-2.5 text-xs rounded-xl bg-[#0C111D] border border-white/[0.12] text-white focus:border-[#7A5AF8]">
                        <p class="text-[10px] text-slate-400">Teks ucapan terima kasih di bagian bawah slide sponsor TV.</p>
                    </div>
                </div>

                <!-- Running Text (Teks Berjalan) -->
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-bold text-slate-300 flex items-center gap-2">
                            <i data-lucide="radio" class="w-4 h-4 text-emerald-400 animate-pulse"></i>
                            <span>Teks Berjalan (Running Text / Ticker Bawah TV)</span>
                        </label>
                        <span class="text-[10px] text-slate-400">Gunakan tanda pemisah • untuk jeda kalimat</span>
                    </div>
                    <textarea name="tv_signage_running_text" rows="3" class="w-full p-4 text-xs rounded-xl bg-[#0C111D] border border-white/[0.12] text-white focus:border-[#7A5AF8] font-sans leading-relaxed">{{ $settings['tv_signage_running_text'] }}</textarea>
                    <p class="text-[10px] text-slate-500">
                        Teks ini akan mengalir secara terus menerus di bagian paling bawah layar TV selama acara berlangsung.
                    </p>
                </div>

                <!-- Toggles & Options -->
                <div class="space-y-3 pt-2">
                    <span class="text-xs font-bold text-slate-300 block">Komponen Tambahan Layar TV</span>
                    
                    <!-- Toggle 1: Strip 24 Logo Sponsor -->
                    <label class="flex items-start gap-3 p-3.5 rounded-xl bg-white/[0.02] border border-white/[0.08] hover:border-white/[0.15] cursor-pointer transition">
                        <input type="checkbox" name="tv_signage_show_sponsor_marquee" value="1" {{ ($settings['tv_signage_show_sponsor_marquee'] ?? '1') == '1' ? 'checked' : '' }} class="w-4 h-4 rounded text-[#7A5AF8] focus:ring-[#7A5AF8] mt-0.5">
                        <div class="space-y-0.5">
                            <span class="text-xs font-bold text-white block">Tampilkan Strip 24 Logo Sponsor (Marquee Berjalan)</span>
                            <span class="text-[11px] text-slate-400 block">
                                Mengalirkan seluruh 24 logo sponsor dari halaman landing page secara otomatis di atas baris running text.
                            </span>
                        </div>
                    </label>

                    <!-- Toggle 2: Jam Digital & Tanggal Live -->
                    <label class="flex items-start gap-3 p-3.5 rounded-xl bg-white/[0.02] border border-white/[0.08] hover:border-white/[0.15] cursor-pointer transition">
                        <input type="checkbox" name="tv_signage_show_clock" value="1" {{ ($settings['tv_signage_show_clock'] ?? '1') == '1' ? 'checked' : '' }} class="w-4 h-4 rounded text-[#7A5AF8] focus:ring-[#7A5AF8] mt-0.5">
                        <div class="space-y-0.5">
                            <span class="text-xs font-bold text-white block">Tampilkan Widget Jam Digital & Tanggal Real-Time</span>
                            <span class="text-[11px] text-slate-400 block">
                                Menampilkan jam:menit:detik WIB dan hari/tanggal di pojok kanan atas TV.
                            </span>
                        </div>
                    </label>
                </div>

                <!-- Efek Transisi & Durasi Slide -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
                    <!-- Efek Transisi -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-slate-300">
                            Efek Animasi Transisi
                        </label>
                        <select name="tv_signage_transition" class="w-full px-4 py-2.5 text-xs rounded-xl bg-[#0C111D] border border-white/[0.12] text-white focus:border-[#7A5AF8]">
                            <option value="fade" {{ ($settings['tv_signage_transition'] ?? 'fade') == 'fade' ? 'selected' : '' }}>✨ Halus (Crossfade Effect)</option>
                            <option value="slide" {{ ($settings['tv_signage_transition'] ?? '') == 'slide' ? 'selected' : '' }}>➡️ Geser Horizontal (Slide Left)</option>
                            <option value="zoom" {{ ($settings['tv_signage_transition'] ?? '') == 'zoom' ? 'selected' : '' }}>🔍 Zoom Perlahan (Cinematic Ken Burns)</option>
                        </select>
                        <p class="text-[10px] text-slate-500">Animasi perpindahan antar slide di TV.</p>
                    </div>

                    <!-- Durasi Per Logo Sponsor Landing -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-amber-300 flex items-center gap-1.5">
                            <i data-lucide="award" class="w-3.5 h-3.5 text-amber-400"></i>
                            <span>Durasi Tiap Logo Sponsor (Detik)</span>
                        </label>
                        <div class="relative">
                            <input type="number" name="tv_signage_sponsor_duration" value="{{ $settings['tv_signage_sponsor_duration'] ?? ($settings['tv_signage_default_duration'] ?? 5) }}" min="2" max="60" required class="w-full pl-4 pr-12 py-2.5 text-xs rounded-xl bg-[#0C111D] border border-amber-500/30 text-amber-200 focus:border-amber-400 font-mono">
                            <span class="absolute right-3.5 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400">detik</span>
                        </div>
                        <p class="text-[10px] text-slate-400">Lama tayang per-1 logo sponsor dari landing page.</p>
                    </div>

                    <!-- Durasi Standar Slide / Pamflet Acara -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-slate-300 flex items-center gap-1.5">
                            <i data-lucide="clock" class="w-3.5 h-3.5 text-cyan-400"></i>
                            <span>Durasi Slide Acara (Detik)</span>
                        </label>
                        <div class="relative">
                            <input type="number" name="tv_signage_default_duration" value="{{ $settings['tv_signage_default_duration'] ?? 10 }}" min="3" max="120" required class="w-full pl-4 pr-12 py-2.5 text-xs rounded-xl bg-[#0C111D] border border-white/[0.12] text-white focus:border-[#7A5AF8] font-mono">
                            <span class="absolute right-3.5 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400">detik</span>
                        </div>
                        <p class="text-[10px] text-slate-400">Durasi default poster acara / pamflet.</p>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-white/[0.08]">
                    <button type="submit" class="gradient-btn px-6 py-2.5 rounded-xl text-white font-black text-xs shadow-lg shadow-[#7A5AF8]/30 flex items-center gap-2">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Simpan Konfigurasi TV</span>
                    </button>
                </div>

            </form>

        </div>

    </div>

    <!-- MODAL 1: EDIT SLIDE -->
    <div x-show="editModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm" style="display: none;">
        <div @click.away="editModal = false" class="ai-card rounded-3xl p-6 max-w-lg w-full border border-white/[0.15] shadow-2xl space-y-4">
            
            <div class="flex items-center justify-between border-b border-white/[0.08] pb-3">
                <h3 class="text-base font-black text-white font-display flex items-center gap-2">
                    <i data-lucide="edit-3" class="w-4 h-4 text-cyan-400"></i>
                    <span>Edit Slide Iklan TV</span>
                </h3>
                <button type="button" @click="editModal = false" class="text-slate-400 hover:text-white p-1 rounded-lg">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form :action="'{{ url('/admin/settings/tv-signage/slide') }}/' + currentEditSlide.id + '/update'" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf

                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-300">Judul Slide</label>
                    <input type="text" name="title" x-model="currentEditSlide.title" required class="w-full px-3.5 py-2 text-xs rounded-xl bg-[#0C111D] border border-white/[0.12] text-white focus:border-[#7A5AF8]">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-300">Durasi (Detik)</label>
                        <input type="number" name="duration" x-model="currentEditSlide.duration" min="3" max="300" required class="w-full px-3.5 py-2 text-xs rounded-xl bg-[#0C111D] border border-white/[0.12] text-white focus:border-[#7A5AF8] font-mono">
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-300">Tipe Media</label>
                        <select name="type" x-model="currentEditSlide.type" class="w-full px-3.5 py-2 text-xs rounded-xl bg-[#0C111D] border border-white/[0.12] text-white">
                            <option value="image">Gambar / Poster</option>
                            <option value="video">Video MP4</option>
                        </select>
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-300">Ganti File Media (Opsional)</label>
                    <input type="file" name="media_file" class="w-full text-xs text-slate-400 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[#7A5AF8] file:text-white hover:file:bg-[#6941C6]">
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-300">Catatan / Keterangan</label>
                    <input type="text" name="notes" x-model="currentEditSlide.notes" class="w-full px-3.5 py-2 text-xs rounded-xl bg-[#0C111D] border border-white/[0.12] text-white">
                </div>

                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" name="is_active" value="1" id="edit_is_active" :checked="currentEditSlide.is_active" class="w-4 h-4 rounded text-[#7A5AF8]">
                    <label for="edit_is_active" class="text-xs text-slate-300 font-bold">Tayangkan di siaran TV</label>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-white/[0.08]">
                    <button type="button" @click="editModal = false" class="px-4 py-2 rounded-xl bg-white/[0.06] text-slate-300 text-xs font-bold">Batal</button>
                    <button type="submit" class="gradient-btn px-5 py-2 rounded-xl text-white font-bold text-xs">Simpan Perubahan</button>
                </div>
            </form>

        </div>
    </div>

    <!-- MODAL 2: PREVIEW SLIDE -->
    <div x-show="previewModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/90 backdrop-blur-md" style="display: none;">
        <div @click.away="previewModal = false" class="ai-card rounded-3xl p-5 max-w-3xl w-full border border-white/[0.2] shadow-2xl space-y-4">
            
            <div class="flex items-center justify-between border-b border-white/[0.08] pb-2">
                <div class="flex items-center gap-2">
                    <i data-lucide="monitor" class="w-5 h-5 text-[#FF58D5]"></i>
                    <h3 class="text-sm font-black text-white" x-text="previewSlide.title"></h3>
                </div>
                <button type="button" @click="previewModal = false" class="text-slate-400 hover:text-white p-1 rounded-lg">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <div class="aspect-video bg-black rounded-2xl overflow-hidden flex items-center justify-center relative shadow-2xl">
                <template x-if="previewSlide.type === 'image' && previewSlide.media_url">
                    <img :src="previewSlide.media_url" class="w-full h-full object-contain">
                </template>
                <template x-if="previewSlide.type === 'video' && previewSlide.media_url">
                    <video :src="previewSlide.media_url" controls autoplay class="w-full h-full object-contain"></video>
                </template>
            </div>

            <div class="flex items-center justify-between text-xs text-slate-400">
                <span>Durasi Tayang: <b class="text-white font-mono" x-text="previewSlide.duration + ' detik'"></b></span>
                <button type="button" @click="previewModal = false" class="px-4 py-1.5 rounded-xl bg-white/10 hover:bg-white/20 text-white font-bold transition">Tutup</button>
            </div>

        </div>
    </div>

</div>

<!-- Sortable.js for Drag & Drop Reordering -->
<script src="{{ asset('vendor/sortable/Sortable.min.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const container = document.getElementById('slides-sortable-container');
        if (container && typeof Sortable !== 'undefined') {
            new Sortable(container, {
                animation: 200,
                ghostClass: 'slide-sortable-ghost',
                chosenClass: 'slide-sortable-chosen',
                dragClass: 'slide-sortable-drag',
                handle: '.cursor-grab',
                onEnd: function () {
                    const cards = container.querySelectorAll('.slide-card');
                    const orderedIds = [];
                    cards.forEach((card, idx) => {
                        orderedIds.push(card.getAttribute('data-id'));
                        const badge = card.querySelector('.slide-order-badge');
                        if (badge) badge.textContent = '#' + (idx + 1);
                    });

                    fetch('{{ route('admin.settings.tv.signage.reorder') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ ordered_ids: orderedIds })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success && window.Alpine) {
                            // Trigger toast
                            const root = document.querySelector('[x-data]');
                            if (root && root._x_dataStack) {
                                root._x_dataStack[0].showToast(data.message, 'success');
                            }
                        }
                    })
                    .catch(err => console.error('Error reordering slides:', err));
                }
            });
        }
    });
</script>
@endsection
