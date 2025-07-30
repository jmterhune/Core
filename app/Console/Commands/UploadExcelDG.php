<?php

namespace App\Console\Commands;

use App\Imports\ExcelUploaded;
use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;

class UploadExcelDG extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jacs:upload-excel';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Case Management Upload';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Excel::import(new ExcelUploaded, 'public/test.xlsx');
    }
}
