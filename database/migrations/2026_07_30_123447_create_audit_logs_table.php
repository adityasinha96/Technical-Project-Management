<?php

use App\Enums\AuditCategory;
use App\Enums\AuditSeverity;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'audit_logs',
            function (Blueprint $table): void {
                $table->id();

                $table->uuid('audit_uuid')
                    ->unique();

                $table->unsignedBigInteger(
                    'sequence'
                )->unique();

                $table->string(
                    'event_type',
                    120
                )->index();

                $table->string(
                    'category',
                    40
                )
                    ->default(
                        AuditCategory::System->value
                    )
                    ->index();

                $table->string(
                    'severity',
                    30
                )
                    ->default(
                        AuditSeverity::Info->value
                    )
                    ->index();

                $table->nullableMorphs(
                    'actor'
                );

                $table->nullableMorphs(
                    'auditable'
                );

                $table->string(
                    'actor_name'
                )->nullable();

                $table->string(
                    'actor_email'
                )->nullable();

                $table->string(
                    'guard',
                    30
                )->nullable();

                $table->string(
                    'route_name'
                )->nullable();

                $table->string(
                    'request_method',
                    10
                )->nullable();

                $table->text(
                    'request_path'
                )->nullable();

                $table->string(
                    'ip_address',
                    45
                )->nullable();

                $table->text(
                    'user_agent'
                )->nullable();

                $table->char(
                    'session_id_hash',
                    64
                )->nullable();

                $table->json(
                    'old_values'
                )->nullable();

                $table->json(
                    'new_values'
                )->nullable();

                $table->json(
                    'metadata'
                )->nullable();

                $table->char(
                    'previous_hash',
                    64
                );

                $table->char(
                    'entry_hash',
                    64
                )->unique();

                $table->timestamp(
                    'occurred_at',
                    6
                )->index();

                $table->timestamp(
                    'created_at',
                    6
                )->useCurrent();

                $table->index([
                    'category',
                    'severity',
                    'occurred_at',
                ]);

                $table->index([
                    'actor_type',
                    'actor_id',
                    'occurred_at',
                ]);

                $table->index([
                    'auditable_type',
                    'auditable_id',
                    'occurred_at',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'audit_logs'
        );
    }
};