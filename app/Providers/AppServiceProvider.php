<?php

namespace App\Providers;

use App\Models\Project;
use App\Models\User;
use App\Observers\ProjectObserver;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Super Administrator Authorization
        |--------------------------------------------------------------------------
        */

        Gate::before(
            function (User $user, string $ability): ?bool {
                return $user->hasRole('super-admin')
                    ? true
                    : null;
            }
        );

        /*
        |--------------------------------------------------------------------------
        | Project Observer
        |--------------------------------------------------------------------------
        */

        Project::observe(ProjectObserver::class);

        /*
        |--------------------------------------------------------------------------
        | Admin Layout Notification Data
        |--------------------------------------------------------------------------
        */

        View::composer(
            'layouts.admin',
            function ($view): void {
                $user = auth()->user();

                $view->with([
                    'headerUnreadNotificationCount' =>
                        $user
                            ? $user
                                ->unreadNotifications()
                                ->count()
                            : 0,

                    'headerNotifications' =>
                        $user
                            ? $user
                                ->notifications()
                                ->limit(6)
                                ->get()
                            : collect(),
                ]);
            }
        );
    }
}