<?php

namespace Database\Seeders;

use App\Models\CourtType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CourtTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $csv = fopen(base_path("database/seeders/csvs/courttypes.csv"), "r");

        $dataRow = true;
        while (($data = fgetcsv($csv, 4000, ",")) !== FALSE) {
            if (!$dataRow) {
                CourtType::create([
                    'old_id' => $data['0'],
                    'description' => Str::title($data['2']),
                ]);
            }
            $dataRow = false;
        }

        fclose($csv);
    }
}
