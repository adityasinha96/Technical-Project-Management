<?php

namespace App\Http\Requests;

class UpdateProjectNoteRequest extends StoreProjectNoteRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(
            'notes.update'
        ) ?? false;
    }
}