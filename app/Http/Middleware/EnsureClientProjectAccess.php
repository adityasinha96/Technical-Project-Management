<?php

namespace App\Http\Middleware;

use App\Models\Project;
use App\Services\ClientPortal\ClientPortalAccessService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureClientProjectAccess
{
    public function __construct(
        private readonly ClientPortalAccessService $accessService
    ) {
    }

    public function handle(
        Request $request,
        Closure $next,
        string $capability = 'view'
    ): Response {
        $project =
            $request->route('project');

        if (!$project instanceof Project) {
            $project = Project::query()
                ->findOrFail($project);
        }

        $this->accessService->accessFor(
            clientUser:
                auth('client')->user(),

            project: $project,
            capability: $capability
        );

        return $next($request);
    }
}