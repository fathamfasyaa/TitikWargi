<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ModerationAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\BanUserRequest;
use App\Http\Requests\ModerationRequest;
use App\Models\ModerationLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

/**
 * Moderation actions on a user. Each action is written to moderation_logs.
 * Admins cannot be warned or banned (see the "moderate-user" gate).
 */
class UserModerationController extends Controller
{
    /**
     * Give a warning: add one strike.
     */
    public function warn(ModerationRequest $request, User $user): RedirectResponse
    {
        Gate::authorize('moderate-user', $user);

        DB::transaction(function () use ($request, $user) {
            $user->increment('strikes');

            ModerationLog::record($request->user(), $user, ModerationAction::WarnUser, $request->validated('reason'));
        });

        return back()->with('status', "{$user->name} diberi peringatan (total {$user->strikes}).");
    }

    /**
     * Ban the user for 7 days, 30 days or permanently.
     */
    public function ban(BanUserRequest $request, User $user): RedirectResponse
    {
        Gate::authorize('moderate-user', $user);

        $duration = $request->validated('duration');
        $bannedUntil = $duration === 'permanent'
            ? User::PERMANENT_BAN_UNTIL
            : now()->addDays((int) $duration);

        DB::transaction(function () use ($request, $user, $bannedUntil, $duration) {
            $user->forceFill(['banned_until' => $bannedUntil])->save();

            $durationText = $duration === 'permanent' ? 'permanen' : "{$duration} hari";
            ModerationLog::record(
                $request->user(),
                $user,
                ModerationAction::BanUser,
                "[{$durationText}] ".$request->validated('reason'),
            );
        });

        return back()->with('status', "{$user->name} diblokir.");
    }

    /**
     * Lift the ban before it ends.
     */
    public function unban(ModerationRequest $request, User $user): RedirectResponse
    {
        Gate::authorize('moderate-user', $user);

        DB::transaction(function () use ($request, $user) {
            $user->forceFill(['banned_until' => null])->save();

            ModerationLog::record($request->user(), $user, ModerationAction::UnbanUser, $request->validated('reason'));
        });

        return back()->with('status', "Blokir {$user->name} dibuka.");
    }
}
