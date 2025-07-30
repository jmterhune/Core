<?php

namespace Database\Seeders;

use App\Models\Court;
use App\Models\Motion;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MotionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $csv = fopen(base_path("database/seeders/csvs/motions.csv"), "r");

        $dataRow = true;
        while (($data = fgetcsv($csv, 4000, ",")) !== FALSE) {
            if (!$dataRow) {
                Motion::create([
                    'old_id' => $data['0'],
                    'description' => Str::title($data['2']),
                ]);
            }
            $dataRow = false;
        }

        fclose($csv);
    }
}
