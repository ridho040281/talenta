{{-- 
  Tournament Stepper Navigation Component
  5-Step Sequential Pipeline:
  1. Data Peserta
  2. Unggulan (Seeded)
  3. Undian Slot (Draw)
  4. Bagan & Jadwal (Knockout Tree)
  5. Wasit & Arena (Match Day)
--}}
@php
    $poolParam = !empty($activePoolKey) ? ['pool' => $activePoolKey] : [];
    $isBadminton = (strtoupper($competition->code ?? '') === 'BLT') 
        || str_contains(strtolower($competition->name ?? ''), 'bulu tangkis') 
        || str_contains(strtolower($competition->name ?? ''), 'badminton');

    // Retrieve tournament competitions for instant switching (Bulu Tangkis vs Tenis Meja)
    if (!isset($tournamentCompetitions)) {
        $user = auth()->user();
        $tCompQuery = \App\Models\Competition::where(function($q) {
            $q->whereIn('code', ['BLT', 'TMJ'])
              ->orWhere('name', 'like', '%Bulu Tangkis%')
              ->orWhere('name', 'like', '%Badminton%')
              ->orWhere('name', 'like', '%Tenis Meja%');
        })->orderByRaw("CASE WHEN code = 'BLT' OR name LIKE '%Bulu Tangkis%' OR name LIKE '%Badminton%' THEN 1 ELSE 2 END");

        if ($user && !in_array($user->role, ['superadmin', 'panitia']) && !$user->managesTournamentBracket()) {
            $tCompQuery->whereIn('id', \App\Http\Controllers\PicController::getManagedCompetitionIds($user));
        }
        $tournamentCompetitions = $tCompQuery->get();
    }

    // Map activePoolKey to sector option for seamless filtering in data peserta
    $sectorParam = '';
    if (!empty($activePoolKey) && $activePoolKey !== 'all') {
        if (str_contains($activePoolKey, 'kat_a') || str_contains($activePoolKey, 'blt_a')) {
            $sectorParam = 'blt_a_all';
        } elseif (str_contains($activePoolKey, 'kat_b') || str_contains($activePoolKey, 'blt_b')) {
            $sectorParam = 'blt_b_all';
        } elseif (str_contains($activePoolKey, 'kat_c') || str_contains($activePoolKey, 'blt_c')) {
            $sectorParam = 'blt_c_all';
        } elseif (str_contains($activePoolKey, 'ganda')) {
            $sectorParam = 'ganda_all';
        } elseif (str_contains($activePoolKey, 'tmj_a')) {
            $sectorParam = 'tmj_a_all';
        } elseif (str_contains($activePoolKey, 'tmj_b')) {
            $sectorParam = 'tmj_b_all';
        }
    }
    $pesertaUrl = route('pic.dashboard') . '?competition_id=' . $competition->id . ($sectorParam ? '&sector=' . $sectorParam : '');
@endphp

