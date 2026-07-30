<?php

namespace App\Models;

use App\Enums\PermissionChangeAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PermissionChangeLog extends Model
{
    protected $fillable = [
        'change_uuid',
        'action',
        'target_user_id',
        'role_id',
        'permission_id',
        'target_user_name',
        'role_name',
        'permission_name',
        'performed_by',
        'before_values',
        'after_values',
        'ip_address',
        'user_agent',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'action' =>
                PermissionChangeAction::class,

            'before_values' => 'array',
            'after_values' => 'array',
            'occurred_at' => 'datetime',
        ];
    }

    public function targetUser(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'target_user_id'
        );
    }

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'performed_by'
        );
    }
}