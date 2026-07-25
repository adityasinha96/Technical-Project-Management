<?php

use App\Enums\ReportExportStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'report_exports',
            function (Blueprint $table): void {
                $table->id();

                $table->uuid('export_uuid')
                    ->unique();

                $table->string(
                    'report_type',
                    50
                )->index();

                $table->string(
                    'format',
                    20
                )->default('csv');

                $table->json('filters');

                $table->string('filename');

                $table->foreignId(
                    'generated_by'
                )
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->string(
                    'status',
                    30
                )->default(
                    ReportExportStatus::Processing->value
                )->index();

                $table->unsignedBigInteger(
                    'rows_exported'
                )->default(0);

                $table->timestamp(
                    'started_at'
                );

                $table->timestamp(
                    'completed_at'
                )->nullable();

                $table->timestamp(
                    'failed_at'
                )->nullable();

                $table->text(
                    'error_message'
                )->nullable();

                $table->string(
                    'ip_address',
                    45
                )->nullable();

                $table->text(
                    'user_agent'
                )->nullable();

                $table->timestamps();

                $table->index([
                    'generated_by',
                    'created_at',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'report_exports'
        );
    }
};