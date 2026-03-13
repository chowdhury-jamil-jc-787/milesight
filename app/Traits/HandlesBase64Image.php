<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait HandlesBase64Image
{
    protected function saveBase64Image(?string $base64Image, string $folder = 'vehicle-counts'): ?string
    {
        if (empty($base64Image)) {
            return null;
        }

        // Remove data URI prefix if exists
        if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $matches)) {
            $extension = strtolower($matches[1]);
            $base64Image = substr($base64Image, strpos($base64Image, ',') + 1);
        } else {
            $extension = 'jpg';
        }

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($extension, $allowedExtensions)) {
            $extension = 'jpg';
        }

        $decodedImage = base64_decode($base64Image, true);

        if ($decodedImage === false) {
            throw new \InvalidArgumentException('Invalid base64 image data.');
        }

        // Basic file size check: 10 MB max
        if (strlen($decodedImage) > 10 * 1024 * 1024) {
            throw new \InvalidArgumentException('Image size exceeds 10MB.');
        }

        $fileName = $folder . '/' . date('Y/m/d') . '/' . Str::uuid() . '.' . $extension;

        Storage::disk('public')->put($fileName, $decodedImage);

        return $fileName;
    }

    protected function deleteImageIfExists(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}