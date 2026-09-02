@extends('layouts.admin')

@section('title', 'Pertandingan Bulu Tangkis')

@section('content')
<div class="space-y-6" x-data="{ createModal: false, editModal: false, editData: {}, openEditModal(item) { this.editData = JSON.parse(JSON.stringify(item)); this.editModal = true; } }">
    
    <!-- HEADER BAR -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
        <div>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-600 flex items-center justify-center font-bold">
                    <i data-lucide="activity" class="w-5 h-5"></i>
                </div>
                <div>
                    <h1 class="text-xl font-black text-slate-800 dark:text-white tracking-tight">Pertandingan Bulu Tangkis</h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">Manajemen jadwal, wasit scoring, dan siaran Papan Skor LED</p>
                </div>
            </div>
        </div>
        
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('badminton.scoreboard') }}" target="_blank" class="px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-amber-300 hover:text-amber-200 text-xs font-bold border border-slate-700 flex items-center gap-2 shadow-sm transition">
                <i data-lucide="tv" class="w-4 h-4 text-rose-500"></i>
                <span>Layar 1 Lapangan</span>
            </a>

            <a href="{{ route('badminton.arena') }}" target="_blank" class="px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-emerald-400 hover:text-emerald-300 text-xs font-bold border border-slate-700 flex items-center gap-2 shadow-sm transition">
                <i data-lucide="layout-grid" class="w-4 h-4 text-emerald-400"></i>
                <span>Arena Multi-Lapangan</span>
            </a>

            @if(in_array(auth()->user()->role, ['superadmin', 'pic_lomba']))
            <button @click="createModal = true" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-md shadow-emerald-600/20 flex items-center gap-2 transition">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                <span>Buat Pertandingan Baru</span>
            </button>
            @endif
        </div>
    </div>

    <!-- ALERTS -->
    @if(session('success'))
    <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-xs font-semibold flex items-center gap-3">
        <i data-lucide="check-circle" class="w-4 h-4 shrink-0 text-emerald-500"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    <!-- FILTER BAR -->
    <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm flex flex-wrap items-center justify-between gap-3 text-xs">
        <form action="{{ route('badminton.index') }}" method="GET" class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
            <select name="court" onchange="this.form.submit()" class="bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 rounded-lg px-3 py-1.5 font-medium">
                <option value="">Semua Lapangan</option>
                @foreach($courts as $c)
                    <option value="{{ $c }}" {{ request('court') == $c ? 'selected' : '' }}>{{ $c }}</option>
                @endforeach
            </select>

            <select name="category" onchange="this.form.submit()" class="bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 rounded-lg px-3 py-1.5 font-medium">
                <option value="">Semua Kategori</option>
                <option value="MS" {{ request('category') == 'MS' ? 'selected' : '' }}>MS - Tunggal Putra</option>
                <option value="WS" {{ request('category') == 'WS' ? 'selected' : '' }}>WS - Tunggal Putri</option>
                <option value="MD" {{ request('category') == 'MD' ? 'selected' : '' }}>MD - Ganda Putra</option>
                <option value="WD" {{ request('category') == 'WD' ? 'selected' : '' }}>WD - Ganda Putri</option>
                <option value="XD" {{ request('category') == 'XD' ? 'selected' : '' }}>XD - Ganda Campuran</option>
            </select>

            <select name="status" onchange="this.form.submit()" class="bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 rounded-lg px-3 py-1.5 font-medium">
                <option value="">Semua Status</option>
                <option value="upcoming" {{ request('status') == 'upcoming' ? 'selected' : '' }}>Belum Dimulai</option>
                <option value="ongoing" {{ request('status') == 'ongoing' ? 'selected' : '' }}>Sedang Berlangsung</option>
                <option value="finished" {{ request('status') == 'finished' ? 'selected' : '' }}>Selesai</option>
            </select>
        </form>

        <span class="text-slate-500 font-medium">Total: <strong>{{ $matches->total() }}</strong> Pertandingan</span>
    </div>

    <!-- MATCHES GRID / LIST -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($matches as $match)
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm hover:shadow-md transition flex flex-col justify-between space-y-4">
            
            <!-- Card Header -->
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800/80 pb-2.5 text-xs">
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 rounded-md font-bold bg-slate-900 text-amber-400 font-mono text-[11px]">{{ $match->category }}</span>
                    <span class="font-bold text-slate-700 dark:text-slate-200">{{ $match->court_number }}</span>
                </div>
                <div>
                    @if($match->match_status === 'ongoing')
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-500/10 text-emerald-600 border border-emerald-500/30">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-ping"></span>
                            LIVE (Set {{ $match->current_set }})
                        </span>
                    @elseif($match->match_status === 'finished')
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-500 border border-slate-200 dark:border-slate-700">
                            Selesai
                        </span>
                    @else
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-800">
                            Belum Mulai
                        </span>
                    @endif
                </div>
            </div>

            <!-- Teams & Scores -->
            <div class="space-y-2.5 my-1">
                <!-- Team 1 -->
                <div class="p-2.5 rounded-xl {{ $match->winner_team == 1 ? 'bg-emerald-50/60 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-800' : 'bg-slate-50 dark:bg-slate-800/40' }} flex items-center justify-between gap-3">
                    <div class="overflow-hidden flex-1">
                        <span class="text-[10px] font-bold text-amber-600 dark:text-amber-400 uppercase tracking-wider block truncate">
                            🏫 {{ $match->team1_school }}
                        </span>
                        <p class="text-xs font-bold text-slate-800 dark:text-slate-100 truncate">
                            {{ $match->team1_player1 }} {{ $match->team1_player2 ? '/ ' . $match->team1_player2 : '' }}
                        </p>
                    </div>
                    <div class="flex items-center gap-1 font-mono font-black text-xs shrink-0">
                        <span class="w-6 h-6 rounded bg-slate-900 text-lime-400 flex items-center justify-center">{{ $match->team1_set1 }}</span>
                        <span class="w-6 h-6 rounded bg-slate-900 text-cyan-400 flex items-center justify-center">{{ $match->team1_set2 }}</span>
                        @if($match->current_set == 3 || $match->team1_set3 > 0 || $match->team2_set3 > 0)
                            <span class="w-6 h-6 rounded bg-slate-900 text-cyan-400 flex items-center justify-center">{{ $match->team1_set3 }}</span>
                        @endif
                    </div>
                </div>

                <!-- Team 2 -->
                <div class="p-2.5 rounded-xl {{ $match->winner_team == 2 ? 'bg-emerald-50/60 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-800' : 'bg-slate-50 dark:bg-slate-800/40' }} flex items-center justify-between gap-3">
                    <div class="overflow-hidden flex-1">
                        <span class="text-[10px] font-bold text-cyan-600 dark:text-cyan-400 uppercase tracking-wider block truncate">
                            🏫 {{ $match->team2_school }}
                        </span>
                        <p class="text-xs font-bold text-slate-800 dark:text-slate-100 truncate">
                            {{ $match->team2_player1 }} {{ $match->team2_player2 ? '/ ' . $match->team2_player2 : '' }}
                        </p>
                    </div>
                    <div class="flex items-center gap-1 font-mono font-black text-xs shrink-0">
                        <span class="w-6 h-6 rounded bg-slate-900 text-lime-400 flex items-center justify-center">{{ $match->team2_set1 }}</span>
                        <span class="w-6 h-6 rounded bg-slate-900 text-cyan-400 flex items-center justify-center">{{ $match->team2_set2 }}</span>
                        @if($match->current_set == 3 || $match->team1_set3 > 0 || $match->team2_set3 > 0)
                            <span class="w-6 h-6 rounded bg-slate-900 text-cyan-400 flex items-center justify-center">{{ $match->team2_set3 }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Footer Details & Actions -->
            <div class="pt-2 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between gap-2">
                <span class="text-[11px] font-semibold text-slate-400">{{ $match->round_name }}</span>

                <div class="flex items-center gap-1.5">
                    <a href="{{ route('badminton.umpire', $match->id) }}" class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-sm flex items-center gap-1 transition" title="Buka Panel Wasit">
                        <i data-lucide="play" class="w-3.5 h-3.5"></i>
                        <span>Wasit</span>
                    </a>
                    
                    <a href="{{ route('badminton.scoreboard', $match->id) }}" target="_blank" class="p-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-amber-300 transition" title="Layar LED TV">
                        <i data-lucide="tv" class="w-3.5 h-3.5"></i>
                    </a>

                    @if(in_array(auth()->user()->role, ['superadmin', 'pic_lomba']))
                    <button @click="openEditModal({{ json_encode($match) }})" class="p-1.5 rounded-lg text-slate-400 hover:text-amber-500 hover:bg-amber-500/10 transition" title="Edit Pertandingan">
                        <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                    </button>

                    <form action="{{ route('badminton.destroy', $match->id) }}" method="POST" onsubmit="return confirm('Hapus pertandingan ini?')">
                        @csrf
                        <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-500 hover:bg-rose-500/10 transition" title="Hapus">
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
            <p class="text-xs">Klik tombol "Buat Pertandingan Baru" untuk menambahkan jadwal.</p>
        </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $matches->links() }}
    </div>

    <!-- MODAL CREATE MATCH -->
    <div x-show="createModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.outside="createModal = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 max-w-xl w-full shadow-2xl space-y-4">
            
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

    <!-- MODAL EDIT MATCH -->
    <div x-show="editModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.outside="editModal = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 max-w-xl w-full shadow-2xl space-y-4">
            
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