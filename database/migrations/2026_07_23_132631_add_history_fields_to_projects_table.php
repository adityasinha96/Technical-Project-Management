<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table): void {
            $table->timestamp('last_activity_at')
                ->nullable()
                ->after('actual_completion_date')
                ->index();
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table): void {
            $table->dropIndex([
                'last_activity_at',
            ]);

            $table->dropColumn('last_activity_at');
        });
    }
};