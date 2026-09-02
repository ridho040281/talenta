<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BUKTI AKUN PENDAFTAR - {{ $registration->registration_code }} ({{ $registration->display_name }})</title>
    
    <!-- Favicon -->
    @if(!empty($appSettings['favicon']))
        <link rel="icon" type="image/png" href="{{ asset('storage/' . $appSettings['favicon']) }}">
        <link rel="shortcut icon" href="{{ asset('storage/' . $appSettings['favicon']) }}">
        <link rel="apple-touch-icon" href="{{ asset('storage/' . $appSettings['favicon']) }}">
    @else
        <link rel="icon" href="{{ asset('favicon.ico') }}">
    @endif

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Mono:wght@700&display=swap" rel="stylesheet">
    
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
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                    <i data-lucide="user-check" class="w-5 h-5"></i>
                </div>
                <div>
                    <h1 class="text-sm font-black text-slate-900">Bukti Akun Pendaftaran {{ $appSettings['app_name'] ?? 'TALENTA 2026' }}</h1>
                    <p class="text-xs text-slate-500 font-mono">{{ $registration->registration_code }} • {{ $registration->user->name ?? $registration->display_name }}</p>
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
                <button onclick="window.print()" class="inline-flex items-center gap-2 px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md shadow-blue-500/20 transition cursor-pointer">
                    <i data-lucide="printer" class="w-4 h-4"></i>
                    <span>Cetak Lembar Bukti</span>
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
                <div class="text-center">
                    <h2 class="text-base sm:text-lg font-black uppercase tracking-wider text-slate-900 underline decoration-2 underline-offset-4">
                        TANDA BUKTI PEMBUATAN AKUN
                    </h2>
                </div>

                <!-- ==================== DETAIL AKUN & KREDENSIAL LOGIN ==================== -->
                <div class="border border-slate-300 rounded-2xl overflow-hidden text-xs">
                    <div class="bg-slate-100 px-4 py-2 font-bold uppercase tracking-wider text-slate-700 border-b border-slate-300 flex items-center justify-between">
                        <span>Identitas Pemilik Akun / Penanggung Jawab</span>
                        <span class="text-[10px] bg-emerald-100 text-emerald-800 font-bold px-2.5 py-0.5 rounded-full border border-emerald-200">AKUN RESMI</span>
                    </div>
                    <div class="p-4 grid grid-cols-1 sm:grid-cols-2 gap-y-3.5 gap-x-6">
                        <div>
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">Nama Penanggung Jawab / Pemilik Akun</span>
                            <span class="font-black text-slate-900 text-sm">{{ $registration->user->name ?? $registration->official_name ?? $registration->display_name }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">Asal Sekolah / Madrasah</span>
                            <span class="font-bold text-slate-800 text-sm">{{ $registration->institution_name }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">Nomor WhatsApp Aktif</span>
                            <span class="font-mono font-bold text-slate-800 text-sm">{{ $registration->user->phone ?? $registration->official_phone ?? '-' }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">Waktu Registrasi Akun</span>
                            <span class="font-medium text-slate-700 text-xs">{{ $registration->user ? $registration->user->created_at->translatedFormat('d F Y, H:i') : now()->translatedFormat('d F Y, H:i') }} WIB</span>
                        </div>
                    </div>

                    <!-- Box Username & Password (Kredensial Login) -->
                    <div class="bg-emerald-50/70 border-t border-emerald-200 p-4 space-y-3">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="p-3 rounded-xl bg-white border border-emerald-300 shadow-xs">
                                <span class="text-[10px] font-black uppercase text-emerald-800 block">Username / ID Login</span>
                                <span class="font-sans font-black text-slate-950 text-sm sm:text-base block mt-0.5 select-all tracking-wide">
                                    {{ $registration->user->nisn ?: ($registration->user->email ?? '-') }}
                                </span>
                                <span class="text-[9px] text-slate-400 block">(Dapat menggunakan NISN atau Alamat Email)</span>
                            </div>
                            <div class="p-3 rounded-xl bg-white border border-amber-300 shadow-xs">
                                <span class="text-[10px] font-black uppercase text-amber-800 block">Password / Kata Sandi</span>
                                <span class="font-sans font-black text-amber-950 text-sm sm:text-base block mt-0.5 select-all tracking-wider">
                                    {{ $registration->user->nisn ?: 'Password Anda' }}
                                </span>
                                <span class="text-[9px] text-slate-400 block">(Default otomatis sesuai NISN saat registrasi)</span>
                            </div>
                        </div>

                        <div class="p-2.5 bg-white rounded-xl border border-emerald-200 flex flex-col sm:flex-row sm:items-center justify-between gap-1.5 text-[11px]">
                            <span class="font-bold text-slate-700 flex items-center gap-1.5">
                                <i data-lucide="globe" class="w-3.5 h-3.5 text-emerald-600 shrink-0"></i>
                                Alamat Web Portal Login:
                            </span>
                            <a href="{{ route('login') }}" target="_blank" class="font-sans font-black text-emerald-700 hover:text-emerald-900 underline text-xs">
                                {{ route('login') }}
                            </a>
                        </div>
                    </div>
                </div>

                <!-- ==================== KOTAK PETUNJUK ==================== -->
                <div class="p-3.5 bg-blue-50/70 border border-blue-200 rounded-2xl text-[11px] text-blue-950 space-y-1.5">
                    <div class="font-bold flex items-center gap-1.5 text-blue-900">
                        <i data-lucide="info" class="w-3.5 h-3.5 text-blue-600"></i>
                        <span>Catatan & Petunjuk Akses Akun:</span>
                    </div>
                    <p class="leading-relaxed">
                        1. Harap simpan lembar tanda bukti pembuatan akun ini dengan baik. Gunakan <strong>Username</strong> dan <strong>Password</strong> di atas untuk login ke portal {{ $appSettings['app_name'] ?? 'TALENTA 2026' }}.<br>
                        2. Akun ini berlaku sebagai <strong>Akun Induk</strong> yang dapat digunakan untuk mendaftarkan <strong>banyak siswa dan cabang lomba sekaligus</strong> (mandiri maupun kolektif).<br>
                        3. Seluruh pembaruan jadwal lomba, pengundian nomor tampil, pembayaran, dan sertifikat digital seluruh siswa akan dikelola melalui akun ini.
                    </p>
                </div>

                <!-- ==================== TANDA TANGAN (DINAMIS MENGIKUTI KOTAK PETUNJUK) ==================== -->
                <div class="pt-4 flex justify-between items-stretch text-xs text-slate-800">
                    <div class="text-center w-56 flex flex-col justify-between">
                        <div>
                            <div class="invisible select-none leading-tight">Tanggal</div>
                            <div class="invisible select-none font-bold">Instansi</div>
                            <div class="font-bold">Pembuat / Pemilik Akun,</div>
                        </div>
                        <div class="pt-12">
                            <div class="font-black text-slate-950 underline underline-offset-2">
                                {{ $registration->user->name ?? $registration->official_name ?? $registration->display_name }}
                            </div>
                            <div class="invisible select-none text-[10px]">NIP / Identitas</div>
                        </div>
                    </div>

                    <div class="text-center w-56 flex flex-col justify-between">
                        <div>
                            <div class="leading-tight">Blitar, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</div>
                            <div class="font-bold">Panitia Milad ke-57</div>
                            <div class="font-bold">MTsN 1 Blitar</div>
                        </div>
                        <div class="pt-12">
                            <div class="font-black text-slate-950 underline underline-offset-2">{{ $appSettings['committee_chairman_name'] ?? 'KHOIRUL ANAM, S.Pd' }}</div>
                            <div class="text-[10px] text-slate-500">{{ !empty($appSettings['committee_chairman_nip']) ? 'NIP. ' . $appSettings['committee_chairman_nip'] : 'Ketua Panitia Pelaksana' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Meta (Tetap di bagian paling bawah halaman) -->
            <div class="pt-3 border-t border-slate-200 flex items-center justify-between text-[9px] text-slate-400 font-mono">
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
