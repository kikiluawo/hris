<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class QuestionBank extends Model
{
    protected $fillable = [
        'type',
        'question',
        'image_path',
        'dimension',
        'options',
        'option_images',
        'correct_option',
        'points',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'option_images' => 'array',
            'correct_option' => 'integer',
            'points' => 'integer',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $term).'%';

        return $query->where(function (Builder $inner) use ($like) {
            $inner->where('question', 'like', $like)
                ->orWhere('dimension', 'like', $like);
        });
    }

    public function scopeOfType(Builder $query, ?string $type): Builder
    {
        if (! array_key_exists((string) $type, AssessmentQuestion::typeOptions())) {
            return $query;
        }

        return $query->where('type', $type);
    }

    public function scopeOfDimension(Builder $query, ?string $dimension): Builder
    {
        $dimension = trim((string) $dimension);

        if ($dimension === '') {
            return $query;
        }

        if ($dimension === '*') {
            return $query->whereNull('dimension');
        }

        if ($dimension === 'Kepribadian DISC' || $dimension === 'DISC') {
            return $query->where('type', AssessmentQuestion::TYPE_DISC);
        }

        return $query->where('dimension', $dimension);
    }

    public function isMultipleChoice(): bool
    {
        return $this->type === AssessmentQuestion::TYPE_MULTIPLE_CHOICE;
    }

    public function isScale(): bool
    {
        return $this->type === AssessmentQuestion::TYPE_SCALE;
    }

    public function isDisc(): bool
    {
        return $this->type === AssessmentQuestion::TYPE_DISC;
    }

    public function typeLabel(): string
    {
        return AssessmentQuestion::typeOptions()[$this->type] ?? Str::headline((string) $this->type);
    }

    public function dimensionLabel(): string
    {
        if ($this->type === AssessmentQuestion::TYPE_DISC) {
            return 'Kepribadian DISC';
        }

        return filled($this->dimension) ? trim((string) $this->dimension) : 'Umum';
    }

    public function correctAnswer(): ?string
    {
        if (! $this->isMultipleChoice() || $this->correct_option === null) {
            return null;
        }

        return $this->options[$this->correct_option] ?? null;
    }

    public static function dimensionList(): array
    {
        $list = static::query()
            ->whereNotNull('dimension')
            ->where('type', '!=', AssessmentQuestion::TYPE_DISC)
            ->distinct()
            ->orderBy('dimension')
            ->pluck('dimension')
            ->all();

        if (static::query()->where('type', AssessmentQuestion::TYPE_DISC)->exists()) {
            $list[] = 'Kepribadian DISC';
        }

        sort($list);

        return array_values(array_unique($list));
    }

    public function hasImage(): bool
    {
        return filled($this->image_path);
    }

    public function optionImage(?int $index): ?string
    {
        if ($index === null || ! is_array($this->option_images)) {
            return null;
        }

        $name = $this->option_images[$index] ?? null;

        return is_string($name) && $name !== '' ? $name : null;
    }

    public function usesImages(): bool
    {
        if ($this->hasImage()) {
            return true;
        }

        foreach (array_keys($this->options ?? []) as $index) {
            if ($this->optionImage($index) !== null) {
                return true;
            }
        }

        return false;
    }

    public function toArrayForBuilder(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'question' => $this->question,
            'image_path' => $this->image_path ?? '',
            'dimension' => $this->dimension ?? '',
            'options' => $this->options ?? ['', ''],
            'option_images' => $this->option_images ?? [],
            'correct_option' => $this->correct_option,
            'points' => $this->points,
        ];
    }
}
