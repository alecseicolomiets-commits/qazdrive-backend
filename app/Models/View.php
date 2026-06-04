<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class View extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'car_id',
        'user_id',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    public function car()
    {
        return $this->belongsTo(Car::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}