<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Gold\GoldForecaster;

class GoldForecast extends Command
{
    protected $signature = 'gold:forecast {days=14}';
    protected $description = 'Generate forecast points using latest trained model';

    public function handle(GoldForecaster $forecaster): int
    {
        $days = (int) $this->argument('days');
        $count = $forecaster->forecast($days);
        $this->info("Generated {$count} forecast points.");
        return self::SUCCESS;
    }
}
