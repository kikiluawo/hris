<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HiringRequest extends Model
{
    public const STATUS_NEW = 'baru';

    public const STATUS_REVIEWED = 'direview';

    public const STATUS_PENDING_APPROVAL = 'menunggu_approval';

    public const STATUS_PUBLISHED = 'terbit';

    public const STATUS_REJECTED = 'ditolak';

    public const TYPE_NEW = 'baru';

    public const TYPE_REPLACEMENT = 'pergantian';

    public const ROUTE_DIRECT = 'direct';

    public const ROUTE_CEO = 'ceo';

    public const ROUTE_CFO = 'cfo';

    public const ROUTE_BOTH = 'both';

    public const APPROVAL_PENDING = 'pending';

    public const APPROVAL_APPROVED = 'approved';

    public const APPROVAL_REJECTED = 'rejected';

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
        'request_type',
        'replaced_employee_name',
        'replacement_reason',
        'title',
        'job_type',
        'location',
        'headcount',
        'description',
        'required_skills',
        'min_education',
        'min_experience',
        'screening_threshold',
        'notes',
        'status',
        'approval_route',
        'ceo_approval_status',
        'ceo_approved_by',
        'ceo_approved_at',
        'ceo_notes',
        'cfo_approval_status',
        'cfo_approved_by',
        'cfo_approved_at',
        'cfo_notes',
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
            'ceo_approved_at' => 'datetime',
            'cfo_approved_at' => 'datetime',
        ];
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_NEW => 'Baru',
            self::STATUS_REVIEWED => 'Ditinjau HRD',
            self::STATUS_PENDING_APPROVAL => 'Menunggu Approval',
            self::STATUS_PUBLISHED => 'Terbit',
            self::STATUS_REJECTED => 'Ditolak',
        ];
    }

    public static function requestTypeOptions(): array
    {
        return [
            self::TYPE_NEW => 'Karyawan Baru',
            self::TYPE_REPLACEMENT => 'Pergantian Karyawan',
        ];
    }

    public static function approvalRouteOptions(): array
    {
        return [
            self::ROUTE_DIRECT => 'Langsung Terbitkan (Tanpa Approval CEO/CFO)',
            self::ROUTE_CEO => 'Perlu Approval CEO (Hadi Ong)',
            self::ROUTE_CFO => 'Perlu Approval CFO (Sri Rahayu)',
            self::ROUTE_BOTH => 'Perlu Approval Keduanya (CEO & CFO)',
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

    public function ceoApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ceo_approved_by');
    }

    public function cfoApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cfo_approved_by');
    }

    public function isReplacement(): bool
    {
        return $this->request_type === self::TYPE_REPLACEMENT;
    }

    public function isNewHire(): bool
    {
        return $this->request_type !== self::TYPE_REPLACEMENT;
    }

    public function needsCeoApproval(): bool
    {
        return in_array($this->approval_route, [self::ROUTE_CEO, self::ROUTE_BOTH], true);
    }

    public function needsCfoApproval(): bool
    {
        return in_array($this->approval_route, [self::ROUTE_CFO, self::ROUTE_BOTH], true);
    }

    public function isCeoPending(): bool
    {
        return $this->needsCeoApproval() && $this->ceo_approval_status === self::APPROVAL_PENDING;
    }

    public function isCfoPending(): bool
    {
        return $this->needsCfoApproval() && $this->cfo_approval_status === self::APPROVAL_PENDING;
    }

    public function isCeoApproved(): bool
    {
        return $this->needsCeoApproval() && $this->ceo_approval_status === self::APPROVAL_APPROVED;
    }

    public function isCfoApproved(): bool
    {
        return $this->needsCfoApproval() && $this->cfo_approval_status === self::APPROVAL_APPROVED;
    }

    public function isCeoRejected(): bool
    {
        return $this->ceo_approval_status === self::APPROVAL_REJECTED;
    }

    public function isCfoRejected(): bool
    {
        return $this->cfo_approval_status === self::APPROVAL_REJECTED;
    }

    public function canUserApprove(?User $user): bool
    {
        if (! $user || $this->status !== self::STATUS_PENDING_APPROVAL) {
            return false;
        }

        if ($user->isCeo() && $this->isCeoPending()) {
            return true;
        }

        if ($user->isCfo() && $this->isCfoPending()) {
            return true;
        }

        return false;
    }

    public function approvalSummary(): string
    {
        if ($this->status === self::STATUS_NEW) {
            return 'Menunggu Review HRD';
        }

        if ($this->status === self::STATUS_PUBLISHED) {
            return 'Sudah Terbit';
        }

        if ($this->status === self::STATUS_REJECTED) {
            return 'Ditolak';
        }

        if ($this->status === self::STATUS_PENDING_APPROVAL) {
            $parts = [];
            if ($this->needsCeoApproval()) {
                $parts[] = 'CEO: ' . ($this->ceo_approval_status === self::APPROVAL_APPROVED ? 'Disetujui' : 'Menunggu');
            }
            if ($this->needsCfoApproval()) {
                $parts[] = 'CFO: ' . ($this->cfo_approval_status === self::APPROVAL_APPROVED ? 'Disetujui' : 'Menunggu');
            }

            return implode(' · ', $parts) ?: 'Menunggu Approval';
        }

        return ucfirst($this->status);
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
