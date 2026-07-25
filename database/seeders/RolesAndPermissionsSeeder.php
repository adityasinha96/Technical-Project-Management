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

        app(PermissionRegistrar::class)
            ->forgetCachedPermissions();

        /*
        |--------------------------------------------------------------------------
        | Application Permissions
        |--------------------------------------------------------------------------
        */

        $permissions = [
            /*
            |--------------------------------------------------------------------------
            | Dashboard
            |--------------------------------------------------------------------------
            */

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
            | Project Templates
            |--------------------------------------------------------------------------
            */

            'templates.view',
            'templates.manage',

            /*
            |--------------------------------------------------------------------------
            | Project Tasks
            |--------------------------------------------------------------------------
            */

            'tasks.view',
            'tasks.create',
            'tasks.update',
            'tasks.delete',

            /*
            |--------------------------------------------------------------------------
            | Project Approvals
            |--------------------------------------------------------------------------
            */

            'approvals.view',
            'approvals.manage',

            /*
            |--------------------------------------------------------------------------
            | Project Notes
            |--------------------------------------------------------------------------
            */

            'notes.view',
            'notes.create',
            'notes.update',
            'notes.delete',
            'notes.pin',
            'notes.manage',
            'notes.view-sensitive',

            /*
            |--------------------------------------------------------------------------
            | Project Work Logs
            |--------------------------------------------------------------------------
            */

            'work-logs.view',
            'work-logs.create',
            'work-logs.update',
            'work-logs.delete',
            'work-logs.manage',

            /*
            |--------------------------------------------------------------------------
            | Project Activities
            |--------------------------------------------------------------------------
            */

            'activities.view',
            'activities.view-sensitive',

            /*
            |--------------------------------------------------------------------------
            | Project Attachments
            |--------------------------------------------------------------------------
            */

            'attachments.view',
            'attachments.upload',
            'attachments.delete',

            /*
            |--------------------------------------------------------------------------
            | Project Payments
            |--------------------------------------------------------------------------
            */

            'payments.view',
            'payments.create',
            'payments.update',
            'payments.delete',

            /*
            |--------------------------------------------------------------------------
            | Payment Follow-ups
            |--------------------------------------------------------------------------
            */

            'payment-followups.view',
            'payment-followups.create',
            'payment-followups.update',
            'payment-followups.delete',

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
            | Expense Categories
            |--------------------------------------------------------------------------
            */

            'expense-categories.manage',

            /*
            |--------------------------------------------------------------------------
            | Tickets
            |--------------------------------------------------------------------------
            */

            'tickets.view',
            'tickets.create',
            'tickets.update',
            'tickets.assign',
            'tickets.comment',
            'tickets.respond',
            'tickets.resolve',
            'tickets.reopen',
            'tickets.manage-all',
            'tickets.manage-sla',
            'tickets.view-escalations',
            'tickets.acknowledge-escalation',
            'tickets.close',
            'tickets.delete',

            /*
            |--------------------------------------------------------------------------
            | Notifications
            |--------------------------------------------------------------------------
            */

            'notifications.view',
            'notifications.manage-preferences',
            'notifications.manage-rules',
            'notifications.view-delivery-history',

            /*
            |--------------------------------------------------------------------------
            | Reports
            |--------------------------------------------------------------------------
            */

            'reports.view',
            'reports.export',
            'reports.profitability',

            /*
            |--------------------------------------------------------------------------
            | Settings
            |--------------------------------------------------------------------------
            */

            'settings.manage',
        ];

        /*
        |--------------------------------------------------------------------------
        | Create or Update Permissions
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
        | Create or Retrieve Roles
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
        |
        | Super administrators receive every application permission.
        |
        */

        $superAdmin->syncPermissions($permissions);

        /*
        |--------------------------------------------------------------------------
        | Project Manager Permissions
        |--------------------------------------------------------------------------
        |
        | Project managers can manage clients, projects, tasks, approvals,
        | notes, work logs, project activities, attachments, payment visibility,
        | collection follow-ups, operational expenses and profitability reports.
        |
        | Project managers cannot delete expense records or manage expense
        | categories.
        |
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
            'notes.pin',
            'notes.manage',
            'notes.view-sensitive',

            'work-logs.view',
            'work-logs.create',
            'work-logs.update',
            'work-logs.delete',
            'work-logs.manage',

            'activities.view',
            'activities.view-sensitive',

            'attachments.view',
            'attachments.upload',
            'attachments.delete',

            'payments.view',

            'payment-followups.view',
            'payment-followups.create',
            'payment-followups.update',

            'expenses.view',
            'expenses.create',
            'expenses.update',

            'tickets.view',
            'tickets.create',
            'tickets.update',
            'tickets.assign',
            'tickets.comment',
            'tickets.respond',
            'tickets.resolve',
            'tickets.reopen',
            'tickets.manage-all',
            'tickets.view-escalations',
            'tickets.acknowledge-escalation',
            'tickets.close',

            'notifications.view',
            'notifications.manage-preferences',
            'notifications.view-delivery-history',

            'reports.view',
            'reports.profitability',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Accounts Permissions
        |--------------------------------------------------------------------------
        |
        | Accounts users can manage payments, payment follow-ups, expenses,
        | expense categories and financial profitability reports.
        |
        | Accounts users can also view project notes, view sensitive timeline
        | activity, and view or upload project attachments.
        |
        */

        $accounts->syncPermissions([
            'dashboard.view',

            'clients.view',

            'projects.view',

            'notes.view',

            'activities.view',
            'activities.view-sensitive',

            'attachments.view',
            'attachments.upload',

            'payments.view',
            'payments.create',
            'payments.update',
            'payments.delete',

            'payment-followups.view',
            'payment-followups.create',
            'payment-followups.update',
            'payment-followups.delete',

            'expenses.view',
            'expenses.create',
            'expenses.update',
            'expenses.delete',

            'expense-categories.manage',

            'tickets.view',
            'tickets.create',
            'tickets.comment',
            'tickets.respond',

            'notifications.view',
            'notifications.manage-preferences',

            'reports.view',
            'reports.export',
            'reports.profitability',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Team Member Permissions
        |--------------------------------------------------------------------------
        |
        | Team members can work on assigned projects, tasks, files, notes,
        | work logs, activities and tickets.
        |
        | Team members can update and delete only their own notes and work
        | logs because ownership is enforced by the relevant controllers.
        |
        | Expense records, payment records and profitability reports remain
        | restricted.
        |
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
            'notes.delete',

            'work-logs.view',
            'work-logs.create',
            'work-logs.update',
            'work-logs.delete',

            'activities.view',

            'attachments.view',
            'attachments.upload',

            'tickets.view',
            'tickets.create',
            'tickets.update',
            'tickets.comment',
            'tickets.respond',
            'tickets.resolve',

            'notifications.view',
            'notifications.manage-preferences',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Create or Update Super Administrator
        |--------------------------------------------------------------------------
        */

        $adminName = config('admin.name');
        $adminEmail = config('admin.email');
        $adminPassword = config('admin.password');

        if (
            blank($adminName) ||
            blank($adminEmail) ||
            blank($adminPassword)
        ) {
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

        app(PermissionRegistrar::class)
            ->forgetCachedPermissions();
    }
}

