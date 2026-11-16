<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentEvent extends Model
{
    protected $fillable = [
        'assessment_attempt_id',
        'type',
        'meta',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'occurred_at' => 'datetime',
        ];
    }

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(AssessmentAttempt::class, 'assessment_attempt_id');
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            'started' => 'Tes dimulai',
            'tab_hidden' => 'Kandidat meninggalkan tab',
            'tab_hidden_long' => 'Meninggalkan tab lebih dari 15 detik',
            'camera_lost' => 'Kamera terputus',
            'warning_acknowledged' => 'Peringatan dibaca kandidat',
            'submitted' => 'Jawaban dikirim',
            'time_up' => 'Waktu habis',
            'ended' => 'Tes dihentikan',
            default => ucfirst(str_replace('_', ' ', $this->type)),
        };
    }

    public function tone(): string
    {
        return match ($this->type) {
            'tab_hidden', 'tab_hidden_long', 'camera_lost', 'ended' => 'danger',
            'warning_acknowledged' => 'warning',
            'started', 'submitted' => 'success',
            default => 'neutral',
        };
    }
}
