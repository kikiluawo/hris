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
        $user = $request->user();
        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'status' => (string) $request->query('status', ''),
            'my_approval' => (string) $request->query('my_approval', ''),
        ];

        $like = $filters['q'] !== '' ? '%' . addcslashes($filters['q'], '\\%_') . '%' : null;

        $requests = HiringRequest::with(['company', 'department', 'position', 'reviewer', 'ceoApprover', 'cfoApprover'])
            ->when(array_key_exists($filters['status'], HiringRequest::statusOptions()), fn ($query) => $query->where('status', $filters['status']))
            ->when($filters['my_approval'] === '1' && $user?->isCeo(), fn ($query) => $query->where('status', HiringRequest::STATUS_PENDING_APPROVAL)->whereIn('approval_route', ['ceo', 'both'])->where('ceo_approval_status', 'pending'))
            ->when($filters['my_approval'] === '1' && $user?->isCfo(), fn ($query) => $query->where('status', HiringRequest::STATUS_PENDING_APPROVAL)->whereIn('approval_route', ['cfo', 'both'])->where('cfo_approval_status', 'pending'))
            ->when($like !== null, function ($query) use ($like) {
                $query->where(function ($query) use ($like) {
                    $query->where('title', 'like', $like)
                        ->orWhere('requester_name', 'like', $like)
                        ->orWhere('requesting_unit', 'like', $like)
                        ->orWhere('replaced_employee_name', 'like', $like)
                        ->orWhereHas('company', fn ($company) => $company->where('name', 'like', $like));
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $pendingMyApproval = 0;
        if ($user?->isCeo()) {
            $pendingMyApproval = HiringRequest::where('status', HiringRequest::STATUS_PENDING_APPROVAL)
                ->whereIn('approval_route', ['ceo', 'both'])
                ->where('ceo_approval_status', 'pending')
                ->count();
        } elseif ($user?->isCfo()) {
            $pendingMyApproval = HiringRequest::where('status', HiringRequest::STATUS_PENDING_APPROVAL)
                ->whereIn('approval_route', ['cfo', 'both'])
                ->where('cfo_approval_status', 'pending')
                ->count();
        }

        return view('recruitment.hiring-requests', [
            'requests' => $requests,
            'statuses' => HiringRequest::statusOptions(),
            'counts' => HiringRequest::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
            'filters' => $filters,
            'token' => HiringRequest::requestToken(),
            'pendingMyApproval' => $pendingMyApproval,
        ]);
    }

    public function rotateToken(): RedirectResponse
    {
        HiringRequest::rotateRequestToken();

        return back()->with('status', 'Token form permintaan sudah diganti. Link lama tidak berlaku lagi.');
    }

    public function show(Request $request, HiringRequest $hiringRequest): View
    {
        $hiringRequest->load(['company', 'department', 'position', 'jobOpening', 'reviewer', 'ceoApprover', 'cfoApprover']);
        $currentUser = $request->user();

        return view('recruitment.hiring-request-review', [
            'hiringRequest' => $hiringRequest,
            'companies' => Company::orderBy('name')->pluck('name', 'id'),
            'departments' => Department::orderBy('name')->pluck('name', 'id'),
            'positions' => Position::orderBy('name')->pluck('name', 'id'),
            'educationLevels' => array_combine(JobOpeningController::EDUCATION_LEVELS, JobOpeningController::EDUCATION_LEVELS),
            'jobTypes' => JobOpening::jobTypeOptions(),
            'editable' => $this->editable($hiringRequest),
            'canApprove' => $hiringRequest->canUserApprove($currentUser) || ($currentUser?->isHrd() && $hiringRequest->isPendingApproval()),
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

        $action = $request->input('approval_action', 'direct');
        $approvalRoute = $request->input('approval_route', 'direct');
        $data = $this->validated($request);

        // Case 1: Direct Approval by HRD -> publish immediately to career portal
        if ($action === 'direct' || $approvalRoute === 'direct') {
            $opening = DB::transaction(function () use ($hiringRequest, $data, $request) {
                $hiringRequest->update([
                    ...$data,
                    'status' => HiringRequest::STATUS_PUBLISHED,
                    'approval_route' => HiringRequest::ROUTE_DIRECT,
                    'reviewed_by' => $request->user()?->id,
                    'reviewed_at' => now(),
                    'published_at' => now(),
                ]);

                $opening = $this->createJobOpeningFromRequest($hiringRequest);
                $hiringRequest->update(['job_opening_id' => $opening->id]);

                return $opening;
            });

            return to_route('hiring-requests.show', $hiringRequest)
                ->with('status', "Permintaan disetujui langsung dan lowongan {$opening->title} telah terbit di halaman karir.");
        }

        // Case 2: Requires Executive Approval (CEO, CFO, or Both)
        if (! in_array($approvalRoute, [HiringRequest::ROUTE_CEO, HiringRequest::ROUTE_CFO, HiringRequest::ROUTE_BOTH], true)) {
            throw ValidationException::withMessages([
                'approval_route' => 'Jalur persetujuan tidak valid.',
            ]);
        }

        $hiringRequest->update([
            ...$data,
            'status' => HiringRequest::STATUS_PENDING_APPROVAL,
            'approval_route' => $approvalRoute,
            'ceo_approval_status' => in_array($approvalRoute, [HiringRequest::ROUTE_CEO, HiringRequest::ROUTE_BOTH], true) ? HiringRequest::APPROVAL_PENDING : null,
            'cfo_approval_status' => in_array($approvalRoute, [HiringRequest::ROUTE_CFO, HiringRequest::ROUTE_BOTH], true) ? HiringRequest::APPROVAL_PENDING : null,
            'reviewed_by' => $request->user()?->id,
            'reviewed_at' => now(),
        ]);

        $routeLabels = [
            HiringRequest::ROUTE_CEO => 'CEO (Hadi Ong)',
            HiringRequest::ROUTE_CFO => 'CFO (Sri Rahayu)',
            HiringRequest::ROUTE_BOTH => 'CEO & CFO (Hadi Ong dan Sri Rahayu)',
        ];

        return to_route('hiring-requests.show', $hiringRequest)
            ->with('status', 'Permintaan telah disimpan dan diajukan untuk approval ' . ($routeLabels[$approvalRoute] ?? 'Eksekutif') . '.');
    }

    public function approve(Request $request, HiringRequest $hiringRequest): RedirectResponse
    {
        if ($hiringRequest->status !== HiringRequest::STATUS_PENDING_APPROVAL) {
            return back()->with('error', 'Permintaan ini tidak dalam status menunggu approval.');
        }

        $user = $request->user();
        $notes = $request->input('approval_notes');
        $actingAs = $request->input('acting_as');

        $canCeoApprove = ($user?->isCeo() || ($user?->isHrd() && $actingAs === 'ceo'))
            && $hiringRequest->needsCeoApproval()
            && $hiringRequest->ceo_approval_status === HiringRequest::APPROVAL_PENDING;

        $canCfoApprove = ($user?->isCfo() || ($user?->isHrd() && $actingAs === 'cfo'))
            && $hiringRequest->needsCfoApproval()
            && $hiringRequest->cfo_approval_status === HiringRequest::APPROVAL_PENDING;

        if (! $canCeoApprove && ! $canCfoApprove) {
            return back()->with('error', 'Anda tidak memiliki hak untuk menyetujui tahap approval ini.');
        }

        if ($canCeoApprove) {
            $hiringRequest->update([
                'ceo_approval_status' => HiringRequest::APPROVAL_APPROVED,
                'ceo_approved_by' => $user->id,
                'ceo_approved_at' => now(),
                'ceo_notes' => $notes ?: $hiringRequest->ceo_notes,
            ]);
        }

        if ($canCfoApprove) {
            $hiringRequest->update([
                'cfo_approval_status' => HiringRequest::APPROVAL_APPROVED,
                'cfo_approved_by' => $user->id,
                'cfo_approved_at' => now(),
                'cfo_notes' => $notes ?: $hiringRequest->cfo_notes,
            ]);
        }

        $hiringRequest->refresh();

        $ceoSatisfied = ! $hiringRequest->needsCeoApproval() || $hiringRequest->ceo_approval_status === HiringRequest::APPROVAL_APPROVED;
        $cfoSatisfied = ! $hiringRequest->needsCfoApproval() || $hiringRequest->cfo_approval_status === HiringRequest::APPROVAL_APPROVED;

        if ($ceoSatisfied && $cfoSatisfied) {
            $opening = DB::transaction(function () use ($hiringRequest) {
                $opening = $this->createJobOpeningFromRequest($hiringRequest);
                $hiringRequest->update([
                    'status' => HiringRequest::STATUS_PUBLISHED,
                    'published_at' => now(),
                    'job_opening_id' => $opening->id,
                ]);

                return $opening;
            });

            return to_route('hiring-requests.show', $hiringRequest)
                ->with('status', "Seluruh persetujuan terpenuhi! Lowongan {$opening->title} telah otomatis terbit di halaman karir.");
        }

        return to_route('hiring-requests.show', $hiringRequest)
            ->with('status', 'Persetujuan Anda telah dicatat. Menunggu persetujuan selanjutnya sebelum lowongan diterbitkan.');
    }

    public function reject(Request $request, HiringRequest $hiringRequest): RedirectResponse
    {
        if ($hiringRequest->status === HiringRequest::STATUS_PUBLISHED) {
            return back()->with('error', 'Permintaan yang sudah terbit tidak bisa ditolak.');
        }

        $data = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:2000'],
        ], [
            'rejection_reason.required' => 'Alasan penolakan wajib diisi.',
        ]);

        $user = $request->user();
        $rolePrefix = $user?->isCeo() ? 'CEO' : ($user?->isCfo() ? 'CFO' : 'HRD');
        $reason = "[{$rolePrefix} - {$user?->name}]: " . $data['rejection_reason'];

        if ($user?->isCeo() && $hiringRequest->needsCeoApproval()) {
            $hiringRequest->ceo_approval_status = HiringRequest::APPROVAL_REJECTED;
            $hiringRequest->ceo_approved_by = $user->id;
            $hiringRequest->ceo_approved_at = now();
            $hiringRequest->ceo_notes = $data['rejection_reason'];
        } elseif ($user?->isCfo() && $hiringRequest->needsCfoApproval()) {
            $hiringRequest->cfo_approval_status = HiringRequest::APPROVAL_REJECTED;
            $hiringRequest->cfo_approved_by = $user->id;
            $hiringRequest->cfo_approved_at = now();
            $hiringRequest->cfo_notes = $data['rejection_reason'];
        }

        $hiringRequest->update([
            'status' => HiringRequest::STATUS_REJECTED,
            'rejection_reason' => $reason,
            'reviewed_by' => $user?->id,
            'reviewed_at' => now(),
        ]);

        return to_route('hiring-requests.show', $hiringRequest)->with('status', 'Permintaan ditolak.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'job_type' => ['nullable', 'string', Rule::in(JobOpening::JOB_TYPES)],
            'request_type' => ['required', 'string', Rule::in([HiringRequest::TYPE_NEW, HiringRequest::TYPE_REPLACEMENT])],
            'replaced_employee_name' => ['required_if:request_type,' . HiringRequest::TYPE_REPLACEMENT, 'nullable', 'string', 'max:255'],
            'replacement_reason' => ['required_if:request_type,' . HiringRequest::TYPE_REPLACEMENT, 'nullable', 'string', 'max:2000'],
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
            'request_type.required' => 'Jenis kebutuhan karyawan wajib dipilih.',
            'replaced_employee_name.required_if' => 'Nama karyawan yang diganti wajib diisi untuk pergantian.',
            'replacement_reason.required_if' => 'Alasan pergantian wajib diisi untuk pergantian.',
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
            'job_type' => $request->filled('job_type') ? $data['job_type'] : JobOpening::TYPE_FULL_TIME,
            'request_type' => $data['request_type'] ?? HiringRequest::TYPE_NEW,
            'replaced_employee_name' => $data['request_type'] === HiringRequest::TYPE_REPLACEMENT ? ($data['replaced_employee_name'] ?? null) : null,
            'replacement_reason' => $data['request_type'] === HiringRequest::TYPE_REPLACEMENT ? ($data['replacement_reason'] ?? null) : null,
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

    private function createJobOpeningFromRequest(HiringRequest $hiringRequest): JobOpening
    {
        if ($hiringRequest->job_opening_id && $hiringRequest->jobOpening) {
            return $hiringRequest->jobOpening;
        }

        return JobOpening::create([
            'title' => $hiringRequest->title,
            'job_type' => $hiringRequest->job_type ?? JobOpening::TYPE_FULL_TIME,
            'slug' => $this->uniqueSlug($hiringRequest->title),
            'company_id' => $hiringRequest->company_id,
            'department_id' => $hiringRequest->department_id,
            'position_id' => $hiringRequest->position_id,
            'description' => $hiringRequest->description,
            'required_skills' => $hiringRequest->required_skills,
            'min_education' => $hiringRequest->min_education,
            'min_experience' => $hiringRequest->min_experience,
            'screening_threshold' => $hiringRequest->screening_threshold ?? 70,
            'is_open' => true,
        ]);
    }

    private function editable(HiringRequest $hiringRequest): bool
    {
        return in_array($hiringRequest->status, [HiringRequest::STATUS_NEW, HiringRequest::STATUS_REVIEWED, HiringRequest::STATUS_PENDING_APPROVAL], true);
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

