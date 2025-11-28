<?php

namespace Database\Seeders;

use App\Models\Driver;
use Illuminate\Database\Seeder;

class DriverSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $drivers = [
            ['name' => 'Juan Pérez', 'license_number' => '12345678-9', 'phone' => '7000-0001'],
            ['name' => 'María García', 'license_number' => '98765432-1', 'phone' => '7000-0002'],
            ['name' => 'Carlos López', 'license_number' => '45678912-3', 'phone' => '7000-0003'],
            ['name' => 'Ana Martínez', 'license_number' => '78912345-6', 'phone' => '7000-0004'],
            ['name' => 'Roberto Sánchez', 'license_number' => '32165498-7', 'phone' => '7000-0005'],
        ];

        foreach ($drivers as $driver) {
            Driver::create($driver);
        }
    }
}
