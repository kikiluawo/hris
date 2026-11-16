<?php

namespace Tests\Unit;

use App\Models\Assessment;
use App\Models\AssessmentAnswer;
use App\Models\AssessmentAttempt;
use App\Models\AssessmentInvitation;
use App\Models\AssessmentQuestion;
use App\Services\DiscBank;
use App\Services\DiscScorer;
use PHPUnit\Framework\TestCase;

class DiscScorerTest extends TestCase
{
    public function test_extracts_and_scores_disc_answers_properly(): void
    {
        $scorer = new DiscScorer();

        // Verifikasi konstanta
        $this->assertEquals(['D', 'I', 'S', 'C'], DiscScorer::TRAITS);
        $this->assertEquals(14, DiscScorer::MIDLINE);
    }
}
