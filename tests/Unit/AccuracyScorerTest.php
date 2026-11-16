<?php

namespace Tests\Unit;

use App\Services\AccuracyScorer;
use PHPUnit\Framework\TestCase;

class AccuracyScorerTest extends TestCase
{
    public function test_evaluates_accuracy_levels(): void
    {
        $scorer = new AccuracyScorer();

        // Menggunakan reflection untuk menguji method protected
        $ref = new \ReflectionClass(AccuracyScorer::class);
        $evaluateAccuracy = $ref->getMethod('evaluateAccuracy');
        $evaluateAccuracy->setAccessible(true);

        $res96 = $evaluateAccuracy->invoke($scorer, 96.0);
        $this->assertEquals('Sangat Teliti (High Precision)', $res96['label']);
        $this->assertEquals('success', $res96['tone']);

        $res89 = $evaluateAccuracy->invoke($scorer, 89.0);
        $this->assertEquals('Teliti & Cermat', $res89['label']);
        $this->assertEquals('primary', $res89['tone']);

        $res65 = $evaluateAccuracy->invoke($scorer, 65.0);
        $this->assertEquals('Kurang Teliti (Rentan Keliru)', $res65['label']);
        $this->assertEquals('warning', $res65['tone']);

        $res50 = $evaluateAccuracy->invoke($scorer, 50.0);
        $this->assertEquals('Sangat Ceroboh / Berisiko', $res50['label']);
        $this->assertEquals('danger', $res50['tone']);
    }

    public function test_evaluates_work_attitude_quadrant(): void
    {
        $scorer = new AccuracyScorer();
        $ref = new \ReflectionClass(AccuracyScorer::class);
        $determineQuadrant = $ref->getMethod('determineQuadrant');
        $determineQuadrant->setAccessible(true);

        // Q1: Akurasi Tinggi (95%), Cepat (95% selesai, 4.0 soal/m)
        $q1 = $determineQuadrant->invoke($scorer, 95.0, 95.0, 4.0);
        $this->assertEquals('Q1', $q1['code']);
        $this->assertStringContainsString('Cepat & Sangat Teliti', $q1['title']);

        // Q2: Akurasi Tinggi (92%), Lambat (70% selesai, 2.0 soal/m)
        $q2 = $determineQuadrant->invoke($scorer, 92.0, 70.0, 2.0);
        $this->assertEquals('Q2', $q2['code']);
        $this->assertStringContainsString('Hati-Hati & Akurat', $q2['title']);

        // Q3: Akurasi Rendah (70%), Cepat (95% selesai, 4.5 soal/m)
        $q3 = $determineQuadrant->invoke($scorer, 70.0, 95.0, 4.5);
        $this->assertEquals('Q3', $q3['code']);
        $this->assertStringContainsString('Cepat tapi Kurang Cermat', $q3['title']);

        // Q4: Akurasi Rendah (60%), Lambat (60% selesai, 1.5 soal/m)
        $q4 = $determineQuadrant->invoke($scorer, 60.0, 60.0, 1.5);
        $this->assertEquals('Q4', $q4['code']);
        $this->assertStringContainsString('Lambat & Kurang Cermat', $q4['title']);
    }

    public function test_detects_mental_fatigue_in_stability_analysis(): void
    {
        $scorer = new AccuracyScorer();
        $ref = new \ReflectionClass(AccuracyScorer::class);
        $evaluateStability = $ref->getMethod('evaluateStability');
        $evaluateStability->setAccessible(true);

        // Kasus fatigue: Fase 1 = 95%, Fase 2 = 90%, Fase 3 anjlok ke 60%
        $fatigueSegments = [
            'phase_1' => ['accuracy_percent' => 95.0],
            'phase_2' => ['accuracy_percent' => 90.0],
            'phase_3' => ['accuracy_percent' => 60.0],
        ];

        $resFatigue = $evaluateStability->invoke($scorer, $fatigueSegments);
        $this->assertEquals('fatigue', $resFatigue['key']);
        $this->assertStringContainsString('Menurun di Akhir', $resFatigue['label']);

        // Kasus stabil: Fase 1 = 92%, Fase 2 = 90%, Fase 3 = 91%
        $stableSegments = [
            'phase_1' => ['accuracy_percent' => 92.0],
            'phase_2' => ['accuracy_percent' => 90.0],
            'phase_3' => ['accuracy_percent' => 91.0],
        ];

        $resStable = $evaluateStability->invoke($scorer, $stableSegments);
        $this->assertEquals('stable', $resStable['key']);
        $this->assertStringContainsString('Sangat Stabil', $resStable['label']);
    }
}
