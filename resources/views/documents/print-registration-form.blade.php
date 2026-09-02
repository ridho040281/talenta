<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BUKTI PENDAFTARAN & KARTU PESERTA - {{ $registration->participant_number ?: $registration->registration_code }} ({{ $registration->display_name }})</title>
    
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
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                    <i data-lucide="file-check" class="w-5 h-5"></i>
                </div>
                <div>
                    <h1 class="text-sm font-black text-slate-900">Bukti Pendaftaran & Biodata Peserta</h1>
                    <p class="text-xs text-slate-500 font-mono">{{ $registration->participant_number ?: $registration->registration_code }} • {{ $registration->display_name }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button onclick="window.history.back()" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-xs font-bold text-slate-700 transition cursor-pointer">
                    Kembali
                </button>
                <button onclick="window.print()" class="inline-flex items-center gap-2 px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-500/20 transition cursor-pointer">
                    <i data-lucide="printer" class="w-4 h-4"></i>
                    <span>Cetak Formulir & Bukti</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Printable Container -->
    <div class="max-w-[210mm] mx-auto">
        <div class="print-page bg-white pt-[1.5cm] px-[2cm] pb-[2cm] shadow-sm border border-slate-200 rounded-3xl flex flex-col justify-between">
            
            <div class="space-y-3">
                <!-- ==================== KOP SURAT RESMI ==================== -->
                @php
                    $kopImage = $appSettings['kop_kegiatan'] ?? ($appSettings['kop_lembaga'] ?? ($appSettings['letterhead_image'] ?? null));
                @endphp
                @if(!empty($kopImage))
                    <div class="mb-1 w-full flex justify-center">
                        <img src="{{ asset('storage/' . $kopImage) }}" alt="Kop Surat" class="w-full h-auto max-h-[135px] object-contain block">
                    </div>
                @else
                    <div class="kop-header pb-2.5 mb-2.5 kop-double-line">
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
                                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-emerald-500 to-emerald-600 text-white font-black flex flex-col items-center justify-center shadow-xs">
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
                        BUKTI PENDAFTARAN
                    </h2>
                </div>

                <!-- ==================== NOMOR ADMINISTRASI HIGHLIGHT CARD ==================== -->
                <div class="grid grid-cols-2 gap-2.5 text-center">
                    <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-300">
                        <span class="text-[10px] uppercase font-bold text-slate-500 block">Kode Registrasi</span>
                        <span class="font-mono font-black text-slate-900 text-xs sm:text-sm">{{ $registration->registration_code }}</span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-emerald-50 border border-emerald-300">
                        <span class="text-[10px] uppercase font-black text-emerald-800 block">No. Peserta</span>
                        <span class="font-mono font-black text-emerald-950 text-sm sm:text-base">{{ $registration->official_participant_number }}</span>
                    </div>
                </div>

                @php
                    $memberNisns = $registration->members->pluck('nisn')->filter()->unique()->toArray();
                    $memberNames = $registration->members->pluck('full_name')->filter()->unique()->toArray();

                    $otherRegistrations = collect([]);
                    if (!empty($memberNisns) || !empty($memberNames)) {
                        $otherRegistrations = \App\Models\Registration::where('id', '!=', $registration->id)
                            ->whereIn('status', ['verified', 'pending', 'confirmed'])
                            ->whereHas('members', function($q) use ($memberNisns, $memberNames) {
                                $q->where(function($subQ) use ($memberNisns, $memberNames) {
                                    if (!empty($memberNisns)) {
                                        $subQ->whereIn('nisn', $memberNisns);
                                    }
                                    if (!empty($memberNames)) {
                                        $subQ->orWhereIn('full_name', $memberNames);
                                    }
                                });
                            })
                            ->with(['competition'])
                            ->get();
                    }
                    $totalLomba = $otherRegistrations->count() + 1;
                @endphp

                <!-- ==================== DATA CABANG LOMBA ==================== -->
                <div class="border border-slate-300 rounded-xl overflow-hidden text-xs">
                    <div class="bg-slate-100 px-3.5 py-1.5 font-bold uppercase tracking-wider text-slate-700 border-b border-slate-300 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span>1. Cabang Perlombaan</span>
                            @if($otherRegistrations->isNotEmpty())
                                <span class="text-[9px] bg-amber-100 text-amber-900 border border-amber-300 px-2 py-0.5 rounded-full font-bold">
                                    ⭐ MULTI-LOMBA ({{ $totalLomba }} Cabang)
                                </span>
                            @endif
                        </div>
                        <span class="font-bold text-emerald-700">Status: {{ ucfirst($registration->status) }}</span>
                    </div>
                    <div class="p-3 grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                        <div>
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">Cabang Lomba</span>
                            <span class="font-black text-slate-900 text-sm">{{ $registration->competition->name }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">Sektor / Kelompok</span>
                            <span class="font-bold text-slate-800">{{ $registration->sub_category ?: 'Umum SD/MI' }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">Kategori Kelas</span>
                            <span class="font-bold text-purple-800">{{ $registration->target_class ?: 'Semua Kelas' }}</span>
                        </div>
                    </div>

                    @if($otherRegistrations->isNotEmpty())
                        <div class="bg-amber-50/80 border-t border-amber-200 px-3.5 py-1.5 text-[11px] text-amber-950 flex items-start gap-2">
                            <i data-lucide="layers" class="w-3.5 h-3.5 text-amber-700 shrink-0 mt-0.5"></i>
                            <div>
                                <span class="font-bold text-amber-900">Cabang Lomba Lain yang Juga Diikuti:</span>
                                <span class="text-slate-700 ml-1">
                                    @foreach($otherRegistrations as $idx => $other)
                                        <span class="font-bold text-slate-900">{{ $other->competition->name ?? '-' }}</span>
                                        @if($other->participant_number)
                                            <span class="font-mono text-slate-600">({{ $other->participant_number }})</span>
                                        @endif{{ !$loop->last ? ', ' : '' }}
                                    @endforeach
                                </span>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- ==================== DATA ATLET / ANGGOTA ==================== -->
                <div class="border border-slate-300 rounded-xl overflow-hidden text-xs">
                    <div class="bg-slate-100 px-3.5 py-1.5 font-bold uppercase tracking-wider text-slate-700 border-b border-slate-300">
                        2. Biodata Siswa / Anggota Atlet Pendaftar
                    </div>
                    <div class="p-3 space-y-2">
                        @foreach($registration->members as $idx => $m)
                            <div class="p-2.5 bg-slate-50 rounded-lg border border-slate-200 text-xs space-y-1.5">
                                <div class="font-bold">
                                    <span class="text-slate-900 text-sm font-black">{{ $m->full_name }}</span>
                                </div>
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-slate-600">
                                    <div>
                                        <span class="text-slate-400 block text-[9px] uppercase font-bold">NISN</span>
                                        <span class="font-mono font-bold text-slate-800">{{ $m->nisn ?: '-' }}</span>
                                    </div>
                                    <div>
                                        <span class="text-slate-400 block text-[9px] uppercase font-bold">Tempat Lahir</span>
                                        <span class="font-medium text-slate-800">{{ $m->birth_place ?: '-' }}</span>
                                    </div>
                                    <div>
                                        <span class="text-slate-400 block text-[9px] uppercase font-bold">Tanggal Lahir</span>
                                        <span class="font-medium text-slate-800">{{ $m->birth_date ? \Carbon\Carbon::parse($m->birth_date)->format('d/m/Y') : '-' }}</span>
                                    </div>
                                    <div>
                                        <span class="text-slate-400 block text-[9px] uppercase font-bold">No. Kontak Siswa</span>
                                        <span class="font-mono font-medium text-slate-800">{{ $m->phone ?: '-' }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- ==================== ASAL SEKOLAH & OFFICIAL ==================== -->
                <div class="border border-slate-300 rounded-xl overflow-hidden text-xs">
                    <div class="bg-slate-100 px-3.5 py-1.5 font-bold uppercase tracking-wider text-slate-700 border-b border-slate-300">
                        3. Asal Lembaga & Guru Official (Pendamping)
                    </div>
                    <div class="p-3 grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                        <div>
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">Asal Sekolah / Madrasah</span>
                            <span class="font-bold text-slate-900 text-sm">{{ $registration->institution_name }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">Guru Pendamping / Official</span>
                            <span class="font-bold text-slate-800">{{ $registration->official_name ?: '-' }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">No. WhatsApp Official</span>
                            <span class="font-mono font-bold text-slate-800">{{ $registration->official_phone ?: '-' }}</span>
                        </div>
                    </div>
                </div>

                @php
                    $isOlimpiade = str_contains(strtolower($registration->competition->name ?? ''), 'olimpiade') 
                        || str_contains(strtolower($registration->competition->code ?? ''), 'mipa') 
                        || str_contains(strtolower($registration->competition->category->name ?? ''), 'olimpiade');
                    
                    $primaryMember = $registration->members->first();
                    $nisnVal = !empty($primaryMember?->nisn) ? trim($primaryMember->nisn) : (!empty($registration->user?->nisn) ? trim($registration->user->nisn) : 'NISN');
                    $cbtUsername = $nisnVal . '@milad57.com';
                    $cbtPassword = $nisnVal;
                    $cbtUrl = 'https://exo.mtsn1blitar.sch.id/';
                @endphp

                @if($isOlimpiade)
                    <!-- ==================== AKUN AKSES CBT OLIMPIADE ==================== -->
                    <div class="border-2 border-indigo-300 bg-indigo-50/50 rounded-xl overflow-hidden text-xs">
                        <div class="bg-indigo-700 px-3.5 py-1.5 font-bold uppercase tracking-wider text-white flex items-center gap-2">
                            <i data-lucide="monitor" class="w-3.5 h-3.5 text-indigo-200"></i>
                            <span>Akun Akses Computer Based Test (CBT) Olimpiade</span>
                        </div>
                        
                        <div class="p-2.5 space-y-2">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                <div class="p-2 bg-white rounded-lg border border-indigo-200 shadow-xs">
                                    <span class="text-[10px] font-black uppercase text-indigo-700 block">Username</span>
                                    <span class="font-sans font-black text-slate-900 text-sm sm:text-base block select-all mt-0.5 tracking-wide">
                                        {{ $cbtUsername }}
                                    </span>
                                </div>

                                <div class="p-2 bg-white rounded-lg border border-indigo-200 shadow-xs">
                                    <span class="text-[10px] font-black uppercase text-indigo-700 block">Password</span>
                                    <span class="font-sans font-black text-slate-900 text-sm sm:text-base block select-all mt-0.5 tracking-wider">
                                        {{ $cbtPassword }}
                                    </span>
                                </div>
                            </div>

                            <div class="p-2 bg-white rounded-lg border border-indigo-200 flex flex-col sm:flex-row sm:items-center justify-between gap-1 text-[11px]">
                                <span class="font-bold text-indigo-950 flex items-center gap-1.5">
                                    <i data-lucide="globe" class="w-3.5 h-3.5 text-indigo-600 shrink-0"></i>
                                    Akses Computer Based Test (CBT):
                                </span>
                                <a href="{{ $cbtUrl }}" target="_blank" class="font-sans font-black text-indigo-700 hover:text-indigo-900 underline text-xs">
                                    {{ $cbtUrl }}
                                </a>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- ==================== TANDA TANGAN (DINAMIS MENGIKUTI CARD TERAKHIR) ==================== -->
                <div class="pt-4 flex justify-between items-stretch text-xs text-slate-800">
                    <div class="text-center w-56 flex flex-col justify-between">
                        <div>
                            <div class="invisible select-none leading-tight">Tanggal</div>
                            <div>Guru Official / Atlet,</div>
                        </div>
                        <div class="pt-8">
                            <div class="font-black text-slate-950 underline underline-offset-2">
                                {{ $registration->official_name ?: $registration->display_name }}
                            </div>
                        </div>
                    </div>

                    <div class="text-center w-56 flex flex-col justify-between">
                        <div>
                            <div class="leading-tight">Blitar, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</div>
                            <div class="font-bold">Verifikator,</div>
                        </div>
                        <div class="pt-8">
                            <div class="font-black text-slate-950 underline underline-offset-2">
                                {{ $registration->verifier ? $registration->verifier->name : 'PANITIA PELAKSANA' }}
                            </div>
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
    </script>
</body>
</html>
