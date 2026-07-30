<?php

namespace App\Services\ClientPortal;

use App\Models\ClientProjectAccess;
use App\Models\ClientUser;
use App\Models\Project;
use Illuminate\Auth\Access\AuthorizationException;

class ClientPortalAccessService
{
    public function accessFor(
        ClientUser $clientUser,
        Project $project,
        ?string $capability = null
    ): ClientProjectAccess {
        $this->ensureBaseAccess(
            $clientUser,
            $project
        );

        $access =
            ClientProjectAccess::query()
                ->where(
                    'client_user_id',
                    $clientUser->id
                )
                ->where(
                    'project_id',
                    $project->id
                )
                ->where('is_active', true)
                ->first();

        if (!$access) {
            throw new AuthorizationException(
                'You do not have access to this project.'
            );
        }

        if (
            $capability
            && !$this->hasCapability(
                $access,
                $capability
            )
        ) {
            throw new AuthorizationException(
                'You do not have permission to perform this action.'
            );
        }

        return $access;
    }

    private function ensureBaseAccess(
        ClientUser $clientUser,
        Project $project
    ): void {
        if (
            !$project->client_portal_enabled
            || $project->client_id !==
                $clientUser->client_id
        ) {
            throw new AuthorizationException(
                'Project portal access is unavailable.'
            );
        }
    }

    private function hasCapability(
        ClientProjectAccess $access,
        string $capability
    ): bool {
        $allowed = [
            'view' =>
                $access->can_view_project,

            'financials' =>
                $access->can_view_financials,

            'approve' =>
                $access->can_approve,

            'tickets' =>
                $access->can_submit_tickets,

            'files' =>
                $access->can_view_files,

            'communicate' =>
                $access->can_communicate,
        ];

        return $allowed[$capability]
            ?? false;
    }
}