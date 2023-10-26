<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesReceiveDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'id', 'sales_id','sales_unit_id', 'quantity', 'product_id', 'total', 'product_variant_id',
        'cost', 'TaxNet', 'discount', 'discount_method', 'tax_method',
    ];

    protected $casts = [
        'total' => 'double',
        'cost' => 'double',
        'TaxNet' => 'double',
        'discount' => 'double',
        'quantity' => 'double',
        'p_id' => 'integer',
        'purchase_unit_id' => 'integer',
        'product_id' => 'integer',
        'product_variant_id' => 'integer',
    ];

    public function sale()
    {
        return $this->hasMany('App\Models\Sale');
    }

    public function product()
    {
        return $this->belongsTo('App\Models\Product');
    }

    public function receipts()
    {
        return $this->hasMany('App\Models\SalesReceive');
    }

    public function details()
    {
        return $this->hasMany('App\Models\SaleDetail','product_id','product_id');
    }


}
