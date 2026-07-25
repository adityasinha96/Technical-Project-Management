@extends('layouts.admin')

@section('title', 'Collection Report')
@section('page-title', 'Collection Report')

@section('content')
    <div class="space-y-6">
        @include('reports.partials.navigation')

        @include(
            'reports.partials.filters',
            [
                'filterAction' =>
                    route('reports.collections'),

                'showProjectStatus' =>
                    true,
            ]
        )

        <section class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-slate-950">
                    Collection and Outstanding
                </h1>

                <p class="mt-1 text-sm text-slate-500">
                    Collection movement:
                    {{ $filters->periodLabel() }}
                </p>
            </div>

            @include(
                'reports.partials.export-form',
                [
                    'reportType' =>
                        \App\Enums\ReportType::Collections,
                ]
            )
        </section>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ([
                [
                    'Period Collections',
                    '₹' . number_format(
                        $summary['period_net_collections'],
                        2
                    ),
                    'border-emerald-200 bg-emerald-50'
                ],
                [
                    'Current Outstanding',
                    '₹' . number_format(
                        $summary['current_outstanding'],
                        2
                    ),
                    'border-amber-200 bg-amber-50'
                ],
                [
                    'Overdue Outstanding',
                    '₹' . number_format(
                        $summary['overdue_outstanding'],
                        2
                    ),
                    'border-red-200 bg-red-50'
                ],
                [
                    'Collection Percentage',
                    $summary['collection_percentage'] . '%',
                    'border-indigo-200 bg-indigo-50'
                ],
            ] as [$label, $value, $classes])
                <article class="rounded-3xl border p-5 shadow-sm {{ $classes }}">
                    <p class="text-sm text-slate-600">
                        {{ $label }}
                    </p>

                    <p class="mt-2 text-2xl font-black text-slate-950">
                        {{ $value }}
                    </p>
                </article>
            @endforeach
        </section>

        <section class="grid gap-6 xl:grid-cols-2">
            <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="font-black text-slate-950">
                    Outstanding Ageing
                </h2>

                <div class="mt-5 space-y-4">
                    @php
                        $maximumAgeing = max(
                            1,
                            $ageing->max('amount')
                        );
                    @endphp

                    @foreach ($ageing as $row)
                        <div>
                            <div class="flex justify-between text-sm">
                                <span class="font-bold text-slate-700">
                                    {{ $row['bucket'] }}
                                </span>

                                <span class="font-black text-slate-950">
                                    ₹{{ number_format(
                                        $row['amount'],
                                        2
                                    ) }}
                                </span>
                            </div>

                            <div class="mt-2 h-3 overflow-hidden rounded-full bg-slate-100">
                                <div
                                    class="h-full rounded-full bg-amber-500"
                                    style="width: {{
                                        $row['amount']
                                        / $maximumAgeing
                                        * 100
                                    }}%"
                                ></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </article>

            <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="font-black text-slate-950">
                    Payment Mode Breakdown
                </h2>

                <div class="mt-5 divide-y divide-slate-100">
                    @foreach ($modeBreakdown as $mode)
                        <div class="flex items-center justify-between py-4">
                            <div>
                                <p class="font-bold text-slate-900">
                                    {{ $mode->payment_mode?->label()
                                        ?? str($mode->payment_mode)->headline() }}
                                </p>

                                <p class="mt-1 text-xs text-slate-500">
                                    {{ $mode->transactions }}
                                    transaction(s)
                                </p>
                            </div>

                            <p class="font-black text-slate-950">
                                ₹{{ number_format(
                                    $mode->net_amount,
                                    2
                                ) }}
                            </p>
                        </div>
                    @endforeach
                </div>
            </article>
        </section>

        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-5 py-4 text-left">Project</th>
                            <th class="px-5 py-4 text-left">Client</th>
                            <th class="px-5 py-4 text-right">Contract</th>
                            <th class="px-5 py-4 text-right">Collected</th>
                            <th class="px-5 py-4 text-right">Outstanding</th>
                            <th class="px-5 py-4 text-left">Due Date</th>
                            <th class="px-5 py-4 text-left">Ageing</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @foreach ($outstandingRows as $project)
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

                                <td class="px-5 py-4 text-right text-emerald-700">
                                    ₹{{ number_format(
                                        $project->net_received_amount,
                                        2
                                    ) }}
                                </td>

                                <td class="px-5 py-4 text-right font-black text-red-700">
                                    ₹{{ number_format(
                                        $project->pending_amount,
                                        2
                                    ) }}
                                </td>

                                <td class="px-5 py-4">
                                    {{ $project
                                        ->effective_collection_due_date
                                        ?->format('d M Y')
                                        ?? 'Not set' }}
                                </td>

                                <td class="px-5 py-4">
                                    <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700">
                                        {{ $project->collection_ageing_bucket }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-100 p-5">
                {{ $outstandingRows->links() }}
            </div>
        </section>
    </div>
@endsection