<!DOCTYPE html>
<html
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    x-data="{
        sidebarOpen: false,
        sidebarCollapsed: false
    }"
>
<head>
    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >

    <title>
        @yield('title', 'Dashboard') | {{ config('app.name') }}
    </title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])

    @livewireStyles
</head>

<body class="min-h-screen bg-slate-100 text-slate-900 antialiased">

    {{-- Mobile overlay --}}
    <div
        x-cloak
        x-show="sidebarOpen"
        x-transition.opacity
        class="fixed inset-0 z-40 bg-slate-950/60 backdrop-blur-sm lg:hidden"
        @click="sidebarOpen = false"
    ></div>

    {{-- Sidebar --}}
    <aside
        :class="[
            sidebarOpen
                ? 'translate-x-0'
                : '-translate-x-full lg:translate-x-0',

            sidebarCollapsed
                ? 'lg:w-20'
                : 'lg:w-72'
        ]"
        class="fixed inset-y-0 left-0 z-50 flex w-72 flex-col overflow-hidden border-r border-white/10 bg-slate-950 text-white shadow-2xl transition-all duration-300"
    >
        {{-- Brand --}}
        <div class="flex h-20 shrink-0 items-center justify-between border-b border-white/10 px-5">
            <a
                href="{{ route('dashboard') }}"
                class="flex min-w-0 items-center gap-3"
            >
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 to-cyan-400 font-black shadow-lg shadow-indigo-950/50">
                    U
                </div>

                <div
                    x-show="!sidebarCollapsed"
                    x-transition.opacity
                    class="min-w-0"
                >
                    <p class="truncate text-sm font-bold tracking-wide">
                        UIPRO PMS
                    </p>

                    <p class="truncate text-xs text-slate-400">
                        Project Management
                    </p>
                </div>
            </a>

            <button
                type="button"
                class="rounded-xl p-2 text-slate-400 transition hover:bg-white/10 hover:text-white lg:hidden"
                @click="sidebarOpen = false"
                aria-label="Close sidebar"
            >
                <svg
                    class="h-5 w-5"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path d="M6 6l12 12M18 6L6 18"/>
                </svg>
            </button>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 overflow-y-auto px-3 py-5">
            <p
                x-show="!sidebarCollapsed"
                class="px-3 pb-2 text-[11px] font-bold uppercase tracking-[0.22em] text-slate-500"
            >
                Workspace
            </p>

            {{-- Dashboard --}}
            @can('dashboard.view')
                <a
                    href="{{ route('dashboard') }}"
                    class="flex items-center gap-3 rounded-2xl px-3 py-3 text-sm font-semibold transition
                        {{ request()->routeIs('dashboard')
                            ? 'bg-gradient-to-r from-indigo-500/25 to-cyan-400/10 text-white ring-1 ring-indigo-400/30'
                            : 'text-slate-400 hover:bg-white/5 hover:text-white' }}"
                >
                    <span
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl transition
                            {{ request()->routeIs('dashboard')
                                ? 'bg-indigo-500 text-white shadow-lg shadow-indigo-950/40'
                                : 'bg-white/5 text-slate-400' }}"
                    >
                        <svg
                            class="h-5 w-5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <path d="M3 13h8V3H3v10zM13 21h8V11h-8v10zM3 21h8v-6H3v6zM13 9h8V3h-8v6z"/>
                        </svg>
                    </span>

                    <span
                        x-show="!sidebarCollapsed"
                        x-transition.opacity
                    >
                        Dashboard
                    </span>
                </a>
            @endcan

            {{-- Clients --}}
            @can('clients.view')
                <a
                    href="{{ route('clients.index') }}"
                    class="mt-1 flex items-center gap-3 rounded-2xl px-3 py-3 text-sm font-semibold transition
                        {{ request()->routeIs('clients.*')
                            ? 'bg-white/10 text-white'
                            : 'text-slate-400 hover:bg-white/5 hover:text-white' }}"
                >
                    <span
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl transition
                            {{ request()->routeIs('clients.*')
                                ? 'bg-indigo-500/20 text-indigo-300'
                                : 'bg-white/5 text-slate-400' }}"
                    >
                        <svg
                            class="h-5 w-5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path d="M22 21v-2a4 4 0 00-3-3.87"/>
                        </svg>
                    </span>

                    <span
                        x-show="!sidebarCollapsed"
                        x-transition.opacity
                    >
                        Clients
                    </span>
                </a>
            @endcan

            {{-- Projects --}}
            @can('projects.view')
                <a
                    href="{{ route('projects.index') }}"
                    class="mt-1 flex items-center gap-3 rounded-2xl px-3 py-3 text-sm font-semibold transition
                        {{ request()->routeIs('projects.*')
                            ? 'bg-white/10 text-white'
                            : 'text-slate-400 hover:bg-white/5 hover:text-white' }}"
                >
                    <span
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl transition
                            {{ request()->routeIs('projects.*')
                                ? 'bg-cyan-500/20 text-cyan-300'
                                : 'bg-white/5 text-slate-400' }}"
                    >
                        <svg
                            class="h-5 w-5"
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
                    </span>

                    <span
                        x-show="!sidebarCollapsed"
                        x-transition.opacity
                    >
                        Projects
                    </span>
                </a>
            @endcan

            {{-- Project Templates --}}
            @can('templates.view')
                <a
                    href="{{ route('project-templates.index') }}"
                    class="mt-1 flex items-center gap-3 rounded-2xl px-3 py-3 text-sm font-semibold transition
                        {{ request()->routeIs('project-templates.*')
                            ? 'bg-white/10 text-white'
                            : 'text-slate-400 hover:bg-white/5 hover:text-white' }}"
                >
                    <span
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl transition
                            {{ request()->routeIs('project-templates.*')
                                ? 'bg-violet-500/20 text-violet-300'
                                : 'bg-white/5 text-slate-400' }}"
                    >
                        <svg
                            class="h-5 w-5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <path d="M4 4h16v4H4z"/>
                            <path d="M4 10h16v10H4z"/>
                            <path d="M8 14h8M8 17h5"/>
                        </svg>
                    </span>

                    <span
                        x-show="!sidebarCollapsed"
                        x-transition.opacity
                    >
                        Project Templates
                    </span>
                </a>
            @endcan


            {{-- Tickets --}}
            @can('tickets.view')
                <a
                    href="{{ route('tickets.index') }}"
                    class="mt-1 flex items-center gap-3 rounded-2xl px-3 py-3 text-sm font-semibold transition
                        {{ request()->routeIs('tickets.*')
                            ? 'bg-white/10 text-white'
                            : 'text-slate-400 hover:bg-white/5 hover:text-white' }}"
                >
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white/5">
                        <svg
                            class="h-5 w-5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <path d="M4 5h16v14H4z"/>
                            <path d="M8 9h8M8 13h5"/>
                        </svg>
                    </span>

                    <span x-show="!sidebarCollapsed">
                        Tickets
                    </span>
                </a>
            @endcan

            {{-- Ticket Escalations --}}
            @can('tickets.view-escalations')
                <a
                    href="{{ route('tickets.escalations') }}"
                    class="mt-1 flex items-center gap-3 rounded-2xl px-3 py-3 text-sm font-semibold transition
                        {{ request()->routeIs('tickets.escalations')
                            ? 'bg-white/10 text-white'
                            : 'text-slate-400 hover:bg-white/5 hover:text-white' }}"
                >
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-red-500/10 text-red-300">
                        !
                    </span>

                    <span x-show="!sidebarCollapsed">
                        Ticket Escalations
                    </span>
                </a>
            @endcan

            {{-- Notifications --}}
            @can('notifications.view')
                <a
                    href="{{ route(
                        'notifications.index'
                    ) }}"
                    class="mt-1 flex items-center gap-3 rounded-2xl px-3 py-3 text-sm font-semibold transition
                        {{
                            request()->routeIs(
                                'notifications.*'
                            )
                                ? 'bg-white/10 text-white'
                                : 'text-slate-400 hover:bg-white/5 hover:text-white'
                        }}"
                >
                    <span class="relative flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white/5">
                        <svg
                            class="h-5 w-5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <path d="M18 8a6 6 0 10-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/>
                            <path d="M10 21h4"/>
                        </svg>

                        @if (
                            ($headerUnreadNotificationCount ?? 0)
                            > 0
                        )
                            <span class="absolute -right-1 -top-1 h-3 w-3 rounded-full bg-red-500"></span>
                        @endif
                    </span>

                    <span x-show="!sidebarCollapsed">
                        Notifications
                    </span>
                </a>
            @endcan

            {{-- Notification Preferences --}}
            @can('notifications.manage-preferences')
                <a
                    href="{{ route(
                        'notification-settings.edit'
                    ) }}"
                    class="mt-1 flex items-center gap-3 rounded-2xl px-3 py-3 text-sm font-semibold text-slate-400 transition hover:bg-white/5 hover:text-white"
                >
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white/5">
                        ⚙
                    </span>

                    <span x-show="!sidebarCollapsed">
                        Notification Preferences
                    </span>
                </a>
            @endcan

            {{-- Reminder Rules --}}
            @can('notifications.manage-rules')
                <a
                    href="{{ route(
                        'notification-rules.index'
                    ) }}"
                    class="mt-1 flex items-center gap-3 rounded-2xl px-3 py-3 text-sm font-semibold text-slate-400 transition hover:bg-white/5 hover:text-white"
                >
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-500/10 text-indigo-300">
                        ⏱
                    </span>

                    <span x-show="!sidebarCollapsed">
                        Reminder Rules
                    </span>
                </a>
            @endcan

            {{-- Payments --}}
            @can('payments.view')
                <a
                    href="{{ route('payments.index') }}"
                    class="mt-1 flex items-center gap-3 rounded-2xl px-3 py-3 text-sm font-semibold transition
                        {{ request()->routeIs(
                            'payments.index',
                            'payments.show'
                        )
                            ? 'bg-white/10 text-white'
                            : 'text-slate-400 hover:bg-white/5 hover:text-white' }}"
                >
                    <span
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl transition
                            {{ request()->routeIs(
                                'payments.index',
                                'payments.show'
                            )
                                ? 'bg-emerald-500/20 text-emerald-300'
                                : 'bg-white/5 text-slate-400' }}"
                    >
                        <svg
                            class="h-5 w-5"
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
                    </span>

                    <span
                        x-show="!sidebarCollapsed"
                        x-transition.opacity
                    >
                        Payments
                    </span>
                </a>

                <a
                    href="{{ route('payments.outstanding') }}"
                    class="mt-1 flex items-center gap-3 rounded-2xl px-3 py-3 text-sm font-semibold transition
                        {{ request()->routeIs('payments.outstanding')
                            ? 'bg-white/10 text-white'
                            : 'text-slate-400 hover:bg-white/5 hover:text-white' }}"
                >
                    <span
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl font-black transition
                            {{ request()->routeIs('payments.outstanding')
                                ? 'bg-amber-500/20 text-amber-300'
                                : 'bg-amber-500/10 text-amber-300' }}"
                    >
                        ₹
                    </span>

                    <span
                        x-show="!sidebarCollapsed"
                        x-transition.opacity
                    >
                        Market Outstanding
                    </span>
                </a>
            @endcan

            {{-- Reports and Analytics --}}
            @can('reports.view')
                <a
                    href="{{ route('reports.index') }}"
                    class="mt-1 flex items-center gap-3 rounded-2xl px-3 py-3 text-sm font-semibold transition
                        {{
                            request()->routeIs(
                                'reports.index',
                                'reports.projects',
                                'reports.team',
                                'reports.collections',
                                'reports.profitability',
                                'reports.ticket-sla'
                            )
                                ? 'bg-white/10 text-white'
                                : 'text-slate-400 hover:bg-white/5 hover:text-white'
                        }}"
                >
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-500/10 text-indigo-300">
                        <svg
                            class="h-5 w-5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <path d="M4 19V9"/>
                            <path d="M10 19V5"/>
                            <path d="M16 19v-7"/>
                            <path d="M22 19V3"/>
                        </svg>
                    </span>

                    <span x-show="!sidebarCollapsed">
                        Reports & Analytics
                    </span>
                </a>
            @endcan

            {{-- Report Exports --}}
            @can('reports.view-export-history')
                <a
                    href="{{ route('reports.exports') }}"
                    class="mt-1 flex items-center gap-3 rounded-2xl px-3 py-3 text-sm font-semibold transition
                        {{
                            request()->routeIs(
                                'reports.exports'
                            )
                                ? 'bg-white/10 text-white'
                                : 'text-slate-400 hover:bg-white/5 hover:text-white'
                        }}"
                >
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white/5">
                        ↓
                    </span>

                    <span x-show="!sidebarCollapsed">
                        Report Exports
                    </span>
                </a>
            @endcan

            {{-- Future modules --}}
            <div class="mt-4 space-y-1">
                <p
                    x-show="!sidebarCollapsed"
                    class="px-3 pb-1 text-[10px] font-bold uppercase tracking-[0.2em] text-slate-600"
                >
                    Upcoming Modules
                </p>

                @foreach ([
                    ['Tasks & Approvals', 'Phase 3'],
                    ['Expenses', 'Phase 5'],
                    ['Project Notes', 'Phase 6'],
                    ['Tickets', 'Phase 7'],
                    ['Reports', 'Phase 9'],
                ] as [$module, $phase])
                    <div
                        class="flex cursor-not-allowed items-center gap-3 rounded-2xl px-3 py-2.5 text-sm text-slate-500"
                        title="{{ $phase }}"
                    >
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white/5">
                            <span class="h-2.5 w-2.5 rounded-full bg-slate-600"></span>
                        </span>

                        <div
                            x-show="!sidebarCollapsed"
                            x-transition.opacity
                            class="min-w-0"
                        >
                            <p class="truncate">
                                {{ $module }}
                            </p>

                            <p class="text-[10px] uppercase tracking-wider text-slate-600">
                                {{ $phase }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        </nav>

        {{-- Sidebar user --}}
        <div class="border-t border-white/10 p-3">
            <div class="flex items-center gap-3 rounded-2xl bg-white/5 p-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-amber-400 to-orange-500 text-sm font-black text-slate-950">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>

                <div
                    x-show="!sidebarCollapsed"
                    x-transition.opacity
                    class="min-w-0 flex-1"
                >
                    <p class="truncate text-sm font-semibold">
                        {{ auth()->user()->name }}
                    </p>

                    <p class="truncate text-xs text-slate-400">
                        {{
                            ucwords(
                                str_replace(
                                    '-',
                                    ' ',
                                    auth()->user()->getRoleNames()->first() ?? 'User'
                                )
                            )
                        }}
                    </p>
                </div>

                <form
                    x-show="!sidebarCollapsed"
                    method="POST"
                    action="{{ route('logout') }}"
                >
                    @csrf

                    <button
                        type="submit"
                        class="rounded-xl p-2 text-slate-400 transition hover:bg-red-500/15 hover:text-red-300"
                        aria-label="Logout"
                    >
                        <svg
                            class="h-5 w-5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <path d="M10 17l5-5-5-5M15 12H3"/>
                            <path d="M14 3h5a2 2 0 012 2v14a2 2 0 01-2 2h-5"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- Main content --}}
    <div
        :class="sidebarCollapsed ? 'lg:pl-20' : 'lg:pl-72'"
        class="min-h-screen transition-all duration-300"
    >
        {{-- Top bar --}}
        <header class="sticky top-0 z-30 border-b border-slate-200/80 bg-white/85 backdrop-blur-xl">
            <div class="flex h-20 items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
                <div class="flex min-w-0 items-center gap-3">
                    <button
                        type="button"
                        class="rounded-xl border border-slate-200 bg-white p-2.5 text-slate-600 shadow-sm transition hover:bg-slate-50 lg:hidden"
                        @click="sidebarOpen = true"
                        aria-label="Open sidebar"
                    >
                        <svg
                            class="h-5 w-5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>

                    <button
                        type="button"
                        class="hidden rounded-xl border border-slate-200 bg-white p-2.5 text-slate-600 shadow-sm transition hover:bg-slate-50 lg:block"
                        @click="sidebarCollapsed = !sidebarCollapsed"
                        aria-label="Collapse sidebar"
                    >
                        <svg
                            class="h-5 w-5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>

                    <div class="min-w-0">
                        <h1 class="truncate text-lg font-bold text-slate-950 sm:text-xl">
                            @yield('page-title', 'Dashboard')
                        </h1>

                        <p class="hidden text-xs text-slate-500 sm:block">
                            {{ now()->format('l, d F Y') }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <div class="hidden rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700 sm:block">
                        System Operational
                    </div>

                    @can('notifications.view')
                        <div
                            x-data="{ open: false }"
                            class="relative"
                        >
                            <button
                                type="button"
                                @click="open = !open"
                                class="relative flex h-11 w-11 items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-600 transition hover:border-indigo-300 hover:text-indigo-600"
                                aria-label="Open notifications"
                            >
                                <svg
                                    class="h-5 w-5"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                >
                                    <path d="M18 8a6 6 0 10-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/>
                                    <path d="M10 21h4"/>
                                </svg>

                                @if ($headerUnreadNotificationCount > 0)
                                    <span class="absolute -right-1 -top-1 flex min-h-5 min-w-5 items-center justify-center rounded-full bg-red-600 px-1 text-[10px] font-black text-white">
                                        {{ $headerUnreadNotificationCount > 99
                                            ? '99+'
                                            : $headerUnreadNotificationCount }}
                                    </span>
                                @endif
                            </button>

                            <div
                                x-show="open"
                                x-cloak
                                x-transition
                                @click.outside="open = false"
                                class="absolute right-0 z-50 mt-3 w-[min(92vw,390px)] overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl"
                            >
                                <div class="flex items-center justify-between border-b border-slate-100 p-4">
                                    <div>
                                        <p class="font-black text-slate-950">
                                            Notifications
                                        </p>

                                        <p class="text-xs text-slate-500">
                                            {{ $headerUnreadNotificationCount }}
                                            unread
                                        </p>
                                    </div>

                                    <a
                                        href="{{ route(
                                            'notifications.index'
                                        ) }}"
                                        class="text-xs font-bold text-indigo-600"
                                    >
                                        View All
                                    </a>
                                </div>

                                <div class="max-h-[420px] overflow-y-auto">
                                    @forelse ($headerNotifications as $notification)
                                        @php
                                            $data = $notification->data;
                                            $severity = $data['severity'] ?? 'info';
                                        @endphp

                                        <a
                                            href="{{ route(
                                                'notifications.open',
                                                $notification
                                            ) }}"
                                            class="block border-b border-slate-100 p-4 transition hover:bg-slate-50
                                                {{ $notification->read_at
                                                    ? ''
                                                    : 'bg-indigo-50/40' }}"
                                        >
                                            <div class="flex gap-3">
                                                <span
                                                    class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full {{
                                                        match ($severity) {
                                                            'success' => 'bg-emerald-500',
                                                            'warning' => 'bg-amber-500',
                                                            'danger' => 'bg-orange-500',
                                                            'critical' => 'bg-red-600',
                                                            default => 'bg-blue-500',
                                                        }
                                                    }}"
                                                ></span>

                                                <div class="min-w-0">
                                                    <p class="text-sm font-black text-slate-950">
                                                        {{ $data['title']
                                                            ?? 'Notification' }}
                                                    </p>

                                                    <p class="mt-1 line-clamp-2 text-xs leading-5 text-slate-600">
                                                        {{ $data['message']
                                                            ?? '' }}
                                                    </p>

                                                    <p class="mt-2 text-[11px] text-slate-400">
                                                        {{ $notification
                                                            ->created_at
                                                            ->diffForHumans() }}
                                                    </p>
                                                </div>
                                            </div>
                                        </a>
                                    @empty
                                        <div class="p-10 text-center text-sm text-slate-500">
                                            No notifications available.
                                        </div>
                                    @endforelse
                                </div>

                                <div class="flex border-t border-slate-100">
                                    <a
                                        href="{{ route(
                                            'notifications.index'
                                        ) }}"
                                        class="flex-1 px-4 py-3 text-center text-xs font-bold text-indigo-600"
                                    >
                                        Notification Centre
                                    </a>

                                    @can('notifications.manage-preferences')
                                        <a
                                            href="{{ route(
                                                'notification-settings.edit'
                                            ) }}"
                                            class="flex-1 border-l border-slate-100 px-4 py-3 text-center text-xs font-bold text-slate-600"
                                        >
                                            Preferences
                                        </a>
                                    @endcan
                                </div>
                            </div>
                        </div>
                    @endcan
                </div>
            </div>
        </header>

        <main class="p-4 sm:p-6 lg:p-8">
            @if (session('success'))
                <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    <p class="font-bold">
                        Please correct the following errors:
                    </p>

                    <ul class="mt-2 list-inside list-disc space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    @livewireScripts
    @stack('scripts')
</body>
</html>

