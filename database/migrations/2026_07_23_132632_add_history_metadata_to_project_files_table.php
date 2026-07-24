<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(
            'project_files',
            function (Blueprint $table): void {
                $table->boolean('is_private')
                    ->default(false)
                    ->after('disk')
                    ->index();

                $table->string(
                    'checksum_sha256',
                    64
                )
                    ->nullable()
                    ->after('is_private');

                $table->unsignedInteger(
                    'download_count'
                )
                    ->default(0)
                    ->after('checksum_sha256');

                $table->timestamp(
                    'last_downloaded_at'
                )
                    ->nullable()
                    ->after('download_count');
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'project_files',
            function (Blueprint $table): void {
                $table->dropIndex([
                    'is_private',
                ]);

                $table->dropColumn([
                    'is_private',
                    'checksum_sha256',
                    'download_count',
                    'last_downloaded_at',
                ]);
            }
        );
    }
};