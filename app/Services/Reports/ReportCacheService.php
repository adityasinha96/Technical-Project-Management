<?php

namespace App\Services\Reports;

use App\Support\Reports\ReportFilters;
use Closure;
use Illuminate\Support\Facades\Cache;

class ReportCacheService
{
    private const VERSION_KEY =
        'reports:cache-version';

    public function remember(
        string $reportKey,
        ReportFilters $filters,
        Closure $callback
    ): mixed {
        $version = Cache::get(
            self::VERSION_KEY,
            1
        );

        $key = implode(':', [
            'reports',
            $version,
            $reportKey,
            $filters->cacheHash(),
        ]);

        return Cache::flexible(
            $key,
            [
                60,
                300,
            ],
            $callback
        );
    }

    public function invalidate(): int
    {
        if (
            !Cache::has(
                self::VERSION_KEY
            )
        ) {
            Cache::forever(
                self::VERSION_KEY,
                1
            );
        }

        return Cache::increment(
            self::VERSION_KEY
        );
    }
}