@extends('layouts.admin')

@section('title', 'Lembar Penilaian - ' . $competition->name)
@section('page_title', 'Lembar Penilaian Juri Digital')

@section('content')
<div class="space-y-8" x-data="scoringApp()">
    
    <!-- Top Header -->
    <div class="ai-card rounded-3xl p-6 sm:p-8 border border-white/[0.08] shadow-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="space-y-2">
            <div class="flex items-center gap-2">
                <span class="px-3 py-1 text-xs font-black uppercase tracking-wider rounded-xl bg-[#7A5AF8]/15 text-[#A594FD] border border-[#7A5AF8]/30">
                    {{ $competition->category->name }}
                </span>
                @if($competition->is_live_score)
                    <span class="px-2.5 py-1 text-[11px] font-black rounded-xl bg-emerald-500/15 text-emerald-400 border border-emerald-500/30 inline-flex items-center gap-1.5 shadow-sm shadow-emerald-500/20">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                        LIVE SCORE AKTIF
                    </span>
                @else
                    <span class="px-2.5 py-1 text-[11px] font-bold rounded-xl bg-amber-500/15 text-amber-300 border border-amber-500/30 inline-flex items-center gap-1.5">
                        <i data-lucide="lock" class="w-3.5 h-3.5"></i>
                        MODE RAHASIA (TERTUTUP)
                    </span>
                @endif
            </div>
            <h2 class="text-2xl sm:text-3xl font-black text-white tracking-tight font-display">{{ $competition->name }}</h2>
            <p class="text-xs text-slate-400">
                Dewan Juri: <strong class="text-[#84D0FF]">{{ $user->name }}</strong> • Urutan Tampil: <strong class="text-white font-mono">{{ $participants->count() }} Peserta Sah</strong>
            </p>
        </div>

        <div class="flex items-center gap-3">
            @if($competition->is_live_score)
            <a href="{{ route('live.scoreboard', $competition->slug) }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-gradient-to-r from-amber-400 to-amber-500 hover:from-amber-500 hover:to-amber-600 text-slate-950 font-black text-xs shadow-lg shadow-amber-400/20 transition">
                <i data-lucide="tv" class="w-4 h-4"></i>
                <span>Lihat Scoreboard Live</span>
            </a>
            @endif
            <a href="{{ url()->previous() }}" class="px-4 py-2.5 rounded-2xl bg-white/[0.06] hover:bg-white/[0.12] text-slate-300 hover:text-white font-bold text-xs border border-white/[0.08] hover:border-white/[0.15] transition flex items-center gap-1.5">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                <span>Kembali</span>
            </a>
        </div>
    </div>

    <!-- Participants Scoring Grid / Accordion -->
    <div class="space-y-6">
        <div class="flex items-center justify-between px-2">
            <h3 class="text-xs font-black uppercase tracking-wider text-slate-400 flex items-center gap-2">
                <i data-lucide="list-ordered" class="w-4 h-4 text-[#84D0FF]"></i>
                <span>Daftar Peserta (Diurutkan Berdasarkan Nomor Undian Tampil)</span>
            </h3>
            <span class="text-xs font-mono font-bold text-slate-500">
                {{ $participants->count() }} Peserta
            </span>
        </div>

        <div class="space-y-4">
            @forelse($participants as $index => $reg)
                @php
                    $existingScore = $existingScores->get($reg->id);
                    $isLocked = $existingScore ? $existingScore->is_locked : false;
                @endphp

                <div class="ai-card rounded-3xl border {{ $isLocked ? 'border-emerald-500/40 shadow-xl shadow-emerald-500/5' : 'border-white/[0.08] hover:border-white/[0.15] shadow-xl' }} p-6 sm:p-7 space-y-6 transition-all duration-200" x-data="{ expanded: {{ $index === 0 ? 'true' : 'false' }} }">
                    
                    <!-- Card Header / Summary -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 cursor-pointer select-none" @click="expanded = !expanded">
                        <div class="flex items-center gap-4">
                            <!-- Draw Number Badge -->
                            <div class="w-14 h-14 rounded-2xl {{ $reg->draw_number ? 'bg-gradient-to-tr from-amber-400 to-amber-500 text-slate-950 font-black text-xl shadow-lg shadow-amber-400/25' : 'bg-[#0C111D] border border-white/[0.08] text-slate-500 font-black text-lg' }} flex items-center justify-center shrink-0 font-mono">
                                {{ $reg->draw_number ? '#' . $reg->draw_number : '?' }}
                            </div>

                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-mono text-xs font-black text-[#84D0FF] bg-[#4E6EFF]/15 px-2.5 py-0.5 rounded-lg border border-[#4E6EFF]/30">
                                        {{ $reg->participant_number ?? $reg->registration_code }}
                                    </span>
                                    <span class="text-xs font-bold text-slate-500">Giliran #{{ $index + 1 }}</span>
                                </div>
                                <h4 class="text-lg font-black text-white mt-1 font-display">{{ $reg->display_name }}</h4>
                                <p class="text-xs text-slate-400 flex items-center gap-1 mt-0.5">
                                    <i data-lucide="building-2" class="w-3 h-3 text-slate-500"></i>
                                    <span>{{ $reg->institution_name }}</span>
                                </p>
                            </div>
                        </div>

                        <!-- Score Preview & Toggle -->
                        <div class="flex items-center gap-4">
                            @if($existingScore)
                                <div class="text-right">
                                    <span class="text-2xl font-black font-mono {{ $isLocked ? 'text-emerald-400' : 'text-amber-400' }}">
                                        {{ number_format($existingScore->total_score, 2) }}
                                    </span>
                                    <span class="text-[10px] font-bold uppercase tracking-wider block {{ $isLocked ? 'text-emerald-400' : 'text-amber-300' }}">
                                        {{ $isLocked ? '🔒 Nilai Terkunci' : '📝 Draft' }}
                                    </span>
                                </div>
                            @else
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-white/[0.04] text-slate-400 border border-white/[0.08]">
                                    Belum Dinilai
                                </span>
                            @endif

                            <div class="w-9 h-9 rounded-xl bg-white/[0.06] hover:bg-white/[0.12] text-slate-300 border border-white/[0.08] flex items-center justify-center transition">
                                <i data-lucide="chevron-down" class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': expanded }"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Scoring Form (Expanded Body) -->
                    <div x-show="expanded" x-transition class="pt-6 border-t border-white/[0.08]">
                        <form action="{{ route('juri.score.store', [$competition->id, $reg->id]) }}" method="POST" class="space-y-6">
                            @csrf

                            <!-- Criteria Scores Grid -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach($competition->criteria as $criterion)
                                    @php
                                        $val = '';
                                        if ($existingScore && $existingScore->details) {
                                            $detail = $existingScore->details->firstWhere('criterion_id', $criterion->id);
                                            $val = $detail ? $detail->score_value : '';
                                        }
                                    @endphp
                                    <div class="p-5 rounded-2xl bg-[#0C111D]/90 border border-white/[0.08] hover:border-[#7A5AF8]/30 transition space-y-2">
                                        <div class="flex items-center justify-between">
                                            <label class="text-xs font-bold text-slate-200">{{ $criterion->name }}</label>
                                            <span class="text-[10px] font-mono font-black text-[#A594FD] bg-[#7A5AF8]/15 px-2 py-0.5 rounded-lg border border-[#7A5AF8]/30">
                                                Bobot {{ $criterion->weight_percentage }}%
                                            </span>
                                        </div>
                                        <p class="text-[11px] text-slate-400">{{ $criterion->description ?? 'Rentang 0 - 100' }}</p>
                                        
                                        <div class="pt-1">
                                            <input type="number" step="0.1" min="0" max="100" name="criteria[{{ $criterion->id }}]" value="{{ old('criteria.' . $criterion->id, $val) }}" required placeholder="0.0" class="block w-full px-4 py-3 rounded-xl border border-white/[0.12] focus:border-[#7A5AF8] focus:ring-2 focus:ring-[#7A5AF8]/30 font-mono font-black text-2xl text-amber-300 bg-slate-950/80 outline-none transition placeholder:text-slate-700">
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Notes / Feedback Input -->
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">Catatan / Ulasan Dewan Juri</label>
                                <textarea name="notes" rows="2" placeholder="Tuliskan catatan teknis, saran penampilan, atau evaluasi peserta..." class="block w-full px-4 py-3 rounded-xl border border-white/[0.12] focus:border-[#7A5AF8] focus:ring-2 focus:ring-[#7A5AF8]/30 text-xs text-slate-200 bg-slate-950/80 placeholder:text-slate-600 outline-none transition">{{ old('notes', $existingScore?->notes) }}</textarea>
                            </div>

                            <!-- Lock Checkbox & Submit Buttons -->
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pt-2">
                                <label class="flex items-center gap-3 cursor-pointer text-xs font-bold text-amber-300 bg-amber-500/10 hover:bg-amber-500/15 px-4 py-3 rounded-2xl border border-amber-500/30 transition">
                                    <input type="checkbox" name="is_locked" value="1" {{ ($existingScore && $existingScore->is_locked) ? 'checked' : '' }} class="w-4 h-4 rounded text-amber-500 focus:ring-amber-400 bg-slate-950 border-amber-500/40">
                                    <span>Kunci Nilai Ini (Finalisasi & Publish ke Scoreboard)</span>
                                </label>

                                <button type="submit" class="gradient-btn px-8 py-3 rounded-2xl text-white font-black text-xs tracking-wider uppercase shadow-lg shadow-[#7A5AF8]/25 hover:scale-[1.01] transition flex items-center justify-center gap-2 cursor-pointer">
                                    <i data-lucide="save" class="w-4 h-4 text-white"></i>
                                    <span>Simpan Nilai Peserta</span>
                                </button>
                            </div>

                        </form>
                    </div>

                </div>
            @empty
                <div class="ai-card p-12 rounded-3xl border border-white/[0.08] text-center text-slate-500 text-xs">
                    Belum ada peserta yang terverifikasi untuk cabang lomba ini.
                </div>
            @endforelse
        </div>
    </div>

</div>

@push('scripts')
<script>
    function scoringApp() {
        return {
            // Future real-time touch calculations if needed
        }
    }
</script>
@endpush
@endsection
