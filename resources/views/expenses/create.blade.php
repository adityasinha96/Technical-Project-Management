@extends('layouts.admin')

@section('title', 'Add Expense')
@section('page-title', 'Add Expense')

@section('content')
    <div class="mx-auto max-w-6xl">
        <div class="mb-6">
            <a
                href="{{ route('expenses.index') }}"
                class="text-sm font-semibold text-indigo-600"
            >
                ← Back to expenses
            </a>

            <h1 class="mt-3 text-2xl font-black text-slate-950">
                Record New Expense
            </h1>

            <p class="mt-1 text-sm text-slate-500">
                Add a project-specific cost or a general business expense.
            </p>
        </div>

        <form
            method="POST"
            action="{{ route('expenses.store') }}"
            enctype="multipart/form-data"
        >
            @csrf

            @include('expenses._form', [
                'submitLabel' => 'Record Expense',
            ])
        </form>
    </div>
@endsection