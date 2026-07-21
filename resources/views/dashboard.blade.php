@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Business Dashboard')

@section('content')
    <div class="space-y-6">

        {{-- Welcome banner --}}
        <section class="relative overflow-hidden rounded-3xl bg-slate-950 p-6 text-white shadow-2xl shadow-slate-300 sm:p-8">
            <div class="absolute -right-20 -top-20 h-72 w-72 rounded-full bg-indigo-500/30 blur-3xl"></div>

            <div class="absolute -bottom-24 right-20 h-64 w-64 rounded-full bg-cyan-400/20 blur-3xl"></div>

            <div class="relative grid gap-6 lg:grid-cols-[1.3fr_0.7fr] lg:items-center">
                <div>
                    <p class="mb-3 inline-flex rounded-full border border-white/10 bg-white/5 px-3 py-1 text-xs font-bold uppercase tracking-[0.18em] text-cyan-300">
                        Project Management Overview
                    </p>

                    <h2 class="max-w-2xl text-2xl font-black tracking-tight sm:text-3xl">
                        Welcome back, {{ auth()->user()->name }}.
                    </h2>

                    <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-300 sm:text-base">
                        Monitor clients, active projects, delivery deadlines,
                        project values, task progress, approvals and estimated
                        profitability from one central workspace.
                    </p>

                    <div class="mt-6 flex flex-wrap gap-3">
                        @can('projects.create')
                            <a
                                href="{{ route('projects.create') }}"
                                class="inline-flex items-center justify-center gap-2 rounded-2xl bg-indigo-500 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-indigo-950/40 transition hover:bg-indigo-400"
                            >
                                <svg
                                    class="h-4 w-4"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path d="M12 5v14M5 12h14"/>
                                </svg>

                                Add Project
                            </a>
                        @endcan

                        @can('clients.create')
                            <a
                                href="{{ route('clients.create') }}"
                                class="inline-flex items-center justify-center gap-2 rounded-2xl border border-white/15 bg-white/5 px-4 py-3 text-sm font-bold text-white transition hover:bg-white/10"
                            >
                                <svg
                                    class="h-4 w-4"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2"/>
                                    <circle cx="9" cy="7" r="4"/>
                                    <path d="M19 8v6M22 11h-6"/>
                                </svg>

                                Add Client
                            </a>
                        @endcan
                    </div>
                </div>

                <div class="rounded-3xl border border-white/10 bg-white/5 p-5 backdrop-blur">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">
                                Active Workload
                            </p>

                            <p class="mt-1 text-3xl font-black">
                                {{ number_format($stats['active_projects']) }}
                            </p>

                            <p class="mt-1 text-xs text-slate-400">
                                Projects currently open
                            </p>
                        </div>

                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-cyan-400/15 text-cyan-300">
                            <svg
                                class="h-7 w-7"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <rect
                                    x="3"
                                    y="7"
                                    width="18"
                                    height="13"
                                    rx="2"
                                />

                                <path d="M8 7V5a2 2 0 012-2h4a2 2 0 012 2v2M3 12h18"/>
                            </svg>
                        </div>
                    </div>

                    <div class="mt-5 grid grid-cols-2 gap-3">
                        <div class="rounded-2xl bg-white/5 p-3">
                            <p class="text-xs text-slate-400">
                                Completed
                            </p>

                            <p class="mt-1 text-lg font-black">
                                {{ number_format($stats['completed_projects']) }}
                            </p>
                        </div>

                        <div class="rounded-2xl bg-white/5 p-3">
                            <p class="text-xs text-slate-400">
                                Delayed
                            </p>

                            <p class="mt-1 text-lg font-black text-red-300">
                                {{ number_format($stats['delayed_projects']) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Primary statistics --}}
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">

            {{-- Total clients --}}
            <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium text-slate-500">
                            Total Clients
                        </p>

                        <p class="mt-2 text-3xl font-black text-slate-950">
                            {{ number_format($stats['total_clients']) }}
                        </p>

                        <p class="mt-2 text-xs font-semibold text-emerald-600">
                            {{ number_format($stats['active_clients']) }}
                            active clients
                        </p>
                    </div>

                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600">
                        <svg
                            class="h-6 w-6"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path d="M22 21v-2a4 4 0 00-3-3.87"/>
                        </svg>
                    </div>
                </div>
            </article>

            {{-- Active projects --}}
            <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium text-slate-500">
                            Active Projects
                        </p>

                        <p class="mt-2 text-3xl font-black text-slate-950">
                            {{ number_format($stats['active_projects']) }}
                        </p>

                        <p class="mt-2 text-xs font-semibold text-slate-500">
                            {{ number_format($stats['total_projects']) }}
                            total projects
                        </p>
                    </div>

                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-cyan-50 text-cyan-600">
                        <svg
                            class="h-6 w-6"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <rect
                                x="3"
                                y="7"
                                width="18"
                                height="13"
                                rx="2"
                            />

                            <path d="M8 7V5a2 2 0 012-2h4a2 2 0 012 2v2M3 12h18"/>
                        </svg>
                    </div>
                </div>
            </article>

            {{-- Delayed projects --}}
            <article class="rounded-3xl border border-red-200 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium text-slate-500">
                            Delayed Projects
                        </p>

                        <p class="mt-2 text-3xl font-black text-red-700">
                            {{ number_format($stats['delayed_projects']) }}
                        </p>

                        <p class="mt-2 text-xs font-semibold text-red-600">
                            Require immediate attention
                        </p>
                    </div>

                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-red-50 text-red-600">
                        <svg
                            class="h-6 w-6"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <circle cx="12" cy="12" r="9"/>
                            <path d="M12 7v6M12 17h.01"/>
                        </svg>
                    </div>
                </div>
            </article>

            {{-- Project value --}}
            <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-slate-500">
                            Total Project Value
                        </p>

                        <p class="mt-2 break-words text-2xl font-black text-slate-950 sm:text-3xl">
                            ₹{{ number_format(
                                (float) $stats['total_project_value'],
                                2
                            ) }}
                        </p>

                        <p class="mt-2 text-xs font-semibold text-slate-500">
                            Combined project pricing
                        </p>
                    </div>

                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600">
                        <svg
                            class="h-6 w-6"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <path d="M6 3h12M6 8h12M9 3c4 0 6 2 6 5s-2 5-6 5H6l9 8"/>
                        </svg>
                    </div>
                </div>
            </article>
        </section>

        {{-- Phase 3 task and approval statistics --}}
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">

            {{-- Pending approvals --}}
            <article class="rounded-3xl border border-cyan-200 bg-cyan-50 p-5 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium text-cyan-700">
                            Pending Approvals
                        </p>

                        <p class="mt-2 text-3xl font-black text-cyan-950">
                            {{ number_format($stats['pending_approvals']) }}
                        </p>

                        <p class="mt-2 text-xs font-semibold text-cyan-700">
                            Awaiting approval workflow action
                        </p>
                    </div>

                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-cyan-100 text-cyan-700">
                        <svg
                            class="h-6 w-6"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <circle cx="12" cy="12" r="9"/>
                            <path d="M12 7v5l3 2"/>
                        </svg>
                    </div>
                </div>
            </article>

            {{-- Overdue tasks --}}
            <article class="rounded-3xl border border-red-200 bg-red-50 p-5 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium text-red-700">
                            Overdue Tasks
                        </p>

                        <p class="mt-2 text-3xl font-black text-red-950">
                            {{ number_format($stats['overdue_tasks']) }}
                        </p>

                        <p class="mt-2 text-xs font-semibold text-red-700">
                            Tasks beyond their due dates
                        </p>
                    </div>

                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-red-100 text-red-700">
                        <svg
                            class="h-6 w-6"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <path d="M12 8v5"/>
                            <path d="M12 17h.01"/>
                            <path d="M10.3 3.8L2.7 17a2 2 0 001.7 3h15.2a2 2 0 001.7-3L13.7 3.8a2 2 0 00-3.4 0z"/>
                        </svg>
                    </div>
                </div>
            </article>

            {{-- Blocked tasks --}}
            <article class="rounded-3xl border border-amber-200 bg-amber-50 p-5 shadow-sm sm:col-span-2 xl:col-span-1">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium text-amber-700">
                            Blocked Tasks
                        </p>

                        <p class="mt-2 text-3xl font-black text-amber-950">
                            {{ number_format($stats['blocked_tasks']) }}
                        </p>

                        <p class="mt-2 text-xs font-semibold text-amber-700">
                            Tasks waiting on blockers
                        </p>
                    </div>

                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-amber-100 text-amber-700">
                        <svg
                            class="h-6 w-6"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <circle cx="12" cy="12" r="9"/>
                            <path d="M6.5 6.5l11 11"/>
                        </svg>
                    </div>
                </div>
            </article>
        </section>

        {{-- Financial and completion summary --}}
        <section class="grid gap-4 sm:grid-cols-2">

            {{-- Estimated profit --}}
            <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium text-slate-500">
                            Estimated Profit
                        </p>

                        <p class="mt-2 text-2xl font-black text-emerald-700 sm:text-3xl">
                            ₹{{ number_format(
                                (float) $stats['estimated_profit'],
                                2
                            ) }}
                        </p>

                        <p class="mt-2 text-xs text-slate-500">
                            Project value minus estimated project cost
                        </p>
                    </div>

                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600">
                        <svg
                            class="h-7 w-7"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <path d="M4 19V9M10 19V5M16 19v-7M22 19H2"/>
                            <path d="M4 9l6-4 6 7 6-5"/>
                        </svg>
                    </div>
                </div>
            </article>

            {{-- Completed projects --}}
            <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium text-slate-500">
                            Completed Projects
                        </p>

                        <p class="mt-2 text-2xl font-black text-indigo-700 sm:text-3xl">
                            {{ number_format($stats['completed_projects']) }}
                        </p>

                        <p class="mt-2 text-xs text-slate-500">
                            Projects successfully marked as completed
                        </p>
                    </div>

                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600">
                        <svg
                            class="h-7 w-7"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <circle cx="12" cy="12" r="9"/>
                            <path d="M8 12l3 3 5-6"/>
                        </svg>
                    </div>
                </div>
            </article>
        </section>

        {{-- Project lists --}}
        <section class="grid gap-6 xl:grid-cols-2">

            {{-- Delayed projects --}}
            <article class="overflow-hidden rounded-3xl border border-red-200 bg-white shadow-sm">
                <div class="flex items-center justify-between gap-4 border-b border-red-100 bg-red-50 p-5">
                    <div>
                        <h3 class="font-bold text-red-950">
                            Delayed Projects
                        </h3>

                        <p class="mt-1 text-sm text-red-700">
                            Projects requiring immediate attention.
                        </p>
                    </div>

                    <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-black text-red-700">
                        {{ number_format($stats['delayed_projects']) }}
                    </span>
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse ($delayedProjects as $project)
                        <a
                            href="{{ route('projects.show', $project) }}"
                            class="block p-5 transition hover:bg-red-50/40"
                        >
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <p class="truncate font-bold text-slate-950">
                                        {{ $project->name }}
                                    </p>

                                    <p class="mt-1 truncate text-xs text-slate-500">
                                        {{ $project->client?->display_name ?? 'No client assigned' }}
                                    </p>

                                    <p class="mt-2 text-xs text-slate-400">
                                        Manager:

                                        <span class="font-semibold text-slate-600">
                                            {{ $project->manager?->name ?? 'Not assigned' }}
                                        </span>
                                    </p>
                                </div>

                                <span class="shrink-0 rounded-full bg-red-100 px-3 py-1 text-xs font-bold text-red-700">
                                    {{ $project->deadline_label }}
                                </span>
                            </div>
                        </a>
                    @empty
                        <div class="p-10 text-center">
                            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600">
                                <svg
                                    class="h-6 w-6"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path d="M20 6L9 17l-5-5"/>
                                </svg>
                            </div>

                            <p class="mt-3 font-bold text-emerald-700">
                                No delayed projects
                            </p>

                            <p class="mt-1 text-sm text-slate-500">
                                All active projects are currently within their deadlines.
                            </p>
                        </div>
                    @endforelse
                </div>
            </article>

            {{-- Recently added projects --}}
            <article class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center justify-between gap-4 border-b border-slate-100 p-5">
                    <div>
                        <h3 class="font-bold text-slate-950">
                            Recently Added Projects
                        </h3>

                        <p class="mt-1 text-sm text-slate-500">
                            Latest projects added to the system.
                        </p>
                    </div>

                    @can('projects.view')
                        <a
                            href="{{ route('projects.index') }}"
                            class="shrink-0 text-xs font-bold text-indigo-600 transition hover:text-indigo-800"
                        >
                            View all
                        </a>
                    @endcan
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse ($recentProjects as $project)
                        <a
                            href="{{ route('projects.show', $project) }}"
                            class="block p-5 transition hover:bg-slate-50"
                        >
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <p class="truncate font-bold text-slate-950">
                                        {{ $project->name }}
                                    </p>

                                    <p class="mt-1 truncate text-xs text-slate-500">
                                        {{ $project->client?->display_name ?? 'No client assigned' }}
                                    </p>

                                    <p class="mt-2 text-xs text-slate-400">
                                        Manager:

                                        <span class="font-semibold text-slate-600">
                                            {{ $project->manager?->name ?? 'Not assigned' }}
                                        </span>
                                    </p>
                                </div>

                                <span class="shrink-0 rounded-full px-3 py-1 text-xs font-bold {{ $project->status->badgeClasses() }}">
                                    {{ $project->status->label() }}
                                </span>
                            </div>
                        </a>
                    @empty
                        <div class="p-10 text-center">
                            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-500">
                                <svg
                                    class="h-6 w-6"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                >
                                    <rect
                                        x="3"
                                        y="7"
                                        width="18"
                                        height="13"
                                        rx="2"
                                    />

                                    <path d="M8 7V5a2 2 0 012-2h4a2 2 0 012 2v2"/>
                                </svg>
                            </div>

                            <p class="mt-3 font-bold text-slate-700">
                                No projects added
                            </p>

                            <p class="mt-1 text-sm text-slate-500">
                                Create your first project to start tracking work.
                            </p>

                            @can('projects.create')
                                <a
                                    href="{{ route('projects.create') }}"
                                    class="mt-4 inline-flex rounded-xl bg-indigo-600 px-4 py-2 text-sm font-bold text-white transition hover:bg-indigo-700"
                                >
                                    Add Project
                                </a>
                            @endcan
                        </div>
                    @endforelse
                </div>
            </article>
        </section>

        {{-- Client and project shortcuts --}}
        <section class="grid gap-4 sm:grid-cols-2">
            @can('clients.view')
                <a
                    href="{{ route('clients.index') }}"
                    class="group rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-md"
                >
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.16em] text-indigo-600">
                                Client Management
                            </p>

                            <h3 class="mt-2 text-lg font-black text-slate-950">
                                View and manage clients
                            </h3>

                            <p class="mt-2 text-sm leading-6 text-slate-500">
                                Access client profiles, contact details, projects
                                and account status.
                            </p>
                        </div>

                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 transition group-hover:bg-indigo-600 group-hover:text-white">
                            <svg
                                class="h-5 w-5"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path d="M5 12h14M13 6l6 6-6 6"/>
                            </svg>
                        </span>
                    </div>
                </a>
            @endcan

            @can('projects.view')
                <a
                    href="{{ route('projects.index') }}"
                    class="group rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:border-cyan-200 hover:shadow-md"
                >
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.16em] text-cyan-600">
                                Project Management
                            </p>

                            <h3 class="mt-2 text-lg font-black text-slate-950">
                                View and manage projects
                            </h3>

                            <p class="mt-2 text-sm leading-6 text-slate-500">
                                Monitor project pricing, deadlines, teams,
                                tasks, approvals, files and delivery status.
                            </p>
                        </div>

                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-cyan-50 text-cyan-600 transition group-hover:bg-cyan-600 group-hover:text-white">
                            <svg
                                class="h-5 w-5"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path d="M5 12h14M13 6l6 6-6 6"/>
                            </svg>
                        </span>
                    </div>
                </a>
            @endcan
        </section>
    </div>
@endsection