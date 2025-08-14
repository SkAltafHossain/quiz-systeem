<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Builder;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
        'avatar',
        'bio',
    ];

    protected $attributes = [
        'role' => 'user',
    ];

    /**
     * Check if the user is an admin
     *
     * @return bool
     */
    /**
     * Check if the user is an admin
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        // Debug logging
        \Log::info('Checking admin status for user:', [
            'user_id' => $this->id,
            'email' => $this->email,
            'is_admin' => $this->is_admin,
            'is_admin_type' => gettype($this->is_admin)
        ]);
        
        return $this->is_admin === true || $this->is_admin === 1 || $this->is_admin === '1';
    }

    /**
     * Check if the user is a regular user
     *
     * @return bool
     */
    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'avatar_url',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    /**
     * Get the results for the user.
     */
    public function results(): HasMany
    {
        return $this->hasMany(Result::class);
    }

    /**
     * Get the user's latest result.
     */
    public function latestResult()
    {
        return $this->hasOne(Result::class)->latestOfMany();
    }

    /**
     * Get the user's average score across all quizzes.
     */
    public function getAverageScoreAttribute(): float
    {
        return $this->results()->avg('score') ?? 0;
    }

    /**
     * Get the total number of quizzes taken by the user.
     */
    public function getQuizzesTakenAttribute(): int
    {
        return $this->results()->count();
    }

    /**
     * Get the URL to the user's profile picture.
     */
    public function getAvatarUrlAttribute(): string
    {
        return $this->avatar
            ? asset('storage/' . $this->avatar)
            : 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($this->email))) . '?d=mp&s=200';
    }

    /**
     * Scope a query to only include admin users.
     */
    public function scopeAdmins(Builder $query): Builder
    {
        return $query->where('role', 'admin');
    }

    /**
     * Scope a query to only include regular users.
     */
    public function scopeRegularUsers(Builder $query): Builder
    {
        return $query->where('role', 'user');
    }
}