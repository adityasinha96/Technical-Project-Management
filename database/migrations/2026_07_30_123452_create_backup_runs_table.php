<?php

use App\Enums\BackupStatus;
use App\Enums\BackupTrigger;
use App\Enums\BackupType;
use App\Enums\BackupVerificationStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'backup_runs',
            function (Blueprint $table): void {
                $table->id();
                $table->uuid('backup_uuid')->unique();

                $table->string(
                    'backup_type',
                    30
                )->default(
                    BackupType::Full->value
                );

                $table->string(
                    'trigger',
                    30
                )->default(
                    BackupTrigger::Scheduled->value
                );

                $table->string(
                    'status',
                    30
                )
                    ->default(
                        BackupStatus::Queued->value
                    )
                    ->index();

                $table->string(
                    'verification_status',
                    30
                )
                    ->default(
                        BackupVerificationStatus::Pending->value
                    )
                    ->index();

                $table->string('disk');
                $table->text('path')->nullable();
                $table->string('filename')->nullable();

                $table->unsignedBigInteger(
                    'size_bytes'
                )->nullable();

                $table->char(
                    'checksum_sha256',
                    64
                )->nullable();

                $table->boolean(
                    'is_encrypted'
                )->default(false);

                $table->string(
                    'encryption_method'
                )->nullable();

                $table->foreignId(
                    'requested_by'
                )
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->timestamp(
                    'queued_at'
                )->nullable();

                $table->timestamp(
                    'started_at'
                )->nullable();

                $table->timestamp(
                    'completed_at'
                )->nullable();

                $table->timestamp(
                    'failed_at'
                )->nullable();

                $table->timestamp(
                    'verified_at'
                )->nullable();

                $table->timestamp(
                    'retention_until'
                )->nullable();

                $table->longText(
                    'verification_message'
                )->nullable();

                $table->longText(
                    'error_message'
                )->nullable();

                $table->json(
                    'manifest'
                )->nullable();

                $table->timestamps();

                $table->index([
                    'status',
                    'completed_at',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'backup_runs'
        );
    }
};