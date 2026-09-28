<?php

namespace App\Models;

use App\Enums\ModerationAction;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable(['action', 'reason'])]
class ModerationLog extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'action' => ModerationAction::class,
        ];
    }

    /**
     * Write down who did what, to which report or user, and why.
     */
    public static function record(User $moderator, Model $target, ModerationAction $action, string $reason): self
    {
        $log = new self(['action' => $action, 'reason' => $reason]);
        $log->moderator()->associate($moderator);
        $log->target()->associate($target);
        $log->save();

        return $log;
    }

    public function moderator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderator_id');
    }

    /**
     * The moderated item: a Report or a User.
     */
    public function target(): MorphTo
    {
        return $this->morphTo();
    }
}
