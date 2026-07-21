<?php

namespace App\Models;

use App\Enums\ApprovalStage;
use App\Enums\ApprovalStatus;
use App\Enums\ProjectPriority;
use App\Enums\ProjectStatus;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'project_code',
        'client_id',
        'project_category_id',
        'manager_id',
        'name',
        'description',

        'project_price',
        'estimated_cost',
        'currency',

        /*
        |--------------------------------------------------------------------------
        | Payment summary fields
        |--------------------------------------------------------------------------
        */
        'net_received_amount',
        'pending_amount',
        'collection_percentage',
        'last_payment_date',

        'start_date',
        'expected_delivery_date',
        'revised_delivery_date',
        'actual_completion_date',
        'maximum_duration_days',

        'status',
        'priority',
        'official_progress',

        'payment_terms',

        'reference_url',
        'development_url',
        'live_url',

        'domain_name',
        'hosting_provider',
        'domain_expiry_date',
        'hosting_expiry_date',

        'internal_remarks',
        'project_template_id',
        'internal_progress',

        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'project_price' => 'decimal:2',
            'estimated_cost' => 'decimal:2',

            /*
            |--------------------------------------------------------------------------
            | Payment summary casts
            |--------------------------------------------------------------------------
            */
            'net_received_amount' => 'decimal:2',
            'pending_amount' => 'decimal:2',
            'collection_percentage' => 'decimal:2',
            'last_payment_date' => 'date',

            'start_date' => 'date',
            'expected_delivery_date' => 'date',
            'revised_delivery_date' => 'date',
            'actual_completion_date' => 'date',
            'domain_expiry_date' => 'date',
            'hosting_expiry_date' => 'date',

            'status' => ProjectStatus::class,
            'priority' => ProjectPriority::class,

            'official_progress' => 'integer',
            'maximum_duration_days' => 'integer',
            'internal_progress' => 'integer',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Client and project ownership relationships
    |--------------------------------------------------------------------------
    */

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(
            ProjectCategory::class,
            'project_category_id'
        );
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'manager_id'
        );
    }

    public function team(): BelongsToMany
    {
        return $this
            ->belongsToMany(User::class)
            ->withPivot([
                'assignment_role',
                'assigned_by',
                'assigned_at',
            ])
            ->withTimestamps();
    }

    public function files(): HasMany
    {
        return $this->hasMany(ProjectFile::class);
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

    /*
    |--------------------------------------------------------------------------
    | Phase 4 payment relationships
    |--------------------------------------------------------------------------
    */

    public function payments(): HasMany
    {
        return $this
            ->hasMany(Payment::class)
            ->latest('payment_date')
            ->latest('id');
    }

    public function paymentFollowups(): HasMany
    {
        return $this
            ->hasMany(PaymentFollowup::class)
            ->latest('followup_at');
    }

    /*
    |--------------------------------------------------------------------------
    | Financial accessors
    |--------------------------------------------------------------------------
    */

    protected function expectedProfit(): Attribute
    {
        return Attribute::make(
            get: fn (): float =>
                (float) $this->project_price -
                (float) $this->estimated_cost
        );
    }

    protected function expectedProfitPercentage(): Attribute
    {
        return Attribute::make(
            get: function (): float {
                if ((float) $this->project_price <= 0) {
                    return 0;
                }

                return round(
                    (
                        (
                            (float) $this->project_price -
                            (float) $this->estimated_cost
                        ) /
                        (float) $this->project_price
                    ) * 100,
                    2
                );
            }
        );
    }

    public function getOverpaidAmountAttribute(): float
    {
        return max(
            0,
            round(
                (float) $this->net_received_amount -
                (float) $this->project_price,
                2
            )
        );
    }

    public function getCollectionBarPercentageAttribute(): float
    {
        return min(
            100,
            max(
                0,
                (float) $this->collection_percentage
            )
        );
    }

    public function getIsFullyPaidAttribute(): bool
    {
        return (float) $this->pending_amount <= 0;
    }

    /*
    |--------------------------------------------------------------------------
    | Deadline accessors
    |--------------------------------------------------------------------------
    */

    public function getDeadlineAttribute(): ?CarbonInterface
    {
        return $this->revised_delivery_date
            ?: $this->expected_delivery_date;
    }

    public function getDaysRemainingAttribute(): ?int
    {
        if (!$this->deadline) {
            return null;
        }

        return (int) today()->diffInDays(
            $this->deadline,
            false
        );
    }

    public function getIsDelayedAttribute(): bool
    {
        if (
            !$this->deadline ||
            $this->status->isClosed()
        ) {
            return false;
        }

        return $this->deadline->isBefore(today());
    }

    public function getDeadlineLabelAttribute(): string
    {
        if ($this->status === ProjectStatus::Completed) {
            return 'Completed';
        }

        if ($this->status === ProjectStatus::Cancelled) {
            return 'Cancelled';
        }

        if ($this->days_remaining === null) {
            return 'No deadline';
        }

        if ($this->days_remaining < 0) {
            return abs($this->days_remaining) .
                ' day(s) delayed';
        }

        if ($this->days_remaining === 0) {
            return 'Due today';
        }

        return $this->days_remaining .
            ' day(s) remaining';
    }

    /*
    |--------------------------------------------------------------------------
    | Query scopes
    |--------------------------------------------------------------------------
    */

    public function scopeSearch(
        Builder $query,
        ?string $search
    ): Builder {
        return $query->when(
            filled($search),
            function (Builder $query) use ($search): void {
                $query->where(
                    function (Builder $query) use ($search): void {
                        $query
                            ->where(
                                'name',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'project_code',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhereHas(
                                'client',
                                function (Builder $query) use ($search): void {
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

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereNotIn(
            'status',
            ProjectStatus::closedValues()
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Template and workflow relationships
    |--------------------------------------------------------------------------
    */

    public function template(): BelongsTo
    {
        return $this->belongsTo(
            ProjectTemplate::class,
            'project_template_id'
        );
    }

    public function tasks(): HasMany
    {
        return $this
            ->hasMany(ProjectTask::class)
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function approvals(): HasMany
    {
        return $this
            ->hasMany(ProjectApproval::class)
            ->latest('submitted_at');
    }

    /*
    |--------------------------------------------------------------------------
    | Approval helpers
    |--------------------------------------------------------------------------
    */

    public function hasApprovedStage(
        ApprovalStage $stage
    ): bool {
        if ($this->relationLoaded('approvals')) {
            return $this->approvals->contains(
                fn (ProjectApproval $approval): bool =>
                    $approval->stage === $stage &&
                    $approval->status === ApprovalStatus::Approved
            );
        }

        return $this
            ->approvals()
            ->where(
                'stage',
                $stage->value
            )
            ->where(
                'status',
                ApprovalStatus::Approved->value
            )
            ->exists();
    }

    public function hasPendingApproval(
        ApprovalStage $stage
    ): bool {
        if ($this->relationLoaded('approvals')) {
            return $this->approvals->contains(
                fn (ProjectApproval $approval): bool =>
                    $approval->stage === $stage &&
                    $approval->status === ApprovalStatus::Submitted
            );
        }

        return $this
            ->approvals()
            ->where(
                'stage',
                $stage->value
            )
            ->where(
                'status',
                ApprovalStatus::Submitted->value
            )
            ->exists();
    }

    public function latestApproval(
        ApprovalStage $stage
    ): ?ProjectApproval {
        if ($this->relationLoaded('approvals')) {
            return $this->approvals
                ->where('stage', $stage)
                ->sortByDesc('submitted_at')
                ->first();
        }

        return $this
            ->approvals()
            ->where(
                'stage',
                $stage->value
            )
            ->latest('submitted_at')
            ->first();
    }
}