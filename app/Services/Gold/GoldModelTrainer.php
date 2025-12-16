<?php

namespace App\Services\Gold;

use App\Models\GoldModelRun;
use App\Models\GoldPrice;
use Illuminate\Support\Facades\File;
use Rubix\ML\Datasets\Labeled;
use Rubix\ML\PersistentModel;
use Rubix\ML\Persisters\Filesystem;
use Rubix\ML\Pipeline;
use Rubix\ML\Regressors\GradientBoost;
use Rubix\ML\Transformers\ZScaleStandardizer;

class GoldModelTrainer
{
    public function train(): GoldModelRun
    {
        // 1) Force safe defaults
        $lookback = max(2, (int) config('gold.lookback', 30));
        $modelPath = (string) config('gold.model_path', '');

        if ($modelPath === '') {
            throw new \RuntimeException('gold.model_path is empty. Check config/gold.php and run php artisan optimize:clear');
        }

        // 2) Ensure directory exists + writable
        $dir = dirname($modelPath);
        File::ensureDirectoryExists($dir);

        if (! is_writable($dir)) {
            throw new \RuntimeException("Model directory not writable: {$dir}. Fix storage permissions.");
        }

        // 3) Load data (reindex array)
        $rows = GoldPrice::query()->orderBy('date')->get(['date', 'price_usd']);
        $prices = array_values(
            $rows->pluck('price_usd')->map(fn ($v) => (float) $v)->all()
        );

        // Need enough samples: each sample uses lookback and predicts next day
        if (count($prices) < ($lookback + 31)) {
            throw new \RuntimeException("Not enough daily data to train. Need at least " . ($lookback + 31) . " rows. Run sync first.");
        }

        $fe = new GoldFeatureEngineer();
        $samples = [];
        $labels  = [];

        // i is the last index of the window; label is next day (i+1)
        for ($i = $lookback - 1; $i < count($prices) - 1; $i++) {
            $window = array_values(array_slice($prices, $i - ($lookback - 1), $lookback));

            // Guard: window must be full length
            if (count($window) < $lookback) {
                continue;
            }

            $samples[] = $fe->makeSample($window);
            $labels[]  = $prices[$i + 1];
        }

        if (count($samples) < 10) {
            throw new \RuntimeException('Training dataset too small after feature engineering. Check lookback and data integrity.');
        }

        $dataset = new Labeled($samples, $labels);

        $pipeline = new Pipeline(
            [new ZScaleStandardizer()],
            new GradientBoost()
        );

        $model = new PersistentModel($pipeline, new Filesystem($modelPath));
        $model->train($dataset);
        $model->save();

        return GoldModelRun::create([
            'trained_at' => now(),
            'model_path' => $modelPath,
            'lookback'   => $lookback,
            'metrics'    => [
                'samples'   => count($samples),
                'min_date'  => optional($rows->first())->date?->toDateString(),
                'max_date'  => optional($rows->last())->date?->toDateString(),
            ],
        ]);
    }
}
