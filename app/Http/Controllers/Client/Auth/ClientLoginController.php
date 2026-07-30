<?php

namespace App\Http\Controllers\Client\Auth;

use App\Enums\ClientUserStatus;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientLoginController extends Controller
{
    public function create(): View
    {
        return view(
            'client.auth.login'
        );
    }

    public function store(
        Request $request
    ): RedirectResponse {
        $credentials =
            $request->validate([
                'email' => [
                    'required',
                    'email',
                ],

                'password' => [
                    'required',
                    'string',
                ],
            ]);

        $remember =
            $request->boolean('remember');

        if (
            !Auth::guard('client')->attempt(
                $credentials,
                $remember
            )
        ) {
            return back()
                ->withErrors([
                    'email' =>
                        'The provided client portal credentials are incorrect.',
                ])
                ->onlyInput('email');
        }

        $request->session()
            ->regenerate();

        $clientUser =
            Auth::guard('client')->user();

        if (
            $clientUser->status !==
            ClientUserStatus::Active
        ) {
            Auth::guard('client')->logout();

            return back()
                ->withErrors([
                    'email' =>
                        'Your client portal account is not active.',
                ])
                ->onlyInput('email');
        }

        $clientUser->forceFill([
            'last_login_at' => now(),

            'last_login_ip' =>
                $request->ip(),
        ])->saveQuietly();

        return redirect()
            ->intended(
                route(
                    'client.dashboard'
                )
            );
    }

    public function destroy(
        Request $request
    ): RedirectResponse {
        Auth::guard('client')->logout();

        $request->session()
            ->invalidate();

        $request->session()
            ->regenerateToken();

        return redirect()
            ->route('client.login')
            ->with(
                'success',
                'You have been logged out.'
            );
    }
}