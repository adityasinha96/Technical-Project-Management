<?php

namespace Database\Seeders;

use App\Models\ProjectCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProjectCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Static Website',
                'description' => 'HTML, CSS or basic frontend website.',
            ],
            [
                'name' => 'Dynamic Website',
                'description' => 'Website with backend and administration.',
            ],
            [
                'name' => 'E-commerce Website',
                'description' => 'Online store and order management.',
            ],
            [
                'name' => 'Laravel Application',
                'description' => 'Custom Laravel web application.',
            ],
            [
                'name' => 'React Application',
                'description' => 'React-based frontend application.',
            ],
            [
                'name' => 'MERN Application',
                'description' => 'MongoDB, Express, React and Node.js.',
            ],
            [
                'name' => 'Mobile Application',
                'description' => 'Android, iOS or Flutter application.',
            ],
            [
                'name' => 'Digital Marketing',
                'description' => 'Advertising and campaign project.',
            ],
            [
                'name' => 'SEO',
                'description' => 'Search engine optimisation project.',
            ],
            [
                'name' => 'Maintenance',
                'description' => 'Website or application maintenance.',
            ],
            [
                'name' => 'Custom Software',
                'description' => 'Custom business software development.',
            ],
            [
                'name' => 'Other',
                'description' => 'Any other project category.',
            ],
        ];

        foreach ($categories as $index => $category) {
            ProjectCategory::updateOrCreate(
                [
                    'slug' => Str::slug($category['name']),
                ],
                [
                    ...$category,
                    'is_active' => true,
                    'sort_order' => $index + 1,
                ]
            );
        }
    }
}