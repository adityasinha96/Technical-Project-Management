@extends('layouts.admin')

@section('title', 'Profitability Report')
@section('page-title', 'Profitability Report')

@section('content')
    <div class="space-y-6">
        @include('reports.partials.navigation')

        @include(
            'reports.partials.filters',
            [
                'filterAction' =>
                    route('reports.profitability'),

                'showProjectStatus' =>
                    true,
            ]
        )

        <section class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-slate-950">
                    Profitability and Cash Position
                </h1>

                <p class="mt-1 text-sm text-slate-500">
                    {{ $filters->periodLabel() }}
                </p>
            </div>

            @include(
                'reports.partials.export-form',
                [
                    'reportType' =>
                        \App\Enums\ReportType::Profitability,
                ]
            )
        </section>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ([
                [
                    'Contract Value',
                    '₹' . number_format(
                        $summary['contract_value'],
                        2
                    )
                ],
                [
                    'Project Expenses',
                    '₹' . number_format(
                        $summary['project_expenses'],
                        2
                    )
                ],
                [
                    'Contract Profit',
                    '₹' . number_format(
                        $summary['contract_profit'],
                        2
                    )
                ],
                [
                    'Contract Margin',
                    $summary['contract_margin'] . '%'
                ],
                [
                    'Period Collections',
                    '₹' . number_format(
                        $summary['period_net_collections'],
                        2
                    )
                ],
                [
                    'Period Paid Expenses',
                    '₹' . number_format(
                        $summary['period_paid_expenses'],
                        2
                    )
                ],
                [
                    'Cash Contribution',
                    '₹' . number_format(
                        $summary['cash_contribution'],
                        2
                    )
                ],
                [
                    'Loss-Making Projects',
                    $summary['loss_making_projects']
                ],
            ] as [$label, $value])
                <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm text-slate-500">
                        {{ $label }}
                    </p>

                    <p class="mt-2 text-2xl font-black text-slate-950">
                        {{ $value }}
                    </p>
                </article>
            @endforeach
        </section>

        <section class="rounded-3xl border border-blue-200 bg-blue-50 p-5">
            <p class="text-sm leading-6 text-blue-900">
                Contract profitability uses project price less recorded project
                expenses. Cash contribution uses cleared collections less paid
                expenses during the selected period. These figures serve different
                management purposes and should not be combined as a statutory P&amp;L.
            </p>
        </section>

        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-5 py-4 text-left">Project</th>
                            <th class="px-5 py-4 text-left">Client</th>
                            <th class="px-5 py-4 text-right">Contract</th>
                            <th class="px-5 py-4 text-right">Expenses</th>
                            <th class="px-5 py-4 text-right">Profit</th>
                            <th class="px-5 py-4 text-right">Margin</th>
                            <th class="px-5 py-4 text-right">Cash Contribution</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @foreach ($projectRows as $project)
                            <tr>
                                <td class="px-5 py-4 font-black text-slate-950">
                                    {{ $project->name }}
                                </td>

                                <td class="px-5 py-4">
                                    {{ $project->client->name }}
                                </td>

                                <td class="px-5 py-4 text-right">
                                    ₹{{ number_format(
                                        $project->project_price,
                                        2
                                    ) }}
                                </td>

                                <td class="px-5 py-4 text-right">
                                    ₹{{ number_format(
                                        $project->project_expense_amount,
                                        2
                                    ) }}
                                </td>

                                <td class="px-5 py-4 text-right font-black
                                    {{
                                        $project->calculated_profit >= 0
                                            ? 'text-emerald-700'
                                            : 'text-red-700'
                                    }}"
                                >
                                    ₹{{ number_format(
                                        $project->calculated_profit,
                                        2
                                    ) }}
                                </td>

                                <td class="px-5 py-4 text-right">
                                    {{ number_format(
                                        $project->calculated_margin,
                                        2
                                    ) }}%
                                </td>

                                <td class="px-5 py-4 text-right">
                                    ₹{{ number_format(
                                        $project->net_received_amount
                                        - $project->project_expense_amount,
                                        2
                                    ) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-100 p-5">
                {{ $projectRows->links() }}
            </div>
        </section>
    </div>
@endsection