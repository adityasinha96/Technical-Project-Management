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

            <a
                href="{{ route('dashboard') }}"
                class="flex items-center gap-3 rounded-2xl bg-gradient-to-r from-indigo-500/25 to-cyan-400/10 px-3 py-3 text-sm font-semibold text-white ring-1 ring-indigo-400/30"
            >
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-500 text-white shadow-lg shadow-indigo-950/40">
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

                <span x-show="!sidebarCollapsed">
                    Dashboard
                </span>
            </a>

            <div class="mt-4 space-y-1">
                @foreach ([
                    ['Clients', 'Phase 2'],
                    ['Projects', 'Phase 2'],
                    ['Tasks & Approvals', 'Phase 3'],
                    ['Payments', 'Phase 4'],
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

                    <button
                        type="button"
                        class="relative rounded-xl border border-slate-200 bg-white p-2.5 text-slate-600 shadow-sm transition hover:bg-slate-50"
                        aria-label="Notifications"
                    >
                        <svg
                            class="h-5 w-5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <path d="M18 8a6 6 0 00-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/>
                            <path d="M10 21h4"/>
                        </svg>

                        <span class="absolute right-2 top-2 h-2 w-2 rounded-full bg-rose-500"></span>
                    </button>
                </div>
            </div>
        </header>

        <main class="p-4 sm:p-6 lg:p-8">
            @if (session('success'))
                <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                    {{ session('success') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    @livewireScripts
    @stack('scripts')
</body>
</html>