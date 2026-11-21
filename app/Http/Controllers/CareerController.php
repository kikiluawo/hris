<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Candidate;
use App\Models\Company;
use App\Models\Department;
use App\Models\JobOpening;
use App\Services\CvScreener;
use App\Services\FileSecurityValidator;
use App\Services\RecruitmentNotifier;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class CareerController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('q')->trim()->toString();
        $location = $request->string('location')->trim()->toString();
        $companyId = $request->integer('company_id') ?: null;
        $department = $request->string('department')->trim()->toString();
        if ($department === '' && $request->filled('department_id')) {
            $deptModel = Department::find($request->input('department_id'));
            $department = $deptModel?->name ?? '';
        }
        $type = $request->string('type')->trim()->toString();
        $selectedType = in_array($type, JobOpening::JOB_TYPES, true) ? $type : null;

        $hasActiveFilters = $search !== ''
            || $location !== ''
            || $companyId !== null
            || $department !== ''
            || $selectedType !== null;

        $openings = JobOpening::with(['company', 'department', 'position'])
            ->where('is_open', true)
            ->when($selectedType !== null, fn ($query) => $query->where('job_type', $selectedType))
            ->when($companyId !== null, fn ($query) => $query->where('company_id', $companyId))
            ->when($department !== '', function ($query) use ($department) {
                $query->where(function ($query) use ($department) {
                    $query->whereHas('department', fn ($d) => $d->where('name', $department))
                        ->orWhereHas('position.department', fn ($d) => $d->where('name', $department));

                    $keywords = $this->departmentKeywords($department);
                    foreach ($keywords as $keyword) {
                        $term = '%' . $keyword . '%';
                        $query->orWhere('title', 'like', $term)
                            ->orWhere('description', 'like', $term)
                            ->orWhere('required_skills', 'like', $term);
                    }
                });
            })
            ->when($location !== '', function ($query) use ($location) {
                $query->where(function ($query) use ($location) {
                    $query->where('location', $location)
                        ->orWhereHas('company.branches', fn ($branch) => $branch->where('city', $location));
                });
            })
            ->when($search !== '', function ($query) use ($search) {
                $term = '%' . $search . '%';

                $query->where(function ($query) use ($term) {
                    $query->where('title', 'like', $term)
                        ->orWhere('description', 'like', $term)
                        ->orWhereHas('position', fn ($position) => $position->where('name', 'like', $term))
                        ->orWhereHas('company', fn ($company) => $company->where('name', 'like', $term))
                        ->orWhereHas('department', fn ($department) => $department->where('name', 'like', $term));
                });
            })
            ->latest()
            ->get();

        $companies = Company::where('is_active', true)
            ->whereHas('jobOpenings', fn ($query) => $query->where('is_open', true))
            ->orderBy('name')
            ->pluck('name', 'id');

        if ($companies->isEmpty()) {
            $companies = Company::where('is_active', true)->orderBy('name')->pluck('name', 'id');
        }

        $departments = Department::whereNotNull('name')
            ->where('name', '!=', '')
            ->distinct()
            ->orderBy('name')
            ->pluck('name')
            ->values()
            ->all();

        $locations = ['Batam', 'Tanjung Pinang', 'Medan', 'Jakarta', 'Bintan', 'Karimun'];

        return view('career.index', [
            'openings' => $openings,
            'search' => $search,
            'selectedLocation' => $location,
            'selectedCompanyId' => $companyId,
            'selectedDepartment' => $department,
            'selectedDepartmentId' => $request->input('department_id'),
            'selectedType' => $selectedType,
            'hasActiveFilters' => $hasActiveFilters,
            'companies' => $companies,
            'departments' => $departments,
            'locations' => $locations,
            'jobTypes' => JobOpening::JOB_TYPES,
            'totalOpenings' => JobOpening::where('is_open', true)->count(),
        ]);
    }

    public function show(string $slug): View
    {
        $opening = JobOpening::with(['company', 'department', 'position'])
            ->where('slug', $slug)
            ->firstOrFail();

        return view('career.show', [
            'opening' => $opening,
            'related' => JobOpening::with(['company', 'department'])
                ->where('is_open', true)
                ->whereKeyNot($opening->id)
                ->latest()
                ->take(3)
                ->get(),
        ]);
    }

    public function apply(
        Request $request,
        string $slug,
        CvScreener $screener,
        RecruitmentNotifier $notifier,
        FileSecurityValidator $securityValidator
    ): RedirectResponse {
        $opening = JobOpening::where('slug', $slug)->firstOrFail();

        if (! $opening->is_open) {
            return back()->with('error', 'Lowongan ini sudah ditutup.');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:30', function (string $attribute, mixed $value, \Closure $fail) {
                if (Candidate::normalizePhone($value) === null) {
                    $fail('Nomor HP tidak valid. Tulis tanpa angka 0 di depan, contoh 812 3456 7890.');
                }
            }],
            'address' => ['required', 'string', 'max:500'],
            'city' => ['required', 'string', 'max:255'],
            'cv' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
            'consent' => ['accepted'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'phone.required' => 'Nomor HP wajib diisi.',
            'address.required' => 'Alamat/domisili wajib diisi.',
            'city.required' => 'Kota wajib diisi.',
            'cv.required' => 'CV wajib diunggah.',
            'cv.mimes' => 'CV harus berupa PDF, DOC, atau DOCX.',
            'cv.max' => 'Ukuran CV maksimal 5 MB.',
            'consent.accepted' => 'Persetujuan penggunaan data wajib dicentang.',
        ]);

        $data['phone'] = Candidate::normalizePhone($data['phone']) ?? $data['phone'];
        $email = Str::lower($data['email']);
        $phone = $data['phone'];

        $alreadyApplied = Application::where('job_opening_id', $opening->id)
            ->whereHas('candidate', function ($query) use ($email, $phone) {
                $query->where('email', $email)
                    ->orWhere('phone', $phone);
            })
            ->exists();

        if ($alreadyApplied) {
            return back()->withInput()->with('already_applied', [
                'email' => $data['email'],
                'phone' => $data['phone'],
                'opening_title' => $opening->title,
                'company_name' => $opening->company?->name ?? 'Majesty Group',
                'other_openings_url' => route('career.index'),
            ])->withErrors([
                'email' => 'Email atau Nomor HP ini sudah pernah melamar untuk lowongan ini. Anda dapat menggunakan data yang sama untuk melamar pada lowongan lain yang tersedia.',
                'phone' => 'Email atau Nomor HP ini sudah pernah melamar untuk lowongan ini. Anda dapat menggunakan data yang sama untuk melamar pada lowongan lain yang tersedia.',
            ]);
        }

        $file = $request->file('cv');

        $safety = $securityValidator->validateUploadSafety($file);
        if (! $safety['safe']) {
            return back()->withInput()->withErrors([
                'cv' => $safety['reason'],
            ]);
        }

        $docInspection = $securityValidator->inspectDocumentType(
            (string) $file->getRealPath(),
            $file->getClientOriginalExtension()
        );

        $cvPath = $file->store('cvs/' . now()->format('Y/m'), 'local');
        $screening = $screener->screen(
            Storage::disk('local')->path($cvPath),
            strtolower($file->getClientOriginalExtension()),
            $opening
        );

        $screening['document_type'] = $docInspection['type'];
        $screening['document_label'] = $docInspection['label'];
        $screening['is_digital_text'] = $docInspection['is_digital_text'];
        $screening['is_scanned_image'] = $docInspection['is_scanned_image'];

        $threshold = (int) ($opening->screening_threshold ?? 70);
        $score = $screening['score'];
        $status = $score === null
            ? Application::STATUS_REVIEW
            : ($score >= $threshold ? Application::STATUS_REVIEW : Application::STATUS_SCREENING_FAILED);

        try {
            $application = DB::transaction(function () use ($data, $email, $opening, $request, $cvPath, $file, $screening, $status, $score) {
                $candidate = Candidate::firstOrCreate(
                    ['email' => $email],
                    [
                        'name' => $data['name'],
                        'phone' => $data['phone'],
                        'address' => $data['address'],
                        'city' => $data['city'],
                        'cv_path' => $cvPath,
                        'cv_original_name' => Str::limit($file->getClientOriginalName(), 200, ''),
                    ]
                );

                if (! $candidate->wasRecentlyCreated) {
                    $candidate->update([
                        'name' => $data['name'],
                        'phone' => $data['phone'],
                        'address' => $data['address'],
                        'city' => $data['city'],
                        'cv_path' => $cvPath,
                        'cv_original_name' => Str::limit($file->getClientOriginalName(), 200, ''),
                    ]);
                }

                return Application::create([
                    'job_opening_id' => $opening->id,
                    'candidate_id' => $candidate->id,
                    'source' => Str::limit(trim((string) $request->input('source')), 100, '') ?: null,
                    'status' => $status,
                    'screening_score' => $score,
                    'screening_data' => $screening,
                    'screened_at' => now(),
                ]);
            });
        } catch (QueryException $e) {
            Storage::disk('local')->delete($cvPath);

            if ($e->getCode() === '23000') {
                return back()->withInput()->with('already_applied', [
                    'email' => $data['email'],
                    'phone' => $data['phone'],
                    'opening_title' => $opening->title,
                    'company_name' => $opening->company?->name ?? 'Majesty Group',
                    'other_openings_url' => route('career.index'),
                ])->withErrors([
                    'email' => 'Email atau Nomor HP ini sudah pernah melamar untuk lowongan ini. Anda dapat menggunakan data yang sama untuk melamar pada lowongan lain yang tersedia.',
                    'phone' => 'Email atau Nomor HP ini sudah pernah melamar untuk lowongan ini. Anda dapat menggunakan data yang sama untuk melamar pada lowongan lain yang tersedia.',
                ]);
            }

            throw $e;
        } catch (Throwable $e) {
            Storage::disk('local')->delete($cvPath);

            throw $e;
        }

        if ($status === Application::STATUS_SCREENING_PASSED) {
            $notifier->sendScreeningPassed($application);
        }

        return redirect()
            ->route('career.thanks', $opening->slug)
            ->with('applied_name', $application->candidate->name);
    }

    public function thanks(string $slug): View|RedirectResponse
    {
        $opening = JobOpening::where('slug', $slug)->firstOrFail();

        if (! session()->has('applied_name')) {
            return redirect()->route('career.show', $opening->slug);
        }

        return view('career.thanks', [
            'opening' => $opening,
            'candidateName' => session('applied_name'),
        ]);
    }

    private function departmentKeywords(string $department): array
    {
        $map = [
            'Accounting' => ['accounting', 'akuntansi', 'keuangan', 'finance', 'pajak', 'tax'],
            'Accounting & Finance' => ['accounting', 'akuntansi', 'keuangan', 'finance', 'pajak', 'tax'],
            'Aftersales & Workshop' => ['aftersales', 'workshop', 'bengkel', 'service', 'advisor', 'warranty'],
            'Collection' => ['collection', 'penagihan', 'kolektor'],
            'Detailing & Coating' => ['detailing', 'coating', 'car grooming', 'grooming', 'salon mobil'],
            'Fleet & Maritime' => ['fleet', 'maritime', 'trailer', 'driver', 'sopir', 'kernet', 'kapal'],
            'General Affairs' => ['general affair', 'ga', 'office boy', 'kebersihan', 'fasilitas'],
            'General Affairs & Admin' => ['general affair', 'ga', 'admin', 'administrasi', 'office boy'],
            'HRD' => ['hrd', 'hr', 'human resource', 'recruitment', 'rekrutmen', 'personalia', 'talent'],
            'Internal Audit' => ['audit', 'auditor', 'internal audit'],
            'IT' => ['it', 'software', 'developer', 'programmer', 'network', 'jaringan', 'hardware'],
            'Legal' => ['legal', 'hukum', 'perizinan', 'kontrak'],
            'Management' => ['management', 'manager', 'personal assistant', 'estate management'],
            'Operations' => ['operations', 'operasional', 'spbu', 'operator', 'terminal', 'hse', 'pengawas'],
            'Operations & Logistics' => ['operations', 'operasional', 'logistics', 'logistik', 'jnt', 'pengiriman', 'gudang'],
            'Production' => ['production', 'produksi', 'pabrik', 'operator produksi'],
            'Project & Maintenance' => ['project', 'proyek', 'maintenance', 'pengawas lapangan', 'pemeliharaan'],
            'Sales & Marketing' => ['sales', 'marketing', 'penjualan', 'pemasaran', 'trainer'],
            'Sparepart & Procurement' => ['sparepart', 'partman', 'procurement', 'pengadaan', 'store', 'gudang'],
            'Workshop & Maintenance' => ['workshop', 'bengkel', 'mekanik', 'foreman', 'maintenance', 'teknisi'],
        ];

        $keywords = $map[$department] ?? [];

        $words = array_filter(preg_split('/\s+(&|\/|\+)?\s+/u', $department) ?: [], fn ($w) => mb_strlen($w) > 2);
        foreach ($words as $w) {
            $keywords[] = mb_strtolower($w);
        }

        return array_values(array_unique($keywords));
    }
}
