@extends('layouts.admin')

@section('title', 'Notification Preferences')
@section('page-title', 'Notification Preferences')

@section('content')
    <form
        method="POST"
        action="{{ route(
            'notification-settings.update'
        ) }}"
        class="space-y-6"
    >
        @csrf
        @method('PUT')

        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h1 class="text-2xl font-black text-slate-950">
                General Notification Settings
            </h1>

            <div class="mt-6 grid gap-4 md:grid-cols-3">
                <label class="flex items-center gap-3 rounded-2xl border border-slate-200 p-4">
                    <input
                        type="checkbox"
                        name="in_app_notifications_enabled"
                        value="1"
                        @checked(
                            $settings
                                ->in_app_notifications_enabled
                        )
                    >

                    <span class="text-sm font-bold">
                        Enable in-app notifications
                    </span>
                </label>

                <label class="flex items-center gap-3 rounded-2xl border border-slate-200 p-4">
                    <input
                        type="checkbox"
                        name="email_notifications_enabled"
                        value="1"
                        @checked(
                            $settings
                                ->email_notifications_enabled
                        )
                    >

                    <span class="text-sm font-bold">
                        Enable email notifications
                    </span>
                </label>

                <label class="flex items-center gap-3 rounded-2xl border border-slate-200 p-4">
                    <input
                        type="checkbox"
                        name="daily_digest_enabled"
                        value="1"
                        @checked(
                            $settings
                                ->daily_digest_enabled
                        )
                    >

                    <span class="text-sm font-bold">
                        Enable daily summary
                    </span>
                </label>

                <x-form.input
                    label="Daily Summary Time"
                    name="daily_digest_time"
                    type="time"
                    :value="substr(
                        $settings->daily_digest_time,
                        0,
                        5
                    )"
                    required
                />

                <x-form.select
                    label="Timezone"
                    name="timezone"
                    required
                >
                    @foreach ([
                        'Asia/Kolkata' => 'India — Asia/Kolkata',
                        'UTC' => 'UTC',
                        'Asia/Dubai' => 'Dubai — Asia/Dubai',
                        'Europe/London' => 'London — Europe/London',
                        'America/New_York' => 'New York — America/New_York',
                    ] as $value => $label)
                        <option
                            value="{{ $value }}"
                            @selected(
                                $settings->timezone ===
                                $value
                            )
                        >
                            {{ $label }}
                        </option>
                    @endforeach
                </x-form.select>
            </div>
        </section>

        @php
            $preferenceIndex = 0;
        @endphp

        @foreach ($catalog as $category => $events)
            <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 bg-slate-50 p-5">
                    <h2 class="font-black text-slate-950">
                        {{ $category }}
                    </h2>
                </div>

                <div class="divide-y divide-slate-100">
                    @foreach ($events as $eventKey => $definition)
                        @php
                            $preference =
                                $preferences->get(
                                    $eventKey
                                );

                            $defaultInApp =
                                in_array(
                                    'database',
                                    $definition['channels'],
                                    true
                                );

                            $defaultEmail =
                                in_array(
                                    'mail',
                                    $definition['channels'],
                                    true
                                );
                        @endphp

                        <div class="grid gap-4 p-5 lg:grid-cols-[1fr_auto_auto_auto] lg:items-center">
                            <div>
                                <p class="font-black text-slate-950">
                                    {{ $definition['label'] }}
                                </p>

                                <p class="mt-1 text-sm text-slate-500">
                                    {{ $definition['description'] }}
                                </p>
                            </div>

                            <input
                                type="hidden"
                                name="preferences[{{ $preferenceIndex }}][event_key]"
                                value="{{ $eventKey }}"
                            >

                            <label class="flex items-center gap-2 text-sm font-bold text-slate-700">
                                <input
                                    type="checkbox"
                                    name="preferences[{{ $preferenceIndex }}][in_app_enabled]"
                                    value="1"
                                    @checked(
                                        $preference
                                            ?->in_app_enabled
                                        ?? $defaultInApp
                                    )
                                >
                                In App
                            </label>

                            <label class="flex items-center gap-2 text-sm font-bold text-slate-700">
                                <input
                                    type="checkbox"
                                    name="preferences[{{ $preferenceIndex }}][email_enabled]"
                                    value="1"
                                    @checked(
                                        $preference
                                            ?->email_enabled
                                        ?? $defaultEmail
                                    )
                                >
                                Email
                            </label>

                            <label class="flex items-center gap-2 text-sm font-bold text-slate-700">
                                <input
                                    type="checkbox"
                                    name="preferences[{{ $preferenceIndex }}][include_in_daily_digest]"
                                    value="1"
                                    @checked(
                                        $preference
                                            ?->include_in_daily_digest
                                        ?? $definition['digest']
                                    )
                                >
                                Daily Summary
                            </label>
                        </div>

                        @php
                            $preferenceIndex++;
                        @endphp
                    @endforeach
                </div>
            </section>
        @endforeach

        <div class="flex justify-end">
            <button class="min-h-12 rounded-2xl bg-slate-950 px-7 text-sm font-bold text-white">
                Save Notification Preferences
            </button>
        </div>
    </form>
@endsection