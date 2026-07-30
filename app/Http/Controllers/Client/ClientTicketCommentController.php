<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\StoreClientTicketCommentRequest;
use App\Models\Project;
use App\Models\ProjectTicket;
use App\Services\ClientPortal\ClientTicketService;
use Illuminate\Http\RedirectResponse;

class ClientTicketCommentController extends Controller
{
    public function __construct(
        private readonly ClientTicketService $ticketService
    ) {
    }

    public function store(
        StoreClientTicketCommentRequest $request,
        Project $project,
        ProjectTicket $ticket
    ): RedirectResponse {
        abort_unless(
            $ticket->project_id ===
                $project->id,
            404
        );

        $this->ticketService->addComment(
            ticket: $ticket,

            clientUser:
                auth('client')->user(),

            message:
                $request->validated(
                    'message'
                )
        );

        return back()->with(
            'success',
            'Your reply has been added.'
        );
    }
}