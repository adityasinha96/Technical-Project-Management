<?php

namespace App\Http\Requests;

use App\Enums\TicketCommentType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTicketCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(
            'tickets.comment'
        ) ?? false;
    }

    public function rules(): array
    {
        return [
            'comment_type' => [
                'required',

                Rule::enum(
                    TicketCommentType::class
                ),
            ],

            'message' => [
                'required',
                'string',
                'max:30000',
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