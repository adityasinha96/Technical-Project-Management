<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmSecurityActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(
            'security.perform-sensitive-actions'
        ) ?? false;
    }

    public function rules(): array
    {
        return [
            'password' => [
                'required',
                'string',
            ],
        ];
    }
}