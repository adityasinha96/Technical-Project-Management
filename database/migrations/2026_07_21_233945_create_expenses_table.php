<?php

use App\Enums\ExpenseScope;
use App\Enums\ExpenseStatus;
use App\Enums\PaymentMode;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table): void {
            $table->id();

            $table->string('expense_number')
                ->nullable()
                ->unique();

            $table->string('scope', 30)
                ->default(ExpenseScope::Project->value)
                ->index();

            /*
             * Project is required for project expenses and
             * null for general business expenses.
             */
            $table->foreignId('project_id')
                ->nullable()
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('expense_category_id')
                ->constrained()
                ->restrictOnDelete();

            $table->string('status', 30)
                ->default(ExpenseStatus::Paid->value)
                ->index();

            $table->decimal('amount', 14, 2);

            /*
             * Tax amount is informational and is included
             * within the total amount above.
             */
            $table->decimal('tax_amount', 14, 2)
                ->default(0);

            $table->date('expense_date')->index();
            $table->date('due_date')->nullable();
            $table->date('paid_at')->nullable()->index();

            $table->string('payment_mode', 50)
                ->nullable()
                ->default(PaymentMode::BankTransfer->value);

            $table->string('vendor_name')->nullable();
            $table->string('bill_number')->nullable()->index();

            $table->string('transaction_reference')
                ->nullable()
                ->index();

            $table->text('description')->nullable();
            $table->text('internal_notes')->nullable();

            /*
             * Receipt metadata works for both project and
             * general business expenses.
             */
            $table->string('receipt_original_name')->nullable();
            $table->string('receipt_stored_name')->nullable();
            $table->string('receipt_path')->nullable();
            $table->string('receipt_disk', 30)
                ->default('public');

            $table->string('receipt_mime_type', 150)
                ->nullable();

            $table->unsignedBigInteger('receipt_size')
                ->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('voided_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('voided_at')->nullable();
            $table->text('void_reason')->nullable();

            $table->timestamps();

            $table->index([
                'scope',
                'status',
                'expense_date',
            ]);

            $table->index([
                'project_id',
                'status',
                'paid_at',
            ]);

            $table->index([
                'expense_category_id',
                'paid_at',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};