<?php

namespace App\Models;

use App\Enums\ReportExportStatus;
use App\Enums\ReportType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportExport extends Model
{
    protected $fillable = [
        'export_uuid',
        'report_type',
        'format',
        'filters',
        'filename',
        'generated_by',
        'status',
        'rows_exported',
        'started_at',
        'completed_at',
        'failed_at',
        'error_message',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'report_type' => ReportType::class,

            'status' =>
                ReportExportStatus::class,

            'filters' => 'array',
            'rows_exported' => 'integer',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'failed_at' => 'datetime',
        ];
    }

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'generated_by'
        );
    }
}