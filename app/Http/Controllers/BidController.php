<?php

namespace App\Http\Controllers;

use App\Models\Bid;
use App\Models\Car;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class BidController extends Controller
{
    public function store(Request $request, int $id): JsonResponse
    {
        $car = Car::whereNull('deleted_at')->find($id);

        if (!$car) {
            return response()->json(['message' => 'Car not found'], 404);
        }

        if ($car->is_finished) {
            return response()->json(['message' => 'Auction has ended'], 400);
        }

        if ($car->ends_at->isPast()) {
            $car->update(['is_finished' => true, 'is_live' => false]);
            return response()->json(['message' => 'Auction has ended'], 400);
        }

        $validator = Validator::make($request->all(), [
            'amount' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        if ($request->amount <= $car->current_bid) {
            return response()->json([
                'message'     => 'Bid must be higher than current bid',
                'current_bid' => $car->current_bid,
            ], 400);
        }

        $user = JWTAuth::parseToken()->authenticate();

        $bid = DB::transaction(function () use ($request, $car, $user) {
            $bid = Bid::create([
                'car_id'  => $car->id,
                'user_id' => $user->id,
                'amount'  => $request->amount,
            ]);

            $car->update(['current_bid' => $request->amount]);

            return $bid;
        });

        return response()->json([
            'id'         => $bid->id,
            'user_id'    => $bid->user_id,
            'car_id'     => $bid->car_id,
            'amount'     => $bid->amount,
            'created_at' => $bid->created_at,
            'message'    => 'Bid placed successfully. You are now leading!',
        ], 201);
    }
}