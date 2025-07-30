<?php

namespace Database\Seeders;

use App\Models\County;
use App\Models\EventType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EventTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        EventType::create([
            'name' => 'Remote',
        ]);

        EventType::create([
            'name' => 'In Person',
        ]);

        EventType::create([
            'name' => 'Telephone',
        ]);

    }
}
