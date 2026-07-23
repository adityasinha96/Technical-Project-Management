<div x-show="activeTab === 'expenses'" x-cloak>
    <div class="space-y-6">
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
            <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-500">
                    Project Price
                </p>

                <p class="mt-2 text-2xl font-black text-slate-950">
                    ₹{{ number_format(
                        $project->project_price,
                        2
                    ) }}
                </p>
            </article>

            <article class="rounded-3xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm">
                <p class="text-sm text-emerald-700">
                    Net Received
                </p>

                <p class="mt-2 text-2xl font-black text-emerald-950">
                    ₹{{ number_format(
                        $project->net_received_amount,
                        2
                    ) }}
                </p>
            </article>

            <article class="rounded-3xl border border-red-200 bg-red-50 p-5 shadow-sm">
                <p class="text-sm text-red-700">
                    Project Expenses
                </p>

                <p class="mt-2 text-2xl font-black text-red-950">
                    ₹{{ number_format(
                        $project->project_expense_amount,
                        2
                    ) }}
                </p>
            </article>

            <article class="rounded-3xl border {{ $project->actual_profit_amount >= 0 ? 'border-emerald-200 bg-emerald-50' : 'border-red-200 bg-red-50' }} p-5 shadow-sm">
                <p class="text-sm {{ $project->actual_profit_amount >= 0 ? 'text-emerald-700' : 'text-red-700' }}">
                    Actual Project Profit
                </p>

                <p class="mt-2 text-2xl font-black {{ $project->actual_profit_amount >= 0 ? 'text-emerald-950' : 'text-red-950' }}">
                    ₹{{ number_format(
                        $project->actual_profit_amount,
                        2
                    ) }}
                </p>

                <p class="mt-1 text-xs font-bold">
                    {{ number_format(
                        $project->profit_margin_percentage,
                        2
                    ) }}% margin
                </p>
            </article>

            <article class="rounded-3xl border {{ $project->cash_position_amount >= 0 ? 'border-indigo-200 bg-indigo-50' : 'border-red-200 bg-red-50' }} p-5 shadow-sm">
                <p class="text-sm {{ $project->cash_position_amount >= 0 ? 'text-indigo-700' : 'text-red-700' }}">
                    Cash Position
                </p>

                <p class="mt-2 text-2xl font-black {{ $project->cash_position_amount >= 0 ? 'text-indigo-950' : 'text-red-950' }}">
                    ₹{{ number_format(
                        $project->cash_position_amount,
                        2
                    ) }}
                </p>

                @if ($project->cash_position_amount < 0)
                    <p class="mt-1 text-xs font-bold text-red-700">
                        Project currently using business cash
                    </p>
                @endif
            </article>
        </section>

        <section class="grid gap-6 xl:grid-cols-[1.3fr_0.7fr]">
            <article class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-100 p-5">
                    <div>
                        <h2 class="text-lg font-bold text-slate-950">
                            Project Expense Ledger
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            Paid project costs affect profit and cash position.
                        </p>
                    </div>

                    <a
                        href="{{ route('expenses.index', [
                            'project_id' => $project->id
                        ]) }}"
                        class="text-sm font-bold text-indigo-600"
                    >
                        Full Ledger
                    </a>
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse ($project->expenses as $expense)
                        <article class="{{ $expense->is_voided ? 'bg-red-50/40' : '' }} p-5">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                <div>
                                    <div class="flex flex-wrap gap-2">
                                        @if ($expense->is_voided)
                                            <span class="rounded-full bg-red-600 px-3 py-1 text-xs font-bold text-white">
                                                Voided
                                            </span>
                                        @else
                                            <span class="rounded-full px-3 py-1 text-xs font-bold ring-1 ring-inset {{ $expense->status->badgeClasses() }}">
                                                {{ $expense->status->label() }}
                                            </span>
                                        @endif

                                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">
                                            {{ $expense->category->name }}
                                        </span>
                                    </div>

                                    <a
                                        href="{{ route(
                                            'expenses.show',
                                            $expense
                                        ) }}"
                                        class="mt-3 block font-black text-slate-950 hover:text-indigo-600"
                                    >
                                        {{ $expense->expense_number }}
                                    </a>

                                    <p class="mt-1 text-sm text-slate-500">
                                        {{ $expense->vendor_name ?: 'No vendor' }}
                                        ·
                                        {{ $expense->expense_date->format(
                                            'd M Y'
                                        ) }}
                                    </p>

                                    <p class="mt-3 text-sm leading-6 text-slate-600">
                                        {{ $expense->description }}
                                    </p>
                                </div>

                                <p class="text-2xl font-black text-red-700">
                                    − ₹{{ number_format(
                                        $expense->amount,
                                        2
                                    ) }}
                                </p>
                            </div>
                        </article>
                    @empty
                        <div class="p-12 text-center">
                            <p class="font-bold text-slate-900">
                                No project expenses recorded
                            </p>
                        </div>
                    @endforelse
                </div>
            </article>

            @can('expenses.create')
                <article class="h-fit rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="text-lg font-bold text-slate-950">
                        Add Project Expense
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Record a cost directly against this project.
                    </p>

                    <a
                        href="{{ route('expenses.create', [
                            'project_id' => $project->id
                        ]) }}"
                        class="mt-5 inline-flex min-h-12 w-full items-center justify-center rounded-2xl bg-slate-950 px-5 text-sm font-bold text-white"
                    >
                        Record Project Expense
                    </a>
                </article>
            @endcan
        </section>
    </div>
</div>