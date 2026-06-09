<?php

namespace Database\Seeders;

use App\Models\ContaCuenta;
use App\Models\ContaGastoCategoria;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContaGastoCategoriaSeeder extends Seeder
{
    public function run(): void
    {
        // Cuenta contable padre (Gastos Operativos) — la del seeder original
        $cuentaGastos = ContaCuenta::query()
            ->where('codigo', '5.1.01')
            ->orWhere('subtipo', 'opex')
            ->first();

        $cuentaId = $cuentaGastos?->id;

        $categorias = [
            ['nombre' => 'Combustible',         'slug' => 'combustible',         'icono' => 'fa-gas-pump',         'orden' => 10],
            ['nombre' => 'Salarios',            'slug' => 'salarios',            'icono' => 'fa-user-tie',         'orden' => 20],
            ['nombre' => 'Alquiler',            'slug' => 'alquiler',            'icono' => 'fa-building',         'orden' => 30],
            ['nombre' => 'Servicios públicos',  'slug' => 'servicios-publicos',  'icono' => 'fa-bolt',             'orden' => 40],
            ['nombre' => 'Mantenimiento',       'slug' => 'mantenimiento',       'icono' => 'fa-wrench',           'orden' => 50],
            ['nombre' => 'Comisiones',          'slug' => 'comisiones',          'icono' => 'fa-percent',          'orden' => 60],
            ['nombre' => 'Suministros',         'slug' => 'suministros',         'icono' => 'fa-box',              'orden' => 70],
            ['nombre' => 'Impuestos',           'slug' => 'impuestos',           'icono' => 'fa-file-invoice',     'orden' => 80],
            ['nombre' => 'Transporte / Fletes', 'slug' => 'transporte-fletes',   'icono' => 'fa-truck',            'orden' => 90],
            ['nombre' => 'Otros',               'slug' => 'otros',               'icono' => 'fa-ellipsis',         'orden' => 100],
        ];

        DB::transaction(function () use ($categorias, $cuentaId) {
            foreach ($categorias as $cat) {
                ContaGastoCategoria::updateOrCreate(
                    ['slug' => $cat['slug']],
                    array_merge($cat, [
                        'cuenta_contable_id' => $cuentaId,
                        'activa' => true,
                    ])
                );
            }
        });
    }
}
