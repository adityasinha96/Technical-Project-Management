<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Client Portal Login · UIPRO</title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
    ])
</head>

<body class="min-h-screen bg-slate-950">
    <main class="grid min-h-screen lg:grid-cols-2">
        <section class="hidden bg-gradient-to-br from-indigo-700 via-slate-900 to-slate-950 p-12 text-white lg:flex lg:flex-col lg:justify-between">
            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-lg font-black text-slate-950">
                UI
            </div>

            <div>
                <p class="text-sm font-bold uppercase tracking-[0.25em] text-indigo-300">
                    UIPRO Client Portal
                </p>

                <h1 class="mt-5 max-w-xl text-5xl font-black leading-tight">
                    Clear project visibility without operational complexity.
                </h1>

                <p class="mt-5 max-w-xl text-lg leading-8 text-slate-300">
                    Review progress, approve work, access shared files,
                    view payments and communicate with the project team.
                </p>
            </div>

            <p class="text-sm text-slate-400">
                Secure access for authorised client contacts only.
            </p>
        </section>

        <section class="flex items-center justify-center bg-slate-50 p-5 sm:p-10">
            <div class="w-full max-w-md rounded-3xl border border-slate-200 bg-white p-7 shadow-2xl sm:p-9">
                <p class="text-sm font-bold uppercase tracking-[0.2em] text-indigo-600">
                    Client Login
                </p>

                <h2 class="mt-3 text-3xl font-black text-slate-950">
                    Welcome back
                </h2>

                <p class="mt-2 text-sm text-slate-500">
                    Enter your authorised client portal credentials.
                </p>

                @if (session('status'))
                    <div class="mt-5 rounded-2xl bg-emerald-50 p-4 text-sm font-bold text-emerald-700">
                        {{ session('status') }}
                    </div>
                @endif

                <form
                    method="POST"
                    action="{{ route('client.login.store') }}"
                    class="mt-7 space-y-5"
                >
                    @csrf

                    <label class="block">
                        <span class="mb-2 block text-sm font-bold text-slate-700">
                            Email Address
                        </span>

                        <input
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            autocomplete="email"
                            class="min-h-12 w-full rounded-2xl border border-slate-200 px-4"
                        >
                    </label>

                    <label class="block">
                        <span class="mb-2 block text-sm font-bold text-slate-700">
                            Password
                        </span>

                        <input
                            type="password"
                            name="password"
                            required
                            autocomplete="current-password"
                            class="min-h-12 w-full rounded-2xl border border-slate-200 px-4"
                        >
                    </label>

                    <div class="flex items-center justify-between gap-4">
                        <label class="flex items-center gap-2 text-sm text-slate-600">
                            <input
                                type="checkbox"
                                name="remember"
                                value="1"
                            >

                            Remember me
                        </label>

                        <a
                            href="{{ route(
                                'client.password.request'
                            ) }}"
                            class="text-sm font-bold text-indigo-600"
                        >
                            Forgot password?
                        </a>
                    </div>

                    <button class="min-h-12 w-full rounded-2xl bg-slate-950 px-6 text-sm font-bold text-white">
                        Login to Client Portal
                    </button>
                </form>
            </div>
        </section>
    </main>
</body>
</html>