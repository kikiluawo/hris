<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Candidate extends Model
{
    public static function normalizePhone(?string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone) ?? '';

        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }

        if (str_starts_with($digits, '62')) {
            $digits = substr($digits, 2);
        }

        $digits = ltrim($digits, '0');

        if (! preg_match('/^8\d{7,11}$/', $digits)) {
            return null;
        }

        return '0' . $digits;
    }

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'city',
        'cv_path',
        'cv_original_name',
    ];

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }
}
