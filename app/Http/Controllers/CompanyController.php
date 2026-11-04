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
        return view('organization.companies', [
            'companies' => Company::withCount(['branches', 'employees'])->orderBy('name')->get(),
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
