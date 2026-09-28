<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Only admins can open the moderation pages.
        Gate::define('moderate', fn (User $user) => $user->isAdmin());

        // Admins can warn or ban regular users, but not other admins (or themselves).
        Gate::define('moderate-user', fn (User $user, User $target) => $user->isAdmin() && ! $target->isAdmin());
    }
}
