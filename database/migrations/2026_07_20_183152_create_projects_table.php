<?php

use App\Enums\ProjectPriority;
use App\Enums\ProjectStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table): void {
            $table->id();

            $table->string('project_code')->nullable()->unique();

            $table->foreignId('client_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('project_category_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('manager_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('name');
            $table->text('description')->nullable();

            $table->decimal('project_price', 14, 2)->default(0);
            $table->decimal('estimated_cost', 14, 2)->default(0);
            $table->char('currency', 3)->default('INR');

            $table->date('start_date');
            $table->date('expected_delivery_date');
            $table->date('revised_delivery_date')->nullable();
            $table->date('actual_completion_date')->nullable();

            $table->unsignedSmallInteger('maximum_duration_days')
                ->default(18);

            $table->string('status', 50)
                ->default(ProjectStatus::NewProject->value)
                ->index();

            $table->string('priority', 30)
                ->default(ProjectPriority::Medium->value)
                ->index();

            /*
             * This value will be controlled by the approval
             * workflow introduced in Phase 3.
             */
            $table->unsignedTinyInteger('official_progress')
                ->default(0);

            $table->text('payment_terms')->nullable();

            $table->string('reference_url')->nullable();
            $table->string('development_url')->nullable();
            $table->string('live_url')->nullable();

            $table->string('domain_name')->nullable();
            $table->string('hosting_provider')->nullable();
            $table->date('domain_expiry_date')->nullable();
            $table->date('hosting_expiry_date')->nullable();

            $table->text('internal_remarks')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index([
                'status',
                'expected_delivery_date',
            ]);

            $table->index([
                'client_id',
                'status',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};