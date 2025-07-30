<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Attorney;

class NewAttoneryActivation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $attorney;
    public $requester;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Attorney $attorney, $requester)
    {
        $this->attorney = $attorney;
        $this->requester = $requester;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'New Attorney Activation',
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
            markdown: 'emails.attorney.attorney_email_alert',
            with: [
                'enable_url' => route('attorney.edit',$this->attorney->id),
                'attorney_url' => 'https://www.floridabar.org/directories/find-mbr/?barNum=' . $this->attorney->bar_num
            ]);
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        return [];
    }
}
