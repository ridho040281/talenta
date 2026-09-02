<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KWITANSI PEMBAYARAN - {{ $registration->registration_code }} ({{ $registration->display_name }})</title>
    
    <!-- Favicon -->
    @if(!empty($appSettings['favicon']))
        <link rel="icon" type="image/png" href="{{ asset('storage/' . $appSettings['favicon']) }}">
        <link rel="shortcut icon" href="{{ asset('storage/' . $appSettings['favicon']) }}">
        <link rel="apple-touch-icon" href="{{ asset('storage/' . $appSettings['favicon']) }}">
    @else
        <link rel="icon" href="{{ asset('favicon.ico') }}">
    @endif

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=Space+Mono:wght@700&display=swap" rel="stylesheet">
    
    <!-- Vite Local Tailwind CSS & JS Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Lucide Icons -->
    <script src="https://cdn.jsdelivr.net/npm/lucide@0.344.0/dist/umd/lucide.min.js"></script>

    <style>
        @page {
            size: A4 portrait;
            margin: 0;
        }

        @media print {
            html, body { 
                width: 210mm !important;
                min-height: 297mm !important;
                background: white !important; 
                -webkit-print-color-adjust: exact; 
                print-color-adjust: exact; 
                margin: 0 !important;
                padding: 0 !important;
            }
            .no-print { 
                display: none !important; 
            }
            .print-page { 
                box-shadow: none !important; 
                margin: 0 auto !important; 
                padding: 1.5cm 2cm 2cm 2cm !important;
                width: 210mm !important;
                max-width: 210mm !important;
                min-height: 297mm !important;
                border: none !important;
                border-radius: 0 !important;
                box-sizing: border-box !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
        }

        .kop-double-line {
            border-bottom: 3px double #0f172a;
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-900 font-sans antialiased min-h-screen py-4 sm:py-6">

    <!-- Top Action Bar (Hidden when printing) -->
    <div class="no-print max-w-3xl mx-auto mb-4 px-4">
        <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200 flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
                    <i data-lucide="receipt" class="w-5 h-5"></i>
                </div>
                <div>
                    <h1 class="text-sm font-black text-slate-900">Kwitansi / Bukti Pembayaran</h1>
                    <p class="text-xs text-slate-500 font-mono">KW-{{ $registration->registration_code }} • {{ $registration->display_name }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                @php
                    $defaultBackUrl = route('peserta.dashboard');
                    if (auth()->check()) {
                        if (auth()->user()->isSuperAdmin()) {
                            $defaultBackUrl = route('admin.dashboard');
                        } elseif (auth()->user()->isPic()) {
                            $defaultBackUrl = route('pic.dashboard');
                        }
                    }
                @endphp
                <button type="button" onclick="smartGoBack('{{ $defaultBackUrl }}')" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-xs font-bold text-slate-700 transition cursor-pointer flex items-center gap-1.5">
                    <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                    <span>Kembali</span>
                </button>
                <button onclick="window.print()" class="inline-flex items-center gap-2 px-5 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs shadow-md shadow-amber-500/20 transition cursor-pointer">
                    <i data-lucide="printer" class="w-4 h-4"></i>
                    <span>Cetak Kwitansi</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Printable Container -->
    <div class="max-w-[210mm] mx-auto">
        <div class="print-page bg-white pt-[1.5cm] px-[2cm] pb-[2cm] shadow-sm border border-slate-200 rounded-3xl flex flex-col justify-between">
            
            <div class="space-y-4">
                <!-- ==================== KOP SURAT RESMI ==================== -->
                @php
                    $kopImage = $appSettings['kop_kegiatan'] ?? ($appSettings['kop_lembaga'] ?? ($appSettings['letterhead_image'] ?? null));
                @endphp
                @if(!empty($kopImage))
                    <div class="mb-2 w-full flex justify-center">
                        <img src="{{ asset('storage/' . $kopImage) }}" alt="Kop Surat" class="w-full h-auto max-h-[145px] object-contain block">
                    </div>
                @else
                    <div class="kop-header pb-3 mb-4 kop-double-line">
                        <div class="flex items-center justify-between gap-4">
                            <div class="w-16 h-16 shrink-0 flex items-center justify-center">
                                @if(!empty($appSettings['app_logo']))
                                    <img src="{{ asset('storage/' . $appSettings['app_logo']) }}" alt="Logo" class="max-h-16 max-w-16 object-contain">
                                @elseif(!empty($appSettings['favicon']))
                                    <img src="{{ asset('storage/' . $appSettings['favicon']) }}" alt="Logo" class="max-h-16 max-w-16 object-contain">
                                @else
                                    <div class="w-14 h-14 rounded-2xl bg-emerald-700 text-white font-black flex flex-col items-center justify-center shadow-xs">
                                        <span class="text-[10px] tracking-wider leading-none">MTsN 1</span>
                                        <span class="text-sm font-black tracking-widest leading-none mt-0.5">BLITAR</span>
                                    </div>
                                @endif
                            </div>

                            <div class="flex-1 text-center space-y-0.5">
                                <div class="text-[11px] font-bold tracking-wider text-slate-600 uppercase">KEMENTERIAN AGAMA REPUBLIK INDONESIA</div>
                                <div class="text-[11px] font-bold tracking-wider text-slate-700 uppercase">KANTOR KEMENTERIAN AGAMA KABUPATEN BLITAR</div>
                                <div class="text-base sm:text-lg font-black tracking-wide text-slate-900 uppercase">{{ $appSettings['institution_name'] ?? 'MADRASAH TSANAWIYAH NEGERI 1 BLITAR' }}</div>
                                <div class="text-xs font-black text-emerald-800 tracking-wider uppercase">PANITIA PELAKSANA {{ $appSettings['app_name'] ?? 'TALENTA 2026' }}</div>
                                <div class="text-[10px] text-slate-500">
                                    {{ $appSettings['address'] ?? 'Jl. Raya Dandong No. 01 Srengat, Blitar, Jawa Timur 66152' }} | Telp: {{ $appSettings['contact_phone'] ?? '(0342) 551234' }} | Website: {{ $appSettings['school_website'] ?? 'mtsn1blitar.sch.id' }}
                                </div>
                            </div>

                            <div class="w-16 h-16 shrink-0 flex items-center justify-center">
                                @if(!empty($appSettings['event_logo']))
                                    <img src="{{ asset('storage/' . $appSettings['event_logo']) }}" alt="Event Logo" class="max-h-16 max-w-16 object-contain">
                                @elseif(!empty($appSettings['app_logo']))
                                    <img src="{{ asset('storage/' . $appSettings['app_logo']) }}" alt="Logo" class="max-h-16 max-w-16 object-contain">
                                @else
                                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-amber-500 to-amber-600 text-slate-950 font-black flex flex-col items-center justify-center shadow-xs">
                                        <i data-lucide="trophy" class="w-6 h-6"></i>
                                        <span class="text-[8px] font-mono tracking-wider">TALENTA</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                <!-- ==================== JUDUL DOKUMEN ==================== -->
                <div class="flex items-center justify-between border-b border-slate-200 pb-3">
                    <div>
                        <h2 class="text-base sm:text-lg font-black uppercase tracking-wider text-slate-900">
                            KWITANSI PEMBAYARAN
                        </h2>
                        <p class="text-xs text-slate-500">Tanda Bukti Pelunasan Biaya Pendaftaran</p>
                    </div>
                    <div class="text-right">
                        <span class="text-[10px] font-bold uppercase text-slate-400 block">No. Kwitansi</span>
                        <span class="font-mono font-black text-slate-900 text-sm">
                            {{ $registration->invoice ? $registration->invoice->invoice_number : 'KW-' . $registration->registration_code }}
                        </span>
                    </div>
                </div>

                <!-- ==================== FORMULIR KWITANSI ==================== -->
                <div class="space-y-4 text-xs text-slate-800">
                    <div class="grid grid-cols-3 gap-2 py-1.5 border-b border-slate-100">
                        <span class="font-bold text-slate-500">Telah Diterima Dari</span>
                        <span class="col-span-2 font-black text-slate-900 text-sm">
                            {{ $registration->institution_name }} ({{ $registration->official_name ?: $registration->display_name }})
                        </span>
                    </div>

                    <div class="grid grid-cols-3 gap-2 py-1.5 border-b border-slate-100">
                        <span class="font-bold text-slate-500">Uang Sejumlah</span>
                        <div class="col-span-2">
                            @php
                                $amount = $registration->invoice ? $registration->invoice->final_amount : ($registration->competition->price ?? 0);
                                $terbilangWords = class_exists(\App\Helpers\Terbilang::class) ? \App\Helpers\Terbilang::make($amount) : '';
                            @endphp
                            <span class="font-black text-emerald-800 text-sm font-mono block">
                                Rp {{ number_format($amount, 0, ',', '.') }}
                            </span>
                            <span class="italic text-slate-600 text-[11px] block mt-0.5">
                                @if($amount == 0)
                                    (Nol Rupiah / Gratis)
                                @else
                                    ({{ ucwords($terbilangWords ? $terbilangWords . ' rupiah' : 'Dua Puluh Lima Ribu Rupiah') }})
                                @endif
                            </span>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-2 py-1.5 border-b border-slate-100">
                        <span class="font-bold text-slate-500">Untuk Pembayaran</span>
                        <div class="col-span-2 space-y-1">
                            <span class="font-bold text-slate-900">
                                Biaya Registrasi Pendaftaran Cabang Perlombaan {{ $registration->competition->name }}
                            </span>
                            <div class="text-[11px] text-slate-600">
                                Peserta / Tim: <strong>{{ $registration->display_name }}</strong> (Kode: {{ $registration->registration_code }})
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-2 py-1.5 border-b border-slate-100">
                        <span class="font-bold text-slate-500">Status Pembayaran</span>
                        <div class="col-span-2">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-emerald-100 text-emerald-900 border border-emerald-300">
                                <i data-lucide="check-circle" class="w-3.5 h-3.5 text-emerald-600"></i>
                                <span>LUNAS & TERVERIFIKASI BENDAHARA</span>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- ==================== TABEL RINCIAN ==================== -->
                <div class="border border-slate-300 rounded-2xl overflow-hidden text-xs">
                    <table class="w-full text-left">
                        <thead class="bg-slate-100 font-bold uppercase tracking-wider text-slate-700 border-b border-slate-300 text-[10px]">
                            <tr>
                                <th class="py-2.5 px-3">No</th>
                                <th class="py-2.5 px-3">Deskripsi Tagihan</th>
                                <th class="py-2.5 px-3 text-center">Qty</th>
                                <th class="py-2.5 px-3 text-right">Nominal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            <tr>
                                <td class="py-2.5 px-3 font-bold">1</td>
                                <td class="py-2.5 px-3 font-bold">
                                    {{ $registration->competition->name }} - {{ $registration->institution_name }}
                                    <div class="text-[10px] text-slate-500 font-normal">Nama: {{ $registration->display_name }}</div>
                                </td>
                                <td class="py-2.5 px-3 text-center font-bold">1</td>
                                <td class="py-2.5 px-3 text-right font-mono font-bold">
                                    Rp {{ number_format($registration->competition->price ?? 0, 0, ',', '.') }}
                                </td>
                            </tr>
                            <tr class="bg-slate-50 font-black text-slate-900 border-t border-slate-300">
                                <td colspan="3" class="py-2.5 px-3 text-right uppercase">Total Pembayaran:</td>
                                <td class="py-2.5 px-3 text-right font-mono text-emerald-800 text-sm">
                                    Rp {{ number_format($amount, 0, ',', '.') }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- ==================== TANDA TANGAN (DINAMIS MENGIKUTI TABEL RINCIAN) ==================== -->
                <div class="pt-4 flex justify-between items-stretch text-xs text-slate-800">
                    <div class="text-center w-56 flex flex-col justify-between">
                        <div>
                            <div class="invisible select-none leading-tight">Tanggal</div>
                            <div class="invisible select-none font-bold">Instansi</div>
                            <div class="font-bold">Penyetor / Official,</div>
                        </div>
                        <div class="pt-12">
                            <div class="font-black text-slate-950 underline underline-offset-2">
                                {{ $registration->official_name ?: $registration->display_name }}
                            </div>
                            <div class="invisible select-none text-[10px]">Identitas</div>
                        </div>
                    </div>

                    <div class="text-center w-56 flex flex-col justify-between">
                        <div>
                            <div class="leading-tight">Blitar, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</div>
                            <div class="font-bold">Bendahara Panitia</div>
                            <div class="font-bold">MTsN 1 Blitar</div>
                        </div>
                        <div class="pt-12">
                            <div class="font-black text-slate-950 underline underline-offset-2">HJ. SITI KHADIJAH, S.E.</div>
                            <div class="text-[10px] text-slate-500">{{ $appSettings['event_name'] ?? 'Milad ke-57' }} • {{ $appSettings['institution_name'] ?? 'MTsN 1 Blitar' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Meta (Tetap di bagian paling bawah halaman) -->
            <div class="pt-2 border-t border-slate-200 flex items-center justify-between text-[9px] text-slate-400 font-mono">
                <span>{{ $appSettings['event_name'] ?? 'Milad ke-57' }} {{ $appSettings['institution_name'] ?? 'MTsN 1 Blitar' }} • Aplikasi {{ $appSettings['app_name'] ?? 'TALENTA' }}</span>
                <span>Dicetak pada: {{ now()->format('d/m/Y H:i:s') }}</span>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();

        function smartGoBack(fallbackUrl) {
            if (window.opener && !window.opener.closed) {
                window.close();
                return;
            }

            if (document.referrer && document.referrer !== window.location.href && document.referrer.indexOf(window.location.origin) === 0) {
                if (window.history.length > 1) {
                    window.history.back();
                    setTimeout(function() {
                        window.location.href = document.referrer;
                    }, 250);
                    return;
                } else {
                    window.location.href = document.referrer;
                    return;
                }
            }

            try {
                window.close();
            } catch (e) {}

            window.location.href = fallbackUrl;
        }
    </script>
</body>
</html>
