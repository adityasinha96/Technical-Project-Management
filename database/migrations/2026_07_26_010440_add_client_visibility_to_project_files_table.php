<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(
            'project_files',
            function (Blueprint $table): void {
                $table->boolean(
                    'client_visible'
                )
                    ->default(false)
                    ->index();

                $table->timestamp(
                    'shared_with_client_at'
                )->nullable();

                $table->foreignId(
                    'shared_with_client_by'
                )
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->foreignId(
                    'uploaded_by_client_user_id'
                )
                    ->nullable()
                    ->constrained('client_users')
                    ->nullOnDelete();
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'project_files',
            function (Blueprint $table): void {
                $table->dropForeign([
                    'shared_with_client_by',
                ]);

                $table->dropForeign([
                    'uploaded_by_client_user_id',
                ]);

                $table->dropIndex([
                    'client_visible',
                ]);

                $table->dropColumn([
                    'client_visible',
                    'shared_with_client_at',
                    'shared_with_client_by',
                    'uploaded_by_client_user_id',
                ]);
            }
        );
    }
};