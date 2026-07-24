<?php

use App\Enums\ProjectNoteType;
use App\Enums\ProjectNoteVisibility;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_notes', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('project_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('title')->nullable();

            $table->string('note_type', 40)
                ->default(ProjectNoteType::General->value)
                ->index();

            $table->string('visibility', 30)
                ->default(ProjectNoteVisibility::Team->value)
                ->index();

            $table->longText('content');

            $table->boolean('is_pinned')
                ->default(false)
                ->index();

            $table->timestamp('pinned_at')->nullable();

            $table->foreignId('pinned_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index([
                'project_id',
                'is_pinned',
                'created_at',
            ]);

            $table->index([
                'project_id',
                'visibility',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_notes');
    }
};