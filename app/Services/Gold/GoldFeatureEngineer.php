<?php

namespace App\Services\Gold;

class GoldFeatureEngineer
{
    public function makeSample(array $window): array
    {
        // $window: oldest -> newest
        $n = count($window);
        $last = $window[$n - 1];

        $lag1  = $window[$n - 2] ?? $last;
        $lag2  = $window[$n - 3] ?? $lag1;
        $lag7  = $window[$n - 8] ?? $lag2;
        $lag14 = $window[$n - 15] ?? $lag7;

        $ma7  = $this->avg(array_slice($window, -7));
        $ma14 = $this->avg(array_slice($window, -14));
        $ma30 = $this->avg(array_slice($window, -30));

        $vol14 = $this->std($this->returns(array_slice($window, -15))); // 14 returns
        $rsi14 = $this->rsi(array_slice($window, -15));

        return [
            (float) $last,
            (float) $lag1,
            (float) $lag2,
            (float) $lag7,
            (float) $lag14,
            (float) $ma7,
            (float) $ma14,
            (float) $ma30,
            (float) $vol14,
            (float) $rsi14,
        ];
    }

    private function avg(array $xs): float
    {
        $c = count($xs);
        return $c ? array_sum($xs) / $c : 0.0;
    }

    private function returns(array $xs): array
    {
        $rets = [];
        for ($i = 1; $i < count($xs); $i++) {
            $prev = (float) $xs[$i - 1];
            $cur  = (float) $xs[$i];
            $rets[] = $prev != 0.0 ? (($cur / $prev) - 1.0) : 0.0;
        }
        return $rets;
    }

    private function std(array $xs): float
    {
        $n = count($xs);
        if ($n < 2) return 0.0;
        $mean = array_sum($xs) / $n;
        $var = 0.0;
        foreach ($xs as $x) $var += ($x - $mean) ** 2;
        return sqrt($var / ($n - 1));
    }

    private function rsi(array $prices): float
    {
        $gains = 0.0; $losses = 0.0;
        for ($i = 1; $i < count($prices); $i++) {
            $diff = (float)$prices[$i] - (float)$prices[$i - 1];
            if ($diff >= 0) $gains += $diff;
            else $losses += abs($diff);
        }
        $avgGain = $gains / 14.0;
        $avgLoss = $losses / 14.0;
        if ($avgLoss == 0.0) return 100.0;
        $rs = $avgGain / $avgLoss;
        return 100.0 - (100.0 / (1.0 + $rs));
    }
}
