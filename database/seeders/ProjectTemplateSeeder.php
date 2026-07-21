<?php

namespace Database\Seeders;

use App\Enums\ProjectPriority;
use App\Enums\TaskPhase;
use App\Models\ProjectCategory;
use App\Models\ProjectTemplate;
use Illuminate\Database\Seeder;

class ProjectTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $categoryId = ProjectCategory::query()
            ->where('slug', 'dynamic-website')
            ->value('id');

        $template = ProjectTemplate::updateOrCreate(
            [
                'slug' => 'standard-website-project',
            ],
            [
                'project_category_id' => $categoryId,
                'name' => 'Standard Website Project',
                'description' =>
                    'Standard frontend, backend, testing and deployment workflow for small website projects.',
                'default_duration_days' => 18,
                'is_active' => true,
            ]
        );

        $tasks = [
            [
                'title' => 'Project planning and requirement review',
                'phase' => TaskPhase::Planning,
                'weight' => 5,
                'duration' => 1,
            ],
            [
                'title' => 'Collect content, logo and access details',
                'phase' => TaskPhase::Planning,
                'weight' => 5,
                'duration' => 1,
            ],
            [
                'title' => 'Homepage UI design and frontend',
                'phase' => TaskPhase::Frontend,
                'weight' => 15,
                'duration' => 3,
            ],
            [
                'title' => 'Inner pages frontend development',
                'phase' => TaskPhase::Frontend,
                'weight' => 15,
                'duration' => 3,
            ],
            [
                'title' => 'Responsive frontend optimisation',
                'phase' => TaskPhase::Frontend,
                'weight' => 5,
                'duration' => 1,
            ],
            [
                'title' => 'Frontend testing and submission',
                'phase' => TaskPhase::Testing,
                'weight' => 5,
                'duration' => 1,
            ],
            [
                'title' => 'Laravel backend foundation',
                'phase' => TaskPhase::Backend,
                'weight' => 10,
                'duration' => 2,
            ],
            [
                'title' => 'Admin panel and content modules',
                'phase' => TaskPhase::Backend,
                'weight' => 15,
                'duration' => 2,
            ],
            [
                'title' => 'Forms, APIs and integrations',
                'phase' => TaskPhase::Backend,
                'weight' => 10,
                'duration' => 1,
            ],
            [
                'title' => 'Backend testing and corrections',
                'phase' => TaskPhase::Testing,
                'weight' => 5,
                'duration' => 1,
            ],
            [
                'title' => 'Backend submission and client review',
                'phase' => TaskPhase::Testing,
                'weight' => 5,
                'duration' => 1,
            ],
            [
                'title' => 'Deployment and project handover',
                'phase' => TaskPhase::Deployment,
                'weight' => 5,
                'duration' => 1,
            ],
        ];

        $template->tasks()->delete();

        foreach ($tasks as $index => $task) {
            $template->tasks()->create([
                'title' => $task['title'],
                'phase' => $task['phase']->value,

                'priority' =>
                    ProjectPriority::Medium->value,

                'weight' => $task['weight'],

                'default_duration_days' =>
                    $task['duration'],

                'sort_order' => $index + 1,
            ]);
        }
    }
}