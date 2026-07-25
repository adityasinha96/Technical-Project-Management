@extends('layouts.admin')

@section('title', 'Ticket SLA Report')
@section('page-title', 'Ticket SLA Report')

@section('content')
    <div class="space-y-6">
        @include('reports.partials.navigation')

        @include(
            'reports.partials.filters',
            [
                'filterAction' =>
                    route('reports.ticket-sla'),

                'showUserFilter' =>
                    true,

                'showTicketStatus' =>
                    true,

                'showTicketPriority' =>
                    true,
            ]
        )

        <section class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-slate-950">
                    Ticket SLA Performance
                </h1>

                <p class="mt-1 text-sm text-slate-500">
                    {{ $filters->periodLabel() }}
                </p>
            </div>

            @include(
                'reports.partials.export-form',
                [
                    'reportType' =>
                        \App\Enums\ReportType::TicketSla,
                ]
            )
        </section>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ([
                ['Tickets', $summary['total_tickets']],
                ['Resolution Rate', $summary['resolution_rate'] . '%'],
                ['Response SLA', $summary['response_sla_compliance'] . '%'],
                ['Resolution SLA', $summary['resolution_sla_compliance'] . '%'],
                ['Response Breaches', $summary['response_breaches']],
                ['Resolution Breaches', $summary['resolution_breaches']],
                ['Escalated', $summary['escalated_tickets']],
                ['Reopened', $summary['reopened_tickets']],
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
                            <th class="px-5 py-4 text-left">Ticket</th>
                            <th class="px-5 py-4 text-left">Project</th>
                            <th class="px-5 py-4 text-left">Assignee</th>
                            <th class="px-5 py-4 text-left">Priority</th>
                            <th class="px-5 py-4 text-left">Status</th>
                            <th class="px-5 py-4 text-right">Response SLA</th>
                            <th class="px-5 py-4 text-right">Resolution SLA</th>
                            <th class="px-5 py-4 text-right">Escalation</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @foreach ($ticketRows as $ticket)
                            <tr>
                                <td class="px-5 py-4">
                                    <a
                                        href="{{ route(
                                            'tickets.show',
                                            $ticket
                                        ) }}"
                                        class="font-black text-slate-950 hover:text-indigo-600"
                                    >
                                        {{ $ticket->ticket_number }}
                                    </a>

                                    <p class="mt-1 max-w-sm truncate text-xs text-slate-500">
                                        {{ $ticket->subject }}
                                    </p>
                                </td>

                                <td class="px-5 py-4">
                                    {{ $ticket->project->name }}
                                </td>

                                <td class="px-5 py-4">
                                    {{ $ticket->assignedTo?->name ?? 'Unassigned' }}
                                </td>

                                <td class="px-5 py-4">
                                    <span class="rounded-full px-3 py-1 text-xs font-bold {{ $ticket->priority->badgeClasses() }}">
                                        {{ $ticket->priority->label() }}
                                    </span>
                                </td>

                                <td class="px-5 py-4">
                                    {{ $ticket->status->label() }}
                                </td>

                                <td class="px-5 py-4 text-right">
                                    @if (
                                        $ticket->first_responded_at
                                        && $ticket->first_response_due_at
                                    )
                                        <span class="{{
                                            $ticket->first_responded_at->lte(
                                                $ticket->first_response_due_at
                                            )
                                                ? 'text-emerald-700'
                                                : 'text-red-700'
                                        }}">
                                            {{
                                                $ticket->first_responded_at->lte(
                                                    $ticket->first_response_due_at
                                                )
                                                    ? 'Compliant'
                                                    : 'Breached'
                                            }}
                                        </span>
                                    @else
                                        Pending
                                    @endif
                                </td>

                                <td class="px-5 py-4 text-right">
                                    @if (
                                        $ticket->resolved_at
                                        && $ticket->resolution_due_at
                                    )
                                        <span class="{{
                                            $ticket->resolved_at->lte(
                                                $ticket->resolution_due_at
                                            )
                                                ? 'text-emerald-700'
                                                : 'text-red-700'
                                        }}">
                                            {{
                                                $ticket->resolved_at->lte(
                                                    $ticket->resolution_due_at
                                                )
                                                    ? 'Compliant'
                                                    : 'Breached'
                                            }}
                                        </span>
                                    @else
                                        Pending
                                    @endif
                                </td>

                                <td class="px-5 py-4 text-right">
                                    {{ $ticket->escalation_level }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-100 p-5">
                {{ $ticketRows->links() }}
            </div>
        </section>
    </div>
@endsection