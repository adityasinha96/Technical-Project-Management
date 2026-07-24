<?php

namespace App\Models;

use App\Enums\TicketPriority;
use Illuminate\Database\Eloquent\Model;

class TicketSlaPolicy extends Model
{
    protected $fillable = [
        'priority',
        'first_response_minutes',
        'resolution_minutes',
        'warning_before_minutes',
        'level_two_after_minutes',
        'level_three_after_minutes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'priority' => TicketPriority::class,

            'first_response_minutes' =>
                'integer',

            'resolution_minutes' =>
                'integer',

            'warning_before_minutes' =>
                'integer',

            'level_two_after_minutes' =>
                'integer',

            'level_three_after_minutes' =>
                'integer',

            'is_active' => 'boolean',
        ];
    }

    public static function forPriority(
        TicketPriority $priority
    ): self {
        return static::query()
            ->where(
                'priority',
                $priority->value
            )
            ->where('is_active', true)
            ->firstOrFail();
    }
}