@extends('layouts.admin')

@section('title', 'Expense Categories')
@section('page-title', 'Expense Categories')

@section('content')
    <div class="grid gap-6 xl:grid-cols-[1.25fr_0.75fr]">
        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 p-6">
                <h2 class="text-lg font-bold text-slate-950">
                    Expense Categories
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Control which categories are available for project or business expenses.
                </p>
            </div>

            <div class="divide-y divide-slate-100">
                @foreach ($categories as $category)
                    <article class="p-5">
                        <details>
                            <summary class="cursor-pointer list-none">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="font-bold text-slate-950">
                                            {{ $category->name }}
                                        </p>

                                        <p class="mt-1 text-xs text-slate-500">
                                            {{ $category->scope->label() }}
                                            ·
                                            {{ $category->expenses_count }}
                                            expense(s)
                                        </p>
                                    </div>

                                    <span class="rounded-full px-3 py-1 text-xs font-bold {{ $category->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                        {{ $category->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                            </summary>

                            <form
                                method="POST"
                                action="{{ route(
                                    'expense-categories.update',
                                    $category
                                ) }}"
                                class="mt-5 grid gap-4 rounded-2xl bg-slate-50 p-4 md:grid-cols-2"
                            >
                                @csrf
                                @method('PUT')

                                <x-form.input
                                    label="Category Name"
                                    name="name"
                                    :value="$category->name"
                                    required
                                />

                                <x-form.select
                                    label="Category Scope"
                                    name="scope"
                                    required
                                >
                                    @foreach ($scopes as $scope)
                                        <option
                                            value="{{ $scope->value }}"
                                            @selected(
                                                $category->scope ===
                                                $scope
                                            )
                                        >
                                            {{ $scope->label() }}
                                        </option>
                                    @endforeach
                                </x-form.select>

                                <x-form.input
                                    label="Sort Order"
                                    name="sort_order"
                                    type="number"
                                    min="0"
                                    :value="$category->sort_order"
                                />

                                <label class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white p-4">
                                    <input
                                        type="checkbox"
                                        name="is_active"
                                        value="1"
                                        @checked(
                                            $category->is_active
                                        )
                                        class="h-5 w-5 rounded border-slate-300 text-indigo-600"
                                    >

                                    <span class="text-sm font-bold text-slate-800">
                                        Active category
                                    </span>
                                </label>

                                <div class="md:col-span-2">
                                    <x-form.textarea
                                        label="Description"
                                        name="description"
                                        :value="$category->description"
                                        rows="3"
                                    />
                                </div>

                                <button class="min-h-11 rounded-2xl bg-slate-950 px-4 text-sm font-bold text-white">
                                    Save Category
                                </button>
                            </form>
                        </details>
                    </article>
                @endforeach
            </div>

            <div class="border-t border-slate-100 p-5">
                {{ $categories->links() }}
            </div>
        </section>

        <section class="h-fit rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-bold text-slate-950">
                Add Category
            </h2>

            <form
                method="POST"
                action="{{ route(
                    'expense-categories.store'
                ) }}"
                class="mt-5 space-y-4"
            >
                @csrf

                <x-form.input
                    label="Category Name"
                    name="name"
                    required
                />

                <x-form.select
                    label="Category Scope"
                    name="scope"
                    required
                >
                    @foreach ($scopes as $scope)
                        <option value="{{ $scope->value }}">
                            {{ $scope->label() }}
                        </option>
                    @endforeach
                </x-form.select>

                <x-form.input
                    label="Sort Order"
                    name="sort_order"
                    type="number"
                    min="0"
                    value="0"
                />

                <x-form.textarea
                    label="Description"
                    name="description"
                    rows="3"
                />

                <label class="flex items-center gap-3 rounded-2xl border border-slate-200 p-4">
                    <input
                        type="checkbox"
                        name="is_active"
                        value="1"
                        checked
                        class="h-5 w-5 rounded border-slate-300 text-indigo-600"
                    >

                    <span class="text-sm font-bold text-slate-800">
                        Active category
                    </span>
                </label>

                <button class="min-h-11 w-full rounded-2xl bg-indigo-600 px-4 text-sm font-bold text-white">
                    Create Category
                </button>
            </form>
        </section>
    </div>
@endsection