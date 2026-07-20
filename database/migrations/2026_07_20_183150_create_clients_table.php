<?php

use App\Enums\ClientStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table): void {
            $table->id();

            $table->string('client_code')->nullable()->unique();

            $table->string('name');
            $table->string('company_name')->nullable();

            $table->string('email')->nullable()->index();
            $table->string('phone', 30)->nullable()->index();
            $table->string('whatsapp', 30)->nullable();

            $table->string('gst_number', 30)->nullable()->index();

            $table->string('client_type', 50)->default('business');
            $table->string('status', 30)
                ->default(ClientStatus::Active->value)
                ->index();

            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('pincode', 20)->nullable();

            $table->text('notes')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['name', 'company_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};