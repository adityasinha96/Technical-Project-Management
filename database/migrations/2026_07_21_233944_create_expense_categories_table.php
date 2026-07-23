<?php

use App\Enums\ExpenseCategoryScope;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'expense_categories',
            function (Blueprint $table): void {
                $table->id();

                $table->string('name');
                $table->string('slug')->unique();

                $table->string('scope', 30)
                    ->default(
                        ExpenseCategoryScope::Both->value
                    )
                    ->index();

                $table->text('description')->nullable();

                $table->boolean('is_active')
                    ->default(true)
                    ->index();

                $table->unsignedInteger('sort_order')
                    ->default(0);

                $table->foreignId('created_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->timestamps();
                $table->softDeletes();

                $table->index([
                    'scope',
                    'is_active',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_categories');
    }
};