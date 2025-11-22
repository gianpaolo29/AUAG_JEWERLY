<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Repair extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'description',
        'price',
        'status',
    ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    // 🔁 ONE picture only, polymorphic
    public function picture()
    {
        return $this->morphOne(PictureUrl::class, 'imageable');
    }

    // Optional helper if you want a URL accessor
    public function getImageUrlAttribute()
    {
        $path = $this->picture->url ?? null;

        if (! $path) {
            return asset('images/placeholder-product.png');
        }

        if (\Illuminate\Support\Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        return asset('storage/'.ltrim($path, '/'));
    }
}
