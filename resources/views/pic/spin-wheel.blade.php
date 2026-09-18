@extends('layouts.admin')

@section('title', 'Spin Wheel Undian Nomor Tampil - ' . $competition->name)
@section('page_title', 'Interactive Spin Wheel Undian')

@section('content')
<div class="space-y-6 font-sans" x-data="spinWheelApp()">
    
    <!-- Top Action Bar (Clean & Focused) -->
    <div class="bg-slate-900 rounded-3xl p-4 sm:p-6 border border-slate-800 shadow-xl flex flex-col sm:flex-row sm:items-center justify-between gap-4 text-white">
        <div class="space-y-1">
            <h2 class="text-xl sm:text-2xl font-black text-white tracking-tight flex items-center gap-2">
                <span>🏸 {{ $competition->name }}</span>
            </h2>
            <p class="text-xs text-slate-400 font-mono">
                <span class="text-amber-400 font-bold" x-text="allUndrawnParticipants.length"></span> Belum Diundi • 
                <span class="text-emerald-400 font-bold" x-text="allDrawnParticipants.length"></span> Selesai Terkunci
            </p>
        </div>

        <div class="flex items-center flex-wrap gap-2.5">
            <!-- Menu Seeded Button -->
            <button type="button" 
                    @click="openSeededModal()" 
                    :disabled="isSpinning"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-amber-500/15 hover:bg-amber-500/25 text-amber-300 border border-amber-500/40 font-bold text-xs shadow-sm transition cursor-pointer"
                    title="Menu Pengaturan Pemain Unggulan (Seeded)">
                <i data-lucide="star" class="w-4 h-4 text-amber-400"></i>
                <span>Atur Seeded</span>
            </button>

            @if(strtoupper($competition->code ?? '') === 'BLT' || str_contains(strtolower($competition->name ?? ''), 'bulu tangkis') || str_contains(strtolower($competition->name ?? ''), 'badminton'))
            <!-- Bagan Pertandingan -->
            <a :href="'{{ route('pic.bracket', $competition->id) }}' + (activePoolKey ? '?pool=' + encodeURIComponent(activePoolKey) : '')" 
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-indigo-500/15 hover:bg-indigo-500/25 text-indigo-300 border border-indigo-500/40 font-bold text-xs shadow-sm transition cursor-pointer" 
               title="Lihat Bagan Pertandingan (Knockout Bracket)">
                <i data-lucide="git-branch" class="w-4 h-4 text-indigo-400"></i>
                <span>Bagan</span>
            </a>
            @endif

            <!-- Public Viewer TV -->
            <a href="{{ url('tv/' . $competition->slug) }}" target="_blank" 
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 font-black text-xs shadow-md shadow-amber-500/20 transition cursor-pointer" 
               title="Buka Layar Tampilan TV: /tv/{{ $competition->slug }}">
                <i data-lucide="tv" class="w-4 h-4"></i>
                <span>Layar TV</span>
            </a>

            <!-- Dropdown Menu Lainnya (⋮) -->
            <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                <button type="button" 
                        @click="open = !open; $nextTick(() => { if (window.lucide) window.lucide.createIcons(); })" 
                        class="px-3 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 font-bold text-xs transition flex items-center gap-1 cursor-pointer"
                        title="Menu Tambahan & Pengaturan">
                    <i data-lucide="more-vertical" class="w-4 h-4"></i>
                    <span>Lainnya</span>
                </button>

                <div x-show="open" 
                     x-cloak 
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="absolute right-0 mt-2 w-56 bg-slate-900 border border-slate-700 rounded-2xl shadow-2xl py-2 z-50 text-xs">
                    
                    <div class="px-3 py-1.5 text-[10px] font-mono uppercase tracking-wider text-slate-400 font-bold border-b border-slate-800">
                        Opsi & Tampilan
                    </div>

                    <!-- Mode Hacker Link -->
                    <a href="{{ route('pic.hacker.draw', $competition->id) }}" 
                       class="flex items-center gap-2.5 px-3.5 py-2 text-emerald-400 hover:bg-slate-800 transition font-bold">
                        <i data-lucide="terminal" class="w-4 h-4"></i>
                        <span>Mode Hacker</span>
                    </a>

                    <!-- Switch Theme -->
                    <div class="px-3.5 py-2 flex items-center justify-between border-t border-slate-800/80">
                        <span class="text-slate-300 font-bold">Tema Roda:</span>
                        <div class="inline-flex rounded-lg bg-slate-950 p-0.5 border border-slate-800 text-[10px]">
                            <button type="button" @click="setTheme('badminton')" :class="theme === 'badminton' ? 'bg-emerald-500 text-slate-950 font-black' : 'text-slate-400 hover:text-white'" class="px-2 py-0.5 rounded transition cursor-pointer">
                                🏸 Arena
                            </button>
                            <button type="button" @click="setTheme('standard')" :class="theme === 'standard' ? 'bg-amber-500 text-slate-950 font-black' : 'text-slate-400 hover:text-white'" class="px-2 py-0.5 rounded transition cursor-pointer">
                                Standar
                            </button>
                        </div>
                    </div>

                    <div class="my-1 border-t border-slate-800"></div>

                    <!-- Reset All Undian -->
                    <form action="{{ route('pic.spin.wheel.reset', $competition->id) }}" method="POST" onsubmit="return confirm('PERINGATAN: Apakah Anda yakin ingin me-reset SEMUA nomor undian untuk semua kategori di cabang {{ addslashes($competition->name) }}?')">
                        @csrf
                        <button type="submit" class="w-full text-left flex items-center gap-2 px-3.5 py-2 text-rose-400 hover:bg-rose-500/10 transition font-bold cursor-pointer">
                            <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                            <span>Reset Semua Undian</span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Back Button -->
            <a href="{{ route('pic.undian') }}" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-xs transition">
                Kembali
            </a>
        </div>
    </div>

    <!-- Category & Sector Navigation Bar (Smart & Practical - Single Unified Selector) -->
    @if(count($pools) > 1)
    <div class="bg-slate-900 rounded-3xl p-4 sm:px-6 sm:py-4 border border-slate-800 shadow-xl">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <!-- Left: Smart Category Dropdown -->
            <div class="flex items-center gap-3.5 flex-1">
                <div class="w-10 h-10 rounded-2xl bg-amber-500/15 border border-amber-500/30 flex items-center justify-center shrink-0 text-amber-400 shadow-inner">
                    <i data-lucide="layers" class="w-5 h-5"></i>
                </div>
                <div class="flex-1 max-w-lg">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-[10px] font-mono font-bold uppercase tracking-wider text-amber-400">Pilih Kategori / Sektor:</span>
                        <span class="text-[10px] font-mono text-slate-400">
                            (<span class="text-white font-bold" x-text="pools.length"></span> Kategori)
                        </span>
                    </div>
                    <select :value="activePoolKey" 
                            @change="switchPool($event.target.value)" 
                            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border-2 border-slate-700 hover:border-slate-600 focus:border-amber-400 text-white font-bold text-xs sm:text-sm outline-none transition cursor-pointer shadow-inner">
                        <template x-for="p in pools" :key="p.key">
                            <option :value="p.key" 
                                    :selected="activePoolKey === p.key"
                                    x-text="p.title + ' (' + p.participants.length + ' Peserta)'"></option>
                        </template>
                    </select>
                </div>
            </div>

            <!-- Right: Active Status & Reset Kategori -->
            <div class="flex items-center justify-between md:justify-end gap-4 pt-2 md:pt-0 border-t md:border-t-0 border-slate-800">
                <div class="text-left md:text-right">
                    <span class="text-[10px] text-slate-400 font-mono block">Status Undian Kategori:</span>
                    <span class="text-xs font-mono font-bold" 
                          :class="getPoolStats(activePoolKey).undrawn === 0 ? 'text-emerald-400' : (getPoolStats(activePoolKey).drawn === 0 ? 'text-slate-300' : 'text-amber-400')"
                          x-text="getPoolStats(activePoolKey).drawn === 0 
                              ? ('Belum Diundi • ' + getPoolStats(activePoolKey).undrawn + ' Peserta') 
                              : (getPoolStats(activePoolKey).undrawn === 0 
                                  ? ('Selesai Diundi (' + getPoolStats(activePoolKey).drawn + ' Peserta)') 
                                  : (getPoolStats(activePoolKey).drawn + ' Terundi • ' + getPoolStats(activePoolKey).undrawn + ' Tersisa'))"></span>
                </div>
                <button type="button" 
                        @click="resetActivePool()" 
                        :disabled="activeDrawnParticipants.length === 0 || isSpinning"
                        class="px-3.5 py-2 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/30 text-xs font-bold disabled:opacity-25 disabled:cursor-not-allowed transition cursor-pointer"
                        title="Reset nomor undian khusus kategori ini">
                    Reset Kategori
                </button>
            </div>
        </div>
    </div>
    @endif

    <!-- Main Workspace Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- Left 7 Cols: Interactive Canvas Wheel -->
        <div class="lg:col-span-7 bg-slate-950 rounded-3xl p-6 sm:p-8 border-2 transition-all duration-500 shadow-2xl flex flex-col items-center justify-center text-center relative overflow-hidden"
             :class="theme === 'badminton' ? 'border-emerald-500/40 shadow-emerald-950/40' : 'border-amber-500/30 shadow-slate-950/50'">
            
            <!-- Background Glow Overlay -->
            <div class="absolute inset-0 transition-all duration-500 pointer-events-none"
                 :class="theme === 'badminton' ? 'bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-emerald-950/40 via-slate-950 to-slate-950' : 'bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-amber-950/20 via-slate-950 to-slate-950'"></div>

            <!-- BADMINTON ARENA DECORATION: Crossed Rackets & Golden Ribbons (Active in Badminton Theme) -->
            <div x-show="theme === 'badminton'" x-transition.opacity.duration.400ms class="absolute inset-0 flex items-center justify-center pointer-events-none select-none overflow-hidden">
                <svg class="w-[620px] h-[620px] max-w-none opacity-85 -translate-y-6" viewBox="0 0 600 600" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <linearGradient id="goldRibbon" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#fef08a" />
                            <stop offset="50%" stop-color="#f59e0b" />
                            <stop offset="100%" stop-color="#b45309" />
                        </linearGradient>
                        <linearGradient id="racketFrameGreen" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#15803d" />
                            <stop offset="50%" stop-color="#166534" />
                            <stop offset="100%" stop-color="#14532d" />
                        </linearGradient>
                        <pattern id="racketStrings" width="8" height="8" patternUnits="userSpaceOnUse">
                            <path d="M 8 0 L 0 0 0 8" fill="none" stroke="rgba(255,255,255,0.35)" stroke-width="0.75" />
                        </pattern>
                    </defs>

                    <!-- Left Racket (Tilted -35 deg) -->
                    <g transform="translate(300, 270) rotate(-35) translate(-300, -270)">
                        <!-- Handle -->
                        <rect x="291" y="380" width="18" height="170" rx="6" fill="#1e293b" stroke="#0f172a" stroke-width="2" />
                        <!-- Grip tape ridges -->
                        <line x1="291" y1="410" x2="309" y2="415" stroke="#334155" stroke-width="1.5" />
                        <line x1="291" y1="440" x2="309" y2="445" stroke="#334155" stroke-width="1.5" />
                        <line x1="291" y1="470" x2="309" y2="475" stroke="#334155" stroke-width="1.5" />
                        <line x1="291" y1="500" x2="309" y2="505" stroke="#334155" stroke-width="1.5" />
                        <!-- Gold end cap -->
                        <rect x="290" y="540" width="20" height="12" rx="3" fill="url(#goldRibbon)" />
                        <!-- Shaft -->
                        <rect x="296" y="240" width="8" height="145" fill="url(#racketFrameGreen)" stroke="#f59e0b" stroke-width="1" />
                        <!-- T-Joint -->
                        <path d="M 294 240 Q 300 230 306 240 Z" fill="url(#goldRibbon)" />
                        <!-- Head Oval with strings -->
                        <ellipse cx="300" cy="115" rx="85" ry="110" fill="url(#racketStrings)" />
                        <!-- Head Outer Frame (Green & Gold Bevel) -->
                        <ellipse cx="300" cy="115" rx="85" ry="110" stroke="url(#goldRibbon)" stroke-width="10" fill="none" />
                        <ellipse cx="300" cy="115" rx="85" ry="110" stroke="url(#racketFrameGreen)" stroke-width="6" fill="none" />
                    </g>

                    <!-- Right Racket (Tilted +35 deg) -->
                    <g transform="translate(300, 270) rotate(35) translate(-300, -270)">
                        <!-- Handle -->
                        <rect x="291" y="380" width="18" height="170" rx="6" fill="#1e293b" stroke="#0f172a" stroke-width="2" />
                        <!-- Grip tape ridges -->
                        <line x1="291" y1="410" x2="309" y2="415" stroke="#334155" stroke-width="1.5" />
                        <line x1="291" y1="440" x2="309" y2="445" stroke="#334155" stroke-width="1.5" />
                        <line x1="291" y1="470" x2="309" y2="475" stroke="#334155" stroke-width="1.5" />
                        <line x1="291" y1="500" x2="309" y2="505" stroke="#334155" stroke-width="1.5" />
                        <!-- Gold end cap -->
                        <rect x="290" y="540" width="20" height="12" rx="3" fill="url(#goldRibbon)" />
                        <!-- Shaft -->
                        <rect x="296" y="240" width="8" height="145" fill="url(#racketFrameGreen)" stroke="#f59e0b" stroke-width="1" />
                        <!-- T-Joint -->
                        <path d="M 294 240 Q 300 230 306 240 Z" fill="url(#goldRibbon)" />
                        <!-- Head Oval with strings -->
                        <ellipse cx="300" cy="115" rx="85" ry="110" fill="url(#racketStrings)" />
                        <!-- Head Outer Frame (Green & Gold Bevel) -->
                        <ellipse cx="300" cy="115" rx="85" ry="110" stroke="url(#goldRibbon)" stroke-width="10" fill="none" />
                        <ellipse cx="300" cy="115" rx="85" ry="110" stroke="url(#racketFrameGreen)" stroke-width="6" fill="none" />
                    </g>

                    <!-- Golden Swirling Ribbons on the sides -->
                    <path d="M 70 230 C 30 300, 50 400, 110 440 C 160 470, 180 400, 160 360 C 130 310, 80 300, 70 230 Z" fill="url(#goldRibbon)" opacity="0.85" filter="drop-shadow(0 4px 10px rgba(0,0,0,0.5))" />
                    <path d="M 530 230 C 570 300, 550 400, 490 440 C 440 470, 420 400, 440 360 C 470 310, 520 300, 530 230 Z" fill="url(#goldRibbon)" opacity="0.85" filter="drop-shadow(0 4px 10px rgba(0,0,0,0.5))" />
                </svg>
            </div>

            <!-- Active Pool Title Banner (Clean & Focused) -->
            <div class="w-full max-w-md mb-2 bg-slate-900/80 rounded-2xl py-2 px-4 border border-slate-800 flex items-center justify-between gap-3 text-left shadow-lg">
                <div class="flex items-center gap-2.5 overflow-hidden">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse shrink-0"></span>
                    <h4 class="text-xs sm:text-sm font-black text-white truncate" x-text="activePool?.title || '{{ $competition->name }}'"></h4>
                </div>
                <span class="text-[11px] font-mono font-bold text-amber-400 shrink-0 bg-amber-500/10 px-2.5 py-0.5 rounded-full border border-amber-500/20" 
                      x-text="activeUndrawnParticipants.length + ' Belum Diundi'"></span>
            </div>

            <div class="relative z-10 w-full flex flex-col items-center">
                <!-- Wheel Pointer Arrow -->
                <div class="relative mb-3">
                    <!-- Standard Pointer -->
                    <div x-show="theme === 'standard'" class="absolute -top-3.5 left-1/2 -translate-x-1/2 z-20 w-8 h-8 flex items-center justify-center">
                        <div class="w-0 h-0 border-l-[14px] border-l-transparent border-r-[14px] border-r-transparent border-t-[24px] border-t-amber-400 drop-shadow-[0_4px_10px_rgba(251,191,36,0.8)]"></div>
                    </div>

                    <!-- Badminton Pointer (Red Triangle with Gold Trim & White Shuttlecock Cork) -->
                    <div x-show="theme === 'badminton'" class="absolute -top-5 left-1/2 -translate-x-1/2 z-20 flex items-center justify-center pointer-events-none">
                        <svg width="42" height="48" viewBox="0 0 42 48" fill="none" class="drop-shadow-[0_4px_12px_rgba(220,38,38,0.85)]">
                            <!-- Gold outer frame -->
                            <polygon points="21,46 2,4 40,4" fill="#f59e0b" stroke="#fef08a" stroke-width="2.5" />
                            <!-- Red inner body -->
                            <polygon points="21,40 6,7 36,7" fill="#dc2626" />
                            <!-- White shuttlecock head dot -->
                            <circle cx="21" cy="18" r="6.5" fill="#ffffff" stroke="#cbd5e1" stroke-width="1.5" />
                            <circle cx="21" cy="18" r="3.5" fill="#f8fafc" />
                        </svg>
                    </div>

                    <!-- Canvas Wheel Container -->
                    <div class="relative p-2.5 rounded-full bg-slate-900 border-4 shadow-2xl transition-all duration-500"
                         :class="theme === 'badminton' ? 'border-amber-400/90 shadow-amber-500/10' : 'border-slate-800'">
                        <canvas id="wheelCanvas" width="440" height="440" class="max-w-full rounded-full cursor-pointer transition-transform"></canvas>
                    </div>
                </div>

                <!-- Badminton Court Base Podium (Active in Badminton Theme) -->
                <div x-show="theme === 'badminton'" x-transition class="w-full max-w-md my-2 relative z-10 flex flex-col items-center select-none">
                    <div class="w-full h-14 bg-gradient-to-b from-emerald-600 via-emerald-700 to-emerald-900 border-2 border-amber-400/90 rounded-2xl shadow-2xl relative overflow-hidden flex items-center justify-between px-4">
                        <!-- White Court Line Grid -->
                        <div class="absolute inset-x-4 top-2.5 bottom-2.5 border-2 border-white/80 pointer-events-none"></div>
                        <div class="absolute inset-x-8 top-2.5 bottom-2.5 border-x-2 border-white/80 pointer-events-none"></div>
                        <div class="absolute inset-y-2.5 left-1/2 -translate-x-1/2 w-0.5 bg-white/80 pointer-events-none"></div>
                        <div class="absolute inset-x-4 top-1/2 -translate-y-1/2 h-0.5 bg-white/80 pointer-events-none"></div>

                        <!-- Left Shuttlecock on Court -->
                        <div class="relative z-10 flex items-center gap-1.5 bg-slate-950/80 px-2.5 py-1 rounded-xl border border-amber-400/40">
                            <span class="text-sm">🏸</span>
                            <span class="text-[10px] font-black text-amber-300 font-mono uppercase tracking-wider">COURT A</span>
                        </div>

                        <!-- Center Tournament Badge -->
                        <div class="relative z-10 text-[10px] font-black text-white uppercase tracking-widest bg-emerald-950/90 px-3 py-1 rounded-lg border border-emerald-400/50 shadow-md">
                            ARENA BULU TANGKIS
                        </div>

                        <!-- Right Shuttlecock on Court -->
                        <div class="relative z-10 flex items-center gap-1.5 bg-slate-950/80 px-2.5 py-1 rounded-xl border border-amber-400/40">
                            <span class="text-[10px] font-black text-amber-300 font-mono uppercase tracking-wider">OFFICIAL</span>
                            <span class="text-sm">🏸</span>
                        </div>
                    </div>
                </div>

                <!-- Target Participant Selector & Spin Button -->
                <div class="w-full max-w-md space-y-4 pt-1">
                    <div class="text-left">
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-amber-400 mb-1">
                            Peserta yang Sedang Diundi:
                            <span class="text-slate-400 font-normal font-mono" x-text="'(' + activeUndrawnParticipants.length + ' tersisa)'"></span>
                        </label>
                        <select x-model="selectedParticipantId" :disabled="isSpinning || activeUndrawnParticipants.length === 0" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-amber-500/30 text-white text-xs font-bold outline-none focus:border-amber-400">
                            <template x-for="p in activeUndrawnParticipants" :key="p.id">
                                <option :value="p.id" x-text="p.name + ' (' + p.institution + ')'"></option>
                            </template>
                        </select>
                    </div>

                    <!-- Spin Trigger Button -->
                    <button type="button" @click="spin()" :disabled="isSpinning || activeUndrawnParticipants.length === 0" class="w-full py-4 px-6 rounded-2xl bg-gradient-to-r from-amber-400 via-amber-500 to-amber-600 hover:from-amber-500 hover:to-amber-700 disabled:opacity-40 disabled:cursor-not-allowed text-slate-950 font-black text-sm sm:text-base tracking-wider uppercase shadow-xl shadow-amber-500/25 hover:scale-[1.01] active:scale-[0.99] transition duration-200 flex items-center justify-center gap-3 cursor-pointer">
                        <i data-lucide="disc" class="w-5 h-5" :class="{ 'animate-spin': isSpinning }"></i>
                        <span x-text="isSpinning ? 'RODA SEDANG BERPUTAR...' : (activeUndrawnParticipants.length === 0 ? 'KATEGORI INI SELESAI DIUNDI' : 'PUTAR RODA UNDIAN SEKARANG')"></span>
                    </button>
                </div>

                <!-- Winner Result Announcement Card -->
                <div x-show="wonDrawNumber" x-transition class="mt-5 p-5 rounded-2xl bg-slate-900/90 border border-amber-400/40 text-center space-y-2 w-full max-w-md shadow-2xl">
                    <span class="text-[10px] font-black uppercase tracking-widest text-amber-400 block">🎉 HASIL PUTARAN RODA RESMI</span>
                    <div class="text-3xl sm:text-4xl font-black text-amber-300 font-mono tracking-wider drop-shadow-[0_0_10px_rgba(251,191,36,0.6)]" x-text="'NOMOR UNDIAN #' + wonDrawNumber"></div>
                    <div class="space-y-1 pt-1">
                        <h4 class="text-base font-bold text-white" x-text="wonParticipantName"></h4>
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-800 border border-emerald-500/30 text-xs font-semibold text-emerald-400">
                            <span x-text="wonParticipantSchool"></span>
                        </div>
                    </div>

                    <!-- BWF Separation Alert if same school detected -->
                    <div x-show="bwfNotification" x-transition class="mt-3 p-3 rounded-xl bg-indigo-950/80 border border-indigo-500/40 text-left flex items-start gap-2.5 shadow-lg">
                        <span class="text-base shrink-0">🛡️</span>
                        <div class="space-y-0.5 min-w-0">
                            <span class="text-[10px] font-black uppercase tracking-wider text-indigo-300 block">
                                Proteksi BWF GCR 14 (Satu Delegasi Sekolah)
                            </span>
                            <p class="text-xs text-slate-200 leading-snug" x-text="bwfNotification"></p>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Right 5 Cols: Drawn & Undrawn Lists (Dark Theme) -->
        <div class="lg:col-span-5 space-y-6">
            
            <!-- List Sudah Mendapatkan Nomor Undian -->
            <div class="bg-slate-900 rounded-3xl p-6 border border-slate-800 shadow-xl space-y-4">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <div class="flex items-center gap-2">
                        <i data-lucide="list-ordered" class="w-4 h-4 text-emerald-400"></i>
                        <h3 class="text-sm font-black text-white">Urutan Tampil Selesai Diundi</h3>
                    </div>
                    <!-- Filter Toggle: Kategori Ini vs Semua -->
                    <div class="inline-flex items-center p-0.5 bg-slate-950 rounded-xl border border-slate-800 text-[10px]">
                        <button type="button" @click="drawnTab = 'pool'" :class="drawnTab === 'pool' ? 'bg-amber-400 text-slate-950 font-black' : 'text-slate-400 hover:text-white'" class="px-2 py-0.5 rounded-lg transition font-bold cursor-pointer">
                            Kategori Ini (<span x-text="activeDrawnParticipants.length"></span>)
                        </button>
                        <button type="button" @click="drawnTab = 'all'" :class="drawnTab === 'all' ? 'bg-amber-400 text-slate-950 font-black' : 'text-slate-400 hover:text-white'" class="px-2 py-0.5 rounded-lg transition font-bold cursor-pointer">
                            Semua (<span x-text="allDrawnParticipants.length"></span>)
                        </button>
                    </div>
                </div>

                <div class="space-y-2.5 max-h-[320px] overflow-y-auto pr-1">
                    <template x-for="item in (drawnTab === 'pool' ? activeDrawnParticipants : allDrawnParticipants)" :key="item.id">
                        <div class="p-3 rounded-2xl bg-slate-950/70 border border-slate-800 hover:border-slate-700 flex items-center justify-between gap-3 transition">
                            <div class="flex items-center gap-3 overflow-hidden">
                                <div class="w-10 h-10 rounded-xl bg-amber-400 text-slate-950 font-mono font-black flex items-center justify-center text-sm shrink-0 shadow-sm shadow-amber-400/20" x-text="'#' + item.draw_number"></div>
                                <div class="overflow-hidden">
                                    <div class="flex items-center gap-2">
                                        <h5 class="text-xs font-bold text-slate-100 truncate" x-text="item.name"></h5>
                                        <span x-show="item.is_seeded" class="px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-amber-500/20 text-amber-300 border border-amber-500/40 shrink-0" x-text="'⭐ ' + (item.seed_label || 'SEED')"></span>
                                    </div>
                                    <div class="flex items-center gap-1.5 text-[11px] text-slate-400 truncate">
                                        <span x-text="item.institution"></span>
                                        <span x-show="drawnTab === 'all' && item.target_class" class="text-amber-400 font-mono text-[10px]" x-text="'• ' + (item.target_class || '')"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-1.5 shrink-0">
                                <span x-show="item.is_seeded" class="text-[10px] font-black text-amber-400 bg-amber-500/15 border border-amber-500/30 px-2 py-0.5 rounded-lg uppercase tracking-wider">Seeded</span>
                                <span class="text-[10px] font-bold text-emerald-400 bg-emerald-500/20 border border-emerald-500/30 px-2.5 py-1 rounded-lg uppercase tracking-wider">Terkunci</span>
                            </div>
                        </div>
                    </template>

                    <div x-show="(drawnTab === 'pool' ? activeDrawnParticipants : allDrawnParticipants).length === 0" class="py-8 text-center text-xs text-slate-500">
                        Belum ada peserta yang diundi pada filter ini.
                    </div>
                </div>
            </div>

            <!-- List Belum Diundi -->
            <div class="bg-slate-900 rounded-3xl p-6 border border-slate-800 shadow-xl space-y-4">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <h3 class="text-sm font-black text-white flex items-center gap-2">
                        <i data-lucide="users" class="w-4 h-4 text-amber-400"></i>
                        <span>Antrean Belum Diundi</span>
                    </h3>
                    <span class="text-xs font-mono font-bold text-amber-300 bg-amber-500/10 border border-amber-500/20 px-2.5 py-0.5 rounded-full" x-text="activeUndrawnParticipants.length + ' Peserta'"></span>
                </div>

                <div class="space-y-2.5 max-h-[260px] overflow-y-auto p-1 pr-2">
                    <template x-for="item in activeUndrawnParticipants" :key="item.id">
                        <div class="p-3 rounded-2xl transition-all flex items-center justify-between gap-3 text-xs" 
                             :class="item.id == selectedParticipantId ? 'border-2 border-amber-400 bg-amber-500/15 shadow-[0_0_15px_rgba(251,191,36,0.25)]' : 'border border-slate-800 bg-slate-950/60 hover:border-slate-700'">
                            <div class="flex items-center gap-3 overflow-hidden">
                                <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 transition"
                                     :class="item.id == selectedParticipantId ? 'bg-amber-400 text-slate-950 font-black shadow-sm' : 'bg-slate-900 text-slate-500 border border-slate-800'">
                                    <span class="text-xs font-mono font-black" x-text="item.id == selectedParticipantId ? '✓' : '•'"></span>
                                </div>
                                <div class="overflow-hidden">
                                    <span class="font-bold truncate block" :class="item.id == selectedParticipantId ? 'text-amber-300 font-extrabold' : 'text-slate-200'" x-text="item.name"></span>
                                    <div class="flex items-center gap-1.5 mt-0.5">
                                        <span class="text-[10px] text-slate-400 truncate block" x-text="item.institution"></span>
                                        <template x-if="hasTeammatesInPool(item)">
                                            <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 shrink-0" title="Sekolah ini memiliki rekan satu delegasi di kategori ini (Proteksi BWF Aktif)">
                                                🛡️ Satu Delegasi
                                            </span>
                                        </template>
                                    </div>
                                </div>
                            </div>
                            <button type="button" @click="selectedParticipantId = item.id" 
                                    class="text-[10px] font-bold px-3 py-1.5 rounded-lg shrink-0 transition cursor-pointer"
                                    :class="item.id == selectedParticipantId ? 'bg-amber-400 text-slate-950 font-black shadow-sm' : 'bg-slate-800 text-slate-300 hover:bg-amber-400 hover:text-slate-950 border border-slate-700'">
                                <span x-text="item.id == selectedParticipantId ? 'Terpilih' : 'Pilih'"></span>
                            </button>
                        </div>
                    </template>

                    <div x-show="activeUndrawnParticipants.length === 0" class="py-6 text-center text-xs text-emerald-400 font-bold">
                        🎉 Semua peserta dalam kategori ini telah selesai diundi!
                    </div>
                </div>
            </div>

        </div>

    </div>

    <!-- Modal Pengaturan Pemain Unggulan (Seeded) -->
    <div x-show="isSeededModalOpen" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/85 backdrop-blur-sm"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95">
        
        <div class="bg-slate-900 border border-slate-700 rounded-3xl p-6 sm:p-7 max-w-2xl w-full max-h-[90vh] flex flex-col shadow-2xl relative text-white space-y-4"
             @click.away="if (!isSavingSeeded) isSeededModalOpen = false">
            
            <!-- Modal Header -->
            <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-0.5 text-[10px] font-mono font-bold rounded bg-amber-500/20 text-amber-300 border border-amber-500/40 uppercase">
                            ⭐ ATUR PEMAIN UNGGULAN (SEEDED)
                        </span>
                    </div>
                    <h3 class="text-lg sm:text-xl font-black text-white" x-text="'Pengaturan Seeded - ' + (activePool?.short_title || activePool?.title || 'Kategori')"></h3>
                    <p class="text-xs text-slate-400">
                        Pilih langsung siapa pemain unggulan untuk tiap posisi Seed. Pemain unggulan otomatis menempati slot tetap bagan dan <strong class="text-amber-400">tidak diundi dalam spin wheel</strong>.
                    </p>
                </div>
                <button type="button" @click="isSeededModalOpen = false" :disabled="isSavingSeeded" class="p-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800 transition cursor-pointer">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Seed Menu Controls Toolbar -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-950/80 p-3.5 rounded-2xl border border-slate-800">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-amber-500/20 border border-amber-500/40 flex items-center justify-center shrink-0">
                        <i data-lucide="sliders" class="w-4 h-4 text-amber-400"></i>
                    </div>
                    <div>
                        <span class="text-xs font-black text-white block">Menu Slot Seeded:</span>
                        <span class="text-[11px] text-slate-400 font-mono">
                            Menampilkan <strong class="text-amber-400" x-text="visibleSeeds.length"></strong> posisi • Maks <span class="text-white font-bold" x-text="maxAvailableSeeds"></span> dari <span class="text-white font-bold" x-text="activeParticipants.length"></span> peserta
                        </span>
                    </div>
                </div>
                <div class="flex items-center flex-wrap gap-2">
                    <button type="button" 
                            @click="setPresetSeeds(4)" 
                            class="px-3 py-1.5 rounded-xl text-xs font-bold transition cursor-pointer"
                            :class="visibleSeeds.length === 4 ? 'bg-amber-500 text-slate-950 font-black shadow-sm' : 'bg-slate-800 text-slate-300 hover:bg-slate-700'">
                        4 Seed
                    </button>
                    <button type="button" 
                            @click="setPresetSeeds(8)" 
                            :disabled="activeParticipants.length < 8" 
                            class="px-3 py-1.5 rounded-xl text-xs font-bold transition cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed"
                            :class="visibleSeeds.length === 8 ? 'bg-amber-500 text-slate-950 font-black shadow-sm' : 'bg-slate-800 text-slate-300 hover:bg-slate-700'"
                            :title="activeParticipants.length < 8 ? 'Minimal 8 peserta untuk 8 seed' : 'Tampilkan 8 Seed'">
                        8 Seed
                    </button>
                    <button type="button" 
                            x-show="visibleSeeds.length < maxAvailableSeeds" 
                            @click="addNextSeed()" 
                            class="px-3 py-1.5 rounded-xl bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-300 border border-emerald-500/40 text-xs font-bold transition flex items-center gap-1.5 cursor-pointer shadow-sm">
                        <i data-lucide="plus-circle" class="w-3.5 h-3.5"></i>
                        <span x-text="'+ Tambah Seed ' + getNextSeedNumber()"></span>
                    </button>
                </div>
            </div>

            <!-- Seed Selector Cards Grid -->
            <div class="flex-1 overflow-y-auto space-y-3 pr-1 max-h-[420px]">
                
                <template x-for="seedNum in visibleSeeds" :key="'seed-card-' + seedNum">
                    <div class="p-4 rounded-2xl bg-slate-950/70 border-2 transition"
                         :class="selectedSeeds[seedNum] ? 'border-amber-500/60 bg-amber-500/10' : 'border-slate-800'">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <span class="px-2.5 py-0.5 rounded-lg text-xs font-black uppercase tracking-wider flex items-center gap-1 shadow-sm"
                                      :class="getSeedInfo(seedNum).badgeColor">
                                    <span x-text="'⭐ SEED ' + seedNum"></span>
                                    <span class="opacity-80" x-text="'• Slot #' + getSeedInfo(seedNum).slot"></span>
                                </span>
                                <span class="text-[11px] text-amber-400 font-bold" x-text="getSeedInfo(seedNum).desc"></span>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" 
                                        x-show="selectedSeeds[seedNum]" 
                                        @click="setSeed(seedNum, '')" 
                                        class="text-[10px] text-rose-400 hover:text-rose-300 font-bold cursor-pointer">
                                    Kosongkan
                                </button>
                                <button type="button" 
                                        x-show="seedNum > 4" 
                                        @click="removeSeedSlot(seedNum)" 
                                        class="text-[10px] text-slate-500 hover:text-rose-400 font-bold cursor-pointer ml-1.5 px-2 py-0.5 rounded bg-slate-900 border border-slate-800 hover:border-rose-500/40">
                                    ✕ Hapus Slot
                                </button>
                            </div>
                        </div>
                        <label class="block text-[11px] text-slate-400 mb-1.5 font-medium" x-text="'Pilih Peserta untuk Seed ' + seedNum + ':'"></label>
                        <select :value="selectedSeeds[seedNum]" 
                                @change="setSeed(seedNum, $event.target.value)" 
                                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border text-xs font-bold outline-none transition cursor-pointer"
                                :class="selectedSeeds[seedNum] ? 'border-amber-400 text-amber-300' : 'border-slate-700 text-slate-300'">
                            <option value="">— Belum Ditentukan (Tidak Ada Seed Ini) —</option>
                            <template x-for="p in activeParticipants" :key="'s' + seedNum + '-' + p.id">
                                <option :value="p.id" 
                                        :selected="selectedSeeds[seedNum] == p.id"
                                        x-text="p.name + ' (' + p.institution + ')'"></option>
                            </template>
                        </select>
                    </div>
                </template>

                <div x-show="activeParticipants.length === 0" class="py-8 text-center text-xs text-slate-500">
                    Tidak ada peserta terdaftar dalam kategori ini.
                </div>

            </div>

            <!-- Modal Footer -->
            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-800">
                <button type="button" 
                        @click="isSeededModalOpen = false" 
                        :disabled="isSavingSeeded"
                        class="px-4 py-2 rounded-xl text-xs font-bold text-slate-400 hover:text-white hover:bg-slate-800 transition cursor-pointer">
                    Batal
                </button>
                <button type="button" 
                        @click="saveSeededPlayers()" 
                        :disabled="isSavingSeeded"
                        class="px-5 py-2 rounded-xl bg-gradient-to-r from-amber-400 to-amber-500 hover:from-amber-300 hover:to-amber-400 text-slate-950 font-black text-xs shadow-lg shadow-amber-500/25 disabled:opacity-50 disabled:cursor-not-allowed transition flex items-center gap-2 cursor-pointer">
                    <span x-show="isSavingSeeded" class="w-3.5 h-3.5 border-2 border-slate-950 border-t-transparent rounded-full animate-spin"></span>
                    <span x-text="isSavingSeeded ? 'Menyimpan...' : 'Simpan Pengaturan Unggulan'"></span>
                </button>
            </div>

        </div>
    </div>

