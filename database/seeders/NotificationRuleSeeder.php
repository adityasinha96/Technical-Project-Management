<?php

namespace Database\Seeders;

use App\Enums\NotificationRecipientStrategy;
use App\Enums\NotificationSeverity;
use App\Models\NotificationRule;
use Illuminate\Database\Seeder;

class NotificationRuleSeeder extends Seeder
{
    public function run(): void
    {
        $rules = [
            [
                'rule_key' =>
                    'project-deadline-3-days',

                'name' =>
                    'Project deadline approaching',

                'description' =>
                    'Notify the project team when delivery is due within three days.',

                'event_key' =>
                    'project.deadline_approaching',

                'severity' =>
                    NotificationSeverity::Warning
                        ->value,

                'recipient_strategy' =>
                    NotificationRecipientStrategy::ProjectTeam
                        ->value,

                'channels' =>
                    ['database', 'mail'],

                'lead_minutes' => 4320,
                'repeat_minutes' => 1440,
                'maximum_occurrences' => 3,
            ],

            [
                'rule_key' =>
                    'project-overdue-daily',

                'name' =>
                    'Overdue project reminder',

                'description' =>
                    'Notify management daily while a project remains overdue.',

                'event_key' =>
                    'project.overdue',

                'severity' =>
                    NotificationSeverity::Critical
                        ->value,

                'recipient_strategy' =>
                    NotificationRecipientStrategy::Management
                        ->value,

                'channels' =>
                    ['database', 'mail'],

                'lead_minutes' => 0,
                'repeat_minutes' => 1440,
                'maximum_occurrences' => 30,
            ],

            [
                'rule_key' =>
                    'task-due-24-hours',

                'name' =>
                    'Task due within 24 hours',

                'description' =>
                    'Notify the task assignee and project manager.',

                'event_key' =>
                    'task.due_soon',

                'severity' =>
                    NotificationSeverity::Warning
                        ->value,

                'recipient_strategy' =>
                    NotificationRecipientStrategy::TaskAssignee
                        ->value,

                'channels' =>
                    ['database', 'mail'],

                'lead_minutes' => 1440,
                'repeat_minutes' => 720,
                'maximum_occurrences' => 2,
            ],

            [
                'rule_key' =>
                    'task-overdue-daily',

                'name' =>
                    'Overdue task reminder',

                'description' =>
                    'Notify the assignee and manager daily while the task remains overdue.',

                'event_key' =>
                    'task.overdue',

                'severity' =>
                    NotificationSeverity::Danger
                        ->value,

                'recipient_strategy' =>
                    NotificationRecipientStrategy::TaskAssignee
                        ->value,

                'channels' =>
                    ['database', 'mail'],

                'lead_minutes' => 0,
                'repeat_minutes' => 1440,
                'maximum_occurrences' => 14,
            ],

            [
                'rule_key' =>
                    'payment-followup-due',

                'name' =>
                    'Payment follow-up due',

                'description' =>
                    'Notify the assigned collection user and accounts team.',

                'event_key' =>
                    'payment.followup_due',

                'severity' =>
                    NotificationSeverity::Warning
                        ->value,

                'recipient_strategy' =>
                    NotificationRecipientStrategy::ManagerAndAccounts
                        ->value,

                'channels' =>
                    ['database', 'mail'],

                'lead_minutes' => 0,
                'repeat_minutes' => 1440,
                'maximum_occurrences' => 30,
            ],

            [
                'rule_key' =>
                    'outstanding-project-weekly',

                'name' =>
                    'Outstanding client balance',

                'description' =>
                    'Weekly notification for projects with pending client balances.',

                'event_key' =>
                    'payment.outstanding',

                'severity' =>
                    NotificationSeverity::Danger
                        ->value,

                'recipient_strategy' =>
                    NotificationRecipientStrategy::ManagerAndAccounts
                        ->value,

                'channels' =>
                    ['database', 'mail'],

                'lead_minutes' => 0,
                'repeat_minutes' => 10080,
                'maximum_occurrences' => 52,
            ],
        ];

        foreach ($rules as $rule) {
            NotificationRule::updateOrCreate(
                [
                    'rule_key' =>
                        $rule['rule_key'],
                ],
                [
                    ...$rule,
                    'is_enabled' => true,
                ]
            );
        }
    }
}