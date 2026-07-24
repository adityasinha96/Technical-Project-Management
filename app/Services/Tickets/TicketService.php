<?php

namespace App\Services\Tickets;

use App\Enums\ActivityVisibility;
use App\Enums\TicketStatus;
use App\Models\Project;
use App\Models\ProjectTicket;
use App\Models\TicketComment;
use App\Models\User;
use App\Services\Projects\ProjectActivityService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TicketService
{
    public function __construct(
        private readonly TicketSlaService $slaService,
        private readonly ProjectActivityService $activityService
    ) {
    }

    public function create(
        Project $project,
        User $creator,
        array $data
    ): ProjectTicket {
        return DB::transaction(
            function () use (
                $project,
                $creator,
                $data
            ): ProjectTicket {
                $assignedTo =
                    $data['assigned_to']
                    ?? null;

                $initialStatus = $assignedTo
                    ? TicketStatus::Assigned
                    : TicketStatus::Open;

                $ticket = $project
                    ->tickets()
                    ->create([
                        'client_id' =>
                            $project->client_id,

                        'type' => $data['type'],
                        'source' => $data['source'],

                        'priority' =>
                            $data['priority'],

                        'status' =>
                            $initialStatus->value,

                        'subject' =>
                            $data['subject'],

                        'description' =>
                            $data['description'],

                        'assigned_to' =>
                            $assignedTo,

                        'assigned_by' =>
                            $assignedTo
                                ? $creator->id
                                : null,

                        'assigned_at' =>
                            $assignedTo
                                ? now()
                                : null,

                        'created_by' =>
                            $creator->id,

                        'updated_by' =>
                            $creator->id,

                        'last_activity_at' =>
                            now(),
                    ]);

                $ticket->forceFill([
                    'ticket_number' => sprintf(
                        'TKT-%s-%05d',
                        now()->format('Y'),
                        $ticket->id
                    ),

                    ...$this->slaService
                        ->initialAttributes(
                            $ticket->priority
                        ),
                ])->saveQuietly();

                $this->recordStatusHistory(
                    ticket: $ticket,
                    from: null,
                    to: $initialStatus,
                    user: $creator,
                    reason:
                        'Ticket created.'
                );

                $this->activityService->logCustom(
                    project: $project,
                    event: 'ticket_created',

                    title:
                        "{$ticket->ticket_number} created",

                    description:
                        $ticket->subject,

                    subject: $ticket,

                    metadata: [
                        'priority' =>
                            $ticket
                                ->priority
                                ->value,

                        'type' =>
                            $ticket
                                ->type
                                ->value,

                        'status' =>
                            $ticket
                                ->status
                                ->value,

                        'assigned_to' =>
                            $ticket
                                ->assigned_to,
                    ],

                    visibility:
                        $this->activityVisibility(
                            $ticket
                        ),

                    actorId:
                        $creator->id
                );

                return $ticket->refresh();
            }
        );
    }

    public function updateDetails(
        ProjectTicket $ticket,
        User $user,
        array $data
    ): ProjectTicket {
        return DB::transaction(
            function () use (
                $ticket,
                $user,
                $data
            ): ProjectTicket {
                $ticket = ProjectTicket::query()
                    ->with('project')
                    ->lockForUpdate()
                    ->findOrFail($ticket->id);

                $oldValues = [
                    'subject' =>
                        $ticket->subject,

                    'description' =>
                        $ticket->description,

                    'type' =>
                        $ticket->type->value,

                    'source' =>
                        $ticket->source->value,

                    'priority' =>
                        $ticket
                            ->priority
                            ->value,
                ];

                $priorityChanged =
                    $ticket->priority->value
                    !== $data['priority'];

                $ticket->forceFill([
                    'subject' =>
                        $data['subject'],

                    'description' =>
                        $data['description'],

                    'type' => $data['type'],
                    'source' => $data['source'],

                    'priority' =>
                        $data['priority'],

                    'updated_by' =>
                        $user->id,

                    'last_activity_at' =>
                        now(),
                ]);

                if (
                    $priorityChanged &&
                    !$ticket
                        ->status
                        ->isCompleted()
                ) {
                    $newPriority =
                        \App\Enums\TicketPriority::from(
                            $data['priority']
                        );

                    $ticket->forceFill([
                        ...$this->slaService
                            ->initialAttributes(
                                $newPriority
                            ),
                    ]);
                }

                $ticket->saveQuietly();

                $this->activityService->logCustom(
                    project:
                        $ticket->project,

                    event: 'ticket_updated',

                    title:
                        "{$ticket->ticket_number} updated",

                    subject: $ticket,

                    oldValues:
                        $oldValues,

                    newValues: [
                        'subject' =>
                            $ticket->subject,

                        'description' =>
                            $ticket
                                ->description,

                        'type' =>
                            $ticket
                                ->type
                                ->value,

                        'source' =>
                            $ticket
                                ->source
                                ->value,

                        'priority' =>
                            $ticket
                                ->priority
                                ->value,
                    ],

                    visibility:
                        $this->activityVisibility(
                            $ticket
                        ),

                    actorId: $user->id
                );

                return $ticket;
            }
        );
    }

    public function assign(
        ProjectTicket $ticket,
        User $assignedBy,
        ?User $assignee
    ): ProjectTicket {
        return DB::transaction(
            function () use (
                $ticket,
                $assignedBy,
                $assignee
            ): ProjectTicket {
                $ticket = ProjectTicket::query()
                    ->with('project')
                    ->lockForUpdate()
                    ->findOrFail($ticket->id);

                if ($ticket->status->isTerminal()) {
                    throw ValidationException::withMessages([
                        'assigned_to' =>
                            'A closed or cancelled ticket cannot be assigned.',
                    ]);
                }

                $oldAssignee =
                    $ticket->assigned_to;

                $oldStatus =
                    $ticket->status;

                $newStatus = $assignee
                    && in_array(
                        $ticket->status,
                        [
                            TicketStatus::Open,
                            TicketStatus::Reopened,
                        ],
                        true
                    )
                        ? TicketStatus::Assigned
                        : $ticket->status;

                $ticket->forceFill([
                    'assigned_to' =>
                        $assignee?->id,

                    'assigned_by' =>
                        $assignedBy->id,

                    'assigned_at' =>
                        $assignee
                            ? now()
                            : null,

                    'status' =>
                        $newStatus->value,

                    'updated_by' =>
                        $assignedBy->id,

                    'last_activity_at' =>
                        now(),
                ])->saveQuietly();

                if ($oldStatus !== $newStatus) {
                    $this->recordStatusHistory(
                        ticket: $ticket,
                        from: $oldStatus,
                        to: $newStatus,
                        user: $assignedBy,
                        reason:
                            'Ticket assigned.'
                    );
                }

                $this->activityService->logCustom(
                    project:
                        $ticket->project,

                    event: 'ticket_assigned',

                    title: $assignee
                        ? "{$ticket->ticket_number} assigned to {$assignee->name}"
                        : "{$ticket->ticket_number} unassigned",

                    subject: $ticket,

                    oldValues: [
                        'assigned_to' =>
                            $oldAssignee,
                    ],

                    newValues: [
                        'assigned_to' =>
                            $assignee?->id,
                    ],

                    visibility:
                        ActivityVisibility::Team,

                    actorId:
                        $assignedBy->id
                );

                return $ticket->refresh();
            }
        );
    }

    public function transition(
        ProjectTicket $ticket,
        User $user,
        TicketStatus $newStatus,
        ?string $reason = null
    ): ProjectTicket {
        if (
            in_array(
                $newStatus,
                [
                    TicketStatus::Resolved,
                    TicketStatus::Reopened,
                ],
                true
            )
        ) {
            throw ValidationException::withMessages([
                'status' =>
                    'Use the dedicated resolution or reopen action.',
            ]);
        }

        return DB::transaction(
            function () use (
                $ticket,
                $user,
                $newStatus,
                $reason
            ): ProjectTicket {
                $ticket = ProjectTicket::query()
                    ->with('project')
                    ->lockForUpdate()
                    ->findOrFail($ticket->id);

                $oldStatus =
                    $ticket->status;

                if (
                    !$oldStatus
                        ->canTransitionTo(
                            $newStatus
                        )
                ) {
                    throw ValidationException::withMessages([
                        'status' =>
                            "Ticket cannot move from {$oldStatus->label()} to {$newStatus->label()}.",
                    ]);
                }

                if (
                    $oldStatus->pausesSla()
                    && !$newStatus->pausesSla()
                ) {
                    $this->slaService
                        ->resume($ticket);

                    $ticket->refresh();
                }

                if (
                    !$oldStatus->pausesSla()
                    && $newStatus->pausesSla()
                ) {
                    $this->slaService
                        ->pause($ticket);

                    $ticket->refresh();
                }

                $attributes = [
                    'status' =>
                        $newStatus->value,

                    'updated_by' =>
                        $user->id,

                    'last_activity_at' =>
                        now(),
                ];

                if (
                    $newStatus ===
                    TicketStatus::Closed
                ) {
                    $attributes['closed_at'] =
                        now();

                    $attributes['closed_by'] =
                        $user->id;
                }

                if (
                    $newStatus ===
                    TicketStatus::Cancelled
                ) {
                    $attributes['closed_at'] =
                        now();

                    $attributes['closed_by'] =
                        $user->id;
                }

                $ticket->forceFill(
                    $attributes
                )->saveQuietly();

                $this->recordStatusHistory(
                    ticket: $ticket,
                    from: $oldStatus,
                    to: $newStatus,
                    user: $user,
                    reason: $reason
                );

                $this->activityService->logCustom(
                    project:
                        $ticket->project,

                    event:
                        'ticket_status_changed',

                    title:
                        "{$ticket->ticket_number} moved to {$newStatus->label()}",

                    description: $reason,

                    subject: $ticket,

                    oldValues: [
                        'status' =>
                            $oldStatus->value,
                    ],

                    newValues: [
                        'status' =>
                            $newStatus->value,
                    ],

                    visibility:
                        $this->activityVisibility(
                            $ticket
                        ),

                    actorId: $user->id
                );

                return $ticket->refresh();
            }
        );
    }

    public function resolve(
        ProjectTicket $ticket,
        User $user,
        array $data
    ): ProjectTicket {
        return DB::transaction(
            function () use (
                $ticket,
                $user,
                $data
            ): ProjectTicket {
                $ticket = ProjectTicket::query()
                    ->with('project')
                    ->lockForUpdate()
                    ->findOrFail($ticket->id);

                $oldStatus =
                    $ticket->status;

                if (
                    !$oldStatus->canTransitionTo(
                        TicketStatus::Resolved
                    )
                ) {
                    throw ValidationException::withMessages([
                        'resolution_summary' =>
                            "A {$oldStatus->label()} ticket cannot be resolved.",
                    ]);
                }

                if ($oldStatus->pausesSla()) {
                    $this->slaService
                        ->resume($ticket);

                    $ticket->refresh();
                }

                $resolvedAt = now();

                $ticket->forceFill([
                    'status' =>
                        TicketStatus::Resolved
                            ->value,

                    'resolved_at' =>
                        $resolvedAt,

                    'resolved_by' =>
                        $user->id,

                    'resolution_summary' =>
                        $data[
                            'resolution_summary'
                        ],

                    'root_cause' =>
                        $data['root_cause']
                        ?? null,

                    'preventive_action' =>
                        $data[
                            'preventive_action'
                        ] ?? null,

                    'resolution_breached_at' =>
                        $ticket
                            ->resolution_due_at
                        && $resolvedAt->greaterThan(
                            $ticket
                                ->resolution_due_at
                        )
                            ? (
                                $ticket
                                    ->resolution_breached_at
                                ?: $resolvedAt
                            )
                            : $ticket
                                ->resolution_breached_at,

                    'updated_by' =>
                        $user->id,

                    'last_activity_at' =>
                        now(),
                ])->saveQuietly();

                $this->recordStatusHistory(
                    ticket: $ticket,
                    from: $oldStatus,
                    to: TicketStatus::Resolved,
                    user: $user,
                    reason:
                        $data[
                            'resolution_summary'
                        ]
                );

                $this->activityService->logCustom(
                    project:
                        $ticket->project,

                    event: 'ticket_resolved',

                    title:
                        "{$ticket->ticket_number} resolved",

                    description:
                        $data[
                            'resolution_summary'
                        ],

                    subject: $ticket,

                    metadata: [
                        'root_cause' =>
                            $data['root_cause']
                            ?? null,

                        'preventive_action' =>
                            $data[
                                'preventive_action'
                            ] ?? null,

                        'sla_breached' =>
                            $ticket
                                ->resolution_breached_at
                            !== null,
                    ],

                    visibility:
                        $this->activityVisibility(
                            $ticket
                        ),

                    actorId: $user->id
                );

                return $ticket->refresh();
            }
        );
    }

    public function reopen(
        ProjectTicket $ticket,
        User $user,
        string $reason
    ): ProjectTicket {
        return DB::transaction(
            function () use (
                $ticket,
                $user,
                $reason
            ): ProjectTicket {
                $ticket = ProjectTicket::query()
                    ->with('project')
                    ->lockForUpdate()
                    ->findOrFail($ticket->id);

                $oldStatus =
                    $ticket->status;

                if (
                    !$oldStatus->canTransitionTo(
                        TicketStatus::Reopened
                    )
                ) {
                    throw ValidationException::withMessages([
                        'reopen_reason' =>
                            "A {$oldStatus->label()} ticket cannot be reopened.",
                    ]);
                }

                $oldResolution = [
                    'resolved_at' =>
                        $ticket->resolved_at
                            ?->toISOString(),

                    'resolution_summary' =>
                        $ticket
                            ->resolution_summary,
                ];

                $ticket->forceFill([
                    'status' =>
                        TicketStatus::Reopened
                            ->value,

                    'reopen_count' =>
                        $ticket->reopen_count + 1,

                    'reopened_at' => now(),
                    'reopened_by' => $user->id,
                    'reopen_reason' => $reason,

                    'resolved_at' => null,
                    'resolved_by' => null,
                    'closed_at' => null,
                    'closed_by' => null,

                    ...$this->slaService
                        ->restartForReopen(
                            $ticket
                        ),

                    'updated_by' => $user->id,
                    'last_activity_at' => now(),
                ])->saveQuietly();

                $this->recordStatusHistory(
                    ticket: $ticket,
                    from: $oldStatus,
                    to: TicketStatus::Reopened,
                    user: $user,
                    reason: $reason,

                    metadata: [
                        'previous_resolution' =>
                            $oldResolution,

                        'reopen_cycle' =>
                            $ticket
                                ->reopen_count,
                    ]
                );

                $this->activityService->logCustom(
                    project:
                        $ticket->project,

                    event: 'ticket_reopened',

                    title:
                        "{$ticket->ticket_number} reopened",

                    description: $reason,

                    subject: $ticket,

                    metadata: [
                        'reopen_count' =>
                            $ticket
                                ->reopen_count,

                        'previous_resolution' =>
                            $oldResolution,
                    ],

                    visibility:
                        $this->activityVisibility(
                            $ticket
                        ),

                    actorId: $user->id
                );

                return $ticket->refresh();
            }
        );
    }

    public function addComment(
        ProjectTicket $ticket,
        User $user,
        array $data
    ): TicketComment {
        return DB::transaction(
            function () use (
                $ticket,
                $user,
                $data
            ): TicketComment {
                $ticket = ProjectTicket::query()
                    ->with('project')
                    ->lockForUpdate()
                    ->findOrFail($ticket->id);

                $comment = $ticket
                    ->comments()
                    ->create([
                        'comment_type' =>
                            $data[
                                'comment_type'
                            ],

                        'message' =>
                            $data['message'],

                        'created_by' =>
                            $user->id,
                    ]);

                if (
                    !$ticket
                        ->first_responded_at
                    && $user->can(
                        'tickets.respond'
                    )
                ) {
                    $this->slaService
                        ->registerFirstResponse(
                            $ticket,
                            $user
                        );

                    $ticket->refresh();
                }

                $ticket->forceFill([
                    'last_reply_at' => now(),
                    'last_reply_by' => $user->id,
                    'last_activity_at' => now(),
                    'updated_by' => $user->id,
                ])->saveQuietly();

                $this->activityService->logCustom(
                    project:
                        $ticket->project,

                    event:
                        'ticket_comment_added',

                    title:
                        "Discussion added to {$ticket->ticket_number}",

                    description:
                        Str::limit(
                            $comment->message,
                            500
                        ),

                    subject: $comment,

                    metadata: [
                        'ticket_id' =>
                            $ticket->id,

                        'comment_type' =>
                            $comment
                                ->comment_type
                                ->value,
                    ],

                    visibility:
                        $this->activityVisibility(
                            $ticket
                        ),

                    actorId: $user->id
                );

                return $comment;
            }
        );
    }

    private function recordStatusHistory(
        ProjectTicket $ticket,
        ?TicketStatus $from,
        TicketStatus $to,
        ?User $user,
        ?string $reason = null,
        array $metadata = []
    ): void {
        $ticket->statusHistories()
            ->create([
                'from_status' =>
                    $from?->value,

                'to_status' =>
                    $to->value,

                'changed_by' =>
                    $user?->id,

                'reason' => $reason,

                'metadata' =>
                    $metadata ?: null,

                'changed_at' => now(),
            ]);
    }

    private function activityVisibility(
        ProjectTicket $ticket
    ): ActivityVisibility {
        return $ticket->type ===
            \App\Enums\TicketType::Billing
                ? ActivityVisibility::Financial
                : ActivityVisibility::Team;
    }
}