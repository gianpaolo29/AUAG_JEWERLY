<?php

namespace App\Notifications;

use App\Models\PawnItem;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PawnDueDateReminderNotification extends Notification
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
            'title' => 'Pawn Due Date Approaching',
            'message' => 'Pawn ticket #'.($this->pawnItem->ticket_no ?? $this->pawnItem->id).
                         ' is due on '.$this->pawnItem->due_date.'.',
            'url' => route('admin.pawn.index'),
            'icon' => 'clock',
            'meta' => [
                'due_date' => $this->pawnItem->due_date,
                'status' => $this->pawnItem->status,
            ],
        ];
    }
}
