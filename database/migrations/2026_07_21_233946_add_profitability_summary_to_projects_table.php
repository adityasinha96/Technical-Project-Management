<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table): void {
            $table->decimal(
                'project_expense_amount',
                14,
                2
            )
                ->default(0)
                ->after('estimated_cost');

            $table->decimal(
                'actual_profit_amount',
                14,
                2
            )
                ->default(0)
                ->after('project_expense_amount');

            $table->decimal(
                'profit_margin_percentage',
                8,
                2
            )
                ->default(0)
                ->after('actual_profit_amount');

            $table->decimal(
                'cash_position_amount',
                14,
                2
            )
                ->default(0)
                ->after('profit_margin_percentage');

            $table->index('project_expense_amount');
            $table->index('actual_profit_amount');
            $table->index('cash_position_amount');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table): void {
            $table->dropIndex([
                'project_expense_amount',
            ]);

            $table->dropIndex([
                'actual_profit_amount',
            ]);

            $table->dropIndex([
                'cash_position_amount',
            ]);

            $table->dropColumn([
                'project_expense_amount',
                'actual_profit_amount',
                'profit_margin_percentage',
                'cash_position_amount',
            ]);
        });
    }
};