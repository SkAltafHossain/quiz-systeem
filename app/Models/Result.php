<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Result extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'quiz_id',
        'score',
        'correct_answers',
        'wrong_answers',
        'total_questions',
        'time_taken',
        'answers',
        'passed',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'score' => 'integer',
        'correct_answers' => 'integer',
        'wrong_answers' => 'integer',
        'total_questions' => 'integer',
        'time_taken' => 'integer',
        'answers' => 'array',
        'passed' => 'boolean',
        'completed_at' => 'datetime',
    ];

    /**
     * The user that owns the result.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The quiz that owns the result.
     */
    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    /**
     * Scope a query to only include results for a specific user.
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to only include results for a specific quiz.
     */
    public function scopeForQuiz(Builder $query, int $quizId): Builder
    {
        return $query->where('quiz_id', $quizId);
    }

    /**
     * Scope a query to only include passed results.
     */
    public function scopePassed(Builder $query): Builder
    {
        return $query->where('passed', true);
    }

    /**
     * Scope a query to only include failed results.
     */
    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('passed', false);
    }

    /**
     * Get the percentage score.
     */
    public function getPercentageAttribute(): float
    {
        return round(($this->score / max(1, $this->total_questions)) * 100, 2);
    }

    /**
     * Get the time taken in minutes:seconds format.
     */
    public function getTimeTakenFormattedAttribute(): string
    {
        $minutes = floor($this->time_taken / 60);
        $seconds = $this->time_taken % 60;
        return sprintf('%02d:%02d', $minutes, $seconds);
    }

    /**
     * Check if the result is a passing score.
     */
    public function isPassingScore(Quiz $quiz): bool
    {
        return $this->percentage >= $quiz->passing_score;
    }
}
