<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShoppingCart extends Model
{
    use HasFactory;
    protected $table = "shopping_cart";
    // public $timestamps = false;

    public function shoppingcart(){
        return $this->hasMany(ShoppingCartDetails::class, 'cart_id', 'id');
    }
}
