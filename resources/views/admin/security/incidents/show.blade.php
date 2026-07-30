@extends('layouts.admin')

@section('title', 'Security Incident')
@section('page-title', 'Security Incident')

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

        $prettyJson = static function ($value): string {
            return json_encode(
                $value ?? [],
                JSON_PRETTY_PRINT
                | JSON_UNESCAPED_SLASHES
                | JSON_UNESCAPED_UNICODE
            ) ?: '{}';
        };
    @endphp

    <div class="space-y-6">
        <section class="rounded-3xl bg-slate-950 p-7 text-white shadow-xl">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-red-300">
                        Security Incident Review
                    </p>

                    <h1 class="mt-3 text-3xl font-black">
                        {{ $securityIncident->title }}
                    </h1>

                    <p class="mt-2 break-all text-sm text-slate-300">
                        {{ $securityIncident->incident_uuid }}
                    </p>
                </div>

                <a
                    href="{{ route('security.incidents.index') }}"
                    class="inline-flex min-h-11 items-center justify-center rounded-2xl border border-white/15 bg-white/10 px-5 text-sm font-bold text-white transition hover:bg-white/15"
                >
                    Back to Incidents
                </a>
            </div>
        </section>

        <section class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
            <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-black uppercase tracking-wider text-slate-400">
                    Severity
                </p>

                <p class="mt-3 text-lg font-black text-slate-950">
                    {{ $enumLabel($securityIncident->severity) }}
                </p>
            </article>

            <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-black uppercase tracking-wider text-slate-400">
                    Status
                </p>

                <p class="mt-3 text-lg font-black text-slate-950">
                    {{ $enumLabel($securityIncident->status) }}
                </p>
            </article>

            <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-black uppercase tracking-wider text-slate-400">
                    Occurrences
                </p>

                <p class="mt-3 text-2xl font-black text-slate-950">
                    {{ number_format((int) $securityIncident->occurrence_count) }}
                </p>
            </article>

            <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-black uppercase tracking-wider text-slate-400">
                    Last Seen
                </p>

                <p class="mt-3 text-sm font-black text-slate-950">
                    {{ optional($securityIncident->last_seen_at)->format('d M Y, h:i:s A') ?? '—' }}
                </p>
            </article>
        </section>

        <section class="grid gap-6 xl:grid-cols-[minmax(0,1.25fr)_minmax(360px,0.75fr)]">
            <div class="space-y-6">
                <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-black text-slate-950">
                        Incident Details
                    </h2>

                    <p class="mt-4 whitespace-pre-line text-sm leading-7 text-slate-600">
                        {{ $securityIncident->description ?: 'No description recorded.' }}
                    </p>

                    <dl class="mt-6 divide-y divide-slate-100">
                        @foreach ([
                            'Incident Type' => $enumLabel($securityIncident->incident_type),
                            'Fingerprint' => $securityIncident->fingerprint ?: '—',
                            'Subject Type' => $securityIncident->subject_type ?: '—',
                            'Subject ID' => $securityIncident->subject_id ?: '—',
                            'Login Event ID' => $securityIncident->login_event_id ?: '—',
                            'Assigned User ID' => $securityIncident->assigned_to ?: '—',
                            'Acknowledged By' => $securityIncident->acknowledged_by ?: '—',
                            'Acknowledged At' => optional($securityIncident->acknowledged_at)->format('d M Y, h:i:s A') ?? '—',
                            'Resolved By' => $securityIncident->resolved_by ?: '—',
                            'Resolved At' => optional($securityIncident->resolved_at)->format('d M Y, h:i:s A') ?? '—',
                            'Detected At' => optional($securityIncident->detected_at)->format('d M Y, h:i:s A') ?? '—',
                        ] as $label => $value)
                            <div class="grid gap-2 py-3 sm:grid-cols-[180px_minmax(0,1fr)]">
                                <dt class="text-sm font-bold text-slate-500">
                                    {{ $label }}
                                </dt>

                                <dd class="break-all text-sm font-semibold text-slate-900">
                                    {{ $value }}
                                </dd>
                            </div>
                        @endforeach
                    </dl>
                </article>

                <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-black text-slate-950">
                        Incident Metadata
                    </h2>

                    <pre class="mt-4 max-h-[520px] overflow-auto rounded-2xl bg-slate-950 p-4 text-xs leading-6 text-slate-200">{{ $prettyJson($securityIncident->metadata) }}</pre>
                </article>
            </div>

            <aside class="space-y-6">
                <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-black text-slate-950">
                        Resolution Notes
                    </h2>

                    <p class="mt-4 whitespace-pre-line text-sm leading-7 text-slate-600">
                        {{ $securityIncident->resolution_notes ?: 'No resolution notes recorded.' }}
                    </p>
                </article>

                @can('security.manage-incidents')
                    <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 class="text-lg font-black text-slate-950">
                            Update Incident
                        </h2>

                        <p class="mt-2 text-sm leading-6 text-slate-500">
                            Updating an incident is a sensitive action and may require recent password confirmation.
                        </p>

                        <form
                            method="POST"
                            action="{{ route(
                                'security.incidents.update',
                                $securityIncident
                            ) }}"
                            class="mt-6 space-y-5"
                        >
                            @csrf
                            @method('PUT')

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
                                    required
                                    class="min-h-11 w-full rounded-2xl border border-slate-300 bg-white px-4 text-sm text-slate-900 outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                                >
                                    @foreach ($statuses as $status)
                                        <option
                                            value="{{ $status->value }}"
                                            @selected(
                                                old(
                                                    'status',
                                                    $enumValue(
                                                        $securityIncident->status
                                                    )
                                                )
                                                === $status->value
                                            )
                                        >
                                            {{ $enumLabel($status) }}
                                        </option>
                                    @endforeach
                                </select>

                                @error('status')
                                    <p class="mt-2 text-sm font-semibold text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <div>
                                <label
                                    for="assigned_to"
                                    class="mb-2 block text-sm font-bold text-slate-700"
                                >
                                    Assign To
                                </label>

                                <select
                                    id="assigned_to"
                                    name="assigned_to"
                                    class="min-h-11 w-full rounded-2xl border border-slate-300 bg-white px-4 text-sm text-slate-900 outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                                >
                                    <option value="">
                                        Unassigned
                                    </option>

                                    @foreach ($users as $user)
                                        <option
                                            value="{{ $user->id }}"
                                            @selected(
                                                (string) old(
                                                    'assigned_to',
                                                    $securityIncident->assigned_to
                                                )
                                                === (string) $user->id
                                            )
                                        >
                                            {{ $user->name }} — {{ $user->email }}
                                        </option>
                                    @endforeach
                                </select>

                                @error('assigned_to')
                                    <p class="mt-2 text-sm font-semibold text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <div>
                                <label
                                    for="resolution_notes"
                                    class="mb-2 block text-sm font-bold text-slate-700"
                                >
                                    Resolution Notes
                                </label>

                                <textarea
                                    id="resolution_notes"
                                    name="resolution_notes"
                                    rows="6"
                                    maxlength="5000"
                                    class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                                    placeholder="Record investigation findings and resolution steps."
                                >{{ old(
                                    'resolution_notes',
                                    $securityIncident->resolution_notes
                                ) }}</textarea>

                                @error('resolution_notes')
                                    <p class="mt-2 text-sm font-semibold text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <button
                                type="submit"
                                class="inline-flex min-h-11 w-full items-center justify-center rounded-2xl bg-slate-950 px-5 text-sm font-bold text-white transition hover:bg-slate-800"
                            >
                                Save Incident Update
                            </button>
                        </form>
                    </article>
                @endcan
            </aside>
        </section>
    </div>
@endsection

