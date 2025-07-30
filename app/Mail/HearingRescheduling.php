<?php

namespace App\Mail;

use App\Models\Court;
use App\Models\Event;
use App\Models\Timeslot;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class HearingRescheduling extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Event $event;
    public Timeslot $old_timeslot;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Event $event, Timeslot $old_timeslot)
    {
        $this->event = $event;
        $this->old_timeslot = $old_timeslot;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'Hearing Rescheduling: Case# ' . $this->event->case_num,
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
            markdown: 'emails.hearing-rescheduling',
            with: [
                'url' => env('APP_URL') . '/event-reminder-link/' . $this->event->id . '/' . $this->to[0]['address'], //TODO: Change to route name
            ],
        );
    }

}
