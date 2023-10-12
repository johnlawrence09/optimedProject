<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseReturn extends Model
{
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'date', 'Ref', 'GrandTotal',
        'user_id', 'discount', 'shipping',
        'warehouse_id', 'provider_id', 'notes', 'TaxNet', 'tax_rate', 'statut',
        'paid_amount', 'payment_statut', 'created_at', 'updated_at', 'deleted_at', 'expiration_date', 'lot_number',
    ];

    protected $casts = [
        'GrandTotal' => 'double',
        'user_id' => 'integer',
        'provider_id' => 'integer',
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
        $code = 'RT-';
        $year = date('y');
        $dayOfYear = date('z') + 1;

        $prefix = $code . $year . $dayOfYear;

        $latest = static::orderBy('created_at', 'desc')->first();

        if ($latest) {
            // Extract the number part and increment
            $latestNumber = (int) substr($latest->Ref, strlen($prefix));
            $number = $latestNumber + 1;
        } else {
            // If no previous record, start from 1
            $number = 1;
        }

        // Ensure the number is 6 digits long
        $paddedNumber = str_pad($number, 6, '0', STR_PAD_LEFT);

        return $prefix . $paddedNumber;
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function details()
    {
        return $this->hasMany('App\Models\PurchaseReturnDetails');
    }

    public function product()
    {
        return $this->belongsTo('App\Models\Product');
    }

    public function provider()
    {
        return $this->belongsTo('App\Models\Provider');
    }

    public function warehouse()
    {
        return $this->belongsTo('App\Models\Warehouse');
    }

    public function facture()
    {
        return $this->hasMany('App\Models\PaymentPurchaseReturns');
    }

}
