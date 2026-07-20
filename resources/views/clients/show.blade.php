@extends('layouts.admin')

@section('title', $client->display_name)
@section('page-title', 'Client Details')

@section('content')
    <div class="space-y-6">
        <section class="rounded-3xl bg-slate-950 p-6 text-white shadow-xl sm:p-8">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-cyan-300">
                        {{ $client->client_code }}
                    </p>

                    <h1 class="mt-3 text-2xl font-black sm:text-3xl">
                        {{ $client->display_name }}
                    </h1>

                    @if ($client->company_name)
                        <p class="mt-2 text-sm text-slate-300">
                            Contact person: {{ $client->name }}
                        </p>
                    @endif

                    <div class="mt-4 flex flex-wrap gap-2">
                        <span class="rounded-full bg-white/10 px-3 py-1 text-xs font-semibold">
                            {{ ucfirst($client->client_type) }}
                        </span>

                        <span class="rounded-full px-3 py-1 text-xs font-bold {{ $client->status->badgeClasses() }}">
                            {{ $client->status->label() }}
                        </span>
                    </div>
                </div>

                <div class="flex flex-wrap gap-3">
                    @can('projects.create')
                        <a
                            href="{{ route('projects.create', [
                                'client_id' => $client->id
                            ]) }}"
                            class="inline-flex min-h-11 items-center rounded-2xl bg-white px-4 text-sm font-bold text-slate-950"
                        >
                            + Add Project
                        </a>
                    @endcan

                    @can('clients.update')
                        <a
                            href="{{ route('clients.edit', $client) }}"
                            class="inline-flex min-h-11 items-center rounded-2xl border border-white/20 px-4 text-sm font-bold text-white"
                        >
                            Edit Client
                        </a>
                    @endcan
                </div>
            </div>
        </section>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ([
                ['Total Projects', $financials['project_count']],
                ['Completed', $financials['completed_projects']],
                [
                    'Contracted Value',
                    '₹' . number_format(
                        $financials['contracted_value'],
                        2
                    )
                ],
                [
                    'Estimated Cost',
                    '₹' . number_format(
                        $financials['estimated_cost'],
                        2
                    )
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

        <section class="grid gap-6 xl:grid-cols-[0.75fr_1.25fr]">
            <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold text-slate-950">
                    Contact Information
                </h2>

                <dl class="mt-6 space-y-5 text-sm">
                    @foreach ([
                        'Phone' => $client->phone,
                        'WhatsApp' => $client->whatsapp,
                        'Email' => $client->email,
                        'GST Number' => $client->gst_number,
                        'Location' => $client->location,
                        'PIN Code' => $client->pincode,
                    ] as $label => $value)
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">
                                {{ $label }}
                            </dt>

                            <dd class="mt-1 font-semibold text-slate-800">
                                {{ $value ?: 'Not provided' }}
                            </dd>
                        </div>
                    @endforeach
                </dl>

                @if ($client->address)
                    <div class="mt-6 border-t border-slate-100 pt-5">
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-400">
                            Address
                        </p>

                        <p class="mt-2 text-sm leading-6 text-slate-700">
                            {{ $client->address }}
                        </p>
                    </div>
                @endif

                @if ($client->notes)
                    <div class="mt-6 rounded-2xl bg-amber-50 p-4">
                        <p class="text-xs font-bold uppercase tracking-wider text-amber-700">
                            Internal Notes
                        </p>

                        <p class="mt-2 whitespace-pre-line text-sm leading-6 text-amber-950">
                            {{ $client->notes }}
                        </p>
                    </div>
                @endif
            </article>

            <article class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 p-6">
                    <h2 class="text-lg font-bold text-slate-950">
                        Client Projects
                    </h2>
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse ($client->projects as $project)
                        <a
                            href="{{ route('projects.show', $project) }}"
                            class="block p-5 transition hover:bg-slate-50"
                        >
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <p class="font-bold text-slate-950">
                                        {{ $project->name }}
                                    </p>

                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ $project->project_code }}
                                        ·
                                        {{ $project->category?->name ?? 'Uncategorised' }}
                                    </p>
                                </div>

                                <span class="inline-flex w-fit rounded-full px-3 py-1 text-xs font-bold ring-1 ring-inset {{ $project->status->badgeClasses() }}">
                                    {{ $project->status->label() }}
                                </span>
                            </div>

                            <div class="mt-4 grid gap-3 text-sm sm:grid-cols-3">
                                <div>
                                    <p class="text-xs text-slate-400">
                                        Project Price
                                    </p>
                                    <p class="mt-1 font-bold text-slate-800">
                                        ₹{{ number_format(
                                            $project->project_price,
                                            2
                                        ) }}
                                    </p>
                                </div>

                                <div>
                                    <p class="text-xs text-slate-400">
                                        Deadline
                                    </p>
                                    <p class="mt-1 font-bold text-slate-800">
                                        {{ $project->deadline?->format('d M Y') }}
                                    </p>
                                </div>

                                <div>
                                    <p class="text-xs text-slate-400">
                                        Project Manager
                                    </p>
                                    <p class="mt-1 font-bold text-slate-800">
                                        {{ $project->manager?->name ?? 'Not assigned' }}
                                    </p>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="p-12 text-center">
                            <p class="font-bold text-slate-800">
                                No projects added
                            </p>
                            <p class="mt-1 text-sm text-slate-500">
                                This client does not have any projects yet.
                            </p>
                        </div>
                    @endforelse
                </div>
            </article>
        </section>
    </div>
@endsection