@extends('layouts.admin')

@section('title', 'Manajemen Pengguna')
@section('page_title', 'Manajemen Akun Pengguna')

@section('content')
<div class="space-y-6" x-data="userManagementApp()">
    
    <!-- AIStarterKit Top Action Bar & Filter Header -->
    <div class="ai-card rounded-3xl p-5 sm:p-6 border border-white/[0.08] shadow-xl space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="p-1 rounded-lg bg-[#7A5AF8]/20 text-[#A594FD] border border-[#7A5AF8]/30">
                        <i data-lucide="users" class="w-4 h-4"></i>
                    </span>
                    <h2 class="text-xl sm:text-2xl font-black text-white ai-gradient-text">Pengguna Terdaftar Sistem</h2>
                </div>
                <p class="text-xs text-slate-400 mt-1">Kelola akun Super Admin, Koordinator Cabang Lomba (PIC), Dewan Juri, dan Pendaftar beserta status aktif/nonaktif akun.</p>
            </div>

            <button type="button" @click="userModal = true" class="gradient-btn inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl text-white font-bold text-xs shadow-lg shadow-[#7A5AF8]/25 transition shrink-0 cursor-pointer">
                <i data-lucide="user-plus" class="w-4 h-4"></i>
                <span>Tambah Akun Baru</span>
            </button>
        </div>

        <!-- Filter & Search Bar -->
        <form method="GET" action="{{ route('admin.users') }}" class="space-y-3 pt-3 border-t border-white/[0.08]">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3">
                <div class="sm:col-span-2 md:col-span-2 relative">
                    <i data-lucide="search" class="w-4 h-4 text-slate-500 absolute left-3.5 top-3"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, jabatan, email, no WA, atau instansi..." class="w-full pl-10 pr-9 py-2 rounded-xl bg-[#0C111D] border border-white/[0.1] text-xs font-medium text-white placeholder-slate-500 outline-none focus:border-[#7A5AF8] focus:ring-2 focus:ring-[#7A5AF8]/30">
                    @if(request('search'))
                        <a href="{{ route('admin.users', request()->except('search')) }}" class="absolute right-3 top-2.5 text-slate-400 hover:text-white" title="Hapus pencarian">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </a>
                    @endif
                </div>

                <div>
                    <select name="role" onchange="this.form.submit()" class="w-full px-3.5 py-2 rounded-xl bg-[#0C111D] border border-white/[0.1] text-xs font-bold text-slate-200 outline-none focus:border-[#7A5AF8] focus:ring-2 focus:ring-[#7A5AF8]/30 cursor-pointer">
                        <option value="all" {{ !request('role') || request('role') == 'all' ? 'selected' : '' }}>Semua Role / Hak Akses</option>
                        <option value="superadmin" {{ request('role') == 'superadmin' ? 'selected' : '' }}>👑 Super Administrator</option>
                        <option value="pic_lomba" {{ request('role') == 'pic_lomba' ? 'selected' : '' }}>🛡️ PIC Cabang Lomba</option>
                        <option value="juri" {{ request('role') == 'juri' ? 'selected' : '' }}>⚖️ Dewan Juri / Wasit</option>
                        <option value="peserta" {{ request('role') == 'peserta' ? 'selected' : '' }}>🎓 Peserta / Official</option>
                    </select>
                </div>

                <div>
                    <select name="status" onchange="this.form.submit()" class="w-full px-3.5 py-2 rounded-xl bg-[#0C111D] border border-white/[0.1] text-xs font-bold text-slate-200 outline-none focus:border-[#7A5AF8] focus:ring-2 focus:ring-[#7A5AF8]/30 cursor-pointer">
                        <option value="all" {{ !request('status') || request('status') == 'all' ? 'selected' : '' }}>Semua Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>🟢 Hanya Aktif</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>🔴 Hanya Nonaktif</option>
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="w-full py-2 px-4 rounded-xl bg-white/[0.08] hover:bg-white/[0.12] text-white text-xs font-bold transition flex items-center justify-center gap-1.5 border border-white/[0.1] cursor-pointer">
                        <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                        <span>Terapkan</span>
                    </button>
                    @if(request('search') || (request('role') && request('role') !== 'all') || (request('status') && request('status') !== 'all'))
                        <a href="{{ route('admin.users') }}" class="p-2 rounded-xl bg-rose-500/15 hover:bg-rose-500/25 text-rose-400 border border-rose-500/30 transition shrink-0 flex items-center justify-center" title="Reset Semua Filter">
                            <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                        </a>
                    @endif
                </div>
            </div>

            @if(request('search') || (request('role') && request('role') !== 'all') || (request('status') && request('status') !== 'all'))
                <div class="flex items-center justify-between text-xs text-slate-400 pt-2 border-t border-white/[0.05] flex-wrap gap-2">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Filter Aktif:</span>
                        @if(request('role') && request('role') !== 'all')
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#7A5AF8]/20 text-[#A594FD] border border-[#7A5AF8]/30">
                                Role: {{ request('role') === 'juri' ? '⚖️ Dewan Juri / Wasit' : (request('role') === 'pic_lomba' ? '🛡️ PIC Cabang Lomba' : (request('role') === 'superadmin' ? '👑 Super Administrator' : '🎓 Peserta / Official')) }}
                            </span>
                        @endif
                        @if(request('status') && request('status') !== 'all')
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ request('status') === 'active' ? 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30' : 'bg-rose-500/20 text-rose-300 border-rose-500/30' }} border">
                                Status: {{ request('status') === 'active' ? '🟢 Aktif' : '🔴 Nonaktif' }}
                            </span>
                        @endif
                        @if(request('search'))
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-500/20 text-blue-300 border border-blue-500/30">
                                Kata Kunci: "{{ request('search') }}"
                            </span>
                        @endif
                    </div>
                    <a href="{{ route('admin.users') }}" class="text-[11px] text-[#84D0FF] hover:underline font-bold flex items-center gap-1">
                        <i data-lucide="rotate-ccw" class="w-3 h-3"></i>
                        <span>Reset Semua Filter</span>
                    </a>
                </div>
            @endif
        </form>
    </div>

    <!-- Users Table (AIStarterKit Glass Style) -->
    <div class="ai-card rounded-3xl border border-white/[0.08] shadow-xl overflow-hidden">
        <div class="overflow-x-auto scrollbar-thin scrollbar-thumb-slate-800">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="text-[11px] font-bold uppercase tracking-wider bg-[#0C111D]/90 text-slate-400 border-b border-white/[0.08]">
                    <tr>
                        <th class="py-3.5 px-4 sm:px-5">Pengguna & Jabatan</th>
                        <th class="py-3.5 px-4 sm:px-5">Kontak (Email / WA)</th>
                        <th class="py-3.5 px-4 text-center">Role / Hak Akses</th>
                        <th class="py-3.5 px-4 sm:px-5">Instansi / Asal Sekolah</th>
                        <th class="py-3.5 px-3 text-center">Status Akun</th>
                        <th class="py-3.5 px-4 sm:px-5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/[0.04] font-medium">
                    @forelse($users as $u)
                        <tr class="hover:bg-white/[0.025] transition {{ $u->status === 'inactive' ? 'opacity-60' : '' }}">
                            <!-- Kolom 1: Pengguna & Jabatan -->
                            <td class="py-3 px-4 sm:px-5">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-[#7A5AF8]/30 to-[#4E6EFF]/30 text-white flex items-center justify-center font-bold text-xs shrink-0 border border-white/[0.1] shadow-xs">
                                        {{ strtoupper(substr($u->name, 0, 1)) }}
                                    </div>
                                    <div class="overflow-hidden min-w-0">
                                        <div class="font-bold text-white truncate max-w-[200px] sm:max-w-[240px] text-xs sm:text-sm" title="{{ $u->name }}">
                                            {{ $u->name }}
                                        </div>
                                        @if(!empty($u->position))
                                            <div class="mt-0.5">
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-[#4E6EFF]/15 text-[#84D0FF] border border-[#4E6EFF]/30 font-bold text-[10px]" title="Jabatan: {{ $u->position }}">
                                                    <i data-lucide="badge-check" class="w-3 h-3 text-[#4E6EFF] shrink-0"></i>
                                                    <span class="truncate max-w-[170px]">{{ $u->position }}</span>
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <!-- Kolom 2: Kontak (Email & WA) -->
                            <td class="py-3 px-4 sm:px-5">
                                <div class="space-y-0.5">
                                    <div class="font-mono text-xs text-slate-200 flex items-center gap-1.5 truncate max-w-[220px]" title="{{ $u->email }}">
                                        <i data-lucide="mail" class="w-3.5 h-3.5 text-slate-500 shrink-0"></i>
                                        <span class="truncate">{{ $u->email }}</span>
                                    </div>
                                    @if(!empty($u->phone))
                                        <div class="font-mono text-[11px] text-slate-400 flex items-center gap-1.5">
                                            <i data-lucide="phone" class="w-3 h-3 text-emerald-400 shrink-0"></i>
                                            <span>{{ $u->phone }}</span>
                                        </div>
                                    @endif
                                </div>
                            </td>

                            <!-- Kolom 3: Role / Hak Akses -->
                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold capitalize {{ match($u->role) {
                                    'superadmin' => 'bg-[#7A5AF8]/15 text-[#A594FD] border border-[#7A5AF8]/30',
                                    'pic_lomba' => 'bg-[#4E6EFF]/15 text-[#84D0FF] border border-[#4E6EFF]/30',
                                    'juri' => 'bg-[#FF58D5]/15 text-[#FFA0E7] border border-[#FF58D5]/30',
                                    default => 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30'
                                } }}">
                                    {{ match($u->role) {
                                        'superadmin' => '👑 Super Admin',
                                        'pic_lomba' => '🛡️ PIC Lomba',
                                        'juri' => '⚖️ Dewan Juri',
                                        default => '🎓 Peserta'
                                    } }}
                                </span>
                            </td>

                            <!-- Kolom 4: Instansi / Asal Sekolah -->
                            <td class="py-3 px-4 sm:px-5 text-xs text-slate-300">
                                <div class="flex items-center gap-1.5">
                                    <i data-lucide="building" class="w-3.5 h-3.5 text-slate-500 shrink-0"></i>
                                    <span class="truncate max-w-[180px] font-medium" title="{{ $u->institution_name ?: '-' }}">{{ $u->institution_name ?: '-' }}</span>
                                </div>
                            </td>

                            <!-- Kolom 5: Status Akun (Aktif / Nonaktif) -->
                            <td class="py-3 px-3 text-center whitespace-nowrap">
                                @if(($u->status ?? 'active') === 'active')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                        <span>Aktif</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-500/15 text-rose-400 border border-rose-500/30">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span>
                                        <span>Nonaktif</span>
                                    </span>
                                @endif
                            </td>

                            <!-- Kolom 6: Aksi -->
                            <td class="py-3 px-4 sm:px-5 text-center whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5 justify-center">
                                    <!-- 🔑 Tombol Reset Password -->
                                    <button type="button" 
                                            @click="openResetPassword(@js($u))" 
                                            class="p-2 rounded-xl bg-amber-500/15 hover:bg-amber-500/25 text-amber-400 border border-amber-500/30 transition cursor-pointer shadow-xs" 
                                            title="Reset Kata Sandi Pengguna">
                                        <i data-lucide="key" class="w-4 h-4"></i>
                                    </button>

                                    <!-- ✏️ Tombol Edit Akun -->
                                    <button type="button" 
                                            @click="openEditUser(@js($u))" 
                                            class="p-2 rounded-xl bg-white/[0.06] hover:bg-white/[0.12] text-slate-200 border border-white/[0.1] transition cursor-pointer shadow-xs" 
                                            title="Edit Data Pengguna & Status Akun">
                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                    </button>

                                    <!-- 🗑️ Tombol Hapus Akun -->
                                    @if($u->id !== auth()->id())
                                        <button type="button"
                                                @click="openDeleteModal(@js($u))"
                                                class="p-2 rounded-xl bg-rose-500/15 hover:bg-rose-500/25 text-rose-400 border border-rose-500/30 transition cursor-pointer shadow-xs" 
                                                title="Hapus Akun Pengguna">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    @else
                                        <button type="button" 
                                                disabled 
                                                class="p-2 rounded-xl bg-white/[0.02] text-slate-600 border border-white/[0.04] cursor-not-allowed" 
                                                title="Akun Anda sedang aktif">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-500">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <i data-lucide="users" class="w-8 h-8 text-slate-600"></i>
                                    <span>Tidak ada pengguna yang cocok dengan kriteria pencarian.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="p-4 border-t border-white/[0.08]">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    <!-- ==================== MODAL 1: RESET PASSWORD (AIStarterKit Dark Style) ==================== -->
    <div x-show="resetPasswordModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div x-show="resetPasswordModal" x-transition.opacity @click="resetPasswordModal = false" class="fixed inset-0 bg-black/80 backdrop-blur-md"></div>

        <div x-show="resetPasswordModal" x-transition.scale.95 @click.stop class="relative z-10 w-full max-w-md bg-[#161F30] rounded-3xl p-6 sm:p-8 space-y-6 border border-white/[0.12] text-slate-200 shadow-2xl my-auto">
                
                <div class="flex items-center justify-between border-b border-white/[0.08] pb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-amber-500/20 text-amber-400 border border-amber-500/30 flex items-center justify-center font-bold">
                            <i data-lucide="key" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-white">Reset Password Pengguna</h3>
                            <p class="text-xs text-slate-400">Buat kata sandi baru untuk pengguna ini</p>
                        </div>
                    </div>
                    <button @click="resetPasswordModal = false" class="text-slate-400 hover:text-white">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <!-- Info User -->
                <div class="p-3.5 rounded-2xl bg-[#0C111D] border border-white/[0.08] text-xs space-y-1.5">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400 font-bold uppercase text-[10px]">Nama Akun:</span>
                        <span class="font-black text-white text-sm" x-text="selectedUser ? selectedUser.name : ''"></span>
                    </div>
                    <div class="flex items-center justify-between" x-show="selectedUser && selectedUser.position">
                        <span class="text-slate-400 font-bold uppercase text-[10px]">Jabatan:</span>
                        <span class="font-bold text-[#84D0FF]" x-text="selectedUser ? selectedUser.position : '-'"></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400 font-bold uppercase text-[10px]">Email:</span>
                        <span class="font-mono font-bold text-slate-300" x-text="selectedUser ? selectedUser.email : ''"></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400 font-bold uppercase text-[10px]">Role / Akses:</span>
                        <span class="font-bold text-[#A594FD] capitalize" x-text="selectedUser ? selectedUser.role : ''"></span>
                    </div>
                </div>

                <form :action="'{{ url('admin/users') }}/' + (selectedUser ? selectedUser.id : '') + '/reset-password'" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">
                            Kata Sandi Baru <span class="text-rose-400">*</span>
                        </label>
                        <div class="relative">
                            <input :type="showPassword ? 'text' : 'password'" 
                                   name="password" 
                                   x-model="newPassword" 
                                   required 
                                   minlength="6" 
                                   placeholder="Ketik password baru (min. 6 karakter)..." 
                                   class="w-full px-4 py-3 rounded-2xl bg-[#0C111D] border border-white/[0.12] text-sm font-mono font-bold text-white outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-400/20 pr-10">
                            <button type="button" @click="showPassword = !showPassword" class="absolute right-3 top-3.5 text-slate-400 hover:text-white cursor-pointer">
                                <i :data-lucide="showPassword ? 'eye-off' : 'eye'" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Shortcut Generator Password Cepat -->
                    <div class="space-y-1.5">
                        <span class="text-[11px] font-bold text-slate-400 block">Pilihan Cepat / Generator:</span>
                        <div class="flex flex-wrap items-center gap-1.5 text-xs">
                            <button type="button" @click="generateRandomPassword()" class="px-2.5 py-1 rounded-xl bg-amber-500/15 hover:bg-amber-500/25 text-amber-300 border border-amber-500/30 text-[11px] font-bold transition flex items-center gap-1 cursor-pointer">
                                <i data-lucide="sparkles" class="w-3 h-3 text-amber-400"></i>
                                <span>🎲 Acak 8 Karakter</span>
                            </button>
                            <button type="button" @click="setDefaultPassword('talenta2026')" class="px-2.5 py-1 rounded-xl bg-white/[0.05] hover:bg-white/[0.1] text-slate-300 font-mono text-[11px] font-bold border border-white/[0.08] transition cursor-pointer">
                                talenta2026
                            </button>
                            <button type="button" @click="setDefaultPassword('12345678')" class="px-2.5 py-1 rounded-xl bg-white/[0.05] hover:bg-white/[0.1] text-slate-300 font-mono text-[11px] font-bold border border-white/[0.08] transition cursor-pointer">
                                12345678
                            </button>
                        </div>
                    </div>

                    <div class="pt-4 flex items-center justify-end gap-3 border-t border-white/[0.08]">
                        <button type="button" @click="resetPasswordModal = false" class="px-5 py-2.5 rounded-xl bg-white/[0.06] hover:bg-white/[0.12] text-slate-300 text-xs font-bold transition cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-black text-xs shadow-lg shadow-amber-500/30 transition cursor-pointer flex items-center gap-1.5">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            <span>Simpan Password Baru</span>
                        </button>
                    </div>
                </form>

            </div>
        </div>

    <!-- ==================== MODAL 2: EDIT DATA PENGGUNA (AIStarterKit Dark Style) ==================== -->
    <div x-show="editUserModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div x-show="editUserModal" x-transition.opacity @click="editUserModal = false" class="fixed inset-0 bg-black/80 backdrop-blur-md"></div>

        <div x-show="editUserModal" x-transition.scale.95 @click.stop class="relative z-10 w-full max-w-lg bg-[#161F30] rounded-3xl p-6 sm:p-8 space-y-6 border border-white/[0.12] text-slate-200 shadow-2xl my-auto">
                
                <div class="flex items-center justify-between border-b border-white/[0.08] pb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-[#7A5AF8]/20 text-[#A594FD] border border-[#7A5AF8]/30 flex items-center justify-center font-bold">
                            <i data-lucide="edit-3" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-white">Edit Data Akun & Status Pengguna</h3>
                            <p class="text-xs text-slate-400">Perbarui profil, jabatan, wewenang, dan status akun</p>
                        </div>
                    </div>
                    <button @click="editUserModal = false" class="text-slate-400 hover:text-white">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form :action="'{{ url('admin/users') }}/' + (selectedUser ? selectedUser.id : '') + '/update'" method="POST" class="space-y-4">
                    @csrf

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">Role / Wewenang <span class="text-rose-400">*</span></label>
                            <select name="role" x-model="selectedUser.role" required class="block w-full px-4 py-2.5 rounded-xl bg-[#0C111D] border border-white/[0.12] text-sm text-white outline-none focus:border-[#7A5AF8] focus:ring-2 focus:ring-[#7A5AF8]/30">
                                <option value="superadmin">👑 Super Administrator</option>
                                <option value="pic_lomba">🛡️ Koordinator PIC Cabang Lomba</option>
                                <option value="juri">⚖️ Dewan Juri / Wasit</option>
                                <option value="peserta">🎓 Pendaftar / Peserta</option>
                            </select>
                        </div>

                        <!-- Status Akun Aktif / Nonaktif -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">Status Akun <span class="text-rose-400">*</span></label>
                            <select name="status" x-model="selectedUser.status" required class="block w-full px-4 py-2.5 rounded-xl bg-[#0C111D] border border-white/[0.12] text-sm font-bold text-white outline-none focus:border-[#7A5AF8] focus:ring-2 focus:ring-[#7A5AF8]/30">
                                <option value="active">🟢 Aktif (Bisa Login)</option>
                                <option value="inactive">🔴 Nonaktif (Dibekukan)</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">Nama Lengkap & Gelar <span class="text-rose-400">*</span></label>
                        <input name="name" type="text" x-model="selectedUser.name" required class="block w-full px-4 py-2.5 rounded-xl bg-[#0C111D] border border-white/[0.12] text-sm text-white outline-none focus:border-[#7A5AF8] focus:ring-2 focus:ring-[#7A5AF8]/30">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">
                            Jabatan / Posisi Panitia <span class="text-[10px] text-slate-400 font-normal lowercase">(opsional)</span>
                        </label>
                        <input name="position" type="text" x-model="selectedUser.position" placeholder="Contoh: Ketua Panitia / Koordinator Lomba / Juri Catur" class="block w-full px-4 py-2.5 rounded-xl bg-[#0C111D] border border-white/[0.12] text-sm text-white outline-none focus:border-[#7A5AF8] focus:ring-2 focus:ring-[#7A5AF8]/30">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">Alamat Email <span class="text-rose-400">*</span></label>
                        <input name="email" type="email" x-model="selectedUser.email" required class="block w-full px-4 py-2.5 rounded-xl bg-[#0C111D] border border-white/[0.12] text-sm text-white outline-none focus:border-[#7A5AF8] focus:ring-2 focus:ring-[#7A5AF8]/30">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">Nomor WhatsApp</label>
                        <input name="phone" type="text" x-model="selectedUser.phone" placeholder="08xxxxxxxxxx" class="block w-full px-4 py-2.5 rounded-xl bg-[#0C111D] border border-white/[0.12] text-sm text-white outline-none focus:border-[#7A5AF8] focus:ring-2 focus:ring-[#7A5AF8]/30">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">Asal Instansi / Lembaga</label>
                        <input name="institution_name" type="text" x-model="selectedUser.institution_name" placeholder="Contoh: SDI Al-Falah / MTsN 1 Blitar / Percasi" class="block w-full px-4 py-2.5 rounded-xl bg-[#0C111D] border border-white/[0.12] text-sm text-white outline-none focus:border-[#7A5AF8] focus:ring-2 focus:ring-[#7A5AF8]/30">
                    </div>

                    <div class="pt-4 flex items-center justify-end gap-3 border-t border-white/[0.08]">
                        <button type="button" @click="editUserModal = false" class="px-5 py-2.5 rounded-xl bg-white/[0.06] hover:bg-white/[0.12] text-slate-300 text-xs font-bold transition cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" class="gradient-btn px-6 py-2.5 rounded-xl text-white text-xs font-bold shadow-lg shadow-[#7A5AF8]/30 transition cursor-pointer flex items-center gap-1.5">
                            <i data-lucide="save" class="w-4 h-4"></i>
                            <span>Simpan Perubahan</span>
                        </button>
                    </div>
                </form>

            </div>
        </div>

    <!-- ==================== MODAL 3: TAMBAH USER BARU (AIStarterKit Dark Style) ==================== -->
    <div x-show="userModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div x-show="userModal" x-transition.opacity @click="userModal = false" class="fixed inset-0 bg-black/80 backdrop-blur-md"></div>

        <div x-show="userModal" x-transition.scale.95 @click.stop class="relative z-10 w-full max-w-lg bg-[#161F30] rounded-3xl p-6 sm:p-8 space-y-6 border border-white/[0.12] text-slate-200 shadow-2xl my-auto">
                
                <div class="flex items-center justify-between border-b border-white/[0.08] pb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-[#7A5AF8]/20 text-[#A594FD] border border-[#7A5AF8]/30 flex items-center justify-center font-bold">
                            <i data-lucide="user-plus" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-white">Tambah Akun Pengguna Baru</h3>
                            <p class="text-xs text-slate-400">Daftarkan akun panitia, dewan juri, atau official peserta</p>
                        </div>
                    </div>
                    <button @click="userModal = false" class="text-slate-400 hover:text-white">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-4">
                    @csrf

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">Role / Wewenang <span class="text-rose-400">*</span></label>
                            <select name="role" required class="block w-full px-4 py-2.5 rounded-xl bg-[#0C111D] border border-white/[0.12] text-sm text-white outline-none focus:border-[#7A5AF8] focus:ring-2 focus:ring-[#7A5AF8]/30">
                                <option value="superadmin">👑 Super Administrator</option>
                                <option value="pic_lomba">🛡️ Koordinator PIC Cabang Lomba</option>
                                <option value="juri">⚖️ Dewan Juri / Wasit</option>
                                <option value="peserta">🎓 Pendaftar / Peserta</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">Status Awal <span class="text-rose-400">*</span></label>
                            <select name="status" class="block w-full px-4 py-2.5 rounded-xl bg-[#0C111D] border border-white/[0.12] text-sm font-bold text-white outline-none focus:border-[#7A5AF8] focus:ring-2 focus:ring-[#7A5AF8]/30">
                                <option value="active" selected>🟢 Aktif (Bisa Langsung Login)</option>
                                <option value="inactive">🔴 Nonaktif (Dibekukan)</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">Nama Lengkap & Gelar <span class="text-rose-400">*</span></label>
                        <input name="name" type="text" required placeholder="Contoh: Dr. H. Ahmad Fauzi, M.Pd" class="block w-full px-4 py-2.5 rounded-xl bg-[#0C111D] border border-white/[0.12] text-sm text-white outline-none focus:border-[#7A5AF8] focus:ring-2 focus:ring-[#7A5AF8]/30">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">
                            Jabatan / Posisi Panitia <span class="text-[10px] text-slate-400 font-normal lowercase">(opsional)</span>
                        </label>
                        <input name="position" type="text" placeholder="Contoh: Ketua Panitia / Koordinator Lomba / Bendahara / Juri Catur" class="block w-full px-4 py-2.5 rounded-xl bg-[#0C111D] border border-white/[0.12] text-sm text-white outline-none focus:border-[#7A5AF8] focus:ring-2 focus:ring-[#7A5AF8]/30">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">Alamat Email <span class="text-rose-400">*</span></label>
                        <input name="email" type="email" required placeholder="nama@email.com" class="block w-full px-4 py-2.5 rounded-xl bg-[#0C111D] border border-white/[0.12] text-sm text-white outline-none focus:border-[#7A5AF8] focus:ring-2 focus:ring-[#7A5AF8]/30">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">Nomor WhatsApp</label>
                        <input name="phone" type="text" placeholder="08xxxxxxxxxx" class="block w-full px-4 py-2.5 rounded-xl bg-[#0C111D] border border-white/[0.12] text-sm text-white outline-none focus:border-[#7A5AF8] focus:ring-2 focus:ring-[#7A5AF8]/30">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">Asal Instansi / Lembaga</label>
                        <input name="institution_name" type="text" placeholder="Contoh: LPTQ / MTsN 1 Blitar / Percasi" class="block w-full px-4 py-2.5 rounded-xl bg-[#0C111D] border border-white/[0.12] text-sm text-white outline-none focus:border-[#7A5AF8] focus:ring-2 focus:ring-[#7A5AF8]/30">
                    </div>

                    <div x-data="{ showPass: false }">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">Kata Sandi Default <span class="text-rose-400">*</span></label>
                        <div class="relative">
                            <input :type="showPass ? 'text' : 'password'" type="password" name="password" required placeholder="Min. 6 karakter" class="block w-full pl-4 pr-11 py-2.5 rounded-xl bg-[#0C111D] border border-white/[0.12] text-sm text-white outline-none focus:border-[#7A5AF8] focus:ring-2 focus:ring-[#7A5AF8]/30">
                            <button type="button" @click="showPass = !showPass" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-white transition" tabindex="-1">
                                <svg x-show="!showPass" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                <svg x-show="showPass" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" /></svg>
                            </button>
                        </div>
                    </div>

                    <div class="pt-4 flex items-center justify-end gap-3 border-t border-white/[0.08]">
                        <button type="button" @click="userModal = false" class="px-5 py-2.5 rounded-xl bg-white/[0.06] hover:bg-white/[0.12] text-slate-300 text-xs font-bold transition cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" class="gradient-btn px-6 py-2.5 rounded-xl text-white text-xs font-bold shadow-lg shadow-[#7A5AF8]/30 transition cursor-pointer flex items-center gap-1.5">
                            <i data-lucide="user-plus" class="w-4 h-4"></i>
                            <span>Simpan Akun</span>
                        </button>
                    </div>
                </form>

            </div>
        </div>

    <!-- ==================== MODAL 4: KONFIRMASI HAPUS PENGGUNA (AIStarterKit Dark Style) ==================== -->
    <div x-show="deleteUserModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div x-show="deleteUserModal" x-transition.opacity @click="deleteUserModal = false" class="fixed inset-0 bg-black/80 backdrop-blur-md"></div>

        <div x-show="deleteUserModal" x-transition.scale.95 @click.stop class="relative z-10 w-full max-w-md bg-[#161F30] rounded-3xl p-6 sm:p-8 space-y-6 border border-rose-500/30 text-slate-200 shadow-2xl my-auto">
                
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-rose-500/20 text-rose-400 border border-rose-500/30 flex items-center justify-center shrink-0 shadow-lg shadow-rose-500/20">
                        <i data-lucide="alert-triangle" class="w-6 h-6"></i>
                    </div>
                    <div class="flex-1 overflow-hidden">
                        <h3 class="text-lg font-black text-white leading-snug">Hapus Akun Pengguna?</h3>
                        <p class="text-xs text-rose-400 font-semibold mt-0.5">Peringatan: Tindakan ini bersifat permanen!</p>
                    </div>
                    <button @click="deleteUserModal = false" class="text-slate-400 hover:text-white -mt-1 -mr-1">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <!-- Info User Yang Akan Dihapus -->
                <div class="p-4 rounded-2xl bg-[#0C111D] border border-white/[0.08] text-xs space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400 font-bold uppercase text-[10px]">Nama Akun:</span>
                        <span class="font-black text-white text-sm truncate max-w-[200px]" x-text="selectedUser ? selectedUser.name : ''"></span>
                    </div>
                    <div class="flex items-center justify-between" x-show="selectedUser && selectedUser.position">
                        <span class="text-slate-400 font-bold uppercase text-[10px]">Jabatan:</span>
                        <span class="font-bold text-[#84D0FF] truncate max-w-[200px]" x-text="selectedUser ? selectedUser.position : '-'"></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400 font-bold uppercase text-[10px]">Email:</span>
                        <span class="font-mono font-bold text-slate-300 truncate max-w-[200px]" x-text="selectedUser ? selectedUser.email : ''"></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400 font-bold uppercase text-[10px]">Role / Akses:</span>
                        <span class="font-bold text-[#A594FD] capitalize" x-text="selectedUser ? selectedUser.role : ''"></span>
                    </div>
                    <div class="flex items-center justify-between" x-show="selectedUser && selectedUser.institution_name">
                        <span class="text-slate-400 font-bold uppercase text-[10px]">Instansi:</span>
                        <span class="font-medium text-slate-300 truncate max-w-[200px]" x-text="selectedUser ? selectedUser.institution_name : '-'"></span>
                    </div>
                </div>

                <div class="p-3.5 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-[11px] text-rose-300 flex items-start gap-2.5">
                    <i data-lucide="alert-circle" class="w-4 h-4 text-rose-400 shrink-0 mt-0.5"></i>
                    <p class="leading-relaxed">
                        Akun pengguna ini akan dihapus secara permanen dari basis data. Pastikan tidak ada data penilaian atau pendaftaran penting yang masih aktif terkait akun ini.
                    </p>
                </div>

                <form :action="'{{ url('admin/users') }}/' + (selectedUser ? selectedUser.id : '') + '/delete'" method="POST" class="pt-2 flex items-center justify-end gap-3 border-t border-white/[0.08]">
                    @csrf
                    <button type="button" @click="deleteUserModal = false" class="px-5 py-2.5 rounded-xl bg-white/[0.06] hover:bg-white/[0.12] text-slate-300 text-xs font-bold transition cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs shadow-lg shadow-rose-600/30 transition cursor-pointer flex items-center gap-1.5">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                        <span>Ya, Hapus Pengguna Ini</span>
                    </button>
                </form>

            </div>
        </div>

</div>

<script>
    function userManagementApp() {
        return {
            userModal: false,
            editUserModal: false,
            resetPasswordModal: false,
            deleteUserModal: false,
            selectedUser: {
                id: null,
                name: '',
                email: '',
                role: 'peserta',
                status: 'active',
                phone: '',
                institution_name: '',
                position: ''
            },
            newPassword: '',
            showPassword: true,
            openResetPassword(user) {
                this.selectedUser = Object.assign({}, user);
                this.newPassword = '';
                this.showPassword = true;
                this.resetPasswordModal = true;
                this.$nextTick(() => {
                    if (window.lucide) window.lucide.createIcons();
                });
            },
            openEditUser(user) {
                this.selectedUser = Object.assign({
                    id: null,
                    name: '',
                    email: '',
                    role: 'peserta',
                    status: 'active',
                    phone: '',
                    institution_name: '',
                    position: ''
                }, user);
                this.selectedUser.phone = this.selectedUser.phone || '';
                this.selectedUser.institution_name = this.selectedUser.institution_name || '';
                this.selectedUser.position = this.selectedUser.position || '';
                this.selectedUser.status = this.selectedUser.status || 'active';
                this.editUserModal = true;
                this.$nextTick(() => {
                    if (window.lucide) window.lucide.createIcons();
                });
            },
            openDeleteModal(user) {
                this.selectedUser = Object.assign({}, user);
                this.deleteUserModal = true;
                this.$nextTick(() => {
                    if (window.lucide) window.lucide.createIcons();
                });
            },
            generateRandomPassword() {
                const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
                let pass = '';
                for (let i = 0; i < 8; i++) {
                    pass += chars.charAt(Math.floor(Math.random() * chars.length));
                }
                this.newPassword = pass;
            },
            setDefaultPassword(preset) {
                this.newPassword = preset;
            }
        };
    }
</script>
@endsection
