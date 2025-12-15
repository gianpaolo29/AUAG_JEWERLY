<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoldPrice extends Model
{
    protected $fillable = [
        'date',
        'price_usd',
        'source',
    ];

    protected $casts = [
        'date' => 'date',
        'price_usd' => 'float',
    ];
}
