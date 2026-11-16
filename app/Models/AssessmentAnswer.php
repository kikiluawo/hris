<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentAnswer extends Model
{
    protected $fillable = [
        'assessment_attempt_id',
        'assessment_question_id',
        'selected_option',
        'most_option',
        'least_option',
        'answer',
        'is_correct',
        'points_awarded',
    ];

    protected function casts(): array
    {
        return [
            'selected_option' => 'integer',
            'most_option' => 'integer',
            'least_option' => 'integer',
            'is_correct' => 'boolean',
            'points_awarded' => 'float',
        ];
    }

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(AssessmentAttempt::class, 'assessment_attempt_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(AssessmentQuestion::class, 'assessment_question_id');
    }
}
