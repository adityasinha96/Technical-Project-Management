<?php

use App\Enums\ClientStatus;
use App\Enums\PaymentKind;
use App\Enums\PaymentMode;
use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Enums\ProjectPriority;
use App\Enums\ProjectStatus;
use App\Models\Client;
use App\Models\Project;
use App\Models\User;
use App\Services\Payments\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createPaymentProject(): Project
{
    $client = Client::create([
        'name' => 'Payment Test Client',
        'client_type' => 'business',
        'status' => ClientStatus::Active->value,
    ]);

    return Project::create([
        'client_id' => $client->id,
        'name' => 'Payment Test Project',

        'project_price' => 50000,
        'estimated_cost' => 10000,

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

function createPaymentUser(): User
{
    return User::factory()->create([
        'status' => 'active',
        'email_verified_at' => now(),
    ]);
}

function paymentData(
    float $amount,
    PaymentKind $kind = PaymentKind::Receipt,
    PaymentStatus $status = PaymentStatus::Cleared
): array {
    return [
        'kind' => $kind->value,

        'payment_type' =>
            $kind === PaymentKind::Refund
                ? PaymentType::Refund->value
                : PaymentType::Partial->value,

        'payment_mode' =>
            PaymentMode::BankTransfer->value,

        'status' => $status->value,
        'amount' => $amount,
        'payment_date' => today()->toDateString(),

        'expected_clearance_date' =>
            $status === PaymentStatus::Pending
                ? today()->addDay()->toDateString()
                : null,

        'received_from' => 'Test Client',
        'transaction_reference' => null,
        'invoice_number' => null,
        'bank_name' => null,
        'remarks' => null,
    ];
}

it('calculates multiple project installments', function () {
    $project = createPaymentProject();
    $user = createPaymentUser();

    $service = app(PaymentService::class);

    $service->record(
        $project,
        $user,
        paymentData(10000)
    );

    $service->record(
        $project,
        $user,
        paymentData(15000)
    );

    $project->refresh();

    expect($project->net_received_amount)
        ->toBe('25000.00')
        ->and($project->pending_amount)
        ->toBe('25000.00')
        ->and($project->collection_percentage)
        ->toBe('50.00');
});

it('does not count a pending payment', function () {
    $project = createPaymentProject();
    $user = createPaymentUser();

    app(PaymentService::class)->record(
        $project,
        $user,
        paymentData(
            20000,
            PaymentKind::Receipt,
            PaymentStatus::Pending
        )
    );

    $project->refresh();

    expect($project->net_received_amount)
        ->toBe('0.00')
        ->and($project->pending_amount)
        ->toBe('50000.00');
});

it('reduces received amount after a refund', function () {
    $project = createPaymentProject();
    $user = createPaymentUser();

    $service = app(PaymentService::class);

    $service->record(
        $project,
        $user,
        paymentData(30000)
    );

    $service->record(
        $project,
        $user,
        paymentData(
            5000,
            PaymentKind::Refund
        )
    );

    $project->refresh();

    expect($project->net_received_amount)
        ->toBe('25000.00')
        ->and($project->pending_amount)
        ->toBe('25000.00');
});

it('restores balance after voiding a payment', function () {
    $project = createPaymentProject();
    $user = createPaymentUser();

    $service = app(PaymentService::class);

    $payment = $service->record(
        $project,
        $user,
        paymentData(20000)
    );

    $service->void(
        $payment,
        $user,
        'The payment was entered against the wrong project.'
    );

    $project->refresh();

    expect($project->net_received_amount)
        ->toBe('0.00')
        ->and($project->pending_amount)
        ->toBe('50000.00');
});

it('prevents refunds above net received amount', function () {
    $project = createPaymentProject();
    $user = createPaymentUser();

    $service = app(PaymentService::class);

    $service->record(
        $project,
        $user,
        paymentData(10000)
    );

    $service->record(
        $project,
        $user,
        paymentData(
            15000,
            PaymentKind::Refund
        )
    );
})->throws(
    Illuminate\Validation\ValidationException::class
);