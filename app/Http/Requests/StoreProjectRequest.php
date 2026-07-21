<?php

namespace App\Http\Requests;

use App\Enums\ProjectPriority;
use App\Enums\ProjectStatus;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('projects.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'client_id' => [
                'required',
                Rule::exists('clients', 'id')
                    ->whereNull('deleted_at'),
            ],

            'project_category_id' => [
                'nullable',
                Rule::exists('project_categories', 'id')
                    ->where('is_active', true),
            ],

            'manager_id' => [
                'nullable',
                Rule::exists('users', 'id')
                    ->where(
                        fn (Builder $query) =>
                        $query->where('status', 'active')
                    ),
            ],

            'team_member_ids' => [
                'nullable',
                'array',
            ],

            'team_member_ids.*' => [
                'integer',
                'distinct',
                Rule::exists('users', 'id')
                    ->where(
                        fn (Builder $query) =>
                        $query->where('status', 'active')
                    ),
            ],

            'project_template_id' => [
                'nullable',
                Rule::exists('project_templates', 'id')
                    ->where('is_active', true),
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
                'max:10000',
            ],

            'project_price' => [
                'required',
                'numeric',
                'min:0',
                'max:999999999999.99',
            ],

            'estimated_cost' => [
                'nullable',
                'numeric',
                'min:0',
                'max:999999999999.99',
            ],

            'currency' => [
                'required',
                'string',
                'size:3',
            ],

            'start_date' => [
                'required',
                'date',
            ],

            'expected_delivery_date' => [
                'required',
                'date',
                'after_or_equal:start_date',
            ],

            'revised_delivery_date' => [
                'nullable',
                'date',
                'after_or_equal:start_date',
            ],

            'actual_completion_date' => [
                'nullable',
                'date',
                'after_or_equal:start_date',
            ],

            'maximum_duration_days' => [
                'required',
                'integer',
                'min:1',
                'max:365',
            ],

            'status' => [
                'required',
                Rule::enum(ProjectStatus::class),
            ],

            'priority' => [
                'required',
                Rule::enum(ProjectPriority::class),
            ],

            'payment_terms' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'reference_url' => [
                'nullable',
                'url:http,https',
                'max:2048',
            ],

            'development_url' => [
                'nullable',
                'url:http,https',
                'max:2048',
            ],

            'live_url' => [
                'nullable',
                'url:http,https',
                'max:2048',
            ],

            'domain_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'hosting_provider' => [
                'nullable',
                'string',
                'max:255',
            ],

            'domain_expiry_date' => [
                'nullable',
                'date',
            ],

            'hosting_expiry_date' => [
                'nullable',
                'date',
            ],

            'internal_remarks' => [
                'nullable',
                'string',
                'max:10000',
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'estimated_cost' => $this->estimated_cost ?: 0,
            'currency' => strtoupper($this->currency ?: 'INR'),
        ]);
    }
}