</div>

@push('scripts')
<script>
    function spinWheelApp() {
        return {
            competitionId: {{ $competition->id }},
            pools: @json($pools),
            activePoolKey: '{{ $pools[0]['key'] ?? 'all' }}',
            drawnTab: 'pool',
            selectedParticipantId: null,
            isSpinning: false,
            wonDrawNumber: null,
            wonParticipantName: '',
            wonParticipantSchool: '',
            bwfNotification: '',
            audioCtx: null,
            theme: '{{ (str_contains(strtolower($competition->name), 'bulu tangkis') || str_contains(strtolower($competition->name), 'badminton')) ? 'badminton' : 'standard' }}',
            
            hasTeammatesInPool(participant) {
                if (!participant || !participant.institution) return false;
                const school = participant.institution.trim().toLowerCase();
                return this.activeParticipants.filter(p => p.id !== participant.id && (p.institution || '').trim().toLowerCase() === school).length > 0;
            },
            
            // Seeded state
            isSeededModalOpen: false,
            visibleSeeds: [1, 2, 3, 4],
            selectedSeeds: { 1: '', 2: '', 3: '', 4: '', 5: '', 6: '', 7: '', 8: '' },
            isSavingSeeded: false,

            get maxAvailableSeeds() {
                const total = this.activeParticipants.length;
                if (total <= 2) return Math.max(total, 1);
                if (total < 8) return 4;
                return Math.min(8, total);
            },

            // Canvas variables
            canvas: null,
            ctx: null,
            wheelSlots: [],
            startAngle: 0,
            arc: 0,
            spinTime: 0,
            spinTimeTotal: 0,
            spinAngleStart: 0,

            get activePool() {
                return this.pools.find(p => p.key === this.activePoolKey) || this.pools[0];
            },

            get activeParticipants() {
                return this.activePool ? this.activePool.participants : [];
            },

            get activeUndrawnParticipants() {
                return this.activeParticipants.filter(p => !p.is_drawn);
            },

            get activeDrawnParticipants() {
                return this.activeParticipants.filter(p => p.is_drawn).sort((a, b) => parseInt(a.draw_number) - parseInt(b.draw_number));
            },

            get allDrawnParticipants() {
                return this.pools.flatMap(p => p.participants).filter(p => p.is_drawn).sort((a, b) => parseInt(a.draw_number) - parseInt(b.draw_number));
            },

            get allUndrawnParticipants() {
                return this.pools.flatMap(p => p.participants).filter(p => !p.is_drawn);
            },

            getPoolStats(key) {
                const p = this.pools.find(item => item.key === key);
                if (!p) return { total: 0, drawn: 0, undrawn: 0 };
                const drawn = p.participants.filter(item => item.is_drawn).length;
                const total = p.participants.length;
                return {
                    total: total,
                    drawn: drawn,
                    undrawn: total - drawn
                };
            },

            init() {
                if (this.activeUndrawnParticipants.length > 0) {
                    this.selectedParticipantId = this.activeUndrawnParticipants[0].id;
                }

                this.canvas = document.getElementById("wheelCanvas");
                if (this.canvas) {
                    this.ctx = this.canvas.getContext("2d");
                    this.calculateAvailableSlots();
                    this.drawWheel();
                }
            },

            switchPool(key) {
                if (this.isSpinning) return;
                this.activePoolKey = key;
                this.wonDrawNumber = null;
                if (this.activeUndrawnParticipants.length > 0) {
                    this.selectedParticipantId = this.activeUndrawnParticipants[0].id;
                } else {
                    this.selectedParticipantId = null;
                }
                this.calculateAvailableSlots();
                this.drawWheel();
            },

            setTheme(t) {
                this.theme = t;
                this.drawWheel();
            },

            calculateAvailableSlots() {
                if (!this.activePool) return;
                const poolTotal = this.activePool.participants.length;
                const assignedNumbers = this.activeDrawnParticipants.map(d => parseInt(d.draw_number));
                
                let slots = [];
                for(let i = 1; i <= Math.max(poolTotal, 1); i++) {
                    if (!assignedNumbers.includes(i)) {
                        slots.push(i);
                    }
                }

                this.wheelSlots = slots;
                if (this.wheelSlots.length > 0) {
                    this.arc = (Math.PI * 2) / this.wheelSlots.length;
                } else {
                    this.arc = 0;
                }
            },

            resetActivePool() {
                if (!this.activePool || this.activeDrawnParticipants.length === 0) return;
                if (!confirm('Apakah Anda yakin ingin me-reset nomor undian KHUSUS untuk kategori ' + this.activePool.title + '?')) {
                    return;
                }

                const regIds = this.activePool.participants.map(p => p.id);
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("pic.spin.wheel.reset", $competition->id) }}';
                
                const csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '_token';
                csrf.value = '{{ csrf_token() }}';
                form.appendChild(csrf);

                const idsInput = document.createElement('input');
                idsInput.type = 'hidden';
                idsInput.name = 'registration_ids';
                idsInput.value = regIds.join(',');
                form.appendChild(idsInput);

                document.body.appendChild(form);
                form.submit();
            },

            drawShuttlecock(ctx, x, y, scale = 1, angle = 0) {
                ctx.save();
                ctx.translate(x, y);
                ctx.rotate(angle);
                ctx.scale(scale, scale);

                // Shuttlecock white feathers fan
                ctx.fillStyle = "#ffffff";
                ctx.strokeStyle = "#94a3b8";
                ctx.lineWidth = 0.8;

                ctx.beginPath();
                ctx.moveTo(-11, -19);
                ctx.lineTo(11, -19);
                ctx.lineTo(6, 2);
                ctx.lineTo(-6, 2);
                ctx.closePath();
                ctx.fill();
                ctx.stroke();

                // Feather lines / ribs
                ctx.beginPath();
                ctx.moveTo(-7, -19); ctx.lineTo(-3, 2);
                ctx.moveTo(-2, -19); ctx.lineTo(-1, 2);
                ctx.moveTo(2, -19); ctx.lineTo(1, 2);
                ctx.moveTo(7, -19); ctx.lineTo(3, 2);
                ctx.strokeStyle = "#cbd5e1";
                ctx.lineWidth = 0.8;
                ctx.stroke();

                // Dark green band
                ctx.fillStyle = "#064e3b";
                ctx.fillRect(-6.5, -2, 13, 3.5);

                // Cork Head (Rounded Dome)
                ctx.fillStyle = "#f8fafc";
                ctx.strokeStyle = "#64748b";
                ctx.lineWidth = 1;
                ctx.beginPath();
                ctx.arc(0, 5.5, 6, 0, Math.PI, false);
                ctx.closePath();
                ctx.fill();
                ctx.stroke();

                ctx.restore();
            },

            drawWheel() {
                if (!this.ctx || !this.canvas) return;
                const outsideRadius = 200;
                const insideRadius = this.theme === 'badminton' ? 56 : 50;

                this.ctx.clearRect(0, 0, 440, 440);

                if (this.wheelSlots.length === 0) {
                    this.ctx.fillStyle = "#1e293b";
                    this.ctx.beginPath();
                    this.ctx.arc(220, 220, outsideRadius, 0, Math.PI * 2);
                    this.ctx.fill();
                    this.ctx.strokeStyle = this.theme === 'badminton' ? "#10b981" : "#0f172a";
                    this.ctx.lineWidth = 4;
                    this.ctx.stroke();

                    this.ctx.fillStyle = this.theme === 'badminton' ? "#34d399" : "#10b981";
                    this.ctx.font = "bold 16px 'Plus Jakarta Sans', sans-serif";
                    const completeText = "SELESAI DIUNDI";
                    this.ctx.fillText(completeText, 220 - this.ctx.measureText(completeText).width / 2, 225);
                    return;
                }

                if (this.theme === 'badminton') {
                    // BADMINTON EDITION PALETTE (Tournament Green, Gold, Royal Blue, Crimson Red)
                    const badmintonColors = ["#059669", "#f59e0b", "#2563eb", "#dc2626"];

                    // Outer Metallic Green Rim
                    this.ctx.save();
                    this.ctx.beginPath();
                    this.ctx.arc(220, 220, outsideRadius + 14, 0, Math.PI * 2);
                    this.ctx.fillStyle = "#14532d";
                    this.ctx.fill();
                    this.ctx.strokeStyle = "#f59e0b";
                    this.ctx.lineWidth = 4;
                    this.ctx.stroke();
                    this.ctx.restore();

                    // Draw Sectors
                    for (let i = 0; i < this.wheelSlots.length; i++) {
                        const angle = this.startAngle + i * this.arc;
                        this.ctx.fillStyle = badmintonColors[i % badmintonColors.length];
                        this.ctx.strokeStyle = "#fef08a";
                        this.ctx.lineWidth = 2.5;

                        this.ctx.beginPath();
                        this.ctx.arc(220, 220, outsideRadius, angle, angle + this.arc, false);
                        this.ctx.arc(220, 220, insideRadius, angle + this.arc, angle, true);
                        this.ctx.closePath();
                        this.ctx.fill();
                        this.ctx.stroke();

                        // Draw Shuttlecock on Sector (Radius ~100)
                        const midAngle = angle + this.arc / 2;
                        const shuttleRadius = 100;
                        const sx = 220 + Math.cos(midAngle) * shuttleRadius;
                        const sy = 220 + Math.sin(midAngle) * shuttleRadius;
                        this.drawShuttlecock(this.ctx, sx, sy, 0.9, midAngle + Math.PI / 2);

                        // Draw Number Text (Radius ~155)
                        this.ctx.save();
                        this.ctx.fillStyle = "#ffffff";
                        this.ctx.font = "900 19px 'Space Grotesk', sans-serif";
                        this.ctx.shadowColor = "rgba(0, 0, 0, 0.75)";
                        this.ctx.shadowBlur = 6;
                        this.ctx.translate(220 + Math.cos(midAngle) * 155, 220 + Math.sin(midAngle) * 155);
                        this.ctx.rotate(midAngle + Math.PI / 2);
                        const text = "No. " + this.wheelSlots[i];
                        this.ctx.fillText(text, -this.ctx.measureText(text).width / 2, 0);
                        this.ctx.restore();
                    }

                    // Draw 24 Golden Studs / Rivets on Outer Rim
                    for (let r = 0; r < 24; r++) {
                        const rivetAngle = (r * Math.PI * 2) / 24;
                        const rx = 220 + Math.cos(rivetAngle) * (outsideRadius + 7);
                        const ry = 220 + Math.sin(rivetAngle) * (outsideRadius + 7);
                        this.ctx.fillStyle = "#fef08a";
                        this.ctx.beginPath();
                        this.ctx.arc(rx, ry, 3.5, 0, Math.PI * 2);
                        this.ctx.fill();
                        this.ctx.strokeStyle = "#92400e";
                        this.ctx.lineWidth = 1;
                        this.ctx.stroke();
                    }

                    // Draw Center Medal ("SPIN UNDIAN" Gold Coin)
                    this.ctx.save();
                    const grad = this.ctx.createRadialGradient(220, 220, 5, 220, 220, insideRadius);
                    grad.addColorStop(0, "#fef9c3");
                    grad.addColorStop(0.35, "#fde047");
                    grad.addColorStop(0.7, "#eab308");
                    grad.addColorStop(1, "#a16207");
                    
                    this.ctx.fillStyle = grad;
                    this.ctx.beginPath();
                    this.ctx.arc(220, 220, insideRadius, 0, Math.PI * 2);
                    this.ctx.fill();
                    this.ctx.strokeStyle = "#713f12";
                    this.ctx.lineWidth = 4;
                    this.ctx.stroke();

                    // Inner decorative circle
                    this.ctx.beginPath();
                    this.ctx.arc(220, 220, insideRadius - 5, 0, Math.PI * 2);
                    this.ctx.strokeStyle = "#ca8a04";
                    this.ctx.lineWidth = 1.5;
                    this.ctx.stroke();

                    // Text: SPIN
                    this.ctx.fillStyle = "#1e293b";
                    this.ctx.font = "900 15px 'Plus Jakarta Sans', sans-serif";
                    this.ctx.shadowColor = "rgba(255, 255, 255, 0.4)";
                    this.ctx.shadowBlur = 2;
                    const spinTxt = "SPIN";
                    this.ctx.fillText(spinTxt, 220 - this.ctx.measureText(spinTxt).width / 2, 212);

                    // Text: UNDIAN
                    this.ctx.font = "900 12px 'Plus Jakarta Sans', sans-serif";
                    const undianTxt = "UNDIAN";
                    this.ctx.fillText(undianTxt, 220 - this.ctx.measureText(undianTxt).width / 2, 229);

                    // Underline arch beneath UNDIAN
                    this.ctx.beginPath();
                    this.ctx.arc(220, 233, 20, 0.15 * Math.PI, 0.85 * Math.PI, false);
                    this.ctx.strokeStyle = "#1e293b";
                    this.ctx.lineWidth = 2.5;
                    this.ctx.stroke();
                    this.ctx.restore();

                } else {
                    // STANDARD THEME (Clean Dark Slate & Gold)
                    const colors = ["#10b981", "#3b82f6", "#f59e0b", "#ec4899", "#8b5cf6", "#06b6d4", "#14b8a6", "#f97316"];
                    const textRadius = 140;
                    this.ctx.strokeStyle = "#0f172a";
                    this.ctx.lineWidth = 4;

                    for (let i = 0; i < this.wheelSlots.length; i++) {
                        const angle = this.startAngle + i * this.arc;
                        this.ctx.fillStyle = colors[i % colors.length];

                        this.ctx.beginPath();
                        this.ctx.arc(220, 220, outsideRadius, angle, angle + this.arc, false);
                        this.ctx.arc(220, 220, insideRadius, angle + this.arc, angle, true);
                        this.ctx.stroke();
                        this.ctx.fill();

                        this.ctx.save();
                        this.ctx.fillStyle = "#ffffff";
                        this.ctx.font = "bold 20px 'Space Mono', sans-serif";
                        this.ctx.translate(220 + Math.cos(angle + this.arc / 2) * textRadius, 220 + Math.sin(angle + this.arc / 2) * textRadius);
                        this.ctx.rotate(angle + this.arc / 2 + Math.PI / 2);
                        const text = "No. " + this.wheelSlots[i];
                        this.ctx.fillText(text, -this.ctx.measureText(text).width / 2, 0);
                        this.ctx.restore();
                    }

                    // Draw Center Circle
                    this.ctx.fillStyle = "#0f172a";
                    this.ctx.beginPath();
                    this.ctx.arc(220, 220, insideRadius, 0, Math.PI * 2, true);
                    this.ctx.fill();
                    this.ctx.strokeStyle = "#f59e0b";
                    this.ctx.lineWidth = 5;
                    this.ctx.stroke();

                    this.ctx.fillStyle = "#fbbf24";
                    this.ctx.font = "black 14px 'Plus Jakarta Sans', sans-serif";
                    const centerText = "TALENTA";
                    this.ctx.fillText(centerText, 220 - this.ctx.measureText(centerText).width / 2, 225);
                }
            },

            playClickSound() {
                try {
                    if (!this.audioCtx) this.audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                    const osc = this.audioCtx.createOscillator();
                    const gain = this.audioCtx.createGain();
                    
                    if (this.theme === 'badminton') {
                        // Crisp shuttlecock / string tap
                        osc.type = "sine";
                        osc.frequency.setValueAtTime(750, this.audioCtx.currentTime);
                        gain.gain.setValueAtTime(0.12, this.audioCtx.currentTime);
                        gain.gain.exponentialRampToValueAtTime(0.001, this.audioCtx.currentTime + 0.035);
                    } else {
                        osc.type = "triangle";
                        osc.frequency.setValueAtTime(440, this.audioCtx.currentTime);
                        gain.gain.setValueAtTime(0.08, this.audioCtx.currentTime);
                        gain.gain.exponentialRampToValueAtTime(0.001, this.audioCtx.currentTime + 0.05);
                    }

                    osc.connect(gain);
                    gain.connect(this.audioCtx.destination);
                    osc.start();
                    osc.stop(this.audioCtx.currentTime + (this.theme === 'badminton' ? 0.035 : 0.05));
                } catch(e) {}
            },

            playSmashSound() {
                try {
                    if (!this.audioCtx) this.audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                    const osc = this.audioCtx.createOscillator();
                    const gain = this.audioCtx.createGain();
                    osc.type = "sine";
                    osc.frequency.setValueAtTime(880, this.audioCtx.currentTime);
                    osc.frequency.exponentialRampToValueAtTime(140, this.audioCtx.currentTime + 0.15);
                    gain.gain.setValueAtTime(0.28, this.audioCtx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.001, this.audioCtx.currentTime + 0.15);
                    osc.connect(gain);
                    gain.connect(this.audioCtx.destination);
                    osc.start();
                    osc.stop(this.audioCtx.currentTime + 0.15);
                } catch(e) {}
            },

            spin() {
                if (this.isSpinning || this.activeUndrawnParticipants.length === 0 || this.wheelSlots.length === 0) return;
                this.isSpinning = true;
                this.wonDrawNumber = null;
                this.bwfNotification = '';

                this.spinAngleStart = Math.random() * 10 + 20;
                this.spinTime = 0;
                this.spinTimeTotal = Math.random() * 2500 + 3500;
                this.rotateWheel();
            },

            rotateWheel() {
                this.spinTime += 30;
                if (this.spinTime >= this.spinTimeTotal) {
                    this.stopRotateWheel();
                    return;
                }
                const spinAngle = this.spinAngleStart - this.easeOut(this.spinTime, 0, this.spinAngleStart, this.spinTimeTotal);
                this.startAngle += (spinAngle * Math.PI / 180);
                this.drawWheel();
                this.playClickSound();
                requestAnimationFrame(() => this.rotateWheel());
            },

            stopRotateWheel() {
                if (this.wheelSlots.length === 0) return;

                // Pointer is at the top (12 o'clock = 270 deg = 3 * PI / 2)
                const currentAngle = (this.startAngle % (Math.PI * 2) + (Math.PI * 2)) % (Math.PI * 2);
                const pointerAngle = (3 * Math.PI / 2 - currentAngle + (Math.PI * 2)) % (Math.PI * 2);
                let index = Math.floor(pointerAngle / this.arc) % this.wheelSlots.length;
                if (isNaN(index) || index < 0 || index >= this.wheelSlots.length) {
                    index = 0;
                }
                const wonNumber = this.wheelSlots[index];

                this.isSpinning = false;
                this.wonDrawNumber = wonNumber;

                // Find participant
                const undrawn = this.activeUndrawnParticipants;
                const participant = undrawn.find(p => p.id == this.selectedParticipantId) || undrawn[0];
                if (!participant) return;

                this.wonParticipantName = participant.name;
                this.wonParticipantSchool = participant.institution;

                // Check BWF Separation Notification
                const school = (participant.institution || '').trim().toLowerCase();
                const sameSchoolTeammates = this.activeParticipants.filter(p => p.id !== participant.id && (p.institution || '').trim().toLowerCase() === school);

                if (sameSchoolTeammates.length > 0) {
                    const alreadyDrawn = sameSchoolTeammates.filter(p => p.is_drawn || p.is_seeded);
                    if (alreadyDrawn.length > 0) {
                        const names = alreadyDrawn.map(p => p.name).join(', ');
                        this.bwfNotification = `Terdeteksi rekan satu delegasi dari ${participant.institution} (${names}) yang telah terundi/seeded sebelumnya. Sesuai aturan resmi BWF GCR 14 (Proteksi Satu Delegasi), peserta ini dialokasikan ke sisi bagan yang berseberangan agar tidak saling berhadapan di Babak 1.`;
                    } else {
                        this.bwfNotification = `Peserta dari ${participant.institution} memiliki rekan satu delegasi dalam kategori ini. Proteksi BWF GCR 14 aktif untuk memastikan mereka dipisahkan pool dan tidak bertemu di Babak 1.`;
                    }
                } else {
                    this.bwfNotification = '';
                }

                // Trigger Confetti Celebration & Sound
                if (typeof confetti === 'function') {
                    confetti({
                        particleCount: 120,
                        spread: 80,
                        origin: { y: 0.6 }
                    });
                }

                if (this.theme === 'badminton') {
                    this.playSmashSound();
                }

                // IMMEDIATELY UPDATE PARTICIPANT STATE DIRECTLY
                participant.is_drawn = true;
                participant.draw_number = wonNumber;

                if (this.activeUndrawnParticipants.length > 0) {
                    this.selectedParticipantId = this.activeUndrawnParticipants[0].id;
                } else {
                    this.selectedParticipantId = null;
                }

                this.calculateAvailableSlots();
                this.drawWheel();

                // Auto save draw number to backend
                this.saveDrawResult(participant.id, wonNumber);
            },

            saveDrawResult(participantId, drawNumber) {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
                
                fetch('/pic/lomba/' + this.competitionId + '/spin-wheel/save', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        registration_id: participantId,
                        draw_number: drawNumber
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (!data.success) {
                        console.error('Server save error:', data);
                    }
                })
                .catch(err => {
                    console.error('Save draw network error:', err);
                });
            },

            openSeededModal() {
                this.selectedSeeds = { 1: '', 2: '', 3: '', 4: '', 5: '', 6: '', 7: '', 8: '' };
                const foundSeeds = [];

                this.activeParticipants.forEach(p => {
                    if (p.seed_number) {
                        const s = parseInt(p.seed_number);
                        this.selectedSeeds[s] = String(p.id);
                        foundSeeds.push(s);
                    }
                });

                // Default show 4 seeds, or expand if existing seeds > 4 are found
                const maxFound = foundSeeds.length > 0 ? Math.max(...foundSeeds) : 4;
                const targetCount = Math.max(4, Math.min(maxFound, this.maxAvailableSeeds));
                
                this.visibleSeeds = [];
                for (let i = 1; i <= targetCount; i++) {
                    this.visibleSeeds.push(i);
                }

                this.isSeededModalOpen = true;
                this.$nextTick(() => {
                    if (window.lucide) lucide.createIcons();
                });
            },

            setSeed(seedNum, participantId) {
                participantId = participantId ? String(participantId) : '';

                // If this participant was already selected for another seed, auto-clear from that seed!
                if (participantId !== '') {
                    Object.keys(this.selectedSeeds).forEach(s => {
                        if (parseInt(s) !== parseInt(seedNum) && this.selectedSeeds[s] === participantId) {
                            this.selectedSeeds[s] = '';
                        }
                    });
                }

                this.selectedSeeds[seedNum] = participantId;
            },

            setPresetSeeds(count) {
                const target = Math.min(count, this.maxAvailableSeeds);
                const newVisible = [];
                for (let i = 1; i <= target; i++) {
                    newVisible.push(i);
                }
                // Clear any seeds beyond the preset target
                Object.keys(this.selectedSeeds).forEach(s => {
                    if (parseInt(s) > target) {
                        this.selectedSeeds[s] = '';
                    }
                });
                this.visibleSeeds = newVisible;
                this.$nextTick(() => {
                    if (window.lucide) lucide.createIcons();
                });
            },

            addNextSeed() {
                const next = this.getNextSeedNumber();
                if (next && !this.visibleSeeds.includes(next)) {
                    this.visibleSeeds.push(next);
                    this.visibleSeeds.sort((a, b) => a - b);
                    this.$nextTick(() => {
                        if (window.lucide) lucide.createIcons();
                    });
                }
            },

            removeSeedSlot(seedNum) {
                this.setSeed(seedNum, '');
                this.visibleSeeds = this.visibleSeeds.filter(s => s !== seedNum);
                this.$nextTick(() => {
                    if (window.lucide) lucide.createIcons();
                });
            },

            getNextSeedNumber() {
                for (let i = 1; i <= this.maxAvailableSeeds; i++) {
                    if (!this.visibleSeeds.includes(i)) return i;
                }
                return null;
            },

            getSeedInfo(seedNum) {
                const slot = this.calculateSlotPreview(seedNum);
                let desc = '';
                let badgeColor = 'bg-amber-500/20 text-amber-300 border border-amber-500/40';

                if (seedNum === 1) {
                    desc = 'Puncak Bagan Atas (QF 1)';
                    badgeColor = 'bg-amber-500 text-slate-950 font-black';
                } else if (seedNum === 2) {
                    desc = 'Dasar Bagan Bawah (QF 4)';
                    badgeColor = 'bg-amber-500/25 text-amber-300 border border-amber-500/50';
                } else if (seedNum === 3) {
                    desc = 'Bagan Bawah (QF 3)';
                } else if (seedNum === 4) {
                    desc = 'Bagan Atas (QF 2)';
                } else if (seedNum === 5) {
                    desc = 'Bagan Atas (QF 2)';
                } else if (seedNum === 6) {
                    desc = 'Bagan Bawah (QF 3)';
                } else if (seedNum === 7) {
                    desc = 'Bagan Bawah (QF 4)';
                } else if (seedNum === 8) {
                    desc = 'Bagan Atas (QF 1)';
                } else {
                    desc = 'Slot Bagan Turnamen';
                }

                return { slot, desc, badgeColor };
            },

            calculateSlotPreview(seed) {
                const total = this.activeParticipants.length;
                if (total <= 1) return 1;

                let baseSlot = 1;
                switch (seed) {
                    case 1: baseSlot = 1; break;
                    case 2: baseSlot = total; break;
                    case 3: baseSlot = Math.floor(total / 2) + 1; break;
                    case 4: baseSlot = Math.floor(total / 2); break;
                    case 5: baseSlot = Math.floor(total / 4) + 1; break;
                    case 6: baseSlot = Math.floor(3 * total / 4); break;
                    case 7: baseSlot = Math.floor(3 * total / 4) + 1; break;
                    case 8: baseSlot = Math.floor(total / 4); break;
                    default: baseSlot = Math.min(seed, total); break;
                }
                baseSlot = Math.max(1, Math.min(total, baseSlot));

                const used = [];
                const sortedSeeds = [...this.visibleSeeds].sort((a, b) => a - b);
                for (const s of sortedSeeds) {
                    if (s === seed) break;
                    let sSlot = 1;
                    switch (s) {
                        case 1: sSlot = 1; break;
                        case 2: sSlot = total; break;
                        case 3: sSlot = Math.floor(total / 2) + 1; break;
                        case 4: sSlot = Math.floor(total / 2); break;
                        case 5: sSlot = Math.floor(total / 4) + 1; break;
                        case 6: sSlot = Math.floor(3 * total / 4); break;
                        case 7: sSlot = Math.floor(3 * total / 4) + 1; break;
                        case 8: sSlot = Math.floor(total / 4); break;
                        default: sSlot = Math.min(s, total); break;
                    }
                    sSlot = Math.max(1, Math.min(total, sSlot));
                    if (used.includes(sSlot)) {
                        for (let offset = 1; offset < total; offset++) {
                            if (sSlot + offset <= total && !used.includes(sSlot + offset)) { sSlot += offset; break; }
                            if (sSlot - offset >= 1 && !used.includes(sSlot - offset)) { sSlot -= offset; break; }
                        }
                    }
                    used.push(sSlot);
                }

                if (used.includes(baseSlot)) {
                    for (let offset = 1; offset < total; offset++) {
                        if (baseSlot + offset <= total && !used.includes(baseSlot + offset)) return baseSlot + offset;
                        if (baseSlot - offset >= 1 && !used.includes(baseSlot - offset)) return baseSlot - offset;
                    }
                }
                return baseSlot;
            },

            async saveSeededPlayers() {
                const seeds = {};
                this.visibleSeeds.forEach(seedNum => {
                    const pId = this.selectedSeeds[seedNum];
                    if (pId) {
                        seeds[pId] = parseInt(seedNum);
                    }
                });

                this.isSavingSeeded = true;
                try {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
                    const response = await fetch('/pic/lomba/' + this.competitionId + '/set-seeded', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                            pool_key: this.activePoolKey,
                            seeds: seeds
                        })
                    });

                    const data = await response.json();
                    if (data.success) {
                        this.isSeededModalOpen = false;
                        alert(data.message || 'Pengaturan pemain unggulan berhasil disimpan!');
                        window.location.reload();
                    } else {
                        alert(data.message || 'Gagal menyimpan pengaturan unggulan.');
                    }
                } catch (err) {
                    console.error('Save seeded error:', err);
                    alert('Terjadi kesalahan koneksi saat menyimpan pengaturan unggulan.');
                } finally {
                    this.isSavingSeeded = false;
                }
            },

            easeOut(t, b, c, d) {
                const ts = (t /= d) * t;
                const tc = ts * t;
                return b + c * (tc + -3 * ts + 3 * t);
            }
        }
    }
</script>
@endpush
@endsection
