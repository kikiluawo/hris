<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Department;
use App\Models\EmployeeTransfer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    public function index(Request $request): View
    {
        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'company' => $request->query('company'),
            'status' => $request->query('status'),
        ];

        $perPage = $request->integer('per_page', 10);
        if (! in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }

        $departments = Department::with('company')
            ->withCount(['positions', 'employees'])
            ->when($filters['q'] !== '', function ($query) use ($filters): void {
                $term = '%' . $filters['q'] . '%';
                $query->where(function ($sub) use ($term): void {
                    $sub->where('name', 'like', $term)
                        ->orWhereHas('company', function ($companyQuery) use ($term): void {
                            $companyQuery->where('name', 'like', $term)
                                ->orWhere('code', 'like', $term);
                        });
                });
            })
            ->when($filters['company'], fn ($query, $companyId) => $query->where('company_id', $companyId))
            ->when($filters['status'] === 'active', fn ($query) => $query->where('is_active', true))
            ->when($filters['status'] === 'inactive', fn ($query) => $query->where('is_active', false))
            ->orderBy('company_id')
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        return view('organization.departments', [
            'departments' => $departments,
            'companies' => Company::orderBy('name')->get(),
            'filters' => $filters,
            'perPage' => $perPage,
            'editing' => Department::find($request->integer('edit')),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'company_id' => ['required', 'exists:companies,id'],
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('departments', 'name')->where(fn ($query) => $query->where('company_id', $request->input('company_id'))),
            ],
        ]);

        Department::create([...$data, 'is_active' => $request->boolean('is_active')]);

        return to_route('departments.index')->with('status', "Departemen {$data['name']} berhasil ditambahkan.");
    }

    public function update(Request $request, Department $department): RedirectResponse
    {
        $data = $request->validate([
            'company_id' => ['required', 'exists:companies,id'],
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('departments', 'name')
                    ->where(fn ($query) => $query->where('company_id', $request->input('company_id')))
                    ->ignore($department->id),
            ],
        ]);

        $usedInHistory = EmployeeTransfer::where('from_department_id', $department->id)
            ->orWhere('to_department_id', $department->id)
            ->exists();

        if ($department->company_id !== (int) $data['company_id'] && ($department->positions()->exists() || $department->employees()->exists() || $usedInHistory)) {
            return back()
                ->with('error', "PT untuk departemen {$department->name} tidak bisa diubah karena masih memiliki jabatan, karyawan, atau riwayat mutasi.")
                ->withInput();
        }

        $department->update([...$data, 'is_active' => $request->boolean('is_active')]);

        return to_route('departments.index')->with('status', "Departemen {$department->name} diperbarui.");
    }

    public function destroy(Department $department): RedirectResponse
    {
        $usedInHistory = EmployeeTransfer::where('from_department_id', $department->id)
            ->orWhere('to_department_id', $department->id)
            ->exists();

        if ($department->employees()->exists() || $department->positions()->exists() || $usedInHistory) {
            return back()->with('error', "Departemen {$department->name} masih memiliki jabatan, karyawan, atau riwayat mutasi sehingga tidak bisa dihapus.");
        }

        $department->delete();

        return to_route('departments.index')->with('status', "Departemen {$department->name} dihapus.");
    }
}
