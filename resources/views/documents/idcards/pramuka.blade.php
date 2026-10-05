{{-- 
    Template Kartu Tanda Peserta Khusus Cabang Pramuka (Aryakasiga)
    Format 100% presisi sesuai contoh fisik resmi:
    - Box Header Judul Event (ARYAKASIGA 2025)
    - Kolom Kiri: Maskot Pramuka + Kotak No Regu Merah
    - Kolom Kanan: Pasfoto Peserta (3x4)
    - Baris Data: Nama, Regu, Pangkalan
    - Footer: URL Resmi & QR Code Validasi Keabsahan
--}}
@php
    $themeColor = $competition->effective_card_theme_color ?? 'red';
    $boxColorClass = match($themeColor) {
        'brown' => 'bg-[#78350f] text-white',
        'blue' => 'bg-blue-600 text-white',
        'emerald' => 'bg-emerald-600 text-white',
        'orange' => 'bg-orange-600 text-white',
        'purple' => 'bg-purple-600 text-white',
        'slate' => 'bg-slate-900 text-white',
        default => 'bg-[#e11d48] text-white', // Red Aryakasiga
    };
    $borderColor = match($themeColor) {
        'brown' => 'border-[#78350f]',
        'blue' => 'border-blue-600',
        'emerald' => 'border-emerald-600',
        'orange' => 'border-orange-600',
        'purple' => 'border-purple-600',
        'slate' => 'border-slate-800',
        default => 'border-black',
    };

    $memberPhotoUrl = null;
    if (!empty($member->photo)) {
        $memberPhotoUrl = asset('storage/' . $member->photo);
    }

    $mascotUrl = $competition->effective_card_mascot_url;
    $reguNumber = $registration->draw_number ?? $registration->participant_number ?? '-';
    $qrUrl = \App\Services\QrSignatureService::registrationFormUrl($registration);
@endphp

<div class="aryakasiga-card bg-white text-black border-2 border-black flex flex-col justify-between overflow-hidden relative" style="width: 65mm; height: 105mm; max-width: 65mm; max-height: 105mm; box-sizing: border-box; page-break-inside: avoid;">
    
    <!-- 1. Header Judul Event (B2 ~7mm) -->
    <div class="border-b-2 border-black py-1 px-1 text-center bg-white shrink-0" style="height: 7mm;">
        <h2 class="font-black text-[11px] uppercase tracking-wider text-black font-sans leading-none truncate">
            {{ $competition->effective_card_title }}
        </h2>
    </div>

    <!-- 2. Mid Section: Maskot + No Regu (Kiri) & Pasfoto (Kanan) (~48mm) -->
    <div class="grid grid-cols-12 border-b-2 border-black shrink-0" style="height: 48mm;">
        
        <!-- Sisi Kiri: Maskot + No Regu (5 cols ~27mm) -->
        <div class="col-span-5 border-r-2 border-black flex flex-col justify-between bg-white">
            <!-- Maskot -->
            <div class="flex-1 flex items-center justify-center p-1 overflow-hidden">
                @if($mascotUrl)
                    <img src="{{ $mascotUrl }}" alt="Maskot" class="max-h-[23mm] max-w-full object-contain">
                @else
                    <div class="w-10 h-10 rounded-full border border-black flex items-center justify-center font-bold text-[9px]">
                        PRAMUKA
                    </div>
                @endif
            </div>

            <!-- Label No Regu -->
            <div class="border-t-2 border-black text-center py-0.5 bg-white">
                <span class="block text-[8.5px] font-bold text-black uppercase tracking-tight leading-none">
                    {{ $competition->effective_card_number_label }}
                </span>
            </div>

            <!-- Box No Regu Merah (Presisi Sesuai Contoh Fisik) -->
            <div class="{{ $boxColorClass }} border-t-2 border-black text-center flex items-center justify-center" style="height: 14mm;">
                <span class="font-black text-2xl font-mono leading-none tracking-tight text-black">
                    {{ $reguNumber }}
                </span>
            </div>
        </div>

        <!-- Sisi Kanan: Pasfoto 3x4 (7 cols ~38mm, edge-to-edge) -->
        <div class="col-span-7 flex items-center justify-center bg-slate-100 relative overflow-hidden">
            @if($memberPhotoUrl)
                <img src="{{ $memberPhotoUrl }}" alt="{{ $member->full_name }}" class="w-full h-full object-cover object-top">
            @else
                <!-- Fallback Box jika belum upload foto -->
                <div class="w-full h-full bg-[#dc2626] flex flex-col items-center justify-center text-white p-2 text-center">
                    <span class="text-[11px] font-black tracking-wider uppercase leading-tight">PASFOTO 3x4</span>
                    <span class="text-[8px] opacity-90 mt-1">Background Merah</span>
                </div>
            @endif
        </div>
    </div>

    <!-- 3. Lower Section: Tabel Rincian Data Peserta (~44mm) -->
    <div class="flex flex-col text-center font-sans flex-1 justify-around">
        
        <!-- Baris Nama -->
        <div class="border-b-2 border-black py-1 px-1 bg-white flex flex-col justify-center" style="min-height: 14mm;">
            <span class="block text-[8px] font-semibold text-slate-600 leading-none mb-0.5">Nama</span>
            <span class="block text-[11px] font-black uppercase text-black truncate leading-tight tracking-tight">
                {{ $member->full_name }}
            </span>
        </div>

        <!-- Baris Regu -->
        <div class="border-b-2 border-black py-1 px-1 bg-white flex flex-col justify-center" style="min-height: 13mm;">
            <span class="block text-[8px] font-semibold text-slate-600 leading-none mb-0.5">
                {{ $competition->effective_card_team_label }}
            </span>
            <span class="block text-[11px] font-black text-black truncate leading-tight">
                {{ $registration->team_name ?: ($registration->sub_category ?: '-') }}
            </span>
        </div>

        <!-- Baris Pangkalan / Asal Sekolah -->
        <div class="border-b-2 border-black py-1 px-1 bg-white flex flex-col justify-center" style="min-height: 14mm;">
            <span class="block text-[8px] font-semibold text-slate-600 leading-none mb-0.5">
                {{ $competition->effective_card_school_label }}
            </span>
            <span class="block text-[10.5px] font-black uppercase text-black truncate leading-tight">
                {{ $registration->display_school ?? $member->school_name ?? $registration->institution_name }}
            </span>
        </div>

    </div>

    <!-- 4. Footer: URL & QR Code Validasi Sah (~6mm) -->
    <div class="py-0.5 px-1.5 flex items-center justify-between bg-white text-[8px] shrink-0" style="height: 6mm;">
        <span class="font-mono text-slate-700 truncate max-w-[44mm] leading-none">
            {{ $competition->effective_card_footer_text }}
        </span>
        <div class="shrink-0 flex items-center gap-1">
            {!! \App\Services\QrSignatureService::generateSvg($qrUrl, 16) !!}
            <span class="text-[7px] font-bold uppercase tracking-tighter text-emerald-800 leading-none">VALID</span>
        </div>
    </div>

</div>
