<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category_id',
        'description',
        'price',
        'quantity',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    // 🔹 One image only, via morphOne
    public function picture()
    {
        return $this->morphOne(PictureUrl::class, 'imageable');
    }

    // 🔹 Clean, safe accessor
    public function getImageUrlAttribute(): string
    {
        // Safely get the url (null if no picture)
        $path = optional($this->picture)->url;

        // Fallback placeholder if no image at all
        if (! $path) {
            return asset('images/placeholder-product.png');
        }

        // If already a full external URL
        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        // Otherwise assume it's a storage path
        return asset('storage/'.ltrim($path, '/'));
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function transactions()
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function pawnItems()
    {
        return $this->hasMany(PawnItem::class);
    }

    public function repairs()
    {
        return $this->hasMany(Repair::class);
    }

    // Alias if you really want it
    public function pictureUrl()
    {
        return $this->picture();
    }
}
