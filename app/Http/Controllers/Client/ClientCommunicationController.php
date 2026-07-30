<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\StoreClientCommunicationRequest;
use App\Models\ClientCommunication;
use App\Models\Project;
use App\Services\ClientPortal\ClientCommunicationService;
use App\Services\ClientPortal\ClientPortalAccessService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ClientCommunicationController extends Controller
{
    public function __construct(
        private readonly ClientPortalAccessService $accessService,
        private readonly ClientCommunicationService $communicationService
    ) {
    }

    public function index(
        Project $project
    ): View {
        $this->accessService->accessFor(
            auth('client')->user(),
            $project,
            'communicate'
        );

        ClientCommunication::query()
            ->where(
                'project_id',
                $project->id
            )
            ->whereNull(
                'client_read_at'
            )
            ->update([
                'client_read_at' =>
                    now(),
            ]);

        $messages =
            ClientCommunication::query()
                ->with([
                    'clientUser',
                    'user',
                    'fileLinks.file',
                ])
                ->where(
                    'project_id',
                    $project->id
                )
                ->oldest()
                ->paginate(50);

        return view(
            'client.communications.index',
            compact(
                'project',
                'messages'
            )
        );
    }

    public function store(
        StoreClientCommunicationRequest $request,
        Project $project
    ): RedirectResponse {
        $this->communicationService
            ->sendFromClient(
                project: $project,

                clientUser:
                    auth('client')->user(),

                message:
                    $request->validated(
                        'message'
                    ),

                replyToId:
                    $request->validated(
                        'reply_to_id'
                    )
            );

        return back()->with(
            'success',
            'Your message has been sent.'
        );
    }
}