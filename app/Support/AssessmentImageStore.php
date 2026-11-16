<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AssessmentImageStore
{
    public const DISK = 'assessment-images';

    public const FOLDER = 'assessment-images';

    public const MAX_SIDE = 1200;

    public const MIN_SIDE = 60;

    public const MAX_BYTES = 6 * 1024 * 1024;

    private const PATTERN = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}\.(jpg|jpeg|png|webp)$/';

    private const TYPES = [
        IMAGETYPE_JPEG => 'jpg',
        IMAGETYPE_PNG => 'png',
        IMAGETYPE_WEBP => 'webp',
    ];

    public function store(UploadedFile $file): string
    {
        if ($file->getSize() > self::MAX_BYTES) {
            throw ValidationException::withMessages([
                'image' => 'Ukuran gambar maksimal 6 MB.',
            ]);
        }

        $info = @getimagesize($file->getPathname());

        if ($info === false || ! array_key_exists($info[2], self::TYPES)) {
            throw ValidationException::withMessages([
                'image' => 'Format gambar harus JPG, PNG, atau WebP.',
            ]);
        }

        [$width, $height] = $info;

        if ($width < self::MIN_SIDE || $height < self::MIN_SIDE) {
            throw ValidationException::withMessages([
                'image' => 'Gambar terlalu kecil ('.$width.'×'.$height.' piksel). Sisi terpendek minimal '.self::MIN_SIDE.' piksel supaya masih terbaca di HP.',
            ]);
        }

        $source = @imagecreatefromstring((string) file_get_contents($file->getPathname()));

        if (! $source instanceof \GdImage) {
            throw ValidationException::withMessages([
                'image' => 'Gambar tidak bisa dibaca. Simpan ulang berkasnya lalu coba lagi.',
            ]);
        }

        $image = $this->orient($source, $file, $info[2]);
        $canvas = $this->fit($image, imagesx($image), imagesy($image));

        $extension = self::TYPES[$info[2]];
        $name = Str::uuid()->toString().'.'.$extension;
        $binary = $this->encode($canvas, $extension);

        imagedestroy($source);

        if ($canvas !== $source && $canvas instanceof \GdImage) {
            imagedestroy($canvas);
        }

        if (! Storage::disk(self::DISK)->put($name, $binary)) {
            throw ValidationException::withMessages([
                'image' => 'Gambar gagal disimpan di server. Hubungi HRD atau coba lagi nanti.',
            ]);
        }

        return $name;
    }

    public static function url(?string $name): ?string
    {
        return self::isSafe($name) ? asset(self::FOLDER.'/'.$name) : null;
    }

    public static function urlRoot(): string
    {
        return asset(self::FOLDER.'/__IMAGE__');
    }

    public static function isSafe(?string $name): bool
    {
        return is_string($name) && preg_match(self::PATTERN, $name) === 1;
    }

    public static function sanitize(mixed $name): ?string
    {
        $name = is_string($name) ? trim($name) : '';

        return self::isSafe($name) ? $name : null;
    }

    public static function sanitizeList(mixed $names, int $size): array
    {
        if (! is_array($names)) {
            $names = [];
        }

        $list = [];

        for ($index = 0; $index < $size; $index++) {
            $list[] = self::sanitize($names[$index] ?? null);
        }

        return $list;
    }

    public static function hasAny(?string $image, mixed $optionImages): bool
    {
        if (self::isSafe($image)) {
            return true;
        }

        return is_array($optionImages) && array_filter($optionImages, fn ($name) => self::isSafe($name)) !== [];
    }

    private function orient(\GdImage $image, UploadedFile $file, int $type): \GdImage
    {
        if ($type !== IMAGETYPE_JPEG || ! function_exists('exif_read_data')) {
            return $image;
        }

        $exif = @exif_read_data($file->getPathname());
        $orientation = (int) ($exif['Orientation'] ?? 1);

        return match ($orientation) {
            3 => imagerotate($image, 180, 0),
            6 => imagerotate($image, -90, 0),
            8 => imagerotate($image, 90, 0),
            default => $image,
        };
    }

    private function fit(\GdImage $image, int $width, int $height): \GdImage
    {
        $scale = min(1, self::MAX_SIDE / max($width, $height));

        if ($scale >= 1) {
            return $image;
        }

        $target = imagescale($image, max(1, (int) round($width * $scale)), max(1, (int) round($height * $scale)));

        return $target instanceof \GdImage ? $target : $image;
    }

    private function encode(\GdImage $image, string $extension): string
    {
        ob_start();

        match ($extension) {
            'png' => imagepng($image, null, 6),
            'webp' => imagewebp($image, null, 85),
            default => imagejpeg($image, null, 85),
        };

        return (string) ob_get_clean();
    }
}
