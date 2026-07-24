@php
    $formatMinutes = function (int $minutes): string {
        $hours = intdiv($minutes, 60);
        $remainingMinutes = $minutes % 60;

        return $hours > 0
            ? "{$hours}h {$remainingMinutes}m"
            : "{$remainingMinutes}m";
    };
@endphp

<div x-show="activeTab === 'work-logs'" x-cloak>
    <div class="space-y-6">
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ([
                [
                    'Total Work Time',
                    $formatMinutes(
                        $workLogSummary['total_minutes']
                    )
                ],
                [
                    'Current Month',
                    $formatMinutes(
                        $workLogSummary[
                            'current_month_minutes'
                        ]
                    )
                ],
                [
                    'My Logged Time',
                    $formatMinutes(
                        $workLogSummary['my_minutes']
                    )
                ],
                [
                    'Total Work Entries',
                    $workLogSummary['log_count']
                ],
            ] as [$label, $value])
                <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm text-slate-500">
                        {{ $label }}
                    </p>

                    <p class="mt-2 text-2xl font-black text-slate-950">
                        {{ $value }}
                    </p>
                </article>
            @endforeach
        </section>

        <section class="grid gap-6 xl:grid-cols-[1.3fr_0.7fr]">
            <div class="space-y-4">
                @forelse ($workLogs as $workLog)
                    <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap gap-2">
                                    <span class="rounded-full px-3 py-1 text-xs font-bold ring-1 ring-inset {{ $workLog->status->badgeClasses() }}">
                                        {{ $workLog->status->label() }}
                                    </span>

                                    <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-bold text-indigo-700">
                                        {{ $workLog->work_type->label() }}
                                    </span>

                                    @if ($workLog->is_billable)
                                        <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">
                                            Billable
                                        </span>
                                    @endif
                                </div>

                                <h2 class="mt-3 text-lg font-black text-slate-950">
                                    {{ $workLog->title }}
                                </h2>

                                <p class="mt-1 text-sm text-slate-500">
                                    {{ $workLog->loggedBy->name }}
                                    ·
                                    {{ $workLog->work_date->format('d M Y') }}
                                    ·
                                    {{ $workLog->formatted_duration }}
                                </p>

                                @if ($workLog->task)
                                    <p class="mt-2 text-xs font-bold text-indigo-600">
                                        Task:
                                        {{ $workLog->task->title }}
                                    </p>
                                @endif

                                @if ($workLog->details)
                                    <div class="mt-4">
                                        <p class="text-xs font-bold uppercase tracking-wider text-slate-400">
                                            Work Performed
                                        </p>

                                        <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-700">
                                            {{ $workLog->details }}
                                        </p>
                                    </div>
                                @endif

                                @if ($workLog->outcome)
                                    <div class="mt-4 rounded-2xl bg-emerald-50 p-4">
                                        <p class="text-xs font-bold uppercase tracking-wider text-emerald-700">
                                            Outcome
                                        </p>

                                        <p class="mt-2 whitespace-pre-line text-sm text-emerald-950">
                                            {{ $workLog->outcome }}
                                        </p>
                                    </div>
                                @endif

                                @if ($workLog->blocker)
                                    <div class="mt-4 rounded-2xl bg-red-50 p-4">
                                        <p class="text-xs font-bold uppercase tracking-wider text-red-700">
                                            Blocker
                                        </p>

                                        <p class="mt-2 whitespace-pre-line text-sm text-red-950">
                                            {{ $workLog->blocker }}
                                        </p>
                                    </div>
                                @endif

                                @if ($workLog->fileLinks->isNotEmpty())
                                    <div class="mt-4 flex flex-wrap gap-2">
                                        @foreach ($workLog->fileLinks as $link)
                                            @if ($link->file)
                                                <a
                                                    href="{{ $link->file->secure_download_url }}"
                                                    class="rounded-xl bg-slate-100 px-3 py-2 text-xs font-bold text-indigo-700"
                                                >
                                                    📎 {{ $link->file->original_name }}
                                                </a>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            <div class="rounded-2xl bg-slate-50 px-4 py-3 text-center">
                                <p class="text-xl font-black text-slate-950">
                                    {{ $workLog->formatted_duration }}
                                </p>

                                <p class="text-xs text-slate-500">
                                    Work Time
                                </p>
                            </div>
                        </div>

                        @if ($workLog->canBeManagedBy(auth()->user()))
                            <details class="mt-5 border-t border-slate-100 pt-5">
                                <summary class="cursor-pointer text-sm font-bold text-indigo-600">
                                    Edit Work Log
                                </summary>

                                <form
                                    method="POST"
                                    action="{{ route(
                                        'projects.work-logs.update',
                                        [$project, $workLog]
                                    ) }}"
                                    enctype="multipart/form-data"
                                    class="mt-4 grid gap-4 rounded-2xl bg-slate-50 p-4 sm:grid-cols-2"
                                >
                                    @csrf
                                    @method('PUT')

                                    <div class="sm:col-span-2">
                                        <x-form.input
                                            label="Title"
                                            name="title"
                                            :value="$workLog->title"
                                            required
                                        />
                                    </div>

                                    <x-form.input
                                        label="Work Date"
                                        name="work_date"
                                        type="date"
                                        :value="$workLog->work_date->format('Y-m-d')"
                                        required
                                    />

                                    <x-form.input
                                        label="Duration in Minutes"
                                        name="duration_minutes"
                                        type="number"
                                        min="1"
                                        max="1440"
                                        :value="$workLog->duration_minutes"
                                        required
                                    />

                                    <x-form.select
                                        label="Work Type"
                                        name="work_type"
                                        required
                                    >
                                        @foreach ($workLogTypes as $type)
                                            <option
                                                value="{{ $type->value }}"
                                                @selected(
                                                    $workLog->work_type === $type
                                                )
                                            >
                                                {{ $type->label() }}
                                            </option>
                                        @endforeach
                                    </x-form.select>

                                    <x-form.select
                                        label="Status"
                                        name="status"
                                        required
                                    >
                                        @foreach ($workLogStatuses as $status)
                                            <option
                                                value="{{ $status->value }}"
                                                @selected(
                                                    $workLog->status === $status
                                                )
                                            >
                                                {{ $status->label() }}
                                            </option>
                                        @endforeach
                                    </x-form.select>

                                    <x-form.select
                                        label="Related Task"
                                        name="project_task_id"
                                    >
                                        <option value="">
                                            No specific task
                                        </option>

                                        @foreach ($project->tasks as $task)
                                            <option
                                                value="{{ $task->id }}"
                                                @selected(
                                                    $workLog->project_task_id ===
                                                    $task->id
                                                )
                                            >
                                                {{ $task->title }}
                                            </option>
                                        @endforeach
                                    </x-form.select>

                                    <label class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white p-4">
                                        <input
                                            type="checkbox"
                                            name="is_billable"
                                            value="1"
                                            @checked(
                                                $workLog->is_billable
                                            )
                                        >

                                        <span class="text-sm font-bold">
                                            Billable work
                                        </span>
                                    </label>

                                    <div class="sm:col-span-2">
                                        <x-form.textarea
                                            label="Work Details"
                                            name="details"
                                            :value="$workLog->details"
                                            rows="4"
                                        />
                                    </div>

                                    <div class="sm:col-span-2">
                                        <x-form.textarea
                                            label="Outcome"
                                            name="outcome"
                                            :value="$workLog->outcome"
                                            rows="3"
                                        />
                                    </div>

                                    <div class="sm:col-span-2">
                                        <x-form.textarea
                                            label="Blocker"
                                            name="blocker"
                                            :value="$workLog->blocker"
                                            rows="3"
                                        />
                                    </div>

                                    <div class="sm:col-span-2">
                                        <input
                                            type="file"
                                            name="attachments[]"
                                            multiple
                                            class="block w-full rounded-2xl border border-dashed border-slate-300 bg-white p-4 text-sm"
                                        >
                                    </div>

                                    <button class="min-h-11 rounded-2xl bg-slate-950 px-5 text-sm font-bold text-white">
                                        Save Work Log
                                    </button>
                                </form>

                                @can('work-logs.delete')
                                    <form
                                        method="POST"
                                        action="{{ route(
                                            'projects.work-logs.destroy',
                                            [$project, $workLog]
                                        ) }}"
                                        class="mt-3"
                                        onsubmit="return confirm('Delete this work log?')"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button class="text-sm font-bold text-red-600">
                                            Delete Work Log
                                        </button>
                                    </form>
                                @endcan
                            </details>
                        @endif
                    </article>
                @empty
                    <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-14 text-center">
                        <p class="font-bold text-slate-900">
                            No work has been logged
                        </p>
                    </div>
                @endforelse

                {{ $workLogs->appends([
                    'tab' => 'work-logs'
                ])->links() }}
            </div>

            @can('work-logs.create')
                <aside class="h-fit rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="text-lg font-black text-slate-950">
                        Record Work
                    </h2>

                    <form
                        method="POST"
                        action="{{ route(
                            'projects.work-logs.store',
                            $project
                        ) }}"
                        enctype="multipart/form-data"
                        class="mt-5 space-y-4"
                    >
                        @csrf

                        <x-form.input
                            label="Work Title"
                            name="title"
                            required
                        />

                        <x-form.input
                            label="Work Date"
                            name="work_date"
                            type="date"
                            :value="today()->format('Y-m-d')"
                            required
                        />

                        <x-form.input
                            label="Duration in Minutes"
                            name="duration_minutes"
                            type="number"
                            min="1"
                            max="1440"
                            value="60"
                            required
                        />

                        <x-form.select
                            label="Work Type"
                            name="work_type"
                            required
                        >
                            @foreach ($workLogTypes as $type)
                                <option value="{{ $type->value }}">
                                    {{ $type->label() }}
                                </option>
                            @endforeach
                        </x-form.select>

                        <x-form.select
                            label="Status"
                            name="status"
                            required
                        >
                            @foreach ($workLogStatuses as $status)
                                <option value="{{ $status->value }}">
                                    {{ $status->label() }}
                                </option>
                            @endforeach
                        </x-form.select>

                        <x-form.select
                            label="Related Task"
                            name="project_task_id"
                        >
                            <option value="">
                                No specific task
                            </option>

                            @foreach ($project->tasks as $task)
                                <option value="{{ $task->id }}">
                                    {{ $task->title }}
                                </option>
                            @endforeach
                        </x-form.select>

                        <x-form.textarea
                            label="Work Details"
                            name="details"
                            rows="5"
                        />

                        <x-form.textarea
                            label="Outcome"
                            name="outcome"
                            rows="3"
                        />

                        <x-form.textarea
                            label="Blocker"
                            name="blocker"
                            rows="3"
                        />

                        <label class="flex items-center gap-3 rounded-2xl border border-slate-200 p-4">
                            <input
                                type="checkbox"
                                name="is_billable"
                                value="1"
                            >

                            <span class="text-sm font-bold">
                                Billable work
                            </span>
                        </label>

                        <input
                            type="file"
                            name="attachments[]"
                            multiple
                            class="block w-full rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-4 text-sm"
                        >

                        <button class="min-h-12 w-full rounded-2xl bg-slate-950 px-5 text-sm font-bold text-white">
                            Save Work Log
                        </button>
                    </form>
                </aside>
            @endcan
        </section>
    </div>
</div>