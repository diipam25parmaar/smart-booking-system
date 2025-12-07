<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\WorkingTimeRule;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        Service::truncate();
        WorkingTimeRule::truncate();

        Service::create(['name' => 'Haircut (30 min)', 'duration_minutes' => 30]);
        Service::create(['name' => 'Massage (60 min)', 'duration_minutes' => 60]);

        foreach ([1,2,3,4,5] as $dow) {
            WorkingTimeRule::create([
                'day_of_week' => $dow,
                'start_time' => '09:00',
                'end_time' => '17:00',
                'is_active' => true,
            ]);
        }
    }
}
