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
<div x-data="{
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
                }, 250);
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

    <!-- Modal Backdrop (High z-index to overlay sticky navbars) -->
    <div x-show="showModal" 
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-[99999] bg-black/85 backdrop-blur-md flex items-center justify-center p-3 sm:p-4 overflow-y-auto"
        @click.self="closeModal()">
        
        <!-- Modal Card Dialog -->
        <div x-show="showModal"
            x-transition:enter="transition ease-out duration-300 transform"
            x-transition:enter-start="opacity-0 scale-95 translate-y-4"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200 transform"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 translate-y-4"
            class="relative w-full max-w-lg my-auto bg-[#0F172A] border border-white/[0.15] rounded-3xl shadow-2xl shadow-[#7A5AF8]/30 overflow-hidden flex flex-col text-slate-100 max-h-[90vh]">

            <!-- Glowing Ambient Top Aura -->
            <div class="absolute -top-16 -left-16 w-48 h-48 bg-[#7A5AF8]/35 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -top-16 -right-16 w-48 h-48 bg-[#FF58D5]/30 rounded-full blur-3xl pointer-events-none"></div>

            <!-- Modal Header -->
            <div class="relative z-10 px-5 sm:px-6 pt-5 pb-3.5 border-b border-white/[0.08] flex items-start justify-between gap-3 bg-white/[0.02]">
                <div class="space-y-1 pr-4">
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-amber-500/15 border border-amber-500/30 text-amber-300 text-[10px] font-extrabold uppercase tracking-wider">
                        <i data-lucide="bell-ring" class="w-3 h-3 text-amber-400"></i>
                        <span>{{ $popupSubtitle }}</span>
                    </div>
                    <h3 class="text-base sm:text-lg font-black text-white font-display tracking-tight leading-snug">
                        {{ $popupTitle }}
                    </h3>
                </div>
                <!-- Close Icon Button -->
                <button type="button" @click="closeModal()" class="w-8 h-8 rounded-xl bg-white/[0.06] hover:bg-white/[0.18] text-slate-400 hover:text-white flex items-center justify-center transition border border-white/[0.1] shrink-0 cursor-pointer" title="Tutup Pengumuman">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <!-- Modal Body (Scrollable) -->
            <div class="relative z-10 px-5 sm:px-6 py-4 overflow-y-auto space-y-4 scrollbar-thin scrollbar-thumb-slate-700">
                @if(!empty($popupImage))
                    <div class="rounded-2xl overflow-hidden border border-white/[0.1] bg-black/40 shadow-inner flex items-center justify-center max-h-[260px]">
                        <img src="{{ asset('storage/' . $popupImage) }}" alt="Pamflet Pengumuman" class="w-full h-auto max-h-[260px] object-contain hover:scale-105 transition-transform duration-300">
                    </div>
                @endif

                @if(!empty($popupContent))
                    <div class="text-xs sm:text-sm text-slate-300 leading-relaxed space-y-2 whitespace-pre-line bg-slate-900/70 p-4 rounded-2xl border border-white/[0.08]">
                        {!! nl2br(e($popupContent)) !!}
                    </div>
                @endif
            </div>

            <!-- Modal Footer / Action Buttons -->
            <div class="relative z-10 px-5 sm:px-6 py-4 border-t border-white/[0.08] bg-[#0C111D]/95 flex flex-col-reverse sm:flex-row sm:items-center justify-end gap-2.5">
                <button type="button" @click="closeModal()" class="w-full sm:w-auto px-4 py-2.5 rounded-xl border border-white/[0.12] bg-white/[0.04] hover:bg-white/[0.1] text-slate-300 hover:text-white text-xs font-bold transition text-center cursor-pointer">
                    {{ $popupSecBtnText }}
                </button>

                @if(!empty($popupBtnText) && !empty($popupBtnUrl))
                    <a href="{{ $popupBtnUrl }}" @click="closeModal()" target="{{ str_starts_with($popupBtnUrl, 'http') ? '_blank' : '_self' }}" class="w-full sm:w-auto px-5 py-2.5 rounded-xl gradient-btn text-white text-xs font-black uppercase tracking-wider flex items-center justify-center gap-2 shadow-lg shadow-[#7A5AF8]/25 hover:scale-[1.02] active:scale-[0.98] transition cursor-pointer">
                        <span>{{ $popupBtnText }}</span>
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </a>
                @endif
            </div>

        </div>
    </div>
</div>
@endif
