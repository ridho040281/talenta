<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\BroadcastLog;
use App\Models\WhatsappTemplate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WablasNotificationService
{
    /**
     * Send an automated notification triggered by system events
     *
     * @param string $templateCode (e.g. 'account_created', 'registration_submitted', 'registration_verified')
     * @param array $data Placeholders data (phone, nama_peserta, nisn, nama_sekolah, cabang_lomba, no_peserta, kode_pendaftaran, etc.)
     * @return bool
     */
    public static function sendAutoNotification(string $templateCode, array $data): bool
    {
        try {
            // 1. Get Template
            $template = WhatsappTemplate::where('code', $templateCode)->first();
            if (!$template || !$template->is_active) {
                return false; // Auto-trigger is disabled or template not found
            }

            // 2. Get API Credentials
            $wablasHost = rtrim(AppSetting::get('wablas_api_host', 'https://jogja.wablas.com'), '/');
            $wablasToken = trim(AppSetting::get('wablas_api_token', ''));
            $wablasSecretKey = trim(AppSetting::get('wablas_secret_key', ''));

            if (empty($wablasToken)) {
                return false; // Gateway not configured
            }

            // 3. Format Phone Number
            $rawPhone = $data['phone'] ?? '';
            if (empty($rawPhone)) {
                return false;
            }

            $cleanPhone = preg_replace('/[^0-9]/', '', $rawPhone);
            if (str_starts_with($cleanPhone, '0')) {
                $cleanPhone = '62' . substr($cleanPhone, 1);
            } elseif (str_starts_with($cleanPhone, '8')) {
                $cleanPhone = '628' . substr($cleanPhone, 1);
            }

            // 4. Build message with dynamic placeholders
            $msg = $template->message;
            $appName = AppSetting::get('app_name', 'TALENTA');
            $eventName = AppSetting::get('event_name', 'Milad ke-57 MTsN 1 Blitar');
            $institutionName = AppSetting::get('institution_name', 'MTsN 1 Blitar');

            $placeholders = [
                '{nama_peserta}' => $data['nama_peserta'] ?? ($data['nama_pendaftar'] ?? 'Bapak/Ibu Peserta'),
                '{nama_pendaftar}' => $data['nama_pendaftar'] ?? ($data['nama_peserta'] ?? 'Bapak/Ibu Pendaftar'),
                '{nisn}' => $data['nisn'] ?? '-',
                '{nama_sekolah}' => $data['nama_sekolah'] ?? ($data['nama_instansi'] ?? $institutionName),
                '{nama_instansi}' => $data['nama_instansi'] ?? ($data['nama_sekolah'] ?? $institutionName),
                '{cabang_lomba}' => $data['cabang_lomba'] ?? 'TALENTA 2026',
                '{kategori_lomba}' => $data['kategori_lomba'] ?? ($data['cabang_lomba'] ?? '-'),
                '{no_peserta}' => $data['no_peserta'] ?? ($data['kode_pendaftaran'] ?? '-'),
                '{kode_pendaftaran}' => $data['kode_pendaftaran'] ?? ($data['no_peserta'] ?? '-'),
                '{nomor_undian}' => $data['nomor_undian'] ?? ($data['draw_number'] ?? '-'),
                '{nominal_biaya}' => !empty($data['nominal_biaya']) ? number_format((float)$data['nominal_biaya'], 0, ',', '.') : '0',
                '{jumlah_peserta}' => $data['jumlah_peserta'] ?? '1',
                '{waktu_daftar}' => $data['waktu_daftar'] ?? now()->translatedFormat('d F Y H:i') . ' WIB',
                '{waktu_verifikasi}' => $data['waktu_verifikasi'] ?? now()->translatedFormat('d F Y H:i') . ' WIB',
                '{link_scoreboard}' => $data['link_scoreboard'] ?? route('live.scoreboard'),
                '{link_login}' => $data['link_login'] ?? route('login'),
                '{no_wa}' => $data['phone_pendaftar'] ?? ($data['phone'] ?? $cleanPhone),
                '{nama_aplikasi}' => $appName,
                '{nama_kegiatan}' => $eventName,
            ];

            foreach ($placeholders as $tag => $val) {
                $msg = str_replace($tag, (string)$val, $msg);
            }

            // 5. Send to Wablas API
            $authHeader = $wablasSecretKey ? ($wablasToken . '.' . $wablasSecretKey) : $wablasToken;

            $res = Http::withoutVerifying()
                ->timeout(8)
                ->withHeaders([
                    'Authorization' => $authHeader,
                ])
                ->post("{$wablasHost}/api/send-message", [
                    'phone' => $cleanPhone,
                    'message' => $msg,
                    'token' => $wablasToken,
                    'secret' => $wablasSecretKey,
                ]);

            $isSent = $res->successful() && $res->json('status') !== false;

            // 6. Record in BroadcastLog
            BroadcastLog::create([
                'sender_id' => auth()->id() ?? 1,
                'target_audience' => 'auto_' . $templateCode,
                'target_competition' => $data['cabang_lomba'] ?? 'Sistem Otomatis',
                'recipients_count' => 1,
                'message' => $msg,
                'status' => $isSent ? 'sent' : 'failed',
            ]);

            return $isSent;
        } catch (\Throwable $e) {
            Log::error("Wablas Auto Notification Error ({$templateCode}): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send new registration notification alert to PIC of the competition
     */
    public static function notifyPicNewRegistration($registration): bool
    {
        try {
            if (!$registration) return false;

            $competition = $registration->competition;
            if (!$competition) return false;

            // Find all assigned PIC phone numbers for this competition (multi-PIC support)
            $phones = $competition->all_pic_phones;

            // If no direct PIC assigned, fallback to any user with role pic_lomba
            if (empty($phones)) {
                $picUser = \App\Models\User::where('role', 'pic_lomba')
                    ->whereNotNull('phone')
                    ->where('phone', '!=', '')
                    ->first();
                if ($picUser && !empty($picUser->phone)) {
                    $phones = [$picUser->phone];
                }
            }

            if (empty($phones)) {
                return false; // No PIC phone number available
            }

            $primaryMember = $registration->members->first();
            $namaPeserta = $primaryMember->full_name ?? ($registration->user->name ?? 'Peserta Baru');
            $pendaftarPhone = $registration->user->phone ?? ($primaryMember->phone ?? '-');

            $allSent = true;
            foreach ($phones as $picPhone) {
                $sent = static::sendAutoNotification('pic_new_registration', [
                    'phone' => $picPhone,
                    'nama_peserta' => $namaPeserta,
                    'nama_pendaftar' => $registration->user->name ?? $namaPeserta,
                    'nama_sekolah' => $registration->institution_name ?: ($registration->user->institution_name ?? '-'),
                    'nama_instansi' => $registration->institution_name ?: ($registration->user->institution_name ?? '-'),
                    'cabang_lomba' => $competition->name,
                    'kode_pendaftaran' => $registration->registration_code,
                    'phone_pendaftar' => $pendaftarPhone,
                    'waktu_daftar' => now()->translatedFormat('d M Y H:i') . ' WIB',
                    'link_login' => route('pic.dashboard'),
                ]);
                if (!$sent) {
                    $allSent = false;
                }
            }

            return $allSent;
        } catch (\Throwable $e) {
            Log::error("notifyPicNewRegistration Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send payment notification alert to Treasurer (Bendahara)
     */
    public static function notifyTreasurerNewPayment($registration, $customAmount = null): bool
    {
        try {
            if (!$registration) return false;

            // Get Treasurer Phone from AppSetting or role
            $treasurerPhone = trim(AppSetting::get('treasurer_phone_number', ''));
            if (empty($treasurerPhone)) {
                // Fallback to superadmin phone if available
                $superAdmin = \App\Models\User::where('role', 'superadmin')->whereNotNull('phone')->first();
                $treasurerPhone = $superAdmin->phone ?? '';
            }

            if (empty($treasurerPhone)) {
                return false; // No treasurer phone configured
            }

            $competition = $registration->competition;
            $compName = $competition->name ?? 'TALENTA 2026';
            $fee = $customAmount ?? ($registration->amount ?? ($competition->registration_fee ?? 0));
            $primaryMember = $registration->members->first();
            $namaPeserta = $primaryMember->full_name ?? ($registration->user->name ?? 'Pendaftar Baru');

            return static::sendAutoNotification('treasurer_new_payment', [
                'phone' => $treasurerPhone,
                'nama_peserta' => $namaPeserta,
                'nama_pendaftar' => $registration->user->name ?? $namaPeserta,
                'nama_sekolah' => $registration->institution_name ?: ($registration->user->institution_name ?? '-'),
                'nama_instansi' => $registration->institution_name ?: ($registration->user->institution_name ?? '-'),
                'cabang_lomba' => $compName,
                'kode_pendaftaran' => $registration->registration_code,
                'nominal_biaya' => $fee,
                'jumlah_peserta' => $registration->members->count() ?: 1,
                'waktu_daftar' => now()->translatedFormat('d M Y H:i') . ' WIB',
                'link_login' => route('admin.dashboard'),
            ]);
        } catch (\Throwable $e) {
            Log::error("notifyTreasurerNewPayment Error: " . $e->getMessage());
            return false;
        }
    }
}
