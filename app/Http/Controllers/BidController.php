<?php

namespace App\Http\Controllers;

use App\Models\Bid;
use App\Models\Car;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BidController extends Controller
{
    public function store(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1'
        ]);

        $bidAmount = $request->amount;
        $user      = auth()->user();

        // ── ПРОВЕРКА ЛИМИТА ТАРИФА ────────────────────────────────
        $alreadyBidOnThisCar = Bid::where('user_id', $user->id)
            ->where('car_id', $id)
            ->exists();

        if (!$alreadyBidOnThisCar && !$user->canPlaceBid()) {
            $limit       = $user->getBidLimit();
            $tariff      = $user->getEffectiveTariff();
            $tariffNames = [
                    'base'     => 'Базовый',
                    'standard' => 'Стандарт',
                    'drive'    => 'Драйв',
                    'business' => 'Бизнес',
                    'vip'      => 'VIP',
                    'БАЗОВЫЙ'  => 'Базовый',
                    'СТАНДАРТ' => 'Стандарт',
                    'ДРАЙВ'    => 'Драйв',
                    'БИЗНЕС'   => 'Бизнес',
                    'VIP'      => 'VIP',
    ];  

            return response()->json([
                'message' => sprintf(
                    'Достигнут лимит активных ставок по тарифу «%s» (%d шт.). Дождитесь завершения одного из аукционов или улучшите тариф.',
                    $tariffNames[$tariff] ?? $tariff,
                    $limit
                ),
                'error_code'    => 'BID_LIMIT_REACHED',
                'current_limit' => $limit,
                'active_bids'   => $user->activeUniqueBidsCount(),
            ], 403);
        }
        // ─────────────────────────────────────────────────────────

        return DB::transaction(function () use ($id, $bidAmount, $user) {
            $car = Car::findOrFail($id);

            if ($car->is_finished || $car->ends_at < now()) {
                return response()->json(['message' => 'Аукцион по этому автомобилю уже завершен'], 400);
            }

            $currentBid     = $car->current_bid ?? $car->starting_price ?? 0;
            $minIncrement   = round($currentBid * 0.05);
            $minRequiredBid = $currentBid + $minIncrement;

            if ($bidAmount < $minRequiredBid) {
                return response()->json([
                    'message' => 'Минимальный шаг повышения составляет 5% (' . number_format($minIncrement, 0, '', ' ') . ' ₸). Минимальная сумма ставки: ' . number_format($minRequiredBid, 0, '', ' ') . ' ₸'
                ], 400);
            }

            $freezeAmount = $bidAmount * 0.10;

            if ($user->balance < $freezeAmount) {
                return response()->json([
                    'message' => 'Недостаточно средств. Для ставки требуется заморозить 10% от суммы (' . number_format($freezeAmount, 0, '', ' ') . ' ₸). Ваш баланс: ' . number_format($user->balance, 0, '', ' ') . ' ₸'
                ], 400);
            }

            $previousBid = Bid::where('car_id', $id)
                ->orderBy('amount', 'desc')
                ->first();

            if ($previousBid && $previousBid->user_id !== $user->id) {
                $previousBidder = User::find($previousBid->user_id);
                if ($previousBidder) {
                    $previousBidder->balance += $previousBid->amount * 0.10;
                    $previousBidder->save();
                }
            }

            $user->balance -= $freezeAmount;
            $user->save();

            $bid = Bid::create([
                'amount'  => $bidAmount,
                'user_id' => $user->id,
                'car_id'  => $id,
            ]);

            $car->current_bid = $bidAmount;
            $car->save();

            return response()->json([
                'message'      => 'Ставка успешно принята!',
                'current_bid'  => $car->current_bid,
                'user_balance' => $user->balance,
                'bid'          => $bid,
            ]);
        });
    }
}