<?php

namespace App\Services;

use App\Models\AssessmentQuestion;
use App\Models\QuestionBank;
use App\Models\User;

class QuestionBankSyncService
{
    
    public static function syncStandardQuestions(?User $user = null): array
    {
        $userId = $user?->id ?? User::first()?->id;
        $added = 0;
        $skipped = 0;

        foreach (DiscBank::questions() as $item) {
            $questionText = trim($item['question']);
            $exists = QuestionBank::query()
                ->where('type', AssessmentQuestion::TYPE_DISC)
                ->where('question', $questionText)
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            QuestionBank::create([
                'type' => AssessmentQuestion::TYPE_DISC,
                'question' => $questionText,
                'dimension' => $item['dimension'] ?? 'S,I,D,C',
                'options' => $item['options'],
                'correct_option' => null,
                'points' => 0,
                'created_by' => $userId,
            ]);

            $added++;
        }

        foreach (AptitudeBank::questions() as $item) {
            $questionText = trim($item['question']);
            $exists = QuestionBank::query()
                ->where('question', $questionText)
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            QuestionBank::create([
                'type' => AssessmentQuestion::TYPE_MULTIPLE_CHOICE,
                'question' => $questionText,
                'dimension' => $item['dimension'],
                'options' => $item['options'],
                'correct_option' => $item['correct_option'],
                'points' => 1,
                'created_by' => $userId,
            ]);

            $added++;
        }

        foreach (AccuracyBank::questions() as $item) {
            $questionText = trim($item['question']);
            $exists = QuestionBank::query()
                ->where('question', $questionText)
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            QuestionBank::create([
                'type' => AssessmentQuestion::TYPE_MULTIPLE_CHOICE,
                'question' => $questionText,
                'dimension' => $item['dimension'],
                'options' => $item['options'],
                'correct_option' => $item['correct_option'],
                'points' => 1,
                'created_by' => $userId,
            ]);

            $added++;
        }

        return [
            'added' => $added,
            'skipped' => $skipped,
            'total' => $added + $skipped,
        ];
    }
}
