<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Assessment;
use App\Models\AssessmentInvitation;
use App\Models\AssessmentQuestion;
use App\Models\QuestionBank;
use App\Support\QuestionNormalizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AssessmentController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', '');

        $query = Assessment::withCount(['questions', 'invitations', 'attempts'])
            ->with(['questions:id,assessment_id,type,dimension'])
            ->latest('id');

        if ($q !== '') {
            $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $q).'%';
            $query->where(function ($sub) use ($like) {
                $sub->where('title', 'like', $like)
                    ->orWhere('description', 'like', $like);
            });
        }

        if ($status !== '' && in_array($status, [Assessment::STATUS_DRAFT, Assessment::STATUS_PUBLISHED, Assessment::STATUS_CLOSED], true)) {
            $query->where('status', $status);
        }

        $allAssessments = Assessment::withCount(['questions', 'invitations', 'attempts'])->get();
        $assessments = $query->paginate(15)->withQueryString();

        $presets = [
            'comprehensive' => Assessment::where('title', 'like', '%Komprehensif%')->first(),
            'disc' => Assessment::where('title', 'like', '%Kepribadian DISC%')->first(),
            'aptitude' => Assessment::where('title', 'like', '%Bakat Kognitif & IQ%')->first(),
            'accuracy' => Assessment::where('title', 'like', '%Ketelitian Kerja%')->first(),
        ];

        return view('recruitment.assessments.index', [
            'assessments' => $assessments,
            'filters' => [
                'q' => $q,
                'status' => $status,
            ],
            'presets' => $presets,
            'stats' => [
                'assessments' => $allAssessments->count(),
                'published' => $allAssessments->where('status', Assessment::STATUS_PUBLISHED)->count(),
                'invitations' => $allAssessments->sum('invitations_count'),
                'attempts' => $allAssessments->sum('attempts_count'),
                'inProgress' => \App\Models\AssessmentAttempt::where('status', \App\Models\AssessmentAttempt::STATUS_IN_PROGRESS)->count(),
            ],
        ]);
    }

    public function seedDisc(Request $request): RedirectResponse
    {
        $assessment = \App\Services\DiscBank::createOrUpdateAssessment($request->user());

        return redirect()
            ->route('assessments.show', $assessment)
            ->with('status', 'Paket Tes Kepribadian DISC (24 soal) berhasil dibuat/disinkronkan dan siap digunakan.');
    }

    public function seedAptitude(Request $request): RedirectResponse
    {
        $assessment = \App\Services\AptitudeBank::createOrUpdateAssessment($request->user());

        return redirect()
            ->route('assessments.show', $assessment)
            ->with('status', 'Paket Tes Bakat Kognitif & IQ (30 soal) berhasil dibuat/disinkronkan dan siap digunakan.');
    }

    public function seedAccuracy(Request $request): RedirectResponse
    {
        $assessment = \App\Services\AccuracyBank::createOrUpdateAssessment($request->user());

        return redirect()
            ->route('assessments.show', $assessment)
            ->with('status', 'Paket Tes Ketelitian Kerja & Verifikasi Data (40 soal) berhasil dibuat/disinkronkan dan siap digunakan.');
    }

    public function seedComprehensive(Request $request): RedirectResponse
    {
        $assessment = \App\Services\ComprehensiveBank::createOrUpdateAssessment($request->user());

        return redirect()
            ->route('assessments.show', $assessment)
            ->with('status', 'Paket Tes Komprehensif HRD (94 soal: DISC, IQ & Ketelitian) berhasil dibuat/disinkronkan dan siap digunakan.');
    }

    public function create(): View
    {
        return view('recruitment.assessments.form', [
            'assessment' => null,
            'initialQuestions' => old('questions_json')
                ? (json_decode((string) old('questions_json'), true) ?: [])
                : [],
            'bankItems' => $this->bankItems(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateForm($request);
        $questions = $this->normalizeQuestions($request);

        $assessment = DB::transaction(function () use ($request, $data, $questions) {
            $assessment = Assessment::create([
                ...$data,
                'created_by' => $request->user()?->id,
            ]);

            $this->replaceQuestions($assessment, $questions);

            return $assessment;
        });

        return redirect()
            ->route('assessments.show', $assessment)
            ->with('status', 'Tes "' . $assessment->title . '" dibuat. Undang kandidat untuk mulai mengerjakan.');
    }

    public function show(Assessment $assessment): View
    {
        $assessment->load([
            'questions',
            'invitations.application.candidate',
            'invitations.application.jobOpening',
            'invitations.attempt',
        ]);

        $attempts = $assessment->attempts()
            ->with(['invitation.application.candidate', 'invitation.application.jobOpening'])
            ->latest('started_at')
            ->get();

        $applicants = Application::with(['candidate', 'jobOpening'])
            ->whereNotIn('id', $assessment->invitations->pluck('application_id'))
            ->latest('id')
            ->get();

        return view('recruitment.assessments.show', [
            'assessment' => $assessment,
            'attempts' => $attempts,
            'applicants' => $applicants,
            'locked' => $assessment->attempts()->exists(),
        ]);
    }

    public function edit(Assessment $assessment): View|RedirectResponse
    {
        if ($assessment->isLocked()) {
            return redirect()
                ->route('assessments.show', $assessment)
                ->with('error', 'Tes ini sudah punya hasil tes dari kandidat, jadi soal tidak bisa diubah lagi.');
        }

        $assessment->load('questions');

        $existing = $assessment->questions->map(function (AssessmentQuestion $question) {
            $options = $question->options ?? ['', ''];
            $normalizedOptions = array_map(function ($opt) {
                if (is_array($opt) && isset($opt['text'])) {
                    return (string) $opt['text'];
                }

                return (string) $opt;
            }, (array) $options);

            return [
                'type' => $question->type,
                'question' => $question->question,
                'image_path' => $question->image_path ?? '',
                'dimension' => $question->dimension ?? '',
                'options' => $normalizedOptions,
                'option_images' => $question->option_images ?? [],
                'correct_option' => $question->correct_option,
                'points' => $question->points,
            ];
        })->values()->all();

        return view('recruitment.assessments.form', [
            'assessment' => $assessment,
            'initialQuestions' => old('questions_json')
                ? (json_decode((string) old('questions_json'), true) ?: [])
                : $existing,
            'bankItems' => $this->bankItems(),
        ]);
    }

    private function bankItems(): array
    {
        return QuestionBank::query()
            ->orderBy('dimension')
            ->orderBy('id')
            ->get()
            ->map(fn (QuestionBank $item) => $item->toArrayForBuilder())
            ->all();
    }

    public function update(Request $request, Assessment $assessment): RedirectResponse
    {
        if ($assessment->isLocked()) {
            return redirect()
                ->route('assessments.show', $assessment)
                ->with('error', 'Tes ini sudah punya hasil tes dari kandidat, jadi soal tidak bisa diubah lagi.');
        }

        $data = $this->validateForm($request);
        $questions = $this->normalizeQuestions($request);

        DB::transaction(function () use ($assessment, $data, $questions) {
            $assessment->update($data);
            $assessment->questions()->delete();
            $this->replaceQuestions($assessment, $questions);
        });

        return redirect()
            ->route('assessments.show', $assessment)
            ->with('status', 'Perubahan tes "' . $assessment->title . '" disimpan.');
    }

    public function destroy(Assessment $assessment): RedirectResponse
    {
        if ($assessment->isLocked()) {
            return redirect()
                ->route('assessments.show', $assessment)
                ->with('error', 'Tes ini sudah punya hasil tes dari kandidat, jadi tidak bisa dihapus.');
        }

        $title = $assessment->title;
        $assessment->delete();

        return redirect()
            ->route('assessments.index')
            ->with('status', 'Tes "' . $title . '" dihapus.');
    }

    public function invite(Request $request, Assessment $assessment): RedirectResponse
    {
        $data = $request->validate([
            'applications' => ['required', 'array', 'min:1'],
            'applications.*' => ['integer', 'distinct', Rule::exists('applications', 'id')],
            'expires_at' => ['required', 'date', 'after:now'],
            'duration_minutes' => ['nullable', 'integer', 'min:5', 'max:240'],
        ], [
            'applications.required' => 'Pilih minimal satu kandidat.',
            'applications.min' => 'Pilih minimal satu kandidat.',
            'applications.*.distinct' => 'Ada kandidat yang dipilih ganda.',
            'applications.*.exists' => 'Kandidat tidak ditemukan.',
            'expires_at.required' => 'Batas waktu undangan wajib diisi.',
            'expires_at.after' => 'Batas waktu harus di waktu yang akan datang.',
            'duration_minutes.min' => 'Durasi minimal 5 menit.',
            'duration_minutes.max' => 'Durasi maksimal 240 menit.',
        ], [
            'applications' => 'kandidat',
            'expires_at' => 'batas waktu',
            'duration_minutes' => 'durasi',
        ]);

        $applications = Application::with('candidate')
            ->whereKey($data['applications'])
            ->whereDoesntHave('assessmentInvitations', fn ($query) => $query->where('assessment_id', $assessment->id))
            ->get();

        if ($applications->isEmpty()) {
            return back()->with('error', 'Kandidat yang dipilih sudah pernah diundang ke tes ini.');
        }

        foreach ($applications as $application) {
            $assessment->invitations()->create([
                'application_id' => $application->id,
                'token' => Str::random(48),
                'duration_minutes' => $data['duration_minutes'] ?? null,
                'expires_at' => $data['expires_at'],
                'created_by' => $request->user()?->id,
            ]);

            if ($application->status !== Application::STATUS_HIRED) {
                $application->update(['status' => Application::STATUS_ASSESSMENT_SENT]);
            }
        }

        return back()->with('status', $applications->count() . ' undangan dibuat. Setiap kandidat punya link pribadi yang hanya bisa dipakai sekali.');
    }

    public function renewInvitation(Request $request, Assessment $assessment, AssessmentInvitation $invitation): RedirectResponse
    {
        abort_unless($invitation->assessment_id === $assessment->id, 404);

        if ($invitation->attempt?->isInProgress()) {
            return back()->with('error', 'Kandidat sedang mengerjakan tes, jadi undangan tidak bisa diperbarui.');
        }

        $request->session()->flash('renew_invitation', $invitation->id);

        $data = $request->validate([
            'expires_at' => ['required', 'date', 'after:now'],
        ], [
            'expires_at.required' => 'Batas waktu undangan wajib diisi.',
            'expires_at.after' => 'Batas waktu harus di waktu yang akan datang.',
        ], ['expires_at' => 'batas waktu']);

        $invitation->update([
            'token' => Str::random(48),
            'expires_at' => $data['expires_at'],
            'revoked_at' => null,
        ]);

        $request->session()->forget('renew_invitation');

        return back()->with('status', 'Undangan untuk ' . $invitation->candidateName() . ' diperbarui. Link lama tidak berlaku lagi, kirim link yang baru.');
    }

    public function revokeInvitation(Assessment $assessment, AssessmentInvitation $invitation): RedirectResponse
    {
        abort_unless($invitation->assessment_id === $assessment->id, 404);

        $invitation->update(['revoked_at' => now()]);

        return back()->with('status', 'Undangan untuk ' . $invitation->candidateName() . ' dicabut. Linknya tidak bisa dipakai lagi.');
    }

    public function destroyInvitation(Assessment $assessment, AssessmentInvitation $invitation): RedirectResponse
    {
        abort_unless($invitation->assessment_id === $assessment->id, 404);

        if ($invitation->attempt !== null) {
            return back()->with('error', 'Undangan ini sudah punya hasil tes, jadi tidak bisa dihapus. Gunakan Cabut untuk menghentikan aksesnya.');
        }

        $name = $invitation->candidateName();
        $invitation->delete();

        return back()->with('status', 'Undangan untuk ' . $name . ' dihapus.');
    }

    private function validateForm(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'instructions' => ['nullable', 'string', 'max:3000'],
            'duration_minutes' => ['required', 'integer', 'min:5', 'max:240'],
            'passing_score' => ['required', 'integer', 'min:0', 'max:100'],
            'status' => ['required', Rule::in([Assessment::STATUS_DRAFT, Assessment::STATUS_PUBLISHED, Assessment::STATUS_CLOSED])],
        ], [
            'title.required' => 'Nama tes wajib diisi.',
            'duration_minutes.required' => 'Durasi tes wajib diisi.',
            'duration_minutes.min' => 'Durasi minimal 5 menit.',
            'duration_minutes.max' => 'Durasi maksimal 240 menit.',
            'passing_score.required' => 'Nilai kelulusan wajib diisi.',
            'status.required' => 'Status tes wajib dipilih.',
        ], [
            'title' => 'nama tes',
            'duration_minutes' => 'durasi',
            'passing_score' => 'nilai kelulusan',
            'status' => 'status',
        ]);
    }

    private function normalizeQuestions(Request $request): array
    {
        $raw = json_decode((string) $request->input('questions_json', '[]'), true);

        if (! is_array($raw) || $raw === []) {
            throw ValidationException::withMessages([
                'questions_json' => 'Tambahkan minimal satu soal sebelum menyimpan.',
            ]);
        }

        return QuestionNormalizer::normalizeList($raw);
    }

    private function replaceQuestions(Assessment $assessment, array $questions): void
    {
        $assessment->questions()->createMany($questions);
    }
}
