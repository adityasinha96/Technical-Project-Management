<?php

use App\Enums\ClientStatus;
use App\Enums\ExpenseCategoryScope;
use App\Enums\ExpenseScope;
use App\Enums\ExpenseStatus;
use App\Enums\PaymentKind;
use App\Enums\PaymentMode;
use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Enums\ProjectPriority;
use App\Enums\ProjectStatus;
use App\Models\Client;
use App\Models\ExpenseCategory;
use App\Models\Project;
use App\Models\User;
use App\Services\Expenses\ExpenseService;
use App\Services\Payments\PaymentService;
use App\Services\Reports\ProfitabilityReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createExpenseProject(): Project
{
    $client = Client::create([
        'name' => 'Expense Test Client',
        'client_type' => 'business',
        'status' => ClientStatus::Active->value,
    ]);

    return Project::create([
        'client_id' => $client->id,
        'name' => 'Expense Test Project',

        'project_price' => 50000,
        'estimated_cost' => 15000,

        'start_date' => today(),

        'expected_delivery_date' =>
            today()->addDays(18),

        'status' =>
            ProjectStatus::NewProject->value,

        'priority' =>
            ProjectPriority::Medium->value,

        'maximum_duration_days' => 18,
    ]);
}

function createExpenseUser(): User
{
    return User::factory()->create([
        'status' => 'active',
        'email_verified_at' => now(),
    ]);
}

function createProjectExpenseCategory(): ExpenseCategory
{
    return ExpenseCategory::create([
        'name' => 'Freelancer Test',
        'slug' => 'freelancer-test',

        'scope' =>
            ExpenseCategoryScope::Project->value,

        'is_active' => true,
    ]);
}

function createBusinessExpenseCategory(): ExpenseCategory
{
    return ExpenseCategory::create([
        'name' => 'Office Rent Test',
        'slug' => 'office-rent-test',

        'scope' =>
            ExpenseCategoryScope::Business->value,

        'is_active' => true,
    ]);
}

function expenseData(
    ExpenseCategory $category,
    float $amount,
    ExpenseScope $scope,
    ExpenseStatus $status,
    ?Project $project = null
): array {
    return [
        'scope' => $scope->value,

        'project_id' =>
            $project?->id,

        'expense_category_id' =>
            $category->id,

        'status' => $status->value,
        'amount' => $amount,
        'tax_amount' => 0,

        'expense_date' =>
            today()->toDateString(),

        'due_date' => null,

        'paid_at' =>
            $status === ExpenseStatus::Paid
                ? today()->toDateString()
                : null,

        'payment_mode' =>
            PaymentMode::BankTransfer->value,

        'vendor_name' => 'Test Vendor',
        'bill_number' => null,

        'transaction_reference' => null,

        'description' =>
            'Automated test expense.',

        'internal_notes' => null,
    ];
}

function receiptPaymentData(
    float $amount
): array {
    return [
        'kind' => PaymentKind::Receipt->value,

        'payment_type' =>
            PaymentType::Partial->value,

        'payment_mode' =>
            PaymentMode::BankTransfer->value,

        'status' =>
            PaymentStatus::Cleared->value,

        'amount' => $amount,

        'payment_date' =>
            today()->toDateString(),

        'expected_clearance_date' => null,
        'received_from' => 'Test Client',
        'transaction_reference' => null,
        'invoice_number' => null,
        'bank_name' => null,
        'remarks' => null,
    ];
}

it('calculates project profit from paid project expenses', function () {
    $project = createExpenseProject();
    $user = createExpenseUser();
    $category = createProjectExpenseCategory();

    app(ExpenseService::class)->record(
        $user,
        expenseData(
            $category,
            10000,
            ExpenseScope::Project,
            ExpenseStatus::Paid,
            $project
        )
    );

    $project->refresh();

    expect($project->project_expense_amount)
        ->toBe('10000.00')
        ->and($project->actual_profit_amount)
        ->toBe('40000.00')
        ->and($project->profit_margin_percentage)
        ->toBe('80.00');
});

it('calculates project cash position', function () {
    $project = createExpenseProject();
    $user = createExpenseUser();
    $category = createProjectExpenseCategory();

    app(PaymentService::class)->record(
        $project,
        $user,
        receiptPaymentData(20000)
    );

    app(ExpenseService::class)->record(
        $user,
        expenseData(
            $category,
            12000,
            ExpenseScope::Project,
            ExpenseStatus::Paid,
            $project
        )
    );

    $project->refresh();

    expect($project->cash_position_amount)
        ->toBe('8000.00');
});

it('does not count pending expenses', function () {
    $project = createExpenseProject();
    $user = createExpenseUser();
    $category = createProjectExpenseCategory();

    app(ExpenseService::class)->record(
        $user,
        expenseData(
            $category,
            10000,
            ExpenseScope::Project,
            ExpenseStatus::Pending,
            $project
        )
    );

    $project->refresh();

    expect($project->project_expense_amount)
        ->toBe('0.00')
        ->and($project->actual_profit_amount)
        ->toBe('50000.00');
});

it('does not charge business expense to a project', function () {
    $project = createExpenseProject();
    $user = createExpenseUser();
    $category = createBusinessExpenseCategory();

    app(ExpenseService::class)->record(
        $user,
        expenseData(
            $category,
            15000,
            ExpenseScope::Business,
            ExpenseStatus::Paid
        )
    );

    $project->refresh();

    expect($project->project_expense_amount)
        ->toBe('0.00')
        ->and($project->actual_profit_amount)
        ->toBe('50000.00');
});

it('restores profitability after voiding a project expense', function () {
    $project = createExpenseProject();
    $user = createExpenseUser();
    $category = createProjectExpenseCategory();

    $expense = app(ExpenseService::class)
        ->record(
            $user,
            expenseData(
                $category,
                10000,
                ExpenseScope::Project,
                ExpenseStatus::Paid,
                $project
            )
        );

    app(ExpenseService::class)->void(
        $expense,
        $user,
        'The expense was entered against the wrong project.'
    );

    $project->refresh();

    expect($project->project_expense_amount)
        ->toBe('0.00')
        ->and($project->actual_profit_amount)
        ->toBe('50000.00');
});

it('calculates monthly cash profit', function () {
    $project = createExpenseProject();
    $user = createExpenseUser();

    $projectCategory =
        createProjectExpenseCategory();

    $businessCategory =
        createBusinessExpenseCategory();

    app(PaymentService::class)->record(
        $project,
        $user,
        receiptPaymentData(30000)
    );

    app(ExpenseService::class)->record(
        $user,
        expenseData(
            $projectCategory,
            10000,
            ExpenseScope::Project,
            ExpenseStatus::Paid,
            $project
        )
    );

    app(ExpenseService::class)->record(
        $user,
        expenseData(
            $businessCategory,
            5000,
            ExpenseScope::Business,
            ExpenseStatus::Paid
        )
    );

    $summary = app(
        ProfitabilityReportService::class
    )->monthSummary();

    expect($summary['collection'])
        ->toBe(30000.0)
        ->and($summary['total_expenses'])
        ->toBe(15000.0)
        ->and($summary['cash_profit'])
        ->toBe(15000.0);
});