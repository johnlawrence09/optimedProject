<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseReceive extends Model
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

    public function details()
    {
        return $this->hasMany('App\Models\PurchaseReceiveDetail');
    }

    public function provider()
    {
        return $this->belongsTo('App\Models\Provider');
    }

    public function facture()
    {
        return $this->hasMany('App\Models\PaymentPurchase','purchase_id', 'purchase_id');
    }

    public function warehouse()
    {
        return $this->belongsTo('App\Models\Warehouse');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function purchase()
    {
        return $this->belongsTo('App\Models\Purchase');
    }
}
