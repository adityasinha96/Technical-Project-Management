<?php

namespace App\Http\Controllers;

use App\Enums\ClientStatus;
use App\Enums\ProjectStatus;
use App\Models\Client;
use App\Models\Project;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total_clients' => Client::query()->count(),

            'active_clients' => Client::query()
                ->where('status', ClientStatus::Active->value)
                ->count(),

            'total_projects' => Project::query()->count(),

            'active_projects' => Project::query()
                ->open()
                ->count(),

            'completed_projects' => Project::query()
                ->where(
                    'status',
                    ProjectStatus::Completed->value
                )
                ->count(),

            'delayed_projects' => Project::query()
                ->open()
                ->whereRaw(
                    'COALESCE(revised_delivery_date, expected_delivery_date) < ?',
                    [today()->toDateString()]
                )
                ->count(),

            'total_project_value' => Project::query()
                ->sum('project_price'),

            'estimated_profit' => Project::query()
                ->selectRaw(
                    'COALESCE(SUM(project_price - estimated_cost), 0) AS total'
                )
                ->value('total') ?? 0,
        ];

        $delayedProjects = Project::query()
            ->with([
                'client',
                'manager',
            ])
            ->open()
            ->whereRaw(
                'COALESCE(revised_delivery_date, expected_delivery_date) < ?',
                [today()->toDateString()]
            )
            ->orderByRaw(
                'COALESCE(revised_delivery_date, expected_delivery_date) ASC'
            )
            ->limit(6)
            ->get();

        $recentProjects = Project::query()
            ->with([
                'client',
                'manager',
            ])
            ->latest()
            ->limit(6)
            ->get();

        return view('dashboard', [
            'stats' => $stats,
            'delayedProjects' => $delayedProjects,
            'recentProjects' => $recentProjects,
        ]);
    }
}