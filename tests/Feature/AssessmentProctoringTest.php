<?php

namespace Tests\Feature;

use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\AssessmentInvitation;
use App\Models\AssessmentQuestion;
use App\Models\AssessmentSnapshot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class AssessmentProctoringTest extends TestCase
{
    use RefreshDatabase;

    private User $hrd;
    private Assessment $assessment;
    private AssessmentInvitation $invitation;
    private AssessmentAttempt $attempt;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        $this->hrd = User::factory()->create(['is_hrd' => true]);

        $this->assessment = Assessment::create([
            'title' => 'Tes Online Standar',
            'duration_minutes' => 30,
            'status' => 'published',
            'created_by' => $this->hrd->id,
        ]);

        $this->assessment->questions()->create([
            'type' => AssessmentQuestion::TYPE_MULTIPLE_CHOICE,
            'question' => '1 + 1 = ?',
            'options' => [
                ['text' => '1', 'is_correct' => false],
                ['text' => '2', 'is_correct' => true],
            ],
            'points' => 10,
        ]);

        $opening = \App\Models\JobOpening::create([
            'title' => 'Staff IT',
            'slug' => 'staff-it-proctor',
            'description' => 'Staff IT',
        ]);

        $candidate = \App\Models\Candidate::create([
            'name' => 'Kandidat Ujian',
            'email' => 'kandidat.ujian@example.com',
            'phone' => '08123456789',
            'address' => 'Jl. Ujian No. 1',
            'city' => 'Batam',
            'cv_path' => 'cvs/dummy.pdf',
            'cv_original_name' => 'cv.pdf',
        ]);

        $application = \App\Models\Application::create([
            'candidate_id' => $candidate->id,
            'job_opening_id' => $opening->id,
            'status' => \App\Models\Application::STATUS_ASSESSMENT_SENT,
        ]);

        $this->invitation = AssessmentInvitation::create([
            'assessment_id' => $this->assessment->id,
            'application_id' => $application->id,
            'token' => 'invitation-proctor-123456',
            'duration_minutes' => 30,
            'expires_at' => now()->addDays(2),
            'created_by' => $this->hrd->id,
        ]);

        $this->attempt = $this->invitation->attempt()->create([
            'token' => 'attempt-proctor-123456',
            'access_token' => 'access-proctor-secret-token',
            'status' => AssessmentAttempt::STATUS_IN_PROGRESS,
            'started_at' => now(),
            'ends_at' => now()->addMinutes(30),
        ]);
    }

    public function test_fullscreen_exit_is_recorded_as_violation(): void
    {
        $response = $this->postJson(route('assessment.violation', $this->attempt->token), [
            'type' => 'fullscreen_exit',
            'access_token' => $this->attempt->access_token,
        ]);

        $response->assertOk();
        $response->assertJson([
            'action' => 'warn',
            'count' => 1,
        ]);

        $this->assertDatabaseHas('assessment_events', [
            'assessment_attempt_id' => $this->attempt->id,
            'type' => 'fullscreen_exit',
        ]);

        $this->assertSame(1, $this->attempt->fresh()->violation_count);
    }

    public function test_candidate_can_upload_snapshot_during_test(): void
    {
        $fakeBase64 = 'data:image/jpeg;base64,' . base64_encode('fake-jpg-binary-content');

        $response = $this->postJson(route('assessment.snapshot', $this->attempt->token), [
            'image' => $fakeBase64,
            'trigger' => 'periodic',
            'access_token' => $this->attempt->access_token,
        ]);

        $response->assertOk();
        $response->assertJson(['ok' => true]);

        $this->assertDatabaseHas('assessment_snapshots', [
            'assessment_attempt_id' => $this->attempt->id,
            'trigger' => 'periodic',
        ]);

        $snapshot = AssessmentSnapshot::first();
        $this->assertNotNull($snapshot);
        Storage::disk('local')->assertExists($snapshot->file_path);
    }

    public function test_invalid_access_token_cannot_upload_snapshot(): void
    {
        $fakeBase64 = 'data:image/jpeg;base64,' . base64_encode('fake-jpg-binary-content');

        $response = $this->postJson(route('assessment.snapshot', $this->attempt->token), [
            'image' => $fakeBase64,
            'trigger' => 'periodic',
            'access_token' => 'wrong-token',
        ]);

        $response->assertStatus(409);
    }

    public function test_hrd_can_view_candidate_snapshot(): void
    {
        $path = 'assessments/snapshots/' . $this->attempt->id . '/test.jpg';
        Storage::disk('local')->put($path, 'fake-jpeg-bytes');

        $snapshot = $this->attempt->snapshots()->create([
            'file_path' => $path,
            'trigger' => 'start',
            'captured_at' => now(),
        ]);

        $response = $this->actingAs($this->hrd)->get(route('assessments.attempts.snapshot', $snapshot));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'image/jpeg');
    }

    public function test_take_page_renders_anti_cheat_controls(): void
    {
        $cookie = ['assessment_lock_' . $this->attempt->id => 'valid_lock'];
        $this->attempt->update(['lock_token' => 'valid_lock']);

        $response = $this->withCookies($cookie)
            ->get(route('assessment.take', $this->attempt->token));

        $response->assertOk()
            ->assertSee('select-none')
            ->assertSee('oncontextmenu="return false;"', false)
            ->assertSee('snapshotUrl')
            ->assertSee('Mode Layar Penuh Diperlukan');
    }
}
