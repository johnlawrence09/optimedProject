<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mapping extends Model
{
    use HasFactory;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'product_id', 'warehouse_id', 'warehouse_location_id',
    ];

    public function product() {
        return $this->belongsTo('App\Models\Product');
    }

    public function warehouse() {
        return $this->belongsTo('App\Models\Warehouse');
    }

    public function warehouse_location() {
        return $this->belongsTo('App\Models\WarehouseLocation');
    }
}
