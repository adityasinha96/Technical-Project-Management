<?php

namespace App\Services\Tickets;

use App\Enums\ActivityVisibility;
use App\Enums\NotificationSeverity;
use App\Enums\TicketEscalationLevel;
use App\Enums\TicketPriority;
use App\Models\ProjectTicket;
use App\Models\TicketSlaPolicy;
use App\Models\User;
use App\Services\Projects\ProjectActivityService;
use App\Services\Notifications\NotificationDispatcher;
use App\Services\Notifications\NotificationRecipientResolver;
use Illuminate\Support\Facades\DB;

class TicketSlaService
{
    public function __construct(
        private readonly ProjectActivityService $activityService,
        private readonly NotificationDispatcher $notificationDispatcher,
        private readonly NotificationRecipientResolver $notificationRecipientResolver
    ) {
    }

    public function initialAttributes(
        TicketPriority $priority
    ): array {
        $policy = TicketSlaPolicy::forPriority(
            $priority
        );

        return [
            'first_response_due_at' =>
                now()->addMinutes(
                    $policy
                        ->first_response_minutes
                ),

            'resolution_due_at' =>
                now()->addMinutes(
                    $policy
                        ->resolution_minutes
                ),

            'sla_paused_at' => null,
            'sla_paused_minutes' => 0,
            'escalation_level' => 0,
            'escalated_at' => null,
        ];
    }

    public function pause(
        ProjectTicket $ticket
    ): void {
        if ($ticket->sla_paused_at) {
            return;
        }

        $ticket->forceFill([
            'sla_paused_at' => now(),
        ])->saveQuietly();
    }

    public function resume(
        ProjectTicket $ticket
    ): void {
        if (!$ticket->sla_paused_at) {
            return;
        }

        $pausedMinutes = max(
            0,
            $ticket->sla_paused_at
                ->diffInMinutes(now())
        );

        $ticket->forceFill([
            'first_response_due_at' =>
                $ticket->first_response_due_at
                    ? $ticket
                        ->first_response_due_at
                        ->copy()
                        ->addMinutes(
                            $pausedMinutes
                        )
                    : null,

            'resolution_due_at' =>
                $ticket->resolution_due_at
                    ? $ticket
                        ->resolution_due_at
                        ->copy()
                        ->addMinutes(
                            $pausedMinutes
                        )
                    : null,

            'sla_paused_minutes' =>
                $ticket->sla_paused_minutes
                + $pausedMinutes,

            'sla_paused_at' => null,
        ])->saveQuietly();
    }

    public function registerFirstResponse(
        ProjectTicket $ticket,
        User $user
    ): void {
        if ($ticket->first_responded_at) {
            return;
        }

        $respondedAt = now();

        $ticket->forceFill([
            'first_responded_at' =>
                $respondedAt,

            'first_responded_by' =>
                $user->id,

            'response_breached_at' =>
                $ticket->first_response_due_at
                && $respondedAt->greaterThan(
                    $ticket
                        ->first_response_due_at
                )
                    ? $respondedAt
                    : null,
        ])->saveQuietly();

        $this->activityService->logCustom(
            project: $ticket->project,
            event: 'ticket_first_response',

            title:
                "{$ticket->ticket_number} received its first response",

            subject: $ticket,

            metadata: [
                'response_due_at' =>
                    $ticket
                        ->first_response_due_at
                        ?->toISOString(),

                'responded_at' =>
                    $respondedAt
                        ->toISOString(),

                'breached' =>
                    $ticket
                        ->response_breached_at
                    !== null,
            ],

            visibility:
                ActivityVisibility::Team,

            actorId: $user->id
        );
    }

    public function restartForReopen(
        ProjectTicket $ticket
    ): array {
        $attributes = $this
            ->initialAttributes(
                $ticket->priority
            );

        return [
            ...$attributes,

            'first_responded_at' => null,
            'first_responded_by' => null,

            'response_breached_at' =>
                null,

            'resolution_breached_at' =>
                null,
        ];
    }

