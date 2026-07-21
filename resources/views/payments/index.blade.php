@extends('layouts.admin')

@section('title', 'Payments')
@section('page-title', 'Payment Management')

@section('content')
    <div class="space-y-6">
        <section class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-black text-slate-950">
                    Payments
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    View installments, refunds and pending transactions.
                </p>
            </div>

            <a
                href="{{ route('payments.outstanding') }}"
                class="inline-flex min-h-12 items-center justify-center rounded-2xl bg-amber-500 px-5 text-sm font-bold text-slate-950"
            >
                View Market Outstanding
            </a>
        </section>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ([
                [
                    'Total Net Received',
                    '₹' . number_format(
                        $summary['total_received'],
                        2
                    ),
                    'border-emerald-200 bg-emerald-50'
                ],
                [
                    'Current Month Collection',
                    '₹' . number_format(
                        $summary['current_month_collection'],
                        2
                    ),
                    'border-indigo-200 bg-indigo-50'
                ],
                [
                    'Market Outstanding',
                    '₹' . number_format(
                        $summary['market_outstanding'],
                        2
                    ),
                    'border-amber-200 bg-amber-50'
                ],
                [
                    'Pending Clearance',
                    '₹' . number_format(
                        $summary['pending_payments'],
                        2
                    ),
                    'border-cyan-200 bg-cyan-50'
                ],
            ] as [$label, $value, $classes])
                <article class="rounded-3xl border p-5 shadow-sm {{ $classes }}">
                    <p class="text-sm font-medium text-slate-600">
                        {{ $label }}
                    </p>

                    <p class="mt-2 text-2xl font-black text-slate-950">
                        {{ $value }}
                    </p>
                </article>
            @endforeach
        </section>

        <section
            x-data="{ filtersOpen: false }"
            class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5"
        >
            <button
                type="button"
                @click="filtersOpen = !filtersOpen"
                class="mb-4 rounded-xl border border-slate-200 px-3 py-2 text-sm font-bold lg:hidden"
            >
                Show / Hide Filters
            </button>

            <form
                method="GET"
                :class="filtersOpen ? 'grid' : 'hidden lg:grid'"
                class="gap-3 lg:grid-cols-4"
            >
                <input
                    type="search"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Payment number, project or reference..."
                    class="min-h-12 rounded-2xl border border-slate-200 px-4 text-sm"
                >

                <select
                    name="client_id"
                    class="min-h-12 rounded-2xl border border-slate-200 px-4 text-sm"
                >
                    <option value="">All clients</option>

                    @foreach ($clients as $client)
                        <option
                            value="{{ $client->id }}"
                            @selected(
                                (string) request('client_id') ===
                                (string) $client->id
                            )
                        >
                            {{ $client->display_name }}
                        </option>
                    @endforeach
                </select>

                <select
                    name="project_id"
                    class="min-h-12 rounded-2xl border border-slate-200 px-4 text-sm"
                >
                    <option value="">All projects</option>

                    @foreach ($projects as $project)
                        <option
                            value="{{ $project->id }}"
                            @selected(
                                (string) request('project_id') ===
                                (string) $project->id
                            )
                        >
                            {{ $project->name }}
                        </option>
                    @endforeach
                </select>

                <select
                    name="status"
                    class="min-h-12 rounded-2xl border border-slate-200 px-4 text-sm"
                >
                    <option value="">All statuses</option>

                    @foreach ($statuses as $status)
                        <option
                            value="{{ $status->value }}"
                            @selected(
                                request('status') ===
                                $status->value
                            )
                        >
                            {{ $status->label() }}
                        </option>
                    @endforeach
                </select>

                <select
                    name="kind"
                    class="min-h-12 rounded-2xl border border-slate-200 px-4 text-sm"
                >
                    <option value="">Receipts and refunds</option>

                    @foreach ($kinds as $kind)
                        <option
                            value="{{ $kind->value }}"
                            @selected(
                                request('kind') ===
                                $kind->value
                            )
                        >
                            {{ $kind->label() }}
                        </option>
                    @endforeach
                </select>

                <select
                    name="payment_mode"
                    class="min-h-12 rounded-2xl border border-slate-200 px-4 text-sm"
                >
                    <option value="">All payment modes</option>

                    @foreach ($modes as $mode)
                        <option
                            value="{{ $mode->value }}"
                            @selected(
                                request('payment_mode') ===
                                $mode->value
                            )
                        >
                            {{ $mode->label() }}
                        </option>
                    @endforeach
                </select>

                <input
                    type="date"
                    name="date_from"
                    value="{{ request('date_from') }}"
                    class="min-h-12 rounded-2xl border border-slate-200 px-4 text-sm"
                >

                <input
                    type="date"
                    name="date_to"
                    value="{{ request('date_to') }}"
                    class="min-h-12 rounded-2xl border border-slate-200 px-4 text-sm"
                >

                <div class="flex gap-2 lg:col-span-4">
                    <button class="min-h-12 flex-1 rounded-2xl bg-indigo-600 px-5 text-sm font-bold text-white">
                        Apply Filters
                    </button>

                    <a
                        href="{{ route('payments.index') }}"
                        class="inline-flex min-h-12 items-center justify-center rounded-2xl border border-slate-200 px-5 text-sm font-bold text-slate-600"
                    >
                        Reset
                    </a>
                </div>
            </form>
        </section>

        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-slate-50">
                        <tr class="text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                            <th class="px-5 py-4">Payment</th>
                            <th class="px-5 py-4">Client / Project</th>
                            <th class="px-5 py-4">Date</th>
                            <th class="px-5 py-4">Mode</th>
                            <th class="px-5 py-4">Status</th>
                            <th class="px-5 py-4 text-right">Amount</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @forelse ($payments as $payment)
                            <tr class="{{ $payment->is_voided ? 'bg-red-50/40' : 'hover:bg-slate-50' }}">
                                <td class="px-5 py-4">
                                    <a
                                        href="{{ route(
                                            'payments.show',
                                            $payment
                                        ) }}"
                                        class="font-bold text-indigo-600"
                                    >
                                        {{ $payment->payment_number }}
                                    </a>

                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ $payment->payment_type->label() }}
                                    </p>
                                </td>

                                <td class="px-5 py-4">
                                    <p class="font-bold text-slate-900">
                                        {{ $payment->client->display_name }}
                                    </p>

                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ $payment->project->name }}
                                    </p>
                                </td>

                                <td class="px-5 py-4 text-sm text-slate-600">
                                    {{ $payment->payment_date->format('d M Y') }}
                                </td>

                                <td class="px-5 py-4 text-sm text-slate-600">
                                    {{ $payment->payment_mode->label() }}
                                </td>

                                <td class="px-5 py-4">
                                    @if ($payment->is_voided)
                                        <span class="rounded-full bg-red-600 px-3 py-1 text-xs font-bold text-white">
                                            Voided
                                        </span>
                                    @else
                                        <span class="rounded-full px-3 py-1 text-xs font-bold ring-1 ring-inset {{ $payment->status->badgeClasses() }}">
                                            {{ $payment->status->label() }}
                                        </span>
                                    @endif
                                </td>

                                <td class="px-5 py-4 text-right text-base font-black {{ $payment->kind === \App\Enums\PaymentKind::Refund ? 'text-red-700' : 'text-emerald-700' }}">
                                    {{ $payment->kind === \App\Enums\PaymentKind::Refund ? '−' : '+' }}
                                    ₹{{ number_format(
                                        $payment->amount,
                                        2
                                    ) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td
                                    colspan="6"
                                    class="px-5 py-14 text-center"
                                >
                                    <p class="font-bold text-slate-900">
                                        No payments found
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($payments->hasPages())
                <div class="border-t border-slate-100 px-5 py-4">
                    {{ $payments->links() }}
                </div>
            @endif
        </section>
    </div>
@endsection