<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ConfirmSecurityActionRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class SecurityConfirmationController extends Controller
{
    public function create(): View
    {
        return view(
            'admin.security.confirmation.create'
        );
    }

    public function store(
        ConfirmSecurityActionRequest $request
    ): RedirectResponse {
        if (
            !Hash::check(
                $request->validated(
                    'password'
                ),
                $request->user()->password
            )
        ) {
            throw ValidationException::withMessages([
                'password' =>
                    'The password is incorrect.',
            ]);
        }

        $request->session()->put(
            'security_confirmed_at',
            now()->timestamp
        );

        $intended =
            $request->session()->pull(
                'security_confirmation_intended_url',
                route('security.index')
            );

        return redirect()->to(
            $intended
        );
    }
}