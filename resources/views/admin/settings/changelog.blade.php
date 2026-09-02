@extends('layouts.admin')

@section('title', 'Changelog & Riwayat Pembaruan Sistem')
@section('page_title', 'Changelog & Catatan Rilis')

@section('content')
<div class="max-w-4xl mx-auto space-y-8">
    
    <!-- Header (AIStarterKit Dark Style) -->
    <div class="ai-card rounded-3xl p-6 sm:p-8 border border-white/[0.08] shadow-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-4 text-white">
        <div>
            <span class="px-3 py-1 text-xs font-bold rounded-lg bg-[#4E6EFF]/20 text-[#84D0FF] border border-[#4E6EFF]/30">
                Dokumentasi Versi Sistem
            </span>
            <h2 class="text-2xl sm:text-3xl font-black text-white mt-2 font-display">Changelog TALENTA Platform</h2>
            <p class="text-xs text-slate-400">Catatan riwayat rilis fitur, peningkatan performa, dan pembaruan arsitektur aplikasi</p>
        </div>

        <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-white/[0.06] hover:bg-white/[0.12] text-slate-300 hover:text-white font-bold text-xs border border-white/[0.08] transition shadow-sm">
            Kembali
        </a>
    </div>

    <!-- Timeline Container -->
    <div class="space-y-6">
        @foreach($changelogs as $item)
            <div class="ai-card rounded-3xl p-6 sm:p-8 border border-white/[0.08] shadow-2xl space-y-4 hover:border-white/[0.15] transition text-white">
                
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-white/[0.08] pb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl gradient-btn text-white flex items-center justify-center font-mono font-black text-sm shadow-md shadow-[#7A5AF8]/30">
                            {{ $item['version'] }}
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-white font-display">{{ $item['title'] }}</h3>
                            <span class="text-xs text-slate-400 font-mono">Dirilis pada: {{ $item['date'] }}</span>
                        </div>
                    </div>

                    <span class="px-3 py-1 text-xs font-bold rounded-full border border-white/[0.1] bg-white/[0.05] text-slate-300 self-start sm:self-auto">
                        {{ $item['badge'] }}
                    </span>
                </div>

                <ul class="space-y-2 text-xs sm:text-sm text-slate-300 pl-2">
                    @foreach($item['changes'] as $change)
                        <li class="flex items-start gap-2.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 shrink-0 mt-2"></span>
                            <span class="leading-relaxed">{{ $change }}</span>
                        </li>
                    @endforeach
                </ul>

            </div>
        @endforeach
    </div>

</div>
@endsection
