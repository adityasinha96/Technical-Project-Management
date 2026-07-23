<?php

namespace App\Http\Controllers;

use App\Enums\ExpenseScope;
use App\Enums\ExpenseStatus;
use App\Enums\PaymentMode;
use App\Http\Requests\StoreExpenseRequest;
use App\Http\Requests\UpdateExpenseRequest;
use App\Http\Requests\UpdateExpenseStatusRequest;
use App\Http\Requests\VoidExpenseRequest;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Project;
use App\Services\Expenses\ExpenseService;
use App\Services\Reports\ProfitabilityReportService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class ExpenseController extends Controller
{
    public function __construct(
        private readonly ExpenseService $expenseService,
        private readonly ProfitabilityReportService $reportService
    ) {
    }

    public function index(Request $request): View
    {
        $expenses = Expense::query()
            ->with([
                'project.client',
                'category',
                'createdBy',
            ])
            ->search(
                $request->string('search')->toString()
            )
            ->when(
                $request->filled('scope'),
                fn ($query) =>
                    $query->where(
                        'scope',
                        $request->string('scope')
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
                $request->filled('category_id'),
                fn ($query) =>
                    $query->where(
                        'expense_category_id',
                        $request->integer(
                            'category_id'
                        )
                    )
            )
            ->when(
                $request->filled('project_id'),
                fn ($query) =>
                    $query->where(
                        'project_id',
                        $request->integer(
                            'project_id'
                        )
                    )
            )
            ->when(
                $request->filled('date_from'),
                fn ($query) =>
                    $query->whereDate(
                        'expense_date',
                        '>=',
                        $request->date('date_from')
                    )
            )
            ->when(
                $request->filled('date_to'),
                fn ($query) =>
                    $query->whereDate(
                        'expense_date',
                        '<=',
                        $request->date('date_to')
                    )
            )
            ->latest('expense_date')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('expenses.index', [
            'expenses' => $expenses,

            'summary' =>
                $this->reportService->summary(),

            'monthSummary' =>
                $this->reportService->monthSummary(),

            'scopes' => ExpenseScope::cases(),
            'statuses' => ExpenseStatus::cases(),

            'categories' =>
                ExpenseCategory::query()
                    ->active()
                    ->get(),

            'projects' => Project::query()
                ->with('client')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function create(Request $request): View
    {
        return view('expenses.create', [
            ...$this->formData(),

            'expense' => new Expense([
                'scope' =>
                    $request->filled('project_id')
                        ? ExpenseScope::Project
                        : ExpenseScope::Business,

                'project_id' =>
                    $request->integer('project_id')
                    ?: null,

                'status' => ExpenseStatus::Paid,
                'expense_date' => today(),
                'paid_at' => today(),

                'payment_mode' =>
                    PaymentMode::BankTransfer,
            ]),
        ]);
    }

    public function store(
        StoreExpenseRequest $request
    ): RedirectResponse {
        $receiptData = [];

        try {
            if ($request->hasFile('receipt')) {
                $receiptData =
                    $this->storeReceipt($request);
            }

            $expense = $this->expenseService
                ->record(
                    createdBy: $request->user(),
                    data: $request->validated(),
                    receiptData: $receiptData
                );
        } catch (Throwable $exception) {
            $this->deleteStoredReceipt(
                $receiptData
            );

            throw $exception;
        }

        return redirect()
            ->route('expenses.show', $expense)
            ->with(
                'success',
                "{$expense->expense_number} recorded successfully."
            );
    }

    public function show(Expense $expense): View
    {
        $expense->load([
            'project.client',
            'category',
            'createdBy',
            'voidedBy',
        ]);

        return view(
            'expenses.show',
            compact('expense')
        );
    }

    public function edit(Expense $expense): View
    {
        abort_if(
            $expense->status !==
                ExpenseStatus::Pending ||
            $expense->is_voided,
            403,
            'Only pending expenses can be edited.'
        );

        return view('expenses.edit', [
            ...$this->formData(),
            'expense' => $expense,
        ]);
    }

    public function update(
        UpdateExpenseRequest $request,
        Expense $expense
    ): RedirectResponse {
        $newReceiptData = [];
        $oldReceiptData = [
            'receipt_path' =>
                $expense->receipt_path,

            'receipt_disk' =>
                $expense->receipt_disk,
        ];

        try {
            if ($request->hasFile('receipt')) {
                $newReceiptData =
                    $this->storeReceipt($request);
            }

            $updatedExpense = $this->expenseService
                ->updatePending(
                    expense: $expense,
                    data: $request->validated(),
                    receiptData: $newReceiptData
                );

            if (
                $newReceiptData &&
                $oldReceiptData['receipt_path']
            ) {
                Storage::disk(
                    $oldReceiptData['receipt_disk']
                )->delete(
                    $oldReceiptData['receipt_path']
                );
            }
        } catch (Throwable $exception) {
            $this->deleteStoredReceipt(
                $newReceiptData
            );

            throw $exception;
        }

        return redirect()
            ->route(
                'expenses.show',
                $updatedExpense
            )
            ->with(
                'success',
                'Pending expense updated successfully.'
            );
    }

    public function updateStatus(
        UpdateExpenseStatusRequest $request,
        Expense $expense
    ): RedirectResponse {
        $status = ExpenseStatus::from(
            $request->validated('status')
        );

        $this->expenseService->changeStatus(
            expense: $expense,
            newStatus: $status,
            paidAt: $request->validated('paid_at')
        );

        return back()->with(
            'success',
            "Expense marked as {$status->label()}."
        );
    }

    public function void(
        VoidExpenseRequest $request,
        Expense $expense
    ): RedirectResponse {
        $this->expenseService->void(
            expense: $expense,
            voidedBy: $request->user(),
            reason: $request->validated(
                'void_reason'
            )
        );

        return back()->with(
            'success',
            'Expense entry voided successfully.'
        );
    }

    private function formData(): array
    {
        return [
            'scopes' => ExpenseScope::cases(),
            'statuses' => ExpenseStatus::cases(),
            'paymentModes' => PaymentMode::cases(),

            'categories' =>
                ExpenseCategory::query()
                    ->active()
                    ->get(),

            'projects' => Project::query()
                ->with('client')
                ->orderBy('name')
                ->get(),
        ];
    }

    private function storeReceipt(
        Request $request
    ): array {
        $uploadedFile = $request->file('receipt');

        $extension = strtolower(
            $uploadedFile
                ->getClientOriginalExtension()
        );

        $storedName = Str::uuid()
            . ($extension ? ".{$extension}" : '');

        $year = now()->format('Y');

        $path = $uploadedFile->storeAs(
            "expenses/{$year}",
            $storedName,
            'public'
        );

        return [
            'receipt_original_name' =>
                $uploadedFile
                    ->getClientOriginalName(),

            'receipt_stored_name' =>
                $storedName,

            'receipt_path' => $path,
            'receipt_disk' => 'public',

            'receipt_mime_type' =>
                $uploadedFile->getMimeType(),

            'receipt_size' =>
                $uploadedFile->getSize(),
        ];
    }

    private function deleteStoredReceipt(
        array $receiptData
    ): void {
        if (
            empty($receiptData['receipt_path'])
        ) {
            return;
        }

        Storage::disk(
            $receiptData['receipt_disk']
            ?? 'public'
        )->delete(
            $receiptData['receipt_path']
        );
    }
}