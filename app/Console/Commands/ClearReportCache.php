<?php

namespace App\Console\Commands;

use App\Services\Reports\ReportCacheService;
use Illuminate\Console\Command;

class ClearReportCache extends Command
{
    protected $signature =
        'reports:clear-cache';

    protected $description =
        'Invalidate all cached report summaries';

    public function handle(
        ReportCacheService $cache
    ): int {
        $version = $cache->invalidate();

        $this->info(
            "Report cache invalidated. Current cache version: {$version}."
        );

        return self::SUCCESS;
    }
}