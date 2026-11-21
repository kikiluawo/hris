<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Department;
use App\Models\JobOpening;
use App\Models\Position;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class JobOpeningController extends Controller
{
    public const EDUCATION_LEVELS = ['SMA/SMK', 'D3', 'S1', 'S2', 'S3'];

    public function index(Request $request): View
    {
        return view('recruitment.openings', [
            'openings' => JobOpening::with(['company', 'department', 'position'])
                ->withCount('applications')
                ->latest()
                ->get(),
            'organization' => $this->organizationTree(),
            'companies' => Company::orderBy('name')->pluck('name', 'id'),
            'departments' => Department::orderBy('name')->pluck('name', 'id'),
            'positions' => Position::orderBy('name')->pluck('name', 'id'),
            'educationLevels' => array_combine(self::EDUCATION_LEVELS, self::EDUCATION_LEVELS),
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
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'company_id' => ['nullable', 'exists:companies,id'],
            'department_id' => [
                'nullable',
                Rule::exists('departments', 'id')->where(fn ($query) => $query->where('company_id', $request->company_id)),
            ],
            'position_id' => [
                'nullable',
                Rule::exists('positions', 'id')->where(fn ($query) => $query->where('department_id', $request->department_id)),
            ],
            'description' => ['required', 'string', 'max:20000'],
            'required_skills' => ['nullable', 'string', 'max:1000'],
            'min_education' => ['nullable', Rule::in(self::EDUCATION_LEVELS)],
            'min_experience' => ['nullable', 'integer', 'min:0', 'max:50'],
            'screening_threshold' => ['nullable', 'integer', 'min:0', 'max:100'],
        ], [
            'department_id.exists' => 'Departemen yang dipilih tidak sesuai dengan PT yang dipilih.',
            'position_id.exists' => 'Jabatan yang dipilih tidak sesuai dengan departemen yang dipilih.',
        ]);

        if (empty($data['company_id'])) {
            $data['department_id'] = null;
            $data['position_id'] = null;
        } elseif (empty($data['department_id'])) {
            $data['position_id'] = null;
        }

        $skills = JobOpening::parseSkills($data['required_skills'] ?? null);

        $data['required_skills'] = $skills === [] ? null : $skills;
        $data['min_experience'] ??= null;
        $data['screening_threshold'] = (int) ($data['screening_threshold'] ?? 70);

        return $data;
    }

    private function organizationTree(): array
    {
        return Company::with([
            'departments' => fn ($query) => $query->orderBy('name'),
            'departments.positions' => fn ($query) => $query->orderBy('name'),
        ])
            ->orderBy('name')
            ->get()
            ->map(fn (Company $company) => [
                'id' => $company->id,
                'name' => $company->name,
                'departments' => $company->departments->map(fn (Department $department) => [
                    'id' => $department->id,
                    'name' => $department->name,
                    'positions' => $department->positions->map(fn (Position $position) => [
                        'id' => $position->id,
                        'name' => $position->name,
                    ])->values()->all(),
                ])->values()->all(),
            ])
            ->values()
            ->all();
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
