<?php

namespace App\Http\Requests\Admin;

use App\Enums\ClientProjectRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InviteClientUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(
            'client-portal.manage'
        ) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
            ],

            'phone' => [
                'nullable',
                'string',
                'max:30',
            ],

            'designation' => [
                'nullable',
                'string',
                'max:255',
            ],

            'role' => [
                'required',
                Rule::enum(
                    ClientProjectRole::class
                ),
            ],

            'can_view_financials' => [
                'nullable',
                'boolean',
            ],

            'can_approve' => [
                'nullable',
                'boolean',
            ],

            'can_submit_tickets' => [
                'nullable',
                'boolean',
            ],

            'can_view_files' => [
                'nullable',
                'boolean',
            ],

            'can_communicate' => [
                'nullable',
                'boolean',
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'can_view_financials' =>
                $this->boolean(
                    'can_view_financials'
                ),

            'can_approve' =>
                $this->boolean(
                    'can_approve'
                ),

            'can_submit_tickets' =>
                $this->boolean(
                    'can_submit_tickets'
                ),

            'can_view_files' =>
                $this->boolean(
                    'can_view_files'
                ),

            'can_communicate' =>
                $this->boolean(
                    'can_communicate'
                ),
        ]);
    }
}