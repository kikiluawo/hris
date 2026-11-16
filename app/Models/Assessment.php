<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Assessment extends Model
{
    public const STATUS_DRAFT = 'draft';

    public const STATUS_PUBLISHED = 'published';

    public const STATUS_CLOSED = 'closed';

    protected $fillable = [
        'title',
        'description',
        'instructions',
        'duration_minutes',
        'passing_score',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'duration_minutes' => 'integer',
            'passing_score' => 'integer',
        ];
    }

    public function questions(): HasMany
    {
        return $this->hasMany(AssessmentQuestion::class)->orderBy('position')->orderBy('id');
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(AssessmentInvitation::class);
    }

    public function attempts(): HasManyThrough
    {
        return $this->hasManyThrough(AssessmentAttempt::class, AssessmentInvitation::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_DRAFT => 'Draf',
            self::STATUS_PUBLISHED => 'Aktif',
            self::STATUS_CLOSED => 'Ditutup',
        ];
    }

    public function statusLabel(): string
    {
        return self::statusOptions()[$this->status] ?? ucfirst((string) $this->status);
    }

    public function statusTone(): string
    {
        return match ($this->status) {
            self::STATUS_PUBLISHED => 'success',
            self::STATUS_CLOSED => 'neutral',
            default => 'warning',
        };
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    public function totalPoints(): int
    {
        return (int) $this->questions
            ->whereNotIn('type', [AssessmentQuestion::TYPE_SCALE, AssessmentQuestion::TYPE_DISC])
            ->sum('points');
    }

    public function isLocked(): bool
    {
        return $this->attempts()->exists();
    }

    public function hasDisc(): bool
    {
        return $this->questions->contains(fn ($q) => $q->type === AssessmentQuestion::TYPE_DISC);
    }

    public function hasAptitude(): bool
    {
        return $this->questions->contains(fn ($q) => in_array($q->dimension, ['Verbal', 'Numerik', 'Logika', 'Spasial'], true));
    }

    public function hasAccuracy(): bool
    {
        return $this->questions->contains(fn ($q) => in_array($q->dimension, ['Ketelitian', 'Clerical', 'Checking'], true));
    }

    public function isComprehensive(): bool
    {
        return $this->hasDisc() && $this->hasAptitude() && $this->hasAccuracy();
    }

    public function instrumentsSummary(): array
    {
        $instruments = [];

        if ($this->hasDisc()) {
            $count = $this->questions->where('type', AssessmentQuestion::TYPE_DISC)->count();
            $instruments[] = [
                'label' => "DISC ({$count})",
                'tone' => 'purple',
                'icon' => 'user',
            ];
        }

        if ($this->hasAptitude()) {
            $count = $this->questions->whereIn('dimension', ['Verbal', 'Numerik', 'Logika', 'Spasial'])->count();
            $instruments[] = [
                'label' => "Bakat IQ ({$count})",
                'tone' => 'brand',
                'icon' => 'calculator',
            ];
        }

        if ($this->hasAccuracy()) {
            $count = $this->questions->whereIn('dimension', ['Ketelitian', 'Clerical', 'Checking'])->count();
            $instruments[] = [
                'label' => "Ketelitian ({$count})",
                'tone' => 'emerald',
                'icon' => 'check',
            ];
        }

        if (empty($instruments)) {
            $scaleCount = $this->questions->where('type', AssessmentQuestion::TYPE_SCALE)->count();
            if ($scaleCount > 0) {
                $instruments[] = [
                    'label' => "Skala ({$scaleCount})",
                    'tone' => 'amber',
                    'icon' => 'clipboard',
                ];
            } else {
                $mcCount = $this->questions->where('type', AssessmentQuestion::TYPE_MULTIPLE_CHOICE)->count();
                $instruments[] = [
                    'label' => $mcCount > 0 ? "Pilihan Ganda ({$mcCount})" : 'Kustom',
                    'tone' => 'neutral',
                    'icon' => 'clipboard',
                ];
            }
        }

        return $instruments;
    }
}
