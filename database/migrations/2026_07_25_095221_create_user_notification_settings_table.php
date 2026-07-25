<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'user_notification_settings',
            function (Blueprint $table): void {
                $table->id();

                $table->foreignId('user_id')
                    ->unique()
                    ->constrained()
                    ->cascadeOnDelete();

                $table->boolean(
                    'in_app_notifications_enabled'
                )->default(true);

                $table->boolean(
                    'email_notifications_enabled'
                )->default(true);

                $table->boolean(
                    'daily_digest_enabled'
                )->default(true);

                $table->time(
                    'daily_digest_time'
                )->default('08:30:00');

                $table->string(
                    'timezone',
                    64
                )->default('Asia/Kolkata');

                $table->date(
                    'last_daily_digest_sent_on'
                )->nullable();

                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'user_notification_settings'
        );
    }
};