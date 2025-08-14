<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Quiz extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'category_id',
        'description',
        'time_limit',
        'status',
        'passing_score',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'time_limit' => 'integer',
        'passing_score' => 'integer',
        'status' => 'string',
    ];

    /**
     * Get the category that owns the quiz.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the questions for the quiz.
     */
    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    /**
     * Get the results for the quiz.
     */
    public function results(): HasMany
    {
        return $this->hasMany(Result::class);
    }

    /**
     * Get the latest result for the quiz.
     */
    public function latestResult(): HasOne
    {
        return $this->hasOne(Result::class)->latestOfMany();
    }

    /**
     * Get all of the options for the quiz through questions.
     */
    public function options(): HasManyThrough
    {
        return $this->hasManyThrough(Option::class, Question::class);
    }

    /**
     * Scope a query to only include published quizzes.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Get the total points available for this quiz.
     */
    public function getTotalPointsAttribute(): int
    {
        return $this->questions->sum('points');
    }

    /**
     * Get the total number of questions in this quiz.
     */
    public function getQuestionCountAttribute(): int
    {
        return $this->questions()->count();
    }
}
