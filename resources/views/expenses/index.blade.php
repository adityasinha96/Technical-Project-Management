@extends('layouts.admin')

@section('title', 'Expenses')
@section('page-title', 'Expense Management')

@section('content')
    <div class="space-y-6">
        <section class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-black text-slate-950">
                    Expenses
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Track project costs and general operating expenses.
                </p>
            </div>

            <div class="flex flex-wrap gap-3">
                @can('expense-categories.manage')
                    <a
                        href="{{ route(
                            'expense-categories.index'
                        ) }}"
                        class="inline-flex min-h-12 items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 text-sm font-bold text-slate-700"
                    >
                        Categories
                    </a>
                @endcan

                @can('expenses.create')
                    <a
                        href="{{ route('expenses.create') }}"
                        class="inline-flex min-h-12 items-center justify-center rounded-2xl bg-slate-950 px-5 text-sm font-bold text-white"
                    >
                        + Add Expense
                    </a>
                @endcan
            </div>
        </section>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <article class="rounded-3xl border border-indigo-200 bg-indigo-50 p-5 shadow-sm">
                <p class="text-sm font-medium text-indigo-700">
                    {{ $monthSummary['month'] }} Collection
                </p>

                <p class="mt-2 text-2xl font-black text-indigo-950">
                    ₹{{ number_format(
                        $monthSummary['collection'],
                        2
                    ) }}
                </p>
            </article>

            <article class="rounded-3xl border border-red-200 bg-red-50 p-5 shadow-sm">
                <p class="text-sm font-medium text-red-700">
                    Monthly Expense
                </p>

                <p class="mt-2 text-2xl font-black text-red-950">
                    ₹{{ number_format(
                        $monthSummary['total_expenses'],
                        2
                    ) }}
                </p>
            </article>

            <article class="rounded-3xl border {{ $monthSummary['cash_profit'] >= 0 ? 'border-emerald-200 bg-emerald-50' : 'border-red-200 bg-red-50' }} p-5 shadow-sm">
                <p class="text-sm font-medium {{ $monthSummary['cash_profit'] >= 0 ? 'text-emerald-700' : 'text-red-700' }}">
                    Monthly Cash Profit
                </p>

                <p class="mt-2 text-2xl font-black {{ $monthSummary['cash_profit'] >= 0 ? 'text-emerald-950' : 'text-red-950' }}">
                    ₹{{ number_format(
                        $monthSummary['cash_profit'],
                        2
                    ) }}
                </p>
            </article>

            <article class="rounded-3xl border border-violet-200 bg-violet-50 p-5 shadow-sm">
                <p class="text-sm font-medium text-violet-700">
                    Total Business Expenses
                </p>

                <p class="mt-2 text-2xl font-black text-violet-950">
                    ₹{{ number_format(
                        $summary['business_expenses'],
                        2
                    ) }}
                </p>
            </article>
        </section>

        <section
            x-data="{ filtersOpen: false }"
            class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm"
        >
            <button
                type="button"
                @click="filtersOpen = !filtersOpen"
                class="mb-4 rounded-xl border border-slate-200 px-3 py-2 text-sm font-bold lg:hidden"
            >
                Show / Hide Filters
            </button>

            <form
                method="GET"
                :class="filtersOpen ? 'grid' : 'hidden lg:grid'"
                class="gap-3 lg:grid-cols-4"
            >
                <input
                    type="search"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Expense, vendor, bill or project..."
                    class="min-h-12 rounded-2xl border border-slate-200 px-4 text-sm"
                >

                <select
                    name="scope"
                    class="min-h-12 rounded-2xl border border-slate-200 px-4 text-sm"
                >
                    <option value="">
                        All expense types
                    </option>

                    @foreach ($scopes as $scope)
                        <option
                            value="{{ $scope->value }}"
                            @selected(
                                request('scope') ===
                                $scope->value
                            )
                        >
                            {{ $scope->label() }}
                        </option>
                    @endforeach
                </select>

                <select
                    name="status"
                    class="min-h-12 rounded-2xl border border-slate-200 px-4 text-sm"
                >
                    <option value="">
                        All statuses
                    </option>

                    @foreach ($statuses as $status)
                        <option
                            value="{{ $status->value }}"
                            @selected(
                                request('status') ===
                                $status->value
                            )
                        >
                            {{ $status->label() }}
                        </option>
                    @endforeach
                </select>

                <select
                    name="category_id"
                    class="min-h-12 rounded-2xl border border-slate-200 px-4 text-sm"
                >
                    <option value="">
                        All categories
                    </option>

                    @foreach ($categories as $category)
                        <option
                            value="{{ $category->id }}"
                            @selected(
                                (string) request(
                                    'category_id'
                                ) ===
                                (string) $category->id
                            )
                        >
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>

                <select
                    name="project_id"
                    class="min-h-12 rounded-2xl border border-slate-200 px-4 text-sm"
                >
                    <option value="">
                        All projects
                    </option>

                    @foreach ($projects as $project)
                        <option
                            value="{{ $project->id }}"
                            @selected(
                                (string) request(
                                    'project_id'
                                ) ===
                                (string) $project->id
                            )
                        >
                            {{ $project->name }}
                        </option>
                    @endforeach
                </select>

                <input
                    type="date"
                    name="date_from"
                    value="{{ request('date_from') }}"
                    class="min-h-12 rounded-2xl border border-slate-200 px-4 text-sm"
                >

                <input
                    type="date"
                    name="date_to"
                    value="{{ request('date_to') }}"
                    class="min-h-12 rounded-2xl border border-slate-200 px-4 text-sm"
                >

                <div class="flex gap-2">
                    <button class="min-h-12 flex-1 rounded-2xl bg-indigo-600 px-5 text-sm font-bold text-white">
                        Filter
                    </button>

                    <a
                        href="{{ route('expenses.index') }}"
                        class="inline-flex min-h-12 items-center justify-center rounded-2xl border border-slate-200 px-4 text-sm font-bold text-slate-600"
                    >
                        Reset
                    </a>
                </div>
            </form>
        </section>

        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-slate-50">
                        <tr class="text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                            <th class="px-5 py-4">Expense</th>
                            <th class="px-5 py-4">Project / Scope</th>
                            <th class="px-5 py-4">Category</th>
                            <th class="px-5 py-4">Date</th>
                            <th class="px-5 py-4">Status</th>
                            <th class="px-5 py-4 text-right">Amount</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @forelse ($expenses as $expense)
                            <tr class="{{ $expense->is_voided ? 'bg-red-50/40' : 'hover:bg-slate-50' }}">
                                <td class="px-5 py-4">
                                    <a
                                        href="{{ route(
                                            'expenses.show',
                                            $expense
                                        ) }}"
                                        class="font-bold text-indigo-600"
                                    >
                                        {{ $expense->expense_number }}
                                    </a>

                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ $expense->vendor_name
                                            ?: 'No vendor' }}
                                    </p>
                                </td>

                                <td class="px-5 py-4">
                                    @if ($expense->project)
                                        <p class="font-bold text-slate-900">
                                            {{ $expense->project->name }}
                                        </p>

                                        <p class="mt-1 text-xs text-slate-500">
                                            {{ $expense->project->client->display_name }}
                                        </p>
                                    @else
                                        <span class="rounded-full px-3 py-1 text-xs font-bold ring-1 ring-inset {{ $expense->scope->badgeClasses() }}">
                                            {{ $expense->scope->label() }}
                                        </span>
                                    @endif
                                </td>

                                <td class="px-5 py-4 text-sm text-slate-600">
                                    {{ $expense->category->name }}
                                </td>

                                <td class="px-5 py-4 text-sm text-slate-600">
                                    {{ $expense->expense_date->format(
                                        'd M Y'
                                    ) }}
                                </td>

                                <td class="px-5 py-4">
                                    @if ($expense->is_voided)
                                        <span class="rounded-full bg-red-600 px-3 py-1 text-xs font-bold text-white">
                                            Voided
                                        </span>
                                    @else
                                        <span class="rounded-full px-3 py-1 text-xs font-bold ring-1 ring-inset {{ $expense->status->badgeClasses() }}">
                                            {{ $expense->status->label() }}
                                        </span>
                                    @endif
                                </td>

                                <td class="px-5 py-4 text-right text-base font-black text-red-700">
                                    − ₹{{ number_format(
                                        $expense->amount,
                                        2
                                    ) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td
                                    colspan="6"
                                    class="px-5 py-14 text-center"
                                >
                                    <p class="font-bold text-slate-900">
                                        No expenses found
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($expenses->hasPages())
                <div class="border-t border-slate-100 px-5 py-4">
                    {{ $expenses->links() }}
                </div>
            @endif
        </section>
    </div>
@endsection