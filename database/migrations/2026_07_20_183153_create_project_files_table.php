<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_files', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('project_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('uploaded_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('category', 50)->default('general');

            $table->string('original_name');
            $table->string('stored_name');
            $table->string('path');
            $table->string('disk', 30)->default('public');

            $table->string('mime_type', 150)->nullable();
            $table->unsignedBigInteger('size')->default(0);

            $table->text('description')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index([
                'project_id',
                'category',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_files');
    }
};