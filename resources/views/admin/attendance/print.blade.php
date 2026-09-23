<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Hadir Peserta - {{ $competition->name ?? 'Semua Cabang' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="{{ asset('vendor/lucide/lucide.min.js') }}"></script>
    <style>
        @media print {
            @page {
                size: A4 portrait;
                margin: 10mm 12mm 10mm 12mm;
            }
            html, body {
                background: #ffffff !important;
                color: #000000 !important;
                font-family: Arial, Helvetica, sans-serif !important;
                font-size: 10pt !important;
                line-height: 1.25 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .print-page {
                page-break-after: auto;
                padding: 0 !important;
                margin: 0 !important;
                border: none !important;
                box-shadow: none !important;
                max-width: 100% !important;
                width: 100% !important;
            }
            table {
                border-collapse: collapse !important;
                width: 100% !important;
            }
            th, td {
                border: 1px solid #000000 !important;
            }
        }

        body {
            background-color: #f1f5f9;
            color: #0f172a;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10pt;
        }

        .kop-double-line {
            border-bottom: 3px double #0f172a;
        }
    </style>
</head>
<body class="p-4 sm:p-8">

    <!-- Action Bar -->
    <div class="no-print max-w-4xl mx-auto mb-6 flex items-center justify-between bg-white p-4 rounded-2xl shadow-sm border border-slate-200">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-cyan-50 text-cyan-600 flex items-center justify-center font-bold">
                <i data-lucide="clipboard-check" class="w-5 h-5"></i>
            </div>
            <div>
                <h1 class="text-sm font-bold text-slate-800">Cetak Lembar Daftar Hadir Peserta</h1>
                <p class="text-xs text-slate-500">{{ $competition->name ?? 'Semua Cabang' }} • {{ $registrations->count() }} Peserta</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="window.history.back()" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-xs font-bold text-slate-700 transition cursor-pointer">
                Kembali
            </button>
            <button onclick="window.print()" class="inline-flex items-center gap-2 px-5 py-2 rounded-xl bg-cyan-600 hover:bg-cyan-700 text-white font-bold text-xs shadow-md transition cursor-pointer">
                <i data-lucide="printer" class="w-4 h-4"></i>
                <span>Cetak Lembar Presensi</span>
            </button>
        </div>
    </div>

    <!-- Printable Sheet -->
    <div class="print-page max-w-4xl mx-auto bg-white p-8 sm:p-10 rounded-2xl shadow-sm border border-slate-200">
        
        <!-- KOP SURAT -->
        @php
            $kopImage = $appSettings['kop_kegiatan'] ?? ($appSettings['kop_lembaga'] ?? ($appSettings['letterhead_image'] ?? null));
        @endphp
        @if(!empty($kopImage))
            <div class="mb-4 w-full flex justify-center">
                <img src="{{ asset('storage/' . $kopImage) }}" alt="Kop Surat" class="w-full h-auto max-h-[135px] object-contain block">
            </div>
        @else
            <div class="kop-header pb-2 mb-4 kop-double-line flex items-center justify-between gap-4">
                <div class="w-16 h-16 shrink-0 flex items-center justify-center">
                    @if(!empty($appSettings['app_logo']))
                        <img src="{{ asset('storage/' . $appSettings['app_logo']) }}" alt="Logo" class="max-h-16 max-w-16 object-contain">
                    @else
                        <div class="w-14 h-14 rounded-2xl bg-emerald-700 text-white font-black flex flex-col items-center justify-center shadow-xs">
                            <span class="text-[9px] leading-none">MTsN 1</span>
                            <span class="text-xs font-black leading-none mt-0.5">BLITAR</span>
                        </div>
                    @endif
                </div>
                <div class="flex-1 text-center space-y-0.5">
                    <div class="text-[11px] font-bold tracking-wider uppercase">KEMENTERIAN AGAMA REPUBLIK INDONESIA</div>
                    <div class="text-[11px] font-bold tracking-wider uppercase">KANTOR KEMENTERIAN AGAMA KABUPATEN BLITAR</div>
                    <div class="text-base font-black tracking-wide uppercase">{{ $appSettings['institution_name'] ?? 'MADRASAH TSANAWIYAH NEGERI 1 BLITAR' }}</div>
                    <div class="text-xs font-black text-emerald-800 tracking-wider uppercase">PANITIA PELAKSANA {{ $appSettings['app_name'] ?? 'TALENTA 2026' }}</div>
                    <div class="text-[9px] text-slate-500">
                        {{ $appSettings['address'] ?? 'Jl. Raya Dandong No. 01 Srengat, Blitar, Jawa Timur 66152' }}
                    </div>
                </div>
                <div class="w-16 h-16 shrink-0 flex items-center justify-center">
                    @if(!empty($appSettings['event_logo']))
                        <img src="{{ asset('storage/' . $appSettings['event_logo']) }}" alt="Event Logo" class="max-h-16 max-w-16 object-contain">
                    @endif
                </div>
            </div>
        @endif

        <!-- JUDUL DOKUMEN -->
        <div class="text-center mb-4">
            <h2 class="text-base font-black uppercase tracking-wider underline decoration-2 underline-offset-4">
                DAFTAR HADIR PESERTA & REGISTRASI ULANG
            </h2>
            <p class="text-xs font-bold uppercase mt-1 text-slate-700">
                CABANG LOMBA: {{ $competition->name ?? 'SEMUA CABANG' }}
            </p>
        </div>

        <!-- INFO RINGKASAN -->
        <div class="mb-4 grid grid-cols-3 gap-2 text-xs border border-slate-300 p-2.5 rounded-lg bg-slate-50">
            <div>
                <span class="text-slate-500 block text-[9px] uppercase font-bold">Total Terdaftar:</span>
                <span class="font-bold text-slate-900">{{ $registrations->count() }} Peserta</span>
            </div>
            <div>
                <span class="text-slate-500 block text-[9px] uppercase font-bold">Sudah Hadir:</span>
                <span class="font-bold text-emerald-700">{{ $registrations->where('is_attended', true)->count() }} Peserta</span>
            </div>
            <div>
                <span class="text-slate-500 block text-[9px] uppercase font-bold">Belum Hadir:</span>
                <span class="font-bold text-amber-700">{{ $registrations->where('is_attended', false)->count() }} Peserta</span>
            </div>
        </div>

        <!-- TABEL DATA KEHADIRAN -->
        <table class="w-full text-left text-xs mb-6 border border-slate-900">
            <thead>
                <tr class="bg-slate-100 font-bold uppercase text-[9px] tracking-wider text-slate-800">
                    <th class="py-2 px-2 text-center w-8 border border-slate-900">No</th>
                    <th class="py-2 px-2 text-center w-24 border border-slate-900">No. Peserta</th>
                    <th class="py-2 px-3 border border-slate-900">Nama Peserta / Tim</th>
                    <th class="py-2 px-3 border border-slate-900">Asal Madrasah / Sekolah</th>
                    <th class="py-2 px-2 text-center w-24 border border-slate-900">Status Kehadiran</th>
                    <th class="py-2 px-2 text-center w-24 border border-slate-900">Waktu Presensi</th>
                    <th class="py-2 px-3 text-center w-24 border border-slate-900">Tanda Tangan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($registrations as $idx => $r)
                    <tr>
                        <td class="py-1.5 px-2 text-center font-mono border border-slate-900 text-[10px]">
                            {{ $idx + 1 }}
                        </td>
                        <td class="py-1.5 px-2 text-center font-mono font-bold border border-slate-900 text-[10px]">
                            {{ $r->participant_number ?: $r->registration_code }}
                        </td>
                        <td class="py-1.5 px-3 border border-slate-900 font-bold text-[10px]">
                            {{ $r->display_name }}
                            @if($r->sub_category)
                                <span class="block text-[8px] font-normal text-slate-600">({{ $r->sub_category }})</span>
                            @endif
                        </td>
                        <td class="py-1.5 px-3 border border-slate-900 text-[10px]">
                            {{ $r->display_school ?: ($r->institution_name ?: '-') }}
                        </td>
                        <td class="py-1.5 px-2 text-center border border-slate-900 text-[9px] font-bold">
                            @if($r->is_attended)
                                <span class="text-emerald-800">HADIR</span>
                            @else
                                <span class="text-slate-400">BELUM</span>
                            @endif
                        </td>
                        <td class="py-1.5 px-2 text-center font-mono border border-slate-900 text-[9px]">
                            {{ $r->attended_at ? $r->attended_at->format('H:i') : '-' }}
                        </td>
                        <td class="py-1.5 px-3 text-center border border-slate-900 text-[8px] text-slate-400">
                            {{ $r->is_attended ? 'Terverifikasi' : '..................' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-6 text-center text-slate-500 border border-slate-900">
                            Belum ada data peserta terdaftar untuk cabang lomba ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- TANDA TANGAN PANITIA -->
        <div class="avoid-break mt-6 flex justify-between items-start text-xs text-slate-800">
            <div class="text-center w-60">
                <div>Mengetahui,</div>
                <div class="font-bold">Ketua Panitia Pelaksana,</div>
                <div class="pt-16 font-black underline underline-offset-2">
                    {{ $appSettings['committee_chairman_name'] ?? 'PANITIA PELAKSANA' }}
                </div>
                <div class="text-[9px] text-slate-500">NIP. {{ $appSettings['committee_chairman_nip'] ?? '-' }}</div>
            </div>

            <div class="text-center w-60">
                <div>Blitar, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</div>
                <div class="font-bold">Koordinator / PIC Cabang Lomba,</div>
                <div class="pt-16 font-black underline underline-offset-2">
                    {{ $competition->pic->name ?? 'PIC CABANG LOMBA' }}
                </div>
                <div class="text-[9px] text-slate-500">{{ $competition->name ?? 'Cabang Lomba' }}</div>
            </div>
        </div>

    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
