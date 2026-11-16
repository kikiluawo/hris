<?php

namespace App\Http\Controllers;

use App\Models\AssessmentQuestion;
use App\Models\QuestionBank;
use App\Services\QuestionBankSyncService;
use App\Support\QuestionNormalizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class QuestionBankController extends Controller
{
    private const LIKERT = [
        'Sangat tidak sesuai',
        'Tidak sesuai',
        'Cukup sesuai',
        'Sesuai',
        'Sangat sesuai',
    ];

    public function index(Request $request): View
    {
        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'type' => (string) $request->query('type', ''),
            'dimension' => (string) $request->query('dimension', ''),
        ];

        $items = QuestionBank::query()
            ->with('creator')
            ->search($filters['q'])
            ->ofType($filters['type'])
            ->ofDimension($filters['dimension'])
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('recruitment.question-bank.index', [
            'items' => $items,
            'filters' => $filters,
            'dimensions' => QuestionBank::dimensionList(),
            'stats' => [
                'total' => QuestionBank::count(),
                'multiple_choice' => QuestionBank::ofType(AssessmentQuestion::TYPE_MULTIPLE_CHOICE)->count(),
                'disc' => QuestionBank::ofType(AssessmentQuestion::TYPE_DISC)->count(),
                'dimensions' => count(QuestionBank::dimensionList()),
            ],
        ]);
    }

    public function create(): View
    {
        return view('recruitment.question-bank.form', [
            'item' => null,
            'fields' => [
                'type' => old('type', AssessmentQuestion::TYPE_SCALE),
                'question' => old('question', ''),
                'dimension' => old('dimension', ''),
                'points' => old('points', 1),
                'options' => old('options', self::LIKERT),
                'option_images' => old('option_images', []),
                'image_path' => old('image_path', ''),
                'correct_option' => old('correct_option'),
            ],
            'dimensions' => QuestionBank::dimensionList(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $normalized = QuestionNormalizer::normalize($this->validateItem($request), 1, 'question');

        $this->guardDuplicate($normalized['question']);

        QuestionBank::create([
            ...$normalized,
            'created_by' => $request->user()?->id,
        ]);

        return redirect()
            ->route('question-bank.index')
            ->with('status', 'Soal ditambahkan ke bank soal.');
    }

    public function edit(QuestionBank $questionBank): View
    {
        return view('recruitment.question-bank.form', [
            'item' => $questionBank,
            'fields' => $questionBank->only('type', 'question', 'dimension', 'points', 'correct_option')
                + [
                    'options' => $questionBank->options ?? ['', ''],
                    'option_images' => $questionBank->option_images ?? [],
                    'image_path' => $questionBank->image_path ?? '',
                ],
            'dimensions' => QuestionBank::dimensionList(),
        ]);
    }

    public function update(Request $request, QuestionBank $questionBank): RedirectResponse
    {
        $normalized = QuestionNormalizer::normalize($this->validateItem($request), 1, 'question');

        $this->guardDuplicate($normalized['question'], $questionBank->id);

        $questionBank->update($normalized);

        return redirect()
            ->route('question-bank.index')
            ->with('status', 'Soal di bank soal diperbarui.');
    }

    public function destroy(QuestionBank $questionBank): RedirectResponse
    {
        $questionBank->delete();

        return back()->with('status', 'Soal dihapus dari bank soal.');
    }

    public function import(Request $request): RedirectResponse
    {
        $raw = json_decode((string) $request->input('questions_json', '[]'), true);

        if (! is_array($raw) || $raw === []) {
            return back()
                ->with('error', 'Belum ada soal yang bisa disimpan ke bank.')
                ->withInput(['questions_json' => $request->input('questions_json', '[]')]);
        }

        $added = 0;
        $skipped = 0;

        foreach (QuestionNormalizer::normalizeList($raw) as $question) {
            unset($question['position']);

            if ($this->exists($question['question'])) {
                $skipped++;

                continue;
            }

            QuestionBank::create([
                ...$question,
                'created_by' => $request->user()?->id,
            ]);

            $added++;
        }

        $message = $added === 0
            ? 'Tidak ada soal baru — semua sudah ada di bank soal.'
            : $added.' soal ditambahkan ke bank soal'.($skipped > 0 ? ', '.$skipped.' sudah ada sebelumnya dan dilewati' : '').'.';

        return back()
            ->with('status', $message)
            ->withInput(['questions_json' => json_encode($raw)]);
    }

    public function syncStandard(Request $request): RedirectResponse
    {
        $result = QuestionBankSyncService::syncStandardQuestions($request->user());

        $message = $result['added'] > 0
            ? "{$result['added']} butir soal standar (DISC, IQ & Ketelitian) berhasil disinkronkan ke Bank Soal."
            : 'Semua 94 butir soal standar (24 DISC, 30 IQ & 40 Ketelitian) sudah ada di Bank Soal.';

        return redirect()
            ->route('question-bank.index')
            ->with('status', $message);
    }

    private function validateItem(Request $request): array
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(array_keys(AssessmentQuestion::typeOptions()))],
            'question' => ['required', 'string', 'max:2000'],
            'dimension' => ['nullable', 'string', 'max:50'],
            'points' => ['required', 'integer', 'min:0', 'max:100'],
            'options' => ['nullable', 'array', 'max:10'],
            'options.*' => ['nullable', 'string', 'max:500'],
            'option_images' => ['nullable', 'array', 'max:10'],
            'option_images.*' => ['nullable', 'string', 'max:80'],
            'image_path' => ['nullable', 'string', 'max:80'],
            'correct_option' => ['nullable', 'integer', 'min:0', 'max:9'],
        ], [
            'question.required' => 'Teks soal wajib diisi.',
            'points.required' => 'Poin soal wajib diisi.',
        ], [
            'type' => 'jenis soal',
            'question' => 'teks soal',
            'points' => 'poin',
        ]);

        return [
            'type' => $data['type'],
            'question' => $data['question'],
            'dimension' => $data['dimension'] ?? null,
            'points' => $data['points'],
            'options' => $data['options'] ?? [],
            'option_images' => $data['option_images'] ?? [],
            'image_path' => $data['image_path'] ?? null,
            'correct_option' => ($data['correct_option'] ?? null) === null || $data['correct_option'] === ''
                ? null
                : (int) $data['correct_option'],
        ];
    }

    private function exists(string $question, ?int $except = null): bool
    {
        return QuestionBank::query()
            ->where('question', trim($question))
            ->when($except, fn ($query) => $query->whereKeyNot($except))
            ->exists();
    }

    private function guardDuplicate(string $question, ?int $except = null): void
    {
        if ($this->exists($question, $except)) {
            throw ValidationException::withMessages([
                'question' => 'Soal dengan teks seperti itu sudah ada di bank soal.',
            ]);
        }
    }
}
