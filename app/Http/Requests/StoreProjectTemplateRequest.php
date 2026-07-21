<?php

namespace App\Http\Requests;

use App\Enums\ProjectPriority;
use App\Enums\TaskPhase;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProjectTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(
            'templates.manage'
        ) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique(
                    'project_templates',
                    'name'
                )->whereNull('deleted_at'),
            ],

            'description' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'project_category_id' => [
                'nullable',
                Rule::exists(
                    'project_categories',
                    'id'
                )->where('is_active', true),
            ],

            'default_duration_days' => [
                'required',
                'integer',
                'min:1',
                'max:365',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],

            'tasks' => [
                'required',
                'array',
                'min:1',
                'max:100',
            ],

            'tasks.*.title' => [
                'required',
                'string',
                'max:255',
            ],

            'tasks.*.description' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'tasks.*.phase' => [
                'required',
                Rule::enum(TaskPhase::class),
            ],

            'tasks.*.priority' => [
                'required',
                Rule::enum(ProjectPriority::class),
            ],

            'tasks.*.weight' => [
                'required',
                'numeric',
                'gt:0',
                'max:1000',
            ],

            'tasks.*.estimated_hours' => [
                'nullable',
                'numeric',
                'min:0',
                'max:99999',
            ],

            'tasks.*.default_duration_days' => [
                'nullable',
                'integer',
                'min:1',
                'max:365',
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' =>
                $this->boolean('is_active'),
        ]);
    }
}