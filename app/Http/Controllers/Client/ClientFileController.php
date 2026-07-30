<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectFile;
use App\Services\ClientPortal\ClientPortalAccessService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ClientFileController extends Controller
{
    public function __construct(
        private readonly ClientPortalAccessService $accessService
    ) {
    }

    public function index(
        Project $project
    ): View {
        $this->accessService->accessFor(
            auth('client')->user(),
            $project,
            'files'
        );

        $files =
            ProjectFile::query()
                ->where(
                    'project_id',
                    $project->id
                )
                ->where(
                    'client_visible',
                    true
                )
                ->latest(
                    'shared_with_client_at'
                )
                ->paginate(30);

        return view(
            'client.files.index',
            compact(
                'project',
                'files'
            )
        );
    }

    public function download(
        Project $project,
        ProjectFile $projectFile
    ): StreamedResponse {
        $this->accessService->accessFor(
            auth('client')->user(),
            $project,
            'files'
        );

        abort_unless(
            $projectFile->project_id ===
                $project->id
            && $projectFile->client_visible,
            404
        );

        abort_unless(
            Storage::disk(
                $projectFile->disk
            )->exists(
                $projectFile->path
            ),
            404
        );

        return Storage::disk(
            $projectFile->disk
        )->download(
            $projectFile->path,
            $projectFile->original_name
        );
    }
}