<?php

namespace Database\Seeders;

use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get staff + products for relations
        $staffIds   = User::where('role', 'staff')->pluck('id')->all();
        $productIds = Product::pluck('id')->all();

        if (empty($staffIds) || empty($productIds)) {
            $this->command?->warn('No staff or products found. Skipping TransactionSeeder.');
            return;
        }

        // Date range: Jan 1 this year -> now
        $startOfYear = Carbon::now()->startOfYear();
        $endOfYear   = Carbon::now();

        for ($i = 0; $i < 5; $i++) {
            // 🎲 Random timestamp within this year
            $randomTimestamp = Carbon::createFromTimestamp(
                rand($startOfYear->timestamp, $endOfYear->timestamp)
            );

            // Create Buy transaction (walk-in, no customer)
            $transaction = Transaction::create([
                'customer_id' => null,                          // walk-in only
                'staff_id'    => $staffIds[array_rand($staffIds)],
                'type'        => 'Buy',                         // only Buy
                'created_at'  => $randomTimestamp,
                'updated_at'  => $randomTimestamp,
            ]);

            // 1–4 items per transaction
            $itemCount = rand(1, 4);

            for ($j = 0; $j < $itemCount; $j++) {
                $productId = $productIds[array_rand($productIds)];
                $product   = Product::find($productId);

                if (! $product) {
                    continue;
                }

                $qty       = rand(1, 3);
                $unitPrice = (float) $product->price;
                $lineTotal = $unitPrice * $qty;

                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'product_id'     => $productId,
                    'pawn_item_id'   => null,
                    'repair_id'      => null,
                    'quantity'       => $qty,
                    'unit_price'     => $unitPrice,
                    'line_total'     => $lineTotal,
                    'created_at'     => $randomTimestamp,
                    'updated_at'     => $randomTimestamp,
                ]);
            }
        }
    }
}
