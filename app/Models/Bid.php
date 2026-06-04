<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bid extends Model
{
    use HasFactory;

    protected $fillable = [
        'car_id',
        'user_id',
        'amount',
    ];

    protected $casts = [
        'amount' => 'integer',
    ];

    public function car()
    {
        return $this->belongsTo(Car::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getStatusAttribute(): string
    {
        $car = $this->car;
        if (!$car) {
            return 'unknown';
        }

        $maxBid = $car->bids()->max('amount');
        return $this->amount >= $maxBid ? 'leading' : 'outbid';
    }
}