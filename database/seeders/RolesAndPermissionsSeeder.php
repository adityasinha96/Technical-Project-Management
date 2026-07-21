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
        /*
        |--------------------------------------------------------------------------
        | Clear Permission Cache
        |--------------------------------------------------------------------------
        */

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        /*
        |--------------------------------------------------------------------------
        | Application Permissions
        |--------------------------------------------------------------------------
        */

        $permissions = [
            'dashboard.view',

            /*
            |--------------------------------------------------------------------------
            | Users
            |--------------------------------------------------------------------------
            */

            'users.view',
            'users.create',
            'users.update',
            'users.delete',

            /*
            |--------------------------------------------------------------------------
            | Clients
            |--------------------------------------------------------------------------
            */

            'clients.view',
            'clients.create',
            'clients.update',
            'clients.delete',

            /*
            |--------------------------------------------------------------------------
            | Projects
            |--------------------------------------------------------------------------
            */

            'projects.view',
            'projects.create',
            'projects.update',
            'projects.delete',
            'projects.assign-team',
            'projects.manage-files',

            /*
            |--------------------------------------------------------------------------
            | Task Templates
            |--------------------------------------------------------------------------
            */

            'templates.view',
            'templates.manage',

            /*
            |--------------------------------------------------------------------------
            | Tasks
            |--------------------------------------------------------------------------
            */

            'tasks.view',
            'tasks.create',
            'tasks.update',
            'tasks.delete',

            /*
            |--------------------------------------------------------------------------
            | Approvals
            |--------------------------------------------------------------------------
            */

            'approvals.view',
            'approvals.manage',

            /*
            |--------------------------------------------------------------------------
            | Notes
            |--------------------------------------------------------------------------
            */

            'notes.view',
            'notes.create',
            'notes.update',
            'notes.delete',

            /*
            |--------------------------------------------------------------------------
            | Payments
            |--------------------------------------------------------------------------
            */

            'payments.view',
            'payments.create',
            'payments.update',
            'payments.delete',

            /*
            |--------------------------------------------------------------------------
            | Expenses
            |--------------------------------------------------------------------------
            */

            'expenses.view',
            'expenses.create',
            'expenses.update',
            'expenses.delete',

            /*
            |--------------------------------------------------------------------------
            | Tickets
            |--------------------------------------------------------------------------
            */

            'tickets.view',
            'tickets.create',
            'tickets.update',
            'tickets.assign',
            'tickets.close',
            'tickets.delete',

            /*
            |--------------------------------------------------------------------------
            | Reports
            |--------------------------------------------------------------------------
            */

            'reports.view',
            'reports.export',

            /*
            |--------------------------------------------------------------------------
            | Settings
            |--------------------------------------------------------------------------
            */

            'settings.manage',
        ];

        /*
        |--------------------------------------------------------------------------
        | Create Permissions
        |--------------------------------------------------------------------------
        */

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Create Roles
        |--------------------------------------------------------------------------
        */

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

        /*
        |--------------------------------------------------------------------------
        | Super Administrator Permissions
        |--------------------------------------------------------------------------
        */

        $superAdmin->syncPermissions($permissions);

        /*
        |--------------------------------------------------------------------------
        | Project Manager Permissions
        |--------------------------------------------------------------------------
        */

        $projectManager->syncPermissions([
            'dashboard.view',

            'clients.view',
            'clients.create',
            'clients.update',

            'projects.view',
            'projects.create',
            'projects.update',
            'projects.assign-team',
            'projects.manage-files',

            'templates.view',
            'templates.manage',

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

        /*
        |--------------------------------------------------------------------------
        | Accounts Permissions
        |--------------------------------------------------------------------------
        */

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

        /*
        |--------------------------------------------------------------------------
        | Team Member Permissions
        |--------------------------------------------------------------------------
        */

        $teamMember->syncPermissions([
            'dashboard.view',

            'projects.view',
            'projects.manage-files',

            'templates.view',

            'tasks.view',
            'tasks.create',
            'tasks.update',

            'approvals.view',

            'notes.view',
            'notes.create',
            'notes.update',

            'tickets.view',
            'tickets.create',
            'tickets.update',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Create or Update Super Administrator
        |--------------------------------------------------------------------------
        */

        $adminName = config('admin.name');
        $adminEmail = config('admin.email');
        $adminPassword = config('admin.password');

        if (!$adminName || !$adminEmail || !$adminPassword) {
            throw new RuntimeException(
                'Set SUPER_ADMIN_NAME, SUPER_ADMIN_EMAIL and SUPER_ADMIN_PASSWORD in the .env file.'
            );
        }

        $user = User::updateOrCreate(
            [
                'email' => $adminEmail,
            ],
            [
                'name' => $adminName,
                'password' => Hash::make($adminPassword),
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        $user->syncRoles([
            $superAdmin,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Clear Permission Cache Again
        |--------------------------------------------------------------------------
        */

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}