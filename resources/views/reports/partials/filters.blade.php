<section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
    <form
        method="GET"
        action="{{ $filterAction }}"
        class="grid gap-4 md:grid-cols-2 xl:grid-cols-4"
    >
        <x-form.input
            label="From Date"
            name="date_from"
            type="date"
            :value="$filters->from->format('Y-m-d')"
        />

        <x-form.input
            label="To Date"
            name="date_to"
            type="date"
            :value="$filters->to->format('Y-m-d')"
        />

        <x-form.select
            label="Project"
            name="project_id"
        >
            <option value="">
                All projects
            </option>

            @foreach ($projects as $projectOption)
                <option
                    value="{{ $projectOption->id }}"
                    @selected(
                        $filters->projectId ===
                        $projectOption->id
                    )
                >
                    {{ $projectOption->name }}
                </option>
            @endforeach
        </x-form.select>

        <x-form.select
            label="Client"
            name="client_id"
        >
            <option value="">
                All clients
            </option>

            @foreach ($clients as $client)
                <option
                    value="{{ $client->id }}"
                    @selected(
                        $filters->clientId ===
                        $client->id
                    )
                >
                    {{ $client->name }}
                </option>
            @endforeach
        </x-form.select>

        @if ($showUserFilter ?? false)
            <x-form.select
                label="Team Member"
                name="user_id"
            >
                <option value="">
                    All team members
                </option>

                @foreach ($users as $userOption)
                    <option
                        value="{{ $userOption->id }}"
                        @selected(
                            $filters->userId ===
                            $userOption->id
                        )
                    >
                        {{ $userOption->name }}
                    </option>
                @endforeach
            </x-form.select>
        @endif

        @if ($showProjectStatus ?? false)
            <x-form.select
                label="Project Status"
                name="project_status"
            >
                <option value="">
                    All statuses
                </option>

                @foreach ($projectStatuses as $status)
                    <option
                        value="{{ $status->value }}"
                        @selected(
                            $filters->projectStatus ===
                            $status->value
                        )
                    >
                        {{ $status->label() }}
                    </option>
                @endforeach
            </x-form.select>
        @endif

        @if ($showProjectPriority ?? false)
            <x-form.select
                label="Project Priority"
                name="project_priority"
            >
                <option value="">
                    All priorities
                </option>

                @foreach ($projectPriorities as $priority)
                    <option
                        value="{{ $priority->value }}"
                        @selected(
                            $filters->projectPriority ===
                            $priority->value
                        )
                    >
                        {{ $priority->label() }}
                    </option>
                @endforeach
            </x-form.select>
        @endif

        @if ($showTicketStatus ?? false)
            <x-form.select
                label="Ticket Status"
                name="ticket_status"
            >
                <option value="">
                    All statuses
                </option>

                @foreach ($ticketStatuses as $status)
                    <option
                        value="{{ $status->value }}"
                        @selected(
                            $filters->ticketStatus ===
                            $status->value
                        )
                    >
                        {{ $status->label() }}
                    </option>
                @endforeach
            </x-form.select>
        @endif

        @if ($showTicketPriority ?? false)
            <x-form.select
                label="Ticket Priority"
                name="ticket_priority"
            >
                <option value="">
                    All priorities
                </option>

                @foreach ($ticketPriorities as $priority)
                    <option
                        value="{{ $priority->value }}"
                        @selected(
                            $filters->ticketPriority ===
                            $priority->value
                        )
                    >
                        {{ $priority->label() }}
                    </option>
                @endforeach
            </x-form.select>
        @endif

        <x-form.select
            label="Rows Per Page"
            name="per_page"
        >
            @foreach ([15, 25, 50, 100] as $perPage)
                <option
                    value="{{ $perPage }}"
                    @selected(
                        $filters->perPage ===
                        $perPage
                    )
                >
                    {{ $perPage }}
                </option>
            @endforeach
        </x-form.select>

        <div class="flex items-end gap-3 xl:col-span-4">
            <button class="min-h-12 rounded-2xl bg-indigo-600 px-6 text-sm font-bold text-white">
                Apply Filters
            </button>

            <a
                href="{{ $filterAction }}"
                class="inline-flex min-h-12 items-center rounded-2xl border border-slate-200 bg-white px-6 text-sm font-bold text-slate-700"
            >
                Reset
            </a>
        </div>
    </form>
</section>