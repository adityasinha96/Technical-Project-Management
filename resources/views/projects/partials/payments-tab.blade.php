<div x-show="activeTab === 'payments'" x-cloak>
    <div class="space-y-6">
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">
                    Project Price
                </p>

                <p class="mt-2 text-2xl font-black text-slate-950">
                    ₹{{ number_format(
                        $project->project_price,
                        2
                    ) }}
                </p>
            </article>

            <article class="rounded-3xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm">
                <p class="text-sm font-medium text-emerald-700">
                    Net Received
                </p>

                <p class="mt-2 text-2xl font-black text-emerald-950">
                    ₹{{ number_format(
                        $project->net_received_amount,
                        2
                    ) }}
                </p>
            </article>

            <article class="rounded-3xl border {{ $project->pending_amount > 0 ? 'border-amber-200 bg-amber-50' : 'border-emerald-200 bg-emerald-50' }} p-5 shadow-sm">
                <p class="text-sm font-medium {{ $project->pending_amount > 0 ? 'text-amber-700' : 'text-emerald-700' }}">
                    Pending Amount
                </p>

                <p class="mt-2 text-2xl font-black {{ $project->pending_amount > 0 ? 'text-amber-950' : 'text-emerald-950' }}">
                    ₹{{ number_format(
                        $project->pending_amount,
                        2
                    ) }}
                </p>

                @if ($project->overpaid_amount > 0)
                    <p class="mt-1 text-xs font-bold text-indigo-700">
                        Overpaid:
                        ₹{{ number_format(
                            $project->overpaid_amount,
                            2
                        ) }}
                    </p>
                @endif
            </article>

            <article class="rounded-3xl border border-indigo-200 bg-indigo-50 p-5 shadow-sm">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-indigo-700">
                            Collection
                        </p>

                        <p class="mt-2 text-2xl font-black text-indigo-950">
                            {{ number_format(
                                $project->collection_percentage,
                                2
                            ) }}%
                        </p>
                    </div>

                    @if ($project->is_fully_paid)
                        <span class="rounded-full bg-emerald-600 px-3 py-1 text-xs font-bold text-white">
                            Fully Paid
                        </span>
                    @endif
                </div>

                <div class="mt-4 h-2 overflow-hidden rounded-full bg-indigo-200">
                    <div
                        class="h-full rounded-full bg-indigo-600"
                        style="width: {{ $project->collection_bar_percentage }}%"
                    ></div>
                </div>
            </article>
        </section>

        <section class="grid gap-6 xl:grid-cols-[1.3fr_0.7fr]">
            <article class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-100 p-5">
                    <div>
                        <h2 class="text-lg font-bold text-slate-950">
                            Payment Ledger
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            All installments, refunds and payment statuses.
                        </p>
                    </div>

                    <a
                        href="{{ route('payments.index', [
                            'project_id' => $project->id
                        ]) }}"
                        class="text-sm font-bold text-indigo-600"
                    >
                        Full Ledger
                    </a>
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse ($project->payments as $payment)
                        <article class="{{ $payment->is_voided ? 'bg-red-50/40' : '' }} p-5">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap gap-2">
                                        <span class="rounded-full px-3 py-1 text-xs font-bold ring-1 ring-inset {{ $payment->kind->badgeClasses() }}">
                                            {{ $payment->kind->label() }}
                                        </span>

                                        @if ($payment->is_voided)
                                            <span class="rounded-full bg-red-600 px-3 py-1 text-xs font-bold text-white">
                                                Voided
                                            </span>
                                        @else
                                            <span class="rounded-full px-3 py-1 text-xs font-bold ring-1 ring-inset {{ $payment->status->badgeClasses() }}">
                                                {{ $payment->status->label() }}
                                            </span>
                                        @endif
                                    </div>

                                    <a
                                        href="{{ route(
                                            'payments.show',
                                            $payment
                                        ) }}"
                                        class="mt-3 block font-black text-slate-950 hover:text-indigo-600"
                                    >
                                        {{ $payment->payment_number }}
                                    </a>

                                    <p class="mt-1 text-sm text-slate-500">
                                        {{ $payment->payment_type->label() }}
                                        · {{ $payment->payment_mode->label() }}
                                        · {{ $payment->payment_date->format('d M Y') }}
                                    </p>

                                    @if ($payment->transaction_reference)
                                        <p class="mt-2 text-xs text-slate-500">
                                            Reference:
                                            {{ $payment->transaction_reference }}
                                        </p>
                                    @endif

                                    @if ($payment->is_voided)
                                        <div class="mt-3 rounded-2xl bg-red-100 p-3 text-sm text-red-800">
                                            <strong>Void reason:</strong>
                                            {{ $payment->void_reason }}
                                        </div>
                                    @endif
                                </div>

                                <div class="text-left lg:text-right">
                                    <p class="text-2xl font-black {{ $payment->kind === \App\Enums\PaymentKind::Refund ? 'text-red-700' : 'text-emerald-700' }}">
                                        {{ $payment->kind === \App\Enums\PaymentKind::Refund ? '−' : '+' }}
                                        ₹{{ number_format(
                                            $payment->amount,
                                            2
                                        ) }}
                                    </p>

                                    <p class="mt-1 text-xs text-slate-500">
                                        Recorded by
                                        {{ $payment->createdBy?->name ?? 'Unknown' }}
                                    </p>

                                    @if (
                                        $payment->status ===
                                        \App\Enums\PaymentStatus::Pending &&
                                        !$payment->is_voided
                                    )
                                        @can('payments.update')
                                            <details class="mt-3">
                                                <summary class="cursor-pointer text-sm font-bold text-indigo-600">
                                                    Update Status
                                                </summary>

                                                <form
                                                    method="POST"
                                                    action="{{ route(
                                                        'payments.status.update',
                                                        $payment
                                                    ) }}"
                                                    class="mt-3 space-y-3 rounded-2xl bg-slate-50 p-3 text-left"
                                                >
                                                    @csrf
                                                    @method('PUT')

                                                    <select
                                                        name="status"
                                                        required
                                                        class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm"
                                                    >
                                                        <option value="cleared">
                                                            Cleared
                                                        </option>

                                                        <option value="failed">
                                                            Failed
                                                        </option>

                                                        <option value="cancelled">
                                                            Cancelled
                                                        </option>
                                                    </select>

                                                    <button class="w-full rounded-xl bg-slate-950 px-3 py-2 text-sm font-bold text-white">
                                                        Save Status
                                                    </button>
                                                </form>
                                            </details>
                                        @endcan
                                    @endif

                                    @if (
                                        $payment->status ===
                                        \App\Enums\PaymentStatus::Cleared &&
                                        !$payment->is_voided
                                    )
                                        @can('payments.delete')
                                            <details class="mt-3">
                                                <summary class="cursor-pointer text-sm font-bold text-red-600">
                                                    Void Entry
                                                </summary>

                                                <form
                                                    method="POST"
                                                    action="{{ route(
                                                        'payments.void',
                                                        $payment
                                                    ) }}"
                                                    class="mt-3 space-y-3 rounded-2xl bg-red-50 p-3 text-left"
                                                >
                                                    @csrf
                                                    @method('PUT')

                                                    <textarea
                                                        name="void_reason"
                                                        rows="3"
                                                        required
                                                        minlength="10"
                                                        placeholder="Explain why this entry is being voided."
                                                        class="w-full rounded-xl border border-red-200 bg-white px-3 py-2 text-sm"
                                                    ></textarea>

                                                    <button class="w-full rounded-xl bg-red-600 px-3 py-2 text-sm font-bold text-white">
                                                        Confirm Void
                                                    </button>
                                                </form>
                                            </details>
                                        @endcan
                                    @endif
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="p-12 text-center">
                            <p class="font-bold text-slate-900">
                                No payments recorded
                            </p>

                            <p class="mt-1 text-sm text-slate-500">
                                Add the first payment installment for this project.
                            </p>
                        </div>
                    @endforelse
                </div>
            </article>

            @can('payments.create')
                <article class="h-fit rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="text-lg font-bold text-slate-950">
                        Record Payment
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Add a receipt, pending payment or refund.
                    </p>

                    <form
                        method="POST"
                        action="{{ route(
                            'projects.payments.store',
                            $project
                        ) }}"
                        enctype="multipart/form-data"
                        class="mt-5 space-y-4"
                        x-data="{
                            kind: 'receipt',
                            status: 'cleared'
                        }"
                    >
                        @csrf

                        <x-form.select
                            label="Entry Type"
                            name="kind"
                            x-model="kind"
                            required
                        >
                            @foreach ($paymentKinds as $kind)
                                <option value="{{ $kind->value }}">
                                    {{ $kind->label() }}
                                </option>
                            @endforeach
                        </x-form.select>

                        <label class="block">
                            <span class="mb-2 block text-sm font-semibold text-slate-700">
                                Payment Type
                                <span class="text-red-500">*</span>
                            </span>

                            <select
                                name="payment_type"
                                required
                                class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm"
                            >
                                <template x-if="kind === 'refund'">
                                    <option value="refund">
                                        Refund
                                    </option>
                                </template>

                                <template x-if="kind === 'receipt'">
                                    <optgroup label="Payment Types">
                                        @foreach ($paymentTypes as $type)
                                            @continue(
                                                $type ===
                                                \App\Enums\PaymentType::Refund
                                            )

                                            <option value="{{ $type->value }}">
                                                {{ $type->label() }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                </template>
                            </select>
                        </label>

                        <x-form.input
                            label="Amount"
                            name="amount"
                            type="number"
                            min="0.01"
                            step="0.01"
                            required
                        />

                        <x-form.input
                            label="Payment Date"
                            name="payment_date"
                            type="date"
                            :value="today()->format('Y-m-d')"
                            required
                        />

                        <x-form.select
                            label="Payment Mode"
                            name="payment_mode"
                            required
                        >
                            @foreach ($paymentModes as $mode)
                                <option value="{{ $mode->value }}">
                                    {{ $mode->label() }}
                                </option>
                            @endforeach
                        </x-form.select>

                        <x-form.select
                            label="Payment Status"
                            name="status"
                            x-model="status"
                            required
                        >
                            <option value="cleared">
                                Cleared
                            </option>

                            <option value="pending">
                                Pending Clearance
                            </option>
                        </x-form.select>

                        <div x-show="status === 'pending'" x-cloak>
                            <x-form.input
                                label="Expected Clearance Date"
                                name="expected_clearance_date"
                                type="date"
                            />
                        </div>

                        <x-form.input
                            label="Received From"
                            name="received_from"
                            :value="$project->client->name"
                        />

                        <x-form.input
                            label="Bank / UPI Reference"
                            name="transaction_reference"
                        />

                        <x-form.input
                            label="Bank Name"
                            name="bank_name"
                        />

                        <x-form.input
                            label="Invoice Number"
                            name="invoice_number"
                        />

                        <x-form.textarea
                            label="Remarks"
                            name="remarks"
                            rows="3"
                        />

                        <label class="block">
                            <span class="mb-2 block text-sm font-semibold text-slate-700">
                                Payment Proof
                            </span>

                            <input
                                type="file"
                                name="proof"
                                accept=".pdf,.png,.jpg,.jpeg,.webp"
                                class="w-full rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-4 text-sm"
                            >
                        </label>

                        <button class="min-h-11 w-full rounded-2xl bg-slate-950 px-4 text-sm font-bold text-white">
                            Record Payment
                        </button>
                    </form>
                </article>
            @endcan
        </section>

        <section class="grid gap-6 xl:grid-cols-[1.3fr_0.7fr]">
            <article class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 p-5">
                    <h2 class="text-lg font-bold text-slate-950">
                        Payment Follow-ups
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Track calls, promises and next collection dates.
                    </p>
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse ($project->paymentFollowups as $followup)
                        <article class="p-5">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap gap-2">
                                        <span class="rounded-full px-3 py-1 text-xs font-bold ring-1 ring-inset {{ $followup->status->badgeClasses() }}">
                                            {{ $followup->status->label() }}
                                        </span>

                                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">
                                            {{ $followup->channel->label() }}
                                        </span>

                                        @if ($followup->is_overdue)
                                            <span class="rounded-full bg-red-600 px-3 py-1 text-xs font-bold text-white">
                                                Follow-up Overdue
                                            </span>
                                        @endif
                                    </div>

                                    <p class="mt-3 font-bold text-slate-950">
                                        {{ $followup->client_contact_name ?: $project->client->name }}
                                    </p>

                                    <p class="mt-1 text-sm text-slate-500">
                                        Follow-up:
                                        {{ $followup->followup_at->format('d M Y, h:i A') }}
                                    </p>

                                    @if ($followup->next_followup_at)
                                        <p class="mt-1 text-sm font-semibold {{ $followup->is_overdue ? 'text-red-600' : 'text-indigo-600' }}">
                                            Next:
                                            {{ $followup->next_followup_at->format('d M Y, h:i A') }}
                                        </p>
                                    @endif

                                    @if ($followup->promised_amount)
                                        <div class="mt-3 rounded-2xl bg-indigo-50 p-3 text-sm text-indigo-900">
                                            Promised:
                                            <strong>
                                                ₹{{ number_format(
                                                    $followup->promised_amount,
                                                    2
                                                ) }}
                                            </strong>

                                            @if ($followup->promised_payment_date)
                                                by
                                                <strong>
                                                    {{ $followup->promised_payment_date->format('d M Y') }}
                                                </strong>
                                            @endif
                                        </div>
                                    @endif

                                    @if ($followup->client_response)
                                        <p class="mt-3 whitespace-pre-line text-sm leading-6 text-slate-600">
                                            {{ $followup->client_response }}
                                        </p>
                                    @endif
                                </div>

                                <div class="text-xs text-slate-500 lg:text-right">
                                    <p>
                                        Assigned to:
                                        <strong>
                                            {{ $followup->assignedTo?->name ?? 'Not assigned' }}
                                        </strong>
                                    </p>

                                    @can('payments.followup')
                                        <details class="mt-3">
                                            <summary class="cursor-pointer text-sm font-bold text-indigo-600">
                                                Update
                                            </summary>

                                            <form
                                                method="POST"
                                                action="{{ route(
                                                    'projects.payment-followups.update',
                                                    [$project, $followup]
                                                ) }}"
                                                class="mt-3 min-w-72 space-y-3 rounded-2xl bg-slate-50 p-4 text-left"
                                            >
                                                @csrf
                                                @method('PUT')

                                                <input
                                                    type="hidden"
                                                    name="channel"
                                                    value="{{ $followup->channel->value }}"
                                                >

                                                <input
                                                    type="hidden"
                                                    name="followup_at"
                                                    value="{{ $followup->followup_at->format('Y-m-d\TH:i') }}"
                                                >

                                                <input
                                                    type="hidden"
                                                    name="assigned_to"
                                                    value="{{ $followup->assigned_to }}"
                                                >

                                                <input
                                                    type="hidden"
                                                    name="client_contact_name"
                                                    value="{{ $followup->client_contact_name }}"
                                                >

                                                <select
                                                    name="status"
                                                    class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm"
                                                >
                                                    @foreach ($followupStatuses as $status)
                                                        <option
                                                            value="{{ $status->value }}"
                                                            @selected(
                                                                $followup->status ===
                                                                $status
                                                            )
                                                        >
                                                            {{ $status->label() }}
                                                        </option>
                                                    @endforeach
                                                </select>

                                                <input
                                                    type="datetime-local"
                                                    name="next_followup_at"
                                                    value="{{ $followup->next_followup_at?->format('Y-m-d\TH:i') }}"
                                                    class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm"
                                                >

                                                <input
                                                    type="date"
                                                    name="promised_payment_date"
                                                    value="{{ $followup->promised_payment_date?->format('Y-m-d') }}"
                                                    class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm"
                                                >

                                                <input
                                                    type="number"
                                                    name="promised_amount"
                                                    min="0.01"
                                                    step="0.01"
                                                    value="{{ $followup->promised_amount }}"
                                                    placeholder="Promised amount"
                                                    class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm"
                                                >

                                                <textarea
                                                    name="client_response"
                                                    rows="3"
                                                    placeholder="Client response"
                                                    class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm"
                                                >{{ $followup->client_response }}</textarea>

                                                <textarea
                                                    name="notes"
                                                    rows="2"
                                                    placeholder="Internal notes"
                                                    class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm"
                                                >{{ $followup->notes }}</textarea>

                                                <button class="w-full rounded-xl bg-slate-950 px-3 py-2 text-sm font-bold text-white">
                                                    Save Follow-up
                                                </button>
                                            </form>
                                        </details>
                                    @endcan
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="p-12 text-center text-sm text-slate-500">
                            No payment follow-ups recorded.
                        </div>
                    @endforelse
                </div>
            </article>

            @can('payments.followup')
                <article class="h-fit rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="text-lg font-bold text-slate-950">
                        Add Follow-up
                    </h2>

                    <form
                        method="POST"
                        action="{{ route(
                            'projects.payment-followups.store',
                            $project
                        ) }}"
                        class="mt-5 space-y-4"
                    >
                        @csrf

                        <x-form.select
                            label="Follow-up Channel"
                            name="channel"
                            required
                        >
                            @foreach ($followupChannels as $channel)
                                <option value="{{ $channel->value }}">
                                    {{ $channel->label() }}
                                </option>
                            @endforeach
                        </x-form.select>

                        <x-form.select
                            label="Status"
                            name="status"
                            required
                        >
                            @foreach ($followupStatuses as $status)
                                <option value="{{ $status->value }}">
                                    {{ $status->label() }}
                                </option>
                            @endforeach
                        </x-form.select>

                        <x-form.input
                            label="Follow-up Date and Time"
                            name="followup_at"
                            type="datetime-local"
                            :value="now()->format('Y-m-d\TH:i')"
                            required
                        />

                        <x-form.input
                            label="Next Follow-up"
                            name="next_followup_at"
                            type="datetime-local"
                        />

                        <x-form.input
                            label="Promised Payment Date"
                            name="promised_payment_date"
                            type="date"
                        />

                        <x-form.input
                            label="Promised Amount"
                            name="promised_amount"
                            type="number"
                            min="0.01"
                            step="0.01"
                        />

                        <x-form.input
                            label="Client Contact Name"
                            name="client_contact_name"
                            :value="$project->client->name"
                        />

                        <x-form.select
                            label="Assign Follow-up To"
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

                        <x-form.textarea
                            label="Client Response"
                            name="client_response"
                            rows="3"
                        />

                        <x-form.textarea
                            label="Internal Notes"
                            name="notes"
                            rows="3"
                        />

                        <button class="min-h-11 w-full rounded-2xl bg-slate-950 px-4 text-sm font-bold text-white">
                            Save Follow-up
                        </button>
                    </form>
                </article>
            @endcan
        </section>
    </div>
</div>