@extends('layouts.admin')

@section('title', 'Panel Operator Panggung & Stage Timer — ' . $competition->name)

@section('content')
<div class="space-y-6 pb-12" x-data="stageControlApp({{ json_encode($state) }}, {{ json_encode($competition->toArray()) }})" x-init="initApp()" @keydown.window="handleKeyboard($event)">
    
    <!-- TOP HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-white/[0.08]">
        <div class="flex items-center gap-3">
            <a href="{{ auth()->user()->role === 'superadmin' ? route('admin.juri.wasit') : route('pic.dashboard') }}" 
               class="p-2.5 rounded-xl bg-white/[0.06] hover:bg-white/[0.12] text-slate-300 hover:text-white border border-white/[0.08] transition">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-[10px] font-black uppercase tracking-wider text-[#84D0FF] bg-[#4E6EFF]/20 border border-[#4E6EFF]/30 px-2 py-0.5 rounded">
                        KONTROL OPERATOR PANGGUNG
                    </span>
                    <span class="text-xs text-slate-400 font-bold">• {{ $competition->code }}</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-black text-white tracking-tight mt-0.5 font-display">
                    {{ $competition->name }}
                </h1>
            </div>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            <!-- Connection Status Indicator Pill -->
            <div class="inline-flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-bold border transition-all duration-200"
                 :class="isOnline 
                     ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/25' 
                     : 'bg-rose-500/20 text-rose-300 border-rose-500/40 animate-pulse shadow-lg shadow-rose-500/10'">
                <span class="relative flex h-2 w-2">
                    <span x-show="!isOnline" class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2" :class="isOnline ? 'bg-emerald-400' : 'bg-rose-500'"></span>
                </span>
                <span x-text="isOnline ? 'Real-time Online' : 'Koneksi Terputus'"></span>
                <button x-show="!isOnline" 
                        type="button" 
                        @click="fetchState(true)" 
                        :disabled="isReconnecting"
                        class="ml-1 px-2 py-0.5 rounded bg-rose-500/30 hover:bg-rose-500/50 text-[11px] font-black cursor-pointer transition">
                    <span x-text="isReconnecting ? 'Menghubungkan...' : 'Hubungkan Ulang'"></span>
                </button>
            </div>

            <!-- Toggle Mode Waktu ON / OFF (Mode Timer vs Mode Nama Saja) -->
            <button type="button" 
                    @click="toggleTimerMode()"
                    class="px-3.5 py-2.5 rounded-xl border text-xs font-black transition flex items-center gap-2 cursor-pointer shadow-md"
                    :class="(timer.enabled ?? true) 
                        ? 'bg-emerald-500/15 hover:bg-emerald-500/25 text-emerald-300 border-emerald-500/40 shadow-emerald-500/10' 
                        : 'bg-indigo-500/15 hover:bg-indigo-500/25 text-indigo-300 border-indigo-500/40 shadow-indigo-500/10'"
                    :title="(timer.enabled ?? true) ? 'Klik untuk mematikan hitung mundur waktu (Mode Nama Saja)' : 'Klik untuk mengaktifkan kembali hitung mundur waktu'">
                <span class="w-2 h-2 rounded-full" :class="(timer.enabled ?? true) ? 'bg-emerald-400 animate-pulse' : 'bg-indigo-400'"></span>
                <i :data-lucide="(timer.enabled ?? true) ? 'timer' : 'user-check'" class="w-4 h-4"></i>
                <span x-text="(timer.enabled ?? true) ? 'Waktu: ON' : 'Waktu: OFF (Nama Saja)'"></span>
            </button>

            <!-- Layar Sapaan Standby / Jeda Button -->
            <button type="button" 
                    @click="setStandbyMode()"
                    class="px-3.5 py-2.5 rounded-xl border text-xs font-black transition flex items-center gap-2 cursor-pointer shadow-md"
                    :class="!current 
                        ? 'bg-cyan-500/20 text-cyan-300 border-cyan-500/40 shadow-cyan-500/10 ring-1 ring-cyan-400/50' 
                        : 'bg-white/[0.06] hover:bg-white/[0.12] text-slate-300 hover:text-white border-white/[0.1]'"
                    title="Kembalikan TV Panggung ke mode sapaan 'Perlombaan Akan Segera Dimulai'">
                <i data-lucide="sparkles" class="w-4 h-4 text-cyan-400"></i>
                <span x-text="!current ? 'Layar Standby: ON' : 'Layar Standby'"></span>
            </button>

            <!-- Open TV Stage Viewer Button -->
            <a href="{{ route('stage.viewer', $competition->slug ?: $competition->code) }}" 
               target="_blank" 
               class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-[#7A5AF8] to-[#4E6EFF] hover:from-[#6941C6] hover:to-[#3555EC] text-white text-xs font-black shadow-lg shadow-[#7A5AF8]/25 transition flex items-center gap-2">
                <i data-lucide="tv" class="w-4 h-4"></i>
                <span>Buka Layar TV (Stage Display)</span>
                <i data-lucide="external-link" class="w-3.5 h-3.5 opacity-70"></i>
            </a>

            <!-- Reset All Queue Button -->
            <button type="button" 
                    @click="confirmResetAll()"
                    class="px-3 py-2.5 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-300 border border-rose-500/25 text-xs font-bold transition flex items-center gap-1.5">
                <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                <span>Reset Semua</span>
            </button>
        </div>
    </div>

    <!-- OFFLINE WARNING BANNER FOR OPERATOR (Muncul Otomatis Saat Sinyal Putus) -->
    <div x-show="!isOnline" 
         x-cloak 
         class="p-4 rounded-2xl bg-rose-950/80 border border-rose-500/40 text-rose-200 text-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-xl backdrop-blur-md">
        <div class="flex items-center gap-2.5">
            <span class="relative flex h-3 w-3 shrink-0">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-3 w-3 bg-rose-500"></span>
            </span>
            <i data-lucide="wifi-off" class="w-4 h-4 text-rose-400 shrink-0"></i>
            <div>
                <span class="font-black text-rose-100">Koneksi jaringan panggung terputus!</span>
                <span class="text-rose-200/90 ml-1">Sistem otomatis mencoba menghubungkan ulang setiap 2 detik. Data lokal tetap aman.</span>
            </div>
        </div>
        <button type="button" 
                @click="fetchState(true)" 
                :disabled="isReconnecting" 
                class="px-3.5 py-1.5 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-bold shrink-0 transition flex items-center justify-center gap-2 cursor-pointer shadow-md">
            <i data-lucide="refresh-cw" class="w-3.5 h-3.5" :class="isReconnecting ? 'animate-spin' : ''"></i>
            <span x-text="isReconnecting ? 'Menghubungkan...' : 'Hubungkan Sekarang'"></span>
        </button>
    </div>

    <!-- MAIN CONSOLE GRID -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- LEFT: PRIMARY STAGE TIMER & CONTROLS (7 COLS) -->
        <div class="lg:col-span-7 space-y-6">
            
            <!-- CURRENT PERFORMER & TIMER CARD -->
            <div class="p-6 sm:p-8 rounded-3xl border relative overflow-hidden transition-all duration-300"
                 :class="{
                     'bg-slate-900/90 border-emerald-500/40 shadow-2xl shadow-emerald-500/10': (timer.enabled ?? true) && timerZone === 'normal' && timer.status === 'running',
                     'bg-slate-900/90 border-amber-500/40 shadow-2xl shadow-amber-500/10': (timer.enabled ?? true) && timerZone === 'warning',
                     'bg-slate-900/90 border-rose-500/40 shadow-2xl shadow-rose-500/10': (timer.enabled ?? true) && timerZone === 'overtime',
                     'bg-slate-900/90 border-indigo-500/40 shadow-2xl shadow-indigo-500/10': !(timer.enabled ?? true) && timer.status === 'running',
                     'bg-[#101726]/90 border-white/[0.12]': timer.status !== 'running' && timerZone === 'normal'
                 }">

                <!-- Status Header -->
                <div class="flex items-center justify-between gap-3 pb-4 border-b border-white/[0.08]">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full"
                              :class="{
                                  'bg-emerald-400 animate-pulse': timer.status === 'running',
                                  'bg-amber-400': timer.status === 'paused',
                                  'bg-indigo-400': timer.status === 'idle',
                                  'bg-rose-400': timer.status === 'finished'
                              }"></span>
                        <span class="text-xs font-black uppercase tracking-wider text-slate-300" x-text="timerStatusLabel"></span>
                    </div>

                    <template x-if="current && current.draw_number">
                        <span class="px-3 py-1 rounded-xl bg-amber-500/20 border border-amber-500/30 text-amber-300 font-mono font-black text-xs">
                            No. Tampil: #<span x-text="current.draw_number"></span>
                        </span>
                    </template>
                </div>

                <!-- Performer Name & Info -->
                <div class="my-6 space-y-1.5" x-show="current">
                    <p class="text-xs font-bold text-[#84D0FF] uppercase tracking-wider flex items-center gap-1.5">
                        <i data-lucide="school" class="w-4 h-4"></i>
                        <span x-text="current ? current.institution : '-'"></span>
                    </p>
                    <h2 class="text-2xl sm:text-4xl font-black text-white tracking-tight uppercase font-display"
                        x-text="current ? current.name : '-'">
                    </h2>
                </div>

                <div class="my-6 py-6 text-center text-slate-400 space-y-2 rounded-2xl bg-white/[0.02] border border-white/[0.06] p-4" x-show="!current">
                    <div class="w-12 h-12 rounded-2xl bg-cyan-500/15 border border-cyan-500/30 text-cyan-400 flex items-center justify-center mx-auto">
                        <i data-lucide="sparkles" class="w-6 h-6 animate-pulse"></i>
                    </div>
                    <p class="text-sm font-black text-white uppercase tracking-wider">Layar Panggung Mode Standby</p>
                    <p class="text-xs text-slate-400 max-w-md mx-auto">
                        Layar TV saat ini menampilkan sapaan <strong class="text-cyan-300">"PERLOMBAAN AKAN SEGERA DIMULAI"</strong>. 
                        Klik <strong class="text-emerald-400">MULAI (Space)</strong> atau tombol <strong class="text-white">Panggil</strong> pada nomor undian di samping untuk memulai penampilan.
                    </p>
                </div>

                <!-- HUGE DIGITAL TIMER DISPLAY (Ketika Mode Waktu ON) -->
                <div x-show="timer.enabled ?? true" class="py-4 my-2 text-center rounded-2xl bg-black/40 border border-white/[0.08]">
                    <div class="text-6xl sm:text-7xl lg:text-8xl font-black font-mono tracking-tight transition-colors"
                         :class="{
                             'text-emerald-400': timerZone === 'normal',
                             'text-amber-300': timerZone === 'warning',
                             'text-rose-500': timerZone === 'overtime'
                         }"
                         x-text="formattedTimer">
                        00:00
                    </div>
                    <div class="text-[11px] font-black uppercase tracking-widest text-slate-400 mt-1"
                         x-text="timerZoneLabel"></div>
                </div>

                <!-- NO-TIMER / NAME-ONLY MODE BANNER (Ketika Mode Waktu OFF) -->
                <div x-show="!(timer.enabled ?? true)" x-cloak class="py-6 my-2 text-center rounded-2xl bg-indigo-950/40 border border-indigo-500/25 space-y-2">
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-indigo-500/20 text-indigo-300 text-xs font-black uppercase tracking-wider border border-indigo-500/30">
                        <i data-lucide="mic" class="w-4 h-4 text-indigo-400"></i>
                        <span>Mode Nama Saja (Tanpa Batasan Waktu)</span>
                    </div>
                    <p class="text-xs text-slate-300 max-w-sm mx-auto">Layar TV Panggung saat ini menampilkan identitas peserta secara penuh tanpa jam hitung mundur.</p>
                </div>

                <!-- MAIN ACTION BUTTONS (Start, Pause, Finish, Skip) -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-6">
                    <!-- Timer Mode: Start / Resume Button -->
                    <template x-if="timer.enabled ?? true">
                        <button type="button" 
                                @click="triggerAction('start')"
                                x-show="timer.status !== 'running'"
                                class="col-span-2 py-3.5 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-black text-sm shadow-lg shadow-emerald-500/25 transition flex items-center justify-center gap-2 cursor-pointer">
                            <i data-lucide="play" class="w-5 h-5 fill-current"></i>
                            <span>MULAI (Space)</span>
                        </button>
                    </template>

                    <!-- Timer Mode: Pause Button -->
                    <template x-if="timer.enabled ?? true">
                        <button type="button" 
                                @click="triggerAction('pause')"
                                x-show="timer.status === 'running'"
                                class="col-span-2 py-3.5 rounded-2xl bg-gradient-to-r from-amber-600 to-yellow-600 hover:from-amber-500 hover:to-yellow-500 text-white font-black text-sm shadow-lg shadow-amber-500/25 transition flex items-center justify-center gap-2 cursor-pointer">
                            <i data-lucide="pause" class="w-5 h-5 fill-current"></i>
                            <span>JEDA (Space)</span>
                        </button>
                    </template>

                    <!-- No-Timer Mode: Start Button -->
                    <template x-if="!(timer.enabled ?? true)">
                        <button type="button" 
                                @click="triggerAction('start')"
                                class="col-span-2 py-3.5 rounded-2xl font-black text-sm shadow-lg transition flex items-center justify-center gap-2 cursor-pointer"
                                :class="timer.status === 'running' 
                                    ? 'bg-indigo-600/50 text-indigo-200 border border-indigo-500/40 shadow-indigo-600/20' 
                                    : 'bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-500 hover:to-blue-500 text-white shadow-indigo-500/25'">
                            <i data-lucide="mic" class="w-5 h-5"></i>
                            <span x-text="timer.status === 'running' ? 'SEDANG TAMPIL (Panggung)' : 'MULAI TAMPIL (Space)'"></span>
                        </button>
                    </template>

                    <!-- Finish & Next Button -->
                    <button type="button" 
                            @click="triggerAction('finish')"
                            class="col-span-2 sm:col-span-1 py-3.5 rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-black text-xs shadow-lg shadow-blue-500/20 transition flex flex-col items-center justify-center gap-1 cursor-pointer">
                        <i data-lucide="check-circle" class="w-4 h-4"></i>
                        <span>SELESAI (Enter)</span>
                    </button>

                    <!-- Skip Button -->
                    <button type="button" 
                            @click="triggerAction('skip')"
                            class="col-span-2 sm:col-span-1 py-3.5 rounded-2xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white border border-slate-700 font-bold text-xs transition flex flex-col items-center justify-center gap-1 cursor-pointer">
                        <i data-lucide="skip-forward" class="w-4 h-4"></i>
                        <span>LEWATI</span>
                    </button>
                </div>

                <!-- KEYBOARD SHORTCUTS HINT -->
                <div class="mt-4 pt-3 border-t border-white/[0.06] flex items-center justify-between text-[11px] text-slate-500 font-mono">
                    <span>Pintasan: <kbd class="px-1.5 py-0.5 rounded bg-white/[0.08] text-slate-300">Spasi</kbd> = Mulai/Jeda</span>
                    <span><kbd class="px-1.5 py-0.5 rounded bg-white/[0.08] text-slate-300">Enter</kbd> = Selesai</span>
                </div>
            </div>

            <!-- TIME ADJUSTERS & BELL CONTROLS -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                
                <!-- Time Adjusters (Hanya muncul jika Mode Waktu Aktif) -->
                <div x-show="timer.enabled ?? true" class="p-5 rounded-2xl bg-[#121929] border border-white/[0.09] space-y-3">
                    <h4 class="text-xs font-black uppercase tracking-wider text-slate-400 flex items-center gap-2">
                        <i data-lucide="clock" class="w-4 h-4 text-[#84D0FF]"></i>
                        <span>Koreksi Waktu</span>
                    </h4>
                    <div class="grid grid-cols-2 gap-2">
                        <button type="button" @click="adjustTime(60)" class="px-3 py-2 rounded-xl bg-white/[0.05] hover:bg-white/[0.1] text-xs font-bold text-white transition border border-white/[0.08] cursor-pointer">
                            +1 Menit
                        </button>
                        <button type="button" @click="adjustTime(-60)" class="px-3 py-2 rounded-xl bg-white/[0.05] hover:bg-white/[0.1] text-xs font-bold text-white transition border border-white/[0.08] cursor-pointer">
                            -1 Menit
                        </button>
                        <button type="button" @click="adjustTime(30)" class="px-3 py-2 rounded-xl bg-white/[0.05] hover:bg-white/[0.1] text-xs font-bold text-white transition border border-white/[0.08] cursor-pointer">
                            +30 Detik
                        </button>
                        <button type="button" @click="triggerAction('reset_timer')" class="px-3 py-2 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-xs font-bold text-rose-300 transition border border-rose-500/25 cursor-pointer">
                            Reset ke {{ $competition->stage_duration_minutes ?: 7 }}m
                        </button>
                    </div>
                </div>

                <!-- Bell & Sound Controls -->
                <div class="p-5 rounded-2xl bg-[#121929] border border-white/[0.09] space-y-3"
                     :class="(timer.enabled ?? true) ? '' : 'sm:col-span-2'">
                    <h4 class="text-xs font-black uppercase tracking-wider text-slate-400 flex items-center gap-2">
                        <i data-lucide="bell" class="w-4 h-4 text-amber-400"></i>
                        <span>Bunyikan Bel ke TV Panggung</span>
                    </h4>
                    <div class="grid grid-cols-2 gap-2" :class="(timer.enabled ?? true) ? '' : 'sm:grid-cols-4'">
                        <button type="button" @click="triggerBell('bell')" class="px-3 py-2 rounded-xl bg-amber-500/15 hover:bg-amber-500/25 text-amber-300 border border-amber-500/30 text-xs font-bold transition flex items-center justify-center gap-1.5 cursor-pointer">
                            <i data-lucide="bell" class="w-3.5 h-3.5"></i>
                            <span>Bel 1x (Peringatan)</span>
                        </button>
                        <button type="button" @click="triggerBell('double')" class="px-3 py-2 rounded-xl bg-amber-500/15 hover:bg-amber-500/25 text-amber-300 border border-amber-500/30 text-xs font-bold transition flex items-center justify-center gap-1.5 cursor-pointer">
                            <i data-lucide="bell-ring" class="w-3.5 h-3.5"></i>
                            <span>Bel 2x (Habis)</span>
                        </button>
                        <button type="button" @click="triggerBell('gong')" class="px-3 py-2 rounded-xl bg-purple-500/15 hover:bg-purple-500/25 text-purple-300 border border-purple-500/30 text-xs font-bold transition flex items-center justify-center gap-1.5 cursor-pointer">
                            <i data-lucide="disc" class="w-3.5 h-3.5"></i>
                            <span>Gong Panggung</span>
                        </button>
                        <button type="button" @click="triggerBell('buzzer')" class="px-3 py-2 rounded-xl bg-rose-500/15 hover:bg-rose-500/25 text-rose-300 border border-rose-500/30 text-xs font-bold transition flex items-center justify-center gap-1.5 cursor-pointer">
                            <i data-lucide="zap" class="w-3.5 h-3.5"></i>
                            <span>Buzzer Digital</span>
                        </button>
                    </div>
                </div>

            </div>
        </div>

        <!-- RIGHT: COMPLETE QUEUE LIST & STATUS (5 COLS) -->
        <div class="lg:col-span-5 p-6 rounded-3xl bg-[#121929] border border-white/[0.1] space-y-4">
            
            <div class="flex items-center justify-between pb-3 border-b border-white/[0.08]">
                <div>
                    <h3 class="text-sm font-black text-white uppercase tracking-wider">Antrian Urutan Tampil</h3>
                    <p class="text-xs text-slate-400">Total {{ $registrations->count() }} peserta terverifikasi</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-300 font-mono" x-text="completedCount + ' Selesai'"></span>
                </div>
            </div>

            <!-- Filter Sektor (Semua, PA, PI) & Quick Search Bar -->
            <div class="space-y-2.5 pb-2">
                <!-- Sector Filter Buttons -->
                <div class="grid grid-cols-3 gap-1.5 p-1 rounded-2xl bg-black/40 border border-white/[0.08] text-xs font-black">
                    <button type="button" 
                            @click="setSector('all')"
                            class="py-2 rounded-xl transition flex items-center justify-center gap-1.5 cursor-pointer"
                            :class="sectorFilter === 'all' ? 'bg-[#7A5AF8] text-white shadow-md' : 'text-slate-400 hover:text-white'">
                        <span>Semua</span>
                        <span class="text-[10px] px-1.5 py-0.2 rounded-full font-mono" :class="sectorFilter === 'all' ? 'bg-white/20 text-white' : 'bg-white/5 text-slate-400'">{{ $registrations->count() }}</span>
                    </button>
                    <button type="button" 
                            @click="setSector('PA')"
                            class="py-2 rounded-xl transition flex items-center justify-center gap-1.5 cursor-pointer"
                            :class="sectorFilter === 'PA' ? 'bg-blue-600 text-white shadow-md' : 'text-slate-400 hover:text-white'">
                        <span>Putra (PA)</span>
                        <span class="text-[10px] px-1.5 py-0.2 rounded-full font-mono" :class="sectorFilter === 'PA' ? 'bg-white/20 text-white' : 'bg-white/5 text-slate-400'">{{ $registrations->filter(fn($r) => $r->primary_gender !== 'P')->count() }}</span>
                    </button>
                    <button type="button" 
                            @click="setSector('PI')"
                            class="py-2 rounded-xl transition flex items-center justify-center gap-1.5 cursor-pointer"
                            :class="sectorFilter === 'PI' ? 'bg-pink-600 text-white shadow-md' : 'text-slate-400 hover:text-white'">
                        <span>Putri (PI)</span>
                        <span class="text-[10px] px-1.5 py-0.2 rounded-full font-mono" :class="sectorFilter === 'PI' ? 'bg-white/20 text-white' : 'bg-white/5 text-slate-400'">{{ $registrations->filter(fn($r) => $r->primary_gender === 'P')->count() }}</span>
                    </button>
                </div>

                <!-- Search Input -->
                <div class="relative">
                    <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                    <input type="text" 
                           x-model="searchQuery" 
                           placeholder="Cari nama, sekolah, #undian..." 
                           class="w-full pl-8.5 pr-8 py-2 rounded-xl bg-white/[0.04] border border-white/[0.08] text-xs text-white placeholder-slate-500 focus:outline-none focus:border-[#7A5AF8] transition">
                    <button type="button" 
                            x-show="searchQuery" 
                            @click="searchQuery = ''" 
                            class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-white">
                        <i data-lucide="x" class="w-3.5 h-3.5"></i>
                    </button>
                </div>
            </div>

            <!-- Participant List -->
            <div class="space-y-2.5 max-h-[600px] overflow-y-auto pr-1 scrollbar-thin scrollbar-thumb-slate-700">
                @forelse($registrations as $reg)
                    @php
                        $firstMember = $reg->members->first();
                        $displayName = $reg->team_name ?: ($firstMember?->full_name ?: 'Peserta #' . $reg->id);
                        $genderCode = ($reg->primary_gender === 'P') ? 'PI' : 'PA';
                        $searchKey = strtolower($displayName . ' ' . $reg->institution_name . ' ' . ($reg->draw_number ?? '') . ' ' . ($reg->participant_number ?? '') . ' ' . ($reg->sub_category ?? ''));
                    @endphp
                    <div x-show="(sectorFilter === 'all' || sectorFilter === '{{ $genderCode }}') && (!searchQuery.trim() || '{{ addslashes($searchKey) }}'.includes(searchQuery.toLowerCase().trim()))"
                         class="p-3.5 rounded-2xl border transition flex items-center justify-between gap-3"
                         :class="{
                             'bg-emerald-500/15 border-emerald-500/40 ring-1 ring-emerald-400/50': current && current.id === {{ $reg->id }},
                             'bg-amber-500/10 border-amber-500/30': next && next.id === {{ $reg->id }} && (!current || current.id !== {{ $reg->id }}),
                             'bg-white/[0.02] border-white/[0.06] opacity-75': isCompleted({{ $reg->id }}),
                             'bg-white/[0.04] border-white/[0.08]': !isCompleted({{ $reg->id }}) && (!current || current.id !== {{ $reg->id }}) && (!next || next.id !== {{ $reg->id }})
                         }">
                        
                        <div class="flex items-center gap-3 min-w-0">
                            <!-- Draw Number Badge -->
                            <div class="w-9 h-9 rounded-xl font-mono font-black text-xs flex items-center justify-center shrink-0"
                                 :class="{
                                     'bg-emerald-400 text-slate-950 font-bold': current && current.id === {{ $reg->id }},
                                     'bg-amber-400/20 text-amber-300 border border-amber-400/30': next && next.id === {{ $reg->id }} && (!current || current.id !== {{ $reg->id }}),
                                     'bg-slate-800 text-slate-400': isCompleted({{ $reg->id }}),
                                     'bg-white/[0.08] text-slate-200': !isCompleted({{ $reg->id }}) && (!current || current.id !== {{ $reg->id }}) && (!next || next.id !== {{ $reg->id }})
                                 }">
                                {{ $reg->draw_number ?? '-' }}
                            </div>

                            <div class="min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h5 class="text-xs font-bold text-white truncate">{{ $displayName }}</h5>
                                    @if($genderCode === 'PI')
                                        <span class="text-[9px] font-bold px-1.5 py-0.2 rounded bg-pink-500/20 text-pink-300 border border-pink-500/30 shrink-0">Putri (PI)</span>
                                    @else
                                        <span class="text-[9px] font-bold px-1.5 py-0.2 rounded bg-blue-500/20 text-blue-300 border border-blue-500/30 shrink-0">Putra (PA)</span>
                                    @endif
                                    @if($reg->sub_category && !str_contains(strtolower($reg->sub_category), 'putra') && !str_contains(strtolower($reg->sub_category), 'putri'))
                                        <span class="text-[9px] font-bold px-1.5 py-0.2 rounded bg-white/[0.06] text-slate-400 shrink-0">{{ $reg->sub_category }}</span>
                                    @endif
                                    @if($reg->chosen_song)
                                        <span class="text-[9px] font-bold px-1.5 py-0.2 rounded bg-purple-500/20 text-purple-300 border border-purple-500/30 shrink-0">🎵 {{ $reg->chosen_song }}</span>
                                    @endif
                                </div>
                                <p class="text-[11px] text-slate-400 truncate">{{ $reg->institution_name }}</p>
                            </div>
                        </div>

                        <!-- Action / Status on right -->
                        <div class="shrink-0 flex items-center gap-2">
                            <!-- If Currently Active -->
                            <template x-if="current && current.id === {{ $reg->id }}">
                                <span class="px-2.5 py-1 rounded-lg bg-emerald-400/20 text-emerald-300 font-bold text-[10px] uppercase border border-emerald-400/30">
                                    Panggung
                                </span>
                            </template>

                            <!-- If Completed -->
                            <template x-if="isCompleted({{ $reg->id }})">
                                <span class="px-2 py-0.5 rounded-md bg-white/[0.08] text-slate-400 text-[10px] font-mono">
                                    ✓ Selesai
                                </span>
                            </template>

                            <!-- Jump to this performer Button -->
                            <template x-if="!current || current.id !== {{ $reg->id }}">
                                <button type="button" 
                                        @click="selectPerformer({{ $reg->id }})"
                                        class="px-2.5 py-1 rounded-lg bg-white/[0.06] hover:bg-white/[0.15] text-slate-300 hover:text-white text-[10px] font-bold transition border border-white/[0.1]">
                                    Panggil
                                </button>
                            </template>
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center text-slate-500 text-xs">
                        Belum ada peserta terverifikasi di cabang lomba ini.
                    </div>
                @endforelse
            </div>

        </div>

    </div>

    <!-- RESET ALL CONFIRMATION FORM -->
    <form id="resetAllForm" action="{{ route('pic.stage.control.reset_all', $competition->id) }}" method="POST" class="hidden">
        @csrf
    </form>

