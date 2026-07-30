<?php

use App\Enums\ClientApprovalDecision;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(
            'project_approvals',
            function (Blueprint $table): void {
                $table->boolean(
                    'is_client_visible'
                )
                    ->default(false)
                    ->index();

                $table->timestamp(
                    'submitted_to_client_at'
                )->nullable();

                $table->foreignId(
                    'submitted_to_client_by'
                )
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->string(
                    'client_decision',
                    40
                )->default(
                    ClientApprovalDecision::Pending->value
                )->index();

                $table->longText(
                    'client_feedback'
                )->nullable();

                $table->timestamp(
                    'client_decided_at'
                )->nullable();

                $table->foreignId(
                    'client_decided_by'
                )
                    ->nullable()
                    ->constrained('client_users')
                    ->nullOnDelete();
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'project_approvals',
            function (Blueprint $table): void {
                $table->dropForeign([
                    'submitted_to_client_by',
                ]);

                $table->dropForeign([
                    'client_decided_by',
                ]);

                $table->dropIndex([
                    'is_client_visible',
                ]);

                $table->dropIndex([
                    'client_decision',
                ]);

                $table->dropColumn([
                    'is_client_visible',
                    'submitted_to_client_at',
                    'submitted_to_client_by',
                    'client_decision',
                    'client_feedback',
                    'client_decided_at',
                    'client_decided_by',
                ]);
            }
        );
    }
};