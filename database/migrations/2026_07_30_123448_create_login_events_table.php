<?php

use App\Enums\LoginEventType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'login_events',
            function (Blueprint $table): void {
                $table->id();
                $table->uuid('event_uuid')->unique();

                $table->string(
                    'event_type',
                    40
                )
                    ->default(
                        LoginEventType::Login->value
                    )
                    ->index();

                $table->string(
                    'guard',
                    30
                )->nullable();

                $table->nullableMorphs(
                    'authenticatable'
                );

                $table->char(
                    'identifier_hash',
                    64
                )->nullable()->index();

                $table->string(
                    'identifier_masked'
                )->nullable();

                $table->boolean(
                    'successful'
                )->default(false)->index();

                $table->string(
                    'ip_address',
                    45
                )->nullable()->index();

                $table->text(
                    'user_agent'
                )->nullable();

                $table->char(
                    'device_fingerprint',
                    64
                )->nullable()->index();

                $table->char(
                    'session_id_hash',
                    64
                )->nullable();

                $table->unsignedTinyInteger(
                    'risk_score'
                )->default(0);

                $table->string(
                    'failure_reason'
                )->nullable();

                $table->json(
                    'metadata'
                )->nullable();

                $table->timestamp(
                    'occurred_at',
                    6
                )->index();

                $table->timestamps();

                $table->index([
                    'event_type',
                    'ip_address',
                    'occurred_at',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'login_events'
        );
    }
};