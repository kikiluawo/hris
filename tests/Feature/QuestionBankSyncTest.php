<?php

namespace Tests\Feature;

use App\Models\AssessmentQuestion;
use App\Models\QuestionBank;
use App\Models\User;
use App\Services\QuestionBankSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuestionBankSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_syncs_94_standard_questions_and_is_idempotent(): void
    {
        $user = User::factory()->create(['is_hrd' => true]);

        $this->assertSame(0, QuestionBank::count());

        $result1 = QuestionBankSyncService::syncStandardQuestions($user);

        $this->assertSame(94, $result1['added']);
        $this->assertSame(0, $result1['skipped']);
        $this->assertSame(94, QuestionBank::count());

        // Cek jumlah per jenis soal
        $this->assertSame(24, QuestionBank::ofType(AssessmentQuestion::TYPE_DISC)->count());
        $this->assertSame(70, QuestionBank::ofType(AssessmentQuestion::TYPE_MULTIPLE_CHOICE)->count());

        // Cek dimensi kognitif & ketelitian
        $this->assertSame(8, QuestionBank::ofDimension('Verbal')->count());
        $this->assertSame(8, QuestionBank::ofDimension('Numerik')->count());
        $this->assertSame(7, QuestionBank::ofDimension('Logika')->count());
        $this->assertSame(7, QuestionBank::ofDimension('Spasial')->count());
        $this->assertSame(40, QuestionBank::ofDimension('Ketelitian')->count());
        $this->assertSame(24, QuestionBank::ofDimension('Kepribadian DISC')->count());

        // Idempotency: run second time
        $result2 = QuestionBankSyncService::syncStandardQuestions($user);
        $this->assertSame(0, $result2['added']);
        $this->assertSame(94, $result2['skipped']);
        $this->assertSame(94, QuestionBank::count());
    }

    public function test_sync_standard_route_works_for_authenticated_hrd(): void
    {
        $user = User::factory()->create(['is_hrd' => true]);

        $response = $this->actingAs($user)
            ->post(route('question-bank.sync-standard'));

        $response->assertRedirect(route('question-bank.index'));
        $response->assertSessionHas('status');

        $this->assertSame(94, QuestionBank::count());
    }

    public function test_dimension_list_includes_standard_dimensions(): void
    {
        QuestionBankSyncService::syncStandardQuestions();

        $dimensions = QuestionBank::dimensionList();

        $this->assertContains('Kepribadian DISC', $dimensions);
        $this->assertContains('Verbal', $dimensions);
        $this->assertContains('Numerik', $dimensions);
        $this->assertContains('Logika', $dimensions);
        $this->assertContains('Spasial', $dimensions);
        $this->assertContains('Ketelitian', $dimensions);
    }
}
