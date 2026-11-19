<?php

namespace Tests\Feature;

use App\Mail\InterviewInvitation;
use App\Mail\ScreeningPassed;
use App\Models\Application;
use App\Models\Candidate;
use App\Models\JobOpening;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RecruitmentPipelineTest extends TestCase
{
    use RefreshDatabase;

    public function test_application_with_unreadable_cv_is_flagged_for_manual_review(): void
    {
        Storage::fake('local');

        $opening = $this->opening();

        $response = $this->post(route('career.apply', $opening->slug), [
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'phone' => '08123456789',
            'address' => 'Jl. Merdeka 1',
            'city' => 'Jakarta',
            'cv' => UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf'),
            'consent' => '1',
        ]);

        $response->assertRedirect(route('career.thanks', $opening->slug));

        $this->assertDatabaseHas('candidates', ['email' => 'budi@example.com']);
        $this->assertDatabaseHas('applications', [
            'job_opening_id' => $opening->id,
            'status' => Application::STATUS_REVIEW,
        ]);
    }

    public function test_same_email_cannot_apply_twice_for_the_same_opening(): void
    {
        Storage::fake('local');

        $opening = $this->opening();
        $candidate = $this->candidate();
        Application::create([
            'job_opening_id' => $opening->id,
            'candidate_id' => $candidate->id,
            'status' => Application::STATUS_NEW,
        ]);

        $response = $this->from(route('career.show', $opening->slug))
            ->post(route('career.apply', $opening->slug), [
                'name' => 'Budi Santoso',
                'email' => 'budi@example.com',
                'phone' => '08123456789',
                'address' => 'Jl. Merdeka 1',
                'city' => 'Jakarta',
                'cv' => UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf'),
                'consent' => '1',
            ]);

        $response->assertSessionHasErrors('email');
        $this->assertSame(1, Application::where('job_opening_id', $opening->id)->count());
    }

    public function test_same_phone_cannot_apply_twice_for_the_same_opening_even_with_different_email(): void
    {
        $opening = $this->opening();
        $candidate = $this->candidate();
        Application::create([
            'job_opening_id' => $opening->id,
            'candidate_id' => $candidate->id,
            'status' => Application::STATUS_NEW,
        ]);

        $response = $this->from(route('career.show', $opening->slug))
            ->post(route('career.apply', $opening->slug), [
                'name' => 'Budi Santoso Email Lain',
                'email' => 'budi.lain@example.com',
                'phone' => '08123456789',
                'address' => 'Jl. Merdeka 1',
                'city' => 'Jakarta',
                'cv' => UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf'),
                'consent' => '1',
            ]);

        $response->assertSessionHasErrors(['email', 'phone']);
        $this->assertSame(1, Application::where('job_opening_id', $opening->id)->count());
    }

    public function test_manual_pass_sends_screening_passed_email_once(): void
    {
        Mail::fake();

        $user = User::factory()->create(['is_hrd' => true]);
        $application = $this->application(Application::STATUS_REVIEW);

        $response = $this->actingAs($user)->patch(route('applicants.status', $application), [
            'decision' => 'pass',
        ]);

        $response->assertSessionHas('status');

        $application->refresh();
        $this->assertSame(Application::STATUS_SCREENING_PASSED, $application->status);
        $this->assertNotNull($application->screening_notified_at);

        Mail::assertSent(ScreeningPassed::class, 1);
    }

    public function test_scheduling_interview_sends_invitation(): void
    {
        Mail::fake();

        $user = User::factory()->create(['is_hrd' => true]);
        $application = $this->application(Application::STATUS_SCREENING_PASSED);

        $response = $this->actingAs($user)->patch(route('applicants.interview', $application), [
            'interview_at' => now()->addDays(3)->format('Y-m-d H:i:s'),
            'interview_mode' => 'online',
            'interview_location' => 'https://meet.example.com/hrd',
            'interview_notes' => 'Bawa portofolio.',
        ]);

        $response->assertSessionHas('status');

        $application->refresh();
        $this->assertSame(Application::STATUS_INTERVIEW_SCHEDULED, $application->status);
        $this->assertSame('online', $application->interview_mode);
        $this->assertNotNull($application->interview_sent_at);

        Mail::assertSent(InterviewInvitation::class, 1);
    }

    public function test_applicant_page_renders_with_pipeline_controls(): void
    {
        $user = User::factory()->create(['is_hrd' => true]);
        $application = $this->application(Application::STATUS_SCREENING_PASSED);

        $response = $this->actingAs($user)->get(route('applicants.index'));

        $response->assertOk()
            ->assertSee($application->candidate->name)
            ->assertSee('Lolos screening')
            ->assertSee('Jadwalkan interview', false);
    }

    public function test_job_opening_form_shows_screening_threshold_field(): void
    {
        $user = User::factory()->create(['is_hrd' => true]);

        $this->actingAs($user)
            ->get(route('openings.index'))
            ->assertOk()
            ->assertSee('Ambang lolos screening');
    }

    private function opening(): JobOpening
    {
        return JobOpening::create([
            'title' => 'Accounting Staff',
            'slug' => 'accounting-staff',
            'description' => 'Mengelola pembukuan harian.',
            'screening_threshold' => 70,
            'is_open' => true,
        ]);
    }

    public function test_can_invite_applicant_to_assessment_from_assessment_page(): void
    {
        $user = User::factory()->create(['is_hrd' => true]);
        $assessment = \App\Models\Assessment::create([
            'title' => 'Tes Bakat IQ',
            'duration_minutes' => 30,
            'status' => 'published',
        ]);
        $app = $this->application(Application::STATUS_NEW);

        $response = $this->actingAs($user)->post(route('assessments.invitations.store', $assessment), [
            'applications' => [$app->id],
            'expires_at' => now()->addDays(5)->format('Y-m-d H:i:s'),
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('assessment_invitations', [
            'assessment_id' => $assessment->id,
            'application_id' => $app->id,
        ]);
        $this->assertSame(Application::STATUS_ASSESSMENT_SENT, $app->fresh()->status);
    }

    public function test_can_invite_applicant_to_assessment_from_applicant_table(): void
    {
        $user = User::factory()->create(['is_hrd' => true]);
        $assessment = \App\Models\Assessment::create([
            'title' => 'Tes DISC',
            'duration_minutes' => 20,
            'status' => 'published',
        ]);
        $app = $this->application(Application::STATUS_REVIEW);

        $response = $this->actingAs($user)->post(route('applicants.invite-assessment', $app), [
            'assessment_id' => $assessment->id,
            'expires_at' => now()->addDays(3)->format('Y-m-d H:i:s'),
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('assessment_invitations', [
            'assessment_id' => $assessment->id,
            'application_id' => $app->id,
        ]);
        $this->assertSame(Application::STATUS_ASSESSMENT_SENT, $app->fresh()->status);
    }

    public function test_hrd_can_mark_interview_as_completed(): void
    {
        $user = User::factory()->create(['is_hrd' => true]);
        $app = $this->application(Application::STATUS_INTERVIEW_SCHEDULED);
        $app->update([
            'interview_at' => now()->addDays(2),
            'interview_mode' => 'onsite',
            'interview_location' => 'Kantor',
        ]);

        $response = $this->actingAs($user)->patch(route('applicants.complete-interview', $app));

        $response->assertSessionHas('status');
        $this->assertNotNull($app->fresh()->interview_completed_at);
        $this->assertTrue($app->fresh()->isInterviewCompleted());
    }

    public function test_applicant_page_hides_decision_buttons_until_assessment_done(): void
    {
        $user = User::factory()->create(['is_hrd' => true]);
        $app = $this->application(Application::STATUS_INTERVIEW_SCHEDULED);
        $app->update([
            'interview_at' => now()->addDays(2),
            'interview_mode' => 'onsite',
            'interview_location' => 'Kantor',
        ]);

        $response = $this->actingAs($user)->get(route('applicants.index'));
        $response->assertOk()
            ->assertDontSee('Terima sebagai Karyawan (Hired)')
            ->assertSee('Kirim undangan interview via WhatsApp');

        $app->update(['interview_completed_at' => now()]);
        $response = $this->actingAs($user)->get(route('applicants.index'));
        $response->assertOk()
            ->assertDontSee('Kirim undangan interview via WhatsApp')
            ->assertDontSee('Terima sebagai Karyawan (Hired)');

        $app->update(['status' => Application::STATUS_ASSESSMENT_DONE]);
        $response = $this->actingAs($user)->get(route('applicants.index'));
        $response->assertOk()
            ->assertDontSee('Kirim undangan interview via WhatsApp')
            ->assertSee('Terima sebagai Karyawan (Hired)');
    }

    public function test_applicant_page_renders_with_attempt_score(): void
    {
        $user = User::factory()->create(['is_hrd' => true]);
        $app = $this->application(Application::STATUS_ASSESSMENT_DONE);

        $assessment = \App\Models\Assessment::create([
            'title' => 'Tes Standar',
            'duration_minutes' => 30,
            'status' => 'published',
        ]);

        $invitation = $assessment->invitations()->create([
            'application_id' => $app->id,
            'token' => 'token-test-123456',
            'duration_minutes' => 30,
            'expires_at' => now()->addDays(2),
        ]);

        $attempt = $invitation->attempt()->create([
            'token' => 'attempt-token-123456',
            'status' => \App\Models\AssessmentAttempt::STATUS_SUBMITTED,
            'score' => 85,
            'started_at' => now(),
            'ends_at' => now()->addMinutes(30),
        ]);

        $response = $this->actingAs($user)->get(route('applicants.index'));
        $response->assertOk()
            ->assertSee('Lihat Hasil (85%)');
    }

    private function candidate(): Candidate
    {
        return Candidate::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'phone' => '08123456789',
            'address' => 'Jl. Merdeka 1',
            'city' => 'Jakarta',
            'cv_path' => 'cvs/2026/11/cv.pdf',
            'cv_original_name' => 'cv.pdf',
        ]);
    }

    private function application(string $status): Application
    {
        return Application::create([
            'job_opening_id' => $this->opening()->id,
            'candidate_id' => $this->candidate()->id,
            'status' => $status,
        ]);
    }
}
