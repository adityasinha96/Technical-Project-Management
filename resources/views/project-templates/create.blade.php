@extends('layouts.admin')

@section('title', 'Add Project Template')
@section('page-title', 'Add Project Template')

@section('content')
    <div class="mx-auto max-w-6xl">
        <div class="mb-6">
            <a
                href="{{ route('project-templates.index') }}"
                class="text-sm font-semibold text-indigo-600"
            >
                ← Back to templates
            </a>

            <h1 class="mt-3 text-2xl font-black text-slate-950">
                Create Project Template
            </h1>
        </div>

        <form
            method="POST"
            action="{{ route('project-templates.store') }}"
        >
            @csrf

            @include('project-templates._form', [
                'submitLabel' => 'Create Template',
            ])
        </form>
    </div>
@endsection