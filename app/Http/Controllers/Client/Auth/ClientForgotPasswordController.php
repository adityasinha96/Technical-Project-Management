<?php

namespace App\Http\Controllers\Client\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ClientForgotPasswordController extends Controller
{
    public function create(): View
    {
        return view(
            'client.auth.forgot-password'
        );
    }

    public function store(
        Request $request
    ): RedirectResponse {
        $request->validate([
            'email' => [
                'required',
                'email',
            ],
        ]);

        $status =
            Password::broker('clients')
                ->sendResetLink(
                    $request->only('email')
                );

        return $status ===
            Password::ResetLinkSent
                ? back()->with(
                    'status',
                    __($status)
                )
                : back()->withErrors([
                    'email' =>
                        __($status),
                ]);
    }
}