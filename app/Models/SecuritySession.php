<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SecuritySession extends Model
{
    protected $fillable = [
        'session_uuid',
        'guard',
        'actor_type',
        'actor_id',
        'session_id_hash',
        'session_id',
        'ip_address',
        'user_agent',
        'device_fingerprint',
        'logged_in_at',
        'last_seen_at',
        'logged_out_at',
        'revoked_at',
        'revoked_by',
        'revoke_reason',
    ];

    protected function casts(): array
    {
        return [
            'session_id' => 'encrypted',
            'logged_in_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'logged_out_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function actor(): MorphTo
    {
        return $this->morphTo();
    }

    public function revokedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'revoked_by'
        );
    }

    public function isActive(): bool
    {
        return !$this->logged_out_at
            && !$this->revoked_at;
    }
}