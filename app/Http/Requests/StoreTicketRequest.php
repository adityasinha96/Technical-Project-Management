<?php

namespace App\Http\Requests;

use App\Enums\TicketPriority;
use App\Enums\TicketSource;
use App\Enums\TicketType;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(
            'tickets.create'
        ) ?? false;
    }

    public function rules(): array
    {
        return [
            'project_id' => [
                'required',

                Rule::exists(
                    'projects',
                    'id'
                )->whereNull('deleted_at'),
            ],

            'type' => [
                'required',
                Rule::enum(TicketType::class),
            ],

            'source' => [
                'required',
                Rule::enum(TicketSource::class),
            ],

            'priority' => [
                'required',
                Rule::enum(
                    TicketPriority::class
                ),
            ],

            'subject' => [
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'required',
                'string',
                'max:30000',
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

            'attachments' => [
                'nullable',
                'array',
                'max:10',
            ],

            'attachments.*' => [
                'file',
                'mimes:pdf,png,jpg,jpeg,webp,doc,docx,xls,xlsx,csv,txt,zip',
                'max:20480',
            ],
        ];
    }
}