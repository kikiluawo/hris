<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\EmployeeBiodata;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class EmployeeBiodataController extends Controller
{
    public function show(string $token): View
    {
        $biodata = EmployeeBiodata::with(['application.candidate', 'application.jobOpening.company', 'application.jobApplicationForm'])
            ->where('token', $token)
            ->firstOrFail();

        $application = $biodata->application;
        $appForm = $application->jobApplicationForm;
        $personalForm = $appForm?->personal_data ?? [];
        $familyForm = $appForm?->family_data ?? [];
        $eduForm = $appForm?->education_data ?? [];
        $workForm = $appForm?->work_history ?? [];

        $personal = $biodata->personal_data ?? [];
        $personal['full_name'] = $personal['full_name'] ?? ($personalForm['name'] ?? $application->candidate?->name);
        $personal['nickname'] = $personal['nickname'] ?? ($personalForm['alias'] ?? '');
        $personal['birth_place'] = $personal['birth_place'] ?? ($personalForm['birth_place'] ?? '');
        $personal['birth_date'] = $personal['birth_date'] ?? ($personalForm['birth_date'] ?? '');
        $personal['race'] = $personal['race'] ?? ($personalForm['race'] ?? '');
        $personal['nationality'] = $personal['nationality'] ?? ($personalForm['nationality'] ?? 'Indonesia');
        $personal['religion'] = $personal['religion'] ?? ($personalForm['religion'] ?? '');
        $personal['marital_status'] = $personal['marital_status'] ?? ($personalForm['marital_status'] ?? '');
        $personal['current_address'] = $personal['current_address'] ?? ($personalForm['address'] ?? $application->candidate?->address);
        $personal['email'] = $personal['email'] ?? ($personalForm['email'] ?? $application->candidate?->email);
        $personal['phone_home'] = $personal['phone_home'] ?? ($personalForm['phone_home'] ?? '');
        $personal['phone_mobile'] = $personal['phone_mobile'] ?? ($personalForm['phone_mobile'] ?? $application->candidate?->phone);
        $personal['ktp_no'] = $personal['ktp_no'] ?? ($personalForm['ktp_no'] ?? '');
        $personal['hobby'] = $personal['hobby'] ?? ($eduForm['hobbies'] ?? '');

        $family = $biodata->family_details ?? [];
        if (empty($family['spouse']) && ! empty($familyForm['spouse']['name'])) {
            $family['spouse'] = [
                'name' => $familyForm['spouse']['name'] ?? '',
                'birth_place' => '',
                'birth_date' => $familyForm['spouse']['birth_date'] ?? '',
                'nationality' => $familyForm['spouse']['nationality'] ?? '',
                'religion' => '',
                'address' => '',
                'phone' => '',
                'job' => $familyForm['spouse']['occupation'] ?? '',
                'office_address' => '',
            ];
        }

        if (empty($family['father']) && ! empty($familyForm['father']['name'])) {
            $family['father'] = [
                'name' => $familyForm['father']['name'] ?? '',
                'status_type' => 'Kandung',
                'birth_place' => '',
                'birth_date' => '',
                'nationality' => 'Indonesia',
                'religion' => '',
                'address' => '',
                'phone' => '',
                'job' => $familyForm['father']['occupation'] ?? '',
                'office_address' => '',
            ];
        }

        if (empty($family['mother']) && ! empty($familyForm['mother']['name'])) {
            $family['mother'] = [
                'name' => $familyForm['mother']['name'] ?? '',
                'status_type' => 'Kandung',
                'birth_place' => '',
                'birth_date' => '',
                'nationality' => 'Indonesia',
                'religion' => '',
                'address' => '',
                'phone' => '',
                'job' => $familyForm['mother']['occupation'] ?? '',
                'office_address' => '',
            ];
        }

        if (empty($family['children']) && ! empty($familyForm['children'])) {
            $family['children'] = array_map(function ($c) {
                return [
                    'name' => $c['name'] ?? '',
                    'gender' => '',
                    'age' => '',
                    'job_school' => '',
                    'phone' => '',
                    'notes' => '',
                ];
            }, $familyForm['children']);
        }

        if (empty($family['siblings']) && ! empty($familyForm['siblings'])) {
            $family['siblings'] = array_map(function ($s) {
                return [
                    'name' => $s['name'] ?? '',
                    'gender' => '',
                    'age' => '',
                    'job_school' => $s['occupation'] ?? '',
                    'phone' => '',
                    'notes' => '',
                ];
            }, $familyForm['siblings']);
        }

        $eduFormal = $biodata->education_formal ?? ($eduForm['schools'] ?? []);
        $eduInformal = $biodata->education_informal ?? ($eduForm['courses'] ?? []);
        $workExperiences = $biodata->work_experiences ?? ($workForm['jobs'] ?? []);

        return view('recruitment.forms.employee-biodata-form', [
            'biodata' => $biodata,
            'application' => $application,
            'company' => $application->jobOpening?->company,
            'personal' => $personal,
            'bankAccounts' => $biodata->bank_accounts ?? [],
            'family' => $family,
            'eduFormal' => $eduFormal,
            'eduInformal' => $eduInformal,
            'workExperiences' => $workExperiences,
            'residences' => $biodata->residences_history ?? [],
            'organizations' => $biodata->organization_history ?? [],
            'attachments' => $biodata->attachments ?? [],
            'isSubmitted' => $biodata->isSubmitted(),
        ]);
    }

    public function store(Request $request, string $token): RedirectResponse
    {
        $biodata = EmployeeBiodata::where('token', $token)->firstOrFail();
        $application = $biodata->application;

        $validated = $request->validate([
            'personal.full_name' => ['required', 'string', 'max:150'],
            'personal.nickname' => ['nullable', 'string', 'max:80'],
            'personal.birth_place' => ['required', 'string', 'max:80'],
            'personal.birth_date' => ['required', 'date'],
            'personal.nationality' => ['nullable', 'string', 'max:40'],
            'personal.religion' => ['required', 'string', 'max:40'],
            'personal.marital_status' => ['required', 'string', 'max:40'],
            'personal.blood_type' => ['nullable', 'string', 'max:5'],
            'personal.birth_order' => ['nullable', 'integer', 'min:1', 'max:20'],
            'personal.siblings_count' => ['nullable', 'integer', 'min:1', 'max:20'],
            'personal.current_address' => ['required', 'string', 'max:500'],
            'personal.email' => ['required', 'email', 'max:120'],
            'personal.phone_mobile' => ['required', 'string', 'max:30'],
            'personal.ktp_no' => ['required', 'string', 'max:30'],
            'personal.npwp_no' => ['nullable', 'string', 'max:35'],
            'personal.bpjs_tk_no' => ['nullable', 'string', 'max:35'],
            'personal.bpjs_kes_no' => ['nullable', 'string', 'max:35'],

            'bank_accounts' => ['nullable', 'array'],
            'family_details' => ['nullable', 'array'],
            'education_formal' => ['nullable', 'array'],
            'education_informal' => ['nullable', 'array'],
            'work_experiences' => ['nullable', 'array'],
            'residences_history' => ['nullable', 'array'],
            'organization_history' => ['nullable', 'array'],

            'attachment_ktp' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
            'attachment_npwp' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
            'attachment_bpjs' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
            'attachment_kk' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],

            'signed_name' => ['required', 'string', 'max:150'],
            'signed_city' => ['nullable', 'string', 'max:50'],
        ]);

        $attachments = $biodata->attachments ?? [];
        foreach (['ktp', 'npwp', 'bpjs', 'kk'] as $doc) {
            $key = "attachment_{$doc}";
            if ($request->hasFile($key)) {
                $attachments[$doc] = $request->file($key)->store('biodata-docs/' . date('Y/m'), 'public');
            }
        }

        $biodata->update([
            'personal_data' => $validated['personal'],
            'bank_accounts' => $request->input('bank_accounts', []),
            'family_details' => $request->input('family_details', []),
            'education_formal' => $request->input('education_formal', []),
            'education_informal' => $request->input('education_informal', []),
            'work_experiences' => $request->input('work_experiences', []),
            'residences_history' => $request->input('residences_history', []),
            'organization_history' => $request->input('organization_history', []),
            'attachments' => $attachments,
            'signed_city' => $validated['signed_city'] ?? 'Batam',
            'signed_name' => $validated['signed_name'],
            'signed_date' => now()->toDateString(),
            'submitted_at' => now(),
            'status' => 'submitted',
        ]);

        return redirect()->route('employee-biodatas.show', $token)
            ->with('status', 'Formulir Biodata Karyawan berhasil disimpan dan terkirim ke HRD. Terima kasih dan selamat bergabung!');
    }

    public function viewAdmin(Application $application): View
    {
        $application->load(['candidate', 'jobOpening.company', 'employeeBiodata']);
        $biodata = $application->employeeBiodata;

        abort_unless($biodata, 404, 'Biodata karyawan untuk kandidat ini belum dibuat.');

        return view('recruitment.forms.admin-employee-biodata-view', [
            'application' => $application,
            'biodata' => $biodata,
            'company' => $application->jobOpening?->company,
        ]);
    }

    public function printAdmin(Application $application): View
    {
        $application->load(['candidate', 'jobOpening.company', 'employeeBiodata']);
        $biodata = $application->employeeBiodata;

        abort_unless($biodata, 404, 'Biodata karyawan untuk kandidat ini belum dibuat.');

        return view('recruitment.forms.print-employee-biodata', [
            'application' => $application,
            'biodata' => $biodata,
            'company' => $application->jobOpening?->company,
        ]);
    }
}
