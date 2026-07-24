<?php

use App\Enums\TicketPriority;
use App\Enums\TicketSource;
use App\Enums\TicketStatus;
use App\Enums\TicketType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'project_tickets',
            function (Blueprint $table): void {
                $table->id();

                $table->string('ticket_number')
                    ->nullable()
                    ->unique();

                $table->foreignId('project_id')
                    ->constrained()
                    ->cascadeOnDelete();

                $table->foreignId('client_id')
                    ->constrained()
                    ->restrictOnDelete();

                $table->string('type', 40)
                    ->default(TicketType::Support->value)
                    ->index();

                $table->string('source', 40)
                    ->default(TicketSource::Internal->value)
                    ->index();

                $table->string('priority', 30)
                    ->default(TicketPriority::Medium->value)
                    ->index();

                $table->string('status', 40)
                    ->default(TicketStatus::Open->value)
                    ->index();

                $table->string('subject');
                $table->longText('description');

                $table->foreignId('assigned_to')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->foreignId('assigned_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->timestamp('assigned_at')
                    ->nullable();

                $table->timestamp(
                    'first_response_due_at'
                )
                    ->nullable()
                    ->index();

                $table->timestamp(
                    'resolution_due_at'
                )
                    ->nullable()
                    ->index();

                $table->timestamp(
                    'first_responded_at'
                )
                    ->nullable();

                $table->foreignId(
                    'first_responded_by'
                )
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->timestamp(
                    'response_breached_at'
                )
                    ->nullable();

                $table->timestamp(
                    'resolution_breached_at'
                )
                    ->nullable();

                $table->timestamp('sla_paused_at')
                    ->nullable();

                $table->unsignedInteger(
                    'sla_paused_minutes'
                )->default(0);

                $table->unsignedTinyInteger(
                    'escalation_level'
                )->default(0)->index();

                $table->timestamp('escalated_at')
                    ->nullable();

                $table->timestamp('last_reply_at')
                    ->nullable();

                $table->foreignId('last_reply_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->timestamp('last_activity_at')
                    ->nullable()
                    ->index();

                $table->timestamp('resolved_at')
                    ->nullable();

                $table->foreignId('resolved_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->longText(
                    'resolution_summary'
                )->nullable();

                $table->longText(
                    'root_cause'
                )->nullable();

                $table->longText(
                    'preventive_action'
                )->nullable();

                $table->timestamp('closed_at')
                    ->nullable();

                $table->foreignId('closed_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->unsignedInteger(
                    'reopen_count'
                )->default(0);

                $table->timestamp('reopened_at')
                    ->nullable();

                $table->foreignId('reopened_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->text('reopen_reason')
                    ->nullable();

                $table->foreignId('created_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->foreignId('updated_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->timestamps();
                $table->softDeletes();

                $table->index([
                    'project_id',
                    'status',
                    'priority',
                ]);

                $table->index([
                    'assigned_to',
                    'status',
                ]);

                $table->index([
                    'client_id',
                    'status',
                ]);

                $table->index([
                    'escalation_level',
                    'status',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('project_tickets');
    }
};