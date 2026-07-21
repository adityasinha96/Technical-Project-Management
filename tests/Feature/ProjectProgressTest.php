<?php

use App\Enums\ApprovalStage;
use App\Enums\ApprovalStatus;
use App\Enums\ClientStatus;
use App\Enums\ProjectPriority;
use App\Enums\ProjectStatus;
use App\Enums\TaskPhase;
use App\Enums\TaskStatus;
use App\Models\Client;
use App\Models\Project;
use App\Services\Projects\ProjectProgressService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Create a basic client and project for workflow testing.
 */
function createTestProject(): Project
{
    $client = Client::create([
        'name' => 'Test Client',
        'status' => ClientStatus::Active->value,
        'client_type' => 'business',
    ]);

    return Project::create([
        'client_id' => $client->id,
        'name' => 'Test Project',

        'project_price' => 50000,
        'estimated_cost' => 10000,

        'start_date' => today(),
        'expected_delivery_date' => today()->addDays(18),

        'status' => ProjectStatus::NewProject->value,
        'priority' => ProjectPriority::Medium->value,

        'maximum_duration_days' => 18,
    ]);
}

it('calculates weighted internal task progress', function () {
    $project = createTestProject();

    $project->tasks()->create([
        'title' => 'Frontend',
        'phase' => TaskPhase::Frontend->value,
        'status' => TaskStatus::Completed->value,
        'priority' => ProjectPriority::Medium->value,
        'weight' => 50,
        'progress' => 100,
    ]);

    $project->tasks()->create([
        'title' => 'Backend',
        'phase' => TaskPhase::Backend->value,
        'status' => TaskStatus::NotStarted->value,
        'priority' => ProjectPriority::Medium->value,
        'weight' => 50,
        'progress' => 0,
    ]);

    app(ProjectProgressService::class)
        ->recalculateInternalProgress($project);

    expect(
        $project->fresh()->internal_progress
    )->toBe(50);
});

it('sets official progress to fifty after frontend approval', function () {
    $project = createTestProject();

    $project->approvals()->create([
        'stage' => ApprovalStage::Frontend->value,
        'submission_number' => 1,
        'status' => ApprovalStatus::Approved->value,
        'submitted_at' => now(),
        'reviewed_at' => now(),
    ]);

    app(ProjectProgressService::class)
        ->synchronizeOfficialProgress($project);

    expect(
        $project->fresh()->official_progress
    )->toBe(50);
});

it('sets official progress to one hundred after backend approval', function () {
    $project = createTestProject();

    $project->approvals()->create([
        'stage' => ApprovalStage::Frontend->value,
        'submission_number' => 1,
        'status' => ApprovalStatus::Approved->value,
        'submitted_at' => now(),
        'reviewed_at' => now(),
    ]);

    $project->approvals()->create([
        'stage' => ApprovalStage::Backend->value,
        'submission_number' => 1,
        'status' => ApprovalStatus::Approved->value,
        'submitted_at' => now(),
        'reviewed_at' => now(),
    ]);

    app(ProjectProgressService::class)
        ->synchronizeOfficialProgress($project);

    $project->refresh();

    expect($project->official_progress)
        ->toBe(100)
        ->and($project->status)
        ->toBe(ProjectStatus::Completed)
        ->and($project->actual_completion_date)
        ->not->toBeNull();
});