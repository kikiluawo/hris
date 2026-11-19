<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentSnapshot extends Model
{
    protected $fillable = [
        'assessment_attempt_id',
        'file_path',
        'trigger',
        'captured_at',
    ];

    protected function casts(): array
    {
        return [
            'captured_at' => 'datetime',
        ];
    }

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(AssessmentAttempt::class, 'assessment_attempt_id');
    }

    public function triggerLabel(): string
    {
        return match ($this->trigger) {
            'start' => 'Mulai tes',
            'periodic' => 'Berkala',
            'violation' => 'Pelanggaran',
            'submit' => 'Selesai',
            default => ucfirst((string) $this->trigger),
        };
    }

    public function triggerTone(): string
    {
        return match ($this->trigger) {
            'violation' => 'danger',
            'start', 'submit' => 'success',
            default => 'neutral',
        };
    }
}
