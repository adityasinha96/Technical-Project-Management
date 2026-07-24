<?php

namespace App\Services\Projects;

use App\Enums\ActivityVisibility;
use App\Models\Project;
use App\Models\ProjectActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ProjectActivityService
{
    public function logModelEvent(
        Model $subject,
        string $event,
        array $oldValues = [],
        array $newValues = []
    ): ?ProjectActivity {
        if (!method_exists(
            $subject,
            'activityProjectId'
        )) {
            return null;
        }

        $projectId =
            $subject->activityProjectId();

        if (!$projectId) {
            return null;
        }

        $visibility = method_exists(
            $subject,
            'activityVisibility'
        )
            ? $subject->activityVisibility()
            : ActivityVisibility::Team;

        $visibleToUserId = method_exists(
            $subject,
            'activityVisibleToUserId'
        )
            ? $subject->activityVisibleToUserId()
            : null;

        $label = method_exists(
            $subject,
            'activityLabel'
        )
            ? $subject->activityLabel()
            : Str::headline(
                class_basename($subject)
            );

        return $this->create([
            'project_id' => $projectId,
            'actor_id' => auth()->id(),
            'event' => $event,
            'visibility' => $visibility->value,

            'visible_to_user_id' =>
                $visibleToUserId,

            'subject_type' =>
                $subject->getMorphClass(),

            'subject_id' =>
                $subject->getKey(),

            'title' =>
                "{$label} {$this->verb($event)}",

            'old_values' =>
                $oldValues ?: null,

            'new_values' =>
                $newValues ?: null,
        ]);
    }

    public function logCustom(
        Project $project,
        string $event,
        string $title,
        ?string $description = null,
        ?Model $subject = null,
        array $oldValues = [],
        array $newValues = [],
        array $metadata = [],
        ActivityVisibility $visibility =
            ActivityVisibility::Team,
        ?int $visibleToUserId = null,
        ?int $actorId = null
    ): ProjectActivity {
        return $this->create([
            'project_id' => $project->id,

            'actor_id' =>
                $actorId ?? auth()->id(),

            'event' => $event,

            'visibility' =>
                $visibility->value,

            'visible_to_user_id' =>
                $visibleToUserId,

            'subject_type' =>
                $subject?->getMorphClass(),

            'subject_id' =>
                $subject?->getKey(),

            'title' => $title,
            'description' => $description,

            'old_values' =>
                $oldValues ?: null,

            'new_values' =>
                $newValues ?: null,

            'metadata' =>
                $metadata ?: null,
        ]);
    }

    private function create(
        array $data
    ): ProjectActivity {
        $activity = ProjectActivity::create([
            ...$data,

            'occurred_at' => now(),

            'ip_address' =>
                app()->runningInConsole()
                    ? null
                    : request()->ip(),

            'user_agent' =>
                app()->runningInConsole()
                    ? null
                    : request()->userAgent(),
        ]);

        Project::query()
            ->whereKey($activity->project_id)
            ->update([
                'last_activity_at' =>
                    $activity->occurred_at,
            ]);

        return $activity;
    }

    private function verb(string $event): string
    {
        return match ($event) {
            'created' => 'created',
            'updated' => 'updated',
            'deleted' => 'deleted',
            'restored' => 'restored',
            default => str($event)
                ->replace('_', ' ')
                ->toString(),
        };
    }
}