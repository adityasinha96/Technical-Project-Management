<?php

namespace App\Models;

use App\Enums\NotificationDeliveryStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class NotificationDispatch extends Model
{
    protected $fillable = [
        'batch_uuid',
        'user_id',
        'event_key',
        'subject_type',
        'subject_id',
        'channel',
        'dedupe_key',
        'status',
        'payload',
        'scheduled_for',
        'sent_at',
        'failed_at',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'status' =>
                NotificationDeliveryStatus::class,

            'payload' => 'array',
            'scheduled_for' => 'datetime',
            'sent_at' => 'datetime',
            'failed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }
}