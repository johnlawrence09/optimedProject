<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseLocation extends Model
{
    use HasFactory;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'name',
        'warehouse_id'
    ];

    public function warehouse() {

        return $this->belongsTo('App\Models\Warehouse');
    }
}
