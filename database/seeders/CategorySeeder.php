<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $csv = fopen(base_path("database/seeders/csvs/courtrooms.csv"), "r");

        $dataRow = true;
        while (($data = fgetcsv($csv, 4000, ",")) !== FALSE) {
            if (!$dataRow) {
                Category::create([
                    'old_id' => $data['0'],
                    'description' => Str::title($data['1']),
                ]);
            }
            $dataRow = false;
        }

        fclose($csv);
    }
}
