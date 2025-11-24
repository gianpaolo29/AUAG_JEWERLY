<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminGenericNotification extends Notification
{
    use Queueable;

    public string $title;
    public string $message;
    public ?string $url;

    public function __construct(string $title, string $message, ?string $url = null)
    {
        $this->title   = $title;
        $this->message = $message;
        $this->url     = $url;
    }

    // Store in DB
    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'title'   => $this->title,
            'message' => $this->message,
            'url'     => $this->url,
        ];
    }
}

