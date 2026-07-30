<?php

namespace App\Models;

use App\Enums\ClientProjectRole;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ClientProjectAccess extends Pivot
{
    protected $table =
        'client_project_access';

    public $incrementing = true;

    protected $fillable = [
        'client_user_id',
        'project_id',
        'role',
        'can_view_project',
        'can_view_financials',
        'can_approve',
        'can_submit_tickets',
        'can_view_files',
        'can_communicate',
        'is_active',
        'granted_by',
        'granted_at',
        'revoked_at',
        'revoked_by',
    ];

    protected function casts(): array
    {
        return [
            'role' =>
                ClientProjectRole::class,

            'can_view_project' =>
                'boolean',

            'can_view_financials' =>
                'boolean',

            'can_approve' =>
                'boolean',

            'can_submit_tickets' =>
                'boolean',

            'can_view_files' =>
                'boolean',

            'can_communicate' =>
                'boolean',

            'is_active' =>
                'boolean',

            'granted_at' =>
                'datetime',

            'revoked_at' =>
                'datetime',
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

    public function grantedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'granted_by'
        );
    }
}