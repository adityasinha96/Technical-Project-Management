<?php

use App\Enums\TicketCommentVisibility;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(
            'ticket_comments',
            function (Blueprint $table): void {
                $table->string(
                    'visibility',
                    30
                )
                    ->default(
                        TicketCommentVisibility::Internal->value
                    )
                    ->index();

                $table->foreignId(
                    'client_user_id'
                )
                    ->nullable()
                    ->constrained()
                    ->nullOnDelete();
            }
        );

        /*
         * Required only when created_by is currently
         * non-nullable in your Phase 7 migration.
         */
        Schema::table(
            'ticket_comments',
            function (Blueprint $table): void {
                $table->foreignId('created_by')
                    ->nullable()
                    ->change();
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'ticket_comments',
            function (Blueprint $table): void {
                $table->dropForeign([
                    'client_user_id',
                ]);

                $table->dropIndex([
                    'visibility',
                ]);

                $table->dropColumn([
                    'visibility',
                    'client_user_id',
                ]);
            }
        );
    }
};