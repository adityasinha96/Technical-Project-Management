@extends('layouts.admin')

@section('title', 'Create Ticket')
@section('page-title', 'Create Project Ticket')

@section('content')
    <div class="mx-auto max-w-5xl">
        <div class="mb-6">
            <a
                href="{{ route('tickets.index') }}"
                class="text-sm font-bold text-indigo-600"
            >
                ← Back to tickets
            </a>

            <h1 class="mt-3 text-2xl font-black text-slate-950">
                Create Project Ticket
            </h1>
        </div>

        <form
            method="POST"
            action="{{ route('tickets.store') }}"
            enctype="multipart/form-data"
        >
            @csrf

            @include('tickets._form')
        </form>
    </div>
@endsection