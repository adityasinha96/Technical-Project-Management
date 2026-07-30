<?php

namespace App\Models;

use App\Traits\AuditsSystemChanges;
use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    use AuditsSystemChanges;

    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'is_public',
        'description',
    ];

    /*
    |--------------------------------------------------------------------------
    | System Audit Exclusions
    |--------------------------------------------------------------------------
    |
    | The generic value column may contain passwords, API keys, SMTP
    | credentials, tokens, or other sensitive configuration. It is excluded
    | from audit payloads to prevent secrets from being stored in audit logs.
    |
    */

    protected array $auditExclude = [
        'value',
        'updated_at',
    ];

    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
        ];
    }
}

