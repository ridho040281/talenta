<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'login.required' => 'Silakan masukkan NISN atau Alamat Email Anda.',
            'password.required' => 'Silakan masukkan kata sandi Anda.',
        ]);

        $loginInput = trim($request->input('login'));

        // Check user by NISN or Email
        $user = User::where('nisn', $loginInput)
            ->orWhere('email', $loginInput)
            ->first();

        if ($user && Hash::check($request->password, $user->password)) {
            if ($user->status !== 'active') {
                return back()->withErrors(['login' => 'Akun Anda sedang dinonaktifkan oleh administrator.']);
            }

            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();

            return $this->redirectBasedOnRole($user)
                ->with('success', 'Selamat datang kembali, ' . $user->name . '!');
        }

        return back()->withErrors([
            'login' => 'NISN / Email atau kata sandi yang Anda masukkan tidak sesuai.',
        ])->onlyInput('login');
    }

    public function showRegister()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'nisn' => ['required', 'string', 'digits:10', 'regex:/^[0-9]{10}$/', 'unique:users,nisn'],
            'name' => ['required', 'string', 'max:255'],
            'institution_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'string', 'email', 'max:255', 'unique:users,email'],
        ], [
            'nisn.required' => 'NISN wajib diisi sebagai identitas akun.',
            'nisn.digits' => 'NISN harus tepat 10 digit angka resmi Kemdikbud/Kemenag.',
            'nisn.regex' => 'NISN hanya boleh berisi angka 10 digit.',
            'nisn.unique' => 'NISN ini sudah terdaftar di sistem. Satu NISN hanya untuk 1 akun. Silakan langsung login dengan NISN Anda.',
            'name.required' => 'Nama lengkap peserta / pendaftar wajib diisi.',
            'institution_name.required' => 'Nama asal sekolah / madrasah wajib diisi.',
            'phone.required' => 'Nomor WhatsApp aktif wajib diisi untuk koordinasi.',
            'email.unique' => 'Email ini sudah terdaftar di sistem.',
        ]);

        $nisnClean = trim($validated['nisn']);

        // Check for dummy / fake patterns
        $invalidPatterns = [
            '0000000000', '1111111111', '2222222222', '3333333333', '4444444444',
            '5555555555', '6666666666', '7777777777', '8888888888', '9999999999',
            '1234567890', '0123456789', '9876543210', '0987654321'
        ];
        if (in_array($nisnClean, $invalidPatterns)) {
            return back()->withErrors(['nisn' => 'Format NISN tidak valid / terdeteksi angka acak. Harap masukkan 10 digit NISN resmi Anda.'])->withInput();
        }

        $email = !empty($validated['email']) ? trim($validated['email']) : ($nisnClean . '@pendaftar.talenta');

        $user = User::create([
            'nisn' => $nisnClean,
            'name' => $validated['name'],
            'email' => $email,
            'password' => Hash::make($nisnClean), // Default password is NISN
            'role' => 'peserta',
            'phone' => $validated['phone'],
            'account_type' => 'pendaftar',
            'institution_name' => $validated['institution_name'],
            'status' => 'active',
        ]);

        // Trigger Auto WhatsApp Notification: Pembuatan Akun Baru
        try {
            \App\Services\WablasNotificationService::sendAutoNotification('account_created', [
                'phone' => $user->phone,
                'nama_peserta' => $user->name,
                'nisn' => $user->nisn,
                'nama_sekolah' => $user->institution_name,
                'link_login' => route('login'),
            ]);
        } catch (\Throwable $e) {
            // Non-blocking if gateway offline
        }

        Auth::login($user);

        // Store Account Slip in Session for display & print
        session()->flash('account_slip', [
            'name' => $user->name,
            'nisn' => $user->nisn,
            'institution_name' => $user->institution_name,
            'phone' => $user->phone,
            'email' => $user->email,
            'default_password' => $nisnClean,
            'created_at' => $user->created_at->format('d F Y, H:i') . ' WIB',
        ]);

        return redirect()->route('register.success');
    }

    public function showRegisterSuccess()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $slip = session('account_slip') ?? [
            'name' => $user->name,
            'nisn' => $user->nisn ?? '-',
            'institution_name' => $user->institution_name ?? '-',
            'phone' => $user->phone ?? '-',
            'email' => $user->email,
            'default_password' => $user->nisn ?? 'Sandi Anda',
            'created_at' => $user->created_at->format('d F Y, H:i') . ' WIB',
        ];

        return view('auth.register-success', compact('user', 'slip'));
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'confirmed', Password::min(6)],
        ], [
            'current_password.required' => 'Kata sandi saat ini / default wajib diisi.',
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
            'password.min' => 'Kata sandi baru minimal terdiri dari 6 karakter.',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Kata sandi saat ini tidak cocok.']);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Kata sandi akun Anda berhasil diperbarui dengan aman!');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('info', 'Anda telah berhasil keluar (logout).');
    }

    protected function redirectBasedOnRole(User $user)
    {
        return match ($user->role) {
            'superadmin' => redirect()->route('admin.dashboard'),
            'pic_lomba' => redirect()->route('pic.dashboard'),
            'juri' => redirect()->route('juri.dashboard'),
            default => redirect()->route('peserta.dashboard'),
        };
    }
}
