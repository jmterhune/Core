<?php

namespace App\Mail;
use Illuminate\Support\HtmlString;
use App\Models\Court;
use App\Models\MediationCases;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MediationCaseConfirmation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public MediationCases $case;
    // public $custom_body;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(MediationCases $case)
    {
        $this->case = $case;
        // $this->custom_body = "";
        // $court = Court::find($this->event->timeslot->court->id);

        // if(isset($court->custom_email_body) && $court->custom_email_body != ""){

        //     $placeholder = $court->custom_email_body;

        //     if(str_contains($court->custom_email_body, '[case]')){
        //         $placeholder = str_replace('[case]', $event->case_num, $placeholder);
        //     }
        //     if(str_contains($court->custom_email_body, '[motion]')){
        //         $placeholder = str_replace('[motion]', $event->motion->description, $placeholder);
        //     }
        //     if(str_contains($court->custom_email_body, '[attorney]')){
        //         $placeholder = str_replace('[attorney]', $event->attorney->name, $placeholder);
        //     }
        //     if(str_contains($court->custom_email_body, '[plaintiff]')){
        //         $placeholder = str_replace('[plaintiff]', $event->plaintiff, $placeholder);
	    // }
	    // $this->custom_body = $placeholder;

	   // $this->custom_body = (strip_tags($placeholder));
	   // $this->custom_body = str_replace('&nbsp;', ' ', $this->custom_body);
        // }
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'Mediation Hearing Confirmation: Case# ' . $this->case->c_caseno.' with Reference Number REF000'.$this->case->id,
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        return new Content(
            markdown: 'emails.new-mediationcase-confirmation',
            with: [
                // 'url' => env('APP_URL') . '/event-reminder-link/' . $this->event->id . '/' . $this->to[0]['address'], //TODO: Change to route name
                // 'custom_body' => $this->custom_body
            ],
        );
    }

}
