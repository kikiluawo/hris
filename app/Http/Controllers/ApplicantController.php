<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Assessment;
use App\Models\JobOpening;
use App\Services\RecruitmentNotifier;
use App\Services\WhatsAppLink;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ApplicantController extends Controller
{
    public const INTERVIEW_MODES = [
        'onsite' => 'Hadir langsung',
        'online' => 'Online / video call',
        'phone' => 'Telepon',
    ];

    public function index(Request $request): View
    {
        $filters = $request->only(['job', 'q', 'status']);

        $term = trim((string) ($filters['q'] ?? ''));
        $like = $term !== '' ? '%' . addcslashes($term, '\\%_') . '%' : null;

        $applications = Application::with([
            'candidate',
            'jobOpening.company',
            'jobOpening.position',
            'assessmentInvitations.assessment',
            'assessmentInvitations.attempt',
            'jobApplicationForm',
            'employeeBiodata',
            'decidedBy',
        ])
            ->when($filters['job'] ?? null, fn ($query, $job) => $query->where('job_opening_id', $job))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($like !== null, function ($query) use ($like) {
                $query->whereHas('candidate', fn ($candidate) => $candidate
                    ->where('name', 'like', $like)
                    ->orWhere('email', 'like', $like));
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('recruitment.applicants', [
            'applications' => $applications,
            'openings' => JobOpening::orderBy('title')->pluck('title', 'id'),
            'statuses' => Application::statusOptions(),
            'interviewModes' => self::INTERVIEW_MODES,
            'filters' => $filters,
            'assessments' => Assessment::where('status', Assessment::STATUS_PUBLISHED)->orderBy('id')->get(),
        ]);
    }

    public function updateStatus(Request $request, Application $application, RecruitmentNotifier $notifier): RedirectResponse
    {
        $data = $request->validate([
            'decision' => ['required', Rule::in(['pass', 'reject'])],
        ]);

        if (in_array($application->status, [Application::STATUS_INTERVIEW_SCHEDULED, Application::STATUS_HIRED], true)) {
            return back()->with('error', 'Lamaran ini sudah berada di tahap interview atau diterima.');
        }

        if ($data['decision'] === 'pass') {
            $application->update([
                'status' => Application::STATUS_SCREENING_PASSED,
                'decided_at' => now(),
                'decided_by' => $request->user()?->id,
            ]);

            $emailed = $notifier->sendScreeningPassed($application);

            return back()->with('status', $emailed
                ? "{$application->candidate->name} diloloskan dan email pemberitahuan sudah dikirim."
                : "{$application->candidate->name} diloloskan. Email belum terkirim — periksa konfigurasi mail.");
        }

        $application->update([
            'status' => Application::STATUS_REJECTED,
            'decided_at' => now(),
            'decided_by' => $request->user()?->id,
        ]);

        return back()->with('status', "Lamaran {$application->candidate->name} ditolak.");
    }

    public function sendApplicationForm(Request $request, Application $application, WhatsAppLink $whatsApp): RedirectResponse
    {
        $form = $application->jobApplicationForm()->firstOrCreate(
            ['application_id' => $application->id],
            ['token' => Str::random(48), 'status' => 'draft']
        );

        if (in_array($application->status, [Application::STATUS_NEW, Application::STATUS_REVIEW, Application::STATUS_SCREENING_PASSED], true)) {
            $application->update([
                'status' => Application::STATUS_FORM_SENT,
            ]);
        }

        $waUrl = $whatsApp->jobApplicationFormInvitation($application, $form->url());

        return back()->with('status', 'Link Formulir Lamaran Kerja berhasil dibuat.')
            ->with('wa_job_form_url', $waUrl)
            ->with('wa_applicant_name', $application->candidate->name);
    }

    public function decideFinal(Request $request, Application $application, WhatsAppLink $whatsApp): RedirectResponse
    {
        $data = $request->validate([
            'decision' => ['required', Rule::in(['hire', 'reject'])],
        ]);

        if ($data['decision'] === 'hire') {
            $application->update([
                'status' => Application::STATUS_HIRED,
                'decided_at' => now(),
                'decided_by' => $request->user()?->id,
            ]);

            $biodata = $application->employeeBiodata()->firstOrCreate(
                ['application_id' => $application->id],
                ['token' => Str::random(48), 'status' => 'draft']
            );

            $waUrl = $whatsApp->onboardingInvitation($application, $biodata->url());

            return back()->with('status', "Kandidat {$application->candidate->name} diterima sebagai karyawan.")
                ->with('wa_onboarding_url', $waUrl)
                ->with('wa_applicant_name', $application->candidate->name);
        }

        $application->update([
            'status' => Application::STATUS_REJECTED,
            'decided_at' => now(),
            'decided_by' => $request->user()?->id,
        ]);

        return back()->with('status', "Kandidat {$application->candidate->name} belum sesuai / ditolak.");
    }

    public function scheduleInterview(Request $request, Application $application, RecruitmentNotifier $notifier): RedirectResponse
    {
        if ($application->status === Application::STATUS_HIRED) {
            return back()->with('error', 'Kandidat sudah diterima, jadwal interview tidak bisa diubah.');
        }

        $data = $request->validate([
            'interview_at' => ['required', 'date', 'after:now'],
            'interview_mode' => ['required', Rule::in(array_keys(self::INTERVIEW_MODES))],
            'interview_location' => ['required', 'string', 'max:255'],
            'interview_address' => ['nullable', 'string', 'max:500'],
            'interview_maps_url' => ['nullable', 'string', 'max:500'],
            'interview_notes' => ['nullable', 'string', 'max:1000'],
        ], [], [
            'interview_at' => 'jadwal interview',
            'interview_mode' => 'metode interview',
            'interview_location' => 'tempat / tautan interview',
            'interview_address' => 'alamat interview',
            'interview_maps_url' => 'tautan Google Maps',
            'interview_notes' => 'catatan',
        ]);

        $newStatus = in_array($application->status, [Application::STATUS_ASSESSMENT_SENT, Application::STATUS_ASSESSMENT_DONE, Application::STATUS_HIRED], true)
            ? $application->status
            : Application::STATUS_INTERVIEW_SCHEDULED;

        $application->update([
            ...$data,
            'status' => $newStatus,
            'decided_at' => now(),
            'decided_by' => $request->user()?->id,
        ]);

        $emailed = $notifier->sendInterviewInvitation($application->fresh());

        return back()->with('status', $emailed
            ? "Jadwal interview {$application->candidate->name} disimpan dan undangan sudah dikirim."
            : "Jadwal interview {$application->candidate->name} disimpan, tetapi email gagal dikirim. Periksa konfigurasi mail.");
    }

    public function inviteAssessment(Request $request, Application $application): RedirectResponse
    {
        $data = $request->validate([
            'assessment_id' => ['required', 'exists:assessments,id'],
            'expires_at' => ['nullable', 'date', 'after:now'],
            'duration_minutes' => ['nullable', 'integer', 'min:5', 'max:240'],
        ], [
            'assessment_id.required' => 'Pilih tes yang ingin diujikan.',
            'assessment_id.exists' => 'Tes tidak ditemukan.',
            'expires_at.after' => 'Batas waktu harus di waktu yang akan datang.',
            'duration_minutes.min' => 'Durasi minimal 5 menit.',
            'duration_minutes.max' => 'Durasi maksimal 240 menit.',
        ]);

        $assessment = Assessment::findOrFail($data['assessment_id']);

        if ($assessment->invitations()->where('application_id', $application->id)->exists()) {
            return back()->with('error', 'Kandidat ini sudah memiliki link undangan untuk tes "' . $assessment->title . '".');
        }

        $invitation = $assessment->invitations()->create([
            'application_id' => $application->id,
            'token' => Str::random(48),
            'duration_minutes' => $data['duration_minutes'] ?? null,
            'expires_at' => $data['expires_at'] ?? now()->addDays(7),
            'created_by' => $request->user()?->id,
        ]);

        if ($application->status !== Application::STATUS_HIRED) {
            $application->update([
                'status' => Application::STATUS_ASSESSMENT_SENT,
                'interview_completed_at' => $application->interview_completed_at ?? now(),
            ]);
        }

        return back()->with('status', 'Undangan tes "' . $assessment->title . '" untuk ' . $application->candidate->name . ' berhasil dibuat.');
    }

    public function completeInterview(Application $application): RedirectResponse
    {
        $application->update([
            'interview_completed_at' => now(),
        ]);

        return back()->with('status', "Interview dengan {$application->candidate->name} telah selesai.");
    }

    public function downloadCv(Application $application): StreamedResponse
    {
        $candidate = $application->candidate;
        $disk = Storage::disk('local');

        abort_unless($disk->exists($candidate->cv_path), 404);

        return $disk->download($candidate->cv_path, $candidate->cv_original_name);
    }
}
