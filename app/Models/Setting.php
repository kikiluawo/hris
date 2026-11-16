<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $primaryKey = 'name';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'name',
        'value',
    ];

    public static function get(string $name, ?string $default = null): ?string
    {
        $value = static::query()->whereKey($name)->value('value');

        return $value !== null && $value !== '' ? $value : $default;
    }

    public static function put(string $name, ?string $value): void
    {
        static::query()->updateOrCreate(['name' => $name], ['value' => $value]);
    }
}
