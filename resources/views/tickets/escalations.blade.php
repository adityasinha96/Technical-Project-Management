@extends('layouts.admin')

@section('title', 'Ticket Escalations')
@section('page-title', 'Ticket Escalation Dashboard')

@section('content')
    <div class="space-y-6">
        <section class="rounded-3xl bg-slate-950 p-7 text-white shadow-xl">
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-red-300">
                SLA Control Centre
            </p>

            <h1 class="mt-3 text-3xl font-black">
                Ticket Escalations
            </h1>

            <p class="mt-2 text-sm text-slate-300">
                Review tickets approaching or exceeding their SLA deadlines.
            </p>
        </section>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <article class="rounded-3xl border border-amber-200 bg-amber-50 p-5">
                <p class="text-sm text-amber-700">Level 1 Warning</p>
                <p class="mt-2 text-3xl font-black text-amber-950">
                    {{ $summary['level_one'] }}
                </p>
            </article>

            <article class="rounded-3xl border border-orange-200 bg-orange-50 p-5">
                <p class="text-sm text-orange-700">Level 2 Overdue</p>
                <p class="mt-2 text-3xl font-black text-orange-950">
                    {{ $summary['level_two'] }}
                </p>
            </article>

            <article class="rounded-3xl border border-red-200 bg-red-50 p-5">
                <p class="text-sm text-red-700">Level 3 Critical</p>
                <p class="mt-2 text-3xl font-black text-red-950">
                    {{ $summary['level_three'] }}
                </p>
            </article>

            <article class="rounded-3xl border border-violet-200 bg-violet-50 p-5">
                <p class="text-sm text-violet-700">Unassigned Escalated</p>
                <p class="mt-2 text-3xl font-black text-violet-950">
                    {{ $summary['unassigned_escalated'] }}
                </p>
            </article>
        </section>

        <section class="grid gap-4">
            @forelse ($tickets as $ticket)
                @php
                    $currentEscalation = $ticket->escalations
                        ->firstWhere('level', $ticket->escalation_level);
                @endphp

                <article class="rounded-3xl border {{ $ticket->escalation_level >= 3 ? 'border-red-400 bg-red-50' : 'border-orange-200 bg-white' }} p-5 shadow-sm">
                    <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
                        <div class="flex-1">
                            <div class="flex flex-wrap gap-2">
                                <span class="rounded-full bg-red-600 px-3 py-1 text-xs font-bold text-white">
                                    Level {{ $ticket->escalation_level }}
                                </span>

                                <span class="rounded-full px-3 py-1 text-xs font-bold {{ $ticket->priority->badgeClasses() }}">
                                    {{ $ticket->priority->label() }}
                                </span>

                                <span class="rounded-full px-3 py-1 text-xs font-bold {{ $ticket->status->badgeClasses() }}">
                                    {{ $ticket->status->label() }}
                                </span>
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
                                · {{ $ticket->project->client->display_name }}
                            </p>

                            <div class="mt-4 grid gap-3 sm:grid-cols-3">
                                <div>
                                    <p class="text-xs text-slate-400">Assigned To</p>
                                    <p class="mt-1 text-sm font-bold">
                                        {{ $ticket->assignedTo?->name ?? 'Unassigned' }}
                                    </p>
                                </div>

                                <div>
                                    <p class="text-xs text-slate-400">SLA Due</p>
                                    <p class="mt-1 text-sm font-bold text-red-700">
                                        {{ $ticket->current_sla_due_at?->format('d M Y, h:i A') }}
                                    </p>
                                </div>

                                <div>
                                    <p class="text-xs text-slate-400">Overdue</p>
                                    <p class="mt-1 text-sm font-bold text-red-700">
                                        {{ $currentEscalation?->minutes_overdue ?? 0 }} minutes
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="w-full rounded-2xl bg-white p-4 xl:w-72">
                            @if ($currentEscalation?->acknowledged_at)
                                <p class="text-sm font-bold text-emerald-700">
                                    Acknowledged by
                                    {{ $currentEscalation->acknowledgedBy?->name }}
                                </p>

                                <p class="mt-1 text-xs text-slate-500">
                                    {{ $currentEscalation->acknowledged_at->format('d M Y, h:i A') }}
                                </p>
                            @elseif ($currentEscalation)
                                @can('tickets.acknowledge-escalation')
                                    <form
                                        method="POST"
                                        action="{{ route(
                                            'tickets.escalations.acknowledge',
                                            [$ticket, $currentEscalation]
                                        ) }}"
                                        class="space-y-3"
                                    >
                                        @csrf
                                        @method('PUT')

                                        <textarea
                                            name="acknowledgement_note"
                                            rows="3"
                                            placeholder="Action being taken"
                                            class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"
                                        ></textarea>

                                        <button class="w-full rounded-xl bg-slate-950 px-4 py-2 text-sm font-bold text-white">
                                            Acknowledge
                                        </button>
                                    </form>
                                @endcan
                            @endif
                        </div>
                    </div>
                </article>
            @empty
                <div class="rounded-3xl border border-dashed border-emerald-300 bg-emerald-50 p-14 text-center">
                    <p class="font-black text-emerald-900">
                        No active SLA escalations
                    </p>
                </div>
            @endforelse
        </section>

        {{ $tickets->links() }}
    </div>
@endsection