<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    public const ROLE_HRD = 'hrd';

    public const ROLE_CEO = 'ceo';

    public const ROLE_CFO = 'cfo';

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_hrd',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_hrd' => 'boolean',
        ];
    }

    public function isHrd(): bool
    {
        return $this->is_hrd === true || in_array($this->role, [self::ROLE_HRD, self::ROLE_CEO, self::ROLE_CFO], true);
    }

    public function isCeo(): bool
    {
        return $this->role === self::ROLE_CEO;
    }

    public function isCfo(): bool
    {
        return $this->role === self::ROLE_CFO;
    }

    public function isExecutive(): bool
    {
        return in_array($this->role, [self::ROLE_CEO, self::ROLE_CFO], true);
    }

    public function isPureHrd(): bool
    {
        return ! $this->isExecutive();
    }

    public function roleLabel(): string
    {
        return match ($this->role) {
            self::ROLE_CEO => 'CEO',
            self::ROLE_CFO => 'CFO',
            default => 'HRD',
        };
    }
}
