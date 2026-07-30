@extends('layouts.admin')

@section('title', 'Security Control Centre')
@section('page-title', 'Security Control Centre')

@section('content')
    <div class="space-y-6">
        <section class="rounded-3xl bg-slate-950 p-7 text-white shadow-xl">
            <p class="text-xs font-bold uppercase tracking-[0.22em] text-indigo-300">
                Administrative Security
            </p>

            <h1 class="mt-3 text-3xl font-black">
                Security and Audit Control Centre
            </h1>

            <p class="mt-2 max-w-3xl text-sm leading-7 text-slate-300">
                Review security incidents, authentication history,
                audit integrity, active sessions, permission changes
                and system backup health.
            </p>
        </section>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-6">
            @foreach ([
                [
                    'Open Incidents',
                    $statistics['open_incidents'],
                    'border-amber-200 bg-amber-50'
                ],
                [
                    'Critical Incidents',
                    $statistics['critical_incidents'],
                    'border-red-200 bg-red-50'
                ],
                [
                    'Failed Logins · 24h',
                    $statistics['failed_logins_24h'],
                    'border-orange-200 bg-orange-50'
                ],
                [
                    'Active Sessions',
                    $statistics['active_sessions'],
                    'border-blue-200 bg-blue-50'
                ],
                [
                    'Audit Entries',
                    number_format(
                        $statistics['audit_entries']
                    ),
                    'border-indigo-200 bg-indigo-50'
                ],
                [
                    'Permission Changes · 30d',
                    $statistics['permission_changes_30d'],
                    'border-violet-200 bg-violet-50'
                ],
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

        <section class="grid gap-6 xl:grid-cols-2">
            <article class="rounded-3xl border
                {{
                    $auditIntegrity['valid']
                        ? 'border-emerald-200 bg-emerald-50'
                        : 'border-red-300 bg-red-50'
                }}
                p-6 shadow-sm"
            >
                <p class="text-sm font-bold uppercase tracking-wider
                    {{
                        $auditIntegrity['valid']
                            ? 'text-emerald-700'
                            : 'text-red-700'
                    }}"
                >
                    Audit Integrity
                </p>

                <h2 class="mt-3 text-2xl font-black text-slate-950">
                    {{
                        $auditIntegrity['valid']
                            ? 'Audit chain verified'
                            : 'Audit chain verification failed'
                    }}
                </h2>

                <p class="mt-2 text-sm text-slate-600">
                    {{ number_format(
                        $auditIntegrity['checked']
                    ) }}
                    audit record(s) checked.
                </p>

                @unless ($auditIntegrity['valid'])
                    <pre class="mt-4 overflow-x-auto rounded-2xl bg-red-100 p-4 text-xs text-red-900">{{ json_encode(
                        $auditIntegrity['failure'],
                        JSON_PRETTY_PRINT
                    ) }}</pre>
                @endunless
            </article>

            <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm font-bold uppercase tracking-wider text-slate-500">
                    Backup Health
                </p>

                @if ($lastBackup)
                    <h2 class="mt-3 text-2xl font-black text-slate-950">
                        {{ $lastBackup->status->label() }}
                    </h2>

                    <p class="mt-2 text-sm text-slate-600">
                        Last completed:
                        {{ $lastBackup->completed_at?->format(
                            'd M Y, h:i A'
                        ) }}
                    </p>

                    <p class="mt-1 text-sm text-slate-600">
                        Size:
                        {{ number_format(
                            ($lastBackup->size_bytes ?? 0)
                            / 1024
                            / 1024,
                            2
                        ) }}
                        MB
                    </p>
                @else
                    <h2 class="mt-3 text-2xl font-black text-red-700">
                        No successful backup
                    </h2>
                @endif

                @can('backups.view')
                    <a
                        href="{{ route(
                            'security.backups.index'
                        ) }}"
                        class="mt-5 inline-flex min-h-11 items-center rounded-2xl bg-slate-950 px-5 text-sm font-bold text-white"
                    >
                        Manage Backups
                    </a>
                @endcan
            </article>
        </section>

        <section class="grid gap-6 xl:grid-cols-2">
            <article class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-100 p-5">
                    <h2 class="font-black text-slate-950">
                        Recent Security Incidents
                    </h2>

                    <a
                        href="{{ route(
                            'security.incidents.index'
                        ) }}"
                        class="text-sm font-bold text-indigo-600"
                    >
                        View All
                    </a>
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse ($recentIncidents as $incident)
                        <a
                            href="{{ route(
                                'security.incidents.show',
                                $incident
                            ) }}"
                            class="block p-5 transition hover:bg-slate-50"
                        >
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="font-black text-slate-950">
                                        {{ $incident->title }}
                                    </p>

                                    <p class="mt-1 line-clamp-2 text-sm text-slate-500">
                                        {{ $incident->description }}
                                    </p>
                                </div>

                                <span class="rounded-full px-3 py-1 text-xs font-bold {{ $incident->severity->badgeClasses() }}">
                                    {{ $incident->severity->label() }}
                                </span>
                            </div>
                        </a>
                    @empty
                        <div class="p-10 text-center text-sm text-slate-500">
                            No security incidents.
                        </div>
                    @endforelse
                </div>
            </article>

            <article class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-100 p-5">
                    <h2 class="font-black text-slate-950">
                        Recent Authentication
                    </h2>

                    <a
                        href="{{ route(
                            'security.login-history.index'
                        ) }}"
                        class="text-sm font-bold text-indigo-600"
                    >
                        View All
                    </a>
                </div>

                <div class="divide-y divide-slate-100">
                    @foreach ($recentLogins as $login)
                        <div class="p-5">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="font-black text-slate-950">
                                        {{ $login->authenticatable?->name
                                            ?? $login->identifier_masked
                                            ?? 'Unknown account' }}
                                    </p>

                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ $login->ip_address ?? 'Unknown IP' }}
                                        ·
                                        {{ $login->occurred_at->diffForHumans() }}
                                    </p>
                                </div>

                                <span class="rounded-full px-3 py-1 text-xs font-bold
                                    {{
                                        $login->successful
                                            ? 'bg-emerald-50 text-emerald-700'
                                            : 'bg-red-50 text-red-700'
                                    }}"
                                >
                                    {{ $login->event_type->label() }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </article>
        </section>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
            @foreach ([
                [
                    'Audit Logs',
                    'security.audit.index',
                    auth()->user()->can(
                        'security.view-audit-logs'
                    ),
                ],
                [
                    'Login History',
                    'security.login-history.index',
                    auth()->user()->can(
                        'security.view-login-history'
                    ),
                ],
                [
                    'Active Sessions',
                    'security.sessions.index',
                    auth()->user()->can(
                        'security.view-sessions'
                    ),
                ],
                [
                    'Permission History',
                    'security.permissions.index',
                    auth()->user()->can(
                        'security.view-permission-history'
                    ),
                ],
                [
                    'Backups',
                    'security.backups.index',
                    auth()->user()->can(
                        'backups.view'
                    ),
                ],
            ] as [$label, $routeName, $allowed])
                @if ($allowed)
                    <a
                        href="{{ route($routeName) }}"
                        class="rounded-3xl border border-slate-200 bg-white p-5 font-black text-slate-950 shadow-sm transition hover:-translate-y-0.5 hover:border-indigo-300 hover:text-indigo-600"
                    >
                        {{ $label }} →
                    </a>
                @endif
            @endforeach
        </section>
    </div>
@endsection