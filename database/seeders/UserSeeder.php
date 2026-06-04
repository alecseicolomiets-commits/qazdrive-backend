<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@drive.kz'],
            [
                'nickname'  => 'Администратор',
                'full_name' => 'Администратор Системы',
                'email'     => 'admin@drive.kz',
                'password'  => Hash::make('admin123'),
                'phone'     => '+7 (701) 123-45-67',
                'city'      => 'Алматы',
                'role'      => 'admin',
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'user@drive.kz'],
            [
                'nickname'  => 'Алибек',
                'full_name' => 'Алибек Нурбеков',
                'email'     => 'user@drive.kz',
                'password'  => Hash::make('user123'),
                'phone'     => '+7 (702) 987-65-43',
                'city'      => 'Астана',
                'role'      => 'user',
                'is_active' => true,
            ]
        );
    }
}