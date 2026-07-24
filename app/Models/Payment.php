<?php

namespace App\Models;

use App\Concerns\LogsProjectActivity;
use App\Enums\ActivityVisibility;
use App\Enums\PaymentKind;
use App\Enums\PaymentMode;
use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use LogsProjectActivity;

    protected $fillable = [
        'payment_number',
        'project_id',
        'client_id',
        'kind',
        'payment_type',
        'payment_mode',
        'status',
        'amount',
        'payment_date',
        'expected_clearance_date',
        'cleared_at',
        'received_from',
        'bank_name',
        'transaction_reference',
        'invoice_number',
        'remarks',
        'proof_file_id',
        'created_by',
        'voided_by',
        'voided_at',
        'void_reason',
    ];

    protected function casts(): array
    {
        return [
            'kind' => PaymentKind::class,
            'payment_type' => PaymentType::class,
            'payment_mode' => PaymentMode::class,
            'status' => PaymentStatus::class,

            'amount' => 'decimal:2',

            'payment_date' => 'date',
            'expected_clearance_date' => 'date',
            'cleared_at' => 'date',

            'voided_at' => 'datetime',
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

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function proofFile(): BelongsTo
    {
        return $this->belongsTo(
            ProjectFile::class,
            'proof_file_id'
        );
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function voidedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'voided_by'
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
            'kind',
            'payment_type',
            'payment_mode',
            'status',
            'amount',
            'payment_date',
            'transaction_reference',
            'voided_at',
            'void_reason',
        ];
    }

    public function activityLabel(): string
    {
        return "Payment: {$this->payment_number}";
    }

    public function activityVisibility(): ActivityVisibility
    {
        return ActivityVisibility::Financial;
    }

    /*
    |--------------------------------------------------------------------------
    | Query scopes
    |--------------------------------------------------------------------------
    */

    public function scopeEffective(
        Builder $query
    ): Builder {
        return $query
            ->where(
                'status',
                PaymentStatus::Cleared->value
            )
            ->whereNull('voided_at');
    }

    public function scopeReceipts(
        Builder $query
    ): Builder {
        return $query->where(
            'kind',
            PaymentKind::Receipt->value
        );
    }

    public function scopeRefunds(
        Builder $query
    ): Builder {
        return $query->where(
            'kind',
            PaymentKind::Refund->value
        );
    }

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
                                'payment_number',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'transaction_reference',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'invoice_number',
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

    /*
    |--------------------------------------------------------------------------
    | Payment helpers
    |--------------------------------------------------------------------------
    */

    public function getIsVoidedAttribute(): bool
    {
        return $this->voided_at !== null;
    }

    public function getSignedAmountAttribute(): float
    {
        return round(
            (float) $this->amount *
            $this->kind->multiplier(),
            2
        );
    }

    public function getDisplayStatusAttribute(): string
    {
        return $this->is_voided
            ? 'Voided'
            : $this->status->label();
    }
}