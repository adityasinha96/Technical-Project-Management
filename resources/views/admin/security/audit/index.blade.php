@extends('layouts.admin')

@section('title', 'Audit Logs')
@section('page-title', 'Audit Logs')

@section('content')
    @php
        $enumValue = static function ($value): string {
            if ($value instanceof \BackedEnum) {
                return (string) $value->value;
            }

            return (string) ($value ?? '');
        };

        $enumLabel = static function ($value) use ($enumValue): string {
            if (is_object($value) && method_exists($value, 'label')) {
                return (string) $value->label();
            }

            return \Illuminate\Support\Str::headline(
                $enumValue($value)
            );
        };

        $severityClasses = static function ($severity) use ($enumValue): string {
            return match ($enumValue($severity)) {
                'critical' =>
                    'bg-red-100 text-red-700 ring-red-200',

                'high' =>
                    'bg-orange-100 text-orange-700 ring-orange-200',

                'warning', 'medium' =>
                    'bg-amber-100 text-amber-700 ring-amber-200',

                'info', 'low' =>
                    'bg-blue-100 text-blue-700 ring-blue-200',

                default =>
                    'bg-slate-100 text-slate-700 ring-slate-200',
            };
        };
    @endphp

    <div class="space-y-6">
        <section class="rounded-3xl bg-slate-950 p-7 text-white shadow-xl">
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-indigo-300">
                Administrative Security
            </p>

            <div class="mt-3 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="text-3xl font-black">
                        Audit Logs
                    </h1>

                    <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-300">
                        Review immutable system activity, security events, actor information, affected records and request metadata.
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
                action="{{ route('security.audit.index') }}"
                class="grid gap-4 md:grid-cols-2 xl:grid-cols-3"
            >
                <div>
                    <label for="category" class="mb-2 block text-sm font-bold text-slate-700">
                        Category
                    </label>

                    <input
                        id="category"
                        name="category"
                        type="text"
                        value="{{ request('category') }}"
                        placeholder="Example: system"
                        class="min-h-11 w-full rounded-2xl border border-slate-300 bg-white px-4 text-sm text-slate-900 outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                    >
                </div>

                <div>
                    <label for="severity" class="mb-2 block text-sm font-bold text-slate-700">
                        Severity
                    </label>

                    <input
                        id="severity"
                        name="severity"
                        type="text"
                        value="{{ request('severity') }}"
                        placeholder="Example: high"
                        class="min-h-11 w-full rounded-2xl border border-slate-300 bg-white px-4 text-sm text-slate-900 outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                    >
                </div>

                <div>
                    <label for="event_type" class="mb-2 block text-sm font-bold text-slate-700">
                        Event Type
                    </label>

                    <input
                        id="event_type"
                        name="event_type"
                        type="text"
                        value="{{ request('event_type') }}"
                        placeholder="Search event type"
                        class="min-h-11 w-full rounded-2xl border border-slate-300 bg-white px-4 text-sm text-slate-900 outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                    >
                </div>

                <div>
                    <label for="actor" class="mb-2 block text-sm font-bold text-slate-700">
                        Actor
                    </label>

                    <input
                        id="actor"
                        name="actor"
                        type="text"
                        value="{{ request('actor') }}"
                        placeholder="Name or email"
                        class="min-h-11 w-full rounded-2xl border border-slate-300 bg-white px-4 text-sm text-slate-900 outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                    >
                </div>

                <div>
                    <label for="date_from" class="mb-2 block text-sm font-bold text-slate-700">
                        Date From
                    </label>

                    <input
                        id="date_from"
                        name="date_from"
                        type="date"
                        value="{{ request('date_from') }}"
                        class="min-h-11 w-full rounded-2xl border border-slate-300 bg-white px-4 text-sm text-slate-900 outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                    >
                </div>

                <div>
                    <label for="date_to" class="mb-2 block text-sm font-bold text-slate-700">
                        Date To
                    </label>

                    <input
                        id="date_to"
                        name="date_to"
                        type="date"
                        value="{{ request('date_to') }}"
                        class="min-h-11 w-full rounded-2xl border border-slate-300 bg-white px-4 text-sm text-slate-900 outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                    >
                </div>

                <div class="flex flex-wrap gap-3 md:col-span-2 xl:col-span-3">
                    <button
                        type="submit"
                        class="inline-flex min-h-11 items-center justify-center rounded-2xl bg-slate-950 px-5 text-sm font-bold text-white transition hover:bg-slate-800"
                    >
                        Apply Filters
                    </button>

                    <a
                        href="{{ route('security.audit.index') }}"
                        class="inline-flex min-h-11 items-center justify-center rounded-2xl border border-slate-300 bg-white px-5 text-sm font-bold text-slate-700 transition hover:bg-slate-50"
                    >
                        Clear Filters
                    </a>
                </div>
            </form>
        </section>

        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-2 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <div>
                    <h2 class="text-lg font-black text-slate-950">
                        Recorded Events
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        {{ number_format($logs->total()) }} audit record(s) found.
                    </p>
                </div>
            </div>

            @if ($logs->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500 sm:px-6">
                                    Sequence
                                </th>

                                <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">
                                    Event
                                </th>

                                <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">
                                    Category
                                </th>

                                <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">
                                    Severity
                                </th>

                                <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">
                                    Actor
                                </th>

                                <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">
                                    Date
                                </th>

                                <th class="px-5 py-4 text-right text-xs font-black uppercase tracking-wider text-slate-500 sm:px-6">
                                    Action
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach ($logs as $log)
                                <tr class="transition hover:bg-slate-50/80">
                                    <td class="whitespace-nowrap px-5 py-4 text-sm font-black text-slate-950 sm:px-6">
                                        #{{ number_format((int) $log->sequence) }}
                                    </td>

                                    <td class="min-w-64 px-5 py-4">
                                        <p class="text-sm font-bold text-slate-950">
                                            {{ $log->event_type }}
                                        </p>

                                        <p class="mt-1 break-all text-xs text-slate-400">
                                            {{ $log->audit_uuid }}
                                        </p>
                                    </td>

                                    <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-700">
                                        {{ $enumLabel($log->category) }}
                                    </td>

                                    <td class="whitespace-nowrap px-5 py-4">
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold ring-1 ring-inset {{ $severityClasses($log->severity) }}">
                                            {{ $enumLabel($log->severity) }}
                                        </span>
                                    </td>

                                    <td class="min-w-52 px-5 py-4">
                                        <p class="text-sm font-semibold text-slate-800">
                                            {{ $log->actor_name ?: 'System' }}
                                        </p>

                                        @if ($log->actor_email)
                                            <p class="mt-1 text-xs text-slate-400">
                                                {{ $log->actor_email }}
                                            </p>
                                        @endif
                                    </td>

                                    <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-600">
                                        {{ optional($log->occurred_at)->format('d M Y, h:i:s A') ?? '—' }}
                                    </td>

                                    <td class="whitespace-nowrap px-5 py-4 text-right sm:px-6">
                                        <a
                                            href="{{ route('security.audit.show', $log) }}"
                                            class="inline-flex min-h-10 items-center justify-center rounded-xl bg-indigo-50 px-4 text-xs font-black text-indigo-700 transition hover:bg-indigo-100"
                                        >
                                            View
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-slate-200 px-5 py-4 sm:px-6">
                    {{ $logs->links() }}
                </div>
            @else
                <div class="px-6 py-16 text-center">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-500">
                        <svg
                            class="h-7 w-7"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <path d="M4 4h16v16H4z"/>
                            <path d="M8 8h8M8 12h8M8 16h5"/>
                        </svg>
                    </div>

                    <h2 class="mt-4 text-xl font-black text-slate-950">
                        No audit records found
                    </h2>

                    <p class="mx-auto mt-2 max-w-xl text-sm leading-6 text-slate-500">
                        No audit entries match the selected filters. Clear the filters or perform an audited system action.
                    </p>
                </div>
            @endif
        </section>
    </div>
@endsection

