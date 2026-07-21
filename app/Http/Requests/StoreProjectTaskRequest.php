<?php

namespace App\Http\Requests;

use App\Enums\ProjectPriority;
use App\Enums\TaskPhase;
use App\Enums\TaskStatus;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProjectTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('tasks.create')
            ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
                'max:10000',
            ],

            'assigned_to' => [
                'nullable',
                Rule::exists('users', 'id')
                    ->where(
                        fn (Builder $query) =>
                            $query->where(
                                'status',
                                'active'
                            )
                    ),
            ],

            'phase' => [
                'required',
                Rule::enum(TaskPhase::class),
            ],

            'priority' => [
                'required',
                Rule::enum(ProjectPriority::class),
            ],

            'status' => [
                'required',
                Rule::enum(TaskStatus::class),
            ],

            'weight' => [
                'required',
                'numeric',
                'gt:0',
                'max:1000',
            ],

            'progress' => [
                'required',
                'integer',
                'min:0',
                'max:100',
            ],

            'estimated_hours' => [
                'nullable',
                'numeric',
                'min:0',
                'max:99999',
            ],

            'start_date' => [
                'nullable',
                'date',
            ],

            'due_date' => [
                'nullable',
                'date',
                'after_or_equal:start_date',
            ],

            'blocked_reason' => [
                Rule::requiredIf(
                    $this->input('status') ===
                    TaskStatus::Blocked->value
                ),
                'nullable',
                'string',
                'max:5000',
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'weight' => $this->input('weight', 1),
            'progress' => $this->input('progress', 0),

            'status' => $this->input(
                'status',
                TaskStatus::NotStarted->value
            ),

            'priority' => $this->input(
                'priority',
                ProjectPriority::Medium->value
            ),

            'phase' => $this->input(
                'phase',
                TaskPhase::General->value
            ),
        ]);
    }
}