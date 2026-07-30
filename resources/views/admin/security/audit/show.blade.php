@extends('layouts.admin')

@section('title', 'Audit Log Details')
@section('page-title', 'Audit Log Details')

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
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-indigo-300">
                        Immutable Audit Record
                    </p>

                    <h1 class="mt-3 text-3xl font-black">
                        {{ $auditLog->event_type }}
                    </h1>

                    <p class="mt-2 break-all text-sm text-slate-300">
                        {{ $auditLog->audit_uuid }}
                    </p>
                </div>

                <a
                    href="{{ route('security.audit.index') }}"
                    class="inline-flex min-h-11 items-center justify-center rounded-2xl border border-white/15 bg-white/10 px-5 text-sm font-bold text-white transition hover:bg-white/15"
                >
                    Back to Audit Logs
                </a>
            </div>
        </section>

        <section class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
            <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-black uppercase tracking-wider text-slate-400">
                    Sequence
                </p>

                <p class="mt-3 text-2xl font-black text-slate-950">
                    #{{ number_format((int) $auditLog->sequence) }}
                </p>
            </article>

            <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-black uppercase tracking-wider text-slate-400">
                    Category
                </p>

                <p class="mt-3 text-lg font-black text-slate-950">
                    {{ $enumLabel($auditLog->category) }}
                </p>
            </article>

            <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-black uppercase tracking-wider text-slate-400">
                    Severity
                </p>

                <p class="mt-3 text-lg font-black text-slate-950">
                    {{ $enumLabel($auditLog->severity) }}
                </p>
            </article>

            <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-black uppercase tracking-wider text-slate-400">
                    Occurred At
                </p>

                <p class="mt-3 text-sm font-black text-slate-950">
                    {{ optional($auditLog->occurred_at)->format('d M Y, h:i:s A') ?? '—' }}
                </p>
            </article>
        </section>

        <section class="grid gap-6 xl:grid-cols-2">
            <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-black text-slate-950">
                    Actor and Request Context
                </h2>

                <dl class="mt-5 divide-y divide-slate-100">
                    @foreach ([
                        'Actor Name' => $auditLog->actor_name ?: 'System',
                        'Actor Email' => $auditLog->actor_email ?: '—',
                        'Actor Type' => $auditLog->actor_type ?: '—',
                        'Actor ID' => $auditLog->actor_id ?: '—',
                        'Guard' => $auditLog->guard ?: '—',
                        'Route Name' => $auditLog->route_name ?: '—',
                        'Request Method' => $auditLog->request_method ?: '—',
                        'Request Path' => $auditLog->request_path ?: '—',
                        'IP Address' => $auditLog->ip_address ?: '—',
                        'Session Hash' => $auditLog->session_id_hash ?: '—',
                    ] as $label => $value)
                        <div class="grid gap-2 py-3 sm:grid-cols-[170px_minmax(0,1fr)]">
                            <dt class="text-sm font-bold text-slate-500">
                                {{ $label }}
                            </dt>

                            <dd class="break-all text-sm font-semibold text-slate-900">
                                {{ $value }}
                            </dd>
                        </div>
                    @endforeach

                    <div class="grid gap-2 py-3 sm:grid-cols-[170px_minmax(0,1fr)]">
                        <dt class="text-sm font-bold text-slate-500">
                            User Agent
                        </dt>

                        <dd class="break-words text-sm font-semibold leading-6 text-slate-900">
                            {{ $auditLog->user_agent ?: '—' }}
                        </dd>
                    </div>
                </dl>
            </article>

            <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-black text-slate-950">
                    Affected Record and Hash Chain
                </h2>

                <dl class="mt-5 divide-y divide-slate-100">
                    @foreach ([
                        'Auditable Type' => $auditLog->auditable_type ?: '—',
                        'Auditable ID' => $auditLog->auditable_id ?: '—',
                        'Previous Hash' => $auditLog->previous_hash ?: '—',
                        'Entry Hash' => $auditLog->entry_hash ?: '—',
                    ] as $label => $value)
                        <div class="grid gap-2 py-3 sm:grid-cols-[170px_minmax(0,1fr)]">
                            <dt class="text-sm font-bold text-slate-500">
                                {{ $label }}
                            </dt>

                            <dd class="break-all font-mono text-xs leading-6 text-slate-900">
                                {{ $value }}
                            </dd>
                        </div>
                    @endforeach
                </dl>
            </article>
        </section>

        <section class="grid gap-6 xl:grid-cols-3">
            <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-black text-slate-950">
                    Old Values
                </h2>

                <pre class="mt-4 max-h-[480px] overflow-auto rounded-2xl bg-slate-950 p-4 text-xs leading-6 text-slate-200">{{ $prettyJson($auditLog->old_values) }}</pre>
            </article>

            <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-black text-slate-950">
                    New Values
                </h2>

                <pre class="mt-4 max-h-[480px] overflow-auto rounded-2xl bg-slate-950 p-4 text-xs leading-6 text-slate-200">{{ $prettyJson($auditLog->new_values) }}</pre>
            </article>

            <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-black text-slate-950">
                    Metadata
                </h2>

                <pre class="mt-4 max-h-[480px] overflow-auto rounded-2xl bg-slate-950 p-4 text-xs leading-6 text-slate-200">{{ $prettyJson($auditLog->metadata) }}</pre>
            </article>
        </section>
    </div>
@endsection

