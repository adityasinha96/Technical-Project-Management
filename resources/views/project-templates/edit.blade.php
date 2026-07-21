@extends('layouts.admin')

@section('title', 'Edit Project Template')
@section('page-title', 'Edit Project Template')

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
                Edit {{ $projectTemplate->name }}
            </h1>
        </div>

        <form
            method="POST"
            action="{{ route(
                'project-templates.update',
                $projectTemplate
            ) }}"
        >
            @csrf
            @method('PUT')

            @include('project-templates._form', [
                'submitLabel' => 'Save Template',
            ])
        </form>
    </div>
@endsection