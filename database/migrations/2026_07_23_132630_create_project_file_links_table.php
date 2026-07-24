<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'project_file_links',
            function (Blueprint $table): void {
                $table->id();

                $table->foreignId('project_file_id')
                    ->constrained('project_files')
                    ->cascadeOnDelete();

                $table->morphs('fileable');

                $table->foreignId('created_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->timestamps();

                $table->unique(
                    [
                        'project_file_id',
                        'fileable_type',
                        'fileable_id',
                    ],
                    'project_file_links_unique'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('project_file_links');
    }
};