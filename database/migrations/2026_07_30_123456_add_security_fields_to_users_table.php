<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->timestamp(
                'last_seen_at'
            )->nullable()->index();

            $table->timestamp(
                'password_changed_at'
            )->nullable();

            $table->boolean(
                'force_password_change'
            )->default(false);

            $table->timestamp(
                'account_locked_until'
            )->nullable();

            $table->unsignedInteger(
                'security_risk_score'
            )->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex([
                'last_seen_at',
            ]);

            $table->dropColumn([
                'last_seen_at',
                'password_changed_at',
                'force_password_change',
                'account_locked_until',
                'security_risk_score',
            ]);
        });
    }
};