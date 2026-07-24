<?php

use App\Enums\ClientStatus;
use App\Enums\ProjectNoteType;
use App\Enums\ProjectNoteVisibility;
use App\Enums\ProjectPriority;
use App\Enums\ProjectStatus;
use App\Enums\TaskPhase;
use App\Enums\TaskStatus;
use App\Enums\WorkLogStatus;
use App\Enums\WorkLogType;
use App\Models\Client;
use App\Models\Project;
use App\Models\ProjectNote;
use App\Models\ProjectTask;
use App\Models\ProjectWorkLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

function createHistoryUser(
    array $permissions = []
): User {
    $user = User::factory()->create([
        'status' => 'active',
        'email_verified_at' => now(),
    ]);

    foreach ($permissions as $permission) {
        Permission::findOrCreate(
            $permission
        );
    }

    $role = Role::findOrCreate(
        'history-test-role'
    );

    $role->syncPermissions(
        $permissions
    );

    $user->assignRole($role);

    return $user;
}

function createHistoryProject(): Project
{
    $client = Client::create([
        'name' => 'History Test Client',
        'client_type' => 'business',
        'status' => ClientStatus::Active->value,
    ]);

    return Project::create([
        'client_id' => $client->id,
        'name' => 'History Test Project',

        'project_price' => 50000,
        'estimated_cost' => 10000,

        'start_date' => today(),

        'expected_delivery_date' =>
            today()->addDays(18),

        'status' =>
            ProjectStatus::NewProject->value,

        'priority' =>
            ProjectPriority::Medium->value,

        'maximum_duration_days' => 18,
    ]);
}

it('logs project task changes in project history', function () {
    $project = createHistoryProject();

    $task = $project->tasks()->create([
        'title' => 'Homepage Development',

        'phase' =>
            TaskPhase::Frontend->value,

        'status' =>
            TaskStatus::NotStarted->value,

        'priority' =>
            ProjectPriority::Medium->value,

        'weight' => 50,
        'progress' => 0,
    ]);

    $task->update([
        'status' =>
            TaskStatus::InProgress->value,

        'progress' => 40,
    ]);

    expect(
        $project
            ->activities()
            ->where('event', 'updated')
            ->where(
                'subject_type',
                $task->getMorphClass()
            )
            ->where(
                'subject_id',
                $task->id
            )
            ->exists()
    )->toBeTrue();
});

it('shows private notes only to their author', function () {
    $project = createHistoryProject();

    $author = createHistoryUser([
        'notes.view',
    ]);

    $anotherUser = createHistoryUser([
        'notes.view',
    ]);

    $note = $project->notes()->create([
        'title' => 'Private Project Note',

        'note_type' =>
            ProjectNoteType::General->value,

        'visibility' =>
            ProjectNoteVisibility::Private->value,

        'content' =>
            'This note is private.',

        'created_by' => $author->id,
        'updated_by' => $author->id,
    ]);

    expect($note->isVisibleTo($author))
        ->toBeTrue()
        ->and(
            $note->isVisibleTo(
                $anotherUser
            )
        )
        ->toBeFalse();
});

it('records work log duration', function () {
    $project = createHistoryProject();
    $user = createHistoryUser();

    $workLog = $project
        ->workLogs()
        ->create([
            'logged_by' => $user->id,

            'work_date' =>
                today()->toDateString(),

            'work_type' =>
                WorkLogType::Development->value,

            'status' =>
                WorkLogStatus::Completed->value,

            'title' =>
                'Developed project dashboard',

            'duration_minutes' => 150,
            'is_billable' => true,
        ]);

    expect($workLog->formatted_duration)
        ->toBe('2 hr 30 min')
        ->and(
            $project
                ->workLogs()
                ->sum('duration_minutes')
        )
        ->toBe(150);
});

it('creates activity when a note is created', function () {
    $project = createHistoryProject();
    $user = createHistoryUser();

    $this->actingAs($user);

    $note = $project->notes()->create([
        'title' => 'Project Requirement',

        'note_type' =>
            ProjectNoteType::Requirement->value,

        'visibility' =>
            ProjectNoteVisibility::Team->value,

        'content' =>
            'Client requested a responsive dashboard.',

        'created_by' => $user->id,
        'updated_by' => $user->id,
    ]);

    expect(
        $project
            ->activities()
            ->where(
                'subject_type',
                $note->getMorphClass()
            )
            ->where(
                'subject_id',
                $note->id
            )
            ->where('event', 'created')
            ->exists()
    )->toBeTrue();
});

it('updates the project last activity timestamp', function () {
    $project = createHistoryProject();

    $project->update([
        'name' =>
            'Updated History Test Project',
    ]);

    expect(
        $project
            ->fresh()
            ->last_activity_at
    )->not->toBeNull();
});