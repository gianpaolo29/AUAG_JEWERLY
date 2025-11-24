<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductView extends Model
{
    protected $fillable = ['user_id', 'product_id'];

    public function ViewByUsers(){
        return $this->belongsToMany(User::class, 'viewed')
    }
}




