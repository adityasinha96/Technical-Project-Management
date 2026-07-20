<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectFileController;
use App\Http\Controllers\ProjectMemberController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Home
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
})->name('home');

/*
|--------------------------------------------------------------------------
| Authenticated Application Routes
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'verified',
    'active',
])->group(function (): void {

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/dashboard',
        [DashboardController::class, 'index']
    )
        ->middleware('can:dashboard.view')
        ->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | Clients
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/clients',
        [ClientController::class, 'index']
    )
        ->middleware('can:clients.view')
        ->name('clients.index');

    Route::get(
        '/clients/create',
        [ClientController::class, 'create']
    )
        ->middleware('can:clients.create')
        ->name('clients.create');

    Route::post(
        '/clients',
        [ClientController::class, 'store']
    )
        ->middleware('can:clients.create')
        ->name('clients.store');

    Route::get(
        '/clients/{client}',
        [ClientController::class, 'show']
    )
        ->middleware('can:clients.view')
        ->name('clients.show');

    Route::get(
        '/clients/{client}/edit',
        [ClientController::class, 'edit']
    )
        ->middleware('can:clients.update')
        ->name('clients.edit');

    Route::put(
        '/clients/{client}',
        [ClientController::class, 'update']
    )
        ->middleware('can:clients.update')
        ->name('clients.update');

    Route::delete(
        '/clients/{client}',
        [ClientController::class, 'destroy']
    )
        ->middleware('can:clients.delete')
        ->name('clients.destroy');

    /*
    |--------------------------------------------------------------------------
    | Projects
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/projects',
        [ProjectController::class, 'index']
    )
        ->middleware('can:projects.view')
        ->name('projects.index');

    Route::get(
        '/projects/create',
        [ProjectController::class, 'create']
    )
        ->middleware('can:projects.create')
        ->name('projects.create');

    Route::post(
        '/projects',
        [ProjectController::class, 'store']
    )
        ->middleware('can:projects.create')
        ->name('projects.store');

    Route::get(
        '/projects/{project}',
        [ProjectController::class, 'show']
    )
        ->middleware('can:projects.view')
        ->name('projects.show');

    Route::get(
        '/projects/{project}/edit',
        [ProjectController::class, 'edit']
    )
        ->middleware('can:projects.update')
        ->name('projects.edit');

    Route::put(
        '/projects/{project}',
        [ProjectController::class, 'update']
    )
        ->middleware('can:projects.update')
        ->name('projects.update');

    Route::delete(
        '/projects/{project}',
        [ProjectController::class, 'destroy']
    )
        ->middleware('can:projects.delete')
        ->name('projects.destroy');

    /*
    |--------------------------------------------------------------------------
    | Project Team
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/projects/{project}/members',
        [ProjectMemberController::class, 'store']
    )
        ->middleware('can:projects.assign-team')
        ->name('projects.members.store');

    Route::delete(
        '/projects/{project}/members/{user}',
        [ProjectMemberController::class, 'destroy']
    )
        ->middleware('can:projects.assign-team')
        ->name('projects.members.destroy');

    /*
    |--------------------------------------------------------------------------
    | Project Files
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/projects/{project}/files',
        [ProjectFileController::class, 'store']
    )
        ->middleware('can:projects.manage-files')
        ->name('projects.files.store');

    Route::delete(
        '/projects/{project}/files/{projectFile}',
        [ProjectFileController::class, 'destroy']
    )
        ->middleware('can:projects.manage-files')
        ->name('projects.files.destroy');
});