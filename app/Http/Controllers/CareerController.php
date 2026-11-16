<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Branch;
use App\Models\Candidate;
use App\Models\Company;
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

        return view('career.index', [
            'openings' => JobOpening::with(['company', 'department', 'position'])
                ->where('is_open', true)
                ->when($search !== '', function ($query) use ($search) {
                    $term = '%' . $search . '%';

                    $query->where(function ($query) use ($term) {
                        $query->where('title', 'like', $term)
                            ->orWhereHas('company', fn ($company) => $company->where('name', 'like', $term))
                            ->orWhereHas('department', fn ($department) => $department->where('name', 'like', $term));
                    });
                })
                ->latest()
                ->get(),
            'search' => $search,
            'totalOpenings' => JobOpening::where('is_open', true)->count(),
            'totalCompanies' => Company::count(),
            'totalBranches' => Branch::count(),
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

        $alreadyApplied = Application::where('job_opening_id', $opening->id)
            ->whereHas('candidate', fn ($query) => $query->where('email', $email))
            ->exists();

        if ($alreadyApplied) {
            return back()->withInput()->withErrors([
                'email' => 'Email ini sudah pernah melamar untuk lowongan ini.',
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
                return back()->withInput()->withErrors([
                    'email' => 'Email ini sudah pernah melamar untuk lowongan ini.',
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
}
