<?php

namespace Database\Seeders;

use App\Enums\TicketPriority;
use App\Models\TicketSlaPolicy;
use Illuminate\Database\Seeder;

class TicketSlaPolicySeeder extends Seeder
{
    public function run(): void
    {
        $policies = [
            TicketPriority::Low->value => [
                'first_response_minutes' => 480,
                'resolution_minutes' => 4320,
                'warning_before_minutes' => 120,
                'level_two_after_minutes' => 240,
                'level_three_after_minutes' => 720,
            ],

            TicketPriority::Medium->value => [
                'first_response_minutes' => 240,
                'resolution_minutes' => 1440,
                'warning_before_minutes' => 60,
                'level_two_after_minutes' => 120,
                'level_three_after_minutes' => 360,
            ],

            TicketPriority::High->value => [
                'first_response_minutes' => 120,
                'resolution_minutes' => 480,
                'warning_before_minutes' => 45,
                'level_two_after_minutes' => 60,
                'level_three_after_minutes' => 180,
            ],

            TicketPriority::Urgent->value => [
                'first_response_minutes' => 60,
                'resolution_minutes' => 240,
                'warning_before_minutes' => 30,
                'level_two_after_minutes' => 30,
                'level_three_after_minutes' => 90,
            ],

            TicketPriority::Critical->value => [
                'first_response_minutes' => 30,
                'resolution_minutes' => 120,
                'warning_before_minutes' => 15,
                'level_two_after_minutes' => 15,
                'level_three_after_minutes' => 45,
            ],
        ];

        foreach (
            $policies as $priority => $data
        ) {
            TicketSlaPolicy::updateOrCreate(
                [
                    'priority' => $priority,
                ],
                [
                    ...$data,
                    'is_active' => true,
                ]
            );
        }
    }
}