<div x-show="activeTab === 'history'" x-cloak>
    <div class="space-y-6">
        <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <form
                method="GET"
                class="grid gap-3 lg:grid-cols-5"
            >
                <input
                    type="hidden"
                    name="tab"
                    value="history"
                >

                <select
                    name="activity_event"
                    class="min-h-12 rounded-2xl border border-slate-200 px-4 text-sm"
                >
                    <option value="">
                        All activity types
                    </option>

                    @foreach ($activityEvents as $event)
                        <option
                            value="{{ $event }}"
                            @selected(
                                request('activity_event') ===
                                $event
                            )
                        >
                            {{ str($event)
                                ->replace('_', ' ')
                                ->title() }}
                        </option>
                    @endforeach
                </select>

                <select
                    name="activity_actor"
                    class="min-h-12 rounded-2xl border border-slate-200 px-4 text-sm"
                >
                    <option value="">
                        All users
                    </option>

                    @foreach ($activityUsers as $activityUser)
                        <option
                            value="{{ $activityUser->id }}"
                            @selected(
                                (string) request(
                                    'activity_actor'
                                ) ===
                                (string) $activityUser->id
                            )
                        >
                            {{ $activityUser->name }}
                        </option>
                    @endforeach
                </select>

                <input
                    type="date"
                    name="activity_from"
                    value="{{ request('activity_from') }}"
                    class="min-h-12 rounded-2xl border border-slate-200 px-4 text-sm"
                >

                <input
                    type="date"
                    name="activity_to"
                    value="{{ request('activity_to') }}"
                    class="min-h-12 rounded-2xl border border-slate-200 px-4 text-sm"
                >

                <button class="min-h-12 rounded-2xl bg-indigo-600 px-5 text-sm font-bold text-white">
                    Filter History
                </button>
            </form>
        </section>

        <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
            <div class="mb-6">
                <h2 class="text-xl font-black text-slate-950">
                    Complete Project History
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Chronological record of project, task, approval, payment, expense, note, work-log and attachment activity.
                </p>
            </div>

            <div class="relative space-y-6 before:absolute before:bottom-0 before:left-5 before:top-0 before:w-px before:bg-slate-200">
                @forelse ($activities as $activity)
                    <article class="relative pl-14">
                        <div class="absolute left-0 top-0 flex h-10 w-10 items-center justify-center rounded-full border-4 border-white bg-slate-950 text-xs font-black text-white shadow">
                            {{ strtoupper(
                                substr(
                                    $activity->actor?->name
                                        ?? 'S',
                                    0,
                                    1
                                )
                            ) }}
                        </div>

                        <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold {{ $activity->badge_classes }}">
                                        {{ $activity->event_label }}
                                    </span>

                                    <h3 class="mt-3 font-black text-slate-950">
                                        {{ $activity->title }}
                                    </h3>

                                    @if ($activity->description)
                                        <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-600">
                                            {{ $activity->description }}
                                        </p>
                                    @endif
                                </div>

                                <div class="text-xs text-slate-500 sm:text-right">
                                    <p class="font-bold text-slate-700">
                                        {{ $activity->actor?->name
                                            ?? 'System' }}
                                    </p>

                                    <p class="mt-1">
                                        {{ $activity->occurred_at->format(
                                            'd M Y, h:i A'
                                        ) }}
                                    </p>

                                    <p class="mt-1">
                                        {{ $activity->occurred_at->diffForHumans() }}
                                    </p>
                                </div>
                            </div>

                            @if (
                                $activity->old_values ||
                                $activity->new_values
                            )
                                <details class="mt-5">
                                    <summary class="cursor-pointer text-sm font-bold text-indigo-600">
                                        View Recorded Changes
                                    </summary>

                                    <div class="mt-4 grid gap-4 lg:grid-cols-2">
                                        @if ($activity->old_values)
                                            <div class="rounded-2xl bg-red-50 p-4">
                                                <p class="text-xs font-bold uppercase tracking-wider text-red-700">
                                                    Previous Values
                                                </p>

                                                <dl class="mt-3 space-y-2">
                                                    @foreach ($activity->old_values as $key => $value)
                                                        <div>
                                                            <dt class="text-xs font-bold text-red-800">
                                                                {{ str($key)
                                                                    ->replace('_', ' ')
                                                                    ->title() }}
                                                            </dt>

                                                            <dd class="mt-1 break-words text-xs text-red-950">
                                                                {{ is_array($value)
                                                                    ? json_encode($value)
                                                                    : ($value ?? 'Empty') }}
                                                            </dd>
                                                        </div>
                                                    @endforeach
                                                </dl>
                                            </div>
                                        @endif

                                        @if ($activity->new_values)
                                            <div class="rounded-2xl bg-emerald-50 p-4">
                                                <p class="text-xs font-bold uppercase tracking-wider text-emerald-700">
                                                    New Values
                                                </p>

                                                <dl class="mt-3 space-y-2">
                                                    @foreach ($activity->new_values as $key => $value)
                                                        <div>
                                                            <dt class="text-xs font-bold text-emerald-800">
                                                                {{ str($key)
                                                                    ->replace('_', ' ')
                                                                    ->title() }}
                                                            </dt>

                                                            <dd class="mt-1 break-words text-xs text-emerald-950">
                                                                {{ is_array($value)
                                                                    ? json_encode($value)
                                                                    : ($value ?? 'Empty') }}
                                                            </dd>
                                                        </div>
                                                    @endforeach
                                                </dl>
                                            </div>
                                        @endif
                                    </div>
                                </details>
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="relative pl-14">
                        <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-10 text-center">
                            <p class="font-bold text-slate-900">
                                No project history available
                            </p>
                        </div>
                    </div>
                @endforelse
            </div>

            <div class="mt-7">
                {{ $activities->appends([
                    'tab' => 'history'
                ])->links() }}
            </div>
        </section>
    </div>
</div>