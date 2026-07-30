@extends('layouts.admin')

@section('title', 'Security Incidents')
@section('page-title', 'Security Incidents')

@section('content')
    @php
        $enumValue = static function ($value): string {
            if ($value instanceof \BackedEnum) {
                return (string) $value->value;
            }

            return (string) ($value ?? '');
        };

        $enumLabel = static function ($value) use ($enumValue): string {
            if (
                is_object($value)
                && method_exists(
                    $value,
                    'label'
                )
            ) {
                return (string) $value->label();
            }

            return \Illuminate\Support\Str::headline(
                $enumValue($value)
            );
        };

        $severityClasses = static function (
            $severity
        ) use ($enumValue): string {
            return match (
                $enumValue($severity)
            ) {
                'critical' =>
                    'bg-red-100 text-red-700 ring-red-200',

                'high' =>
                    'bg-orange-100 text-orange-700 ring-orange-200',

                'warning' =>
                    'bg-amber-100 text-amber-700 ring-amber-200',

                default =>
                    'bg-blue-100 text-blue-700 ring-blue-200',
            };
        };

        $statusClasses = static function (
            $status
        ) use ($enumValue): string {
            return match (
                $enumValue($status)
            ) {
                'open' =>
                    'bg-red-100 text-red-700 ring-red-200',

                'acknowledged' =>
                    'bg-amber-100 text-amber-700 ring-amber-200',

                'resolved' =>
                    'bg-emerald-100 text-emerald-700 ring-emerald-200',

                'dismissed' =>
                    'bg-slate-100 text-slate-700 ring-slate-200',

                default =>
                    'bg-indigo-100 text-indigo-700 ring-indigo-200',
            };
        };
    @endphp

    <div class="space-y-6">
        <section class="rounded-3xl bg-slate-950 p-7 text-white shadow-xl">
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-red-300">
                Security Monitoring
            </p>

            <div class="mt-3 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="text-3xl font-black">
                        Security Incidents
                    </h1>

                    <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-300">
                        Review detected threats, repeated security events, severity, ownership and incident-resolution progress.
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
                action="{{ route('security.incidents.index') }}"
                class="grid gap-4 md:grid-cols-2 xl:grid-cols-4"
            >
                <div>
                    <label
                        for="status"
                        class="mb-2 block text-sm font-bold text-slate-700"
                    >
                        Status
                    </label>

                    <select
                        id="status"
                        name="status"
                        class="min-h-11 w-full rounded-2xl border border-slate-300 bg-white px-4 text-sm text-slate-900 outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                    >
                        <option value="">
                            All Statuses
                        </option>

                        @foreach ($statuses as $status)
                            <option
                                value="{{ $status->value }}"
                                @selected(
                                    request('status')
                                    === $status->value
                                )
                            >
                                {{ $enumLabel($status) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label
                        for="severity"
                        class="mb-2 block text-sm font-bold text-slate-700"
                    >
                        Severity
                    </label>

                    <select
                        id="severity"
                        name="severity"
                        class="min-h-11 w-full rounded-2xl border border-slate-300 bg-white px-4 text-sm text-slate-900 outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                    >
                        <option value="">
                            All Severities
                        </option>

                        @foreach ($severities as $severity)
                            <option
                                value="{{ $severity->value }}"
                                @selected(
                                    request('severity')
                                    === $severity->value
                                )
                            >
                                {{ $enumLabel($severity) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label
                        for="incident_type"
                        class="mb-2 block text-sm font-bold text-slate-700"
                    >
                        Incident Type
                    </label>

                    <input
                        id="incident_type"
                        name="incident_type"
                        type="text"
                        value="{{ request('incident_type') }}"
                        placeholder="Example: repeated_login_failure"
                        class="min-h-11 w-full rounded-2xl border border-slate-300 bg-white px-4 text-sm text-slate-900 outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                    >
                </div>

                <div>
                    <label
                        for="search"
                        class="mb-2 block text-sm font-bold text-slate-700"
                    >
                        Search
                    </label>

                    <input
                        id="search"
                        name="search"
                        type="search"
                        value="{{ request('search') }}"
                        placeholder="Title, description or UUID"
                        class="min-h-11 w-full rounded-2xl border border-slate-300 bg-white px-4 text-sm text-slate-900 outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                    >
                </div>

                <div class="flex flex-wrap gap-3 md:col-span-2 xl:col-span-4">
                    <button
                        type="submit"
                        class="inline-flex min-h-11 items-center justify-center rounded-2xl bg-slate-950 px-5 text-sm font-bold text-white transition hover:bg-slate-800"
                    >
                        Apply Filters
                    </button>

                    <a
                        href="{{ route('security.incidents.index') }}"
                        class="inline-flex min-h-11 items-center justify-center rounded-2xl border border-slate-300 bg-white px-5 text-sm font-bold text-slate-700 transition hover:bg-slate-50"
                    >
                        Clear Filters
                    </a>
                </div>
            </form>
        </section>

        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-5 py-4 sm:px-6">
                <h2 class="text-lg font-black text-slate-950">
                    Detected Incidents
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    {{ number_format($incidents->total()) }} incident record(s) found.
                </p>
            </div>

            @if ($incidents->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500 sm:px-6">
                                    Incident
                                </th>

                                <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">
                                    Severity
                                </th>

                                <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">
                                    Status
                                </th>

                                <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">
                                    Occurrences
                                </th>

                                <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">
                                    Last Seen
                                </th>

                                <th class="px-5 py-4 text-right text-xs font-black uppercase tracking-wider text-slate-500 sm:px-6">
                                    Action
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach ($incidents as $incident)
                                <tr class="align-top transition hover:bg-slate-50/80">
                                    <td class="min-w-80 px-5 py-4 sm:px-6">
                                        <p class="text-sm font-black text-slate-950">
                                            {{ $incident->title }}
                                        </p>

                                        <p class="mt-1 text-xs font-semibold text-slate-500">
                                            {{ $enumLabel($incident->incident_type) }}
                                        </p>

                                        <p class="mt-2 break-all font-mono text-[11px] leading-5 text-slate-400">
                                            {{ $incident->incident_uuid }}
                                        </p>
                                    </td>

                                    <td class="whitespace-nowrap px-5 py-4">
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold ring-1 ring-inset {{ $severityClasses($incident->severity) }}">
                                            {{ $enumLabel($incident->severity) }}
                                        </span>
                                    </td>

                                    <td class="whitespace-nowrap px-5 py-4">
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold ring-1 ring-inset {{ $statusClasses($incident->status) }}">
                                            {{ $enumLabel($incident->status) }}
                                        </span>
                                    </td>

                                    <td class="whitespace-nowrap px-5 py-4 text-sm font-black text-slate-900">
                                        {{ number_format((int) $incident->occurrence_count) }}
                                    </td>

                                    <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-600">
                                        {{ optional($incident->last_seen_at)->format('d M Y, h:i:s A') ?? '—' }}
                                    </td>

                                    <td class="whitespace-nowrap px-5 py-4 text-right sm:px-6">
                                        <a
                                            href="{{ route(
                                                'security.incidents.show',
                                                $incident
                                            ) }}"
                                            class="inline-flex min-h-10 items-center justify-center rounded-xl bg-indigo-50 px-4 text-xs font-black text-indigo-700 transition hover:bg-indigo-100"
                                        >
                                            Review
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-slate-200 px-5 py-4 sm:px-6">
                    {{ $incidents->links() }}
                </div>
            @else
                <div class="px-6 py-16 text-center">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600">
                        <svg
                            class="h-7 w-7"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <path d="M12 3l8 4v5c0 5-3.5 8.5-8 10-4.5-1.5-8-5-8-10V7l8-4z"/>
                            <path d="M8.5 12l2.3 2.3 4.7-5"/>
                        </svg>
                    </div>

                    <h2 class="mt-4 text-xl font-black text-slate-950">
                        No security incidents found
                    </h2>

                    <p class="mx-auto mt-2 max-w-xl text-sm leading-6 text-slate-500">
                        No incidents match the selected filters. New incidents will appear after the security-evaluation process detects a qualifying event.
                    </p>
                </div>
            @endif
        </section>
    </div>
@endsection

