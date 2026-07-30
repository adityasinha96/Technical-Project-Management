<?php

namespace App\Models;

use App\Enums\BackupStatus;
use App\Enums\BackupTrigger;
use App\Enums\BackupType;
use App\Enums\BackupVerificationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BackupRun extends Model
{
    protected $fillable = [
        'backup_uuid',
        'backup_type',
        'trigger',
        'status',
        'verification_status',
        'disk',
        'path',
        'filename',
        'size_bytes',
        'checksum_sha256',
        'is_encrypted',
        'encryption_method',
        'requested_by',
        'queued_at',
        'started_at',
        'completed_at',
        'failed_at',
        'verified_at',
        'retention_until',
        'verification_message',
        'error_message',
        'manifest',
    ];

    protected function casts(): array
    {
        return [
            'backup_type' =>
                BackupType::class,

            'trigger' =>
                BackupTrigger::class,

            'status' =>
                BackupStatus::class,

            'verification_status' =>
                BackupVerificationStatus::class,

            'size_bytes' => 'integer',
            'is_encrypted' => 'boolean',
            'manifest' => 'array',

            'queued_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'failed_at' => 'datetime',
            'verified_at' => 'datetime',
            'retention_until' => 'datetime',
        ];
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'requested_by'
        );
    }
}