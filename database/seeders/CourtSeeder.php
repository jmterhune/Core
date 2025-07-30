<?php

namespace Database\Seeders;

use App\Models\Court;
use App\Models\CourtType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CourtSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $csv = fopen(base_path("database/seeders/csvs/courts.csv"), "r");

        while (($data = fgetcsv($csv, 4000, ",")) !== FALSE) {
            Court::create([
                'old_id' => $data['0'],
                'case_num_format' => $data['1'],
                'county_id' => 1,
                'description' => Str::title($data['2']),
                'plaintiff' => Str::title($data['10']),
            ]);
        }

        fclose($csv);
    }
}
