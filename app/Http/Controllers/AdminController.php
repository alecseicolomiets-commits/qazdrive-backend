<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Car;
use App\Models\Bid;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    // ─── Дашборд ─────────────────────────────────────────────────────────────

    public function dashboard(): JsonResponse
    {
        $totalUsers   = User::count();
        $activeUsers  = User::where('is_active', true)->count();
        $totalLots    = Car::count();
        $totalBids    = Bid::count();
        $totalTurnover = Bid::max('amount') ?? 0; // или sum — под твою логику

        $recentCars = Car::with(['bids' => function ($q) {
            $q->orderByDesc('amount')->limit(1);
        }])
        ->orderByDesc('created_at')
        ->limit(10)
        ->get()
        ->map(fn($car) => [
            'id'          => $car->id,
            'make'        => $car->make,
            'model'       => $car->model,
            'year'        => $car->year,
            'current_bid' => $car->current_bid ?? 0,
            'bids_count'  => $car->bids()->count(),
            'status'      => 'live',
            'image_url'   => $car->image_url,
        ]);

        return response()->json([
            'stats' => [
                'total_users'   => $totalUsers,
                'active_users'  => $activeUsers,
                'total_lots'    => $totalLots,
                'total_bids'    => $totalBids,
                'total_turnover'=> $totalTurnover,
            ],
            'recent_cars' => $recentCars,
        ]);
    }

    // ─── Пользователи ────────────────────────────────────────────────────────

    public function users(): JsonResponse
    {
        $users = User::withTrashed()
            ->orderByRaw("role = 'admin' DESC")
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($u) => $this->formatUser($u));

        return response()->json($users);
    }

    public function toggleBlock(int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        if ($user->role === 'admin') {
            return response()->json(['message' => 'Cannot block admin'], 403);
        }

        $user->update(['is_active' => !$user->is_active]);

        return response()->json([
            'id'     => $user->id,
            'status' => $user->is_active ? 'active' : 'blocked',
        ]);
    }

    public function changeTariff(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'tariff' => 'required|in:БАЗОВЫЙ,СТАНДАРТ,ДРАЙВ,БИЗНЕС,VIP',
        ]);

        $user = User::findOrFail($id);
        $user->update(['tariff' => $request->tariff]);

        return response()->json([
            'id'     => $user->id,
            'tariff' => $user->tariff,
        ]);
    }

    public function deleteUser(int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        if ($user->role === 'admin') {
            return response()->json(['message' => 'Cannot delete admin'], 403);
        }

        $user->delete(); // soft delete

        return response()->json(['message' => 'User deleted', 'id' => $id]);
    }

    // ─── Лоты ────────────────────────────────────────────────────────────────

    public function lots(): JsonResponse
    {
        $cars = Car::withCount('bids')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($car) => [
                'id'          => $car->id,
                'make'        => $car->make,
                'model'       => $car->model,
                'year'        => $car->year,
                'current_bid' => $car->current_bid ?? 0,
                'bids_count'  => $car->bids_count,
                'status'      => 'live',
                'image_url'   => $car->image_url,
            ]);

        return response()->json($cars);
    }

    public function deleteLot(string $id): JsonResponse
    {
        $car = Car::findOrFail($id);
        $car->delete();

        return response()->json(['message' => 'Lot deleted', 'id' => $id]);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function formatUser(User $user): array
    {
        return [
            'id'         => $user->id,
            'nickname'   => $user->nickname,
            'full_name'  => $user->full_name,
            'email'      => $user->email,
            'phone'      => $user->phone,
            'city'       => $user->city,
            'tariff'     => $user->tariff ?? 'БАЗОВЫЙ',
            'balance'    => $user->balance ?? 0,
            'role'       => $user->role,
            'status'     => $user->is_active ? 'active' : 'blocked',
            'created_at' => $user->created_at?->format('d.m.Y'),
            'avatar_url' => $user->avatar_url,
        ];
    }
}
