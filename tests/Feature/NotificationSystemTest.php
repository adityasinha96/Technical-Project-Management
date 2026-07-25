<?php

use App\Enums\NotificationSeverity;
use App\Models\NotificationDispatch;
use App\Models\User;
use App\Notifications\SystemAlertNotification;
use App\Services\Notifications\NotificationDispatcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

function createNotificationUser(
    array $permissions = []
): User {
    $user = User::factory()->create([
        'status' => 'active',
        'email_verified_at' => now(),
    ]);

    $role = Role::findOrCreate(
        'notification-test-role',
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

    /*
     * Standard informational notifications are filtered through the
     * user's global notification settings. Create the default settings
     * so database notifications are enabled during feature tests.
     */
    $user->getOrCreateNotificationSetting();

    return $user;
}

it('stores a database notification', function () {
    $user = createNotificationUser();

    app(
        NotificationDispatcher::class
    )->send(
        recipients: $user,
        eventKey: 'task.assigned',
        title: 'Task assigned',
        message: 'A task was assigned.',
        severity:
            NotificationSeverity::Info,

        requestedChannels: [
            'database',
        ],

        dedupeBucket:
            'test-task-assignment'
    );

    expect(
        $user
            ->fresh()
            ->notifications()
            ->count()
    )->toBe(1);

    $notification =
        $user
            ->notifications()
            ->first();

    expect($notification)
        ->not
        ->toBeNull()
        ->and(
            $notification->data[
                'event_key'
            ]
        )
        ->toBe('task.assigned');
});

it('prevents duplicate notifications', function () {
    $user = createNotificationUser();

    $dispatcher = app(
        NotificationDispatcher::class
    );

    for ($attempt = 1; $attempt <= 2; $attempt++) {
        $dispatcher->send(
            recipients: $user,
            eventKey: 'task.overdue',
            title: 'Task overdue',
            message: 'The task is overdue.',

            severity:
                NotificationSeverity::Danger,

            requestedChannels: [
                'database',
            ],

            dedupeBucket:
                'task-100-day-1'
        );
    }

    expect(
        $user
            ->fresh()
            ->notifications()
            ->count()
    )->toBe(1)
        ->and(
            NotificationDispatch::query()
                ->count()
        )
        ->toBe(1);
});

it('marks one notification as read', function () {
    $user = createNotificationUser([
        'notifications.view',
    ]);

    app(
        NotificationDispatcher::class
    )->send(
        recipients: $user,
        eventKey: 'project.assigned',
        title: 'Project assigned',
        message: 'A project was assigned.',

        requestedChannels: [
            'database',
        ],

        dedupeBucket:
            'project-assigned-test'
    );

    $notification =
        $user
            ->notifications()
            ->first();

    expect($notification)
        ->not
        ->toBeNull();

    $this
        ->actingAs($user)
        ->put(
            route(
                'notifications.read',
                $notification
            )
        )
        ->assertRedirect();

    expect(
        $notification
            ->fresh()
            ->read_at
    )->not->toBeNull();
});

it('respects disabled email preferences', function () {
    Notification::fake();

    $user = createNotificationUser();

    $user
        ->getOrCreateNotificationSetting()
        ->update([
            'in_app_notifications_enabled' =>
                true,

            'email_notifications_enabled' =>
                false,

            'daily_digest_enabled' =>
                true,

            'daily_digest_time' =>
                '08:30:00',

            'timezone' =>
                'Asia/Kolkata',
        ]);

    app(
        NotificationDispatcher::class
    )->send(
        recipients: $user,
        eventKey: 'task.assigned',
        title: 'Task assigned',
        message: 'A task was assigned.',

        requestedChannels: [
            'database',
            'mail',
        ],

        dedupeBucket:
            'disabled-email-test'
    );

    Notification::assertSentTo(
        $user,
        SystemAlertNotification::class,
        function (
            $notification,
            array $channels
        ): bool {
            return $channels === [
                'database',
            ];
        }
    );
});

