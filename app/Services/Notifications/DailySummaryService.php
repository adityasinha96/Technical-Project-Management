<?php

namespace App\Services\Notifications;

use App\Enums\ProjectStatus;
use App\Enums\TaskStatus;
use App\Models\PaymentFollowup;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\ProjectTicket;
use App\Models\User;

class DailySummaryService
{
    public function buildFor(
        User $user
    ): array {
        $isAccounts =
            $user->hasRole(
                'accounts'
            );

        $taskQuery =
            ProjectTask::query()
                ->where(
                    'assigned_to',
                    $user->id
                )
                ->whereNotIn('status', [
                    TaskStatus::Completed
                        ->value,

                    TaskStatus::Cancelled
                        ->value,
                ]);

        $managedProjects =
            Project::query()
                ->where(
                    'manager_id',
                    $user->id
                )
                ->whereNotIn('status', [
                    ProjectStatus::Completed
                        ->value,

                    ProjectStatus::Cancelled
                        ->value,
                ]);

        $ticketQuery =
            ProjectTicket::query()
                ->open()
                ->where(
                    'assigned_to',
                    $user->id
                );

        $followupQuery =
            PaymentFollowup::query()
                ->open()
                ->whereRaw(
                    'COALESCE(next_followup_at, followup_at) <= ?',
                    [now()]
                );

        if (!$isAccounts) {
            $followupQuery->where(
                'assigned_to',
                $user->id
            );
        }

        return [
            'date' => today()
                ->format('d M Y'),

            'tasks_due_today' =>
                (clone $taskQuery)
                    ->whereDate(
                        'due_date',
                        today()
                    )
                    ->count(),

            'overdue_tasks' =>
                (clone $taskQuery)
                    ->whereDate(
                        'due_date',
                        '<',
                        today()
                    )
                    ->count(),

            'projects_due_soon' =>
                (clone $managedProjects)
                    ->whereBetween(
                        'expected_delivery_date',
                        [
                            today(),
                            today()->addDays(3),
                        ]
                    )
                    ->count(),

            'overdue_projects' =>
                (clone $managedProjects)
                    ->whereDate(
                        'expected_delivery_date',
                        '<',
                        today()
                    )
                    ->count(),

            'assigned_tickets' =>
                (clone $ticketQuery)
                    ->count(),

            'escalated_tickets' =>
                (clone $ticketQuery)
                    ->where(
                        'escalation_level',
                        '>',
                        0
                    )
                    ->count(),

            'payment_followups_due' =>
                $followupQuery->count(),

            'unread_notifications' =>
                $user
                    ->unreadNotifications()
                    ->count(),

            'market_outstanding' =>
                $isAccounts
                    ? Project::query()
                        ->sum(
                            'pending_amount'
                        )
                    : null,
        ];
    }
}