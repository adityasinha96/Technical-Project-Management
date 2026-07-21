<?php

namespace App\Http\Requests;

class UpdateProjectTaskRequest extends StoreProjectTaskRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('tasks.update')
            ?? false;
    }
}