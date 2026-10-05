{{-- 
    Template Kartu Tanda Peserta Khusus Cabang Pramuka (Aryakasiga)
    Format presisi sesuai panduan fisik & revisi terbaru:
    1. Header Judul Event: Mengikuti tahun pelaksanaan lomba (misal: ARYAKASIGA 2026)
    2. Bagian Atas: Maskot Aryakasiga (Kiri) & Pasfoto Peserta (Kanan, sebatas sebelah logo)
    3. Bagian Nomor Regu: Dari Nomor Undian (draw_number) dengan warna tema khas
    4. Bagian Data: Nama Peserta, Regu, Pangkalan
    5. Footer: URL Resmi & QR Code Validasi Keabsahan
--}}
@php
    $themeColor = $competition->effective_card_theme_color ?? 'red';
    $boxColorClass = match($themeColor) {
        'brown' => 'bg-[#78350f] text-white',
        'blue' => 'bg-blue-600 text-white',
        'emerald' => 'bg-[#059669] text-white',
        'orange' => 'bg-orange-600 text-white',
        'purple' => 'bg-purple-600 text-white',
        'slate' => 'bg-slate-900 text-white',
        default => 'bg-[#dc2626] text-black', // Red Aryakasiga
    };
    $boxTextColor = in_array($themeColor, ['red', 'yellow', 'amber']) ? 'text-black' : 'text-white';

    $memberPhotoUrl = null;
    if (!empty($member->photo)) {
        $memberPhotoUrl = asset('storage/' . $member->photo);
    }

    $mascotUrl = $competition->effective_card_mascot_url;
    // Nomor Regu murni diambil dari Nomor Undian (draw_number)
    $reguNumber = $registration->draw_number ? (string) $registration->draw_number : '-';
    $qrUrl = \App\Services\QrSignatureService::registrationFormUrl($registration);
@endphp

<div class="aryakasiga-card bg-white text-black border-2 border-black flex flex-col justify-between overflow-hidden relative" style="width: 65mm; height: 105mm; max-width: 65mm; max-height: 105mm; box-sizing: border-box; page-break-inside: avoid;">
    
    <!-- 1. Header Judul Event (B2 ~7mm) - Dinamis Mengikuti Tahun Pelaksanaan -->
    <div class="border-b-2 border-black py-1 px-1 text-center bg-white shrink-0" style="height: 7mm;">
        <h2 class="font-black text-[11px] uppercase tracking-wider text-black font-sans leading-none truncate">
            {{ $competition->effective_card_title }}
        </h2>
    </div>

    <!-- 2. Mid Section: Maskot (Kiri) & Pasfoto (Kanan, Sebatas Sebelah Logo) (~32mm) -->
    <div class="grid grid-cols-2 border-b-2 border-black shrink-0" style="height: 32mm;">
        
        <!-- Sisi Kiri: Maskot Aryakasiga -->
        <div class="border-r-2 border-black flex items-center justify-center p-1 bg-white overflow-hidden">
            @if($mascotUrl)
                <img src="{{ $mascotUrl }}" alt="Maskot" class="max-h-[28mm] max-w-full object-contain">
            @else
                <div class="w-12 h-12 rounded-full border border-black flex items-center justify-center font-bold text-[9px]">
                    PRAMUKA
                </div>
            @endif
        </div>

        <!-- Sisi Kanan: Pasfoto 3x4 (Sebatas Sebelah Logo) -->
        <div class="flex items-center justify-center bg-slate-100 relative overflow-hidden">
            @if($memberPhotoUrl)
                <img src="{{ $memberPhotoUrl }}" alt="{{ $member->full_name }}" class="w-full h-full object-cover object-top">
            @else
                <!-- Fallback Box jika belum upload foto -->
                <div class="w-full h-full bg-[#dc2626] flex flex-col items-center justify-center text-white p-1 text-center">
                    <span class="text-[10px] font-black tracking-wider uppercase leading-tight">PASFOTO 3x4</span>
                    <span class="text-[7.5px] opacity-90 mt-0.5">Background Merah</span>
                </div>
            @endif
        </div>
    </div>

    <!-- 3. Section No Regu: Murni dari Nomor Undian (~18mm) -->
    <div class="border-b-2 border-black flex flex-col shrink-0" style="height: 18mm;">
        <!-- Label No Regu -->
        <div class="border-b border-black text-center py-0.5 bg-white">
            <span class="block text-[8.5px] font-bold text-black uppercase tracking-wider leading-none">
                {{ $competition->effective_card_number_label }}
            </span>
        </div>

        <!-- Box No Regu / Nomor Undian -->
        <div class="{{ $boxColorClass }} flex-1 flex items-center justify-center">
            <span class="font-black text-2xl font-mono leading-none tracking-tight {{ $boxTextColor }}">
                {{ $reguNumber }}
            </span>
        </div>
    </div>

    <!-- 4. Lower Section: Tabel Rincian Data Peserta (~42mm) -->
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

    <!-- 5. Footer: URL & QR Code Validasi Sah (~6mm) -->
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
