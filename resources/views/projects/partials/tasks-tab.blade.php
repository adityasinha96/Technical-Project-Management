<div x-show="activeTab === 'tasks'" x-cloak>
    <div class="space-y-6">
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @php
                $activeTasks = $project->tasks
                    ->where(
                        'status',
                        '!=',
                        \App\Enums\TaskStatus::Cancelled
                    );

                $completedTasks = $project->tasks
                    ->where(
                        'status',
                        \App\Enums\TaskStatus::Completed
                    );

                $blockedTasks = $project->tasks
                    ->where(
                        'status',
                        \App\Enums\TaskStatus::Blocked
                    );

                $overdueTasks = $project->tasks
                    ->filter(fn ($task) => $task->is_overdue);
            @endphp

            @foreach ([
                ['Total Tasks', $activeTasks->count()],
                ['Completed', $completedTasks->count()],
                ['Blocked', $blockedTasks->count()],
                ['Overdue', $overdueTasks->count()],
            ] as [$label, $value])
                <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm text-slate-500">
                        {{ $label }}
                    </p>

                    <p class="mt-2 text-3xl font-black text-slate-950">
                        {{ $value }}
                    </p>
                </article>
            @endforeach
        </section>

        @if ($project->tasks->isEmpty())
            <section class="rounded-3xl border border-dashed border-indigo-300 bg-indigo-50 p-6">
                <h2 class="font-bold text-indigo-950">
                    No project tasks available
                </h2>

                <p class="mt-1 text-sm text-indigo-700">
                    Apply a reusable template or add tasks manually.
                </p>

                @can('projects.update')
                    <div class="mt-5 grid gap-3 md:grid-cols-[1fr_auto]">
                        <form
                            method="POST"
                            action=""
                            x-data="{ selectedTemplate: '' }"
                            @submit="
                                if (!selectedTemplate) {
                                    $event.preventDefault();
                                    alert('Select a project template.');
                                } else {
                                    $el.action =
                                        '{{ url('/projects/' . $project->id . '/templates') }}/'
                                        + selectedTemplate
                                        + '/apply';
                                }
                            "
                            class="contents"
                        >
                            @csrf

                            <select
                                x-model="selectedTemplate"
                                class="min-h-12 rounded-2xl border border-indigo-200 bg-white px-4 text-sm"
                            >
                                <option value="">
                                    Select project template
                                </option>

                                @foreach ($availableTemplates as $template)
                                    <option value="{{ $template->id }}">
                                        {{ $template->name }}
                                        — {{ $template->tasks_count }} tasks
                                    </option>
                                @endforeach
                            </select>

                            <button class="min-h-12 rounded-2xl bg-indigo-600 px-5 text-sm font-bold text-white">
                                Apply Template
                            </button>
                        </form>
                    </div>
                @endcan
            </section>
        @endif

        <section class="grid gap-6 xl:grid-cols-[1.3fr_0.7fr]">
            <article class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-100 p-5">
                    <div>
                        <h2 class="text-lg font-bold text-slate-950">
                            Project Tasks
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            Internal progress is recalculated after every update.
                        </p>
                    </div>
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse ($project->tasks as $task)
                        <article class="p-5">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap gap-2">
                                        <span class="rounded-full px-3 py-1 text-xs font-bold {{ $task->phase->badgeClasses() }}">
                                            {{ $task->phase->label() }}
                                        </span>

                                        <span class="rounded-full px-3 py-1 text-xs font-bold ring-1 ring-inset {{ $task->status->badgeClasses() }}">
                                            {{ $task->status->label() }}
                                        </span>

                                        @if ($task->is_overdue)
                                            <span class="rounded-full bg-red-600 px-3 py-1 text-xs font-bold text-white">
                                                Overdue
                                            </span>
                                        @endif
                                    </div>

                                    <h3 class="mt-3 font-bold text-slate-950">
                                        {{ $task->title }}
                                    </h3>

                                    @if ($task->description)
                                        <p class="mt-2 text-sm leading-6 text-slate-500">
                                            {{ $task->description }}
                                        </p>
                                    @endif

                                    <div class="mt-4 grid gap-3 text-xs sm:grid-cols-4">
                                        <div>
                                            <p class="text-slate-400">
                                                Assigned To
                                            </p>
                                            <p class="mt-1 font-bold text-slate-700">
                                                {{ $task->assignee?->name ?? 'Not assigned' }}
                                            </p>
                                        </div>

                                        <div>
                                            <p class="text-slate-400">
                                                Weight
                                            </p>
                                            <p class="mt-1 font-bold text-slate-700">
                                                {{ number_format($task->weight, 2) }}
                                            </p>
                                        </div>

                                        <div>
                                            <p class="text-slate-400">
                                                Start Date
                                            </p>
                                            <p class="mt-1 font-bold text-slate-700">
                                                {{ $task->start_date?->format('d M Y') ?? 'Not set' }}
                                            </p>
                                        </div>

                                        <div>
                                            <p class="text-slate-400">
                                                Due Date
                                            </p>
                                            <p class="mt-1 font-bold {{ $task->is_overdue ? 'text-red-700' : 'text-slate-700' }}">
                                                {{ $task->due_date?->format('d M Y') ?? 'Not set' }}
                                            </p>
                                        </div>
                                    </div>

                                    @if ($task->blocked_reason)
                                        <div class="mt-4 rounded-2xl bg-red-50 p-3 text-sm text-red-800">
                                            <strong>Blocked:</strong>
                                            {{ $task->blocked_reason }}
                                        </div>
                                    @endif
                                </div>

                                <div class="w-full lg:w-56">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-bold text-slate-500">
                                            Progress
                                        </span>

                                        <span class="text-sm font-black text-slate-950">
                                            {{ $task->progress }}%
                                        </span>
                                    </div>

                                    <div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-200">
                                        <div
                                            class="h-full rounded-full bg-indigo-600"
                                            style="width: {{ $task->progress }}%"
                                        ></div>
                                    </div>

                                    @can('tasks.update')
                                        <details class="mt-4">
                                            <summary class="cursor-pointer text-center text-sm font-bold text-indigo-600">
                                                Update Task
                                            </summary>

                                            <form
                                                method="POST"
                                                action="{{ route(
                                                    'projects.tasks.update',
                                                    [$project, $task]
                                                ) }}"
                                                class="mt-4 space-y-3 rounded-2xl bg-slate-50 p-4"
                                            >
                                                @csrf
                                                @method('PUT')

                                                <input
                                                    type="hidden"
                                                    name="title"
                                                    value="{{ $task->title }}"
                                                >

                                                <input
                                                    type="hidden"
                                                    name="description"
                                                    value="{{ $task->description }}"
                                                >

                                                <input
                                                    type="hidden"
                                                    name="assigned_to"
                                                    value="{{ $task->assigned_to }}"
                                                >

                                                <input
                                                    type="hidden"
                                                    name="phase"
                                                    value="{{ $task->phase->value }}"
                                                >

                                                <input
                                                    type="hidden"
                                                    name="priority"
                                                    value="{{ $task->priority->value }}"
                                                >

                                                <input
                                                    type="hidden"
                                                    name="weight"
                                                    value="{{ $task->weight }}"
                                                >

                                                <input
                                                    type="hidden"
                                                    name="estimated_hours"
                                                    value="{{ $task->estimated_hours }}"
                                                >

                                                <input
                                                    type="hidden"
                                                    name="start_date"
                                                    value="{{ $task->start_date?->format('Y-m-d') }}"
                                                >

                                                <input
                                                    type="hidden"
                                                    name="due_date"
                                                    value="{{ $task->due_date?->format('Y-m-d') }}"
                                                >

                                                <select
                                                    name="status"
                                                    class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm"
                                                >
                                                    @foreach ($taskStatuses as $status)
                                                        <option
                                                            value="{{ $status->value }}"
                                                            @selected(
                                                                $task->status === $status
                                                            )
                                                        >
                                                            {{ $status->label() }}
                                                        </option>
                                                    @endforeach
                                                </select>

                                                <input
                                                    type="number"
                                                    name="progress"
                                                    min="0"
                                                    max="100"
                                                    value="{{ $task->progress }}"
                                                    class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm"
                                                >

                                                <textarea
                                                    name="blocked_reason"
                                                    rows="2"
                                                    placeholder="Reason when blocked"
                                                    class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm"
                                                >{{ $task->blocked_reason }}</textarea>

                                                <button class="w-full rounded-xl bg-slate-950 px-3 py-2 text-sm font-bold text-white">
                                                    Save Update
                                                </button>
                                            </form>
                                        </details>
                                    @endcan
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="p-12 text-center text-sm text-slate-500">
                            No tasks have been added.
                        </div>
                    @endforelse
                </div>
            </article>

            @can('tasks.create')
                <article class="h-fit rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="text-lg font-bold text-slate-950">
                        Add Manual Task
                    </h2>

                    <form
                        method="POST"
                        action="{{ route(
                            'projects.tasks.store',
                            $project
                        ) }}"
                        class="mt-5 space-y-4"
                    >
                        @csrf

                        <x-form.input
                            label="Task Title"
                            name="title"
                            required
                        />

                        <x-form.textarea
                            label="Description"
                            name="description"
                            rows="3"
                        />

                        <x-form.select
                            label="Assign To"
                            name="assigned_to"
                        >
                            <option value="">
                                Not assigned
                            </option>

                            @foreach ($availableUsers as $user)
                                <option value="{{ $user->id }}">
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </x-form.select>

                        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-1">
                            <x-form.select
                                label="Phase"
                                name="phase"
                                required
                            >
                                @foreach ($taskPhases as $phase)
                                    <option value="{{ $phase->value }}">
                                        {{ $phase->label() }}
                                    </option>
                                @endforeach
                            </x-form.select>

                            <x-form.select
                                label="Priority"
                                name="priority"
                                required
                            >
                                @foreach ($priorities as $priority)
                                    <option value="{{ $priority->value }}">
                                        {{ $priority->label() }}
                                    </option>
                                @endforeach
                            </x-form.select>
                        </div>

                        <input
                            type="hidden"
                            name="status"
                            value="not_started"
                        >

                        <input
                            type="hidden"
                            name="progress"
                            value="0"
                        >

                        <x-form.input
                            label="Task Weight"
                            name="weight"
                            type="number"
                            min="0.01"
                            step="0.01"
                            value="5"
                            required
                        />

                        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-1">
                            <x-form.input
                                label="Start Date"
                                name="start_date"
                                type="date"
                            />

                            <x-form.input
                                label="Due Date"
                                name="due_date"
                                type="date"
                            />
                        </div>

                        <button class="min-h-11 w-full rounded-2xl bg-slate-950 px-4 text-sm font-bold text-white">
                            Add Task
                        </button>
                    </form>
                </article>
            @endcan
        </section>
    </div>
</div>