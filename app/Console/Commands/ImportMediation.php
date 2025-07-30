<?php

namespace App\Console\Commands;

use App\Models\Judge;
use App\Models\MediationCases;
use App\Models\MediationEvents;
use App\Models\MediationMediator;
use App\Models\MediationOutcome;
use App\Models\Party;
use Carbon\Carbon;
use Carbon\PHPStan\AbstractMacro;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ImportMediation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mediation:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Family Mediation';

    public function readCSV($csvFile, $delimiter = ',')
    {
        $file_handle = fopen($csvFile, 'r');
        while ($csvRow = fgetcsv($file_handle, null, $delimiter)) {
            $line_of_text[] = $csvRow;
        }
        fclose($file_handle);
        return $line_of_text;
    }


    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $csvFile = public_path('data.csv');
        $csv_cases = $this->readCSV($csvFile,',');

        foreach ($csv_cases as $csv_line) {

            $judge = $csv_line[8] == 'PATELDOOKHOO'
                ? Judge::where('name','like', '%Patel%')->first()->id
                : Judge::where('name','like', '%' . trim($csv_line[8]) . '%')->first()->id;

            $family_case = MediationCases::firstOrCreate([
                'form_type' => "f-form",
                'c_caseno' => $csv_line[0],
                'petitioner' => $csv_line[9] == 'IND',
                'e_pltf_chg' => strtolower($csv_line[9]) != 'ind' ? substr($csv_line[9], 1) : 0,
                'e_pltf_annl_chg' => $csv_line[13] === 'X' ? ($csv_line[9] == 'IND' ? 0 : 24000) : 50000,
                'respondent' => $csv_line[11] == 'IND',
                'e_def_chg' => strtolower($csv_line[11]) != 'ind' ? substr($csv_line[11], 1) : 0,
                'e_def_annl_chg' => $csv_line[13] === 'X' ? ($csv_line[11] == 'IND' ? 0 : 24000) : 50000,
                'c_div' => $judge,
                'approved' => true
            ]);

            $petitioner = Party::firstOrCreate(
                [
                    'name' => Str::title($csv_line[1]),
                    'type' => 'petitioner',
                    'mediation_case_id' => $family_case->id
                ]
            );

            $respondent = Party::firstOrCreate(
                [
                    'name' => Str::title($csv_line[3]),
                    'type' => 'respondent',
                    'mediation_case_id' => $family_case->id
                ]
            );

            $mediator = MediationMediator::firstOrCreate(
                [
                    'name' => Str::title($csv_line[6]),
                    'email' => 'idontknow@flcourts18.org',
                    'phone' => '8675309',
                    'type' => 'family',
                    'county' => 'brevard',
                ],
            );

            if(str_contains(strtolower($csv_line[7]), 'cancelled')){
                $outcome_parsed = 'Cancelled';
            } elseif(str_contains(strtolower($csv_line[7]), 'rescheduled')){
                $outcome_parsed = 'Rescheduled';
            } else{
                $outcome_parsed = Str::title($csv_line[7]);
            }

            $outcome = MediationOutcome::firstOrCreate(
                [
                    'o_outcome' => $outcome_parsed
                ]
            );

            $event = MediationEvents::firstOrCreate(
                ['e_c_id' => $family_case->id],
                [
                    'e_m_id' => $mediator->id,
                    'e_outcome_id' => $outcome->id,
                    'e_pltf_chg' => $family_case->e_pltf_chg,
                    'e_def_chg' => $family_case->e_def_chg,
                    'e_med_fee' => $family_case->e_pltf_chg + $family_case->e_def_chg,
                    'e_sch_datetime' => Carbon::create($csv_line[5]),
                ]
            );
        }

        return Command::SUCCESS;
    }
}
