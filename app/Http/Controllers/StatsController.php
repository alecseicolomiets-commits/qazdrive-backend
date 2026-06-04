<?php

namespace App\Http\Controllers;

use App\Models\Bid;
use App\Models\Car;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class StatsController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'total_cars'       => Car::whereNull('deleted_at')->count(),
            'active_auctions'  => Car::whereNull('deleted_at')->where('is_finished', false)->where('is_live', true)->count(),
            'total_users'      => User::whereNull('deleted_at')->count(),
            'total_bids'       => Bid::count(),
        ]);
    }
}