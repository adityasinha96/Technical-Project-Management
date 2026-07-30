@extends('layouts.admin')

@section('title', 'Login History')
@section('page-title', 'Login History')

@section('content')
    @php
        $value = static function ($event, string ...$keys) {
            foreach ($keys as $key) {
                $result = $event->getAttribute($key);

                if ($result !== null && $result !== '') {
                    return $result;
                }
            }

            return null;
        };

        $dateValue = static function ($event) use ($value) {
            return $value(
                $event,
                'occurred_at',
                'attempted_at',
                'logged_in_at',
                'created_at'
            );
        };

        $statusValue = static function ($event) use ($value): string {
            $status = $value(
                $event,
                'status',
                'event_type',
                'event'
            );

            if ($status !== null) {
                return \Illuminate\Support\Str::headline(
                    $status instanceof \BackedEnum
                        ? $status->value
                        : (string) $status
                );
            }

            $successful = $value(
                $event,
                'successful',
                'was_successful',
                'is_successful'
            );

            if ($successful !== null) {
                return filter_var(
                    $successful,
                    FILTER_VALIDATE_BOOLEAN
                )
                    ? 'Successful'
                    : 'Failed';
            }

            return 'Recorded';
        };

        $statusClasses = static function (string $status): string {
            return match (strtolower($status)) {
                'successful', 'success', 'login successful' =>
                    'bg-emerald-100 text-emerald-700 ring-emerald-200',

                'failed', 'failure', 'login failed' =>
                    'bg-red-100 text-red-700 ring-red-200',

                'logout', 'logged out' =>
                    'bg-slate-100 text-slate-700 ring-slate-200',

                default =>
                    'bg-blue-100 text-blue-700 ring-blue-200',
            };
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
                        Login History
                    </h1>

                    <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-300">
                        Review recorded staff and client authentication activity, source addresses, guards and login outcomes.
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

        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-5 py-4 sm:px-6">
                <h2 class="text-lg font-black text-slate-950">
                    Authentication Events
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    {{ number_format($loginEvents->total()) }} record(s) found.
                </p>
            </div>

            @if ($loginEvents->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500 sm:px-6">
                                    ID
                                </th>

                                <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">
                                    Status
                                </th>

                                <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">
                                    User
                                </th>

                                <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">
                                    Guard
                                </th>

                                <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">
                                    IP Address
                                </th>

                                <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500 sm:px-6">
                                    Date
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach ($loginEvents as $event)
                                @php
                                    $status =
                                        $statusValue($event);

                                    $date =
                                        $dateValue($event);

                                    $actorName =
                                        $value(
                                            $event,
                                            'actor_name',
                                            'user_name',
                                            'name'
                                        );

                                    $actorEmail =
                                        $value(
                                            $event,
                                            'actor_email',
                                            'email',
                                            'login'
                                        );
                                @endphp

                                <tr class="transition hover:bg-slate-50/80">
                                    <td class="whitespace-nowrap px-5 py-4 text-sm font-black text-slate-950 sm:px-6">
                                        #{{ $event->getKey() }}
                                    </td>

                                    <td class="whitespace-nowrap px-5 py-4">
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold ring-1 ring-inset {{ $statusClasses($status) }}">
                                            {{ $status }}
                                        </span>
                                    </td>

                                    <td class="min-w-52 px-5 py-4">
                                        <p class="text-sm font-semibold text-slate-900">
                                            {{ $actorName ?: 'Unknown user' }}
                                        </p>

                                        <p class="mt-1 text-xs text-slate-400">
                                            {{ $actorEmail ?: 'No email recorded' }}
                                        </p>
                                    </td>

                                    <td class="whitespace-nowrap px-5 py-4 text-sm font-semibold text-slate-700">
                                        {{
                                            \Illuminate\Support\Str::headline(
                                                (string) (
                                                    $value(
                                                        $event,
                                                        'guard'
                                                    ) ?? 'unknown'
                                                )
                                            )
                                        }}
                                    </td>

                                    <td class="whitespace-nowrap px-5 py-4 font-mono text-xs text-slate-700">
                                        {{
                                            $value(
                                                $event,
                                                'ip_address',
                                                'ip'
                                            ) ?? '—'
                                        }}
                                    </td>

                                    <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-600 sm:px-6">
                                        @if ($date instanceof \DateTimeInterface)
                                            {{ $date->format('d M Y, h:i:s A') }}
                                        @else
                                            {{ $date ?: '—' }}
                                        @endif
                                    </td>
                                </tr>

                                @if (
                                    $value(
                                        $event,
                                        'user_agent',
                                        'failure_reason',
                                        'reason'
                                    )
                                )
                                    <tr class="bg-slate-50/50">
                                        <td></td>

                                        <td
                                            colspan="5"
                                            class="px-5 pb-4 pt-2 text-xs leading-5 text-slate-500 sm:px-6"
                                        >
                                            <span class="font-bold text-slate-700">
                                                Details:
                                            </span>

                                            {{
                                                $value(
                                                    $event,
                                                    'failure_reason',
                                                    'reason',
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
                    {{ $loginEvents->links() }}
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
                            <circle cx="12" cy="8" r="4"/>
                            <path d="M4 21a8 8 0 0116 0"/>
                            <path d="M17 3l2 2 3-3"/>
                        </svg>
                    </div>

                    <h2 class="mt-4 text-xl font-black text-slate-950">
                        No login history found
                    </h2>

                    <p class="mx-auto mt-2 max-w-xl text-sm leading-6 text-slate-500">
                        Authentication events will appear here after login, logout or failed-login activity is recorded.
                    </p>
                </div>
            @endif
        </section>
    </div>
@endsection

