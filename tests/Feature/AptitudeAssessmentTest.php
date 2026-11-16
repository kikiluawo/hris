<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\AssessmentInvitation;
use App\Models\AssessmentQuestion;
use App\Models\Candidate;
use App\Models\JobOpening;
use App\Models\User;
use App\Services\AptitudeBank;
use App\Services\AssessmentRunner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class AptitudeAssessmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_seed_and_score_aptitude_assessment(): void
    {
        $user = User::factory()->create(['is_hrd' => true]);

        // 1. Buat paket Aptitude Test (30 soal)
        $assessment = AptitudeBank::createOrUpdateAssessment($user);

        $this->assertEquals(30, $assessment->questions()->count());
        $this->assertEquals(8, $assessment->questions()->where('dimension', 'Verbal')->count());
        $this->assertEquals(8, $assessment->questions()->where('dimension', 'Numerik')->count());
        $this->assertEquals(7, $assessment->questions()->where('dimension', 'Logika')->count());
        $this->assertEquals(7, $assessment->questions()->where('dimension', 'Spasial')->count());

        // 2. Buat lowongan, kandidat, dan undangan
        $opening = JobOpening::create([
            'title' => 'Business Analyst',
            'slug' => 'business-analyst',
            'description' => 'Menganalisis proses bisnis dan data operasional.',
            'department' => 'Technology',
            'status' => 'published',
        ]);

        $candidate = Candidate::create([
            'name' => 'Siti Rahma',
            'email' => 'siti.rahma@example.com',
            'phone' => '081298765432',
            'address' => 'Jl. Thamrin 45',
            'city' => 'Jakarta',
            'cv_path' => 'cvs/siti.pdf',
            'cv_original_name' => 'cv.pdf',
        ]);

        $application = Application::create([
            'candidate_id' => $candidate->id,
            'job_opening_id' => $opening->id,
            'status' => Application::STATUS_NEW,
        ]);

        $invitation = AssessmentInvitation::create([
            'assessment_id' => $assessment->id,
            'application_id' => $application->id,
            'token' => 'test-aptitude-token-' . uniqid(),
            'sent_at' => now(),
            'expires_at' => now()->addDays(3),
        ]);

        $runner = app(AssessmentRunner::class);
        $request = Request::create('/', 'GET', [], [], [], ['REMOTE_ADDR' => '127.0.0.1']);
        $attempt = $runner->start($invitation, $request);

        $this->assertTrue($attempt->isInProgress());
        $this->assertTrue($attempt->hasAptitudeQuestions());

        // 3. Jawab 25 dari 30 soal dengan benar (Kandidat Superior)
        // dan 5 soal dengan salah
        $questions = $assessment->questions()->orderBy('position')->get();
        $correctAnswersCount = 0;

        foreach ($questions as $index => $question) {
            if ($index < 25) {
                // Jawaban benar
                $chosen = $question->correct_option;
                $correctAnswersCount++;
            } else {
                // Jawaban salah: pilih opsi yang bukan kunci jawaban
                $chosen = ($question->correct_option + 1) % count($question->options);
            }

            $runner->saveAnswer($attempt, $question, $chosen, null);
        }

        $this->assertEquals(30, $attempt->answeredCount());

        // 4. Finalisasi tes
        $finalized = $runner->finalize($attempt, AssessmentAttempt::STATUS_SUBMITTED, AssessmentAttempt::REASON_SUBMITTED);

        $this->assertNotNull($finalized->aptitude_report);
        $report = $finalized->aptitude_report;

        // 5. Verifikasi laporan IQ & Kognitif
        $this->assertEquals(30, $report['total_questions']);
        $this->assertEquals(25, $report['correct_count']);
        $this->assertEquals(83.3, $report['accuracy_percent']);

        // Skor 25 / 30 menghasilkan IQ 124 (Superior)
        $this->assertEquals(124, $report['estimated_iq']);
        $this->assertStringContainsString('Superior', $report['iq_classification']);

        // Verifikasi ke-4 subtes kognitif terhitung
        $this->assertArrayHasKey('verbal', $report['domains']);
        $this->assertArrayHasKey('numeric', $report['domains']);
        $this->assertArrayHasKey('logic', $report['domains']);
        $this->assertArrayHasKey('spatial', $report['domains']);

        $this->assertArrayHasKey('cognitive_profile', $report);
        $this->assertArrayHasKey('recommended_roles', $report);
        $this->assertNotEmpty($report['recommended_roles']);

        // 6. Verifikasi render pada view HRD
        $response = $this->actingAs($user)->get(route('assessments.attempt', $finalized));
        $response->assertOk();
        $response->assertSee('Estimasi Kapasitas Intelektual (IQ)');
        $response->assertSee('124');
        $response->assertSee('Superior');
    }
}
