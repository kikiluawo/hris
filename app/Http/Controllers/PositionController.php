<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Department;
use App\Models\EmployeeTransfer;
use App\Models\Position;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PositionController extends Controller
{
    public function index(Request $request): View
    {
        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'company' => $request->query('company'),
            'department' => $request->query('department'),
            'status' => $request->query('status'),
        ];

        $perPage = $request->integer('per_page', 10);
        if (! in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }

        $positions = Position::with('department.company')
            ->withCount('employees')
            ->when($filters['q'] !== '', function ($query) use ($filters): void {
                $term = '%' . $filters['q'] . '%';
                $query->where(function ($sub) use ($term): void {
                    $sub->where('name', 'like', $term)
                        ->orWhereHas('department', function ($deptQuery) use ($term): void {
                            $deptQuery->where('name', 'like', $term)
                                ->orWhereHas('company', function ($companyQuery) use ($term): void {
                                    $companyQuery->where('name', 'like', $term)
                                        ->orWhere('code', 'like', $term);
                                });
                        });
                });
            })
            ->when($filters['company'], function ($query, $companyId): void {
                $query->whereHas('department', fn ($deptQuery) => $deptQuery->where('company_id', $companyId));
            })
            ->when($filters['department'], fn ($query, $departmentId) => $query->where('department_id', $departmentId))
            ->when($filters['status'] === 'active', fn ($query) => $query->where('is_active', true))
            ->when($filters['status'] === 'inactive', fn ($query) => $query->where('is_active', false))
            ->orderBy('department_id')
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        return view('organization.positions', [
            'positions' => $positions,
            'companies' => Company::orderBy('name')->get(),
            'departments' => Department::with('company')->orderBy('company_id')->orderBy('name')->get(),
            'filters' => $filters,
            'perPage' => $perPage,
            'editing' => Position::find($request->integer('edit')),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'department_id' => ['required', 'exists:departments,id'],
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('positions', 'name')->where(fn ($query) => $query->where('department_id', $request->input('department_id'))),
            ],
        ]);

        Position::create([...$data, 'is_active' => $request->boolean('is_active')]);

        return to_route('positions.index')->with('status', "Jabatan {$data['name']} berhasil ditambahkan.");
    }

    public function update(Request $request, Position $position): RedirectResponse
    {
        $data = $request->validate([
            'department_id' => ['required', 'exists:departments,id'],
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('positions', 'name')
                    ->where(fn ($query) => $query->where('department_id', $request->input('department_id')))
                    ->ignore($position->id),
            ],
        ]);

        $usedInHistory = EmployeeTransfer::where('from_position_id', $position->id)
            ->orWhere('to_position_id', $position->id)
            ->exists();

        if ($position->department_id !== (int) $data['department_id'] && ($position->employees()->exists() || $usedInHistory)) {
            return back()
                ->with('error', "Departemen untuk jabatan {$position->name} tidak bisa diubah karena masih dipakai karyawan atau riwayat mutasi.")
                ->withInput();
        }

        $position->update([...$data, 'is_active' => $request->boolean('is_active')]);

        return to_route('positions.index')->with('status', "Jabatan {$position->name} diperbarui.");
    }

    public function destroy(Position $position): RedirectResponse
    {
        $usedInHistory = EmployeeTransfer::where('from_position_id', $position->id)
            ->orWhere('to_position_id', $position->id)
            ->exists();

        if ($position->employees()->exists() || $usedInHistory) {
            return back()->with('error', "Jabatan {$position->name} masih dipakai karyawan atau riwayat mutasi sehingga tidak bisa dihapus.");
        }

        $position->delete();

        return to_route('positions.index')->with('status', "Jabatan {$position->name} dihapus.");
    }
}
