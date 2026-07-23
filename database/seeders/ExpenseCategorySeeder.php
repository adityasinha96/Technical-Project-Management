<?php

namespace Database\Seeders;

use App\Enums\ExpenseCategoryScope;
use App\Models\ExpenseCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ExpenseCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Domain and SSL',
                'scope' =>
                    ExpenseCategoryScope::Project,
            ],
            [
                'name' => 'Hosting and Server',
                'scope' =>
                    ExpenseCategoryScope::Both,
            ],
            [
                'name' => 'Freelancer and Outsourcing',
                'scope' =>
                    ExpenseCategoryScope::Project,
            ],
            [
                'name' => 'Design Assets',
                'scope' =>
                    ExpenseCategoryScope::Project,
            ],
            [
                'name' => 'API and Software License',
                'scope' =>
                    ExpenseCategoryScope::Both,
            ],
            [
                'name' => 'Project Travel and Meeting',
                'scope' =>
                    ExpenseCategoryScope::Project,
            ],
            [
                'name' => 'Salaries and Stipends',
                'scope' =>
                    ExpenseCategoryScope::Business,
            ],
            [
                'name' => 'Office Rent',
                'scope' =>
                    ExpenseCategoryScope::Business,
            ],
            [
                'name' => 'Electricity',
                'scope' =>
                    ExpenseCategoryScope::Business,
            ],
            [
                'name' => 'Internet and Telephone',
                'scope' =>
                    ExpenseCategoryScope::Business,
            ],
            [
                'name' => 'Marketing and Advertising',
                'scope' =>
                    ExpenseCategoryScope::Business,
            ],
            [
                'name' => 'Office Supplies',
                'scope' =>
                    ExpenseCategoryScope::Business,
            ],
            [
                'name' => 'Professional Fees',
                'scope' =>
                    ExpenseCategoryScope::Business,
            ],
            [
                'name' => 'Bank and Payment Charges',
                'scope' =>
                    ExpenseCategoryScope::Both,
            ],
            [
                'name' => 'Taxes and Government Fees',
                'scope' =>
                    ExpenseCategoryScope::Both,
            ],
            [
                'name' => 'Miscellaneous',
                'scope' =>
                    ExpenseCategoryScope::Both,
            ],
        ];

        foreach (
            $categories as $index => $category
        ) {
            ExpenseCategory::updateOrCreate(
                [
                    'slug' => Str::slug(
                        $category['name']
                    ),
                ],
                [
                    'name' => $category['name'],

                    'scope' =>
                        $category['scope']->value,

                    'is_active' => true,
                    'sort_order' => $index + 1,
                ]
            );
        }
    }
}