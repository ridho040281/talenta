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
            background-color: #050811;
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
            filter: blur(140px);
            pointer-events: none;
            z-index: 0;
        }
    </style>
</head>
<body class="h-full w-full flex flex-col justify-between text-slate-100 font-sans relative"
      x-data="tvSignagePlayer()"
      x-init="initPlayer()"
      @keydown.window="handleKeyboard($event)">

    <!-- Background Ambient Mesh Lighting -->
    <div class="ambient-glow w-[500px] h-[500px] -top-32 -left-32 bg-[#7A5AF8]/25"></div>
    <div class="ambient-glow w-[600px] h-[600px] -bottom-40 -right-40 bg-[#FF58D5]/20"></div>
    <div class="ambient-glow w-[450px] h-[450px] top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 bg-cyan-500/15"></div>

    <!-- ========================================================================= -->
    <!-- 1. TOP HEADER BAR (Header Display TV)                                      -->
    <!-- ========================================================================= -->
    <header x-show="showHeaderFooter" x-transition.opacity.duration.300ms class="relative z-20 h-16 sm:h-20 px-6 sm:px-10 bg-[#090E1A]/95 backdrop-blur-xl border-b border-white/[0.08] flex items-center justify-between shadow-2xl shrink-0">
        
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

        <!-- Right: Status / Slide Counter + Digital Clock & Date Live + Fullscreen Toggle -->
        <div class="flex items-center gap-3 sm:gap-5 shrink-0">
            
            <!-- Slide Status & Index Counter Badge (Moved to Top Right) -->
            <div class="px-3 py-1.5 sm:px-4 sm:py-2 rounded-2xl bg-white/[0.06] border border-white/[0.12] text-xs font-mono font-bold text-white shadow-lg flex items-center gap-2">
                <span x-text="isPaused ? '⏸ PAUSED' : '▶ LIVE'" :class="isPaused ? 'text-amber-400' : 'text-emerald-400'" class="tracking-wide"></span>
                <span class="text-slate-500">•</span>
                <span class="flex items-center gap-1">
                    <span class="text-white text-xs sm:text-sm font-black" x-text="String(currentIndex + 1).padStart(2, '0')">01</span>
                    <span class="text-slate-500">/</span>
                    <span class="text-slate-400 text-xs sm:text-sm" x-text="String(Math.max(1, totalSlides)).padStart(2, '0')">01</span>
                </span>
            </div>

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
            
            <!-- Direct Blade Rendering (Each Slide 100% Isolated & Opaque) -->
            <div class="relative w-full h-full">
                @php
                    $transitionMode = $settings['tv_signage_transition'] ?? 'fade';
                @endphp

                @foreach($activeSlides as $index => $slide)
                    @if($transitionMode === 'slide')
                        <div x-show="currentIndex === {{ $index }}"
                             x-cloak
                             x-transition:enter="transition-all duration-700 ease-out"
                             x-transition:enter-start="opacity-0 translate-x-full"
                             x-transition:enter-end="opacity-100 translate-x-0"
                             x-transition:leave="transition-all duration-500 ease-in"
                             x-transition:leave-start="opacity-100 translate-x-0"
                             x-transition:leave-end="opacity-0 -translate-x-full"
                             class="absolute inset-0 w-full h-full bg-[#070B14] flex items-center justify-center overflow-hidden">
                    @elseif($transitionMode === 'zoom')
                        <div x-show="currentIndex === {{ $index }}"
                             x-cloak
                             x-transition:enter="transition-all duration-700 ease-out"
                             x-transition:enter-start="opacity-0 scale-90"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition-all duration-500 ease-in"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-110"
                             class="absolute inset-0 w-full h-full bg-[#070B14] flex items-center justify-center overflow-hidden">
                    @else
                        <div x-show="currentIndex === {{ $index }}"
                             x-cloak
                             x-transition:enter="transition-opacity duration-700 ease-out"
                             x-transition:enter-start="opacity-0"
                             x-transition:enter-end="opacity-100"
                             x-transition:leave="transition-opacity duration-500 ease-in"
                             x-transition:leave-start="opacity-100"
                             x-transition:leave-end="opacity-0"
                             class="absolute inset-0 w-full h-full bg-[#070B14] flex items-center justify-center overflow-hidden">
                    @endif
                        
                        {{-- SLIDE VARIANT A: CUSTOM IMAGE / POSTER / BANNER --}}
                        @if($slide['type'] === 'image' && !empty($slide['media_url']))
                            <div class="relative w-full h-full flex items-center justify-center bg-black">
                                <div class="absolute inset-0 bg-cover bg-center blur-3xl opacity-50 scale-110" style="background-image: url('{{ $slide['media_url'] }}')"></div>
                                <img src="{{ $slide['media_url'] }}" alt="{{ $slide['title'] }}" class="relative z-10 max-h-full max-w-full w-auto h-auto object-contain drop-shadow-2xl">
                                
                                <div class="absolute bottom-6 left-6 z-20 max-w-xl p-4 rounded-2xl bg-[#090E1A]/90 backdrop-blur-xl border border-white/[0.15] shadow-2xl">
                                    <span class="text-[10px] font-mono font-black uppercase tracking-widest text-[#FF58D5] block">TALENTA TV SPONSOR & EVENT</span>
                                    <h3 class="text-base font-black text-white leading-snug">{{ $slide['title'] }}</h3>
                                    @if(!empty($slide['notes']))
                                        <p class="text-xs text-slate-300 mt-0.5">{{ $slide['notes'] }}</p>
                                    @endif
                                </div>
                            </div>

                        {{-- SLIDE VARIANT B: VIDEO MP4 / WEBM --}}
                        @elseif($slide['type'] === 'video' && !empty($slide['media_url']))
                            <div class="relative w-full h-full bg-black flex items-center justify-center">
                                <video id="video-slide-{{ $index }}" src="{{ $slide['media_url'] }}" class="w-full h-full object-contain" autoplay muted playsinline></video>
                            </div>

                        {{-- SLIDE VARIANT C: SPONSOR SINGLE SPOTLIGHT (1 SLIDE 1 LOGO BESAR FULL LAYAR) --}}
                        @elseif($slide['type'] === 'sponsor_single' && !empty($slide['logo_url']))
                            <div class="relative w-full h-full flex flex-col items-center justify-between p-3 sm:p-5 lg:p-6 bg-gradient-to-br from-[#090D1A] via-[#060912] to-[#11091C] overflow-hidden">
                                
                                <!-- Background Ambient Spotlight Glows -->
                                <div class="w-[600px] h-[600px] rounded-full bg-gradient-to-tr from-[#7A5AF8]/25 via-[#4E6EFF]/20 to-[#FF58D5]/25 blur-[140px] absolute pointer-events-none"></div>
                                <div class="w-[400px] h-[400px] rounded-full bg-cyan-500/15 blur-[120px] absolute pointer-events-none"></div>

                                <!-- Header Slide Sponsor (Compact & Ultra Clean) -->
                                <div class="text-center space-y-1 relative z-10 shrink-0">
                                    <div class="inline-flex items-center gap-2 px-4 py-1 rounded-full text-[11px] sm:text-xs font-mono font-black uppercase tracking-widest bg-gradient-to-r from-amber-500/20 to-orange-500/20 text-amber-300 border border-amber-500/40 shadow-xl backdrop-blur-md">
                                        <i data-lucide="award" class="w-3.5 h-3.5 text-amber-400"></i>
                                        <span>★ OFFICIAL SPONSOR & PARTNER ★</span>
                                    </div>
                                    <h3 class="text-xl sm:text-2xl lg:text-3xl font-black text-white font-display tracking-tight drop-shadow-md">
                                        {{ $slide['title'] }}
                                    </h3>
                                </div>

                                <!-- Single Logo Hero Card (MAXIMUM FULL SCREEN SPOTLIGHT) -->
                                <div class="relative z-10 w-full max-w-6xl px-2 sm:px-4 flex-1 flex items-center justify-center my-1 sm:my-2 overflow-hidden">
                                    <div class="w-full h-full max-h-[560px] sm:max-h-[640px] lg:max-h-[720px] p-6 sm:p-10 rounded-3xl bg-gradient-to-b from-[#111933]/90 to-[#0A0F20]/90 border border-white/[0.16] shadow-[0_25px_60px_rgba(0,0,0,0.85)] flex items-center justify-center backdrop-blur-2xl relative">
                                        <img src="{{ $slide['logo_url'] }}" 
                                             alt="Sponsor Logo" 
                                             class="max-h-full max-w-full w-auto h-auto object-contain drop-shadow-[0_25px_50px_rgba(0,0,0,0.95)] transition-transform duration-700 ease-out hover:scale-105">
                                    </div>
                                </div>

                                <!-- Footer Caption -->
                                <div class="text-center relative z-10 shrink-0 pb-1">
                                    <p class="text-[11px] sm:text-xs lg:text-sm text-slate-300 font-medium max-w-2xl mx-auto drop-shadow-sm">
                                        {{ $slide['notes'] ?? 'Terima kasih atas partisipasi dan dukungan sponsorship TALENTA 2026' }}
                                    </p>
                                </div>

                            </div>

                        {{-- SLIDE VARIANT C2: SPONSOR GROUP (MULTI LOGO) --}}
                        @elseif($slide['type'] === 'sponsor_group' && !empty($slide['logos']))
                            <div class="relative w-full h-full flex flex-col items-center justify-center p-6 sm:p-12 bg-gradient-to-br from-[#0A0F1D] via-[#070B14] to-[#140D24] overflow-hidden">
                                
                                <!-- Ambient Glow -->
                                <div class="w-96 h-96 rounded-full bg-gradient-to-tr from-[#7A5AF8]/30 via-[#4E6EFF]/20 to-[#FF58D5]/25 blur-3xl absolute pointer-events-none"></div>

                                <!-- Header Slide Sponsor -->
                                <div class="text-center space-y-1.5 mb-8 relative z-10">
                                    <span class="px-3.5 py-1 rounded-full text-[11px] font-mono font-black uppercase tracking-widest bg-amber-500/20 text-amber-300 border border-amber-500/30 shadow-md">
                                        ★ OFFICIAL SPONSOR & PARTNER ★
                                    </span>
                                    <h3 class="text-2xl sm:text-4xl font-black text-white font-display">
                                        Didukung Oleh Mitra Resmi
                                    </h3>
                                    <p class="text-xs sm:text-sm text-slate-400">
                                        {{ $slide['notes'] ?? 'Terima kasih atas partisipasi dan dukungan sponsorship' }}
                                    </p>
                                </div>

                                <!-- 6 Logos Grid (3 Columns x 2 Rows) - Extra Large Cards -->
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-5 sm:gap-8 max-w-5xl mx-auto w-full relative z-10 px-4">
                                    @foreach($slide['logos'] as $logoUrl)
                                        <div class="p-6 sm:p-8 rounded-3xl bg-[#0E1528]/90 border border-white/[0.12] hover:border-[#7A5AF8]/60 flex items-center justify-center shadow-2xl transition-all duration-300 hover:scale-105 h-32 sm:h-44">
                                            <img src="{{ $logoUrl }}" alt="Sponsor Logo" class="h-full w-auto max-w-full object-contain drop-shadow-lg">
                                        </div>
                                    @endforeach
                                </div>

                            </div>

                        {{-- SLIDE VARIANT D: EVENT HERO SHOWCASE --}}
                        @elseif($slide['type'] === 'default_event')
                            <div class="relative w-full h-full flex flex-col items-center justify-center text-center p-8 sm:p-14 space-y-6 bg-gradient-to-br from-[#0D1527] via-[#070B14] to-[#160B26]">
                                
                                <div class="w-96 h-96 rounded-full bg-gradient-to-tr from-[#7A5AF8]/40 via-[#4E6EFF]/30 to-[#FF58D5]/40 blur-3xl absolute pointer-events-none"></div>
                                
                                <div class="relative z-10 max-w-4xl mx-auto space-y-6">
                                    <div class="inline-flex items-center gap-2 px-5 py-2 rounded-full bg-white/[0.08] border border-white/[0.15] text-[#84D0FF] text-xs sm:text-sm font-bold shadow-lg">
                                        <i data-lucide="sparkles" class="w-4 h-4 text-amber-400"></i>
                                        <span>{{ $settings['tv_signage_header_subtitle'] ?? 'PENTAS SENI & KEJUARAAN PELAJAR JAWA TIMUR' }}</span>
                                    </div>
                                    
                                    <h2 class="text-4xl sm:text-6xl lg:text-7xl font-black text-white font-display tracking-tight leading-tight drop-shadow-2xl">
                                        {{ $settings['tv_signage_header_title'] ?? 'Milad ke-57 MTsN 1 Blitar' }}
                                    </h2>
                                    
                                    <p class="text-base sm:text-2xl text-slate-300 max-w-3xl mx-auto leading-relaxed">
                                        Ajang kompetisi bergengsi tingkat SD/MI & SMP/MTs sederajat. Junjung tinggi sportivitas, ukir prestasi gemilang!
                                    </p>
                                </div>

                            </div>
                        @endif

                    </div>
                @endforeach
            </div>

            <!-- Bottom Overall Playlist Progress Bar (1 Full Cycle Progress, No Per-Slide Reset) -->
            <div class="absolute bottom-0 left-0 right-0 h-1.5 bg-white/[0.08] z-30 overflow-hidden">
                <div class="h-full bg-gradient-to-r from-[#7A5AF8] via-[#4E6EFF] to-[#FF58D5] transition-all ease-linear"
                     :style="'width: ' + overallProgressPercent + '%; transition-duration: 50ms;'"></div>
            </div>

        </div>

    </main>

    <!-- ========================================================================= -->
    <!-- 3. BOTTOM SIGNAGE FOOTER (24 Sponsor Marquee + Running Text Ticker)        -->
    <!-- ========================================================================= -->
    <footer x-show="showHeaderFooter" x-transition.opacity.duration.300ms class="relative z-20 flex flex-col bg-[#080C17]/98 backdrop-blur-2xl border-t border-white/[0.08] shadow-2xl shrink-0">
        
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
                            $logoUrl = \Illuminate\Support\Str::startsWith($logo, ['http://', 'https://']) ? $logo : asset('storage/' . $cleanLogo);
                        @endphp
                        <div class="h-9 sm:h-11 px-3 py-1 rounded-xl bg-white/[0.04] border border-white/[0.08] flex items-center justify-center shrink-0">
                            <img src="{{ $logoUrl }}" alt="Sponsor" class="h-full w-auto max-w-[120px] object-contain drop-shadow-sm">
                        </div>
                    @endforeach

                    <!-- Repeat 2 (For infinite loop smoothness) -->
                    @foreach($sponsorLogos as $logo)
                        @php
                            $cleanLogo = ltrim(str_replace(['public/', 'storage/'], '', $logo), '/');
                            $logoUrl = \Illuminate\Support\Str::startsWith($logo, ['http://', 'https://']) ? $logo : asset('storage/' . $cleanLogo);
                        @endphp
                        <div class="h-9 sm:h-11 px-3 py-1 rounded-xl bg-white/[0.04] border border-white/[0.08] flex items-center justify-center shrink-0">
                            <img src="{{ $logoUrl }}" alt="Sponsor" class="h-full w-auto max-w-[120px] object-contain drop-shadow-sm">
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
                totalSlides: {{ count($activeSlides) }},
                currentIndex: 0,
                isPaused: false,
                isMuted: true,
                isTransitioning: false,
                showHeaderFooter: true,
                progressPercent: 0,
                currentDuration: 10,
                progressInterval: null,
                pollInterval: null,
                clockInterval: null,
                clockTime: '00:00:00',
                clockDate: '',
                currentVersion: '{{ $settings['tv_signage_version'] ?? 'v1' }}',

                get overallProgressPercent() {
                    if (this.totalSlides <= 0) return 0;
                    const fraction = (this.progressPercent || 0) / 100;
                    const val = ((this.currentIndex + fraction) / this.totalSlides) * 100;
                    return Math.min(100, Math.max(0, val)).toFixed(2);
                },

                initPlayer() {
                    this.updateClock();
                    this.clockInterval = setInterval(() => { this.updateClock(); }, 1000);

                    // Start Slideshow Loop
                    this.startSlideTimer();

                    // Start Background API Sync (Check for live updates from admin every 20s)
                    this.pollInterval = setInterval(() => { this.checkLiveState(); }, 20000);

                    this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
                },

                startSlideTimer() {
                    if (this.totalSlides === 0) return;

                    if (this.progressInterval) {
                        clearInterval(this.progressInterval);
                        this.progressInterval = null;
                    }

                    const cur = this.slides[this.currentIndex];
                    this.currentDuration = (cur && cur.duration) ? Math.max(2, parseInt(cur.duration)) : 10;
                    this.progressPercent = 0;

                    // If video slide, let the video drive duration & progress cleanly
                    if (cur && cur.type === 'video') {
                        this.$nextTick(() => {
                            const videoEl = document.getElementById('video-slide-' + this.currentIndex);
                            if (videoEl) {
                                videoEl.currentTime = 0;
                                videoEl.muted = this.isMuted;
                                videoEl.play().catch(e => console.log('Auto-play error:', e));

                                videoEl.ontimeupdate = () => {
                                    if (videoEl.duration && !isNaN(videoEl.duration)) {
                                        this.progressPercent = (videoEl.currentTime / videoEl.duration) * 100;
                                    }
                                };

                                videoEl.onended = () => {
                                    videoEl.ontimeupdate = null;
                                    videoEl.onended = null;
                                    this.nextSlide();
                                };
                            }
                        });
                        return;
                    }

                    // For image / sponsor slides, run smooth step-by-step progress timer
                    const stepMs = 50;
                    const totalSteps = Math.max(1, Math.round((this.currentDuration * 1000) / stepMs));
                    let currentStep = 0;

                    this.progressInterval = setInterval(() => {
                        if (!this.isPaused) {
                            currentStep++;
                            this.progressPercent = Math.min(100, (currentStep / totalSteps) * 100);

                            if (currentStep >= totalSteps) {
                                if (this.progressInterval) {
                                    clearInterval(this.progressInterval);
                                    this.progressInterval = null;
                                }
                                this.nextSlide();
                            }
                        }
                    }, stepMs);
                },

                nextSlide() {
                    if (this.totalSlides === 0 || this.isTransitioning) return;
                    this.isTransitioning = true;

                    if (this.progressInterval) {
                        clearInterval(this.progressInterval);
                        this.progressInterval = null;
                    }

                    this.currentIndex = (this.currentIndex + 1) % this.totalSlides;
                    this.startSlideTimer();

                    setTimeout(() => {
                        this.isTransitioning = false;
                    }, 350);
                },

                prevSlide() {
                    if (this.totalSlides === 0 || this.isTransitioning) return;
                    this.isTransitioning = true;

                    if (this.progressInterval) {
                        clearInterval(this.progressInterval);
                        this.progressInterval = null;
                    }

                    this.currentIndex = (this.currentIndex - 1 + this.totalSlides) % this.totalSlides;
                    this.startSlideTimer();

                    setTimeout(() => {
                        this.isTransitioning = false;
                    }, 350);
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
                                console.log('New TV Signage version detected, reloading...');
                                window.location.reload();
                            }
                        })
                        .catch(err => console.error('Signage sync polling error:', err));
                }
            };
        }
    </script>
</body>
</html>
