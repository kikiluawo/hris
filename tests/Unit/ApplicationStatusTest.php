<?php

namespace Tests\Unit;

use App\Models\Application;
use PHPUnit\Framework\TestCase;

class ApplicationStatusTest extends TestCase
{
    public function test_every_status_has_a_label_and_tone(): void
    {
        foreach (array_keys(Application::statusOptions()) as $status) {
            $application = new Application(['status' => $status]);

            $this->assertNotSame('', $application->statusLabel());
            $this->assertContains($application->statusTone(), ['neutral', 'success', 'warning', 'danger', 'info']);
        }
    }

    public function test_interview_scheduled_counts_as_passed_screening(): void
    {
        $application = new Application(['status' => Application::STATUS_INTERVIEW_SCHEDULED]);

        $this->assertTrue($application->hasPassedScreening());
    }

    public function test_manual_review_does_not_count_as_passed_screening(): void
    {
        $application = new Application(['status' => Application::STATUS_REVIEW]);

        $this->assertFalse($application->hasPassedScreening());
    }
}
