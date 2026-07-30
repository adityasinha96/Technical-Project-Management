<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ClientPortalProjectController extends Controller
{
    public function update(
        Request $request,
        Project $project
    ): RedirectResponse {
        abort_unless(
            $request->user()->can(
                'client-portal.manage'
            ),
            403
        );

        $validated =
            $request->validate([
                'client_portal_enabled' => [
                    'nullable',
                    'boolean',
                ],

                'client_portal_summary' => [
                    'nullable',
                    'string',
                    'max:10000',
                ],
            ]);

        $enabled =
            $request->boolean(
                'client_portal_enabled'
            );

        $project->update([
            'client_portal_enabled' =>
                $enabled,

            'client_portal_summary' =>
                $validated[
                    'client_portal_summary'
                ] ?? null,

            'client_portal_enabled_at' =>
                $enabled
                    ? (
                        $project
                            ->client_portal_enabled_at
                        ?: now()
                    )
                    : null,

            'client_portal_enabled_by' =>
                $enabled
                    ? $request->user()->id
                    : null,
        ]);

        return back()->with(
            'success',
            $enabled
                ? 'Client portal enabled.'
                : 'Client portal disabled.'
        );
    }
}