<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\MicrosoftTeams\MicrosoftTeamsChannel;
use NotificationChannels\MicrosoftTeams\MicrosoftTeamsMessage;

class NewAttorney extends Notification implements ShouldQueue
{
    use Queueable;

    public $requester;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($requester)
    {
        $this->requester = $requester;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [MicrosoftTeamsChannel::class];
    }

    public function toMicrosoftTeams($notifiable)
    {
        $summary = $this->requester != $notifiable->name ?
            $this->requester .' is requesting access for ' . $notifiable->name . '.'
            : $notifiable->name . ' is requesting access.';

        return MicrosoftTeamsMessage::create()
            ->to(config('services.teams.webhook_url'))
            ->type('success')
            ->title('New JACS Account Request')
            ->content($summary)
            ->button('View Attorney', 'https://www.floridabar.org/directories/find-mbr/?barNum=' . $notifiable->bar_num)
            ->button('Enable User', route('attorney.edit',$notifiable->id));
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }

    public function routeNotificationForMicrosoftTeams(Notification $notification)
    {
        return config('services.teams.webhook_url');
    }
}
