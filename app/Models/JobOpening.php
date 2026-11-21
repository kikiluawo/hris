<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class JobOpening extends Model
{
    public static function parseSkills(?string $value): array
    {
        $lines = preg_split('/[\n;]+/u', (string) $value) ?: [];
        $skills = [];

        foreach ($lines as $line) {
            $line = trim(preg_replace('/^[\s\-•*·]+/u', '', $line) ?? $line);

            if ($line === '') {
                continue;
            }

            $parts = [$line];

            if (! preg_match('/\.\s*$/u', $line) && str_contains($line, ',')) {
                $candidates = array_values(array_filter(array_map('trim', explode(',', $line)), fn ($part) => $part !== ''));

                if (count($candidates) > 1 && array_filter($candidates, fn ($part) => count(preg_split('/\s+/u', $part, -1, PREG_SPLIT_NO_EMPTY) ?: []) > 8) === []) {
                    $parts = $candidates;
                }
            }

            foreach ($parts as $part) {
                $part = trim($part, " \t\n\r\0\x0B-•*·");

                if ($part !== '') {
                    $skills[Str::lower($part)] ??= $part;
                }
            }
        }

        return array_slice(array_values($skills), 0, 20);
    }

    public const TYPE_FULL_TIME = 'Full Time';

    public const TYPE_PART_TIME = 'Part Time';

    public const TYPE_INTERNSHIP = 'Internship';

    public const JOB_TYPES = [
        self::TYPE_FULL_TIME,
        self::TYPE_PART_TIME,
        self::TYPE_INTERNSHIP,
    ];

    public static function jobTypeOptions(): array
    {
        return [
            self::TYPE_FULL_TIME => 'Full Time',
            self::TYPE_PART_TIME => 'Part Time',
            self::TYPE_INTERNSHIP => 'Internship',
        ];
    }

    protected $fillable = [
        'title',
        'job_type',
        'location',
        'slug',
        'company_id',
        'department_id',
        'position_id',
        'description',
        'required_skills',
        'min_education',
        'min_experience',
        'screening_threshold',
        'is_open',
    ];

    protected function casts(): array
    {
        return [
            'required_skills' => 'array',
            'min_experience' => 'integer',
            'screening_threshold' => 'integer',
            'is_open' => 'boolean',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }
}
