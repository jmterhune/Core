<?php

namespace App\Jobs;

use App\Models\Attorney;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Zendesk\API\HttpClient as ZendeskAPI;

class NewAttorneyPublicJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $data;
    public $attorney;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $request)
    {
        $this->data = $request;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if(env('APP_ENV') != "staging"){
            $subdomain = "flcourts18";
            $username  = "kunle.adelakun@flcourts18.org"; // replace this with your registered email
            $token     = "1k2TUkxmukI9izroMlG8xG4PkjH1NGaxsirZNW30"; // replace this with your token

            $client = new ZendeskAPI($subdomain);
            $client->setAuth('basic', ['username' => $username, 'token' => $token]);

            $newTicket = $client->tickets()->create([
                'subject'  => 'JACS Staging: Login Request',
                'comment'  => [
                    'body' =>
                        $this->data['name'] . ' was unable to find the Attorney below in our database.' . PHP_EOL . PHP_EOL .
                        'Please use the Creation Link below to create the attorney after verifying the Bar Number' . PHP_EOL . PHP_EOL .
                        '**New Attorney Name:** ' . $this->data['attorney_name'] . PHP_EOL . PHP_EOL .
                        '**New Attorney Bar Number:** ' . $this->data['attorney_bar'] . ' [Click Me](https://www.floridabar.org/directories/find-mbr/?barNum=' . $this->data['attorney_bar'] . ')' . PHP_EOL . PHP_EOL .
                        '**New Creation Link:** [CLick Me] ('  . route('attorney.create') . ')' . PHP_EOL . PHP_EOL .
                        '**Reply Email:**'  . $this->data['email']
                ],
                'tags' => [
                    'JACS',
                    'Attorney Request'
                ],
                'priority' => 'normal'
            ]);
        }
    }
}
