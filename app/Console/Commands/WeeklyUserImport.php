<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class WeeklyUserImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jacs:sync-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'AD user sync';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        Artisan::call('ldap:import -n -d -r -f "(mail=*@flcourts18.org)" users');

        return Command::SUCCESS;
    }
}
