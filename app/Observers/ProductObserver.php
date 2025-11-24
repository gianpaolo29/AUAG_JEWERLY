<?php

namespace App\Observers;

use App\Models\Product;
use App\Models\User;
use App\Notifications\LowStockAlertNotification;

class ProductObserver
{
    public function updated(Product $product)
    {
        // Trigger when crossing below threshold (e.g. from >=5 to <5)
        if ($product->quantity < 5 && $product->getOriginal('quantity') >= 5) {
            $admins = User::where('role', 'admin')->get();

            foreach ($admins as $admin) {
                $admin->notify(new LowStockAlertNotification($product));
            }
        }
    }
}
