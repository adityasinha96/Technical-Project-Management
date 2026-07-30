<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientPortalInvitation extends Model
{
    protected $fillable = [
        'client_user_id',
        'project_id',
        'token_hash',
        'expires_at',
        'accepted_at',
        'cancelled_at',
        'invited_by',
        'cancelled_by',
        'send_count',
        'last_sent_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'accepted_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'last_sent_at' => 'datetime',
            'send_count' => 'integer',
        ];
    }

    public function clientUser(): BelongsTo
    {
        return $this->belongsTo(
            ClientUser::class
        );
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(
            Project::class
        );
    }

    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'invited_by'
        );
    }

    public function isUsable(): bool
    {
        return !$this->accepted_at
            && !$this->cancelled_at
            && $this->expires_at->isFuture();
    }

    public function matchesToken(
        string $rawToken
    ): bool {
        return hash_equals(
            $this->token_hash,
            hash('sha256', $rawToken)
        );
    }
}