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
                        Phase 1 Foundation
                    </p>

                    <h2 class="max-w-2xl text-2xl font-black tracking-tight sm:text-3xl">
                        Welcome back, {{ auth()->user()->name }}.
                    </h2>

                    <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-300 sm:text-base">
                        The application foundation, authentication, permission
                        structure and responsive administration interface are now
                        ready for the business modules.
                    </p>
                </div>

                <div class="rounded-3xl border border-white/10 bg-white/5 p-5 backdrop-blur">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">
                                Phase Completion
                            </p>

                            <p class="mt-1 text-3xl font-black">
                                100%
                            </p>
                        </div>

                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-400/15 text-emerald-300">
                            <svg
                                class="h-7 w-7"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path d="M20 6L9 17l-5-5"/>
                            </svg>
                        </div>
                    </div>

                    <div class="mt-4 h-2 overflow-hidden rounded-full bg-white/10">
                        <div class="h-full w-full rounded-full bg-gradient-to-r from-emerald-400 to-cyan-400"></div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Foundation statistics --}}
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium text-slate-500">
                            Registered Users
                        </p>

                        <p class="mt-2 text-3xl font-black text-slate-950">
                            {{ number_format($stats['users']) }}
                        </p>

                        <p class="mt-2 text-xs font-semibold text-emerald-600">
                            {{ $stats['active_users'] }} active
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
                            <path d="M22 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>
                        </svg>
                    </div>
                </div>
            </article>

            <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium text-slate-500">
                            User Roles
                        </p>

                        <p class="mt-2 text-3xl font-black text-slate-950">
                            {{ number_format($stats['roles']) }}
                        </p>

                        <p class="mt-2 text-xs font-semibold text-slate-500">
                            Access levels configured
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
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        </svg>
                    </div>
                </div>
            </article>

            <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium text-slate-500">
                            Permissions
                        </p>

                        <p class="mt-2 text-3xl font-black text-slate-950">
                            {{ number_format($stats['permissions']) }}
                        </p>

                        <p class="mt-2 text-xs font-semibold text-slate-500">
                            Granular controls
                        </p>
                    </div>

                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-50 text-amber-600">
                        <svg
                            class="h-6 w-6"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <circle cx="12" cy="12" r="3"/>
                            <path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06-2.83 2.83-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21h-4v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06-2.83-2.83.06-.06A1.65 1.65 0 004.6 15a1.65 1.65 0 00-1.51-1H3v-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06 2.83-2.83.06.06A1.65 1.65 0 009 4.6a1.65 1.65 0 001-1.51V3h4v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06 2.83 2.83-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21v4h-.09a1.65 1.65 0 00-1.51 1z"/>
                        </svg>
                    </div>
                </div>
            </article>

            <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium text-slate-500">
                            System Settings
                        </p>

                        <p class="mt-2 text-3xl font-black text-slate-950">
                            {{ number_format($stats['settings']) }}
                        </p>

                        <p class="mt-2 text-xs font-semibold text-slate-500">
                            Business rules ready
                        </p>
                    </div>

                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600">
                        <svg
                            class="h-6 w-6"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <path d="M9 11l3 3L22 4"/>
                            <path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/>
                        </svg>
                    </div>
                </div>
            </article>
        </section>

        <section class="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">

            {{-- Foundation checklist --}}
            <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-slate-950">
                            Foundation Checklist
                        </h3>

                        <p class="mt-1 text-sm text-slate-500">
                            Core systems completed during Phase 1.
                        </p>
                    </div>

                    <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">
                        Completed
                    </span>
                </div>

                <div class="mt-6 space-y-3">
                    @foreach ([
                        'Laravel application configured',
                        'MySQL database connected',
                        'Authentication system installed',
                        'Public registration disabled',
                        'Roles and permissions configured',
                        'Super Administrator created',
                        'Inactive-user protection enabled',
                        'Responsive admin layout completed',
                        'Tablet navigation completed',
                        'Project settings foundation created',
                    ] as $item)
                        <div class="flex items-center gap-3 rounded-2xl border border-slate-100 bg-slate-50 p-3">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700">
                                <svg
                                    class="h-4 w-4"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2.5"
                                >
                                    <path d="M20 6L9 17l-5-5"/>
                                </svg>
                            </span>

                            <span class="text-sm font-medium text-slate-700">
                                {{ $item }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </article>

            {{-- Role overview --}}
            <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <h3 class="text-lg font-bold text-slate-950">
                    Access Structure
                </h3>

                <p class="mt-1 text-sm text-slate-500">
                    Initial internal user roles.
                </p>

                <div class="mt-6 space-y-3">
                    @foreach ([
                        [
                            'Super Administrator',
                            'Complete system access',
                            'bg-indigo-50 text-indigo-700'
                        ],
                        [
                            'Project Manager',
                            'Projects, tasks and approvals',
                            'bg-cyan-50 text-cyan-700'
                        ],
                        [
                            'Accounts',
                            'Payments, expenses and reports',
                            'bg-amber-50 text-amber-700'
                        ],
                        [
                            'Team Member',
                            'Assigned work and tickets',
                            'bg-emerald-50 text-emerald-700'
                        ],
                    ] as [$role, $description, $classes])
                        <div class="rounded-2xl border border-slate-100 p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm font-bold text-slate-900">
                                        {{ $role }}
                                    </p>

                                    <p class="mt-1 text-xs leading-5 text-slate-500">
                                        {{ $description }}
                                    </p>
                                </div>

                                <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider {{ $classes }}">
                                    Active
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </article>
        </section>

        {{-- Next phase --}}
        <section class="rounded-3xl border border-indigo-200 bg-gradient-to-br from-indigo-50 to-cyan-50 p-6">
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-indigo-600">
                Next Development Stage
            </p>

            <h3 class="mt-2 text-xl font-black text-slate-950">
                Phase 2: Client and Project Management
            </h3>

            <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">
                The next phase will introduce client profiles, project creation,
                pricing, deadlines, team assignments, project status, project
                URLs, documents and the complete project overview page.
            </p>
        </section>
    </div>
@endsection