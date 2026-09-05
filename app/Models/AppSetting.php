<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class AppSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'group',
    ];

    public static function allKeyValues(): array
    {
        return Cache::remember('global_app_settings', 3600, function () {
            try {
                return static::pluck('value', 'key')->toArray();
            } catch (\Throwable $e) {
                return [];
            }
        });
    }

    public static function getAllSettings(): array
    {
        return static::allKeyValues();
    }

    public static function get(string $key, $default = null)
    {
        $all = static::allKeyValues();

        return array_key_exists($key, $all) ? $all[$key] : $default;
    }

    public static function set(string $key, $value, string $group = 'general')
    {
        Cache::forget('global_app_settings');

        return static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group]
        );
    }

    public static function isRegistrationOpen(): bool
    {
        $status = static::get('global_registration_status', 'open');
        if ($status === 'closed') {
            return false;
        }

        $autoClose = static::get('registration_auto_close', '1') == '1';
        if ($autoClose) {
            $now = now();
            $startDate = static::get('registration_start_date');
            if (!empty($startDate) && strtotime($startDate)) {
                if ($now->lt(\Carbon\Carbon::parse($startDate))) {
                    return false;
                }
            }

            $deadline = static::get('registration_deadline');
            if (!empty($deadline) && strtotime($deadline)) {
                if ($now->gt(\Carbon\Carbon::parse($deadline))) {
                    return false;
                }
            }
        }

        return true;
    }

    public static function getRegistrationStatusInfo(): array
    {
        $status = static::get('global_registration_status', 'open');
        $autoClose = static::get('registration_auto_close', '1') == '1';
        $startDate = static::get('registration_start_date', '2026-09-01T08:00');
        $deadline = static::get('registration_deadline', '2026-09-25T23:59');
        $closedMessage = static::get('registration_closed_message', 'Pendaftaran TALENTA 2026 telah resmi ditutup.');
        
        $now = now();
        $isStarted = true;
        $isExpired = false;

        if (!empty($startDate) && strtotime($startDate)) {
            $isStarted = $now->gte(\Carbon\Carbon::parse($startDate));
        }

        if (!empty($deadline) && strtotime($deadline)) {
            $isExpired = $now->gt(\Carbon\Carbon::parse($deadline));
        }

        $isOpen = ($status !== 'closed');
        if ($autoClose) {
            if (!$isStarted || $isExpired) {
                $isOpen = false;
            }
        }

        $statusCode = 'open';
        $statusLabel = 'Pendaftaran Dibuka';
        $statusColor = 'emerald';
        $buttonText = 'Daftar Cabang Ini';
        $buttonIcon = 'arrow-right';

        if ($status === 'closed') {
            $statusCode = 'closed_manual';
            $statusLabel = 'Ditutup Manual oleh Panitia';
            $statusColor = 'rose';
            $buttonText = 'Pendaftaran Ditutup';
            $buttonIcon = 'lock';
        } elseif ($autoClose && !$isStarted) {
            $statusCode = 'not_started';
            $statusLabel = 'Belum Dibuka (Terjadwal)';
            $statusColor = 'amber';
            $buttonText = 'Belum Dibuka';
            $buttonIcon = 'clock';
        } elseif ($autoClose && $isExpired) {
            $statusCode = 'closed_expired';
            $statusLabel = 'Ditutup (Batas Waktu Berakhir)';
            $statusColor = 'rose';
            $buttonText = 'Pendaftaran Ditutup';
            $buttonIcon = 'lock';
        }

        $startDateFormatted = !empty($startDate) ? \Carbon\Carbon::parse($startDate)->translatedFormat('d F Y, H:i') : null;
        $deadlineFormatted = !empty($deadline) ? \Carbon\Carbon::parse($deadline)->translatedFormat('d F Y, H:i') : null;

        return [
            'is_open' => $isOpen,
            'status_code' => $statusCode,
            'status_label' => $statusLabel,
            'status_color' => $statusColor,
            'button_text' => $buttonText,
            'button_icon' => $buttonIcon,
            'start_date' => $startDate,
            'deadline' => $deadline,
            'start_date_formatted' => $startDateFormatted,
            'deadline_formatted' => $deadlineFormatted,
            'closed_message' => $closedMessage,
            'auto_close' => $autoClose,
            'global_status' => $status,
        ];
    }
}
