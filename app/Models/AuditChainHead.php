<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditChainHead extends Model
{
    protected $fillable = [
        'last_sequence',
        'last_hash',
    ];

    protected function casts(): array
    {
        return [
            'last_sequence' => 'integer',
        ];
    }
}