<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectTemplate extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'project_category_id',
        'name',
        'slug',
        'description',
        'default_duration_days',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'default_duration_days' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(
            ProjectCategory::class,
            'project_category_id'
        );
    }

    public function tasks(): HasMany
    {
        return $this
            ->hasMany(ProjectTemplateTask::class)
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->orderBy('name');
    }
}