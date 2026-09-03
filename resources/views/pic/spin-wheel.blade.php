@extends('layouts.admin')

@section('title', 'Spin Wheel Undian Nomor Tampil - ' . $competition->name)
@section('page_title', 'Interactive Spin Wheel Undian')

@section('content')
<div class="space-y-6 font-sans" x-data="spinWheelApp()">
    
    <!-- Top Action Bar (Cohesive Dark Card matching Hacker Draw) -->
    <div class="bg-slate-900 rounded-3xl p-5 sm:p-7 border border-slate-800 shadow-xl flex flex-col sm:flex-row sm:items-center justify-between gap-4 text-white">
        <div class="space-y-1">
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 text-[10px] font-mono font-bold rounded bg-amber-500/20 text-amber-400 border border-amber-500/30 uppercase">
                    SYS_MODULE: SPIN_WHEEL
                </span>
                <span class="px-2.5 py-0.5 text-[10px] font-mono font-bold rounded bg-cyan-500/20 text-cyan-400 border border-cyan-500/30 uppercase">
                    {{ $competition->category->name }}
                </span>
            </div>
            <h2 class="text-xl sm:text-2xl font-black text-white tracking-tight flex items-center gap-2">
                <span>{{ $competition->name }}</span>
            </h2>
            <p class="text-xs text-slate-400 font-mono">
                <span class="text-amber-400 font-bold" x-text="undrawnParticipants.length"></span> Belum Diundi • 
                <span class="text-emerald-400 font-bold" x-text="drawnList.length"></span> Selesai Terkunci
            </p>
        </div>

        <div class="flex items-center flex-wrap gap-2.5">
            <!-- Switch to Hacker Draw -->
            <a href="{{ route('pic.hacker.draw', $competition->id) }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-emerald-400 border border-emerald-500/30 font-bold text-xs shadow-md transition">
                <i data-lucide="terminal" class="w-4 h-4 text-emerald-400"></i>
                <span>Mode Hacker</span>
            </a>

            <!-- Public Viewer TV -->
            <a href="{{ url('tv/' . $competition->slug) }}" target="_blank" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-black text-xs shadow-md shadow-amber-500/20 transition" title="Link Cepat TV: /tv/{{ $competition->slug }}">
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

    <!-- Main Workspace Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- Left 7 Cols: Interactive Canvas Wheel -->
        <div class="lg:col-span-7 bg-slate-950 rounded-3xl p-6 sm:p-8 border-2 border-amber-500/30 shadow-2xl shadow-slate-950/50 flex flex-col items-center justify-center text-center relative overflow-hidden">
            
            <!-- Background Glow Overlay -->
            <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-amber-950/20 via-slate-950 to-slate-950 pointer-events-none"></div>

            <div class="relative z-10 w-full flex flex-col items-center">
                <!-- Wheel Pointer Arrow -->
                <div class="relative mb-5">
                    <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 z-20 w-8 h-8 flex items-center justify-center">
                        <div class="w-0 h-0 border-l-[14px] border-l-transparent border-r-[14px] border-r-transparent border-t-[24px] border-t-amber-400 drop-shadow-[0_4px_10px_rgba(251,191,36,0.8)]"></div>
                    </div>

                    <!-- Canvas Wheel Container -->
                    <div class="relative p-2.5 rounded-full bg-slate-900 border-4 border-slate-800 shadow-2xl">
                        <canvas id="wheelCanvas" width="440" height="440" class="max-w-full rounded-full cursor-pointer transition-transform"></canvas>
                    </div>
                </div>

                <!-- Target Participant Selector & Spin Button -->
                <div class="w-full max-w-md space-y-4 pt-1">
                    <div class="text-left">
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-amber-400 mb-1">Peserta yang Sedang Diundi:</label>
                        <select x-model="selectedParticipantId" :disabled="isSpinning || undrawnParticipants.length === 0" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-amber-500/30 text-white text-xs font-bold outline-none focus:border-amber-400">
                            <template x-for="p in undrawnParticipants" :key="p.id">
                                <option :value="p.id" x-text="p.name + ' (' + p.institution + ')'"></option>
                            </template>
                        </select>
                    </div>

                    <!-- Spin Trigger Button -->
                    <button type="button" @click="spin()" :disabled="isSpinning || undrawnParticipants.length === 0" class="w-full py-4 px-6 rounded-2xl bg-gradient-to-r from-amber-400 via-amber-500 to-amber-600 hover:from-amber-500 hover:to-amber-700 disabled:opacity-40 disabled:cursor-not-allowed text-slate-950 font-black text-sm sm:text-base tracking-wider uppercase shadow-xl shadow-amber-500/25 hover:scale-[1.01] active:scale-[0.99] transition duration-200 flex items-center justify-center gap-3 cursor-pointer">
                        <i data-lucide="disc" class="w-5 h-5" :class="{ 'animate-spin': isSpinning }"></i>
                        <span x-text="isSpinning ? 'RODA SEDANG BERPUTAR...' : (undrawnParticipants.length === 0 ? 'SEMUA PESERTA TELAH SELESAI DIUNDI' : 'PUTAR RODA UNDIAN SEKARANG')"></span>
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
                </div>
            </div>

        </div>

        <!-- Right 5 Cols: Drawn & Undrawn Lists (Dark Theme) -->
        <div class="lg:col-span-5 space-y-6">
            
            <!-- List Sudah Mendapatkan Nomor Undian -->
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
                                <div class="w-10 h-10 rounded-xl bg-amber-400 text-slate-950 font-mono font-black flex items-center justify-center text-sm shrink-0 shadow-sm shadow-amber-400/20" x-text="'#' + item.draw_number"></div>
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

            <!-- List Belum Diundi -->
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
                             :class="item.id == selectedParticipantId ? 'border-2 border-amber-400 bg-amber-500/15 shadow-[0_0_15px_rgba(251,191,36,0.25)]' : 'border border-slate-800 bg-slate-950/60 hover:border-slate-700'">
                            <div class="flex items-center gap-3 overflow-hidden">
                                <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 transition"
                                     :class="item.id == selectedParticipantId ? 'bg-amber-400 text-slate-950 font-black shadow-sm' : 'bg-slate-900 text-slate-500 border border-slate-800'">
                                    <span class="text-xs font-mono font-black" x-text="item.id == selectedParticipantId ? '✓' : '•'"></span>
                                </div>
                                <div class="overflow-hidden">
                                    <span class="font-bold truncate block" :class="item.id == selectedParticipantId ? 'text-amber-300 font-extrabold' : 'text-slate-200'" x-text="item.name"></span>
                                    <span class="text-[10px] text-slate-400 truncate block mt-0.5" x-text="item.institution"></span>
                                </div>
                            </div>
                            <button type="button" @click="selectedParticipantId = item.id" 
                                    class="text-[10px] font-bold px-3 py-1.5 rounded-lg shrink-0 transition cursor-pointer"
                                    :class="item.id == selectedParticipantId ? 'bg-amber-400 text-slate-950 font-black shadow-sm' : 'bg-slate-800 text-slate-300 hover:bg-amber-400 hover:text-slate-950 border border-slate-700'">
                                <span x-text="item.id == selectedParticipantId ? 'Terpilih' : 'Pilih'"></span>
                            </button>
                        </div>
                    </template>

                    <div x-show="undrawnParticipants.length === 0" class="py-6 text-center text-xs text-emerald-400 font-bold">
                        🎉 Semua peserta telah selesai diundi!
                    </div>
                </div>
            </div>

        </div>

    </div>

