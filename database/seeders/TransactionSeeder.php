<?php

namespace Database\Seeders;

use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use App\Models\Product;
use App\Models\Customer; // ✅ ADD THIS
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        $staffIds    = User::where('role', 'staff')->pluck('id')->all();
        $customerIds = Customer::pluck('id')->all(); // ✅ customers table
        $products    = Product::all();

        if (empty($staffIds) || $products->isEmpty()) {
            $this->command?->warn('No staff or products found. Skipping TransactionSeeder.');
            return;
        }

        // If you have no customers, we can still seed walk-in transactions.
        $hasCustomers = !empty($customerIds);

        $now       = Carbon::now();
        $year      = $now->year;
        $lastMonth = $now->month;

        $totalTransactions = 500;

        // Strongly increasing weights per month
        $monthWeights = [];
        $totalWeight  = 0;

        for ($m = 1; $m <= $lastMonth; $m++) {
            $weight = $m * $m;
            $monthWeights[$m] = $weight;
            $totalWeight     += $weight;
        }

        $monthCounts = [];
        $assigned    = 0;

        foreach ($monthWeights as $m => $weight) {
            $count = (int) floor($totalTransactions * $weight / $totalWeight);
            $monthCounts[$m] = $count;
            $assigned        += $count;
        }

        $remaining = $totalTransactions - $assigned;

        while ($remaining > 0) {
            for ($m = $lastMonth; $m >= 1 && $remaining > 0; $m--) {
                $monthCounts[$m]++;
                $remaining--;
            }
        }

        // % of transactions that are walk-in (no customer_id)
        // Adjust as you like: 30% walk-in, 70% registered customer
        $walkInChance = 30;

        foreach ($monthCounts as $month => $txCount) {
            if ($txCount <= 0) continue;

            $daysInMonth = Carbon::create($year, $month, 1)->daysInMonth;
            $monthRatio  = $month / max(1, $lastMonth);

            for ($i = 0; $i < $txCount; $i++) {
                $day    = rand(1, $daysInMonth);
                $hour   = rand(9, 20);
                $minute = rand(0, 59);
                $second = rand(0, 59);

                $randomTimestamp = Carbon::create($year, $month, $day, $hour, $minute, $second);

                if ($randomTimestamp->greaterThan($now)) {
                    $randomTimestamp = $now->copy()->subMinutes(rand(0, 60 * 24));
                }

                // ✅ Choose customer from customers table (or walk-in)
                $customerId = null;
                if ($hasCustomers && rand(1, 100) > $walkInChance) {
                    $customerId = $customerIds[array_rand($customerIds)];
                }

                $transaction = Transaction::create([
                    'customer_id' => $customerId, // ✅ now can be real customer
                    'staff_id'    => $staffIds[array_rand($staffIds)],
                    'type'        => 'Buy',
                    'created_at'  => $randomTimestamp,
                    'updated_at'  => $randomTimestamp,
                ]);

                $minItems  = 1;
                $maxItems  = 2 + (int) ceil($monthRatio * 3);
                $itemCount = rand($minItems, $maxItems);

                for ($j = 0; $j < $itemCount; $j++) {
                    $product = $products->random();

                    $baseMaxQty = 2;
                    $extraQty   = (int) ceil($monthRatio * 3);
                    $maxQty     = max(1, $baseMaxQty + $extraQty);

                    $qty       = rand(1, $maxQty);
                    $unitPrice = (float) $product->price;
                    $lineTotal = $unitPrice * $qty;

                    TransactionItem::create([
                        'transaction_id' => $transaction->id,
                        'product_id'     => $product->id,
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
}
