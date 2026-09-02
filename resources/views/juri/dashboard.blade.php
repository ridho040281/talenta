@extends('layouts.admin')

@section('title', 'Dashboard Dewan Juri')
@section('page_title', 'Portal Penilaian Dewan Juri')

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
                <p class="text-xs text-slate-400 mt-1">Pilih cabang lomba untuk memulai proses input nilai</p>
            </div>
            <span class="text-xs font-bold px-3 py-1 rounded-full bg-white/[0.06] text-slate-300 border border-white/[0.08]">
                Total: {{ $competitions->count() }} Lomba
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @forelse($competitions as $comp)
                <div class="bg-[#0C111D]/80 border border-white/[0.08] rounded-3xl p-6 hover:border-[#7A5AF8]/50 hover:shadow-xl transition duration-300 space-y-4 flex flex-col justify-between group">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="px-3 py-1 text-xs font-bold rounded-lg bg-[#7A5AF8]/15 text-[#A594FD] border border-[#7A5AF8]/30">
                                {{ $comp->category->name }}
                            </span>
                            <span class="text-xs font-bold text-emerald-400 bg-emerald-500/15 border border-emerald-500/30 px-2.5 py-0.5 rounded-full">
                                {{ $comp->registrations->where('status', 'verified')->count() }} Peserta Sah
                            </span>
                        </div>

                        <h4 class="text-lg font-black text-white group-hover:text-[#A594FD] transition">{{ $comp->name }}</h4>
                        
                        <!-- Criteria summary -->
                        <div class="mt-4 pt-3 border-t border-white/[0.06] space-y-1.5">
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Kriteria Bobot Penilaian:</span>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach($comp->criteria as $crit)
                                    <span class="px-2.5 py-1 rounded-lg bg-white/[0.04] border border-white/[0.08] text-[11px] font-semibold text-slate-300">
                                        {{ $crit->name }} ({{ $crit->weight_percentage }}%)
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Action Button -->
                    <div class="pt-4 border-t border-white/[0.08]">
                        <a href="{{ route('juri.scoring', $comp->id) }}" class="gradient-btn w-full text-center py-3 px-4 rounded-xl text-white font-bold text-xs shadow-lg shadow-[#7A5AF8]/25 transition flex items-center justify-center gap-2">
                            <i data-lucide="clipboard-pen" class="w-4 h-4"></i>
                            <span>Buka Lembar Penilaian Digital</span>
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-2 text-center py-12 text-slate-500">
                    Belum ada penugasan cabang lomba untuk akun juri Anda.
                </div>
            @endforelse
        </div>
    </div>

</div>
@endsection
