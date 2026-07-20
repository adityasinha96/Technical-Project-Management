@extends('layouts.admin')

@section('title', $project->name)
@section('page-title', 'Project Details')

@section('content')
    <div
        x-data="{ activeTab: 'overview' }"
        class="space-y-6"
    >
        <section class="relative overflow-hidden rounded-3xl bg-slate-950 p-6 text-white shadow-2xl sm:p-8">
            <div class="absolute -right-20 -top-20 h-72 w-72 rounded-full bg-indigo-500/30 blur-3xl"></div>

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
                        {{ $project->client->display_name }}
                        ·
                        {{ $project->category?->name ?? 'Uncategorised' }}
                    </p>
                </div>

                <div class="flex flex-wrap gap-3">
                    @can('projects.update')
                        <a
                            href="{{ route('projects.edit', $project) }}"
                            class="inline-flex min-h-11 items-center rounded-2xl bg-white px-4 text-sm font-bold text-slate-950"
                        >
                            Edit Project
                        </a>
                    @endcan

                    <a
                        href="{{ route('projects.index') }}"
                        class="inline-flex min-h-11 items-center rounded-2xl border border-white/20 px-4 text-sm font-bold text-white"
                    >
                        All Projects
                    </a>
                </div>
            </div>

            <div class="relative mt-7">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">
                        Official Client-Approved Progress
                    </p>

                    <p class="text-xl font-black">
                        {{ $project->official_progress }}%
                    </p>
                </div>

                <div class="mt-3 h-3 overflow-hidden rounded-full bg-white/10">
                    <div
                        class="h-full rounded-full bg-gradient-to-r from-indigo-400 to-cyan-300"
                        style="width: {{ $project->official_progress }}%"
                    ></div>
                </div>

                <p class="mt-2 text-xs text-slate-400">
                    Frontend approval will mark 50%. Backend approval will mark 100% in Phase 3.
                </p>
            </div>
        </section>

        <nav class="overflow-x-auto rounded-2xl border border-slate-200 bg-white p-2 shadow-sm">
            <div class="flex min-w-max gap-2">
                @foreach ([
                    'overview' => 'Overview',
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

        <div x-show="activeTab === 'overview'" x-cloak>
            <div class="space-y-6">
                <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    @can('payments.view')
                        <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                            <p class="text-sm text-slate-500">
                                Project Price
                            </p>

                            <p class="mt-2 text-2xl font-black text-slate-950">
                                ₹{{ number_format(
                                    $project->project_price,
                                    2
                                ) }}
                            </p>
                        </article>

                        <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                            <p class="text-sm text-slate-500">
                                Estimated Cost
                            </p>

                            <p class="mt-2 text-2xl font-black text-slate-950">
                                ₹{{ number_format(
                                    $project->estimated_cost,
                                    2
                                ) }}
                            </p>
                        </article>

                        <article class="rounded-3xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm">
                            <p class="text-sm text-emerald-700">
                                Expected Profit
                            </p>

                            <p class="mt-2 text-2xl font-black text-emerald-950">
                                ₹{{ number_format(
                                    $project->expected_profit,
                                    2
                                ) }}
                            </p>

                            <p class="mt-1 text-xs font-bold text-emerald-700">
                                {{ $project->expected_profit_percentage }}%
                            </p>
                        </article>
                    @endcan

                    <article class="rounded-3xl border {{ $project->is_delayed ? 'border-red-200 bg-red-50' : 'border-slate-200 bg-white' }} p-5 shadow-sm">
                        <p class="text-sm {{ $project->is_delayed ? 'text-red-700' : 'text-slate-500' }}">
                            Deadline
                        </p>

                        <p class="mt-2 text-xl font-black {{ $project->is_delayed ? 'text-red-950' : 'text-slate-950' }}">
                            {{ $project->deadline?->format('d M Y') }}
                        </p>

                        <p class="mt-1 text-xs font-bold {{ $project->is_delayed ? 'text-red-700' : 'text-slate-500' }}">
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
                                'Maximum Duration' => $project->maximum_duration_days . ' days',
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

        <div x-show="activeTab === 'team'" x-cloak>
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
                                        {{ str($member->pivot->assignment_role)->replace('_', ' ')->title() }}
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

                                            <button class="text-xs font-bold text-red-600">
                                                Remove
                                            </button>
                                        </form>
                                    @endif
                                @endcan
                            </div>
                        @empty
                            <p class="text-sm text-slate-500">
                                No team members assigned.
                            </p>
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
                                <option value="">Select user</option>

                                @foreach ($availableUsers as $user)
                                    <option value="{{ $user->id }}">
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </x-form.select>

                            <x-form.select
                                label="Assignment Role"
                                name="assignment_role"
                                required
                            >
                                <option value="project_manager">
                                    Project Manager
                                </option>
                                <option value="frontend_developer">
                                    Frontend Developer
                                </option>
                                <option value="backend_developer">
                                    Backend Developer
                                </option>
                                <option value="designer">
                                    UI/UX Designer
                                </option>
                                <option value="tester">
                                    Tester
                                </option>
                                <option value="content_manager">
                                    Content Manager
                                </option>
                                <option value="team_member" selected>
                                    Team Member
                                </option>
                            </x-form.select>

                            <button class="min-h-11 w-full rounded-2xl bg-slate-950 px-4 text-sm font-bold text-white">
                                Save Team Member
                            </button>
                        </form>
                    </article>
                @endcan
            </section>
        </div>

        <div x-show="activeTab === 'files'" x-cloak>
            <section class="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
                <article class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 p-6">
                        <h2 class="text-lg font-bold text-slate-950">
                            Project Documents
                        </h2>
                    </div>

                    <div class="divide-y divide-slate-100">
                        @forelse ($project->files as $file)
                            <div class="flex flex-col gap-3 p-5 sm:flex-row sm:items-center sm:justify-between">
                                <div class="min-w-0">
                                    <a
                                        href="{{ $file->url }}"
                                        target="_blank"
                                        rel="noopener"
                                        class="block truncate font-bold text-indigo-600 hover:text-indigo-700"
                                    >
                                        {{ $file->original_name }}
                                    </a>

                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ ucfirst($file->category) }}
                                        · {{ $file->formatted_size }}
                                        · Uploaded by
                                        {{ $file->uploadedBy?->name ?? 'Unknown' }}
                                    </p>

                                    @if ($file->description)
                                        <p class="mt-2 text-sm text-slate-600">
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

                                        <button class="text-sm font-bold text-red-600">
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
                                <option value="general">General</option>
                                <option value="agreement">Agreement</option>
                                <option value="quotation">Quotation</option>
                                <option value="content">Client Content</option>
                                <option value="design">Design</option>
                                <option value="screenshot">Screenshot</option>
                                <option value="credentials">Access Document</option>
                                <option value="approval">Approval Proof</option>
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
                            />

                            <button class="min-h-11 w-full rounded-2xl bg-slate-950 px-4 text-sm font-bold text-white">
                                Upload Files
                            </button>
                        </form>
                    </article>
                @endcan
            </section>
        </div>

        <div x-show="activeTab === 'technical'" x-cloak>
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
                                        class="mt-1 block break-all text-sm font-bold text-indigo-600"
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
            </section>
        </div>
    </div>
@endsection