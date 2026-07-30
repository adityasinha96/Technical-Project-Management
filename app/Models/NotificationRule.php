<?php

namespace App\Models;

use App\Enums\NotificationRecipientStrategy;
use App\Enums\NotificationSeverity;
use App\Traits\AuditsSystemChanges;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class NotificationRule extends Model
{
    use AuditsSystemChanges;

    protected $fillable = [
        'rule_key',
        'name',
        'description',
        'event_key',
        'severity',
        'recipient_strategy',
        'channels',
        'lead_minutes',
        'repeat_minutes',
        'maximum_occurrences',
        'is_enabled',
        'configuration',
    ];

    /*
    |--------------------------------------------------------------------------
    | System Audit Exclusions
    |--------------------------------------------------------------------------
    |
    | The routine updated_at timestamp is excluded so audit records focus on
    | meaningful notification rule configuration changes.
    |
    */

    protected array $auditExclude = [
        'updated_at',
    ];

    protected function casts(): array
    {
        return [
            'severity' =>
                NotificationSeverity::class,

            'recipient_strategy' =>
                NotificationRecipientStrategy::class,

            'channels' => 'array',
            'configuration' => 'array',

            'lead_minutes' => 'integer',
            'repeat_minutes' => 'integer',
            'maximum_occurrences' => 'integer',
            'is_enabled' => 'boolean',
        ];
    }

    public function scopeEnabled(
        Builder $query
    ): Builder {
        return $query->where(
            'is_enabled',
            true
        );
    }
}