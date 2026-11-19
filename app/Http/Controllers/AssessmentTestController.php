<?php

namespace App\Http\Controllers;

use App\Models\AssessmentAttempt;
use App\Models\AssessmentInvitation;
use App\Models\AssessmentQuestion;
use App\Services\AssessmentRunner;
use App\Support\AssessmentImageStore;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AssessmentTestController extends Controller
{
    public function landing(string $token): View
    {
        $invitation = $this->invitation($token);
        $invitation->assessment->loadCount('questions');

        return view('assessment.landing', [
            'invitation' => $invitation,
            'assessment' => $invitation->assessment,
            'duration' => $invitation->durationMinutes(),
            'blocked' => $invitation->blockedReason(),
            'attempt' => $invitation->attempt,
        ]);
    }

    public function start(string $token, AssessmentRunner $runner): JsonResponse
    {
        $invitation = $this->invitation($token);

        if ($reason = $invitation->blockedReason()) {
            return response()->json(['message' => $reason], 422);
        }

        $existing = $invitation->attempt;

        if ($existing !== null) {
            $runner->finalizeIfExpired($existing);
            $existing->refresh();

            if ($existing->isInProgress() && ! $existing->accessExpired()) {
                return response()->json([
                    'redirect' => route('assessment.take', $existing->token),
                    'ends_at' => $existing->ends_at->toIso8601String(),
                ]);
            }

            return response()->json([
                'message' => 'Undangan ini sudah dipakai mengerjakan tes. Hubungi HRD bila perlu mengulang.',
                'redirect' => route('assessment.finished', $existing->token),
            ], 422);
        }

        $attempt = $runner->start($invitation, request());

        return response()->json([
            'redirect' => route('assessment.take', $attempt->token),
            'ends_at' => $attempt->ends_at->toIso8601String(),
        ]);
    }

    public function take(string $token, AssessmentRunner $runner): View|RedirectResponse
    {
        $attempt = $this->attempt($token);

        $cookieName = 'assessment_lock_' . $attempt->id;
        $cookieToken = request()->cookie($cookieName);

        if ($attempt->lock_token !== null && ! hash_equals($attempt->lock_token, (string) $cookieToken)) {
            if ($attempt->isInProgress()) {
                return redirect()
                    ->route('assessment.finished', $token)
                    ->with('info', 'Tes sedang dikerjakan di perangkat lain. Hubungi HRD bila perlu mengulang.');
            }
        }

        if ($attempt->lock_token === null || ! hash_equals($attempt->lock_token, (string) $cookieToken)) {
            $lockToken = Str::random(40);
            $attempt->forceFill(['lock_token' => $lockToken])->save();
            cookie()->queue(cookie($cookieName, $lockToken, 60 * 24));
        }

        $attempt->forceFill(['access_token' => Str::random(40)])->save();
        $attempt->refresh();
        $assessment = $attempt->invitation->assessment;

        if ($attempt->accessExpired()) {
            return redirect()->route('assessment.finished', $token);
        }

        if ($runner->finalizeIfExpired($attempt)) {
            return redirect()
                ->route('assessment.finished', $token)
                ->with('info', 'Waktu tes sudah habis dan jawaban yang tersimpan sudah dikirim.');
        }

        if (! $attempt->isInProgress()) {
            return redirect()->route('assessment.finished', $token);
        }

        if (! $assessment->isPublished()) {
            return redirect()
                ->route('assessment.finished', $token)
                ->with('info', 'Tes ini sedang ditutup oleh HRD.');
        }

        $answers = $attempt->answers->keyBy('assessment_question_id');

        $payload = $assessment->questions->map(function (AssessmentQuestion $question) use ($answers) {
            $answer = $answers->get($question->id);
            $options = $question->options ?? [];

            $normalizedOptions = array_map(function ($opt) {
                if (is_array($opt) && isset($opt['text'])) {
                    return $opt['text'];
                }

                return (string) $opt;
            }, $options);

            return [
                'id' => $question->id,
                'type' => $question->type,
                'dimension' => $question->dimension,
                'question' => $question->question,
                'image' => AssessmentImageStore::url($question->image_path),
                'options' => $normalizedOptions,
                'option_images' => array_map(fn (int $index) => AssessmentImageStore::url($question->optionImage($index)), array_keys($options)),
                'points' => $question->points,
                'selected_option' => $answer?->selected_option,
                'most_option' => $answer?->most_option,
                'least_option' => $answer?->least_option,
                'answer' => $answer?->answer ?? '',
            ];
        })->values()->all();

        return view('assessment.take', [
            'attempt' => $attempt,
            'assessment' => $assessment,
            'questions' => $payload,
            'endsAt' => $attempt->ends_at->toIso8601String(),
            'remaining' => $attempt->remainingSeconds(),
            'answeredCount' => $attempt->answeredCount(),
        ]);
    }

    public function finished(string $token, AssessmentRunner $runner): View
    {
        $attempt = $this->attempt($token);
        $runner->finalizeIfExpired($attempt);
        $attempt->refresh();

        return view('assessment.finished', [
            'attempt' => $attempt,
            'assessment' => $attempt->invitation->assessment,
            'expired' => $attempt->accessExpired(),
        ]);
    }

    public function answer(Request $request, string $token, AssessmentRunner $runner): JsonResponse
    {
        $attempt = $this->attempt($token);

        if (! $this->hasValidAccess($attempt, $request)) {
            return response()->json([
                'message' => 'Tes sedang dibuka di tab lain. Lanjutkan di tab terakhir yang dibuka.',
                'redirect' => route('assessment.finished', $token),
            ], 409);
        }

        if (! $attempt->isInProgress() || $attempt->accessExpired()) {
            return response()->json($this->endedPayload($attempt, 'Tes sudah berakhir.'));
        }

        if ($runner->finalizeIfExpired($attempt)) {
            return response()->json($this->endedPayload($attempt->fresh(), 'Waktu tes sudah habis.'));
        }

        $data = $request->validate([
            'question_id' => ['required', 'integer'],
            'selected_option' => ['nullable', 'integer', 'min:0', 'max:20'],
            'most_option' => ['nullable', 'integer', 'min:0', 'max:20'],
            'least_option' => ['nullable', 'integer', 'min:0', 'max:20'],
            'answer' => ['nullable', 'string', 'max:20000'],
        ]);

        $question = $attempt->invitation->assessment->questions()
            ->whereKey($data['question_id'])
            ->first();

        if ($question === null) {
            return response()->json(['message' => 'Soal tidak ditemukan pada tes ini.'], 422);
        }

        $selected = $data['selected_option'] ?? null;
        $most = $data['most_option'] ?? null;
        $least = $data['least_option'] ?? null;
        $text = $data['answer'] ?? null;

        if ($question->isDisc()) {
            $optCount = count($question->options ?? []);
            if ($most !== null && ($most < 0 || $most >= $optCount)) {
                return response()->json(['message' => 'Pilihan Paling (Most) tidak valid.'], 422);
            }
            if ($least !== null && ($least < 0 || $least >= $optCount)) {
                return response()->json(['message' => 'Pilihan Kurang (Least) tidak valid.'], 422);
            }
            if ($most !== null && $least !== null && $most === $least) {
                return response()->json(['message' => 'Pernyataan yang sama tidak boleh dipilih sebagai Paling dan Kurang sekaligus.'], 422);
            }
            $selected = $most;
            $text = null;
        } elseif ($question->isMultipleChoice() || $question->isScale()) {
            if ($selected === null || $selected >= count($question->options ?? [])) {
                return response()->json(['message' => 'Pilihan jawaban tidak valid.'], 422);
            }

            $text = null;
            $most = null;
            $least = null;
        } else {
            $selected = null;
            $most = null;
            $least = null;
        }

        $runner->saveAnswer($attempt, $question, $selected, $text, $most, $least);

        return response()->json([
            'ok' => true,
            'saved_at' => now()->toIso8601String(),
            'remaining' => $attempt->fresh()->remainingSeconds(),
        ]);
    }

    public function violation(Request $request, string $token, AssessmentRunner $runner): JsonResponse
    {
        $attempt = $this->attempt($token);

        if (! $this->hasValidAccess($attempt, $request)) {
            return response()->json(['message' => 'Tes sedang dibuka di tab lain.'], 409);
        }

        $data = $request->validate([
            'type' => ['required', Rule::in(AssessmentRunner::VIOLATION_TYPES)],
        ]);

        return response()->json($runner->recordViolation($attempt, $data['type']));
    }

    public function snapshot(Request $request, string $token): JsonResponse
    {
        $attempt = $this->attempt($token);

        if (! $this->hasValidAccess($attempt, $request)) {
            return response()->json(['message' => 'Tes sedang dibuka di tab lain.'], 409);
        }

        if (! $attempt->isInProgress()) {
            return response()->json(['status' => 'ignored'], 200);
        }

        $data = $request->validate([
            'image' => ['required', 'string'],
            'trigger' => ['nullable', 'string', 'max:50'],
        ]);

        $imageStr = $data['image'];
        if (str_contains($imageStr, ',')) {
            $imageStr = explode(',', $imageStr, 2)[1];
        }

        $bytes = base64_decode($imageStr, true);
        if ($bytes === false || strlen($bytes) > 500 * 1024) {
            return response()->json(['error' => 'Ukuran atau format gambar tidak valid.'], 422);
        }

        $filename = 'snap_' . now()->format('Ymd_His') . '_' . Str::random(8) . '.jpg';
        $path = 'assessments/snapshots/' . $attempt->id . '/' . $filename;
        Storage::disk('local')->put($path, $bytes);

        $snapshot = $attempt->snapshots()->create([
            'file_path' => $path,
            'trigger' => $data['trigger'] ?? 'periodic',
            'captured_at' => now(),
        ]);

        return response()->json([
            'ok' => true,
            'id' => $snapshot->id,
        ]);
    }

    public function status(Request $request, string $token, AssessmentRunner $runner): JsonResponse
    {
        $attempt = $this->attempt($token);

        if (! $this->hasValidAccess($attempt, $request)) {
            return response()->json(['message' => 'Tes sedang dibuka di tab lain.'], 409);
        }

        $runner->finalizeIfExpired($attempt);
        $attempt->refresh();

        return response()->json([
            'status' => $attempt->status,
            'reason' => $attempt->end_reason,
            'remaining' => $attempt->remainingSeconds(),
            'redirect' => route('assessment.finished', $token),
            'message' => match ($attempt->end_reason) {
                AssessmentAttempt::REASON_TAB_HIDDEN => 'Tes dihentikan karena Anda meninggalkan halaman lebih dari 15 detik.',
                AssessmentAttempt::REASON_CAMERA => 'Tes dihentikan karena kamera tidak aktif.',
                AssessmentAttempt::REASON_VIOLATION => 'Tes dihentikan karena pelanggaran aturan kedua.',
                AssessmentAttempt::REASON_TIME_UP => 'Waktu tes sudah habis. Jawaban yang tersimpan sudah dikirim.',
                default => null,
            },
        ]);
    }

    public function submit(Request $request, string $token, AssessmentRunner $runner): JsonResponse
    {
        $attempt = $this->attempt($token);

        if (! $this->hasValidAccess($attempt, $request)) {
            return response()->json([
                'message' => 'Tes sedang dibuka di tab lain. Lanjutkan di tab terakhir yang dibuka.',
                'redirect' => route('assessment.finished', $token),
            ], 409);
        }

        if ($attempt->isInProgress()) {
            $reason = $attempt->hasExpired()
                ? AssessmentAttempt::REASON_TIME_UP
                : AssessmentAttempt::REASON_SUBMITTED;

            $runner->finalize($attempt, AssessmentAttempt::STATUS_SUBMITTED, $reason);
        }

        return response()->json([
            'redirect' => route('assessment.finished', $token),
        ]);
    }

    private function hasValidAccess(AssessmentAttempt $attempt, Request $request): bool
    {
        $provided = $request->input('access_token', $request->header('X-Access-Token'));

        return $attempt->access_token !== null
            && hash_equals($attempt->access_token, (string) $provided);
    }

    private function endedPayload(AssessmentAttempt $attempt, string $message): array
    {
        return [
            'action' => 'ended',
            'redirect' => route('assessment.finished', $attempt->token),
            'message' => $message,
        ];
    }

    private function invitation(string $token): AssessmentInvitation
    {
        return AssessmentInvitation::with(['assessment', 'application.candidate', 'attempt'])
            ->where('token', $token)
            ->firstOrFail();
    }

    private function attempt(string $token): AssessmentAttempt
    {
        return AssessmentAttempt::with(['invitation.assessment.questions', 'invitation.application.candidate', 'answers'])
            ->where('token', $token)
            ->firstOrFail();
    }
}