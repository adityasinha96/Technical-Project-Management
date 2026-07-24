<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            SystemSettingsSeeder::class,
            ProjectCategorySeeder::class,
            ProjectTemplateSeeder::class,
            ExpenseCategorySeeder::class,
            TicketSlaPolicySeeder::class,
        ]);
    }
}