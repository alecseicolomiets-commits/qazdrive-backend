<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Car extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'seller_id',
        'make',
        'model',
        'year',
        'vin',
        'location',
        'city',
        'description',
        'mileage',
        'color',
        'condition',
        'transmission',
        'drive_type',
        'fuel_type',
        'engine_volume',
        'power_hp',
        'image_url',
        'is_live',
        'is_top',
        'is_finished',
        'status',
        'current_bid',
        'current_price',
        'starting_price',
        'start_price',
        'ends_at',
    ];

    protected $casts = [
        'is_live'        => 'boolean',
        'is_top'         => 'boolean',
        'is_finished'    => 'boolean',
        'ends_at'        => 'datetime',
        'engine_volume'  => 'float',
        'current_bid'    => 'integer',
        'current_price'  => 'integer',
        'starting_price' => 'integer',
        'start_price'    => 'integer',
        'mileage'        => 'integer',
        'power_hp'       => 'integer',
        'year'           => 'integer',
    ];

    public function bids()
    {
        return $this->hasMany(Bid::class);
    }

    public function views()
    {
        return $this->hasMany(View::class);
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    public function getTimeLeftAttribute(): string
    {
        if ($this->is_finished) return 'Завершен';
        $ends = $this->ends_at;
        if (!$ends || $ends->isPast()) return 'Завершен';
        $diff = now()->diff($ends);
        if ($diff->days > 0) {
            return $diff->days . 'd ' . sprintf('%02d:%02d:%02d', $diff->h, $diff->i, $diff->s);
        }
        return sprintf('%02d:%02d:%02d', $diff->h, $diff->i, $diff->s);
    }

    public function getBidsCountAttribute(): int
    {
        return $this->bids()->count();
    }

    public function getViewsCountAttribute(): int
    {
        return $this->views()->count();
    }
}