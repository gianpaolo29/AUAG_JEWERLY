<?php

namespace App\Services\Gold;

use App\Models\GoldForecast;
use App\Models\GoldModelRun;
use App\Models\GoldPrice;
use Rubix\ML\PersistentModel;
use Rubix\ML\Persisters\Filesystem;
use Rubix\ML\Datasets\Unlabeled;

class GoldForecaster
{
    public function forecast(int $days = 14): int
    {
        $run = GoldModelRun::query()->latest('trained_at')->firstOrFail();

        // load model from disk (PersistentModel::load pattern is supported) :contentReference[oaicite:9]{index=9}
        $model = PersistentModel::load(new Filesystem($run->model_path));

        $lookback = (int) $run->lookback;

        $rows = GoldPrice::query()->orderBy('date')->get(['date', 'price_usd']);
        $asOfDate = $rows->last()->date;
        $series = $rows->pluck('price_usd')->map(fn ($v) => (float)$v)->all();

        if (count($series) < $lookback) {
            throw new \RuntimeException('Not enough data for forecasting window.');
        }

        $fe = new GoldFeatureEngineer();
        $window = array_slice($series, -$lookback);
        $count = 0;

        for ($i = 1; $i <= $days; $i++) {
            $sample = $fe->makeSample($window);

            $pred = (float) $model->predict(new Unlabeled([$sample]))[0];

            $targetDate = \Carbon\Carbon::parse($asOfDate)->addDays($i);

            GoldForecast::query()->updateOrCreate(
                [
                    'as_of_date' => $asOfDate,
                    'target_date' => $targetDate->toDateString(),
                    'gold_model_run_id' => $run->id,
                ],
                [
                    'predicted_usd' => $pred,
                    'lower_usd' => null,
                    'upper_usd' => null,
                ]
            );

            array_shift($window);
            $window[] = $pred;

            $count++;
        }

        return $count;
    }
}
