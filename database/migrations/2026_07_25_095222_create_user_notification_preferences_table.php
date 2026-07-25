<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'user_notification_preferences',
            function (Blueprint $table): void {
                $table->id();

                $table->foreignId('user_id')
                    ->constrained()
                    ->cascadeOnDelete();

                $table->string(
                    'event_key',
                    100
                );

                $table->boolean(
                    'in_app_enabled'
                )->default(true);

                $table->boolean(
                    'email_enabled'
                )->default(true);

                $table->boolean(
                    'include_in_daily_digest'
                )->default(true);

                $table->timestamps();

                $table->unique([
                    'user_id',
                    'event_key',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'user_notification_preferences'
        );
    }
};