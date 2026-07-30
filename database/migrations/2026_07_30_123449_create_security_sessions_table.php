<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'security_sessions',
            function (Blueprint $table): void {
                $table->id();
                $table->uuid('session_uuid')->unique();

                $table->string(
                    'guard',
                    30
                )->index();

                $table->morphs('actor');

                $table->char(
                    'session_id_hash',
                    64
                )->unique();

                /*
                 * Encrypted with Laravel's encrypted cast.
                 */
                $table->longText(
                    'session_id'
                );

                $table->string(
                    'ip_address',
                    45
                )->nullable();

                $table->text(
                    'user_agent'
                )->nullable();

                $table->char(
                    'device_fingerprint',
                    64
                )->nullable();

                $table->timestamp(
                    'logged_in_at'
                )->nullable();

                $table->timestamp(
                    'last_seen_at'
                )->nullable()->index();

                $table->timestamp(
                    'logged_out_at'
                )->nullable();

                $table->timestamp(
                    'revoked_at'
                )->nullable()->index();

                $table->foreignId(
                    'revoked_by'
                )
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->string(
                    'revoke_reason'
                )->nullable();

                $table->timestamps();

                $table->index([
                    'actor_type',
                    'actor_id',
                    'last_seen_at',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'security_sessions'
        );
    }
};