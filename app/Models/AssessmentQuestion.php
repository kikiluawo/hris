<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentQuestion extends Model
{
    public const TYPE_MULTIPLE_CHOICE = 'multiple_choice';

    public const TYPE_ESSAY = 'essay';

    public const TYPE_SCALE = 'scale';

    public const TYPE_DISC = 'disc';

    protected $fillable = [
        'assessment_id',
        'type',
        'question',
        'image_path',
        'dimension',
        'options',
        'option_images',
        'correct_option',
        'points',
        'position',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'option_images' => 'array',
            'correct_option' => 'integer',
            'points' => 'integer',
            'position' => 'integer',
        ];
    }

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    public static function typeOptions(): array
    {
        return [
            self::TYPE_MULTIPLE_CHOICE => 'Pilihan ganda',
            self::TYPE_ESSAY => 'Esai',
            self::TYPE_SCALE => 'Skala / psikotes',
            self::TYPE_DISC => 'DISC (Kepribadian & Adaptasi)',
        ];
    }

    public function isMultipleChoice(): bool
    {
        return $this->type === self::TYPE_MULTIPLE_CHOICE;
    }

    public function isEssay(): bool
    {
        return $this->type === self::TYPE_ESSAY;
    }

    public function isScale(): bool
    {
        return $this->type === self::TYPE_SCALE;
    }

    public function isDisc(): bool
    {
        return $this->type === self::TYPE_DISC;
    }

    public function correctAnswer(): ?string
    {
        if (! $this->isMultipleChoice() || $this->correct_option === null) {
            return null;
        }

        return $this->options[$this->correct_option] ?? null;
    }

    public function dimensionLabel(): string
    {
        return filled($this->dimension) ? trim((string) $this->dimension) : 'Umum';
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

    public function typeLabel(): string
    {
        return match ($this->type) {
            self::TYPE_ESSAY => 'Esai',
            self::TYPE_SCALE => 'Skala',
            self::TYPE_DISC => 'DISC',
            default => 'Pilihan ganda',
        };
    }
}
