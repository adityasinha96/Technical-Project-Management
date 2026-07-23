<?php

namespace App\Models;

use App\Enums\ExpenseCategoryScope;
use App\Enums\ExpenseScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExpenseCategory extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'scope',
        'description',
        'is_active',
        'sort_order',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'scope' => ExpenseCategoryScope::class,
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
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
            ->orderBy('sort_order')
            ->orderBy('name');
    }

    public function scopeForExpenseScope(
        Builder $query,
        ExpenseScope $scope
    ): Builder {
        return $query->whereIn(
            'scope',
            [
                $scope->value,
                ExpenseCategoryScope::Both->value,
            ]
        );
    }
}