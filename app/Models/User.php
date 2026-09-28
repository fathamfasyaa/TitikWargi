<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// "role", "strikes" and "banned_until" are intentionally not fillable,
// so they can only be changed on purpose (for example by a moderator).
#[Fillable(['name', 'email', 'password', 'google_id', 'avatar'])]
#[Hidden(['password', 'remember_token', 'google_id'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'strikes' => 'integer',
            'banned_until' => 'datetime',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function isBanned(): bool
    {
        return $this->banned_until !== null && $this->banned_until->isFuture();
    }

    /**
     * Number of reports this user created today (Asia/Jakarta), including deleted ones.
     */
    public function reportsCreatedToday(): int
    {
        return $this->reports()
            ->withTrashed()
            ->where('created_at', '>=', today())
            ->count();
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    public function supportedReports(): BelongsToMany
    {
        return $this->belongsToMany(Report::class, 'report_supports')->withTimestamps();
    }

    public function reportFlags(): HasMany
    {
        return $this->hasMany(ReportFlag::class);
    }

    public function moderationLogs(): HasMany
    {
        return $this->hasMany(ModerationLog::class, 'moderator_id');
    }
}
