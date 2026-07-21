<div x-show="activeTab === 'approvals'" x-cloak>
    @php
        $frontendApproved = $project->hasApprovedStage(
            \App\Enums\ApprovalStage::Frontend
        );

        $backendApproved = $project->hasApprovedStage(
            \App\Enums\ApprovalStage::Backend
        );

        $frontendPending = $project->hasPendingApproval(
            \App\Enums\ApprovalStage::Frontend
        );

        $backendPending = $project->hasPendingApproval(
            \App\Enums\ApprovalStage::Backend
        );
    @endphp

    <div class="space-y-6">
        <section class="grid gap-4 md:grid-cols-2">
            <article class="rounded-3xl border {{ $frontendApproved ? 'border-emerald-200 bg-emerald-50' : 'border-slate-200 bg-white' }} p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">
                            Stage One
                        </p>

                        <h2 class="mt-1 text-xl font-black text-slate-950">
                            Frontend Approval
                        </h2>
                    </div>

                    <div class="text-3xl font-black {{ $frontendApproved ? 'text-emerald-600' : 'text-slate-300' }}">
                        50%
                    </div>
                </div>

                <p class="mt-4 text-sm text-slate-600">
                    Official project progress becomes 50% only after approval.
                </p>

                <div class="mt-5">
                    @if ($frontendApproved)
                        <span class="inline-flex rounded-full bg-emerald-600 px-4 py-2 text-sm font-bold text-white">
                            Approved
                        </span>
                    @elseif ($frontendPending)
                        <span class="inline-flex rounded-full bg-cyan-100 px-4 py-2 text-sm font-bold text-cyan-700">
                            Waiting for Client Review
                        </span>
                    @else
                        <span class="inline-flex rounded-full bg-slate-100 px-4 py-2 text-sm font-bold text-slate-600">
                            Not Approved
                        </span>
                    @endif
                </div>
            </article>

            <article class="rounded-3xl border {{ $backendApproved ? 'border-emerald-200 bg-emerald-50' : 'border-slate-200 bg-white' }} p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">
                            Stage Two
                        </p>

                        <h2 class="mt-1 text-xl font-black text-slate-950">
                            Backend Approval
                        </h2>
                    </div>

                    <div class="text-3xl font-black {{ $backendApproved ? 'text-emerald-600' : 'text-slate-300' }}">
                        100%
                    </div>
                </div>

                <p class="mt-4 text-sm text-slate-600">
                    Backend approval completes the project and marks official progress as 100%.
                </p>

                <div class="mt-5">
                    @if ($backendApproved)
                        <span class="inline-flex rounded-full bg-emerald-600 px-4 py-2 text-sm font-bold text-white">
                            Approved and Completed
                        </span>
                    @elseif (!$frontendApproved)
                        <span class="inline-flex rounded-full bg-amber-100 px-4 py-2 text-sm font-bold text-amber-700">
                            Frontend Approval Required
                        </span>
                    @elseif ($backendPending)
                        <span class="inline-flex rounded-full bg-cyan-100 px-4 py-2 text-sm font-bold text-cyan-700">
                            Waiting for Client Review
                        </span>
                    @else
                        <span class="inline-flex rounded-full bg-slate-100 px-4 py-2 text-sm font-bold text-slate-600">
                            Not Approved
                        </span>
                    @endif
                </div>
            </article>
        </section>

        @can('approvals.manage')
            <section class="grid gap-6 lg:grid-cols-2">
                @if (!$frontendApproved && !$frontendPending)
                    <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 class="text-lg font-bold text-slate-950">
                            Submit Frontend for Approval
                        </h2>

                        <form
                            method="POST"
                            action="{{ route(
                                'projects.approvals.store',
                                $project
                            ) }}"
                            enctype="multipart/form-data"
                            class="mt-5 space-y-4"
                        >
                            @csrf

                            <input
                                type="hidden"
                                name="stage"
                                value="frontend"
                            >

                            <x-form.textarea
                                label="Submission Notes"
                                name="submission_notes"
                                rows="4"
                                placeholder="Mention the completed pages, development URL and review instructions."
                            />

                            <label class="block">
                                <span class="mb-2 block text-sm font-semibold text-slate-700">
                                    Approval Proof or Screenshot
                                </span>

                                <input
                                    type="file"
                                    name="proof"
                                    accept=".pdf,.png,.jpg,.jpeg,.webp"
                                    class="w-full rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-4 text-sm"
                                >
                            </label>

                            <button class="min-h-11 w-full rounded-2xl bg-indigo-600 px-4 text-sm font-bold text-white">
                                Submit Frontend
                            </button>
                        </form>
                    </article>
                @endif

                @if (
                    $frontendApproved &&
                    !$backendApproved &&
                    !$backendPending
                )
                    <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 class="text-lg font-bold text-slate-950">
                            Submit Backend for Approval
                        </h2>

                        <form
                            method="POST"
                            action="{{ route(
                                'projects.approvals.store',
                                $project
                            ) }}"
                            enctype="multipart/form-data"
                            class="mt-5 space-y-4"
                        >
                            @csrf

                            <input
                                type="hidden"
                                name="stage"
                                value="backend"
                            >

                            <x-form.textarea
                                label="Submission Notes"
                                name="submission_notes"
                                rows="4"
                                placeholder="Mention completed backend modules, testing details and client review instructions."
                            />

                            <label class="block">
                                <span class="mb-2 block text-sm font-semibold text-slate-700">
                                    Approval Proof or Screenshot
                                </span>

                                <input
                                    type="file"
                                    name="proof"
                                    accept=".pdf,.png,.jpg,.jpeg,.webp"
                                    class="w-full rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-4 text-sm"
                                >
                            </label>

                            <button class="min-h-11 w-full rounded-2xl bg-indigo-600 px-4 text-sm font-bold text-white">
                                Submit Backend
                            </button>
                        </form>
                    </article>
                @endif
            </section>
        @endcan

        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 p-6">
                <h2 class="text-lg font-bold text-slate-950">
                    Approval History
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Every submission and client response is preserved.
                </p>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse ($project->approvals as $approval)
                    <article class="p-6">
                        <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap gap-2">
                                    <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-bold text-indigo-700">
                                        {{ $approval->stage->label() }}
                                        #{{ $approval->submission_number }}
                                    </span>

                                    <span class="rounded-full px-3 py-1 text-xs font-bold ring-1 ring-inset {{ $approval->status->badgeClasses() }}">
                                        {{ $approval->status->label() }}
                                    </span>
                                </div>

                                <p class="mt-3 text-sm text-slate-600">
                                    Submitted by
                                    <strong>
                                        {{ $approval->submittedBy?->name ?? 'Unknown' }}
                                    </strong>
                                    on
                                    {{ $approval->submitted_at->format('d M Y, h:i A') }}
                                </p>

                                @if ($approval->submission_notes)
                                    <div class="mt-4 rounded-2xl bg-slate-50 p-4">
                                        <p class="text-xs font-bold uppercase tracking-wider text-slate-400">
                                            Submission Notes
                                        </p>

                                        <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-700">
                                            {{ $approval->submission_notes }}
                                        </p>
                                    </div>
                                @endif

                                @if ($approval->client_remarks)
                                    <div class="mt-4 rounded-2xl bg-amber-50 p-4">
                                        <p class="text-xs font-bold uppercase tracking-wider text-amber-700">
                                            Client Remarks
                                        </p>

                                        <p class="mt-2 whitespace-pre-line text-sm leading-6 text-amber-950">
                                            {{ $approval->client_remarks }}
                                        </p>
                                    </div>
                                @endif

                                @if ($approval->proofFile)
                                    <a
                                        href="{{ $approval->proofFile->url }}"
                                        target="_blank"
                                        rel="noopener"
                                        class="mt-4 inline-flex rounded-xl bg-slate-100 px-4 py-2 text-sm font-bold text-indigo-600"
                                    >
                                        Open Approval Proof
                                    </a>
                                @endif

                                @if ($approval->reviewed_at)
                                    <p class="mt-4 text-xs text-slate-500">
                                        Reviewed by
                                        {{ $approval->reviewedBy?->name ?? 'Unknown' }}
                                        on
                                        {{ $approval->reviewed_at->format('d M Y, h:i A') }}
                                    </p>

                                    @if ($approval->client_reviewer_name)
                                        <p class="mt-1 text-xs text-slate-500">
                                            Client reviewer:
                                            {{ $approval->client_reviewer_name }}
                                        </p>
                                    @endif
                                @endif
                            </div>

                            @can('approvals.manage')
                                @if (
                                    $approval->status ===
                                    \App\Enums\ApprovalStatus::Submitted
                                )
                                    <details class="w-full rounded-2xl border border-slate-200 p-4 xl:w-96">
                                        <summary class="cursor-pointer font-bold text-slate-900">
                                            Record Client Response
                                        </summary>

                                        <form
                                            method="POST"
                                            action="{{ route(
                                                'projects.approvals.review',
                                                [$project, $approval]
                                            ) }}"
                                            class="mt-5 space-y-4"
                                        >
                                            @csrf
                                            @method('PUT')

                                            <x-form.select
                                                label="Decision"
                                                name="status"
                                                required
                                            >
                                                <option value="approved">
                                                    Approved
                                                </option>

                                                <option value="changes_requested">
                                                    Changes Requested
                                                </option>

                                                <option value="rejected">
                                                    Rejected
                                                </option>
                                            </x-form.select>

                                            <x-form.input
                                                label="Client Reviewer Name"
                                                name="client_reviewer_name"
                                            />

                                            <x-form.input
                                                label="Client Reviewer Email"
                                                name="client_reviewer_email"
                                                type="email"
                                            />

                                            <x-form.input
                                                label="Client Reviewer Phone"
                                                name="client_reviewer_phone"
                                            />

                                            <x-form.textarea
                                                label="Client Remarks"
                                                name="client_remarks"
                                                rows="4"
                                            />

                                            <x-form.textarea
                                                label="Internal Remarks"
                                                name="internal_remarks"
                                                rows="3"
                                            />

                                            <button class="min-h-11 w-full rounded-2xl bg-slate-950 px-4 text-sm font-bold text-white">
                                                Save Client Response
                                            </button>
                                        </form>
                                    </details>
                                @endif
                            @endcan
                        </div>
                    </article>
                @empty
                    <div class="p-12 text-center">
                        <p class="font-bold text-slate-900">
                            No approval submissions
                        </p>

                        <p class="mt-1 text-sm text-slate-500">
                            Frontend and backend submissions will appear here.
                        </p>
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</div>