<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ItemImageService
{
    /**
     * Max length of the longest edge (px) before downscaling.
     */
    protected const MAX_DIMENSION = 1280;

    /**
     * JPEG quality used when re-encoding.
     */
    protected const JPEG_QUALITY = 80;

    /**
     * Downscale (if needed), re-encode as JPEG and store on the public disk.
     * Returns the relative storage path.
     */
    public function store(UploadedFile $file): string
    {
        $image = $this->createFromFile($file);

        $width = imagesx($image);
        $height = imagesy($image);
        $max = max($width, $height);

        if ($max > self::MAX_DIMENSION) {
            $ratio = self::MAX_DIMENSION / $max;
            $newWidth = (int) round($width * $ratio);
            $newHeight = (int) round($height * $ratio);

            $resized = imagecreatetruecolor($newWidth, $newHeight);
            $white = imagecolorallocate($resized, 255, 255, 255);
            imagefill($resized, 0, 0, $white);
            imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            $image = $resized;
        }

        $tempPath = tempnam(sys_get_temp_dir(), 'item_');
        $tempPath .= '.jpg';
        imagejpeg($image, $tempPath, self::JPEG_QUALITY);

        $uploaded = new UploadedFile(
            $tempPath,
            Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)).'_'.Str::random(8).'.jpg',
            'image/jpeg',
            null,
            true
        );

        $path = $uploaded->store('items', 'public');
        @unlink($tempPath);

        return $path;
    }

    /**
     * Delete an image from the public disk.
     */
    public function delete(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }

    /**
     * Decode the uploaded file into a GD image resource.
     */
    protected function createFromFile(UploadedFile $file): \GdImage
    {
        $mime = $file->getMimeType();

        $image = match ($mime) {
            'image/png' => imagecreatefrompng($file->getRealPath()),
            'image/webp' => imagecreatefromwebp($file->getRealPath()),
            default => imagecreatefromjpeg($file->getRealPath()),
        };

        if ($image === false) {
            throw new \InvalidArgumentException('Gagal membaca file gambar.');
        }

        return $image;
    }
}
