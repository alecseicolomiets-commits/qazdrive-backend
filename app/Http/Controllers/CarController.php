<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Report;
use App\Models\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class CarController extends Controller
{
    // ── GET /cars ──────────────────────────────────────────────────────────
    public function index(Request $request): JsonResponse
    {
        $query = Car::query()->whereNull('deleted_at');

        if ($request->filled('make')) {
            $query->where('make', $request->make);
        }
        if ($request->filled('city')) {
            $query->where('city', $request->city);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('seller_id')) {
            $query->where('seller_id', $request->seller_id);
        }

        $sort  = in_array($request->sort, ['created_at', 'current_bid', 'current_price']) ? $request->sort : 'created_at';
        $order = in_array($request->order, ['asc', 'desc']) ? $request->order : 'desc';
        $query->orderBy($sort, $order);

        $limit = min((int) ($request->limit ?? 50), 100);
        $page  = (int) ($request->page ?? 1);

        $total = $query->count();
        $cars  = $query->offset(($page - 1) * $limit)->limit($limit)->get();

        $data = $cars->map(fn($car) => $this->formatCarList($car));

        if ($request->has('page') || $request->has('limit') || $request->has('seller_id')) {
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

    // ── GET /cars/{id} ─────────────────────────────────────────────────────
    public function show(Request $request, int $id): JsonResponse
    {
        $car = Car::whereNull('deleted_at')->find($id);

        if (!$car) {
            return response()->json(['message' => 'Car not found'], 404);
        }

        $userId = null;
        try {
            $user   = JWTAuth::parseToken()->authenticate();
            $userId = $user?->id;
        } catch (\Exception $e) {}

        View::create([
            'car_id'     => $car->id,
            'user_id'    => $userId,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);

        $bids = $car->bids()
            ->with('user:id,nickname')
            ->orderBy('amount', 'desc')
            ->get()
            ->map(fn($bid) => [
                'id'         => $bid->id,
                'user_id'    => $bid->user_id,
                'amount'     => $bid->amount,
                'created_at' => $bid->created_at,
                'user'       => $bid->user ? ['nickname' => $bid->user->nickname] : null,
            ]);

        return response()->json([
            'id'             => $car->id,
            'make'           => $car->make,
            'model'          => $car->model,
            'year'           => $car->year,
            'vin'            => $car->vin,
            'location'       => $car->location,
            'city'           => $car->city ?? $car->location,
            'description'    => $car->description,
            'mileage'        => $car->mileage,
            'color'          => $car->color,
            'condition'      => $car->condition,
            'transmission'   => $car->transmission,
            'drive_type'     => $car->drive_type,
            'fuel_type'      => $car->fuel_type,
            'engine_volume'  => $car->engine_volume,
            'power_hp'       => $car->power_hp,
            'image_url'      => $car->image_url,
            'images'         => $car->images ? json_decode($car->images) : [],
            'is_live'        => $car->is_live,
            'is_top'         => $car->is_top,
            'is_finished'    => $car->is_finished,
            'status'         => $car->status,
            'current_bid'    => $car->current_bid ?? $car->current_price,
            'current_price'  => $car->current_price ?? $car->current_bid,
            'starting_price' => $car->starting_price ?? $car->start_price,
            'start_price'    => $car->start_price ?? $car->starting_price,
            'time_left'      => $car->time_left,
            'bids_count'     => $car->bids_count,
            'views_count'    => $car->views_count,
            'bids'           => $bids,
            'created_at'     => $car->created_at,
            'ends_at'        => $car->ends_at,
        ]);
    }

    // ── POST /cars ─────────────────────────────────────────────────────────
    public function store(Request $request): JsonResponse
    {
        $user = null;
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (\Exception $e) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if (!in_array($user->role, ['seller', 'admin'])) {
            return response()->json(['message' => 'Только продавцы могут создавать лоты'], 403);
        }
        // Проверка лимита лотов по тарифу
if ($user->role === 'seller' && !$user->canCreateLot()) {
    $limit = $user->getLotLimit();
    $tariffNames = [
        'СТАРТ'        => 'Старт',
        'ДИЛЕР'        => 'Дилер',
        'ПРОФИ'        => 'Профи',
        'АВТОСАЛОН'    => 'Автосалон',
        'АУКЦИОН-ХАУС' => 'Аукцион-Хаус',
    ];
    $tariff = $user->getEffectiveTariff();
    return response()->json([
        'message' => sprintf(
            'Достигнут лимит активных лотов по тарифу «%s» (%d шт.). Улучшите тариф.',
            $tariffNames[$tariff] ?? $tariff,
            $limit
        ),
        'error_code'    => 'LOT_LIMIT_REACHED',
        'current_limit' => $limit,
        'active_lots'   => $user->activeLotsCount(),
    ], 403);
}

        $validator = Validator::make($request->all(), [
            'make'        => 'required|string|max:100',
            'model'       => 'required|string|max:100',
            'year'        => 'required|integer|min:1990|max:' . (date('Y') + 1),
            'start_price' => 'required|integer|min:1',
            'auction_end' => 'required|date|after:now',
        ], [
            'make.required'        => 'Укажите марку автомобиля',
            'model.required'       => 'Укажите модель',
            'year.required'        => 'Укажите год выпуска',
            'start_price.required' => 'Укажите стартовую цену',
            'auction_end.required' => 'Укажите дату окончания аукциона',
            'auction_end.after'    => 'Дата окончания должна быть в будущем',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $startPrice = (int) $request->start_price;

        $car = Car::create([
            'seller_id'      => $user->id,
            'make'           => $request->make,
            'model'          => $request->model,
            'year'           => (int) $request->year,
            'vin'            => $request->vin,
            'location'       => $request->city,
            'city'           => $request->city,
            'description'    => $request->description,
            'mileage'        => $request->mileage ? (int) str_replace([' ', ','], '', $request->mileage) : null,
            'color'          => $request->color,
            'condition'      => $request->condition,
            'transmission'   => $request->transmission,
            'drive_type'     => $request->drive_type,
            'fuel_type'      => $request->fuel_type,
            'engine_volume'  => $request->engine_volume
                ? min(9.9, (float) str_replace(',', '.', $request->engine_volume))
                : null,
            'image_url'      => '',
            'images'         => null,
            'is_live'        => true,
            'is_top'         => false,
            'is_finished'    => false,
            'status'         => 'active',
            'current_bid'    => $startPrice,
            'current_price'  => $startPrice,
            'starting_price' => $startPrice,
            'start_price'    => $startPrice,
            'ends_at'        => $request->auction_end,
        ]);

        return response()->json([
            'message' => 'Лот успешно опубликован',
            'car'     => $this->formatCarList($car),
        ], 201);
    }

    // ── POST /cars/{id}/images ─────────────────────────────────────────────
    public function uploadImages(Request $request, int $id): JsonResponse
    {
        $user = null;
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (\Exception $e) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $car = Car::whereNull('deleted_at')->find($id);
        if (!$car) {
            return response()->json(['message' => 'Car not found'], 404);
        }

        if ($car->seller_id !== $user->id && $user->role !== 'admin') {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validator = Validator::make($request->all(), [
            'images'   => 'required|array|min:1|max:10',
            'images.*' => 'required|image|mimes:jpeg,jpg,png,webp|max:5120',
        ], [
            'images.required' => 'Добавьте хотя бы одно фото',
            'images.*.image'  => 'Файл должен быть изображением',
            'images.*.max'    => 'Размер каждого фото не более 5MB',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $urls = [];
        foreach ($request->file('images') as $file) {
            $path   = $file->store("cars/{$car->id}", 'public');
            $urls[] = rtrim(config('app.url'), '/') . '/storage/' . $path;
        }

        $car->image_url = $urls[0];
        $car->images    = json_encode($urls);
        $car->save();

        return response()->json([
            'message'   => 'Фотографии загружены',
            'image_url' => $urls[0],
            'images'    => $urls,
        ]);
    }

    // ── POST /cars/{id}/view ───────────────────────────────────────────────
    public function recordView(Request $request, int $id): JsonResponse
    {
        $car = Car::whereNull('deleted_at')->find($id);
        if (!$car) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $userId = null;
        try {
            $user   = JWTAuth::parseToken()->authenticate();
            $userId = $user?->id;
        } catch (\Exception $e) {}

        View::create([
            'car_id'     => $car->id,
            'user_id'    => $userId,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);

        return response()->json(['message' => 'ok']);
    }

    // ── POST /cars/{id}/report ─────────────────────────────────────────────
    public function report(Request $request, int $id): JsonResponse
    {
        $car = Car::whereNull('deleted_at')->find($id);
        if (!$car) {
            return response()->json(['message' => 'Car not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:255',
        ], [
            'reason.required' => 'Укажите причину жалобы',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Укажите причину жалобы'], 422);
        }

        $userId = null;
        try {
            $user   = JWTAuth::parseToken()->authenticate();
            $userId = $user?->id;
        } catch (\Exception $e) {}

        Report::create([
            'car_id'  => $car->id,
            'user_id' => $userId,
            'reason'  => $request->reason,
            'comment' => $request->comment,
            'status'  => 'pending',
        ]);

        return response()->json(['message' => 'Жалоба принята, спасибо'], 201);
    }

    // ── DELETE /cars/{id} ─────────────────────────────────────────────────
    public function destroy(int $id): JsonResponse
    {
        $car = Car::find($id);
        if (!$car) {
            return response()->json(['message' => 'Not found'], 404);
        }
        $car->delete();
        return response()->json(['message' => 'Лот удалён']);
    }

    // ── Private ───────────────────────────────────────────────────────────
    private function formatCarList(Car $car): array
    {
        return [
            'id'            => $car->id,
            'make'          => $car->make,
            'model'         => $car->model,
            'year'          => $car->year,
            'vin'           => $car->vin,
            'location'      => $car->location,
            'city'          => $car->city ?? $car->location,
            'condition'     => $car->condition,
            'transmission'  => $car->transmission,
            'fuel_type'     => $car->fuel_type,
            'current_bid'   => $car->current_bid ?? $car->current_price,
            'current_price' => $car->current_price ?? $car->current_bid,
            'start_price'   => $car->start_price ?? $car->starting_price,
            'image_url'     => $car->image_url,
            'images'        => $car->images ? json_decode($car->images) : [],
            'is_live'       => $car->is_live,
            'is_top'        => $car->is_top,
            'is_finished'   => $car->is_finished,
            'status'        => $car->status,
            'time_left'     => $car->time_left,
            'bids_count'    => $car->bids_count,
            'views_count'   => $car->views_count,
            'created_at'    => $car->created_at,
            'ends_at'       => $car->ends_at,
            'seller_id'     => $car->seller_id,
        ];
    }
}