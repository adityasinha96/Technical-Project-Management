@extends('layouts.admin')

@section('title', $project->name)
@section('page-title', 'Project Details')

@section('content')
    <div
        x-data="{
            activeTab: '{{ request('tab', 'overview') }}',
            taskModalOpen: false,
            editingTask: null
        }"
        class="space-y-6"
    >
        {{-- Project hero --}}
        <section class="relative overflow-hidden rounded-3xl bg-slate-950 p-6 text-white shadow-2xl sm:p-8">
            <div class="absolute -right-20 -top-20 h-72 w-72 rounded-full bg-indigo-500/30 blur-3xl"></div>

            <div class="absolute -bottom-24 right-20 h-64 w-64 rounded-full bg-cyan-400/20 blur-3xl"></div>

            <div class="relative flex flex-col gap-6 xl:flex-row xl:items-start xl:justify-between">
                <div class="min-w-0">
                    <div class="flex flex-wrap gap-2">
                        <span class="rounded-full bg-white/10 px-3 py-1 text-xs font-bold">
                            {{ $project->project_code }}
                        </span>

                        <span class="rounded-full px-3 py-1 text-xs font-bold {{ $project->status->badgeClasses() }}">
                            {{ $project->status->label() }}
                        </span>

                        <span class="rounded-full px-3 py-1 text-xs font-bold {{ $project->priority->badgeClasses() }}">
                            {{ $project->priority->label() }}
                        </span>

                        @if ($project->is_delayed)
                            <span class="rounded-full bg-red-500 px-3 py-1 text-xs font-black text-white">
                                {{ $project->deadline_label }}
                            </span>
                        @endif
                    </div>

                    <h1 class="mt-4 text-2xl font-black sm:text-3xl">
                        {{ $project->name }}
                    </h1>

                    <p class="mt-2 text-sm text-slate-300">
                        {{ $project->client?->display_name ?? 'No client assigned' }}
                        ·
                        {{ $project->category?->name ?? 'Uncategorised' }}
                    </p>

                    @if ($project->template)
                        <p class="mt-2 text-xs font-semibold text-violet-300">
                            Workflow Template: {{ $project->template->name }}
                        </p>
                    @endif
                </div>

                <div class="flex flex-wrap gap-3">
                    @can('projects.update')
                        <a
                            href="{{ route('projects.edit', $project) }}"
                            class="inline-flex min-h-11 items-center justify-center rounded-2xl bg-white px-4 text-sm font-bold text-slate-950 transition hover:bg-slate-100"
                        >
                            Edit Project
                        </a>
                    @endcan

                    <a
                        href="{{ route('projects.index') }}"
                        class="inline-flex min-h-11 items-center justify-center rounded-2xl border border-white/20 px-4 text-sm font-bold text-white transition hover:bg-white/10"
                    >
                        All Projects
                    </a>
                </div>
            </div>

            {{-- Internal and official progress --}}
            <div class="relative mt-7 grid gap-5 lg:grid-cols-2">
                {{-- Internal task-based progress --}}
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">
                                Internal Work Progress
                            </p>

                            <p class="mt-1 text-xs text-slate-400">
                                Calculated from project tasks
                            </p>
                        </div>

                        <p class="shrink-0 text-2xl font-black">
                            {{ $project->internal_progress }}%
                        </p>
                    </div>

                    <div class="mt-3 h-3 overflow-hidden rounded-full bg-white/10">
                        <div
                            class="h-full rounded-full bg-gradient-to-r from-violet-400 to-indigo-400 transition-all duration-500"
                            style="width: {{ min(100, max(0, $project->internal_progress)) }}%"
                        ></div>
                    </div>
                </div>

                {{-- Official client-approved progress --}}
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">
                                Official Progress
                            </p>

                            <p class="mt-1 text-xs text-slate-400">
                                Controlled by client approvals
                            </p>
                        </div>

                        <p class="shrink-0 text-2xl font-black">
                            {{ $project->official_progress }}%
                        </p>
                    </div>

                    <div class="mt-3 h-3 overflow-hidden rounded-full bg-white/10">
                        <div
                            class="h-full rounded-full bg-gradient-to-r from-emerald-400 to-cyan-300 transition-all duration-500"
                            style="width: {{ min(100, max(0, $project->official_progress)) }}%"
                        ></div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Project navigation tabs --}}
        <nav class="overflow-x-auto rounded-2xl border border-slate-200 bg-white p-2 shadow-sm">
            <div class="flex min-w-max gap-2">
                @foreach ([
                    'overview' => 'Overview',
                    'tasks' => 'Tasks',
                    'tickets' => 'Tickets',
                    'approvals' => 'Approvals',
                    'payments' => 'Payments',
                    'expenses' => 'Expenses & Profit',
                    'notes' => 'Notes',
                    'work-logs' => 'Work Logs',
                    'history' => 'Complete History',
                    'attachments' => 'Attachments',
                    'team' => 'Team',
                    'files' => 'Files',
                    'technical' => 'Technical Details',
                ] as $key => $label)
                    <button
                        type="button"
                        @click="activeTab = '{{ $key }}'"
                        :class="activeTab === '{{ $key }}'
                            ? 'bg-slate-950 text-white'
                            : 'text-slate-600 hover:bg-slate-100'"
                        class="min-h-11 rounded-xl px-4 text-sm font-bold transition"
                    >
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </nav>

        {{-- Overview tab --}}
        <div
            x-show="activeTab === 'overview'"
            x-cloak
        >
            <div class="space-y-6">
                {{-- Pinned project information --}}
                @if ($pinnedNotes->isNotEmpty())
                    <section class="rounded-3xl border border-amber-200 bg-amber-50 p-5 shadow-sm sm:p-6">
                        <div class="flex items-center gap-3">
                            <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-amber-500 text-xl text-white">
                                📌
                            </span>

                            <div>
                                <h2 class="text-lg font-black text-amber-950">
                                    Pinned Project Information
                                </h2>

                                <p class="text-sm text-amber-700">
                                    Important information kept visible for the project team.
                                </p>
                            </div>
                        </div>

                        <div class="mt-5 grid gap-4 lg:grid-cols-2">
                            @foreach ($pinnedNotes as $note)
                                <article class="rounded-2xl border border-amber-200 bg-white p-5">
                                    <div class="flex flex-wrap gap-2">
                                        <span class="rounded-full px-3 py-1 text-xs font-bold ring-1 ring-inset {{ $note->note_type->badgeClasses() }}">
                                            {{ $note->note_type->label() }}
                                        </span>

                                        <span class="rounded-full px-3 py-1 text-xs font-bold ring-1 ring-inset {{ $note->visibility->badgeClasses() }}">
                                            {{ $note->visibility->label() }}
                                        </span>
                                    </div>

                                    @if ($note->title)
                                        <h3 class="mt-3 font-black text-slate-950">
                                            {{ $note->title }}
                                        </h3>
                                    @endif

                                    <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-700">
                                        {{ $note->content }}
                                    </p>

                                    <p class="mt-4 text-xs text-slate-500">
                                        Added by
                                        {{ $note->createdBy?->name ?? 'Unknown' }}
                                    </p>
                                </article>
                            @endforeach
                        </div>
                    </section>
                @endif

                <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
                    @can('payments.view')
                        {{-- Project price --}}
                        <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                            <p class="text-sm text-slate-500">
                                Project Price
                            </p>

                            <p class="mt-2 text-2xl font-black text-slate-950">
                                ₹{{ number_format(
                                    (float) $project->project_price,
                                    2
                                ) }}
                            </p>
                        </article>

                        {{-- Estimated project cost --}}
                        <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                            <p class="text-sm text-slate-500">
                                Estimated Cost
                            </p>

                            <p class="mt-2 text-2xl font-black text-slate-950">
                                ₹{{ number_format(
                                    (float) $project->estimated_cost,
                                    2
                                ) }}
                            </p>

                            <p class="mt-1 text-xs font-semibold text-slate-500">
                                Planned project cost
                            </p>
                        </article>

                        {{-- Actual paid project expenses --}}
                        <article class="rounded-3xl border border-red-200 bg-red-50 p-5 shadow-sm">
                            <p class="text-sm text-red-700">
                                Actual Project Expenses
                            </p>

                            <p class="mt-2 text-2xl font-black text-red-950">
                                ₹{{ number_format(
                                    (float) $project->project_expense_amount,
                                    2
                                ) }}
                            </p>

                            <p class="mt-1 text-xs font-semibold text-red-700">
                                Approved and paid expenses
                            </p>
                        </article>

                        {{-- Actual project profit --}}
                        <article
                            class="rounded-3xl border {{
                                (float) $project->actual_profit_amount >= 0
                                    ? 'border-emerald-200 bg-emerald-50'
                                    : 'border-red-200 bg-red-50'
                            }} p-5 shadow-sm"
                        >
                            <p
                                class="text-sm {{
                                    (float) $project->actual_profit_amount >= 0
                                        ? 'text-emerald-700'
                                        : 'text-red-700'
                                }}"
                            >
                                Actual Project Profit
                            </p>

                            <p
                                class="mt-2 text-2xl font-black {{
                                    (float) $project->actual_profit_amount >= 0
                                        ? 'text-emerald-950'
                                        : 'text-red-950'
                                }}"
                            >
                                ₹{{ number_format(
                                    (float) $project->actual_profit_amount,
                                    2
                                ) }}
                            </p>

                            <p
                                class="mt-1 text-xs font-bold {{
                                    (float) $project->actual_profit_amount >= 0
                                        ? 'text-emerald-700'
                                        : 'text-red-700'
                                }}"
                            >
                                {{ number_format(
                                    (float) $project->profit_margin_percentage,
                                    2
                                ) }}% margin
                            </p>
                        </article>
                    @endcan

                    {{-- Project deadline --}}
                    <article
                        class="rounded-3xl border {{
                            $project->is_delayed
                                ? 'border-red-200 bg-red-50'
                                : 'border-slate-200 bg-white'
                        }} p-5 shadow-sm"
                    >
                        <p
                            class="text-sm {{
                                $project->is_delayed
                                    ? 'text-red-700'
                                    : 'text-slate-500'
                            }}"
                        >
                            Deadline
                        </p>

                        <p
                            class="mt-2 text-xl font-black {{
                                $project->is_delayed
                                    ? 'text-red-950'
                                    : 'text-slate-950'
                            }}"
                        >
                            {{ $project->deadline?->format('d M Y') ?? 'Not provided' }}
                        </p>

                        <p
                            class="mt-1 text-xs font-bold {{
                                $project->is_delayed
                                    ? 'text-red-700'
                                    : 'text-slate-500'
                            }}"
                        >
                            {{ $project->deadline_label }}
                        </p>
                    </article>
                </section>

                <section class="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
                    <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 class="text-lg font-bold text-slate-950">
                            Project Summary
                        </h2>

                        <p class="mt-4 whitespace-pre-line text-sm leading-7 text-slate-600">
                            {{ $project->description ?: 'No project description has been added.' }}
                        </p>

                        @if ($project->internal_remarks)
                            <div class="mt-6 rounded-2xl bg-amber-50 p-4">
                                <p class="text-xs font-bold uppercase tracking-wider text-amber-700">
                                    Internal Remarks
                                </p>

                                <p class="mt-2 whitespace-pre-line text-sm leading-6 text-amber-950">
                                    {{ $project->internal_remarks }}
                                </p>
                            </div>
                        @endif
                    </article>

                    <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 class="text-lg font-bold text-slate-950">
                            Schedule
                        </h2>

                        <dl class="mt-5 space-y-5">
                            @foreach ([
                                'Start Date' => $project->start_date?->format('d M Y'),
                                'Original Delivery' => $project->expected_delivery_date?->format('d M Y'),
                                'Revised Delivery' => $project->revised_delivery_date?->format('d M Y'),
                                'Actual Completion' => $project->actual_completion_date?->format('d M Y'),
                                'Maximum Duration' => $project->maximum_duration_days
                                    ? $project->maximum_duration_days . ' days'
                                    : null,
                            ] as $label => $value)
                                <div>
                                    <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">
                                        {{ $label }}
                                    </dt>

                                    <dd class="mt-1 text-sm font-bold text-slate-800">
                                        {{ $value ?: 'Not provided' }}
                                    </dd>
                                </div>
                            @endforeach
                        </dl>
                    </article>
                </section>
            </div>
        </div>

        {{-- Tasks tab partial --}}
        @include('projects.partials.tasks-tab')

        {{-- Phase 7 Tickets tab partial --}}
        @include('projects.partials.tickets-tab')

        {{-- Approvals tab partial --}}
        @include('projects.partials.approvals-tab')

        {{-- Payments tab partial --}}
        @include('projects.partials.payments-tab')

        {{-- Expenses and profitability tab partial --}}
        @include('projects.partials.expenses-tab')

        {{-- Notes tab partial --}}
        @include('projects.partials.notes-tab')

        {{-- Work logs tab partial --}}
        @include('projects.partials.work-logs-tab')

        {{-- Complete history tab partial --}}
        @include('projects.partials.history-tab')

        {{-- Attachments tab partial --}}
        @include('projects.partials.attachments-tab')

        {{-- Team tab --}}
        <div
            x-show="activeTab === 'team'"
            x-cloak
        >
            <section class="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
                <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-slate-950">
                        Assigned Team
                    </h2>

                    <div class="mt-6 grid gap-3 sm:grid-cols-2">
                        @forelse ($project->team as $member)
                            <div class="flex items-center gap-3 rounded-2xl border border-slate-200 p-4">
                                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-indigo-100 font-black text-indigo-700">
                                    {{ strtoupper(substr($member->name, 0, 1)) }}
                                </div>

                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-bold text-slate-900">
                                        {{ $member->name }}
                                    </p>

                                    <p class="truncate text-xs text-slate-500">
                                        {{
                                            str($member->pivot->assignment_role)
                                                ->replace('_', ' ')
                                                ->title()
                                        }}
                                    </p>
                                </div>

                                @can('projects.assign-team')
                                    @if ($project->manager_id !== $member->id)
                                        <form
                                            method="POST"
                                            action="{{ route(
                                                'projects.members.destroy',
                                                [$project, $member]
                                            ) }}"
                                            onsubmit="return confirm('Remove this team member?')"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button
                                                type="submit"
                                                class="text-xs font-bold text-red-600 transition hover:text-red-800"
                                            >
                                                Remove
                                            </button>
                                        </form>
                                    @endif
                                @endcan
                            </div>
                        @empty
                            <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center sm:col-span-2">
                                <p class="font-bold text-slate-700">
                                    No team members assigned
                                </p>

                                <p class="mt-1 text-sm text-slate-500">
                                    Add project members using the team form.
                                </p>
                            </div>
                        @endforelse
                    </div>
                </article>

                @can('projects.assign-team')
                    <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 class="text-lg font-bold text-slate-950">
                            Add or Update Member
                        </h2>

                        <form
                            method="POST"
                            action="{{ route(
                                'projects.members.store',
                                $project
                            ) }}"
                            class="mt-5 space-y-4"
                        >
                            @csrf

                            <x-form.select
                                label="Team Member"
                                name="user_id"
                                required
                            >
                                <option value="">
                                    Select user
                                </option>

                                @foreach ($availableUsers as $user)
                                    <option
                                        value="{{ $user->id }}"
                                        @selected(
                                            (string) old('user_id')
                                                === (string) $user->id
                                        )
                                    >
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </x-form.select>

                            <x-form.select
                                label="Assignment Role"
                                name="assignment_role"
                                required
                            >
                                <option
                                    value="project_manager"
                                    @selected(
                                        old('assignment_role')
                                            === 'project_manager'
                                    )
                                >
                                    Project Manager
                                </option>

                                <option
                                    value="frontend_developer"
                                    @selected(
                                        old('assignment_role')
                                            === 'frontend_developer'
                                    )
                                >
                                    Frontend Developer
                                </option>

                                <option
                                    value="backend_developer"
                                    @selected(
                                        old('assignment_role')
                                            === 'backend_developer'
                                    )
                                >
                                    Backend Developer
                                </option>

                                <option
                                    value="designer"
                                    @selected(
                                        old('assignment_role')
                                            === 'designer'
                                    )
                                >
                                    UI/UX Designer
                                </option>

                                <option
                                    value="tester"
                                    @selected(
                                        old('assignment_role')
                                            === 'tester'
                                    )
                                >
                                    Tester
                                </option>

                                <option
                                    value="content_manager"
                                    @selected(
                                        old('assignment_role')
                                            === 'content_manager'
                                    )
                                >
                                    Content Manager
                                </option>

                                <option
                                    value="team_member"
                                    @selected(
                                        old(
                                            'assignment_role',
                                            'team_member'
                                        ) === 'team_member'
                                    )
                                >
                                    Team Member
                                </option>
                            </x-form.select>

                            <button
                                type="submit"
                                class="min-h-11 w-full rounded-2xl bg-slate-950 px-4 text-sm font-bold text-white transition hover:bg-indigo-600"
                            >
                                Save Team Member
                            </button>
                        </form>
                    </article>
                @endcan
            </section>
        </div>

        {{-- Files tab --}}
        <div
            x-show="activeTab === 'files'"
            x-cloak
        >
            <section class="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
                <article class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 p-6">
                        <h2 class="text-lg font-bold text-slate-950">
                            Project Documents
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            Agreements, content, screenshots and supporting files.
                        </p>
                    </div>

                    <div class="divide-y divide-slate-100">
                        @forelse ($project->files as $file)
                            <div class="flex flex-col gap-3 p-5 sm:flex-row sm:items-center sm:justify-between">
                                <div class="min-w-0">
                                    <a
                                        href="{{ $file->url }}"
                                        target="_blank"
                                        rel="noopener"
                                        class="block truncate font-bold text-indigo-600 transition hover:text-indigo-700"
                                    >
                                        {{ $file->original_name }}
                                    </a>

                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ ucfirst($file->category) }}
                                        ·
                                        {{ $file->formatted_size }}
                                        ·
                                        Uploaded by
                                        {{ $file->uploadedBy?->name ?? 'Unknown' }}
                                    </p>

                                    @if ($file->description)
                                        <p class="mt-2 text-sm leading-6 text-slate-600">
                                            {{ $file->description }}
                                        </p>
                                    @endif
                                </div>

                                @can('projects.manage-files')
                                    <form
                                        method="POST"
                                        action="{{ route(
                                            'projects.files.destroy',
                                            [$project, $file]
                                        ) }}"
                                        onsubmit="return confirm('Delete this project file?')"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="text-sm font-bold text-red-600 transition hover:text-red-800"
                                        >
                                            Delete
                                        </button>
                                    </form>
                                @endcan
                            </div>
                        @empty
                            <div class="p-12 text-center">
                                <p class="font-bold text-slate-800">
                                    No files uploaded
                                </p>

                                <p class="mt-1 text-sm text-slate-500">
                                    Upload agreements, screenshots, content or project documents.
                                </p>
                            </div>
                        @endforelse
                    </div>
                </article>

                @can('projects.manage-files')
                    <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 class="text-lg font-bold text-slate-950">
                            Upload Files
                        </h2>

                        <form
                            method="POST"
                            action="{{ route(
                                'projects.files.store',
                                $project
                            ) }}"
                            enctype="multipart/form-data"
                            class="mt-5 space-y-4"
                        >
                            @csrf

                            <x-form.select
                                label="File Category"
                                name="category"
                                required
                            >
                                <option value="general">
                                    General
                                </option>

                                <option value="agreement">
                                    Agreement
                                </option>

                                <option value="quotation">
                                    Quotation
                                </option>

                                <option value="content">
                                    Client Content
                                </option>

                                <option value="design">
                                    Design
                                </option>

                                <option value="screenshot">
                                    Screenshot
                                </option>

                                <option value="credentials">
                                    Access Document
                                </option>

                                <option value="approval">
                                    Approval Proof
                                </option>
                            </x-form.select>

                            <label class="block">
                                <span class="mb-2 block text-sm font-semibold text-slate-700">
                                    Select Files
                                </span>

                                <input
                                    type="file"
                                    name="files[]"
                                    multiple
                                    required
                                    class="block w-full rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-4 text-sm"
                                >

                                @error('files')
                                    <span class="mt-1 block text-xs text-red-600">
                                        {{ $message }}
                                    </span>
                                @enderror

                                @error('files.*')
                                    <span class="mt-1 block text-xs text-red-600">
                                        {{ $message }}
                                    </span>
                                @enderror
                            </label>

                            <x-form.textarea
                                label="Description"
                                name="description"
                                rows="3"
                                placeholder="Optional information about the uploaded files."
                            />

                            <button
                                type="submit"
                                class="min-h-11 w-full rounded-2xl bg-slate-950 px-4 text-sm font-bold text-white transition hover:bg-indigo-600"
                            >
                                Upload Files
                            </button>
                        </form>
                    </article>
                @endcan
            </section>
        </div>

        {{-- Technical details tab --}}
        <div
            x-show="activeTab === 'technical'"
            x-cloak
        >
            <section class="grid gap-6 lg:grid-cols-2">
                <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-slate-950">
                        Project URLs
                    </h2>

                    <div class="mt-5 space-y-4">
                        @foreach ([
                            'Reference Website' => $project->reference_url,
                            'Development Website' => $project->development_url,
                            'Live Website' => $project->live_url,
                        ] as $label => $url)
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">
                                    {{ $label }}
                                </p>

                                @if ($url)
                                    <a
                                        href="{{ $url }}"
                                        target="_blank"
                                        rel="noopener"
                                        class="mt-1 block break-all text-sm font-bold text-indigo-600 transition hover:text-indigo-800"
                                    >
                                        {{ $url }}
                                    </a>
                                @else
                                    <p class="mt-1 text-sm text-slate-500">
                                        Not provided
                                    </p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </article>

                <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-slate-950">
                        Domain and Hosting
                    </h2>

                    <dl class="mt-5 space-y-5">
                        @foreach ([
                            'Domain Name' => $project->domain_name,
                            'Hosting Provider' => $project->hosting_provider,
                            'Domain Expiry' => $project->domain_expiry_date?->format('d M Y'),
                            'Hosting Expiry' => $project->hosting_expiry_date?->format('d M Y'),
                        ] as $label => $value)
                            <div>
                                <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">
                                    {{ $label }}
                                </dt>

                                <dd class="mt-1 text-sm font-bold text-slate-800">
                                    {{ $value ?: 'Not provided' }}
                                </dd>
                            </div>
                        @endforeach
                    </dl>
                </article>

                <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2">
                    <h2 class="text-lg font-bold text-slate-950">
                        Project Administration
                    </h2>

                    <dl class="mt-5 grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">
                                Project Manager
                            </dt>

                            <dd class="mt-1 text-sm font-bold text-slate-800">
                                {{ $project->manager?->name ?? 'Not assigned' }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">
                                Created By
                            </dt>

                            <dd class="mt-1 text-sm font-bold text-slate-800">
                                {{ $project->createdBy?->name ?? 'Unknown' }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">
                                Created On
                            </dt>

                            <dd class="mt-1 text-sm font-bold text-slate-800">
                                {{ $project->created_at?->format('d M Y, h:i A') ?? 'Unknown' }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">
                                Last Updated By
                            </dt>

                            <dd class="mt-1 text-sm font-bold text-slate-800">
                                {{ $project->updatedBy?->name ?? 'Unknown' }}
                            </dd>
                        </div>
                    </dl>
                </article>
            </section>
        </div>
    </div>
@endsection