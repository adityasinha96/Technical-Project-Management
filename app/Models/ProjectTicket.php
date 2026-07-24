<?php

namespace App\Models;

use App\Enums\TicketPriority;
use App\Enums\TicketSource;
use App\Enums\TicketStatus;
use App\Enums\TicketType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectTicket extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'ticket_number',
        'project_id',
        'client_id',
        'type',
        'source',
        'priority',
        'status',
        'subject',
        'description',

        'assigned_to',
        'assigned_by',
        'assigned_at',

        'first_response_due_at',
        'resolution_due_at',
        'first_responded_at',
        'first_responded_by',
        'response_breached_at',
        'resolution_breached_at',

        'sla_paused_at',
        'sla_paused_minutes',

        'escalation_level',
        'escalated_at',

        'last_reply_at',
        'last_reply_by',
        'last_activity_at',

        'resolved_at',
        'resolved_by',
        'resolution_summary',
        'root_cause',
        'preventive_action',

        'closed_at',
        'closed_by',

        'reopen_count',
        'reopened_at',
        'reopened_by',
        'reopen_reason',

        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'type' => TicketType::class,
            'source' => TicketSource::class,
            'priority' => TicketPriority::class,
            'status' => TicketStatus::class,

            'assigned_at' => 'datetime',

            'first_response_due_at' => 'datetime',
            'resolution_due_at' => 'datetime',
            'first_responded_at' => 'datetime',
            'response_breached_at' => 'datetime',
            'resolution_breached_at' => 'datetime',

            'sla_paused_at' => 'datetime',

            'sla_paused_minutes' => 'integer',
            'escalation_level' => 'integer',

            'escalated_at' => 'datetime',
            'last_reply_at' => 'datetime',
            'last_activity_at' => 'datetime',

            'resolved_at' => 'datetime',
            'closed_at' => 'datetime',
            'reopened_at' => 'datetime',
            'reopen_count' => 'integer',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'assigned_to'
        );
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'assigned_by'
        );
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

    public function firstRespondedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'first_responded_by'
        );
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'resolved_by'
        );
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'closed_by'
        );
    }

    public function reopenedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'reopened_by'
        );
    }

    public function comments(): HasMany
    {
        return $this
            ->hasMany(
                TicketComment::class,
                'project_ticket_id'
            )
            ->oldest('created_at');
    }

    public function statusHistories(): HasMany
    {
        return $this
            ->hasMany(
                TicketStatusHistory::class,
                'project_ticket_id'
            )
            ->latest('changed_at');
    }

    public function escalations(): HasMany
    {
        return $this
            ->hasMany(
                TicketEscalation::class,
                'project_ticket_id'
            )
            ->latest('triggered_at');
    }

    public function fileLinks(): MorphMany
    {
        return $this->morphMany(
            ProjectFileLink::class,
            'fileable'
        );
    }

    public function scopeOpen(
        Builder $query
    ): Builder {
        return $query->whereIn(
            'status',
            TicketStatus::activeValues()
        );
    }

    public function scopeSearch(
        Builder $query,
        ?string $search
    ): Builder {
        return $query->when(
            filled($search),
            function (
                Builder $query
            ) use ($search): void {
                $query->where(
                    function (
                        Builder $query
                    ) use ($search): void {
                        $query
                            ->where(
                                'ticket_number',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'subject',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'description',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhereHas(
                                'project',
                                fn (Builder $query) =>
                                    $query->where(
                                        'name',
                                        'like',
                                        "%{$search}%"
                                    )
                            )
                            ->orWhereHas(
                                'client',
                                function (
                                    Builder $query
                                ) use ($search): void {
                                    $query
                                        ->where(
                                            'name',
                                            'like',
                                            "%{$search}%"
                                        )
                                        ->orWhere(
                                            'company_name',
                                            'like',
                                            "%{$search}%"
                                        );
                                }
                            );
                    }
                );
            }
        );
    }

    public function getCurrentSlaDueAtAttribute()
    {
        if ($this->status->isCompleted()) {
            return null;
        }

        return $this->first_responded_at
            ? $this->resolution_due_at
            : $this->first_response_due_at;
    }

    public function getIsResponseOverdueAttribute(): bool
    {
        return !$this->first_responded_at
            && !$this->status->isCompleted()
            && !$this->status->pausesSla()
            && $this->first_response_due_at
            && now()->greaterThan(
                $this->first_response_due_at
            );
    }

    public function getIsResolutionOverdueAttribute(): bool
    {
        return !$this->status->isCompleted()
            && !$this->status->pausesSla()
            && $this->resolution_due_at
            && now()->greaterThan(
                $this->resolution_due_at
            );
    }

    public function getSlaStateAttribute(): string
    {
        if ($this->status->isCompleted()) {
            return 'completed';
        }

        if ($this->status->pausesSla()) {
            return 'paused';
        }

        if (
            $this->is_response_overdue ||
            $this->is_resolution_overdue
        ) {
            return 'breached';
        }

        if ($this->escalation_level > 0) {
            return 'warning';
        }

        return 'on_track';
    }

    public function getSlaBadgeClassesAttribute(): string
    {
        return match ($this->sla_state) {
            'completed' =>
                'bg-emerald-50 text-emerald-700',

            'paused' =>
                'bg-slate-100 text-slate-700',

            'breached' =>
                'bg-red-600 text-white',

            'warning' =>
                'bg-amber-50 text-amber-700',

            default =>
                'bg-blue-50 text-blue-700',
        };
    }

    public function getSlaLabelAttribute(): string
    {
        return match ($this->sla_state) {
            'completed' => 'SLA Completed',
            'paused' => 'SLA Paused',
            'breached' => 'SLA Breached',
            'warning' => 'SLA At Risk',
            default => 'SLA On Track',
        };
    }

    public function canBeManagedBy(
        User $user
    ): bool {
        return $user->hasRole('super-admin')
            || $user->can('tickets.manage-all')
            || $this->assigned_to === $user->id
            || $this->project?->manager_id ===
                $user->id;
    }
}