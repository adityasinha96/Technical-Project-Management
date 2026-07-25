<?php

namespace App\Support;

use App\Enums\NotificationSeverity;

class NotificationCatalog
{
    public static function all(): array
    {
        return [
            'project.assigned' => [
                'label' => 'Project Assigned',
                'category' => 'Projects',
                'description' => 'A project has been assigned to you.',
                'severity' => NotificationSeverity::Info,
                'channels' => ['database', 'mail'],
                'digest' => true,
            ],

            'project.deadline_approaching' => [
                'label' => 'Project Deadline Approaching',
                'category' => 'Projects',
                'description' => 'A project deadline is approaching.',
                'severity' => NotificationSeverity::Warning,
                'channels' => ['database', 'mail'],
                'digest' => true,
            ],

            'project.overdue' => [
                'label' => 'Project Overdue',
                'category' => 'Projects',
                'description' => 'A project has passed its delivery deadline.',
                'severity' => NotificationSeverity::Critical,
                'channels' => ['database', 'mail'],
                'digest' => true,
            ],

            'task.assigned' => [
                'label' => 'Task Assigned',
                'category' => 'Tasks',
                'description' => 'A project task has been assigned to you.',
                'severity' => NotificationSeverity::Info,
                'channels' => ['database', 'mail'],
                'digest' => true,
            ],

            'task.due_soon' => [
                'label' => 'Task Due Soon',
                'category' => 'Tasks',
                'description' => 'An assigned task is approaching its due date.',
                'severity' => NotificationSeverity::Warning,
                'channels' => ['database', 'mail'],
                'digest' => true,
            ],

            'task.overdue' => [
                'label' => 'Task Overdue',
                'category' => 'Tasks',
                'description' => 'An assigned task is overdue.',
                'severity' => NotificationSeverity::Danger,
                'channels' => ['database', 'mail'],
                'digest' => true,
            ],

            'approval.submitted' => [
                'label' => 'Approval Submitted',
                'category' => 'Approvals',
                'description' => 'Frontend or backend work has been submitted for approval.',
                'severity' => NotificationSeverity::Info,
                'channels' => ['database', 'mail'],
                'digest' => true,
            ],

            'approval.changes_requested' => [
                'label' => 'Approval Changes Requested',
                'category' => 'Approvals',
                'description' => 'Changes have been requested during an approval review.',
                'severity' => NotificationSeverity::Warning,
                'channels' => ['database', 'mail'],
                'digest' => true,
            ],

            'approval.approved' => [
                'label' => 'Approval Completed',
                'category' => 'Approvals',
                'description' => 'Frontend or backend approval has been recorded.',
                'severity' => NotificationSeverity::Success,
                'channels' => ['database', 'mail'],
                'digest' => true,
            ],

            'payment.received' => [
                'label' => 'Payment Received',
                'category' => 'Payments',
                'description' => 'A project payment has been recorded.',
                'severity' => NotificationSeverity::Success,
                'channels' => ['database', 'mail'],
                'digest' => true,
            ],

            'payment.followup_due' => [
                'label' => 'Payment Follow-up Due',
                'category' => 'Payments',
                'description' => 'A payment collection follow-up is due.',
                'severity' => NotificationSeverity::Warning,
                'channels' => ['database', 'mail'],
                'digest' => true,
            ],

            'payment.outstanding' => [
                'label' => 'Outstanding Client Balance',
                'category' => 'Payments',
                'description' => 'A project has an outstanding client balance.',
                'severity' => NotificationSeverity::Danger,
                'channels' => ['database', 'mail'],
                'digest' => true,
            ],

            'ticket.created' => [
                'label' => 'Ticket Created',
                'category' => 'Tickets',
                'description' => 'A project ticket has been created.',
                'severity' => NotificationSeverity::Info,
                'channels' => ['database'],
                'digest' => true,
            ],

            'ticket.assigned' => [
                'label' => 'Ticket Assigned',
                'category' => 'Tickets',
                'description' => 'A ticket has been assigned or reassigned.',
                'severity' => NotificationSeverity::Info,
                'channels' => ['database', 'mail'],
                'digest' => true,
            ],

            'ticket.comment_added' => [
                'label' => 'Ticket Discussion Added',
                'category' => 'Tickets',
                'description' => 'A new internal discussion was added to a ticket.',
                'severity' => NotificationSeverity::Info,
                'channels' => ['database'],
                'digest' => true,
            ],

            'ticket.sla_warning' => [
                'label' => 'Ticket SLA Warning',
                'category' => 'Tickets',
                'description' => 'A ticket is approaching its SLA deadline.',
                'severity' => NotificationSeverity::Warning,
                'channels' => ['database', 'mail'],
                'digest' => true,
            ],

            'ticket.sla_overdue' => [
                'label' => 'Ticket SLA Overdue',
                'category' => 'Tickets',
                'description' => 'A ticket has breached its SLA.',
                'severity' => NotificationSeverity::Danger,
                'channels' => ['database', 'mail'],
                'digest' => true,
            ],

            'ticket.sla_critical' => [
                'label' => 'Critical Ticket Escalation',
                'category' => 'Tickets',
                'description' => 'A ticket has reached critical escalation.',
                'severity' => NotificationSeverity::Critical,
                'channels' => ['database', 'mail'],
                'digest' => true,
            ],

            'ticket.resolved' => [
                'label' => 'Ticket Resolved',
                'category' => 'Tickets',
                'description' => 'A ticket has been resolved.',
                'severity' => NotificationSeverity::Success,
                'channels' => ['database'],
                'digest' => true,
            ],

            'ticket.reopened' => [
                'label' => 'Ticket Reopened',
                'category' => 'Tickets',
                'description' => 'A previously resolved ticket has been reopened.',
                'severity' => NotificationSeverity::Warning,
                'channels' => ['database', 'mail'],
                'digest' => true,
            ],

            'digest.daily' => [
                'label' => 'Daily Work Summary',
                'category' => 'Summaries',
                'description' => 'Daily summary of projects, tasks, payments and tickets.',
                'severity' => NotificationSeverity::Info,
                'channels' => ['database', 'mail'],
                'digest' => false,
            ],
        ];
    }

    public static function get(string $key): array
    {
        return self::all()[$key] ?? [
            'label' => str($key)
                ->replace(['.', '_'], ' ')
                ->title()
                ->toString(),

            'category' => 'System',
            'description' => 'System notification.',
            'severity' => NotificationSeverity::Info,
            'channels' => ['database'],
            'digest' => true,
        ];
    }

    public static function grouped(): array
    {
        return collect(self::all())
            ->groupBy('category')
            ->all();
    }
}