<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HiringRequest extends Model
{
    public const STATUS_NEW = 'baru';

    public const STATUS_REVIEWED = 'direview';

    public const STATUS_PUBLISHED = 'terbit';

    public const STATUS_REJECTED = 'ditolak';

    public const TOKEN_SETTING = 'hiring_request_token';

    protected $fillable = [
        'company_id',
        'department_id',
        'position_id',
        'requester_name',
        'requester_position',
        'requester_phone',
        'requester_email',
        'requesting_unit',
        'title',
        'headcount',
        'description',
        'required_skills',
        'min_education',
        'min_experience',
        'screening_threshold',
        'notes',
        'status',
        'hr_notes',
        'rejection_reason',
        'job_opening_id',
        'reviewed_by',
        'reviewed_at',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'required_skills' => 'array',
            'headcount' => 'integer',
            'min_experience' => 'integer',
            'screening_threshold' => 'integer',
            'reviewed_at' => 'datetime',
            'published_at' => 'datetime',
        ];
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_NEW => 'Baru',
            self::STATUS_REVIEWED => 'Ditinjau',
            self::STATUS_PUBLISHED => 'Terbit',
            self::STATUS_REJECTED => 'Ditolak',
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

    public function jobOpening(): BelongsTo
    {
        return $this->belongsTo(JobOpening::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public static function requestToken(): string
    {
        return (string) (Setting::get(self::TOKEN_SETTING) ?: config('recruitment.request_token'));
    }

    public static function rotateRequestToken(): string
    {
        $token = bin2hex(random_bytes(20));
        Setting::put(self::TOKEN_SETTING, $token);

        return $token;
    }

    public function reference(): string
    {
        return 'REQ-' . str_pad((string) $this->id, 4, '0', STR_PAD_LEFT);
    }
}
