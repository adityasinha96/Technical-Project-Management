@extends('layouts.admin')

@section('title', 'Team Performance')
@section('page-title', 'Team Performance')

@section('content')
    @php
        /*
        |--------------------------------------------------------------------------
        | Normalise Team Report Data
        |--------------------------------------------------------------------------
        |
        | Report data may arrive as arrays, models, collection items, JSON
        | strings or previously serialised values. Convert each usable row
        | into a plain array before the Blade template accesses its offsets.
        |
        */

        $normaliseTeamReportRow = static function (
            mixed $row
        ): ?array {
            if (is_array($row)) {
                return $row;
            }

            if (
                $row instanceof
                \Illuminate\Contracts\Support\Arrayable
            ) {
                return $row->toArray();
            }

            if ($row instanceof \JsonSerializable) {
                $row = $row->jsonSerialize();

                return is_array($row)
                    ? $row
                    : null;
            }

            if (is_object($row)) {
                $arrayRow = get_object_vars($row);

                return $arrayRow !== []
                    ? $arrayRow
                    : null;
            }

            if (is_string($row)) {
                $decodedRow = json_decode(
                    $row,
                    true
                );

                return is_array($decodedRow)
                    ? $decodedRow
                    : null;
            }

            return null;
        };

        $teamReportRows = collect(
            is_array($teamRows ?? null)
                ? $teamRows
                : (
                    $teamRows instanceof
                    \Illuminate\Support\Collection
                        ? $teamRows->all()
                        : []
                )
        )
            ->map($normaliseTeamReportRow)
            ->filter()
            ->map(
                static function (
                    array $row
                ): array {
                    return array_merge(
                        [
                            'name' =>
                                'Unknown Team Member',

                            'email' =>
                                'No email available',

                            'total_tasks' =>
                                0,

                            'completed_tasks' =>
                                0,

                            'completion_rate' =>
                                0,

                            'overdue_tasks' =>
                                0,

                            'on_time_rate' =>
                                0,

                            'work_minutes' =>
                                0,

                            'resolved_tickets' =>
                                0,

                            'assigned_tickets' =>
                                0,

                            'sla_compliance' =>
                                0,

                            'delivery_index' =>
                                0,
                        ],
                        $row
                    );
                }
            )
            ->values();

        $teamReportSummary = array_merge(
            [
                'active_users' =>
                    0,

                'total_tasks' =>
                    0,

                'completed_tasks' =>
                    0,

                'overdue_tasks' =>
                    0,

                'average_delivery_index' =>
                    0,
            ],
            is_array($summary ?? null)
                ? $summary
                : (
                    $summary instanceof
                    \Illuminate\Contracts\Support\Arrayable
                        ? $summary->toArray()
                        : []
                )
        );
    @endphp

    <div class="space-y-6">
        @include('reports.partials.navigation')

        @include(
            'reports.partials.filters',
            [
                'filterAction' =>
                    route('reports.team'),

                'showUserFilter' =>
                    true,
            ]
        )

        <section class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-slate-950">
                    Team Performance
                </h1>

                <p class="mt-1 text-sm text-slate-500">
                    Delivery and workload indicators for
                    {{ $filters->periodLabel() }}
                </p>
            </div>

            @include(
                'reports.partials.export-form',
                [
                    'reportType' =>
                        \App\Enums\ReportType::TeamPerformance,
                ]
            )
        </section>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
            @foreach ([
                [
                    'Team Members',
                    $teamReportSummary[
                        'active_users'
                    ],
                ],
                [
                    'Assigned Tasks',
                    $teamReportSummary[
                        'total_tasks'
                    ],
                ],
                [
                    'Completed Tasks',
                    $teamReportSummary[
                        'completed_tasks'
                    ],
                ],
                [
                    'Overdue Tasks',
                    $teamReportSummary[
                        'overdue_tasks'
                    ],
                ],
                [
                    'Average Delivery Index',
                    $teamReportSummary[
                        'average_delivery_index'
                    ] . '%',
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

        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-5 py-4 text-left">
                                Team Member
                            </th>

                            <th class="px-5 py-4 text-right">
                                Tasks
                            </th>

                            <th class="px-5 py-4 text-right">
                                Completed
                            </th>

                            <th class="px-5 py-4 text-right">
                                Overdue
                            </th>

                            <th class="px-5 py-4 text-right">
                                On-Time
                            </th>

                            <th class="px-5 py-4 text-right">
                                Work Time
                            </th>

                            <th class="px-5 py-4 text-right">
                                Tickets
                            </th>

                            <th class="px-5 py-4 text-right">
                                SLA
                            </th>

                            <th class="px-5 py-4 text-right">
                                Delivery Index
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @forelse ($teamReportRows as $row)
                            @php
                                $workMinutes = max(
                                    0,
                                    (int) $row[
                                        'work_minutes'
                                    ]
                                );

                                $deliveryIndex = (float) $row[
                                    'delivery_index'
                                ];
                            @endphp

                            <tr>
                                <td class="px-5 py-4">
                                    <p class="font-black text-slate-950">
                                        {{ $row['name'] }}
                                    </p>

                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ $row['email'] }}
                                    </p>
                                </td>

                                <td class="px-5 py-4 text-right">
                                    {{ number_format(
                                        (int) $row[
                                            'total_tasks'
                                        ]
                                    ) }}
                                </td>

                                <td class="px-5 py-4 text-right">
                                    {{ number_format(
                                        (int) $row[
                                            'completed_tasks'
                                        ]
                                    ) }}

                                    <br>

                                    <span class="text-xs text-slate-500">
                                        {{ number_format(
                                            (float) $row[
                                                'completion_rate'
                                            ],
                                            1
                                        ) }}%
                                    </span>
                                </td>

                                <td class="px-5 py-4 text-right text-red-700">
                                    {{ number_format(
                                        (int) $row[
                                            'overdue_tasks'
                                        ]
                                    ) }}
                                </td>

                                <td class="px-5 py-4 text-right">
                                    {{ number_format(
                                        (float) $row[
                                            'on_time_rate'
                                        ],
                                        1
                                    ) }}%
                                </td>

                                <td class="px-5 py-4 text-right">
                                    {{ intdiv(
                                        $workMinutes,
                                        60
                                    ) }}h

                                    {{ $workMinutes % 60 }}m
                                </td>

                                <td class="px-5 py-4 text-right">
                                    {{ number_format(
                                        (int) $row[
                                            'resolved_tickets'
                                        ]
                                    ) }}

                                    /

                                    {{ number_format(
                                        (int) $row[
                                            'assigned_tickets'
                                        ]
                                    ) }}
                                </td>

                                <td class="px-5 py-4 text-right">
                                    {{ number_format(
                                        (float) $row[
                                            'sla_compliance'
                                        ],
                                        1
                                    ) }}%
                                </td>

                                <td class="px-5 py-4 text-right">
                                    <span
                                        class="rounded-full px-3 py-1 text-xs font-black
                                            {{
                                                $deliveryIndex >= 80
                                                    ? 'bg-emerald-50 text-emerald-700'
                                                    : (
                                                        $deliveryIndex >= 60
                                                            ? 'bg-amber-50 text-amber-700'
                                                            : 'bg-red-50 text-red-700'
                                                    )
                                            }}"
                                    >
                                        {{ number_format(
                                            $deliveryIndex,
                                            1
                                        ) }}%
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td
                                    colspan="9"
                                    class="px-5 py-12 text-center text-sm text-slate-500"
                                >
                                    No team performance data is available for the selected period.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="rounded-3xl border border-amber-200 bg-amber-50 p-5">
            <p class="text-sm leading-6 text-amber-900">
                The Delivery Index combines task completion, on-time completion,
                ticket resolution and first-response SLA performance. It is an
                operational indicator, not an employee-rating or payroll formula.
            </p>
        </section>
    </div>
@endsection

