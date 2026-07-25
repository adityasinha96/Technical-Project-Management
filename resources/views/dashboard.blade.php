@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Business Dashboard')

@section('content')
@php
    $formatDashboardMinutes = function (int $minutes): string {
        $hours = intdiv($minutes, 60);
        $remaining = $minutes % 60;

        return $hours > 0
            ? "{$hours}h {$remaining}m"
            : "{$remaining}m";
    };
@endphp

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
                        project values, task progress, approvals, payments,
                        outstanding balances and estimated profitability from
                        one central workspace.
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


        {{-- Phase 8 notification statistics --}}
        @can('notifications.view')
            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <article class="rounded-3xl border border-indigo-200 bg-indigo-50 p-5">
                    <p class="text-sm font-medium text-indigo-700">
                        Unread Notifications
                    </p>

                    <p class="mt-2 text-3xl font-black text-indigo-950">
                        {{ $notificationStats['unread'] }}
                    </p>
                </article>

                <article class="rounded-3xl border border-red-200 bg-red-50 p-5">
                    <p class="text-sm font-medium text-red-700">
                        Critical Unread Alerts
                    </p>

                    <p class="mt-2 text-3xl font-black text-red-950">
                        {{ $notificationStats['critical_unread'] }}
                    </p>
                </article>

                @can('notifications.view-delivery-history')
                    <article class="rounded-3xl border border-amber-200 bg-amber-50 p-5">
                        <p class="text-sm font-medium text-amber-700">
                            Queued Emails
                        </p>

                        <p class="mt-2 text-3xl font-black text-amber-950">
                            {{ $notificationStats['queued_email'] }}
                        </p>
                    </article>

                    <article class="rounded-3xl border border-orange-200 bg-orange-50 p-5">
                        <p class="text-sm font-medium text-orange-700">
                            Failed Deliveries
                        </p>

                        <p class="mt-2 text-3xl font-black text-orange-950">
                            {{ $notificationStats['failed_delivery'] }}
                        </p>
                    </article>
                @endcan
            </section>
        @endcan


        {{-- Phase 7 ticket statistics --}}
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
            @foreach ([
                ['Open Tickets', $ticketStats['open'], 'border-blue-200 bg-blue-50'],
                ['Assigned to Me', $ticketStats['assigned_to_me'], 'border-indigo-200 bg-indigo-50'],
                ['Unassigned', $ticketStats['unassigned'], 'border-amber-200 bg-amber-50'],
                ['Escalated', $ticketStats['escalated'], 'border-red-200 bg-red-50'],
                ['Critical', $ticketStats['critical'], 'border-violet-200 bg-violet-50'],
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

        {{-- Phase 7 SLA risk panel --}}
        <section class="overflow-hidden rounded-3xl border border-red-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-red-100 bg-red-50 p-5">
                <div>
                    <h3 class="font-black text-red-950">
                        Tickets at SLA Risk
                    </h3>

                    <p class="mt-1 text-sm text-red-700">
                        Escalated tickets and tickets approaching resolution deadline.
                    </p>
                </div>

                @can('tickets.view-escalations')
                    <a
                        href="{{ route('tickets.escalations') }}"
                        class="text-sm font-bold text-red-700"
                    >
                        View Escalations
                    </a>
                @endcan
            </div>

            <div class="divide-y divide-slate-100">
                @forelse ($slaRiskTickets as $ticket)
                    <a
                        href="{{ route('tickets.show', $ticket) }}"
                        class="block p-5 transition hover:bg-red-50/40"
                    >
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="font-bold text-slate-950">
                                    {{ $ticket->subject }}
                                </p>

                                <p class="mt-1 text-xs text-slate-500">
                                    {{ $ticket->ticket_number }}
                                    · {{ $ticket->project->name }}
                                    · {{ $ticket->assignedTo?->name ?? 'Unassigned' }}
                                </p>
                            </div>

                            <div class="text-right">
                                @if ($ticket->escalation_level > 0)
                                    <span class="rounded-full bg-red-600 px-3 py-1 text-xs font-bold text-white">
                                        Level {{ $ticket->escalation_level }}
                                    </span>
                                @endif

                                <p class="mt-2 text-xs font-bold text-red-700">
                                    {{ $ticket->current_sla_due_at?->diffForHumans() }}
                                </p>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="p-10 text-center">
                        <p class="font-bold text-emerald-700">
                            No tickets are currently at SLA risk.
                        </p>
                    </div>
                @endforelse
            </div>
        </section>

        {{-- Phase 6 work logs, pinned notes and project activity statistics --}}
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <article class="rounded-3xl border border-indigo-200 bg-indigo-50 p-5 shadow-sm">
                <p class="text-sm font-medium text-indigo-700">
                    Team Work Logged Today
                </p>

                <p class="mt-2 text-3xl font-black text-indigo-950">
                    {{ $formatDashboardMinutes(
                        $todayWorkMinutes
                    ) }}
                </p>
            </article>

            <article class="rounded-3xl border border-cyan-200 bg-cyan-50 p-5 shadow-sm">
                <p class="text-sm font-medium text-cyan-700">
                    My Work Logged Today
                </p>

                <p class="mt-2 text-3xl font-black text-cyan-950">
                    {{ $formatDashboardMinutes(
                        $myTodayWorkMinutes
                    ) }}
                </p>
            </article>

            <article class="rounded-3xl border border-amber-200 bg-amber-50 p-5 shadow-sm">
                <p class="text-sm font-medium text-amber-700">
                    Pinned Project Notes
                </p>

                <p class="mt-2 text-3xl font-black text-amber-950">
                    {{ $pinnedNoteCount }}
                </p>
            </article>

            <article class="rounded-3xl border border-red-200 bg-red-50 p-5 shadow-sm">
                <p class="text-sm font-medium text-red-700">
                    Inactive Projects
                </p>

                <p class="mt-2 text-3xl font-black text-red-950">
                    {{ $inactiveProjects->count() }}
                </p>

                <p class="mt-1 text-xs font-bold text-red-700">
                    No activity for
                    {{ $projectInactivityDays }}+ days
                </p>
            </article>
        </section>

        {{-- Phase 6 recent activity and inactive project panels --}}
        <section class="grid gap-6 xl:grid-cols-2">
            <article class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 p-5">
                    <h3 class="font-black text-slate-950">
                        Recent Project Activity
                    </h3>

                    <p class="mt-1 text-sm text-slate-500">
                        Latest changes across accessible projects.
                    </p>
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse ($recentActivities as $activity)
                        <a
                            href="{{ route('projects.show', [
                                'project' => $activity->project,
                                'tab' => 'history',
                            ]) }}"
                            class="block p-5 transition hover:bg-slate-50"
                        >
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <p class="font-bold text-slate-950">
                                        {{ $activity->title }}
                                    </p>

                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ $activity->project?->name ?? 'Project unavailable' }}
                                        ·
                                        {{ $activity->actor?->name ?? 'System' }}
                                    </p>
                                </div>

                                <p class="shrink-0 text-xs text-slate-500">
                                    {{ $activity->occurred_at?->diffForHumans() ?? 'Unknown time' }}
                                </p>
                            </div>
                        </a>
                    @empty
                        <div class="p-10 text-center text-sm text-slate-500">
                            No recent project activity.
                        </div>
                    @endforelse
                </div>
            </article>

            <article class="overflow-hidden rounded-3xl border border-red-200 bg-white shadow-sm">
                <div class="border-b border-red-100 bg-red-50 p-5">
                    <h3 class="font-black text-red-950">
                        Inactive Projects
                    </h3>

                    <p class="mt-1 text-sm text-red-700">
                        Projects without recorded activity for
                        {{ $projectInactivityDays }} or more days.
                    </p>
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse ($inactiveProjects as $inactiveProject)
                        <a
                            href="{{ route(
                                'projects.show',
                                $inactiveProject
                            ) }}"
                            class="block p-5 transition hover:bg-red-50/40"
                        >
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <p class="font-bold text-slate-950">
                                        {{ $inactiveProject->name }}
                                    </p>

                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ $inactiveProject->client?->display_name ?? 'No client assigned' }}
                                    </p>

                                    <p class="mt-2 text-xs text-slate-400">
                                        Manager:

                                        <span class="font-semibold text-slate-600">
                                            {{ $inactiveProject->manager?->name ?? 'Not assigned' }}
                                        </span>
                                    </p>
                                </div>

                                <p class="shrink-0 text-xs font-bold text-red-700">
                                    {{ (
                                        $inactiveProject->last_activity_at
                                        ?: $inactiveProject->created_at
                                    )?->diffForHumans() ?? 'Unknown' }}
                                </p>
                            </div>
                        </a>
                    @empty
                        <div class="p-10 text-center">
                            <p class="font-bold text-emerald-700">
                                All active projects have recent activity
                            </p>
                        </div>
                    @endforelse
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

        @can('payments.view')
            {{-- Phase 4 payment and collection statistics --}}
            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">

                {{-- Total payment received --}}
                <article class="rounded-3xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-emerald-700">
                                Total Payment Received
                            </p>

                            <p class="mt-2 break-words text-2xl font-black text-emerald-950 sm:text-3xl">
                                ₹{{ number_format(
                                    (float) $stats['total_received'],
                                    2
                                ) }}
                            </p>

                            <p class="mt-2 text-xs font-semibold text-emerald-700">
                                Net payment received across projects
                            </p>
                        </div>

                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700">
                            <svg
                                class="h-6 w-6"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <rect
                                    x="3"
                                    y="5"
                                    width="18"
                                    height="14"
                                    rx="2"
                                />

                                <path d="M3 10h18M7 15h2"/>
                            </svg>
                        </div>
                    </div>
                </article>

                {{-- Current month collection --}}
                <article class="rounded-3xl border border-indigo-200 bg-indigo-50 p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-indigo-700">
                                Current Month Collection
                            </p>

                            <p class="mt-2 break-words text-2xl font-black text-indigo-950 sm:text-3xl">
                                ₹{{ number_format(
                                    (float) $stats['current_month_collection'],
                                    2
                                ) }}
                            </p>

                            <p class="mt-2 text-xs font-semibold text-indigo-700">
                                Receipts minus refunds this month
                            </p>
                        </div>

                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-indigo-100 text-indigo-700">
                            <svg
                                class="h-6 w-6"
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

                {{-- Market outstanding --}}
                <article class="rounded-3xl border border-amber-200 bg-amber-50 p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-amber-700">
                                Market Outstanding
                            </p>

                            <p class="mt-2 break-words text-2xl font-black text-amber-950 sm:text-3xl">
                                ₹{{ number_format(
                                    (float) $stats['market_outstanding'],
                                    2
                                ) }}
                            </p>

                            <p class="mt-2 text-xs font-bold text-amber-700">
                                {{ number_format(
                                    $stats['projects_with_pending']
                                ) }}
                                projects with pending balance
                            </p>
                        </div>

                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-amber-100 text-xl font-black text-amber-700">
                            ₹
                        </div>
                    </div>
                </article>

                {{-- Overdue payment follow-ups --}}
                <article class="rounded-3xl border border-red-200 bg-red-50 p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-medium text-red-700">
                                Overdue Payment Follow-ups
                            </p>

                            <p class="mt-2 text-3xl font-black text-red-950">
                                {{ number_format(
                                    $stats['overdue_payment_followups']
                                ) }}
                            </p>

                            <p class="mt-2 text-xs font-semibold text-red-700">
                                Collection follow-ups requiring action
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
                                <circle cx="12" cy="12" r="9"/>
                                <path d="M12 7v5l3 2"/>
                                <path d="M17.5 17.5l2 2"/>
                            </svg>
                        </div>
                    </div>
                </article>
            </section>

            {{-- Outstanding balances and overdue payment follow-ups --}}
            <section class="grid gap-6 xl:grid-cols-2">

                {{-- Highest pending balances --}}
                <article class="overflow-hidden rounded-3xl border border-amber-200 bg-white shadow-sm">
                    <div class="flex items-center justify-between gap-4 border-b border-amber-100 bg-amber-50 p-5">
                        <div>
                            <h3 class="font-bold text-amber-950">
                                Highest Pending Balances
                            </h3>

                            <p class="mt-1 text-sm text-amber-700">
                                Projects requiring collection action.
                            </p>
                        </div>

                        <a
                            href="{{ route('payments.outstanding') }}"
                            class="shrink-0 text-sm font-bold text-amber-800 transition hover:text-amber-950"
                        >
                            View All
                        </a>
                    </div>

                    <div class="divide-y divide-slate-100">
                        @forelse ($outstandingProjects as $project)
                            <a
                                href="{{ route('projects.show', [
                                    'project' => $project,
                                    'tab' => 'payments',
                                ]) }}"
                                class="block p-5 transition hover:bg-amber-50/40"
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

                                    <p class="shrink-0 font-black text-amber-700">
                                        ₹{{ number_format(
                                            (float) $project->pending_amount,
                                            2
                                        ) }}
                                    </p>
                                </div>

                                <div class="mt-4">
                                    <div class="flex items-center justify-between gap-3 text-xs">
                                        <span class="font-semibold text-slate-500">
                                            Received
                                        </span>

                                        <span class="font-bold text-emerald-700">
                                            ₹{{ number_format(
                                                (float) $project->net_received_amount,
                                                2
                                            ) }}
                                        </span>
                                    </div>

                                    <div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-100">
                                        <div
                                            class="h-full rounded-full bg-gradient-to-r from-emerald-500 to-cyan-500"
                                            style="width: {{ $project->collection_bar_percentage }}%"
                                        ></div>
                                    </div>
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
                                    No pending balances
                                </p>

                                <p class="mt-1 text-sm text-slate-500">
                                    All project balances are currently cleared.
                                </p>
                            </div>
                        @endforelse
                    </div>
                </article>

                {{-- Overdue collection follow-ups --}}
                <article class="overflow-hidden rounded-3xl border border-red-200 bg-white shadow-sm">
                    <div class="border-b border-red-100 bg-red-50 p-5">
                        <h3 class="font-bold text-red-950">
                            Overdue Collection Follow-ups
                        </h3>

                        <p class="mt-1 text-sm text-red-700">
                            Client follow-ups that have crossed their scheduled time.
                        </p>
                    </div>

                    <div class="divide-y divide-slate-100">
                        @forelse ($overduePaymentFollowups as $followup)
                            <a
                                href="{{ route('projects.show', [
                                    'project' => $followup->project,
                                    'tab' => 'payments',
                                ]) }}"
                                class="block p-5 transition hover:bg-red-50/40"
                            >
                                <div class="flex items-start justify-between gap-4">
                                    <div class="min-w-0">
                                        <p class="truncate font-bold text-slate-950">
                                            {{ $followup->project?->name ?? 'Project unavailable' }}
                                        </p>

                                        <p class="mt-1 truncate text-xs text-slate-500">
                                            {{ $followup->client?->display_name ?? 'No client assigned' }}

                                            @if ($followup->channel)
                                                · {{ $followup->channel->label() }}
                                            @endif
                                        </p>

                                        <p class="mt-2 text-xs text-slate-400">
                                            Assigned to:

                                            <span class="font-semibold text-slate-600">
                                                {{ $followup->assignedTo?->name ?? 'Not assigned' }}
                                            </span>
                                        </p>
                                    </div>

                                    @if ($followup->due_at)
                                        <span class="shrink-0 rounded-full bg-red-100 px-3 py-1 text-xs font-bold text-red-700">
                                            {{ $followup->due_at->diffForHumans() }}
                                        </span>
                                    @endif
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
                                    No overdue follow-ups
                                </p>

                                <p class="mt-1 text-sm text-slate-500">
                                    All payment follow-ups are currently within schedule.
                                </p>
                            </div>
                        @endforelse
                    </div>
                </article>
            </section>
        @endcan

        {{-- Phase 5 monthly profitability statistics --}}
        @can('reports.profitability')
            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <article class="rounded-3xl border border-indigo-200 bg-indigo-50 p-5 shadow-sm">
                    <p class="text-sm font-medium text-indigo-700">
                        Monthly Collection
                    </p>

                    <p class="mt-2 text-3xl font-black text-indigo-950">
                        ₹{{ number_format(
                            (float) $currentMonthFinancials['collection'],
                            2
                        ) }}
                    </p>
                </article>

                <article class="rounded-3xl border border-red-200 bg-red-50 p-5 shadow-sm">
                    <p class="text-sm font-medium text-red-700">
                        Monthly Expenses
                    </p>

                    <p class="mt-2 text-3xl font-black text-red-950">
                        ₹{{ number_format(
                            (float) $currentMonthFinancials['total_expenses'],
                            2
                        ) }}
                    </p>
                </article>

                <article
                    class="rounded-3xl border {{
                        (float) $currentMonthFinancials['cash_profit'] >= 0
                            ? 'border-emerald-200 bg-emerald-50'
                            : 'border-red-200 bg-red-50'
                    }} p-5 shadow-sm"
                >
                    <p
                        class="text-sm font-medium {{
                            (float) $currentMonthFinancials['cash_profit'] >= 0
                                ? 'text-emerald-700'
                                : 'text-red-700'
                        }}"
                    >
                        Monthly Cash Profit
                    </p>

                    <p
                        class="mt-2 text-3xl font-black {{
                            (float) $currentMonthFinancials['cash_profit'] >= 0
                                ? 'text-emerald-950'
                                : 'text-red-950'
                        }}"
                    >
                        ₹{{ number_format(
                            (float) $currentMonthFinancials['cash_profit'],
                            2
                        ) }}
                    </p>
                </article>

                <article
                    class="rounded-3xl border {{
                        (float) $profitabilitySummary['business_cash_position'] >= 0
                            ? 'border-cyan-200 bg-cyan-50'
                            : 'border-red-200 bg-red-50'
                    }} p-5 shadow-sm"
                >
                    <p
                        class="text-sm font-medium {{
                            (float) $profitabilitySummary['business_cash_position'] >= 0
                                ? 'text-cyan-700'
                                : 'text-red-700'
                        }}"
                    >
                        Business Cash Position
                    </p>

                    <p
                        class="mt-2 text-3xl font-black {{
                            (float) $profitabilitySummary['business_cash_position'] >= 0
                                ? 'text-cyan-950'
                                : 'text-red-950'
                        }}"
                    >
                        ₹{{ number_format(
                            (float) $profitabilitySummary['business_cash_position'],
                            2
                        ) }}
                    </p>
                </article>
            </section>

            {{-- Phase 5 profitability risk alerts --}}
            <section class="grid gap-6 xl:grid-cols-2">
                {{-- Loss-making projects --}}
                <article class="overflow-hidden rounded-3xl border border-red-200 bg-white shadow-sm">
                    <div class="border-b border-red-100 bg-red-50 p-5">
                        <h3 class="font-bold text-red-950">
                            Loss-Making Projects
                        </h3>

                        <p class="mt-1 text-sm text-red-700">
                            Actual project expenses have exceeded the contracted project price.
                        </p>
                    </div>

                    <div class="divide-y divide-slate-100">
                        @forelse ($lossMakingProjects as $project)
                            <a
                                href="{{ route('projects.show', [
                                    'project' => $project,
                                    'tab' => 'expenses',
                                ]) }}"
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
                                    </div>

                                    <p class="shrink-0 font-black text-red-700">
                                        ₹{{ number_format(
                                            (float) $project->actual_profit_amount,
                                            2
                                        ) }}
                                    </p>
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
                                    No loss-making projects
                                </p>
                            </div>
                        @endforelse
                    </div>
                </article>

                {{-- Cash-negative projects --}}
                <article class="overflow-hidden rounded-3xl border border-amber-200 bg-white shadow-sm">
                    <div class="border-b border-amber-100 bg-amber-50 p-5">
                        <h3 class="font-bold text-amber-950">
                            Cash-Negative Projects
                        </h3>

                        <p class="mt-1 text-sm text-amber-700">
                            Project expenses are currently higher than payment collected.
                        </p>
                    </div>

                    <div class="divide-y divide-slate-100">
                        @forelse ($cashNegativeProjects as $project)
                            <a
                                href="{{ route('projects.show', [
                                    'project' => $project,
                                    'tab' => 'expenses',
                                ]) }}"
                                class="block p-5 transition hover:bg-amber-50/40"
                            >
                                <div class="flex items-start justify-between gap-4">
                                    <div class="min-w-0">
                                        <p class="truncate font-bold text-slate-950">
                                            {{ $project->name }}
                                        </p>

                                        <p class="mt-1 text-xs text-slate-500">
                                            Received:
                                            ₹{{ number_format(
                                                (float) $project->net_received_amount,
                                                2
                                            ) }}

                                            · Expenses:
                                            ₹{{ number_format(
                                                (float) $project->project_expense_amount,
                                                2
                                            ) }}
                                        </p>
                                    </div>

                                    <p class="shrink-0 font-black text-red-700">
                                        ₹{{ number_format(
                                            (float) $project->cash_position_amount,
                                            2
                                        ) }}
                                    </p>
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
                                    No cash-negative projects
                                </p>
                            </div>
                        @endforelse
                    </div>
                </article>
            </section>
        @endcan

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

