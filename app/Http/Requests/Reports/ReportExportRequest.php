<?php

namespace App\Http\Requests\Reports;

use App\Enums\ReportType;
use Illuminate\Validation\Rule;

class ReportExportRequest extends ReportFilterRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(
            'reports.export'
        ) ?? false;
    }

    public function rules(): array
    {
        return [
            ...parent::rules(),

            'report_type' => [
                'required',
                Rule::enum(ReportType::class),
            ],
        ];
    }
}