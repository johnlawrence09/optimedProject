<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transfer extends Model
{
    protected $table = 'transfers';
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'id', 'date','user_id', 'from_warehouse_id', 'to_warehouse_id',
        'items', 'statut', 'notes', 'GrandTotal', 'discount', 'shipping', 'TaxNet', 'tax_rate',
        'created_at', 'updated_at', 'deleted_at', 'expiration_date', 'lot_number',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'from_warehouse_id' => 'integer',
        'to_warehouse_id' => 'integer',
        'items' => 'double',
        'GrandTotal' => 'double',
        'discount' => 'double',
        'shipping' => 'double',
        'TaxNet' => 'double',
        'tax_rate' => 'double',

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
        $code = 'TR-';
        $year = date('y');
        $dayOfYear = date('z') + 1;

        $prefix = $code.$year.$dayOfYear;

        $latest = static::orderBy('created_at', 'desc')->first();
        $number = $latest ? intval(substr($latest->Ref, strlen($prefix))) + 1 : '000001';
        $paddedNumber = str_pad($number, 6, '0', STR_PAD_LEFT);

        return $prefix . $paddedNumber;
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function details()
    {
        return $this->hasMany('App\Models\TransferDetail');
    }

    public function from_warehouse()
    {
        return $this->belongsTo('App\Models\Warehouse', 'from_warehouse_id');
    }

    public function to_warehouse()
    {
        return $this->belongsTo('App\Models\Warehouse', 'to_warehouse_id');
    }

}
