<?php

namespace Database\Seeders;

use App\Models\County;
use App\Models\TicketsPriority;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TicketPrioritySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        TicketsPriority::create([
            'name' => 'Low',
        ]);

        TicketsPriority::create([
            'name' => 'Medium',
        ]);

        TicketsPriority::create([
            'name' => 'High',
        ]);

    }
}
