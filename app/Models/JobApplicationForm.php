<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobApplicationForm extends Model
{
    protected $fillable = [
        'application_id',
        'token',
        'submitted_at',
        'photo_path',
        'personal_data',
        'family_data',
        'education_data',
        'work_history',
        'additional_info',
        'office_use',
        'signature_name',
        'signed_date',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'signed_date' => 'date',
            'personal_data' => 'array',
            'family_data' => 'array',
            'education_data' => 'array',
            'work_history' => 'array',
            'additional_info' => 'array',
            'office_use' => 'array',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function isSubmitted(): bool
    {
        return $this->submitted_at !== null;
    }

    public function url(): string
    {
        return route('application-forms.show', $this->token);
    }
}
