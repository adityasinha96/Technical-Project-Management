<?php

use App\Enums\ApprovalStage;
use App\Enums\ApprovalStatus;
use App\Enums\ClientApprovalDecision;
use App\Enums\ClientProjectRole;
use App\Enums\ClientStatus;
use App\Enums\ClientUserStatus;
use App\Enums\ProjectPriority;
use App\Enums\ProjectStatus;
use App\Enums\TicketPriority;
use App\Enums\TicketType;
use App\Models\Client;
use App\Models\ClientProjectAccess;
use App\Models\ClientUser;
use App\Models\Project;
use App\Models\ProjectApproval;
use App\Models\ProjectFile;
use App\Models\TicketSlaPolicy;
use App\Models\User;
use App\Services\ClientPortal\ClientApprovalService;
use App\Services\ClientPortal\ClientTicketService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function createPortalClient(): Client
{
    return Client::create([
        'name' =>
            'Portal Test Client',

        'client_type' =>
            'business',

        'status' =>
            ClientStatus::Active->value,
    ]);
}

function createPortalProject(
    Client $client,
    array $attributes = []
): Project {
    return Project::create([
        'client_id' =>
            $client->id,

        'name' =>
            'Portal Test Project',

        'project_price' =>
            100000,

        'estimated_cost' =>
            25000,

        'start_date' =>
            today(),

        'expected_delivery_date' =>
            today()->addDays(18),

        'status' =>
            ProjectStatus::NewProject->value,

        'priority' =>
            ProjectPriority::Medium->value,

        'official_progress' =>
            0,

        'internal_progress' =>
            20,

        'maximum_duration_days' =>
            18,

        'client_portal_enabled' =>
            true,

        ...$attributes,
    ]);
}

function createPortalUser(
    Client $client,
    Project $project,
    array $access = []
): ClientUser {
    $clientUser =
        ClientUser::create([
            'client_id' =>
                $client->id,

            'name' =>
                'Client Contact',

            'email' =>
                'client@example.test',

            'password' =>
                'Secure@Password123',

            'status' =>
                ClientUserStatus::Active->value,

            'email_verified_at' =>
                now(),
        ]);

    ClientProjectAccess::create([
        'client_user_id' =>
            $clientUser->id,

        'project_id' =>
            $project->id,

        'role' =>
            ClientProjectRole::PrimaryContact
                ->value,

        'can_view_project' =>
            true,

        'can_view_financials' =>
            true,

        'can_approve' =>
            true,

        'can_submit_tickets' =>
            true,

        'can_view_files' =>
            true,

        'can_communicate' =>
            true,

        'is_active' =>
            true,

        'granted_at' =>
            now(),

        ...$access,
    ]);

    return $clientUser;
}

function seedPortalTicketSla(): void
{
    foreach (
        TicketPriority::cases()
        as $priority
    ) {
        TicketSlaPolicy::create([
            'priority' =>
                $priority->value,

            'first_response_minutes' =>
                60,

            'resolution_minutes' =>
                240,

            'warning_before_minutes' =>
                15,

            'level_two_after_minutes' =>
                30,

            'level_three_after_minutes' =>
                60,

            'is_active' =>
                true,
        ]);
    }
}

it('keeps staff and client authentication guards separate', function () {
    $client =
        createPortalClient();

    $project =
        createPortalProject(
            $client
        );

    $clientUser =
        createPortalUser(
            $client,
            $project
        );

    $staffUser =
        User::factory()->create([
            'status' =>
                'active',
        ]);

    $this
        ->actingAs(
            $clientUser,
            'client'
        )
        ->get(
            route(
                'client.dashboard'
            )
        )
        ->assertSuccessful();

    $this
        ->actingAs(
            $clientUser,
            'client'
        )
        ->get(
            route(
                'dashboard'
            )
        )
        ->assertRedirect();

    $this
        ->actingAs(
            $staffUser
        )
        ->get(
            route(
                'client.dashboard'
            )
        )
        ->assertRedirect(
            route(
                'client.login'
            )
        );
});

