<?php

use App\Enums\TicketCommentType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'ticket_comments',
            function (Blueprint $table): void {
                $table->id();

                $table->foreignId(
                    'project_ticket_id'
                )
                    ->constrained('project_tickets')
                    ->cascadeOnDelete();

                $table->string(
                    'comment_type',
                    40
                )->default(
                    TicketCommentType::Discussion->value
                );

                $table->longText('message');

                $table->foreignId('created_by')
                    ->constrained('users')
                    ->restrictOnDelete();

                $table->foreignId('edited_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->timestamp('edited_at')
                    ->nullable();

                $table->timestamps();
                $table->softDeletes();

                $table->index([
                    'project_ticket_id',
                    'created_at',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_comments');
    }
};