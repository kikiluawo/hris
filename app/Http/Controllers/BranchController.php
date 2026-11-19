<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Company;
use App\Models\EmployeeTransfer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BranchController extends Controller
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

        $branches = Branch::with('company')
            ->withCount('employees')
            ->when($filters['q'] !== '', function ($query) use ($filters): void {
                $term = '%' . $filters['q'] . '%';
                $query->where(function ($sub) use ($term): void {
                    $sub->where('name', 'like', $term)
                        ->orWhere('code', 'like', $term)
                        ->orWhere('city', 'like', $term)
                        ->orWhere('brand', 'like', $term)
                        ->orWhere('type', 'like', $term)
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

        return view('organization.branches', [
            'branches' => $branches,
            'companies' => Company::orderBy('name')->get(),
            'filters' => $filters,
            'perPage' => $perPage,
            'editing' => Branch::find($request->integer('edit')),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'company_id' => ['required', 'exists:companies,id'],
            'code' => ['nullable', 'string', 'max:30', 'alpha_dash', Rule::unique('branches', 'code')],
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('branches', 'name')->where(fn ($query) => $query->where('company_id', $request->input('company_id'))),
            ],
            'brand' => ['nullable', 'string', 'max:50'],
            'type' => ['nullable', 'string', 'max:20'],
            'city' => ['nullable', 'string', 'max:255'],
        ]);

        Branch::create([...$data, 'is_active' => $request->boolean('is_active')]);

        return to_route('branches.index')->with('status', "Cabang {$data['name']} berhasil ditambahkan.");
    }

    public function update(Request $request, Branch $branch): RedirectResponse
    {
        $data = $request->validate([
            'company_id' => ['required', 'exists:companies,id'],
            'code' => ['nullable', 'string', 'max:30', 'alpha_dash', Rule::unique('branches', 'code')->ignore($branch->id)],
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('branches', 'name')
                    ->where(fn ($query) => $query->where('company_id', $request->input('company_id')))
                    ->ignore($branch->id),
            ],
            'brand' => ['nullable', 'string', 'max:50'],
            'type' => ['nullable', 'string', 'max:20'],
            'city' => ['nullable', 'string', 'max:255'],
        ]);

        $usedInHistory = EmployeeTransfer::where('from_branch_id', $branch->id)
            ->orWhere('to_branch_id', $branch->id)
            ->exists();

        if ($branch->company_id !== (int) $data['company_id'] && ($branch->employees()->exists() || $usedInHistory)) {
            return back()
                ->with('error', "PT untuk cabang {$branch->name} tidak bisa diubah karena masih dipakai karyawan atau riwayat mutasi.")
                ->withInput();
        }

        $branch->update([...$data, 'is_active' => $request->boolean('is_active')]);

        return to_route('branches.index')->with('status', "Cabang {$branch->name} diperbarui.");
    }

    public function destroy(Branch $branch): RedirectResponse
    {
        $usedInHistory = EmployeeTransfer::where('from_branch_id', $branch->id)
            ->orWhere('to_branch_id', $branch->id)
            ->exists();

        if ($branch->employees()->exists() || $usedInHistory) {
            return back()->with('error', "Cabang {$branch->name} masih dipakai karyawan atau riwayat mutasi sehingga tidak bisa dihapus.");
        }

        $branch->delete();

        return to_route('branches.index')->with('status', "Cabang {$branch->name} dihapus.");
    }
}
