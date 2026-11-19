<?php

namespace App\Services;

use App\Models\Application;
use App\Models\AssessmentAttempt;
use App\Models\AssessmentInvitation;
use App\Models\AssessmentQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AssessmentRunner
{
    public const VIOLATION_TYPES = [
        'tab_hidden',
        'tab_hidden_long',
        'fullscreen_exit',
        'camera_lost',
        'warning_acknowledged',
    ];

    public function start(AssessmentInvitation $invitation, Request $request): AssessmentAttempt
    {
        if ($invitation->attempt !== null) {
            return $invitation->attempt;
        }

        $attempt = $invitation->attempt()->create([
            'token' => Str::random(48),
            'status' => AssessmentAttempt::STATUS_IN_PROGRESS,
            'started_at' => now(),
            'ends_at' => now()->addMinutes($invitation->durationMinutes()),
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 500, ''),
        ]);

        $this->event($attempt, 'started');

        return $attempt;
    }

    public function finalize(AssessmentAttempt $attempt, string $status, ?string $reason): AssessmentAttempt
    {
        if (! $attempt->isInProgress()) {
            return $attempt->fresh();
        }

        $attempt->forceFill([
            'status' => $status,
            'end_reason' => $reason,
            'submitted_at' => now(),
        ])->save();

        $attempt->forceFill([
            'dimension_scores' => $this->mergeAutomaticDimensions($attempt),
            'disc_report' => app(DiscScorer::class)->calculate($attempt),
            'aptitude_report' => app(AptitudeScorer::class)->calculate($attempt),
            'accuracy_report' => app(AccuracyScorer::class)->calculate($attempt),
        ])->save();

        $this->recalculateScore($attempt);

        $application = $attempt->invitation?->application;
        if ($application !== null && ! in_array($application->status, [Application::STATUS_REJECTED, Application::STATUS_HIRED], true)) {
            $application->update(['status' => Application::STATUS_ASSESSMENT_DONE]);
        }

        $this->event($attempt, match (true) {
            $status === AssessmentAttempt::STATUS_ENDED => 'ended',
            $reason === AssessmentAttempt::REASON_TIME_UP => 'time_up',
            default => 'submitted',
        }, ['reason' => $reason]);

        return $attempt->fresh();
    }

    public function finalizeIfExpired(AssessmentAttempt $attempt): bool
    {
        if (! $attempt->isInProgress() || ! $attempt->hasExpired()) {
            return false;
        }

        $this->finalize($attempt, AssessmentAttempt::STATUS_SUBMITTED, AssessmentAttempt::REASON_TIME_UP);

        return true;
    }

    public function saveAnswer(
        AssessmentAttempt $attempt,
        AssessmentQuestion $question,
        ?int $selectedOption,
        ?string $answerText,
        ?int $mostOption = null,
        ?int $leastOption = null
    ): AssessmentAttempt
    {
        $payload = match (true) {
            $question->isMultipleChoice() => [
                'selected_option' => $selectedOption,
                'most_option' => null,
                'least_option' => null,
                'answer' => null,
                'is_correct' => $selectedOption !== null && $selectedOption === $question->correct_option,
                'points_awarded' => $selectedOption !== null && $selectedOption === $question->correct_option
                    ? $question->points
                    : 0,
            ],
            $question->isScale() => [
                'selected_option' => $selectedOption,
                'most_option' => null,
                'least_option' => null,
                'answer' => null,
                'is_correct' => null,
                'points_awarded' => null,
            ],
            $question->isDisc() => [
                'selected_option' => $selectedOption ?? $mostOption,
                'most_option' => $mostOption,
                'least_option' => $leastOption,
                'answer' => ($mostOption !== null || $leastOption !== null)
                    ? json_encode(['most' => $mostOption, 'least' => $leastOption])
                    : null,
                'is_correct' => null,
                'points_awarded' => null,
            ],
            default => [
                'selected_option' => null,
                'most_option' => null,
                'least_option' => null,
                'answer' => filled($answerText) ? $answerText : null,
                'is_correct' => null,
                'points_awarded' => null,
            ],
        };

        $attempt->answers()->updateOrCreate(
            ['assessment_question_id' => $question->id],
            $payload,
        );

        return $attempt->fresh();
    }

    public function recordViolation(AssessmentAttempt $attempt, string $type): array
    {
        if (! $attempt->isInProgress()) {
            return $this->endedResponse($attempt);
        }

        if ($attempt->hasExpired()) {
            $this->finalize($attempt, AssessmentAttempt::STATUS_SUBMITTED, AssessmentAttempt::REASON_TIME_UP);

            return $this->endedResponse($attempt->fresh(), 'Waktu tes sudah habis.');
        }

        if ($type === 'warning_acknowledged') {
            $this->event($attempt, $type);

            return ['action' => 'acknowledged'];
        }

        $recentIncident = $attempt->events()
            ->whereIn('type', ['tab_hidden', 'fullscreen_exit', 'camera_lost'])
            ->where('occurred_at', '>=', now()->subSeconds(5))
            ->exists();

        if ($recentIncident) {
            $this->event($attempt, $type, ['debounced' => true]);

            return [
                'action' => 'warn',
                'count' => max(1, $attempt->violation_count),
                'remaining' => $attempt->remainingSeconds(),
            ];
        }

        $this->event($attempt, $type);
        $attempt->increment('violation_count');
        $attempt->refresh();

        if ($type === 'tab_hidden_long') {
            $this->finalize($attempt, AssessmentAttempt::STATUS_ENDED, AssessmentAttempt::REASON_TAB_HIDDEN);

            return $this->endedResponse($attempt->fresh(), 'Kandidat meninggalkan halaman lebih dari 15 detik.');
        }

        if ($attempt->violation_count >= 2) {
            $reason = $type === 'camera_lost'
                ? AssessmentAttempt::REASON_CAMERA
                : AssessmentAttempt::REASON_VIOLATION;

            $this->finalize($attempt, AssessmentAttempt::STATUS_ENDED, $reason);

            return $this->endedResponse($attempt->fresh());
        }

        return [
            'action' => 'warn',
            'count' => $attempt->violation_count,
            'remaining' => $attempt->remainingSeconds(),
        ];
    }

    public function recalculateScore(AssessmentAttempt $attempt): void
    {
        $questions = $attempt->invitation->assessment->questions()->get(['id', 'type', 'points']);
        $answers = $attempt->answers()->get()->keyBy('assessment_question_id');

        $objective = $questions->reject(fn (AssessmentQuestion $question) => $question->isScale() || $question->isDisc());

        $objectivePercent = null;

        if ($objective->isNotEmpty()) {
            $max = (int) $objective->sum('points');
            $earned = 0.0;

            foreach ($objective as $question) {
                $earned += (float) ($answers->get($question->id)?->points_awarded ?? 0);
            }

            $objectivePercent = $max > 0 ? ($earned / $max) * 100 : 0.0;
        }

        $dimensionPercent = null;

        if ($questions->contains(fn (AssessmentQuestion $question) => $question->isScale())) {
            $dimensionScores = collect($attempt->dimension_scores ?? [])
                ->filter(fn ($score) => $score !== null && $score !== '');

            if ($dimensionScores->isNotEmpty()) {
                $dimensionPercent = (float) $dimensionScores->avg();
            }
        }

        $parts = collect([$objectivePercent, $dimensionPercent])->reject(fn ($percent) => $percent === null);

        $attempt->forceFill([
            'score' => $parts->isEmpty() ? null : round((float) $parts->avg(), 2),
            'disc_report' => $attempt->hasDiscQuestions() ? app(DiscScorer::class)->calculate($attempt) : $attempt->disc_report,
            'aptitude_report' => $attempt->hasAptitudeQuestions() ? app(AptitudeScorer::class)->calculate($attempt) : $attempt->aptitude_report,
            'accuracy_report' => $attempt->hasAccuracyQuestions() ? app(AccuracyScorer::class)->calculate($attempt) : $attempt->accuracy_report,
        ])->save();
    }

    public function dimensionReport(AssessmentAttempt $attempt): array
    {
        $questions = $attempt->invitation?->assessment?->questions()
            ->where('type', AssessmentQuestion::TYPE_SCALE)
            ->get(['id', 'dimension', 'options']);

        if ($questions === null || $questions->isEmpty()) {
            return [];
        }

        $answers = $attempt->answers()->get(['assessment_question_id', 'selected_option'])->keyBy('assessment_question_id');
        $report = [];

        foreach ($questions as $question) {
            $dimension = $question->dimensionLabel();
            $report[$dimension] ??= ['score' => null, 'answered' => 0, 'total' => 0, 'sum' => 0.0];
            $report[$dimension]['total']++;

            $optionCount = count($question->options ?? []);
            $selected = $answers->get($question->id)?->selected_option;

            if ($optionCount < 2 || $selected === null || $selected < 0 || $selected >= $optionCount) {
                continue;
            }

            $report[$dimension]['answered']++;
            $report[$dimension]['sum'] += ($selected / ($optionCount - 1)) * 100;
        }

        foreach ($report as $dimension => $item) {
            $report[$dimension]['score'] = $item['answered'] > 0
                ? round($item['sum'] / $item['answered'], 2)
                : null;
            unset($report[$dimension]['sum']);
        }

        return $report;
    }

    public function automaticDimensionScores(AssessmentAttempt $attempt): array
    {
        return collect($this->dimensionReport($attempt))
            ->filter(fn (array $item) => $item['score'] !== null)
            ->map(fn (array $item) => $item['score'])
            ->all();
    }

    public function mergeAutomaticDimensions(AssessmentAttempt $attempt): ?array
    {
        $merged = array_replace($this->automaticDimensionScores($attempt), $attempt->dimension_scores ?? []);

        return $merged === [] ? null : $merged;
    }

    public function event(AssessmentAttempt $attempt, string $type, array $meta = []): void
    {
        $attempt->events()->create([
            'type' => $type,
            'meta' => $meta === [] ? null : $meta,
            'occurred_at' => now(),
        ]);
    }

    private function endedResponse(AssessmentAttempt $attempt, ?string $message = null): array
    {
        return [
            'action' => 'ended',
            'status' => $attempt->status,
            'reason' => $attempt->end_reason,
            'redirect' => route('assessment.finished', $attempt->token),
            'message' => $message ?? match ($attempt->end_reason) {
                AssessmentAttempt::REASON_TAB_HIDDEN => 'Tes dihentikan karena Anda meninggalkan halaman lebih dari 15 detik.',
                AssessmentAttempt::REASON_CAMERA => 'Tes dihentikan karena kamera tidak aktif.',
                AssessmentAttempt::REASON_VIOLATION => 'Tes dihentikan karena pelanggaran aturan kedua.',
                AssessmentAttempt::REASON_TIME_UP => 'Waktu tes sudah habis.',
                default => 'Tes sudah berakhir.',
            },
        ];
    }
}
