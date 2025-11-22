<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use App\Models\PictureUrl;

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

    public function picture()
    {
        return $this->morphOne(PictureUrl::class, 'imageable');
    }


    public function getImageUrlAttribute(): string
    {
        // picture_urls.url could be:
        // - full URL (https://...) OR
        // - relative ('products/xyz.jpg')
        $path = $this->picture->url ?? null;

        if (!$path) {
            return asset('images/placeholder-product.png');
        }

        // If already full URL, just return as-is
        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        // Otherwise assume it’s in storage/app/public/...
        return asset('storage/' . ltrim($path, '/'));
    }

    

    // 👇 Category relation used in index blade
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

    public function pictureUrl()
    {
        return $this->picture();
    }

    

}
