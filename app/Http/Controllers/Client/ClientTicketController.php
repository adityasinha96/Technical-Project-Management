<?php

namespace App\Http\Controllers\Client;

use App\Enums\TicketPriority;
use App\Enums\TicketType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Client\StoreClientTicketRequest;
use App\Models\Project;
use App\Models\ProjectTicket;
use App\Services\ClientPortal\ClientPortalAccessService;
use App\Services\ClientPortal\ClientTicketService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ClientTicketController extends Controller
{
    public function __construct(
        private readonly ClientPortalAccessService $accessService,
        private readonly ClientTicketService $ticketService
    ) {
    }

    public function create(
        Project $project
    ): View {
        $this->accessService
            ->accessFor(
                auth('client')->user(),
                $project,
                'tickets'
            );

        return view(
            'client.tickets.create',
            [
                'project' => $project,

                'types' =>
                    TicketType::cases(),

                'priorities' => [
                    TicketPriority::Low,
                    TicketPriority::Medium,
                    TicketPriority::High,
                    TicketPriority::Urgent,
                ],
            ]
        );
    }

    public function store(
        StoreClientTicketRequest $request,
        Project $project
    ): RedirectResponse {
        $ticket =
            $this->ticketService
                ->create(
                    project: $project,

                    clientUser:
                        auth('client')->user(),

                    data:
                        $request->validated()
                );

        return redirect()
            ->route(
                'client.tickets.show',
                [
                    $project,
                    $ticket,
                ]
            )
            ->with(
                'success',
                "{$ticket->ticket_number} submitted successfully."
            );
    }

    public function show(
        Project $project,
        ProjectTicket $ticket
    ): View {
        $this->accessService
            ->accessFor(
                auth('client')->user(),
                $project,
                'view'
            );

        abort_unless(
            $ticket->project_id ===
                $project->id
            && $ticket->client_visible,
            404
        );

        $ticket->load([
            'assignedTo',

            'comments' =>
                fn ($query) =>
                    $query
                        ->where(
                            'visibility',
                            \App\Enums\TicketCommentVisibility::Client
                                ->value
                        )
                        ->with([
                            'createdBy',
                            'clientUser',
                            'fileLinks.file',
                        ])
                        ->oldest(),
        ]);

        return view(
            'client.tickets.show',
            compact(
                'project',
                'ticket'
            )
        );
    }
}