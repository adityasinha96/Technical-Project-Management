@extends('layouts.admin')

@section('title', 'Edit Project')
@section('page-title', 'Edit Project')

@section('content')
    <div class="mx-auto max-w-6xl">
        <div class="mb-6">
            <a
                href="{{ route('projects.show', $project) }}"
                class="text-sm font-semibold text-indigo-600"
            >
                ← Back to project
            </a>

            <h1 class="mt-3 text-2xl font-black text-slate-950">
                Edit {{ $project->name }}
            </h1>
        </div>

        <form
            method="POST"
            action="{{ route('projects.update', $project) }}"
        >
            @csrf
            @method('PUT')

            @include('projects._form', [
                'submitLabel' => 'Save Project',
            ])
        </form>
    </div>
@endsection