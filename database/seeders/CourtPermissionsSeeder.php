<?php

namespace Database\Seeders;

use App\Models\CourtPermission;
use App\Models\Judge;
use App\Models\Motion;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CourtPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $csv = fopen(base_path("database/seeders/csvs/jajudges.csv"), "r");

        $dataRow = true;
        while (($data = fgetcsv($csv, 4000, ",")) !== FALSE) {
            if (!$dataRow) {

                $judge = Judge::where('old_id', $data[1])->first();
                $ja = User::where('name', 'like', '%' . strtolower(ltrim($data['0'], $data['0'][0])) . '%')->first();

                if($ja != null){
                    CourtPermission::create([
                        'user_id' => $ja->id,
                        'judge_id' => $judge->id,
                        'active' => $data['3'] == 'Y',
                        'editable' => $data['2'] == 'E'
                    ]);
                }
            }
            $dataRow = false;
        }

        fclose($csv);
    }
}
