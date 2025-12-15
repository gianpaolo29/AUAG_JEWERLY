<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Gold\GoldModelTrainer;

class GoldTrain extends Command
{
    protected $signature = 'gold:train';
    protected $description = 'Train and save the gold forecast ML model';

    public function handle(GoldModelTrainer $trainer): int
    {
        $run = $trainer->train();
        $this->info("Trained model run #{$run->id}. Saved to: {$run->model_path}");
        return self::SUCCESS;
    }
}
