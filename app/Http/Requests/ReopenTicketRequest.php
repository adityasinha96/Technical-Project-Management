<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReopenTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(
            'tickets.reopen'
        ) ?? false;
    }

    public function rules(): array
    {
        return [
            'reopen_reason' => [
                'required',
                'string',
                'min:10',
                'max:10000',
            ],
        ];
    }
}