<?php

namespace App\Http\Requests;

use App\Enums\WorkLogStatus;
use App\Enums\WorkLogType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreProjectWorkLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(
            'work-logs.create'
        ) ?? false;
    }

    public function rules(): array
    {
        return [
            'project_task_id' => [
                'nullable',
                Rule::exists(
                    'project_tasks',
                    'id'
                )->whereNull('deleted_at'),
            ],

            'work_date' => [
                'required',
                'date',
            ],

            'work_type' => [
                'required',
                Rule::enum(WorkLogType::class),
            ],

            'status' => [
                'required',
                Rule::enum(WorkLogStatus::class),
            ],

            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'details' => [
                'nullable',
                'string',
                'max:20000',
            ],

            'outcome' => [
                'nullable',
                'string',
                'max:10000',
            ],

            'blocker' => [
                Rule::requiredIf(
                    $this->input('status') ===
                    WorkLogStatus::Blocked->value
                ),
                'nullable',
                'string',
                'max:10000',
            ],

            'duration_minutes' => [
                'required',
                'integer',
                'min:1',
                'max:1440',
            ],

            'is_billable' => [
                'nullable',
                'boolean',
            ],

            'attachments' => [
                'nullable',
                'array',
                'max:10',
            ],

            'attachments.*' => [
                'file',
                'mimes:pdf,png,jpg,jpeg,webp,doc,docx,xls,xlsx,csv,txt,zip',
                'max:20480',
            ],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if (!$this->filled('project_task_id')) {
                    return;
                }

                $project = $this->route('project');

                $belongsToProject =
                    $project?->tasks()
                        ->whereKey(
                            $this->integer(
                                'project_task_id'
                            )
                        )
                        ->exists();

                if (!$belongsToProject) {
                    $validator->errors()->add(
                        'project_task_id',
                        'The selected task does not belong to this project.'
                    );
                }
            },
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_billable' =>
                $this->boolean('is_billable'),
        ]);
    }
}