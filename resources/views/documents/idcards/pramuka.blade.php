{{-- 
    Template Kartu Tanda Peserta Khusus Cabang Pramuka (Aryakasiga)
    Format presisi sesuai panduan fisik & revisi terbaru:
    1. Header Judul Event: Mengikuti tahun pelaksanaan lomba (misal: ARYAKASIGA 2026)
    2. Bagian Atas: Maskot Aryakasiga (Kiri, 39.5mm) & Pasfoto Peserta (Kanan, 25.5mm rasio 3x4)
    3. Bagian Nomor Regu: Dari Nomor Undian (draw_number) dengan warna tema merah Aryakasiga
    4. Bagian Data: Nama Peserta, Regu, Pangkalan
    5. Footer: URL Resmi & QR Code Validasi Keabsahan
--}}
@php
    $themeColor = $competition->effective_card_theme_color ?? 'red';
    $boxBgColor = match($themeColor) {
        'brown' => '#78350f',
        'blue' => '#2563eb',
        'emerald' => '#059669',
        'orange' => '#ea580c',
        'purple' => '#9333ea',
        'slate' => '#0f172a',
        default => '#dc2626', // Red Aryakasiga
    };
    $boxTextColorHex = in_array($themeColor, ['red', 'yellow', 'amber']) ? '#000000' : '#ffffff';
    if ($themeColor === 'red' || empty($competition->card_theme_color)) {
        $boxBgColor = '#dc2626';
        $boxTextColorHex = '#000000';
    }

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
    
    <!-- 1. Header Judul Event (B2 ~7.5mm) - Dinamis Mengikuti Tahun Pelaksanaan -->
    <div class="border-b-2 border-black py-1 px-1 text-center bg-white shrink-0 flex items-center justify-center" style="height: 7.5mm; width: 65mm;">
        <h2 class="font-black text-[11px] uppercase tracking-wider text-black font-sans leading-none truncate">
            {{ $competition->effective_card_title }}
        </h2>
    </div>

    <!-- 2. Mid Section: Maskot (Kiri 39.5mm) & Pasfoto Rasio 3x4 (Kanan 25.5mm x 34mm) -->
    <div class="flex border-b-2 border-black shrink-0 overflow-hidden" style="height: 34mm; width: 65mm;">
        
        <!-- Sisi Kiri: Maskot Aryakasiga (Lebar 39.5mm) -->
        <div class="border-r-2 border-black flex items-center justify-center p-1 bg-white overflow-hidden shrink-0" style="width: 39.5mm; height: 34mm;">
            @if($mascotUrl)
                <img src="{{ $mascotUrl }}" alt="Maskot" class="max-h-[30mm] max-w-[36mm] object-contain">
            @else
                <div class="w-12 h-12 rounded-full border border-black flex items-center justify-center font-bold text-[9px]">
                    PRAMUKA
                </div>
            @endif
        </div>

        <!-- Sisi Kanan: Pasfoto Peserta Murni Rasio 3x4 (Lebar 25.5mm x Tinggi 34mm = 3:4) -->
        <div class="flex items-center justify-center relative overflow-hidden shrink-0" style="width: 25.5mm; height: 34mm; background-color: #dc2626;">
            @if($memberPhotoUrl)
                <img src="{{ $memberPhotoUrl }}" alt="{{ $member->full_name }}" style="width: 25.5mm; height: 34mm; aspect-ratio: 3/4; object-fit: cover; object-position: center top; display: block;">
            @else
                <!-- Fallback Box jika belum upload foto -->
                <div class="w-full h-full flex flex-col items-center justify-center text-white p-1 text-center" style="background-color: #dc2626;">
                    <span class="text-[10px] font-black tracking-wider uppercase leading-tight">PASFOTO 3x4</span>
                    <span class="text-[7.5px] opacity-90 mt-0.5">Background Merah</span>
                </div>
            @endif
        </div>
    </div>

    <!-- 3. Section No Regu: Murni dari Nomor Undian (Tinggi 16.5mm) -->
    <div class="border-b-2 border-black flex flex-col shrink-0" style="height: 16.5mm; width: 65mm;">
        <!-- Label No Regu -->
        <div class="border-b border-black text-center py-0.5 bg-white shrink-0" style="height: 5mm;">
            <span class="block text-[8.5px] font-bold text-black uppercase tracking-wider leading-none">
                {{ $competition->effective_card_number_label }}
            </span>
        </div>

        <!-- Box No Regu / Nomor Undian -->
        <div class="flex-1 flex items-center justify-center" style="background-color: {{ $boxBgColor }}; color: {{ $boxTextColorHex }};">
            <span class="font-black text-2xl font-mono leading-none tracking-tight">
                {{ $reguNumber }}
            </span>
        </div>
    </div>

    <!-- 4. Lower Section: Tabel Rincian Data Peserta (~41mm) -->
    <div class="flex flex-col text-center font-sans flex-1 justify-around shrink-0" style="height: 41mm; width: 65mm;">
        
        <!-- Baris Nama -->
        <div class="border-b-2 border-black py-1 px-1 bg-white flex flex-col justify-center" style="height: 14mm;">
            <span class="block text-[8px] font-semibold text-slate-600 leading-none mb-0.5">Nama</span>
            <span class="block text-[11px] font-black uppercase text-black truncate leading-tight tracking-tight">
                {{ $member->full_name }}
            </span>
        </div>

        <!-- Baris Regu -->
        <div class="border-b-2 border-black py-1 px-1 bg-white flex flex-col justify-center" style="height: 13.5mm;">
            <span class="block text-[8px] font-semibold text-slate-600 leading-none mb-0.5">
                {{ $competition->effective_card_team_label }}
            </span>
            <span class="block text-[11px] font-black text-black truncate leading-tight">
                {{ $registration->team_name ?: ($registration->sub_category ?: '-') }}
            </span>
        </div>

        <!-- Baris Pangkalan / Asal Sekolah -->
        <div class="border-b-2 border-black py-1 px-1 bg-white flex flex-col justify-center" style="height: 13.5mm;">
            <span class="block text-[8px] font-semibold text-slate-600 leading-none mb-0.5">
                {{ $competition->effective_card_school_label }}
            </span>
            <span class="block text-[10.5px] font-black uppercase text-black truncate leading-tight">
                {{ $registration->display_school ?? $member->school_name ?? $registration->institution_name }}
            </span>
        </div>

    </div>

    <!-- 5. Footer: URL & QR Code Validasi Sah (~6mm) -->
    <div class="py-0.5 px-1.5 flex items-center justify-between bg-white text-[8px] shrink-0" style="height: 6mm; width: 65mm;">
        <span class="font-mono text-slate-700 truncate max-w-[44mm] leading-none">
            {{ $competition->effective_card_footer_text }}
        </span>
        <div class="shrink-0 flex items-center gap-1">
            {!! \App\Services\QrSignatureService::generateSvg($qrUrl, 16) !!}
            <span class="text-[7px] font-bold uppercase tracking-tighter text-emerald-800 leading-none">VALID</span>
        </div>
    </div>

</div>
