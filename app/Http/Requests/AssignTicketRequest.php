<?php

namespace App\Http\Requests;

use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(
            'tickets.assign'
        ) ?? false;
    }

    public function rules(): array
    {
        return [
            'assigned_to' => [
                'nullable',

                Rule::exists('users', 'id')
                    ->where(
                        fn (Builder $query) =>
                            $query->where(
                                'status',
                                'active'
                            )
                    ),
            ],
        ];
    }
}