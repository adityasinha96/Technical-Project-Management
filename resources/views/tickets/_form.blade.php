<div class="space-y-6">
    <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <h2 class="text-lg font-black text-slate-950">
            Ticket Information
        </h2>

        <div class="mt-6 grid gap-5 md:grid-cols-2">
            <div class="md:col-span-2">
                <x-form.select
                    label="Project"
                    name="project_id"
                    required
                >
                    <option value="">
                        Select project
                    </option>

                    @foreach ($projects as $project)
                        <option
                            value="{{ $project->id }}"
                            @selected(
                                (string) old(
                                    'project_id',
                                    $selectedProjectId ?? null
                                ) ===
                                (string) $project->id
                            )
                        >
                            {{ $project->name }}
                            — {{ $project->client->display_name }}
                        </option>
                    @endforeach
                </x-form.select>
            </div>

            <x-form.select
                label="Ticket Type"
                name="type"
                required
            >
                @foreach ($types as $type)
                    <option value="{{ $type->value }}">
                        {{ $type->label() }}
                    </option>
                @endforeach
            </x-form.select>

            <x-form.select
                label="Reported Through"
                name="source"
                required
            >
                @foreach ($sources as $source)
                    <option value="{{ $source->value }}">
                        {{ $source->label() }}
                    </option>
                @endforeach
            </x-form.select>

            <x-form.select
                label="Priority"
                name="priority"
                required
            >
                @foreach ($priorities as $priority)
                    <option
                        value="{{ $priority->value }}"
                        @selected(
                            $priority ===
                            \App\Enums\TicketPriority::Medium
                        )
                    >
                        {{ $priority->label() }}
                    </option>
                @endforeach
            </x-form.select>

            <x-form.select
                label="Initial Assignee"
                name="assigned_to"
            >
                <option value="">
                    Leave unassigned
                </option>

                @foreach ($users as $user)
                    <option value="{{ $user->id }}">
                        {{ $user->name }}
                    </option>
                @endforeach
            </x-form.select>

            <div class="md:col-span-2">
                <x-form.input
                    label="Ticket Subject"
                    name="subject"
                    required
                />
            </div>

            <div class="md:col-span-2">
                <x-form.textarea
                    label="Detailed Description"
                    name="description"
                    rows="8"
                    required
                />
            </div>
        </div>
    </section>

    <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <label class="block">
            <span class="mb-2 block text-sm font-bold text-slate-700">
                Ticket Attachments
            </span>

            <input
                type="file"
                name="attachments[]"
                multiple
                class="block w-full rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-4 text-sm"
            >
        </label>
    </section>

    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
        <a
            href="{{ route('tickets.index') }}"
            class="inline-flex min-h-12 items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 text-sm font-bold text-slate-700"
        >
            Cancel
        </a>

        <button class="inline-flex min-h-12 items-center justify-center rounded-2xl bg-slate-950 px-6 text-sm font-bold text-white">
            Create Ticket
        </button>
    </div>
</div>