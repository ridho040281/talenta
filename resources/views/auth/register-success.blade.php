<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tanda Bukti Pembuatan Akun — {{ $slip['nisn'] ?? ($user->nisn ?? 'TALENTA') }} ({{ $slip['name'] ?? $user->name }})</title>
    
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
        @media screen {
            body {
                background-color: #0b1120;
                background-image:
                    radial-gradient(at 15% 15%, rgba(78, 110, 255, 0.22) 0px, transparent 55%),
                    radial-gradient(at 85% 10%, rgba(122, 90, 248, 0.20) 0px, transparent 50%),
                    radial-gradient(at 50% 50%, rgba(30, 41, 59, 0.5) 0px, transparent 70%),
                    radial-gradient(at 20% 80%, rgba(16, 185, 129, 0.12) 0px, transparent 50%),
                    linear-gradient(180deg, #131c31 0%, #0d1527 50%, #070b14 100%);
                background-attachment: fixed;
                min-height: 100vh;
            }

            .print-only-page {
                display: none !important;
            }
        }

        @media print {
            @page {
                size: A4 portrait;
                margin: 0;
            }

            html, body {
                width: 210mm !important;
                min-height: 297mm !important;
                background: white !important;
                color: #0f172a !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .screen-only-modal {
                display: none !important;
            }

            .print-only-page {
                display: flex !important;
                flex-direction: column !important;
                justify-content: space-between !important;
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
                background: white !important;
            }

            .kop-double-line {
                border-bottom: 3px double #0f172a;
            }
        }
    </style>
</head>
<body class="font-sans antialiased text-slate-900">

    <!-- ============================================================== -->
    <!-- 1. TAMPILAN SCREEN: MODAL BUKTI PEMBUATAN AKUN (KOTAK MERAH)  -->
    <!-- ============================================================== -->
    <div class="screen-only-modal min-h-screen flex items-center justify-center p-4 sm:p-6 lg:p-8">
        <div class="w-full max-w-2xl bg-white rounded-3xl shadow-2xl border border-slate-200/80 overflow-hidden animate-in fade-in zoom-in duration-300">
            
            <!-- Modal Top Header -->
            <div class="px-6 py-5 bg-gradient-to-r from-emerald-600 via-teal-600 to-indigo-700 text-white flex items-center justify-between gap-4">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-11 h-11 rounded-2xl bg-white/15 backdrop-blur-md flex items-center justify-center text-white shrink-0 border border-white/20 shadow-inner">
                        <i data-lucide="check-circle-2" class="w-6 h-6 text-emerald-300"></i>
                    </div>
                    <div class="min-w-0">
                        <h2 class="text-sm sm:text-base font-black tracking-wide uppercase truncate">Tanda Bukti Pembuatan Akun</h2>
                        <p class="text-xs text-emerald-100/90 truncate">Akun Resmi {{ $appSettings['app_name'] ?? 'TALENTA 2026' }} Berhasil Dibuat</p>
                    </div>
                </div>
                <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-white/20 text-white border border-white/30 backdrop-blur-md shrink-0">
                    AKUN RESMI
                </span>
            </div>

            <!-- Modal Content (Sesuai Kotak Merah) -->
            <div class="p-5 sm:p-7 space-y-4 text-xs">

                <!-- IDENTITAS PEMILIK AKUN / PENANGGUNG JAWAB -->
                <div class="border border-slate-300 rounded-2xl overflow-hidden shadow-xs">
                    <div class="bg-slate-100 px-4 py-2.5 font-bold uppercase tracking-wider text-slate-700 border-b border-slate-300 flex items-center justify-between">
                        <span>Identitas Pemilik Akun / Penanggung Jawab</span>
                        <span class="text-[10px] bg-emerald-100 text-emerald-800 font-bold px-2.5 py-0.5 rounded-full border border-emerald-200">Terdaftar</span>
                    </div>
                    <div class="p-4 grid grid-cols-1 sm:grid-cols-2 gap-y-3.5 gap-x-6 bg-white">
                        <div>
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">Nama Penanggung Jawab / Pemilik Akun</span>
                            <span class="font-black text-slate-900 text-sm block mt-0.5">{{ $slip['name'] ?? $user->name }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">Asal Sekolah / Madrasah</span>
                            <span class="font-bold text-slate-800 text-sm block mt-0.5">{{ $slip['institution_name'] ?? ($user->institution_name ?? '-') }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">Nomor WhatsApp Aktif</span>
                            <span class="font-mono font-bold text-slate-800 text-sm block mt-0.5">{{ $slip['phone'] ?? ($user->phone ?? '-') }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">Waktu Registrasi Akun</span>
                            <span class="font-medium text-slate-700 text-xs block mt-0.5">{{ $slip['created_at'] ?? ($user->created_at->translatedFormat('d F Y, H:i') . ' WIB') }}</span>
                        </div>
                    </div>

                    <!-- Box Username & Password (Kredensial Login) -->
                    <div class="bg-emerald-50/70 border-t border-emerald-200 p-4 space-y-3">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="p-3.5 rounded-xl bg-white border border-emerald-300 shadow-xs">
                                <span class="text-[10px] font-black uppercase text-emerald-800 block">Username / ID Login</span>
                                <span class="font-sans font-black text-slate-950 text-sm sm:text-base block mt-0.5 select-all tracking-wide">
                                    {{ $slip['nisn'] ?? ($user->nisn ?: $user->email) }}
                                </span>
                                <span class="text-[9px] text-slate-400 block mt-0.5">(Dapat menggunakan NISN atau Alamat Email)</span>
                            </div>
                            <div class="p-3.5 rounded-xl bg-white border border-amber-300 shadow-xs">
                                <span class="text-[10px] font-black uppercase text-amber-800 block">Password / Kata Sandi</span>
                                <span class="font-sans font-black text-amber-950 text-sm sm:text-base block mt-0.5 select-all tracking-wider">
                                    {{ $slip['default_password'] ?? ($user->nisn ?: 'Password Anda') }}
                                </span>
                                <span class="text-[9px] text-slate-400 block mt-0.5">(Default otomatis sesuai NISN saat registrasi)</span>
                            </div>
                        </div>

                        <div class="p-3 bg-white rounded-xl border border-emerald-200 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-[11px]">
                            <span class="font-bold text-slate-700 flex items-center gap-1.5">
                                <i data-lucide="globe" class="w-3.5 h-3.5 text-emerald-600 shrink-0"></i>
                                Alamat Web Portal Login:
                            </span>
                            <a href="{{ route('login') }}" target="_blank" class="font-sans font-black text-emerald-700 hover:text-emerald-900 underline text-xs break-all">
                                {{ route('login') }}
                            </a>
                        </div>
                    </div>
                </div>

                <!-- CATATAN & PETUNJUK AKSES AKUN -->
                <div class="p-4 bg-blue-50/80 border border-blue-200 rounded-2xl text-[11px] text-blue-950 space-y-1.5 leading-relaxed">
                    <div class="font-bold flex items-center gap-1.5 text-blue-900">
                        <i data-lucide="info" class="w-4 h-4 text-blue-600 shrink-0"></i>
                        <span>Catatan & Petunjuk Akses Akun:</span>
                    </div>
                    <p class="text-slate-700">
                        <strong>1.</strong> Harap simpan lembar tanda bukti pembuatan akun ini dengan baik. Gunakan <strong>Username</strong> dan <strong>Password</strong> di atas untuk login ke portal {{ $appSettings['app_name'] ?? 'TALENTA 2026' }}.<br>
                        <strong>2.</strong> Akun ini berlaku sebagai <strong>Akun Induk</strong> yang dapat digunakan untuk mendaftarkan <strong>banyak siswa dan cabang lomba sekaligus</strong> (mandiri maupun kolektif).<br>
                        <strong>3.</strong> Seluruh pembaruan jadwal lomba, pengundian nomor tampil, pembayaran, dan sertifikat digital seluruh siswa akan dikelola melalui akun ini.
                    </p>
                </div>

            </div>

            <!-- Modal Footer Action Buttons -->
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-3">
                <button type="button" onclick="window.print()" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md shadow-blue-500/20 transition cursor-pointer">
                    <i data-lucide="printer" class="w-4 h-4"></i>
                    <span>Cetak Lembar Bukti (PDF)</span>
                </button>
                <a href="{{ route('peserta.dashboard') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-bold text-xs shadow-md shadow-emerald-500/20 transition cursor-pointer">
                    <span>Lanjut ke Dashboard</span>
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
            </div>

        </div>
    </div>

    <!-- ============================================================== -->
    <!-- 2. TAMPILAN CETAK LENGKAP: KOP RESMI + ISI + TTD BAWAH        -->
    <!-- (Hanya muncul saat tombol Cetak diklik / window.print())       -->
    <!-- ============================================================== -->
    <div class="print-only-page">
        <div class="space-y-4">
            
            <!-- KOP SURAT RESMI -->
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

            <!-- JUDUL DOKUMEN -->
            <div class="text-center my-3">
                <h2 class="text-base sm:text-lg font-black uppercase tracking-wider text-slate-900 underline decoration-2 underline-offset-4">
                    TANDA BUKTI PEMBUATAN AKUN
                </h2>
            </div>

            <!-- DETAIL AKUN & KREDENSIAL LOGIN -->
            <div class="border border-slate-300 rounded-2xl overflow-hidden text-xs">
                <div class="bg-slate-100 px-4 py-2 font-bold uppercase tracking-wider text-slate-700 border-b border-slate-300 flex items-center justify-between">
                    <span>Identitas Pemilik Akun / Penanggung Jawab</span>
                    <span class="text-[10px] bg-emerald-100 text-emerald-800 font-bold px-2.5 py-0.5 rounded-full border border-emerald-200">AKUN RESMI</span>
                </div>
                <div class="p-4 grid grid-cols-2 gap-y-3.5 gap-x-6 bg-white">
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase font-bold">Nama Penanggung Jawab / Pemilik Akun</span>
                        <span class="font-black text-slate-900 text-sm">{{ $slip['name'] ?? $user->name }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase font-bold">Asal Sekolah / Madrasah</span>
                        <span class="font-bold text-slate-800 text-sm">{{ $slip['institution_name'] ?? ($user->institution_name ?? '-') }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase font-bold">Nomor WhatsApp Aktif</span>
                        <span class="font-mono font-bold text-slate-800 text-sm">{{ $slip['phone'] ?? ($user->phone ?? '-') }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase font-bold">Waktu Registrasi Akun</span>
                        <span class="font-medium text-slate-700 text-xs">{{ $slip['created_at'] ?? ($user->created_at->translatedFormat('d F Y, H:i') . ' WIB') }}</span>
                    </div>
                </div>

                <!-- Box Username & Password (Kredensial Login) -->
                <div class="bg-emerald-50/70 border-t border-emerald-200 p-4 space-y-3">
                    <div class="grid grid-cols-2 gap-3">
                        <div class="p-3 rounded-xl bg-white border border-emerald-300 shadow-xs">
                            <span class="text-[10px] font-black uppercase text-emerald-800 block">Username / ID Login</span>
                            <span class="font-sans font-black text-slate-950 text-sm sm:text-base block mt-0.5 tracking-wide">
                                {{ $slip['nisn'] ?? ($user->nisn ?: $user->email) }}
                            </span>
                            <span class="text-[9px] text-slate-400 block">(Dapat menggunakan NISN atau Alamat Email)</span>
                        </div>
                        <div class="p-3 rounded-xl bg-white border border-amber-300 shadow-xs">
                            <span class="text-[10px] font-black uppercase text-amber-800 block">Password / Kata Sandi</span>
                            <span class="font-sans font-black text-amber-950 text-sm sm:text-base block mt-0.5 tracking-wider">
                                {{ $slip['default_password'] ?? ($user->nisn ?: 'Password Anda') }}
                            </span>
                            <span class="text-[9px] text-slate-400 block">(Default otomatis sesuai NISN saat registrasi)</span>
                        </div>
                    </div>

                    <div class="p-2.5 bg-white rounded-xl border border-emerald-200 flex items-center justify-between text-[11px]">
                        <span class="font-bold text-slate-700 flex items-center gap-1.5">
                            <i data-lucide="globe" class="w-3.5 h-3.5 text-emerald-600 shrink-0"></i>
                            Alamat Web Portal Login:
                        </span>
                        <span class="font-sans font-black text-emerald-700 text-xs">
                            {{ route('login') }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- KOTAK PETUNJUK -->
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

            <!-- TANDA TANGAN (TTD BAWAH LENGKAP) -->
            <div class="pt-6 flex justify-between items-stretch text-xs text-slate-800">
                <div class="text-center w-56 flex flex-col justify-between">
                    <div>
                        <div class="invisible select-none leading-tight">Tanggal</div>
                        <div class="invisible select-none font-bold">Instansi</div>
                        <div class="font-bold">Pembuat / Pemilik Akun,</div>
                    </div>
                    <div class="pt-14">
                        <div class="font-black text-slate-950 underline underline-offset-2">
                            {{ $slip['name'] ?? $user->name }}
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
                    <div class="pt-14">
                        <div class="font-black text-slate-950 underline underline-offset-2">{{ $appSettings['committee_chairman_name'] ?? 'KHOIRUL ANAM, S.Pd' }}</div>
                        <div class="text-[10px] text-slate-500">{{ !empty($appSettings['committee_chairman_nip']) ? 'NIP. ' . $appSettings['committee_chairman_nip'] : 'Ketua Panitia Pelaksana' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- FOOTER META -->
        <div class="pt-3 border-t border-slate-200 flex items-center justify-between text-[9px] text-slate-400 font-mono">
            <span>{{ $appSettings['event_name'] ?? 'Milad ke-57' }} {{ $appSettings['institution_name'] ?? 'MTsN 1 Blitar' }} • Aplikasi {{ $appSettings['app_name'] ?? 'TALENTA' }}</span>
            <span>Dicetak pada: {{ now()->format('d/m/Y H:i:s') }}</span>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (window.lucide) {
                lucide.createIcons();
            }
        });
    </script>
</body>
</html>
