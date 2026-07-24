<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class ProjectFile extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'project_id',
        'uploaded_by',
        'category',
        'original_name',
        'stored_name',
        'path',
        'disk',
        'mime_type',
        'size',
        'description',

        /*
        |--------------------------------------------------------------------------
        | Phase 6 secure attachment fields
        |--------------------------------------------------------------------------
        */
        'is_private',
        'checksum_sha256',
        'download_count',
        'last_downloaded_at',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',

            /*
            |--------------------------------------------------------------------------
            | Phase 6 secure attachment casts
            |--------------------------------------------------------------------------
            */
            'is_private' => 'boolean',
            'download_count' => 'integer',
            'last_downloaded_at' => 'datetime',
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

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'uploaded_by'
        );
    }

    public function links(): HasMany
    {
        return $this->hasMany(
            ProjectFileLink::class,
            'project_file_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | File URL helpers
    |--------------------------------------------------------------------------
    */

    public function getUrlAttribute(): string
    {
        return Storage::disk($this->disk)
            ->url($this->path);
    }

    public function getSecureDownloadUrlAttribute(): string
    {
        return route(
            'projects.attachments.download',
            [
                'project' => $this->project_id,
                'projectFile' => $this->id,
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | File access control
    |--------------------------------------------------------------------------
    */

    public function isAccessibleBy(User $user): bool
    {
        if ($user->hasRole('super-admin')) {
            return true;
        }

        if (!$user->can('attachments.view')) {
            return false;
        }

        return match ($this->category) {
            'payment' =>
                $user->can('payments.view'),

            'expense' =>
                $user->can('expenses.view'),

            'approval' =>
                $user->can('approvals.view'),

            'work_log' =>
                $user->can('work-logs.view'),

            'ticket',
            'ticket_comment' =>
                $user->can('tickets.view'),

            'note' =>
                $this->linkedNotesAreVisibleTo($user),

            default =>
                true,
        };
    }

    private function linkedNotesAreVisibleTo(
        User $user
    ): bool {
        $links = $this->relationLoaded('links')
            ? $this->links
            : $this->links()
                ->with('fileable')
                ->get();

        $noteLinks = $links->filter(
            fn (ProjectFileLink $link): bool =>
                $link->fileable instanceof ProjectNote
        );

        if ($noteLinks->isEmpty()) {
            return $user->can('notes.view');
        }

        return $noteLinks->contains(
            fn (ProjectFileLink $link): bool =>
                $link->fileable->isVisibleTo($user)
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Formatting helpers
    |--------------------------------------------------------------------------
    */

    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->size;

        if ($bytes >= 1_048_576) {
            return round(
                $bytes / 1_048_576,
                2
            ) . ' MB';
        }

        if ($bytes >= 1_024) {
            return round(
                $bytes / 1_024,
                2
            ) . ' KB';
        }

        return $bytes . ' Bytes';
    }
}