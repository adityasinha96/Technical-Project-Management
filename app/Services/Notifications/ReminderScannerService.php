<?php

namespace App\Services\Notifications;

use App\Enums\ProjectStatus;
use App\Enums\TaskStatus;
use App\Models\NotificationDispatch;
use App\Models\NotificationRule;
use App\Models\PaymentFollowup;
use App\Models\Project;
use App\Models\ProjectTask;
use Illuminate\Database\Eloquent\Model;

class ReminderScannerService
{
    public function __construct(
        private readonly NotificationDispatcher $dispatcher,
        private readonly NotificationRecipientResolver $recipientResolver
    ) {
    }

    public function scan(): array
    {
        $result = [
            'rules' => 0,
            'subjects' => 0,
            'recipients' => 0,
        ];

        NotificationRule::query()
            ->enabled()
            ->orderBy('id')
            ->each(
                function (
                    NotificationRule $rule
                ) use (&$result): void {
                    $result['rules']++;

                    $subjects =
                        $this->subjectsFor(
                            $rule
                        );

                    foreach ($subjects as $subject) {
                        if (
                            $this->maximumReached(
                                $rule,
                                $subject
                            )
                        ) {
                            continue;
                        }

                        $recipients =
                            $this
                                ->recipientResolver
                                ->resolve(
                                    $rule,
                                    $subject
                                );

                        if ($recipients->isEmpty()) {
                            continue;
                        }

                        $payload =
                            $this->payloadFor(
                                $rule,
                                $subject
                            );

                        $sent =
                            $this->dispatcher->send(
                                recipients:
                                    $recipients,

                                eventKey:
                                    $rule->event_key,

                                title:
                                    $payload['title'],

                                message:
                                    $payload['message'],

                                url:
                                    $payload['url'],

                                severity:
                                    $rule->severity,

                                subject:
                                    $subject,

                                context:
                                    $payload['context'],

                                requestedChannels:
                                    $rule->channels,

                                dedupeBucket:
                                    $this->repeatBucket(
                                        $rule
                                    )
                            );

                        $result['subjects']++;
                        $result['recipients'] +=
                            $sent;
                    }
                }
            );

        return $result;
    }

    private function subjectsFor(
        NotificationRule $rule
    ) {
        return match ($rule->event_key) {
            'project.deadline_approaching' =>
                $this->projectsDueSoon(
                    $rule
                ),

            'project.overdue' =>
                $this->overdueProjects(),

            'task.due_soon' =>
                $this->tasksDueSoon(
                    $rule
                ),

            'task.overdue' =>
                $this->overdueTasks(),

            'payment.followup_due' =>
                $this->duePaymentFollowups(),

            'payment.outstanding' =>
                $this->outstandingProjects(),

            default => collect(),
        };
    }

    private function projectsDueSoon(
        NotificationRule $rule
    ) {
        $cutoff = now()
            ->addMinutes(
                $rule->lead_minutes
            );

        return Project::query()
            ->with([
                'manager',
                'members',
                'client',
            ])
            ->whereNotIn('status', [
                ProjectStatus::Completed->value,
                ProjectStatus::Cancelled->value,
            ])
            ->whereDate(
                'expected_delivery_date',
                '>=',
                today()
            )
            ->where(
                'expected_delivery_date',
                '<=',
                $cutoff
            )
            ->get();
    }

    private function overdueProjects()
    {
        return Project::query()
            ->with([
                'manager',
                'members',
                'client',
            ])
            ->whereNotIn('status', [
                ProjectStatus::Completed->value,
                ProjectStatus::Cancelled->value,
            ])
            ->whereDate(
                'expected_delivery_date',
                '<',
                today()
            )
            ->get();
    }

    private function tasksDueSoon(
        NotificationRule $rule
    ) {
        $cutoff = now()
            ->addMinutes(
                $rule->lead_minutes
            );

        return ProjectTask::query()
            ->with([
                'assignee',
                'project.manager',
            ])
            ->whereNotIn('status', [
                TaskStatus::Completed->value,
                TaskStatus::Cancelled->value,
            ])
            ->whereNotNull('due_date')
            ->whereDate(
                'due_date',
                '>=',
                today()
            )
            ->where(
                'due_date',
                '<=',
                $cutoff
            )
            ->get();
    }

    private function overdueTasks()
    {
        return ProjectTask::query()
            ->with([
                'assignee',
                'project.manager',
            ])
            ->whereNotIn('status', [
                TaskStatus::Completed->value,
                TaskStatus::Cancelled->value,
            ])
            ->whereNotNull('due_date')
            ->whereDate(
                'due_date',
                '<',
                today()
            )
            ->get();
    }

