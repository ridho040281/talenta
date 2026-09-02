@extends('layouts.admin')

@section('title', 'Pendaftaran Saya')
@section('page_title', 'Pendaftaran Saya')

@section('content')
<div class="space-y-8" 
     x-data="{ 
        searchQuery: '', 
        statusFilter: 'all',
        openMenuId: null,
        matchesSearch(text) {
            if (!this.searchQuery) return true;
            return text.toLowerCase().includes(this.searchQuery.toLowerCase());
        }
     }">
    
    <!-- SECTION 1: DAFTAR LOMBA YANG DIIKUTI (TABEL DATA) -->
    <div class="glass-card rounded-3xl p-6 sm:p-8 border border-slate-800/80 shadow-2xl space-y-6">
        
        <!-- Header & Action Buttons -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-800/80 pb-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-[#7A5AF8]/30 to-[#4E6EFF]/30 text-white flex items-center justify-center font-bold shrink-0 border border-[#7A5AF8]/40 shadow-lg shadow-[#7A5AF8]/20">
                    <i data-lucide="award" class="w-5 h-5 text-amber-400"></i>
                </div>
                <div>
                    <h3 class="text-lg font-black text-white font-display">Daftar Lomba yang Diikuti</h3>
                    <p class="text-xs text-slate-400">Data pendaftaran delegasi peserta lomba yang telah berhasil didaftarkan</p>
                </div>
            </div>
            
            <div class="flex items-center gap-2.5 shrink-0 flex-wrap">
                <span class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1.5 rounded-full bg-slate-900 text-slate-300 border border-slate-800">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>Total: {{ $registrations->count() }} Pendaftaran</span>
                </span>
                
                <a href="{{ route('peserta.dashboard') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-gradient-to-r from-[#7A5AF8] to-[#4E6EFF] hover:opacity-90 text-white font-black text-xs shadow-lg shadow-[#7A5AF8]/25 transition cursor-pointer">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    <span>+ Tambah Lomba Baru (Katalog)</span>
                </a>
            </div>
        </div>

        @if($registrations->isNotEmpty())
            <!-- Live Search & Status Filters Bar -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                <!-- Search Input -->
                <div class="relative flex-1 max-w-md">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                    <input 
                        type="text" 
                        x-model="searchQuery" 
                        placeholder="Cari nama peserta, tim, cabang lomba, kode reg..." 
                        class="w-full pl-9 pr-4 py-2.5 rounded-2xl bg-slate-900/90 border border-slate-700 text-xs text-white placeholder-slate-500 focus:bg-slate-900 focus:border-[#7A5AF8] focus:ring-1 focus:ring-[#7A5AF8] outline-none transition shadow-xs"
                    >
                    <button 
                        x-show="searchQuery" 
                        @click="searchQuery = ''" 
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-white"
                        title="Hapus pencarian"
                    >
                        <i data-lucide="x" class="w-3.5 h-3.5"></i>
                    </button>
                </div>

                <!-- Status Filter Pills -->
                <div class="flex items-center gap-1.5 overflow-x-auto pb-1 sm:pb-0 scrollbar-none">
                    <button 
                        type="button" 
                        @click="statusFilter = 'all'" 
                        :class="statusFilter === 'all' ? 'bg-gradient-to-r from-[#7A5AF8] to-[#4E6EFF] text-white font-black shadow-md shadow-[#7A5AF8]/25' : 'bg-slate-900 text-slate-300 hover:bg-slate-800 font-medium border border-slate-800'"
                        class="px-3.5 py-2 rounded-xl transition shrink-0 cursor-pointer"
                    >
                        Semua ({{ $registrations->count() }})
                    </button>
                    <button 
                        type="button" 
                        @click="statusFilter = 'verified'" 
                        :class="statusFilter === 'verified' ? 'bg-emerald-600 text-white font-bold' : 'bg-emerald-500/10 text-emerald-400 hover:bg-emerald-500/20 font-medium border border-emerald-500/30'"
                        class="px-3.5 py-2 rounded-xl transition shrink-0 cursor-pointer"
                    >
                        ✓ Terverifikasi ({{ $registrations->where('status', 'verified')->count() }})
                    </button>
                    <button 
                        type="button" 
                        @click="statusFilter = 'pending'" 
                        :class="statusFilter === 'pending' ? 'bg-amber-500 text-slate-950 font-bold' : 'bg-amber-500/10 text-amber-300 hover:bg-amber-500/20 font-medium border border-amber-500/30'"
                        class="px-3.5 py-2 rounded-xl transition shrink-0 cursor-pointer"
                    >
                        ⏳ Menunggu ({{ $registrations->whereIn('status', ['pending', 'draft', 'submitted'])->count() }})
                    </button>
                    @if($registrations->where('status', 'revision')->count() > 0)
                        <button 
                            type="button" 
                            @click="statusFilter = 'revision'" 
                            :class="statusFilter === 'revision' ? 'bg-orange-500 text-white font-bold' : 'bg-orange-500/10 text-orange-300 hover:bg-orange-500/20 font-medium border border-orange-500/30'"
                            class="px-3.5 py-2 rounded-xl transition shrink-0 cursor-pointer"
                        >
                            ⚠️ Revisi ({{ $registrations->where('status', 'revision')->count() }})
                        </button>
                    @endif
                    @if($registrations->where('status', 'rejected')->count() > 0)
                        <button 
                            type="button" 
                            @click="statusFilter = 'rejected'" 
                            :class="statusFilter === 'rejected' ? 'bg-rose-600 text-white font-bold' : 'bg-rose-500/10 text-rose-300 hover:bg-rose-500/20 font-medium border border-rose-500/30'"
                            class="px-3.5 py-2 rounded-xl transition shrink-0 cursor-pointer"
                        >
                            ✕ Ditolak ({{ $registrations->where('status', 'rejected')->count() }})
                        </button>
                    @endif
                </div>
            </div>

            <!-- Modern Dark Interactive Table Container -->
            <div class="overflow-x-auto rounded-2xl border border-slate-800 shadow-2xl">
                <table class="w-full text-left border-collapse min-w-[780px]">
                    <thead>
                        <tr class="bg-slate-950/80 border-b border-slate-800 text-[11px] font-black uppercase tracking-wider text-slate-400">
                            <th class="py-3.5 px-4 w-12 text-center">No</th>
                            <th class="py-3.5 px-4">Kode & No. Dada</th>
                            <th class="py-3.5 px-4">Cabang Perlombaan</th>
                            <th class="py-3.5 px-4">Nama Peserta / Delegasi</th>
                            <th class="py-3.5 px-4 text-center">No. Undian</th>
                            <th class="py-3.5 px-4 text-center">Status</th>
                            <th class="py-3.5 px-4 text-center w-56">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/80 text-xs">
                        @foreach($registrations as $reg)
                            @php
                                $statusGroup = in_array($reg->status, ['pending', 'draft', 'submitted']) ? 'pending' : $reg->status;
                                $searchableText = strtolower(
                                    $reg->registration_code . ' ' . 
                                    ($reg->participant_number ?? '') . ' ' . 
                                    ($reg->competition->name ?? '') . ' ' . 
                                    ($reg->competition->category->name ?? '') . ' ' . 
                                    ($reg->sub_category ?? '') . ' ' . 
                                    $reg->display_name . ' ' . 
                                    ($reg->team_name ?? '')
                                );
                            @endphp
                            <tr 
                                x-show="(statusFilter === 'all' || statusFilter === '{{ $statusGroup }}') && matchesSearch('{{ addslashes($searchableText) }}')"
                                class="hover:bg-slate-800/40 transition-colors group"
                            >
                                <!-- No -->
                                <td class="py-3.5 px-4 text-center font-mono font-bold text-slate-500">
                                    {{ $loop->iteration }}
                                </td>

                                <!-- Kode & No. Dada -->
                                <td class="py-3.5 px-4">
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-1.5">
                                            <span class="font-mono text-xs font-bold text-white bg-slate-800 px-2 py-0.5 rounded-lg border border-slate-700">
                                                {{ $reg->registration_code }}
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <span class="text-[10px] uppercase font-bold text-slate-400">No. Dada:</span>
                                            @if($reg->participant_number)
                                                <span class="font-mono font-black text-emerald-300 text-[11px] bg-emerald-500/20 px-1.5 py-0.2 rounded border border-emerald-500/30">
                                                    {{ $reg->participant_number }}
                                                </span>
                                            @else
                                                <span class="text-[10px] text-slate-500 italic">Menunggu Verifikasi</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <!-- Cabang Lomba -->
                                <td class="py-3.5 px-4">
                                    <div class="space-y-0.5">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                            {{ $reg->competition->category->name ?? 'Lomba' }}
                                        </span>
                                        <h5 class="font-extrabold text-white text-xs font-display">{{ $reg->competition->name }}</h5>
                                        @if($reg->sub_category)
                                            <span class="inline-flex items-center gap-1 text-[10px] font-bold text-amber-300 bg-amber-500/20 px-1.5 py-0.2 rounded border border-amber-500/30">
                                                🏸 {{ $reg->sub_category }}
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                <!-- Nama Peserta / Tim -->
                                <td class="py-3.5 px-4">
                                    <div class="space-y-0.5">
                                        <div class="font-bold text-white flex items-center gap-1.5">
                                            @if($reg->team_name)
                                                <span class="text-blue-400 font-extrabold">Tim:</span>
                                                <span>{{ $reg->team_name }}</span>
                                            @else
                                                <span>{{ $reg->display_name }}</span>
                                            @endif
                                        </div>
                                        <p class="text-[11px] text-slate-400">
                                            @if($reg->members && $reg->members->count() > 1)
                                                <span class="text-blue-400 font-semibold">{{ $reg->members->count() }} Anggota</span> • {{ $reg->display_name }} (Ketua)
                                            @else
                                                <span>Peserta Individu</span>
                                            @endif
                                        </p>
                                    </div>
                                </td>

                                <!-- No. Undian -->
                                <td class="py-3.5 px-4 text-center">
                                    @if($reg->draw_number)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black text-xs border border-amber-500/30 shadow-xs">
                                            #{{ $reg->draw_number }}
                                        </span>
                                    @else
                                        <span class="text-[11px] text-slate-500 font-medium italic">Belum diundi</span>
                                    @endif
                                </td>

                                <!-- Status Verifikasi -->
                                <td class="py-3.5 px-4 text-center">
                                    @if($reg->status === 'verified')
                                        <span class="inline-flex items-center gap-1.5 text-[11px] font-bold px-2.5 py-1 rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 shadow-xs">
                                            <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                                            <span>Terverifikasi</span>
                                        </span>
                                    @elseif($reg->status === 'revision')
                                        <span class="inline-flex items-center gap-1.5 text-[11px] font-bold px-2.5 py-1 rounded-full bg-orange-500/20 text-orange-300 border border-orange-500/30">
                                            <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                                            <span>Perlu Revisi</span>
                                        </span>
                                    @elseif($reg->status === 'rejected')
                                        <span class="inline-flex items-center gap-1.5 text-[11px] font-bold px-2.5 py-1 rounded-full bg-rose-500/20 text-rose-300 border border-rose-500/30">
                                            <i data-lucide="x-circle" class="w-3.5 h-3.5"></i>
                                            <span>Ditolak</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 text-[11px] font-bold px-2.5 py-1 rounded-full bg-amber-500/20 text-amber-300 border border-amber-500/30">
                                            <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                                            <span>Menunggu</span>
                                        </span>
                                    @endif
                                </td>

                                <!-- Aksi -->
                                <td class="py-3.5 px-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('peserta.registration.detail', $reg->id) }}" class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white font-bold text-xs transition border border-slate-700 shadow-sm">
                                            <i data-lucide="eye" class="w-3.5 h-3.5 text-[#7A5AF8]"></i>
                                            <span>Detail & Berkas</span>
                                        </a>

                                        @if($reg->status === 'verified')
                                            <a href="{{ route('document.print.registration', $reg->id) }}" target="_blank" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-300 font-bold text-xs border border-emerald-500/30 transition shadow-sm" title="Cetak Bukti Pendaftaran">
                                                <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                                                <span>Cetak</span>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Empty Search Results Alert -->
            <div x-show="searchQuery && !document.querySelector('tbody tr:not([style*=\'display: none\'])')" x-cloak class="text-center py-8 bg-slate-900/50 rounded-2xl border border-dashed border-slate-800">
                <p class="text-xs text-slate-400 font-medium">Tidak ada pendaftaran yang cocok dengan kata kunci "<span class="font-bold text-white" x-text="searchQuery"></span>".</p>
                <button type="button" @click="searchQuery = ''; statusFilter = 'all'" class="mt-2 text-xs font-bold text-[#7A5AF8] hover:text-[#9B82FA]">Reset Pencarian & Filter</button>
            </div>

        @else
            <!-- Empty State (Modern Dark Clean) -->
            <div class="text-center py-14 space-y-4 bg-slate-900/40 rounded-3xl border border-dashed border-slate-800">
                <div class="w-16 h-16 mx-auto rounded-3xl bg-slate-900 border border-slate-800 text-[#7A5AF8] flex items-center justify-center shadow-lg shadow-[#7A5AF8]/10">
                    <i data-lucide="folder-open" class="w-8 h-8"></i>
                </div>
                <div class="space-y-1">
                    <h4 class="text-base font-black text-white font-display">Belum Ada Cabang Lomba yang Didaftarkan</h4>
                    <p class="text-xs text-slate-400 max-w-md mx-auto">
                        Anda belum mendaftarkan peserta pada cabang lomba manapun. Buka halaman Dashboard untuk memilih cabang lomba yang ingin diikuti.
                    </p>
                </div>
                <div class="pt-2">
                    <a href="{{ route('peserta.dashboard') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-2xl bg-gradient-to-r from-[#7A5AF8] to-[#4E6EFF] text-white font-black text-xs shadow-lg shadow-[#7A5AF8]/25 hover:scale-105 transition">
                        <i data-lucide="layout-grid" class="w-4 h-4"></i>
                        <span>Buka Katalog Lomba di Dashboard</span>
                    </a>
                </div>
            </div>
        @endif
    </div>

    <!-- SECTION 2: PUSAT CETAK BERKAS ADMINISTRASI (BUKTI AKUN, BUKTI PENDAFTARAN, INVOICE) -->
    <div class="glass-card rounded-3xl p-6 sm:p-8 border border-slate-800/80 shadow-2xl space-y-6">
        <div class="flex items-center justify-between border-b border-slate-800/80 pb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-blue-500/20 to-indigo-500/20 text-blue-400 flex items-center justify-center font-bold shrink-0 border border-blue-500/30 shadow-lg shadow-blue-500/10">
                    <i data-lucide="printer" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-base sm:text-lg font-black text-white font-display">Cetak Berkas Administrasi Resmi</h3>
                    <p class="text-xs text-slate-400">Unduh dan cetak tanda bukti pembuatan akun, bukti pendaftaran lomba, dan invoice pembayaran</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            
            <!-- 1. BUKTI PEMBUATAN AKUN (1 Halaman A4 Standalone) -->
            <div class="p-5 rounded-3xl bg-slate-900/80 border border-slate-800 hover:border-blue-500/60 hover:shadow-[0_0_30px_rgba(59,130,246,0.18)] transition-all duration-300 flex flex-col justify-between space-y-4 group">
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="w-10 h-10 rounded-2xl bg-blue-500/20 text-blue-400 flex items-center justify-center font-bold border border-blue-500/30">
                            <i data-lucide="user-check" class="w-5 h-5"></i>
                        </div>
                        <span class="text-[10px] font-black uppercase px-2.5 py-0.5 rounded-full bg-blue-500/20 text-blue-300 border border-blue-500/30 font-mono">
                            A4 Slip
                        </span>
                    </div>
                    <div>
                        <h4 class="font-black text-white text-base group-hover:text-blue-300 transition font-display">
                            Bukti Pembuatan Akun
                        </h4>
                        <p class="text-xs text-slate-400 mt-1 leading-relaxed">
                            Lembar bukti sah registrasi akun sistem login peserta (NISN/ID: <strong class="text-slate-200">{{ $user->nisn ?: $user->email }}</strong>, Instansi: <strong class="text-slate-200">{{ $user->institution_name ?: '-' }}</strong>).
                        </p>
                    </div>
                </div>

                <a href="{{ route('register.success') }}" target="_blank" class="w-full py-2.5 px-4 rounded-2xl bg-blue-600 hover:bg-blue-500 text-white font-black text-xs flex items-center justify-center gap-2 shadow-lg shadow-blue-600/20 hover:scale-[1.02] transition cursor-pointer">
                    <i data-lucide="printer" class="w-4 h-4"></i>
                    <span>Cetak Bukti Pembuatan Akun</span>
                </a>
            </div>

            <!-- 2. BUKTI PENDAFTARAN LOMBA (Semua / Kolektif) -->
            <div class="p-5 rounded-3xl bg-slate-900/80 border border-slate-800 hover:border-emerald-500/60 hover:shadow-[0_0_30px_rgba(16,185,129,0.18)] transition-all duration-300 flex flex-col justify-between space-y-4 group">
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="w-10 h-10 rounded-2xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold border border-emerald-500/30">
                            <i data-lucide="file-check" class="w-5 h-5"></i>
                        </div>
                        <span class="text-[10px] font-black uppercase px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 font-mono">
                            Formulir Sah
                        </span>
                    </div>
                    <div>
                        <h4 class="font-black text-white text-base group-hover:text-emerald-300 transition font-display">
                            Bukti Pendaftaran Lomba
                        </h4>
                        <p class="text-xs text-slate-400 mt-1 leading-relaxed">
                            Formulir pendaftaran resmi seluruh delegasi lomba, nomor dada atlet, biodata anggota, dan stempel panitia pelaksana.
                        </p>
                    </div>
                </div>

                @if($registrations->isNotEmpty())
                    <a href="{{ route('document.print.collective') }}" target="_blank" class="w-full py-2.5 px-4 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-slate-950 font-black text-xs flex items-center justify-center gap-2 shadow-lg shadow-emerald-600/20 hover:scale-[1.02] transition cursor-pointer">
                        <i data-lucide="printer" class="w-4 h-4"></i>
                        <span>Cetak Semua Bukti Pendaftaran</span>
                    </a>
                @else
                    <button type="button" disabled class="w-full py-2.5 px-4 rounded-2xl bg-slate-800 text-slate-500 font-bold text-xs flex items-center justify-center gap-2 cursor-not-allowed">
                        <i data-lucide="lock" class="w-4 h-4"></i>
                        <span>Belum Ada Pendaftaran</span>
                    </button>
                @endif
            </div>

            <!-- 3. KWITANSI / INVOICE PEMBAYARAN -->
            <div class="p-5 rounded-3xl bg-slate-900/80 border border-slate-800 hover:border-amber-500/60 hover:shadow-[0_0_30px_rgba(245,158,11,0.18)] transition-all duration-300 flex flex-col justify-between space-y-4 group">
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="w-10 h-10 rounded-2xl bg-amber-500/20 text-amber-400 flex items-center justify-center font-bold border border-amber-500/30">
                            <i data-lucide="receipt" class="w-5 h-5"></i>
                        </div>
                        <span class="text-[10px] font-black uppercase px-2.5 py-0.5 rounded-full bg-amber-500/20 text-amber-300 border border-amber-500/30 font-mono">
                            Kwitansi
                        </span>
                    </div>
                    <div>
                        <h4 class="font-black text-white text-base group-hover:text-amber-300 transition font-display">
                            Kwitansi / Invoice Resmi
                        </h4>
                        <p class="text-xs text-slate-400 mt-1 leading-relaxed">
                            @if(isset($invoices) && $invoices->isNotEmpty())
                                Tersedia <strong>{{ $invoices->count() }} Dokumen Tagihan/Kwitansi</strong> resmi rincian pembayaran pendaftaran.
                            @else
                                Bukti tanda terima pembayaran pendaftaran kontingen lomba berstempel bendahara panitia.
                            @endif
                        </p>
                    </div>
                </div>

                @if(isset($invoices) && $invoices->isNotEmpty())
                    <a href="{{ route('peserta.invoices.show', $invoices->first()->id) }}" class="w-full py-2.5 px-4 rounded-2xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs flex items-center justify-center gap-2 shadow-lg shadow-amber-500/20 hover:scale-[1.02] transition cursor-pointer">
                        <i data-lucide="receipt" class="w-4 h-4"></i>
                        <span>Lihat & Cetak Invoice ({{ $invoices->first()->invoice_number }})</span>
                    </a>
                @elseif($registrations->where('status', 'verified')->isNotEmpty())
                    <a href="{{ route('document.print.receipt', $registrations->where('status', 'verified')->first()->id) }}" target="_blank" class="w-full py-2.5 px-4 rounded-2xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs flex items-center justify-center gap-2 shadow-lg shadow-amber-500/20 hover:scale-[1.02] transition cursor-pointer">
                        <i data-lucide="printer" class="w-4 h-4"></i>
                        <span>Cetak Kwitansi Pembayaran</span>
                    </a>
                @else
                    <button type="button" @click="alert('Kwitansi / Invoice resmi dapat dicetak setelah pendaftaran Anda diverifikasi dan berstatus lunas oleh panitia.')" class="w-full py-2.5 px-4 rounded-2xl bg-slate-800 text-slate-400 hover:text-slate-200 font-bold text-xs flex items-center justify-center gap-2 transition cursor-pointer">
                        <i data-lucide="info" class="w-4 h-4 text-amber-400"></i>
                        <span>Status Invoice / Kwitansi</span>
                    </button>
                @endif
            </div>

        </div>
    </div>

</div>
@endsection
