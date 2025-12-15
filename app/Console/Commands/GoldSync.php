<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Gold\GoldDataSyncService;

class GoldSync extends Command
{
    protected $signature = 'gold:sync';
    protected $description = 'Sync historical gold prices into database';

    public function handle(GoldDataSyncService $svc): int
    {
        $count = $svc->sync();
        $this->info("Synced {$count} rows.");
        return self::SUCCESS;
    }
}
