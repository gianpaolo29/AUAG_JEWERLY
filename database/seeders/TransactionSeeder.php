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
        $staffIds = User::where('role', 'staff')->pluck('id')->all();
        $products = Product::all();

        if (empty($staffIds) || $products->isEmpty()) {
            $this->command?->warn('No staff or products found. Skipping TransactionSeeder.');
            return;
        }

        $now       = Carbon::now();
        $year      = $now->year;
        $lastMonth = $now->month; // up to current month

        $totalTransactions = 500;

        // -------------------------------------------------
        // 1) Build strongly increasing weights per month
        //    (later months have MUCH higher weight)
        // -------------------------------------------------
        $monthWeights = [];
        $totalWeight  = 0;

        for ($m = 1; $m <= $lastMonth; $m++) {
            // square the month to strongly favor later ones
            $weight = $m * $m;
            $monthWeights[$m] = $weight;
            $totalWeight     += $weight;
        }

        // Compute exact transaction counts per month
        $monthCounts = [];
        $assigned    = 0;

        foreach ($monthWeights as $m => $weight) {
            $count = (int) floor($totalTransactions * $weight / $totalWeight);
            $monthCounts[$m] = $count;
            $assigned        += $count;
        }

        // Distribute any leftover transactions, starting from the last month backwards
        $remaining = $totalTransactions - $assigned;

        while ($remaining > 0) {
            for ($m = $lastMonth; $m >= 1 && $remaining > 0; $m--) {
                $monthCounts[$m]++;
                $remaining--;
            }
        }

        // -------------------------------------------------
        // 2) Generate transactions per month
        // -------------------------------------------------
        foreach ($monthCounts as $month => $txCount) {
            if ($txCount <= 0) {
                continue;
            }

            $daysInMonth = Carbon::create($year, $month, 1)->daysInMonth;

            // Ratio used to scale sales for later months
            $monthRatio = $month / max(1, $lastMonth); // 0–1

            for ($i = 0; $i < $txCount; $i++) {
                // Random day/time within the month
                $day    = rand(1, $daysInMonth);
                $hour   = rand(9, 20);  // business hours-ish
                $minute = rand(0, 59);
                $second = rand(0, 59);

                $randomTimestamp = Carbon::create($year, $month, $day, $hour, $minute, $second);

                // Avoid future timestamps (for current month)
                if ($randomTimestamp->greaterThan($now)) {
                    $randomTimestamp = $now->copy()->subMinutes(rand(0, 60 * 24));
                }

                // Create Buy transaction (walk-in)
                $transaction = Transaction::create([
                    'customer_id' => null,
                    'staff_id'    => $staffIds[array_rand($staffIds)],
                    'type'        => 'Buy',
                    'created_at'  => $randomTimestamp,
                    'updated_at'  => $randomTimestamp,
                ]);

                // -------------------------------------------------
                // Items per transaction:
                //   earlier months: fewer items, smaller qty
                //   later months: more items, bigger qty
                // -------------------------------------------------
                // max items grows with month
                $minItems = 1;
                $maxItems = 2 + (int) ceil($monthRatio * 3); // early: 2–3, late: up to 5
                $itemCount = rand($minItems, $maxItems);

                for ($j = 0; $j < $itemCount; $j++) {
                    /** @var \App\Models\Product $product */
                    $product = $products->random();

                    $baseMaxQty = 2;
                    $extraQty   = (int) ceil($monthRatio * 3); // later months: more qty
                    $maxQty     = max(1, $baseMaxQty + $extraQty); // early: 2–3, late: up to 5

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
