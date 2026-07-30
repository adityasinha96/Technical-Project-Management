<?php

use App\Enums\ClientUserStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_users', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('client_id')
                ->constrained()
                ->restrictOnDelete();

            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone', 30)->nullable();
            $table->string('designation')->nullable();

            $table->string('password')->nullable();

            $table->string('status', 30)
                ->default(ClientUserStatus::Invited->value)
                ->index();

            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip', 45)->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->index([
                'client_id',
                'status',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_users');
    }
};