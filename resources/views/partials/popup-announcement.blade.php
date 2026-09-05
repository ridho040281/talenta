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
        version: '{{ $popupVersion }}',
        storageKey: 'talenta_popup_seen_{{ $popupVersion }}',
        init() {
            if (!localStorage.getItem(this.storageKey)) {
                setTimeout(() => {
                    this.showModal = true;
                    this.$nextTick(() => {
                        if (window.lucide) {
                            window.lucide.createIcons();
                        }
                    });
                }, 200);
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
    @keydown.escape.window="closeModal()">

    <!-- Fullscreen Backdrop (Pure Solid Overlay with High z-index) -->
    <div x-show="showModal"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        style="position: fixed !important; inset: 0 !important; top: 0 !important; left: 0 !important; width: 100vw !important; height: 100vh !important; background-color: rgba(6, 10, 20, 0.90) !important; backdrop-filter: blur(14px) !important; -webkit-backdrop-filter: blur(14px) !important; z-index: 999999 !important; display: flex !important; align-items: center !important; justify-content: center !important; padding: 1.25rem !important; overflow-y: auto !important;"
        @click.self="closeModal()">
        
        <!-- Modal Card Container (Larger, High-Impact Width, Solid Opaque) -->
        <div x-show="showModal"
            x-transition:enter="transition ease-out duration-300 transform"
            x-transition:enter-start="opacity-0 scale-95 translate-y-6"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200 transform"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 translate-y-6"
            style="position: relative !important; width: 100% !important; max-width: 48rem !important; background-color: #0f172a !important; border: 1.5px solid rgba(255, 255, 255, 0.2) !important; border-radius: 1.75rem !important; box-shadow: 0 30px 70px -10px rgba(0, 0, 0, 0.95), 0 0 60px rgba(122, 90, 248, 0.4) !important; overflow: hidden !important; display: flex !important; flex-direction: column !important; color: #f8fafc !important; max-height: 90vh !important; z-index: 1000000 !important; margin: auto !important;">

            <!-- Glowing Ambient Top Aura -->
            <div style="position: absolute; top: -5rem; left: -5rem; width: 16rem; height: 16rem; background: rgba(122, 90, 248, 0.45); border-radius: 9999px; filter: blur(70px); pointer-events: none;"></div>
            <div style="position: absolute; top: -5rem; right: -5rem; width: 16rem; height: 16rem; background: rgba(255, 88, 213, 0.4); border-radius: 9999px; filter: blur(70px); pointer-events: none;"></div>

            <!-- Modal Header (Enlarged Title & Subtitle) -->
            <div style="position: relative; z-index: 10; padding: 1.5rem 2rem; border-bottom: 1px solid rgba(255, 255, 255, 0.12); background-color: #131d33; display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem;">
                <div style="display: flex; flex-direction: column; gap: 0.5rem; padding-right: 1rem;">
                    <div style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.35rem 0.85rem; border-radius: 9999px; background-color: rgba(245, 158, 11, 0.18); border: 1px solid rgba(245, 158, 11, 0.45); color: #fcd34d; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; width: fit-content;">
                        <i data-lucide="bell-ring" style="width: 0.95rem; height: 0.95rem; color: #fbbf24;"></i>
                        <span>{{ $popupSubtitle }}</span>
                    </div>
                    <h2 style="font-size: clamp(1.25rem, 2.5vw, 1.65rem); font-weight: 900; color: #ffffff; line-height: 1.3; margin: 0; font-family: 'Space Grotesk', 'Plus Jakarta Sans', sans-serif; letter-spacing: -0.02em;">
                        {{ $popupTitle }}
                    </h2>
                </div>
                <!-- Close Button -->
                <button type="button" @click="closeModal()" style="width: 2.5rem; height: 2.5rem; border-radius: 0.875rem; background-color: rgba(255, 255, 255, 0.08); border: 1px solid rgba(255, 255, 255, 0.15); color: #cbd5e1; display: flex; align-items: center; justify-content: center; cursor: pointer; flex-shrink: 0; transition: all 0.2s;" onmouseover="this.style.backgroundColor='rgba(255,255,255,0.25)'; this.style.color='#ffffff';" onmouseout="this.style.backgroundColor='rgba(255,255,255,0.08)'; this.style.color='#cbd5e1';" title="Tutup Pengumuman">
                    <i data-lucide="x" style="width: 1.25rem; height: 1.25rem;"></i>
                </button>
            </div>

            <!-- Modal Body (Larger Font, Enhanced Readability & Scrollable) -->
            <div style="position: relative; z-index: 10; padding: 1.75rem 2rem; overflow-y: auto; display: flex; flex-direction: column; gap: 1.25rem; background-color: #0f172a;">
                @if(!empty($popupImage))
                    <div style="border-radius: 1.25rem; overflow: hidden; border: 1.5px solid rgba(255, 255, 255, 0.15); background-color: #060a14; display: flex; align-items: center; justify-content: center; max-height: 380px; box-shadow: inset 0 2px 10px rgba(0,0,0,0.5);">
                        <img src="{{ asset('storage/' . $popupImage) }}" alt="Pamflet Pengumuman" style="width: 100%; height: auto; max-height: 380px; object-fit: contain;">
                    </div>
                @endif

                @if(!empty($popupContent))
                    <div style="background-color: #152238; border: 1px solid rgba(255, 255, 255, 0.12); border-radius: 1.25rem; padding: 1.35rem 1.6rem; color: #f1f5f9; font-size: clamp(0.95rem, 1.5vw, 1.08rem); line-height: 1.8; white-space: pre-line; box-shadow: 0 4px 20px rgba(0,0,0,0.2);">
                        {!! nl2br(e($popupContent)) !!}
                    </div>
                @endif
            </div>

            <!-- Modal Footer (Larger, Prominent Action Buttons) -->
            <div style="position: relative; z-index: 10; padding: 1.25rem 2rem; border-top: 1px solid rgba(255, 255, 255, 0.12); background-color: #0c1220; display: flex; flex-wrap: wrap; align-items: center; justify-content: flex-end; gap: 0.85rem;">
                <button type="button" @click="closeModal()" style="padding: 0.85rem 1.6rem; border-radius: 0.875rem; border: 1px solid rgba(255, 255, 255, 0.18); background-color: rgba(255, 255, 255, 0.08); color: #e2e8f0; font-size: 0.875rem; font-weight: 700; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.backgroundColor='rgba(255,255,255,0.18)'; this.style.color='#ffffff';" onmouseout="this.style.backgroundColor='rgba(255,255,255,0.08)'; this.style.color='#e2e8f0';">
                    {{ $popupSecBtnText }}
                </button>

                @if(!empty($popupBtnText) && !empty($popupBtnUrl))
                    <a href="{{ $popupBtnUrl }}" @click="closeModal()" target="{{ str_starts_with($popupBtnUrl, 'http') ? '_blank' : '_self' }}" style="padding: 0.85rem 1.85rem; border-radius: 0.875rem; background: linear-gradient(90deg, #7A5AF8 0%, #4E6EFF 100%); color: #ffffff; font-size: 0.875rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; display: inline-flex; align-items: center; gap: 0.6rem; text-decoration: none; box-shadow: 0 6px 20px rgba(122, 90, 248, 0.45); cursor: pointer; transition: all 0.2s;" onmouseover="this.style.transform='scale(1.03)';" onmouseout="this.style.transform='scale(1)';">
                        <span>{{ $popupBtnText }}</span>
                        <i data-lucide="arrow-right" style="width: 1.1rem; height: 1.1rem;"></i>
                    </a>
                @endif
            </div>

        </div>
    </div>
</div>
@endif
