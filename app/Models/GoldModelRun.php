<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GoldModelRun extends Model
{
    protected $fillable = [
        'trained_at',
        'model_path',
        'lookback',
        'metrics',
    ];

    protected $casts = [
        'trained_at' => 'datetime',
        'lookback' => 'integer',
        'metrics' => 'array',
    ];

    public function forecasts(): HasMany
    {
        return $this->hasMany(GoldForecast::class);
    }
}
