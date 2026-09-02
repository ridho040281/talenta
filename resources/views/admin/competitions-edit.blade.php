<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit {{ $competition->name }} — TALENTA Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }

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
    </style>
</head>
<body class="font-sans antialiased min-h-screen" x-data="editPageApp()" x-init="init()">

    <!-- TOP STICKY HEADER — sama dengan competitions.blade.php header style -->
    <div style="position: sticky; top: 0; z-index: 100; background: rgba(9,13,23,0.97); border-bottom: 1px solid rgba(255,255,255,0.09); padding: 14px 32px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 4px 32px rgba(0,0,0,0.5);">
        <div class="flex items-center gap-3.5 min-w-0">
            <a href="{{ route('admin.competitions') }}" class="flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white/[0.07] hover:bg-white/[0.13] text-slate-200 hover:text-white text-xs font-bold transition border border-white/[0.1] shrink-0">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Kembali ke Daftar</span>
            </a>
            <div class="min-w-0">
                <h3 class="text-sm sm:text-base font-black text-white truncate">Edit Cabang Perlombaan</h3>
                <p class="text-xs text-slate-400 hidden sm:block truncate">Perbarui informasi <strong class="text-emerald-400">{{ $competition->name }}</strong></p>
            </div>
        </div>
        <div class="flex items-center gap-3 shrink-0">
            <a href="{{ route('admin.competitions') }}" class="px-4 py-2 rounded-xl bg-white/[0.06] hover:bg-white/[0.1] text-slate-300 hover:text-white text-xs font-bold transition border border-white/[0.08]">
                Batal
            </a>
            <button type="button" onclick="document.getElementById('editCompetitionForm').submit()" class="px-5 py-2 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-black text-xs shadow-lg shadow-emerald-500/25 transition flex items-center gap-1.5">
                <i data-lucide="check" class="w-4 h-4"></i>
                <span>Simpan Perubahan</span>
            </button>
        </div>
    </div>

    <!-- FORM BODY -->
    <div class="max-w-5xl w-full mx-auto p-4 sm:p-6 lg:p-8 space-y-5">
        <form id="editCompetitionForm" action="{{ route('admin.competitions.update', $competition->id) }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf

            @if ($errors->any())
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

                    <!-- Row 3: Biaya, Kuota, PIC, Status -->
                    @php $isMultiTier = in_array($competition->code, ['BLT', 'MTQ', 'POP', 'TMJ']); @endphp
                    @if(!$isMultiTier)
                    <div class="grid grid-cols-2 sm:grid-cols-12 gap-3">
                        <div class="sm:col-span-3">
                            <label class="block text-[10px] font-black uppercase tracking-widest text-slate-500 mb-1.5">Biaya (Rp)</label>
                            <input name="registration_fee" type="number" step="1000" min="0" value="{{ old('registration_fee', $competition->registration_fee) }}" class="input-admin block w-full px-3 py-2.5 rounded-xl text-xs font-mono font-bold">
                            <p class="text-[10px] text-slate-600 mt-0.5">Isi 0 untuk Tak Terbatas</p>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-[10px] font-black uppercase tracking-widest text-slate-500 mb-1.5">Kuota Total</label>
                            <input name="quota" type="number" min="0" value="{{ old('quota', $competition->quota) }}" class="input-admin block w-full px-3 py-2.5 rounded-xl text-xs font-bold">
                        </div>
                        <div class="col-span-2 sm:col-span-4">
                            <label class="block text-[10px] font-black uppercase tracking-widest text-slate-500 mb-1.5">Koordinator PIC</label>
                            <select name="pic_id" class="input-admin block w-full px-3 py-2.5 rounded-xl text-xs font-semibold">
                                <option value="">-- Belum Ditugaskan --</option>
                                @foreach($pics as $pic)
                                    <option value="{{ $pic->id }}" {{ $competition->pic_id == $pic->id ? 'selected' : '' }}>{{ $pic->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-[10px] font-black uppercase tracking-widest text-slate-500 mb-1.5">Status</label>
                            <select name="status" required class="input-admin block w-full px-3 py-2.5 rounded-xl text-xs font-bold">
                                <option value="buka" {{ $competition->status == 'buka' ? 'selected' : '' }}>Buka</option>
                                <option value="tutup" {{ $competition->status == 'tutup' ? 'selected' : '' }}>Tutup</option>
                                <option value="selesai" {{ $competition->status == 'selesai' ? 'selected' : '' }}>Selesai</option>
                            </select>
                        </div>
                    </div>
                    @endif

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
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-slate-500 mb-1.5">Aturan & Petunjuk Teknis Singkat</label>
                        <textarea name="rules" rows="4" class="input-admin block w-full px-3 py-2.5 rounded-xl text-xs font-medium resize-none">{{ old('rules', $competition->rules) }}</textarea>
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
                    <button type="button" @click="addCriterion()" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition" style="background: rgba(16,185,129,0.12); border: 1px solid rgba(16,185,129,0.22); color: #34d399;">
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                        + Tambah Kriteria
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
                            <button type="button" @click="criteria.splice(index, 1)" class="px-2.5 py-1.5 rounded-lg text-xs font-bold transition" style="background: rgba(239,68,68,0.08); border: 1px solid rgba(239,68,68,0.2); color: #f87171;">Hapus</button>
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
                <button type="button" onclick="if(confirm('Yakin hapus cabang lomba {{ addslashes($competition->name) }}?')) { document.getElementById('deleteForm').submit(); }" class="w-full sm:w-auto px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5" style="background: rgba(239,68,68,0.08); border: 1px solid rgba(239,68,68,0.2); color: #f87171;">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                    <span>Hapus Cabang Lomba</span>
                </button>
                <div class="w-full sm:w-auto flex items-center justify-end gap-3">
                    <a href="{{ route('admin.competitions') }}" class="px-5 py-2.5 rounded-xl text-slate-400 hover:text-white text-xs font-bold transition" style="border: 1px solid rgba(255,255,255,0.09);">Batal</a>
                    <button type="submit" class="px-6 py-2.5 rounded-xl text-white font-black text-xs shadow-lg transition flex items-center gap-1.5" style="background: linear-gradient(135deg, #059669 0%, #0d9488 100%); box-shadow: 0 4px 16px rgba(16,185,129,0.25);">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Simpan Perubahan</span>
                    </button>
                </div>
            </div>

        </form>

        <form id="deleteForm" action="{{ route('admin.competitions.delete', $competition->id) }}" method="POST" class="hidden">@csrf</form>
    </div>

    <script>
        function editPageApp() { return { init() { this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); }); } }; }
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
