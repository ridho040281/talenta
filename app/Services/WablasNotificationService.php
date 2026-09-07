<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\BroadcastLog;
use App\Models\User;
use App\Models\WhatsappTemplate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WablasNotificationService
{
    /**
     * Send an automated notification triggered by system events
     *
     * @param  string  $templateCode  (e.g. 'account_created', 'registration_submitted', 'registration_verified')
     * @param  array  $data  Placeholders data (phone, nama_peserta, nisn, nama_sekolah, cabang_lomba, no_peserta, kode_pendaftaran, etc.)
     */
    public static function sendAutoNotification(string $templateCode, array $data): bool
    {
        try {
            // 1. Get Template
            $template = WhatsappTemplate::where('code', $templateCode)->first();
            if (! $template || ! $template->is_active) {
                return false; // Auto-trigger is disabled or template not found
            }

            // 2. Get API Credentials
            $wablasHost = rtrim(AppSetting::get('wablas_api_host', 'https://jogja.wablas.com'), '/');
            $wablasToken = trim(AppSetting::get('wablas_api_token', ''));
            $wablasSecretKey = trim(AppSetting::get('wablas_secret_key', ''));

            if (empty($wablasToken)) {
                return false; // Gateway not configured
            }

            // 3. Format Phone Numbers (Supports both single phone or array of phones)
            $rawInput = $data['phones'] ?? ($data['phone'] ?? []);
            $rawPhones = is_array($rawInput) ? $rawInput : [$rawInput];

            $cleanPhones = [];
            foreach ($rawPhones as $raw) {
                if (empty($raw)) {
                    continue;
                }
                $clean = preg_replace('/[^0-9]/', '', (string) $raw);
                if (empty($clean)) {
                    continue;
                }
                if (str_starts_with($clean, '0')) {
                    $clean = '62'.substr($clean, 1);
                } elseif (str_starts_with($clean, '8')) {
                    $clean = '628'.substr($clean, 1);
                }
                $cleanPhones[] = $clean;
            }
            $cleanPhones = array_values(array_unique($cleanPhones));

            if (empty($cleanPhones)) {
                return false;
            }

            // 4. Build message with dynamic placeholders
            $msg = $template->message;
            $appName = AppSetting::get('app_name', 'TALENTA');
            $eventName = AppSetting::get('event_name', 'Milad ke-57 MTsN 1 Blitar');
            $institutionName = AppSetting::get('institution_name', 'MTsN 1 Blitar');

            $firstCleanPhone = $cleanPhones[0] ?? '';

            $rawNamaPeserta = $data['nama_peserta'] ?? ($data['nama_pendaftar'] ?? 'Bapak/Ibu Peserta');
            $namaSekolah = $data['nama_sekolah'] ?? ($data['nama_instansi'] ?? $institutionName);

            // Clean duplicate school/institution from nama_peserta if it already ends with "(Nama Sekolah)"
            $cleanNamaPeserta = $rawNamaPeserta;
            if (is_string($cleanNamaPeserta) && ! empty($namaSekolah)) {
                $quotedSchool = preg_quote(trim($namaSekolah), '/');
                $cleanNamaPeserta = trim(preg_replace('/\s*\(' . $quotedSchool . '\)$/i', '', $cleanNamaPeserta));
            }

            $placeholders = [
                '{nama_peserta}' => $cleanNamaPeserta,
                '{nama_pendaftar}' => $cleanNamaPeserta,
                '{nisn}' => $data['nisn'] ?? '-',
                '{nama_sekolah}' => $namaSekolah,
                '{nama_instansi}' => $namaSekolah,
                '{cabang_lomba}' => $data['cabang_lomba'] ?? 'TALENTA 2026',
                '{kategori_lomba}' => $data['kategori_lomba'] ?? ($data['cabang_lomba'] ?? '-'),
                '{no_peserta}' => $data['no_peserta'] ?? ($data['kode_pendaftaran'] ?? '-'),
                '{kode_pendaftaran}' => $data['kode_pendaftaran'] ?? ($data['no_peserta'] ?? '-'),
                '{nomor_undian}' => $data['nomor_undian'] ?? ($data['draw_number'] ?? '-'),
                '{nominal_biaya}' => ! empty($data['nominal_biaya']) ? number_format((float) $data['nominal_biaya'], 0, ',', '.') : '0',
                '{jumlah_peserta}' => $data['jumlah_peserta'] ?? '1',
                '{waktu_daftar}' => $data['waktu_daftar'] ?? now()->translatedFormat('d F Y H:i').' WIB',
                '{waktu_verifikasi}' => $data['waktu_verifikasi'] ?? now()->translatedFormat('d F Y H:i').' WIB',
                '{link_scoreboard}' => $data['link_scoreboard'] ?? route('live.scoreboard'),
                '{link_login}' => $data['link_login'] ?? route('login'),
                '{no_wa}' => $data['phone_pendaftar'] ?? ($data['phone'] ?? $firstCleanPhone),
                '{nama_aplikasi}' => $appName,
                '{nama_kegiatan}' => $eventName,
                '{catatan_verifikasi}' => $data['catatan_verifikasi'] ?? ($data['catatan'] ?? ($data['alasan'] ?? 'Mohon periksa kembali kelengkapan berkas Anda.')),
                '{catatan}' => $data['catatan'] ?? ($data['catatan_verifikasi'] ?? ($data['alasan'] ?? 'Mohon periksa kembali kelengkapan berkas Anda.')),
                '{alasan}' => $data['alasan'] ?? ($data['catatan_verifikasi'] ?? ($data['catatan'] ?? 'Berkas belum memenuhi ketentuan.')),
            ];

            foreach ($placeholders as $tag => $val) {
                $msg = str_replace($tag, (string) $val, $msg);
            }

            // 5. Send to Wablas API for all unique phone numbers
            $authHeader = $wablasSecretKey ? ($wablasToken.'.'.$wablasSecretKey) : $wablasToken;
            $anySuccess = false;

            foreach ($cleanPhones as $cleanPhone) {
                try {
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
                    if ($isSent) {
                        $anySuccess = true;
                    }

                    // Record each recipient delivery
                    BroadcastLog::create([
                        'sender_id' => auth()->id() ?? 1,
                        'target_audience' => 'auto_'.$templateCode,
                        'target_competition' => $data['cabang_lomba'] ?? 'Sistem Otomatis',
                        'recipients_count' => 1,
                        'message' => "Tujuan: {$cleanPhone}\n\n".$msg,
                        'status' => $isSent ? 'sent' : 'failed',
                    ]);
                } catch (\Throwable $e) {
                    Log::error("Wablas Auto Notification Error ({$templateCode}) to {$cleanPhone}: ".$e->getMessage());
                }
            }

            return $anySuccess;
        } catch (\Throwable $e) {
            Log::error("Wablas Auto Notification Error ({$templateCode}): ".$e->getMessage());

            return false;
        }
    }

    /**
     * Send new registration notification alert to PIC of the competition
     */
    public static function notifyPicNewRegistration($registration): bool
    {
        try {
            if (! $registration) {
                return false;
            }

            $competition = $registration->competition;
            if (! $competition) {
                return false;
            }

            // If WhatsApp notification for PIC is disabled for this competition, skip cleanly
            if (! $competition->notify_pic) {
                return true;
            }

            // Find all assigned PIC phone numbers for this competition (Primary PIC + Assistants + Sector PICs)
            $phones = $competition->all_pic_phones;

            // If no direct PIC assigned, fallback to any user with role pic_lomba
            if (empty($phones)) {
                $picUser = User::where('role', 'pic_lomba')
                    ->whereNotNull('phone')
                    ->where('phone', '!=', '')
                    ->first();
                if ($picUser && ! empty($picUser->phone)) {
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
                    'waktu_daftar' => now()->translatedFormat('d M Y H:i').' WIB',
                    'link_login' => route('pic.dashboard'),
                ]);
                if (! $sent) {
                    $allSent = false;
                }
            }

            return $allSent;
        } catch (\Throwable $e) {
            Log::error('notifyPicNewRegistration Error: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Get all valid phone numbers of Treasurer & Assistants formatted for WhatsApp.
     */
    public static function getAllTreasurerPhones(): array
    {
        $phones = collect();

        // 1. Primary Treasurer Phone from AppSetting
        $primaryPhone = trim(AppSetting::get('treasurer_phone_number', ''));
        if (! empty($primaryPhone)) {
            $phones->push($primaryPhone);
        }

        // 2. Assistant Treasurer / Financial Team Phones (comma or newline separated)
        $assistantPhones = trim(AppSetting::get('treasurer_assistant_phones', ''));
        if (! empty($assistantPhones)) {
            $rawList = preg_split('/[\r\n,;]+/', $assistantPhones);
            foreach ($rawList as $raw) {
                $trimmed = trim($raw);
                if (! empty($trimmed)) {
                    $phones->push($trimmed);
                }
            }
        }

        // 3. Fallback to Superadmin user phone if absolutely no numbers configured
        if ($phones->isEmpty()) {
            $superAdmin = User::where('role', 'superadmin')->whereNotNull('phone')->where('phone', '!=', '')->first();
            if ($superAdmin && ! empty($superAdmin->phone)) {
                $phones->push($superAdmin->phone);
            }
        }

        return $phones
            ->map(function ($phone) {
                $clean = preg_replace('/[^0-9]/', '', (string) $phone);
                if (str_starts_with($clean, '0')) {
                    $clean = '62'.substr($clean, 1);
                } elseif (str_starts_with($clean, '8')) {
                    $clean = '628'.substr($clean, 1);
                }

                return $clean;
            })
            ->filter(fn ($p) => strlen($p) >= 9)
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * Send payment notification alert to Treasurer (Bendahara) & Financial Assistants
     */
    public static function notifyTreasurerNewPayment($registration, $customAmount = null): bool
    {
        try {
            if (! $registration) {
                return false;
            }

            $phones = static::getAllTreasurerPhones();
            if (empty($phones)) {
                return false; // No treasurer phone configured
            }

            $competition = $registration->competition;
            $compName = $competition->name ?? 'TALENTA 2026';
            $fee = $customAmount ?? ($registration->amount ?? ($competition->registration_fee ?? 0));
            $primaryMember = $registration->members->first();
            $namaPeserta = $primaryMember->full_name ?? ($registration->user->name ?? 'Pendaftar Baru');

            $allSent = true;
            foreach ($phones as $treasurerPhone) {
                $sent = static::sendAutoNotification('treasurer_new_payment', [
                    'phone' => $treasurerPhone,
                    'nama_peserta' => $namaPeserta,
                    'nama_pendaftar' => $registration->user->name ?? $namaPeserta,
                    'nama_sekolah' => $registration->institution_name ?: ($registration->user->institution_name ?? '-'),
                    'nama_instansi' => $registration->institution_name ?: ($registration->user->institution_name ?? '-'),
                    'cabang_lomba' => $compName,
                    'kode_pendaftaran' => $registration->registration_code,
                    'nominal_biaya' => $fee,
                    'jumlah_peserta' => $registration->members->count() ?: 1,
                    'waktu_daftar' => now()->translatedFormat('d M Y H:i').' WIB',
                    'link_login' => route('admin.dashboard'),
                ]);
                if (! $sent) {
                    $allSent = false;
                }
            }

            return $allSent;
        } catch (\Throwable $e) {
            Log::error('notifyTreasurerNewPayment Error: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Send collective invoice notification alert specifically to Treasurer & Financial Assistants
     */
    public static function notifyTreasurerCollectiveInvoice($invoice): bool
    {
        try {
            if (! $invoice) {
                return false;
            }

            $phones = static::getAllTreasurerPhones();
            if (empty($phones)) {
                return false; // No treasurer phone configured
            }

            $user = $invoice->user;
            $institutionName = $user->institution_name ?: ($invoice->notes ?? 'Sekolah/Lembaga');
            $regCount = $invoice->registrations()->count();

            // Try dedicated template 'treasurer_collective_invoice', fallback to 'treasurer_new_payment'
            $templateCode = WhatsappTemplate::where('code', 'treasurer_collective_invoice')->where('is_active', true)->exists()
                ? 'treasurer_collective_invoice'
                : 'treasurer_new_payment';

            $allSent = true;
            foreach ($phones as $treasurerPhone) {
                $sent = static::sendAutoNotification($templateCode, [
                    'phone' => $treasurerPhone,
                    'nama_peserta' => $user->name ?? 'Official Sekolah',
                    'nama_pendaftar' => $user->name ?? 'Official Sekolah',
                    'nama_sekolah' => $institutionName,
                    'nama_instansi' => $institutionName,
                    'cabang_lomba' => "{$regCount} Peserta (Kolektif)",
                    'kode_pendaftaran' => $invoice->invoice_number,
                    'nominal_biaya' => $invoice->final_amount,
                    'jumlah_peserta' => $regCount,
                    'phone_pendaftar' => $user->phone ?? '-',
                    'waktu_daftar' => now()->translatedFormat('d M Y H:i').' WIB',
                    'link_login' => route('admin.invoices.show', $invoice->id),
                ]);
                if (! $sent) {
                    $allSent = false;
                }
            }

            return $allSent;
        } catch (\Throwable $e) {
            Log::error('notifyTreasurerCollectiveInvoice Error: '.$e->getMessage());

            return false;
        }
    }
}
