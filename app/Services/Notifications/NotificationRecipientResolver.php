<?php

namespace App\Services\Notifications;

use App\Enums\NotificationRecipientStrategy;
use App\Models\NotificationRule;
use App\Models\PaymentFollowup;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\ProjectTicket;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class NotificationRecipientResolver
{
    public function resolve(
        NotificationRule $rule,
        Model $subject
    ): Collection {
        return match (
            $rule->recipient_strategy
        ) {
            NotificationRecipientStrategy::ProjectManager =>
                $this->projectManager(
                    $this->projectFrom(
                        $subject
                    )
                ),

            NotificationRecipientStrategy::ProjectTeam =>
                $this->projectTeam(
                    $this->projectFrom(
                        $subject
                    )
                ),

            NotificationRecipientStrategy::TaskAssignee =>
                $subject instanceof ProjectTask
                    ? $this->taskRecipients(
                        $subject
                    )
                    : collect(),

            NotificationRecipientStrategy::AssignedUser =>
                $this->assignedUser(
                    $subject
                ),

            NotificationRecipientStrategy::Accounts =>
                $this->accounts(),

            NotificationRecipientStrategy::Management =>
                $this->management(),

            NotificationRecipientStrategy::ManagerAndAccounts =>
                $this->projectManager(
                    $this->projectFrom(
                        $subject
                    )
                )
                    ->merge(
                        $this->accounts()
                    )
                    ->unique('id')
                    ->values(),
        };
    }

    public function projectManager(
        ?Project $project
    ): Collection {
        return collect([
            $project?->manager,
        ])
            ->filter()
            ->unique('id')
            ->values();
    }

    public function projectTeam(
        ?Project $project
    ): Collection {
        if (!$project) {
            return collect();
        }

        $project->loadMissing(
            'manager',
            'members'
        );

        return collect([
            $project->manager,
        ])
            ->merge($project->members)
            ->filter()
            ->unique('id')
            ->values();
    }

    public function taskRecipients(
        ProjectTask $task
    ): Collection {
        $task->loadMissing(
            'assignee',
            'project.manager'
        );

        return collect([
            $task->assignee,
            $task->project?->manager,
        ])
            ->filter()
            ->unique('id')
            ->values();
    }

    public function ticketRecipients(
        ProjectTicket $ticket
    ): Collection {
        $ticket->loadMissing(
            'assignedTo',
            'project.manager',
            'createdBy'
        );

        return collect([
            $ticket->assignedTo,
            $ticket->project?->manager,
            $ticket->createdBy,
        ])
            ->filter()
            ->unique('id')
            ->values();
    }

    public function accounts(): Collection
    {
        return User::query()
            ->where('status', 'active')
            ->role('accounts')
            ->get();
    }

    public function management(): Collection
    {
        return User::query()
            ->where('status', 'active')
            ->role([
                'super-admin',
                'project-manager',
            ])
            ->get();
    }

    private function assignedUser(
        Model $subject
    ): Collection {
        $user = match (true) {
            $subject instanceof ProjectTask =>
                $subject->assignee,

            $subject instanceof ProjectTicket =>
                $subject->assignedTo,

            $subject instanceof PaymentFollowup =>
                $subject->assignedTo,

            default => null,
        };

        return collect([$user])
            ->filter()
            ->unique('id')
            ->values();
    }

    private function projectFrom(
        Model $subject
    ): ?Project {
        if ($subject instanceof Project) {
            return $subject;
        }

        if (
            method_exists(
                $subject,
                'project'
            )
        ) {
            return $subject->project;
        }

        return null;
    }
}