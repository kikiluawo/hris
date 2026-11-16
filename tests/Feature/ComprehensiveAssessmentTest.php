<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\AssessmentAttempt;
use App\Models\AssessmentInvitation;
use App\Models\AssessmentQuestion;
use App\Models\Candidate;
use App\Models\JobOpening;
use App\Models\User;
use App\Services\AssessmentRunner;
use App\Services\ComprehensiveBank;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class ComprehensiveAssessmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_seed_and_evaluate_all_three_modules_in_one_assessment(): void
    {
        $user = User::factory()->create(['is_hrd' => true]);

        // 1. Buat paket All-in-One (94 Soal: 24 DISC + 30 IQ + 40 Ketelitian)
        $assessment = ComprehensiveBank::createOrUpdateAssessment($user);

        $this->assertEquals(94, $assessment->questions()->count());
        $this->assertEquals(24, $assessment->questions()->where('type', AssessmentQuestion::TYPE_DISC)->count());
        $this->assertEquals(30, $assessment->questions()->where('type', AssessmentQuestion::TYPE_MULTIPLE_CHOICE)->whereIn('dimension', ['Verbal', 'Numerik', 'Logika', 'Spasial'])->count());
        $this->assertEquals(40, $assessment->questions()->where('dimension', 'Ketelitian')->count());

        // 2. Buat lowongan, kandidat, dan undangan
        $opening = JobOpening::create([
            'title' => 'Management Trainee',
            'slug' => 'management-trainee',
            'description' => 'Program akselerasi kepemimpinan.',
            'department' => 'Operations',
            'status' => 'published',
        ]);

        $candidate = Candidate::create([
            'name' => 'Aditya Pratama',
            'email' => 'aditya@example.com',
            'phone' => '081299887766',
            'address' => 'Jl. Asia Afrika No. 12',
            'city' => 'Bandung',
            'cv_path' => 'cvs/aditya.pdf',
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
            'token' => 'all-in-one-token-' . uniqid(),
            'sent_at' => now(),
            'expires_at' => now()->addDays(3),
        ]);

        $runner = app(AssessmentRunner::class);
        $request = Request::create('/', 'GET', [], [], [], ['REMOTE_ADDR' => '127.0.0.1']);
        $attempt = $runner->start($invitation, $request);

        $this->assertTrue($attempt->isInProgress());
        $this->assertTrue($attempt->hasDiscQuestions());
        $this->assertTrue($attempt->hasAptitudeQuestions());
        $this->assertTrue($attempt->hasAccuracyQuestions());

        // 3. Jawab seluruh 94 soal:
        $questions = $assessment->questions()->orderBy('position')->get();

        foreach ($questions as $question) {
            if ($question->isDisc()) {
                // Jawab DISC: Pilih D sebagai Most, S sebagai Least
                $traits = array_map('trim', explode(',', $question->dimension ?? 'D,I,S,C'));
                $dIndex = array_search('D', $traits, true);
                $sIndex = array_search('S', $traits, true);
                $dIndex = $dIndex !== false ? (int) $dIndex : 0;
                $sIndex = $sIndex !== false ? (int) $sIndex : 1;

                $runner->saveAnswer($attempt, $question, null, null, $dIndex, $sIndex);
            } elseif (in_array($question->dimension, ['Verbal', 'Numerik', 'Logika', 'Spasial'], true)) {
                // Jawab Aptitude: Kunci benar
                $runner->saveAnswer($attempt, $question, $question->correct_option, null);
            } else {
                // Jawab Ketelitian: Kunci benar
                $runner->saveAnswer($attempt, $question, $question->correct_option, null);
            }
        }

        $this->assertEquals(94, $attempt->answeredCount());

        // 4. Finalisasi tes
        $finalized = $runner->finalize($attempt, AssessmentAttempt::STATUS_SUBMITTED, AssessmentAttempt::REASON_SUBMITTED);

        // 5. Verifikasi bahwa KETIGA modul berhasil dikalkulasi secara bersamaan dan terisolasi
        $this->assertNotNull($finalized->disc_report);
        $this->assertNotNull($finalized->aptitude_report);
        $this->assertNotNull($finalized->accuracy_report);

        // DISC Report
        $this->assertEquals(24, $finalized->disc_report['total_questions']);
        $this->assertEquals(24, $finalized->disc_report['tally']['most']['D']);
        $this->assertEquals('D', $finalized->disc_report['profile']['primary_trait']);

        // Aptitude Report (30 soal, semua benar -> IQ 142)
        $this->assertEquals(30, $finalized->aptitude_report['total_questions']);
        $this->assertEquals(30, $finalized->aptitude_report['correct_count']);
        $this->assertEquals(142, $finalized->aptitude_report['estimated_iq']);

        // Accuracy Report (40 soal, semua benar -> Akurasi 100%, Q1)
        $this->assertEquals(40, $finalized->accuracy_report['total_items']);
        $this->assertEquals(40, $finalized->accuracy_report['correct_count']);
        $this->assertEquals(100.0, $finalized->accuracy_report['accuracy_rate']);
        $this->assertEquals('Q1', $finalized->accuracy_report['quadrant']['code']);

        // 6. Verifikasi halaman detail hasil menampilkan ketiga komponen
        $response = $this->actingAs($user)->get(route('assessments.attempt', $finalized));
        $response->assertOk();
        $response->assertSee('Profil DISC');
        $response->assertSee('Estimasi IQ');
        $response->assertSee('Ketelitian Kerja');
        $response->assertSee('Grafik 1: Adaptasi (Mask)');
        $response->assertSee('Estimasi Kapasitas Intelektual (IQ)');
        $response->assertSee('Profil Ketelitian & Sikap Kerja', false);
    }
}
