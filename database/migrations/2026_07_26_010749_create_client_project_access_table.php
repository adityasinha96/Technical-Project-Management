<?php

use App\Enums\ClientProjectRole;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'client_project_access',
            function (Blueprint $table): void {
                $table->id();

                $table->foreignId('client_user_id')
                    ->constrained()
                    ->cascadeOnDelete();

                $table->foreignId('project_id')
                    ->constrained()
                    ->cascadeOnDelete();

                $table->string('role', 40)
                    ->default(
                        ClientProjectRole::Viewer->value
                    );

                $table->boolean('can_view_project')
                    ->default(true);

                $table->boolean('can_view_financials')
                    ->default(false);

                $table->boolean('can_approve')
                    ->default(false);

                $table->boolean('can_submit_tickets')
                    ->default(false);

                $table->boolean('can_view_files')
                    ->default(true);

                $table->boolean('can_communicate')
                    ->default(true);

                $table->boolean('is_active')
                    ->default(true)
                    ->index();

                $table->foreignId('granted_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->timestamp('granted_at')
                    ->nullable();

                $table->timestamp('revoked_at')
                    ->nullable();

                $table->foreignId('revoked_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->timestamps();

                $table->unique([
                    'client_user_id',
                    'project_id',
                ]);

                $table->index([
                    'project_id',
                    'is_active',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'client_project_access'
        );
    }
};