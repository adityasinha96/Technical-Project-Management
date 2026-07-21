<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateProjectTemplateRequest extends StoreProjectTemplateRequest
{
    public function rules(): array
    {
        $rules = parent::rules();

        $rules['name'] = [
            'required',
            'string',
            'max:255',
            Rule::unique(
                'project_templates',
                'name'
            )
                ->ignore($this->route('projectTemplate'))
                ->whereNull('deleted_at'),
        ];

        return $rules;
    }
}