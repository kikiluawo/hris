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
        return view('organization.branches', [
            'branches' => Branch::with('company')->withCount('employees')->orderBy('company_id')->orderBy('name')->get(),
            'companies' => Company::orderBy('name')->get(),
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
