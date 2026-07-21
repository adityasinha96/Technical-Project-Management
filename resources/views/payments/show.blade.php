@extends('layouts.admin')

@section('title', $payment->payment_number)
@section('page-title', 'Payment Details')

@section('content')
    <div class="mx-auto max-w-4xl">
        <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between print:hidden">
            <a
                href="{{ route('projects.show', [
                    'project' => $payment->project,
                    'tab' => 'payments',
                ]) }}"
                class="text-sm font-bold text-indigo-600"
            >
                ← Back to project payments
            </a>

            <button
                type="button"
                onclick="window.print()"
                class="min-h-11 rounded-2xl bg-slate-950 px-5 text-sm font-bold text-white"
            >
                Print Receipt
            </button>
        </div>

        <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm print:border-0 print:shadow-none sm:p-10">
            <header class="flex flex-col gap-6 border-b border-slate-200 pb-7 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-indigo-600">
                        UIPRO Corporation Pvt. Ltd.
                    </p>

                    <h1 class="mt-3 text-3xl font-black text-slate-950">
                        {{ $payment->kind === \App\Enums\PaymentKind::Refund
                            ? 'Refund Voucher'
                            : 'Payment Receipt' }}
                    </h1>
                </div>

                <div class="sm:text-right">
                    <p class="font-black text-slate-950">
                        {{ $payment->payment_number }}
                    </p>

                    <p class="mt-1 text-sm text-slate-500">
                        {{ $payment->payment_date->format('d F Y') }}
                    </p>
                </div>
            </header>

            @if ($payment->is_voided)
                <div class="mt-6 rounded-2xl bg-red-100 p-4 text-center font-black text-red-700">
                    THIS FINANCIAL ENTRY HAS BEEN VOIDED
                </div>
            @endif

            <section class="mt-8 grid gap-6 sm:grid-cols-2">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">
                        Received From / Paid To
                    </p>

                    <p class="mt-2 font-black text-slate-950">
                        {{ $payment->received_from
                            ?: $payment->client->display_name }}
                    </p>

                    <p class="mt-1 text-sm text-slate-500">
                        {{ $payment->client->display_name }}
                    </p>
                </div>

                <div class="sm:text-right">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">
                        Amount
                    </p>

                    <p class="mt-2 text-3xl font-black {{ $payment->kind === \App\Enums\PaymentKind::Refund ? 'text-red-700' : 'text-emerald-700' }}">
                        ₹{{ number_format(
                            $payment->amount,
                            2
                        ) }}
                    </p>
                </div>
            </section>

            <section class="mt-8 rounded-3xl bg-slate-50 p-6">
                <dl class="grid gap-5 sm:grid-cols-2">
                    @foreach ([
                        'Project' => $payment->project->name,
                        'Payment Type' => $payment->payment_type->label(),
                        'Payment Mode' => $payment->payment_mode->label(),
                        'Status' => $payment->display_status,
                        'Transaction Reference' => $payment->transaction_reference,
                        'Invoice Number' => $payment->invoice_number,
                        'Bank Name' => $payment->bank_name,
                        'Recorded By' => $payment->createdBy?->name,
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
            </section>

            @if ($payment->remarks)
                <section class="mt-7">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">
                        Remarks
                    </p>

                    <p class="mt-2 whitespace-pre-line text-sm leading-7 text-slate-700">
                        {{ $payment->remarks }}
                    </p>
                </section>
            @endif

            @if ($payment->proofFile)
                <a
                    href="{{ $payment->proofFile->url }}"
                    target="_blank"
                    rel="noopener"
                    class="mt-7 inline-flex rounded-2xl bg-indigo-50 px-4 py-3 text-sm font-bold text-indigo-700 print:hidden"
                >
                    Open Payment Proof
                </a>
            @endif

            @if ($payment->is_voided)
                <section class="mt-7 rounded-2xl bg-red-50 p-5">
                    <p class="text-xs font-bold uppercase tracking-wider text-red-700">
                        Void Information
                    </p>

                    <p class="mt-2 text-sm leading-6 text-red-900">
                        {{ $payment->void_reason }}
                    </p>

                    <p class="mt-2 text-xs text-red-700">
                        Voided by
                        {{ $payment->voidedBy?->name ?? 'Unknown' }}
                        on
                        {{ $payment->voided_at->format('d M Y, h:i A') }}
                    </p>
                </section>
            @endif

            <footer class="mt-10 border-t border-slate-200 pt-6 text-center text-xs text-slate-500">
                This receipt was generated through the UIPRO Project Management System.
            </footer>
        </article>
    </div>
@endsection