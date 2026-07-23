@extends('layouts.admin')

@section('title', 'Edit Expense')
@section('page-title', 'Edit Pending Expense')

@section('content')
    <div class="mx-auto max-w-6xl">
        <div class="mb-6">
            <a
                href="{{ route(
                    'expenses.show',
                    $expense
                ) }}"
                class="text-sm font-semibold text-indigo-600"
            >
                ← Back to expense
            </a>

            <h1 class="mt-3 text-2xl font-black text-slate-950">
                Edit {{ $expense->expense_number }}
            </h1>
        </div>

        <form
            method="POST"
            action="{{ route(
                'expenses.update',
                $expense
            ) }}"
            enctype="multipart/form-data"
        >
            @csrf
            @method('PUT')

            @include('expenses._form', [
                'submitLabel' => 'Save Expense',
            ])
        </form>
    </div>
@endsection