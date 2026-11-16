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
use App\Services\AssessmentRunner;
use App\Services\DiscBank;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class DiscAssessmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_seed_and_score_disc_assessment(): void
    {
        $user = User::factory()->create(['is_hrd' => true]);

        // 1. Buat paket DISC
        $assessment = DiscBank::createOrUpdateAssessment($user);

        $this->assertEquals(24, $assessment->questions()->where('type', AssessmentQuestion::TYPE_DISC)->count());

        // 2. Buat kandidat dan undangan
        $opening = JobOpening::create([
            'title' => 'Staf Operasional',
            'slug' => 'staf-operasional',
            'description' => 'Tugas operasional kantor.',
            'department' => 'Operasional',
            'status' => 'published',
        ]);

        $candidate = Candidate::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'phone' => '081234567890',
            'address' => 'Jl. Sudirman 10',
            'city' => 'Jakarta',
            'cv_path' => 'cvs/budi.pdf',
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
            'token' => 'test-disc-token-' . uniqid(),
            'sent_at' => now(),
            'expires_at' => now()->addDays(2),
        ]);

        $runner = app(AssessmentRunner::class);
        $request = Request::create('/assessment/' . $invitation->token, 'GET');
        $attempt = $runner->start($invitation, $request);

        $this->assertNotNull($attempt);
        $this->assertTrue($attempt->isInProgress());

        // 3. Jawab semua 24 soal DISC
        // Berikan pola jawaban: sering memilih D sebagai Most, dan S sebagai Least
        $questions = $assessment->questions()->orderBy('position')->get();

        foreach ($questions as $question) {
            $options = $question->options;

            $traits = array_map('trim', explode(',', $question->dimension ?? 'D,I,S,C'));
            $dIndex = array_search('D', $traits, true);
            $sIndex = array_search('S', $traits, true);

            if ($dIndex === false) {
                foreach ($options as $idx => $opt) {
                    if (is_array($opt) && ($opt['trait'] ?? '') === 'D') {
                        $dIndex = $idx;
                    }
                }
            }

            if ($sIndex === false) {
                foreach ($options as $idx => $opt) {
                    if (is_array($opt) && ($opt['trait'] ?? '') === 'S') {
                        $sIndex = $idx;
                    }
                }
            }

            $dIndex = $dIndex !== false ? (int) $dIndex : 0;
            $sIndex = $sIndex !== false ? (int) $sIndex : 1;

            $runner->saveAnswer($attempt, $question, null, null, $dIndex, $sIndex);
        }

        $this->assertEquals(24, $attempt->answeredCount());

        // 4. Finalisasi tes
        $finalized = $runner->finalize($attempt, AssessmentAttempt::STATUS_SUBMITTED, AssessmentAttempt::REASON_SUBMITTED);

        $this->assertNotNull($finalized->disc_report);
        $report = $finalized->disc_report;

        // Verifikasi struktur laporan
        $this->assertArrayHasKey('tally', $report);
        $this->assertArrayHasKey('graphs', $report);
        $this->assertArrayHasKey('profile', $report);
        $this->assertArrayHasKey('adaptation_analysis', $report);

        // Verifikasi hasil: Most D = 24, Least S = 24
        $this->assertEquals(24, $report['tally']['most']['D']);
        $this->assertEquals(24, $report['tally']['least']['S']);

        // Verifikasi Grafik 1, 2, 3
        $this->assertArrayHasKey('graph_1', $report['graphs']);
        $this->assertArrayHasKey('graph_2', $report['graphs']);
        $this->assertArrayHasKey('graph_3', $report['graphs']);

        // D harus menjadi profil dominan (High D)
        $this->assertEquals('D', $report['profile']['primary_trait']);
        $this->assertStringContainsString('Dominance', $report['profile']['title']);

        // Verifikasi analisis adaptasi
        $this->assertArrayHasKey('shifts', $report['adaptation_analysis']);
        $this->assertArrayHasKey('effort_level', $report['adaptation_analysis']);
    }
}
