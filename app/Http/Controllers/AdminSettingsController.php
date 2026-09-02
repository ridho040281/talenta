<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\BroadcastLog;
use App\Models\Competition;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AdminSettingsController extends Controller
{
    /**
     * Pengaturan Aplikasi (General Settings & System Identity)
     */
    public function generalSettings()
    {
        $settings = [
            'app_name' => AppSetting::get('app_name', 'TALENTA'),
            'event_name' => AppSetting::get('event_name', 'Milad ke-57 MTsN 1 Blitar'),
            'institution_name' => AppSetting::get('institution_name', 'MTs Negeri 1 Blitar'),
            'headmaster_name' => AppSetting::get('headmaster_name', 'H. Samsuri, S.Ag., M.Pd.'),
            'headmaster_nip' => AppSetting::get('headmaster_nip', '197505122000031002'),
            'committee_chairman_name' => AppSetting::get('committee_chairman_name', 'Ahmad Fawaid, S.Pd.I.'),
            'committee_chairman_nip' => AppSetting::get('committee_chairman_nip', '198809142014031001'),
            'address' => AppSetting::get('address', 'Jl. Raya Kuningan, Kanigoro, Kab. Blitar, Jawa Timur'),
            'contact_phone' => AppSetting::get('contact_phone', '+62 812-3456-7890'),
            'contact_email' => AppSetting::get('contact_email', 'talenta@mtsn1blitar.sch.id'),
            'school_website' => AppSetting::get('school_website', 'https://mtsn1blitar.sch.id'),
            'event_year' => AppSetting::get('event_year', '2026'),
            
            // Uploaded Images
            'app_logo' => AppSetting::get('app_logo', null),
            'favicon' => AppSetting::get('favicon', null),
            'event_logo' => AppSetting::get('event_logo', null),
            'letterhead_image' => AppSetting::get('letterhead_image', null),
            'kop_lembaga' => AppSetting::get('kop_lembaga', null),
            'kop_kegiatan' => AppSetting::get('kop_kegiatan', null),
            'certificate_header_image' => AppSetting::get('certificate_header_image', null),

            // Landing, Event & Registration Modes
            'allow_individual_reg' => AppSetting::get('allow_individual_reg', '1'),
            'allow_collective_reg' => AppSetting::get('allow_collective_reg', '1'),
            'registration_status' => AppSetting::get('registration_status', 'open'),
            'registration_deadline' => AppSetting::get('registration_deadline', '2026-09-15'),
            'bank_name' => AppSetting::get('bank_name', 'Bank Syariah Indonesia (BSI)'),
            'bank_account_number' => AppSetting::get('bank_account_number', '7123456789'),
            'bank_account_holder' => AppSetting::get('bank_account_holder', 'Panitia TALENTA MTsN 1 Blitar'),
            'announcement_banner' => AppSetting::get('announcement_banner', 'Registrasi TALENTA 2026 resmi dibuka!'),

            // Landing Page Content & Narratives
            'hero_title' => AppSetting::get('hero_title', 'Selamat Datang di TALENTA MTsN 1 Blitar'),
            'hero_subtitle' => AppSetting::get('hero_subtitle', 'Platform manajemen perlombaan MTsN 1 Blitar. Terbuka untuk SD/MI & SMP/MTs sederajat dalam berbagai cabang perlombaan bergengsi.'),
            'how_it_works_tagline' => AppSetting::get('how_it_works_tagline', 'Tahapan Partisipasi'),
            'how_it_works_title' => AppSetting::get('how_it_works_title', 'Alur Mudah Mengikuti TALENTA'),
            'how_it_works_subtitle' => AppSetting::get('how_it_works_subtitle', '4 langkah terstruktur dari pembuatan akun resmi, pendaftaran, undian giliran tampil, hingga bertanding.'),
            'step_1_title' => AppSetting::get('step_1_title', 'Buat Akun'),
            'step_1_desc' => AppSetting::get('step_1_desc', 'Daftar akun resmi untuk mengakses portal dashboard dan mendaftarkan peserta perlombaan.'),
            'step_2_title' => AppSetting::get('step_2_title', 'Pilih Lomba'),
            'step_2_desc' => AppSetting::get('step_2_desc', 'Pilih cabang lomba yang diminati, isi biodata peserta/tim, dan unggah berkas syarat pendukung.'),
            'step_3_title' => AppSetting::get('step_3_title', 'Spin Undian'),
            'step_3_desc' => AppSetting::get('step_3_desc', 'Verifikasi berkas oleh PIC dan pengundian nomor urut tampil secara transparan via Interactive Spin Wheel.'),
            'step_4_title' => AppSetting::get('step_4_title', 'Live Scoring'),
            'step_4_desc' => AppSetting::get('step_4_desc', 'Pelaksanaan lomba, penilaian digital oleh dewan juri, dan skor tampil langsung di Live Scoreboard.'),
            'catalog_tagline' => AppSetting::get('catalog_tagline', 'Live Status Cabang Lomba'),
            'catalog_title' => AppSetting::get('catalog_title', 'Katalog & Kuota Perlombaan'),
            'timeline_tagline' => AppSetting::get('timeline_tagline', 'Agenda & Jadwal Resmi TALENTA 2026'),
            'timeline_title' => AppSetting::get('timeline_title', 'Timeline Rangkaian Kegiatan'),
            'timeline_subtitle' => AppSetting::get('timeline_subtitle', 'Rangkaian tahapan pelaksanaan dari pendaftaran online hingga penganugerahan piala bergilir juara umum.'),
            'cta_tagline' => AppSetting::get('cta_tagline', 'Siap Menjadi Juara?'),
            'cta_title' => AppSetting::get('cta_title', 'Daftarkan Delegasi & Peserta Sekarang'),
            'cta_subtitle' => AppSetting::get('cta_subtitle', 'Jangan lewatkan kesempatan mengukir prestasi gemilang bersama ratusan siswa berprestasi di TALENTA MTsN 1 Blitar 2026.'),
            'cta_button_text' => AppSetting::get('cta_button_text', 'Buat Akun & Registrasi'),
            'sponsor_title' => AppSetting::get('sponsor_title', 'Supported by :'),
            'sponsor_logos' => json_decode(AppSetting::get('sponsor_logos', '[]'), true) ?: [],
            'pamphlet_images' => json_decode(AppSetting::get('pamphlet_images', '[]'), true) ?: [],
            'pamphlet_embed_url' => AppSetting::get('pamphlet_embed_url', ''),
            'footer_about' => AppSetting::get('footer_about', 'Sistem Pendaftaran & Manajemen Perlombaan Terpadu MTsN 1 Blitar. Mengusung arsitektur modern berkecepatan tinggi, sistem undian interaktif spin wheel, dan live scoreboard transparan.'),
        ];

        $systemInfo = [
            'framework' => 'Laravel ' . app()->version(),
            'php_version' => PHP_VERSION,
            'architecture' => 'MVC (Model - View - Controller)',
            'database' => 'MySQL (InnoDB - ' . config('database.connections.mysql.database') . ')',
            'javascript_env' => 'TailwindCSS & AlpineJS',
            'pdf_generator' => 'Dompdf Engine',
            'whatsapp_gateway' => 'Direct API Protocol',
            'server_os' => php_uname('s') . ' ' . php_uname('r'),
            'timezone' => config('app.timezone') . ' (WIB)',
        ];

        return view('admin.settings.general', compact('settings', 'systemInfo'));
    }

    /**
     * Update General Settings
     */
    public function updateGeneralSettings(Request $request)
    {
        $data = $request->validate([
            'app_name' => 'nullable|string|max:255',
            'event_name' => 'nullable|string|max:255',
            'institution_name' => 'nullable|string|max:255',
            'headmaster_name' => 'nullable|string|max:255',
            'headmaster_nip' => 'nullable|string|max:50',
            'committee_chairman_name' => 'nullable|string|max:255',
            'committee_chairman_nip' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'contact_phone' => 'nullable|string|max:50',
            'contact_email' => 'nullable|email|max:100',
            'school_website' => 'nullable|url|max:255',
            'event_year' => 'nullable|string|max:10',
            
            // Files
            'app_logo' => 'nullable|image|mimes:jpeg,png,jpg,svg,webp|max:3072',
            'favicon' => 'nullable|image|mimes:jpeg,png,jpg,svg,webp,ico|max:1024',
            'event_logo' => 'nullable|image|mimes:jpeg,png,jpg,svg,webp|max:3072',
            'letterhead_image' => 'nullable|image|mimes:jpeg,png,jpg,svg,webp|max:4096',
            'certificate_header_image' => 'nullable|image|mimes:jpeg,png,jpg,svg,webp|max:4096',
            'sponsor_logos.*' => 'nullable|image|mimes:jpeg,png,jpg,svg,webp|max:3072',
            'delete_sponsor_logos' => 'nullable|array',
            'delete_sponsor_logos.*' => 'nullable|string',

            // Landing & Event
            'allow_individual_reg' => 'nullable|string|max:5',
            'allow_collective_reg' => 'nullable|string|max:5',
            'registration_status' => 'nullable|in:open,closed,technical_meeting,ongoing,finished',
            'registration_deadline' => 'nullable|date',
            'bank_name' => 'nullable|string|max:100',
            'bank_account_number' => 'nullable|string|max:50',
            'bank_account_holder' => 'nullable|string|max:100',
            'announcement_banner' => 'nullable|string|max:500',

            // Landing Page Content & Narratives
            'hero_title' => 'nullable|string|max:255',
            'hero_subtitle' => 'nullable|string|max:1000',
            'how_it_works_tagline' => 'nullable|string|max:255',
            'how_it_works_title' => 'nullable|string|max:255',
            'how_it_works_subtitle' => 'nullable|string|max:500',
            'step_1_title' => 'nullable|string|max:255',
            'step_1_desc' => 'nullable|string|max:500',
            'step_2_title' => 'nullable|string|max:255',
            'step_2_desc' => 'nullable|string|max:500',
            'step_3_title' => 'nullable|string|max:255',
            'step_3_desc' => 'nullable|string|max:500',
            'step_4_title' => 'nullable|string|max:255',
            'step_4_desc' => 'nullable|string|max:500',
            'catalog_tagline' => 'nullable|string|max:255',
            'catalog_title' => 'nullable|string|max:255',
            'timeline_tagline' => 'nullable|string|max:255',
            'timeline_title' => 'nullable|string|max:255',
            'timeline_subtitle' => 'nullable|string|max:500',
            'cta_tagline' => 'nullable|string|max:255',
            'cta_title' => 'nullable|string|max:255',
            'cta_subtitle' => 'nullable|string|max:500',
            'cta_button_text' => 'nullable|string|max:100',
            'sponsor_title' => 'nullable|string|max:255',
            'pamphlet_embed_url' => 'nullable|string|max:5000',
            'footer_about' => 'nullable|string|max:1000',
        ]);

        $fileKeys = ['app_logo', 'favicon', 'event_logo', 'letterhead_image', 'kop_lembaga', 'kop_kegiatan', 'certificate_header_image'];
        foreach ($fileKeys as $fileKey) {
            if ($request->hasFile($fileKey)) {
                $old = AppSetting::get($fileKey);
                if ($old && Storage::disk('public')->exists($old)) {
                    Storage::disk('public')->delete($old);
                }
                $path = $request->file($fileKey)->store('settings', 'public');

                // Auto crop white/transparent borders for Kop & Letterhead images so they fit flush to width
                if (in_array($fileKey, ['kop_lembaga', 'kop_kegiatan', 'letterhead_image', 'certificate_header_image'])) {
                    $fullPath = storage_path('app/public/' . $path);
                    self::autoCropImageMargins($fullPath);
                }

                AppSetting::set($fileKey, $path);
            }
        }

        // Handle Image Deletions if requested
        $deleteKeys = ['delete_app_logo' => 'app_logo', 'delete_favicon' => 'favicon', 'delete_event_logo' => 'event_logo', 'delete_kop_lembaga' => 'kop_lembaga', 'delete_kop_kegiatan' => 'kop_kegiatan', 'delete_letterhead_image' => 'letterhead_image'];
        foreach ($deleteKeys as $deleteInput => $settingKey) {
            if ($request->boolean($deleteInput)) {
                $old = AppSetting::get($settingKey);
                if ($old && Storage::disk('public')->exists($old)) {
                    Storage::disk('public')->delete($old);
                }
                AppSetting::set($settingKey, null);
            }
        }

        // Handle Sponsor Logos Uploads & Deletions
        $currentSponsorLogos = json_decode(AppSetting::get('sponsor_logos', '[]'), true) ?: [];

        if ($request->has('delete_sponsor_logos') && is_array($request->delete_sponsor_logos)) {
            foreach ($request->delete_sponsor_logos as $toDelete) {
                if (Storage::disk('public')->exists($toDelete)) {
                    Storage::disk('public')->delete($toDelete);
                }
                $currentSponsorLogos = array_values(array_filter($currentSponsorLogos, fn($item) => $item !== $toDelete));
            }
        }

        if ($request->hasFile('sponsor_logos')) {
            foreach ($request->file('sponsor_logos') as $sponsorFile) {
                if ($sponsorFile->isValid()) {
                    $path = $sponsorFile->store('sponsors', 'public');
                    $currentSponsorLogos[] = $path;
                }
            }
        }

        AppSetting::set('sponsor_logos', json_encode(array_values($currentSponsorLogos)));

        // Handle Pamphlet Images Uploads & Deletions
        $currentPamphletImages = json_decode(AppSetting::get('pamphlet_images', '[]'), true) ?: [];

        if ($request->has('delete_pamphlet_images') && is_array($request->delete_pamphlet_images)) {
            foreach ($request->delete_pamphlet_images as $toDelete) {
                if (Storage::disk('public')->exists($toDelete)) {
                    Storage::disk('public')->delete($toDelete);
                }
                $currentPamphletImages = array_values(array_filter($currentPamphletImages, fn($item) => $item !== $toDelete));
            }
        }

        if ($request->hasFile('pamphlet_images')) {
            foreach ($request->file('pamphlet_images') as $pamphletFile) {
                if ($pamphletFile->isValid()) {
                    $path = $pamphletFile->store('pamphlets', 'public');
                    $currentPamphletImages[] = $path;
                }
            }
        }

        AppSetting::set('pamphlet_images', json_encode(array_values($currentPamphletImages)));

        $textFields = [
            'app_name', 'institution_name', 'headmaster_name', 'headmaster_nip',
            'committee_chairman_name', 'committee_chairman_nip', 'address',
            'contact_phone', 'contact_email', 'school_website', 'event_year',
            'allow_individual_reg', 'allow_collective_reg',
            'registration_status', 'registration_deadline', 'bank_name',
            'bank_account_number', 'bank_account_holder', 'announcement_banner',
            'hero_title', 'hero_subtitle', 'how_it_works_tagline', 'how_it_works_title',
            'how_it_works_subtitle', 'step_1_title', 'step_1_desc', 'step_2_title',
            'step_2_desc', 'step_3_title', 'step_3_desc', 'step_4_title', 'step_4_desc',
            'catalog_tagline', 'catalog_title', 'timeline_tagline', 'timeline_title',
            'timeline_subtitle', 'cta_tagline', 'cta_title', 'cta_subtitle',
            'cta_button_text', 'sponsor_title', 'pamphlet_embed_url', 'footer_about'
        ];

        foreach ($textFields as $field) {
            if (array_key_exists($field, $data)) {
                AppSetting::set($field, $data[$field]);
            }
        }

        return redirect()->back()->with('success', 'Pengaturan aplikasi dan konten landing page berhasil disimpan.');
    }


    /**
     * WhatsApp Blast Management
     */
    public function whatsappBlast()
    {
        $competitions = Competition::orderBy('name')->get();
        $broadcastLogs = BroadcastLog::with('sender')->latest()->paginate(10);
        
        $stats = [
            'total_recipients' => Registration::count(),
            'verified_recipients' => Registration::where('status', 'verified')->count(),
            'pending_recipients' => Registration::where('status', 'pending')->count(),
            'total_broadcasts' => BroadcastLog::count(),
        ];

        $wablasCredentials = [
            'api_host' => AppSetting::get('wablas_api_host', 'https://jogja.wablas.com/'),
            'token' => AppSetting::get('wablas_api_token', ''),
            'secret_key' => AppSetting::get('wablas_secret_key', ''),
        ];

        // 1. Peserta Contacts
        $participantContacts = collect();
        $registrations = Registration::with(['competition', 'user', 'members'])->latest()->get();
        foreach ($registrations as $reg) {
            $firstMember = $reg->members->first();
            $purePhone = $reg->official_phone ?: ($reg->user?->phone ?: $firstMember?->phone);
            if (empty($purePhone)) continue;

            $cleanPhone = preg_replace('/[^0-9]/', '', $purePhone);
            if (str_starts_with($cleanPhone, '0')) $cleanPhone = '62' . substr($cleanPhone, 1);
            elseif (str_starts_with($cleanPhone, '8')) $cleanPhone = '628' . substr($cleanPhone, 1);

            $participantContacts->push([
                'id' => 'reg_' . $reg->id,
                'name' => $reg->display_name,
                'subtitle' => ($reg->institution_name ?: 'Mandiri') . ' • ' . ($reg->competition->name ?? 'Lomba'),
                'institution' => $reg->institution_name ?: '-',
                'phone' => $cleanPhone,
                'display_phone' => $purePhone,
                'type' => 'peserta',
                'status' => $reg->status,
            ]);
        }

        $userParticipants = User::where('role', 'peserta')->whereNotNull('phone')->get();
        foreach ($userParticipants as $u) {
            $cleanPhone = preg_replace('/[^0-9]/', '', $u->phone);
            if (str_starts_with($cleanPhone, '0')) $cleanPhone = '62' . substr($cleanPhone, 1);
            elseif (str_starts_with($cleanPhone, '8')) $cleanPhone = '628' . substr($cleanPhone, 1);

            $participantContacts->push([
                'id' => 'user_' . $u->id,
                'name' => $u->name,
                'subtitle' => ($u->institution_name ?: 'Akun Peserta') . ' • Terdaftar Akun',
                'institution' => $u->institution_name ?: '-',
                'phone' => $cleanPhone,
                'display_phone' => $u->phone,
                'type' => 'peserta',
                'status' => 'verified',
            ]);
        }
        $participantContacts = $participantContacts->unique('phone')->values();

        // 2. Panitia Contacts (Superadmin, PIC, Juri)
        $committeeUsers = User::whereIn('role', ['superadmin', 'pic_lomba', 'juri'])->whereNotNull('phone')->orderBy('role')->orderBy('name')->get();
        $committeeContacts = $committeeUsers->map(function ($u) {
            $cleanPhone = preg_replace('/[^0-9]/', '', $u->phone);
            if (str_starts_with($cleanPhone, '0')) $cleanPhone = '62' . substr($cleanPhone, 1);
            elseif (str_starts_with($cleanPhone, '8')) $cleanPhone = '628' . substr($cleanPhone, 1);

            $roleLabel = match($u->role) {
                'superadmin' => 'Super Administrator',
                'pic_lomba' => 'Koordinator / PIC Lomba',
                'juri' => 'Dewan Juri / Wasit',
                default => 'Panitia',
            };

            return [
                'id' => 'panitia_' . $u->id,
                'name' => $u->name,
                'subtitle' => $roleLabel . ($u->institution_name ? ' • ' . $u->institution_name : ''),
                'institution' => $u->institution_name ?: 'Panitia TALENTA',
                'phone' => $cleanPhone,
                'display_phone' => $u->phone,
                'type' => 'panitia',
                'role' => $u->role,
                'role_label' => $roleLabel,
            ];
        })->unique('phone')->values();

        // 3. Publikasi & Sekolah Contacts
        $publicationContacts = collect();
        $distinctInstitutions = User::whereNotNull('institution_name')->whereNotNull('phone')->get();
        foreach ($distinctInstitutions as $inst) {
            $cleanPhone = preg_replace('/[^0-9]/', '', $inst->phone);
            if (str_starts_with($cleanPhone, '0')) $cleanPhone = '62' . substr($cleanPhone, 1);
            elseif (str_starts_with($cleanPhone, '8')) $cleanPhone = '628' . substr($cleanPhone, 1);

            $publicationContacts->push([
                'id' => 'pub_' . $inst->id,
                'name' => $inst->institution_name,
                'subtitle' => 'Humas / Delegasi: ' . $inst->name,
                'institution' => $inst->institution_name,
                'phone' => $cleanPhone,
                'display_phone' => $inst->phone,
                'type' => 'publikasi',
            ]);
        }
        $publicationContacts = $publicationContacts->unique('phone')->values();

        return view('admin.settings.whatsapp-blast', compact(
            'competitions',
            'broadcastLogs',
            'stats',
            'wablasCredentials',
            'participantContacts',
            'committeeContacts',
            'publicationContacts'
        ));
    }

    /**
     * Save Wablas API Credentials
     */
    public function saveWablasCredentials(Request $request)
    {
        $request->validate([
            'api_host' => 'required|string',
            'token' => 'required|string',
            'secret_key' => 'nullable|string',
        ]);

        $host = trim($request->api_host);
        if (!preg_match('#^https?://#i', $host)) {
            $host = 'https://' . $host;
        }
        $host = rtrim($host, '/');

        AppSetting::set('wablas_api_host', $host);
        AppSetting::set('wablas_api_token', trim($request->token));
        AppSetting::set('wablas_secret_key', trim($request->secret_key ?? ''));

        return redirect()->back()->with('success', 'Kredensial API Wablas berhasil disimpan.');
    }

    /**
     * Check Wablas API Connection Status (Async API for Signal Indicator)
     */
    public function checkWablasStatus(Request $request)
    {
        $wablasHost = rtrim(AppSetting::get('wablas_api_host', 'https://jogja.wablas.com'), '/');
        $wablasToken = trim(AppSetting::get('wablas_api_token', ''));
        $wablasSecretKey = trim(AppSetting::get('wablas_secret_key', ''));

        if (!$wablasToken) {
            return response()->json([
                'connected' => false,
                'status' => 'unconfigured',
                'message' => 'Token Belum Diisi',
                'sender' => null,
                'quota' => '-',
            ]);
        }

        $authHeader = $wablasSecretKey ? ($wablasToken . '.' . $wablasSecretKey) : $wablasToken;

        try {
            // Priority 1: Query param ?token= (Official Wablas standard)
            $response = \Illuminate\Support\Facades\Http::withoutVerifying()
                ->timeout(8)
                ->withHeaders([
                    'Authorization' => $authHeader,
                ])
                ->get("{$wablasHost}/api/device/info?token={$wablasToken}");

            // Priority 2: Header Authorization only
            if (!$response->successful() || $response->json('status') === false) {
                $response = \Illuminate\Support\Facades\Http::withoutVerifying()
                    ->timeout(8)
                    ->withHeaders([
                        'Authorization' => $authHeader,
                    ])
                    ->get("{$wablasHost}/api/device/info");
            }

            // Priority 3: Fallback token only
            if (!$response->successful() || $response->json('status') === false) {
                $response = \Illuminate\Support\Facades\Http::withoutVerifying()
                    ->timeout(8)
                    ->get("{$wablasHost}/api/device/info?token={$wablasToken}");
            }

            if ($response->successful() && $response->json('status') !== false) {
                $body = $response->json();
                $data = $body['data'] ?? [];
                $rawStatus = strtolower((string)($data['status'] ?? ''));
                
                $isConnected = in_array($rawStatus, ['connected', 'online', 'active']) || (!empty($data['sender']) && $rawStatus !== 'disconnected') || (!empty($data['whatsapp_number']) && $rawStatus !== 'disconnected');
                $sender = $data['sender'] ?? ($data['name'] ?? ($data['whatsapp_name'] ?? ($data['whatsapp_number'] ?? 'Device Terhubung')));
                $quota = $data['quota'] ?? ($data['remaining_quota'] ?? '-');

                $message = $isConnected ? 'Terhubung' : 'Device WhatsApp di Dashboard Wablas belum scan QR / Disconnected';

                return response()->json([
                    'connected' => $isConnected,
                    'status' => $isConnected ? 'connected' : 'disconnected',
                    'message' => $message,
                    'sender' => $sender,
                    'quota' => $quota,
                    'raw_status' => $rawStatus,
                ]);
            } else {
                $err = $response->json('message') ?? ('Status HTTP ' . $response->status());
                return response()->json([
                    'connected' => false,
                    'status' => 'disconnected',
                    'message' => 'Wablas: ' . $err,
                    'sender' => null,
                    'quota' => '-',
                ]);
            }
        } catch (\Throwable $e) {
            return response()->json([
                'connected' => false,
                'status' => 'error',
                'message' => 'Gagal koneksi: ' . $e->getMessage(),
                'sender' => null,
                'quota' => '-',
            ]);
        }
    }

    /**
     * Test Wablas API Connection
     */
    public function testWablasConnection(Request $request)
    {
        $wablasHost = rtrim(AppSetting::get('wablas_api_host', 'https://jogja.wablas.com'), '/');
        $wablasToken = trim(AppSetting::get('wablas_api_token', ''));
        $wablasSecretKey = trim(AppSetting::get('wablas_secret_key', ''));

        if (!$wablasToken) {
            return redirect()->back()->with('error', 'Token API Wablas belum diisi.');
        }

        $authHeader = $wablasSecretKey ? ($wablasToken . '.' . $wablasSecretKey) : $wablasToken;

        try {
            $response = \Illuminate\Support\Facades\Http::withoutVerifying()
                ->timeout(10)
                ->withHeaders([
                    'Authorization' => $authHeader,
                ])
                ->get("{$wablasHost}/api/device/info?token={$wablasToken}");

            if (!$response->successful() || $response->json('status') === false) {
                $response = \Illuminate\Support\Facades\Http::withoutVerifying()
                    ->timeout(10)
                    ->get("{$wablasHost}/api/device/info?token={$wablasToken}");
            }

            if ($response->successful() && $response->json('status') !== false) {
                $body = $response->json();
                $sender = $body['data']['sender'] ?? ($body['data']['name'] ?? ($body['data']['whatsapp_number'] ?? 'Device Terhubung'));
                $quota = $body['data']['quota'] ?? '-';
                return redirect()->back()->with('success', "Koneksi API Wablas Berhasil! Device: {$sender} (Sisa Kuota: {$quota})");
            } else {
                $err = $response->json('message') ?? ('Status HTTP ' . $response->status());
                return redirect()->back()->with('error', 'Respon dari Wablas: ' . $err);
            }
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Gagal menghubungi server Wablas: ' . $e->getMessage());
        }
    }

    /**
     * Download WhatsApp Blast Excel Template
     */
    public function downloadWhatsappTemplate()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data WhatsApp Blast');

        // Header
        $headers = ['Nomor WhatsApp', 'Nama Penerima', 'Asal Sekolah / Instansi'];
        $sheet->fromArray([$headers], null, 'A1');

        // Sample Rows
        $sampleData = [
            ['081234567890', 'Ahmad Pratama', 'SDN 1 Wonodadi Blitar'],
            ['085712345678', 'Siti Nurhaliza', 'MI Miftahul Huda Blitar'],
            ['089988776655', 'Budi Santoso', 'SD Plus Rahmat'],
        ];
        $sheet->fromArray($sampleData, null, 'A2');

        // Auto width
        foreach (range('A', 'C') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Style headers
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '10B981'],
            ],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ];
        $sheet->getStyle('A1:C1')->applyFromArray($headerStyle);

        // Format phone column as text
        $sheet->getStyle('A2:A100')->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);

        $fileName = 'Template_WhatsApp_Blast_TALENTA.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Send / Generate WhatsApp Blast
     */
    public function sendWhatsappBlast(Request $request)
    {
        $request->validate([
            'target_audience' => 'required|string|in:all,verified,pending,competition,panitia,publikasi,manual,excel',
            'competition_id' => 'nullable|exists:competitions,id',
            'manual_numbers' => 'nullable|string',
            'excel_file' => 'nullable|file|mimes:xlsx,xls,csv|max:5120',
            'message' => 'required|string|min:5',
        ]);

        $recipientsList = collect();
        $targetLabel = '';
        $compName = null;

        if ($request->target_audience === 'manual') {
            if (empty(trim($request->manual_numbers ?? ''))) {
                return redirect()->back()->with('error', 'Silakan masukkan daftar nomor WhatsApp pada input manual.');
            }

            $lines = preg_split('/[\r\n]+/', trim($request->manual_numbers));
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;

                $parts = preg_split('/[,;\t]+/', $line);
                $rawPhone = trim($parts[0] ?? '');
                $rawName = isset($parts[1]) ? trim($parts[1]) : 'Bapak/Ibu Peserta';
                $rawSchool = isset($parts[2]) ? trim($parts[2]) : '-';

                $cleanPhone = preg_replace('/[^0-9]/', '', $rawPhone);
                if (empty($cleanPhone)) continue;

                if (str_starts_with($cleanPhone, '0')) {
                    $cleanPhone = '62' . substr($cleanPhone, 1);
                } elseif (str_starts_with($cleanPhone, '8')) {
                    $cleanPhone = '628' . substr($cleanPhone, 1);
                }

                $recipientsList->push([
                    'phone' => $cleanPhone,
                    'name' => $rawName ?: 'Bapak/Ibu Peserta',
                    'school' => $rawSchool ?: '-',
                    'competition' => 'TALENTA 2026',
                    'no_peserta' => '-',
                    'link_scoreboard' => url('/'),
                ]);
            }

            $recipientsList = $recipientsList->unique('phone');
            $targetLabel = 'Input Manual (' . $recipientsList->count() . ' Nomor)';
        } elseif ($request->target_audience === 'excel') {
            if (!$request->hasFile('excel_file') || !$request->file('excel_file')->isValid()) {
                return redirect()->back()->with('error', 'Silakan unggah file Excel / CSV yang valid.');
            }

            try {
                $file = $request->file('excel_file');
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getRealPath());
                $sheet = $spreadsheet->getActiveSheet();
                $rows = $sheet->toArray(null, true, true, true);

                $isFirstRow = true;
                foreach ($rows as $row) {
                    if ($isFirstRow) {
                        $isFirstRow = false;
                        $firstCol = strtolower(trim((string)($row['A'] ?? '')));
                        if (str_contains($firstCol, 'nomor') || str_contains($firstCol, 'phone') || str_contains($firstCol, 'wa')) {
                            continue;
                        }
                    }

                    $rawPhone = trim((string)($row['A'] ?? ''));
                    $rawName = trim((string)($row['B'] ?? ''));
                    $rawSchool = trim((string)($row['C'] ?? ''));

                    if (empty($rawPhone)) continue;

                    $cleanPhone = preg_replace('/[^0-9]/', '', $rawPhone);
                    if (empty($cleanPhone)) continue;

                    if (str_starts_with($cleanPhone, '0')) {
                        $cleanPhone = '62' . substr($cleanPhone, 1);
                    } elseif (str_starts_with($cleanPhone, '8')) {
                        $cleanPhone = '628' . substr($cleanPhone, 1);
                    }

                    $recipientsList->push([
                        'phone' => $cleanPhone,
                        'name' => $rawName ?: 'Bapak/Ibu Peserta',
                        'school' => $rawSchool ?: '-',
                        'competition' => 'TALENTA 2026',
                        'no_peserta' => '-',
                        'link_scoreboard' => url('/'),
                    ]);
                }

                $recipientsList = $recipientsList->unique('phone');
                $targetLabel = 'Import Excel: ' . $file->getClientOriginalName() . ' (' . $recipientsList->count() . ' Nomor)';
            } catch (\Throwable $e) {
                return redirect()->back()->with('error', 'Gagal membaca file Excel: ' . $e->getMessage());
            }
        } elseif ($request->target_audience === 'panitia') {
            $committeeUsers = User::whereIn('role', ['superadmin', 'pic_lomba', 'juri'])->whereNotNull('phone')->get();
            foreach ($committeeUsers as $u) {
                $cleanPhone = preg_replace('/[^0-9]/', '', $u->phone);
                if (str_starts_with($cleanPhone, '0')) $cleanPhone = '62' . substr($cleanPhone, 1);
                elseif (str_starts_with($cleanPhone, '8')) $cleanPhone = '628' . substr($cleanPhone, 1);

                $recipientsList->push([
                    'phone' => $cleanPhone,
                    'name' => $u->name,
                    'school' => $u->institution_name ?: 'Panitia TALENTA',
                    'competition' => 'Panitia TALENTA 2026',
                    'no_peserta' => '-',
                    'link_scoreboard' => url('/'),
                ]);
            }
            $recipientsList = $recipientsList->unique('phone');
            $targetLabel = 'Semua Panitia & Juri (' . $recipientsList->count() . ' Kontak)';
        } elseif ($request->target_audience === 'publikasi') {
            $instUsers = User::whereNotNull('institution_name')->whereNotNull('phone')->get();
            foreach ($instUsers as $u) {
                $cleanPhone = preg_replace('/[^0-9]/', '', $u->phone);
                if (str_starts_with($cleanPhone, '0')) $cleanPhone = '62' . substr($cleanPhone, 1);
                elseif (str_starts_with($cleanPhone, '8')) $cleanPhone = '628' . substr($cleanPhone, 1);

                $recipientsList->push([
                    'phone' => $cleanPhone,
                    'name' => $u->institution_name,
                    'school' => $u->institution_name,
                    'competition' => 'TALENTA 2026',
                    'no_peserta' => '-',
                    'link_scoreboard' => url('/'),
                ]);
            }
            $recipientsList = $recipientsList->unique('phone');
            $targetLabel = 'Publikasi & Humas Sekolah (' . $recipientsList->count() . ' Kontak)';
        } else {
            $query = Registration::with(['competition', 'user', 'members']);

            if ($request->target_audience === 'verified') {
                $query->where('status', 'verified');
                $targetLabel = 'Pendaftar Sah (Verified)';
            } elseif ($request->target_audience === 'pending') {
                $query->where('status', 'pending');
                $targetLabel = 'Pendaftar Menunggu Verifikasi (Pending)';
            } elseif ($request->target_audience === 'competition' && $request->competition_id) {
                $query->where('competition_id', $request->competition_id);
                $comp = Competition::find($request->competition_id);
                $compName = $comp ? $comp->name : null;
                $targetLabel = 'Lomba: ' . ($compName ?? 'Spesifik');
            } else {
                $targetLabel = 'Semua Pendaftar Terdata';
            }

            $regs = $query->get();
            foreach ($regs as $reg) {
                $firstMember = $reg->members->first();
                $purePhone = $reg->official_phone ?: ($reg->user?->phone ?: $firstMember?->phone);
                
                if (empty($purePhone)) continue;

                $phone = preg_replace('/[^0-9]/', '', $purePhone);
                if (str_starts_with($phone, '0')) {
                    $phone = '62' . substr($phone, 1);
                } elseif (str_starts_with($phone, '8')) {
                    $phone = '628' . substr($phone, 1);
                }

                $recipientsList->push([
                    'phone' => $phone,
                    'name' => $reg->display_name ?: 'Bapak/Ibu Peserta',
                    'school' => $reg->institution_name ?: '-',
                    'competition' => $reg->competition->name ?? 'TALENTA 2026',
                    'no_peserta' => $reg->participant_number ?? $reg->registration_code,
                    'link_scoreboard' => url('/live-scoreboard/' . ($reg->competition->slug ?? '')),
                ]);
            }

            $recipientsList = $recipientsList->unique('phone');
        }

        if ($recipientsList->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada nomor penerima yang valid untuk dikirim.');
        }

        $wablasHost = rtrim(AppSetting::get('wablas_api_host', 'https://jogja.wablas.com/'), '/');
        $wablasToken = AppSetting::get('wablas_api_token', '');
        $wablasSecretKey = AppSetting::get('wablas_secret_key', '');
        $authHeader = $wablasSecretKey ? ($wablasToken . '.' . $wablasSecretKey) : $wablasToken;

        $sentSuccessCount = 0;
        $failedCount = 0;

        if (!empty($wablasToken)) {
            foreach ($recipientsList as $recipient) {
                $phone = $recipient['phone'];

                $msg = $request->message;
                $msg = str_replace('{nama_peserta}', $recipient['name'], $msg);
                $msg = str_replace('{nama_sekolah}', $recipient['school'], $msg);
                $msg = str_replace('{cabang_lomba}', $recipient['competition'], $msg);
                $msg = str_replace('{no_peserta}', $recipient['no_peserta'], $msg);
                $msg = str_replace('{link_scoreboard}', $recipient['link_scoreboard'], $msg);

                try {
                    $res = \Illuminate\Support\Facades\Http::withoutVerifying()
                        ->timeout(10)
                        ->withHeaders([
                            'Authorization' => $authHeader,
                        ])
                        ->post("{$wablasHost}/api/send-message", [
                            'phone' => $phone,
                            'message' => $msg,
                            'token' => $wablasToken,
                            'secret' => $wablasSecretKey,
                        ]);

                    if ($res->successful() && $res->json('status') !== false) {
                        $sentSuccessCount++;
                    } else {
                        $failedCount++;
                    }
                } catch (\Throwable $e) {
                    $failedCount++;
                }
            }
        }

        // Save broadcast log
        BroadcastLog::create([
            'sender_id' => Auth::id(),
            'target_audience' => $request->target_audience,
            'target_competition' => $compName ?: $targetLabel,
            'recipients_count' => $recipientsList->count(),
            'message' => $request->message,
            'status' => (!empty($wablasToken) && $sentSuccessCount > 0) ? 'sent' : 'logged',
        ]);

        $feedbackMsg = 'Pesan WhatsApp Blast berhasil diproses untuk ' . $recipientsList->count() . ' kontak penerima.';
        if (!empty($wablasToken)) {
            $feedbackMsg .= " ({$sentSuccessCount} pesan terkirim via Wablas" . ($failedCount > 0 ? ", {$failedCount} gagal/tidak aktif" : "") . ").";
        } else {
            $feedbackMsg .= ' (Mode Simulasi Log: Token Wablas belum dihubungkan).';
        }

        return redirect()->back()->with('success', $feedbackMsg);
    }

    /**
     * Changelog & System Updates History
     */
    public function changelog()
    {
        $changelogs = [
            [
                'version' => 'v1.5.0',
                'date' => '26 Agustus 2026',
                'title' => 'Identitas Instansi, Headings Surat & Multi-Upload Logo',
                'badge' => 'Major Update',
                'badge_color' => 'bg-cyan-500/20 text-cyan-400 border-cyan-500/30',
                'changes' => [
                    'Penerapan UI Dark Executive Dashboard untuk pengaturan identitas instansi dan kop surat.',
                    'Multi-upload logo: Logo Aplikasi & Navbar, Favicon Browser, Logo Kegiatan TALENTA, dan Kop Surat Resmi.',
                    'Manajemen pejabat instansi: Kepala Madrasah, NIP, Ketua Panitia, NIP, serta alamat lengkap lembaga.',
                ],
            ],
            [
                'version' => 'v1.4.0',
                'date' => '26 Agustus 2026',
                'title' => 'Pengaturan Aplikasi, WhatsApp Blast & Info Sistem',
                'badge' => 'Release',
                'badge_color' => 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30',
                'changes' => [
                    'Modul Pengaturan Umum Aplikasi, WhatsApp Blast massal terstruktur, dan info spesifikasi engine.',
                ],
            ],
            [
                'version' => 'v1.3.0',
                'date' => '26 Agustus 2026',
                'title' => 'Futuristic Crypto-NextJS Pure Tailwind Theme',
                'badge' => 'UI Redesign',
                'badge_color' => 'bg-blue-500/20 text-blue-400 border-blue-500/30',
                'changes' => [
                    'Desain visual Dark Space Glassmorphism murni Tailwind CSS.',
                ],
            ],
            [
                'version' => 'v1.2.0',
                'date' => '26 Agustus 2026',
                'title' => 'Interactive Spin Wheel & Live Scoreboard TV',
                'badge' => 'Core Feature',
                'badge_color' => 'bg-amber-500/20 text-amber-400 border-amber-500/30',
                'changes' => [
                    'Interactive HTML5 Canvas Spin Wheel dan Live Scoreboard Display.',
                ],
            ],
            [
                'version' => 'v1.0.0',
                'date' => '26 Agustus 2026',
                'title' => 'Inisialisasi Sistem TALENTA MTsN 1 Blitar',
                'badge' => 'Initial Build',
                'badge_color' => 'bg-purple-500/20 text-purple-400 border-purple-500/30',
                'changes' => [
                    'Manajemen pendaftaran multi-kategori, sistem verifikasi berkas, dan kartu peserta.',
                ],
            ],
        ];

        return view('admin.settings.changelog', compact('changelogs'));
    }

    /**
     * Info Aplikasi & System Health
     */
    public function appInfo()
    {
        $systemInfo = [
            'app_name' => AppSetting::get('app_name', 'TALENTA 2026 - MTsN 1 BLITAR'),
            'institution' => AppSetting::get('institution_name', 'MTs Negeri 1 Blitar'),
            'institution_name' => AppSetting::get('institution_name', 'MTs Negeri 1 Blitar'),
            'headmaster' => AppSetting::get('headmaster_name', 'H. Samsuri, S.Ag., M.Pd.') . ' (NIP: ' . AppSetting::get('headmaster_nip', '-') . ')',
            'committee_chairman' => AppSetting::get('committee_chairman_name', 'Ahmad Fawaid, S.Pd.I.') . ' (NIP: ' . AppSetting::get('committee_chairman_nip', '-') . ')',
            'app_version' => '1.5.0 (Stable)',
            'laravel_version' => app()->version(),
            'php_version' => PHP_VERSION,
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'PHP ' . PHP_VERSION . ' Development Server',
            'database_engine' => 'MySQL (InnoDB)',
            'db_name' => config('database.connections.mysql.database'),
            'environment' => app()->environment(),
            'timezone' => config('app.timezone'),
            'server_time' => now()->translatedFormat('d F Y, H:i:s T'),
            'debug_mode' => config('app.debug') ? 'Aktif (Development)' : 'Nonaktif (Production)',
            'storage_status' => is_dir(public_path('storage')) ? 'Tersambung (Junction / Symlink OK)' : 'Belum Tersambung',
            'developer' => 'Tim IT & Publikasi TALENTA MTsN 1 Blitar',
        ];

        return view('admin.settings.app-info', compact('systemInfo'));
    }

    /**
     * Auto trim transparent and near-white outer margins from uploaded Kop/Letterhead images
     */
    public static function autoCropImageMargins(string $filePath): void
    {
        if (!file_exists($filePath) || !extension_loaded('gd')) {
            return;
        }

        try {
            $info = @getimagesize($filePath);
            if (!$info) return;

            $mime = $info['mime'];
            $im = null;
            if ($mime === 'image/png') {
                $im = @imagecreatefrompng($filePath);
            } elseif ($mime === 'image/jpeg') {
                $im = @imagecreatefromjpeg($filePath);
            } elseif ($mime === 'image/webp' && function_exists('imagecreatefromwebp')) {
                $im = @imagecreatefromwebp($filePath);
            }

            if (!$im) return;

            $w = imagesx($im);
            $h = imagesy($im);

            $min_x = $w; $max_x = 0; $min_y = $h; $max_y = 0;

            for ($y = 0; $y < $h; $y++) {
                for ($x = 0; $x < $w; $x++) {
                    $rgba = imagecolorat($im, $x, $y);
                    $colors = imagecolorsforindex($im, $rgba);
                    if ($colors['alpha'] < 120 && !($colors['red'] > 242 && $colors['green'] > 242 && $colors['blue'] > 242)) {
                        if ($x < $min_x) $min_x = $x;
                        if ($x > $max_x) $max_x = $x;
                        if ($y < $min_y) $min_y = $y;
                        if ($y > $max_y) $max_y = $y;
                    }
                }
            }

            if ($min_x < $max_x && $min_y < $max_y) {
                $padding_y = 4;
                $crop_x = max(0, $min_x);
                $crop_y = max(0, $min_y - $padding_y);
                $crop_w = min($w - $crop_x, ($max_x - $min_x));
                $crop_h = min($h - $crop_y, ($max_y - $min_y) + ($padding_y * 2));

                $cropped = imagecrop($im, ['x' => $crop_x, 'y' => $crop_y, 'width' => $crop_w, 'height' => $crop_h]);
                if ($cropped !== false) {
                    if ($mime === 'image/png') {
                        imagealphablending($cropped, false);
                        imagesavealpha($cropped, true);
                        imagepng($cropped, $filePath);
                    } elseif ($mime === 'image/jpeg') {
                        imagejpeg($cropped, $filePath, 95);
                    } elseif ($mime === 'image/webp') {
                        imagewebp($cropped, $filePath, 95);
                    }
                    imagedestroy($cropped);
                }
            }
            imagedestroy($im);
        } catch (\Throwable $e) {
            // Ignore if image cropping fails gracefully
        }
    }
}
