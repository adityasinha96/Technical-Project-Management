<?php

use App\Enums\AuditCategory;
use App\Enums\AuditSeverity;
use App\Enums\PermissionChangeAction;
use App\Enums\SecurityIncidentStatus;
use App\Models\AuditLog;
use App\Models\PermissionChangeLog;
use App\Models\SecurityIncident;
use App\Models\SecuritySession;
use App\Models\User;
use App\Services\Audit\AuditIntegrityService;
use App\Services\Audit\AuditLogService;
use App\Services\Security\PermissionAdministrationService;
use App\Services\Security\SecuritySessionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    config()->set(
        'security.audit.hmac_key',
        'base64:'
        . base64_encode(
            str_repeat('a', 32)
        )
    );
});

function createSecurityUser(
    array $permissions = []
): User {
    $user = User::factory()->create([
        'status' => 'active',
    ]);

    $role = Role::findOrCreate(
        'security-test-role'
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

it('creates chained immutable audit logs', function () {
    $service =
        app(
            AuditLogService::class
        );

    $first = $service->record(
        eventType:
            'test.first',

        category:
            AuditCategory::System,

        severity:
            AuditSeverity::Info
    );

    $second = $service->record(
        eventType:
            'test.second',

        category:
            AuditCategory::System,

        severity:
            AuditSeverity::Warning
    );

    expect($first->sequence)
        ->toBe(1)
        ->and($second->sequence)
        ->toBe(2)
        ->and($second->previous_hash)
        ->toBe($first->entry_hash);

    $result = app(
        AuditIntegrityService::class
    )->verify();


    expect(
        $result['valid']
    )->toBeTrue();
});

it('detects audit log tampering', function () {
    $log = app(
        AuditLogService::class
    )->record(
        eventType:
            'test.original',

        category:
            AuditCategory::System
    );

    /*
     * Raw SQL intentionally bypasses
     * the immutable model protection.
     */
    DB::table('audit_logs')
        ->where('id', $log->id)
        ->update([
            'event_type' =>
                'test.tampered',
        ]);

    $result = app(
        AuditIntegrityService::class
    )->verify();

    expect($result['valid'])
        ->toBeFalse();
});

it('prevents normal audit log updates', function () {
    $log = app(
        AuditLogService::class
    )->record(
        eventType:
            'test.immutable'
    );

    expect(
        fn () => $log->update([
            'event_type' =>
                'test.changed',
        ])
    )->toThrow(
        LogicException::class
    );
});

it('records role changes', function () {
    $actor =
        createSecurityUser();

    $target =
        createSecurityUser();

    Role::findOrCreate(
        'project-manager'
    );

    app(
        PermissionAdministrationService::class
    )->syncUserRoles(
        targetUser: $target,
        roleNames: [
            'project-manager',
        ],
        performedBy: $actor
    );

    expect(
        $target->fresh()->hasRole(
            'project-manager'
        )
    )->toBeTrue();

    $change =
        PermissionChangeLog::query()
            ->latest()
            ->first();

    expect($change->action)
        ->toBe(
            PermissionChangeAction::RolesSynchronized
        )
        ->and($change->performed_by)
        ->toBe($actor->id);
});

it('revokes an active security session', function () {
    $administrator =
        createSecurityUser();

    $target =
        createSecurityUser();

    $session =
        SecuritySession::create([
            'session_uuid' =>
                (string)
                Str::uuid(),

            'guard' => 'web',

            'actor_type' =>
                $target->getMorphClass(),

            'actor_id' =>
                $target->id,

            'session_id_hash' =>
                hash(
                    'sha256',
                    'test-session-id'
                ),

            'session_id' =>
                'test-session-id',

            'logged_in_at' =>
                now(),

            'last_seen_at' =>
                now(),
        ]);

    app(
        SecuritySessionService::class
    )->revoke(
        securitySession:
            $session,

        revokedBy:
            $administrator,

        reason:
            'Security test'
    );

    expect(
        $session->fresh()->revoked_at
    )->not->toBeNull();
});

