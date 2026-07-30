<?php

use App\Enums\ClientMessageSenderType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'client_communications',
            function (Blueprint $table): void {
                $table->id();

                $table->foreignId('project_id')
                    ->constrained()
                    ->cascadeOnDelete();

                $table->foreignId('client_user_id')
                    ->nullable()
                    ->constrained()
                    ->nullOnDelete();

                $table->foreignId('user_id')
                    ->nullable()
                    ->constrained()
                    ->nullOnDelete();

                $table->foreignId('reply_to_id')
                    ->nullable()
                    ->constrained(
                        'client_communications'
                    )
                    ->nullOnDelete();

                $table->string(
                    'sender_type',
                    30
                )->default(
                    ClientMessageSenderType::Client->value
                );

                $table->longText('message');

                $table->timestamp(
                    'client_read_at'
                )->nullable();

                $table->timestamp(
                    'internal_read_at'
                )->nullable();

                $table->timestamps();

                $table->index([
                    'project_id',
                    'created_at',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'client_communications'
        );
    }
};