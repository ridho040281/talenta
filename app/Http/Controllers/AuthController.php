<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use App\Services\WablasNotificationService;
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

        if (! session()->has('login_captcha_question') || ! session()->has('login_captcha_answer')) {
            static::generateMathCaptcha();
        }

        return view('auth.login');
    }

    /**
     * Refresh Math Captcha for AJAX request
     */
    public function refreshCaptcha()
    {
        $captcha = static::generateMathCaptcha();

        return response()->json(['question' => $captcha['question']]);
    }

    /**
     * Generate Math Captcha (Addition, Subtraction, Division 1-25)
     */
    public static function generateMathCaptcha(): array
    {
        $types = ['add', 'sub', 'div'];
        $type = $types[array_rand($types)];

        if ($type === 'add') {
            $a = rand(1, 15);
            $b = rand(1, 10);
            $question = "{$a} + {$b}";
            $answer = $a + $b;
        } elseif ($type === 'sub') {
            $a = rand(10, 25);
            $b = rand(1, $a - 1);
            $question = "{$a} - {$b}";
            $answer = $a - $b;
        } else { // div
            $divisor = rand(2, 5);
            $quotient = rand(1, 5);
            $a = $divisor * $quotient;
            $question = "{$a} ÷ {$divisor}";
            $answer = $quotient;
        }

        session([
            'login_captcha_question' => $question,
            'login_captcha_answer' => (string) $answer,
        ]);

        return ['question' => $question, 'answer' => $answer];
    }

    public function login(Request $request)
    {
        $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
            'captcha' => ['required', 'string'],
        ], [
            'login.required' => 'Silakan masukkan Nomor WhatsApp, NISN, atau Alamat Email Anda.',
            'password.required' => 'Silakan masukkan kata sandi Anda.',
            'captcha.required' => 'Silakan isi jawaban perhitungan verifikasi (Captcha).',
        ]);

        // Validate Captcha
        $expectedAnswer = session('login_captcha_answer');
        $userAnswer = trim((string) $request->input('captcha', ''));

        if ($expectedAnswer === null || $userAnswer !== (string) $expectedAnswer) {
            static::generateMathCaptcha();

            return back()->withErrors([
                'captcha' => 'Jawaban hitungan keamanan (Captcha) tidak sesuai. Silakan coba lagi.',
            ])->onlyInput('login');
        }

        $loginInput = trim($request->input('login'));
        $digitsOnly = preg_replace('/[^0-9]/', '', $loginInput);

        // Normalize phone variations (08xxx <-> 628xxx <-> +628xxx)
        $phoneVariations = [];
        if (! empty($digitsOnly)) {
            $phoneVariations[] = $digitsOnly;
            if (str_starts_with($digitsOnly, '08')) {
                $phoneVariations[] = '62'.substr($digitsOnly, 1);
                $phoneVariations[] = '+62'.substr($digitsOnly, 1);
            } elseif (str_starts_with($digitsOnly, '628')) {
                $phoneVariations[] = '0'.substr($digitsOnly, 2);
                $phoneVariations[] = '+'.$digitsOnly;
            }
        }

        // Check user by Phone (WhatsApp), NISN (old accounts), or Email
        $user = User::where(function ($q) use ($loginInput, $phoneVariations) {
            $q->where('email', $loginInput)
                ->orWhere('nisn', $loginInput);

            if (! empty($phoneVariations)) {
                $q->orWhereIn('phone', $phoneVariations);
            }
        })->first();

        if ($user && Hash::check($request->password, $user->password)) {
            if ($user->status !== 'active') {
                ActivityLog::record('LOGIN_BLOCKED', "Percobaan login pada akun yang dinonaktifkan: '{$user->name}'", $user, 'warning', $loginInput);

                static::generateMathCaptcha();

                return back()->withErrors(['login' => 'Akun Anda sedang dinonaktifkan oleh administrator.']);
            }

            // Forget captcha session on successful login
            session()->forget(['login_captcha_question', 'login_captcha_answer']);

            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();

            ActivityLog::record('LOGIN_SUCCESS', 'Berhasil login ke sistem sebagai role: '.strtoupper($user->role), $user, 'success', $loginInput);

            return $this->redirectBasedOnRole($user);
        }

        static::generateMathCaptcha();

        if ($user) {
            ActivityLog::record('LOGIN_FAILED', "Percobaan login GAGAL (kata sandi salah) untuk akun: '{$user->name}' ({$user->role})", $user, 'failed', $loginInput);
        } else {
            ActivityLog::record('LOGIN_FAILED', "Percobaan login GAGAL (akun tidak terdaftar): '{$loginInput}'", null, 'failed', $loginInput);
        }

        return back()->withErrors([
            'login' => 'Nomor WhatsApp / NISN / Email atau kata sandi yang Anda masukkan tidak sesuai.',
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
            'name' => ['required', 'string', 'max:255'],
            'institution_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'min:9', 'max:20'],
            'email' => ['nullable', 'string', 'email', 'max:255', 'unique:users,email'],
        ], [
            'name.required' => 'Nama lengkap pendaftar / pembina wajib diisi.',
            'institution_name.required' => 'Nama asal sekolah / madrasah / instansi wajib diisi.',
            'phone.required' => 'Nomor WhatsApp aktif wajib diisi sebagai identitas akun.',
            'phone.min' => 'Nomor WhatsApp minimal 9 digit angka.',
            'email.unique' => 'Alamat Email ini sudah terdaftar di sistem.',
        ]);

        $cleanPhone = preg_replace('/[^0-9]/', '', $validated['phone']);
        if (str_starts_with($cleanPhone, '62')) {
            $cleanPhone = '0'.substr($cleanPhone, 2);
        }

        // Check if phone already registered
        $existingPhone = User::where('phone', $cleanPhone)
            ->orWhere('phone', '62'.substr($cleanPhone, 1))
            ->orWhere('phone', '+62'.substr($cleanPhone, 1))
            ->first();

        if ($existingPhone) {
            return back()->withErrors([
                'phone' => 'Nomor WhatsApp ini sudah terdaftar. Silakan langsung masuk (login) menggunakan nomor WhatsApp Anda.',
            ])->withInput();
        }

        $email = ! empty($validated['email']) ? trim($validated['email']) : ($cleanPhone.'@pendaftar.talenta');

        $user = User::create([
            'name' => $validated['name'],
            'institution_name' => $validated['institution_name'],
            'phone' => $cleanPhone,
            'email' => $email,
            'password' => Hash::make($cleanPhone), // Default password is Phone Number
            'role' => 'peserta',
            'account_type' => 'pendaftar',
            'status' => 'active',
        ]);

        // Trigger Auto WhatsApp Notification: Pembuatan Akun Baru
        try {
            WablasNotificationService::sendAutoNotification('account_created', [
                'phone' => $user->phone,
                'nama_peserta' => $user->name,
                'nisn' => $user->phone,
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
            'institution_name' => $user->institution_name,
            'phone' => $user->phone,
            'email' => $user->email,
            'default_password' => $cleanPhone,
            'created_at' => $user->created_at->format('d F Y, H:i').' WIB',
        ]);

        return redirect()->route('register.success');
    }

    public function showRegisterSuccess()
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }

        $slip = session('account_slip') ?? [
            'name' => $user->name,
            'institution_name' => $user->institution_name ?? '-',
            'phone' => $user->phone ?? '-',
            'email' => $user->email,
            'default_password' => $user->phone ?? ($user->nisn ?? 'Sandi Anda'),
            'created_at' => $user->created_at->format('d F Y, H:i').' WIB',
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

        if (! Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Kata sandi saat ini tidak cocok.']);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Kata sandi akun Anda berhasil diperbarui dengan aman!');
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            ActivityLog::record('LOGOUT', 'Pengguna keluar (logout) dari sistem', $user, 'info');
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('info', 'Anda telah berhasil keluar (logout).');
    }

    protected function redirectBasedOnRole(User $user)
    {
        return match ($user->role) {
            'superadmin', 'panitia' => redirect()->route('admin.dashboard'),
            'pic_lomba' => redirect()->route('pic.dashboard'),
            'juri' => redirect()->route('juri.dashboard'),
            default => redirect()->route('peserta.dashboard'),
        };
    }
}
