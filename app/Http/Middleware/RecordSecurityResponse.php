<?php

namespace App\Http\Middleware;

use App\Enums\AuditCategory;
use App\Enums\AuditSeverity;
use App\Services\Audit\AuditLogService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RecordSecurityResponse
{
    public function __construct(
        private readonly AuditLogService $audit
    ) {
    }

    public function handle(
        Request $request,
        Closure $next
    ): Response {
        $response =
            $next($request);

        if (
            in_array(
                $response->getStatusCode(),
                [
                    401,
                    403,
                    419,
                    429,
                ],
                true
            )
        ) {
            $this->audit->record(
                eventType:
                    'security.http_access_denied',

                category:
                    AuditCategory::Security,

                severity:
                    $response->getStatusCode() === 429
                        ? AuditSeverity::High
                        : AuditSeverity::Warning,

                metadata: [
                    'status_code' =>
                        $response
                            ->getStatusCode(),

                    'route' =>
                        $request
                            ->route()
                            ?->getName(),

                    'path' =>
                        $request->path(),
                ]
            );
        }

        return $response;
    }
}