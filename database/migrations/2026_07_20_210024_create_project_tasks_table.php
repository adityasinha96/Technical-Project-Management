<?php

use App\Enums\ProjectPriority;
use App\Enums\TaskPhase;
use App\Enums\TaskStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_tasks', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('project_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('project_template_task_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('assigned_to')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('title');
            $table->text('description')->nullable();

            $table->string('phase', 30)
                ->default(TaskPhase::General->value)
                ->index();

            $table->string('priority', 30)
                ->default(ProjectPriority::Medium->value)
                ->index();

            $table->string('status', 30)
                ->default(TaskStatus::NotStarted->value)
                ->index();

            $table->decimal('weight', 6, 2)
                ->default(1);

            $table->unsignedTinyInteger('progress')
                ->default(0);

            $table->decimal('estimated_hours', 8, 2)
                ->nullable();

            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable();

            $table->timestamp('completed_at')->nullable();

            $table->text('blocked_reason')->nullable();

            $table->unsignedInteger('sort_order')
                ->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index([
                'project_id',
                'status',
            ]);

            $table->index([
                'project_id',
                'phase',
            ]);

            $table->index([
                'project_id',
                'due_date',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_tasks');
    }
};