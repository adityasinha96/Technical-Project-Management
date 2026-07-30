<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectFile;
use App\Notifications\ClientPortalAlertNotification;
use Illuminate\Http\RedirectResponse;

class ClientPortalFileController extends Controller
{
    public function update(
        Project $project,
        ProjectFile $projectFile
    ): RedirectResponse {
        abort_unless(
            request()->user()->can(
                'client-portal.manage'
            ),
            403
        );

        abort_unless(
            $projectFile->project_id ===
                $project->id,
            404
        );

        $visible =
            request()->boolean(
                'client_visible'
            );

        $projectFile->update([
            'client_visible' =>
                $visible,

            'shared_with_client_at' =>
                $visible
                    ? now()
                    : null,

            'shared_with_client_by' =>
                $visible
                    ? request()->user()->id
                    : null,
        ]);

        if ($visible) {
            $recipients =
                $project->clientUsers()
                    ->wherePivot(
                        'is_active',
                        true
                    )
                    ->wherePivot(
                        'can_view_files',
                        true
                    )
                    ->get();

            foreach ($recipients as $recipient) {
                $recipient->notify(
                    new ClientPortalAlertNotification(
                        title:
                            'New project file shared',

                        message:
                            "{$projectFile->original_name} has been shared for {$project->name}.",

                        url:
                            route(
                                'client.files.index',
                                $project
                            )
                    )
                );
            }
        }

        return back()->with(
            'success',
            $visible
                ? 'File shared with client.'
                : 'File removed from client portal.'
        );
    }
}