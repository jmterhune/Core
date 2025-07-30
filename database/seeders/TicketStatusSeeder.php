<?php

namespace Database\Seeders;

use App\Models\County;
use App\Models\TicketStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TicketStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        TicketStatus::create([
            'name' => 'Pending',
        ]);

        TicketStatus::create([
            'name' => 'Close',
        ]);

        TicketStatus::create([
            'name' => 'In Progress',
        ]);

    }
}
