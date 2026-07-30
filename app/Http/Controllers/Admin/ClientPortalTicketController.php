<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TicketCommentVisibility;
use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectTicket;
use App\Models\TicketComment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ClientPortalTicketController extends Controller
{
    public function update(
        Request $request,
        Project $project,
        ProjectTicket $ticket
    ): RedirectResponse {
        abort_unless(
            $request->user()->can(
                'client-portal.manage'
            ),
            403
        );

        abort_unless(
            $ticket->project_id ===
                $project->id,
            404
        );

        $ticket->update([
            'client_visible' =>
                $request->boolean(
                    'client_visible'
                ),

            'client_can_reply' =>
                $request->boolean(
                    'client_can_reply'
                ),
        ]);

        return back()->with(
            'success',
            'Ticket client visibility updated.'
        );
    }

    public function updateComment(
        Request $request,
        Project $project,
        ProjectTicket $ticket,
        TicketComment $ticketComment
    ): RedirectResponse {
        abort_unless(
            $request->user()->can(
                'client-portal.manage'
            ),
            403
        );

        abort_unless(
            $ticket->project_id ===
                $project->id
            && $ticketComment
                ->project_ticket_id ===
                $ticket->id,
            404
        );

        $ticketComment->update([
            'visibility' =>
                $request->boolean(
                    'client_visible'
                )
                    ? TicketCommentVisibility::Client
                        ->value
                    : TicketCommentVisibility::Internal
                        ->value,
        ]);

        return back()->with(
            'success',
            'Discussion visibility updated.'
        );
    }
}