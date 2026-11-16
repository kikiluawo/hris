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
use App\Services\AccuracyBank;
use App\Services\AssessmentRunner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class AccuracyAssessmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_seed_and_score_accuracy_assessment(): void
    {
        $user = User::factory()->create(['is_hrd' => true]);

        // 1. Buat paket Tes Ketelitian (40 soal)
        $assessment = AccuracyBank::createOrUpdateAssessment($user);

        $this->assertEquals(40, $assessment->questions()->count());
        $this->assertEquals(40, $assessment->questions()->where('dimension', 'Ketelitian')->count());

        // 2. Buat lowongan, pelamar, dan undangan tes
        $opening = JobOpening::create([
            'title' => 'Staf Accounting & Finance',
            'slug' => 'staf-accounting-finance',
            'description' => 'Memeriksa faktur, rekonsiliasi bank, dan entri data keuangan.',
            'department' => 'Finance',
            'status' => 'published',
        ]);

        $candidate = Candidate::create([
            'name' => 'Dewi Lestari',
            'email' => 'dewi.lestari@example.com',
            'phone' => '081234567891',
            'address' => 'Jl. Gatot Subroto No. 8',
            'city' => 'Jakarta Selatan',
            'cv_path' => 'cvs/dewi.pdf',
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
            'token' => 'test-accuracy-token-' . uniqid(),
            'sent_at' => now(),
            'expires_at' => now()->addDays(2),
        ]);

        $runner = app(AssessmentRunner::class);
        $request = Request::create('/', 'GET', [], [], [], ['REMOTE_ADDR' => '127.0.0.1']);
        $attempt = $runner->start($invitation, $request);

        $this->assertTrue($attempt->isInProgress());
        $this->assertTrue($attempt->hasAccuracyQuestions());

        // 3. Jawab 38 soal: 36 benar, 2 salah, 2 tidak dijawab
        $questions = $assessment->questions()->orderBy('position')->get();

        foreach ($questions as $index => $question) {
            if ($index >= 38) {
                // Biarkan kosong / tidak dijawab
                continue;
            }

            if ($index === 5 || $index === 25) {
                // Jawaban salah
                $chosen = ($question->correct_option + 1) % 2;
            } else {
                // Jawaban benar
                $chosen = $question->correct_option;
            }

            $runner->saveAnswer($attempt, $question, $chosen, null);
        }

        $this->assertEquals(38, $attempt->answeredCount());

        // 4. Finalisasi tes
        $finalized = $runner->finalize($attempt, AssessmentAttempt::STATUS_SUBMITTED, AssessmentAttempt::REASON_SUBMITTED);

        $this->assertNotNull($finalized->accuracy_report);
        $report = $finalized->accuracy_report;

        // 5. Verifikasi struktur laporan ketelitian
        $this->assertEquals(40, $report['total_items']);
        $this->assertEquals(38, $report['attempted_count']);
        $this->assertEquals(36, $report['correct_count']);
        $this->assertEquals(2, $report['error_count']);
        $this->assertEquals(2, $report['unattempted_count']);

        // Akurasi murni 36 / 38 = 94.7%
        $this->assertEquals(94.7, $report['accuracy_rate']);

        // Verifikasi kuadran (Q1: Cepat & Sangat Teliti)
        $this->assertEquals('Q1', $report['quadrant']['code']);
        $this->assertArrayHasKey('stability', $report);
        $this->assertArrayHasKey('risk_profile', $report);
        $this->assertNotEmpty($report['recommended_roles']);

        // 6. Verifikasi render pada view HRD
        $response = $this->actingAs($user)->get(route('assessments.attempt', $finalized));
        $response->assertOk();
        $response->assertSee('Profil Ketelitian & Sikap Kerja', false);
        $response->assertSee('94.7%');
        $response->assertSee('Kuadran');
        $response->assertSee('Q1');
    }
}
