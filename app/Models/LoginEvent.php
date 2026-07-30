<?php

namespace App\Models;

use App\Enums\LoginEventType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class LoginEvent extends Model
{
    protected $fillable = [
        'event_uuid',
        'event_type',
        'guard',
        'authenticatable_type',
        'authenticatable_id',
        'identifier_hash',
        'identifier_masked',
        'successful',
        'ip_address',
        'user_agent',
        'device_fingerprint',
        'session_id_hash',
        'risk_score',
        'failure_reason',
        'metadata',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'event_type' =>
                LoginEventType::class,

            'successful' => 'boolean',
            'risk_score' => 'integer',
            'metadata' => 'array',
            'occurred_at' => 'datetime',
        ];
    }

    public function authenticatable(): MorphTo
    {
        return $this->morphTo();
    }
}