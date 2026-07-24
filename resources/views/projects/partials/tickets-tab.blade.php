<div x-show="activeTab === 'tickets'" x-cloak>
    <div class="space-y-6">
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
            @foreach ([
                ['Total Tickets', $ticketSummary['total']],
                ['Open', $ticketSummary['open']],
                ['Unassigned', $ticketSummary['unassigned']],
                ['Escalated', $ticketSummary['escalated']],
                ['Resolved', $ticketSummary['resolved']],
            ] as [$label, $value])
                <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm text-slate-500">
                        {{ $label }}
                    </p>

                    <p class="mt-2 text-3xl font-black text-slate-950">
                        {{ $value }}
                    </p>
                </article>
            @endforeach
        </section>

        <section class="flex justify-end">
            @can('tickets.create')
                <a
                    href="{{ route('tickets.create', [
                        'project_id' => $project->id
                    ]) }}"
                    class="inline-flex min-h-12 items-center rounded-2xl bg-slate-950 px-5 text-sm font-bold text-white"
                >
                    + Create Project Ticket
                </a>
            @endcan
        </section>

        <section class="grid gap-4">
            @forelse ($projectTickets as $ticket)
                <article class="rounded-3xl border {{ $ticket->escalation_level > 0 ? 'border-red-200' : 'border-slate-200' }} bg-white p-5 shadow-sm">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap gap-2">
                                <span class="rounded-full px-3 py-1 text-xs font-bold {{ $ticket->priority->badgeClasses() }}">
                                    {{ $ticket->priority->label() }}
                                </span>

                                <span class="rounded-full px-3 py-1 text-xs font-bold {{ $ticket->status->badgeClasses() }}">
                                    {{ $ticket->status->label() }}
                                </span>

                                <span class="rounded-full px-3 py-1 text-xs font-bold {{ $ticket->sla_badge_classes }}">
                                    {{ $ticket->sla_label }}
                                </span>
                            </div>

                            <a
                                href="{{ route('tickets.show', $ticket) }}"
                                class="mt-3 block text-lg font-black text-slate-950 hover:text-indigo-600"
                            >
                                {{ $ticket->subject }}
                            </a>

                            <p class="mt-1 text-xs text-slate-500">
                                {{ $ticket->ticket_number }}
                                · Assigned to
                                {{ $ticket->assignedTo?->name ?? 'Unassigned' }}
                                · {{ $ticket->comments_count }} discussion(s)
                            </p>
                        </div>

                        <div class="text-sm lg:text-right">
                            <p class="font-bold text-slate-900">
                                {{ $ticket->current_sla_due_at?->format('d M Y, h:i A') ?? 'Completed' }}
                            </p>

                            <a
                                href="{{ route('tickets.show', $ticket) }}"
                                class="mt-3 inline-flex rounded-xl bg-indigo-50 px-4 py-2 font-bold text-indigo-700"
                            >
                                Open Ticket
                            </a>
                        </div>
                    </div>
                </article>
            @empty
                <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-14 text-center">
                    <p class="font-bold text-slate-900">
                        No tickets have been created for this project.
                    </p>
                </div>
            @endforelse
        </section>

        {{ $projectTickets->appends([
            'tab' => 'tickets'
        ])->links() }}
    </div>
</div>