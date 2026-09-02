@extends('layouts.admin')

@section('title', 'Master Dashboard Super Admin')
@section('page_title', 'Dashboard Overview')

@section('content')
<div class="space-y-6">
    
    <!-- AIStarterKit Dashboard Header Bar -->
    <div class="ai-card rounded-3xl p-4 sm:px-6 sm:py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-xl">
        <div class="space-y-0.5">
            <div class="flex items-center gap-2">
                <span class="p-1 rounded-lg bg-[#7A5AF8]/20 text-[#A594FD] border border-[#7A5AF8]/30">
                    <i data-lucide="sparkles" class="w-4 h-4"></i>
                </span>
                <h2 class="text-base sm:text-lg font-black tracking-tight text-white ai-gradient-text">Pusat Kendali & Analitik Super Admin</h2>
            </div>
            <p class="text-xs text-slate-400">Statistik pendaftaran, kuota cabang lomba, dan verifikasi berkas real-time.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5 shrink-0">
            <a href="{{ route('admin.competitions') }}" class="gradient-btn inline-flex items-center gap-1.5 px-4 py-2 rounded-2xl font-bold text-xs shadow-lg shadow-[#7A5AF8]/25 transition cursor-pointer">
                <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                <span>Cabang Lomba</span>
            </a>
            <a href="{{ route('pic.dashboard') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-2xl bg-white/[0.05] hover:bg-white/[0.1] text-slate-200 border border-white/[0.1] font-bold text-xs transition cursor-pointer">
                <i data-lucide="users" class="w-3.5 h-3.5 text-[#4E6EFF]"></i>
                <span>Data Peserta</span>
            </a>
            <a href="{{ route('admin.recap') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-2xl bg-white/[0.05] hover:bg-white/[0.1] text-slate-200 border border-white/[0.1] font-bold text-xs transition cursor-pointer">
                <i data-lucide="bar-chart-3" class="w-3.5 h-3.5 text-[#FF58D5]"></i>
                <span>Rekapitulasi</span>
            </a>
        </div>
    </div>

    <!-- AIStarterKit KPI Stat Cards Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Card 1: Total Cabang Lomba (AI Purple Accent) -->
        <div class="ai-card p-5 rounded-3xl space-y-2 hover:border-[#7A5AF8]/50 transition duration-300 shadow-lg group">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Cabang Lomba</span>
                <div class="w-9 h-9 rounded-2xl bg-[#7A5AF8]/15 text-[#A594FD] border border-[#7A5AF8]/30 flex items-center justify-center group-hover:scale-110 transition">
                    <i data-lucide="medal" class="w-4 h-4"></i>
                </div>
            </div>
            <div>
                <div class="text-2xl sm:text-3xl font-black tracking-tight text-white">{{ $stats['total_competitions'] }}</div>
                <div class="flex items-center gap-1.5 mt-1 text-[11px] text-slate-400">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-500/15 text-emerald-400 border border-emerald-500/20 font-bold">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        Buka
                    </span>
                    <span>• {{ $categories->count() }} Kategori</span>
                </div>
            </div>
        </div>

        <!-- Card 2: Total Registrasi Peserta (Electric Blue Accent) -->
        <div class="ai-card p-5 rounded-3xl space-y-2 hover:border-[#4E6EFF]/50 transition duration-300 shadow-lg group">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Pendaftaran</span>
                <div class="w-9 h-9 rounded-2xl bg-[#4E6EFF]/15 text-[#84D0FF] border border-[#4E6EFF]/30 flex items-center justify-center group-hover:scale-110 transition">
                    <i data-lucide="users" class="w-4 h-4"></i>
                </div>
            </div>
            <div>
                <div class="text-2xl sm:text-3xl font-black tracking-tight text-white">{{ $stats['total_registrations'] }}</div>
                <div class="flex items-center gap-1.5 mt-1 text-[11px] text-slate-400">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-[#4E6EFF]/15 text-[#84D0FF] border border-[#4E6EFF]/20 font-bold">
                        {{ $stats['total_participants'] }} Akun
                    </span>
                    <span>Delegasi Masuk</span>
                </div>
            </div>
        </div>

        <!-- Card 3: Berkas Terverifikasi Sah (Emerald Accent) -->
        <div class="ai-card p-5 rounded-3xl space-y-2 hover:border-emerald-500/50 transition duration-300 shadow-lg group">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Lolos Verifikasi</span>
                <div class="w-9 h-9 rounded-2xl bg-emerald-500/15 text-emerald-400 border border-emerald-500/30 flex items-center justify-center group-hover:scale-110 transition">
                    <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                </div>
            </div>
            <div>
                <div class="text-2xl sm:text-3xl font-black tracking-tight text-emerald-400">{{ $stats['verified_registrations'] }}</div>
                <div class="flex items-center gap-1.5 mt-1 text-[11px] text-slate-400">
                    @php
                        $verRate = $stats['total_registrations'] > 0 ? round(($stats['verified_registrations'] / $stats['total_registrations']) * 100) : 0;
                    @endphp
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-emerald-500/15 text-emerald-400 border border-emerald-500/20 font-bold">
                        {{ $verRate }}% Sah
                    </span>
                    <span>Siap ID Card</span>
                </div>
            </div>
        </div>

        <!-- Card 4: Nilai Juri Terkunci (Magenta / Pink Accent) -->
        <div class="ai-card p-5 rounded-3xl space-y-2 hover:border-[#FF58D5]/50 transition duration-300 shadow-lg group">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Nilai Terkunci</span>
                <div class="w-9 h-9 rounded-2xl bg-[#FF58D5]/15 text-[#FFA0E7] border border-[#FF58D5]/30 flex items-center justify-center group-hover:scale-110 transition">
                    <i data-lucide="lock" class="w-4 h-4"></i>
                </div>
            </div>
            <div>
                <div class="text-2xl sm:text-3xl font-black tracking-tight text-[#FFA0E7]">{{ $stats['scores_entered'] }}</div>
                <div class="flex items-center gap-1.5 mt-1 text-[11px] text-slate-400">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-amber-500/15 text-amber-400 border border-amber-500/20 font-bold">
                        {{ $stats['total_judges'] }} Juri
                    </span>
                    <span>Tabulasi Nilai</span>
                </div>
            </div>
        </div>

    </div>

    <!-- AI 2-Column Workspace Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- Left Column: Recent Registrations Table -->
        <div class="lg:col-span-8 ai-card rounded-3xl overflow-hidden shadow-xl">
            
            <!-- Card Header -->
            <div class="p-5 sm:p-6 border-b border-white/[0.08] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="space-y-0.5">
                    <div class="flex items-center gap-2">
                        <h3 class="text-base font-bold tracking-tight text-white">Pendaftaran Masuk Terbaru</h3>
                        <span class="px-2.5 py-0.5 rounded-full bg-white/[0.06] text-slate-300 text-[10px] font-bold border border-white/[0.08]">
                            {{ $recentRegistrations->count() }} Data
                        </span>
                    </div>
                    <p class="text-xs text-slate-400">Aktivitas pendaftaran peserta mandiri & batch kolektif terbaru</p>
                </div>
                <a href="{{ route('admin.invoices.index') }}" class="text-xs font-bold text-[#7A5AF8] hover:text-[#9B82FC] transition inline-flex items-center gap-1">
                    <span>Lihat Semua Data</span>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="bg-[#0C111D]/90 text-[11px] font-bold uppercase tracking-wider text-slate-400 border-b border-white/[0.08]">
                        <tr>
                            <th class="py-3.5 px-4 sm:px-5">Peserta / Tim</th>
                            <th class="py-3.5 px-4">Cabang Lomba</th>
                            <th class="py-3.5 px-4">Kode Reg</th>
                            <th class="py-3.5 px-4">Asal Sekolah</th>
                            <th class="py-3.5 px-4 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/[0.04] font-medium">
                        @forelse($recentRegistrations as $reg)
                            @php
                                $categoryName = $reg->competition->category->name ?? 'Lomba';
                            @endphp
                            <tr class="hover:bg-white/[0.025] transition">
                                <td class="py-3.5 px-4 sm:px-5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-[#7A5AF8]/30 to-[#4E6EFF]/30 text-white flex items-center justify-center font-bold text-xs shrink-0 border border-white/[0.1]">
                                            {{ strtoupper(substr($reg->display_name ?? 'P', 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-white text-xs sm:text-sm">{{ $reg->display_name }}</div>
                                            <div class="text-[11px] text-slate-400">
                                                {{ $reg->members->count() }} Anggota • <span class="capitalize">{{ $reg->competition->type }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-bold bg-white/[0.05] text-slate-200 border border-white/[0.08]">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#7A5AF8]"></span>
                                        <span>{{ $reg->competition->name }}</span>
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 font-mono text-[11px] font-bold text-[#84D0FF]">
                                    {{ $reg->registration_code }}
                                </td>
                                <td class="py-3.5 px-4 text-slate-300">
                                    {{ $reg->institution_name }}
                                </td>
                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                    @if($reg->status === 'verified')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                            <span>Verified</span>
                                        </span>
                                    @elseif($reg->status === 'revision')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-500/15 text-amber-400 border border-amber-500/30">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                                            <span>Revision</span>
                                        </span>
                                    @elseif($reg->status === 'rejected')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-500/15 text-rose-400 border border-rose-500/30">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span>
                                            <span>Rejected</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-white/[0.08] text-slate-300 border border-white/[0.1]">
                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                            <span>Pending</span>
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-10 text-center text-slate-400">
                                    <div class="flex flex-col items-center justify-center space-y-2">
                                        <i data-lucide="inbox" class="w-8 h-8 text-slate-500"></i>
                                        <p class="text-xs font-semibold">Belum ada pendaftaran masuk.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>

        <!-- Right Column: Quota Progress & Quick Management -->
        <div class="lg:col-span-4 space-y-6">
            
            <!-- Quota Monitoring Card -->
            <div class="ai-card rounded-3xl p-5 sm:p-6 space-y-4 shadow-xl">
                <div class="flex items-center justify-between border-b border-white/[0.08] pb-3.5">
                    <div>
                        <h4 class="text-sm font-bold text-white tracking-tight">Kapasitas Kuota Lomba</h4>
                        <p class="text-[11px] text-slate-400">Keterisian peserta per cabang lomba</p>
                    </div>
                    <a href="{{ route('admin.competitions') }}" class="p-2 rounded-xl text-slate-400 hover:text-white hover:bg-white/[0.06] transition" title="Kelola Cabang Lomba">
                        <i data-lucide="settings-2" class="w-4 h-4"></i>
                    </a>
                </div>

                <div class="space-y-4 max-h-[340px] overflow-y-auto pr-1">
                    @foreach($competitions as $comp)
                        @php
                            $isUnlimited = $comp->isUnlimitedQuota();
                            $quota = $comp->quota ?: 1;
                            $count = $comp->registrations_count;
                            $percentage = $isUnlimited ? 100 : min(100, round(($count / $quota) * 100));
                        @endphp
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold text-slate-200 truncate max-w-[170px]">{{ $comp->name }}</span>
                                <span class="font-mono text-[11px] text-slate-400 font-semibold">
                                    @if($isUnlimited)
                                        <strong class="text-white">{{ $count }}</strong> / <span class="text-purple-300 font-bold">∞</span>
                                    @else
                                        <strong class="text-white">{{ $count }}</strong> / {{ $comp->quota }}
                                    @endif
                                </span>
                            </div>
                            <div class="w-full bg-[#0C111D] border border-white/[0.06] rounded-full h-2 overflow-hidden">
                                <div class="h-2 rounded-full transition-all duration-500 {{ $isUnlimited ? 'bg-gradient-to-r from-[#7A5AF8] to-[#4E6EFF] opacity-70' : ($percentage >= 90 ? 'bg-gradient-to-r from-amber-500 to-rose-500' : 'bg-gradient-to-r from-[#7A5AF8] to-[#4E6EFF]') }}" style="width: {{ $percentage }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
