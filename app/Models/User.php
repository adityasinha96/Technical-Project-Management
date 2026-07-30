<?php

namespace App\Models;

use App\Traits\AuditsSystemChanges;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory;
    use HasRoles;
    use Notifiable;
    use AuditsSystemChanges;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'avatar',
        'status',
        'password',
        'email_verified_at',
        'last_login_at',
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
    | Credentials and frequently changing login/session fields must never be
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
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function managedProjects(): HasMany
    {
        return $this->hasMany(
            Project::class,
            'manager_id'
        );
    }

    public function assignedProjects(): BelongsToMany
    {
        return $this
            ->belongsToMany(Project::class)
            ->withPivot([
                'assignment_role',
                'assigned_by',
                'assigned_at',
            ])
            ->withTimestamps();
    }

    public function uploadedProjectFiles(): HasMany
    {
        return $this->hasMany(
            ProjectFile::class,
            'uploaded_by'
        );
    }

    public function assignedTasks(): HasMany
    {
        return $this->hasMany(
            ProjectTask::class,
            'assigned_to'
        );
    }

    public function submittedApprovals(): HasMany
    {
        return $this->hasMany(
            ProjectApproval::class,
            'submitted_by'
        );
    }

    public function reviewedApprovals(): HasMany
    {
        return $this->hasMany(
            ProjectApproval::class,
            'reviewed_by'
        );
    }

    public function recordedPayments(): HasMany
    {
        return $this->hasMany(
            Payment::class,
            'created_by'
        );
    }

    public function voidedPayments(): HasMany
    {
        return $this->hasMany(
            Payment::class,
            'voided_by'
        );
    }

    public function assignedPaymentFollowups(): HasMany
    {
        return $this->hasMany(
            PaymentFollowup::class,
            'assigned_to'
        );
    }

    public function recordedExpenses(): HasMany
    {
        return $this->hasMany(
            Expense::class,
            'created_by'
        );
    }

    public function voidedExpenses(): HasMany
    {
        return $this->hasMany(
            Expense::class,
            'voided_by'
        );
    }

    public function createdTickets(): HasMany
    {
        return $this->hasMany(
            ProjectTicket::class,
            'created_by'
        );
    }

    public function assignedTickets(): HasMany
    {
        return $this->hasMany(
            ProjectTicket::class,
            'assigned_to'
        );
    }

    public function ticketComments(): HasMany
    {
        return $this->hasMany(
            TicketComment::class,
            'created_by'
        );
    }

    public function acknowledgedTicketEscalations(): HasMany
    {
        return $this->hasMany(
            TicketEscalation::class,
            'acknowledged_by'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Phase 8 Notification Relationships
    |--------------------------------------------------------------------------
    */

    public function notificationSetting(): HasOne
    {
        return $this->hasOne(
            UserNotificationSetting::class
        );
    }

    public function notificationPreferences(): HasMany
    {
        return $this->hasMany(
            UserNotificationPreference::class
        );
    }

    public function notificationDispatches(): HasMany
    {
        return $this->hasMany(
            NotificationDispatch::class
        );
    }

    public function getOrCreateNotificationSetting():
        UserNotificationSetting
    {
        return $this
            ->notificationSetting()
            ->firstOrCreate([], [
                'timezone' => 'Asia/Kolkata',
                'daily_digest_time' => '08:30:00',
            ]);
    }

    public function reportExports(): HasMany
    {
        return $this->hasMany(
            ReportExport::class,
            'generated_by'
        );
    }
}

