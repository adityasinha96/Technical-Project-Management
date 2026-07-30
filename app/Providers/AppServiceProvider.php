<?php

namespace App\Providers;

use App\Listeners\AuthenticationEventSubscriber;
use App\Models\Project;
use App\Models\User;
use App\Observers\ProjectObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

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
        | Authentication Event Subscriber
        |--------------------------------------------------------------------------
        */

        Event::subscribe(
            AuthenticationEventSubscriber::class
        );

        /*
        |--------------------------------------------------------------------------
        | Client Login Rate Limiting
        |--------------------------------------------------------------------------
        */

        RateLimiter::for(
            'client-login',
            function (Request $request): Limit {
                return Limit::perMinute(5)
                    ->by(
                        Str::lower(
                            (string)
                            $request->input('email')
                        )
                        . '|'
                        . $request->ip()
                    );
            }
        );

        /*
        |--------------------------------------------------------------------------
        | Super Administrator Authorization
        |--------------------------------------------------------------------------
        |
        | Laravel's Gate may be evaluated for both internal staff users and
        | authenticated client portal users. Only the internal User model uses
        | Spatie roles, so client users must pass through without invoking
        | hasRole().
        |
        */

        Gate::before(
            function (
                mixed $user,
                string $ability
            ): ?bool {
                if (!$user instanceof User) {
                    return null;
                }

                return $user->hasRole(
                    'super-admin'
                )
                    ? true
                    : null;
            }
        );

        /*
        |--------------------------------------------------------------------------
        | Project Observer
        |--------------------------------------------------------------------------
        */

        Project::observe(
            ProjectObserver::class
        );

        /*
        |--------------------------------------------------------------------------
        | Admin Layout Notification Data
        |--------------------------------------------------------------------------
        */

        View::composer(
            'layouts.admin',
            function ($view): void {
                $user = auth('web')->user();

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
