<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AssessmentInvitation extends Model
{
    protected $fillable = [
        'assessment_id',
        'application_id',
        'token',
        'duration_minutes',
        'expires_at',
        'revoked_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'duration_minutes' => 'integer',
            'expires_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function attempt(): HasOne
    {
        return $this->hasOne(AssessmentAttempt::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function link(): string
    {
        return route('assessment.landing', $this->token);
    }

    public function durationMinutes(): int
    {
        return $this->duration_minutes ?? (int) $this->assessment->duration_minutes;
    }

    public function candidateName(): string
    {
        return $this->application?->candidate?->name ?? 'Kandidat';
    }

    public function candidateEmail(): string
    {
        return $this->application?->candidate?->email ?? '—';
    }

    public function isRevoked(): bool
    {
        return $this->revoked_at !== null;
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isUsable(): bool
    {
        return ! $this->isRevoked() && ! $this->isExpired() && $this->assessment->isPublished();
    }

    public function blockedReason(): ?string
    {
        return match (true) {
            $this->isRevoked() => 'Undangan ini sudah dicabut oleh HRD.',
            ! $this->assessment->isPublished() => 'Tes ini sedang ditutup oleh HRD.',
            $this->isExpired() => 'Undangan ini sudah kedaluwarsa pada ' . $this->expires_at->translatedFormat('d M Y H:i') . '.',
            default => null,
        };
    }

    public function statusLabel(): string
    {
        return match (true) {
            $this->attempt !== null => $this->attempt->statusLabel(),
            $this->isRevoked() => 'Dicabut',
            $this->isExpired() => 'Kedaluwarsa',
            default => 'Belum dikerjakan',
        };
    }

    public function statusTone(): string
    {
        return match (true) {
            $this->attempt !== null => $this->attempt->statusTone(),
            $this->isRevoked() => 'danger',
            $this->isExpired() => 'warning',
            default => 'neutral',
        };
    }
}