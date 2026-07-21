<?php

namespace App\Models;

use App\Enums\ProjectPriority;
use App\Enums\TaskPhase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectTemplateTask extends Model
{
    protected $fillable = [
        'project_template_id',
        'title',
        'description',
        'phase',
        'priority',
        'weight',
        'estimated_hours',
        'default_duration_days',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'phase' => TaskPhase::class,
            'priority' => ProjectPriority::class,
            'weight' => 'decimal:2',
            'estimated_hours' => 'decimal:2',
            'default_duration_days' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(
            ProjectTemplate::class,
            'project_template_id'
        );
    }

    public function projectTasks()
    {
        return $this->hasMany(ProjectTask::class);
    }
}