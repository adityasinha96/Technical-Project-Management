@extends('layouts.admin')

@section('title', 'Add Project')
@section('page-title', 'Add New Project')

@section('content')
    <div class="mx-auto max-w-6xl">
        <div class="mb-6">
            <a
                href="{{ route('projects.index') }}"
                class="text-sm font-semibold text-indigo-600"
            >
                ← Back to projects
            </a>

            <h1 class="mt-3 text-2xl font-black text-slate-950">
                Add New Project
            </h1>

            <p class="mt-1 text-sm text-slate-500">
                Enter pricing, schedule and team assignment information.
            </p>
        </div>

        <form
            method="POST"
            action="{{ route('projects.store') }}"
        >
            @csrf

            @include('projects._form', [
                'submitLabel' => 'Create Project',
            ])
        </form>
    </div>
@endsection