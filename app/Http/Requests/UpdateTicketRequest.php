<?php

namespace App\Http\Requests;

class UpdateTicketRequest extends StoreTicketRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(
            'tickets.update'
        ) ?? false;
    }

    public function rules(): array
    {
        $rules = parent::rules();

        unset(
            $rules['assigned_to'],
            $rules['attachments']
        );

        $rules['project_id'] = [
            'required',
            'integer',
        ];

        return $rules;
    }
}