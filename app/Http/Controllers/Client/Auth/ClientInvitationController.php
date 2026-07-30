<?php

namespace App\Http\Controllers\Client\Auth;

use App\Enums\ClientUserStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Client\AcceptClientInvitationRequest;
use App\Models\ClientPortalInvitation;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ClientInvitationController extends Controller
{
    public function show(
        ClientPortalInvitation $invitation,
        string $token
    ): View {
        $invitation->load([
            'clientUser',
            'project.client',
        ]);

        $this->ensureUsable(
            $invitation,
            $token
        );

        return view(
            'client.auth.accept-invitation',
            [
                'invitation' =>
                    $invitation,

                'token' => $token,
            ]
        );
    }

    public function store(
        AcceptClientInvitationRequest $request,
        ClientPortalInvitation $invitation
    ): RedirectResponse {
        $token =
            $request->validated('token');

        $this->ensureUsable(
            $invitation,
            $token
        );

        $clientUser =
            DB::transaction(
                function () use (
                    $request,
                    $invitation
                ) {
                    $locked =
                        ClientPortalInvitation::query()
                            ->lockForUpdate()
                            ->findOrFail(
                                $invitation->id
                            );

                    $this->ensureUsable(
                        $locked,
                        $request->validated(
                            'token'
                        )
                    );

                    $clientUser =
                        $locked->clientUser;

                    $clientUser->forceFill([
                        'password' =>
                            $request->validated(
                                'password'
                            ),

                        'status' =>
                            ClientUserStatus::Active
                                ->value,

                        'email_verified_at' =>
                            $clientUser
                                ->email_verified_at
                            ?: now(),
                    ])->save();

                    $locked->update([
                        'accepted_at' =>
                            now(),
                    ]);

                    return $clientUser;
                }
            );

        Auth::guard('client')
            ->login($clientUser);

        $request->session()
            ->regenerate();

        return redirect()
            ->route('client.dashboard')
            ->with(
                'success',
                'Your client portal account has been activated.'
            );
    }

    private function ensureUsable(
        ClientPortalInvitation $invitation,
        string $token
    ): void {
        if (
            !$invitation->isUsable()
            || !$invitation
                ->matchesToken($token)
        ) {
            throw ValidationException::withMessages([
                'token' =>
                    'This client portal invitation is invalid or has expired.',
            ]);
        }
    }
}