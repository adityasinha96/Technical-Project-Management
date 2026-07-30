@extends('layouts.admin')

@section('title', 'Active Sessions')
@section('page-title', 'Active Sessions')

@section('content')
    @php
        $sessionValue = static function (
            $session,
            string ...$keys
        ) {
            foreach ($keys as $key) {
                $value =
                    $session->getAttribute(
                        $key
                    );

                if (
                    $value !== null
                    && $value !== ''
                ) {
                    return $value;
                }
            }

            return null;
        };

        $formatDate = static function (
            $value
        ): string {
            if (
                $value instanceof
                \DateTimeInterface
            ) {
                return $value->format(
                    'd M Y, h:i:s A'
                );
            }

            return $value
                ? (string) $value
                : '—';
        };
    @endphp

    <div class="space-y-6">
        <section class="rounded-3xl bg-slate-950 p-7 text-white shadow-xl">
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-indigo-300">
                Authentication Security
            </p>

            <div class="mt-3 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="text-3xl font-black">
                        Security Sessions
                    </h1>

                    <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-300">
                        Review active and revoked staff or client sessions and terminate suspicious access.
                    </p>
                </div>

                <a
                    href="{{ route('security.index') }}"
                    class="inline-flex min-h-11 items-center justify-center rounded-2xl border border-white/15 bg-white/10 px-5 text-sm font-bold text-white transition hover:bg-white/15"
                >
                    Back to Security Centre
                </a>
            </div>
        </section>

        <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <form
                method="GET"
                action="{{ route('security.sessions.index') }}"
                class="grid gap-4 md:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto]"
            >
                <div>
                    <label
                        for="status"
                        class="mb-2 block text-sm font-bold text-slate-700"
                    >
                        Session Status
                    </label>

                    <select
                        id="status"
                        name="status"
                        class="min-h-11 w-full rounded-2xl border border-slate-300 bg-white px-4 text-sm text-slate-900 outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                    >
                        <option
                            value="active"
                            @selected($status === 'active')
                        >
                            Active
                        </option>

                        <option
                            value="revoked"
                            @selected($status === 'revoked')
                        >
                            Revoked
                        </option>

                        <option
                            value="all"
                            @selected($status === 'all')
                        >
                            All Sessions
                        </option>
                    </select>
                </div>

                <div>
                    <label
                        for="guard"
                        class="mb-2 block text-sm font-bold text-slate-700"
                    >
                        Authentication Guard
                    </label>

                    <select
                        id="guard"
                        name="guard"
                        class="min-h-11 w-full rounded-2xl border border-slate-300 bg-white px-4 text-sm text-slate-900 outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                    >
                        <option value="">
                            All Guards
                        </option>

                        <option
                            value="web"
                            @selected($guard === 'web')
                        >
                            Staff / Web
                        </option>

                        <option
                            value="client"
                            @selected($guard === 'client')
                        >
                            Client Portal
                        </option>
                    </select>
                </div>

                <div class="flex items-end gap-3">
                    <button
                        type="submit"
                        class="inline-flex min-h-11 items-center justify-center rounded-2xl bg-slate-950 px-5 text-sm font-bold text-white transition hover:bg-slate-800"
                    >
                        Apply
                    </button>

                    <a
                        href="{{ route('security.sessions.index') }}"
                        class="inline-flex min-h-11 items-center justify-center rounded-2xl border border-slate-300 bg-white px-5 text-sm font-bold text-slate-700 transition hover:bg-slate-50"
                    >
                        Reset
                    </a>
                </div>
            </form>
        </section>

        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-5 py-4 sm:px-6">
                <h2 class="text-lg font-black text-slate-950">
                    Recorded Sessions
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    {{ number_format($securitySessions->total()) }} session record(s) found.
                </p>
            </div>

            @if ($securitySessions->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500 sm:px-6">
                                    Session
                                </th>

                                <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">
                                    Actor
                                </th>

                                <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">
                                    Guard
                                </th>

                                <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">
                                    Logged In
                                </th>

                                <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">
                                    Last Seen
                                </th>

                                <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">
                                    Status
                                </th>

                                <th class="px-5 py-4 text-right text-xs font-black uppercase tracking-wider text-slate-500 sm:px-6">
                                    Action
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach ($securitySessions as $session)
                                @php
                                    $revoked =
                                        $session->revoked_at
                                        !== null;

                                    $actorType =
                                        class_basename(
                                            (string) (
                                                $session->actor_type
                                                ?? 'Unknown'
                                            )
                                        );

                                    $ipAddress =
                                        $sessionValue(
                                            $session,
                                            'ip_address',
                                            'ip'
                                        );
                                @endphp

                                <tr class="align-top transition hover:bg-slate-50/80">
                                    <td class="min-w-64 px-5 py-4 sm:px-6">
                                        <p class="text-sm font-black text-slate-950">
                                            #{{ $session->getKey() }}
                                        </p>

                                        <p class="mt-1 break-all font-mono text-[11px] leading-5 text-slate-400">
                                            {{ $session->session_uuid }}
                                        </p>

                                        @if ($ipAddress)
                                            <p class="mt-2 text-xs font-semibold text-slate-500">
                                                IP: {{ $ipAddress }}
                                            </p>
                                        @endif
                                    </td>

                                    <td class="min-w-48 px-5 py-4">
                                        <p class="text-sm font-bold text-slate-900">
                                            {{ $actorType }}
                                        </p>

                                        <p class="mt-1 text-xs text-slate-500">
                                            Actor ID:
                                            {{ $session->actor_id ?? '—' }}
                                        </p>
                                    </td>

                                    <td class="whitespace-nowrap px-5 py-4">
                                        <span class="inline-flex rounded-full bg-indigo-50 px-3 py-1 text-xs font-bold text-indigo-700 ring-1 ring-inset ring-indigo-100">
                                            {{
                                                \Illuminate\Support\Str::headline(
                                                    (string) $session->guard
                                                )
                                            }}
                                        </span>
                                    </td>

                                    <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-600">
                                        {{ $formatDate($session->logged_in_at) }}
                                    </td>

                                    <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-600">
                                        {{ $formatDate($session->last_seen_at) }}
                                    </td>

                                    <td class="whitespace-nowrap px-5 py-4">
                                        @if ($revoked)
                                            <span class="inline-flex rounded-full bg-red-100 px-3 py-1 text-xs font-bold text-red-700 ring-1 ring-inset ring-red-200">
                                                Revoked
                                            </span>

                                            <p class="mt-2 text-xs text-slate-400">
                                                {{ $formatDate($session->revoked_at) }}
                                            </p>
                                        @else
                                            <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-700 ring-1 ring-inset ring-emerald-200">
                                                Active
                                            </span>
                                        @endif
                                    </td>

                                    <td class="min-w-64 px-5 py-4 text-right sm:px-6">
                                        @if (!$revoked)
                                            @can('security.revoke-sessions')
                                                <form
                                                    method="POST"
                                                    action="{{ route(
                                                        'security.sessions.destroy',
                                                        $session
                                                    ) }}"
                                                    class="space-y-2"
                                                    onsubmit="return confirm('Revoke this security session?');"
                                                >
                                                    @csrf
                                                    @method('DELETE')

                                                    <input
                                                        type="text"
                                                        name="reason"
                                                        maxlength="2000"
                                                        placeholder="Revocation reason"
                                                        class="min-h-10 w-full rounded-xl border border-slate-300 bg-white px-3 text-xs text-slate-900 outline-none transition focus:border-red-500 focus:ring-4 focus:ring-red-100"
                                                    >

                                                    <button
                                                        type="submit"
                                                        class="inline-flex min-h-10 items-center justify-center rounded-xl bg-red-600 px-4 text-xs font-black text-white transition hover:bg-red-700"
                                                    >
                                                        Revoke Session
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-xs font-semibold text-slate-400">
                                                    View only
                                                </span>
                                            @endcan
                                        @else
                                            <span class="text-xs font-semibold text-slate-400">
                                                No action available
                                            </span>
                                        @endif
                                    </td>
                                </tr>

                                @if (
                                    $sessionValue(
                                        $session,
                                        'user_agent',
                                        'revocation_reason',
                                        'revoked_reason'
                                    )
                                )
                                    <tr class="bg-slate-50/50">
                                        <td></td>

                                        <td
                                            colspan="6"
                                            class="px-5 pb-4 pt-2 text-xs leading-5 text-slate-500 sm:px-6"
                                        >
                                            <span class="font-bold text-slate-700">
                                                Details:
                                            </span>

                                            {{
                                                $sessionValue(
                                                    $session,
                                                    'revocation_reason',
                                                    'revoked_reason',
                                                    'user_agent'
                                                )
                                            }}
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-slate-200 px-5 py-4 sm:px-6">
                    {{ $securitySessions->links() }}
                </div>
            @else
                <div class="px-6 py-16 text-center">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600">
                        <svg
                            class="h-7 w-7"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <rect x="3" y="5" width="18" height="14" rx="2"/>
                            <path d="M7 9h10M7 13h6"/>
                        </svg>
                    </div>

                    <h2 class="mt-4 text-xl font-black text-slate-950">
                        No security sessions found
                    </h2>

                    <p class="mx-auto mt-2 max-w-xl text-sm leading-6 text-slate-500">
                        No session records match the selected status and guard filters.
                    </p>
                </div>
            @endif
        </section>
    </div>
@endsection

