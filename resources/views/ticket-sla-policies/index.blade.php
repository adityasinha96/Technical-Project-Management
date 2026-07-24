@extends('layouts.admin')

@section('title', 'Ticket SLA Policies')
@section('page-title', 'Ticket SLA Policies')

@section('content')
    <div class="space-y-6">
        <section class="rounded-3xl border border-amber-200 bg-amber-50 p-5">
            <h1 class="text-xl font-black text-amber-950">
                Calendar-Time SLA Configuration
            </h1>

            <p class="mt-2 text-sm leading-6 text-amber-800">
                All values are stored in minutes. SLA clocks pause while tickets are Pending Client or On Hold.
            </p>
        </section>

        <section class="grid gap-5 xl:grid-cols-2">
            @foreach ($policies as $policy)
                <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-black text-slate-950">
                            {{ $policy->priority->label() }}
                        </h2>

                        <span class="rounded-full px-3 py-1 text-xs font-bold {{ $policy->priority->badgeClasses() }}">
                            {{ $policy->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>

                    <form
                        method="POST"
                        action="{{ route(
                            'ticket-sla-policies.update',
                            $policy
                        ) }}"
                        class="mt-6 grid gap-4 sm:grid-cols-2"
                    >
                        @csrf
                        @method('PUT')

                        <x-form.input
                            label="First Response Minutes"
                            name="first_response_minutes"
                            type="number"
                            min="5"
                            :value="$policy->first_response_minutes"
                            required
                        />

                        <x-form.input
                            label="Resolution Minutes"
                            name="resolution_minutes"
                            type="number"
                            min="5"
                            :value="$policy->resolution_minutes"
                            required
                        />

                        <x-form.input
                            label="Warning Before Due"
                            name="warning_before_minutes"
                            type="number"
                            min="0"
                            :value="$policy->warning_before_minutes"
                            required
                        />

                        <x-form.input
                            label="Level 2 After Due"
                            name="level_two_after_minutes"
                            type="number"
                            min="0"
                            :value="$policy->level_two_after_minutes"
                            required
                        />

                        <x-form.input
                            label="Level 3 After Due"
                            name="level_three_after_minutes"
                            type="number"
                            min="0"
                            :value="$policy->level_three_after_minutes"
                            required
                        />

                        <label class="flex items-center gap-3 rounded-2xl border border-slate-200 p-4">
                            <input
                                type="checkbox"
                                name="is_active"
                                value="1"
                                @checked($policy->is_active)
                            >

                            <span class="text-sm font-bold">
                                Active policy
                            </span>
                        </label>

                        <button class="min-h-11 rounded-2xl bg-slate-950 px-5 text-sm font-bold text-white sm:col-span-2">
                            Save SLA Policy
                        </button>
                    </form>
                </article>
            @endforeach
        </section>
    </div>
@endsection