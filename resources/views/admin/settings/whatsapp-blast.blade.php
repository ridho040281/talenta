@extends('layouts.admin')

@section('title', 'WhatsApp Blast & Gateway')
@section('page_title', 'WhatsApp Blast & Gateway Wablas')

@section('content')
<div class="space-y-6" x-data="whatsappBlastApp()">

    <!-- MAIN TAB NAVIGATION BAR -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-2 bg-[#0C111D]/90 rounded-3xl border border-white/[0.08] shadow-2xl backdrop-blur-xl">
        <div class="flex flex-wrap items-center gap-2">
            <!-- Tab 1: Broadcast Pesan -->
            <button type="button" @click="setMainTab('broadcast')" 
                :class="mainTab === 'broadcast' ? 'bg-gradient-to-r from-[#7A5AF8] to-[#4E6EFF] text-white shadow-lg shadow-[#7A5AF8]/30 font-black' : 'text-slate-400 hover:text-white font-bold hover:bg-white/[0.04]'"
                class="px-4 sm:px-5 py-2.5 sm:py-3 rounded-2xl text-xs sm:text-sm transition-all duration-200 flex items-center gap-2 cursor-pointer">
                <i data-lucide="megaphone" class="w-4 h-4"></i>
                <span>Kirim Broadcast</span>
            </button>

            <!-- Tab 2: Template & Auto-Trigger -->
            <button type="button" @click="setMainTab('templates')" 
                :class="mainTab === 'templates' ? 'bg-gradient-to-r from-emerald-500 to-teal-600 text-slate-950 shadow-lg shadow-emerald-500/30 font-black' : 'text-slate-400 hover:text-white font-bold hover:bg-white/[0.04]'"
                class="px-4 sm:px-5 py-2.5 sm:py-3 rounded-2xl text-xs sm:text-sm transition-all duration-200 flex items-center gap-2 cursor-pointer">
                <i data-lucide="zap" class="w-4 h-4"></i>
                <span>Template & Auto-Trigger</span>
                <span class="px-1.5 py-0.5 rounded-full text-[10px] font-mono" :class="mainTab === 'templates' ? 'bg-black/25 text-slate-950 font-bold' : 'bg-white/10 text-emerald-400'">{{ $whatsappTemplates->count() }}</span>
            </button>

            <!-- Tab 3: Pengaturan API Gateway -->
            <button type="button" @click="setMainTab('api')" 
                :class="mainTab === 'api' ? 'bg-gradient-to-r from-amber-500 to-orange-500 text-slate-950 shadow-lg shadow-amber-500/30 font-black' : 'text-slate-400 hover:text-white font-bold hover:bg-white/[0.04]'"
                class="px-4 sm:px-5 py-2.5 sm:py-3 rounded-2xl text-xs sm:text-sm transition-all duration-200 flex items-center gap-2 cursor-pointer">
                <i data-lucide="key-round" class="w-4 h-4"></i>
                <span>Pengaturan Gateway</span>
            </button>
        </div>

        <!-- Quick Gateway Status Pill in Top Nav -->
        <div class="flex items-center gap-2.5 px-4 py-2 rounded-2xl bg-white/[0.03] border border-white/[0.06] text-xs self-start sm:self-auto cursor-pointer" @click="setMainTab('api')" title="Buka pengaturan API Gateway Wablas">
            <span class="text-slate-400 text-[11px] font-medium">Gateway:</span>
            <div x-show="statusState === 'connected'" class="flex items-center gap-2 text-emerald-400 font-bold font-mono text-xs">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                </span>
                <span class="truncate max-w-[130px]" x-text="deviceSender || 'Terhubung'"></span>
                <span class="text-[10px] px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 font-mono" x-text="remainingQuota + ' Kuota'"></span>
            </div>
            <div x-show="statusState !== 'connected'" class="flex items-center gap-1.5 text-rose-400 font-bold font-mono text-xs">
                <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                <span>Terputus / Belum Diatur</span>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 1: KIRIM PESAN BROADCAST (PENGATURAN PESAN & DIREKTORI KONTAK) -->
    <!-- ========================================================================= -->
    <div x-show="mainTab === 'broadcast'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6">
        
        <!-- Quick Audience Stats -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="ai-card p-5 rounded-3xl border border-white/[0.08] shadow-lg">
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Semua Pendaftar</div>
                <div class="text-2xl font-black text-white mt-1">{{ $stats['total_recipients'] }} Kontak</div>
            </div>
            <div class="ai-card p-5 rounded-3xl border border-emerald-500/30 bg-emerald-500/5 shadow-lg">
                <div class="text-[11px] font-bold text-emerald-400 uppercase tracking-wider">Lolos Verifikasi</div>
                <div class="text-2xl font-black text-emerald-400 mt-1">{{ $stats['verified_recipients'] }} Kontak</div>
            </div>
            <div class="ai-card p-5 rounded-3xl border border-amber-500/30 bg-amber-500/5 shadow-lg">
                <div class="text-[11px] font-bold text-amber-300 uppercase tracking-wider">Menunggu Verifikasi</div>
                <div class="text-2xl font-black text-amber-300 mt-1">{{ $stats['pending_recipients'] }} Kontak</div>
            </div>
            <div class="ai-card p-5 rounded-3xl border border-[#4E6EFF]/30 bg-[#4E6EFF]/5 shadow-lg">
                <div class="text-[11px] font-bold text-[#84D0FF] uppercase tracking-wider">Total Broadcast Dikirim</div>
                <div class="text-2xl font-black text-[#84D0FF] mt-1">{{ $stats['total_broadcasts'] }} Kali</div>
            </div>
        </div>

        <!-- Broadcast Composer Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <!-- Left: Form Composer -->
            <div class="lg:col-span-7 ai-card rounded-3xl p-6 sm:p-8 border border-white/[0.08] shadow-2xl space-y-6 text-white">
                <div class="flex items-center justify-between border-b border-white/[0.08] pb-4">
                    <div>
                        <h3 class="text-lg font-black text-white font-display">Buat Pesan WhatsApp Blast</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Tentukan target penerima dan susun isi pesan broadcast</p>
                    </div>

                    <!-- Quick Template Dropdown Button -->
                    <div class="relative" x-data="{ openTmpl: false }">
                        <button type="button" @click="openTmpl = !openTmpl" class="px-3 py-1.5 rounded-xl bg-[#7A5AF8]/20 hover:bg-[#7A5AF8]/30 text-[#A594FD] border border-[#7A5AF8]/30 text-xs font-bold transition flex items-center gap-1.5 cursor-pointer shadow-sm">
                            <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
                            <span>Pilih Template</span>
                            <i data-lucide="chevron-down" class="w-3 h-3"></i>
                        </button>

                        <div x-show="openTmpl" @click.outside="openTmpl = false" class="absolute right-0 mt-2 w-72 rounded-2xl bg-[#0C111D] border border-white/[0.15] shadow-2xl z-30 p-2 space-y-1" style="display: none;">
                            <div class="px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-slate-400 border-b border-white/[0.06]">
                                Pilih Template Siap Pakai
                            </div>
                            <template x-for="t in templatesList" :key="t.id">
                                <button type="button" @click="useTemplate(t); openTmpl = false" class="w-full text-left p-2 rounded-xl hover:bg-white/[0.06] transition flex flex-col gap-0.5 cursor-pointer group">
                                    <span class="text-xs font-bold text-white group-hover:text-[#84D0FF]" x-text="t.name"></span>
                                    <span class="text-[10px] text-slate-400 truncate" x-text="t.description || t.message"></span>
                                </button>
                            </template>
                            <div class="pt-1 border-t border-white/[0.06]">
                                <button type="button" @click="setMainTab('templates'); openTmpl = false" class="w-full text-center py-1.5 text-[11px] font-bold text-emerald-400 hover:underline">
                                    ⚙️ Kelola Semua Template
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <form action="{{ route('admin.settings.whatsapp.blast.send') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                    @csrf

                    <!-- Target Audience Selection Grid -->
                    <div class="space-y-2.5">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-300">Pilih Target Penerima Pesan</label>
                            <span class="text-[11px] font-mono text-[#84D0FF] font-semibold" x-text="'Target: ' + targetAudience.toUpperCase()"></span>
                        </div>

                        <input type="hidden" name="target_audience" :value="targetAudience">

                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                            
                            <!-- 1. Semua Pendaftar -->
                            <div @click="targetAudience = 'all'"
                                :class="targetAudience === 'all' ? 'bg-gradient-to-br from-[#7A5AF8]/25 to-[#4E6EFF]/20 border-[#7A5AF8] ring-1 ring-[#7A5AF8] shadow-lg shadow-[#7A5AF8]/20' : 'bg-[#0C111D] border-white/[0.08] hover:border-white/[0.2] hover:bg-white/[0.02]'"
                                class="p-3 rounded-2xl border transition-all duration-200 cursor-pointer flex flex-col justify-between space-y-2 group relative">
                                <div class="flex items-center justify-between">
                                    <span class="w-7 h-7 rounded-xl flex items-center justify-center text-xs" :class="targetAudience === 'all' ? 'bg-[#7A5AF8] text-white' : 'bg-white/[0.06] text-slate-400 group-hover:text-white'">
                                        <i data-lucide="megaphone" class="w-3.5 h-3.5"></i>
                                    </span>
                                    <span class="text-[10px] font-mono font-bold px-1.5 py-0.5 rounded-md" :class="targetAudience === 'all' ? 'bg-[#7A5AF8]/30 text-white' : 'bg-white/[0.04] text-slate-400'">
                                        {{ $stats['total_recipients'] }}
                                    </span>
                                </div>
                                <div>
                                    <div class="text-xs font-bold leading-snug" :class="targetAudience === 'all' ? 'text-white font-black' : 'text-slate-200'">Semua Pendaftar</div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">Seluruh kontak</div>
                                </div>
                            </div>

                            <!-- 2. Lolos Verifikasi -->
                            <div @click="targetAudience = 'verified'"
                                :class="targetAudience === 'verified' ? 'bg-gradient-to-br from-emerald-500/25 to-teal-500/15 border-emerald-500 ring-1 ring-emerald-500 shadow-lg shadow-emerald-500/20' : 'bg-[#0C111D] border-white/[0.08] hover:border-white/[0.2] hover:bg-white/[0.02]'"
                                class="p-3 rounded-2xl border transition-all duration-200 cursor-pointer flex flex-col justify-between space-y-2 group relative">
                                <div class="flex items-center justify-between">
                                    <span class="w-7 h-7 rounded-xl flex items-center justify-center text-xs" :class="targetAudience === 'verified' ? 'bg-emerald-500 text-white' : 'bg-emerald-500/10 text-emerald-400 group-hover:text-emerald-300'">
                                        <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                                    </span>
                                    <span class="text-[10px] font-mono font-bold px-1.5 py-0.5 rounded-md" :class="targetAudience === 'verified' ? 'bg-emerald-500/30 text-emerald-200' : 'bg-white/[0.04] text-emerald-400'">
                                        {{ $stats['verified_recipients'] }}
                                    </span>
                                </div>
                                <div>
                                    <div class="text-xs font-bold leading-snug" :class="targetAudience === 'verified' ? 'text-white font-black' : 'text-slate-200'">Lolos Verifikasi</div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">Pendaftar sah</div>
                                </div>
                            </div>

                            <!-- 3. Belum Terverifikasi -->
                            <div @click="targetAudience = 'pending'"
                                :class="targetAudience === 'pending' ? 'bg-gradient-to-br from-amber-500/25 to-orange-500/15 border-amber-500 ring-1 ring-amber-500 shadow-lg shadow-amber-500/20' : 'bg-[#0C111D] border-white/[0.08] hover:border-white/[0.2] hover:bg-white/[0.02]'"
                                class="p-3 rounded-2xl border transition-all duration-200 cursor-pointer flex flex-col justify-between space-y-2 group relative">
                                <div class="flex items-center justify-between">
                                    <span class="w-7 h-7 rounded-xl flex items-center justify-center text-xs" :class="targetAudience === 'pending' ? 'bg-amber-500 text-slate-950 font-bold' : 'bg-amber-500/10 text-amber-400 group-hover:text-amber-300'">
                                        <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                                    </span>
                                    <span class="text-[10px] font-mono font-bold px-1.5 py-0.5 rounded-md" :class="targetAudience === 'pending' ? 'bg-amber-500/30 text-amber-200' : 'bg-white/[0.04] text-amber-400'">
                                        {{ $stats['pending_recipients'] }}
                                    </span>
                                </div>
                                <div>
                                    <div class="text-xs font-bold leading-snug" :class="targetAudience === 'pending' ? 'text-white font-black' : 'text-slate-200'">Belum Verifikasi</div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">Menunggu / pending</div>
                                </div>
                            </div>

                            <!-- 4. Per Cabang Lomba -->
                            <div @click="targetAudience = 'competition'"
                                :class="targetAudience === 'competition' ? 'bg-gradient-to-br from-[#4E6EFF]/25 to-cyan-500/15 border-[#4E6EFF] ring-1 ring-[#4E6EFF] shadow-lg shadow-[#4E6EFF]/20' : 'bg-[#0C111D] border-white/[0.08] hover:border-white/[0.2] hover:bg-white/[0.02]'"
                                class="p-3 rounded-2xl border transition-all duration-200 cursor-pointer flex flex-col justify-between space-y-2 group relative">
                                <div class="flex items-center justify-between">
                                    <span class="w-7 h-7 rounded-xl flex items-center justify-center text-xs" :class="targetAudience === 'competition' ? 'bg-[#4E6EFF] text-white' : 'bg-[#4E6EFF]/10 text-[#84D0FF] group-hover:text-cyan-300'">
                                        <i data-lucide="target" class="w-3.5 h-3.5"></i>
                                    </span>
                                    <span class="text-[9px] font-bold px-1.5 py-0.5 rounded-md" :class="targetAudience === 'competition' ? 'bg-[#4E6EFF]/30 text-white' : 'bg-white/[0.04] text-slate-400'">
                                        Pilih
                                    </span>
                                </div>
                                <div>
                                    <div class="text-xs font-bold leading-snug" :class="targetAudience === 'competition' ? 'text-white font-black' : 'text-slate-200'">Per Cabang Lomba</div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">Pilih 1 lomba</div>
                                </div>
                            </div>

                            <!-- 5. Panitia & PIC -->
                            <div @click="targetAudience = 'panitia'"
                                :class="targetAudience === 'panitia' ? 'bg-gradient-to-br from-indigo-500/25 to-purple-500/15 border-indigo-500 ring-1 ring-indigo-500 shadow-lg shadow-indigo-500/20' : 'bg-[#0C111D] border-white/[0.08] hover:border-white/[0.2] hover:bg-white/[0.02]'"
                                class="p-3 rounded-2xl border transition-all duration-200 cursor-pointer flex flex-col justify-between space-y-2 group relative">
                                <div class="flex items-center justify-between">
                                    <span class="w-7 h-7 rounded-xl flex items-center justify-center text-xs" :class="targetAudience === 'panitia' ? 'bg-indigo-500 text-white' : 'bg-indigo-500/10 text-indigo-400 group-hover:text-indigo-300'">
                                        <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                                    </span>
                                    <span class="text-[10px] font-mono font-bold px-1.5 py-0.5 rounded-md" :class="targetAudience === 'panitia' ? 'bg-indigo-500/30 text-indigo-200' : 'bg-white/[0.04] text-indigo-400'">
                                        {{ $committeeContacts->count() }}
                                    </span>
                                </div>
                                <div>
                                    <div class="text-xs font-bold leading-snug" :class="targetAudience === 'panitia' ? 'text-white font-black' : 'text-slate-200'">Panitia & Juri</div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">Internal panitia</div>
                                </div>
                            </div>

                            <!-- 6. Publikasi & Humas -->
                            <div @click="targetAudience = 'publikasi'"
                                :class="targetAudience === 'publikasi' ? 'bg-gradient-to-br from-pink-500/25 to-rose-500/15 border-pink-500 ring-1 ring-pink-500 shadow-lg shadow-pink-500/20' : 'bg-[#0C111D] border-white/[0.08] hover:border-white/[0.2] hover:bg-white/[0.02]'"
                                class="p-3 rounded-2xl border transition-all duration-200 cursor-pointer flex flex-col justify-between space-y-2 group relative">
                                <div class="flex items-center justify-between">
                                    <span class="w-7 h-7 rounded-xl flex items-center justify-center text-xs" :class="targetAudience === 'publikasi' ? 'bg-pink-500 text-white' : 'bg-pink-500/10 text-pink-400 group-hover:text-pink-300'">
                                        <i data-lucide="share-2" class="w-3.5 h-3.5"></i>
                                    </span>
                                    <span class="text-[10px] font-mono font-bold px-1.5 py-0.5 rounded-md" :class="targetAudience === 'publikasi' ? 'bg-pink-500/30 text-pink-200' : 'bg-white/[0.04] text-pink-400'">
                                        {{ $publicationContacts->count() }}
                                    </span>
                                </div>
                                <div>
                                    <div class="text-xs font-bold leading-snug" :class="targetAudience === 'publikasi' ? 'text-white font-black' : 'text-slate-200'">Publikasi & Humas</div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">Delegasi sekolah</div>
                                </div>
                            </div>

                            <!-- 7. Input Manual -->
                            <div @click="targetAudience = 'manual'"
                                :class="targetAudience === 'manual' ? 'bg-gradient-to-br from-amber-400/25 to-yellow-500/15 border-amber-400 ring-1 ring-amber-400 shadow-lg shadow-amber-400/20' : 'bg-[#0C111D] border-white/[0.08] hover:border-white/[0.2] hover:bg-white/[0.02]'"
                                class="p-3 rounded-2xl border transition-all duration-200 cursor-pointer flex flex-col justify-between space-y-2 group relative">
                                <div class="flex items-center justify-between">
                                    <span class="w-7 h-7 rounded-xl flex items-center justify-center text-xs" :class="targetAudience === 'manual' ? 'bg-amber-400 text-slate-950 font-bold' : 'bg-amber-400/10 text-amber-300 group-hover:text-amber-200'">
                                        <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                    </span>
                                    <span class="text-[9px] font-bold px-1.5 py-0.5 rounded-md" :class="targetAudience === 'manual' ? 'bg-amber-400/30 text-amber-200' : 'bg-white/[0.04] text-slate-400'">
                                        Paste
                                    </span>
                                </div>
                                <div>
                                    <div class="text-xs font-bold leading-snug" :class="targetAudience === 'manual' ? 'text-white font-black' : 'text-slate-200'">Input Manual</div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">Ketik / copy-paste</div>
                                </div>
                            </div>

                            <!-- 8. Import Excel -->
                            <div @click="targetAudience = 'excel'"
                                :class="targetAudience === 'excel' ? 'bg-gradient-to-br from-emerald-400/25 to-green-500/15 border-emerald-400 ring-1 ring-emerald-400 shadow-lg shadow-emerald-400/20' : 'bg-[#0C111D] border-white/[0.08] hover:border-white/[0.2] hover:bg-white/[0.02]'"
                                class="p-3 rounded-2xl border transition-all duration-200 cursor-pointer flex flex-col justify-between space-y-2 group relative">
                                <div class="flex items-center justify-between">
                                    <span class="w-7 h-7 rounded-xl flex items-center justify-center text-xs" :class="targetAudience === 'excel' ? 'bg-emerald-400 text-slate-950 font-bold' : 'bg-emerald-400/10 text-emerald-300 group-hover:text-emerald-200'">
                                        <i data-lucide="file-spreadsheet" class="w-3.5 h-3.5"></i>
                                    </span>
                                    <span class="text-[9px] font-bold px-1.5 py-0.5 rounded-md" :class="targetAudience === 'excel' ? 'bg-emerald-400/30 text-emerald-200' : 'bg-white/[0.04] text-slate-400'">
                                        .xlsx
                                    </span>
                                </div>
                                <div>
                                    <div class="text-xs font-bold leading-snug" :class="targetAudience === 'excel' ? 'text-white font-black' : 'text-slate-200'">Import Excel</div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">Upload spreadsheet</div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- 1. Specific Competition Selector -->
                    <div x-show="targetAudience === 'competition'" x-transition>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-2">Pilih Cabang Lomba</label>
                        <select name="competition_id" class="block w-full px-4 py-3 rounded-xl bg-[#0C111D] border border-white/[0.12] text-white text-sm outline-none focus:border-[#7A5AF8] focus:ring-2 focus:ring-[#7A5AF8]/30">
                            <option value="">-- Pilih Cabang Lomba --</option>
                            @foreach($competitions as $c)
                                <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->category->name }})</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- 2. Manual Number Input Container -->
                    <div x-show="targetAudience === 'manual'" x-transition class="p-4 rounded-2xl bg-[#0C111D] border border-emerald-500/30 space-y-3 shadow-inner">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-bold uppercase tracking-wider text-emerald-300 flex items-center gap-1.5">
                                <i data-lucide="edit-3" class="w-3.5 h-3.5 text-emerald-400"></i>
                                <span>Daftar Nomor WhatsApp (Input Manual)</span>
                            </label>
                            <div class="flex items-center gap-2">
                                <button type="button" x-show="manualNumbers.trim().length > 0" @click="manualNumbers = ''" class="text-[10px] font-bold text-rose-400 hover:text-rose-300 transition flex items-center gap-1 px-2 py-0.5 rounded-md bg-rose-500/10 border border-rose-500/20 cursor-pointer">
                                    <i data-lucide="trash-2" class="w-3 h-3"></i>
                                    <span>Bersihkan Kotak</span>
                                </button>
                                <span class="text-[11px] font-mono px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 font-bold border border-emerald-500/30" x-text="manualCountText"></span>
                            </div>
                        </div>
                        <textarea name="manual_numbers" x-model="manualNumbers" rows="4" placeholder="Contoh format per baris:
