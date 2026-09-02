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
                <span class="text-emerald-400" x-text="undrawnParticipants.length"></span> Belum Diundi • 
                <span class="text-cyan-400" x-text="drawnList.length"></span> Selesai Terkunci
            </p>
        </div>

        <div class="flex items-center flex-wrap gap-2.5">
            <!-- Switch to Spin Wheel -->
            <a href="{{ route('pic.spin.wheel', $competition->id) }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-amber-500/10 hover:bg-amber-500/20 text-amber-400 border border-amber-500/30 font-bold text-xs transition">
                <i data-lucide="disc" class="w-4 h-4"></i>
                <span>Mode Spin Wheel</span>
            </a>

            <!-- Public Viewer -->
            <a href="{{ route('spin.viewer', $competition->slug) }}" target="_blank" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-black text-xs shadow-md shadow-emerald-500/20 transition">
                <i data-lucide="tv" class="w-4 h-4"></i>
                <span>Layar Publik</span>
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

    <!-- Main Workspace: Terminal Display on Left, Queue & Logs on Right -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- Left 7 Cols: Matrix Cyber Terminal Screen -->
        <div class="lg:col-span-7 space-y-4">
            
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

                    <!-- Big Hacker Trigger Button -->
                    <div class="pt-1">
                        <button type="button" @click="startHackerDraw()" :disabled="isDecoding || undrawnParticipants.length === 0" class="w-full py-4 px-6 rounded-2xl bg-gradient-to-r from-emerald-500 via-teal-400 to-emerald-500 hover:from-emerald-400 hover:to-teal-300 disabled:opacity-40 disabled:cursor-not-allowed text-slate-950 font-black text-sm sm:text-base tracking-wider uppercase shadow-xl shadow-emerald-500/25 hover:scale-[1.01] active:scale-[0.99] transition duration-200 flex items-center justify-center gap-3 cursor-pointer">
                            <i data-lucide="terminal" class="w-5 h-5" :class="{ 'animate-spin': isDecoding }"></i>
                            <span x-text="isDecoding ? 'SEDANG MENGACAK SELURUH KANDIDAT PESERTA...' : (undrawnParticipants.length === 0 ? 'SEMUA PESERTA TELAH SELESAI DIUNDI' : (drawMode === 'draw_slot' ? 'ACAK PESERTA UNTUK NO ' + displayNumber : 'ACAK NOMOR UNDIAN PESERTA'))"></span>
                        </button>
                    </div>

                </div>

            </div>

        </div>

        <!-- Right 5 Cols: Lists (Drawn & Undrawn) -->
        <!-- Right 5 Cols: Lists (Drawn & Undrawn in Dark Theme) -->
        <div class="lg:col-span-5 space-y-6">
            
            <!-- Confirmed / Drawn Winners Queue -->
            <div class="bg-slate-900 rounded-3xl p-6 border border-slate-800 shadow-xl space-y-4">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <h3 class="text-sm font-black text-white flex items-center gap-2">
                        <i data-lucide="list-ordered" class="w-4 h-4 text-emerald-400"></i>
                        <span>Urutan Tampil Selesai Diundi</span>
                    </h3>
                    <span class="text-xs font-mono font-bold text-emerald-300 bg-emerald-500/10 border border-emerald-500/20 px-2.5 py-0.5 rounded-full" x-text="drawnList.length + ' Peserta'"></span>
                </div>

                <div class="space-y-2.5 max-h-[320px] overflow-y-auto pr-1">
                    <template x-for="item in drawnList" :key="item.id">
                        <div class="p-3 rounded-2xl bg-slate-950/70 border border-slate-800 hover:border-slate-700 flex items-center justify-between gap-3 transition">
                            <div class="flex items-center gap-3 overflow-hidden">
                                <div class="w-10 h-10 rounded-xl bg-slate-900 text-emerald-400 font-mono font-black flex items-center justify-center text-sm shrink-0 border border-emerald-500/30 shadow-sm shadow-emerald-500/20" x-text="'#' + item.draw_number"></div>
                                <div class="overflow-hidden">
                                    <h5 class="text-xs font-bold text-slate-100 truncate" x-text="item.name"></h5>
                                    <p class="text-[11px] text-slate-400 truncate" x-text="item.institution"></p>
                                </div>
                            </div>
                            <span class="text-[10px] font-bold text-emerald-400 bg-emerald-500/20 border border-emerald-500/30 px-2.5 py-1 rounded-lg uppercase tracking-wider shrink-0">Terkunci</span>
                        </div>
                    </template>

                    <div x-show="drawnList.length === 0" class="py-8 text-center text-xs text-slate-500">
                        Belum ada peserta yang diundi.
                    </div>
                </div>
            </div>

            <!-- Undrawn Participants Queue -->
            <div class="bg-slate-900 rounded-3xl p-6 border border-slate-800 shadow-xl space-y-4">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <h3 class="text-sm font-black text-white flex items-center gap-2">
                        <i data-lucide="users" class="w-4 h-4 text-amber-400"></i>
                        <span>Antrean Belum Diundi</span>
                    </h3>
                    <span class="text-xs font-mono font-bold text-amber-300 bg-amber-500/10 border border-amber-500/20 px-2.5 py-0.5 rounded-full" x-text="undrawnParticipants.length + ' Peserta'"></span>
                </div>

                <div class="space-y-2.5 max-h-[260px] overflow-y-auto p-1 pr-2">
                    <template x-for="item in undrawnParticipants" :key="item.id">
                        <div class="p-3 rounded-2xl transition-all flex items-center justify-between gap-3 text-xs" 
                             :class="(item.id == selectedParticipantId && drawMode === 'draw_participant') ? 'border-2 border-emerald-400 bg-emerald-500/15 shadow-[0_0_15px_rgba(52,211,153,0.25)]' : 'border border-slate-800 bg-slate-950/60 hover:border-slate-700'">
                            <div class="flex items-center gap-3 overflow-hidden">
                                <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 transition"
                                     :class="(item.id == selectedParticipantId && drawMode === 'draw_participant') ? 'bg-emerald-400 text-slate-950 font-black shadow-sm' : 'bg-slate-900 text-slate-500 border border-slate-800'">
                                    <span class="text-xs font-mono font-black" x-text="(item.id == selectedParticipantId && drawMode === 'draw_participant') ? '✓' : '•'"></span>
                                </div>
                                <div class="overflow-hidden">
                                    <span class="font-bold truncate block" :class="(item.id == selectedParticipantId && drawMode === 'draw_participant') ? 'text-emerald-300 font-extrabold' : 'text-slate-200'" x-text="item.name"></span>
                                    <span class="text-[10px] text-slate-400 truncate block mt-0.5" x-text="item.institution"></span>
                                </div>
                            </div>
                            <button type="button" x-show="drawMode === 'draw_participant'" @click="selectedParticipantId = item.id" 
                                    class="text-[10px] font-bold px-3 py-1.5 rounded-lg shrink-0 transition cursor-pointer"
                                    :class="item.id == selectedParticipantId ? 'bg-emerald-400 text-slate-950 font-black shadow-sm' : 'bg-slate-800 text-slate-300 hover:bg-emerald-400 hover:text-slate-950 border border-slate-700'">
                                <span x-text="item.id == selectedParticipantId ? 'Terpilih' : 'Pilih'"></span>
                            </button>
                        </div>
                    </template>

                    <div x-show="undrawnParticipants.length === 0" class="py-6 text-center text-xs text-emerald-400 font-bold">
                        🎉 Seluruh peserta telah selesai diundi!
                    </div>
                </div>
            </div>

        </div>

    </div>

