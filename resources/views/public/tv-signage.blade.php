<!DOCTYPE html>
<html lang="id" class="dark h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $settings['tv_signage_header_title'] ?? 'Layar Iklan & TV Signage' }} | TALENTA MTsN 1 Blitar</title>
    
    <!-- Favicon -->
    @if(!empty($settings['favicon']))
        <link rel="icon" type="image/png" href="{{ asset('storage/' . $settings['favicon']) }}">
    @else
        <link rel="icon" href="{{ asset('favicon.ico') }}">
    @endif

    <!-- Fonts -->
    <link rel="stylesheet" href="{{ asset('vendor/fonts/fonts.css') }}">
    
    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Lucide Icons & Alpine.js -->
    <script defer src="{{ asset('vendor/lucide/lucide.min.js') }}"></script>
    <script defer src="{{ asset('vendor/alpine/alpine.min.js') }}"></script>

    <style>
        [x-cloak] { display: none !important; }

        body {
            background-color: #060913;
            overflow: hidden;
            user-select: none;
            -webkit-user-select: none;
        }

        /* Seamless Marquee Animation */
        @keyframes marquee {
            0% { transform: translateX(0%); }
            100% { transform: translateX(-50%); }
        }

        @keyframes marquee-fast {
            0% { transform: translateX(0%); }
            100% { transform: translateX(-50%); }
        }

        .animate-marquee {
            display: flex;
            width: max-content;
            animation: marquee 35s linear infinite;
        }

        .animate-marquee:hover {
            animation-play-state: paused;
        }

        .animate-ticker {
            display: flex;
            width: max-content;
            animation: marquee-fast 40s linear infinite;
        }

        /* Subtle Ambient Glow */
        .ambient-glow {
            position: absolute;
            border-radius: 50%;
            filter: blur(120px);
            pointer-events: none;
            z-index: 0;
        }

        /* Smooth Slide Transitions */
        .slide-fade-enter {
            opacity: 0;
            transform: scale(0.98);
        }
        .slide-fade-enter-active {
            transition: opacity 0.7s cubic-bezier(0.4, 0, 0.2, 1), transform 0.7s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .slide-fade-leave-active {
            transition: opacity 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            position: absolute;
            inset: 0;
        }
        .slide-fade-leave-to {
            opacity: 0;
        }
    </style>
</head>
<body class="h-full w-full flex flex-col justify-between text-slate-100 font-sans relative"
      x-data="tvSignagePlayer()"
      x-init="initPlayer()"
      @keydown.window="handleKeyboard($event)">

    <!-- Background Ambient Mesh Lighting -->
    <div class="ambient-glow w-[500px] h-[500px] -top-32 -left-32 bg-[#7A5AF8]/20"></div>
    <div class="ambient-glow w-[600px] h-[600px] -bottom-40 -right-40 bg-[#FF58D5]/15"></div>
    <div class="ambient-glow w-[400px] h-[400px] top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 bg-cyan-500/10"></div>

    <!-- ========================================================================= -->
    <!-- 1. TOP HEADER BAR (Header Display TV)                                      -->
    <!-- ========================================================================= -->
    <header x-show="showHeaderFooter" x-transition.opacity.duration.300ms class="relative z-20 h-16 sm:h-20 px-6 sm:px-10 bg-[#090E1A]/85 backdrop-blur-xl border-b border-white/[0.08] flex items-center justify-between shadow-2xl shrink-0">
        
        <!-- Left: Logo & Event Title -->
        <div class="flex items-center gap-3.5 sm:gap-5 min-w-0">
            @if(!empty($settings['app_logo']) || !empty($settings['event_logo']))
                @php
                    $logoToShow = !empty($settings['event_logo']) ? $settings['event_logo'] : $settings['app_logo'];
                    $cleanLogo = ltrim(str_replace(['public/', 'storage/'], '', $logoToShow), '/');
                @endphp
                <img src="{{ asset('storage/' . $cleanLogo) }}" 
                     alt="Event Logo" 
                     class="h-10 sm:h-12 w-auto max-w-[120px] object-contain drop-shadow-md shrink-0">
            @else
                <div class="w-10 sm:w-12 h-10 sm:h-12 rounded-2xl bg-gradient-to-tr from-[#7A5AF8] to-[#4E6EFF] flex items-center justify-center text-white font-black shadow-lg shadow-[#7A5AF8]/30 shrink-0">
                    <i data-lucide="sparkles" class="w-5 sm:w-6 h-5 sm:h-6"></i>
                </div>
            @endif

            <div class="min-w-0">
                <div class="flex items-center gap-2">
                    <h1 class="text-base sm:text-xl lg:text-2xl font-black tracking-tight text-white font-display truncate leading-tight">
                        {{ $settings['tv_signage_header_title'] ?? 'TALENTA 2026' }}
                    </h1>
                    <span class="hidden md:inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-rose-500/20 text-rose-300 border border-rose-500/30">
                        <span class="w-1.5 h-1.5 rounded-full bg-rose-400 animate-pulse"></span>
                        <span>LIVE TV</span>
                    </span>
                </div>
                <p class="text-[11px] sm:text-xs text-[#84D0FF] font-bold truncate mt-0.5">
                    {{ $settings['tv_signage_header_subtitle'] ?? ($settings['institution_name'] ?? 'MTs Negeri 1 Blitar') }}
                </p>
            </div>
        </div>

        <!-- Right: Digital Clock & Date Live + Fullscreen Toggle -->
        <div class="flex items-center gap-4 sm:gap-6 shrink-0">
            
            @if(($settings['tv_signage_show_clock'] ?? '1') == '1')
            <div class="text-right flex flex-col items-end">
                <div class="text-lg sm:text-2xl lg:text-3xl font-black font-mono tracking-tight text-white flex items-center gap-1 leading-none">
                    <span x-text="clockTime">00:00:00</span>
                    <span class="text-[10px] sm:text-xs text-[#7A5AF8] font-bold font-sans">WIB</span>
                </div>
                <span class="text-[10px] sm:text-xs text-slate-400 font-semibold mt-1" x-text="clockDate">
                    Kamis, 8 Oktober 2026
                </span>
            </div>
            @endif

            <!-- Remote Helper / Fullscreen Button -->
            <button @click="toggleFullscreen()" class="p-2.5 sm:p-3 rounded-2xl bg-white/[0.06] hover:bg-white/[0.12] text-slate-300 hover:text-white border border-white/[0.1] transition cursor-pointer shadow-sm" title="Toggle Fullscreen (Tekan tombol 'F')">
                <i data-lucide="maximize" class="w-4 sm:w-5 h-4 sm:h-5"></i>
            </button>
        </div>

    </header>

    <!-- ========================================================================= -->
    <!-- 2. MAIN STAGE SLIDESHOW (Area Utama Layar Iklan & Poster)                  -->
    <!-- ========================================================================= -->
    <main class="flex-1 relative overflow-hidden flex items-center justify-center p-3 sm:p-6 z-10">
        
        <!-- SLIDE CONTAINER -->
        <div class="relative w-full h-full rounded-3xl overflow-hidden shadow-[0_20px_50px_rgba(0,0,0,0.8)] border border-white/[0.12] bg-[#070B14] flex items-center justify-center">
            
            <!-- Dynamic Active Slide Display -->
            <template x-if="slides.length > 0">
                <div class="relative w-full h-full flex items-center justify-center">
                    
                    <template x-for="(slide, index) in slides" :key="slide.id || index">
                        <div x-show="currentIndex === index"
                             x-transition:enter="transition-all duration-700 ease-out"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition-all duration-500 ease-in absolute inset-0"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-to="opacity-0 scale-105"
                             class="absolute inset-0 w-full h-full flex items-center justify-center overflow-hidden">
                            
                            <!-- Slide Type: IMAGE -->
                            <template x-if="slide.type === 'image' && slide.media_url">
                                <div class="relative w-full h-full flex items-center justify-center bg-black/90">
                                    <!-- Blurred Ambient Background (Fills screen nicely for non-16:9 images) -->
                                    <div class="absolute inset-0 bg-cover bg-center blur-2xl opacity-40 scale-110" 
                                         :style="'background-image: url(' + slide.media_url + ')'"></div>
                                    
                                    <!-- Main Sharp High-Res Image -->
                                    <img :src="slide.media_url" 
                                         :alt="slide.title" 
                                         class="relative z-10 max-h-full max-w-full w-auto h-auto object-contain drop-shadow-2xl">
                                </div>
                            </template>

                            <!-- Slide Type: VIDEO -->
                            <template x-if="slide.type === 'video' && slide.media_url">
                                <div class="relative w-full h-full bg-black flex items-center justify-center">
                                    <video :id="'video-slide-' + index"
                                           :src="slide.media_url" 
                                           class="w-full h-full object-contain"
                                           autoplay
                                           :muted="isMuted"
                                           playsinline
                                           @ended="nextSlide()"></video>
                                </div>
                            </template>

                            <!-- Slide Floating Caption / Badge Overlay (Bottom Left of Slide) -->
                            @if(!empty($slide['notes']) || true)
                            <div x-show="slide.title" class="absolute bottom-6 left-6 z-20 max-w-xl p-3.5 sm:p-4 rounded-2xl bg-[#090E1A]/85 backdrop-blur-xl border border-white/[0.15] shadow-2xl pointer-events-none">
                                <span class="text-[10px] font-mono font-black uppercase tracking-widest text-[#FF58D5] block">
                                    TALENTA TV SPONSOR & EVENT
                                </span>
                                <h3 class="text-sm sm:text-base font-black text-white leading-snug" x-text="slide.title"></h3>
                                <p x-show="slide.notes" class="text-xs text-slate-300 line-clamp-1 mt-0.5" x-text="slide.notes"></p>
                            </div>
                            @endif

                        </div>
                    </template>

                </div>
            </template>

            <!-- FALLBACK DEFAULT SLIDESHOW (When no custom slides uploaded yet) -->
            <template x-if="slides.length === 0">
                <div class="relative w-full h-full flex flex-col items-center justify-center text-center p-8 sm:p-12 space-y-8 bg-gradient-to-br from-[#0D1527] via-[#090E1A] to-[#120B20]">
                    
                    <!-- Ambient Glow Elements -->
                    <div class="w-96 h-96 rounded-full bg-gradient-to-tr from-[#7A5AF8]/30 via-[#4E6EFF]/30 to-[#FF58D5]/30 blur-3xl absolute pointer-events-none"></div>

                    <!-- Center Event Hero Card -->
                    <div class="relative z-10 max-w-3xl mx-auto space-y-6">
                        
                        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/[0.08] border border-white/[0.15] text-[#84D0FF] text-xs sm:text-sm font-bold shadow-lg">
                            <i data-lucide="sparkles" class="w-4 h-4 text-amber-400"></i>
                            <span>{{ $settings['tv_signage_header_subtitle'] ?? 'PLATFORM MANAJEMEN & LIVE EVENT PERLOMBAAN' }}</span>
                        </div>

                        <h2 class="text-3xl sm:text-5xl lg:text-6xl font-black text-white font-display tracking-tight leading-tight">
                            {{ $settings['tv_signage_header_title'] ?? 'TALENTA 2026' }}
                        </h2>

                        <p class="text-sm sm:text-lg text-slate-300 max-w-2xl mx-auto leading-relaxed">
                            Terbuka untuk SD/MI & SMP/MTs sederajat dalam berbagai cabang perlombaan bergengsi.
                        </p>

                        <!-- Highlight Grid Sponsors Showcase (24 Logo) -->
                        @if(count($sponsorLogos) > 0)
                        <div class="pt-6 space-y-3">
                            <span class="text-xs font-black uppercase tracking-widest text-slate-400 block font-mono">
                                ★ DIDUKUNG OLEH SPONSOR RESMI ★
                            </span>
                            <div class="flex flex-wrap items-center justify-center gap-3 sm:gap-5 max-w-4xl mx-auto">
                                @foreach(array_slice($sponsorLogos, 0, 12) as $logo)
                                    @php
                                        $cleanLogo = ltrim(str_replace(['public/', 'storage/'], '', $logo), '/');
                                    @endphp
                                    <div class="p-2 sm:p-3 rounded-xl bg-white/[0.05] border border-white/[0.1] shadow-md flex items-center justify-center">
                                        <img src="{{ asset('storage/' . $cleanLogo) }}" class="h-8 sm:h-12 w-auto max-w-[120px] object-contain drop-shadow">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                    </div>

                </div>
            </template>

            <!-- Bottom Slide Progress Bar (Active Countdown) -->
            <div class="absolute bottom-0 left-0 right-0 h-1.5 bg-white/[0.08] z-30 overflow-hidden">
                <div class="h-full bg-gradient-to-r from-[#7A5AF8] via-[#4E6EFF] to-[#FF58D5] transition-all ease-linear"
                     :style="'width: ' + progressPercent + '%; transition-duration: 100ms;'"></div>
            </div>

            <!-- Slide Index Counter Badge (Bottom Right) -->
            <div class="absolute bottom-4 right-4 z-30 px-3 py-1.5 rounded-xl bg-[#090E1A]/90 backdrop-blur-md border border-white/[0.12] text-xs font-mono font-bold text-white shadow-xl flex items-center gap-2">
                <span x-text="isPaused ? '⏸ PAUSED' : '▶ PLAYING'" :class="isPaused ? 'text-amber-400' : 'text-emerald-400'"></span>
                <span class="text-slate-500">•</span>
                <span>
                    <span class="text-white" x-text="String(currentIndex + 1).padStart(2, '0')">01</span>
                    <span class="text-slate-500">/</span>
                    <span class="text-slate-400" x-text="String(Math.max(1, slides.length)).padStart(2, '0')">01</span>
                </span>
            </div>

        </div>

    </main>

    <!-- ========================================================================= -->
    <!-- 3. BOTTOM SIGNAGE FOOTER (24 Sponsor Marquee + Running Text Ticker)        -->
    <!-- ========================================================================= -->
    <footer x-show="showHeaderFooter" x-transition.opacity.duration.300ms class="relative z-20 flex flex-col bg-[#080C17]/95 backdrop-blur-2xl border-t border-white/[0.08] shadow-2xl shrink-0">
        
        <!-- ROW 1: SPONSOR 24 LOGO MARQUEE (Horizontal Auto-Slide) -->
        @if(($settings['tv_signage_show_sponsor_marquee'] ?? '1') == '1' && count($sponsorLogos) > 0)
        <div class="h-14 sm:h-16 border-b border-white/[0.06] flex items-center overflow-hidden relative">
            
            <!-- Supported By Label Badge -->
            <div class="h-full px-4 sm:px-6 bg-[#0E1528] border-r border-white/[0.08] flex items-center gap-2 z-20 shrink-0 shadow-lg">
                <i data-lucide="award" class="w-4 h-4 text-amber-400"></i>
                <span class="text-[11px] sm:text-xs font-black uppercase tracking-wider text-slate-300 whitespace-nowrap font-display">
                    SUPPORTED BY :
                </span>
            </div>

            <!-- Continuous Infinite Scrolling Logo Track -->
            <div class="flex-1 overflow-hidden relative flex items-center">
                <div class="animate-marquee flex items-center gap-6 sm:gap-10 px-4">
                    
                    <!-- Repeat 1: 24 Logos -->
                    @foreach($sponsorLogos as $logo)
                        @php
                            $cleanLogo = ltrim(str_replace(['public/', 'storage/'], '', $logo), '/');
                        @endphp
                        <div class="h-9 sm:h-11 px-3 py-1 rounded-xl bg-white/[0.04] border border-white/[0.08] flex items-center justify-center shrink-0">
                            <img src="{{ asset('storage/' . $cleanLogo) }}" alt="Sponsor" class="h-full w-auto max-w-[120px] object-contain drop-shadow-sm">
                        </div>
                    @endforeach

                    <!-- Repeat 2 (For infinite loop smoothness) -->
                    @foreach($sponsorLogos as $logo)
                        @php
                            $cleanLogo = ltrim(str_replace(['public/', 'storage/'], '', $logo), '/');
                        @endphp
                        <div class="h-9 sm:h-11 px-3 py-1 rounded-xl bg-white/[0.04] border border-white/[0.08] flex items-center justify-center shrink-0">
                            <img src="{{ asset('storage/' . $cleanLogo) }}" alt="Sponsor" class="h-full w-auto max-w-[120px] object-contain drop-shadow-sm">
                        </div>
                    @endforeach

                </div>
            </div>

        </div>
        @endif

        <!-- ROW 2: RUNNING TEXT (NEWS TICKER BERJALAN) -->
        @if(!empty($settings['tv_signage_running_text']))
        <div class="h-10 sm:h-11 flex items-center overflow-hidden relative bg-[#04060C]">
            
            <!-- Broadcast Icon Badge -->
            <div class="h-full px-3.5 sm:px-5 bg-gradient-to-r from-[#7A5AF8] to-[#4E6EFF] text-white flex items-center gap-2 z-20 shrink-0 font-black shadow-lg">
                <i data-lucide="radio" class="w-3.5 sm:w-4 h-3.5 sm:h-4 animate-pulse"></i>
                <span class="text-[10px] sm:text-xs font-black uppercase tracking-widest font-mono">
                    INFO & PENGUMUMAN
                </span>
            </div>

            <!-- Ticker Scrolling Stream -->
            <div class="flex-1 overflow-hidden relative flex items-center">
                <div class="animate-ticker flex items-center gap-12 px-6">
                    <span class="text-xs sm:text-sm font-bold text-slate-200 tracking-wide whitespace-nowrap">
                        {{ $settings['tv_signage_running_text'] }}
                    </span>
                    <span class="text-xs sm:text-sm font-bold text-slate-200 tracking-wide whitespace-nowrap">
                        ★ {{ $settings['tv_signage_running_text'] }} ★
                    </span>
                </div>
            </div>

        </div>
        @endif

    </footer>

    <!-- ========================================================================= -->
    <!-- JAVASCRIPT PLAYER LOGIC & REAL-TIME TV ENGINE                             -->
    <!-- ========================================================================= -->
    <script>
        function tvSignagePlayer() {
            return {
                slides: @json($activeSlides),
                currentIndex: 0,
                isPaused: false,
                isMuted: true,
                showHeaderFooter: true,
                progressPercent: 0,
                currentDuration: 10,
                progressInterval: null,
                pollInterval: null,
                clockInterval: null,
                clockTime: '00:00:00',
                clockDate: '',
                currentVersion: '{{ $settings['tv_signage_version'] ?? 'v1' }}',

                initPlayer() {
                    // Pre-process media URLs for slides
                    this.slides = this.slides.map(s => {
                        let url = s.video_url || null;
                        if (s.media_path) {
                            let clean = s.media_path.replace(/^(public\/|storage\/)/, '');
                            url = '{{ asset('storage') }}/' + clean;
                        }
                        return { ...s, media_url: url };
                    });

                    this.updateClock();
                    this.clockInterval = setInterval(() => { this.updateClock(); }, 1000);

                    // Start Slideshow Loop
                    this.startSlideTimer();

                    // Start Background API Sync (Check for live updates from admin every 20s)
                    this.pollInterval = setInterval(() => { this.checkLiveState(); }, 20000);

                    this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
                },

                startSlideTimer() {
                    if (this.slides.length === 0) return;

                    const cur = this.slides[this.currentIndex];
                    this.currentDuration = (cur && cur.duration) ? parseInt(cur.duration) : 10;
                    this.progressPercent = 0;

                    if (this.progressInterval) clearInterval(this.progressInterval);

                    const stepMs = 100;
                    const totalSteps = (this.currentDuration * 1000) / stepMs;
                    let currentStep = 0;

                    this.progressInterval = setInterval(() => {
                        if (!this.isPaused) {
                            currentStep++;
                            this.progressPercent = Math.min(100, (currentStep / totalSteps) * 100);

                            if (currentStep >= totalSteps) {
                                this.nextSlide();
                            }
                        }
                    }, stepMs);
                },

                nextSlide() {
                    if (this.slides.length === 0) return;
                    this.currentIndex = (this.currentIndex + 1) % this.slides.length;
                    this.startSlideTimer();
                    this.$nextTick(() => { this.playCurrentVideo(); });
                },

                prevSlide() {
                    if (this.slides.length === 0) return;
                    this.currentIndex = (this.currentIndex - 1 + this.slides.length) % this.slides.length;
                    this.startSlideTimer();
                    this.$nextTick(() => { this.playCurrentVideo(); });
                },

                playCurrentVideo() {
                    const videoEl = document.getElementById('video-slide-' + this.currentIndex);
                    if (videoEl) {
                        videoEl.currentTime = 0;
                        videoEl.play().catch(e => console.log('Auto-play prevented:', e));
                    }
                },

                toggleFullscreen() {
                    if (!document.fullscreenElement) {
                        document.documentElement.requestFullscreen().catch(err => {
                            console.error('Fullscreen error:', err);
                        });
                    } else {
                        if (document.exitFullscreen) {
                            document.exitFullscreen();
                        }
                    }
                },

                handleKeyboard(e) {
                    if (e.key === 'f' || e.key === 'F') {
                        this.toggleFullscreen();
                    } else if (e.key === ' ' || e.code === 'Space') {
                        e.preventDefault();
                        this.isPaused = !this.isPaused;
                    } else if (e.key === 'ArrowRight' || e.key === 'PageDown') {
                        this.nextSlide();
                    } else if (e.key === 'ArrowLeft' || e.key === 'PageUp') {
                        this.prevSlide();
                    } else if (e.key === 'm' || e.key === 'M') {
                        this.isMuted = !this.isMuted;
                    } else if (e.key === 'h' || e.key === 'H') {
                        this.showHeaderFooter = !this.showHeaderFooter;
                    }
                },

                updateClock() {
                    const now = new Date();
                    const hours = String(now.getHours()).padStart(2, '0');
                    const minutes = String(now.getMinutes()).padStart(2, '0');
                    const seconds = String(now.getSeconds()).padStart(2, '0');
                    this.clockTime = `${hours}:${minutes}:${seconds}`;

                    const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                    const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                    
                    const dayName = days[now.getDay()];
                    const dayNum = now.getDate();
                    const monthName = months[now.getMonth()];
                    const year = now.getFullYear();

                    this.clockDate = `${dayName}, ${dayNum} ${monthName} ${year}`;
                },

                checkLiveState() {
                    fetch('{{ route('api.tv.signage.state') }}')
                        .then(res => res.json())
                        .then(data => {
                            if (data.version && data.version !== this.currentVersion) {
                                console.log('New TV Signage version detected, syncing...');
                                this.currentVersion = data.version;
                                
                                // Map slides with absolute storage URLs
                                this.slides = (data.slides || []).map(s => {
                                    let url = s.video_url || null;
                                    if (s.media_path) {
                                        let clean = s.media_path.replace(/^(public\/|storage\/)/, '');
                                        url = '{{ asset('storage') }}/' + clean;
                                    }
                                    return { ...s, media_url: url };
                                });

                                if (this.currentIndex >= this.slides.length) {
                                    this.currentIndex = 0;
                                }
                                this.startSlideTimer();
                            }
                        })
                        .catch(err => console.error('Signage sync polling error:', err));
                }
            };
        }
    </script>
</body>
</html>
