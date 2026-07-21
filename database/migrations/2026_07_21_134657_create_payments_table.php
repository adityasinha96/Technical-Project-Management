<?php

use App\Enums\PaymentKind;
use App\Enums\PaymentMode;
use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table): void {
            $table->id();

            $table->string('payment_number')
                ->nullable()
                ->unique();

            $table->foreignId('project_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('client_id')
                ->constrained()
                ->restrictOnDelete();

            $table->string('kind', 30)
                ->default(PaymentKind::Receipt->value)
                ->index();

            $table->string('payment_type', 50)
                ->default(PaymentType::Partial->value)
                ->index();

            $table->string('payment_mode', 50)
                ->default(PaymentMode::BankTransfer->value)
                ->index();

            $table->string('status', 30)
                ->default(PaymentStatus::Cleared->value)
                ->index();

            $table->decimal('amount', 14, 2);

            $table->date('payment_date')->index();
            $table->date('expected_clearance_date')->nullable();
            $table->date('cleared_at')->nullable();

            $table->string('received_from')->nullable();
            $table->string('bank_name')->nullable();

            $table->string('transaction_reference')
                ->nullable()
                ->index();

            $table->string('invoice_number')->nullable();

            $table->text('remarks')->nullable();

            $table->foreignId('proof_file_id')
                ->nullable()
                ->constrained('project_files')
                ->nullOnDelete();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            /*
             * Cleared financial entries are never physically edited
             * or deleted. An incorrect entry is voided instead.
             */
            $table->foreignId('voided_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('voided_at')->nullable();
            $table->text('void_reason')->nullable();

            $table->timestamps();

            $table->index([
                'project_id',
                'status',
                'payment_date',
            ]);

            $table->index([
                'client_id',
                'payment_date',
            ]);

            $table->index([
                'kind',
                'status',
                'voided_at',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};