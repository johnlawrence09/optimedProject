<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'date', 'Ref', 'is_pos', 'client_id', 'GrandTotal', 'qte_retturn', 'TaxNet', 'tax_rate', 'notes',
        'total_retturn', 'warehouse_id', 'user_id', 'statut', 'discount', 'shipping',
        'paid_amount', 'payment_statut', 'created_at', 'updated_at', 'deleted_at','shipping_status'
    ];

    protected $casts = [
        'is_pos' => 'integer',
        'GrandTotal' => 'double',
        'qte_retturn' => 'double',
        'total_retturn' => 'double',
        'user_id' => 'integer',
        'client_id' => 'integer',
        'warehouse_id' => 'integer',
        'discount' => 'double',
        'shipping' => 'double',
        'TaxNet' => 'double',
        'tax_rate' => 'double',
        'paid_amount' => 'double',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->Ref = static::generateReference(); // Generate and assign reference
        });
    }

    private static function generateReference()
    {
        $code = 'SO-';
        $year = date('y');
        $dayOfYear = date('z') + 1;

        $prefix = $code.$year.$dayOfYear;

        $latest = static::orderBy('created_at', 'desc')->first();

        $number = $latest ? intval(substr($latest->code, strlen($prefix))) + 1 : '000001';
        $paddedNumber = str_pad($number, 6, '0', STR_PAD_LEFT);

        return $prefix . $paddedNumber;
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function details()
    {
        return $this->hasMany('App\Models\SaleDetail');
    }

    public function client()
    {
        return $this->belongsTo('App\Models\Client');
    }

    public function facture()
    {
        return $this->hasMany('App\Models\PaymentSale');
    }

    public function warehouse()
    {
        return $this->belongsTo('App\Models\Warehouse');
    }

    public function receipts()
    {
        return $this->hasMany('App\Models\SalesReceive');
    }

    public function productwarehouse()
    {
        return $this->hasMany('App\Models\product_warehouse');
    }

    public function shipment()
    {
        return $this->hasOne('App\Models\Shipment');
    }


}
