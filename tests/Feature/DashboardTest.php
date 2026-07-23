<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

test('guests are redirected to the login page', function () {
    $response = $this->get(
        route('dashboard')
    );

    $response->assertRedirect(
        route('login')
    );
});

test('authenticated authorized users can visit the dashboard', function () {
    $permission = Permission::findOrCreate(
        'dashboard.view',
        'web'
    );

    $user = User::factory()->create([
        'status' => 'active',
        'email_verified_at' => now(),
    ]);

    $user->givePermissionTo(
        $permission
    );

    $this->actingAs($user);

    $response = $this->get(
        route('dashboard')
    );

    $response->assertOk();
});