@extends('layouts.app')

@section('title', 'Buat Akun Pendaftar TALENTA')

@section('content')
<div class="min-h-[85vh] flex items-center justify-center py-16 px-4 sm:px-6 lg:px-8 relative">
    
    <div class="max-w-xl w-full space-y-8 relative z-10">
        
        <!-- Header -->
        <div class="text-center space-y-2">
            @if(!empty($appSettings['app_logo']))
                <div class="flex justify-center mb-3">
                    <img src="{{ asset('storage/' . $appSettings['app_logo']) }}" alt="{{ $appSettings['app_name'] ?? 'Logo' }}" class="h-16 w-auto object-contain drop-shadow-xl">
                </div>
            @else
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-3xl bg-gradient-to-tr from-emerald-500 to-blue-600 text-white shadow-2xl shadow-emerald-500/30 mb-2">
                    <i data-lucide="user-plus" class="w-8 h-8"></i>
                </div>
            @endif
            <h2 class="text-3xl sm:text-4xl font-black text-white tracking-tight font-display">Pendaftaran Akun Resmi</h2>
            <p class="text-xs sm:text-sm text-slate-400">Buat akun untuk mendaftarkan peserta perlombaan {{ $appSettings['app_name'] ?? 'TALENTA MTsN 1 Blitar' }}</p>
        </div>

        <!-- Glass Card Form -->
        <div class="glass-card p-8 rounded-3xl border border-slate-800 shadow-2xl">
            
            <form action="{{ route('register.post') }}" method="POST" class="space-y-5">
                @csrf

                <!-- Name Input -->
                <div>
                    <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">
                        Nama Lengkap Pendaftar / Pembina <span class="text-rose-400">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                            <i data-lucide="user" class="w-5 h-5"></i>
                        </div>
                        <input id="name" name="name" type="text" required value="{{ old('name') }}" placeholder="Contoh: Budi Santoso, S.Pd / Ust. Ridwan" class="block w-full pl-11 pr-4 py-3 rounded-2xl bg-slate-900/90 border border-slate-700/80 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 text-white text-sm outline-none transition @error('name') border-rose-500 @enderror">
                    </div>
                    @error('name')
                        <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Institution Name -->
                <div>
                    <label for="institution_name" class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">
                        Asal Sekolah / Madrasah / Instansi <span class="text-rose-400">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                            <i data-lucide="building-2" class="w-5 h-5"></i>
                        </div>
                        <input id="institution_name" name="institution_name" type="text" required value="{{ old('institution_name') }}" placeholder="Contoh: SD Islam Al-Falah / MI Miftahul Huda" class="block w-full pl-11 pr-4 py-3 rounded-2xl bg-slate-900/90 border border-slate-700/80 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 text-white text-sm outline-none transition @error('institution_name') border-rose-500 @enderror">
                    </div>
                    @error('institution_name')
                        <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Phone WhatsApp (Primary Identifier & Default Password) -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="phone" class="block text-xs font-bold uppercase tracking-wider text-slate-300">
                            Nomor WhatsApp Aktif <span class="text-rose-400">*</span>
                        </label>
                        <span class="text-[10px] font-bold text-amber-400 bg-amber-400/10 px-2 py-0.5 rounded-lg border border-amber-400/20">
                            Otomatis Jadi User & Sandi
                        </span>
                    </div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                            <i data-lucide="phone" class="w-5 h-5 text-emerald-400"></i>
                        </div>
                        <input id="phone" name="phone" type="text" required value="{{ old('phone') }}" placeholder="Contoh: 081234567890" maxlength="20" class="block w-full pl-11 pr-4 py-3 rounded-2xl bg-slate-900/90 border border-slate-700/80 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 text-white font-mono text-sm outline-none transition @error('phone') border-rose-500 @enderror">
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">Digunakan untuk login akun dan menerima notifikasi WhatsApp resmi dari sistem TALENTA.</p>
                    @error('phone')
                        <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email Input (Optional) -->
                <div>
                    <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">
                        Alamat Email (Opsional)
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                            <i data-lucide="mail" class="w-5 h-5"></i>
                        </div>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="email@domain.com (kosongkan jika tidak ada)" class="block w-full pl-11 pr-4 py-3 rounded-2xl bg-slate-900/90 border border-slate-700/80 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 text-white text-sm outline-none transition @error('email') border-rose-500 @enderror">
                    </div>
                    @error('email')
                        <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Auto-Generated Password Info Banner -->
                <div class="p-4 rounded-2xl bg-emerald-950/40 border border-emerald-500/30 flex items-start gap-3 text-emerald-200 text-xs">
                    <i data-lucide="key" class="w-5 h-5 text-emerald-400 shrink-0 mt-0.5"></i>
                    <div>
                        <span class="font-bold text-white block">Informasi Login Akun Induk:</span>
                        <span>Username login dan kata sandi awal Anda adalah <strong>Nomor WhatsApp</strong> yang dimasukkan. Akun ini dapat digunakan untuk mendaftarkan <strong>banyak siswa</strong> pada berbagai cabang lomba.</span>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="w-full py-3.5 px-4 rounded-2xl btn-gradient text-slate-950 font-black text-sm uppercase tracking-wider shadow-lg shadow-emerald-500/25 hover:scale-[1.01] active:scale-[0.99] transition duration-200 flex items-center justify-center gap-2">
                    <i data-lucide="user-plus" class="w-4 h-4"></i>
                    <span>Buat Akun & Dapatkan Bukti Akun</span>
                </button>
            </form>

        </div>

        <p class="text-center text-xs text-slate-400">
            Sudah memiliki akun sebelumnya?
            <a href="{{ route('login') }}" class="font-bold text-emerald-400 hover:text-emerald-300 hover:underline">
                Masuk di sini
            </a>
        </p>

    </div>
</div>
@endsection
