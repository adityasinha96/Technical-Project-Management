@extends('layouts.admin')

@section('title', 'Profitability')
@section('page-title', 'Profitability Dashboard')

@section('content')
    <div class="space-y-6">
        <section class="rounded-3xl bg-slate-950 p-6 text-white shadow-xl sm:p-8">
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-emerald-300">
                Financial Performance
            </p>

            <div class="mt-4 grid gap-6 lg:grid-cols-3">
                <div>
                    <p class="text-sm text-slate-400">
                        Business Cash Position
                    </p>

                    <p class="mt-2 text-3xl font-black {{ $summary['business_cash_position'] >= 0 ? 'text-emerald-300' : 'text-red-300' }}">
                        ₹{{ number_format(
                            $summary['business_cash_position'],
                            2
                        ) }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-slate-400">
                        Contracted Project Profit
                    </p>

                    <p class="mt-2 text-3xl font-black text-cyan-300">
                        ₹{{ number_format(
                            $summary['contracted_project_profit'],
                            2
                        ) }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-slate-400">
                        Market Outstanding
                    </p>

                    <p class="mt-2 text-3xl font-black text-amber-300">
                        ₹{{ number_format(
                            $summary['market_outstanding'],
                            2
                        ) }}
                    </p>
                </div>
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
                    Monthly Expenses
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

            <article class="rounded-3xl border border-amber-200 bg-amber-50 p-5 shadow-sm">
                <p class="text-sm font-medium text-amber-700">
                    Financial Risks
                </p>

                <p class="mt-2 text-xl font-black text-amber-950">
                    {{ $summary['loss_making_projects'] }}
                    loss-making
                </p>

                <p class="mt-1 text-xs font-bold text-amber-700">
                    {{ $summary['cash_negative_projects'] }}
                    cash-negative projects
                </p>
            </article>
        </section>

        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div>
                <h2 class="text-lg font-bold text-slate-950">
                    12-Month Cash Performance
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Cash profit equals monthly collection minus all expenses paid during that month.
                </p>
            </div>

            <div class="mt-6 overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="text-left text-xs font-bold uppercase tracking-wider text-slate-400">
                            <th class="pb-4 pr-5">Month</th>
                            <th class="pb-4 pr-5">Booked Value</th>
                            <th class="pb-4 pr-5">Collection</th>
                            <th class="pb-4 pr-5">Project Expense</th>
                            <th class="pb-4 pr-5">Business Expense</th>
                            <th class="pb-4 text-right">Cash Profit</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @foreach ($monthly['rows'] as $row)
                            <tr>
                                <td class="py-4 pr-5 font-bold text-slate-900">
                                    {{ $row['label'] }}
                                </td>

                                <td class="py-4 pr-5 text-sm text-slate-600">
                                    ₹{{ number_format(
                                        $row['booked_value'],
                                        2
                                    ) }}
                                </td>

                                <td class="py-4 pr-5">
                                    <p class="text-sm font-bold text-indigo-700">
                                        ₹{{ number_format(
                                            $row['collection'],
                                            2
                                        ) }}
                                    </p>

                                    <div class="mt-2 h-1.5 w-32 overflow-hidden rounded-full bg-slate-100">
                                        <div
                                            class="h-full rounded-full bg-indigo-500"
                                            style="width: {{ min(
                                                100,
                                                abs($row['collection']) /
                                                $monthly['maximum'] *
                                                100
                                            ) }}%"
                                        ></div>
                                    </div>
                                </td>

                                <td class="py-4 pr-5 text-sm font-bold text-red-700">
                                    ₹{{ number_format(
                                        $row['project_expenses'],
                                        2
                                    ) }}
                                </td>

                                <td class="py-4 pr-5 text-sm font-bold text-violet-700">
                                    ₹{{ number_format(
                                        $row['business_expenses'],
                                        2
                                    ) }}
                                </td>

                                <td class="py-4 text-right">
                                    <p class="font-black {{ $row['cash_profit'] >= 0 ? 'text-emerald-700' : 'text-red-700' }}">
                                        ₹{{ number_format(
                                            $row['cash_profit'],
                                            2
                                        ) }}
                                    </p>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <form
                method="GET"
                class="grid gap-3 md:grid-cols-[1fr_220px_220px_auto]"
            >
                <input
                    type="search"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search project or client..."
                    class="min-h-12 rounded-2xl border border-slate-200 px-4 text-sm"
                >

                <select
                    name="health"
                    class="min-h-12 rounded-2xl border border-slate-200 px-4 text-sm"
                >
                    <option value="">
                        All profitability levels
                    </option>

                    <option
                        value="loss"
                        @selected(
                            request('health') === 'loss'
                        )
                    >
                        Loss-Making
                    </option>

                    <option
                        value="low_margin"
                        @selected(
                            request('health') ===
                            'low_margin'
                        )
                    >
                        Low Margin
                    </option>

                    <option
                        value="cash_negative"
                        @selected(
                            request('health') ===
                            'cash_negative'
                        )
                    >
                        Cash Negative
                    </option>
                </select>

                <select
                    name="sort"
                    class="min-h-12 rounded-2xl border border-slate-200 px-4 text-sm"
                >
                    <option value="">
                        Highest Profit
                    </option>

                    <option
                        value="lowest_margin"
                        @selected(
                            request('sort') ===
                            'lowest_margin'
                        )
                    >
                        Lowest Margin
                    </option>
                </select>

                <button class="min-h-12 rounded-2xl bg-indigo-600 px-5 text-sm font-bold text-white">
                    Filter
                </button>
            </form>
        </section>

        <section class="grid gap-4">
            @forelse ($projects as $project)
                <article class="rounded-3xl border {{ $project->is_loss_making ? 'border-red-200' : 'border-slate-200' }} bg-white p-5 shadow-sm sm:p-6">
                    <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap gap-2">
                                <span class="rounded-full px-3 py-1 text-xs font-bold ring-1 ring-inset {{ $project->profit_health_classes }}">
                                    {{ $project->profit_health_label }}
                                </span>

                                @if ($project->is_cash_negative)
                                    <span class="rounded-full bg-red-600 px-3 py-1 text-xs font-bold text-white">
                                        Cash Negative
                                    </span>
                                @endif
                            </div>

                            <a
                                href="{{ route('projects.show', [
                                    'project' => $project,
                                    'tab' => 'expenses',
                                ]) }}"
                                class="mt-3 block text-xl font-black text-slate-950 hover:text-indigo-600"
                            >
                                {{ $project->name }}
                            </a>

                            <p class="mt-1 text-sm text-slate-500">
                                {{ $project->client->display_name }}
                                · {{ $project->project_code }}
                            </p>

                            <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                                <div>
                                    <p class="text-xs text-slate-400">
                                        Project Price
                                    </p>

                                    <p class="mt-1 font-bold text-slate-900">
                                        ₹{{ number_format(
                                            $project->project_price,
                                            2
                                        ) }}
                                    </p>
                                </div>

                                <div>
                                    <p class="text-xs text-slate-400">
                                        Received
                                    </p>

                                    <p class="mt-1 font-bold text-emerald-700">
                                        ₹{{ number_format(
                                            $project->net_received_amount,
                                            2
                                        ) }}
                                    </p>
                                </div>

                                <div>
                                    <p class="text-xs text-slate-400">
                                        Expenses
                                    </p>

                                    <p class="mt-1 font-bold text-red-700">
                                        ₹{{ number_format(
                                            $project->project_expense_amount,
                                            2
                                        ) }}
                                    </p>
                                </div>

                                <div>
                                    <p class="text-xs text-slate-400">
                                        Actual Profit
                                    </p>

                                    <p class="mt-1 font-black {{ $project->actual_profit_amount >= 0 ? 'text-emerald-700' : 'text-red-700' }}">
                                        ₹{{ number_format(
                                            $project->actual_profit_amount,
                                            2
                                        ) }}
                                    </p>
                                </div>

                                <div>
                                    <p class="text-xs text-slate-400">
                                        Cash Position
                                    </p>

                                    <p class="mt-1 font-black {{ $project->cash_position_amount >= 0 ? 'text-indigo-700' : 'text-red-700' }}">
                                        ₹{{ number_format(
                                            $project->cash_position_amount,
                                            2
                                        ) }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="w-full rounded-2xl bg-slate-50 p-4 xl:w-60">
                            <div class="flex items-center justify-between">
                                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">
                                    Profit Margin
                                </p>

                                <p class="font-black {{ $project->profit_margin_percentage >= 0 ? 'text-emerald-700' : 'text-red-700' }}">
                                    {{ number_format(
                                        $project->profit_margin_percentage,
                                        2
                                    ) }}%
                                </p>
                            </div>

                            <div class="mt-3 h-2 overflow-hidden rounded-full bg-slate-200">
                                <div
                                    class="h-full rounded-full {{ $project->profit_margin_percentage >= 0 ? 'bg-emerald-500' : 'bg-red-500' }}"
                                    style="width: {{ min(
                                        100,
                                        max(
                                            0,
                                            $project->profit_margin_percentage
                                        )
                                    ) }}%"
                                ></div>
                            </div>

                            <a
                                href="{{ route('projects.show', [
                                    'project' => $project,
                                    'tab' => 'expenses',
                                ]) }}"
                                class="mt-4 inline-flex min-h-10 w-full items-center justify-center rounded-xl bg-slate-950 px-4 text-sm font-bold text-white"
                            >
                                View Costs
                            </a>
                        </div>
                    </div>
                </article>
            @empty
                <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-14 text-center">
                    <p class="font-bold text-slate-900">
                        No projects found
                    </p>
                </div>
            @endforelse
        </section>

        {{ $projects->links() }}
    </div>
@endsection