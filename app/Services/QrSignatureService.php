<?php

namespace App\Services;

use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrSignatureService
{
    /**
     * Generate clean inline SVG QR code for verification in documents.
     */
    public static function generateSvg(string $data, int $size = 60): string
    {
        try {
            if (class_exists(QrCode::class)) {
                $svg = (string) QrCode::size($size)->margin(0)->errorCorrection('M')->generate($data);
                // Strip XML declaration if present so inline SVG renders cleanly in HTML/Blade
                $svg = preg_replace('/<\?xml[^\>]*\?>/i', '', $svg);
                return trim($svg);
            }
        } catch (\Throwable $e) {
            // Fallback to web QR API
        }

        $encoded = urlencode($data);
        return '<img src="https://api.qrserver.com/v1/create-qr-code/?size='.$size.'x'.$size.'&data='.$encoded.'" width="'.$size.'" height="'.$size.'" alt="QR Code" style="display:inline-block; border-radius: 4px;" />';
    }

    /**
     * Generate QR Code URL for Registration Form (PIC / Verifikator)
     */
    public static function registrationFormUrl($registration): string
    {
        return url('/cek-status?q=' . urlencode($registration->registration_code));
    }

    /**
     * Generate QR Code URL for Receipt (Bendahara)
     */
    public static function receiptUrl($registration): string
    {
        $code = $registration->invoice ? $registration->invoice->invoice_number : $registration->registration_code;
        return url('/cek-status?q=' . urlencode($code));
    }

    /**
     * Generate QR Code URL for Account Proof / Ketua Panitia
     */
    public static function accountProofUrl($registration): string
    {
        return url('/cek-status?q=' . urlencode($registration->registration_code));
    }
}
