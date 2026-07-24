@extends('layouts.admin')

@section('title', 'Tickets')
@section('page-title', 'Project Tickets')

@section('content')
    <div class="space-y-6">
        <section class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-black text-slate-950">
                    Project Tickets
                </h1>

                <p class="mt-1 text-sm text-slate-500">
                    Track issues, assignments, SLA and resolutions.
                </p>
            </div>

            <div class="flex flex-wrap gap-3">
                @can('tickets.view-escalations')
                    <a
                        href="{{ route('tickets.escalations') }}"
                        class="inline-flex min-h-12 items-center rounded-2xl bg-red-50 px-5 text-sm font-bold text-red-700"
                    >
                        Escalation Dashboard
                    </a>
                @endcan

                @can('tickets.create')
                    <a
                        href="{{ route('tickets.create') }}"
                        class="inline-flex min-h-12 items-center rounded-2xl bg-slate-950 px-5 text-sm font-bold text-white"
                    >
                        + Create Ticket
                    </a>
                @endcan
            </div>
        </section>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
            @foreach ([
                ['Open Tickets', $summary['open'], 'border-blue-200 bg-blue-50'],
                ['Unassigned', $summary['unassigned'], 'border-amber-200 bg-amber-50'],
                ['Escalated', $summary['escalated'], 'border-orange-200 bg-orange-50'],
                ['SLA Overdue', $summary['overdue'], 'border-red-200 bg-red-50'],
                ['Resolved This Month', $summary['resolved_this_month'], 'border-emerald-200 bg-emerald-50'],
            ] as [$label, $value, $classes])
                <article class="rounded-3xl border p-5 shadow-sm {{ $classes }}">
                    <p class="text-sm font-medium text-slate-600">
                        {{ $label }}
                    </p>

                    <p class="mt-2 text-3xl font-black text-slate-950">
                        {{ $value }}
                    </p>
                </article>
            @endforeach
        </section>

        <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <form
                method="GET"
                class="grid gap-3 lg:grid-cols-4"
            >
                <input
                    type="search"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Ticket number, subject, project..."
                    class="min-h-12 rounded-2xl border border-slate-200 px-4 text-sm"
                >

                <select
                    name="status"
                    class="min-h-12 rounded-2xl border border-slate-200 px-4 text-sm"
                >
                    <option value="">All statuses</option>

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
                    name="priority"
                    class="min-h-12 rounded-2xl border border-slate-200 px-4 text-sm"
                >
                    <option value="">All priorities</option>

                    @foreach ($priorities as $priority)
                        <option
                            value="{{ $priority->value }}"
                            @selected(
                                request('priority') ===
                                $priority->value
                            )
                        >
                            {{ $priority->label() }}
                        </option>
                    @endforeach
                </select>

                <select
                    name="assigned_to"
                    class="min-h-12 rounded-2xl border border-slate-200 px-4 text-sm"
                >
                    <option value="">All assignees</option>

                    @foreach ($users as $user)
                        <option
                            value="{{ $user->id }}"
                            @selected(
                                (string) request('assigned_to') ===
                                (string) $user->id
                            )
                        >
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>

                <div class="flex flex-wrap gap-4 lg:col-span-3">
                    <label class="flex items-center gap-2 text-sm font-bold text-slate-700">
                        <input
                            type="checkbox"
                            name="unassigned"
                            value="1"
                            @checked(request()->boolean('unassigned'))
                        >
                        Unassigned only
                    </label>

                    <label class="flex items-center gap-2 text-sm font-bold text-slate-700">
                        <input
                            type="checkbox"
                            name="escalated"
                            value="1"
                            @checked(request()->boolean('escalated'))
                        >
                        Escalated only
                    </label>
                </div>

                <button class="min-h-12 rounded-2xl bg-indigo-600 px-5 text-sm font-bold text-white">
                    Apply Filters
                </button>
            </form>
        </section>

        <section class="grid gap-4">
            @forelse ($tickets as $ticket)
                <article class="rounded-3xl border {{ $ticket->escalation_level >= 3 ? 'border-red-300 bg-red-50/30' : 'border-slate-200 bg-white' }} p-5 shadow-sm sm:p-6">
                    <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap gap-2">
                                <span class="rounded-full px-3 py-1 text-xs font-bold ring-1 ring-inset {{ $ticket->priority->badgeClasses() }}">
                                    {{ $ticket->priority->label() }}
                                </span>

                                <span class="rounded-full px-3 py-1 text-xs font-bold ring-1 ring-inset {{ $ticket->status->badgeClasses() }}">
                                    {{ $ticket->status->label() }}
                                </span>

                                <span class="rounded-full px-3 py-1 text-xs font-bold {{ $ticket->sla_badge_classes }}">
                                    {{ $ticket->sla_label }}
                                </span>

                                @if ($ticket->escalation_level > 0)
                                    <span class="rounded-full bg-red-600 px-3 py-1 text-xs font-bold text-white">
                                        Level {{ $ticket->escalation_level }}
                                    </span>
                                @endif
                            </div>

                            <a
                                href="{{ route('tickets.show', $ticket) }}"
                                class="mt-3 block text-xl font-black text-slate-950 hover:text-indigo-600"
                            >
                                {{ $ticket->subject }}
                            </a>

                            <p class="mt-1 text-sm text-slate-500">
                                {{ $ticket->ticket_number }}
                                · {{ $ticket->project->name }}
                                · {{ $ticket->client->display_name }}
                            </p>

                            <p class="mt-3 line-clamp-2 text-sm leading-6 text-slate-600">
                                {{ $ticket->description }}
                            </p>

                            <div class="mt-4 flex flex-wrap gap-4 text-xs text-slate-500">
                                <span>
                                    Assigned:
                                    <strong>{{ $ticket->assignedTo?->name ?? 'Unassigned' }}</strong>
                                </span>

                                <span>
                                    Discussion:
                                    <strong>{{ $ticket->comments_count }}</strong>
                                </span>

                                <span>
                                    Created:
                                    <strong>{{ $ticket->created_at->diffForHumans() }}</strong>
                                </span>
                            </div>
                        </div>

                        <div class="w-full rounded-2xl bg-slate-50 p-4 xl:w-72">
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">
                                Current SLA Deadline
                            </p>

                            <p class="mt-2 font-black {{ $ticket->sla_state === 'breached' ? 'text-red-700' : 'text-slate-950' }}">
                                {{ $ticket->current_sla_due_at?->format('d M Y, h:i A') ?? 'Completed' }}
                            </p>

                            @if ($ticket->current_sla_due_at)
                                <p class="mt-1 text-xs text-slate-500">
                                    {{ $ticket->current_sla_due_at->diffForHumans() }}
                                </p>
                            @endif

                            <a
                                href="{{ route('tickets.show', $ticket) }}"
                                class="mt-4 inline-flex min-h-10 w-full items-center justify-center rounded-xl bg-slate-950 px-4 text-sm font-bold text-white"
                            >
                                Open Ticket
                            </a>
                        </div>
                    </div>
                </article>
            @empty
                <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-14 text-center">
                    <p class="font-bold text-slate-900">
                        No tickets found
                    </p>
                </div>
            @endforelse
        </section>

        {{ $tickets->links() }}
    </div>
@endsection