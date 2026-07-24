<?php

namespace App\Http\Controllers;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\ProjectTicket;
use App\Models\TicketEscalation;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TicketEscalationController extends Controller
{
    public function index(
        Request $request
    ): View {
        $tickets = ProjectTicket::query()
            ->with([
                'project.client',
                'assignedTo',

                'escalations' =>
                    fn ($query) =>
                        $query->latest(
                            'triggered_at'
                        ),
            ])
            ->open()
            ->where(
                'escalation_level',
                '>',
                0
            )
            ->when(
                $request->filled('level'),
                fn ($query) =>
                    $query->where(
                        'escalation_level',
                        $request->integer(
                            'level'
                        )
                    )
            )
            ->when(
                $request->filled('priority'),
                fn ($query) =>
                    $query->where(
                        'priority',
                        $request->string(
                            'priority'
                        )
                    )
            )
            ->when(
                $request->boolean(
                    'unacknowledged'
                ),
                fn ($query) =>
                    $query->whereHas(
                        'escalations',
                        function ($query): void {
                            $query
                                ->whereColumn(
                                    'level',
                                    'project_tickets.escalation_level'
                                )
                                ->whereNull(
                                    'acknowledged_at'
                                );
                        }
                    )
            )
            ->orderByDesc(
                'escalation_level'
            )
            ->orderBy(
                'resolution_due_at'
            )
            ->paginate(20)
            ->withQueryString();

        $summary = [
            'level_one' =>
                ProjectTicket::query()
                    ->open()
                    ->where(
                        'escalation_level',
                        1
                    )
                    ->count(),

            'level_two' =>
                ProjectTicket::query()
                    ->open()
                    ->where(
                        'escalation_level',
                        2
                    )
                    ->count(),

            'level_three' =>
                ProjectTicket::query()
                    ->open()
                    ->where(
                        'escalation_level',
                        3
                    )
                    ->count(),

            'unassigned_escalated' =>
                ProjectTicket::query()
                    ->open()
                    ->where(
                        'escalation_level',
                        '>',
                        0
                    )
                    ->whereNull(
                        'assigned_to'
                    )
                    ->count(),
        ];

        return view(
            'tickets.escalations',
            [
                'tickets' => $tickets,
                'summary' => $summary,

                'priorities' =>
                    TicketPriority::cases(),
            ]
        );
    }

    public function acknowledge(
        Request $request,
        ProjectTicket $ticket,
        TicketEscalation $ticketEscalation
    ): RedirectResponse {
        abort_unless(
            $ticketEscalation
                ->project_ticket_id
            === $ticket->id,
            404
        );

        $validated = $request->validate([
            'acknowledgement_note' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ]);

        $ticketEscalation->update([
            'acknowledged_at' => now(),

            'acknowledged_by' =>
                $request->user()->id,

            'acknowledgement_note' =>
                $validated[
                    'acknowledgement_note'
                ] ?? null,
        ]);

        return back()->with(
            'success',
            'Escalation acknowledged.'
        );
    }
}