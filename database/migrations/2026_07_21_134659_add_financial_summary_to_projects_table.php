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
                'net_received_amount',
                14,
                2
            )
                ->default(0)
                ->after('estimated_cost');

            $table->decimal(
                'pending_amount',
                14,
                2
            )
                ->default(0)
                ->after('net_received_amount');

            $table->decimal(
                'collection_percentage',
                7,
                2
            )
                ->default(0)
                ->after('pending_amount');

            $table->date('last_payment_date')
                ->nullable()
                ->after('collection_percentage');

            $table->index('pending_amount');
            $table->index('last_payment_date');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table): void {
            $table->dropIndex([
                'pending_amount',
            ]);

            $table->dropIndex([
                'last_payment_date',
            ]);

            $table->dropColumn([
                'net_received_amount',
                'pending_amount',
                'collection_percentage',
                'last_payment_date',
            ]);
        });
    }
};