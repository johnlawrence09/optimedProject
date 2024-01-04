<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'code', 'name', 'image'
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
        $latest = static::orderBy('created_at', 'desc')->first();
        $prefix = 'CAT-';
        $number = $latest ? intval(substr($latest->code, strlen($prefix))) + 1 : 1;
        $paddedNumber = str_pad($number, 3, '0', STR_PAD_LEFT);

        return $prefix . $paddedNumber;
    }
}
