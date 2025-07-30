<?php

namespace App\Mail;
use App\Models\Court;
use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\HtmlString;

class HearingCancellation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $event;
    public $custom_body;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Event $event)
    {
	    $this->event = $event;
	    $this->custom_body = "";
	    $court = Court::find($this->event->timeslot->court->id);

        if(isset($court->custom_email_body) && $court->custom_email_body != ""){

            $placeholder = $court->custom_email_body;

            if(str_contains($court->custom_email_body, '[case]')){
                $placeholder = str_replace('[case]', $event->case_num, $placeholder);
            }
            if(str_contains($court->custom_email_body, '[motion]')){
                $placeholder = str_replace('[motion]', $event->motion->description, $placeholder);
            }
            if(str_contains($court->custom_email_body, '[attorney]')){
                $placeholder = str_replace('[attorney]', $event->attorney->name, $placeholder);
            }
            if(str_contains($court->custom_email_body, '[plaintiff]')){
                $placeholder = str_replace('[plaintiff]', $event->plaintiff, $placeholder);
            }

            $this->custom_body = strip_tags($placeholder);
        }
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.new-hearing-cancellation')
            ->subject('Hearing Cancellation: Case# ' . $this->event->case_num);
    }
}
