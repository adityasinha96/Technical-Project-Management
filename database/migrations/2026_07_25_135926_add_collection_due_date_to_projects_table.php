<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table): void {
            $table->date('collection_due_date')
                ->nullable()
                ->after('expected_delivery_date')
                ->index();
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table): void {
            $table->dropIndex([
                'collection_due_date',
            ]);

            $table->dropColumn(
                'collection_due_date'
            );
        });
    }
};