<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PaymentFollowupController;
use App\Http\Controllers\ProjectApprovalController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectFileController;
use App\Http\Controllers\ProjectMemberController;
use App\Http\Controllers\ProjectTaskController;
use App\Http\Controllers\ProjectTemplateController;
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
    | Project Templates
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/project-templates',
        [ProjectTemplateController::class, 'index']
    )
        ->middleware('can:templates.view')
        ->name('project-templates.index');

    Route::get(
        '/project-templates/create',
        [ProjectTemplateController::class, 'create']
    )
        ->middleware('can:templates.manage')
        ->name('project-templates.create');

    Route::post(
        '/project-templates',
        [ProjectTemplateController::class, 'store']
    )
        ->middleware('can:templates.manage')
        ->name('project-templates.store');

    Route::get(
        '/project-templates/{projectTemplate}/edit',
        [ProjectTemplateController::class, 'edit']
    )
        ->middleware('can:templates.manage')
        ->name('project-templates.edit');

    Route::put(
        '/project-templates/{projectTemplate}',
        [ProjectTemplateController::class, 'update']
    )
        ->middleware('can:templates.manage')
        ->name('project-templates.update');

    Route::delete(
        '/project-templates/{projectTemplate}',
        [ProjectTemplateController::class, 'destroy']
    )
        ->middleware('can:templates.manage')
        ->name('project-templates.destroy');

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
    | Apply Project Template
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/projects/{project}/templates/{projectTemplate}/apply',
        [ProjectTemplateController::class, 'apply']
    )
        ->middleware([
            'can:projects.update',
            'can:templates.view',
        ])
        ->name('projects.templates.apply');

    /*
    |--------------------------------------------------------------------------
    | Project Tasks
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/projects/{project}/tasks',
        [ProjectTaskController::class, 'store']
    )
        ->middleware('can:tasks.create')
        ->name('projects.tasks.store');

    Route::put(
        '/projects/{project}/tasks/{projectTask}',
        [ProjectTaskController::class, 'update']
    )
        ->middleware('can:tasks.update')
        ->name('projects.tasks.update');

    Route::delete(
        '/projects/{project}/tasks/{projectTask}',
        [ProjectTaskController::class, 'destroy']
    )
        ->middleware('can:tasks.delete')
        ->name('projects.tasks.destroy');

    /*
    |--------------------------------------------------------------------------
    | Project Approvals
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/projects/{project}/approvals',
        [ProjectApprovalController::class, 'store']
    )
        ->middleware('can:approvals.manage')
        ->name('projects.approvals.store');

    Route::put(
        '/projects/{project}/approvals/{projectApproval}/review',
        [ProjectApprovalController::class, 'review']
    )
        ->middleware('can:approvals.manage')
        ->name('projects.approvals.review');

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

    /*
    |--------------------------------------------------------------------------
    | Payments and Outstanding Balance
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/payments',
        [PaymentController::class, 'index']
    )
        ->middleware('can:payments.view')
        ->name('payments.index');

    /*
     * This route must remain above /payments/{payment}.
     * Otherwise, Laravel may treat "outstanding" as a payment identifier.
     */
    Route::get(
        '/payments/outstanding',
        [PaymentController::class, 'outstanding']
    )
        ->middleware('can:payments.view')
        ->name('payments.outstanding');

    Route::get(
        '/payments/{payment}',
        [PaymentController::class, 'show']
    )
        ->middleware('can:payments.view')
        ->name('payments.show');

    Route::post(
        '/projects/{project}/payments',
        [PaymentController::class, 'store']
    )
        ->middleware('can:payments.create')
        ->name('projects.payments.store');

    Route::put(
        '/payments/{payment}/status',
        [PaymentController::class, 'updateStatus']
    )
        ->middleware('can:payments.update')
        ->name('payments.status.update');

    Route::put(
        '/payments/{payment}/void',
        [PaymentController::class, 'void']
    )
        ->middleware('can:payments.delete')
        ->name('payments.void');

    /*
    |--------------------------------------------------------------------------
    | Payment Follow-ups
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/projects/{project}/payment-followups',
        [PaymentFollowupController::class, 'store']
    )
        ->middleware('can:payments.followup')
        ->name('projects.payment-followups.store');

    Route::put(
        '/projects/{project}/payment-followups/{paymentFollowup}',
        [PaymentFollowupController::class, 'update']
    )
        ->middleware('can:payments.followup')
        ->name('projects.payment-followups.update');

    Route::delete(
        '/projects/{project}/payment-followups/{paymentFollowup}',
        [PaymentFollowupController::class, 'destroy']
    )
        ->middleware('can:payments.followup')
        ->name('projects.payment-followups.destroy');
});