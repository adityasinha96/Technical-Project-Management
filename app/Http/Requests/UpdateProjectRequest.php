<?php

namespace App\Http\Requests;
use App\Enums\ProjectStatus;
use Illuminate\Validation\Validator;

class UpdateProjectRequest extends StoreProjectRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('projects.update') ?? false;
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $project = $this->route('project');

                if (!$project) {
                    return;
                }

                $requestedStatus = $this->input('status');

                $protectedStatuses = [
                    ProjectStatus::FrontendApproved->value,
                    ProjectStatus::BackendApproved->value,
                    ProjectStatus::Completed->value,
                ];

                $currentStatus = $project->status instanceof ProjectStatus
                    ? $project->status->value
                    : (string) $project->status;

                if (
                    in_array(
                        $requestedStatus,
                        $protectedStatuses,
                        true
                    ) &&
                    $currentStatus !== $requestedStatus
                ) {
                    $validator->errors()->add(
                        'status',
                        'Approval-controlled statuses cannot be selected manually.'
                    );
                }
            },
        ];
    }
}