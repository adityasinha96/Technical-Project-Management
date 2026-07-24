<?php

namespace App\Models;

use App\Concerns\LogsProjectActivity;
use App\Enums\ActivityVisibility;
use App\Enums\ProjectNoteType;
use App\Enums\ProjectNoteVisibility;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectNote extends Model
{
    use LogsProjectActivity;
    use SoftDeletes;

    protected $fillable = [
        'project_id',
        'title',
        'note_type',
        'visibility',
        'content',
        'is_pinned',
        'pinned_at',
        'pinned_by',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'note_type' => ProjectNoteType::class,
            'visibility' => ProjectNoteVisibility::class,
            'is_pinned' => 'boolean',
            'pinned_at' => 'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'updated_by'
        );
    }

    public function pinnedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'pinned_by'
        );
    }

    public function fileLinks(): MorphMany
    {
        return $this->morphMany(
            ProjectFileLink::class,
            'fileable'
        );
    }

    public function scopeVisibleTo(
        Builder $query,
        User $user
    ): Builder {
        if ($user->hasRole('super-admin')) {
            return $query;
        }

        return $query->where(
            function (Builder $query) use ($user): void {
                $query->where(
                    'visibility',
                    ProjectNoteVisibility::Team->value
                );

                if ($user->can('notes.view-sensitive')) {
                    $query->orWhere(
                        'visibility',
                        ProjectNoteVisibility::Management->value
                    );
                }

                $query->orWhere(
                    function (Builder $query) use ($user): void {
                        $query
                            ->where(
                                'visibility',
                                ProjectNoteVisibility::Private->value
                            )
                            ->where(
                                'created_by',
                                $user->id
                            );
                    }
                );
            }
        );
    }

    public function isVisibleTo(User $user): bool
    {
        if ($user->hasRole('super-admin')) {
            return true;
        }

        return match ($this->visibility) {
            ProjectNoteVisibility::Team =>
                $user->can('notes.view'),

            ProjectNoteVisibility::Management =>
                $user->can('notes.view-sensitive'),

            ProjectNoteVisibility::Private =>
                $this->created_by === $user->id,
        };
    }

    public function canBeManagedBy(User $user): bool
    {
        return $user->hasRole('super-admin')
            || $user->can('notes.manage')
            || $this->created_by === $user->id;
    }

    public function activityTrackedAttributes(): array
    {
        return [
            'title',
            'note_type',
            'visibility',
            'is_pinned',
        ];
    }

    public function activityLabel(): string
    {
        return 'Project note'
            . ($this->title ? ": {$this->title}" : '');
    }

    public function activityVisibility(): ActivityVisibility
    {
        return match ($this->visibility) {
            ProjectNoteVisibility::Team =>
                ActivityVisibility::Team,

            ProjectNoteVisibility::Management =>
                ActivityVisibility::Management,

            ProjectNoteVisibility::Private =>
                ActivityVisibility::Private,
        };
    }

    public function activityVisibleToUserId(): ?int
    {
        return $this->visibility ===
            ProjectNoteVisibility::Private
                ? $this->created_by
                : null;
    }
}