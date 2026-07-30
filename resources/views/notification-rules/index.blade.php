@extends('layouts.admin')

@section('title', 'Notification Rules')
@section('page-title', 'Notification Reminder Rules')

@section('content')
    <div class="space-y-6">
        <section class="rounded-3xl bg-slate-950 p-7 text-white">
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-indigo-300">
                Automation Control
            </p>

            <h1 class="mt-3 text-3xl font-black">
                Reminder Rules
            </h1>

            <p class="mt-2 text-sm text-slate-300">
                Configure reminder timing, recipients, channels and repetition limits.
            </p>
        </section>

        <section class="grid gap-5 xl:grid-cols-2">
            @forelse ($rules as $rule)
                <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-black text-slate-950">
                                {{ $rule->name }}
                            </h2>

                            <p class="mt-1 text-sm text-slate-500">
                                {{ $rule->description }}
                            </p>
                        </div>

                        <span class="rounded-full px-3 py-1 text-xs font-bold ring-1 ring-inset {{ $rule->severity->badgeClasses() }}">
                            {{ $rule->severity->label() }}
                        </span>
                    </div>

                    <form
                        method="POST"
                        action="{{ route(
                            'notification-rules.update',
                            $rule
                        ) }}"
                        class="mt-6 grid gap-4 sm:grid-cols-2"
                    >
                        @csrf
                        @method('PUT')

                        <div class="sm:col-span-2">
                            <x-form.input
                                label="Rule Name"
                                name="name"
                                :value="$rule->name"
                                required
                            />
                        </div>

                        <div class="sm:col-span-2">
                            <x-form.textarea
                                label="Description"
                                name="description"
                                :value="$rule->description"
                                rows="3"
                            />
                        </div>

                        <x-form.select
                            label="Severity"
                            name="severity"
                            required
                        >
                            @foreach ($severities as $severity)
                                <option
                                    value="{{ $severity->value }}"
                                    @selected(
                                        $rule->severity ===
                                        $severity
                                    )
                                >
                                    {{ $severity->label() }}
                                </option>
                            @endforeach
                        </x-form.select>

                        <x-form.select
                            label="Recipients"
                            name="recipient_strategy"
                            required
                        >
                            @foreach ($recipientStrategies as $strategy)
                                <option
                                    value="{{ $strategy->value }}"
                                    @selected(
                                        $rule
                                            ->recipient_strategy
                                        === $strategy
                                    )
                                >
                                    {{ $strategy->label() }}
                                </option>
                            @endforeach
                        </x-form.select>

                        <x-form.input
                            label="Lead Time in Minutes"
                            name="lead_minutes"
                            type="number"
                            min="0"
                            :value="$rule->lead_minutes"
                            required
                        />

                        <x-form.input
                            label="Repeat Every Minutes"
                            name="repeat_minutes"
                            type="number"
                            min="15"
                            :value="$rule->repeat_minutes"
                            required
                        />

                        <x-form.input
                            label="Maximum Occurrences"
                            name="maximum_occurrences"
                            type="number"
                            min="1"
                            max="365"
                            :value="$rule->maximum_occurrences"
                            required
                        />

                        <div class="space-y-2">
                            <p class="text-sm font-semibold text-slate-700">
                                Delivery Channels
                            </p>

                            <label class="flex items-center gap-2 text-sm font-bold">
                                <input
                                    type="checkbox"
                                    name="channels[]"
                                    value="database"
                                    @checked(
                                        in_array(
                                            'database',
                                            $rule->channels,
                                            true
                                        )
                                    )
                                >
                                In App
                            </label>

                            <label class="flex items-center gap-2 text-sm font-bold">
                                <input
                                    type="checkbox"
                                    name="channels[]"
                                    value="mail"
                                    @checked(
                                        in_array(
                                            'mail',
                                            $rule->channels,
                                            true
                                        )
                                    )
                                >
                                Email
                            </label>
                        </div>

                        <label class="flex items-center gap-3 rounded-2xl border border-slate-200 p-4 sm:col-span-2">
                            <input
                                type="checkbox"
                                name="is_enabled"
                                value="1"
                                @checked($rule->is_enabled)
                            >

                            <span class="text-sm font-bold">
                                Enable this reminder rule
                            </span>
                        </label>

                        <button class="min-h-11 rounded-2xl bg-slate-950 px-5 text-sm font-bold text-white sm:col-span-2">
                            Save Rule
                        </button>
                    </form>
                </article>
            @empty
                <article class="rounded-3xl border border-dashed border-slate-300 bg-white p-10 text-center shadow-sm xl:col-span-2">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600">
                        <svg
                            class="h-7 w-7"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <path d="M12 8v4l3 2"/>
                            <circle cx="12" cy="12" r="9"/>
                            <path d="M4 4l2 2M20 4l-2 2"/>
                        </svg>
                    </div>

                    <h2 class="mt-4 text-xl font-black text-slate-950">
                        No reminder rules found
                    </h2>

                    <p class="mx-auto mt-2 max-w-xl text-sm leading-6 text-slate-500">
                        The page is working, but the notification rules table does not currently contain any reminder-rule records. Run the notification-rule seeder or create the default rules before configuring them here.
                    </p>
                </article>
            @endforelse
        </section>
    </div>
@endsection

