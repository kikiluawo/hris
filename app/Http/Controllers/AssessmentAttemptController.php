<?php

namespace App\Http\Controllers;

use App\Models\AssessmentAttempt;
use App\Models\AssessmentQuestion;
use App\Models\AssessmentSnapshot;
use App\Services\AssessmentRunner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AssessmentAttemptController extends Controller
{
    public function show(AssessmentAttempt $attempt, AssessmentRunner $runner): View
    {
        $runner->finalizeIfExpired($attempt);
        $attempt->refresh();

        $attempt->load([
            'invitation.assessment.questions',
            'invitation.application.candidate',
            'invitation.application.jobOpening',
            'answers.question',
            'events',
            'snapshots',
        ]);

        $assessment = $attempt->invitation->assessment;
        $answers = $attempt->answers->keyBy('assessment_question_id');
        $dimensionNames = $assessment->questions
            ->where('type', AssessmentQuestion::TYPE_SCALE)
            ->map(fn (AssessmentQuestion $question) => $question->dimensionLabel())
            ->unique()
            ->values();

        $discReport = $attempt->disc_report;
        if ($discReport === null && $attempt->hasDiscQuestions()) {
            $discReport = app(\App\Services\DiscScorer::class)->calculate($attempt);
            if ($discReport !== null) {
                $attempt->forceFill(['disc_report' => $discReport])->save();
            }
        }

        $aptitudeReport = $attempt->aptitude_report;
        if ($aptitudeReport === null && $attempt->hasAptitudeQuestions()) {
            $aptitudeReport = app(\App\Services\AptitudeScorer::class)->calculate($attempt);
            if ($aptitudeReport !== null) {
                $attempt->forceFill(['aptitude_report' => $aptitudeReport])->save();
            }
        }

        $accuracyReport = $attempt->accuracy_report;
        if ($accuracyReport === null && $attempt->hasAccuracyQuestions()) {
            $accuracyReport = app(\App\Services\AccuracyScorer::class)->calculate($attempt);
            if ($accuracyReport !== null) {
                $attempt->forceFill(['accuracy_report' => $accuracyReport])->save();
            }
        }

        return view('recruitment.assessments.attempt', [
            'attempt' => $attempt,
            'assessment' => $assessment,
            'questions' => $assessment->questions,
            'answers' => $answers,
            'dimensionNames' => $dimensionNames,
            'dimensionScores' => $attempt->dimension_scores ?? [],
            'dimensionReport' => $runner->dimensionReport($attempt),
            'discReport' => $discReport,
            'aptitudeReport' => $aptitudeReport,
            'accuracyReport' => $accuracyReport,
            'fullyGraded' => $attempt->isFullyGraded(),
            'passed' => $attempt->score !== null
                && $attempt->isFullyGraded()
                && $attempt->score >= $assessment->passing_score,
        ]);
    }

    public function grade(Request $request, AssessmentAttempt $attempt, AssessmentRunner $runner): RedirectResponse
    {
        if ($attempt->isInProgress()) {
            return back()->with('error', 'Tes masih berjalan. Penilaian bisa dilakukan setelah kandidat selesai.');
        }

        $attempt->load(['answers.question', 'invitation.assessment.questions']);

        $data = $request->validate([
            'points' => ['nullable', 'array'],
            'points.*' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'dimensions' => ['nullable', 'array'],
            'dimensions.*' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ], [], [
            'points.*' => 'poin',
            'dimensions.*' => 'skor dimensi',
            'notes' => 'catatan',
        ]);

        foreach ($attempt->answers as $answer) {
            if (! $answer->question?->isEssay()) {
                continue;
            }

            $questionPoints = $answer->question->points;

            if (! array_key_exists($answer->id, $data['points'] ?? [])) {
                continue;
            }

            $points = $data['points'][$answer->id];

            if ($points === null || $points === '') {
                $answer->forceFill(['points_awarded' => null])->save();

                continue;
            }

            $answer->forceFill([
                'points_awarded' => min((float) $points, (float) $questionPoints),
                'is_correct' => null,
            ])->save();
        }

        $dimensionNames = $attempt->scaleDimensions();
        $dimensionScores = [];

        $submittedDimensions = $data['dimensions'] ?? [];

        foreach ($dimensionNames as $dimension) {
            $value = $submittedDimensions[$dimension] ?? null;

            if ($value === null || $value === '') {
                continue;
            }

            $dimensionScores[$dimension] = round((float) $value, 2);
        }

        $attempt->forceFill([
            'dimension_scores' => $dimensionNames === [] ? null : $dimensionScores,
            'review_notes' => filled($data['notes'] ?? null) ? trim((string) $data['notes']) : null,
        ])->save();

        $runner->recalculateScore($attempt);

        return back()->with('status', 'Penilaian disimpan dan skor tes diperbarui.');
    }

    public function viewSnapshot(AssessmentSnapshot $snapshot): BinaryFileResponse
    {
        $disk = Storage::disk('local');
        abort_unless($disk->exists($snapshot->file_path), 404);

        return response()->file($disk->path($snapshot->file_path), [
            'Content-Type' => 'image/jpeg',
            'Cache-Control' => 'private, max-age=86400',
        ]);
    }
}
