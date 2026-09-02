<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DAFTAR PESERTA RESMI - {{ $appSettings['app_name'] ?? 'TALENTA 2026' }}</title>
    
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
            size: A4 portrait !important;
            margin: 0;
        }

        .print-page {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            background: white;
            box-sizing: border-box;
        }

        @media screen {
            .print-page {
                box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.1), 0 2px 6px -1px rgba(0, 0, 0, 0.06);
                border: 1px solid #e2e8f0;
                border-radius: 6px;
                padding: 1.5cm 2cm 2cm 2cm;
                margin-bottom: 24px;
            }
        }

        @media print {
            body { 
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
                border: none !important; 
                border-radius: 0 !important; 
                margin: 0 auto !important; 
                padding: 1.5cm 2cm 2cm 2cm !important;
                page-break-after: always;
                break-after: page;
                min-height: 297mm;
                max-width: 210mm;
                width: 210mm;
            }
            .print-page:last-child {
                page-break-after: auto;
                break-after: auto;
            }
        }

        .kop-double-line {
            border-bottom: 3px double #0f172a;
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-900 font-sans antialiased min-h-screen py-4 sm:py-6">

    <!-- Top Action Bar (Hidden when printing) -->
    <div class="no-print max-w-[210mm] mx-auto mb-4 px-4">
        <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div>
                <h1 class="text-sm font-black text-slate-900 flex items-center gap-2">
                    <i data-lucide="printer" class="w-4 h-4 text-emerald-600"></i>
                    <span>Pratinjau Dokumen Cetak Peserta (A4 Portrait)</span>
                </h1>
                <p class="text-xs text-slate-500 mt-0.5">Format A4 Portrait siap cetak / simpan ke PDF.</p>
            </div>
            <div class="flex items-center gap-2.5 w-full sm:w-auto">
                @php
                    $defaultBackUrl = route('pic.dashboard');
                    if (auth()->check() && auth()->user()->isSuperAdmin()) {
                        $defaultBackUrl = route('admin.dashboard');
                    }
                @endphp
                <button type="button" onclick="smartGoBack('{{ $defaultBackUrl }}')" class="flex-1 sm:flex-none text-center px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-xs font-bold text-slate-700 transition cursor-pointer flex items-center justify-center gap-1.5">
                    <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                    <span>Kembali</span>
                </button>
                <button onclick="window.print()" class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-6 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-500/20 transition cursor-pointer">
                    <i data-lucide="printer" class="w-4 h-4"></i>
                    <span>Cetak / PDF (A4)</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Printable Pages Container (A4 Portrait Width) -->
    <div class="max-w-[210mm] mx-auto space-y-6">

        @forelse($pages as $pageIndex => $page)
            <div class="print-page relative flex flex-col justify-between">
                
                <div>
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
                                    <div class="text-xs font-black text-emerald-800 tracking-wider uppercase">PANITIA PELAKSANA {{ $appSettings['event_name'] ?? 'MILAD KE-57' }}</div>
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

                    <!-- ==================== JUDUL DOKUMEN & KATEGORI ==================== -->
                    <div class="text-center my-3">
                        <h2 class="text-sm sm:text-base font-black uppercase tracking-wider text-slate-900 underline decoration-2 underline-offset-4">
                            DAFTAR NOMINATIF PESERTA RESMI
                        </h2>
                        <div class="mt-1.5 flex items-center justify-center gap-2 flex-wrap text-xs font-bold">
                            <span class="px-2.5 py-0.5 rounded bg-slate-100 text-slate-800">
                                Cabang: <strong class="text-slate-950">{{ $page['competition_name'] }}</strong>
                            </span>
                            <span class="px-2.5 py-0.5 rounded {{ $page['gender_badge_class'] }}">
                                {{ $page['sub_group_title'] }}
                            </span>
                            @if(!empty($page['sector_title']))
                                <span class="px-2.5 py-0.5 rounded bg-emerald-100 text-emerald-900">
                                    {{ $page['sector_title'] }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- ==================== TABEL PESERTA (A4 PORTRAIT) ==================== -->
                    <div class="mt-3 overflow-x-auto">
                        <table class="w-full text-left text-xs border-collapse border border-slate-900">
                            <thead>
                                <tr class="bg-slate-200/90 text-slate-900 font-bold uppercase text-[10px] tracking-wider text-center border-b border-slate-900">
                                    <th class="py-2 px-1.5 border border-slate-900 w-8">No</th>
                                    <th class="py-2 px-2 border border-slate-900 w-24">No. Peserta</th>
                                    <th class="py-2 px-1.5 border border-slate-900 w-20">No. Undian</th>
                                    <th class="py-2 px-3 border border-slate-900 text-left">Nama Atlet / Peserta</th>
                                    <th class="py-2 px-3 border border-slate-900 text-left">Asal Sekolah / Madrasah</th>
                                    <th class="py-2 px-2 border border-slate-900 w-24 text-center">Paraf</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800 text-[11px]">
                                @forelse($page['registrations'] as $idx => $reg)
                                    @php
                                        $firstMember = $reg->members->first();
                                        $isGanda = $reg->members->count() > 1;
                                    @endphp
                                    <tr class="border-b border-slate-900">
                                        <td class="py-2 px-1.5 border border-slate-900 text-center font-bold">{{ $idx + 1 }}</td>
                                        <td class="py-2 px-2 border border-slate-900 font-mono font-bold text-center text-xs">
                                            {{ $reg->participant_number ?: '-' }}
                                        </td>
                                        <td class="py-2 px-1.5 border border-slate-900 text-center font-black text-sm font-mono text-slate-950">
                                            {{ $reg->draw_number ? '#' . $reg->draw_number : '-' }}
                                        </td>
                                        <td class="py-2 px-3 border border-slate-900 font-bold">
                                            @if($isGanda)
                                                <div class="text-slate-950 font-black text-xs">{{ $reg->team_name ?: $reg->display_name }}</div>
                                                <div class="text-[10px] text-slate-600 font-medium mt-0.5 leading-tight">
                                                    @foreach($reg->members as $m)
                                                        <span>• {{ $m->full_name }} ({{ $m->gender === 'L' ? 'PA' : 'PI' }})</span><br>
                                                    @endforeach
                                                </div>
                                            @else
                                                <div class="text-slate-950 font-bold">{{ $firstMember?->full_name ?: $reg->display_name }}</div>
                                            @endif
                                        </td>
                                        <td class="py-2 px-3 border border-slate-900 font-medium">
                                            @if($isGanda && $reg->members->pluck('school_name')->filter()->unique()->count() > 1)
                                                <div class="text-[10px] text-slate-800 leading-tight">
                                                    @foreach($reg->members as $m)
                                                        <span>• {{ $m->school_name ?: $reg->institution_name }}</span><br>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span>{{ $reg->institution_name }}</span>
                                            @endif
                                        </td>
                                        <td class="py-2 px-2 border border-slate-900 text-center text-slate-400 text-[10px]">
                                            {{ $idx + 1 }}. ........
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-8 text-center text-slate-400 italic border border-slate-900">
                                            Tidak ada peserta pada kelompok ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                    <!-- ==================== TANDA TANGAN RESMI ==================== -->
                    <div class="mt-8 pt-4">
                        <div class="flex justify-between items-stretch text-xs text-slate-800">
                            <div class="text-center w-56 flex flex-col justify-between">
                                <div>
                                    <div>Mengetahui,</div>
                                    <div class="font-bold">Ketua Panitia</div>
                                    <div class="font-bold">MTsN 1 Blitar</div>
                                </div>
                                <div class="pt-12">
                                    <div class="font-black text-slate-950 underline underline-offset-2">{{ $appSettings['committee_chairman_name'] ?? 'KHOIRUL ANAM, S.Pd' }}</div>
                                    <div class="text-[10px] text-slate-500">{{ !empty($appSettings['committee_chairman_nip']) ? 'NIP. ' . $appSettings['committee_chairman_nip'] : 'Ketua Panitia Pelaksana' }}</div>
                                </div>
                            </div>

                            <div class="text-center w-56 flex flex-col justify-between">
                                <div>
                                    <div>Blitar, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</div>
                                    <div class="font-bold">Koordinator Cabang Lomba</div>
                                    <div class="font-bold">MTsN 1 Blitar</div>
                                </div>
                                <div class="pt-12">
                                    <div class="font-black text-slate-950 underline underline-offset-2">
                                        {{ Auth::user()->name ?: 'PANITIA PELAKSANA' }}
                                    </div>
                                    <div class="text-[10px] text-slate-500">{{ $appSettings['event_name'] ?? 'Milad ke-57' }} • {{ $appSettings['institution_name'] ?? 'MTsN 1 Blitar' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Page Info (Tetap di bagian paling bawah halaman) -->
                <div class="mt-4 pt-2 border-t border-slate-200 flex items-center justify-between text-[9px] text-slate-400 font-mono">
                    <span>Dokumen Resmi Panitia {{ $appSettings['event_name'] ?? 'Milad ke-57' }} {{ $appSettings['institution_name'] ?? 'MTsN 1 Blitar' }} • Aplikasi {{ $appSettings['app_name'] ?? 'TALENTA' }}</span>
                    <span>Halaman {{ $pageIndex + 1 }} dari {{ count($pages) }}</span>
                </div>

            </div>
        @empty
            <div class="bg-white p-12 rounded-3xl text-center text-slate-400">
                Belum ada data pendaftar yang dapat dicetak.
            </div>
        @endforelse

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
