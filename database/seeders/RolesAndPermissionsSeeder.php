<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'dashboard.view',

            'users.view',
            'users.create',
            'users.update',
            'users.delete',

            'clients.view',
            'clients.create',
            'clients.update',
            'clients.delete',

            'projects.view',
            'projects.create',
            'projects.update',
            'projects.delete',

            'tasks.view',
            'tasks.create',
            'tasks.update',
            'tasks.delete',

            'approvals.view',
            'approvals.manage',

            'notes.view',
            'notes.create',
            'notes.update',
            'notes.delete',

            'payments.view',
            'payments.create',
            'payments.update',
            'payments.delete',

            'expenses.view',
            'expenses.create',
            'expenses.update',
            'expenses.delete',

            'tickets.view',
            'tickets.create',
            'tickets.update',
            'tickets.assign',
            'tickets.close',
            'tickets.delete',

            'reports.view',
            'reports.export',

            'settings.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $superAdmin = Role::firstOrCreate([
            'name' => 'super-admin',
            'guard_name' => 'web',
        ]);

        $projectManager = Role::firstOrCreate([
            'name' => 'project-manager',
            'guard_name' => 'web',
        ]);

        $accounts = Role::firstOrCreate([
            'name' => 'accounts',
            'guard_name' => 'web',
        ]);

        $teamMember = Role::firstOrCreate([
            'name' => 'team-member',
            'guard_name' => 'web',
        ]);

        $superAdmin->syncPermissions($permissions);

        $projectManager->syncPermissions([
            'dashboard.view',

            'clients.view',
            'clients.create',
            'clients.update',

            'projects.view',
            'projects.create',
            'projects.update',

            'tasks.view',
            'tasks.create',
            'tasks.update',
            'tasks.delete',

            'approvals.view',
            'approvals.manage',

            'notes.view',
            'notes.create',
            'notes.update',
            'notes.delete',

            'payments.view',
            'expenses.view',

            'tickets.view',
            'tickets.create',
            'tickets.update',
            'tickets.assign',
            'tickets.close',

            'reports.view',
        ]);

        $accounts->syncPermissions([
            'dashboard.view',

            'clients.view',
            'projects.view',

            'payments.view',
            'payments.create',
            'payments.update',
            'payments.delete',

            'expenses.view',
            'expenses.create',
            'expenses.update',
            'expenses.delete',

            'reports.view',
            'reports.export',
        ]);

        $teamMember->syncPermissions([
            'dashboard.view',
            'projects.view',

            'tasks.view',
            'tasks.update',

            'notes.view',
            'notes.create',
            'notes.update',

            'tickets.view',
            'tickets.create',
            'tickets.update',
        ]);

        $adminName = config('admin.name');
        $adminEmail = config('admin.email');
        $adminPassword = config('admin.password');

        if (!$adminName || !$adminEmail || !$adminPassword) {
            throw new RuntimeException(
                'Set SUPER_ADMIN_NAME, SUPER_ADMIN_EMAIL and SUPER_ADMIN_PASSWORD in the .env file.'
            );
        }

        $user = User::updateOrCreate(
            ['email' => $adminEmail],
            [
                'name' => $adminName,
                'password' => Hash::make($adminPassword),
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        $user->syncRoles([$superAdmin]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}