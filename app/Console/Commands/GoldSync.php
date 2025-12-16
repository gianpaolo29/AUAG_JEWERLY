<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Gold\GoldDataSyncService;

class GoldSync extends Command
{
    protected $signature = 'gold:sync
        {days? : Number of days to sync (default: 180)}
        {--from= : Start date (YYYY-MM-DD)}
        {--to= : End date (YYYY-MM-DD)}';

    protected $description = 'Sync gold prices into database (timeseries + latest)';

    public function handle(GoldDataSyncService $sync): int
    {
        $from = $this->option('from');
        $to   = $this->option('to');

        if ($from || $to) {
            $from = $from ?: now('UTC')->subDays(29)->toDateString();
            $to   = $to   ?: now('UTC')->toDateString();

            $this->info("Syncing range: {$from} → {$to}");
            $count = $sync->syncRange($from, $to);

            $this->info("Done. Upserted {$count} rows (includes /latest upsert).");
            return self::SUCCESS;
        }

        $days = (int) ($this->argument('days') ?: 180);
        $days = max(1, $days);

        $this->info("Syncing last {$days} days...");
        $count = $sync->syncDays($days);

        $this->info("Done. Upserted {$count} rows (includes /latest upsert).");
        return self::SUCCESS;
    }
}
