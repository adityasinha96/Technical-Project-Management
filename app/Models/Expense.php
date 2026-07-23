<?php

namespace App\Models;

use App\Enums\ExpenseScope;
use App\Enums\ExpenseStatus;
use App\Enums\PaymentMode;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Expense extends Model
{
    protected $fillable = [
        'expense_number',
        'scope',
        'project_id',
        'expense_category_id',
        'status',
        'amount',
        'tax_amount',
        'expense_date',
        'due_date',
        'paid_at',
        'payment_mode',
        'vendor_name',
        'bill_number',
        'transaction_reference',
        'description',
        'internal_notes',

        'receipt_original_name',
        'receipt_stored_name',
        'receipt_path',
        'receipt_disk',
        'receipt_mime_type',
        'receipt_size',

        'created_by',
        'voided_by',
        'voided_at',
        'void_reason',
    ];

    protected function casts(): array
    {
        return [
            'scope' => ExpenseScope::class,
            'status' => ExpenseStatus::class,
            'payment_mode' => PaymentMode::class,

            'amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',

            'expense_date' => 'date',
            'due_date' => 'date',
            'paid_at' => 'date',

            'receipt_size' => 'integer',
            'voided_at' => 'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(
            ExpenseCategory::class,
            'expense_category_id'
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

    public function scopeEffective(Builder $query): Builder
    {
        return $query
            ->where(
                'status',
                ExpenseStatus::Paid->value
            )
            ->whereNull('voided_at');
    }

    public function scopeProjectExpenses(
        Builder $query
    ): Builder {
        return $query->where(
            'scope',
            ExpenseScope::Project->value
        );
    }

    public function scopeBusinessExpenses(
        Builder $query
    ): Builder {
        return $query->where(
            'scope',
            ExpenseScope::Business->value
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
                                'expense_number',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'vendor_name',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'bill_number',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'transaction_reference',
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
                                'category',
                                fn (Builder $query) =>
                                    $query->where(
                                        'name',
                                        'like',
                                        "%{$search}%"
                                    )
                            );
                    }
                );
            }
        );
    }

    public function getIsVoidedAttribute(): bool
    {
        return $this->voided_at !== null;
    }

    public function getDisplayStatusAttribute(): string
    {
        return $this->is_voided
            ? 'Voided'
            : $this->status->label();
    }

    public function getReceiptUrlAttribute(): ?string
    {
        if (!$this->receipt_path) {
            return null;
        }

        return Storage::disk(
            $this->receipt_disk
        )->url($this->receipt_path);
    }

    public function getFormattedReceiptSizeAttribute(): ?string
    {
        if (!$this->receipt_size) {
            return null;
        }

        if ($this->receipt_size >= 1_048_576) {
            return round(
                $this->receipt_size / 1_048_576,
                2
            ) . ' MB';
        }

        if ($this->receipt_size >= 1_024) {
            return round(
                $this->receipt_size / 1_024,
                2
            ) . ' KB';
        }

        return $this->receipt_size . ' Bytes';
    }
}