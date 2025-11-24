<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
        'material',
        'size',
        'style',
    ];

    protected $casts = [
        'status' => 'boolean',
        'price'  => 'decimal:2',
    ];

    public const MATERIAL_OPTIONS = [
        'gold',
        'silver',
        'stainless',
    ];

    public const STYLE_OPTIONS = [
        'minimalist',
        'vintage',
        'classic',
        'modern',
        'luxury',
        'casual',
        'wedding'
    ];

    public function favouritedByUsers()
    {
        return $this->belongsToMany(User::class, 'favorites')
            ->withTimestamps();
    }

    public function picture()
    {
        return $this->morphOne(PictureUrl::class, 'imageable');
    }

    protected $appends = ['image_url'];


    public function getImageUrlAttribute(): string
{
    $path = $this->picture->url ?? null;

    if (!$path) {
        return asset('images/placeholder-product.png');
    }

    // If full URL, return it
    if (Str::startsWith($path, ['http://', 'https://'])) {
        return $path;
    }

    // Now using public/
    return asset($path);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    // If you still use these in other parts of the app:
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

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
    

}
