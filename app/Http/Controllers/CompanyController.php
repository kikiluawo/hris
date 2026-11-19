<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CompanyController extends Controller
{
    public function index(Request $request): View
    {
        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'status' => $request->query('status'),
        ];

        $perPage = $request->integer('per_page', 10);
        if (! in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }

        $companies = Company::withCount(['branches', 'employees'])
            ->when($filters['q'] !== '', function ($query) use ($filters): void {
                $term = '%' . $filters['q'] . '%';
                $query->where(function ($sub) use ($term): void {
                    $sub->where('name', 'like', $term)
                        ->orWhere('code', 'like', $term)
                        ->orWhere('address', 'like', $term)
                        ->orWhere('phone', 'like', $term);
                });
            })
            ->when($filters['status'] === 'active', fn ($query) => $query->where('is_active', true))
            ->when($filters['status'] === 'inactive', fn ($query) => $query->where('is_active', false))
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        return view('organization.companies', [
            'companies' => $companies,
            'filters' => $filters,
            'perPage' => $perPage,
            'editing' => Company::find($request->integer('edit')),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:10', 'alpha_dash', 'unique:companies,code'],
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        Company::create([
            ...$data,
            'code' => Str::upper($data['code']),
            'is_active' => $request->boolean('is_active'),
        ]);

        return to_route('companies.index')->with('status', "PT {$data['name']} berhasil ditambahkan.");
    }

    public function update(Request $request, Company $company): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:10', 'alpha_dash', 'unique:companies,code,' . $company->id],
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        $company->update([
            ...$data,
            'code' => Str::upper($data['code']),
            'is_active' => $request->boolean('is_active'),
        ]);

        return to_route('companies.index')->with('status', "Data PT {$company->name} diperbarui.");
    }

    public function destroy(Company $company): RedirectResponse
    {
        if ($company->employees()->exists() || $company->branches()->exists() || $company->departments()->exists()) {
            return back()->with('error', "PT {$company->name} masih memiliki cabang, departemen, atau karyawan sehingga tidak bisa dihapus.");
        }

        $company->delete();

        return to_route('companies.index')->with('status', "PT {$company->name} dihapus.");
    }
}
