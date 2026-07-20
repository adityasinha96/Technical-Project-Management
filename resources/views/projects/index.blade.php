@extends('layouts.admin')

@section('title', 'Projects')
@section('page-title', 'Project Management')

@section('content')
    <div class="space-y-6">
        <section class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-black text-slate-950">
                    Projects
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Track projects, deadlines, pricing and team assignments.
                </p>
            </div>

            @can('projects.create')
                <a
                    href="{{ route('projects.create') }}"
                    class="inline-flex min-h-12 items-center justify-center rounded-2xl bg-slate-950 px-5 text-sm font-bold text-white shadow-lg shadow-slate-300 transition hover:bg-indigo-600"
                >
                    + Add Project
                </a>
            @endcan
        </section>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
            @foreach ([
                ['Total Projects', $summary['total_projects']],
                ['Active', $summary['active_projects']],
                ['Completed', $summary['completed_projects']],
                ['Delayed', $summary['delayed_projects']],
                [
                    'Contracted Value',
                    '₹' . number_format(
                        $summary['contracted_value'],
                        2
                    )
                ],
            ] as [$label, $value])
                <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">
                        {{ $label }}
                    </p>

                    <p class="mt-2 text-2xl font-black text-slate-950">
                        {{ $value }}
                    </p>
                </article>
            @endforeach
        </section>

        <section
            x-data="{ filtersOpen: false }"
            class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5"
        >
            <div class="flex items-center justify-between lg:hidden">
                <p class="font-bold text-slate-900">
                    Project Filters
                </p>

                <button
                    type="button"
                    @click="filtersOpen = !filtersOpen"
                    class="rounded-xl border border-slate-200 px-3 py-2 text-sm font-bold"
                >
                    Show / Hide
                </button>
            </div>

            <form
                method="GET"
                :class="filtersOpen ? 'grid' : 'hidden lg:grid'"
                class="mt-4 gap-3 lg:mt-0 lg:grid-cols-4"
            >
                <input
                    type="search"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search project or client..."
                    class="min-h-12 rounded-2xl border border-slate-200 px-4 text-sm outline-none focus:border-indigo-400 focus:ring-4 focus:ring-indigo-100"
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
                                request('status') === $status->value
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
                                request('priority') === $priority->value
                            )
                        >
                            {{ $priority->label() }}
                        </option>
                    @endforeach
                </select>

                <select
                    name="deadline"
                    class="min-h-12 rounded-2xl border border-slate-200 px-4 text-sm"
                >
                    <option value="">All deadlines</option>
                    <option
                        value="delayed"
                        @selected(request('deadline') === 'delayed')
                    >
                        Delayed Projects
                    </option>
                    <option
                        value="due_soon"
                        @selected(request('deadline') === 'due_soon')
                    >
                        Due Soon
                    </option>
                </select>

                <select
                    name="client_id"
                    class="min-h-12 rounded-2xl border border-slate-200 px-4 text-sm"
                >
                    <option value="">All clients</option>

                    @foreach ($clients as $client)
                        <option
                            value="{{ $client->id }}"
                            @selected(
                                (string) request('client_id')
                                === (string) $client->id
                            )
                        >
                            {{ $client->display_name }}
                        </option>
                    @endforeach
                </select>

                <select
                    name="category_id"
                    class="min-h-12 rounded-2xl border border-slate-200 px-4 text-sm"
                >
                    <option value="">All categories</option>

                    @foreach ($categories as $category)
                        <option
                            value="{{ $category->id }}"
                            @selected(
                                (string) request('category_id')
                                === (string) $category->id
                            )
                        >
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>

                <select
                    name="manager_id"
                    class="min-h-12 rounded-2xl border border-slate-200 px-4 text-sm"
                >
                    <option value="">All managers</option>

                    @foreach ($managers as $manager)
                        <option
                            value="{{ $manager->id }}"
                            @selected(
                                (string) request('manager_id')
                                === (string) $manager->id
                            )
                        >
                            {{ $manager->name }}
                        </option>
                    @endforeach
                </select>

                <div class="flex gap-2">
                    <button class="min-h-12 flex-1 rounded-2xl bg-indigo-600 px-5 text-sm font-bold text-white">
                        Filter
                    </button>

                    <a
                        href="{{ route('projects.index') }}"
                        class="inline-flex min-h-12 items-center justify-center rounded-2xl border border-slate-200 px-4 text-sm font-bold text-slate-600"
                    >
                        Reset
                    </a>
                </div>
            </form>
        </section>

        <section class="grid gap-4">
            @forelse ($projects as $project)
                <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-indigo-200 hover:shadow-md sm:p-6">
                    <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-full px-3 py-1 text-xs font-bold ring-1 ring-inset {{ $project->status->badgeClasses() }}">
                                    {{ $project->status->label() }}
                                </span>

                                <span class="rounded-full px-3 py-1 text-xs font-bold ring-1 ring-inset {{ $project->priority->badgeClasses() }}">
                                    {{ $project->priority->label() }}
                                </span>

                                @if ($project->is_delayed)
                                    <span class="rounded-full bg-red-600 px-3 py-1 text-xs font-bold text-white">
                                        Delayed
                                    </span>
                                @endif
                            </div>

                            <a
                                href="{{ route('projects.show', $project) }}"
                                class="mt-3 block text-xl font-black text-slate-950 hover:text-indigo-600"
                            >
                                {{ $project->name }}
                            </a>

                            <p class="mt-1 text-sm text-slate-500">
                                {{ $project->project_code }}
                                ·
                                {{ $project->client->display_name }}
                                ·
                                {{ $project->category?->name ?? 'Uncategorised' }}
                            </p>

                            <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                                @can('payments.view')
                                    <div>
                                        <p class="text-xs text-slate-400">
                                            Project Price
                                        </p>

                                        <p class="mt-1 font-bold text-slate-900">
                                            ₹{{ number_format(
                                                $project->project_price,
                                                2
                                            ) }}
                                        </p>
                                    </div>
                                @endcan

                                <div>
                                    <p class="text-xs text-slate-400">
                                        Delivery Date
                                    </p>

                                    <p class="mt-1 font-bold text-slate-900">
                                        {{ $project->deadline?->format('d M Y') }}
                                    </p>

                                    <p class="mt-1 text-xs font-semibold {{ $project->is_delayed ? 'text-red-600' : 'text-slate-500' }}">
                                        {{ $project->deadline_label }}
                                    </p>
                                </div>

                                <div>
                                    <p class="text-xs text-slate-400">
                                        Project Manager
                                    </p>

                                    <p class="mt-1 font-bold text-slate-900">
                                        {{ $project->manager?->name ?? 'Not assigned' }}
                                    </p>
                                </div>

                                <div>
                                    <p class="text-xs text-slate-400">
                                        Team
                                    </p>

                                    <p class="mt-1 font-bold text-slate-900">
                                        {{ $project->team_count }} member(s)
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="w-full rounded-2xl bg-slate-50 p-4 xl:w-60">
                            <div class="flex items-center justify-between">
                                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">
                                    Official Progress
                                </p>

                                <p class="font-black text-slate-900">
                                    {{ $project->official_progress }}%
                                </p>
                            </div>

                            <div class="mt-3 h-2 overflow-hidden rounded-full bg-slate-200">
                                <div
                                    class="h-full rounded-full bg-gradient-to-r from-indigo-500 to-cyan-400"
                                    style="width: {{ $project->official_progress }}%"
                                ></div>
                            </div>

                            <p class="mt-3 text-xs leading-5 text-slate-500">
                                Controlled by client approvals from Phase 3.
                            </p>

                            <a
                                href="{{ route('projects.show', $project) }}"
                                class="mt-4 inline-flex min-h-10 w-full items-center justify-center rounded-xl bg-slate-950 px-4 text-sm font-bold text-white"
                            >
                                Open Project
                            </a>
                        </div>
                    </div>
                </article>
            @empty
                <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-14 text-center">
                    <p class="font-bold text-slate-800">
                        No projects found
                    </p>

                    <p class="mt-1 text-sm text-slate-500">
                        Add a project or change the selected filters.
                    </p>
                </div>
            @endforelse
        </section>

        @if ($projects->hasPages())
            <div>
                {{ $projects->links() }}
            </div>
        @endif
    </div>
@endsection