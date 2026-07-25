<?php

use App\Enums\NotificationDeliveryStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'notification_dispatches',
            function (Blueprint $table): void {
                $table->id();

                $table->uuid('batch_uuid')
                    ->index();

                $table->foreignId('user_id')
                    ->constrained()
                    ->cascadeOnDelete();

                $table->string(
                    'event_key',
                    100
                )->index();

                $table->nullableMorphs(
                    'subject'
                );

                $table->string(
                    'channel',
                    30
                )->index();

                $table->string(
                    'dedupe_key',
                    64
                )->unique();

                $table->string(
                    'status',
                    30
                )->default(
                    NotificationDeliveryStatus::Queued->value
                )->index();

                $table->json('payload')
                    ->nullable();

                $table->timestamp(
                    'scheduled_for'
                )->nullable();

                $table->timestamp(
                    'sent_at'
                )->nullable();

                $table->timestamp(
                    'failed_at'
                )->nullable();

                $table->text(
                    'error_message'
                )->nullable();

                $table->timestamps();

                $table->index([
                    'user_id',
                    'event_key',
                    'created_at',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'notification_dispatches'
        );
    }
};