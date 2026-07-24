<?php

namespace App\Models;

use App\Concerns\LogsProjectActivity;
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
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory;
    use SoftDeletes;
    use LogsProjectActivity;

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

        /*
        |--------------------------------------------------------------------------
        | Expense and profitability summary fields
        |--------------------------------------------------------------------------
        */
        'project_expense_amount',
        'actual_profit_amount',
        'profit_margin_percentage',
        'cash_position_amount',

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

        /*
        |--------------------------------------------------------------------------
        | Phase 6 activity fields
        |--------------------------------------------------------------------------
        */
        'last_activity_at',

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

            /*
            |--------------------------------------------------------------------------
            | Expense and profitability summary casts
            |--------------------------------------------------------------------------
            */
            'project_expense_amount' => 'decimal:2',
            'actual_profit_amount' => 'decimal:2',
            'profit_margin_percentage' => 'decimal:2',
            'cash_position_amount' => 'decimal:2',

            'start_date' => 'date',
            'expected_delivery_date' => 'date',
            'revised_delivery_date' => 'date',
            'actual_completion_date' => 'date',
            'domain_expiry_date' => 'date',
            'hosting_expiry_date' => 'date',

            /*
            |--------------------------------------------------------------------------
            | Phase 6 activity casts
            |--------------------------------------------------------------------------
            */
            'last_activity_at' => 'datetime',

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
    | Expense relationships
    |--------------------------------------------------------------------------
    */

    public function expenses(): HasMany
    {
        return $this
            ->hasMany(Expense::class)
            ->latest('expense_date')
            ->latest('id');
    }

    /*
    |--------------------------------------------------------------------------
    | Phase 6 notes, work logs and activity relationships
    |--------------------------------------------------------------------------
    */

    public function notes(): HasMany
    {
        return $this
            ->hasMany(ProjectNote::class)
            ->latest('created_at');
    }

    public function workLogs(): HasMany
    {
        return $this
            ->hasMany(ProjectWorkLog::class)
            ->latest('work_date')
            ->latest('id');
    }

    public function activities(): HasMany
    {
        return $this
            ->hasMany(ProjectActivity::class)
            ->latest('occurred_at')
            ->latest('id');
    }

    public function fileLinks(): MorphMany
    {
        return $this->morphMany(
            ProjectFileLink::class,
            'fileable'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Phase 6 activity tracking configuration
    |--------------------------------------------------------------------------
    */

    public function activityProjectId(): ?int
    {
        return $this->id;
    }

    public function activityLabel(): string
    {
        return "Project: {$this->name}";
    }

    public function activityTrackedAttributes(): array
    {
        return [
            'name',
            'client_id',
            'project_category_id',
            'manager_id',
            'status',
            'priority',
            'project_price',
            'start_date',
            'expected_delivery_date',
            'official_progress',
            'internal_progress',
            'actual_completion_date',
        ];
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
    | Profitability helpers
    |--------------------------------------------------------------------------
    */

    public function getIsLossMakingAttribute(): bool
    {
        return (float) $this->actual_profit_amount < 0;
    }

    public function getIsCashNegativeAttribute(): bool
    {
        return (float) $this->cash_position_amount < 0;
    }

    public function getProfitHealthLabelAttribute(): string
    {
        if ($this->is_loss_making) {
            return 'Loss Making';
        }

        if ((float) $this->profit_margin_percentage < 10) {
            return 'Low Margin';
        }

        if ((float) $this->profit_margin_percentage < 25) {
            return 'Moderate Margin';
        }

        return 'Healthy Margin';
    }

    public function getProfitHealthClassesAttribute(): string
    {
        if ($this->is_loss_making) {
            return 'bg-red-50 text-red-700 ring-red-600/20';
        }

        if ((float) $this->profit_margin_percentage < 10) {
            return 'bg-orange-50 text-orange-700 ring-orange-600/20';
        }

        if ((float) $this->profit_margin_percentage < 25) {
            return 'bg-amber-50 text-amber-700 ring-amber-600/20';
        }

        return 'bg-emerald-50 text-emerald-700 ring-emerald-600/20';
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

    public function tickets(): HasMany
    {
        return $this
            ->hasMany(ProjectTicket::class)
            ->latest('created_at');
    }
}