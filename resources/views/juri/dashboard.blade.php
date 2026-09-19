@extends('layouts.admin')

@php
    $hasSports = $competitions->contains(fn($c) => $c->isSports());
    $hasNonSports = $competitions->contains(fn($c) => !$c->isSports());
    
    $portalTitle = ($hasSports && !$hasNonSports) ? 'Portal Penugasan Wasit' : ((!$hasSports && $hasNonSports) ? 'Portal Penilaian Dewan Juri' : 'Portal Dewan Juri & Wasit');
    $pageTitle = ($hasSports && !$hasNonSports) ? 'Dashboard Wasit Olahraga' : ((!$hasSports && $hasNonSports) ? 'Dashboard Dewan Juri' : 'Dashboard Dewan Juri & Wasit');
@endphp

@section('title', $pageTitle)
@section('page_title', $portalTitle)

@section('content')
<div class="space-y-6">

    <!-- Assigned Competitions (AIStarterKit Design) -->
    <div class="ai-card rounded-3xl p-6 sm:p-8 border border-white/[0.08] shadow-xl space-y-6">
        <div class="flex items-center justify-between border-b border-white/[0.08] pb-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="p-1 rounded-lg bg-[#7A5AF8]/20 text-[#A594FD] border border-[#7A5AF8]/30">
                        <i data-lucide="award" class="w-4 h-4"></i>
                    </span>
                    <h3 class="text-lg font-black text-white ai-gradient-text">Cabang Perlombaan yang Ditugaskan</h3>
                </div>
                <p class="text-xs text-slate-400 mt-1">Pilih cabang lomba untuk memulai proses input skor atau nilai</p>
            </div>
            <span class="text-xs font-bold px-3 py-1 rounded-full bg-white/[0.06] text-slate-300 border border-white/[0.08]">
                Total: {{ $competitions->count() }} Cabang
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @forelse($competitions as $comp)
                @php
                    $isSports = $comp->isSports();
                    $compCode = strtoupper($comp->code ?? '');
                    $isBadminton = ($compCode === 'BLT' || str_contains(strtolower($comp->name ?? ''), 'bulu tangkis') || str_contains(strtolower($comp->name ?? ''), 'badminton'));
                    $isMipa = ($compCode === 'MIPA' || str_contains(strtolower($comp->name ?? ''), 'mipa'));
                    $isTmjCtr = in_array($compCode, ['TMJ', 'CTR']) || str_contains(strtolower($comp->name ?? ''), 'tenis meja') || str_contains(strtolower($comp->name ?? ''), 'catur');
                @endphp
                <div class="bg-[#0C111D]/80 border border-white/[0.08] rounded-3xl p-6 hover:border-[#7A5AF8]/50 hover:shadow-xl transition duration-300 space-y-4 flex flex-col justify-between group">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-1.5">
                                <span class="px-3 py-1 text-xs font-bold rounded-lg bg-[#7A5AF8]/15 text-[#A594FD] border border-[#7A5AF8]/30">
                                    {{ $comp->category->name }}
                                </span>
                                <span class="px-2 py-0.5 text-[10px] font-black uppercase tracking-wider rounded-md {{ $isSports ? 'bg-amber-500/15 text-amber-300 border border-amber-500/30' : 'bg-purple-500/15 text-purple-300 border border-purple-500/30' }}">
                                    {{ $isSports ? '🏁 Wasit' : '⚖️ Dewan Juri' }}
                                </span>
                            </div>
                            <span class="text-xs font-bold text-emerald-400 bg-emerald-500/15 border border-emerald-500/30 px-2.5 py-0.5 rounded-full">
                                {{ $comp->registrations->where('status', 'verified')->count() }} Peserta Sah
                            </span>
                        </div>

                        <h4 class="text-lg font-black text-white group-hover:text-[#A594FD] transition">{{ $comp->name }}</h4>

                        <!-- Criteria summary -->
                        <div class="mt-4 pt-3 border-t border-white/[0.06] space-y-1.5">
                            @if($isBadminton)
                                <span class="text-[11px] font-bold text-emerald-400 uppercase tracking-wider block flex items-center gap-1">
                                    <i data-lucide="activity" class="w-3.5 h-3.5"></i>
                                    Format Pertandingan & Tugas Wasit:
                                </span>
                                <div class="flex flex-wrap gap-1.5">
                                    <span class="px-2.5 py-1 rounded-xl bg-emerald-500/10 border border-emerald-500/25 text-xs font-semibold text-emerald-300 inline-flex items-center gap-1.5">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                        Sistem Reli & Poin Wasit Digital (21 Poin / Set)
                                    </span>
                                </div>
                            @elseif($isMipa)
                                <span class="text-[11px] font-bold text-[#84D0FF] uppercase tracking-wider block flex items-center gap-1">
                                    <i data-lucide="file-check" class="w-3.5 h-3.5"></i>
                                    Format Penilaian Akademik:
                                </span>
                                <div class="flex flex-wrap gap-1.5">
                                    <span class="px-2.5 py-1 rounded-xl bg-blue-500/10 border border-blue-500/25 text-xs font-semibold text-[#84D0FF] inline-flex items-center gap-1.5">
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-400"></span>
                                        Skor Ujian Objektif / CBT
                                    </span>
                                    @foreach($comp->criteria as $crit)
                                        <span class="px-2.5 py-1 rounded-lg bg-white/[0.04] border border-white/[0.08] text-[11px] font-semibold text-slate-300">
                                            {{ $crit->name }} ({{ $crit->weight_percentage }}%)
                                        </span>
                                    @endforeach
                                </div>
                            @elseif($isTmjCtr)
                                <span class="text-[11px] font-bold text-amber-400 uppercase tracking-wider block flex items-center gap-1">
                                    <i data-lucide="trophy" class="w-3.5 h-3.5"></i>
                                    Format Pertandingan & Tugas Wasit:
                                </span>
                                <div class="flex flex-wrap gap-1.5">
                                    <span class="px-2.5 py-1 rounded-xl bg-amber-500/10 border border-amber-500/25 text-xs font-semibold text-amber-300 inline-flex items-center gap-1.5">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                                        {{ $compCode === 'CTR' ? 'Poin Match Kemenangan & Taktik' : 'Poin Game & Skor Match' }}
                                    </span>
                                    @foreach($comp->criteria as $crit)
                                        <span class="px-2.5 py-1 rounded-lg bg-white/[0.04] border border-white/[0.08] text-[11px] font-semibold text-slate-300">
                                            {{ $crit->name }} ({{ $crit->weight_percentage }}%)
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-[11px] font-bold text-[#A594FD] uppercase tracking-wider block flex items-center gap-1">
                                    <i data-lucide="award" class="w-3.5 h-3.5"></i>
                                    Kriteria Bobot Penilaian Dewan Juri:
                                </span>
                                <div class="flex flex-wrap gap-1.5">
                                    @forelse($comp->criteria as $crit)
                                        <span class="px-2.5 py-1 rounded-lg bg-white/[0.04] border border-white/[0.08] text-[11px] font-semibold text-slate-300">
                                            {{ $crit->name }} ({{ $crit->weight_percentage }}%)
                                        </span>
                                    @empty
                                        <span class="text-xs text-slate-500 italic">Penilaian Umum (100%)</span>
                                    @endforelse
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Action Button -->
                    <div class="pt-4 border-t border-white/[0.08] flex flex-wrap items-center gap-2">
                        @if($isBadminton)
                            <a href="{{ route('badminton.index') }}" class="flex-1 py-3 px-4 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-slate-950 font-black text-xs shadow-lg shadow-emerald-500/25 transition flex items-center justify-center gap-2 cursor-pointer">
                                <i data-lucide="activity" class="w-4 h-4 text-slate-950"></i>
                                <span>Buka Panel Wasit Bulu Tangkis</span>
                            </a>
                            <a href="{{ route('badminton.scoreboard') }}" target="_blank" class="py-3 px-3 rounded-xl bg-rose-500/15 hover:bg-rose-500/25 text-rose-300 border border-rose-500/30 text-xs font-bold transition flex items-center gap-1.5 shrink-0" title="Buka Papan Skor LED TV">
                                <i data-lucide="tv" class="w-4 h-4 text-rose-400"></i>
                                <span class="hidden sm:inline">Papan Skor TV</span>
                            </a>
                        @elseif($isMipa)
                            <a href="{{ route('juri.scoring', $comp->id) }}" class="gradient-btn w-full text-center py-3 px-4 rounded-xl text-white font-bold text-xs shadow-lg shadow-[#7A5AF8]/25 transition flex items-center justify-center gap-2">
                                <i data-lucide="file-edit" class="w-4 h-4"></i>
                                <span>Input / Rekap Nilai CBT</span>
                            </a>
                        @elseif($isTmjCtr)
                            <a href="{{ route('juri.scoring', $comp->id) }}" class="px-4 py-3 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-slate-950 font-black text-xs shadow-lg shadow-amber-500/25 transition flex items-center justify-center gap-2 w-full cursor-pointer">
                                <i data-lucide="clipboard-pen" class="w-4 h-4 text-slate-950"></i>
                                <span>Input Skor Pertandingan (Wasit)</span>
                            </a>
                        @else
                            <a href="{{ route('juri.scoring', $comp->id) }}" class="gradient-btn w-full text-center py-3 px-4 rounded-xl text-white font-bold text-xs shadow-lg shadow-[#7A5AF8]/25 transition flex items-center justify-center gap-2">
                                <i data-lucide="clipboard-pen" class="w-4 h-4"></i>
                                <span>Buka Lembar Penilaian Dewan Juri</span>
                            </a>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-12 px-4 rounded-2xl bg-[#0C111D] border border-white/[0.06] space-y-3">
                    <div class="w-12 h-12 rounded-2xl bg-[#7A5AF8]/15 text-[#A594FD] border border-[#7A5AF8]/30 flex items-center justify-center mx-auto">
                        <i data-lucide="award" class="w-6 h-6"></i>
                    </div>
                    <div class="space-y-1">
                        <h4 class="text-sm font-bold text-white">Belum Ada Cabang Lomba Ditugaskan</h4>
                        <p class="text-xs text-slate-400 max-w-md mx-auto">Akun dewan juri/wasit Anda saat ini belum ditugaskan ke cabang perlombaan manapun. Silakan hubungi Super Administrator untuk penugasan cabang lomba.</p>
                    </div>
                </div>
            @endforelse
        </div>
    </div>

</div>
@endsection
