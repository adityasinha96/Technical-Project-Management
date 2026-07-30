<?php

namespace App\Models;

use App\Concerns\LogsProjectActivity;
use App\Enums\ActivityVisibility;
use App\Enums\ApprovalStage;
use App\Enums\ApprovalStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectApproval extends Model
{
    use LogsProjectActivity;

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

        /*
        |--------------------------------------------------------------------------
        | Client approval fields
        |--------------------------------------------------------------------------
        */
        'is_client_visible',
        'submitted_to_client_at',
        'submitted_to_client_by',
        'client_decision',
        'client_feedback',
        'client_decided_at',
        'client_decided_by',
    ];

    protected function casts(): array
    {
        return [
            'stage' => ApprovalStage::class,
            'status' => ApprovalStatus::class,
            'submission_number' => 'integer',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',

            /*
            |--------------------------------------------------------------------------
            | Client approval casts
            |--------------------------------------------------------------------------
            */
            'is_client_visible' => 'boolean',

            'client_decision' =>
                \App\Enums\ClientApprovalDecision::class,

            'submitted_to_client_at' => 'datetime',
            'client_decided_at' => 'datetime',
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

    public function submittedToClientBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'submitted_to_client_by'
        );
    }

    public function clientDecidedBy(): BelongsTo
    {
        return $this->belongsTo(
            ClientUser::class,
            'client_decided_by'
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
            'stage',
            'submission_number',
            'status',
            'client_reviewer_name',
            'reviewed_at',
        ];
    }

    public function activityLabel(): string
    {
        return "Approval: {$this->stage->label()}";
    }

    public function activityVisibility(): ActivityVisibility
    {
        return ActivityVisibility::Management;
    }
}

