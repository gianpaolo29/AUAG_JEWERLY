<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PawnItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'title',
        'description',
        'price',
        'interest_cost',
        'due_date',
        'status',
    ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function pictures()
    {
        return $this->morphMany(PictureUrl::class, 'imageable');
    }

    // 👇 SAME IDEA AS IN Product::getImageUrlAttribute()
    public function getImageUrlAttribute(): string
    {
        // first() because morphMany = multiple records
        $pic = $this->pictures->first();

        if (! $pic || ! $pic->url) {
            return asset('images/placeholder-product.png');
        }

        $path = $pic->url;

        // already full URL
        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        // stored in storage/app/public/...
        return asset('storage/'.ltrim($path, '/'));
    }

    protected $casts = [
        'due_date' => 'date',
    ];
}
