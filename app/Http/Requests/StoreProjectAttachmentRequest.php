<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectAttachmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(
            'attachments.upload'
        ) ?? false;
    }

    public function rules(): array
    {
        return [
            'attachments' => [
                'required',
                'array',
                'min:1',
                'max:10',
            ],

            'attachments.*' => [
                'required',
                'file',
                'mimes:pdf,png,jpg,jpeg,webp,doc,docx,xls,xlsx,ppt,pptx,csv,txt,zip',
                'max:20480',
            ],
        ];
    }
}