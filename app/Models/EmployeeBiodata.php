<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeBiodata extends Model
{
    protected $table = 'employee_biodatas';

    protected $fillable = [
        'application_id',
        'token',
        'submitted_at',
        'status',
        'personal_data',
        'bank_accounts',
        'education_formal',
        'education_informal',
        'work_experiences',
        'residences_history',
        'family_details',
        'organization_history',
        'attachments',
        'signed_city',
        'signed_name',
        'signed_date',
        'digital_signature',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'signed_date' => 'date',
            'personal_data' => 'array',
            'bank_accounts' => 'array',
            'education_formal' => 'array',
            'education_informal' => 'array',
            'work_experiences' => 'array',
            'residences_history' => 'array',
            'family_details' => 'array',
            'organization_history' => 'array',
            'attachments' => 'array',
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
        return route('employee-biodatas.show', $this->token);
    }
}
