<?php

namespace App\Mail;

use App\Models\Attorney;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewAttorney extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;


    public $attorney;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Attorney $attorney)
    {
        $this->attorney = $attorney;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.attorney.new')->subject('JACS Account Registration');
    }
}
