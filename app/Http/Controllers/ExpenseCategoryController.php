<?php

namespace App\Http\Controllers;

use App\Enums\ExpenseCategoryScope;
use App\Http\Requests\StoreExpenseCategoryRequest;
use App\Http\Requests\UpdateExpenseCategoryRequest;
use App\Models\ExpenseCategory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class ExpenseCategoryController extends Controller
{
    public function index(): View
    {
        $categories = ExpenseCategory::query()
            ->withCount('expenses')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(20);

        return view(
            'expense-categories.index',
            [
                'categories' => $categories,
                'scopes' =>
                    ExpenseCategoryScope::cases(),
            ]
        );
    }

    public function store(
        StoreExpenseCategoryRequest $request
    ): RedirectResponse {
        $validated = $request->validated();

        ExpenseCategory::create([
            ...$validated,

            'slug' => $this->uniqueSlug(
                $validated['name']
            ),

            'created_by' =>
                $request->user()->id,
        ]);

        return back()->with(
            'success',
            'Expense category created successfully.'
        );
    }

    public function update(
        UpdateExpenseCategoryRequest $request,
        ExpenseCategory $expenseCategory
    ): RedirectResponse {
        $validated = $request->validated();

        $expenseCategory->update([
            ...$validated,

            'slug' => $this->uniqueSlug(
                $validated['name'],
                $expenseCategory->id
            ),
        ]);

        return back()->with(
            'success',
            'Expense category updated successfully.'
        );
    }

    public function destroy(
        ExpenseCategory $expenseCategory
    ): RedirectResponse {
        if ($expenseCategory->expenses()->exists()) {
            $expenseCategory->update([
                'is_active' => false,
            ]);

            return back()->with(
                'success',
                'Category disabled because expense records are attached to it.'
            );
        }

        $expenseCategory->delete();

        return back()->with(
            'success',
            'Expense category archived.'
        );
    }

    private function uniqueSlug(
        string $name,
        ?int $ignoreId = null
    ): string {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 2;

        while (
            ExpenseCategory::withTrashed()
                ->when(
                    $ignoreId,
                    fn ($query) =>
                        $query->whereKeyNot($ignoreId)
                )
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}