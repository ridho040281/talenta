<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit {{ $competition->name }} — {{ $appSettings['app_name'] ?? 'TALENTA' }} {{ $appSettings['institution_name'] ?? 'MTsN 1 Blitar' }}</title>

    <!-- Favicon -->
    @if(!empty($appSettings['favicon']))
        <link rel="icon" type="image/png" href="{{ asset('storage/' . $appSettings['favicon']) }}">
        <link rel="shortcut icon" href="{{ asset('storage/' . $appSettings['favicon']) }}">
        <link rel="apple-touch-icon" href="{{ asset('storage/' . $appSettings['favicon']) }}">
    @else
        <link rel="icon" href="{{ asset('favicon.ico') }}">
    @endif

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }

        /* === Global cursor pointer for all interactive elements === */
        button, [type='button'], [type='submit'], [type='reset'], a, select, input[type='checkbox'], input[type='radio'], label[class*='cursor-pointer'] {
            cursor: pointer !important;
        }

        /* === Sama persis dengan admin.blade.php body background === */
        body {
            background-color: #141c2e;
            background-image:
                radial-gradient(at 15% 15%, rgba(78, 110, 255, 0.22) 0px, transparent 55%),
                radial-gradient(at 85% 10%, rgba(122, 90, 248, 0.20) 0px, transparent 50%),
                radial-gradient(at 50% 50%, rgba(30, 41, 59, 0.5) 0px, transparent 70%),
                radial-gradient(at 75% 85%, rgba(255, 88, 213, 0.12) 0px, transparent 55%),
                radial-gradient(at 20% 80%, rgba(16, 185, 129, 0.10) 0px, transparent 50%),
                linear-gradient(180deg, #182338 0%, #131b2e 50%, #0e1524 100%);
            background-attachment: fixed;
            color: #F8FAFC;
            min-height: 100vh;
        }

        /* Card sama dengan card admin panel */
        .card-admin {
            background: rgba(22, 31, 48, 0.88);
            border: 1px solid rgba(255, 255, 255, 0.09);
            backdrop-filter: blur(12px);
        }

        /* Input sama dengan admin panel */
        .input-admin {
            background: rgba(12, 17, 29, 0.92);
            border: 1px solid rgba(255, 255, 255, 0.12);
            color: #e2e8f0;
            transition: border-color 0.2s;
        }
        .input-admin:focus {
            outline: none;
            border-color: rgba(52, 211, 153, 0.55);
            box-shadow: 0 0 0 3px rgba(52, 211, 153, 0.08);
        }
        .input-admin::placeholder { color: rgba(148,163,184,0.4); }
        .input-admin option { background: #161f30; color: #e2e8f0; }

        /* Glowing interactive buttons */
        .btn-save-glow {
            background: linear-gradient(135deg, #059669 0%, #0d9488 100%) !important;
            box-shadow: 0 4px 16px rgba(16, 185, 129, 0.28);
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer !important;
        }
        .btn-save-glow:hover {
            background: linear-gradient(135deg, #10b981 0%, #14b8a6 100%) !important;
            box-shadow: 0 6px 28px rgba(16, 185, 129, 0.55);
            transform: translateY(-2px) scale(1.02);
            filter: brightness(1.12);
        }
        .btn-save-glow:active {
            transform: translateY(1px) scale(0.98);
        }

        .btn-cancel-action {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #cbd5e1;
            transition: all 0.2s ease;
            cursor: pointer !important;
        }
        .btn-cancel-action:hover {
            background: rgba(255, 255, 255, 0.14);
            color: #ffffff;
            border-color: rgba(255, 255, 255, 0.25);
            transform: translateY(-1px);
        }
        .btn-cancel-action:active {
            transform: translateY(1px);
        }
    </style>
</head>
<body class="font-sans antialiased min-h-screen" x-data="editPageApp()" x-init="init()">

    <!-- TOP STICKY HEADER (Clean & Minimalist) -->
    <div style="position: sticky; top: 0; z-index: 100; background: rgba(9,13,23,0.97); border-bottom: 1px solid rgba(255,255,255,0.09); padding: 14px 32px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 4px 32px rgba(0,0,0,0.5);">
        <div class="flex items-center gap-3.5 min-w-0">
            <a href="{{ route('admin.competitions') }}" class="btn-cancel-action flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition shrink-0 cursor-pointer">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Kembali ke Daftar</span>
            </a>
            <div class="min-w-0">
                <h3 class="text-sm sm:text-base font-black text-white truncate">Edit Cabang Perlombaan</h3>
                <p class="text-xs text-slate-400 hidden sm:block truncate">Perbarui informasi <strong class="text-emerald-400">{{ $competition->name }}</strong></p>
            </div>
        </div>
    </div>

    <!-- FORM BODY -->
    <div class="max-w-5xl w-full mx-auto p-4 sm:p-6 lg:p-8 space-y-5">
        <form id="editCompetitionForm" action="{{ route('admin.competitions.update', $competition->id) }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf

            @if (isset($errors) && $errors->any())
                <div class="bg-rose-500/10 border border-rose-500/25 rounded-2xl p-4">
                    <ul class="text-xs text-rose-400 space-y-1 list-disc pl-4">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- FORM CARD UTAMA -->
            <div class="card-admin rounded-2xl p-5 sm:p-7 space-y-5">

                <!-- Section Header -->
                <div class="flex items-center gap-3 pb-4 border-b border-white/[0.07]">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0" style="background: rgba(78,110,255,0.15); border: 1px solid rgba(78,110,255,0.25);">
                        <i data-lucide="settings" class="w-4 h-4 text-[#84D0FF]"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-black text-white">Informasi Cabang Lomba</h4>
                        <p class="text-xs text-slate-500">Data dasar, lokasi, dan pengaturan pendaftaran</p>
                    </div>
                </div>

                <div class="space-y-4">

                    <!-- Row 1: Jenis Lomba, Nama Lomba, Kode Singkat, Urutan -->
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                        <div class="md:col-span-3">
                            <label class="block text-[10px] font-black uppercase tracking-widest text-slate-500 mb-1.5">Jenis Lomba</label>
                            <select name="category_id" required class="input-admin block w-full px-3 py-2.5 rounded-xl text-xs font-semibold">
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ $competition->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-4">
                            <label class="block text-[10px] font-black uppercase tracking-widest text-slate-500 mb-1.5">Nama Lomba</label>
                            <input name="name" type="text" required value="{{ old('name', $competition->name) }}" class="input-admin block w-full px-3 py-2.5 rounded-xl text-xs font-semibold">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-[10px] font-black uppercase tracking-widest text-slate-500 mb-1.5">Kode Singkat</label>
                            <input name="code" type="text" required value="{{ old('code', $competition->code) }}" style="text-transform:uppercase" class="input-admin block w-full px-3 py-2.5 rounded-xl text-xs font-mono font-black">
                        </div>
                        <div class="md:col-span-3">
                            <label class="block text-[10px] font-black uppercase tracking-widest mb-1.5 flex items-center gap-1" style="color: rgba(245,158,11,0.8);">
                                <i data-lucide="list-ordered" class="w-3 h-3"></i> Urutan Tampilan
                            </label>
                            <input name="order" type="number" min="1" value="{{ old('order', $competition->order) }}" class="block w-full px-3 py-2.5 rounded-xl text-xs font-mono font-black" style="background: rgba(245,158,11,0.07); border: 1px solid rgba(245,158,11,0.22); color: #fcd34d; outline: none;">
                        </div>
                    </div>

                    <!-- Row 2 -->
                    <div class="grid grid-cols-2 sm:grid-cols-12 gap-3">
                        <div class="sm:col-span-3">
                            <label class="block text-[10px] font-black uppercase tracking-widest text-slate-500 mb-1.5">Kategori Lomba</label>
                            <select name="type" required class="input-admin block w-full px-2.5 py-2.5 rounded-xl text-xs font-bold">
                                <option value="individu" {{ $competition->type == 'individu' ? 'selected' : '' }}>Individu</option>
                                <option value="tim" {{ $competition->type == 'tim' ? 'selected' : '' }}>Tim</option>
                                <option value="kelompok" {{ $competition->type == 'kelompok' ? 'selected' : '' }}>Kelompok</option>
                                <option value="regu" {{ $competition->type == 'regu' ? 'selected' : '' }}>Regu</option>
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-[10px] font-black uppercase tracking-widest text-slate-500 mb-1.5">Min Anggota</label>
                            <input name="min_members" type="number" min="1" required value="{{ old('min_members', $competition->min_members) }}" class="input-admin block w-full px-2.5 py-2.5 rounded-xl text-xs font-bold">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-[10px] font-black uppercase tracking-widest text-slate-500 mb-1.5">Maks Anggota</label>
                            <input name="max_members" type="number" min="1" required value="{{ old('max_members', $competition->max_members) }}" class="input-admin block w-full px-2.5 py-2.5 rounded-xl text-xs font-bold">
                        </div>
                        <div class="col-span-2 sm:col-span-3">
                            <label class="block text-[10px] font-black uppercase tracking-widest text-slate-500 mb-1.5">Lokasi / Venue</label>
                            <input name="venue" type="text" value="{{ old('venue', $competition->venue) }}" placeholder="GOR MTsN 1 Blitar" class="input-admin block w-full px-3 py-2.5 rounded-xl text-xs font-medium">
                        </div>
                        <div class="col-span-2 sm:col-span-2">
                            <label class="block text-[10px] font-black uppercase tracking-widest text-slate-500 mb-1.5">Waktu</label>
                            <input name="schedule_time" type="text" value="{{ old('schedule_time', $competition->schedule_time) }}" placeholder="08.00 WIB" class="input-admin block w-full px-3 py-2.5 rounded-xl text-xs font-medium">
                        </div>
                    </div>

                    <!-- Row 3: Biaya, Kuota, Status -->
                    @php $isMultiTier = in_array($competition->code, ['BLT', 'MTQ', 'POP', 'TMJ']); @endphp
                    @if(!$isMultiTier)
                    <div class="grid grid-cols-2 sm:grid-cols-12 gap-3">
                        <div class="sm:col-span-4">
                            <label class="block text-[10px] font-black uppercase tracking-widest text-slate-500 mb-1.5">Biaya Pendaftaran (Rp)</label>
                            <input name="registration_fee" type="number" step="1000" min="0" value="{{ old('registration_fee', $competition->registration_fee) }}" class="input-admin block w-full px-3 py-2.5 rounded-xl text-xs font-mono font-bold">
                            <p class="text-[10px] text-slate-600 mt-0.5">Isi 0 untuk Gratis</p>
                        </div>
                        <div class="sm:col-span-4">
                            <label class="block text-[10px] font-black uppercase tracking-widest text-slate-500 mb-1.5">Kuota Total</label>
                            <input name="quota" type="number" min="0" value="{{ old('quota', $competition->quota) }}" class="input-admin block w-full px-3 py-2.5 rounded-xl text-xs font-bold">
                            <p class="text-[10px] text-slate-600 mt-0.5">Isi 0 untuk Tak Terbatas (∞)</p>
                        </div>
                        <div class="col-span-2 sm:col-span-4">
                            <label class="block text-[10px] font-black uppercase tracking-widest text-slate-500 mb-1.5">Status Pendaftaran</label>
                            <select name="status" required class="input-admin block w-full px-3 py-2.5 rounded-xl text-xs font-bold">
                                <option value="buka" {{ $competition->status == 'buka' ? 'selected' : '' }}>Buka</option>
                                <option value="tutup" {{ $competition->status == 'tutup' ? 'selected' : '' }}>Tutup</option>
                                <option value="selesai" {{ $competition->status == 'selesai' ? 'selected' : '' }}>Selesai</option>
                            </select>
                        </div>
                    </div>
                    @endif

                    <!-- PENGATURAN PIC & NOTIFIKASI WHATSAPP CABANG LOMBA -->
                    <div class="p-4 sm:p-5 rounded-2xl space-y-4" style="background: rgba(78,110,255,0.06); border: 1px solid rgba(78,110,255,0.22);">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-white/[0.08]">
                            <div>
                                <label class="text-xs font-black uppercase tracking-wider text-[#84D0FF] flex items-center gap-2">
                                    <i data-lucide="user-check" class="w-4 h-4 text-[#84D0FF]"></i>
                                    <span>Pengaturan PIC & Notifikasi WhatsApp Lomba</span>
                                </label>
                                <p class="text-[11px] text-slate-400 mt-0.5">Tentukan koordinator penanggung jawab, saklar notifikasi WA, dan nomor asisten lapangan lomba ini</p>
                            </div>
                            <span class="text-[10px] font-bold text-[#84D0FF] px-2.5 py-1 rounded-full bg-[#4E6EFF]/20 border border-[#4E6EFF]/30 self-start sm:self-auto font-mono">
                                PIC & WhatsApp
                            </span>
                        </div>

                        <div class="space-y-4">
                            <!-- Baris 1: PIC Utama (50%) & Saklar Notifikasi WA (50%) -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-start">
                                <!-- 1. Koordinator PIC Utama -->
                                <div class="space-y-1.5">
                                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400">
                                        Koordinator PIC Utama
                                    </label>
                                    <select name="pic_id" class="input-admin block w-full px-3.5 py-2.5 rounded-xl text-xs font-semibold">
                                        <option value="">-- Belum Ditugaskan --</option>
                                        @foreach($pics as $pic)
                                            <option value="{{ $pic->id }}" {{ old('pic_id', $competition->pic_id) == $pic->id ? 'selected' : '' }}>
                                                {{ $pic->name }} {{ $pic->phone ? '('.$pic->phone.')' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <p class="text-[10px] text-slate-500">Akun resmi dengan hak akses login mengelola lomba ini di Dashboard PIC.</p>
                                </div>

                                <!-- 2. Saklar Notifikasi WA -->
                                <div class="space-y-1.5">
                                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400">
                                        Status Notifikasi WhatsApp Pendaftar
                                    </label>
                                    <select name="notify_pic" class="input-admin block w-full px-3.5 py-2.5 rounded-xl text-xs font-bold {{ $competition->notify_pic ? 'text-emerald-400' : 'text-slate-400' }}">
                                        <option value="1" {{ old('notify_pic', $competition->notify_pic) ? 'selected' : '' }}>🟢 AKTIF (Kirim Pesan WA Otomatis)</option>
                                        <option value="0" {{ !old('notify_pic', $competition->notify_pic) ? 'selected' : '' }}>⚪ NONAKTIF (Cek via Dashboard Saja)</option>
                                    </select>
                                    <p class="text-[10px] text-slate-500">Pilihan untuk mengirimkan pesan WA otomatis ke panitia setiap ada pendaftar baru.</p>
                                </div>
                            </div>

                            <!-- Baris 2: Nomor Asisten / Panitia Lapangan (Lebar Penuh) -->
                            <div class="space-y-1.5 pt-1">
                                <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 flex items-center justify-between">
                                    <span>Nomor WhatsApp Tambahan / Asisten Lapangan</span>
                                    <span class="text-[9px] font-normal text-slate-500 font-mono">(Opsional)</span>
                                </label>
                                <input type="text" name="assistant_phones" value="{{ old('assistant_phones', $competition->assistant_phones) }}" placeholder="Contoh: 081234567890, 089876543210 (pisahkan tanda koma jika lebih dari satu)" class="input-admin block w-full px-3.5 py-2.5 rounded-xl text-xs font-mono">
                                <p class="text-[10px] text-slate-500">Masukkan 1 atau beberapa nomor WA panitia/juri pendamping (pisahkan koma). Mereka otomatis ikut menerima notifikasi saat saklar aktif.</p>
                            </div>
                        </div>

                        <div class="pt-2 border-t border-white/[0.06] flex items-center gap-2 text-[11px] text-slate-400">
                            <i data-lucide="info" class="w-3.5 h-3.5 text-[#84D0FF] shrink-0"></i>
                            <span>Jika status <strong>AKTIF</strong>, notifikasi WA peserta baru akan dikirimkan ke PIC Utama dan nomor asisten tambahan yang didaftarkan.</span>
                        </div>
                    </div>

                    @if($isMultiTier)
                        @php $mode = request('mode', 'all'); @endphp

                        {{-- MTQ & POP SINGER --}}
                        @if(in_array($competition->code, ['MTQ', 'POP']))
                            @php
                                $prefix = strtolower($competition->code);
                                $fee_pa = old($prefix . '_fee_pa', \App\Models\AppSetting::get($prefix . '_fee_pa', $competition->registration_fee));
                                $quota_pa = old($prefix . '_quota_pa', \App\Models\AppSetting::get($prefix . '_quota_pa', ceil($competition->quota / 2)));
                                $pic_pa = old($prefix . '_pic_pa', \App\Models\AppSetting::get($prefix . '_pic_pa', $competition->pic_id));
                                $status_pa = old($prefix . '_status_pa', \App\Models\AppSetting::get($prefix . '_status_pa', $competition->status ?? 'buka'));

                                $fee_pi = old($prefix . '_fee_pi', \App\Models\AppSetting::get($prefix . '_fee_pi', $competition->registration_fee));
                                $quota_pi = old($prefix . '_quota_pi', \App\Models\AppSetting::get($prefix . '_quota_pi', floor($competition->quota / 2)));
                                $pic_pi = old($prefix . '_pic_pi', \App\Models\AppSetting::get($prefix . '_pic_pi', $competition->pic_id));
                                $status_pi = old($prefix . '_status_pi', \App\Models\AppSetting::get($prefix . '_status_pi', $competition->status ?? 'buka'));
                            @endphp

                            <div class="space-y-4 pt-1">
                                <!-- SEKTOR PUTRA (PA) -->
                                <div class="p-4 sm:p-5 rounded-2xl space-y-3.5 transition {{ $mode === 'pa' ? 'ring-2 ring-emerald-400' : '' }}" style="background: rgba(16,185,129,0.06); border: 1px solid rgba(16,185,129,0.22);">
                                    <div class="flex items-center justify-between pb-2" style="border-bottom: 1px solid rgba(16,185,129,0.18);">
                                        <span class="text-xs font-black text-emerald-300 flex items-center gap-2">
                                            <i data-lucide="user" class="w-4 h-4 text-emerald-400"></i>
                                            <span>PENGATURAN {{ strtoupper($competition->name) }} — INDIVIDU PUTRA (PA)</span>
                                        </span>
                                        <span class="text-[10px] font-bold text-emerald-300 px-2 py-0.5 rounded font-mono" style="background: rgba(16,185,129,0.18);">Individu • PA</span>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                                        <div>
                                            <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Biaya Putra (Rp)</label>
                                            <input name="{{ $prefix }}_fee_pa" type="number" step="1000" min="0" value="{{ $fee_pa }}" class="input-admin block w-full px-3 py-2 rounded-xl text-xs font-mono font-bold text-emerald-400">
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Kuota Putra (Peserta)</label>
                                            <input name="{{ $prefix }}_quota_pa" type="number" min="0" value="{{ $quota_pa }}" class="input-admin block w-full px-3 py-2 rounded-xl text-xs font-bold text-white">
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Petugas PIC Putra</label>
                                            <select name="{{ $prefix }}_pic_pa" class="input-admin block w-full px-3 py-2 rounded-xl text-xs font-semibold">
                                                <option value="">-- Sama PIC Utama --</option>
                                                @foreach($pics as $p)
                                                    <option value="{{ $p->id }}" {{ $pic_pa == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Status Pendaftaran</label>
                                            <select name="{{ $prefix }}_status_pa" class="input-admin block w-full px-3 py-2 rounded-xl text-xs font-semibold">
                                                <option value="buka" {{ $status_pa === 'buka' ? 'selected' : '' }}>Buka</option>
                                                <option value="tutup" {{ $status_pa === 'tutup' ? 'selected' : '' }}>Tutup</option>
                                                <option value="selesai" {{ $status_pa === 'selesai' ? 'selected' : '' }}>Selesai</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- SEKTOR PUTRI (PI) -->
                                <div class="p-4 sm:p-5 rounded-2xl space-y-3.5 transition {{ $mode === 'pi' ? 'ring-2 ring-pink-400' : '' }}" style="background: rgba(236,72,153,0.06); border: 1px solid rgba(236,72,153,0.22);">
                                    <div class="flex items-center justify-between pb-2" style="border-bottom: 1px solid rgba(236,72,153,0.18);">
                                        <span class="text-xs font-black text-pink-300 flex items-center gap-2">
                                            <i data-lucide="user" class="w-4 h-4 text-pink-400"></i>
                                            <span>PENGATURAN {{ strtoupper($competition->name) }} — INDIVIDU PUTRI (PI)</span>
                                        </span>
                                        <span class="text-[10px] font-bold text-pink-300 px-2 py-0.5 rounded font-mono" style="background: rgba(236,72,153,0.18);">Individu • PI</span>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                                        <div>
                                            <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Biaya Putri (Rp)</label>
                                            <input name="{{ $prefix }}_fee_pi" type="number" step="1000" min="0" value="{{ $fee_pi }}" class="input-admin block w-full px-3 py-2 rounded-xl text-xs font-mono font-bold text-pink-400">
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Kuota Putri (Peserta)</label>
                                            <input name="{{ $prefix }}_quota_pi" type="number" min="0" value="{{ $quota_pi }}" class="input-admin block w-full px-3 py-2 rounded-xl text-xs font-bold text-white">
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Petugas PIC Putri</label>
                                            <select name="{{ $prefix }}_pic_pi" class="input-admin block w-full px-3 py-2 rounded-xl text-xs font-semibold">
                                                <option value="">-- Sama PIC Utama --</option>
                                                @foreach($pics as $p)
                                                    <option value="{{ $p->id }}" {{ $pic_pi == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Status Pendaftaran</label>
                                            <select name="{{ $prefix }}_status_pi" class="input-admin block w-full px-3 py-2 rounded-xl text-xs font-semibold">
                                                <option value="buka" {{ $status_pi === 'buka' ? 'selected' : '' }}>Buka</option>
                                                <option value="tutup" {{ $status_pi === 'tutup' ? 'selected' : '' }}>Tutup</option>
                                                <option value="selesai" {{ $status_pi === 'selesai' ? 'selected' : '' }}>Selesai</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- KHUSUS POP SINGER: DAFTAR LAGU PILIHAN -->
                                @if($competition->code === 'POP' || \Illuminate\Support\Str::contains(strtolower($competition->slug), 'pop') || \Illuminate\Support\Str::contains(strtolower($competition->name), 'pop'))
                                <div class="p-4 sm:p-5 rounded-2xl space-y-3" style="background: rgba(122,90,248,0.06); border: 1px solid rgba(122,90,248,0.22);">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2" style="border-bottom: 1px solid rgba(122,90,248,0.18);">
                                        <div>
                                            <span class="text-xs font-black text-[#A594FD] flex items-center gap-2">
                                                <i data-lucide="music" class="w-4 h-4 text-[#7A5AF8]"></i>
                                                <span>DAFTAR LAGU PILIHAN POP SINGER (DROPDOWN PENDAFTARAN)</span>
                                            </span>
                                            <p class="text-[11px] text-slate-400 mt-0.5">Daftar judul lagu yang otomatis muncul pada menu drop-down saat peserta mendaftar Pop Singer</p>
                                        </div>
                                        <span class="text-[10px] font-bold text-[#A594FD] px-2.5 py-1 rounded-full font-mono self-start sm:self-auto" style="background: rgba(122,90,248,0.18); border: 1px solid rgba(122,90,248,0.3);">
                                            1 Baris = 1 Judul Lagu
                                        </span>
                                    </div>
                                    <div>
                                        <textarea name="pop_song_options" rows="7" class="input-admin block w-full px-4 py-3 rounded-xl text-xs font-mono leading-relaxed" placeholder="Contoh:&#10;Deen Assalam&#10;Rahmatun Lil'Alameen&#10;Ya Maulana&#10;Aisyah Istri Rasulullah">{{ old('pop_song_options', $competition->raw_song_options) }}</textarea>
                                        <div class="mt-2 flex items-start gap-2 text-[11px] text-slate-400">
                                            <i data-lucide="info" class="w-3.5 h-3.5 text-[#7A5AF8] shrink-0 mt-0.5"></i>
                                            <span>Ketikkan atau tempel (paste) daftar judul lagu pilihan di atas. Setiap baris baru akan langsung menjadi pilihan drop-down bagi calon peserta saat mengisi formulir lomba.</span>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>
                        @endif

                        {{-- TENIS MEJA (TMJ) --}}
                        @if($competition->code === 'TMJ')
                            @php
                                $mode = request('mode', 'all');
                                $tmjTiers = [
                                    ['key' => 'a_tunggal_pa', 'label' => 'Tunggal Putra (PA) — Kat A (Kelas 1–3 SD/MI)', 'badge' => 'Tunggal PA • Kat A', 'color' => 'emerald', 'mode_match' => 'tmj_pa_a'],
                                    ['key' => 'b_tunggal_pa', 'label' => 'Tunggal Putra (PA) — Kat B (Kelas 4–6 SD/MI)', 'badge' => 'Tunggal PA • Kat B', 'color' => 'emerald', 'mode_match' => 'tmj_pa_b'],
                                    ['key' => 'a_tunggal_pi', 'label' => 'Tunggal Putri (PI) — Kat A (Kelas 1–3 SD/MI)', 'badge' => 'Tunggal PI • Kat A', 'color' => 'pink', 'mode_match' => 'tmj_pi_a'],
                                    ['key' => 'b_tunggal_pi', 'label' => 'Tunggal Putri (PI) — Kat B (Kelas 4–6 SD/MI)', 'badge' => 'Tunggal PI • Kat B', 'color' => 'pink', 'mode_match' => 'tmj_pi_b'],
                                ];
                            @endphp

                            <div class="space-y-4 pt-1">
                                @foreach($tmjTiers as $t)
                                    @php
                                        $fee = old('tmj_fee_' . $t['key'], \App\Models\AppSetting::get('tmj_fee_' . $t['key'], $competition->registration_fee ?: 35000));
                                        $quota = old('tmj_quota_' . $t['key'], \App\Models\AppSetting::get('tmj_quota_' . $t['key'], floor($competition->quota / 4)));
                                        $picKey = str_contains($t['key'], '_pa') ? 'tmj_pic_tunggal_pa' : 'tmj_pic_tunggal_pi';
                                        $pic = old($picKey, \App\Models\AppSetting::get($picKey, $competition->pic_id));
                                        $status = old('tmj_status_' . $t['key'], \App\Models\AppSetting::get('tmj_status_' . $t['key'], $competition->status ?? 'buka'));
                                        $isPa = $t['color'] === 'emerald';
                                    @endphp
                                    <div class="p-4 sm:p-5 rounded-2xl space-y-3.5 transition {{ $mode === $t['mode_match'] ? 'ring-2 ' . ($isPa ? 'ring-emerald-400' : 'ring-pink-400') : '' }}" style="background: {{ $isPa ? 'rgba(16,185,129,0.06)' : 'rgba(236,72,153,0.06)' }}; border: 1px solid {{ $isPa ? 'rgba(16,185,129,0.22)' : 'rgba(236,72,153,0.22)' }};">
                                        <div class="flex items-center justify-between pb-2" style="border-bottom: 1px solid {{ $isPa ? 'rgba(16,185,129,0.18)' : 'rgba(236,72,153,0.18)' }};">
                                            <span class="text-xs font-black {{ $isPa ? 'text-emerald-300' : 'text-pink-300' }} flex items-center gap-2">
                                                <i data-lucide="user" class="w-4 h-4"></i>
                                                <span>PENGATURAN TENIS MEJA — {{ strtoupper($t['label']) }}</span>
                                            </span>
                                            <span class="text-[10px] font-bold px-2 py-0.5 rounded font-mono" style="background: {{ $isPa ? 'rgba(16,185,129,0.18)' : 'rgba(236,72,153,0.18)' }}; color: {{ $isPa ? '#34d399' : '#f472b6' }};">{{ $t['badge'] }}</span>
                                        </div>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                                            <div>
                                                <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Biaya (Rp)</label>
                                                <input name="tmj_fee_{{ $t['key'] }}" type="number" step="1000" min="0" value="{{ $fee }}" class="input-admin block w-full px-3 py-2 rounded-xl text-xs font-mono font-bold" style="color: {{ $isPa ? '#34d399' : '#f472b6' }};">
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Kuota (Peserta)</label>
                                                <input name="tmj_quota_{{ $t['key'] }}" type="number" min="0" value="{{ $quota }}" class="input-admin block w-full px-3 py-2 rounded-xl text-xs font-bold text-white">
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Petugas PIC</label>
                                                <select name="{{ $picKey }}" class="input-admin block w-full px-3 py-2 rounded-xl text-xs font-semibold">
                                                    <option value="">-- Sama PIC Utama --</option>
                                                    @foreach($pics as $p)
                                                        <option value="{{ $p->id }}" {{ $pic == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Status Pendaftaran</label>
                                                <select name="tmj_status_{{ $t['key'] }}" class="input-admin block w-full px-3 py-2 rounded-xl text-xs font-semibold">
                                                    <option value="buka" {{ $status === 'buka' ? 'selected' : '' }}>Buka</option>
                                                    <option value="tutup" {{ $status === 'tutup' ? 'selected' : '' }}>Tutup</option>
                                                    <option value="selesai" {{ $status === 'selesai' ? 'selected' : '' }}>Selesai</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        {{-- BULU TANGKIS (BLT) --}}
                        @if($competition->code === 'BLT')
                            @php
                                $mode = request('mode', 'all');
                                $bltTiers = [
                                    ['fee_k' => 'blt_fee_a_tunggal_pa', 'quota_k' => 'blt_quota_a_tunggal_pa', 'pic_k' => 'blt_pic_tunggal_pa', 'stat_k' => 'blt_status_a_tunggal_pa', 'label' => 'Tunggal Putra (PA) — Kat A (Kelas 1–2 SD/MI)', 'badge' => 'Tunggal PA • Kat A', 'type' => 'pa', 'def_fee' => 130000, 'def_q' => 16, 'mode_match' => 'tunggal_pa_a'],
                                    ['fee_k' => 'blt_fee_b_tunggal_pa', 'quota_k' => 'blt_quota_b_tunggal_pa', 'pic_k' => 'blt_pic_tunggal_pa', 'stat_k' => 'blt_status_b_tunggal_pa', 'label' => 'Tunggal Putra (PA) — Kat B (Kelas 3–4 SD/MI)', 'badge' => 'Tunggal PA • Kat B', 'type' => 'pa', 'def_fee' => 150000, 'def_q' => 16, 'mode_match' => 'tunggal_pa_b'],
                                    ['fee_k' => 'blt_fee_c_tunggal_pa', 'quota_k' => 'blt_quota_c_tunggal_pa', 'pic_k' => 'blt_pic_tunggal_pa', 'stat_k' => 'blt_status_c_tunggal_pa', 'label' => 'Tunggal Putra (PA) — Kat C (Kelas 5–6 SD/MI)', 'badge' => 'Tunggal PA • Kat C', 'type' => 'pa', 'def_fee' => 150000, 'def_q' => 16, 'mode_match' => 'tunggal_pa_c'],

                                    ['fee_k' => 'blt_fee_a_tunggal_pi', 'quota_k' => 'blt_quota_a_tunggal_pi', 'pic_k' => 'blt_pic_tunggal_pi', 'stat_k' => 'blt_status_a_tunggal_pi', 'label' => 'Tunggal Putri (PI) — Kat A (Kelas 1–2 SD/MI)', 'badge' => 'Tunggal PI • Kat A', 'type' => 'pi', 'def_fee' => 130000, 'def_q' => 16, 'mode_match' => 'tunggal_pi_a'],
                                    ['fee_k' => 'blt_fee_b_tunggal_pi', 'quota_k' => 'blt_quota_b_tunggal_pi', 'pic_k' => 'blt_pic_tunggal_pi', 'stat_k' => 'blt_status_b_tunggal_pi', 'label' => 'Tunggal Putri (PI) — Kat B (Kelas 3–4 SD/MI)', 'badge' => 'Tunggal PI • Kat B', 'type' => 'pi', 'def_fee' => 150000, 'def_q' => 16, 'mode_match' => 'tunggal_pi_b'],
                                    ['fee_k' => 'blt_fee_c_tunggal_pi', 'quota_k' => 'blt_quota_c_tunggal_pi', 'pic_k' => 'blt_pic_tunggal_pi', 'stat_k' => 'blt_status_c_tunggal_pi', 'label' => 'Tunggal Putri (PI) — Kat C (Kelas 5–6 SD/MI)', 'badge' => 'Tunggal PI • Kat C', 'type' => 'pi', 'def_fee' => 150000, 'def_q' => 16, 'mode_match' => 'tunggal_pi_c'],

                                    ['fee_k' => 'blt_fee_ganda_pa', 'quota_k' => 'blt_quota_ganda_pa', 'pic_k' => 'blt_pic_ganda_pa', 'stat_k' => 'blt_status_ganda_pa', 'label' => 'Ganda Putra (PA) — (Kelas 3–6 SD/MI)', 'badge' => 'Ganda • PA', 'type' => 'ganda_pa', 'def_fee' => 200000, 'def_q' => 10, 'mode_match' => 'ganda_pa'],
                                    ['fee_k' => 'blt_fee_ganda_pi', 'quota_k' => 'blt_quota_ganda_pi', 'pic_k' => 'blt_pic_ganda_pi', 'stat_k' => 'blt_status_ganda_pi', 'label' => 'Ganda Putri (PI) — (Kelas 3–6 SD/MI)', 'badge' => 'Ganda • PI', 'type' => 'ganda_pi', 'def_fee' => 200000, 'def_q' => 10, 'mode_match' => 'ganda_pi'],
                                ];
                            @endphp

                            <div class="space-y-4 pt-1">
                                @foreach($bltTiers as $bt)
                                    @php
                                        $fee = old($bt['fee_k'], \App\Models\AppSetting::get($bt['fee_k'], $bt['def_fee']));
                                        $quota = old($bt['quota_k'], \App\Models\AppSetting::get($bt['quota_k'], $bt['def_q']));
                                        $pic = old($bt['pic_k'], \App\Models\AppSetting::get($bt['pic_k'], $competition->pic_id));
                                        $status = old($bt['stat_k'], \App\Models\AppSetting::get($bt['stat_k'], $competition->status ?? 'buka'));
                                        $colorClass = $bt['type'] === 'pa' ? 'emerald' : ($bt['type'] === 'pi' ? 'pink' : 'amber');
                                        $bgStyle = $colorClass === 'emerald' ? 'rgba(16,185,129,0.06)' : ($colorClass === 'pink' ? 'rgba(236,72,153,0.06)' : 'rgba(245,158,11,0.06)');
                                        $borderStyle = $colorClass === 'emerald' ? 'rgba(16,185,129,0.22)' : ($colorClass === 'pink' ? 'rgba(236,72,153,0.22)' : 'rgba(245,158,11,0.22)');
                                        $textColor = $colorClass === 'emerald' ? '#34d399' : ($colorClass === 'pink' ? '#f472b6' : '#fbbf24');
                                    @endphp
                                    <div class="p-4 sm:p-5 rounded-2xl space-y-3.5 transition {{ $mode === $bt['mode_match'] ? 'ring-2 ring-emerald-400' : '' }}" style="background: {{ $bgStyle }}; border: 1px solid {{ $borderStyle }};">
                                        <div class="flex items-center justify-between pb-2" style="border-bottom: 1px solid {{ $borderStyle }};">
                                            <span class="text-xs font-black flex items-center gap-2" style="color: {{ $textColor }};">
                                                <i data-lucide="{{ str_contains($bt['type'], 'ganda') ? 'users' : 'user' }}" class="w-4 h-4"></i>
                                                <span>PENGATURAN BULU TANGKIS — {{ strtoupper($bt['label']) }}</span>
                                            </span>
                                            <span class="text-[10px] font-bold px-2 py-0.5 rounded font-mono" style="background: {{ $bgStyle }}; color: {{ $textColor }}; border: 1px solid {{ $borderStyle }};">{{ $bt['badge'] }}</span>
                                        </div>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                                            <div>
                                                <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Biaya (Rp)</label>
                                                <input name="{{ $bt['fee_k'] }}" type="number" step="1000" min="0" value="{{ $fee }}" class="input-admin block w-full px-3 py-2 rounded-xl text-xs font-mono font-bold" style="color: {{ $textColor }};">
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Kuota (Peserta)</label>
                                                <input name="{{ $bt['quota_k'] }}" type="number" min="0" value="{{ $quota }}" class="input-admin block w-full px-3 py-2 rounded-xl text-xs font-bold text-white">
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Petugas PIC</label>
                                                <select name="{{ $bt['pic_k'] }}" class="input-admin block w-full px-3 py-2 rounded-xl text-xs font-semibold">
                                                    <option value="">-- Sama PIC Utama --</option>
                                                    @foreach($pics as $p)
                                                        <option value="{{ $p->id }}" {{ $pic == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Status Pendaftaran</label>
                                                <select name="{{ $bt['stat_k'] }}" class="input-admin block w-full px-3 py-2 rounded-xl text-xs font-semibold">
                                                    <option value="buka" {{ $status === 'buka' ? 'selected' : '' }}>Buka</option>
                                                    <option value="tutup" {{ $status === 'tutup' ? 'selected' : '' }}>Tutup</option>
                                                    <option value="selesai" {{ $status === 'selesai' ? 'selected' : '' }}>Selesai</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    @endif

                    <!-- Aturan & Petunjuk Teknis -->
                    <div x-data="{ showRules: {{ $competition->show_rules ? 'true' : 'false' }} }">
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400">Aturan & Petunjuk Teknis Singkat</label>
                            <label class="flex items-center gap-2 cursor-pointer select-none px-2.5 py-1 rounded-lg transition"
                                   :style="showRules ? 'background: rgba(16,185,129,0.12); border: 1px solid rgba(16,185,129,0.25);' : 'background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08);'">
                                <input name="show_rules" type="checkbox" value="1" x-model="showRules" class="w-3.5 h-3.5 rounded text-emerald-500 bg-slate-900 border-slate-700 focus:ring-0 cursor-pointer">
                                <span class="text-[10px] font-black tracking-wide" :class="showRules ? 'text-emerald-400' : 'text-slate-400'" x-text="showRules ? '✓ AKTIF (TAMPIL DI PESERTA)' : '✗ NONAKTIF (TIDAK TAMPIL)'"></span>
                            </label>
                        </div>
                        <textarea name="rules" rows="4" class="input-admin block w-full px-3 py-2.5 rounded-xl text-xs font-medium resize-none" placeholder="Tuliskan petunjuk teknis / aturan singkat...">{{ old('rules', $competition->rules) }}</textarea>
                        <p class="text-[10px] text-slate-500 mt-1">Centang aktif di atas jika ingin teks aturan ini ditampilkan ke peserta di halaman rincian lomba.</p>
                    </div>

                    <!-- Juknis PDF -->
                    <div class="p-4 rounded-xl space-y-3" style="background: rgba(12,17,29,0.6); border: 1px solid rgba(255,255,255,0.07);">
                        <label class="block text-[10px] font-black uppercase tracking-widest text-slate-500 flex items-center gap-2">
                            <i data-lucide="file-text" class="w-3.5 h-3.5 text-[#84D0FF]"></i>
                            Embed Link Juknis PDF / Dokumen Resmi
                        </label>
                        <span class="inline-block text-[10px] font-bold px-2 py-0.5 rounded-md" style="background: rgba(78,110,255,0.12); color: #84D0FF; border: 1px solid rgba(78,110,255,0.2);">Google Drive / URL PDF / Upload</span>
                        <input name="guidelines_file" type="text" value="{{ old('guidelines_file', $competition->guidelines_file) }}" placeholder="https://drive.google.com/..." class="input-admin block w-full px-3 py-2.5 rounded-xl text-xs font-mono">
                        <p class="text-[10px] text-slate-600">atau upload file PDF baru:</p>
                        <input name="guidelines_pdf" type="file" accept=".pdf" class="block w-full text-xs text-slate-400 file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold cursor-pointer" style="--tw-file-bg: rgba(78,110,255,0.12);">
                    </div>

                    <!-- Link Grup WhatsApp Resmi Cabang -->
                    <div class="p-4 rounded-xl space-y-2.5" style="background: rgba(12,17,29,0.6); border: 1px solid rgba(16,185,129,0.25);">
                        <label class="block text-[10px] font-black uppercase tracking-widest text-emerald-400 flex items-center gap-2">
                            <i data-lucide="message-circle" class="w-3.5 h-3.5 text-emerald-400"></i>
                            Tautan Undangan Grup WhatsApp Cabang (Opsional)
                        </label>
                        <input name="whatsapp_group_url" type="url" value="{{ old('whatsapp_group_url', $competition->whatsapp_group_url) }}" placeholder="https://chat.whatsapp.com/Gzxxxxxxxxxx" class="input-admin block w-full px-3 py-2.5 rounded-xl text-xs font-mono">
                        <p class="text-[10px] text-slate-400">Jika diisi, tombol hijau "Grup WA" otomatis muncul di samping tombol Juknis di halaman utama untuk memudahkan peserta/pembina bergabung.</p>
                    </div>

                    <!-- Checkboxes -->
                    <div class="flex flex-col sm:flex-row gap-3">
                        <label class="flex items-center gap-3 p-3 rounded-xl flex-1 cursor-pointer" style="background: rgba(122,90,248,0.08); border: 1px solid rgba(122,90,248,0.18);">
                            <input name="is_live_score" type="checkbox" value="1" {{ $competition->is_live_score ? 'checked' : '' }} class="w-4 h-4 rounded accent-violet-500">
                            <span class="text-xs font-bold" style="color: #c4b5fd;">Tampilkan di Live Score Publik</span>
                        </label>
                        <label class="flex items-center gap-3 p-3 rounded-xl flex-1 cursor-pointer" style="background: rgba(16,185,129,0.07); border: 1px solid rgba(16,185,129,0.18);">
                            <input name="show_criteria" type="checkbox" value="1" {{ $competition->show_criteria ? 'checked' : '' }} class="w-4 h-4 rounded accent-emerald-500">
                            <span class="text-xs font-bold text-emerald-400">Tampilkan Kriteria Penilaian ke Publik</span>
                        </label>
                    </div>

                </div>
            </div>

            <!-- PENGATURAN LAYAR PANGGUNG & STAGE TIMER CARD -->
            <div class="card-admin rounded-2xl p-5 sm:p-7 space-y-5" x-data="{ stageEnabled: {{ $competition->has_stage_timer ? 'true' : 'false' }} }">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-white/[0.07]">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0" style="background: rgba(245,158,11,0.12); border: 1px solid rgba(245,158,11,0.22);">
                            <i data-lucide="tv" class="w-4 h-4 text-amber-400"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-black text-white">Layar Panggung & Stage Timer (TV Display)</h4>
                            <p class="text-xs text-slate-500">Tampilan urutan tampil 3-panel (Sedang Tampil, Berikutnya, Selesai) untuk panggung perlombaan</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        @if($competition->has_stage_timer)
                            <a href="{{ route('stage.viewer', $competition->slug ?: $competition->code) }}" target="_blank" class="px-3 py-1.5 rounded-xl bg-amber-500/15 hover:bg-amber-500/25 text-amber-300 border border-amber-500/30 text-xs font-bold transition flex items-center gap-1.5">
                                <i data-lucide="tv" class="w-3.5 h-3.5"></i>
                                <span>Layar TV</span>
                            </a>
                            <a href="{{ route('pic.stage.control', $competition->id) }}" class="px-3 py-1.5 rounded-xl bg-[#4E6EFF]/20 hover:bg-[#4E6EFF]/30 text-[#84D0FF] border border-[#4E6EFF]/40 text-xs font-bold transition flex items-center gap-1.5">
                                <i data-lucide="sliders" class="w-3.5 h-3.5"></i>
                                <span>Panel Kontrol</span>
                            </a>
                        @endif
                    </div>
                </div>

                <div class="space-y-4">
                    <!-- Toggle Aktifkan -->
                    <label class="flex items-start gap-3 p-3.5 rounded-xl cursor-pointer select-none transition" 
                           :style="stageEnabled ? 'background: rgba(245,158,11,0.12); border: 1px solid rgba(245,158,11,0.3);' : 'background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.08);'">
                        <input name="has_stage_timer" type="checkbox" value="1" x-model="stageEnabled" class="mt-0.5 w-4 h-4 rounded text-amber-500 bg-slate-900 border-slate-700 focus:ring-0 cursor-pointer">
                        <div class="flex-1">
                            <span class="text-xs font-bold text-white block">Aktifkan Fitur Stage Timer & Layar Panggung untuk Cabang Ini</span>
                            <span class="text-[11px] text-slate-400 block mt-0.5">Sangat direkomendasikan untuk lomba panggung (Pop Singer, MTQ, Tari, Teater, Pidato/Debat, dll).</span>
                        </div>
                    </label>

                    <!-- Stage Timer Configuration Fields -->
                    <div x-show="stageEnabled" x-collapse class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 pt-2">
                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Durasi Tampil (Menit)</label>
                            <input name="stage_duration_minutes" type="number" min="1" max="180" value="{{ old('stage_duration_minutes', $competition->stage_duration_minutes ?: 7) }}" class="input-admin block w-full px-3 py-2.5 rounded-xl text-xs font-mono font-bold text-emerald-400">
                            <p class="text-[10px] text-slate-500 mt-1">Misal: 7 menit</p>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Peringatan Sisa Waktu (Menit)</label>
                            <input name="stage_warning_minutes" type="number" min="1" max="60" value="{{ old('stage_warning_minutes', $competition->stage_warning_minutes ?: 2) }}" class="input-admin block w-full px-3 py-2.5 rounded-xl text-xs font-mono font-bold text-amber-400">
                            <p class="text-[10px] text-slate-500 mt-1">Layar jadi kuning & bunyi bel</p>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Toleransi Overtime (Menit)</label>
                            <input name="stage_overtime_minutes" type="number" min="0" max="15" value="{{ old('stage_overtime_minutes', $competition->stage_overtime_minutes ?: 1) }}" class="input-admin block w-full px-3 py-2.5 rounded-xl text-xs font-mono font-bold text-rose-400">
                            <p class="text-[10px] text-slate-500 mt-1">Layar kedip merah</p>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Pilihan Suara Bel</label>
                            <select name="stage_bell_sound" class="input-admin block w-full px-3 py-2.5 rounded-xl text-xs font-semibold">
                                <option value="bell" {{ ($competition->stage_bell_sound ?: 'bell') === 'bell' ? 'selected' : '' }}>🔔 Bel Standar (Chime Jernih)</option>
                                <option value="double" {{ $competition->stage_bell_sound === 'double' ? 'selected' : '' }}>🔔🔔 Bel Ganda (2x)</option>
                                <option value="gong" {{ $competition->stage_bell_sound === 'gong' ? 'selected' : '' }}>🔊 Gong Resonan</option>
                                <option value="buzzer" {{ $competition->stage_bell_sound === 'buzzer' ? 'selected' : '' }}>⚡ Buzzer Digital</option>
                                <option value="none" {{ $competition->stage_bell_sound === 'none' ? 'selected' : '' }}>❌ Tanpa Suara</option>
                            </select>
                            <p class="text-[10px] text-slate-500 mt-1">Dibunyikan via Web Audio API</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KRITERIA PENILAIAN CARD -->
            <div class="card-admin rounded-2xl p-5 sm:p-7 space-y-4" x-data="criteriaApp({{ json_encode($competition->criteria->toArray()) }})">
                <div class="flex items-center justify-between pb-4 border-b border-white/[0.07]">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0" style="background: rgba(56,189,248,0.12); border: 1px solid rgba(56,189,248,0.22);">
                            <i data-lucide="bar-chart-2" class="w-4 h-4" style="color: #84D0FF;"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-black text-white">Kriteria Penilaian</h4>
                            <p class="text-xs text-slate-500">Bobot persentase penilaian cabang lomba</p>
                        </div>
                    </div>
                    <button type="button" @click="addCriterion()" class="btn-cancel-action flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition cursor-pointer" style="color: #34d399; border-color: rgba(16,185,129,0.3); background: rgba(16,185,129,0.12);">
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                        <span>Tambah Kriteria</span>
                    </button>
                </div>

                <div x-show="criteria.length === 0">
                    <p class="text-xs text-slate-600 text-center py-6 rounded-xl" style="border: 2px dashed rgba(255,255,255,0.07);">Belum ada kriteria penilaian. Klik tombol di atas untuk menambah.</p>
                </div>

                <template x-for="(criterion, index) in criteria" :key="index">
                    <div class="grid grid-cols-12 gap-2 items-end p-3 rounded-xl" style="background: rgba(12,17,29,0.6); border: 1px solid rgba(255,255,255,0.07);">
                        <div class="col-span-4">
                            <label class="block text-[10px] font-bold text-slate-500 mb-1">Nama Kriteria</label>
                            <input :name="'criteria[' + index + '][name]'" type="text" x-model="criterion.name" placeholder="Contoh: Kreativitas" class="input-admin block w-full px-2.5 py-1.5 rounded-lg text-xs font-semibold">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-[10px] font-bold text-slate-500 mb-1">Bobot (%)</label>
                            <input :name="'criteria[' + index + '][weight_percentage]'" type="number" min="0" max="100" x-model="criterion.weight_percentage" class="input-admin block w-full px-2.5 py-1.5 rounded-lg text-xs font-bold">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-[10px] font-bold text-slate-500 mb-1">Min Skor</label>
                            <input :name="'criteria[' + index + '][min_score]'" type="number" min="0" x-model="criterion.min_score" class="input-admin block w-full px-2.5 py-1.5 rounded-lg text-xs font-bold">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-[10px] font-bold text-slate-500 mb-1">Maks Skor</label>
                            <input :name="'criteria[' + index + '][max_score]'" type="number" min="0" x-model="criterion.max_score" class="input-admin block w-full px-2.5 py-1.5 rounded-lg text-xs font-bold">
                        </div>
                        <div class="col-span-2 flex items-end justify-end">
                            <button type="button" @click="criteria.splice(index, 1)" class="px-2.5 py-1.5 rounded-lg text-xs font-bold transition hover:bg-rose-500/20 cursor-pointer" style="background: rgba(239,68,68,0.08); border: 1px solid rgba(239,68,68,0.2); color: #f87171;">Hapus</button>
                        </div>
                    </div>
                </template>

                <div x-show="criteria.length > 0" class="flex items-center justify-between text-xs px-1 font-bold">
                    <span class="text-slate-500">Total Bobot:</span>
                    <span :class="totalWeight === 100 ? 'text-emerald-400' : 'text-amber-400'" 
                          :style="totalWeight === 100 ? 'background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.2); padding: 2px 10px; border-radius: 999px;' : 'background: rgba(245,158,11,0.1); border: 1px solid rgba(245,158,11,0.2); padding: 2px 10px; border-radius: 999px;'"
                          x-text="totalWeight + '% ' + (totalWeight === 100 ? '✓ Pas 100%' : '(Disarankan 100%)')"></span>
                </div>
            </div>

            <!-- FOOTER CARD -->
            <div class="card-admin rounded-2xl p-5 flex flex-col sm:flex-row items-center justify-between gap-4">
                <button type="button" onclick="if(confirm('Yakin hapus cabang lomba {{ addslashes($competition->name) }}?')) { document.getElementById('deleteForm').submit(); }" class="w-full sm:w-auto px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 hover:bg-rose-500/20 cursor-pointer" style="background: rgba(239,68,68,0.08); border: 1px solid rgba(239,68,68,0.2); color: #f87171;">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                    <span>Hapus Cabang Lomba</span>
                </button>
                <div class="w-full sm:w-auto flex items-center justify-end gap-3">
                    <a href="{{ route('admin.competitions') }}" class="btn-cancel-action px-5 py-2.5 rounded-xl text-xs font-bold transition cursor-pointer">Batal</a>
                    <button type="submit" class="btn-save-glow px-6 py-2.5 rounded-xl text-white font-black text-xs transition flex items-center gap-2 cursor-pointer shadow-lg shadow-emerald-500/25">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        <span>Simpan Perubahan</span>
                    </button>
                </div>
            </div>

        </form>

        <form id="deleteForm" action="{{ route('admin.competitions.delete', $competition->id) }}" method="POST" class="hidden">@csrf</form>
    </div>

    <script>
        function editPageApp() {
            return {
                isSubmitting: false,
                stageEnabled: {{ $competition->has_stage_timer ? 'true' : 'false' }},
                init() {
                    this.$nextTick(() => {
                        if (window.lucide) window.lucide.createIcons();
                    });
                },
                submitForm() {
                    const form = document.getElementById('editCompetitionForm');
                    if (form.reportValidity && !form.reportValidity()) {
                        return;
                    }
                    this.isSubmitting = true;
                    form.submit();
                }
            };
        }
        function criteriaApp(initialCriteria) {
            return {
                criteria: initialCriteria || [],
                get totalWeight() { return this.criteria.reduce((s, c) => s + (parseInt(c.weight_percentage) || 0), 0); },
                addCriterion() {
                    this.criteria.push({ name: '', weight_percentage: 0, min_score: 0, max_score: 100, description: '' });
                    this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
                }
            };
        }
        document.addEventListener('DOMContentLoaded', function() { if (window.lucide) lucide.createIcons(); });
    </script>
</body>
</html>
