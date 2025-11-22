<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionItem extends Model
{
    protected $fillable = [
        'transaction_id',
        'product_id',
        'pawn_item_id',
        'repair_id',
        'quantity',
        'unit_price',
        'line_total',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function pawnItem()
    {
        return $this->belongsTo(PawnItem::class);
    }

    public function repair()
    {
        return $this->belongsTo(Repair::class);
    }
}
