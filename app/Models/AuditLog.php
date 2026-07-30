<?php

namespace App\Models;

use App\Enums\AuditCategory;
use App\Enums\AuditSeverity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use LogicException;

class AuditLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'audit_uuid',
        'sequence',
        'event_type',
        'category',
        'severity',
        'actor_type',
        'actor_id',
        'auditable_type',
        'auditable_id',
        'actor_name',
        'actor_email',
        'guard',
        'route_name',
        'request_method',
        'request_path',
        'ip_address',
        'user_agent',
        'session_id_hash',
        'old_values',
        'new_values',
        'metadata',
        'previous_hash',
        'entry_hash',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'category' =>
                AuditCategory::class,

            'severity' =>
                AuditSeverity::class,

            'old_values' => 'array',
            'new_values' => 'array',
            'metadata' => 'array',

            'occurred_at' =>
                'immutable_datetime',
        ];
    }

    protected static function booted(): void
    {
        static::updating(
            fn () => throw new LogicException(
                'Audit logs are immutable.'
            )
        );

        static::deleting(
            fn () => throw new LogicException(
                'Audit logs cannot be deleted.'
            )
        );
    }

    public function actor(): MorphTo
    {
        return $this->morphTo();
    }

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }
}