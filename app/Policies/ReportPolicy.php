<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class ReportPolicy
{
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
}
