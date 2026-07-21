<?php

use App\Enums\ProjectPriority;
use App\Enums\TaskPhase;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'project_template_tasks',
            function (Blueprint $table): void {
                $table->id();

                $table->foreignId('project_template_id')
                    ->constrained()
                    ->cascadeOnDelete();

                $table->string('title');
                $table->text('description')->nullable();

                $table->string('phase', 30)
                    ->default(TaskPhase::General->value)
                    ->index();

                $table->string('priority', 30)
                    ->default(ProjectPriority::Medium->value);

                /*
                 * Weights are relative. They do not technically
                 * have to total 100, although templates should
                 * normally be designed that way.
                 */
                $table->decimal('weight', 6, 2)
                    ->default(1);

                $table->decimal('estimated_hours', 8, 2)
                    ->nullable();

                $table->unsignedSmallInteger(
                    'default_duration_days'
                )->nullable();

                $table->unsignedInteger('sort_order')
                    ->default(0);

                $table->timestamps();

                $table->index([
                    'project_template_id',
                    'sort_order',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('project_template_tasks');
    }
};