@extends('layouts.admin')

@section('title', 'Project Analytics')
@section('page-title', 'Project Analytics')

@section('content')
    <div class="space-y-6">
        @include('reports.partials.navigation')

        @include(
            'reports.partials.filters',
            [
                'filterAction' =>
                    route('reports.projects'),

                'showProjectStatus' =>
                    true,

                'showProjectPriority' =>
                    true,
            ]
        )

        <section class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-black text-slate-950">
                    Project Performance
                </h1>

                <p class="mt-1 text-sm text-slate-500">
                    {{ $filters->periodLabel() }}
                </p>
            </div>

            @include(
                'reports.partials.export-form',
                [
                    'reportType' =>
                        \App\Enums\ReportType::Projects,
                ]
            )
        </section>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-6">
            @foreach ([
                ['Projects', $summary['total_projects']],
                ['Active', $summary['active_projects']],
                ['Completed', $summary['completed_projects']],
                ['Delayed', $summary['delayed_projects']],
                ['Completion Rate', $summary['completion_rate'] . '%'],
                ['On-Time Rate', $summary['on_time_completion_rate'] . '%'],
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
                            <th class="px-5 py-4 text-left">Project</th>
                            <th class="px-5 py-4 text-left">Client</th>
                            <th class="px-5 py-4 text-left">Status</th>
                            <th class="px-5 py-4 text-right">Progress</th>
                            <th class="px-5 py-4 text-right">Value</th>
                            <th class="px-5 py-4 text-right">Collected</th>
                            <th class="px-5 py-4 text-right">Outstanding</th>
                            <th class="px-5 py-4 text-right">Contract Profit</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @forelse ($projectRows as $project)
                            <tr>
                                <td class="px-5 py-4">
                                    <a
                                        href="{{ route(
                                            'projects.show',
                                            $project
                                        ) }}"
                                        class="font-black text-slate-950 hover:text-indigo-600"
                                    >
                                        {{ $project->name }}
                                    </a>

                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ $project->manager?->name ?? 'No manager' }}
                                    </p>
                                </td>

                                <td class="px-5 py-4">
                                    {{ $project->client->name }}
                                </td>

                                <td class="px-5 py-4">
                                    <span class="rounded-full px-3 py-1 text-xs font-bold {{ $project->status->badgeClasses() }}">
                                        {{ $project->status->label() }}
                                    </span>
                                </td>

                                <td class="px-5 py-4 text-right">
                                    {{ $project->official_progress }}%
                                    official
                                    <br>
                                    <span class="text-xs text-slate-500">
                                        {{ $project->internal_progress }}%
                                        internal
                                    </span>
                                </td>

                                <td class="px-5 py-4 text-right font-bold">
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

                                <td class="px-5 py-4 text-right text-amber-700">
                                    ₹{{ number_format(
                                        $project->pending_amount,
                                        2
                                    ) }}
                                </td>

                                <td class="px-5 py-4 text-right font-black">
                                    ₹{{ number_format(
                                        $project->project_price
                                        - $project->project_expense_amount,
                                        2
                                    ) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td
                                    colspan="8"
                                    class="px-5 py-14 text-center text-slate-500"
                                >
                                    No projects found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-100 p-5">
                {{ $projectRows->links() }}
            </div>
        </section>
    </div>
@endsection