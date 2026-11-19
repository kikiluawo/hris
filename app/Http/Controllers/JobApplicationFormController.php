<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\JobApplicationForm;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class JobApplicationFormController extends Controller
{
    public function show(string $token): View
    {
        $form = JobApplicationForm::with(['application.candidate', 'application.jobOpening.company', 'application.jobOpening.position'])
            ->where('token', $token)
            ->firstOrFail();

        $application = $form->application;
        $candidate = $application->candidate;
        $opening = $application->jobOpening;

        $personal = $form->personal_data ?? [];
        $personal['name'] = $personal['name'] ?? $candidate->name;
        $personal['email'] = $personal['email'] ?? $candidate->email;
        $personal['phone_mobile'] = $personal['phone_mobile'] ?? $candidate->phone;
        $personal['address'] = $personal['address'] ?? $candidate->address;
        $personal['city'] = $personal['city'] ?? $candidate->city;

        return view('recruitment.forms.job-application-form', [
            'form' => $form,
            'application' => $application,
            'candidate' => $candidate,
            'opening' => $opening,
            'jobOpening' => $opening,
            'personal' => $personal,
            'family' => $form->family_data ?? [],
            'education' => $form->education_data ?? [],
            'workHistory' => $form->work_history ?? [],
            'additional' => $form->additional_info ?? [],
            'isSubmitted' => $form->isSubmitted(),
        ]);
    }

    public function store(Request $request, string $token): RedirectResponse
    {
        $form = JobApplicationForm::where('token', $token)->firstOrFail();
        $application = $form->application;

        $request->validate([
            'name' => ['required_without:personal.name', 'nullable', 'string', 'max:150'],
            'address' => ['required_without:personal.address', 'nullable', 'string', 'max:500'],
            'mobile_phone' => ['required_without:personal.phone_mobile', 'nullable', 'string', 'max:30'],
            'photo' => ['nullable', 'image', 'max:3072'],
            'applicant_signature' => ['required_without:signature_name', 'nullable', 'string', 'max:120'],
        ]);

        $personal = [
            'name' => $request->input('name') ?? $request->input('personal.name', $application->candidate->name),
            'alias' => $request->input('alias') ?? $request->input('personal.alias'),
            'address' => $request->input('address') ?? $request->input('personal.address', $application->candidate->address),
            'email' => $request->input('email') ?? $request->input('personal.email', $application->candidate->email),
            'phone_mobile' => $request->input('mobile_phone') ?? $request->input('personal.phone_mobile', $application->candidate->phone),
            'phone_home' => $request->input('home_phone') ?? $request->input('personal.phone_home'),
            'phone_office' => $request->input('office_phone') ?? $request->input('personal.phone_office'),
            'gender' => $request->input('gender') ?? $request->input('personal.gender'),
            'race' => $request->input('race') ?? $request->input('personal.race'),
            'age' => $request->input('age') ?? $request->input('personal.age'),
            'religion' => $request->input('religion') ?? $request->input('personal.religion'),
            'marital_status' => $request->input('marital_status') ?? $request->input('personal.marital_status'),
            'birth_place' => $request->input('birth_place') ?? $request->input('personal.birth_place'),
            'birth_date' => $request->input('birth_date') ?? $request->input('personal.birth_date'),
            'ktp_no' => $request->input('ktp_no') ?? $request->input('personal.ktp_no'),
            'nationality' => $request->input('nationality') ?? $request->input('personal.nationality', 'Indonesia'),
            'passport_no' => $request->input('passport_no') ?? $request->input('personal.passport_no'),
            'serious_illness' => $request->input('serious_illness') ?? $request->input('personal.serious_illness'),
            'disabilities' => $request->input('disabilities') ?? $request->input('personal.disabilities'),
            'present_health' => $request->input('present_health') ?? $request->input('personal.present_health'),
        ];

        $photoPath = $form->photo_path;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('applicant-photos/' . date('Y/m'), 'public');
        }

        $familyData = [
            'spouse' => $request->input('spouse', []),
            'children' => $request->input('children', []),
            'father' => $request->input('father', []),
            'mother' => $request->input('mother', []),
            'siblings' => $request->input('siblings', []),
            'expected_salary' => $request->input('expected_salary'),
            'notice_period' => $request->input('notice_period'),
        ];

        $educationData = [
            'schools' => $request->input('education', []),
            'languages' => $request->input('languages', []),
            'strengths' => $request->input('strengths', []),
            'weaknesses' => $request->input('weaknesses', []),
            'computer_skills' => $request->input('computer_skills'),
        ];

        $workHistory = [
            'jobs' => $request->input('careers', []),
            'current_benefits' => $request->input('current_benefits'),
        ];

        $additionalInfo = [
            'criminal_record' => $request->input('criminal_record'),
            'driving_license' => $request->input('driving_license'),
            'vehicle_owned' => $request->input('vehicle_owned'),
            'other_business' => $request->input('other_business'),
            'suitability_statement' => $request->input('suitability_statement'),
            'references' => $request->input('references', []),
        ];

        $signatureName = $request->input('applicant_signature') ?? $request->input('signature_name', $personal['name']);

        $form->update([
            'photo_path' => $photoPath,
            'personal_data' => $personal,
            'family_data' => $familyData,
            'education_data' => $educationData,
            'work_history' => $workHistory,
            'additional_info' => $additionalInfo,
            'signature_name' => $signatureName,
            'signed_date' => now()->toDateString(),
            'submitted_at' => now(),
            'status' => 'submitted',
        ]);

        $application->candidate->update([
            'name' => $personal['name'],
            'address' => $personal['address'],
            'phone' => $personal['phone_mobile'],
        ]);

        if (in_array($application->status, [Application::STATUS_NEW, Application::STATUS_REVIEW, Application::STATUS_SCREENING_PASSED, Application::STATUS_FORM_SENT], true)) {
            $application->update([
                'status' => Application::STATUS_FORM_COMPLETED,
            ]);
        }

        return redirect()->route('application-forms.show', $token)
            ->with('status', 'Formulir lamaran kerja Anda berhasil dikirim dan tersimpan di sistem. Tim HRD kami akan meninjau dan menghubungi Anda untuk tahapan selanjutnya.');
    }

    public function viewAdmin(Application $application): View
    {
        $application->load(['candidate', 'jobOpening.company', 'jobApplicationForm']);
        $form = $application->jobApplicationForm;

        abort_unless($form, 404, 'Formulir lamaran untuk kandidat ini belum dibuat.');

        return view('recruitment.forms.admin-job-application-view', [
            'application' => $application,
            'candidate' => $application->candidate,
            'jobOpening' => $application->jobOpening,
            'form' => $form,
        ]);
    }

    public function printAdmin(Application $application): View
    {
        $application->load(['candidate', 'jobOpening.company', 'jobApplicationForm']);
        $form = $application->jobApplicationForm;

        abort_unless($form, 404, 'Formulir lamaran untuk kandidat ini belum dibuat.');

        return view('recruitment.forms.print-job-application', [
            'application' => $application,
            'candidate' => $application->candidate,
            'jobOpening' => $application->jobOpening,
            'form' => $form,
        ]);
    }
}
