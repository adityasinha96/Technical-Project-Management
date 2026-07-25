<?php

use App\Enums\NotificationRecipientStrategy;
use App\Enums\NotificationSeverity;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'notification_rules',
            function (Blueprint $table): void {
                $table->id();

                $table->string(
                    'rule_key',
                    100
                )->unique();

                $table->string('name');

                $table->text(
                    'description'
                )->nullable();

                $table->string(
                    'event_key',
                    100
                )->index();

                $table->string(
                    'severity',
                    30
                )->default(
                    NotificationSeverity::Info->value
                );

                $table->string(
                    'recipient_strategy',
                    50
                )->default(
                    NotificationRecipientStrategy::ProjectManager->value
                );

                $table->json('channels');

                /*
                 * How many minutes before the due time
                 * this reminder becomes eligible.
                 */
                $table->integer(
                    'lead_minutes'
                )->default(0);

                /*
                 * Minimum time before repeating
                 * the reminder for the same subject.
                 */
                $table->unsignedInteger(
                    'repeat_minutes'
                )->default(1440);

                $table->unsignedInteger(
                    'maximum_occurrences'
                )->default(30);

                $table->boolean('is_enabled')
                    ->default(true)
                    ->index();

                $table->json(
                    'configuration'
                )->nullable();

                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'notification_rules'
        );
    }
};