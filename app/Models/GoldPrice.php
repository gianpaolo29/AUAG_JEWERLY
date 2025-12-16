<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoldPrice extends Model
{
    protected $fillable = [
        'date',
        'price_usd',
        'source',
        'usd_to_php',
        'price_php_per_gram'

    ];

    protected $casts = [
        'date' => 'date',
        'price_usd' => 'float',
    ];
}
