@extends('layouts.admin')

@php
    $isSports = $competition->isSports();
    $compCode = strtoupper($competition->code ?? '');
    $isStageSupported = in_array($compCode, ['MTQ', 'THF', 'TFID', 'POP']) 
        || str_contains(strtolower($competition->name ?? ''), 'mtq') 
        || str_contains(strtolower($competition->name ?? ''), 'tahfid') 
        || str_contains(strtolower($competition->name ?? ''), 'pop');
@endphp

@section('title', 'Input Penilaian Multi-Juri — ' . $competition->name)
@section('page_title', 'Input Penilaian Multi-Juri')

@section('content')
<div class="space-y-6 pb-16" x-data="multiJudgeScoringApp(
    @js($competition),
    @js($competition->judges->map(fn($j) => ['id' => $j->id, 'name' => $j->name, 'role_title' => $j->pivot->role_title ?? 'Dewan Juri'])),
    @js($competition->criteria),
    @js($participants->map(fn($r) => [
        'id' => $r->id,
        'name' => $r->pure_name,
        'institution' => $r->display_school ?: ($r->institution_name ?: '-'),
        'draw_number' => $r->draw_number,
        'participant_number' => $r->participant_number ?: $r->registration_code,
        'gender' => $r->primary_gender,
        'target_class' => $r->target_class ?: '-',
        'match_type' => $r->match_type ?: '-',
        'chosen_song' => $r->chosen_song ?: '',
    ])),
    @js($scoresMap)
)" x-init="initApp()" @keydown.window="handleGlobalKey($event)">

    <!-- =========================================================================
         TOP HERO HEADER BANNER
         ========================================================================= -->
    <div class="ai-card rounded-3xl p-6 sm:p-8 border border-white/[0.08] shadow-2xl relative overflow-hidden">
        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            
            <div class="space-y-2">
                <div class="flex items-center gap-2 flex-wrap">
                    <a href="{{ auth()->user()->role === 'superadmin' ? route('admin.juri.wasit') : route('pic.dashboard') }}" 
                       class="p-2 rounded-xl bg-white/[0.06] hover:bg-white/[0.12] text-slate-300 hover:text-white border border-white/[0.08] transition inline-flex items-center gap-1 text-xs">
                        <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                        <span>Dashboard</span>
                    </a>
                    <span class="text-[10px] font-black uppercase tracking-wider text-[#84D0FF] bg-[#4E6EFF]/20 border border-[#4E6EFF]/30 px-2.5 py-0.5 rounded-lg">
                        OPERATOR INPUT PENILAIAN MULTI-JURI
                    </span>
                    <span class="text-xs font-mono font-black text-amber-300 bg-amber-500/15 border border-amber-500/30 px-2 py-0.5 rounded-lg">
                        {{ $competition->code }}
                    </span>
                    @if($competition->is_live_score)
                        <span class="text-[10px] font-black uppercase text-emerald-300 bg-emerald-500/15 border border-emerald-500/30 px-2 py-0.5 rounded-lg flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            Live Score Aktif
                        </span>
                    @endif
                </div>

                <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight font-display mt-1">
                    {{ $competition->name }}
                </h1>
                
                <p class="text-xs sm:text-sm text-slate-400 max-w-2xl leading-relaxed">
                    Panel input terpadu untuk memasukkan nilai dari lembar form fisik <strong class="text-white" x-text="judges.length + ' Dewan Juri'"></strong> sekaligus dalam satu layar per peserta secara cepat dan akurat.
                </p>
            </div>

            <!-- Action Buttons Launch Bar -->
            <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                <!-- Manage Judges Button -->
                <button type="button" 
                        @click="openJudgesModal()"
                        class="px-4 py-2.5 rounded-2xl bg-indigo-500/20 hover:bg-indigo-500/30 text-indigo-300 border border-indigo-500/40 text-xs font-black shadow-lg shadow-indigo-500/10 transition flex items-center gap-2 cursor-pointer">
                    <i data-lucide="users" class="w-4 h-4 text-indigo-400"></i>
                    <span>Kelola Nama Juri</span>
                    <span class="px-2 py-0.5 rounded-full bg-indigo-500/30 text-white font-mono text-[10px]" x-text="judges.length + ' Juri'"></span>
                </button>

                <!-- Export Excel Button in Hero Header -->
                <button type="button" 
                        @click="exportRecapXls()"
                        class="px-4 py-2.5 rounded-2xl bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-300 border border-emerald-500/40 text-xs font-black shadow-lg shadow-emerald-500/10 transition flex items-center gap-2 cursor-pointer">
                    <i data-lucide="file-spreadsheet" class="w-4 h-4 text-emerald-400"></i>
                    <span>Export Excel (.xlsx)</span>
                </button>
            </div>

        </div>

        <!-- STATS & PROGRESS BAR -->
        <div class="mt-6 pt-5 border-t border-white/[0.08] grid grid-cols-1 sm:grid-cols-3 gap-4 items-center">
            <div class="space-y-1">
                <div class="flex items-center justify-between text-xs font-bold">
                    <span class="text-slate-400">Progres Penilaian Masuk:</span>
                    <span class="text-white font-mono" x-text="scoredCount + ' / ' + participants.length + ' Peserta (' + scoringPercentage + '%)'"></span>
                </div>
                <div class="w-full h-2.5 bg-[#0C111D] rounded-full overflow-hidden p-[1px] border border-white/[0.08]">
                    <div class="h-full rounded-full transition-all duration-500 bg-gradient-to-r from-emerald-500 via-[#4E6EFF] to-[#7A5AF8]" 
                         :style="'width: ' + scoringPercentage + '%'"></div>
                </div>
            </div>

            <div class="flex items-center justify-around sm:col-span-2 bg-slate-950/60 p-3 rounded-2xl border border-white/[0.06] text-xs">
                <div class="text-center">
                    <span class="text-[10px] uppercase font-bold text-slate-500 block">Total Peserta Sah</span>
                    <span class="text-base font-black font-mono text-white" x-text="participants.length"></span>
                </div>
                <div class="h-6 w-px bg-white/[0.08]"></div>
                <div class="text-center">
                    <span class="text-[10px] uppercase font-bold text-emerald-400 block">Lengkap Terkunci</span>
                    <span class="text-base font-black font-mono text-emerald-400" x-text="scoredCount"></span>
                </div>
                <div class="h-6 w-px bg-white/[0.08]"></div>
                <div class="text-center">
                    <span class="text-[10px] uppercase font-bold text-amber-400 block">Belum / Sebagian</span>
                    <span class="text-base font-black font-mono text-amber-400" x-text="participants.length - scoredCount"></span>
                </div>
                <div class="h-6 w-px bg-white/[0.08]"></div>
                <div class="text-center">
                    <span class="text-[10px] uppercase font-bold text-indigo-400 block">Dewan Juri</span>
                    <span class="text-base font-black font-mono text-indigo-300" x-text="judges.length + ' Juri'"></span>
                </div>
            </div>
        </div>
    </div>

    <!-- =========================================================================
         TAB NAVIGATION CONTROLS (Tab 1: Input Penilaian | Tab 2: Matriks Rekap Nilai)
         ========================================================================= -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <!-- Segmented Tab Pills -->
        <div class="inline-flex p-1.5 bg-slate-950/90 rounded-2xl border border-white/[0.08] shadow-xl">
            <button type="button" 
                    @click="switchTab('input')"
                    class="px-5 py-2.5 rounded-xl font-black text-xs transition-all flex items-center gap-2 cursor-pointer select-none"
                    :class="activeTab === 'input' ? 'bg-gradient-to-r from-[#7A5AF8] to-[#4E6EFF] text-white shadow-lg shadow-[#7A5AF8]/30 font-black' : 'text-slate-400 hover:text-white'">
                <i data-lucide="edit-3" class="w-4 h-4"></i>
                <span>1. Form Input Penilaian</span>
            </button>
            <button type="button" 
                    @click="switchTab('rekap')"
                    class="px-5 py-2.5 rounded-xl font-black text-xs transition-all flex items-center gap-2 cursor-pointer select-none"
                    :class="activeTab === 'rekap' ? 'bg-gradient-to-r from-[#7A5AF8] to-[#4E6EFF] text-white shadow-lg shadow-[#7A5AF8]/30 font-black' : 'text-slate-400 hover:text-white'">
                <i data-lucide="table-2" class="w-4 h-4"></i>
                <span>2. Matriks Rekap Nilai Lengkap</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold"
                      :class="activeTab === 'rekap' ? 'bg-white/20 text-white' : 'bg-white/[0.08] text-slate-400'"
                      x-text="scoredCount + '/' + participants.length"></span>
            </button>
        </div>

        <!-- External Quick Links -->
        <div class="flex items-center gap-2 flex-wrap">
            <button type="button" 
                    @click="exportRecapXls()"
                    class="px-4 py-2.5 rounded-2xl bg-emerald-500/15 hover:bg-emerald-500/25 text-emerald-300 border border-emerald-500/30 text-xs font-bold transition flex items-center gap-1.5 shadow-sm cursor-pointer">
                <i data-lucide="file-spreadsheet" class="w-4 h-4 text-emerald-400"></i>
                <span>Export Excel (.xlsx)</span>
            </button>
            <a href="{{ route('admin.berita-acara.index', ['competition_id' => $competition->id]) }}" 
               class="px-4 py-2.5 rounded-2xl bg-white/[0.06] hover:bg-white/[0.12] text-slate-200 hover:text-white border border-white/[0.08] text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                <i data-lucide="file-text" class="w-4 h-4 text-emerald-400"></i>
                <span>Berita Acara Cetak</span>
                <i data-lucide="arrow-up-right" class="w-3 h-3 opacity-60"></i>
            </a>
        </div>
    </div>

    <!-- =========================================================================
         TAB 1 CONTENT: FORM INPUT PENILAIAN PER PESERTA (ACCORDION)
         ========================================================================= -->
    <div x-show="activeTab === 'input'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6">
        
        <!-- SEARCH & FILTER TOOLBAR -->
        <div class="ai-panel p-4 rounded-2xl border border-white/[0.08] flex flex-col sm:flex-row items-center justify-between gap-3 shadow-lg">
            
            <!-- Search Input -->
            <div class="relative w-full sm:w-80">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                <input type="text" 
                       x-model="searchQuery" 
                       placeholder="Cari nama peserta, sekolah, #undian..." 
                       class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-950 border border-white/[0.12] text-xs font-semibold text-white focus:outline-none focus:border-[#7A5AF8] transition placeholder:text-slate-500">
            </div>

            <!-- Filter Segmented Controls -->
            <div class="flex items-center gap-2 flex-wrap w-full sm:w-auto justify-end">
                <!-- Sector Filter -->
                <div class="inline-flex p-1 bg-slate-950 rounded-xl border border-white/[0.08] text-xs">
                    <button type="button" 
                            @click="setSectorFilter('all')" 
                            class="px-3 py-1.5 rounded-lg font-bold transition cursor-pointer"
                            :class="sectorFilter === 'all' ? 'bg-[#7A5AF8] text-white shadow-xs' : 'text-slate-400 hover:text-white'">
                        Semua Sektor
                    </button>
                    <button type="button" 
                            @click="setSectorFilter('PA')" 
                            class="px-3 py-1.5 rounded-lg font-bold transition cursor-pointer"
                            :class="sectorFilter === 'PA' ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-400 hover:text-white'">
                        👦 Putra (PA)
                    </button>
                    <button type="button" 
                            @click="setSectorFilter('PI')" 
                            class="px-3 py-1.5 rounded-lg font-bold transition cursor-pointer"
                            :class="sectorFilter === 'PI' ? 'bg-pink-600 text-white shadow-xs' : 'text-slate-400 hover:text-white'">
                        👧 Putri (PI)
                    </button>
                </div>

                <!-- Status Filter -->
                <select x-model="statusFilter" 
                        class="px-3 py-2 rounded-xl bg-slate-950 border border-white/[0.12] text-xs font-bold text-slate-300 focus:outline-none focus:border-[#7A5AF8] cursor-pointer">
                    <option value="all">Semua Status</option>
                    <option value="unscored">⚪ Belum Dinilai Lengkap</option>
                    <option value="scored">🟢 Sudah Terkunci</option>
                </select>

                <!-- Tab 1 Export Excel Quick Button -->
                <button type="button" 
                        @click="exportRecapXls()"
                        class="px-3 py-2 rounded-xl bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-300 border border-emerald-500/40 text-xs font-bold transition flex items-center gap-1.5 cursor-pointer shadow-sm"
                        title="Download Data Rekap Nilai ke Format Excel (.xlsx)">
                    <i data-lucide="file-spreadsheet" class="w-3.5 h-3.5 text-emerald-400"></i>
                    <span>Export Excel</span>
                </button>
            </div>
        </div>

        <!-- PARTICIPANTS SCORING ACCORDION LIST -->
        <div class="space-y-4">
        
        <template x-for="(reg, index) in filteredParticipants" :key="reg.id">
            <div class="ai-card rounded-3xl border transition-all duration-300 overflow-hidden shadow-xl"
                 :class="{
                     'border-emerald-500/40 shadow-emerald-500/5 bg-slate-900/90': isRegFullyScored(reg.id),
                     'border-amber-500/40 shadow-amber-500/5 bg-slate-900/90': isRegPartiallyScored(reg.id),
                     'border-white/[0.08] hover:border-white/[0.15] bg-[#0C111D]/90': !isRegFullyScored(reg.id) && !isRegPartiallyScored(reg.id),
                     'ring-2 ring-[#7A5AF8]/60': activeParticipantId === reg.id
                 }">

                <!-- CARD HEADER: Clickable Summary Bar -->
                <div class="p-5 sm:p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4 cursor-pointer select-none transition hover:bg-white/[0.02]"
                     @click="toggleExpand(reg.id)">
                    
                    <div class="flex items-center gap-4 min-w-0">
                        <!-- Draw Number Badge -->
                        <div class="w-14 h-14 rounded-2xl flex flex-col items-center justify-center shrink-0 font-mono shadow-md"
                             :class="reg.draw_number ? 'bg-gradient-to-tr from-amber-400 to-amber-500 text-slate-950 font-black shadow-amber-400/20' : 'bg-slate-950 border border-white/[0.1] text-slate-500 font-bold'">
                            <span class="text-[9px] uppercase font-bold leading-none" x-text="reg.draw_number ? 'NO.' : 'UNDIAN'"></span>
                            <span class="text-xl font-black leading-none mt-0.5" x-text="reg.draw_number ? '#' + reg.draw_number : '?'"></span>
                        </div>

                        <!-- Name & School Info -->
                        <div class="min-w-0 space-y-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-mono text-[11px] font-black text-[#84D0FF] bg-[#4E6EFF]/15 border border-[#4E6EFF]/30 px-2 py-0.5 rounded">
                                    <span x-text="reg.participant_number"></span>
                                </span>
                                <span class="text-[11px] font-bold px-2 py-0.5 rounded"
                                      :class="reg.gender === 'P' ? 'bg-pink-500/15 text-pink-300 border border-pink-500/30' : 'bg-blue-500/15 text-blue-300 border border-blue-500/30'"
                                      x-text="reg.gender === 'P' ? '👧 Putri' : '👦 Putra'">
                                </span>
                                <template x-if="reg.chosen_song">
                                    <span class="text-[11px] font-medium text-purple-300 bg-purple-500/15 border border-purple-500/30 px-2 py-0.5 rounded truncate max-w-xs"
                                          x-text="'🎵 ' + reg.chosen_song">
                                    </span>
                                </template>
                            </div>

                            <h3 class="text-lg sm:text-xl font-black text-white tracking-tight uppercase font-display truncate"
                                x-text="reg.name">
                            </h3>

                            <p class="text-xs font-semibold text-slate-400 flex items-center gap-1.5 truncate">
                                <i data-lucide="school" class="w-3.5 h-3.5 text-slate-500 shrink-0"></i>
                                <span x-text="reg.institution" class="truncate"></span>
                            </p>
                        </div>
                    </div>

                    <!-- Right: Average Score & Status Badge -->
                    <div class="flex items-center gap-3 self-end sm:self-center shrink-0">
                        <!-- Calculated Average Score Pill -->
                        <div class="text-right">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Rata-Rata Nilai</span>
                            <div class="text-xl sm:text-2xl font-black font-mono tracking-tight"
                                 :class="getRegAverage(reg.id) > 0 ? 'text-amber-400' : 'text-slate-600'"
                                 x-text="getRegAverage(reg.id) > 0 ? getRegAverage(reg.id).toFixed(2) : '--.--'">
                            </div>
                        </div>

                        <!-- Status Pill -->
                        <template x-if="isRegFullyScored(reg.id)">
                            <span class="px-3 py-1.5 rounded-xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 text-xs font-bold flex items-center gap-1.5 shadow-sm">
                                <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                                <span class="hidden sm:inline">Lengkap</span>
                            </span>
                        </template>
                        <template x-if="isRegPartiallyScored(reg.id)">
                            <span class="px-3 py-1.5 rounded-xl bg-amber-500/15 border border-amber-500/30 text-amber-300 text-xs font-bold flex items-center gap-1.5 shadow-sm">
                                <i data-lucide="clock" class="w-4 h-4"></i>
                                <span x-text="getScoredJudgesCount(reg.id) + '/' + judges.length + ' Juri'"></span>
                            </span>
                        </template>
                        <template x-if="!isRegFullyScored(reg.id) && !isRegPartiallyScored(reg.id)">
                            <span class="px-3 py-1.5 rounded-xl bg-slate-950 border border-white/[0.08] text-slate-500 text-xs font-bold flex items-center gap-1.5">
                                <i data-lucide="minus-circle" class="w-4 h-4"></i>
                                <span class="hidden sm:inline">Belum Dinilai</span>
                            </span>
                        </template>

                        <div class="w-8 h-8 rounded-xl bg-white/[0.06] flex items-center justify-center text-slate-300">
                            <i data-lucide="chevron-down" class="w-4 h-4 transition-transform duration-300" :class="activeParticipantId === reg.id ? 'rotate-180 text-white' : ''"></i>
                        </div>
                    </div>
                </div>

                <!-- CARD BODY: MULTI-JUDGE INPUT MATRIX (Expanded Workspace) -->
                <div x-show="activeParticipantId === reg.id" 
                     x-collapse
                     class="px-5 pb-6 sm:px-8 sm:pb-8 pt-2 border-t border-white/[0.08] bg-black/20 space-y-6">

                    <div class="flex items-center justify-between gap-3 pt-3">
                        <div class="flex items-center gap-2 text-xs text-slate-300">
                            <i data-lucide="edit-3" class="w-4 h-4 text-amber-400"></i>
                            <span>Masukkan skor dari masing-masing form fisik juri di bawah ini:</span>
                        </div>
                        <span class="text-[11px] font-mono text-slate-400 hidden sm:inline">
                            Pintasan: <kbd class="px-1.5 py-0.5 rounded bg-white/10 text-white font-mono text-[10px]">Tab</kbd> untuk pindah kolom • <kbd class="px-1.5 py-0.5 rounded bg-white/10 text-white font-mono text-[10px]">Enter</kbd> untuk simpan
                        </span>
                    </div>

                    <!-- MULTI-JUDGE SIDE-BY-SIDE COLUMNS GRID -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 items-start">
                        
                        <template x-for="(judge, jIndex) in judges" :key="judge.id">
                            <div class="ai-panel rounded-2xl p-5 border border-white/[0.08] hover:border-white/[0.2] transition-all duration-200 space-y-4 shadow-lg flex flex-col justify-between"
                                 :class="isJudgeScored(reg.id, judge.id) ? 'bg-slate-900/90 border-indigo-500/30' : 'bg-slate-950/90'">
                                
                                <!-- Judge Title Header -->
                                <div class="flex items-center justify-between pb-3 border-b border-white/[0.08]">
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-1.5">
                                            <span class="w-2 h-2 rounded-full" :class="isJudgeScored(reg.id, judge.id) ? 'bg-emerald-400' : 'bg-slate-600'"></span>
                                            <span class="text-[10px] font-black uppercase tracking-wider text-[#84D0FF]" x-text="judge.role_title || ('Juri ' + (jIndex + 1))"></span>
                                        </div>
                                        <h4 class="text-sm font-black text-white truncate mt-0.5" x-text="judge.name"></h4>
                                    </div>

                                    <!-- Subtotal Badge for this Judge -->
                                    <div class="text-right shrink-0">
                                        <span class="text-[9px] font-bold text-slate-400 uppercase">Subtotal</span>
                                        <div class="text-base font-black font-mono text-emerald-400"
                                             x-text="getJudgeSubtotal(reg.id, judge.id).toFixed(2)">
                                        </div>
                                    </div>
                                </div>

                                <!-- Criteria Input Fields -->
                                <div class="space-y-3.5">
                                    <template x-for="crit in criteria" :key="crit.id">
                                        <div class="space-y-1">
                                            <div class="flex items-center justify-between text-[11px] font-bold">
                                                <label class="text-slate-300 truncate max-w-[170px]" :title="crit.name">
                                                    <span x-text="crit.name"></span>
                                                    <span class="text-slate-500 text-[10px]" x-text="'(' + crit.weight_percentage + '%)'"></span>
                                                </label>
                                                <span class="text-[10px] font-mono text-slate-500" x-text="'Rentang: ' + (crit.min_score || 0) + ' - ' + (crit.max_score || 100)"></span>
                                            </div>

                                            <div class="relative">
                                                <input type="number" 
                                                       step="0.1" 
                                                       :min="crit.min_score || 0" 
                                                       :max="crit.max_score || 100"
                                                       :value="getCriterionValue(reg.id, judge.id, crit.id)"
                                                       @input="setCriterionValue(reg.id, judge.id, crit.id, $event.target.value)"
                                                       @keydown.enter.prevent="saveRegistrationScores(reg.id); goToNextParticipant(reg.id)"
                                                       placeholder="0.0"
                                                       class="w-full px-3.5 py-2 rounded-xl bg-black/50 border border-white/[0.12] text-sm font-black font-mono text-white focus:outline-none focus:border-amber-400 focus:ring-1 focus:ring-amber-400 transition placeholder:text-slate-700">
                                            </div>
                                        </div>
                                    </template>
                                </div>

                                <!-- Judge Notes (Catatan Tambahan) -->
                                <div class="pt-2 border-t border-white/[0.06]">
                                    <input type="text" 
                                           :value="getJudgeNotes(reg.id, judge.id)"
                                           @input="setJudgeNotes(reg.id, judge.id, $event.target.value)"
                                           placeholder="Catatan juri (opsional)..." 
                                           class="w-full px-3 py-1.5 rounded-lg bg-black/40 border border-white/[0.08] text-xs text-slate-300 placeholder:text-slate-600 focus:outline-none focus:border-indigo-400">
                                </div>

                            </div>
                        </template>

                    </div>

                    <!-- CARD FOOTER: OVERALL AVERAGE & SAVE BUTTON BAR -->
                    <div class="p-4 rounded-2xl bg-gradient-to-r from-slate-950 via-slate-900 to-slate-950 border border-white/[0.12] flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-2xl">
                        
                        <!-- Real-time Live Average Calculation -->
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-amber-500/20 border border-amber-500/40 text-amber-300 flex items-center justify-center font-mono font-black text-xl shadow-lg shadow-amber-500/10">
                                <i data-lucide="calculator" class="w-6 h-6"></i>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-black uppercase text-slate-400">NILAI RATA-RATA AKHIR (SEMUA JURI):</span>
                                    <span class="text-[10px] font-mono font-bold text-indigo-300 px-2 py-0.5 rounded bg-indigo-500/20"
                                          x-text="getScoredJudgesCount(reg.id) + ' dari ' + judges.length + ' Juri terisi'">
                                    </span>
                                </div>
                                <div class="text-2xl sm:text-3xl font-black font-mono text-amber-400 tracking-tight"
                                     x-text="getRegAverage(reg.id).toFixed(2)">
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center gap-3 flex-wrap justify-end">
                            <label class="flex items-center gap-2 text-xs font-bold text-slate-300 cursor-pointer select-none px-3 py-2 rounded-xl bg-white/[0.04] border border-white/[0.08] hover:bg-white/[0.08] transition">
                                <input type="checkbox" 
                                       x-model="lockState[reg.id]" 
                                       class="rounded border-slate-700 text-emerald-500 focus:ring-emerald-400">
                                <span>Kunci & Kirim ke Live Scoreboard</span>
                            </label>

                            <button type="button" 
                                    @click="saveRegistrationScores(reg.id)"
                                    :disabled="isSaving[reg.id]"
                                    class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-slate-950 font-black text-xs shadow-lg shadow-emerald-500/25 transition flex items-center gap-2 cursor-pointer disabled:opacity-50">
                                <i data-lucide="save" class="w-4 h-4" x-show="!isSaving[reg.id]"></i>
                                <i data-lucide="loader-2" class="w-4 h-4 animate-spin" x-show="isSaving[reg.id]" x-cloak></i>
                                <span x-text="isSaving[reg.id] ? 'Menyimpan...' : 'Simpan Nilai Peserta (Enter)'"></span>
                            </button>

                            <!-- Next Participant button -->
                            <button type="button" 
                                    @click="goToNextParticipant(reg.id)"
                                    class="px-3.5 py-2.5 rounded-xl bg-white/[0.06] hover:bg-white/[0.12] text-slate-300 hover:text-white font-bold text-xs border border-white/[0.08] transition flex items-center gap-1.5"
                                    title="Lanjut ke peserta berikutnya">
                                <span>Selanjutnya</span>
                                <i data-lucide="chevron-right" class="w-4 h-4"></i>
                            </button>
                        </div>

                    </div>

                </div>

            </div>
        </template>

        <!-- EMPTY STATE TAB 1 -->
        <template x-if="filteredParticipants.length === 0">
            <div class="p-12 text-center rounded-3xl bg-slate-950/60 border border-white/[0.08] space-y-3">
                <i data-lucide="user-x" class="w-12 h-12 text-slate-600 mx-auto"></i>
                <h4 class="text-base font-bold text-slate-300">Tidak ada peserta yang cocok dengan filter / pencarian.</h4>
                <p class="text-xs text-slate-500">Coba ubah kata kunci pencarian atau reset filter sektor di atas.</p>
            </div>
        </template>

    </div>
    </div>

    <!-- =========================================================================
         TAB 2 CONTENT: MATRIKS REKAP NILAI LENGKAP SEMUA JURI & KRITERIA
         ========================================================================= -->
    <div x-show="activeTab === 'rekap'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6">
        
        <!-- TOOLBAR REKAP & VIEW OPTIONS -->
        <div class="ai-panel p-4 rounded-2xl border border-white/[0.08] flex flex-col lg:flex-row lg:items-center justify-between gap-4 shadow-lg">
            
            <!-- Left: Search & Filter Controls -->
            <div class="flex items-center gap-3 flex-wrap flex-1">
                <!-- Search Input -->
                <div class="relative w-full sm:w-72">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                    <input type="text" 
                           x-model="searchQuery" 
                           placeholder="Cari nama, sekolah, #undian..." 
                           class="w-full pl-10 pr-4 py-2 rounded-xl bg-slate-950 border border-white/[0.12] text-xs font-semibold text-white focus:outline-none focus:border-[#7A5AF8] transition placeholder:text-slate-500">
                </div>

                <!-- Sector Filter -->
                <div class="inline-flex p-1 bg-slate-950 rounded-xl border border-white/[0.08] text-xs">
                    <button type="button" 
                            @click="sectorFilter = 'all'" 
                            class="px-2.5 py-1 rounded-lg font-bold transition cursor-pointer"
                            :class="sectorFilter === 'all' ? 'bg-[#7A5AF8] text-white shadow-xs' : 'text-slate-400 hover:text-white'">
                        Semua Sektor
                    </button>
                    <button type="button" 
                            @click="sectorFilter = 'PA'" 
                            class="px-2.5 py-1 rounded-lg font-bold transition cursor-pointer"
                            :class="sectorFilter === 'PA' ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-400 hover:text-white'">
                        👦 PA
                    </button>
                    <button type="button" 
                            @click="sectorFilter = 'PI'" 
                            class="px-2.5 py-1 rounded-lg font-bold transition cursor-pointer"
                            :class="sectorFilter === 'PI' ? 'bg-pink-600 text-white shadow-xs' : 'text-slate-400 hover:text-white'">
                        👧 PI
                    </button>
                </div>

                <!-- Status Filter -->
                <select x-model="statusFilter" 
                        class="px-3 py-1.5 rounded-xl bg-slate-950 border border-white/[0.12] text-xs font-bold text-slate-300 focus:outline-none focus:border-[#7A5AF8] cursor-pointer">
                    <option value="all">Semua Status</option>
                    <option value="scored">🟢 Lengkap Terkunci</option>
                    <option value="unscored">⚪ Belum Lengkap</option>
                </select>
            </div>

            <!-- Right: Mode Tampilan, Reset Sort, & Export -->
            <div class="flex items-center gap-2.5 flex-wrap justify-end">
                <!-- Mode Tampilan Switcher (Rinci vs Ringkas) -->
                <div class="inline-flex p-1 bg-slate-950 rounded-xl border border-white/[0.08] text-xs">
                    <button type="button" 
                            @click="viewMode = 'detailed'" 
                            class="px-3 py-1.5 rounded-lg font-bold transition flex items-center gap-1.5 cursor-pointer"
                            :class="viewMode === 'detailed' ? 'bg-[#7A5AF8] text-white shadow-xs' : 'text-slate-400 hover:text-white'"
                            title="Tampilkan rincian sub-kriteria per juri">
                        <i data-lucide="layout-grid" class="w-3.5 h-3.5"></i>
                        <span>Rincian Kriteria</span>
                    </button>
                    <button type="button" 
                            @click="viewMode = 'summary'" 
                            class="px-3 py-1.5 rounded-lg font-bold transition flex items-center gap-1.5 cursor-pointer"
                            :class="viewMode === 'summary' ? 'bg-[#7A5AF8] text-white shadow-xs' : 'text-slate-400 hover:text-white'"
                            title="Tampilkan hanya total akumulasi per juri">
                        <i data-lucide="columns" class="w-3.5 h-3.5"></i>
                        <span>Ringkas (Total Juri)</span>
                    </button>
                </div>

                <!-- Reset Sort Button -->
                <button type="button" 
                        @click="setSort('rank')"
                        class="px-3 py-2 rounded-xl bg-white/[0.06] hover:bg-white/[0.12] text-slate-300 hover:text-white border border-white/[0.08] text-xs font-bold transition flex items-center gap-1.5 cursor-pointer"
                        title="Kembalikan urutan tabel ke Peringkat Nilai Tertinggi">
                    <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                    <span>Reset Urutan</span>
                </button>

                <!-- Export XLSX Excel Button -->
                <button type="button" 
                        @click="exportRecapXls()"
                        class="px-3.5 py-2 rounded-xl bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-300 border border-emerald-500/40 text-xs font-bold transition flex items-center gap-1.5 cursor-pointer shadow-sm"
                        title="Download Data Rekap Nilai ke Format Dokumen Excel (.xlsx) Rapi (1 Baris per Peserta)">
                    <i data-lucide="file-spreadsheet" class="w-3.5 h-3.5 text-emerald-400"></i>
                    <span>Export Excel (.xlsx)</span>
                </button>
            </div>

        </div>

        <!-- INFO TIPS BAR -->
        <div class="px-4 py-2.5 rounded-2xl bg-slate-900/60 border border-white/[0.06] flex items-center justify-between text-xs text-slate-400 flex-wrap gap-2">
            <div class="flex items-center gap-2">
                <i data-lucide="help-circle" class="w-3.5 h-3.5 text-amber-400 shrink-0"></i>
                <span>Klik pada <strong>judul kolom tabel</strong> mana saja untuk mengurutkan (Sort Ascending <span class="text-amber-400">▲</span> / Descending <span class="text-amber-400">▼</span>).</span>
            </div>
            <div class="font-mono text-[11px] text-slate-300">
                Total: <strong class="text-white" x-text="sortedAndFilteredRecapParticipants.length"></strong> Peserta
            </div>
        </div>

        <!-- MATRIKS REKAP TABLE -->
        <div class="ai-card rounded-3xl border border-white/[0.08] shadow-2xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    
                    <!-- THEAD -->
                    <thead class="bg-slate-950/95 backdrop-blur-md text-[11px] font-black uppercase tracking-wider text-slate-300 border-b border-white/[0.12] sticky top-0 z-20">
                        
                        <!-- Top Header Row -->
                        <tr>
                            <th :rowspan="viewMode === 'detailed' ? 2 : 1" 
                                @click="setSort('rank')"
                                class="py-3.5 px-3 text-center cursor-pointer hover:bg-white/[0.06] transition select-none border-r border-white/[0.06] min-w-[65px]"
                                title="Urutkan berdasarkan Peringkat / Ranking">
                                <div class="flex items-center justify-center gap-1">
                                    <span>Rank</span>
                                    <span class="text-slate-500 font-mono text-[10px]" :class="sortBy === 'rank' ? 'text-amber-400 font-black' : ''" x-text="sortBy === 'rank' ? (sortDirection === 'asc' ? '▲' : '▼') : '↕'"></span>
                                </div>
                            </th>

                            <th :rowspan="viewMode === 'detailed' ? 2 : 1" 
                                @click="setSort('draw_number')"
                                class="py-3.5 px-3 text-center cursor-pointer hover:bg-white/[0.06] transition select-none border-r border-white/[0.06] min-w-[70px]"
                                title="Urutkan berdasarkan Nomor Undian">
                                <div class="flex items-center justify-center gap-1">
                                    <span>#Undian</span>
                                    <span class="text-slate-500 font-mono text-[10px]" :class="sortBy === 'draw_number' ? 'text-amber-400 font-black' : ''" x-text="sortBy === 'draw_number' ? (sortDirection === 'asc' ? '▲' : '▼') : '↕'"></span>
                                </div>
                            </th>

                            <th :rowspan="viewMode === 'detailed' ? 2 : 1" 
                                @click="setSort('participant_number')"
                                class="py-3.5 px-3 cursor-pointer hover:bg-white/[0.06] transition select-none border-r border-white/[0.06] min-w-[90px]"
                                title="Urutkan berdasarkan Nomor Peserta">
                                <div class="flex items-center gap-1">
                                    <span>No. Peserta</span>
                                    <span class="text-slate-500 font-mono text-[10px]" :class="sortBy === 'participant_number' ? 'text-amber-400 font-black' : ''" x-text="sortBy === 'participant_number' ? (sortDirection === 'asc' ? '▲' : '▼') : '↕'"></span>
                                </div>
                            </th>

                            <th :rowspan="viewMode === 'detailed' ? 2 : 1" 
                                @click="setSort('name')"
                                class="py-3.5 px-4 cursor-pointer hover:bg-white/[0.06] transition select-none border-r border-white/[0.06] min-w-[180px]"
                                title="Urutkan berdasarkan Nama Peserta (A-Z)">
                                <div class="flex items-center gap-1">
                                    <span>Nama Peserta</span>
                                    <span class="text-slate-500 font-mono text-[10px]" :class="sortBy === 'name' ? 'text-amber-400 font-black' : ''" x-text="sortBy === 'name' ? (sortDirection === 'asc' ? '▲' : '▼') : '↕'"></span>
                                </div>
                            </th>

                            <th :rowspan="viewMode === 'detailed' ? 2 : 1" 
                                @click="setSort('institution')"
                                class="py-3.5 px-4 cursor-pointer hover:bg-white/[0.06] transition select-none border-r border-white/[0.06] min-w-[160px]"
                                title="Urutkan berdasarkan Asal Lembaga / Sekolah">
                                <div class="flex items-center gap-1">
                                    <span>Lembaga / Sekolah</span>
                                    <span class="text-slate-500 font-mono text-[10px]" :class="sortBy === 'institution' ? 'text-amber-400 font-black' : ''" x-text="sortBy === 'institution' ? (sortDirection === 'asc' ? '▲' : '▼') : '↕'"></span>
                                </div>
                            </th>

                            <!-- Dynamic Judges Columns Header -->
                            <template x-for="(j, jIdx) in judges" :key="j.id || jIdx">
                                <template x-if="viewMode === 'detailed'">
                                    <th :colspan="criteria.length + 1" 
                                        class="py-2.5 px-3 text-center border-r border-white/[0.08] bg-indigo-950/40 text-indigo-200">
                                        <div class="font-black text-xs text-white" x-text="(j.role_title || ('Juri ' + (jIdx + 1))) + ': ' + j.name"></div>
                                        <div class="text-[9px] text-indigo-300 font-medium lowercase" x-text="'rincian ' + criteria.length + ' kriteria penilaian'"></div>
                                    </th>
                                </template>
                                <template x-if="viewMode === 'summary'">
                                    <th @click="setSort('judge_total', j.id)"
                                        class="py-3.5 px-3 text-center cursor-pointer hover:bg-white/[0.06] transition select-none border-r border-white/[0.06] bg-indigo-950/30 text-indigo-200 min-w-[100px]"
                                        :title="'Urutkan berdasarkan nilai total ' + (j.role_title || ('Juri ' + (jIdx + 1)))">
                                        <div class="flex items-center justify-center gap-1">
                                            <span x-text="j.role_title || ('Juri ' + (jIdx + 1))"></span>
                                            <span class="text-slate-500 font-mono text-[10px]" :class="sortBy === 'judge_total' && sortJudgeId === j.id ? 'text-amber-400 font-black' : ''" x-text="sortBy === 'judge_total' && sortJudgeId === j.id ? (sortDirection === 'asc' ? '▲' : '▼') : '↕'"></span>
                                        </div>
                                    </th>
                                </template>
                            </template>

                            <!-- Overall Totals, Average, & Status Headers -->
                            <th :rowspan="viewMode === 'detailed' ? 2 : 1" 
                                @click="setSort('total')"
                                class="py-3.5 px-3 text-center cursor-pointer hover:bg-white/[0.06] transition select-none border-r border-white/[0.06] bg-[#7A5AF8]/15 text-[#C7D2FE] min-w-[90px]"
                                title="Urutkan berdasarkan Total Akumulasi Nilai Semua Juri">
                                <div class="flex items-center justify-center gap-1">
                                    <span>Total Skor</span>
                                    <span class="text-slate-500 font-mono text-[10px]" :class="sortBy === 'total' ? 'text-amber-400 font-black' : ''" x-text="sortBy === 'total' ? (sortDirection === 'asc' ? '▲' : '▼') : '↕'"></span>
                                </div>
                            </th>

                            <th :rowspan="viewMode === 'detailed' ? 2 : 1" 
                                @click="setSort('average')"
                                class="py-3.5 px-3 text-center cursor-pointer hover:bg-white/[0.06] transition select-none border-r border-white/[0.06] bg-amber-500/15 text-amber-300 min-w-[95px]"
                                title="Urutkan berdasarkan Nilai Rata-Rata Akhir">
                                <div class="flex items-center justify-center gap-1">
                                    <span>Rata-Rata</span>
                                    <span class="text-slate-500 font-mono text-[10px]" :class="sortBy === 'average' ? 'text-amber-400 font-black' : ''" x-text="sortBy === 'average' ? (sortDirection === 'asc' ? '▲' : '▼') : '↕'"></span>
                                </div>
                            </th>

                            <th :rowspan="viewMode === 'detailed' ? 2 : 1" 
                                @click="setSort('status')"
                                class="py-3.5 px-3 text-center cursor-pointer hover:bg-white/[0.06] transition select-none border-r border-white/[0.06] min-w-[85px]"
                                title="Urutkan berdasarkan Kelengkapan Status Penilaian">
                                <div class="flex items-center justify-center gap-1">
                                    <span>Status</span>
                                    <span class="text-slate-500 font-mono text-[10px]" :class="sortBy === 'status' ? 'text-amber-400 font-black' : ''" x-text="sortBy === 'status' ? (sortDirection === 'asc' ? '▲' : '▼') : '↕'"></span>
                                </div>
                            </th>

                            <th :rowspan="viewMode === 'detailed' ? 2 : 1" 
                                class="py-3.5 px-3 text-center min-w-[60px]">
                                <span>Aksi</span>
                            </th>
                        </tr>

                        <!-- Sub Header Row (Khusus Detailed View Mode: Menampilkan Nama Tiap Kriteria & Total per Juri) -->
                        <tr x-show="viewMode === 'detailed'" class="border-t border-white/[0.08] bg-slate-950/80 text-[10px]">
                            <template x-for="(j, jIdx) in judges" :key="'sub-j-' + (j.id || jIdx)">
                                <!-- Group of Criteria Sub Headers -->
                                <template x-for="(c, cIdx) in criteria" :key="'sub-c-' + c.id">
                                    <th @click="setSort('criterion', j.id, c.id)"
                                        class="py-2 px-2 text-center cursor-pointer hover:bg-white/[0.08] transition select-none border-r border-white/[0.04] text-slate-300 min-w-[65px]"
                                        :title="c.name + ' (Bobot: ' + (c.weight_percentage || 100) + '%, Rentang: ' + (c.min_score || 0) + '-' + (c.max_score || 100) + ')'">
                                        <div class="flex items-center justify-center gap-0.5">
                                            <span class="truncate max-w-[75px]" x-text="c.name"></span>
                                            <span class="text-slate-500 font-mono text-[9px]" :class="sortBy === 'criterion' && sortJudgeId === j.id && sortCritId === c.id ? 'text-amber-400 font-black' : ''" x-text="sortBy === 'criterion' && sortJudgeId === j.id && sortCritId === c.id ? (sortDirection === 'asc' ? '▲' : '▼') : '↕'"></span>
                                        </div>
                                    </th>
                                </template>
                                <!-- Subtotal Column for this Judge -->
                                <th @click="setSort('judge_total', j.id)"
                                    class="py-2 px-2.5 text-center cursor-pointer hover:bg-white/[0.08] transition select-none border-r border-white/[0.08] bg-indigo-950/60 text-indigo-300 font-black min-w-[70px]"
                                    :title="'Total skor dari ' + (j.role_title || ('Juri ' + (jIdx + 1)))">
                                    <div class="flex items-center justify-center gap-0.5">
                                        <span>Total</span>
                                        <span class="text-slate-500 font-mono text-[9px]" :class="sortBy === 'judge_total' && sortJudgeId === j.id ? 'text-amber-400 font-black' : ''" x-text="sortBy === 'judge_total' && sortJudgeId === j.id ? (sortDirection === 'asc' ? '▲' : '▼') : '↕'"></span>
                                    </div>
                                </th>
                            </template>
                        </tr>

                    </thead>

                    <!-- TBODY DATA ROWS -->
                    <tbody class="divide-y divide-white/[0.06] font-medium text-slate-300">
                        <template x-for="(reg, index) in sortedAndFilteredRecapParticipants" :key="'recap-' + reg.id">
                            <tr class="hover:bg-white/[0.03] transition-colors"
                                :class="{
                                    'bg-amber-500/[0.04]': getParticipantRank(reg.id) === 1,
                                    'bg-slate-300/[0.03]': getParticipantRank(reg.id) === 2,
                                    'bg-amber-700/[0.03]': getParticipantRank(reg.id) === 3
                                }">
                                
                                <!-- Rank Badge -->
                                <td class="py-3 px-3 text-center border-r border-white/[0.06]">
                                    <template x-if="getParticipantRank(reg.id) === 1">
                                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-xl bg-gradient-to-tr from-amber-400 to-amber-500 text-slate-950 font-black shadow-md shadow-amber-500/20 text-xs">
                                            🥇 1
                                        </span>
                                    </template>
                                    <template x-if="getParticipantRank(reg.id) === 2">
                                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-xl bg-gradient-to-tr from-slate-200 to-slate-400 text-slate-950 font-black shadow-md text-xs">
                                            🥈 2
                                        </span>
                                    </template>
                                    <template x-if="getParticipantRank(reg.id) === 3">
                                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-xl bg-gradient-to-tr from-amber-700 to-amber-800 text-amber-100 font-black shadow-md text-xs">
                                            🥉 3
                                        </span>
                                    </template>
                                    <template x-if="getParticipantRank(reg.id) > 3">
                                        <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-lg bg-slate-800 border border-white/[0.08] text-slate-300 font-mono font-black text-xs" 
                                              x-text="getParticipantRank(reg.id)">
                                        </span>
                                    </template>
                                    <template x-if="!getParticipantRank(reg.id)">
                                        <span class="text-slate-600 font-mono">-</span>
                                    </template>
                                </td>

                                <!-- Draw Number -->
                                <td class="py-3 px-3 text-center border-r border-white/[0.06] font-mono">
                                    <span class="px-2 py-1 rounded-lg font-black text-xs"
                                          :class="reg.draw_number ? 'bg-amber-400/15 text-amber-300 border border-amber-400/30' : 'text-slate-600'"
                                          x-text="reg.draw_number ? '#' + reg.draw_number : '-'"></span>
                                </td>

                                <!-- Participant Code -->
                                <td class="py-3 px-3 border-r border-white/[0.06] font-mono">
                                    <span class="text-[11px] font-bold text-[#84D0FF] bg-[#4E6EFF]/15 border border-[#4E6EFF]/30 px-2 py-0.5 rounded"
                                          x-text="reg.participant_number"></span>
                                </td>

                                <!-- Participant Name -->
                                <td class="py-3 px-4 border-r border-white/[0.06]">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-black text-white uppercase truncate font-display" x-text="reg.name"></span>
                                        <span class="text-[10px] font-bold px-1.5 py-0.2 rounded shrink-0"
                                              :class="reg.gender === 'P' ? 'bg-pink-500/20 text-pink-300' : 'bg-blue-500/20 text-blue-300'"
                                              x-text="reg.gender === 'P' ? 'PI' : 'PA'"></span>
                                    </div>
                                    <template x-if="reg.chosen_song">
                                        <div class="text-[10px] text-purple-300 truncate max-w-xs mt-0.5" x-text="'🎵 ' + reg.chosen_song"></div>
                                    </template>
                                </td>

                                <!-- Institution -->
                                <td class="py-3 px-4 border-r border-white/[0.06] text-xs font-semibold text-slate-400 truncate max-w-[200px]"
                                    x-text="reg.institution"></td>

                                <!-- Judge Scores Cells -->
                                <template x-for="(j, jIdx) in judges" :key="'cell-j-' + (j.id || jIdx)">
                                    <template x-if="viewMode === 'detailed'">
                                        <!-- Criteria Values List -->
                                        <template x-for="(c, cIdx) in criteria" :key="'cell-c-' + c.id">
                                            <td class="py-3 px-2 text-center border-r border-white/[0.04] font-mono text-xs"
                                                :class="getCriterionValue(reg.id, j.id, c.id) !== '' ? 'text-slate-200' : 'text-slate-600'"
                                                x-text="getCriterionValue(reg.id, j.id, c.id) !== '' ? Number(getCriterionValue(reg.id, j.id, c.id)).toFixed(1) : '-'">
                                            </td>
                                        </template>
                                        <!-- Judge Total Subtotal -->
                                        <td class="py-3 px-2.5 text-center border-r border-white/[0.08] font-mono text-xs font-black bg-indigo-950/20"
                                            :class="getJudgeSubtotal(reg.id, j.id) > 0 ? 'text-indigo-300' : 'text-slate-600'"
                                            x-text="getJudgeSubtotal(reg.id, j.id) > 0 ? getJudgeSubtotal(reg.id, j.id).toFixed(1) : '-'">
                                        </td>
                                    </template>
                                    <template x-if="viewMode === 'summary'">
                                        <td class="py-3 px-3 text-center border-r border-white/[0.06] font-mono text-xs font-black bg-indigo-950/20"
                                            :class="getJudgeSubtotal(reg.id, j.id) > 0 ? 'text-indigo-300' : 'text-slate-600'"
                                            x-text="getJudgeSubtotal(reg.id, j.id) > 0 ? getJudgeSubtotal(reg.id, j.id).toFixed(1) : '-'">
                                        </td>
                                    </template>
                                </template>

                                <!-- Total Score -->
                                <td class="py-3 px-3 text-center border-r border-white/[0.06] font-mono text-xs font-black bg-[#7A5AF8]/10"
                                    :class="getRegTotalScore(reg.id) > 0 ? 'text-white' : 'text-slate-600'"
                                    x-text="getRegTotalScore(reg.id) > 0 ? getRegTotalScore(reg.id).toFixed(1) : '-'">
                                </td>

                                <!-- Average Score -->
                                <td class="py-3 px-3 text-center border-r border-white/[0.06] font-mono text-xs font-black bg-amber-500/10"
                                    :class="getRegAverage(reg.id) > 0 ? 'text-amber-400 font-black' : 'text-slate-600'"
                                    x-text="getRegAverage(reg.id) > 0 ? getRegAverage(reg.id).toFixed(2) : '-'">
                                </td>

                                <!-- Status -->
                                <td class="py-3 px-3 text-center border-r border-white/[0.06]">
                                    <template x-if="isRegFullyScored(reg.id)">
                                        <span class="inline-flex items-center gap-1 text-[10px] font-black uppercase text-emerald-300 bg-emerald-500/15 border border-emerald-500/30 px-2 py-0.5 rounded-lg">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                            Lengkap
                                        </span>
                                    </template>
                                    <template x-if="isRegPartiallyScored(reg.id)">
                                        <span class="inline-flex items-center gap-1 text-[10px] font-black uppercase text-amber-300 bg-amber-500/15 border border-amber-500/30 px-2 py-0.5 rounded-lg">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                                            Sebagian
                                        </span>
                                    </template>
                                    <template x-if="!isRegFullyScored(reg.id) && !isRegPartiallyScored(reg.id)">
                                        <span class="inline-flex items-center text-[10px] font-bold text-slate-500 bg-slate-900 border border-white/[0.08] px-2 py-0.5 rounded-lg">
                                            Belum
                                        </span>
                                    </template>
                                </td>

                                <!-- Action Button: Open and Edit in Tab 1 -->
                                <td class="py-3 px-3 text-center">
                                    <button type="button" 
                                            @click="editParticipantInTab1(reg.id)"
                                            class="p-1.5 rounded-xl bg-white/[0.06] hover:bg-white/[0.12] text-slate-300 hover:text-white border border-white/[0.08] transition inline-flex items-center justify-center shadow-sm cursor-pointer"
                                            title="Buka Form Input Penilaian Peserta Ini">
                                        <i data-lucide="edit-3" class="w-3.5 h-3.5 text-[#4E6EFF]"></i>
                                    </button>
                                </td>

                            </tr>
                        </template>

                        <!-- Empty State in Tab 2 -->
                        <template x-if="sortedAndFilteredRecapParticipants.length === 0">
                            <tr>
                                <td :colspan="10 + (viewMode === 'detailed' ? (judges.length * (criteria.length + 1)) : judges.length)" 
                                    class="py-12 text-center text-slate-500">
                                    <div class="space-y-2">
                                        <i data-lucide="user-x" class="w-8 h-8 mx-auto text-slate-600"></i>
                                        <p class="font-bold text-sm text-slate-400">Tidak ada peserta yang cocok dengan filter pencarian.</p>
                                    </div>
                                </td>
                            </tr>
                        </template>

                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- =========================================================================
         MODAL: KELOLA NAMA DEWAN JURI
         ========================================================================= -->
    <div x-show="isJudgesModalOpen" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-md"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="ai-card w-full max-w-2xl rounded-3xl p-6 sm:p-8 border border-white/[0.15] shadow-2xl space-y-6 relative max-h-[90vh] overflow-y-auto"
             @click.away="isJudgesModalOpen = false">
            
            <div class="flex items-center justify-between pb-4 border-b border-white/[0.08]">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-indigo-500/20 text-indigo-400 flex items-center justify-center border border-indigo-500/30">
                        <i data-lucide="users" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-white font-display">Kelola Nama Dewan Juri</h3>
                        <p class="text-xs text-slate-400">Atur jumlah dan nama juri penilai untuk cabang {{ $competition->name }}.</p>
                    </div>
                </div>

                <button type="button" @click="isJudgesModalOpen = false" class="p-2 rounded-xl bg-white/[0.06] hover:bg-white/[0.12] text-slate-400 hover:text-white transition">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <!-- Dynamic Judges Form Rows -->
            <div class="space-y-4">
                <template x-for="(j, index) in editableJudges" :key="index">
                    <div class="p-4 rounded-2xl bg-slate-950/80 border border-white/[0.08] space-y-3 relative group">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-black uppercase text-indigo-300 font-mono flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-indigo-400"></span>
                                <span x-text="'Posisi Juri #' + (index + 1)"></span>
                            </span>

                            <button type="button" 
                                    x-show="editableJudges.length > 1"
                                    @click="removeJudgeRow(index)"
                                    class="text-xs text-rose-400 hover:text-rose-300 p-1 rounded hover:bg-rose-500/10 transition"
                                    title="Hapus Juri Ini">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[10px] font-black uppercase text-slate-400 mb-1">Nama Lengkap & Gelar Juri</label>
                                <input type="text" 
                                       x-model="j.name" 
                                       placeholder="Contoh: Drs. H. Ahmad Subagio, M.Pd"
                                       class="w-full px-3.5 py-2 rounded-xl bg-black/60 border border-white/[0.12] text-xs font-bold text-white focus:outline-none focus:border-indigo-400 placeholder:text-slate-600">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black uppercase text-slate-400 mb-1">Bidang / Jabatan Juri</label>
                                <input type="text" 
                                       x-model="j.role_title" 
                                       placeholder="Contoh: Juri 1 - Bidang Tajwid"
                                       class="w-full px-3.5 py-2 rounded-xl bg-black/60 border border-white/[0.12] text-xs font-bold text-white focus:outline-none focus:border-indigo-400 placeholder:text-slate-600">
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Add Judge Button -->
            <button type="button" 
                    @click="addJudgeRow()"
                    class="w-full py-2.5 rounded-2xl bg-white/[0.04] hover:bg-white/[0.08] border border-dashed border-white/[0.2] text-xs font-bold text-indigo-300 hover:text-indigo-200 transition flex items-center justify-center gap-2 cursor-pointer">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>+ Tambah Juri Lainnya</span>
            </button>

            <!-- Modal Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-white/[0.08]">
                <button type="button" 
                        @click="isJudgesModalOpen = false" 
                        class="px-4 py-2.5 rounded-xl bg-white/[0.06] hover:bg-white/[0.12] text-slate-300 hover:text-white text-xs font-bold transition">
                    Batal
                </button>

                <button type="button" 
                        @click="saveJudgesList()" 
                        :disabled="isSavingJudges"
                        class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-indigo-500 to-[#4E6EFF] hover:from-indigo-600 hover:to-[#3b5beb] text-white font-black text-xs shadow-lg shadow-indigo-500/25 transition flex items-center gap-2 cursor-pointer disabled:opacity-50">
                    <i data-lucide="save" class="w-4 h-4" x-show="!isSavingJudges"></i>
                    <i data-lucide="loader-2" class="w-4 h-4 animate-spin" x-show="isSavingJudges" x-cloak></i>
                    <span x-text="isSavingJudges ? 'Menyimpan...' : 'Simpan Nama Juri'"></span>
                </button>
            </div>

        </div>
    </div>

</div>

<!-- =========================================================================
     JAVASCRIPT APP LOGIC (Alpine.js)
     ========================================================================= -->
<script>
function multiJudgeScoringApp(competition, initialJudges, initialCriteria, initialParticipants, initialScoresMap) {
    return {
        competition: competition,
        judges: initialJudges || [],
        criteria: initialCriteria || [],
        participants: initialParticipants || [],
        scoresData: initialScoresMap || {},
        
        // Tab Navigation State (input: Form Input Nilai | rekap: Matriks Rekap Lengkap)
        activeTab: '{{ request("tab") === "rekap" ? "rekap" : "input" }}',
        viewMode: 'detailed', // 'detailed' (per kriteria) | 'summary' (total juri saja)
        
        // Sorting State
        sortBy: 'rank', // 'rank', 'draw_number', 'participant_number', 'name', 'institution', 'average', 'total', 'judge_total', 'criterion', 'status'
        sortDirection: 'asc', // 'asc' or 'desc'
        sortJudgeId: null,
        sortCritId: null,

        searchQuery: '',
        sectorFilter: 'all',
        statusFilter: 'all',
        activeParticipantId: initialParticipants.length > 0 ? initialParticipants[0].id : null,
        
        // Modal state
        isJudgesModalOpen: false,
        editableJudges: [],
        isSavingJudges: false,
        
        // Locking & saving states
        lockState: {},
        isSaving: {},

        initApp() {
            // Initialize lock states for all participants
            this.participants.forEach(p => {
                const pScores = this.scoresData[p.id] || {};
                const anyLocked = Object.values(pScores).some(s => s.is_locked);
                this.lockState[p.id] = anyLocked || true; // default checked
            });

            this.$nextTick(() => {
                if (window.lucide) window.lucide.createIcons();
            });
        },

        async switchTab(tab) {
            if (this.activeParticipantId && this.scoresData[this.activeParticipantId]) {
                await this.saveRegistrationScores(this.activeParticipantId);
            }
            this.activeTab = tab;
            this.$nextTick(() => {
                if (window.lucide) window.lucide.createIcons();
            });
        },

        setSectorFilter(val) {
            this.sectorFilter = val;
            this.$nextTick(() => {
                if (window.lucide) window.lucide.createIcons();
                const list = this.filteredParticipants;
                if (list.length > 0) {
                    if (!list.some(p => p.id === this.activeParticipantId)) {
                        this.activeParticipantId = list[0].id;
                    }
                } else {
                    this.activeParticipantId = null;
                }
            });
        },

        editParticipantInTab1(regId) {
            this.activeTab = 'input';
            this.activeParticipantId = regId;
            this.$nextTick(() => {
                if (window.lucide) window.lucide.createIcons();
                const el = document.getElementById('participant-card-' + regId);
                if (el) {
                    el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            });
        },

        // Dynamic Ranking Map based on scored participants (highest average/total)
        get rankMap() {
            const scored = this.participants
                .filter(p => this.getRegAverage(p.id) > 0)
                .map(p => ({
                    id: p.id,
                    avg: this.getRegAverage(p.id),
                    total: this.getRegTotalScore(p.id),
                    draw: p.draw_number ?? 99999
                }))
                .sort((a, b) => {
                    if (Math.abs(b.avg - a.avg) > 0.0001) return b.avg - a.avg;
                    if (Math.abs(b.total - a.total) > 0.0001) return b.total - a.total;
                    return a.draw - b.draw;
                });

            const map = {};
            scored.forEach((item, idx) => {
                map[item.id] = idx + 1;
            });
            return map;
        },

        getParticipantRank(regId) {
            return this.rankMap[regId] || null;
        },

        // Sort switcher
        setSort(column, judgeId = null, critId = null) {
            if (this.sortBy === column && this.sortJudgeId === judgeId && this.sortCritId === critId) {
                this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortBy = column;
                this.sortJudgeId = judgeId;
                this.sortCritId = critId;
                // Numeric scores, averages, and totals default to descending; strings/ranks/numbers default to ascending
                if (['average', 'total', 'judge_total', 'criterion'].includes(column)) {
                    this.sortDirection = 'desc';
                } else {
                    this.sortDirection = 'asc';
                }
            }
            this.$nextTick(() => {
                if (window.lucide) window.lucide.createIcons();
            });
        },

        get sortedAndFilteredRecapParticipants() {
            let list = this.filteredParticipants.slice();
            const dir = this.sortDirection === 'asc' ? 1 : -1;

            list.sort((a, b) => {
                let valA, valB;

                if (this.sortBy === 'rank') {
                    valA = this.getParticipantRank(a.id) ?? 99999;
                    valB = this.getParticipantRank(b.id) ?? 99999;
                } else if (this.sortBy === 'draw_number') {
                    valA = a.draw_number ?? 99999;
                    valB = b.draw_number ?? 99999;
                } else if (this.sortBy === 'participant_number') {
                    valA = a.participant_number || '';
                    valB = b.participant_number || '';
                    return dir * valA.localeCompare(valB, undefined, { numeric: true });
                } else if (this.sortBy === 'name') {
                    valA = a.name || '';
                    valB = b.name || '';
                    return dir * valA.localeCompare(valB);
                } else if (this.sortBy === 'institution') {
                    valA = a.institution || '';
                    valB = b.institution || '';
                    return dir * valA.localeCompare(valB);
                } else if (this.sortBy === 'average') {
                    valA = this.getRegAverage(a.id);
                    valB = this.getRegAverage(b.id);
                } else if (this.sortBy === 'total') {
                    valA = this.getRegTotalScore(a.id);
                    valB = this.getRegTotalScore(b.id);
                } else if (this.sortBy === 'judge_total') {
                    valA = this.getJudgeSubtotal(a.id, this.sortJudgeId);
                    valB = this.getJudgeSubtotal(b.id, this.sortJudgeId);
                } else if (this.sortBy === 'criterion') {
                    const rawA = this.getCriterionValue(a.id, this.sortJudgeId, this.sortCritId);
                    const rawB = this.getCriterionValue(b.id, this.sortJudgeId, this.sortCritId);
                    valA = parseFloat(rawA) || 0;
                    valB = parseFloat(rawB) || 0;
                } else if (this.sortBy === 'status') {
                    valA = this.isRegFullyScored(a.id) ? 2 : (this.isRegPartiallyScored(a.id) ? 1 : 0);
                    valB = this.isRegFullyScored(b.id) ? 2 : (this.isRegPartiallyScored(b.id) ? 1 : 0);
                } else {
                    valA = this.getParticipantRank(a.id) ?? 99999;
                    valB = this.getParticipantRank(b.id) ?? 99999;
                }

                if (valA < valB) return -1 * dir;
                if (valA > valB) return 1 * dir;
                return (a.draw_number ?? 99999) - (b.draw_number ?? 99999);
            });

            return list;
        },

        // Server-side Native Spreadsheet (.xlsx) Export
        exportRecapXls() {
            const params = new URLSearchParams({
                view_mode: this.viewMode,
                sector: this.sectorFilter,
                status: this.statusFilter,
                sort_by: this.sortBy,
                sort_dir: this.sortDirection
            });
            this.showToast('Menyiapkan file Excel (.xlsx)...', 'success');
            window.location.href = '{{ route("pic.scoring.export.excel", $competition->id) }}?' + params.toString();
        },

        get filteredParticipants() {
            return this.participants.filter(p => {
                // Search query
                if (this.searchQuery.trim() !== '') {
                    const q = this.searchQuery.toLowerCase();
                    const matchName = (p.name || '').toLowerCase().includes(q);
                    const matchInst = (p.institution || '').toLowerCase().includes(q);
                    const matchNo = (p.participant_number || '').toLowerCase().includes(q);
                    const matchDraw = String(p.draw_number || '').includes(q);
                    if (!matchName && !matchInst && !matchNo && !matchDraw) return false;
                }

                // Sector filter
                if (this.sectorFilter === 'PA' && p.gender !== 'L') return false;
                if (this.sectorFilter === 'PI' && p.gender !== 'P') return false;

                // Status filter
                if (this.statusFilter === 'scored' && !this.isRegFullyScored(p.id)) return false;
                if (this.statusFilter === 'unscored' && this.isRegFullyScored(p.id)) return false;

                return true;
            });
        },

        get scoredCount() {
            return this.participants.filter(p => this.isRegFullyScored(p.id)).length;
        },

        get scoringPercentage() {
            if (this.participants.length === 0) return 0;
            return Math.round((this.scoredCount / this.participants.length) * 100);
        },

        async toggleExpand(regId) {
            if (this.activeParticipantId && this.activeParticipantId !== regId && this.scoresData[this.activeParticipantId]) {
                await this.saveRegistrationScores(this.activeParticipantId);
            }
            this.activeParticipantId = this.activeParticipantId === regId ? null : regId;
            this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
        },

        // Helper getters & setters for criteria values
        getCriterionValue(regId, judgeId, critId) {
            const pScores = this.scoresData[regId] || {};
            const jScore = pScores[judgeId] || {};
            const crits = jScore.criteria || {};
            if (crits[critId] !== undefined && crits[critId] !== null && crits[critId] !== '') {
                return crits[critId];
            }
            if (crits[String(critId)] !== undefined && crits[String(critId)] !== null && crits[String(critId)] !== '') {
                return crits[String(critId)];
            }
            if (this.criteria && this.criteria.length === 1 && (jScore.total_score || 0) > 0) {
                return jScore.total_score;
            }
            return '';
        },

        setCriterionValue(regId, judgeId, critId, value) {
            if (!this.scoresData[regId]) this.scoresData[regId] = {};
            if (!this.scoresData[regId][judgeId]) {
                this.scoresData[regId][judgeId] = { criteria: {}, notes: '', total_score: 0, is_locked: true };
            }
            if (!this.scoresData[regId][judgeId].criteria) {
                this.scoresData[regId][judgeId].criteria = {};
            }
            this.scoresData[regId][judgeId].criteria[critId] = value;
            this.recalculateJudgeTotal(regId, judgeId);
        },

        getJudgeNotes(regId, judgeId) {
            const pScores = this.scoresData[regId] || {};
            const jScore = pScores[judgeId] || {};
            return jScore.notes || '';
        },

        setJudgeNotes(regId, judgeId, val) {
            if (!this.scoresData[regId]) this.scoresData[regId] = {};
            if (!this.scoresData[regId][judgeId]) {
                this.scoresData[regId][judgeId] = { criteria: {}, notes: '', total_score: 0, is_locked: true };
            }
            this.scoresData[regId][judgeId].notes = val;
        },

        recalculateJudgeTotal(regId, judgeId) {
            const pScores = this.scoresData[regId] || {};
            const jScore = pScores[judgeId] || {};
            const crits = jScore.criteria || {};

            let total = 0;
            let hasAnyInput = false;
            if (this.criteria && this.criteria.length > 0) {
                let totalWeight = this.criteria.reduce((sum, c) => sum + (parseFloat(c.weight_percentage) || 0), 0);
                if (!totalWeight || totalWeight <= 0) totalWeight = 100;

                this.criteria.forEach(c => {
                    const rawVal = crits[c.id] !== undefined ? crits[c.id] : (crits[String(c.id)] !== undefined ? crits[String(c.id)] : null);
                    if (rawVal !== null && rawVal !== '') {
                        hasAnyInput = true;
                        const val = parseFloat(rawVal) || 0;
                        const weight = parseFloat(c.weight_percentage) || (100 / this.criteria.length);
                        total += (val * (weight / totalWeight));
                    }
                });
            } else if (jScore.direct_score !== undefined) {
                total = parseFloat(jScore.direct_score) || 0;
                hasAnyInput = true;
            }

            if (hasAnyInput) {
                jScore.total_score = Math.round(total * 100) / 100;
            }
        },

        getJudgeSubtotal(regId, judgeId) {
            const pScores = this.scoresData[regId] || {};
            const jScore = pScores[judgeId] || {};
            return jScore.total_score || 0;
        },

        isJudgeScored(regId, judgeId) {
            const pScores = this.scoresData[regId] || {};
            const jScore = pScores[judgeId] || {};
            return (jScore.total_score || 0) > 0;
        },

        getScoredJudgesCount(regId) {
            const pScores = this.scoresData[regId] || {};
            let count = 0;
            this.judges.forEach(j => {
                if ((pScores[j.id]?.total_score || 0) > 0) count++;
            });
            return count;
        },

        isRegFullyScored(regId) {
            if (this.judges.length === 0) return false;
            return this.getScoredJudgesCount(regId) >= this.judges.length;
        },

        isRegPartiallyScored(regId) {
            const count = this.getScoredJudgesCount(regId);
            return count > 0 && count < this.judges.length;
        },

        getRegTotalScore(regId) {
            const pScores = this.scoresData[regId] || {};
            let sum = 0;
            this.judges.forEach(j => {
                const jTotal = pScores[j.id]?.total_score || 0;
                sum += jTotal;
            });
            return sum;
        },

        getRegAverage(regId) {
            const pScores = this.scoresData[regId] || {};
            let sum = 0;
            let count = 0;

            this.judges.forEach(j => {
                const jTotal = pScores[j.id]?.total_score || 0;
                if (jTotal > 0) {
                    sum += jTotal;
                    count++;
                }
            });

            return count > 0 ? (sum / count) : 0;
        },

        // Save scores via AJAX
        async saveRegistrationScores(regId) {
            if (this.isSaving[regId]) return;
            this.isSaving[regId] = true;

            const pScores = this.scoresData[regId] || {};
            const isLocked = this.lockState[regId] !== false;

            const payload = {
                scores: pScores,
                is_locked: isLocked
            };

            const url = '{{ route("pic.scoring.save", [":comp", ":reg"]) }}'
                .replace(':comp', this.competition.id)
                .replace(':reg', regId);

            try {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const data = await res.json();
                if (data.success) {
                    // Update scoresData with confirmed server state
                    if (data.scores) {
                        this.scoresData[regId] = { ...this.scoresData[regId], ...data.scores };
                    }
                    this.showToast('✓ ' + data.message, 'success');
                } else {
                    this.showToast('Gagal: ' + (data.message || 'Terjadi kesalahan.'), 'error');
                }
            } catch (err) {
                console.error(err);
                this.showToast('Gagal menghubungi server.', 'error');
            } finally {
                this.isSaving[regId] = false;
            }
        },

        async goToNextParticipant(currentRegId) {
            if (currentRegId && this.scoresData[currentRegId]) {
                await this.saveRegistrationScores(currentRegId);
            }
            const currIdx = this.filteredParticipants.findIndex(p => p.id === currentRegId);
            if (currIdx >= 0 && currIdx < this.filteredParticipants.length - 1) {
                const nextP = this.filteredParticipants[currIdx + 1];
                this.activeParticipantId = nextP.id;
                this.$nextTick(() => {
                    if (window.lucide) window.lucide.createIcons();
                    const el = document.getElementById('participant-card-' + nextP.id);
                    if (el) {
                        el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                });
            }
        },

        // Manage Judges Modal
        openJudgesModal() {
            this.editableJudges = JSON.parse(JSON.stringify(this.judges));
            if (this.editableJudges.length === 0) {
                this.editableJudges = [
                    { id: null, name: 'Dewan Juri 1', role_title: 'Juri 1' },
                    { id: null, name: 'Dewan Juri 2', role_title: 'Juri 2' },
                    { id: null, name: 'Dewan Juri 3', role_title: 'Juri 3' }
                ];
            }
            this.isJudgesModalOpen = true;
            this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
        },

        addJudgeRow() {
            const nextNum = this.editableJudges.length + 1;
            this.editableJudges.push({
                id: null,
                name: 'Dewan Juri ' + nextNum,
                role_title: 'Juri ' + nextNum
            });
            this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
        },

        removeJudgeRow(idx) {
            if (this.editableJudges.length <= 1) {
                alert('Minimal harus ada 1 Dewan Juri.');
                return;
            }
            this.editableJudges.splice(idx, 1);
        },

        async saveJudgesList() {
            if (this.isSavingJudges) return;
            this.isSavingJudges = true;

            const url = '{{ route("pic.scoring.judges", $competition->id) }}';

            try {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ judges: this.editableJudges })
                });

                const data = await res.json();
                if (data.success) {
                    this.judges = data.judges || this.editableJudges;
                    this.isJudgesModalOpen = false;
                    this.showToast('✓ ' + data.message, 'success');
                    this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
                } else {
                    alert(data.message || 'Gagal menyimpan nama dewan juri.');
                }
            } catch (err) {
                console.error(err);
                alert('Terjadi kesalahan jaringan.');
            } finally {
                this.isSavingJudges = false;
            }
        },

        handleGlobalKey(e) {
            // Save on Ctrl+Enter
            if (e.ctrlKey && e.code === 'Enter') {
                if (this.activeParticipantId) {
                    e.preventDefault();
                    this.saveRegistrationScores(this.activeParticipantId);
                }
            }
        },

        showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `fixed bottom-6 right-6 z-50 px-5 py-3 rounded-2xl text-xs font-black shadow-2xl flex items-center gap-2.5 transition-all transform duration-300 ${
                type === 'success' ? 'bg-emerald-500 text-slate-950 shadow-emerald-500/25' : 'bg-rose-500 text-white shadow-rose-500/25'
            }`;
            toast.innerHTML = `<span>${message}</span>`;
            document.body.appendChild(toast);

            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(10px)';
                setTimeout(() => toast.remove(), 300);
            }, 2500);
        }
    };
}
</script>
@endsection
