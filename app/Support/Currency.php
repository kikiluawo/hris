<?php

namespace App\Support;

class Currency
{
    public static function rupiah(int|float|null $value, bool $compact = false): string
    {
        $value = (int) ($value ?? 0);

        if ($compact) {
            if (abs($value) >= 1_000_000_000) {
                return 'Rp ' . self::trim($value / 1_000_000_000) . ' M';
            }

            if (abs($value) >= 1_000_000) {
                return 'Rp ' . self::trim($value / 1_000_000) . ' jt';
            }

            if (abs($value) >= 1_000) {
                return 'Rp ' . self::trim($value / 1_000) . ' rb';
            }
        }

        return 'Rp ' . number_format($value, 0, ',', '.');
    }

    private static function trim(float $value): string
    {
        return rtrim(rtrim(number_format($value, 1, ',', '.'), '0'), ',');
    }
}
