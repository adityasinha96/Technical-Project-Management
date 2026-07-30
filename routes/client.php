<?php

use App\Http\Controllers\Client\Auth\ClientForgotPasswordController;
use App\Http\Controllers\Client\Auth\ClientInvitationController;
use App\Http\Controllers\Client\Auth\ClientLoginController;
use App\Http\Controllers\Client\Auth\ClientResetPasswordController;
use App\Http\Controllers\Client\ClientApprovalController;
use App\Http\Controllers\Client\ClientCommunicationController;
use App\Http\Controllers\Client\ClientDashboardController;
use App\Http\Controllers\Client\ClientFileController;
use App\Http\Controllers\Client\ClientNotificationController;
use App\Http\Controllers\Client\ClientPaymentController;
use App\Http\Controllers\Client\ClientProjectController;
use App\Http\Controllers\Client\ClientTicketCommentController;
use App\Http\Controllers\Client\ClientTicketController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest:client')
    ->group(function (): void {
        Route::get(
            '/login',
            [ClientLoginController::class, 'create']
        )->name('login');

        Route::post(
            '/login',
            [ClientLoginController::class, 'store']
        )
            ->middleware(
                'throttle:client-login'
            )
            ->name('login.store');

        Route::get(
            '/forgot-password',
            [ClientForgotPasswordController::class, 'create']
        )->name('password.request');

        Route::post(
            '/forgot-password',
            [ClientForgotPasswordController::class, 'store']
        )
            ->middleware('throttle:5,1')
            ->name('password.email');

        Route::get(
            '/reset-password/{token}',
            [ClientResetPasswordController::class, 'create']
        )->name('password.reset');

        Route::post(
            '/reset-password',
            [ClientResetPasswordController::class, 'store']
        )->name('password.update');

        Route::get(
            '/invitation/{invitation}/{token}',
            [ClientInvitationController::class, 'show']
        )
            ->middleware('signed')
            ->name('invitation.show');

        Route::post(
            '/invitation/{invitation}/accept',
            [ClientInvitationController::class, 'store']
        )->name('invitation.accept');
    });

Route::middleware([
    'auth:client',
    'client.active',
])
    ->group(function (): void {
        Route::get(
            '/',
            ClientDashboardController::class
        )->name('dashboard');

        Route::post(
            '/logout',
            [ClientLoginController::class, 'destroy']
        )->name('logout');

        Route::get(
            '/projects/{project}',
            [ClientProjectController::class, 'show']
        )
            ->middleware(
                'client.project:view'
            )
            ->name('projects.show');

        Route::put(
            '/projects/{project}/approvals/{approval}',
            [ClientApprovalController::class, 'update']
        )
            ->middleware(
                'client.project:approve'
            )
            ->name('approvals.update');

        Route::get(
            '/projects/{project}/payments',
            [ClientPaymentController::class, 'index']
        )
            ->middleware(
                'client.project:financials'
            )
            ->name('payments.index');

        Route::get(
            '/projects/{project}/tickets/create',
            [ClientTicketController::class, 'create']
        )
            ->middleware(
                'client.project:tickets'
            )
            ->name('tickets.create');

        Route::post(
            '/projects/{project}/tickets',
            [ClientTicketController::class, 'store']
        )
            ->middleware(
                'client.project:tickets'
            )
            ->name('tickets.store');

        Route::get(
            '/projects/{project}/tickets/{ticket}',
            [ClientTicketController::class, 'show']
        )
            ->middleware(
                'client.project:view'
            )
            ->name('tickets.show');

        Route::post(
            '/projects/{project}/tickets/{ticket}/comments',
            [ClientTicketCommentController::class, 'store']
        )
            ->middleware(
                'client.project:tickets'
            )
            ->name('ticket-comments.store');

        Route::get(
            '/projects/{project}/files',
            [ClientFileController::class, 'index']
        )
            ->middleware(
                'client.project:files'
            )
            ->name('files.index');

        Route::get(
            '/projects/{project}/files/{projectFile}/download',
            [ClientFileController::class, 'download']
        )
            ->middleware(
                'client.project:files'
            )
            ->name('files.download');

        Route::get(
            '/projects/{project}/communications',
            [ClientCommunicationController::class, 'index']
        )
            ->middleware(
                'client.project:communicate'
            )
            ->name('communications.index');

        Route::post(
            '/projects/{project}/communications',
            [ClientCommunicationController::class, 'store']
        )
            ->middleware(
                'client.project:communicate'
            )
            ->name('communications.store');

        Route::get(
            '/notifications',
            [ClientNotificationController::class, 'index']
        )->name('notifications.index');

        Route::get(
            '/notifications/{notification}/open',
            [ClientNotificationController::class, 'open']
        )->name('notifications.open');

        Route::put(
            '/notifications/read-all',
            [ClientNotificationController::class, 'markAllRead']
        )->name('notifications.read-all');
    });