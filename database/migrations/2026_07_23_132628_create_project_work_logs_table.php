<?php

use App\Enums\WorkLogStatus;
use App\Enums\WorkLogType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'project_work_logs',
            function (Blueprint $table): void {
                $table->id();

                $table->foreignId('project_id')
                    ->constrained()
                    ->cascadeOnDelete();

                $table->foreignId('project_task_id')
                    ->nullable()
                    ->constrained('project_tasks')
                    ->nullOnDelete();

                $table->foreignId('logged_by')
                    ->constrained('users')
                    ->restrictOnDelete();

                $table->date('work_date')->index();

                $table->string('work_type', 40)
                    ->default(WorkLogType::Development->value)
                    ->index();

                $table->string('status', 30)
                    ->default(WorkLogStatus::Completed->value)
                    ->index();

                $table->string('title');

                $table->text('details')->nullable();
                $table->text('outcome')->nullable();
                $table->text('blocker')->nullable();

                $table->unsignedInteger('duration_minutes')
                    ->default(0);

                $table->boolean('is_billable')
                    ->default(false)
                    ->index();

                $table->timestamps();
                $table->softDeletes();

                $table->index([
                    'project_id',
                    'work_date',
                ]);

                $table->index([
                    'project_id',
                    'logged_by',
                ]);

                $table->index([
                    'project_task_id',
                    'work_date',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('project_work_logs');
    }
};