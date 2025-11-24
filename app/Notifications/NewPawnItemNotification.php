<?php

namespace App\Notifications;

use App\Models\PawnItem;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewPawnItemNotification extends Notification
{
    use Queueable;

    public function __construct(public PawnItem $pawnItem) {}

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => 'New Pawn Item',
            'message' => 'New pawn item created for customer: '.($this->pawnItem->customer->name ?? 'Unknown'),
            'url' => route('admin.pawn.index'),
            'icon' => 'briefcase',
            'meta' => [
                'ticket_no' => $this->pawnItem->ticket_no ?? null,
                'due_date' => $this->pawnItem->due_date,
                'status' => $this->pawnItem->status,
            ],
        ];
    }
}
