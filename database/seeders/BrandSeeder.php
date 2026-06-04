<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        $brands = [
            ['name' => 'Toyota',       'logo_url' => 'https://logo.clearbit.com/toyota.com'],
            ['name' => 'BMW',          'logo_url' => 'https://logo.clearbit.com/bmw.com'],
            ['name' => 'Mercedes',     'logo_url' => 'https://logo.clearbit.com/mercedes-benz.com'],
            ['name' => 'Audi',         'logo_url' => 'https://logo.clearbit.com/audi.com'],
            ['name' => 'Lexus',        'logo_url' => 'https://logo.clearbit.com/lexus.com'],
            ['name' => 'Porsche',      'logo_url' => 'https://logo.clearbit.com/porsche.com'],
            ['name' => 'Ferrari',      'logo_url' => 'https://logo.clearbit.com/ferrari.com'],
            ['name' => 'Lamborghini',  'logo_url' => 'https://logo.clearbit.com/lamborghini.com'],
            ['name' => 'Bentley',      'logo_url' => 'https://logo.clearbit.com/bentley.com'],
            ['name' => 'Rolls-Royce',  'logo_url' => 'https://logo.clearbit.com/rolls-roycemotorcars.com'],
            ['name' => 'Aston Martin', 'logo_url' => 'https://logo.clearbit.com/astonmartin.com'],
            ['name' => 'Maserati',     'logo_url' => 'https://logo.clearbit.com/maserati.com'],
            ['name' => 'McLaren',      'logo_url' => 'https://logo.clearbit.com/mclaren.com'],
            ['name' => 'Bugatti',      'logo_url' => 'https://logo.clearbit.com/bugatti.com'],
            ['name' => 'Jaguar',       'logo_url' => 'https://logo.clearbit.com/jaguar.com'],
            ['name' => 'Land Rover',   'logo_url' => 'https://logo.clearbit.com/landrover.com'],
            ['name' => 'Volvo',        'logo_url' => 'https://logo.clearbit.com/volvocars.com'],
            ['name' => 'Tesla',        'logo_url' => 'https://logo.clearbit.com/tesla.com'],
            ['name' => 'Ford',         'logo_url' => 'https://logo.clearbit.com/ford.com'],
            ['name' => 'Chevrolet',    'logo_url' => 'https://logo.clearbit.com/chevrolet.com'],
            ['name' => 'Dodge',        'logo_url' => 'https://logo.clearbit.com/dodge.com'],
            ['name' => 'Cadillac',     'logo_url' => 'https://logo.clearbit.com/cadillac.com'],
            ['name' => 'Lincoln',      'logo_url' => 'https://logo.clearbit.com/lincoln.com'],
            ['name' => 'Jeep',         'logo_url' => 'https://logo.clearbit.com/jeep.com'],
            ['name' => 'Honda',        'logo_url' => 'https://logo.clearbit.com/honda.com'],
            ['name' => 'Nissan',       'logo_url' => 'https://logo.clearbit.com/nissan.com'],
            ['name' => 'Mazda',        'logo_url' => 'https://logo.clearbit.com/mazda.com'],
            ['name' => 'Subaru',       'logo_url' => 'https://logo.clearbit.com/subaru.com'],
            ['name' => 'Mitsubishi',   'logo_url' => 'https://logo.clearbit.com/mitsubishi.com'],
            ['name' => 'Infiniti',     'logo_url' => 'https://logo.clearbit.com/infiniti.com'],
            ['name' => 'Acura',        'logo_url' => 'https://logo.clearbit.com/acura.com'],
            ['name' => 'Hyundai',      'logo_url' => 'https://logo.clearbit.com/hyundai.com'],
            ['name' => 'Kia',          'logo_url' => 'https://logo.clearbit.com/kia.com'],
            ['name' => 'Genesis',      'logo_url' => 'https://logo.clearbit.com/genesis.com'],
            ['name' => 'Volkswagen',   'logo_url' => 'https://logo.clearbit.com/vw.com'],
            ['name' => 'Skoda',        'logo_url' => 'https://logo.clearbit.com/skoda-auto.com'],
            ['name' => 'Peugeot',      'logo_url' => 'https://logo.clearbit.com/peugeot.com'],
            ['name' => 'Renault',      'logo_url' => 'https://logo.clearbit.com/renault.com'],
            ['name' => 'Citroën',      'logo_url' => 'https://logo.clearbit.com/citroen.com'],
            ['name' => 'Alfa Romeo',   'logo_url' => 'https://logo.clearbit.com/alfaromeo.com'],
            ['name' => 'Fiat',         'logo_url' => 'https://logo.clearbit.com/fiat.com'],
            ['name' => 'Lada',         'logo_url' => 'https://logo.clearbit.com/lada.ru'],
        ];

        foreach ($brands as $brand) {
            Brand::updateOrCreate(['name' => $brand['name']], $brand);
        }
    }
}