081234567890
085712345678, Ahmad Pratama, SDN 1 Wonodadi
089988776655, Siti Nurhaliza" class="block w-full px-4 py-3 rounded-xl bg-slate-900 border border-white/[0.12] text-white text-xs font-mono placeholder-slate-500 outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/30 leading-relaxed"></textarea>
                        
                        <div class="flex items-center justify-between pt-1 text-[11px]">
                            <div class="flex items-center gap-1.5 text-slate-400">
                                <i data-lucide="info" class="w-3.5 h-3.5 text-emerald-400 shrink-0"></i>
                                <span>Format: <code>Nomor, Nama, Sekolah</code> dipisahkan koma per baris.</span>
                            </div>
                            <span class="text-emerald-400 font-medium flex items-center gap-1">
                                <i data-lucide="check" class="w-3 h-3"></i> Otomatis tersimpan ke Buku Kontak
                            </span>
                        </div>
                    </div>

                    <!-- 3. Excel File Upload Container -->
                    <div x-show="targetAudience === 'excel'" x-transition class="p-4 rounded-2xl bg-[#0C111D] border border-[#4E6EFF]/40 space-y-3.5 shadow-inner">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-bold uppercase tracking-wider text-[#84D0FF] flex items-center gap-1.5">
                                <i data-lucide="file-spreadsheet" class="w-3.5 h-3.5 text-[#4E6EFF]"></i>
                                <span>Upload File Excel / CSV</span>
                            </label>
                            <a href="{{ route('admin.settings.whatsapp.blast.template') }}" class="px-2.5 py-1 rounded-xl bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-300 border border-emerald-500/40 text-[11px] font-bold transition flex items-center gap-1 shadow-sm">
                                <i data-lucide="download" class="w-3.5 h-3.5"></i>
                                <span>Download Template Excel</span>
                            </a>
                        </div>
                        
                        <div class="border-2 border-dashed border-white/[0.15] hover:border-[#4E6EFF] rounded-xl p-4 text-center bg-slate-900/80 transition relative cursor-pointer group">
                            <input type="file" name="excel_file" accept=".xlsx,.xls,.csv" @change="handleFileSelect($event)" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                            <div class="space-y-1.5 pointer-events-none">
                                <i data-lucide="upload-cloud" class="w-8 h-8 text-[#84D0FF] group-hover:scale-110 transition mx-auto"></i>
                                <p class="text-xs font-bold text-white" x-text="selectedFileName || 'Klik atau seret file Excel/CSV ke sini (.xlsx, .xls, .csv)'"></p>
                                <p class="text-[10px] text-slate-400 font-mono">Format Kolom: A (Nomor WA), B (Nama Penerima), C (Instansi / Sekolah)</p>
                            </div>
                        </div>
                    </div>

                    <!-- Variable Tags Inserter -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-2">Sisipkan Tag Dinamis (Klik untuk Menambahkan)</label>
                        <div class="flex flex-wrap gap-1.5">
                            <button type="button" @click="insertTag('{nama_peserta}')" class="px-3 py-1.5 rounded-xl bg-white/[0.06] hover:bg-white/[0.12] text-[#84D0FF] border border-white/[0.08] text-xs font-mono font-bold transition cursor-pointer">
                                {nama_peserta}
                            </button>
                            <button type="button" @click="insertTag('{nisn}')" class="px-3 py-1.5 rounded-xl bg-white/[0.06] hover:bg-white/[0.12] text-amber-300 border border-white/[0.08] text-xs font-mono font-bold transition cursor-pointer">
                                {nisn}
                            </button>
                            <button type="button" @click="insertTag('{nama_sekolah}')" class="px-3 py-1.5 rounded-xl bg-white/[0.06] hover:bg-white/[0.12] text-[#A594FD] border border-white/[0.08] text-xs font-mono font-bold transition cursor-pointer">
                                {nama_sekolah}
                            </button>
                            <button type="button" @click="insertTag('{cabang_lomba}')" class="px-3 py-1.5 rounded-xl bg-white/[0.06] hover:bg-white/[0.12] text-emerald-400 border border-white/[0.08] text-xs font-mono font-bold transition cursor-pointer">
                                {cabang_lomba}
                            </button>
                            <button type="button" @click="insertTag('{no_peserta}')" class="px-3 py-1.5 rounded-xl bg-white/[0.06] hover:bg-white/[0.12] text-amber-300 border border-white/[0.08] text-xs font-mono font-bold transition cursor-pointer">
                                {no_peserta}
                            </button>
                            <button type="button" @click="insertTag('{kode_pendaftaran}')" class="px-3 py-1.5 rounded-xl bg-white/[0.06] hover:bg-white/[0.12] text-cyan-300 border border-white/[0.08] text-xs font-mono font-bold transition cursor-pointer">
                                {kode_pendaftaran}
                            </button>
                            <button type="button" @click="insertTag('{link_scoreboard}')" class="px-3 py-1.5 rounded-xl bg-white/[0.06] hover:bg-white/[0.12] text-pink-400 border border-white/[0.08] text-xs font-mono font-bold transition cursor-pointer">
                                {link_scoreboard}
                            </button>
                            <button type="button" @click="insertTag('{link_login}')" class="px-3 py-1.5 rounded-xl bg-white/[0.06] hover:bg-white/[0.12] text-indigo-400 border border-white/[0.08] text-xs font-mono font-bold transition cursor-pointer">
                                {link_login}
                            </button>
                        </div>
                    </div>

                    <!-- Message Box -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-300">Isi Pesan WhatsApp</label>
                            <span class="text-xs font-mono text-slate-400" x-text="messageText.length + ' karakter'"></span>
                        </div>
                        <textarea id="messageBox" name="message" x-model="messageText" rows="6" required placeholder="Tuliskan isi pengumuman broadcast resmi di sini..." class="block w-full px-4 py-3 rounded-2xl bg-[#0C111D] border border-white/[0.12] text-white focus:border-[#7A5AF8] focus:ring-2 focus:ring-[#7A5AF8]/30 text-xs sm:text-sm font-sans leading-relaxed outline-none"></textarea>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex items-center justify-end pt-2">
                        <button type="submit" class="gradient-btn px-8 py-3.5 rounded-2xl text-white font-black text-xs shadow-xl shadow-[#7A5AF8]/25 hover:scale-[1.01] transition duration-200 flex items-center gap-2 cursor-pointer">
                            <i data-lucide="send" class="w-4 h-4 text-white"></i>
                            <span>Kirim WhatsApp Blast</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Right: Buku Kontak & Direktori Nomor WA -->
            <div class="lg:col-span-5 space-y-6">
                
                <div class="ai-card rounded-3xl p-6 border border-white/[0.08] text-white shadow-2xl space-y-4">
                    <div class="flex items-center justify-between border-b border-white/[0.08] pb-3">
                        <div class="flex items-center gap-2">
                            <span class="p-1.5 rounded-xl bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                                <i data-lucide="contact" class="w-4 h-4"></i>
                            </span>
                            <div>
                                <h3 class="text-sm font-black text-white font-display">Buku Kontak & Direktori Nomor</h3>
                                <p class="text-[11px] text-slate-400">Pilih kategori untuk melihat, menyalin, memakai, atau mengelola kontak</p>
                            </div>
                        </div>

                        <!-- Add Manual Contact Trigger Button -->
                        <button type="button" @click="showAddContactModal = true" class="p-1.5 rounded-xl bg-amber-500/20 hover:bg-amber-500/30 text-amber-300 border border-amber-500/30 text-xs font-bold transition flex items-center gap-1 cursor-pointer" title="Tambah Kontak Baru Manual">
                            <i data-lucide="user-plus" class="w-3.5 h-3.5"></i>
                            <span class="hidden sm:inline text-[11px]">Tambah</span>
                        </button>
                    </div>

                    <!-- Category Tabs: Peserta, Panitia, Publikasi, Tersimpan (Custom) - BERJAJAR 1 BARIS -->
                    <div class="flex items-center gap-1.5 p-1 bg-[#0C111D] rounded-2xl border border-white/[0.08]">
                        <button type="button" @click="setContactTab('peserta')" 
                            :class="activeContactTab === 'peserta' ? 'bg-gradient-to-r from-[#4E6EFF] to-[#7A5AF8] text-white shadow-lg font-black' : 'text-slate-400 hover:text-white font-medium hover:bg-white/[0.04]'"
                            class="flex-1 py-2 px-2 rounded-xl text-xs transition flex items-center justify-center gap-1.5 cursor-pointer">
                            <span>🎓 Peserta</span>
                            <span class="px-1.5 py-0.5 rounded-full text-[10px] font-mono" :class="activeContactTab === 'peserta' ? 'bg-white/25 text-white' : 'bg-white/[0.06] text-slate-400'" x-text="contactsPeserta.length"></span>
                        </button>

                        <button type="button" @click="setContactTab('panitia')" 
                            :class="activeContactTab === 'panitia' ? 'bg-gradient-to-r from-amber-500 to-orange-600 text-slate-950 shadow-lg font-black' : 'text-slate-400 hover:text-white font-medium hover:bg-white/[0.04]'"
                            class="flex-1 py-2 px-2 rounded-xl text-xs transition flex items-center justify-center gap-1.5 cursor-pointer">
                            <span>🛡️ Panitia</span>
                            <span class="px-1.5 py-0.5 rounded-full text-[10px] font-mono" :class="activeContactTab === 'panitia' ? 'bg-black/25 text-slate-950 font-bold' : 'bg-white/[0.06] text-slate-400'" x-text="contactsPanitia.length"></span>
                        </button>

                        <button type="button" @click="setContactTab('publikasi')" 
                            :class="activeContactTab === 'publikasi' ? 'bg-gradient-to-r from-pink-500 to-rose-600 text-white shadow-lg font-black' : 'text-slate-400 hover:text-white font-medium hover:bg-white/[0.04]'"
                            class="flex-1 py-2 px-2 rounded-xl text-xs transition flex items-center justify-center gap-1.5 cursor-pointer">
                            <span>📢 Publikasi</span>
                            <span class="px-1.5 py-0.5 rounded-full text-[10px] font-mono" :class="activeContactTab === 'publikasi' ? 'bg-white/25 text-white' : 'bg-white/[0.06] text-slate-400'" x-text="contactsPublikasi.length"></span>
                        </button>

                        <button type="button" @click="setContactTab('custom')" 
                            :class="activeContactTab === 'custom' ? 'bg-gradient-to-r from-emerald-500 to-teal-600 text-slate-950 shadow-lg font-black' : 'text-slate-400 hover:text-white font-medium hover:bg-white/[0.04]'"
                            class="flex-1 py-2 px-2 rounded-xl text-xs transition flex items-center justify-center gap-1.5 cursor-pointer">
                            <span>⭐ Manual</span>
                            <span class="px-1.5 py-0.5 rounded-full text-[10px] font-mono" :class="activeContactTab === 'custom' ? 'bg-black/25 text-slate-950 font-bold' : 'bg-white/[0.06] text-slate-400'" x-text="contactsCustom.length"></span>
                        </button>
                    </div>

                    <!-- Search Box & Bulk Action -->
                    <div class="flex items-center gap-2">
                        <div class="relative flex-1">
                            <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-3 top-2.5"></i>
                            <input type="text" x-model="contactSearch" placeholder="Cari nama, instansi, nomor HP..." class="w-full pl-8 pr-3 py-1.5 rounded-xl bg-slate-900 border border-white/[0.1] text-xs text-white placeholder-slate-500 outline-none focus:border-[#7A5AF8]">
                        </div>
                        <button type="button" @click="addAllActiveContactsToManual()" class="px-3 py-1.5 rounded-xl bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-300 border border-emerald-500/40 text-[11px] font-bold transition flex items-center gap-1 shrink-0 cursor-pointer" title="Salin dan masukkan semua nomor pada tab ini ke kotak input broadcast manual">
                            <i data-lucide="user-plus" class="w-3.5 h-3.5"></i>
                            <span class="hidden sm:inline">Pakai Semua</span>
                        </button>
                    </div>

                    <!-- Scrollable Contacts List -->
                    <div class="max-h-[460px] overflow-y-auto space-y-2 pr-1 custom-scrollbar">
                        <template x-for="c in filteredContacts" :key="c.id">
                            <div class="p-2.5 rounded-2xl bg-[#0C111D] border border-white/[0.06] hover:border-white/[0.15] transition flex items-center justify-between gap-3 group">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-8 h-8 rounded-xl bg-white/[0.08] text-white flex items-center justify-center text-xs font-black shrink-0 font-display border border-white/[0.08]"
                                        :class="activeContactTab === 'peserta' ? 'text-[#84D0FF] border-[#4E6EFF]/30' : (activeContactTab === 'panitia' ? 'text-amber-300 border-amber-500/30' : (activeContactTab === 'publikasi' ? 'text-pink-300 border-pink-500/30' : 'text-emerald-300 border-emerald-500/30'))"
                                        x-text="(c.name || 'P').charAt(0).toUpperCase()">
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-1.5">
                                            <p class="text-xs font-bold text-white truncate" x-text="c.name"></p>
                                        </div>
                                        <p class="text-[10px] text-slate-400 truncate" x-text="c.subtitle || c.institution"></p>
                                        <p class="text-[11px] font-mono text-emerald-400 font-bold" x-text="c.display_phone"></p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-1 shrink-0">
                                    <!-- Copy Number Button -->
                                    <button type="button" @click="copyText(c.display_phone, c.id)" class="p-1.5 rounded-lg bg-white/[0.06] hover:bg-white/[0.12] text-slate-300 hover:text-white border border-white/[0.08] transition cursor-pointer relative" title="Salin Nomor WhatsApp">
                                        <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                                    </button>

                                    <!-- Add to Manual Textarea -->
                                    <button type="button" @click="addContactToManual(c.display_phone, c.name, c.institution)" class="px-2.5 py-1.5 rounded-lg bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-300 border border-emerald-500/40 text-[10px] font-bold transition flex items-center gap-1 cursor-pointer" title="Masukkan nomor ini ke Form Broadcast">
                                        <i data-lucide="plus" class="w-3 h-3"></i>
                                        <span>Pakai</span>
                                    </button>

                                    <!-- Delete Button (Only for Custom / Manual Contacts) -->
                                    <template x-if="c.type === 'custom' && c.real_id">
                                        <form :action="'{{ url('admin/settings/whatsapp-blast/contacts') }}/' + c.real_id + '/delete'" method="POST" onsubmit="return confirm('Hapus kontak ini dari Buku Kontak?')">
                                            @csrf
                                            <button type="submit" class="p-1.5 rounded-lg bg-rose-500/20 hover:bg-rose-500/40 text-rose-400 border border-rose-500/30 transition cursor-pointer" title="Hapus Kontak Ini">
                                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                            </button>
                                        </form>
                                    </template>

                                    <!-- Direct WhatsApp Link -->
                                    <a :href="'https://wa.me/' + c.phone" target="_blank" class="p-1.5 rounded-lg bg-emerald-500/20 hover:bg-emerald-500/40 text-emerald-400 border border-emerald-500/30 transition cursor-pointer" title="Buka Chat WhatsApp">
                                        <i data-lucide="message-circle" class="w-3.5 h-3.5"></i>
                                    </a>
                                </div>
                            </div>
                        </template>

                        <div x-show="filteredContacts.length === 0" class="py-8 text-center text-xs text-slate-500">
                            Tidak ada nomor kontak yang cocok dengan pencarian.
                        </div>
                    </div>
                </div>

            </div>

        </div>

        <!-- Broadcast History Table -->
        <div class="ai-card rounded-3xl border border-white/[0.08] shadow-2xl overflow-hidden p-6 sm:p-8 space-y-4 text-white">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-white/[0.08] pb-4">
                <div>
                    <h3 class="text-base sm:text-lg font-black text-white font-display">Riwayat Pengiriman WhatsApp Blast</h3>
                    <p class="text-xs text-slate-400">Daftar rekaman broadcast yang telah dikirimkan oleh administrator</p>
                </div>
                @if($broadcastLogs->count() > 0)
                    <form action="{{ route('admin.settings.whatsapp.blast.logs.clear-all') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus SELURUH riwayat broadcast WhatsApp?')">
                        @csrf
                        <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-rose-500/15 hover:bg-rose-500/25 text-rose-400 border border-rose-500/30 text-xs font-bold transition flex items-center gap-1.5 cursor-pointer shadow-sm">
                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                            <span>Bersihkan Semua Riwayat</span>
                        </button>
                    </form>
                @endif
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="text-[11px] font-bold uppercase tracking-wider bg-[#0C111D]/90 text-slate-400 border-b border-white/[0.08]">
                        <tr>
                            <th class="py-3.5 px-4">Waktu</th>
                            <th class="py-3.5 px-4">Pengirim</th>
                            <th class="py-3.5 px-4">Target Audience</th>
                            <th class="py-3.5 px-4 text-center">Jumlah Penerima</th>
                            <th class="py-3.5 px-4">Cuplikan Pesan</th>
                            <th class="py-3.5 px-4">Status</th>
                            <th class="py-3.5 px-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/[0.04] font-medium">
                        @forelse($broadcastLogs as $log)
                            <tr class="hover:bg-white/[0.02] transition text-xs">
                                <td class="py-3.5 px-4 font-mono text-slate-400">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                                <td class="py-3.5 px-4 font-bold text-white">{{ $log->sender->name ?? 'Admin / Sistem' }}</td>
                                <td class="py-3.5 px-4 capitalize text-[#84D0FF]">
                                    {{ $log->target_competition ? $log->target_competition : $log->target_audience }}
                                </td>
                                <td class="py-3.5 px-4 text-center font-bold font-mono text-emerald-400">{{ $log->recipients_count }} Kontak</td>
                                <td class="py-3.5 px-4 text-slate-400 truncate max-w-xs">{{ $log->message }}</td>
                                <td class="py-3.5 px-4">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">
                                        Terkirim
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <form action="{{ route('admin.settings.whatsapp.blast.logs.delete', $log->id) }}" method="POST" onsubmit="return confirm('Hapus log riwayat ini?')">
                                        @csrf
                                        <button type="submit" class="p-1.5 rounded-lg bg-white/[0.05] hover:bg-rose-500/20 text-slate-400 hover:text-rose-400 border border-white/[0.08] hover:border-rose-500/30 transition cursor-pointer" title="Hapus Riwayat Ini">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-slate-500">Belum ada riwayat pengiriman broadcast.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($broadcastLogs->hasPages())
                <div class="pt-4 border-t border-white/[0.08]">
                    {{ $broadcastLogs->links() }}
                </div>
            @endif
        </div>

    </div>

    <!-- ========================================================================= -->
    <!-- TAB 2: TEMPLATE PESAN & AUTO-TRIGGER NOTIFIKASI -->
    <!-- ========================================================================= -->
    <div x-show="mainTab === 'templates'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6">
        
        <!-- Header & Action Bar -->
        <div class="ai-card rounded-3xl p-6 sm:p-8 border border-white/[0.08] text-white shadow-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2.5">
                    <span class="p-2 rounded-2xl bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 shadow-md">
                        <i data-lucide="zap" class="w-5 h-5"></i>
                    </span>
                    <div>
                        <h3 class="text-base sm:text-lg font-black text-white font-display">Manajemen Template & Auto-Trigger WhatsApp</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Atur pesan otomatis saat pendaftaran serta buat dan kelola template kustom</p>
                    </div>
                </div>
            </div>

            <button type="button" @click="openCreateTemplateModal()" class="px-5 py-3 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-400 hover:to-teal-500 text-slate-950 font-black text-xs sm:text-sm shadow-xl shadow-emerald-500/20 flex items-center justify-center gap-2 cursor-pointer shrink-0 transition">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                <span>Buat Template Kustom Baru</span>
            </button>
        </div>

        <!-- 3 Trigger Otomatis Banner Notice -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="p-4 rounded-3xl bg-indigo-500/10 border border-indigo-500/30 text-white space-y-1.5 shadow-lg">
                <div class="flex items-center gap-2 text-indigo-300 font-bold text-xs">
                    <span class="w-2 h-2 rounded-full bg-indigo-400 animate-pulse"></span>
                    <span>1. Auto-Trigger: Pembuatan Akun</span>
                </div>
                <p class="text-[11px] text-slate-300">Terkirim instan saat peserta mendaftar akun di portal (menyertakan NISN & password).</p>
            </div>

            <div class="p-4 rounded-3xl bg-cyan-500/10 border border-cyan-500/30 text-white space-y-1.5 shadow-lg">
                <div class="flex items-center gap-2 text-cyan-300 font-bold text-xs">
                    <span class="w-2 h-2 rounded-full bg-cyan-400 animate-pulse"></span>
                    <span>2. Auto-Trigger: Pengiriman Pendaftaran</span>
                </div>
                <p class="text-[11px] text-slate-300">Terkirim saat formulir pendaftaran cabang lomba berhasil di-submit oleh peserta.</p>
            </div>

            <div class="p-4 rounded-3xl bg-emerald-500/10 border border-emerald-500/30 text-white space-y-1.5 shadow-lg">
                <div class="flex items-center gap-2 text-emerald-300 font-bold text-xs">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>3. Auto-Trigger: Pendaftaran Terverifikasi</span>
                </div>
                <p class="text-[11px] text-slate-300">Terkirim saat admin / PIC memvalidasi berkas pendaftaran menjadi sah & terverifikasi.</p>
            </div>
        </div>

        <!-- List of Templates Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            @foreach($whatsappTemplates as $template)
                <div class="ai-card rounded-3xl p-6 sm:p-7 border border-white/[0.08] hover:border-white/[0.2] transition text-white shadow-2xl flex flex-col justify-between space-y-4 relative overflow-hidden group">
                    
                    <!-- Top Info & Badges -->
                    <div class="space-y-2.5">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h4 class="text-sm sm:text-base font-black text-white font-display">{{ $template->name }}</h4>
                                    @if($template->is_system)
                                        <span class="px-2 py-0.5 rounded-full text-[9px] font-extrabold uppercase tracking-wider bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                                            Sistem Auto-Trigger
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[9px] font-extrabold uppercase tracking-wider bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                            Template Kustom
                                        </span>
                                    @endif
                                </div>
                                <p class="text-[11px] text-slate-400 mt-1 leading-relaxed">{{ $template->description ?: 'Template pesan WhatsApp siap pakai' }}</p>
                            </div>

                            <!-- Trigger Status Toggle Button -->
                            <form action="{{ route('admin.settings.whatsapp.blast.templates.toggle', $template->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 cursor-pointer shadow-sm {{ $template->is_active ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 hover:bg-emerald-500/30' : 'bg-slate-800 text-slate-400 border border-slate-700 hover:text-white' }}" title="Klik untuk mengubah status aktif auto-trigger">
                                    <span class="w-2 h-2 rounded-full {{ $template->is_active ? 'bg-emerald-400' : 'bg-slate-500' }}"></span>
                                    <span>{{ $template->is_active ? 'Auto-Trigger Aktif' : 'Nonaktif' }}</span>
                                </button>
                            </form>
                        </div>

                        <!-- Message Content Preview Box -->
                        <div class="p-4 rounded-2xl bg-[#0C111D] border border-white/[0.08] text-xs font-mono text-slate-300 whitespace-pre-wrap leading-relaxed max-h-56 overflow-y-auto custom-scrollbar">
{{ $template->message }}
                        </div>
                    </div>

                    <!-- Bottom Action Buttons -->
                    <div class="flex items-center justify-between pt-3 border-t border-white/[0.06] text-xs">
                        <!-- Use in Broadcast Composer -->
                        <button type="button" @click="useTemplate(@json($template))" class="px-3.5 py-2 rounded-xl bg-[#7A5AF8]/20 hover:bg-[#7A5AF8]/30 text-[#A594FD] border border-[#7A5AF8]/30 font-bold transition flex items-center gap-1.5 cursor-pointer">
                            <i data-lucide="send" class="w-3.5 h-3.5"></i>
                            <span>Pakai di Broadcast</span>
                        </button>

                        <div class="flex items-center gap-1.5">
                            <!-- Edit Button -->
                            <button type="button" @click="openEditTemplateModal(@json($template))" class="p-2 rounded-xl bg-white/[0.05] hover:bg-white/[0.1] text-slate-300 hover:text-white border border-white/[0.08] transition cursor-pointer" title="Edit Isi Template Ini">
                                <i data-lucide="edit" class="w-4 h-4"></i>
                            </button>

                            <!-- Delete Button (Only for Custom Templates) -->
                            @if(!$template->is_system)
                                <form action="{{ route('admin.settings.whatsapp.blast.templates.delete', $template->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus template kustom ini?')">
                                    @csrf
                                    <button type="submit" class="p-2 rounded-xl bg-rose-500/20 hover:bg-rose-500/30 text-rose-400 border border-rose-500/30 transition cursor-pointer" title="Hapus Template Kustom">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>

                </div>
            @endforeach
        </div>

    </div>

    <!-- ========================================================================= -->
    <!-- TAB 3: PENGATURAN API GATEWAY WABLAS (DEDICATED FULL VIEW) -->
    <!-- ========================================================================= -->
    <div x-show="mainTab === 'api'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6">
        
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <!-- Left: API Credentials Card -->
            <div class="lg:col-span-7 ai-card rounded-3xl p-6 sm:p-8 border border-white/[0.08] text-white shadow-2xl space-y-6">
                
                <div class="flex items-center justify-between border-b border-white/[0.08] pb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-amber-500/15 border border-amber-500/30 flex items-center justify-center text-amber-400 shadow-lg shadow-amber-500/10">
                            <i data-lucide="key-round" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="text-base sm:text-lg font-black text-white uppercase tracking-wider font-display">Kredensial Gateway Wablas</h3>
                            <p class="text-xs text-slate-400">Konfigurasi token dan server gateway WhatsApp resmi</p>
                        </div>
                    </div>
                    
                    <!-- SIGNAL BADGE -->
                    <div class="inline-flex items-center cursor-pointer transition hover:scale-105 active:scale-95" @click="checkStatus()" title="Klik untuk refresh sinyal">
                        <div x-show="statusState === 'loading'" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-amber-500/15 border border-amber-500/30 text-amber-300 text-xs font-bold font-mono animate-pulse">
                            <span class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                            </span>
                            <span>Memeriksa...</span>
                        </div>

                        <div x-show="statusState === 'connected'" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 text-xs font-extrabold font-mono shadow-sm shadow-emerald-500/20">
                            <span class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                            </span>
                            <i data-lucide="wifi" class="w-3.5 h-3.5 text-emerald-400"></i>
                            <span>Terhubung</span>
                        </div>

                        <div x-show="statusState === 'disconnected'" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-rose-500/15 border border-rose-500/30 text-rose-400 text-xs font-extrabold font-mono shadow-sm">
                            <span class="h-2 w-2 rounded-full bg-rose-500"></span>
                            <i data-lucide="wifi-off" class="w-3.5 h-3.5 text-rose-400"></i>
                            <span>Terputus</span>
                        </div>

                        <div x-show="statusState === 'unconfigured'" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-slate-800 border border-slate-700 text-slate-400 text-xs font-bold font-mono">
                            <span class="h-2 w-2 rounded-full bg-slate-500"></span>
                            <span>Belum Dikonfigurasi</span>
                        </div>
                    </div>
                </div>

                <!-- Form Kredensial -->
                <form action="{{ route('admin.settings.whatsapp.blast.save-credentials') }}" method="POST" class="space-y-4">
                    @csrf

                    <!-- 1. API Host Box -->
                    <div class="p-4 rounded-2xl bg-[#0C111D] border border-white/[0.08] space-y-2 hover:border-amber-400/40 transition">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-bold text-slate-300 flex items-center gap-1.5">
                                <span class="text-amber-400 text-sm">🌐</span>
                                <span>API Host (Server Domain Wablas)</span>
                            </label>
                            <button type="button" @click="copy(apiHost, 'host')" class="p-1.5 rounded-lg bg-white/[0.06] hover:bg-white/[0.12] text-amber-300 hover:text-amber-200 border border-white/[0.08] transition cursor-pointer flex items-center gap-1 text-[10px] font-bold" title="Salin API Host">
                                <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                                <span x-show="copiedField === 'host'" class="text-emerald-400 font-mono text-[10px]">Tersalin!</span>
                            </button>
                        </div>
                        <input type="text" name="api_host" x-model="apiHost" required placeholder="https://jogja.wablas.com" class="block w-full px-4 py-3 rounded-xl bg-slate-900 border border-white/[0.1] text-xs sm:text-sm font-mono text-white placeholder-slate-500 outline-none focus:border-amber-400 focus:ring-1 focus:ring-amber-400/30">
                        <p class="text-[10px] text-slate-400">Contoh: <code>https://jogja.wablas.com</code>, <code>https://solo.wablas.com</code>, atau server yang tertera di address bar browser saat login Wablas.</p>
                    </div>

                    <!-- 2. Token Box -->
                    <div class="p-4 rounded-2xl bg-[#0C111D] border border-white/[0.08] space-y-2 hover:border-amber-400/40 transition">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-bold text-slate-300 flex items-center gap-1.5">
                                <span class="text-amber-400 text-sm">🔑</span>
                                <span>API Token (Device Token)</span>
                            </label>
                            <div class="flex items-center gap-1.5">
                                <button type="button" @click="showToken = !showToken" class="p-1.5 rounded-lg bg-white/[0.06] hover:bg-white/[0.12] text-amber-300 hover:text-amber-200 border border-white/[0.08] transition cursor-pointer" :title="showToken ? 'Sembunyikan' : 'Tampilkan Token'">
                                    <i :data-lucide="showToken ? 'eye-off' : 'eye'" class="w-3.5 h-3.5"></i>
                                </button>
                                <button type="button" @click="copy(apiToken, 'token')" class="p-1.5 rounded-lg bg-white/[0.06] hover:bg-white/[0.12] text-amber-300 hover:text-amber-200 border border-white/[0.08] transition cursor-pointer flex items-center gap-1 text-[10px] font-bold" title="Salin Token">
                                    <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                                    <span x-show="copiedField === 'token'" class="text-emerald-400 font-mono text-[10px]">Tersalin!</span>
                                </button>
                            </div>
                        </div>
                        <input :type="showToken ? 'text' : 'password'" name="token" x-model="apiToken" required placeholder="Masukkan API Token device Wablas..." class="block w-full px-4 py-3 rounded-xl bg-slate-900 border border-white/[0.1] text-xs sm:text-sm font-mono text-white placeholder-slate-500 outline-none focus:border-amber-400 focus:ring-1 focus:ring-amber-400/30 tracking-wider">
                    </div>

                    <!-- 3. Secret Key Box -->
                    <div class="p-4 rounded-2xl bg-[#0C111D] border border-white/[0.08] space-y-2 hover:border-amber-400/40 transition">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-bold text-slate-300 flex items-center gap-1.5">
                                <span class="text-amber-400 text-sm">🔒</span>
                                <span>Secret Key (Opsional)</span>
                            </label>
                            <div class="flex items-center gap-1.5">
                                <button type="button" @click="showSecret = !showSecret" class="p-1.5 rounded-lg bg-white/[0.06] hover:bg-white/[0.12] text-amber-300 hover:text-amber-200 border border-white/[0.08] transition cursor-pointer" :title="showSecret ? 'Sembunyikan' : 'Tampilkan Secret Key'">
                                    <i :data-lucide="showSecret ? 'eye-off' : 'eye'" class="w-3.5 h-3.5"></i>
                                </button>
                                <button type="button" @click="copy(secretKey, 'secret')" class="p-1.5 rounded-lg bg-white/[0.06] hover:bg-white/[0.12] text-amber-300 hover:text-amber-200 border border-white/[0.08] transition cursor-pointer flex items-center gap-1 text-[10px] font-bold" title="Salin Secret Key">
                                    <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                                    <span x-show="copiedField === 'secret'" class="text-emerald-400 font-mono text-[10px]">Tersalin!</span>
                                </button>
                            </div>
                        </div>
                        <input :type="showSecret ? 'text' : 'password'" name="secret_key" x-model="secretKey" placeholder="Biarkan kosong jika paket Wablas tidak menggunakan secret key..." class="block w-full px-4 py-3 rounded-xl bg-slate-900 border border-white/[0.1] text-xs sm:text-sm font-mono text-white placeholder-slate-500 outline-none focus:border-amber-400 focus:ring-1 focus:ring-amber-400/30 tracking-wider">
                    </div>

                    <!-- Button: Save Credentials -->
                    <div class="pt-2">
                        <button type="submit" class="w-full py-3.5 rounded-2xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-sm transition shadow-xl shadow-amber-500/20 flex items-center justify-center gap-2 cursor-pointer">
                            <i data-lucide="save" class="w-4 h-4"></i>
                            <span>Simpan Kredensial Wablas</span>
                        </button>
                    </div>
                </form>

                <!-- Live Device Details Banner when connected -->
                <div x-show="statusState === 'connected'" x-transition class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 shadow-inner">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-emerald-500/20 border border-emerald-500/40 text-emerald-400 flex items-center justify-center font-black shrink-0">
                            <i data-lucide="smartphone" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <span class="text-slate-400 text-[10px] uppercase font-bold tracking-wider block">Device Aktif Terhubung:</span>
                            <span class="text-white font-mono font-black text-sm block" x-text="deviceSender"></span>
                        </div>
                    </div>
                    <div class="text-xs font-mono text-emerald-300 font-bold bg-emerald-500/20 px-3.5 py-1.5 rounded-xl border border-emerald-500/30 shrink-0">
                        Sisa Kuota: <span class="text-white font-extrabold text-sm" x-text="remainingQuota"></span>
                    </div>
                </div>

                <!-- Disconnected / Error Detail Banner -->
                <div x-show="statusState === 'disconnected' && statusMessage" x-transition class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-xs flex items-center gap-3 text-rose-300">
                    <i data-lucide="alert-circle" class="w-5 h-5 text-rose-400 shrink-0"></i>
                    <div class="min-w-0 flex-1">
                        <span class="font-bold text-white text-xs block">Status Koneksi: Terputus</span>
                        <span class="text-xs text-rose-300 break-words" x-text="statusMessage"></span>
                    </div>
                </div>

                <!-- Test Connection Trigger -->
                <div class="pt-2 border-t border-white/[0.06]">
                    <button type="button" @click="checkStatus()" :disabled="isChecking" class="w-full py-3 rounded-2xl bg-white/[0.05] hover:bg-white/[0.1] text-[#84D0FF] border border-[#4E6EFF]/30 font-bold text-xs sm:text-sm transition flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50" title="Cek status sinyal & koneksi token ke server Wablas">
                        <i data-lucide="refresh-cw" class="w-4 h-4 text-[#84D0FF]" :class="isChecking ? 'animate-spin' : ''"></i>
                        <span x-text="isChecking ? 'Memeriksa Sinyal Gateway...' : 'Uji Coba Koneksi Device Wablas'"></span>
                    </button>
                </div>

            </div>

            <!-- Right: Petunjuk & Tips Setting Delay Wablas -->
            <div class="lg:col-span-5 space-y-6">
                
                <!-- Card 1: Cara Mempercepat Kirim (Setting Delay) -->
                <div class="ai-card rounded-3xl p-6 border border-emerald-500/30 bg-emerald-500/[0.03] text-white shadow-2xl space-y-4">
                    <div class="flex items-center gap-3 border-b border-white/[0.08] pb-3.5">
                        <div class="w-8 h-8 rounded-xl bg-emerald-500/20 border border-emerald-500/30 flex items-center justify-center text-emerald-400">
                            <i data-lucide="zap" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-black text-white font-display">Tips Pengiriman Cepat (1–3 Detik)</h4>
                            <p class="text-[11px] text-slate-400">Cara mengatur jeda delay pesan di Wablas</p>
                        </div>
                    </div>

                    <div class="space-y-3 text-xs text-slate-300 leading-relaxed">
                        <p>Jika pengiriman broadcast terasa lambat (hampir 2 menit), hal itu disebabkan oleh pengaturan antrean default bawaan Wablas (30 detik per pesan).</p>
                        
                        <div class="p-3.5 rounded-2xl bg-[#0C111D] border border-white/[0.08] space-y-2">
                            <span class="text-amber-400 font-bold text-xs block">⚡ Langkah Mempercepat:</span>
                            <ol class="list-decimal list-inside space-y-1 text-slate-300 text-[11px]">
                                <li>Buka dashboard Wablas (<code class="text-amber-300">https://jogja.wablas.com</code>).</li>
                                <li>Masuk ke menu <strong>Device</strong> / <strong>Settings</strong>.</li>
                                <li>Ubah <strong>Delay Message</strong> dari <code>30 seconds</code> menjadi <strong><code>2 seconds</code></strong> atau <strong><code>3 seconds</code></strong>.</li>
                                <li>Klik <strong>Save</strong> di Wablas.</li>
                            </ol>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Panduan Variabel Template -->
                <div class="ai-card rounded-3xl p-6 border border-white/[0.08] text-white shadow-2xl space-y-4">
                    <div class="flex items-center gap-3 border-b border-white/[0.08] pb-3.5">
                        <div class="w-8 h-8 rounded-xl bg-[#4E6EFF]/20 border border-[#4E6EFF]/30 flex items-center justify-center text-[#84D0FF]">
                            <i data-lucide="code" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-black text-white font-display">Daftar Tag Dinamis Pesan</h4>
                            <p class="text-[11px] text-slate-400">Tag otomatis yang dapat disisipkan dalam pesan</p>
                        </div>
                    </div>

                    <div class="space-y-2 text-xs">
                        <div class="p-2.5 rounded-xl bg-[#0C111D] border border-white/[0.06] flex items-center justify-between">
                            <code class="text-[#84D0FF] font-bold">{nama_peserta}</code>
                            <span class="text-slate-400 text-[11px]">Nama Peserta / Pendaftar</span>
                        </div>
                        <div class="p-2.5 rounded-xl bg-[#0C111D] border border-white/[0.06] flex items-center justify-between">
                            <code class="text-amber-300 font-bold">{nisn}</code>
                            <span class="text-slate-400 text-[11px]">NISN Peserta</span>
                        </div>
                        <div class="p-2.5 rounded-xl bg-[#0C111D] border border-white/[0.06] flex items-center justify-between">
                            <code class="text-[#A594FD] font-bold">{nama_sekolah}</code>
                            <span class="text-slate-400 text-[11px]">Nama Asal Sekolah / Lembaga</span>
                        </div>
                        <div class="p-2.5 rounded-xl bg-[#0C111D] border border-white/[0.06] flex items-center justify-between">
                            <code class="text-emerald-400 font-bold">{cabang_lomba}</code>
                            <span class="text-slate-400 text-[11px]">Nama Cabang Perlombaan</span>
                        </div>
                        <div class="p-2.5 rounded-xl bg-[#0C111D] border border-white/[0.06] flex items-center justify-between">
                            <code class="text-amber-300 font-bold">{no_peserta}</code>
                            <span class="text-slate-400 text-[11px]">Nomor Peserta Resmi</span>
                        </div>
                        <div class="p-2.5 rounded-xl bg-[#0C111D] border border-white/[0.06] flex items-center justify-between">
                            <code class="text-cyan-300 font-bold">{kode_pendaftaran}</code>
                            <span class="text-slate-400 text-[11px]">Kode Pendaftaran Lomba</span>
                        </div>
                        <div class="p-2.5 rounded-xl bg-[#0C111D] border border-white/[0.06] flex items-center justify-between">
                            <code class="text-pink-400 font-bold">{link_scoreboard}</code>
                            <span class="text-slate-400 text-[11px]">Link Live Scoreboard Resmi</span>
                        </div>
                        <div class="p-2.5 rounded-xl bg-[#0C111D] border border-white/[0.06] flex items-center justify-between">
                            <code class="text-indigo-400 font-bold">{link_login}</code>
                            <span class="text-slate-400 text-[11px]">Link Login Portal</span>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </div>

    <!-- MODAL 1: TAMBAH KONTAK MANUAL -->
    <div x-show="showAddContactModal" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm" style="display: none;">
        <div class="ai-card w-full max-w-md p-6 rounded-3xl border border-white/[0.12] text-white shadow-2xl space-y-4" @click.outside="showAddContactModal = false">
            <div class="flex items-center justify-between border-b border-white/[0.08] pb-3">
                <div class="flex items-center gap-2">
                    <span class="p-1.5 rounded-xl bg-amber-500/20 text-amber-400">
                        <i data-lucide="user-plus" class="w-4 h-4"></i>
                    </span>
                    <h3 class="text-sm font-black font-display text-white">Tambah Kontak Manual Baru</h3>
                </div>
                <button type="button" @click="showAddContactModal = false" class="text-slate-400 hover:text-white p-1">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form action="{{ route('admin.settings.whatsapp.blast.contacts.store') }}" method="POST" class="space-y-3.5">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1">Nama Penerima / Kontak *</label>
                    <input type="text" name="name" required placeholder="Contoh: Bpk. H. Supriyadi" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-white/[0.1] text-xs text-white outline-none focus:border-[#7A5AF8]">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1">Nomor WhatsApp *</label>
                    <input type="text" name="phone" required placeholder="Contoh: 081234567890" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-white/[0.1] text-xs font-mono text-white outline-none focus:border-[#7A5AF8]">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1">Nama Instansi / Sekolah (Opsional)</label>
                    <input type="text" name="institution" placeholder="Contoh: MI Al-Hidayah Blitar" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-white/[0.1] text-xs text-white outline-none focus:border-[#7A5AF8]">
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-white/[0.08]">
                    <button type="button" @click="showAddContactModal = false" class="px-4 py-2 rounded-xl bg-white/[0.05] hover:bg-white/[0.1] text-xs text-slate-300 font-bold">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-xs text-slate-950 font-black shadow-lg shadow-amber-500/20">
                        Simpan ke Kontak
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 2: BUAT TEMPLATE KUSTOM BARU -->
    <div x-show="showCreateTemplateModal" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm" style="display: none;">
        <div class="ai-card w-full max-w-lg p-6 sm:p-7 rounded-3xl border border-white/[0.12] text-white shadow-2xl space-y-4" @click.outside="showCreateTemplateModal = false">
            <div class="flex items-center justify-between border-b border-white/[0.08] pb-3">
                <div class="flex items-center gap-2">
                    <span class="p-1.5 rounded-xl bg-emerald-500/20 text-emerald-400">
                        <i data-lucide="plus-circle" class="w-4 h-4"></i>
                    </span>
                    <h3 class="text-sm sm:text-base font-black font-display text-white">Buat Template Pesan Kustom Baru</h3>
                </div>
                <button type="button" @click="showCreateTemplateModal = false" class="text-slate-400 hover:text-white p-1">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form action="{{ route('admin.settings.whatsapp.blast.templates.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1">Nama Judul Template *</label>
                    <input type="text" name="name" required placeholder="Contoh: Pengumuman Pembagian Sertifikat & Hadiah" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-white/[0.1] text-xs text-white outline-none focus:border-emerald-400">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1">Deskripsi Singkat (Opsional)</label>
                    <input type="text" name="description" placeholder="Contoh: Template broadcast pemberitahuan pengambilan sertifikat" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-white/[0.1] text-xs text-white outline-none focus:border-emerald-400">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1">Isi Pesan Template *</label>
                    <textarea name="message" rows="6" required placeholder="Tuliskan isi format pesan WhatsApp di sini... Gunakan tag seperti {nama_peserta}, {cabang_lomba}, {nama_sekolah}, dll." class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-white/[0.1] text-xs font-mono text-white outline-none focus:border-emerald-400 leading-relaxed"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-white/[0.08]">
                    <button type="button" @click="showCreateTemplateModal = false" class="px-4 py-2 rounded-xl bg-white/[0.05] hover:bg-white/[0.1] text-xs text-slate-300 font-bold">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-xs text-slate-950 font-black shadow-lg shadow-emerald-500/20">
                        Simpan Template
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 3: EDIT TEMPLATE -->
    <div x-show="showEditTemplateModal" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm" style="display: none;">
        <div class="ai-card w-full max-w-lg p-6 sm:p-7 rounded-3xl border border-white/[0.12] text-white shadow-2xl space-y-4" @click.outside="showEditTemplateModal = false">
            <div class="flex items-center justify-between border-b border-white/[0.08] pb-3">
                <div class="flex items-center gap-2">
                    <span class="p-1.5 rounded-xl bg-[#7A5AF8]/20 text-[#A594FD]">
                        <i data-lucide="edit" class="w-4 h-4"></i>
                    </span>
                    <h3 class="text-sm sm:text-base font-black font-display text-white">Edit Template Pesan WhatsApp</h3>
                </div>
                <button type="button" @click="showEditTemplateModal = false" class="text-slate-400 hover:text-white p-1">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form :action="'{{ url('admin/settings/whatsapp-blast/templates') }}/' + editingTemplate.id + '/update'" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1">Nama Judul Template *</label>
                    <input type="text" name="name" x-model="editingTemplate.name" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-white/[0.1] text-xs text-white outline-none focus:border-[#7A5AF8]">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1">Deskripsi Singkat</label>
                    <input type="text" name="description" x-model="editingTemplate.description" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-white/[0.1] text-xs text-white outline-none focus:border-[#7A5AF8]">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1">Isi Pesan Template *</label>
                    <textarea name="message" x-model="editingTemplate.message" rows="7" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-white/[0.1] text-xs font-mono text-white outline-none focus:border-[#7A5AF8] leading-relaxed"></textarea>
                </div>

                <div class="flex items-center justify-between pt-2 border-t border-white/[0.08]">
                    <label class="flex items-center gap-2 cursor-pointer text-xs text-slate-300">
                        <input type="checkbox" name="is_active" value="1" :checked="editingTemplate.is_active" class="rounded bg-slate-900 border-white/[0.2] text-[#7A5AF8] focus:ring-0">
                        <span>Aktifkan Template / Auto-Trigger</span>
                    </label>

                    <div class="flex items-center gap-2">
                        <button type="button" @click="showEditTemplateModal = false" class="px-4 py-2 rounded-xl bg-white/[0.05] hover:bg-white/[0.1] text-xs text-slate-300 font-bold">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#7A5AF8] hover:bg-[#6842f6] text-xs text-white font-black shadow-lg shadow-[#7A5AF8]/20">
                            Simpan Perubahan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

</div>

@push('scripts')
<script>
    function whatsappBlastApp() {
        return {
            mainTab: 'broadcast', // 'broadcast', 'templates', 'api'
            targetAudience: 'all',
            manualNumbers: '',
            selectedFileName: '',
            activeContactTab: 'peserta',
            contactSearch: '',
            showAddContactModal: false,
            showCreateTemplateModal: false,
            showEditTemplateModal: false,
            editingTemplate: {},
            templatesList: @json($whatsappTemplates),
            contactsPeserta: @json($participantContacts),
            contactsPanitia: @json($committeeContacts),
            contactsPublikasi: @json($publicationContacts),
            contactsCustom: @json($customContacts),
            messageText: 'Yth. Official {nama_sekolah} & Peserta {nama_peserta},\n\nTerima kasih telah mendaftar di ajang TALENTA 2026 MTsN 1 Blitar pada cabang {cabang_lomba}.\n\nNomor peserta resmi Anda adalah: {no_peserta}.\n\nPantau live scoreboard dan hasil undian melalui:\n{link_scoreboard}\n\nSalam hangat,\nPanitia TALENTA 2026',
            
            // API Credential States
            apiHost: '{{ $wablasCredentials['api_host'] ?? 'https://jogja.wablas.com' }}',
            apiToken: '{{ $wablasCredentials['token'] ?? '' }}',
            secretKey: '{{ $wablasCredentials['secret_key'] ?? '' }}',
            showToken: false,
            showSecret: false,
            copiedField: null,
            statusState: 'loading',
            statusMessage: '',
            deviceSender: '',
            remainingQuota: '',
            isChecking: false,

            init() {
                this.checkStatus();
            },

            setMainTab(tab) {
                this.mainTab = tab;
                this.$nextTick(() => {
                    if (window.lucide) lucide.createIcons();
                });
            },

            useTemplate(tmpl) {
                if (tmpl && tmpl.message) {
                    this.messageText = tmpl.message;
                    this.mainTab = 'broadcast';
                    this.$nextTick(() => {
                        const textarea = document.getElementById('messageBox');
                        if (textarea) {
                            textarea.focus();
                            textarea.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                    });
                }
            },

            openCreateTemplateModal() {
                this.showCreateTemplateModal = true;
                this.$nextTick(() => {
                    if (window.lucide) lucide.createIcons();
                });
            },

            openEditTemplateModal(tmpl) {
                this.editingTemplate = Object.assign({}, tmpl);
                this.showEditTemplateModal = true;
                this.$nextTick(() => {
                    if (window.lucide) lucide.createIcons();
                });
            },

            async checkStatus() {
                this.isChecking = true;
                this.statusState = 'loading';
                try {
                    const res = await fetch('{{ route('admin.settings.whatsapp.blast.check-status') }}');
                    const data = await res.json();
                    if (data.connected) {
                        this.statusState = 'connected';
                        this.statusMessage = data.message || 'Terhubung';
                        this.deviceSender = data.sender || '';
                        this.remainingQuota = data.quota || '-';
                    } else {
                        this.statusState = data.status === 'unconfigured' ? 'unconfigured' : 'disconnected';
                        this.statusMessage = data.message || 'Terputus';
                        this.deviceSender = '';
                        this.remainingQuota = '';
                    }
                } catch (e) {
                    this.statusState = 'disconnected';
                    this.statusMessage = 'Gagal Cek: ' + (e.message || '');
                } finally {
                    this.isChecking = false;
                    this.$nextTick(() => {
                        if (window.lucide) lucide.createIcons();
                    });
                }
            },

            copy(text, field) {
                if (!text) return;
                navigator.clipboard.writeText(text);
                this.copiedField = field;
                setTimeout(() => this.copiedField = null, 2000);
            },

            get manualCountText() {
                if (!this.manualNumbers || !this.manualNumbers.trim()) return '0 Nomor';
                const lines = this.manualNumbers.trim().split(/[\r\n]+/);
                const count = lines.filter(l => l.replace(/[^0-9]/g, '').length >= 8).length;
                return `${count} Nomor`;
            },
            
            get filteredContacts() {
                let list = [];
                if (this.activeContactTab === 'peserta') list = this.contactsPeserta;
                else if (this.activeContactTab === 'panitia') list = this.contactsPanitia;
                else if (this.activeContactTab === 'publikasi') list = this.contactsPublikasi;
                else if (this.activeContactTab === 'custom') list = this.contactsCustom;

                if (!this.contactSearch || !this.contactSearch.trim()) return list;
                const q = this.contactSearch.toLowerCase();
                return list.filter(c => 
                    (c.name && c.name.toLowerCase().includes(q)) ||
                    (c.subtitle && c.subtitle.toLowerCase().includes(q)) ||
                    (c.display_phone && c.display_phone.includes(q)) ||
                    (c.institution && c.institution.toLowerCase().includes(q))
                );
            },

            setContactTab(tab) {
                this.activeContactTab = tab;
                this.$nextTick(() => {
                    if (window.lucide) lucide.createIcons();
                });
            },

            addContactToManual(phone, name, school) {
                this.targetAudience = 'manual';
                const entry = `${phone}, ${name || 'Bapak/Ibu'}, ${school || '-'}`;
                if (this.manualNumbers.trim()) {
                    this.manualNumbers += '\n' + entry;
                } else {
                    this.manualNumbers = entry;
                }
            },

            addAllActiveContactsToManual() {
                this.targetAudience = 'manual';
                const currentList = this.filteredContacts;
                if (!currentList.length) return;
                const entries = currentList.map(c => `${c.display_phone}, ${c.name || 'Bapak/Ibu'}, ${c.institution || '-'}`).join('\n');
                if (this.manualNumbers.trim()) {
                    this.manualNumbers += '\n' + entries;
                } else {
                    this.manualNumbers = entries;
                }
            },

            copyText(text, id) {
                if (navigator.clipboard) {
                    navigator.clipboard.writeText(text);
                }
            },

            handleFileSelect(event) {
                const file = event.target.files[0];
                if (file) {
                    this.selectedFileName = `📄 ${file.name} (${(file.size / 1024).toFixed(1)} KB)`;
                } else {
                    this.selectedFileName = '';
                }
            },

            insertTag(tag) {
                const textarea = document.getElementById('messageBox');
                const start = textarea.selectionStart;
                const end = textarea.selectionEnd;
                this.messageText = this.messageText.substring(0, start) + tag + this.messageText.substring(end);
                this.$nextTick(() => {
                    textarea.focus();
                    textarea.selectionStart = textarea.selectionEnd = start + tag.length;
                });
            }
        }
    }
</script>
@endpush
@endsection
