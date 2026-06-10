<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UpdateCarsSeeder extends Seeder
{
    public function run(): void
    {
        $week = Carbon::now()->addDays(7);
        $week2 = Carbon::now()->addDays(5);
        $week3 = Carbon::now()->addDays(3);
        $week4 = Carbon::now()->addDays(10);

        $cars = [
            [
                'id' => 1,
                'image_url' => 'https://images.unsplash.com/photo-1617814076367-b759c7d7e738?w=800',
                'ends_at' => $week,
            ],
            [
                'id' => 2,
                'image_url' => 'https://images.unsplash.com/photo-1520031441872-265e4ff70366?w=800',
                'ends_at' => $week2,
            ],
            [
                'id' => 3,
                'image_url' => 'https://images.unsplash.com/photo-1614162692292-7ac56d7f7f1e?w=800',
                'ends_at' => $week3,
            ],
            [
                'id' => 4,
                'image_url' => 'https://images.unsplash.com/photo-1588258219511-64eb629cb833?w=800',
                'ends_at' => $week4,
            ],
            [
                'id' => 5,
                'image_url' => 'https://images.unsplash.com/photo-1606016159991-dfe4f2746ad5?w=800',
                'ends_at' => $week,
            ],
            [
                'id' => 6,
                'image_url' => 'https://images.unsplash.com/photo-1559416523-140ddc3d238c?w=800',
                'ends_at' => $week2,
            ],
            [
                'id' => 7,
                'image_url' => 'https://images.unsplash.com/photo-1560958089-b8a1929cea89?w=800',
                'ends_at' => $week3,
            ],
            [
                'id' => 8,
                'image_url' => 'https://images.unsplash.com/photo-1631295868223-63265b40d9e4?w=800',
                'ends_at' => $week4,
            ],
            [
                'id' => 9,
                'image_url' => 'https://images.unsplash.com/photo-1606152421802-db97b9c7a11b?w=800',
                'ends_at' => $week,
            ],
            [
                'id' => 10,
                'image_url' => 'https://images.unsplash.com/photo-1563720223185-11003d516935?w=800',
                'ends_at' => $week2,
            ],
            [
                'id' => 11,
                'image_url' => 'https://images.unsplash.com/photo-1621135802920-133df287f89c?w=800',
                'ends_at' => $week3,
            ],
            [
                'id' => 12,
                'image_url' => 'https://images.unsplash.com/photo-1562591176-b4ac53c20f6c?w=800',
                'ends_at' => $week4,
            ],
            [
                'id' => 14,
                'image_url' => 'https://images.unsplash.com/photo-1503376780353-7e6692767b70?w=800',
                'ends_at' => $week,
            ],
            [
                'id' => 15,
                'image_url' => 'https://images.unsplash.com/photo-1612544448445-b8232cff3b6c?w=800',
                'ends_at' => $week2,
            ],
            [
                'id' => 16,
                'image_url' => 'https://images.unsplash.com/photo-1519245659620-e859806a8d3b?w=800',
                'ends_at' => $week3,
            ],
        ];

        foreach ($cars as $car) {
            DB::table('cars')->where('id', $car['id'])->update([
                'image_url' => $car['image_url'],
                'ends_at'   => $car['ends_at'],
                'is_finished' => false,
                'updated_at' => Carbon::now(),
            ]);
        }

        $this->command->info('✅ Все авто обновлены с новыми фото и таймерами!');
    }
}