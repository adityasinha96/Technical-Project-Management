<?php

namespace App\Http\Requests;

class UpdateProjectWorkLogRequest extends StoreProjectWorkLogRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(
            'work-logs.update'
        ) ?? false;
    }
}