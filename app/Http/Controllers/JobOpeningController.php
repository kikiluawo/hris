<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Department;
use App\Models\JobOpening;
use App\Models\Position;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class JobOpeningController extends Controller
{
    public function index(Request $request): View
    {
        return view('recruitment.openings', [
            'openings' => JobOpening::with(['company', 'department', 'position'])
                ->withCount('applications')
                ->latest()
                ->get(),
            'companies' => Company::orderBy('name')->pluck('name', 'id'),
            'departments' => Department::orderBy('name')->pluck('name', 'id'),
            'positions' => Position::orderBy('name')->pluck('name', 'id'),
            'editing' => JobOpening::find($request->integer('edit')),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        JobOpening::create([
            ...$data,
            'slug' => $this->uniqueSlug($data['title']),
            'is_open' => $request->boolean('is_open'),
        ]);

        return to_route('openings.index')->with('status', "Lowongan {$data['title']} berhasil dibuat.");
    }

    public function update(Request $request, JobOpening $jobOpening): RedirectResponse
    {
        $data = $this->validated($request);

        $jobOpening->update([
            ...$data,
            'is_open' => $request->boolean('is_open'),
        ]);

        return to_route('openings.index')->with('status', "Lowongan {$jobOpening->title} diperbarui.");
    }

    public function toggleStatus(JobOpening $jobOpening): RedirectResponse
    {
        $jobOpening->update(['is_open' => ! $jobOpening->is_open]);

        return to_route('openings.index')->with('status', $jobOpening->is_open
            ? "Lowongan {$jobOpening->title} diaktifkan kembali dan tampil di halaman karir."
            : "Lowongan {$jobOpening->title} dinonaktifkan dan tidak tampil di halaman karir.");
    }

    public function destroy(JobOpening $jobOpening): RedirectResponse
    {
        if ($jobOpening->applications()->exists()) {
            return back()->with('error', "Lowongan {$jobOpening->title} sudah memiliki pelamar sehingga tidak bisa dihapus.");
        }

        $jobOpening->delete();

        return to_route('openings.index')->with('status', "Lowongan {$jobOpening->title} dihapus.");
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'company_id' => ['nullable', 'exists:companies,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'position_id' => ['nullable', 'exists:positions,id'],
            'description' => ['required', 'string', 'max:20000'],
        ]);
    }

    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'lowongan';
        $slug = $base;
        $suffix = 2;

        while (JobOpening::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
