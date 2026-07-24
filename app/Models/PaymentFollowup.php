<?php

namespace App\Models;

use App\Concerns\LogsProjectActivity;
use App\Enums\ActivityVisibility;
use App\Enums\PaymentFollowupChannel;
use App\Enums\PaymentFollowupStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentFollowup extends Model
{
    use LogsProjectActivity;
    use SoftDeletes;

    protected $fillable = [
        'project_id',
        'client_id',
        'channel',
        'status',
        'followup_at',
        'next_followup_at',
        'promised_payment_date',
        'promised_amount',
        'client_contact_name',
        'client_response',
        'notes',
        'assigned_to',
        'created_by',
        'completed_by',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'channel' => PaymentFollowupChannel::class,
            'status' => PaymentFollowupStatus::class,

            'followup_at' => 'datetime',
            'next_followup_at' => 'datetime',
            'promised_payment_date' => 'date',

            'promised_amount' => 'decimal:2',
            'completed_at' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'assigned_to'
        );
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'completed_by'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Project activity logging configuration
    |--------------------------------------------------------------------------
    */

    public function activityTrackedAttributes(): array
    {
        return [
            'channel',
            'status',
            'followup_at',
            'next_followup_at',
            'promised_payment_date',
            'promised_amount',
            'assigned_to',
            'completed_at',
        ];
    }

    public function activityLabel(): string
    {
        return 'Payment follow-up';
    }

    public function activityVisibility(): ActivityVisibility
    {
        return ActivityVisibility::Financial;
    }

    /*
    |--------------------------------------------------------------------------
    | Query scopes
    |--------------------------------------------------------------------------
    */

    public function scopeOpen(
        Builder $query
    ): Builder {
        return $query->whereNotIn(
            'status',
            PaymentFollowupStatus::closedValues()
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Follow-up helpers
    |--------------------------------------------------------------------------
    */

    public function getIsOverdueAttribute(): bool
    {
        if ($this->status->isClosed()) {
            return false;
        }

        $dueAt = $this->next_followup_at
            ?: $this->followup_at;

        if (!$dueAt) {
            return false;
        }

        return $dueAt->isBefore(
            now()
        );
    }

    public function getDueAtAttribute()
    {
        return $this->next_followup_at
            ?: $this->followup_at;
    }
}