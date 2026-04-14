<?php

namespace Database\Seeders;

use App\Models\ContaCuenta;
use Illuminate\Database\Seeder;

class ContaCuentaSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['codigo' => '1.1.01', 'nombre' => 'Caja General', 'tipo' => 'activo', 'subtipo' => 'caja', 'acepta_movimiento' => true],
            ['codigo' => '1.1.02', 'nombre' => 'Banco BAC', 'tipo' => 'activo', 'subtipo' => 'banco', 'acepta_movimiento' => true],
            ['codigo' => '1.1.04', 'nombre' => 'Banco Lafise', 'tipo' => 'activo', 'subtipo' => 'banco', 'acepta_movimiento' => true],
            ['codigo' => '1.1.05', 'nombre' => 'Banco Ficohsa', 'tipo' => 'activo', 'subtipo' => 'banco', 'acepta_movimiento' => true],
            ['codigo' => '1.1.06', 'nombre' => 'Zelle', 'tipo' => 'activo', 'subtipo' => 'banco', 'acepta_movimiento' => true],
            ['codigo' => '1.1.03', 'nombre' => 'Cuentas por Cobrar Clientes', 'tipo' => 'activo', 'subtipo' => 'cxc', 'acepta_movimiento' => true],
            ['codigo' => '2.1.01', 'nombre' => 'Impuestos por Pagar', 'tipo' => 'pasivo', 'subtipo' => 'impuesto', 'acepta_movimiento' => true],
            ['codigo' => '3.1.01', 'nombre' => 'Capital', 'tipo' => 'patrimonio', 'subtipo' => 'capital', 'acepta_movimiento' => true],
            ['codigo' => '4.1.01', 'nombre' => 'Ingresos por Servicios', 'tipo' => 'ingreso', 'subtipo' => 'servicios', 'acepta_movimiento' => true],
            ['codigo' => '5.1.01', 'nombre' => 'Gastos Operativos', 'tipo' => 'gasto', 'subtipo' => 'opex', 'acepta_movimiento' => true],
            ['codigo' => '5.1.02', 'nombre' => 'Pérdida Cambiaria', 'tipo' => 'gasto', 'subtipo' => 'fx', 'acepta_movimiento' => true],
            ['codigo' => '4.1.02', 'nombre' => 'Ganancia Cambiaria', 'tipo' => 'ingreso', 'subtipo' => 'fx', 'acepta_movimiento' => true],
        ];

        foreach ($rows as $row) {
            ContaCuenta::updateOrCreate(
                ['codigo' => $row['codigo']],
                array_merge($row, ['activa' => true])
            );
        }
    }
}
