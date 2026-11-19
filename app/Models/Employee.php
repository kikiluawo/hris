<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    protected $fillable = [
        'nik',
        'name',
        'email',
        'phone',
        'company_id',
        'branch_id',
        'department_id',
        'position_id',
        'employment_status',
        'joined_at',
        'salary',
        'is_active',
        'contract_1_start',
        'contract_1_end',
        'contract_2_start',
        'contract_2_end',
        'note',
        'birth_place',
        'birth_date',
        'gender',
        'religion',
        'marital_status',
        'daughters_count',
        'daughters_names',
        'sons_count',
        'sons_names',
        'address',
        'ktp_no',
        'npwp_no',
        'bpjs_tk_no',
        'bpjs_kes_no',
        'spouse_name',
        'father_name',
        'mother_name',
        'emergency_phone',
    ];

    protected function casts(): array
    {
        return [
            'joined_at' => 'date',
            'salary' => 'integer',
            'is_active' => 'boolean',
            'contract_1_start' => 'date',
            'contract_1_end' => 'date',
            'contract_2_start' => 'date',
            'contract_2_end' => 'date',
            'birth_date' => 'date',
            'daughters_count' => 'integer',
            'sons_count' => 'integer',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function transfers(): HasMany
    {
        return $this->hasMany(EmployeeTransfer::class)->orderByDesc('effective_date')->orderByDesc('id');
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        return $query->when($term, function (Builder $query, string $term) {
            $query->where(function (Builder $query) use ($term) {
                $query->where('name', 'like', "%{$term}%")
                    ->orWhere('nik', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%")
                    ->orWhere('ktp_no', 'like', "%{$term}%");
            });
        });
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->search($filters['q'] ?? null)
            ->when($filters['company'] ?? null, fn (Builder $query, string $company) => $query->where('company_id', $company))
            ->when($filters['branch'] ?? null, fn (Builder $query, string $branch) => $query->where('branch_id', $branch))
            ->when($filters['department'] ?? null, fn (Builder $query, string $department) => $query->where('department_id', $department))
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('employment_status', $status));
    }

    public static function employmentStatusOptions(): array
    {
        return [
            'permanent' => 'Tetap',
            'contract' => 'Kontrak',
            'probation' => 'Probation',
            'intern' => 'Magang',
        ];
    }

    public static function generateStaffNumber(Company|int|string $company, \DateTimeInterface|string|null $joinedAt = null): string
    {
        $companyModel = $company instanceof Company ? $company : Company::find($company);
        $companyCode = strtoupper(trim((string) ($companyModel?->code ?? 'EMP')));

        $date = $joinedAt instanceof \DateTimeInterface
            ? \Illuminate\Support\Carbon::instance($joinedAt)
            : ($joinedAt ? \Illuminate\Support\Carbon::parse($joinedAt) : now());

        $year = $date->format('Y');
        $month = $date->format('m');
        $prefix = "{$companyCode}-{$year}.{$month}.";

        $highestSeq = self::where('nik', 'like', "{$prefix}%")
            ->pluck('nik')
            ->map(function ($nik) use ($prefix) {
                $suffix = substr($nik, strlen($prefix));
                return is_numeric($suffix) ? (int) $suffix : 0;
            })
            ->max() ?? 0;

        $nextSeq = $highestSeq + 1;

        return "{$prefix}" . str_pad((string) $nextSeq, 3, '0', STR_PAD_LEFT);
    }

    public function employmentStatusLabel(): string
    {
        return match ($this->employment_status) {
            'permanent' => 'Tetap',
            'contract' => 'Kontrak',
            'probation' => 'Probation',
            'intern' => 'Magang',
            default => ucfirst((string) $this->employment_status),
        };
    }

    public function employmentStatusTone(): string
    {
        return match ($this->employment_status) {
            'permanent' => 'success',
            'contract' => 'warning',
            'probation' => 'indigo',
            'intern' => 'info',
            default => 'neutral',
        };
    }

    public function genderLabel(): ?string
    {
        return match ($this->gender) {
            'pria' => 'Pria',
            'wanita' => 'Wanita',
            default => null,
        };
    }

    public function birthLabel(): ?string
    {
        $place = trim((string) $this->birth_place);
        $date = $this->birth_date?->translatedFormat('d F Y');

        if ($place === '' && ! $date) {
            return null;
        }

        return trim($place . ($place !== '' && $date ? ', ' : '') . ($date ?? ''));
    }

    public function activeContractLabel(): ?string
    {
        if ($this->employment_status !== 'contract') {
            return null;
        }

        $today = now()->startOfDay();

        if ($this->contract_2_start && $this->contract_2_start->lte($today) && (! $this->contract_2_end || $this->contract_2_end->gte($today))) {
            return 'Kontrak II';
        }

        if ($this->contract_1_start && $this->contract_1_start->lte($today) && (! $this->contract_1_end || $this->contract_1_end->gte($today))) {
            return 'Kontrak I';
        }

        return null;
    }

    public function childrenLabel(): ?string
    {
        $daughters = (int) $this->daughters_count;
        $sons = (int) $this->sons_count;

        if ($daughters === 0 && $sons === 0) {
            return null;
        }

        return "{$daughters} perempuan · {$sons} laki-laki";
    }

    public function daughterNamesList(): array
    {
        return self::parseChildrenNames($this->daughters_names);
    }

    public function sonNamesList(): array
    {
        return self::parseChildrenNames($this->sons_names);
    }

    public static function parseChildrenNames(?string $raw): array
    {
        if ($raw === null) {
            return [];
        }

        $text = trim(preg_replace('/\s+/', ' ', $raw));
        if ($text === '' || in_array($text, ['-', '--', '.', '—'], true)) {
            return [];
        }

        $normalized = preg_replace("/['’]\s*-/", ' , ', $text);
        $normalized = preg_replace('/(?<=^|[\s,\/])-\s*(?=[A-Za-z])/', ' , ', $normalized);
        $items = preg_split('/\s*(?:,|;|\/|\n|&|\bdan\b)\s*/i', $normalized) ?: [];

        $clean = [];
        foreach ($items as $item) {
            $name = trim($item, " \t\n\r\0\x0B-,;'\"/");
            if ($name !== '' && ! in_array($name, ['-', '--', '.'], true)) {
                $clean[] = $name;
            }
        }

        return array_values(array_unique($clean));
    }
}
