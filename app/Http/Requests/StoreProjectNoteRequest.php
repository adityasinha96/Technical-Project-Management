<?php

namespace App\Http\Requests;

use App\Enums\ProjectNoteType;
use App\Enums\ProjectNoteVisibility;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProjectNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(
            'notes.create'
        ) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_pinned' =>
                $this->boolean('is_pinned'),
        ]);
    }

    public function rules(): array
    {
        return [
            'title' => [
                'nullable',
                'string',
                'max:255',
            ],

            'note_type' => [
                'required',
                Rule::enum(
                    ProjectNoteType::class
                ),
            ],

            'visibility' => [
                'required',
                Rule::enum(
                    ProjectNoteVisibility::class
                ),
            ],

            'content' => [
                'required',
                'string',
                'max:100000',
            ],

            'is_pinned' => [
                'required',
                'boolean',
            ],

            'attachments' => [
                'sometimes',
                'array',
                'max:10',
            ],

            'attachments.*' => [
                'file',
                'max:10240',
            ],
        ];
    }
}