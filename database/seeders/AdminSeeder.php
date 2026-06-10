<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Создаём или обновляем админ-аккаунт
        User::updateOrCreate(
            ['email' => 'admin@drive.kz'],
            [
                'nickname'   => 'Администратор',
                'full_name'  => 'Администратор QAZDrive',
                'email'      => 'admin@drive.kz',
                'phone'      => '+77000000001',
                'city'       => 'Алматы',
                'password'   => Hash::make('Admin@12345'),
                'role'       => 'admin',
                'tariff'     => 'VIP',
                'balance'    => 10000000,
                'is_active'  => true,
            ]
        );

        $this->command->info('✅ Админ создан: admin@drive.kz / Admin@12345');
    }
}
