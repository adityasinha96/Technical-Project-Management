<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'ticket_status_histories',
            function (Blueprint $table): void {
                $table->id();

                $table->foreignId(
                    'project_ticket_id'
                )
                    ->constrained('project_tickets')
                    ->cascadeOnDelete();

                $table->string(
                    'from_status',
                    40
                )->nullable();

                $table->string('to_status', 40);

                $table->foreignId('changed_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->text('reason')->nullable();
                $table->json('metadata')->nullable();

                $table->timestamp('changed_at')
                    ->index();

                $table->timestamps();

                $table->index([
                    'project_ticket_id',
                    'changed_at',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'ticket_status_histories'
        );
    }
};