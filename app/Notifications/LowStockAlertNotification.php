<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LowStockAlertNotification extends Notification
{
    use Queueable;

    public function __construct(public Product $product) {}

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => 'Low Stock Alert',
            'message' => "Product '{$this->product->name}' is low on stock ({$this->product->quantity} left).",
            'url' => route('admin.products.edit', $this->product),
            'icon' => 'alert-triangle',
            'meta' => [
                'quantity' => $this->product->quantity,
                'id' => $this->product->id,
            ],
        ];
    }
}
