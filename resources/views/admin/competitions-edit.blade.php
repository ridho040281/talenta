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
        body { background: #070A13; min-height: 100vh; }
    </style>
</head>
<body class="bg-[#070A13] min-h-screen" x-data="editPageApp()" x-init="init()">

    <!-- TOP STICKY HEADER BAR -->
    <div style="position: sticky; top: 0; z-index: 100; background: rgba(11,16,29,0.97); border-bottom: 1px solid rgba(255,255,255,0.1); padding: 14px 32px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 4px 32px rgba(0,0,0,0.6);">
        <div class="flex items-center gap-3.5 min-w-0">
            <a href="{{ route('admin.competitions') }}" class="flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white/[0.08] hover:bg-white/[0.15] text-slate-200 hover:text-white text-xs font-bold transition border border-white/[0.1] shrink-0">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Kembali ke Daftar</span>
            </a>
            <div class="min-w-0">
                <h3 class="text-sm sm:text-base font-black text-white truncate">Edit Cabang Perlombaan</h3>
                <p class="text-xs text-slate-400 hidden sm:block truncate">Perbarui informasi <strong class="text-white">{{ $competition->name }}</strong></p>
            </div>
        </div>
        <div class="flex items-center gap-3 shrink-0">
            <a href="{{ route('admin.competitions') }}" class="px-4 py-2 rounded-xl bg-white/[0.06] hover:bg-white/[0.1] text-slate-300 text-xs font-bold transition">
                Batal
            </a>
            <button type="button" onclick="document.getElementById('editCompetitionForm').submit()" class="px-5 py-2 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-black text-xs shadow-lg shadow-emerald-500/20 transition flex items-center gap-1.5">
                <i data-lucide="check" class="w-4 h-4"></i>
                <span>Simpan Perubahan</span>
            </button>
        </div>
    </div>

    <!-- FORM BODY -->
    <div class="max-w-5xl w-full mx-auto p-4 sm:p-6 lg:p-8 space-y-6">
        <form id="editCompetitionForm" action="{{ route('admin.competitions.update', $competition->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            @if ($errors->any())
                <div class="bg-rose-50 border border-rose-200 rounded-2xl p-4">
                    <ul class="text-xs text-rose-700 space-y-1 list-disc pl-4">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- FORM CARD -->
            <div class="bg-white rounded-3xl p-5 sm:p-7 shadow-2xl space-y-5 text-slate-900 border border-slate-200">
                <div class="space-y-3.5">

                    <!-- Row 1 -->
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                        <div class="md:col-span-3">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Jenis Lomba</label>
                            <select name="category_id" required class="block w-full px-3 py-2 rounded-xl bg-white border border-slate-200 text-xs font-semibold text-slate-900 outline-none focus:border-emerald-500">
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ $competition->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-4">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Nama Lomba</label>
                            <input name="name" type="text" required value="{{ old('name', $competition->name) }}" class="block w-full px-3 py-2 rounded-xl bg-white border border-slate-200 text-xs font-semibold text-slate-900 outline-none focus:border-emerald-500">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Kode Singkat</label>
                            <input name="code" type="text" required value="{{ old('code', $competition->code) }}" class="block w-full px-3 py-2 rounded-xl bg-white border border-slate-200 text-xs font-mono font-bold text-slate-900 outline-none uppercase focus:border-emerald-500">
                        </div>
                        <div class="md:col-span-3">
                            <label class="block text-xs font-bold uppercase tracking-wider text-amber-700 mb-1">Urutan Tampilan</label>
                            <input name="order" type="number" min="1" value="{{ old('order', $competition->order) }}" class="block w-full px-3 py-2 rounded-xl bg-amber-50/60 border border-amber-300/80 text-xs font-mono font-black text-amber-900 outline-none focus:border-amber-500">
                        </div>
                    </div>

                    <!-- Row 2 -->
                    <div class="grid grid-cols-2 sm:grid-cols-12 gap-3">
                        <div class="sm:col-span-3">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Kategori Lomba</label>
                            <select name="type" required class="block w-full px-2.5 py-2 rounded-xl bg-white border border-slate-200 text-xs font-bold text-slate-900 outline-none focus:border-emerald-500">
                                <option value="individu" {{ $competition->type == 'individu' ? 'selected' : '' }}>Individu</option>
                                <option value="tim" {{ $competition->type == 'tim' ? 'selected' : '' }}>Tim</option>
                                <option value="kelompok" {{ $competition->type == 'kelompok' ? 'selected' : '' }}>Kelompok</option>
                                <option value="regu" {{ $competition->type == 'regu' ? 'selected' : '' }}>Regu</option>
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Min Anggota</label>
                            <input name="min_members" type="number" min="1" required value="{{ old('min_members', $competition->min_members) }}" class="block w-full px-2.5 py-2 rounded-xl bg-white border border-slate-200 text-xs font-bold text-slate-900 outline-none focus:border-emerald-500">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Maks Anggota</label>
                            <input name="max_members" type="number" min="1" required value="{{ old('max_members', $competition->max_members) }}" class="block w-full px-2.5 py-2 rounded-xl bg-white border border-slate-200 text-xs font-bold text-slate-900 outline-none focus:border-emerald-500">
                        </div>
                        <div class="col-span-2 sm:col-span-3">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Lokasi / Venue</label>
                            <input name="venue" type="text" value="{{ old('venue', $competition->venue) }}" placeholder="Contoh: GOR MTsN 1 Blitar" class="block w-full px-3 py-2 rounded-xl bg-white border border-slate-200 text-xs font-medium text-slate-900 outline-none">
                        </div>
                        <div class="col-span-2 sm:col-span-2">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Waktu / Jadwal</label>
                            <input name="schedule_time" type="text" value="{{ old('schedule_time', $competition->schedule_time) }}" placeholder="08.00 WIB" class="block w-full px-3 py-2 rounded-xl bg-white border border-slate-200 text-xs font-medium text-slate-900 outline-none">
                        </div>
                    </div>

                    <!-- Row 3: Biaya, Kuota, PIC, Status -->
                    @php $isMultiTier = in_array($competition->code, ['BLT', 'MTQ', 'POP', 'TMJ']); @endphp
                    @if(!$isMultiTier)
                    <div class="grid grid-cols-2 sm:grid-cols-12 gap-3">
                        <div class="sm:col-span-3">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Biaya (Rp)</label>
                            <input name="registration_fee" type="number" step="1000" min="0" value="{{ old('registration_fee', $competition->registration_fee) }}" class="block w-full px-3 py-2 rounded-xl bg-white border border-slate-200 text-xs font-mono font-bold text-slate-900 outline-none focus:border-emerald-500">
                            <p class="text-[10px] text-slate-400 mt-0.5">Isi 0 untuk Tak Terbatas</p>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Kuota Total</label>
                            <input name="quota" type="number" min="0" value="{{ old('quota', $competition->quota) }}" class="block w-full px-3 py-2 rounded-xl bg-white border border-slate-200 text-xs font-bold text-slate-900 outline-none focus:border-emerald-500">
                        </div>
                        <div class="col-span-2 sm:col-span-4">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Koordinator PIC</label>
                            <select name="pic_id" class="block w-full px-3 py-2 rounded-xl bg-white border border-slate-200 text-xs font-semibold text-slate-900 outline-none focus:border-emerald-500">
                                <option value="">-- Belum Ditugaskan --</option>
                                @foreach($pics as $pic)
                                    <option value="{{ $pic->id }}" {{ $competition->pic_id == $pic->id ? 'selected' : '' }}>{{ $pic->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Status</label>
                            <select name="status" required class="block w-full px-3 py-2 rounded-xl bg-white border border-slate-200 text-xs font-bold text-slate-900 outline-none focus:border-emerald-500">
                                <option value="buka" {{ $competition->status == 'buka' ? 'selected' : '' }}>Buka</option>
                                <option value="tutup" {{ $competition->status == 'tutup' ? 'selected' : '' }}>Tutup</option>
                                <option value="selesai" {{ $competition->status == 'selesai' ? 'selected' : '' }}>Selesai</option>
                            </select>
                        </div>
                    </div>
                    @endif

                    @if($isMultiTier)
                    <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-xs text-amber-800">
                        <p class="font-bold">Cabang lomba multi-tier ({{ $competition->code }})</p>
                        <p class="mt-1 text-amber-700">Pengaturan biaya, kuota, PIC, dan status per kategori dikelola melalui menu Master Cabang Lomba di halaman daftar.</p>
                    </div>
                    @endif

                    <!-- Aturan & Petunjuk Teknis -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Aturan & Petunjuk Teknis Singkat</label>
                        <textarea name="rules" rows="4" class="block w-full px-3 py-2 rounded-xl bg-white border border-slate-200 text-xs font-medium text-slate-900 outline-none focus:border-emerald-500 resize-none">{{ old('rules', $competition->rules) }}</textarea>
                    </div>

                    <!-- Juknis PDF -->
                    <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200 space-y-2">
                        <label class="block text-xs font-bold text-slate-700">Embed Link Juknis PDF / Dokumen Resmi</label>
                        <input name="guidelines_file" type="text" value="{{ old('guidelines_file', $competition->guidelines_file) }}" placeholder="https://drive.google.com/..." class="block w-full px-3 py-2 rounded-xl bg-white border border-slate-200 text-xs font-mono text-slate-900 outline-none focus:border-blue-500">
                        <p class="text-[10px] text-slate-400">atau upload file PDF baru:</p>
                        <input name="guidelines_pdf" type="file" accept=".pdf" class="block w-full text-xs text-slate-500 file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    </div>

                    <!-- Checkboxes -->
                    <div class="flex flex-col sm:flex-row gap-3">
                        <div class="flex items-center gap-3 p-3 rounded-2xl bg-violet-50/60 border border-violet-200/80 flex-1">
                            <input name="is_live_score" type="checkbox" id="isLiveScore" value="1" {{ $competition->is_live_score ? 'checked' : '' }} class="w-4 h-4 rounded text-violet-600">
                            <label for="isLiveScore" class="text-xs font-bold text-violet-900">Tampilkan di Live Score Publik</label>
                        </div>
                        <div class="flex items-center gap-3 p-3 rounded-2xl bg-slate-50/60 border border-slate-200/80 flex-1">
                            <input name="show_criteria" type="checkbox" id="showCriteria" value="1" {{ $competition->show_criteria ? 'checked' : '' }} class="w-4 h-4 rounded text-slate-600">
                            <label for="showCriteria" class="text-xs font-bold text-slate-700">Tampilkan Kriteria Penilaian ke Publik</label>
                        </div>
                    </div>

                </div>
            </div>

            <!-- KRITERIA PENILAIAN -->
            <div class="bg-white rounded-3xl p-5 sm:p-7 shadow-2xl space-y-4 text-slate-900 border border-slate-200" x-data="criteriaApp({{ json_encode($competition->criteria->toArray()) }})">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-sm font-black text-slate-900">Kriteria Penilaian</h4>
                        <p class="text-xs text-slate-500 mt-0.5">Tambahkan kriteria dan bobot persentase</p>
                    </div>
                    <button type="button" @click="addCriterion()" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition">
                        + Tambah Kriteria
                    </button>
                </div>

                <div x-show="criteria.length === 0">
                    <p class="text-xs text-slate-400 text-center py-6 border-2 border-dashed border-slate-200 rounded-xl">Belum ada kriteria penilaian.</p>
                </div>

                <template x-for="(criterion, index) in criteria" :key="index">
                    <div class="grid grid-cols-12 gap-2 items-end p-3 rounded-xl bg-slate-50 border border-slate-200">
                        <div class="col-span-4">
                            <label class="block text-[10px] font-bold text-slate-600 mb-1">Nama Kriteria</label>
                            <input :name="'criteria[' + index + '][name]'" type="text" x-model="criterion.name" placeholder="Contoh: Kreativitas" class="block w-full px-2.5 py-1.5 rounded-lg bg-white border border-slate-200 text-xs font-semibold text-slate-900 outline-none focus:border-emerald-500">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-[10px] font-bold text-slate-600 mb-1">Bobot (%)</label>
                            <input :name="'criteria[' + index + '][weight_percentage]'" type="number" min="0" max="100" x-model="criterion.weight_percentage" class="block w-full px-2.5 py-1.5 rounded-lg bg-white border border-slate-200 text-xs font-bold text-slate-900 outline-none">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-[10px] font-bold text-slate-600 mb-1">Min Skor</label>
                            <input :name="'criteria[' + index + '][min_score]'" type="number" min="0" x-model="criterion.min_score" class="block w-full px-2.5 py-1.5 rounded-lg bg-white border border-slate-200 text-xs font-bold text-slate-900 outline-none">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-[10px] font-bold text-slate-600 mb-1">Maks Skor</label>
                            <input :name="'criteria[' + index + '][max_score]'" type="number" min="0" x-model="criterion.max_score" class="block w-full px-2.5 py-1.5 rounded-lg bg-white border border-slate-200 text-xs font-bold text-slate-900 outline-none">
                        </div>
                        <div class="col-span-2 flex items-end justify-end">
                            <button type="button" @click="criteria.splice(index, 1)" class="px-2.5 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 text-xs font-bold transition">Hapus</button>
                        </div>
                    </div>
                </template>

                <div x-show="criteria.length > 0" class="flex items-center justify-between text-xs px-2 pt-1 font-bold">
                    <span class="text-slate-500">Total Akumulasi Bobot:</span>
                    <span :class="totalWeight === 100 ? 'text-emerald-700 bg-emerald-100 px-2.5 py-0.5 rounded-full font-black' : 'text-amber-700 bg-amber-100 px-2.5 py-0.5 rounded-full font-black'" x-text="totalWeight + '%'"></span>
                </div>
            </div>

            <!-- FOOTER -->
            <div class="bg-white rounded-3xl p-5 sm:p-7 shadow-xl border border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-4">
                <button type="button" onclick="if(confirm('Yakin hapus cabang lomba {{ addslashes($competition->name) }}?')) { document.getElementById('deleteForm').submit(); }" class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 text-xs font-bold transition flex items-center justify-center gap-1.5">
                    Hapus Cabang Lomba
                </button>
                <div class="w-full sm:w-auto flex items-center justify-end gap-3">
                    <a href="{{ route('admin.competitions') }}" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">Batal</a>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-black text-xs shadow-lg shadow-emerald-500/20 transition flex items-center gap-1.5">
                        Simpan Perubahan
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
                addCriterion() { this.criteria.push({ name: '', weight_percentage: 0, min_score: 0, max_score: 100, description: '' }); }
            };
        }
        document.addEventListener('DOMContentLoaded', function() { if (window.lucide) lucide.createIcons(); });
    </script>
</body>
</html>
