<?php

namespace App\Policies;

use App\Models\Report;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ReportPolicy
{
    /**
     * Everyone, including guests, can view a report. A hidden report
     * can only be viewed by admins; others get "not found".
     */
    public function view(?User $user, Report $report): Response
    {
        if ($report->isHidden() && ! $user?->isAdmin()) {
            return Response::denyAsNotFound();
        }

        return Response::allow();
    }

    /**
     * Banned users cannot create reports, and nobody may create more than
     * the daily limit (config/titikwargi.php).
     */
    public function create(User $user): Response
    {
        if ($user->isBanned()) {
            return Response::deny('Akun Anda sedang diblokir, jadi belum bisa membuat laporan.');
        }

        $dailyLimit = config('titikwargi.daily_report_limit');

        if ($user->reportsCreatedToday() >= $dailyLimit) {
            return Response::deny("Anda sudah membuat {$dailyLimit} laporan hari ini. Silakan lapor lagi besok.");
        }

        return Response::allow();
    }

    /**
     * "Saya juga terdampak": anyone except the reporter, on a visible report.
     */
    public function support(User $user, Report $report): bool
    {
        return ! $report->isHidden() && ! $report->user()->is($user);
    }

    /**
     * "Laporkan konten ini": anyone except the reporter, once per visible report.
     */
    public function flag(User $user, Report $report): bool
    {
        return ! $report->isHidden()
            && ! $report->user()->is($user)
            && ! $report->flags()->whereBelongsTo($user)->exists();
    }
}
