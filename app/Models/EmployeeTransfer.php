<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeTransfer extends Model
{
    protected $fillable = [
        'employee_id',
        'from_company_id',
        'from_branch_id',
        'from_department_id',
        'from_position_id',
        'to_company_id',
        'to_branch_id',
        'to_department_id',
        'to_position_id',
        'effective_date',
        'position_changed',
        'reason',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'effective_date' => 'date',
            'position_changed' => 'boolean',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function fromCompany(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'from_company_id');
    }

    public function fromBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'from_branch_id');
    }

    public function fromDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'from_department_id');
    }

    public function fromPosition(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'from_position_id');
    }

    public function toCompany(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'to_company_id');
    }

    public function toBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'to_branch_id');
    }

    public function toDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'to_department_id');
    }

    public function toPosition(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'to_position_id');
    }

    public function companyChanged(): bool
    {
        return $this->from_company_id !== $this->to_company_id;
    }

    public function branchChanged(): bool
    {
        return $this->from_branch_id !== $this->to_branch_id;
    }

    public function departmentChanged(): bool
    {
        return $this->from_department_id !== $this->to_department_id;
    }

    public function changedAreas(): string
    {
        $areas = [];

        if ($this->companyChanged()) {
            $areas[] = 'PT';
        }

        if ($this->branchChanged()) {
            $areas[] = 'cabang';
        }

        if ($this->departmentChanged()) {
            $areas[] = 'departemen';
        }

        if ($this->position_changed) {
            $areas[] = 'jabatan';
        }

        return implode(', ', $areas);
    }
}
