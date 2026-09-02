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
            $placeholders = [
                '{nama_peserta}' => $data['nama_peserta'] ?? 'Bapak/Ibu Peserta',
                '{nisn}' => $data['nisn'] ?? '-',
                '{nama_sekolah}' => $data['nama_sekolah'] ?? '-',
                '{cabang_lomba}' => $data['cabang_lomba'] ?? 'TALENTA 2026',
                '{no_peserta}' => $data['no_peserta'] ?? ($data['kode_pendaftaran'] ?? '-'),
                '{kode_pendaftaran}' => $data['kode_pendaftaran'] ?? ($data['no_peserta'] ?? '-'),
                '{link_scoreboard}' => $data['link_scoreboard'] ?? url('/'),
                '{link_login}' => $data['link_login'] ?? route('login'),
                '{no_wa}' => $data['phone'] ?? $cleanPhone,
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
}
