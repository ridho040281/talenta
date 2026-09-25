@extends('layouts.admin')

@section('title', 'Spin Wheel Undian Nomor Tampil - ' . $competition->name)
@section('page_title', 'Interactive Spin Wheel Undian')

@section('content')
<div class="space-y-6 font-sans" x-data="spinWheelApp()">

    <!-- Tournament 5-Step Workflow Stepper (Hanya untuk Cabang Turnamen: Bulu Tangkis & Tenis Meja) -->
    @if($competition->isTournamentBracket())
        @include('partials.tournament-stepper', [
            'competition' => $competition,
            'activeStep' => 'undian',
            'activePoolKey' => request('pool', $pools[0]['key'] ?? ''),
            'pools' => $pools
        ])
    @endif
    


    <!-- Category & Sector Navigation Bar (Option 2: 1 Baris Ramping Terpadu) -->
    @if(count($pools) > 1)
    @php
        $classGroups = collect($pools)->groupBy('class_key');
    @endphp
    <div class="bg-slate-900/95 backdrop-blur-md rounded-2xl px-4 py-2.5 sm:px-5 border border-slate-800 shadow-xl flex flex-wrap items-center justify-between gap-3 relative z-10">
        
        <!-- Left: Dropdown Kategori Kelas + Toggle Sektor PA / PI -->
        <div class="flex flex-wrap items-center gap-2.5 sm:gap-3.5">
            
            @if($classGroups->count() > 1)
            <!-- Dropdown Kategori Kelas -->
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-lg bg-amber-500/15 border border-amber-500/30 flex items-center justify-center shrink-0 text-amber-400">
                    <i data-lucide="layers" class="w-3.5 h-3.5"></i>
                </div>
                <label class="text-[11px] font-mono font-bold uppercase tracking-wider text-amber-400 shrink-0 hidden sm:inline">
                    Kategori:
                </label>
                <select x-model="activeClassKey" 
                        @change="switchClass($event.target.value)"
                        :disabled="isSpinning || isDecoding"
                        class="bg-slate-950 border border-slate-700/80 hover:border-amber-500/60 text-amber-300 font-bold text-xs rounded-xl px-3 py-2 outline-none focus:ring-2 focus:ring-amber-500/40 cursor-pointer shadow-inner disabled:opacity-50 transition">
                    @foreach($classGroups as $cKey => $cPools)
                        @php
                            $firstP = $cPools->first();
                            $cLabel = $firstP['class_label'] ?? $firstP['title'];
                            $cTotal = $cPools->sum(fn($p) => count($p['participants']));
                        @endphp
                        <option value="{{ $cKey }}" class="bg-slate-900 text-white font-semibold">
                            {{ $cLabel }} ({{ $cTotal }} Peserta)
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Divider Vertical -->
            <div class="hidden sm:block h-6 w-px bg-slate-800"></div>
            @endif

            <!-- Segmented Sektor Toggle Buttons (PA vs PI) -->
            <div class="flex items-center gap-2">
                <span class="text-[11px] font-mono font-bold uppercase tracking-wider text-slate-400 shrink-0 hidden md:inline">
                    Sektor:
                </span>
                <div class="inline-flex p-1 bg-slate-950 rounded-xl border border-slate-800/90 shadow-inner gap-1">
                    <template x-for="sec in availableSectors" :key="sec.key">
                        <button type="button" 
                                @click="switchSector(sec.key)"
                                :disabled="isSpinning || isDecoding || !sec.exists || sec.count === 0"
                                class="px-3.5 py-1.5 rounded-lg font-black text-xs transition-all duration-200 flex items-center gap-2 cursor-pointer disabled:opacity-30 disabled:cursor-not-allowed"
                                :class="activeSector === sec.key 
                                    ? (sec.key === 'PA' 
                                        ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-md shadow-blue-500/30 border border-blue-400/60 ring-1 ring-blue-400/40' 
                                        : (sec.key === 'PI' 
                                            ? 'bg-gradient-to-r from-rose-600 to-pink-600 text-white shadow-md shadow-rose-500/30 border border-rose-400/60 ring-1 ring-rose-400/40' 
                                            : 'bg-gradient-to-r from-amber-600 to-orange-600 text-white shadow-md shadow-amber-500/30 border border-amber-400/60 ring-1 ring-amber-400/40')) 
                                    : 'text-slate-400 hover:text-white hover:bg-slate-900 border border-transparent'">
                            <span x-text="sec.icon" class="text-sm"></span>
                            <span x-text="sec.label"></span>
                            <span class="px-1.5 py-0.5 rounded text-[10px] font-mono font-black"
                                  :class="activeSector === sec.key ? 'bg-black/35 text-white' : 'bg-slate-900 text-slate-300 border border-slate-800'"
                                  x-text="sec.count + ' Peserta'"></span>
                            <span x-show="sec.pool && getPoolStats(sec.pool.key).undrawn === 0" class="text-emerald-400 text-xs font-black" title="Selesai Diundi">✓</span>
                        </button>
                    </template>
                </div>
            </div>
        </div>

        <!-- Right: Status Undian, Auto Draw, & Reset Kategori -->
        <div class="flex items-center gap-2.5 shrink-0 ml-auto">
            <div class="text-right hidden sm:block">
                <span class="text-[10px] text-slate-500 font-mono block leading-none mb-0.5">Status Undian:</span>
                <span class="text-xs font-mono font-bold" 
                      :class="getPoolStats(activePoolKey).undrawn === 0 ? 'text-emerald-400' : (getPoolStats(activePoolKey).drawn === 0 ? 'text-slate-400' : 'text-amber-400')"
                      x-text="getPoolStats(activePoolKey).drawn === 0 
                          ? ('Belum Diundi • ' + getPoolStats(activePoolKey).undrawn + ' Peserta') 
                          : (getPoolStats(activePoolKey).undrawn === 0 
                              ? ('Selesai Diundi (' + getPoolStats(activePoolKey).drawn + ')') 
                              : (getPoolStats(activePoolKey).drawn + ' Terundi • ' + getPoolStats(activePoolKey).undrawn + ' Sisa'))"></span>
            </div>

            <!-- Fast Auto Draw Button -->
            <button type="button" 
                    @click="openBatchModal()" 
                    :disabled="isSpinning || activeUndrawnParticipants.length === 0"
                    class="px-3.5 py-1.5 rounded-xl bg-gradient-to-r from-emerald-500/20 to-teal-500/20 hover:from-emerald-500/30 hover:to-teal-500/30 text-emerald-300 border border-emerald-500/40 text-xs font-bold disabled:opacity-30 disabled:cursor-not-allowed transition cursor-pointer shrink-0 flex items-center gap-1.5 shadow-sm"
                    title="Undi Cepat Semua Peserta di Kategori / Pool Aktif">
                <i data-lucide="zap" class="w-3.5 h-3.5 text-emerald-400"></i>
                <span>⚡ Auto Draw</span>
            </button>

            <!-- Selesai Undi / Selesai Terkunci Badge (Menggantikan Reset Kategori Sesuai Arahan User) -->
            <div class="flex items-center gap-1.5 shrink-0">
                <span class="px-3 py-1.5 rounded-xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 font-mono font-bold text-xs flex items-center gap-1.5 whitespace-nowrap shadow-inner"
                      title="Jumlah peserta yang sudah selesai diundi & terkunci nomornya">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span x-text="allDrawnParticipants.length + ' Selesai Terkunci'"></span>
                </span>
            </div>
        </div>
    </div>
    @endif

    <!-- Main Workspace Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 relative z-0">
        
        <!-- Left 7 Cols: Interactive Canvas Wheel & Hacker Live Terminal -->
        <div class="lg:col-span-7 space-y-4">
            
            <!-- VIEW 1: Interactive Canvas Wheel -->
            <div x-show="visualMode === 'wheel'"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 class="bg-slate-950 rounded-3xl p-6 sm:p-8 border-2 transition-all duration-500 shadow-2xl flex flex-col items-center justify-center text-center relative overflow-hidden"
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

                <!-- Top Controls Bar: Tema Roda (Kiri) | Kategori Aktif (Tengah) | Mode Hacker (Kanan) -->
                <div class="w-full flex flex-wrap sm:flex-nowrap items-center justify-between gap-2.5 mb-4 relative z-20">
                    <!-- Pojok Kiri Atas: Switcher Tema Roda (Sesuai Panah Merah 1) -->
                    <div class="inline-flex items-center p-1 bg-slate-900/90 backdrop-blur-md rounded-xl border border-slate-800 shadow-md shrink-0">
                        <span class="text-[10px] text-slate-400 font-bold px-2 hidden sm:inline">Tema:</span>
                        <button type="button" @click="setTheme('badminton')" 
                                :class="theme === 'badminton' ? 'bg-emerald-500 text-slate-950 font-black shadow-sm' : 'text-slate-400 hover:text-white'" 
                                class="px-2.5 py-1 rounded-lg text-xs font-bold transition cursor-pointer flex items-center gap-1">
                            <span>🏸</span>
                            <span>Arena</span>
                        </button>
                        <button type="button" @click="setTheme('standard')" 
                                :class="theme === 'standard' ? 'bg-amber-500 text-slate-950 font-black shadow-sm' : 'text-slate-400 hover:text-white'" 
                                class="px-2.5 py-1 rounded-lg text-xs font-bold transition cursor-pointer">
                            Standar
                        </button>
                    </div>

                    <!-- Bagian Tengah: Banner Info Kategori Aktif & Sisa Belum Diundi -->
                    <div class="flex-1 max-w-sm mx-auto bg-slate-900/80 backdrop-blur-md rounded-2xl py-1.5 px-3.5 border border-slate-800 flex items-center justify-between gap-2 text-left shadow-lg order-last sm:order-none w-full sm:w-auto">
                        <div class="flex items-center gap-2 overflow-hidden">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse shrink-0"></span>
                            <h4 class="text-xs font-black text-white truncate" x-text="activePool?.title || '{{ $competition->name }}'"></h4>
                        </div>
                        <span class="text-[10px] font-mono font-bold text-amber-400 shrink-0 bg-amber-500/10 px-2 py-0.5 rounded-full border border-amber-500/20" 
                              x-text="activeUndrawnParticipants.length + ' Belum Diundi'"></span>
                    </div>

                    <!-- Pojok Kanan Atas: Tombol Mode Hacker (In-place Toggle) -->
                    <button type="button" 
                            @click="setVisualMode('hacker')" 
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-cyan-500/10 hover:bg-cyan-500/20 text-cyan-400 border border-cyan-500/30 text-xs font-bold transition cursor-pointer shadow-md shrink-0 font-sans"
                            title="Beralih ke Tampilan Terminal Mode Hacker">
                        <i data-lucide="terminal" class="w-3.5 h-3.5 text-cyan-400"></i>
                        <span class="hidden sm:inline">Mode Hacker</span>
                    </button>
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
                        <button type="button" @click="spin()" :disabled="isSpinning || isBatchRunning || activeUndrawnParticipants.length === 0" class="w-full py-4 px-6 rounded-2xl bg-gradient-to-r from-amber-400 via-amber-500 to-amber-600 hover:from-amber-500 hover:to-amber-700 disabled:opacity-40 disabled:cursor-not-allowed text-slate-950 font-black text-sm sm:text-base tracking-wider uppercase shadow-xl shadow-amber-500/25 hover:scale-[1.01] active:scale-[0.99] transition duration-200 flex items-center justify-center gap-3 cursor-pointer">
                            <i data-lucide="disc" class="w-5 h-5" :class="{ 'animate-spin': isSpinning }"></i>
                            <span x-text="isSpinning ? 'RODA SEDANG BERPUTAR...' : (isBatchRunning ? 'UNDIAN MASSAL BERJALAN...' : (activeUndrawnParticipants.length === 0 ? 'KATEGORI INI SELESAI DIUNDI' : 'PUTAR RODA UNDIAN (1-BY-1)'))"></span>
                        </button>

                        <!-- Batch / Full-Shuffle Auto Draw Quick Trigger Button -->
                        <button type="button" @click="openBatchModal()" :disabled="isSpinning || isBatchRunning || activeUndrawnParticipants.length === 0" class="w-full py-3 px-4 rounded-xl bg-slate-800/80 hover:bg-slate-700/80 border border-emerald-500/30 hover:border-emerald-400 text-emerald-300 font-bold text-xs flex items-center justify-center gap-2 transition cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed shadow-md">
                            <i data-lucide="zap" class="w-4 h-4 text-emerald-400"></i>
                            <span>⚡ Undi Sekaligus: Batch / Full-Shuffle Auto Draw (<span x-text="activeUndrawnParticipants.length"></span> Sisa)</span>
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

            <!-- VIEW 2: Hacker Live Decoder Terminal -->
            <div x-show="visualMode === 'hacker'" 
                 x-cloak
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 class="relative bg-slate-950 rounded-3xl p-6 sm:p-8 border-2 border-emerald-500/40 shadow-2xl shadow-emerald-950/50 text-emerald-400 font-mono overflow-hidden">
                
                <!-- Background Scanlines & Glow Overlay -->
                <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-emerald-950/30 via-slate-950 to-slate-950 pointer-events-none"></div>
                <div class="absolute inset-0 bg-[linear-gradient(rgba(16,185,129,0.03)_1px,transparent_1px),linear-gradient(90deg,rgba(16,185,129,0.03)_1px,transparent_1px)] bg-[size:24px_24px] pointer-events-none"></div>

                <div class="relative z-10 space-y-5">
                    <!-- Top Bar in Terminal: Module Status | Category Banner | Mode Roda Switcher -->
                    <div class="flex flex-wrap sm:flex-nowrap items-center justify-between gap-2.5 border-b border-emerald-500/20 pb-3 text-xs">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span class="font-black tracking-widest text-emerald-400 uppercase text-[11px]">SYS_MODULE: CSPRNG // LIVE_DECODER</span>
                            <span class="text-[10px] text-emerald-600 hidden md:inline font-mono">
                                [SFX: <button type="button" @click="soundEnabled = !soundEnabled" class="text-emerald-400 font-bold underline cursor-pointer" x-text="soundEnabled ? 'ON' : 'OFF'"></button>]
                            </span>
                        </div>

                        <!-- Tengah: Info Kategori -->
                        <div class="bg-slate-900/80 rounded-xl py-1 px-3 border border-emerald-500/30 text-xs font-bold text-white flex items-center gap-2">
                            <span class="text-emerald-400 truncate max-w-[150px] sm:max-w-none" x-text="activePool?.title"></span>
                            <span class="text-[10px] font-mono text-amber-400" x-text="activeUndrawnParticipants.length + ' Sisa'"></span>
                        </div>

                        <!-- Pojok Kanan Atas: Tombol Balik ke Mode Roda -->
                        <button type="button" 
                                @click="setVisualMode('wheel')" 
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-500/15 hover:bg-amber-500/25 text-amber-400 border border-amber-500/40 text-xs font-bold transition cursor-pointer shadow-md shrink-0 font-sans"
                                title="Kembali ke Tampilan Spin Wheel">
                            <i data-lucide="disc" class="w-3.5 h-3.5 text-amber-400"></i>
                            <span>Mode Roda</span>
                        </button>
                    </div>

                    <!-- Scramble Duration & Mode Controls -->
                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-2 text-xs font-sans">
                        <!-- Mode Selector (8 cols) -->
                        <div class="sm:col-span-8 flex items-center bg-slate-900/90 p-1 rounded-xl border border-emerald-500/30">
                            <button type="button" @click="drawMode = 'draw_slot'; updateDisplayNumber()" 
                                    :class="drawMode === 'draw_slot' ? 'bg-emerald-500 text-slate-950 font-black shadow-md' : 'text-emerald-400 hover:text-white font-bold'" 
                                    class="flex-1 py-1.5 px-2.5 rounded-lg transition text-center cursor-pointer text-[11px]">
                                🎲 1. Urutan Tampil (#1, #2...)
                            </button>
                            <button type="button" @click="drawMode = 'draw_participant'; updateDisplayNumber()" 
                                    :class="drawMode === 'draw_participant' ? 'bg-emerald-500 text-slate-950 font-black shadow-md' : 'text-emerald-400 hover:text-white font-bold'" 
                                    class="flex-1 py-1.5 px-2.5 rounded-lg transition text-center cursor-pointer text-[11px]">
                                👤 2. Pilih Peserta Manual
                            </button>
                        </div>

                        <!-- Duration Selector (4 cols) -->
                        <div class="sm:col-span-4 flex items-center bg-slate-900/90 p-1 rounded-xl border border-emerald-500/30 text-[10px]">
                            <button type="button" @click="shuffleDuration = 3000" :class="shuffleDuration === 3000 ? 'bg-emerald-500 text-slate-950 font-black' : 'text-emerald-400 hover:text-white'" class="flex-1 py-1.5 rounded-lg text-center font-bold transition cursor-pointer">3s</button>
                            <button type="button" @click="shuffleDuration = 5000" :class="shuffleDuration === 5000 ? 'bg-emerald-500 text-slate-950 font-black' : 'text-emerald-400 hover:text-white'" class="flex-1 py-1.5 rounded-lg text-center font-bold transition cursor-pointer">5s</button>
                            <button type="button" @click="shuffleDuration = 8000" :class="shuffleDuration === 8000 ? 'bg-emerald-500 text-slate-950 font-black' : 'text-emerald-400 hover:text-white'" class="flex-1 py-1.5 rounded-lg text-center font-bold transition cursor-pointer">8s 🔥</button>
                        </div>
                    </div>

                    <!-- Batch Active Cascade HUD Banner -->
                    <div x-show="isBatchRunning" x-cloak class="p-3 rounded-2xl bg-emerald-950/90 border-2 border-emerald-500/60 shadow-xl shadow-emerald-500/20 flex flex-wrap items-center justify-between gap-3 font-sans">
                        <div class="flex items-center gap-2.5">
                            <span class="relative flex h-3 w-3">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                            </span>
                            <div>
                                <span class="text-[10px] font-mono uppercase tracking-wider text-emerald-400 block font-bold">⚡ PENGACAKAN MASSAL AKTIF (CASCADE DECODER)</span>
                                <span class="text-xs font-black text-white font-mono" x-text="batchProgressText"></span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" @click="toggleBatchPause()" class="px-3 py-1.5 rounded-xl bg-slate-900 border border-amber-500/40 text-amber-300 hover:bg-slate-800 text-xs font-bold transition flex items-center gap-1.5 cursor-pointer shadow-sm">
                                <span x-text="isBatchPaused ? '▶️ Lanjutkan' : '⏸️ Jeda'"></span>
                            </button>
                            <button type="button" @click="skipBatchAnimation()" class="px-3 py-1.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-400 hover:from-emerald-400 hover:to-teal-300 text-slate-950 text-xs font-black transition flex items-center gap-1.5 cursor-pointer shadow-md shadow-emerald-500/30">
                                <i data-lucide="fast-forward" class="w-3.5 h-3.5"></i>
                                <span>Lewati Animasi</span>
                            </button>
                        </div>
                    </div>

                    <!-- Target Number / Slot Display Box -->
                    <div class="text-center py-1 space-y-1.5">
                        <span class="text-[10px] font-bold tracking-widest text-emerald-500/70 uppercase block"
                              x-text="drawMode === 'draw_slot' ? '[ MENGUNDI SIAPA PEMENANG NOMOR URUT TAMPIL ]' : '[ TARGET NOMOR UNDIAN PESERTA ]'">
                        </span>
                        
                        <div class="inline-flex items-center justify-center min-w-[160px] px-6 py-2.5 rounded-2xl bg-emerald-950/60 border-2 border-emerald-400/50 shadow-lg shadow-emerald-500/10">
                            <span class="text-4xl sm:text-5xl font-black tracking-widest text-emerald-300 drop-shadow-[0_0_15px_rgba(52,211,153,0.8)] font-mono" x-text="displayNumber">
                                #01
                            </span>
                        </div>
                    </div>

                    <!-- High-Speed Live Stream Feed -->
                    <div x-show="isDecoding" x-transition class="p-2.5 rounded-xl bg-slate-900/60 border border-emerald-500/20 text-[10px] font-mono text-emerald-400/80 flex items-center justify-between overflow-hidden">
                        <span class="truncate">RADAR_STREAM: <span class="text-white font-bold" x-text="radarTicker"></span></span>
                        <span class="text-emerald-500 font-bold shrink-0 ml-2 animate-pulse">>>> SHUFFLING</span>
                    </div>

                    <!-- Hacker Scramble Display Arena (Name & School) -->
                    <div class="p-6 sm:p-8 rounded-2xl bg-slate-900/90 border border-emerald-500/40 min-h-[170px] flex flex-col items-center justify-center text-center space-y-3 relative overflow-hidden">
                        
                        <div class="text-xs uppercase tracking-widest text-emerald-600 font-bold flex items-center gap-2">
                            <span x-text="isDecoding ? '>>> HIGH-ENTROPY CRYPTOGRAPHIC DECODER ACTIVE <<<' : (lockedWinner ? '>>> TARGET IDENTIFIED & LOCKED <<<' : '>>> READY FOR SHUFFLE <<<')"></span>
                        </div>

                        <!-- Scrambled Name Display -->
                        <div class="text-2xl sm:text-3xl lg:text-4xl font-black tracking-tight text-white break-words drop-shadow-[0_0_15px_rgba(255,255,255,0.7)] transition duration-75"
                             :class="{ 'text-emerald-300': !isDecoding && lockedWinner, 'text-emerald-400 scale-[1.02]': isDecoding }"
                             x-text="displayName">
                            SIAP UNTUK DIUNDI
                        </div>

                        <!-- Scrambled School Display -->
                        <div class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full bg-slate-800/80 border border-emerald-500/30 text-xs sm:text-sm font-semibold text-emerald-300 tracking-wide max-w-md truncate shadow-inner font-sans"
                             x-text="displaySchool">
                            Tekan tombol di bawah untuk mengacak seluruh nama peserta
                        </div>

                        <!-- Target Locked Status Badge -->
                        <div x-show="lockedWinner" x-transition class="pt-2">
                            <span class="px-4 py-1.5 rounded-full bg-emerald-500 text-slate-950 font-black text-xs tracking-widest uppercase shadow-md shadow-emerald-400/30 inline-flex items-center gap-1.5 font-sans">
                                <i data-lucide="check-circle" class="w-4 h-4"></i>
                                <span>BERHASIL DIKUNCI (TARGET LOCKED)</span>
                            </span>
                        </div>

                        <!-- BWF Separation Alert if same school detected -->
                        <div x-show="bwfNotification" x-transition class="mt-3 p-3 rounded-xl bg-indigo-950/80 border border-indigo-500/50 text-left flex items-start gap-2.5 shadow-lg max-w-md font-sans">
                            <span class="text-base shrink-0">🛡️</span>
                            <div class="space-y-0.5 min-w-0">
                                <span class="text-[10px] font-mono font-black uppercase tracking-wider text-indigo-300 block">
                                    [BWF GCR 14] PROTEKSI SATU DELEGASI
                                </span>
                                <p class="text-xs text-slate-200 leading-snug" x-text="bwfNotification"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Target Selection (Only active in draw_participant mode) -->
                    <div x-show="drawMode === 'draw_participant'" x-transition class="space-y-3 pt-1 font-sans">
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-emerald-600 mb-1">Pilih Peserta yang Ingin Diundi:</label>
                            <select x-model="selectedParticipantId" :disabled="isDecoding || activeUndrawnParticipants.length === 0" class="w-full px-3 py-2.5 rounded-xl bg-slate-900 border border-emerald-500/30 text-emerald-200 text-xs font-mono outline-none focus:border-emerald-400">
                                <template x-for="p in activeUndrawnParticipants" :key="p.id">
                                    <option :value="p.id" x-text="p.name + ' (' + p.institution + ')'"></option>
                                </template>
                            </select>
                        </div>
                    </div>

                    <!-- Trigger Buttons (1-by-1 vs Batch All) -->
                    <div class="space-y-2.5 pt-1 font-sans">
                        <!-- Big Hacker 1-by-1 Trigger Button -->
                        <button type="button" @click="startHackerDraw()" :disabled="isDecoding || isBatchRunning || activeUndrawnParticipants.length === 0" class="w-full py-3.5 px-5 rounded-2xl bg-gradient-to-r from-emerald-500 via-teal-400 to-emerald-500 hover:from-emerald-400 hover:to-teal-300 disabled:opacity-40 disabled:cursor-not-allowed text-slate-950 font-black text-sm tracking-wider uppercase shadow-xl shadow-emerald-500/25 hover:scale-[1.01] active:scale-[0.99] transition duration-200 flex items-center justify-center gap-2.5 cursor-pointer">
                            <i data-lucide="terminal" class="w-5 h-5" :class="{ 'animate-spin': isDecoding && !isBatchRunning }"></i>
                            <span x-text="isBatchRunning ? 'UNDIAN MASSAL SEDANG BERJALAN...' : (isDecoding ? 'SEDANG MENGACAK SELURUH KANDIDAT PESERTA...' : (activeUndrawnParticipants.length === 0 ? 'KATEGORI INI TELAH SELESAI DIUNDI' : (drawMode === 'draw_slot' ? 'UNDI 1-BY-1 UNTUK NO ' + displayNumber : 'UNDI 1-BY-1 NOMOR PESERTA')))"></span>
                        </button>

                        <!-- Batch / Full-Shuffle Auto Draw Quick Trigger Button -->
                        <button type="button" @click="openBatchModal()" :disabled="isDecoding || isBatchRunning || activeUndrawnParticipants.length === 0" class="w-full py-3 px-4 rounded-xl bg-slate-800/80 hover:bg-slate-700/80 border border-emerald-500/30 hover:border-emerald-400 text-emerald-300 font-bold text-xs flex items-center justify-center gap-2 transition cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed shadow-md">
                            <i data-lucide="zap" class="w-4 h-4 text-emerald-400"></i>
                            <span>⚡ Undi Sekaligus: Batch / Full-Shuffle Auto Draw (<span x-text="activeUndrawnParticipants.length"></span> Sisa)</span>
                        </button>
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
                        <div class="p-3 rounded-2xl border flex items-center justify-between gap-3 transition duration-300"
                             :class="recentlyDrawnId === item.id ? 'bg-emerald-500/20 border-emerald-400 shadow-[0_0_15px_rgba(52,211,153,0.35)] scale-[1.02]' : 'bg-slate-950/70 border-slate-800 hover:border-slate-700'">
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
                    <div class="flex items-center gap-2">
                        <button type="button" 
                                @click="openBatchModal()" 
                                :disabled="isSpinning || activeUndrawnParticipants.length === 0"
                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-400 border border-emerald-500/40 text-[11px] font-black transition cursor-pointer disabled:opacity-30 disabled:cursor-not-allowed shadow-sm"
                                title="Undi Cepat Semua Peserta di Antrean Ini">
                            <i data-lucide="zap" class="w-3 h-3 text-emerald-400"></i>
                            <span>⚡ Auto Draw</span>
                        </button>
                        <span class="text-xs font-mono font-bold text-amber-300 bg-amber-500/10 border border-amber-500/20 px-2.5 py-0.5 rounded-full" x-text="activeUndrawnParticipants.length + ' Peserta'"></span>
                    </div>
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

    <!-- Modal Batch / Full-Shuffle Auto Draw -->
    <div x-show="isBatchModalOpen" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md font-sans"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="bg-slate-900 border border-emerald-500/40 rounded-3xl p-6 sm:p-8 max-w-lg w-full shadow-2xl space-y-5 relative text-white"
             @click.outside="if (!isProcessingBatch) isBatchModalOpen = false">
            
            <div class="flex items-start justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-500/20 text-emerald-400 border border-emerald-500/40 flex items-center justify-center shrink-0 shadow-lg shadow-emerald-500/20">
                        <i data-lucide="zap" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <span class="text-[10px] font-mono font-bold uppercase tracking-wider text-emerald-400 block">MODUL PENGACAKAN MASSAL</span>
                        <h3 class="text-lg font-black text-white font-display">Batch / Full-Shuffle Auto Draw</h3>
                    </div>
                </div>
                <button type="button" @click="isBatchModalOpen = false" :disabled="isProcessingBatch" class="text-slate-400 hover:text-white p-1">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <div class="space-y-3 bg-slate-950/60 p-4 rounded-2xl border border-white/[0.08] text-xs leading-relaxed text-slate-300">
                <p>Cabang Lomba: <strong class="text-white">{{ $competition->name }}</strong></p>

                @if(count($pools) > 1)
                <!-- Pilihan Scope Pool / Semua -->
                <div class="space-y-1.5 pt-1">
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-emerald-400">Target Kategori Pengundian:</label>
                    <div class="grid grid-cols-2 gap-2">
                        <button type="button" 
                                @click="batchTargetScope = 'pool'"
                                class="p-2.5 rounded-xl border text-xs font-bold transition text-left cursor-pointer"
                                :class="batchTargetScope === 'pool' ? 'bg-emerald-500/20 border-emerald-400 text-emerald-300' : 'bg-slate-900 border-slate-800 text-slate-400 hover:text-white'">
                            <span class="block truncate" x-text="activePool ? activePool.short_title : 'Kategori Aktif'"></span>
                            <span class="text-[10px] text-amber-400 font-mono" x-text="activeUndrawnParticipants.length + ' Belum Diundi'"></span>
                        </button>
                        <button type="button" 
                                @click="batchTargetScope = 'all'"
                                class="p-2.5 rounded-xl border text-xs font-bold transition text-left cursor-pointer"
                                :class="batchTargetScope === 'all' ? 'bg-emerald-500/20 border-emerald-400 text-emerald-300' : 'bg-slate-900 border-slate-800 text-slate-400 hover:text-white'">
                            <span class="block font-bold">Semua Kategori</span>
                            <span class="text-[10px] text-amber-400 font-mono" x-text="allUndrawnParticipants.length + ' Belum Diundi'"></span>
                        </button>
                    </div>
                </div>
                @endif

                <!-- Pilihan Mode & Kecepatan Pengacakan -->
                <div class="space-y-2 pt-2 border-t border-slate-800 font-sans">
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-emerald-400">Metode Tampilan Pengacakan:</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        <!-- Mode Hacker Beruntun -->
                        <button type="button" 
                                @click="batchMode = 'cascade'"
                                class="p-3 rounded-2xl border text-left transition cursor-pointer flex flex-col justify-between"
                                :class="batchMode === 'cascade' ? 'bg-emerald-500/15 border-emerald-400 text-white shadow-lg shadow-emerald-500/10' : 'bg-slate-900 border-slate-800 text-slate-400 hover:text-white'">
                            <div class="flex items-center gap-2 mb-1">
                                <i data-lucide="terminal" class="w-4 h-4 text-emerald-400"></i>
                                <span class="text-xs font-black text-emerald-300">Mode Hacker (1-by-1)</span>
                            </div>
                            <span class="text-[10px] text-slate-300 leading-snug">Mengacak beruntun tiap slot nomor dengan visual glitch matrix & suara hacker.</span>
                        </button>

                        <!-- Mode Instan -->
                        <button type="button" 
                                @click="batchMode = 'instant'"
                                class="p-3 rounded-2xl border text-left transition cursor-pointer flex flex-col justify-between"
                                :class="batchMode === 'instant' ? 'bg-emerald-500/15 border-emerald-400 text-white shadow-lg shadow-emerald-500/10' : 'bg-slate-900 border-slate-800 text-slate-400 hover:text-white'">
                            <div class="flex items-center gap-2 mb-1">
                                <i data-lucide="fast-forward" class="w-4 h-4 text-amber-400"></i>
                                <span class="text-xs font-black text-amber-300">Mode Instan (Langsung)</span>
                            </div>
                            <span class="text-[10px] text-slate-300 leading-snug">Mengunci seluruh peserta seketika dalam 1 kali simpan tanpa animasi beruntun.</span>
                        </button>
                    </div>

                    <!-- Pilihan Kecepatan jika Mode Cascade dipilih -->
                    <div x-show="batchMode === 'cascade'" x-transition class="pt-1.5 flex items-center justify-between bg-slate-900/90 p-2.5 rounded-xl border border-slate-800">
                        <span class="text-[11px] text-slate-300 font-bold">Kecepatan Animasi:</span>
                        <div class="inline-flex items-center p-0.5 bg-slate-950 rounded-lg border border-slate-800 text-[10px]">
                            <button type="button" @click="batchSpeed = 800" :class="batchSpeed === 800 ? 'bg-emerald-500 text-slate-950 font-black' : 'text-slate-400 hover:text-white'" class="px-2.5 py-1 rounded-md transition font-bold cursor-pointer">
                                ⚡ Kilat Turbo (~0.8s)
                            </button>
                            <button type="button" @click="batchSpeed = 1600" :class="batchSpeed === 1600 ? 'bg-emerald-500 text-slate-950 font-black' : 'text-slate-400 hover:text-white'" class="px-2.5 py-1 rounded-md transition font-bold cursor-pointer">
                                🎬 Dramatis (~1.6s)
                            </button>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between text-[11px] font-mono bg-slate-900/90 p-2.5 rounded-xl border border-slate-800">
                    <span class="text-slate-400">Total Peserta yang Akan Diundi:</span>
                    <span class="text-amber-400 font-bold" x-text="(batchTargetScope === 'pool' ? activeUndrawnParticipants.length : allUndrawnParticipants.length) + ' Peserta'"></span>
                </div>
                <p class="text-[11px] text-slate-400">
                    Sistem akan mengacak seluruh slot nomor urut yang tersedia secara kriptografis & adil dalam 1 kali proses. Hasil langsung tersimpan dan otomatis sinkron ke Layar TV Publik.
                </p>
            </div>

            <!-- Countdown / Processing Animation Banner -->
            <div x-show="isProcessingBatch" class="p-4 rounded-2xl bg-emerald-950/60 border border-emerald-500/50 text-center space-y-2">
                <div class="text-3xl font-black font-mono text-emerald-400 animate-bounce" x-text="batchCountdown || 'MEMPROSES...'"></div>
                <div class="text-xs font-mono text-emerald-300 tracking-wider">Mengacak & Mengunci Seluruh Nomor Peserta...</div>
            </div>

            <!-- Success / Error Alerts -->
            <div x-show="batchSuccessMessage" class="p-3 rounded-xl bg-emerald-500/20 border border-emerald-500/40 text-emerald-300 text-xs font-bold flex items-center gap-2">
                <i data-lucide="check-circle" class="w-4 h-4 text-emerald-400"></i>
                <span x-text="batchSuccessMessage"></span>
            </div>
            <div x-show="batchErrorMessage" class="p-3 rounded-xl bg-rose-500/20 border border-rose-500/40 text-rose-300 text-xs font-bold flex items-center gap-2">
                <i data-lucide="alert-triangle" class="w-4 h-4 text-rose-400"></i>
                <span x-text="batchErrorMessage"></span>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="button" 
                        @click="isBatchModalOpen = false" 
                        :disabled="isProcessingBatch"
                        class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-xs transition cursor-pointer">
                    Batal
                </button>
                <button type="button" 
                        @click="executeBatchDraw()" 
                        :disabled="isProcessingBatch || (batchTargetScope === 'pool' ? activeUndrawnParticipants.length === 0 : allUndrawnParticipants.length === 0)"
                        class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 text-slate-950 font-black text-xs shadow-lg shadow-emerald-500/25 flex items-center gap-2 transition cursor-pointer disabled:opacity-40">
                    <i data-lucide="zap" class="w-4 h-4"></i>
                    <span>Mulai Batch / Full-Shuffle Auto Draw</span>
                </button>
            </div>

        </div>
    </div>

    <!-- Modal Konfirmasi Reset Undian dengan Password Admin -->
    <div x-show="isResetModalOpen" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/85 backdrop-blur-md font-sans"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95">
        
        <div class="bg-slate-900 border border-rose-500/40 rounded-3xl p-6 sm:p-7 max-w-md w-full shadow-2xl space-y-5 relative text-white"
             @click.outside="if (!isProcessingReset) closeResetModal()">
            
            <!-- Header Modal -->
            <div class="flex items-start justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-rose-500/20 text-rose-400 border border-rose-500/40 flex items-center justify-center shrink-0 shadow-lg shadow-rose-500/20">
                        <i data-lucide="shield-alert" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <span class="text-[10px] font-mono font-bold uppercase tracking-wider text-rose-400 block">OTORISASI KEAMANAN</span>
                        <h3 class="text-base sm:text-lg font-black text-white font-display" x-text="resetScope === 'all' ? 'Reset Semua Undian Cabor' : 'Reset Undian Kategori'"></h3>
                    </div>
                </div>
                <button type="button" @click="closeResetModal()" :disabled="isProcessingReset" class="text-slate-400 hover:text-white p-1 rounded-lg hover:bg-slate-800 transition cursor-pointer">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Body Info -->
            <div class="space-y-4 text-xs">
                <!-- Deskripsi Tindakan -->
                <div class="p-3.5 rounded-2xl bg-rose-950/30 border border-rose-500/30 text-rose-200 space-y-1">
                    <p class="font-bold flex items-center gap-1.5 text-rose-300">
                        <span>⚠️</span>
                        <span x-text="resetScope === 'all' ? 'Peringatan: Seluruh undian cabor akan dihapus!' : 'Peringatan: Undian kategori ini akan dihapus!'"></span>
                    </p>
                    <p class="text-[11px] text-slate-300 leading-relaxed" x-show="resetScope === 'pool'">
                        Semua nomor undian pada kategori <span class="font-bold text-amber-300" x-text="activePool?.title"></span> akan direset ke antrean belum diundi.
                    </p>
                    <p class="text-[11px] text-slate-300 leading-relaxed" x-show="resetScope === 'all'">
                        Seluruh nomor undian pada semua kategori cabang <span class="font-bold text-amber-300">{{ $competition->name }}</span> akan dikosongkan.
                    </p>
                </div>

                <!-- Input Password Admin -->
                <div class="space-y-1.5">
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-300">
                        Masukkan Password Admin: <span class="text-rose-400">*</span>
                    </label>
                    <div class="relative">
                        <input :type="showResetPassword ? 'text' : 'password'" 
                               x-model="resetAdminPassword"
                               x-ref="resetPasswordInput"
                               @keydown.enter.prevent="executeResetWithPassword()"
                               placeholder="Ketik password admin..."
                               :disabled="isProcessingReset"
                               class="w-full pl-3.5 pr-10 py-2.5 rounded-xl bg-slate-950 border border-slate-700 focus:border-rose-400 focus:ring-2 focus:ring-rose-500/30 text-white text-xs font-medium outline-none transition disabled:opacity-50">
                        <button type="button" 
                                @click="showResetPassword = !showResetPassword"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-white transition cursor-pointer p-0.5">
                            <span x-text="showResetPassword ? '🙈' : '👁️'"></span>
                        </button>
                    </div>
                    <p class="text-[10px] text-slate-400">
                        Masukkan password akun Admin / Super Admin Anda untuk mengonfirmasi tindakan ini.
                    </p>
                </div>

                <!-- Alert Error jika password salah / kosong -->
                <div x-show="resetErrorMessage" x-transition class="p-3 rounded-xl bg-rose-500/20 border border-rose-500/50 text-rose-300 text-xs flex items-center gap-2">
                    <i data-lucide="alert-circle" class="w-4 h-4 shrink-0 text-rose-400"></i>
                    <span x-text="resetErrorMessage"></span>
                </div>
            </div>

            <!-- Modal Footer Buttons -->
            <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-800">
                <button type="button" 
                        @click="closeResetModal()" 
                        :disabled="isProcessingReset"
                        class="px-4 py-2 rounded-xl text-xs font-bold text-slate-400 hover:text-white hover:bg-slate-800 transition cursor-pointer">
                    Batal
                </button>
                <button type="button" 
                        @click="executeResetWithPassword()" 
                        :disabled="isProcessingReset || !resetAdminPassword"
                        class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-rose-500 to-rose-600 hover:from-rose-600 hover:to-rose-700 text-white font-black text-xs shadow-lg shadow-rose-600/30 disabled:opacity-40 disabled:cursor-not-allowed transition flex items-center gap-2 cursor-pointer">
                    <span x-show="isProcessingReset" class="w-3.5 h-3.5 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                    <i x-show="!isProcessingReset" data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                    <span x-text="isProcessingReset ? 'Memverifikasi...' : 'Konfirmasi & Reset'"></span>
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
            activeClassKey: 'all',
            activeSector: 'PA',
            drawnTab: 'pool',
            selectedParticipantId: null,
            isSpinning: false,
            wonDrawNumber: null,
            wonParticipantName: '',
            wonParticipantSchool: '',
            bwfNotification: '',
            audioCtx: null,
            theme: '{{ (str_contains(strtolower($competition->name), 'bulu tangkis') || str_contains(strtolower($competition->name), 'badminton')) ? 'badminton' : 'standard' }}',
            
            // Visual Mode: 'wheel' (Interactive Wheel Canvas) or 'hacker' (Hacker Live Decoder Terminal)
            visualMode: 'wheel',

            // Hacker Live Decoder Specific States
            drawMode: 'draw_slot', // 'draw_slot' or 'draw_participant'
            shuffleDuration: 5000,
            isDecoding: false,
            lockedWinner: null,
            displayName: 'SIAP UNTUK DIUNDI',
            displaySchool: 'Tekan tombol di bawah untuk mengacak seluruh nama peserta',
            displayNumber: '#01',
            radarTicker: 'IDLE',
            soundEnabled: true,

            setVisualMode(mode) {
                if (this.isSpinning || this.isDecoding || this.isBatchRunning) return;
                this.visualMode = mode;
                if (mode === 'wheel') {
                    this.$nextTick(() => {
                        this.canvas = document.getElementById("wheelCanvas");
                        if (this.canvas) {
                            this.ctx = this.canvas.getContext("2d");
                            this.calculateAvailableSlots();
                            this.drawWheel();
                        }
                        if (window.lucide) window.lucide.createIcons();
                    });
                } else if (mode === 'hacker') {
                    this.updateDisplayNumber();
                    this.$nextTick(() => {
                        if (window.lucide) window.lucide.createIcons();
                    });
                }
            },

            // Reset with Password Modal States
            isResetModalOpen: false,
            resetScope: 'pool',
            resetAdminPassword: '',
            showResetPassword: false,
            isProcessingReset: false,
            resetErrorMessage: '',

            // Batch / Full-Shuffle Auto Draw States
            isBatchModalOpen: false,
            batchTargetScope: 'pool',
            isProcessingBatch: false,
            batchCountdown: null,
            batchSuccessMessage: '',
            batchErrorMessage: '',
            batchMode: 'cascade', // 'cascade' (Mode Hacker 1-by-1) or 'instant' (Semua Langsung)
            batchSpeed: 800, // 800ms (Kilat Turbo), 1600ms (Dramatis)
            isBatchRunning: false,
            isBatchPaused: false,
            batchStopRequested: false,
            batchProgressText: '',
            recentlyDrawnId: null,

            openBatchModal() {
                if (this.isBatchRunning) return;
                this.isBatchModalOpen = true;
                this.batchSuccessMessage = '';
                this.batchErrorMessage = '';
                this.isProcessingBatch = false;
                this.batchCountdown = null;
                this.batchTargetScope = 'pool';
                this.batchMode = 'cascade';
                this.batchSpeed = 800;
                this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
            },

            async executeBatchDraw() {
                if (this.isProcessingBatch || this.isBatchRunning) return;
                this.isProcessingBatch = true;
                this.batchErrorMessage = '';
                this.batchSuccessMessage = '';

                try {
                    const poolKey = this.batchTargetScope === 'pool' ? this.activePoolKey : 'all';
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

                    const response = await fetch(`/pic/lomba/${this.competitionId}/batch-draw`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({ pool_key: poolKey })
                    });

                    const data = await response.json();
                    if (!data.success) {
                        this.batchErrorMessage = data.message || 'Gagal memproses Batch Auto Draw.';
                        this.isProcessingBatch = false;
                        return;
                    }

                    const results = data.results && Array.isArray(data.results) ? data.results : [];
                    if (results.length === 0) {
                        this.batchErrorMessage = 'Tidak ada peserta yang perlu diundi.';
                        this.isProcessingBatch = false;
                        return;
                    }

                    // Sort results ascending by draw_number so slots #1, #2, #3... are animated in sequence
                    results.sort((a, b) => parseInt(a.draw_number) - parseInt(b.draw_number));

                    if (this.batchMode === 'instant') {
                        // Instant Mode: Commit all at once
                        results.forEach(res => {
                            this.commitParticipantDrawn(res);
                        });

                        this.batchSuccessMessage = data.message;
                        this.displayName = '⚡ BATCH SHUFFLE COMPLETED';
                        this.displaySchool = `${data.drawn_count} peserta berhasil diundi secara serentak`;
                        this.radarTicker = 'BATCH_COMPLETED >> ' + data.drawn_count + ' SLOTS ALLOCATED';

                        if (typeof this.playWinnerSound === 'function') {
                            this.playWinnerSound();
                        } else if (typeof this.playLockSound === 'function') {
                            this.playLockSound();
                        }

                        if (typeof confetti === 'function') {
                            confetti({ particleCount: 160, spread: 90, origin: { y: 0.6 } });
                        }

                        setTimeout(() => {
                            this.isBatchModalOpen = false;
                            this.isProcessingBatch = false;
                            this.drawWheel();
                        }, 1400);
                        return;
                    }

                    // Cascade Hacker Mode: Close modal and launch sequential hacker cipher decoder
                    this.isBatchModalOpen = false;
                    this.isProcessingBatch = false;

                    // Auto-switch to Hacker Mode terminal so spectators see the full live decoder spectacle
                    if (this.visualMode !== 'hacker') {
                        this.setVisualMode('hacker');
                    }

                    // Run the animated sequential cascade
                    await this.runBatchHackerCascade(results, this.batchSpeed);

                } catch (err) {
                    console.error('Batch draw network error:', err);
                    this.batchErrorMessage = 'Terjadi gangguan saat memproses Batch Auto Draw: ' + (err.message || err);
                    this.isProcessingBatch = false;
                }
            },

            async runBatchHackerCascade(results, speed = 800) {
                this.isBatchRunning = true;
                this.isDecoding = true;
                this.batchStopRequested = false;
                this.isBatchPaused = false;
                this.lockedWinner = null;
                this.bwfNotification = '';

                const totalItems = results.length;

                for (let idx = 0; idx < totalItems; idx++) {
                    if (this.batchStopRequested) {
                        // Skip remaining: commit immediately
                        for (let rem = idx; rem < totalItems; rem++) {
                            this.commitParticipantDrawn(results[rem]);
                        }
                        break;
                    }

                    // Handle Pause
                    while (this.isBatchPaused && !this.batchStopRequested) {
                        await new Promise(r => setTimeout(r, 100));
                    }

                    const item = results[idx];
                    this.batchProgressText = `${idx + 1} / ${totalItems} Peserta • Target Slot #${String(item.draw_number).padStart(2, '0')}`;

                    // Run punchy single hacker cipher animation for this slot
                    await this.animateSingleHackerSlot(item, speed);

                    // Commit item to reactive state and highlight
                    this.commitParticipantDrawn(item);
                    this.recentlyDrawnId = item.id;

                    if (idx < totalItems - 1 && !this.batchStopRequested) {
                        await new Promise(r => setTimeout(r, 160));
                    }
                }

                // Final celebration
                this.isBatchRunning = false;
                this.isDecoding = false;
                this.isBatchPaused = false;
                this.batchStopRequested = false;
                this.batchProgressText = '';
                this.displayName = '⚡ SELURUH SLOT SELESAI DIUNDI';
                this.displaySchool = `${totalItems} peserta sukses dialokasikan secara berurutan`;
                this.radarTicker = 'CASCADE_FINISHED >> ALL_TARGETS_LOCKED';
                this.updateDisplayNumber();

                if (typeof this.playLockSound === 'function') {
                    this.playLockSound();
                } else if (typeof this.playWinnerSound === 'function') {
                    this.playWinnerSound();
                }

                if (typeof confetti === 'function') {
                    confetti({ particleCount: 180, spread: 100, origin: { y: 0.6 } });
                }

                setTimeout(() => {
                    this.recentlyDrawnId = null;
                }, 3000);
            },

            animateSingleHackerSlot(item, duration = 800) {
                return new Promise((resolve) => {
                    const targetName = item.name || 'PESERTA';
                    const targetSchool = item.institution || '';
                    const targetNumStr = '#' + String(item.draw_number).padStart(2, '0');
                    const glitchChars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%&*<>/=[]{}?+~§µ";

                    this.displayNumber = targetNumStr;
                    this.lockedWinner = null;
                    this.bwfNotification = '';

                    const startTime = performance.now();
                    let frameCount = 0;
                    const candidatePool = this.activeParticipants.length > 0 ? this.activeParticipants : [item];

                    const animateFrame = (currentTime) => {
                        if (this.batchStopRequested) {
                            this.displayName = targetName;
                            this.displaySchool = targetSchool;
                            this.displayNumber = targetNumStr;
                            resolve();
                            return;
                        }

                        const elapsed = currentTime - startTime;
                        const progress = Math.min(elapsed / duration, 1);
                        frameCount++;

                        if (progress < 0.65) {
                            const randCandidate = candidatePool[this.getCryptoRandomInt(candidatePool.length)];
                            const baseName = randCandidate ? randCandidate.name : targetName;

                            let scrambled = "";
                            for (let i = 0; i < baseName.length; i++) {
                                if (baseName[i] === ' ') {
                                    scrambled += ' ';
                                } else if (Math.random() > 0.4) {
                                    scrambled += baseName[i];
                                } else {
                                    scrambled += glitchChars[this.getCryptoRandomInt(glitchChars.length)];
                                }
                            }
                            this.displayName = scrambled;
                            this.displaySchool = randCandidate ? randCandidate.institution : targetSchool;
                            this.radarTicker = (randCandidate ? randCandidate.name : targetName) + ' [' + (randCandidate ? randCandidate.institution : targetSchool) + ']';

                            if (frameCount % 2 === 0 && typeof this.playBeep === 'function') {
                                this.playBeep(520 + (Math.sin(frameCount) * 320) + this.getCryptoRandomInt(160), 0.02, 'square');
                            }
                        } else {
                            const resolveRatio = (progress - 0.65) / 0.35;
                            const charsToLock = Math.floor(resolveRatio * targetName.length);

                            let partial = "";
                            for (let i = 0; i < targetName.length; i++) {
                                if (i <= charsToLock) {
                                    partial += targetName[i];
                                } else if (targetName[i] === ' ') {
                                    partial += ' ';
                                } else {
                                    partial += glitchChars[this.getCryptoRandomInt(glitchChars.length)];
                                }
                            }
                            this.displayName = partial;
                            this.displaySchool = targetSchool;
                            this.radarTicker = 'LOCKING_ON >> ' + targetName + ' (' + Math.floor(resolveRatio * 100) + '%)';

                            if (frameCount % 3 === 0 && typeof this.playBeep === 'function') {
                                this.playBeep(650 + (resolveRatio * 500), 0.03, 'triangle');
                            }
                        }

                        if (progress < 1) {
                            requestAnimationFrame(animateFrame);
                        } else {
                            this.displayName = targetName;
                            this.displaySchool = targetSchool;
                            this.displayNumber = targetNumStr;
                            this.radarTicker = 'TARGET_LOCKED >> ' + targetName;
                            this.lockedWinner = {
                                participant: item,
                                drawNumber: item.draw_number
                            };

                            if (typeof this.playBeep === 'function') {
                                this.playBeep(880, 0.08, 'triangle');
                            }

                            resolve();
                        }
                    };

                    requestAnimationFrame(animateFrame);
                });
            },

            commitParticipantDrawn(item) {
                this.pools.forEach(p => {
                    const participant = p.participants.find(pt => pt.id == item.id);
                    if (participant) {
                        participant.is_drawn = true;
                        participant.draw_number = item.draw_number;
                    }
                });

                if (this.activeUndrawnParticipants.length > 0) {
                    this.selectedParticipantId = this.activeUndrawnParticipants[0].id;
                } else {
                    this.selectedParticipantId = null;
                }

                this.updateDisplayNumber();
                if (typeof this.calculateAvailableSlots === 'function') {
                    this.calculateAvailableSlots();
                }
            },

            toggleBatchPause() {
                this.isBatchPaused = !this.isBatchPaused;
            },

            skipBatchAnimation() {
                this.batchStopRequested = true;
            },
            
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

            getSector(p) {
                if (!p) return 'PA';
                if (p.sector) return p.sector;
                const k = (p.key || '').toLowerCase();
                const t = (p.title || '').toLowerCase();
                if (p.gender === 'P' || k.endsWith('_pi') || t.includes('putri') || t.includes('(pi)')) return 'PI';
                if (p.gender === 'M' || k.endsWith('_mix') || t.includes('campuran')) return 'MIX';
                return 'PA';
            },

            getCleanCategoryTitle(p) {
                if (!p) return '';
                if (p.category_label) return p.category_label;
                let title = p.title || '';
                title = title.replace(/\s*-\s*Tunggal\s+(Putra|Putri)\s*\(P[AI]\)/gi, '');
                title = title.replace(/\s*\(P[AI]\)/gi, '');
                return title.trim();
            },

            getClassKey(p) {
                if (!p) return 'all';
                if (p.class_key) return p.class_key;
                const k = (p.key || '').toLowerCase();
                if (k.startsWith('kat_a')) return 'kat_a';
                if (k.startsWith('kat_b')) return 'kat_b';
                if (k.startsWith('kat_c')) return 'kat_c';
                if (k.startsWith('ganda')) return 'ganda';
                return k.replace(/(_pa|_pi|_mix)$/i, '') || 'all';
            },

            getClassLabel(p) {
                if (!p) return '';
                if (p.class_label) return p.class_label;
                return this.getCleanCategoryTitle(p);
            },

            get availableClasses() {
                const map = new Map();
                this.pools.forEach(p => {
                    const cKey = this.getClassKey(p);
                    if (!map.has(cKey)) {
                        map.set(cKey, {
                            key: cKey,
                            label: this.getClassLabel(p),
                            totalParticipants: 0,
                            pools: []
                        });
                    }
                    const item = map.get(cKey);
                    item.totalParticipants += p.participants.length;
                    item.pools.push(p);
                });
                return Array.from(map.values());
            },

            get currentClassPools() {
                const filtered = this.pools.filter(p => this.getClassKey(p) === this.activeClassKey);
                return filtered.length > 0 ? filtered : this.pools;
            },

            get availableSectors() {
                const list = [];
                const classPools = this.currentClassPools;

                const paPool = classPools.find(p => this.getSector(p) === 'PA');
                const piPool = classPools.find(p => this.getSector(p) === 'PI');
                const mixPool = classPools.find(p => this.getSector(p) === 'MIX');

                if (paPool || this.pools.some(p => this.getSector(p) === 'PA')) {
                    list.push({
                        key: 'PA',
                        label: '(PA)',
                        icon: '👦',
                        pool: paPool,
                        count: paPool ? paPool.participants.length : 0,
                        exists: !!paPool
                    });
                }

                if (piPool || this.pools.some(p => this.getSector(p) === 'PI')) {
                    list.push({
                        key: 'PI',
                        label: '(PI)',
                        icon: '👧',
                        pool: piPool,
                        count: piPool ? piPool.participants.length : 0,
                        exists: !!piPool
                    });
                }

                if (mixPool) {
                    list.push({
                        key: 'MIX',
                        label: 'CAMPURAN',
                        icon: '👥',
                        pool: mixPool,
                        count: mixPool ? mixPool.participants.length : 0,
                        exists: !!mixPool
                    });
                }

                return list;
            },

            switchClass(cKey) {
                if (this.isSpinning || this.isDecoding) return;
                this.activeClassKey = cKey;
                const classPools = this.pools.filter(p => this.getClassKey(p) === cKey);
                const targetPool = classPools.find(p => this.getSector(p) === this.activeSector) || classPools[0];
                if (targetPool) {
                    this.switchPool(targetPool.key);
                }
            },

            switchSector(secKey) {
                if (this.isSpinning || this.isDecoding) return;
                this.activeSector = secKey;
                const classPools = this.currentClassPools;
                const targetPool = classPools.find(p => this.getSector(p) === secKey);
                if (targetPool) {
                    this.switchPool(targetPool.key);
                } else {
                    const anyPool = this.pools.find(p => this.getSector(p) === secKey);
                    if (anyPool) {
                        this.switchPool(anyPool.key);
                    }
                }
            },

            get nextAvailableSlot() {
                if (!this.activePool) return 1;
                const assigned = this.activeDrawnParticipants.map(d => parseInt(d.draw_number));
                const total = this.activePool.participants.length;
                for (let i = 1; i <= Math.max(total, 1); i++) {
                    if (!assigned.includes(i)) return i;
                }
                return 1;
            },

            get availableSlots() {
                if (!this.activePool) return [];
                const assigned = this.activeDrawnParticipants.map(d => parseInt(d.draw_number));
                const total = this.activePool.participants.length;
                let slots = [];
                for (let i = 1; i <= Math.max(total, 1); i++) {
                    if (!assigned.includes(i)) slots.push(i);
                }
                return slots;
            },

            updateDisplayNumber() {
                if (this.drawMode === 'draw_slot') {
                    const nextSlot = this.nextAvailableSlot;
                    this.displayNumber = '#' + String(nextSlot).padStart(2, '0');
                } else {
                    this.displayNumber = '#??';
                }
            },

            getCryptoRandomInt(max) {
                if (max <= 0) return 0;
                const array = new Uint32Array(1);
                window.crypto.getRandomValues(array);
                return array[0] % max;
            },

            cryptoShuffle(arr) {
                const shuffled = [...arr];
                for (let round = 0; round < 3; round++) {
                    for (let i = shuffled.length - 1; i > 0; i--) {
                        const j = this.getCryptoRandomInt(i + 1);
                        [shuffled[i], shuffled[j]] = [shuffled[j], shuffled[i]];
                    }
                }
                return shuffled;
            },

            playLockSound() {
                if (!this.soundEnabled) return;
                try {
                    if (!this.audioCtx) this.audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                    const chord = [440, 554.37, 659.25, 880, 1108.73];
                    chord.forEach((freq, idx) => {
                        setTimeout(() => {
                            const osc = this.audioCtx.createOscillator();
                            const gain = this.audioCtx.createGain();
                            osc.type = (idx === 0) ? 'sawtooth' : 'triangle';
                            osc.frequency.setValueAtTime(freq, this.audioCtx.currentTime);
                            gain.gain.setValueAtTime(0.12, this.audioCtx.currentTime);
                            gain.gain.exponentialRampToValueAtTime(0.001, this.audioCtx.currentTime + 0.45);
                            osc.connect(gain);
                            gain.connect(this.audioCtx.destination);
                            osc.start();
                            osc.stop(this.audioCtx.currentTime + 0.45);
                        }, idx * 50);
                    });
                } catch(e) {}
            },

            startHackerDraw() {
                if (this.isDecoding || this.activeUndrawnParticipants.length === 0) return;
                this.isDecoding = true;
                this.lockedWinner = null;
                this.bwfNotification = '';

                const shuffledCandidates = this.cryptoShuffle(this.activeUndrawnParticipants);

                let winnerParticipant;
                let winnerDrawNumber;

                if (this.drawMode === 'draw_slot') {
                    const randIndex = this.getCryptoRandomInt(shuffledCandidates.length);
                    winnerParticipant = shuffledCandidates[randIndex];
                    winnerDrawNumber = this.nextAvailableSlot;
                } else {
                    winnerParticipant = this.activeUndrawnParticipants.find(p => p.id == this.selectedParticipantId) || shuffledCandidates[0];
                    const shuffledSlots = this.cryptoShuffle(this.availableSlots);
                    if (shuffledSlots.length > 0) {
                        const randSlotIdx = this.getCryptoRandomInt(shuffledSlots.length);
                        winnerDrawNumber = shuffledSlots[randSlotIdx];
                    } else {
                        winnerDrawNumber = this.activeDrawnParticipants.length + 1;
                    }
                }

                const targetName = winnerParticipant.name;
                const targetSchool = winnerParticipant.institution;
                const targetNumStr = '#' + String(winnerDrawNumber).padStart(2, '0');

                const glitchChars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%&*<>/=[]{}?+~§µ";
                const totalDuration = this.shuffleDuration;
                const startTime = performance.now();
                let frameCount = 0;

                const animate = (currentTime) => {
                    const elapsed = currentTime - startTime;
                    const progress = Math.min(elapsed / totalDuration, 1);
                    frameCount++;

                    if (progress < 0.65) {
                        const randCandidate = shuffledCandidates[this.getCryptoRandomInt(shuffledCandidates.length)];
                        const baseName = randCandidate ? randCandidate.name : targetName;
                        
                        let scrambledName = "";
                        for (let i = 0; i < baseName.length; i++) {
                            if (baseName[i] === ' ') {
                                scrambledName += ' ';
                            } else if (Math.random() > 0.35) {
                                scrambledName += baseName[i];
                            } else {
                                scrambledName += glitchChars[this.getCryptoRandomInt(glitchChars.length)];
                            }
                        }
                        this.displayName = scrambledName;
                        this.displaySchool = randCandidate ? randCandidate.institution : 'SCANNING DATABASE ENTROPY...';
                        this.radarTicker = (randCandidate ? randCandidate.name : '0x' + this.getCryptoRandomInt(99999).toString(16).toUpperCase()) + ' [' + (randCandidate ? randCandidate.institution : 'SCAN') + ']';

                        if (this.drawMode === 'draw_slot') {
                            this.displayNumber = targetNumStr;
                        } else {
                            const randNum = this.availableSlots.length > 0 ? this.availableSlots[this.getCryptoRandomInt(this.availableSlots.length)] : this.getCryptoRandomInt(99) + 1;
                            this.displayNumber = '#' + String(randNum).padStart(2, '0');
                        }

                        if (frameCount % 2 === 0) {
                            this.playBeep(450 + (Math.sin(frameCount) * 350) + this.getCryptoRandomInt(200), 0.025, 'square');
                        }
                    } else {
                        const resolveRatio = (progress - 0.65) / 0.35;
                        const charsToLock = Math.floor(resolveRatio * targetName.length);

                        let partialName = "";
                        for (let i = 0; i < targetName.length; i++) {
                            if (i <= charsToLock) {
                                partialName += targetName[i];
                            } else if (targetName[i] === ' ') {
                                partialName += ' ';
                            } else {
                                partialName += glitchChars[this.getCryptoRandomInt(glitchChars.length)];
                            }
                        }
                        this.displayName = partialName;
                        this.displaySchool = targetSchool;
                        this.displayNumber = targetNumStr;
                        this.radarTicker = 'LOCKING_ON >> ' + targetName + ' (' + Math.floor(resolveRatio * 100) + '%)';

                        if (frameCount % 3 === 0) {
                            this.playBeep(600 + (resolveRatio * 600), 0.04, 'triangle');
                        }
                    }

                    if (progress < 1) {
                        const stepDelay = progress > 0.70 ? (progress - 0.70) * 160 : 20;
                        setTimeout(() => {
                            requestAnimationFrame(animate);
                        }, stepDelay);
                    } else {
                        this.displayName = targetName;
                        this.displaySchool = targetSchool;
                        this.displayNumber = targetNumStr;
                        this.radarTicker = 'TARGET_LOCKED >> ' + targetName;
                        this.isDecoding = false;
                        this.lockedWinner = {
                            participant: winnerParticipant,
                            drawNumber: winnerDrawNumber
                        };

                        const school = (winnerParticipant.institution || '').trim().toLowerCase();
                        const sameSchoolTeammates = this.activeParticipants.filter(p => p.id !== winnerParticipant.id && (p.institution || '').trim().toLowerCase() === school);

                        if (sameSchoolTeammates.length > 0) {
                            const alreadyDrawn = sameSchoolTeammates.filter(p => p.is_drawn || p.is_seeded);
                            if (alreadyDrawn.length > 0) {
                                const names = alreadyDrawn.map(p => p.name).join(', ');
                                this.bwfNotification = `Terdeteksi rekan satu delegasi dari ${winnerParticipant.institution} (${names}) yang telah terundi/seeded sebelumnya. Sesuai aturan resmi BWF GCR 14 (Proteksi Satu Delegasi), peserta ini dialokasikan ke sisi bagan yang berseberangan agar tidak saling berhadapan di Babak 1.`;
                            } else {
                                this.bwfNotification = `Peserta dari ${winnerParticipant.institution} memiliki rekan satu delegasi dalam kategori ini. Proteksi BWF GCR 14 aktif untuk memastikan mereka dipisahkan pool dan tidak bertemu di Babak 1.`;
                            }
                        } else {
                            this.bwfNotification = '';
                        }

                        this.playLockSound();

                        if (typeof confetti === 'function') {
                            confetti({
                                particleCount: 140,
                                spread: 90,
                                origin: { y: 0.6 }
                            });
                        }

                        winnerParticipant.is_drawn = true;
                        winnerParticipant.draw_number = winnerDrawNumber;

                        if (this.activeUndrawnParticipants.length > 0) {
                            this.selectedParticipantId = this.activeUndrawnParticipants[0].id;
                        } else {
                            this.selectedParticipantId = null;
                        }
                        this.updateDisplayNumber();
                        this.calculateAvailableSlots();

                        this.saveDrawResult(winnerParticipant.id, winnerDrawNumber);
                    }
                };

                requestAnimationFrame(animate);
            },

            init() {
                const cur = this.pools.find(p => p.key === this.activePoolKey) || this.pools[0];
                if (cur) {
                    this.activeClassKey = this.getClassKey(cur);
                    this.activeSector = this.getSector(cur);
                }

                if (this.activeUndrawnParticipants.length > 0) {
                    this.selectedParticipantId = this.activeUndrawnParticipants[0].id;
                }

                this.updateDisplayNumber();

                this.canvas = document.getElementById("wheelCanvas");
                if (this.canvas) {
                    this.ctx = this.canvas.getContext("2d");
                    this.calculateAvailableSlots();
                    this.drawWheel();
                }
                this.$nextTick(() => {
                    if (window.lucide) window.lucide.createIcons();
                    if (new URLSearchParams(window.location.search).get('open_seeded') === '1') {
                        this.openSeededModal();
                    }
                });
            },

            switchPool(key) {
                if (this.isSpinning || this.isDecoding || this.isBatchRunning) return;
                this.activePoolKey = key;
                const target = this.pools.find(p => p.key === key);
                if (target) {
                    this.activeClassKey = this.getClassKey(target);
                    this.activeSector = this.getSector(target);
                }
                this.wonDrawNumber = null;
                this.lockedWinner = null;
                this.displayName = 'SIAP UNTUK DIUNDI';
                this.displaySchool = 'Tekan tombol di bawah untuk mengacak seluruh nama peserta';
                if (this.activeUndrawnParticipants.length > 0) {
                    this.selectedParticipantId = this.activeUndrawnParticipants[0].id;
                } else {
                    this.selectedParticipantId = null;
                }
                this.updateDisplayNumber();
                this.calculateAvailableSlots();
                if (this.visualMode === 'wheel') {
                    this.drawWheel();
                }
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
                this.promptResetWithPassword('pool');
            },

            promptResetWithPassword(scope = 'pool') {
                if (this.isSpinning) return;
                this.resetScope = scope;
                this.resetAdminPassword = '';
                this.showResetPassword = false;
                this.resetErrorMessage = '';
                this.isProcessingReset = false;
                this.isResetModalOpen = true;
                this.$nextTick(() => {
                    if (window.lucide) window.lucide.createIcons();
                    if (this.$refs.resetPasswordInput) {
                        this.$refs.resetPasswordInput.focus();
                    }
                });
            },

            closeResetModal() {
                if (this.isProcessingReset) return;
                this.isResetModalOpen = false;
                this.resetAdminPassword = '';
                this.resetErrorMessage = '';
            },

            async executeResetWithPassword() {
                if (this.isProcessingReset) return;
                if (!this.resetAdminPassword) {
                    this.resetErrorMessage = 'Silakan masukkan password admin terlebih dahulu.';
                    return;
                }

                this.isProcessingReset = true;
                this.resetErrorMessage = '';

                try {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
                    const payload = {
                        admin_password: this.resetAdminPassword
                    };

                    if (this.resetScope === 'pool') {
                        if (!this.activePool || !this.activePool.participants) {
                            this.resetErrorMessage = 'Kategori aktif tidak valid.';
                            this.isProcessingReset = false;
                            return;
                        }
                        payload.registration_ids = this.activePool.participants.map(p => p.id).join(',');
                    }

                    const response = await fetch('{{ route("pic.spin.wheel.reset", $competition->id) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify(payload)
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        this.isResetModalOpen = false;
                        window.location.reload();
                    } else {
                        this.resetErrorMessage = data.message || 'Password admin salah atau terjadi kesalahan.';
                        this.isProcessingReset = false;
                        this.$nextTick(() => {
                            if (window.lucide) window.lucide.createIcons();
                            if (this.$refs.resetPasswordInput) {
                                this.$refs.resetPasswordInput.select();
                            }
                        });
                    }
                } catch (err) {
                    console.error('Reset error:', err);
                    this.resetErrorMessage = 'Terjadi kesalahan sistem saat memproses reset.';
                    this.isProcessingReset = false;
                }
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

            playBeep(freq = 600, duration = 0.03, type = 'sine') {
                try {
                    if (!this.audioCtx) this.audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                    const osc = this.audioCtx.createOscillator();
                    const gain = this.audioCtx.createGain();
                    osc.type = type;
                    osc.frequency.setValueAtTime(freq, this.audioCtx.currentTime);
                    gain.gain.setValueAtTime(0.08, this.audioCtx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.001, this.audioCtx.currentTime + duration);
                    osc.connect(gain);
                    gain.connect(this.audioCtx.destination);
                    osc.start();
                    osc.stop(this.audioCtx.currentTime + duration);
                } catch(e) {}
            },

            playWinnerSound() {
                try {
                    if (!this.audioCtx) this.audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                    const notes = [523.25, 659.25, 783.99, 1046.50]; // C5, E5, G5, C6
                    notes.forEach((freq, idx) => {
                        const osc = this.audioCtx.createOscillator();
                        const gain = this.audioCtx.createGain();
                        osc.type = 'triangle';
                        osc.frequency.setValueAtTime(freq, this.audioCtx.currentTime + idx * 0.12);
                        gain.gain.setValueAtTime(0.12, this.audioCtx.currentTime + idx * 0.12);
                        gain.gain.exponentialRampToValueAtTime(0.001, this.audioCtx.currentTime + idx * 0.12 + 0.35);
                        osc.connect(gain);
                        gain.connect(this.audioCtx.destination);
                        osc.start(this.audioCtx.currentTime + idx * 0.12);
                        osc.stop(this.audioCtx.currentTime + idx * 0.12 + 0.35);
                    });
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
