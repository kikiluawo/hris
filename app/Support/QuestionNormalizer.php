<?php

namespace App\Support;

use App\Models\AssessmentQuestion;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class QuestionNormalizer
{
    public static function normalizeList(array $raw, string $errorKey = 'questions_json'): array
    {
        $validated = Validator::make(
            ['questions' => $raw],
            self::listRules(),
            self::messages(),
        )->validate();

        $normalized = [];

        foreach ($validated['questions'] as $index => $question) {
            $normalized[] = [
                ...self::normalize($question, $index + 1, $errorKey),
                'position' => $index,
            ];
        }

        return $normalized;
    }

    public static function normalize(array $question, int $number = 1, string $errorKey = 'questions_json'): array
    {
        $type = $question['type'] ?? AssessmentQuestion::TYPE_MULTIPLE_CHOICE;
        $text = trim((string) ($question['question'] ?? ''));

        if ($text === '') {
            throw ValidationException::withMessages([
                $errorKey => 'Teks soal nomor ' . $number . ' masih kosong.',
            ]);
        }

        $options = null;
        $correct = null;
        $dimension = null;
        $optionImages = null;
        $image = AssessmentImageStore::sanitize($question['image_path'] ?? null);

        if (! $image && filled($question['image_path'] ?? null)) {
            throw ValidationException::withMessages([
                $errorKey => 'Gambar soal nomor ' . $number . ' tidak dikenali. Unggah ulang gambarnya.',
            ]);
        }

        if ($type === AssessmentQuestion::TYPE_MULTIPLE_CHOICE || $type === AssessmentQuestion::TYPE_SCALE || $type === AssessmentQuestion::TYPE_DISC) {
            $label = match ($type) {
                AssessmentQuestion::TYPE_SCALE => 'skala',
                AssessmentQuestion::TYPE_DISC => 'DISC',
                default => 'pilihan ganda',
            };

            $options = array_values(array_map(function ($option) {
                if (is_array($option) && isset($option['text'])) {
                    return trim((string) $option['text']);
                }

                return trim((string) $option);
            }, $question['options'] ?? []));
            $optionImages = AssessmentImageStore::sanitizeList($question['option_images'] ?? [], count($options));

            foreach ((array) ($question['option_images'] ?? []) as $index => $name) {
                if (filled($name) && ($optionImages[$index] ?? null) === null) {
                    throw ValidationException::withMessages([
                        $errorKey => 'Gambar pilihan soal '.$label.' nomor ' . $number . ' tidak dikenali. Unggah ulang gambarnya.',
                    ]);
                }
            }

            if (count($options) < 2) {
                throw ValidationException::withMessages([
                    $errorKey => 'Soal '.$label.' nomor '.$number.' butuh minimal dua pilihan.',
                ]);
            }

            $blank = [];

            foreach ($options as $index => $option) {
                if ($option === '' && $optionImages[$index] === null) {
                    $blank[] = chr(65 + $index);
                }
            }

            if ($blank !== []) {
                throw ValidationException::withMessages([
                    $errorKey => 'Pilihan '.implode(', ', $blank).' pada soal '.$label.' nomor '.$number.' belum ada teks atau gambarnya.',
                ]);
            }
        }

        if ($type === AssessmentQuestion::TYPE_MULTIPLE_CHOICE) {
            $correct = $question['correct_option'] ?? null;

            if ($correct === null || ! array_key_exists($correct, $options)) {
                throw ValidationException::withMessages([
                    $errorKey => 'Tandai jawaban benar untuk soal pilihan ganda nomor ' . $number . '.',
                ]);
            }
        }

        if ($type === AssessmentQuestion::TYPE_SCALE || $type === AssessmentQuestion::TYPE_DISC) {
            $dimension = trim((string) ($question['dimension'] ?? ''));
            $dimension = $dimension === '' ? ($type === AssessmentQuestion::TYPE_DISC ? 'Kepribadian DISC' : null) : $dimension;
        }

        return [
            'type' => $type,
            'question' => $text,
            'image_path' => $image,
            'dimension' => $dimension,
            'options' => $options,
            'option_images' => $optionImages,
            'correct_option' => $correct,
            'points' => $type === AssessmentQuestion::TYPE_DISC ? 0 : (int) ($question['points'] ?? 1),
        ];
    }

    private static function listRules(): array
    {
        return [
            'questions' => ['required', 'array', 'min:1', 'max:300'],
            'questions.*.type' => ['required', Rule::in(array_keys(AssessmentQuestion::typeOptions()))],
            'questions.*.question' => ['required', 'string', 'max:2000'],
            'questions.*.dimension' => ['nullable', 'string', 'max:50'],
            'questions.*.points' => ['required', 'integer', 'min:0', 'max:100'],
            'questions.*.options' => ['nullable', 'array', 'max:10'],
            'questions.*.options.*' => ['nullable', 'string', 'max:500'],
            'questions.*.option_images' => ['nullable', 'array', 'max:10'],
            'questions.*.option_images.*' => ['nullable', 'string', 'max:80'],
            'questions.*.image_path' => ['nullable', 'string', 'max:80'],
            'questions.*.correct_option' => ['nullable', 'integer', 'min:0', 'max:9'],
        ];
    }

    private static function messages(): array
    {
        return [
            'questions.*.question.required' => 'Teks soal wajib diisi.',
            'questions.*.points.required' => 'Poin soal wajib diisi.',
        ];
    }
}
