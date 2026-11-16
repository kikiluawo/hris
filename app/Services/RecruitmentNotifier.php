<?php

namespace App\Services;

use App\Mail\InterviewInvitation;
use App\Mail\ScreeningPassed;
use App\Models\Application;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class RecruitmentNotifier
{
    
    public function sendScreeningPassed(Application $application): bool
    {
        $application->loadMissing(['candidate', 'jobOpening.company']);

        if ($application->screening_notified_at !== null || ! $application->candidate) {
            return false;
        }

        try {
            Mail::to($application->candidate->email)->send(new ScreeningPassed($application));
        } catch (Throwable $e) {
            $this->logFailure('screening lulus', $application, $e);

            return false;
        }

        $application->forceFill(['screening_notified_at' => now()])->save();

        return true;
    }

    public function sendInterviewInvitation(Application $application): bool
    {
        $application->loadMissing(['candidate', 'jobOpening.company']);

        if ($application->interview_at === null || ! $application->candidate) {
            return false;
        }

        try {
            Mail::to($application->candidate->email)->send(new InterviewInvitation($application));
        } catch (Throwable $e) {
            $this->logFailure('undangan interview', $application, $e);

            return false;
        }

        $application->forceFill(['interview_sent_at' => now()])->save();

        return true;
    }

    private function logFailure(string $type, Application $application, Throwable $e): void
    {
        Log::warning("Gagal mengirim email {$type}.", [
            'application_id' => $application->id,
            'candidate_email' => $application->candidate?->email,
            'error' => $e->getMessage(),
        ]);
    }
}
