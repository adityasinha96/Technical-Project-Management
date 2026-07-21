<?php

use App\Enums\PaymentFollowupChannel;
use App\Enums\PaymentFollowupStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'payment_followups',
            function (Blueprint $table): void {
                $table->id();

                $table->foreignId('project_id')
                    ->constrained()
                    ->cascadeOnDelete();

                $table->foreignId('client_id')
                    ->constrained()
                    ->cascadeOnDelete();

                $table->string('channel', 30)
                    ->default(
                        PaymentFollowupChannel::Phone->value
                    );

                $table->string('status', 30)
                    ->default(
                        PaymentFollowupStatus::Planned->value
                    )
                    ->index();

                $table->dateTime('followup_at')->index();
                $table->dateTime('next_followup_at')->nullable();

                $table->date('promised_payment_date')->nullable();

                $table->decimal(
                    'promised_amount',
                    14,
                    2
                )->nullable();

                $table->string('client_contact_name')->nullable();

                $table->text('client_response')->nullable();
                $table->text('notes')->nullable();

                $table->foreignId('assigned_to')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->foreignId('created_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->foreignId('completed_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->timestamp('completed_at')->nullable();

                $table->timestamps();
                $table->softDeletes();

                $table->index([
                    'project_id',
                    'status',
                    'next_followup_at',
                ]);

                $table->index([
                    'client_id',
                    'status',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_followups');
    }
};