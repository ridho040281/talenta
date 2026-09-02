@extends('layouts.admin')

@section('title', 'Pengaturan Aplikasi & Rekening Pembayaran')
@section('page_title', 'Pengaturan Aplikasi & Sistem')

@section('content')
<div x-data="{ activeTab: 'pembayaran' }" class="space-y-6">
    
    <!-- Top Header Bar (AIStarterKit Dark Style) -->
    <div class="ai-card rounded-3xl p-6 sm:p-8 border border-white/[0.08] shadow-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="space-y-1.5">
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-400">
                <span>TALENTA Admin</span>
                <span>/</span>
                <span>Pengaturan Sistem</span>
                <span>/</span>
                <span class="text-[#84D0FF] font-bold">Konfigurasi</span>
            </div>
            <h2 class="text-xl sm:text-3xl font-black tracking-tight text-white font-display">Pengaturan Sistem & Rekening Bank</h2>
            <p class="text-xs text-slate-400">Kelola rekening tujuan transfer, identitas instansi, kop surat resmi, dan status pendaftaran</p>
        </div>

        <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-white/[0.06] hover:bg-white/[0.12] text-slate-300 hover:text-white font-bold text-xs border border-white/[0.08] hover:border-white/[0.15] transition shadow-sm">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Kembali ke Dashboard</span>
        </a>
    </div>

    <!-- Navigation Tabs (AIStarterKit Dark Tabs) -->
    <div class="flex items-center gap-2.5 pb-2 overflow-x-auto no-scrollbar">
        <button type="button" @click="activeTab = 'pembayaran'" :class="activeTab === 'pembayaran' ? 'gradient-btn text-white font-black shadow-lg shadow-[#7A5AF8]/25' : 'bg-white/[0.04] hover:bg-white/[0.08] text-slate-400 hover:text-slate-200 font-bold border border-white/[0.08]'" class="flex items-center gap-2 px-4 py-2.5 rounded-2xl text-xs transition whitespace-nowrap cursor-pointer">
            <i data-lucide="credit-card" class="w-4 h-4"></i>
            <span>💳 Rekening & Pembayaran</span>
        </button>

        <button type="button" @click="activeTab = 'identitas'" :class="activeTab === 'identitas' ? 'gradient-btn text-white font-black shadow-lg shadow-[#7A5AF8]/25' : 'bg-white/[0.04] hover:bg-white/[0.08] text-slate-400 hover:text-slate-200 font-bold border border-white/[0.08]'" class="flex items-center gap-2 px-4 py-2.5 rounded-2xl text-xs transition whitespace-nowrap cursor-pointer">
            <i data-lucide="building-2" class="w-4 h-4"></i>
            <span>🏫 Identitas Instansi & Logo</span>
        </button>

        <button type="button" @click="activeTab = 'landing'" :class="activeTab === 'landing' ? 'gradient-btn text-white font-black shadow-lg shadow-[#7A5AF8]/25' : 'bg-white/[0.04] hover:bg-white/[0.08] text-slate-400 hover:text-slate-200 font-bold border border-white/[0.08]'" class="flex items-center gap-2 px-4 py-2.5 rounded-2xl text-xs transition whitespace-nowrap cursor-pointer">
            <i data-lucide="layout-template" class="w-4 h-4"></i>
            <span>🌐 Konten Landing Page</span>
        </button>

        <button type="button" @click="activeTab = 'system'" :class="activeTab === 'system' ? 'gradient-btn text-white font-black shadow-lg shadow-[#7A5AF8]/25' : 'bg-white/[0.04] hover:bg-white/[0.08] text-slate-400 hover:text-slate-200 font-bold border border-white/[0.08]'" class="flex items-center gap-2 px-4 py-2.5 rounded-2xl text-xs transition whitespace-nowrap cursor-pointer">
            <i data-lucide="server" class="w-4 h-4"></i>
            <span>⚙️ Status Server & Engine</span>
        </button>
    </div>


    <!-- TAB 1: REKENING & PEMBAYARAN (HIGHLIGHTED TAB) -->
    <div x-show="activeTab === 'pembayaran'" x-transition class="space-y-6">
        <div class="ai-card rounded-3xl border border-white/[0.08] shadow-2xl p-6 sm:p-8 space-y-6">
            
            <div class="flex items-center gap-3 border-b border-white/[0.08] pb-4">
                <div class="w-10 h-10 rounded-2xl bg-emerald-500/15 text-emerald-400 border border-emerald-500/30 flex items-center justify-center font-bold shadow-xs">
                    <i data-lucide="credit-card" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-base sm:text-lg font-black text-white font-display">
                        Pengaturan Rekening Bank & Informasi Pembayaran
                    </h3>
                    <p class="text-xs text-slate-400">
                        Rekening ini otomatis ditampilkan kepada peserta saat mendaftar mandiri maupun upload batch kolektif.
                    </p>
                </div>
            </div>

            <form action="{{ route('admin.settings.general.update') }}" method="POST" class="space-y-6">
                @csrf

                <!-- Bank Info Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    
                    <div class="space-y-1.5 bg-[#0C111D]/80 p-4 rounded-2xl border border-white/[0.08]">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-300">
                            Nama Bank / E-Wallet <span class="text-rose-400">*</span>
                        </label>
                        <p class="text-[10px] text-slate-500">Contoh: BSI, BRI, BCA, Mandiri</p>
                        <input type="text" name="bank_name" required value="{{ old('bank_name', $settings['bank_name'] ?? 'Bank Syariah Indonesia (BSI)') }}" class="block w-full px-3.5 py-2.5 rounded-xl bg-[#161F30] border border-white/[0.1] text-white text-xs font-bold focus:border-[#7A5AF8] outline-none">
                    </div>

                    <div class="space-y-1.5 bg-[#0C111D]/80 p-4 rounded-2xl border border-white/[0.08]">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-300">
                            Nomor Rekening <span class="text-rose-400">*</span>
                        </label>
                        <p class="text-[10px] text-slate-500">Nomor rekening tanpa spasi / tanda hubung</p>
                        <input type="text" name="bank_account_number" required value="{{ old('bank_account_number', $settings['bank_account_number'] ?? '7123456789') }}" class="block w-full px-3.5 py-2.5 rounded-xl bg-[#161F30] border border-white/[0.1] text-amber-300 text-xs font-mono font-black focus:border-[#7A5AF8] outline-none">
                    </div>

                    <div class="space-y-1.5 bg-[#0C111D]/80 p-4 rounded-2xl border border-white/[0.08]">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-300">
                            Atas Nama Pemilik Rekening <span class="text-rose-400">*</span>
                        </label>
                        <p class="text-[10px] text-slate-500">Nama resmi pemilik rekening / panitia</p>
                        <input type="text" name="bank_account_holder" required value="{{ old('bank_account_holder', $settings['bank_account_holder'] ?? 'Panitia TALENTA MTsN 1 Blitar') }}" class="block w-full px-3.5 py-2.5 rounded-xl bg-[#161F30] border border-white/[0.1] text-white text-xs font-semibold focus:border-[#7A5AF8] outline-none">
                    </div>

                </div>

                <!-- Preview Rekening Box -->
                <div class="p-6 rounded-2xl bg-gradient-to-r from-[#101828] via-[#161F30] to-emerald-950/60 border border-emerald-500/30 text-white space-y-3 shadow-lg">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] uppercase font-black tracking-widest text-emerald-400 flex items-center gap-1.5">
                            <i data-lucide="shield-check" class="w-4 h-4 text-emerald-400"></i>
                            <span>Pratinjau Tampilan Rekening di Portal Peserta</span>
                        </span>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/15 text-emerald-300 border border-emerald-500/30">Live Preview</span>
                    </div>
                    <div class="space-y-1 pt-1">
                        <p class="text-xs text-slate-400">Bank Tujuan Transfer:</p>
                        <h4 class="text-lg font-black text-white">{{ $settings['bank_name'] ?? 'Bank Syariah Indonesia (BSI)' }}</h4>
                        <p class="font-mono text-2xl font-black text-amber-300 tracking-wider">{{ $settings['bank_account_number'] ?? '7123456789' }}</p>
                        <p class="text-xs text-slate-300">a.n. <strong class="text-white">{{ $settings['bank_account_holder'] ?? 'Panitia TALENTA MTsN 1 Blitar' }}</strong></p>
                    </div>
                </div>

                <div class="pt-4 border-t border-white/[0.08] flex items-center justify-end">
                    <button type="submit" class="gradient-btn px-6 py-2.5 rounded-2xl text-white font-bold text-xs shadow-lg shadow-[#7A5AF8]/25 transition flex items-center gap-2 cursor-pointer">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        <span>Simpan Pengaturan Rekening</span>
                    </button>
                </div>

            </form>

        </div>
    </div>

    <!-- TAB 2: IDENTITAS INSTANSI & LOGO -->
    <div x-show="activeTab === 'identitas'" x-transition class="space-y-6">
        <div class="ai-card rounded-3xl border border-white/[0.08] shadow-2xl p-6 sm:p-8 space-y-6">
            
            <div class="flex items-center gap-3 border-b border-white/[0.08] pb-4">
                <div class="w-10 h-10 rounded-2xl bg-[#4E6EFF]/15 text-[#84D0FF] border border-[#4E6EFF]/30 flex items-center justify-center font-bold shadow-xs">
                    <i data-lucide="building-2" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-base sm:text-lg font-black text-white font-display">
                        Identitas Instansi & Headings Surat Resmi
                    </h3>
                    <p class="text-xs text-slate-400">Nama madrasah, kepala, ketua panitia, dan gambar kop surat resmi</p>
                </div>
            </div>

            <form action="{{ route('admin.settings.general.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <!-- Identitas Aplikasi vs Identitas Kegiatan -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Nama Aplikasi (Software/System) -->
                    <div class="space-y-1 bg-[#0C111D]/80 p-4 rounded-2xl border border-white/[0.08]">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-300">
                            Nama Aplikasi (Sistem / Platform) <span class="text-rose-400">*</span>
                        </label>
                        <p class="text-[10px] text-slate-500">Nama sistem aplikasi manajemen lomba (misal: <strong>TALENTA</strong>)</p>
                        <input type="text" name="app_name" required value="{{ old('app_name', $settings['app_name']) }}" class="block w-full px-3.5 py-2.5 rounded-xl bg-[#161F30] border border-white/[0.1] text-white text-xs font-bold focus:border-[#7A5AF8] outline-none">
                    </div>

                    <!-- Nama Kegiatan / Event Acara -->
                    <div class="space-y-1 bg-[#0C111D]/80 p-4 rounded-2xl border border-white/[0.08]">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-300">
                            Nama Kegiatan / Event Acara <span class="text-rose-400">*</span>
                        </label>
                        <p class="text-[10px] text-slate-500">Nama agenda kegiatan lomba (misal: <strong>Milad ke-57 MTsN 1 Blitar</strong>)</p>
                        <input type="text" name="event_name" required value="{{ old('event_name', $settings['event_name'] ?? 'Milad ke-57 MTsN 1 Blitar') }}" class="block w-full px-3.5 py-2.5 rounded-xl bg-[#161F30] border border-white/[0.1] text-white text-xs font-bold focus:border-[#7A5AF8] outline-none">
                    </div>
                </div>

                <!-- Nama Instansi & Tahun Event -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="sm:col-span-2 space-y-1 bg-[#0C111D]/80 p-4 rounded-2xl border border-white/[0.08]">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-300">
                            Nama Instansi / Madrasah Penyelenggara <span class="text-rose-400">*</span>
                        </label>
                        <input type="text" name="institution_name" required value="{{ old('institution_name', $settings['institution_name']) }}" class="block w-full px-3.5 py-2.5 rounded-xl bg-[#161F30] border border-white/[0.1] text-white text-xs font-semibold focus:border-[#7A5AF8] outline-none">
                    </div>

                    <div class="space-y-1 bg-[#0C111D]/80 p-4 rounded-2xl border border-white/[0.08]">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-300">
                            Tahun Kegiatan <span class="text-rose-400">*</span>
                        </label>
                        <input type="text" name="event_year" required value="{{ old('event_year', $settings['event_year'] ?? '2026') }}" class="block w-full px-3.5 py-2.5 rounded-xl bg-[#161F30] border border-white/[0.1] text-white text-xs font-mono font-bold focus:border-[#7A5AF8] outline-none">
                    </div>
                </div>

                <!-- Kepala Madrasah & NIP -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1 bg-[#0C111D]/80 p-4 rounded-2xl border border-white/[0.08]">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-300">
                            Nama Kepala Madrasah <span class="text-rose-400">*</span>
                        </label>
                        <input type="text" name="headmaster_name" value="{{ old('headmaster_name', $settings['headmaster_name']) }}" class="block w-full px-3.5 py-2.5 rounded-xl bg-[#161F30] border border-white/[0.1] text-white text-xs font-semibold focus:border-[#7A5AF8] outline-none">
                    </div>

                    <div class="space-y-1 bg-[#0C111D]/80 p-4 rounded-2xl border border-white/[0.08]">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-300">
                            NIP Kepala Madrasah
                        </label>
                        <input type="text" name="headmaster_nip" value="{{ old('headmaster_nip', $settings['headmaster_nip']) }}" class="block w-full px-3.5 py-2.5 rounded-xl bg-[#161F30] border border-white/[0.1] text-white text-xs font-mono focus:border-[#7A5AF8] outline-none">
                    </div>
                </div>

                <!-- Ketua Panitia & NIP -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1 bg-[#0C111D]/80 p-4 rounded-2xl border border-white/[0.08]">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-300">
                            Nama Ketua Panitia <span class="text-rose-400">*</span>
                        </label>
                        <input type="text" name="committee_chairman_name" value="{{ old('committee_chairman_name', $settings['committee_chairman_name']) }}" class="block w-full px-3.5 py-2.5 rounded-xl bg-[#161F30] border border-white/[0.1] text-white text-xs font-semibold focus:border-[#7A5AF8] outline-none">
                    </div>

                    <div class="space-y-1 bg-[#0C111D]/80 p-4 rounded-2xl border border-white/[0.08]">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-300">
                            NIP Ketua Panitia
                        </label>
                        <input type="text" name="committee_chairman_nip" value="{{ old('committee_chairman_nip', $settings['committee_chairman_nip']) }}" class="block w-full px-3.5 py-2.5 rounded-xl bg-[#161F30] border border-white/[0.1] text-white text-xs font-mono focus:border-[#7A5AF8] outline-none">
                    </div>
                </div>

                <!-- Alamat Lengkap -->
                <div class="space-y-1 bg-[#0C111D]/80 p-4 rounded-2xl border border-white/[0.08]">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-300">
                        Alamat Lengkap <span class="text-rose-400">*</span>
                    </label>
                    <textarea name="address" rows="2" class="block w-full px-3.5 py-2.5 rounded-xl bg-[#161F30] border border-white/[0.1] text-white text-xs font-semibold focus:border-[#7A5AF8] outline-none">{{ old('address', $settings['address']) }}</textarea>
                </div>

                <!-- Telepon & Email -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1 bg-[#0C111D]/80 p-4 rounded-2xl border border-white/[0.08]">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-300">
                            Telepon / WhatsApp <span class="text-rose-400">*</span>
                        </label>
                        <input type="text" name="contact_phone" value="{{ old('contact_phone', $settings['contact_phone']) }}" class="block w-full px-3.5 py-2.5 rounded-xl bg-[#161F30] border border-white/[0.1] text-white text-xs font-semibold focus:border-[#7A5AF8] outline-none">
                    </div>

                    <div class="space-y-1 bg-[#0C111D]/80 p-4 rounded-2xl border border-white/[0.08]">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-300">
                            Email Resmi <span class="text-rose-400">*</span>
                        </label>
                        <input type="email" name="contact_email" value="{{ old('contact_email', $settings['contact_email']) }}" class="block w-full px-3.5 py-2.5 rounded-xl bg-[#161F30] border border-white/[0.1] text-white text-xs font-semibold focus:border-[#7A5AF8] outline-none">
                    </div>
                </div>

                <!-- SECTION: LOGO APLIKASI & FAVICON -->
                <div class="pt-4 border-t border-white/[0.08] space-y-4">
                    <h4 class="text-xs font-black text-white uppercase tracking-wider">Logo Aplikasi & Icon</h4>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        
                        <!-- Logo Aplikasi -->
                        <div class="p-4 rounded-2xl bg-[#0C111D]/80 border border-white/[0.08] space-y-3">
                            <div>
                                <span class="text-xs font-bold text-white block">Logo Aplikasi & Navbar</span>
                                <span class="text-[10px] text-slate-400 block">Digunakan untuk Topbar, Navbar, & Login.</span>
                            </div>

                            <div class="w-full h-16 rounded-xl bg-[#161F30] border border-white/[0.08] flex items-center justify-center p-2">
                                @if(!empty($settings['app_logo']))
                                    <img src="{{ asset('storage/' . $settings['app_logo']) }}" alt="Logo Aplikasi" class="max-h-full max-w-full object-contain">
                                @else
                                    <div class="flex items-center gap-2 text-[#84D0FF] font-bold text-xs">
                                        <i data-lucide="award" class="w-5 h-5"></i>
                                        <span>TALENTA 2026</span>
                                    </div>
                                @endif
                            </div>

                            <div class="space-y-1.5">
                                <input type="file" name="app_logo" accept="image/*" class="block w-full text-[11px] text-slate-400 file:mr-2 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-[11px] file:font-bold file:bg-[#7A5AF8]/20 file:text-[#A594FD] hover:file:bg-[#7A5AF8]/30 cursor-pointer">
                                @if(!empty($settings['app_logo']))
                                    <label class="inline-flex items-center gap-2 text-[10px] text-rose-400 font-medium cursor-pointer">
                                        <input type="checkbox" name="delete_app_logo" value="1" class="rounded border-rose-400/40 text-rose-600 focus:ring-rose-500">
                                        <span>Hapus Logo Aplikasi</span>
                                    </label>
                                @endif
                            </div>
                        </div>

                        <!-- Favicon -->
                        <div class="p-4 rounded-2xl bg-[#0C111D]/80 border border-white/[0.08] space-y-3">
                            <div>
                                <span class="text-xs font-bold text-white block">Favicon Browser</span>
                                <span class="text-[10px] text-slate-400 block">Ikon tab pada browser.</span>
                            </div>

                            <div class="w-full h-16 rounded-xl bg-[#161F30] border border-white/[0.08] flex items-center justify-center p-2">
                                @if(!empty($settings['favicon']))
                                    <img src="{{ asset('storage/' . $settings['favicon']) }}" alt="Favicon" class="w-8 h-8 object-contain">
                                @else
                                    <div class="w-8 h-8 rounded-lg bg-[#4E6EFF]/20 text-[#84D0FF] border border-[#4E6EFF]/30 flex items-center justify-center font-bold">
                                        <i data-lucide="globe" class="w-4 h-4"></i>
                                    </div>
                                @endif
                            </div>

                            <div class="space-y-1.5">
                                <input type="file" name="favicon" accept="image/*" class="block w-full text-[11px] text-slate-400 file:mr-2 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-[11px] file:font-bold file:bg-[#4E6EFF]/20 file:text-[#84D0FF] hover:file:bg-[#4E6EFF]/30 cursor-pointer">
                                @if(!empty($settings['favicon']))
                                    <label class="inline-flex items-center gap-2 text-[10px] text-rose-400 font-medium cursor-pointer">
                                        <input type="checkbox" name="delete_favicon" value="1" class="rounded border-rose-400/40 text-rose-600 focus:ring-rose-500">
                                        <span>Hapus Favicon</span>
                                    </label>
                                @endif
                            </div>
                        </div>

                    </div>
                </div>

                <!-- SECTION: KOP SURAT RESMI (KOP LEMBAGA & KOP KEGIATAN) -->
                <div class="pt-4 border-t border-white/[0.08] space-y-4">
                    <div>
                        <h4 class="text-xs font-black text-white uppercase tracking-wider">Kop Surat Resmi (KOP Lembaga & KOP Kegiatan)</h4>
                        <p class="text-[11px] text-slate-400">Upload gambar banner kop surat untuk dokumen cetak PDF, kartu peserta, daftar nominatif, dan lembar penilaian.</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        
                        <!-- KOP Lembaga -->
                        <div class="p-4 rounded-2xl bg-[#0C111D]/80 border border-white/[0.08] space-y-3">
                            <div>
                                <span class="text-xs font-bold text-white block">KOP Lembaga / Instansi</span>
                                <span class="text-[10px] text-slate-400 block">Header kop resmi madrasah / kementerian agama.</span>
                            </div>

                            <div class="w-full h-24 rounded-xl bg-white border border-white/[0.1] flex items-center justify-center p-2 overflow-hidden shadow-inner">
                                @if(!empty($settings['kop_lembaga']))
                                    <img src="{{ asset('storage/' . $settings['kop_lembaga']) }}" alt="KOP Lembaga" class="max-h-full max-w-full object-contain">
                                @elseif(!empty($settings['letterhead_image']))
                                    <img src="{{ asset('storage/' . $settings['letterhead_image']) }}" alt="KOP Lembaga" class="max-h-full max-w-full object-contain">
                                @else
                                    <div class="flex flex-col items-center gap-1 text-slate-400 text-xs">
                                        <i data-lucide="file-text" class="w-6 h-6 text-slate-300"></i>
                                        <span class="text-[10px]">Belum ada KOP Lembaga</span>
                                    </div>
                                @endif
                            </div>

                            <div class="space-y-1.5">
                                <input type="file" name="kop_lembaga" accept="image/*" class="block w-full text-[11px] text-slate-400 file:mr-2 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-[11px] file:font-bold file:bg-[#4E6EFF]/20 file:text-[#84D0FF] hover:file:bg-[#4E6EFF]/30 cursor-pointer">
                                
                                @if(!empty($settings['kop_lembaga']) || !empty($settings['letterhead_image']))
                                    <label class="inline-flex items-center gap-2 text-[10px] text-rose-400 font-medium cursor-pointer">
                                        <input type="checkbox" name="delete_kop_lembaga" value="1" class="rounded border-rose-400/40 text-rose-600 focus:ring-rose-500">
                                        <span>Hapus KOP Lembaga</span>
                                    </label>
                                @endif
                            </div>
                        </div>

                        <!-- KOP Kegiatan -->
                        <div class="p-4 rounded-2xl bg-[#0C111D]/80 border border-white/[0.08] space-y-3">
                            <div>
                                <span class="text-xs font-bold text-white block">KOP Kegiatan / Kepanitiaan</span>
                                <span class="text-[10px] text-slate-400 block">Header kop resmi panitia pelaksana TALENTA.</span>
                            </div>

                            <div class="w-full h-24 rounded-xl bg-white border border-white/[0.1] flex items-center justify-center p-2 overflow-hidden shadow-inner">
                                @if(!empty($settings['kop_kegiatan']))
                                    <img src="{{ asset('storage/' . $settings['kop_kegiatan']) }}" alt="KOP Kegiatan" class="max-h-full max-w-full object-contain">
                                @else
                                    <div class="flex flex-col items-center gap-1 text-slate-400 text-xs">
                                        <i data-lucide="award" class="w-6 h-6 text-slate-300"></i>
                                        <span class="text-[10px]">Belum ada KOP Kegiatan</span>
                                    </div>
                                @endif
                            </div>

                            <div class="space-y-1.5">
                                <input type="file" name="kop_kegiatan" accept="image/*" class="block w-full text-[11px] text-slate-400 file:mr-2 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-[11px] file:font-bold file:bg-amber-500/20 file:text-amber-300 hover:file:bg-amber-500/30 cursor-pointer">
                                
                                @if(!empty($settings['kop_kegiatan']))
                                    <label class="inline-flex items-center gap-2 text-[10px] text-rose-400 font-medium cursor-pointer">
                                        <input type="checkbox" name="delete_kop_kegiatan" value="1" class="rounded border-rose-400/40 text-rose-600 focus:ring-rose-500">
                                        <span>Hapus KOP Kegiatan</span>
                                    </label>
                                @endif
                            </div>
                        </div>

                    </div>
                </div>

                <div class="pt-4 border-t border-white/[0.08] flex items-center justify-end">
                    <button type="submit" class="gradient-btn px-6 py-2.5 rounded-2xl text-white font-bold text-xs shadow-lg shadow-[#7A5AF8]/25 transition flex items-center gap-2 cursor-pointer">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        <span>Simpan Pengaturan Identitas</span>
                    </button>
                </div>

            </form>

        </div>
    </div>

    <!-- TAB 3: KONTEN & NARASI LANDING PAGE -->
    <div x-show="activeTab === 'landing'" x-transition class="space-y-6">
        <div class="ai-card rounded-3xl border border-white/[0.08] shadow-2xl p-6 sm:p-8 space-y-8">
            
            <div class="flex items-center justify-between border-b border-white/[0.08] pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-emerald-500/15 text-emerald-400 border border-emerald-500/30 flex items-center justify-center font-bold shadow-xs">
                        <i data-lucide="layout-template" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-base sm:text-lg font-black text-white font-display">
                            Kustomisasi Teks & Narasi Halaman Landing (Beranda)
                        </h3>
                        <p class="text-xs text-slate-400">Atur seluruh teks headline, alur tahapan, judul katalog, narasi ajakan, dan footer aplikasi.</p>
                    </div>
                </div>
                <a href="{{ route('home') }}" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-2xl bg-white/[0.06] hover:bg-white/[0.12] text-slate-300 hover:text-white text-xs font-bold border border-white/[0.08] transition">
                    <i data-lucide="external-link" class="w-3.5 h-3.5 text-emerald-400"></i>
                    <span>Lihat Beranda</span>
                </a>
            </div>

            <form action="{{ route('admin.settings.general.update') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                @csrf

                <!-- SECTION 1: HERO (HEADLINE & SUBTITLE BERANDA) -->
                <div class="space-y-4">
                    <div class="flex items-center gap-2 border-b border-white/[0.08] pb-2">
                        <i data-lucide="sparkles" class="w-4 h-4 text-amber-400"></i>
                        <h4 class="text-xs font-black text-white uppercase tracking-wider">1. Hero Section (Ucapan Selamat Datang & Subtitle Beranda)</h4>
                    </div>

                    <div class="space-y-3">
                        <div class="space-y-1.5 bg-[#0C111D]/80 p-4 rounded-2xl border border-white/[0.08]">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-300">Judul Utama (Headline / Ucapan Selamat Datang)</label>
                            <p class="text-[10px] text-slate-500">Tampil di bawah kaligrafi Ahlan Wa Sahlan pada bagian paling atas Beranda.</p>
                            <input type="text" name="hero_title" value="{{ old('hero_title', $settings['hero_title']) }}" placeholder="Selamat Datang di TALENTA MTsN 1 Blitar" class="block w-full px-3.5 py-2.5 rounded-xl bg-[#161F30] border border-white/[0.1] text-white text-xs font-bold focus:border-[#7A5AF8] outline-none">
                        </div>

                        <div class="space-y-1.5 bg-[#0C111D]/80 p-4 rounded-2xl border border-white/[0.08]">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-300">Deskripsi / Subtitle Hero</label>
                            <p class="text-[10px] text-slate-500">Teks penjelas singkat di bawah judul headline Beranda.</p>
                            <textarea name="hero_subtitle" rows="2" placeholder="Platform manajemen perlombaan MTsN 1 Blitar." class="block w-full px-3.5 py-2.5 rounded-xl bg-[#161F30] border border-white/[0.1] text-white text-xs font-medium focus:border-[#7A5AF8] outline-none leading-relaxed">{{ old('hero_subtitle', $settings['hero_subtitle']) }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- SECTION 2: TAHAPAN PARTISIPASI (ALUR 4 LANGKAH) -->
                <div class="space-y-4 pt-2">
                    <div class="flex items-center gap-2 border-b border-white/[0.08] pb-2">
                        <i data-lucide="list-ordered" class="w-4 h-4 text-[#84D0FF]"></i>
                        <h4 class="text-xs font-black text-white uppercase tracking-wider">2. Alur & Tahapan Partisipasi</h4>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="space-y-1 bg-[#0C111D]/80 p-4 rounded-2xl border border-white/[0.08]">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-300">Tagline Section</label>
                            <input type="text" name="how_it_works_tagline" value="{{ old('how_it_works_tagline', $settings['how_it_works_tagline']) }}" placeholder="Tahapan Partisipasi" class="block w-full px-3 py-2 rounded-xl bg-[#161F30] border border-white/[0.1] text-white text-xs font-semibold focus:border-[#7A5AF8] outline-none">
                        </div>
                        <div class="space-y-1 sm:col-span-2 bg-[#0C111D]/80 p-4 rounded-2xl border border-white/[0.08]">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-300">Judul Section Alur</label>
                            <input type="text" name="how_it_works_title" value="{{ old('how_it_works_title', $settings['how_it_works_title']) }}" placeholder="Alur Mudah Mengikuti TALENTA" class="block w-full px-3 py-2 rounded-xl bg-[#161F30] border border-white/[0.1] text-white text-xs font-semibold focus:border-[#7A5AF8] outline-none">
                        </div>
                    </div>

                    <div class="space-y-1 bg-[#0C111D]/80 p-4 rounded-2xl border border-white/[0.08]">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-300">Deskripsi Singkat Alur</label>
                        <input type="text" name="how_it_works_subtitle" value="{{ old('how_it_works_subtitle', $settings['how_it_works_subtitle']) }}" placeholder="4 langkah terstruktur dari pembuatan akun resmi..." class="block w-full px-3 py-2 rounded-xl bg-[#161F30] border border-white/[0.1] text-white text-xs font-medium focus:border-[#7A5AF8] outline-none">
                    </div>

                    <!-- 4 Steps Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 pt-2">
                        <!-- Step 1 -->
                        <div class="p-4 rounded-2xl bg-[#0C111D]/80 border border-white/[0.08] space-y-2">
                            <span class="text-[10px] font-black uppercase text-emerald-400 block">Langkah 1</span>
                            <input type="text" name="step_1_title" value="{{ old('step_1_title', $settings['step_1_title']) }}" placeholder="Judul Langkah 1" class="block w-full px-3 py-2 rounded-xl bg-[#161F30] border border-white/[0.1] text-white text-xs font-bold outline-none">
                            <textarea name="step_1_desc" rows="2" placeholder="Penjelasan Langkah 1" class="block w-full px-3 py-2 rounded-xl bg-[#161F30] border border-white/[0.1] text-slate-300 text-[11px] outline-none leading-relaxed">{{ old('step_1_desc', $settings['step_1_desc']) }}</textarea>
                        </div>

                        <!-- Step 2 -->
                        <div class="p-4 rounded-2xl bg-[#0C111D]/80 border border-white/[0.08] space-y-2">
                            <span class="text-[10px] font-black uppercase text-[#84D0FF] block">Langkah 2</span>
                            <input type="text" name="step_2_title" value="{{ old('step_2_title', $settings['step_2_title']) }}" placeholder="Judul Langkah 2" class="block w-full px-3 py-2 rounded-xl bg-[#161F30] border border-white/[0.1] text-white text-xs font-bold outline-none">
                            <textarea name="step_2_desc" rows="2" placeholder="Penjelasan Langkah 2" class="block w-full px-3 py-2 rounded-xl bg-[#161F30] border border-white/[0.1] text-slate-300 text-[11px] outline-none leading-relaxed">{{ old('step_2_desc', $settings['step_2_desc']) }}</textarea>
                        </div>

                        <!-- Step 3 -->
                        <div class="p-4 rounded-2xl bg-[#0C111D]/80 border border-white/[0.08] space-y-2">
                            <span class="text-[10px] font-black uppercase text-amber-400 block">Langkah 3</span>
                            <input type="text" name="step_3_title" value="{{ old('step_3_title', $settings['step_3_title']) }}" placeholder="Judul Langkah 3" class="block w-full px-3 py-2 rounded-xl bg-[#161F30] border border-white/[0.1] text-white text-xs font-bold outline-none">
                            <textarea name="step_3_desc" rows="2" placeholder="Penjelasan Langkah 3" class="block w-full px-3 py-2 rounded-xl bg-[#161F30] border border-white/[0.1] text-slate-300 text-[11px] outline-none leading-relaxed">{{ old('step_3_desc', $settings['step_3_desc']) }}</textarea>
                        </div>

                        <!-- Step 4 -->
                        <div class="p-4 rounded-2xl bg-[#0C111D]/80 border border-white/[0.08] space-y-2">
                            <span class="text-[10px] font-black uppercase text-teal-400 block">Langkah 4</span>
                            <input type="text" name="step_4_title" value="{{ old('step_4_title', $settings['step_4_title']) }}" placeholder="Judul Langkah 4" class="block w-full px-3 py-2 rounded-xl bg-[#161F30] border border-white/[0.1] text-white text-xs font-bold outline-none">
                            <textarea name="step_4_desc" rows="2" placeholder="Penjelasan Langkah 4" class="block w-full px-3 py-2 rounded-xl bg-[#161F30] border border-white/[0.1] text-slate-300 text-[11px] outline-none leading-relaxed">{{ old('step_4_desc', $settings['step_4_desc']) }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- SECTION 3: KATALOG & TIMELINE -->
                <div class="space-y-4 pt-2">
                    <div class="flex items-center gap-2 border-b border-white/[0.08] pb-2">
                        <i data-lucide="layers" class="w-4 h-4 text-[#A594FD]"></i>
                        <h4 class="text-xs font-black text-white uppercase tracking-wider">3. Heading Katalog Lomba & Jadwal Rangkaian Acara</h4>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="p-4 rounded-2xl bg-[#0C111D]/80 border border-white/[0.08] space-y-3">
                            <span class="text-xs font-bold text-white block">Katalog Perlombaan</span>
                            <div class="space-y-1">
                                <label class="block text-[11px] font-semibold text-slate-400">Tagline / Sub-badge</label>
                                <input type="text" name="catalog_tagline" value="{{ old('catalog_tagline', $settings['catalog_tagline']) }}" class="block w-full px-3 py-2 rounded-xl bg-[#161F30] border border-white/[0.1] text-white text-xs font-semibold outline-none">
                            </div>
                            <div class="space-y-1">
                                <label class="block text-[11px] font-semibold text-slate-400">Judul Utama Katalog</label>
                                <input type="text" name="catalog_title" value="{{ old('catalog_title', $settings['catalog_title']) }}" class="block w-full px-3 py-2 rounded-xl bg-[#161F30] border border-white/[0.1] text-white text-xs font-semibold outline-none">
                            </div>
                        </div>

                        <div class="p-4 rounded-2xl bg-[#0C111D]/80 border border-white/[0.08] space-y-3">
                            <span class="text-xs font-bold text-white block">Agenda & Timeline</span>
                            <div class="space-y-1">
                                <label class="block text-[11px] font-semibold text-slate-400">Tagline Jadwal</label>
                                <input type="text" name="timeline_tagline" value="{{ old('timeline_tagline', $settings['timeline_tagline']) }}" class="block w-full px-3 py-2 rounded-xl bg-[#161F30] border border-white/[0.1] text-white text-xs font-semibold outline-none">
                            </div>
                            <div class="space-y-1">
                                <label class="block text-[11px] font-semibold text-slate-400">Judul Section Timeline</label>
                                <input type="text" name="timeline_title" value="{{ old('timeline_title', $settings['timeline_title']) }}" class="block w-full px-3 py-2 rounded-xl bg-[#161F30] border border-white/[0.1] text-white text-xs font-semibold outline-none">
                            </div>
                            <div class="space-y-1">
                                <label class="block text-[11px] font-semibold text-slate-400">Deskripsi Singkat Timeline</label>
                                <input type="text" name="timeline_subtitle" value="{{ old('timeline_subtitle', $settings['timeline_subtitle']) }}" class="block w-full px-3 py-2 rounded-xl bg-[#161F30] border border-white/[0.1] text-white text-xs outline-none">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECTION 4: PAMFLET / BROSUR EVENT (UPLOAD GAMBAR & LINK CANVA) -->
                <div class="space-y-4 pt-2">
                    <div class="flex items-center gap-2 border-b border-white/[0.08] pb-2">
                        <i data-lucide="image" class="w-4 h-4 text-pink-400"></i>
                        <h4 class="text-xs font-black text-white uppercase tracking-wider">4. Pamflet / Brosur Event (Auto-Slide Carousel & Zoom)</h4>
                    </div>

                    <div class="space-y-5 bg-[#0C111D]/80 p-5 rounded-2xl border border-white/[0.08]">
                        
                        <!-- 1. Upload File Gambar Pamflet (Direct Image Files) -->
                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-200 flex items-center gap-1.5">
                                    <i data-lucide="upload" class="w-3.5 h-3.5 text-pink-400"></i>
                                    <span>Upload Gambar Pamflet / Brosur (Bisa Banyak File Sekaligus)</span>
                                </label>
                                <p class="text-[11px] text-slate-400 mt-0.5">
                                    Unggah satu atau banyak file gambar pamflet (.PNG, .JPG, .WEBP). Di landing page, pamflet akan otomatis tampil besar, tajam, dan berputar otomatis (Auto-Slide Carousel) dengan tombol zoom layar penuh.
                                </p>
                            </div>

                            <div class="p-5 rounded-2xl bg-[#161F30] border-2 border-dashed border-white/[0.12] flex flex-col items-center justify-center text-center hover:border-pink-500/50 transition">
                                <i data-lucide="image-plus" class="w-8 h-8 text-pink-400 mb-2"></i>
                                <input type="file" name="pamphlet_images[]" multiple accept="image/png,image/jpeg,image/jpg,image/webp" class="text-xs text-slate-400 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-pink-500/20 file:text-pink-300 hover:file:bg-pink-500/30 cursor-pointer">
                                <p class="text-[10px] text-slate-500 mt-2">Format: JPG, PNG, WEBP (Bisa pilih 1 sampai 10+ file gambar sekaligus)</p>
                            </div>

                            <!-- Existing Uploaded Pamphlet Images List -->
                            @if(!empty($settings['pamphlet_images']) && count($settings['pamphlet_images']) > 0)
                                <div class="space-y-2 pt-2 border-t border-white/[0.06]">
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-300">
                                        Gambar Pamflet yang Terpasang Saat Ini ({{ count($settings['pamphlet_images']) }} Pamflet):
                                    </label>
                                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3">
                                        @foreach($settings['pamphlet_images'] as $index => $img)
                                            <div class="p-2.5 rounded-2xl bg-[#0C111D] border border-white/[0.08] flex flex-col items-center justify-between gap-2.5 text-center relative group">
                                                <div class="w-full h-36 rounded-xl overflow-hidden bg-slate-900 border border-white/[0.06] flex items-center justify-center">
                                                    <img src="{{ asset('storage/' . $img) }}" alt="Pamflet {{ $index + 1 }}" class="w-full h-full object-cover">
                                                </div>
                                                <label class="inline-flex items-center gap-1.5 text-[11px] font-bold text-rose-400 cursor-pointer hover:text-rose-300">
                                                    <input type="checkbox" name="delete_pamphlet_images[]" value="{{ $img }}" class="rounded border-rose-400/40 text-rose-600 focus:ring-rose-500">
                                                    <span>Hapus</span>
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- 2. Option B: Link Sematan Canva -->
                        <div class="space-y-2.5 pt-3 border-t border-white/[0.08]">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-200 flex items-center gap-1.5">
                                    <i data-lucide="link" class="w-3.5 h-3.5 text-pink-400"></i>
                                    <span>Link Embed / Kode Canva (Opsional)</span>
                                </label>
                                <p class="text-[11px] text-slate-400 mt-0.5">
                                    Atau tempelkan link sematan Canva (misal: <code>https://www.canva.com/design/.../view?embed</code> atau kode <code>&lt;iframe&gt;</code>). Bisa masukkan lebih dari satu link (pisahkan dengan baris baru).
                                </p>
                            </div>

                            <textarea name="pamphlet_embed_url" rows="3" placeholder="Contoh: https://www.canva.com/design/DAGxxxx/view?embed" class="block w-full px-3.5 py-2.5 rounded-xl bg-[#161F30] border border-white/[0.1] text-white text-xs font-mono placeholder-slate-500 focus:border-pink-500 focus:ring-1 focus:ring-pink-500/30 outline-none leading-relaxed">{{ old('pamphlet_embed_url', $settings['pamphlet_embed_url'] ?? '') }}</textarea>
                        </div>

                    </div>
                </div>

                <!-- SECTION 5: SPONSOR & MITRA KERJASAMA (SUPPORTED BY) -->
                <div class="space-y-4 pt-2">
                    <div class="flex items-center gap-2 border-b border-white/[0.08] pb-2">
                        <i data-lucide="handshake" class="w-4 h-4 text-emerald-400"></i>
                        <h4 class="text-xs font-black text-white uppercase tracking-wider">5. Sponsor & Mitra Kerjasama (Supported by)</h4>
                    </div>

                    <div class="space-y-4">
                        <div class="space-y-1.5 bg-[#0C111D]/80 p-4 rounded-2xl border border-white/[0.08]">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-300">Teks Judul / Tagline Sponsor</label>
                            <input type="text" name="sponsor_title" value="{{ old('sponsor_title', $settings['sponsor_title']) }}" placeholder="Contoh: Supported by :" class="block w-full px-3.5 py-2.5 rounded-xl bg-[#161F30] border border-white/[0.1] text-white text-xs font-semibold focus:border-[#7A5AF8] outline-none">
                        </div>

                        <!-- Upload New Sponsor Logos -->
                        <div class="space-y-2 bg-[#0C111D]/80 p-4 rounded-2xl border border-white/[0.08]">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-300">
                                Upload Logo Sponsor / Mitra (Bisa Pilih Banyak File Sekaligus)
                            </label>
                            <div class="p-6 rounded-2xl bg-[#161F30] border-2 border-dashed border-white/[0.12] flex flex-col items-center justify-center text-center hover:border-[#7A5AF8]/50 transition">
                                <i data-lucide="upload-cloud" class="w-8 h-8 text-[#A594FD] mb-2"></i>
                                <input type="file" name="sponsor_logos[]" multiple accept="image/png,image/jpeg,image/jpg,image/svg+xml,image/webp" class="text-xs text-slate-400 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[#7A5AF8]/20 file:text-[#A594FD] hover:file:bg-[#7A5AF8]/30 cursor-pointer">
                                <p class="text-[11px] text-slate-500 mt-2">Disarankan format PNG transparan atau SVG (Maks. 3MB per file)</p>
                            </div>
                        </div>

                        <!-- Existing Sponsor Logos List -->
                        @if(!empty($settings['sponsor_logos']) && count($settings['sponsor_logos']) > 0)
                            <div class="space-y-2 pt-2">
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-300">Logo Sponsor yang Terpasang Saat Ini:</label>
                                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                                    @foreach($settings['sponsor_logos'] as $index => $logo)
                                        <div class="p-3.5 rounded-2xl bg-[#0C111D] border border-white/[0.08] flex flex-col items-center justify-between gap-3 text-center relative group">
                                            <div class="h-14 flex items-center justify-center">
                                                <img src="{{ asset('storage/' . $logo) }}" alt="Sponsor {{ $index + 1 }}" class="max-h-12 w-auto object-contain">
                                            </div>
                                            <label class="inline-flex items-center gap-1.5 text-[11px] font-bold text-rose-400 cursor-pointer hover:text-rose-300">
                                                <input type="checkbox" name="delete_sponsor_logos[]" value="{{ $logo }}" class="rounded border-rose-400/40 text-rose-600 focus:ring-rose-500">
                                                <span>Centang untuk Hapus</span>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- SECTION 5: FOOTER -->
                <div class="space-y-4 pt-2">
                    <div class="flex items-center gap-2 border-b border-white/[0.08] pb-2">
                        <i data-lucide="info" class="w-4 h-4 text-[#84D0FF]"></i>
                        <h4 class="text-xs font-black text-white uppercase tracking-wider">5. Deskripsi Footer (Tentang Aplikasi)</h4>
                    </div>

                    <div class="space-y-1.5 bg-[#0C111D]/80 p-4 rounded-2xl border border-white/[0.08]">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-300">Deskripsi Singkat Footer</label>
                        <textarea name="footer_about" rows="3" class="block w-full px-3.5 py-2.5 rounded-xl bg-[#161F30] border border-white/[0.1] text-white text-xs font-medium outline-none leading-relaxed">{{ old('footer_about', $settings['footer_about']) }}</textarea>
                    </div>
                </div>

                <div class="pt-4 border-t border-white/[0.08] flex items-center justify-end">
                    <button type="submit" class="gradient-btn px-8 py-3 rounded-2xl text-white font-bold text-xs shadow-lg shadow-[#7A5AF8]/25 transition cursor-pointer flex items-center gap-2">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        <span>Simpan Seluruh Konten Landing Page</span>
                    </button>
                </div>

            </form>

        </div>
    </div>

    <!-- TAB 5: STATUS SERVER & ENGINE -->
    <div x-show="activeTab === 'system'" x-transition class="space-y-6">
        <div class="ai-card rounded-3xl border border-white/[0.08] shadow-2xl p-6 sm:p-8 space-y-6">
            <div class="flex items-center gap-3 border-b border-white/[0.08] pb-4">
                <div class="w-10 h-10 rounded-2xl bg-white/[0.06] text-slate-300 border border-white/[0.08] flex items-center justify-center font-bold">
                    <i data-lucide="server" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-base sm:text-lg font-black text-white font-display">Status Lingkungan Server</h3>
                    <p class="text-xs text-slate-400">Rincian modul hosting & framework</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div class="p-4 rounded-2xl bg-[#0C111D]/80 border border-white/[0.08] flex items-center justify-between">
                    <span class="text-slate-400 font-medium">Framework</span>
                    <span class="font-bold text-white font-mono">{{ $systemInfo['framework'] ?? 'Laravel 11' }}</span>
                </div>
                <div class="p-4 rounded-2xl bg-[#0C111D]/80 border border-white/[0.08] flex items-center justify-between">
                    <span class="text-slate-400 font-medium">PHP Version</span>
                    <span class="font-bold text-emerald-400 font-mono">{{ $systemInfo['php_version'] ?? PHP_VERSION }}</span>
                </div>
                <div class="p-4 rounded-2xl bg-[#0C111D]/80 border border-white/[0.08] flex items-center justify-between">
                    <span class="text-slate-400 font-medium">Database Engine</span>
                    <span class="font-bold text-white">{{ $systemInfo['database'] ?? 'MySQL' }}</span>
                </div>
                <div class="p-4 rounded-2xl bg-[#0C111D]/80 border border-white/[0.08] flex items-center justify-between">
                    <span class="text-slate-400 font-medium">Timezone</span>
                    <span class="font-bold text-white">{{ $systemInfo['timezone'] ?? 'Asia/Jakarta' }}</span>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
