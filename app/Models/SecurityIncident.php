<?php

namespace App\Models;

use App\Enums\AuditSeverity;
use App\Enums\SecurityIncidentStatus;
use App\Enums\SecurityIncidentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SecurityIncident extends Model
{
    protected $fillable = [
        'incident_uuid',
        'incident_type',
        'severity',
        'status',
        'fingerprint',
        'title',
        'description',
        'subject_type',
        'subject_id',
        'login_event_id',
        'occurrence_count',
        'detected_at',
        'last_seen_at',
        'assigned_to',
        'acknowledged_by',
        'acknowledged_at',
        'resolved_by',
        'resolved_at',
        'resolution_notes',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'incident_type' =>
                SecurityIncidentType::class,

            'severity' =>
                AuditSeverity::class,

            'status' =>
                SecurityIncidentStatus::class,

            'metadata' => 'array',
            'occurrence_count' => 'integer',
            'detected_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'acknowledged_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function loginEvent(): BelongsTo
    {
        return $this->belongsTo(
            LoginEvent::class
        );
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'assigned_to'
        );
    }
}