<?php

namespace Database\Seeders;

use App\Models\Court;
use App\Models\Judge;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class JudgeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $csv = fopen(base_path("database/seeders/csvs/judges.csv"), "r");

        $dataRow = true;
        while (($data = fgetcsv($csv, 4000, ",")) !== FALSE) {
            if (!$dataRow) {

                switch($data['13']){
                    case 2:
                        $title = 'Mediator';
                        break;
                    case 3:
                        $title = 'Magistrate';
                        break;
                    case 4:
                        $title = 'Case Manager';
                        break;
                    default:
                        $title = 'Judge';
                        break;
                }

                Judge::create([
                    'old_id' => $data['0'],
                    'name' => Str::title($data['1']),
                    'phone' => $data['2'],
                    'court_id' => Court::where('old_id', $data['3'])->first()->id ?? null,
                    'title' => $title
                ]);
            }
            $dataRow = false;
        }

        fclose($csv);
    }
}
