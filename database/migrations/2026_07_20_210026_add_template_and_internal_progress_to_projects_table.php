<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table): void {
            $table->foreignId('project_template_id')
                ->nullable()
                ->after('project_category_id')
                ->constrained('project_templates')
                ->nullOnDelete();

            $table->unsignedTinyInteger('internal_progress')
                ->default(0)
                ->after('official_progress');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table): void {
            $table->dropConstrainedForeignId(
                'project_template_id'
            );

            $table->dropColumn('internal_progress');
        });
    }
};