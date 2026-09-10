@php
    $popupEnabled = \App\Models\AppSetting::get('popup_enabled', '1') == '1';
    $popupTarget = \App\Models\AppSetting::get('popup_target', 'all');
    $popupVersion = \App\Models\AppSetting::get('popup_version', 'v1');
    $popupTitle = \App\Models\AppSetting::get('popup_title', '📢 PENGUMUMAN RESMI TALENTA 2026');
    $popupSubtitle = \App\Models\AppSetting::get('popup_subtitle', 'Informasi Resmi Panitia');
    $popupImage = \App\Models\AppSetting::get('popup_image');
    $popupContent = \App\Models\AppSetting::get('popup_content', "Selamat datang di Portal Resmi TALENTA MTsN 1 Blitar 2026.\n\nPastikan official dan peserta membaca Petunjuk Teknis (Juknis) masing-masing cabang lomba serta mematuhi batas akhir pendaftaran sebelum mengisi formulir.");
    $popupBtnText = \App\Models\AppSetting::get('popup_button_text', 'Lihat Katalog Lomba & Juknis');
    $popupBtnUrl = \App\Models\AppSetting::get('popup_button_url', '#kategori');
    $popupSecBtnText = \App\Models\AppSetting::get('popup_secondary_button_text', 'Saya Mengerti / Tutup');

    // Context can be 'landing' or 'dashboard'
    $currentContext = $context ?? 'landing';
    $isTargetMatch = ($popupTarget === 'all') || ($popupTarget === $currentContext);
@endphp

