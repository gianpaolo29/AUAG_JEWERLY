<?php

namespace App\Notifications;

use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewTransactionNotification extends Notification
{
    use Queueable;

    public function __construct(public Transaction $transaction) {}

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => 'New Sale Transaction',
            'message' => 'New '.$this->transaction->type.' transaction (#'.$this->transaction->id.') was recorded.',
            'url' => route('admin.transactions.show', $this->transaction),
            'icon' => 'credit-card',
            'meta' => [
                'type' => $this->transaction->type,
                'total' => $this->transaction->total ?? null,
                'staff' => $this->transaction->staff?->name,
                'customer' => $this->transaction->customer?->name ?? null,
            ],
        ];
    }
}
