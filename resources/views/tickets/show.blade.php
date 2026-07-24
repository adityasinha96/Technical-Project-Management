@extends('layouts.admin')

@section('title', $ticket->ticket_number)
@section('page-title', 'Ticket Details')

@section('content')
    <div class="space-y-6">
        <section class="rounded-3xl bg-slate-950 p-6 text-white shadow-xl sm:p-8">
            <div class="flex flex-col gap-6 xl:flex-row xl:items-start xl:justify-between">
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap gap-2">
                        <span class="rounded-full bg-white/10 px-3 py-1 text-xs font-bold">
                            {{ $ticket->ticket_number }}
                        </span>

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

                    <h1 class="mt-4 text-2xl font-black sm:text-3xl">
                        {{ $ticket->subject }}
                    </h1>

                    <p class="mt-2 text-sm text-slate-300">
                        {{ $ticket->project->name }}
                        · {{ $ticket->client->display_name }}
                    </p>
                </div>

                <a
                    href="{{ route('projects.show', [
                        'project' => $ticket->project,
                        'tab' => 'tickets',
                    ]) }}"
                    class="inline-flex min-h-11 items-center rounded-2xl border border-white/20 px-4 text-sm font-bold text-white"
                >
                    Open Project
                </a>
            </div>
        </section>

        @if ($ticket->escalation_level > 0)
            <section class="rounded-3xl border border-red-200 bg-red-50 p-5">
                <p class="font-black text-red-950">
                    SLA Escalation Level {{ $ticket->escalation_level }}
                </p>

                <p class="mt-1 text-sm text-red-700">
                    Immediate review and action are required.
                </p>
            </section>
        @endif

        <section class="grid gap-6 xl:grid-cols-[1.35fr_0.65fr]">
            <div class="space-y-6">
                <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-black text-slate-950">
                        Ticket Description
                    </h2>

                    <p class="mt-4 whitespace-pre-line text-sm leading-7 text-slate-700">
                        {{ $ticket->description }}
                    </p>

                    @if ($ticket->fileLinks->isNotEmpty())
                        <div class="mt-5 flex flex-wrap gap-2">
                            @foreach ($ticket->fileLinks as $link)
                                @if ($link->file)
                                    <a
                                        href="{{ $link->file->secure_download_url }}"
                                        class="rounded-xl bg-indigo-50 px-3 py-2 text-xs font-bold text-indigo-700"
                                    >
                                        📎 {{ $link->file->original_name }}
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </article>

                <article class="rounded-3xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 p-5">
                        <h2 class="text-lg font-black text-slate-950">
                            Internal Discussion
                        </h2>
                    </div>

                    <div class="divide-y divide-slate-100">
                        @forelse ($comments as $comment)
                            <article class="p-5">
                                <div class="flex items-start gap-4">
                                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-slate-950 font-black text-white">
                                        {{ strtoupper(substr($comment->createdBy->name, 0, 1)) }}
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                            <div>
                                                <p class="font-black text-slate-950">
                                                    {{ $comment->createdBy->name }}
                                                </p>

                                                <span class="mt-1 inline-flex rounded-full px-2.5 py-1 text-xs font-bold {{ $comment->comment_type->badgeClasses() }}">
                                                    {{ $comment->comment_type->label() }}
                                                </span>
                                            </div>

                                            <p class="text-xs text-slate-500">
                                                {{ $comment->created_at->format('d M Y, h:i A') }}
                                            </p>
                                        </div>

                                        <p class="mt-4 whitespace-pre-line text-sm leading-7 text-slate-700">
                                            {{ $comment->message }}
                                        </p>

                                        @if ($comment->fileLinks->isNotEmpty())
                                            <div class="mt-4 flex flex-wrap gap-2">
                                                @foreach ($comment->fileLinks as $link)
                                                    @if ($link->file)
                                                        <a
                                                            href="{{ $link->file->secure_download_url }}"
                                                            class="rounded-xl bg-slate-100 px-3 py-2 text-xs font-bold text-indigo-700"
                                                        >
                                                            📎 {{ $link->file->original_name }}
                                                        </a>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @endif

                                        @if ($comment->edited_at)
                                            <p class="mt-3 text-xs text-slate-400">
                                                Edited {{ $comment->edited_at->diffForHumans() }}
                                            </p>
                                        @endif

                                        @if ($comment->canBeManagedBy(auth()->user()))
                                            <details class="mt-4">
                                                <summary class="cursor-pointer text-xs font-bold text-indigo-600">
                                                    Edit Discussion
                                                </summary>

                                                <form
                                                    method="POST"
                                                    action="{{ route(
                                                        'tickets.comments.update',
                                                        [$ticket, $comment]
                                                    ) }}"
                                                    enctype="multipart/form-data"
                                                    class="mt-3 space-y-3 rounded-2xl bg-slate-50 p-4"
                                                >
                                                    @csrf
                                                    @method('PUT')

                                                    <select
                                                        name="comment_type"
                                                        class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"
                                                    >
                                                        @foreach ($commentTypes as $type)
                                                            <option
                                                                value="{{ $type->value }}"
                                                                @selected($comment->comment_type === $type)
                                                            >
                                                                {{ $type->label() }}
                                                            </option>
                                                        @endforeach
                                                    </select>

                                                    <textarea
                                                        name="message"
                                                        rows="5"
                                                        required
                                                        class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"
                                                    >{{ $comment->message }}</textarea>

                                                    <input
                                                        type="file"
                                                        name="attachments[]"
                                                        multiple
                                                        class="w-full rounded-xl border border-dashed border-slate-300 bg-white p-3 text-sm"
                                                    >

                                                    <button class="rounded-xl bg-slate-950 px-4 py-2 text-sm font-bold text-white">
                                                        Save Discussion
                                                    </button>
                                                </form>
                                            </details>
                                        @endif
                                    </div>
                                </div>
                            </article>
                        @empty
                            <div class="p-12 text-center text-sm text-slate-500">
                                No internal discussion yet.
                            </div>
                        @endforelse
                    </div>

                    <div class="border-t border-slate-100 p-5">
                        {{ $comments->links() }}
                    </div>
                </article>

                @can('tickets.comment')
                    <article class="rounded-3xl border border-indigo-200 bg-indigo-50 p-5 shadow-sm">
                        <h2 class="text-lg font-black text-indigo-950">
                            Add Discussion
                        </h2>

                        <form
                            method="POST"
                            action="{{ route('tickets.comments.store', $ticket) }}"
                            enctype="multipart/form-data"
                            class="mt-5 space-y-4"
                        >
                            @csrf

                            <x-form.select
                                label="Discussion Type"
                                name="comment_type"
                                required
                            >
                                @foreach ($commentTypes as $type)
                                    <option value="{{ $type->value }}">
                                        {{ $type->label() }}
                                    </option>
                                @endforeach
                            </x-form.select>

                            <x-form.textarea
                                label="Message"
                                name="message"
                                rows="6"
                                required
                            />

                            <input
                                type="file"
                                name="attachments[]"
                                multiple
                                class="block w-full rounded-2xl border border-dashed border-indigo-300 bg-white p-4 text-sm"
                            >

                            <button class="min-h-12 rounded-2xl bg-indigo-600 px-6 text-sm font-bold text-white">
                                Add Discussion
                            </button>
                        </form>
                    </article>
                @endcan
            </div>

            <aside class="space-y-6">
                <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="font-black text-slate-950">
                        Ticket Information
                    </h2>

                    <dl class="mt-5 space-y-4">
                        @foreach ([
                            'Type' => $ticket->type->label(),
                            'Source' => $ticket->source->label(),
                            'Created By' => $ticket->createdBy?->name,
                            'Assigned To' => $ticket->assignedTo?->name,
                            'First Response' => $ticket->first_responded_at?->format('d M Y, h:i A'),
                            'Response Due' => $ticket->first_response_due_at?->format('d M Y, h:i A'),
                            'Resolution Due' => $ticket->resolution_due_at?->format('d M Y, h:i A'),
                            'SLA Paused Time' => $ticket->sla_paused_minutes . ' minutes',
                            'Reopened' => $ticket->reopen_count . ' time(s)',
                        ] as $label => $value)
                            <div>
                                <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">
                                    {{ $label }}
                                </dt>

                                <dd class="mt-1 text-sm font-bold text-slate-800">
                                    {{ $value ?: 'Not available' }}
                                </dd>
                            </div>
                        @endforeach
                    </dl>
                </article>

                @can('tickets.assign')
                    <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                        <h2 class="font-black text-slate-950">
                            Assignment
                        </h2>

                        <form
                            method="POST"
                            action="{{ route('tickets.assign', $ticket) }}"
                            class="mt-4 space-y-3"
                        >
                            @csrf
                            @method('PUT')

                            <select
                                name="assigned_to"
                                class="min-h-12 w-full rounded-2xl border border-slate-200 px-4 text-sm"
                            >
                                <option value="">Unassigned</option>

                                @foreach ($users as $user)
                                    <option
                                        value="{{ $user->id }}"
                                        @selected($ticket->assigned_to === $user->id)
                                    >
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>

                            <button class="min-h-11 w-full rounded-2xl bg-slate-950 px-4 text-sm font-bold text-white">
                                Update Assignment
                            </button>
                        </form>
                    </article>
                @endcan

                @if ($ticket->canBeManagedBy(auth()->user()))
                    <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                        <h2 class="font-black text-slate-950">
                            Change Status
                        </h2>

                        <form
                            method="POST"
                            action="{{ route('tickets.transition', $ticket) }}"
                            class="mt-4 space-y-3"
                        >
                            @csrf
                            @method('PUT')

                            <select
                                name="status"
                                required
                                class="min-h-12 w-full rounded-2xl border border-slate-200 px-4 text-sm"
                            >
                                @foreach ($allowedTransitions as $status)
                                    @continue(
                                        $status === \App\Enums\TicketStatus::Resolved ||
                                        $status === \App\Enums\TicketStatus::Reopened
                                    )

                                    <option value="{{ $status->value }}">
                                        {{ $status->label() }}
                                    </option>
                                @endforeach
                            </select>

                            <textarea
                                name="reason"
                                rows="3"
                                placeholder="Reason, client dependency or hold details"
                                class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm"
                            ></textarea>

                            <button class="min-h-11 w-full rounded-2xl bg-indigo-600 px-4 text-sm font-bold text-white">
                                Change Status
                            </button>
                        </form>
                    </article>
                @endif

                @if (
                    !$ticket->status->isCompleted() &&
                    $ticket->canBeManagedBy(auth()->user())
                )
                    @can('tickets.resolve')
                        <article class="rounded-3xl border border-emerald-200 bg-emerald-50 p-5">
                            <h2 class="font-black text-emerald-950">
                                Resolve Ticket
                            </h2>

                            <form
                                method="POST"
                                action="{{ route('tickets.resolve', $ticket) }}"
                                class="mt-4 space-y-3"
                            >
                                @csrf
                                @method('PUT')

                                <textarea
                                    name="resolution_summary"
                                    rows="4"
                                    required
                                    placeholder="Explain the final resolution"
                                    class="w-full rounded-2xl border border-emerald-200 bg-white px-4 py-3 text-sm"
                                ></textarea>

                                <textarea
                                    name="root_cause"
                                    rows="3"
                                    placeholder="Root cause"
                                    class="w-full rounded-2xl border border-emerald-200 bg-white px-4 py-3 text-sm"
                                ></textarea>

                                <textarea
                                    name="preventive_action"
                                    rows="3"
                                    placeholder="Preventive action"
                                    class="w-full rounded-2xl border border-emerald-200 bg-white px-4 py-3 text-sm"
                                ></textarea>

                                <button class="min-h-11 w-full rounded-2xl bg-emerald-600 px-4 text-sm font-bold text-white">
                                    Resolve Ticket
                                </button>
                            </form>
                        </article>
                    @endcan
                @endif

                @if (
                    $ticket->status === \App\Enums\TicketStatus::Resolved ||
                    $ticket->status === \App\Enums\TicketStatus::Closed
                )
                    @can('tickets.reopen')
                        <article class="rounded-3xl border border-violet-200 bg-violet-50 p-5">
                            <h2 class="font-black text-violet-950">
                                Reopen Ticket
                            </h2>

                            <form
                                method="POST"
                                action="{{ route('tickets.reopen', $ticket) }}"
                                class="mt-4 space-y-3"
                            >
                                @csrf
                                @method('PUT')

                                <textarea
                                    name="reopen_reason"
                                    rows="4"
                                    required
                                    placeholder="Explain why the issue remains unresolved"
                                    class="w-full rounded-2xl border border-violet-200 bg-white px-4 py-3 text-sm"
                                ></textarea>

                                <button class="min-h-11 w-full rounded-2xl bg-violet-600 px-4 text-sm font-bold text-white">
                                    Reopen Ticket
                                </button>
                            </form>
                        </article>
                    @endcan
                @endif
            </aside>
        </section>
    </div>
@endsection

