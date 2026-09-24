@extends('layouts.admin')

@section('title', 'Pertandingan Bulu Tangkis')

@section('content')
<div class="space-y-4 sm:space-y-6" x-data="{ createModal: false, editModal: false, editData: {}, openEditModal(item) { this.editData = JSON.parse(JSON.stringify(item)); this.editModal = true; } }">
    
    @php
        $bltComp = $competitions->first();
    @endphp
    @if($bltComp)
        @include('partials.tournament-stepper', [
            'competition' => $bltComp,
            'activeStep' => 'wasit'
        ])
    @endif

    <!-- HEADER BAR (RESPONSIVE MOBILE 2X2 GRID & DESKTOP ROW) -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3.5 sm:gap-4 bg-white dark:bg-slate-900 p-4 sm:p-5 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
        <div>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-600 flex items-center justify-center font-bold shrink-0">
                    <i data-lucide="activity" class="w-5 h-5"></i>
                </div>
                <div class="min-w-0">
                    <h1 class="text-lg sm:text-xl font-black text-slate-800 dark:text-white tracking-tight truncate">Pertandingan Bulu Tangkis</h1>
                    <p class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 font-medium truncate">Manajemen jadwal, wasit scoring, dan siaran Papan Skor LED</p>
                </div>
            </div>
        </div>
        
        <div class="grid grid-cols-2 sm:flex sm:flex-wrap items-center gap-2">
            <a href="{{ route('badminton.scoreboard') }}" target="_blank" class="px-3 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 active:scale-95 text-amber-300 hover:text-amber-200 text-xs font-bold border border-slate-700 flex items-center justify-center gap-2 shadow-sm transition">
                <i data-lucide="tv" class="w-4 h-4 text-rose-500 shrink-0"></i>
                <span class="truncate">Layar TV</span>
            </a>

            <a href="{{ route('badminton.arena') }}" target="_blank" class="px-3 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 active:scale-95 text-emerald-400 hover:text-emerald-300 text-xs font-bold border border-slate-700 flex items-center justify-center gap-2 shadow-sm transition">
                <i data-lucide="layout-grid" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                <span class="truncate">Arena Multi</span>
            </a>

            <a href="{{ route('badminton.bracket') }}" class="px-3 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white text-xs font-bold shadow-md shadow-indigo-600/20 flex items-center justify-center gap-2 transition" title="Buka Bagan Pertandingan Bulu Tangkis">
                <i data-lucide="git-branch" class="w-4 h-4 shrink-0"></i>
                <span class="truncate">Bagan Lomba</span>
            </a>

            @if(in_array(auth()->user()->role, ['superadmin', 'pic_lomba']))
            <button type="button" @click="createModal = true" class="px-3 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white text-xs font-bold shadow-md shadow-emerald-600/20 flex items-center justify-center gap-2 transition cursor-pointer">
                <i data-lucide="plus-circle" class="w-4 h-4 shrink-0"></i>
                <span class="truncate">Jadwal Baru</span>
            </button>
            @endif
        </div>
    </div>

    <!-- FILTER BAR (CLEAN MOBILE GRID & COUNTER) -->
    <div class="bg-white dark:bg-slate-900 p-3.5 sm:p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-3 text-xs">
        <form action="{{ route('badminton.index') }}" method="GET" class="grid grid-cols-2 sm:grid-cols-3 gap-2 w-full md:w-auto">
            <select name="court" onchange="this.form.submit()" class="bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 rounded-xl px-2.5 sm:px-3 py-2 font-semibold text-xs outline-none focus:ring-2 focus:ring-amber-400 transition cursor-pointer">
                <option value="">Semua Lapangan</option>
                @foreach($courts as $c)
                    <option value="{{ $c }}" {{ request('court') == $c ? 'selected' : '' }}>{{ $c }}</option>
                @endforeach
            </select>

            <select name="category" onchange="this.form.submit()" class="bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 rounded-xl px-2.5 sm:px-3 py-2 font-semibold text-xs outline-none focus:ring-2 focus:ring-amber-400 transition cursor-pointer">
                <option value="">Semua Kategori</option>
                <option value="MS" {{ request('category') == 'MS' ? 'selected' : '' }}>MS - Tunggal Putra</option>
                <option value="WS" {{ request('category') == 'WS' ? 'selected' : '' }}>WS - Tunggal Putri</option>
                <option value="MD" {{ request('category') == 'MD' ? 'selected' : '' }}>MD - Ganda Putra</option>
                <option value="WD" {{ request('category') == 'WD' ? 'selected' : '' }}>WD - Ganda Putri</option>
                <option value="XD" {{ request('category') == 'XD' ? 'selected' : '' }}>XD - Ganda Campuran</option>
            </select>

            <select name="status" onchange="this.form.submit()" class="col-span-2 sm:col-span-1 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 rounded-xl px-2.5 sm:px-3 py-2 font-semibold text-xs outline-none focus:ring-2 focus:ring-amber-400 transition cursor-pointer">
                <option value="">Semua Status</option>
                <option value="upcoming" {{ request('status') == 'upcoming' ? 'selected' : '' }}>Belum Dimulai</option>
                <option value="ongoing" {{ request('status') == 'ongoing' ? 'selected' : '' }}>Sedang Berlangsung</option>
                <option value="finished" {{ request('status') == 'finished' ? 'selected' : '' }}>Selesai</option>
            </select>
        </form>

        <div class="flex items-center justify-between md:justify-end gap-2 text-slate-500 font-medium pt-2 md:pt-0 border-t md:border-t-0 border-slate-100 dark:border-slate-800 shrink-0">
            <span class="text-[11px] sm:text-xs">Total Pertandingan:</span>
            <span class="px-2.5 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 font-bold font-mono text-xs border border-slate-200 dark:border-slate-700">
                {{ $matches->total() }}
            </span>
        </div>
    </div>

    <!-- MATCHES GRID / LIST (RESPONSIVE CARDS) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3.5 sm:gap-4">
        @forelse($matches as $match)
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-3.5 sm:p-4 shadow-sm hover:shadow-md transition flex flex-col justify-between space-y-3">
            
            <!-- Card Header (Clean Non-Wrapping Layout) -->
            <div class="flex items-start justify-between gap-2 border-b border-slate-100 dark:border-slate-800/80 pb-2.5 text-xs">
                <!-- Left: Category, Court, Order, Time -->
                <div class="flex flex-wrap items-center gap-1.5 min-w-0">
                    <span class="px-2 py-0.5 rounded-lg font-black bg-slate-900 text-amber-400 font-mono text-[11px] border border-amber-400/25 shrink-0 shadow-xs">
                        {{ $match->category }}
                    </span>
                    <span class="font-bold text-xs sm:text-sm text-slate-800 dark:text-slate-200 shrink-0">
                        {{ $match->court_number }}
                    </span>
                    @if($match->match_order)
                        <span class="px-1.5 py-0.5 rounded-md bg-amber-500/15 text-amber-600 dark:text-amber-400 font-mono font-bold text-[10px] border border-amber-500/20 shrink-0">
                            Partai #{{ $match->match_order }}
                        </span>
                    @endif
                    @if($match->scheduled_time)
                        <span class="inline-flex items-center gap-1 text-emerald-600 dark:text-emerald-400 font-mono text-[11px] font-bold shrink-0 bg-emerald-500/10 dark:bg-emerald-500/15 px-1.5 py-0.5 rounded-md border border-emerald-500/20">
                            <i data-lucide="clock" class="w-3 h-3"></i>
                            <span>{{ $match->scheduled_time }}</span>
                        </span>
                    @endif
                </div>

                <!-- Right: Status Badge (Always single-line, never wraps) -->
                <div class="shrink-0">
                    @if($match->match_status === 'ongoing')
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] sm:text-[11px] font-black bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30 whitespace-nowrap shadow-xs">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-ping"></span>
                            <span>LIVE (Set {{ $match->current_set }})</span>
                        </span>
                    @elseif($match->match_status === 'finished')
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] sm:text-[11px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700 whitespace-nowrap">
                            <i data-lucide="check-circle-2" class="w-3 h-3 text-emerald-500"></i>
                            <span>Selesai</span>
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] sm:text-[11px] font-bold bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-800/80 whitespace-nowrap">
                            Belum Mulai
                        </span>
                    @endif
                </div>
            </div>

            <!-- Teams & Scores (Athletes First, Clear Hierarchy) -->
            <div class="space-y-2 my-0.5">
                <!-- Team 1 -->
                <div class="p-2.5 rounded-xl {{ $match->winner_team == 1 ? 'bg-amber-500/10 dark:bg-amber-500/15 border-2 border-amber-400/60 shadow-xs' : 'bg-slate-50 dark:bg-slate-800/50 border border-slate-200/60 dark:border-slate-700/40' }} flex items-center justify-between gap-2.5 transition">
                    <div class="overflow-hidden flex-1 min-w-0">
                        <!-- Athlete Name (Utama) -->
                        <div class="flex items-center gap-1.5">
                            @if($match->match_status === 'finished' && $match->winner_team == 1)
                                <span class="text-xs shrink-0">👑</span>
                            @endif
                            <p class="text-xs sm:text-sm font-extrabold text-slate-900 dark:text-white truncate uppercase tracking-tight">
                                {{ $match->team1_player1 }} {{ $match->team1_player2 ? '/ ' . $match->team1_player2 : '' }}
                            </p>
                        </div>
                        <!-- School (Subteks) -->
                        <span class="text-[10px] sm:text-[11px] font-semibold text-amber-600 dark:text-amber-400 truncate flex items-center gap-1 mt-0.5">
                            <span class="opacity-70">🏫</span>
                            <span class="truncate">{{ $match->team1_school }}</span>
                        </span>
                    </div>

                    <!-- Scores -->
                    <div class="flex items-center gap-1 font-mono font-black text-xs shrink-0">
                        <span class="w-7 h-7 rounded-lg bg-slate-900 text-lime-400 flex items-center justify-center border border-white/10 shadow-inner">{{ $match->team1_set1 }}</span>
                        <span class="w-7 h-7 rounded-lg bg-slate-900 text-cyan-400 flex items-center justify-center border border-white/10 shadow-inner">{{ $match->team1_set2 }}</span>
                        @if($match->current_set == 3 || $match->team1_set3 > 0 || $match->team2_set3 > 0)
                            <span class="w-7 h-7 rounded-lg bg-slate-900 text-amber-400 flex items-center justify-center border border-white/10 shadow-inner">{{ $match->team1_set3 }}</span>
                        @endif
                    </div>
                </div>

                <!-- Team 2 -->
                <div class="p-2.5 rounded-xl {{ $match->winner_team == 2 ? 'bg-cyan-500/10 dark:bg-cyan-500/15 border-2 border-cyan-400/60 shadow-xs' : 'bg-slate-50 dark:bg-slate-800/50 border border-slate-200/60 dark:border-slate-700/40' }} flex items-center justify-between gap-2.5 transition">
                    <div class="overflow-hidden flex-1 min-w-0">
                        <!-- Athlete Name (Utama) -->
                        <div class="flex items-center gap-1.5">
                            @if($match->match_status === 'finished' && $match->winner_team == 2)
                                <span class="text-xs shrink-0">👑</span>
                            @endif
                            <p class="text-xs sm:text-sm font-extrabold text-slate-900 dark:text-white truncate uppercase tracking-tight">
                                {{ $match->team2_player1 }} {{ $match->team2_player2 ? '/ ' . $match->team2_player2 : '' }}
                            </p>
                        </div>
                        <!-- School (Subteks) -->
                        <span class="text-[10px] sm:text-[11px] font-semibold text-cyan-600 dark:text-cyan-400 truncate flex items-center gap-1 mt-0.5">
                            <span class="opacity-70">🏫</span>
                            <span class="truncate">{{ $match->team2_school }}</span>
                        </span>
                    </div>

                    <!-- Scores -->
                    <div class="flex items-center gap-1 font-mono font-black text-xs shrink-0">
                        <span class="w-7 h-7 rounded-lg bg-slate-900 text-lime-400 flex items-center justify-center border border-white/10 shadow-inner">{{ $match->team2_set1 }}</span>
                        <span class="w-7 h-7 rounded-lg bg-slate-900 text-cyan-400 flex items-center justify-center border border-white/10 shadow-inner">{{ $match->team2_set2 }}</span>
                        @if($match->current_set == 3 || $match->team1_set3 > 0 || $match->team2_set3 > 0)
                            <span class="w-7 h-7 rounded-lg bg-slate-900 text-amber-400 flex items-center justify-center border border-white/10 shadow-inner">{{ $match->team2_set3 }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Footer Details & Actions (Touch-friendly & Ergonomic on Mobile) -->
            <div class="pt-2 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between gap-2">
                <span class="text-[11px] font-bold text-slate-400 truncate flex-1 min-w-0" title="{{ $match->round_name }}">
                    {{ $match->round_name }}
                </span>

                <div class="flex items-center gap-1.5 shrink-0">
                    <a href="{{ route('badminton.umpire', $match->id) }}" class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white text-xs font-bold shadow-sm shadow-emerald-600/30 flex items-center gap-1.5 transition" title="Buka Panel Wasit">
                        <i data-lucide="play" class="w-3.5 h-3.5 fill-white"></i>
                        <span>Wasit</span>
                    </a>
                    
                    <a href="{{ route('badminton.scoreboard', $match->id) }}" target="_blank" class="p-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 active:scale-95 text-amber-300 border border-slate-700 transition" title="Layar LED TV">
                        <i data-lucide="tv" class="w-4 h-4"></i>
                    </a>

                    @if(in_array(auth()->user()->role, ['superadmin', 'pic_lomba']))
                    <button type="button" @click="openEditModal({{ json_encode($match) }})" class="p-1.5 rounded-xl text-slate-400 hover:text-amber-500 hover:bg-amber-500/10 active:scale-95 transition cursor-pointer" title="Edit Pertandingan">
                        <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                    </button>

                    <form action="{{ route('badminton.destroy', $match->id) }}" method="POST" onsubmit="return confirm('Hapus pertandingan ini?')">
                        @csrf
                        <button type="submit" class="p-1.5 rounded-xl text-slate-400 hover:text-rose-500 hover:bg-rose-500/10 active:scale-95 transition cursor-pointer" title="Hapus">
                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                        </button>
                    </form>
                    @endif
                </div>
            </div>

        </div>
        @empty
        <div class="col-span-full py-12 text-center text-slate-400 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800">
            <i data-lucide="clipboard-x" class="w-12 h-12 mx-auto mb-2 text-slate-300"></i>
            <p class="text-sm font-bold text-slate-600 dark:text-slate-300">Belum ada data pertandingan</p>
            <p class="text-xs">Klik tombol "Jadwal Baru" untuk menambahkan pertandingan.</p>
        </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $matches->links() }}
    </div>

    <!-- MODAL CREATE MATCH (MOBILE RESPONSIVE SCROLL) -->
    <div x-show="createModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4">
        <div @click.outside="createModal = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 sm:p-6 max-w-xl w-full shadow-2xl space-y-4 max-h-[92vh] overflow-y-auto">
            
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <h3 class="text-base font-black text-slate-800 dark:text-white">Tambah Pertandingan Bulu Tangkis</h3>
                <button @click="createModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-white"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>

            <form action="{{ route('badminton.store') }}" method="POST" class="space-y-4 text-xs font-medium">
                @csrf
                
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-600 dark:text-slate-300 mb-1 font-bold">Nomor Lapangan</label>
                        <input type="text" name="court_number" value="Lapangan 1" required class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-slate-600 dark:text-slate-300 mb-1 font-bold">Babak</label>
                        <select name="round_name" required class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-white">
                            <option value="Babak Penyisihan">Babak Penyisihan</option>
                            <option value="Babak 16 Besar">Babak 16 Besar</option>
                            <option value="Perempat Final (QF)">Perempat Final (QF)</option>
                            <option value="Semifinal (SF)">Semifinal (SF)</option>
                            <option value="Final">Final</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-600 dark:text-slate-300 mb-1 font-bold">Kategori Sektor</label>
                        <select name="category" required class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-white">
                            <option value="MS">MS - Tunggal Putra</option>
                            <option value="WS">WS - Tunggal Putri</option>
                            <option value="MD">MD - Ganda Putra</option>
                            <option value="WD">WD - Ganda Putri</option>
                            <option value="XD">XD - Ganda Campuran</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-slate-600 dark:text-slate-300 mb-1 font-bold">Tipe Pertandingan</label>
                        <select name="match_type" required class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-white">
                            <option value="single">Single (Tunggal)</option>
                            <option value="double">Double (Ganda)</option>
                        </select>
                    </div>
                </div>

                <!-- SISI ATAS / TIM 1 -->
                <div class="p-3 bg-amber-50/50 dark:bg-amber-950/20 border border-amber-200/60 dark:border-amber-800/40 rounded-xl space-y-2">
                    <span class="font-bold text-amber-700 dark:text-amber-400 block uppercase tracking-wider text-[11px]">🏸 Tim 1 (Sisi Atas)</span>
                    <input type="text" name="team1_school" placeholder="Asal Sekolah / Kontingen" required class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-white">
                    <input type="text" name="team1_player1" placeholder="Nama Pemain 1" required class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-white">
                    <input type="text" name="team1_player2" placeholder="Nama Pemain 2 (Kosongkan jika Single)" class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-white">
                </div>

                <!-- SISI BAWAH / TIM 2 -->
                <div class="p-3 bg-cyan-50/50 dark:bg-cyan-950/20 border border-cyan-200/60 dark:border-cyan-800/40 rounded-xl space-y-2">
                    <span class="font-bold text-cyan-700 dark:text-cyan-400 block uppercase tracking-wider text-[11px]">🏸 Tim 2 (Sisi Bawah)</span>
                    <input type="text" name="team2_school" placeholder="Asal Sekolah / Kontingen" required class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-white">
                    <input type="text" name="team2_player1" placeholder="Nama Pemain 1" required class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-white">
                    <input type="text" name="team2_player2" placeholder="Nama Pemain 2 (Kosongkan jika Single)" class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-white">
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="createModal = false" class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-bold">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold shadow-md shadow-emerald-600/30">Simpan Pertandingan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL EDIT MATCH (MOBILE RESPONSIVE SCROLL) -->
    <div x-show="editModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4">
        <div @click.outside="editModal = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 sm:p-6 max-w-xl w-full shadow-2xl space-y-4 max-h-[92vh] overflow-y-auto">
            
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <h3 class="text-base font-black text-slate-800 dark:text-white">Edit Pertandingan Bulu Tangkis</h3>
                <button @click="editModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-white"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>

            <form :action="'/badminton/matches/' + editData.id + '/update'" method="POST" class="space-y-4 text-xs font-medium">
                @csrf
                
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-600 dark:text-slate-300 mb-1 font-bold">Nomor Lapangan</label>
                        <input type="text" name="court_number" x-model="editData.court_number" required class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-slate-600 dark:text-slate-300 mb-1 font-bold">Babak</label>
                        <select name="round_name" x-model="editData.round_name" required class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-white">
                            <option value="Babak Penyisihan">Babak Penyisihan</option>
                            <option value="Babak 16 Besar">Babak 16 Besar</option>
                            <option value="Perempat Final (QF)">Perempat Final (QF)</option>
                            <option value="Semifinal (SF)">Semifinal (SF)</option>
                            <option value="Final">Final</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-600 dark:text-slate-300 mb-1 font-bold">Kategori Sektor</label>
                        <select name="category" x-model="editData.category" required class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-white">
                            <option value="MS">MS - Tunggal Putra</option>
                            <option value="WS">WS - Tunggal Putri</option>
                            <option value="MD">MD - Ganda Putra</option>
                            <option value="WD">WD - Ganda Putri</option>
                            <option value="XD">XD - Ganda Campuran</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-slate-600 dark:text-slate-300 mb-1 font-bold">Tipe Pertandingan</label>
                        <select name="match_type" x-model="editData.match_type" required class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-white">
                            <option value="single">Single (Tunggal)</option>
                            <option value="double">Double (Ganda)</option>
                        </select>
                    </div>
                </div>

                <!-- SISI ATAS / TIM 1 -->
                <div class="p-3 bg-amber-50/50 dark:bg-amber-950/20 border border-amber-200/60 dark:border-amber-800/40 rounded-xl space-y-2">
                    <span class="font-bold text-amber-700 dark:text-amber-400 block uppercase tracking-wider text-[11px]">🏸 Tim 1 (Sisi Atas)</span>
                    <input type="text" name="team1_school" x-model="editData.team1_school" placeholder="Asal Sekolah / Kontingen" required class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-white">
                    <input type="text" name="team1_player1" x-model="editData.team1_player1" placeholder="Nama Pemain 1" required class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-white">
                    <input type="text" name="team1_player2" x-model="editData.team1_player2" placeholder="Nama Pemain 2 (Kosongkan jika Single)" class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-white">
                </div>

                <!-- SISI BAWAH / TIM 2 -->
                <div class="p-3 bg-cyan-50/50 dark:bg-cyan-950/20 border border-cyan-200/60 dark:border-cyan-800/40 rounded-xl space-y-2">
                    <span class="font-bold text-cyan-700 dark:text-cyan-400 block uppercase tracking-wider text-[11px]">🏸 Tim 2 (Sisi Bawah)</span>
                    <input type="text" name="team2_school" x-model="editData.team2_school" placeholder="Asal Sekolah / Kontingen" required class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-white">
                    <input type="text" name="team2_player1" x-model="editData.team2_player1" placeholder="Nama Pemain 1" required class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-white">
                    <input type="text" name="team2_player2" x-model="editData.team2_player2" placeholder="Nama Pemain 2 (Kosongkan jika Single)" class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-white">
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="editModal = false" class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-bold">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold shadow-md shadow-amber-500/30">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('badmintonManagerApp', () => ({
            createModal: false,
            editModal: false,
            editData: {},
            openEditModal(item) {
                this.editData = JSON.parse(JSON.stringify(item));
                this.editModal = true;
                this.$nextTick(() => {
                    if (window.lucide) lucide.createIcons();
                });
            }
        }));
    });
</script>
@endsection