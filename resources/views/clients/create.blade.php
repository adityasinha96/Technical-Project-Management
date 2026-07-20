@extends('layouts.admin')

@section('title', 'Add Client')
@section('page-title', 'Add New Client')

@section('content')
    <div class="mx-auto max-w-5xl">
        <div class="mb-6">
            <a
                href="{{ route('clients.index') }}"
                class="text-sm font-semibold text-indigo-600 hover:text-indigo-700"
            >
                ← Back to clients
            </a>

            <h1 class="mt-3 text-2xl font-black text-slate-950">
                Add New Client
            </h1>

            <p class="mt-1 text-sm text-slate-500">
                Create the client profile before adding a project.
            </p>
        </div>

        <form
            method="POST"
            action="{{ route('clients.store') }}"
        >
            @csrf

            @include('clients._form', [
                'submitLabel' => 'Create Client',
            ])
        </form>
    </div>
@endsection