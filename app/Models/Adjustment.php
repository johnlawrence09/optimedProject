<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Adjustment extends Model
{
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'date', 'Ref', 'user_id', 'warehouse_id',
        'items', 'notes', 'created_at', 'updated_at', 'deleted_at',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'warehouse_id' => 'integer',
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
        $code = 'AD-';
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
        return $this->hasMany('App\Models\AdjustmentDetail');
    }

    public function warehouse()
    {
        return $this->belongsTo('App\Models\Warehouse');
    }

}
