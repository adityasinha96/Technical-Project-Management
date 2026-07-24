<?php

use App\Enums\ClientStatus;
use App\Enums\ProjectPriority;
use App\Enums\ProjectStatus;
use App\Enums\TicketCommentType;
use App\Enums\TicketPriority;
use App\Enums\TicketSource;
use App\Enums\TicketStatus;
use App\Enums\TicketType;
use App\Models\Client;
use App\Models\Project;
use App\Models\TicketSlaPolicy;
use App\Models\User;
use App\Services\Tickets\TicketService;
use App\Services\Tickets\TicketSlaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

function createTicketTestUser(
    array $permissions = []
): User {
    $user = User::factory()->create([
        'status' => 'active',
        'email_verified_at' => now(),
    ]);

    $role = Role::findOrCreate(
        'ticket-test-role'
    );

    foreach ($permissions as $permission) {
        Permission::findOrCreate(
            $permission
        );
    }

    $role->syncPermissions(
        $permissions
    );

    $user->assignRole($role);

    return $user;
}

function createTicketTestProject(): Project
{
    $client = Client::create([
        'name' => 'Ticket Test Client',
        'client_type' => 'business',
        'status' =>
            ClientStatus::Active->value,
    ]);

    return Project::create([
        'client_id' => $client->id,
        'name' => 'Ticket Test Project',
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

function seedTicketTestSla(): void
{
    foreach (
        TicketPriority::cases()
        as $priority
    ) {
        TicketSlaPolicy::create([
            'priority' => $priority->value,
            'first_response_minutes' => 60,
            'resolution_minutes' => 240,
            'warning_before_minutes' => 15,
            'level_two_after_minutes' => 30,
            'level_three_after_minutes' => 60,
            'is_active' => true,
        ]);
    }
}

function ticketTestData(): array
{
    return [
        'type' => TicketType::Bug->value,

        'source' =>
            TicketSource::Internal->value,

        'priority' =>
            TicketPriority::High->value,

        'subject' =>
            'Homepage form is not submitting',

        'description' =>
            'The contact form returns an error.',

        'assigned_to' => null,
    ];
}

it('creates ticket with response and resolution deadlines', function () {
    Carbon::setTestNow(
        '2026-07-24 10:00:00'
    );

    seedTicketTestSla();

    $project = createTicketTestProject();
    $user = createTicketTestUser();

    $ticket = app(
        TicketService::class
    )->create(
        $project,
        $user,
        ticketTestData()
    );

    expect($ticket->ticket_number)
        ->toStartWith('TKT-2026-')
        ->and(
            $ticket
                ->first_response_due_at
                ->format('Y-m-d H:i')
        )
        ->toBe('2026-07-24 11:00')
        ->and(
            $ticket
                ->resolution_due_at
                ->format('Y-m-d H:i')
        )
        ->toBe('2026-07-24 14:00');
});

it('pauses and resumes ticket sla', function () {
    Carbon::setTestNow(
        '2026-07-24 10:00:00'
    );

    seedTicketTestSla();

    $project = createTicketTestProject();
    $user = createTicketTestUser();

    $service = app(
        TicketService::class
    );

    $ticket = $service->create(
        $project,
        $user,
        ticketTestData()
    );

    $service->transition(
        $ticket,
        $user,
        TicketStatus::PendingClient,
        'Waiting for client credentials.'
    );

    Carbon::setTestNow(
        '2026-07-24 12:00:00'
    );

    $ticket = $service->transition(
        $ticket->fresh(),
        $user,
        TicketStatus::InProgress,
        'Credentials received.'
    );

    expect($ticket->sla_paused_minutes)
        ->toBe(120)
        ->and(
            $ticket
                ->first_response_due_at
                ->format('H:i')
        )
        ->toBe('13:00')
        ->and(
            $ticket
                ->resolution_due_at
                ->format('H:i')
        )
        ->toBe('16:00');
});

it('records first response from ticket discussion', function () {
    seedTicketTestSla();

    $project = createTicketTestProject();

    $user = createTicketTestUser([
        'tickets.respond',
    ]);

    $service = app(
        TicketService::class
    );

    $ticket = $service->create(
        $project,
        $user,
        ticketTestData()
    );

    $service->addComment(
        $ticket,
        $user,
        [
            'comment_type' =>
                TicketCommentType::Discussion
                    ->value,

            'message' =>
                'I am investigating this issue.',
        ]
    );

    $ticket->refresh();

    expect($ticket->first_responded_at)
        ->not->toBeNull()
        ->and($ticket->first_responded_by)
        ->toBe($user->id);
});

it('resolves and reopens a ticket with a new sla cycle', function () {
    seedTicketTestSla();

    $project = createTicketTestProject();
    $user = createTicketTestUser();

    $service = app(
        TicketService::class
    );

    $ticket = $service->create(
        $project,
        $user,
        ticketTestData()
    );

    $ticket = $service->resolve(
        $ticket,
        $user,
        [
            'resolution_summary' =>
                'The form API endpoint was corrected.',

            'root_cause' =>
                'Incorrect production API URL.',

            'preventive_action' =>
                'Add environment validation.',
        ]
    );

    expect($ticket->status)
        ->toBe(TicketStatus::Resolved);

    $ticket = $service->reopen(
        $ticket,
        $user,
        'The same issue appeared again after deployment.'
    );

    expect($ticket->status)
        ->toBe(TicketStatus::Reopened)
        ->and($ticket->reopen_count)
        ->toBe(1)
        ->and($ticket->first_responded_at)
        ->toBeNull()
        ->and($ticket->escalation_level)
        ->toBe(0);
});

it('escalates tickets based on sla deadlines', function () {
    Carbon::setTestNow(
        '2026-07-24 10:00:00'
    );

    seedTicketTestSla();

    $project = createTicketTestProject();
    $user = createTicketTestUser();

    $ticket = app(
        TicketService::class
    )->create(
        $project,
        $user,
        ticketTestData()
    );

    Carbon::setTestNow(
        '2026-07-24 12:10:00'
    );

    $level = app(
        TicketSlaService::class
    )->checkAndEscalate(
        $ticket
    );

    expect($level)
        ->toBeGreaterThanOrEqual(2)
        ->and(
            $ticket
                ->fresh()
                ->escalation_level
        )
        ->toBeGreaterThanOrEqual(2)
        ->and(
            $ticket
                ->escalations()
                ->count()
        )
        ->toBeGreaterThanOrEqual(2);
});