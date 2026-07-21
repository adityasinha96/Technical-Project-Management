@extends('layouts.admin')

@section('title', 'Market Outstanding')
@section('page-title', 'Market Outstanding')

@section('content')
    <div class="space-y-6">
        <section class="rounded-3xl bg-slate-950 p-6 text-white shadow-xl sm:p-8">
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-amber-300">
                Collection Dashboard
            </p>

            <div class="mt-3 flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <h1 class="text-3xl font-black">
                        ₹{{ number_format(
                            $summary['market_outstanding'],
                            2
                        ) }}
                    </h1>

                    <p class="mt-2 text-sm text-slate-300">
                        Total amount pending across all projects.
                    </p>
                </div>

                <a
                    href="{{ route('payments.index') }}"
                    class="inline-flex min-h-11 items-center rounded-2xl bg-white px-4 text-sm font-bold text-slate-950"
                >
                    Open Payment Ledger
                </a>
            </div>
        </section>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ([
                [
                    'Market Outstanding',
                    '₹' . number_format(
                        $summary['market_outstanding'],
                        2
                    )
                ],
                [
                    'Projects With Pending',
                    $summary['projects_with_pending']
                ],
                [
                    'Fully Paid Projects',
                    $summary['fully_paid_projects']
                ],
                [
                    'Average Pending',
                    '₹' . number_format(
                        $summary['average_pending'],
                        2
                    )
                ],
            ] as [$label, $value])
                <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm text-slate-500">
                        {{ $label }}
                    </p>

                    <p class="mt-2 text-2xl font-black text-slate-950">
                        {{ $value }}
                    </p>
                </article>
            @endforeach
        </section>

        <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <form
                method="GET"
                class="grid gap-3 md:grid-cols-[1fr_240px_220px_auto]"
            >
                <input
                    type="search"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search project or client..."
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
                    name="sort"
                    class="min-h-12 rounded-2xl border border-slate-200 px-4 text-sm"
                >
                    <option value="">
                        Highest Pending First
                    </option>

                    <option
                        value="oldest_payment"
                        @selected(
                            request('sort') ===
                            'oldest_payment'
                        )
                    >
                        Oldest Payment Activity
                    </option>
                </select>

                <button class="min-h-12 rounded-2xl bg-indigo-600 px-5 text-sm font-bold text-white">
                    Filter
                </button>
            </form>
        </section>

        <section class="grid gap-4">
            @forelse ($projects as $project)
                <article class="rounded-3xl border border-amber-200 bg-white p-5 shadow-sm sm:p-6">
                    <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap gap-2">
                                <span class="rounded-full px-3 py-1 text-xs font-bold {{ $project->status->badgeClasses() }}">
                                    {{ $project->status->label() }}
                                </span>

                                @if ($project->is_delayed)
                                    <span class="rounded-full bg-red-600 px-3 py-1 text-xs font-bold text-white">
                                        Project Delayed
                                    </span>
                                @endif
                            </div>

                            <a
                                href="{{ route('projects.show', [
                                    'project' => $project,
                                    'tab' => 'payments',
                                ]) }}"
                                class="mt-3 block text-xl font-black text-slate-950 hover:text-indigo-600"
                            >
                                {{ $project->name }}
                            </a>

                            <p class="mt-1 text-sm text-slate-500">
                                {{ $project->client->display_name }}
                                · {{ $project->project_code }}
                            </p>

                            <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                                <div>
                                    <p class="text-xs text-slate-400">
                                        Project Price
                                    </p>

                                    <p class="mt-1 font-bold text-slate-900">
                                        ₹{{ number_format(
                                            $project->project_price,
                                            2
                                        ) }}
                                    </p>
                                </div>

                                <div>
                                    <p class="text-xs text-slate-400">
                                        Received
                                    </p>

                                    <p class="mt-1 font-bold text-emerald-700">
                                        ₹{{ number_format(
                                            $project->net_received_amount,
                                            2
                                        ) }}
                                    </p>
                                </div>

                                <div>
                                    <p class="text-xs text-slate-400">
                                        Pending
                                    </p>

                                    <p class="mt-1 font-black text-amber-700">
                                        ₹{{ number_format(
                                            $project->pending_amount,
                                            2
                                        ) }}
                                    </p>
                                </div>

                                <div>
                                    <p class="text-xs text-slate-400">
                                        Last Payment
                                    </p>

                                    <p class="mt-1 font-bold text-slate-900">
                                        {{ $project->last_payment_date?->format('d M Y') ?? 'No payment' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="w-full rounded-2xl bg-amber-50 p-4 xl:w-64">
                            <div class="flex items-center justify-between">
                                <p class="text-xs font-bold uppercase tracking-wider text-amber-700">
                                    Collection
                                </p>

                                <p class="font-black text-amber-950">
                                    {{ number_format(
                                        $project->collection_percentage,
                                        2
                                    ) }}%
                                </p>
                            </div>

                            <div class="mt-3 h-2 overflow-hidden rounded-full bg-amber-200">
                                <div
                                    class="h-full rounded-full bg-amber-500"
                                    style="width: {{ $project->collection_bar_percentage }}%"
                                ></div>
                            </div>

                            <a
                                href="{{ route('projects.show', [
                                    'project' => $project,
                                    'tab' => 'payments',
                                ]) }}"
                                class="mt-4 inline-flex min-h-10 w-full items-center justify-center rounded-xl bg-slate-950 px-4 text-sm font-bold text-white"
                            >
                                Collect Payment
                            </a>
                        </div>
                    </div>
                </article>
            @empty
                <div class="rounded-3xl border border-dashed border-emerald-300 bg-emerald-50 p-14 text-center">
                    <p class="text-lg font-black text-emerald-900">
                        No market outstanding
                    </p>

                    <p class="mt-1 text-sm text-emerald-700">
                        Every project is fully paid.
                    </p>
                </div>
            @endforelse
        </section>

        {{ $projects->links() }}
    </div>
@endsection