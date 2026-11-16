<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Application extends Model
{
    public const STATUS_NEW = 'new';

    public const STATUS_REVIEW = 'review';

    public const STATUS_SCREENING_PASSED = 'screening_passed';

    public const STATUS_SCREENING_FAILED = 'screening_failed';

    public const STATUS_FORM_SENT = 'form_sent';

    public const STATUS_FORM_COMPLETED = 'form_completed';

    public const STATUS_INTERVIEW_SCHEDULED = 'interview_scheduled';

    public const STATUS_ASSESSMENT_SENT = 'assessment_sent';

    public const STATUS_ASSESSMENT_DONE = 'assessment_done';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_HIRED = 'hired';

    protected $fillable = [
        'job_opening_id',
        'candidate_id',
        'source',
        'status',
        'screening_score',
        'screening_data',
        'screened_at',
        'screening_notified_at',
        'interview_at',
        'interview_mode',
        'interview_location',
        'interview_notes',
        'interview_sent_at',
        'decided_at',
        'decided_by',
    ];

    protected function casts(): array
    {
        return [
            'screening_data' => 'array',
            'screened_at' => 'datetime',
            'screening_notified_at' => 'datetime',
            'interview_at' => 'datetime',
            'interview_sent_at' => 'datetime',
            'decided_at' => 'datetime',
            'screening_score' => 'integer',
        ];
    }

    public function jobOpening(): BelongsTo
    {
        return $this->belongsTo(JobOpening::class);
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    public function decider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }

    public function assessmentInvitations(): HasMany
    {
        return $this->hasMany(AssessmentInvitation::class);
    }

    public function jobApplicationForm(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(JobApplicationForm::class);
    }

    public function employeeBiodata(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(EmployeeBiodata::class);
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_NEW => 'Baru',
            self::STATUS_REVIEW => 'Perlu review',
            self::STATUS_SCREENING_PASSED => 'Lolos screening',
            self::STATUS_SCREENING_FAILED => 'Tidak lolos screening',
            self::STATUS_FORM_SENT => 'Formulir dikirim',
            self::STATUS_FORM_COMPLETED => 'Formulir terisi',
            self::STATUS_INTERVIEW_SCHEDULED => 'Interview',
            self::STATUS_ASSESSMENT_SENT => 'Tes online',
            self::STATUS_ASSESSMENT_DONE => 'Tes selesai',
            self::STATUS_REJECTED => 'Belum sesuai',
            self::STATUS_HIRED => 'Diterima',
        ];
    }

    public function statusLabel(): string
    {
        return self::statusOptions()[$this->status] ?? ucfirst(str_replace('_', ' ', (string) $this->status));
    }

    public function statusTone(): string
    {
        return match ($this->status) {
            self::STATUS_SCREENING_PASSED, self::STATUS_HIRED, self::STATUS_ASSESSMENT_DONE, self::STATUS_FORM_COMPLETED => 'success',
            self::STATUS_INTERVIEW_SCHEDULED, self::STATUS_ASSESSMENT_SENT => 'info',
            self::STATUS_FORM_SENT, self::STATUS_REVIEW => 'warning',
            self::STATUS_SCREENING_FAILED, self::STATUS_REJECTED => 'danger',
            default => 'neutral',
        };
    }

    public function hasPassedScreening(): bool
    {
        return in_array($this->status, [
            self::STATUS_SCREENING_PASSED,
            self::STATUS_FORM_SENT,
            self::STATUS_FORM_COMPLETED,
            self::STATUS_INTERVIEW_SCHEDULED,
            self::STATUS_ASSESSMENT_SENT,
            self::STATUS_ASSESSMENT_DONE,
            self::STATUS_HIRED,
        ], true);
    }

    public function interviewModeLabel(): ?string
    {
        return match ($this->interview_mode) {
            'onsite' => 'Hadir langsung',
            'online' => 'Online / video call',
            'phone' => 'Telepon',
            default => $this->interview_mode,
        };
    }
}
