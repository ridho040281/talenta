{{-- 
    Template Kartu Tanda Peserta Khusus Cabang Robotik
    Format modern cyber tech:
    - Header: TALENTA 2026 - ROBOTIK
    - Kolom Kiri: Maskot Robot + Box No Tim
    - Kolom Kanan: Pasfoto Anggota Tim (3x4)
    - Baris Data: Nama Anggota, Kategori Robotik (Sumo/Soccer/Kreatif), Nama Tim, Asal Sekolah
    - Footer: URL Resmi & QR Code Validasi Keabsahan
--}}
@php
    $themeColor = $competition->effective_card_theme_color ?? 'blue';
    $boxColorClass = match($themeColor) {
        'red' => 'bg-red-600 text-white',
        'emerald' => 'bg-emerald-600 text-white',
        'orange' => 'bg-orange-600 text-white',
        'purple' => 'bg-purple-600 text-white',
        default => 'bg-[#2563eb] text-white', // Cyber Blue
    };

    $memberPhotoUrl = null;
    if (!empty($member->photo)) {
        $memberPhotoUrl = asset('storage/' . $member->photo);
    }

    $mascotUrl = $competition->effective_card_mascot_url;
    $reguNumber = $registration->draw_number ? ('#' . $registration->draw_number) : ($registration->participant_number ?? '-');
    $qrUrl = \App\Services\QrSignatureService::registrationFormUrl($registration);
@endphp

<div class="robotik-card bg-white text-slate-900 border-2 border-slate-900 flex flex-col justify-between overflow-hidden relative" style="width: 65mm; height: 105mm; max-width: 65mm; max-height: 105mm; box-sizing: border-box; page-break-inside: avoid;">
    
    <!-- 1. Header Judul Event (B2 ~7mm) -->
    <div class="border-b-2 border-slate-900 py-1 px-1 text-center bg-slate-900 text-white shrink-0" style="height: 7mm;">
        <h2 class="font-black text-[10px] uppercase tracking-wider text-cyan-300 font-sans leading-none truncate">
            {{ $competition->effective_card_title }}
        </h2>
    </div>

    <!-- 2. Mid Section: Maskot + No Tim (Kiri) & Pasfoto (Kanan) (~48mm) -->
    <div class="grid grid-cols-12 border-b-2 border-slate-900 shrink-0" style="height: 48mm;">
        
        <!-- Sisi Kiri: Maskot + No Tim (Lebar 35% ~23mm) -->
        <div class="col-span-5 border-r-2 border-slate-900 flex flex-col justify-between bg-slate-50 p-1">
            <!-- Maskot Robot / Logo -->
            <div class="flex-1 flex items-center justify-center p-0.5 overflow-hidden">
                @if($mascotUrl)
                    <img src="{{ $mascotUrl }}" alt="Maskot" class="max-h-[22mm] max-w-full object-contain">
                @else
                    <div class="w-10 h-10 rounded-xl bg-blue-100 border border-blue-400 flex items-center justify-center font-bold text-[9px] text-blue-700">
                        🤖 ROBOT
                    </div>
                @endif
            </div>

            <!-- Box No Tim -->
            <div class="border-t-2 border-slate-900 text-center pt-0.5 pb-0.5">
                <span class="block text-[8px] font-bold text-slate-700 uppercase tracking-tight leading-none mb-0.5">
                    {{ $competition->effective_card_number_label }}
                </span>
                <div class="{{ $boxColorClass }} mx-auto py-0.5 px-1 rounded-sm border border-slate-900 flex items-center justify-center" style="height: 13mm;">
                    <span class="font-black text-sm font-mono leading-none tracking-tight">
                        {{ $reguNumber }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Sisi Kanan: Pasfoto 3x4 (Lebar 65% ~42mm) -->
        <div class="col-span-7 flex items-center justify-center p-1 bg-slate-100 relative overflow-hidden">
            @if($memberPhotoUrl)
                <img src="{{ $memberPhotoUrl }}" alt="{{ $member->full_name }}" class="object-cover object-top rounded-sm border border-slate-300" style="aspect-ratio: 3/4; max-height: 46mm; max-width: 100%;">
            @else
                <div class="w-full h-full bg-blue-600 flex flex-col items-center justify-center text-white p-1 rounded-sm text-center" style="aspect-ratio: 3/4;">
                    <span class="text-[10px] font-black tracking-wider uppercase leading-tight">PASFOTO 3x4</span>
                    <span class="text-[8px] opacity-80 mt-0.5">Background Biru/Merah</span>
                </div>
            @endif
        </div>
    </div>

    <!-- 3. Lower Section: Tabel Rincian Data Peserta (~44mm) -->
    <div class="flex flex-col text-center font-sans flex-1 justify-around">
        
        <!-- Baris Nama -->
        <div class="border-b border-slate-900 py-1 px-1 bg-slate-50 flex flex-col justify-center" style="min-height: 14mm;">
            <span class="block text-[8px] font-semibold text-slate-500 leading-none mb-0.5">Nama Peserta</span>
            <span class="block text-[11px] font-black uppercase text-slate-900 truncate leading-tight tracking-tight">
                {{ $member->full_name }}
            </span>
        </div>

        <!-- Baris Kategori Robotik -->
        <div class="border-b border-slate-900 py-1 px-1 flex flex-col justify-center" style="min-height: 13mm;">
            <span class="block text-[8px] font-semibold text-slate-500 leading-none mb-0.5">
                {{ $competition->effective_card_team_label }}
            </span>
            <span class="block text-[10px] font-bold text-blue-700 truncate leading-tight">
                {{ $registration->sub_category ?: ($registration->target_class ?: 'Robotik') }}
                @if($registration->team_name)
                    <span class="text-slate-800 font-black">({{ $registration->team_name }})</span>
                @endif
            </span>
        </div>

        <!-- Baris Asal Sekolah -->
        <div class="border-b border-slate-900 py-1 px-1 bg-slate-50 flex flex-col justify-center" style="min-height: 14mm;">
            <span class="block text-[8px] font-semibold text-slate-500 leading-none mb-0.5">
                {{ $competition->effective_card_school_label }}
            </span>
            <span class="block text-[10px] font-black uppercase text-slate-900 truncate leading-tight">
                {{ $registration->display_school ?? $member->school_name ?? $registration->institution_name }}
            </span>
        </div>

    </div>

    <!-- 4. Footer: URL & QR Code Validasi Sah (~6mm) -->
    <div class="py-0.5 px-1.5 flex items-center justify-between bg-white text-[8px] shrink-0" style="height: 6mm;">
        <span class="font-mono text-slate-600 truncate max-w-[44mm] leading-none">
            {{ $competition->effective_card_footer_text }}
        </span>
        <div class="shrink-0 flex items-center gap-1">
            {!! \App\Services\QrSignatureService::generateSvg($qrUrl, 16) !!}
            <span class="text-[7px] font-bold uppercase tracking-tighter text-blue-700 leading-none">OFFICIAL</span>
        </div>
    </div>

</div>
