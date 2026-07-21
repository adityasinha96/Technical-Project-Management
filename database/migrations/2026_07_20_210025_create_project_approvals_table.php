<?php

use App\Enums\ApprovalStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_approvals', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('project_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('stage', 30)->index();

            $table->unsignedSmallInteger('submission_number')
                ->default(1);

            $table->string('status', 30)
                ->default(ApprovalStatus::Submitted->value)
                ->index();

            $table->foreignId('submitted_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('submitted_at');

            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('reviewed_at')->nullable();

            $table->string('client_reviewer_name')->nullable();
            $table->string('client_reviewer_email')->nullable();
            $table->string('client_reviewer_phone', 30)->nullable();

            $table->text('submission_notes')->nullable();
            $table->text('client_remarks')->nullable();
            $table->text('internal_remarks')->nullable();

            $table->foreignId('proof_file_id')
                ->nullable()
                ->constrained('project_files')
                ->nullOnDelete();

            $table->timestamps();

            $table->unique([
                'project_id',
                'stage',
                'submission_number',
            ]);

            $table->index([
                'project_id',
                'stage',
                'status',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_approvals');
    }
};