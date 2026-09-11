<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class ImageOptimizerService
{
    /**
     * Optimize, auto-orient, resize and compress an uploaded image to a target file size (e.g. ~200KB).
     *
     * @param  UploadedFile|string  $file  The uploaded file or path to source image
     * @param  string  $targetFolder  Relative target directory inside storage/app/public (e.g. 'photos/pramuka/members')
     * @param  string  $fileName  Target file name (e.g. '3123412231_Joko Kelana.jpg')
     * @param  int  $maxDimension  Maximum width or height in pixels (default: 1080)
     * @param  int  $targetMaxKb  Target maximum file size in Kilobytes (default: 200)
     * @return string Relative storage path for database storage (e.g. 'photos/pramuka/members/3123412231_Joko Kelana.jpg')
     */
    public static function optimizeAndStore($file, string $targetFolder, string $fileName, int $maxDimension = 1080, int $targetMaxKb = 200): string
    {
        $cleanFolder = trim(str_replace('\\', '/', $targetFolder), '/');
        $storageDir = storage_path('app/public/'.$cleanFolder);

        if (! file_exists($storageDir)) {
            @mkdir($storageDir, 0755, true);
        }

        $sourcePath = $file instanceof UploadedFile ? $file->getRealPath() : $file;
        $targetPath = $storageDir.'/'.$fileName;
        $relativeReturnPath = $cleanFolder.'/'.$fileName;

        if (! file_exists($sourcePath)) {
            if ($file instanceof UploadedFile) {
                $stored = $file->storeAs($cleanFolder, $fileName, 'public');
                if (class_exists(\App\Http\Controllers\AdminSettingsController::class)) {
                    \App\Http\Controllers\AdminSettingsController::ensurePublicStorageSync($stored);
                }
                return $stored;
            }

            return $relativeReturnPath;
        }

        // Check if GD extension is available
        if (! extension_loaded('gd')) {
            if ($file instanceof UploadedFile) {
                $stored = $file->storeAs($cleanFolder, $fileName, 'public');
                if (class_exists(\App\Http\Controllers\AdminSettingsController::class)) {
                    \App\Http\Controllers\AdminSettingsController::ensurePublicStorageSync($stored);
                }
                return $stored;
            }
            @copy($sourcePath, $targetPath);
            if (class_exists(\App\Http\Controllers\AdminSettingsController::class)) {
                \App\Http\Controllers\AdminSettingsController::ensurePublicStorageSync($relativeReturnPath);
            }

            return $relativeReturnPath;
        }

        try {
            $info = @getimagesize($sourcePath);
            if (! $info) {
                if ($file instanceof UploadedFile) {
                    $stored = $file->storeAs($cleanFolder, $fileName, 'public');
                    if (class_exists(\App\Http\Controllers\AdminSettingsController::class)) {
                        \App\Http\Controllers\AdminSettingsController::ensurePublicStorageSync($stored);
                    }
                    return $stored;
                }
                @copy($sourcePath, $targetPath);
                if (class_exists(\App\Http\Controllers\AdminSettingsController::class)) {
                    \App\Http\Controllers\AdminSettingsController::ensurePublicStorageSync($relativeReturnPath);
                }

                return $relativeReturnPath;
            }

            $mime = $info['mime'] ?? '';
            $srcWidth = $info[0];
            $srcHeight = $info[1];

            // Create GD source resource
            $srcImage = null;
            switch ($mime) {
                case 'image/jpeg':
                case 'image/jpg':
                    $srcImage = @imagecreatefromjpeg($sourcePath);
                    break;
                case 'image/png':
                    $srcImage = @imagecreatefrompng($sourcePath);
                    break;
                case 'image/webp':
                    $srcImage = @imagecreatefromwebp($sourcePath);
                    break;
                case 'image/gif':
                    $srcImage = @imagecreatefromgif($sourcePath);
                    break;
                case 'image/bmp':
                case 'image/x-ms-bmp':
                    $srcImage = @imagecreatefrombmp($sourcePath);
                    break;
                default:
                    $rawContent = @file_get_contents($sourcePath);
                    if ($rawContent) {
                        $srcImage = @imagecreatefromstring($rawContent);
                    }
            }

            if (! $srcImage) {
                if ($file instanceof UploadedFile) {
                    $stored = $file->storeAs($cleanFolder, $fileName, 'public');
                    if (class_exists(\App\Http\Controllers\AdminSettingsController::class)) {
                        \App\Http\Controllers\AdminSettingsController::ensurePublicStorageSync($stored);
                    }
                    return $stored;
                }
                @copy($sourcePath, $targetPath);
                if (class_exists(\App\Http\Controllers\AdminSettingsController::class)) {
                    \App\Http\Controllers\AdminSettingsController::ensurePublicStorageSync($relativeReturnPath);
                }

                return $relativeReturnPath;
            }

            // Auto-orient based on EXIF (Smartphone cameras often save orientation in EXIF)
            if (function_exists('exif_read_data') && in_array($mime, ['image/jpeg', 'image/jpg'])) {
                try {
                    $exif = @exif_read_data($sourcePath);
                    if (! empty($exif['Orientation'])) {
                        switch ($exif['Orientation']) {
                            case 3:
                                $rotated = imagerotate($srcImage, 180, 0);
                                if ($rotated) {
                                    imagedestroy($srcImage);
                                    $srcImage = $rotated;
                                }
                                break;
                            case 6:
                                $rotated = imagerotate($srcImage, -90, 0);
                                if ($rotated) {
                                    imagedestroy($srcImage);
                                    $srcImage = $rotated;
                                    $tmp = $srcWidth;
                                    $srcWidth = $srcHeight;
                                    $srcHeight = $tmp;
                                }
                                break;
                            case 8:
                                $rotated = imagerotate($srcImage, 90, 0);
                                if ($rotated) {
                                    imagedestroy($srcImage);
                                    $srcImage = $rotated;
                                    $tmp = $srcWidth;
                                    $srcWidth = $srcHeight;
                                    $srcHeight = $tmp;
                                }
                                break;
                        }
                    }
                } catch (\Throwable $e) {
                    // Ignore EXIF read errors
                }
            }

            // Calculate proportional dimensions (bounding box: $maxDimension)
            $newWidth = $srcWidth;
            $newHeight = $srcHeight;

            if ($srcWidth > $maxDimension || $srcHeight > $maxDimension) {
                if ($srcWidth > $srcHeight) {
                    $newWidth = $maxDimension;
                    $newHeight = (int) round(($srcHeight / $srcWidth) * $maxDimension);
                } else {
                    $newHeight = $maxDimension;
                    $newWidth = (int) round(($srcWidth / $srcHeight) * $maxDimension);
                }
            }

            // Create truecolor destination canvas
            $dstImage = imagecreatetruecolor($newWidth, $newHeight);

            // Fill with clean white background for transparency fallback
            $white = imagecolorallocate($dstImage, 255, 255, 255);
            imagefilledrectangle($dstImage, 0, 0, $newWidth, $newHeight, $white);

            // Resample high-quality
            imagecopyresampled($dstImage, $srcImage, 0, 0, 0, 0, $newWidth, $newHeight, $srcWidth, $srcHeight);

            // Ensure destination filename has .jpg extension for standard optimized JPEG
            $pathInfo = pathinfo($targetPath);
            $normalizedTargetPath = $pathInfo['dirname'].'/'.$pathInfo['filename'].'.jpg';
            $normalizedRelativePath = $cleanFolder.'/'.$pathInfo['filename'].'.jpg';

            // Adaptive compression loop to achieve target file size (~100KB - 200KB)
            $quality = 82;
            imagejpeg($dstImage, $normalizedTargetPath, $quality);

            $targetBytes = $targetMaxKb * 1024;
            while (file_exists($normalizedTargetPath) && filesize($normalizedTargetPath) > $targetBytes && $quality > 25) {
                $quality -= 6;
                imagejpeg($dstImage, $normalizedTargetPath, $quality);
            }

            // If still over targetBytes (e.g. extremely complex high-frequency photo), scale down slightly
            if (file_exists($normalizedTargetPath) && filesize($normalizedTargetPath) > $targetBytes && $newWidth > 450) {
                $scaleFactor = 0.75;
                $scaledWidth = (int) round($newWidth * $scaleFactor);
                $scaledHeight = (int) round($newHeight * $scaleFactor);
                $secondDst = imagecreatetruecolor($scaledWidth, $scaledHeight);
                $white2 = imagecolorallocate($secondDst, 255, 255, 255);
                imagefilledrectangle($secondDst, 0, 0, $scaledWidth, $scaledHeight, $white2);
                imagecopyresampled($secondDst, $dstImage, 0, 0, 0, 0, $scaledWidth, $scaledHeight, $newWidth, $newHeight);

                $quality = 70;
                imagejpeg($secondDst, $normalizedTargetPath, $quality);
                while (file_exists($normalizedTargetPath) && filesize($normalizedTargetPath) > $targetBytes && $quality > 25) {
                    $quality -= 6;
                    imagejpeg($secondDst, $normalizedTargetPath, $quality);
                }
                imagedestroy($secondDst);
            }

            imagedestroy($srcImage);
            imagedestroy($dstImage);

            if (class_exists(\App\Http\Controllers\AdminSettingsController::class)) {
                \App\Http\Controllers\AdminSettingsController::ensurePublicStorageSync($normalizedRelativePath);
            }

            return $normalizedRelativePath;
        } catch (\Throwable $e) {
            Log::warning('ImageOptimizerService compression failed: '.$e->getMessage());
            if ($file instanceof UploadedFile) {
                $stored = $file->storeAs($cleanFolder, $fileName, 'public');
                if (class_exists(\App\Http\Controllers\AdminSettingsController::class)) {
                    \App\Http\Controllers\AdminSettingsController::ensurePublicStorageSync($stored);
                }
                return $stored;
            }
            @copy($sourcePath, $targetPath);
            if (class_exists(\App\Http\Controllers\AdminSettingsController::class)) {
                \App\Http\Controllers\AdminSettingsController::ensurePublicStorageSync($relativeReturnPath);
            }

            return $relativeReturnPath;
        }
    }

    /**
     * Store and compress payment proof (slip transfer).
     * If image (JPG/PNG/WebP/BMP), compresses to target ~100KB with 1200px resolution so text remains crisp.
     * If PDF, bypasses GD compression and stores directly.
     *
     * @param  UploadedFile  $file
     * @param  string  $folder Target directory (e.g. 'payments' or 'payment_proofs')
     * @param  string|null  $customPrefix Optional prefix for filename (e.g. 'pay', 'kolektif', 'invoice_12')
     * @return string Relative path stored in public disk
     */
    public static function storePaymentProof(UploadedFile $file, string $folder = 'payments', ?string $customPrefix = 'pay'): string
    {
        $cleanFolder = trim(str_replace('\\', '/', $folder), '/');
        $rawExt = strtolower($file->getClientOriginalExtension());
        $mime = strtolower($file->getMimeType() ?? '');
        $isPdf = ($rawExt === 'pdf' || $mime === 'application/pdf');

        $prefix = $customPrefix ? rtrim($customPrefix, '_') . '_' : 'pay_';

        if ($isPdf) {
            $fileName = $prefix . uniqid() . '_' . time() . '.pdf';
            $storedPath = $file->storeAs($cleanFolder, $fileName, 'public');
            if (class_exists(\App\Http\Controllers\AdminSettingsController::class)) {
                \App\Http\Controllers\AdminSettingsController::ensurePublicStorageSync($storedPath);
            }
            return $storedPath;
        }

        // Image optimization: maxDimension 1200 for sharpness of numbers/text, targetMaxKb 100 for fast loading
        $fileName = $prefix . uniqid() . '_' . time() . '.jpg';
        $storedPath = static::optimizeAndStore(
            $file,
            $cleanFolder,
            $fileName,
            1200, // 1200px max dimension ensures transfer slips and small text remain razor sharp
            100   // Target 100 KB max
        );

        if (class_exists(\App\Http\Controllers\AdminSettingsController::class)) {
            \App\Http\Controllers\AdminSettingsController::ensurePublicStorageSync($storedPath);
        }

        return $storedPath;
    }
}
