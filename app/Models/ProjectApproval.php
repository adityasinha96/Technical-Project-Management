<?php

namespace App\Models;

use App\Enums\ApprovalStage;
use App\Enums\ApprovalStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectApproval extends Model
{
    protected $fillable = [
        'project_id',
        'stage',
        'submission_number',
        'status',
        'submitted_by',
        'submitted_at',
        'reviewed_by',
        'reviewed_at',
        'client_reviewer_name',
        'client_reviewer_email',
        'client_reviewer_phone',
        'submission_notes',
        'client_remarks',
        'internal_remarks',
        'proof_file_id',
    ];

    protected function casts(): array
    {
        return [
            'stage' => ApprovalStage::class,
            'status' => ApprovalStatus::class,
            'submission_number' => 'integer',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'submitted_by'
        );
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'reviewed_by'
        );
    }

    public function proofFile(): BelongsTo
    {
        return $this->belongsTo(
            ProjectFile::class,
            'proof_file_id'
        );
    }
}