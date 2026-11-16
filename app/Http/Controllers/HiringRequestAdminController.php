<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Department;
use App\Models\HiringRequest;
use App\Models\JobOpening;
use App\Models\Position;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class HiringRequestAdminController extends Controller
{
    public function index(Request $request): View
    {
        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'status' => (string) $request->query('status', ''),
        ];

        $like = $filters['q'] !== '' ? '%' . addcslashes($filters['q'], '\\%_') . '%' : null;

        $requests = HiringRequest::with('company')
            ->when(array_key_exists($filters['status'], HiringRequest::statusOptions()), fn ($query) => $query->where('status', $filters['status']))
            ->when($like !== null, function ($query) use ($like) {
                $query->where(function ($query) use ($like) {
                    $query->where('title', 'like', $like)
                        ->orWhere('requester_name', 'like', $like)
                        ->orWhere('requesting_unit', 'like', $like)
                        ->orWhereHas('company', fn ($company) => $company->where('name', 'like', $like));
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('recruitment.hiring-requests', [
            'requests' => $requests,
            'statuses' => HiringRequest::statusOptions(),
            'counts' => HiringRequest::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
            'filters' => $filters,
            'token' => HiringRequest::requestToken(),
        ]);
    }

    public function rotateToken(): RedirectResponse
    {
        HiringRequest::rotateRequestToken();

        return back()->with('status', 'Token form permintaan sudah diganti. Link lama tidak berlaku lagi.');
    }

    public function show(HiringRequest $hiringRequest): View
    {
        $hiringRequest->load(['company', 'department', 'position', 'jobOpening', 'reviewer']);

        return view('recruitment.hiring-request-review', [
            'hiringRequest' => $hiringRequest,
            'companies' => Company::orderBy('name')->pluck('name', 'id'),
            'departments' => Department::orderBy('name')->pluck('name', 'id'),
            'positions' => Position::orderBy('name')->pluck('name', 'id'),
            'educationLevels' => array_combine(JobOpeningController::EDUCATION_LEVELS, JobOpeningController::EDUCATION_LEVELS),
            'editable' => $this->editable($hiringRequest),
        ]);
    }

    public function update(Request $request, HiringRequest $hiringRequest): RedirectResponse
    {
        if (! $this->editable($hiringRequest)) {
            return back()->with('error', 'Permintaan ini sudah dikunci dan tidak bisa diubah.');
        }

        $data = $this->validated($request);

        $hiringRequest->update([
            ...$data,
            'status' => $hiringRequest->status === HiringRequest::STATUS_NEW ? HiringRequest::STATUS_REVIEWED : $hiringRequest->status,
            'reviewed_by' => $request->user()?->id,
            'reviewed_at' => now(),
        ]);

        return back()->with('status', 'Perubahan permintaan disimpan.');
    }

    public function publish(Request $request, HiringRequest $hiringRequest): RedirectResponse
    {
        if ($hiringRequest->status === HiringRequest::STATUS_PUBLISHED) {
            return back()->with('error', 'Permintaan ini sudah diterbitkan.');
        }

        if ($hiringRequest->status === HiringRequest::STATUS_REJECTED) {
            return back()->with('error', 'Permintaan yang sudah ditolak tidak bisa diterbitkan.');
        }

        $data = $this->validated($request);

        $opening = DB::transaction(function () use ($hiringRequest, $data, $request) {
            $hiringRequest->update([
                ...$data,
                'status' => HiringRequest::STATUS_PUBLISHED,
                'reviewed_by' => $request->user()?->id,
                'reviewed_at' => now(),
                'published_at' => now(),
            ]);

            $opening = JobOpening::create([
                'title' => $hiringRequest->title,
                'slug' => $this->uniqueSlug($hiringRequest->title),
                'company_id' => $hiringRequest->company_id,
                'department_id' => $hiringRequest->department_id,
                'position_id' => $hiringRequest->position_id,
                'description' => $hiringRequest->description,
                'required_skills' => $hiringRequest->required_skills,
                'min_education' => $hiringRequest->min_education,
                'min_experience' => $hiringRequest->min_experience,
                'screening_threshold' => $hiringRequest->screening_threshold,
                'is_open' => true,
            ]);

            $hiringRequest->update(['job_opening_id' => $opening->id]);

            return $opening;
        });

        return to_route('hiring-requests.show', $hiringRequest)
            ->with('status', "Lowongan {$opening->title} sudah terbit di halaman karir.");
    }

    public function reject(Request $request, HiringRequest $hiringRequest): RedirectResponse
    {
        if (! $this->editable($hiringRequest)) {
            return back()->with('error', 'Permintaan ini sudah dikunci dan tidak bisa ditolak.');
        }

        $data = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:1000'],
        ], [
            'rejection_reason.required' => 'Alasan penolakan wajib diisi.',
        ]);

        $hiringRequest->update([
            'status' => HiringRequest::STATUS_REJECTED,
            'rejection_reason' => $data['rejection_reason'],
            'reviewed_by' => $request->user()?->id,
            'reviewed_at' => now(),
        ]);

        return to_route('hiring-requests.show', $hiringRequest)->with('status', 'Permintaan ditolak.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'company_id' => ['nullable', 'exists:companies,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'position_id' => ['nullable', 'exists:positions,id'],
            'headcount' => ['required', 'integer', 'min:1', 'max:100'],
            'description' => ['required', 'string', 'max:20000'],
            'required_skills' => ['nullable', 'string', 'max:1000'],
            'min_education' => ['nullable', Rule::in(JobOpeningController::EDUCATION_LEVELS)],
            'min_experience' => ['nullable', 'integer', 'min:0', 'max:50'],
            'screening_threshold' => ['nullable', 'integer', 'min:0', 'max:100'],
            'hr_notes' => ['nullable', 'string', 'max:2000'],
        ], [
            'title.required' => 'Jabatan lowongan wajib diisi.',
            'headcount.required' => 'Jumlah orang wajib diisi.',
            'headcount.min' => 'Jumlah orang minimal 1.',
            'description.required' => 'Deskripsi pekerjaan wajib diisi.',
            'min_experience.max' => 'Pengalaman maksimal 50 tahun.',
        ]);

        $companyId = $request->filled('company_id') ? (int) $data['company_id'] : null;
        $departmentId = $request->filled('department_id') ? (int) $data['department_id'] : null;
        $positionId = $request->filled('position_id') ? (int) $data['position_id'] : null;

        $department = $departmentId ? Department::find($departmentId) : null;
        $position = $positionId ? Position::find($positionId) : null;

        if ($position && ! $department) {
            $department = $position->department;
            $departmentId = $department?->id;
        }

        if ($position && $department && (int) $position->department_id !== (int) $department->id) {
            throw ValidationException::withMessages([
                'position_id' => 'Jabatan ini bukan bagian dari departemen yang dipilih.',
            ]);
        }

        if ($department) {
            if ($companyId === null) {
                $companyId = (int) $department->company_id;
            } elseif ($companyId !== (int) $department->company_id) {
                throw ValidationException::withMessages([
                    'department_id' => 'Departemen ini bukan bagian dari PT yang dipilih.',
                ]);
            }
        }

        $skills = JobOpening::parseSkills($data['required_skills'] ?? null);

        return [
            'title' => $data['title'],
            'company_id' => $companyId,
            'department_id' => $departmentId,
            'position_id' => $positionId,
            'headcount' => (int) $data['headcount'],
            'description' => $data['description'],
            'required_skills' => $skills === [] ? null : $skills,
            'min_education' => $request->filled('min_education') ? $data['min_education'] : null,
            'min_experience' => $request->filled('min_experience') ? (int) $data['min_experience'] : null,
            'screening_threshold' => $request->filled('screening_threshold') ? (int) $data['screening_threshold'] : 70,
            'hr_notes' => $request->filled('hr_notes') ? $data['hr_notes'] : null,
        ];
    }

    private function editable(HiringRequest $hiringRequest): bool
    {
        return in_array($hiringRequest->status, [HiringRequest::STATUS_NEW, HiringRequest::STATUS_REVIEWED], true);
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
