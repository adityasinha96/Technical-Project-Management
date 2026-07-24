<?php

namespace App\Models;

use App\Concerns\LogsProjectActivity;
use App\Enums\ProjectPriority;
use App\Enums\TaskPhase;
use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectTask extends Model
{
    use LogsProjectActivity;
    use SoftDeletes;

    protected $fillable = [
        'project_id',
        'project_template_task_id',
        'assigned_to',
        'created_by',
        'title',
        'description',
        'phase',
        'priority',
        'status',
        'weight',
        'progress',
        'estimated_hours',
        'start_date',
        'due_date',
        'completed_at',
        'blocked_reason',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'phase' => TaskPhase::class,
            'priority' => ProjectPriority::class,
            'status' => TaskStatus::class,

            'weight' => 'decimal:2',
            'estimated_hours' => 'decimal:2',
            'progress' => 'integer',
            'sort_order' => 'integer',

            'start_date' => 'date',
            'due_date' => 'date',
            'completed_at' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function templateTask(): BelongsTo
    {
        return $this->belongsTo(
            ProjectTemplateTask::class,
            'project_template_task_id'
        );
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'assigned_to'
        );
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Project activity logging configuration
    |--------------------------------------------------------------------------
    */

    public function activityTrackedAttributes(): array
    {
        return [
            'title',
            'assigned_to',
            'phase',
            'priority',
            'status',
            'weight',
            'progress',
            'start_date',
            'due_date',
            'completed_at',
            'blocked_reason',
        ];
    }

    public function activityLabel(): string
    {
        return "Task: {$this->title}";
    }

    /*
    |--------------------------------------------------------------------------
    | Task helpers
    |--------------------------------------------------------------------------
    */

    public function getIsOverdueAttribute(): bool
    {
        if (
            !$this->due_date ||
            in_array(
                $this->status,
                [
                    TaskStatus::Completed,
                    TaskStatus::Cancelled,
                ],
                true
            )
        ) {
            return false;
        }

        return $this->due_date->isBefore(
            today()
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Query scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActive(
        Builder $query
    ): Builder {
        return $query->where(
            'status',
            '!=',
            TaskStatus::Cancelled->value
        );
    }
}