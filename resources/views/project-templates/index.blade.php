@extends('layouts.admin')

@section('title', 'Project Templates')
@section('page-title', 'Project Templates')

@section('content')
    <div class="space-y-6">
        <section class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-black text-slate-950">
                    Project Templates
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Create reusable task workflows for recurring project types.
                </p>
            </div>

            @can('templates.manage')
                <a
                    href="{{ route('project-templates.create') }}"
                    class="inline-flex min-h-12 items-center justify-center rounded-2xl bg-slate-950 px-5 text-sm font-bold text-white shadow-lg shadow-slate-300 transition hover:bg-indigo-600"
                >
                    + Add Template
                </a>
            @endcan
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @forelse ($templates as $template)
                <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-indigo-600">
                                {{ $template->category?->name ?? 'General Template' }}
                            </p>

                            <h3 class="mt-2 text-lg font-black text-slate-950">
                                {{ $template->name }}
                            </h3>
                        </div>

                        <span class="rounded-full px-3 py-1 text-xs font-bold {{ $template->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                            {{ $template->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>

                    <p class="mt-3 text-sm leading-6 text-slate-500">
                        {{ $template->description ?: 'No description provided.' }}
                    </p>

                    <div class="mt-5 grid grid-cols-3 gap-3">
                        <div class="rounded-2xl bg-slate-50 p-3 text-center">
                            <p class="text-xl font-black text-slate-950">
                                {{ $template->tasks_count }}
                            </p>
                            <p class="text-xs text-slate-500">
                                Tasks
                            </p>
                        </div>

                        <div class="rounded-2xl bg-slate-50 p-3 text-center">
                            <p class="text-xl font-black text-slate-950">
                                {{ $template->default_duration_days }}
                            </p>
                            <p class="text-xs text-slate-500">
                                Days
                            </p>
                        </div>

                        <div class="rounded-2xl bg-slate-50 p-3 text-center">
                            <p class="text-xl font-black text-slate-950">
                                {{ $template->projects_count }}
                            </p>
                            <p class="text-xs text-slate-500">
                                Projects
                            </p>
                        </div>
                    </div>

                    @can('templates.manage')
                        <a
                            href="{{ route(
                                'project-templates.edit',
                                $template
                            ) }}"
                            class="mt-5 inline-flex min-h-11 w-full items-center justify-center rounded-2xl bg-slate-950 px-4 text-sm font-bold text-white"
                        >
                            Edit Template
                        </a>
                    @endcan
                </article>
            @empty
                <div class="md:col-span-2 xl:col-span-3 rounded-3xl border border-dashed border-slate-300 bg-white p-14 text-center">
                    <p class="font-bold text-slate-900">
                        No templates available
                    </p>
                </div>
            @endforelse
        </section>

        {{ $templates->links() }}
    </div>
@endsection