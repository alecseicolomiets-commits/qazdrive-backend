<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'nickname', 'full_name', 'email', 'password', 'phone',
        'city', 'avatar_url', 'role', 'tariff', 'balance',
        'tariff_expires_at', 'is_active', 'last_login_at',
    ];

    protected $hidden = ['password', 'remember_token', 'deleted_at'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at'     => 'datetime',
        'tariff_expires_at' => 'datetime',
        'is_active'         => 'boolean',
        'password'          => 'hashed',
        'balance'           => 'integer',
    ];

    // Лимиты по тарифам
public const TARIFF_LIMITS = [
    // Покупатели — лимит ставок
    'БАЗОВЫЙ'  => 1,
    'СТАНДАРТ' => 3,
    'ДРАЙВ'    => PHP_INT_MAX,
    'БИЗНЕС'   => PHP_INT_MAX,
    'VIP'      => PHP_INT_MAX,
    // английские
    'base'     => 1,
    'standard' => 3,
    'drive'    => PHP_INT_MAX,
    'business' => PHP_INT_MAX,
    'vip'      => PHP_INT_MAX,
];

public const SELLER_TARIFF_LIMITS = [
    // Продавцы — лимит активных лотов
    'СТАРТ'        => 1,
    'ДИЛЕР'        => 5,
    'ПРОФИ'        => 20,
    'АВТОСАЛОН'    => 50,
    'АУКЦИОН-ХАУС' => PHP_INT_MAX,
];

    /**
     * Возвращает действующий тариф.
     * Если платный тариф истёк — возвращает 'base'.
     */
public function getEffectiveTariff(): string
{
    $tariff = $this->tariff ?? 'БАЗОВЫЙ';
    $tariffLower = mb_strtolower($tariff);

    $baseTariffs = ['base', 'базовый', 'старт'];
    if (in_array($tariffLower, $baseTariffs) || $tariff === null) {
        return in_array($tariffLower, ['старт']) ? 'СТАРТ' : 'БАЗОВЫЙ';
    }

    if ($this->tariff_expires_at && $this->tariff_expires_at->isPast()) {
        // Если продавец — откат на СТАРТ, если покупатель — на БАЗОВЫЙ
        return in_array($tariff, ['СТАРТ', 'ДИЛЕР', 'ПРОФИ', 'АВТОСАЛОН', 'АУКЦИОН-ХАУС'])
            ? 'СТАРТ'
            : 'БАЗОВЫЙ';
    }

    return $tariff;
}

    /**
     * Лимит активных ставок по текущему тарифу.
     */
    public function getBidLimit(): int
    {
        return self::TARIFF_LIMITS[$this->getEffectiveTariff()] ?? 1;
    }

    /**
     * Количество уникальных активных аукционов где пользователь участвует.
     * Активный = аукцион не завершён.
     */
    public function activeUniqueBidsCount(): int
    {
        return $this->bids()
            ->whereHas('car', function ($q) {
                $q->where('is_finished', false)
                  ->where('ends_at', '>', now());
            })
            ->distinct('car_id')
            ->count('car_id');
    }

    /**
     * Может ли пользователь поставить ещё одну ставку (на новый аукцион).
     */
    public function canPlaceBid(): bool
    {
        return $this->activeUniqueBidsCount() < $this->getBidLimit();
    }
/**
 * Лимит активных лотов для продавца
 */
public function getLotLimit(): int
{
    $tariff = $this->getEffectiveTariff();
    return self::SELLER_TARIFF_LIMITS[$tariff] ?? 1;
}

/**
 * Количество активных лотов продавца
 */
public function activeLotsCount(): int
{
    return \App\Models\Car::where('seller_id', $this->id)
        ->where('is_finished', false)
        ->where('ends_at', '>', now())
        ->count();
}

/**
 * Может ли продавец создать ещё один лот
 */
public function canCreateLot(): bool
{
    return $this->activeLotsCount() < $this->getLotLimit();
}
    // JWT
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return ['role' => $this->role];
    }

    // Relations
    public function bids()
    {
        return $this->hasMany(Bid::class);
    }

    public function views()
    {
        return $this->hasMany(View::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}