<?php

namespace App\Http\Controllers;

use App\Models\Bid;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends Controller
{
    public function profile(): JsonResponse
    {
        $user = JWTAuth::parseToken()->authenticate();

        $bids = Bid::with('car')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($bid) => $this->formatBid($bid));

        return response()->json([
            'id'                => $user->id,
            'nickname'          => $user->nickname,
            'email'             => $user->email,
            'phone'             => $user->phone,
            'city'              => $user->city,
            'avatar_url'        => $user->avatar_url,
            'role'              => $user->role,
            'balance'           => $user->balance,
            'tariff'            => $user->tariff ?? 'БАЗОВЫЙ',
            'tariff_expires_at' => $user->tariff_expires_at,
            'created_at'        => $user->created_at,
            'bids'              => $bids,
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $user = JWTAuth::parseToken()->authenticate();

        $validator = Validator::make($request->all(), [
            'full_name'          => 'nullable|string|max:255',
            'phone'              => 'nullable|string|max:20|unique:users,phone,' . $user->id,
            'city'               => 'nullable|string|max:100',
            'balance'            => 'nullable|numeric|min:0',
            'tariff' => 'nullable|string|in:БАЗОВЫЙ,СТАНДАРТ,ДРАЙВ,БИЗНЕС,VIP,СТАРТ,ДИЛЕР,ПРОФИ,АВТОСАЛОН,АУКЦИОН-ХАУС',
            'tariff_expires_at'  => 'nullable|date',
        ], [
            'phone.unique'  => 'Номер телефона уже используется',
            'tariff.in'     => 'Недопустимый тариф',
            'balance.min'   => 'Баланс не может быть отрицательным',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        // Если меняется тариф — проверяем баланс
        if ($request->has('tariff') && $request->tariff !== ($user->tariff ?? 'БАЗОВЫЙ')) {
            $tariffPrices = [
                'БАЗОВЫЙ'  => 0,
                'СТАНДАРТ' => 25000,
                'ДРАЙВ'    => 55000,
                'БИЗНЕС'   => 125000,
                'VIP'      => 250000,
            ];
            $price = $tariffPrices[$request->tariff] ?? 0;

            // Если цена передана через balance — уже учтена на фронте
            // Дополнительная проверка на случай прямого вызова API
            if ($price > 0 && !$request->has('balance')) {
                if ($user->balance < $price) {
                    return response()->json([
                        'message' => 'Недостаточно средств для смены тарифа',
                    ], 400);
                }
                $user->balance -= $price;
            }

            $user->tariff = $request->tariff;
            $user->tariff_expires_at = $request->tariff_expires_at
                ?? ($price > 0 ? now()->addYear() : null);
        }

        if ($request->has('full_name')) {
            $user->full_name = $request->full_name;
            $user->nickname  = explode(' ', trim($request->full_name))[0];
        }

        if ($request->has('phone'))   $user->phone   = $request->phone;
        if ($request->has('city'))    $user->city    = $request->city;
        if ($request->has('balance')) $user->balance = $request->balance;

        $user->save();

        return response()->json([
            'message' => 'Profile updated successfully',
            'user'    => [
                'id'                => $user->id,
                'nickname'          => $user->nickname,
                'email'             => $user->email,
                'phone'             => $user->phone,
                'city'              => $user->city,
                'avatar_url'        => $user->avatar_url,
                'balance'           => $user->balance,
                'tariff'            => $user->tariff,
                'tariff_expires_at' => $user->tariff_expires_at,
            ],
        ]);
    }

    public function uploadAvatar(Request $request): JsonResponse
    {
        $user = JWTAuth::parseToken()->authenticate();

        $validator = Validator::make($request->all(), [
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Ошибка валидации', 'errors' => $validator->errors()], 422);
        }

        if ($user->avatar_url) {
            $oldPath = str_replace(config('app.url') . '/storage/', '', $user->avatar_url);
            Storage::disk('public')->delete($oldPath);
        }

        $file     = $request->file('avatar');
        $filename = 'avatars/' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
        $file->storeAs('', $filename, 'public');

        $avatarUrl = config('app.url') . '/storage/' . $filename;
        $user->update(['avatar_url' => $avatarUrl]);

        return response()->json(['message' => 'Avatar uploaded successfully', 'avatar_url' => $avatarUrl]);
    }

    public function myBids(Request $request): JsonResponse
    {
        $user = JWTAuth::parseToken()->authenticate();

        $bids = Bid::with('car')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $formatted = $bids->map(fn($bid) => $this->formatBid($bid));

        if ($request->has('status') && $request->status !== 'all') {
            $formatted = $formatted->filter(fn($bid) => $bid['status'] === $request->status)->values();
        }

        return response()->json($formatted);
    }

    private function formatBid(Bid $bid): array
    {
        $car    = $bid->car;
        $maxBid = $car ? $car->bids()->max('amount') : 0;
        $status = $bid->amount >= $maxBid ? 'leading' : 'outbid';

        return [
            'id'         => $bid->id,
            'car_id'     => $bid->car_id,
            'car'        => $car ? [
                'id'        => $car->id,
                'make'      => $car->make,
                'model'     => $car->model,
                'year'      => $car->year,
                'image_url' => $car->image_url,
            ] : null,
            'amount'     => $bid->amount,
            'status'     => $status,
            'created_at' => $bid->created_at,
        ];
    }
}
