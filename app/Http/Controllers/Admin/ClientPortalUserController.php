<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ClientProjectRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\InviteClientUserRequest;
use App\Models\ClientProjectAccess;
use App\Models\ClientUser;
use App\Models\Project;
use App\Services\ClientPortal\ClientInvitationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ClientPortalUserController extends Controller
{
    public function __construct(
        private readonly ClientInvitationService $invitationService
    ) {
    }

    public function index(
        Project $project
    ): View {
        $project->load([
            'client',
            'clientUsers',
        ]);

        return view(
            'admin.client-portal.users',
            [
                'project' => $project,

                'accessRecords' =>
                    ClientProjectAccess::query()
                        ->with('clientUser')
                        ->where(
                            'project_id',
                            $project->id
                        )
                        ->latest()
                        ->get(),

                'roles' =>
                    ClientProjectRole::cases(),
            ]
        );
    }

    public function store(
        InviteClientUserRequest $request,
        Project $project
    ): RedirectResponse {
        abort_unless(
            $project->client_portal_enabled,
            422,
            'Enable the client portal before inviting a client.'
        );

        $invitation =
            $this->invitationService
                ->invite(
                    project: $project,
                    invitedBy:
                        $request->user(),
                    data:
                        $request->validated()
                );

        return back()->with(
            'success',
            "Invitation sent to {$invitation->clientUser->email}."
        );
    }

    public function revoke(
        Project $project,
        ClientUser $clientUser
    ): RedirectResponse {
        $access =
            ClientProjectAccess::query()
                ->where(
                    'project_id',
                    $project->id
                )
                ->where(
                    'client_user_id',
                    $clientUser->id
                )
                ->firstOrFail();

        $access->update([
            'is_active' => false,
            'revoked_at' => now(),

            'revoked_by' =>
                request()->user()->id,
        ]);

        return back()->with(
            'success',
            'Client project access revoked.'
        );
    }
}