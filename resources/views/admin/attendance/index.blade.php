@extends('layouts.admin')

@section('title', 'Daftar Hadir & Scan Presensi Peserta')

@section('content')
<div class="space-y-6" x-data="attendanceScanner()">
    <!-- Floating Realtime Scan Toast Notification -->
    <div x-cloak
         x-show="toast.show" 
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="-translate-y-8 opacity-0 scale-95"
         x-transition:enter-end="translate-y-0 opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="translate-y-0 opacity-100 scale-100"
         x-transition:leave-end="-translate-y-8 opacity-0 scale-95"
         class="fixed top-5 left-1/2 -translate-x-1/2 z-[9999] w-[94vw] max-w-xl shadow-2xl rounded-2xl border backdrop-blur-xl p-4 sm:p-5"
         :class="{
            'bg-slate-950/95 border-emerald-500 shadow-emerald-500/20 text-emerald-100': toast.type === 'success',
            'bg-slate-950/95 border-amber-500 shadow-amber-500/20 text-amber-100': toast.type === 'warning',
            'bg-slate-950/95 border-rose-500 shadow-rose-500/20 text-rose-100': toast.type === 'error'
         }">
        
        <div class="flex items-start gap-3.5">
            <!-- Icon Indicator -->
            <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 shadow-lg"
                 :class="{
                    'bg-emerald-500 text-slate-950 shadow-emerald-500/30': toast.type === 'success',
                    'bg-amber-500 text-slate-950 shadow-amber-500/30': toast.type === 'warning',
                    'bg-rose-500 text-white shadow-rose-500/30': toast.type === 'error'
                 }">
                <template x-if="toast.type === 'success'">
                    <i data-lucide="check-circle-2" class="w-6 h-6 stroke-[2.5]"></i>
                </template>
                <template x-if="toast.type === 'warning'">
                    <i data-lucide="alert-triangle" class="w-6 h-6 stroke-[2.5]"></i>
                </template>
                <template x-if="toast.type === 'error'">
                    <i data-lucide="x-circle" class="w-6 h-6 stroke-[2.5]"></i>
                </template>
            </div>

            <!-- Toast Content -->
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-[10px] sm:text-xs font-black uppercase tracking-wider px-2 py-0.5 rounded-md"
                          :class="{
                             'bg-emerald-500/20 text-emerald-300 border border-emerald-500/40': toast.type === 'success',
                             'bg-amber-500/20 text-amber-300 border border-amber-500/40': toast.type === 'warning',
                             'bg-rose-500/20 text-rose-300 border border-rose-500/40': toast.type === 'error'
                          }"
                          x-text="toast.type === 'success' ? 'PRESENSI BERHASIL - HADIR' : (toast.type === 'warning' ? 'SUDAH HADIR SEBELUMNYA' : 'PRESENSI GAGAL')"></span>
                    <button type="button" @click="toast.show = false" class="text-slate-400 hover:text-white transition p-1">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <h3 class="text-sm sm:text-base font-black text-white mt-1" x-text="toast.title"></h3>
                <p class="text-xs text-slate-300 mt-0.5 leading-relaxed" x-text="toast.message"></p>

                <!-- Participant details when available -->
                <template x-if="toast.participant">
                    <div class="mt-3 p-3 rounded-xl bg-white/[0.04] border border-white/[0.08] grid grid-cols-2 gap-2 text-xs">
                        <div>
                            <span class="text-[9px] uppercase font-bold text-slate-400 block">Nama Peserta / Tim</span>
                            <span class="font-bold text-white text-xs truncate block" x-text="toast.participant.name"></span>
                        </div>
                        <div>
                            <span class="text-[9px] uppercase font-bold text-slate-400 block">No. BIB / Peserta</span>
                            <span class="font-mono font-black text-cyan-300 text-xs block" x-text="toast.participant.participant_number || '-'"></span>
                        </div>
                        <div>
                            <span class="text-[9px] uppercase font-bold text-slate-400 block">Asal Lembaga</span>
                            <span class="text-slate-300 text-[11px] truncate block" x-text="toast.participant.institution || '-'"></span>
                        </div>
                        <div>
                            <span class="text-[9px] uppercase font-bold text-slate-400 block">Cabang Lomba</span>
                            <span class="text-slate-300 text-[11px] truncate block" x-text="toast.participant.competition"></span>
                        </div>
                    </div>
                </template>

                <!-- Status Note / Unlock Alert -->
                <template x-if="toast.type === 'success'">
                    <div class="mt-2.5 text-[11px] text-emerald-400 font-semibold flex items-center gap-1.5">
                        <i data-lucide="award" class="w-4 h-4 shrink-0"></i>
                        <span>Sertifikat Digital Otomatis Terbuka & Dapat Diunduh Peserta.</span>
                    </div>
                </template>
                <template x-if="toast.type === 'warning'">
                    <div class="mt-2.5 text-[11px] text-amber-300 font-semibold flex items-center gap-1.5">
                        <i data-lucide="clock" class="w-4 h-4 shrink-0"></i>
                        <span x-text="'Telah tercatat presensi pada ' + (toast.participant?.attended_at || '-') + ' oleh ' + (toast.participant?.attended_by || 'Panitia')"></span>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- Top Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <span>Laporan & Operasional</span>
                <span>/</span>
                <span class="text-cyan-400 font-medium">Daftar Hadir & Presensi</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-white tracking-tight flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-2xl bg-gradient-to-tr from-cyan-500 to-blue-600 flex items-center justify-center text-white shadow-lg shadow-cyan-500/20 shrink-0">
                    <i data-lucide="clipboard-check" class="w-5 h-5"></i>
                </div>
                <span>Daftar Hadir & Scan QR Peserta</span>
            </h1>
            <p class="text-xs text-slate-400 mt-1">
                Pindai QR Code pada lembar bukti pendaftaran peserta untuk absensi kehadiran dan mengaktifkan akses sertifikat digital.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <button @click="toggleScanner()" 
                    type="button"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold text-xs transition shadow-lg cursor-pointer"
                    :class="scannerOpen ? 'bg-rose-500/20 text-rose-300 border border-rose-500/30 hover:bg-rose-500/30' : 'bg-gradient-to-r from-cyan-500 to-blue-600 text-white shadow-cyan-500/25 hover:opacity-95'">
                <i data-lucide="scan" class="w-4 h-4"></i>
                <span x-text="scannerOpen ? 'Tutup Scanner Kamera' : 'Buka Scanner Kamera'"></span>
            </button>

            <a href="{{ route('admin.attendance.print', ['competition_id' => $selectedCompId]) }}" target="_blank"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700/80 font-bold text-xs transition cursor-pointer">
                <i data-lucide="printer" class="w-4 h-4 text-slate-400"></i>
                <span>Cetak Lembar Presensi</span>
            </a>
        </div>
    </div>

    <!-- Flash message from direct scan (?scan=...) -->
    @if(!empty($scanFlash))
        <div class="p-4 rounded-2xl border backdrop-blur-xl flex items-start gap-3.5 {{ $scanFlash['type'] === 'success' ? 'bg-emerald-500/10 border-emerald-500/30 text-emerald-300' : ($scanFlash['type'] === 'info' ? 'bg-cyan-500/10 border-cyan-500/30 text-cyan-300' : 'bg-rose-500/10 border-rose-500/30 text-rose-300') }}">
            <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 {{ $scanFlash['type'] === 'success' ? 'bg-emerald-500/20 text-emerald-400' : ($scanFlash['type'] === 'info' ? 'bg-cyan-500/20 text-cyan-400' : 'bg-rose-500/20 text-rose-400') }}">
                <i data-lucide="{{ $scanFlash['type'] === 'success' ? 'check-circle' : ($scanFlash['type'] === 'info' ? 'info' : 'alert-triangle') }}" class="w-5 h-5"></i>
            </div>
            <div class="flex-1">
                <h4 class="text-xs font-black uppercase tracking-wider">{{ $scanFlash['type'] === 'success' ? 'Presensi Berhasil' : ($scanFlash['type'] === 'info' ? 'Sudah Tercatat' : 'Gagal') }}</h4>
                <p class="text-xs mt-0.5 text-slate-200">{{ $scanFlash['message'] }}</p>
                @if(!empty($scanFlash['participant']))
                    <div class="mt-2.5 pt-2 border-t border-white/[0.1] grid grid-cols-2 sm:grid-cols-4 gap-2 text-[11px]">
                        <div><span class="text-slate-400 block text-[9px]">Nama:</span> <strong class="text-white">{{ $scanFlash['participant']['name'] }}</strong></div>
                        <div><span class="text-slate-400 block text-[9px]">No. Peserta:</span> <strong class="font-mono text-cyan-300">{{ $scanFlash['participant']['participant_number'] }}</strong></div>
                        <div><span class="text-slate-400 block text-[9px]">Lembaga:</span> <span class="text-slate-300">{{ $scanFlash['participant']['institution'] }}</span></div>
                        <div><span class="text-slate-400 block text-[9px]">Cabang:</span> <span class="text-slate-300">{{ $scanFlash['participant']['competition'] }}</span></div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- Statistic Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
        <!-- Total Peserta Terverifikasi -->
        <div class="p-4 sm:p-5 rounded-2xl bg-[#0C111D] border border-white/[0.08] relative overflow-hidden group hover:border-[#4E6EFF]/50 transition">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Peserta Sah</span>
                <div class="w-8 h-8 rounded-xl bg-blue-500/10 text-blue-400 flex items-center justify-center">
                    <i data-lucide="users" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-2xl sm:text-3xl font-black text-white font-mono" x-text="stats.total">{{ number_format($totalRegistered) }}</span>
                <span class="text-[10px] text-slate-400">Pendaftar</span>
            </div>
            <div class="mt-2 text-[10px] text-slate-500">Status verifikasi sah panitia</div>
        </div>

        <!-- Sudah Hadir -->
        <div class="p-4 sm:p-5 rounded-2xl bg-[#0C111D] border border-white/[0.08] relative overflow-hidden group hover:border-emerald-500/50 transition">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-400">Sudah Hadir</span>
                <div class="w-8 h-8 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center">
                    <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-2xl sm:text-3xl font-black text-emerald-400 font-mono" x-text="stats.attended">{{ number_format($totalAttended) }}</span>
                <span class="text-[10px] text-emerald-400/80 font-bold">Peserta</span>
            </div>
            <div class="mt-2 text-[10px] text-slate-400">Telah scan lembar bukti</div>
        </div>

        <!-- Belum Hadir -->
        <div class="p-4 sm:p-5 rounded-2xl bg-[#0C111D] border border-white/[0.08] relative overflow-hidden group hover:border-amber-500/50 transition">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-amber-400">Belum Hadir</span>
                <div class="w-8 h-8 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center">
                    <i data-lucide="clock" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-2xl sm:text-3xl font-black text-amber-300 font-mono" x-text="stats.unattended">{{ number_format($totalUnattended) }}</span>
                <span class="text-[10px] text-amber-400/80 font-bold">Peserta</span>
            </div>
            <div class="mt-2 text-[10px] text-slate-400">Sertifikat terkunci</div>
        </div>

        <!-- Persentase Kehadiran -->
        <div class="p-4 sm:p-5 rounded-2xl bg-[#0C111D] border border-white/[0.08] relative overflow-hidden group hover:border-cyan-500/50 transition">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-cyan-400">Persentase</span>
                <div class="w-8 h-8 rounded-xl bg-cyan-500/10 text-cyan-400 flex items-center justify-center">
                    <i data-lucide="pie-chart" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-2xl sm:text-3xl font-black text-cyan-300 font-mono" x-text="stats.percentage + '%'">{{ $attendancePercentage }}%</span>
            </div>
            <div class="mt-2 w-full bg-slate-800 rounded-full h-1.5 overflow-hidden">
                <div class="bg-gradient-to-r from-cyan-500 to-emerald-400 h-1.5 rounded-full transition-all duration-500" :style="'width: ' + Math.min(100, stats.percentage) + '%'" style="width: {{ min(100, $attendancePercentage) }}%"></div>
            </div>
        </div>
    </div>

    <style>
        #qr-reader {
            width: 100% !important;
            max-width: 100% !important;
            border: none !important;
            background: transparent !important;
        }
        #qr-reader video {
            width: 100% !important;
            height: 100% !important;
            max-height: 380px !important;
            object-fit: cover !important;
            border-radius: 1rem !important;
        }
        #qr-reader__scan_region {
            background: transparent !important;
            border: none !important;
        }
        #qr-reader__scan_region video {
            border-radius: 0.75rem !important;
        }
        #qr-reader__dashboard, #qr-reader__status_span, #qr-reader__header_message {
            display: none !important;
        }
        #qr-reader img {
            display: none !important;
        }
    </style>

    <!-- Live Scanner & Barcode Gun Section -->
    <div x-show="scannerOpen" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 -translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-4"
         class="flex flex-col lg:flex-row items-stretch gap-6 p-5 sm:p-6 rounded-3xl bg-[#0C111D] border border-cyan-500/30 shadow-2xl relative w-full"
         style="width: 100%;">
        
        <!-- Left: Camera Viewport (50% Width) -->
        <div class="w-full lg:w-1/2 flex flex-col items-center justify-between" style="flex: 1 1 350px; min-width: 0;">
            <div class="w-full flex items-center justify-between gap-3 mb-3">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-cyan-400 animate-ping"></span>
                    <h3 class="text-xs font-black uppercase tracking-wider text-white">Scanner Kamera Web/HP</h3>
                </div>
                <div class="flex items-center gap-2 max-w-[200px] sm:max-w-xs">
                    <select x-model="selectedCameraId" @change="switchCamera()" class="w-full px-2.5 py-1.5 rounded-xl bg-slate-900 border border-white/[0.1] text-xs text-slate-300 outline-none focus:border-cyan-400 truncate">
                        <template x-for="cam in cameras" :key="cam.id">
                            <option :value="cam.id" x-text="cam.label || 'Kamera ' + cam.id"></option>
                        </template>
                    </select>
                </div>
            </div>

            <!-- Scanner Box Container -->
            <div class="w-full h-[320px] sm:h-[380px] bg-slate-950 rounded-2xl overflow-hidden border border-white/[0.15] relative flex items-center justify-center shadow-inner">
                <!-- Video stream container used by html5-qrcode -->
                <div id="qr-reader" class="w-full h-full flex items-center justify-center"></div>

                <!-- Laser scanning animation overlay -->
                <div class="pointer-events-none absolute inset-0 flex items-center justify-center">
                    <div class="w-56 sm:w-64 h-56 sm:h-64 border-2 rounded-2xl relative transition-all duration-300"
                         :class="{
                             'border-cyan-400/80 shadow-[0_0_15px_rgba(34,211,238,0.3)]': scanGlow === 'default',
                             'border-emerald-400 shadow-[0_0_35px_#10b981] bg-emerald-500/10': scanGlow === 'success',
                             'border-amber-400 shadow-[0_0_35px_#f59e0b] bg-amber-500/10': scanGlow === 'warning',
                             'border-rose-500 shadow-[0_0_35px_#f43f5e] bg-rose-500/10': scanGlow === 'error'
                         }">
                        <div class="absolute -top-1 -left-1 w-4 h-4 border-t-4 border-l-4 transition-colors duration-300"
                             :class="{
                                 'border-cyan-400': scanGlow === 'default',
                                 'border-emerald-400': scanGlow === 'success',
                                 'border-amber-400': scanGlow === 'warning',
                                 'border-rose-500': scanGlow === 'error'
                             }"></div>
                        <div class="absolute -top-1 -right-1 w-4 h-4 border-t-4 border-r-4 transition-colors duration-300"
                             :class="{
                                 'border-cyan-400': scanGlow === 'default',
                                 'border-emerald-400': scanGlow === 'success',
                                 'border-amber-400': scanGlow === 'warning',
                                 'border-rose-500': scanGlow === 'error'
                             }"></div>
                        <div class="absolute -bottom-1 -left-1 w-4 h-4 border-b-4 border-l-4 transition-colors duration-300"
                             :class="{
                                 'border-cyan-400': scanGlow === 'default',
                                 'border-emerald-400': scanGlow === 'success',
                                 'border-amber-400': scanGlow === 'warning',
                                 'border-rose-500': scanGlow === 'error'
                             }"></div>
                        <div class="absolute -bottom-1 -right-1 w-4 h-4 border-b-4 border-r-4 transition-colors duration-300"
                             :class="{
                                 'border-cyan-400': scanGlow === 'default',
                                 'border-emerald-400': scanGlow === 'success',
                                 'border-amber-400': scanGlow === 'warning',
                                 'border-rose-500': scanGlow === 'error'
                             }"></div>
                        <!-- Animated laser beam -->
                        <div class="w-full h-0.5 shadow-lg animate-pulse absolute top-1/2 -translate-y-1/2 transition-colors duration-300"
                             :class="{
                                 'bg-gradient-to-r from-transparent via-cyan-400 to-transparent shadow-[0_0_12px_#22d3ee]': scanGlow === 'default',
                                 'bg-gradient-to-r from-transparent via-emerald-400 to-transparent shadow-[0_0_15px_#10b981]': scanGlow === 'success',
                                 'bg-gradient-to-r from-transparent via-amber-400 to-transparent shadow-[0_0_15px_#f59e0b]': scanGlow === 'warning',
                                 'bg-gradient-to-r from-transparent via-rose-500 to-transparent shadow-[0_0_15px_#f43f5e]': scanGlow === 'error'
                             }"></div>
                    </div>
                </div>

                <!-- Loading State overlay -->
                <div x-show="cameraLoading" class="absolute inset-0 bg-slate-950/80 backdrop-blur-xs flex flex-col items-center justify-center text-cyan-400 gap-2 z-10">
                    <i data-lucide="loader-2" class="w-7 h-7 animate-spin"></i>
                    <span class="text-xs font-bold">Menyiapkan Kamera...</span>
                </div>
            </div>

            <div class="w-full mt-3 flex items-center justify-between gap-2 text-xs">
                <button type="button" @click="toggleCameraStream()" class="px-3.5 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-bold text-slate-200 border border-white/[0.1] transition cursor-pointer">
                    <span x-text="cameraRunning ? 'Jeda Kamera' : 'Aktifkan Kamera'"></span>
                </button>
                <span class="text-[11px] text-slate-400">Arahkan kamera ke QR Code di Bukti Pendaftaran</span>
            </div>
        </div>

        <!-- Right: Barcode Gun / Manual Input & Recent Scan Feedback (50% Width) -->
        <div class="w-full lg:w-1/2 flex flex-col justify-between space-y-4" style="flex: 1 1 350px; min-width: 0;">
            <!-- Manual / Barcode Gun Form -->
            <div class="p-4 sm:p-5 rounded-2xl bg-slate-900/80 border border-white/[0.08] space-y-3">
                <div class="flex items-center justify-between">
                    <label for="manual-code-input" class="text-xs font-black uppercase tracking-wider text-slate-300 flex items-center gap-2">
                        <i data-lucide="barcode" class="w-4 h-4 text-cyan-400"></i>
                        <span>Input Barcode Gun / Ketik Kode</span>
                    </label>
                    <span class="text-[10px] text-slate-500 font-mono">Tekan Enter untuk scan</span>
                </div>

                <form @submit.prevent="submitManualCode()" class="flex items-center gap-2">
                    <input type="text" 
                           id="manual-code-input"
                           x-ref="manualInput"
                           x-model="manualCode" 
                           placeholder="Contoh: TLT-2026-0001 atau No. Peserta..." 
                           class="flex-1 px-4 py-2.5 rounded-xl bg-slate-950 border border-white/[0.15] text-sm text-white placeholder-slate-500 outline-none focus:border-cyan-400 font-mono transition"
                           autocomplete="off">
                    <button type="submit" 
                            :disabled="processing || !manualCode.trim()"
                            class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 hover:opacity-90 disabled:opacity-50 text-white font-bold text-xs shadow-lg shadow-cyan-500/20 transition cursor-pointer shrink-0 flex items-center gap-1.5">
                        <i data-lucide="arrow-right" class="w-4 h-4" x-show="!processing"></i>
                        <i data-lucide="loader-2" class="w-4 h-4 animate-spin" x-show="processing"></i>
                        <span>Validasi</span>
                    </button>
                </form>
            </div>

            <!-- Recent Scan Participant Modal/Card -->
            <div class="flex-1 p-4 rounded-2xl border transition-all duration-300"
                 :class="{
                     'bg-emerald-500/10 border-emerald-500/40 text-emerald-200': lastScanResult && lastScanResult.type === 'success',
                     'bg-amber-500/10 border-amber-500/40 text-amber-200': lastScanResult && lastScanResult.type === 'warning',
                     'bg-rose-500/10 border-rose-500/40 text-rose-200': lastScanResult && lastScanResult.type === 'error',
                     'bg-slate-900/50 border-white/[0.08] text-slate-400 flex flex-col items-center justify-center text-center': !lastScanResult
                 }">
                
                <template x-if="!lastScanResult">
                    <div class="py-6 flex flex-col items-center justify-center">
                        <div class="w-12 h-12 rounded-2xl bg-white/[0.04] flex items-center justify-center text-slate-500 mb-2">
                            <i data-lucide="qr-code" class="w-6 h-6"></i>
                        </div>
                        <h4 class="text-xs font-bold text-slate-300">Belum Ada Pemindaian</h4>
                        <p class="text-[11px] text-slate-500 max-w-xs mt-1">Hasil pemindaian QR Code peserta akan muncul seketika di kartu ini beserta status pembukaan sertifikat.</p>
                    </div>
                </template>

                <template x-if="lastScanResult && lastScanResult.type === 'error'">
                    <div class="space-y-3">
                        <div class="flex items-center gap-2 pb-2 border-b border-rose-500/20 text-rose-300">
                            <span class="w-6 h-6 rounded-lg bg-rose-500 text-white flex items-center justify-center text-xs font-bold">
                                <i data-lucide="x" class="w-4 h-4"></i>
                            </span>
                            <span class="text-xs font-black uppercase tracking-wider">Presensi Gagal Diproses</span>
                        </div>
                        <div class="p-3 rounded-xl bg-black/40 border border-rose-500/30">
                            <p class="text-xs text-rose-200 font-medium" x-text="lastScanResult.message"></p>
                        </div>
                        <div class="text-[11px] text-slate-400">
                            Pastikan peserta telah terdaftar secara sah dan QR code yang dipindai berasal dari lembar bukti pendaftaran resmi.
                        </div>
                    </div>
                </template>

                <template x-if="lastScanResult && lastScanResult.type !== 'error'">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between pb-2 border-b border-white/[0.1]">
                            <div class="flex items-center gap-2">
                                <span class="w-6 h-6 rounded-lg flex items-center justify-center text-xs font-bold"
                                      :class="lastScanResult.already_attended ? 'bg-amber-500 text-slate-950' : 'bg-emerald-500 text-slate-950'">
                                    <i :data-lucide="lastScanResult.already_attended ? 'clock' : 'check'" class="w-4 h-4"></i>
                                </span>
                                <span class="text-xs font-black uppercase tracking-wider"
                                      x-text="lastScanResult.already_attended ? 'Peserta Sudah Hadir Sebelumnya' : 'Presensi Berhasil Terverifikasi!'"></span>
                            </div>
                            <span class="text-[10px] font-mono opacity-80" x-text="lastScanResult.participant?.attended_at"></span>
                        </div>

                        <div class="space-y-1">
                            <h3 class="text-base sm:text-lg font-black text-white" x-text="lastScanResult.participant?.name"></h3>
                            <div class="text-xs text-slate-300 flex items-center gap-2">
                                <i data-lucide="building-2" class="w-3.5 h-3.5 text-slate-400"></i>
                                <span x-text="lastScanResult.participant?.institution"></span>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2 text-xs pt-1">
                            <div class="p-2 rounded-xl bg-black/30 border border-white/[0.08]">
                                <span class="text-[9px] uppercase font-bold text-slate-400 block">No. BIB / Peserta</span>
                                <span class="font-mono font-black text-cyan-300 text-sm" x-text="lastScanResult.participant?.participant_number || '-'"></span>
                            </div>
                            <div class="p-2 rounded-xl bg-black/30 border border-white/[0.08]">
                                <span class="text-[9px] uppercase font-bold text-slate-400 block">Kode Registrasi</span>
                                <span class="font-mono font-bold text-slate-300 text-xs" x-text="lastScanResult.participant?.registration_code"></span>
                            </div>
                            <div class="p-2 rounded-xl bg-black/30 border border-white/[0.08] col-span-2">
                                <span class="text-[9px] uppercase font-bold text-slate-400 block">Cabang Lomba & Sektor</span>
                                <span class="font-bold text-white text-xs" x-text="lastScanResult.participant?.competition + (lastScanResult.participant?.sub_category ? ' (' + lastScanResult.participant?.sub_category + ')' : '')"></span>
                            </div>
                        </div>

                        <template x-if="!lastScanResult.already_attended">
                            <div class="p-2.5 rounded-xl bg-emerald-500/20 border border-emerald-500/30 text-emerald-300 text-[11px] flex items-center gap-2">
                                <i data-lucide="award" class="w-4 h-4 shrink-0"></i>
                                <span><strong>Sertifikat Aktif:</strong> Akses sertifikat digital resmi telah dibuka untuk akun peserta ini.</span>
                            </div>
                        </template>

                        <template x-if="lastScanResult.already_attended">
                            <div class="p-2.5 rounded-xl bg-amber-500/20 border border-amber-500/30 text-amber-300 text-[11px] flex items-center gap-2">
                                <i data-lucide="info" class="w-4 h-4 shrink-0"></i>
                                <span><strong>Duplikat Presensi:</strong> Peserta ini telah tercatat sebelumnya pada <span x-text="lastScanResult.participant?.attended_at"></span> oleh <span x-text="lastScanResult.participant?.attended_by || 'Panitia'"></span>.</span>
                            </div>
                        </template>
                    </div>
                </template>
            </div>

            <!-- Petunjuk Singkat -->
            <div class="text-[11px] text-slate-400 flex items-center gap-2 px-1">
                <i data-lucide="info" class="w-3.5 h-3.5 text-cyan-400 shrink-0"></i>
                <span>Scanner dilengkapi suara konfirmasi otomatis. Pastikan volume speaker perangkat aktif.</span>
            </div>
        </div>
    </div>

    <!-- Filter & Attendance Table Section -->
    <div class="bg-[#0C111D] border border-white/[0.08] rounded-3xl overflow-hidden shadow-xl">
        <!-- Filter Bar -->
        <div class="p-4 sm:p-5 border-b border-white/[0.08] bg-white/[0.01]">
            <form action="{{ route('admin.attendance.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
                <!-- Cabang Lomba -->
                <div class="sm:col-span-4">
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Filter Cabang Lomba</label>
                    <select name="competition_id" onchange="this.form.submit()" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-white/[0.1] text-xs text-white outline-none focus:border-cyan-400">
                        <option value="">Semua Cabang Lomba</option>
                        @foreach($competitions as $c)
                            <option value="{{ $c->id }}" {{ (string)$selectedCompId === (string)$c->id ? 'selected' : '' }}>
                                {{ $c->name }} ({{ $c->category->name ?? '-' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Kehadiran -->
                <div class="sm:col-span-3">
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Status Kehadiran</label>
                    <select name="status" onchange="this.form.submit()" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-white/[0.1] text-xs text-white outline-none focus:border-cyan-400">
                        <option value="all" {{ $attendanceStatus === 'all' ? 'selected' : '' }}>Semua Status ({{ $totalRegistered }})</option>
                        <option value="hadir" {{ $attendanceStatus === 'hadir' ? 'selected' : '' }}>Sudah Hadir ({{ $totalAttended }})</option>
                        <option value="belum_hadir" {{ $attendanceStatus === 'belum_hadir' ? 'selected' : '' }}>Belum Hadir ({{ $totalUnattended }})</option>
                    </select>
                </div>

                <!-- Search Keyword -->
                <div class="sm:col-span-5">
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Cari Peserta / Sekolah</label>
                    <div class="flex items-center gap-2">
                        <div class="relative flex-1">
                            <i data-lucide="search" class="w-4 h-4 text-slate-500 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                            <input type="text" name="search" value="{{ $search }}" placeholder="Nama, no. peserta, kode, sekolah..." 
                                   class="w-full pl-9 pr-3.5 py-2.5 rounded-xl bg-slate-900 border border-white/[0.1] text-xs text-white placeholder-slate-500 outline-none focus:border-cyan-400">
                        </div>
                        <button type="submit" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs transition cursor-pointer shrink-0">
                            Cari
                        </button>
                        @if($search || $selectedCompId || $attendanceStatus !== 'all')
                            <a href="{{ route('admin.attendance.index') }}" class="p-2.5 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-300 border border-rose-500/30 transition cursor-pointer" title="Reset Filter">
                                <i data-lucide="x" class="w-4 h-4"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-white/[0.03] text-[10px] uppercase font-bold tracking-wider text-slate-400 border-b border-white/[0.08]">
                    <tr>
                        <th class="py-3 px-4 w-12 text-center">No</th>
                        <th class="py-3 px-4">No. Peserta</th>
                        <th class="py-3 px-4">Nama Peserta / Tim</th>
                        <th class="py-3 px-4">Asal Lembaga</th>
                        <th class="py-3 px-4">Cabang Lomba</th>
                        <th class="py-3 px-4 text-center">Status Hadir</th>
                        <th class="py-3 px-4">Waktu Presensi & PIC</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/[0.06]">
                    @forelse($registrations as $idx => $r)
                        <tr id="reg-row-{{ $r->id }}" data-id="{{ $r->id }}" data-code="{{ $r->registration_code }}" data-bib="{{ $r->participant_number }}" class="hover:bg-white/[0.02] transition {{ $r->is_attended ? 'bg-emerald-500/[0.02]' : '' }}">
                            <td class="py-3 px-4 text-center text-slate-500 font-mono">
                                {{ $registrations->firstItem() + $idx }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="font-mono font-bold text-white px-2 py-0.5 rounded-md bg-white/[0.05] border border-white/[0.1]">
                                    {{ $r->participant_number ?: '-' }}
                                </span>
                                <span class="block text-[10px] font-mono text-slate-500 mt-0.5">{{ $r->registration_code }}</span>
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-bold text-white text-sm">{{ $r->display_name }}</div>
                                @if($r->members->count() > 1)
                                    <div class="text-[10px] text-slate-400 mt-0.5">
                                        {{ $r->members->pluck('full_name')->implode(', ') }}
                                    </div>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-slate-300">
                                {{ $r->display_school ?: ($r->institution_name ?: '-') }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="font-medium text-white">{{ $r->competition->name ?? '-' }}</span>
                                @if($r->sub_category)
                                    <span class="block text-[10px] text-slate-400">{{ $r->sub_category }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center" id="reg-status-{{ $r->id }}">
                                @if($r->is_attended)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                        <i data-lucide="check-circle" class="w-3 h-3"></i>
                                        <span>Hadir</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-800 text-slate-400 border border-slate-700">
                                        <i data-lucide="circle-dashed" class="w-3 h-3"></i>
                                        <span>Belum Hadir</span>
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4" id="reg-time-{{ $r->id }}">
                                @if($r->is_attended)
                                    <div class="font-mono text-slate-200 text-xs">
                                        {{ $r->attended_at ? $r->attended_at->format('d/m/Y H:i') : '-' }} WIB
                                    </div>
                                    <div class="text-[10px] text-cyan-400 font-medium">
                                        Oleh: {{ $r->attendedBy?->name ?? 'Panitia' }}
                                    </div>
                                @else
                                    <span class="text-slate-500 text-[11px] italic">-</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center">
                                <form action="{{ route('admin.attendance.toggle', $r->id) }}" method="POST" class="inline-block">
                                    @csrf
                                    <button type="submit" 
                                            onclick="return confirm('Apakah Anda yakin ingin mengubah status kehadiran peserta {{ addslashes($r->display_name) }}?')"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold transition cursor-pointer {{ $r->is_attended ? 'bg-amber-500/20 text-amber-300 hover:bg-amber-500/30 border border-amber-500/30' : 'bg-emerald-500/20 text-emerald-300 hover:bg-emerald-500/30 border border-emerald-500/30' }}"
                                            title="{{ $r->is_attended ? 'Batalkan Status Hadir' : 'Tandai Hadir Manual' }}">
                                        <i data-lucide="{{ $r->is_attended ? 'x' : 'check' }}" class="w-3.5 h-3.5"></i>
                                        <span>{{ $r->is_attended ? 'Batalkan' : 'Tandai Hadir' }}</span>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-500">
                                <div class="flex flex-col items-center justify-center">
                                    <i data-lucide="users" class="w-8 h-8 text-slate-600 mb-2"></i>
                                    <p class="text-sm font-bold text-slate-400">Tidak ada data peserta yang cocok.</p>
                                    <p class="text-xs text-slate-600 mt-1">Coba sesuaikan filter cabang lomba atau kata kunci pencarian.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($registrations->hasPages())
            <div class="p-4 border-t border-white/[0.08] bg-white/[0.01]">
                {{ $registrations->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Local html5-qrcode library -->
<script src="{{ asset('vendor/html5-qrcode/html5-qrcode.min.js') }}"></script>

<script>
function attendanceScanner() {
    return {
        scannerOpen: false,
        cameraRunning: false,
        cameraLoading: false,
        html5QrCode: null,
        cameras: [],
        selectedCameraId: null,
        manualCode: '',
        processing: false,
        lastScanResult: null,
        cooldown: false,
        scanGlow: 'default',
        scanGlowTimer: null,

        stats: {
            total: {{ $totalRegistered }},
            attended: {{ $totalAttended }},
            unattended: {{ $totalUnattended }},
            percentage: {{ $attendancePercentage }}
        },

        toast: {
            show: false,
            type: 'success',
            title: '',
            message: '',
            participant: null,
            timer: null
        },

        init() {
            // Check if there are initial scan results from session flash
            @if(!empty($scanFlash))
                this.showToast(
                    '{{ $scanFlash['type'] === 'success' ? 'success' : ($scanFlash['type'] === 'info' ? 'warning' : 'error') }}',
                    '{{ $scanFlash['type'] === 'success' ? 'PRESENSI BERHASIL' : ($scanFlash['type'] === 'info' ? 'SUDAH HADIR SEBELUMNYA' : 'PRESENSI GAGAL') }}',
                    '{{ addslashes($scanFlash['message']) }}',
                    @json($scanFlash['participant'] ?? null)
                );
                @if(!empty($scanFlash['participant']))
                    this.lastScanResult = {
                        type: '{{ $scanFlash['type'] === 'info' ? 'warning' : 'success' }}',
                        already_attended: {{ $scanFlash['type'] === 'info' ? 'true' : 'false' }},
                        participant: @json($scanFlash['participant'])
                    };
                @endif
            @endif
        },

        showToast(type, title, message, participant = null) {
            if (this.toast.timer) clearTimeout(this.toast.timer);
            this.toast.type = type;
            this.toast.title = title;
            this.toast.message = message;
            this.toast.participant = participant;
            this.toast.show = true;

            this.$nextTick(() => {
                if (window.lucide) lucide.createIcons();
            });

            this.toast.timer = setTimeout(() => {
                this.toast.show = false;
            }, 6000);
        },

        triggerGlow(type) {
            this.scanGlow = type;
            if (this.scanGlowTimer) clearTimeout(this.scanGlowTimer);
            this.scanGlowTimer = setTimeout(() => {
                this.scanGlow = 'default';
            }, 2500);
        },

        toggleScanner() {
            this.scannerOpen = !this.scannerOpen;
            if (this.scannerOpen) {
                this.$nextTick(() => {
                    this.initCamera();
                    if (this.$refs.manualInput) {
                        this.$refs.manualInput.focus();
                    }
                });
            } else {
                this.stopCamera();
            }
        },

        async initCamera() {
            if (typeof Html5Qrcode === 'undefined') {
                console.warn('Html5Qrcode library not loaded.');
                return;
            }

            try {
                this.cameraLoading = true;
                const devices = await Html5Qrcode.getCameras();
                if (devices && devices.length) {
                    this.cameras = devices;
                    // Prefer back camera on mobile phones
                    const backCam = devices.find(c => c.label.toLowerCase().includes('back') || c.label.toLowerCase().includes('belakang') || c.label.toLowerCase().includes('environment'));
                    this.selectedCameraId = backCam ? backCam.id : devices[0].id;
                    await this.startCamera();
                } else {
                    this.showToast('error', 'KAMERA TIDAK TERDETEKSI', 'Tidak ditemukan perangkat kamera di komputer/HP Anda.');
                }
            } catch (err) {
                console.error('Error accessing cameras:', err);
                this.showToast('error', 'IZIN KAMERA DITOLAK', 'Pastikan izin akses kamera telah diizinkan di peramban (browser) Anda.');
            } finally {
                this.cameraLoading = false;
            }
        },

        async startCamera() {
            if (!this.selectedCameraId) return;

            try {
                this.cameraLoading = true;
                if (!this.html5QrCode) {
                    this.html5QrCode = new Html5Qrcode("qr-reader");
                }

                await this.html5QrCode.start(
                    this.selectedCameraId,
                    {
                        fps: 10,
                        qrbox: { width: 250, height: 250 },
                        aspectRatio: 1.0
                    },
                    (decodedText, decodedResult) => {
                        this.onQrCodeScanned(decodedText);
                    },
                    (errorMessage) => {
                        // ignore frame parse errors
                    }
                );
                this.cameraRunning = true;
            } catch (err) {
                console.error("Gagal memulai kamera:", err);
            } finally {
                this.cameraLoading = false;
            }
        },

        async stopCamera() {
            if (this.html5QrCode && this.cameraRunning) {
                try {
                    await this.html5QrCode.stop();
                    this.cameraRunning = false;
                } catch (e) {
                    console.error("Gagal menghentikan kamera:", e);
                }
            }
        },

        async toggleCameraStream() {
            if (this.cameraRunning) {
                await this.stopCamera();
            } else {
                await this.startCamera();
            }
        },

        async switchCamera() {
            await this.stopCamera();
            await this.startCamera();
        },

        onQrCodeScanned(decodedText) {
            if (this.cooldown || this.processing) return;
            this.cooldown = true;

            this.processCode(decodedText);

            // Cooldown 2.5 seconds to prevent spam
            setTimeout(() => {
                this.cooldown = false;
            }, 2500);
        },

        submitManualCode() {
            if (!this.manualCode.trim() || this.processing) return;
            this.processCode(this.manualCode);
            this.manualCode = '';
        },

        async processCode(code) {
            this.processing = true;

            try {
                const response = await fetch("{{ route('admin.attendance.scan') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ code: code })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    if (data.already_attended) {
                        // DUPLICATE / ALREADY ATTENDED
                        this.playBeep('already');
                        this.triggerGlow('warning');
                        this.lastScanResult = {
                            type: 'warning',
                            already_attended: true,
                            participant: data.participant
                        };
                        this.showToast(
                            'warning',
                            'PESERTA SUDAH HADIR SEBELUMNYA',
                            data.message,
                            data.participant
                        );
                    } else {
                        // SUCCESS NEW ATTENDANCE
                        this.playBeep('success');
                        this.triggerGlow('success');
                        this.lastScanResult = {
                            type: 'success',
                            already_attended: false,
                            participant: data.participant
                        };
                        this.showToast(
                            'success',
                            'PRESENSI BERHASIL - HADIR',
                            'Kehadiran resmi dicatat. Sertifikat digital peserta otomatis terbuka!',
                            data.participant
                        );

                        // Realtime stats update
                        this.stats.attended++;
                        this.stats.unattended = Math.max(0, this.stats.unattended - 1);
                        this.stats.percentage = this.stats.total > 0 ? Math.round((this.stats.attended / this.stats.total) * 1000) / 10 : 0;

                        // Update table row if visible
                        this.updateTableRow(data.participant);
                    }
                } else {
                    // SCAN FAILED / REJECTED
                    this.playBeep('error');
                    this.triggerGlow('error');
                    this.lastScanResult = {
                        type: 'error',
                        message: data.message || 'Presensi gagal diproses.'
                    };
                    this.showToast(
                        'error',
                        'PRESENSI GAGAL DIPROSES',
                        data.message || 'Data peserta tidak valid atau tidak memiliki wewenang kehadiran.'
                    );
                }
            } catch (err) {
                console.error("Scan fetch error:", err);
                this.playBeep('error');
                this.triggerGlow('error');
                this.lastScanResult = {
                    type: 'error',
                    message: 'Gagal menghubungi server. Periksa koneksi jaringan internet Anda.'
                };
                this.showToast(
                    'error',
                    'KONEKSI TERPUTUS',
                    'Gagal menghubungi server. Periksa koneksi jaringan Anda.'
                );
            } finally {
                this.processing = false;
                this.$nextTick(() => {
                    if (window.lucide) lucide.createIcons();
                    if (this.$refs.manualInput) {
                        this.$refs.manualInput.focus();
                    }
                });
            }
        },

        updateTableRow(participant) {
            if (!participant || !participant.id) return;
            const row = document.getElementById('reg-row-' + participant.id);
            if (row) {
                row.classList.add('bg-emerald-500/[0.04]');
                const statusCell = document.getElementById('reg-status-' + participant.id);
                if (statusCell) {
                    statusCell.innerHTML = `
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                            <i data-lucide="check-circle" class="w-3 h-3"></i>
                            <span>Hadir</span>
                        </span>
                    `;
                }
                const timeCell = document.getElementById('reg-time-' + participant.id);
                if (timeCell) {
                    timeCell.innerHTML = `
                        <div class="font-mono text-slate-200 text-xs">${participant.attended_at || 'Baru saja'} WIB</div>
                        <div class="text-[10px] text-cyan-400 font-medium">Oleh: ${participant.attended_by || 'Panitia'}</div>
                    `;
                }
            }
        },

        playBeep(type = 'success') {
            try {
                const AudioCtx = window.AudioContext || window.webkitAudioContext;
                if (!AudioCtx) return;
                const ctx = new AudioCtx();

                if (type === 'success') {
                    // Pleasant high 2-tone melodic chime (C6 -> G6)
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    osc.type = 'sine';

                    osc.frequency.setValueAtTime(1046.5, ctx.currentTime);
                    osc.frequency.setValueAtTime(1568, ctx.currentTime + 0.1);

                    gain.gain.setValueAtTime(0.3, ctx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.35);

                    osc.start(ctx.currentTime);
                    osc.stop(ctx.currentTime + 0.35);
                } else if (type === 'already') {
                    // Notice chord double-tone
                    const now = ctx.currentTime;
                    [0, 0.12].forEach(delay => {
                        const osc = ctx.createOscillator();
                        const gain = ctx.createGain();
                        osc.connect(gain);
                        gain.connect(ctx.destination);
                        osc.type = 'triangle';
                        osc.frequency.setValueAtTime(587.33, now + delay);
                        gain.gain.setValueAtTime(0.25, now + delay);
                        gain.gain.exponentialRampToValueAtTime(0.001, now + delay + 0.09);
                        osc.start(now + delay);
                        osc.stop(now + delay + 0.09);
                    });
                } else {
                    // Error buzz (low sawtooth)
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    osc.type = 'sawtooth';
                    osc.frequency.setValueAtTime(160, ctx.currentTime);
                    osc.frequency.linearRampToValueAtTime(110, ctx.currentTime + 0.3);
                    gain.gain.setValueAtTime(0.3, ctx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.3);
                    osc.start(ctx.currentTime);
                    osc.stop(ctx.currentTime + 0.3);
                }
            } catch(e) {
                console.warn('Audio feedback failed:', e);
            }
        }
    };
}
</script>
@endsection