</div>

@push('scripts')
<script>
    function spinWheelApp() {
        return {
            competitionId: {{ $competition->id }},
            undrawnParticipants: @json($undrawnList),
            drawnList: @json($drawnList),
            selectedParticipantId: null,
            isSpinning: false,
            wonDrawNumber: null,
            wonParticipantName: '',
            wonParticipantSchool: '',
            audioCtx: null,
            
            // Canvas variables
            canvas: null,
            ctx: null,
            wheelSlots: [],
            startAngle: 0,
            arc: 0,
            spinTime: 0,
            spinTimeTotal: 0,
            spinAngleStart: 0,

            init() {
                if (this.undrawnParticipants.length > 0) {
                    this.selectedParticipantId = this.undrawnParticipants[0].id;
                }

                this.canvas = document.getElementById("wheelCanvas");
                if (this.canvas) {
                    this.ctx = this.canvas.getContext("2d");
                    this.calculateAvailableSlots();
                    this.drawWheel();
                }
            },

            calculateAvailableSlots() {
                const totalParticipants = {{ $competition->verifiedRegistrations()->count() }};
                const assignedNumbers = this.drawnList.map(d => parseInt(d.draw_number));
                
                let slots = [];
                for(let i = 1; i <= Math.max(totalParticipants, 1); i++) {
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

            drawWheel() {
                if (!this.ctx || !this.canvas) return;
                const outsideRadius = 200;
                const textRadius = 140;
                const insideRadius = 50;
                const colors = ["#10b981", "#3b82f6", "#f59e0b", "#ec4899", "#8b5cf6", "#06b6d4", "#14b8a6", "#f97316"];

                this.ctx.clearRect(0, 0, 440, 440);
                this.ctx.strokeStyle = "#0f172a";
                this.ctx.lineWidth = 4;

                if (this.wheelSlots.length === 0) {
                    this.ctx.fillStyle = "#1e293b";
                    this.ctx.beginPath();
                    this.ctx.arc(220, 220, outsideRadius, 0, Math.PI * 2);
                    this.ctx.fill();
                    this.ctx.stroke();

                    this.ctx.fillStyle = "#10b981";
                    this.ctx.font = "bold 16px 'Plus Jakarta Sans', sans-serif";
                    const completeText = "SELESAI DIUNDI";
                    this.ctx.fillText(completeText, 220 - this.ctx.measureText(completeText).width / 2, 225);
                    return;
                }

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
            },

            playClickSound() {
                try {
                    if (!this.audioCtx) this.audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                    const osc = this.audioCtx.createOscillator();
                    const gain = this.audioCtx.createGain();
                    osc.type = "triangle";
                    osc.frequency.setValueAtTime(440, this.audioCtx.currentTime);
                    gain.gain.setValueAtTime(0.08, this.audioCtx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.001, this.audioCtx.currentTime + 0.05);
                    osc.connect(gain);
                    gain.connect(this.audioCtx.destination);
                    osc.start();
                    osc.stop(this.audioCtx.currentTime + 0.05);
                } catch(e) {}
            },

            spin() {
                if (this.isSpinning || this.undrawnParticipants.length === 0 || this.wheelSlots.length === 0) return;
                this.isSpinning = true;
                this.wonDrawNumber = null;

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
                const participant = this.undrawnParticipants.find(p => p.id == this.selectedParticipantId) || this.undrawnParticipants[0];
                if (!participant) return;

                this.wonParticipantName = participant.name;
                this.wonParticipantSchool = participant.institution;

                // Trigger Confetti Celebration
                if (typeof confetti === 'function') {
                    confetti({
                        particleCount: 120,
                        spread: 80,
                        origin: { y: 0.6 }
                    });
                }

                // IMMEDIATELY UPDATE RIGHT SIDE LISTS & WHEEL STATE
                const pIdx = this.undrawnParticipants.findIndex(p => p.id == participant.id);
                if (pIdx > -1) {
                    const p = this.undrawnParticipants.splice(pIdx, 1)[0];
                    p.draw_number = wonNumber;
                    this.drawnList.push(p);
                    // Sort ascending by draw number
                    this.drawnList.sort((a, b) => parseInt(a.draw_number) - parseInt(b.draw_number));
                }

                if (this.undrawnParticipants.length > 0) {
                    this.selectedParticipantId = this.undrawnParticipants[0].id;
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
