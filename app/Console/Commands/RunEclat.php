<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class RunEclat extends Command
{
    protected $signature = 'eclat:mine {--min_support=2}';
    protected $description = 'Run ECLAT on Buy transactions and output frequent itemsets';

    public function handle()
    {
        $minSupport = (int) $this->option('min_support');


        $this->info('Fetching Buy transactions from database...');

        $rows = DB::table('transactions as t')
            ->join('transaction_items as ti', 'ti.transaction_id', '=', 't.id')
            ->where('t.type', 'Buy')
            ->whereNotNull('ti.product_id')
            ->select('t.id as transaction_id', 'ti.product_id')
            ->orderBy('t.id')
            ->get();

        if ($rows->isEmpty()) {
            $this->warn('No Buy transactions found.');
            return Command::SUCCESS;
        }


        $transactions = [];

        foreach ($rows as $row) {
            $tid = (string) $row->transaction_id;

            if (! isset($transactions[$tid])) {
                $transactions[$tid] = [];
            }

           
            if (! in_array((int) $row->product_id, $transactions[$tid], true)) {
                $transactions[$tid][] = (int) $row->product_id;
            }
        }

        $payload = json_encode([
            'transactions' => $transactions,
            'min_support'  => $minSupport,
        ]);


        $python = config('services.python.binary');
        $script = base_path('scripts/eclat_miner.py');

        $this->info("Running Python script: {$python} {$script} ...");

        $process = new Process([$python, $script]);
        $process->setInput($payload);
        $process->setTimeout(300); // 5 minutes max, adjust if needed
        $process->run();

        if (! $process->isSuccessful()) {
            $this->error('Python process failed:');
            $this->error($process->getErrorOutput());
            throw new ProcessFailedException($process);
        }

        $output = $process->getOutput();
        $result = json_decode($output, true);

        if (! isset($result['frequent_itemsets'])) {
            $this->error('No frequent_itemsets key in Python output.');
            $this->line($output);
            return Command::FAILURE;
        }

        $this->info('Frequent itemsets:');
        foreach ($result['frequent_itemsets'] as $itemset) {
            $items = implode(', ', $itemset['items']);
            $support = $itemset['support'];
            $this->line("  { {$items} } -> support = {$support}");
        }

        return Command::SUCCESS;
    }
}
