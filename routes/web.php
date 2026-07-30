<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseCategoryController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\NotificationCenterController;
use App\Http\Controllers\NotificationPreferenceController;
use App\Http\Controllers\NotificationRuleController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PaymentFollowupController;
use App\Http\Controllers\ProfitabilityController;
use App\Http\Controllers\ProjectApprovalController;
use App\Http\Controllers\ProjectAttachmentController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectFileController;
use App\Http\Controllers\ProjectMemberController;
use App\Http\Controllers\ProjectNoteController;
use App\Http\Controllers\ProjectTaskController;
use App\Http\Controllers\ProjectTemplateController;
use App\Http\Controllers\ProjectWorkLogController;
use App\Http\Controllers\Reports\ReportController;
use App\Http\Controllers\Reports\ReportExportController;
use App\Http\Controllers\TicketCommentController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TicketEscalationController;
use App\Http\Controllers\TicketSlaPolicyController;
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
    'auth:web',
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
    | Notification Centre
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/notifications',
        [NotificationCenterController::class, 'index']
    )
        ->middleware('can:notifications.view')
        ->name('notifications.index');

    /*
     * These static notification routes must remain above the dynamic
     * /notifications/{notification} routes.
     */
    Route::put(
        '/notifications/read-all',
        [NotificationCenterController::class, 'markAllRead']
    )
        ->middleware('can:notifications.view')
        ->name('notifications.read-all');

    Route::delete(
        '/notifications/read',
        [NotificationCenterController::class, 'clearRead']
    )
        ->middleware('can:notifications.view')
        ->name('notifications.clear-read');

    Route::get(
        '/notifications/{notification}/open',
        [NotificationCenterController::class, 'open']
    )
        ->middleware('can:notifications.view')
        ->name('notifications.open');

    Route::put(
        '/notifications/{notification}/read',
        [NotificationCenterController::class, 'markRead']
    )
        ->middleware('can:notifications.view')
        ->name('notifications.read');

    Route::delete(
        '/notifications/{notification}',
        [NotificationCenterController::class, 'destroy']
    )
        ->middleware('can:notifications.view')
        ->name('notifications.destroy');

    /*
    |--------------------------------------------------------------------------
    | User Notification Preferences
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/notification-settings',
        [NotificationPreferenceController::class, 'edit']
    )
        ->middleware(
            'can:notifications.manage-preferences'
        )
        ->name('notification-settings.edit');

    Route::put(
        '/notification-settings',
        [NotificationPreferenceController::class, 'update']
    )
        ->middleware(
            'can:notifications.manage-preferences'
        )
        ->name('notification-settings.update');

    /*
    |--------------------------------------------------------------------------
    | Administrator Reminder Rules
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/notification-rules',
        [NotificationRuleController::class, 'index']
    )
        ->middleware(
            'can:notifications.manage-rules'
        )
        ->name('notification-rules.index');

    Route::put(
        '/notification-rules/{notificationRule}',
        [NotificationRuleController::class, 'update']
    )
        ->middleware(
            'can:notifications.manage-rules'
        )
        ->name('notification-rules.update');

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
    | Project Notes
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/projects/{project}/notes',
        [ProjectNoteController::class, 'store']
    )
        ->middleware('can:notes.create')
        ->name('projects.notes.store');

    Route::put(
        '/projects/{project}/notes/{projectNote}',
        [ProjectNoteController::class, 'update']
    )
        ->middleware('can:notes.update')
        ->name('projects.notes.update');

    Route::put(
        '/projects/{project}/notes/{projectNote}/pin',
        [ProjectNoteController::class, 'togglePin']
    )
        ->middleware('can:notes.pin')
        ->name('projects.notes.pin');

    Route::delete(
        '/projects/{project}/notes/{projectNote}',
        [ProjectNoteController::class, 'destroy']
    )
        ->middleware('can:notes.delete')
        ->name('projects.notes.destroy');

    /*
    |--------------------------------------------------------------------------
    | Project Work Logs
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/projects/{project}/work-logs',
        [ProjectWorkLogController::class, 'store']
    )
        ->middleware('can:work-logs.create')
        ->name('projects.work-logs.store');

    Route::put(
        '/projects/{project}/work-logs/{projectWorkLog}',
        [ProjectWorkLogController::class, 'update']
    )
        ->middleware('can:work-logs.update')
        ->name('projects.work-logs.update');

    Route::delete(
        '/projects/{project}/work-logs/{projectWorkLog}',
        [ProjectWorkLogController::class, 'destroy']
    )
        ->middleware('can:work-logs.delete')
        ->name('projects.work-logs.destroy');

    /*
    |--------------------------------------------------------------------------
    | Project Attachments
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/projects/{project}/attachments',
        [ProjectAttachmentController::class, 'store']
    )
        ->middleware('can:attachments.upload')
        ->name('projects.attachments.store');

    Route::get(
        '/projects/{project}/attachments/{projectFile}/download',
        [ProjectAttachmentController::class, 'download']
    )
        ->middleware('can:attachments.view')
        ->name('projects.attachments.download');

    Route::delete(
        '/projects/{project}/attachments/{projectFile}',
        [ProjectAttachmentController::class, 'destroy']
    )
        ->middleware('can:attachments.delete')
        ->name('projects.attachments.destroy');


    /*
    |--------------------------------------------------------------------------
    | Ticket Escalation and SLA Configuration
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/tickets/escalations',
        [TicketEscalationController::class, 'index']
    )
        ->middleware(
            'can:tickets.view-escalations'
        )
        ->name('tickets.escalations');

    Route::put(
        '/tickets/{ticket}/escalations/{ticketEscalation}/acknowledge',
        [
            TicketEscalationController::class,
            'acknowledge',
        ]
    )
        ->middleware(
            'can:tickets.acknowledge-escalation'
        )
        ->name(
            'tickets.escalations.acknowledge'
        );

    Route::get(
        '/ticket-sla-policies',
        [TicketSlaPolicyController::class, 'index']
    )
        ->middleware(
            'can:tickets.manage-sla'
        )
        ->name('ticket-sla-policies.index');

    Route::put(
        '/ticket-sla-policies/{ticketSlaPolicy}',
        [TicketSlaPolicyController::class, 'update']
    )
        ->middleware(
            'can:tickets.manage-sla'
        )
        ->name('ticket-sla-policies.update');

    /*
    |--------------------------------------------------------------------------
    | Project Tickets
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/tickets',
        [TicketController::class, 'index']
    )
        ->middleware('can:tickets.view')
        ->name('tickets.index');

    Route::get(
        '/tickets/create',
        [TicketController::class, 'create']
    )
        ->middleware('can:tickets.create')
        ->name('tickets.create');

    Route::post(
        '/tickets',
        [TicketController::class, 'store']
    )
        ->middleware('can:tickets.create')
        ->name('tickets.store');

    Route::get(
        '/tickets/{ticket}',
        [TicketController::class, 'show']
    )
        ->middleware('can:tickets.view')
        ->name('tickets.show');

    Route::put(
        '/tickets/{ticket}',
        [TicketController::class, 'update']
    )
        ->middleware('can:tickets.update')
        ->name('tickets.update');

    Route::put(
        '/tickets/{ticket}/assign',
        [TicketController::class, 'assign']
    )
        ->middleware('can:tickets.assign')
        ->name('tickets.assign');

    Route::put(
        '/tickets/{ticket}/transition',
        [TicketController::class, 'transition']
    )
        ->middleware('can:tickets.update')
        ->name('tickets.transition');

    Route::put(
        '/tickets/{ticket}/resolve',
        [TicketController::class, 'resolve']
    )
        ->middleware('can:tickets.resolve')
        ->name('tickets.resolve');

    Route::put(
        '/tickets/{ticket}/reopen',
        [TicketController::class, 'reopen']
    )
        ->middleware('can:tickets.reopen')
        ->name('tickets.reopen');

    /*
    |--------------------------------------------------------------------------
    | Ticket Discussion
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/tickets/{ticket}/comments',
        [TicketCommentController::class, 'store']
    )
        ->middleware('can:tickets.comment')
        ->name('tickets.comments.store');

    Route::put(
        '/tickets/{ticket}/comments/{ticketComment}',
        [TicketCommentController::class, 'update']
    )
        ->middleware('can:tickets.comment')
        ->name('tickets.comments.update');

    Route::delete(
        '/tickets/{ticket}/comments/{ticketComment}',
        [TicketCommentController::class, 'destroy']
    )
        ->middleware('can:tickets.comment')
        ->name('tickets.comments.destroy');

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
        ->middleware('can:payment-followups.create')
        ->name('projects.payment-followups.store');

    Route::put(
        '/projects/{project}/payment-followups/{paymentFollowup}',
        [PaymentFollowupController::class, 'update']
    )
        ->middleware('can:payment-followups.update')
        ->name('projects.payment-followups.update');

    Route::delete(
        '/projects/{project}/payment-followups/{paymentFollowup}',
        [PaymentFollowupController::class, 'destroy']
    )
        ->middleware('can:payment-followups.delete')
        ->name('projects.payment-followups.destroy');

    /*
    |--------------------------------------------------------------------------
    | Expense Categories
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/expense-categories',
        [ExpenseCategoryController::class, 'index']
    )
        ->middleware('can:expense-categories.manage')
        ->name('expense-categories.index');

    Route::post(
        '/expense-categories',
        [ExpenseCategoryController::class, 'store']
    )
        ->middleware('can:expense-categories.manage')
        ->name('expense-categories.store');

    Route::put(
        '/expense-categories/{expenseCategory}',
        [ExpenseCategoryController::class, 'update']
    )
        ->middleware('can:expense-categories.manage')
        ->name('expense-categories.update');

    Route::delete(
        '/expense-categories/{expenseCategory}',
        [ExpenseCategoryController::class, 'destroy']
    )
        ->middleware('can:expense-categories.manage')
        ->name('expense-categories.destroy');

    /*
    |--------------------------------------------------------------------------
    | Expenses
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/expenses',
        [ExpenseController::class, 'index']
    )
        ->middleware('can:expenses.view')
        ->name('expenses.index');

    /*
     * This route must remain above /expenses/{expense}.
     * Otherwise, Laravel may treat "create" as an expense identifier.
     */
    Route::get(
        '/expenses/create',
        [ExpenseController::class, 'create']
    )
        ->middleware('can:expenses.create')
        ->name('expenses.create');

    Route::get(
        '/expenses/{expense}',
        [ExpenseController::class, 'show']
    )
        ->middleware('can:expenses.view')
        ->name('expenses.show');

    Route::post(
        '/expenses',
        [ExpenseController::class, 'store']
    )
        ->middleware('can:expenses.create')
        ->name('expenses.store');

    Route::get(
        '/expenses/{expense}/edit',
        [ExpenseController::class, 'edit']
    )
        ->middleware('can:expenses.update')
        ->name('expenses.edit');

    Route::put(
        '/expenses/{expense}',
        [ExpenseController::class, 'update']
    )
        ->middleware('can:expenses.update')
        ->name('expenses.update');

    Route::put(
        '/expenses/{expense}/status',
        [ExpenseController::class, 'updateStatus']
    )
        ->middleware('can:expenses.update')
        ->name('expenses.status.update');

    Route::put(
        '/expenses/{expense}/void',
        [ExpenseController::class, 'void']
    )
        ->middleware('can:expenses.delete')
        ->name('expenses.void');

    /*
    |--------------------------------------------------------------------------
    | Reports and Analytics
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/reports',
        [ReportController::class, 'index']
    )
        ->middleware('can:reports.view')
        ->name('reports.index');

    Route::get(
        '/reports/projects',
        [ReportController::class, 'projects']
    )
        ->middleware('can:reports.view')
        ->name('reports.projects');

    Route::get(
        '/reports/team-performance',
        [ReportController::class, 'team']
    )
        ->middleware('can:reports.view-team')
        ->name('reports.team');

    Route::get(
        '/reports/collections',
        [ReportController::class, 'collections']
    )
        ->middleware('can:reports.view-financial')
        ->name('reports.collections');

    Route::get(
        '/reports/profitability',
        [ReportController::class, 'profitability']
    )
        ->middleware('can:reports.view-financial')
        ->name('reports.profitability');

    Route::get(
        '/reports/ticket-sla',
        [ReportController::class, 'ticketSla']
    )
        ->middleware('can:reports.view-ticket-sla')
        ->name('reports.ticket-sla');

    Route::post(
        '/reports/export',
        [ReportExportController::class, 'store']
    )
        ->middleware('can:reports.export')
        ->name('reports.export');

    Route::get(
        '/reports/export-history',
        [ReportExportController::class, 'index']
    )
        ->middleware(
            'can:reports.view-export-history'
        )
        ->name('reports.exports');

    /*
    |--------------------------------------------------------------------------
    | Profitability
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/profitability',
        [ProfitabilityController::class, 'index']
    )
        ->middleware('can:reports.profitability')
        ->name('profitability.index');
});