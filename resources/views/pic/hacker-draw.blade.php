@extends('layouts.admin')

@section('title', 'Hacker Terminal Undian - ' . $competition->name)
@section('page_title', 'Undi Peserta (Hacker Live Decoder)')

@section('content')
<div class="space-y-6 font-sans" x-data="hackerDrawApp()">
    
    <!-- Top Action Bar -->
    <div class="bg-slate-900 rounded-3xl p-5 sm:p-7 border border-slate-800 shadow-xl flex flex-col sm:flex-row sm:items-center justify-between gap-4 text-white">
        <div class="space-y-1">
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 text-[10px] font-mono font-bold rounded bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 uppercase">
                    SYS_MODULE: DECODER_DRAW_V3
                </span>
                <span class="px-2.5 py-0.5 text-[10px] font-mono font-bold rounded bg-cyan-500/20 text-cyan-400 border border-cyan-500/30 uppercase">
                    {{ $competition->category->name }}
                </span>
            </div>
            <h2 class="text-xl sm:text-2xl font-black text-white tracking-tight flex items-center gap-2">
                <span>{{ $competition->name }}</span>
            </h2>
            <p class="text-xs text-slate-400 font-mono">
                <span class="text-emerald-400 font-bold" x-text="allUndrawnParticipants.length"></span> Belum Diundi • 
                <span class="text-cyan-400 font-bold" x-text="allDrawnParticipants.length"></span> Selesai Terkunci
            </p>
        </div>

        <div class="flex items-center flex-wrap gap-2.5">
            <!-- Batch / Full-Shuffle Auto Draw -->
            <button type="button" 
                    @click="openBatchModal()" 
                    :disabled="isDecoding || allUndrawnParticipants.length === 0"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-gradient-to-r from-emerald-500/20 to-teal-500/20 hover:from-emerald-500/30 hover:to-teal-500/30 text-emerald-300 border border-emerald-500/50 font-bold text-xs shadow-md transition cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed"
                    title="Undi Semua Peserta Sekaligus dalam 1 Kali Putar">
                <i data-lucide="zap" class="w-4 h-4 text-emerald-400"></i>
                <span>⚡ Batch / Full-Shuffle Auto Draw</span>
            </button>

            <!-- Switch to Spin Wheel -->
            <a href="{{ route('pic.spin.wheel', $competition->id) }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-amber-500/10 hover:bg-amber-500/20 text-amber-400 border border-amber-500/30 font-bold text-xs transition">
                <i data-lucide="disc" class="w-4 h-4"></i>
                <span>Mode Spin Wheel</span>
            </a>

            <!-- Menu Seeded Button -->
            <button type="button" 
                    @click="openSeededModal()" 
                    :disabled="isDecoding"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-gradient-to-r from-amber-500/20 to-amber-600/20 hover:from-amber-500/30 hover:to-amber-600/30 text-amber-300 border border-amber-500/50 font-bold text-xs shadow-md transition cursor-pointer"
                    title="Menu Pengaturan Pemain Unggulan (Seeded)">
                <i data-lucide="star" class="w-4 h-4 text-amber-400"></i>
                <span>Menu Seeded</span>
            </button>

            @if(strtoupper($competition->code ?? '') === 'BLT' || str_contains(strtolower($competition->name ?? ''), 'bulu tangkis') || str_contains(strtolower($competition->name ?? ''), 'badminton'))
            <!-- Bagan Pertandingan -->
            <a :href="'{{ route('pic.bracket', $competition->id) }}' + (activePoolKey ? '?pool=' + encodeURIComponent(activePoolKey) : '')" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-gradient-to-r from-indigo-500/20 to-blue-600/20 hover:from-indigo-500/30 hover:to-blue-600/30 text-indigo-300 border border-indigo-500/50 font-bold text-xs shadow-md transition cursor-pointer" title="Lihat Bagan Pertandingan (Knockout Bracket)">
                <i data-lucide="git-branch" class="w-4 h-4 text-indigo-400"></i>
                <span>Bagan Pertandingan</span>
            </a>
            @endif

            <!-- Public Viewer TV -->
            <a href="{{ url('tv/' . $competition->slug) }}" target="_blank" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-black text-xs shadow-md shadow-emerald-500/20 transition" title="Link Cepat TV: /tv/{{ $competition->slug }}">
                <i data-lucide="tv" class="w-4 h-4"></i>
                <span>Layar TV (/tv/{{ $competition->slug }})</span>
            </a>

            <!-- Reset All -->
            <form action="{{ route('pic.spin.wheel.reset', $competition->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin me-reset SEMUA nomor undian pada cabang {{ addslashes($competition->name) }}?')">
                @csrf
                <button type="submit" class="px-3.5 py-2 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/30 font-bold text-xs transition cursor-pointer">
                    Reset Semua Undian
                </button>
            </form>

            <a href="{{ route('pic.undian') }}" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-xs transition">
                Kembali
            </a>
        </div>
    </div>

    <!-- Category & Sector Navigation Tabs (Bulu Tangkis & Tenis Meja Pools) -->
    @if(count($pools) > 1)
    <div class="bg-slate-900 rounded-3xl p-4 sm:p-5 border border-slate-800 shadow-xl space-y-3">
        <div class="flex items-center justify-between gap-3 px-1">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                <span class="text-xs font-mono font-black uppercase tracking-wider text-emerald-400">Pilih Kategori Kelas & Sektor (PA / PI):</span>
            </div>
            <span class="text-[11px] font-mono text-slate-400">
                <span class="text-white font-bold" x-text="pools.length"></span> Kategori / Kelompok Terdaftar
            </span>
        </div>
        
        <div class="flex items-center gap-2.5 overflow-x-auto pb-1.5 scrollbar-thin scrollbar-thumb-slate-700">
            <template x-for="p in pools" :key="p.key">
                <button type="button" 
                        @click="switchPool(p.key)"
                        class="px-4 py-2.5 rounded-2xl font-bold text-xs transition-all duration-200 flex items-center gap-2.5 whitespace-nowrap cursor-pointer shrink-0 border font-mono"
                        :class="activePoolKey === p.key 
                            ? 'bg-gradient-to-r from-emerald-500 to-teal-500 text-slate-950 border-emerald-300 shadow-lg shadow-emerald-500/25 scale-[1.02] font-black' 
                            : 'bg-slate-950 text-slate-300 border-slate-800 hover:border-slate-700 hover:text-white'">
                    <span x-text="p.short_title"></span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-black transition"
                          :class="activePoolKey === p.key 
                              ? 'bg-slate-950 text-emerald-400' 
                              : (getPoolStats(p.key).undrawn === 0 ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-slate-800 text-slate-400')">
                        <span x-text="getPoolStats(p.key).drawn + '/' + p.participants.length"></span>
                    </span>
                </button>
            </template>
        </div>
    </div>
    @endif

    <!-- Main Workspace: Terminal Display on Left, Queue & Logs on Right -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- Left 7 Cols: Matrix Cyber Terminal Screen -->
        <div class="lg:col-span-7 space-y-4">
            
            <!-- Active Pool Status Banner -->
            <div class="bg-slate-900 rounded-2xl p-3 border border-emerald-500/30 flex items-center justify-between gap-3 text-left font-mono">
                <div class="overflow-hidden">
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-400">Kategori Aktif:</span>
                        <span class="text-[10px] px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-300 font-bold" x-text="activeDrawnParticipants.length + '/' + activeParticipants.length + ' Selesai'"></span>
                    </div>
                    <h4 class="text-sm font-black text-white truncate mt-0.5" x-text="activePool?.title || '{{ $competition->name }}'"></h4>
                </div>
                <div class="shrink-0 flex items-center gap-1.5">
                    <button type="button" 
                            @click="openSeededModal()" 
                            :disabled="isDecoding"
                            class="px-2.5 py-1.5 rounded-xl bg-amber-500/20 hover:bg-amber-500/30 text-amber-300 border border-amber-500/40 text-[10px] font-bold transition flex items-center gap-1 cursor-pointer"
                            title="Atur Pemain Unggulan (Seeded) untuk kategori ini">
                        <span>⭐ Atur Seeded</span>
                    </button>
                    <button type="button" 
                            @click="resetActivePool()" 
                            :disabled="activeDrawnParticipants.length === 0 || isDecoding"
                            class="px-2.5 py-1.5 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/30 text-[10px] font-bold disabled:opacity-25 disabled:cursor-not-allowed transition cursor-pointer"
                            title="Reset hanya nomor undian kategori ini">
                        Reset
                    </button>
                </div>
            </div>
            
            <div class="relative bg-slate-950 rounded-3xl p-6 sm:p-8 border-2 border-emerald-500/30 shadow-2xl shadow-emerald-950/40 text-emerald-400 font-mono overflow-hidden">
                
                <!-- Background Scanlines & Glow Overlay -->
                <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-emerald-950/30 via-slate-950 to-slate-950 pointer-events-none"></div>
                <div class="absolute inset-0 bg-[linear-gradient(rgba(16,185,129,0.03)_1px,transparent_1px),linear-gradient(90deg,rgba(16,185,129,0.03)_1px,transparent_1px)] bg-[size:24px_24px] pointer-events-none"></div>

                <div class="relative z-10 space-y-5">
                    
                    <!-- Terminal Top Bar -->
                    <div class="flex items-center justify-between border-b border-emerald-500/20 pb-3 text-xs">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span class="font-black tracking-widest text-emerald-400 uppercase">CSPRNG_ENTROPY_ENGINE // 100% RANDOM</span>
                        </div>
                        <div class="flex items-center gap-3 text-[11px] text-emerald-600 font-mono">
                            <span>SFX: <button type="button" @click="soundEnabled = !soundEnabled" class="text-emerald-400 font-bold underline cursor-pointer" x-text="soundEnabled ? 'ON' : 'OFF'"></button></span>
                        </div>
                    </div>

                    <!-- Scramble Duration & Mode Controls -->
                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-2 text-xs">
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
                            <button type="button" @click="shuffleDuration = 3000" :class="shuffleDuration === 3000 ? 'bg-emerald-500 text-slate-950 font-black' : 'text-emerald-400 hover:text-white'" class="flex-1 py-1.5 rounded-lg text-center font-bold transition">3s</button>
                            <button type="button" @click="shuffleDuration = 5000" :class="shuffleDuration === 5000 ? 'bg-emerald-500 text-slate-950 font-black' : 'text-emerald-400 hover:text-white'" class="flex-1 py-1.5 rounded-lg text-center font-bold transition">5s</button>
                            <button type="button" @click="shuffleDuration = 8000" :class="shuffleDuration === 8000 ? 'bg-emerald-500 text-slate-950 font-black' : 'text-emerald-400 hover:text-white'" class="flex-1 py-1.5 rounded-lg text-center font-bold transition">8s 🔥</button>
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
                            <span class="text-4xl sm:text-5xl font-black tracking-widest text-emerald-300 drop-shadow-[0_0_15px_rgba(52,211,153,0.8)]" x-text="displayNumber">
                                #01
                            </span>
                        </div>
                    </div>

                    <!-- High-Speed Live Stream Feed (Shows real candidate stream cycling like in movies) -->
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

                        <!-- Scrambled School Display (Taruh rapi di bawah nama) -->
                        <div class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full bg-slate-800/80 border border-emerald-500/30 text-xs sm:text-sm font-semibold text-emerald-300 tracking-wide max-w-md truncate shadow-inner"
                             x-text="displaySchool">
                            Tekan tombol di bawah untuk mengacak seluruh nama peserta
                        </div>

                        <!-- Target Locked Status Badge -->
                        <div x-show="lockedWinner" x-transition class="pt-2">
                            <span class="px-4 py-1.5 rounded-full bg-emerald-500 text-slate-950 font-black text-xs tracking-widest uppercase shadow-md shadow-emerald-400/30 inline-flex items-center gap-1.5">
                                <i data-lucide="check-circle" class="w-4 h-4"></i>
                                <span>BERHASIL DIKUNCI (TARGET LOCKED)</span>
                            </span>
                        </div>

                        <!-- BWF Separation Alert if same school detected -->
                        <div x-show="bwfNotification" x-transition class="mt-3 p-3 rounded-xl bg-indigo-950/80 border border-indigo-500/50 text-left flex items-start gap-2.5 shadow-lg max-w-md">
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
                    <div x-show="drawMode === 'draw_participant'" x-transition class="space-y-3 pt-1">
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-emerald-600 mb-1">Pilih Peserta yang Ingin Diundi:</label>
                            <select x-model="selectedParticipantId" :disabled="isDecoding || undrawnParticipants.length === 0" class="w-full px-3 py-2.5 rounded-xl bg-slate-900 border border-emerald-500/30 text-emerald-200 text-xs font-mono outline-none focus:border-emerald-400">
                                <template x-for="p in undrawnParticipants" :key="p.id">
                                    <option :value="p.id" x-text="p.name + ' (' + p.institution + ')'"></option>
                                </template>
                            </select>
                        </div>
                    </div>

                    <!-- Trigger Buttons (1-by-1 vs Batch All) -->
                    <div class="space-y-2.5 pt-1">
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

        <!-- Right 5 Cols: Lists (Drawn & Undrawn in Dark Theme) -->
        <div class="lg:col-span-5 space-y-6">
            
            <!-- Confirmed / Drawn Winners Queue -->
            <div class="bg-slate-900 rounded-3xl p-6 border border-slate-800 shadow-xl space-y-4">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <div class="flex items-center gap-2">
                        <i data-lucide="list-ordered" class="w-4 h-4 text-emerald-400"></i>
                        <h3 class="text-sm font-black text-white font-mono">Urutan Tampil Selesai Diundi</h3>
                    </div>
                    <!-- Filter Toggle: Kategori Ini vs Semua -->
                    <div class="inline-flex items-center p-0.5 bg-slate-950 rounded-xl border border-slate-800 text-[10px] font-mono">
                        <button type="button" @click="drawnTab = 'pool'" :class="drawnTab === 'pool' ? 'bg-emerald-500 text-slate-950 font-black' : 'text-slate-400 hover:text-white'" class="px-2.5 py-0.5 rounded-lg transition font-bold cursor-pointer">
                            Kategori Ini (<span x-text="activeDrawnParticipants.length"></span>)
                        </button>
                        <button type="button" @click="drawnTab = 'all'" :class="drawnTab === 'all' ? 'bg-emerald-500 text-slate-950 font-black' : 'text-slate-400 hover:text-white'" class="px-2.5 py-0.5 rounded-lg transition font-bold cursor-pointer">
                            Semua (<span x-text="allDrawnParticipants.length"></span>)
                        </button>
                    </div>
                </div>

                <div class="space-y-2.5 max-h-[320px] overflow-y-auto pr-1">
                    <template x-for="item in (drawnTab === 'pool' ? activeDrawnParticipants : allDrawnParticipants)" :key="item.id">
                        <div class="p-3 rounded-2xl border flex items-center justify-between gap-3 transition duration-300 font-mono"
                             :class="recentlyDrawnId === item.id ? 'bg-emerald-500/20 border-emerald-400 shadow-[0_0_15px_rgba(52,211,153,0.35)] scale-[1.02]' : 'bg-slate-950/70 border-slate-800 hover:border-slate-700'">
                            <div class="flex items-center gap-3 overflow-hidden">
                                <div class="w-10 h-10 rounded-xl bg-slate-900 text-emerald-400 font-mono font-black flex items-center justify-center text-sm shrink-0 border border-emerald-500/30 shadow-sm shadow-emerald-500/20" x-text="'#' + item.draw_number"></div>
                                <div class="overflow-hidden">
                                    <div class="flex items-center gap-2">
                                        <h5 class="text-xs font-bold text-slate-100 truncate font-sans" x-text="item.name"></h5>
                                        <span x-show="item.is_seeded" class="px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-amber-500/20 text-amber-300 border border-amber-500/40 shrink-0 font-sans" x-text="'⭐ ' + (item.seed_label || 'SEED')"></span>
                                    </div>
                                    <div class="flex items-center gap-1.5 text-[11px] text-slate-400 truncate">
                                        <span x-text="item.institution"></span>
                                        <span x-show="drawnTab === 'all' && item.target_class" class="text-emerald-400 font-mono text-[10px]" x-text="'• ' + (item.target_class || '')"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-1.5 shrink-0">
                                <span x-show="item.is_seeded" class="text-[10px] font-black text-amber-400 bg-amber-500/15 border border-amber-500/30 px-2 py-0.5 rounded-lg uppercase tracking-wider font-sans">Seeded</span>
                                <span class="text-[10px] font-bold text-emerald-400 bg-emerald-500/20 border border-emerald-500/30 px-2.5 py-1 rounded-lg uppercase tracking-wider">Terkunci</span>
                            </div>
                        </div>
                    </template>

                    <div x-show="(drawnTab === 'pool' ? activeDrawnParticipants : allDrawnParticipants).length === 0" class="py-8 text-center text-xs text-slate-500 font-mono">
                        Belum ada peserta yang diundi pada filter ini.
                    </div>
                </div>
            </div>

            <!-- Undrawn Participants Queue -->
            <div class="bg-slate-900 rounded-3xl p-6 border border-slate-800 shadow-xl space-y-4">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <h3 class="text-sm font-black text-white flex items-center gap-2 font-mono">
                        <i data-lucide="users" class="w-4 h-4 text-amber-400"></i>
                        <span>Antrean Belum Diundi</span>
                    </h3>
                    <span class="text-xs font-mono font-bold text-amber-300 bg-amber-500/10 border border-amber-500/20 px-2.5 py-0.5 rounded-full" x-text="activeUndrawnParticipants.length + ' Peserta'"></span>
                </div>

                <div class="space-y-2.5 max-h-[260px] overflow-y-auto p-1 pr-2">
                    <template x-for="item in activeUndrawnParticipants" :key="item.id">
                        <div class="p-3 rounded-2xl transition-all flex items-center justify-between gap-3 text-xs" 
                             :class="(item.id == selectedParticipantId && drawMode === 'draw_participant') ? 'border-2 border-emerald-400 bg-emerald-500/15 shadow-[0_0_15px_rgba(52,211,153,0.25)]' : 'border border-slate-800 bg-slate-950/60 hover:border-slate-700'">
                            <div class="flex items-center gap-3 overflow-hidden">
                                <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 transition"
                                     :class="(item.id == selectedParticipantId && drawMode === 'draw_participant') ? 'bg-emerald-400 text-slate-950 font-black shadow-sm' : 'bg-slate-900 text-slate-500 border border-slate-800'">
                                    <span class="text-xs font-mono font-black" x-text="(item.id == selectedParticipantId && drawMode === 'draw_participant') ? '✓' : '•'"></span>
                                </div>
                                <div class="overflow-hidden">
                                    <span class="font-bold truncate block font-sans" :class="(item.id == selectedParticipantId && drawMode === 'draw_participant') ? 'text-emerald-300 font-extrabold' : 'text-slate-200'" x-text="item.name"></span>
                                    <div class="flex items-center gap-1.5 mt-0.5">
                                        <span class="text-[10px] text-slate-400 truncate block font-mono" x-text="item.institution"></span>
                                        <template x-if="hasTeammatesInPool(item)">
                                            <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 shrink-0 font-mono" title="Sekolah ini memiliki rekan satu delegasi di kategori ini (Proteksi BWF Aktif)">
                                                🛡️ Satu Delegasi
                                            </span>
                                        </template>
                                    </div>
                                </div>
                            </div>
                            <button type="button" x-show="drawMode === 'draw_participant'" @click="selectedParticipantId = item.id" 
                                    class="text-[10px] font-bold px-3 py-1.5 rounded-lg shrink-0 transition cursor-pointer font-mono"
                                    :class="item.id == selectedParticipantId ? 'bg-emerald-400 text-slate-950 font-black shadow-sm' : 'bg-slate-800 text-slate-300 hover:bg-emerald-400 hover:text-slate-950 border border-slate-700'">
                                <span x-text="item.id == selectedParticipantId ? 'Terpilih' : 'Pilih'"></span>
                            </button>
                        </div>
                    </template>

                    <div x-show="activeUndrawnParticipants.length === 0" class="py-6 text-center text-xs text-emerald-400 font-bold font-mono">
                        🎉 Seluruh peserta dalam kategori ini telah selesai diundi!
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
        
        <div class="bg-slate-900 border border-slate-700 rounded-3xl p-6 sm:p-7 max-w-2xl w-full max-h-[90vh] flex flex-col shadow-2xl relative text-white space-y-4 font-mono"
             @click.away="if (!isSavingSeeded) isSeededModalOpen = false">
            
            <!-- Modal Header -->
            <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                <div class="space-y-1 font-sans">
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-0.5 text-[10px] font-mono font-bold rounded bg-amber-500/20 text-amber-300 border border-amber-500/40 uppercase">
                            ⭐ ATUR PEMAIN UNGGULAN (SEEDED)
                        </span>
                    </div>
                    <h3 class="text-lg sm:text-xl font-black text-white" x-text="'Pengaturan Seeded - ' + (activePool?.short_title || activePool?.title || 'Kategori')"></h3>
                    <p class="text-xs text-slate-400">
                        Pilih langsung siapa pemain unggulan untuk tiap posisi Seed. Pemain unggulan otomatis menempati slot tetap bagan dan <strong class="text-amber-400">tidak diundi dalam pengacakan</strong>.
                    </p>
                </div>
                <button type="button" @click="isSeededModalOpen = false" :disabled="isSavingSeeded" class="p-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800 transition cursor-pointer">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Seed Menu Controls Toolbar -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-950/80 p-3.5 rounded-2xl border border-slate-800 font-sans">
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
                            @click="setPresetSeeds(12)" 
                            :disabled="activeParticipants.length < 12" 
                            class="px-3 py-1.5 rounded-xl text-xs font-bold transition cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed"
                            :class="visibleSeeds.length === 12 ? 'bg-amber-500 text-slate-950 font-black shadow-sm' : 'bg-slate-800 text-slate-300 hover:bg-slate-700'"
                            :title="activeParticipants.length < 12 ? 'Minimal 12 peserta untuk 12 seed' : 'Tampilkan 12 Seed'">
                        12 Seed
                    </button>
                    <button type="button" 
                            @click="setPresetSeeds(16)" 
                            :disabled="activeParticipants.length < 16" 
                            class="px-3 py-1.5 rounded-xl text-xs font-bold transition cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed"
                            :class="visibleSeeds.length === 16 ? 'bg-amber-500 text-slate-950 font-black shadow-sm' : 'bg-slate-800 text-slate-300 hover:bg-slate-700'"
                            :title="activeParticipants.length < 16 ? 'Minimal 16 peserta untuk 16 seed' : 'Tampilkan 16 Seed'">
                        16 Seed
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
            <div class="flex-1 overflow-y-auto space-y-3 pr-1 max-h-[420px] font-sans">
                
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
            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-800 font-sans">
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

</div>

@push('scripts')
<script>
    function hackerDrawApp() {
        return {
            competitionId: {{ $competition->id }},
            pools: @json($pools),
            activePoolKey: '{{ $pools[0]['key'] ?? 'all' }}',
            drawnTab: 'pool',
            
            // Mode & Configuration
            drawMode: 'draw_slot',
            selectedParticipantId: null,
            shuffleDuration: 5000, // 3000ms, 5000ms, or 8000ms
            
            // Animation States
            isDecoding: false,
            lockedWinner: null,
            displayName: 'SIAP UNTUK DIUNDI',
            displaySchool: 'Tekan tombol MULAI PENGACAKAN untuk mengundi nama',
            displayNumber: '#01',
            radarTicker: 'IDLE',
            soundEnabled: true,
            audioCtx: null,
            bwfNotification: '',

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

                        if (typeof this.playLockSound === 'function') {
                            this.playLockSound();
                        }

                        if (typeof confetti === 'function') {
                            confetti({ particleCount: 160, spread: 90, origin: { y: 0.6 } });
                        }

                        setTimeout(() => {
                            this.isBatchModalOpen = false;
                            this.isProcessingBatch = false;
                            this.updateDisplayNumber();
                        }, 1400);
                        return;
                    }

                    // Cascade Hacker Mode: Close modal and launch sequential hacker cipher decoder
                    this.isBatchModalOpen = false;
                    this.isProcessingBatch = false;

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
            selectedSeeds: { 1: '', 2: '', 3: '', 4: '', 5: '', 6: '', 7: '', 8: '', 9: '', 10: '', 11: '', 12: '', 13: '', 14: '', 15: '', 16: '' },
            isSavingSeeded: false,

            get maxAvailableSeeds() {
                const total = this.activeParticipants.length;
                if (total <= 2) return Math.max(total, 1);
                if (total < 8) return 4;
                if (total < 12) return 8;
                return Math.min(16, total);
            },

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

            init() {
                this.updateDisplayNumber();
                if (this.activeUndrawnParticipants.length > 0) {
                    this.selectedParticipantId = this.activeUndrawnParticipants[0].id;
                }
            },

            switchPool(key) {
                if (this.isDecoding || this.isBatchRunning) return;
                this.activePoolKey = key;
                this.lockedWinner = null;
                this.displayName = 'SIAP UNTUK DIUNDI';
                this.displaySchool = 'Tekan tombol MULAI PENGACAKAN untuk mengundi nama';
                if (this.activeUndrawnParticipants.length > 0) {
                    this.selectedParticipantId = this.activeUndrawnParticipants[0].id;
                } else {
                    this.selectedParticipantId = null;
                }
                this.updateDisplayNumber();
            },

            resetActivePool() {
                if (!this.activePool || this.activeDrawnParticipants.length === 0) return;
                const password = window.prompt('KONFIRMASI RESET: Masukkan Password Admin untuk me-reset kategori "' + this.activePool.title + '":');
                if (!password) return;

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

                const pwdInput = document.createElement('input');
                pwdInput.type = 'hidden';
                pwdInput.name = 'admin_password';
                pwdInput.value = password;
                form.appendChild(pwdInput);

                document.body.appendChild(form);
                form.submit();
            },

            updateDisplayNumber() {
                if (this.drawMode === 'draw_slot') {
                    const nextSlot = this.nextAvailableSlot;
                    this.displayNumber = '#' + String(nextSlot).padStart(2, '0');
                } else {
                    this.displayNumber = '#??';
                }
            },

            // Cryptographically Secure Random Int Generator
            getCryptoRandomInt(max) {
                if (max <= 0) return 0;
                const array = new Uint32Array(1);
                window.crypto.getRandomValues(array);
                return array[0] % max;
            },

            // Multi-pass Fisher-Yates array shuffler with crypto entropy
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

            playBeep(freq = 600, duration = 0.03, type = 'sine') {
                if (!this.soundEnabled) return;
                try {
                    if (!this.audioCtx) this.audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                    const osc = this.audioCtx.createOscillator();
                    const gain = this.audioCtx.createGain();
                    osc.type = type;
                    osc.frequency.setValueAtTime(freq, this.audioCtx.currentTime);
                    gain.gain.setValueAtTime(0.07, this.audioCtx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.001, this.audioCtx.currentTime + duration);
                    osc.connect(gain);
                    gain.connect(this.audioCtx.destination);
                    osc.start();
                    osc.stop(this.audioCtx.currentTime + duration);
                } catch(e) {}
            },

            playLockSound() {
                if (!this.soundEnabled) return;
                try {
                    if (!this.audioCtx) this.audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                    
                    // Rich multi-harmonic cinematic lock chord
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

                // High entropy shuffle of the candidate pool
                const shuffledCandidates = this.cryptoShuffle(this.activeUndrawnParticipants);

                let winnerParticipant;
                let winnerDrawNumber;

                if (this.drawMode === 'draw_slot') {
                    // MODE 1: Pick from crypto-shuffled pool
                    const randIndex = this.getCryptoRandomInt(shuffledCandidates.length);
                    winnerParticipant = shuffledCandidates[randIndex];
                    winnerDrawNumber = this.nextAvailableSlot;
                } else {
                    // MODE 2: Pick number from crypto-shuffled available slots
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

                // High-intensity glitch characters
                const glitchChars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%&*<>/=[]{}?+~§µ";
                const totalDuration = this.shuffleDuration;
                const startTime = performance.now();
                let frameCount = 0;

                const animate = (currentTime) => {
                    const elapsed = currentTime - startTime;
                    const progress = Math.min(elapsed / totalDuration, 1);
                    frameCount++;

                    // PHASE 1: HYPER-SPEED REAL CANDIDATE CYCLING (0% to 65%)
                    if (progress < 0.65) {
                        // Pick random candidate each frame for realistic rapid name switching
                        const randCandidate = shuffledCandidates[this.getCryptoRandomInt(shuffledCandidates.length)];
                        const baseName = randCandidate ? randCandidate.name : targetName;
                        
                        // Apply intense glitch character scattering
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

                        // High frequency laser beep
                        if (frameCount % 2 === 0) {
                            this.playBeep(450 + (Math.sin(frameCount) * 350) + this.getCryptoRandomInt(200), 0.025, 'square');
                        }
                    } 
                    // PHASE 2: DECELERATION & CHARACTER-BY-CHARACTER RESOLUTION (65% to 100%)
                    else {
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

                        // Ascending resolution chime
                        if (frameCount % 3 === 0) {
                            this.playBeep(600 + (resolveRatio * 600), 0.04, 'triangle');
                        }
                    }

                    if (progress < 1) {
                        // Exponential easing delay for intense movie deceleration
                        const stepDelay = progress > 0.70 ? (progress - 0.70) * 160 : 20;
                        setTimeout(() => {
                            requestAnimationFrame(animate);
                        }, stepDelay);
                    } else {
                        // FINAL LOCK-IN & CELEBRATION
                        this.displayName = targetName;
                        this.displaySchool = targetSchool;
                        this.displayNumber = targetNumStr;
                        this.radarTicker = 'TARGET_LOCKED >> ' + targetName;
                        this.isDecoding = false;
                        this.lockedWinner = {
                            participant: winnerParticipant,
                            drawNumber: winnerDrawNumber
                        };

                        // Check BWF Separation Notification
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

                        // Confetti Celebration
                        if (typeof confetti === 'function') {
                            confetti({
                                particleCount: 140,
                                spread: 90,
                                origin: { y: 0.6 }
                            });
                        }

                        // IMMEDIATELY UPDATE CLIENT STATE (Instant Snappy UI)
                        winnerParticipant.is_drawn = true;
                        winnerParticipant.draw_number = winnerDrawNumber;

                        if (this.activeUndrawnParticipants.length > 0) {
                            this.selectedParticipantId = this.activeUndrawnParticipants[0].id;
                        } else {
                            this.selectedParticipantId = null;
                        }
                        this.updateDisplayNumber();

                        // Auto-save to backend in background
                        this.saveDrawResult(winnerParticipant.id, winnerDrawNumber);
                    }
                };

                requestAnimationFrame(animate);
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
                .catch(err => console.error('Save draw network error:', err));
            },

            openSeededModal() {
                this.selectedSeeds = { 1: '', 2: '', 3: '', 4: '', 5: '', 6: '', 7: '', 8: '', 9: '', 10: '', 11: '', 12: '', 13: '', 14: '', 15: '', 16: '' };
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
                let badgeColor = 'bg-amber-500/20 text-amber-300 border border-amber-500/40 font-sans';

                if (seedNum === 1) {
                    desc = 'Puncak Bagan Atas (Finalis 1)';
                    badgeColor = 'bg-amber-500 text-slate-950 font-black font-sans';
                } else if (seedNum === 2) {
                    desc = 'Dasar Bagan Bawah (Finalis 2)';
                    badgeColor = 'bg-amber-500/25 text-amber-300 border border-amber-500/50 font-sans';
                } else if (seedNum === 3) {
                    desc = 'Bagan Bawah (Semifinalis 1)';
                } else if (seedNum === 4) {
                    desc = 'Bagan Atas (Semifinalis 2)';
                } else if (seedNum === 5) {
                    desc = 'Bagan Atas (Perempat Final 2)';
                } else if (seedNum === 6) {
                    desc = 'Bagan Bawah (Perempat Final 3)';
                } else if (seedNum === 7) {
                    desc = 'Bagan Bawah (Perempat Final 4)';
                } else if (seedNum === 8) {
                    desc = 'Bagan Atas (Perempat Final 1)';
                } else if (seedNum >= 9 && seedNum <= 16) {
                    desc = 'Sektor Babak 16 Besar (Seed ' + seedNum + ')';
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
                    case 9: baseSlot = Math.floor(total / 8) + 1; break;
                    case 10: baseSlot = Math.floor(7 * total / 8); break;
                    case 11: baseSlot = Math.floor(5 * total / 8) + 1; break;
                    case 12: baseSlot = Math.floor(3 * total / 8); break;
                    case 13: baseSlot = Math.floor(3 * total / 8) + 1; break;
                    case 14: baseSlot = Math.floor(5 * total / 8); break;
                    case 15: baseSlot = Math.floor(7 * total / 8) + 1; break;
                    case 16: baseSlot = Math.floor(total / 8); break;
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
                        case 9: sSlot = Math.floor(total / 8) + 1; break;
                        case 10: sSlot = Math.floor(7 * total / 8); break;
                        case 11: sSlot = Math.floor(5 * total / 8) + 1; break;
                        case 12: sSlot = Math.floor(3 * total / 8); break;
                        case 13: sSlot = Math.floor(3 * total / 8) + 1; break;
                        case 14: sSlot = Math.floor(5 * total / 8); break;
                        case 15: sSlot = Math.floor(7 * total / 8) + 1; break;
                        case 16: sSlot = Math.floor(total / 8); break;
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
            }
        }
    }
</script>
@endpush
@endsection