</div>

<script>
    function stageControlApp(initialState, competitionData) {
        return {
            competition: initialState.competition || competitionData,
            current: initialState.current,
            next: initialState.next,
            completed: initialState.completed || [],
            timer: initialState.timer,

            secondsLeft: initialState.timer.seconds_remaining || ((initialState.competition.duration_minutes || 7) * 60),
            totalSeconds: initialState.timer.total_duration_seconds || ((initialState.competition.duration_minutes || 7) * 60),
            warningThreshold: initialState.timer.warning_threshold_seconds || ((initialState.competition.warning_minutes || 2) * 60),

            sectorFilter: initialState.active_sector || 'all',
            searchQuery: '',

            actionUrl: '{{ route("pic.stage.control.action", $competition->id) }}',
            syncUrl: '{{ route("stage.api.state", $competition->slug ?: $competition->code) }}',
            pollInterval: null,
            timerTickInterval: null,
            isOnline: true,
            consecutiveFailures: 0,
            isReconnecting: false,

            initApp() {
                this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });

                // Start local countdown
                this.timerTickInterval = setInterval(() => {
                    if (this.timer.status === 'running') {
                        this.secondsLeft = Math.max(-3600, this.secondsLeft - 1);
                    }
                }, 1000);

                // Browser Online / Offline listeners for smart auto-reconnect
                window.addEventListener('online', () => {
                    this.isOnline = true;
                    this.fetchState(true);
                });
                window.addEventListener('offline', () => {
                    this.isOnline = false;
                });

                // Start Sync Polling (every 2 seconds)
                this.pollInterval = setInterval(() => this.fetchState(), 2000);
            },

            async fetchState(forced = false) {
                if (forced) this.isReconnecting = true;
                try {
                    const controller = new AbortController();
                    const timeoutId = setTimeout(() => controller.abort(), 4000);
                    const res = await fetch(this.syncUrl, { 
                        headers: { 'Accept': 'application/json' },
                        signal: controller.signal
                    });
                    clearTimeout(timeoutId);

                    if (res.ok) {
                        const data = await res.json();
                        this.current = data.current;
                        this.next = data.next;
                        this.completed = data.completed || [];
                        this.timer = data.timer;
                        this.totalSeconds = data.timer.total_duration_seconds;
                        this.warningThreshold = data.timer.warning_threshold_seconds;
                        if (data.timer.status !== 'running') {
                            this.secondsLeft = data.timer.seconds_remaining;
                        }
                        this.isOnline = true;
                        this.consecutiveFailures = 0;
                        this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
                    } else {
                        this.handleSyncFailure();
                    }
                } catch (err) {
                    this.handleSyncFailure();
                } finally {
                    if (forced) this.isReconnecting = false;
                }
            },

            handleSyncFailure() {
                this.consecutiveFailures++;
                if (this.consecutiveFailures >= 2) {
                    this.isOnline = false;
                }
                this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
            },

            async triggerAction(actionName, payload = {}) {
                try {
                    const bodyData = {
                        action: actionName,
                        seconds_remaining: this.secondsLeft,
                        elapsed_seconds: Math.max(0, this.totalSeconds - this.secondsLeft),
                        ...payload
                    };

                    const res = await fetch(this.actionUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(bodyData)
                    });

                    if (res.ok) {
                        const resData = await res.json();
                        if (resData.state) {
                            this.current = resData.state.current;
                            this.next = resData.state.next;
                            this.completed = resData.state.completed || [];
                            this.timer = resData.state.timer;
                            this.secondsLeft = resData.state.timer.seconds_remaining;
                            this.totalSeconds = resData.state.timer.total_duration_seconds;
                            this.warningThreshold = resData.state.timer.warning_threshold_seconds;
                            this.isOnline = true;
                            this.consecutiveFailures = 0;
                            this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
                        }
                    } else {
                        this.handleSyncFailure();
                    }
                } catch (err) {
                    console.error('Action error:', err);
                    this.handleSyncFailure();
                    alert('Gagal mengirim perintah ke server karena koneksi terputus. Sistem sedang mencoba menyambungkan kembali secara otomatis...');
                }
            },

            setSector(sec) {
                this.sectorFilter = sec;
                this.triggerAction('set_sector', { sector: sec });
            },

            setStandbyMode() {
                if (confirm('Kembalikan TV Panggung ke mode sapaan Standby ("Perlombaan Akan Segera Dimulai")?')) {
                    this.triggerAction('set_standby');
                }
            },

            selectPerformer(regId) {
                if (confirm('Panggil peserta ini ke panggung?')) {
                    this.triggerAction('select_performer', { registration_id: regId });
                }
            },

            adjustTime(delta) {
                this.secondsLeft = Math.max(0, this.secondsLeft + delta);
                this.triggerAction('adjust_time', { delta_seconds: delta });
            },

            triggerBell(bellType) {
                this.triggerAction('trigger_bell', { bell_type: bellType });
            },

            confirmResetAll() {
                if (confirm('Yakin ingin mereset seluruh antrian dan durasi tampil panggung untuk cabang lomba ini?')) {
                    document.getElementById('resetAllForm').submit();
                }
            },

            async toggleTimerMode() {
                const newEnabled = !(this.timer.enabled ?? true);
                this.timer.enabled = newEnabled;
                await this.triggerAction('toggle_timer_mode', { enabled: newEnabled });
            },

            handleKeyboard(e) {
                // Ignore if typing inside input
                if (['INPUT', 'TEXTAREA', 'SELECT'].includes(e.target.tagName)) return;

                if (e.code === 'Space') {
                    e.preventDefault();
                    if (!(this.timer.enabled ?? true)) {
                        if (this.timer.status !== 'running') {
                            this.triggerAction('start');
                        }
                    } else {
                        if (this.timer.status === 'running') {
                            this.triggerAction('pause');
                        } else {
                            this.triggerAction('start');
                        }
                    }
                } else if (e.code === 'Enter') {
                    e.preventDefault();
                    if (confirm('Selesaikan penampilan peserta saat ini dan panggil giliran berikutnya?')) {
                        this.triggerAction('finish');
                    }
                }
            },

            get formattedTimer() {
                const isNegative = this.secondsLeft < 0;
                const absSec = Math.abs(this.secondsLeft);
                const mins = Math.floor(absSec / 60);
                const secs = absSec % 60;
                const sign = isNegative ? '+' : '';
                return sign + String(mins).padStart(2, '0') + ':' + String(secs).padStart(2, '0');
            },

            get timerZone() {
                if (this.secondsLeft <= 0) return 'overtime';
                if (this.secondsLeft <= this.warningThreshold) return 'warning';
                return 'normal';
            },

            get timerZoneLabel() {
                if (this.secondsLeft <= 0) return 'WAKTU HABIS (OVERTIME)';
                if (this.secondsLeft <= this.warningThreshold) return 'PERINGATAN WAKTU';
                return 'WAKTU BERJALAN NORMAL';
            },

            get timerStatusLabel() {
                if (!this.current) {
                    return 'Layar Standby (Sapaan Pembuka)';
                }
                if (!(this.timer.enabled ?? true)) {
                    if (this.timer.status === 'running') return 'Sedang Tampil di Panggung';
                    if (this.timer.status === 'finished') return 'Penampilan Selesai';
                    return 'Siap Dimulai (Mode Nama Saja)';
                }
                if (this.timer.status === 'running') return 'Timer Aktif Berjalan';
                if (this.timer.status === 'paused') return 'Timer Dijeda (Paused)';
                if (this.timer.status === 'finished') return 'Penampilan Selesai';
                return 'Siap Dimulai';
            },

            get completedCount() {
                return this.completed ? this.completed.length : 0;
            },

            isCompleted(regId) {
                return this.completed && this.completed.some(c => c.id === regId);
            }
        };
    }
</script>
@endsection