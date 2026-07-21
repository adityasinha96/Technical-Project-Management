@extends('layouts.admin')

@section('title', $client->display_name)
@section('page-title', 'Client Details')

@section('content')
    <div class="space-y-6">
        {{-- Client header --}}
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
                                'client_id' => $client->id,
                            ]) }}"
                            class="inline-flex min-h-11 items-center rounded-2xl bg-white px-4 text-sm font-bold text-slate-950 transition hover:bg-slate-100"
                        >
                            + Add Project
                        </a>
                    @endcan

                    @can('clients.update')
                        <a
                            href="{{ route('clients.edit', $client) }}"
                            class="inline-flex min-h-11 items-center rounded-2xl border border-white/20 px-4 text-sm font-bold text-white transition hover:bg-white/10"
                        >
                            Edit Client
                        </a>
                    @endcan
                </div>
            </div>
        </section>

        {{-- Client financial summary --}}
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
            @foreach ([
                [
                    'label' => 'Total Projects',
                    'value' => $financials['project_count'],
                    'classes' => 'border-slate-200 bg-white',
                    'label_classes' => 'text-slate-500',
                    'value_classes' => 'text-slate-950',
                ],
                [
                    'label' => 'Contracted Value',
                    'value' => '₹' . number_format(
                        (float) $financials['contracted_value'],
                        2
                    ),
                    'classes' => 'border-indigo-200 bg-indigo-50',
                    'label_classes' => 'text-indigo-700',
                    'value_classes' => 'text-indigo-950',
                ],
                [
                    'label' => 'Received',
                    'value' => '₹' . number_format(
                        (float) $financials['received_amount'],
                        2
                    ),
                    'classes' => 'border-emerald-200 bg-emerald-50',
                    'label_classes' => 'text-emerald-700',
                    'value_classes' => 'text-emerald-950',
                ],
                [
                    'label' => 'Pending',
                    'value' => '₹' . number_format(
                        (float) $financials['pending_amount'],
                        2
                    ),
                    'classes' => 'border-amber-200 bg-amber-50',
                    'label_classes' => 'text-amber-700',
                    'value_classes' => 'text-amber-950',
                ],
                [
                    'label' => 'Completed',
                    'value' => $financials['completed_projects'],
                    'classes' => 'border-cyan-200 bg-cyan-50',
                    'label_classes' => 'text-cyan-700',
                    'value_classes' => 'text-cyan-950',
                ],
            ] as $card)
                <article class="rounded-3xl border p-5 shadow-sm {{ $card['classes'] }}">
                    <p class="text-sm font-medium {{ $card['label_classes'] }}">
                        {{ $card['label'] }}
                    </p>

                    <p class="mt-2 text-2xl font-black {{ $card['value_classes'] }}">
                        {{ $card['value'] }}
                    </p>
                </article>
            @endforeach
        </section>

        <section class="grid gap-6 xl:grid-cols-[0.75fr_1.25fr]">
            {{-- Client contact information --}}
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

            {{-- Client projects --}}
            <article class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 p-6">
                    <h2 class="text-lg font-bold text-slate-950">
                        Client Projects
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Project values, collections, pending balances and delivery details.
                    </p>
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse ($client->projects as $project)
                        <a
                            href="{{ route('projects.show', $project) }}"
                            class="block p-5 transition hover:bg-slate-50"
                        >
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                <div class="min-w-0">
                                    <p class="truncate font-bold text-slate-950">
                                        {{ $project->name }}
                                    </p>

                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ $project->project_code }}
                                        ·
                                        {{ $project->category?->name ?? 'Uncategorised' }}
                                    </p>
                                </div>

                                <span class="inline-flex w-fit shrink-0 rounded-full px-3 py-1 text-xs font-bold ring-1 ring-inset {{ $project->status->badgeClasses() }}">
                                    {{ $project->status->label() }}
                                </span>
                            </div>

                            <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
                                {{-- Project price --}}
                                <div class="rounded-2xl bg-slate-50 p-3">
                                    <p class="text-xs font-medium text-slate-400">
                                        Project Price
                                    </p>

                                    <p class="mt-1 font-bold text-slate-800">
                                        ₹{{ number_format(
                                            (float) $project->project_price,
                                            2
                                        ) }}
                                    </p>
                                </div>

                                {{-- Received amount --}}
                                <div class="rounded-2xl bg-emerald-50 p-3">
                                    <p class="text-xs font-medium text-emerald-600">
                                        Received
                                    </p>

                                    <p class="mt-1 font-bold text-emerald-900">
                                        ₹{{ number_format(
                                            (float) $project->net_received_amount,
                                            2
                                        ) }}
                                    </p>
                                </div>

                                {{-- Pending amount --}}
                                <div class="rounded-2xl bg-amber-50 p-3">
                                    <p class="text-xs font-medium text-amber-600">
                                        Pending
                                    </p>

                                    <p class="mt-1 font-bold text-amber-900">
                                        ₹{{ number_format(
                                            (float) $project->pending_amount,
                                            2
                                        ) }}
                                    </p>
                                </div>

                                {{-- Deadline --}}
                                <div class="rounded-2xl bg-slate-50 p-3">
                                    <p class="text-xs font-medium text-slate-400">
                                        Deadline
                                    </p>

                                    <p class="mt-1 font-bold {{ $project->is_delayed ? 'text-red-700' : 'text-slate-800' }}">
                                        {{ $project->deadline?->format('d M Y') ?? 'Not provided' }}
                                    </p>

                                    @if ($project->is_delayed)
                                        <p class="mt-1 text-xs font-semibold text-red-600">
                                            {{ $project->deadline_label }}
                                        </p>
                                    @endif
                                </div>

                                {{-- Project manager --}}
                                <div class="rounded-2xl bg-slate-50 p-3">
                                    <p class="text-xs font-medium text-slate-400">
                                        Project Manager
                                    </p>

                                    <p class="mt-1 truncate font-bold text-slate-800">
                                        {{ $project->manager?->name ?? 'Not assigned' }}
                                    </p>
                                </div>
                            </div>

                            {{-- Collection progress --}}
                            <div class="mt-4">
                                <div class="flex items-center justify-between gap-4">
                                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">
                                        Collection Progress
                                    </p>

                                    <p class="text-xs font-black text-slate-700">
                                        {{ number_format(
                                            (float) $project->collection_percentage,
                                            2
                                        ) }}%
                                    </p>
                                </div>

                                <div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-100">
                                    <div
                                        class="h-full rounded-full bg-gradient-to-r from-emerald-500 to-cyan-500 transition-all duration-500"
                                        style="width: {{ $project->collection_bar_percentage }}%"
                                    ></div>
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