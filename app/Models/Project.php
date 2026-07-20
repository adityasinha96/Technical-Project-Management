<?php

namespace App\Models;

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
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'project_price' => 'decimal:2',
            'estimated_cost' => 'decimal:2',

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
        ];
    }

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
        if (!$this->deadline || $this->status->isClosed()) {
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
            return abs($this->days_remaining) . ' day(s) delayed';
        }

        if ($this->days_remaining === 0) {
            return 'Due today';
        }

        return $this->days_remaining . ' day(s) remaining';
    }

    public function scopeSearch(
        Builder $query,
        ?string $search
    ): Builder {
        return $query->when(
            filled($search),
            function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('project_code', 'like', "%{$search}%")
                        ->orWhereHas(
                            'client',
                            function (Builder $query) use ($search): void {
                                $query
                                    ->where('name', 'like', "%{$search}%")
                                    ->orWhere(
                                        'company_name',
                                        'like',
                                        "%{$search}%"
                                    );
                            }
                        );
                });
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
}