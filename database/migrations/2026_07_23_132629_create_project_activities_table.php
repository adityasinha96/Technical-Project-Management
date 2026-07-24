<?php

use App\Enums\ActivityVisibility;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'project_activities',
            function (Blueprint $table): void {
                $table->id();

                $table->foreignId('project_id')
                    ->constrained()
                    ->cascadeOnDelete();

                $table->foreignId('actor_id')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->string('event', 60)->index();

                $table->string('visibility', 30)
                    ->default(ActivityVisibility::Team->value)
                    ->index();

                $table->foreignId('visible_to_user_id')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->nullableMorphs('subject');

                $table->string('title');
                $table->text('description')->nullable();

                $table->json('old_values')->nullable();
                $table->json('new_values')->nullable();
                $table->json('metadata')->nullable();

                $table->timestamp('occurred_at')->index();

                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();

                $table->timestamps();

                $table->index([
                    'project_id',
                    'occurred_at',
                ]);

                $table->index([
                    'project_id',
                    'event',
                    'occurred_at',
                ]);

                $table->index([
                    'project_id',
                    'actor_id',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('project_activities');
    }
};