    private function duePaymentFollowups()
    {
        return PaymentFollowup::query()
            ->open()
            ->with([
                'project.client',
                'project.manager',
                'assignedTo',
            ])
            ->whereRaw(
                'COALESCE(next_followup_at, followup_at) <= ?',
                [now()]
            )
            ->get();
    }

    private function outstandingProjects()
    {
        return Project::query()
            ->with([
                'client',
                'manager',
            ])
            ->where(
                'pending_amount',
                '>',
                0
            )
            ->whereNotIn('status', [
                ProjectStatus::Cancelled->value,
            ])
            ->get();
    }

    private function payloadFor(
        NotificationRule $rule,
        Model $subject
    ): array {
        return match ($rule->event_key) {
            'project.deadline_approaching' => [
                'title' =>
                    'Project deadline approaching',

                'message' =>
                    "{$subject->name} is due on "
                    . $subject
                        ->expected_delivery_date
                        ->format('d M Y')
                    . '.',

                'url' => route(
                    'projects.show',
                    $subject
                ),

                'context' => [
                    'project_id' =>
                        $subject->id,

                    'deadline' =>
                        $subject
                            ->expected_delivery_date
                            ->toDateString(),
                ],
            ],

            'project.overdue' => [
                'title' =>
                    'Project is overdue',

                'message' =>
                    "{$subject->name} has passed its delivery deadline.",

                'url' => route(
                    'projects.show',
                    $subject
                ),

                'context' => [
                    'project_id' =>
                        $subject->id,

                    'deadline' =>
                        $subject
                            ->expected_delivery_date
                            ->toDateString(),
                ],
            ],

            'task.due_soon' => [
                'title' =>
                    'Task deadline approaching',

                'message' =>
                    "{$subject->title} is due on "
                    . $subject
                        ->due_date
                        ->format('d M Y')
                    . '.',

                'url' => route(
                    'projects.show',
                    [
                        'project' =>
                            $subject->project_id,

                        'tab' => 'tasks',
                    ]
                ),

                'context' => [
                    'project_id' =>
                        $subject->project_id,

                    'task_id' =>
                        $subject->id,
                ],
            ],

            'task.overdue' => [
                'title' => 'Task is overdue',

                'message' =>
                    "{$subject->title} has passed its due date.",

                'url' => route(
                    'projects.show',
                    [
                        'project' =>
                            $subject->project_id,

                        'tab' => 'tasks',
                    ]
                ),

                'context' => [
                    'project_id' =>
                        $subject->project_id,

                    'task_id' =>
                        $subject->id,
                ],
            ],

            'payment.followup_due' => [
                'title' =>
                    'Payment follow-up is due',

                'message' =>
                    "Collection follow-up is due for {$subject->project->name}.",

                'url' => route(
                    'projects.show',
                    [
                        'project' =>
                            $subject->project_id,

                        'tab' => 'payments',
                    ]
                ),

                'context' => [
                    'project_id' =>
                        $subject->project_id,

                    'payment_followup_id' =>
                        $subject->id,
                ],
            ],

            'payment.outstanding' => [
                'title' =>
                    'Outstanding client balance',

                'message' =>
                    "{$subject->name} has a pending client balance of ₹"
                    . number_format(
                        (float)
                        $subject->pending_amount,
                        2
                    )
                    . '.',

                'url' => route(
                    'projects.show',
                    [
                        'project' =>
                            $subject->id,

                        'tab' => 'payments',
                    ]
                ),

                'context' => [
                    'project_id' =>
                        $subject->id,

                    'pending_amount' =>
                        $subject
                            ->pending_amount,
                ],
            ],

            default => [
                'title' => $rule->name,

                'message' =>
                    $rule->description
                    ?: 'Action is required.',

                'url' => null,
                'context' => [],
            ],
        };
    }

    private function repeatBucket(
        NotificationRule $rule
    ): string {
        $seconds = max(
            60,
            $rule->repeat_minutes * 60
        );

        return $rule->rule_key
            . ':'
            . floor(
                now()->timestamp
                / $seconds
            );
    }

    private function maximumReached(
        NotificationRule $rule,
        Model $subject
    ): bool {
        $dispatchCount =
            NotificationDispatch::query()
                ->where(
                    'event_key',
                    $rule->event_key
                )
                ->where(
                    'subject_type',
                    $subject
                        ->getMorphClass()
                )
                ->where(
                    'subject_id',
                    $subject->getKey()
                )
                ->where(
                    'channel',
                    'database'
                )
                ->count();

        return $dispatchCount >=
            $rule->maximum_occurrences;
    }
}