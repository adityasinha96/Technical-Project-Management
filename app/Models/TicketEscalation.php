<?php

namespace App\Models;

use App\Enums\TicketEscalationLevel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketEscalation extends Model
{
    protected $fillable = [
        'project_ticket_id',
        'cycle',
        'level',
        'due_at',
        'triggered_at',
        'minutes_overdue',
        'reason',
        'acknowledged_at',
        'acknowledged_by',
        'acknowledgement_note',
    ];

    protected function casts(): array
    {
        return [
            'level' =>
                TicketEscalationLevel::class,

            'cycle' => 'integer',
            'due_at' => 'datetime',
            'triggered_at' => 'datetime',
            'minutes_overdue' => 'integer',
            'acknowledged_at' => 'datetime',
        ];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(
            ProjectTicket::class,
            'project_ticket_id'
        );
    }

    public function acknowledgedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'acknowledged_by'
        );
    }
}