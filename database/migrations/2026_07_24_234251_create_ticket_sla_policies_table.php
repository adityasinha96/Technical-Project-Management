<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'ticket_sla_policies',
            function (Blueprint $table): void {
                $table->id();

                $table->string('priority', 30)
                    ->unique();

                $table->unsignedInteger(
                    'first_response_minutes'
                );

                $table->unsignedInteger(
                    'resolution_minutes'
                );

                $table->unsignedInteger(
                    'warning_before_minutes'
                )->default(60);

                $table->unsignedInteger(
                    'level_two_after_minutes'
                )->default(60);

                $table->unsignedInteger(
                    'level_three_after_minutes'
                )->default(240);

                $table->boolean('is_active')
                    ->default(true)
                    ->index();

                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'ticket_sla_policies'
        );
    }
};