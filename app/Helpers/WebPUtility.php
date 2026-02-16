<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class WebPUtility
{
    /**
     * Compress an uploaded image to WebP format.
     * 
     * @param UploadedFile $file
     * @param string $directory
     * @param int $quality
     * @return string Path to the compressed image
     */
    public static function compress(UploadedFile $file, string $directory, int $quality = 80): string
    {
        $extension = $file->getClientOriginalExtension();
        $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) . '_' . time() . '.webp';
        $fullPath = storage_path('app/public/' . $directory . '/' . $filename);

        // Ensure directory exists
        if (!Storage::disk('public')->exists($directory)) {
            Storage::disk('public')->makeDirectory($directory);
        }

        $image = null;
        switch (strtolower($extension)) {
            case 'jpeg':
            case 'jpg':
                $image = imagecreatefromjpeg($file->getRealPath());
                break;
            case 'png':
                $image = imagecreatefrompng($file->getRealPath());
                // Handle transparency if needed
                imagepalettetotruecolor($image);
                imagealphablending($image, true);
                imagesavealpha($image, true);
                break;
            case 'webp':
                // Just move it if already webp
                return $file->store($directory, 'public');
            default:
                // Fallback to standard store if not supported for compression
                return $file->store($directory, 'public');
        }

        if ($image) {
            imagewebp($image, $fullPath, $quality);
            imagedestroy($image);
            return $directory . '/' . $filename;
        }

        return $file->store($directory, 'public');
    }
}
