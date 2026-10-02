<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VERIFIKASI KEABSAHAN SERTIFIKAT - TALENTA MTsN 1 BLITAR</title>
    
    <!-- Self-hosted Fonts (lokal) -->
    <link rel="stylesheet" href="{{ asset('vendor/fonts/fonts.css') }}">
    
    <!-- Vite Local Tailwind CSS & JS Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Lucide Icons -->
    <script src="{{ asset('vendor/lucide/lucide.min.js') }}"></script>
</head>
<body class="bg-[#0B0F19] text-slate-100 min-h-screen flex flex-col justify-between selection:bg-[#7A5AF8] selection:text-white font-['Plus_Jakarta_Sans',sans-serif]">

    <!-- Header Sederhana -->
    <header class="border-b border-white/[0.08] bg-[#0C111D]/80 backdrop-blur-md sticky top-0 z-50">
        <div class="max-w-4xl mx-auto px-4 py-3.5 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-[#7A5AF8] to-[#4E6EFF] flex items-center justify-center text-white shadow-lg shadow-[#7A5AF8]/30 font-black text-sm">
                    T
                </div>
                <div>
                    <h1 class="text-sm font-black text-white tracking-wide">TALENTA MTsN 1 BLITAR</h1>
                    <p class="text-[10px] text-slate-400">Sistem Verifikasi Digital Dokumen & Piagam Resmi</p>
                </div>
            </div>
            <a href="{{ route('home') }}" class="text-xs text-slate-400 hover:text-white transition flex items-center gap-1.5">
                <i data-lucide="home" class="w-4 h-4"></i>
                <span class="hidden sm:inline">Beranda</span>
            </a>
        </div>
    </header>

    <!-- Konten Utama Verifikasi -->
    <main class="max-w-xl mx-auto px-4 py-8 sm:py-12 w-full">
        @if($isValid && $certData)
            <div class="bg-[#111827] rounded-3xl border border-emerald-500/30 p-6 sm:p-8 shadow-2xl shadow-emerald-500/5 relative overflow-hidden text-center space-y-6">
                <!-- Background Accent Glow -->
                <div class="absolute -top-24 -left-24 w-48 h-48 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute -bottom-24 -right-24 w-48 h-48 bg-blue-500/10 rounded-full blur-3xl pointer-events-none"></div>

                <!-- Verified Badge Icon -->
                <div class="w-20 h-20 rounded-3xl bg-emerald-500/20 border border-emerald-500/40 mx-auto flex items-center justify-center text-emerald-400 shadow-xl shadow-emerald-500/20">
                    <i data-lucide="shield-check" class="w-10 h-10"></i>
                </div>

                <div>
                    <span class="px-3.5 py-1.5 rounded-full text-xs font-black uppercase tracking-widest bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 inline-flex items-center gap-2 shadow-sm">
                        <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-400"></i>
                        Dokumen Resmi & Asli Terverifikasi
                    </span>

                    <!-- IDENTITAS UTAMA (Nama, NISN, Asal Sekolah, Cabang Lomba) -->
                    <div class="mt-5 space-y-2.5">
                        <h2 class="text-2xl sm:text-3xl font-black text-white tracking-wide uppercase leading-snug">
                            {{ $certData['name'] }}
                        </h2>

                        <div class="flex flex-wrap items-center justify-center gap-2 text-sm text-slate-300">
                            @if(!empty($certData['nisn']) && $certData['nisn'] !== '-')
                            <span class="px-2.5 py-0.5 rounded-lg bg-purple-500/20 border border-purple-500/30 text-purple-300 font-mono text-xs font-bold tracking-wider inline-flex items-center gap-1.5">
                                <i data-lucide="id-card" class="w-3.5 h-3.5"></i>
                                NISN: {{ $certData['nisn'] }}
                            </span>
                            @endif
                            <span class="font-bold text-slate-200 inline-flex items-center gap-1.5">
                                <i data-lucide="school" class="w-3.5 h-3.5 text-slate-400"></i>
                                {{ $certData['institution'] }}
                            </span>
                        </div>

                        <div class="pt-1">
                            <span class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-amber-500/15 border border-amber-500/30 text-amber-300 text-xs font-bold tracking-wide">
                                <i data-lucide="trophy" class="w-3.5 h-3.5 text-amber-400"></i>
                                Cabang Lomba: {{ $certData['competition'] }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Detail Verifikasi Card -->
                <div class="bg-slate-900/90 rounded-2xl p-4 sm:p-5 border border-white/[0.08] text-left space-y-3 text-xs shadow-inner">
                    <div class="flex items-start justify-between py-1 border-b border-white/[0.06] gap-3">
                        <span class="text-slate-400 font-medium shrink-0">Nama Penerima:</span>
                        <span class="font-bold text-white text-right uppercase">{{ $certData['name'] }}</span>
                    </div>
                    @if(!empty($certData['nisn']) && $certData['nisn'] !== '-')
                    <div class="flex items-start justify-between py-1 border-b border-white/[0.06] gap-3">
                        <span class="text-slate-400 font-medium shrink-0">NISN Siswa:</span>
                        <span class="font-mono font-bold text-purple-300 text-right">{{ $certData['nisn'] }}</span>
                    </div>
                    @endif
                    <div class="flex items-start justify-between py-1 border-b border-white/[0.06] gap-3">
                        <span class="text-slate-400 font-medium shrink-0">Asal Lembaga / Sekolah:</span>
                        <span class="font-semibold text-slate-200 text-right">{{ $certData['institution'] }}</span>
                    </div>
                    <div class="flex items-start justify-between py-1 border-b border-white/[0.06] gap-3">
                        <span class="text-slate-400 font-medium shrink-0">Cabang Lomba:</span>
                        <span class="font-bold text-white text-right">{{ $certData['competition'] }}</span>
                    </div>
                    <div class="flex items-start justify-between py-1 border-b border-white/[0.06] gap-3">
                        <span class="text-slate-400 font-medium shrink-0">Predikat Penghargaan:</span>
                        <span class="font-bold text-amber-400 text-right">{{ $certData['predikat'] }}</span>
                    </div>
                    <div class="flex items-start justify-between py-1 border-b border-white/[0.06] gap-3">
                        <span class="text-slate-400 font-medium shrink-0">Kode Dokumen Unik:</span>
                        <span class="font-mono font-bold text-[#A594FD] text-right">{{ $certData['code'] }}</span>
                    </div>
                    <div class="flex items-start justify-between py-1 border-b border-white/[0.06] gap-3">
                        <span class="text-slate-400 font-medium shrink-0">Instansi Penerbit:</span>
                        <span class="font-bold text-white text-right">{{ $certData['institution_issuer'] }}</span>
                    </div>
                    <div class="flex items-start justify-between py-1 gap-3">
                        <span class="text-slate-400 font-medium shrink-0">Status Keabsahan Sistem:</span>
                        <span class="font-black text-emerald-400 text-right flex items-center gap-1.5">
                            <i data-lucide="shield-check" class="w-4 h-4"></i> SAH & AKTIF TERDAFTAR
                        </span>
                    </div>
                </div>

                <p class="text-[11px] text-slate-400 leading-relaxed">
                    Sertifikat / Piagam ini diterbitkan secara resmi melalui Sistem Informasi TALENTA MTsN 1 Blitar dan telah melalui proses verifikasi database keabsahan kejuaraan.
                </p>
            </div>
        @else
            <!-- Invalid / Not Found Card -->
            <div class="bg-[#111827] rounded-3xl border border-rose-500/30 p-6 sm:p-8 shadow-2xl text-center space-y-6">
                <div class="w-20 h-20 rounded-3xl bg-rose-500/20 border border-rose-500/40 mx-auto flex items-center justify-center text-rose-400 shadow-xl shadow-rose-500/20">
                    <i data-lucide="alert-triangle" class="w-10 h-10"></i>
                </div>

                <div>
                    <span class="px-3 py-1 rounded-full text-[11px] font-black uppercase tracking-widest bg-rose-500/20 text-rose-300 border border-rose-500/30 inline-block">
                        Verifikasi Gagal
                    </span>
                    <h2 class="text-lg sm:text-xl font-black text-white mt-3">Kode Dokumen Tidak Valid</h2>
                    <p class="text-xs text-slate-400 mt-2">
                        Kode sertifikat <code class="text-rose-300 bg-rose-500/10 px-1.5 py-0.5 rounded font-mono">{{ $code }}</code> tidak ditemukan atau belum terdaftar dalam basis data resmi TALENTA MTsN 1 Blitar.
                    </p>
                </div>

                <a href="{{ route('home') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold transition">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    <span>Kembali ke Beranda</span>
                </a>
            </div>
        @endif
    </main>

    <!-- Footer -->
    <footer class="border-t border-white/[0.06] py-5 text-center text-xs text-slate-500">
        <p>&copy; {{ date('Y') }} Panitia TALENTA MTsN 1 Blitar. Seluruh hak cipta dilindungi undang-undang.</p>
    </footer>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
