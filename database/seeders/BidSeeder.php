<?php

namespace Database\Seeders;

use App\Models\Bid;
use App\Models\Car;
use App\Models\User;
use Illuminate\Database\Seeder;

class BidSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@drive.kz')->first();
        $user  = User::where('email', 'user@drive.kz')->first();

        if (!$admin || !$user) {
            return;
        }

        $bmw = Car::where('vin', 'WBHJE7C58NCC12345')->first();
        if ($bmw) {
            Bid::updateOrCreate(
                ['car_id' => $bmw->id, 'user_id' => $user->id, 'amount' => 47000000],
                ['car_id' => $bmw->id, 'user_id' => $user->id, 'amount' => 47000000]
            );
            Bid::updateOrCreate(
                ['car_id' => $bmw->id, 'user_id' => $admin->id, 'amount' => 50000000],
                ['car_id' => $bmw->id, 'user_id' => $admin->id, 'amount' => 50000000]
            );
            Bid::updateOrCreate(
                ['car_id' => $bmw->id, 'user_id' => $user->id, 'amount' => 52500000],
                ['car_id' => $bmw->id, 'user_id' => $user->id, 'amount' => 52500000]
            );
        }

        $mercedes = Car::where('vin', 'WDC4632241X123456')->first();
        if ($mercedes) {
            Bid::updateOrCreate(
                ['car_id' => $mercedes->id, 'user_id' => $admin->id, 'amount' => 82000000],
                ['car_id' => $mercedes->id, 'user_id' => $admin->id, 'amount' => 82000000]
            );
            Bid::updateOrCreate(
                ['car_id' => $mercedes->id, 'user_id' => $user->id, 'amount' => 89000000],
                ['car_id' => $mercedes->id, 'user_id' => $user->id, 'amount' => 89000000]
            );
        }

        $porsche = Car::where('vin', 'WP0ZZZ99ZNS123456')->first();
        if ($porsche) {
            Bid::updateOrCreate(
                ['car_id' => $porsche->id, 'user_id' => $user->id, 'amount' => 105000000],
                ['car_id' => $porsche->id, 'user_id' => $user->id, 'amount' => 105000000]
            );
            Bid::updateOrCreate(
                ['car_id' => $porsche->id, 'user_id' => $admin->id, 'amount' => 115000000],
                ['car_id' => $porsche->id, 'user_id' => $admin->id, 'amount' => 115000000]
            );
        }
    }
}