<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Template WhatsApp — TALENTA Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }

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

        .card-admin {
            background: rgba(22, 31, 48, 0.88);
            border: 1px solid rgba(255, 255, 255, 0.09);
            backdrop-filter: blur(12px);
        }

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
    </style>
</head>
<body class="font-sans antialiased min-h-screen">

    <!-- TOP STICKY HEADER -->
    <div style="position: sticky; top: 0; z-index: 100; background: rgba(9,13,23,0.97); border-bottom: 1px solid rgba(255,255,255,0.09); padding: 14px 32px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 4px 32px rgba(0,0,0,0.5);">
        <div class="flex items-center gap-3.5 min-w-0">
            <a href="{{ route('admin.settings.whatsapp.blast', ['tab' => 'templates']) }}" class="flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white/[0.07] hover:bg-white/[0.13] text-slate-200 hover:text-white text-xs font-bold transition border border-white/[0.1] shrink-0">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Kembali ke WhatsApp Blast</span>
            </a>
            <div class="min-w-0">
                <h3 class="text-sm sm:text-base font-black text-white truncate">Edit Template Pesan WhatsApp</h3>
                <p class="text-xs text-slate-400 hidden sm:block truncate">Perbarui format pesan <strong class="text-emerald-400">{{ $template->name }}</strong></p>
            </div>
        </div>
        <div class="flex items-center gap-3 shrink-0">
            <a href="{{ route('admin.settings.whatsapp.blast', ['tab' => 'templates']) }}" class="px-4 py-2 rounded-xl bg-white/[0.06] hover:bg-white/[0.1] text-slate-300 hover:text-white text-xs font-bold transition border border-white/[0.08]">
                Batal
            </a>
            <button type="button" onclick="document.getElementById('editTemplateForm').submit()" class="px-5 py-2 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-black text-xs shadow-lg shadow-emerald-500/25 transition flex items-center gap-1.5 cursor-pointer">
                <i data-lucide="check" class="w-4 h-4"></i>
                <span>Simpan Perubahan</span>
            </button>
        </div>
    </div>

    <!-- FORM BODY -->
    <div class="max-w-4xl w-full mx-auto p-4 sm:p-6 lg:p-8 space-y-5">
        <form id="editTemplateForm" action="{{ route('admin.settings.whatsapp.blast.templates.update', $template->id) }}" method="POST" class="space-y-5">
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

            <!-- CARD UTAMA -->
            <div class="card-admin rounded-2xl p-5 sm:p-7 space-y-5">
                
                <!-- Section Header -->
                <div class="flex items-center justify-between pb-4 border-b border-white/[0.07]">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl flex items-center justify-center shrink-0" style="background: rgba(122,90,248,0.15); border: 1px solid rgba(122,90,248,0.25);">
                            <i data-lucide="message-square" class="w-5 h-5 text-[#A594FD]"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-black text-white">{{ $template->name }}</h4>
                            <p class="text-xs text-slate-400 mt-0.5">{{ $template->description ?: 'Template pesan otomatis' }}</p>
                        </div>
                    </div>
                    <div>
                        @if($template->is_system)
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                                Sistem Otomatis
                            </span>
                        @else
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                Template Kustom
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Input: Nama Judul -->
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1.5">Nama Judul Template *</label>
                    <input type="text" name="name" required value="{{ old('name', $template->name) }}" class="input-admin block w-full px-3.5 py-2.5 rounded-xl text-xs font-semibold">
                </div>

                <!-- Input: Deskripsi -->
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1.5">Deskripsi Singkat</label>
                    <input type="text" name="description" value="{{ old('description', $template->description) }}" placeholder="Deskripsi kapan pesan ini dikirimkan..." class="input-admin block w-full px-3.5 py-2.5 rounded-xl text-xs">
                </div>

                <!-- Tag Variabel Cepat -->
                <div class="p-3.5 rounded-xl space-y-2" style="background: rgba(12,17,29,0.6); border: 1px solid rgba(255,255,255,0.07);">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Variabel Dinamis (Klik untuk Sisipkan):</span>
                    </div>
                    <div class="flex flex-wrap gap-1.5">
                        <button type="button" onclick="insertTag('{nama_peserta}')" class="px-2 py-1 rounded-lg text-[11px] font-mono font-bold cursor-pointer hover:scale-105 transition" style="background: rgba(78,110,255,0.15); color: #84D0FF; border: 1px solid rgba(78,110,255,0.25);">
                            {nama_peserta}
                        </button>
                        <button type="button" onclick="insertTag('{nama_sekolah}')" class="px-2 py-1 rounded-lg text-[11px] font-mono font-bold cursor-pointer hover:scale-105 transition" style="background: rgba(78,110,255,0.15); color: #84D0FF; border: 1px solid rgba(78,110,255,0.25);">
                            {nama_sekolah}
                        </button>
                        <button type="button" onclick="insertTag('{cabang_lomba}')" class="px-2 py-1 rounded-lg text-[11px] font-mono font-bold cursor-pointer hover:scale-105 transition" style="background: rgba(52,211,153,0.15); color: #34d399; border: 1px solid rgba(52,211,153,0.25);">
                            {cabang_lomba}
                        </button>
                        <button type="button" onclick="insertTag('{no_peserta}')" class="px-2 py-1 rounded-lg text-[11px] font-mono font-bold cursor-pointer hover:scale-105 transition" style="background: rgba(245,158,11,0.15); color: #fbbf24; border: 1px solid rgba(245,158,11,0.25);">
                            {no_peserta}
                        </button>
                        <button type="button" onclick="insertTag('{kode_pendaftaran}')" class="px-2 py-1 rounded-lg text-[11px] font-mono font-bold cursor-pointer hover:scale-105 transition" style="background: rgba(168,85,247,0.15); color: #c084fc; border: 1px solid rgba(168,85,247,0.25);">
                            {kode_pendaftaran}
                        </button>
                        <button type="button" onclick="insertTag('{nisn}')" class="px-2 py-1 rounded-lg text-[11px] font-mono font-bold cursor-pointer hover:scale-105 transition" style="background: rgba(255,255,255,0.06); color: #e2e8f0; border: 1px solid rgba(255,255,255,0.1);">
                            {nisn}
                        </button>
                        <button type="button" onclick="insertTag('{no_wa}')" class="px-2 py-1 rounded-lg text-[11px] font-mono font-bold cursor-pointer hover:scale-105 transition" style="background: rgba(255,255,255,0.06); color: #e2e8f0; border: 1px solid rgba(255,255,255,0.1);">
                            {no_wa}
                        </button>
                        <button type="button" onclick="insertTag('{link_login}')" class="px-2 py-1 rounded-lg text-[11px] font-mono font-bold cursor-pointer hover:scale-105 transition" style="background: rgba(236,72,153,0.15); color: #f472b6; border: 1px solid rgba(236,72,153,0.25);">
                            {link_login}
                        </button>
                        <button type="button" onclick="insertTag('{link_scoreboard}')" class="px-2 py-1 rounded-lg text-[11px] font-mono font-bold cursor-pointer hover:scale-105 transition" style="background: rgba(236,72,153,0.15); color: #f472b6; border: 1px solid rgba(236,72,153,0.25);">
                            {link_scoreboard}
                        </button>
                        <button type="button" onclick="insertTag('{nominal_biaya}')" class="px-2 py-1 rounded-lg text-[11px] font-mono font-bold cursor-pointer hover:scale-105 transition" style="background: rgba(20,184,166,0.15); color: #2dd4bf; border: 1px solid rgba(20,184,166,0.25);">
                            {nominal_biaya}
                        </button>
                    </div>
                </div>

                <!-- Input: Isi Pesan Template -->
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1.5">Isi Pesan WhatsApp *</label>
                    <textarea id="messageBox" name="message" rows="12" required class="input-admin block w-full px-4 py-3 rounded-xl text-xs font-mono leading-relaxed" style="tab-size: 4;">{{ old('message', $template->message) }}</textarea>
                </div>

                <!-- Checkbox: Aktifkan Template -->
                <div class="pt-2">
                    <label class="flex items-center gap-3 p-3.5 rounded-xl cursor-pointer" style="background: rgba(16,185,129,0.07); border: 1px solid rgba(16,185,129,0.2);">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $template->is_active) ? 'checked' : '' }} class="w-4 h-4 rounded accent-emerald-500">
                        <div>
                            <span class="text-xs font-bold text-emerald-400">Aktifkan Template / Auto-Trigger Otomatis</span>
                            <p class="text-[11px] text-slate-400">Jika aktif, pesan otomatis akan terkirim saat event pendaftaran terjadi.</p>
                        </div>
                    </label>
                </div>

            </div>

            <!-- FOOTER -->
            <div class="card-admin rounded-2xl p-5 flex items-center justify-between">
                <a href="{{ route('admin.settings.whatsapp.blast', ['tab' => 'templates']) }}" class="px-5 py-2.5 rounded-xl text-slate-400 hover:text-white text-xs font-bold transition" style="border: 1px solid rgba(255,255,255,0.09);">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl text-white font-black text-xs shadow-lg transition flex items-center gap-1.5 cursor-pointer" style="background: linear-gradient(135deg, #059669 0%, #0d9488 100%); box-shadow: 0 4px 16px rgba(16,185,129,0.25);">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>Simpan Perubahan</span>
                </button>
            </div>

        </form>
    </div>

    <script>
        function insertTag(tag) {
            const textarea = document.getElementById('messageBox');
            if (!textarea) return;
            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            textarea.value = textarea.value.substring(0, start) + tag + textarea.value.substring(end);
            textarea.focus();
            textarea.selectionStart = textarea.selectionEnd = start + tag.length;
        }

        document.addEventListener('DOMContentLoaded', function() {
            if (window.lucide) lucide.createIcons();
        });
    </script>
</body>
</html>
