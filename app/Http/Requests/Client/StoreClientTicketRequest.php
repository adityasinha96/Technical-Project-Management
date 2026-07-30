<?php

namespace App\Http\Requests\Client;

use App\Enums\TicketPriority;
use App\Enums\TicketType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClientTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('client')->check();
    }

    public function rules(): array
    {
        return [
            'type' => [
                'required',
                Rule::enum(
                    TicketType::class
                ),
            ],

            'priority' => [
                'required',

                Rule::in([
                    TicketPriority::Low->value,
                    TicketPriority::Medium->value,
                    TicketPriority::High->value,
                    TicketPriority::Urgent->value,
                ]),
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

            'attachments' => [
                'nullable',
                'array',
                'max:5',
            ],

            'attachments.*' => [
                'file',
                'mimes:pdf,png,jpg,jpeg,webp,doc,docx,xls,xlsx,csv,txt,zip',
                'max:20480',
            ],
        ];
    }
}