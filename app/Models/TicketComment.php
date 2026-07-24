<?php

namespace App\Models;

use App\Enums\TicketCommentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TicketComment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'project_ticket_id',
        'comment_type',
        'message',
        'created_by',
        'edited_by',
        'edited_at',
    ];

    protected function casts(): array
    {
        return [
            'comment_type' =>
                TicketCommentType::class,

            'edited_at' => 'datetime',
        ];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(
            ProjectTicket::class,
            'project_ticket_id'
        );
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function editedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'edited_by'
        );
    }

    public function fileLinks(): MorphMany
    {
        return $this->morphMany(
            ProjectFileLink::class,
            'fileable'
        );
    }

    public function canBeManagedBy(
        User $user
    ): bool {
        return $user->hasRole('super-admin')
            || $user->can('tickets.manage-all')
            || $this->created_by === $user->id;
    }
}