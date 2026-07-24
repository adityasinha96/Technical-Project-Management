<?php

namespace App\Models;

use App\Concerns\LogsProjectActivity;
use App\Enums\WorkLogStatus;
use App\Enums\WorkLogType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectWorkLog extends Model
{
    use LogsProjectActivity;
    use SoftDeletes;

    protected $fillable = [
        'project_id',
        'project_task_id',
        'logged_by',
        'work_date',
        'work_type',
        'status',
        'title',
        'details',
        'outcome',
        'blocker',
        'duration_minutes',
        'is_billable',
    ];

    protected function casts(): array
    {
        return [
            'work_date' => 'date',
            'work_type' => WorkLogType::class,
            'status' => WorkLogStatus::class,
            'duration_minutes' => 'integer',
            'is_billable' => 'boolean',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(
            ProjectTask::class,
            'project_task_id'
        );
    }

    public function loggedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'logged_by'
        );
    }

    public function fileLinks(): MorphMany
    {
        return $this->morphMany(
            ProjectFileLink::class,
            'fileable'
        );
    }

    public function canBeManagedBy(User $user): bool
    {
        return $user->hasRole('super-admin')
            || $user->can('work-logs.manage')
            || $this->logged_by === $user->id;
    }

    public function getFormattedDurationAttribute(): string
    {
        $hours = intdiv(
            $this->duration_minutes,
            60
        );

        $minutes = $this->duration_minutes % 60;

        if ($hours === 0) {
            return "{$minutes} min";
        }

        if ($minutes === 0) {
            return "{$hours} hr";
        }

        return "{$hours} hr {$minutes} min";
    }

    public function activityTrackedAttributes(): array
    {
        return [
            'project_task_id',
            'work_date',
            'work_type',
            'status',
            'title',
            'duration_minutes',
            'is_billable',
        ];
    }

    public function activityLabel(): string
    {
        return "Work log: {$this->title}";
    }
}