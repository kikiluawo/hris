<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssessmentAttempt extends Model
{
    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_SUBMITTED = 'submitted';

    public const STATUS_ENDED = 'ended';

    public const REASON_SUBMITTED = 'submitted';

    public const REASON_TIME_UP = 'time_up';

    public const REASON_VIOLATION = 'violation';

    public const REASON_TAB_HIDDEN = 'tab_hidden';

    public const REASON_CAMERA = 'camera';

    public const GRACE_MINUTES = 5;

    protected $fillable = [
        'assessment_invitation_id',
        'token',
        'access_token',
        'lock_token',
        'status',
        'started_at',
        'ends_at',
        'submitted_at',
        'score',
        'dimension_scores',
        'disc_report',
        'aptitude_report',
        'accuracy_report',
        'review_notes',
        'violation_count',
        'end_reason',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ends_at' => 'datetime',
            'submitted_at' => 'datetime',
            'score' => 'float',
            'dimension_scores' => 'array',
            'disc_report' => 'array',
            'aptitude_report' => 'array',
            'accuracy_report' => 'array',
            'violation_count' => 'integer',
        ];
    }

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(AssessmentInvitation::class, 'assessment_invitation_id');
    }

    public function candidateName(): string
    {
        return $this->invitation?->candidateName() ?? 'Kandidat';
    }

    public function candidateEmail(): string
    {
        return $this->invitation?->candidateEmail() ?? '—';
    }

    public function accessExpired(): bool
    {
        return $this->ends_at->copy()->addMinutes(self::GRACE_MINUTES)->isPast();
    }

    public function answers(): HasMany
    {
        return $this->hasMany(AssessmentAnswer::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(AssessmentEvent::class)->orderBy('occurred_at')->orderBy('id');
    }

    public function isInProgress(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    public function hasExpired(): bool
    {
        return $this->ends_at->isPast();
    }

    public function remainingSeconds(): int
    {
        return max(0, now()->diffInSeconds($this->ends_at, false));
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_IN_PROGRESS => 'Sedang dikerjakan',
            self::STATUS_SUBMITTED => 'Selesai',
            self::STATUS_ENDED => 'Dihentikan',
            default => ucfirst((string) $this->status),
        };
    }

    public function statusTone(): string
    {
        return match ($this->status) {
            self::STATUS_SUBMITTED => 'success',
            self::STATUS_ENDED => 'danger',
            default => 'warning',
        };
    }

    public function endReasonLabel(): ?string
    {
        return match ($this->end_reason) {
            self::REASON_SUBMITTED => 'Dikirim oleh kandidat',
            self::REASON_TIME_UP => 'Waktu habis',
            self::REASON_VIOLATION => 'Pelanggaran aturan',
            self::REASON_TAB_HIDDEN => 'Meninggalkan halaman terlalu lama',
            self::REASON_CAMERA => 'Kamera tidak aktif',
            default => $this->end_reason,
        };
    }

    public function scoreLabel(): string
    {
        if ($this->score === null) {
            return '—';
        }

        return rtrim(rtrim(number_format($this->score, 2, '.', ''), '0'), '.');
    }

    public function isFullyGraded(): bool
    {
        return $this->pendingEssayCount() === 0
            && $this->pendingDimensions() === [];
    }

    public function scaleDimensions(): array
    {
        $assessment = $this->invitation?->assessment;

        if ($assessment === null) {
            return [];
        }

        return $assessment->questions()
            ->where('type', AssessmentQuestion::TYPE_SCALE)
            ->pluck('dimension')
            ->map(fn ($dimension) => filled($dimension) ? trim((string) $dimension) : 'Umum')
            ->unique()
            ->values()
            ->all();
    }

    public function pendingDimensions(): array
    {
        $scores = $this->dimension_scores ?? [];

        return array_values(array_filter(
            $this->scaleDimensions(),
            fn (string $dimension) => ! array_key_exists($dimension, $scores)
                || $scores[$dimension] === null
                || $scores[$dimension] === '',
        ));
    }

    public function answeredCount(): int
    {
        return $this->answers()->where(function ($query) {
            $query->whereNotNull('answer')
                ->orWhereNotNull('selected_option')
                ->orWhere(function ($sub) {
                    $sub->whereNotNull('most_option')->whereNotNull('least_option');
                });
        })->count();
    }

    public function hasDiscQuestions(): bool
    {
        return $this->invitation?->assessment?->questions()
            ->where('type', AssessmentQuestion::TYPE_DISC)
            ->exists() ?? false;
    }

    public function hasAptitudeQuestions(): bool
    {
        if ($this->aptitude_report !== null) {
            return true;
        }

        $assessment = $this->invitation?->assessment;
        if ($assessment === null) {
            return false;
        }

        $title = strtolower((string) $assessment->title);
        if (str_contains($title, 'aptitude') || str_contains($title, 'iq') || str_contains($title, 'bakat') || str_contains($title, 'kognitif') || str_contains($title, 'tpa') || str_contains($title, 'skolastik')) {
            return true;
        }

        return $assessment->questions()
            ->where('type', AssessmentQuestion::TYPE_MULTIPLE_CHOICE)
            ->where(function ($query) {
                $query->where('dimension', 'like', '%verbal%')
                    ->orWhere('dimension', 'like', '%numerik%')
                    ->orWhere('dimension', 'like', '%angka%')
                    ->orWhere('dimension', 'like', '%logika%')
                    ->orWhere('dimension', 'like', '%penalaran%')
                    ->orWhere('dimension', 'like', '%spasial%')
                    ->orWhere('dimension', 'like', '%figural%');
            })
            ->exists();
    }

    public function hasAccuracyQuestions(): bool
    {
        if ($this->accuracy_report !== null) {
            return true;
        }

        $assessment = $this->invitation?->assessment;
        if ($assessment === null) {
            return false;
        }

        $title = strtolower((string) $assessment->title);
        if (str_contains($title, 'ketelitian') || str_contains($title, 'akurasi') || str_contains($title, 'clerical') || str_contains($title, 'checking') || str_contains($title, 'pauli') || str_contains($title, 'kraepelin')) {
            return true;
        }

        return $assessment->questions()
            ->where('type', AssessmentQuestion::TYPE_MULTIPLE_CHOICE)
            ->where(function ($query) {
                $query->where('dimension', 'like', '%ketelitian%')
                    ->orWhere('dimension', 'like', '%akurasi%')
                    ->orWhere('dimension', 'like', '%clerical%')
                    ->orWhere('dimension', 'like', '%checking%')
                    ->orWhere('dimension', 'like', '%pencocokan%');
            })
            ->exists();
    }

    public function pendingEssayCount(): int
    {
        return $this->answers()
            ->whereHas('question', fn ($query) => $query->where('type', AssessmentQuestion::TYPE_ESSAY))
            ->whereNull('points_awarded')
            ->count();
    }
}
