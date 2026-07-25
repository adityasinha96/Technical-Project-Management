<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserNotificationSetting extends Model
{
    protected $fillable = [
        'user_id',
        'in_app_notifications_enabled',
        'email_notifications_enabled',
        'daily_digest_enabled',
        'daily_digest_time',
        'timezone',
        'last_daily_digest_sent_on',
    ];

    protected function casts(): array
    {
        return [
            'in_app_notifications_enabled' =>
                'boolean',

            'email_notifications_enabled' =>
                'boolean',

            'daily_digest_enabled' =>
                'boolean',

            'last_daily_digest_sent_on' =>
                'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}