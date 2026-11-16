<?php

namespace Tests\Unit;

use App\Services\AptitudeScorer;
use PHPUnit\Framework\TestCase;

class AptitudeScorerTest extends TestCase
{
    public function test_converts_30_question_raw_scores_to_standard_iq(): void
    {
        $scorer = new AptitudeScorer();

        // 30 soal (Sempurna) -> IQ 142 (Very Superior)
        $this->assertEquals(142, $scorer->calculateIq(30, 30));

        // 27 soal -> IQ 130 (Very Superior)
        $this->assertEquals(130, $scorer->calculateIq(27, 30));

        // 25 soal -> IQ 124 (Superior)
        $this->assertEquals(124, $scorer->calculateIq(25, 30));

        // 22 soal -> IQ 115 (High Average)
        $this->assertEquals(115, $scorer->calculateIq(22, 30));

        // 16 soal (Median Norm) -> IQ 100 (Average)
        $this->assertEquals(100, $scorer->calculateIq(16, 30));

        // 10 soal -> IQ 83 (Low Average)
        $this->assertEquals(83, $scorer->calculateIq(10, 30));

        // 5 soal -> IQ 68 (Borderline)
        $this->assertEquals(68, $scorer->calculateIq(5, 30));
    }

    public function test_converts_arbitrary_question_counts_proportionally(): void
    {
        $scorer = new AptitudeScorer();

        // Tes 50 soal: 50/50 -> 142
        $this->assertGreaterThanOrEqual(138, $scorer->calculateIq(50, 50));

        // Tes 50 soal: ~53% (27/50) -> ~100
        $this->assertEquals(101, $scorer->calculateIq(27, 50));

        // Tes 20 soal: 18/20 (90%) -> ~130
        $this->assertGreaterThanOrEqual(125, $scorer->calculateIq(18, 20));
    }
}
