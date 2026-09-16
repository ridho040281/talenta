<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CETAK SERTIFIKAT & PIAGAM - TALENTA MTsN 1 BLITAR</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=Playfair+Display:ital,wght@0,700;0,900;1,700&display=swap" rel="stylesheet">
    
    <!-- Lucide Icons -->
    <script src="{{ asset('vendor/lucide/lucide.min.js') }}"></script>

    <style>
        @page {
            size: A4 landscape;
            margin: 0;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #334155;
            color: #0f172a;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* Screen Presentation */
        .print-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 60px 20px 40px;
            gap: 30px;
        }

        .certificate-sheet {
            position: relative;
            width: 297mm;
            height: 210mm;
            background-color: #ffffff;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35);
            overflow: hidden;
            page-break-inside: avoid;
            page-break-after: always;
        }

        .certificate-sheet:last-child {
            page-break-after: auto;
        }

        .cert-background {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: fill;
            z-index: 1;
            pointer-events: none;
        }

        .cert-overlay {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            z-index: 10;
        }

        .cert-text-element {
            position: absolute;
            transform: translate(-50%, -50%);
            white-space: nowrap;
            text-align: center;
            line-height: 1.2;
        }

        .font-serif {
            font-family: 'Playfair Display', Georgia, serif;
        }

        .font-sans {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        /* Floating Non-Print Toolbar */
        .floating-toolbar {
            position: fixed;
            top: 15px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(15, 23, 42, 0.9);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.15);
            padding: 8px 18px;
            border-radius: 9999px;
            display: flex;
            align-items: center;
            gap: 12px;
            z-index: 99999;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.5);
            color: #ffffff;
            font-size: 13px;
            font-weight: 600;
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
            text-decoration: none;
        }

        .btn-print {
            background: linear-gradient(135deg, #7A5AF8, #4E6EFF);
            color: white;
            box-shadow: 0 4px 12px rgba(122, 90, 248, 0.35);
        }

        .btn-print:hover {
            opacity: 0.9;
        }

        .btn-close {
            background: rgba(255, 255, 255, 0.1);
            color: #e2e8f0;
        }

        .btn-close:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        /* Default Elegant Frame when no background uploaded */
        .default-frame {
            position: absolute;
            inset: 0;
            padding: 18mm;
            border: 14mm solid #1e3a8a;
            outline: 2mm solid #d97706;
            outline-offset: -8mm;
            background: radial-gradient(circle at center, #ffffff 0%, #fffbeb 100%);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            pointer-events: none;
        }

        .default-frame-inner {
            border: 1px solid #d97706;
            height: 100%;
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            padding: 10mm;
        }

        /* Print Media Styles */
        @media print {
            body {
                background: none !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .floating-toolbar {
                display: none !important;
            }

            .print-container {
                padding: 0 !important;
                gap: 0 !important;
            }

            .certificate-sheet {
                width: 297mm !important;
                height: 210mm !important;
                margin: 0 !important;
                box-shadow: none !important;
                page-break-inside: avoid !important;
                page-break-after: always !important;
            }

            .certificate-sheet:last-child {
                page-break-after: auto !important;
            }
        }
    </style>
</head>
<body>

    <!-- Floating Print Control Toolbar -->
    <div class="floating-toolbar">
        <span>📄 {{ count($certificateItems) }} Lembar Sertifikat</span>
        <button type="button" onclick="window.print()" class="btn-action btn-print">
            <i data-lucide="printer" style="width: 15px; height: 15px;"></i>
            <span>Cetak / Simpan PDF (Ctrl+P)</span>
        </button>
        <button type="button" onclick="window.close()" class="btn-action btn-close">
            <i data-lucide="x" style="width: 15px; height: 15px;"></i>
            <span>Tutup</span>
        </button>
    </div>

    @php
        $layout = $template->effective_layout;
        $bgUrl = $template->background_path ? asset('storage/' . $template->background_path) : null;
    @endphp

    <div class="print-container">
        @foreach($certificateItems as $item)
        <div class="certificate-sheet">

            <!-- 1. Background Blangko Gambar (Jika Ada) -->
            @if($bgUrl)
                <img src="{{ $bgUrl }}" alt="Certificate Template" class="cert-background">
            @else
                <!-- 2. Fallback Desain Blangko Elegan Bawaan CSS jika belum upload -->
                <div class="default-frame">
                    <div class="default-frame-inner">
                        <div style="text-align: center; margin-top: 5mm;">
                            <h4 style="font-size: 13pt; letter-spacing: 2px; color: #1e3a8a; text-transform: uppercase; font-weight: 800;">KEMENTERIAN AGAMA REPUBLIK INDONESIA</h4>
                            <h2 style="font-size: 18pt; letter-spacing: 1.5px; color: #0f172a; text-transform: uppercase; font-weight: 900; margin-top: 2mm;">MADRASAH TSANAWIYAH NEGERI 1 BLITAR</h2>
                            <p style="font-size: 10pt; color: #64748b; margin-top: 1mm;">AJANG KREASI, PRESTASI & SENI (TALENTA) TINGKAT SD/MI SEDERAJAT</p>
                        </div>
                        <div style="text-align: center; margin-bottom: 8mm;">
                            <div style="font-size: 11pt; color: #334155;">Mengetahui,</div>
                            <div style="font-size: 12pt; font-weight: 800; color: #0f172a; margin-top: 15mm; text-decoration: underline;">SIHABUDIN, M.Pd.</div>
                            <div style="font-size: 10pt; color: #64748b;">Kepala MTsN 1 Blitar</div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- 3. Lapisan Teks Overlay Dinamis Sesuai Koordinat Persentase -->
            <div class="cert-overlay">
                @php
                    $calcTx = fn($align) => ($align === 'left') ? 'translate(0, -50%)' : (($align === 'right') ? 'translate(-100%, -50%)' : 'translate(-50%, -50%)');
                @endphp

                <!-- Nomor Sertifikat -->
                @if(!empty($layout['nomor']['visible']))
                @php $aNomor = $layout['nomor']['align'] ?? 'center'; @endphp
                <div class="cert-text-element {{ ($layout['nomor']['font'] ?? 'sans') === 'serif' ? 'font-serif' : 'font-sans' }}"
                     style="top: {{ $layout['nomor']['top'] }}%; left: {{ $layout['nomor']['left'] }}%; transform: {{ $calcTx($aNomor) }}; text-align: {{ $aNomor }}; font-size: {{ $layout['nomor']['size'] }}px; color: {{ $layout['nomor']['color'] }}; font-weight: {{ !empty($layout['nomor']['bold']) ? 'bold' : 'normal' }};">
                    Nomor: {{ $item['cert_number'] }}
                </div>
                @endif

                <!-- Nama Penerima (Juara / Peserta / Guru / Juri) -->
                @if(!empty($layout['nama']['visible']))
                @php $aNama = $layout['nama']['align'] ?? 'center'; @endphp
                <div class="cert-text-element {{ ($layout['nama']['font'] ?? 'serif') === 'serif' ? 'font-serif' : 'font-sans' }}"
                     style="top: {{ $layout['nama']['top'] }}%; left: {{ $layout['nama']['left'] }}%; transform: {{ $calcTx($aNama) }}; text-align: {{ $aNama }}; font-size: {{ $layout['nama']['size'] }}px; color: {{ $layout['nama']['color'] }}; font-weight: {{ !empty($layout['nama']['bold']) ? 'bold' : 'normal' }}; letter-spacing: 0.5px;">
                    {{ $item['name'] }}
                </div>
                @endif

                <!-- Asal Sekolah / Madrasah / Lembaga -->
                @if(!empty($layout['sekolah']['visible']))
                @php $aSekolah = $layout['sekolah']['align'] ?? 'center'; @endphp
                <div class="cert-text-element {{ ($layout['sekolah']['font'] ?? 'sans') === 'serif' ? 'font-serif' : 'font-sans' }}"
                     style="top: {{ $layout['sekolah']['top'] }}%; left: {{ $layout['sekolah']['left'] }}%; transform: {{ $calcTx($aSekolah) }}; text-align: {{ $aSekolah }}; font-size: {{ $layout['sekolah']['size'] }}px; color: {{ $layout['sekolah']['color'] }}; font-weight: {{ !empty($layout['sekolah']['bold']) ? 'bold' : 'normal' }};">
                    {{ $item['institution'] }}
                </div>
                @endif

                <!-- Predikat Juara / Kategori -->
                @if(!empty($layout['predikat']['visible']))
                @php $aPredikat = $layout['predikat']['align'] ?? 'center'; @endphp
                <div class="cert-text-element {{ ($layout['predikat']['font'] ?? 'sans') === 'serif' ? 'font-serif' : 'font-sans' }}"
                     style="top: {{ $layout['predikat']['top'] }}%; left: {{ $layout['predikat']['left'] }}%; transform: {{ $calcTx($aPredikat) }}; text-align: {{ $aPredikat }}; font-size: {{ $layout['predikat']['size'] }}px; color: {{ $layout['predikat']['color'] }}; font-weight: {{ !empty($layout['predikat']['bold']) ? 'bold' : 'normal' }};">
                    {{ $item['predikat'] }}
                </div>
                @endif

                <!-- Cabang Lomba -->
                @if(!empty($layout['lomba']['visible']))
                @php $aLomba = $layout['lomba']['align'] ?? 'center'; @endphp
                <div class="cert-text-element {{ ($layout['lomba']['font'] ?? 'sans') === 'serif' ? 'font-serif' : 'font-sans' }}"
                     style="top: {{ $layout['lomba']['top'] }}%; left: {{ $layout['lomba']['left'] }}%; transform: {{ $calcTx($aLomba) }}; text-align: {{ $aLomba }}; font-size: {{ $layout['lomba']['size'] }}px; color: {{ $layout['lomba']['color'] }}; font-weight: {{ !empty($layout['lomba']['bold']) ? 'bold' : 'normal' }};">
                    {{ $item['competition_name'] }}
                </div>
                @endif

                <!-- Tanggal Titimangsa Penerbitan -->
                @if(!empty($layout['tanggal']['visible']))
                @php $aTanggal = $layout['tanggal']['align'] ?? 'center'; @endphp
                <div class="cert-text-element {{ ($layout['tanggal']['font'] ?? 'sans') === 'serif' ? 'font-serif' : 'font-sans' }}"
                     style="top: {{ $layout['tanggal']['top'] }}%; left: {{ $layout['tanggal']['left'] }}%; transform: {{ $calcTx($aTanggal) }}; text-align: {{ $aTanggal }}; font-size: {{ $layout['tanggal']['size'] }}px; color: {{ $layout['tanggal']['color'] }}; font-weight: {{ !empty($layout['tanggal']['bold']) ? 'bold' : 'normal' }};">
                    {{ $item['date_formatted'] }}
                </div>
                @endif

                <!-- QR Code Validasi Keaslian Dokumen -->
                @if(!empty($layout['qrcode']['visible']))
                <div class="cert-text-element"
                     style="top: {{ $layout['qrcode']['top'] }}%; left: {{ $layout['qrcode']['left'] }}%; width: {{ $layout['qrcode']['size'] }}px; height: {{ $layout['qrcode']['size'] }}px; padding: 2px; background: #ffffff; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                    {!! $item['qr_svg'] !!}
                </div>
                @endif

                <!-- Teks Tambahan 1 (Opsional) -->
                @if(!empty($layout['teks_1']['visible']) && !empty($layout['teks_1']['text']))
                @php
                    $t1 = str_replace(
                        ['[NAMA]', '[SEKOLAH]', '[PREDIKAT]', '[LOMBA]', '[TANGGAL]', '[NOMOR]'],
                        [$item['name'], $item['institution'], $item['predikat'], $item['competition_name'], $item['date_formatted'], $item['cert_number']],
                        $layout['teks_1']['text']
                    );
                    $align1 = $layout['teks_1']['align'] ?? 'center';
                    $tx1 = $align1 === 'left' ? 'translate(0, -50%)' : ($align1 === 'right' ? 'translate(-100%, -50%)' : 'translate(-50%, -50%)');
                @endphp
                <div class="cert-text-element {{ ($layout['teks_1']['font'] ?? 'sans') === 'serif' ? 'font-serif' : 'font-sans' }}"
                     style="top: {{ $layout['teks_1']['top'] }}%; left: {{ $layout['teks_1']['left'] }}%; transform: {{ $tx1 }}; text-align: {{ $align1 }}; font-size: {{ $layout['teks_1']['size'] }}px; color: {{ $layout['teks_1']['color'] }}; font-weight: {{ !empty($layout['teks_1']['bold']) ? 'bold' : 'normal' }}; white-space: pre-wrap; max-width: 80%;">
                    {!! nl2br(e($t1)) !!}
                </div>
                @endif

                <!-- Teks Tambahan 2 (Opsional) -->
                @if(!empty($layout['teks_2']['visible']) && !empty($layout['teks_2']['text']))
                @php
                    $t2 = str_replace(
                        ['[NAMA]', '[SEKOLAH]', '[PREDIKAT]', '[LOMBA]', '[TANGGAL]', '[NOMOR]'],
                        [$item['name'], $item['institution'], $item['predikat'], $item['competition_name'], $item['date_formatted'], $item['cert_number']],
                        $layout['teks_2']['text']
                    );
                    $align2 = $layout['teks_2']['align'] ?? 'center';
                    $tx2 = $align2 === 'left' ? 'translate(0, -50%)' : ($align2 === 'right' ? 'translate(-100%, -50%)' : 'translate(-50%, -50%)');
                @endphp
                <div class="cert-text-element {{ ($layout['teks_2']['font'] ?? 'sans') === 'serif' ? 'font-serif' : 'font-sans' }}"
                     style="top: {{ $layout['teks_2']['top'] }}%; left: {{ $layout['teks_2']['left'] }}%; transform: {{ $tx2 }}; text-align: {{ $align2 }}; font-size: {{ $layout['teks_2']['size'] }}px; color: {{ $layout['teks_2']['color'] }}; font-weight: {{ !empty($layout['teks_2']['bold']) ? 'bold' : 'normal' }}; white-space: pre-wrap; max-width: 80%;">
                    {!! nl2br(e($t2)) !!}
                </div>
                @endif

                <!-- Teks Tambahan 3 (Opsional) -->
                @if(!empty($layout['teks_3']['visible']) && !empty($layout['teks_3']['text']))
                @php
                    $t3 = str_replace(
                        ['[NAMA]', '[SEKOLAH]', '[PREDIKAT]', '[LOMBA]', '[TANGGAL]', '[NOMOR]'],
                        [$item['name'], $item['institution'], $item['predikat'], $item['competition_name'], $item['date_formatted'], $item['cert_number']],
                        $layout['teks_3']['text']
                    );
                    $align3 = $layout['teks_3']['align'] ?? 'center';
                    $tx3 = $align3 === 'left' ? 'translate(0, -50%)' : ($align3 === 'right' ? 'translate(-100%, -50%)' : 'translate(-50%, -50%)');
                @endphp
                <div class="cert-text-element {{ ($layout['teks_3']['font'] ?? 'sans') === 'serif' ? 'font-serif' : 'font-sans' }}"
                     style="top: {{ $layout['teks_3']['top'] }}%; left: {{ $layout['teks_3']['left'] }}%; transform: {{ $tx3 }}; text-align: {{ $align3 }}; font-size: {{ $layout['teks_3']['size'] }}px; color: {{ $layout['teks_3']['color'] }}; font-weight: {{ !empty($layout['teks_3']['bold']) ? 'bold' : 'normal' }}; white-space: pre-wrap; max-width: 80%;">
                    {!! nl2br(e($t3)) !!}
                </div>
                @endif

            </div>
        </div>
        @endforeach
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
