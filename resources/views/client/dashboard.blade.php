@extends('layouts.client')

@section('title', 'Dashboard')

@section('content')
    <div class="space-y-7">
        <section class="rounded-3xl bg-slate-950 p-7 text-white shadow-xl sm:p-9">
            <p class="text-sm font-bold text-indigo-300">
                Welcome, {{ $clientUser->name }}
            </p>

            <h1 class="mt-3 text-3xl font-black sm:text-4xl">
                Your Project Dashboard
            </h1>

            <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-300">
                Review authorised projects, outstanding approvals,
                active tickets and project communications.
            </p>
        </section>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ([
                ['Projects', $summary['projects']],
                ['Pending Approvals', $summary['pending_approvals']],
                ['Open Tickets', $summary['open_tickets']],
                ['Unread Alerts', $summary['unread_notifications']],
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

        <section>
            <div>
                <h2 class="text-2xl font-black text-slate-950">
                    Your Projects
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Projects specifically shared with your client account.
                </p>
            </div>

            <div class="mt-5 grid gap-5 lg:grid-cols-2">
                @forelse ($projects as $project)
                    <a
                        href="{{ route(
                            'client.projects.show',
                            $project
                        ) }}"
                        class="group rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:border-indigo-300 hover:shadow-xl"
                    >
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wider text-indigo-600">
                                    {{ $project->project_code ?? 'Project' }}
                                </p>

                                <h3 class="mt-2 text-xl font-black text-slate-950 group-hover:text-indigo-600">
                                    {{ $project->name }}
                                </h3>

                                <p class="mt-1 text-sm text-slate-500">
                                    Project Manager:
                                    {{ $project->manager?->name ?? 'UIPRO Team' }}
                                </p>
                            </div>

                            <span class="rounded-full px-3 py-1 text-xs font-bold {{ $project->status->badgeClasses() }}">
                                {{ $project->status->label() }}
                            </span>
                        </div>

                        <div class="mt-6">
                            <div class="flex items-center justify-between text-sm">
                                <span class="font-bold text-slate-600">
                                    Official Progress
                                </span>

                                <span class="font-black text-slate-950">
                                    {{ $project->official_progress }}%
                                </span>
                            </div>

                            <div class="mt-2 h-3 overflow-hidden rounded-full bg-slate-100">
                                <div
                                    class="h-full rounded-full bg-indigo-600"
                                    style="width: {{ min(
                                        100,
                                        $project->official_progress
                                    ) }}%"
                                ></div>
                            </div>
                        </div>

                        <div class="mt-5 flex items-center justify-between border-t border-slate-100 pt-4 text-sm">
                            <span class="text-slate-500">
                                {{ $project->tickets_count }}
                                open ticket(s)
                            </span>

                            <span class="font-bold text-indigo-600">
                                Open Project →
                            </span>
                        </div>
                    </a>
                @empty
                    <div class="lg:col-span-2 rounded-3xl border border-dashed border-slate-300 bg-white p-14 text-center">
                        <p class="font-black text-slate-900">
                            No projects are currently assigned to your portal account.
                        </p>
                    </div>
                @endforelse
            </div>
        </section>
    </div>
@endsection