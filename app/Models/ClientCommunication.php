<?php

namespace App\Models;

use App\Enums\ClientMessageSenderType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class ClientCommunication extends Model
{
    protected $fillable = [
        'project_id',
        'client_user_id',
        'user_id',
        'reply_to_id',
        'sender_type',
        'message',
        'client_read_at',
        'internal_read_at',
    ];

    protected function casts(): array
    {
        return [
            'sender_type' =>
                ClientMessageSenderType::class,

            'client_read_at' =>
                'datetime',

            'internal_read_at' =>
                'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(
            Project::class
        );
    }

    public function clientUser(): BelongsTo
    {
        return $this->belongsTo(
            ClientUser::class
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class
        );
    }

    public function replyTo(): BelongsTo
    {
        return $this->belongsTo(
            self::class,
            'reply_to_id'
        );
    }

    public function replies(): HasMany
    {
        return $this->hasMany(
            self::class,
            'reply_to_id'
        );
    }

    public function fileLinks(): MorphMany
    {
        return $this->morphMany(
            ProjectFileLink::class,
            'fileable'
        );
    }

    public function getSenderNameAttribute(): string
    {
        return match (
            $this->sender_type
        ) {
            ClientMessageSenderType::Client =>
                $this->clientUser?->name
                    ?? 'Client',

            ClientMessageSenderType::InternalUser =>
                $this->user?->name
                    ?? 'UIPRO Team',

            ClientMessageSenderType::System =>
                'System',
        };
    }
}