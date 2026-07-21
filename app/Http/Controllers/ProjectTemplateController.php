<?php

namespace App\Http\Controllers;

use App\Enums\ProjectPriority;
use App\Enums\TaskPhase;
use App\Http\Requests\StoreProjectTemplateRequest;
use App\Http\Requests\UpdateProjectTemplateRequest;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\ProjectTemplate;
use App\Services\Projects\ProjectTemplateService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProjectTemplateController extends Controller
{
    public function index(): View
    {
        $templates = ProjectTemplate::query()
            ->with('category')
            ->withCount([
                'tasks',
                'projects',
            ])
            ->latest()
            ->paginate(15);

        return view(
            'project-templates.index',
            compact('templates')
        );
    }

    public function create(): View
    {
        return view(
            'project-templates.create',
            [
                ...$this->formData(),

                'projectTemplate' =>
                    new ProjectTemplate([
                        'default_duration_days' => 18,
                        'is_active' => true,
                    ]),
            ]
        );
    }

    public function store(
        StoreProjectTemplateRequest $request
    ): RedirectResponse {
        $validated = $request->validated();

        $taskData = $validated['tasks'];

        $templateData = Arr::except(
            $validated,
            ['tasks']
        );

        $template = DB::transaction(
            function () use (
                $templateData,
                $taskData,
                $request
            ): ProjectTemplate {
                $template = ProjectTemplate::create([
                    ...$templateData,
                    'slug' => $this->uniqueSlug(
                        $templateData['name']
                    ),
                    'created_by' =>
                        $request->user()->id,
                ]);

                foreach ($taskData as $index => $task) {
                    $template->tasks()->create([
                        ...$task,
                        'sort_order' => $index + 1,
                    ]);
                }

                return $template;
            }
        );

        return redirect()
            ->route('project-templates.edit', $template)
            ->with(
                'success',
                'Project template created successfully.'
            );
    }

    public function edit(
        ProjectTemplate $projectTemplate
    ): View {
        $projectTemplate->load('tasks');

        return view(
            'project-templates.edit',
            [
                ...$this->formData(),
                'projectTemplate' => $projectTemplate,
            ]
        );
    }

    public function update(
        UpdateProjectTemplateRequest $request,
        ProjectTemplate $projectTemplate
    ): RedirectResponse {
        $validated = $request->validated();

        $taskData = $validated['tasks'];

        $templateData = Arr::except(
            $validated,
            ['tasks']
        );

        DB::transaction(
            function () use (
                $projectTemplate,
                $templateData,
                $taskData
            ): void {
                $projectTemplate->update([
                    ...$templateData,
                    'slug' => $this->uniqueSlug(
                        $templateData['name'],
                        $projectTemplate->id
                    ),
                ]);

                /*
                 * Existing project tasks are not deleted.
                 * Their template_task link becomes null
                 * because of the nullOnDelete constraint.
                 */
                $projectTemplate->tasks()->delete();

                foreach ($taskData as $index => $task) {
                    $projectTemplate
                        ->tasks()
                        ->create([
                            ...$task,
                            'sort_order' => $index + 1,
                        ]);
                }
            }
        );

        return back()->with(
            'success',
            'Project template updated successfully.'
        );
    }

    public function destroy(
        ProjectTemplate $projectTemplate
    ): RedirectResponse {
        $projectTemplate->update([
            'is_active' => false,
        ]);

        $projectTemplate->delete();

        return redirect()
            ->route('project-templates.index')
            ->with(
                'success',
                'Project template archived.'
            );
    }

    public function apply(
        Request $request,
        Project $project,
        ProjectTemplate $projectTemplate,
        ProjectTemplateService $templateService
    ): RedirectResponse {
        abort_unless(
            $projectTemplate->is_active,
            404
        );

        $templateService->apply(
            project: $project,
            template: $projectTemplate,
            createdBy: $request->user()->id
        );

        return redirect()
            ->route('projects.show', $project)
            ->with(
                'success',
                'Project template applied successfully.'
            );
    }

    private function formData(): array
    {
        return [
            'categories' => ProjectCategory::query()
                ->active()
                ->get(),

            'phases' => TaskPhase::cases(),

            'priorities' =>
                ProjectPriority::cases(),
        ];
    }

    private function uniqueSlug(
        string $name,
        ?int $ignoreId = null
    ): string {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 2;

        while (
            ProjectTemplate::withTrashed()
                ->when(
                    $ignoreId,
                    fn ($query) =>
                        $query->whereKeyNot($ignoreId)
                )
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}