@extends('layouts.admin')

@section('title', 'Clients')
@section('page-title', 'Client Management')

@section('content')
    <div class="space-y-6">
        <section class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-black text-slate-950">
                    Clients
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Manage client profiles and project relationships.
                </p>
            </div>

            @can('clients.create')
                <a
                    href="{{ route('clients.create') }}"
                    class="inline-flex min-h-12 items-center justify-center rounded-2xl bg-slate-950 px-5 text-sm font-bold text-white shadow-lg shadow-slate-300 transition hover:bg-indigo-600"
                >
                    + Add Client
                </a>
            @endcan
        </section>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ([
                ['Total Clients', $summary['total_clients']],
                ['Active Clients', $summary['active_clients']],
                ['Prospects', $summary['prospects']],
                [
                    'Total Project Value',
                    '₹' . number_format($summary['project_value'], 2)
                ],
            ] as [$label, $value])
                <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">
                        {{ $label }}
                    </p>

                    <p class="mt-2 text-2xl font-black text-slate-950">
                        {{ $value }}
                    </p>
                </article>
            @endforeach
        </section>

        <section class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
            <form
                method="GET"
                class="grid gap-3 md:grid-cols-[1fr_220px_auto]"
            >
                <input
                    type="search"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search name, company, phone or email..."
                    class="min-h-12 rounded-2xl border border-slate-200 px-4 text-sm outline-none focus:border-indigo-400 focus:ring-4 focus:ring-indigo-100"
                >

                <select
                    name="status"
                    class="min-h-12 rounded-2xl border border-slate-200 px-4 text-sm outline-none focus:border-indigo-400 focus:ring-4 focus:ring-indigo-100"
                >
                    <option value="">All statuses</option>

                    @foreach ($statuses as $status)
                        <option
                            value="{{ $status->value }}"
                            @selected(
                                request('status') === $status->value
                            )
                        >
                            {{ $status->label() }}
                        </option>
                    @endforeach
                </select>

                <div class="flex gap-2">
                    <button
                        class="min-h-12 flex-1 rounded-2xl bg-indigo-600 px-5 text-sm font-bold text-white"
                    >
                        Filter
                    </button>

                    <a
                        href="{{ route('clients.index') }}"
                        class="inline-flex min-h-12 items-center justify-center rounded-2xl border border-slate-200 px-4 text-sm font-bold text-slate-600"
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
                            <th class="px-5 py-4">Client</th>
                            <th class="px-5 py-4">Contact</th>
                            <th class="px-5 py-4">Projects</th>
                            <th class="px-5 py-4">Project Value</th>
                            <th class="px-5 py-4">Status</th>
                            <th class="px-5 py-4 text-right">Action</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @forelse ($clients as $client)
                            <tr class="transition hover:bg-slate-50/70">
                                <td class="px-5 py-4">
                                    <a
                                        href="{{ route('clients.show', $client) }}"
                                        class="font-bold text-slate-950 hover:text-indigo-600"
                                    >
                                        {{ $client->display_name }}
                                    </a>

                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ $client->client_code }}
                                        · {{ $client->name }}
                                    </p>
                                </td>

                                <td class="px-5 py-4 text-sm text-slate-600">
                                    <p>{{ $client->phone ?: 'No phone' }}</p>
                                    <p class="mt-1 text-xs">
                                        {{ $client->email ?: 'No email' }}
                                    </p>
                                </td>

                                <td class="px-5 py-4 text-sm font-bold text-slate-700">
                                    {{ $client->projects_count }}
                                </td>

                                <td class="px-5 py-4 text-sm font-bold text-slate-950">
                                    ₹{{ number_format(
                                        $client->projects_sum_project_price ?? 0,
                                        2
                                    ) }}
                                </td>

                                <td class="px-5 py-4">
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold ring-1 ring-inset {{ $client->status->badgeClasses() }}">
                                        {{ $client->status->label() }}
                                    </span>
                                </td>

                                <td class="px-5 py-4 text-right">
                                    <a
                                        href="{{ route('clients.show', $client) }}"
                                        class="text-sm font-bold text-indigo-600 hover:text-indigo-700"
                                    >
                                        View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td
                                    colspan="6"
                                    class="px-5 py-14 text-center"
                                >
                                    <p class="font-bold text-slate-800">
                                        No clients found
                                    </p>

                                    <p class="mt-1 text-sm text-slate-500">
                                        Add your first client to begin.
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($clients->hasPages())
                <div class="border-t border-slate-100 px-5 py-4">
                    {{ $clients->links() }}
                </div>
            @endif
        </section>
    </div>
@endsection