<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $rows = [
            ['codigo' => '1.1.01', 'nombre' => 'Caja General', 'tipo' => 'activo', 'subtipo' => 'caja'],
            ['codigo' => '1.1.02', 'nombre' => 'Banco BAC', 'tipo' => 'activo', 'subtipo' => 'banco'],
            ['codigo' => '1.1.04', 'nombre' => 'Banco Lafise', 'tipo' => 'activo', 'subtipo' => 'banco'],
            ['codigo' => '1.1.05', 'nombre' => 'Banco Ficohsa', 'tipo' => 'activo', 'subtipo' => 'banco'],
            ['codigo' => '1.1.06', 'nombre' => 'Zelle', 'tipo' => 'activo', 'subtipo' => 'banco'],
        ];

        foreach ($rows as $row) {
            DB::table('conta_cuentas')->updateOrInsert(
                ['codigo' => $row['codigo']],
                [
                    'nombre' => $row['nombre'],
                    'tipo' => $row['tipo'],
                    'subtipo' => $row['subtipo'],
                    'acepta_movimiento' => true,
                    'activa' => true,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }

    public function down(): void
    {
        DB::table('conta_cuentas')
            ->whereIn('codigo', ['1.1.02', '1.1.04', '1.1.05', '1.1.06'])
            ->delete();
    }
};
