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
                return $file->storeAs($cleanFolder, $fileName, 'public');
            }

            return $relativeReturnPath;
        }

        // Check if GD extension is available
        if (! extension_loaded('gd')) {
            if ($file instanceof UploadedFile) {
                return $file->storeAs($cleanFolder, $fileName, 'public');
            }
            @copy($sourcePath, $targetPath);

            return $relativeReturnPath;
        }

        try {
            $info = @getimagesize($sourcePath);
            if (! $info) {
                if ($file instanceof UploadedFile) {
                    return $file->storeAs($cleanFolder, $fileName, 'public');
                }
                @copy($sourcePath, $targetPath);

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
                    return $file->storeAs($cleanFolder, $fileName, 'public');
                }
                @copy($sourcePath, $targetPath);

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
            while (file_exists($normalizedTargetPath) && filesize($normalizedTargetPath) > $targetBytes && $quality > 45) {
                $quality -= 8;
                imagejpeg($dstImage, $normalizedTargetPath, $quality);
            }

            imagedestroy($srcImage);
            imagedestroy($dstImage);

            return $normalizedRelativePath;
        } catch (\Throwable $e) {
            Log::warning('ImageOptimizerService compression failed: '.$e->getMessage());
            if ($file instanceof UploadedFile) {
                return $file->storeAs($cleanFolder, $fileName, 'public');
            }
            @copy($sourcePath, $targetPath);

            return $relativeReturnPath;
        }
    }
}
