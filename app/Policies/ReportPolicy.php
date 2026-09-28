<?php

namespace App\Policies;

use App\Models\User;

class ReportPolicy
{
    /**
     * Banned users cannot create reports.
     */
    public function create(User $user): bool
    {
        return ! $user->isBanned();
    }
}
