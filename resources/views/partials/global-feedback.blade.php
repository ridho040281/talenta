<!-- GLOBAL FEEDBACK ENGINE (Center Modal Notification & Modern Loading Overlay) -->

<!-- 1. GLOBAL LOADING OVERLAY (Modern Dual-Orbit Glowing Spinner) -->
<div id="globalAppLoading" 
     style="position: fixed !important; inset: 0 !important; top: 0 !important; left: 0 !important; width: 100vw !important; height: 100vh !important; background: rgba(5, 9, 20, 0.88) !important; backdrop-filter: blur(14px) !important; -webkit-backdrop-filter: blur(14px) !important; z-index: 99999999 !important; display: none; align-items: center !important; justify-content: center !important; padding: 1.25rem !important;"
     class="opacity-0 transition-all duration-300 select-none">
    <div class="relative w-full max-w-xs sm:max-w-sm bg-gradient-to-b from-[#161F30]/95 to-[#0C111D]/95 border border-white/[0.12] rounded-3xl p-6 sm:p-8 text-center shadow-2xl space-y-4 transform scale-95 transition-all duration-300 ring-1 ring-white/[0.08]">
        
        <!-- Animated Glowing Orbit Spinner -->
        <div class="relative mx-auto w-20 h-20 flex items-center justify-center">
            <!-- Outer Pulsing Glow -->
            <div class="absolute inset-0 rounded-full bg-[#7A5AF8]/20 animate-ping opacity-50"></div>
            
            <!-- Spinning Gradient Border Ring -->
            <div class="w-16 h-16 rounded-full border-3 border-transparent border-t-[#7A5AF8] border-r-[#4E6EFF] border-b-[#FF58D5] animate-spin"></div>
            
            <!-- Inner Glowing Core Icon -->
            <div class="absolute inset-0 flex items-center justify-center">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-[#7A5AF8] to-[#4E6EFF] text-white flex items-center justify-center shadow-lg shadow-[#7A5AF8]/40 animate-pulse">
                    <i data-lucide="sparkles" class="w-5 h-5 text-white" id="globalLoadingIcon"></i>
                </div>
            </div>
        </div>

        <!-- Dynamic Loading Title & Subtext -->
        <div class="space-y-1.5 pt-1">
            <h4 id="globalLoadingTitle" class="text-base sm:text-lg font-black text-white font-display tracking-tight">
                Sedang Memproses...
            </h4>
            <p id="globalLoadingMessage" class="text-xs text-slate-300 leading-relaxed font-medium">
                Mohon tunggu sebentar, sistem sedang memproses permintaan Anda.
            </p>
        </div>

        <!-- Animated Progress Dots -->
        <div class="flex items-center justify-center gap-1.5 pt-1">
            <span class="w-2 h-2 rounded-full bg-[#7A5AF8] animate-bounce" style="animation-delay: 0ms;"></span>
            <span class="w-2 h-2 rounded-full bg-[#4E6EFF] animate-bounce" style="animation-delay: 150ms;"></span>
            <span class="w-2 h-2 rounded-full bg-[#FF58D5] animate-bounce" style="animation-delay: 300ms;"></span>
        </div>

    </div>
</div>

<!-- 2. GLOBAL AESTHETIC CENTER MODAL NOTIFICATION -->
<div id="globalAppModal" 
     style="position: fixed !important; inset: 0 !important; top: 0 !important; left: 0 !important; width: 100vw !important; height: 100vh !important; background: rgba(5, 9, 20, 0.85) !important; backdrop-filter: blur(14px) !important; -webkit-backdrop-filter: blur(14px) !important; z-index: 9999999 !important; display: none; align-items: center !important; justify-content: center !important; padding: 1.25rem !important;"
     class="opacity-0 transition-all duration-300 select-none"
     role="dialog"
     aria-modal="true">
    
    <!-- Modal Dialog Card (High-Impact Opaque Design, Prevents Background Leak) -->
    <div id="globalAppModalCard" 
         style="position: relative !important; width: 100% !important; max-width: 28rem !important; background-color: #0f172a !important; border: 1.5px solid rgba(255, 255, 255, 0.18) !important; border-radius: 1.75rem !important; box-shadow: 0 30px 70px -10px rgba(0, 0, 0, 0.95), 0 0 60px rgba(122, 90, 248, 0.35) !important; padding: 2rem !important; text-align: center !important; z-index: 10000000 !important; margin: auto !important;"
         class="transform scale-95 transition-all duration-300 space-y-5">
        
        <!-- Glow Badge & Dynamic Action Icon -->
        <div class="relative mx-auto w-20 h-20 flex items-center justify-center">
            <div id="globalModalPing" class="absolute inset-0 rounded-full bg-emerald-500/20 animate-ping opacity-40"></div>
            <div id="globalModalBadge" class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-emerald-600 to-teal-400 flex items-center justify-center shadow-xl shadow-emerald-500/30 text-white transition-all duration-300">
                <i id="globalModalIcon" data-lucide="check" class="w-8 h-8"></i>
            </div>
        </div>

        <!-- Title & Description Message -->
        <div class="space-y-2">
            <h3 id="globalModalTitle" class="text-lg sm:text-xl font-black text-white font-display tracking-tight leading-snug">
                Berhasil Diproses
            </h3>
            <p id="globalModalMessage" class="text-xs sm:text-sm text-slate-300 leading-relaxed font-medium break-words">
                Aktivitas Anda telah berhasil diselesaikan oleh sistem.
            </p>
        </div>

        <!-- Action Button -->
        <div class="pt-2">
            <button type="button" 
                    id="globalModalBtn"
                    onclick="window.closeAppModal()" 
                    class="w-full gradient-btn py-3 px-6 rounded-2xl text-white font-black text-xs sm:text-sm tracking-wide shadow-lg shadow-[#7A5AF8]/30 hover:scale-[1.02] active:scale-[0.98] transition cursor-pointer flex items-center justify-center gap-2">
                <i data-lucide="check" class="w-4 h-4"></i>
                <span id="globalModalBtnText">Tutup & Lanjutkan</span>
            </button>
        </div>

    </div>
</div>

<!-- GLOBAL FEEDBACK JAVASCRIPT ENGINE -->
<script>
(function() {
    let modalCallback = null;

    // Type Configurations for Dynamic Colors & Icons
    const TYPE_THEMES = {
        'delete': {
            badge: 'bg-gradient-to-tr from-rose-600 to-pink-500 shadow-rose-500/40',
            ping: 'bg-rose-500/25',
            icon: 'trash-2',
            btnText: 'Tutup & Lanjutkan',
            defaultTitle: 'Data Berhasil Dihapus'
        },
        'update': {
            badge: 'bg-gradient-to-tr from-[#7A5AF8] to-[#4E6EFF] shadow-[#7A5AF8]/40',
            ping: 'bg-[#7A5AF8]/25',
            icon: 'check-circle-2',
            btnText: 'Tutup & Lanjutkan',
            defaultTitle: 'Perubahan Berhasil Disimpan'
        },
        'create': {
            badge: 'bg-gradient-to-tr from-emerald-600 to-teal-400 shadow-emerald-500/40',
            ping: 'bg-emerald-500/25',
            icon: 'check',
            btnText: 'Tutup & Lanjutkan',
            defaultTitle: 'Data Berhasil Ditambahkan'
        },
        'upload': {
            badge: 'bg-gradient-to-tr from-emerald-600 to-teal-400 shadow-emerald-500/40',
            ping: 'bg-emerald-500/25',
            icon: 'upload-cloud',
            btnText: 'Tutup & Lanjutkan',
            defaultTitle: 'Berkas Berhasil Diunggah'
        },
        'send': {
            badge: 'bg-gradient-to-tr from-cyan-600 to-blue-500 shadow-cyan-500/40',
            ping: 'bg-cyan-500/25',
            icon: 'send',
            btnText: 'Tutup & Lanjutkan',
            defaultTitle: 'Pesan Berhasil Dikirim'
        },
        'success': {
            badge: 'bg-gradient-to-tr from-emerald-600 to-teal-400 shadow-emerald-500/40',
            ping: 'bg-emerald-500/25',
            icon: 'check',
            btnText: 'Tutup & Lanjutkan',
            defaultTitle: 'Berhasil'
        },
        'info': {
            badge: 'bg-gradient-to-tr from-cyan-600 to-blue-500 shadow-cyan-500/40',
            ping: 'bg-cyan-500/25',
            icon: 'info',
            btnText: 'Tutup & Lanjutkan',
            defaultTitle: 'Informasi'
        },
        'warning': {
            badge: 'bg-gradient-to-tr from-amber-500 to-orange-500 shadow-amber-500/40',
            ping: 'bg-amber-500/25',
            icon: 'alert-triangle',
            btnText: 'Saya Mengerti',
            defaultTitle: 'Perhatian'
        },
        'error': {
            badge: 'bg-gradient-to-tr from-rose-600 to-red-500 shadow-rose-500/40',
            ping: 'bg-rose-500/25',
            icon: 'alert-circle',
            btnText: 'Tutup',
            defaultTitle: 'Terjadi Kesalahan'
        }
    };

    /**
     * Show Global Center Modal Notification
     * @param {Object} options - { type: 'delete'|'update'|'create'|'upload'|'send'|'success'|'warning'|'error', title: string, message: string, btnText: string, callback: function }
     */
    window.showAppModal = function(options = {}) {
        // Automatically close any active loading overlay
        window.hideAppLoading();

        const modal = document.getElementById('globalAppModal');
        const card = document.getElementById('globalAppModalCard');
        const titleEl = document.getElementById('globalModalTitle');
        const msgEl = document.getElementById('globalModalMessage');
        const badgeEl = document.getElementById('globalModalBadge');
        const pingEl = document.getElementById('globalModalPing');
        const iconEl = document.getElementById('globalModalIcon');
        const btnTextEl = document.getElementById('globalModalBtnText');

        if (!modal || !card) return;

        const type = options.type || 'success';
        const theme = TYPE_THEMES[type] || TYPE_THEMES['success'];

        modalCallback = typeof options.callback === 'function' ? options.callback : null;

        if (titleEl) titleEl.textContent = options.title || theme.defaultTitle;
        if (msgEl) msgEl.textContent = options.message || '';
        if (btnTextEl) btnTextEl.textContent = options.btnText || theme.btnText;

        // Apply dynamic theme classes
        if (badgeEl) {
            badgeEl.className = `w-16 h-16 rounded-2xl flex items-center justify-center shadow-xl text-white transition-all duration-300 ${theme.badge}`;
        }
        if (pingEl) {
            pingEl.className = `absolute inset-0 rounded-full animate-ping opacity-40 ${theme.ping}`;
        }
        if (iconEl) {
            iconEl.setAttribute('data-lucide', options.icon || theme.icon);
        }

        if (window.lucide && typeof window.lucide.createIcons === 'function') {
            window.lucide.createIcons();
        }

        // Show modal with smooth scale-up animation
        modal.style.display = 'flex';
        modal.classList.remove('hidden');
        requestAnimationFrame(() => {
            modal.classList.remove('opacity-0');
            card.classList.remove('scale-95');
            card.classList.add('scale-100');
        });

        window.dispatchEvent(new CustomEvent('app-modal-opened', { detail: { type, options } }));
    };

    /**
     * Close Global Center Modal Notification
     */
    window.closeAppModal = function() {
        const modal = document.getElementById('globalAppModal');
        const card = document.getElementById('globalAppModalCard');
        if (!modal || !card) return;

        card.classList.remove('scale-100');
        card.classList.add('scale-95');
        modal.classList.add('opacity-0');

        setTimeout(() => {
            modal.classList.add('hidden');
            modal.style.display = 'none';
            window.dispatchEvent(new CustomEvent('app-modal-closed'));
            if (modalCallback) {
                const cb = modalCallback;
                modalCallback = null;
                cb();
            }
        }, 250);
    };

    /**
     * Show Global Loading Indicator Overlay
     * @param {string} title - Main loading title
     * @param {string} message - Subtitle or processing description
     * @param {string} iconName - Lucide icon name (optional)
     */
    window.showAppLoading = function(title = 'Sedang Memproses...', message = 'Mohon tunggu sebentar, sistem sedang memproses data.', iconName = 'sparkles') {
        const loading = document.getElementById('globalAppLoading');
        if (!loading) return;

        const titleEl = document.getElementById('globalLoadingTitle');
        const msgEl = document.getElementById('globalLoadingMessage');
        const iconEl = document.getElementById('globalLoadingIcon');

        if (titleEl) titleEl.textContent = title;
        if (msgEl) msgEl.textContent = message;
        if (iconEl && iconName) {
            iconEl.setAttribute('data-lucide', iconName);
            if (window.lucide) window.lucide.createIcons();
        }

        const card = loading.querySelector('div');
        loading.style.display = 'flex';
        loading.classList.remove('hidden');
        requestAnimationFrame(() => {
            loading.classList.remove('opacity-0');
            if (card) {
                card.classList.remove('scale-95');
                card.classList.add('scale-100');
            }
        });
    };

    /**
     * Hide Global Loading Indicator Overlay
     */
    window.hideAppLoading = function() {
        const loading = document.getElementById('globalAppLoading');
        if (!loading || loading.classList.contains('hidden') || loading.style.display === 'none') return;

        const card = loading.querySelector('div');
        if (card) {
            card.classList.remove('scale-100');
            card.classList.add('scale-95');
        }
        loading.classList.add('opacity-0');

        setTimeout(() => {
            loading.classList.add('hidden');
            loading.style.display = 'none';
        }, 200);
    };

    // Keyboard Shortcuts: Enter or Escape to dismiss active modal
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' || e.key === 'Enter') {
            const modal = document.getElementById('globalAppModal');
            if (modal && !modal.classList.contains('hidden')) {
                window.closeAppModal();
            }
        }
    });

    // Close on backdrop click (outside modal card)
    document.addEventListener('click', function(e) {
        const modal = document.getElementById('globalAppModal');
        const card = document.getElementById('globalAppModalCard');
        if (modal && !modal.classList.contains('hidden') && e.target === modal) {
            window.closeAppModal();
        }
    });

    // Auto Form Submit Interceptor: show loading overlay on all normal form submissions
    document.addEventListener('submit', function(e) {
        const form = e.target;
        if (!form || form.hasAttribute('data-no-loading') || form.getAttribute('target') === '_blank' || e.defaultPrevented) {
            return;
        }

        // Determine context-aware text based on submit button
        const submitBtn = form.querySelector('button[type="submit"], input[type="submit"]');
        const btnText = submitBtn ? (submitBtn.innerText || submitBtn.value || '').toLowerCase() : '';

        let loadingTitle = 'Sedang Menyimpan Data...';
        let loadingMsg = 'Mohon tunggu sebentar, perubahan sedang diproses oleh server.';
        let icon = 'save';

        if (btnText.includes('daftar') || btnText.includes('pendaftaran') || (form.action && form.action.includes('daftar'))) {
            loadingTitle = 'Sedang Mengirim Pendaftaran...';
            loadingMsg = 'Mohon tunggu sebentar, data dan berkas pendaftaran Anda sedang diproses oleh server.';
            icon = 'file-text';
        } else if (btnText.includes('hapus') || btnText.includes('delete')) {
            loadingTitle = 'Sedang Menghapus Data...';
            loadingMsg = 'Mohon tunggu sebentar, data sedang dihapus dari sistem.';
            icon = 'trash-2';
        } else if (btnText.includes('unggah') || btnText.includes('upload')) {
            loadingTitle = 'Sedang Mengunggah Berkas...';
            loadingMsg = 'Mohon tunggu sebentar, berkas sedang ditransfer ke server.';
            icon = 'upload-cloud';
        } else if (btnText.includes('blast') || btnText.includes('broadcast') || btnText.includes('kirim pesan') || btnText.includes('pesan')) {
            loadingTitle = 'Sedang Mengirim Pesan...';
            loadingMsg = 'Mohon tunggu sebentar, pesan sedang dikirim ke tujuan.';
            icon = 'send';
        } else if (btnText.includes('verifikasi')) {
            loadingTitle = 'Sedang Memverifikasi...';
            loadingMsg = 'Mohon tunggu sebentar, status verifikasi sedang diperbarui.';
            icon = 'shield-check';
        } else if (btnText.includes('masuk') || btnText.includes('login') || (form.action && form.action.includes('login'))) {
            loadingTitle = 'Sedang Masuk...';
            loadingMsg = 'Memverifikasi akun dan hak akses Anda...';
            icon = 'log-in';
        }

        window.showAppLoading(loadingTitle, loadingMsg, icon);
    });

    // Handle Laravel Session Flash Messages on Page Load
    document.addEventListener('DOMContentLoaded', function() {
        @if(session('modal_success_title') || session('success') || session('status'))
            @php
                $title = session('modal_success_title', 'Berhasil Diproses');
                $msg = session('modal_success_message', session('success') ?? session('status', 'Aktivitas berhasil diselesaikan.'));
                $tLower = strtolower($title . ' ' . $msg);
                $isWelcomeMessage = str_contains($tLower, 'selamat datang');
                $type = 'success';
                if (str_contains($tLower, 'hapus') || str_contains($tLower, 'delete') || str_contains($tLower, 'dihapus')) {
                    $type = 'delete';
                } elseif (str_contains($tLower, 'simpan') || str_contains($tLower, 'perbarui') || str_contains($tLower, 'update') || str_contains($tLower, 'disimpan')) {
                    $type = 'update';
                } elseif (str_contains($tLower, 'unggah') || str_contains($tLower, 'upload') || str_contains($tLower, 'tambah') || str_contains($tLower, 'ditambahkan')) {
                    $type = 'upload';
                } elseif (str_contains($tLower, 'kirim') || str_contains($tLower, 'blast') || str_contains($tLower, 'broadcast')) {
                    $type = 'send';
                }
            @endphp
            @if(!$isWelcomeMessage)
            window.showAppModal({
                type: '{{ $type }}',
                title: "{!! addslashes($title) !!}",
                message: "{!! addslashes($msg) !!}"
            });
            @endif
        @elseif(session('error'))
            window.showAppModal({
                type: 'error',
                title: 'Terjadi Kesalahan',
                message: "{!! addslashes(session('error')) !!}"
            });
        @elseif(session('warning'))
            window.showAppModal({
                type: 'warning',
                title: 'Perhatian',
                message: "{!! addslashes(session('warning')) !!}"
            });
        @elseif(session('info'))
            window.showAppModal({
                type: 'info',
                title: 'Informasi',
                message: "{!! addslashes(session('info')) !!}"
            });
        @elseif(isset($errors) && $errors->any())
            window.showAppModal({
                type: 'error',
                title: 'Validasi Tidak Lengkap',
                message: "{!! addslashes(implode(' ', $errors->all())) !!}"
            });
        @endif
    });
})();
</script>