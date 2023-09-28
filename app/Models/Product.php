<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'code', 'Type_barcode', 'name', 'cost', 'price', 'unit_id', 'unit_sale_id', 'unit_purchase_id',
        'stock_alert', 'category_id', 'sub_category_id', 'is_variant','is_imei',
        'tax_method', 'image', 'brand_id', 'is_active', 'note',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->code = static::generateReference(); // Generate and assign reference
        });
    }

    private static function generateReference()
    {
        $code = 'PROD';
        $year = date('y');
        $dayOfYear = date('z') + 1;

        $prefix = $code.$year.$dayOfYear;

        $latest = static::orderBy('created_at', 'desc')->first();

        $number = $latest ? intval(substr($latest->code, strlen($prefix))) + 1 : '000001';
        $paddedNumber = str_pad($number, 6, '0', STR_PAD_LEFT);

        return $prefix . $paddedNumber;
    }

    protected $casts = [
        'category_id' => 'integer',
        'sub_category_id' => 'integer',
        'unit_id' => 'integer',
        'unit_sale_id' => 'integer',
        'unit_purchase_id' => 'integer',
        'is_variant' => 'integer',
        'is_imei' => 'integer',
        'brand_id' => 'integer',
        'is_active' => 'integer',
        'cost' => 'double',
        'price' => 'double',
        'stock_alert' => 'double',
        'TaxNet' => 'double',
    ];

    public function ProductVariant()
    {
        return $this->belongsTo('App\Models\ProductVariant');
    }

    public function PurchaseDetail()
    {
        return $this->belongsTo('App\Models\PurchaseDetail');
    }

    public function SaleDetail()
    {
        return $this->belongsTo('App\Models\SaleDetail');
    }

    public function QuotationDetail()
    {
        return $this->belongsTo('App\Models\QuotationDetail');
    }

    public function category()
    {
        return $this->belongsTo('App\Models\Category');
    }

    public function unit()
    {
        return $this->belongsTo('App\Models\Unit', 'unit_id');
    }

    public function unitPurchase()
    {
        return $this->belongsTo('App\Models\Unit', 'unit_purchase_id');
    }

    public function unitSale()
    {
        return $this->belongsTo('App\Models\Unit', 'unit_sale_id');
    }

    public function brand()
    {
        return $this->belongsTo('App\Models\Brand');
    }

}
