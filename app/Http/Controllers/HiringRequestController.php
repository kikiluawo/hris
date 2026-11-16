<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\HiringRequest;
use App\Models\JobOpening;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class HiringRequestController extends Controller
{
    public function create(string $token): View
    {
        $this->ensureToken($token);

        return view('hiring-requests.create', [
            'token' => $token,
            'companies' => Company::where('is_active', true)->orderBy('name')->pluck('name', 'id'),
            'educationLevels' => array_combine(JobOpeningController::EDUCATION_LEVELS, JobOpeningController::EDUCATION_LEVELS),
        ]);
    }

    public function store(Request $request, string $token): RedirectResponse
    {
        $this->ensureToken($token);

        if ($request->filled('website')) {
            return to_route('hiring-requests.create', $token);
        }

        $data = $request->validate([
            'requester_name' => ['required', 'string', 'max:255'],
            'requester_position' => ['required', 'string', 'max:255'],
            'requester_phone' => ['required', 'string', 'max:30'],
            'requester_email' => ['nullable', 'email', 'max:255'],
            'requesting_unit' => ['nullable', 'string', 'max:255'],
            'company_id' => ['nullable', 'exists:companies,id'],
            'title' => ['required', 'string', 'max:255'],
            'headcount' => ['required', 'integer', 'min:1', 'max:100'],
            'description' => ['required', 'string', 'max:20000'],
            'required_skills' => ['nullable', 'string', 'max:1000'],
            'min_education' => ['nullable', Rule::in(JobOpeningController::EDUCATION_LEVELS)],
            'min_experience' => ['nullable', 'integer', 'min:0', 'max:50'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ], [
            'requester_name.required' => 'Nama pengaju wajib diisi.',
            'requester_position.required' => 'Jabatan pengaju wajib diisi.',
            'requester_phone.required' => 'Nomor HP / WhatsApp wajib diisi.',
            'requester_email.email' => 'Format email tidak valid.',
            'title.required' => 'Jabatan yang dibutuhkan wajib diisi.',
            'headcount.required' => 'Jumlah orang wajib diisi.',
            'headcount.min' => 'Jumlah orang minimal 1.',
            'description.required' => 'Deskripsi pekerjaan wajib diisi.',
            'min_experience.max' => 'Pengalaman maksimal 50 tahun.',
        ]);

        $skills = JobOpening::parseSkills($data['required_skills'] ?? null);

        $hiringRequest = HiringRequest::create([
            'company_id' => $request->filled('company_id') ? (int) $data['company_id'] : null,
            'requester_name' => $data['requester_name'],
            'requester_position' => $data['requester_position'],
            'requester_phone' => $data['requester_phone'],
            'requester_email' => $request->filled('requester_email') ? $data['requester_email'] : null,
            'requesting_unit' => $request->filled('requesting_unit') ? $data['requesting_unit'] : null,
            'title' => $data['title'],
            'headcount' => (int) $data['headcount'],
            'description' => $data['description'],
            'required_skills' => $skills === [] ? null : $skills,
            'min_education' => $request->filled('min_education') ? $data['min_education'] : null,
            'min_experience' => $request->filled('min_experience') ? (int) $data['min_experience'] : null,
            'notes' => $request->filled('notes') ? $data['notes'] : null,
            'status' => HiringRequest::STATUS_NEW,
        ]);

        return to_route('hiring-requests.thanks', $token)->with([
            'request_reference' => $hiringRequest->reference(),
            'request_title' => $hiringRequest->title,
        ]);
    }

    public function thanks(string $token): View|RedirectResponse
    {
        $this->ensureToken($token);

        if (! session()->has('request_reference')) {
            return to_route('hiring-requests.create', $token);
        }

        return view('hiring-requests.thanks', [
            'token' => $token,
            'reference' => session('request_reference'),
            'title' => session('request_title'),
        ]);
    }

    private function ensureToken(string $token): void
    {
        $expected = HiringRequest::requestToken();

        abort_unless($expected !== '' && hash_equals($expected, $token), 404);
    }
}