    public function checkAndEscalate(
        ProjectTicket $ticket
    ): int {
        return DB::transaction(
            function () use ($ticket): int {
                $ticket = ProjectTicket::query()
                    ->with('project')
                    ->lockForUpdate()
                    ->findOrFail($ticket->id);

                if (
                    $ticket->status->isCompleted()
                    || $ticket->status->pausesSla()
                ) {
                    return $ticket
                        ->escalation_level;
                }

                $dueAt =
                    $ticket->current_sla_due_at;

                if (!$dueAt) {
                    return 0;
                }

                $policy =
                    TicketSlaPolicy::forPriority(
                        $ticket->priority
                    );

                $desiredLevel =
                    $this->desiredLevel(
                        $dueAt,
                        $policy
                    );

                if (
                    now()->greaterThan($dueAt)
                ) {
                    if (
                        !$ticket
                            ->first_responded_at
                    ) {
                        $ticket->forceFill([
                            'response_breached_at' =>
                                $ticket
                                    ->response_breached_at
                                ?: now(),
                        ]);
                    } else {
                        $ticket->forceFill([
                            'resolution_breached_at' =>
                                $ticket
                                    ->resolution_breached_at
                                ?: now(),
                        ]);
                    }
                }

                if (
                    $desiredLevel <=
                    $ticket->escalation_level
                ) {
                    $ticket->saveQuietly();

                    return $ticket
                        ->escalation_level;
                }

                for (
                    $level =
                        $ticket->escalation_level + 1;

                    $level <= $desiredLevel;

                    $level++
                ) {
                    $levelEnum =
                        TicketEscalationLevel::from(
                            $level
                        );

                    $minutesOverdue =
                        now()->greaterThan($dueAt)
                            ? $dueAt
                                ->diffInMinutes(
                                    now()
                                )
                            : 0;

                    $escalation = $ticket
                        ->escalations()
                        ->firstOrCreate(
                            [
                                'cycle' =>
                                    $ticket
                                        ->reopen_count,

                                'level' => $level,
                            ],
                            [
                                'due_at' => $dueAt,

                                'triggered_at' =>
                                    now(),

                                'minutes_overdue' =>
                                    $minutesOverdue,

                                'reason' =>
                                    $levelEnum
                                        ->label(),
                            ]
                        );

                    if ($escalation->wasRecentlyCreated) {
                        $this->activityService
                            ->logCustom(
                                project:
                                    $ticket->project,

                                event:
                                    'ticket_escalated',

                                title:
                                    "{$ticket->ticket_number} escalated to {$levelEnum->label()}",

                                subject:
                                    $ticket,

                                metadata: [
                                    'level' =>
                                        $level,

                                    'due_at' =>
                                        $dueAt
                                            ->toISOString(),

                                    'minutes_overdue' =>
                                        $minutesOverdue,

                                    'cycle' =>
                                        $ticket
                                            ->reopen_count,
                                ],

                                visibility:
                                    ActivityVisibility::Management,

                                actorId: null
                            );

                        $eventKey = match ($levelEnum) {
                            TicketEscalationLevel::Warning =>
                                'ticket.sla_warning',

                            TicketEscalationLevel::Overdue =>
                                'ticket.sla_overdue',

                            TicketEscalationLevel::Critical =>
                                'ticket.sla_critical',
                        };

                        $severity = match ($levelEnum) {
                            TicketEscalationLevel::Warning =>
                                NotificationSeverity::Warning,

                            TicketEscalationLevel::Overdue =>
                                NotificationSeverity::Danger,

                            TicketEscalationLevel::Critical =>
                                NotificationSeverity::Critical,
                        };

                        $recipients =
                            $this
                                ->notificationRecipientResolver
                                ->ticketRecipients(
                                    $ticket
                                )
                                ->merge(
                                    $this
                                        ->notificationRecipientResolver
                                        ->management()
                                )
                                ->unique('id')
                                ->values();

                        $this->notificationDispatcher->send(
                            recipients: $recipients,
                            eventKey: $eventKey,

                            title:
                                "{$ticket->ticket_number}: {$levelEnum->label()}",

                            message:
                                "{$ticket->subject} requires action. Current escalation level: {$level}.",

                            url: route(
                                'tickets.show',
                                $ticket
                            ),

                            severity: $severity,
                            subject: $ticket,

                            context: [
                                'ticket_id' =>
                                    $ticket->id,

                                'escalation_level' =>
                                    $level,

                                'minutes_overdue' =>
                                    $minutesOverdue,
                            ],

                            dedupeBucket:
                                "ticket-escalation:{$ticket->id}:{$ticket->reopen_count}:{$level}"
                        );
                    }
                }

                $ticket->forceFill([
                    'escalation_level' =>
                        $desiredLevel,

                    'escalated_at' =>
                        now(),
                ])->saveQuietly();

                return $desiredLevel;
            }
        );
    }

    private function desiredLevel(
        $dueAt,
        TicketSlaPolicy $policy
    ): int {
        $warningAt = $dueAt
            ->copy()
            ->subMinutes(
                $policy
                    ->warning_before_minutes
            );

        $levelTwoAt = $dueAt
            ->copy()
            ->addMinutes(
                $policy
                    ->level_two_after_minutes
            );

        $levelThreeAt = $dueAt
            ->copy()
            ->addMinutes(
                $policy
                    ->level_three_after_minutes
            );

        if (now()->lessThan($warningAt)) {
            return 0;
        }

        if (now()->lessThan($levelTwoAt)) {
            return 1;
        }

        if (now()->lessThan($levelThreeAt)) {
            return 2;
        }

        return 3;
    }
}

