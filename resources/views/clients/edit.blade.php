@extends('layouts.admin')

@section('title', 'Edit Client')
@section('page-title', 'Edit Client')

@section('content')
    <div class="mx-auto max-w-5xl">
        <div class="mb-6">
            <a
                href="{{ route('clients.show', $client) }}"
                class="text-sm font-semibold text-indigo-600 hover:text-indigo-700"
            >
                ← Back to client
            </a>

            <h1 class="mt-3 text-2xl font-black text-slate-950">
                Edit {{ $client->display_name }}
            </h1>
        </div>

        <form
            method="POST"
            action="{{ route('clients.update', $client) }}"
        >
            @csrf
            @method('PUT')

            @include('clients._form', [
                'submitLabel' => 'Save Changes',
            ])
        </form>
    </div>
@endsection