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
            'id'         => $user->id,
            'nickname'   => $user->nickname,
            'email'      => $user->email,
            'phone'      => $user->phone,
            'city'       => $user->city,
            'avatar_url' => $user->avatar_url,
            'role'       => $user->role,
            'created_at' => $user->created_at,
            'bids'       => $bids,
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $user = JWTAuth::parseToken()->authenticate();

        $validator = Validator::make($request->all(), [
            'phone' => 'nullable|string|max:20|unique:users,phone,' . $user->id,
            'city'  => 'nullable|string|max:100',
        ], [
            'phone.unique' => 'Номер телефона уже используется',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $user->update($request->only(['phone', 'city']));

        return response()->json([
            'message' => 'Profile updated successfully',
            'user'    => [
                'id'         => $user->id,
                'nickname'   => $user->nickname,
                'email'      => $user->email,
                'phone'      => $user->phone,
                'city'       => $user->city,
                'avatar_url' => $user->avatar_url,
            ],
        ]);
    }

    public function uploadAvatar(Request $request): JsonResponse
    {
        $user = JWTAuth::parseToken()->authenticate();

        $validator = Validator::make($request->all(), [
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ], [
            'avatar.required' => 'Файл аватара обязателен',
            'avatar.image'    => 'Файл должен быть изображением',
            'avatar.max'      => 'Размер файла не должен превышать 5MB',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        // Удаляем старый аватар если есть
        if ($user->avatar_url) {
            $oldPath = str_replace(config('app.url') . '/storage/', '', $user->avatar_url);
            Storage::disk('public')->delete($oldPath);
        }

        $file     = $request->file('avatar');
        $filename = 'avatars/' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
        $file->storeAs('', $filename, 'public');

        $avatarUrl = config('app.url') . '/storage/' . $filename;
        $user->update(['avatar_url' => $avatarUrl]);

        return response()->json([
            'message'    => 'Avatar uploaded successfully',
            'avatar_url' => $avatarUrl,
        ]);
    }

    public function myBids(Request $request): JsonResponse
    {
        $user = JWTAuth::parseToken()->authenticate();

        $query = Bid::with('car')->where('user_id', $user->id);

        $bids = $query->orderBy('created_at', 'desc')->get();

        $formatted = $bids->map(fn($bid) => $this->formatBid($bid));

        // Фильтр по статусу после маппинга
        if ($request->has('status') && $request->status !== 'all') {
            $formatted = $formatted->filter(
                fn($bid) => $bid['status'] === $request->status
            )->values();
        }

        return response()->json($formatted);
    }

    private function formatBid(Bid $bid): array
    {
        $car = $bid->car;

        $maxBid = $car ? $car->bids()->max('amount') : 0;
        $status = $bid->amount >= $maxBid ? 'leading' : 'outbid';

        return [
            'id'     => $bid->id,
            'car_id' => $bid->car_id,
            'car'    => $car ? [
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