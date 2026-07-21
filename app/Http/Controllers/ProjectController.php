<?php

namespace App\Http\Controllers;

use App\Enums\ApprovalStage;
use App\Enums\ApprovalStatus;
use App\Enums\PaymentFollowupChannel;
use App\Enums\PaymentFollowupStatus;
use App\Enums\PaymentKind;
use App\Enums\PaymentMode;
use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Enums\ProjectPriority;
use App\Enums\ProjectStatus;
use App\Enums\TaskPhase;
use App\Enums\TaskStatus;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Client;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\ProjectTemplate;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\Projects\ProjectTemplateService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{
    public function __construct(
        private readonly ProjectTemplateService $templateService
    ) {
    }

    public function index(Request $request): View
    {
        $dueSoonDays = (int) (
            SystemSetting::query()
                ->where('key', 'due_soon_days')
                ->value('value') ?: 3
        );

        $projects = Project::query()
            ->with([
                'client',
                'category',
                'manager',
            ])
            ->withCount('team')
            ->search(
                $request
                    ->string('search')
                    ->toString()
            )
            ->when(
                $request->filled('status'),
                fn ($query) => $query->where(
                    'status',
                    $request
                        ->string('status')
                        ->toString()
                )
            )
            ->when(
                $request->filled('priority'),
                fn ($query) => $query->where(
                    'priority',
                    $request
                        ->string('priority')
                        ->toString()
                )
            )
            ->when(
                $request->filled('client_id'),
                fn ($query) => $query->where(
                    'client_id',
                    $request->integer('client_id')
                )
            )
            ->when(
                $request->filled('category_id'),
                fn ($query) => $query->where(
                    'project_category_id',
                    $request->integer('category_id')
                )
            )
            ->when(
                $request->filled('manager_id'),
                fn ($query) => $query->where(
                    'manager_id',
                    $request->integer('manager_id')
                )
            )
            ->when(
                $request
                    ->string('deadline')
                    ->toString() === 'delayed',
                fn ($query) => $query
                    ->open()
                    ->whereRaw(
                        'COALESCE(revised_delivery_date, expected_delivery_date) < ?',
                        [
                            today()->toDateString(),
                        ]
                    )
            )
            ->when(
                $request
                    ->string('deadline')
                    ->toString() === 'due_soon',
                fn ($query) => $query
                    ->open()
                    ->whereRaw(
                        'COALESCE(revised_delivery_date, expected_delivery_date) BETWEEN ? AND ?',
                        [
                            today()->toDateString(),
                            today()
                                ->addDays($dueSoonDays)
                                ->toDateString(),
                        ]
                    )
            )
            ->orderByRaw(
                'CASE
                    WHEN COALESCE(
                        revised_delivery_date,
                        expected_delivery_date
                    ) < CURDATE()
                    AND status NOT IN (?, ?)
                    THEN 0
                    ELSE 1
                END',
                ProjectStatus::closedValues()
            )
            ->orderByRaw(
                'COALESCE(
                    revised_delivery_date,
                    expected_delivery_date
                ) ASC'
            )
            ->paginate(15)
            ->withQueryString();

        $summary = [
            'total_projects' => Project::query()
                ->count(),

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
                    [
                        today()->toDateString(),
                    ]
                )
                ->count(),

            'contracted_value' => Project::query()
                ->sum('project_price'),
        ];

        return view('projects.index', [
            'projects' => $projects,
            'summary' => $summary,

            'statuses' => ProjectStatus::cases(),
            'priorities' => ProjectPriority::cases(),

            'clients' => Client::query()
                ->orderBy('company_name')
                ->orderBy('name')
                ->get(),

            'categories' => ProjectCategory::query()
                ->active()
                ->get(),

            'managers' => User::query()
                ->where('status', 'active')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function create(): View
    {
        return view('projects.create', [
            ...$this->formData(),

            'project' => new Project([
                'currency' => 'INR',
                'status' => ProjectStatus::NewProject,
                'priority' => ProjectPriority::Medium,
                'start_date' => today(),
                'expected_delivery_date' =>
                    today()->addDays(18),
                'maximum_duration_days' => 18,
            ]),

            'selectedTeam' => [],
        ]);
    }

    public function store(
        StoreProjectRequest $request
    ): RedirectResponse {
        $validated = $request->validated();

        $teamIds = $validated['team_member_ids'] ?? [];

        $templateId =
            $validated['project_template_id'] ?? null;

        $projectData = Arr::except(
            $validated,
            [
                'team_member_ids',
                'project_template_id',
            ]
        );

        $project = DB::transaction(
            function () use (
                $projectData,
                $teamIds,
                $request
            ): Project {
                $project = Project::create([
                    ...$projectData,
                    'official_progress' => 0,
                    'created_by' => $request->user()->id,
                    'updated_by' => $request->user()->id,
                ]);

                $project->forceFill([
                    'project_code' => sprintf(
                        'UIP-%s-%05d',
                        $project->start_date->format('Y'),
                        $project->id
                    ),
                ])->saveQuietly();

                $this->syncTeam(
                    project: $project,
                    teamIds: $teamIds,
                    managerId: $project->manager_id,
                    assignedBy: $request->user()->id
                );

                return $project;
            }
        );

        if ($templateId) {
            $template = ProjectTemplate::query()
                ->active()
                ->findOrFail($templateId);

            $this->templateService->apply(
                project: $project,
                template: $template,
                createdBy: $request->user()->id
            );
        }

        return redirect()
            ->route(
                'projects.show',
                $project
            )
            ->with(
                'success',
                $templateId
                    ? 'Project created and template applied successfully.'
                    : 'Project created successfully.'
            );
    }

    public function show(Project $project): View
    {
        $project->load([
            /*
            |--------------------------------------------------------------------------
            | Core project relationships
            |--------------------------------------------------------------------------
            */

            'client',
            'category',
            'template',
            'manager',
            'team',

            /*
            |--------------------------------------------------------------------------
            | Tasks
            |--------------------------------------------------------------------------
            */

            'tasks.assignee',
            'tasks.createdBy',

            /*
            |--------------------------------------------------------------------------
            | Approvals
            |--------------------------------------------------------------------------
            */

            'approvals.submittedBy',
            'approvals.reviewedBy',
            'approvals.proofFile',

            /*
            |--------------------------------------------------------------------------
            | Payments
            |--------------------------------------------------------------------------
            */

            'payments.proofFile',
            'payments.createdBy',
            'payments.voidedBy',

            /*
            |--------------------------------------------------------------------------
            | Payment Follow-ups
            |--------------------------------------------------------------------------
            */

            'paymentFollowups.assignedTo',
            'paymentFollowups.createdBy',
            'paymentFollowups.completedBy',

            /*
            |--------------------------------------------------------------------------
            | Files and audit information
            |--------------------------------------------------------------------------
            */

            'files.uploadedBy',
            'createdBy',
            'updatedBy',
        ]);

        return view('projects.show', [
            'project' => $project,

            /*
            |--------------------------------------------------------------------------
            | Available Users
            |--------------------------------------------------------------------------
            */

            'availableUsers' => User::query()
                ->where('status', 'active')
                ->orderBy('name')
                ->get(),

            /*
            |--------------------------------------------------------------------------
            | Available Project Templates
            |--------------------------------------------------------------------------
            */

            'availableTemplates' =>
                ProjectTemplate::query()
                    ->active()
                    ->withCount('tasks')
                    ->get(),

            /*
            |--------------------------------------------------------------------------
            | Task Data
            |--------------------------------------------------------------------------
            */

            'taskPhases' => TaskPhase::cases(),
            'taskStatuses' => TaskStatus::cases(),
            'priorities' => ProjectPriority::cases(),

            /*
            |--------------------------------------------------------------------------
            | Approval Data
            |--------------------------------------------------------------------------
            */

            'approvalStages' => ApprovalStage::cases(),
            'approvalStatuses' =>
                ApprovalStatus::cases(),

            /*
            |--------------------------------------------------------------------------
            | Payment Data
            |--------------------------------------------------------------------------
            */

            'paymentKinds' => PaymentKind::cases(),
            'paymentTypes' => PaymentType::cases(),
            'paymentModes' => PaymentMode::cases(),
            'paymentStatuses' => PaymentStatus::cases(),

            /*
            |--------------------------------------------------------------------------
            | Payment Follow-up Data
            |--------------------------------------------------------------------------
            */

            'followupChannels' =>
                PaymentFollowupChannel::cases(),

            'followupStatuses' =>
                PaymentFollowupStatus::cases(),
        ]);
    }

    public function edit(Project $project): View
    {
        $project->load([
            'team',
            'template',
        ]);

        return view('projects.edit', [
            ...$this->formData(),

            'project' => $project,

            'selectedTeam' => $project
                ->team
                ->pluck('id')
                ->map(
                    fn ($id): int => (int) $id
                )
                ->all(),
        ]);
    }

    public function update(
        UpdateProjectRequest $request,
        Project $project
    ): RedirectResponse {
        $validated = $request->validated();

        $teamIds =
            $validated['team_member_ids'] ?? [];

        /*
         * The selected template is intentionally excluded.
         *
         * Editing general project information must not reapply
         * a template or replace the project's existing tasks.
         */
        $projectData = Arr::except(
            $validated,
            [
                'team_member_ids',
                'project_template_id',
            ]
        );

        DB::transaction(
            function () use (
                $project,
                $projectData,
                $teamIds,
                $request
            ): void {
                $project->update([
                    ...$projectData,
                    'updated_by' =>
                        $request->user()->id,
                ]);

                $this->syncTeam(
                    project: $project,
                    teamIds: $teamIds,
                    managerId: $project->manager_id,
                    assignedBy: $request->user()->id
                );
            }
        );

        return redirect()
            ->route(
                'projects.show',
                $project
            )
            ->with(
                'success',
                'Project updated successfully.'
            );
    }

    public function destroy(
        Project $project
    ): RedirectResponse {
        $project->delete();

        return redirect()
            ->route('projects.index')
            ->with(
                'success',
                'Project moved to archive.'
            );
    }

    private function formData(): array
    {
        return [
            'clients' => Client::query()
                ->where(
                    'status',
                    '!=',
                    'blocked'
                )
                ->orderBy('company_name')
                ->orderBy('name')
                ->get(),

            'categories' => ProjectCategory::query()
                ->active()
                ->get(),

            'users' => User::query()
                ->where('status', 'active')
                ->orderBy('name')
                ->get(),

            'templates' => ProjectTemplate::query()
                ->active()
                ->withCount('tasks')
                ->get(),

            'statuses' => ProjectStatus::cases(),
            'priorities' => ProjectPriority::cases(),
        ];
    }

    private function syncTeam(
        Project $project,
        array $teamIds,
        ?int $managerId,
        int $assignedBy
    ): void {
        $memberIds = collect($teamIds)
            ->filter()
            ->map(
                fn ($id): int => (int) $id
            );

        if ($managerId) {
            $memberIds->push($managerId);
        }

        $memberIds = $memberIds
            ->unique()
            ->values();

        $syncData = $memberIds
            ->mapWithKeys(
                fn (int $userId): array => [
                    $userId => [
                        'assignment_role' =>
                            $userId === $managerId
                                ? 'project_manager'
                                : 'team_member',

                        'assigned_by' => $assignedBy,
                        'assigned_at' => now(),
                    ],
                ]
            )
            ->all();

        $project
            ->team()
            ->sync($syncData);
    }
}