<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ProjectFileLink extends Model
{
    protected $fillable = [
        'project_file_id',
        'fileable_type',
        'fileable_id',
        'created_by',
    ];

    public function file(): BelongsTo
    {
        return $this->belongsTo(
            ProjectFile::class,
            'project_file_id'
        );
    }

    public function fileable(): MorphTo
    {
        return $this->morphTo();
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }
}