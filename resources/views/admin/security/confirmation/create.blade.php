@extends('layouts.admin')

@section('title', 'Confirm Security Action')
@section('page-title', 'Confirm Security Action')

@section('content')
    <div class="mx-auto max-w-2xl">
        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-xl">
            <div class="bg-slate-950 px-6 py-8 text-white sm:px-8">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-red-500/15 text-red-300">
                    <svg
                        class="h-7 w-7"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                    >
                        <path d="M12 3l8 4v5c0 5-3.5 8.5-8 10-4.5-1.5-8-5-8-10V7l8-4z"/>
                        <path d="M9 11V9a3 3 0 016 0v2"/>
                        <rect x="8" y="11" width="8" height="6" rx="1.5"/>
                    </svg>
                </div>

                <p class="mt-5 text-xs font-bold uppercase tracking-[0.2em] text-red-300">
                    Sensitive Administrative Action
                </p>

                <h1 class="mt-3 text-3xl font-black">
                    Confirm Your Password
                </h1>

                <p class="mt-3 max-w-xl text-sm leading-6 text-slate-300">
                    For your security, please confirm your current account password before continuing with this sensitive action.
                </p>
            </div>

            <form
                method="POST"
                action="{{ route('security.confirmation.store') }}"
                class="space-y-6 p-6 sm:p-8"
            >
                @csrf

                <div>
                    <label
                        for="password"
                        class="mb-2 block text-sm font-bold text-slate-700"
                    >
                        Current Password
                    </label>

                    <input
                        id="password"
                        name="password"
                        type="password"
                        autocomplete="current-password"
                        required
                        autofocus
                        class="min-h-12 w-full rounded-2xl border border-slate-300 bg-white px-4 text-sm text-slate-950 outline-none transition placeholder:text-slate-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                        placeholder="Enter your current password"
                    >

                    @error('password')
                        <p class="mt-2 text-sm font-semibold text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4">
                    <div class="flex gap-3">
                        <svg
                            class="mt-0.5 h-5 w-5 shrink-0 text-amber-600"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <path d="M12 9v4"/>
                            <path d="M12 17h.01"/>
                            <path d="M10.3 3.6L2.6 17a2 2 0 001.7 3h15.4a2 2 0 001.7-3L13.7 3.6a2 2 0 00-3.4 0z"/>
                        </svg>

                        <div>
                            <p class="text-sm font-black text-amber-900">
                                Why confirmation is required
                            </p>

                            <p class="mt-1 text-sm leading-6 text-amber-800">
                                Backup creation, backup downloads, session revocation and other sensitive security operations require a recent password confirmation.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <a
                        href="{{ url()->previous() }}"
                        class="inline-flex min-h-11 items-center justify-center rounded-2xl border border-slate-300 bg-white px-5 text-sm font-bold text-slate-700 transition hover:bg-slate-50"
                    >
                        Cancel
                    </a>

                    <button
                        type="submit"
                        class="inline-flex min-h-11 items-center justify-center rounded-2xl bg-slate-950 px-6 text-sm font-bold text-white transition hover:bg-slate-800 focus:outline-none focus:ring-4 focus:ring-slate-200"
                    >
                        Confirm and Continue
                    </button>
                </div>
            </form>
        </section>
    </div>
@endsection

