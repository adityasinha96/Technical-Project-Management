<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class SystemSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            [
                'key' => 'company_name',
                'value' => 'UIPRO Corporation Pvt. Ltd.',
                'type' => 'string',
                'group' => 'company',
                'description' => 'Company name displayed inside the application.',
            ],
            [
                'key' => 'currency',
                'value' => 'INR',
                'type' => 'string',
                'group' => 'finance',
                'description' => 'Default application currency.',
            ],
            [
                'key' => 'currency_symbol',
                'value' => '₹',
                'type' => 'string',
                'group' => 'finance',
                'description' => 'Default currency symbol.',
            ],
            [
                'key' => 'default_project_duration_days',
                'value' => '18',
                'type' => 'integer',
                'group' => 'projects',
                'description' => 'Default number of days allowed for a project.',
            ],
            [
                'key' => 'due_soon_days',
                'value' => '3',
                'type' => 'integer',
                'group' => 'projects',
                'description' => 'Number of days before delivery when a due-soon warning appears.',
            ],
            [
                'key' => 'critical_delay_days',
                'value' => '5',
                'type' => 'integer',
                'group' => 'projects',
                'description' => 'Overdue days after which a project becomes critically delayed.',
            ],
            [
                'key' => 'project_inactivity_days',
                'value' => '3',
                'type' => 'integer',
                'group' => 'projects',
                'description' => 'Days without activity before an inactivity warning appears.',
            ],
            [
                'key' => 'daily_operational_cost',
                'value' => '0',
                'type' => 'decimal',
                'group' => 'finance',
                'description' => 'Estimated operational cost per delayed project day.',
            ],
            [
                'key' => 'timezone',
                'value' => 'Asia/Kolkata',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Application timezone.',
            ],
        ];

        foreach ($settings as $setting) {
            SystemSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}