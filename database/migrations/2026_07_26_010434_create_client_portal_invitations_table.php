<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'client_portal_invitations',
            function (Blueprint $table): void {
                $table->id();

                $table->foreignId('client_user_id')
                    ->constrained()
                    ->cascadeOnDelete();

                $table->foreignId('project_id')
                    ->constrained()
                    ->cascadeOnDelete();

                /*
                 * Store only the token hash.
                 * Never store the raw invitation token.
                 */
                $table->string(
                    'token_hash',
                    64
                )->unique();

                $table->timestamp('expires_at')
                    ->index();

                $table->timestamp('accepted_at')
                    ->nullable();

                $table->timestamp('cancelled_at')
                    ->nullable();

                $table->foreignId('invited_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->foreignId('cancelled_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->unsignedInteger(
                    'send_count'
                )->default(1);

                $table->timestamp(
                    'last_sent_at'
                )->nullable();

                $table->timestamps();

                $table->index([
                    'client_user_id',
                    'project_id',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'client_portal_invitations'
        );
    }
};