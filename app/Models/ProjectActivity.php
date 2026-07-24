<?php

namespace App\Models;

use App\Enums\ActivityVisibility;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ProjectActivity extends Model
{
    protected $fillable = [
        'project_id',
        'actor_id',
        'event',
        'visibility',
        'visible_to_user_id',
        'subject_type',
        'subject_id',
        'title',
        'description',
        'old_values',
        'new_values',
        'metadata',
        'occurred_at',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'visibility' => ActivityVisibility::class,
            'old_values' => 'array',
            'new_values' => 'array',
            'metadata' => 'array',
            'occurred_at' => 'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'actor_id'
        );
    }

    public function visibleToUser(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'visible_to_user_id'
        );
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
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
                    ActivityVisibility::Team->value
                );

                if ($user->can('activities.view-sensitive')) {
                    $query->orWhere(
                        'visibility',
                        ActivityVisibility::Management->value
                    );
                }

                if (
                    $user->can('payments.view') ||
                    $user->can('expenses.view')
                ) {
                    $query->orWhere(
                        'visibility',
                        ActivityVisibility::Financial->value
                    );
                }

                $query->orWhere(
                    function (Builder $query) use ($user): void {
                        $query
                            ->where(
                                'visibility',
                                ActivityVisibility::Private->value
                            )
                            ->where(
                                'visible_to_user_id',
                                $user->id
                            );
                    }
                );
            }
        );
    }

    public function getBadgeClassesAttribute(): string
    {
        return match ($this->event) {
            'created',
            'note_created',
            'work_logged',
            'attachment_uploaded',
            'history_initialized',
            'ticket_created',
            'ticket_comment_added',
            'ticket_first_response' =>
                'bg-emerald-50 text-emerald-700',

            'updated',
            'status_changed',
            'ticket_updated',
            'ticket_assigned',
            'ticket_status_changed',
            'ticket_comment_updated' =>
                'bg-blue-50 text-blue-700',

            'pinned' =>
                'bg-amber-50 text-amber-700',

            'unpinned' =>
                'bg-slate-100 text-slate-700',

            'ticket_resolved' =>
                'bg-emerald-100 text-emerald-800',

            'ticket_reopened',
            'attachment_downloaded' =>
                'bg-violet-50 text-violet-700',

            'ticket_escalated' =>
                'bg-red-600 text-white',

            'deleted',
            'attachment_deleted',
            'ticket_comment_deleted' =>
                'bg-red-50 text-red-700',

            default =>
                'bg-slate-100 text-slate-700',
        };
    }

    public function getEventLabelAttribute(): string
    {
        return str($this->event)
            ->replace('_', ' ')
            ->title()
            ->toString();
    }
}