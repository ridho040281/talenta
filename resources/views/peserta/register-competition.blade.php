@extends('layouts.admin')

@section('title', 'Formulir Pendaftaran - ' . $competition->name)
@section('page_title', 'Formulir Registrasi Cabang Lomba')

@section('content')
<div class="max-w-4xl mx-auto space-y-8" x-data="registrationForm({{ $competition->min_members }}, {{ $competition->max_members }})">
    
    <!-- Lomba Summary Header -->
    <div class="glass-card rounded-3xl p-6 sm:p-8 border border-slate-800 shadow-2xl flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="space-y-1">
            <span class="px-3 py-1 text-xs font-bold rounded-lg bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                {{ $competition->category->name }}
            </span>
            <h2 class="text-2xl font-black text-white tracking-tight font-display">{{ $competition->name }}</h2>
            <p class="text-xs text-slate-400">
                Tipe: <strong class="text-slate-200 capitalize">{{ $competition->type }}</strong> ({{ $competition->min_members }} - {{ $competition->max_members }} Peserta) • Biaya: <strong class="text-emerald-400 font-bold text-sm font-mono" x-text="formattedFee"></strong>
            </p>
        </div>
        <a href="{{ route('peserta.dashboard') }}" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold transition border border-slate-700">
            Kembali ke Dashboard
        </a>
    </div>

    <!-- Registration Form -->
    <form id="registration-main-form" @submit.prevent="handleSubmit($event)" action="{{ route('peserta.register.competition.store', $competition->slug) }}" method="POST" enctype="multipart/form-data" class="space-y-8">
        @csrf

        @if($competition->code === 'BLT')
            <!-- KHUSUS BULU TANGKIS: Pilihan Kategori Kelas & Nomor Pertandingan -->
            <div class="bg-gradient-to-br from-slate-900 via-slate-800 to-slate-950 rounded-3xl p-6 sm:p-8 text-white border border-slate-700/80 shadow-2xl space-y-6">
                <div class="flex items-center justify-between border-b border-slate-700/80 pb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-amber-500/20 border border-amber-500/30 text-amber-400 flex items-center justify-center font-bold">
                            <i data-lucide="trophy" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-black text-white tracking-wide font-display">Kategori Kelas & Nomor Tanding Bulu Tangkis</h3>
                            <p class="text-xs text-slate-400">Nomor Tunggal dibagi Kelas A/B/C. Nomor Ganda langsung Ganda Putra (PA) & Putri (PI).</p>
                        </div>
                    </div>
                    <div class="text-right hidden sm:block">
                        <span class="text-[10px] text-slate-400 block uppercase font-bold">Biaya Kategori Ini</span>
                        <span class="text-base font-black text-amber-400 font-mono" x-text="formattedFee"></span>
                    </div>
                </div>

                <!-- Pilihan Kategori Kelas & Nomor Tanding Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <!-- 1. Nomor Pertandingan (Dahulukan pemilihan nomor) -->
                    <div class="space-y-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-emerald-400">
                            1. Kategori Nomor Tanding <span class="text-rose-400">*</span>
                        </label>
                        <select name="match_type" x-model="matchType" @change="onMatchTypeChange()" required class="block w-full px-4 py-3 rounded-2xl bg-slate-950 border border-slate-700 text-white text-sm font-bold focus:border-emerald-500 outline-none">
                            <optgroup label="Nomor Tunggal (Perorangan - Ada Kelas A, B, C)">
                                <option value="Tunggal Putra (PA)">👤 Tunggal Putra (PA)</option>
                                <option value="Tunggal Putri (PI)">👤 Tunggal Putri (PI)</option>
                            </optgroup>
                            <optgroup label="Nomor Ganda (Pasangan - 2 Pemain)">
                                <option value="Ganda Putra (PA)">👥 Ganda Putra (PA)</option>
                                <option value="Ganda Putri (PI)">👥 Ganda Putri (PI)</option>
                            </optgroup>
                        </select>
                        <p class="text-[11px] text-slate-400">Pilih sektor Putra (PA) atau Putri (PI).</p>
                    </div>

                    <!-- 2. Kategori Kelas (Hanya untuk Tunggal) -->
                    <div class="space-y-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-emerald-400">
                            2. Tingkatan / Kategori Kelas
                        </label>

                        <div x-show="!matchType.includes('Ganda')">
                            <select name="target_class" x-model="targetClass" :required="!matchType.includes('Ganda')" class="block w-full px-4 py-3 rounded-2xl bg-slate-950 border border-slate-700 text-white text-sm font-bold focus:border-emerald-500 outline-none">
                                <option value="Kategori A (Kelas 1 - 2)">
                                    🏸 Kategori A (Kelas 1 – 2 SD/MI) — <span x-text="matchType.includes('(PI)') ? 'Rp {{ number_format($competition->tier_fees['A_tunggal_pi'] ?? 130000, 0, ',', '.') }}' : 'Rp {{ number_format($competition->tier_fees['A_tunggal_pa'] ?? 130000, 0, ',', '.') }}'"></span>
                                </option>
                                <option value="Kategori B (Kelas 3 - 4)">
                                    🏸 Kategori B (Kelas 3 – 4 SD/MI) — <span x-text="matchType.includes('(PI)') ? 'Rp {{ number_format($competition->tier_fees['B_tunggal_pi'] ?? 150000, 0, ',', '.') }}' : 'Rp {{ number_format($competition->tier_fees['B_tunggal_pa'] ?? 150000, 0, ',', '.') }}'"></span>
                                </option>
                                <option value="Kategori C (Kelas 5 - 6)">
                                    🏸 Kategori C (Kelas 5 – 6 SD/MI) — <span x-text="matchType.includes('(PI)') ? 'Rp {{ number_format($competition->tier_fees['C_tunggal_pi'] ?? 150000, 0, ',', '.') }}' : 'Rp {{ number_format($competition->tier_fees['C_tunggal_pa'] ?? 150000, 0, ',', '.') }}'"></span>
                                </option>
                            </select>
                            <p class="text-[11px] text-slate-400 mt-1.5">Sesuai jenjang kelas siswa saat ini di SD/MI.</p>
                        </div>

                        <div x-show="matchType.includes('Ganda')" class="p-3.5 rounded-2xl bg-blue-950/60 border border-blue-600/60 space-y-1">
                            <input type="hidden" name="target_class" value="Ganda (Semua Kelas)">
                            <div class="flex items-center gap-2 text-xs font-bold text-blue-300">
                                <i data-lucide="info" class="w-4 h-4 text-blue-400"></i>
                                <span>Kategori Ganda (Tanpa Kelas A, B, C)</span>
                            </div>
                            <p class="text-[11px] text-slate-300 leading-relaxed">
                                Nomor Ganda terbuka untuk seluruh siswa SD/MI (2 Pemain sejenis PA/PI) dengan biaya <strong class="text-amber-300 font-mono" x-text="formattedFee"></strong>.
                            </p>
                        </div>
                    </div>

                </div>

                <!-- Tabel Informasi Kuota & Biaya Resmi -->
                <div class="pt-4 border-t border-slate-800 space-y-2">
                    <span class="text-[11px] font-bold text-slate-300 uppercase tracking-wider block">
                        📊 Tabel Batas Kuota & Biaya Resmi Pendaftaran Bulu Tangkis:
                    </span>

                    <div class="overflow-x-auto">
                        <table class="w-full text-xs text-left border border-slate-700 rounded-xl overflow-hidden">
                            <thead class="bg-slate-950 text-slate-300 uppercase text-[10px] font-black tracking-wider border-b border-slate-700">
                                <tr>
                                    <th class="py-2.5 px-4">SEKTOR / NOMOR</th>
                                    <th class="py-2.5 px-4 text-center">KATEGORI</th>
                                    <th class="py-2.5 px-4 text-center">JENJANG KELAS</th>
                                    <th class="py-2.5 px-4 text-center text-cyan-400">KUOTA MAKSIMAL</th>
                                    <th class="py-2.5 px-4 text-center text-amber-400">BIAYA REGISTRASI</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800 text-slate-300 font-medium">
                                <!-- Tunggal PA -->
                                <tr :class="targetClass.includes('Kategori A') && matchType === 'Tunggal Putra (PA)' ? 'bg-emerald-950/80 font-bold text-white ring-1 ring-emerald-500' : ''">
                                    <td class="py-2 px-4 font-bold text-slate-200">👤 Tunggal Putra (PA)</td>
                                    <td class="py-2 px-4 text-center font-black text-emerald-400">Kat A</td>
                                    <td class="py-2 px-4 text-center">Kelas 1 – 2 SD/MI</td>
                                    <td class="py-2 px-4 text-center font-bold text-cyan-300">{{ $competition->tier_quotas['A_tunggal_pa'] ?? 16 }} Peserta</td>
                                    <td class="py-2 px-4 text-center font-mono font-bold text-emerald-400">Rp {{ number_format($competition->tier_fees['A_tunggal_pa'] ?? 130000, 0, ',', '.') }}</td>
                                </tr>
                                <tr :class="targetClass.includes('Kategori B') && matchType === 'Tunggal Putra (PA)' ? 'bg-emerald-950/80 font-bold text-white ring-1 ring-emerald-500' : ''">
                                    <td class="py-2 px-4 font-bold text-slate-200">👤 Tunggal Putra (PA)</td>
                                    <td class="py-2 px-4 text-center font-black text-emerald-400">Kat B</td>
                                    <td class="py-2 px-4 text-center">Kelas 3 – 4 SD/MI</td>
                                    <td class="py-2 px-4 text-center font-bold text-cyan-300">{{ $competition->tier_quotas['B_tunggal_pa'] ?? 16 }} Peserta</td>
                                    <td class="py-2 px-4 text-center font-mono font-bold text-emerald-400">Rp {{ number_format($competition->tier_fees['B_tunggal_pa'] ?? 150000, 0, ',', '.') }}</td>
                                </tr>
                                <tr :class="targetClass.includes('Kategori C') && matchType === 'Tunggal Putra (PA)' ? 'bg-emerald-950/80 font-bold text-white ring-1 ring-emerald-500' : ''">
                                    <td class="py-2 px-4 font-bold text-slate-200">👤 Tunggal Putra (PA)</td>
                                    <td class="py-2 px-4 text-center font-black text-emerald-400">Kat C</td>
                                    <td class="py-2 px-4 text-center">Kelas 5 – 6 SD/MI</td>
                                    <td class="py-2 px-4 text-center font-bold text-cyan-300">{{ $competition->tier_quotas['C_tunggal_pa'] ?? 16 }} Peserta</td>
                                    <td class="py-2 px-4 text-center font-mono font-bold text-emerald-400">Rp {{ number_format($competition->tier_fees['C_tunggal_pa'] ?? 150000, 0, ',', '.') }}</td>
                                </tr>

                                <!-- Tunggal PI -->
                                <tr :class="targetClass.includes('Kategori A') && matchType === 'Tunggal Putri (PI)' ? 'bg-pink-950/80 font-bold text-white ring-1 ring-pink-500' : ''">
                                    <td class="py-2 px-4 font-bold text-slate-200">👤 Tunggal Putri (PI)</td>
                                    <td class="py-2 px-4 text-center font-black text-pink-400">Kat A</td>
                                    <td class="py-2 px-4 text-center">Kelas 1 – 2 SD/MI</td>
                                    <td class="py-2 px-4 text-center font-bold text-cyan-300">{{ $competition->tier_quotas['A_tunggal_pi'] ?? 16 }} Peserta</td>
                                    <td class="py-2 px-4 text-center font-mono font-bold text-pink-400">Rp {{ number_format($competition->tier_fees['A_tunggal_pi'] ?? 130000, 0, ',', '.') }}</td>
                                </tr>
                                <tr :class="targetClass.includes('Kategori B') && matchType === 'Tunggal Putri (PI)' ? 'bg-pink-950/80 font-bold text-white ring-1 ring-pink-500' : ''">
                                    <td class="py-2 px-4 font-bold text-slate-200">👤 Tunggal Putri (PI)</td>
                                    <td class="py-2 px-4 text-center font-black text-pink-400">Kat B</td>
                                    <td class="py-2 px-4 text-center">Kelas 3 – 4 SD/MI</td>
                                    <td class="py-2 px-4 text-center font-bold text-cyan-300">{{ $competition->tier_quotas['B_tunggal_pi'] ?? 16 }} Peserta</td>
                                    <td class="py-2 px-4 text-center font-mono font-bold text-pink-400">Rp {{ number_format($competition->tier_fees['B_tunggal_pi'] ?? 150000, 0, ',', '.') }}</td>
                                </tr>
                                <tr :class="targetClass.includes('Kategori C') && matchType === 'Tunggal Putri (PI)' ? 'bg-pink-950/80 font-bold text-white ring-1 ring-pink-500' : ''">
                                    <td class="py-2 px-4 font-bold text-slate-200">👤 Tunggal Putri (PI)</td>
                                    <td class="py-2 px-4 text-center font-black text-pink-400">Kat C</td>
                                    <td class="py-2 px-4 text-center">Kelas 5 – 6 SD/MI</td>
                                    <td class="py-2 px-4 text-center font-bold text-cyan-300">{{ $competition->tier_quotas['C_tunggal_pi'] ?? 16 }} Peserta</td>
                                    <td class="py-2 px-4 text-center font-mono font-bold text-pink-400">Rp {{ number_format($competition->tier_fees['C_tunggal_pi'] ?? 150000, 0, ',', '.') }}</td>
                                </tr>

                                <!-- Ganda PA & PI -->
                                <tr :class="matchType === 'Ganda Putra (PA)' ? 'bg-blue-950/80 font-bold text-white ring-1 ring-blue-500' : ''">
                                    <td class="py-2 px-4 font-bold text-blue-300">👥 Ganda Putra (PA)</td>
                                    <td class="py-2 px-4 text-center font-black text-blue-400">Ganda</td>
                                    <td class="py-2 px-4 text-center text-slate-300">Semua Kelas SD/MI</td>
                                    <td class="py-2 px-4 text-center font-bold text-blue-300">{{ $competition->tier_quotas['ganda_pa'] ?? 10 }} Pasang</td>
                                    <td class="py-2 px-4 text-center font-mono font-bold text-blue-400">Rp {{ number_format($competition->tier_fees['ganda_pa'] ?? 200000, 0, ',', '.') }}</td>
                                </tr>
                                <tr :class="matchType === 'Ganda Putri (PI)' ? 'bg-amber-950/80 font-bold text-white ring-1 ring-amber-500' : ''">
                                    <td class="py-2 px-4 font-bold text-amber-300">👥 Ganda Putri (PI)</td>
                                    <td class="py-2 px-4 text-center font-black text-amber-400">Ganda</td>
                                    <td class="py-2 px-4 text-center text-slate-300">Semua Kelas SD/MI</td>
                                    <td class="py-2 px-4 text-center font-bold text-amber-300">{{ $competition->tier_quotas['ganda_pi'] ?? 10 }} Pasang</td>
                                    <td class="py-2 px-4 text-center font-mono font-bold text-amber-400">Rp {{ number_format($competition->tier_fees['ganda_pi'] ?? 200000, 0, ',', '.') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        @elseif($competition->code === 'TMJ')
            <!-- KHUSUS TENIS MEJA: Pilihan Kategori Kelas & Nomor Pertandingan -->
            <div class="bg-gradient-to-br from-slate-900 via-slate-800 to-slate-950 rounded-3xl p-6 sm:p-8 text-white border border-slate-700/80 shadow-2xl space-y-6">
                <div class="flex items-center justify-between border-b border-slate-700/80 pb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-amber-500/20 border border-amber-500/30 text-amber-400 flex items-center justify-center font-bold">
                            <i data-lucide="trophy" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-black text-white tracking-wide font-display">Kategori Kelas & Nomor Tanding Tenis Meja</h3>
                            <p class="text-xs text-slate-400">Nomor Tunggal dibagi Kategori A (Kelas 1–3) dan Kategori B (Kelas 4–6).</p>
                        </div>
                    </div>
                    <div class="text-right hidden sm:block">
                        <span class="text-[10px] text-slate-400 block uppercase font-bold">Biaya Kategori Ini</span>
                        <span class="text-base font-black text-amber-400 font-mono" x-text="formattedFee"></span>
                    </div>
                </div>

                <!-- Pilihan Kategori Kelas & Nomor Tanding Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- 1. Nomor Pertandingan -->
                    <div class="space-y-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-emerald-400">
                            1. Kategori Sektor Tanding <span class="text-rose-400">*</span>
                        </label>
                        <select name="match_type" x-model="matchType" @change="onMatchTypeChange()" required class="block w-full px-4 py-3 rounded-2xl bg-slate-950 border border-slate-700 text-white text-sm font-bold focus:border-emerald-500 outline-none">
                            <option value="Tunggal Putra (PA)">👤 Tunggal Putra (PA)</option>
                            <option value="Tunggal Putri (PI)">👤 Tunggal Putri (PI)</option>
                        </select>
                        <p class="text-[11px] text-slate-400">Pilih sektor Putra (PA) atau Putri (PI).</p>
                    </div>

                    <!-- 2. Kategori Kelas -->
                    <div class="space-y-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-emerald-400">
                            2. Tingkatan / Kategori Kelas <span class="text-rose-400">*</span>
                        </label>
                        <select name="target_class" x-model="targetClass" required class="block w-full px-4 py-3 rounded-2xl bg-slate-950 border border-slate-700 text-white text-sm font-bold focus:border-emerald-500 outline-none">
                            <option value="Kategori A (Kelas 1 - 3)">
                                🏓 Kategori A (Kelas 1 – 3 SD/MI) — <span x-text="matchType.includes('(PI)') ? 'Rp {{ number_format($competition->tier_fees['A_tunggal_pi'] ?? 35000, 0, ',', '.') }}' : 'Rp {{ number_format($competition->tier_fees['A_tunggal_pa'] ?? 35000, 0, ',', '.') }}'"></span>
                            </option>
                            <option value="Kategori B (Kelas 4 - 6)">
                                🏓 Kategori B (Kelas 4 – 6 SD/MI) — <span x-text="matchType.includes('(PI)') ? 'Rp {{ number_format($competition->tier_fees['B_tunggal_pi'] ?? 35000, 0, ',', '.') }}' : 'Rp {{ number_format($competition->tier_fees['B_tunggal_pa'] ?? 35000, 0, ',', '.') }}'"></span>
                            </option>
                        </select>
                        <p class="text-[11px] text-slate-400">Sesuai jenjang kelas siswa saat ini di SD/MI.</p>
                    </div>
                </div>

                <!-- Tabel Informasi Kuota & Biaya Resmi -->
                <div class="pt-4 border-t border-slate-800 space-y-2">
                    <span class="text-[11px] font-bold text-slate-300 uppercase tracking-wider block">
                        📊 Tabel Batas Kuota & Biaya Resmi Pendaftaran Tenis Meja:
                    </span>

                    <div class="overflow-x-auto">
                        <table class="w-full text-xs text-left border border-slate-700 rounded-xl overflow-hidden">
                            <thead class="bg-slate-950 text-slate-300 uppercase text-[10px] font-black tracking-wider border-b border-slate-700">
                                <tr>
                                    <th class="py-2.5 px-4">SEKTOR / NOMOR</th>
                                    <th class="py-2.5 px-4 text-center">KATEGORI</th>
                                    <th class="py-2.5 px-4 text-center">JENJANG KELAS</th>
                                    <th class="py-2.5 px-4 text-center text-cyan-400">KUOTA MAKSIMAL</th>
                                    <th class="py-2.5 px-4 text-center text-amber-400">BIAYA REGISTRASI</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800 text-slate-300 font-medium">
                                <!-- Tunggal PA -->
                                <tr :class="targetClass.includes('Kategori A') && matchType === 'Tunggal Putra (PA)' ? 'bg-emerald-950/80 font-bold text-white ring-1 ring-emerald-500' : ''">
                                    <td class="py-2 px-4 font-bold text-slate-200">👤 Tunggal Putra (PA)</td>
                                    <td class="py-2 px-4 text-center font-black text-emerald-400">Kat A</td>
                                    <td class="py-2 px-4 text-center">Kelas 1 – 3 SD/MI</td>
                                    <td class="py-2 px-4 text-center font-bold text-cyan-300">{{ $competition->tier_quotas['A_tunggal_pa'] ?? 10 }} Peserta</td>
                                    <td class="py-2 px-4 text-center font-mono font-bold text-emerald-400">Rp {{ number_format($competition->tier_fees['A_tunggal_pa'] ?? 35000, 0, ',', '.') }}</td>
                                </tr>
                                <tr :class="targetClass.includes('Kategori B') && matchType === 'Tunggal Putra (PA)' ? 'bg-emerald-950/80 font-bold text-white ring-1 ring-emerald-500' : ''">
                                    <td class="py-2 px-4 font-bold text-slate-200">👤 Tunggal Putra (PA)</td>
                                    <td class="py-2 px-4 text-center font-black text-emerald-400">Kat B</td>
                                    <td class="py-2 px-4 text-center">Kelas 4 – 6 SD/MI</td>
                                    <td class="py-2 px-4 text-center font-bold text-cyan-300">{{ $competition->tier_quotas['B_tunggal_pa'] ?? 10 }} Peserta</td>
                                    <td class="py-2 px-4 text-center font-mono font-bold text-emerald-400">Rp {{ number_format($competition->tier_fees['B_tunggal_pa'] ?? 35000, 0, ',', '.') }}</td>
                                </tr>

                                <!-- Tunggal PI -->
                                <tr :class="targetClass.includes('Kategori A') && matchType === 'Tunggal Putri (PI)' ? 'bg-pink-950/80 font-bold text-white ring-1 ring-pink-500' : ''">
                                    <td class="py-2 px-4 font-bold text-slate-200">👤 Tunggal Putri (PI)</td>
                                    <td class="py-2 px-4 text-center font-black text-pink-400">Kat A</td>
                                    <td class="py-2 px-4 text-center">Kelas 1 – 3 SD/MI</td>
                                    <td class="py-2 px-4 text-center font-bold text-cyan-300">{{ $competition->tier_quotas['A_tunggal_pi'] ?? 10 }} Peserta</td>
                                    <td class="py-2 px-4 text-center font-mono font-bold text-pink-400">Rp {{ number_format($competition->tier_fees['A_tunggal_pi'] ?? 35000, 0, ',', '.') }}</td>
                                </tr>
                                <tr :class="targetClass.includes('Kategori B') && matchType === 'Tunggal Putri (PI)' ? 'bg-pink-950/80 font-bold text-white ring-1 ring-pink-500' : ''">
                                    <td class="py-2 px-4 font-bold text-slate-200">👤 Tunggal Putri (PI)</td>
                                    <td class="py-2 px-4 text-center font-black text-pink-400">Kat B</td>
                                    <td class="py-2 px-4 text-center">Kelas 4 – 6 SD/MI</td>
                                    <td class="py-2 px-4 text-center font-bold text-cyan-300">{{ $competition->tier_quotas['B_tunggal_pi'] ?? 10 }} Peserta</td>
                                    <td class="py-2 px-4 text-center font-mono font-bold text-pink-400">Rp {{ number_format($competition->tier_fees['B_tunggal_pi'] ?? 35000, 0, ',', '.') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        <!-- Section 1: Data Kontingen & Asal Sekolah -->
        <div class="glass-card rounded-3xl p-6 sm:p-8 border border-slate-800 shadow-2xl space-y-6">
            <div class="flex items-center gap-3 border-b border-slate-800 pb-4">
                <div class="w-9 h-9 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold border border-emerald-500/30">
                    1
                </div>
                <div>
                    <h3 class="text-base font-bold text-white font-display" x-text="matchType.includes('Ganda') ? 'Data Official Kontingen' : 'Data Sekolah & Official Kontingen'"></h3>
                    <p class="text-xs text-slate-400" x-text="matchType.includes('Ganda') ? 'Informasi guru pembimbing / official penanggung jawab' : 'Informasi identitas sekolah dan kontak penanggung jawab'"></p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                
                <div class="sm:col-span-2" x-show="isCollective && !isBuluTangkis">
                    <label for="team_name" class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">
                        <span>Nama Tim / Regu</span>
                        <span class="text-rose-400">*</span>
                    </label>
                    <input id="team_name" name="team_name" type="text" :required="isCollective && !isBuluTangkis" value="{{ old('team_name') }}" placeholder="Contoh: Regu Pramuka SD Islam Al-Falah A" class="block w-full px-4 py-3 rounded-xl bg-slate-900/90 border border-slate-700 text-white placeholder-slate-500 focus:border-emerald-400 focus:ring-1 focus:ring-emerald-400 text-sm outline-none">
                </div>

                <div x-show="!matchType.includes('Ganda')">
                    <label for="institution_name" class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">Nama Asal Sekolah / Madrasah <span class="text-rose-400">*</span></label>
                    <input id="institution_name" name="institution_name" type="text" :required="!matchType.includes('Ganda')" value="{{ old('institution_name', $user->institution_name) }}" placeholder="Contoh: SD Islam Al-Falah Blitar" class="block w-full px-4 py-3 rounded-xl bg-slate-900/90 border border-slate-700 text-white placeholder-slate-500 focus:border-emerald-400 focus:ring-1 focus:ring-emerald-400 text-sm outline-none">
                </div>

                <div :class="matchType.includes('Ganda') ? 'sm:col-span-1' : ''">
                    <label for="official_name" class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">Nama Guru Pembimbing / Official</label>
                    <input id="official_name" name="official_name" type="text" value="{{ old('official_name', $user->name) }}" placeholder="Contoh: Ust. Salman, S.Pd" class="block w-full px-4 py-3 rounded-xl bg-slate-900/90 border border-slate-700 text-white placeholder-slate-500 focus:border-emerald-400 focus:ring-1 focus:ring-emerald-400 text-sm outline-none">
                </div>

                <div :class="matchType.includes('Ganda') ? 'sm:col-span-1' : 'sm:col-span-2'">
                    <label for="official_phone" class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">Nomor WhatsApp Official / Pendamping</label>
                    <input id="official_phone" name="official_phone" type="text" value="{{ old('official_phone', $user->phone) }}" placeholder="08xxxxxxxxxx" class="block w-full px-4 py-3 rounded-xl bg-slate-900/90 border border-slate-700 text-white placeholder-slate-500 focus:border-emerald-400 focus:ring-1 focus:ring-emerald-400 text-sm outline-none">
                </div>

            </div>
        </div>

        <!-- Section 2: Biodata Anggota Peserta -->
        <div class="glass-card rounded-3xl p-6 sm:p-8 border border-slate-800 shadow-2xl space-y-6">
            <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold border border-emerald-500/30">
                        2
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-white font-display">
                            <span x-text="matchType.includes('Ganda') ? 'Biodata Pasangan Pemain Ganda' : 'Biodata Peserta Lomba'"></span>
                        </h3>
                        <p class="text-xs text-slate-400">Isi data lengkap peserta yang akan bertanding</p>
                    </div>
                </div>

                <div x-show="isCollective && !isBuluTangkis">
                    <button type="button" @click="addMember()" x-show="members.length < maxMembers" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-300 border border-emerald-500/30 text-xs font-bold transition">
                        <i data-lucide="user-plus" class="w-4 h-4"></i>
                        <span>Tambah Anggota (<span x-text="members.length"></span>/<span x-text="maxMembers"></span>)</span>
                    </button>
                </div>
            </div>

            <!-- Dynamic Member Form Cards -->
            <template x-for="(member, index) in members" :key="index">
                <div class="p-6 rounded-2xl bg-slate-900/80 border border-slate-800 space-y-4 relative">
                    
                    <div x-show="members.length > 1 || (isBuluTangkis && matchType.includes('Ganda'))" class="flex items-center justify-between pb-1 border-b border-slate-800/80">
                        <span class="text-xs font-black uppercase tracking-wider text-slate-300 flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-slate-800 text-emerald-400 flex items-center justify-center text-xs font-bold border border-slate-700" x-text="index + 1"></span>
                            <span x-text="matchType.includes('Ganda') ? (index === 0 ? 'Pemain 1 (Ketua Pasangan)' : 'Pemain 2 (Pasangan Ganda)') : 'Anggota Tim #' + (index + 1)"></span>
                        </span>
                        
                        <button type="button" @click="removeMember(index)" x-show="members.length > minMembers && !isBuluTangkis" class="text-rose-400 hover:text-rose-300 text-xs font-bold flex items-center gap-1">
                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                            <span>Hapus</span>
                        </button>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-300 mb-1">
                                <span x-text="matchType.includes('Ganda') ? (index === 0 ? 'Nama Lengkap Pemain 1' : 'Nama Lengkap Pemain 2') : 'Nama Lengkap Siswa / Peserta'"></span>
                                <span class="text-rose-400">*</span>
                            </label>
                            <input :name="'members[' + index + '][full_name]'" type="text" required placeholder="Nama lengkap sesuai akta / kartu pelajar" class="block w-full px-4 py-2.5 rounded-xl border border-slate-700 bg-slate-950 text-white placeholder-slate-500 focus:border-emerald-400 text-sm outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-300 mb-1">
                                <span x-text="matchType.includes('Ganda') ? (index === 0 ? 'Asal Sekolah / Madrasah Pemain 1' : 'Asal Sekolah / Madrasah Pemain 2') : 'Asal Sekolah / Madrasah'"></span>
                                <span class="text-rose-400">*</span>
                            </label>
                            <input :name="'members[' + index + '][school_name]'" type="text" required :value="index === 0 ? '{{ old('institution_name', $user->institution_name) }}' : ''" placeholder="Contoh: SD Islam Al-Falah / MIN 1 Blitar" class="block w-full px-4 py-2.5 rounded-xl border border-slate-700 bg-slate-950 text-white placeholder-slate-500 focus:border-emerald-400 text-sm outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-300 mb-1">NISN (Nomor Induk Siswa Nasional)</label>
                            <input :name="'members[' + index + '][nisn]'" type="text" placeholder="10 digit NISN" class="block w-full px-4 py-2.5 rounded-xl border border-slate-700 bg-slate-950 text-white placeholder-slate-500 focus:border-emerald-400 text-sm outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-300 mb-1">
                                <span x-text="matchType.includes('Ganda') ? (index === 0 ? 'No. WhatsApp Pemain 1' : 'No. WhatsApp Pemain 2') : 'Nomor WhatsApp Siswa'"></span>
                                <span class="text-slate-500 font-normal">(Opsional)</span>
                            </label>
                            <input :name="'members[' + index + '][phone]'" type="text" placeholder="08xxxxxxxxxx" class="block w-full px-4 py-2.5 rounded-xl border border-slate-700 bg-slate-950 text-white placeholder-slate-500 focus:border-emerald-400 text-sm outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-300 mb-1">Jenis Kelamin <span class="text-rose-400">*</span></label>
                            <select :name="'members[' + index + '][gender]'" x-model="member.gender" required class="block w-full px-4 py-2.5 rounded-xl border border-slate-700 bg-slate-950 text-white focus:border-emerald-400 text-sm outline-none">
                                <option value="L">Laki-laki (Putra / PA)</option>
                                <option value="P">Perempuan (Putri / PI)</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-xs font-bold text-slate-300 mb-1">Tempat Lahir</label>
                                <input :name="'members[' + index + '][birth_place]'" type="text" placeholder="Kota lahir" class="block w-full px-3 py-2.5 rounded-xl border border-slate-700 bg-slate-950 text-white placeholder-slate-500 focus:border-emerald-400 text-sm outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-300 mb-1">Tanggal Lahir</label>
                                <input :name="'members[' + index + '][birth_date]'" 
                                       type="date" 
                                       onclick="this.showPicker && this.showPicker()"
                                       class="block w-full px-3 py-2.5 rounded-xl border border-slate-700 bg-slate-950 text-white focus:border-emerald-400 text-sm outline-none cursor-pointer [color-scheme:dark]">
                            </div>
                        </div>
                    </div>

                </div>
            </template>
        </div>

        <!-- Section 3: Upload Berkas Syarat & Bukti Pembayaran/Transfer -->
        <div class="glass-card rounded-3xl p-6 sm:p-8 border border-slate-800 shadow-2xl space-y-6">
            <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-amber-500/20 text-amber-400 flex items-center justify-center font-bold border border-amber-500/30">
                        3
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-white font-display">Upload Dokumen Pendukung & Bukti Pendaftaran</h3>
                        <p class="text-xs text-slate-400">Unggah berkas identitas dan bukti transfer/pendaftaran untuk diverifikasi oleh panitia</p>
                    </div>
                </div>
                <div class="text-right">
                    <span class="text-[10px] text-slate-400 font-bold block uppercase">Nominal Tagihan</span>
                    <span class="text-base font-black text-emerald-400 font-mono" x-text="formattedFee"></span>
                </div>
            </div>

            <!-- Dynamic Payment Details Box -->
            <div class="p-5 rounded-2xl bg-slate-950 border border-slate-800 text-white space-y-3">
                <div class="flex items-center justify-between text-xs">
                    <span class="text-emerald-400 font-bold uppercase tracking-wider flex items-center gap-1.5">
                        <i data-lucide="credit-card" class="w-4 h-4"></i>
                        <span>Informasi Rekening Pembayaran</span>
                    </span>
                    <span class="px-2.5 py-0.5 rounded-md bg-emerald-500/20 text-emerald-300 text-[10px] font-bold border border-emerald-500/30">
                        Transfer Sesuai Nominal
                    </span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs pt-1">
                    <div>
                        <span class="text-[11px] text-slate-400 block">Bank Tujuan:</span>
                        <strong class="text-white text-sm">Bank Syariah Indonesia (BSI)</strong>
                    </div>
                    <div>
                        <span class="text-[11px] text-slate-400 block">Nomor Rekening:</span>
                        <strong class="text-amber-400 font-mono text-base tracking-wider">7123456789</strong>
                    </div>
                    <div>
                        <span class="text-[11px] text-slate-400 block">Atas Nama:</span>
                        <strong class="text-white">Panitia TALENTA 2026</strong>
                    </div>
                </div>
                <div class="pt-2 border-t border-slate-800 flex items-center justify-between text-xs">
                    <span class="text-slate-300">Biaya yang harus ditransfer:</span>
                    <span class="font-black text-base text-amber-400 font-mono" x-text="formattedFee"></span>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                
                <!-- 1. Upload Surat Rekomendasi / Kartu Pelajar -->
                <!-- 1. Upload Surat Rekomendasi / Kartu Pelajar -->
                <div class="p-6 rounded-2xl border-2 border-dashed border-slate-700 hover:border-emerald-500 bg-slate-900/60 transition text-center space-y-3">
                    <div class="w-12 h-12 mx-auto rounded-2xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center shadow-sm border border-emerald-500/30">
                        <i data-lucide="file-text" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-white">Surat Rekomendasi / Kartu Pelajar</label>
                        <p class="text-[11px] text-slate-400 mt-0.5">Surat tugas kepala sekolah / kartu pelajar (PDF, JPG, PNG, maks 5 MB)</p>
                    </div>
                    <input type="file" name="document_file" class="block w-full text-xs text-slate-400 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-800 file:text-slate-200 hover:file:bg-slate-700 cursor-pointer">
                </div>

                <!-- 2. Upload Bukti Transfer / Bukti Pendaftaran -->
                <div class="p-6 rounded-2xl border-2 border-dashed border-emerald-500/50 hover:border-emerald-400 bg-emerald-950/20 transition text-center space-y-3 relative">
                    <div class="w-12 h-12 mx-auto rounded-2xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center shadow-sm border border-emerald-500/30">
                        <i data-lucide="receipt" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-white">
                            Bukti Transfer / Slip Pendaftaran
                        </label>
                        <p class="text-[11px] text-slate-400 mt-0.5">
                            Struk transfer bank / screenshot bukti pembayaran (JPG, PNG, PDF maks 5 MB)
                        </p>
                    </div>
                    <input type="file" name="payment_proof" accept="image/*,application/pdf" class="block w-full text-xs text-slate-400 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-emerald-600 file:text-slate-950 hover:file:bg-emerald-500 cursor-pointer">
                    @error('payment_proof')
                        <p class="text-xs text-rose-400 font-bold mt-1">{{ $message }}</p>
                    @enderror
                </div>

            </div>

            <div class="p-4 rounded-xl bg-amber-500/10 border border-amber-500/30 text-xs text-amber-300 flex items-start gap-2.5">
                <i data-lucide="alert-circle" class="w-4 h-4 text-amber-400 shrink-0 mt-0.5"></i>
                <span><strong>Satu Kali Kirim:</strong> Formulir pendaftaran dan slip bukti transfer dikirim bersamaan dalam satu kali langkah. Pastikan file bukti pembayaran telah dipilih sebelum menekan tombol kirim di bawah.</span>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex items-center justify-end gap-3 pt-4">
            <a href="{{ route('peserta.dashboard') }}" class="px-6 py-3.5 rounded-2xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold text-sm transition border border-slate-700">
                Batal
            </a>
            <button type="submit" class="inline-flex items-center gap-2 px-8 py-3.5 rounded-2xl bg-gradient-to-r from-emerald-400 via-teal-300 to-cyan-400 hover:from-emerald-300 hover:to-cyan-300 text-slate-950 font-black text-sm shadow-xl shadow-emerald-500/30 hover:scale-[1.02] transition duration-200 cursor-pointer">
                <i data-lucide="send" class="w-4 h-4 text-slate-950"></i>
                <span>Kirim Formulir Pendaftaran</span>
            </button>
        </div>

    </form>

    <!-- Modal Peringatan Berkas Belum Diunggah (Dark Glass) -->
    <div x-show="showWarningModal" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/80 backdrop-blur-md flex items-center justify-center p-4"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="relative w-full max-w-lg rounded-3xl bg-slate-900 border border-amber-500/40 shadow-2xl p-6 sm:p-8 space-y-6 text-white"
             @click.away="showWarningModal = false">
            
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-2xl bg-amber-500/20 text-amber-400 flex items-center justify-center shrink-0 border border-amber-500/30">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div class="space-y-1">
                    <h3 class="text-lg font-black text-white font-display">Peringatan: Dokumen Belum Diunggah</h3>
                    <p class="text-xs text-slate-300">Mohon periksa kembali berkas berikut sebelum mengirimkan pendaftaran:</p>
                </div>
            </div>

            <!-- List Berkas yang Belum Diunggah -->
            <div class="space-y-3">
                <!-- 1. Bukti Transfer -->
                <div x-show="isPaymentProofMissing" class="p-3.5 rounded-2xl bg-rose-500/10 border border-rose-500/30 flex items-start gap-3">
                    <div class="w-8 h-8 rounded-xl bg-rose-500/20 text-rose-400 flex items-center justify-center shrink-0 mt-0.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                    </div>
                    <div class="flex-1 text-xs">
                        <span class="font-bold text-rose-300 block">Bukti Transfer / Slip Pembayaran</span>
                        <p class="text-slate-300 text-[11px] mt-0.5">File bukti transfer belum dipilih. Bukti pembayaran diperlukan agar panitia dapat segera memverifikasi dan menerbitkan nomor peserta Anda.</p>
                    </div>
                </div>

                <!-- 2. Surat Rekomendasi -->
                <div x-show="isDocumentFileMissing" class="p-3.5 rounded-2xl bg-amber-500/10 border border-amber-500/30 flex items-start gap-3">
                    <div class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-400 flex items-center justify-center shrink-0 mt-0.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div class="flex-1 text-xs">
                        <span class="font-bold text-amber-300 block">Surat Rekomendasi / Kartu Pelajar</span>
                        <p class="text-slate-300 text-[11px] mt-0.5">File surat tugas kepala sekolah atau kartu pelajar peserta belum dipilih.</p>
                    </div>
                </div>
            </div>

            <!-- Tombol Aksi Modal -->
            <div class="pt-2 flex flex-col sm:flex-row items-center justify-end gap-3 border-t border-slate-800">
                <button type="button" 
                        @click="showWarningModal = false" 
                        class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-xs transition border border-slate-700">
                    Batal
                </button>
                
                <button type="button" 
                        @click="focusMissingUpload()" 
                        class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-gradient-to-r from-amber-400 via-amber-500 to-amber-600 text-slate-950 font-black text-xs shadow-lg shadow-amber-500/20 hover:scale-[1.02] transition">
                    Unggah Berkas Sekarang
                </button>
            </div>

        </div>
    </div>
</div>

@push('scripts')
<script>
    function registrationForm(min, max) {
        const isBlt = "{{ $competition->code }}" === "BLT";
        const isTmj = "{{ $competition->code }}" === "TMJ";
        const isColl = "{{ $competition->isCollective() ? 'true' : 'false' }}" === "true";
        const baseFeeVal = {{ $competition->registration_fee ?? 0 }};

        return {
            minMembers: min,
            maxMembers: max,
            isBuluTangkis: isBlt,
            isTenisMeja: isTmj,
            isCollective: isColl,
            baseFee: baseFeeVal,
            tierFees: @json($competition->tier_fees),
            targetClass: isTmj ? 'Kategori A (Kelas 1 - 3)' : 'Kategori A (Kelas 1 - 2)',
            matchType: 'Tunggal Putra (PA)',
            showWarningModal: false,
            isPaymentProofMissing: false,
            isDocumentFileMissing: false,
            members: [
                { full_name: '', nisn: '', gender: 'L', birth_place: '', birth_date: '', role_in_team: 'Peserta Utama' }
            ],
            get calculatedFee() {
                if (this.isBuluTangkis && this.tierFees) {
                    const isPutri = this.matchType.includes('(PI)') || this.matchType.includes('Putri');
                    if (this.matchType.includes('Ganda')) {
                        return isPutri ? (this.tierFees.ganda_pi || 200000) : (this.tierFees.ganda_pa || 200000);
                    }
                    if (this.targetClass.includes('Kategori A')) {
                        return isPutri ? (this.tierFees.A_tunggal_pi || 130000) : (this.tierFees.A_tunggal_pa || 130000);
                    }
                    if (this.targetClass.includes('Kategori B')) {
                        return isPutri ? (this.tierFees.B_tunggal_pi || 150000) : (this.tierFees.B_tunggal_pa || 150000);
                    }
                    if (this.targetClass.includes('Kategori C')) {
                        return isPutri ? (this.tierFees.C_tunggal_pi || 150000) : (this.tierFees.C_tunggal_pa || 150000);
                    }
                    return isPutri ? (this.tierFees.A_tunggal_pi || 130000) : (this.tierFees.A_tunggal_pa || 130000);
                }
                if (this.isTenisMeja && this.tierFees) {
                    const isPutri = this.matchType.includes('(PI)') || this.matchType.includes('Putri');
                    if (this.targetClass.includes('Kategori A')) {
                        return isPutri ? (this.tierFees.A_tunggal_pi || 35000) : (this.tierFees.A_tunggal_pa || 35000);
                    }
                    if (this.targetClass.includes('Kategori B')) {
                        return isPutri ? (this.tierFees.B_tunggal_pi || 35000) : (this.tierFees.B_tunggal_pa || 35000);
                    }
                    return isPutri ? (this.tierFees.A_tunggal_pi || 35000) : (this.tierFees.A_tunggal_pa || 35000);
                }
                return this.baseFee;
            },
            get formattedFee() {
                if (this.calculatedFee <= 0) {
                    return 'Gratis / Rp 0';
                }
                return 'Rp ' + Number(this.calculatedFee).toLocaleString('id-ID');
            },
            init() {
                if (this.isBuluTangkis || this.isTenisMeja) {
                    this.onMatchTypeChange();
                } else {
                    while(this.members.length < this.minMembers) {
                        this.members.push({ full_name: '', nisn: '', gender: 'L', birth_place: '', birth_date: '', role_in_team: 'Anggota ' + (this.members.length + 1) });
                    }
                }
            },
            onMatchTypeChange() {
                if (this.isTenisMeja) {
                    const isPutri = this.matchType.includes('(PI)') || this.matchType.includes('Putri');
                    const defaultGender = isPutri ? 'P' : 'L';
                    this.minMembers = 1;
                    this.maxMembers = 1;
                    this.members = [this.members[0]];
                    this.members[0].role_in_team = 'Peserta Tunggal';
                    this.members[0].gender = defaultGender;
                    this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
                    return;
                }

                if (!this.isBuluTangkis) return;

                const isPutri = this.matchType.includes('(PI)') || this.matchType.includes('Putri');
                const defaultGender = isPutri ? 'P' : 'L';

                if (this.matchType.includes('Ganda')) {
                    this.minMembers = 2;
                    this.maxMembers = 2;
                    while(this.members.length < 2) {
                        this.members.push({ full_name: '', nisn: '', gender: defaultGender, birth_place: '', birth_date: '', role_in_team: 'Pemain 2' });
                    }
                    this.members[0].role_in_team = 'Pemain 1 (Ketua)';
                    this.members[0].gender = defaultGender;
                    this.members[1].gender = defaultGender;
                } else {
                    this.minMembers = 1;
                    this.maxMembers = 1;
                    this.members = [this.members[0]];
                    this.members[0].role_in_team = 'Peserta Tunggal';
                    this.members[0].gender = defaultGender;
                }
                this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
            },
            addMember() {
                if (this.members.length < this.maxMembers) {
                    this.members.push({ full_name: '', nisn: '', gender: 'L', birth_place: '', birth_date: '', role_in_team: 'Anggota ' + (this.members.length + 1) });
                    this.$nextTick(() => lucide.createIcons());
                }
            },
            removeMember(index) {
                if (this.members.length > this.minMembers) {
                    this.members.splice(index, 1);
                }
            },
            handleSubmit(e) {
                const tfInput = document.querySelector('input[name="payment_proof"]');
                const docInput = document.querySelector('input[name="document_file"]');
                
                this.isPaymentProofMissing = !tfInput || !tfInput.files || tfInput.files.length === 0;
                this.isDocumentFileMissing = !docInput || !docInput.files || docInput.files.length === 0;

                if (this.isPaymentProofMissing || this.isDocumentFileMissing) {
                    this.showWarningModal = true;
                    return;
                }

                // If all files present, submit form
                document.getElementById('registration-main-form').submit();
            },
            focusMissingUpload() {
                this.showWarningModal = false;
                setTimeout(() => {
                    if (this.isPaymentProofMissing) {
                        const el = document.querySelector('input[name="payment_proof"]');
                        if (el) {
                            el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            el.click();
                        }
                    } else if (this.isDocumentFileMissing) {
                        const el = document.querySelector('input[name="document_file"]');
                        if (el) {
                            el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            el.click();
                        }
                    }
                }, 200);
            }
        }
    }
</script>
@endpush
@endsection
