<?php

namespace Database\Seeders;

use App\Models\CargotrackAgencia;
use App\Models\CargotrackSetting;
use Illuminate\Database\Seeder;

class CargotrackAgenciaSeeder extends Seeder
{
    public function run(): void
    {
        CargotrackSetting::current();

        foreach ([
            ['codigo' => 'CHLOGIX', 'nombre' => 'CH Logix'],
            ['codigo' => 'SHIPIA', 'nombre' => 'Shipia'],
        ] as $row) {
            CargotrackAgencia::updateOrCreate(
                ['codigo' => $row['codigo']],
                [
                    'nombre' => $row['nombre'],
                    'activo' => true,
                ]
            );
        }
    }
}
