<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class CarController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Car::query()->whereNull('deleted_at');

        if ($request->has('make') && $request->make !== '') {
            $query->where('make', $request->make);
        }

        $sort  = in_array($request->sort, ['created_at', 'current_bid']) ? $request->sort : 'created_at';
        $order = in_array($request->order, ['asc', 'desc']) ? $request->order : 'desc';
        $query->orderBy($sort, $order);

        $limit = min((int) ($request->limit ?? 50), 100);
        $page  = (int) ($request->page ?? 1);

        $total = $query->count();
        $cars  = $query->offset(($page - 1) * $limit)->limit($limit)->get();

        $data = $cars->map(fn($car) => $this->formatCarList($car));

        if ($request->has('page') || $request->has('limit')) {
            return response()->json([
                'data' => $data,
                'pagination' => [
                    'total'        => $total,
                    'per_page'     => $limit,
                    'current_page' => $page,
                    'last_page'    => (int) ceil($total / $limit),
                ],
            ]);
        }

        return response()->json($data);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $car = Car::whereNull('deleted_at')->find($id);

        if (!$car) {
            return response()->json(['message' => 'Car not found'], 404);
        }

        // Записываем просмотр
        $userId = null;
        try {
            $user   = JWTAuth::parseToken()->authenticate();
            $userId = $user?->id;
        } catch (\Exception $e) {
            // не авторизован — ок
        }

        View::create([
            'car_id'     => $car->id,
            'user_id'    => $userId,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);

        $bids = $car->bids()->orderBy('amount', 'desc')->get()->map(fn($bid) => [
            'id'         => $bid->id,
            'user_id'    => $bid->user_id,
            'amount'     => $bid->amount,
            'created_at' => $bid->created_at,
        ]);

        return response()->json([
            'id'             => $car->id,
            'make'           => $car->make,
            'model'          => $car->model,
            'year'           => $car->year,
            'vin'            => $car->vin,
            'location'       => $car->location,
            'description'    => $car->description,
            'mileage'        => $car->mileage,
            'color'          => $car->color,
            'transmission'   => $car->transmission,
            'fuel_type'      => $car->fuel_type,
            'engine_volume'  => $car->engine_volume,
            'power_hp'       => $car->power_hp,
            'image_url'      => $car->image_url,
            'is_live'        => $car->is_live,
            'is_top'         => $car->is_top,
            'is_finished'    => $car->is_finished,
            'current_bid'    => $car->current_bid,
            'starting_price' => $car->starting_price,
            'time_left'      => $car->time_left,
            'bids_count'     => $car->bids_count,
            'views_count'    => $car->views_count,
            'bids'           => $bids,
            'created_at'     => $car->created_at,
            'ends_at'        => $car->ends_at,
        ]);
    }

    private function formatCarList(Car $car): array
    {
        return [
            'id'          => $car->id,
            'make'        => $car->make,
            'model'       => $car->model,
            'year'        => $car->year,
            'vin'         => $car->vin,
            'location'    => $car->location,
            'current_bid' => $car->current_bid,
            'image_url'   => $car->image_url,
            'is_live'     => $car->is_live,
            'is_top'      => $car->is_top,
            'is_finished' => $car->is_finished,
            'time_left'   => $car->time_left,
            'bids_count'  => $car->bids_count,
            'views_count' => $car->views_count,
            'created_at'  => $car->created_at,
            'ends_at'     => $car->ends_at,
        ];
    }
}