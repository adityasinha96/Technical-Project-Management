<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'permission_change_logs',
            function (Blueprint $table): void {
                $table->id();
                $table->uuid('change_uuid')->unique();

                $table->string(
                    'action',
                    80
                )->index();

                $table->foreignId(
                    'target_user_id'
                )
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->foreignId(
                    'role_id'
                )
                    ->nullable()
                    ->constrained('roles')
                    ->nullOnDelete();

                $table->foreignId(
                    'permission_id'
                )
                    ->nullable()
                    ->constrained('permissions')
                    ->nullOnDelete();

                $table->string(
                    'target_user_name'
                )->nullable();

                $table->string(
                    'role_name'
                )->nullable();

                $table->string(
                    'permission_name'
                )->nullable();

                $table->foreignId(
                    'performed_by'
                )
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->json(
                    'before_values'
                )->nullable();

                $table->json(
                    'after_values'
                )->nullable();

                $table->string(
                    'ip_address',
                    45
                )->nullable();

                $table->text(
                    'user_agent'
                )->nullable();

                $table->timestamp(
                    'occurred_at'
                )->index();

                $table->timestamps();

                $table->index([
                    'target_user_id',
                    'occurred_at',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'permission_change_logs'
        );
    }
};