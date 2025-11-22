<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'customer_id',
        'staff_id',
        'type',
    ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function items()
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function pawnItem()
    {
        return $this->belongsTo(PawnItem::class, 'pawn_item_id');
    }
}
