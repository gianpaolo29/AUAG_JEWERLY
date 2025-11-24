<?php

namespace App\Notifications;

use App\Models\Repair;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewRepairRequestNotification extends Notification
{
    use Queueable;

    public function __construct(public Repair $repair) {}

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => 'New Repair Request',
            'message' => 'New repair request from '.($this->repair->customer->name ?? 'Unknown').'.',
            'url' => route('admin.repairs.index'),
            'icon' => 'wrench',
            'meta' => [
                'status' => $this->repair->status,
                'item' => $this->repair->item_description ?? null,
            ],
        ];
    }
}
