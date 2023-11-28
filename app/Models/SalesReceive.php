<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesReceive extends Model
{
    use HasFactory;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'date', 'Ref', 'provider_id', 'warehouse_id', 'GrandTotal',
        'discount', 'shipping', 'statut', 'notes', 'TaxNet', 'tax_rate', 'paid_amount',
        'payment_statut', 'created_at', 'updated_at', 'deleted_at',
    ];

    protected $casts = [
        'provider_id' => 'integer',
        'warehouse_id' => 'integer',
        'GrandTotal' => 'double',
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
        $code = 'SR';
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

    public function detailsRecieved()
    {
        return $this->hasMany('App\Models\SalesReceiveDetail');
    }

    public function client()
    {
        return $this->belongsTo('App\Models\Client');
    }

    public function facture()
    {
        return $this->hasMany('App\Models\PaymentPurchase','sales_id', 'sales_id');
    }

    public function warehouse()
    {
        return $this->belongsTo('App\Models\Warehouse');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function sale()
    {
        return $this->belongsTo('App\Models\Sale');
    }

    public function details()
    {
        return $this->hasMany('App\Models\SaleDetail','sale_id','sale_id');
    }

    public function shipment()
    {
        return $this->hasMany('App\Models\Shipment','sale_id','sale_id');
    }
}