<div class="ai-card bg-[#090D17]/95 border border-white/[0.12] rounded-3xl p-3 sm:p-4 mb-6 shadow-2xl backdrop-blur-xl relative z-40">
    <div class="flex flex-col gap-3.5">
        
        <!-- Baris Atas: Competition Context & Cabor Switcher (Kiri) vs Quick Actions & Titik 3 (Pojok Kanan Atas) -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            
            <!-- Left: Competition Context & Title & Cabor Switcher -->
            <div class="flex flex-col sm:flex-row sm:items-center gap-3 shrink-0">
                <div class="flex items-center gap-3 shrink-0">
                    <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-[#7A5AF8] to-[#4E6EFF] text-white flex items-center justify-center font-bold shadow-lg shadow-[#7A5AF8]/30 shrink-0">
                        <i data-lucide="git-branch" class="w-5 h-5"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="px-2 py-0.5 rounded-md bg-[#7A5AF8]/20 text-[#A594FD] border border-[#7A5AF8]/40 text-[10px] font-mono font-bold uppercase tracking-wider">
                                {{ $competition->code ?? 'BLT' }}
                            </span>
                            <h2 class="text-sm sm:text-base font-black text-white truncate font-display">
                                {{ $competition->name }}
                            </h2>
                        </div>
                        <p class="text-[11px] text-slate-400 font-medium truncate mt-0.5">
                            Alur Kerja Turnamen: 5 Langkah Terarah Sistem Gugur
                        </p>
                    </div>
                </div>

                @if(isset($tournamentCompetitions) && $tournamentCompetitions->count() > 1)
                <!-- Cabor Switcher Pill Tabs -->
                <div class="inline-flex items-center gap-1 p-1 bg-white/[0.04] border border-white/[0.10] rounded-2xl shrink-0 self-start sm:self-center sm:ml-2">
                    @foreach($tournamentCompetitions as $tc)
                        @php
                            $isCurComp = ($tc->id === $competition->id);
                            $isTcBadminton = (strtoupper($tc->code ?? '') === 'BLT') 
                                || str_contains(strtolower($tc->name ?? ''), 'bulu tangkis') 
                                || str_contains(strtolower($tc->name ?? ''), 'badminton');
                            $icon = $isTcBadminton ? '🏸' : '🏓';
                            $shortName = $isTcBadminton ? 'Bulu Tangkis' : 'Tenis Meja';

                            $targetUrl = match($activeStep ?? 'bagan') {
                                'peserta' => route('pic.dashboard') . '?competition_id=' . $tc->id,
                                'seeded' => route('pic.spin.wheel', $tc->id) . '?open_seeded=1',
                                'undian' => route('pic.spin.wheel', $tc->id),
                                'wasit' => $isTcBadminton ? route('badminton.index') : route('juri.scoring', $tc->id),
                                default => route('pic.bracket', $tc->id),
                            };
                        @endphp
                        <a href="{{ $targetUrl }}" 
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition cursor-pointer {{ $isCurComp ? 'bg-gradient-to-r from-[#7A5AF8] to-[#4E6EFF] text-white shadow-md shadow-[#7A5AF8]/30 font-black' : 'text-slate-400 hover:text-slate-200 hover:bg-white/[0.06]' }}"
                           title="Beralih ke {{ $tc->name }}">
                            <span>{{ $icon }}</span>
                            <span>{{ $shortName }}</span>
                        </a>
                    @endforeach
                </div>
                @endif
            </div>

            <!-- Right (Pojok Kanan Atas): Quick Actions (TV Bagan, Arena TV) & Titik 3 Opsi -->
            <div class="flex items-center gap-2 shrink-0 self-end sm:self-center ml-auto">
                <a href="{{ route('public.bracket', $competition->slug) }}?{{ http_build_query($poolParam) }}" 
                   target="_blank"
                   class="px-2.5 py-1.5 rounded-xl bg-white/[0.06] hover:bg-white/[0.12] text-amber-300 border border-amber-500/30 text-[11px] font-bold transition flex items-center gap-1.5 cursor-pointer"
                   title="Buka Layar TV Bagan untuk Penonton & Pemain">
                    <i data-lucide="tv" class="w-3.5 h-3.5 text-amber-400"></i>
                    <span class="hidden sm:inline">TV Bagan</span>
                </a>

                @if($isBadminton)
                <a href="{{ route('badminton.arena') }}" 
                   target="_blank"
                   class="px-2.5 py-1.5 rounded-xl bg-white/[0.06] hover:bg-white/[0.12] text-cyan-300 border border-cyan-500/30 text-[11px] font-bold transition flex items-center gap-1.5 cursor-pointer"
                   title="Buka Monitor Arena Multi-Lapangan (3 Court Monitor)">
                    <i data-lucide="layout-grid" class="w-3.5 h-3.5 text-cyan-400"></i>
                    <span class="hidden sm:inline">Arena TV</span>
                </a>
                @endif

                @if(($activeStep ?? '') === 'undian')
                <!-- Dropdown Menu Opsi Undian (Titik 3 di Pojok Kanan Atas) -->
                <div class="relative z-50" x-data="{ openMenu: false }" @click.outside="openMenu = false">
                    <button type="button" 
                            @click="openMenu = !openMenu; $nextTick(() => { if (window.lucide) window.lucide.createIcons(); })" 
                            class="p-2 rounded-xl bg-white/[0.06] hover:bg-white/[0.12] text-slate-300 hover:text-white border border-white/[0.12] transition cursor-pointer flex items-center justify-center shadow-sm"
                            title="Opsi & Pengaturan Undian">
                        <i data-lucide="more-vertical" class="w-4 h-4"></i>
                    </button>

                    <div x-show="openMenu" 
                         x-cloak 
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-56 bg-[#0B1120] border border-slate-700/90 rounded-2xl shadow-2xl py-2 z-50 text-xs">
                        
                        <div class="px-3.5 py-1.5 text-[10px] font-mono uppercase tracking-wider text-slate-400 font-bold border-b border-slate-800">
                            Opsi & Pengaturan Undian
                        </div>

                        <!-- Batch / Full-Shuffle Auto Draw -->
                        <button type="button" 
                                @click="openMenu = false; openBatchModal()"
                                class="w-full text-left flex items-center gap-2.5 px-3.5 py-2.5 text-emerald-400 hover:bg-slate-800/80 transition font-bold cursor-pointer">
                            <i data-lucide="zap" class="w-4 h-4 text-emerald-400"></i>
                            <span>⚡ Batch Auto Draw</span>
                        </button>

                        <!-- Reset Kategori Ini (Sesuai Permintaan User: Masukkan di Titik 3) -->
                        <button type="button" 
                                @click="openMenu = false; resetActivePool()"
                                :disabled="activeDrawnParticipants.length === 0 || isSpinning"
                                class="w-full text-left flex items-center gap-2.5 px-3.5 py-2.5 text-amber-400 hover:bg-amber-500/10 transition font-bold cursor-pointer disabled:opacity-30 disabled:cursor-not-allowed">
                            <i data-lucide="rotate-ccw" class="w-4 h-4 text-amber-400"></i>
                            <span>Reset Kategori Aktif</span>
                        </button>

                        <!-- Mode Hacker Link -->
                        <a href="{{ route('pic.hacker.draw', $competition->id) }}" 
                           class="flex items-center gap-2.5 px-3.5 py-2.5 text-cyan-400 hover:bg-slate-800/80 transition font-bold">
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
                                <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                                <span>Reset Semua Undian</span>
                            </button>
                        </form>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Baris Bawah: 5-Step Pipeline (Terbentang Rapi & Terarah) -->
        <div class="border-t border-white/[0.08] pt-3 flex items-center gap-1 sm:gap-2 overflow-x-auto pb-1 scrollbar-none">
            
            <!-- Step 1: Peserta -->
            <a href="{{ $pesertaUrl }}" 
               class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap {{ ($activeStep ?? '') === 'peserta' ? 'bg-gradient-to-r from-[#7A5AF8] to-[#4E6EFF] text-white shadow-md shadow-[#7A5AF8]/30 font-black' : 'bg-white/[0.04] text-slate-400 hover:text-slate-200 hover:bg-white/[0.08] border border-white/[0.06]' }}"
               title="Langkah 1: Verifikasi data peserta & pembagian pool kategori">
                <i data-lucide="users" class="w-3.5 h-3.5 {{ ($activeStep ?? '') === 'peserta' ? 'text-white' : 'text-[#4E6EFF]' }}"></i>
                <span>1. Peserta</span>
            </a>

            <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-600 shrink-0"></i>

            <!-- Step 2: Seeded (Unggulan) -->
            @if(($activeStep ?? '') === 'undian')
                <button type="button" 
                        @click="openSeededModal()"
                        class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap cursor-pointer {{ ($activeStep ?? '') === 'seeded' ? 'bg-gradient-to-r from-amber-500 to-amber-600 text-slate-950 shadow-md shadow-amber-500/30 font-black' : 'bg-amber-500/10 text-amber-300 hover:bg-amber-500/20 border border-amber-500/30' }}"
                        title="Langkah 2: Tentukan pemain unggulan (Seeded 1-8 BWF)">
                    <i data-lucide="star" class="w-3.5 h-3.5 text-amber-400"></i>
                    <span>2. Seeded</span>
                </button>
            @else
                <a href="{{ route('pic.spin.wheel', $competition->id) }}?{{ http_build_query(array_merge($poolParam, ['open_seeded' => 1])) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap {{ ($activeStep ?? '') === 'seeded' ? 'bg-gradient-to-r from-amber-500 to-amber-600 text-slate-950 shadow-md shadow-amber-500/30 font-black' : 'bg-white/[0.04] text-slate-400 hover:text-amber-300 hover:bg-amber-500/10 border border-white/[0.06]' }}"
                   title="Langkah 2: Tentukan pemain unggulan (Seeded 1-8 BWF)">
                    <i data-lucide="star" class="w-3.5 h-3.5 {{ ($activeStep ?? '') === 'seeded' ? 'text-slate-950' : 'text-amber-400' }}"></i>
                    <span>2. Seeded</span>
                </a>
            @endif

            <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-600 shrink-0"></i>

            <!-- Step 3: Undian Slot -->
            <a href="{{ route('pic.spin.wheel', $competition->id) }}?{{ http_build_query($poolParam) }}" 
               class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap {{ ($activeStep ?? '') === 'undian' ? 'bg-gradient-to-r from-[#7A5AF8] to-[#4E6EFF] text-white shadow-md shadow-[#7A5AF8]/30 font-black' : 'bg-white/[0.04] text-slate-400 hover:text-slate-200 hover:bg-white/[0.08] border border-white/[0.06]' }}"
               title="Langkah 3: Pengundian nomor bagan (Spin Wheel & Auto Draw)">
                <i data-lucide="disc" class="w-3.5 h-3.5 {{ ($activeStep ?? '') === 'undian' ? 'text-white' : 'text-[#FF58D5]' }}"></i>
                <span>3. Undian Slot</span>
            </a>

            <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-600 shrink-0"></i>

            <!-- Step 4: Bagan & Jadwal -->
            <a href="{{ route('pic.bracket', $competition->id) }}?{{ http_build_query($poolParam) }}" 
               class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap {{ ($activeStep ?? '') === 'bagan' ? 'bg-gradient-to-r from-[#7A5AF8] to-[#4E6EFF] text-white shadow-md shadow-[#7A5AF8]/30 font-black' : 'bg-white/[0.04] text-slate-400 hover:text-slate-200 hover:bg-white/[0.08] border border-white/[0.06]' }}"
               title="Langkah 4: Bagan sistem gugur BWF, format padat/play-off, dan jadwal 4 hari">
                <i data-lucide="calendar-clock" class="w-3.5 h-3.5 {{ ($activeStep ?? '') === 'bagan' ? 'text-white' : 'text-emerald-400' }}"></i>
                <span>4. Bagan & Jadwal</span>
            </a>

            <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-600 shrink-0"></i>

            <!-- Step 5: Wasit & Arena -->
            <a href="{{ $isBadminton ? route('badminton.index') : route('juri.scoring', $competition->id) }}" 
               class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap {{ ($activeStep ?? '') === 'wasit' ? 'bg-gradient-to-r from-[#7A5AF8] to-[#4E6EFF] text-white shadow-md shadow-[#7A5AF8]/30 font-black' : 'bg-white/[0.04] text-slate-400 hover:text-slate-200 hover:bg-white/[0.08] border border-white/[0.06]' }}"
               title="Langkah 5: Pelaksanaan pertandingan, scoring wasit digital & siaran arena">
                <i data-lucide="activity" class="w-3.5 h-3.5 {{ ($activeStep ?? '') === 'wasit' ? 'text-white' : 'text-cyan-400' }}"></i>
                <span>5. Wasit & Arena</span>
            </a>
        </div>

    </div>
</div>
