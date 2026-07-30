<?php

namespace App\Services\ClientPortal;

use App\Enums\ClientProjectRole;
use App\Enums\ClientUserStatus;
use App\Models\ClientPortalInvitation;
use App\Models\ClientUser;
use App\Models\Project;
use App\Models\User;
use App\Notifications\ClientPortalInvitationNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class ClientInvitationService
{
    public function invite(
        Project $project,
        User $invitedBy,
        array $data
    ): ClientPortalInvitation {
        return DB::transaction(
            function () use (
                $project,
                $invitedBy,
                $data
            ): ClientPortalInvitation {
                $clientUser =
                    ClientUser::withTrashed()
                        ->where(
                            'email',
                            $data['email']
                        )
                        ->first();

                if (!$clientUser) {
                    $clientUser =
                        ClientUser::create([
                            'client_id' =>
                                $project->client_id,

                            'name' =>
                                $data['name'],

                            'email' =>
                                strtolower(
                                    $data['email']
                                ),

                            'phone' =>
                                $data['phone']
                                ?? null,

                            'designation' =>
                                $data['designation']
                                ?? null,

                            'status' =>
                                ClientUserStatus::Invited
                                    ->value,

                            'created_by' =>
                                $invitedBy->id,
                        ]);
                } else {
                    if (
                        $clientUser->client_id !==
                        $project->client_id
                    ) {
                        abort(
                            422,
                            'This email is already connected to another client.'
                        );
                    }

                    if ($clientUser->trashed()) {
                        $clientUser->restore();
                    }

                    $clientUser->update([
                        'name' =>
                            $data['name'],

                        'phone' =>
                            $data['phone']
                            ?? $clientUser->phone,

                        'designation' =>
                            $data['designation']
                            ?? $clientUser
                                ->designation,
                    ]);
                }

                $clientUser->projects()
                    ->syncWithoutDetaching([
                        $project->id => [
                            'role' =>
                                $data['role']
                                ?? ClientProjectRole::Viewer
                                    ->value,

                            'can_view_project' =>
                                true,

                            'can_view_financials' =>
                                $data[
                                    'can_view_financials'
                                ] ?? false,

                            'can_approve' =>
                                $data[
                                    'can_approve'
                                ] ?? false,

                            'can_submit_tickets' =>
                                $data[
                                    'can_submit_tickets'
                                ] ?? false,

                            'can_view_files' =>
                                $data[
                                    'can_view_files'
                                ] ?? true,

                            'can_communicate' =>
                                $data[
                                    'can_communicate'
                                ] ?? true,

                            'is_active' => true,

                            'granted_by' =>
                                $invitedBy->id,

                            'granted_at' => now(),

                            'revoked_at' => null,
                            'revoked_by' => null,
                        ],
                    ]);

                $rawToken =
                    Str::random(64);

                $invitation =
                    ClientPortalInvitation::create([
                        'client_user_id' =>
                            $clientUser->id,

                        'project_id' =>
                            $project->id,

                        'token_hash' =>
                            hash(
                                'sha256',
                                $rawToken
                            ),

                        'expires_at' =>
                            now()->addDays(7),

                        'invited_by' =>
                            $invitedBy->id,

                        'last_sent_at' =>
                            now(),
                    ]);

                $invitationUrl =
                    URL::temporarySignedRoute(
                        'client.invitation.show',
                        now()->addDays(7),
                        [
                            'invitation' =>
                                $invitation->id,

                            'token' =>
                                $rawToken,
                        ]
                    );

                $clientUser->notify(
                    new ClientPortalInvitationNotification(
                        project: $project,
                        invitationUrl:
                            $invitationUrl
                    )
                );

                return $invitation;
            }
        );
    }
}