<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use App\Models\Project;
use App\Observers\ProjectObserver;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::before(
            function (User $user, string $ability): ?bool {
                return $user->hasRole('super-admin')
                    ? true
                    : null;
            }
        );

        Project::observe(ProjectObserver::class);
    }
}