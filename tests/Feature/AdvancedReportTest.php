<?php

use App\Enums\ClientStatus;
use App\Enums\PaymentKind;
use App\Enums\PaymentMode;
use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Enums\ProjectPriority;
use App\Enums\ProjectStatus;
use App\Enums\ReportType;
use App\Models\Client;
use App\Models\Payment;
use App\Models\Project;
use App\Models\User;
use App\Services\Reports\CollectionReportService;
use App\Services\Reports\ProjectAnalyticsService;
use App\Support\Reports\ReportFilters;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    app(PermissionRegistrar::class)
        ->forgetCachedPermissions();
});

afterEach(function (): void {
    Carbon::setTestNow();

    app(PermissionRegistrar::class)
        ->forgetCachedPermissions();
});

function createPhaseNineUser(
    array $permissions = []
): User {
    $user = User::factory()->create([
        'status' => 'active',
        'email_verified_at' => now(),
    ]);

    $role = Role::findOrCreate(
        'phase-nine-test-role',
        'web'
    );

    foreach ($permissions as $permission) {
        Permission::findOrCreate(
            $permission,
            'web'
        );
    }

    $role->syncPermissions(
        $permissions
    );

    $user->assignRole($role);

    return $user;
}

function createPhaseNineProject(
    array $attributes = []
): Project {
    $client = Client::create([
        'name' =>
            'Report Test Client',

        'client_type' =>
            'business',

        'status' =>
            ClientStatus::Active->value,
    ]);

    $projectAttributes = [
        'client_id' => $client->id,
        'name' => 'Report Test Project',

        'project_price' =>
            100000,

        'estimated_cost' =>
            30000,

        'project_expense_amount' =>
            25000,

        'net_received_amount' =>
            60000,

        'pending_amount' =>
            40000,

        'collection_percentage' =>
            60,

        'start_date' =>
            '2026-04-01',

        'expected_delivery_date' =>
            '2026-04-20',

        'collection_due_date' =>
            '2026-04-20',

        'status' =>
            ProjectStatus::Completed->value,

        'priority' =>
            ProjectPriority::Medium->value,

        'official_progress' => 100,
        'internal_progress' => 100,
        'maximum_duration_days' => 18,

        ...$attributes,
    ];

    $project = Project::create(
        $projectAttributes
    );

    /*
    |--------------------------------------------------------------------------
    | Preserve Explicit Reporting Snapshot Values
    |--------------------------------------------------------------------------
    |
    | Project financial summary fields are normally recalculated by the
    | application's model events and financial services. This test helper
    | intentionally creates a known reporting snapshot, so the explicit
    | aggregate values must be restored without triggering another
    | recalculation cycle.
    |
    */

    $project->forceFill([
        'project_expense_amount' =>
            $projectAttributes[
                'project_expense_amount'
            ],

        'net_received_amount' =>
            $projectAttributes[
                'net_received_amount'
            ],

        'pending_amount' =>
            $projectAttributes[
                'pending_amount'
            ],

        'collection_percentage' =>
            $projectAttributes[
                'collection_percentage'
            ],
    ])->saveQuietly();

    return $project->fresh();
}

it('uses the current Indian financial year by default', function () {
    Carbon::setTestNow(
        '2026-07-25 10:00:00'
    );

    $filters =
        ReportFilters::fromArray([]);

    expect(
        $filters->from->toDateString()
    )->toBe('2026-04-01')
        ->and(
            $filters->to->toDateString()
        )
        ->toBe('2026-07-25');
});

it('calculates project contract profitability', function () {
    createPhaseNineProject();

    $filters =
        ReportFilters::fromArray([
            'date_from' =>
                '2026-04-01',

            'date_to' =>
                '2026-04-30',
        ]);

    $summary = app(
        ProjectAnalyticsService::class
    )->summary($filters);

    expect(
        $summary['contract_value']
    )->toBe(100000.0)
        ->and(
            $summary['project_costs']
        )
        ->toBe(25000.0)
        ->and(
            $summary['contract_profit']
        )
        ->toBe(75000.0)
        ->and(
            $summary['contract_margin']
        )
        ->toBe(75.0);
});

it('calculates net collections after refunds', function () {
    $project =
        createPhaseNineProject();

    Payment::create([
        'payment_number' =>
            'PAY-2026-00001',

        'project_id' =>
            $project->id,

        'client_id' =>
            $project->client_id,

        'kind' =>
            PaymentKind::Receipt->value,

        'payment_type' =>
            PaymentType::Partial->value,

        'payment_mode' =>
            PaymentMode::BankTransfer->value,

        'status' =>
            PaymentStatus::Cleared->value,

        'amount' => 30000,

        'payment_date' =>
            '2026-04-10',

        'cleared_at' =>
            '2026-04-10',
    ]);

    Payment::create([
        'payment_number' =>
            'REF-2026-00002',

        'project_id' =>
            $project->id,

        'client_id' =>
            $project->client_id,

        'kind' =>
            PaymentKind::Refund->value,

        'payment_type' =>
            PaymentType::Refund->value,

        'payment_mode' =>
            PaymentMode::BankTransfer->value,

        'status' =>
            PaymentStatus::Cleared->value,

        'amount' => 5000,

        'payment_date' =>
            '2026-04-15',

        'cleared_at' =>
            '2026-04-15',
    ]);

    $filters =
        ReportFilters::fromArray([
            'date_from' =>
                '2026-04-01',

            'date_to' =>
                '2026-04-30',
        ]);

    $summary = app(
        CollectionReportService::class
    )->summary($filters);

    expect(
        $summary[
            'period_receipts'
        ]
    )->toBe(30000.0)
        ->and(
            $summary[
                'period_refunds'
            ]
        )
        ->toBe(5000.0)
        ->and(
            $summary[
                'period_net_collections'
            ]
        )
        ->toBe(25000.0);
});

it('allows authorised users to export reports', function () {
    createPhaseNineProject();

    $user = createPhaseNineUser([
        'reports.view',
        'reports.export',
    ]);

    $response = $this
        ->actingAs($user)
        ->post(
            route('reports.export'),
            [
                'report_type' =>
                    ReportType::Projects->value,

                'date_from' =>
                    '2026-04-01',

                'date_to' =>
                    '2026-04-30',
            ]
        );

    $response->assertSuccessful();

    expect(
        $response->headers->get(
            'content-type'
        )
    )->toContain('text/csv');
});

it('blocks financial reports without financial permission', function () {
    $user = createPhaseNineUser([
        'reports.view',
    ]);

    $this
        ->actingAs($user)
        ->get(
            route(
                'reports.collections'
            )
        )
        ->assertForbidden();
});