@if($popupEnabled && $isTargetMatch)
<div id="talenta-popup-announcement-root"
    x-data="{
        showModal: false,
        zoomImage: false,
        version: '{{ $popupVersion }}',
        storageKey: 'talenta_popup_seen_{{ $popupVersion }}',
        init() {
            if (!localStorage.getItem(this.storageKey)) {
                const tryShow = () => {
                    const globalModal = document.getElementById('globalAppModal');
                    const isGlobalActive = globalModal && (globalModal.style.display === 'flex' || (!globalModal.classList.contains('hidden') && !globalModal.classList.contains('opacity-0')));
                    if (isGlobalActive) {
                        // Wait for user to dismiss the feedback modal first
                        window.addEventListener('app-modal-closed', () => {
                            setTimeout(() => {
                                if (!this.showModal && !localStorage.getItem(this.storageKey)) {
                                    this.showModal = true;
                                    this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
                                }
                            }, 300);
                        }, { once: true });
                        return;
                    }
                    this.showModal = true;
                    this.$nextTick(() => {
                        if (window.lucide) {
                            window.lucide.createIcons();
                        }
                    });
                };
                setTimeout(tryShow, 300);
            }
        },
        closeModal() {
            try {
                localStorage.setItem(this.storageKey, 'true');
            } catch (e) {}
            this.showModal = false;
        }
    }"
    x-cloak
    @keydown.escape.window="zoomImage ? zoomImage = false : closeModal()">

    <!-- Fullscreen Backdrop (Pure Solid Overlay with High z-index) -->
    <div x-show="showModal"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        style="position: fixed !important; inset: 0 !important; top: 0 !important; left: 0 !important; width: 100vw !important; height: 100vh !important; background-color: rgba(6, 10, 20, 0.92) !important; backdrop-filter: blur(16px) !important; -webkit-backdrop-filter: blur(16px) !important; z-index: 999999 !important; display: flex !important; align-items: center !important; justify-content: center !important; padding: 1rem !important; overflow-y: auto !important; cursor: pointer !important;"
        @click="if ($event.target === $el && !zoomImage) closeModal()"
        @touchend="if ($event.target === $el && !zoomImage) closeModal()">
        
        <!-- Modal Card Container (Wide HD Layout for Infographics & Posters) -->
        <div x-show="showModal"
            @click.away="if (!zoomImage) closeModal()"
            x-transition:enter="transition ease-out duration-300 transform"
            x-transition:enter-start="opacity-0 scale-95 translate-y-6"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200 transform"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 translate-y-6"
            style="position: relative !important; width: 100% !important; max-width: min(62rem, 95vw) !important; background-color: #0f172a !important; border: 1.5px solid rgba(255, 255, 255, 0.22) !important; border-radius: 1.75rem !important; box-shadow: 0 30px 80px -10px rgba(0, 0, 0, 0.98), 0 0 70px rgba(122, 90, 248, 0.45) !important; overflow: hidden !important; display: flex !important; flex-direction: column !important; color: #f8fafc !important; max-height: 94vh !important; z-index: 1000000 !important; margin: auto !important; cursor: default !important;">

            <!-- Glowing Ambient Top Aura -->
            <div style="position: absolute; top: -5rem; left: -5rem; width: 18rem; height: 18rem; background: rgba(122, 90, 248, 0.5); border-radius: 9999px; filter: blur(75px); pointer-events: none;"></div>
            <div style="position: absolute; top: -5rem; right: -5rem; width: 18rem; height: 18rem; background: rgba(255, 88, 213, 0.45); border-radius: 9999px; filter: blur(75px); pointer-events: none;"></div>

            <!-- Modal Header (Sleek, High-Impact Banner) -->
            <div style="position: relative; z-index: 10; padding: 1.25rem 1.75rem; border-bottom: 1px solid rgba(255, 255, 255, 0.12); background-color: #131d33; display: flex; align-items: center; justify-content: space-between; gap: 1rem;">
                <div style="display: flex; flex-direction: column; gap: 0.35rem; padding-right: 1rem;">
                    <div style="display: inline-flex; align-items: center; gap: 0.45rem; padding: 0.3rem 0.8rem; border-radius: 9999px; background-color: rgba(245, 158, 11, 0.18); border: 1px solid rgba(245, 158, 11, 0.45); color: #fcd34d; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; width: fit-content;">
                        <i data-lucide="bell-ring" style="width: 0.9rem; height: 0.9rem; color: #fbbf24;"></i>
                        <span>{{ $popupSubtitle }}</span>
                    </div>
                    <h2 style="font-size: clamp(1.15rem, 2.2vw, 1.55rem); font-weight: 900; color: #ffffff; line-height: 1.25; margin: 0; font-family: 'Space Grotesk', 'Plus Jakarta Sans', sans-serif; letter-spacing: -0.02em;">
                        {{ $popupTitle }}
                    </h2>
                </div>
                <!-- Close Button -->
                <button type="button" @click="closeModal()" style="width: 2.5rem; height: 2.5rem; border-radius: 0.875rem; background-color: rgba(255, 255, 255, 0.08); border: 1px solid rgba(255, 255, 255, 0.15); color: #cbd5e1; display: flex; align-items: center; justify-content: center; cursor: pointer; flex-shrink: 0; transition: all 0.2s;" onmouseover="this.style.backgroundColor='rgba(255,255,255,0.25)'; this.style.color='#ffffff';" onmouseout="this.style.backgroundColor='rgba(255,255,255,0.08)'; this.style.color='#cbd5e1';" title="Tutup Pengumuman">
                    <i data-lucide="x" style="width: 1.25rem; height: 1.25rem;"></i>
                </button>
            </div>

            <!-- Modal Body (Maximized Image Presentation with Zoom Lightbox Support) -->
            <div style="position: relative; z-index: 10; padding: 1.25rem 1.75rem; overflow-y: auto; display: flex; flex-direction: column; gap: 1rem; background-color: #0c1220;">
                @if(!empty($popupImage))
                    <div style="position: relative; border-radius: 1.25rem; overflow: hidden; border: 1.5px solid rgba(255, 255, 255, 0.16); background-color: #060a14; display: flex; align-items: center; justify-content: center; box-shadow: 0 10px 30px rgba(0,0,0,0.5); cursor: zoom-in;"
                        @click="zoomImage = true; $nextTick(() => { if (window.lucide) window.lucide.createIcons(); });"
                        title="Klik untuk memperbesar gambar">
                        
                        <img src="{{ asset('storage/' . $popupImage) }}" 
                             alt="Pamflet Pengumuman" 
                             style="width: 100%; height: auto; max-height: clamp(420px, 64vh, 660px); object-fit: contain; display: block; transition: transform 0.25s ease;">
                        
                        <!-- Floating Zoom Hint Badge -->
                        <div style="position: absolute; bottom: 0.75rem; right: 0.75rem; padding: 0.4rem 0.85rem; border-radius: 0.75rem; background-color: rgba(12, 17, 29, 0.88); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.22); color: #ffffff; font-size: 0.75rem; font-weight: 800; display: inline-flex; align-items: center; gap: 0.4rem; box-shadow: 0 4px 15px rgba(0,0,0,0.5); pointer-events: none;">
                            <i data-lucide="zoom-in" style="width: 0.95rem; height: 0.95rem; color: #fbbf24;"></i>
                            <span>Klik Gambar untuk Perbesar (HD)</span>
                        </div>
                    </div>
                @endif

                @if(!empty($popupContent))
                    <div style="background-color: #131d33; border: 1px solid rgba(255, 255, 255, 0.12); border-radius: 1rem; padding: 1rem 1.4rem; color: #f1f5f9; font-size: clamp(0.9rem, 1.3vw, 1rem); line-height: 1.65; white-space: pre-line; box-shadow: 0 4px 20px rgba(0,0,0,0.25);">
                        {!! nl2br(e($popupContent)) !!}
                    </div>
                @endif
            </div>

            <!-- Modal Footer (Action Buttons & Fullscreen Shortcut) -->
            <div style="position: relative; z-index: 10; padding: 1rem 1.75rem; border-top: 1px solid rgba(255, 255, 255, 0.12); background-color: #090e1a; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.75rem;">
                <div>
                    @if(!empty($popupImage))
                        <button type="button" @click="zoomImage = true; $nextTick(() => { if (window.lucide) window.lucide.createIcons(); });" style="padding: 0.65rem 1.15rem; border-radius: 0.75rem; border: 1px solid rgba(255, 255, 255, 0.16); background-color: rgba(255, 255, 255, 0.06); color: #cbd5e1; font-size: 0.8rem; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 0.45rem; transition: all 0.2s;" onmouseover="this.style.backgroundColor='rgba(255,255,255,0.15)'; this.style.color='#ffffff';" onmouseout="this.style.backgroundColor='rgba(255,255,255,0.06)'; this.style.color='#cbd5e1';">
                            <i data-lucide="maximize-2" style="width: 0.95rem; height: 0.95rem; color: #a594fd;"></i>
                            <span>Buka Gambar Penuh</span>
                        </button>
                    @endif
                </div>

                <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
                    @if(!empty($popupBtnText) && !empty($popupBtnUrl))
                        <a href="{{ $popupBtnUrl }}" @click="closeModal()" target="{{ str_starts_with($popupBtnUrl, 'http') ? '_blank' : '_self' }}" style="padding: 0.75rem 1.6rem; border-radius: 0.875rem; background: linear-gradient(90deg, #7A5AF8 0%, #4E6EFF 100%); color: #ffffff; font-size: 0.875rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; display: inline-flex; align-items: center; gap: 0.5rem; text-decoration: none; box-shadow: 0 6px 20px rgba(122, 90, 248, 0.45); cursor: pointer; transition: all 0.2s;" onmouseover="this.style.transform='scale(1.03)';" onmouseout="this.style.transform='scale(1)';">
                            <span>{{ $popupBtnText }}</span>
                            <i data-lucide="arrow-right" style="width: 1.05rem; height: 1.05rem;"></i>
                        </a>
                    @endif
                </div>
            </div>

        </div>
    </div>

    <!-- Dedicated Fullscreen Zoom Lightbox Mode -->
    @if(!empty($popupImage))
    <div x-show="zoomImage"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        style="position: fixed !important; inset: 0 !important; top: 0 !important; left: 0 !important; width: 100vw !important; height: 100vh !important; background-color: rgba(3, 7, 18, 0.96) !important; backdrop-filter: blur(24px) !important; -webkit-backdrop-filter: blur(24px) !important; z-index: 10000000 !important; display: flex !important; flex-direction: column !important; align-items: center !important; justify-content: center !important; padding: 1rem !important;"
        @click.self="zoomImage = false"
        x-cloak>
        
        <!-- Lightbox Floating Top Bar -->
        <div style="position: absolute; top: 1.25rem; left: 1.5rem; right: 1.5rem; display: flex; align-items: center; justify-content: space-between; pointer-events: none; z-index: 10;">
            <div style="padding: 0.45rem 1rem; border-radius: 9999px; background-color: rgba(122, 90, 248, 0.35); border: 1px solid rgba(122, 90, 248, 0.6); color: #ffffff; font-size: 0.75rem; font-weight: 800; display: inline-flex; align-items: center; gap: 0.5rem; backdrop-filter: blur(10px); pointer-events: auto;">
                <i data-lucide="image" style="width: 0.9rem; height: 0.9rem; color: #a594fd;"></i>
                <span>Tampilan Resolusi Penuh HD</span>
            </div>
            
            <button type="button" @click="zoomImage = false" style="pointer-events: auto; padding: 0.6rem 1.2rem; border-radius: 0.875rem; background-color: rgba(255, 255, 255, 0.15); border: 1px solid rgba(255, 255, 255, 0.25); color: #ffffff; font-size: 0.8rem; font-weight: 800; display: inline-flex; align-items: center; gap: 0.4rem; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.backgroundColor='rgba(239, 68, 68, 0.8)';" onmouseout="this.style.backgroundColor='rgba(255, 255, 255, 0.15)';">
                <i data-lucide="x" style="width: 1rem; height: 1rem;"></i>
                <span>Tutup Pembesar</span>
            </button>
        </div>

        <!-- Fullscreen Image Container -->
        <div style="max-width: 96vw; max-height: 88vh; display: flex; align-items: center; justify-content: center; overflow: auto; padding: 0.5rem;" @click.self="zoomImage = false">
            <img src="{{ asset('storage/' . $popupImage) }}" alt="Pamflet Pengumuman HD" style="max-width: 100%; max-height: 88vh; object-fit: contain; border-radius: 0.75rem; box-shadow: 0 25px 60px rgba(0,0,0,0.9); cursor: zoom-out;" @click="zoomImage = false" title="Klik gambar untuk menutup pembesar">
        </div>
    </div>
    @endif
</div>
</div>
@endif
