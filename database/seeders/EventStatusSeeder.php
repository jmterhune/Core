<?php

namespace Database\Seeders;

use App\Models\County;
use App\Models\EventStatus;
use App\Models\EventType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EventStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        EventStatus::create([
            'name' => 'Cancelled',
        ]);
        EventStatus::create([
            'name' => 'Rescheduled',
        ]);
        EventStatus::create([
            'name' => 'Scheduled',
        ]);
        EventStatus::create([
            'name' => 'Past',
        ]);
    }
}
