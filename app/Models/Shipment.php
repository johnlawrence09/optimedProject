<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'user_id','date','Ref', 'sale_id', 'customer_name', 'shipping_address', 'status', 'shipping_details','phone_number','email'

    ];

    protected $casts = [
        'user_id' => 'integer',
        'sale_id' => 'integer',
    ];


    public function sale()
    {
        return $this->hasOne('App\Models\Sale');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function saleReceive()
    {
        return $this->belongsTo('App\Models\SalesReceive','sale_id','sale_id' );
    }
}
