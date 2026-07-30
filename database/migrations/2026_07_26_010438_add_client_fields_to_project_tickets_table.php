<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(
            'project_tickets',
            function (Blueprint $table): void {
                $table->boolean(
                    'client_visible'
                )
                    ->default(false)
                    ->index();

                $table->boolean(
                    'client_can_reply'
                )->default(true);

                $table->foreignId(
                    'submitted_by_client_user_id'
                )
                    ->nullable()
                    ->constrained('client_users')
                    ->nullOnDelete();

                $table->timestamp(
                    'client_last_replied_at'
                )->nullable();

                $table->timestamp(
                    'client_closed_at'
                )->nullable();
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'project_tickets',
            function (Blueprint $table): void {
                $table->dropForeign([
                    'submitted_by_client_user_id',
                ]);

                $table->dropIndex([
                    'client_visible',
                ]);

                $table->dropColumn([
                    'client_visible',
                    'client_can_reply',
                    'submitted_by_client_user_id',
                    'client_last_replied_at',
                    'client_closed_at',
                ]);
            }
        );
    }
};