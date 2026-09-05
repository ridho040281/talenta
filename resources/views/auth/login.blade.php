@extends('layouts.app')

@section('title', 'Masuk ke Akun TALENTA')

@section('content')
<div class="min-h-[80vh] flex items-center justify-center py-16 px-4 sm:px-6 lg:px-8 relative">
    
    <div class="max-w-md w-full space-y-8 relative z-10">
        
        <!-- Header -->
        <div class="text-center space-y-2">
            @if(!empty($appSettings['app_logo']))
                <div class="flex justify-center mb-3">
                    <img src="{{ asset('storage/' . $appSettings['app_logo']) }}" alt="{{ $appSettings['app_name'] ?? 'Logo' }}" class="h-16 w-auto object-contain drop-shadow-xl">
                </div>
            @else
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-3xl bg-gradient-to-tr from-emerald-500 to-blue-600 text-white shadow-2xl shadow-emerald-500/30 mb-2">
                    <i data-lucide="lock" class="w-8 h-8"></i>
                </div>
            @endif
            <h2 class="text-3xl sm:text-4xl font-black text-white tracking-tight font-display">Portal Masuk {{ $appSettings['app_name'] ?? 'TALENTA' }}</h2>
            <p class="text-xs sm:text-sm text-slate-400">Masuk untuk mengakses pendaftaran lomba, lembar juri, atau panel admin</p>
        </div>

        <!-- Glass Card Login -->
        <div class="glass-card p-8 rounded-3xl border border-slate-800 shadow-2xl space-y-6">
            
            <form action="{{ route('login.post') }}" method="POST" class="space-y-5" id="loginForm">
                @csrf

                <!-- NISN or Email -->
                <div>
                    <label for="login" class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-2">
                        NISN atau Alamat Email
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                            <i data-lucide="user" class="w-5 h-5"></i>
                        </div>
                        <input id="login" name="login" type="text" autocomplete="username" required value="{{ old('login') }}" placeholder="Masukkan nomor NISN atau Email" class="block w-full pl-11 pr-4 py-3 rounded-2xl bg-slate-900/90 border border-slate-700/80 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 text-white text-sm outline-none transition @error('login') border-rose-500 @enderror">
                    </div>
                    @error('login')
                        <p class="mt-1.5 text-xs text-rose-400 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div x-data="{ showPassword: false }">
                    <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-2">Kata Sandi (Password)</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                            <i data-lucide="key-round" class="w-5 h-5"></i>
                        </div>
                        <input id="password" 
                               name="password" 
                               :type="showPassword ? 'text' : 'password'" 
                               type="password" 
                               required 
                               placeholder="Masukkan password (default: NISN)" 
                               class="block w-full pl-11 pr-11 py-3 rounded-2xl bg-slate-900/90 border border-slate-700/80 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 text-white text-sm outline-none transition">
                        <button type="button" 
                                @click="showPassword = !showPassword" 
                                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-emerald-400 focus:outline-none transition-colors"
                                :title="showPassword ? 'Sembunyikan Kata Sandi' : 'Lihat Kata Sandi'"
                                tabindex="-1">
                            <!-- Icon Eye (saat password tersembunyi) -->
                            <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <!-- Icon Eye-Off (saat password terlihat) -->
                            <svg x-show="showPassword" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Math Captcha Security Verification (1-25) -->
                <div x-data="{ 
                    captchaQuestion: '{{ session('login_captcha_question') ?? '...' }}',
                    refreshing: false,
                    refresh() {
                        this.refreshing = true;
                        fetch('{{ route('captcha.refresh') }}')
                            .then(res => res.json())
                            .then(data => {
                                this.captchaQuestion = data.question;
                                this.refreshing = false;
                            })
                            .catch(() => this.refreshing = false);
                    }
                }" class="p-3.5 rounded-2xl bg-slate-900/90 border border-slate-700/80 space-y-2">
                    <div class="flex items-center justify-between">
                        <label for="captcha" class="block text-xs font-bold uppercase tracking-wider text-slate-300 flex items-center gap-1.5">
                            <i data-lucide="shield-check" class="w-4 h-4 text-emerald-400"></i>
                            <span>Hitung Angka Pengaman</span>
                        </label>
                        <button type="button" @click="refresh()" class="text-[11px] text-slate-400 hover:text-emerald-400 flex items-center gap-1 transition cursor-pointer" title="Ganti Soal Captcha">
                            <i data-lucide="refresh-cw" class="w-3.5 h-3.5" :class="refreshing ? 'animate-spin' : ''"></i>
                            <span>Ganti Soal</span>
                        </button>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <div class="px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-700/80 text-emerald-400 font-mono font-black text-sm tracking-wider select-none shrink-0 shadow-inner flex items-center gap-1">
                            <span x-text="captchaQuestion + ' =' "></span>
                        </div>
                        <input id="captcha" name="captcha" type="number" required placeholder="Jawaban?" autocomplete="off" class="block w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-700/80 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 text-white font-mono font-bold text-sm outline-none transition @error('captcha') border-rose-500 @enderror">
                    </div>
                    @error('captcha')
                        <p class="text-xs text-rose-400 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Remember & Forgot -->
                <div class="flex items-center justify-between text-xs">
                    <label class="flex items-center gap-2 cursor-pointer text-slate-400">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded text-emerald-500 bg-slate-900 border-slate-700 focus:ring-emerald-500">
                        <span>Ingat sesi saya</span>
                    </label>
                    <a href="https://wa.me/6281234567890?text=Halo%20Panitia%20TALENTA,%20saya%20lupa%20password%20akun%20saya" target="_blank" class="font-bold text-emerald-400 hover:text-emerald-300">Lupa sandi?</a>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="w-full py-3.5 px-4 rounded-2xl btn-gradient text-slate-950 font-black text-sm uppercase tracking-wider shadow-lg shadow-emerald-500/25 hover:scale-[1.01] active:scale-[0.99] transition duration-200 flex items-center justify-center gap-2 cursor-pointer">
                    <i data-lucide="log-in" class="w-4 h-4"></i>
                    <span>Masuk ke Akun</span>
                </button>
            </form>

        </div>

        <p class="text-center text-xs text-slate-400">
            Belum memiliki akun pendaftar?
            <a href="{{ route('register') }}" class="font-bold text-emerald-400 hover:text-emerald-300 hover:underline">
                Daftar Akun Baru
            </a>
        </p>

    </div>
</div>
@endsection
