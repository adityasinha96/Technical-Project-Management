@php
    $existingTasks = old(
        'tasks',
        $projectTemplate->exists
            ? $projectTemplate->tasks->map(
                fn ($task) => [
                    'title' => $task->title,
                    'description' => $task->description,
                    'phase' => $task->phase->value,
                    'priority' => $task->priority->value,
                    'weight' => $task->weight,
                    'estimated_hours' => $task->estimated_hours,
                    'default_duration_days' => $task->default_duration_days,
                ]
            )->values()->all()
            : [
                [
                    'title' => '',
                    'description' => '',
                    'phase' => 'planning',
                    'priority' => 'medium',
                    'weight' => 10,
                    'estimated_hours' => '',
                    'default_duration_days' => 1,
                ],
            ]
    );

    $phaseOptions = collect($phases)
        ->map(fn ($phase) => [
            'value' => $phase->value,
            'label' => $phase->label(),
        ])->values();

    $priorityOptions = collect($priorities)
        ->map(fn ($priority) => [
            'value' => $priority->value,
            'label' => $priority->label(),
        ])->values();
@endphp

<div class="space-y-6">
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-bold text-slate-950">
            Template Information
        </h2>

        <div class="mt-6 grid gap-5 md:grid-cols-2">
            <x-form.input
                label="Template Name"
                name="name"
                :value="$projectTemplate->name"
                required
            />

            <x-form.select
                label="Project Category"
                name="project_category_id"
            >
                <option value="">
                    General / All Categories
                </option>

                @foreach ($categories as $category)
                    <option
                        value="{{ $category->id }}"
                        @selected(
                            (string) old(
                                'project_category_id',
                                $projectTemplate->project_category_id
                            ) === (string) $category->id
                        )
                    >
                        {{ $category->name }}
                    </option>
                @endforeach
            </x-form.select>

            <x-form.input
                label="Default Duration in Days"
                name="default_duration_days"
                type="number"
                min="1"
                max="365"
                :value="$projectTemplate->default_duration_days ?: 18"
                required
            />

            <label class="flex items-center gap-3 rounded-2xl border border-slate-200 p-4">
                <input
                    type="checkbox"
                    name="is_active"
                    value="1"
                    @checked(
                        old(
                            'is_active',
                            $projectTemplate->is_active
                        )
                    )
                    class="h-5 w-5 rounded border-slate-300 text-indigo-600"
                >

                <div>
                    <p class="text-sm font-bold text-slate-900">
                        Active Template
                    </p>
                    <p class="text-xs text-slate-500">
                        Available while creating projects.
                    </p>
                </div>
            </label>

            <div class="md:col-span-2">
                <x-form.textarea
                    label="Description"
                    name="description"
                    :value="$projectTemplate->description"
                    rows="4"
                />
            </div>
        </div>
    </section>

    <section
        x-data="{
            tasks: @js($existingTasks),
            phases: @js($phaseOptions),
            priorities: @js($priorityOptions),

            addTask() {
                this.tasks.push({
                    title: '',
                    description: '',
                    phase: 'general',
                    priority: 'medium',
                    weight: 5,
                    estimated_hours: '',
                    default_duration_days: 1
                });
            },

            removeTask(index) {
                if (this.tasks.length > 1) {
                    this.tasks.splice(index, 1);
                }
            },

            totalWeight() {
                return this.tasks.reduce(
                    (total, task) =>
                        total + Number(task.weight || 0),
                    0
                );
            }
        }"
        class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm"
    >
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-bold text-slate-950">
                    Template Tasks
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Weight controls the task’s contribution to internal progress.
                </p>
            </div>

            <button
                type="button"
                @click="addTask()"
                class="min-h-11 rounded-2xl bg-indigo-600 px-4 text-sm font-bold text-white"
            >
                + Add Task
            </button>
        </div>

        <div class="mt-4 rounded-2xl bg-indigo-50 p-4">
            <p class="text-sm font-semibold text-indigo-700">
                Current total task weight:
                <span
                    class="font-black"
                    x-text="totalWeight().toFixed(2)"
                ></span>
            </p>

            <p class="mt-1 text-xs text-indigo-600">
                A total of 100 is recommended but not mandatory.
            </p>
        </div>

        <div class="mt-6 space-y-4">
            <template
                x-for="(task, index) in tasks"
                :key="index"
            >
                <article class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                    <div class="mb-4 flex items-center justify-between">
                        <h3
                            class="font-bold text-slate-900"
                            x-text="`Task ${index + 1}`"
                        ></h3>

                        <button
                            type="button"
                            @click="removeTask(index)"
                            class="text-sm font-bold text-red-600"
                        >
                            Remove
                        </button>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <label class="md:col-span-2 block">
                            <span class="mb-2 block text-sm font-semibold text-slate-700">
                                Task Title
                            </span>

                            <input
                                type="text"
                                :name="`tasks[${index}][title]`"
                                x-model="task.title"
                                required
                                class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm"
                            >
                        </label>

                        <label class="block">
                            <span class="mb-2 block text-sm font-semibold text-slate-700">
                                Phase
                            </span>

                            <select
                                :name="`tasks[${index}][phase]`"
                                x-model="task.phase"
                                class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm"
                            >
                                <template
                                    x-for="phase in phases"
                                    :key="phase.value"
                                >
                                    <option
                                        :value="phase.value"
                                        x-text="phase.label"
                                    ></option>
                                </template>
                            </select>
                        </label>

                        <label class="block">
                            <span class="mb-2 block text-sm font-semibold text-slate-700">
                                Priority
                            </span>

                            <select
                                :name="`tasks[${index}][priority]`"
                                x-model="task.priority"
                                class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm"
                            >
                                <template
                                    x-for="priority in priorities"
                                    :key="priority.value"
                                >
                                    <option
                                        :value="priority.value"
                                        x-text="priority.label"
                                    ></option>
                                </template>
                            </select>
                        </label>

                        <label class="block">
                            <span class="mb-2 block text-sm font-semibold text-slate-700">
                                Weight
                            </span>

                            <input
                                type="number"
                                min="0.01"
                                step="0.01"
                                :name="`tasks[${index}][weight]`"
                                x-model.number="task.weight"
                                required
                                class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm"
                            >
                        </label>

                        <label class="block">
                            <span class="mb-2 block text-sm font-semibold text-slate-700">
                                Duration in Days
                            </span>

                            <input
                                type="number"
                                min="1"
                                :name="`tasks[${index}][default_duration_days]`"
                                x-model="task.default_duration_days"
                                class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm"
                            >
                        </label>

                        <label class="block">
                            <span class="mb-2 block text-sm font-semibold text-slate-700">
                                Estimated Hours
                            </span>

                            <input
                                type="number"
                                min="0"
                                step="0.5"
                                :name="`tasks[${index}][estimated_hours]`"
                                x-model="task.estimated_hours"
                                class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm"
                            >
                        </label>

                        <label class="md:col-span-2 block">
                            <span class="mb-2 block text-sm font-semibold text-slate-700">
                                Description
                            </span>

                            <textarea
                                :name="`tasks[${index}][description]`"
                                x-model="task.description"
                                rows="3"
                                class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm"
                            ></textarea>
                        </label>
                    </div>
                </article>
            </template>
        </div>
    </section>

    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
        <a
            href="{{ route('project-templates.index') }}"
            class="inline-flex min-h-12 items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 text-sm font-bold text-slate-700"
        >
            Cancel
        </a>

        <button
            type="submit"
            class="inline-flex min-h-12 items-center justify-center rounded-2xl bg-slate-950 px-6 text-sm font-bold text-white"
        >
            {{ $submitLabel }}
        </button>
    </div>
</div>