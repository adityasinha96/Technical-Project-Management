<?php

namespace App\Http\Controllers\Client\Auth;

use App\Http\Controllers\Controller;
use App\Models\ClientUser;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;

class ClientResetPasswordController extends Controller
{
    public function create(
        Request $request,
        string $token
    ): View {
        return view(
            'client.auth.reset-password',
            [
                'token' => $token,
                'email' =>
                    $request->query('email'),
            ]
        );
    }

    public function store(
        Request $request
    ): RedirectResponse {
        $request->validate([
            'token' => [
                'required',
            ],

            'email' => [
                'required',
                'email',
            ],

            'password' => [
                'required',
                'confirmed',

                PasswordRule::min(10)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
        ]);

        $status =
            Password::broker('clients')
                ->reset(
                    $request->only(
                        'email',
                        'password',
                        'password_confirmation',
                        'token'
                    ),
                    function (
                        ClientUser $clientUser,
                        string $password
                    ): void {
                        $clientUser->forceFill([
                            'password' =>
                                $password,

                            'remember_token' =>
                                Str::random(60),
                        ])->save();

                        event(
                            new PasswordReset(
                                $clientUser
                            )
                        );
                    }
                );

        return $status ===
            Password::PasswordReset
                ? redirect()
                    ->route(
                        'client.login'
                    )
                    ->with(
                        'status',
                        __($status)
                    )
                : back()->withErrors([
                    'email' =>
                        __($status),
                ]);
    }
}