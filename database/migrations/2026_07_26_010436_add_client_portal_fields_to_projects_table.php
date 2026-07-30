<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table): void {
            $table->boolean(
                'client_portal_enabled'
            )
                ->default(false)
                ->after('status')
                ->index();

            $table->text(
                'client_portal_summary'
            )
                ->nullable()
                ->after('client_portal_enabled');

            $table->timestamp(
                'client_portal_enabled_at'
            )->nullable();

            $table->foreignId(
                'client_portal_enabled_by'
            )
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table): void {
            $table->dropForeign([
                'client_portal_enabled_by',
            ]);

            $table->dropIndex([
                'client_portal_enabled',
            ]);

            $table->dropColumn([
                'client_portal_enabled',
                'client_portal_summary',
                'client_portal_enabled_at',
                'client_portal_enabled_by',
            ]);
        });
    }
};