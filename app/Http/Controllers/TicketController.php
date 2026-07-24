<?php

namespace App\Http\Controllers;

use App\Enums\TicketCommentType;
use App\Enums\TicketPriority;
use App\Enums\TicketSource;
use App\Enums\TicketStatus;
use App\Enums\TicketType;
use App\Http\Requests\AssignTicketRequest;
use App\Http\Requests\ReopenTicketRequest;
use App\Http\Requests\ResolveTicketRequest;
use App\Http\Requests\StoreTicketRequest;
use App\Http\Requests\TransitionTicketRequest;
use App\Http\Requests\UpdateTicketRequest;
use App\Models\Project;
use App\Models\ProjectTicket;
use App\Models\User;
use App\Services\Attachments\ProjectAttachmentService;
use App\Services\Tickets\TicketService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TicketController extends Controller
{
    public function __construct(
        private readonly TicketService $ticketService,
        private readonly ProjectAttachmentService $attachmentService
    ) {
    }

    public function index(
        Request $request
    ): View {
        $tickets = ProjectTicket::query()
            ->with([
                'project',
                'client',
                'assignedTo',
                'createdBy',
            ])
            ->withCount('comments')
            ->search(
                $request
                    ->string('search')
                    ->toString()
            )
            ->when(
                $request->filled('status'),
                fn ($query) =>
                    $query->where(
                        'status',
                        $request->string(
                            'status'
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
                $request->filled('type'),
                fn ($query) =>
                    $query->where(
                        'type',
                        $request->string(
                            'type'
                        )
                    )
            )
            ->when(
                $request->filled('project_id'),
                fn ($query) =>
                    $query->where(
                        'project_id',
                        $request->integer(
                            'project_id'
                        )
                    )
            )
            ->when(
                $request->filled('assigned_to'),
                fn ($query) =>
                    $query->where(
                        'assigned_to',
                        $request->integer(
                            'assigned_to'
                        )
                    )
            )
            ->when(
                $request->boolean('unassigned'),
                fn ($query) =>
                    $query->whereNull(
                        'assigned_to'
                    )
            )
            ->when(
                $request->boolean('escalated'),
                fn ($query) =>
                    $query->where(
                        'escalation_level',
                        '>',
                        0
                    )
            )
            ->orderByDesc('escalation_level')
            ->orderByRaw(
                "
                CASE priority
                    WHEN 'critical' THEN 5
                    WHEN 'urgent' THEN 4
                    WHEN 'high' THEN 3
                    WHEN 'medium' THEN 2
                    ELSE 1
                END DESC
                "
            )
            ->latest('created_at')
            ->paginate(20)
            ->withQueryString();

        $summary = [
            'open' =>
                ProjectTicket::query()
                    ->open()
                    ->count(),

            'unassigned' =>
                ProjectTicket::query()
                    ->open()
                    ->whereNull(
                        'assigned_to'
                    )
                    ->count(),

            'escalated' =>
                ProjectTicket::query()
                    ->open()
                    ->where(
                        'escalation_level',
                        '>',
                        0
                    )
                    ->count(),

            'overdue' =>
                ProjectTicket::query()
                    ->open()
                    ->whereNotIn(
                        'status',
                        [
                            TicketStatus::PendingClient
                                ->value,

                            TicketStatus::OnHold
                                ->value,
                        ]
                    )
                    ->where(
                        function ($query): void {
                            $query
                                ->where(
                                    function (
                                        $query
                                    ): void {
                                        $query
                                            ->whereNull(
                                                'first_responded_at'
                                            )
                                            ->where(
                                                'first_response_due_at',
                                                '<',
                                                now()
                                            );
                                    }
                                )
                                ->orWhere(
                                    'resolution_due_at',
                                    '<',
                                    now()
                                );
                        }
                    )
                    ->count(),

            'resolved_this_month' =>
                ProjectTicket::query()
                    ->whereNotNull(
                        'resolved_at'
                    )
                    ->whereBetween(
                        'resolved_at',
                        [
                            now()->startOfMonth(),
                            now()->endOfMonth(),
                        ]
                    )
                    ->count(),
        ];

        return view('tickets.index', [
            'tickets' => $tickets,
            'summary' => $summary,

            'statuses' =>
                TicketStatus::cases(),

            'priorities' =>
                TicketPriority::cases(),

            'types' =>
                TicketType::cases(),

            'projects' =>
                Project::query()
                    ->orderBy('name')
                    ->get(),

            'users' =>
                User::query()
                    ->where(
                        'status',
                        'active'
                    )
                    ->orderBy('name')
                    ->get(),
        ]);
    }

    public function create(
        Request $request
    ): View {
        return view('tickets.create', [
            ...$this->formData(),

            'selectedProjectId' =>
                $request->integer(
                    'project_id'
                ) ?: null,
        ]);
    }

    public function store(
        StoreTicketRequest $request
    ): RedirectResponse {
        $validated = $request->validated();

        $project = Project::query()
            ->findOrFail(
                $validated['project_id']
            );

        $ticket = DB::transaction(
            function () use (
                $request,
                $validated,
                $project
            ) {
                $ticket = $this
                    ->ticketService
                    ->create(
                        project: $project,
                        creator:
                            $request->user(),
                        data: $validated
                    );

                if (
                    $request->hasFile(
                        'attachments'
                    )
                ) {
                    $this->attachmentService
                        ->storeMany(
                            project: $project,
                            attachable: $ticket,

                            files:
                                $request->file(
                                    'attachments'
                                ),

                            uploadedBy:
                                $request->user(),

                            category: 'ticket'
                        );
                }

                return $ticket;
            }
        );

        return redirect()
            ->route(
                'tickets.show',
                $ticket
            )
            ->with(
                'success',
                "{$ticket->ticket_number} created successfully."
            );
    }

    public function show(
        ProjectTicket $ticket
    ): View {
        $ticket->load([
            'project.client',
            'client',
            'assignedTo',
            'assignedBy',
            'createdBy',
            'firstRespondedBy',
            'resolvedBy',
            'closedBy',
            'reopenedBy',

            'fileLinks.file',

            'statusHistories.changedBy',

            'escalations.acknowledgedBy',
        ]);

        $comments = $ticket
            ->comments()
            ->with([
                'createdBy',
                'editedBy',
                'fileLinks.file',
            ])
            ->paginate(30)
            ->withQueryString();

        return view('tickets.show', [
            'ticket' => $ticket,
            'comments' => $comments,

            'users' =>
                User::query()
                    ->where(
                        'status',
                        'active'
                    )
                    ->orderBy('name')
                    ->get(),

            'commentTypes' =>
                TicketCommentType::cases(),

            'allowedTransitions' =>
                $ticket
                    ->status
                    ->allowedTransitions(),
        ]);
    }

    public function update(
        UpdateTicketRequest $request,
        ProjectTicket $ticket
    ): RedirectResponse {
        abort_unless(
            $ticket->canBeManagedBy(
                $request->user()
            ),
            403
        );

        $this->ticketService
            ->updateDetails(
                ticket: $ticket,
                user: $request->user(),
                data: $request->validated()
            );

        return back()->with(
            'success',
            'Ticket details updated.'
        );
    }

    public function assign(
        AssignTicketRequest $request,
        ProjectTicket $ticket
    ): RedirectResponse {
        $assignee = $request->filled(
            'assigned_to'
        )
            ? User::query()->findOrFail(
                $request->integer(
                    'assigned_to'
                )
            )
            : null;

        $this->ticketService->assign(
            ticket: $ticket,
            assignedBy: $request->user(),
            assignee: $assignee
        );

        return back()->with(
            'success',
            $assignee
                ? "Ticket assigned to {$assignee->name}."
                : 'Ticket unassigned.'
        );
    }

    public function transition(
        TransitionTicketRequest $request,
        ProjectTicket $ticket
    ): RedirectResponse {
        abort_unless(
            $ticket->canBeManagedBy(
                $request->user()
            ),
            403
        );

        $newStatus = TicketStatus::from(
            $request->validated('status')
        );

        $this->ticketService->transition(
            ticket: $ticket,
            user: $request->user(),
            newStatus: $newStatus,
            reason:
                $request->validated(
                    'reason'
                )
        );

        return back()->with(
            'success',
            "Ticket moved to {$newStatus->label()}."
        );
    }

    public function resolve(
        ResolveTicketRequest $request,
        ProjectTicket $ticket
    ): RedirectResponse {
        abort_unless(
            $ticket->canBeManagedBy(
                $request->user()
            ),
            403
        );

        $this->ticketService->resolve(
            ticket: $ticket,
            user: $request->user(),
            data: $request->validated()
        );

        return back()->with(
            'success',
            'Ticket resolved successfully.'
        );
    }

    public function reopen(
        ReopenTicketRequest $request,
        ProjectTicket $ticket
    ): RedirectResponse {
        $this->ticketService->reopen(
            ticket: $ticket,
            user: $request->user(),
            reason:
                $request->validated(
                    'reopen_reason'
                )
        );

        return back()->with(
            'success',
            'Ticket reopened with a new SLA cycle.'
        );
    }

    private function formData(): array
    {
        return [
            'projects' =>
                Project::query()
                    ->with('client')
                    ->orderBy('name')
                    ->get(),

            'users' =>
                User::query()
                    ->where(
                        'status',
                        'active'
                    )
                    ->orderBy('name')
                    ->get(),

            'types' =>
                TicketType::cases(),

            'sources' =>
                TicketSource::cases(),

            'priorities' =>
                TicketPriority::cases(),
        ];
    }
}