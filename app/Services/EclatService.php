<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class EclatService
{
    protected string $pythonBinary;
    protected string $scriptPath;

    public function __construct()
    {
        $this->pythonBinary = config('services.python.binary', 'python');
        $this->scriptPath   = base_path('scripts/eclat_miner.py');
    }

    /**
     * Build transactions array from DB:
     * [ "transaction_id" => [product_id, ...], ... ]
     */
    protected function buildTransactions(): array
    {
        $rows = DB::table('transactions as t')
            ->join('transaction_items as ti', 'ti.transaction_id', '=', 't.id')
            ->where('t.type', 'Buy')
            ->whereNotNull('ti.product_id')
            ->select('t.id as transaction_id', 'ti.product_id')
            ->orderBy('t.id')
            ->get();

        $transactions = [];

        foreach ($rows as $row) {
            $tid = (string) $row->transaction_id;

            if (! isset($transactions[$tid])) {
                $transactions[$tid] = [];
            }

            $pid = (int) $row->product_id;

            if (! in_array($pid, $transactions[$tid], true)) {
                $transactions[$tid][] = $pid;
            }
        }

        return $transactions;
    }

    /**
     * Call Python ECLAT and return the raw result array.
     * returns: ['frequent_itemsets' => [ ['items'=>[...], 'support'=>N], ... ]]
     */
    public function mine(int $minSupport = 2): array
    {
        $transactions = $this->buildTransactions();

        if (empty($transactions)) {
            return ['frequent_itemsets' => []];
        }

        $payload = json_encode([
            'transactions' => $transactions,
            'min_support'  => $minSupport,
        ]);

        $process = new Process([$this->pythonBinary, $this->scriptPath]);
        $process->setInput($payload);
        $process->setTimeout(300);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $data = json_decode($process->getOutput(), true);

        return $data ?: ['frequent_itemsets' => []];
    }

    /**
     * Get recommended products for a given product ID (in memory only).
     */
    public function recommendForProduct(int $productId, int $minSupport = 2, int $limit = 4)
    {
        $result   = $this->mine($minSupport);
        $itemsets = $result['frequent_itemsets'] ?? [];

        $scores = []; // product_id => accumulated score

        foreach ($itemsets as $set) {
            $items   = $set['items'] ?? [];
            $support = $set['support'] ?? 0;

            // Only use itemsets that contain the target product
            if (! in_array($productId, $items, true)) {
                continue;
            }

            foreach ($items as $otherId) {
                if ($otherId === $productId) {
                    continue;
                }

                if (! isset($scores[$otherId])) {
                    $scores[$otherId] = 0;
                }

                $scores[$otherId] += $support;
            }
        }

        if (empty($scores)) {
            return collect(); // empty Laravel collection
        }

        // Sort by score desc
        arsort($scores);
        $topIds = array_slice(array_keys($scores), 0, $limit);

        return \App\Models\Product::whereIn('id', $topIds)
            ->orderByRaw('FIELD(id, '.implode(',', $topIds).')')
            ->get();
    }
}
