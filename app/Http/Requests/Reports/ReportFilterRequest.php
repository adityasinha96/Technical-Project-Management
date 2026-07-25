<?php

namespace App\Http\Requests\Reports;

use App\Enums\ProjectPriority;
use App\Enums\ProjectStatus;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReportFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(
            'reports.view'
        ) ?? false;
    }

    public function rules(): array
    {
        return [
            'date_from' => [
                'nullable',
                'date',
            ],

            'date_to' => [
                'nullable',
                'date',
                'after_or_equal:date_from',
            ],

            'project_id' => [
                'nullable',
                'integer',
                Rule::exists('projects', 'id'),
            ],

            'client_id' => [
                'nullable',
                'integer',
                Rule::exists('clients', 'id'),
            ],

            'user_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id'),
            ],

            'project_status' => [
                'nullable',
                Rule::enum(
                    ProjectStatus::class
                ),
            ],

            'project_priority' => [
                'nullable',
                Rule::enum(
                    ProjectPriority::class
                ),
            ],

            'ticket_status' => [
                'nullable',
                Rule::enum(
                    TicketStatus::class
                ),
            ],

            'ticket_priority' => [
                'nullable',
                Rule::enum(
                    TicketPriority::class
                ),
            ],

            'per_page' => [
                'nullable',
                'integer',
                Rule::in([
                    15,
                    25,
                    50,
                    100,
                ]),
            ],

            'sort' => [
                'nullable',
                'string',
                'max:50',
            ],

            'direction' => [
                'nullable',
                Rule::in([
                    'asc',
                    'desc',
                ]),
            ],
        ];
    }
}