<?php

namespace App\Services\Security;

use App\Enums\AuditCategory;
use App\Enums\AuditSeverity;
use App\Enums\PermissionChangeAction;
use App\Enums\SecurityIncidentType;
use App\Models\PermissionChangeLog;
use App\Models\User;
use App\Services\Audit\AuditLogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class PermissionAdministrationService
{
    public function __construct(
        private readonly AuditLogService $audit,
        private readonly SecurityIncidentService $incidents
    ) {
    }

    public function syncUserRoles(
        User $targetUser,
        array $roleNames,
        User $performedBy
    ): void {
        DB::transaction(
            function () use (
                $targetUser,
                $roleNames,
                $performedBy
            ): void {
                $before =
                    $targetUser->roles()
                        ->pluck('name')
                        ->sort()
                        ->values()
                        ->all();

                $targetUser->syncRoles(
                    $roleNames
                );

                $after =
                    $targetUser
                        ->fresh()
                        ->roles()
                        ->pluck('name')
                        ->sort()
                        ->values()
                        ->all();

                if ($before === $after) {
                    return;
                }

                $change =
                    PermissionChangeLog::create([
                        'change_uuid' =>
                            (string) Str::uuid(),

                        'action' =>
                            PermissionChangeAction::RolesSynchronized
                                ->value,

                        'target_user_id' =>
                            $targetUser->id,

                        'target_user_name' =>
                            $targetUser->name,

                        'performed_by' =>
                            $performedBy->id,

                        'before_values' => [
                            'roles' => $before,
                        ],

                        'after_values' => [
                            'roles' => $after,
                        ],

                        'ip_address' =>
                            request()->ip(),

                        'user_agent' =>
                            request()->userAgent(),

                        'occurred_at' =>
                            now(),
                    ]);

                $severity =
                    in_array(
                        'super-admin',
                        $after,
                        true
                    )
                    && !in_array(
                        'super-admin',
                        $before,
                        true
                    )
                        ? AuditSeverity::Critical
                        : AuditSeverity::High;

                $this->audit->record(
                    eventType:
                        'authorization.user_roles_synchronized',

                    category:
                        AuditCategory::Authorization,

                    severity:
                        $severity,

                    auditable:
                        $targetUser,

                    oldValues: [
                        'roles' => $before,
                    ],

                    newValues: [
                        'roles' => $after,
                    ],

                    metadata: [
                        'permission_change_uuid' =>
                            $change->change_uuid,
                    ],

                    actor:
                        $performedBy,

                    guard: 'web'
                );

                if (
                    in_array(
                        'super-admin',
                        $after,
                        true
                    )
                    && !in_array(
                        'super-admin',
                        $before,
                        true
                    )
                ) {
                    $this->incidents->raise(
                        type:
                            SecurityIncidentType::PermissionEscalation,

                        severity:
                            AuditSeverity::Critical,

                        title:
                            'Super Admin role assigned',

                        description:
                            "{$performedBy->name} assigned the Super Admin role to {$targetUser->name}.",

                        fingerprintSource:
                            implode('|', [
                                'super-admin',
                                $targetUser->id,
                                now()->format(
                                    'Y-m-d-H'
                                ),
                            ]),

                        subject:
                            $targetUser,

                        metadata: [
                            'performed_by' =>
                                $performedBy->id,

                            'before_roles' =>
                                $before,

                            'after_roles' =>
                                $after,
                        ]
                    );
                }
            }
        );
    }

    public function syncRolePermissions(
        Role $role,
        array $permissionNames,
        User $performedBy
    ): void {
        DB::transaction(
            function () use (
                $role,
                $permissionNames,
                $performedBy
            ): void {
                $before =
                    $role->permissions()
                        ->pluck('name')
                        ->sort()
                        ->values()
                        ->all();

                $role->syncPermissions(
                    $permissionNames
                );

                $after =
                    $role
                        ->fresh()
                        ->permissions()
                        ->pluck('name')
                        ->sort()
                        ->values()
                        ->all();

                if ($before === $after) {
                    return;
                }

                $change =
                    PermissionChangeLog::create([
                        'change_uuid' =>
                            (string) Str::uuid(),

                        'action' =>
                            PermissionChangeAction::RolePermissionsSynchronized
                                ->value,

                        'role_id' =>
                            $role->id,

                        'role_name' =>
                            $role->name,

                        'performed_by' =>
                            $performedBy->id,

                        'before_values' => [
                            'permissions' =>
                                $before,
                        ],

                        'after_values' => [
                            'permissions' =>
                                $after,
                        ],

                        'ip_address' =>
                            request()->ip(),

                        'user_agent' =>
                            request()->userAgent(),

                        'occurred_at' =>
                            now(),
                    ]);

                $this->audit->record(
                    eventType:
                        'authorization.role_permissions_synchronized',

                    category:
                        AuditCategory::Authorization,

                    severity:
                        AuditSeverity::High,

                    auditable:
                        $role,

                    oldValues: [
                        'permissions' =>
                            $before,
                    ],

                    newValues: [
                        'permissions' =>
                            $after,
                    ],

                    metadata: [
                        'permission_change_uuid' =>
                            $change->change_uuid,

                        'role_name' =>
                            $role->name,
                    ],

                    actor:
                        $performedBy,

                    guard: 'web'
                );
            }
        );
    }
}