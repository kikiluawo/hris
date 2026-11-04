<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Candidate;
use App\Models\JobOpening;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

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
        ]);
    }

    public function show(string $slug): View
    {
        return view('career.show', [
            'opening' => JobOpening::with(['company', 'department', 'position'])
                ->where('slug', $slug)
                ->firstOrFail(),
        ]);
    }

    public function apply(Request $request, string $slug): RedirectResponse
    {
        $opening = JobOpening::where('slug', $slug)->firstOrFail();

        if (! $opening->is_open) {
            return back()->with('error', 'Lowongan ini sudah ditutup.');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
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

        $candidate = Candidate::create([
            'name' => $data['name'],
            'email' => $email,
            'phone' => $data['phone'],
            'address' => $data['address'],
            'city' => $data['city'],
            'cv_path' => $file->store('cvs/' . now()->format('Y/m'), 'local'),
            'cv_original_name' => Str::limit($file->getClientOriginalName(), 200, ''),
        ]);

        Application::create([
            'job_opening_id' => $opening->id,
            'candidate_id' => $candidate->id,
            'source' => Str::limit(trim((string) $request->input('source')), 100, '') ?: null,
            'status' => 'new',
        ]);

        return redirect()
            ->route('career.thanks', $opening->slug)
            ->with('applied_name', $candidate->name);
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
