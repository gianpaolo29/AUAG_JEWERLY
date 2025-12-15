<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'contact_no',
    ];

    public function pawnItems()
    {
        return $this->hasMany(PawnItem::class);
    }

    public function repairs()
    {
        return $this->hasMany(Repair::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    
}
