<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoldForecast extends Model
{
    protected $fillable = [
        'as_of_date',
        'target_date',
        'predicted_usd',
        'lower_usd',
        'upper_usd',
        'gold_model_run_id',
    ];

    protected $casts = [
        'as_of_date' => 'date',
        'target_date' => 'date',
        'predicted_usd' => 'float',
        'lower_usd' => 'float',
        'upper_usd' => 'float',
        'gold_model_run_id' => 'integer',
    ];

    public function run(): BelongsTo
    {
        return $this->belongsTo(GoldModelRun::class, 'gold_model_run_id');
    }
}
