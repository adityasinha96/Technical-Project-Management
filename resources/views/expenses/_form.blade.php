@php
    $dateValue = function ($date) {
        return $date
            ? \Illuminate\Support\Carbon::parse($date)
                ->format('Y-m-d')
            : null;
    };
@endphp

<div
    x-data="{
        scope: '{{ old(
            'scope',
            $expense->scope?->value ?? 'business'
        ) }}',

        status: '{{ old(
            'status',
            $expense->status?->value ?? 'paid'
        ) }}'
    }"
    class="space-y-6"
>
    <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <div class="mb-6">
            <h2 class="text-lg font-bold text-slate-950">
                Expense Information
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Select whether this cost belongs to one project or to general business operations.
            </p>
        </div>

        <div class="grid gap-5 md:grid-cols-2">
            <x-form.select
                label="Expense Type"
                name="scope"
                x-model="scope"
                required
            >
                @foreach ($scopes as $scope)
                    <option value="{{ $scope->value }}">
                        {{ $scope->label() }}
                    </option>
                @endforeach
            </x-form.select>

            <div x-show="scope === 'project'" x-cloak>
                <x-form.select
                    label="Project"
                    name="project_id"
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
                                    $expense->project_id
                                ) === (string) $project->id
                            )
                        >
                            {{ $project->name }}
                            — {{ $project->client->display_name }}
                        </option>
                    @endforeach
                </x-form.select>
            </div>

            <x-form.select
                label="Expense Category"
                name="expense_category_id"
                required
            >
                <option value="">
                    Select category
                </option>

                @foreach ($categories as $category)
                    <option
                        value="{{ $category->id }}"
                        data-scope="{{ $category->scope->value }}"
                        @selected(
                            (string) old(
                                'expense_category_id',
                                $expense->expense_category_id
                            ) === (string) $category->id
                        )
                    >
                        {{ $category->name }}
                        — {{ $category->scope->label() }}
                    </option>
                @endforeach
            </x-form.select>

            <x-form.select
                label="Expense Status"
                name="status"
                x-model="status"
                required
            >
                <option value="paid">
                    Paid
                </option>

                <option value="pending">
                    Pending Payment
                </option>
            </x-form.select>

            <x-form.input
                label="Total Expense Amount"
                name="amount"
                type="number"
                min="0.01"
                step="0.01"
                :value="$expense->amount"
                required
            />

            <x-form.input
                label="Tax Included in Amount"
                name="tax_amount"
                type="number"
                min="0"
                step="0.01"
                :value="$expense->tax_amount ?: 0"
            />

            <x-form.input
                label="Expense Date"
                name="expense_date"
                type="date"
                :value="$dateValue(
                    $expense->expense_date ?: today()
                )"
                required
            />

            <x-form.input
                label="Due Date"
                name="due_date"
                type="date"
                :value="$dateValue(
                    $expense->due_date
                )"
            />

            <div x-show="status === 'paid'" x-cloak>
                <x-form.input
                    label="Paid Date"
                    name="paid_at"
                    type="date"
                    :value="$dateValue(
                        $expense->paid_at ?: today()
                    )"
                />
            </div>

            <div x-show="status === 'paid'" x-cloak>
                <x-form.select
                    label="Payment Mode"
                    name="payment_mode"
                >
                    @foreach ($paymentModes as $mode)
                        <option
                            value="{{ $mode->value }}"
                            @selected(
                                old(
                                    'payment_mode',
                                    $expense->payment_mode?->value
                                        ?? \App\Enums\PaymentMode::BankTransfer->value
                                ) === $mode->value
                            )
                        >
                            {{ $mode->label() }}
                        </option>
                    @endforeach
                </x-form.select>
            </div>
        </div>
    </section>

    <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <h2 class="text-lg font-bold text-slate-950">
            Vendor and Transaction Details
        </h2>

        <div class="mt-6 grid gap-5 md:grid-cols-2">
            <x-form.input
                label="Vendor / Paid To"
                name="vendor_name"
                :value="$expense->vendor_name"
            />

            <x-form.input
                label="Bill or Invoice Number"
                name="bill_number"
                :value="$expense->bill_number"
            />

            <div class="md:col-span-2">
                <x-form.input
                    label="Transaction Reference"
                    name="transaction_reference"
                    :value="$expense->transaction_reference"
                />
            </div>
        </div>
    </section>

    <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <div class="space-y-5">
            <x-form.textarea
                label="Expense Description"
                name="description"
                :value="$expense->description"
                rows="4"
                required
            />

            <x-form.textarea
                label="Internal Notes"
                name="internal_notes"
                :value="$expense->internal_notes"
                rows="3"
            />

            <label class="block">
                <span class="mb-2 block text-sm font-semibold text-slate-700">
                    Receipt or Bill
                </span>

                <input
                    type="file"
                    name="receipt"
                    accept=".pdf,.png,.jpg,.jpeg,.webp"
                    class="block w-full rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-4 text-sm"
                >

                @if ($expense->receipt_url)
                    <a
                        href="{{ $expense->receipt_url }}"
                        target="_blank"
                        rel="noopener"
                        class="mt-2 inline-flex text-sm font-bold text-indigo-600"
                    >
                        Open existing receipt
                    </a>
                @endif

                @error('receipt')
                    <span class="mt-1 block text-xs font-medium text-red-600">
                        {{ $message }}
                    </span>
                @enderror
            </label>
        </div>
    </section>

    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
        <a
            href="{{ route('expenses.index') }}"
            class="inline-flex min-h-12 items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 text-sm font-bold text-slate-700"
        >
            Cancel
        </a>

        <button
            type="submit"
            class="inline-flex min-h-12 items-center justify-center rounded-2xl bg-slate-950 px-6 text-sm font-bold text-white shadow-lg shadow-slate-300"
        >
            {{ $submitLabel }}
        </button>
    </div>
</div>