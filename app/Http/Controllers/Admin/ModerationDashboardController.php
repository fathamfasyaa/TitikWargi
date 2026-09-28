<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\BanUserRequest;
use App\Models\ModerationLog;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\View\View;

class ModerationDashboardController extends Controller
{
    /**
     * The moderation page: flagged reports, hidden reports, banned users and recent actions.
     */
    public function __invoke(): View
    {
        // Used both in whereHas (a Builder) and in with() (a HasMany relation).
        $openFlags = fn (Builder|HasMany $query) => $query->whereNull('resolved_at');

        // Reports with open flags, most flagged first.
        $flaggedReports = Report::query()
            ->whereHas('flags', $openFlags)
            ->withCount(['flags as open_flags_count' => $openFlags])
            ->with([
                'user',
                'photos',
                'flags' => fn ($query) => $openFlags($query)->with('user')->latest(),
            ])
            ->orderByDesc('open_flags_count')
            ->limit(50)
            ->get();

        $hiddenReports = Report::query()
            ->whereNotNull('hidden_at')
            ->whereDoesntHave('flags', $openFlags)
            ->with('user')
            ->latest('hidden_at')
            ->limit(50)
            ->get();

        $bannedUsers = User::query()
            ->where('banned_until', '>', now())
            ->orderBy('banned_until')
            ->limit(50)
            ->get();

        $recentLogs = ModerationLog::query()
            ->with(['moderator', 'target'])
            ->latest()
            ->limit(30)
            ->get();

        return view('admin.moderation', [
            'flaggedReports' => $flaggedReports,
            'hiddenReports' => $hiddenReports,
            'bannedUsers' => $bannedUsers,
            'recentLogs' => $recentLogs,
            'banDurations' => BanUserRequest::DURATIONS,
        ]);
    }
}
