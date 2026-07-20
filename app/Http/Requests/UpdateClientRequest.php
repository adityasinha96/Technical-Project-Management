<?php

namespace App\Http\Requests;

class UpdateClientRequest extends StoreClientRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('clients.update') ?? false;
    }
}