@extends('layouts.client')

@section('title', $project->name)

@section('content')
    <div
        x-data="{
            tab: new URLSearchParams(
                window.location.search
            ).get('tab') || 'overview'
        }"
        class="space-y-6"
    >
        <section class="rounded-3xl bg-slate-950 p-7 text-white shadow-xl sm:p-9">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-indigo-300">
                        {{ $project->project_code ?? 'Client Project' }}
                    </p>

                    <h1 class="mt-3 text-3xl font-black sm:text-4xl">
                        {{ $project->name }}
                    </h1>

                    <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-300">
                        {{ $project->client_portal_summary
                            ?: 'Project progress and shared client information.' }}
                    </p>
                </div>

                <span class="inline-flex rounded-full px-4 py-2 text-sm font-bold {{ $project->status->badgeClasses() }}">
                    {{ $project->status->label() }}
                </span>
            </div>

            <div class="mt-7">
                <div class="flex justify-between text-sm">
                    <span class="font-bold text-slate-300">
                        Official Progress
                    </span>

                    <span class="font-black">
                        {{ $project->official_progress }}%
                    </span>
                </div>

                <div class="mt-2 h-3 overflow-hidden rounded-full bg-white/10">
                    <div
                        class="h-full rounded-full bg-indigo-400"
                        style="width: {{ min(
                            100,
                            $project->official_progress
                        ) }}%"
                    ></div>
                </div>
            </div>
        </section>

        <nav class="flex gap-2 overflow-x-auto rounded-3xl border border-slate-200 bg-white p-2 shadow-sm">
            @foreach ([
                'overview' => 'Overview',
                'approvals' => 'Approvals',
                'tickets' => 'Tickets',
                'files' => 'Files',
                'messages' => 'Messages',
            ] as $key => $label)
                @if (
                    $key === 'files'
                    && !$access->can_view_files
                )
                    @continue
                @endif

                @if (
                    $key === 'messages'
                    && !$access->can_communicate
                )
                    @continue
                @endif

                <button
                    type="button"
                    @click="tab = '{{ $key }}'"
                    :class="tab === '{{ $key }}'
                        ? 'bg-slate-950 text-white'
                        : 'text-slate-600 hover:bg-slate-100'"
                    class="whitespace-nowrap rounded-2xl px-4 py-3 text-sm font-bold"
                >
                    {{ $label }}
                </button>
            @endforeach

            @if ($access->can_view_financials)
                <a
                    href="{{ route(
                        'client.payments.index',
                        $project
                    ) }}"
                    class="whitespace-nowrap rounded-2xl px-4 py-3 text-sm font-bold text-slate-600 hover:bg-slate-100"
                >
                    Payments
                </a>
            @endif
        </nav>

        <section
            x-show="tab === 'overview'"
            x-cloak
            class="grid gap-5 lg:grid-cols-3"
        >
            <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2">
                <h2 class="text-xl font-black text-slate-950">
                    Project Information
                </h2>

                <dl class="mt-6 grid gap-5 sm:grid-cols-2">
                    @foreach ([
                        'Project Manager' =>
                            $project->manager?->name ?? 'UIPRO Team',

                        'Start Date' =>
                            $project->start_date?->format('d M Y') ?? 'Not set',

                        'Expected Delivery' =>
                            $project->expected_delivery_date?->format('d M Y') ?? 'Not set',

                        'Project Status' =>
                            $project->status->label(),

                        'Official Progress' =>
                            $project->official_progress . '%',

                        'Pending Approvals' =>
                            $pendingApprovalCount,
                    ] as $label => $value)
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">
                                {{ $label }}
                            </dt>

                            <dd class="mt-1 font-black text-slate-900">
                                {{ $value }}
                            </dd>
                        </div>
                    @endforeach
                </dl>
            </article>

            <article class="rounded-3xl border border-indigo-200 bg-indigo-50 p-6">
                <h2 class="font-black text-indigo-950">
                    Need Support?
                </h2>

                <p class="mt-2 text-sm leading-6 text-indigo-700">
                    Submit a project ticket for an issue, change request or support requirement.
                </p>

                @if ($access->can_submit_tickets)
                    <a
                        href="{{ route(
                            'client.tickets.create',
                            $project
                        ) }}"
                        class="mt-5 inline-flex min-h-11 items-center rounded-2xl bg-indigo-600 px-5 text-sm font-bold text-white"
                    >
                        Create Ticket
                    </a>
                @endif
            </article>
        </section>

        <section
            x-show="tab === 'approvals'"
            x-cloak
            class="space-y-4"
        >
            @forelse ($approvals as $approval)
                <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-indigo-600">
                                {{ $approval->stage->label() }}
                            </p>

                            <h2 class="mt-2 text-xl font-black text-slate-950">
                                {{ $approval->title ?? $approval->stage->label() . ' Approval' }}
                            </h2>
                        </div>

                        <span class="rounded-full px-3 py-1 text-xs font-bold {{ $approval->client_decision->badgeClasses() }}">
                            {{ $approval->client_decision->label() }}
                        </span>
                    </div>

                    @if ($approval->notes ?? false)
                        <p class="mt-4 whitespace-pre-line text-sm leading-7 text-slate-600">
                            {{ $approval->notes }}
                        </p>
                    @endif

                    @if (
                        $access->can_approve
                        && $approval->client_decision ===
                            \App\Enums\ClientApprovalDecision::Pending
                    )
                        <form
                            method="POST"
                            action="{{ route(
                                'client.approvals.update',
                                [$project, $approval]
                            ) }}"
                            class="mt-6 rounded-2xl bg-slate-50 p-5"
                        >
                            @csrf
                            @method('PUT')

                            <label class="block">
                                <span class="mb-2 block text-sm font-bold text-slate-700">
                                    Decision
                                </span>

                                <select
                                    name="decision"
                                    required
                                    class="min-h-12 w-full rounded-2xl border border-slate-200 bg-white px-4"
                                >
                                    <option value="">
                                        Select decision
                                    </option>

                                    <option value="approved">
                                        Approve
                                    </option>

                                    <option value="changes_requested">
                                        Request Changes
                                    </option>
                                </select>
                            </label>

                            <label class="mt-4 block">
                                <span class="mb-2 block text-sm font-bold text-slate-700">
                                    Feedback
                                </span>

                                <textarea
                                    name="feedback"
                                    rows="5"
                                    class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3"
                                    placeholder="Provide clear feedback when requesting changes"
                                ></textarea>
                            </label>

                            <button class="mt-4 min-h-11 rounded-2xl bg-slate-950 px-6 text-sm font-bold text-white">
                                Submit Decision
                            </button>
                        </form>
                    @elseif ($approval->client_feedback)
                        <div class="mt-5 rounded-2xl bg-slate-50 p-4">
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">
                                Client Feedback
                            </p>

                            <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-700">
                                {{ $approval->client_feedback }}
                            </p>
                        </div>
                    @endif
                </article>
            @empty
                <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-14 text-center">
                    <p class="font-black text-slate-900">
                        No approvals have been shared.
                    </p>
                </div>
            @endforelse
        </section>

        <section
            x-show="tab === 'tickets'"
            x-cloak
            class="space-y-4"
        >
            <div class="flex justify-end">
                @if ($access->can_submit_tickets)
                    <a
                        href="{{ route(
                            'client.tickets.create',
                            $project
                        ) }}"
                        class="inline-flex min-h-11 items-center rounded-2xl bg-slate-950 px-5 text-sm font-bold text-white"
                    >
                        + Create Ticket
                    </a>
                @endif
            </div>

            @forelse ($tickets as $ticket)
                <a
                    href="{{ route(
                        'client.tickets.show',
                        [$project, $ticket]
                    ) }}"
                    class="block rounded-3xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-indigo-300"
                >
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <p class="text-xs font-bold text-indigo-600">
                                {{ $ticket->ticket_number }}
                            </p>

                            <h3 class="mt-2 font-black text-slate-950">
                                {{ $ticket->subject }}
                            </h3>

                            <p class="mt-1 text-sm text-slate-500">
                                Assigned to:
                                {{ $ticket->assignedTo?->name ?? 'UIPRO Support Team' }}
                            </p>
                        </div>

                        <span class="rounded-full px-3 py-1 text-xs font-bold {{ $ticket->status->badgeClasses() }}">
                            {{ $ticket->status->label() }}
                        </span>
                    </div>
                </a>
            @empty
                <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-14 text-center">
                    No client-visible tickets found.
                </div>
            @endforelse
        </section>

        <section
            x-show="tab === 'files'"
            x-cloak
            class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3"
        >
            @foreach ($files as $file)
                <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="truncate font-black text-slate-950">
                        {{ $file->original_name }}
                    </p>

                    <p class="mt-2 text-xs text-slate-500">
                        {{ $file->formatted_size ?? number_format(
                            $file->size_bytes / 1024,
                            1
                        ) . ' KB' }}
                    </p>

                    <a
                        href="{{ route(
                            'client.files.download',
                            [$project, $file]
                        ) }}"
                        class="mt-4 inline-flex min-h-10 items-center rounded-xl bg-indigo-50 px-4 text-sm font-bold text-indigo-700"
                    >
                        Download
                    </a>
                </article>
            @endforeach
        </section>

        <section
            x-show="tab === 'messages'"
            x-cloak
        >
            <a
                href="{{ route(
                    'client.communications.index',
                    $project
                ) }}"
                class="inline-flex min-h-12 items-center rounded-2xl bg-indigo-600 px-6 text-sm font-bold text-white"
            >
                Open Project Messages
            </a>
        </section>
    </div>
@endsection