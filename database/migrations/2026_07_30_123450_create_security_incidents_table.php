<?php

use App\Enums\AuditSeverity;
use App\Enums\SecurityIncidentStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'security_incidents',
            function (Blueprint $table): void {
                $table->id();
                $table->uuid('incident_uuid')->unique();

                $table->string(
                    'incident_type',
                    80
                )->index();

                $table->string(
                    'severity',
                    30
                )
                    ->default(
                        AuditSeverity::Warning->value
                    )
                    ->index();

                $table->string(
                    'status',
                    30
                )
                    ->default(
                        SecurityIncidentStatus::Open->value
                    )
                    ->index();

                $table->char(
                    'fingerprint',
                    64
                )->index();

                $table->string('title');
                $table->longText('description');

                $table->nullableMorphs(
                    'subject'
                );

                $table->foreignId(
                    'login_event_id'
                )
                    ->nullable()
                    ->constrained()
                    ->nullOnDelete();

                $table->unsignedInteger(
                    'occurrence_count'
                )->default(1);

                $table->timestamp(
                    'detected_at'
                )->index();

                $table->timestamp(
                    'last_seen_at'
                )->nullable();

                $table->foreignId(
                    'assigned_to'
                )
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->foreignId(
                    'acknowledged_by'
                )
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->timestamp(
                    'acknowledged_at'
                )->nullable();

                $table->foreignId(
                    'resolved_by'
                )
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->timestamp(
                    'resolved_at'
                )->nullable();

                $table->longText(
                    'resolution_notes'
                )->nullable();

                $table->json(
                    'metadata'
                )->nullable();

                $table->timestamps();

                $table->index([
                    'status',
                    'severity',
                    'detected_at',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'security_incidents'
        );
    }
};