</div>

@push('scripts')
<script>
    function hackerDrawApp() {
        return {
            competitionId: {{ $competition->id }},
            undrawnParticipants: @json($undrawnList),
            drawnList: @json($drawnList),
            
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

            get nextAvailableSlot() {
                const assigned = this.drawnList.map(d => parseInt(d.draw_number));
                const total = this.undrawnParticipants.length + this.drawnList.length;
                for (let i = 1; i <= Math.max(total, 1); i++) {
                    if (!assigned.includes(i)) return i;
                }
                return 1;
            },

            get availableSlots() {
                const assigned = this.drawnList.map(d => parseInt(d.draw_number));
                const total = this.undrawnParticipants.length + this.drawnList.length;
                let slots = [];
                for (let i = 1; i <= Math.max(total, 1); i++) {
                    if (!assigned.includes(i)) slots.push(i);
                }
                return slots;
            },

            init() {
                this.updateDisplayNumber();
                if (this.undrawnParticipants.length > 0) {
                    this.selectedParticipantId = this.undrawnParticipants[0].id;
                }
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
                if (this.isDecoding || this.undrawnParticipants.length === 0) return;
                this.isDecoding = true;
                this.lockedWinner = null;

                // High entropy shuffle of the candidate pool
                const shuffledCandidates = this.cryptoShuffle(this.undrawnParticipants);

                let winnerParticipant;
                let winnerDrawNumber;

                if (this.drawMode === 'draw_slot') {
                    // MODE 1: Pick from crypto-shuffled pool
                    const randIndex = this.getCryptoRandomInt(shuffledCandidates.length);
                    winnerParticipant = shuffledCandidates[randIndex];
                    winnerDrawNumber = this.nextAvailableSlot;
                } else {
                    // MODE 2: Pick number from crypto-shuffled available slots
                    winnerParticipant = this.undrawnParticipants.find(p => p.id == this.selectedParticipantId) || shuffledCandidates[0];
                    const shuffledSlots = this.cryptoShuffle(this.availableSlots);
                    if (shuffledSlots.length > 0) {
                        const randSlotIdx = this.getCryptoRandomInt(shuffledSlots.length);
                        winnerDrawNumber = shuffledSlots[randSlotIdx];
                    } else {
                        winnerDrawNumber = this.drawnList.length + 1;
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
                        const pIdx = this.undrawnParticipants.findIndex(p => p.id == winnerParticipant.id);
                        if (pIdx > -1) {
                            const p = this.undrawnParticipants.splice(pIdx, 1)[0];
                            p.draw_number = winnerDrawNumber;
                            this.drawnList.push(p);
                            // Sort by draw number ascending
                            this.drawnList.sort((a, b) => parseInt(a.draw_number) - parseInt(b.draw_number));
                        }

                        if (this.undrawnParticipants.length > 0) {
                            this.selectedParticipantId = this.undrawnParticipants[0].id;
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
            }
        }
    }
</script>
@endpush
@endsection
