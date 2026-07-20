@php
    $currentTeam = old('team_member_ids', $selectedTeam);

    $dateValue = function ($date) {
        return $date
            ? \Illuminate\Support\Carbon::parse($date)->format('Y-m-d')
            : null;
    };
@endphp

<div class="space-y-6">
    <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <div class="mb-6">
            <h2 class="text-lg font-bold text-slate-950">
                Project Information
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Primary project, client and assignment details.
            </p>
        </div>

        <div class="grid gap-5 md:grid-cols-2">
            <x-form.select
                label="Client"
                name="client_id"
                required
            >
                <option value="">Select client</option>

                @foreach ($clients as $client)
                    <option
                        value="{{ $client->id }}"
                        @selected(
                            (string) old(
                                'client_id',
                                request('client_id', $project->client_id)
                            ) === (string) $client->id
                        )
                    >
                        {{ $client->display_name }}
                        — {{ $client->client_code }}
                    </option>
                @endforeach
            </x-form.select>

            <x-form.select
                label="Project Category"
                name="project_category_id"
            >
                <option value="">Select category</option>

                @foreach ($categories as $category)
                    <option
                        value="{{ $category->id }}"
                        @selected(
                            (string) old(
                                'project_category_id',
                                $project->project_category_id
                            ) === (string) $category->id
                        )
                    >
                        {{ $category->name }}
                    </option>
                @endforeach
            </x-form.select>

            <div class="md:col-span-2">
                <x-form.input
                    label="Project Name"
                    name="name"
                    :value="$project->name"
                    required
                    placeholder="Example: Crednexa Finserv Website"
                />
            </div>

            <div class="md:col-span-2">
                <x-form.textarea
                    label="Project Description"
                    name="description"
                    :value="$project->description"
                    rows="5"
                    placeholder="Describe the project objectives, modules and deliverables."
                />
            </div>

            <x-form.select
                label="Project Manager"
                name="manager_id"
            >
                <option value="">Not assigned</option>

                @foreach ($users as $user)
                    <option
                        value="{{ $user->id }}"
                        @selected(
                            (string) old(
                                'manager_id',
                                $project->manager_id
                            ) === (string) $user->id
                        )
                    >
                        {{ $user->name }}
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
                            old(
                                'priority',
                                $project->priority?->value
                                    ?? \App\Enums\ProjectPriority::Medium->value
                            ) === $priority->value
                        )
                    >
                        {{ $priority->label() }}
                    </option>
                @endforeach
            </x-form.select>

            <x-form.select
                label="Project Status"
                name="status"
                required
            >
                @foreach ($statuses as $status)
                    <option
                        value="{{ $status->value }}"
                        @selected(
                            old(
                                'status',
                                $project->status?->value
                                    ?? \App\Enums\ProjectStatus::NewProject->value
                            ) === $status->value
                        )
                    >
                        {{ $status->label() }}
                    </option>
                @endforeach
            </x-form.select>

            <x-form.input
                label="Maximum Project Duration"
                name="maximum_duration_days"
                type="number"
                min="1"
                max="365"
                :value="$project->maximum_duration_days ?: 18"
                required
            />
        </div>
    </section>

    <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <div class="mb-6">
            <h2 class="text-lg font-bold text-slate-950">
                Pricing and Expected Cost
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Expected profit is calculated automatically.
            </p>
        </div>

        <div
            x-data="{
                price: Number('{{ old('project_price', $project->project_price ?: 0) }}') || 0,
                cost: Number('{{ old('estimated_cost', $project->estimated_cost ?: 0) }}') || 0,
                format(value) {
                    return new Intl.NumberFormat('en-IN', {
                        style: 'currency',
                        currency: 'INR'
                    }).format(value || 0)
                }
            }"
            class="grid gap-5 md:grid-cols-3"
        >
            <x-form.input
                label="Project Price"
                name="project_price"
                type="number"
                min="0"
                step="0.01"
                :value="$project->project_price"
                x-model.number="price"
                required
            />

            <x-form.input
                label="Estimated Project Cost"
                name="estimated_cost"
                type="number"
                min="0"
                step="0.01"
                :value="$project->estimated_cost"
                x-model.number="cost"
            />

            <x-form.select
                label="Currency"
                name="currency"
                required
            >
                <option
                    value="INR"
                    @selected(
                        old('currency', $project->currency ?: 'INR')
                            === 'INR'
                    )
                >
                    INR — Indian Rupee
                </option>
            </x-form.select>

            <div class="md:col-span-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
                <p class="text-xs font-bold uppercase tracking-wider text-emerald-700">
                    Estimated Project Profit
                </p>

                <p
                    class="mt-1 text-2xl font-black text-emerald-950"
                    x-text="format(price - cost)"
                ></p>
            </div>

            <div class="md:col-span-3">
                <x-form.textarea
                    label="Payment Terms"
                    name="payment_terms"
                    :value="$project->payment_terms"
                    rows="4"
                    placeholder="Example: 50% advance and 50% after final deployment."
                />
            </div>
        </div>
    </section>

    <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <div class="mb-6">
            <h2 class="text-lg font-bold text-slate-950">
                Project Schedule
            </h2>
        </div>

        <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
            <x-form.input
                label="Start Date"
                name="start_date"
                type="date"
                :value="$dateValue($project->start_date)"
                required
            />

            <x-form.input
                label="Expected Delivery Date"
                name="expected_delivery_date"
                type="date"
                :value="$dateValue($project->expected_delivery_date)"
                required
            />

            <x-form.input
                label="Revised Delivery Date"
                name="revised_delivery_date"
                type="date"
                :value="$dateValue($project->revised_delivery_date)"
            />

            <x-form.input
                label="Actual Completion Date"
                name="actual_completion_date"
                type="date"
                :value="$dateValue($project->actual_completion_date)"
            />
        </div>
    </section>

    <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <div class="mb-6">
            <h2 class="text-lg font-bold text-slate-950">
                Assign Project Team
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                The selected project manager is automatically added to the team.
            </p>
        </div>

        @error('team_member_ids.*')
            <p class="mb-4 text-sm font-medium text-red-600">
                {{ $message }}
            </p>
        @enderror

        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($users as $user)
                <label class="flex cursor-pointer items-center gap-3 rounded-2xl border border-slate-200 p-4 transition hover:border-indigo-300 hover:bg-indigo-50/40">
                    <input
                        type="checkbox"
                        name="team_member_ids[]"
                        value="{{ $user->id }}"
                        @checked(
                            in_array(
                                $user->id,
                                array_map('intval', $currentTeam)
                            )
                        )
                        class="h-5 w-5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                    >

                    <div class="min-w-0">
                        <p class="truncate text-sm font-bold text-slate-900">
                            {{ $user->name }}
                        </p>

                        <p class="truncate text-xs text-slate-500">
                            {{ $user->email }}
                        </p>
                    </div>
                </label>
            @endforeach
        </div>
    </section>

    <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <div class="mb-6">
            <h2 class="text-lg font-bold text-slate-950">
                Project URLs
            </h2>
        </div>

        <div class="grid gap-5 md:grid-cols-2">
            <x-form.input
                label="Reference Website URL"
                name="reference_url"
                type="url"
                :value="$project->reference_url"
                placeholder="https://example.com"
            />

            <x-form.input
                label="Development URL"
                name="development_url"
                type="url"
                :value="$project->development_url"
                placeholder="https://dev.example.com"
            />

            <div class="md:col-span-2">
                <x-form.input
                    label="Live Website URL"
                    name="live_url"
                    type="url"
                    :value="$project->live_url"
                    placeholder="https://clientwebsite.com"
                />
            </div>
        </div>
    </section>

    <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <div class="mb-6">
            <h2 class="text-lg font-bold text-slate-950">
                Domain and Hosting
            </h2>
        </div>

        <div class="grid gap-5 md:grid-cols-2">
            <x-form.input
                label="Domain Name"
                name="domain_name"
                :value="$project->domain_name"
                placeholder="example.com"
            />

            <x-form.input
                label="Hosting Provider"
                name="hosting_provider"
                :value="$project->hosting_provider"
                placeholder="Hostinger, cPanel, AWS..."
            />

            <x-form.input
                label="Domain Expiry Date"
                name="domain_expiry_date"
                type="date"
                :value="$dateValue($project->domain_expiry_date)"
            />

            <x-form.input
                label="Hosting Expiry Date"
                name="hosting_expiry_date"
                type="date"
                :value="$dateValue($project->hosting_expiry_date)"
            />
        </div>
    </section>

    <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <x-form.textarea
            label="Internal Project Remarks"
            name="internal_remarks"
            :value="$project->internal_remarks"
            rows="6"
            placeholder="Important internal information about this project."
        />
    </section>

    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
        <a
            href="{{ route('projects.index') }}"
            class="inline-flex min-h-12 items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 text-sm font-bold text-slate-700"
        >
            Cancel
        </a>

        <button
            type="submit"
            class="inline-flex min-h-12 items-center justify-center rounded-2xl bg-slate-950 px-6 text-sm font-bold text-white shadow-lg shadow-slate-300 transition hover:bg-indigo-600"
        >
            {{ $submitLabel }}
        </button>
    </div>
</div>