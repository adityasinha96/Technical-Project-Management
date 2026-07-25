@extends('layouts.admin')

@section('title', 'Management Reports')
@section('page-title', 'Management Reports')

@section('content')
    @php
        /*
        |--------------------------------------------------------------------------
        | Normalise Report Collections
        |--------------------------------------------------------------------------
        |
        | Cached or previously serialised report data can occasionally be
        | restored as an incomplete PHP object. Convert valid arrays and
        | traversable values into Laravel collections and safely fall back
        | to empty collections for unusable values.
        |
        */

        $normaliseReportCollection = static function (
            mixed $value
        ): \Illuminate\Support\Collection {
            if (
                $value instanceof
                \Illuminate\Support\Collection
            ) {
                return $value;
            }

            if (is_array($value)) {
                return collect($value);
            }

            if (
                $value instanceof
                \Traversable
            ) {
                return collect(
                    iterator_to_array($value)
                );
            }

            return collect();
        };

        $projectTrendRows =
            $normaliseReportCollection(
                $projectTrend ?? []
            );

        $insightRows =
            $normaliseReportCollection(
                $insights ?? []
            );

        $riskProjectRows =
            $normaliseReportCollection(
                $riskProjects ?? []
            );
    @endphp

    <div class="space-y-6">
        <section class="rounded-3xl bg-slate-950 p-7 text-white shadow-xl">
            <p class="text-xs font-bold uppercase tracking-[0.22em] text-indigo-300">
                Management Intelligence
            </p>

            <h1 class="mt-3 text-3xl font-black">
                Executive Reporting Dashboard
            </h1>

            <p class="mt-2 text-sm text-slate-300">
                Reporting period:
                {{ $filters->periodLabel() }}
            </p>
        </section>

        @include(
            'reports.partials.navigation'
        )

        @include(
            'reports.partials.filters',
            [
                'filterAction' =>
                    route('reports.index'),

                'showProjectStatus' =>
                    true,

                'showProjectPriority' =>
                    true,
            ]
        )

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ([
                [
                    'Contract Value',
                    '₹' . number_format(
                        $projectSummary['contract_value'],
                        2
                    ),
                    'border-blue-200 bg-blue-50'
                ],
                [
                    'Net Collected',
                    '₹' . number_format(
                        $projectSummary['collected'],
                        2
                    ),
                    'border-emerald-200 bg-emerald-50'
                ],
                [
                    'Outstanding',
                    '₹' . number_format(
                        $projectSummary['outstanding'],
                        2
                    ),
                    'border-amber-200 bg-amber-50'
                ],
                [
                    'Contract Profit',
                    '₹' . number_format(
                        $projectSummary['contract_profit'],
                        2
                    ),
                    'border-violet-200 bg-violet-50'
                ],
                [
                    'Active Projects',
                    $projectSummary['active_projects'],
                    'border-indigo-200 bg-indigo-50'
                ],
                [
                    'Delayed Projects',
                    $projectSummary['delayed_projects'],
                    'border-red-200 bg-red-50'
                ],
                [
                    'Task Completion',
                    number_format(
                        $teamSummary[
                            'completed_tasks'
                        ]
                    )
                    . ' / '
                    . number_format(
                        $teamSummary[
                            'total_tasks'
                        ]
                    ),
                    'border-cyan-200 bg-cyan-50'
                ],
                [
                    'Ticket SLA',
                    number_format(
                        $ticketSummary[
                            'response_sla_compliance'
                        ],
                        1
                    )
                    . '%',
                    'border-orange-200 bg-orange-50'
                ],
            ] as [$label, $value, $classes])
                <article class="rounded-3xl border p-5 shadow-sm {{ $classes }}">
                    <p class="text-sm font-medium text-slate-600">
                        {{ $label }}
                    </p>

                    <p class="mt-2 text-2xl font-black text-slate-950">
                        {{ $value }}
                    </p>
                </article>
            @endforeach
        </section>

        <section class="grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">
            <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-black text-slate-950">
                    Project Movement
                </h2>

                <div class="mt-6 space-y-4">
                    @php
                        $maximumProjects = max(
                            1,
                            $projectTrendRows->max(
                                fn ($row) =>
                                    max(
                                        $row['started'],
                                        $row['completed']
                                    )
                            )
                        );
                    @endphp

                    @foreach ($projectTrendRows as $row)
                        <div>
                            <div class="flex items-center justify-between text-xs font-bold text-slate-500">
                                <span>{{ $row['label'] }}</span>

                                <span>
                                    {{ $row['started'] }}
                                    started /
                                    {{ $row['completed'] }}
                                    completed
                                </span>
                            </div>

                            <div class="mt-2 grid gap-2">
                                <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                                    <div
                                        class="h-full rounded-full bg-indigo-500"
                                        style="width: {{
                                            $row['started']
                                            / $maximumProjects
                                            * 100
                                        }}%"
                                    ></div>
                                </div>

                                <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                                    <div
                                        class="h-full rounded-full bg-emerald-500"
                                        style="width: {{
                                            $row['completed']
                                            / $maximumProjects
                                            * 100
                                        }}%"
                                    ></div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </article>

            <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-black text-slate-950">
                    Key Ratios
                </h2>

                <dl class="mt-6 space-y-5">
                    @foreach ([
                        'Project Completion Rate' =>
                            $projectSummary[
                                'completion_rate'
                            ] . '%',

                        'On-Time Completion Rate' =>
                            $projectSummary[
                                'on_time_completion_rate'
                            ] . '%',

                        'Collection Percentage' =>
                            $projectSummary[
                                'collection_percentage'
                            ] . '%',

                        'Contract Margin' =>
                            $projectSummary[
                                'contract_margin'
                            ] . '%',

                        'Response SLA Compliance' =>
                            $ticketSummary[
                                'response_sla_compliance'
                            ] . '%',

                        'Resolution SLA Compliance' =>
                            $ticketSummary[
                                'resolution_sla_compliance'
                            ] . '%',
                    ] as $label => $value)
                        <div class="flex items-center justify-between gap-4 border-b border-slate-100 pb-4">
                            <dt class="text-sm text-slate-500">
                                {{ $label }}
                            </dt>

                            <dd class="font-black text-slate-950">
                                {{ $value }}
                            </dd>
                        </div>
                    @endforeach
                </dl>
            </article>
        </section>

        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-xl font-black text-slate-950">
                Management Insights
            </h2>

            <div class="mt-5 grid gap-4 lg:grid-cols-2">
                @forelse ($insightRows as $insight)
                    <a
                        href="{{ $insight['url'] }}"
                        class="rounded-3xl border p-5 transition hover:-translate-y-0.5 hover:shadow-md
                            {{
                                match (
                                    $insight['severity']
                                ) {
                                    \App\Enums\NotificationSeverity::Critical =>
                                        'border-red-300 bg-red-50',

                                    \App\Enums\NotificationSeverity::Danger =>
                                        'border-orange-300 bg-orange-50',

                                    \App\Enums\NotificationSeverity::Warning =>
                                        'border-amber-300 bg-amber-50',

                                    default =>
                                        'border-blue-200 bg-blue-50',
                                }
                            }}"
                    >
                        <span class="rounded-full px-3 py-1 text-xs font-bold ring-1 ring-inset {{ $insight['severity']->badgeClasses() }}">
                            {{ $insight['severity']->label() }}
                        </span>

                        <h3 class="mt-4 font-black text-slate-950">
                            {{ $insight['title'] }}
                        </h3>

                        <p class="mt-2 text-sm leading-6 text-slate-600">
                            {{ $insight['message'] }}
                        </p>
                    </a>
                @empty
                    <div class="lg:col-span-2 rounded-3xl border border-emerald-200 bg-emerald-50 p-8 text-center">
                        <p class="font-black text-emerald-800">
                            No major management exceptions detected.
                        </p>
                    </div>
                @endforelse
            </div>
        </section>

        @if ($riskProjectRows->isNotEmpty())
            <section class="rounded-3xl border border-red-200 bg-white p-6 shadow-sm">
                <h2 class="text-xl font-black text-red-950">
                    Highest-Risk Projects
                </h2>

                <div class="mt-5 space-y-3">
                    @foreach ($riskProjectRows as $risk)
                        <a
                            href="{{ route(
                                'projects.show',
                                $risk['project']
                            ) }}"
                            class="flex flex-col gap-4 rounded-2xl border border-red-100 bg-red-50 p-4 sm:flex-row sm:items-center sm:justify-between"
                        >
                            <div>
                                <p class="font-black text-slate-950">
                                    {{ $risk['project']->name }}
                                </p>

                                <p class="mt-1 text-xs text-red-700">
                                    {{ implode(
                                        ' · ',
                                        $risk['reasons']
                                    ) }}
                                </p>
                            </div>

                            <span class="rounded-full bg-red-600 px-4 py-2 text-sm font-black text-white">
                                Risk {{ $risk['risk_score'] }}/100
                            </span>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
@endsection

