<?php

namespace App\Models;

use App\Enums\ClientUserStatus;
use App\Traits\AuditsSystemChanges;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class ClientUser extends Authenticatable implements
    CanResetPasswordContract
{
    use CanResetPassword;
    use HasFactory;
    use Notifiable;
    use SoftDeletes;
    use AuditsSystemChanges;

    protected $fillable = [
        'client_id',
        'name',
        'email',
        'phone',
        'designation',
        'password',
        'status',
        'email_verified_at',
        'last_login_at',
        'last_login_ip',
        'created_by',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /*
    |--------------------------------------------------------------------------
    | System audit exclusions
    |--------------------------------------------------------------------------
    |
    | Credentials and frequently changing authentication fields must not be
    | stored in system change-audit records.
    |
    */

    protected array $auditExclude = [
        'password',
        'remember_token',
        'last_seen_at',
        'last_login_at',
        'last_login_ip',
        'updated_at',
    ];

    protected function casts(): array
    {
        return [
            'status' =>
                ClientUserStatus::class,

            'password' => 'hashed',

            'email_verified_at' =>
                'datetime',

            'last_login_at' =>
                'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(
            Client::class
        );
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(
            Project::class,
            'client_project_access'
        )
            ->using(ClientProjectAccess::class)
            ->withPivot([
                'id',
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
            ])
            ->withTimestamps();
    }

    public function projectAccessRecords(): HasMany
    {
        return $this->hasMany(
            ClientProjectAccess::class
        );
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(
            ClientPortalInvitation::class
        );
    }

    public function submittedTickets(): HasMany
    {
        return $this->hasMany(
            ProjectTicket::class,
            'submitted_by_client_user_id'
        );
    }

    public function ticketComments(): HasMany
    {
        return $this->hasMany(
            TicketComment::class
        );
    }

    public function communications(): HasMany
    {
        return $this->hasMany(
            ClientCommunication::class
        );
    }

    public function uploadedFiles(): HasMany
    {
        return $this->hasMany(
            ProjectFile::class,
            'uploaded_by_client_user_id'
        );
    }

    public function isActive(): bool
    {
        return $this->status ===
            ClientUserStatus::Active;
    }

    public function sendPasswordResetNotification(
        $token
    ): void {
        $this->notify(
            new \App\Notifications\ClientPortalResetPasswordNotification(
                token: $token
            )
        );
    }
}