it('prevents access to another client project', function () {
    $clientOne =
        createPortalClient();

    $projectOne =
        createPortalProject(
            $clientOne
        );

    $clientUser =
        createPortalUser(
            $clientOne,
            $projectOne
        );

    $clientTwo =
        Client::create([
            'name' =>
                'Second Client',

            'client_type' =>
                'business',

            'status' =>
                ClientStatus::Active->value,
        ]);

    $projectTwo =
        createPortalProject(
            $clientTwo,
            [
                'name' =>
                    'Restricted Project',
            ]
        );

    $this
        ->actingAs(
            $clientUser,
            'client'
        )
        ->get(
            route(
                'client.projects.show',
                $projectTwo
            )
        )
        ->assertForbidden();
});

it('enforces financial visibility permission', function () {
    $client =
        createPortalClient();

    $project =
        createPortalProject(
            $client
        );

    $clientUser =
        createPortalUser(
            $client,
            $project,
            [
                'can_view_financials' =>
                    false,
            ]
        );

    $this
        ->actingAs(
            $clientUser,
            'client'
        )
        ->get(
            route(
                'client.payments.index',
                $project
            )
        )
        ->assertForbidden();
});

it('allows an authorised client to submit a ticket', function () {
    $client =
        createPortalClient();

    $project =
        createPortalProject(
            $client
        );

    $clientUser =
        createPortalUser(
            $client,
            $project
        );

    seedPortalTicketSla();

    $ticket = app(
        ClientTicketService::class
    )->create(
        project:
            $project,

        clientUser:
            $clientUser,

        data: [
            'type' =>
                TicketType::Bug->value,

            'priority' =>
                TicketPriority::High->value,

            'subject' =>
                'Contact form is not working',

            'description' =>
                'The production contact form returns an error.',
        ]
    );

    expect(
        $ticket->client_visible
    )
        ->toBeTrue()
        ->and(
            $ticket
                ->submitted_by_client_user_id
        )
        ->toBe(
            $clientUser->id
        )
        ->and(
            $ticket->ticket_number
        )
        ->toStartWith(
            'TKT-'
        );
});

it('allows an authorised client to approve submitted work', function () {
    $client =
        createPortalClient();

    $project =
        createPortalProject(
            $client
        );

    $clientUser =
        createPortalUser(
            $client,
            $project
        );

    $approval =
        ProjectApproval::create([
            'project_id' =>
                $project->id,

            'stage' =>
                ApprovalStage::Frontend->value,

            'submission_number' =>
                1,

            'status' =>
                ApprovalStatus::Submitted
                    ->value,

            'submitted_at' =>
                now(),

            'is_client_visible' =>
                true,

            'submitted_to_client_at' =>
                now(),

            'client_decision' =>
                ClientApprovalDecision::Pending
                    ->value,
        ]);

    $approval = app(
        ClientApprovalService::class
    )->decide(
        approval:
            $approval,

        clientUser:
            $clientUser,

        decision:
            ClientApprovalDecision::Approved,

        feedback:
            'Frontend design approved.'
    );

    expect(
        $approval->client_decision
    )
        ->toBe(
            ClientApprovalDecision::Approved
        )
        ->and(
            $approval->client_decided_by
        )
        ->toBe(
            $clientUser->id
        );
});

it('blocks unshared project files', function () {
    Storage::fake(
        'local'
    );

    $client =
        createPortalClient();

    $project =
        createPortalProject(
            $client
        );

    $clientUser =
        createPortalUser(
            $client,
            $project
        );

    Storage::disk(
        'local'
    )->put(
        'projects/test/private.pdf',
        'private'
    );

    $file =
        ProjectFile::create([
            'project_id' =>
                $project->id,

            'category' =>
                'general',

            'original_name' =>
                'private.pdf',

            'stored_name' =>
                'private.pdf',

            'path' =>
                'projects/test/private.pdf',

            'disk' =>
                'local',

            'mime_type' =>
                'application/pdf',

            'size' =>
                7,

            'client_visible' =>
                false,
        ]);

    $this
        ->actingAs(
            $clientUser,
            'client'
        )
        ->get(
            route(
                'client.files.download',
                [
                    $project,
                    $file,
                ]
            )
        )
        ->assertNotFound();
});