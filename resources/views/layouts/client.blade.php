<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >

    <title>
        @yield('title', 'Client Portal')
        · UIPRO
    </title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
    ])

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="min-h-screen bg-slate-50 text-slate-900">
    <header class="sticky top-0 z-40 border-b border-slate-200 bg-white/95 backdrop-blur">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
            <a
                href="{{ route('client.dashboard') }}"
                class="flex items-center gap-3"
            >
                <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-950 text-sm font-black text-white">
                    UI
                </div>

                <div>
                    <p class="font-black text-slate-950">
                        UIPRO Client Portal
                    </p>

                    <p class="text-xs text-slate-500">
                        Project visibility and communication
                    </p>
                </div>
            </a>

            @auth('client')
                <div class="flex items-center gap-3">
                    <a
                        href="{{ route(
                            'client.notifications.index'
                        ) }}"
                        class="relative flex h-11 w-11 items-center justify-center rounded-2xl border border-slate-200 bg-white"
                    >
                        <span>🔔</span>

                        @if (
                            auth('client')
                                ->user()
                                ->unreadNotifications()
                                ->count() > 0
                        )
                            <span class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-red-600 px-1 text-[10px] font-black text-white">
                                {{
                                    min(
                                        99,
                                        auth('client')
                                            ->user()
                                            ->unreadNotifications()
                                            ->count()
                                    )
                                }}
                            </span>
                        @endif
                    </a>

                    <div class="hidden text-right sm:block">
                        <p class="text-sm font-black">
                            {{ auth('client')->user()->name }}
                        </p>

                        <p class="text-xs text-slate-500">
                            {{ auth('client')->user()->client->display_name }}
                        </p>
                    </div>

                    <form
                        method="POST"
                        action="{{ route('client.logout') }}"
                    >
                        @csrf

                        <button class="rounded-xl bg-slate-950 px-4 py-2 text-sm font-bold text-white">
                            Logout
                        </button>
                    </form>
                </div>
            @endauth
        </div>
    </header>

    <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        @if (session('success'))
            <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-bold text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4">
                <p class="font-black text-red-900">
                    Please correct the following:
                </p>

                <ul class="mt-2 space-y-1 text-sm text-red-700">
                    @foreach ($errors->all() as $error)
                        <li>• {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>