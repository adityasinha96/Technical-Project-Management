<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'ticket_escalations',
            function (Blueprint $table): void {
                $table->id();

                $table->foreignId(
                    'project_ticket_id'
                )
                    ->constrained('project_tickets')
                    ->cascadeOnDelete();

                /*
                 * Each reopen starts a new escalation cycle.
                 */
                $table->unsignedInteger('cycle')
                    ->default(0);

                $table->unsignedTinyInteger('level');

                /*
                 * dateTime is used instead of timestamp to avoid
                 * invalid implicit timestamp defaults on older
                 * MySQL and MariaDB configurations.
                 */
                $table->dateTime('due_at');
                $table->dateTime('triggered_at');

                $table->unsignedInteger(
                    'minutes_overdue'
                )->default(0);

                $table->text('reason')->nullable();

                $table->dateTime(
                    'acknowledged_at'
                )->nullable();

                $table->foreignId(
                    'acknowledged_by'
                )
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->text(
                    'acknowledgement_note'
                )->nullable();

                $table->timestamps();

                $table->unique(
                    [
                        'project_ticket_id',
                        'cycle',
                        'level',
                    ],
                    'ticket_escalation_cycle_unique'
                );

                $table->index([
                    'level',
                    'acknowledged_at',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'ticket_escalations'
        );
    }
};