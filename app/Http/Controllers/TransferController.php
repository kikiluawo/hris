<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeTransfer;
use App\Models\Position;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TransferController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->only(['q', 'company']);

        $transfers = EmployeeTransfer::with([
            'employee', 'fromCompany', 'fromBranch', 'fromDepartment', 'fromPosition',
            'toCompany', 'toBranch', 'toDepartment', 'toPosition',
        ])
            ->when($filters['q'] ?? null, fn ($query, string $term) => $query->whereHas(
                'employee',
                fn ($query) => $query->search($term)
            ))
            ->when($filters['company'] ?? null, fn ($query, string $company) => $query->where(
                fn ($query) => $query->where('from_company_id', $company)->orWhere('to_company_id', $company)
            ))
            ->orderByDesc('effective_date')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('transfers.index', [
            'transfers' => $transfers,
            'filters' => $filters,
            'companies' => Company::orderBy('name')->get(),
            'totalTransfers' => EmployeeTransfer::count(),
            'changedPositions' => EmployeeTransfer::where('position_changed', true)->count(),
        ]);
    }

    public function create(Request $request): View
    {
        $companies = Company::with(['branches', 'departments.positions'])->orderBy('name')->get();
        $employees = Employee::with(['company', 'branch', 'department', 'position'])->orderBy('name')->get();

        return view('transfers.create', [
            'companies' => $companies,
            'employees' => $employees,
            'organization' => $companies->map(fn (Company $company) => [
                'id' => $company->id,
                'name' => $company->name,
                'branches' => $company->branches->map(fn (Branch $branch) => [
                    'id' => $branch->id,
                    'name' => $branch->name,
                ])->values(),
                'departments' => $company->departments->map(fn (Department $department) => [
                    'id' => $department->id,
                    'name' => $department->name,
                    'positions' => $department->positions->map(fn (Position $position) => [
                        'id' => $position->id,
                        'name' => $position->name,
                    ])->values(),
                ])->values(),
            ])->values(),
            'assignments' => $employees->mapWithKeys(fn (Employee $employee) => [
                $employee->id => [
                    'company_id' => $employee->company_id,
                    'branch_id' => $employee->branch_id,
                    'department_id' => $employee->department_id,
                    'position_id' => $employee->position_id,
                ],
            ]),
            'selectedEmployeeId' => $request->integer('employee') ?: null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge(['to_branch_id' => $request->input('to_branch_id') ?: null]);

        $data = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'to_company_id' => ['required', 'exists:companies,id'],
            'to_branch_id' => ['nullable', 'exists:branches,id'],
            'to_department_id' => ['required', 'exists:departments,id'],
            'to_position_id' => ['required', 'exists:positions,id'],
            'effective_date' => ['required', 'date'],
            'reason' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $employee = Employee::with(['company', 'department', 'position'])->findOrFail($data['employee_id']);

        $branch = $data['to_branch_id'] ? Branch::find($data['to_branch_id']) : null;
        $department = Department::findOrFail($data['to_department_id']);
        $position = Position::findOrFail($data['to_position_id']);

        $errors = [];

        if ($branch && $branch->company_id !== (int) $data['to_company_id']) {
            $errors['to_branch_id'] = 'Cabang tujuan tidak berada di PT yang dipilih.';
        }

        if ($department->company_id !== (int) $data['to_company_id']) {
            $errors['to_department_id'] = 'Departemen tujuan tidak berada di PT yang dipilih.';
        }

        if ($position->department_id !== $department->id) {
            $errors['to_position_id'] = 'Jabatan tujuan tidak berada di departemen yang dipilih.';
        }

        $unchanged = $employee->company_id === (int) $data['to_company_id']
            && $employee->branch_id === ($data['to_branch_id'] ? (int) $data['to_branch_id'] : null)
            && $employee->department_id === (int) $data['to_department_id']
            && $employee->position_id === (int) $data['to_position_id'];

        if ($unchanged) {
            $errors['to_company_id'] = 'Tujuan mutasi masih sama dengan penempatan karyawan saat ini.';
        }

        if ($errors) {
            return back()->withErrors($errors)->withInput();
        }

        $from = [
            'from_company_id' => $employee->company_id,
            'from_branch_id' => $employee->branch_id,
            'from_department_id' => $employee->department_id,
            'from_position_id' => $employee->position_id,
        ];

        DB::transaction(function () use ($employee, $from, $data, $position) {
            EmployeeTransfer::create([
                ...$from,
                'employee_id' => $employee->id,
                'to_company_id' => $data['to_company_id'],
                'to_branch_id' => $data['to_branch_id'],
                'to_department_id' => $data['to_department_id'],
                'to_position_id' => $data['to_position_id'],
                'effective_date' => $data['effective_date'],
                'position_changed' => $employee->position->name !== $position->name,
                'reason' => $data['reason'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            $employee->update([
                'company_id' => $data['to_company_id'],
                'branch_id' => $data['to_branch_id'],
                'department_id' => $data['to_department_id'],
                'position_id' => $data['to_position_id'],
            ]);
        });

        $company = Company::find($data['to_company_id']);

        return to_route('employees.show', $employee)->with(
            'status',
            "Mutasi {$employee->name} ke {$company->name} berhasil disimpan."
        );
    }
}
