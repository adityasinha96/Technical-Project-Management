<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;

class StoreClientTicketCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('client')->check();
    }

    public function rules(): array
    {
        return [
            'message' => [
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