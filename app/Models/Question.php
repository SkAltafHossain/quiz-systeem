<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Question extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'quiz_id',
        'question_text',
        'explanation',
        'order',
        'points',
        'question_type',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'order' => 'integer',
        'points' => 'integer',
        'question_type' => 'string',
    ];

    /**
     * The quiz that owns the question.
     */
    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    /**
     * Get the options for the question.
     */
    public function options(): HasMany
    {
        return $this->hasMany(Option::class);
    }

    /**
     * Get the correct options for the question.
     */
    public function correctOptions()
    {
        return $this->options()->where('is_correct', true);
    }

    /**
     * Scope a query to only include questions of a given type.
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('question_type', $type);
    }

    /**
     * Check if the question is multiple choice.
     */
    public function isMultipleChoice(): bool
    {
        return $this->question_type === 'multiple_choice';
    }

    /**
     * Check if the question is true/false.
     */
    public function isTrueFalse(): bool
    {
        return $this->question_type === 'true_false';
    }

    /**
     * Check if the question is short answer.
     */
    public function isShortAnswer(): bool
    {
        return $this->question_type === 'short_answer';
    }

    /**
     * Get the correct answer text (for short answer questions).
     */
    public function getCorrectAnswerTextAttribute(): ?string
    {
        if (!$this->isShortAnswer()) {
            return null;
        }
        
        return $this->options()->first()?->option_text;
    }
}
