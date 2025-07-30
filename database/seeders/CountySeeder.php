<?php

namespace Database\Seeders;

use App\Models\County;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CountySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        County::create([
            'name' => 'Brevard',
            'code' => '05',
        ]);

        County::create([
            'name' => 'Seminole',
            'code' => '59'
        ]);

    }
}
