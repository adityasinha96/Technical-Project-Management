@extends('layouts.admin')

@section('title', 'Permission History')
@section('page-title', 'Permission History')

@section('content')
    @php
        $value = static function (
            $record,
            string ...$keys
        ) {
            foreach ($keys as $key) {
                $result =
                    $record->getAttribute(
                        $key
                    );

                if (
                    $result !== null
                    && $result !== ''
                ) {
                    return $result;
                }
            }

            return null;
        };

        $displayValue = static function (
            $value
        ): string {
            if ($value instanceof \BackedEnum) {
                return \Illuminate\Support\Str::headline(
                    (string) $value->value
                );
            }

            if (is_array($value)) {
                return json_encode(
                    $value,
                    JSON_UNESCAPED_SLASHES
                    | JSON_UNESCAPED_UNICODE
                ) ?: '[]';
            }

            if ($value instanceof \DateTimeInterface) {
                return $value->format(
                    'd M Y, h:i:s A'
                );
            }

            return $value !== null
                && $value !== ''
                    ? (string) $value
                    : '—';
        };

        $actionClasses = static function (
            string $action
        ): string {
            $normalised =
                strtolower($action);

            if (
                str_contains(
                    $normalised,
                    'removed'
                )
                || str_contains(
                    $normalised,
                    'revoked'
                )
                || str_contains(
                    $normalised,
                    'deleted'
                )
            ) {
                return
                    'bg-red-100 text-red-700 ring-red-200';
            }

            if (
                str_contains(
                    $normalised,
                    'added'
                )
                || str_contains(
                    $normalised,
                    'assigned'
                )
                || str_contains(
                    $normalised,
                    'granted'
                )
            ) {
                return
                    'bg-emerald-100 text-emerald-700 ring-emerald-200';
            }

            return
                'bg-indigo-100 text-indigo-700 ring-indigo-200';
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
                        Permission History
                    </h1>

                    <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-300">
                        Review recorded role and permission changes, affected users or roles, and the administrator who performed each action.
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
                    Recorded Permission Changes
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    {{ number_format($permissionChanges->total()) }} record(s) found.
                </p>
            </div>

            @if ($permissionChanges->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500 sm:px-6">
                                    ID
                                </th>

                                <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">
                                    Action
                                </th>

                                <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">
                                    Target
                                </th>

                                <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">
                                    Performed By
                                </th>

                                <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">
                                    Previous Values
                                </th>

                                <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">
                                    New Values
                                </th>

                                <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500 sm:px-6">
                                    Date
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach ($permissionChanges as $change)
                                @php
                                    $action =
                                        $displayValue(
                                            $value(
                                                $change,
                                                'action',
                                                'event_type',
                                                'change_type'
                                            )
                                        );

                                    $targetType =
                                        $value(
                                            $change,
                                            'target_type',
                                            'subject_type',
                                            'permissionable_type'
                                        );

                                    $targetId =
                                        $value(
                                            $change,
                                            'target_id',
                                            'subject_id',
                                            'permissionable_id'
                                        );

                                    $performedBy =
                                        $value(
                                            $change,
                                            'performed_by',
                                            'actor_id',
                                            'user_id'
                                        );

                                    $oldValues =
                                        $value(
                                            $change,
                                            'old_values',
                                            'previous_values',
                                            'before'
                                        );

                                    $newValues =
                                        $value(
                                            $change,
                                            'new_values',
                                            'current_values',
                                            'after'
                                        );

                                    $recordedAt =
                                        $value(
                                            $change,
                                            'occurred_at',
                                            'created_at',
                                            'changed_at'
                                        );
                                @endphp

                                <tr class="align-top transition hover:bg-slate-50/80">
                                    <td class="whitespace-nowrap px-5 py-4 text-sm font-black text-slate-950 sm:px-6">
                                        #{{ $change->getKey() }}
                                    </td>

                                    <td class="whitespace-nowrap px-5 py-4">
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold ring-1 ring-inset {{ $actionClasses($action) }}">
                                            {{ $action }}
                                        </span>
                                    </td>

                                    <td class="min-w-52 px-5 py-4">
                                        <p class="text-sm font-bold text-slate-900">
                                            {{
                                                $targetType
                                                    ? class_basename(
                                                        (string) $targetType
                                                    )
                                                    : 'Unknown Target'
                                            }}
                                        </p>

                                        <p class="mt-1 text-xs text-slate-500">
                                            ID:
                                            {{ $targetId ?? '—' }}
                                        </p>
                                    </td>

                                    <td class="whitespace-nowrap px-5 py-4 text-sm font-semibold text-slate-700">
                                        User ID:
                                        {{ $performedBy ?? '—' }}
                                    </td>

                                    <td class="min-w-64 px-5 py-4">
                                        <pre class="max-h-36 overflow-auto whitespace-pre-wrap break-words rounded-xl bg-slate-950 p-3 text-[11px] leading-5 text-slate-200">{{ $displayValue($oldValues) }}</pre>
                                    </td>

                                    <td class="min-w-64 px-5 py-4">
                                        <pre class="max-h-36 overflow-auto whitespace-pre-wrap break-words rounded-xl bg-slate-950 p-3 text-[11px] leading-5 text-slate-200">{{ $displayValue($newValues) }}</pre>
                                    </td>

                                    <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-600 sm:px-6">
                                        {{ $displayValue($recordedAt) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-slate-200 px-5 py-4 sm:px-6">
                    {{ $permissionChanges->links() }}
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
                            <path d="M12 3l8 4v5c0 5-3.5 8.5-8 10-4.5-1.5-8-5-8-10V7l8-4z"/>
                            <path d="M8 12h8M12 8v8"/>
                        </svg>
                    </div>

                    <h2 class="mt-4 text-xl font-black text-slate-950">
                        No permission changes found
                    </h2>

                    <p class="mx-auto mt-2 max-w-xl text-sm leading-6 text-slate-500">
                        Role and permission changes will appear here after they are performed through the controlled permission-administration service.
                    </p>
                </div>
            @endif
        </section>
    </div>
@endsection

