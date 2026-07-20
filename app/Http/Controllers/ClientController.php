<?php

namespace App\Http\Controllers;

use App\Enums\ClientStatus;
use App\Enums\ProjectStatus;
use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Models\Client;
use App\Models\Project;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClientController extends Controller
{
    public function index(Request $request): View
    {
        $clients = Client::query()
            ->withCount('projects')
            ->withSum('projects', 'project_price')
            ->search($request->string('search')->toString())
            ->when(
                $request->filled('status'),
                fn ($query) =>
                $query->where('status', $request->string('status'))
            )
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $summary = [
            'total_clients' => Client::query()->count(),

            'active_clients' => Client::query()
                ->where('status', ClientStatus::Active->value)
                ->count(),

            'prospects' => Client::query()
                ->where('status', ClientStatus::Prospect->value)
                ->count(),

            'project_value' => Project::query()
                ->sum('project_price'),
        ];

        return view('clients.index', [
            'clients' => $clients,
            'summary' => $summary,
            'statuses' => ClientStatus::cases(),
        ]);
    }

    public function create(): View
    {
        return view('clients.create', [
            'client' => new Client([
                'client_type' => 'business',
                'status' => ClientStatus::Active,
            ]),
            'statuses' => ClientStatus::cases(),
        ]);
    }

    public function store(
        StoreClientRequest $request
    ): RedirectResponse {
        $client = DB::transaction(
            function () use ($request): Client {
                $client = Client::create([
                    ...$request->validated(),
                    'created_by' => $request->user()->id,
                ]);

                $client->forceFill([
                    'client_code' => sprintf(
                        'CLI-%05d',
                        $client->id
                    ),
                ])->saveQuietly();

                return $client;
            }
        );

        return redirect()
            ->route('clients.show', $client)
            ->with('success', 'Client created successfully.');
    }

    public function show(Client $client): View
    {
        $client->load([
            'createdBy',
            'projects' => fn ($query) =>
                $query
                    ->with([
                        'category',
                        'manager',
                    ])
                    ->latest(),
        ]);

        $financials = [
            'project_count' => $client
                ->projects()
                ->count(),

            'contracted_value' => $client
                ->projects()
                ->sum('project_price'),

            'estimated_cost' => $client
                ->projects()
                ->sum('estimated_cost'),

            'completed_projects' => $client
                ->projects()
                ->where(
                    'status',
                    ProjectStatus::Completed->value
                )
                ->count(),
        ];

        return view('clients.show', [
            'client' => $client,
            'financials' => $financials,
        ]);
    }

    public function edit(Client $client): View
    {
        return view('clients.edit', [
            'client' => $client,
            'statuses' => ClientStatus::cases(),
        ]);
    }

    public function update(
        UpdateClientRequest $request,
        Client $client
    ): RedirectResponse {
        $client->update($request->validated());

        return redirect()
            ->route('clients.show', $client)
            ->with('success', 'Client updated successfully.');
    }

    public function destroy(
        Client $client
    ): RedirectResponse {
        $hasActiveProjects = $client
            ->projects()
            ->whereNotIn(
                'status',
                ProjectStatus::closedValues()
            )
            ->exists();

        if ($hasActiveProjects) {
            return back()->withErrors([
                'client' =>
                    'This client has active projects and cannot be deleted.',
            ]);
        }

        $client->delete();

        return redirect()
            ->route('clients.index')
            ->with('success', 'Client moved to archive.');
    }
}