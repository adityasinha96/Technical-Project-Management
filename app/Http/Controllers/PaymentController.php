<?php

namespace App\Http\Controllers;

use App\Enums\NotificationSeverity;
use App\Enums\PaymentKind;
use App\Enums\PaymentMode;
use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Http\Requests\StorePaymentRequest;
use App\Http\Requests\UpdatePaymentStatusRequest;
use App\Http\Requests\VoidPaymentRequest;
use App\Models\Client;
use App\Models\Payment;
use App\Models\Project;
use App\Models\ProjectFile;
use App\Services\Notifications\NotificationDispatcher;
use App\Services\Notifications\NotificationRecipientResolver;
use App\Services\Payments\PaymentService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class PaymentController extends Controller
{
    public function __construct(
        private readonly PaymentService $paymentService,
        private readonly NotificationDispatcher $notificationDispatcher,
        private readonly NotificationRecipientResolver $recipientResolver
    ) {
    }

    public function index(Request $request): View
    {
        $payments = Payment::query()
            ->with([
                'project',
                'client',
                'createdBy',
            ])
            ->search(
                $request->string('search')->toString()
            )
            ->when(
                $request->filled('client_id'),
                fn ($query) =>
                    $query->where(
                        'client_id',
                        $request->integer('client_id')
                    )
            )
            ->when(
                $request->filled('project_id'),
                fn ($query) =>
                    $query->where(
                        'project_id',
                        $request->integer('project_id')
                    )
            )
            ->when(
                $request->filled('kind'),
                fn ($query) =>
                    $query->where(
                        'kind',
                        $request->string('kind')
                    )
            )
            ->when(
                $request->filled('status'),
                fn ($query) =>
                    $query->where(
                        'status',
                        $request->string('status')
                    )
            )
            ->when(
                $request->filled('payment_mode'),
                fn ($query) =>
                    $query->where(
                        'payment_mode',
                        $request->string('payment_mode')
                    )
            )
            ->when(
                $request->filled('date_from'),
                fn ($query) =>
                    $query->whereDate(
                        'payment_date',
                        '>=',
                        $request->date('date_from')
                    )
            )
            ->when(
                $request->filled('date_to'),
                fn ($query) =>
                    $query->whereDate(
                        'payment_date',
                        '<=',
                        $request->date('date_to')
                    )
            )
            ->latest('payment_date')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $totalReceipts = Payment::query()
            ->effective()
            ->receipts()
            ->sum('amount');

        $totalRefunds = Payment::query()
            ->effective()
            ->refunds()
            ->sum('amount');

        $monthlyReceipts = Payment::query()
            ->effective()
            ->receipts()
            ->whereBetween('payment_date', [
                now()->startOfMonth()->toDateString(),
                now()->endOfMonth()->toDateString(),
            ])
            ->sum('amount');

        $monthlyRefunds = Payment::query()
            ->effective()
            ->refunds()
            ->whereBetween('payment_date', [
                now()->startOfMonth()->toDateString(),
                now()->endOfMonth()->toDateString(),
            ])
            ->sum('amount');

        $summary = [
            'total_received' =>
                (float) $totalReceipts -
                (float) $totalRefunds,

            'current_month_collection' =>
                (float) $monthlyReceipts -
                (float) $monthlyRefunds,

            'market_outstanding' =>
                Project::query()->sum('pending_amount'),

            'pending_payments' =>
                Payment::query()
                    ->where(
                        'status',
                        PaymentStatus::Pending->value
                    )
                    ->whereNull('voided_at')
                    ->sum('amount'),
        ];

        return view('payments.index', [
            'payments' => $payments,
            'summary' => $summary,

            'clients' => Client::query()
                ->orderBy('company_name')
                ->orderBy('name')
                ->get(),

            'projects' => Project::query()
                ->orderBy('name')
                ->get(),

            'kinds' => PaymentKind::cases(),
            'statuses' => PaymentStatus::cases(),
            'modes' => PaymentMode::cases(),
        ]);
    }

    public function outstanding(
        Request $request
    ): View {
        $projects = Project::query()
            ->with([
                'client',
                'manager',
            ])
            ->where(
                'pending_amount',
                '>',
                0
            )
            ->search(
                $request->string('search')->toString()
            )
            ->when(
                $request->filled('client_id'),
                fn ($query) =>
                    $query->where(
                        'client_id',
                        $request->integer('client_id')
                    )
            )
            ->when(
                $request->string('sort')->toString()
                    === 'oldest_payment',
                fn ($query) =>
                    $query
                        ->orderByRaw(
                            'last_payment_date IS NULL DESC'
                        )
                        ->orderBy('last_payment_date')
            )
            ->when(
                $request->string('sort')->toString()
                    !== 'oldest_payment',
                fn ($query) =>
                    $query->orderByDesc(
                        'pending_amount'
                    )
            )
            ->paginate(20)
            ->withQueryString();

        $summary = [
            'market_outstanding' =>
                Project::query()->sum('pending_amount'),

            'projects_with_pending' =>
                Project::query()
                    ->where('pending_amount', '>', 0)
                    ->count(),

            'fully_paid_projects' =>
                Project::query()
                    ->where('project_price', '>', 0)
                    ->where('pending_amount', '<=', 0)
                    ->count(),

            'average_pending' =>
                Project::query()
                    ->where('pending_amount', '>', 0)
                    ->avg('pending_amount') ?: 0,
        ];

        return view('payments.outstanding', [
            'projects' => $projects,
            'summary' => $summary,

            'clients' => Client::query()
                ->orderBy('company_name')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(
        StorePaymentRequest $request,
        Project $project
    ): RedirectResponse {
        $validated = $request->validated();

        $projectFile = null;

        try {
            if ($request->hasFile('proof')) {
                $projectFile = $this->storeProofFile(
                    $request,
                    $project
                );
            }

            $payment = $this->paymentService
                ->record(
                    project: $project,
                    createdBy: $request->user(),
                    data: $validated,
                    proofFileId: $projectFile?->id
                );
        } catch (Throwable $exception) {
            if ($projectFile) {
                Storage::disk($projectFile->disk)
                    ->delete($projectFile->path);

                $projectFile->forceDelete();
            }

            throw $exception;
        }

        if (
            $this->shouldSendPaymentReceivedNotification(
                $payment
            )
        ) {
            $this->sendPaymentReceivedNotification(
                $payment
            );
        }

        return redirect()
            ->route('projects.show', [
                'project' => $project,
                'tab' => 'payments',
            ])
            ->with(
                'success',
                "{$payment->payment_number} recorded successfully."
            );
    }

    public function show(Payment $payment): View
    {
        $payment->load([
            'project.client',
            'proofFile',
            'createdBy',
            'voidedBy',
        ]);

        return view(
            'payments.show',
            compact('payment')
        );
    }

    public function updateStatus(
        UpdatePaymentStatusRequest $request,
        Payment $payment
    ): RedirectResponse {
        $oldStatusValue =
            $payment->status->value;

        $status = PaymentStatus::from(
            $request->validated('status')
        );

        $this->paymentService
            ->changeStatus($payment, $status);

        $payment->refresh();

        if (
            $oldStatusValue !==
                $payment->status->value
            && $this
                ->shouldSendPaymentReceivedNotification(
                    $payment
                )
        ) {
            $this->sendPaymentReceivedNotification(
                $payment
            );
        }

        return back()->with(
            'success',
            "Payment marked as {$status->label()}."
        );
    }

    public function void(
        VoidPaymentRequest $request,
        Payment $payment
    ): RedirectResponse {
        $this->paymentService->void(
            payment: $payment,
            voidedBy: $request->user(),
            reason: $request->validated('void_reason')
        );

        return back()->with(
            'success',
            'Payment entry voided successfully.'
        );
    }

    private function shouldSendPaymentReceivedNotification(
        Payment $payment
    ): bool {
        $statusValue =
            $payment->status->value;

        return
            $payment->kind ===
                PaymentKind::Receipt
            && in_array(
                $statusValue,
                [
                    'received',
                    'completed',
                ],
                true
            )
            && $payment->voided_at === null;
    }

    private function sendPaymentReceivedNotification(
        Payment $payment
    ): void {
        $payment->loadMissing(
            'project.manager'
        );

        $recipients = $this
            ->recipientResolver
            ->projectManager(
                $payment->project
            )
            ->merge(
                $this
                    ->recipientResolver
                    ->accounts()
            )
            ->unique('id')
            ->values();

        $this->notificationDispatcher->send(
            recipients: $recipients,
            eventKey: 'payment.received',
            title: 'Project payment received',

            message:
                '₹'
                . number_format(
                    (float) $payment->amount,
                    2
                )
                . " was recorded for {$payment->project->name}.",

            url: route(
                'payments.show',
                $payment
            ),

            severity:
                NotificationSeverity::Success,

            subject: $payment,

            context: [
                'project_id' =>
                    $payment->project_id,

                'payment_id' =>
                    $payment->id,

                'amount' =>
                    $payment->amount,
            ],

            dedupeBucket:
                "payment-received:{$payment->id}:{$payment->status->value}"
        );
    }

    private function storeProofFile(
        StorePaymentRequest $request,
        Project $project
    ): ProjectFile {
        $uploadedFile = $request->file('proof');

        $extension = strtolower(
            $uploadedFile->getClientOriginalExtension()
        );

        $storedName = Str::uuid()
            . ($extension ? ".{$extension}" : '');

        $path = $uploadedFile->storeAs(
            "projects/{$project->id}/payments",
            $storedName,
            'public'
        );

        return $project->files()->create([
            'uploaded_by' => $request->user()->id,
            'category' => 'payment',

            'original_name' =>
                $uploadedFile->getClientOriginalName(),

            'stored_name' => $storedName,
            'path' => $path,
            'disk' => 'public',

            'mime_type' =>
                $uploadedFile->getMimeType(),

            'size' =>
                $uploadedFile->getSize(),

            'description' =>
                'Payment proof or transaction document.',
        ]);
    }
}

